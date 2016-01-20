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
 * Availability password - Settings file
 *
 * @package     availabiliy
 * @subpackage  availabiliy_password
 * @copyright   2016 Davo Smith, Synergy Learning UK on behalf of Alexander Bias, University of Ulm <alexander.bias@uni-ulm.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Sadly, this is currently unsupported by Moodle - leaving in place in case it is in the future.

$opts = [
    'db' => new lang_string('permanently', 'availability_password'),
    'session' => new lang_string('untillogout', 'availability_password')
];
$setting = new admin_setting_configselect('availability_password/remember',
                                          new lang_string('rememberpassword', 'availability_password'), '', 'db', $opts);
$settings->add($setting);
