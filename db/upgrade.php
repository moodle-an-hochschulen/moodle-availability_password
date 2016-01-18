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
 * DB upgrade steps
 *
 * @package   availability_password
 * @copyright 2016 Davo Smith, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_availability_password_upgrade($oldversion = 0) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2016011800) {

        // Define table availability_password_avail to be renamed to NEWNAMEGOESHERE.
        $table = new xmldb_table('availability_password_avail');

        // Launch rename table for availability_password_avail.
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'availability_password_grant');
        }

        // Password savepoint reached.
        upgrade_plugin_savepoint(true, 2016011800, 'availability', 'password');
    }

    return true;
}
