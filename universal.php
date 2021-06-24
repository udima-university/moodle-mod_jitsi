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
require_once(dirname(__FILE__).'/lib.php');

$sessionid = required_param('ses', PARAM_INT);
$name = required_param('name', PARAM_TEXT);
$mail = required_param('mail', PARAM_TEXT);
$avatar = optional_param('avatar', null, PARAM_TEXT);
$id = required_param('id', PARAM_INT);

global $DB, $CFG;
$PAGE->set_url($CFG->wwwroot.'/mod/jitsi/universal.php');
$sesion = $DB->get_record('jitsi', array('id' => $sessionid));
$course = $DB->get_record('course', array('id' => $sesion->course));
$PAGE->set_course($course);
$cm = get_coursemodule_from_id('jitsi', $id, 0, false, MUST_EXIST);

$PAGE->set_context(context_module::instance($cm->id));

$event = \mod_jitsi\event\jitsi_session_enter::create(array(
  'objectid' => $PAGE->cm->instance,
  'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $jitsi);
$event->trigger();

$fieldssessionname = $CFG->jitsi_sesionname;
$allowed = explode(',', $fieldssessionname);
$max = count($allowed);

$sesparam = '';
$optionsseparator = ['.', '-', '_', ''];
for ($i = 0; $i < $max; $i++) {
    if ($i != $max - 1) {
        if ($allowed[$i] == 0) {
            $sesparam .= string_sanitize($course->shortname).$optionsseparator[$CFG->jitsi_separator];
        } else if ($allowed[$i] == 1) {
            $sesparam .= $sesion->id.$optionsseparator[$CFG->jitsi_separator];
        } else if ($allowed[$i] == 2) {
            $sesparam .= string_sanitize($sesion->name).$optionsseparator[$CFG->jitsi_separator];
        }
    } else {
        if ($allowed[$i] == 0) {
            $sesparam .= string_sanitize($course->shortname);
        } else if ($allowed[$i] == 1) {
            $sesparam .= $sessionid;
        } else if ($allowed[$i] == 2) {
            $sesparam .= string_sanitize($sesion->name);
        }
    }
}

$PAGE->set_title($sesion->name);
$PAGE->set_heading($sesion->name);

echo $OUTPUT->header();

createsession($teacher, $id,  $avatar, $name, $sesparam, $mail, $sesion);

echo $OUTPUT->footer();

/**
 * Base 64 encode
 * @param $inputstr - Input to encode
 */
function base64urlencode($inputstr) {
    return strtr(base64_encode($inputstr), '+/=', '-_,');
}

/**
 * Base 64 decode
 * @param $inputstr - Input to decode
 */
function base64urldecode($inputstr) {
    return base64_decode(strtr($inputstr, '-_,', '+/='));
}

/**
 * Create session
 * @param $teacher - Moderation
 * @param $cmid - Course module
 * @param $avatar - Avatar
 * @param $nombre - Name
 * @param $session - sesssion name
 * @param $mail - mail
 * @param $jitsi - Jitsi session
 */
function createsession($teacher, $cmid, $avatar, $nombre, $session, $mail, $jitsi) {
    global $CFG, $DB, $PAGE, $USER;
    $sessionnorm = str_replace(array(' ', ':', '"'), '', $session);
    if ($teacher == 1) {
        $teacher = true;
        $affiliation = "owner";
    } else {
        $teacher = false;
        $affiliation = "member";
    }

    $jitsimodule = $DB->get_record('modules', array('name' => 'jitsi'));
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
                "email" => $mail,
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
    echo "<script src=\"//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js\"></script>";
    echo "<script src=\"https://".$CFG->jitsi_domain."/external_api.js\"></script>\n";
    $streamingoption = '';
    $desktop = '';

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
        'fodeviceselection', 'hangup', 'chat', 'etherpad', '".$youtubeoption."',
        'settings', 'raisehand', 'videoquality', 'filmstrip', '".$invite."', 'stats',
        'shortcuts', 'tileview', '".$bluroption."', 'download', 'help', '".$security."']";

    echo "<script>\n";
    echo "const domain = \"".$CFG->jitsi_domain."\";\n";
    echo "const options = {\n";
    echo "configOverwrite: {\n";
    if ($CFG->jitsi_deeplink == 0) {
        echo "disableDeepLinking: true,\n";
    }
    echo "toolbarButtons: ".$buttons.",\n";
    echo "disableProfile: true,\n";
    echo "prejoinPageEnabled: false,";
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
    echo "interfaceConfigOverwrite:{\n";
    echo "TOOLBAR_BUTTONS: ".$buttons.",\n";
    echo "SHOW_JITSI_WATERMARK: true,\n";
    echo "JITSI_WATERMARK_LINK: '".$CFG->jitsi_watermarklink."',\n";
    echo "},\n";
    echo "width: '100%',\n";
    echo "height: 650,\n";
    echo "}\n";
    echo "const api = new JitsiMeetExternalAPI(domain, options);\n";
    echo "api.executeCommand('displayName', '".$nombre."');\n";
    echo "api.executeCommand('avatarUrl', '".$avatar."');\n";
    if ($CFG->jitsi_finishandreturn == 1) {
        echo "api.on('readyToClose', () => {\n";
        echo "    api.dispose();\n";
        echo "    location.href=\"".$CFG->wwwroot."/mod/jitsi/formuniversal.php?id=".$cmid."&ses=".$jitsi->id."\";";
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
}

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
