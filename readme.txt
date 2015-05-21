=== Plugin Name ===
Contributors: Ippei Sumida
Donate link:
Tags: calendar, doorkeeper
Requires at least: 4.2.0
Tested up to: 4.2.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display Doorkeeper event calendar in your page.

== Description ==

This plugin shows calendar of Doorkeeper's event.
You can deploy this calendar in your wordpress at few step.


This plugin uses,
Doorkeeper API - http://www.doorkeeperhq.com/developer/api
Full Calendar - http://fullcalendar.io

== Installation ==

1. Upload `DoorkeeperCalendar` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set a Doorkeeper's group id  through the 'Doorkeeper設定'.
4. Place `<?php echo $dkCalendar->get_calendar(); ?>` in your templates.
5. Or use shortcode [doorkeeper_calendar].

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 0.2 =
* Add a shortcode [doorkeeper_calendar].

== Upgrade Notice ==

== Arbitrary section ==
