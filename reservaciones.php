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

// Procesar nueva reservación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_reservacion'])) {
    $amenidades = $_POST['amenidad'] ?? [];
    $fecha_reserva = $_POST['fecha_reserva'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];

    // Validaciones
    if (empty($amenidades)) {
        $message = "Debe seleccionar al menos una amenidad";
        $messageType = 'error';
    } elseif (empty($fecha_reserva)) {
        $message = "Debe seleccionar la fecha de reservación";
        $messageType = 'error';
    } elseif (empty($hora_inicio) || empty($hora_fin)) {
        $message = "Debe especificar el horario completo";
        $messageType = 'error';
    } elseif (strtotime($fecha_reserva . ' ' . $hora_inicio) <= time()) {
        $message = "La fecha y hora deben ser futuras";
        $messageType = 'error';
    } elseif ($hora_inicio >= $hora_fin) {
        $message = "La hora de inicio debe ser anterior a la hora de fin";
        $messageType = 'error';
    } else {
        // Verificar disponibilidad
        $amenidad_str = implode(',', $amenidades);
        if (count($amenidades) > 1) {
            $amenidad_str = 'ambas';
        } else {
            $amenidad_str = $amenidades[0];
        }

        // Verificar conflictos de horario
        $query = "SELECT COUNT(*) as conflictos 
                 FROM reservaciones 
                 WHERE fecha_reserva = ? 
                 AND estado != 'rechazada'
                 AND (
                     (amenidad = ? OR amenidad = 'ambas' OR ? = 'ambas')
                     AND (
                         (hora_inicio <= ? AND hora_fin > ?) OR
                         (hora_inicio < ? AND hora_fin >= ?) OR
                         (hora_inicio >= ? AND hora_fin <= ?)
                     )
                 )";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $fecha_reserva, $amenidad_str, $amenidad_str,
            $hora_inicio, $hora_inicio,
            $hora_fin, $hora_fin,
            $hora_inicio, $hora_fin
        ]);
        
        $conflictos = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($conflictos['conflictos'] > 0) {
            $message = "Ya existe una reservación en ese horario para las amenidades seleccionadas";
            $messageType = 'error';
        } else {
            try {
                $query = "INSERT INTO reservaciones (usuario_id, amenidad, fecha_reserva, hora_inicio, hora_fin) 
                         VALUES (?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$_SESSION['usuario_id'], $amenidad_str, $fecha_reserva, $hora_inicio, $hora_fin]);
                
                $message = "Reservación solicitada correctamente. Pendiente de aprobación.";
                $messageType = 'success';
                
                // Limpiar formulario
                $_POST = array();
            } catch (Exception $e) {
                $message = "Error al crear la reservación: " . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Obtener reservaciones del usuario
$query = "SELECT * FROM reservaciones 
          WHERE usuario_id = ? 
          ORDER BY fecha_reserva DESC, hora_inicio DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['usuario_id']]);
$mis_reservaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si es miembro del comité, obtener todas las reservaciones
$todas_reservaciones = [];
if (in_array($_SESSION['rol'], ['presidente', 'secretario', 'vocal'])) {
    $query = "SELECT r.*, u.nombre, u.numero_casa 
              FROM reservaciones r 
              JOIN usuarios u ON r.usuario_id = u.id 
              WHERE r.estado = 'pendiente'
              ORDER BY r.fecha_solicitud ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $todas_reservaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Procesar aprobación/rechazo (solo comité)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aprobar_reservacion']) && in_array($_SESSION['rol'], ['presidente', 'secretario', 'vocal'])) {
    $reservacion_id = $_POST['reservacion_id'];
    $accion = $_POST['accion'];
    
    $nuevo_estado = ($accion == 'aprobar') ? 'aprobada' : 'rechazada';
    
    $query = "UPDATE reservaciones SET estado = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$nuevo_estado, $reservacion_id]);
    
    $message = "Reservación " . ($accion == 'aprobar' ? 'aprobada' : 'rechazada') . " correctamente";
    $messageType = 'success';
    
    // Refrescar la página
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservaciones - Cluster Admin</title>
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

        .amenities-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border-left: 4px solid #2196f3;
        }

        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .amenity-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .amenity-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .reservation-form {
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

        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .checkbox-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f8f9fa;
            padding: 0.75rem 1rem;
            border-radius: 5px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
        }

        .checkbox-item:hover {
            border-color: #667eea;
        }

        .checkbox-item input[type="checkbox"] {
            width: auto;
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

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .reservations-list {
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

        .status-pendiente {
            background: #fff3cd;
            color: #856404;
        }

        .status-aprobada {
            background: #d4edda;
            color: #155724;
        }

        .status-rechazada {
            background: #f8d7da;
            color: #721c24;
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

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .rules-section {
            background: #fff3cd;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border-left: 4px solid #ffc107;
        }

        .rules-section h3 {
            margin-bottom: 1rem;
            color: #856404;
        }

        .rules-section ul {
            margin-left: 1.5rem;
        }

        .rules-section li {
            margin-bottom: 0.5rem;
            color: #856404;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .amenities-grid {
                grid-template-columns: 1fr;
            }
            
            .checkbox-group {
                flex-direction: column;
            }
            
            .reservations-list {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏊‍♀️ Reservación de Amenidades</h1>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="mis-pagos.php">Mis Pagos</a>
            <a href="solicitudes.php">Solicitudes</a>
        </div>
    </div>

    <div class="container">
        <div class="page-title">
            <h2>Casa #<?php echo $_SESSION['numero_casa']; ?> - <?php echo $_SESSION['nombre']; ?></h2>
            <p>Solicita el uso de las amenidades del cluster</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Información de Amenidades -->
        <div class="amenities-info">
            <h3 style="margin-bottom: 1.5rem;">🌟 Amenidades Disponibles</h3>
            <div class="amenities-grid">
                <div class="amenity-card">
                    <div class="amenity-icon">🏊‍♀️</div>
                    <h4>Alberca</h4>
                    <p>Disfruta de un chapuzón refrescante en nuestra alberca</p>
                </div>
                <div class="amenity-card">
                    <div class="amenity-icon">🏠</div>
                    <h4>Palapa</h4>
                    <p>Espacio techado perfecto para reuniones y eventos</p>
                </div>
            </div>
        </div>

        <!-- Reglas de Uso -->
        <div class="rules-section">
            <h3>📋 Reglas de Uso</h3>
            <ul>
                <li>Las reservaciones deben hacerse con al menos 24 horas de anticipación</li>
                <li>El horario de uso es de 8:00 AM a 10:00 PM</li>
                <li>Máximo 4 horas de uso por reservación</li>
                <li>El usuario es responsable de mantener limpia el área</li>
                <li>No está permitido el uso de altavoces a alto volumen después de las 8:00 PM</li>
                <li>Las reservaciones están sujetas a aprobación del comité</li>
            </ul>
        </div>

        <!-- Formulario de Nueva Reservación -->
        <div class="reservation-form">
            <h3 style="margin-bottom: 1.5rem;">📅 Nueva Reservación</h3>
            
            <form method="POST">
                <div class="form-group">
                    <label>Amenidades a Reservar *</label>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="amenidad[]" value="palapa">
                            <span>🏠 Palapa</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="amenidad[]" value="alberca">
                            <span>🏊‍♀️ Alberca</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="fecha_reserva">Fecha de Reservación *</label>
                        <input type="date" id="fecha_reserva" name="fecha_reserva" 
                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                               value="<?php echo $_POST['fecha_reserva'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hora_inicio">Hora de Inicio *</label>
                        <input type="time" id="hora_inicio" name="hora_inicio" 
                               min="08:00" max="22:00"
                               value="<?php echo $_POST['hora_inicio'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hora_fin">Hora de Fin *</label>
                        <input type="time" id="hora_fin" name="hora_fin" 
                               min="08:00" max="22:00"
                               value="<?php echo $_POST['hora_fin'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <button type="submit" name="crear_reservacion" class="btn btn-primary">
                    Solicitar Reservación
                </button>
            </form>
        </div>

        <!-- Mis Reservaciones -->
        <div class="reservations-list">
            <h3 style="padding: 1.5rem; margin: 0; background: #f8f9fa; border-bottom: 1px solid #f0f0f0;">
                📋 Mis Reservaciones
            </h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Amenidad</th>
                        <th>Fecha</th>
                        <th>Horario</th>
                        <th>Estado</th>
                        <th>Solicitud</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mis_reservaciones)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem;">No tienes reservaciones</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($mis_reservaciones as $reservacion): ?>
                    <tr>
                        <td>
                            <?php if ($reservacion['amenidad'] == 'palapa'): ?>
                                🏠 Palapa
                            <?php elseif ($reservacion['amenidad'] == 'alberca'): ?>
                                🏊‍♀️ Alberca
                            <?php else: ?>
                                🏠🏊‍♀️ Ambas
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($reservacion['fecha_reserva'])); ?></td>
                        <td>
                            <?php echo date('H:i', strtotime($reservacion['hora_inicio'])); ?> - 
                            <?php echo date('H:i', strtotime($reservacion['hora_fin'])); ?>
                        </td>
                        <td>
                            <?php
                            $status_class = 'status-' . $reservacion['estado'];
                            $status_text = ucfirst($reservacion['estado']);
                            if ($reservacion['estado'] == 'pendiente') $status_text = '⏳ Pendiente';
                            elseif ($reservacion['estado'] == 'aprobada') $status_text = '✅ Aprobada';
                            elseif ($reservacion['estado'] == 'rechazada') $status_text = '❌ Rechazada';
                            ?>
                            <span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </td>
                        <td>
                            <small><?php echo date('d/m/Y H:i', strtotime($reservacion['fecha_solicitud'])); ?></small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Panel del Comité (solo para miembros del comité) -->
        <?php if (in_array($_SESSION['rol'], ['presidente', 'secretario', 'vocal']) && !empty($todas_reservaciones)): ?>
        <div class="reservations-list" style="margin-top: 2rem;">
            <h3 style="padding: 1.5rem; margin: 0; background: #fff3cd; border-bottom: 1px solid #f0f0f0; color: #856404;">
                🛠️ Reservaciones Pendientes de Aprobación
            </h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Casa</th>
                        <th>Inquilino</th>
                        <th>Amenidad</th>
                        <th>Fecha</th>
                        <th>Horario</th>
                        <th>Solicitud</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todas_reservaciones as $reservacion): ?>
                    <tr>
                        <td>#<?php echo $reservacion['numero_casa']; ?></td>
                        <td><?php echo htmlspecialchars($reservacion['nombre']); ?></td>
                        <td>
                            <?php if ($reservacion['amenidad'] == 'palapa'): ?>
                                🏠 Palapa
                            <?php elseif ($reservacion['amenidad'] == 'alberca'): ?>
                                🏊‍♀️ Alberca
                            <?php else: ?>
                                🏠🏊‍♀️ Ambas
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($reservacion['fecha_reserva'])); ?></td>
                        <td>
                            <?php echo date('H:i', strtotime($reservacion['hora_inicio'])); ?> - 
                            <?php echo date('H:i', strtotime($reservacion['hora_fin'])); ?>
                        </td>
                        <td>
                            <small><?php echo date('d/m/Y H:i', strtotime($reservacion['fecha_solicitud'])); ?></small>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="reservacion_id" value="<?php echo $reservacion['id']; ?>">
                                <button type="submit" name="aprobar_reservacion" value="aprobar" 
                                        class="btn btn-success btn-sm"
                                        onclick="this.form.accion.value='aprobar'">
                                    ✅ Aprobar
                                </button>
                                <button type="submit" name="aprobar_reservacion" value="rechazar" 
                                        class="btn btn-danger btn-sm"
                                        onclick="this.form.accion.value='rechazar'"
                                        style="margin-left: 0.5rem;">
                                    ❌ Rechazar
                                </button>
                                <input type="hidden" name="accion" value="">
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Validar horarios
        document.getElementById('hora_inicio').addEventListener('change', function() {
            const horaInicio = this.value;
            const horaFinInput = document.getElementById('hora_fin');
            
            if (horaInicio) {
                // Calcular hora mínima de fin (1 hora después del inicio)
                const [horas, minutos] = horaInicio.split(':');
                const horaInicioDate = new Date();
                horaInicioDate.setHours(parseInt(horas), parseInt(minutos));
                horaInicioDate.setHours(horaInicioDate.getHours() + 1);
                
                const horaMinFin = horaInicioDate.toTimeString().slice(0, 5);
                horaFinInput.min = horaMinFin;
                
                // Si la hora de fin es menor que la mínima, actualizarla
                if (horaFinInput.value && horaFinInput.value <= horaInicio) {
                    horaFinInput.value = horaMinFin;
                }
            }
        });

        document.getElementById('hora_fin').addEventListener('change', function() {
            const horaInicio = document.getElementById('hora_inicio').value;
            const horaFin = this.value;
            
            if (horaInicio && horaFin) {
                // Verificar que no exceda 4 horas
                const [horasInicio, minutosInicio] = horaInicio.split(':');
                const [horasFin, minutosFin] = horaFin.split(':');
                
                const inicioMinutos = parseInt(horasInicio) * 60 + parseInt(minutosInicio);
                const finMinutos = parseInt(horasFin) * 60 + parseInt(minutosFin);
                
                const duracionMinutos = finMinutos - inicioMinutos;
                
                if (duracionMinutos > 240) { // 4 horas = 240 minutos
                    alert('La duración máxima de la reservación es de 4 horas');
                    
                    // Calcular hora máxima permitida
                    const maxFinMinutos = inicioMinutos + 240;
                    const maxHoras = Math.floor(maxFinMinutos / 60);
                    const maxMinutos = maxFinMinutos % 60;
                    
                    this.value = String(maxHoras).padStart(2, '0') + ':' + String(maxMinutos).padStart(2, '0');
                }
            }
        });

        // Validar fecha mínima (24 horas de anticipación)
        document.getElementById('fecha_reserva').min = '<?php echo date('Y-m-d', strtotime('+1 day')); ?>';
    </script>
</body>
</html>