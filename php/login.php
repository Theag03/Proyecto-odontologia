<?php
session_start();
require_once 'conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = limpiarDatos($_POST['username']);
    $password = limpiarDatos($_POST['password']);
    
    try {
        $stmt = $conexion->prepare("SELECT id, username, password, nombre_completo, rol FROM usuarios WHERE username = :username AND activo = 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $usuario['password'])) {
                // Iniciar sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['username'] = $usuario['username'];
                $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
                $_SESSION['rol'] = $usuario['rol'];
                
                // Actualizar último acceso
                $update = $conexion->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id");
                $update->bindParam(':id', $usuario['id']);
                $update->execute();
                
                // Redirigir según el rol
                if ($usuario['rol'] == 'admin') {
                    header('Location: admin.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
        } else {
            $error = "Usuario o contraseña incorrectos";
        }
    } catch(PDOException $e) {
        $error = "Error al intentar iniciar sesión";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - AgendaDental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            height: 80px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="IMG/Gemini_Generated_Image_lqwt86lqwt86lqwt.png" alt="Logo AgendaDental">
        </div>
        <h2 class="text-center mb-4">Iniciar Sesión</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>