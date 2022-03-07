<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

$post = 'https://www.mashshare.net/?mashsb_action=checkin';
if (MASHSB_DEBUG){
  $post = 'https://src.wordpress-develop.dev/?mashsb_action=checkin';  
}

// Get the current user data
$user = wp_get_current_user();

?>

<div id="mashsb-sidebar">

	<a class="mashsb-banner" target="_blank" rel="noopener" href="https://www.mashshare.net/pricing/?utm_source=insideplugin&utm_medium=userwebsite&utm_content=sidebar&utm_campaign=freeplugin"><img src="<?php echo MASHSB_PLUGIN_URL . 'assets/images/upgrade_to_pro.png'; ?>" width="300" height="250" alt="<?php _e( 'Increase your Shares and Social Traffic', 'mashsb' ); ?>" /></a>

	<form method="post" action="<?php echo $post; ?>" target="_blank" rel="noopener" class="subscribe block" style="display:none;">
		<h2><?php _e( 'Get More Traffic', 'mashsb' ); ?></h2>

		<?php $user = wp_get_current_user(); ?>

		<p class="interesting">
			<?php echo wptexturize( __( "Submit your name and email and we'll send you tips and tricks how to get more traffic by using MashShare", 'mashsb' ) ); ?>
		</p>

		<div class="field">
			<input type="email" name="email" value="<?php echo esc_attr( $user->user_email ); ?>" placeholder="<?php _e( 'Your Email', 'mashsb' ); ?>"/>
		</div>

		<div class="field">
			<input type="text" name="firstname" value="<?php echo esc_attr( trim( $user->user_firstname ) ); ?>" placeholder="<?php _e( 'First Name', 'mashsb' ); ?>"/>
		</div>

		<div class="field">
			<input type="text" name="lastname" value="<?php echo esc_attr( trim( $user->user_lastname ) ); ?>" placeholder="<?php _e( 'Last Name', 'mashsb' ); ?>"/>
		</div>

		<input type="hidden" name="campaigns[]" value="4" />
		<input type="hidden" name="source" value="8" />

		<div class="field submit-button">
			<input type="submit" class="button" value="<?php _e( 'Send me the free stuff', 'mashsb' ); ?>"/>
		</div>

		<p class="promise">
			<?php _e( 'Your email will not be used for anything else and you can unsubscribe with 1-click anytime.', 'mashsb' ); ?>
		</p>
                <p style="text-align: center;margin-top:25px;"><?php echo sprintf(__( '<a href="%s" target="_new" style="font-weight:bold;color:#00adff;border: 1px solid #00adff;padding:6px;">Visit Our Affiliate Program', 'mashsb'), 'https://www.mashshare.net/become-partner/?utm_source=mashsbadmin&utm_medium=website&utm_campaign=see_our_affiliate_program' ); ?></a></p>
                
                
	</form>

	<div class="block testimonial">
		<p class="stars">
			<span class="dashicons dashicons-star-filled"></span>
			<span class="dashicons dashicons-star-filled"></span>
			<span class="dashicons dashicons-star-filled"></span>
			<span class="dashicons dashicons-star-filled"></span>
			<span class="dashicons dashicons-star-filled"></span>
		</p>

		<p class="quote">
			&#8220;Really happy with @Mashshare. This brilliant WordPress plugin helped increase @iCulture shares by 30%. Highly recommended.&#8221;
		</p>

		<p class="author">&mdash; Jean-Paul Horn</p>

		<p class="via"><a target="_blank" rel="noopener" href="https://twitter.com/JeanPaulH/status/726084101145550850">via Twitter</a></p>
	</div>
</div>