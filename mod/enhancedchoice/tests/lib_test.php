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
 * EnhancedChoice module library functions tests
 *
 * @package    mod_enhancedchoice
 * @category   test
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/enhancedchoice/lib.php');

/**
 * EnhancedChoice module library functions tests
 *
 * @package    mod_enhancedchoice
 * @category   test
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_enhancedchoice_lib_testcase extends externallib_advanced_testcase {

    /**
     * Test enhancedchoice_view
     * @return void
     */
    public function test_enhancedchoice_view() {
        global $CFG;

        $this->resetAfterTest();

        $this->setAdminUser();
        // Setup test data.
        $course = $this->getDataGenerator()->create_course();
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id));
        $context = context_module::instance($enhancedchoice->cmid);
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();

        enhancedchoice_view($enhancedchoice, $course, $cm, $context);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_enhancedchoice\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $url = new \moodle_url('/mod/enhancedchoice/view.php', array('id' => $cm->id));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());
    }

    /**
     * Test enhancedchoice_can_view_results
     * @return void
     */
    public function test_enhancedchoice_can_view_results() {
        global $DB, $USER;

        $this->resetAfterTest();

        $this->setAdminUser();
        // Setup test data.
        $course = $this->getDataGenerator()->create_course();
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id));
        $context = context_module::instance($enhancedchoice->cmid);
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);

        // Default values are false, user cannot view results.
        $canview = enhancedchoice_can_view_results($enhancedchoice);
        $this->assertFalse($canview);

        // Show results forced.
        $enhancedchoice->showresults = ENHANCEDCHOICE_SHOWRESULTS_ALWAYS;
        $DB->update_record('enhancedchoice', $enhancedchoice);
        $canview = enhancedchoice_can_view_results($enhancedchoice);
        $this->assertTrue($canview);

        // Add a time restriction (enhancedchoice not open yet).
        $enhancedchoice->timeopen = time() + YEARSECS;
        $DB->update_record('enhancedchoice', $enhancedchoice);
        $canview = enhancedchoice_can_view_results($enhancedchoice);
        $this->assertFalse($canview);

        // Show results after closing.
        $enhancedchoice->timeopen = 0;
        $enhancedchoice->showresults = ENHANCEDCHOICE_SHOWRESULTS_AFTER_CLOSE;
        $DB->update_record('enhancedchoice', $enhancedchoice);
        $canview = enhancedchoice_can_view_results($enhancedchoice);
        $this->assertFalse($canview);

        $enhancedchoice->timeclose = time() - HOURSECS;
        $DB->update_record('enhancedchoice', $enhancedchoice);
        $canview = enhancedchoice_can_view_results($enhancedchoice);
        $this->assertTrue($canview);

        // Show results after answering.
        $enhancedchoice->timeclose = 0;
        $enhancedchoice->showresults = ENHANCEDCHOICE_SHOWRESULTS_AFTER_ANSWER;
        $DB->update_record('enhancedchoice', $enhancedchoice);
        $canview = enhancedchoice_can_view_results($enhancedchoice);
        $this->assertFalse($canview);

        // Get the first option.
        $enhancedchoicewithoptions = enhancedchoice_get_enhancedchoice($enhancedchoice->id);
        $optionids = array_keys($enhancedchoicewithoptions->option);

        enhancedchoice_user_submit_response($optionids[0], $enhancedchoice, $USER->id, $course, $cm);

        $canview = enhancedchoice_can_view_results($enhancedchoice);
        $this->assertTrue($canview);

    }

    /**
     * @expectedException moodle_exception
     */
    public function test_enhancedchoice_user_submit_response_validation() {
        global $USER;

        $this->resetAfterTest();

        $this->setAdminUser();
        // Setup test data.
        $course = $this->getDataGenerator()->create_course();
        $enhancedchoice1 = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id));
        $enhancedchoice2 = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id));
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice1->id);

        $enhancedchoicewithoptions1 = enhancedchoice_get_enhancedchoice($enhancedchoice1->id);
        $enhancedchoicewithoptions2 = enhancedchoice_get_enhancedchoice($enhancedchoice2->id);
        $optionids1 = array_keys($enhancedchoicewithoptions1->option);
        $optionids2 = array_keys($enhancedchoicewithoptions2->option);

        // Make sure we cannot submit options from a different enhancedchoice instance.
        enhancedchoice_user_submit_response($optionids2[0], $enhancedchoice1, $USER->id, $course, $cm);
    }

    /**
     * Test enhancedchoice_get_user_response
     * @return void
     */
    public function test_enhancedchoice_get_user_response() {
        $this->resetAfterTest();

        $this->setAdminUser();
        // Setup test data.
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id));
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);

        $enhancedchoicewithoptions = enhancedchoice_get_enhancedchoice($enhancedchoice->id);
        $optionids = array_keys($enhancedchoicewithoptions->option);

        enhancedchoice_user_submit_response($optionids[0], $enhancedchoice, $student->id, $course, $cm);
        $responses = enhancedchoice_get_user_response($enhancedchoice, $student->id);
        $this->assertCount(1, $responses);
        $response = array_shift($responses);
        $this->assertEquals($optionids[0], $response->optionid);

        // Multiple responses.
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id, 'allowmultiple' => 1));
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);

        $enhancedchoicewithoptions = enhancedchoice_get_enhancedchoice($enhancedchoice->id);
        $optionids = array_keys($enhancedchoicewithoptions->option);

        // Submit a response with the options reversed.
        $selections = $optionids;
        rsort($selections);
        enhancedchoice_user_submit_response($selections, $enhancedchoice, $student->id, $course, $cm);
        $responses = enhancedchoice_get_user_response($enhancedchoice, $student->id);
        $this->assertCount(count($optionids), $responses);
        foreach ($responses as $resp) {
            $this->assertEquals(array_shift($optionids), $resp->optionid);
        }
    }

    /**
     * Test enhancedchoice_get_my_response
     * @return void
     */
    public function test_enhancedchoice_get_my_response() {
        global $USER;

        $this->resetAfterTest();

        $this->setAdminUser();
        // Setup test data.
        $course = $this->getDataGenerator()->create_course();
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id));
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);

        $enhancedchoicewithoptions = enhancedchoice_get_enhancedchoice($enhancedchoice->id);
        $optionids = array_keys($enhancedchoicewithoptions->option);

        enhancedchoice_user_submit_response($optionids[0], $enhancedchoice, $USER->id, $course, $cm);
        $responses = enhancedchoice_get_my_response($enhancedchoice);
        $this->assertCount(1, $responses);
        $response = array_shift($responses);
        $this->assertEquals($optionids[0], $response->optionid);

        // Multiple responses.
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id, 'allowmultiple' => 1));
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);

        $enhancedchoicewithoptions = enhancedchoice_get_enhancedchoice($enhancedchoice->id);
        $optionids = array_keys($enhancedchoicewithoptions->option);

        // Submit a response with the options reversed.
        $selections = $optionids;
        rsort($selections);
        enhancedchoice_user_submit_response($selections, $enhancedchoice, $USER->id, $course, $cm);
        $responses = enhancedchoice_get_my_response($enhancedchoice);
        $this->assertCount(count($optionids), $responses);
        foreach ($responses as $resp) {
            $this->assertEquals(array_shift($optionids), $resp->optionid);
        }
    }

    /**
     * Test enhancedchoice_get_availability_status
     * @return void
     */
    public function test_enhancedchoice_get_availability_status() {
        global $USER;

        $this->resetAfterTest();

        $this->setAdminUser();
        // Setup test data.
        $course = $this->getDataGenerator()->create_course();
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id));

        // No time restrictions and updates allowed.
        list($status, $warnings) = enhancedchoice_get_availability_status($enhancedchoice, false);
        $this->assertEquals(true, $status);
        $this->assertCount(0, $warnings);

        // No updates allowed, but haven't answered yet.
        $enhancedchoice->allowupdate = false;
        list($status, $warnings) = enhancedchoice_get_availability_status($enhancedchoice, false);
        $this->assertEquals(true, $status);
        $this->assertCount(0, $warnings);

        // No updates allowed and have answered.
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);
        $enhancedchoicewithoptions = enhancedchoice_get_enhancedchoice($enhancedchoice->id);
        $optionids = array_keys($enhancedchoicewithoptions->option);
        enhancedchoice_user_submit_response($optionids[0], $enhancedchoice, $USER->id, $course, $cm);
        list($status, $warnings) = enhancedchoice_get_availability_status($enhancedchoice, false);
        $this->assertEquals(false, $status);
        $this->assertCount(1, $warnings);
        $this->assertEquals('enhancedchoicesaved', array_keys($warnings)[0]);

        $enhancedchoice->allowupdate = true;

        // With time restrictions, still open.
        $enhancedchoice->timeopen = time() - DAYSECS;
        $enhancedchoice->timeclose = time() + DAYSECS;
        list($status, $warnings) = enhancedchoice_get_availability_status($enhancedchoice, false);
        $this->assertEquals(true, $status);
        $this->assertCount(0, $warnings);

        // EnhancedChoice not open yet.
        $enhancedchoice->timeopen = time() + DAYSECS;
        $enhancedchoice->timeclose = $enhancedchoice->timeopen + DAYSECS;
        list($status, $warnings) = enhancedchoice_get_availability_status($enhancedchoice, false);
        $this->assertEquals(false, $status);
        $this->assertCount(1, $warnings);
        $this->assertEquals('notopenyet', array_keys($warnings)[0]);

        // EnhancedChoice closed.
        $enhancedchoice->timeopen = time() - DAYSECS;
        $enhancedchoice->timeclose = time() - 1;
        list($status, $warnings) = enhancedchoice_get_availability_status($enhancedchoice, false);
        $this->assertEquals(false, $status);
        $this->assertCount(1, $warnings);
        $this->assertEquals('expired', array_keys($warnings)[0]);
    }

    /*
     * The enhancedchoice's event should not be shown to a user when the user cannot view the enhancedchoice activity at all.
     */
    public function test_enhancedchoice_core_calendar_provide_event_action_in_hidden_section() {
        global $CFG;

        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a student.
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        // Create a enhancedchoice.
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id,
                'timeopen' => time() - DAYSECS, 'timeclose' => time() + DAYSECS));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $enhancedchoice->id, ENHANCEDCHOICE_EVENT_TYPE_OPEN);

        // Set sections 0 as hidden.
        set_section_visible($course->id, 0, 0);

        // Now, log out.
        $CFG->forcelogin = true; // We don't want to be logged in as guest, as guest users might still have some capabilities.
        $this->setUser();

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event for the student.
        $actionevent = mod_enhancedchoice_core_calendar_provide_event_action($event, $factory, $student->id);

        // Confirm the event is not shown at all.
        $this->assertNull($actionevent);
    }

    /*
     * The enhancedchoice's event should not be shown to a user who does not have permission to view the enhancedchoice.
     */
    public function test_enhancedchoice_core_calendar_provide_event_action_for_non_user() {
        global $CFG;

        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a enhancedchoice.
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id,
                'timeopen' => time() - DAYSECS, 'timeclose' => time() + DAYSECS));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $enhancedchoice->id, ENHANCEDCHOICE_EVENT_TYPE_OPEN);

        // Now, log out.
        $CFG->forcelogin = true; // We don't want to be logged in as guest, as guest users might still have some capabilities.
        $this->setUser();

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $actionevent = mod_enhancedchoice_core_calendar_provide_event_action($event, $factory);

        // Confirm the event is not shown at all.
        $this->assertNull($actionevent);
    }

    public function test_enhancedchoice_core_calendar_provide_event_action_open() {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a enhancedchoice.
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id,
            'timeopen' => time() - DAYSECS, 'timeclose' => time() + DAYSECS));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $enhancedchoice->id, ENHANCEDCHOICE_EVENT_TYPE_OPEN);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $actionevent = mod_enhancedchoice_core_calendar_provide_event_action($event, $factory);

        // Confirm the event was decorated.
        $this->assertInstanceOf('\core_calendar\local\event\value_objects\action', $actionevent);
        $this->assertEquals(get_string('viewenhancedchoices', 'enhancedchoice'), $actionevent->get_name());
        $this->assertInstanceOf('moodle_url', $actionevent->get_url());
        $this->assertEquals(1, $actionevent->get_item_count());
        $this->assertTrue($actionevent->is_actionable());
    }

    public function test_enhancedchoice_core_calendar_provide_event_action_open_for_user() {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a student.
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        // Create a enhancedchoice.
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id,
            'timeopen' => time() - DAYSECS, 'timeclose' => time() + DAYSECS));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $enhancedchoice->id, ENHANCEDCHOICE_EVENT_TYPE_OPEN);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event for the student.
        $actionevent = mod_enhancedchoice_core_calendar_provide_event_action($event, $factory, $student->id);

        // Confirm the event was decorated.
        $this->assertInstanceOf('\core_calendar\local\event\value_objects\action', $actionevent);
        $this->assertEquals(get_string('viewenhancedchoices', 'enhancedchoice'), $actionevent->get_name());
        $this->assertInstanceOf('moodle_url', $actionevent->get_url());
        $this->assertEquals(1, $actionevent->get_item_count());
        $this->assertTrue($actionevent->is_actionable());
    }

    /**
     * An event should not have an action if the user has already submitted a response
     * to the enhancedchoice activity.
     */
    public function test_enhancedchoice_core_calendar_provide_event_action_already_submitted() {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a student.
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        // Create a enhancedchoice.
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id,
            'timeopen' => time() - DAYSECS, 'timeclose' => time() + DAYSECS));
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);

        $enhancedchoicewithoptions = enhancedchoice_get_enhancedchoice($enhancedchoice->id);
        $optionids = array_keys($enhancedchoicewithoptions->option);

        enhancedchoice_user_submit_response($optionids[0], $enhancedchoice, $student->id, $course, $cm);

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $enhancedchoice->id, ENHANCEDCHOICE_EVENT_TYPE_OPEN);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        $this->setUser($student);

        // Decorate action event.
        $action = mod_enhancedchoice_core_calendar_provide_event_action($event, $factory);

        // Confirm no action was returned if the user has already submitted the
        // enhancedchoice activity.
        $this->assertNull($action);
    }

    /**
     * An event should not have an action if the user has already submitted a response
     * to the enhancedchoice activity.
     */
    public function test_enhancedchoice_core_calendar_provide_event_action_already_submitted_for_user() {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a student.
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        // Create a enhancedchoice.
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id,
            'timeopen' => time() - DAYSECS, 'timeclose' => time() + DAYSECS));
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);

        $enhancedchoicewithoptions = enhancedchoice_get_enhancedchoice($enhancedchoice->id);
        $optionids = array_keys($enhancedchoicewithoptions->option);

        enhancedchoice_user_submit_response($optionids[0], $enhancedchoice, $student->id, $course, $cm);

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $enhancedchoice->id, ENHANCEDCHOICE_EVENT_TYPE_OPEN);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event for the student.
        $action = mod_enhancedchoice_core_calendar_provide_event_action($event, $factory, $student->id);

        // Confirm no action was returned if the user has already submitted the
        // enhancedchoice activity.
        $this->assertNull($action);
    }

    public function test_enhancedchoice_core_calendar_provide_event_action_closed() {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        $timeclose = time() - DAYSECS;
        // Create a enhancedchoice.
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id,
            'timeclose' => $timeclose));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $enhancedchoice->id, ENHANCEDCHOICE_EVENT_TYPE_OPEN, $timeclose - 1);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $action = mod_enhancedchoice_core_calendar_provide_event_action($event, $factory);

        // Confirm not action was provided for a closed activity.
        $this->assertNull($action);
    }

    public function test_enhancedchoice_core_calendar_provide_event_action_closed_for_user() {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a student.
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        $timeclose = time() - DAYSECS;
        // Create a enhancedchoice.
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id,
            'timeclose' => $timeclose));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $enhancedchoice->id, ENHANCEDCHOICE_EVENT_TYPE_OPEN, $timeclose - 1);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event for the student.
        $action = mod_enhancedchoice_core_calendar_provide_event_action($event, $factory, $student->id);

        // Confirm not action was provided for a closed activity.
        $this->assertNull($action);
    }

    public function test_enhancedchoice_core_calendar_provide_event_action_open_in_future() {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        $timeopen = time() + DAYSECS;
        $timeclose = $timeopen + DAYSECS;

        // Create a enhancedchoice.
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id,
            'timeopen' => $timeopen, 'timeclose' => $timeclose));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $enhancedchoice->id, ENHANCEDCHOICE_EVENT_TYPE_OPEN, $timeopen);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $actionevent = mod_enhancedchoice_core_calendar_provide_event_action($event, $factory);

        // Confirm the event was decorated.
        $this->assertInstanceOf('\core_calendar\local\event\value_objects\action', $actionevent);
        $this->assertEquals(get_string('viewenhancedchoices', 'enhancedchoice'), $actionevent->get_name());
        $this->assertInstanceOf('moodle_url', $actionevent->get_url());
        $this->assertEquals(1, $actionevent->get_item_count());
        $this->assertFalse($actionevent->is_actionable());
    }

    public function test_enhancedchoice_core_calendar_provide_event_action_open_in_future_for_user() {
        global $CFG;

        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a student.
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        $timeopen = time() + DAYSECS;
        $timeclose = $timeopen + DAYSECS;

        // Create a enhancedchoice.
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id,
            'timeopen' => $timeopen, 'timeclose' => $timeclose));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $enhancedchoice->id, ENHANCEDCHOICE_EVENT_TYPE_OPEN, $timeopen);

        // Now, log out.
        $CFG->forcelogin = true; // We don't want to be logged in as guest, as guest users might still have some capabilities.
        $this->setUser();

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event for the student.
        $actionevent = mod_enhancedchoice_core_calendar_provide_event_action($event, $factory, $student->id);

        // Confirm the event was decorated.
        $this->assertInstanceOf('\core_calendar\local\event\value_objects\action', $actionevent);
        $this->assertEquals(get_string('viewenhancedchoices', 'enhancedchoice'), $actionevent->get_name());
        $this->assertInstanceOf('moodle_url', $actionevent->get_url());
        $this->assertEquals(1, $actionevent->get_item_count());
        $this->assertFalse($actionevent->is_actionable());
    }

    public function test_enhancedchoice_core_calendar_provide_event_action_no_time_specified() {
        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a enhancedchoice.
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $enhancedchoice->id, ENHANCEDCHOICE_EVENT_TYPE_OPEN);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $actionevent = mod_enhancedchoice_core_calendar_provide_event_action($event, $factory);

        // Confirm the event was decorated.
        $this->assertInstanceOf('\core_calendar\local\event\value_objects\action', $actionevent);
        $this->assertEquals(get_string('viewenhancedchoices', 'enhancedchoice'), $actionevent->get_name());
        $this->assertInstanceOf('moodle_url', $actionevent->get_url());
        $this->assertEquals(1, $actionevent->get_item_count());
        $this->assertTrue($actionevent->is_actionable());
    }

    public function test_enhancedchoice_core_calendar_provide_event_action_no_time_specified_for_user() {
        global $CFG;

        $this->resetAfterTest();

        $this->setAdminUser();

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create a student.
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        // Create a enhancedchoice.
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id));

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $enhancedchoice->id, ENHANCEDCHOICE_EVENT_TYPE_OPEN);

        // Now, log out.
        $CFG->forcelogin = true; // We don't want to be logged in as guest, as guest users might still have some capabilities.
        $this->setUser();

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event for the student.
        $actionevent = mod_enhancedchoice_core_calendar_provide_event_action($event, $factory, $student->id);

        // Confirm the event was decorated.
        $this->assertInstanceOf('\core_calendar\local\event\value_objects\action', $actionevent);
        $this->assertEquals(get_string('viewenhancedchoices', 'enhancedchoice'), $actionevent->get_name());
        $this->assertInstanceOf('moodle_url', $actionevent->get_url());
        $this->assertEquals(1, $actionevent->get_item_count());
        $this->assertTrue($actionevent->is_actionable());
    }

    public function test_enhancedchoice_core_calendar_provide_event_action_already_completed() {
        $this->resetAfterTest();
        set_config('enablecompletion', 1);
        $this->setAdminUser();

        // Create the activity.
        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id),
            array('completion' => 2, 'completionview' => 1, 'completionexpected' => time() + DAYSECS));

        // Get some additional data.
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $enhancedchoice->id,
            \core_completion\api::COMPLETION_EVENT_TYPE_DATE_COMPLETION_EXPECTED);

        // Mark the activity as completed.
        $completion = new completion_info($course);
        $completion->set_module_viewed($cm);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event.
        $actionevent = mod_enhancedchoice_core_calendar_provide_event_action($event, $factory);

        // Ensure result was null.
        $this->assertNull($actionevent);
    }

    public function test_enhancedchoice_core_calendar_provide_event_action_already_completed_for_user() {
        $this->resetAfterTest();
        set_config('enablecompletion', 1);
        $this->setAdminUser();

        // Create the activity.
        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id),
            array('completion' => 2, 'completionview' => 1, 'completionexpected' => time() + DAYSECS));

        // Enrol a student in the course.
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');

        // Get some additional data.
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);

        // Create a calendar event.
        $event = $this->create_action_event($course->id, $enhancedchoice->id,
            \core_completion\api::COMPLETION_EVENT_TYPE_DATE_COMPLETION_EXPECTED);

        // Mark the activity as completed for the student.
        $completion = new completion_info($course);
        $completion->set_module_viewed($cm, $student->id);

        // Create an action factory.
        $factory = new \core_calendar\action_factory();

        // Decorate action event for the student.
        $actionevent = mod_enhancedchoice_core_calendar_provide_event_action($event, $factory, $student->id);

        // Ensure result was null.
        $this->assertNull($actionevent);
    }

    /**
     * Creates an action event.
     *
     * @param int $courseid
     * @param int $instanceid The enhancedchoice id.
     * @param string $eventtype The event type. eg. ENHANCEDCHOICE_EVENT_TYPE_OPEN.
     * @param int|null $timestart The start timestamp for the event
     * @return bool|calendar_event
     */
    private function create_action_event($courseid, $instanceid, $eventtype, $timestart = null) {
        $event = new stdClass();
        $event->name = 'Calendar event';
        $event->modulename = 'enhancedchoice';
        $event->courseid = $courseid;
        $event->instance = $instanceid;
        $event->type = CALENDAR_EVENT_TYPE_ACTION;
        $event->eventtype = $eventtype;

        if ($timestart) {
            $event->timestart = $timestart;
        } else {
            $event->timestart = time();
        }

        return calendar_event::create($event);
    }

    /**
     * Test the callback responsible for returning the completion rule descriptions.
     * This function should work given either an instance of the module (cm_info), such as when checking the active rules,
     * or if passed a stdClass of similar structure, such as when checking the the default completion settings for a mod type.
     */
    public function test_mod_enhancedchoice_completion_get_active_rule_descriptions() {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Two activities, both with automatic completion. One has the 'completionsubmit' rule, one doesn't.
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $enhancedchoice1 = $this->getDataGenerator()->create_module('enhancedchoice', [
            'course' => $course->id,
            'completion' => 2,
            'completionsubmit' => 1
        ]);
        $enhancedchoice2 = $this->getDataGenerator()->create_module('enhancedchoice', [
            'course' => $course->id,
            'completion' => 2,
            'completionsubmit' => 0
        ]);
        $cm1 = cm_info::create(get_coursemodule_from_instance('enhancedchoice', $enhancedchoice1->id));
        $cm2 = cm_info::create(get_coursemodule_from_instance('enhancedchoice', $enhancedchoice2->id));

        // Data for the stdClass input type.
        // This type of input would occur when checking the default completion rules for an activity type, where we don't have
        // any access to cm_info, rather the input is a stdClass containing completion and customdata attributes, just like cm_info.
        $moddefaults = new stdClass();
        $moddefaults->customdata = ['customcompletionrules' => ['completionsubmit' => 1]];
        $moddefaults->completion = 2;

        $activeruledescriptions = [get_string('completionsubmit', 'enhancedchoice')];
        $this->assertEquals(mod_enhancedchoice_get_completion_active_rule_descriptions($cm1), $activeruledescriptions);
        $this->assertEquals(mod_enhancedchoice_get_completion_active_rule_descriptions($cm2), []);
        $this->assertEquals(mod_enhancedchoice_get_completion_active_rule_descriptions($moddefaults), $activeruledescriptions);
        $this->assertEquals(mod_enhancedchoice_get_completion_active_rule_descriptions(new stdClass()), []);
    }

    /**
     * An unkown event type should not change the enhancedchoice instance.
     */
    public function test_mod_enhancedchoice_core_calendar_event_timestart_updated_unknown_event() {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $enhancedchoicegenerator = $generator->get_plugin_generator('mod_enhancedchoice');
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $enhancedchoice = $enhancedchoicegenerator->create_instance(['course' => $course->id]);
        $enhancedchoice->timeopen = $timeopen;
        $enhancedchoice->timeclose = $timeclose;
        $DB->update_record('enhancedchoice', $enhancedchoice);

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'enhancedchoice',
            'instance' => $enhancedchoice->id,
            'eventtype' => ENHANCEDCHOICE_EVENT_TYPE_OPEN . "SOMETHING ELSE",
            'timestart' => 1,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        mod_enhancedchoice_core_calendar_event_timestart_updated($event, $enhancedchoice);

        $enhancedchoice = $DB->get_record('enhancedchoice', ['id' => $enhancedchoice->id]);
        $this->assertEquals($timeopen, $enhancedchoice->timeopen);
        $this->assertEquals($timeclose, $enhancedchoice->timeclose);
    }

    /**
     * A ENHANCEDCHOICE_EVENT_TYPE_OPEN event should update the timeopen property of
     * the enhancedchoice activity.
     */
    public function test_mod_enhancedchoice_core_calendar_event_timestart_updated_open_event() {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $enhancedchoicegenerator = $generator->get_plugin_generator('mod_enhancedchoice');
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $timemodified = 1;
        $newtimeopen = $timeopen - DAYSECS;
        $enhancedchoice = $enhancedchoicegenerator->create_instance(['course' => $course->id]);
        $enhancedchoice->timeopen = $timeopen;
        $enhancedchoice->timeclose = $timeclose;
        $enhancedchoice->timemodified = $timemodified;
        $DB->update_record('enhancedchoice', $enhancedchoice);

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'enhancedchoice',
            'instance' => $enhancedchoice->id,
            'eventtype' => ENHANCEDCHOICE_EVENT_TYPE_OPEN,
            'timestart' => $newtimeopen,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        // Trigger and capture the event when adding a contact.
        $sink = $this->redirectEvents();

        mod_enhancedchoice_core_calendar_event_timestart_updated($event, $enhancedchoice);

        $triggeredevents = $sink->get_events();
        $moduleupdatedevents = array_filter($triggeredevents, function($e) {
            return is_a($e, 'core\event\course_module_updated');
        });

        $enhancedchoice = $DB->get_record('enhancedchoice', ['id' => $enhancedchoice->id]);
        // Ensure the timeopen property matches the event timestart.
        $this->assertEquals($newtimeopen, $enhancedchoice->timeopen);
        // Ensure the timeclose isn't changed.
        $this->assertEquals($timeclose, $enhancedchoice->timeclose);
        // Ensure the timemodified property has been changed.
        $this->assertNotEquals($timemodified, $enhancedchoice->timemodified);
        // Confirm that a module updated event is fired when the module
        // is changed.
        $this->assertNotEmpty($moduleupdatedevents);
    }

    /**
     * A ENHANCEDCHOICE_EVENT_TYPE_CLOSE event should update the timeclose property of
     * the enhancedchoice activity.
     */
    public function test_mod_enhancedchoice_core_calendar_event_timestart_updated_close_event() {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $enhancedchoicegenerator = $generator->get_plugin_generator('mod_enhancedchoice');
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $timemodified = 1;
        $newtimeclose = $timeclose + DAYSECS;
        $enhancedchoice = $enhancedchoicegenerator->create_instance(['course' => $course->id]);
        $enhancedchoice->timeopen = $timeopen;
        $enhancedchoice->timeclose = $timeclose;
        $enhancedchoice->timemodified = $timemodified;
        $DB->update_record('enhancedchoice', $enhancedchoice);

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'enhancedchoice',
            'instance' => $enhancedchoice->id,
            'eventtype' => ENHANCEDCHOICE_EVENT_TYPE_CLOSE,
            'timestart' => $newtimeclose,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        // Trigger and capture the event when adding a contact.
        $sink = $this->redirectEvents();

        mod_enhancedchoice_core_calendar_event_timestart_updated($event, $enhancedchoice);

        $triggeredevents = $sink->get_events();
        $moduleupdatedevents = array_filter($triggeredevents, function($e) {
            return is_a($e, 'core\event\course_module_updated');
        });

        $enhancedchoice = $DB->get_record('enhancedchoice', ['id' => $enhancedchoice->id]);
        // Ensure the timeclose property matches the event timestart.
        $this->assertEquals($newtimeclose, $enhancedchoice->timeclose);
        // Ensure the timeopen isn't changed.
        $this->assertEquals($timeopen, $enhancedchoice->timeopen);
        // Ensure the timemodified property has been changed.
        $this->assertNotEquals($timemodified, $enhancedchoice->timemodified);
        // Confirm that a module updated event is fired when the module
        // is changed.
        $this->assertNotEmpty($moduleupdatedevents);
    }

    /**
     * An unkown event type should not have any limits
     */
    public function test_mod_enhancedchoice_core_calendar_get_valid_event_timestart_range_unknown_event() {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $enhancedchoice = new \stdClass();
        $enhancedchoice->timeopen = $timeopen;
        $enhancedchoice->timeclose = $timeclose;

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'enhancedchoice',
            'instance' => 1,
            'eventtype' => ENHANCEDCHOICE_EVENT_TYPE_OPEN . "SOMETHING ELSE",
            'timestart' => 1,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        list ($min, $max) = mod_enhancedchoice_core_calendar_get_valid_event_timestart_range($event, $enhancedchoice);
        $this->assertNull($min);
        $this->assertNull($max);
    }

    /**
     * The open event should be limited by the enhancedchoice's timeclose property, if it's set.
     */
    public function test_mod_enhancedchoice_core_calendar_get_valid_event_timestart_range_open_event() {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $enhancedchoice = new \stdClass();
        $enhancedchoice->timeopen = $timeopen;
        $enhancedchoice->timeclose = $timeclose;

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'enhancedchoice',
            'instance' => 1,
            'eventtype' => ENHANCEDCHOICE_EVENT_TYPE_OPEN,
            'timestart' => 1,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        // The max limit should be bounded by the timeclose value.
        list ($min, $max) = mod_enhancedchoice_core_calendar_get_valid_event_timestart_range($event, $enhancedchoice);

        $this->assertNull($min);
        $this->assertEquals($timeclose, $max[0]);

        // No timeclose value should result in no upper limit.
        $enhancedchoice->timeclose = 0;
        list ($min, $max) = mod_enhancedchoice_core_calendar_get_valid_event_timestart_range($event, $enhancedchoice);

        $this->assertNull($min);
        $this->assertNull($max);
    }

    /**
     * The close event should be limited by the enhancedchoice's timeopen property, if it's set.
     */
    public function test_mod_enhancedchoice_core_calendar_get_valid_event_timestart_range_close_event() {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/calendar/lib.php");

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $timeopen = time();
        $timeclose = $timeopen + DAYSECS;
        $enhancedchoice = new \stdClass();
        $enhancedchoice->timeopen = $timeopen;
        $enhancedchoice->timeclose = $timeclose;

        // Create a valid event.
        $event = new \calendar_event([
            'name' => 'Test event',
            'description' => '',
            'format' => 1,
            'courseid' => $course->id,
            'groupid' => 0,
            'userid' => 2,
            'modulename' => 'enhancedchoice',
            'instance' => 1,
            'eventtype' => ENHANCEDCHOICE_EVENT_TYPE_CLOSE,
            'timestart' => 1,
            'timeduration' => 86400,
            'visible' => 1
        ]);

        // The max limit should be bounded by the timeclose value.
        list ($min, $max) = mod_enhancedchoice_core_calendar_get_valid_event_timestart_range($event, $enhancedchoice);

        $this->assertEquals($timeopen, $min[0]);
        $this->assertNull($max);

        // No timeclose value should result in no upper limit.
        $enhancedchoice->timeopen = 0;
        list ($min, $max) = mod_enhancedchoice_core_calendar_get_valid_event_timestart_range($event, $enhancedchoice);

        $this->assertNull($min);
        $this->assertNull($max);
    }

    /**
     * Test enhancedchoice_user_submit_response for a enhancedchoice with specific options.
     * Options:
     * allowmultiple: false
     * limitanswers: false
     */
    public function test_enhancedchoice_user_submit_response_no_multiple_no_limits() {
        global $DB;
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $user2 = $generator->create_user();

        // User must be enrolled in the course for enhancedchoice limits to be honoured properly.
        $role = $DB->get_record('role', ['shortname' => 'student']);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $role->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, $role->id);

        // Create enhancedchoice, with updates allowed and a two options both limited to 1 response each.
        $enhancedchoice = $generator->get_plugin_generator('mod_enhancedchoice')->create_instance([
            'course' => $course->id,
            'allowupdate' => false,
            'limitanswers' => false,
            'allowmultiple' => false,
            'option' => ['red', 'green'],
        ]);
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);

        // Get the enhancedchoice, with options and limits included.
        $enhancedchoicewithoptions = enhancedchoice_get_enhancedchoice($enhancedchoice->id);
        $optionids = array_keys($enhancedchoicewithoptions->option);

        // Now, save an response which includes the first option.
        $this->assertNull(enhancedchoice_user_submit_response($optionids[0], $enhancedchoicewithoptions, $user->id, $course, $cm));

        // Confirm that saving again without changing the selected option will not throw a 'enhancedchoice full' exception.
        $this->assertNull(enhancedchoice_user_submit_response($optionids[1], $enhancedchoicewithoptions, $user->id, $course, $cm));

        // Confirm that saving a response for student 2 including the first option is allowed.
        $this->assertNull(enhancedchoice_user_submit_response($optionids[0], $enhancedchoicewithoptions, $user2->id, $course, $cm));

        // Confirm that trying to save multiple options results in an exception.
        $this->expectException('moodle_exception');
        enhancedchoice_user_submit_response([$optionids[1], $optionids[1]], $enhancedchoicewithoptions, $user->id, $course, $cm);
    }

    /**
     * Test enhancedchoice_user_submit_response for a enhancedchoice with specific options.
     * Options:
     * allowmultiple: true
     * limitanswers: false
     */
    public function test_enhancedchoice_user_submit_response_multiples_no_limits() {
        global $DB;
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $user2 = $generator->create_user();

        // User must be enrolled in the course for enhancedchoice limits to be honoured properly.
        $role = $DB->get_record('role', ['shortname' => 'student']);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $role->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, $role->id);

        // Create enhancedchoice, with updates allowed and a two options both limited to 1 response each.
        $enhancedchoice = $generator->get_plugin_generator('mod_enhancedchoice')->create_instance([
            'course' => $course->id,
            'allowupdate' => false,
            'allowmultiple' => true,
            'limitanswers' => false,
            'option' => ['red', 'green'],
        ]);
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);

        // Get the enhancedchoice, with options and limits included.
        $enhancedchoicewithoptions = enhancedchoice_get_enhancedchoice($enhancedchoice->id);
        $optionids = array_keys($enhancedchoicewithoptions->option);

        // Save a response which includes the first option only.
        $this->assertNull(enhancedchoice_user_submit_response([$optionids[0]], $enhancedchoicewithoptions, $user->id, $course, $cm));

        // Confirm that adding an option to the response is allowed.
        $this->assertNull(enhancedchoice_user_submit_response([$optionids[0], $optionids[1]], $enhancedchoicewithoptions, $user->id, $course, $cm));

        // Confirm that saving a response for student 2 including the first option is allowed.
        $this->assertNull(enhancedchoice_user_submit_response($optionids[0], $enhancedchoicewithoptions, $user2->id, $course, $cm));

        // Confirm that removing an option from the response is allowed.
        $this->assertNull(enhancedchoice_user_submit_response([$optionids[0]], $enhancedchoicewithoptions, $user->id, $course, $cm));

        // Confirm that removing all options from the response is not allowed via this method.
        $this->expectException('moodle_exception');
        enhancedchoice_user_submit_response([], $enhancedchoicewithoptions, $user->id, $course, $cm);
    }

    /**
     * Test enhancedchoice_user_submit_response for a enhancedchoice with specific options.
     * Options:
     * allowmultiple: false
     * limitanswers: true
     */
    public function test_enhancedchoice_user_submit_response_no_multiples_limits() {
        global $DB;
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $user2 = $generator->create_user();

        // User must be enrolled in the course for enhancedchoice limits to be honoured properly.
        $role = $DB->get_record('role', ['shortname' => 'student']);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $role->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, $role->id);

        // Create enhancedchoice, with updates allowed and a two options both limited to 1 response each.
        $enhancedchoice = $generator->get_plugin_generator('mod_enhancedchoice')->create_instance([
            'course' => $course->id,
            'allowupdate' => false,
            'allowmultiple' => false,
            'limitanswers' => true,
            'option' => ['red', 'green'],
            'limit' => [1, 1]
        ]);
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);

        // Get the enhancedchoice, with options and limits included.
        $enhancedchoicewithoptions = enhancedchoice_get_enhancedchoice($enhancedchoice->id);
        $optionids = array_keys($enhancedchoicewithoptions->option);

        // Save a response which includes the first option only.
        $this->assertNull(enhancedchoice_user_submit_response($optionids[0], $enhancedchoicewithoptions, $user->id, $course, $cm));

        // Confirm that changing the option in the response is allowed.
        $this->assertNull(enhancedchoice_user_submit_response($optionids[1], $enhancedchoicewithoptions, $user->id, $course, $cm));

        // Confirm that limits are respected by trying to save the same option as another user.
        $this->expectException('moodle_exception');
        enhancedchoice_user_submit_response($optionids[1], $enhancedchoicewithoptions, $user2->id, $course, $cm);
    }

    /**
     * Test enhancedchoice_user_submit_response for a enhancedchoice with specific options.
     * Options:
     * allowmultiple: true
     * limitanswers: true
     */
    public function test_enhancedchoice_user_submit_response_multiples_limits() {
        global $DB;
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $user2 = $generator->create_user();

        // User must be enrolled in the course for enhancedchoice limits to be honoured properly.
        $role = $DB->get_record('role', ['shortname' => 'student']);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $role->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, $role->id);

        // Create enhancedchoice, with updates allowed and a two options both limited to 1 response each.
        $enhancedchoice = $generator->get_plugin_generator('mod_enhancedchoice')->create_instance([
            'course' => $course->id,
            'allowupdate' => false,
            'allowmultiple' => true,
            'limitanswers' => true,
            'option' => ['red', 'green'],
            'limit' => [1, 1]
        ]);
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);

        // Get the enhancedchoice, with options and limits included.
        $enhancedchoicewithoptions = enhancedchoice_get_enhancedchoice($enhancedchoice->id);
        $optionids = array_keys($enhancedchoicewithoptions->option);

        // Now, save a response which includes the first option only.
        $this->assertNull(enhancedchoice_user_submit_response([$optionids[0]], $enhancedchoicewithoptions, $user->id, $course, $cm));

        // Confirm that changing the option in the response is allowed.
        $this->assertNull(enhancedchoice_user_submit_response([$optionids[1]], $enhancedchoicewithoptions, $user->id, $course, $cm));

        // Confirm that adding an option to the response is allowed.
        $this->assertNull(enhancedchoice_user_submit_response([$optionids[0], $optionids[1]], $enhancedchoicewithoptions, $user->id, $course, $cm));

        // Confirm that limits are respected by trying to save the same option as another user.
        $this->expectException('moodle_exception');
        enhancedchoice_user_submit_response($optionids[1], $enhancedchoicewithoptions, $user2->id, $course, $cm);
    }

    /**
     * A user who does not have capabilities to add events to the calendar should be able to create an enhancedchoice.
     */
    public function test_creation_with_no_calendar_capabilities() {
        $this->resetAfterTest();
        $course = self::getDataGenerator()->create_course();
        $context = context_course::instance($course->id);
        $user = self::getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $roleid = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($roleid, $user->id, $context->id);
        assign_capability('moodle/calendar:manageentries', CAP_PROHIBIT, $roleid, $context, true);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_enhancedchoice');
        // Create an instance as a user without the calendar capabilities.
        $this->setUser($user);
        $time = time();
        $params = array(
            'course' => $course->id,
            'timeopen' => $time + 200,
            'timeclose' => $time + 500,
        );
        $generator->create_instance($params);
    }
}
