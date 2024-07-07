<?php
/*
Plugin Name: No-Bot Registration
Plugin URI: https://ajdg.solutions/product/no-bot-registration/
Author: Arnan de Gans
Author URI: https://www.arnan.me/
Description: Prevent people from registering by blacklisting emails and present people with a security question when registering or posting a comment.
Text Domain: ajdg-nobot
Version: 2.1.1
License: GPLv3
*/

/* ------------------------------------------------------------------------------------
*  COPYRIGHT NOTICE
*  Copyright 2014-2024 Arnan de Gans. All Rights Reserved.

*  COPYRIGHT NOTICES AND ALL THE COMMENTS SHOULD REMAIN INTACT.
*  By using this code you agree to indemnify Arnan de Gans from any
*  liability that might arise from its use.
------------------------------------------------------------------------------------ */

defined('ABSPATH') or die();

/*--- Load Files --------------------------------------------*/
$plugin_folder = plugin_dir_path(__FILE__);

register_activation_hook(__FILE__, 'ajdg_nobot_activate');
register_uninstall_hook(__FILE__, 'ajdg_nobot_deactivate');
load_plugin_textdomain('ajdg-nobot', false, 'no-bot-registration/language');

add_action('init', 'ajdg_nobot_init');

// Protect comments
add_action('comment_form_after_fields', 'ajdg_nobot_comment_field');
add_action('comment_form_logged_in_after', 'ajdg_nobot_comment_field');
add_filter('preprocess_comment', 'ajdg_nobot_check_comment');

// Protect the registration form (Including custom registration in theme)
add_action('register_form', 'ajdg_nobot_registration_field');
add_filter('registration_errors', 'ajdg_nobot_check_registration', 10, 3);
add_action('registration_errors', 'ajdg_nobot_blacklist', 11, 3);

if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	// Protect WooCommerce My-Account page
	add_action('woocommerce_register_form', 'ajdg_nobot_woocommerce_field');
	// Protect WooCommerce Registration on checkout
	add_action('woocommerce_after_checkout_registration_form', 'ajdg_nobot_woocommerce_field');
	add_action('woocommerce_registration_errors', 'ajdg_nobot_check_woocommerce', 10 ,3);
	add_action('woocommerce_registration_errors', 'ajdg_nobot_blacklist', 11, 3);
}

/*--- Back end ----------------------------------------------*/
if(is_admin()) {
	ajdg_nobot_check_config();
	add_action('admin_menu', 'ajdg_nobot_adminmenu');
	add_action("admin_print_styles", 'ajdg_nobot_dashboard_styles');
	add_action('admin_notices', 'ajdg_nobot_notifications_dashboard');
	add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), 'ajdg_nobot_action_links');
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_activate
 Purpose: 	Activation/setup script
 Since:		1.0
-------------------------------------------------------------*/
function ajdg_nobot_activate() {
	global $wp_version;

	add_option('ajdg_nobot_protect', array('registration' => 1, 'comment' => 1, 'woocommerce' => 0));
	add_option('ajdg_nobot_security_message', 'Please fill in the correct answer to the security question!');
	add_option('ajdg_nobot_questions', array('What is the sum of 2 and 7?'));
	add_option('ajdg_nobot_answers', array(array('nine','9')));

	add_option('ajdg_nobot_blacklist_message', 'Your email has been banned from registration! Try using another email address or contact support for a solution.');
	add_option('ajdg_nobot_blacklist_usernames', implode("\n", array('subscriber', 'editor', 'admin', 'superadmin', 'author', 'customer', 'contributor', 'administrator', 'shop manager', 'shopmanager', 'email', 'ecommerce', 'forum', 'forums', 'feedback', 'follow', 'guest', 'httpd', 'https', 'information', 'invite', 'knowledgebase', 'lists', 'webmaster', 'yourname', 'support', 'team')));
	add_option('ajdg_nobot_blacklist_protect', array('namelength' => 0, 'nameisemail' => 0, 'emailperiods' => 0, 'namespaces' => 0));

	add_option('ajdg_nobot_hide_review', current_time('timestamp'));

	if(version_compare($wp_version, '5.5.0', '>=')) {
		$blacklist = explode("\n", get_option('disallowed_keys')); // wp core option
	} else {
		$blacklist = explode("\n", get_option('blacklist_keys')); // wp core option
	}

	$blacklist = array_merge($blacklist, array('hotmail', 'yahoo', '.cn', '.info', '.biz'));
	sort($blacklist);
	$blacklist = implode("\n", array_unique($blacklist));

	if(version_compare($wp_version, '5.5.0', '>=')) {
		update_option('disallowed_keys', $blacklist);
	} else {
		update_option('blacklist_keys', $blacklist);
	}
	unset($blacklist);
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_deactivate
 Purpose: 	uninstall script
 Since:		1.0
-------------------------------------------------------------*/
function ajdg_nobot_deactivate() {
	delete_option('ajdg_nobot_protect');
	delete_option('ajdg_nobot_security_message');
	delete_option('ajdg_nobot_questions');
	delete_option('ajdg_nobot_answers');
	delete_option('ajdg_nobot_blacklist_message');
	delete_option('ajdg_nobot_blacklist_protect');
	delete_option('ajdg_nobot_blacklist_usernames');
	delete_option('ajdg_nobot_hide_review');

	delete_option('ajdg_activate_no-bot-registration'); // Obsolete
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_init
 Purpose: 	Initialize
 Since:		1.0
-------------------------------------------------------------*/
function ajdg_nobot_init() {
	wp_enqueue_script('jquery', false, false, false, true);
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_adminmenu
 Purpose: 	Set up dashboard menu
 Since:		1.0
-------------------------------------------------------------*/
function ajdg_nobot_adminmenu() {
	add_management_page('No-Bot Registration &rarr; Settings', 'No-Bot Registration', 'moderate_comments', 'ajdg-nobot-settings', 'ajdg_nobot_dashboard');
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_action_links
 Purpose:	Plugin page link
 Since:		1.0
-------------------------------------------------------------*/
function ajdg_nobot_action_links($links) {
	$links['nobot-settings'] = sprintf('<a href="%s">%s</a>', admin_url('tools.php?page=ajdg-nobot-settings'), 'Settings');
	$links['nobot-help'] = sprintf('<a href="%s" target="_blank">%s</a>', 'https://ajdg.solutions/forums/?mtm_campaign=nobot_registration', 'Support');
	$links['nobot-plugins'] = sprintf('<a href="%s" target="_blank">%s</a>', 'https://ajdg.solutions/plugins/?mtm_campaign=nobot_registration', 'More plugins');

	return $links;
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_dashboard_styles
 Purpose: 	Add security field to comment form
 Since:		1.0
-------------------------------------------------------------*/
function ajdg_nobot_dashboard_styles() {
	wp_enqueue_style('ajdg-nobot-admin-stylesheet', plugins_url('library/dashboard.css', __FILE__));
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_comment_field
 Purpose: 	Add security field to comment form
 Since:		1.0
-------------------------------------------------------------*/
function ajdg_nobot_comment_field() {
	$protect = get_option('ajdg_nobot_protect');

	if($protect['comment']) {
		ajdg_nobot_field();
	}
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_registration_field
 Purpose: 	Add security field to registration form
 Since:		1.0
-------------------------------------------------------------*/
function ajdg_nobot_registration_field() {
	$protect = get_option('ajdg_nobot_protect');

	if($protect['registration']) {
		ajdg_nobot_field();
	}
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_woocommerce_field
 Purpose: 	Add security field to WooCommerce Checkout
 Since:		1.0
-------------------------------------------------------------*/
function ajdg_nobot_woocommerce_field() {
	$protect = get_option('ajdg_nobot_protect');

	if($protect['woocommerce']) {
		ajdg_nobot_field();
	}
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_field
 Purpose: 	Format the security field and put a random question in there
 Since:		1.0
-------------------------------------------------------------*/
function ajdg_nobot_field($context = 'comment') {
	if(current_user_can('editor') OR current_user_can('administrator')) return;
	?>
	<p class="comment-form-ajdg_nobot">
		<?php
		$questions = get_option('ajdg_nobot_questions');
		$answers = get_option('ajdg_nobot_answers');
		$selected_id = rand(0, count($questions)-1);
		?>
		<label for="ajdg_nobot_answer"><?php echo htmlspecialchars($questions[$selected_id]); ?> <?php _e('(Required)', 'ajdg-nobot'); ?></label>
		<input id="ajdg_nobot_answer" name="ajdg_nobot_answer" type="text" value="" size="30"/>
		<input type="hidden" name="ajdg_nobot_id" value="<?php echo $selected_id; ?>" />
		<input type="hidden" name="ajdg_nobot_hash" value="<?php echo ajdg_nobot_security_hash($selected_id, $questions[$selected_id], $answers[$selected_id]); ?>" />
	</p>
	<div style="display:none; height:0px;">
		<p>Leave the field below empty!</p>
		<label for="captcha">Security:</label> <input type="text" name="captcha" value="" />
		<label for="captcha_confirm">Confirm:</label> <input type="text" name="captcha_confirm" value=" " />
	</div>
<?php
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_check_comment
 Purpose: 	Inject error filter and fail if errors are generated
 Since:		1.8
-------------------------------------------------------------*/
function ajdg_nobot_check_comment($commentdata) {
	if($commentdata['comment_type'] == 'pingback' OR $commentdata['comment_type'] == 'trackback') {
		return $commentdata;
	}

	$protect = get_option('ajdg_nobot_protect');

	if(!$protect['comment']) {
		return $commentdata;
	}

	$errors = new WP_Error();
	$errors = ajdg_nobot_check_fields($errors);

	if(count($errors->errors) > 0) {
		$security_message = $errors->errors[array_key_first($errors->errors)][0];
		wp_die('<p>'.$security_message.'</p><p><button onclick="history.back()">Go Back</button></p>');
	}
	unset($errors);

	return $commentdata;
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_check_registration
 Purpose: 	Check user registration for WP
 Since:		1.8.2
-------------------------------------------------------------*/
function ajdg_nobot_check_registration($errors, $user_login, $user_email) {
	$protect = get_option('ajdg_nobot_protect');

	if($protect['registration']) {
		return ajdg_nobot_check_fields($errors);
	}

	return $errors;
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_check_woocommerce
 Purpose: 	Check user registration for WC/CC
 Since:		1.8.2
-------------------------------------------------------------*/
function ajdg_nobot_check_woocommerce($errors, $user_login, $user_email) {
	$protect = get_option('ajdg_nobot_protect');

	if($protect['woocommerce']) {
		return ajdg_nobot_check_fields($errors);
	}

	return $errors;
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_check_fields
 Purpose: 	Check the given answer and respond accordingly
 Since:		1.8
-------------------------------------------------------------*/
function ajdg_nobot_check_fields($errors) {
	if(current_user_can('editor') OR current_user_can('administrator')) return $errors;

	$security_message = get_option('ajdg_nobot_security_message');
	$questions_all = get_option('ajdg_nobot_questions');
	$answers_all = get_option('ajdg_nobot_answers');

	$question_id = (array_key_exists('ajdg_nobot_id', $_POST)) ? intval(trim($_POST['ajdg_nobot_id'])) : 0;
	$question_hash = (array_key_exists('ajdg_nobot_hash', $_POST)) ? trim($_POST['ajdg_nobot_hash']) : 0;
	$user_answer = (array_key_exists('ajdg_nobot_answer', $_POST)) ? trim($_POST['ajdg_nobot_answer']) : '';
	$trap_captcha = (isset($_POST['captcha'])) ? strip_tags($_POST['captcha']) : null;
	$trap_confirm = (isset($_POST['captcha_confirm'])) ? strip_tags($_POST['captcha_confirm']) : null;

	// Empty or no answer?
	if($user_answer == '') {
	    $errors->add( 'nobot_answer_empty', $security_message );
		return $errors;
	}

	// Check trap fields
	if($trap_captcha != "" OR $trap_confirm != " ") {
	    $errors->add( 'nobot_answer_trap', '<strong>Error</strong>: Bots are not welcome!');
		return $errors;
	}

	// Hash verification to make sure the bot isn't picking on one answer. This does not mean that they got the question right.
	if($question_hash != ajdg_nobot_security_hash($question_id, $questions_all[$question_id], $answers_all[$question_id])) {
	    $errors->add( 'nobot_answer_trap2', '<strong>Error</strong>: Bots are not welcome!');
		return $errors;
	}

	// Verify the answer.
	if($question_id < count($answers_all)) {
		$answers = $answers_all[$question_id];
		foreach($answers as $answer) {
			if(strtolower(strip_tags(trim($user_answer))) == strtolower($answer)) {
				$right_answer[] = true;
			} else {
				$right_answer[] = false;
			}
		}

		if(!in_array(true, $right_answer)) {
		    $errors->add( 'nobot_answer_wrong', $security_message );
			return $errors;
		}
	}

	unset($question_id, $question_hash, $user_answer, $trap_captcha, $trap_confirm, $right_answer);

	return $errors;
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_check_config
 Purpose:   Update the options
 Since:		2.0
-------------------------------------------------------------*/
function ajdg_nobot_check_config() {
    $nobot_protect = get_option('ajdg_nobot_protect');
    $nobot_questions = get_option('ajdg_nobot_questions');
    $nobot_answers = get_option('ajdg_nobot_answers');
    $nobot_message = get_option('ajdg_nobot_security_message');

	if(!is_array($nobot_protect) OR count($nobot_protect) == 0) {
		update_option('ajdg_nobot_protect', array('registration' => 1, 'comment' => 1, 'woocommerce' => 0));
	}
	if(!is_array($nobot_questions) OR count($nobot_questions) == 0) {
		update_option('ajdg_nobot_questions', array('What is the sum of 2 and 7?'));
	}
	if(!is_array($nobot_answers) OR count($nobot_answers) == 0) {
		update_option('ajdg_nobot_answers', array(array('nine','9')));
	}
	if(strlen($nobot_message) == 0) {
		update_option('ajdg_nobot_security_message', 'Please fill in the correct answer to the security question!');
	}

    $nobot_blacklist_protect = get_option('ajdg_nobot_blacklist_protect');
    $nobot_blacklist_usernames = get_option('ajdg_nobot_blacklist_usernames');
    $nobot_blacklist_message = get_option('ajdg_nobot_blacklist_message');

	if(!is_array($nobot_blacklist_protect) OR count($nobot_blacklist_protect) == 0) {
		update_option('ajdg_nobot_blacklist_protect', array('namelength' => 0, 'nameisemail' => 0, 'emailperiods' => 0, 'namespaces' => 0));
	}
	if(!is_array($nobot_blacklist_usernames) OR count($nobot_blacklist_usernames) == 0) {
		update_option('ajdg_nobot_blacklist_usernames', implode("\n", array('subscriber', 'editor', 'admin', 'superadmin', 'author', 'customer', 'contributor', 'administrator', 'shop manager', 'shopmanager', 'email', 'ecommerce', 'forum', 'forums', 'feedback', 'follow', 'guest', 'httpd', 'https', 'information', 'invite', 'knowledgebase', 'lists', 'webmaster', 'yourname', 'support', 'team')));
	}
	if(strlen($nobot_blacklist_message) == 0) {
		update_option('ajdg_nobot_blacklist_message', 'Your email has been banned from registration! Try using another email address or contact support for a solution.');
	}
	
	unset($nobot_protect, $nobot_questions, $nobot_answers, $nobot_message, $nobot_blacklist_protect, $nobot_blacklist_usernames, $nobot_blacklist_message);
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_blacklist
 Purpose: 	Check for banned emails on registration
 Since:		1.0
-------------------------------------------------------------*/
function ajdg_nobot_blacklist($errors, $user_login, $user_email) {
 	global $wp_version;

	if(version_compare($wp_version, '5.5.0', '>=')) {
		$blacklist = get_option('disallowed_keys'); // wp core option
	} else {
		$blacklist = get_option('blacklist_keys'); // wp core option
	}
    $blacklist_usernames = get_option('ajdg_nobot_blacklist_usernames');
    $blacklist_message = get_option('ajdg_nobot_blacklist_message');
    $blacklist_protect = get_option('ajdg_nobot_blacklist_protect');

    $blacklist_array = explode("\n", $blacklist);
    $blacklist_usernames_array = explode("\n", $blacklist_usernames);

	if(count($blacklist_usernames_array) > 0) {
		if(
			($blacklist_protect['namespaces'] == 1 AND strpos($user_login, ' ') !== false) // No spaces
			OR ($blacklist_protect['namelength'] == 1 AND strlen($user_login) < 5) // No short names
			OR ($blacklist_protect['nameisemail'] == 1 AND is_email($user_login)) // Not an email address
			OR (in_array($user_login, $blacklist_usernames_array)) // Blacklist
		) {
			if(is_wp_error($errors)) {
				$errors->add('invalid_username', $blacklist_message);
			}
		}
	}

	// Check if email address has too many periods
	$user_email_parts = explode('@', $user_email);
	if($blacklist_protect['emailperiods'] == 1 AND substr_count($user_email_parts[0], '.') > 4) {
		if(is_wp_error($errors)) {
			$errors->add('invalid_email', $blacklist_message);
		}
	}

    // Go through blacklist
	if(count($blacklist_array) > 0) {
	    foreach($blacklist_array as $k => $email) {
	        if(stripos($user_email, trim($email)) !== false) {
				if(is_wp_error($errors)) {
					$errors->add('invalid_email', $blacklist_message);
				}
	        }
	    }
	}

	return $errors;
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_security_hash
 Purpose: 	Generate security hash used in question verification
 Since:		1.0
-------------------------------------------------------------*/
function ajdg_nobot_security_hash($id, $question, $answer) {
	// Hash format: SHA256( Question ID + Question Title + serialize( Question Answers ) )
	$hash_string = strval($id).strval($question).serialize($answer);

	return hash('sha256', $hash_string);
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_template
 Purpose: 	Settings questions listing
 Since:		1.0
-------------------------------------------------------------*/
function ajdg_nobot_template($id, $question, $answers) {
	$id = intval($id);
?>
	<fieldset class="ajdg_nobot_row_<?php echo $id; ?>">
		<p class="ajdg_nobot_row_<?php echo $id; ?>"><strong><?php _e('Question:', 'ajdg-nobot'); ?></strong></p>
		<p><input type="input" name="ajdg_nobot_question_<?php echo $id; ?>" size="50" style="width: 75%;" value="<?php echo htmlspecialchars($question); ?>" placeholder="<?php _e('Type here to add a new question', 'ajdg-nobot'); ?>" /> <a href="javascript:void(0)" onclick="ajdg_nobot_delete_entire_question(&quot;<?php echo $id; ?>&quot;)"><?php _e('Delete Question', 'ajdg-nobot'); ?></a></p>
	
		<?php if(count($answers) === 0 AND !empty($question)) echo "<p style=\"color: #F00;\"><strong>".__('This question has no answers! Add at least one answer or remove the question.', 'ajdg-nobot')."</strong></p>"; ?>
	
		<p><strong><?php _e('Possible Answers:', 'ajdg-nobot'); ?></strong><br /><em><?php _e('Answers are case-insensitive.', 'ajdg-nobot'); ?></em></p>
		<p>
			<?php
			$i = 0;
			foreach($answers as $value) {
				echo '<span id="ajdg_nobot_answer_'.$id.'_'.$i.'">';
				echo '<input type="input" id="ajdg_nobot_answer_'.$id.'_'.$i.'" name="ajdg_nobot_answers_'.$id.'[]" size="50" style="width: 75%;" value="'.htmlspecialchars($value).'" /> <a href="javascript:void(0)" onclick="ajdg_nobot_delete(&quot;'.$id.'&quot;, &quot;'.$i.'&quot;)">Delete Answer</a>';
				echo '</span><br />';
				$i++;
			}
			echo '<script id="ajdg_nobot_placeholder_'.$id.'">ct['.$id.'] = '.$i.';</script>';
			?>
			&nbsp;<a href="javascript:void(0)" onclick="return ajdg_nobot_add_newitem(<?php echo $id; ?>)"><?php _e('Add Possible Answer', 'ajdg-nobot'); ?></a>
		</p>
	</fieldset>
<?php
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_dashboard
 Purpose: 	Admin screen and save settings
 Since:		1.0
-------------------------------------------------------------*/
function ajdg_nobot_dashboard() {
	global $wp_version;

	if(!current_user_can('moderate_comments')) return;

	if(isset($_POST['nobot_protection']) AND wp_verify_nonce($_POST['ajdg_nobot_nonce'], 'ajdg_nobot_protection')) {
		$questions = $answers = $protect = array();

		$protect['registration'] = (isset($_POST['ajdg_nobot_registration'])) ? 1 : 0;
		$protect['comment'] = (isset($_POST['ajdg_nobot_comment'])) ? 1 : 0;
		$protect['woocommerce'] = (isset($_POST['ajdg_nobot_woocommerce'])) ? 1 : 0;

		foreach($_POST as $key => $value) {
			if(strpos($key, 'ajdg_nobot_question_') === 0) {
				// value starts with ajdg_nobot_question_ (form field name)
				$q_id = str_replace('ajdg_nobot_question_', '', $key);
				if(trim(strval($value)) != '') { // if not empty
					$question_slashed = trim(strval($value));
					// WordPress seems to add quotes by default:
					$questions[] = stripslashes($question_slashed);
					if(isset($_POST['ajdg_nobot_answers_' . $q_id])) {
						$answers_slashed = array_filter($_POST['ajdg_nobot_answers_' . $q_id]);
						foreach($answers_slashed as $key => $value) {
							$answers_slashed[$key] = stripslashes($value);
						}
						$answers[] = $answers_slashed;
					}
				}
			}
		}

		update_option('ajdg_nobot_protect', $protect);
		update_option('ajdg_nobot_questions', $questions);
		update_option('ajdg_nobot_answers', $answers);

		if(isset($_POST['ajdg_nobot_security_message'])) {
			update_option('ajdg_nobot_security_message', sanitize_text_field($_POST['ajdg_nobot_security_message']));
		}

		add_settings_error('ajdg_nobot', 'ajdg_nobot_updated', 'Settings updated.', 'updated');
	}

	if(isset($_POST['nobot_blacklist']) AND wp_verify_nonce($_POST['ajdg_nobot_nonce'], 'ajdg_nobot_blacklist')) {
		if(isset($_POST['ajdg_nobot_blacklist_message'])) {
			update_option('ajdg_nobot_blacklist_message', sanitize_text_field($_POST['ajdg_nobot_blacklist_message']));
		}

		if(isset($_POST['ajdg_nobot_blacklist'])) {
			$blacklist_new_keys = strip_tags(htmlspecialchars($_POST['ajdg_nobot_blacklist'], ENT_QUOTES));
			$blacklist_array = explode("\n", $blacklist_new_keys);
			sort($blacklist_array);

			if(version_compare($wp_version, '5.5.0', '>=')) {
				update_option('disallowed_keys', implode("\n", array_unique($blacklist_array)));
			} else {
				update_option('blacklist_keys', implode("\n", array_unique($blacklist_array)));
			}
		}

		if(isset($_POST['ajdg_nobot_blacklist_usernames'])) {
			$blacklist_new_usernames = strip_tags(htmlspecialchars($_POST['ajdg_nobot_blacklist_usernames'], ENT_QUOTES));
			$blacklist_usernames_array = explode("\n", $blacklist_new_usernames);
			sort($blacklist_usernames_array);
			update_option('ajdg_nobot_blacklist_usernames', implode("\n", array_unique($blacklist_usernames_array)));
		}

		$blacklist_protect['namespaces'] = (isset($_POST['ajdg_nobot_allow_namespaces'])) ? 1 : 0;
		$blacklist_protect['namelength'] = (isset($_POST['ajdg_nobot_allow_namelength'])) ? 1 : 0;
		$blacklist_protect['nameisemail'] = (isset($_POST['ajdg_nobot_allow_nameisemail'])) ? 1 : 0;
		$blacklist_protect['emailperiods'] = (isset($_POST['ajdg_nobot_allow_emailperiods'])) ? 1 : 0;
		update_option('ajdg_nobot_blacklist_protect', $blacklist_protect);
		
		add_settings_error('ajdg_nobot', 'ajdg_nobot_updated', __('Settings updated.', 'ajdg-nobot'), __('updated', 'ajdg-nobot'));
	}

	$ajdg_nobot_protect = get_option('ajdg_nobot_protect', array());
	$ajdg_nobot_questions = get_option('ajdg_nobot_questions', array());
	$ajdg_nobot_answers = get_option('ajdg_nobot_answers', array());
	if(version_compare($wp_version, '5.5.0', '>=')) {
	    $ajdg_nobot_blacklist = get_option('disallowed_keys'); // WP Core
	} else {
	    $ajdg_nobot_blacklist = get_option('blacklist_keys'); // WP Core
	}
    $ajdg_nobot_blacklist_usernames = get_option('ajdg_nobot_blacklist_usernames');
	$ajdg_nobot_blacklist_protect = get_option('ajdg_nobot_blacklist_protect');

    $ajdg_nobot_blacklist_message = get_option('ajdg_nobot_blacklist_message');
    $ajdg_nobot_security_message = get_option('ajdg_nobot_security_message');
	?>

	<div class="wrap">
		<h2><?php _e('No-Bot Registration settings', 'ajdg-nobot'); ?></h2>
		<?php settings_errors(); ?>

		<div id="dashboard-widgets-wrap">
			<div id="dashboard-widgets" class="metabox-holder">
				<div id="left-column" class="ajdg-postbox-container">

					<div class="ajdg-postbox">
						<h2 class="ajdg-postbox-title"><?php _e('No-Bot Registration', 'ajdg-nobot'); ?></h2>
						<div id="stats" class="ajdg-postbox-content">
							<p><strong><?php _e('Get help with No-Bot Registration', 'ajdg-nobot'); ?></strong></p>
							<p><?php _e('If you have any questions about using No-Bot Registration please post it on my support forum. Always happy to help!', 'ajdg-nobot'); ?></p>

							<p><a class="button-primary" href="https://ajdg.solutions/forums/forum/no-bot-registration/?mtm_campaign=nobot_registration" target="_blank" title="<?php _e('AJdG Solutions support forum', 'ajdg-nobot'); ?>"><?php _e('AJdG Solutions support forum', 'ajdg-nobot'); ?></a> <a class="button-secondary" href="https://wordpress.org/support/plugin/no-bot-registration/" target="_blank" title="<?php _e('Forum on wordpress.org', 'ajdg-nobot'); ?>"><?php _e('Forum on wordpress.org', 'ajdg-nobot'); ?></a></p>

							<p><strong><?php _e('Support No-Bot Registration', 'ajdg-nobot'); ?></strong></p>
							<p><?php _e('Consider writing a review or making a donation if you like the plugin or if you find the plugin useful. Thanks for your support!', 'ajdg-nobot'); ?></p>

							<p><a class="button-primary" href="https://www.arnan.me/donate.html?mtm_campaign=nobot_registration" target="_blank" title="<?php _e('Support me with a token of thanks', 'ajdg-nobot'); ?>"><?php _e('Gift a token of thanks', 'ajdg-nobot'); ?></a> <a class="button-secondary" href="https://wordpress.org/support/plugin/no-bot-registration/reviews?rate=5#postform" target="_blank" title="<?php _e('Write review on wordpress.org', 'ajdg-nobot'); ?>"><?php _e('Write review on wordpress.org', 'ajdg-nobot'); ?></a></p>

							<p><strong><?php _e('Plugins and services', 'ajdg-nobot'); ?></strong></p>
							<table width="100%">
								<tr>
									<td width="33%">
										<div class="ajdg-sales-widget" style="display: inline-block; margin-right:2%;">
											<a href="https://ajdg.solutions/product/adrotate-pro-single/?mtm_campaign=nobot_registration" target="_blank"><div class="header"><img src="<?php echo plugins_url("/images/offers/monetize-your-site.jpg", __FILE__); ?>" alt="AdRotate Professional" width="228" height="120"></div></a>
											<a href="https://ajdg.solutions/product/adrotate-pro-single/?mtm_campaign=nobot_registration" target="_blank"><div class="title"><?php _e('AdRotate Professional', 'ajdg-nobot'); ?></div></a>
											<div class="sub_title"><?php _e('WordPress Plugin', 'ajdg-nobot'); ?></div>
											<div class="cta"><a role="button" class="cta_button" href="https://ajdg.solutions/product/adrotate-pro-single/?mtm_campaign=nobot_registration" target="_blank">Starting at &euro; 39,-</a></div>
											<hr>
											<div class="description"><?php _e('Place Adsense Ads and any other kind of advert on your WordPress and ClassicPress website.', 'ajdg-nobot'); ?></div>
										</div>
									</td>
									<td width="33%">
										<div class="ajdg-sales-widget" style="display: inline-block; margin-right:2%;">
											<a href="https://ajdg.solutions/product/wordpress-maintenance-and-updates/?mtm_campaign=nobot_registration" target="_blank"><div class="header"><img src="<?php echo plugins_url("/images/offers/wordpress-maintenance.jpg", __FILE__); ?>" alt="WordPress Maintenance" width="228" height="120"></div></a>
											<a href="https://ajdg.solutions/product/wordpress-maintenance-and-updates/?mtm_campaign=nobot_registration" target="_blank"><div class="title"><?php _e('WP Maintenance', 'ajdg-nobot'); ?></div></a>
											<div class="sub_title"><?php _e('Professional service', 'ajdg-nobot'); ?></div>
											<div class="cta"><a role="button" class="cta_button" href="https://ajdg.solutions/product/wordpress-maintenance-and-updates/?mtm_campaign=nobot_registration" target="_blank">Starting at &euro; 22,50</a></div>
											<hr>
											<div class="description"><?php _e('Get all the latest updates for WordPress and plugins. Maintenance, delete spam and clean up files.', 'ajdg-nobot'); ?></div>
										</div>
									</td>
									<td>
										<div class="ajdg-sales-widget" style="display: inline-block;">
											<a href="https://ajdg.solutions/plugins/?mtm_campaign=nobot_registration" target="_blank"><div class="header"><img src="<?php echo plugins_url("/images/offers/more-plugins.jpg", __FILE__); ?>" alt="AJdG Solutions Plugins" width="228" height="120"></div></a>
											<a href="https://ajdg.solutions/plugins/?mtm_campaign=nobot_registration" target="_blank"><div class="title"><?php _e('All my plugins', 'ajdg-nobot'); ?></div></a>
											<div class="sub_title"><?php _e('WordPress and ClassicPress', 'ajdg-nobot'); ?></div>
											<div class="cta"><a role="button" class="cta_button" href="https://ajdg.solutions/plugins/?mtm_campaign=nobot_registration" target="_blank">View now</a></div>
											<hr>
											<div class="description"><?php _e('Plugins for WordPres, ClassicPress, WooCommerce and bbPress.', 'ajdg-nobot'); ?></div>
										</div>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<div class="ajdg-postbox">
						<h2 class="ajdg-postbox-title"><?php _e('Registration protection', 'ajdg-nobot'); ?></h2>
						<div id="nobot" class="ajdg-postbox-content">

							<form method="post" name="ajdg_nobot_protection">
								<?php wp_nonce_field('ajdg_nobot_protection','ajdg_nobot_nonce'); ?>
								<?php settings_fields('ajdg_nobot_question'); ?>

								<p><strong><?php _e('Where to add security questions?', 'ajdg-nobot'); ?></strong></p>
								<p><input type="checkbox" name="ajdg_nobot_registration" value="1" <?php if($ajdg_nobot_protect['registration']) echo 'checked="checked"' ?> /> <?php _e('Protect user registration.', 'ajdg-nobot'); ?><br /><em><?php _e('Has no effect if user registration is disabled.', 'ajdg-nobot'); ?></em></p>


								<p><input type="checkbox" name="ajdg_nobot_comment" value="1" <?php if($ajdg_nobot_protect['comment']) echo 'checked="checked"' ?> /> <?php _e('Protect blog comments.', 'ajdg-nobot'); ?><br /><em><?php _e('Has no effect if comments on posts are not enabled.', 'ajdg-nobot'); ?></em></p>

								<p><input type="checkbox" name="ajdg_nobot_woocommerce" value="1" <?php if($ajdg_nobot_protect['woocommerce']) echo 'checked="checked"' ?> /> <?php _e('Protect WooCommerce checkout pages.', 'ajdg-nobot'); ?><br /><em><?php _e('If user registration is enabled. Has no effect if WooCommerce is not installed.', 'ajdg-nobot'); ?></em></p>

								<p><strong><?php _e('Failure message:', 'ajdg-nobot'); ?></strong></p>
								<p><textarea name='ajdg_nobot_security_message' cols='70' rows='2' style="width: 100%;"><?php echo stripslashes($ajdg_nobot_security_message); ?></textarea><br /><em><?php _e('Displayed to those who fail the security question. Keep it short and simple.', 'ajdg-nobot'); ?></em></p>

								<script type="text/javascript">
								var ct = Array();
								function ajdg_nobot_delete(id, x) {
									jQuery("#ajdg_nobot_answer_" + id + "_" + x).remove();
								}

								function ajdg_nobot_delete_entire_question(id) {
									jQuery("fieldset.ajdg_nobot_row_" + id).remove();
								}

								function ajdg_nobot_add_newitem(id) {
									jQuery("#ajdg_nobot_placeholder_" + id).before("<span id=\"ajdg_nobot_line_" + id + "_" + ct[id] + "\"><input type=\"input\" id=\"ajdg_nobot_answer_" + id + "_" + ct + "\" name=\"ajdg_nobot_answers_" + id + "[]\" size=\"50\" style=\"width: 75%;\" value=\"\" placeholder=\"<?php _e('Enter a new answer here', 'ajdg-nobot'); ?>\" /> <a href=\"javascript:void(0)\" onclick=\"ajdg_nobot_delete(&quot;" + id + "&quot;, &quot;" + ct[id] + "&quot;)\">Delete</a><br /></span>");
									ct[id]++;
									return false;
								}
								</script>

								<?php
								foreach($ajdg_nobot_questions as $key => $question) {
									// Question has no answers...
									if(!isset($ajdg_nobot_answers[$key])) $ajdg_nobot_answers[$key] = array();
									
									// Add form to create/edit questions and answers
									ajdg_nobot_template($key, $question, $ajdg_nobot_answers[$key]);
								}
								ajdg_nobot_template(array_key_last($ajdg_nobot_questions)+1, '', Array());
								?>

								<?php submit_button(__('Save Changes', 'ajdg-nobot'), 'primary large', 'nobot_protection'); ?>
							</form>

						</div>
					</div>

				</div>
				<div id="right-column" class="ajdg-postbox-container">

					<div class="ajdg-postbox">
						<h2 class="ajdg-postbox-title"><?php _e('Blacklisted e-mail domains and usernames', 'ajdg-nobot'); ?></h2>
						<div id="nobot" class="ajdg-postbox-content">

							<form method="post" name="ajdg_nobot_blacklist">
								<?php wp_nonce_field('ajdg_nobot_blacklist','ajdg_nobot_nonce'); ?>
								<p><em><?php _e('If you get many fake accounts or paid robots registering you can blacklist their usernames, email addresses or domains to prevent them from adding multiple accounts.', 'ajdg-nobot'); ?></em></p>

								<p><strong><?php _e('Blacklist message:', 'ajdg-nobot'); ?></strong></p>
								<p><textarea name='ajdg_nobot_blacklist_message' cols='70' rows='2' style="width: 100%"><?php echo stripslashes($ajdg_nobot_blacklist_message); ?></textarea><br /><em><?php _e('This message is shown to users who are not allowed to register on your site. Keep it short and simple.', 'ajdg-nobot'); ?></em></p>

								<p><strong><?php _e('Blacklisted emails:', 'ajdg-nobot'); ?></strong></p>
								<p><textarea name='ajdg_nobot_blacklist' cols='70' rows='10' style="width: 100%"><?php echo stripslashes($ajdg_nobot_blacklist); ?></textarea><br /><?php _e('You can add: full emails (someone@hotmail.com), domains (hotmail.com) or simply a keyword (hotmail).', 'ajdg-nobot'); ?> <?php _e('One item per line! Add as many items as you need.', 'ajdg-nobot'); ?><br /><em><strong><?php _e('Caution:', 'ajdg-nobot'); ?></strong> <?php _e('This is a powerful filter matching partial words. So banning "mail" will also block Gmail users!', 'ajdg-nobot'); ?></em></p>

								<p><strong><?php _e('Blacklisted usernames:', 'ajdg-nobot'); ?></strong></p>
								<p><textarea name='ajdg_nobot_blacklist_usernames' cols='70' rows='10' style="width: 100%"><?php echo stripslashes($ajdg_nobot_blacklist_usernames); ?></textarea><br /><?php _e('One item per line! Add as many as you need.', 'ajdg-nobot'); ?><br /><em><strong><?php _e('Caution:', 'ajdg-nobot'); ?></strong> <?php _e('This is a powerful filter matching partial words. So banning "web" will also block webmaster!', 'ajdg-nobot'); ?></em></p>

								<p><strong><?php _e('Need more protection against fake accounts?', 'ajdg-nobot'); ?></strong></p>
								<p><?php _e('Add a few restrictions on how usernames are formatted. This helps against automated bots and manual entry fake accounts.', 'ajdg-nobot'); ?></p>
								<p><input type="checkbox" name="ajdg_nobot_allow_namespaces" value="1" <?php if($ajdg_nobot_blacklist_protect['namespaces']) echo 'checked="checked"' ?> /> <?php _e('Disallow spaces in usernames?', 'ajdg-nobot'); ?></p>
								<p><input type="checkbox" name="ajdg_nobot_allow_namelength" value="1" <?php if($ajdg_nobot_blacklist_protect['namelength']) echo 'checked="checked"' ?> /> <?php _e('Disallow usernames that are shorter than 5 characters?', 'ajdg-nobot'); ?></p>
								<p><input type="checkbox" name="ajdg_nobot_allow_nameisemail" value="1" <?php if($ajdg_nobot_blacklist_protect['nameisemail']) echo 'checked="checked"' ?> /> <?php _e('Disallow usernames to be an email address?', 'ajdg-nobot'); ?><br /><em><?php _e('Use with caution, this may conflict with some plugins like WooCommerce which DO allow email addresses as usernames.', 'ajdg-nobot'); ?></em></p>
								<p><input type="checkbox" name="ajdg_nobot_allow_emailperiods" value="1" <?php if($ajdg_nobot_blacklist_protect['emailperiods']) echo 'checked="checked"' ?> /> <?php _e('Disallow more than 4 periods in email addresses?', 'ajdg-nobot'); ?><br /><em><?php _e('A common trick is to break up words or names in email addresses with a lot of p.er.i.od.s@example.com.', 'ajdg-nobot'); ?></em></p>
										
								<?php submit_button(__('Save Changes', 'ajdg-nobot'), 'primary large', 'nobot_blacklist'); ?>
							</form>

						</div>
					</div>

					<div class="ajdg-postbox">
						<h2 class="ajdg-postbox-title"><?php _e('News & Updates', 'ajdg-nobot'); ?></h2>
						<div id="news" class="ajdg-postbox-content">
							<p><a href="http://ajdg.solutions/feed/" target="_blank" title="Subscribe to the AJdG Solutions RSS feed!" class="button-primary"><i class="icn-rss"></i><?php _e('Subscribe via RSS feed', 'ajdg-nobot'); ?></a> <em><?php _e('No account required!', 'ajdg-nobot'); ?></em></p>

							<?php wp_widget_rss_output(array(
								'url' => 'http://ajdg.solutions/feed/',
								'items' => 5,
								'show_summary' => 1,
								'show_author' => 0,
								'show_date' => 1)
							); ?>
						</div>
					</div>

				</div>
			</div>
		</div>

	</div>
<?php
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_notifications_dashboard
 Since:		1.1
-------------------------------------------------------------*/
function ajdg_nobot_notifications_dashboard() {
	global $current_user;

	if(isset($_GET['hide'])) {
		if($_GET['hide'] == 1) update_option('ajdg_nobot_hide_review', 1);
	}

	$displayname = (strlen($current_user->user_firstname) > 0) ? $current_user->user_firstname : $current_user->display_name;
	$review_banner = get_option('ajdg_nobot_hide_review');
	if($review_banner != 1 AND $review_banner < (current_time('timestamp') - 2419200)) {
		echo '<div class="ajdg-notification notice" style="">';
		echo '	<div class="ajdg-notification-logo" style="background-image: url(\''.plugins_url('/images/notification.png', __FILE__).'\');"><span></span></div>';
		echo '	<div class="ajdg-notification-message">Welcome back <strong>'.$displayname.'</strong>! If you like <strong>No-Bot Registration</strong> let the world know that you do. Thanks for your support!.<br />If you have questions, complaints or something else that does not belong in a review, please use the <a href="https://ajdg.solutions/forums/forum/no-bot-registration/">support forum</a>!</div>';
		echo '	<div class="ajdg-notification-cta">';
		echo '		<a href="https://wordpress.org/support/plugin/no-bot-registration/reviews/?rate=5#postform" class="ajdg-notification-act button-primary">Write Review</a>';
		echo '		<a href="tools.php?page=ajdg-nobot-settings&hide=1" class="ajdg-notification-dismiss">Maybe later</a>';
		echo '	</div>';
		echo '</div>';
	}
}
?>
