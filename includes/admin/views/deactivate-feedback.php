<?php 
$reasons = array(
    		1 => '<li><label><input type="radio" name="mashsb_disable_reason" value="temporary"/>' . esc_html__('It is only temporary', 'mashsb') . '</label></li>',
		2 => '<li><label><input type="radio" name="mashsb_disable_reason" value="stopped showing social buttons"/>' . esc_html__('I stopped showing Social Buttons on my site', 'mashsb') . '</label></li>',
		3 => '<li><label><input type="radio" name="mashsb_disable_reason" value="missing feature"/>' . esc_html__('I miss a feature', 'mashsb') . '</label></li>
		<li><input type="text" name="mashsb_disable_text[]" value="" placeholder="Please describe the feature"/></li>',
		4 => '<li><label><input type="radio" name="mashsb_disable_reason" value="technical issue"/>' . esc_html__('Technical Issue', 'mashsb') . '</label></li>
		<li><textarea name="mashsb_disable_text[]" placeholder="' . __('Can we help? Please describe your problem', 'mashsb') . '"></textarea></li>',
		5 => '<li><label><input type="radio" name="mashsb_disable_reason" value="other plugin"/>' . esc_html__('I switched to another plugin', 'mashsb') .  '</label></li>
		<li><input type="text" name="mashsb_disable_text[]" value="" placeholder="Name of the plugin"/></li>',
		6 => '<li><label><input type="radio" name="mashsb_disable_reason" value="other"/>' . esc_html__('Other reason', 'mashsb') . '</label></li>
		<li><textarea name="mashsb_disable_text[]" placeholder="' . esc_html__('Please specify, if possible', 'mashsb') . '"></textarea></li>',
    );
shuffle($reasons);
?>


<div id="mashsb-feedback-overlay" style="display: none;">
    <div id="mashsb-feedback-content">
	<form action="" method="post">
	    <h3><strong><?php esc_html_e('If you have a moment, please let us know why you are deactivating:', 'mashsb'); ?></strong></h3>
	    <ul>
                <?php 
                foreach ($reasons as $reason){
                    echo $reason;
                }
                ?>
	    </ul>
	    <?php if ($email) : ?>
    	    <input type="hidden" name="mashsb_disable_from" value="<?php echo esc_html($email); ?>"/>
	    <?php endif; ?>
	    <input id="mashsb-feedback-submit" class="button button-primary" type="submit" name="mashsb_disable_submit" value="<?php esc_html_e('Submit & Deactivate', 'mashsb'); ?>"/>
	    <a class="button"><?php esc_html_e('Only Deactivate', 'mashsb'); ?></a>
	    <a class="mashsb-feedback-not-deactivate" href="#"><?php esc_html_e('Don\'t deactivate', 'mashsb'); ?></a>
	</form>
    </div>
</div>