<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Financieros - Cluster Admin</title>
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

        .report-controls {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .control-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
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

        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 1rem;
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
            margin-right: 1rem;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .summary-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .financial-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }

        .summary-card.positive {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .summary-card.negative {
            background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
        }

        .summary-card h3 {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }

        .summary-card .value {
            font-size: 2rem;
            font-weight: bold;
        }

        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .details-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .detail-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .detail-card h3 {
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
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

        .print-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        @media print {
            body { background: white; }
            .header, .nav-links, .report-controls, .btn { display: none; }
            .container { margin: 0; padding: 0; max-width: none; }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .control-grid {
                grid-template-columns: 1fr;
            }
            
            .details-section {
                grid-template-columns: 1fr;
            }
            
            .financial-summary {
                grid-template-columns: 1fr;
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

    // Parámetros del reporte
    $tipo_reporte = $_GET['tipo'] ?? 'mensual';
    $periodo_inicio = $_GET['inicio'] ?? date('Y-m-01');
    $periodo_fin = $_GET['fin'] ?? date('Y-m-t');
    $casa_especifica = $_GET['casa'] ?? '';

    // Si es reporte mensual, ajustar fechas
    if ($tipo_reporte == 'mensual') {
        $mes = $_GET['mes'] ?? date('Y-m');
        $periodo_inicio = $mes . '-01';
        $periodo_fin = date('Y-m-t', strtotime($periodo_inicio));
    } elseif ($tipo_reporte == 'anual') {
        $año = $_GET['año'] ?? date('Y');
        $periodo_inicio = $año . '-01-01';
        $periodo_fin = $año . '-12-31';
    }

    // Función para obtener datos financieros
    function obtenerDatosFinancieros($db, $inicio, $fin, $casa = '') {
        $datos = [
            'ingresos' => 0,
            'egresos' => 0,
            'pagos_verificados' => 0,
            'pagos_pendientes' => 0,
            'balance' => 0
        ];

        // Condición para casa específica
        $casa_condition = $casa ? "AND u.numero_casa = $casa" : "";

        // Ingresos (pagos verificados)
        $query = "SELECT SUM(pm.total) as total, COUNT(*) as cantidad 
                  FROM pagos_mantenimiento pm 
                  JOIN usuarios u ON pm.usuario_id = u.id
                  WHERE pm.verificado = 1 
                  AND pm.fecha_pago BETWEEN ? AND ? 
                  $casa_condition";
        $stmt = $db->prepare($query);
        $stmt->execute([$inicio, $fin]);
        $ingresos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $datos['ingresos'] = $ingresos['total'] ?? 0;
        $datos['pagos_verificados'] = $ingresos['cantidad'] ?? 0;

        // Pagos pendientes
        $query = "SELECT COUNT(*) as cantidad 
                  FROM pagos_mantenimiento pm 
                  JOIN usuarios u ON pm.usuario_id = u.id
                  WHERE pm.verificado = 0 
                  AND pm.fecha_pago BETWEEN ? AND ? 
                  $casa_condition";
        $stmt = $db->prepare($query);
        $stmt->execute([$inicio, $fin]);
        $pendientes = $stmt->fetch(PDO::FETCH_ASSOC);
        $datos['pagos_pendientes'] = $pendientes['cantidad'] ?? 0;

        // Egresos (solo si no es casa específica)
        if (!$casa) {
            $query = "SELECT SUM(monto) as total FROM egresos WHERE fecha_pago BETWEEN ? AND ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$inicio, $fin]);
            $egresos = $stmt->fetch(PDO::FETCH_ASSOC);
            $datos['egresos'] = $egresos['total'] ?? 0;
        }

        $datos['balance'] = $datos['ingresos'] - $datos['egresos'];

        return $datos;
    }

    // Obtener datos del reporte
    $datos_reporte = obtenerDatosFinancieros($db, $periodo_inicio, $periodo_fin, $casa_especifica);

    // Obtener detalles de pagos
    $casa_condition = $casa_especifica ? "AND u.numero_casa = " . intval($casa_especifica) : "";
    $query = "SELECT pm.*, u.nombre, u.numero_casa, r.numero_recibo
              FROM pagos_mantenimiento pm 
              JOIN usuarios u ON pm.usuario_id = u.id
              LEFT JOIN recibos r ON pm.id = r.pago_id
              WHERE pm.fecha_pago BETWEEN ? AND ? 
              $casa_condition
              ORDER BY pm.fecha_pago DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([$periodo_inicio, $periodo_fin]);
    $detalle_pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener detalles de egresos (solo si no es casa específica)
    $detalle_egresos = [];
    if (!$casa_especifica) {
        $query = "SELECT e.*, u.nombre as realizado_por_nombre
                  FROM egresos e 
                  JOIN usuarios u ON e.realizado_por = u.id
                  WHERE e.fecha_pago BETWEEN ? AND ?
                  ORDER BY e.fecha_pago DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$periodo_inicio, $periodo_fin]);
        $detalle_egresos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener casas para el filtro
    $query = "SELECT DISTINCT numero_casa FROM usuarios WHERE rol = 'inquilino' ORDER BY numero_casa";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $casas_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="header">
        <h1>📈 Reportes Financieros</h1>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="pagos.php">Pagos</a>
            <a href="egresos.php">Egresos</a>
        </div>
    </div>

    <div class="container">
        <div class="page-title">
            <h2>Reportes y Estados de Cuenta</h2>
            <p>Análisis financiero detallado del cluster</p>
        </div>

        <!-- Controles del Reporte -->
        <div class="report-controls">
            <h3 style="margin-bottom: 1.5rem;">🔧 Configuración del Reporte</h3>
            
            <form method="GET" style="margin-bottom: 2rem;">
                <div class="control-grid">
                    <div class="form-group">
                        <label for="tipo">Tipo de Reporte</label>
                        <select name="tipo" id="tipo" onchange="toggleDateInputs()">
                            <option value="mensual" <?php echo $tipo_reporte == 'mensual' ? 'selected' : ''; ?>>Mensual</option>
                            <option value="anual" <?php echo $tipo_reporte == 'anual' ? 'selected' : ''; ?>>Anual</option>
                            <option value="personalizado" <?php echo $tipo_reporte == 'personalizado' ? 'selected' : ''; ?>>Período Personalizado</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="mes-input" style="<?php echo $tipo_reporte != 'mensual' ? 'display:none' : ''; ?>">
                        <label for="mes">Mes</label>
                        <input type="month" name="mes" id="mes" value="<?php echo $_GET['mes'] ?? date('Y-m'); ?>">
                    </div>
                    
                    <div class="form-group" id="año-input" style="<?php echo $tipo_reporte != 'anual' ? 'display:none' : ''; ?>">
                        <label for="año">Año</label>
                        <input type="number" name="año" id="año" min="2020" max="2030" value="<?php echo $_GET['año'] ?? date('Y'); ?>">
                    </div>
                    
                    <div class="form-group" id="inicio-input" style="<?php echo $tipo_reporte != 'personalizado' ? 'display:none' : ''; ?>">
                        <label for="inicio">Fecha Inicio</label>
                        <input type="date" name="inicio" id="inicio" value="<?php echo $_GET['inicio'] ?? date('Y-m-01'); ?>">
                    </div>
                    
                    <div class="form-group" id="fin-input" style="<?php echo $tipo_reporte != 'personalizado' ? 'display:none' : ''; ?>">
                        <label for="fin">Fecha Fin</label>
                        <input type="date" name="fin" id="fin" value="<?php echo $_GET['fin'] ?? date('Y-m-t'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="casa">Casa Específica (Opcional)</label>
                        <select name="casa" id="casa">
                            <option value="">Todas las casas</option>
                            <?php foreach ($casas_disponibles as $casa): ?>
                            <option value="<?php echo $casa['numero_casa']; ?>" 
                                    <?php echo $casa_especifica == $casa['numero_casa'] ? 'selected' : ''; ?>>
                                Casa #<?php echo $casa['numero_casa']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Generar Reporte</button>
                <button type="button" class="btn btn-success" onclick="window.print()">Imprimir</button>
            </form>
        </div>

        <!-- Resumen Financiero -->
        <div class="summary-section">
            <h3 style="margin-bottom: 1.5rem;">💰 Resumen Financiero</h3>
            <p><strong>Período:</strong> <?php echo date('d/m/Y', strtotime($periodo_inicio)); ?> - <?php echo date('d/m/Y', strtotime($periodo_fin)); ?></p>
            <?php if ($casa_especifica): ?>
            <p><strong>Casa:</strong> #<?php echo $casa_especifica; ?></p>
            <?php endif; ?>
            
            <div class="financial-summary">
                <div class="summary-card positive">
                    <h3>Total Ingresos</h3>
                    <div class="value">$<?php echo number_format($datos_reporte['ingresos'], 2); ?></div>
                </div>
                
                <?php if (!$casa_especifica): ?>
                <div class="summary-card negative">
                    <h3>Total Egresos</h3>
                    <div class="value">$<?php echo number_format($datos_reporte['egresos'], 2); ?></div>
                </div>
                
                <div class="summary-card <?php echo $datos_reporte['balance'] >= 0 ? 'positive' : 'negative'; ?>">
                    <h3>Balance</h3>
                    <div class="value">$<?php echo number_format($datos_reporte['balance'], 2); ?></div>
                </div>
                <?php endif; ?>
                
                <div class="summary-card">
                    <h3>Pagos Verificados</h3>
                    <div class="value"><?php echo $datos_reporte['pagos_verificados']; ?></div>
                </div>
                
                <div class="summary-card">
                    <h3>Pagos Pendientes</h3>
                    <div class="value"><?php echo $datos_reporte['pagos_pendientes']; ?></div>
                </div>
            </div>
        </div>

        <!-- Detalles -->
        <div class="details-section">
            <!-- Detalle de Pagos -->
            <div class="detail-card">
                <h3>💳 Detalle de Ingresos</h3>
                <?php if (empty($detalle_pagos)): ?>
                    <p>No hay pagos en el período seleccionado</p>
                <?php else: ?>
                    <?php foreach ($detalle_pagos as $pago): ?>
                    <div class="detail-item">
                        <div>
                            <strong>Casa #<?php echo $pago['numero_casa']; ?></strong><br>
                            <small><?php echo htmlspecialchars($pago['nombre']); ?></small><br>
                            <small><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></small>
                        </div>
                        <div style="text-align: right;">
                            <strong>$<?php echo number_format($pago['total'], 2); ?></strong><br>
                            <?php if ($pago['verificado']): ?>
                                <span class="status status-verified">Verificado</span>
                            <?php else: ?>
                                <span class="status status-pending">Pendiente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Detalle de Egresos -->
            <?php if (!$casa_especifica): ?>
            <div class="detail-card">
                <h3>📊 Detalle de Egresos</h3>
                <?php if (empty($detalle_egresos)): ?>
                    <p>No hay egresos en el período seleccionado</p>
                <?php else: ?>
                    <?php foreach ($detalle_egresos as $egreso): ?>
                    <div class="detail-item">
                        <div>
                            <strong><?php echo htmlspecialchars($egreso['pagado_a']); ?></strong><br>
                            <small><?php echo htmlspecialchars(substr($egreso['motivo'], 0, 50)) . '...'; ?></small><br>
                            <small><?php echo date('d/m/Y', strtotime($egreso['fecha_pago'])); ?></small>
                        </div>
                        <div style="text-align: right;">
                            <strong style="color: #dc3545;">$<?php echo number_format($egreso['monto'], 2); ?></strong><br>
                            <small><?php echo htmlspecialchars($egreso['realizado_por_nombre']); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tabla Detallada de Pagos -->
        <div class="table-container">
            <h3 style="padding: 1.5rem; margin: 0; background: #f8f9fa; border-bottom: 1px solid #f0f0f0;">
                📋 Detalle Completo de Pagos
            </h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Casa</th>
                        <th>Inquilino</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Recargo</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Recibo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($detalle_pagos)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">No hay pagos en el período seleccionado</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($detalle_pagos as $pago): ?>
                    <tr>
                        <td><?php echo $pago['numero_casa']; ?></td>
                        <td><?php echo htmlspecialchars($pago['nombre']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></td>
                        <td>$<?php echo number_format($pago['monto'], 2); ?></td>
                        <td>$<?php echo number_format($pago['recargo'], 2); ?></td>
                        <td><strong>$<?php echo number_format($pago['total'], 2); ?></strong></td>
                        <td>
                            <?php if ($pago['verificado']): ?>
                                <span class="status status-verified">Verificado</span>
                            <?php else: ?>
                                <span class="status status-pending">Pendiente</span>
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

        <!-- Información adicional para impresión -->
        <div class="print-section" style="page-break-before: always;">
            <h3>📄 Información del Reporte</h3>
            <p><strong>Generado por:</strong> <?php echo $_SESSION['nombre']; ?> (<?php echo ucfirst($_SESSION['rol']); ?>)</p>
            <p><strong>Fecha de generación:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
            <p><strong>Período del reporte:</strong> <?php echo date('d/m/Y', strtotime($periodo_inicio)); ?> - <?php echo date('d/m/Y', strtotime($periodo_fin)); ?></p>
            <?php if ($casa_especifica): ?>
            <p><strong>Casa específica:</strong> #<?php echo $casa_especifica; ?></p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleDateInputs() {
            const tipo = document.getElementById('tipo').value;
            
            // Ocultar todos los inputs
            document.getElementById('mes-input').style.display = 'none';
            document.getElementById('año-input').style.display = 'none';
            document.getElementById('inicio-input').style.display = 'none';
            document.getElementById('fin-input').style.display = 'none';
            
            // Mostrar inputs según el tipo
            switch(tipo) {
                case 'mensual':
                    document.getElementById('mes-input').style.display = 'block';
                    break;
                case 'anual':
                    document.getElementById('año-input').style.display = 'block';
                    break;
                case 'personalizado':
                    document.getElementById('inicio-input').style.display = 'block';
                    document.getElementById('fin-input').style.display = 'block';
                    break;
            }
        }

        // Inicializar al cargar la página
        toggleDateInputs();
    </script>
</body>
</html>