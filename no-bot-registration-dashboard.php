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

<?php settings_errors(); ?>

<div id="dashboard-widgets-wrap">
	<div id="dashboard-widgets" class="metabox-holder">
		<div id="left-column" class="ajdg-postbox-container">

			<div class="ajdg-postbox">
				<h2 class="ajdg-postbox-title"><?php _e('Registration protection', 'no-bot-registration'); ?></h2>
				<div id="nobot" class="ajdg-postbox-content">

					<form method="post" name="ajdg_nobot_protection">
						<?php wp_nonce_field('ajdg_nobot_protection','ajdg_nobot_nonce'); ?>

						<p><strong><?php _e('Where to add security questions?', 'no-bot-registration'); ?></strong></p>
						<p><input type="checkbox" name="ajdg_nobot_registration" value="1" <?php if($ajdg_nobot_protect['registration']) echo 'checked="checked"' ?> /> <?php _e('Protect user registration.', 'no-bot-registration'); ?><br /><em><?php _e('Has no effect if user registration is disabled.', 'no-bot-registration'); ?></em></p>


						<p><input type="checkbox" name="ajdg_nobot_comment" value="1" <?php if($ajdg_nobot_protect['comment']) echo 'checked="checked"' ?> /> <?php _e('Protect blog comments.', 'no-bot-registration'); ?><br /><em><?php _e('Has no effect if comments on posts are not enabled.', 'no-bot-registration'); ?></em></p>

						<p><input type="checkbox" name="ajdg_nobot_woocommerce" value="1" <?php if($ajdg_nobot_protect['woocommerce']) echo 'checked="checked"' ?> /> <?php _e('Protect WooCommerce checkout pages.', 'no-bot-registration'); ?><br /><em><?php _e('If user registration is enabled. Has no effect if WooCommerce is not installed.', 'no-bot-registration'); ?></em></p>

						<p><strong><?php _e('Failure message:', 'no-bot-registration'); ?></strong></p>
						<p><textarea name='ajdg_nobot_security_message' cols='70' rows='2' style="width: 100%;"><?php echo stripslashes($ajdg_nobot_security_message); ?></textarea><br /><em><?php _e('Displayed to those who fail the security question. Keep it short and simple.', 'no-bot-registration'); ?></em></p>

						<script type="text/javascript">
						var ct = Array();
						function ajdg_nobot_delete(id, x) {
							jQuery("#ajdg_nobot_answer_" + id + "_" + x).remove();
						}

						function ajdg_nobot_delete_entire_question(id) {
							jQuery("fieldset.ajdg_nobot_row_" + id).remove();
						}

						function ajdg_nobot_add_newitem(id) {
							jQuery("#ajdg_nobot_placeholder_" + id).before("<span id=\"ajdg_nobot_line_" + id + "_" + ct[id] + "\"><input type=\"text\" id=\"ajdg_nobot_answer_" + id + "_" + ct + "\" name=\"ajdg_nobot_answers_" + id + "[]\" size=\"50\" style=\"width: 75%; margin:4px 1px;\" value=\"\" placeholder=\"<?php _e('Enter a new answer here', 'no-bot-registration'); ?>\" /> &cross; <a href=\"javascript:void(0)\" onclick=\"ajdg_nobot_delete(&quot;" + id + "&quot;, &quot;" + ct[id] + "&quot;)\"><?php _e('Remove Answer', 'no-bot-registration'); ?></a><br /></span>");
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

						<p class="submit">
							<input tabindex="1000" type="submit" name="nobot_protection" class="button-primary" value="<?php _e('Save Settings', 'no-bot-registration'); ?>" />
						</p>
					</form>

				</div>
			</div>

			<div class="ajdg-postbox">
				<h2 class="ajdg-postbox-title"><?php _e('Blacklisted e-mail domains and usernames', 'no-bot-registration'); ?></h2>
				<div id="nobot" class="ajdg-postbox-content">

					<form method="post" name="ajdg_nobot_blacklist">
						<?php wp_nonce_field('ajdg_nobot_blacklist','ajdg_nobot_nonce'); ?>

						<p><em><?php _e('If you get many fake accounts or paid robots registering you can blacklist their usernames, email addresses or domains to prevent them from adding multiple accounts.', 'no-bot-registration'); ?></em></p>

						<p><strong><?php _e('Blacklist message:', 'no-bot-registration'); ?></strong></p>
						<p><textarea name='ajdg_nobot_blacklist_message' cols='70' rows='2' style="width: 100%"><?php echo stripslashes($ajdg_nobot_blacklist_message); ?></textarea><br /><em><?php _e('This message is shown to users who are not allowed to register on your site. Keep it short and simple.', 'no-bot-registration'); ?></em></p>

						<p><strong><?php _e('Blacklisted emails:', 'no-bot-registration'); ?></strong></p>
						<p><textarea name='ajdg_nobot_blacklist' cols='70' rows='10' style="width: 100%"><?php echo stripslashes($ajdg_nobot_blacklist); ?></textarea><br /><?php _e('Comma separated. Add as many as you need.', 'no-bot-registration'); ?><br /><strong><?php _e('Caution:', 'no-bot-registration'); ?></strong> <?php _e('This is a powerful filter matching partial words. So banning "mail" will also block Gmail users!', 'no-bot-registration'); ?></p>
						<p><strong><?php _e('You can add:', 'no-bot-registration'); ?></strong> <?php _e('full emails (someone@hotmail.com), domains (hotmail.com) or simply a keyword (hotmail).', 'no-bot-registration'); ?><br /><strong><?php _e('Default:', 'no-bot-registration'); ?></strong> hotmail, yahoo, .cn, .info, .biz, .xyz, .ws.</em></p>

						<p><strong><?php _e('Blacklisted usernames:', 'no-bot-registration'); ?></strong></p>
						<p><textarea name='ajdg_nobot_blacklist_usernames' cols='70' rows='10' style="width: 100%"><?php echo stripslashes($ajdg_nobot_blacklist_usernames); ?></textarea><br /><?php _e('Comma separated. Add as many as you need.', 'no-bot-registration'); ?><br /><strong><?php _e('Caution:', 'no-bot-registration'); ?></strong> <?php _e('This is a powerful filter matching partial words. So adding "web" will also block webmaster!', 'no-bot-registration'); ?></p>
						<p><strong><?php _e('Default:', 'no-bot-registration'); ?></strong> subscriber, editor, admin, superadmin, author, customer, contributor, administrator, shop manager, shopmanager, email, ecommerce, forum, forums, feedback, follow, guest, httpd, https, information, invite, knowledgebase, lists, webmaster, yourname, support, team.</em></p>

						<p><strong><?php _e('Need more protection against fake accounts?', 'no-bot-registration'); ?></strong></p>
						<p><?php _e('Add a few restrictions on how usernames are formatted. This helps against automated bots and manual entry fake accounts.', 'no-bot-registration'); ?></p>
						<p><input type="checkbox" name="ajdg_nobot_allow_namespaces" value="1" <?php if($ajdg_nobot_blacklist_protect['namespaces']) echo 'checked="checked"' ?> /> <?php _e('Disallow spaces in usernames?', 'no-bot-registration'); ?></p>
						<p><input type="checkbox" name="ajdg_nobot_allow_namelength" value="1" <?php if($ajdg_nobot_blacklist_protect['namelength']) echo 'checked="checked"' ?> /> <?php _e('Disallow usernames that are shorter than 5 characters?', 'no-bot-registration'); ?></p>
						<p><input type="checkbox" name="ajdg_nobot_allow_nameisemail" value="1" <?php if($ajdg_nobot_blacklist_protect['nameisemail']) echo 'checked="checked"' ?> /> <?php _e('Disallow usernames to be an email address?', 'no-bot-registration'); ?><br /><em><?php _e('Use with caution, this may conflict with some plugins like WooCommerce which DO allow email addresses as usernames.', 'no-bot-registration'); ?></em></p>
						<p><input type="checkbox" name="ajdg_nobot_allow_emailperiods" value="1" <?php if($ajdg_nobot_blacklist_protect['emailperiods']) echo 'checked="checked"' ?> /> <?php _e('Disallow more than 4 periods in email addresses?', 'no-bot-registration'); ?><br /><em><?php _e('A common trick is to break up words or names in email addresses with a lot of p.er.i.od.s@example.com.', 'no-bot-registration'); ?></em></p>
								
						<p class="submit">
							<input tabindex="1000" type="submit" name="nobot_blacklist" class="button-primary" value="<?php _e('Save Blacklist Settings', 'no-bot-registration'); ?>" />
						</p>
					</form>

				</div>
			</div>

		</div>
		<div id="right-column" class="ajdg-postbox-container">

			<div class="ajdg-postbox">
				<h2 class="ajdg-postbox-title"><?php _e('No-Bot Registration', 'no-bot-registration'); ?></h2>
				<div id="general" class="ajdg-postbox-content">
					<p><strong><?php _e('Get help with No-Bot Registration', 'no-bot-registration'); ?></strong></p>
					<p><?php _e('Use the buttons below if you have any questions about using No-Bot Registration. I am always happy to help!', 'no-bot-registration'); ?></p>

					<p><a class="button-primary" href="https://ajdg.solutions/product/support-ticket/" target="_blank" title="<?php _e('Buy support ticket', 'no-bot-registration'); ?>"><?php _e('Buy a support ticket', 'no-bot-registration'); ?></a> <a class="button-primary" href="https://support.ajdg.net/knowledgebase.php" target="_blank" title="<?php _e('Knowledgebase', 'no-bot-registration'); ?>"><?php _e('Knowledgebase', 'no-bot-registration'); ?></a> <a class="button-secondary" href="https://wordpress.org/support/plugin/no-bot-registration/" target="_blank" title="<?php _e('Forum on wordpress.org', 'no-bot-registration'); ?>"><?php _e('Forum on wordpress.org', 'no-bot-registration'); ?></a></p>

					<p><strong><?php _e('Support No-Bot Registration', 'no-bot-registration'); ?></strong></p>
					<p><?php _e('Consider writing a review or making a donation if you like the plugin or if you find the plugin useful. Thanks for your support!', 'no-bot-registration'); ?></p>

					<p><a class="button-primary" href="https://ajdg.solutions/product/token-of-thanks/" target="_blank" title="<?php _e('Support me with a token of thanks', 'no-bot-registration'); ?>"><?php _e('Gift a token of thanks', 'no-bot-registration'); ?></a> <a class="button-secondary" href="https://wordpress.org/support/plugin/no-bot-registration/reviews?rate=5#postform" target="_blank" title="<?php _e('Write review on wordpress.org', 'no-bot-registration'); ?>"><?php _e('Write review on wordpress.org', 'no-bot-registration'); ?></a></p>

					<p><strong><?php _e('Plugins and services', 'no-bot-registration'); ?></strong></p>
					<table width="100%">
						<tr>
							<td width="50%">
								<div class="ajdg-sales-widget" style="display: inline-block; margin-right:2%;">
									<a href="https://ajdg.solutions/product/adrotate-pro-single/" target="_blank"><div class="header"><img src="<?php echo plugins_url("/images/offers/monetize-your-site.jpg", __FILE__); ?>" alt="AdRotate Professional" width="228" height="120"></div></a>
									<a href="https://ajdg.solutions/product/adrotate-pro-single/" target="_blank"><div class="title"><?php _e('AdRotate Professional', 'no-bot-registration'); ?></div></a>
									<div class="sub_title"><?php _e('WordPress Plugin', 'no-bot-registration'); ?></div>
									<div class="cta"><a role="button" class="cta_button" href="https://ajdg.solutions/product/adrotate-pro-single/" target="_blank"><?php _e('Starting at', 'no-bot-registration'); ?> &euro; 49,-</a></div>
									<hr>
									<div class="description"><?php _e('Place any kind of advert including those from Google Adsense or affiliate links on your WordPress and ClassicPress website.', 'no-bot-registration'); ?></div>
								</div>
							</td>
							<td>
								<div class="ajdg-sales-widget" style="display: inline-block;">
									<a href="https://ajdg.solutions/plugins/" target="_blank"><div class="header"><img src="<?php echo plugins_url("/images/offers/more-plugins.jpg", __FILE__); ?>" alt="AJdG Solutions Plugins" width="228" height="120"></div></a>
									<a href="https://ajdg.solutions/plugins/" target="_blank"><div class="title"><?php _e('All my plugins', 'no-bot-registration'); ?></div></a>
									<div class="sub_title"><?php _e('WordPress and ClassicPress', 'no-bot-registration'); ?></div>
									<div class="cta"><a role="button" class="cta_button" href="https://ajdg.solutions/plugins/" target="_blank"><?php _e('View now', 'no-bot-registration'); ?></a></div>
									<hr>
									<div class="description"><?php _e('Excellent plugins for WordPres, ClassicPress, WooCommerce and bbPress. Most of them are completely FREE to use!', 'no-bot-registration'); ?></div>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="ajdg-postbox">
				<h2 class="ajdg-postbox-title"><?php _e('News & Updates', 'no-bot-registration'); ?></h2>
				<div id="news" class="ajdg-postbox-content">
					<p><a href="http://ajdg.solutions/feed/" target="_blank" title="Subscribe to the AJdG Solutions RSS feed!" class="button-primary"><i class="icn-rss"></i><?php _e('Subscribe via RSS feed', 'no-bot-registration'); ?></a> <em><?php _e('No account required!', 'no-bot-registration'); ?></em></p>

					<?php wp_widget_rss_output(array(
						'url' => 'http://ajdg.solutions/feed/',
						'items' => 10,
						'show_summary' => 0,
						'show_author' => 0,
						'show_date' => 1)
					); ?>
				</div>
			</div>

		</div>
	</div>
</div>
