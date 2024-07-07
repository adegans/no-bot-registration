=== No-Bot Registration ===
Contributors: adegans
Tags: antispam, protection, fake account, comment spam, security
Donate link: https://www.arnan.me/donate.html?mtm_campaign=nobot_registration
Requires at least: 4.9
Tested up to: 6.6
Stable tag: 2.1.1
License: GPLv3

Prevent bots from creating accounts by blacklisting domains and usernames and present people with a human friendly security question.

== Description ==
Tired of spam bots in your WordPress and ClassicPress website? Do you want to get rid of false registrations and other spammy nonsense? Don't wan't to use a clumsy and user-unfriendly Captcha? Don't want to use a Captcha from Google or other big-tech company period?

Meet **No-Bot Registration**, easy to use, superior protection without making it hard for your visitors. Easily blacklist (partial) email addresses and domains so they can no longer register an account. \
Create one or more questions and a set of possible answers for them and visitors have to answer your question when they register.
If they answer wrong, they get denied their account.

Questions can be as simple as "1 + 1", with possible answers being 1, one or uno. That way you can plan for eventualities and how people interpret your question.

= Features =
* Protect registration forms
* Protect the WooCommerce checkout form if you let people register from there
* Protect your blog comment form
* Prevents comment spam, trackback spam and other nuisances with ease
* Set up multiple security questions to further confuse bots
* Blacklist any email, domain or tld you don't like
* Configurable notification messages for users failing the security tests

== Changelog ==

= 2.1.1 - July 7, 2024 =
* [removed] Classic Commerce support
* [updated] Readme

= 2.1 - 17 June, 2024 =
* [new] Visual error when a question has no answers 
* [fix] deleting questions now works
* [fix] PHP Fatal error when a question has no answers
* [i18n] Updated no-bot-registration.pot
* [updated] readme.txt & Action links

= 2.0.1 - 8 March, 2024 =
* [new] POT file for translations
* [fix] settings of extra blacklist settings not saving
* [fix] extra blacklist settings defaulting to 'on' if not set

= 2.0 - 19 February, 2024 =
* [new] username blacklist
* [new] username and email restrictions
* [new] nonce protections to settings forms
* [new] basic config check

= 1.9.1 - 5 February, 2023 =
* [new] missing ABSPATH in plugin file

= 1.9 - 31 January, 2023 =
* [updated] dashboard sections
* [updated] support links
* [removed] help tabs

= 1.8.2 - 2 January, 2023 =
* [fix] better check for filtering comments/registrations
* [tweak] replaced alias functions with actual functions
* [tweak] minor code cleanups

= 1.8.1 - 30 December, 2022 =
* [fix] excempting editors and admins on field check
* [fix] excluding non-protected fields from field check

= 1.8 - 22 October, 2022 =
* [fix] email error on wrong question answer
* [fix] redone the filter process (simpler and better hooks/filters)
* [fix] added native Classic Commerce compatibility
* [tweak] better formatted error message

= 1.7.12 - 21 October, 2022 =
* [fix] support links
* [fix] compatibility info
* [fix] plugin links

= 1.7.11 - 18 October, 2022 =
* [new] several missing translation strings

= 1.7.10 - 7 October, 2022 =
* [updated] support links
* Tested to work with WordPress 6+

= 1.7.9 - 25 January, 2022 =
* Tested to work with WordPress 5.9
* Tested to work with ClassicPress 1.3.1

= 1.7.8 - 20 June, 2021 =
* [updated] readme.txt
* Tested to work with WordPress 5.7

= 1.7.7 - 25 January, 2021 =
* Happy New Year
* [new] News&Update links

= 1.7.6 - 3 August, 2020 =
* [Updated] to work with WordPress 5.5
* [change] Dashboard tweaks

= 1.7.5 - 16 July 2020 =
* [fix] Captcha trap not always checked
* [fix] Empty question list causing array errors

= 1.7.4 - 3 February, 2020 =
* [fix] Review notification linking to wrong dashboard url
* [fix] Deleting answers not always removing an variation
* [change] New style for notification banners

= 1.7.3 - 4 October, 2019 =
* [fix] Not always showing the captcha if WooCommerce protection is on
* [change] Dashboard tweaks
* [change] Moved dashboard to Tools Menu

= 1.7.2 - 9 September, 2019 =
* [fix] Dashboard widgets showing properly
* [i18n] Updated translations

= 1.7.1 - 30 August, 2019 =
* [change] Dashboard tweaks
* [i18n] Updated translations

= 1.7 - 21 August, 2019 =
* [change] Updated dashboard
* [change] WordPress 5.2.2 compatibility
* [i18n] Updated translation implementation

= 1.6.1 - 16 June, 2019 =
* [change] Updated dashboard
* [fix] Wrong url in plugin links

= 1.6 - 29 May, 2019 =
* [fix] Not able to hide Rate plugin banner
* [change] Answers are now case-insensitive, 'Nine' and 'nine' are the same
* [i18n] Added more strings to translate
* [i18n] Updated translation file

= 1.5 - 9 May, 2019 =
* [change] WordPress 5.2 compatibility
* [fix] Improved WooCommerce compatibility
* [change] Updated dashboard

= 1.4 - 26 March, 2019 =
* [change] WordPress 5.0 compatibility
* [change] Updated dashboard

= 1.3 - 8 July, 2018 =
* [new] Bot trap in registration forms
* [new] Bot trap in comment form
* [change] Updated dashboard

= 1.2.3 - 3 June, 2018 =
* [fix] Close link on notification not working

= 1.2.2 - 17 May, 2018 =
* [change] Compatibility update
* [change] Updated dashboard
* [change] Cleaned up CSS
* [i18n] Added missing translation strings
* [i18n] Updated translation files

= 1.2.1 - 13 December, 2017 =
* [removed] unused assets
* Dashboard tweaks
* Compatibility update

= 1.2 - 14 May, 2017 =
* Dashboard tweaks

= 1.1 - 19 April, 2017 =
* [fix] security field on my-account page of WooCommerce
* Improved WooCommerce compatibity
* Dashboard tweaks

= 1.0 - 24 February, 2017 =
* First release

== Installation ==

1. Navigate to Plugins > Add New in your dashboard.
2. Search for 'Arnan No-Bot' or 'Arnan' in the plugin database and click install.
3. Activate the plugin when done.
4. Navigate to Tools > No-Bot Registration in your dashboard for settings.

== Frequently Asked Questions ==

= I need help with this plugin =
Check out my [Support Forum](https://ajdg.solutions/forums/forum/no-bot-registration/)

= This is cool, do you have more plugins? =
Yep, check out my website [AJdG Solutions](https://ajdg.solutions/plugins/?mtm_campaign=nobot_registration)

== Screenshots ==

1. Registration Protection Settings
2. Blacklist Settings
3. Example of security question on registration
4. Example of security question on WooCommerce
