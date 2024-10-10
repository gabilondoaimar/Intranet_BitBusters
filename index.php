<?php
include 'konexioa.php';
session_start();
require_once 'model/Curso.php';
require_once 'model/usuario.php';

// Conexión a la base de datos


// Manejo del login
$isLoggedIn = isset($_SESSION['usuario_id']);
?>

<!DOCTYPE html>
<html lang="eu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="styles/index.css" rel="stylesheet" type="text/css" />

</head>
<body>
    <header>
        <h1>Ikastetxeko Kurtsoak</h1>
        <nav>
            <ul>
                <?php if (!$isLoggedIn): ?>
                    <li><a href="view/login.php">Login</a></li>
                    <li><a href="view/register.php">Erregistratu</a></li>
                <?php else: ?>
                    <li><a href="view/logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Gaur egun eskaintzen diren kurtsoak</h2>
        <ul>
            <?php
            $curso = new Curso($conn);
            $cursos = $curso->getAll();

            foreach ($cursos as $cursoItem) {
                echo "<li>{$cursoItem['nombre']} - {$cursoItem['descripcion']}</li>";
                if ($isLoggedIn && $_SESSION['rol'] === 'ikaslea') {
                    echo "<form method='POST' action='matricular.php'>
                            <input type='hidden' name='curso_id' value='{$cursoItem['id']}'>
                            <button type='submit'>Matrikulatu</button>
                          </form>";
                }
            }
            ?>
        </ul>
    </main>
</body>
</html>
