<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__).'/lib.php');


global $DB;

$deletejitsirecordid = optional_param('deletejitsirecordid', 0, PARAM_INT);
$sesskey = optional_param('sesskey', null, PARAM_TEXT);



$PAGE->set_context(context_system::instance());

$PAGE->set_url('/mod/jitsi/adminrecord.php');
require_login();


$PAGE->set_title(format_string('Records'));
$PAGE->set_heading(format_string('Records'));

echo $OUTPUT->header();
echo $OUTPUT->heading('Records');

$records = $DB->get_records('jitsi_record', array());

if ($deletejitsirecordid && confirm_sesskey($sesskey)) {
    // delete($deletejitsirecordid, 1);
    deleterecordyoutube($deletejitsirecordid);
    redirect($PAGE->url, get_string('deleted'));
}

$table = new html_table();
    $table->head = array('jitsi', 'link', 'deleted', 'courses' ,'delete' );

    foreach ($records as $record) {
        if ($record->deleted != 0) {
            if (isDeletable($record->link)){
                $deleteurl = new moodle_url('/mod/jitsi/adminrecord.php?&deletejitsirecordid=' . $record->id . '&sesskey=' . sesskey());
            $deleteicon = new pix_icon('t/delete', get_string('delete'));
            $deleteaction = $OUTPUT->action_icon($deleteurl, $deleteicon, new confirm_action('Delete?'));
            $table->data[] = array($record->jitsi, $record->link, $record->deleted, $deleteaction);
            } else {
                $table->data[] = array($record->jitsi, $record->link, $record->deleted);

            }
            
        }
    }
    echo html_writer::table($table);







echo $OUTPUT->footer();



