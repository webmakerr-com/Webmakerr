<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
/**
 * Generic Fallback Template for FluentCart Archives mainly for product archives
 *
 */
webmakerr_do_action('webmakerr_cart/generic_template/rendering');
get_header();
?>
<?php webmakerr_do_action('webmakerr_cart/generic_template/before_content'); ?>
<div style="width: 100%; max-width: var(--global-content-width); display: block; margin-top: 20px; margin-bottom: 20px;" class="fct-genric-template-wrapper site-container">
    <div id="main" class="site-main">
        <?php webmakerr_do_action('webmakerr_cart/template/before_content'); ?>
        <?php webmakerr_do_action('webmakerr_cart/template/main_content'); ?>
        <?php webmakerr_do_action('webmakerr_cart/template/after_content'); ?>
    </div>
</div>
<?php webmakerr_do_action('webmakerr_cart/generic_template/after_content'); ?>

<?php get_footer(); ?>
