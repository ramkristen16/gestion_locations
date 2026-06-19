<?php
// controllers/LocataireController.php

class LocataireController {
    private LocataireModel $model;

    public function __construct(Database $db) {
        $this->model = new LocataireModel($db);
    }

    // GET /locataires
    public function liste(): void {
        $q = trim($_GET['q'] ?? '');
        $locataires = $q ? $this->model->findByNom($q) : $this->model->findAll();
        include 'views/locataires/liste.php';
    }

    // GET /locataires/voir?id=X
    public function voir(): void {
        $id = (int)($_GET['id'] ?? 0);
        $locataire = $this->model->findById($id);
        if (!$locataire) { http_response_code(404); include 'views/errors/404.php'; return; }
        include 'views/locataires/voir.php';
    }

    // GET /locataires/ajouter  |  POST /locataires/ajouter
    public function ajouter(): void {
        $erreurs = [];
        $data = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom'           => trim($_POST['nom'] ?? ''),
                'prenom'        => trim($_POST['prenom'] ?? ''),
                'telephone'     => trim($_POST['telephone'] ?? ''),
                'email'         => trim($_POST['email'] ?? ''),
                'adresse'       => trim($_POST['adresse'] ?? ''),
                'piece_identite'=> trim($_POST['piece_identite'] ?? ''),
            ];
            if (!$data['nom'])           $erreurs[] = 'Nom requis.';
            if (!$data['prenom'])        $erreurs[] = 'Prénom requis.';
            if (!$data['telephone'])     $erreurs[] = 'Télephone requis.';
            if (!ctype_digit($telephone)) {
                echo("Erreur: Telephone invzlide");
            }
            if (!ctype_digit($piece_identite)) {
                echo("Erreur: piece invzlide");
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $erreurs[] = 'Email invalide.';
            if (!$data['piece_identite'])$erreurs[] = 'Pièce d\'identité requise.';

            if (empty($erreurs) && $this->model->existsTelOrEmail($data['telephone'], $data['email'])) {
                $erreurs[] = 'Téléphone ou email déjà utilisé.';
            }

            if (empty($erreurs)) {
                $this->model->create($data);
                header('Location: ?page=locataires');
                exit;
            }
        }
        include 'views/locataires/form.php';
    }

    // GET /locataires/modifier?id=X  |  POST
    public function modifier(): void {
        $id = (int)($_GET['id'] ?? 0);
        $locataire = $this->model->findById($id);
        if (!$locataire) { http_response_code(404); include 'views/errors/404.php'; return; }

        $erreurs = [];
        $data = $locataire;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom'           => trim($_POST['nom'] ?? ''),
                'prenom'        => trim($_POST['prenom'] ?? ''),
                'telephone'     => trim($_POST['telephone'] ?? ''),
                'email'         => trim($_POST['email'] ?? ''),
                'adresse'       => trim($_POST['adresse'] ?? ''),
                'piece_identite'=> trim($_POST['piece_identite'] ?? ''),
            ];
            if ($this->model->existsTelOrEmail($data['telephone'], $data['email'], $id)) {
                $erreurs[] = 'Téléphone ou email déjà utilisé par un autre locataire.';
            }
            if (empty($erreurs)) {
                $this->model->update($id, $data);
                header('Location: ?page=locataires');
                exit;
            }
        }
        $formMode = 'modifier';
        include 'views/locataires/form.php';
    }

    // POST /locataires/blacklister?id=X
    public function blacklister(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->model->blacklister($id);
        header('Location: ?page=locataires');
        exit;
    }

    // POST /locataires/supprimer?id=X
    public function supprimer(): void {
        $id = (int)($_GET['id'] ?? 0);
        $ok = $this->model->delete($id);
        $msg = $ok ? 'Locataire supprimé.' : 'Impossible : contrat actif en cours.';
        header('Location: ?page=locataires&msg=' . urlencode($msg));
        exit;
    }
}
