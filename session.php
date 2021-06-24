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
  'name' => $name,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $jitsi);
$event->trigger();

createsession($teacher, $cmid, $avatar, $nombre, $session, null, $jitsi);

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
    $context = context_module::instance($cmid);
    if (!has_capability('mod/jitsi:view', $context)) {
        notice(get_string('noviewpermission', 'jitsi'));
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
    if (($CFG->jitsi_livebutton == 1) && (has_capability('mod/jitsi:record', $PAGE->context))
        && ($CFG->jitsi_streamingoption == 0)) {
        $streamingoption = 'livestreaming';
    }
    $desktop = '';
    if (has_capability('mod/jitsi:sharedesktop', $context)) {
        $desktop = 'desktop';
    }
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
    $record = '';
    if ($CFG->jitsi_record == 1) {
        $record = 'recording';
    }
    $invite = '';
    $muteeveryone = '';
    $mutevideoeveryone = '';
    if ($teacher){
        $muteeveryone = 'mute-everyone';
        $mutevideoeveryone = 'mute-video-everyone';
    }

    $buttons = "['microphone', 'camera', 'closedcaptions', '".$desktop."', 'fullscreen',
        'fodeviceselection', 'hangup', 'chat', '".$record."', 'etherpad', '".$youtubeoption."',
        'settings', 'raisehand', 'videoquality', '".$streamingoption."','filmstrip', '".$invite."', 'stats',
        'shortcuts', 'tileview', '".$bluroption."', 'download', 'help', '".$muteeveryone."',
        '".$mutevideoeveryone."', '".$security."']";

    echo "<div class=\"row\">";
    echo "<div class=\"col-sm\">";
    if ($CFG->jitsi_livebutton == 1 && has_capability('mod/jitsi:record', $PAGE->context)
        && get_config('mod_jitsi', 'jitsi_clientrefreshtoken') != null && get_config('mod_jitsi', 'jitsi_clientaccesstoken') != null
        && ($CFG->jitsi_streamingoption == 1)) {
        echo "<button onclick=\"stream()\" type=\"button\" class=\"btn btn-secondary\" id=\"startstream\">".
            get_string('startstream', 'jitsi')."</button>";
        echo " ";
        echo "<button onclick=\"stopStream()\" type=\"button\" class=\"btn btn-secondary\"
              id=\"stopstream\" disabled=\"true\">".get_string('stopstream', 'jitsi')."</button>";
    }
    if ($CFG->jitsi_invitebuttons == 1 && has_capability('mod/jitsi:createlink', $PAGE->context)) {
        echo " ";
        echo "<button onclick=\"copyurl()\" type=\"button\" class=\"btn btn-secondary\" id=\"copyurl\">";
        echo get_string('URLguest', 'jitsi');
        echo "</button>";
    }

    echo "</div></div>";
    echo "<hr>";

    echo "<div class=\"modal fade\" id=\"exampleModal\" tabindex=\"-1\" role=\"dialog\"
          aria-labelledby=\"exampleModalLabel\" aria-hidden=\"true\">";
    echo "    <div class=\"modal-dialog\" role=\"document\">";
    echo "        <div class=\"modal-content\">";
    echo "            <div class=\"modal-header\">";
    echo "                <h5 class=\"modal-title\" id=\"exampleModalLabel\">Link</h5>";
    echo "                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">";
    echo "                    <span aria-hidden=\"true\">&times;</span>";
    echo "                </button>";
    echo "            </div>";
    echo "            <div class=\"modal-body\">";
    echo "                <form>";
    echo "                    <div class=\"form-group\">";
    echo "                        <label for=\"formGroupExampleInput\">Nombre</label>";
    echo "                        <input type=\"text\" class=\"form-control\" id=\"nombrelink\" placeholder=\"Enter name\">";
    echo "                    </div>";
    echo "                    <div class=\"form-group\">";
    echo "                        <label for=\"exampleInputEmail1\">Email address</label>";
    echo "                        <input type=\"email\" class=\"form-control\" id=\"maillink\"
                                    aria-describedby=\"emailHelp\" placeholder=\"Enter email\">";
    echo "                        <small id=\"emailHelp\" class=\"form-text text-muted\">
                                    We'll never share your email with anyone else.</small>";
    echo "                    </div>";
    echo "                    <div class=\"form-group\">";
    echo "                        <button onclick=\"sendlink()\" type=\"button\" class=\"btn btn-primary\">Send</button>";
    echo "                    </div>";
    echo "                </form>";
    echo "            </div>";
    echo "        </div>";
    echo "    </div>";
    echo "</div>";

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
        echo "    location.href=\"".$CFG->wwwroot."/mod/jitsi/view.php?id=".$cmid."\";";
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

    echo "api.addEventListener('recordingStatusChanged', function(event) {\n";
    echo "    if (event['on']){\n";
    echo "        document.getElementById(\"startstream\").disabled = true;\n";
    echo "        document.getElementById(\"stopstream\").disabled = false;\n";
    echo "    } else if (!event['on']){\n";
    echo "        document.getElementById(\"stopstream\").disabled = true;\n";
    echo "        document.getElementById(\"startstream\").disabled = false;\n";
    echo "    }\n";
    echo "    require(['jquery', 'core/ajax', 'core/notification'], function($, ajax, notification) {\n";
    echo "        ajax.call([{\n";
    echo "            methodname: 'mod_jitsi_state_record',\n";
    echo "            args: {jitsi:".$jitsi->id.", state: event['on']},\n";

    echo "            done: console.log(\"Cambio grabación\"),\n";
    echo "            fail: notification.exception\n";
    echo "        }]);\n";
    echo "        console.log(event['on']);\n";
    echo "    })\n";
    echo "});\n";

    echo "function stream(){\n";
    echo "document.getElementById(\"startstream\").disabled = true;\n";
    echo "document.getElementById(\"stopstream\").disabled = false;\n";
    echo "    require(['jquery', 'core/ajax', 'core/notification'], function($, ajax, notification) {\n";
    echo "       var respuesta = ajax.call([{\n";
    echo "            methodname: 'mod_jitsi_create_stream',\n";
    echo "            args: {session:'".$session."', jitsi:'".$jitsi->id."'},\n";

    echo "       }]);\n";
    echo "       respuesta[0].done(function(response) {\n";
    echo "          api.executeCommand('startRecording', {\n";
    echo "              mode: 'stream',\n";
    echo "              youtubeStreamKey: response \n";
    echo "          })\n";
    echo "            console.log(response);";
    echo ";})";
    echo  ".fail(function(ex) {console.log(ex);});";
    echo "    })\n";
    echo "}\n";

    echo "function stopStream(){\n";
    echo "document.getElementById(\"startstream\").disabled = false;\n";
    echo "document.getElementById(\"stopstream\").disabled = true;\n";
    echo "api.executeCommand('stopRecording', 'stream');\n";
    echo "}\n";

    echo "function sendlink(){\n";
    echo "        var nombreform = document.getElementById(\"nombrelink\").value;";
    echo "        var mailform = document.getElementById(\"maillink\").value;";
    echo "    require(['jquery', 'core/ajax', 'core/notification'], function($, ajax, notification) {\n";
    echo "       var respuesta = ajax.call([{\n";
    echo "            methodname: 'mod_jitsi_create_link',\n";
    echo "            args: {jitsi: ".$jitsi->id."},\n";
    echo "       }]);\n";
    echo "       respuesta[0].done(function(response) {\n";
    echo "            alert(\"Enviado\");";
    echo ";})";
    echo  ".fail(function(ex) {console.log(ex);});";
    echo "    })\n";
    echo "}\n";

    echo "function copyurl() {\n";
    echo "            var copyText = '".$CFG->wwwroot.'/mod/jitsi/formuniversal.php?id='.$cmid.'&ses='.$jitsi->id."';";
    echo "            navigator.clipboard.writeText(copyText);";
    echo "            alert(\"".get_string('copied', 'jitsi')."\");";
    echo "}\n";

    echo "</script>\n";
}
