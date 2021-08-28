<?php


/**
 * GoToWebinar module  library file
 *
 * @package mod_gototraining
 * @copyright 2017 Alok Kumar Rai <alokr.mail@gmail.com,alokkumarrai@outlook.in>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once $CFG->dirroot . '/calendar/lib.php';

function gototraining_add_instance($data, $mform = null) {

    global $USER, $DB;

    $response = createGoToWebibnar($data);

    if ($response) {
        $data->userid = $USER->id;
        $data->timecreated = time();
        $data->timemodified = time();
        //$data->meetinfo = trim(, '"');
        $data->webinarkey = $response;


        $data->id = $DB->insert_record('gototraining', $data);
    }

    if (!empty($data->id)) {
        // Add event to calendar
        $event = new stdClass();
        $event->name = $data->name;
        $event->description = $data->intro;
        $event->courseid = $data->course;
        $event->groupid = 0;
        $event->userid = 0;
        $event->instance = $data->id;
        $event->eventtype = 'course';
        $event->timestart = $data->startdatetime;
        $event->timeduration = $data->enddatetime - $data->startdatetime;
        $event->visible = 1;
        $event->modulename = 'gototraining';
        calendar_event::create($event);

        $event = \mod_gototraining\event\gototraining_created::create(array(
            'objectid' => $data->id,
            'context' => context_module::instance($data->coursemodule),
            'other' => array('modulename' => $data->name, 'startdatetime' => $data->startdatetime),
        ));
        $event->trigger();
        return $data->id;
    } else {
        return FALSE;
    }


}

/**
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function gototraining_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_GROUPMEMBERSONLY:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return false;
        default:
            return null;
    }
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function gototraining_update_instance($gototraining) {
    global $DB;
    if (!($oldgototraining = $DB->get_record('gototraining', array('id' => $gototraining->instance)))) {
        return false;
    }
    $result = updateGoToWebinar($oldgototraining, $gototraining);
    // $oldgototraining->meetingtype is always empty, set it up like this or add an invisible option to the mod_form
    if ($result) {

        $oldgototraining->name = $gototraining->name;
        $oldgototraining->intro = $gototraining->intro;
        $oldgototraining->startdatetime = $gototraining->startdatetime;
        $oldgototraining->enddatetime = $gototraining->enddatetime;
        $oldgototraining->timemodified = time();
        $DB->update_record('gototraining', $oldgototraining);
        $param = array('courseid' => $gototraining->course, 'instance' => $gototraining->instance,
            'groupid' => 0, 'modulename' => 'gototraining');

        $eventid = $DB->get_field('event', 'id', $param);

        if (!empty($eventid)) {

            $event = new stdClass();
            $event->id = $eventid;
            $event->name = $gototraining->name;
            $event->description = $gototraining->intro;
            $event->courseid = $gototraining->course;
            $event->groupid = 0;
            $event->userid = 0;
            $event->instance = $gototraining->instance;
            $event->eventtype = 'course';
            $event->timestart = $gototraining->startdatetime;
            $event->timeduration = $gototraining->enddatetime - $gototraining->startdatetime;
            $event->visible = 1;
            $event->modulename = 'gototraining';
            $calendarevent = calendar_event::load($eventid);
            $calendarevent->update($event);
        }
    }
    $event = \mod_gototraining\event\gototraining_updated::create(array(
        'objectid' => $gototraining->instance,
        'context' => context_module::instance($gototraining->coursemodule),
        'other' => array('modulename' => $gototraining->name, 'startdatetime' => $gototraining->startdatetime),
    ));
    $event->trigger();
    return $result;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $adobeconnect An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function gototraining_delete_instance($id) {
    global $DB, $CFG;

    $result = false;
    if (!$gototraining = $DB->get_record('gototraining', array('id' => $id))) {
        return false;
    }

    if (!$cm = get_coursemodule_from_instance('gototraining', $id)) {
        return false;
    }
    $context = context_module::instance($cm->id);
    if ($gototraining->meetingtype == 'gotomeeting') {
        if (deleteGoToMeeting($gototraining->gotoid)) {
            $params = array('id' => $gototraining->id);
            $result = $DB->delete_records('gototraining', $params);
        }
    } else if ($gototraining->meetingtype == 'gototraining') {
        if (deleteGoToWebinar($gototraining->gotoid)) {
            $params = array('id' => $gototraining->id);
            $result = $DB->delete_records('gototraining', $params);
        }
    } else if ($gototraining->meetingtype == 'gototraining') {
        if (deleteGoToTraining((int)$gototraining->gotoid)) {
            $params = array('id' => $gototraining->id);
            $result = $DB->delete_records('gototraining', $params);
        }
    }
    // Delete calendar  event
    $param = array('courseid' => $gototraining->course, 'instance' => $gototraining->id,
        'groupid' => 0, 'modulename' => 'gototraining');

    $eventid = $DB->get_field('event', 'id', $param);
    if ($eventid) {
        $calendarevent = calendar_event::load($eventid);
        $calendarevent->delete();
    }

    $event = \mod_gototraining\event\gototraining_deleted::create(array(
        'objectid' => $id,
        'context' => $context,
        'other' => array('modulename' => $gototraining->name, 'startdatetime' => $gototraining->startdatetime),
    ));


    $event->trigger();


    return $result;
}

/*
 *
 *
 *
 */

function gototraining_get_completion_state($course, $cm, $userid, $type) {
    global $CFG, $DB;
    $result = $type;
    if (!($gototraining = $DB->get_record('gototraining', array('id' => $cm->instance)))) {
        throw new Exception("Can't find GoToLMS {$cm->instance}");
    } // as of now it is not implemented will implement it soon
    /* if ($gototraining->completionparticipation && $gototraining->completionparticipation > 0 && $gototraining->completionparticipation <= 100) {
      if ($gototraining->meetingtype == 'gototraining') {
      $config = get_config('gototraining');
      OSD::setup(trim($config->gototraining_consumer_key));
      OSD::authenticate_with_password(trim($config->gototraining_userid), trim($config->gototraining_password));
      } else if ($gototraining->meetingtype == 'gototraining') {
      $config = get_config('gototraining');
      OSD::setup(trim($config->gototraining_consumer_key));
      OSD::authenticate_with_password(trim($config->gototraining_userid), trim($config->gototraining_password));
      }
      } */
    return true;
}
