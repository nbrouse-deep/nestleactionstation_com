=== Password only login ===
Contributors: calvaweb
Donate link: 
Tags: password, login, form
Requires at least: 3.3
Tested up to: 3.8
Stable tag: 0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Log into the site using just your password. Restricted to specific users selected via the wordpress backend. 

== Description ==

Select one or more user accounts that can login to the site using just their password via a custom login form supplied with the plugin. For security purposes, only user accounts with subscriber role can be selected and any accounts selected will be prevented from accessing the wordpress backend. The custom login/logout form is added to a template file using a PHP function 'pol_showform()'. Like with the standard login form, if you pass 'redirect_to' on the querystring, it will use this as the url to redirect to after the login/logout.

== Installation ==

1. Upload `password-only-login` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php if (function_exists('pol_showform')) pol_showform(); ?>` in your templates

== Frequently asked questions ==


== Screenshots ==



== Changelog ==



== Upgrade notice ==



== Arbitrary section 1 ==

