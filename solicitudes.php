<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes de Servicio - Cluster Admin</title>
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

        .new-request-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
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

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
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

        .requests-forum {
            display: grid;
            gap: 1.5rem;
        }

        .request-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #e1e5e9;
        }

        .request-card.pendiente {
            border-left-color: #ffc107;
        }

        .request-card.en_proceso {
            border-left-color: #17a2b8;
        }

        .request-card.completado {
            border-left-color: #28a745;
        }

        .request-card.rechazado {
            border-left-color: #dc3545;
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .request-title {
            flex: 1;
        }

        .request-title h3 {
            margin-bottom: 0.5rem;
            color: #333;
        }

        .request-meta {
            font-size: 0.9rem;
            color: #666;
        }

        .request-status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-pendiente {
            background: #fff3cd;
            color: #856404;
        }

        .status-en_proceso {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-completado {
            background: #d4edda;
            color: #155724;
        }

        .status-rechazado {
            background: #f8d7da;
            color: #721c24;
        }

        .request-description {
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .request-comment {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
            border-left: 3px solid #667eea;
        }

        .comment-header {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #667eea;
        }

        .request-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .admin-actions {
            background: #fff3cd;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
        }

        .update-form {
            display: none;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
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

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .request-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .request-actions {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
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

    // Procesar nueva solicitud
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_solicitud'])) {
        $titulo = trim($_POST['titulo']);
        $descripcion = trim($_POST['descripcion']);

        // Validaciones
        if (empty($titulo)) {
            $message = "El título es obligatorio";
            $messageType = 'error';
        } elseif (empty($descripcion)) {
            $message = "La descripción es obligatoria";
            $messageType = 'error';
        } else {
            try {
                $query = "INSERT INTO solicitudes_servicios (usuario_id, titulo, descripcion) 
                         VALUES (?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$_SESSION['usuario_id'], $titulo, $descripcion]);
                
                $message = "Solicitud creada correctamente";
                $messageType = 'success';
                
                // Limpiar formulario
                $_POST = array();
            } catch (Exception $e) {
                $message = "Error al crear la solicitud: " . $e->getMessage();
                $messageType = 'error';
            }
        }
    }

    // Procesar actualización de estado (solo comité)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_solicitud']) && in_array($_SESSION['rol'], ['presidente', 'secretario', 'vocal'])) {
        $solicitud_id = $_POST['solicitud_id'];
        $nuevo_estado = $_POST['nuevo_estado'];
        $comentario = trim($_POST['comentario_comite']);

        try {
            $query = "UPDATE solicitudes_servicios 
                     SET estado = ?, comentario_comite = ?, fecha_actualizacion = NOW() 
                     WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$nuevo_estado, $comentario, $solicitud_id]);
            
            $message = "Solicitud actualizada correctamente";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error al actualizar la solicitud: " . $e->getMessage();
            $messageType = 'error';
        }
    }

    // Filtros
    $filtro_estado = $_GET['estado'] ?? 'todos';
    $filtro_usuario = $_GET['usuario'] ?? '';

    // Construir consulta con filtros
    $where_conditions = [];
    $params = [];

    if ($filtro_estado != 'todos') {
        $where_conditions[] = "s.estado = ?";
        $params[] = $filtro_estado;
    }

    if ($filtro_usuario && in_array($_SESSION['rol'], ['presidente', 'secretario', 'vocal'])) {
        $where_conditions[] = "s.usuario_id = ?";
        $params[] = $filtro_usuario;
    } elseif ($_SESSION['rol'] == 'inquilino') {
        // Los inquilinos solo ven sus propias solicitudes
        $where_conditions[] = "s.usuario_id = ?";
        $params[] = $_SESSION['usuario_id'];
    }

    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = "WHERE " . implode(' AND ', $where_conditions);
    }

    // Obtener solicitudes
    $query = "SELECT s.*, u.nombre, u.numero_casa, u.rol
              FROM solicitudes_servicios s 
              JOIN usuarios u ON s.usuario_id = u.id
              $where_clause
              ORDER BY s.fecha_solicitud DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener usuarios para filtro (solo para el comité)
    $usuarios_disponibles = [];
    if (in_array($_SESSION['rol'], ['presidente', 'secretario', 'vocal'])) {
        $query = "SELECT id, nombre, numero_casa FROM usuarios WHERE rol = 'inquilino' ORDER BY numero_casa";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $usuarios_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    ?>

    <div class="header">
        <h1>🔧 Solicitudes de Servicio</h1>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <?php if ($_SESSION['rol'] == 'inquilino'): ?>
            <a href="mis-pagos.php">Mis Pagos</a>
            <a href="reservaciones.php">Reservaciones</a>
            <?php else: ?>
            <a href="pagos.php">Pagos</a>
            <a href="egresos.php">Egresos</a>
            <a href="reportes.php">Reportes</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <div class="page-title">
            <h2>Foro de Solicitudes y Servicios</h2>
            <p>Comunícate con el comité para reportar problemas o solicitar servicios</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Formulario de Nueva Solicitud -->
        <div class="new-request-form">
            <h3 style="margin-bottom: 1.5rem;">📝 Nueva Solicitud de Servicio</h3>
            
            <form method="POST">
                <div class="form-group">
                    <label for="titulo">Título de la Solicitud *</label>
                    <input type="text" id="titulo" name="titulo" 
                           placeholder="Ej: Problema con el drenaje común, Solicitud de poda de árboles..."
                           value="<?php echo $_POST['titulo'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción Detallada *</label>
                    <textarea id="descripcion" name="descripcion" rows="4" 
                              placeholder="Describe detalladamente el problema o servicio que solicitas..."
                              required><?php echo $_POST['descripcion'] ?? ''; ?></textarea>
                </div>
                
                <button type="submit" name="crear_solicitud" class="btn btn-primary">
                    Enviar Solicitud
                </button>
            </form>
        </div>

        <!-- Filtros -->
        <div class="filters">
            <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; width: 100%;">
                <div class="filter-group">
                    <label for="estado">Estado</label>
                    <select name="estado" id="estado">
                        <option value="todos" <?php echo $filtro_estado == 'todos' ? 'selected' : ''; ?>>Todos</option>
                        <option value="pendiente" <?php echo $filtro_estado == 'pendiente' ? 'selected' : ''; ?>>Pendientes</option>
                        <option value="en_proceso" <?php echo $filtro_estado == 'en_proceso' ? 'selected' : ''; ?>>En Proceso</option>
                        <option value="completado" <?php echo $filtro_estado == 'completado' ? 'selected' : ''; ?>>Completadas</option>
                        <option value="rechazado" <?php echo $filtro_estado == 'rechazado' ? 'selected' : ''; ?>>Rechazadas</option>
                    </select>
                </div>
                
                <?php if (in_array($_SESSION['rol'], ['presidente', 'secretario', 'vocal'])): ?>
                <div class="filter-group">
                    <label for="usuario">Usuario</label>
                    <select name="usuario" id="usuario">
                        <option value="">Todos los usuarios</option>
                        <?php foreach ($usuarios_disponibles as $usuario): ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="filter-group" style="display: flex; align-items: end;">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>

        <!-- Lista de Solicitudes (Estilo Foro) -->
        <div class="requests-forum">
            <?php if (empty($solicitudes)): ?>
            <div class="request-card">
                <div style="text-align: center; padding: 2rem;">
                    <h3>No hay solicitudes</h3>
                    <p>No se encontraron solicitudes con los filtros seleccionados</p>
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($solicitudes as $solicitud): ?>
            <div class="request-card <?php echo $solicitud['estado']; ?>">
                <div class="request-header">
                    <div class="request-title">
                        <h3><?php echo htmlspecialchars($solicitud['titulo']); ?></h3>
                        <div class="request-meta">
                            <span>👤 Casa #<?php echo $solicitud['numero_casa']; ?> - <?php echo htmlspecialchars($solicitud['nombre']); ?></span>
                            <span style="margin-left: 1rem;">📅 <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?></span>
                            <?php if ($solicitud['fecha_actualizacion']): ?>
                            <span style="margin-left: 1rem;">🔄 Actualizado: <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_actualizacion'])); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="request-status status-<?php echo $solicitud['estado']; ?>">
                        <?php
                        $estados = [
                            'pendiente' => '⏳ Pendiente',
                            'en_proceso' => '🔄 En Proceso',
                            'completado' => '✅ Completado',
                            'rechazado' => '❌ Rechazado'
                        ];
                        echo $estados[$solicitud['estado']];
                        ?>
                    </div>
                </div>
                
                <div class="request-description">
                    <?php echo nl2br(htmlspecialchars($solicitud['descripcion'])); ?>
                </div>

                <?php if ($solicitud['comentario_comite']): ?>
                <div class="request-comment">
                    <div class="comment-header">💬 Respuesta del Comité:</div>
                    <div><?php echo nl2br(htmlspecialchars($solicitud['comentario_comite'])); ?></div>
                </div>
                <?php endif; ?>

                <!-- Acciones del Comité -->
                <?php if (in_array($_SESSION['rol'], ['presidente', 'secretario', 'vocal'])): ?>
                <div class="admin-actions">
                    <strong>🛠️ Acciones del Comité:</strong>
                    <div class="request-actions">
                        <button type="button" class="btn btn-warning btn-sm" 
                                onclick="toggleUpdateForm(<?php echo $solicitud['id']; ?>)">
                            Actualizar Estado
                        </button>
                    </div>
                    
                    <div id="update-form-<?php echo $solicitud['id']; ?>" class="update-form">
                        <form method="POST">
                            <input type="hidden" name="solicitud_id" value="<?php echo $solicitud['id']; ?>">
                            
                            <div class="form-group">
                                <label for="nuevo_estado_<?php echo $solicitud['id']; ?>">Nuevo Estado</label>
                                <select name="nuevo_estado" id="nuevo_estado_<?php echo $solicitud['id']; ?>" required>
                                    <option value="pendiente" <?php echo $solicitud['estado'] == 'pendiente' ? 'selected' : ''; ?>>⏳ Pendiente</option>
                                    <option value="en_proceso" <?php echo $solicitud['estado'] == 'en_proceso' ? 'selected' : ''; ?>>🔄 En Proceso</option>
                                    <option value="completado" <?php echo $solicitud['estado'] == 'completado' ? 'selected' : ''; ?>>✅ Completado</option>
                                    <option value="rechazado" <?php echo $solicitud['estado'] == 'rechazado' ? 'selected' : ''; ?>>❌ Rechazado</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="comentario_<?php echo $solicitud['id']; ?>">Comentario del Comité</label>
                                <textarea name="comentario_comite" id="comentario_<?php echo $solicitud['id']; ?>" 
                                          rows="3" placeholder="Respuesta o actualización para el inquilino..."><?php echo htmlspecialchars($solicitud['comentario_comite']); ?></textarea>
                            </div>
                            
                            <div style="display: flex; gap: 0.5rem;">
                                <button type="submit" name="actualizar_solicitud" class="btn btn-success btn-sm">
                                    Guardar Cambios
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" 
                                        onclick="toggleUpdateForm(<?php echo $solicitud['id']; ?>)">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Información sobre el Foro -->
        <div class="alert alert-info" style="margin-top: 2rem;">
            <h4>📢 Información del Foro</h4>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                <li>Todas las solicitudes son visibles para todos los residentes</li>
                <li>El comité responderá a las solicitudes en orden de llegada</li>
                <li>Recibirás actualizaciones sobre el estado de tus solicitudes</li>
                <li>Utiliza títulos descriptivos para facilitar la organización</li>
                <li>Para emergencias, contacta directamente al comité</li>
            </ul>
        </div>
    </div>

    <script>
        // Auto-submit form cuando cambian los filtros
        document.getElementById('estado').addEventListener('change', function() {
            this.form.submit();
        });
        
        <?php if (in_array($_SESSION['rol'], ['presidente', 'secretario', 'vocal'])): ?>
        document.getElementById('usuario').addEventListener('change', function() {
            this.form.submit();
        });
        <?php endif; ?>

        // Toggle update form
        function toggleUpdateForm(solicitudId) {
            const form = document.getElementById('update-form-' + solicitudId);
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }

        // Confirmar acciones importantes
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                const nuevoEstado = form.querySelector('select[name="nuevo_estado"]');
                if (nuevoEstado && (nuevoEstado.value === 'completado' || nuevoEstado.value === 'rechazado')) {
                    const confirmMsg = nuevoEstado.value === 'completado' ? 
                        '¿Confirmar que la solicitud está completada?' : 
                        '¿Confirmar que desea rechazar la solicitud?';
                    
                    if (!confirm(confirmMsg)) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
</body>
</html>