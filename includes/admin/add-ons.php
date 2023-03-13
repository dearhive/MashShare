<?php
/**
 * Admin Add-ons
 *
 * @package     MASHSB
 * @subpackage  Admin/Add-ons
 * @copyright   Copyright (c) 2014, Rene Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add-ons
 *
 * Renders the add-ons content.
 *
 * @since 1.1.8
 * @return void
 */
function mashsb_add_ons_page() {
	ob_start(); ?>
	<div class="wrap" id="mashsb-add-ons">
		<h2>
			<?php esc_html_e( 'Add-Ons for MashShare', 'mashsb' ); ?>
			&nbsp;&mdash;&nbsp;<a href="https://www.mashshare.net" class="button-primary" title="<?php esc_html_e( 'Visit Website', 'mashsb' ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'See Details', 'mashsb' ); ?></a>
		</h2>
		<p><?php esc_html_e( 'These add-ons extend the functionality of MashShare.', 'mashsb' ); ?></p>
		<?php echo mashsbGetAddOns(); ?>
	</div>
	<?php
	echo ob_get_clean();
}

/**
 * @return string
 */
function mashsbGetAddOns(){
	return <<< EOT
<div class="mashshare-addons">
<h3 class="mashshare-addons-title">Add more social networks</h3>
<a title="MashShare Networks Add-On" href="https://www.mashshare.net/downloads/mashshare-social-networks-addon/?ref=1" target="_blank"><img class="alignnone wp-image-107 size-full" src="https://www.mashshare.net/wp-content/uploads/2014/03/mash-networks-e1404756641988.png" alt="MashShare More Networks" width="300" height="200" /></a>Extend MashShare: Whatsapp, Pinterest, Digg, Linkedin, Reddit, Vk,Print, Buffer, Weibo, Pocket, Xing, Tumblr …<a class="button-secondary" title="MashShare Networks Add-On" href="https://www.mashshare.net/downloads/mashshare-social-networks-addon/?ref=1" target="_blank">Get this Add On</a>
</div>

<div class="mashshare-addons">
<h3 class="mashshare-addons-title">VideoPost Popup</h3>
<a title="VideoPost Popup Add-On" href="https://www.mashshare.net/downloads/videopost-popup/?ref=1" target="_blank"><img class="alignnone wp-image-107 size-full" src="https://www.mashshare.net/wp-content/uploads/edd/2015/03/videopost1.png" alt="VideoPost Popup" width="300" height="200" /></a>Share and Like Add-On for Facebook and Twitter which shows an Popup after end of YouTube Video play or read of a story. <a class="button-secondary" title="MashShare Networks Add-On" href="https://www.mashshare.net/downloads/videopost-popup/?ref=1" target="_blank">Get this Add On</a>

</div>
<div class="mashshare-addons">
<h3 class="mashshare-addons-title">Floating Sidebar</h3>
<a title="Select and Share Popup Add-On" href="https://www.mashshare.net/downloads/floating-sidebar/?ref=1" target="_blank"><img class="alignnone wp-image-107 size-full" src="https://www.mashshare.net/wp-content/uploads/edd/2016/01/floating.png" alt="Floating Sidebar" width="300" height="200" /></a>Grow your social traffic with this beautiful and powerful sidebar Add-on.<a class="button-secondary" title="MashShare Networks Add-On" href="https://www.mashshare.net/downloads/floating-sidebar/?ref=1" target="_blank">Get this Add On</a>

</div>
<div class="mashshare-addons">
<h3 class="mashshare-addons-title">Sticky ShareBar</h3>
<a title="MashShare Networks Add-On" href="https://www.mashshare.net/downloads/sticky-sharebar/?ref=1" target="_blank"><img class="alignnone wp-image-107 size-full" src="https://www.mashshare.net/wp-content/uploads/edd/2014/10/sharebar.png" alt="Sticky ShareBar" width="300" height="200" /></a>
Full responsive all time visible ShareBar on top or bottom of your page which slides down after scrolling.<a class="button-secondary" title="Sticky ShareBar" href="https://www.mashshare.net/downloads/sticky-sharebar/?ref=1" target="_blank">Get this Add On</a>

</div>
<div class="mashshare-addons">
<h3 class="mashshare-addons-title">Click To Tweet</h3>
<a title="MashShare Networks Add-On" href="https://www.mashshare.net/downloads/click-to-tweet/?ref=1" target="_blank"><img class="alignnone wp-image-107 size-full" src="https://www.mashshare.net/wp-content/uploads/edd/2016/09/product-image-click-to-tweet-300x200.png" alt="Sticky ShareBar" width="300" height="200" /></a>
Create beautiful twitter quotes that drive traffic to your site. Use any text from your post editor and create a twitter quote in seconds.<a class="button-secondary" title="Sticky ShareBar" href="https://www.mashshare.net/downloads/click-to-tweet/?ref=1" target="_blank">Get this Add On</a>

</div>
<div class="mashshare-addons">
<h3 class="mashshare-addons-title">Realtime Pageview Counter</h3>
<a title="MashShare Networks Add-On" href="https://www.mashshare.net/downloads/mashshare-pageviews/" target="_blank"><img class="alignnone wp-image-6713 size-medium" src="https://www.mashshare.net/wp-content/uploads/edd/2014/10/pageviews-300x187.png" alt="Pageviews Realtime Counter" width="300" height="187" />
</a>Realtime Pageview Counter - Increase user interaction and interest with this ajax based Pageview Counter.<a class="button-secondary" title="Pageview Counter" href="https://www.mashshare.net/downloads/mashshare-pageviews/?ref=1" target="_blank">Get this Add On</a>

</div>
<div class="mashshare-addons">
<h3 class="mashshare-addons-title">Sticky Facebook LikeBar</h3>
<a title="MashShare Networks Add-On" href="https://www.mashshare.net/downloads/facebook-like-bar?ref=1" target="_blank"><img class="alignnone wp-image-107 size-full" src="https://www.mashshare.net/wp-content/uploads/edd/2015/01/fblike.png" alt="MashShare Sticky facebook like bar" width="300" height="200" /></a>
A highly customizable, full responsive and mobile optimized sticky Like-Bar. <a class="button-secondary" title="MashShare Like-Bar" href="https://www.mashshare.net/downloads/facebook-like-bar?ref=1" target="_blank">Get this Add On</a>

</div>
<div class="mashshare-addons">
<h3 class="mashshare-addons-title">Select and Share</h3>
<a title="Select and Share Popup Add-On" href="https://www.mashshare.net/downloads/select-and-share/?ref=1" target="_blank"><img class="alignnone wp-image-107 size-full" src="https://www.mashshare.net/wp-content/uploads/edd/2015/10/select-and-share.png" alt="VideoPost Popup" width="300" height="200" /></a>Share any text selection via twitter, facebook and mail. Increase sharing of your valuable content. <a class="button-secondary" title="MashShare Networks Add-On" href="https://www.mashshare.net/downloads/select-and-share/?ref=1" target="_blank">Get this Add On</a>

</div>
<div class="mashshare-addons">
<h3 class="mashshare-addons-title">Google Analytics Integration</h3>
<a title="Google Analytics Integration" href="https://www.mashshare.net/downloads/google-analytics-integration/" target="_blank"><img class="alignnone wp-image-4273 size-full" src="https://www.mashshare.net/wp-content/uploads/edd/2014/09/google-analytics.png" alt="" width="320" height="200" />
</a>Track and count clicks on all Share Buttons within your Google Analytics account in realtime. <a title="Google Analytics Integration" class="button-secondary" href="https://www.mashshare.net/downloads/google-analytics-integration/" target="_blank">Get this Add On</a>

</div>
<div class="mashshare-addons">
<h3 class="mashshare-addons-title">Add responsive style</h3>
<a href="https://www.mashshare.net/downloads/mashshare-responsive/?ref=1" target="_blank"><img class="alignnone wp-image-494 size-full" src="https://www.mashshare.net/wp-content/uploads/2014/03/mashshare-responsive.png" alt="mashshare-responsive" width="320" height="200" /></a>
Add responsive style to MashShare. <a class="button-secondary" title="MashShare responsive Add-On" href="https://www.mashshare.net/downloads/mashshare-responsive/?ref=1" target="_blank">Get this Add On</a>

</div>
<div class="mashshare-addons">
<h3 class="mashshare-addons-title">ShortURLs Integration</h3>
<a href="https://www.mashshare.net/downloads/shorturls-integration/" target="_blank"><img class="alignnone wp-image-5207 size-medium" src="https://www.mashshare.net/wp-content/uploads/edd/2014/10/shorturls-300x187.png" alt="shorturls" width="300" height="187" /></a>
Add shortURLs for Twitter. <a class="button-secondary" title="MashShare Shorturls Add-On" href="https://www.mashshare.net/downloads/shorturls-integration/?ref=1" target="_blank">Get this Add On</a>

</div>
<div class="mashshare-addons">
<h3 class="mashshare-addons-title">Like Pop-Up after Share</h3>
<a href="https://www.mashshare.net/downloads/mashshare-likeaftershare/?ref=1" target="_blank"><img class="alignnone wp-image-107 size-full" src="https://www.mashshare.net/wp-content/uploads/edd/2014/08/likeaftershare1.jpg" alt="mash-networks" width="300" height="200" /></a>
Share a post and get a Facebook Like Pop-Up overlay. <a class="button-secondary" title="MashShare LikeAfterShare Add-On" href="https://www.mashshare.net/downloads/mashshare-likeaftershare/?ref=1" target="_blank">Get this Add On</a>

</div>
<div class="mashshare-addons">
<h3 class="mashshare-addons-title">Performance Widget</h3>
<a title="MashShare Performance Widget Add-On" href="https://www.mashshare.net/downloads/performance-widget?ref=1" target="_blank"><img class="alignnone wp-image-107 size-full" src="https://www.mashshare.net/wp-content/uploads/edd/2015/07/performance.png" alt="MashShare Performance Widget Add-On" width="300" height="200" /></a>
Shares, comments and real time reads at a glance. Give your website a great visual social impact. <a class="button-secondary" title="MashShare Performance Widget Add-On" href="https://www.mashshare.net/downloads/performance-widget?ref=1" target="_blank">Get this Add On</a>

</div>
<div class="mashshare-addons">
<h3 class="mashshare-addons-title">MashShare Open Graph</h3>
<a href="https://www.mashshare.net/downloads/mashshare-open-graph/" target="_blank"><img class="alignnone wp-image-4472 size-full" src="https://www.mashshare.net/wp-content/uploads/edd/2014/09/open-graph.png" alt="" width="320" height="200" /></a>
Share your content in the best possible way. Social sharing optimization with Open Graph implementation.
<a class="button-secondary" title="MashShare Open Graph" href="https://www.mashshare.net/downloads/mashshare-open-graph/" target="_blank">Get this Add On</a>

</div>
EOT;
}