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
 * Jitsi Mobile output class
 *
 * @package    mod_jitsi
 * @copyright  2019 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_jitsi\output;

use context_module;
use context_course;

/**
 * Mobile output class for jitsi
 *
 * @package    mod_jitsi
 * @copyright  2021 Arnes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {
    /**
     * Returns the Jitsi pre-session view for the mobile app.
     * @param  array $args Arguments from tool_mobile_get_content WS
     * @return array HTML, javascript and otherdata
     */
    public static function mobile_presession_view($args) {
        global $CFG, $DB, $OUTPUT, $USER;

        $id = $args['cmid'];

        if ($args['appversioncode'] >= 3950) {
            $foldername = 'ionic5';
        } else {
            $foldername = 'ionic3';
        }

        $courseid = $args['courseid'];

        if ($id) {
            $cm = get_coursemodule_from_id('jitsi', $id, 0, false, MUST_EXIST);
            $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
            $jitsi = $DB->get_record('jitsi', ['id' => $cm->instance], '*', MUST_EXIST);
        } else {
            throw new \moodle_exception('You must specify a course_module ID');
        }

        require_login($course, false, $cm, true, true);

        $context = \context_module::instance($cm->id);

        if (!has_capability('mod/jitsi:view', $context)) {
            notice(get_string('noviewpermission', 'jitsi'));
        }

        $context = \context_course::instance($courseid);

        $roles = get_user_roles($context, $USER->id);

        $rolestr[] = null;
        foreach ($roles as $role) {
            $rolestr[] = $role->shortname;
        }

        if ($jitsi->intro) {
            $intro = format_module_intro('jitsi', $jitsi, $cm->id);

            $intro = str_replace(['<h2', '<h3'], '<h1', $intro);
            $intro = str_replace(['</h2>', '</h3>'], '</h1>', $intro);

            $intro = str_replace(['<h4', '<h5', '<h6'], '<h2', $intro);
            $intro = str_replace(['</h4>', '</h5>', '</h6>'], '</h2>', $intro);

        } else {
            $intro = "";
        }

        $moderation = false;
        if (has_capability('mod/jitsi:moderation', $context)) {
            $moderation = true;
        }

        $nom = null;
        switch (get_config('mod_jitsi', 'id')) {
            case 'username':
                $nom = $USER->username;
                break;
            case 'nameandsurname':
                $nom = $USER->firstname.' '.$USER->lastname;
                break;
            case 'alias':
                break;
        }

        $fieldssessionname = get_config('mod_jitsi', 'sesionname');

        $allowed = explode(',', $fieldssessionname);
        $max = count($allowed);

        $sesparam = '';
        $optionsseparator = ['.', '-', '_', ''];
        for ($i = 0; $i < $max; $i++) {
            if ($i != $max - 1) {
                if ($allowed[$i] == 0) {
                    $sesparam .= string_sanitize($course->shortname).$optionsseparator[get_config('mod_jitsi', 'separator')];
                } else if ($allowed[$i] == 1) {
                    $sesparam .= $jitsi->id.$optionsseparator[get_config('mod_jitsi', 'separator')];
                } else if ($allowed[$i] == 2) {
                    $sesparam .= string_sanitize($jitsi->name).$optionsseparator[get_config('mod_jitsi', 'separator')];
                }
            } else {
                if ($allowed[$i] == 0) {
                    $sesparam .= string_sanitize($course->shortname);
                } else if ($allowed[$i] == 1) {
                    $sesparam .= $jitsi->id;
                } else if ($allowed[$i] == 2) {
                    $sesparam .= string_sanitize($jitsi->name);
                }
            }
        }

        $help = "";
        if (get_config('mod_jitsi', 'help')) {
            $help = str_replace(['<h2', '<h3'], '<h1', get_config('mod_jitsi', 'help'));
            $help = str_replace(['</h2>', '</h3>'], '</h1>', $help);

            $help = str_replace(['<h4', '<h5', '<h6'], '<h2>', $help);
            $help = str_replace(['</h4>', '</h5>', '</h6>'], '</h2>', $help);
        }

        $contextuserpic = $DB->get_record('context', ['instanceid' => $USER->id, 'contextlevel' => 30]);
        $avatar = $CFG->wwwroot.'/pluginfile.php/'.$contextuserpic->id.'/user/icon/boost/f1';

        $data = [
            'avatar' => $avatar,
            'nom' => $nom,
            'ses' => $sesparam,
            'courseid' => $course->id,
            'cmid' => $id,
            't' => $moderation,
            'help' => $help,
            'intro' => $intro,
            'title' => format_string($jitsi->name),
            'room' => str_replace([' ', ':', '"'], '', $sesparam),
            'minpretime' => $jitsi->minpretime,
        ];

        $today = getdate();
        if ($today[0] > (($jitsi->timeopen) - ($jitsi->minpretime * 60))) {
                $data['nostart_show'] = false;
        } else {
            $data['nostart_show'] = true;
        }

        if ($today[0] < $jitsi->timeclose || $jitsi->timeclose == 0) {
            $data['finish_show'] = false;
        } else {
            $data['finish_show'] = true;
        }

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template("mod_jitsi/mobile_presession_view_page_$foldername", $data),
                ],
            ],
            'javascript' => '',
            'otherdata' => '',
        ];
    }

    /**
     * Returns the Jitsi session view for the mobile app.
     * @param  array $args Arguments from tool_mobile_get_content WS
     * @return array HTML, javascript and otherdata
     */
    public static function mobile_session_view($args) {
        global $OUTPUT, $CFG;
        $serverid = get_config('mod_jitsi', 'server');
        $server = $DB->get_record('jitsi_servers', ['id' => $serverid]);
        $servertype = $server->type;
        $appid = $server->appid;
        $domain = $server->domain;
        $secret = $server->secret;

        if ($args['appversioncode'] >= 3950) {
            $foldername = 'ionic5';
        } else {
            $foldername = 'ionic3';
        }

        $courseid = $args['courseid'];
        $cmid = $args['cmid'];
        $nombre = $args['nom'];
        $session = $args['ses'];
        $sessionnorm = str_replace([' ', ':', '"'], '', $session);
        $avatar = $args['avatar'];
        $teacher = $args['t'];

        require_login($courseid);

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
            "alg" => "HS256",
        ], JSON_UNESCAPED_SLASHES);
        $base64urlheader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        $payload = json_encode([
            "context" => [
                "user" => [
                    "affiliation" => $affiliation,
                    "avatar" => $avatar,
                    "name" => $nombre,
                    "email" => "",
                    "id" => "",
                ],
                "group" => "",
            ],
            "aud" => "jitsi",
            "iss" => $appid,
            "sub" => $secret,
            "room" => urlencode($sessionnorm),
            "exp" => time() + 24 * 3600,
            "moderator" => $teacher,
        ], JSON_UNESCAPED_SLASHES);
        $base64urlpayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $secret = get_config('mod_jitsi', 'secret');
        $signature = hash_hmac('sha256', $base64urlheader . "." . $base64urlpayload, $secret, true);
        $base64urlsignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $jwt = $base64urlheader . "." . $base64urlpayload . "." . $base64urlsignature;

        $desktop = '';
        if (has_capability('mod/jitsi:sharedesktop', $context)) {
            $desktop = 'desktop';
        }

        $youtubeoption = '';
        if (get_config('mod_jitsi', 'shareyoutube') == 1) {
            $youtubeoption = 'sharedvideo';
        }

        $bluroption = '';
        if (get_config('mod_jitsi', 'blurbutton') == 1) {
            $bluroption = 'videobackgroundblur';
        }

        $security = '';
        if (get_config('mod_jitsi', 'securitybutton') == 1) {
            $security = 'security';
        }

        $invite = '';
        if (get_config('mod_jitsi', 'invitebuttons') == 1) {
            $invite = 'invite';
        }
        $muteeveryone = '';
        $mutevideoeveryone = '';
        if ($teacher) {
            $muteeveryone = 'mute-everyone';
            $mutevideoeveryone = 'mute-video-everyone';
        }

        $buttons = "[\"microphone\",\"camera\",\"closedcaptions\",\"".$desktop."\",\"fullscreen\",";
        $buttons .= "\"fodeviceselection\",\"hangup\",\"profile\",\"chat\",\"recording\",\"etherpad\",";
        $buttons .= "\"".$youtubeoption."\",\"settings\",\"raisehand\",\"videoquality\",\"filmstrip\",";
        $buttons .= "\"".$invite."\",\"feedback\",\"stats\",\"shortcuts\",\"tileview\",\"".$bluroption."\",";
        $buttons .= "\"download\",\"help\",\"".$muteeveryone."\",\"".$mutevideoeveryone."\",\"".$security."\"]";

        $data = [];
        if ($appid != null && $secret != null) {
            $data['jwt'] = 'jwt='.$jwt;
        }

        $config = '&config.channelLastN='.get_config('mod_jitsi', 'channellastcam');
        $config .= '&config.startWithAudioMuted=true';
        $config .= '&config.startWithVideoMuted=true';
        if (get_config('mod_jitsi', 'deeplink') == 0) {
            $config .= '&config.disableDeepLinking=true';
        }
        $config .= '&config.disableProfile=true';
        $config .= '&config.toolbarButtons='.urlencode($buttons);
        $data['config'] = $config;
        $data['displayName'] = 'userInfo.displayName="'.$nombre.'"';

        $interfaceconfig .= '&interfaceConfig.SHOW_JITSI_WATERMARK=false';
        $interfaceconfig .= '&interfaceConfig.JITSI_WATERMARK_LINK='.urlencode("'".get_config('mod_jitsi', 'watermarklink')."'");
        $data['interface_config'] = $interfaceconfig;

        $data['is_desktop'] = $args['appisdesktop'];
        global $DB;
        $serverid = get_config('mod_jitsi', 'server');
        $domain = 'meet.jit.si';
        if (!empty($serverid)) {
            $srv = $DB->get_record('jitsi_servers', ['id' => $serverid]);
            if ($srv && !empty($srv->domain)) {
                $domain = $srv->domain;
            }
        }
        $data['jitsi_domain'] = $domain;
        $data['room'] = $sessionnorm;

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template("mod_jitsi/mobile_session_view_page_$foldername", $data),
                ],
            ],
            'javascript' => '',
            'otherdata' => json_encode($data),
        ];
    }
}

/**
 * Sanitize strings
 * @param string $string - The string to sanitize.
 * @param boolean $forcelowercase - Force the string to lowercase?
 * @param boolean $anal - If set to *true*, will remove all non-alphanumeric characters.
 */
function string_sanitize($string, $forcelowercase = true, $anal = false) {
    $strip = ["~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")",
            "_", "=", "+", "[", "{", "]", "}", "\\", "|", ";", ":", "\"",
            "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ".", ">", "/", "?",
        ];
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;
    return ($forcelowercase) ?
        (function_exists('mb_strtolower')) ?
            mb_strtolower($clean, 'UTF-8') :
            strtolower($clean) :
        $clean;
}
