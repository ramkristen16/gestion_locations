<?php
// controllers/AppartementController.php

class AppartementController {
    private AppartementModel $model;

    public function __construct(Database $db) {
        $this->model = new AppartementModel($db);
    }

    public function liste(): void {
        $q      = trim($_GET['q'] ?? '');
        $statut = trim($_GET['statut'] ?? '');
        $appartements = $this->model->rechercher($q, $statut);
        include 'views/appartements/liste.php';
    }

    public function voir(): void {
        $id = (int)($_GET['id'] ?? 0);
        $appartement = $this->model->findById($id);
        if (!$appartement) { http_response_code(404); include 'views/errors/404.php'; return; }
        $historique = $this->model->historiqueLocataires($id);
        include 'views/appartements/voir.php';
    }

    public function ajouter(): void {
        $erreurs = [];
        $data = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'numero_appartement' => trim($_POST['numero_appartement'] ?? ''),
                'designation'        => trim($_POST['designation'] ?? ''),
                'lieu'               => trim($_POST['lieu'] ?? ''),
                'loyer_mensuel'      => (float)($_POST['loyer_mensuel'] ?? 0),
                'caution'            => (float)($_POST['caution'] ?? 0),
                'charges_incluses'   => $_POST['charges_incluses'] ?? 'Non',
                'surface_m2'         => (float)($_POST['surface_m2'] ?? 0),
                'nombre_pieces'      => (int)($_POST['nombre_pieces'] ?? 0),
                'description'        => trim($_POST['description'] ?? ''),
            ];
            if (!$data['numero_appartement']) $erreurs[] = 'Numéro requis.';
            if (!$data['designation'])        $erreurs[] = 'Désignation requise.';
            if ($data['loyer_mensuel'] <= 0)  $erreurs[] = 'Loyer invalide.';
            if ($data['caution'] > 3 * $data['loyer_mensuel']) {
                $erreurs[] = 'Caution ≤ 3 × loyer mensuel.';
            }

            if (empty($erreurs)) {
                try {
                    $this->model->create($data);
                    header('Location: ?page=appartements');
                    exit;
                } catch (Exception $e) {
                    $erreurs[] = $e->getMessage();
                }
            }
        }
        include 'views/appartements/form.php';
    }

    public function modifier(): void {
        $id = (int)($_GET['id'] ?? 0);
        $appartement = $this->model->findById($id);
        if (!$appartement) { http_response_code(404); include 'views/errors/404.php'; return; }

        $erreurs = [];
        $data = $appartement;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'numero_appartement' => trim($_POST['numero_appartement'] ?? ''),
                'designation'        => trim($_POST['designation'] ?? ''),
                'lieu'               => trim($_POST['lieu'] ?? ''),
                'loyer_mensuel'      => (float)($_POST['loyer_mensuel'] ?? 0),
                'caution'            => (float)($_POST['caution'] ?? 0),
                'charges_incluses'   => $_POST['charges_incluses'] ?? 'Non',
                'surface_m2'         => (float)($_POST['surface_m2'] ?? 0),
                'nombre_pieces'      => (int)($_POST['nombre_pieces'] ?? 0),
                'description'        => trim($_POST['description'] ?? ''),
                'statut'             => $_POST['statut'] ?? $appartement['statut'],
            ];
            if (empty($erreurs)) {
                try {
                    $this->model->update($id, $data);
                    header('Location: ?page=appartements');
                    exit;
                } catch (Exception $e) {
                    $erreurs[] = $e->getMessage();
                }
            }
        }
        $formMode = 'modifier';
        include 'views/appartements/form.php';
    }

    public function supprimer(): void {
        $id = (int)($_GET['id'] ?? 0);
        $ok = $this->model->delete($id);
        $msg = $ok ? 'Appartement supprimé.' : 'Impossible : appartement loué ou avec historique.';
        header('Location: ?page=appartements&msg=' . urlencode($msg));
        exit;
    }
}
