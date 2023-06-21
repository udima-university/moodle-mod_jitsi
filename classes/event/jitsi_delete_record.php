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
class jitsi_delete_record extends \core\event\base {
    /**
     * Create instance of event.
     *
     * @param string $record
     * @param string $link
     * @return record
     */
    public static function event_with_record($record, $link) {
        $data = array(
            'context' => \context_system::instance(),
            'other' => array(
                'record' => $record,
                'link' => $link,
            )
        );

        return self::create($data);
    }

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'jitsi_record';
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('deleterecord', 'jitsi');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' delete record with id {$this->other['record']}
             with link: https://www.youtube.com/watch?v={$this->other['link']}";
    }

    /**
     * Get url related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/jitsi/view.php', array('id' => $this->contextinstanceid));
    }

    /**
     * Mapping between log fields and event properties.
     */
    public static function get_objectid_mapping() {
        return false;
    }

}
