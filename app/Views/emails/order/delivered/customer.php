<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
/**
 * @var $order \Webmakerr\App\Models\Order
 */
?>

<div class="space_bottom_30">
    <p>Hello <?php echo esc_html($order->customer->full_name); ?>,</p>
    <p>Wonderful news! Your order has been successfully delivered. We hope you're thrilled with your purchase! If you need any assistance or have feedback, we're here to help.</p>
</div>

<?php

\Webmakerr\App\App::make('view')->render('emails.parts.items_table', [
    'order'          => $order,
    'formattedItems' => $order->order_items,
    'heading'        => 'Order Summary',
]);

$downloads = $order->getDownloads();
if($downloads) {
    \Webmakerr\App\App::make('view')->render('emails.parts.downloads', [
        'order'         => $order,
        'heading'       => 'Downloads',
        'downloadItems' => $order->getDownloads() ?: [],
    ]);
}

echo '<hr />';

\Webmakerr\App\App::make('view')->render('emails.parts.addresses', [
    'order' => $order,
]);

\Webmakerr\App\App::make('view')->render('emails.parts.call_to_action_box', [
    'content'     => 'Thank you for shopping with us! We look forward to serving you again.',
    'link'        => $order->getViewUrl('customer'),
    'button_text' => 'View Details'
]);
