<?php
// controllers/ContratController.php

class ContratController {
    private ContratModel $model;
    private AppartementModel $apptModel;
    private LocataireModel $locModel;

    public function __construct(Database $db) {
        $this->model     = new ContratModel($db);
        $this->apptModel = new AppartementModel($db);
        $this->locModel  = new LocataireModel($db);
    }

    public function liste(): void {
        $contrats = $this->model->findAll();
        include 'views/contrats/liste.php';
    }

    public function voir(): void {
        $id = (int)($_GET['id'] ?? 0);
        $contrat = $this->model->findById($id);
        if (!$contrat) { http_response_code(404); include 'views/errors/404.php'; return; }
        include 'views/contrats/voir.php';
    }

    // Créer un contrat = Louer un appartement
    public function creer(): void {
        $erreurs = [];
        $locataires   = $this->locModel->findAll();
        $appartements = $this->apptModel->findLibres();
        $data = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_locataire'    => (int)($_POST['id_locataire'] ?? 0),
                'id_appartement'  => (int)($_POST['id_appartement'] ?? 0),
                'date_entree'     => $_POST['date_entree'] ?? '',
                'date_sortie_prevue' => $_POST['date_sortie_prevue'] ?? '',
                'caution_versee'  => (float)($_POST['caution_versee'] ?? 0),
            ];
            if (!$data['id_locataire'])   $erreurs[] = 'Locataire requis.';
            if (!$data['id_appartement']) $erreurs[] = 'Appartement requis.';
            if (!$data['date_entree'])    $erreurs[] = 'Date d\'entrée requise.';
            if (!$data['date_sortie_prevue']) $erreurs[] = 'Date de sortie prévue requise.';

            if (empty($erreurs)) {
                $result = $this->model->creer($data);
                if ($result['success']) {
                    header('Location: ?page=contrats');
                    exit;
                }
                $erreurs = array_merge($erreurs, $result['errors']);
            }
        }
        include 'views/contrats/form.php';
    }

    // Prolonger un contrat
    public function prolonger(): void {
        $id = (int)($_GET['id'] ?? 0);
        $contrat = $this->model->findById($id);
        if (!$contrat) { http_response_code(404); include 'views/errors/404.php'; return; }

        $erreurs = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nouvelleDateSortie = $_POST['date_sortie_prevue'] ?? '';
            if (!$nouvelleDateSortie) {
                $erreurs[] = 'Nouvelle date de sortie requise.';
            } else {
                $result = $this->model->prolonger($id, $nouvelleDateSortie);
                if ($result['success']) {
                    header('Location: ?page=contrats&action=voir&id=' . $id);
                    exit;
                }
                $erreurs = $result['errors'];
            }
        }
        $action = 'prolonger';
        include 'views/contrats/modifier.php';
    }

    // Résilier un contrat
    public function resilier(): void {
        $id = (int)($_GET['id'] ?? 0);
        $contrat = $this->model->findById($id);
        if (!$contrat) { http_response_code(404); include 'views/errors/404.php'; return; }

        $erreurs = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dateSortie = $_POST['date_sortie_reelle'] ?? date('Y-m-d');
            $result = $this->model->resilier($id, $dateSortie);
            if ($result['success']) {
                header('Location: ?page=contrats');
                exit;
            }
            $erreurs = $result['errors'];
        }
        $action = 'resilier';
        include 'views/contrats/modifier.php';
    }

    // Terminer un contrat
    public function terminer(): void {
        $id = (int)($_GET['id'] ?? 0);
        $result = $this->model->terminer($id);
        $msg = $result['success'] ? 'Contrat terminé.' : implode(' | ', $result['errors']);
        header('Location: ?page=contrats&msg=' . urlencode($msg));
        exit;
    }

    // Liste des impayés
    public function impayes(): void {
        $impayes = $this->model->impayes();
        include 'views/contrats/impayes.php';
    }
}
