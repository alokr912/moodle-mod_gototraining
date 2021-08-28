<?php

/**
 * GoToWebinar module local library file
 *
 * @package mod_gototraining
 * @copyright 2017 Alok Kumar Rai <alokr.mail@gmail.com,alokkumarrai@outlook.in>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

function createGoToWebibnar($gototraining) {
    global $USER, $DB, $CFG;
    require_once $CFG->dirroot . '/mod/gototraining/classes/gotooauth.class.php';
     $goToOauth = new GoToOAuth();
     $config = get_config('mod_gototraining');
     if(!isset( $config->organizer_key) || empty($config->organizer_key)){
         print_error("Incomplete GoToWebinar setup");
     }
 
    $attributes = array();
    $dstoffset = dst_offset_on($gototraining->startdatetime, get_user_timezone());
    $attributes['subject'] = $gototraining->name;
    $attributes['description'] = clean_param($gototraining->intro, PARAM_NOTAGS);

    $times = array();
    $startdate = usergetdate(usertime($gototraining->startdatetime - $dstoffset));
    $timearray = array();
    $timearray['startTime'] = $startdate['year'] . '-' . $startdate['mon'] . '-' . $startdate['mday'] . 'T' . $startdate['hours'] . ':' . $startdate['minutes'] . ':' . $startdate['seconds'] . 'Z';
    $endtdate = usergetdate(usertime($gototraining->enddatetime - $dstoffset));
    $timearray['endTime'] = $endtdate['year'] . '-' . $endtdate['mon'] . '-' . $endtdate['mday'] . 'T' . $endtdate['hours'] . ':' . $endtdate['minutes'] . ':' . $endtdate['seconds'] . 'Z';
    $attributes['times'] = array($timearray);
    $attributes['timeZone'] = get_user_timezone();
    $attributes['type'] = 'single_session';
    $attributes['isPasswordProtected'] = 'false';

    $key = $config->organizer_key;

    $response = $goToOauth->post("/G2W/rest/v2/organizers/{$key}/webinars", $attributes);
   
    if ($response && !empty($response->webinarKey)) {
        return $response->webinarKey;
    }
    return false;
}

function updateGoToWebinar($oldgototraining, $gototraining) {
    global $USER, $DB, $CFG;
     require_once $CFG->dirroot . '/mod/gototraining/classes/gotooauth.class.php';
     $goToOauth = new GoToOAuth();
     $config = get_config(GoToOAuth::PLUGIN_NAME);
  
    $attributes = array();
    $dstoffset = dst_offset_on($gototraining->startdatetime, get_user_timezone());
    $attributes['subject'] = $gototraining->name;
    $attributes['description'] = clean_param($gototraining->intro, PARAM_NOTAGS);
    $attributes['timeZone'] = get_user_timezone();
    $times = array();
    $startdate = usergetdate(usertime($gototraining->startdatetime - $dstoffset));
    $timearray = array();
    $timearray['startTime'] = $startdate['year'] . '-' . $startdate['mon'] . '-' . $startdate['mday'] . 'T' .
        $startdate['hours'] . ':' . $startdate['minutes'] . ':' . $startdate['seconds'] . 'Z';
    $endtdate = usergetdate(usertime($gototraining->enddatetime - $dstoffset));
    $timearray['endTime'] = $endtdate['year'] . '-' . $endtdate['mon'] . '-' . $endtdate['mday'] . 'T' .
        $endtdate['hours'] . ':' . $endtdate['minutes'] . ':' . $endtdate['seconds'] . 'Z';
    $attributes['times'] = array($timearray);
   
       $key = $config->organizer_key;

    $response = $goToOauth->put("/G2W/rest/v2/organizers/{$key}/webinars/{$oldgototraining->webinarkey}", $attributes);
    if ($response) {
        return true;
    }
    return false;
}

function deleteGoToWebinar($gotoid) {
    global $USER, $DB, $CFG;
    require_once $CFG->dirroot . '/mod/gototraining/classes/gotooauth.class.php';
     $goToOauth = new GoToOAuth();
  
    $key = $config->organizer_key;
    $responce = $goToOauth->delete("/G2W/rest/v2/organizers/{$key}/webinars/{$gotoid}");
   
    if ($responce) {
        return true;
    } else {
        return false;
    }
}

function get_gototraining($gototraining) {
    global $USER, $DB, $CFG;
  require_once $CFG->dirroot . '/mod/gototraining/classes/gotooauth.class.php';
     $goToOauth = new GoToOAuth();
       $config = get_config(GoToOAuth::PLUGIN_NAME);
    $context = context_course::instance($gototraining->course);
     $organiser_key = $config->organizer_key;
    
    if (has_capability('mod/gototraining:organiser', $context) OR has_capability('mod/gototraining:presenter', $context)) {
        $coorganisers = $goToOauth->get("/G2W/rest/v2/organizers/{$organiser_key}/webinars/{$gototraining->webinarkey}/coorganizers");

        if ($coorganisers ) {
     
            foreach ($coorganisers as $coorganiser) {
                if ($coorganiser->email == $USER->email) {
                    return $coorganiser->joinLink;
                }
            }
        } else {// No co organiser found , create one
            $attributes = array(array('external' => true, 'organizerKey' => $organiser_key, 'givenName' => fullname($USER), 'email' => $USER->email));
            $response = $goToOauth->post("/G2W/rest/v2/organizers/{$organiser_key}/webinars/{$gototraining->webinarkey}/coorganizers", $attributes);
                    
            if ($response ) {
                $coorganiser = json_decode($response);
               
                return $coorganiser[0]->joinLink;
            }
        }
    }
    // Now register and check registrant
    $registrant = $DB->get_record('gototraining_registrant', array('userid' => $USER->id, 'gototrainingid' => $gototraining->webinarkey));
   
    if ($registrant) {
        return $registrant->joinurl;
    } else {
        $attributes = array();
        $attributes['firstName'] = $USER->firstname;
        $attributes['lastName'] = $USER->lastname;
        $attributes['email'] = $USER->email;
        $attributes['source'] = '';
        $attributes['address'] = '';
        $attributes['city'] = $USER->city;
        $attributes['state'] = '';
        $attributes['zipCode'] = '';
        $attributes['country'] = $USER->country;
        $attributes['phone'] = '';
        $attributes['organization'] = '';
        $attributes['jobTitle'] = '';
        $attributes['questionsAndComments'] = '';
        $attributes['industry'] = '';
        $attributes['numberOfEmployees'] = '';
        $attributes['purchasingTimeFrame'] = '';
        $attributes['purchasingRole'] = '';
        $attributes['responses'] = array(array('questionKey' => 0, 'responseText' => '', 'answerKey' => 0));
        $response = $goToOauth->post("/G2W/rest/v2/organizers/{$organiser_key}/webinars/{$gototraining->webinarkey}/registrants", $attributes);
    
        if (isset($response) && isset($response->registrantKey)  && isset($response->joinUrl)) {
         
   
          
            $gototraining_registrant = new stdClass();
            $gototraining_registrant->course = $gototraining->course;
            $gototraining_registrant->instanceid = '';
            $gototraining_registrant->joinurl = $response->joinUrl;
            $gototraining_registrant->registrantkey = $response->registrantKey;
            $gototraining_registrant->userid = $USER->id;
            $gototraining_registrant->gototrainingid = $gototraining->webinarkey;
            $gototraining_registrant->timecreated = time();
            $gototraining_registrant->timemodified = time();
            $gototraining_registrant->id = $DB->insert_record('gototraining_registrant', $gototraining_registrant);

            return $response->joinUrl;
        }
    }
}

function get_gototraininginfo($gototraining) {
    global $CFG;
    require_once $CFG->dirroot . '/mod/gototraining/lib/OSD.php';
    $config = get_config('gototraining');
    $context = context_course::instance($gototraining->course);
    OSD::setup(trim($config->gototraining_consumer_key), trim($config->consumer_secret));
    OSD::authenticate_with_password(trim($config->gototraining_userid), trim($config->gototraining_password));
    $organiser_key = OSD::$oauth->organizer_key;
}
