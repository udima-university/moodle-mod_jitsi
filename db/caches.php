<?php
defined('MOODLE_INTERNAL') || die();

$definitions = [
    'getminutes' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'ttl' => 120, // Tiempo de vida en segundos (2 minutos)
    ],
    'getminutesdates' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'ttl' => 120, // Tiempo de vida en segundos (2 minutos)
    ],
];
