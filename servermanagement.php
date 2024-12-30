<?php
/**
 * Página de administración para gestionar (crear, editar, eliminar) servidores Jitsi.
 */
require_once(__DIR__ . '/../../config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());

global $DB, $OUTPUT, $PAGE;

$PAGE->set_url('/mod/jitsi/servermanagement.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/jitsi/servermanagement.php');
$PAGE->set_title(get_string('servermanagement', 'mod_jitsi'));
// $PAGE->set_heading(get_string('servermanagement', 'mod_jitsi'));

require_once($CFG->dirroot . '/mod/jitsi/servermanagement_form.php');

$action = optional_param('action', '', PARAM_ALPHA); // p.ej. 'edit', 'delete', ''
$id     = optional_param('id', 0, PARAM_INT);        // id del servidor
$confirm= optional_param('confirm', 0, PARAM_BOOL);  // para confirmaciones

// 1. Si se solicitó eliminar un servidor: "action=delete&id=X".
if ($action === 'delete' && $id > 0) {
    // Primero, comprobamos que el servidor existe.
    if (!$server = $DB->get_record('jitsi_servers', ['id' => $id])) {
        print_error('invalidid', 'error');
    }

    // Preguntamos si el usuario ya confirmó la acción.
    if ($confirm) {
        // Borramos el registro.
        $DB->delete_records('jitsi_servers', ['id' => $server->id]);

        \core\notification::add(
            get_string('serverdeleted', 'mod_jitsi', $server->name),
            \core\output\notification::NOTIFY_SUCCESS
        );
        redirect(new moodle_url('/mod/jitsi/servermanagement.php'));
    } else {
        // Mostramos la confirmación.
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('delete'));
        $msg = get_string('confirmdelete', 'mod_jitsi', format_string($server->name));
        echo $OUTPUT->confirm(
            $msg,
            new moodle_url('/mod/jitsi/servermanagement.php', ['action'=>'delete', 'id'=>$id, 'confirm'=>1]),
            new moodle_url('/mod/jitsi/servermanagement.php')
        );
        echo $OUTPUT->footer();
        exit;
    }
}

// 2. Instanciamos el formulario (para crear o editar).
$mform = new servermanagement_form();

// Si es “editar” (action=edit&id=X), cargamos datos en el formulario.
if ($action === 'edit' && $id > 0) {
    if ($server = $DB->get_record('jitsi_servers', ['id' => $id])) {
        // Cargamos datos en el form.
        $mform->set_data($server);
    } else {
        print_error('invalidid', 'error');
    }
}

// 3. Comprobar si el formulario fue cancelado.
if ($mform->is_cancelled()) {
    // Simplemente redirige a la misma página (limpia).
    redirect(new moodle_url('/mod/jitsi/servermanagement.php'));

// 4. Procesar datos si el formulario fue enviado y validado.
} else if ($data = $mform->get_data()) {
    // Si $data->id > 0, significa que estamos editando un servidor, si =0, es nuevo.
    if ($data->id) {
        // EDITAR
        if (!$server = $DB->get_record('jitsi_servers', ['id' => $data->id])) {
            print_error('invalidid', 'error');
        }

        $server->name   = $data->name;
        $server->type   = $data->type;
        $server->domain = $data->domain;
        $server->appid               = '';
        $server->secret              = '';
        $server->eightbyeightappid   = '';
        $server->eightbyeightapikeyid= '';
        $server->privatekey          = '';

        if ($data->type == 1) {
            $server->appid  = $data->appid;
            $server->secret = $data->secret;
        } else if ($data->type == 2) {
            $server->eightbyeightappid    = $data->eightbyeightappid;
            $server->eightbyeightapikeyid = $data->eightbyeightapikeyid;
            $server->privatekey           = $data->privatekey;
        }

        $server->timemodified = time();
        $DB->update_record('jitsi_servers', $server);

        \core\notification::add(
            get_string('serverupdated', 'mod_jitsi', $server->name),
            \core\output\notification::NOTIFY_SUCCESS
        );

    } else {
        // CREAR
        $server = new stdClass();
        $server->name   = $data->name;
        $server->type   = $data->type;
        $server->domain = $data->domain;
        $server->appid               = '';
        $server->secret              = '';
        $server->eightbyeightappid   = '';
        $server->eightbyeightapikeyid= '';
        $server->privatekey          = '';

        if ($data->type == 1) {
            $server->appid  = $data->appid;
            $server->secret = $data->secret;
        } else if ($data->type == 2) {
            $server->eightbyeightappid    = $data->eightbyeightappid;
            $server->eightbyeightapikeyid = $data->eightbyeightapikeyid;
            $server->privatekey           = $data->privatekey;
        }

        $server->timecreated  = time();
        $server->timemodified = time();

        $DB->insert_record('jitsi_servers', $server);

        \core\notification::add(
            get_string('serveradded', 'mod_jitsi'),
            \core\output\notification::NOTIFY_SUCCESS
        );
    }

    // Tras crear o editar, redirigir a la misma página para refrescar.
    redirect(new moodle_url('/mod/jitsi/servermanagement.php'));
}

// 5. Mostrar la lista de servidores y el formulario (si se desea crear uno nuevo o editar).
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('servermanagement', 'mod_jitsi'));

// Añadir el enlace para volver a settings.php
$settingsurl = new moodle_url('/admin/settings.php', ['section' => 'modsettingjitsi']);
echo html_writer::link($settingsurl, get_string('backtosettings', 'mod_jitsi'), ['class' => 'btn btn-secondary']);


// Mostrar la lista de servidores existentes en tabla.
$servers = $DB->get_records('jitsi_servers', null, 'name ASC');
$table = new html_table();
$table->head = [
    get_string('name'),
    get_string('type', 'mod_jitsi'),
    get_string('domain', 'mod_jitsi'),
    get_string('actions', 'mod_jitsi') // nueva columna
];

foreach ($servers as $s) {
    switch ($s->type) {
        case 0:
            $typestring = 'Server without token';
            break;
        case 1:
            $typestring = 'Self-hosted (appid & secret)';
            break;
        case 2:
            $typestring = '8x8 server';
            break;
        default:
            $typestring = get_string('unknowntype', 'mod_jitsi');
    }

    // Enlaces de acción (editar y borrar).
    $editurl = new moodle_url('/mod/jitsi/servermanagement.php', ['action'=>'edit', 'id'=>$s->id]);
    $deleteurl = new moodle_url('/mod/jitsi/servermanagement.php', ['action'=>'delete', 'id'=>$s->id]);

    $links = html_writer::link($editurl, get_string('edit')) . ' | '
           . html_writer::link($deleteurl, get_string('delete'));

    $table->data[] = [
        format_string($s->name),
        $typestring,
        format_string($s->domain),
        $links
    ];
}
echo html_writer::table($table);

// Título del formulario: depende si estamos editando o no.
if ($action === 'edit' && $id > 0) {
    echo $OUTPUT->heading(get_string('editserver', 'mod_jitsi'));
} else {
    echo $OUTPUT->heading(get_string('addnewserver', 'mod_jitsi'));
}

// Mostramos el formulario.
$mform->display();

echo $OUTPUT->footer();