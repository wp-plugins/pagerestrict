=== Page Restrict ===
Contributors: sivel
Tags: pages, page, restrict, restriction, logged in, cms
Requires at least: 2.6
Tested up to: 2.7
Stable tag: 1.5

Restrict certain pages to logged in users.

== Description ==

Restrict certain pages to logged in users

This plugin will allow you to restrict all, none, or certain pages to logged in users only.  

In some cases where you are using WordPress as a CMS and only want logged in users to have access to the content or where you want users to register for purposes unknown so that they can see the content, then this plugin is what you are looking for.

Simple admin interface to select all, none, or some of your pages.  This does not work for posts, only pages.

== Installation ==

1. Upload the `pagerestrict` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

NOTE: See "Other Notes" for Upgrade and Usage Instructions as well as other pertinent topics.

== Screenshots ==

1. Login Form
2. Admin Page

== Upgrade ==

1. Deactivate the plugin through the 'Plugins' menu in WordPress
1. Delete the previous `pagerestrict` folder from the `/wp-content/plugins/` directory
1. Upload the new `pagerestrict` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Usage ==

1. Visit Settings>Page Restrict in the admin area of your blog.
1. Select your restriction method (all, none, selected).
1. If you chose selected, select the pages you wish to restrict.
1. Enjoy.

== Change Log ==

= 1.6 (2009-02-xx): =
* Replaced while loop with foreach for display list of pages 
* Added meta box to write/edit pages page
* Added capability to display or not display the login form
* Updated admin styling
* Restrict commeting or viewing comments on restricted pages
* Restrict search results also so restricted pages are not shown

= 1.5 (2008-09-03): =
* Added ability to change restriction method
* Rewrote and simplified areas pertaining to the list of pages

= 1.4.1 (2008-08-25): =
* Added back no_cache add_action that was lost in the admin separation
* Removed duplicate add_action for the admin page

= 1.4 (2008-08-25): =
* Updated version number scheme
* Updated code for readability
* Moved admin functionality to separate file included only when is_admin is true

= 0.3.1 (2008-08-16): =
* Updated for PHP4 Support
* Restored end PHP tag at end of script

= 0.3 (2008-08-13): =
* Initial Public Release
