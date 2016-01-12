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
 * Availability password - Frontend form
 *
 * @package     availabiliy
 * @subpackage  availabiliy_password
 * @copyright   2016 Davo Smith, Synergy Learning UK on behalf of Alexander Bias, University of Ulm <alexander.bias@uni-ulm.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_password;

defined('MOODLE_INTERNAL') || die();

class frontend extends \core_availability\frontend {
    protected function get_javascript_strings() {
        return ['title', 'error_setpassword'];
    }

    protected function allow_add($course, \cm_info $cm = null, \section_info $section = null) {
        return ($section === null); // Can only be added to modules, not sections.
    }
}
