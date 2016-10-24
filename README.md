moodle-availability_password
============================

Moodle availability plugin which lets users restrict resources and activities with password access


Requirements
------------

This plugin requires Moodle 3.1+


Changes
-------

* 2016-07-19 - Check compatibility for Moodle 3.1, no functionality change
* 2016-05-26 - Fix javascript error when viewing new assign grading page on Moodle 3.1 - Credits to Davo Smith
* 2016-02-10 - Update README section about availability conditions settings pages
* 2016-02-10 - Change plugin version and release scheme to the scheme promoted by moodle.org, no functionality change
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


Protection course sections
--------------------------

At the moment, availability_password can't be used to protect course sections, it can only be applied to single activities / resources in a course.

For those who are curious: There is a technical reason for this restriction. Most availability plugins use some external data to decide on the availability. This plugin uses data which is internal to the plugin itself. As availability data is stored in a serialised object in the course_modules table, it has no unique identifying ID of its own. Instead we use the course module id to be the link between the passwords already entered, the particular availability condition and the password being attempted (as appropriate).
It would be possible to rework the plugin to work with sections as well by processing the availability information in the course_sections table as well, but, as that was not our goal, we did not attempt the (slightly tricky) reworking to allow this yet.

If you really want availability_password to protect course sections, please let us know on https://github.com/moodleuulm/moodle-availability_password/issues or, ideally, submit a pull request on https://github.com/moodleuulm/moodle-availability_password/pull.


Granting only temporary access
------------------------------

At the moment, if a student has input the correct password for an activity / resource, availability_password remembers this fact and grants access for this particular student until forever (Special case: Or until, for whatever reason, a teacher changes the password for the activity / resource).

However, there might be scenarios with even higher security demands which make it necessary that a student inputs the password every time he wants to re-access the activity / resource after a login and to have Moodle forget the fact that the user has input the correct password after he has logged out or his Moodle session has expired.

availability_password also supports this use case. Go to Site administration -> Plugins -> Availability restrictions -> Restriction by password and change the "Remember password entered" setting to "Until the user logs out".

Note: 
Moodle core did not support settings pages for availability conditions until 2.9.5 and 3.0.3 (see https://tracker.moodle.org/browse/MDL-49620).

So, if your are running a legacy version of Moodle and you really need to limit the memory of availability_password to a user's session, you have to set the plugin's configuration directly in the DB. This can be done with this SQL command:

INSERT INTO mdl_config_plugins ("plugin", "name", "value") VALUES ('availability_password', 'remember', 'session');

Please only run this SQL command if you really know what you are doing. After running the SQL command, you might have to clear your Moodle cache for the change to take effect.


Restricting usage
-----------------

The feature which availability_password provides is necessary / useful in specialized scenarios / environments. However, you might not want every teacher in your Moodle instance to be able to add passwords to their activities / resources because this will put additional barriers between your students and your content and might even harm the acceptance of Moodle among students in your institution.

Because of that and in contrast to other availability plugins, availability_password supports a capability availability/password:addinstance which lets you control who is able to add this condition to activities / resources and who is not. By default, the capability is granted to managers, coursecreators and editing teachers at plugin installation time, but feel free to change this setup within your Moodle role configuration.

By the way, if teacher A who has this capability adds the condition to an activity / resource and teacher B who has not the capability edits this activity / resource, B is able to see, edit and delete the condition of this particular activity / resource, but is still not allowed to add the condition to another activity / resource in the course.


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

Due to limited resources, availability_password is only maintained for the most recent major release of Moodle. However, previous versions of this plugin which work in legacy major releases of Moodle are still available as-is without any further updates in the Moodle Plugins repository.

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

Ulm University
kiz - Media Department
Team Web & Teaching Support
Alexander Bias
