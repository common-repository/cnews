# CNews

Wordpress plugin for sending out notifications by html email from the add/edit post/page/cpt screen.

## Features

- Use a wysiwyg editor to write and send html emails to user groups from the add/edit post/page/cpt screen.
- View all emails sent from the post/page/cpt directly from the add/edit post/page/cpt screen.
- Choose the post types that you would like to see the send mail interface from.
- Lets the users decide if they want to receive emails.

The plugin uses wp_mail() to send out mails so with the default settings there are limitations.

- Some host providers has a limit for how many emails there can be sent out every hour.
- If the mail() function bugs, not all emails will be sent out.

These limitations can be overcome by using a 3 party plugin that uses an external service to send out mails by hooking into wp_mail()

# Installation

Download the plugin and either unzip it and upload it to the WordPress plugin folder or upload it through the WordPress plugin menu.

Activate the plugin and go to settings -> CNews. Here you can choose the post types you wish to be able to send out emails from and set the name and email address that will be shown in the email header when sent out.

## Information

- Contributors: [Casper Schultz](https://www.casperschultz.dk)
- Tags: Email, Notifications, News
- Tested: Up to 4.6
- License: GNU GPLv3




