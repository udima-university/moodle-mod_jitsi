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
 * Defines the accept event.
 *
 * @package    mod_jitsi
 * @copyright  2022 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_jitsi\event;

/**
 * The mod_jitsi instance accepted event class
 *
 * If the accept mode needs to be stored as well, you may need to
 * override methods get_url() and get_legacy_log_data(), too.
 *
 * @package    mod_jitsi
 * @copyright  2022 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class jitsi_error extends \core\event\base {
    /**
     * Create instance of event.
     *
     * @param string $error
     * @return langpack_updated
     */
    public static function event_with_error($error) {
        $data = [
            'context' => \context_system::instance(),
            'other' => [
                'error' => $error,
                'account' => $account,
            ],
        ];

        return self::create($data);
    }

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'jitsi';
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('error', 'jitsi');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "User with id '$this->userid'. - jitsi activity with course module id '$this->contextinstanceid'
            get this error: {$this->other['error']} with account with id {$this->other['account']}.";
    }

    /**
     * Get url related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/jitsi/view.php', ['id' => $this->contextinstanceid]);
    }

    /**
     * Mapping between log fields and event properties.
     */
    public static function get_objectid_mapping() {
        return false;
    }
}
