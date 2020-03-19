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
    $settings->add(new admin_setting_confightmleditor('jitsi_help', get_string('help', 'jitsi'), get_string('helpex', 'jitsi'), null));
    $options = ['username'=>get_string('username', 'jitsi'), 'nameandsurname'=>get_string('nameandsurname', 'jitsi')];
    $settings->add(new admin_setting_configselect('jitsi_id', get_string('identification', 'jitsi'), get_string('identificationex', 'jitsi'),null,$options));
    $sessionoptions = ['Course Shortname','Session ID','Session Name'];
    $sessionoptionsDefault = [0,1,2];

    $optionsSeparator = ['.', '-', '_', 'empty'];
    $settings->add(new admin_setting_configselect('jitsi_separator', 'separator', 'separatorex','.',$optionsSeparator));
    $settings->add(new admin_setting_configmultiselect('jitsi_sesionname',
        'Session name fields', 'Fields name session',
        $sessionoptionsDefault , $sessionoptions));
    $settings->add(new admin_setting_heading('bookmodeditdefaults',
        get_string('tokennconfig', 'jitsi'), get_string('tokenconfigurationex', 'jitsi')));
    $settings->add(new admin_setting_configtext('jitsi_app_id', 'App_id', 'Token app id', ''));
    $settings->add(new admin_setting_configpasswordunmask('jitsi_secret', 'Secret', 'Secret', ''));

}
