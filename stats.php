<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
global $DB, $OUTPUT, $PAGE;

$context = context_system::instance();
require_login();
require_capability('moodle/site:config', $context);

$PAGE->set_url(new moodle_url('/mod/jitsi/stats.php'));
$PAGE->set_context($context);
// Cambiar el título de la página utilizando get_string.
$PAGE->set_title(get_string('jitsi_recording_statistics', 'jitsi'));

// Define la clase del formulario.
class datesearch_form extends moodleform {

    public function definition() {
        global $DB;
        $mform = $this->_form;
        $defaulttimestart = [
            'year' => date('Y'),
            'month' => date('n'),
            'day' => date('j'),
            'hour' => 0,
            'minute' => 0,
        ];
        $mform->addElement('date_time_selector', 'timestart', get_string('from', 'jitsi'), ['defaulttime' => $defaulttimestart]);
        $mform->addElement('date_time_selector', 'timeend', get_string('to', 'jitsi'));

        $top_options = [10 => 'Top 10', 100 => 'Top 100', 200 => 'Top 200'];
        $mform->addElement('select', 'toplimit', get_string('toplimit', 'jitsi'), $top_options);
        $mform->setDefault('toplimit', 10);

        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('search'));
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }

    public function validation($data, $files) {
        return [];
    }
}

// Instancia el formulario.
$mform = new datesearch_form();

// Inicio de la salida.
echo $OUTPUT->header();

// Añadir el título principal de la página utilizando get_string.
echo $OUTPUT->heading(get_string('jitsi_recording_statistics', 'jitsi'));

// Estadísticas generales.
$total_recordings = $DB->count_records('jitsi_record');

if ($fromform = $mform->get_data()) {
    $fromdate = $fromform->timestart;
    $todate = $fromform->timeend;
    $toplimit = $fromform->toplimit;
} else {
    $fromdate = 0;
    $todate = time();
    $toplimit = 10;
}

// Modifica la consulta SQL para filtrar por fechas.
$top_teachers_sql = "SELECT jsr.userid, COUNT(jr.id) AS recording_count,
                    ROUND(COUNT(jr.id) / GREATEST((MAX(jsr.timecreated) - MIN(jsr.timecreated)) / 86400.0, 1), 2) AS avg_recordings_per_day,
                    ROUND(COUNT(jr.id) / GREATEST((MAX(jsr.timecreated) - MIN(jsr.timecreated)) / (86400.0 * 7), 1), 2) AS avg_recordings_per_week
                    FROM {jitsi_record} jr
                    JOIN {jitsi_source_record} jsr ON jr.source = jsr.id
                    WHERE jsr.timecreated BETWEEN :fromdate AND :todate
                    GROUP BY jsr.userid
                    ORDER BY recording_count DESC";

$params = ['fromdate' => $fromdate, 'todate' => $todate];
$top_teachers = $DB->get_records_sql($top_teachers_sql, $params, 0, $toplimit);

// Comienza la sección de estadísticas generales.
$general_stats_html = html_writer::start_tag('div', ['class' => 'general-stats']);

$general_stats_html .= html_writer::tag('p', get_string('total_recordings', 'jitsi') . ': ' . $total_recordings);

// Recolecta datos para el gráfico.
$chart_labels = [];
$chart_data = [];

foreach ($top_teachers as $teacher) {
    $user = $DB->get_record('user', ['id' => $teacher->userid], 'firstname, lastname');
    $username = $user->firstname . ' ' . $user->lastname;
    $chart_labels[] = $username;
    $chart_data[] = $teacher->recording_count;
}

$general_stats_html .= html_writer::end_tag('div');

echo $general_stats_html;

// Genera el gráfico.
$chart = new core\chart_bar();
$series = new core\chart_series(get_string('number_of_recordings', 'jitsi'), $chart_data);
$chart->add_series($series);
$chart->set_labels($chart_labels);
// Ajustar el título del gráfico si es necesario.

echo $OUTPUT->render_chart($chart);

// Muestra el formulario.
$mform->display();

// Pie de página.
echo $OUTPUT->footer();