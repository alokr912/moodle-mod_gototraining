<?php

/**
 * GoToWebinar module view file
 *
 * @package mod_gototraining
 * @copyright 2017 Alok Kumar Rai <alokr.mail@gmail.com,alokkumarrai@outlook.in>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class restore_gototraining_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = false;
        //        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('gototraining', '/activity/gototraining');
        // $paths[] = new restore_path_element('gototraining_meeting_group', '/activity/gototraining/meeting_groups/meeting_group');
        //        if ($userinfo) {
        //            $paths[] = new restore_path_element('survey_answer', '/activity/survey/answers/answer');
        //            $paths[] = new restore_path_element('survey_analys', '/activity/survey/analysis/analys');
        //        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_gototraining($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);

        // insert the gototraining record
        $newitemid = $DB->insert_record('gototraining', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_gototraining_registrant($data) {
        global $DB;

        $data = (object)$data;
        $data->instanceid = $this->get_new_parentid('gototraining');
        $data->groupid = $this->get_mappingid('instanceid', $data->instanceid);

        $newitemid = $DB->insert_record('gototraining_registrant', $data);

        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }

    protected function after_execute() {
        // Add survey related files, no need to match by itemname (just internally handled context)
        // $this->add_related_files('mod_gototraining', 'intro', null);
    }
}