<?php

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

 // $services = array(
 //       'jitsi' => array(                      //the name of the web service
 //           'functions' => array ('mod_jitsi_enter_session', 'mod_jitsi_exit_session'), //web service functions of this service
 //           'requiredcapability' => '',                //if set, the web service user need this capability to access
 //                                                      //any function of this service. For example: 'some/capability:specified'
 //           'restrictedusers' =>0,                      //if enabled, the Moodle administrator must link some user to this service
 //                                                       //into the administration
 //           'enabled'=>1,                               //if enabled, the service can be reachable on a default installation
 //           'shortname'=>'jitsi' //the short name used to refer to this service from elsewhere including when fetching a token
 //        )
 //   );

// We defined the web service functions to install.
$functions = array(
        // 'mod_jitsi_enter_session' => array(
        //         'classname'   => 'mod_jitsi_external',
        //         'methodname'  => 'enter_session',
        //         'classpath'   => 'mod/jitsi/externallib.php',
        //         'description' => 'User enter session',
        //         'type'        => 'write',
        //         'ajax'        => true,
        //         'loginrequired' => false,
        // ),
        // 'mod_jitsi_exit_session' => array(
        //         'classname'   => 'mod_jitsi_external',
        //         'methodname'  => 'exit_session',
        //         'classpath'   => 'mod/jitsi/externallib.php',
        //         'description' => 'User exit session',
        //         'type'        => 'write',
        //         'ajax'        => true,
        //         'loginrequired' => false,
        // ),
        // 'mod_jitsi_left_session' => array(
        //         'classname'   => 'mod_jitsi_external',
        //         'methodname'  => 'left_session',
        //         'classpath'   => 'mod/jitsi/externallib.php',
        //         'description' => 'User left session',
        //         'type'        => 'write',
        //         'ajax'        => true,
        //         'loginrequired' => false,
        // ),
        'mod_jitsi_state_record' => array(
                'classname'   => 'mod_jitsi_external',
                'methodname'  => 'state_record',
                'classpath'   => 'mod/jitsi/externallib.php',
                'description' => 'State session recording',
                'type'        => 'write',
                'ajax'        => true,
                'loginrequired' => false,
                'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'],
        ),
        // 'mod_jitsi_joined_session' => array(
        //         'classname'   => 'mod_jitsi_external',
        //         'methodname'  => 'joined_session',
        //         'classpath'   => 'mod/jitsi/externallib.php',
        //         'description' => 'User exit session',
        //         'type'        => 'write',
        //         'ajax'        => true,
        //         'loginrequired' => false,
        // ),
        'mod_jitsi_view_jitsi' => array(
                'classname'     => 'mod_jitsi_external',
                'methodname'    => 'view_jitsi',
                'description'   => 'Trigger the course module viewed event.',
                'type'          => 'write',
                'capabilities'  => 'mod/jitsi:view',
                'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'local_mobile'),
        ),
        // 'mod_jitsi_create_video' => array(
        //         'classname'   => 'mod_jitsi_external',
        //         'methodname'  => 'create_video',
        //         'classpath'   => 'mod/jitsi/externallib.php',
        //         'description' => 'Cretae video for streaming',
        //         'type'        => 'write',
        //         'ajax'        => true,
        //         'loginrequired' => false,
        // )
);
