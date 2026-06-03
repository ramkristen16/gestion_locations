<?php
// config/config.php
define('DB_NAME', 'gestion_locations');
define('BASE_URL', '');

require_once __DIR__ . '/Database.php';

// Connexion unique partagée dans tout le projet
$db = new Database(DB_NAME);
