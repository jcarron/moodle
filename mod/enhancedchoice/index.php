<?php

    require_once("../../config.php");
    require_once("lib.php");

    $id = required_param('id',PARAM_INT);   // course

    $PAGE->set_url('/mod/enhancedchoice/index.php', array('id'=>$id));

    if (!$course = $DB->get_record('course', array('id'=>$id))) {
        print_error('invalidcourseid');
    }

    require_course_login($course);
    $PAGE->set_pagelayout('incourse');

    $eventdata = array('context' => context_course::instance($id));
    $event = \mod_enhancedchoice\event\course_module_instance_list_viewed::create($eventdata);
    $event->add_record_snapshot('course', $course);
    $event->trigger();

    $strenhancedchoice = get_string("modulename", "enhancedchoice");
    $strenhancedchoices = get_string("modulenameplural", "enhancedchoice");
    $PAGE->set_title($strenhancedchoices);
    $PAGE->set_heading($course->fullname);
    $PAGE->navbar->add($strenhancedchoices);
    echo $OUTPUT->header();

    if (! $enhancedchoices = get_all_instances_in_course("enhancedchoice", $course)) {
        notice(get_string('thereareno', 'moodle', $strenhancedchoices), "../../course/view.php?id=$course->id");
    }

    $usesections = course_format_uses_sections($course->format);

    $sql = "SELECT cha.*
              FROM {enhancedchoice} ch, {enhancedchoice_answers} cha
             WHERE cha.enhancedchoiceid = ch.id AND
                   ch.course = ? AND cha.userid = ?";

    $answers = array () ;
    if (isloggedin() and !isguestuser() and $allanswers = $DB->get_records_sql($sql, array($course->id, $USER->id))) {
        foreach ($allanswers as $aa) {
            $answers[$aa->enhancedchoiceid] = $aa;
        }
        unset($allanswers);
    }


    $timenow = time();

    $table = new html_table();

    if ($usesections) {
        $strsectionname = get_string('sectionname', 'format_'.$course->format);
        $table->head  = array ($strsectionname, get_string("question"), get_string("answer"));
        $table->align = array ("center", "left", "left");
    } else {
        $table->head  = array (get_string("question"), get_string("answer"));
        $table->align = array ("left", "left");
    }

    $currentsection = "";

    foreach ($enhancedchoices as $enhancedchoice) {
        if (!empty($answers[$enhancedchoice->id])) {
            $answer = $answers[$enhancedchoice->id];
        } else {
            $answer = "";
        }
        if (!empty($answer->optionid)) {
            $aa = format_string(enhancedchoice_get_option_text($enhancedchoice, $answer->optionid));
        } else {
            $aa = "";
        }
        if ($usesections) {
            $printsection = "";
            if ($enhancedchoice->section !== $currentsection) {
                if ($enhancedchoice->section) {
                    $printsection = get_section_name($course, $enhancedchoice->section);
                }
                if ($currentsection !== "") {
                    $table->data[] = 'hr';
                }
                $currentsection = $enhancedchoice->section;
            }
        }

        //Calculate the href
        if (!$enhancedchoice->visible) {
            //Show dimmed if the mod is hidden
            $tt_href = "<a class=\"dimmed\" href=\"view.php?id=$enhancedchoice->coursemodule\">".format_string($enhancedchoice->name,true)."</a>";
        } else {
            //Show normal if the mod is visible
            $tt_href = "<a href=\"view.php?id=$enhancedchoice->coursemodule\">".format_string($enhancedchoice->name,true)."</a>";
        }
        if ($usesections) {
            $table->data[] = array ($printsection, $tt_href, $aa);
        } else {
            $table->data[] = array ($tt_href, $aa);
        }
    }
    echo "<br />";
    echo html_writer::table($table);

    echo $OUTPUT->footer();


