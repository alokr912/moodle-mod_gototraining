<?php

/**
 * GoToMeeting module configtest  file
 *
 * @package mod_gototraining
 * @copyright 2017 Alok Kumar Rai <alokr.mail@gmail.com,alokkumarrai@outlook.in>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once('./classes/gotooauth.class.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url($CFG->wwwroot . '/mod/gototraining/configtest.php'));
$PAGE->set_pagelayout('admin');
$PAGE->set_heading('GoToMeeting config test report');
$PAGE->set_title('GoToMeeting config test report');
require_login();

if (!is_siteadmin()) {
    print_error('nopermissions', 'gototraining', '', null);
}

$gototrainingconfig = get_config('gototraining');
$goToAuth = new GoToOAuth();
$status = $goToAuth->getSetupStatus();
if ($status) {
    echo $OUTPUT->header();
    echo html_writer::div('GoToWebinar setup status ', 'alert alert-info');
    echo html_writer::div('GoToWebinar config  organizer email '.$status->email, 'alert alert-success');
    echo html_writer::div('GoToWebinar config organizer firstName '.$status->firstName, 'alert alert-info');
    echo html_writer::div('GoToWebinar config  organizer lastName '. $status->lastName, 'alert alert-info');
    echo html_writer::div('GoToWebinar config  organizer key '.$status->organizer_key, 'alert alert-info');
     echo html_writer::div('GoToWebinar config  account key '.$status->account_key, 'alert alert-info');
  
 
  
    
            
            
    echo $OUTPUT->footer();
} else if (isset($gototrainingconfig->consumer_key) && $gototrainingconfig->consumer_key != '' && isset($gototrainingconfig->consumer_secret) && $gototrainingconfig->consumer_secret != '') {

    $redirect_url = $CFG->wwwroot . '/mod/gototraining/oauthCallback.php';
    $url = GoToOAuth::BASE_URL . "/oauth/v2/authorize?client_id=$gototrainingconfig->consumer_key&response_type=code&redirect_uri=$redirect_url";

    redirect($url);
} else {

    echo $OUTPUT->header();

    echo html_writer::div('GoToMeeting config validation ', 'alert alert-info');

    $consumerKey = trim($gototrainingconfig->consumer_key);
    if (isset($gototrainingconfig->consumer_key) && $gototrainingconfig->consumer_key == '') {


        echo html_writer::div('GoToMeeting consumer key missing', 'alert alert-danger');
    }
    if (isset($gototrainingconfig->consumer_secret) && $gototrainingconfig->consumer_secret == '') {

        echo html_writer::div('GoToMeeting consumer secert missing', 'alert alert-danger');
    }
}