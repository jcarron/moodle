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
 * Events tests.
 *
 * @package    mod_enhancedchoice
 * @copyright  2013 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/enhancedchoice/lib.php');

/**
 * Events tests class.
 *
 * @package    mod_enhancedchoice
 * @copyright  2013 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_enhancedchoice_events_testcase extends advanced_testcase {
    /** @var enhancedchoice_object */
    protected $enhancedchoice;

    /** @var course_object */
    protected $course;

    /** @var cm_object Course module object. */
    protected $cm;

    /** @var context_object */
    protected $context;

    /**
     * Setup often used objects for the following tests.
     */
    protected function setup() {
        global $DB;

        $this->resetAfterTest();

        $this->course = $this->getDataGenerator()->create_course();
        $this->enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $this->course->id));
        $this->cm = $DB->get_record('course_modules', array('id' => $this->enhancedchoice->cmid));
        $this->context = context_module::instance($this->enhancedchoice->cmid);
    }

    /**
     * Test to ensure that event data is being stored correctly.
     */
    public function test_answer_created() {
        global $DB;
        // Generate user data.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $optionids = array_keys($DB->get_records('enhancedchoice_options', array('enhancedchoiceid' => $this->enhancedchoice->id)));
        // Redirect event.
        $sink = $this->redirectEvents();
        enhancedchoice_user_submit_response($optionids[3], $this->enhancedchoice, $user->id, $this->course, $this->cm);
        $events = $sink->get_events();
        $answer = $DB->get_record('enhancedchoice_answers', ['userid' => $user->id, 'enhancedchoiceid' => $this->enhancedchoice->id]);

        // Data checking.
        $this->assertCount(1, $events);
        $this->assertInstanceOf('\mod_enhancedchoice\event\answer_created', $events[0]);
        $this->assertEquals($user->id, $events[0]->userid);
        $this->assertEquals($user->id, $events[0]->relateduserid);
        $this->assertEquals(context_module::instance($this->enhancedchoice->cmid), $events[0]->get_context());
        $this->assertEquals($answer->id, $events[0]->objectid);
        $this->assertEquals($this->enhancedchoice->id, $events[0]->other['enhancedchoiceid']);
        $this->assertEquals($optionids[3], $events[0]->other['optionid']);
        $this->assertEventContextNotUsed($events[0]);
        $sink->close();
    }

    /**
     * Test to ensure that event data is being stored correctly.
     */
    public function test_answer_submitted_by_another_user() {
        global $DB, $USER;
        // Generate user data.
        $user = $this->getDataGenerator()->create_user();

        $optionids = array_keys($DB->get_records('enhancedchoice_options', array('enhancedchoiceid' => $this->enhancedchoice->id)));
        // Redirect event.
        $sink = $this->redirectEvents();
        enhancedchoice_user_submit_response($optionids[3], $this->enhancedchoice, $user->id, $this->course, $this->cm);
        $events = $sink->get_events();
        $answer = $DB->get_record('enhancedchoice_answers', ['userid' => $user->id, 'enhancedchoiceid' => $this->enhancedchoice->id]);

        // Data checking.
        $this->assertCount(1, $events);
        $this->assertInstanceOf('\mod_enhancedchoice\event\answer_created', $events[0]);
        $this->assertEquals($USER->id, $events[0]->userid);
        $this->assertEquals($user->id, $events[0]->relateduserid);
        $this->assertEquals(context_module::instance($this->enhancedchoice->cmid), $events[0]->get_context());
        $this->assertEquals($answer->id, $events[0]->objectid);
        $this->assertEquals($this->enhancedchoice->id, $events[0]->other['enhancedchoiceid']);
        $this->assertEquals($optionids[3], $events[0]->other['optionid']);
        $this->assertEventContextNotUsed($events[0]);
        $sink->close();
    }

    /**
     * Test to ensure that multiple enhancedchoice data is being stored correctly.
     */
    public function test_answer_created_multiple() {
        global $DB;

        // Generate user data.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        // Create multiple enhancedchoice.
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $this->course->id,
            'allowmultiple' => 1));
        $cm = $DB->get_record('course_modules', array('id' => $enhancedchoice->cmid));
        $context = context_module::instance($enhancedchoice->cmid);

        $optionids = array_keys($DB->get_records('enhancedchoice_options', array('enhancedchoiceid' => $enhancedchoice->id)));
        $submittedoptionids = array($optionids[1], $optionids[3]);

        // Redirect event.
        $sink = $this->redirectEvents();
        enhancedchoice_user_submit_response($submittedoptionids, $enhancedchoice, $user->id, $this->course, $cm);
        $events = $sink->get_events();
        $answers = $DB->get_records('enhancedchoice_answers', ['userid' => $user->id, 'enhancedchoiceid' => $enhancedchoice->id], 'id');
        $answers = array_values($answers);

        // Data checking.
        $this->assertCount(2, $events);
        $this->assertInstanceOf('\mod_enhancedchoice\event\answer_created', $events[0]);
        $this->assertEquals($user->id, $events[0]->userid);
        $this->assertEquals($user->id, $events[0]->relateduserid);
        $this->assertEquals(context_module::instance($enhancedchoice->cmid), $events[0]->get_context());
        $this->assertEquals($answers[0]->id, $events[0]->objectid);
        $this->assertEquals($enhancedchoice->id, $events[0]->other['enhancedchoiceid']);
        $this->assertEquals($optionids[1], $events[0]->other['optionid']);
        $this->assertEventContextNotUsed($events[0]);

        $this->assertInstanceOf('\mod_enhancedchoice\event\answer_created', $events[1]);
        $this->assertEquals($user->id, $events[1]->userid);
        $this->assertEquals($user->id, $events[1]->relateduserid);
        $this->assertEquals(context_module::instance($enhancedchoice->cmid), $events[1]->get_context());
        $this->assertEquals($answers[1]->id, $events[1]->objectid);
        $this->assertEquals($enhancedchoice->id, $events[1]->other['enhancedchoiceid']);
        $this->assertEquals($optionids[3], $events[1]->other['optionid']);
        $this->assertEventContextNotUsed($events[1]);
        $sink->close();
    }

    /**
     * Test custom validations.
     *
     * @expectedException coding_exception
     */
    public function test_answer_created_other_exception() {
        // Generate user data.
        $user = $this->getDataGenerator()->create_user();

        $eventdata = array();
        $eventdata['context'] = $this->context;
        $eventdata['objectid'] = 2;
        $eventdata['userid'] = $user->id;
        $eventdata['courseid'] = $this->course->id;
        $eventdata['other'] = array();

        // Make sure content identifier is always set.
        $event = \mod_enhancedchoice\event\answer_created::create($eventdata);
        $event->trigger();
        $this->assertEventContextNotUsed($event);
    }

    /**
     * Test to ensure that event data is being stored correctly.
     */
    public function test_answer_updated() {
        global $DB;
        // Generate user data.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $optionids = array_keys($DB->get_records('enhancedchoice_options', array('enhancedchoiceid' => $this->enhancedchoice->id)));

        // Create the first answer.
        enhancedchoice_user_submit_response($optionids[2], $this->enhancedchoice, $user->id, $this->course, $this->cm);
        $oldanswer = $DB->get_record('enhancedchoice_answers', ['userid' => $user->id, 'enhancedchoiceid' => $this->enhancedchoice->id]);

        // Redirect event.
        $sink = $this->redirectEvents();
        // Now choose a different answer.
        enhancedchoice_user_submit_response($optionids[3], $this->enhancedchoice, $user->id, $this->course, $this->cm);
        $newanswer = $DB->get_record('enhancedchoice_answers', ['userid' => $user->id, 'enhancedchoiceid' => $this->enhancedchoice->id]);

        $events = $sink->get_events();

        // Data checking.
        $this->assertCount(2, $events);
        $this->assertInstanceOf('\mod_enhancedchoice\event\answer_deleted', $events[0]);
        $this->assertEquals($user->id, $events[0]->userid);
        $this->assertEquals(context_module::instance($this->enhancedchoice->cmid), $events[0]->get_context());
        $this->assertEquals($oldanswer->id, $events[0]->objectid);
        $this->assertEquals($this->enhancedchoice->id, $events[0]->other['enhancedchoiceid']);
        $this->assertEquals($optionids[2], $events[0]->other['optionid']);
        $this->assertEventContextNotUsed($events[0]);

        $this->assertInstanceOf('\mod_enhancedchoice\event\answer_created', $events[1]);
        $this->assertEquals($user->id, $events[1]->userid);
        $this->assertEquals(context_module::instance($this->enhancedchoice->cmid), $events[1]->get_context());
        $this->assertEquals($newanswer->id, $events[1]->objectid);
        $this->assertEquals($this->enhancedchoice->id, $events[1]->other['enhancedchoiceid']);
        $this->assertEquals($optionids[3], $events[1]->other['optionid']);
        $this->assertEventContextNotUsed($events[1]);

        $sink->close();
    }

    /**
     * Test to ensure that event data is being stored correctly.
     */
    public function test_answer_deleted() {
        global $DB, $USER;
        // Generate user data.
        $user = $this->getDataGenerator()->create_user();

        $optionids = array_keys($DB->get_records('enhancedchoice_options', array('enhancedchoiceid' => $this->enhancedchoice->id)));

        // Create the first answer.
        enhancedchoice_user_submit_response($optionids[2], $this->enhancedchoice, $user->id, $this->course, $this->cm);
        // Get the users response.
        $answer = $DB->get_record('enhancedchoice_answers', array('userid' => $user->id, 'enhancedchoiceid' => $this->enhancedchoice->id),
            '*', $strictness = IGNORE_MULTIPLE);

        // Redirect event.
        $sink = $this->redirectEvents();
        // Now delete the answer.
        enhancedchoice_delete_responses(array($answer->id), $this->enhancedchoice, $this->cm, $this->course);

        // Get our event event.
        $events = $sink->get_events();
        $event = reset($events);

        // Data checking.
        $this->assertInstanceOf('\mod_enhancedchoice\event\answer_deleted', $event);
        $this->assertEquals($USER->id, $event->userid);
        $this->assertEquals($user->id, $event->relateduserid);
        $this->assertEquals(context_module::instance($this->enhancedchoice->cmid), $event->get_context());
        $this->assertEquals($this->enhancedchoice->id, $event->other['enhancedchoiceid']);
        $this->assertEquals($answer->optionid, $event->other['optionid']);
        $this->assertEventContextNotUsed($event);
        $sink->close();
    }

    /**
     * Test to ensure that event data is being stored correctly.
     */
    public function test_report_viewed() {
        global $USER;

        $this->resetAfterTest();

        // Generate user data.
        $this->setAdminUser();

        $eventdata = array();
        $eventdata['objectid'] = $this->enhancedchoice->id;
        $eventdata['context'] = $this->context;
        $eventdata['courseid'] = $this->course->id;
        $eventdata['other']['content'] = 'enhancedchoicereportcontentviewed';

        // This is fired in a page view so we can't run this through a function.
        $event = \mod_enhancedchoice\event\report_viewed::create($eventdata);

        // Redirect event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $event = $sink->get_events();

        // Data checking.
        $this->assertCount(1, $event);
        $this->assertInstanceOf('\mod_enhancedchoice\event\report_viewed', $event[0]);
        $this->assertEquals($USER->id, $event[0]->userid);
        $this->assertEquals(context_module::instance($this->enhancedchoice->cmid), $event[0]->get_context());
        $expected = array($this->course->id, "enhancedchoice", "report", 'report.php?id=' . $this->context->instanceid,
            $this->enhancedchoice->id, $this->context->instanceid);
        $this->assertEventLegacyLogData($expected, $event[0]);
        $this->assertEventContextNotUsed($event[0]);
        $sink->close();
    }

    /**
     * Test to ensure that event data is being stored correctly.
     */
    public function test_report_downloaded() {
        global $USER;

        $this->resetAfterTest();

        // Generate user data.
        $this->setAdminUser();

        $eventdata = array();
        $eventdata['context'] = $this->context;
        $eventdata['courseid'] = $this->course->id;
        $eventdata['other']['content'] = 'enhancedchoicereportcontentviewed';
        $eventdata['other']['format'] = 'csv';
        $eventdata['other']['enhancedchoiceid'] = $this->enhancedchoice->id;

        // This is fired in a page view so we can't run this through a function.
        $event = \mod_enhancedchoice\event\report_downloaded::create($eventdata);

        // Redirect event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $event = $sink->get_events();

        // Data checking.
        $this->assertCount(1, $event);
        $this->assertInstanceOf('\mod_enhancedchoice\event\report_downloaded', $event[0]);
        $this->assertEquals($USER->id, $event[0]->userid);
        $this->assertEquals(context_module::instance($this->enhancedchoice->cmid), $event[0]->get_context());
        $this->assertEquals('csv', $event[0]->other['format']);
        $this->assertEquals($this->enhancedchoice->id, $event[0]->other['enhancedchoiceid']);
        $this->assertEventContextNotUsed($event[0]);
        $sink->close();
    }

    /**
     * Test to ensure that event data is being stored correctly.
     */
    public function test_course_module_viewed() {
        global $USER;

        // Generate user data.
        $this->setAdminUser();

        $eventdata = array();
        $eventdata['objectid'] = $this->enhancedchoice->id;
        $eventdata['context'] = $this->context;
        $eventdata['courseid'] = $this->course->id;
        $eventdata['other']['content'] = 'pageresourceview';

        // This is fired in a page view so we can't run this through a function.
        $event = \mod_enhancedchoice\event\course_module_viewed::create($eventdata);

        // Redirect event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $event = $sink->get_events();

        // Data checking.
        $this->assertCount(1, $event);
        $this->assertInstanceOf('\mod_enhancedchoice\event\course_module_viewed', $event[0]);
        $this->assertEquals($USER->id, $event[0]->userid);
        $this->assertEquals(context_module::instance($this->enhancedchoice->cmid), $event[0]->get_context());
        $expected = array($this->course->id, "enhancedchoice", "view", 'view.php?id=' . $this->context->instanceid,
            $this->enhancedchoice->id, $this->context->instanceid);
        $this->assertEventLegacyLogData($expected, $event[0]);
        $this->assertEventContextNotUsed($event[0]);
        $sink->close();
    }

    /**
     * Test to ensure that event data is being stored correctly.
     */
    public function test_course_module_instance_list_viewed_viewed() {
        global $USER;

        // Not much can be tested here as the event is only triggered on a page load,
        // let's just check that the event contains the expected basic information.
        $this->setAdminUser();

        $params = array('context' => context_course::instance($this->course->id));
        $event = \mod_enhancedchoice\event\course_module_instance_list_viewed::create($params);
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);
        $this->assertInstanceOf('\mod_enhancedchoice\event\course_module_instance_list_viewed', $event);
        $this->assertEquals($USER->id, $event->userid);
        $this->assertEquals(context_course::instance($this->course->id), $event->get_context());
        $expected = array($this->course->id, 'enhancedchoice', 'view all', 'index.php?id=' . $this->course->id, '');
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }
}
