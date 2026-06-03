<?php
class Database {
    private $pdo;
    private $sql;   
    private $params = [];

    public function __construct($dbName) {
        try {
            $this->pdo = new PDO(
                "mysql:host=localhost;dbname=$dbName;charset=utf8mb4",
                "root", "",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("Connexion BDD échouée : " . $e->getMessage());
        }
    }

    public function createTable($tableName, $columns) {
        $hasPrimary = false;
        foreach ($columns as $type) {
            if (stripos($type, 'PRIMARY KEY') !== false) {
                $hasPrimary = true;
                break;
            }
        }
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (";
        if (!$hasPrimary) {
            $sql .= "id INT AUTO_INCREMENT PRIMARY KEY, ";
        }
        $parts = [];
        foreach ($columns as $name => $type) { 
            $parts[] = "$name $type"; 
        }
        $sql .= implode(", ", $parts) . ")";
        $this->pdo->exec($sql);
    }

    public function insert($table, $data) {
        $keys = array_keys($data);
        $sql = "INSERT INTO $table (" . implode(',', $keys) . ") VALUES (:" . implode(',:', $keys) . ")";
        return $this->pdo->prepare($sql)->execute($data);
    }

    public function update($table, $id, $data) {
        $parts = [];
        foreach ($data as $key => $value) { $parts[] = "$key = :$key"; }
        $sql = "UPDATE $table SET " . implode(', ', $parts) . " WHERE id = :id";
        $data['id'] = $id;
        return $this->pdo->prepare($sql)->execute($data);
    }

    public function delete($table, $id) {
        $sql = "DELETE FROM $table WHERE id = ?";
        return $this->pdo->prepare($sql)->execute([$id]);
    }

    public function select($columns = '*') {
        $this->sql = "SELECT $columns ";
        $this->params = []; 
        return $this;
    }
    
    public function from($table) {
        $this->sql .= "FROM $table ";
        return $this;
    }

    public function where($condition, $params = []) {
        $prefix = (strpos($this->sql, 'WHERE') === false) ? "WHERE" : "AND";
        $this->sql .= "$prefix $condition ";
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function groupBy($column) {
        $this->sql .= "GROUP BY $column ";
        return $this;
    }

    public function count($column, $alias = "total") {
        $this->sql = "SELECT COUNT($column) AS $alias ";
        $this->params = [];
        return $this;
    }

    public function join($table, $on, $type = "INNER") {
        $this->sql .= "$type JOIN $table ON $on ";
        return $this;
    }

    public function orderBy($column, $direction = "ASC") {
        $this->sql .= "ORDER BY $column $direction ";
        return $this;
    }

    public function having($condition) {
        $this->sql .= "HAVING $condition ";
        return $this;
    }

    public function execute() {
        $stmt = $this->pdo->prepare($this->sql);
        $stmt->execute($this->params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll($table) {
        return $this->select('*')->from($table)->execute();
    }

    public function query($sql) {
        try {
            $stmt = $this->pdo->query($sql); 
            if ($stmt === false) return [["Erreur" => "La requête a échoué"]];
            $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($resultats)) return [["Info" => "Aucun résultat trouvé"]];
            return $resultats;
        } catch (PDOException $e) {
            return [["ERREUR SQL" => $e->getMessage()]];
        }
    }

    public function renderHTML($data) {
        if (empty($data)) return "Table vide.";
        $html = "<table border='1'>";
        $html .= "<tr>"; 
        foreach (array_keys($data[0]) as $col) { $html .= "<th>$col</th>"; } 
        $html .= "</tr>";
        foreach ($data as $row) {
            $html .= "<tr>";
            foreach ($row as $cell) { $html .= "<td>$cell</td>"; }
            $html .= "</tr>";
        }
        return $html . "</table>";
    }

    public function getPDO() {
        return $this->pdo;
    }
}
