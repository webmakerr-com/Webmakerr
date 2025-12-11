<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php

return [
    'name'           => 'Webmakerr Cart',
    'slug'           => 'webmakerr',
    'domain_path'    => '/language',
    'text_domain'    => 'webmakerr-cart',
    'legacy_text_domains' => [
        'fluent-cart'
    ],
    'hook_prefix'    => 'webmakerr_cart/',
    'rest_namespace' => 'webmakerr-cart',
    'rest_version'   => 'v2',
    'env'            => 'production',
    'using_faker'    => false,
];
