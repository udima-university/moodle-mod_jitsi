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
 * @copyright  2021 Sergio Comerón <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/lib/moodlelib.php');
require_once(dirname(dirname(dirname(__FILE__))).'/lib/grouplib.php');
require_once(dirname(__FILE__).'/lib.php');

$PAGE->set_url($CFG->wwwroot.'/mod/jitsi/session.php');

$state = optional_param('state', null, PARAM_TEXT);

if ($state == null) {
    $courseid = required_param('courseid', PARAM_INT);
    $cmid = required_param('cmid', PARAM_INT);
    $nombre = required_param('nom', PARAM_TEXT);
    $session = required_param('ses', PARAM_TEXT);
    $avatar = $CFG->jitsi_showavatars == true ? required_param('avatar', PARAM_TEXT) : null;
    $teacher = required_param('t', PARAM_BOOL);

} else {
    $paramdecode = base64urldecode($state);
    $parametrosarray = explode("&", $paramdecode);
    $avatararray = $parametrosarray[0];
    $nomarray = $parametrosarray[1];
    $sessionarray = $parametrosarray[2];
    $coursearray = $parametrosarray[3];
    $cmidarray = $parametrosarray[4];
    $tarray = $parametrosarray[5];
    $statesesarray = $parametrosarray[6];
    $avatara = explode("=", $avatararray);
    $nombrea = explode("=", $nomarray);
    $sessiona = explode("=", $sessionarray);
    $courseida = explode("=", $coursearray);
    $cmida = explode("=", $cmidarray);
    $teachera = explode("=", $tarray);
    $statesesa = explode("=", $statesesarray);
    $avatar = $avatara[1];
    $nombre = $nombrea[1];
    $session = $sessiona[1];
    $courseid = $courseida[1];
    $cmid = $cmida[1];
    $teacher = $teachera[1];
    $stateses = $statesesa[1];
}
// start jitsi-group-room parameters
    $groupeNameSeleted = '';
    $mygrouproomselected = $_POST['mygrouproom'];
    $urlparams1 = array('avatar' => $avatar, 'nom' => $nombre, 'ses' => $session, 'userid' => $USER->id, 'courseid' => $courseid, 'cmid' => $cmid, 't' => $teacher);
    $choosegroup = get_string('access', 'jitsi');
    $nogroup = get_string('nogroup', 'group');
// end jitsi-group-room parameters

$cm = get_coursemodule_from_id('jitsi', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
require_login($course, true, $cm);

$module = $DB->get_record('modules', array('name' => 'jitsi'));

$jitsi = $DB->get_record('jitsi', array('id' => $cm->instance));

$PAGE->set_title($jitsi->name);
$PAGE->set_heading($jitsi->name);
echo $OUTPUT->header();

$PAGE->set_context(context_module::instance($cm->id));

$event = \mod_jitsi\event\jitsi_session_enter::create(array(
  'objectid' => $PAGE->cm->instance,
  'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $jitsi);
$event->trigger();

echo "<script>";
echo "function participating () {";
echo "  console.log(\"RUNNING\");";
echo "    require(['jquery', 'core/ajax', 'core/notification'], function($, ajax, notification) {\n";
echo "       var respuesta = ajax.call([{\n";
echo "            methodname: 'mod_jitsi_participating_session',\n";
echo "            args: {jitsi:'".$jitsi->id."', user:'".$USER->id."', cmid:'".$cm->id."'},\n";
echo "       }]);\n";
echo "        console.log(respuesta[0]);";
echo "})\n";
echo "}";
echo "setInterval(participating, 60000);\n";
echo "</script>";

// start jitsi-group-room
echo '<script>function changemygrouproom() {document.getElementById("SelectGroupRoomForm").submit();} </script>';	
if($teacher){
$results = $DB->get_records_sql("SELECT * FROM {groups} WHERE courseid='$courseid' ORDER BY name ASC");
echo '<div style="text-align:center;">
<form name="SelectGroupRoomForm" action="'.new moodle_url('/mod/jitsi/session.php', $urlparams1).'" method="POST" id="SelectGroupRoomForm">
<select class="custom-select" type="text" name="mygrouproom" id="mygrouproom_ID" onchange="changemygrouproom()">
<option value="" disabled selected>'.$choosegroup.'</option>
<option value="" >'.$nogroup.'</option>';
foreach ($results as $row) {
	$groupeNameSeleted = $row->name;
echo'<option value="'.$groupeNameSeleted.'">'.$groupeNameSeleted.'</option>';
    }
echo '</select></form></div>'; 
	createsession($teacher, $cmid, $avatar, $nombre, $session.' '.$mygrouproomselected, null, $jitsi);
}else{
	$results = groups_get_my_groups();
$row_cnt = count($results);
if($row_cnt > 1){
echo '<div style="text-align:center;">
<form name="SelectGroupRoomForm" action="'.new moodle_url('/mod/jitsi/session.php', $urlparams1).'" method="POST" id="SelectGroupRoomForm">
<select class="custom-select" type="text" name="mygrouproom" id="mygrouproom_ID" onchange="changemygrouproom()">
<option value="" disabled selected>'.$choosegroup.'</option>';
foreach ($results as $row) {
	$groupeNameSeleted = $row->name;
echo'<option value="'.$groupeNameSeleted.'">'.$groupeNameSeleted.'</option>';
    }
echo '</select></form></div>'; 

	createsession($teacher, $cmid, $avatar, $nombre, $session.' '.$mygrouproomselected, null, $jitsi);
}
elseif ($row_cnt <= 1) {
foreach ($results as $row) {
	$groupeNameSeleted = $row->name;
    }
	createsession($teacher, $cmid, $avatar, $nombre, $session.' '.$groupeNameSeleted, null, $jitsi);
}
}
// end jitsi-group-room

echo $OUTPUT->footer();
