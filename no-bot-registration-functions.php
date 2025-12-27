<?php
/* ------------------------------------------------------------------------------------
*  COPYRIGHT NOTICE
*  Copyright 2017-2026 Arnan de Gans. All Rights Reserved.

*  COPYRIGHT NOTICES AND ALL THE COMMENTS SHOULD REMAIN INTACT.
*  By using this code you agree to indemnify Arnan de Gans from any
*  liability that might arise from its use.
------------------------------------------------------------------------------------ */

defined('ABSPATH') or die();

/*-------------------------------------------------------------
 Name:      ajdg_nobot_activate
 Purpose: 	Activation/setup script
-------------------------------------------------------------*/
function ajdg_nobot_activate() {
	global $wp_version;

	add_option('ajdg_nobot_protect', array('registration' => 1, 'comment' => 1, 'woocommerce' => 0));
	add_option('ajdg_nobot_security_message', 'Please fill in the correct answer to the security question!');
	add_option('ajdg_nobot_questions', array('What is the sum of 2 and 7?'));
	add_option('ajdg_nobot_answers', array(array('nine','9')));

	add_option('ajdg_nobot_blacklist_message', 'Your email has been banned from registration! Try using another email address or contact support for a solution.');
	add_option('ajdg_nobot_blacklist_usernames', array('subscriber', 'editor', 'admin', 'superadmin', 'author', 'customer', 'contributor', 'administrator', 'shop manager', 'shopmanager', 'email', 'ecommerce', 'forum', 'forums', 'feedback', 'follow', 'guest', 'httpd', 'https', 'information', 'invite', 'knowledgebase', 'lists', 'webmaster', 'yourname', 'support', 'team'));
	add_option('ajdg_nobot_blacklist_protect', array('namelength' => 0, 'nameisemail' => 0, 'emailperiods' => 0, 'namespaces' => 0));

	if(version_compare($wp_version, '5.5.0', '>=')) {
		$blacklist = explode("\n", get_option('disallowed_keys')); // wp core option
	} else {
		$blacklist = explode("\n", get_option('blacklist_keys')); // wp core option
	}

	$blacklist = array_merge($blacklist, array('hotmail', 'yahoo', '.cn', '.info', '.biz', '.xyz', '.ws'));
	$blacklist = array_unique($blacklist);
	sort($blacklist);
	$blacklist = implode("\n", $blacklist);

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
-------------------------------------------------------------*/
function ajdg_nobot_deactivate() {
	delete_option('ajdg_nobot_protect');
	delete_option('ajdg_nobot_security_message');
	delete_option('ajdg_nobot_questions');
	delete_option('ajdg_nobot_answers');
	delete_option('ajdg_nobot_blacklist_message');
	delete_option('ajdg_nobot_blacklist_protect');
	delete_option('ajdg_nobot_blacklist_usernames');
	delete_option('ajdg_nobot_hide_review'); // Obsolete in 2.5
	delete_option('ajdg_activate_no-bot-registration'); // Must match slug
	delete_transient('ajdg_update_no-bot-registration'); // Must match slug
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_init
 Purpose: 	Initialize
-------------------------------------------------------------*/
function ajdg_nobot_init() {
	load_plugin_textdomain('no-bot-registration', false, 'no-bot-registration/languages');
	wp_enqueue_script('jquery', false, false, false, true);
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_dashboard_styles
 Purpose: 	Add security field to comment form
-------------------------------------------------------------*/
function ajdg_nobot_dashboard_styles() {
	wp_enqueue_style('ajdg-nobot-admin-stylesheet', plugins_url('library/dashboard.css', __FILE__));
}

/*-------------------------------------------------------------
 Name:	  	ajdg_nobot_meta_links
 Purpose:	Extra links on the plugins dashboard page
-------------------------------------------------------------*/
function ajdg_nobot_meta_links($links, $file) {
	if($file !== 'no-bot-registration/no-bot-registration.php') return $links;
	
	$links['ajdg-settings'] = sprintf('<a href="%s">%s</a>', admin_url('tools.php?page=ajdg-nobot-settings'), 'Settings');
	$links['ajdg-help'] = sprintf('<a href="%s" target="_blank">%s</a>', 'https://support.ajdg.net/knowledgebase.php', 'Plugin Support');
	$links['ajdg-more'] = sprintf('<a href="%s" target="_blank">%s</a>', 'https://ajdg.solutions/plugins/', 'More plugins');	

	return $links;
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_return
-------------------------------------------------------------*/
function ajdg_nobot_return($page, $status, $args = null) {

	if(strlen($page) > 0 AND ($status > 0 AND $status < 1000)) {
		$defaults = array(
			'status' => $status
		);
		$arguments = wp_parse_args($args, $defaults);
		$redirect = 'tools.php?page=' . $page . '&'.http_build_query($arguments);
	} else {
		$redirect = 'tools.php?page=ajdg-nobot-settings';
	}

	wp_redirect($redirect);
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_status
-------------------------------------------------------------*/
function ajdg_nobot_status($status) {

	switch($status) {
		case '100' :
			echo '<div class="updated"><p>'.__('Settings saved', 'no-bot-registration').'</p></div>';
		break;

		default :
			echo '<div class="error"><p>'.__('Unexpected error', 'no-bot-registration').'</p></div>';
		break;
	}
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_comment_field
 Purpose: 	Add security field to comment form
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
		<label for="ajdg_nobot_answer"><?php echo htmlspecialchars($questions[$selected_id]); ?> <?php _e('(Required)', 'no-bot-registration'); ?></label>
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
 Name:      ajdg_nobot_save_settings
-------------------------------------------------------------*/
function ajdg_nobot_save_settings() {
	global $wp_version;

	if(!current_user_can('moderate_comments')) return;

	if(isset($_POST['nobot_protection_save_options'])) {
		if(wp_verify_nonce($_POST['ajdg_nobot_nonce'], 'no-bot-registration')) {
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
	
			if(isset($_POST['ajdg_nobot_blacklist_message'])) {
				update_option('ajdg_nobot_blacklist_message', sanitize_text_field($_POST['ajdg_nobot_blacklist_message']));
			}
	
			if(isset($_POST['ajdg_nobot_blacklist'])) {
				$blacklist_new_keys = strip_tags(htmlspecialchars($_POST['ajdg_nobot_blacklist'], ENT_QUOTES));
				$blacklist_new_keys = str_replace(array("\r\n", "\r", "\n", ", "), ',', trim($blacklist_new_keys));
				$blacklist_new_keys = explode(",", $blacklist_new_keys);
		
				$blacklist_new_keys = array_unique(array_filter($blacklist_new_keys));
				sort($blacklist_new_keys);
				$blacklist_new_keys = implode("\n", $blacklist_new_keys); // Must be string with newlines for WordPress...
	
				if(version_compare($wp_version, '5.5.0', '>=')) {
					update_option('disallowed_keys', $blacklist_new_keys);
				} else {
					update_option('blacklist_keys', $blacklist_new_keys);
				}
			}
	
			if(isset($_POST['ajdg_nobot_blacklist_usernames'])) {
				$blacklist_new_usernames = strip_tags(htmlspecialchars($_POST['ajdg_nobot_blacklist_usernames'], ENT_QUOTES));
				$blacklist_new_usernames = str_replace(array("\r\n", "\r", "\n", ", "), ',', trim($blacklist_new_usernames));
				$blacklist_new_usernames = explode(",", $blacklist_new_usernames);
				$blacklist_new_usernames = array_unique(array_filter($blacklist_new_usernames));
				sort($blacklist_new_usernames);
	
				update_option('ajdg_nobot_blacklist_usernames', $blacklist_new_usernames);
			}
	
			$blacklist_protect['namespaces'] = (isset($_POST['ajdg_nobot_allow_namespaces'])) ? 1 : 0;
			$blacklist_protect['namelength'] = (isset($_POST['ajdg_nobot_allow_namelength'])) ? 1 : 0;
			$blacklist_protect['nameisemail'] = (isset($_POST['ajdg_nobot_allow_nameisemail'])) ? 1 : 0;
			$blacklist_protect['emailperiods'] = (isset($_POST['ajdg_nobot_allow_emailperiods'])) ? 1 : 0;
			update_option('ajdg_nobot_blacklist_protect', $blacklist_protect);
			
			ajdg_nobot_return('ajdg-nobot-settings', 100);
			exit;
		} else {
			ajdg_nobot_nonce_error();
			exit;
		}
	}
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_check_config
 Purpose:   Update the options
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
	if(!is_array($nobot_blacklist_usernames)) {
		update_option('ajdg_nobot_blacklist_usernames', array('subscriber', 'editor', 'admin', 'superadmin', 'author', 'customer', 'contributor', 'administrator', 'shop manager', 'shopmanager', 'email', 'ecommerce', 'forum', 'forums', 'feedback', 'follow', 'guest', 'httpd', 'https', 'information', 'invite', 'knowledgebase', 'lists', 'webmaster', 'yourname', 'support', 'team'));
	}
	if(strlen($nobot_blacklist_message) == 0) {
		update_option('ajdg_nobot_blacklist_message', 'Your email has been banned from registration! Try using another email address or contact support for a solution.');
	}
	
	unset($nobot_protect, $nobot_questions, $nobot_answers, $nobot_message, $nobot_blacklist_protect, $nobot_blacklist_usernames, $nobot_blacklist_message);
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_blacklist
 Purpose: 	Check for banned emails on registration
-------------------------------------------------------------*/
function ajdg_nobot_blacklist($errors, $user_login, $user_email) {
 	global $wp_version;

	if(version_compare($wp_version, '5.5.0', '>=')) {
		$blacklist = get_option('disallowed_keys'); // wp core option
	} else {
		$blacklist = get_option('blacklist_keys'); // wp core option
	}
    $blacklist_usernames_array = get_option('ajdg_nobot_blacklist_usernames');
    $blacklist_message = get_option('ajdg_nobot_blacklist_message');
    $blacklist_protect = get_option('ajdg_nobot_blacklist_protect');

    $blacklist_array = explode("\n", $blacklist);

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
-------------------------------------------------------------*/
function ajdg_nobot_security_hash($id, $question, $answer) {
	// Hash format: SHA256( Question ID + Question Title + serialize( Question Answers ) )
	$hash_string = strval($id).strval($question).serialize($answer);

	return hash('sha256', $hash_string);
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_template
 Purpose: 	Settings questions listing
-------------------------------------------------------------*/
function ajdg_nobot_template($id, $question, $answers) {
	$id = intval($id);
?>
	<fieldset class="ajdg_nobot_row_<?php echo $id; ?>">
		<p>
			<strong><?php _e('Question:', 'no-bot-registration'); ?></strong>
		</p>

		<p>
			<input type="text" name="ajdg_nobot_question_<?php echo $id; ?>" size="50" style="width: 75%; margin:4px 1px;" value="<?php echo htmlspecialchars($question); ?>" placeholder="<?php _e('Type here to add a new question', 'no-bot-registration'); ?>" /> &cross; <a href="javascript:void(0)" onclick="ajdg_nobot_delete_entire_question(&quot;<?php echo $id; ?>&quot;)"><?php _e('Remove Question', 'no-bot-registration'); ?></a>
		</p>
	
		<?php if(count($answers) === 0 AND !empty($question)) echo "<p style=\"color: #F00;\"><strong>".__('This question has no answers! Add at least one answer or remove the question.', 'no-bot-registration')."</strong></p>"; ?>
	
		<p>
			<strong><?php _e('Possible Answers:', 'no-bot-registration'); ?></strong><br />
			<em><?php _e('Variations of the same answer help with user interpretation. Answers are case-insensitive.', 'no-bot-registration'); ?></em>
		</p>

		<p>
			<?php
			$i = 0;
			foreach($answers as $value) {
				echo '<span id="ajdg_nobot_answer_'.$id.'_'.$i.'">';
				echo '<input type="text" id="ajdg_nobot_answer_'.$id.'_'.$i.'" name="ajdg_nobot_answers_'.$id.'[]" size="50" style="width:75%; margin:4px 1px;" value="'.htmlspecialchars($value).'" /> &cross; <a href="javascript:void(0)" onclick="ajdg_nobot_delete(&quot;'.$id.'&quot;, &quot;'.$i.'&quot;)">'.__('Remove Answer', 'no-bot-registration').'</a>';
				echo '</span><br />';
				$i++;
			}
			echo '<script id="ajdg_nobot_placeholder_'.$id.'">ct['.$id.'] = '.$i.';</script>';
			?>
			&nbsp;&nbsp;&rdca;&nbsp;<a href="javascript:void(0)" onclick="return ajdg_nobot_add_newitem(<?php echo $id; ?>)"><?php _e('Add Possible Answer', 'no-bot-registration'); ?></a>
		</p>
	</fieldset>
<?php
}

/*-------------------------------------------------------------
 Name:      ajdg_nobot_nonce_error
-------------------------------------------------------------*/
function ajdg_nobot_nonce_error() {
	echo '	<h2 style="text-align: center;">'.__('Oh no! Something went wrong!', 'no-bot-registration').'</h2>';
	echo '	<p style="text-align: center;">'.__('WordPress was unable to verify the authenticity of the url you have clicked. Verify if the url used is valid or log in via your browser.', 'no-bot-registration').'</p>';
	echo '	<p style="text-align: center;">'.__('If you have received the url you want to visit via email, you are being tricked!', 'no-bot-registration').'</p>';
	echo '	<p style="text-align: center;">'.__('Contact support if the issue persists:', 'no-bot-registration').' <a href="https://support.ajdg.net/" title="AJdG Solutions Support" target="_blank">AJdG Solutions Support</a>.</p>';
}
?>
