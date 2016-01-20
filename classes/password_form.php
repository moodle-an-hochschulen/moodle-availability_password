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
 * Availability password - Simple form for entering the password
 *
 * @package     availabiliy
 * @subpackage  availabiliy_password
 * @copyright   2016 Davo Smith, Synergy Learning UK on behalf of Alexander Bias, University of Ulm <alexander.bias@uni-ulm.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_password;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir.'/formslib.php');

class password_form extends \moodleform {
    protected function definition() {
        $mform = $this->_form;
        /** @var \cm_info $cm */
        $cm = $this->_customdata['cm'];

        $mform->addElement('hidden', 'id', $cm->id);
        $mform->setType('id', PARAM_INT);

        $label = get_string('passwordintro', 'availability_password', $cm->get_formatted_name());
        $mform->addElement('static', 'passwordintro', '', $label);

        $label = get_string('enterpasswordfor', 'availability_password');
        $mform->addElement('password', 'activitypassword', $label, array('maxlength' => 255));
        $mform->addRule('activitypassword', null, 'required');
        $mform->addRule('activitypassword', null, 'maxlength', 255);
        $mform->setType('activitypassword', PARAM_RAW);

        $this->add_action_buttons(true, get_string('submit'));
    }
}
