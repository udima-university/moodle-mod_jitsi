<?php
/**
 * Formulario para crear o editar un servidor Jitsi.
 */

require_once($CFG->libdir . '/formslib.php');

class servermanagement_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        // Campo oculto para guardar el ID del servidor (0 si es nuevo).
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        // name (nombre del servidor).
        $mform->addElement('text', 'name', get_string('name'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required');

        // type (tipo de servidor).
        $types = [
            0 => 'Server without token',
            1 => 'Self-hosted with appid and secret',
            2 => '8x8 servers'
        ];
        $mform->addElement('select', 'type', get_string('type', 'mod_jitsi'), $types);
        $mform->addRule('type', get_string('required'), 'required');
        $mform->setDefault('type', 0);

        // domain (campo común).
        $mform->addElement('text', 'domain', get_string('domain', 'mod_jitsi'));
        $mform->setType('domain', PARAM_TEXT);
        $mform->addRule('domain', get_string('required'), 'required');

        // Campos para type=1 (Self-hosted).
        $mform->addElement('text', 'appid', 'App ID');
        $mform->setType('appid', PARAM_TEXT);
        $mform->disabledIf('appid', 'type', 'neq', 1);

        $mform->addElement('text', 'secret', 'Secret');
        $mform->setType('secret', PARAM_TEXT);
        $mform->disabledIf('secret', 'type', 'neq', 1);

        // Campos para type=2 (8x8).
        $mform->addElement('text', 'eightbyeightappid', '8x8 App ID');
        $mform->setType('eightbyeightappid', PARAM_TEXT);
        $mform->disabledIf('eightbyeightappid', 'type', 'neq', 2);

        $mform->addElement('text', 'eightbyeightapikeyid', '8x8 API Key ID');
        $mform->setType('eightbyeightapikeyid', PARAM_TEXT);
        $mform->disabledIf('eightbyeightapikeyid', 'type', 'neq', 2);

        $mform->addElement('textarea', 'privatekey', 'Private Key', 'wrap="virtual" rows="4" cols="60"');
        $mform->setType('privatekey', PARAM_TEXT);
        $mform->disabledIf('privatekey', 'type', 'neq', 2);

        // Botones de acción (Guardar/Cancelar).
        $this->add_action_buttons();
    }
}