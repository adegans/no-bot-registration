<div class="ajdg-box">
	<h2 class="ajdg-box-title"><?php _e('Become an advertising professional', 'no-bot-registration'); ?></h2>
	<div class="ajdg-box-content ajdg-box-sale">

		<a href="https://ajdg.solutions/product/adrotate-pro-single/" target="_blank"><img src="<?php echo plugins_url("/images/offers/monetize-your-site.jpg", __FILE__); ?>" alt="AdRotate Professional" width="100%"></a>
		<div class="title"><?php _e("AdRotate Professional", 'no-bot-registration'); ?></div>
		<div class="sub_title"><?php _e("Starting at only €49.00", 'no-bot-registration'); ?></div>
		<div class="cta"><a role="button" class="cta_button" href="https://ajdg.solutions/product/adrotate-pro-single/" target="_blank"><?php _e("Get a Single site License", 'no-bot-registration'); ?></a></div>
		<hr>
		<div class="description">
			<p><?php _e("Place any kind of advert including those from Google Adsense or affiliate links on your WordPress and ClassicPress website.", 'no-bot-registration'); ?></p>
		</div>

	</div>
</div>

<div class="ajdg-box">
	<h2 class="ajdg-box-title"><?php _e('Get more plugins', 'no-bot-registration'); ?></h2>
	<div class="ajdg-box-content ajdg-box-sale">

		<a href="https://ajdg.solutions/plugins/" target="_blank"><img src="<?php echo plugins_url("/images/offers/more-plugins.jpg", __FILE__); ?>" alt="AJdG Solutions Plugins" width="100%"></a>
		<div class="title"><?php _e("All my plugins", 'no-bot-registration'); ?></div>
		<div class="sub_title"><?php _e("For WordPress and ClassicPress", 'no-bot-registration'); ?></div>
		<div class="cta"><a role="button" class="cta_button" href="https://ajdg.solutions/plugins/" target="_blank"><?php _e("View now", 'no-bot-registration'); ?></a></div>

	</div>
</div>

<?php if(!is_plugin_active('gooseup/gooseup.php')) { ?>
<div class="ajdg-box">
	<h2 class="ajdg-box-title"><?php _e('GooseUp does updates', 'no-bot-registration'); ?></h2>
	<div class="ajdg-box-content ajdg-box-sale">

		<a href="https://ajdg.solutions/product/gooseup/" target="_blank"><img src="<?php echo plugins_url("/images/offers/gooseup.jpg", __FILE__); ?>" alt="GooseUp" width="100%"></a>
		<div class="title"><?php _e("GooseUp", 'no-bot-registration'); ?></div>
		<div class="sub_title"><?php _e("Get your updates straight from the source", 'no-bot-registration'); ?></div>
		<div class="cta"><a role="button" class="cta_button" href="https://ajdg.solutions/product/gooseup/" target="_blank"><?php _e("More info & download", 'no-bot-registration'); ?></a></div>

	</div>
</div>
<?php } ?>

<div class="ajdg-box">
	<h2 class="ajdg-box-title"><?php _e("Blogs & updates", 'no-bot-registration'); ?></h2>
	<div class="ajdg-box-content">

		<h3>AJdG Updates</h3>
		<?php echo ajdg_fetch_rss_feed('https://ajdg.solutions/feed/', 3); ?>
		<p>Stay up-to-date with plugins: <a href="https://ajdg.solutions/feed/" target="_blank" title="Subscribe to the AJdG Solutions blog!" class="button-primary"><i class="icn-rss"></i> RSS</a></p>

		<h3>Arnan's blog</h3>
		<?php echo ajdg_fetch_rss_feed('https://www.arnan.me/feed/page:feed.xml', 3); ?>
		<p>Subscribe to the blog of Arnan: <a href="https://www.arnan.me/feed/page:feed.xml" target="_blank" title="Subscribe to Arnan's blog!" class="button-primary"><i class="icn-rss"></i> RSS</a></p>

	</div>
</div>
