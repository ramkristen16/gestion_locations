// ... [Vos vérifications d'erreurs existantes restent inchangées] ...
if (!empty($errors)) return ['success' => false, 'errors' => $errors];

$data['numero_contrat'] = $this->genererNumero();
$data['duree_mois'] = $this->calculerDureeMois($data['date_entree'], $data['date_sortie_prevue']);
$data['statut_contrat'] = 'En cours';

// Figer le loyer au moment du contrat
$appt = $this->db->select('loyer_mensuel')->from('appartements')
    ->where('id = :id', ['id' => $data['id_appartement']])->execute();
$data['loyer_mensuel_contractuel'] = $appt[0]['loyer_mensuel'];

// --- DÉBUT DE LA TRANSACTION SÉCURISÉE ---
$pdo = $this->db->getPDO();
try {
    $pdo->beginTransaction(); 

    // 1. Insertion du contrat
    $this->db->insert('contrats', $data);
    
    // 2. Mise à jour du statut de l'appartement
    $this->db->update('appartements', $data['id_appartement'], ['statut' => 'Occupé']);

    $pdo->commit(); // Tout est validé ensemble en base de données
    return ['success' => true];
    
} catch (Exception $e) {
    $pdo->rollBack(); // En cas de bug, on annule l'insert et l'update
    return ['success' => false, 'errors' => ["Erreur base de données : " . $e->getMessage()]];
}


Models : 
<?php
// models/QuittanceModel.php

class QuittanceModel {
    public function __construct(private Database $db) {}

    public function findByIdQuittance(string $idQuittance): array|false {
        $rows = $this->db->select(
            'p.*, c.numero_contrat, c.loyer_mensuel_contractuel,
             c.date_entree, c.date_sortie_prevue, c.caution_versee,
             l.nom, l.prenom, l.telephone, l.email, l.adresse, l.piece_identite,
             a.numero_appartement, a.designation, a.lieu, a.surface_m2, a.nombre_pieces'
        )
        ->from('paiements p')
        ->join('contrats c', 'c.id = p.id_contrat')
        ->join('locataires l', 'l.id = c.id_locataire')
        ->join('appartements a', 'a.id = c.id_appartement')
        ->where('p.id_quittance = :q', ['q' => $idQuittance])
        ->execute();

        return $rows[0] ?? false;
    }

    public function findByPaiementId(int $id): array|false {
        $rows = $this->db->select(
            'p.*, c.numero_contrat, c.loyer_mensuel_contractuel,
             c.date_entree, c.date_sortie_prevue, c.caution_versee,
             l.nom, l.prenom, l.telephone, l.email, l.adresse, l.piece_identite,
             a.numero_appartement, a.designation, a.lieu, a.surface_m2, a.nombre_pieces'
        )
        ->from('paiements p')
        ->join('contrats c', 'c.id = p.id_contrat')
        ->join('locataires l', 'l.id = c.id_locataire')
        ->join('appartements a', 'a.id = c.id_appartement')
        ->where('p.id = :id', ['id' => $id])
        ->execute();

        return $rows[0] ?? false;
    }
}




Controller :

<?php
// controllers/QuittanceController.php

class QuittanceController {
    private QuittanceModel $model;

    public function __construct(Database $db) {
        $this->model = new QuittanceModel($db);
    }

    // Prévisualisation HTML de la quittance
    public function voir(): void {
        $id = (int)($_GET['id'] ?? 0);
        $quittance = $this->model->findByPaiementId($id);
        if (!$quittance) {
            http_response_code(404);
            include 'views/errors/404.php';
            return;
        }
        include 'views/quittances/voir.php';
    }

    // Génération et téléchargement PDF
    public function telecharger(): void {
        $id = (int)($_GET['id'] ?? 0);
        $q  = $this->model->findByPaiementId($id);
        if (!$q) { http_response_code(404); exit; }

        // Chemin vers TCPDF — adapte selon ton installation
        require_once ROOT . '/lib/tcpdf/tcpdf.php';

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('GestiLoc');
        $pdf->SetAuthor('GestiLoc');
        $pdf->SetTitle('Quittance ' . $q['id_quittance']);
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 11);

        $mois = date('F Y', strtotime($q['mois_concerne']));
        $html = $this->genererHTML($q, $mois);

        $pdf->writeHTML($html, true, false, true, false, '');

        $filename = 'Quittance_' . $q['id_quittance'] . '.pdf';
        $pdf->Output($filename, 'D'); // D = téléchargement direct
        exit;
    }

    private function genererHTML(array $q, string $mois): string {
        $loyer     = number_format($q['loyer_mensuel_contractuel'], 0, ',', ' ');
        $paye      = number_format($q['montant_paye'], 0, ',', ' ');
        $reste     = number_format($q['reste_a_payer'], 0, ',', ' ');
        $datePaie  = date('d/m/Y', strtotime($q['date_paiement']));

        return '
        <style>
            body { font-family: helvetica; font-size: 11pt; color: #1a1a1a; }
            .header-box { background-color: #00b36b; color: #ffffff; padding: 18px 20px; border-radius: 6px; margin-bottom: 24px; }
            .header-box h1 { font-size: 20pt; margin: 0 0 4px; }
            .header-box p  { font-size: 9pt; margin: 0; opacity: .85; }
            .section-title { font-size: 10pt; font-weight: bold; color: #00b36b;
                             border-bottom: 1px solid #00b36b; padding-bottom: 3px;
                             margin: 18px 0 10px; text-transform: uppercase; }
            table.info { width: 100%; border-collapse: collapse; font-size: 10pt; }
            table.info td { padding: 5px 8px; }
            table.info td:first-child { color: #555; width: 45%; }
            table.recap { width: 100%; border-collapse: collapse; margin-top: 10px; }
            table.recap th { background: #f4f4f4; padding: 8px 10px; text-align: left;
                              font-size: 10pt; border: 1px solid #ddd; }
            table.recap td { padding: 8px 10px; font-size: 10pt; border: 1px solid #ddd; }
            .badge-paye    { background: #d4edda; color: #155724; padding: 3px 10px;
                             border-radius: 4px; font-weight: bold; }
            .badge-partiel { background: #fff3cd; color: #856404; padding: 3px 10px;
                             border-radius: 4px; font-weight: bold; }
            .badge-impaye  { background: #f8d7da; color: #721c24; padding: 3px 10px;
                             border-radius: 4px; font-weight: bold; }
            .total-line { background: #e8f8f1; font-weight: bold; }
            .footer-box { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 14px;
                          font-size: 9pt; color: #777; text-align: center; }
            .signature-zone { margin-top: 40px; }
            .sig-block { display: inline-block; width: 45%; text-align: center; }
        </style>

        <div class="header-box">
            <h1>QUITTANCE DE LOYER</h1>
            <p>N° ' . htmlspecialchars($q['id_quittance']) . ' &nbsp;|&nbsp; Mois de ' . $mois . '</p>
        </div>

        <div class="section-title">Informations locataire</div>
        <table class="info">
            <tr><td>Nom complet</td><td><b>' . htmlspecialchars($q['prenom'] . ' ' . $q['nom']) . '</b></td></tr>
            <tr><td>Téléphone</td><td>' . htmlspecialchars($q['telephone']) . '</td></tr>
            <tr><td>Email</td><td>' . htmlspecialchars($q['email']) . '</td></tr>
            <tr><td>Pièce d\'identité</td><td>' . htmlspecialchars($q['piece_identite']) . '</td></tr>
        </table>

        <div class="section-title">Appartement loué</div>
        <table class="info">
            <tr><td>Numéro</td><td><b>' . htmlspecialchars($q['numero_appartement']) . '</b></td></tr>
            <tr><td>Désignation</td><td>' . htmlspecialchars($q['designation']) . '</td></tr>
            <tr><td>Lieu</td><td>' . htmlspecialchars($q['lieu']) . '</td></tr>
            <tr><td>Surface</td><td>' . $q['surface_m2'] . ' m² — ' . $q['nombre_pieces'] . ' pièce(s)</td></tr>
            <tr><td>Contrat N°</td><td>' . htmlspecialchars($q['numero_contrat']) . '</td></tr>
        </table>

        <div class="section-title">Récapitulatif du paiement</div>
        <table class="recap">
            <tr><th>Désignation</th><th>Montant</th></tr>
            <tr><td>Loyer contractuel du mois de ' . $mois . '</td>
                <td>' . $loyer . ' Ar</td></tr>
            <tr><td>Montant payé le ' . $datePaie . '</td>
                <td>' . $paye . ' Ar</td></tr>
            <tr class="total-line">
                <td>Reste à payer</td>
                <td>' . $reste . ' Ar</td></tr>
            <tr><td>Mode de paiement</td>
                <td>' . htmlspecialchars($q['mode_paiement']) . 
                (!empty($q['reference_transaction']) ? ' — Réf: ' . htmlspecialchars($q['reference_transaction']) : '') .
                '</td></tr>
            <tr><td>Statut</td>
                <td><span class="badge-' . strtolower($q['statut_paiement']) . '">' . $q['statut_paiement'] . '</span></td></tr>
        </table>

        <div class="signature-zone">
            <table width="100%"><tr>
                <td width="50%" align="center">
                    <p style="font-size:10pt;color:#555">Signature du locataire</p>
                    <br/><br/>
                    <p>________________________</p>
                    <p style="font-size:9pt">' . htmlspecialchars($q['prenom'] . ' ' . $q['nom']) . '</p>
                </td>
                <td width="50%" align="center">
                    <p style="font-size:10pt;color:#555">Cachet du propriétaire</p>
                    <br/><br/>
                    <p>________________________</p>
                    <p style="font-size:9pt">GestiLoc</p>
                </td>
            </tr></table>
        </div>

        <div class="footer-box">
            <p>Quittance générée le ' . date('d/m/Y à H:i') . ' — GestiLoc — Réf. paiement : ' . htmlspecialchars($q['id_paiement']) . '</p>
        </div>';
    }
}


paiment/liste
// Dans le <thead>, ajouter une colonne :
<th style="padding:.6rem;color:var(--texte-muted)">Actions</th>

// Dans chaque <tr> de paiement :
<td style="padding:.6rem">
    <a href="?page=quittances&action=voir&id=<?= $p['id'] ?>"
       class="btn-ghost" style="font-size:.78rem;padding:.3rem .6rem">👁 Voir</a>
    <a href="?page=quittances&action=telecharger&id=<?= $p['id'] ?>"
       class="btn" style="font-size:.78rem;padding:.3rem .6rem">⬇ PDF</a>
</td>


index.php

// Ajouter dans le switch, après le case 'paiements' :

case 'quittances':
    require_once ROOT . '/models/QuittanceModel.php';
    require_once ROOT . '/controllers/QuittanceController.php';
    $ctrl = new QuittanceController($db);
    match ($action) {
        'telecharger' => $ctrl->telecharger(),
        default       => $ctrl->voir(),
    };
    break;

