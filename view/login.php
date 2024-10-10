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

    // Consultar el usuario por email
    $stmt = $conn->prepare("SELECT id, password, rol FROM usuarios WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hashedPassword, $rol);
        $stmt->fetch();

        // Verificar la contraseña
        if (password_verify($pasahitza, $hashedPassword)) {
            $_SESSION['id'] = $id;  // Asegúrate de asignar correctamente el ID del usuario
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['rol'] = $rol;  // Asegúrate de asignar correctamente el rol del usuario
        
            // Redirige según el rol
            if ($rol === 'admin') {
                header('Location: administrazioa.php');
                exit();
            } else {
                header('Location: ikaslea.php');
                exit();
            }
        } else {
            $error = 'Pasahitza okerra da.';
        }
        
    } else {
        $error = 'Email hau ez dago erregistratuta.';
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
    <link href="../styles/login.css" rel="stylesheet" type="text/css" />
    <title>Login</title>
    
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if ($error): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="pasahitza">Pasahitza:</label>
            <input type="password" id="pasahitza" name="pasahitza" required>

            <button type="submit">Saioa hasi</button>
            <button type="button" onclick="window.location.href='../index.php';">Itzuli</button> <!-- Botón para volver -->
        </form>
        <p>Oraindik ez zara erregistratu? <a href="register.php">Erregistratu</a></p>
    </div>
</body>
</html>
