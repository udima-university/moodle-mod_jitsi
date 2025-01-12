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
 * @copyright  2025 Sergio Comerón (jitsi@sergiocomeron.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Class servermanagement_form
 *
 * This class defines the form for managing Jitsi server configurations in Moodle.
 * It extends the moodleform class and provides various form elements for configuring
 * different types of Jitsi servers.
 *
 * @package    mod_jitsi
 * @category   form
 */
class servermanagement_form extends moodleform {
    /**
     * Defines the form for managing Jitsi server configurations.
     *
     * This form allows the user to configure different types of Jitsi servers,
     * including servers without tokens, self-hosted servers with app ID and secret,
     * and 8x8 servers. Depending on the selected server type, different fields
     * will be enabled or disabled.
     *
     * Form fields:
     * - id: Hidden field for the server ID.
     * - name: Text field for the server name.
     * - type: Select field for the server type (0: Server without token, 1: Self-hosted with appid and secret, 2: 8x8 servers).
     * - domain: Text field for the server domain.
     * - appid: Text field for the app ID (enabled only for self-hosted servers).
     * - secret: Text field for the secret (enabled only for self-hosted servers).
     * - eightbyeightappid: Text field for the 8x8 app ID (enabled only for 8x8 servers).
     * - eightbyeightapikeyid: Text field for the 8x8 API key ID (enabled only for 8x8 servers).
     * - privatekey: Textarea for the private key (enabled only for 8x8 servers).
     *
     * Validation rules:
     * - name: Required.
     * - type: Required.
     * - domain: Required.
     *
     * Action buttons are added at the end of the form.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        $mform->addElement('text', 'name', get_string('name'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required');

        $types = [
            0 => 'Server without token',
            1 => 'Self-hosted with appid and secret',
            2 => '8x8 servers',
        ];
        $mform->addElement('select', 'type', get_string('type', 'mod_jitsi'), $types);
        $mform->addRule('type', get_string('required'), 'required');
        $mform->setDefault('type', 0);

        $mform->addElement('text', 'domain', get_string('domain', 'mod_jitsi'));
        $mform->setType('domain', PARAM_TEXT);
        $mform->addRule('domain', get_string('required'), 'required');

        $mform->addElement('text', 'appid', 'App ID');
        $mform->setType('appid', PARAM_TEXT);
        $mform->disabledIf('appid', 'type', 'neq', 1);

        $mform->addElement('text', 'secret', 'Secret');
        $mform->setType('secret', PARAM_TEXT);
        $mform->disabledIf('secret', 'type', 'neq', 1);

        $mform->addElement('text', 'eightbyeightappid', '8x8 App ID');
        $mform->setType('eightbyeightappid', PARAM_TEXT);
        $mform->disabledIf('eightbyeightappid', 'type', 'neq', 2);

        $mform->addElement('text', 'eightbyeightapikeyid', '8x8 API Key ID');
        $mform->setType('eightbyeightapikeyid', PARAM_TEXT);
        $mform->disabledIf('eightbyeightapikeyid', 'type', 'neq', 2);

        $mform->addElement('textarea', 'privatekey', 'Private Key', 'wrap="virtual" rows="4" cols="60"');
        $mform->setType('privatekey', PARAM_TEXT);
        $mform->disabledIf('privatekey', 'type', 'neq', 2);

        $this->add_action_buttons();
    }
}
