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
 * Availability password - Unit tests for the condition
 *
 * @package    availability_password
 * @copyright  2026 Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_password;

use core_availability\info_module;
use core_availability\tree;

/**
 * Unit tests for the password condition.
 *
 * @package    availability_password
 * @copyright  2026 Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \availability_password\condition
 */
final class condition_test extends \advanced_testcase {
    /**
     * Enable availability and reset the static cache of accepted passwords before each test.
     *
     * The cache is keyed by cmid only and survives between tests, while the cmids are reused after each reset.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        set_config('enableavailability', 1);
        $this->reset_passwords_cache();
    }

    /**
     * Reset the static cache of accepted passwords in the condition class.
     */
    private function reset_passwords_cache(): void {
        $property = new \ReflectionProperty(condition::class, 'passwordsaccepted');
        $property->setValue(null, null);
    }

    /**
     * Build the availability JSON for a single password condition.
     *
     * @param mixed $password The configured password.
     * @return string
     */
    private function get_availability_json($password): string {
        return json_encode(tree::get_root_json([(object) ['type' => 'password', 'password' => $password]]));
    }

    /**
     * Create a course with a page which is restricted by the given password and an enrolled student.
     *
     * @param mixed $password The configured password.
     * @return array The course, the cm_info of the page and the student.
     */
    private function create_restricted_page($password): array {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $student = $generator->create_and_enrol($course, 'student');
        $page = $generator->create_module('page', [
            'course' => $course->id,
            'availability' => $this->get_availability_json($password),
        ]);
        $cm = get_fast_modinfo($course)->get_cm($page->cmid);
        return [$course, $cm, $student];
    }

    /**
     * Check whether the given cm is available for the given user.
     *
     * @param \cm_info $cm The course module.
     * @param int $userid The user ID.
     * @return bool
     */
    private function is_available(\cm_info $cm, int $userid): bool {
        $info = new info_module($cm);
        $information = '';
        return $info->is_available($information, false, $userid);
    }

    /**
     * Data provider for test_submit_password.
     *
     * @return array
     */
    public static function submit_password_provider(): array {
        return [
            'Correct password' => ['secret', 'secret', true],
            'Wrong password' => ['secret', 'wrong', false],
            'Password with different case' => ['secret', 'Secret', false],
            'Scientific notation password matched by zero' => ['0e1337', '0', false],
            'Scientific notation password matched by another zero value' => ['0e1337', '0e42', false],
            'Scientific notation password matched exactly' => ['0e1337', '0e1337', true],
            'Numeric password matched by scientific notation' => ['1000', '1e3', false],
            'Numeric password matched with leading zero' => ['1', '01', false],
            'Numeric password matched exactly' => ['1000', '1000', true],
            'Zero password matched by empty string' => ['0', '', false],
            'Zero password matched exactly' => ['0', '0', true],
            'Integer password in the JSON structure' => [1234, '1234', true],
        ];
    }

    /**
     * Test that submitted passwords are compared strictly with the configured password.
     *
     * @dataProvider submit_password_provider
     * @param mixed $configured The configured password.
     * @param string $submitted The submitted password.
     * @param bool $expected Whether the submitted password is expected to be accepted.
     */
    public function test_submit_password($configured, string $submitted, bool $expected): void {
        [, $cm, $student] = $this->create_restricted_page($configured);
        $this->setUser($student);

        $this->assertFalse($this->is_available($cm, $student->id));
        $this->assertSame($expected, condition::submit_password_for_cm($cm, $submitted));
        $this->assertSame($expected, $this->is_available($cm, $student->id));
    }

    /**
     * Test that an empty password is never accepted, even if the condition has no password configured.
     */
    public function test_submit_empty_password(): void {
        global $DB;

        [$course, $cm, $student] = $this->create_restricted_page('secret');
        $availability = json_encode(tree::get_root_json([(object) ['type' => 'password']]));
        $DB->set_field('course_modules', 'availability', $availability, ['id' => $cm->id]);
        rebuild_course_cache($course->id, true);
        $cm = get_fast_modinfo($course)->get_cm($cm->id);
        $this->setUser($student);

        $this->assertFalse(condition::submit_password_for_cm($cm, ''));
        $this->assertFalse($this->is_available($cm, $student->id));
    }

    /**
     * Test that an accepted password does not grant access anymore after the password was changed
     * to a value which is only loosely equal to the old one.
     */
    public function test_changed_password(): void {
        global $DB;

        [$course, $cm, $student] = $this->create_restricted_page('0e111');
        $this->setUser($student);

        $this->assertTrue(condition::submit_password_for_cm($cm, '0e111'));
        $this->assertTrue($this->is_available($cm, $student->id));

        $DB->set_field('course_modules', 'availability', $this->get_availability_json('0e222'), ['id' => $cm->id]);
        rebuild_course_cache($course->id, true);
        $cm = get_fast_modinfo($course)->get_cm($cm->id);

        $this->assertFalse($this->is_available($cm, $student->id));
    }
}
