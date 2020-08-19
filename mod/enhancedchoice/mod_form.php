<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_enhancedchoice_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $ENHANCEDCHOICE_SHOWRESULTS, $ENHANCEDCHOICE_PUBLISH, $ENHANCEDCHOICE_DISPLAY, $DB;

        $mform    =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('enhancedchoicename', 'enhancedchoice'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('description', 'enhancedchoice'));

        $mform->addElement('select', 'display', get_string("displaymode","enhancedchoice"), $ENHANCEDCHOICE_DISPLAY);

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'optionhdr', get_string('options', 'enhancedchoice'));

        $mform->addElement('selectyesno', 'allowupdate', get_string("allowupdate", "enhancedchoice"));

        $mform->addElement('selectyesno', 'allowmultiple', get_string('allowmultiple', 'enhancedchoice'));
        if ($this->_instance) {
            if ($DB->count_records('enhancedchoice_answers', array('enhancedchoiceid' => $this->_instance)) > 0) {
                // Prevent user from toggeling the number of allowed answers once there are submissions.
                $mform->freeze('allowmultiple');
            }
        }

        $mform->addElement('selectyesno', 'limitanswers', get_string('limitanswers', 'enhancedchoice'));
        $mform->addHelpButton('limitanswers', 'limitanswers', 'enhancedchoice');

        $repeatarray = array();
        // $repeatarray[] = $mform->createElement('text', 'option', get_string('optionno', 'enhancedchoice'));
        $repeatarray[] = $mform->createElement('editor', 'option', get_string('option','enhancedchoice'), null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$this->context));
        $repeatarray[] = $mform->createElement('text', 'limit', get_string('limitno', 'enhancedchoice'));
        $repeatarray[] = $mform->createElement('hidden', 'optionid', 0);

        if ($this->_instance){
            $repeatno = $DB->count_records('enhancedchoice_options', array('enhancedchoiceid'=>$this->_instance));
            $repeatno += 2;
        } else {
            $repeatno = 5;
        }

        $repeateloptions = array();
        $repeateloptions['limit']['default'] = 0;
        $repeateloptions['limit']['hideif'] = array('limitanswers', 'eq', 0);
        $repeateloptions['limit']['rule'] = 'numeric';
        $repeateloptions['limit']['type'] = PARAM_INT;

        $repeateloptions['option']['helpbutton'] = array('enhancedchoiceoptions', 'enhancedchoice');
        $mform->setType('option', PARAM_CLEANHTML);

        $mform->setType('optionid', PARAM_INT);

        $this->repeat_elements($repeatarray, $repeatno,
                    $repeateloptions, 'option_repeats', 'option_add_fields', 3, null, true);

        // Make the first option required
        if ($mform->elementExists('option[0]')) {
            $mform->addRule('option[0]', get_string('atleastoneoption', 'enhancedchoice'), 'required', null, 'client');
        }

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'availabilityhdr', get_string('availability'));
        $mform->addElement('date_time_selector', 'timeopen', get_string("enhancedchoiceopen", "enhancedchoice"),
            array('optional' => true));

        $mform->addElement('date_time_selector', 'timeclose', get_string("enhancedchoiceclose", "enhancedchoice"),
            array('optional' => true));

        $mform->addElement('advcheckbox', 'showpreview', get_string('showpreview', 'enhancedchoice'));
        $mform->addHelpButton('showpreview', 'showpreview', 'enhancedchoice');
        $mform->disabledIf('showpreview', 'timeopen[enabled]');

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'resultshdr', get_string('results', 'enhancedchoice'));

        $mform->addElement('select', 'showresults', get_string("publish", "enhancedchoice"), $ENHANCEDCHOICE_SHOWRESULTS);

        $mform->addElement('select', 'publish', get_string("privacy", "enhancedchoice"), $ENHANCEDCHOICE_PUBLISH);
        $mform->hideIf('publish', 'showresults', 'eq', 0);

        $mform->addElement('selectyesno', 'showunanswered', get_string("showunanswered", "enhancedchoice"));

        $mform->addElement('selectyesno', 'includeinactive', get_string('includeinactive', 'enhancedchoice'));
        $mform->setDefault('includeinactive', 0);

//-------------------------------------------------------------------------------
        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values){
        global $DB;
        if (!empty($this->_instance) && ($options = $DB->get_records_menu('enhancedchoice_options',array('enhancedchoiceid'=>$this->_instance), 'id', 'id,text'))
               && ($options2 = $DB->get_records_menu('enhancedchoice_options', array('enhancedchoiceid'=>$this->_instance), 'id', 'id,maxanswers')) ) {
            $enhancedchoiceids=array_keys($options);
            $options=array_values($options);
            $options2=array_values($options2);

            foreach (array_keys($options) as $key){
                $default_values['option['.$key.']'] = $options[$key];
                $default_values['limit['.$key.']'] = $options2[$key];
                $default_values['optionid['.$key.']'] = $enhancedchoiceids[$key];
            }

        }

    }

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        // Set up completion section even if checkbox is not ticked
        if (!empty($data->completionunlocked)) {
            if (empty($data->completionsubmit)) {
                $data->completionsubmit = 0;
            }
        }
    }

    /**
     * Enforce validation rules here
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array
     **/
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Check open and close times are consistent.
        if ($data['timeopen'] && $data['timeclose'] &&
                $data['timeclose'] < $data['timeopen']) {
            $errors['timeclose'] = get_string('closebeforeopen', 'enhancedchoice');
        }

        return $errors;
    }

    function add_completion_rules() {
        $mform =& $this->_form;

        $mform->addElement('checkbox', 'completionsubmit', '', get_string('completionsubmit', 'enhancedchoice'));
        // Enable this completion rule by default.
        $mform->setDefault('completionsubmit', 1);
        return array('completionsubmit');
    }

    function completion_rule_enabled($data) {
        return !empty($data['completionsubmit']);
    }
}

