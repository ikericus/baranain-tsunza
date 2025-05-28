<?php
session_start();

// Configuración de seguridad
$PASSWORD = "BT2025_Admin"; // Cambia esta contraseña por una más segura

// Verificar autenticación
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === $PASSWORD) {
            $_SESSION['authenticated'] = true;
        } else {
            $error_message = "Contraseña incorrecta";
        }
    }
    
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        // Mostrar formulario de login
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Acceso - Recogida Inscripciones BT2025</title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                
                .login-container {
                    background: white;
                    padding: 40px;
                    border-radius: 15px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                    width: 100%;
                    max-width: 400px;
                    text-align: center;
                }
                
                .login-header {
                    margin-bottom: 30px;
                }
                
                .login-header h1 {
                    color: #2c3e50;
                    font-size: 1.8rem;
                    margin-bottom: 10px;
                }
                
                .login-header p {
                    color: #7f8c8d;
                    font-size: 0.9rem;
                }
                
                .form-group {
                    margin-bottom: 25px;
                    text-align: left;
                }
                
                .form-label {
                    display: block;
                    margin-bottom: 8px;
                    color: #2c3e50;
                    font-weight: 500;
                }
                
                .form-input {
                    width: 100%;
                    padding: 15px;
                    border: 2px solid #bdc3c7;
                    border-radius: 8px;
                    font-size: 16px;
                    transition: border-color 0.3s ease;
                }
                
                .form-input:focus {
                    outline: none;
                    border-color: #3498db;
                    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
                }
                
                .login-btn {
                    width: 100%;
                    padding: 15px;
                    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
                    color: white;
                    border: none;
                    border-radius: 8px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                }
                
                .login-btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
                }
                
                .error-message {
                    background: #e74c3c;
                    color: white;
                    padding: 12px;
                    border-radius: 6px;
                    margin-bottom: 20px;
                    font-size: 14px;
                }
                
                .security-info {
                    margin-top: 20px;
                    padding: 15px;
                    background: #f8f9fa;
                    border-radius: 6px;
                    font-size: 12px;
                    color: #7f8c8d;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <div class="login-header">
                    <h1>🔐 Acceso Restringido</h1>
                    <p>Recogida de Inscripciones BT2025</p>
                </div>
                
                <?php if (isset($error_message)): ?>
                    <div class="error-message">
                        ⚠️ <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="password">Contraseña de acceso:</label>
                        <input type="password" id="password" name="password" class="form-input" 
                               placeholder="Introduce la contraseña" required autofocus>
                    </div>
                    
                    <button type="submit" class="login-btn">
                        🚀 Acceder al Sistema
                    </button>
                </form>
                
                <div class="security-info">
                    🛡️ Sistema protegido para personal autorizado
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Logout si se solicita
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recogida por Inscripciones - BT2025</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container { 
            max-width: 1600px; 
            margin: 0 auto; 
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header { 
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white; 
            padding: 30px; 
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,255,255,0.05) 10px,
                rgba(255,255,255,0.05) 20px
            );
            animation: slide 20s linear infinite;
        }
        
        @keyframes slide {
            0% { transform: translateX(-50px); }
            100% { transform: translateX(50px); }
        }
        
        .header h1 { 
            font-size: 2.5rem; 
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .header p {
            position: relative;
            z-index: 1;
            opacity: 0.9;
        }
        
        .header-actions {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 2;
        }
        
        .logout-btn {
            background: rgba(231, 76, 60, 0.8);
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(231, 76, 60, 1);
            transform: translateY(-1px);
        }
        
        .stats { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            padding: 30px;
            background: #f8f9fa;
        }
        
        .stat-card { 
            background: white;
            padding: 25px; 
            border-radius: 12px; 
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid #3498db;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .stat-number { 
            font-size: 2.5rem; 
            font-weight: bold; 
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label { 
            color: #7f8c8d; 
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .search-section { 
            padding: 30px;
            background: white;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .search-container {
            display: flex;
            gap: 15px;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .search-input { 
            font-size: 18px; 
            padding: 15px 20px; 
            border: 2px solid #bdc3c7; 
            border-radius: 10px; 
            width: 300px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-btn { 
            padding: 12px 20px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .filter-btn.active { 
            background: #3498db; 
            color: white;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }
        
        .filter-btn:not(.active) { 
            background: #ecf0f1; 
            color: #2c3e50;
        }
        
        .filter-btn:hover:not(.active) {
            background: #d5dbdb;
        }
        
        .inscripciones-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(500px, 1fr)); 
            gap: 25px; 
            padding: 30px;
            background: #f8f9fa;
        }
        
        .inscripcion-card { 
            background: white;
            border-radius: 15px; 
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 6px solid #95a5a6;
            position: relative;
        }
        
        .inscripcion-card.recogido {
            border-left-color: #27ae60;
            background: linear-gradient(135deg, #ffffff 0%, #f0fff4 100%);
        }
        
        .inscripcion-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .inscripcion-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .inscripcion-info {
            flex: 1;
        }
        
        .inscripcion-numero { 
            font-size: 1.8rem; 
            font-weight: bold; 
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .inscripcion-contacto {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .status-badge {
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-badge.recogido {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }
        
        .status-badge.pendiente {
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(230, 126, 34, 0.3);
        }
        
        .corredores-section {
            margin: 20px 0;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .corredor-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }
        
        .corredor-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .corredor-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .corredor-nombre {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1rem;
        }
        
        .corredor-detalles {
            color: #7f8c8d;
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        .dorsal-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            color: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        .dorsal-10k { 
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            border-left-color: #27ae60;
        }
        
        .dorsal-5k { 
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border-left-color: #e74c3c;
        }
        
        .dorsal-2k { 
            background: linear-gradient(135deg, #f1c40f 0%, #f39c12 100%);
            border-left-color: #f1c40f;
        }
        
        .dorsal-txiki { 
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border-left-color: #3498db;
        }
        
        .dorsal-sin-carrera { 
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            border-left-color: #95a5a6;
        }
        
        .camisetas-section {
            background: #f1f3f4;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 4px solid #9b59b6;
        }
        
        .camisetas-info {
            color: #2c3e50;
            font-weight: 500;
        }
        
        .inscripcion-actions {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
        }
        
        .toggle-btn { 
            width: 100%;
            padding: 15px 25px; 
            border: none; 
            border-radius: 10px; 
            cursor: pointer; 
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .toggle-btn.recogido { 
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.3);
        }
        
        .toggle-btn.pendiente { 
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(230, 126, 34, 0.3);
        }
        
        .toggle-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .toggle-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .resumen-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .resumen-text {
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
            font-size: 1.2rem;
        }
        
        .loading {
            text-align: center;
            padding: 60px;
            font-size: 1.2rem;
            color: #7f8c8d;
        }
        
        .spinner {
            border: 4px solid #ecf0f1;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #27ae60;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transform: translateX(400px);
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.error {
            background: #e74c3c;
        }
        
        .toast.info {
            background: #3498db;
        }
        
        .nav-links {
            padding: 20px 30px;
            background: #ecf0f1;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .nav-btn {
            background: #2c3e50;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .nav-btn:hover {
            background: #34495e;
            transform: translateY(-2px);
        }
        
        .refresh-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .refresh-btn {
            background: #27ae60;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            background: #2ecc71;
            transform: translateY(-2px);
        }
        
        .refresh-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .last-update {
            color: #7f8c8d;
            font-size: 12px;
        }
        
        @media (max-width: 768px) {
            .stats { grid-template-columns: repeat(2, 1fr); }
            .inscripciones-grid { 
                grid-template-columns: 1fr; 
                padding: 20px; 
            }
            .search-container { flex-direction: column; }
            .search-input { width: 100%; }
            .header h1 { font-size: 2rem; }
            .corredor-info { flex-direction: column; align-items: flex-start; }
            .nav-links { flex-direction: column; align-items: stretch; }
            .nav-buttons { justify-content: center; }
            .refresh-section { justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-actions">
                <a href="?logout=1" class="logout-btn" onclick="return confirm('¿Seguro que quieres cerrar sesión?')">
                    🚪 Salir
                </a>
            </div>
            <h1>📋 Recogida por Inscripciones BT2025</h1>
            <p>Gestión de entrega de dorsales y camisetas por inscripción completa</p>
        </div>

        <div class="nav-links">
            <div class="nav-buttons">
                <a href="recogida_dorsales.php" class="nav-btn">👤 Vista por Corredor</a>
                <a href="dorsales.php" class="nav-btn" target="_blank">📋 Lista Completa</a>
            </div>
            
            <div class="refresh-section">
                <button id="refreshBtn" class="refresh-btn" onclick="manualRefresh()">
                    🔄 Actualizar Datos
                </button>
                <div class="last-update">
                    <div id="lastUpdate">Última actualización: --:--</div>
                </div>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number" id="totalInscripciones">0</div>
                <div class="stat-label">Total Inscripciones</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="recogidosCount">0</div>
                <div class="stat-label">Recogidos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="pendientesCount">0</div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="porcentajeRecogida">0%</div>
                <div class="stat-label">% Recogida</div>
            </div>
        </div>

        <div class="search-section">
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="🔍 Buscar por nombre, orden, email o DNI...">
                
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="todos">Todos</button>
                    <button class="filter-btn" data-filter="pendientes">Pendientes</button>
                    <button class="filter-btn" data-filter="recogidos">Recogidos</button>
                </div>
            </div>
        </div>

        <div id="loadingDiv" class="loading">
            <div class="spinner"></div>
            Cargando inscripciones...
        </div>

        <div id="inscripcionesGrid" class="inscripciones-grid" style="display: none;"></div>
        
        <div id="noResults" class="no-results" style="display: none;">
            <h3>No se encontraron inscripciones</h3>
            <p>Intenta ajustar los filtros de búsqueda</p>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        let inscripciones = [];
        let filteredInscripciones = [];
        let currentFilter = 'todos';

        async function loadInscripciones() {
            try {
                const response = await fetch('get_inscripciones.php');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                inscripciones = await response.json();
                
                if (Array.isArray(inscripciones)) {
                    filteredInscripciones = [...inscripciones];
                    updateStats();
                    renderInscripciones();
                    document.getElementById('loadingDiv').style.display = 'none';
                    document.getElementById('inscripcionesGrid').style.display = 'grid';
                    updateLastRefresh();
                } else {
                    throw new Error('Formato de datos inválido');
                }
                
            } catch (error) {
                console.error('Error loading data:', error);
                showToast('Error al cargar los datos: ' + error.message, 'error');
                document.getElementById('loadingDiv').innerHTML = '<p>Error al cargar los datos. Por favor, recarga la página.</p>';
            }
        }

        function updateLastRefresh() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('es-ES', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('lastUpdate').textContent = `Última actualización: ${timeString}`;
        }

        async function manualRefresh() {
            const refreshBtn = document.getElementById('refreshBtn');
            refreshBtn.disabled = true;
            refreshBtn.textContent = '🔄 Actualizando...';
            
            try {
                await loadInscripciones();
                showToast('Datos actualizados correctamente', 'success');
            } catch (error) {
                showToast('Error al actualizar los datos', 'error');
            } finally {
                refreshBtn.disabled = false;
                refreshBtn.textContent = '🔄 Actualizar Datos';
            }
        }

        function updateStats() {
            const total = inscripciones.length;
            const recogidos = inscripciones.filter(i => i.recogido == 1).length;
            const pendientes = total - recogidos;
            const porcentaje = total > 0 ? Math.round((recogidos / total) * 100) : 0;

            document.getElementById('totalInscripciones').textContent = total;
            document.getElementById('recogidosCount').textContent = recogidos;
            document.getElementById('pendientesCount').textContent = pendientes;
            document.getElementById('porcentajeRecogida').textContent = porcentaje + '%';
        }

        function getDorsalClass(dorsalInfo) {
            if (dorsalInfo.includes('10K')) return 'dorsal-10k';
            if (dorsalInfo.includes('5K')) return 'dorsal-5k';
            if (dorsalInfo.includes('2K')) return 'dorsal-2k';
            if (dorsalInfo.includes('TXIKI')) return 'dorsal-txiki';
            return 'dorsal-sin-carrera';
        }

        function formatDorsal(dorsalInfo) {
            if (dorsalInfo.includes('-0')) {
                return dorsalInfo.replace('-0', ' - Sin dorsal');
            }
            return dorsalInfo.replace('-', ' #');
        }

        function renderInscripciones() {
            const grid = document.getElementById('inscripcionesGrid');
            const noResults = document.getElementById('noResults');

            if (filteredInscripciones.length === 0) {
                grid.style.display = 'none';
                noResults.style.display = 'block';
                return;
            }

            noResults.style.display = 'none';
            grid.style.display = 'grid';

            grid.innerHTML = filteredInscripciones.map(inscripcion => `
                <div class="inscripcion-card ${inscripcion.recogido == 1 ? 'recogido' : ''}" data-id="${inscripcion.inscripcion_id}">
                    <div class="inscripcion-header">
                        <div class="inscripcion-info">
                            <div class="inscripcion-numero">Inscripción #${inscripcion.orden}</div>
                            <div class="inscripcion-contacto">
                                📧 ${inscripcion.email}<br>
                                📱 ${inscripcion.telefono}
                            </div>
                        </div>
                        <div class="status-badge ${inscripcion.recogido == 1 ? 'recogido' : 'pendiente'}">
                            ${inscripcion.recogido == 1 ? '✅ Recogida' : '📦 Pendiente'}
                        </div>
                    </div>

                    <div class="resumen-box">
                        <div class="resumen-text">
                            👥 ${inscripcion.total_corredores} corredor${inscripcion.total_corredores !== 1 ? 'es' : ''} | 
                            👕 ${inscripcion.total_camisetas} camiseta${inscripcion.total_camisetas !== 1 ? 's' : ''}
                        </div>
                    </div>

                    <div class="corredores-section">
                        <div class="section-title">
                            🏃‍♂️ Corredores y Dorsales
                        </div>
                        ${inscripcion.corredores.map(corredor => `
                            <div class="corredor-item">
                                <div class="corredor-info">
                                    <div>
                                        <div class="corredor-nombre">
                                            ${corredor.nombre} ${corredor.apellido1} ${corredor.apellido2}
                                        </div>
                                        <div class="corredor-detalles">
                                            DNI: ${corredor.dni} | Edad: ${corredor.edad} años | ${corredor.sexo} | ${corredor.carrera}
                                        </div>
                                    </div>
                                    <div class="dorsal-badge ${getDorsalClass(corredor.dorsal_info)}">
                                        ${formatDorsal(corredor.dorsal_info)}
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>

                    <div class="camisetas-section">
                        <div class="section-title">
                            👕 Camisetas
                        </div>
                        <div class="camisetas-info">
                            ${inscripcion.camisetas}
                        </div>
                    </div>

                    <div class="inscripcion-actions">
                        <button class="toggle-btn ${inscripcion.recogido == 1 ? 'recogido' : 'pendiente'}" 
                                id="btn-${inscripcion.inscripcion_id}"
                                onclick="toggleRecogida(${inscripcion.inscripcion_id})">
                            ${inscripcion.recogido == 1 ? '✅ Inscripción Recogida' : '📦 Marcar como Recogida'}
                        </button>
                    </div>
                </div>
            `).join('');
        }

        async function toggleRecogida(id) {
            const inscripcion = inscripciones.find(i => i.inscripcion_id === id);
            if (!inscripcion) return;

            const newStatus = inscripcion.recogido == 1 ? 0 : 1;
            const action = newStatus == 1 ? 'marcar como recogida' : 'desmarcar como recogida';
            
            const corredoresNames = inscripcion.corredores.map(c => `${c.nombre} ${c.apellido1}`).join(', ');
            
            if (!confirm(`¿Deseas ${action} la inscripción #${inscripcion.orden}?\n\nCorredores: ${corredoresNames}`)) {
                return;
            }

            // Deshabilitar botón mientras se procesa
            const btn = document.getElementById(`btn-${id}`);
            btn.disabled = true;
            btn.textContent = '⏳ Procesando...';

            try {
                const response = await fetch('toggle_recogida.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `id=${id}&recogido=${newStatus}`
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    inscripcion.recogido = newStatus;
                    updateStats();
                    applyFilters();
                    showToast(
                        newStatus == 1 
                            ? `Inscripción #${inscripcion.orden} marcada como recogida` 
                            : `Inscripción #${inscripcion.orden} desmarcada`,
                        newStatus == 1 ? 'success' : 'info'
                    );
                } else {
                    showToast('Error: ' + (result.message || 'Error desconocido'), 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al actualizar el estado: ' + error.message, 'error');
            } finally {
                // Rehabilitar botón
                btn.disabled = false;
            }
        }

        function applyFilters() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            filteredInscripciones = inscripciones.filter(inscripcion => {
                // Filtro de búsqueda
                const matchesSearch = !searchTerm || 
                    inscripcion.orden.toString().includes(searchTerm) ||
                    inscripcion.email.toLowerCase().includes(searchTerm) ||
                    inscripcion.telefono.includes(searchTerm) ||
                    inscripcion.corredores.some(c => 
                        c.nombre.toLowerCase().includes(searchTerm) ||
                        c.apellido1.toLowerCase().includes(searchTerm) ||
                        c.apellido2.toLowerCase().includes(searchTerm) ||
                        c.dni.toLowerCase().includes(searchTerm)
                    );

                // Filtro por estado
                let matchesFilter = true;
                switch (currentFilter) {
                    case 'pendientes':
                        matchesFilter = inscripcion.recogido == 0;
                        break;
                    case 'recogidos':
                        matchesFilter = inscripcion.recogido == 1;
                        break;
                    default:
                        matchesFilter = true;
                }

                return matchesSearch && matchesFilter;
            });

            renderInscripciones();
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type} show`;
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 4000);
        }

        // Event listeners
        document.getElementById('searchInput').addEventListener('input', applyFilters);

        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentFilter = btn.dataset.filter;
                applyFilters();
            });
        });

        // Cargar datos al iniciar (sin auto-refresh)
        loadInscripciones();
    </script>
</body>
</html>