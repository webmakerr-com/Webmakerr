<?php

namespace Webmakerr\App\Http\Rules;

class SanitizeTextArea
{
    public function __invoke($attr, $value, $rules, $data, ...$params)
    {
        $value = trim($value);
        
        if (is_numeric($value)) {
            $value = (string)$value;
        }

        $isString = is_string($value);
        if (!$isString) {
            return sprintf(
                /* translators: 1: attribute name */
                __('The %s must be a valid text', 'webmakerr-cart'),
                $attr
            );
        }
        $sanitizedValue = sanitize_textarea_field($value);
        if ($sanitizedValue !== $value) {
            return sprintf(
                /* translators: 1: attribute name */
                __('The %s must be a valid text', 'webmakerr-cart'),
                $attr
            );
        }

        return null;
    }

}