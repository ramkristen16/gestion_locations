<?php
// models/ContratModel.php

class ContratModel {
    public function __construct(private Database $db) {}

    private function genererNumero(): string {
        $prefix = 'CTR-' . date('Y') . '-';
        $rows = $this->db->select('numero_contrat')
            ->from('contrats')
            ->where("numero_contrat LIKE :p", ['p' => $prefix . '%'])
            ->orderBy('numero_contrat', 'DESC')
            ->execute();
        $seq = empty($rows) ? 1 : (intval(substr(end($rows)['numero_contrat'], -4)) + 1);
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // Calculer durée en mois : CEIL((date_sortie - date_entree) / 30.44)
    private function calculerDureeMois(string $dateEntree, string $dateSortie): int {
        $d1 = new DateTime($dateEntree);
        $d2 = new DateTime($dateSortie);
        $jours = $d1->diff($d2)->days;
        return (int) ceil($jours / 30.44);
    }

    public function findAll(): array {
        return $this->db->select('c.*, l.nom, l.prenom, l.telephone, a.numero_appartement, a.designation')
            ->from('contrats c')
            ->join('locataires l', 'l.id = c.id_locataire')
            ->join('appartements a', 'a.id = c.id_appartement')
            ->orderBy('c.date_entree', 'DESC')
            ->execute();
    }

    public function findById(int $id): array|false {
        $rows = $this->db->select('c.*, l.nom, l.prenom, l.telephone, l.email, a.numero_appartement, a.designation, a.lieu')
            ->from('contrats c')
            ->join('locataires l', 'l.id = c.id_locataire')
            ->join('appartements a', 'a.id = c.id_appartement')
            ->where('c.id = :id', ['id' => $id])
            ->execute();
        return $rows[0] ?? false;
    }

    public function findByAppartement(int $idAppart): array {
        return $this->db->select('c.*, l.nom, l.prenom')
            ->from('contrats c')
            ->join('locataires l', 'l.id = c.id_locataire')
            ->where('c.id_appartement = :id', ['id' => $idAppart])
            ->orderBy('c.date_entree', 'DESC')
            ->execute();
    }

    // Vérifier chevauchement de périodes pour un appartement
    public function chevauchement(int $idAppart, string $dateEntree, string $dateSortie, int $excludeId = 0): bool {
        $rows = $this->db->select('id')->from('contrats')
            ->where(
                "id_appartement = :a AND id != :ex AND statut_contrat IN ('En cours','Prolongé')
                 AND date_entree < :ds AND date_sortie_prevue > :de",
                ['a' => $idAppart, 'ex' => $excludeId, 'ds' => $dateSortie, 'de' => $dateEntree]
            )->execute();
        return !empty($rows);
    }

    // Créer un contrat (louer un appartement)
    public function creer(array $data): array {
        $errors = [];

        // Vérifications
        $apptRows = $this->db->select('statut')->from('appartements')
            ->where('id = :id', ['id' => $data['id_appartement']])->execute();
        if (empty($apptRows) || $apptRows[0]['statut'] !== 'Libre') {
            $errors[] = "L'appartement n'est pas Libre.";
        }

        $locRows = $this->db->select('blackliste')->from('locataires')
            ->where('id = :id', ['id' => $data['id_locataire']])->execute();
        if (empty($locRows) || $locRows[0]['blackliste']) {
            $errors[] = "Le locataire est Blacklisté.";
        }

        if ($data['date_entree'] > date('Y-m-d')) {
            $errors[] = "La date d'entrée ne peut pas être dans le futur.";
        }

        if ($data['date_sortie_prevue'] <= $data['date_entree']) {
            $errors[] = "La date de sortie prévue doit être après la date d'entrée.";
        }

        if ($this->chevauchement($data['id_appartement'], $data['date_entree'], $data['date_sortie_prevue'])) {
            $errors[] = "Chevauchement de périodes pour cet appartement.";
        }

        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $data['numero_contrat'] = $this->genererNumero();
        $data['duree_mois'] = $this->calculerDureeMois($data['date_entree'], $data['date_sortie_prevue']);
        $data['statut_contrat'] = 'En cours';

        // Figer le loyer au moment du contrat
        $appt = $this->db->select('loyer_mensuel')->from('appartements')
            ->where('id = :id', ['id' => $data['id_appartement']])->execute();
        $data['loyer_mensuel_contractuel'] = $appt[0]['loyer_mensuel'];

        $this->db->insert('contrats', $data);
        // Mettre l'appartement Occupé
        $this->db->update('appartements', $data['id_appartement'], ['statut' => 'Occupé']);

        return ['success' => true];
    }

    // Prolonger un contrat
    public function prolonger(int $id, string $nouvelleDateSortie): array {
        $contrat = $this->findById($id);
        if (!$contrat || $contrat['statut_contrat'] !== 'En cours') {
            return ['success' => false, 'errors' => ['Contrat invalide ou terminé.']];
        }
        $nouvelleDuree = $this->calculerDureeMois($contrat['date_entree'], $nouvelleDateSortie);
        $this->db->update('contrats', $id, [
            'date_sortie_prevue' => $nouvelleDateSortie,
            'duree_mois' => $nouvelleDuree,
            'statut_contrat' => 'Prolongé'
        ]);
        return ['success' => true];
    }

    // Résilier un contrat
    public function resilier(int $id, string $dateSortieReelle): array {
        $contrat = $this->findById($id);
        if (!$contrat || !in_array($contrat['statut_contrat'], ['En cours', 'Prolongé'])) {
            return ['success' => false, 'errors' => ['Contrat invalide.']];
        }
        $this->db->update('contrats', $id, [
            'date_sortie_reelle' => $dateSortieReelle,
            'statut_contrat' => 'Résilié'
        ]);
        $this->db->update('appartements', $contrat['id_appartement'], ['statut' => 'Libre']);
        return ['success' => true];
    }

    // Terminer un contrat (départ normal)
    public function terminer(int $id): array {
        $contrat = $this->findById($id);
        if (!$contrat) return ['success' => false, 'errors' => ['Contrat introuvable.']];

        // Vérifier que tous les loyers sont payés
        $impayes = $this->db->select('id')->from('paiements')
            ->where("id_contrat = :id AND statut_paiement IN ('Impayé','Partiel')", ['id' => $id])
            ->execute();
        if (!empty($impayes)) {
            return ['success' => false, 'errors' => ['Des loyers sont encore impayés ou partiels.']];
        }

        $this->db->update('contrats', $id, [
            'date_sortie_reelle' => date('Y-m-d'),
            'statut_contrat' => 'Terminé'
        ]);
        $this->db->update('appartements', $contrat['id_appartement'], ['statut' => 'Libre']);
        return ['success' => true];
    }

    // Impayés : contrats avec mois non payés ou partiellement payés
    public function impayes(): array {
        return $this->db->select('c.numero_contrat, l.nom, l.prenom, l.telephone, a.numero_appartement, p.mois_concerne, p.montant_paye, p.reste_a_payer, p.statut_paiement, p.date_paiement')
            ->from('contrats c')
            ->join('locataires l', 'l.id = c.id_locataire')
            ->join('appartements a', 'a.id = c.id_appartement')
            ->join('paiements p', 'p.id_contrat = c.id')
            ->where("p.statut_paiement IN ('Impayé','Partiel')")
            ->orderBy('p.date_paiement', 'ASC')
            ->execute();
    }

    // Locataires ayant occupé entre deux dates
    public function locatairesBetween(int $idAppart, string $debut, string $fin): array {
        return $this->db->select('l.nom, l.prenom, l.telephone, c.date_entree, c.date_sortie_prevue')
            ->from('contrats c')
            ->join('locataires l', 'l.id = c.id_locataire')
            ->where('c.id_appartement = :a AND c.date_entree <= :fin AND c.date_sortie_prevue >= :debut',
                    ['a' => $idAppart, 'fin' => $fin, 'debut' => $debut])
            ->execute();
    }
}
