<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of jitsi
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_jitsi
 * @copyright  2024 Sergio Comerón <jitsi@sergiocomeron.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once('view_table.php');

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');

global $USER;

$id = optional_param('id', 0, PARAM_INT);
$n = optional_param('n', 0, PARAM_INT);
$state = optional_param('state', null, PARAM_TEXT);
$deletejitsirecordid = optional_param('deletejitsirecordid', 0, PARAM_INT);
$hidejitsirecordid = optional_param('hidejitsirecordid', 0, PARAM_INT);
$showjitsirecordid = optional_param('showjitsirecordid', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('jitsi', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $jitsi = $DB->get_record('jitsi', ['id' => $cm->instance], '*', MUST_EXIST);
    $sesskey = optional_param('sesskey', null, PARAM_TEXT);
} else {
    throw new \moodle_exception('Unable to find jitsi');
}
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
$PAGE->set_url('/mod/jitsi/attendessview.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($jitsi->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();
$backurl = new moodle_url('/mod/jitsi/view.php', ['id' => $cm->id]);
echo html_writer::link($backurl, get_string('back'));
$cm = get_coursemodule_from_id('jitsi', $id, 0, false, MUST_EXIST);
$contextmodule = context_module::instance($cm->id);

$table = new mod_view_table('search');
    $fields = '{jitsi_record}.id,
               {jitsi_source_record}.link,
               {jitsi_record}.jitsi,
               {jitsi_record}.name,
               {jitsi_source_record}.timecreated';
    $from = '{jitsi_record}, {jitsi_source_record}';
    if (has_capability('mod/jitsi:hide', $context)) {
        $where = '{jitsi_record}.source = {jitsi_source_record}.id and
        {jitsi_record}.jitsi = '.$jitsi->id.' and
        {jitsi_record}.deleted = 0';
    } else {
        $where = '{jitsi_record}.source = {jitsi_source_record}.id and
        {jitsi_record}.jitsi = '.$jitsi->id.' and
        {jitsi_record}.deleted = 0 and
        {jitsi_record}.visible = 1';
    }
    if (!empty($recorder)) {
        $recorderlist = implode(',', $recorder);
        $where .= ' AND {jitsi_source_record}.account IN ('.$recorderlist.')';
    }
    if (!empty($userselected)) {
        $userlist = implode(',', $userselected);
        $where .= ' AND {jitsi_source_record}.userid IN ('.$userlist.')';
    }
    $table->set_sql($fields, $from, $where, ['1']);
    $table->sortable(true, 'id', SORT_DESC);
    $table->define_baseurl('/mod/jitsi/view.php?id='.$id.'#record');
    $table->out(5, true);

echo $OUTPUT->footer();
