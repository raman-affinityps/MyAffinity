=== p5 : Plenty of Perishable Passwords for Protected Posts ===
Contributors: cyrilbatillat
Tags: password, protected posts, expiration
Requires at least: 3.5
Tested up to: 3.9.1
Stable tag: 1.4
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Specify multiple passwords for pages / posts / custom post  types. An expiration date can be set for each password.

== Description ==

By default, Wordpress can protect each post with one and only password. This plugin gives you the possibility to assign multiple passwords on each post, with an expiration date.

== Installation ==

1. Upload plugin folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Specify WordPress timezone on General Settings screen (/wp-admin/options-general.php). If possible, choose a real timezone (eg 'Europe/London') which may be more accurate than offsets ('+2:00') in some cases. 
1. Be sure that your template files use the WordPress function to protect your content :

`<?php
if ( post_password_required() ) { ?>
    <div id="form-password">
        <?php echo get_the_password_form(); ?>
    </div>
    <?php
}
?>`
See [WordPress codex](http://codex.wordpress.org/Using_Password_Protection) for more info.

== Frequently asked questions ==

= What happens when a password expire ? =
The password is deleted from the database, so it is no longer attached to your post.

= Expired passwords aren't deleted. Why ? =
The plugin use WordPress cron feature to periodically delete expired passwords. Please make sure this functionnality is working on your WordPress installation. [WP-Cron Control](http://wordpress.org/plugins/wp-cron-control/) plugin is a good way to see what's happening with the cron.

= My post is no longer protected. Why ? =
In WordPress, a post is protected as long as it has a password attached. When all the post passwords have expired, the post is no longer protected. It's as simple as that.
To keep a post protected, assign it a password without an expiration date.

= Are my already defined passwords conserved after installation ? =
Yes.

= Are my password-protected posts still protected when I deactivate/uninstall p5 plugin ? =
Yes. After deactivation or uninstallation, your posts are still protected with the first password that was attached to each of them. 

= My password is supposed to be expired, but I still can see my protected content =
Be sure that the timezone is well defined in /wp-admin/options-general.php

= Does this plugin provide some hooks ? =
Yes. Actually these actions are defined : 

1. p5_insert_password, called after insertion of a new password
1. p5_update_password, called after password update
1. p5_save_password, called indifferently after p5_insert_password or p5_update_password.
1. p5_delete_password, after a password has been deleted

== Screenshots ==

1. A protected post with multiple passwords

== Changelog ==

= 1.4 =
- Fixed bug on cookie expiration date, due to difference of timezone between WordPress and the client
- Minor improvements for WP UI
- Updated jQuery Timepicker Addon

= 1.3 =
Get ready for languages packs (WP 3.7.1 feature)

= 1.2 =
Workaround to url_to_postid getting bugged. (see http://core.trac.wordpress.org/ticket/19744)
The post ID was not retrieved on custom post types.

= 1.1 =
Use CSS scope on jQuery UI datetime picker to avoid collisions

= 1.0 =
First release