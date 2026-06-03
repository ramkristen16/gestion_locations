<?php
// controllers/PaiementController.php

class PaiementController {
    private PaiementModel $model;
    private ContratModel $contratModel;

    public function __construct(Database $db) {
        $this->model       = new PaiementModel($db);
        $this->contratModel = new ContratModel($db);
    }

    public function liste(): void {
        $paiements = $this->model->findAll();
        include 'views/paiements/liste.php';
    }

    public function enregistrer(): void {
        $erreurs = [];
        $contrats = $this->contratModel->findAll();
        // Filtrer : seulement contrats En cours
        $contrats = array_filter($contrats, fn($c) => in_array($c['statut_contrat'], ['En cours', 'Prolongé']));
        $data = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_contrat'          => (int)($_POST['id_contrat'] ?? 0),
                'mois_concerne'       => ($_POST['mois_concerne'] ?? '') . '-01',
                'montant_paye'        => (float)($_POST['montant_paye'] ?? 0),
                'mode_paiement'       => $_POST['mode_paiement'] ?? 'Espèces',
                'reference_transaction' => trim($_POST['reference_transaction'] ?? ''),
            ];
            if (!$data['id_contrat'])    $erreurs[] = 'Contrat requis.';
            if (!$_POST['mois_concerne'])$erreurs[] = 'Mois concerné requis.';
            if ($data['montant_paye'] < 0) $erreurs[] = 'Montant invalide.';

            if (empty($erreurs)) {
                $result = $this->model->enregistrer($data);
                if ($result['success']) {
                    $msg = 'Paiement enregistré. Quittance : ' . $result['quittance_id'];
                    if ($result['reste'] > 0) $msg .= ' | Reste à payer : ' . $result['reste'] . ' Ar';
                    header('Location: ?page=paiements&msg=' . urlencode($msg));
                    exit;
                }
                $erreurs = array_merge($erreurs, $result['errors']);
            }
        }
        include 'views/paiements/form.php';
    }

    // Rapport : totaux et CA
    public function rapport(): void {
        $debut = $_GET['debut'] ?? date('Y-01-01');
        $fin   = $_GET['fin']   ?? date('Y-m-d');
        $idAppart = (int)($_GET['id_appartement'] ?? 0);

        $total = $this->model->totalLoyers($debut, $fin, $idAppart);
        $ca    = $this->model->chiffreAffaires($debut, $fin);

        include 'views/paiements/rapport.php';
    }
}
