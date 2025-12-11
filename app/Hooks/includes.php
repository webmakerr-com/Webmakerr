<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php

use Webmakerr\Framework\Validator\Rule;

Rule::add('sanitizeTextArea', \Webmakerr\App\Http\Rules\SanitizeTextArea::class);
Rule::add('sanitizeText', \Webmakerr\App\Http\Rules\SanitizeText::class);
Rule::add('maxPostCode', \Webmakerr\App\Http\Rules\MaxPostCodeRule::class);
Rule::add('maxLength', \Webmakerr\App\Http\Rules\MaxLengthRule::class);