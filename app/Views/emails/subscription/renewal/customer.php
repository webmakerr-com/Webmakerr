<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
/**
 * @var \Webmakerr\App\Models\Subscription $subscription
 * @var \Webmakerr\App\Models\Order $order
 */

$transaction = $subscription->getLatestTransaction();
?>

<div class="space_bottom_30">
    <p>Hello <?php echo esc_html($subscription->customer->full_name) ?>,</p>
    <p>Your subscription has been successfully renewed, ensuring uninterrupted access to <?php echo esc_html($subscription->item_name); ?>.</p>
</div>

<?php
\Webmakerr\App\App::make('view')->render('emails.parts.subscription_item', [
    'transaction'  => $transaction,
    'order'        => $transaction->order,
    'subscription' => $subscription,
    'heading'      => 'Summary',
]);

\Webmakerr\App\App::make('view')->render('emails.parts.call_to_action_box', [
    'content'     => 'To manage your subscription, download receipts, please visit the details page.',
    'link'        => $subscription->getViewUrl('customer'),
    'button_text' => 'View Details'
]);
?>
