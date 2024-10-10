<?php
class Usuario {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Método para autenticar al usuario
    public function authenticate($email, $pasahitza) {
        // Buscar el email en la tabla 'ikasleak'
        $stmt = $this->db->prepare("
            SELECT ikasleak.id, usuarios.rol, usuarios.password 
            FROM ikasleak 
            JOIN usuarios ON ikasleak.id = usuarios.id
            WHERE usuarios.email = ?
        ");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Verificar si la contraseña es correcta
        if ($user && password_verify($pasahitza, $user['password'])) {
            return $user;
        }
        return false;
    }

    // Método para crear una nueva contraseña (al crear un usuario, por ejemplo)
    public function createPassword($plainPassword) {
        return password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    // Método para obtener todos los usuarios
    public function getAllUsuarios() {
        $query = "SELECT * FROM usuarios";
        $result = $this->db->query($query); // Usar $this->db en lugar de $this->mysqli

        // Manejo de errores si la consulta falla
        if ($result === false) {
            echo "Error en la consulta: " . $this->db->error;
            return [];
        }

        if ($result->num_rows > 0) {
            $usuarios = [];
            while ($row = $result->fetch_assoc()) {
                $usuarios[] = $row;
            }
            return $usuarios;
        } else {
            return [];
        }
    }
}
