var strict;

jQuery(document).ready(function ($) {

    /**
     * DEACTIVATION FEEDBACK FORM
     */
    // show overlay when clicked on "deactivate"
    mashsb_deactivate_link = $('.wp-admin.plugins-php tr[data-slug="mashsharer"] .row-actions .deactivate a');
    mashsb_deactivate_link_url = mashsb_deactivate_link.attr('href');

    mashsb_deactivate_link.click(function (e) {
        e.preventDefault();

        // only show feedback form once per 30 days
        var c_value = mashsb_admin_get_cookie("mashsb_hide_deactivate_feedback");

        if (c_value === undefined) {
            $('#mashsb-feedback-overlay').show();
        } else {
            // click on the link
            window.location.href = mashsb_deactivate_link_url;
        }
    });
    // show text fields
    $('#mashsb-feedback-content input[type="radio"]').click(function () {
        // show text field if there is one
        $(this).parents('li').next('li').children('input[type="text"], textarea').show();
    });
    // send form or close it
    $('#mashsb-feedback-content .button').click(function (e) {
        e.preventDefault();
        // set cookie for 30 days
        var exdate = new Date();
        exdate.setSeconds(exdate.getSeconds() + 2592000);
        document.cookie = "mashsb_hide_deactivate_feedback=1; expires=" + exdate.toUTCString() + "; path=/";

        $('#mashsb-feedback-overlay').hide();
        if ('mashsb-feedback-submit' === this.id) {
            // Send form data
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'mashsb_send_feedback',
                    data: $('#mashsb-feedback-content form').serialize()
                },
                complete: function (MLHttpRequest, textStatus, errorThrown) {
                    // deactivate the plugin and close the popup
                    $('#mashsb-feedback-overlay').remove();
                    window.location.href = mashsb_deactivate_link_url;

                }
            });
        } else {
            $('#mashsb-feedback-overlay').remove();
            window.location.href = mashsb_deactivate_link_url;
        }
    });
    // close form without doing anything
    $('.mashsb-feedback-not-deactivate').click(function (e) {
        $('#mashsb-feedback-overlay').hide();
    });
    
    function mashsb_admin_get_cookie (name) {
	var i, x, y, mashsb_cookies = document.cookie.split( ";" );
	for (i = 0; i < mashsb_cookies.length; i++)
	{
		x = mashsb_cookies[i].substr( 0, mashsb_cookies[i].indexOf( "=" ) );
		y = mashsb_cookies[i].substr( mashsb_cookies[i].indexOf( "=" ) + 1 );
		x = x.replace( /^\s+|\s+$/g, "" );
		if (x === name)
		{
			return unescape( y );
		}
	}
}

}); // document ready