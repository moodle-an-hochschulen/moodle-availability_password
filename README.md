moodle-availability_password
============================

Moodle availability plugin which lets users restrict resources and activities with password access


Requirements
------------

This plugin requires Moodle 3.0+


Changes
-------

* 2016-01-01 - Initial version


Installation
------------

Install the plugin like any other plugin to folder
/availability/condition/password

See http://docs.moodle.org/en/Installing_plugins for details on installing Moodle plugins


Usage
-----

After installing, availability_password is ready to use without the need for any configuration.

Teachers (and other users with editing rights) can add the "Password" availability condition to activities / resources in their courses. While adding the condition, they have to define the password which will be requested from students before they can access the activity / resource for the first time. For subsequent access, the availability plugin remembers that the student has already given the correct password once and does not bug him anymore.

If you want to learn more about using availability plugins in Moodle, please see https://docs.moodle.org/en/Restrict_access.


Themes
------

availability_password should work with all Bootstrap based Moodle themes.
availability_password provides a fallback for browsers with JavaScript disabled.


Further information
-------------------

availability_password is found in the Moodle Plugins repository: http://moodle.org/plugins/view/availability_password

Report a bug or suggest an improvement: https://github.com/moodleuulm/moodle-availability_password/issues


Moodle release support
----------------------

Due to limited ressources, availability_password is only maintained for the most recent major release of Moodle. However, previous versions of this plugin which work in legacy major releases of Moodle are still available as-is without any further updates in the Moodle Plugins repository.

There may be several weeks after a new major release of Moodle has been published until we can do a compatibility check and fix problems if necessary. If you encounter problems with a new major release of Moodle - or can confirm that availability_password still works with a new major relase - please let us know on https://github.com/moodleuulm/moodle-availability_password/issues


Right-to-left support
---------------------

This plugin has not been tested with Moodle's support for right-to-left (RTL) languages.
If you want to use this plugin with a RTL language and it doesn't work as-is, you are free to send me a pull request on
github with modifications.


Copyright
---------

Davo Smith
Synergy Learning UK
www.synergy-learning.com

on behalf of

University of Ulm
kiz - Media Department
Team Web & Teaching Support
Alexander Bias
