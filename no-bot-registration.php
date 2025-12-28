<?php
/*
Plugin Name: No-Bot Registration
Plugin URI: https://ajdg.solutions/product/no-bot-registration/
Author: Arnan de Gans
Author URI: https://www.arnan.me/
Description: Prevent people from registering by blacklisting emails and present people with a security question when registering or posting a comment.
Version: 2.5.1
License: GPLv3

Text Domain no-bot-registration
Domain Path: /languages

Requires at least: 5.8
Requires PHP: 8.0
Requires CP: 2.0
Tested CP: 2.6
Premium URI: https://ajdg.solutions/
GooseUp: compatible
*/

/* ------------------------------------------------------------------------------------
*  COPYRIGHT NOTICE
*  Copyright 2017-2026 Arnan de Gans. All Rights Reserved.

*  COPYRIGHT NOTICES AND ALL THE COMMENTS SHOULD REMAIN INTACT.
*  By using this code you agree to indemnify Arnan de Gans from any
*  liability that might arise from its use.
------------------------------------------------------------------------------------ */

defined('ABSPATH') or die();

/*--- Load Files --------------------------------------------*/
include_once(plugin_dir_path(__FILE__).'/library/common.php');
include_once(plugin_dir_path(__FILE__).'/no-bot-registration-functions.php');
/*-----------------------------------------------------------*/

/*--- Core --------------------------------------------------*/
register_activation_hook(__FILE__, 'ajdg_nobot_activate');
register_uninstall_hook(__FILE__, 'ajdg_nobot_deactivate');
add_action('init', 'ajdg_nobot_init');
// Protect comments
add_action('comment_form_after_fields', 'ajdg_nobot_comment_field');
add_action('comment_form_logged_in_after', 'ajdg_nobot_comment_field');
add_filter('preprocess_comment', 'ajdg_nobot_check_comment');
// Protect the registration form (Including custom registration in theme)
add_action('register_form', 'ajdg_nobot_registration_field');
add_filter('registration_errors', 'ajdg_nobot_check_registration', 10, 3);
add_action('registration_errors', 'ajdg_nobot_blacklist', 11, 3);

if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
	// Protect WooCommerce My-Account page
	add_action('woocommerce_register_form', 'ajdg_nobot_woocommerce_field');
	// Protect WooCommerce Registration on checkout
	add_action('woocommerce_after_checkout_registration_form', 'ajdg_nobot_woocommerce_field');
	add_action('woocommerce_registration_errors', 'ajdg_nobot_check_woocommerce', 10 ,3);
	add_action('woocommerce_registration_errors', 'ajdg_nobot_blacklist', 11, 3);
}

if(is_admin()) {
	ajdg_nobot_check_config();
	/*--- Dashboard ---------------------------------------------*/
	add_action('admin_menu', 'ajdg_nobot_dashboard_menu');
	add_action("admin_print_styles", 'ajdg_nobot_dashboard_styles');
	add_filter('plugin_row_meta', 'ajdg_nobot_meta_links', 10, 2);
	/*--- Actions -----------------------------------------------*/
	if(isset($_POST['nobot_protection_save_options'])) add_action('init', 'ajdg_nobot_save_settings');
}
/*-----------------------------------------------------------*/

/*-------------------------------------------------------------
 Name:      ajdg_nobot_dashboard_menu
 Purpose: 	Set up dashboard menu
-------------------------------------------------------------*/
function ajdg_nobot_dashboard_menu() {
	add_management_page('No-Bot Registration &rarr; Settings', 'No-Bot Registration', 'moderate_comments', 'ajdg-nobot-settings', 'ajdg_nobot_dashboard');
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_dashboard
 Purpose: 	Admin screen and save settings
-------------------------------------------------------------*/
function ajdg_nobot_dashboard() {
	global $wp_version;

	$status = '';
	if(isset($_GET['status'])) $status = esc_attr($_GET['status']);
?>
	<div class="wrap">
		<h1><?php _e('No-Bot Registration', 'no-bot-registration'); ?></h1>

		<?php
		if($status > 0) ajdg_nobot_status($status);
		include("no-bot-registration-dashboard.php");
		?>

		<br class="clear" />
	</div>
<?php
}
?>
