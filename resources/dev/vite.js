const [major] = process.versions.node.split('.').map(Number);

if (major < 20) {
    console.error('\x1b[31m\x1b[1m❌🚨 Node.js version 20 or higher is required!! 🚨❌\x1b[0m');
    process.exit(1); // exit with error
}

const {glob} = require("glob");
const fs = require('fs');
const fsPromises = fs.promises;
const path = require('path');

const updateServerConfigPort = require('./portfinder.js');
const {spawn} = require("child_process");

const argumentsList = process.argv;

let mode = 'dev';
let switchTo = 'production';

if (typeof argumentsList[2] !== 'undefined' && argumentsList[2] === '--build') {
    mode = 'production';
    switchTo = 'dev';
}

const modeTitle = mode === 'dev' ? 'Development' : 'Production';
const regexObj = new RegExp(`["']env["']\\s+=>\\s*["']` + switchTo + `["'],?`, 'g');
const fakerRegex = new RegExp(`["']using_faker["']\\s+=>\\s*(true|false),?`, "g");
let fakerMode = true;

if (mode === 'production' && typeof process.env.npm_config_faker === 'undefined') {
    fakerMode = false;
}

const cleanDevArtifacts = async () => {
    if (mode !== 'dev') {
        return;
    }

    const assetsPath = path.resolve('./assets');
    const buildPath = path.resolve('./builds');

    if (fs.existsSync(assetsPath)) {
        try {
            await fsPromises.rm(assetsPath, {recursive: true, force: true});
            console.log('🧹 Deleted assets folder.');
        } catch (err) {
        }
    }

    if (fs.existsSync(buildPath)) {
        try {
            await fsPromises.rm(buildPath, {recursive: true, force: true});
            console.log('🧹 Deleted builds folder.');
        } catch (err) {
        }
    }

    const configPath = path.resolve(__dirname, "../../config/vite_config.php");
    console.log(configPath, 'configPath');
    if (fs.existsSync(configPath)) {
        await fsPromises.writeFile(configPath, '<?php return ' + '[]' + ';', "utf8");
        console.log("✅ Manifest array cleared");
    }

    const {port, updated, isFree} = await updateServerConfigPort();
    if (!isFree) {
        console.error(`❌ All allowed ports are currently in use. Exiting.`);
        process.exit(1);
    }

    if (updated) {
        console.log(`✅ Server port switched to ${port}`);
    } else {
        console.log(`✅ Server port ${port} is free. No update needed.`);
    }
};

const dumpComposerAutoload = async () => {
    if (mode !== 'production') {
        return;
    }

    await new Promise((resolve, reject) => {
        let stderrData = '';

        const composer = spawn('composer', ['dump-autoload', '--classmap-authoritative'], {
            stdio: ['inherit', 'inherit', 'pipe']
        });

        composer.stderr.on('data', (data) => {
            stderrData += data.toString();
            process.stderr.write(data);
        });

        composer.on('error', (error) => {
            reject(error);
        });

        composer.on('close', (code) => {
            if (code === 0) {
                if (stderrData) {
                    console.warn('⚠️ Composer autoload completed with warnings.');
                } else {
                    console.log('✅ Composer autoload dump completed.');
                }

                resolve();
                return;
            }

            reject(new Error('❌🚨 There was some error while autoloading Composer! 🚨❌'));
        });
    });
};

const updateAppConfig = async () => {
    const files = await glob(['config/app.php']);

    await Promise.all(files.map(async (item) => {
        const data = await fsPromises.readFile(item, 'utf8');
        let result = data.replace(regexObj, "'env'            => '" + mode + "',");

        result = result.replace(fakerRegex, `'using_faker'    => ${fakerMode},`);

        await fsPromises.writeFile(item, result, 'utf8');
        console.log(`✅ ${modeTitle} asset enqueued!`);
    }));
};

(async () => {
    await cleanDevArtifacts();
    await dumpComposerAutoload();
    await updateAppConfig();
})().catch((error) => {
    console.error(error.message || error);
    process.exit(1);
});
