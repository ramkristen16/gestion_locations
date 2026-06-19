<?php

class LocataireModel {
    public function __construct(private Database $db) {}

   public function genererNumeroUnique() {
    // Crée le préfixe avec la date du jour (ex: LOC-20260603-)
    $prefixe = 'LOC-' . date('Ymd') . '-';
    
    // Compte le nombre de locataires déjà créés aujourd'hui
    $sql = "SELECT COUNT(*) as total FROM locataires WHERE numero_locataire LIKE :prefixe";
    
    // Récupération de l'instance PDO (adaptez $this->db selon votre classe Database)
    $db = $this->db->getPDO(); // Ou simplement $this->db si c'est directement PDO
    $stmt = $db->prepare($sql);
    $stmt->execute(['prefixe' => $prefixe . '%']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Incrémente de 1 le total trouvé
    $compteur = $row['total'] + 1;
    
    // Génère le numéro final (ex: LOC-20260603-0003)
    return $prefixe . str_pad($compteur, 4, '0', STR_PAD_LEFT);
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
    public function existsTelOrEmail(int $tel, string $email, int $excludeId = 0): bool {
        $rows = $this->db->select('id')->from('locataires')
            ->where('(telephone = :t OR email = :e) AND id != :id', 
                    ['t' => $tel, 'e' => $email, 'id' => $excludeId])
            ->execute();
            if (!preg_match('/^[0-9]+$/', $tel)) {
                die("Erreur: Telephone incorrecte");
            }
        return !empty($rows);
    }

   public function create($data) {
    // On génère le numéro unique à la volée avant l'insertion
    $data['numero_locataire'] = $this->genererNumeroUnique();
    
    // Votre code existant qui plante à la ligne 46 :
    return $this->db->insert('locataires', $data);
}


    public function update(int $id, array $data): bool {
        return $this->db->update('locataires', $id, $data);
    }

    public function blacklister(int $id): bool {
        return $this->db->update('locataires', $id, [
            'blackliste' => 1,
            'statut_locataire' => 'blackliste'
        ]);
    }

    public function changerCoordonnees(int $id, int $telephone, string $email, string $adresse): bool {
        return $this->db->update('locataires', $id, [
            'telephone' => $telephone,
            'email' => $email,
            'adresse' => $adresse
        ]);
    }

    public function blacklisterImpayes(): int {
        $pdo = $this->db->getPDO();
        $sql = "UPDATE locataires SET blackliste=1, statut_locataire='blackliste'
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
