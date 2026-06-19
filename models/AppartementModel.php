<?php

class AppartementModel {
    public function __construct(private Database $db) {}

    public function findAll(): array {
        return $this->db->select('*')->from('appartements')->orderBy('numero_appartement')->execute();
    }

    public function findById(int $id): array|false {
        $rows = $this->db->select('*')->from('appartements')
            ->where('id = :id', ['id' => $id])->execute();
        return $rows[0] ?? false;
    }

    public function findLibres(): array {
        return $this->db->select('*')->from('appartements')
            ->where("statut = 'Libre'")->execute();
    }

    public function findByNumero(string $numero): array|false {
        $rows = $this->db->select('*')->from('appartements')
            ->where('numero_appartement = :n', ['n' => $numero])->execute();
        return $rows[0] ?? false;
    }

    // Recherche par nom, numéro, lieu, statut
    public function rechercher(string $q = '', string $statut = ''): array {
        $qb = $this->db->select('*')->from('appartements');
        if ($q) {
            $qb->where("(designation LIKE :q OR numero_appartement LIKE :q OR lieu LIKE :q)", ['q' => "%$q%"]);
        }
        if ($statut) {
            $qb->where("statut = :s", ['s' => $statut]);
        }
        return $qb->orderBy('numero_appartement')->execute();
    }

    public function create(array $data): bool {
        if ($data['caution'] > 3 * $data['loyer_mensuel']) {
            throw new Exception("La caution ne peut pas dépasser 3 × le loyer mensuel.");
        }
        $data['statut'] = 'Libre';
        return $this->db->insert('appartements', $data);
    }

    public function update(int $id, array $data): bool {
        if (isset($data['caution'], $data['loyer_mensuel'])) {
            if ($data['caution'] > 3 * $data['loyer_mensuel']) {
                throw new Exception("La caution ne peut pas dépasser 3 × le loyer mensuel.");
            }
        }
        return $this->db->update('appartements', $id, $data);
    }

    public function changerStatut(int $id, string $statut): bool {
        $statuts = ['Libre', 'Occupé', 'En Travaux', 'Hors service'];
        if (!in_array($statut, $statuts)) return false;
        return $this->db->update('appartements', $id, ['statut' => $statut]);
    }

    public function delete(int $id): bool {
        $app = $this->findById($id);
        if (!$app || $app['statut'] !== 'Libre') return false;
        $contrats = $this->db->select('id')->from('contrats')
            ->where('id_appartement = :id', ['id' => $id])->execute();
        if (!empty($contrats)) return false;
        return $this->db->delete('appartements', $id);
    }

    // Historique des locataires d'un appartement
    public function historiqueLocataires(int $idAppart): array {
        return $this->db->select('l.nom, l.prenom, l.telephone, c.date_entree, c.date_sortie_prevue, c.date_sortie_reelle, c.statut_contrat, c.loyer_mensuel_contractuel')
            ->from('contrats c')
            ->join('locataires l', 'l.id = c.id_locataire')
            ->where('c.id_appartement = :id', ['id' => $idAppart])
            ->orderBy('c.date_entree', 'DESC')
            ->execute();
    }

    // Taux d'occupation : (jours occupés / jours total période) × 100
    public function tauxOccupation(int $idAppart, string $debut, string $fin): float {
        $pdo = $this->db->getPDO();
        $sql = "SELECT SUM(
                    DATEDIFF(
                        LEAST(COALESCE(c.date_sortie_reelle, c.date_sortie_prevue), :fin),
                        GREATEST(c.date_entree, :debut)
                    )
                ) AS jours_occupes
                FROM contrats c
                WHERE c.id_appartement = :id
                AND c.date_entree <= :fin2
                AND COALESCE(c.date_sortie_reelle, c.date_sortie_prevue) >= :debut2
                AND c.statut_contrat IN ('En cours','Terminé','Prolongé')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['fin' => $fin, 'debut' => $debut, 'id' => $idAppart, 'fin2' => $fin, 'debut2' => $debut]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $joursOccupes = max(0, (int)($row['jours_occupes'] ?? 0));
        $joursTotaux = (new DateTime($debut))->diff(new DateTime($fin))->days;
        return $joursTotaux > 0 ? round(($joursOccupes / $joursTotaux) * 100, 2) : 0;
    }
}
