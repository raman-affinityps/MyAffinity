=== Members Import ===
Contributors: manishkrag
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=soumi.das1990@gmail.com&item_name=Members%20Import%20&return=http://www.youngtechleads.com/thanks/
Tags: csv, user, import, users, member, members, batch, batch import, batch import members, batch import users, one click user creation, import csv, import from csv, user registration, wordpress members import,wordpress csv import, wordpress users import, buddypress, buddypress members import
Stable tag: 1.3
Tested up to: 4.5

This plug-in allows you to batch import of users/members taken from an uploaded CSV file.

== Description ==
Latest Version @ <a href="http://youngtechleads.com/buddypress-members-import/">BuddyPress Members Import</a>
This allows you to batch import of users/members taken from an uploaded CSV file.

It will add users/members with basic information, including firstname, lastname, username, password and email address.
Each user who is added will be a 'subscriber' if value not provided in csv file, and be able to login to your site.

You can also choose to send a notification to the new users and to display password nag on user login.

There are no additional options available at the moment, but if you want to add a bunch of users in one go, this will do this for you quickly.

<b>Basic Fields:</b>
<br />user_login, user_pass, user_email, user_url,
<br />user_nicename, display_name, user_registered,
<br />first_name, last_name, nickname, description,
<br />rich_editing, comment_shortcuts, admin_color,
<br />use_ssl, show_admin_bar_front, show_admin_bar_admin,
<br />role

**Pro version plugin name** 
<br /> BuddyPress Members Import http://www.youngtechleads.com/buddypress-members-import/

== Features ==

* Create users/members in wordpress site in a single click.
* Imports all users/members fields.
* Imports user/members meta fields.
* Allows setting user role.
* Sends new user notification (if the option is selected)
* Shows password nag on user login (if the option is selected)
* If username already present in database then this user will not register, in this way we can avoid the multi use of same username.

== Installation ==

* Upload `members-import` to the `/wp-content/plugins/` directory.
* Activate the plugin through the 'Plugins' menu in WordPress.
* You will see a new 'Members Import' option under the existing 'Users' menu area.
* Data in csv file should be delimitted by double quote(").

== Frequently Asked Questions ==

= CSV file format? =

* CSV file should have all the field name separated by comma(,) and delimitted by double quote(") in first row.
* Data present from the second row are assume as data to be import.
* In a row all the data should be separated by comma(,) and delimitted by double quote(").

== Changelog ==

= 1.3 =
* Fatal Error fixed
= 1.2 =
* No, more password send via email. According to WP 4.3
* Tested with WP 4.5
* Removed password Nag feature, no more required.
* Optimized code.
* Fixed couple of warnings.
* Thanks to team youngtechleads to join.
= 1.1 =
* If you are using email id as user login/username then in CSV use only one and select first check box before import.
= 1.0.0 =
* First release of plug-in. No changes.
