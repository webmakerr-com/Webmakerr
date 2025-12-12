<?php

namespace FluentCart\App\Helpers;

class SupportBoardAssets
{
    protected static $enqueued = false;

    public static function enqueue()
    {
        if (self::$enqueued) {
            return;
        }

        $dependency = 'jquery';

        if (!wp_script_is('jquery', 'enqueued') && !wp_script_is('jquery', 'registered')) {
            wp_register_script(
                'fluentcart-supportboard-jquery',
                'https://webmakerr.com/wp-content/plugins/supportboard/supportboard/js/min/jquery.min.js',
                [],
                FLUENTCART_VERSION,
                false
            );
            wp_enqueue_script('fluentcart-supportboard-jquery');
            $dependency = 'fluentcart-supportboard-jquery';
        } else {
            wp_enqueue_script('jquery');
        }

        wp_enqueue_script(
            'fluentcart-supportboard-main',
            'https://webmakerr.com/wp-content/plugins/supportboard/supportboard/js/main.js',
            [$dependency],
            FLUENTCART_VERSION,
            true
        );

        self::$enqueued = true;
    }
}
