<?php

/**
 * GoToWebinar module view file
 *
 * @package mod_gototraining
 * @copyright 2017 Alok Kumar Rai <alokr.mail@gmail.com,alokkumarrai@outlook.in>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_gototraining_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
        $gototraining = new backup_nested_element('gototraining', array('id'),
            array('course',
                'name',
                'intro',
                'introformat',
                'meetingtype',
                'userid',
                'meetinginfo',
                'gotoid',
                'startdatetime',
                'enddatetime',
                'completionparticipation',
                'meetingpublic',
                'timecreated',
                'timemodified'));

        $gototraining_registrants = new backup_nested_element('gototraining_registrants', array('id'), array('course', 'cmid', 'email', 'status', 'joinurl',
            'confirmationurl', 'registrantkey', 'userid',
            'gotoid', 'timecreated', 'timemodified'));
        $gototraining->add_child($gototraining_registrants);
        $gototraining->set_source_table('gototraining', array('id' => backup::VAR_ACTIVITYID));
        $gototraining_registrants->set_source_sql('SELECT * FROM {gototraining_registrant}  WHERE gototrainingid = ?', array(backup::VAR_PARENTID));
        return $this->prepare_activity_structure($gototraining);
    }

}
