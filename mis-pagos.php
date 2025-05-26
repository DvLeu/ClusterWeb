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

$message = '';
$messageType = '';

// Procesar nuevo pago
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_pago'])) {
    $monto = floatval($_POST['monto']);
    $recargo = floatval($_POST['recargo'] ?? 0);
    $fecha_pago = $_POST['fecha_pago'];
    $mes_correspondiente = $_POST['mes_correspondiente'];
    $concepto = trim($_POST['concepto']);
    
    $total = $monto + $recargo;

    // Validaciones
    if ($monto <= 0) {
        $message = "El monto debe ser mayor a 0";
        $messageType = 'error';
    } elseif (empty($fecha_pago)) {
        $message = "Debe seleccionar la fecha de pago";
        $messageType = 'error';
    } elseif (empty($mes_correspondiente)) {
        $message = "Debe seleccionar el mes correspondiente";
        $messageType = 'error';
    } else {
        // Verificar si ya existe un pago para ese mes
        $query = "SELECT COUNT(*) as total FROM pagos_mantenimiento 
                 WHERE usuario_id = ? AND mes_correspondiente = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['usuario_id'], $mes_correspondiente]);
        $existe = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existe['total'] > 0) {
            $message = "Ya existe un pago registrado para el mes seleccionado";
            $messageType = 'error';
        } else {
            try {
                $query = "INSERT INTO pagos_mantenimiento 
                         (usuario_id, monto, recargo, total, fecha_pago, mes_correspondiente, concepto) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$_SESSION['usuario_id'], $monto, $recargo, $total, $fecha_pago, $mes_correspondiente, $concepto]);
                
                $message = "Pago registrado correctamente. Pendiente de verificación por el comité.";
                $messageType = 'success';
            } catch (Exception $e) {
                $message = "Error al registrar el pago: " . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Obtener historial de pagos del usuario
$query = "SELECT pm.*, r.numero_recibo 
          FROM pagos_mantenimiento pm 
          LEFT JOIN recibos r ON pm.id = r.pago_id
          WHERE pm.usuario_id = ? 
          ORDER BY pm.fecha_pago DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['usuario_id']]);
$mis_pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular estadísticas
$total_pagado = array_sum(array_column($mis_pagos, 'total'));
$pagos_verificados = count(array_filter($mis_pagos, function($p) { return $p['verificado']; }));
$pagos_pendientes = count($mis_pagos) - $pagos_verificados;
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pagos - Cluster Admin</title>
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

        .page-title {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .payment-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .payment-info {
            background: #e3f2fd;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border-left: 4px solid #2196f3;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .info-item {
            text-align: center;
        }

        .info-item .label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .info-item .value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2196f3;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-size: 1rem;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a67d8;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .table tr:hover {
            background-color: #f8f9fa;
        }

        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-verified {
            background: #d4edda;
            color: #155724;
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .recargo-calc {
            background: #fff3cd;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
            display: none;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .table-container {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>💳 Mis Pagos de Mantenimiento</h1>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="reservaciones.php">Reservaciones</a>
            <a href="solicitudes.php">Solicitudes</a>
        </div>
    </div>

    <div class="container">
        <div class="page-title">
            <h2>Casa #<?php echo $_SESSION['numero_casa']; ?> - <?php echo $_SESSION['nombre']; ?></h2>
            <p>Registro y seguimiento de pagos de mantenimiento</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Información de Pagos -->
        <div class="payment-info">
            <h3 style="margin-bottom: 1rem;">📊 Resumen de Pagos</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Cuota Mensual</div>
                    <div class="value">$650.00</div>
                </div>
                <div class="info-item">
                    <div class="label">Recargo por Retraso</div>
                    <div class="value">$50.00</div>
                </div>
                <div class="info-item">
                    <div class="label">Total Pagado</div>
                    <div class="value">$<?php echo number_format($total_pagado, 2); ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Pagos Pendientes</div>
                    <div class="value"><?php echo $pagos_pendientes; ?></div>
                </div>
            </div>
        </div>

        <!-- Formulario de Nuevo Pago -->
        <div class="payment-form">
            <h3 style="margin-bottom: 1.5rem;">💰 Registrar Nuevo Pago</h3>
            
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="monto">Monto Pagado *</label>
                        <input type="number" id="monto" name="monto" step="0.01" min="0" value="650" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_pago">Fecha de Pago *</label>
                        <input type="date" id="fecha_pago" name="fecha_pago" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mes_correspondiente">Mes Correspondiente *</label>
                        <input type="month" id="mes_correspondiente" name="mes_correspondiente" value="<?php echo date('Y-m'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="recargo">Recargo por Retraso</label>
                        <input type="number" id="recargo" name="recargo" step="0.01" min="0" value="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="concepto">Concepto/Observaciones</label>
                    <textarea id="concepto" name="concepto" rows="3" placeholder="Pago de cuota de mantenimiento...">Pago de cuota de mantenimiento</textarea>
                </div>

                <div id="recargo-info" class="recargo-calc">
                    <strong>⚠️ Pago Extemporáneo Detectado</strong><br>
                    Se ha aplicado un recargo de $50.00 por pago fuera de tiempo.
                </div>
                
                <button type="submit" name="registrar_pago" class="btn btn-primary">
                    Registrar Pago
                </button>
            </form>
        </div>

        <!-- Historial de Pagos -->
        <div class="table-container">
            <h3 style="padding: 1.5rem; margin: 0; background: #f8f9fa; border-bottom: 1px solid #f0f0f0;">
                📋 Historial de Pagos
            </h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th>Monto</th>
                        <th>Recargo</th>
                        <th>Total</th>
                        <th>Fecha Pago</th>
                        <th>Estado</th>
                        <th>Recibo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mis_pagos)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem;">No hay pagos registrados</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($mis_pagos as $pago): ?>
                    <tr>
                        <td><?php echo date('m/Y', strtotime($pago['mes_correspondiente'] . '-01')); ?></td>
                        <td>$<?php echo number_format($pago['monto'], 2); ?></td>
                        <td>$<?php echo number_format($pago['recargo'], 2); ?></td>
                        <td><strong>$<?php echo number_format($pago['total'], 2); ?></strong></td>
                        <td><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></td>
                        <td>
                            <?php if ($pago['verificado']): ?>
                                <span class="status status-verified">✅ Verificado</span>
                            <?php else: ?>
                                <span class="status status-pending">⏳ Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($pago['numero_recibo']): ?>
                                <small><?php echo $pago['numero_recibo']; ?></small>
                            <?php else: ?>
                                <small>-</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Detectar pago extemporáneo
        document.getElementById('mes_correspondiente').addEventListener('change', function() {
            const mesSeleccionado = new Date(this.value + '-01');
            const hoy = new Date();
            const diaLimite = 5; // Hasta el día 5 del mes siguiente
            
            // Calcular fecha límite (día 5 del mes siguiente al seleccionado)
            const fechaLimite = new Date(mesSeleccionado.getFullYear(), mesSeleccionado.getMonth() + 1, diaLimite);
            
            const recargoInfo = document.getElementById('recargo-info');
            const campoRecargo = document.getElementById('recargo');
            
            if (hoy > fechaLimite) {
                // Pago extemporáneo
                recargoInfo.style.display = 'block';
                campoRecargo.value = '50.00';
            } else {
                recargoInfo.style.display = 'none';
                campoRecargo.value = '0.00';
            }
        });

        // Ejecutar al cargar la página
        document.getElementById('mes_correspondiente').dispatchEvent(new Event('change'));
    </script>
</body>
</html>