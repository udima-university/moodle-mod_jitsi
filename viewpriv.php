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
 * @copyright  2019 Sergio Comerón <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');


global $USER, $DB, $PAGE;

$userid = required_param('user', PARAM_INT);

$user = $DB->get_record('user', array('id' => $userid));

$PAGE->set_context(context_system::instance());

$PAGE->set_url('/mod/jitsi/viewpriv.php', array('user' => $user->id));
$PAGE->set_title(format_string($user->firstname));
$PAGE->set_heading(format_string($user->firstname));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('privatesession', 'jitsi', $user->firstname));
if ($USER->id == $user->id) {
    $moderation = 1;
} else {
    $moderation = 0;
}

$nom = null;
switch ($CFG->jitsi_id) {
    case 'username':
        $nom = $USER->username;
        break;
    case 'nameandsurname':
        $nom = $USER->firstname.' '.$USER->lastname;
        break;
    case 'alias':
        break;
}
$sessionoptionsparam = ['$course->shortname', '$jitsi->id', '$jitsi->name'];
$fieldssessionname = $CFG->jitsi_sesionname;

$allowed = explode(',', $fieldssessionname);
$max = count($allowed);

$sesparam = $user->username;
$avatar = $CFG->wwwroot.'/user/pix.php/'.$USER->id.'/f1.jpg';

$urlparams = array('avatar' => $avatar, 'nom' => $nom, 'ses' => $sesparam,
    't' => $moderation);

echo $OUTPUT->box(get_string('instruction', 'jitsi'));
echo $OUTPUT->single_button(new moodle_url('/mod/jitsi/sessionpriv.php', $urlparams), get_string('access', 'jitsi'), 'post');

echo $CFG->jitsi_help;
echo $OUTPUT->footer();

/**
 * Sanitize strings
 * @param $string - The string to sanitize.
 * @param $forcelowercase - Force the string to lowercase?
 * @param $anal - If set to *true*, will remove all non-alphanumeric characters.
 */
function string_sanitize($string, $forcelowercase = true, $anal = false) {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")",
            "_", "=", "+", "[", "{", "]", "}", "\\", "|", ";", ":", "\"",
            "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ".", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;
    return ($forcelowercase) ?
        (function_exists('mb_strtolower')) ?
            mb_strtolower($clean, 'UTF-8') :
            strtolower($clean) :
        $clean;
}
