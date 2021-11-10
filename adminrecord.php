<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__).'/lib.php');

global $DB;

$deletejitsisourceid = optional_param('deletejitsisourceid', 0, PARAM_INT);
$sesskey = optional_param('sesskey', null, PARAM_TEXT);

$PAGE->set_context(context_system::instance());

$PAGE->set_url('/mod/jitsi/adminrecord.php');
require_login();

if ($deletejitsisourceid && confirm_sesskey($sesskey)) {
    deleterecordyoutube($deletejitsisourceid);
    redirect($PAGE->url, get_string('deleted'));
}

$PAGE->set_title(format_string(get_string('records', 'jitsi')));
$PAGE->set_heading(format_string(get_string('records', 'jitsi')));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('records', 'jitsi'));
echo $OUTPUT->box("This table lists all videos to deleted stored in your streaming provider. Those that are not linked to any Jitsi are available to remove.");

if  (is_siteadmin()){
    $table = new html_table();
    $table->head = array('Id', 'Link', get_string('acount', 'jitsi'), get_string('date'), get_string('delete'));
    $sources = $DB->get_records('jitsi_source_record', array());
    $acountinuse = $DB->get_record('jitsi_record_acount', array('inuse' => 1));

    foreach ($sources as $source) {
        if (isDeletable($source->id)){

            if ($source->acount == $acountinuse->id) {
                $deleteurl = new moodle_url('/mod/jitsi/adminrecord.php?&deletejitsisourceid=' . $source->id. '&sesskey=' . sesskey());
                $deleteicon = new pix_icon('t/delete', get_string('delete'));
                $acount = $DB->get_record('jitsi_record_acount', array('id'=>$source->acount));
                $deleteaction = $OUTPUT->action_icon($deleteurl, $deleteicon, new confirm_action('Delete? All jitsi records with this video record will be deleted'));
                $table->data[] = array($source->id, $source->link, $acount->name, userdate($source->timecreated), $deleteaction);
            } else {
                $acount = $DB->get_record('jitsi_record_acount', array('id'=>$source->acount));
                $table->data[] = array($source->id, $source->link, $acount->name, userdate($source->timecreated), 'Other acount');
            }  
        } 
    }
    echo html_writer::table($table);
}
echo $OUTPUT->footer();

function isincluded($sources, $sourceelement) {
    foreach ($sources as $source) {
        if ($source->id == $sourceelement->id) {
            return true;
        }
    }
    return false;
}
