<?php if (!defined('ABSPATH')) exit; ?>
<?php
/**
 * @var $order \Webmakerr\App\Models\Order
 */
?>
    <div class="space_bottom_30">
        <p><?php
            printf(
            /* translators: %s is the customer's full name */
                esc_html__('Hello %s,', 'webmakerr-cart'),
                esc_html($order->customer->full_name)
            );
            ?></p>
        <p>
            <?php esc_html_e('We have processed a refund for your recent order', 'webmakerr-cart'); ?>
            <a href="<?php echo esc_url($order->getViewUrl('customer')); ?>">
                #<?php echo esc_html($order->invoice_no); ?>
            </a>.
            <?php echo esc_html__('Thank you for your understanding, and we truly value your trust in us. Below are the details of your refund.', 'webmakerr-cart'); ?>
        </p>
    </div>
<?php

\Webmakerr\App\App::make('view')->render('emails.parts.items_table', [
    'order'          => $order,
    'formattedItems' => $order->order_items,
    'heading'        => __('Summary', 'webmakerr-cart'),
]);

echo '<hr />';

\Webmakerr\App\App::make('view')->render('emails.parts.call_to_action_box', [
    'content'     => __('The refund should appear in your account within 5-10 business days, depending on your payment provider.', 'webmakerr-cart'),
    'link'        => $order->getViewUrl('customer'),
    'button_text' => __('View Details', 'webmakerr-cart')
]);