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
 * @copyright  2021 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_jitsi\event;

/**
 * The mod_identifier instance accepted event class
 *
 * If the accept mode needs to be stored as well, you may need to
 * override methods get_url() and get_legacy_log_data(), too.
 *
 * @package    mod_jitsi
 * @copyright  2019 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class jitsi_session_enter extends \core\event\base {
    /**
     * Create instance of event.
     *
     * @param string $navigator
     * @return langpack_updated
     */
    public static function event_with_navigator($navigator) {
        $data = [
            'context' => \context_system::instance(),
            'other' => [
                'navigator' => $navigator,
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
        return get_string('entersession', 'jitsi');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        if ($this->userid != 0) {
            if (!empty($this->other['navigator'])) {
                return "The user with id '$this->userid' enter to session with coursemodule id '
                    $this->contextinstanceid' with: {$this->other['navigator']}.";
            } else {
                return "The user with id '$this->userid' enter to session with coursemodule id '
                    $this->contextinstanceid'.";
            }
        } else {
            return "Guest user enter to session with coursemodule id '$this->contextinstanceid' with: {$this->other['navigator']}.";
        }
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['navigator'])) {
            throw new \coding_exception('The \'navigator\' value must be set');
        }
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
