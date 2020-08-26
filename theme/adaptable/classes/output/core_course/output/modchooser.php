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
 * The modchooser renderable.
 *
 * @package    theme_adaptable
 * @copyright  2015-2019 Jeremy Hopkins (Coventry University)
 * @copyright  2015-2019 Fernando Acedo (3-bits.com)
 * @copyright  2017-2019 Manoj Solanki (Coventry University)
 * @author     G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace theme_adaptable\output\core_course\output;
defined('MOODLE_INTERNAL') || die();

use core_course\output\modchooser_item;
use core\output\chooser_section;
use context_course;
use lang_string;
use moodle_url;
use pix_icon;
use renderer_base;
use stdClass;

/**
 * The modchooser renderable class.
 *
 * @package    theme_adaptable
 * @author     G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @copyright Copyright (c) 2017 Manoj Solanki (Coventry University)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class modchooser extends \core\output\chooser {

    /** @var stdClass The course. */
    public $course;

    /**
     * Constructor.
     *
     * @param stdClass $course The course.
     * @param stdClass[] $modules The modules.
     */
    public function __construct(stdClass $course, array $modules) {
        $this->course = $course;
        $sections = [];
        $context = context_course::instance($course->id);

        // Common modules.
        $commonmodnames = get_config('theme_adaptable', 'commonlyusedar');
        if (!empty($commonmodnames)) {
            if (count($modules)) {
                $modnames = explode(',', $commonmodnames);
                $commonmods = array();
                foreach ($modnames as $modname) {
                    $modname = trim($modname);
                    if (array_key_exists($modname, $modules)) {
                        $commonmods[$modname] = $modules[$modname];
                        unset($modules[$modname]);
                    }
                }
                if (count($commonmods)) {
                    $sections[] = new chooser_section('commonlyusedar', new lang_string('commonlyusedartitle', 'theme_adaptable'),
                        array_map(function($commonmod) use ($context) {
                            return new modchooser_item($commonmod, $context);
                        }, $commonmods)
                    );
                }
            }
        }

         // Activities.
        $activities = array_filter($modules, function($mod) {
            return ($mod->archetype !== MOD_ARCHETYPE_RESOURCE && $mod->archetype !== MOD_ARCHETYPE_SYSTEM);
        });
        // Add remaining activities.
        if (count($activities)) {
            $sections[] = new chooser_section('activities', new lang_string('activities'),
                array_map(function($module) use ($context) {
                    return new modchooser_item($module, $context);
                }, $activities)
            );
        }

         // Resources.
        $resources = array_filter($modules, function($mod) {
            return ($mod->archetype === MOD_ARCHETYPE_RESOURCE);
        });
        // Add remaining resources.
        if (count($resources)) {
            $sections[] = new chooser_section('resources', new lang_string('resources'),
                array_map(function($module) use ($context) {
                    return new modchooser_item($module, $context);
                }, $resources)
            );
        }

        $actionurl = new moodle_url('/course/jumpto.php');
        $title = new lang_string('addresourceoractivity');
        parent::__construct($actionurl, $title, $sections, 'jumplink');

        $this->set_instructions(new lang_string('selectmoduletoviewhelp'));
        $this->add_param('course', $course->id);
    }

    /**
     * Export for template.
     *
     * @param renderer_base  $output The renderer.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = parent::export_for_template($output);
        $data->courseid = $this->course->id;
        return $data;
    }

}
