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
require_once(dirname(dirname(dirname(__FILE__))).'/lib/moodlelib.php');
require_once(dirname(__FILE__).'/lib.php');
$PAGE->set_url($CFG->wwwroot.'/mod/jitsi/session.php');
$PAGE->set_context(context_system::instance());

$nombre = required_param('nom', PARAM_TEXT);
$session = required_param('ses', PARAM_TEXT);
$user = $DB->get_record('user', array('username' => $session));
$sessionnorm = str_replace(array(' ', ':', '"'), '', $user->username);
$avatar = required_param('avatar', PARAM_TEXT);
$teacher = required_param('t', PARAM_BOOL);

$PAGE->set_title(get_string('privatesession', 'jitsi', $user->firstname));
$PAGE->set_heading(get_string('privatesession', 'jitsi', $user->firstname));
echo $OUTPUT->header();

if ($teacher == 1) {
      $teacher = true;
      $affiliation = "owner";
} else {
      $teacher = false;
      $affiliation = "member";
}

$header = json_encode([
  "kid" => "jitsi/custom_key_name",
  "typ" => "JWT",
  "alg" => "HS256"
], JSON_UNESCAPED_SLASHES);
$base64urlheader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

$payload  = json_encode([
  "context" => [
    "user" => [
      "affiliation" => $affiliation,
      "avatar" => $avatar,
      "name" => $nombre,
      "email" => "",
      "id" => ""
    ],
    "group" => ""
  ],
  "aud" => "jitsi",
  "iss" => $CFG->jitsi_app_id,
  "sub" => $CFG->jitsi_domain,
  "room" => urlencode($sessionnorm),
  "exp" => time() + 24 * 3600,
  "moderator" => $teacher

], JSON_UNESCAPED_SLASHES);
$base64urlpayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

$secret = $CFG->jitsi_secret;
$signature = hash_hmac('sha256', $base64urlheader . "." . $base64urlpayload, $secret, true);
$base64urlsignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

$jwt = $base64urlheader . "." . $base64urlpayload . "." . $base64urlsignature;
echo "<script src=\"https://".$CFG->jitsi_domain."/external_api.js\"></script>\n";

echo "<script>\n";
echo "var domain = \"".$CFG->jitsi_domain."\";\n";
echo "var options = {\n";
echo "configOverwrite: {\n";
echo "channelLastN: ".$CFG->jitsi_channellastcam.",\n";
echo "startWithAudioMuted: true,\n";
echo "startWithVideoMuted: true,\n";
echo "},\n";
echo "roomName: \"".urlencode($sessionnorm)."\",\n";

if ($CFG->jitsi_app_id != null && $CFG->jitsi_secret != null) {
    echo "jwt: \"".$jwt."\",\n";
}
if ($CFG->branch < 36) {
    if ($CFG->theme == 'boost' || in_array('boost', $themeconfig->parents)) {
        echo "parentNode: document.querySelector('#region-main .card-body'),\n";
    } else {
        echo "parentNode: document.querySelector('#region-main'),\n";
    }
} else {
    echo "parentNode: document.querySelector('#region-main'),\n";
}
$streamingoption = '';

if ($teacher == true && $CFG->jitsi_livebutton == 1) {
    $streamingoption = 'livestreaming';
}

$desktop = 'desktop';

$youtubeoption = '';
if ($CFG->jitsi_shareyoutube == 1) {
    $youtubeoption = 'sharedvideo';
}

$bluroption = '';
if ($CFG->jitsi_blurbutton == 1) {
    $bluroption = 'videobackgroundblur';
}

$security = '';
if ($CFG->jitsi_securitybutton == 1) {
    $security = 'security';
}

$invite = '';
if ($CFG->jitsi_invitebuttons == 1) {
    $invite = 'invite';
}

$buttons = "['microphone', 'camera', 'closedcaptions', '".$desktop."', 'fullscreen',
        'fodeviceselection', 'hangup', 'profile', 'chat', 'recording',
        '".$streamingoption."', 'etherpad', '".$youtubeoption."', 'settings', 'raisehand',
        'videoquality', 'filmstrip', '".$invite."', 'feedback', 'stats', 'shortcuts',
        'tileview', '".$bluroption."', 'download', 'help', 'mute-everyone', '".$security."']";

echo "interfaceConfigOverwrite:{\n";
echo "TOOLBAR_BUTTONS:".$buttons.",\n";

echo "SHOW_JITSI_WATERMARK: true,\n";
echo "JITSI_WATERMARK_LINK: '".$CFG->jitsi_watermarklink."',\n";
echo "},\n";

echo "width: '100%',\n";
echo "height: 650,\n";
echo "}\n";
echo "var api = new JitsiMeetExternalAPI(domain, options);\n";
echo "api.executeCommand('displayName', '".$nombre."');\n";
echo "api.executeCommand('avatarUrl', '".$avatar."');\n";
if ($CFG->jitsi_finishandreturn == 1) {
    echo "api.on('readyToClose', () => {\n";
    echo      "api.dispose();\n";
    echo      "location.href=\"".$CFG->wwwroot."/user/profile.php?id=".$user->id."\";";
    echo  "});\n";
}

if ($CFG->jitsi_password != null) {
    echo "api.addEventListener('participantRoleChanged', function(event) {";
    echo "    if (event.role === \"moderator\") {";
    echo "        api.executeCommand('password', '".$CFG->jitsi_password."');";
    echo "    }";
    echo "});";
    echo "api.on('passwordRequired', function ()";
    echo "{";
    echo "    api.executeCommand('password', '".$CFG->jitsi_password."');";
    echo "});";
}

echo "</script>\n";

echo $OUTPUT->footer();
