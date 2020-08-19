<?php

    require_once("../../config.php");
    require_once("lib.php");

    $id         = required_param('id', PARAM_INT);   //moduleid
    $download   = optional_param('download', '', PARAM_ALPHA);
    $action     = optional_param('action', '', PARAM_ALPHANUMEXT);
    $attemptids = optional_param_array('attemptid', array(), PARAM_INT); // Get array of responses to delete or modify.
    $userids    = optional_param_array('userid', array(), PARAM_INT); // Get array of users whose enhancedchoices need to be modified.

    $url = new moodle_url('/mod/enhancedchoice/report.php', array('id'=>$id));
    if ($download !== '') {
        $url->param('download', $download);
    }
    if ($action !== '') {
        $url->param('action', $action);
    }
    $PAGE->set_url($url);

    if (! $cm = get_coursemodule_from_id('enhancedchoice', $id)) {
        print_error("invalidcoursemodule");
    }

    if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
        print_error("coursemisconf");
    }

    require_login($course, false, $cm);

    $context = context_module::instance($cm->id);

    require_capability('mod/enhancedchoice:readresponses', $context);

    if (!$enhancedchoice = enhancedchoice_get_enhancedchoice($cm->instance)) {
        print_error('invalidcoursemodule');
    }

    $strenhancedchoice = get_string("modulename", "enhancedchoice");
    $strenhancedchoices = get_string("modulenameplural", "enhancedchoice");
    $strresponses = get_string("responses", "enhancedchoice");

    $eventdata = array();
    $eventdata['objectid'] = $enhancedchoice->id;
    $eventdata['context'] = $context;
    $eventdata['courseid'] = $course->id;
    $eventdata['other']['content'] = 'enhancedchoicereportcontentviewed';

    $event = \mod_enhancedchoice\event\report_viewed::create($eventdata);
    $event->trigger();

    if (data_submitted() && has_capability('mod/enhancedchoice:deleteresponses', $context) && confirm_sesskey()) {
        if ($action === 'delete') {
            // Delete responses of other users.
            enhancedchoice_delete_responses($attemptids, $enhancedchoice, $cm, $course);
            redirect("report.php?id=$cm->id");
        }
        if (preg_match('/^choose_(\d+)$/', $action, $actionmatch)) {
            // Modify responses of other users.
            $newoptionid = (int)$actionmatch[1];
            enhancedchoice_modify_responses($userids, $attemptids, $newoptionid, $enhancedchoice, $cm, $course);
            redirect("report.php?id=$cm->id");
        }
    }

    if (!$download) {
        $PAGE->navbar->add($strresponses);
        $PAGE->set_title(format_string($enhancedchoice->name).": $strresponses");
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->heading(format_string($enhancedchoice->name), 2, null);
        /// Check to see if groups are being used in this enhancedchoice
        $groupmode = groups_get_activity_groupmode($cm);
        if ($groupmode) {
            groups_get_activity_group($cm, true);
            groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/enhancedchoice/report.php?id='.$id);
        }
    } else {
        $groupmode = groups_get_activity_groupmode($cm);

        // Trigger the report downloaded event.
        $eventdata = array();
        $eventdata['context'] = $context;
        $eventdata['courseid'] = $course->id;
        $eventdata['other']['content'] = 'enhancedchoicereportcontentviewed';
        $eventdata['other']['format'] = $download;
        $eventdata['other']['enhancedchoiceid'] = $enhancedchoice->id;
        $event = \mod_enhancedchoice\event\report_downloaded::create($eventdata);
        $event->trigger();

    }

    // Check if we want to include responses from inactive users.
    $onlyactive = $enhancedchoice->includeinactive ? false : true;

    $users = enhancedchoice_get_response_data($enhancedchoice, $cm, $groupmode, $onlyactive);

    $extrafields = get_extra_user_fields($context);

    if ($download == "ods" && has_capability('mod/enhancedchoice:downloadresponses', $context)) {
        require_once("$CFG->libdir/odslib.class.php");

    /// Calculate file name
        $shortname = format_string($course->shortname, true, array('context' => $context));
        $enhancedchoicename = format_string($enhancedchoice->name, true, array('context' => $context));
        $filename = clean_filename("$shortname " . strip_tags($enhancedchoicename)) . '.ods';
    /// Creating a workbook
        $workbook = new MoodleODSWorkbook("-");
    /// Send HTTP headers
        $workbook->send($filename);
    /// Creating the first worksheet
        $myxls = $workbook->add_worksheet($strresponses);

    /// Print names of all the fields
        $i = 0;
        $myxls->write_string(0, $i++, get_string("lastname"));
        $myxls->write_string(0, $i++, get_string("firstname"));

        // Add headers for extra user fields.
        foreach ($extrafields as $field) {
            $myxls->write_string(0, $i++, get_user_field_name($field));
        }

        $myxls->write_string(0, $i++, get_string("group"));
        $myxls->write_string(0, $i++, get_string("enhancedchoice", "enhancedchoice"));

        // Generate the data for the body of the spreadsheet.
        $row = 1;
        if ($users) {
            foreach ($users as $option => $userid) {
                $option_text = enhancedchoice_get_option_text($enhancedchoice, $option);
                foreach ($userid as $user) {
                    $i = 0;
                    $myxls->write_string($row, $i++, $user->lastname);
                    $myxls->write_string($row, $i++, $user->firstname);
                    foreach ($extrafields as $field) {
                        $myxls->write_string($row, $i++, $user->$field);
                    }
                    $ug2 = '';
                    if ($usergrps = groups_get_all_groups($course->id, $user->id)) {
                        foreach ($usergrps as $ug) {
                            $ug2 = $ug2 . $ug->name;
                        }
                    }
                    $myxls->write_string($row, $i++, $ug2);

                    if (isset($option_text)) {
                        $myxls->write_string($row, $i++, format_string($option_text, true));
                    }
                    $row++;
                }
            }
        }
        /// Close the workbook
        $workbook->close();

        exit;
    }

    //print spreadsheet if one is asked for:
    if ($download == "xls" && has_capability('mod/enhancedchoice:downloadresponses', $context)) {
        require_once("$CFG->libdir/excellib.class.php");

    /// Calculate file name
        $shortname = format_string($course->shortname, true, array('context' => $context));
        $enhancedchoicename = format_string($enhancedchoice->name, true, array('context' => $context));
        $filename = clean_filename("$shortname " . strip_tags($enhancedchoicename)) . '.xls';
    /// Creating a workbook
        $workbook = new MoodleExcelWorkbook("-");
    /// Send HTTP headers
        $workbook->send($filename);
    /// Creating the first worksheet
        $myxls = $workbook->add_worksheet($strresponses);

    /// Print names of all the fields
        $i = 0;
        $myxls->write_string(0, $i++, get_string("lastname"));
        $myxls->write_string(0, $i++, get_string("firstname"));

        // Add headers for extra user fields.
        foreach ($extrafields as $field) {
            $myxls->write_string(0, $i++, get_user_field_name($field));
        }

        $myxls->write_string(0, $i++, get_string("group"));
        $myxls->write_string(0, $i++, get_string("enhancedchoice", "enhancedchoice"));

        // Generate the data for the body of the spreadsheet.
        $row = 1;
        if ($users) {
            foreach ($users as $option => $userid) {
                $i = 0;
                $option_text = enhancedchoice_get_option_text($enhancedchoice, $option);
                foreach($userid as $user) {
                    $i = 0;
                    $myxls->write_string($row, $i++, $user->lastname);
                    $myxls->write_string($row, $i++, $user->firstname);
                    foreach ($extrafields as $field) {
                        $myxls->write_string($row, $i++, $user->$field);
                    }
                    $ug2 = '';
                    if ($usergrps = groups_get_all_groups($course->id, $user->id)) {
                        foreach ($usergrps as $ug) {
                            $ug2 = $ug2 . $ug->name;
                        }
                    }
                    $myxls->write_string($row, $i++, $ug2);
                    if (isset($option_text)) {
                        $myxls->write_string($row, $i++, format_string($option_text, true));
                    }
                    $row++;
                }
            }
        }
        /// Close the workbook
        $workbook->close();
        exit;
    }

    // print text file
    if ($download == "txt" && has_capability('mod/enhancedchoice:downloadresponses', $context)) {
        $shortname = format_string($course->shortname, true, array('context' => $context));
        $enhancedchoicename = format_string($enhancedchoice->name, true, array('context' => $context));
        $filename = clean_filename("$shortname " . strip_tags($enhancedchoicename)) . '.txt';

        header("Content-Type: application/download\n");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
        header("Pragma: public");

        /// Print names of all the fields

        echo get_string("lastname") . "\t" . get_string("firstname") . "\t";

        // Add headers for extra user fields.
        foreach ($extrafields as $field) {
            echo get_user_field_name($field) . "\t";
        }

        echo get_string("group"). "\t";
        echo get_string("enhancedchoice","enhancedchoice"). "\n";

        /// generate the data for the body of the spreadsheet
        $i=0;
        if ($users) {
            foreach ($users as $option => $userid) {
                $option_text = enhancedchoice_get_option_text($enhancedchoice, $option);
                foreach($userid as $user) {
                    echo $user->lastname . "\t";
                    echo $user->firstname . "\t";
                    foreach ($extrafields as $field) {
                        echo $user->$field . "\t";
                    }
                    $ug2 = '';
                    if ($usergrps = groups_get_all_groups($course->id, $user->id)) {
                        foreach ($usergrps as $ug) {
                            $ug2 = $ug2. $ug->name;
                        }
                    }
                    echo $ug2. "\t";
                    if (isset($option_text)) {
                        echo format_string($option_text,true);
                    }
                    echo "\n";
                }
            }
        }
        exit;
    }
    $results = prepare_enhancedchoice_show_results($enhancedchoice, $course, $cm, $users);
    $renderer = $PAGE->get_renderer('mod_enhancedchoice');
    echo $renderer->display_result($results, true);

   //now give links for downloading spreadsheets.
    if (!empty($users) && has_capability('mod/enhancedchoice:downloadresponses',$context)) {
        $downloadoptions = array();
        $options = array();
        $options["id"] = "$cm->id";
        $options["download"] = "ods";
        $button =  $OUTPUT->single_button(new moodle_url("report.php", $options), get_string("downloadods"));
        $downloadoptions[] = html_writer::tag('li', $button, array('class' => 'reportoption list-inline-item'));

        $options["download"] = "xls";
        $button = $OUTPUT->single_button(new moodle_url("report.php", $options), get_string("downloadexcel"));
        $downloadoptions[] = html_writer::tag('li', $button, array('class' => 'reportoption list-inline-item'));

        $options["download"] = "txt";
        $button = $OUTPUT->single_button(new moodle_url("report.php", $options), get_string("downloadtext"));
        $downloadoptions[] = html_writer::tag('li', $button, array('class' => 'reportoption list-inline-item'));

        $downloadlist = html_writer::tag('ul', implode('', $downloadoptions), array('class' => 'list-inline inline'));
        $downloadlist .= html_writer::tag('div', '', array('class' => 'clearfloat'));
        echo html_writer::tag('div',$downloadlist, array('class' => 'downloadreport mt-1'));
    }
    echo $OUTPUT->footer();

