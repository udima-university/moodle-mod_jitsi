<?php
/**
 * Jitsi module capability definition
 *
 * @package    mod_jitsi
 * @copyright  2021 Arnes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$addons = [
    'mod_jitsi' => [ // Plugin identifier
        'handlers' => [ // Different places where the plugin will display content.
            'jitsimeeting' => [ // Handler unique name (alphanumeric).
                'displaydata' => [
                    'icon' => $CFG->wwwroot . '/mod/jitsi/pix/icon.gif',
                    'class' => '',
                ],
                'delegate' => 'CoreCourseModuleDelegate', // Delegate (where to display the link to the plugin)
                'method' => 'mobile_presession_view', // Main function in \mod_jitsi\output\mobile
                'offlinefunctions' => [
                    'mobile_presession_view' => [],
                    'mobile_session_view' => [],
                ], // Function that needs to be downloaded for offline.
            ],
        ],
        'lang' => [ // Language strings that are used in all the handlers.
            ['pluginname', 'jitsi'],
            ['instruction', 'jitsi'],
            ['access', 'jitsi'],
            ['nostart', 'jitsi'],
            ['buttonopeninbrowser', 'jitsi'],
            ['buttonopenwithapp', 'jitsi'],
            ['buttondownloadapp', 'jitsi'],
            ['appaccessinfo', 'jitsi'],
            ['appinstalledtext', 'jitsi'],
            ['appnotinstalledtext', 'jitsi'],
            ['desktopaccessinfo', 'jitsi']
        ],
    ],
];
