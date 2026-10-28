<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Availability password - Upgrade script
 *
 * @package    availability_password
 * @copyright  2018 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to upgrade availability_password.
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_availability_password_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2018051605) {
        // Remove remembered passwords for non-existing courses because this hasn't been handled with an event handler before.
        $allcoursesinmoodle = $DB->get_fieldset_select('course', 'id', '');
        $allcourseswithpasswords = $DB->get_fieldset_select('availability_password_grant', 'courseid', '');
        foreach ($allcourseswithpasswords as $c) {
            if (!in_array($c, $allcoursesinmoodle)) {
                $DB->delete_records('availability_password_grant', ['courseid' => $c]);
            }
        }

        // Remove remembered passwords for non-existing users because this hasn't been handled with an event handler before.
        $allusersinmoodle = $DB->get_fieldset_select('user', 'id', 'deleted = :deleted', ['deleted' => '0']);
        $alluserswithpasswords = $DB->get_fieldset_select('availability_password_grant', 'userid', '');
        foreach ($alluserswithpasswords as $u) {
            if (!in_array($u, $allusersinmoodle)) {
                $DB->delete_records('availability_password_grant', ['userid' => $u]);
            }
        }

        upgrade_plugin_savepoint(true, 2018051605, 'availability', 'password');
    }

    if ($oldversion < 2026100501) {
        // Replace the cleartext passwords in the grant table with salted hashes.
        $dbman = $DB->get_manager();
        $table = new xmldb_table('availability_password_grant');

        // Add the passwordhash field, nullable for now as the existing records do not have a hash yet.
        $field = new xmldb_field('passwordhash', XMLDB_TYPE_CHAR, '64', null, null, null, null, 'userid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Hash the existing cleartext passwords.
        // The hash has to match \availability_password\condition::get_password_hash(). It is computed here directly
        // to keep this upgrade step independent of later changes to the condition class.
        $oldfield = new xmldb_field('password');
        if ($dbman->field_exists($table, $oldfield)) {
            $recs = $DB->get_recordset('availability_password_grant', null, '', 'id, cmid, password');
            foreach ($recs as $rec) {
                $passwordhash = hash('sha256', $rec->cmid . ':' . $rec->password);
                $DB->set_field('availability_password_grant', 'passwordhash', $passwordhash, ['id' => $rec->id]);
            }
            $recs->close();
        }

        // Now that all records have a hash, make the passwordhash field NOT NULL.
        $field = new xmldb_field('passwordhash', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null, 'userid');
        $dbman->change_field_notnull($table, $field);

        // Drop the password field which contained the cleartext passwords.
        if ($dbman->field_exists($table, $oldfield)) {
            $dbman->drop_field($table, $oldfield);
        }

        upgrade_plugin_savepoint(true, 2026100501, 'availability', 'password');
    }

    return true;
}
