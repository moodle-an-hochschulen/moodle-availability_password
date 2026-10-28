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
 * Availability password - Test the privacy API functions
 *
 * @package    availability_password
 * @copyright  2019 Davo Smith, Synergy Learning UK on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_password\privacy;

/**
 * Class availability_password_privacy_test.
 *
 * @package    availability_password
 * @copyright  2019 Davo Smith, Synergy Learning UK on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \availability_password\privacy\provider
 */
final class privacy_test extends \core_privacy\tests\provider_testcase {
    /**
     * Set up the unit tests.
     */
    public function setUp(): void {
        $this->resetAfterTest();

        // Call parent setup.
        parent::setUp();
    }

    /**
     * Create courses, users and pages.
     * Create 'password entered' records for some of the users.
     *
     * @return array [$array_of_page_records, $array_of_user_records]
     * @throws coding_exception
     * @throws dml_exception
     */
    private function setup_data() {
        global $DB;
        $gen = self::getDataGenerator();
        $c1 = $gen->create_course();
        $c2 = $gen->create_course();
        $u1 = $gen->create_user();
        $u2 = $gen->create_user();
        $u3 = $gen->create_user();

        /** @var mod_page_generator $pagegen */
        $pagegen = $gen->get_plugin_generator('mod_page');
        $p1 = $pagegen->create_instance(['course' => $c1]);
        $p2 = $pagegen->create_instance(['course' => $c1]);
        $p3 = $pagegen->create_instance(['course' => $c2]);
        $p4 = $pagegen->create_instance(['course' => $c2]);

        // Create availability password records for these users.
        // Page 1 (u1 + u2).
        $DB->insert_record('availability_password_grant', (object)[
            'courseid' => $p1->course,
            'cmid' => $p1->cmid,
            'userid' => $u1->id,
            'passwordhash' => \availability_password\condition::get_password_hash($p1->cmid, 'fred'),
        ]);
        $DB->insert_record('availability_password_grant', (object)[
            'courseid' => $p1->course,
            'cmid' => $p1->cmid,
            'userid' => $u2->id,
            'passwordhash' => \availability_password\condition::get_password_hash($p1->cmid, 'fred'),
        ]);
        // Page 2 (u1).
        $DB->insert_record('availability_password_grant', (object)[
            'courseid' => $p2->course,
            'cmid' => $p2->cmid,
            'userid' => $u1->id,
            'passwordhash' => \availability_password\condition::get_password_hash($p2->cmid, 'fred'),
        ]);
        // Page 3 (u1 + u3).
        $DB->insert_record('availability_password_grant', (object)[
            'courseid' => $p3->course,
            'cmid' => $p3->cmid,
            'userid' => $u1->id,
            'passwordhash' => \availability_password\condition::get_password_hash($p3->cmid, 'fred'),
        ]);
        $DB->insert_record('availability_password_grant', (object)[
            'courseid' => $p3->course,
            'cmid' => $p3->cmid,
            'userid' => $u3->id,
            'passwordhash' => \availability_password\condition::get_password_hash($p3->cmid, 'fred'),
        ]);
        // Nothing for p4.

        return [[$p1, $p2, $p3, $p4], [$u1, $u2, $u3]];
    }

    /**
     * Given a page instance, return the associated userlist (using the privacy API).
     *
     * @param object $page
     * @return \core_privacy\local\request\userlist
     */
    private function get_users_on_page($page) {
        $ctx = \context_module::instance($page->cmid);
        $userlist = new \core_privacy\local\request\userlist($ctx, 'availability_password');
        provider::get_users_in_context($userlist);
        return $userlist;
    }

    /**
     * Given a user and a list of pages, return the approved contextlist for these pages.
     *
     * @param object $user
     * @param array $pages
     * @return \core_privacy\local\request\approved_contextlist
     */
    private function get_approved_contextlist($user, array $pages) {
        $contextids = array_map(fn($page) => \context_module::instance($page->cmid)->id, $pages);
        return new \core_privacy\local\request\approved_contextlist($user, 'availability_password', $contextids);
    }

    /**
     * Test get_metadata() function describes the grant table.
     */
    public function test_get_metadata(): void {
        $collection = new \core_privacy\local\metadata\collection('availability_password');
        $items = provider::get_metadata($collection)->get_collection();

        $this->assertCount(1, $items);
        $this->assertEquals('availability_password_grant', $items[0]->get_name());
        $this->assertEquals(
            ['courseid', 'cmid', 'userid', 'passwordhash'],
            array_keys($items[0]->get_privacy_fields())
        );
    }

    /**
     * Test get_contexts_for_userid() function returns the expected contexts for each user.
     */
    public function test_get_contexts_for_userid(): void {
        [$pages, $users] = $this->setup_data();
        [$p1, $p2, $p3, ] = $pages;
        [$u1, $u2, $u3] = $users;
        $u4 = self::getDataGenerator()->create_user();

        $ctx1 = \context_module::instance($p1->cmid)->id;
        $ctx2 = \context_module::instance($p2->cmid)->id;
        $ctx3 = \context_module::instance($p3->cmid)->id;

        $contextids = provider::get_contexts_for_userid($u1->id)->get_contextids();
        $this->assertEqualsCanonicalizing([$ctx1, $ctx2, $ctx3], $contextids);
        $contextids = provider::get_contexts_for_userid($u2->id)->get_contextids();
        $this->assertEqualsCanonicalizing([$ctx1], $contextids);
        $contextids = provider::get_contexts_for_userid($u3->id)->get_contextids();
        $this->assertEqualsCanonicalizing([$ctx3], $contextids);
        $this->assertCount(0, provider::get_contexts_for_userid($u4->id));
    }

    /**
     * Test export_user_data() exports the grants of the given user in the approved contexts only and
     * does not export the password hash.
     */
    public function test_export_user_data(): void {
        [$pages, $users] = $this->setup_data();
        [$p1, $p2, $p3, $p4] = $pages;
        [$u1, $u2, ] = $users;
        $subcontext = [get_string('pluginname', 'availability_password')];

        // Export u1's data for pages 1, 2 and 4, but not for page 3.
        provider::export_user_data($this->get_approved_contextlist($u1, [$p1, $p2, $p4]));

        foreach ([$p1, $p2] as $page) {
            $writer = \core_privacy\local\request\writer::with_context(\context_module::instance($page->cmid));
            $this->assertTrue($writer->has_any_data());
            $data = $writer->get_data($subcontext);
            $this->assertEquals($page->course, $data->courseid);
            $this->assertEquals($page->cmid, $data->cmid);
            $this->assertEquals($u1->id, $data->userid);
            $this->assertObjectNotHasProperty('passwordhash', $data);
        }

        // Page 3 was not approved and page 4 does not contain any data.
        foreach ([$p3, $p4] as $page) {
            $writer = \core_privacy\local\request\writer::with_context(\context_module::instance($page->cmid));
            $this->assertFalse($writer->has_any_data());
        }

        // Exporting u2's data for page 1 exports u2's grant and not u1's.
        \core_privacy\local\request\writer::reset();
        provider::export_user_data($this->get_approved_contextlist($u2, [$p1]));
        $writer = \core_privacy\local\request\writer::with_context(\context_module::instance($p1->cmid));
        $this->assertEquals($u2->id, $writer->get_data($subcontext)->userid);
    }

    /**
     * Test export_user_data() does not export anything for an empty contextlist.
     */
    public function test_export_user_data_empty_contextlist(): void {
        [$pages, $users] = $this->setup_data();
        [$p1, , , ] = $pages;
        [$u1, , ] = $users;

        provider::export_user_data($this->get_approved_contextlist($u1, []));

        $writer = \core_privacy\local\request\writer::with_context(\context_module::instance($p1->cmid));
        $this->assertFalse($writer->has_any_data());
    }

    /**
     * Test delete_data_for_all_users_in_context() removes all data in the given module context only and
     * ignores other context levels.
     */
    public function test_delete_data_for_all_users_in_context(): void {
        [$pages, ] = $this->setup_data();
        [$p1, $p2, $p3, $p4] = $pages;

        // A course context is ignored, even if it contains modules with data.
        provider::delete_data_for_all_users_in_context(\context_course::instance($p1->course));
        $this->assertCount(2, $this->get_users_on_page($p1));
        $this->assertCount(1, $this->get_users_on_page($p2));

        // Delete all users from page 1.
        provider::delete_data_for_all_users_in_context(\context_module::instance($p1->cmid));

        // Check that there are now no users for page 1, but the other counts are unaffected.
        $this->assertCount(0, $this->get_users_on_page($p1));
        $this->assertCount(1, $this->get_users_on_page($p2));
        $this->assertCount(2, $this->get_users_on_page($p3));
        $this->assertCount(0, $this->get_users_on_page($p4));
    }

    /**
     * Test delete_data_for_user() removes the data of the given user in the approved contexts only.
     */
    public function test_delete_data_for_user(): void {
        [$pages, $users] = $this->setup_data();
        [$p1, $p2, $p3, $p4] = $pages;
        [$u1, $u2, $u3] = $users;

        // Delete u1 from pages 1 and 3.
        provider::delete_data_for_user($this->get_approved_contextlist($u1, [$p1, $p3]));

        // Check that u1 is only left on page 2 and the other users are unaffected.
        $this->assertEquals([$u2->id], $this->get_users_on_page($p1)->get_userids());
        $this->assertEquals([$u1->id], $this->get_users_on_page($p2)->get_userids());
        $this->assertEquals([$u3->id], $this->get_users_on_page($p3)->get_userids());
        $this->assertCount(0, $this->get_users_on_page($p4));

        // An empty contextlist does not delete anything.
        provider::delete_data_for_user($this->get_approved_contextlist($u1, []));
        $this->assertCount(1, $this->get_users_on_page($p2));
    }

    /**
     * Test get_users_in_context() function returns the expected users for each page.
     */
    public function test_get_users_in_context(): void {
        [$pages, ] = $this->setup_data();
        [$p1, $p2, $p3, $p4] = $pages;

        $this->assertCount(2, $this->get_users_on_page($p1));
        $this->assertCount(1, $this->get_users_on_page($p2));
        $this->assertCount(2, $this->get_users_on_page($p3));
        $this->assertCount(0, $this->get_users_on_page($p4));
    }

    /**
     * Test delete_data_for_users() removes the expected data from the specified users (in the specified context) and
     * no other data is affected.
     */
    public function test_delete_data_for_users(): void {
        [$pages, $users] = $this->setup_data();
        [$p1, $p2, $p3, $p4] = $pages;
        [$u1, $u2, $u3] = $users;

        // Delete u1 + u3 from page 1.
        $ctx = \context_module::instance($p1->cmid);
        $userlist = new \core_privacy\local\request\userlist($ctx, 'availability_password');
        provider::get_users_in_context($userlist);
        $approvedlist = new \core_privacy\local\request\approved_userlist($ctx, 'availability_password', [$u1->id, $u3->id]);
        provider::delete_data_for_users($approvedlist);

        // Check that there is now only 1 user for page 1, but the other counts are unaffected.
        $this->assertCount(1, $this->get_users_on_page($p1));
        $this->assertCount(1, $this->get_users_on_page($p2));
        $this->assertCount(2, $this->get_users_on_page($p3));
        $this->assertCount(0, $this->get_users_on_page($p4));

        // Delete u1 + u3 from page 2.
        $ctx = \context_module::instance($p2->cmid);
        $userlist = new \core_privacy\local\request\userlist($ctx, 'availability_password');
        provider::get_users_in_context($userlist);
        $approvedlist = new \core_privacy\local\request\approved_userlist($ctx, 'availability_password', [$u1->id, $u3->id]);
        provider::delete_data_for_users($approvedlist);

        // Check that there are now no users for page 2, but the other counts are unaffected.
        $this->assertCount(1, $this->get_users_on_page($p1));
        $this->assertCount(0, $this->get_users_on_page($p2));
        $this->assertCount(2, $this->get_users_on_page($p3));
        $this->assertCount(0, $this->get_users_on_page($p4));

        // Delete u1 + u3 from page 3.
        $ctx = \context_module::instance($p3->cmid);
        $userlist = new \core_privacy\local\request\userlist($ctx, 'availability_password');
        provider::get_users_in_context($userlist);
        $approvedlist = new \core_privacy\local\request\approved_userlist($ctx, 'availability_password', [$u1->id, $u3->id]);
        provider::delete_data_for_users($approvedlist);

        // Check that there are now no users for page 3, but the other counts are unaffected.
        $this->assertCount(1, $this->get_users_on_page($p1));
        $this->assertCount(0, $this->get_users_on_page($p2));
        $this->assertCount(0, $this->get_users_on_page($p3));
        $this->assertCount(0, $this->get_users_on_page($p4));
    }
}
