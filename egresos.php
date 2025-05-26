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

// Procesar nuevo egreso
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_egreso'])) {
    $monto = floatval($_POST['monto']);
    $pagado_a = trim($_POST['pagado_a']);
    $fecha_pago = $_POST['fecha_pago'];
    $motivo = trim($_POST['motivo']);

    // Validaciones
    if ($monto <= 0) {
        $message = "El monto debe ser mayor a 0";
        $messageType = 'error';
    } elseif (empty($pagado_a)) {
        $message = "Debe especificar a quién se le pagó";
        $messageType = 'error';
    } elseif (empty($fecha_pago)) {
        $message = "Debe seleccionar la fecha de pago";
        $messageType = 'error';
    } elseif (empty($motivo)) {
        $message = "Debe especificar el motivo del egreso";
        $messageType = 'error';
    } else {
        try {
            $query = "INSERT INTO egresos (monto, pagado_a, fecha_pago, motivo, realizado_por) 
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$monto, $pagado_a, $fecha_pago, $motivo, $_SESSION['usuario_id']]);
            
            $message = "Egreso registrado correctamente";
            $messageType = 'success';
            
            // Limpiar formulario
            $_POST = array();
        } catch (Exception $e) {
            $message = "Error al registrar el egreso: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Filtros
$filtro_mes = $_GET['mes'] ?? date('Y-m');
$filtro_usuario = $_GET['usuario'] ?? '';

// Construir consulta con filtros
$where_conditions = [];
$params = [];

if ($filtro_mes) {
    $where_conditions[] = "DATE_FORMAT(e.fecha_pago, '%Y-%m') = ?";
    $params[] = $filtro_mes;
}

if ($filtro_usuario) {
    $where_conditions[] = "e.realizado_por = ?";
    $params[] = $filtro_usuario;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(' AND ', $where_conditions);
}

// Obtener egresos
$query = "SELECT e.*, u.nombre as realizado_por_nombre, u.rol
          FROM egresos e 
          JOIN usuarios u ON e.realizado_por = u.id
          $where_clause
          ORDER BY e.fecha_pago DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$egresos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener miembros del comité para filtro
$query = "SELECT id, nombre, rol FROM usuarios WHERE rol IN ('presidente', 'secretario', 'vocal') ORDER BY rol, nombre";
$stmt = $db->prepare($query);
$stmt->execute();
$miembros_comite = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular estadísticas
$total_egresos = array_sum(array_column($egresos, 'monto'));
$total_mes_actual = 0;
$total_general = 0;

// Total del mes actual
$query = "SELECT SUM(monto) as total FROM egresos WHERE DATE_FORMAT(fecha_pago, '%Y-%m') = ?";
$stmt = $db->prepare($query);
$stmt->execute([date('Y-m')]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_mes_actual = $result['total'] ?? 0;

// Total general
$query = "SELECT SUM(monto) as total FROM egresos";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_general = $result['total'] ?? 0;
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Egresos - Cluster Admin</title>
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

        .form-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
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

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
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

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .summary-card h3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .summary-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #dc3545;
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
            .container {
                padding: 0 1rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .table-container {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Control de Egresos</h1>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="pagos.php">Pagos</a>
            <a href="reportes.php">Reportes</a>
        </div>
    </div>

    <div class="container">
        <div class="page-title">
            <h2>Gestión de Egresos del Cluster</h2>
            <p>Registro y control de gastos realizados por el comité</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Resumen de Egresos -->
        <div class="summary-cards">
            <div class="summary-card">
                <h3>Total Este Mes</h3>
                <div class="value">$<?php echo number_format($total_mes_actual, 2); ?></div>
            </div>
            <div class="summary-card">
                <h3>Total Filtrado</h3>
                <div class="value">$<?php echo number_format($total_egresos, 2); ?></div>
            </div>
            <div class="summary-card">
                <h3>Total General</h3>
                <div class="value">$<?php echo number_format($total_general, 2); ?></div>
            </div>
            <div class="summary-card">
                <h3>Registros</h3>
                <div class="value"><?php echo count($egresos); ?></div>
            </div>
        </div>

        <!-- Formulario de Nuevo Egreso -->
        <div class="form-card">
            <h3 style="margin-bottom: 1.5rem;">💰 Registrar Nuevo Egreso</h3>
            
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="monto">Monto Pagado *</label>
                        <input type="number" id="monto" name="monto" step="0.01" min="0" 
                               value="<?php echo $_POST['monto'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pagado_a">Pagado a *</label>
                        <input type="text" id="pagado_a" name="pagado_a" 
                               placeholder="Nombre del proveedor o persona"
                               value="<?php echo $_POST['pagado_a'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_pago">Fecha de Pago *</label>
                        <input type="date" id="fecha_pago" name="fecha_pago" 
                               value="<?php echo $_POST['fecha_pago'] ?? date('Y-m-d'); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="motivo">Motivo del Egreso *</label>
                    <textarea id="motivo" name="motivo" rows="3" 
                              placeholder="Descripción detallada del gasto realizado..."
                              required><?php echo $_POST['motivo'] ?? ''; ?></textarea>
                </div>
                
                <button type="submit" name="registrar_egreso" class="btn btn-primary">
                    Registrar Egreso
                </button>
            </form>
        </div>

        <!-- Filtros -->
        <div class="filters">
            <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; width: 100%;">
                <div class="filter-group">
                    <label for="mes">Mes</label>
                    <input type="month" name="mes" id="mes" value="<?php echo $filtro_mes; ?>">
                </div>
                <div class="filter-group">
                    <label for="usuario">Realizado por</label>
                    <select name="usuario" id="usuario">
                        <option value="">Todos</option>
                        <?php foreach ($miembros_comite as $miembro): ?>
                        <option value="<?php echo $miembro['id']; ?>" 
                                <?php echo $filtro_usuario == $miembro['id'] ? 'selected' : ''; ?>>
                            <?php echo $miembro['nombre'] . ' (' . ucfirst($miembro['rol']) . ')'; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group" style="display: flex; align-items: end;">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>

        <!-- Tabla de Egresos -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Pagado a</th>
                        <th>Motivo</th>
                        <th>Realizado por</th>
                        <th>Fecha Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($egresos)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">No hay egresos registrados</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($egresos as $egreso): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($egreso['fecha_pago'])); ?></td>
                        <td><strong style="color: #dc3545;">$<?php echo number_format($egreso['monto'], 2); ?></strong></td>
                        <td><?php echo htmlspecialchars($egreso['pagado_a']); ?></td>
                        <td>
                            <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($egreso['motivo']); ?>
                            </div>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($egreso['realizado_por_nombre']); ?><br>
                            <small style="color: #666;"><?php echo ucfirst($egreso['rol']); ?></small>
                        </td>
                        <td>
                            <small><?php echo date('d/m/Y H:i', strtotime($egreso['fecha_registro'])); ?></small>
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
        document.getElementById('mes').addEventListener('change', function() {
            this.form.submit();
        });
        
        document.getElementById('usuario').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html>