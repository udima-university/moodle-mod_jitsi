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
 * Install
 * @package   mod_jitsi
 * @copyright  2025 Sergio Comerón (jitsi@sergiocomeron.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to handle the installation process for the Jitsi module.
 *
 * This function checks if the 'jitsi_servers' table exists in the database.
 * If the table exists and there is no record with the domain 'meet.jit.si',
 * it inserts a default server record with the domain 'meet.jit.si'.
 *
 * @return bool Returns true upon successful execution.
 */
function xmldb_jitsi_install() {
    global $DB;
    $dbman = $DB->get_manager();
    $table = new xmldb_table('jitsi_servers');
    if ($dbman->table_exists($table)) {
        if (!$DB->record_exists('jitsi_servers', ['domain' => 'meet.jit.si'])) {
            $server = new stdClass();
            $server->name         = 'Meet JitSi default';
            $server->type         = 0;
            $server->domain       = 'meet.jit.si';
            $server->appid        = '';
            $server->secret       = '';
            $server->eightbyeightappid    = '';
            $server->eightbyeightapikeyid = '';
            $server->privatekey   = '';
            $server->timecreated  = time();
            $server->timemodified = time();

            $DB->insert_record('jitsi_servers', $server);
        }
    }

    return true;
}
