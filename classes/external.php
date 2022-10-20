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
 * Jitsi module external API
 *
 * @package    mod_jitsi
 * @category   external
 * @copyright  2021 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/jitsi/lib.php');

/**
 * Jitsi module external API
 *
 * @package    mod_jitsi
 * @category   external
 * @copyright  2021 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_jitsi_external extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function view_jitsi_parameters() {
        return new external_function_parameters(
            array(
                'cmid' => new external_value(PARAM_INT, 'course module instance id')
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function state_record_parameters() {
        return new external_function_parameters(
            array('jitsi' => new external_value(PARAM_INT, 'Jitsi session id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                  'state' => new external_value(PARAM_TEXT, 'State', VALUE_REQUIRED, '', NULL_NOT_ALLOWED))
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function create_stream_parameters() {
        return new external_function_parameters(
            array('session' => new external_value(PARAM_TEXT, 'Session object from google', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                  'jitsi' => new external_value(PARAM_INT, 'Jitsi session id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                  'userid' => new external_value(PARAM_INT, 'User id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED))
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function enter_session_parameters() {
        return new external_function_parameters(
            array('jitsi' => new external_value(PARAM_INT, 'Jitsi session id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'user' => new external_value(PARAM_INT, 'User id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED))
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function press_record_button_parameters() {
        return new external_function_parameters(
            array('jitsi' => new external_value(PARAM_INT, 'Jitsi session id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'user' => new external_value(PARAM_INT, 'User id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'cmid' => new external_value(PARAM_INT, 'Course Module id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED))
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function delete_record_youtube_parameters() {
        return new external_function_parameters(
            array('idsource' => new external_value(PARAM_INT, 'Record session id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED))
        );
    }

    /**
     * Delete Video from youtube when jitsi get an error
     *
     * @param int $idsource Source record id
     * @return external_function_parameters
     */
    public static function delete_record_youtube($idsource) {
        global $CFG, $DB, $PAGE;
        if (!file_exists(__DIR__ . '/../api/vendor/autoload.php')) {
            throw new \Exception('Api client not found on '.$CFG->wwwroot.'/mod/jitsi/api/vendor/autoload.php');
        }

        require_once(__DIR__ . '/../api/vendor/autoload.php');

        $client = new Google_Client();

        $client->setClientId($CFG->jitsi_oauth_id);
        $client->setClientSecret($CFG->jitsi_oauth_secret);

        $tokensessionkey = 'token-' . "https://www.googleapis.com/auth/youtube";
        $source = $DB->get_record('jitsi_source_record', array('id' => $idsource));
        $account = $DB->get_record('jitsi_record_account', array('id' => $source->account));

        $_SESSION[$tokensessionkey] = $account->clientaccesstoken;
        $client->setAccessToken($_SESSION[$tokensessionkey]);
        $t = time();
        $timediff = $t - $account->tokencreated;
        if ($timediff > 3599) {
            $newaccesstoken = $client->fetchAccessTokenWithRefreshToken($account->clientrefreshtoken);
            try {
                $account->clientaccesstoken = $newaccesstoken["access_token"];
                $newrefreshaccesstoken = $client->getRefreshToken();
                $newrefreshaccesstoken = $client->getRefreshToken();
                $account->clientrefreshtoken = $newrefreshaccesstoken;
                $account->tokencreated = time();
            } catch (Google_Service_Exception $e) {
                if ($account->inuse == 1) {
                    $account->inuse = 0;
                }
                $account->clientaccesstoken = null;
                $account->clientrefreshtoken = null;
                $account->tokencreated = 0;
                $DB->update_record('jitsi_record_account', $account);
                $client->revokeToken();
                return false;
            } catch (Google_Exception $e) {
                if ($account->inuse == 1) {
                    $account->inuse = 0;
                }
                $account->clientaccesstoken = null;
                $account->clientrefreshtoken = null;
                $account->tokencreated = 0;
                $DB->update_record('jitsi_record_account', $account);
                $client->revokeToken();
                return false;
            }
        }
        $youtube = new Google_Service_YouTube($client);
        try {
            $listresponse = $youtube->videos->listVideos("snippet", array('id' => $source->link));
        } catch (Google_Service_Exception $e) {
            if ($account->inuse == 1) {
                $account->inuse = 0;
            }
            $account->clientaccesstoken = null;
            $account->clientrefreshtoken = null;
            $account->tokencreated = 0;
            $DB->update_record('jitsi_record_account', $account);
            $client->revokeToken();
            return false;
            throw new \Exception("exception".$e->getMessage());
        } catch (Google_Exception $e) {
            if ($account->inuse == 1) {
                $account->inuse = 0;
            }
            $account->clientaccesstoken = null;
            $account->clientrefreshtoken = null;
            $account->tokencreated = 0;
            $DB->update_record('jitsi_record_account', $account);
            $client->revokeToken();
            return false;
            throw new \Exception("exception".$e->getMessage());
        }
        if ($listresponse['items'] != []) {
            if ($client->getAccessToken($idsource)) {
                try {
                    $youtube->videos->delete($source->link);
                    delete_jitsi_record($idsource);
                } catch (Google_Service_Exception $e) {
                    throw new \Exception("exception".$e->getMessage());
                } catch (Google_Exception $e) {
                    throw new \Exception("exception".$e->getMessage());
                }
            }
        }
        return true;
    }

    /**
     * Returns description of method parameters
     *
     * @param int $jitsi Jitsi session id
     * @param int $user User id
     */
    public static function enter_session($jitsi, $user) {
        global $DB;
        $event = \mod_jitsi\event\jitsi_session_enter::create(array(
            'objectid' => $PAGE->cm->instance,
            'context' => $PAGE->context,
          ));
          $jitsiob = $DB->get_record('jitsi', array('id' => $jitsi));
          $event->add_record_snapshot('course', $jitsi->course);
          $event->add_record_snapshot('jitsi', $jitsiob);
          $event->trigger();
    }

    /**
     * Returns description of method parameters
     *
     * @param int $jitsi Jitsi session id
     * @param int $user User id
     * @param int $cmid Course Module id
     */
    public static function press_record_button($jitsi, $user, $cmid) {
          global $DB;
          $context = context_module::instance($cmid);
          $event = \mod_jitsi\event\jitsi_press_record_button::create(array(
              'objectid' => $jitsi,
              'context' => $context,
          ));
          $event->add_record_snapshot('course', $jitsi->course);
          $event->add_record_snapshot('jitsi', $jitsiob);
          $event->trigger();
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function press_button_cam_parameters() {
        return new external_function_parameters(
            array('jitsi' => new external_value(PARAM_INT, 'Jitsi session id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'user' => new external_value(PARAM_INT, 'User id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'cmid' => new external_value(PARAM_INT, 'Course Module id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED))
        );
    }

    /**
     * Returns description of method parameters
     *
     * @param int $jitsi Jitsi session id
     * @param int $user User id
     * @param int $cmid Course Module id
     */
    public static function press_button_cam($jitsi, $user, $cmid) {
        global $DB;
        $context = context_module::instance($cmid);
        $event = \mod_jitsi\event\jitsi_press_button_cam::create(array(
            'objectid' => $jitsi,
            'context' => $context,
        ));
        $event->add_record_snapshot('course', $jitsi->course);
        $event->add_record_snapshot('jitsi', $jitsiob);
        $event->trigger();
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function press_button_cam_returns() {
        return new external_value(PARAM_TEXT, 'Press cam button');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function press_button_desktop_parameters() {
        return new external_function_parameters(
            array('jitsi' => new external_value(PARAM_INT, 'Jitsi session id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'user' => new external_value(PARAM_INT, 'User id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'cmid' => new external_value(PARAM_INT, 'Course Module id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED))
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return send_error_parameters
     */
    public static function send_error_parameters() {
        return new external_function_parameters(
            array('jitsi' => new external_value(PARAM_INT, 'Jitsi session id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'user' => new external_value(PARAM_INT, 'User id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'error' => new external_value(PARAM_TEXT, 'Error', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'cmid' => new external_value(PARAM_INT, 'Course Module id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED))
        );
    }

    /**
     * Returns description of method parameters
     *
     * @param int $jitsi Jitsi session id
     * @param int $user User id
     * @param string $error Error message
     * @param int $cmid Course Module id
     */
    public static function send_error($jitsi, $user, $error, $cmid) {
        global $PAGE, $DB, $CFG;
        $PAGE->set_context(context_module::instance($cmid));
        $admins = get_admins();
        $user = $DB->get_record('user', array('id' => $user));
        $mensaje = "El usuario ".$user->firstname." ".$user->lastname.
            " ha tenido un error al intentar grabar la sesión de jitsi con id ".$jitsi."\nInfo:\n".$error."\n
        Para más información, accede a la sesión de jitsi y mira el log.\n
        URL: ".$CFG->wwwroot."/mod/jitsi/view.php?id=".$cmid."\n
        Nombre de la sesión: ".$DB->get_record('jitsi', array('id' => $jitsi))->name."\n
        Curso: ".$DB->get_record('course', array('id' => $DB->get_record('jitsi', array('id' => $jitsi))->course))->fullname."\n
        Usuario: ".$user->username."\n";
        foreach ($admins as $admin) {
            email_to_user($admin, $admin, "ERROR JITSI! el usuario: "
                .$user->username." ha tenido un error en el jitsi: ".$jitsi, $mensaje);
        }
    }

    /**
     * Returns description of method parameters
     *
     * @param int $jitsi Jitsi session id
     * @param int $user User id
     * @param int $cmid Course Module id
     */
    public static function press_button_desktop($jitsi, $user, $cmid) {
        global $DB;
        $context = context_module::instance($cmid);
        $event = \mod_jitsi\event\jitsi_press_button_desktop::create(array(
            'objectid' => $jitsi,
            'context' => $context,
        ));
        $event->add_record_snapshot('course', $jitsi->course);
        $event->add_record_snapshot('jitsi', $jitsiob);
        $event->trigger();
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function press_button_desktop_returns() {
        return new external_value(PARAM_TEXT, 'Press desktop button');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function press_button_end_parameters() {
        return new external_function_parameters(
            array('jitsi' => new external_value(PARAM_INT, 'Jitsi session id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'user' => new external_value(PARAM_INT, 'User id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'cmid' => new external_value(PARAM_INT, 'Course Module id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED))
        );
    }

    /**
     * Returns description of method parameters
     *
     * @param int $jitsi Jitsi session id
     * @param int $user User id
     * @param int $cmid Course Module id
     */
    public static function press_button_end($jitsi, $user, $cmid) {
        global $DB;
        $context = context_module::instance($cmid);
        $event = \mod_jitsi\event\jitsi_press_button_end::create(array(
            'objectid' => $jitsi,
            'context' => $context,
        ));
        $event->add_record_snapshot('course', $jitsi->course);
        $event->add_record_snapshot('jitsi', $jitsiob);
        $event->trigger();
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function press_button_microphone_returns() {
        return new external_value(PARAM_TEXT, 'Press microphone button');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function press_button_microphone_parameters() {
        return new external_function_parameters(
            array('jitsi' => new external_value(PARAM_INT, 'Jitsi session id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'user' => new external_value(PARAM_INT, 'User id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'cmid' => new external_value(PARAM_INT, 'Course Module id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED))
        );
    }

    /**
     * Returns description of method parameters
     *
     * @param int $jitsi Jitsi session id
     * @param int $user User id
     * @param int $cmid Course Module id
     */
    public static function press_button_microphone($jitsi, $user, $cmid) {
        global $DB;
        $context = context_module::instance($cmid);
        $event = \mod_jitsi\event\jitsi_press_button_microphone::create(array(
            'objectid' => $jitsi,
            'context' => $context,
        ));
        $event->add_record_snapshot('course', $jitsi->course);
        $event->add_record_snapshot('jitsi', $jitsiob);
        $event->trigger();
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function press_button_end_returns() {
        return new external_value(PARAM_TEXT, 'Press end button');
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function enter_session_returns() {
        return new external_value(PARAM_TEXT, 'Enter session');
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function press_record_button_returns() {
        return new external_value(PARAM_TEXT, 'Press record button');
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function participating_session_parameters() {
        return new external_function_parameters(
            array('jitsi' => new external_value(PARAM_INT, 'Jitsi session id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'user' => new external_value(PARAM_INT, 'User id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'cmid' => new external_value(PARAM_INT, 'Course Module id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED))
        );
    }

    /**
     * Register a participation in a Jitsi session
     * @param int $jitsi Jitsi session id
     * @param int $user User id
     * @param int $cmid Course Module id
     */
    public static function participating_session($jitsi, $user, $cmid) {
        global $DB;
        $context = context_module::instance($cmid);
        $event = \mod_jitsi\event\jitsi_session_participating::create(array(
            'objectid' => $jitsi,
            'context' => $context,
        ));
        $event->add_record_snapshot('course', $jitsi->course);
        $event->add_record_snapshot('jitsi', $jitsiob);
        $event->trigger();
        update_completition(get_coursemodule_from_id('jitsi', $cmid, 0, false, MUST_EXIST));
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function participating_session_returns() {
        return new external_value(PARAM_TEXT, 'Participating session');
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function delete_record_youtube_returns() {
        return new external_value(PARAM_TEXT, 'Video deleted');
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function send_error_returns() {
        return new external_value(PARAM_TEXT, 'Error sent');
    }

    /**
     * Trigger the course module viewed event.
     *
     * @param int $cmid the course module instance id
     * @return array of warnings and status result
     * @throws moodle_exception
     */
    public static function view_jitsi($cmid) {
        global $DB;

        $params = self::validate_parameters(self::view_jitsi_parameters(),
                                            array(
                                                'cmid' => $cmid
                                            )
        );
        $warnings = array();

        $cm = get_coursemodule_from_id('jitsi', $cmid, 0, false, MUST_EXIST);

        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/jitsi:view', $context);

        $event = \mod_jitsi\event\course_module_viewed::create(
            array(
                'objectid' => $cm->instance,
                'context' => $context,
            )
        );
        $event->add_record_snapshot('course', $course);
        $event->add_record_snapshot($cm->modname, $jitsi);
        $event->trigger();

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns record state
     * @param int $jitsi Jitsi session id
     * @param string $state State
     * @return array
     */
    public static function state_record($jitsi, $state) {
        global $USER, $DB;

        $params = self::validate_parameters(self::state_record_parameters(),
                array('jitsi' => $jitsi, 'state' => $state));
        $jitsiob = $DB->get_record('jitsi', array('id' => $jitsi));
        if ($state == 1) {
            $jitsiob->recording = 'recording';
        } else {
            $jitsiob->recording = 'stop';
        }
        $DB->update_record('jitsi', $jitsiob);
        return 'recording'.$jitsiob->recording;
    }

    /**
     * Start stream with youtube
     * @param int $session session
     * @param int $jitsi Jitsi session id
     * @param int $userid User id
     * @return array result
     */
    public static function create_stream($session, $jitsi, $userid) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::create_stream_parameters(),
                array('session' => $session, 'jitsi' => $jitsi, 'userid' => $userid));

        if (!file_exists(__DIR__ . '/../api/vendor/autoload.php')) {
            throw new \Exception('Api client not found on '.$CFG->wwwroot.'/mod/jitsi/api/vendor/autoload.php');
        }

        require_once(__DIR__ . '/../api/vendor/autoload.php');

        $client = new Google_Client();
        $client->setClientId($CFG->jitsi_oauth_id);
        $client->setClientSecret($CFG->jitsi_oauth_secret);

        $tokensessionkey = 'token-' . "https://www.googleapis.com/auth/youtube";

        $account = $DB->get_record('jitsi_record_account', array('inuse' => 1));

        $_SESSION[$tokensessionkey] = $account->clientaccesstoken;

        $client->setAccessToken($_SESSION[$tokensessionkey]);

        $t = time();
        $timediff = $t - $token->tokencreated;

        if ($timediff > 3599) {
            $newaccesstoken = $client->fetchAccessTokenWithRefreshToken($account->clientrefreshtoken);

            $account->clientaccesstoken = $newaccesstoken["access_token"];
            $newrefreshaccesstoken = $client->getRefreshToken();
            $account->clientrefreshtoken = $newrefreshaccesstoken;

            $account->tokencreated = $t;
            $DB->update_record('jitsi_record_account', $account);
        }
        $youtube = new Google_Service_YouTube($client);

        if ($client->getAccessToken()) {
            try {
                $broadcastsnippet = new Google_Service_YouTube_LiveBroadcastSnippet();
                $testdate = time();

                $broadcastsnippet->setTitle("Record ".date('Y-m-d\T H:i A', $testdate));
                $broadcastsnippet->setScheduledStartTime(date('Y-m-d\TH:i:s', $testdate));

                $status = new Google_Service_YouTube_LiveBroadcastStatus();
                $status->setPrivacyStatus('unlisted');
                $status->setSelfDeclaredMadeForKids('false');
                $contentdetails = new Google_Service_YouTube_LiveBroadcastContentDetails();
                $contentdetails->setEnableAutoStart(true);
                $contentdetails->setEnableAutoStop(true);

                $broadcastinsert = new Google_Service_YouTube_LiveBroadcast();
                $broadcastinsert->setSnippet($broadcastsnippet);
                $broadcastinsert->setStatus($status);
                $broadcastinsert->setKind('youtube#liveBroadcast');
                $broadcastinsert->setContentDetails($contentdetails);
                sleep(rand(1, 2));
                $broadcastsresponse = $youtube->liveBroadcasts->insert('snippet,status,contentDetails', $broadcastinsert, array());

                $streamsnippet = new Google_Service_YouTube_LiveStreamSnippet();
                $streamsnippet->setTitle("Record ".date('l jS \of F', $testdate));

                $cdn = new Google_Service_YouTube_CdnSettings();
                $cdn->setIngestionType('rtmp');
                $cdn->setResolution("variable");
                $cdn->setFrameRate("variable");

                $streaminsert = new Google_Service_YouTube_LiveStream();
                $streaminsert->setSnippet($streamsnippet);
                $streaminsert->setCdn($cdn);
                $streaminsert->setKind('youtube#liveStream');
                sleep(rand(1, 2));
                $streamsresponse = $youtube->liveStreams->insert('snippet,cdn', $streaminsert, array());
                sleep(rand(1, 2));
                $bindbroadcastresponse = $youtube->liveBroadcasts->bind($broadcastsresponse['id'], 'id,contentDetails',
                    array('streamId' => $streamsresponse['id'], ));
            } catch (Google_Service_Exception $e) {
                throw new \Exception("exception".$session.'-'.$e->getMessage());
            } catch (Google_Exception $e) {
                throw new \Exception("exception".$session.'-'.$e->getMessage());
            }
        }
        $account = $DB->get_record('jitsi_record_account', array('inuse' => 1));

        $source = new stdClass();
        $source->link = $broadcastsresponse['id'];
        $source->account = $account->id;
        $source->timecreated = time();
        $source->userid = $userid;
        $jitsiob = $DB->get_record('jitsi', array('id' => $jitsi));

        $record = new stdClass();
        $record->jitsi = $jitsi;
        $record->source = $DB->insert_record('jitsi_source_record', $source);
        $record->deleted = 0;
        $record->visible = 1;
        $record->name = get_string('recordtitle', 'jitsi').' '.mb_substr($jitsiob->name, 0, 30);

        $DB->insert_record('jitsi_record', $record);

        $result = array();
        $result['stream'] = $streamsresponse['cdn']['ingestionInfo']['streamName'];
        $result['idsource'] = $record->source;
        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function state_record_returns() {
        return new external_value(PARAM_TEXT, 'State record session');
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function view_jitsi_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function create_stream_returns() {
        return new external_single_structure(
            array(
                'stream' => new external_value(PARAM_TEXT, 'stream'),
                'idsource' => new external_value(PARAM_INT, 'source instance id')
            )
        );
    }
}
