<?php
/* ------------------------------------------------------------------------------------
*  COPYRIGHT NOTICE
*  Copyright 2017-2026 Arnan de Gans. All Rights Reserved.

*  COPYRIGHT NOTICES AND ALL THE COMMENTS SHOULD REMAIN INTACT.
*  By using this code you agree to indemnify Arnan de Gans from any
*  liability that might arise from its use.
------------------------------------------------------------------------------------ */

defined('ABSPATH') or die();

$ajdg_nobot_protect = get_option('ajdg_nobot_protect', array());
$ajdg_nobot_questions = get_option('ajdg_nobot_questions', array());
$ajdg_nobot_answers = get_option('ajdg_nobot_answers', array());

if(version_compare($wp_version, '5.5.0', '>=')) {
    $ajdg_nobot_blacklist = get_option('disallowed_keys'); // WP Core
} else {
    $ajdg_nobot_blacklist = get_option('blacklist_keys'); // WP Core
}
$ajdg_nobot_blacklist = explode("\n", $ajdg_nobot_blacklist);
$ajdg_nobot_blacklist = implode(", ", $ajdg_nobot_blacklist);

$ajdg_nobot_blacklist_usernames = get_option('ajdg_nobot_blacklist_usernames');
$ajdg_nobot_blacklist_usernames = implode(", ", $ajdg_nobot_blacklist_usernames);
$ajdg_nobot_blacklist_protect = get_option('ajdg_nobot_blacklist_protect');

$ajdg_nobot_blacklist_message = get_option('ajdg_nobot_blacklist_message');
$ajdg_nobot_security_message = get_option('ajdg_nobot_security_message');
?>

<form name="no-bot-registration" method="post">
<?php wp_nonce_field('no-bot-registration','ajdg_nobot_nonce'); ?>

<div class="ajdg-box-wrap">
	<div class="ajdg-box-three">

		<div class="ajdg-box">
			<h2 class="ajdg-box-title"><?php _e("What do you want to protect?", 'no-bot-registration'); ?></h2>
			<div class="ajdg-box-content">

				<p><strong><?php _e("Where to add security questions?", 'no-bot-registration'); ?></strong></p>
				<p><input type="checkbox" name="ajdg_nobot_registration" value="1" <?php if($ajdg_nobot_protect['registration']) echo 'checked="checked"' ?> /> <?php _e("Protect user registration.", 'no-bot-registration'); ?><br /><em><?php _e("Has no effect if user registration is disabled.", 'no-bot-registration'); ?></em></p>


				<p><input type="checkbox" name="ajdg_nobot_comment" value="1" <?php if($ajdg_nobot_protect['comment']) echo 'checked="checked"' ?> /> <?php _e("Protect blog comments.", 'no-bot-registration'); ?><br /><em><?php _e("Has no effect if comments on posts are not enabled.", 'no-bot-registration'); ?></em></p>

				<p><input type="checkbox" name="ajdg_nobot_woocommerce" value="1" <?php if($ajdg_nobot_protect['woocommerce']) echo 'checked="checked"' ?> /> <?php _e("Protect WooCommerce checkout pages.", 'no-bot-registration'); ?><br /><em><?php _e("If user registration is enabled. Has no effect if WooCommerce is not installed.", 'no-bot-registration'); ?></em></p>

				<p><strong><?php _e("Failure message:", 'no-bot-registration'); ?></strong></p>
				<p><textarea name='ajdg_nobot_security_message' cols='70' rows='2' style="width: 100%;"><?php echo stripslashes($ajdg_nobot_security_message); ?></textarea><br /><em><?php _e("Displayed to those who fail the security question. Keep it short and simple.", 'no-bot-registration'); ?></em></p>

				<p><strong><?php _e("Blacklist message:", 'no-bot-registration'); ?></strong></p>
				<p><textarea name='ajdg_nobot_blacklist_message' cols='70' rows='2' style="width: 100%"><?php echo stripslashes($ajdg_nobot_blacklist_message); ?></textarea><br /><em><?php _e("This message is shown to visitors who use a blacklisted email address or username. Keep it short and simple.", 'no-bot-registration'); ?></em></p>

			</div>
		</div>

		<div class="ajdg-box">
			<h2 class="ajdg-box-title"><?php _e("Security questions", 'no-bot-registration'); ?></h2>
			<div class="ajdg-box-content">

				<script type="text/javascript">
				var ct = Array();
				function ajdg_nobot_delete(id, x) {
					jQuery("#ajdg_nobot_answer_" + id + "_" + x).remove();
				}

				function ajdg_nobot_delete_entire_question(id) {
					jQuery("fieldset.ajdg_nobot_row_" + id).remove();
				}

				function ajdg_nobot_add_newitem(id) {
					jQuery("#ajdg_nobot_placeholder_" + id).before("<span id=\"ajdg_nobot_line_" + id + "_" + ct[id] + "\"><input type=\"text\" id=\"ajdg_nobot_answer_" + id + "_" + ct + "\" name=\"ajdg_nobot_answers_" + id + "[]\" size=\"50\" style=\"width: 75%; margin:4px 1px;\" value=\"\" placeholder=\"<?php _e("Enter a new answer here", 'no-bot-registration'); ?>\" /> &cross; <a href=\"javascript:void(0)\" onclick=\"ajdg_nobot_delete(&quot;" + id + "&quot;, &quot;" + ct[id] + "&quot;)\"><?php _e("Remove Answer", 'no-bot-registration'); ?></a><br /></span>");
					ct[id]++;
					return false;
				}
				</script>

				<?php
				// List all questions
				foreach($ajdg_nobot_questions as $key => $question) {
					// Question has no answers...
					if(!isset($ajdg_nobot_answers[$key])) $ajdg_nobot_answers[$key] = array();
					
					// Add form to create/edit questions and answers
					ajdg_nobot_template($key, $question, $ajdg_nobot_answers[$key]);
				}

				// Add one empty question
				ajdg_nobot_template(array_key_last($ajdg_nobot_questions)+1, '', Array());
				?>

			</div>
		</div>

		<div class="ajdg-box">
			<h2 class="ajdg-box-title"><?php _e("Blacklisted e-mail domains", 'no-bot-registration'); ?></h2>
			<div class="ajdg-box-content">

				<p><?php _e("If you get many fake accounts or paid robots registering you can blacklist the email address or the entire domains.", 'no-bot-registration'); ?></p>
				<p><textarea name='ajdg_nobot_blacklist' cols='70' rows='10' style="width: 100%"><?php echo stripslashes($ajdg_nobot_blacklist); ?></textarea><br /><?php _e("Comma separated. Add full emails (someone@hotmail.com), domains (hotmail.com) or simply a keyword (hotmail).", 'no-bot-registration'); ?><br /><strong><?php _e("Caution:", 'no-bot-registration'); ?></strong> <?php _e("This is a powerful filter matching partial words. So banning 'mail' will also block Gmail users!", 'no-bot-registration'); ?></p>
				<p><strong><?php _e("Default:", 'no-bot-registration'); ?></strong> hotmail, yahoo, .cn, .info, .biz, .xyz, .ws.</em></p>

			</div>
		</div>

		<div class="ajdg-box">
			<h2 class="ajdg-box-title"><?php _e("Blacklisted usernames", 'no-bot-registration'); ?></h2>
			<div class="ajdg-box-content">

				<p><?php _e("Some usernames simply should not be available for users. For various reasons. You can list all of them here in a comma separated list.", 'no-bot-registration'); ?></p>
				<p><textarea name='ajdg_nobot_blacklist_usernames' cols='70' rows='10' style="width: 100%"><?php echo stripslashes($ajdg_nobot_blacklist_usernames); ?></textarea><br /><strong><?php _e("Caution:", 'no-bot-registration'); ?></strong> <?php _e("This is a powerful filter matching partial words. So adding 'web' will also block 'webmaster'!", 'no-bot-registration'); ?></p>
				<p><strong><?php _e("Default:", 'no-bot-registration'); ?></strong> subscriber, editor, admin, superadmin, author, customer, contributor, administrator, shop manager, shopmanager, email, ecommerce, forum, forums, feedback, follow, guest, httpd, https, information, invite, knowledgebase, lists, webmaster, yourname, support, team.</em></p>

			</div>
		</div>

		<div class="ajdg-box">
			<h2 class="ajdg-box-title"><?php _e("Protection against fake accounts", 'no-bot-registration'); ?></h2>
			<div class="ajdg-box-content">

				<p><?php _e("Add a few restrictions on how usernames are formatted. This helps against automated bots and manual entry fake accounts.", 'no-bot-registration'); ?></p>
				<p><input type="checkbox" name="ajdg_nobot_allow_namespaces" value="1" <?php if($ajdg_nobot_blacklist_protect['namespaces']) echo 'checked="checked"' ?> /> <?php _e("Disallow spaces in usernames?", 'no-bot-registration'); ?></p>
				<p><input type="checkbox" name="ajdg_nobot_allow_namelength" value="1" <?php if($ajdg_nobot_blacklist_protect['namelength']) echo 'checked="checked"' ?> /> <?php _e("Disallow usernames that are shorter than 5 characters?", 'no-bot-registration'); ?></p>
				<p><input type="checkbox" name="ajdg_nobot_allow_nameisemail" value="1" <?php if($ajdg_nobot_blacklist_protect['nameisemail']) echo 'checked="checked"' ?> /> <?php _e("Disallow usernames to be an email address?", 'no-bot-registration'); ?><br /><em><?php _e("Use with caution, this may conflict with some plugins like WooCommerce which DO allow email addresses as usernames.", 'no-bot-registration'); ?></em></p>
				<p><input type="checkbox" name="ajdg_nobot_allow_emailperiods" value="1" <?php if($ajdg_nobot_blacklist_protect['emailperiods']) echo 'checked="checked"' ?> /> <?php _e("Disallow more than 4 periods in email addresses?", 'no-bot-registration'); ?><br /><em><?php _e("A common trick is to break up words or names in email addresses with a lot of p.er.i.od.s@example.com.", 'no-bot-registration'); ?></em></p>
							
			</div>
		</div>

		<div class="ajdg-box">
			<p class="submit">
			  	<input type="submit" name="nobot_protection_save_options" class="button-primary" value="<?php _e("Save settings", 'no-bot-registration'); ?>" tabindex="1000" />
			</p>
		</div>

	</div>
	<div class="ajdg-box-one">

		<?php include_once(__DIR__.'/no-bot-registration-sidebar.php'); ?>

	</div>
</div>

</form>