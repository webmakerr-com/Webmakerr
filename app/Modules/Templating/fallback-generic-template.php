<?php if (!defined('ABSPATH')) exit; ?>
<?php
/**
 * Generic Fallback Template for FluentCart pages with built-in header and footer
 */

do_action('fluent_cart/generic_template/rendering');

$storeSettings = new \FluentCart\Api\StoreSettings();
$storeName = $storeSettings->get('store_name', get_bloginfo('name'));
$storeTagline = get_bloginfo('description');
$storeLogo = $storeSettings->get('store_logo.url');
$homeUrl = home_url('/');
$pageType = \FluentCart\App\Services\TemplateService::getCurrentFcPageType();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class('fct-standalone-page'); ?>>
<?php wp_body_open(); ?>
<div class="fct-standalone-page__wrapper">
    <header class="fct-standalone-header">
        <div class="fct-standalone-container">
            <div class="fct-standalone-header__grid">
                <div class="fct-standalone-brand">
                    <a class="fct-standalone-brand__link" href="<?php echo esc_url($homeUrl); ?>">
                        <?php if ($storeLogo) : ?>
                            <img class="fct-standalone-brand__logo" src="<?php echo esc_url($storeLogo); ?>" alt="<?php echo esc_attr($storeName); ?>">
                        <?php else : ?>
                            <span class="fct-standalone-brand__name"><?php echo esc_html($storeName); ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if ($storeTagline) : ?>
                        <p class="fct-standalone-brand__tagline"><?php echo esc_html($storeTagline); ?></p>
                    <?php endif; ?>
                </div>
                <div class="fct-standalone-header__support">
                    <div class="fct-standalone-header__meta">
                        <span class="fct-standalone-header__badge"><?php esc_html_e('Secure Checkout', 'fluent-cart'); ?></span>
                        <span class="fct-standalone-header__badge"><?php esc_html_e('Fast Delivery', 'fluent-cart'); ?></span>
                        <span class="fct-standalone-header__badge"><?php esc_html_e('Quality Products', 'fluent-cart'); ?></span>
                    </div>
                    <div class="fct-standalone-header__actions">
                        <a class="fct-standalone-header__link" href="<?php echo esc_url($homeUrl); ?>"><?php esc_html_e('Back to Storefront', 'fluent-cart'); ?></a>
                        <span class="fct-standalone-header__divider" aria-hidden="true"></span>
                        <span class="fct-standalone-header__help">
                            <span class="fct-standalone-header__help-label"><?php esc_html_e('Need help?', 'fluent-cart'); ?></span>
                            <span class="fct-standalone-header__help-text"><?php esc_html_e('Our support team is ready to assist you', 'fluent-cart'); ?></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <?php do_action('fluent_cart/generic_template/before_content'); ?>
    <main class="fct-standalone-main">
        <div class="fct-standalone-container">
            <?php do_action('fluent_cart/template/before_content'); ?>
            <div class="fct-standalone-content">
                <?php
                if ($pageType === 'single_product') {
                    if (have_posts()) {
                        while (have_posts()) {
                            the_post();
                            the_content();
                        }
                    }
                } else {
                    do_action('fluent_cart/template/main_content');
                }
                ?>
            </div>
            <?php do_action('fluent_cart/template/after_content'); ?>
        </div>
    </main>
    <?php do_action('fluent_cart/generic_template/after_content'); ?>
    <footer class="fct-standalone-footer">
        <div class="fct-standalone-container">
            <div class="fct-standalone-footer__grid">
                <div class="fct-standalone-footer__brand">
                    <div class="fct-standalone-brand__name"><?php echo esc_html($storeName); ?></div>
                    <?php if ($storeTagline) : ?>
                        <p class="fct-standalone-footer__tagline"><?php echo esc_html($storeTagline); ?></p>
                    <?php endif; ?>
                    <p class="fct-standalone-footer__note"><?php echo esc_html__('Powered by FluentCart', 'fluent-cart'); ?></p>
                </div>
                <div class="fct-standalone-footer__meta">
                    <div class="fct-standalone-footer__links">
                        <a class="fct-standalone-footer__link" href="<?php echo esc_url($homeUrl); ?>"><?php esc_html_e('Home', 'fluent-cart'); ?></a>
                        <span class="fct-standalone-footer__separator" aria-hidden="true"></span>
                        <span class="fct-standalone-footer__link"><?php esc_html_e('Customer Support', 'fluent-cart'); ?></span>
                        <span class="fct-standalone-footer__separator" aria-hidden="true"></span>
                        <span class="fct-standalone-footer__link"><?php esc_html_e('Shipping & Returns', 'fluent-cart'); ?></span>
                    </div>
                    <div class="fct-standalone-footer__note">&copy; <?php echo esc_html(date_i18n('Y')); ?> <?php echo esc_html($storeName); ?></div>
                </div>
            </div>
        </div>
    </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
