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
 * Restore date tests.
 *
 * @package    mod_enhancedchoice
 * @copyright  2017 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . "/phpunit/classes/restore_date_testcase.php");

/**
 * Restore date tests.
 *
 * @package    mod_enhancedchoice
 * @copyright  2017 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_enhancedchoice_restore_date_testcase extends restore_date_testcase {

    public function test_restore_dates() {
        global $DB, $USER;

        $time = 100000;
        $record = ['timeopen' => $time, 'timeclose' => $time + 1];
        list($course, $enhancedchoice) = $this->create_course_and_module('enhancedchoice', $record);

        $options = $DB->get_records('enhancedchoice_options', ['enhancedchoiceid' => $enhancedchoice->id]);
        $DB->set_field('enhancedchoice_options', 'timemodified', $time);
        $option = reset($options);
        $cm = $DB->get_record('course_modules', ['id' => $enhancedchoice->cmid]);
        enhancedchoice_user_submit_response($option->id, $enhancedchoice, $USER->id, $course, $cm);
        $answer = $DB->get_record('enhancedchoice_answers', ['enhancedchoiceid' => $enhancedchoice->id]);

        // Do backup and restore.
        $newcourseid = $this->backup_and_restore($course);
        $newenhancedchoice = $DB->get_record('enhancedchoice', ['course' => $newcourseid]);
        $newoptions = $DB->get_records('enhancedchoice_options', ['enhancedchoiceid' => $newenhancedchoice->id]);

        $this->assertFieldsNotRolledForward($enhancedchoice, $newenhancedchoice, ['timemodified']);
        $props = ['timeopen', 'timeclose'];
        $this->assertFieldsRolledForward($enhancedchoice, $newenhancedchoice, $props);

        // Options check.
        foreach ($newoptions as $newoption) {
            $this->assertEquals($time, $newoption->timemodified);
        }

        // Answers check.
        $newanswer = $DB->get_record('enhancedchoice_answers', ['enhancedchoiceid' => $newenhancedchoice->id]);
        $this->assertEquals($answer->timemodified, $newanswer->timemodified);
    }
}
