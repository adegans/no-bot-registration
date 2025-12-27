<?php
/* ------------------------------------------------------------------------------------
*  COPYRIGHT NOTICE
*  Copyright 2008-2025 Arnan de Gans. All Rights Reserved.
*  ADROTATE is a registered trademark of Arnan de Gans.

*  COPYRIGHT NOTICES AND ALL THE COMMENTS SHOULD REMAIN INTACT.
*  By using this code you agree to indemnify Arnan de Gans from any
*  liability that might arise from its use.
---------------------------------------------------------------------------------------
*  This file includes common functions used by several plugins
------------------------------------------------------------------------------------ */

/*-------------------------------------------------------------
 AJdG Solutions common functions used by several plugins
---------------------------------------------------------------
 Changelog:
---------------------------------------------------------------
2.0 - December 21, 2025
	* Updated - Redid the RSS function for SimplePie 1.9 compatibility
1.0.1 - November 21, 2025
	* Updated - Arnan.me RSS url
1.0 - November 7, 2025
	* Added - RSS feed reader
-------------------------------------------------------------*/

/*-------------------------------------------------------------
 Name:      ajdg_fetch_rss_feed
 Purpose:   Load one of more RSS feeds to show in the AdRotate dashboard. Cache it for a day.
 Note: 		Cache is shared between compatible plugins!
-------------------------------------------------------------*/
if(!function_exists('ajdg_fetch_rss_feed')) {
	function ajdg_fetch_rss_feed($url = '', $show_items = 6) {
		// Check for errors
		if(!is_numeric($show_items) OR $show_items < 1 OR $show_items > 20) {
			$show_items = 6;
		}
	
		$rss = fetch_feed($url);
	
		if(is_wp_error($rss)) {
			$feed_output = '<p>The feed could not be fetched.</p>';
		} else if(!$rss->get_item_quantity()) {
			$feed_output = '<p>The feed has no items or could not be read.</p>';
		} else {		
			// Prepare output
			$feed_output = '<ul>';
			foreach($rss->get_items(0, $show_items) as $item) {
				$link = $item->get_link();
	
				while(!empty($link) AND stristr($link, 'http') !== $link) {
					$link = substr($link, 1);
				}
	
				$link = esc_url(strip_tags($link));
				$title = esc_html(trim(strip_tags($item->get_title())));
				$date = $item->get_date('U');
	
				if(empty($title)) $title = __('Untitled');
				if($date) $date = ' <span class="rss-date">'.date_i18n(get_option('date_format'), $date).'</span>';
	
				$feed_output .= (empty($link)) ? "<li>$title<br /><em>{$date}</em></li>" : "<li><a class='rsswidget' href='$link'>$title</a><br /><em>{$date}</em></li>";
			}
			$feed_output .= '</ul>';
		}
		unset($rss);
	
		// Done!
		return $feed_output;
	}
}