<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
/**
 * @var $order \Webmakerr\App\Models\Order
 */
$celebration = \Webmakerr\App\Services\TemplateService::getCelebration('order');
?>

<?php
if (!empty($celebration)) {
    \Webmakerr\App\App::make('view')->render('emails.parts.celebration', [
        'text' => $celebration
    ]);
}
?>

<div class="space_bottom_30">
    <p><?php echo esc_html__('Hey there 🙌,', 'webmakerr-cart'); ?></p>
    <p>
        <?php
        printf(
            /* translators: %s is the customer's full name */
            esc_html__('%s just placed an order. Here are the details:', 'webmakerr-cart'),
            esc_html($order->customer->full_name)
        );
        ?>
    </p>
</div>

<?php

\Webmakerr\App\App::make('view')->render('emails.parts.items_table', [
    'order'          => $order,
    'formattedItems' => $order->order_items,
    'heading'        => __('Order Summary', 'webmakerr-cart'),
]);

echo '<hr />';

\Webmakerr\App\App::make('view')->render('emails.parts.addresses', [
    'order' => $order,
]);

echo '<hr />';

\Webmakerr\App\App::make('view')->render('emails.parts.call_to_action_box', [
    'content'     => __('To view more details of this order, please check the order detail page.', 'webmakerr-cart'),
    'link'        => $order->getViewUrl('admin'),
    'button_text' => __('View Details', 'webmakerr-cart')
]);