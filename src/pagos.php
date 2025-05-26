<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pagos - Cluster Admin</title>
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

        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .filter-group select, .filter-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
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
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
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

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
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

        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .container {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once 'config/database.php';

    // Verificar que sea miembro del comité
    if (!in_array($_SESSION['rol'], ['presidente', 'secretario', 'vocal'])) {
        header("Location: dashboard.php");
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();
    
    $message = '';
    $messageType = '';

    // Procesar verificación de pago
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verificar_pago'])) {
        $pago_id = $_POST['pago_id'];
        
        try {
            $db->beginTransaction();
            
            // Verificar pago
            $query = "UPDATE pagos_mantenimiento SET verificado = 1, verificado_por = ?, fecha_verificacion = NOW() WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_SESSION['usuario_id'], $pago_id]);
            
            // Generar recibo
            $numero_recibo = 'REC-' . date('Y') . '-' . str_pad($pago_id, 5, '0', STR_PAD_LEFT);
            $query = "INSERT INTO recibos (pago_id, numero_recibo) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$pago_id, $numero_recibo]);
            
            $db->commit();
            $message = "Pago verificado correctamente. Recibo generado: " . $numero_recibo;
            $messageType = 'success';
            
        } catch (Exception $e) {
            $db->rollback();
            $message = "Error al verificar el pago: " . $e->getMessage();
            $messageType = 'error';
        }
    }

    // Filtros
    $filtro_estado = $_GET['estado'] ?? 'todos';
    $filtro_mes = $_GET['mes'] ?? date('Y-m');

    // Construir consulta con filtros
    $where_conditions = [];
    $params = [];

    if ($filtro_estado != 'todos') {
        if ($filtro_estado == 'verificado') {
            $where_conditions[] = "pm.verificado = 1";
        } else {
            $where_conditions[] = "pm.verificado = 0";
        }
    }

    if ($filtro_mes) {
        $where_conditions[] = "pm.mes_correspondiente = ?";
        $params[] = $filtro_mes;
    }

    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = "WHERE " . implode(' AND ', $where_conditions);
    }

    $query = "SELECT pm.*, u.nombre, u.numero_casa, u.email,
                     CASE WHEN pm.verificado = 1 THEN CONCAT(uv.nombre, ' (', uv.rol, ')') ELSE NULL END as verificado_por_nombre
              FROM pagos_mantenimiento pm 
              JOIN usuarios u ON pm.usuario_id = u.id
              LEFT JOIN usuarios uv ON pm.verificado_por = uv.id
              $where_clause
              ORDER BY pm.fecha_pago DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="header">
        <h1>💰 Gestión de Pagos</h1>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="egresos.php">Egresos</a>
            <a href="reportes.php">Reportes</a>
        </div>
    </div>

    <div class="container">
        <div class="page-title">
            <h2>Control de Pagos de Mantenimiento</h2>
            <p>Gestión y verificación de pagos de los inquilinos</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="filters">
            <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; width: 100%;">
                <div class="filter-group">
                    <label for="estado">Estado</label>
                    <select name="estado" id="estado">
                        <option value="todos" <?php echo $filtro_estado == 'todos' ? 'selected' : ''; ?>>Todos</option>
                        <option value="pendiente" <?php echo $filtro_estado == 'pendiente' ? 'selected' : ''; ?>>Pendientes</option>
                        <option value="verificado" <?php echo $filtro_estado == 'verificado' ? 'selected' : ''; ?>>Verificados</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="mes">Mes</label>
                    <input type="month" name="mes" id="mes" value="<?php echo $filtro_mes; ?>">
                </div>
                <div class="filter-group" style="display: flex; align-items: end;">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>

        <!-- Tabla de Pagos -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Casa</th>
                        <th>Inquilino</th>
                        <th>Mes</th>
                        <th>Monto</th>
                        <th>Recargo</th>
                        <th>Total</th>
                        <th>Fecha Pago</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pagos)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2rem;">No hay pagos registrados</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($pagos as $pago): ?>
                    <tr>
                        <td><?php echo $pago['numero_casa']; ?></td>
                        <td><?php echo htmlspecialchars($pago['nombre']); ?></td>
                        <td><?php echo date('m/Y', strtotime($pago['mes_correspondiente'] . '-01')); ?></td>
                        <td>$<?php echo number_format($pago['monto'], 2); ?></td>
                        <td>$<?php echo number_format($pago['recargo'], 2); ?></td>
                        <td><strong>$<?php echo number_format($pago['total'], 2); ?></strong></td>
                        <td><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></td>
                        <td>
                            <?php if ($pago['verificado']): ?>
                                <span class="status status-verified">Verificado</span>
                            <?php else: ?>
                                <span class="status status-pending">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$pago['verificado']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="pago_id" value="<?php echo $pago['id']; ?>">
                                    <button type="submit" name="verificar_pago" class="btn btn-success btn-sm" 
                                            onclick="return confirm('¿Verificar este pago?')">
                                        Verificar
                                    </button>
                                </form>
                            <?php else: ?>
                                <small>Verificado por:<br><?php echo $pago['verificado_por_nombre']; ?></small>
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
        // Auto-submit form cuando cambian los filtros
        document.getElementById('estado').addEventListener('change', function() {
            this.form.submit();
        });
        
        document.getElementById('mes').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html>