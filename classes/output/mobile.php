<?php
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
        $courseid = $args['courseid'];

        if ($id) {
            $cm = get_coursemodule_from_id('jitsi', $id, 0, false, MUST_EXIST);
            $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
            $jitsi = $DB->get_record('jitsi', array('id' => $cm->instance), '*', MUST_EXIST);
        } else {
            print_error('missingparam');
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

            // Make titles more mobile friendly.
            $intro = str_replace(array('<h2', '<h3'),'<h1', $intro);
            $intro = str_replace(array('</h2>', '</h3>'),'</h1>', $intro);

            $intro = str_replace(array('<h4', '<h5', '<h6'), '<h2', $intro);
            $intro = str_replace(array('</h4>', '</h5>', '</h6>'), '</h2>', $intro);

        } else {
            $intro = "";
        }

        $moderation = false;
        if (has_capability('mod/jitsi:moderation', $context)) {
            $moderation = true;
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
                    $sesparam .= $jitsi->id.$optionsseparator[$CFG->jitsi_separator];
                } else if ($allowed[$i] == 2) {
                    $sesparam .= string_sanitize($jitsi->name).$optionsseparator[$CFG->jitsi_separator];
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
        // Make titles more mobile friendly.
        if($CFG->jitsi_help) {
            $help = str_replace(array('<h2', '<h3'),'<h1', $CFG->jitsi_help);
            $help = str_replace(array('</h2>', '</h3>'),'</h1>', $help);

            $help = str_replace(array('<h4', '<h5', '<h6'), '<h2', $help);
            $help = str_replace(array('</h4>', '</h5>', '</h6>'), '</h2>', $help);
        }

        $avatar = $CFG->wwwroot.'/user/pix.php/'.$USER->id.'/f1.jpg';
        $data = array(
            'avatar' => $avatar,
            'nom' => $nom,
            'ses' => $sesparam,
            'courseid' => $course->id,
            'cmid' => $id,
            't' => $moderation,
            'help' => $help,
            'intro' => $intro,
            'title' => format_string($jitsi->name),
            'room' => str_replace(array(' ', ':', '"'), '', $sesparam),
            'minpretime' => $jitsi->minpretime,
        );

        $today = getdate();
        if ($today[0] > (($jitsi->timeopen) - ($jitsi->minpretime * 60))||
            (in_array('editingteacher', $rolestr) == 1)) {
                $data['nostart_show'] = false;
        } else {
            $data['nostart_show'] = true;
        }

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template('mod_jitsi/mobile_presession_view_page', $data),
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

        $courseid = $args['courseid'];
        $cmid = $args['cmid'];
        $nombre = $args['nom'];
        $session = $args['ses'];
        $sessionnorm = str_replace(array(' ', ':', '"'), '', $session);
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

        if ($CFG->jitsi_app_id != null && $CFG->jitsi_secret != null) {
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
        }

        $streamingoption = '';
        if ($teacher == true && $CFG->jitsi_livebutton == 1) {
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

        $invite = '';
        if ($CFG->jitsi_invitebuttons == 1) {
            $invite = 'invite';
        }

        $buttons = "['microphone','camera','closedcaptions','".$desktop."','fullscreen','fodeviceselection','hangup','profile','chat','recording','".$streamingoption."','etherpad','".$youtubeoption."','settings','raisehand','videoquality','filmstrip','".$invite."','feedback','stats','shortcuts','tileview','".$bluroption."','download','help','mute-everyone','".$security."']";

        $data = array();
        $data['jwt'] = "";

        if ($CFG->jitsi_app_id != null && $CFG->jitsi_secret != null) {
            $data['jwt'] = '?jwt='.$jwt;
        }

        $config = 'config.channelLastN='.$CFG->jitsi_channellastcam;
        $config .= '&config.startWithAudioMuted=true';
        $config .= '&config.startWithVideoMuted=true';
        $config .= '&config.disableDeepLinking=true';
        $data['config'] = $config;

        $interfaceConfig = 'interfaceConfig.TOOLBAR_BUTTONS='.urlencode($buttons);
        $interfaceConfig .= '&interfaceConfig.SHOW_JITSI_WATERMARK=true';
        $interfaceConfig .= '&interfaceConfig.JITSI_WATERMARK_LINK = '.urlencode("'".$CFG->jitsi_watermarklink."'");
        $data['interface_config']=$interfaceConfig;

        $data['is_ios'] = $args['appplatform'] == 'darwin' ? true : false;
        $data['is_desktop'] = $args['appisdesktop'];
        $data['jitsi_domain'] = $CFG->jitsi_domain;
        $data['room'] = $sessionnorm;

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template('mod_jitsi/mobile_session_view_page', $data),
                ],
            ],
            'javascript' => '',
            'otherdata' => json_encode($data),
        ];
    }
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
