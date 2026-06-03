<?php
// models/LocataireModel.php

class LocataireModel {
    public function __construct(private Database $db) {}

    // Générer un numéro unique : LOC-YYYYMMDD-XXXX
    public function genererNumero(): string {
        $prefix = 'LOC-' . date('Ymd') . '-';
        $rows = $this->db->select('numero_locataire')
            ->from('locataires')
            ->where("numero_locataire LIKE :p", ['p' => $prefix . '%'])
            ->orderBy('numero_locataire', 'DESC')
            ->execute();
        $seq = empty($rows) ? 1 : (intval(substr(end($rows)['numero_locataire'], -4)) + 1);
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function findAll(): array {
        return $this->db->select('*')->from('locataires')->orderBy('nom')->execute();
    }

    public function findById(int $id): array|false {
        $rows = $this->db->select('*')->from('locataires')
            ->where('id = :id', ['id' => $id])->execute();
        return $rows[0] ?? false;
    }

    public function findByNom(string $nom): array {
        return $this->db->select('*')->from('locataires')
            ->where("nom LIKE :n OR prenom LIKE :n", ['n' => "%$nom%"])->execute();
    }

    // Vérifier unicité téléphone/email
    public function existsTelOrEmail(string $tel, string $email, int $excludeId = 0): bool {
        $rows = $this->db->select('id')->from('locataires')
            ->where('(telephone = :t OR email = :e) AND id != :id', 
                    ['t' => $tel, 'e' => $email, 'id' => $excludeId])
            ->execute();
        return !empty($rows);
    }

    public function create(array $data): bool {
        $data['numero_locataire'] = $this->genererNumero();
        $data['date_inscription'] = date('Y-m-d');
        $data['statut_locataire'] = 'actif';
        $data['blacklisté'] = 0;
        return $this->db->insert('locataires', $data);
    }

    public function update(int $id, array $data): bool {
        return $this->db->update('locataires', $id, $data);
    }

    public function blacklister(int $id): bool {
        return $this->db->update('locataires', $id, [
            'blacklisté' => 1,
            'statut_locataire' => 'blacklisté'
        ]);
    }

    public function changerCoordonnees(int $id, string $telephone, string $email, string $adresse): bool {
        return $this->db->update('locataires', $id, [
            'telephone' => $telephone,
            'email' => $email,
            'adresse' => $adresse
        ]);
    }

    // Locataires avec contrat actif impayé > 60 jours → Blacklist auto
    public function blacklisterImpayes(): int {
        $pdo = $this->db->getPDO();
        $sql = "UPDATE locataires SET blacklisté=1, statut_locataire='blacklisté'
                WHERE id IN (
                    SELECT DISTINCT c.id_locataire FROM contrats c
                    JOIN paiements p ON p.id_contrat = c.id
                    WHERE p.statut_paiement IN ('Partiel','Impayé')
                    AND DATEDIFF(NOW(), p.date_paiement) > 60
                )";
        $stmt = $pdo->exec($sql);
        return $stmt;
    }

    public function delete(int $id): bool {
        // Vérifier aucun contrat actif
        $contrats = $this->db->select('id')->from('contrats')
            ->where("id_locataire = :id AND statut_contrat = 'En cours'", ['id' => $id])
            ->execute();
        if (!empty($contrats)) return false;
        return $this->db->delete('locataires', $id);
    }
}
