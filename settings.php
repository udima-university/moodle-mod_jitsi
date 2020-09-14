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
 * Settings for Jitsi instances
 * @package   mod_jitsi
 * @copyright  2019 Sergio Comerón (sergiocomeron@icloud.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/jitsi/lib.php');
    $settings->add(new admin_setting_configtext('jitsi_domain', 'Domain', 'Domain Jitsi Server', 'meet.jit.si'));
    $settings->add(new admin_setting_confightmleditor('jitsi_help', get_string('help', 'jitsi'),
        get_string('helpex', 'jitsi'), null));
    $options = ['username' => get_string('username', 'jitsi'),
        'nameandsurname' => get_string('nameandsurname', 'jitsi'),
        'alias' => get_string('alias', 'jitsi')];
    $settings->add(new admin_setting_configselect('jitsi_id', get_string('identification', 'jitsi'),
        get_string('identificationex', 'jitsi'), null, $options));
    $sessionoptions = ['Course Shortname', 'Session ID', 'Session Name'];
    $sessionoptionsdefault = [0, 1, 2];

    $optionsseparator = ['.', '-', '_', 'empty'];
    $settings->add(new admin_setting_configselect('jitsi_separator',
        get_string('separator', 'jitsi'), get_string('separatorex', 'jitsi'), '.', $optionsseparator));
    $settings->add(new admin_setting_configmultiselect('jitsi_sesionname',
        get_string('sessionnamefields', 'jitsi'), get_string('sessionnamefieldsex', 'jitsi'),
        $sessionoptionsdefault, $sessionoptions));
    $settings->add(new admin_setting_configcheckbox('jitsi_securitybutton', get_string('securitybutton', 'jitsi'),
        get_string('securitybuttonex', 'jitsi'), 0));
    $settings->add(new admin_setting_configcheckbox('jitsi_invitebuttons', get_string('invitebutton', 'jitsi'),
        get_string('invitebuttonex', 'jitsi'), 0));
    $settings->add(new admin_setting_configtext('jitsi_channellastcam', get_string('simultaneouscameras', 'jitsi'),
        get_string('simultaneouscamerasex', 'jitsi'), '4', PARAM_INT, 1));
    $settings->add(new admin_setting_configcheckbox('jitsi_livebutton', get_string('streamingbutton', 'jitsi'),
        get_string('streamingbuttonex', 'jitsi'), 0));
    $settings->add(new admin_setting_configcheckbox('jitsi_blurbutton', get_string('blurbutton', 'jitsi'),
        get_string('blurbuttonex', 'jitsi'), 0));
    $settings->add(new admin_setting_configcheckbox('jitsi_shareyoutube', get_string('youtubebutton', 'jitsi'),
        get_string('youtubebuttonex', 'jitsi'), 0));
    $settings->add(new admin_setting_configtext('jitsi_watermarklink', get_string('watermarklink', 'jitsi'),
        get_string('watermarklinkex', 'jitsi'), 'https://jitsi.org'));
    $settings->add(new admin_setting_configcheckbox('jitsi_finishandreturn', get_string('finishandreturn', 'jitsi'),
        get_string('finishandreturnex', 'jitsi'), 0));

    $settings->add(new admin_setting_configpasswordunmask('jitsi_password', get_string('password', 'jitsi'),
        get_string('passwordex', 'jitsi'), ''));
    $settings->add(new admin_setting_configcheckbox('jitsi_privatesessions', get_string('privatesessions', 'jitsi'),
        get_string('privatesessionsex', 'jitsi'), 1));

    $settings->add(new admin_setting_heading('bookmodeditdefaults',
        get_string('tokennconfig', 'jitsi'), get_string('tokenconfigurationex', 'jitsi')));
    $settings->add(new admin_setting_configtext('jitsi_app_id', get_string('appid', 'jitsi'),
        get_string('appidex', 'jitsi'), ''));
    $settings->add(new admin_setting_configpasswordunmask('jitsi_secret', get_string('secret', 'jitsi'),
        get_string('secretex', 'jitsi'), ''));
}
