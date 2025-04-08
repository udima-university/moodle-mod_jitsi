<?php
namespace mod_jitsi\courseformat;

// Esto asegura que el archivo no se acceda directamente.
defined('MOODLE_INTERNAL') || die();

use core_courseformat\activityoverviewbase;
use core_courseformat\local\overview\overviewitem;
use moodle_url;
use html_writer;

/**
 * Clase overview para mostrar información del módulo Jitsi en la vista general del curso (Moodle 5.0+).
 */
class overview extends activityoverviewbase {

    /**
     * Devuelve elementos adicionales que se mostrarán en la vista general.
     *
     * @return overviewitem[]
     */
    public function get_extra_overview_items(): array {
        return [
            'sessionstatus' => $this->get_session_status_item(),
        ];
    }

    /**
     * Crea un elemento que muestra el estado de la sesión Jitsi (ejemplo básico).
     *
     * @return overviewitem|null
     */
    private function get_session_status_item(): ?overviewitem {
        // Aquí deberías poner tu lógica real de si la sesión está activa o no.
        $sessionactive = false; // Cambia esto por tu lógica.

        return new overviewitem(
            name: get_string('sessionstatus', 'mod_jitsi'),
            value: $sessionactive ? 1 : 0,
            content: $sessionactive ? get_string('sessionactive', 'mod_jitsi') : get_string('sessioninactive', 'mod_jitsi')
        );
    }

    /**
     * Devuelve la acción principal para esta actividad (enlace a unirse a la sesión).
     *
     * @return overviewitem|null
     */
    public function get_actions_overview(): ?overviewitem {
        $url = new moodle_url('/mod/jitsi/view.php', ['id' => $this->cm->id]);
        $link = html_writer::link($url, get_string('joinmeeting', 'mod_jitsi'));

        return new overviewitem(
            name: get_string('action', 'mod_jitsi'),
            value: 'join',
            content: $link
        );
    }

    /**
     * Devuelve una fecha de vencimiento si aplica (opcional).
     *
     * @return overviewitem|null
     */
    public function get_due_date_overview(): ?overviewitem {
        // Solo si tu actividad tiene alguna fecha límite.
        if (empty($this->cm->customdata['duedate'])) {
            return null;
        }

        $duedate = $this->cm->customdata['duedate'];

        return new overviewitem(
            name: get_string('duedate', 'mod_jitsi'),
            value: $duedate,
            content: userdate($duedate)
        );
    }
}
