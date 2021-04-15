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
 * Library of interface functions and constants for module jitsi
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the jitsi specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_jitsi
 * @copyright  2019 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function jitsi_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the jitsi into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $jitsi Submitted data from the form in mod_form.php
 * @param mod_jitsi_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted jitsi record
 */
function jitsi_add_instance($jitsi,  $mform = null) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/mod/jitsi/locallib.php');

    $jitsi->timecreated = time();
    $cmid       = $jitsi->coursemodule;

    $jitsi->id = $DB->insert_record('jitsi', $jitsi);
    jitsi_update_calendar($jitsi, $cmid);

    return $jitsi->id;
}

/**
 * Updates an instance of the jitsi in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $jitsi An object from the form in mod_form.php
 * @param mod_jitsi_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function jitsi_update_instance($jitsi,  $mform = null) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/mod/jitsi/locallib.php');

    $jitsi->timemodified = time();
    $jitsi->id = $jitsi->instance;
    $cmid       = $jitsi->coursemodule;

    $result = $DB->update_record('jitsi', $jitsi);
    jitsi_update_calendar($jitsi, $cmid);

    return $result;
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every assignment event in the site is checked, else
 * only assignment events belonging to the course specified are checked.
 *
 * @param int $courseid
 * @param int|stdClass $instance Jitsi module instance or ID.
 * @param int|stdClass $cm Course module object or ID.
 * @return bool
 */
function jitsi_refresh_events($courseid = 0, $instance = null, $cm = null) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/jitsi/locallib.php');

    if (isset($instance)) {
        if (!is_object($instance)) {
            $instance = $DB->get_record('jitsi', array('id' => $instance), '*', MUST_EXIST);
        }
        if (isset($cm)) {
            if (!is_object($cm)) {
                $cm = (object)array('id' => $cm);
            }
        } else {
            $cm = get_coursemodule_from_instance('jitsi', $instance->id);
        }
        jitsi_update_calendar($instance, $cm->id);
        return true;
    }

    if ($courseid) {
        if (!is_numeric($courseid)) {
            return false;
        }
        if (!$jitsis = $DB->get_records('jitsi', array('course' => $courseid))) {
            return true;
        }
    } else {
        return true;
    }

    foreach ($jitsis as $jitsi) {
        $cm = get_coursemodule_from_instance('jitsi', $jitsi->id);
        jitsi_update_calendar($jitsi, $cm->id);
    }

    return true;
}

/**
 * Removes an instance of the jitsi from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function jitsi_delete_instance($id) {
    global $CFG, $DB;

    if (! $jitsi = $DB->get_record('jitsi', array('id' => $id))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records('jitsi', array('id' => $jitsi->id))) {
        $result = false;
    }

    return $result;
}

function jitsi_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $DB, $CFG, $USER;
    if ($CFG->jitsi_privatesessions == 1) {
        $urlparams = array('user' => $user->id);
        $url = new moodle_url('/mod/jitsi/viewpriv.php', $urlparams);
        $category = new core_user\output\myprofile\category('jitsi',
            get_string('jitsi', 'jitsi'), null);
        $tree->add_category($category);
        if ($iscurrentuser == 0) {
            $node = new core_user\output\myprofile\node('jitsi', 'jitsi',
                get_string('privatesession', 'jitsi', $user->firstname), null, $url);
        } else {
            $node = new core_user\output\myprofile\node('jitsi', 'jitsi',
                get_string('myprivatesession', 'jitsi'), null, $url);
        }
        $tree->add_node($node);
    }
    return true;
}

/**
 * Creates a string with the settings for a conference which can be
 * appended to a conference link to set specific options like in the
 * external api.
 * 
 * @param meetingId string id to join the meeting
 * @param name Display name of the user who want's to join
 * @param jwt string | null JWT-Token
 */
function jitsi_get_url_parameters($meetingId, $name, $jwt) {
    global $CFG;

    $configString = '';

    $toolbarButtons = ['microphone', 'camera', 'desktop', 'fullscreen', 'hangup', 'fodeviceselection', 
    'chat', 'profile', 'recording', 'etherpad', 'settings', 'raisehand', 'videoquality', 'stats', 'shortcuts',
    'help', 'mute-everyone', 'mute-video-everyone'];

    if ($CFG->jitsi_securitybutton) {
        $toolbarButtons[] = 'security';
    }

    if ($CFG->jitsi_invitebuttons) {
        $toolbarButtons[] = 'invite';
    } else {
        $configString = 'config.disableInviteFunctions=true&';
    }

    if ($CFG->jitsi_shareyoutube) {
        $toolbarButtons[] = 'livestreaming';
    }

    if ($CFG->jitsi_blurbutton) {
        $toolbarButtons[] = 'select-background';
    }

    if ($CFG->jitsi_shareyoutube) {
        $toolbarButtons[] = 'sharevideo';
    }

    $configString .= "config.startWithAudioMuted=true&config.startWithVideoMuted=true&userInfo.displayName=%22" . $name . '%22&';
    $configString .= "interfaceConfig.TOOLBAR_BUTTONS=" . urlencode(json_encode($toolbarButtons)) . "";

    if (!$jwt) {
        return 'https://' . $CFG->jitsi_domain . '/' . $meetingId . '#' . $configString;
    } else {
        return 'https://' . $CFG->jitsi_domain . '/' . $meetingId . '?jwt=' . $jwt .  '#' . $configString;
    }
}

/**
 * Returns a JWT-Token
 * based on the algorythm in session.php / sessionpriv.php
 * 
 * @param affiliation string Role of the user owner | member
 * @param avatar string | null URL to the user avatar
 * @param name string (Display) name of the user
 * @param session string Session id for the conference
 * @param moderator boolean whether the user is moderator
 */
function jitsi_get_jwt_token($affiliation, $avatar, $name, $session, $moderator) {
    global $CFG;

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
            "name" => $name,
            "email" => "",
            "id" => ""
          ],
          "group" => ""
        ],
        "aud" => "jitsi",
        "iss" => $CFG->jitsi_app_id,
        "sub" => $CFG->jitsi_domain,
        "room" => urlencode($session),
        "exp" => time() + 24 * 3600,
        "moderator" => $moderator
      
      ], JSON_UNESCAPED_SLASHES);
      $base64urlpayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
      
      $secret = $CFG->jitsi_secret;
      $signature = hash_hmac('sha256', $base64urlheader . "." . $base64urlpayload, $secret, true);
      $base64urlsignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
      
      return $base64urlheader . "." . $base64urlpayload . "." . $base64urlsignature;
}