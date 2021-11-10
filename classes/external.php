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

/**
 * Jitsi module external API
 *
 * @package    mod_jitsi
 * @category   external
 * @copyright  2021 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_jitsi_external extends external_api{
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
                  'jitsi' => new external_value(PARAM_INT, 'Jitsi session id', VALUE_REQUIRED, '', NULL_NOT_ALLOWED))
        );
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

    public static function create_stream($session, $jitsi) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::create_stream_parameters(),
                array('session' => $session, 'jitsi' => $jitsi));

        if (!file_exists(__DIR__ . '/../api/vendor/autoload.php')) {
            throw new \Exception('Api client not found on '.$CFG->wwwroot.'/mod/jitsi/api/vendor/autoload.php');
        }

        require_once(__DIR__ . '/../api/vendor/autoload.php');

        $client = new Google_Client();
        $client->setClientId($CFG->jitsi_oauth_id);
        $client->setClientSecret($CFG->jitsi_oauth_secret);

        $tokensessionkey = 'token-' . "https://www.googleapis.com/auth/youtube";

        // $sql = 'select * from {jitsi_record_acount} limit 1';
        // $acount = $DB->get_record_sql($sql);
        $acount = $DB->get_record('jitsi_record_acount', array('inuse'=>1));


        $_SESSION[$tokensessionkey] = $acount->clientaccesstoken;

        $client->setAccessToken($_SESSION[$tokensessionkey]);

        $t = time();
        $timediff = $t - $token->tokencreated;

        if ($timediff > 3599) {
            $newaccesstoken = $client->fetchAccessTokenWithRefreshToken($acount->clientrefreshtoken);

            $acount->clientaccesstoken = $newaccesstoken["access_token"];
            $newrefreshaccesstoken = $client->getRefreshToken();
            $acount->clientrefreshtoken = $newrefreshaccesstoken;

            $acount->tokencreated = $t;
            $DB->update_record('jitsi_record_acount', $acount);
        }
        $youtube = new Google_Service_YouTube($client);

        if ($client->getAccessToken()) {
            try {
                $broadcastsnippet = new Google_Service_YouTube_LiveBroadcastSnippet();
                $testdate = time();
                $broadcastsnippet->setTitle("Record: ".$session." (".date('l jS \of F', $testdate).")");
                $broadcastsnippet->setScheduledStartTime(date('Y-m-d\TH:i:s', $testdate));

                $status = new Google_Service_YouTube_LiveBroadcastStatus();
                $status->setPrivacyStatus('unlisted');
                $status->setSelfDeclaredMadeForKids('false');
                $contentdetails = new Google_Service_YouTube_LiveBroadcastContentDetails();
                $contentdetails->setEnableAutoStart(true);
                $contentdetails->setEnableAutoStop(true);
                $contentdetails->setEnableEmbed(true);

                $broadcastinsert = new Google_Service_YouTube_LiveBroadcast();
                $broadcastinsert->setSnippet($broadcastsnippet);
                $broadcastinsert->setStatus($status);
                $broadcastinsert->setKind('youtube#liveBroadcast');
                $broadcastinsert->setContentDetails($contentdetails);

                $broadcastsresponse = $youtube->liveBroadcasts->insert('snippet,status,contentDetails', $broadcastinsert, array());

                $streamsnippet = new Google_Service_YouTube_LiveStreamSnippet();
                $streamsnippet->setTitle($session);

                $cdn = new Google_Service_YouTube_CdnSettings();
                $cdn->setIngestionType('rtmp');
                $cdn->setResolution("variable");
                $cdn->setFrameRate("variable");

                $streaminsert = new Google_Service_YouTube_LiveStream();
                $streaminsert->setSnippet($streamsnippet);
                $streaminsert->setCdn($cdn);
                $streaminsert->setKind('youtube#liveStream');

                $streamsresponse = $youtube->liveStreams->insert('snippet,cdn', $streaminsert, array());

                $bindbroadcastresponse = $youtube->liveBroadcasts->bind($broadcastsresponse['id'], 'id,contentDetails',
                    array('streamId' => $streamsresponse['id'], ));
            } catch (Google_Service_Exception $e) {
                throw new \Exception("exception".$e->getMessage());
            } catch (Google_Exception $e) {
                throw new \Exception("exception".$e->getMessage());
            }
        }
        // $sql = 'select * from {jitsi_record_acount} limit 1';
        // $acount = $DB->get_record_sql($sql);
        $acount = $DB->get_record('jitsi_record_acount', array('inuse'=>1));


        $source = new stdClass();
        $source->link = $broadcastsresponse['id'];
        $source->acount = $acount->id;
        $source->timecreated = time();
        $jitsiob = $DB->get_record('jitsi', array('id' => $jitsi));

        $record = new stdClass();
        $record->jitsi = $jitsi;
        $record->source = $DB->insert_record('jitsi_source_record', $source);
        $record->deleted = 0;
        $record->visible = 1;
        $record->name = get_string('record', 'jitsi').' '.$jitsiob->name;

        $DB->insert_record('jitsi_record', $record);

        return $streamsresponse['cdn']['ingestionInfo']['streamName'];
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
        return new external_value(PARAM_TEXT, 'stream');
    }
}
