<?php
include '../konexioa.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pasahitza = $_POST['pasahitza'];

    // Crear conexión a la base de datos
    
    if ($conn->connect_error) {
        die('Conexión fallida: ' . $conn->connect_error);
    }

    // Comprobar si el email está en la tabla ikasleak
    $stmt = $conn->prepare("SELECT id FROM ikasleak WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $error = 'Email hau ez dago ikasleak taulan. Mesedez, lehenik ikaslea izan behar duzu.';
    } else {
        // Comprobar si el email ya está registrado en la tabla usuarios
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Email hau jada erregistratuta dago.';
        } else {
            // Comenzar transacción
            $mysqli->begin_transaction();

            try {
                // Insertar en la tabla de usuarios
                $hashedPassword = password_hash($pasahitza, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO usuarios (email, password, rol) VALUES (?, ?, 'ikaslea')");
                $stmt->bind_param('ss', $email, $hashedPassword);
                $stmt->execute();

                // Si todo ha ido bien, confirmar la transacción
                $conn->commit();
                $success = 'Erregistratu zara! Saioa hasi dezakezu.';
            } catch (Exception $e) {
                // Si hay un error, deshacer la transacción
                $conn->rollback();
                $error = 'Errore bat gertatu da. Mesedez, saiatu berriro: ' . $e->getMessage();
            }
        }
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="eu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erregistratu</title>
    <link href="../styles/register.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="container">
        <h1>Erregistratu</h1>
        <?php if ($error): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success-message"><?php echo $success; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="pasahitza">Pasahitza:</label>
            <input type="password" id="pasahitza" name="pasahitza" required>

            <button type="submit">Erregistratu</button>
            <button type="button" onclick="window.location.href='../index.php';">Itzuli</button> <!-- Botón para volver -->
        </form>
        <p>Erregistratuta zaude?  <a href="login.php">Login</a></p>
    </div>
</body>
</html>
