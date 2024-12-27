<?php
/**
 * This file is part of mod_jitsi
 * It is executed only on fresh install (not on upgrade).
 */

function xmldb_jitsi_install() {
    global $DB;

    // Verificar si la tabla jitsi_servers existe.
    $dbman = $DB->get_manager();
    $table = new xmldb_table('jitsi_servers');
    if ($dbman->table_exists($table)) {

        // Comprobamos si ya existe un servidor con el dominio meet.jit.si
        // (para no duplicar si se reinstala en un entorno de prueba).
        if (!$DB->record_exists('jitsi_servers', ['domain' => 'meet.jit.si'])) {

            $server = new stdClass();
            $server->name         = 'Meet JitSi default'; // O el nombre que prefieras.
            $server->type         = 0;  // 0 => sin token.
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