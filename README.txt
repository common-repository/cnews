=== CNews ===
Contributors: caspersch
Tags: Email, News, Notifications, Users
Requires at least: 4.0
Tested up to: 4.6
Stable tag: 1.0.1
License: GNU GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Write and send html emails to users registered on the WordPress site.

Write the emails using the form at the post/page/cpt screens.

Easy overview of the emails sent out from each post/page/cpt screen.

The users decides for them self if they would like to receive notifications or not.


Imagine that you have a custom post type called classes, now at the class page, all information about each class i shown. The classes has students and teachers.
Lets say you want to notify the students that a class has been cancelled, you go to the edit class page and write an email that you send out to all the students.

The next person that goes to the edit class screen can easely see the email that has been sent out, and who it has been sent to, so the students wan't receive several emails because different people send them out.

== Description ==
Wordpress plugin for sending out notifications by html email from the add/edit post/page/cpt screen.

Use a wysiwyg editor to write and send html emails to user groups from the add/edit post/page/cpt screen.

View all emails sent from the post/page/cpt directly from the add/edit post/page/cpt screen.

Choose the post types that you would like to see the send email wysiwyg editor and overview from.

Lets the users decide if they want to receive emails.

The plugin uses wp_mail() to send out mails so with the default settings there are limitations.

Some host providers has a limit for how many emails there can be sent out every hour.
If the mail() function bugs, not all emails will be sent out.
Sending out mails from servers on shared hosting has a tendency to end up in the spam folder in the users email client.

These limitations can be overcome by using a 3 party plugin that uses an external service to send out mails by hooking into wp_mail()

== Installation ==
Download the plugin and either unzip it and upload it to the WordPress plugin folder or upload it through the WordPress plugin menu.

Activate the plugin

Go to settings -> CNews. Here you can choose the post types you wish to be able to send out emails from and set the name and email address that will be shown in the email header.


== Screenshots ==
1. Write html emails using a wysiwyg editor and send out the emails to the usergroups registered on the site.
2. View all emails send out from each post.
3. It all happens directly at the page/post/edit screens.

== Changelog ==

Version 1.0.1

Fixed error that made it impossible to delete the plugin.
