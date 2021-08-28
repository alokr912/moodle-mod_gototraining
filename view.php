<?php


/**
 * GoToWebinar module view file
 *
 * @package mod_gototraining
 * @copyright 2017 Alok Kumar Rai <alokr.mail@gmail.com,alokkumarrai@outlook.in>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->dirroot . '/mod/gototraining/locallib.php');
require_once $CFG->dirroot . '/mod/gototraining/classes/gotooauth.class.php';
require_once($CFG->libdir . '/completionlib.php');
global $DB, $USER;
$id = required_param('id', PARAM_INT); // Course Module ID

if ($id) {
    if (!$cm = get_coursemodule_from_id('gototraining', $id)) {
        print_error('invalidcoursemodule');
    }
    $gototraining = $DB->get_record('gototraining', array('id' => $cm->instance), '*', MUST_EXIST);
}
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$meeturl = '';
$gototrainingdownloads = array();
$meeturl = get_gototraining($gototraining);




$meetinginfo = json_decode($gototraining->meetinfo);
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/gototraining:view', $context);


$PAGE->set_url('/mod/gototraining/view.php', array('id' => $cm->id));
$PAGE->set_title($course->shortname . ': ' . $gototraining->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($gototraining);

$completion = new completion_info($course);
$completion->set_module_viewed($cm);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('course') . ':  ' . $course->fullname);
$table = new html_table();
$table->head = array(get_string('pluginname', 'mod_gototraining'));
$table->headspan = array(2);
$table->size = array('30%', '70%');

$cell1 = new html_table_cell(get_string('meetingtitle', 'mod_gototraining'));
$cell1->colspan = 1;
$cell1->style = 'text-align:left;';

$cell2 = new html_table_cell("<b>" . $gototraining->name . "</b>");
$cell2->colspan = 1;
$cell2->style = 'text-align:left;';
$table->data[] = array($cell1, $cell2);

$cell1 = new html_table_cell(get_string('meetingdescription', 'mod_gototraining'));
$cell1->colspan = 1;
$cell1->style = 'text-align:left;';

$cell2 = new html_table_cell("<b>" . strip_tags($gototraining->intro) . "</b>");
$cell2->colspan = 1;
$cell2->style = 'text-align:left;';

$table->data[] = array($cell1, $cell2);


$cell1 = new html_table_cell(get_string('meetingstartenddate', 'mod_gototraining'));
$cell1->colspan = 1;
$cell1->style = 'text-align:left;';

$cell2 = new html_table_cell("<b>" . userdate($gototraining->startdatetime) . "</b>");
$cell2->colspan = 1;
$cell2->style = 'text-align:left;';

$table->data[] = array($cell1, $cell2);


$cell1 = new html_table_cell(get_string('meetingenddateandtime', 'mod_gototraining'));
$cell1->colspan = 1;
$cell1->style = 'text-align:left;';

$cell2 = new html_table_cell("<b>" . userdate($gototraining->enddatetime) . "</b>");
$cell2->colspan = 1;
$cell2->style = 'text-align:left;';

$table->data[] = array($cell1, $cell2);

$cell2 = new html_table_cell(html_writer::link(trim($meeturl, '"'), get_string('joinmeeting', 'mod_gototraining'),
    array("target" => "_blank", 'class' => 'btn btn-primary')));
$cell2->colspan = 2;
$cell2->style = 'text-align:center;';

$table->data[] = array($cell2);

foreach ($gototrainingdownloads as $gototrainingdownload) {
    $cell1 = new html_table_cell(get_string('meetingrecording', 'mod_gototraining'));
    $cell1->colspan = 1;
    $cell1->style = 'text-align:left;';
    $downloadlink = html_writer::link($gototrainingdownload->downloadUrl, get_string('downloadurl', 'mod_gototraining') . ' ');
    $cell2 = new html_table_cell("<b>$downloadlink</b>");
    $cell2->colspan = 1;
    $cell2->style = 'text-align:left;';
    $table->data[] = array($cell1, $cell2);
}


echo html_writer::table($table);


echo $OUTPUT->footer();
