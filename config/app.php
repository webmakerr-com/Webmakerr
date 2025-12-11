<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php

return [
    'name'           => 'Webmakerr Cart',
    'slug'           => 'fluent-cart',
    'domain_path'    => '/language',
    'text_domain'    => 'webmakerr-cart',
    'legacy_text_domains' => [
        'fluent-cart'
    ],
    'hook_prefix'    => 'fluent_cart/',
    'rest_namespace' => 'fluent-cart',
    'rest_version'   => 'v2',
    'env'            => 'production',
    'using_faker'    => false,
];
