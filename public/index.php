<?php
// public/index.php — POINT D'ENTRÉE UNIQUE (Front Controller)
// Toutes les requêtes passent ici.

declare(strict_types=1);

// Chemin racine du projet (un niveau au-dessus de public/)
define('ROOT', dirname(__DIR__));

// Charger config et database
require_once ROOT . '/config/config.php';

// Charger tous les models
require_once ROOT . '/models/LocataireModel.php';
require_once ROOT . '/models/AppartementModel.php';
require_once ROOT . '/models/ContratModel.php';
require_once ROOT . '/models/PaiementModel.php';

// Charger tous les controllers
require_once ROOT . '/controllers/LocataireController.php';
require_once ROOT . '/controllers/AppartementController.php';
require_once ROOT . '/controllers/ContratController.php';
require_once ROOT . '/controllers/PaiementController.php';

// Changer le working directory pour que les includes des vues fonctionnent
chdir(ROOT);

// Servir le CSS statique
if (isset($_GET['asset']) && $_GET['asset'] === 'style.css') {
    header('Content-Type: text/css');
    readfile(ROOT . '/public/css/style.css');
    exit;
}

// ─── ROUTAGE ──────────────────────────────────────
$page   = $_GET['page']   ?? 'dashboard';
$action = $_GET['action'] ?? 'liste';

switch ($page) {

    case 'locataires':
        $ctrl = new LocataireController($db);
        match ($action) {
            'ajouter'     => $ctrl->ajouter(),
            'modifier'    => $ctrl->modifier(),
            'blacklister' => $ctrl->blacklister(),
            'supprimer'   => $ctrl->supprimer(),
            'voir'        => $ctrl->voir(),
            default       => $ctrl->liste(),
        };
        break;

    case 'appartements':
        $ctrl = new AppartementController($db);
        match ($action) {
            'ajouter'  => $ctrl->ajouter(),
            'modifier' => $ctrl->modifier(),
            'supprimer'=> $ctrl->supprimer(),
            'voir'     => $ctrl->voir(),
            default    => $ctrl->liste(),
        };
        break;

    case 'contrats':
        $ctrl = new ContratController($db);
        match ($action) {
            'creer'    => $ctrl->creer(),
            'prolonger'=> $ctrl->prolonger(),
            'resilier' => $ctrl->resilier(),
            'terminer' => $ctrl->terminer(),
            'impayes'  => $ctrl->impayes(),
            'voir'     => $ctrl->voir(),
            default    => $ctrl->liste(),
        };
        break;

    case 'paiements':
        $ctrl = new PaiementController($db);
        match ($action) {
            'enregistrer' => $ctrl->enregistrer(),
            'rapport'     => $ctrl->rapport(),
            default       => $ctrl->liste(),
        };
        break;

    case 'dashboard':
    default:
        // Statistiques pour le dashboard
        $stats = [
            'total_appts'       => count($db->getAll('appartements')),
            'libres'            => count($db->select('id')->from('appartements')->where("statut='Libre'")->execute()),
            'occupes'           => count($db->select('id')->from('appartements')->where("statut='Occupé'")->execute()),
            'total_locs'        => count($db->getAll('locataires')),
            'blacklistes'       => count($db->select('id')->from('locataires')->where("blackliste=1")->execute()),
            'contrats_en_cours' => count($db->select('id')->from('contrats')->where("statut_contrat IN ('En cours','Prolongé')")->execute()),
            'contrats_termines' => count($db->select('id')->from('contrats')->where("statut_contrat='Terminé' AND MONTH(date_sortie_reelle)=MONTH(NOW())")->execute()),
            'loyers_mois'       => (float)($db->select('SUM(montant_paye) AS t')->from('paiements')->where("MONTH(date_paiement)=MONTH(NOW()) AND YEAR(date_paiement)=YEAR(NOW())")->execute()[0]['t'] ?? 0),
            'impayes'           => count($db->select('id')->from('paiements')->where("statut_paiement IN ('Impayé','Partiel')")->execute()),
        ];
        include ROOT . '/views/dashboard.php';
        break;
}
