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
 * Web service local plugin template external functions and service definitions.
 *
 * @package    mod_jitsi
 * @copyright  2021 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
defined('MOODLE_INTERNAL') || die();

$functions = [
        'mod_jitsi_state_record' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'state_record',
                'classpath' => 'mod/jitsi/externallib.php',
                'description' => 'State session recording',
                'type' => 'write',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_participating_session' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'participating_session',
                'classpath' => 'mod/jitsi/classes/external.php',
                'description' => 'State session recording',
                'type' => 'write',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_press_record_button' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'press_record_button',
                'classpath' => 'mod/jitsi/classes/external.php',
                'description' => 'User press record button',
                'type' => 'write',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_press_button_cam' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'press_button_cam',
                'classpath' => 'mod/jitsi/classes/external.php',
                'description' => 'User press a camera button',
                'type' => 'write',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_press_button_desktop' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'press_button_desktop',
                'classpath' => 'mod/jitsi/classes/external.php',
                'description' => 'User press a desktop button',
                'type' => 'write',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_press_button_end' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'press_button_end',
                'classpath' => 'mod/jitsi/classes/external.php',
                'description' => 'User press a end button',
                'type' => 'write',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_press_button_microphone' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'press_button_microphone',
                'classpath' => 'mod/jitsi/classes/external.php',
                'description' => 'User press a microphone button',
                'type' => 'write',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_create_stream' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'create_stream',
                'classpath' => 'mod/jitsi/classes/external.php',
                'description' => 'Create a stream',
                'type' => 'write',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_view_jitsi' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'view_jitsi',
                'description' => 'Trigger the course module viewed event.',
                'type' => 'write',
                'capabilities' => 'mod/jitsi:view',
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_delete_record_youtube' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'delete_record_youtube',
                'description' => 'Delete video from youtube when problem',
                'type' => 'read',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_send_error' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'send_error',
                'description' => 'Send error to admin',
                'type' => 'read',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_stop_stream' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'stop_stream',
                'description' => 'Stop stream',
                'type' => 'write',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_stop_stream_byerror' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'stop_stream_byerror',
                'description' => 'Stop stream by error',
                'type' => 'write',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_update_participants' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'update_participants',
                'description' => 'Update Participatns',
                'type' => 'write',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_get_participants' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'get_participants',
                'description' => 'Get Participatns',
                'type' => 'write',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_log_error' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'log_error',
                'description' => 'Log error',
                'type' => 'read',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_getminutesfromlastconexion' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'getminutesfromlastconexion',
                'description' => 'Get minutes from last conexion',
                'type' => 'read',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],

        'mod_jitsi_stop_stream_noauthor' => [
                'classname' => 'mod_jitsi_external',
                'methodname' => 'stop_stream_byerror',
                'description' => 'Stop stream by error',
                'type' => 'write',
                'ajax' => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ],
];
