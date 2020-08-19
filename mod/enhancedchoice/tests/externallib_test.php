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
 * External enhancedchoice functions unit tests
 *
 * @package    mod_enhancedchoice
 * @category   external
 * @copyright  2015 Costantino Cito <ccito@cvaconsulting.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/enhancedchoice/lib.php');

/**
 * External enhancedchoice functions unit tests
 *
 * @package    mod_enhancedchoice
 * @category   external
 * @copyright  2015 Costantino Cito <ccito@cvaconsulting.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_enhancedchoice_externallib_testcase extends externallib_advanced_testcase {

    /**
     * Test get_enhancedchoice_results
     */
    public function test_get_enhancedchoice_results() {
        global $DB;

        $this->resetAfterTest(true);

        $course = self::getDataGenerator()->create_course();
        $params = new stdClass();
        $params->course = $course->id;
        $params->option = array('fried rice', 'spring rolls', 'sweet and sour pork', 'satay beef', 'gyouza');
        $params->name = 'First EnhancedChoice Activity';
        $params->showresults = ENHANCEDCHOICE_SHOWRESULTS_AFTER_ANSWER;
        $params->publish = 1;
        $params->allowmultiple = 1;
        $params->showunanswered = 1;
        $enhancedchoice = self::getDataGenerator()->create_module('enhancedchoice', $params);

        $cm = get_coursemodule_from_id('enhancedchoice', $enhancedchoice->cmid);
        $enhancedchoiceinstance = enhancedchoice_get_enhancedchoice($cm->instance);
        $options = array_keys($enhancedchoiceinstance->option);
        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

        // Enroll Students in Course1.
        self::getDataGenerator()->enrol_user($student1->id,  $course->id, $studentrole->id);
        self::getDataGenerator()->enrol_user($student2->id,  $course->id, $studentrole->id);

        $this->setUser($student1);
        $myanswer = $options[2];
        enhancedchoice_user_submit_response($myanswer, $enhancedchoice, $student1->id, $course, $cm);
        $results = mod_enhancedchoice_external::get_enhancedchoice_results($enhancedchoice->id);
        // We need to execute the return values cleaning process to simulate the web service server.
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::get_enhancedchoice_results_returns(), $results);

        // Create an array with optionID as Key.
        $resultsarr = array();
        foreach ($results['options'] as $option) {
            $resultsarr[$option['id']] = $option['userresponses'];
        }
        // The stundent1 is the userid who choosed the myanswer(option 3).
        $this->assertEquals($resultsarr[$myanswer][0]['userid'], $student1->id);
        // The stundent2 is the userid who didn't answered yet.
        $this->assertEquals($resultsarr[0][0]['userid'], $student2->id);

        // As Stundent2 we cannot see results (until we answered).
        $this->setUser($student2);
        $results = mod_enhancedchoice_external::get_enhancedchoice_results($enhancedchoice->id);
        // We need to execute the return values cleaning process to simulate the web service server.
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::get_enhancedchoice_results_returns(), $results);
        // We do not retrieve any response!
        foreach ($results['options'] as $option) {
            $this->assertCount(0, $option['userresponses']);
        }

        $timenow = time();
        // We can see results only after activity close (even if we didn't answered).
        $enhancedchoice->showresults = ENHANCEDCHOICE_SHOWRESULTS_AFTER_CLOSE;
        // Set timeopen and timeclose in the past.
        $enhancedchoice->timeopen = $timenow - (60 * 60 * 24 * 3);
        $enhancedchoice->timeclose = $timenow + (60 * 60 * 24 * 2);
        $DB->update_record('enhancedchoice', $enhancedchoice);

        $results = mod_enhancedchoice_external::get_enhancedchoice_results($enhancedchoice->id);
        // We need to execute the return values cleaning process to simulate the web service server.
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::get_enhancedchoice_results_returns(), $results);
        // We do not retrieve any response (activity is still open).
        foreach ($results['options'] as $option) {
            $this->assertCount(0, $option['userresponses']);
        }

        // We close the activity (setting timeclose in the past).
        $enhancedchoice->timeclose = $timenow - (60 * 60 * 24 * 2);
        $DB->update_record('enhancedchoice', $enhancedchoice);
        // Now as Stundent2 we will see results!
        $results = mod_enhancedchoice_external::get_enhancedchoice_results($enhancedchoice->id);
        // We need to execute the return values cleaning process to simulate the web service server.
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::get_enhancedchoice_results_returns(), $results);
        // Create an array with optionID as Key.
        $resultsarr = array();
        foreach ($results['options'] as $option) {
            $resultsarr[$option['id']] = $option['userresponses'];
        }
        // The stundent1 is the userid who choosed the myanswer(option 3).
        $this->assertEquals($resultsarr[$myanswer][0]['userid'], $student1->id);
        // The stundent2 is the userid who didn't answered yet.
        $this->assertEquals($resultsarr[0][0]['userid'], $student2->id);

        // Do not publish user names!
        $enhancedchoice->publish = 0;
        $DB->update_record('enhancedchoice', $enhancedchoice);
        $results = mod_enhancedchoice_external::get_enhancedchoice_results($enhancedchoice->id);
        // We need to execute the return values cleaning process to simulate the web service server.
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::get_enhancedchoice_results_returns(), $results);
        // Create an array with optionID as Key.
        $resultsarr = array();
        // Does not show any user response!
        foreach ($results['options'] as $option) {
            $this->assertCount(0, $option['userresponses']);
            $resultsarr[$option['id']] = $option;
        }
        // But we can see totals and percentages.
        $this->assertEquals(1, $resultsarr[$myanswer]['numberofuser']);
    }

    /**
     * Test get_enhancedchoice_options
     */
    public function test_get_enhancedchoice_options() {
        global $DB;

        // Warningcodes.
        $notopenyet = 1;
        $previewonly = 2;
        $expired = 3;

        $this->resetAfterTest(true);
        $timenow = time();
        $timeopen = $timenow + (60 * 60 * 24 * 2);
        $timeclose = $timenow + (60 * 60 * 24 * 7);
        $course = self::getDataGenerator()->create_course();
        $possibleoptions = array('fried rice', 'spring rolls', 'sweet and sour pork', 'satay beef', 'gyouza');
        $params = array();
        $params['course'] = $course->id;
        $params['option'] = $possibleoptions;
        $params['name'] = 'First EnhancedChoice Activity';
        $params['showpreview'] = 0;

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_enhancedchoice');
        $enhancedchoice = $generator->create_instance($params);

        $student1 = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        // Enroll Students in Course.
        self::getDataGenerator()->enrol_user($student1->id,  $course->id, $studentrole->id);
        $this->setUser($student1);

        $results = mod_enhancedchoice_external::get_enhancedchoice_options($enhancedchoice->id);
        // We need to execute the return values cleaning process to simulate the web service server.
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::get_enhancedchoice_options_returns(), $results);
        // We should retrieve all options.
        $this->assertCount(count($possibleoptions), $results['options']);

        // Here we force timeopen/close in the future.
        $enhancedchoice->timeopen = $timeopen;
        $enhancedchoice->timeclose = $timeclose;
        $DB->update_record('enhancedchoice', $enhancedchoice);

        $results = mod_enhancedchoice_external::get_enhancedchoice_options($enhancedchoice->id);
        // We need to execute the return values cleaning process to simulate the web service server.
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::get_enhancedchoice_options_returns(), $results);
        // We should retrieve no options.
        $this->assertCount(0, $results['options']);
        $this->assertEquals($notopenyet, $results['warnings'][0]['warningcode']);

        // Here we see the options because of preview!
        $enhancedchoice->showpreview = 1;
        $DB->update_record('enhancedchoice', $enhancedchoice);
        $results = mod_enhancedchoice_external::get_enhancedchoice_options($enhancedchoice->id);
        // We need to execute the return values cleaning process to simulate the web service server.
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::get_enhancedchoice_options_returns(), $results);
        // We should retrieve all options.
        $this->assertCount(count($possibleoptions), $results['options']);

        foreach ($results['options'] as $option) {
            // Each option is disabled as this is only the preview!
            $this->assertEquals(1, $option['disabled']);
        }
        $warnings = array();
        foreach ($results['warnings'] as $warning) {
            $warnings[$warning['warningcode']] = $warning['message'];
        }
        $this->assertTrue(isset($warnings[$previewonly]));
        $this->assertTrue(isset($warnings[$notopenyet]));

        // Simulate activity as opened!
        $enhancedchoice->timeopen = $timenow - (60 * 60 * 24 * 3);
        $enhancedchoice->timeclose = $timenow + (60 * 60 * 24 * 2);
        $DB->update_record('enhancedchoice', $enhancedchoice);
        $cm = get_coursemodule_from_id('enhancedchoice', $enhancedchoice->cmid);
        $enhancedchoiceinstance = enhancedchoice_get_enhancedchoice($cm->instance);
        $optionsids = array_keys($enhancedchoiceinstance->option);
        $myanswerid = $optionsids[2];
        enhancedchoice_user_submit_response($myanswerid, $enhancedchoice, $student1->id, $course, $cm);

        $results = mod_enhancedchoice_external::get_enhancedchoice_options($enhancedchoice->id);
        // We need to execute the return values cleaning process to simulate the web service server.
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::get_enhancedchoice_options_returns(), $results);
        // We should retrieve all options.
        $this->assertCount(count($possibleoptions), $results['options']);
        foreach ($results['options'] as $option) {
            // When we answered and we cannot update our enhancedchoice.
            if ($option['id'] == $myanswerid and !$enhancedchoice->allowupdate) {
                $this->assertEquals(1, $option['disabled']);
                $this->assertEquals(1, $option['checked']);
            } else {
                $this->assertEquals(0, $option['disabled']);
            }
        }

        // Set timeopen and timeclose as older than today!
        // We simulate what happens when the activity is closed.
        $enhancedchoice->timeopen = $timenow - (60 * 60 * 24 * 3);
        $enhancedchoice->timeclose = $timenow - (60 * 60 * 24 * 2);
        $DB->update_record('enhancedchoice', $enhancedchoice);
        $results = mod_enhancedchoice_external::get_enhancedchoice_options($enhancedchoice->id);
        // We need to execute the return values cleaning process to simulate the web service server.
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::get_enhancedchoice_options_returns(), $results);
        // We should retrieve no options.
        $this->assertCount(0, $results['options']);
        $this->assertEquals($expired, $results['warnings'][0]['warningcode']);

    }

    /**
     * Test submit_enhancedchoice_response
     */
    public function test_submit_enhancedchoice_response() {
        global $DB;

        $this->resetAfterTest(true);

        $course = self::getDataGenerator()->create_course();
        $params = new stdClass();
        $params->course = $course->id;
        $params->option = array('fried rice', 'spring rolls', 'sweet and sour pork', 'satay beef', 'gyouza');
        $params->name = 'First EnhancedChoice Activity';
        $params->showresults = ENHANCEDCHOICE_SHOWRESULTS_ALWAYS;
        $params->allowmultiple = 1;
        $params->showunanswered = 1;
        $enhancedchoice = self::getDataGenerator()->create_module('enhancedchoice', $params);
        $cm = get_coursemodule_from_id('enhancedchoice', $enhancedchoice->cmid);
        $enhancedchoiceinstance = enhancedchoice_get_enhancedchoice($cm->instance);
        $options = array_keys($enhancedchoiceinstance->option);
        $student1 = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

        // Enroll Students in Course1.
        self::getDataGenerator()->enrol_user($student1->id,  $course->id, $studentrole->id);

        $this->setUser($student1);
        $myresponse = $options[2];
        $results = mod_enhancedchoice_external::submit_enhancedchoice_response($enhancedchoice->id, array($myresponse));
        // We need to execute the return values cleaning process to simulate the web service server.
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::submit_enhancedchoice_response_returns(), $results);
        $myanswers = $DB->get_records('enhancedchoice_answers', array('enhancedchoiceid' => $enhancedchoice->id, 'userid' => $student1->id));
        $myanswer = reset($myanswers);
        $this->assertEquals($results['answers'][0]['id'], $myanswer->id);
        $this->assertEquals($results['answers'][0]['enhancedchoiceid'], $myanswer->enhancedchoiceid);
        $this->assertEquals($results['answers'][0]['userid'], $myanswer->userid);
        $this->assertEquals($results['answers'][0]['timemodified'], $myanswer->timemodified);
    }

    /**
     * Test view_enhancedchoice
     */
    public function test_view_enhancedchoice() {
        global $DB;

        $this->resetAfterTest(true);

        // Setup test data.
        $course = $this->getDataGenerator()->create_course();
        $enhancedchoice = $this->getDataGenerator()->create_module('enhancedchoice', array('course' => $course->id));
        $context = context_module::instance($enhancedchoice->cmid);
        $cm = get_coursemodule_from_instance('enhancedchoice', $enhancedchoice->id);

        // Test invalid instance id.
        try {
            mod_enhancedchoice_external::view_enhancedchoice(0);
            $this->fail('Exception expected due to invalid mod_enhancedchoice instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidcoursemodule', $e->errorcode);
        }

        // Test not-enrolled user.
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        try {
            mod_enhancedchoice_external::view_enhancedchoice($enhancedchoice->id);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

        // Test user with full capabilities.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();

        $result = mod_enhancedchoice_external::view_enhancedchoice($enhancedchoice->id);
        $result = external_api::clean_returnvalue(mod_enhancedchoice_external::view_enhancedchoice_returns(), $result);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_enhancedchoice\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodleenhancedchoice = new \moodle_url('/mod/enhancedchoice/view.php', array('id' => $cm->id));
        $this->assertEquals($moodleenhancedchoice, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

    }

    /**
     * Test get_enhancedchoices_by_courses
     */
    public function test_get_enhancedchoices_by_courses() {
        global $DB;
        $this->resetAfterTest(true);
        // As admin.
        $this->setAdminUser();
        $course1 = self::getDataGenerator()->create_course();
        $enhancedchoiceoptions1 = array(
          'course' => $course1->id,
          'name' => 'First IMSCP'
        );
        $enhancedchoice1 = self::getDataGenerator()->create_module('enhancedchoice', $enhancedchoiceoptions1);
        $course2 = self::getDataGenerator()->create_course();

        $enhancedchoiceoptions2 = array(
          'course' => $course2->id,
          'name' => 'Second IMSCP'
        );
        $enhancedchoice2 = self::getDataGenerator()->create_module('enhancedchoice', $enhancedchoiceoptions2);
        $student1 = $this->getDataGenerator()->create_user();

        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

        // Enroll Student1 in Course1.
        self::getDataGenerator()->enrol_user($student1->id,  $course1->id, $studentrole->id);

        $this->setUser($student1);
        $enhancedchoices = mod_enhancedchoice_external::get_enhancedchoices_by_courses(array());
        $enhancedchoices = external_api::clean_returnvalue(mod_enhancedchoice_external::get_enhancedchoices_by_courses_returns(), $enhancedchoices);
        $this->assertCount(1, $enhancedchoices['enhancedchoices']);
        $this->assertEquals('First IMSCP', $enhancedchoices['enhancedchoices'][0]['name']);
        // As Student you cannot see some IMSCP properties like 'section'.
        $this->assertFalse(isset($enhancedchoices['enhancedchoices'][0]['section']));

        // Student1 is not enrolled in this Course.
        // The webservice will give a warning!
        $enhancedchoices = mod_enhancedchoice_external::get_enhancedchoices_by_courses(array($course2->id));
        $enhancedchoices = external_api::clean_returnvalue(mod_enhancedchoice_external::get_enhancedchoices_by_courses_returns(), $enhancedchoices);
        $this->assertCount(0, $enhancedchoices['enhancedchoices']);
        $this->assertEquals(1, $enhancedchoices['warnings'][0]['warningcode']);

        // Now as admin.
        $this->setAdminUser();
        // As Admin we can see this IMSCP.
        $enhancedchoices = mod_enhancedchoice_external::get_enhancedchoices_by_courses(array($course2->id));
        $enhancedchoices = external_api::clean_returnvalue(mod_enhancedchoice_external::get_enhancedchoices_by_courses_returns(), $enhancedchoices);
        $this->assertCount(1, $enhancedchoices['enhancedchoices']);
        $this->assertEquals('Second IMSCP', $enhancedchoices['enhancedchoices'][0]['name']);
        // As an Admin you can see some IMSCP properties like 'section'.
        $this->assertEquals(0, $enhancedchoices['enhancedchoices'][0]['section']);

        // Now, prohibit capabilities.
        $this->setUser($student1);
        $contextcourse1 = context_course::instance($course1->id);
        // Prohibit capability = mod:enhancedchoice:choose on Course1 for students.
        assign_capability('mod/enhancedchoice:choose', CAP_PROHIBIT, $studentrole->id, $contextcourse1->id);
        accesslib_clear_all_caches_for_unit_testing();

        $enhancedchoices = mod_enhancedchoice_external::get_enhancedchoices_by_courses(array($course1->id));
        $enhancedchoices = external_api::clean_returnvalue(mod_enhancedchoice_external::get_enhancedchoices_by_courses_returns(), $enhancedchoices);
        $this->assertFalse(isset($enhancedchoices['enhancedchoices'][0]['timeopen']));
    }

    /**
     * Test delete_enhancedchoice_responses
     */
    public function test_delete_enhancedchoice_responses() {
        global $DB;

        $this->resetAfterTest(true);

        $course = self::getDataGenerator()->create_course();
        $params = new stdClass();
        $params->course = $course->id;
        $params->option = array('fried rice', 'spring rolls', 'sweet and sour pork', 'satay beef', 'gyouza');
        $params->name = 'First EnhancedChoice Activity';
        $params->showresults = ENHANCEDCHOICE_SHOWRESULTS_ALWAYS;
        $params->allowmultiple = 1;
        $params->showunanswered = 1;
        $enhancedchoice = self::getDataGenerator()->create_module('enhancedchoice', $params);
        $cm = get_coursemodule_from_id('enhancedchoice', $enhancedchoice->cmid);

        $enhancedchoiceinstance = enhancedchoice_get_enhancedchoice($cm->instance);
        $options = array_keys($enhancedchoiceinstance->option);

        $student = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

        // Enroll student in Course1.
        self::getDataGenerator()->enrol_user($student->id,  $course->id, $studentrole->id);

        $this->setUser($student);
        $results = mod_enhancedchoice_external::submit_enhancedchoice_response($enhancedchoice->id, array($options[1], $options[2]));
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::submit_enhancedchoice_response_returns(), $results);

        $myresponses = array_keys(enhancedchoice_get_my_response($enhancedchoice));

        // Try to delete responses when allow update is false.
        try {
            mod_enhancedchoice_external::delete_enhancedchoice_responses($enhancedchoice->id, array($myresponses[0], $myresponses[0]));
            $this->fail('Exception expected due to missing permissions.');
        } catch (required_capability_exception $e) {
            $this->assertEquals('nopermissions', $e->errorcode);
        }

        // Set allow update to true, and a passed time close.
        $DB->set_field('enhancedchoice', 'allowupdate', 1, array('id' => $enhancedchoice->id));
        $DB->set_field('enhancedchoice', 'timeclose', time() - DAYSECS, array('id' => $enhancedchoice->id));
        try {
            mod_enhancedchoice_external::delete_enhancedchoice_responses($enhancedchoice->id, array($myresponses[0], $myresponses[1]));
            $this->fail('Exception expected due to expired enhancedchoice.');
        } catch (moodle_exception $e) {
            $this->assertEquals('expired', $e->errorcode);
        }

        // Reset time close. We should be able now to delete all the responses.
        $DB->set_field('enhancedchoice', 'timeclose', 0, array('id' => $enhancedchoice->id));
        $results = mod_enhancedchoice_external::delete_enhancedchoice_responses($enhancedchoice->id, array($myresponses[0], $myresponses[1]));
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::delete_enhancedchoice_responses_returns(), $results);

        $this->assertTrue($results['status']);
        $this->assertCount(0, $results['warnings']);
        // Now, in the DB 0 responses.
        $this->assertCount(0, enhancedchoice_get_my_response($enhancedchoice));

        // Submit again the responses.
        $results = mod_enhancedchoice_external::submit_enhancedchoice_response($enhancedchoice->id, array($options[1], $options[2]));
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::submit_enhancedchoice_response_returns(), $results);

        $myresponses = array_keys(enhancedchoice_get_my_response($enhancedchoice));
        // Delete only one response.
        $results = mod_enhancedchoice_external::delete_enhancedchoice_responses($enhancedchoice->id, array($myresponses[0]));
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::delete_enhancedchoice_responses_returns(), $results);
        $this->assertTrue($results['status']);
        $this->assertCount(0, $results['warnings']);
        // Now, in the DB 1 response still.
        $this->assertCount(1, enhancedchoice_get_my_response($enhancedchoice));

        // Delete the remaining response, passing 2 invalid responses ids.
        $results = mod_enhancedchoice_external::delete_enhancedchoice_responses($enhancedchoice->id, array($myresponses[1], $myresponses[0] + 2,
                                                                $myresponses[0] + 3));
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::delete_enhancedchoice_responses_returns(), $results);
        $this->assertTrue($results['status']);
        // 2 warnings, 2 invalid responses.
        $this->assertCount(2, $results['warnings']);
        // Now, in the DB 0 responses.
        $this->assertCount(0, enhancedchoice_get_my_response($enhancedchoice));

        // Now, as an admin we must be able to delete all the responses under any condition.
        $this->setUser($student);
        // Submit again the responses.
        $results = mod_enhancedchoice_external::submit_enhancedchoice_response($enhancedchoice->id, array($options[1], $options[2]));
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::submit_enhancedchoice_response_returns(), $results);
        $studentresponses = array_keys(enhancedchoice_get_my_response($enhancedchoice));

        $this->setAdminUser();
        $DB->set_field('enhancedchoice', 'allowupdate', 0, array('id' => $enhancedchoice->id));
        $DB->set_field('enhancedchoice', 'timeclose', time() - DAYSECS, array('id' => $enhancedchoice->id));

        $results = mod_enhancedchoice_external::delete_enhancedchoice_responses($enhancedchoice->id, array($studentresponses[0], $studentresponses[1]));
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::delete_enhancedchoice_responses_returns(), $results);

        $this->assertTrue($results['status']);
        $this->assertCount(0, $results['warnings']);

        // Submit again the responses.
        $this->setUser($student);
        $DB->set_field('enhancedchoice', 'timeclose', 0, array('id' => $enhancedchoice->id));
        $results = mod_enhancedchoice_external::submit_enhancedchoice_response($enhancedchoice->id, array($options[1], $options[2]));
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::submit_enhancedchoice_response_returns(), $results);

        // Test admin try to delete his own responses (he didn't respond so nothing should be deleted).
        $this->setAdminUser();
        $results = mod_enhancedchoice_external::delete_enhancedchoice_responses($enhancedchoice->id);
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::delete_enhancedchoice_responses_returns(), $results);
        $this->assertFalse($results['status']);
        $this->assertCount(0, $results['warnings']);
        $allresponses = enhancedchoice_get_all_responses($enhancedchoice);
        $this->assertCount(2, $allresponses);   // No responses deleted (admin didn't submit any).

        // Now admin submit a couple of responses more.
        $results = mod_enhancedchoice_external::submit_enhancedchoice_response($enhancedchoice->id, array($options[1], $options[2]));
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::submit_enhancedchoice_response_returns(), $results);
        $allresponses = enhancedchoice_get_all_responses($enhancedchoice);
        $this->assertCount(4, $allresponses);
        // Admin responses are deleted when passing an empty array.
        $results = mod_enhancedchoice_external::delete_enhancedchoice_responses($enhancedchoice->id);
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::delete_enhancedchoice_responses_returns(), $results);
        $this->assertTrue($results['status']);
        $this->assertCount(0, $results['warnings']);
        $allresponses = enhancedchoice_get_all_responses($enhancedchoice);
        $this->assertCount(2, $allresponses);

        // Now admin will delete all the other users responses.
        $results = mod_enhancedchoice_external::delete_enhancedchoice_responses($enhancedchoice->id, array_keys($allresponses));
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::delete_enhancedchoice_responses_returns(), $results);

        $this->assertTrue($results['status']);
        $this->assertCount(0, $results['warnings']);
        $allresponses = enhancedchoice_get_all_responses($enhancedchoice);
        $this->assertCount(0, $allresponses);   // Now all the responses were deleted.

        // Admin try do delete an invalid response.
        $results = mod_enhancedchoice_external::delete_enhancedchoice_responses($enhancedchoice->id, array(-1));
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::delete_enhancedchoice_responses_returns(), $results);

        $this->assertFalse($results['status']);
        $this->assertCount(1, $results['warnings']);

        // Now, in the DB 0 responses.
        $this->setUser($student);

        // Submit again respones.
        $DB->set_field('enhancedchoice', 'allowupdate', 1, array('id' => $enhancedchoice->id));
        $DB->set_field('enhancedchoice', 'timeclose', 0, array('id' => $enhancedchoice->id));
        $results = mod_enhancedchoice_external::submit_enhancedchoice_response($enhancedchoice->id, array($options[1], $options[2]));
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::submit_enhancedchoice_response_returns(), $results);

        // Delete all responses.
        $results = mod_enhancedchoice_external::delete_enhancedchoice_responses($enhancedchoice->id);
        $results = external_api::clean_returnvalue(mod_enhancedchoice_external::delete_enhancedchoice_responses_returns(), $results);

        $this->assertTrue($results['status']);
        $this->assertCount(0, $results['warnings']);
        $this->assertCount(0, enhancedchoice_get_my_response($enhancedchoice));

    }
}
