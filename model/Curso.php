<?php
class Curso {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM Cursos");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function crearCurso($nombre, $descripcion) {
        $stmt = $this->db->prepare("INSERT INTO Cursos (nombre, descripcion) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $descripcion);
        return $stmt->execute();
    }
}
?>
