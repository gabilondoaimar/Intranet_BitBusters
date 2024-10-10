<?php
include '../konexioa.php';
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'ikaslea') {
    header('Location: login.php'); // Redirigir al login si no está autenticado o no es ikaslea
    exit();
}

// Obtener la información del usuario desde la sesión
$id_usuario = $_SESSION['id'];
$rol_usuario = $_SESSION['rol'];

// Crear conexión a la base de datos

if ($conn->connect_error) {
    die('Conexión fallida: ' . $conn->connect_error);
}

// Obtener el email del usuario desde la tabla usuarios usando el id
$stmt = $conn->prepare("SELECT email FROM usuarios WHERE id = ?");
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$stmt->bind_result($email_usuario);
$stmt->fetch();
$stmt->close();

// Verificar si se encontró el email
if ($email_usuario === null) {
    echo "No se encontró el email para este ID de usuario.";
    exit();
}

// Obtener el nombre y apellido del usuario desde la tabla ikasleak usando email
$stmt = $conn->prepare("SELECT izena, abizena FROM ikasleak WHERE email = ?");
$stmt->bind_param('s', $email_usuario);
$stmt->execute();
$stmt->bind_result($nombre_usuario, $apellido_usuario);
$stmt->fetch();
$stmt->close();

// Comprobar si se encontraron resultados
if ($nombre_usuario === null || $apellido_usuario === null) {
    echo "No se encontraron resultados para este email: " . htmlspecialchars($email_usuario);
    exit();
}

// Obtener los cursos activos
$cursos = [];
$stmt = $conn->prepare("SELECT id, nombre FROM cursos");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $cursos[] = $row;
}
$stmt->close();

// Verificar si el usuario ya está inscrito en un curso
$curs_id = null;
$stmt = $conn->prepare("SELECT curso FROM usuarios WHERE id = ?");
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$stmt->bind_result($curs_id);
$stmt->fetch();
$stmt->close();

// Manejar inscripción y baja de curso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['apuntarse']) && $curs_id === null) {
        $curso_id = $_POST['curso_id'];
        $stmt = $conn->prepare("UPDATE usuarios SET curso = ? WHERE id = ?");
        $stmt->bind_param('ii', $curso_id, $id_usuario);
        $stmt->execute();
        $stmt->close();
        header('Location: ikaslea.php'); // Redirigir para evitar el reenvío del formulario
        exit();
    }

    if (isset($_POST['desapuntarse']) && $curs_id !== null) {
        $stmt = $conn->prepare("UPDATE usuarios SET curso = NULL WHERE id = ?");
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        $stmt->close();
        header('Location: ikaslea.php'); // Redirigir para evitar el reenvío del formulario
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="eu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../styles/ikaslea.css" rel="stylesheet" type="text/css" />
    <title>Ikaslea Dashboard</title>
</head>
<body>
    <header>
        <h1>Ongi Etorri, Ikaslea!</h1>
    </header>

    <main>
        <!-- Sección de información del usuario -->
        <section>
            <h2>Zure Informazioa</h2>
            <div class="user-info">
                <p><strong>ID:</strong> <?= htmlspecialchars($id_usuario); ?></p>
                <p><strong>Nombre:</strong> <?= htmlspecialchars($nombre_usuario); ?></p>
                <p><strong>Apellido:</strong> <?= htmlspecialchars($apellido_usuario); ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($email_usuario); ?></p>
                <p><strong>Rol:</strong> <?= htmlspecialchars($rol_usuario); ?></p>
            </div>
            <div class="logout-btn">
                <form action="logout.php" method="post">
                    <button type="submit">Saioa itxi</button>
                </form>
            </div>
        </section>

        <!-- Sección de cursos -->
        <section>
            <h2>Kursuak Aktiboak</h2>
            <ul>
                <?php foreach ($cursos as $curso): ?>
                    <li>
                        <span><?= htmlspecialchars($curso['nombre']); ?></span>
                        <?php if ($curs_id === $curso['id']): ?>
                            <form method="POST">
                                <button type="submit" name="desapuntarse">Desapuntatu</button>
                            </form>
                        <?php elseif ($curs_id === null): ?>
                            <form method="POST">
                                <input type="hidden" name="curso_id" value="<?= $curso['id']; ?>">
                                <button type="submit" name="apuntarse">Apuntatu</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>
</body>
</html>
