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
 * Version details
 *
 * @package    theme_adaptable
 * @copyright  2015 Jeremy Hopkins (Coventry University)
 * @copyright  2015-2017 Fernando Acedo (3-bits.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

// Load libraries.
require_once($CFG->dirroot.'/course/renderer.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/message/lib.php');
require_once($CFG->dirroot.'/course/format/topics/renderer.php');
require_once($CFG->dirroot.'/course/format/weeks/renderer.php');

use \theme_adaptable\traits\single_section_page;

/**
 * Class for implementing topics format rendering.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 * @copyright 2017 Gareth J Barnard
 *
 */
class theme_adaptable_format_topics_renderer extends format_topics_renderer {
    use single_section_page;
}

/**
 * Class for implementing weeks format rendering.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 * @copyright 2017 Gareth J Barnard
 *
 */
class theme_adaptable_format_weeks_renderer extends format_weeks_renderer {
    use single_section_page;
}

/******************************************************************************************
 * @copyright 2017 Gareth J Barnard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 * @copyright 2017 Gareth J Barnard
 *
 * Grid format renderer for the Adaptable theme.
 */

// Check if GRID is installed before trying to override it.
if (file_exists("$CFG->dirroot/course/format/grid/renderer.php")) {
    include_once($CFG->dirroot."/course/format/grid/renderer.php");

    /**
     * Class for implementing grid format rendering.
     * @copyright 2017 Gareth J Barnard
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
     *
     */
    class theme_adaptable_format_grid_renderer extends format_grid_renderer {
        use single_section_page;

        /**
         * Generate the html for the 'Jump to' menu on a single section page.
         *
         * @param stdClass $course The course entry from DB.
         * @param array $sections The course_sections entries from the DB.
         * @param bool $displaysection the current displayed section number.
         *
         * @return string HTML to output.
         */
        protected function section_nav_selection($course, $sections, $displaysection) {
            $settings = $this->courseformat->get_settings();
            if (!$this->section0attop) {
                $section = 0;
            } else if ($settings['setsection0ownpagenogridonesection'] == 2) {
                $section = 0;
            } else {
                $section = 1;
            }
            return $this->section_nav_selection_content($course, $sections, $displaysection, $section);
        }

        /**
         * Generate next/previous section links for navigation.
         *
         * @param stdClass $course The course entry from DB.
         * @param array $sections The course_sections entries from the DB.
         * @param int $sectionno The section number in the coruse which is being displayed.
         * @return array associative array with previous and next section link.
         */
        public function get_nav_links($course, $sections, $sectionno) {
            $settings = $this->courseformat->get_settings();
            if (!$this->section0attop) {
                $buffer = -1;
            } else if ($settings['setsection0ownpagenogridonesection'] == 2) {
                $buffer = -1;
            } else {
                $buffer = 0;
            }
            return $this->get_nav_links_content($course, $sections, $sectionno, $buffer);
        }

        /**
         * Output the html for a single section page.
         *
         * @param stdClass $course The course entry from DB.
         * @param array $sections (argument not used).
         * @param array $mods (argument not used).
         * @param array $modnames (argument not used).
         * @param array $modnamesused (argument not used).
         * @param int $displaysection The section number in the course which is being displayed.
         */
        public function print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {
            $settings = $this->courseformat->get_settings();
            if (!$this->section0attop) {
                $section0attop = 0;
            } else if ($settings['setsection0ownpagenogridonesection'] == 2) {
                $section0attop = 0;
            } else {
                $section0attop = 1;
            }
            $this->print_single_section_page_content($course, $sections, $mods, $modnames, $modnamesused, $displaysection,
                $section0attop);
        }
    }
}

// Check if Flexible is installed before trying to override it.
if (file_exists("$CFG->dirroot/course/format/flexible/renderer.php")) {
    include_once($CFG->dirroot."/course/format/flexible/renderer.php");

    /**
     * @copyright 2019 Gareth J Barnard
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
     *
     * Flexible format renderer for the Adaptable theme.
     */
    class theme_adaptable_format_flexible_renderer extends format_flexible_renderer {
        use single_section_page;

        /**
         * Generate the html for the 'Jump to' menu on a single section page.
         *
         * @param stdClass $course The course entry from DB.
         * @param array $sections The course_sections entries from the DB.
         * @param bool $displaysection the current displayed section number.
         *
         * @return string HTML to output.
         */
        protected function section_nav_selection($course, $sections, $displaysection) {
            if ($this->settings['section0attop'] == 2) { // One is 'Top' and two is 'Grid'.
                $section = 0;
            } else {
                $section = 1;
            }
            return $this->section_nav_selection_content($course, $sections, $displaysection, $section);
        }

        /**
         * Generate next/previous section links for navigation.
         *
         * @param stdClass $course The course entry from DB.
         * @param array $sections The course_sections entries from the DB.
         * @param int $sectionno The section number in the coruse which is being displayed.
         * @return array associative array with previous and next section link.
         */
        public function get_nav_links($course, $sections, $sectionno) {
            if ($this->settings['section0attop'] == 2) { // One is 'Top' and two is 'Grid'.
                $buffer = -1;
            } else {
                $buffer = 0;
            }
            return $this->get_nav_links_content($course, $sections, $sectionno, $buffer);
        }

        /**
         * Output the html for a single section page.
         *
         * @param stdClass $course The course entry from DB.
         * @param array $sections (argument not used).
         * @param array $mods (argument not used).
         * @param array $modnames (argument not used).
         * @param array $modnamesused (argument not used).
         * @param int $displaysection The section number in the course which is being displayed.
         */
        public function print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {
            $this->print_single_section_page_content($course, $sections, $mods, $modnames, $modnamesused, $displaysection,
                false);
        }
    }
}

define('ADAPTABLE_COURSE_STARRED', 'starred');
define('ADAPTABLE_COURSE_IN_PROGRESS', 'inprogress');
define('ADAPTABLE_COURSE_PAST', 'past');
define('ADAPTABLE_COURSE_FUTURE', 'future');
define('ADAPTABLE_COURSE_HIDDEN', 'hidden');

/**
 * Class for core renderer.
 *
 * @copyright 2015 Jeremy Hopkins (Coventry University)
 * @copyright 2015 Fernando Acedo (3-bits.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Core renderers for Adaptable theme
 */
class theme_adaptable_core_renderer extends core_renderer {
    /** @var custom_menu_item language The language menu if created */
    protected $language = null;

    /**
     * Outputs the opening section of a box.
     *
     * @param string $classes A space-separated list of CSS classes
     * @param string $id An optional ID
     * @param array $attributes An array of other
     * attributes to give the box.
     * @return string the HTML to output.
     */
    public function box_start($classes = 'generalbox', $id = null, $attributes = array()) {
        $this->opencontainers->push('box', html_writer::end_tag('div'));
        $attributes['id'] = $id;
        $attributes['class'] = 'box ' . renderer_base::prepare_classes($classes);
        return html_writer::start_tag('div', $attributes);
    }

    /**
     * Renders an action menu component.
     *
     * @param action_menu $menu
     * @return string HTML
     */
    public function render_action_menu(action_menu $menu) {
        global $CFG;

        if ($CFG->branch < 37) {
            // We don't want the class icon there!
            foreach ($menu->get_secondary_actions() as $action) {
                if ($action instanceof \action_menu_link && $action->has_class('icon')) {
                    $action->attributes['class'] = preg_replace('/(^|\s+)icon(\s+|$)/i', '', $action->attributes['class']);
                }
            }

            if ($menu->is_empty()) {
                return '';
            }
            $context = $menu->export_for_template($this);

            return $this->render_from_template('core/action_menu', $context);
        } else {
            return parent::render_action_menu($menu);
        }
    }

    /**
     * Return list of the user's courses
     *
     * @return array list of courses
     */
    public function render_mycourses() {
        // Set limit of courses to show in dropdown from setting.
        $coursedisplaylimit = '20';
        if (isset($this->page->theme->settings->mycoursesmenulimit)) {
            $coursedisplaylimit = $this->page->theme->settings->mycoursesmenulimit;
        }

        $courses = enrol_get_my_courses(
            join(',', array_keys(\core_course\external\course_summary_exporter::define_properties()))
        );

        $sortedcourses = array();
        $counter = 0;

        // Get courses in sort order into list.
        foreach ($courses as $course) {

            if (($counter >= $coursedisplaylimit) && ($coursedisplaylimit != 0)) {
                break;
            }

            $sortedcourses[] = $course;
            $counter++;

        }

        return array($sortedcourses);
    }



    /**
     * Returns the URL for the favicon.
     *
     * @return moodle_url The favicon Moodle URL.
     */
    public function favicon() {
        if (!empty($this->page->theme->settings->favicon)) {
            return \theme_adaptable\toolbox::get_setting_moodle_url('favicon', $this->page->theme);
        }
        return parent::favicon();
    }

    /**
     * Returns settings as formatted text
     *
     * @param string $setting
     * @param string $format = false
     * @param string $theme = null
     * @return string
     */
    public function get_setting($setting, $format = false, $theme = null) {
        if (empty($theme)) {
            $theme = theme_config::load('adaptable');
        }

        if (empty($theme->settings->$setting)) {
            return false;
        } else if (!$format) {
            return $theme->settings->$setting;
        } else if ($format === 'format_text') {
            return format_text($theme->settings->$setting, FORMAT_PLAIN);
        } else if ($format === 'format_html') {
            return format_text($theme->settings->$setting, FORMAT_HTML, array('trusted' => true));
        } else {
            return format_string($theme->settings->$setting);
        }
    }

    /**
     * Returns user profile menu
     */
    public function user_profile_menu() {
        global $CFG, $COURSE, $PAGE;
        $retval = '';

        // False or theme setting name to first array param (not all links have settings).
        // False or Moodle version number to second param (only some links check version).
        // URL for link in third param.
        // Link text in fourth parameter.
        // Icon in fifth param.
        $usermenuitems = array();
        $usermenuitems[] = array('enablemy', false, $CFG->wwwroot.'/my', get_string('myhome'),
            \theme_adaptable\toolbox::getfontawesomemarkup('dashboard'));
        $usermenuitems[] = array('enableprofile', false, $CFG->wwwroot.'/user/profile.php', get_string('viewprofile'),
            \theme_adaptable\toolbox::getfontawesomemarkup('user'));
        $usermenuitems[] = array('enableeditprofile', false, $CFG->wwwroot.'/user/edit.php', get_string('editmyprofile'),
            \theme_adaptable\toolbox::getfontawesomemarkup('cog'));
        $usermenuitems[] = array('enableprivatefiles', false, $CFG->wwwroot.'/user/files.php', get_string('privatefiles', 'block_private_files'),
             \theme_adaptable\toolbox::getfontawesomemarkup('file'));
        if (\theme_adaptable\toolbox::kalturaplugininstalled()) {
            $usermenuitems[] = array(false, false, $CFG->wwwroot.'/local/mymedia/mymedia.php', get_string('nav_mymedia', 'local_mymedia'),
                $this->pix_icon('my-media', '', 'local_mymedia'));
        }
        $usermenuitems[] = array('enablegrades', false, $CFG->wwwroot.'/grade/report/overview/index.php', get_string('grades'),
            \theme_adaptable\toolbox::getfontawesomemarkup('list-alt'));
        $usermenuitems[] = array('enablebadges', false, $CFG->wwwroot.'/badges/mybadges.php', get_string('badges'),
            \theme_adaptable\toolbox::getfontawesomemarkup('certificate'));
        $usermenuitems[] = array('enablepref', '2015051100', $CFG->wwwroot.'/user/preferences.php', get_string('preferences'),
            \theme_adaptable\toolbox::getfontawesomemarkup('cog'));
        $usermenuitems[] = array('enablenote', false, $CFG->wwwroot.'/message/edit.php', get_string('notifications'),
            \theme_adaptable\toolbox::getfontawesomemarkup('paper-plane'));
        $usermenuitems[] = array('enableblog', false, $CFG->wwwroot.'/blog/index.php', get_string('enableblog', 'theme_adaptable'),
            \theme_adaptable\toolbox::getfontawesomemarkup('rss'));
        $usermenuitems[] = array('enableposts', false, $CFG->wwwroot.'/mod/forum/user.php', get_string('enableposts', 'theme_adaptable'),
            \theme_adaptable\toolbox::getfontawesomemarkup('commenting'));
        $usermenuitems[] = array('enablefeed', false, $CFG->wwwroot.'/report/myfeedback/index.php',
            get_string('enablefeed', 'theme_adaptable'), \theme_adaptable\toolbox::getfontawesomemarkup('bullhorn'));
        $usermenuitems[] = array('enablecalendar', false, $CFG->wwwroot.'/calendar/view.php', get_string('pluginname', 'block_calendar_month'),
            \theme_adaptable\toolbox::getfontawesomemarkup('calendar'));

        $returnurl = $this->get_current_page_url(true);
        $context = context_course::instance($COURSE->id);

        if ((!is_role_switched($COURSE->id)) && (has_capability('moodle/role:switchroles', $context))) {
            // TBR $returnurl = str_replace().
            $url = $CFG->wwwroot.'/course/switchrole.php?id='.$COURSE->id.'&switchrole=-1&returnurl='.$returnurl;
                $usermenuitems[] = array(false, false, $url, get_string('switchroleto'),
                \theme_adaptable\toolbox::getfontawesomemarkup('user-o'));
        }

        if (is_role_switched($COURSE->id)) {
            $url = $CFG->wwwroot.'/course/switchrole.php?id='.$COURSE->id.'&sesskey='.sesskey().
            '&switchrole=0&returnurl='.$returnurl;

            $usermenuitems[] = array(false, false, $url, get_string('switchrolereturn'),
                \theme_adaptable\toolbox::getfontawesomemarkup('user-o'));
        }

        $usermenuitems[] = array(false, false, $CFG->wwwroot.'/login/logout.php?sesskey='.sesskey(), get_string('logout'),
            \theme_adaptable\toolbox::getfontawesomemarkup('sign-out'));

        for ($i = 0; $i < count($usermenuitems); $i++) {
            $additem = true;

            // If theme setting is specified in array but not enabled in theme settings do not add to menu.
            $usermenuitem = $usermenuitems[$i][0];
            if (empty($PAGE->theme->settings->$usermenuitem) && $usermenuitems[$i][0]) {
                $additem = false;
            }

            // If item requires version number and moodle is below that version to not add to menu.
            if ($usermenuitems[$i][1] && $CFG->version < $usermenuitems[$i][1]) {
                $additem = false;
            }

            if ($additem) {
                $retval .= '<a class="dropdown-item" href="' . $usermenuitems[$i][2] . '" title="' . $usermenuitems[$i][3] . '">';
                $retval .= $usermenuitems[$i][4].$usermenuitems[$i][3].'</a>';
            }
        }
        return $retval;
    }

    

    /**
     * Returns current url minus the value of $CFG->wwwroot
     *
     * @param bool $stripwwwroot
     *
     * Should be replaced with inbuilt Moodle function if one can be found
     */
    public function get_current_page_url($stripwwwroot = false) {
        global $CFG;
        $pageurl = 'http';

        if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
            $pageurl .= "s";
        }

        $pageurl .= "://";

        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageurl .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageurl .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }

        if ($stripwwwroot) {
            $pageurl = str_replace($CFG->wwwroot, '', $pageurl);
        }
        return $pageurl;
    }

    /**
     * Returns the user menu
     *
     * @param string $user = null
     * @param string $withlinks = null
     * @return the user menu
     */
    public function user_menu($user = null, $withlinks = null) {
        global $CFG;
        $usermenu = new custom_menu('', current_language());
        return $this->render_user_menu($usermenu);
    }

    /**
     * Prints a nice side block with an optional header.
     *
     * The content is described
     * by a {@link core_renderer::block_contents} object.
     *
     * <div id="inst{$instanceid}" class="block_{$blockname} block">
     *      <div class="header"></div>
     *      <div class="content">
     *          ...CONTENT...
     *          <div class="footer">
     *          </div>
     *      </div>
     *      <div class="annotation">
     *      </div>
     * </div>
     *
     * @param block_contents $bc HTML for the content
     * @param string $region the region the block is appearing in.
     * @return string the HTML to be output.
     */
    public function block(block_contents $bc, $region) {
        $bc = clone($bc); // Avoid messing up the object passed in.
        if (empty($bc->blockinstanceid) || !strip_tags($bc->title)) {
            $bc->collapsible = block_contents::NOT_HIDEABLE;
        }
        if (!empty($bc->blockinstanceid)) {
            $bc->attributes['data-instanceid'] = $bc->blockinstanceid;
        }
        $skiptitle = strip_tags($bc->title);
        if ($bc->blockinstanceid && !empty($skiptitle)) {
            $bc->attributes['aria-labelledby'] = 'instance-'.$bc->blockinstanceid.'-header';
        } else if (!empty($bc->arialabel)) {
            $bc->attributes['aria-label'] = $bc->arialabel;
        }
        if ($bc->dockable) {
            $bc->attributes['data-dockable'] = 1;
        }
        if ($bc->collapsible == block_contents::HIDDEN) {
            $bc->add_class('hidden');
        }
        if (!empty($bc->controls)) {
            $bc->add_class('block_with_controls');
        }
        $bc->add_class('mb-3');

        if (empty($skiptitle)) {
            $output = '';
            $skipdest = '';
        } else {
            $output = html_writer::link('#sb-'.$bc->skipid, get_string('skipa', 'access', $skiptitle),
                      array('class' => 'skip skip-block', 'id' => 'fsb-' . $bc->skipid));
            $skipdest = html_writer::span('', 'skip-block-to',
                      array('id' => 'sb-' . $bc->skipid));
        }

        $output .= html_writer::start_tag('div', $bc->attributes);

        $output .= $this->block_header($bc);
        $output .= $this->block_content($bc);

        $output .= html_writer::end_tag('div');

        $output .= $this->block_annotation($bc);

        $output .= $skipdest;

        $this->init_block_hider_js($bc);
        return $output;
    }

    /**
     * Produces a header for a block
     *
     * @param block_contents $bc
     * @return string
     */
    protected function block_header(block_contents $bc) {

        $title = '';
        if ($bc->title) {
            $attributes = array();
            if ($bc->blockinstanceid) {
                $attributes['id'] = 'instance-'.$bc->blockinstanceid.'-header';
            }
            $title = html_writer::tag('h2', $bc->title, $attributes);
        }

        $blockid = null;
        if (isset($bc->attributes['id'])) {
            $blockid = $bc->attributes['id'];
        }
        $controlshtml = $this->block_controls($bc->controls, $blockid);

        $output = '';
        if ($title || $controlshtml) {
            $output .= html_writer::tag('div', html_writer::tag('div',
                       html_writer::tag('div', '', array('class' => 'block_action')) .
                       $title . $controlshtml, array('class' => 'title')), array('class' => 'header'));
        }
        return $output;
    }

    /**
     * Produces the content area for a block
     *
     * @param block_contents $bc
     * @return string
     */
    protected function block_content(block_contents $bc) {
        $output = html_writer::start_tag('div', array('class' => 'content'));
        if (!$bc->title && !$this->block_controls($bc->controls)) {
            $output .= html_writer::tag('div', '', array('class' => 'block_action notitle'));
        }
        $output .= $bc->content;
        $output .= $this->block_footer($bc);
        $output .= html_writer::end_tag('div');

        return $output;
    }

    /**
     * Produces the footer for a block
     *
     * @param block_contents $bc
     * @return string
     */
    protected function block_footer(block_contents $bc) {
        $output = '';
        if ($bc->footer) {
            $output .= html_writer::tag('div', $bc->footer, array('class' => 'footer'));
        }
        return $output;
    }

    /**
     * Produces the annotation for a block
     *
     * @param block_contents $bc
     * @return string
     */
    protected function block_annotation(block_contents $bc) {
        $output = '';
        if ($bc->annotation) {
            $output .= html_writer::tag('div', $bc->annotation, array('class' => 'blockannotation'));
        }
        return $output;
    }

    /**
     * Calls the JS require function to hide a block.
     *
     * @param block_contents $bc A block_contents object
     */
    protected function init_block_hider_js(block_contents $bc) {
        if (!empty($bc->attributes['id']) and $bc->collapsible != block_contents::NOT_HIDEABLE) {
            $config = new stdClass;
            $config->id = $bc->attributes['id'];
            $config->title = strip_tags($bc->title);
            $config->preference = 'block' . $bc->blockinstanceid . 'hidden';
            $config->tooltipVisible = get_string('hideblocka', 'access', $config->title);
            $config->tooltipHidden = get_string('showblocka', 'access', $config->title);

            $this->page->requires->js_init_call('M.util.init_block_hider', array($config));
            user_preference_allow_ajax_update($config->preference, PARAM_BOOL);
        }
    }

    /**
     * Renders preferences groups.
     *
     * @param  preferences_groups $renderable The renderable
     * @return string The output.
     */
    public function render_preferences_groups(preferences_groups $renderable) {
        return $this->render_from_template('core/preferences_groups', $renderable);
    }

    /**
     * Returns list of alert messages for the user
     *
     * @return string
     */
    public function get_alert_messages() {
        global $PAGE, $CFG, $COURSE;
        $alerts = '';

        $alertcount = $PAGE->theme->settings->alertcount;

        if (core\session\manager::is_loggedinas()) {
            $alertindex = $alertcount + 1;
            $alertkey = "undismissable";
            $logininfo = $this->login_info();
            $logininfo = str_replace('<div class="logininfo">', '', $logininfo);
            $logininfo = str_replace('</div>', '', $logininfo);
            $alerts = $this->get_alert_message($logininfo, 'warning', $alertindex, $alertkey) . $alerts;
        }

        if (empty($PAGE->theme->settings->enablealerts)) {
            return $alerts;
        }

        for ($i = 1; $i <= $alertcount; $i++) {
            $enablealert = 'enablealert' . $i;
            $alerttext = 'alerttext' . $i;
            $alertsession = 'alert' . $i;

            if (isset($PAGE->theme->settings->$enablealert)) {
                $enablealert = $PAGE->theme->settings->$enablealert;
            } else {
                $enablealert = false;
            }

            if (isset($PAGE->theme->settings->$alerttext)) {
                $alerttext = $PAGE->theme->settings->$alerttext;
            } else {
                $alerttext = '';
            }

            if ($enablealert && !empty($alerttext)) {
                $alertprofilefield = 'alertprofilefield' . $i;
                $profilevals = array('', '');

                if (!empty($PAGE->theme->settings->$alertprofilefield)) {
                    $profilevals = explode('=', $PAGE->theme->settings->$alertprofilefield);
                }

                if (!empty($PAGE->theme->settings->enablealertstriptags)) {
                    $alerttext = strip_tags($alerttext);
                }

                $alerttype = 'alerttype' . $i;
                $alertaccess = 'alertaccess' . $i;
                $alertkey = 'alertkey' . $i;

                $alerttype = $PAGE->theme->settings->$alerttype;
                $alertaccess = $PAGE->theme->settings->$alertaccess;
                $alertkey = $PAGE->theme->settings->$alertkey;

                if ($this->get_alert_access($alertaccess, $profilevals[0], $profilevals[1], $alertsession)) {
                    $alerts .= $this->get_alert_message($alerttext, $alerttype, $i, $alertkey);
                }
            }
        }

        if (is_role_switched($COURSE->id)) {
            $alertindex = $alertcount + 1;
            $alertkey = "undismissable";

            $returnurl = $this->get_current_page_url(true);
            $url = $CFG->wwwroot.'/course/switchrole.php?id='.$COURSE->id.'&sesskey='.sesskey().
                '&switchrole=0&returnurl='.$returnurl;

            $message = get_string('actingasrole', 'theme_adaptable') . '.  ';
            $message .= '<a href="' . $url . '">' . get_string('switchrolereturn') . '</a>';
            $alerts = $this->get_alert_message($message, 'warning', $alertindex, $alertkey) . $alerts;
        }

        return $alerts;
    }

    /**
     * Returns formatted alert message
     *
     * @param string $text message text
     * @param string $type alert type
     * @param int $alertindex
     * @param int $alertkey
     */
    public function get_alert_message($text, $type, $alertindex, $alertkey) {
        if ($alertkey == '' || theme_adaptable_get_alertkey($alertindex) == $alertkey) {
            return '';
        }

        global $PAGE;

        $retval = '<div class="customalert alert alert-dismissable adaptable-alert-' . $type . ' fade in">';
        $retval .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close" data-alertkey="' . $alertkey.
            '" data-alertindex="' . $alertindex . '">';

        if ($alertkey != 'undismissable') {
            $retval .= '<span aria-hidden="true">&times;</span>';
        }

        $retval .= '</button>';
        $retval .= '<i class="fa fa-' . $this->alert_icon($type) . ' fa-lg"></i>&nbsp;';
        $retval .= $text;
        $retval .= '</div>';
        return $retval;
    }

    /**
     * Displays notices to alert teachers of problems with course such as being hidden
     */
    public function get_course_alerts() {
        global $PAGE, $CFG, $COURSE;
        $retval = '';
        $warninghidden = $PAGE->theme->settings->alerthiddencourse;

        if ($warninghidden != 'disabled') {
            if ($this->page->course->visible == 0) {
                $alerttext = get_string('alerthiddencoursetext-1', 'theme_adaptable')
                . '<a href="' . $CFG->wwwroot . '/course/edit.php?id=' . $COURSE->id . '">'
                        . get_string('alerthiddencoursetext-2', 'theme_adaptable') . '</a>';

                        $alerttype = $warninghidden;
                        $alertindex = 'hiddencoursealert-' . $COURSE->id;
                        $alertkey = $alertindex; // These keys are never reset so can use fixed value.

                        $retval = $this->get_alert_message($alerttext, $alerttype, $alertindex, $alertkey);
            }
        }

        return $retval;
    }

    /**
     * Checks the users access to alerts
     * @param string $access the kind of access rule applied
     * @param string $profilefield the custom profile filed to check
     * @param string $profilevalue the expected value to be found in users profile
     * @param string $alertsession a token to be used to store access in session
     * @return boolean
     */
    public function get_alert_access($access, $profilefield, $profilevalue, $alertsession) {
        $retval = false;
        switch ($access) {
            case "global":
                $retval = true;
                break;
            case "user":
                if (isloggedin()) {
                    $retval = true;
                }
                break;
            case "admin":
                if (is_siteadmin()) {
                    $retval = true;
                }
                break;
            case "profile":
                /* Check if user is logged in and then check menu access for profile field. */
                if ( (isloggedin()) && ($this->check_menu_access($profilefield, $profilevalue, $alertsession)) ) {
                    $retval = true;
                }
                break;
        }
        return $retval;
    }

    /**
     * Returns FA icon depending on the type of alert selected
     *
     * @param string $alertclassglobal     *
     * @return string
     */
    public function alert_icon($alertclassglobal) {
        global $PAGE;
        switch ($alertclassglobal) {
            case "success":
                $alerticonglobal = $PAGE->theme->settings->alerticonsuccess;
                break;
            case "info":
                $alerticonglobal = $PAGE->theme->settings->alerticoninfo;
                break;
            case "warning":
                $alerticonglobal = $PAGE->theme->settings->alerticonwarning;
                break;
        }
        return $alerticonglobal;
    }

    /**
     * Returns html to render Development version alert message in the header
     *
     * @return string
     */
    public function get_dev_alert() {
        global $CFG;
        $output = '';

        // Development version.
        if (get_config('theme_adaptable', 'version') < '2019051300') {
            $output .= '<div id="beta"><h3>';
            $output .= get_string('beta', 'theme_adaptable');
            $output .= '</h3></div>';
        }

        // Deprecated moodle version (< 3.6).
        if ($CFG->version < 2018120300) {
            $output .= '<div id="beta"><center><h3>';
            $output .= get_string('deprecated', 'theme_adaptable');
            $output .= '</h3></center></div>';
        }

        return $output;
    }

    /**
     * Returns Google Analytics code if analytics are enabled
     *
     * @return string
     */
    public function get_analytics() {
        global $PAGE;
        $analytics = '';
        $analyticscount = $PAGE->theme->settings->enableanalytics;
        $anonymize = true;

        // Anonymize IP.
        if (($PAGE->theme->settings->anonymizega = 1) || (empty($PAGE->theme->settings->anonymizega))) {
            $anonymize = true;
        } else {
            $anonymize = false;
        }

        // Load settings.
        if (isset($PAGE->theme->settings->enableanalytics)) {
            for ($i = 1; $i <= $analyticscount; $i++) {
                $analyticstext = 'analyticstext' . $i;
                $analyticsprofilefield = 'analyticsprofilefield' . $i;
                $analyticssession = 'analytics' . $i;
                $access = true;

                if (!empty($PAGE->theme->settings->$analyticsprofilefield)) {
                    $profilevals = explode('=', $PAGE->theme->settings->$analyticsprofilefield);
                    $profilefield = $profilevals[0];
                    $profilevalue = $profilevals[1];
                    if (!$this->check_menu_access($profilefield, $profilevalue, $analyticssession)) {
                        $access = false;
                    }
                }

                if (!empty($PAGE->theme->settings->$analyticstext) && $access) {
                    // The closing tag of PHP heredoc doesn't like being indented so do not meddle with indentation of 'EOT;' below!
                    $analytics .= <<<EOT

                    <script type="text/javascript">
                        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

                        ga('create', '$analyticstext', 'auto');
                        ga('send', 'pageview');
                        ga('set', 'anonymizeIp', $anonymize);
                    </script>
EOT;
                }
            }
        }
        return $analytics;
    }

    /**
     * Returns Piwik code if enabled
     *
     * @copyright  2016 COMETE-UPO (Universit\E9 Paris Ouest)
     *
     * @return string
     */
    public function get_piwik() {
        global $CFG, $DB, $PAGE, $COURSE, $SITE;

        $enabled = $PAGE->theme->settings->piwikenabled;
        $imagetrack = $PAGE->theme->settings->piwikimagetrack;
        $siteurl = $PAGE->theme->settings->piwiksiteurl;
        $siteid = $PAGE->theme->settings->piwiksiteid;
        $trackadmin = $PAGE->theme->settings->piwiktrackadmin;

        $enabled = $PAGE->theme->settings->piwikenabled;
        $imagetrack = $PAGE->theme->settings->piwikimagetrack;
        $siteurl = $PAGE->theme->settings->piwiksiteurl;
        $siteid = $PAGE->theme->settings->piwiksiteid;
        $trackadmin = $PAGE->theme->settings->piwiktrackadmin;

        $analytics = '';
        if ($enabled && !empty($siteurl) && !empty($siteid) && (!is_siteadmin() || $trackadmin)) {
            if ($imagetrack) {
                $addition = '<noscript><p>
                            <img src="//'.$siteurl.'/piwik.php?idsite='.$siteid.' style="border:0;" alt="" /></p></noscript>';
            } else {
                $addition = '';
            }
            // Cleanurl.
            $pageinfo = get_context_info_array($PAGE->context->id);
            $trackurl = '';
            // Adds course category name.
            if (isset($pageinfo[1]->category)) {
                if ($category = $DB->get_record('course_categories', array('id' => $pageinfo[1]->category))) {
                    $cats = explode("/", $category->path);
                    foreach (array_filter($cats) as $cat) {
                        if ($categorydepth = $DB->get_record("course_categories", array("id" => $cat))) {
                            $trackurl .= $categorydepth->name.'/';
                        }
                    }
                }
            }
            // Adds course full name.
            if (isset($pageinfo[1]->fullname)) {
                if (isset($pageinfo[2]->name)) {
                    $trackurl .= $pageinfo[1]->fullname.'/';
                } else if ($PAGE->user_is_editing()) {
                    $trackurl .= $pageinfo[1]->fullname.'/'.get_string('edit', 'local_analytics');
                } else {
                    $trackurl .= $pageinfo[1]->fullname.'/'.get_string('view', 'local_analytics');
                }
            }
            // Adds activity name.
            if (isset($pageinfo[2]->name)) {
                $trackurl .= $pageinfo[2]->modname.'/'.$pageinfo[2]->name;
            }
            $trackurl = '"'.str_replace('"', '\"', $trackurl).'"';
            // Here we go.
            $analytics .= '<!-- Start Piwik Code -->'."\n".
                '<script type="text/javascript">'."\n".
                '   var _paq = _paq || [];'."\n".
                '   _paq.push(["setDocumentTitle", '.$trackurl.']);'."\n".
                '   _paq.push(["trackPageView"]);'."\n".
                '   _paq.push(["enableLinkTracking"]);'."\n".
                '   (function() {'."\n".
                '     var u="//'.$siteurl.'/";'."\n".
                '     _paq.push(["setTrackerUrl", u+"piwik.php"]);'."\n".
                '     _paq.push(["setSiteId", '.$siteid.']);'."\n".
                '     var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0];'."\n".
                '   g.type="text/javascript"; g.async=true; g.defer=true; g.src=u+"piwik.js";s.parentNode.insertBefore(g,s);'."\n".
                '   })();'."\n".
                '</script>'.$addition."\n".
                '<!-- End Piwik Code -->'."\n".
                '';
        }
        return $analytics;
    }

    /**
     * Returns all tracking methods (Analytics and Piwik)
     *
     * @return string
     */
    public function get_all_tracking_methods() {
        $analytics = '';
        $analytics .= $this->get_analytics();
        $analytics .= $this->get_piwik();
        return $analytics;
    }

    /**
     * Returns HTML to display a "Turn editing on/off" button in a form.
     *
     * @param moodle_url $url The URL + params to send through when clicking the button
     * @return string HTML the button
     * Written by G J Barnard
     */
    public function edit_button(moodle_url $url) {
        $url->param('sesskey', sesskey());
        if ($this->page->user_is_editing()) {
            $url->param('edit', 'off');
            $btn = 'btn-danger';
            $title = get_string('turneditingoff');
            $icon = 'fa-power-off';
        } else {
            $url->param('edit', 'on');
            $btn = 'btn-success';
            $title = get_string('turneditingon');
            $icon = 'fa-edit';
        }
        $editingtext = get_config('theme_adaptable', 'displayeditingbuttontext');
        $buttontitle = '';
        if ($editingtext) {
            $buttontitle = $title;
        } else {
            $icon .= ' only';
        }
        return html_writer::tag('a', html_writer::tag('i', '', array('class' => $icon.' fa fa-fw')).
            $buttontitle, array('href' => $url, 'class' => 'btn '.$btn, 'title' => $title));
    }

    /**
     * Returns the upper user menu
     *
     * @param custom_menu $menu
     * @return string
     */
    protected function render_user_menu(custom_menu $menu) {
        global $CFG, $DB, $PAGE, $OUTPUT;

        // Add the custom usermenus.
        $content = html_writer::start_tag('ul', array('class' => 'navbar-nav mr-auto'));
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1, 'usermenu');
        }

        return $content.html_writer::end_tag('ul');
    }

    /**
     * Returns formats messages in the header with user profile images
     *
     * @return array
     */
    protected function process_user_messages() {
        $messagelist = array();
        foreach ($usermessages as $message) {
            $cleanmsg = new stdClass();
            $cleanmsg->from = fullname($message);
            $cleanmsg->msguserid = $message->id;

            $userpicture = new user_picture($message);
            $userpicture->link = false;
            $picture = $this->render($userpicture);

            $cleanmsg->text = $picture . ' ' . $cleanmsg->text;

            $messagelist[] = $cleanmsg;
        }

        return $messagelist;
    }

    /**
     * Process user messages
     *
     * @param array $message
     * @return array
     */
    protected function process_message($message) {
        global $DB, $USER;

        $messagecontent = new stdClass();
        if ($message->notification || $message->useridfrom < 1) {
            $messagecontent->text = $message->smallmessage;
            $messagecontent->type = 'notification';

            if (empty($message->contexturl)) {
                $messagecontent->url = new moodle_url('/message/index.php',
                        array('user1' => $USER->id, 'viewing' => 'recentnotifications'));
            } else {
                $messagecontent->url = new moodle_url($message->contexturl);
            }

        } else {
            $messagecontent->type = 'message';
            if ($message->fullmessageformat == FORMAT_HTML) {
                $message->smallmessage = html_to_text($message->smallmessage);
            }
            if (strlen($message->smallmessage) > 18) {
                $messagecontent->text = core_text::substr($message->smallmessage, 0, 15) . '...';
            } else {
                $messagecontent->text = $message->smallmessage;
            }
            $messagecontent->from = $DB->get_record('user', array('id' => $message->useridfrom));
            $messagecontent->url = new moodle_url('/message/index.php',
                    array('user1' => $USER->id, 'user2' => $message->useridfrom));
        }
        $messagecontent->date = userdate($message->timecreated, get_string('strftimetime', 'langconfig'));
        $messagecontent->unread = empty($message->timeread);
        return $messagecontent;
    }

    /**
     * This renders a notification message.
     * Uses bootstrap compatible html.
     *
     * @param string $message
     * @param string $classes for css
     */
    public function notification($message, $classes = 'notifyproblem') {
        $message = clean_text($message);
        $type = '';

        if ($classes == 'notifyproblem') {
            $type = 'alert alert-error';
        }
        if ($classes == 'notifysuccess') {
            $type = 'alert alert-success';
        }
        if ($classes == 'notifymessage') {
            $type = 'alert alert-info';
        }
        if ($classes == 'redirectmessage') {
            $type = 'alert alert-block alert-info';
        }
        return '<div class="' . $type . '">' . $message . '</div>';
    }

    /**
     * Returns html to render socialicons
     *
     * @return string
     */
    public function socialicons() {
        global $CFG, $PAGE;

        if (!isset($PAGE->theme->settings->socialiconlist)) {
            return '';
        }

        $target = '_blank';
        if (isset($PAGE->theme->settings->socialtarget)) {
            $target = $PAGE->theme->settings->socialtarget;
        }

        $retval = '<div class="socialbox">';

        $socialiconlist = $PAGE->theme->settings->socialiconlist;
        $lines = explode("\n", $socialiconlist);

        foreach ($lines as $line) {
            if (strstr($line, '|')) {
                $fields = explode('|', $line);
                $val = '<a';
                $val .= ' target="' . $target;
                $val .= '" title="' . $fields[1];
                $val .= '" href="' . $fields[0] . '">';
                $val .= '<i class="fa ' . $fields[2] . '"></i>';
                $val .= '</a>';
                $retval .= $val;
            }
        }

        $retval .= '</div>';
        return $retval;
    }

    /**
     * Returns html to render news ticker
     *
     * @return string
     */
    public function get_news_ticker() {
        global $PAGE, $OUTPUT;
        $retval = '';

        if (!isset($PAGE->theme->settings->enabletickermy)) {
            $PAGE->theme->settings->enabletickermy = 0;
        }

        // Display ticker if possible.
        if ((!empty($PAGE->theme->settings->enableticker) &&
                $PAGE->theme->settings->enableticker &&
                $PAGE->bodyid == "page-site-index") ||
                ($PAGE->theme->settings->enabletickermy && $PAGE->bodyid == "page-my-index")) {
                    $msg = '';
                    $tickercount = $PAGE->theme->settings->newstickercount;

            for ($i = 1; $i <= $tickercount; $i++) {
                $textfield = 'tickertext' . $i;
                $profilefield = 'tickertext' . $i . 'profilefield';

                format_text($textfield, FORMAT_HTML);

                $access = true;

                if (!empty($PAGE->theme->settings->$profilefield)) {
                    $profilevals = explode('=', $PAGE->theme->settings->$profilefield);
                    if (!$this->check_menu_access($profilevals[0], $profilevals[1], $textfield)) {
                        $access = false;
                    }
                }

                if ($access) {
                    $msg .= format_text($PAGE->theme->settings->$textfield, FORMAT_HTML, array('trusted' => true));
                }
            }

            $msg = preg_replace('#\<[\/]{0,1}(li|ul|div|pre|blockquote)\>#', '', $msg);
            if ($msg == '') {
                $msg = '<p>' . get_string('tickerdefault', 'theme_adaptable') . '</p>';
            }

            $retval .= '<div id="ticker-wrap" class="clearfix container ' . $PAGE->theme->settings->responsiveticker . '">';
            $retval .= '<div class="pull-left" id="ticker-announce">';
            $retval .= get_string('ticker', 'theme_adaptable');
            $retval .= '</div>';
            $retval .= '<ul id="ticker">';
            $retval .= $msg;
            $retval .= '</ul>';
            $retval .= '</div>';
        }

        return $retval;
    }


    /**
     * Renders block regions on front page (or any other page
     * if specifying a different value for $settingsname). Used for various block region rendering.
     *
     * @param   string $settingsname  Setting name to retrieve from theme settings containing actual layout (e.g. 4-4-4-4)
     * @param   string $classnamebeginswith  Used when building the blockname to retrieve for display
     * @param   string $customrowsetting  If $settingsname value set to 'customrowsetting', then set this to
     *                 the layout required to display a one row layout.
     *                 When using this, ensure the appropriate number of block regions are defined in config.php.
     *                 E.g. if $classnamebeginswith = 'my-block' and $customrowsetting = '4-4-0-0', 2 regions called
     *                 'my-block-a' and 'my-block-a' are expected to exist.
     * @return  string HTML output
     */
    public function get_block_regions($settingsname = 'blocklayoutlayoutrow', $classnamebeginswith = 'frnt-market-'
            , $customrowsetting = null) {
        global $PAGE, $OUTPUT, $USER, $COURSE;
        $fields = array();
        $retval = '';
        $blockcount = 0;
        $style = '';
        $adminediting = false;

        // Check if user has capability to edit block on homepage.  This is used as part of checking if
        // blocks should display the dotted borders and labels for editing. (Issue #809).
        $context = context_course::instance($COURSE->id);

        // Check if front page and if has capability to edit blocks.  The $pageallowed variable will store
        // the correct state of whether user can edit that page.
        $caneditblock = has_capability('moodle/block:edit', $context);
        if ( ($PAGE->pagelayout == "frontpage") && ($caneditblock !== true) ) {
            $pageallowed = false;
        } else {
            $pageallowed = true;
        }

        if ( (isset($USER->editing) && $USER->editing == 1) && ($pageallowed == true) ) {
            $style = '" style="display: block; background: #EEEEEE; min-height: 50px; border: 2px dashed #BFBDBD; margin-top: 5px';
            $adminediting = true;
        }

        if ($settingsname == 'customrowsetting') {
            $fields[] = $customrowsetting;
        } else {
            for ($i = 1; $i <= 8; $i++) {
                $marketrow = $settingsname . $i;

                // Need to check if the setting exists as this function is now
                // called for variable row numbers in block regions (e.g. course page
                // which is a single row of block regions).

                if (isset($PAGE->theme->settings->$marketrow)) {
                    $marketrow = $PAGE->theme->settings->$marketrow;
                } else {
                    $marketrow = '0-0-0-0';
                }

                if ($marketrow != '0-0-0-0') {
                    $fields[] = $marketrow;
                }
            }
        }

        foreach ($fields as $field) {
            $vals = explode('-', $field);
            foreach ($vals as $val) {
                if ($val > 0) {
                    $retval .= '<div class="my-1 col-md-' . $val . $style . '">';

                    // Moodle does not seem to like numbers in region names so using letter instead.
                    $blockcount ++;
                    $block = $classnamebeginswith. chr(96 + $blockcount);

                    if ($adminediting) {
                        $retval .= '<span style="padding-left: 10px;"> ' . get_string('region-' . $block, 'theme_adaptable') .
                        '' . '</span>';
                    }

                    $retval .= $OUTPUT->blocks($block, 'block-region-front');
                    $retval .= '</div>';
                }
            }
        }
        return $retval;
    }

    /**
     * Renders block regions for potentially hidden blocks.  For example, 4-4-4-4 to 6-6-0-0
     * would mean the last two blocks get inadvertently hidden. This function can recover and
     * display those blocks.  An override option also available to display blocks for the region, regardless.
     *
     * @param array  $blocksarray Settings names containing the actual layout(s) (i.e. 4-4-4-4)
     * @param array  $classes Used when building the blockname to retrieve for display
     * @param bool   $displayall An override setting to simply display all blocks from the region
     * @return string HTML output
     */
    public function get_missing_block_regions($blocksarray, $classes = array(), $displayall = false) {
        global $PAGE, $OUTPUT, $USER;
        $retval = '';
        $style = '';
        $adminediting = false;

        if (isset($USER->editing) && $USER->editing == 1) {
            $adminediting = true;
        }

        if (!empty($blocksarray)) {

            $classes = (array)$classes;
            $missingblocks = '';

            foreach ($blocksarray as $block) {

                // Do this for up to 8 rows (allows for expansion.  Be careful
                // of losing blocks if this value changes from a high to low number!).
                for ($i = 1; $i <= 8; $i++) {

                    // For each block region in a row, analyse the current layout (e.g. 6-6-0-0, 3-3-3-3).  Check if less than
                    // 4 blocks (meaning a change in settings from say 4-4-4-4 to 6-6.  Meaning missing blocks,
                    // i.e. 6-6-0-0 means the two end ones may have content that is inadvertantly lost.
                    $rowsetting = $block['settingsname'] . $i;

                    if (isset($PAGE->theme->settings->$rowsetting)) {
                        $rowvalue = $PAGE->theme->settings->$rowsetting;

                        $spannumbers = explode('-', $rowvalue);
                        $y = 0;
                        foreach ($spannumbers as $spannumber) {
                            $y++;

                            // Here's the crucial bit.  Check if span number is 0,
                            // or $displayall is true (override) and if so, print it out.
                            if ($spannumber == 0 || $displayall) {

                                $blockclass = $block['classnamebeginswith'] . chr(96 + $y);
                                $missingblock = $OUTPUT->blocks($blockclass, 'block');

                                // Check if the block actually has content to display before displaying.
                                if (strip_tags($missingblock)) {
                                    if ($adminediting) {
                                        $missingblocks .= '<em>ORPHANED BLOCK - Originally displays in: <strong>' .
                                                get_string('region-' . $blockclass, 'theme_adaptable') .'</strong></em>';

                                    }
                                    $missingblocks .= $missingblock;
                                }

                            }
                        } // End foreach.
                    }
                }
            }

            if (!empty($missingblocks)) {
                $retval .= '<aside class="' . join(' ', $classes) . '">';
                $retval .= $missingblocks;
                $retval .= '</aside>';
            }
        }

        return $retval;
    }

    /**
     * Renders marketing blocks on front page
     *
     * @param string $layoutrow
     * @param string $settingname
     */
    public function get_marketing_blocks($layoutrow = 'marketlayoutrow', $settingname = 'market') {
        global $PAGE, $OUTPUT;
        $fields = array();
        $blockcount = 0;
        $style = '';

        $extramarketclass = $PAGE->theme->settings->frontpagemarketoption;

        $retval = '<div id="marketblocks" class="container '. $extramarketclass .'">';

        for ($i = 1; $i <= 5; $i++) {
            $marketrow = $layoutrow . $i;
            $marketrow = $PAGE->theme->settings->$marketrow;
            if ($marketrow != '0-0-0-0') {
                $fields[] = $marketrow;
            }
        }

        foreach ($fields as $field) {
            $retval .= '<div class="row marketrow">';
            $vals = explode('-', $field);
            foreach ($vals as $val) {
                if ($val > 0) {
                    $retval .= '<div class="my-1 col-md-' . $val . ' ' . $extramarketclass . '">';
                    $blockcount ++;
                    $fieldname = $settingname . $blockcount;
                    if (isset($PAGE->theme->settings->$fieldname)) {
                        // Add HTML format.
                        $retval .= $OUTPUT->get_setting($fieldname, 'format_html');
                    }
                    $retval .= '</div>';
                }
            }
            $retval .= '</div>';
        }
        $retval .= '</div>';
        if ($blockcount == 0 ) {
            $retval = '';
        }
        return $retval;
    }

    /**
     * Returns footer visibility setting
     *
     */
    public function get_footer_visibility() {
        global $PAGE, $COURSE;
        $value = $PAGE->theme->settings->footerblocksplacement;

        if ($value == 1) {
            return true;
        }

        if ($value == 2 && $COURSE->id != 1) {
            return false;
        }

        if ($value == 3) {
            return false;
        }
        return true;
    }

    /**
     * Renders footer blocks
     *
     * @param string $layoutrow
     */
    public function get_footer_blocks($layoutrow = 'footerlayoutrow') {
        global $PAGE, $OUTPUT;
        $fields = array();
        $blockcount = 0;
        $style = '';

        if (!$this->get_footer_visibility()) {
            return '';
        }

        $output = '<div id="course-footer">' . $OUTPUT->course_footer() . '</div>
                <div class="container blockplace1">';

        for ($i = 1; $i <= 3; $i++) {
            $footerrow = $layoutrow . $i;
            $footerrow = $PAGE->theme->settings->$footerrow;
            if ($footerrow != '0-0-0-0') {
                $fields[] = $footerrow;
            }
        }

        foreach ($fields as $field) {
            $output .= '<div class="row">';
            $vals = explode('-', $field);
            foreach ($vals as $val) {
                if ($val > 0) {
                    $blockcount ++;
                    $footerheader = 'footer' . $blockcount . 'header';
                    $footercontent = 'footer' . $blockcount . 'content';
                    if (!empty($PAGE->theme->settings->$footercontent)) {
                        $output .= '<div class="left-col col-' . $val . '">';
                        if (!empty($PAGE->theme->settings->$footerheader)) {
                            $output .= '<h3>';
                            $output .= $OUTPUT->get_setting($footerheader, 'format_text');
                            $output .= '</h3>';
                        }
                        $output .= $OUTPUT->get_setting($footercontent, 'format_html');
                        $output .= '</div>';
                    }
                }
            }
            $output .= '</div>';
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * Renders frontpage slider
     *
     */
    public function get_frontpage_slider() {
        global $PAGE, $OUTPUT;
        $noslides = $PAGE->theme->settings->slidercount;
        $responsiveslider = $PAGE->theme->settings->responsiveslider;

        $retval = '';

        // Will we have any slides?
        $haveslides = false;
        for ($i = 1; $i <= $noslides; $i++) {
            $sliderimage = 'p' . $i;
            if (!empty($PAGE->theme->settings->$sliderimage)) {
                $haveslides = true;
                break;
            }
        }

        if (!$haveslides) {
            return '';
        }

        if (!empty($PAGE->theme->settings->sliderfullscreen)) {
            $retval .= '<div class="slidewrap';
        } else {
            $retval .= '<div class="container slidewrap';
        }

        if ($PAGE->theme->settings->slideroption2 == 'slider2') {
            $retval .= " slidestyle2";
        }

        $retval .= ' ' . $responsiveslider . '">
            <div id="main-slider" class="flexslider">
            <ul class="slides">';

        for ($i = 1; $i <= $noslides; $i++) {
            $sliderimage = 'p' . $i;
            $sliderurl = 'p' . $i . 'url';

            if (!empty($PAGE->theme->settings->$sliderimage)) {
                $slidercaption = 'p' . $i .'cap';
            }

            $closelink = '';
            if (!empty($PAGE->theme->settings->$sliderimage)) {
                $retval .= '<li>';

                if (!empty($PAGE->theme->settings->$sliderurl)) {
                    $retval .= '<a href="' . $PAGE->theme->settings->$sliderurl . '">';
                    $closelink = '</a>';
                }

                $retval .= '<img src="' . $PAGE->theme->setting_file_url($sliderimage, $sliderimage)
                . '" alt="' . $sliderimage . '"/>';

                if (!empty($PAGE->theme->settings->$slidercaption)) {
                    $retval .= '<div class="flex-caption">';
                    $retval .= $OUTPUT->get_setting($slidercaption, 'format_html');
                    $retval .= '</div>';
                }
                $retval .= $closelink . '</li>';
            }
        }
        $retval .= '</ul></div></div>';
        return $retval;
    }

    /**
     * Renders the breadcrumb navbar.
     *
     * @param boolean $addbutton Add the page heading button.
     * return string Markup or empty string if 'nonavbar' for tge given page layout in the config.php file is true.
     */
    public function page_navbar($addbutton = false) {
        global $PAGE;
        $retval = '';
        if (empty($PAGE->layout_options['nonavbar'])) { // Not disabled by 'nonavbar' in config.php.

            // Remove breadcrumb in a quiz page.
            if ($PAGE->pagetype != "mod-quiz-attempt") {
                if (!isset($PAGE->theme->settings->enabletickermy)) {
                    $PAGE->theme->settings->enabletickermy = 0;
                }

                // Do not show navbar on dashboard / my home if news ticker is rendering.
                if (!($PAGE->theme->settings->enabletickermy && $PAGE->bodyid == "page-my-index")) {
                    $retval = '<div class="row">';
                    if (($PAGE->theme->settings->breadcrumbdisplay != 'breadcrumb')
                        && (($PAGE->pagelayout == 'course')
                        || ($PAGE->pagelayout == 'incourse'))) {
                        global $COURSE;
                        $retval .= '<div id="page-coursetitle" class="col-12">';
                        switch ($PAGE->theme->settings->breadcrumbdisplay) {
                            case 'fullname':
                                // Full Course Name.
                                $coursetitle = $COURSE->fullname;
                            break;
                            case 'shortname':
                                // Short Course Name.
                                $coursetitle = $COURSE->shortname;
                            break;
                        }

                        $coursetitlemaxwidth = (!empty($PAGE->theme->settings->coursetitlemaxwidth)
                            ? $PAGE->theme->settings->coursetitlemaxwidth : 0);
                        // Check max width of course title and trim if appropriate.
                        if (($coursetitlemaxwidth > 0) && ($coursetitle <> '')) {
                            if (strlen($coursetitle) > $coursetitlemaxwidth) {
                                $coursetitle = core_text::substr($coursetitle, 0, $coursetitlemaxwidth) . " ...";
                            }
                        }

                        switch ($PAGE->theme->settings->breadcrumbdisplay) {
                            case 'fullname':
                            case 'shortname':
                                // Full / Short Course Name.
                                $courseurl = new moodle_url('/course/view.php', array('id' => $COURSE->id));
                                $retval .= '<div id="coursetitle" class="p-2 bd-highlight"><h1><a href ="'
                                    .$courseurl->out(true).'">'.format_string($coursetitle).'</a></h1></div>';
                            break;
                        }
                        $retval .= '</div>';
                    } else {
                        $retval .= '<div id="page-navbar" class="col-12">';
                        if ($addbutton) {
                            $retval .= '<nav class="breadcrumb-button">' . $this->page_heading_button() . '</nav>';
                        }
                        $retval .= $this->navbar();
                        $retval .= '</div>';
                    }
                    $retval .= '</div>';
                }
            }
        }

        return $retval;
    }

    /**
     * Render the navbar
     *
     * return string
     */
    public function navbar() {
        global $PAGE;

        $items = $this->page->navbar->get_items();
        $breadcrumbseparator = $PAGE->theme->settings->breadcrumbseparator;

        $breadcrumbs = "";

        if (empty($items)) {
            return '';
        }

        $i = 0;

        foreach ($items as $item) {
            $item->hideicon = true;

            // Text / Icon home.
            if ($i++ == 0) {
                $breadcrumbs .= '<li>';

                if (get_config('theme_adaptable', 'enablehome') && get_config('theme_adaptable', 'enablemyhome')) {
                    $breadcrumbs = html_writer::tag('i', '', array(
                            'title' => get_string('home', 'theme_adaptable'),
                            'class' => 'fa fa-folder-open fa-lg'
                    )
                            );
                } else if (get_config('theme_adaptable', 'breadcrumbhome') == 'icon') {
                    $breadcrumbs .= html_writer::link(new moodle_url('/'),
                            // Adds in a title for accessibility purposes.
                            html_writer::tag('i', '', array(
                                    'title' => get_string('home', 'theme_adaptable'),
                                    'class' => 'fa fa-home fa-lg')
                                    )
                            );
                    $breadcrumbs .= '</li>';
                } else {
                    $breadcrumbs .= html_writer::link(new moodle_url('/'), get_string('home', 'theme_adaptable'));
                    $breadcrumbs .= '</li>';
                }
                continue;
            }

            $breadcrumbs .= '<span class="separator"><i class="fa-'.$breadcrumbseparator.' fa"></i>
                             </span><li>'.$this->render($item).'</li>';

        } // End loop.

        $classes = $PAGE->theme->settings->responsivebreadcrumb;

        return '<nav role="navigation" aria-label="'. get_string("breadcrumb", "theme_adaptable") .'">
            <ol  class="breadcrumb ' . $classes . '">'.$breadcrumbs.'</ol>
        </nav>';
    }


    /**
     * Returns html to render footer
     *
     * @return string
     */
    public function footer() {
        global $CFG;

        $output = $this->container_end_all(true);

        $footer = $this->opencontainers->pop('header/footer');

        // Provide some performance info if required.
        $performanceinfo = '';
        if (defined('MDL_PERF') || (!empty($CFG->perfdebug) and $CFG->perfdebug > 7)) {
            $perf = get_performance_info();

            if (defined('MDL_PERFTOFOOT') || debugging() || $CFG->perfdebug > 7) {
                $performanceinfo = theme_adaptable_performance_output($perf);
            }
        }

        $footer = str_replace($this->unique_performance_info_token, $performanceinfo, $footer);
        $footer = str_replace($this->unique_end_html_token, $this->page->requires->get_end_code(), $footer);
        $this->page->set_state(moodle_page::STATE_DONE);

        return $output . $footer;
    }

    /**
     * Compares two course entries against their access time for a user to see which is first.
     *
     * @param stdClass $a A course.
     * @param stdClass $b A course.
     *
     * @return int -1 'a' is first, 1 'b' is first or 0 they are equal.
     */
    protected static function timeaccesscompare($a, $b) {
        // The timeaccess is lastaccess entry and timestart an enrol entry.
        if ((!empty($a->timeaccess)) && (!empty($b->timeaccess))) {
            // Both last access.
            if ($a->timeaccess == $b->timeaccess) {
                return 0;
            }
            return ($a->timeaccess > $b->timeaccess) ? -1 : 1;
        } else if ((!empty($a->timestart)) && (!empty($b->timestart))) {
            // Both enrol.
            if ($a->timestart == $b->timestart) {
                return 0;
            }
            return ($a->timestart > $b->timestart) ? -1 : 1;
        }

        /* Must be comparing an enrol with a last access.
           -1 is to say that 'a' comes before 'b'. */
        if (!empty($a->timestart)) {
            // If 'a' is the enrol entry.
            return -1;
        }
        // Then 'b' must be the enrol entry.
        return 1;
    }

    /**
     * Returns menu object containing main navigation.
     *
     * @return menu boject
     */
    public function navigation_menu_content() {

        global $PAGE, $COURSE, $OUTPUT, $CFG, $USER;
        $menu = new custom_menu();

        $access = true;
        $overridelist = false;
        $overridestrings = false;
        $overridetype = 'off';

        if (!empty($PAGE->theme->settings->navbardisplayicons)) {
            $navbardisplayicons = true;
        } else {
            $navbardisplayicons = false;
        }

        $usernavbar = 'excludehidden';
        if (!empty($PAGE->theme->settings->enablemysites)) {
            $mysitesvisibility = $PAGE->theme->settings->enablemysites;
        }

        $mysitesmaxlength = '30';
        if (!empty($PAGE->theme->settings->mysitesmaxlength)) {
            $mysitesmaxlength = $PAGE->theme->settings->mysitesmaxlength;
        }

        $mysitesmaxlengthhidden = $mysitesmaxlength - 3;

        if (isloggedin() && !isguestuser()) {
            if (!empty($PAGE->theme->settings->enablehome)) {
                $branchtitle = get_string('home', 'theme_adaptable');
                $branchlabel = '';
                if ($navbardisplayicons) {
                    $branchlabel .= '<i class="fa fa-home fa-lg"></i>';
                }
                $branchlabel .= ' ' . $branchtitle;

                if (!empty($PAGE->theme->settings->enablehomeredirect)) {
                    $branchurl   = new moodle_url('/?redirect=0');
                } else {
                    $branchurl   = new moodle_url('/');
                }
                $branchsort  = 9998;
                $branch = $menu->add($branchlabel, $branchurl, '', $branchsort);
            }

            if (!empty($PAGE->theme->settings->enablemyhome)) {
                $branchtitle = get_string('myhome');

                $branchlabel = '';
                if ($navbardisplayicons) {
                    $branchlabel .= '<i class="fa fa-dashboard fa-lg"></i> ';
                }
                $branchlabel .= ' ' . $branchtitle;
                $branchurl   = new moodle_url('/my/index.php');
                $branchsort  = 9999;
                $branch = $menu->add($branchlabel, $branchurl, '', $branchsort);
            }

            if (!empty($PAGE->theme->settings->enableevents)) {
                $branchtitle = get_string('events', 'theme_adaptable');
                $branchlabel = '';
                if ($navbardisplayicons) {
                    $branchlabel .= '<i class="fa fa-calendar fa-lg"></i>';
                }
                $branchlabel .= ' ' . $branchtitle;

                $branchurl   = new moodle_url('/calendar/view.php');
                $branchsort  = 10000;
                $branch = $menu->add($branchlabel, $branchurl, '', $branchsort);
            }

            $overridetype = null;
            $overridelist = null;

            if (!empty($PAGE->theme->settings->mysitessortoverride)){
                $overridetype = $PAGE->theme->settings->mysitessortoverride;
            }

            if (!empty($PAGE->theme->settings->mysitessortoverridefield)) {
                $overridelist = $PAGE->theme->settings->mysitessortoverridefield;
            }

            if (($overridetype == 'profilefields' || $overridetype == 'profilefieldscohort') && (isset($overridelist))){
                $overridelist = $this->get_profile_field_contents($overridelist);

                if ($overridetype == 'profilefieldscohort') {
                    $overridelist = array_merge($this->get_cohort_enrollments(), $overridelist);
                }
            }

            if ($overridetype == 'strings' && isset($overridelist)) {
                $overridelist = explode(',', $overridelist);
            }

            if ($mysitesvisibility != 'disabled') {
                $showmysites = true;

                // Check custom profile field to restrict display of menu.
                if (!empty($PAGE->theme->settings->enablemysitesrestriction)) {
                    $fields = explode('=', $PAGE->theme->settings->enablemysitesrestriction);
                    $ftype = $fields[0];
                    $setvalue = $fields[1];

                    if (!$this->check_menu_access($ftype, $setvalue, 'mysitesrestriction')) {
                        $showmysites = false;
                    }

                }

                if ($showmysites) {
                    $branchtitle = get_string('mysites', 'theme_adaptable');
                    $branchlabel = '';

                    if ($navbardisplayicons) {
                        $branchlabel .= '<i class="fa fa-briefcase fa-lg"></i>';
                    }

                    $branchlabel .= ' ' . $branchtitle;

                    $branchurl   = new moodle_url('#');
                    $branchsort  = 10001;

                    $menudisplayoption = '';

                    // Check menu hover settings.
                    if (isset($PAGE->theme->settings->mysitesmenudisplay)) {
                        $menudisplayoption = $PAGE->theme->settings->mysitesmenudisplay;
                    } else {
                        $menudisplayoption = 'shortcodehover';
                    }

                    // The two variables below will control the 4 options available from the settings above for mysitesmenuhover.
                    $showshortcode = true;  // If false, then display full course name.
                    $showhover = true;

                    switch ($menudisplayoption) {
                        case 'shortcodenohover':
                            $showhover = false;
                            break;
                        case 'fullnamenohover':
                            $showshortcode = false;
                            $showhover = false;
                        case 'fullnamehover':
                            $showshortcode = false;
                            break;
                    }

                    // Calls a local method (render_mycourses) to get list of a user's current courses that they are enrolled on.
                    list($sortedcourses) = $this->render_mycourses();

                    // After finding out if there will be at least one course to display, check
                    // for the option of displaying a sub-menu arrow symbol.
                    if (!empty($PAGE->theme->settings->navbardisplaysubmenuarrow)) {
                        $branchlabel .= ' &nbsp;<i class="fa fa-caret-down"></i>';
                    }

                    // Add top level menu option here after finding out if there will be at least one course to display.  This is
                    // for the option of displaying a sub-menu arrow symbol above, if configured in the theme settings.
                    $branch = $menu->add($branchlabel, $branchurl, '', $branchsort);
                    $icon = '';

                    if ($sortedcourses) {
                        /* Add timeaccess and timestart to the courses for all override types to use in some shape or form.
                           Get the last accessed information for the user and populate. */
                        global $DB, $USER;
                        $lastaccess = $DB->get_records('user_lastaccess', array('userid' => $USER->id), '', 'courseid, timeaccess');
                        if ($lastaccess) {
                            foreach ($sortedcourses as $course) {
                                if (!empty($lastaccess[$course->id])) {
                                    $course->timeaccess = $lastaccess[$course->id]->timeaccess;
                                }
                            }
                        }
                        // Determine if we need to query the enrolment and user enrolment tables.
                        $enrolquery = false;
                        foreach ($sortedcourses as $course) {
                            if (empty($course->timeaccess)) {
                                $enrolquery = true;
                                break;
                            }
                        }
                        if ($enrolquery) {
                            // We do.
                            $params = array('userid' => $USER->id);
                            $sql = "SELECT ue.id, e.courseid, ue.timestart
                                FROM {enrol} e
                                JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)";
                            $enrolments = $DB->get_records_sql($sql, $params, 0, 0);
                            if ($enrolments) {
                                // Sort out any multiple enrolments on the same course.
                                $userenrolments = array();
                                foreach ($enrolments as $enrolment) {
                                    if (!empty($userenrolments[$enrolment->courseid])) {
                                        if ($userenrolments[$enrolment->courseid] < $enrolment->timestart) {
                                            // Replace.
                                            $userenrolments[$enrolment->courseid] = $enrolment->timestart;
                                        }
                                    } else {
                                        $userenrolments[$enrolment->courseid] = $enrolment->timestart;
                                    }
                                }
                                // We don't need to worry about timeend etc. as our course list will be valid for the user from above.
                                foreach ($sortedcourses as $course) {
                                    if (empty($course->timeaccess)) {
                                        $course->timestart = $userenrolments[$course->id];
                                    }
                                }
                            }
                        }

                        if ($overridetype == 'myoverview') {
                            $myoverviewcourses = $this->parsemyoverview($sortedcourses);

                            if (!empty($myoverviewcourses[ADAPTABLE_COURSE_STARRED])) {
                                $icon = \theme_adaptable\toolbox::getfontawesomemarkup('star-o');
                                $this->addcoursestomenu($branch, $myoverviewcourses[ADAPTABLE_COURSE_STARRED],
                                    $showshortcode, $showhover, $mysitesmaxlength, $icon);
                            }

                            if (!empty($myoverviewcourses[ADAPTABLE_COURSE_IN_PROGRESS])) {
                                $icon = \theme_adaptable\toolbox::getfontawesomemarkup('tasks');
                                $child = $branch->add($icon . rtrim(
                                    mb_strimwidth(format_string(get_string('inprogress', 'theme_adaptable')),
                                    0, $mysitesmaxlengthhidden)) . '...', $this->page->url, '', 1000);
                                $this->addcoursestomenu($child, $myoverviewcourses[ADAPTABLE_COURSE_IN_PROGRESS],
                                    $showshortcode, $showhover, $mysitesmaxlength);
                            }

                            if (!empty($myoverviewcourses[ADAPTABLE_COURSE_PAST])) {
                                $icon = \theme_adaptable\toolbox::getfontawesomemarkup('history');
                                $child = $branch->add($icon . rtrim(
                                    mb_strimwidth(format_string(get_string('past', 'theme_adaptable')),
                                    0, $mysitesmaxlengthhidden)) . '...', $this->page->url, '', 1000);
                                $this->addcoursestomenu($child, $myoverviewcourses[ADAPTABLE_COURSE_PAST],
                                    $showshortcode, $showhover, $mysitesmaxlength);
                            }

                            if (!empty($myoverviewcourses[ADAPTABLE_COURSE_FUTURE])) {
                                $icon = \theme_adaptable\toolbox::getfontawesomemarkup('clock-o');
                                $child = $branch->add($icon . rtrim(
                                    mb_strimwidth(format_string(get_string('future', 'theme_adaptable')),
                                    0, $mysitesmaxlengthhidden)) . '...', $this->page->url, '', 1000);
                                $this->addcoursestomenu($child, $myoverviewcourses[ADAPTABLE_COURSE_FUTURE],
                                    $showshortcode, $showhover, $mysitesmaxlength);
                            }

                            if (!empty($myoverviewcourses[ADAPTABLE_COURSE_HIDDEN])) {
                                $faicon = (!empty($this->page->theme->settings->chiddenicon)) ? 
                                    $this->page->theme->settings->chiddenicon : '';
                                $hiddenicon = \theme_adaptable\toolbox::getfontawesomemarkup($faicon);
                                $child = $branch->add($hiddenicon . rtrim(
                                    mb_strimwidth(format_string(get_string('hiddenfromview', 'theme_adaptable')),
                                    0, $mysitesmaxlengthhidden)) . '...', $this->page->url, '', 1000);
                                $this->addcoursestomenu($child, $myoverviewcourses[ADAPTABLE_COURSE_HIDDEN],
                                    $showshortcode, $showhover, $mysitesmaxlength);
                            }
                        } else {
                            if ($overridetype == 'last') {
                                uasort($sortedcourses, array($this, 'timeaccesscompare'));
                            }

                            foreach ($sortedcourses as $course) {
                                if ($course->visible) {
                                    $coursename = '';
                                    $rawcoursename = ''; // Untrimmed course name.

                                    if ($showshortcode) {
                                        $coursename = mb_strimwidth(format_string($course->shortname), 0,
                                            $mysitesmaxlength, '...', 'utf-8');
                                    } else {
                                        $coursename = mb_strimwidth(format_string($course->fullname), 0,
                                            $mysitesmaxlength, '...', 'utf-8');
                                    }

                                    if ($showhover) {
                                        $alttext = $course->fullname;
                                    } else {
                                        $alttext = '';
                                    }

                                    if (!$overridelist) { // Feature not in use, add to menu as normal.
                                        $icon = $this->getcoursemenuicons($course);
                                        $branch->add($icon.$coursename,
                                            new moodle_url('/course/view.php?id='.$course->id), $alttext);
                                    } else {
                                        // We want to check against array from profile field.
                                        if ((($overridetype == 'profilefields' ||
                                            $overridetype == 'profilefieldscohort') &&
                                                in_array($course->shortname, $overridelist)) ||
                                                ($overridetype == 'strings' &&
                                                 $this->check_if_in_array_string($overridelist, $course->shortname))) {

                                            $icon = $this->getcoursemenuicons($course);
                                            $branch->add($icon.$coursename,
                                                new moodle_url('/course/view.php?id='.$course->id), $alttext, 100);
                                        } else {
                                            // If not in array add to sub menu item.
                                            if (!isset($child)) {
                                                $icon = '<i class="fa fa-history"></i> ';
                                                $child = $branch->add($icon . rtrim(
                                                    mb_strimwidth(format_string(get_string('pastcourses', 'theme_adaptable')),
                                                    0, $mysitesmaxlengthhidden)) . '...', $this->page->url, $alttext, 1000);
                                            }
                                            if ($showshortcode) {
                                                $rawcoursename = $course->shortname;
                                            } else {
                                                $rawcoursename = $course->fullname;
                                            }

                                            $icon = $this->getcoursemenuicons($course);
                                            $child->add($icon.rtrim(mb_strimwidth(format_string($rawcoursename),
                                                0, $mysitesmaxlengthhidden)) . '...',
                                                new moodle_url('/course/view.php?id='.$course->id),
                                                format_string($rawcoursename));
                                        }
                                    }
                                }
                            }

                            $faicon = (!empty($this->page->theme->settings->chiddenicon)) ? 
                                $this->page->theme->settings->chiddenicon : 'eye-slash';
                            $hiddenicon = \theme_adaptable\toolbox::getfontawesomemarkup($faicon);
                            $child = null;
                            foreach ($sortedcourses as $course) {
                                $icon = $this->getcoursemenuicons($course, $hiddenicon);
                                if (!$course->visible && $mysitesvisibility == 'includehidden') {
                                    if (empty($child)) {
                                        $child = $branch->add($hiddenicon.
                                            rtrim(mb_strimwidth(format_string(get_string('hiddencourses', 'theme_adaptable')),
                                            0, $mysitesmaxlengthhidden)) . '...', $this->page->url, '', 2000);
                                    }

                                    $child->add($icon.rtrim(mb_strimwidth(format_string($course->fullname),
                                        0, $mysitesmaxlengthhidden)) . '...',
                                        new moodle_url('/course/view.php?id='.$course->id), format_string($course->shortname));
                                }
                            }
                        }
                    } else {
                        $noenrolments = get_string('noenrolments', 'theme_adaptable');
                        $branch->add('<em>'.$noenrolments.'</em>', new moodle_url('/'), $noenrolments);
                    }
                }
            }

            if (!empty($PAGE->theme->settings->enablethiscourse)) {
                if (ISSET($COURSE->id) && $COURSE->id > 1) {
                    $branchtitle = get_string('thiscourse', 'theme_adaptable');
                    $branchlabel = '';
                    if ($navbardisplayicons) {
                        $branchlabel .= '<i class="fa fa-sitemap fa-lg"></i><span class="menutitle">';
                    }

                    $branchlabel .= $branchtitle . '</span>';

                    $data = theme_adaptable_get_course_activities();

                    // Check the option of displaying a sub-menu arrow symbol.
                    if (!empty($PAGE->theme->settings->navbardisplaysubmenuarrow)) {
                        $branchlabel .= ' &nbsp;<i class="fa fa-caret-down"></i>';
                    }

                    $branchurl = $this->page->url;
                    $branch = $menu->add($branchlabel, $branchurl, '', 10002);

                    // Course sections.
                    if ($PAGE->theme->settings->enablecoursesections) {
                        $this->create_course_sections_menu($branch);
                    }

                    // Display Participants.
                    if ($PAGE->theme->settings->displayparticipants) {
                        $branchtitle = get_string('people', 'theme_adaptable');
                        $branchlabel = '<i class="icon fa fa-users fa-lg"></i>'.$branchtitle;
                        $branchurl = new moodle_url('/user/index.php', array('id' => $PAGE->course->id));
                        $branch->add($branchlabel, $branchurl, '', 100004);
                    }

                    // Display Grades.
                    if ($PAGE->theme->settings->displaygrades) {
                        $branchtitle = get_string('grades');
                        $branchlabel = $OUTPUT->pix_icon('i/grades', '', '', array('class' => 'icon')).$branchtitle;
                        $branchurl = new moodle_url('/grade/report/index.php', array('id' => $PAGE->course->id));
                        $branch->add($branchlabel, $branchurl, '', 100005);
                    }

                    // Kaltura video gallery.
                    if (\theme_adaptable\toolbox::kalturaplugininstalled()) {
                        $branchtitle = get_string('nav_mediagallery', 'local_kalturamediagallery');
                        $branchlabel = $OUTPUT->pix_icon('media-gallery', '', 'local_kalturamediagallery').$branchtitle;
                        $branchurl = new moodle_url('/local/kalturamediagallery/index.php',
                            array('courseid' => $PAGE->course->id));
                        $branch->add($branchlabel, $branchurl, '', 100006);
                    }

                    // Display Competencies.
                    if (get_config('core_competency', 'enabled')) {
                        if ($PAGE->theme->settings->enablecompetencieslink) {
                            $branchtitle = get_string('competencies', 'competency');
                            $branchlabel = $OUTPUT->pix_icon('i/competencies', '', '', array('class' => 'icon')).$branchtitle;
                            $branchurl = new moodle_url('/admin/tool/lp/coursecompetencies.php',
                                         array('courseid' => $PAGE->course->id));
                            $branch->add($branchlabel, $branchurl, '', 100007);
                        }
                    }

                    // Display activities.
                    foreach ($data as $modname => $modfullname) {
                        if ($modname === 'resources') {
                            $icon = $OUTPUT->pix_icon('icon', '', 'mod_page', array('class' => 'icon'));
                            $branch->add($icon.$modfullname, new moodle_url('/course/resources.php',
                                    array('id' => $PAGE->course->id)));
                        } else {
                            $icon = $OUTPUT->pix_icon('icon', '', $modname, array('class' => 'icon'));
                            $branch->add($icon.$modfullname, new moodle_url('/mod/'.$modname.'/index.php',
                                    array('id' => $PAGE->course->id)));
                        }
                    }
                }
            }
        }

        if ($navbardisplayicons) {
            $helpicon = '<i class="fa fa-life-ring fa-lg"></i>';
        } else {
            $helpicon = '';
        }

        if (!empty($PAGE->theme->settings->helplinkscount)) {
            for ($helpcount = 1; $helpcount <= $PAGE->theme->settings->helplinkscount; $helpcount++) {
                $enablehelpsetting = 'enablehelp'.$helpcount;
                if (!empty($PAGE->theme->settings->$enablehelpsetting)) {
                    $access = true;
                    $helpprofilefieldsetting = 'helpprofilefield'.$helpcount;
                    if (!empty($PAGE->theme->settings->$helpprofilefieldsetting)) {
                        $fields = explode('=', $PAGE->theme->settings->$helpprofilefieldsetting);
                        $ftype = $fields[0];
                        $setvalue = $fields[1];
                        if (!$this->check_menu_access($ftype, $setvalue, 'help'.$helpcount)) {
                            $access = false;
                        }
                    }

                    if ($access && !$this->hideinforum()) {
                        $helplinktitlesetting = 'helplinktitle'.$helpcount;
                        if (empty($PAGE->theme->settings->$helplinktitlesetting)) {
                            $branchtitle = get_string('helptitle', 'theme_adaptable', array('number' => $helpcount));
                        } else {
                            $branchtitle = $PAGE->theme->settings->$helplinktitlesetting;
                        }
                        $branchlabel = $helpicon.$branchtitle;
                        $branchurl = new moodle_url($PAGE->theme->settings->$enablehelpsetting,
                            array('helptarget' => $PAGE->theme->settings->helptarget));

                        $branchsort  = 10003;
                        $branch = $menu->add($branchlabel, $branchurl, '', $branchsort);
                    }
                }
            }
        }

        return $menu;
    }

    /**
     * Get the icon markup of the icon(s) for the course that will be used in its menu item.
     *
     * @param stdClass $course Course.
     * @param string $existingicon Existing icon markup if any.
     *
     * @return string Icon markup(s).
     */
    protected function getcoursemenuicons($course, $existingicon = '') {
        global $CFG;
        $icon = $existingicon;

        if (!empty($course->timestart)) {
            $faicon = (!empty($this->page->theme->settings->cneveraccessedicon)) ? 
                $this->page->theme->settings->cneveraccessedicon : '';
            $icon .= \theme_adaptable\toolbox::getfontawesomemarkup($faicon);
        }

        if (!empty($CFG->contextlocking)) {
            $context = context_course::instance($course->id);
            if ($context->locked) {
                $faicon = (!empty($this->page->theme->settings->cfrozenicon)) ? 
                    $this->page->theme->settings->cfrozenicon : '';
                $icon .= \theme_adaptable\toolbox::getfontawesomemarkup($faicon);
            }
        }

        if (empty($icon)) {
            $faicon = (!empty($this->page->theme->settings->cdefaulticon)) ? 
                $this->page->theme->settings->cdefaulticon : '';
            $icon = \theme_adaptable\toolbox::getfontawesomemarkup($faicon);
        }

        return $icon;
    }

    /**
     * Classify the courses in the same way that the My Overview block does non the dashboard.
     *
     * @param array $sortedcourses Array of courses - must contain the fields by 'define_properties' in 'course_summary_exporter'.
     *
     * @return array array of arrays that classify the courses.
     */
     protected function parsemyoverview(&$sortedcourses) {
        global $CFG, $USER;

        $ufservice = \core_favourites\service_factory::get_service_for_user_context(\context_user::instance($USER->id));
        $starred = $ufservice->find_favourites_by_type('core_course', 'courses');
        $starredids = array();

        if ($starred) {
            $starredids = array_map(
                function($favourite) {
                    return $favourite->itemid;
                }, $starred);
        }

        $hiddenids = get_hidden_courses_on_timeline($USER);

        $myoverviewcourses = array(
            ADAPTABLE_COURSE_STARRED => array(),
            ADAPTABLE_COURSE_IN_PROGRESS => array(),
            ADAPTABLE_COURSE_PAST => array(),
            ADAPTABLE_COURSE_FUTURE => array(),
            ADAPTABLE_COURSE_HIDDEN => array()
        );

        foreach($sortedcourses as $course) {
            if (in_array($course->id, $starredids)) {
                $myoverviewcourses[ADAPTABLE_COURSE_STARRED][] = $course;
            } // Starred can also appear in the respective sub-menu.
            if (in_array($course->id, $hiddenids)) {
                $myoverviewcourses[ADAPTABLE_COURSE_HIDDEN][] = $course;
            } else {
                switch (course_classify_for_timeline($course, $USER)) {
                    case COURSE_TIMELINE_PAST:
                        $myoverviewcourses[ADAPTABLE_COURSE_PAST][] = $course;
                    break;
                    case COURSE_TIMELINE_FUTURE:
                        $myoverviewcourses[ADAPTABLE_COURSE_FUTURE][] = $course;
                    break;
                    case COURSE_TIMELINE_INPROGRESS:
                        $myoverviewcourses[ADAPTABLE_COURSE_IN_PROGRESS][] = $course;
                    break;
                }
            }
        }

        return $myoverviewcourses;
    }

    /**
     * Adds the given array of courses to the supplied menu.
     *
     * @param custom_menu_item $menu The menu to add to.
     * @param array $courses Array of courses.
     * @param bool $showshortcode Use the course shortname instead of full.
     * @param bool $showhover Put the course full name in the alternative text.
     * @param int $mysitesmaxlength Max lenghth of the course name string displayed.
     * @param string $icon Prefix an icon (HTML markup) if any.
     */
    protected function addcoursestomenu(&$menu, $courses, $showshortcode, $showhover, $mysitesmaxlength, $icon = '') {
        foreach ($courses as $course) {
            if (($course->visible) || (has_capability('moodle/course:viewhiddencourses', $this->page->context))) {
                if ($showshortcode) {
                    $coursename = mb_strimwidth(format_string($course->shortname), 0,
                        $mysitesmaxlength, '...', 'utf-8');
                } else {
                    $coursename = mb_strimwidth(format_string($course->fullname), 0,
                        $mysitesmaxlength, '...', 'utf-8');
                }

                if ($showhover) {
                    $alttext = $course->fullname;
                } else {
                    $alttext = '';
                }

                $courseicon = $this->getcoursemenuicons($course, $icon);
                $menu->add($courseicon.$coursename, new moodle_url('/course/view.php?id='.$course->id), $alttext);
            }
        }
    }

    /**
     * Adds the course sections to the 'This course' menu.
     *
     * @param custom_menu_item $menu The menu to add to.
     */
    protected function create_course_sections_menu($menu) {
        global $COURSE;

        $courseformat = course_get_format($COURSE);
        $modinfo = get_fast_modinfo($COURSE);
        $numsections = $courseformat->get_last_section_number();
        $sectionsformnenu = array();
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if ($section > $numsections) {
                // Don't link to stealth sections.
                continue;
            }
            /* Show the section if the user is permitted to access it, OR if it's not available
               but there is some available info text which explains the reason & should display. */
            $showsection = $thissection->uservisible ||
                ($thissection->visible && !$thissection->available && !empty($thissection->availableinfo));

            if (($showsection) || ($section == 0)) {
                $sectionsformnenu[$section] = array(
                    'sectionname' => $courseformat->get_section_name($section),
                    'url' => $courseformat->get_view_url($section)
                );
            }
        }

        if (!empty($sectionsformnenu)) { // Rare but possible!
            $branchtitle = get_string('sections', 'theme_adaptable');
            $branchlabel = '<i class="icon fa fa-list-ol fa-lg"></i>'.$branchtitle;
            $branch = $menu->add($branchlabel, null, '', 100003);

            foreach ($sectionsformnenu as $sectionformenu) {
                $branch->add($sectionformenu['sectionname'], $sectionformenu['url']);
            }
        }

        return $sectionsformnenu;
    }

    /**
     * Returns html to render main navigation menu
     *
     * @param string $menuid The id to use when creating menu. Used so this can be called for a nav drawer style display.
     *
     * @return string
     */
    public function navigation_menu($menuid) {

        $sessttl = 0;
        $cache = cache::make('theme_adaptable', 'userdata');

        if ($sessttl > 0 && time() <= $cache->get('usernavbarttl')) {
            return $cache->get('mysitesvisibility');
        }
        static $builtmenu = null;

        if ($builtmenu != null) {
            $menu = $builtmenu;
        } else {
            $menu = $this->navigation_menu_content();
            $builtmenu = $menu;
        }

        if ($sessttl > 0) {
            $cache->set('usernavbarttl', $sessttl);
            $cache->set('usernavbar', $this->render_custom_menu($menu, '', '', $menuid));
        }

        return $this->render_custom_menu($menu, '', '', $menuid);
    }

    /**
     * Returns true if needs from array found in haystack
     * @param array $needles a list of strings to check
     * @param string $haystack value which may contain string
     * @return boolean
     */
    public function check_if_in_array_string($needles, $haystack) {
        foreach ($needles as $needle) {
            $needle = trim($needle);
            if (strstr($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns html to render tools menu in main navigation bar
     *
     * @param string $menuid The id to use when creating menu. Used so this can be called for a nav drawer style display.
     *
     *
     * @return string
     */
    public function tools_menu($menuid = '') {
        global $PAGE;
        $custommenuitems = '';
        $access = true;
        $retval = '';

        if (!isset($PAGE->theme->settings->toolsmenuscount)) {
            return '';
        }
        $toolsmenuscount = $PAGE->theme->settings->toolsmenuscount;

        $class = '';
        if (!empty($PAGE->theme->settings->navbardisplayicons)) {
            $class .= "<i class='fa fa-wrench fa-lg'></i>";
        }
        $class .= "<span class='menutitle'>";

        for ($i = 1; $i <= $toolsmenuscount; $i++) {
            $menunumber = 'toolsmenu' . $i;
            $menutitle = $menunumber . 'title';
            $requirelogin = $menunumber . 'requirelogin';
            $accessrules = $menunumber . 'field';
            $access = true;

            if (!empty($PAGE->theme->settings->$accessrules)) {
                $fields = explode ('=', $PAGE->theme->settings->$accessrules);
                $ftype = $fields[0];
                $setvalue = $fields[1];
                if (!$this->check_menu_access($ftype, $setvalue, $menunumber)) {
                    $access = false;
                }
            }

            if (!empty($PAGE->theme->settings->$menunumber) && $access == true && !$this->hideinforum()) {
                $menu = ($PAGE->theme->settings->$menunumber);

                /******************************************************************************************
                 * @copyright 2018 Mathieu Domingo
                 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
                 *
                 * Parse the end of each line to look for capabilities.
                 */

                // Explode the content of the toolmenu in an "array of lines".
                $linesmenu = explode("\n", $menu);

                // For each line we take the "$key" to be able to remove it from the "array of lines".
                foreach ($linesmenu as $key => $line) {
                    // Explode each line in an "array of cells".
                    $cells = explode("|", $line);

                    // If there is more than 3 cells, the user have add some "|text" to the line.
                    if (count($cells) > 3) {
                        // We look each cells added to the line for capabilities.
                        for ($i = 3; $i < count($cells); $i++) {
                            // Check if the current cell contain a valid capability or not.
                            if (!$capinfo = get_capability_info(trim($cells[$i]))) {

                                // Should we say to the user that the capability is not valid ?
                                // It should be better to print this when the "admin" fill the toolmenu, not when we print it.

                                // If it's not valid, check the next cell (here we could change the behaviour from "do nothing"
                                // to "delete the line").
                                continue;
                            }

                            // Check if the current user have the capability contained in the current cell.
                            if (!has_capability(trim($cells[$i]), context_course::instance($PAGE->course->id))) {
                                // We remove the current line from the array.
                                unset($linesmenu[$key]);

                                // We have removed the line, we don't need to check nexts cells.
                                break;

                                // NOTE: The behaviour here is "the user need to have ALL capabilities written on the line"
                                // I.E: AND logic only, it needs a more complex traitement if we want to take in
                                // account some logics mixing OR and AND.
                            }
                        }
                    }
                }

                // Once we have finish to check all lines, we recreate the menu
                // (without the lines that the user don't have the capabilities needed) to continue the original process.
                $menu = implode("\n", $linesmenu);

                $label = $PAGE->theme->settings->$menutitle;

                // Check the option of displaying a sub-menu arrow symbol.
                if (!empty($PAGE->theme->settings->navbardisplaysubmenuarrow)) {
                    $label .= ' &nbsp;<i class="fa fa-caret-down"></i>';
                }

                $custommenuitems = $this->parse_custom_menu($menu, $label, $class, '</span>');
                $custommenu = new custom_menu($custommenuitems);
                $retval .= $this->render_custom_menu($custommenu, '', '', $menuid);
            }
        }
        return $retval;
    }

    /**
     * Returns html to render logo / title area.
     * @param bool/int $currenttopcat The id of the current top category or false if none.
     *
     * @return string Markup.
     */
    public function get_logo_title($currenttopcat) {
        global $PAGE, $COURSE, $CFG, $SITE;
        $retval = '';

        $responsivelogo = $PAGE->theme->settings->responsivelogo;
        $responsivecoursetitle = $PAGE->theme->settings->responsivecoursetitle;

        $logosetarea = '';
        if (!empty($currenttopcat)) {
            $categoryheaderlogoset = 'categoryheaderlogo'.$currenttopcat;
            if (!empty($PAGE->theme->settings->$categoryheaderlogoset)) {
                $logosetarea = $categoryheaderlogoset;
            }
        }
        if ((empty($logosetarea)) && (!empty($PAGE->theme->settings->logo))) {
            $logosetarea = 'logo';
        }
        if (!empty($logosetarea)) {
            // Logo.
            $retval .= '<div class="p-2 bd-highlight ' . $responsivelogo . '">';
            $logo = '<img src=' . $PAGE->theme->setting_file_url($logosetarea, $logosetarea) . ' id="logo" alt="" />';

            // Exception - Quiz page - logo is not a link to site homepage.
            if ($PAGE->pagetype == "mod-quiz-attempt") {
                $retval .= $logo;
            } else {
                // Standard - Output the logo as a link to site homepage.
                $retval .= '<a href=' . $CFG->wwwroot . ' aria-label="home" title="' . format_string($SITE->fullname). '">';
                $retval .= $logo;
                $retval .= '</a>';
            }
            $retval .= '</div>';
        }

        $coursetitlemaxwidth =
            (!empty($PAGE->theme->settings->coursetitlemaxwidth) ? $PAGE->theme->settings->coursetitlemaxwidth : 0);

        // If it is a mobile and the site title/course is not hidden or it is a desktop then we display the site title / course.
        $usedefault = false;
        $categoryheadercustomtitle = '';
        if (!empty($currenttopcat)) {
            $categoryheadercustomtitleset = 'categoryheadercustomtitle'.$currenttopcat;
            if (!empty($PAGE->theme->settings->$categoryheadercustomtitleset)) {
                $categoryheadercustomtitle = $PAGE->theme->settings->$categoryheadercustomtitleset;
            }
        }

        // If course id is greater than 1 we display course title.
        if ($COURSE->id > 1) {
            // Select title.
            $coursetitle = '';

            switch ($PAGE->theme->settings->enableheading) {
                case 'fullname':
                    // Full Course Name.
                    $coursetitle = $COURSE->fullname;
                    break;

                case 'shortname':
                    // Short Course Name.
                    $coursetitle = $COURSE->shortname;
                    break;
            }

            // Check max width of course title and trim if appropriate.
            if (($coursetitlemaxwidth > 0) && ($coursetitle <> '')) {
                if (strlen($coursetitle) > $coursetitlemaxwidth) {
                    $coursetitle = core_text::substr($coursetitle, 0, $coursetitlemaxwidth) . " ...";
                }
            }

            switch ($PAGE->theme->settings->enableheading) {
                case 'fullname':
                    // Full Course Name.
                    $retval .= '<div id="sitetitle" class="p-2 bd-highlight ' . $responsivecoursetitle . '">';
                    if (!empty($categoryheadercustomtitle)) {
                        $retval .= '<h1>'. format_string($categoryheadercustomtitle) . '</h1>';
                    }
                    $retval .= '<h1>'. format_string($coursetitle) . '</h1>';
                    $retval .= '</div>';
                    break;

                case 'shortname':
                    // Short Course Name.
                    $retval .= '<div id="sitetitle" class="p-2 bd-highlight ' . $responsivecoursetitle . '">';
                    if (!empty($categoryheadercustomtitle)) {
                        $retval .= '<h1>'. format_string($categoryheadercustomtitle) . '</h1>';
                    }
                    $retval .= '<h1>'. format_string($coursetitle) . '</h1>';
                    $retval .= '</div>';
                    break;

                default:
                    // Default 'off'.
                    $usedefault = true;
                    break;
            }
        }

        // If course id is one or 'enableheading' was 'off' above then we display the site title.
        if (($COURSE->id == 1) || ($usedefault)) {
            if (!empty($categoryheadercustomtitle)) {
                $retval .= '<div id="sitetitle" class="p-2 bd-highlight ' . $responsivecoursetitle . '">';
                $retval .= '<h1>'. format_string($categoryheadercustomtitle) . '</h1>';
                $retval .= '</div>';
            } else {
                switch ($PAGE->theme->settings->sitetitle) {
                    case 'default':
                        $sitetitle = $SITE->fullname;
                        $retval .= '<div id="sitetitle" class="p-2 bd-highlight ' . $responsivecoursetitle . '"><h1>'
                            . format_string($sitetitle) . '</h1></div>';
                        break;

                    case 'custom':
                        // Custom site title.
                        if (!empty($PAGE->theme->settings->sitetitletext)) {
                            $header = theme_adaptable_remove_site_fullname($PAGE->theme->settings->sitetitletext);
                            $sitetitlehtml = $PAGE->theme->settings->sitetitletext;
                            $header = format_string($header);
                            $PAGE->set_heading($header);

                            $retval .= '<div id="sitetitle" class="p-2 bd-highlight ' . $responsivecoursetitle . '">'
                                . format_text($sitetitlehtml, FORMAT_HTML) . '</div>';
                        }
                }
            }
        }

        return $retval;
    }

    /**
     * Returns html to render top menu items
     *
     * @param bool $showlinktext
     *
     * @return string
     */
    public function get_top_menus($showlinktext = false) {
        global $PAGE, $COURSE;
        $template = new stdClass();
        $menus = array();
        $visibility = true;
        $nummenus = 0;

        if (!empty($PAGE->theme->settings->menuuseroverride)) {
            $visibility = $this->check_menu_user_visibility();
        }

        $template->showright = false;
        if (!empty($PAGE->theme->settings->menuslinkright)) {
            $template->showright = true;
        }

        if (!empty($PAGE->theme->settings->menuslinkicon)) {
            $template->menuslinkicon = $PAGE->theme->settings->menuslinkicon;
        } else {
            $template->menuslinkicon = 'fa-link';
        }

        if ($visibility) {
            if (!empty($PAGE->theme->settings->topmenuscount) && !empty($PAGE->theme->settings->enablemenus)
                    && (!$PAGE->theme->settings->disablemenuscoursepages || $COURSE->id == 1)) {
                $topmenuscount = $PAGE->theme->settings->topmenuscount;

                for ($i = 1; $i <= $topmenuscount; $i++) {
                    $menunumber = 'menu' . $i;
                    $newmenu = 'newmenu' . $i;
                    $class = 'newmenu' . ($i + 4);
                    $fieldsetting = 'newmenu' . $i . 'field';
                    $valuesetting = 'newmenu' . $i . 'value';
                    $newmenutitle = 'newmenu' . $i . 'title';
                    $requirelogin = 'newmenu' . $i . 'requirelogin';
                    $logincheck = true;
                    $custommenuitems = '';
                    $access = true;

                    if (empty($PAGE->theme->settings->$requirelogin) || isloggedin()) {
                        if (!empty($PAGE->theme->settings->$fieldsetting)) {
                            $fields = explode('=', $PAGE->theme->settings->$fieldsetting);
                            $ftype = $fields[0];
                            $setvalue = $fields[1];
                            if (!$this->check_menu_access($ftype, $setvalue, $menunumber)) {
                                $access = false;
                            }
                        }

                        if (!empty($PAGE->theme->settings->$newmenu) && $access == true) {
                            $nummenus++;
                            $menu = ($PAGE->theme->settings->$newmenu);
                            $title = ($PAGE->theme->settings->$newmenutitle);
                            $custommenuitems = $this->parse_custom_menu($menu, format_string($title));
                            $custommenu = new custom_menu($custommenuitems, current_language());
                            $menus[] = $this->render_overlay_menu($custommenu);
                        }
                    }
                }
            }
        }

        if ($nummenus == 0) {
            return '';
        }

        $template->rows = array();

        $grid = array(
                '5' => '3',
                '6' => '3',
                '7' => '4',
                '8' => '4',
                '9' => '3',
                '10' => '4',
                '11' => '4',
                '12' => '4'
        );

        if ($nummenus <= 4) {
            $row = new stdClass();
            $row->span = (12 / $nummenus);
            $row->menus = $menus;
            $template->rows[] = $row;
        } else {
            $numperrow = $grid[$nummenus];
            $chunks = array_chunk($menus, $numperrow);
            $menucount = 0;
            for ($i = 0; $i < $nummenus; $i++) {
                if ($i % $numperrow == 0) {
                    $row = new stdClass();
                    $row->span = (12 / $numperrow);
                    $row->menus = $chunks[$menucount++];
                    $template->rows[] = $row;
                }
            }
        }

        if ($showlinktext == false) {
            $template->showlinktext = false;
        } else {
            $template->showlinktext = true;
        }

        return $this->render_from_template('theme_adaptable/overlaymenu', $template);
    }

    /**
     * Render the menu items for the overlay menu
     *
     * @param custom_menu $menu
     * @return array of menus
     */
    private function render_overlay_menu(custom_menu $menu) {
        $template = new stdClass();
        if (!$menu->has_children()) {
            return '';
        }
        $template->menuitems = '';
        foreach ($menu->get_children() as $item) {
            $template->menuitems .= $this->render_overlay_menu_item($item, 0);
        }
        return $template;
    }

    /**
     * Render the overlay menu items.
     *
     * @param custom_menu_item $item
     * @param int $level
     *
     * @return string html for item
     */
    private function render_overlay_menu_item(custom_menu_item $item, $level = 0) {
        $content = '';
        if ($item->has_children()) {
            $node = new stdClass;
            $node->title = $item->get_title();
            $node->text = $item->get_text();
            $node->class = 'level-' . $level;

            // Top level menu.  Check if URL contains a valid URL, if not
            // then use standard javascript:void(0).  Done to fix current
            // jquery / Bootstrap incompatibility with using # in target URLS.
            // Ref: Issue 617 on Adaptable theme issues on Bitbucket.
            if (empty($item->get_url())) {
                $node->url = "javascript:void(0)";
            } else {
                $node->url = $item->get_url();
            }

            $content .= $this->render_from_template('theme_adaptable/overlaymenuitem', $node);
            $level++;
            foreach ($item->get_children() as $subitem) {
                $content .= $this->render_overlay_menu_item($subitem, $level);
            }
        } else {
            $node = new stdClass;
            $node->title = $item->get_title();
            $node->text = $item->get_text();
            $node->class = 'level-' . $level;
            $node->url = $item->get_url();
            $content .= $this->render_from_template('theme_adaptable/overlaymenuitem', $node);
        }
        return $content;
    }

    /**
     * Checks menu visibility where setup to allow users to control via custom profile setting
     *
     * @return boolean
     */
    public function check_menu_user_visibility() {
        global $PAGE, $USER, $COURSE;
        $uservalue = '';

        if (empty($PAGE->theme->settings->menuuseroverride)) {
            return true;
        }

        if (isset($USER->theme_adaptable_menus['menuvisibility'])) {
            $uservalue = $USER->theme_adaptable_menus['menuvisibility'];
        } else {
            $profilefield = $PAGE->theme->settings->menuoverrideprofilefield;
            $profilefield = 'profile_field_' . $profilefield;
            $uservalue = $this->get_user_visibility($profilefield);
        }

        if ($uservalue == 0) {
            return true;
        }

        if ($uservalue == 1 && $COURSE->id != 1) {
            return false;
        }

        if ($uservalue == 2) {
            return false;
        }

        // Default to true means we dont have to evaluate sitewide setting and guarantees return value.
        return true;
    }

    /**
     * Check users menu visibility settings, will store in session to avaoid repeated loading of profile data
     * @param string $profilefield
     * @return boolean
     */
    public function get_user_visibility($profilefield) {
        global $USER, $CFG;
        $uservisibility = '';

        require_once($CFG->dirroot.'/user/profile/lib.php');
        require_once($CFG->dirroot.'/user/lib.php');
        profile_load_data($USER);

        $uservisibility = $USER->$profilefield;
        $USER->theme_adaptable_menus['menuvisibility'] = $uservisibility;
        return $uservisibility;
    }

    /**
     * Checks menu access based on admin settings and a users custom profile fields
     *
     * @param string $ftype the custom profile field
     * @param string $setvalue the expected value a user must have in their profile field
     * @param string $menu a token to identify the menu used to store access in session
     * @return boolean
     */
    public function check_menu_access($ftype, $setvalue, $menu) {
        global $PAGE, $USER, $CFG;
        $usersvalue = 'default-zz'; // Just want a value that will not be matched by accident.
        $sessttl = (time() + ($PAGE->theme->settings->menusessionttl * 60));
        $menuttl = $menu . 'ttl';

        if ($PAGE->theme->settings->menusession) {
            if (isset($USER->theme_adaptable_menus[$menu])) {

                // If cache hasn't yet expired.
                if ($USER->theme_adaptable_menus[$menuttl] >= time()) {
                    if ($USER->theme_adaptable_menus[$menu] == true) {
                        return true;
                    } else if ($USER->theme_adaptable_menus[$menu] == false) {
                        return false;
                    }
                }
            }
        }

        require_once($CFG->dirroot.'/user/profile/lib.php');
        require_once($CFG->dirroot.'/user/lib.php');
        profile_load_data($USER);
        $ftype = "profile_field_$ftype";
        if (isset($USER->$ftype)) {
            $usersvalue = $USER->$ftype;
        }

        if ($usersvalue == $setvalue) {
            $USER->theme_adaptable_menus[$menu] = true;
            $USER->theme_adaptable_menus[$menuttl] = $sessttl;
            return true;
        }

        $USER->theme_adaptable_menus[$menu] = false;
        $USER->theme_adaptable_menus[$menuttl] = $sessttl;
        return false;
    }

    /**
     * Returns list of cohort enrollments
     *
     * @return array
     */
    public function get_cohort_enrollments() {
        global $DB, $USER;
        $userscohorts = $DB->get_records('cohort_members', array('userid' => $USER->id));
        $courses = array();
        if ($userscohorts) {
            $cohortedcourseslist = $DB->get_records_sql('select '
                    . 'courseid '
                    . 'from {enrol} '
                    . 'where enrol = "cohort" '
                    . 'and customint1 in (?)', array_keys($userscohorts));
            $cohortedcourses = $DB->get_records_list('course', 'id', array_keys($cohortedcourseslist), null, 'shortname');
            foreach ($cohortedcourses as $course) {
                $courses[] = $course->shortname;
            }
        }
        return($courses);
    }

    /**
     * Returns contents of multiple comma delimited custom profile fields
     *
     * @param string $profilefields delimited list of fields
     * @return array
     */
    public function get_profile_field_contents($profilefields) {
        global $PAGE, $USER, $CFG;
        $timestamp = 'currentcoursestime';
        $list = 'currentcourseslist';

        if (isset($USER->theme_adaptable_menus[$timestamp])) {
            if ($USER->theme_adaptable_menus[$timestamp] >= time()) {
                if (isset($USER->theme_adaptable_menus[$list])) {
                    return $USER->theme_adaptable_menus[$list];
                }
            }
        }

        $sessttl = 1000 * 60 * 3;
        $sessttl = 0;
        $sessttl = time() + $sessttl;
        $retval = array();

        require_once($CFG->dirroot.'/user/profile/lib.php');
        require_once($CFG->dirroot.'/user/lib.php');
        profile_load_data($USER);

        $fields = explode(',', $profilefields);

        foreach ($fields as $field) {
            $field = trim($field);
            $field = "profile_field_$field";
            if (isset($USER->$field)) {
                $vals = explode(',', $USER->$field);
                foreach ($vals as $value) {
                    $retval[] = trim($value);
                }
            }
        }

        $USER->theme_adaptable_menus[$list] = $retval;
        $USER->theme_adaptable_menus[$timestamp] = $sessttl;
        return $retval;
    }

    /**
     * Parses / wraps custom menus in HTML
     *
     * @param string $menu
     * @param string $label
     * @param string $class
     * @param string $close
     *
     * @return string
     */
    public function parse_custom_menu($menu, $label, $class = '', $close = '') {

        // Top level menu option.  No URL added after $close (previously was #).
        // Done to fix current jquery / Bootstrap version incompatibility with using #
        // in target URLS. Ref: Issue 617 on Adaptable theme issues on Bitbucket.
        $custommenuitems = $class . $label. $close . "||".$label."\n";
        $arr = explode("\n", $menu);

        // We want to force everything inputted under this menu.
        foreach ($arr as $key => $value) {
            $arr[$key] = '-' . $arr[$key];
        }

        $custommenuitems .= implode("\n", $arr);
        return $custommenuitems;
    }

    /**
     * Hide tools menu in forum to make room for forum search optoin
     *
     * @return boolean
     */
    public function hideinforum() {
        global $PAGE;
        $hidelinks = false;
        if (!empty($PAGE->theme->settings->hideinforum)) {
            if (strstr($_SERVER['REQUEST_URI'], '/mod/forum/')) {
                $hidelinks = true;
            }
        }
        return $hidelinks;
    }

    /**
     * Wrap html round custom menu
     *
     * @param string $custommenu
     * @param string $classno
     *
     * @return string
     */
    public function wrap_custom_menu_top($custommenu, $classno) {
        $retval = '<div class="dropdown pull-right newmenus newmenu$classno">';
        $retval .= $custommenu;
        $retval .= '</div>';
        return $retval;
    }

    /**
     * Returns language menu
     *
     * @param bool $showtext
     *
     * @return string
     */
    public function lang_menu($showtext = true) {
        global $CFG;
        $langmenu = new custom_menu();

        $addlangmenu = true;
        $langs = get_string_manager()->get_list_of_translations();
        if (count($langs) < 2 || empty($CFG->langmenu) || ($this->page->course != SITEID && !empty($this->page->course->lang))) {
            $addlangmenu = false;
        }

        if ($addlangmenu) {
            $strlang = get_string('language');
            $currentlang = current_language();

            if (isset($langs[$currentlang])) {
                $currentlang = $langs[$currentlang];
            } else {
                $currentlang = $strlang;
            }

            if ($showtext != true) {
                $currentlang = '';
            }

            $this->language = $langmenu->add('<i class="fa fa-globe fa-lg"></i><span class="langdesc">'.$currentlang.'</span>',
                                        new moodle_url($this->page->url), $strlang, 10000);

            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }
        return $this->render_custom_menu($langmenu, '', '', 'langmenu');
    }

    /**
     * Display custom menu in the format required for the nav drawer. Slight cludge here to make this work.
     * The calling function can't call the default custom_menu() method as there is no way to know to
     * render custom menu items in the format required for the drawer (which is different from displaying on the normal navbar).
     *
     * @return Custom menu html
     */
    public function custom_menu_drawer() {
        global $CFG;

        if (!empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        } else {
            return '';
        }

        $custommenu = new custom_menu($custommenuitems, current_language());
        return $this->render_custom_menu($custommenu, '', '', 'custom-menu-drawer');
    }

    /**
     * This renders the bootstrap top menu.
     * This renderer is needed to enable the Bootstrap style navigation.
     *
     * @param custom_menu $menu
     * @param string $wrappre
     * @param string $wrappost
     * @param string $menuid
     *
     * @return string
     */
    protected function render_custom_menu(custom_menu $menu, $wrappre = '', $wrappost = '', $menuid = '') {
        global $CFG;

        // TODO: eliminate this duplicated logic, it belongs in core, not
        // here. See MDL-39565.
        $addlangmenu = true;
        $langs = get_string_manager()->get_list_of_translations();
        if (count($langs) < 2
                or empty($CFG->langmenu)
                or ($this->page->course != SITEID and !empty($this->page->course->lang))) {
            $addlangmenu = false;
        }

        if (!$menu->has_children() && $addlangmenu === false) {
            return '';
        }

        $content = '';
        foreach ($menu->get_children() as $item) {
            if (stristr($menuid, 'drawer')) {
                $content .= $this->render_custom_menu_item_drawer($item, 0, $menuid, false);
            } else {
                $content .= $this->render_custom_menu_item($item, 0, $menuid);
            }
        }
        $content = $wrappre . $content . $wrappost;
        return $content;
    }

    /**
     * This code renders the custom menu items for the bootstrap dropdown menu.
     *
     * @param custom_menu_item $menunode
     * @param int $level = 0
     * @param int $menuid
     *
     * @return string
     */
    protected function render_custom_menu_item(custom_menu_item $menunode, $level = 0, $menuid = '') {
        static $submenucount = 0;

        if ($menunode->has_children()) {

            $url = '#';

            $content = '<li class="nav-item dropdown my-auto">';
            $content .= html_writer::start_tag('a', array('href' => $url,
                    'class' => 'nav-link dropdown-toggle my-auto', 'role' => 'button',
                    'id' => $menuid . $submenucount,
                    'aria-haspopup' => 'true',
                    'aria-expanded' => 'false',
                    'aria-controls' => 'dropdown' . $menuid . $submenucount,
                    'data-target' => $url,
                    'data-toggle' => 'dropdown',
                    'title' => $menunode->get_title()));
            $content .= $menunode->get_text();
            $content .= '</a>';
            $content .= '<ul role="menu" class="dropdown-menu" id="dropdown' . $menuid . $submenucount . '" aria-labelledby="'
                        .$menuid . $submenucount . '">';

            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode, 1, $menuid . $submenucount);
            }
            $content .= '</ul></li>';

        } else {
            if ($level == 0) {
                $content = '<li class="nav-item">';
                $linkclass = 'nav-link';
            } else {
                $content = '<li>';
                $linkclass = 'dropdown-item';
            }
            // The node doesn't have children so produce a final menuitem.
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#';
            }

            /* This is a bit of a cludge, but allows us to pass url, of type moodle_url with a param of
             * "helptarget", which when equal to "_blank", will create a link with target="_blank" to allow the link to open
             * in a new window.  This param is removed once checked.
             */
            if (is_object($url) && (get_class($url) == 'moodle_url') && ($url->get_param('helptarget') != null)) {
                $helptarget = $url->get_param('helptarget');
                $url->remove_params('helptarget');
                $content .= html_writer::link($url, $menunode->get_text(), array('title' => $menunode->get_title(),
                        'target' => $helptarget, 'class' => $linkclass));
            } else {
                $content .= html_writer::link($url, $menunode->get_text(),
                        array('title' => $menunode->get_title(), 'class' => $linkclass));
            }

            $content .= "</li>";
        }
        return $content;
    }

    /**
     * This code renders the custom menu items for the bootstrap dropdown menu.
     *
     * @param custom_menu_item $menunode
     * @param int $level = 0
     * @param int $menuid
     * @param bool $indent
     *
     * @return string
     */
    protected function render_custom_menu_item_drawer(custom_menu_item $menunode, $level = 0, $menuid = '', $indent = false) {
        static $submenucount = 0;

        if ($menunode->has_children()) {

            $submenucount++;
            $url = '#';

            $content = html_writer::start_tag('a', array('href' => '#' . $menuid . $submenucount,
                        'class' => 'list-group-item dropdown-toggle',
                        'aria-haspopup' => 'true', 'data-target' => $url, 'data-toggle' => 'collapse',
                        'title' => $menunode->get_title()));
            $content .= $menunode->get_text();
            $content .= '</a>';

            $content .= '<ul class="collapse" id="' . $menuid . $submenucount . '">';
            $indent = true;
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item_drawer($menunode, 1, $menuid . $submenucount, $indent);
            }
            $content .= '</ul>';
        } else {

            // The node doesn't have children so produce a final menuitem.
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#';
            }

            $marginclass = 'm-l-0';

            $dataindent = 0;
            if ($indent) {
                $dataindent = 1;
                $marginclass = ' m-l-1';
            }

            $content = '<li class="' . $marginclass . '">';

            $content = '<a class="list-group-item list-group-item-action" href="' . $url . '"';
            $content .= 'data-key="" data-isexpandable="0" data-indent="' . $dataindent;
            $content .= '" data-showdivider="0" data-type="1" data-nodetype="1"';
            $content .= 'data-collapse="0" data-forceopen="1" data-isactive="1" data-hidden="0" ';
            $content .= 'data-preceedwithhr="0" data-parent-key="' . $menuid . '">';
            $content .= '<div class="' . $marginclass . '">';
            $content .= $menunode->get_text();
            $content .= '</div></a></li>';

        }
        return $content;
    }


    /**
     * Renders tabtree
     *
     * @param tabtree $tabtree
     * @return string
     */
    protected function render_tabtree(tabtree $tabtree) {
        if (empty($tabtree->subtree)) {
            return '';
        }
        $firstrow = $secondrow = '';
        foreach ($tabtree->subtree as $tab) {
            $firstrow .= $this->render($tab);
            if (($tab->selected || $tab->activated) && !empty($tab->subtree) && $tab->subtree !== array()) {
                $secondrow = $this->tabtree($tab->subtree);
            }
        }
        return html_writer::tag('ul', $firstrow, array('class' => 'nav nav-tabs mb-3')) . $secondrow;
    }

    /**
     * Renders tabobject (part of tabtree)
     *
     * This function is called from {@link core_renderer::render_tabtree()}
     * and also it calls itself when printing the $tabobject subtree recursively.
     *
     * @param tabobject $tab
     * @return string HTML fragment
     */
    protected function render_tabobject(tabobject $tab) {
        if ($tab->selected or $tab->activated) {
            return html_writer::tag('li', html_writer::tag('a', $tab->text,
                array('class' => 'nav-link active')), array('class' => 'nav-item'));
        } else if ($tab->inactive) {
            return html_writer::tag('li', html_writer::tag('a', $tab->text, array('class' => 'nav-link disabled')),
                array('class' => 'nav-link'));
        } else {
            if (!($tab->link instanceof moodle_url)) {
                // Backward compatibility when link was passed as quoted string.
                $link = "<a class=\"nav-link\" href=\"$tab->link\" title=\"$tab->title\">$tab->text</a>";
            } else {
                $link = html_writer::link($tab->link, $tab->text, array('title' => $tab->title, 'class' => 'nav-link'));
            }
            return html_writer::tag('li', $link, array('class' => 'nav-item'));
        }
    }

    /**
     * Returns empty string
     *
     * @return string
     */
    protected function theme_switch_links() {
        // We're just going to return nothing and fail nicely, whats the point in bootstrap if not for responsive?
        return '';
    }

    /**
     * Output all the blocks in a particular region.
     *
     * @param string $region the name of a region on this page.
     * @return string the HTML to be output.
     */
    public function blocks_for_region($region) {
        /* If 'shownavigationblockoncoursepage' is false and we are in a 'course' or 'incourse' page then
           the navigation block will not be shown. */
        if ((!empty($this->page->theme->settings->shownavigationblockoncoursepage)) ||
            (($this->page->pagelayout != 'course') && ($this->page->pagelayout != 'incourse'))) {
            return parent::blocks_for_region($region);
        }
        $blockcontents = $this->page->blocks->get_content_for_region($region, $this);
        $blocks = $this->page->blocks->get_blocks_for_region($region);

        $lastblock = null;
        $zones = array();
        foreach ($blocks as $block) {
            if ($block->instance->blockname == 'navigation') {
                continue;
            }
            $zones[] = $block->title;
        }
        $output = '';

        foreach ($blockcontents as $bc) {
            if ($bc->attributes['data-block'] == 'navigation') {
                continue;
            }
            if ($bc instanceof block_contents) {
                $output .= $this->block($bc, $region);
                $lastblock = $bc->title;
            } else if ($bc instanceof block_move_target) {
                $output .= $this->block_move_target($bc, $zones, $lastblock, $region);
            } else {
                throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
            }
        }
        return $output;
    }

    /**
     * Render blocks
     * @param string $region
     * @param array $classes
     * @param string $tag
     * @return string
     */
    public function blocks($region, $classes = array(), $tag = 'aside') {
        $output = parent::blocks($region, $classes, $tag);

        if ((!empty($output)) && ($region == 'side-post')) {
            $output .= html_writer::tag('div',
                html_writer::tag('i', '', array('class' => 'fa fa-3x fa-angle-left', 'aria-hidden' => 'true')),
                array('id' => 'showsidebaricon', 'title' => get_string('sidebaricon', 'theme_adaptable')));
            $this->page->requires->js_call_amd('theme_adaptable/showsidebar', 'init');
        }

        return $output;
    }

    /**
     * This is an optional menu that can be added to a layout by a theme. It contains the
     * menu for the course administration, only on the course main page. Lifted from Boost theme
     * to use for the course actions menu.
     *
     * @return string
     */
    public function context_header_settings_menu() {
        $context = $this->page->context;
        $menu = new action_menu();

        $items = $this->page->navbar->get_items();
        $currentnode = end($items);

        $showcoursemenu = false;
        $showfrontpagemenu = false;
        $showusermenu = false;

        // We are on the course home page.
        if (($context->contextlevel == CONTEXT_COURSE) &&
        !empty($currentnode) &&
        ($currentnode->type == navigation_node::TYPE_COURSE ||
         $currentnode->type == navigation_node::TYPE_SECTION ||
         $currentnode->type == navigation_node::TYPE_SETTING)) { // Show cog on grade report page.
            $showcoursemenu = true;
        }

        $courseformat = course_get_format($this->page->course);
        // This is a single activity course format, always show the course menu on the activity main page.
        if ($context->contextlevel == CONTEXT_MODULE &&
        !$courseformat->has_view_page()) {

            $this->page->navigation->initialise();
            $activenode = $this->page->navigation->find_active_node();
            // If the settings menu has been forced then show the menu.
            if ($this->page->is_settings_menu_forced()) {
                $showcoursemenu = true;
            } else if (!empty($activenode) && ($activenode->type == navigation_node::TYPE_ACTIVITY ||
                $activenode->type == navigation_node::TYPE_RESOURCE)) {

                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.
                if ($currentnode && ($currentnode->key == $activenode->key && $currentnode->type == $activenode->type)) {
                    $showcoursemenu = true;
                }
            }
        }

        // This is the site front page.
        if ($context->contextlevel == CONTEXT_COURSE &&
            !empty($currentnode) &&
            $currentnode->key === 'home') {
                $showfrontpagemenu = true;
        }

        // This is the user profile page.
        if ($context->contextlevel == CONTEXT_USER &&
            !empty($currentnode) &&
            ($currentnode->key === 'myprofile')) {
                $showusermenu = true;
        }

        if ($showfrontpagemenu) {
            $settingsnode = $this->page->settingsnav->find('frontpage', navigation_node::TYPE_SETTING);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', ''));
                    $menu->add_secondary_action($link);
                }
            }
            return $this->render($menu);
        } else if ($showcoursemenu) {
            $settingsnode = $this->page->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', ''));
                    $menu->add_secondary_action($link);
                }
            }
            return $this->render($menu);
        } else if ($showusermenu) {
            // Get the course admin node from the settings navigation.
            $settingsnode = $this->page->settingsnav->find('useraccount', navigation_node::TYPE_CONTAINER);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $this->build_action_menu_from_navigation($menu, $settingsnode);
            }
            return $this->render($menu);
        }

        return '';
    }

    /**
     * Mobile settings menu.
     *
     * TODO: Possibly make a Mustache template for all of the menu?
     *
     * @return string Markup.
     */
    public function context_mobile_settings_menu() {
        $output = '';

        $showcourseitems = false;
        $context = $this->page->context;
        $items = $this->page->navbar->get_items();
        $currentnode = end($items);

        // We are on the course home page.
        if (($context->contextlevel == CONTEXT_COURSE) &&
            !empty($currentnode) &&
            ($currentnode->type == navigation_node::TYPE_COURSE || $currentnode->type == navigation_node::TYPE_SECTION)) {
            $showcourseitems = true;
        }

        $courseformat = course_get_format($this->page->course);
        // This is a single activity course format, always show the course menu on the activity main page.
        if ($context->contextlevel == CONTEXT_MODULE &&
            !$courseformat->has_view_page()) {

            $this->page->navigation->initialise();
            $activenode = $this->page->navigation->find_active_node();
            // If the settings menu has been forced then show the menu.
            if ($this->page->is_settings_menu_forced()) {
                $showcourseitems = true;
            } else if (!empty($activenode) && ($activenode->type == navigation_node::TYPE_ACTIVITY ||
                $activenode->type == navigation_node::TYPE_RESOURCE)) {

                /* We only want to show the menu on the first page of the activity.  This means
                   the breadcrumb has no additional nodes. */
                if ($currentnode && ($currentnode->key == $activenode->key && $currentnode->type == $activenode->type)) {
                    $showcourseitems = true;
                }
            }
        }

        if ($showcourseitems) {
            $settingsnode = $this->page->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
            if ($settingsnode) {
                $displaykeys = array('turneditingonoff', 'editsettings'); // In the order we want.
                $displaykeyscount = count($displaykeys);
                $displaynodes = array();
                foreach ($settingsnode->children as $node) {
                    if ($node->display) {
                        if (in_array($node->key, $displaykeys)) {
                            $displaynodes[$node->key] = $node;
                        }
                        if (count($displaynodes) == $displaykeyscount) {
                            break;
                        }
                    }
                }

                foreach ($displaykeys as $displaykey) { // Ensure order.
                    if (!empty($displaynodes[$displaykey])) {
                        $currentnode = $displaynodes[$displaykey];
                        $output .= '<a class="list-group-item list-group-item-action " href="'.$currentnode->action.'">';
                        $output .= '<div class="m-l-0">';
                        $output .= '<div class="media">';
                        $output .= '<span class="media-left">';
                        $output .= $this->render($currentnode->icon);
                        $output .= '</span>';
                        $output .= '<span class="media-body ">'.$currentnode->text.'</span>';
                        $output .= '</div>';
                        $output .= '</div>';
                        $output .= '</a >';
                    }
                }
            }
        }

        return $output;
    }

    /**
     * This is an optional menu that can be added to a layout by a theme. It contains the
     * menu for the most specific thing from the settings block. E.g. Module administration. Lifted from Boost.
     *
     * @return string
     */
    public function region_main_settings_menu() {
        $context = $this->page->context;
        $menu = new action_menu();

        if ($context->contextlevel == CONTEXT_MODULE) {

            $this->page->navigation->initialise();
            $node = $this->page->navigation->find_active_node();
            $buildmenu = true;
            // If the settings menu has been forced then show the menu.
            if ($this->page->is_settings_menu_forced()) {
                $buildmenu = true;
            } else if (!empty($node) && ($node->type == navigation_node::TYPE_ACTIVITY ||
                    $node->type == navigation_node::TYPE_RESOURCE)) {

                $items = $this->page->navbar->get_items();
                $navbarnode = end($items);
                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.
                if ($navbarnode && ($navbarnode->key === $node->key && $navbarnode->type == $node->type)) {
                    $buildmenu = true;
                }
            }
            if ($buildmenu) {
                // Get the course admin node from the settings navigation.
                $node = $this->page->settingsnav->find('modulesettings', navigation_node::TYPE_SETTING);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }
            }

        } else if ($context->contextlevel == CONTEXT_COURSECAT) {
            // For course category context, show category settings menu, if we're on the course category page.
            if ($this->page->pagetype === 'course-index-category') {
                $node = $this->page->settingsnav->find('categorysettings', navigation_node::TYPE_CONTAINER);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }
            }

        } else {
            return '';
        }
        return $this->render($menu);
    }

    /**
     * Take a node in the nav tree and make an action menu out of it.
     * The links are injected in the action menu. Lifted from Boost theme.
     *
     * @param action_menu $menu
     * @param navigation_node $node
     * @param boolean $indent
     * @param boolean $onlytopleafnodes
     * @return boolean nodesskipped - True if nodes were skipped in building the menu
     */
    protected function build_action_menu_from_navigation(action_menu $menu,
        navigation_node $node, $indent = false, $onlytopleafnodes = false) {
        $skipped = false;

        // Build an action menu based on the visible nodes from this navigation tree.
        foreach ($node->children as $menuitem) {

            if ($menuitem->display) {
                if ($onlytopleafnodes && $menuitem->children->count()) {
                    $skipped = true;
                    continue;
                }
                if ($menuitem->action) {
                    if ($menuitem->action instanceof action_link) {
                        $link = $menuitem->action;
                        // Give preference to setting icon over action icon.
                        if (!empty($menuitem->icon)) {
                            $link->icon = $menuitem->icon;
                        }
                    } else {
                        $link = new action_link($menuitem->action, $menuitem->text, null, null, $menuitem->icon);
                    }
                } else {
                    if ($onlytopleafnodes) {
                        $skipped = true;
                        continue;
                    }
                    $link = new action_link(new moodle_url('#'), $menuitem->text, null, ['disabled' => true], $menuitem->icon);
                }
                if ($indent) {
                    $link->add_class('ml-4');
                }
                if (!empty($menuitem->classes)) {
                    $link->add_class(implode(" ", $menuitem->classes));
                }

                $menu->add_secondary_action($link);
                $skipped = $skipped || $this->build_action_menu_from_navigation($menu, $menuitem, true);
            }
        }
        return $skipped;
    }

    /**
     * Redirects the user by any means possible given the current state
     *
     * This function should not be called directly, it should always be called using
     * the redirect function in lib/weblib.php
     *
     * The redirect function should really only be called before page output has started
     * however it will allow itself to be called during the state STATE_IN_BODY
     *
     * @param string $encodedurl The URL to send to encoded if required
     * @return string The HTML with javascript refresh...
     */
    public function adaptable_redirect($encodedurl) {
        $url = str_replace('&amp;', '&', $encodedurl);
        $this->page->requires->js_function_call('document.location.replace', array($url), false, '0');
        $output = $this->opencontainers->pop_all_but_last();
        $output .= $this->footer();
        return $output;
    }

    /**
     * Returns a search box.
     *
     * @param  string $id     The search box wrapper div id, defaults to an autogenerated one.
     * @return string         HTML with the search form hidden by default.
     */
    public function search_box($id = false) {
        global $CFG;

        // Accessing $CFG directly as using \core_search::is_global_search_enabled would
        // result in an extra included file for each site, even the ones where global search
        // is disabled.
        if (empty($CFG->enableglobalsearch) || !has_capability('moodle/search:query', context_system::instance())) {
            return '';
        }

        $header2searchbox = 'expandable';
        if (!empty($this->page->theme->settings->header2searchbox)) {
            $header2searchbox = $this->page->theme->settings->header2searchbox;
        }

        if ($header2searchbox == 'disabled') {
            return '';
        } else if ($header2searchbox == 'static') {
            $expandable = false;
        } else {
            $expandable = true;
        }

        if ($id == false) {
            $id = uniqid();
        } else {
            // Needs to be cleaned, we use it for the input id.
            $id = clean_param($id, PARAM_ALPHANUMEXT);
        }

        // JS to animate the form.
        $this->page->requires->js_call_amd('theme_adaptable/search-input',
            'init', array('data' => array('id' => $id, 'expandable' => $expandable)));

        $searchicon = html_writer::tag('div', $this->pix_icon('a/search', get_string('search', 'search'), 'moodle'),
            array('role' => 'button', 'tabindex' => 0));
        $formclass = 'search-input-form';
        if (!$expandable) {
            $formclass .= ' expanded';
        }
        $formattrs = array('class' => $formclass, 'action' => $CFG->wwwroot . '/search/index.php');
        $inputattrs = array('type' => 'text', 'name' => 'q', 'placeholder' => get_string('search', 'search'),
            'size' => 13, 'tabindex' => -1, 'id' => 'id_q_' . $id, 'class' => 'form-control');

        $contents = html_writer::tag('label', get_string('enteryoursearchquery', 'search'),
            array('for' => 'id_q_' . $id, 'class' => 'accesshide')) . html_writer::tag('input', '', $inputattrs);
        if ($this->page->context && $this->page->context->contextlevel !== CONTEXT_SYSTEM) {
            $contents .= html_writer::empty_tag('input', ['type' => 'hidden',
                'name' => 'context', 'value' => $this->page->context->id]);
        }
        $searchinput = html_writer::tag('form', $contents, $formattrs);

        $wrapperclass = 'search-input-wrapper nav-link';
        if (!$expandable) {
            $wrapperclass .= ' expanded expandable';
        }

        return html_writer::tag('div', $searchicon . $searchinput, array('class' => $wrapperclass, 'id' => $id));
    }
}
