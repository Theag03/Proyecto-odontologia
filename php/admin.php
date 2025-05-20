<?php
require_once 'auth.php';
verificarRol(['admin']); // Solo accesible para administradores

require_once 'conexion.php';

// Obtener estadísticas
try {
    // Total pacientes
    $stmtPacientes = $conexion->query("SELECT COUNT(*) as total FROM pacientes WHERE activo = 1");
    $totalPacientes = $stmtPacientes->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Citas hoy
    $hoy = date('Y-m-d');
    $stmtCitasHoy = $conexion->prepare("SELECT COUNT(*) as total FROM citas WHERE fecha = :hoy AND estado != 'cancelada'");
    $stmtCitasHoy->bindParam(':hoy', $hoy);
    $stmtCitasHoy->execute();
    $totalCitasHoy = $stmtCitasHoy->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Ingresos del mes
    $mesActual = date('Y-m');
    $stmtIngresos = $conexion->prepare("
        SELECT SUM(t.precio) as total 
        FROM citas c
        JOIN tratamientos t ON c.tratamiento_id = t.id
        WHERE c.fecha LIKE :mes AND c.estado = 'completada'
    ");
    $stmtIngresos->bindValue(':mes', "$mesActual%");
    $stmtIngresos->execute();
    $totalIngresos = $stmtIngresos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total tratamientos
    $stmtTratamientos = $conexion->query("SELECT COUNT(*) as total FROM tratamientos WHERE activo = 1");
    $totalTratamientos = $stmtTratamientos->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Próximas citas
    $stmtProxCitas = $conexion->prepare("
        SELECT c.id, c.fecha, c.hora, p.nombre as paciente_nombre, p.apellido as paciente_apellido, 
               d.nombre as dentista_nombre, t.nombre as tratamiento
        FROM citas c
        JOIN pacientes p ON c.paciente_id = p.id
        JOIN dentistas d ON c.dentista_id = d.id
        JOIN tratamientos t ON c.tratamiento_id = t.id
        WHERE c.fecha >= :hoy AND c.estado = 'confirmada'
        ORDER BY c.fecha, c.hora ASC
        LIMIT 5
    ");
    $stmtProxCitas->bindParam(':hoy', $hoy);
    $stmtProxCitas->execute();
    $proxCitas = $stmtProxCitas->fetchAll(PDO::FETCH_ASSOC);
    
    // Estadísticas de tratamientos
    $stmtStatsTratamientos = $conexion->query("
        SELECT t.nombre, COUNT(c.id) as cantidad
        FROM tratamientos t
        LEFT JOIN citas c ON t.id = c.tratamiento_id AND c.estado = 'completada'
        WHERE t.activo = 1
        GROUP BY t.id
        ORDER BY cantidad DESC
        LIMIT 5
    ");
    $statsTratamientos = $stmtStatsTratamientos->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Error al obtener datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - AgendaDental</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .admin-navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .admin-navbar .navbar-brand img {
            height: 50px;
        }
        
        .admin-navbar .nav-link {
            color: white !important;
            font-weight: 500;
        }
        
        .admin-navbar .nav-link.active {
            color: var(--secondary-color) !important;
        }
        
        .sidebar {
            background-color: white;
            min-height: calc(100vh - 56px);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
        }
        
        .sidebar .nav-link {
            color: var(--dark-color);
            border-left: 3px solid transparent;
            padding: 10px 15px;
            margin: 5px 0;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(52, 152, 219, 0.1);
            border-left: 3px solid var(--secondary-color);
            color: var(--secondary-color);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }
        
        .main-content {
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .card-admin {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .card-admin:hover {
            transform: translateY(-5px);
        }
        
        .card-admin .card-body {
            padding: 20px;
        }
        
        .card-admin .card-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--secondary-color);
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
        }
        
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .badge-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-canceled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- Navbar de administrador -->
    <nav class="navbar navbar-expand-lg navbar-dark admin-navbar">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="IMG/Gemini_Generated_Image_lqwt86lqwt86lqwt.png" alt="Logo AgendaDental" height="60">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-bell me-1"></i> Notificaciones</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="https://via.placeholder.com/30" alt="User" class="rounded-circle me-1"> <?php echo $_SESSION['nombre_completo']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Perfil</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Configuración</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="admin.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="citas.php">
                                <i class="fas fa-calendar-alt"></i> Citas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pacientes.php">
                                <i class="fas fa-users"></i> Pacientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="dentistas.php">
                                <i class="fas fa-user-md"></i> Dentistas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="tratamientos.php">
                                <i class="fas fa-procedures"></i> Tratamientos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="facturacion.php">
                                <i class="fas fa-file-invoice-dollar"></i> Facturación
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reportes.php">
                                <i class="fas fa-chart-line"></i> Reportes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="configuracion.php">
                                <i class="fas fa-cog"></i> Configuración
                            </a>
                        </li>
                    </ul>
                    
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Administración</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="basedatos.php">
                                <i class="fas fa-database"></i> Base de Datos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="usuarios.php">
                                <i class="fas fa-users-cog"></i> Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="permisos.php">
                                <i class="fas fa-shield-alt"></i> Permisos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="backup.php">
                                <i class="fas fa-backup"></i> Backup
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Contenido principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom admin-header">
                    <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i> Panel de Administración</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Exportar</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Imprimir</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary">
                            <i class="fas fa-sync-alt me-1"></i> Actualizar
                        </button>
                    </div>
                </div>

                <!-- Estadísticas rápidas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card card-admin">
                            <div class="card-body text-center">
                                <i class="fas fa-users card-icon"></i>
                                <h5 class="card-title">Pacientes</h5>
                                <h3 class="mb-0"><?php echo number_format($totalPacientes); ?></h3>
                                <p class="text-muted mb-0">Registrados en el sistema</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-admin">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-check card-icon"></i>
                                <h5 class="card-title">Citas Hoy</h5>
                                <h3 class="mb-0"><?php echo number_format($totalCitasHoy); ?></h3>
                                <p class="text-muted mb-0">Programadas para hoy</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-admin">
                            <div class="card-body text-center">
                                <i class="fas fa-dollar-sign card-icon"></i>
                                <h5 class="card-title">Ingresos</h5>
                                <h3 class="mb-0">$<?php echo number_format($totalIngresos, 2); ?></h3>
                                <p class="text-muted mb-0">Este mes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-admin">
                            <div class="card-body text-center">
                                <i class="fas fa-procedures card-icon"></i>
                                <h5 class="card-title">Tratamientos</h5>
                                <h3 class="mb-0"><?php echo number_format($totalTratamientos); ?></h3>
                                <p class="text-muted mb-0">Disponibles</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Secciones de información -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Próximas Citas</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Paciente</th>
                                                <th>Tratamiento</th>
                                                <th>Dentista</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($proxCitas as $cita): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($cita['fecha'])); ?></td>
                                                <td><?php echo $cita['paciente_nombre'] . ' ' . $cita['paciente_apellido']; ?></td>
                                                <td><?php echo $cita['tratamiento']; ?></td>
                                                <td>Dr. <?php echo $cita['dentista_nombre']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Tratamientos Más Solicitados</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="treatmentChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <span class="text-muted">© <?php echo date('Y'); ?> AgendaDental - Panel de Administración</span>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted">Versión 2.1.0 | Último acceso: <?php echo date('d/m/Y H:i'); ?></span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Gráfico de tratamientos más solicitados
        const ctx = document.getElementById('treatmentChart').getContext('2d');
        const treatmentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function($t) { return "'" . addslashes($t['nombre']) . "'"; }, $statsTratamientos)); ?>],
                datasets: [{
                    label: 'Veces solicitado',
                    data: [<?php echo implode(',', array_column($statsTratamientos, 'cantidad')); ?>],
                    backgroundColor: [
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(241, 196, 15, 0.7)',
                        'rgba(231, 76, 60, 0.7)',
                        'rgba(155, 89, 182, 0.7)'
                    ],
                    borderColor: [
                        'rgba(52, 152, 219, 1)',
                        'rgba(46, 204, 113, 1)',
                        'rgba(241, 196, 15, 1)',
                        'rgba(231, 76, 60, 1)',
                        'rgba(155, 89, 182, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>