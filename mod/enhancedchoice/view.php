<?php

require_once("../../config.php");
require_once("lib.php");
require_once($CFG->libdir . '/completionlib.php');

$id         = required_param('id', PARAM_INT);                 // Course Module ID
$action     = optional_param('action', '', PARAM_ALPHANUMEXT);
$attemptids = optional_param_array('attemptid', array(), PARAM_INT); // Get array of responses to delete or modify.
$userids    = optional_param_array('userid', array(), PARAM_INT); // Get array of users whose enhancedchoices need to be modified.
$notify     = optional_param('notify', '', PARAM_ALPHA);

$url = new moodle_url('/mod/enhancedchoice/view.php', array('id'=>$id));
if ($action !== '') {
    $url->param('action', $action);
}
$PAGE->set_url($url);

if (! $cm = get_coursemodule_from_id('enhancedchoice', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}

require_course_login($course, false, $cm);

if (!$enhancedchoice = enhancedchoice_get_enhancedchoice($cm->instance)) {
    print_error('invalidcoursemodule');
}

$strenhancedchoice = get_string('modulename', 'enhancedchoice');
$strenhancedchoices = get_string('modulenameplural', 'enhancedchoice');

$context = context_module::instance($cm->id);

list($enhancedchoiceavailable, $warnings) = enhancedchoice_get_availability_status($enhancedchoice);

if ($action == 'delenhancedchoice' and confirm_sesskey() and is_enrolled($context, NULL, 'mod/enhancedchoice:choose') and $enhancedchoice->allowupdate
        and $enhancedchoiceavailable) {
    $answercount = $DB->count_records('enhancedchoice_answers', array('enhancedchoiceid' => $enhancedchoice->id, 'userid' => $USER->id));
    if ($answercount > 0) {
        $enhancedchoiceanswers = $DB->get_records('enhancedchoice_answers', array('enhancedchoiceid' => $enhancedchoice->id, 'userid' => $USER->id),
            '', 'id');
        $todelete = array_keys($enhancedchoiceanswers);
        enhancedchoice_delete_responses($todelete, $enhancedchoice, $cm, $course);
        redirect("view.php?id=$cm->id");
    }
}

$PAGE->set_title($enhancedchoice->name);
$PAGE->set_heading($course->fullname);

/// Submit any new data if there is any
if (data_submitted() && !empty($action) && confirm_sesskey()) {
    $timenow = time();
    if (has_capability('mod/enhancedchoice:deleteresponses', $context)) {
        if ($action === 'delete') {
            // Some responses need to be deleted.
            enhancedchoice_delete_responses($attemptids, $enhancedchoice, $cm, $course);
            redirect("view.php?id=$cm->id");
        }
        if (preg_match('/^choose_(\d+)$/', $action, $actionmatch)) {
            // Modify responses of other users.
            $newoptionid = (int)$actionmatch[1];
            enhancedchoice_modify_responses($userids, $attemptids, $newoptionid, $enhancedchoice, $cm, $course);
            redirect("view.php?id=$cm->id");
        }
    }

    // Redirection after all POSTs breaks block editing, we need to be more specific!
    if ($enhancedchoice->allowmultiple) {
        $answer = optional_param_array('answer', array(), PARAM_INT);
    } else {
        $answer = optional_param('answer', '', PARAM_INT);
    }

    if (!$enhancedchoiceavailable) {
        $reason = current(array_keys($warnings));
        throw new moodle_exception($reason, 'enhancedchoice', '', $warnings[$reason]);
    }

    if ($answer && is_enrolled($context, null, 'mod/enhancedchoice:choose')) {
        enhancedchoice_user_submit_response($answer, $enhancedchoice, $USER->id, $course, $cm);
        redirect(new moodle_url('/mod/enhancedchoice/view.php',
            array('id' => $cm->id, 'notify' => 'enhancedchoicesaved', 'sesskey' => sesskey())));
    } else if (empty($answer) and $action === 'makeenhancedchoice') {
        // We cannot use the 'makeenhancedchoice' alone because there might be some legacy renderers without it,
        // outdated renderers will not get the 'mustchoose' message - bad luck.
        redirect(new moodle_url('/mod/enhancedchoice/view.php',
            array('id' => $cm->id, 'notify' => 'mustchooseone', 'sesskey' => sesskey())));
    }
}

// Completion and trigger events.
enhancedchoice_view($enhancedchoice, $course, $cm, $context);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($enhancedchoice->name), 2, null);

if ($notify and confirm_sesskey()) {
    if ($notify === 'enhancedchoicesaved') {
        echo $OUTPUT->notification(get_string('enhancedchoicesaved', 'enhancedchoice'), 'notifysuccess');
    } else if ($notify === 'mustchooseone') {
        echo $OUTPUT->notification(get_string('mustchooseone', 'enhancedchoice'), 'notifyproblem');
    }
}

/// Display the enhancedchoice and possibly results
$eventdata = array();
$eventdata['objectid'] = $enhancedchoice->id;
$eventdata['context'] = $context;

/// Check to see if groups are being used in this enhancedchoice
$groupmode = groups_get_activity_groupmode($cm);

if ($groupmode) {
    groups_get_activity_group($cm, true);
    groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/enhancedchoice/view.php?id='.$id);
}

// Check if we want to include responses from inactive users.
$onlyactive = $enhancedchoice->includeinactive ? false : true;

$allresponses = enhancedchoice_get_response_data($enhancedchoice, $cm, $groupmode, $onlyactive);   // Big function, approx 6 SQL calls per user.


if (has_capability('mod/enhancedchoice:readresponses', $context)) {
    enhancedchoice_show_reportlink($allresponses, $cm);
}

echo '<div class="clearer"></div>';

if ($enhancedchoice->intro) {
    echo $OUTPUT->box(format_module_intro('enhancedchoice', $enhancedchoice, $cm->id), 'generalbox', 'intro');
}

$timenow = time();
$current = enhancedchoice_get_my_response($enhancedchoice);
//if user has already made a selection, and they are not allowed to update it or if enhancedchoice is not open, show their selected answer.
if (isloggedin() && (!empty($current)) &&
    (empty($enhancedchoice->allowupdate) || ($timenow > $enhancedchoice->timeclose)) ) {
    $enhancedchoicetexts = array();
    foreach ($current as $c) {
        $enhancedchoicetexts[] = format_string(enhancedchoice_get_option_text($enhancedchoice, $c->optionid));
    }
    echo $OUTPUT->box(get_string("yourselection", "enhancedchoice", userdate($enhancedchoice->timeopen)).": ".implode('; ', $enhancedchoicetexts), 'generalbox', 'yourselection');
}

/// Print the form
$enhancedchoiceopen = true;
if ((!empty($enhancedchoice->timeopen)) && ($enhancedchoice->timeopen > $timenow)) {
    if ($enhancedchoice->showpreview) {
        echo $OUTPUT->box(get_string('previewonly', 'enhancedchoice', userdate($enhancedchoice->timeopen)), 'generalbox alert');
    } else {
        echo $OUTPUT->box(get_string("notopenyet", "enhancedchoice", userdate($enhancedchoice->timeopen)), "generalbox notopenyet");
        echo $OUTPUT->footer();
        exit;
    }
} else if ((!empty($enhancedchoice->timeclose)) && ($timenow > $enhancedchoice->timeclose)) {
    echo $OUTPUT->box(get_string("expired", "enhancedchoice", userdate($enhancedchoice->timeclose)), "generalbox expired");
    $enhancedchoiceopen = false;
}

if ( (!$current or $enhancedchoice->allowupdate) and $enhancedchoiceopen and is_enrolled($context, NULL, 'mod/enhancedchoice:choose')) {

    // Show information on how the results will be published to students.
    $publishinfo = null;
    switch ($enhancedchoice->showresults) {
        case ENHANCEDCHOICE_SHOWRESULTS_NOT:
            $publishinfo = get_string('publishinfonever', 'enhancedchoice');
            break;

        case ENHANCEDCHOICE_SHOWRESULTS_AFTER_ANSWER:
            if ($enhancedchoice->publish == ENHANCEDCHOICE_PUBLISH_ANONYMOUS) {
                $publishinfo = get_string('publishinfoanonafter', 'enhancedchoice');
            } else {
                $publishinfo = get_string('publishinfofullafter', 'enhancedchoice');
            }
            break;

        case ENHANCEDCHOICE_SHOWRESULTS_AFTER_CLOSE:
            if ($enhancedchoice->publish == ENHANCEDCHOICE_PUBLISH_ANONYMOUS) {
                $publishinfo = get_string('publishinfoanonclose', 'enhancedchoice');
            } else {
                $publishinfo = get_string('publishinfofullclose', 'enhancedchoice');
            }
            break;

        default:
            // No need to inform the user in the case of ENHANCEDCHOICE_SHOWRESULTS_ALWAYS since it's already obvious that the results are
            // being published.
            break;
    }

    // Show info if necessary.
    if (!empty($publishinfo)) {
        echo $OUTPUT->notification($publishinfo, 'info');
    }

    // They haven't made their enhancedchoice yet or updates allowed and enhancedchoice is open.
    $options = enhancedchoice_prepare_options($enhancedchoice, $USER, $cm, $allresponses);
    $renderer = $PAGE->get_renderer('mod_enhancedchoice');
    echo $renderer->display_options($options, $cm->id, $enhancedchoice->display, $enhancedchoice->allowmultiple);
    $enhancedchoiceformshown = true;
} else {
    $enhancedchoiceformshown = false;
}

if (!$enhancedchoiceformshown) {
    $sitecontext = context_system::instance();

    if (isguestuser()) {
        // Guest account
        echo $OUTPUT->confirm(get_string('noguestchoose', 'enhancedchoice').'<br /><br />'.get_string('liketologin'),
                     get_login_url(), new moodle_url('/course/view.php', array('id'=>$course->id)));
    } else if (!is_enrolled($context)) {
        // Only people enrolled can make a enhancedchoice
        $SESSION->wantsurl = qualified_me();
        $SESSION->enrolcancel = get_local_referer(false);

        $coursecontext = context_course::instance($course->id);
        $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));

        echo $OUTPUT->box_start('generalbox', 'notice');
        echo '<p align="center">'. get_string('notenrolledchoose', 'enhancedchoice') .'</p>';
        echo $OUTPUT->container_start('continuebutton');
        echo $OUTPUT->single_button(new moodle_url('/enrol/index.php?', array('id'=>$course->id)), get_string('enrolme', 'core_enrol', $courseshortname));
        echo $OUTPUT->container_end();
        echo $OUTPUT->box_end();

    }
}

// print the results at the bottom of the screen
if (enhancedchoice_can_view_results($enhancedchoice, $current, $enhancedchoiceopen)) {
    $results = prepare_enhancedchoice_show_results($enhancedchoice, $course, $cm, $allresponses);
    $renderer = $PAGE->get_renderer('mod_enhancedchoice');
    $resultstable = $renderer->display_result($results);
    echo $OUTPUT->box($resultstable);

} else if (!$enhancedchoiceformshown) {
    echo $OUTPUT->box(get_string('noresultsviewable', 'enhancedchoice'));
}

echo $OUTPUT->footer();
