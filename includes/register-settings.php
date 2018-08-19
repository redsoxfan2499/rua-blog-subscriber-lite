<?php
/**
 * Register Settings Page
 *
*/

if ( !function_exists('register_rua_settings' ) )
{
	function register_rua_settings() {

		$default_site_name = get_bloginfo('name');
		add_option('rua_site_name', $default_site_name);
		$default_site_url = get_site_url();
		add_option('rua_site_url', $default_site_url);
		add_option('rua_site_contact_form', '');
		add_option('rua_company_address', '');
		add_option('rua_company_city', '');
		add_option('rua_company_state', '');
		add_option('rua_company_zip', '');
		add_option('rua_company_phone_number', '');
		add_option('rua_from_email_address', '');
		add_option('rua_email_subject', 'Blog Subscription');
		add_option('rua_email_logo', '');
		add_option('rua_form_header', 'SUBSCRIBE VIA EMAIL');
		add_option('rua_custom_message', 'An email was just sent to confirm your subscription.
							Please check your email and click confirm to activate your subscription.');
		add_option('rua_button_text', 'SUBMIT');
		add_option('rua_blog_url', '');
		add_option('rua_recaptcha_enable', 'off');
		add_option('rua_recaptcha_site_key', '');
		add_option('rua_recaptcha_secret_key', '');
	 }
}
add_action( 'init', 'register_rua_settings' );