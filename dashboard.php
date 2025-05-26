<?php
session_start();
require_once 'config/database.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener estadísticas generales
$total_casas = 60;
$cuota_mantenimiento = 650;

// Estadísticas para el comité
if (in_array($_SESSION['rol'], ['presidente', 'secretario', 'vocal'])) {
    // Total de pagos verificados este mes
    $query = "SELECT COUNT(*) as total, SUM(total) as monto FROM pagos_mantenimiento 
             WHERE verificado = 1 AND DATE_FORMAT(fecha_pago, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $pagos_mes = $stmt->fetch(PDO::FETCH_ASSOC);

    // Pagos pendientes de verificación
    $query = "SELECT COUNT(*) as total FROM pagos_mantenimiento WHERE verificado = 0";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $pagos_pendientes = $stmt->fetch(PDO::FETCH_ASSOC);

    // Total de egresos este mes
    $query = "SELECT SUM(monto) as total FROM egresos 
             WHERE DATE_FORMAT(fecha_pago, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $egresos_mes = $stmt->fetch(PDO::FETCH_ASSOC);

    // Solicitudes pendientes
    $query = "SELECT COUNT(*) as total FROM solicitudes_servicios WHERE estado = 'pendiente'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $solicitudes_pendientes = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Actividad reciente
$query = "SELECT 'pago' as tipo, CONCAT('Pago de ', u.nombre, ' - Casa ', u.numero_casa) as descripcion, 
                 pm.fecha_pago as fecha
          FROM pagos_mantenimiento pm 
          JOIN usuarios u ON pm.usuario_id = u.id 
          WHERE pm.verificado = 1
          UNION ALL
          SELECT 'solicitud' as tipo, CONCAT('Solicitud: ', titulo) as descripcion, fecha_solicitud as fecha
          FROM solicitudes_servicios
          ORDER BY fecha DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$actividad_reciente = $stmt->fetchAll(PDO::FETCH_ASSOC);
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cluster Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 1.8rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-role {
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .nav-links {
            display: flex;
            gap: 1rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            background: rgba(255,255,255,0.2);
        }

        .nav-links a:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }

        .stat-card h3 {
            color: #333;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }

        .navigation {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .nav-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-decoration: none;
            color: #333;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .nav-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .nav-card h3 {
            color: #667eea;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-card p {
            color: #666;
            font-size: 0.9rem;
        }

        .recent-activity {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .recent-activity h3 {
            color: #333;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .activity-item {
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-text {
            flex: 1;
        }

        .activity-date {
            color: #666;
            font-size: 0.85rem;
        }

        .icon {
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .header {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }

            .container {
                padding: 0 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .navigation {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏘️ Cluster Admin</h1>
        <div class="user-info">
            <span><?php echo $_SESSION['nombre']; ?></span>
            <span class="user-role"><?php echo ucfirst($_SESSION['rol']); ?> - Casa <?php echo $_SESSION['numero_casa']; ?></span>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="equipo.php">Nuestro Equipo</a>
                <a href="logout.php">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (in_array($_SESSION['rol'], ['presidente', 'secretario', 'vocal'])): ?>
        <!-- Estadísticas del Comité -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Pagos Verificados (Este Mes)</h3>
                <div class="value"><?php echo $pagos_mes['total'] ?? 0; ?></div>
                <p>Total: <?php echo '$' . number_format($pagos_mes['monto'] ?? 0, 2); ?></p>
            </div>
            <div class="stat-card">
                <h3>Pagos Pendientes</h3>
                <div class="value"><?php echo $pagos_pendientes['total'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h3>Egresos (Este Mes)</h3>
                <div class="value"><?php echo '$' . number_format($egresos_mes['total'] ?? 0, 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Solicitudes Pendientes</h3>
                <div class="value"><?php echo $solicitudes_pendientes['total'] ?? 0; ?></div>
            </div>
        </div>

        <!-- Navegación del Comité -->
        <div class="navigation">
            <a href="pagos.php" class="nav-card">
                <h3><span class="icon">💰</span> Gestión de Pagos</h3>
                <p>Verificar pagos de mantenimiento y generar recibos</p>
            </a>
            <a href="egresos.php" class="nav-card">
                <h3><span class="icon">📊</span> Control de Egresos</h3>
                <p>Registrar gastos y administrar salidas de dinero</p>
            </a>
            <a href="reportes.php" class="nav-card">
                <h3><span class="icon">📈</span> Reportes Financieros</h3>
                <p>Consultar estados de cuenta y reportes mensuales</p>
            </a>
            <a href="solicitudes.php" class="nav-card">
                <h3><span class="icon">📋</span> Solicitudes de Servicio</h3>
                <p>Administrar peticiones de los inquilinos</p>
            </a>
        </div>
        <?php else: ?>
        <!-- Navegación del Inquilino -->
        <div class="navigation">
            <a href="mis-pagos.php" class="nav-card">
                <h3><span class="icon">💳</span> Mis Pagos</h3>
                <p>Registrar pagos de mantenimiento y ver historial</p>
            </a>
            <a href="reservaciones.php" class="nav-card">
                <h3><span class="icon">🏊‍♀️</span> Reservar Amenidades</h3>
                <p>Solicitar palapa y/o alberca</p>
            </a>
            <a href="solicitudes.php" class="nav-card">
                <h3><span class="icon">🔧</span> Solicitudes de Servicio</h3>
                <p>Reportar problemas o solicitar mantenimiento</p>
            </a>
            <a href="equipo.php" class="nav-card">
                <h3><span class="icon">👥</span> Nuestro Equipo</h3>
                <p>Conoce a los desarrolladores del sistema</p>
            </a>
        </div>
        <?php endif; ?>

        <!-- Actividad Reciente -->
        <div class="recent-activity">
            <h3>Actividad Reciente</h3>
            <?php if (empty($actividad_reciente)): ?>
                <p>No hay actividad reciente</p>
            <?php else: ?>
                <?php foreach ($actividad_reciente as $actividad): ?>
                <div class="activity-item">
                    <div class="activity-text"><?php echo htmlspecialchars($actividad['descripcion']); ?></div>
                    <div class="activity-date"><?php echo date('d/m/Y', strtotime($actividad['fecha'])); ?></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>