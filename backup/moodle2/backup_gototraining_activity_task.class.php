<?php


/**
 * GoToWebinar module view file
 *
 * @package mod_gototraining
 * @copyright 2017 Alok Kumar Rai <alokr.mail@gmail.com,alokkumarrai@outlook.in>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once $CFG->dirroot . '/mod/gototraining/backup/moodle2/backup_gototraining_stepslib.php';

class backup_gototraining_activity_task extends backup_activity_task {
    protected function define_my_settings() {

    }

    protected function define_my_steps() {
        $this->add_step(new backup_gototraining_activity_structure_step('gototraining_structure', 'gototraining.xml'));
    }

    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of adobeconnect instances
        $search = "/(" . $base . "\/mod\/gototraining\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@GOTOLMS*$2@$', $content);

        // Link to adobeconnect view by moduleid
        $search = "/(" . $base . "\/mod\/GOTOLMS\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@GOTOLMS*$2@$', $content);

        return $content;
    }
}