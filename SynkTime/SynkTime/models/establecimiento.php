<?php
// models/Establecimiento.php
class Establecimiento {
    private $conn;
    private $table = 'establecimiento';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getAll() {
        $query = "SELECT 
                    e.*,
                    s.NOMBRE as sede_nombre,
                    s.ID_SEDE,
                    emp.NOMBRE as empresa_nombre,
                    emp.ID_EMPRESA
                  FROM " . $this->table . " e
                  LEFT JOIN sede s ON e.ID_SEDE = s.ID_SEDE
                  LEFT JOIN empresa emp ON s.ID_EMPRESA = emp.ID_EMPRESA
                  WHERE e.ESTADO = 'A'
                  ORDER BY emp.NOMBRE, s.NOMBRE, e.NOMBRE";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $query = "SELECT 
                    e.*,
                    s.NOMBRE as sede_nombre,
                    s.ID_SEDE,
                    emp.NOMBRE as empresa_nombre,
                    emp.ID_EMPRESA
                  FROM " . $this->table . " e
                  LEFT JOIN sede s ON e.ID_SEDE = s.ID_SEDE
                  LEFT JOIN empresa emp ON s.ID_EMPRESA = emp.ID_EMPRESA
                  WHERE e.ID_ESTABLECIMIENTO = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}

// models/Sede.php
class Sede {
    private $conn;
    private $table = 'sede';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getAll() {
        $query = "SELECT 
                    s.*,
                    e.NOMBRE as empresa_nombre,
                    e.ID_EMPRESA
                  FROM " . $this->table . " s
                  LEFT JOIN empresa e ON s.ID_EMPRESA = e.ID_EMPRESA
                  WHERE s.ESTADO = 'A'
                  ORDER BY e.NOMBRE, s.NOMBRE";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $query = "SELECT 
                    s.*,
                    e.NOMBRE as empresa_nombre,
                    e.ID_EMPRESA
                  FROM " . $this->table . " s
                  LEFT JOIN empresa e ON s.ID_EMPRESA = e.ID_EMPRESA
                  WHERE s.ID_SEDE = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}

// models/Empresa.php
class Empresa {
    private $conn;
    private $table = 'empresa';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " WHERE ESTADO = 'A' ORDER BY NOMBRE";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE ID_EMPRESA = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}
?>