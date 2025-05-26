<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corrector de Headers - Cluster Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; background: #d4edda; padding: 1rem; border-radius: 5px; margin: 1rem 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 1rem; border-radius: 5px; margin: 1rem 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 1rem; border-radius: 5px; margin: 1rem 0; }
        .btn { 
            display: inline-block; 
            padding: 0.75rem 1.5rem; 
            background: #007bff; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
            margin: 0.5rem 0;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Corrector de Headers PHP</h1>
        
        <?php if (isset($_POST['fix_headers'])): ?>
            
            <h2>Aplicando Correcciones...</h2>
            
            <?php
            $files_to_fix = [
                'mis-pagos.php',
                'egresos.php', 
                'reportes.php',
                'reservaciones.php',
                'solicitudes.php',
                'equipo.php'
            ];
            
            foreach ($files_to_fix as $file) {
                if (file_exists($file)) {
                    echo "<div class='info'>📄 Procesando: $file</div>";
                    
                    // Leer contenido del archivo
                    $content = file_get_contents($file);
                    
                    // Verificar si ya tiene session_start al inicio
                    if (strpos(trim($content), '<?php') === 0) {
                        // Buscar si session_start está después de HTML
                        if (preg_match('/<!DOCTYPE.*?session_start\(\)/s', $content)) {
                            echo "<div class='error'>⚠️ $file necesita corrección manual (session_start después de HTML)</div>";
                        } else {
                            echo "<div class='success'>✅ $file parece estar correcto</div>";
                        }
                    } else {
                        echo "<div class='error'>❌ $file no inicia con PHP correctamente</div>";
                    }
                } else {
                    echo "<div class='error'>❌ Archivo $file no encontrado</div>";
                }
            }
            ?>
            
            <div class="info">
                <h3>📋 Instrucciones para corrección manual:</h3>
                <ol>
                    <li>Cada archivo PHP debe iniciar exactamente con: <code>&lt;?php</code> (sin espacios antes)</li>
                    <li>Inmediatamente después: <code>session_start();</code></li>
                    <li>Todo el código PHP de procesamiento debe ir antes del HTML</li>
                    <li>El HTML debe iniciar con: <code>&gt;!DOCTYPE html&gt;</code></li>
                </ol>
            </div>
            
        <?php endif; ?>
        
        <div class="info">
            <h3>🚨 Estado Actual de Archivos:</h3>
            <p><strong>✅ Corregidos:</strong></p>
            <ul>
                <li>✅ login.php</li>
                <li>✅ dashboard.php</li>
                <li>✅ pagos.php</li>
            </ul>
            
            <p><strong>⚠️ Pendientes de revisar:</strong></p>
            <ul>
                <li>mis-pagos.php</li>
                <li>egresos.php</li>
                <li>reportes.php</li>
                <li>reservaciones.php</li>
                <li>solicitudes.php</li>
                <li>equipo.php</li>
            </ul>
        </div>
        
        <h2>🛠️ Plantilla Correcta</h2>
        <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin: 1rem 0;">
            <pre><code>&lt;?php
session_start();
require_once 'config/database.php';

// Todo el código PHP aquí...

?&gt;&lt;!DOCTYPE html&gt;
&lt;html lang="es"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;Título&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;!-- HTML aquí --&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
        </div>
        
        <form method="POST">
            <button type="submit" name="fix_headers" class="btn">Analizar Archivos</button>
        </form>
        
        <p><a href="dashboard.php" class="btn" style="background: #28a745;">🔙 Volver al Dashboard</a></p>
    </div>
</body>
</html>