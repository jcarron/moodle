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
 * The mod_enhancedchoice answer updated event.
 *
 * @package    mod_enhancedchoice
 * @copyright  2013 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_enhancedchoice\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_enhancedchoice answer updated event class.
 *
 * This event is deprecated in Moodle 3.2, it can no longer be triggered, do not
 * write event observers for it. This event can only be initiated during
 * restore from previous Moodle versions and appear in the logs.
 *
 * Event observers should listen to mod_enhancedchoice\event\answer_created and
 * mod_enhancedchoice\event\answer_deleted instead, these events will be triggered for
 * each option that was user has selected or unselected
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int enhancedchoiceid: id of enhancedchoice.
 *      - int optionid: (optional) id of option.
 * }
 *
 * @deprecated since 3.2
 * @package    mod_enhancedchoice
 * @since      Moodle 2.6
 * @copyright  2013 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class answer_updated extends \core\event\base {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' updated their enhancedchoice with id '$this->objectid' in the enhancedchoice activity
            with course module id '$this->contextinstanceid'.";
    }

    /**
     * Return legacy data for add_to_log().
     *
     * @return array
     */
    protected function get_legacy_logdata() {
        $legacylogdata = array($this->courseid,
            'enhancedchoice',
            'choose again',
            'view.php?id=' . $this->contextinstanceid,
            $this->other['enhancedchoiceid'],
            $this->contextinstanceid);

        return $legacylogdata;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventanswerupdated', 'mod_enhancedchoice');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/enhancedchoice/view.php', array('id' => $this->contextinstanceid));
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        // The objecttable here is wrong. We are updating an answer, not a enhancedchoice activity.
        // This also makes the description misleading as it states we made a enhancedchoice with id
        // '$this->objectid' which just refers to the 'enhancedchoice' table. The trigger for
        // this event should be triggered after we update the 'enhancedchoice_answers' table.
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'enhancedchoice';
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        debugging('Event \\mod_enhancedchoice\event\\answer_updated should not be used '
                . 'any more for triggering new events and can only be initiated during restore. '
                . 'For new events please use \\mod_enhancedchoice\\event\\answer_created '
                . 'and  \\mod_enhancedchoice\\event\\answer_deleted', DEBUG_DEVELOPER);

        if (!isset($this->other['enhancedchoiceid'])) {
            throw new \coding_exception('The \'enhancedchoiceid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'enhancedchoice', 'restore' => 'enhancedchoice');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['enhancedchoiceid'] = array('db' => 'enhancedchoice', 'restore' => 'enhancedchoice');

        // The 'optionid' is being passed as an array, so we can't map it. The event is
        // triggered each time a enhancedchoice is answered, where it may be possible to select
        // multiple enhancedchoices, so the value is converted to an array, which is then passed
        // to the event. Ideally this event should be triggered every time we update the
        // 'enhancedchoice_answers' table so this will only be an int.
        $othermapped['optionid'] = \core\event\base::NOT_MAPPED;

        return $othermapped;
    }

    public static function is_deprecated() {
        return true;
    }
}
