<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cluster Admin - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        .alert {
            padding: 12px;
            margin-bottom: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .demo-credentials {
            margin-top: 2rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            font-size: 0.85rem;
        }

        .demo-credentials h4 {
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .demo-credentials p {
            color: #6c757d;
            margin: 0.25rem 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🏘️ Cluster Admin</h1>
            <p>Sistema de Administración del Conjunto</p>
        </div>

        <?php
        session_start();
        require_once 'config/database.php';

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            if (!empty($email) && !empty($password)) {
                $database = new Database();
                $db = $database->getConnection();

                $query = "SELECT id, nombre, email, password, rol, numero_casa FROM usuarios WHERE email = ? AND activo = 1";
                $stmt = $db->prepare($query);
                $stmt->execute([$email]);

                if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['usuario_id'] = $user['id'];
                        $_SESSION['nombre'] = $user['nombre'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['rol'] = $user['rol'];
                        $_SESSION['numero_casa'] = $user['numero_casa'];

                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error = "Credenciales incorrectas";
                    }
                } else {
                    $error = "Usuario no encontrado o inactivo";
                }
            } else {
                $error = "Por favor complete todos los campos";
            }
        }
        ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>

        <div class="demo-credentials">
            <h4>Credenciales de Demo:</h4>
            <p><strong>Presidente:</strong> admin@cluster.com / password</p>
            <p><strong>Secretario:</strong> secretario@cluster.com / password</p>
            <p><strong>Vocal:</strong> vocal@cluster.com / password</p>
        </div>
    </div>
</body>
</html>