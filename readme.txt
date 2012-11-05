=== Object Oriented Plugin Template Solution ===
Contributors: convissor
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=danielc%40analysisandsolutions%2ecom&lc=US&item_name=Donate%3a%20Object%20Oriented%20Plugin%20Template%20Solution&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: plugin, template, skeleton, object oriented, settings api, multisite, i18n, translation, phpunit
Requires at least: 3.3
Tested up to: 3.5beta1
Stable tag: trunk

A well engineered template for creating plugins using object-oriented programming practices. Uses Settings API, multisite, i18n, PHPUnit tests.


== Description ==

Gives authors of new plugins a leg up on creating a great, easy to
maintain plugin by providing a carefully designed plugin skeleton to build on.
Authors of existing plugins can extract individual components and concepts
for transplantation into their own projects.

* Clean, object-oriented design
* PHPUnit tests
* Admin screen uses the Settings API
* Multisite support
* Creates a table during activation
* Drops the table and settings during deactivation
* Uses WordPress' i18n and provides scripts for generating the gettext files
* Installation instructions have a script for renaming files, classes and IDs

See the FAQ section for more details about this plugin's features.

Development of this plugin template happens on
[GitHub](https://github.com/convissor/oop-plugin-template-solution).
Releases are then squashed and pushed to WordPress'
[Plugins SVN repository](http://plugins.svn.wordpress.org/oop-plugin-template-solution/).
This division is necessary due having being chastised that "the Plugins SVN
repository is a release system, not a development system."

Please submit
[bug and feature requests](https://github.com/convissor/oop-plugin-template-solution/issues),
[pull requests](https://github.com/convissor/oop-plugin-template-solution/pulls),
[wiki entries](https://github.com/convissor/oop-plugin-template-solution/wiki)
there.


== Installation ==

1. Download the zip file from WordPress' plugin
    site: `http://wordpress.org/extend/plugins/oop-plugin-template-solution/`

1. Unzip the file

1. Here are some semi-automated steps to copy this plugin and rename the
    files, class names, and identifiers.  The commands are in Bash,
    adjust them as needed for your environment.  Replace the three mentions
    of "My Plugin" in the settings section with the name of your plugin.

        # Settings -----
        # Plugin identifier / directory (hyphen separated).
        old_id=oop-plugin-template-solution
        new_id=my-plugin

        # Class name (underscore separated).
        old_class=oop_plugin_template_solution
        new_class=my_plugin

        # Plugin Name (space separated).
        old_name="Object Oriented Plugin Template Solution"
        new_name="My Plugin"
        # --------------


        # Copy and rename the files.
        cp -R $old_id $new_id
        cd $new_id
        mv $old_id.php $new_id.php

        # Replace strings in the files.
        find . -type f -exec sed "s/$old_id/$new_id/g" -i {} \;
        find . -type f -exec sed "s/$old_class/$new_class/g" -i {} \;
        find . -type f -exec sed "s/$old_name/$new_name/g" -i {} \;
        find . -type f -exec sed -E "s/^ \* (Author:|Author URI:|@author|@copyright) (.*)$/ * \1/g" -i {} \;
        find . -type f -exec sed "s@REPLACE_PLUGIN_URI@http://wordpress.org/extend/plugins/oop-plugin-template-solution/@g" -i {} \;
        sed -E "s/^(Contributors|Donate link|Tags): (.*)$/\1:/g" -i readme.txt

1. Now get down to making the plugin do what you want.  See the FAQ
   for instructions about particular aspects.

1. Upload your plugin directory to your server's `/wp-content/plugins/`
    directory

1. Activate the plugin using WordPress' admin interface:
    * Regular sites:  Plugins
    * Sites using multisite networks:  My Sites | Network Admin | Plugins

= Removal =

1. This plugin offers the ability to remove all of this plugin's settings
    from your database.  Go to WordPress' "Plugins" admin interface and
    click the "Settings" link for this plugin.  In the "Deactivate" entry,
    click the "Yes, delete the damn data" button and save the form.

1. Use WordPress' "Plugins" admin interface to click the "Deactivate" link

1. Remove the plugins directory from the server


== Frequently Asked Questions ==

= Multisite Networks =

This plugin is coded to be installed in either a regular, single WordPress
installation or as a network plugin for multisite installations.  So, by
default, multisite networks can only activate this plugin via the Network Admin
panel.

If you want your plugin to be configurable for each site in a multisite
network, follow the instructions in the docblock at the top of `admin.php`.


= Settings API =

We add some abstraction around WordPress'
[Settings API](http://codex.wordpress.org/Settings_API).  All you need to do
is add some elements to two arrays and maybe create a section header if you
want.  This is way better than having to write out `add_settings_field()`
calls and creating display and validation callbacks for each and every field.

1. Open `admin.php` in your favorite text editor

1. Read the docblock at the top of the file


= Unit Tests =

This framework uses PHPUnit, so standard PHPUnit file, class, and method
naming practices apply.  Our framework requires that your test files and
classes:

* Have a `require_once` call for `TestCase.php` at the top of the script.
  That obtains the PHPUnit and other items needed.  It's the only file you
  need to include.
* Classes must extend `TestCase`
* If you add a `setUpBeforeClass()` method, it must
  call `parent::setUpBeforeClass()`
* If you add a `setUp()` method, it must call `parent::setUp()`
* If you add a `tearDown()` method, it must call `parent::tearDown()`
* If you add a `tearDownAfterClass()` method, it must
  call `parent::tearDownAfterClass()`

Take a look at the `TestLogin.php` script for examples of how to handle
calls to `wp_mail()` (and translations of mail messages) and `wp_redirect()`,
the use of database savepoints, and manipulating user metadata.

Please note that the tests make extensive use of database transactions.
Many tests will be skipped if your `wp_options` and `wp_usermeta` tables
are not using the `InnoDB` storage engine.

To execute the tests, install and activate the plugin, then use a shell
to `cd` into this plugin's directory and call `phpunit tests`

While it is possible to test plugins using [WordPress' Automated
Testing](http://codex.wordpress.org/Automated_Testing) PHPUnit framework, it
is a complex system, is another dependency, and runs in its own environment.
The benefit of using my plugin's PHPUnit is that it ships with the plugin
and executes in the users actual WordPress installation.  This means any end
user can easily test how the plugin interacts with their site.


= Translations =

To produce the machine readable translations used by WordPress' gettext
implementation, use the scripts I made for generating all of
the `.pot`, `.po` and `.mo` files:

* `cd languages`
* `./makepot.sh`
* Update the headers, version number, etc in the `.pot` file as desired.
* To add a new language: `touch <plugin-id>-<lc>_<CC>.mo`  Substitutions:
    plugin-id: the plugin's identifier ($new_id from above)
    lc: language code
    CC: country code
* `./updatepos.sh`
* Fill the translated text in the `.po` files.
* `./makemos.sh`


== Changelog ==

= 1.0.2 (2012-11-05) =
* Explain why can't use PHPUnit's @expectedException functionality.

= 1.0.1 (2012-11-05) =
* Clarify instructions and descriptions.

= 1.0.0 (2012-11-05) =
* Initial release.
