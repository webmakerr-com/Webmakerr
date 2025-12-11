const fs = require('fs');
const path = require('path');

const DEFAULT_TEXT_DOMAIN = 'webmakerr-cart';
const LEGACY_TEXT_DOMAINS = ['fluent-cart'];
const DEFAULT_POT_DIR = path.join(process.cwd(), 'language');


function extractTranslatorComment(content, lineNumber) {
    const lines = content.split(/\r?\n/);
    lineNumber = lineNumber - 1;

    let extractedCommand = null;

    // Get the target line index (0-based)
    const targetLineIndex = Math.max(0, lineNumber);

    // Take the last 3 lines including the current line
    const startLine = Math.max(0, targetLineIndex - 2);
    const chunk = lines.slice(startLine, targetLineIndex + 1).join('\n');


    // Match /* translators: ... */
    const blockCommentMatch = chunk.match(/\/\*\s*translators:\s*.*?\*\//is);
    if (blockCommentMatch) {
        extractedCommand = blockCommentMatch[0].trim();
    }

    // Match // translators: ...
    const lineCommentMatch = chunk.match(/\/\/\s*translators:\s*(.+?)$/im);
    if (lineCommentMatch) {
        extractedCommand = lineCommentMatch[0].trim();
    }

    // Match <!-- translators: ... --> (for HTML/Vue files)
    const htmlCommentMatch = [...chunk.matchAll(/<!--\s*translators:\s*(.+?)-->/gis)].pop();

    if (htmlCommentMatch) {
        let comment = htmlCommentMatch[0].trim();
        comment = comment.replace(/<!--/g, '/*').replace(/-->/g, ' */').trim();
        extractedCommand = comment;
    }

    if (extractedCommand) {
        extractedCommand = '    '+extractedCommand.replace(/(\*)\s+(translators)/, '$1 $2');
    }

    return extractedCommand;
}

function escapePhpString(str) {
    const escaped = str
        .replace(/(?<!\\)'/g, "\\'")  // Escape unescaped single quotes
        .replace(/(?<!\\)"/g, '\\"'); // Escape unescaped double quotes

    // if (str !== escaped) {
    //     console.log(`Escaping: "${str}" -> "${escaped}"`);
    // }

    return escaped;
}

function extractTranslations(dir, translationRegex, excludeDirs, excludeFiles) {
    let translations = {};
    const commentsArray = {};

    function scanDirectory(directory) {
        fs.readdirSync(directory).forEach(file => {
            const fullPath = path.join(directory, file);


            if (fs.statSync(fullPath).isDirectory()) {
                if (!excludeDirs.includes(path.basename(fullPath))) {
                    scanDirectory(fullPath);
                }
            } else if ((fullPath.endsWith('.js') || fullPath.endsWith('.jsx') || fullPath.endsWith('.vue')) && !excludeFiles.includes(path.basename(fullPath))) {
                const content = fs.readFileSync(fullPath, 'utf8');
                let match;
                while ((match = translationRegex.exec(content)) !== null) {
                    const translation = match[1] || match[2] || match[3] || match[4] || match[5] || match[6] || match[7] || match[8] || match[9] || match[10] || match[11] || match[12];
                    if (translation) {
                        const textBeforeMatch = content.substring(0, match.index);
                        const lineNumber = (textBeforeMatch.match(/\n/g) || []).length + 1;
                        const relativePath = path.relative(process.cwd(), fullPath);
                        const location = `${relativePath}:${lineNumber}`;

                        const transComment = extractTranslatorComment(content, lineNumber);
                        if (transComment) {
                            commentsArray[translation] = transComment;
                        }

                        if (!translations[translation]) {
                            translations[translation] = new Set();
                        }
                        translations[translation].add(location);
                    }
                }
            }
        });
    }

    if (Array.isArray(dir)) {
        dir.forEach(d => {
            scanDirectory(d);
        });
    } else {
        scanDirectory(dir);
    }

    const t = Object.fromEntries(
        Object.entries(translations).map(([key, locations]) => [key, [...locations].join(', ')])
    );

    return {
        translations: t,
        comments: commentsArray
    }

}


function updatePhpTranslations(newTranslations, existingTranslations, filePath, includeSource, commentsArray, domain = DEFAULT_TEXT_DOMAIN) {

    let phpArray = {};
    Object.keys(newTranslations).forEach(t => {
        phpArray[t] = t;
    });

    let phpContent = `<?php if ( ! defined( 'ABSPATH' ) ) exit; \n\nreturn [\n`;
    const escapedKeys = [];
    Object.keys(phpArray).sort().forEach(key => {

        let escapedKey = escapePhpString(key);
        escapedKeys.push(escapedKey);
        if (includeSource) {
            phpContent += `    /**\n    * Found In\n`;
            const locations = newTranslations[key].split(', ');
            locations.forEach((location, index) => {
                phpContent += `    * ${index + 1}) @see ${location}\n`;
            });
            phpContent += `    */\n`;
            if (commentsArray.hasOwnProperty(key)) {
                phpContent += (commentsArray[key] + `\n`);
            }
            phpContent += `    '${escapedKey}' => __('${escapedKey}', '${domain}'),\n`;
        } else {
            if (commentsArray.hasOwnProperty(key)) {
                phpContent += (commentsArray[key] + `\n`);
            }
            phpContent += `    '${escapedKey}' => __('${escapedKey}', '${domain}'),\n`;
        }


    });
    phpContent += `];\n`;

    fs.writeFileSync(filePath, phpContent, {encoding: 'utf8'});
    console.log(`PHP translations updated successfully. Total entries: ${Object.keys(phpArray).length}`);

    const writtenContent = fs.readFileSync(filePath, 'utf8');
    escapedKeys.forEach(escapedKey => {
        if (!writtenContent.includes(escapedKey)) {
            console.warn(`Warning: "${escapedKey}" not found in written file. Possible truncation. Check ${filePath}`);
        }
    });
}

function formatPotDate() {
    const now = new Date();
    const year = now.getUTCFullYear();
    const month = String(now.getUTCMonth() + 1).padStart(2, '0');
    const day = String(now.getUTCDate()).padStart(2, '0');
    const hours = String(now.getUTCHours()).padStart(2, '0');
    const minutes = String(now.getUTCMinutes()).padStart(2, '0');

    return `${year}-${month}-${day} ${hours}:${minutes}+0000`;
}

function escapePotString(str) {
    return str
        .replace(/\\/g, '\\\\')
        .replace(/"/g, '\\"')
        .replace(/\n/g, '\\n');
}

function collectLegacyEntries(potPath) {
    if (!fs.existsSync(potPath)) {
        return [];
    }

    const lines = fs.readFileSync(potPath, 'utf8').split(/\r?\n/);
    const entries = [];
    let buffering = false;
    let buffer = '';

    lines.forEach(line => {
        if (line.startsWith('msgid')) {
            buffer = line.replace(/^msgid\s+/, '').trim();

            if (buffer === '""') {
                buffering = false;
                buffer = '';
                return;
            }

            buffer = buffer.replace(/^"/, '').replace(/"$/, '');
            buffering = true;
        } else if (buffering && line.startsWith('"')) {
            buffer += line.slice(1, -1);
        } else if (buffering && line.startsWith('msgstr')) {
            if (buffer) {
                entries.push(buffer);
            }
            buffering = false;
            buffer = '';
        }
    });

    return entries;
}

function normalizeTranslationsForPot(translations, commentsArray, domain, outputDir, legacyDomains) {
    const translationMap = new Map();

    Object.entries(translations).forEach(([key, locations]) => {
        translationMap.set(key, locations.split(', '));
    });

    const existingFiles = [path.join(outputDir, `${domain}.pot`), ...legacyDomains.map(ld => path.join(outputDir, `${ld}.pot`))];

    existingFiles.forEach(filePath => {
        collectLegacyEntries(filePath).forEach(entry => {
            if (!translationMap.has(entry)) {
                translationMap.set(entry, []);
            }
        });
    });

    return translationMap;
}

function buildPotContent(translationMap, commentsArray, domain) {
    let potContent = `msgid ""\n` +
        `msgstr ""\n` +
        `"Project-Id-Version: Webmakerr Cart\\n"\n` +
        `"Report-Msgid-Bugs-To: \n"\n` +
        `"POT-Creation-Date: ${formatPotDate()}\\n"\n` +
        `"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n"\n` +
        `"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n"\n` +
        `"Language-Team: \n"\n` +
        `"Language: \n"\n` +
        `"Plural-Forms: nplurals=INTEGER; plural=EXPRESSION;\\n"\n` +
        `"MIME-Version: 1.0\\n"\n` +
        `"Content-Type: text/plain; charset=UTF-8\\n"\n` +
        `"Content-Transfer-Encoding: 8bit\\n"\n` +
        `"X-Domain: ${domain}\n"\n\n`;

    Array.from(translationMap.keys()).sort().forEach(key => {
        const escapedKey = escapePotString(key);
        const locations = translationMap.get(key) || [];

        locations.forEach(location => {
            if (location) {
                potContent += `#: ${location}\n`;
            }
        });

        if (commentsArray && commentsArray.hasOwnProperty(key)) {
            const normalizedComment = commentsArray[key]
                .replace(/\/\*\s*/g, '')
                .replace(/\s*\*\//g, '')
                .trim();

            if (normalizedComment) {
                potContent += `#. ${normalizedComment}\n`;
            }
        }

        potContent += `msgid "${escapedKey}"\n`;
        potContent += `msgstr ""\n\n`;
    });

    return potContent;
}

function writePotFiles(translations, commentsArray = {}, options = {}) {
    const domain = options.domain || DEFAULT_TEXT_DOMAIN;
    const legacyDomains = options.legacyDomains || LEGACY_TEXT_DOMAINS;
    const outputDir = options.outputDir || DEFAULT_POT_DIR;

    if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, {recursive: true});
    }

    const translationMap = normalizeTranslationsForPot(translations, commentsArray, domain, outputDir, legacyDomains);

    const potContent = buildPotContent(translationMap, commentsArray, domain);
    fs.writeFileSync(path.join(outputDir, `${domain}.pot`), potContent, 'utf8');
    fs.writeFileSync(path.join(outputDir, `${domain}.po`), potContent, 'utf8');

    legacyDomains.forEach(legacyDomain => {
        const legacyContent = buildPotContent(translationMap, commentsArray, legacyDomain);
        fs.writeFileSync(path.join(outputDir, `${legacyDomain}.pot`), legacyContent, 'utf8');
    });
}

// CommonJS exports
module.exports = {
    DEFAULT_POT_DIR,
    DEFAULT_TEXT_DOMAIN,
    LEGACY_TEXT_DOMAINS,
    extractTranslatorComment,
    escapePhpString,
    extractTranslations,
    updatePhpTranslations,
    writePotFiles
};
