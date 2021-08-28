<?php

/**
 * Global Settings file
 *
 * @package mod_gototraining
 * @copyright 2017 Alok Kumar Rai <alokr.mail@gmail.com,alokkumarrai@outlook.in>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {


    //----------------  Consumer Key Settings -----------------------------------------//
    $name = 'gototraining/consumer_key';
    $visiblename = get_string('gtw_consumer_key', 'gototraining');
    $description = get_string('gtw_consumer_key_desc', 'gototraining');
    $settings->add(new admin_setting_configtext($name, $visiblename, $description, '', PARAM_RAW, 50));

    //-----------------Consumer secret settings ----------------------------------------
    $name = 'gototraining/consumer_secret';
    $visiblename = get_string('gtw_consumer_secret', 'gototraining');
    $description = get_string('gtw_consumer_secret_desc', 'gototraining');
    $settings->add(new admin_setting_configtext($name, $visiblename, $description, '', PARAM_RAW, 50));

    //---------------------Userid(License userid) General settings -----------------------------------------------------------------------------------
    $name = 'gototraining/userid';
    $visiblename = get_string('gtw_userid', 'gototraining');
    $description = get_string('gtw_userid_desc', 'gototraining');
    //$settings->add(new admin_setting_configtext($name, $visiblename, $description, '', PARAM_RAW, 50));

    //---------------------Password settings -----------------------------------------------------------------------------------
    $name = 'gototraining/password';
    $visiblename = get_string('gtw_password', 'gototraining');
    $description = get_string('gtw_password_desc', 'gototraining');
    //$settings->add(new admin_setting_configpasswordunmask($name, $visiblename, $description, '', PARAM_RAW, 50));


    $url = $CFG->wwwroot . '/mod/gototraining/setup.php';
    $url = htmlentities($url, ENT_COMPAT, 'UTF-8');
    $options = 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=0,width=700,height=300';
    $str = '<center><input type="button" onclick="window.open(\'' . $url . '\', \'\', \'' . $options . '\');" value="' .
        get_string('setup', 'gototraining') . '" /></center>';
    $settings->add(new admin_setting_heading('gototraining_setup', '', $str));
}

