<?php
session_start();
include '../konexioa.php';
require_once '../model/Curso.php';
require_once '../model/usuario.php';

// Verifica si el usuario es administrador
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}



$curso = new Curso($conn);
$usuario = new Usuario($conn);

// Crear un nuevo curso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_curso'])) {
    $nombre_curso = $_POST['nombre_curso'];
    $descripcion_curso = $_POST['descripcion_curso'];
    $curso->crearCurso($nombre_curso, $descripcion_curso);
}

// Crear un nuevo ikaslea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_ikaslea'])) {
    $izena = $_POST['izena'];
    $abizena = $_POST['abizena'];
    $email = $_POST['email'];
    $adina = $_POST['adina'];
    
    // Comenzar la transacción
    $conn->begin_transaction();

    try {
        // Insertar en la tabla de ikasleak directamente
        $stmt = $conn->prepare("INSERT INTO ikasleak (izena, abizena, adina, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssis', $izena, $abizena, $adina, $email);
        $stmt->execute();

        // Confirmar la transacción
        $conn->commit();
        $success = 'Ikaslea arrakastaz sortu da!';
    } catch (Exception $e) {
        // Deshacer la transacción en caso de error
        $conn->rollback();
        $error = 'Errore bat gertatu da: ' . $e->getMessage();
    }
    $stmt->close();
}

// Obtener la lista de todos los ikasleak
$ikasleak = $conn->query("SELECT * FROM ikasleak");

// Función para borrar un ikaslea y su usuario
if (isset($_GET['borrar'])) {
    $id_to_delete = $_GET['borrar'];

    // Comenzar la transacción
    $conn->begin_transaction();

    try {
        // Obtener el ID del usuario relacionado
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email IN (SELECT email FROM ikasleak WHERE id = ?)");
        $stmt->bind_param('i', $id_to_delete);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $userId = $user['id'];

        // Eliminar de la tabla ikasleak
        $stmt = $conn->prepare("DELETE FROM ikasleak WHERE id = ?");
        $stmt->bind_param('i', $id_to_delete);
        $stmt->execute();

        // Eliminar de la tabla usuarios
        if ($userId) {
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
        }

        // Confirmar la transacción
        $conn->commit();
        header("Location: administrazioa.php"); // Redirigir para evitar múltiples eliminaciones
        exit;
    } catch (Exception $e) {
        // Deshacer la transacción en caso de error
        $conn->rollback();
        $error = 'Errore bat gertatu da: ' . $e->getMessage();
    }
}

// Editar un ikaslea
if (isset($_GET['editar'])) {
    $id_to_edit = $_GET['editar'];
    $stmt = $conn->prepare("SELECT * FROM ikasleak WHERE id = ?");
    $stmt->bind_param('i', $id_to_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    $ikaslea = $result->fetch_assoc();
    $stmt->close();
}

// Actualizar ikaslea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_ikaslea'])) {
    $id_ikaslea = $_POST['id'];
    $izena = $_POST['izena'];
    $abizena = $_POST['abizena'];
    $email = $_POST['email'];
    $adina = $_POST['adina'];

    $stmt = $conn->prepare("UPDATE ikasleak SET izena = ?, abizena = ?, email = ?, adina = ? WHERE id = ?");
    $stmt->bind_param('ssssi', $izena, $abizena, $email, $adina, $id_ikaslea);
    $stmt->execute();
    $stmt->close();
    header("Location: administrazioa.php"); // Redirigir después de la actualización
    exit;
}
?>
<!DOCTYPE html>
<html lang="eu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../styles/ikaslea.css" rel="stylesheet" type="text/css" />
    <title>Administrazioa</title>
</head>
<body>
    <header>
        <h1>Administrazioa - Kurtsoen kudeaketa</h1>
        <nav>
            <ul>
                <li><a href="index.php" class="nav-btn">Ikastetxeko Kurtsoak</a></li>
                <li><a href="logout.php" class="nav-btn">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section>
            <h2>Administrazio Aukerak</h2>
            <button onclick="toggleSection('crearCurso')">Kurtsoa sortu</button>
            <button onclick="toggleSection('verIkasles')">Ikasleak ikusi</button>
            <button onclick="toggleSection('crearIkaslea')">Ikaslea sortu</button>
        </section>

        <!-- Crear Curso Form -->
        <section id="crearCurso" class="form-section" style="display: none;">
            <h2>Kurtso berria sortu</h2>
            <form method="POST">
                <label for="nombre_curso">Kurtsoaren izena:</label>
                <input type="text" id="nombre_curso" name="nombre_curso" required>
                <label for="descripcion_curso">Deskribapena:</label>
                <textarea id="descripcion_curso" name="descripcion_curso" rows="4" required></textarea>
                <button type="submit" name="crear_curso">Kurtsoa Sortu</button>
            </form>
        </section>

        <!-- Ver Ikasleak (Tabla con Estilo) -->
        <section id="verIkasles" class="form-section" style="display: none;">
            <h2>Ikasleak</h2>
            <?php if ($ikasleak->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Izena</th>
                        <th>Abizena</th>
                        <th>Adina</th>
                        <th>Email</th>
                        <th>Ekintzak</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $ikasleak->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['izena']); ?></td>
                        <td><?php echo htmlspecialchars($row['abizena']); ?></td>
                        <td><?php echo htmlspecialchars($row['adina']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <a href="administrazioa.php?editar=<?php echo $row['id']; ?>" class="edit-btn">Editatu</a>
                            <a href="administrazioa.php?borrar=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Ziur ikaslea ezabatu nahi duzula?');">Ezabatu</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>Ez dago ikasleik.</p>
            <?php endif; ?>
        </section>

        <!-- Crear Ikaslea Form -->
        <section id="crearIkaslea" class="form-section" style="display: none;">
            <h2>Ikasle berria sortu</h2>
            <form method="POST">
                <label for="izena">Izena:</label>
                <input type="text" id="izena" name="izena" required>
                <label for="abizena">Abizena:</label>
                <input type="text" id="abizena" name="abizena" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="adina">Adina:</label>
                <input type="number" id="adina" name="adina" required>
                <button type="submit" name="crear_ikaslea">Ikaslea Sortu</button>
            </form>
        </section>
    </main>

    <script>
        let lastOpened = null;

        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            
            if (lastOpened && lastOpened !== section) {
                lastOpened.style.display = 'none';
            }
            
            if (section.style.display === 'none' || section.style.display === '') {
                section.style.display = 'block';
                lastOpened = section;
            } else {
                section.style.display = 'none';
                lastOpened = null;
            }
        }
    </script>
</body>
</html>
