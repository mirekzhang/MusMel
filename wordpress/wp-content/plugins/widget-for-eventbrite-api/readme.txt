=== Eventbrite Events Display in WordPress ===
Contributors: fullworks
Tags: eventbrite, widget, api, events, eventbrite widget, eventbrite shortcode
Requires at least: 4.6
Tested up to: 5.1.1
Stable tag: 2.7.10
Requires PHP: 5.6
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

Display your upcoming Eventbrite events in an easy to configure widget.

== Description ==

Solve your problem by integrating Eventbrite events to your WordPress website.

Eventbrite is undoubtedly a very easy and powerful way to setup an events calendar, whether for free events or ticketed events.

Whilst there are many events calendar plugins, there are disadvantages of managing your own events system and Eventbrite brings
a whole new level to your event management, as Eventbrite is a marketing platform as well as a processing platform.

This free plugin creates a Widget for Eventbrite using the API enabling you to display your forthcoming events, just like you would do for recent posts in an easy to use and familiar way.

Unlike other plugins that import Eventbrite events into WordPress and so creates custom posts types or tables, this plugin uses the Eventbrite API to read directly from Evenbrite servers. Don't be put off by the words API, that just means the plugin is linked directly to Eventbrite data - no rekeying, no extra data to manage.

See a [demo here](https://widget-for-eventbrite-api.demo.fullworks.net/)

What it looks like on your site will depend on your theme and any extra styling you want to apply. It will blend in as though the Eventbrite events are posts on your own site.

The Widget gives you easy but powerful control over how events are displayed.

= Features Include =
* Custom html or text before and/or after recent posts.
* Allow you to set title url.
* Display thumbnails, with customizable size and alignment.
* Exclude current event post
* Read more option, with a link direct to the Eventbrite page.
* Book now option with a link direct to the Eventbrite page..
* Decide if you want Eventbrite to open in a new tab, or not
* Easy to customise CSS.
* Ability for developers to override widget design using templates in ( child ) theme

== Installation ==

**Through Dashboard**

1. Log in to your WordPress admin panel and go to Plugins -> Add New
1. Type widget for eventbrite api in the search box and click on search button.
1. Find Widget for Eventbrite API plugin.
1. Then click on Install Now after that activate the plugin.
1. Go to the widgets page Appearance -> Widgets.
1. Widget for Eventbrite API widget.

**Installing Via FTP**

1. Download the plugin to your hardisk.
2. Unzip.
3. Upload the **widget-for-eventbrite-api** folder into your plugins directory.
4. Log in to your WordPress admin panel and click the Plugins menu.
5. Then activate the plugin.
6. Go to the widgets page **Appearance -> Widgets**.
7. Find **Widget for Eventbrite API** widget.

== Frequently Asked Questions ==

= How Do I Use this Plugin? =
This plugin creates a Widget specifically for EventBrite Events. Once installed, you use the widget the same way as you would any other WordPress widget, set the settings and see what happens. Before you can use the widget
you need to connect to your Eventbrite account so it can read your events, how to do that is detailed in the next FAQ. If anything is not clear, why not ask in the support forum for clarification.  If you need more personalised support, then you can sign up for the Pro version right from inside your WordPress admin panel.

= How to set up a connection to Eventbrite? =
You will need to obtain a personal OAuth key from your Eventbrite account and enter it in the settings page, this is fairly straight forward and instructions are [here](https://fullworks.net/technical-documentation/widget-for-eventbrite-technical-installation/eventbrite-key/)

= Does this work on multisite? =
Yes.

= My events are not updating or coming through? =
The plugin has a 24 hour cache so new events may not show for up to 24 hours and deleted events may remain for up to 24 hours.  The Pro version ( you can update to a trial in your dashboard ) has setting to allow you to clear the cache and change the cache period.

= Why do I need to pay to upgrade for the shortcode, shouldn't it all be free? =
This plugin was originally developed as a widget. The shortcode was added later and has been extended based on user requests, this all take time and effort, by paying for the pro version, yun ot only get all the additional features, but you get support if you have any issue, and you are ensuring that this plugin, by funding it, exists a long time into the future. There is nothing worse than setting up a plugin, and some time in the future finding it has been abandoned due to lack of investment.


= (advanced) How does the template system work? =
create a directory in your theme ( or child theme ) directory called widget-for-eventbrite-api  and copy the template from wp-content/plugins/widget-for-event-brite-api/templates/widget.php
and customise that code as required.



== Go Pro ==

Get so much more with the Pro version.

Move out of the sidebar or footer, and create full page layouts using the pro shortcode and additional widget features.

Upgrade to get new awesome features and support. Upgrade directly from the WordPress dashboard. Additional features include

* shortcode version, include on pages & post, with additional filters so you can split events across pages
* pre built shortcode templates for Divi, Genesis and WP default themes
* pre built calendar page template
* full width or grid layouts
* link a button to the Eventbrite checkout popup directly
* customise with developer templates
* listing private / invite only events
* manage Eventbrite API cache time to optimised performance
* clear Eventbrite API cache, to help testing

== Privacy and GDPR ==

This plugin does not collect, process or send any website visitor personal data in anyway


== Upgrade notice ==


== Changelog ==
= 2.7.10 =
* Display error message to front end ( only to users with manage_options capabilities )

= 2.7.9 =
* Display error message to front end ( only to users with manage_options capabilities )


= 2.7.8 =
* Missing files in 2.7.7

= 2.7.7 =
* Minor fix

= 2.7.6 =
* Warning that Keyring is deprecated for this plugin

= 2.7.5 =
* Improve validation of Oauth API key in settings

= 2.7.4 =
* Use the new Eventbrite API endpoint for description to cater for the new UI if long description required


= 2.7.3 =
* Improve resolution of conflict if free and premium activated

= 2.7.2 =
* Cater for ultra large Org Ids from Eventbrite

= 2.7.0 =
* Change to use /organizations end point as the /users/me/owned_events is advised as to be deprecated

= 2.6.7 =
* Improved coverage and filtering for accounts with more 50 events, and fixed issue with 2.6.4 upwards which using personal auth displaying draft and completed events

= 2.6.6 =
* Improve reliability of the events displayed via the api


= 2.6.5 =
* Fix to calendar template to display calendar even when zero events

= 2.6.4 =
* Removed dependency on Keyring plugin

= 2.6.3 =
* Update readme for 5.0.1

= 2.6.2 =
* minor change to admin style for 5.0 block editor

= 2.6.1 =
* wording change

= 2.5 =
* code change to widget template to allow descriptions with percent signs

= 2.4 =
* added Jetpack Photon filter to stop Jetpack corrupting Eventbrite Image URLs

= 2.3 =
* minor Freemius change

= 2.2 =
* minor template tweaks

= 2.1 =
* refactored vendor dir and removed uneeded files

= 2.0 =
* Incorporated direct calls to Eventbrite API, removing need for Eventbrite API plugin


= 1.15 =
* minor change
* pro filter location

= 1.14 =
* minor change

= 1.13 =
* added options to specify open in new tab or not
* new calendar template


= 1.12 =
* shortcode features

= 1.11 =
* tweak dates on templates

= 1.10 =
* skipped

= 1.9 =
Add grid templates

= 1.8 =
Added Divi template and default CSS

= 1.7 =
Updated to support PHP 7.2

= 1.6 =
* code rationalisation

= 1.5 =
* Removed custom CSS in widget area, use the Additional CSS section in Customizer to override widget CSS.
* bug fix for link hover text
* refactor
* Removed spurious characters on excerpt

= 1.4 =
* clean up redundant code

= 1.3 =
* refactor code
* 4.8.1 tested

= 1.2 =
* Fix link on placeholder image to now go to EventBrite
* Fix link on excerpt readmore to now go to EventBrite
* Add code for book now button
* Change wording to reflect the excerpt is from description


= 1.1 =
* Fix to allow limit change

= 1.0 =
* First Release
