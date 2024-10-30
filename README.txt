=== Easily integrate SendGrid with your WordPress site ===
Plugin Name: Contact Manager for SendGrid
Description: Makes it easy to automatically add contacts to SendGrid marketing lists
Author: Zeb Fross
Contributors: Zeb Fross
Tags: sendgrid, wpforms, email marketing
Version: 1.0
Stable tag: 1.0
Requires PHP: 8.0.10
Requires at least: 5.3
Tested up to: 5.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Contact Manager for SendGrid lets you automatically add new users to a specific SendGrid list.  It also integrates with wpforms to add contacts to SendGrid from any form.

####Privacy Policy & Terms of Service

I don't use or even receive any data from this plugin, and you can use it however you want.

== Installation ==

####Option 1:

- Go to the Add New plugins screen in your WordPress admin area.
- Using the search box, find the Contact Manager for SendGrid plugin and click the install button and then activate the plugin.

== Usage Instructions ==

You will need a SendGrid API Key which can be found on the SendGrid dashboard under Settings -> API Keys.

Once you've downloaded and installed this plugin on your site, go to the Settings -> Contacts for SendGrid submenu to set your SendGrid API Key.

If you want to add users to a SendGrid list from a WPForms form, open the WPForms form and click on Settings -> SendGrid to specify your list and to map fields.

If you want to add new users to a list (like Subscribers), you can enable that option and choose which SendGrid list to add them to.

== Actions and Filters ==

cmfs-add-on-register - This lets you customize when to add new users to your SendGrid contacts.  You could use this to only add new users in a specific role.
cmfs-register-list - This lets you customize which list new users are added to.  You could use this to change the list based on user role.

== Frequently Asked Questions ==

= How do I use this thing? =

Check the Usage Instructions above, but basically set the SendGrid API Key in Settings -> Contacts for SendGrid and then set your list in your WPForms form Settings -> SendGrid.

== Screenshots ==

1. General settings
2. WPForms settings

== Contact ==

zebfross@hotmail.com

== Changelog ==

= 1.0.0 =

*   Stable version
