<?php
// models/PaiementModel.php

class PaiementModel {
    public function __construct(private Database $db) {}

    private function genererIdQuittance(): string {
        return 'QTT-' . date('YmdHis') . '-' . rand(100, 999);
    }

    private function genererIdPaiement(): string {
        return 'PAY-' . date('YmdHis') . '-' . rand(100, 999);
    }

    public function findAll(): array {
        return $this->db->select('p.*, c.numero_contrat, l.nom, l.prenom, a.numero_appartement')
            ->from('paiements p')
            ->join('contrats c', 'c.id = p.id_contrat')
            ->join('locataires l', 'l.id = c.id_locataire')
            ->join('appartements a', 'a.id = c.id_appartement')
            ->orderBy('p.date_paiement', 'DESC')
            ->execute();
    }

    public function findById(int $id): array|false {
        $rows = $this->db->select('p.*, c.numero_contrat, c.loyer_mensuel_contractuel, l.nom, l.prenom, a.numero_appartement, a.designation')
            ->from('paiements p')
            ->join('contrats c', 'c.id = p.id_contrat')
            ->join('locataires l', 'l.id = c.id_locataire')
            ->join('appartements a', 'a.id = c.id_appartement')
            ->where('p.id = :id', ['id' => $id])
            ->execute();
        return $rows[0] ?? false;
    }

    public function findByContrat(int $idContrat): array {
        return $this->db->select('*')->from('paiements')
            ->where('id_contrat = :id', ['id' => $idContrat])
            ->orderBy('mois_concerne', 'ASC')
            ->execute();
    }

    // Enregistrer un paiement de loyer
    public function enregistrer(array $data): array {
        $errors = [];

        // Vérifier contrat En cours
        $contratRows = $this->db->select('*')->from('contrats')
            ->where('id = :id', ['id' => $data['id_contrat']])->execute();
        if (empty($contratRows)) return ['success' => false, 'errors' => ['Contrat introuvable.']];

        $contrat = $contratRows[0];
        if (!in_array($contrat['statut_contrat'], ['En cours', 'Prolongé'])) {
            $errors[] = "Le contrat n'est pas En cours.";
        }

        // Vérifier que le mois concerné est dans la période du contrat
        $mois = $data['mois_concerne']; // format Y-m-01
        if ($mois < $contrat['date_entree'] || $mois > $contrat['date_sortie_prevue']) {
            $errors[] = "Le mois concerné est hors de la période du contrat.";
        }

        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $loyer = $contrat['loyer_mensuel_contractuel'];
        $montantPaye = (float) $data['montant_paye'];
        $resteAPayer = max(0, $loyer - $montantPaye);

        $statut = 'Payé';
        if ($montantPaye <= 0) $statut = 'Impayé';
        elseif ($resteAPayer > 0) $statut = 'Partiel';

        $row = [
            'id_quittance'       => $this->genererIdQuittance(),
            'id_paiement'        => $this->genererIdPaiement(),
            'id_contrat'         => $data['id_contrat'],
            'mois_concerne'      => $mois,
            'montant_paye'       => $montantPaye,
            'reste_a_payer'      => $resteAPayer,
            'date_paiement'      => date('Y-m-d'),
            'mode_paiement'      => $data['mode_paiement'] ?? 'Espèces',
            'statut_paiement'    => $statut,
            'reference_transaction' => $data['reference_transaction'] ?? null,
        ];

        $this->db->insert('paiements', $row);
        return ['success' => true, 'quittance_id' => $row['id_quittance'], 'reste' => $resteAPayer];
    }

    // Calcul total loyers perçus par appartement ou global, entre deux dates
    public function totalLoyers(string $debut, string $fin, int $idAppart = 0): float {
        $qb = $this->db->select('SUM(p.montant_paye) AS total')
            ->from('paiements p')
            ->join('contrats c', 'c.id = p.id_contrat')
            ->where("p.date_paiement BETWEEN :d AND :f AND p.statut_paiement IN ('Payé','Partiel')",
                    ['d' => $debut, 'f' => $fin]);
        if ($idAppart > 0) {
            $qb->where('c.id_appartement = :a', ['a' => $idAppart]);
        }
        $rows = $qb->execute();
        return (float)($rows[0]['total'] ?? 0);
    }

    // Chiffre d'affaires total sur une période
    public function chiffreAffaires(string $debut, string $fin): float {
        return $this->totalLoyers($debut, $fin);
    }
}
