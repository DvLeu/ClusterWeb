<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuestro Equipo - Cluster Admin</title>
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
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
            text-align: center;
        }

        .page-title h1 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }

        .page-title p {
            color: #666;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .member-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .member-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .member-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .member-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            font-weight: bold;
        }

        .member-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .member-role {
            color: #667eea;
            font-weight: 500;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .member-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .member-contact {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f8f9fa;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            color: #666;
        }

        .project-info {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .project-info h2 {
            color: #333;
            margin-bottom: 1rem;
            text-align: center;
        }

        .project-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .detail-item {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }

        .detail-item .icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .detail-item h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .detail-item p {
            color: #666;
            font-size: 0.9rem;
        }

        .technologies {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .technologies h2 {
            color: #333;
            margin-bottom: 2rem;
            text-align: center;
        }

        .tech-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .tech-item {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .team-grid {
                grid-template-columns: 1fr;
            }
            
            .page-title h1 {
                font-size: 2rem;
            }
            
            .member-avatar {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <?php
    session_start();

    // Si no hay sesión, permitir acceso pero con navegación limitada
    $loggedIn = isset($_SESSION['usuario_id']);
    ?>

    <div class="header">
        <h1>👥 Nuestro Equipo</h1>
        <div class="nav-links">
            <?php if ($loggedIn): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Cerrar Sesión</a>
            <?php else: ?>
                <a href="login.php">Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <div class="page-title">
            <h1>🚀 Equipo de Desarrollo</h1>
            <p>Conoce a los desarrolladores que hicieron posible el Sistema de Administración del Cluster</p>
        </div>

        <!-- Equipo de Desarrollo -->
        <div class="team-grid">
            <!-- Miembro 1 -->
            <div class="member-card">
                <div class="member-avatar">
                    👨‍💻
                </div>
                <div class="member-name">David Leon Salas</div>
                <div class="member-role">21020400</div>
                <div class="member-description">
                    Especialista en desarrollo web con PHP y MySQL. Encargado del backend, base de datos y lógica de negocio del sistema.
                </div>
                <div class="member-contact">
                    <div class="contact-item">
                        <span>📧</span>
                        <span>L21020400@veracruz.tecnm.mx</span>
                    </div>
                    <div class="contact-item">
                        <span>🎓</span>
                        <span>Instituto Tecnologico de Veracruz</span>
                    </div>
                </div>
            </div>

            <!-- Miembro 2 -->
            <div class="member-card">
                <div class="member-avatar">
                    👩‍💻
                </div>
                <div class="member-name">Jorge Angel Estudillo Silva</div>
                <div class="member-role">20021039</div>
                <div class="member-description">
                    Experto en diseño de interfaces y experiencia de usuario. Responsable del diseño visual y la usabilidad del sistema.
                </div>
                <div class="member-contact">
                    <div class="contact-item">
                        <span>📧</span>
                        <span>L20021039@veracruz.tecnm.mx</span>
                    </div>
                    <div class="contact-item">
                        <span>🎓</span>
                        <span>Instituto Tecnologico de Veracruz</span>
                    </div>
                </div>
            </div>

            <!-- Miembro 3 -->
            <div class="member-card">
                <div class="member-avatar">
                    👨‍💼
                </div>
                <div class="member-name">Brian Carrion Wirth</div>
                <div class="member-role">Analista de Sistemas</div>
                <div class="member-description">
                    Especialista en análisis de requerimientos y arquitectura de sistemas. Encargado de la documentación y testing.
                </div>
                <div class="member-contact">
                    <div class="contact-item">
                        <span>📧</span>
                        <span>L21020150@veracruz.tecnm.mx</span>
                    </div>
                    <div class="contact-item">
                        <span>🎓</span>
                        <span>Instituto Tecnologico de Veracruz</span>
                    </div>
                </div>
            </div>

            <!-- Miembro 4 -->
            <div class="member-card">
                <div class="member-avatar">
                    👨‍💻
                </div>
                <div class="member-name">JOSÉ VLADIMIR DÍAZ NICOLAS</div>
                <div class="member-role">Desarrollador Frontend</div>
                <div class="member-description">
                    Experto en tecnologías frontend y responsive design. Responsable de la interfaz de usuario y la experiencia móvil.
                </div>
                <div class="member-contact">
                    <div class="contact-item">
                        <span>📧</span>
                        <span>L21020151@veracruz.tecnm.mx</span>
                    </div>
                    <div class="contact-item">
                        <span>🎓</span>
                        <span>Instituto Tecnologico de Veracruz</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Proyecto -->
        <div class="project-info">
            <h2>📋 Información del Proyecto</h2>
            <p style="text-align: center; color: #666; margin-bottom: 2rem;">
                Sistema de Administración para Conjunto de Casas desarrollado como proyecto universitario
            </p>
            
            <div class="project-details">
                <div class="detail-item">
                    <div class="icon">🏫</div>
                    <h3>Universidad</h3>
                    <p>Instituto Tecnologico de Veracruz</p>
                </div>
                <div class="detail-item">
                    <div class="icon">📚</div>
                    <h3>Materia</h3>
                    <p>Programación Web</p>
                </div>
                <div class="detail-item">
                    <div class="icon">👨‍🏫</div>
                    <h3>Profesor</h3>
                    <p>Delio Coss Camilo</p>
                </div>
                <div class="detail-item">
                    <div class="icon">📅</div>
                    <h3>Fecha</h3>
                    <p><?php echo date('Y'); ?> - Semestre Actual</p>
                </div>
            </div>
        </div>

        <!-- Tecnologías Utilizadas -->
        <div class="technologies">
            <h2>🛠️ Tecnologías Utilizadas</h2>
            <div class="tech-grid">
                <div class="tech-item">PHP 7+</div>
                <div class="tech-item">MySQL</div>
                <div class="tech-item">HTML5</div>
                <div class="tech-item">CSS3</div>
                <div class="tech-item">Responsive Design</div>
                <div class="tech-item">Session Management</div>
            </div>
        </div>
    </div>

    <script>
        // Animación de entrada para las tarjetas
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.member-card');
            
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(50px)';
                
                setTimeout(() => {
                    card.style.transition = 'opacity 1s ease, transform 1s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>