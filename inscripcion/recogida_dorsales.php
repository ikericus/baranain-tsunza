<?php
include 'Db.php';
$db = new Db();

// Verificar autenticación si es necesario
// session_start();
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
//     header('Location: login.php');
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recogida de Dorsales - BT2025</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container { 
            max-width: 1400px; 
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
        
        .runners-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); 
            gap: 20px; 
            padding: 30px;
            background: #f8f9fa;
        }
        
        .runner-card { 
            background: white;
            border-radius: 12px; 
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 5px solid #95a5a6;
        }
        
        .runner-card.recogido {
            border-left-color: #27ae60;
            background: linear-gradient(135deg, #ffffff 0%, #f0fff4 100%);
        }
        
        .runner-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .runner-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .runner-name { 
            font-size: 1.2rem; 
            font-weight: bold; 
            color: #2c3e50;
            line-height: 1.3;
        }
        
        .dorsal-number { 
            color: white; 
            padding: 8px 15px; 
            border-radius: 20px; 
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.3);
        }
        
        /* Colores por categoría */
        .dorsal-10k { 
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            box-shadow: 0 3px 10px rgba(39, 174, 96, 0.3);
        }
        
        .dorsal-5k { 
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            box-shadow: 0 3px 10px rgba(231, 76, 60, 0.3);
        }
        
        .dorsal-2k { 
            background: linear-gradient(135deg, #f1c40f 0%, #f39c12 100%);
            box-shadow: 0 3px 10px rgba(241, 196, 15, 0.3);
        }
        
        .dorsal-txiki { 
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
        }
        
        .dorsal-sin-carrera { 
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            box-shadow: 0 3px 10px rgba(149, 165, 166, 0.3);
        }
        
        .runner-details { 
            margin: 15px 0;
            color: #7f8c8d;
            line-height: 1.6;
        }
        
        .runner-details div {
            margin: 5px 0;
        }
        
        .runner-details strong {
            color: #2c3e50;
            font-weight: 600;
        }
        
        .runner-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .toggle-btn { 
            flex: 1;
            padding: 12px 20px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .toggle-btn.recogido { 
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
        }
        
        .toggle-btn.pendiente { 
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(230, 126, 34, 0.3);
        }
        
        .toggle-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        
        .toggle-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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
        
        .info-banner {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 20px 30px;
            margin: 0;
            position: relative;
            overflow: hidden;
        }
        
        .info-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        .info-content {
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            z-index: 1;
        }
        
        .info-icon {
            font-size: 2rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .info-text {
            font-size: 1.1rem;
            line-height: 1.4;
        }
        
        .info-text strong {
            color: #fff;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        
        .export-btn {
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
            margin-bottom: 20px;
        }
        
        .export-btn:hover {
            background: #34495e;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .stats { grid-template-columns: repeat(2, 1fr); }
            .runners-grid { grid-template-columns: 1fr; padding: 20px; }
            .search-container { flex-direction: column; }
            .search-input { width: 100%; }
            .header h1 { font-size: 2rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏃‍♂️ Recogida de Dorsales BT2025</h1>
            <p>Sistema de gestión para entrega de dorsales y camisetas</p>
        </div>

        <div class="info-banner">
            <div class="info-content">
                <div class="info-icon">👶</div>
                <div class="info-text">
                    <strong>IMPORTANTE:</strong> Los niños (Txiki) no tienen asignado nº de dorsal, ir dando dorsales azules de manera incremental hasta que se acaben
                </div>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number" id="totalCorredores">0</div>
                <div class="stat-label">Total Corredores</div>
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
            <a href="dorsales.php" class="export-btn" target="_blank">📋 Ver Lista Completa</a>
            
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="🔍 Buscar por nombre, apellido, dorsal o DNI...">
                
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="todos">Todos</button>
                    <button class="filter-btn" data-filter="pendientes">Pendientes</button>
                    <button class="filter-btn" data-filter="recogidos">Recogidos</button>
                    <button class="filter-btn" data-filter="txiki">Txiki (Azul)</button>
                    <button class="filter-btn" data-filter="2k">2K (Amarillo)</button>
                    <button class="filter-btn" data-filter="5k">5K (Rojo)</button>
                    <button class="filter-btn" data-filter="10k">10K (Verde)</button>
                </div>
            </div>
        </div>

        <div id="loadingDiv" class="loading">
            <div class="spinner"></div>
            Cargando corredores...
        </div>

        <div id="runnersGrid" class="runners-grid" style="display: none;"></div>
        
        <div id="noResults" class="no-results" style="display: none;">
            <h3>No se encontraron corredores</h3>
            <p>Intenta ajustar los filtros de búsqueda</p>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        let corredores = [];
        let filteredCorredores = [];
        let currentFilter = 'todos';

        async function loadCorredores() {
            try {
                const response = await fetch('get_corredores.php');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                corredores = await response.json();
                
                if (Array.isArray(corredores)) {
                    filteredCorredores = [...corredores];
                    updateStats();
                    renderCorredores();
                    document.getElementById('loadingDiv').style.display = 'none';
                    document.getElementById('runnersGrid').style.display = 'grid';
                } else {
                    throw new Error('Formato de datos inválido');
                }
                
            } catch (error) {
                console.error('Error loading data:', error);
                showToast('Error al cargar los datos: ' + error.message, 'error');
                document.getElementById('loadingDiv').innerHTML = '<p>Error al cargar los datos. Por favor, recarga la página.</p>';
            }
        }

        function updateStats() {
            const total = corredores.length;
            const recogidos = corredores.filter(c => c.recogido == 1).length;
            const pendientes = total - recogidos;
            const porcentaje = total > 0 ? Math.round((recogidos / total) * 100) : 0;

            document.getElementById('totalCorredores').textContent = total;
            document.getElementById('recogidosCount').textContent = recogidos;
            document.getElementById('pendientesCount').textContent = pendientes;
            document.getElementById('porcentajeRecogida').textContent = porcentaje + '%';
        }

        function getDorsalInfo(corredor) {
            // Determinar el texto y clase del dorsal según la carrera
            if (corredor.dorsal <= 0) {
                // Casos especiales sin dorsal asignado
                if (corredor.carrera.includes('Txiki') || corredor.carrera.includes('Benjamin') || corredor.carrera.includes('Alevin')) {
                    return { text: 'SIGUIENTE', class: 'dorsal-number dorsal-txiki' };
                } else {
                    return { text: 'SIN DORSAL', class: 'dorsal-number dorsal-sin-carrera' };
                }
            }
            
            // Dorsales asignados con colores por categoría
            if (corredor.carrera.includes('10K')) {
                return { text: `#${corredor.dorsal}`, class: 'dorsal-number dorsal-10k' };
            } else if (corredor.carrera.includes('5K')) {
                return { text: `#${corredor.dorsal}`, class: 'dorsal-number dorsal-5k' };
            } else if (corredor.carrera.includes('2K')) {
                return { text: `#${corredor.dorsal}`, class: 'dorsal-number dorsal-2k' };
            } else if (corredor.carrera.includes('Txiki') || corredor.carrera.includes('Benjamin') || corredor.carrera.includes('Alevin')) {
                return { text: `#${corredor.dorsal}`, class: 'dorsal-number dorsal-txiki' };
            } else {
                return { text: `#${corredor.dorsal}`, class: 'dorsal-number dorsal-sin-carrera' };
            }
        }

        function renderCorredores() {
            const grid = document.getElementById('runnersGrid');
            const noResults = document.getElementById('noResults');
            
            if (filteredCorredores.length === 0) {
                grid.style.display = 'none';
                noResults.style.display = 'block';
                return;
            }

            noResults.style.display = 'none';
            grid.style.display = 'grid';
            
            grid.innerHTML = filteredCorredores.map(corredor => {
                const dorsalInfo = getDorsalInfo(corredor);
                
                return `
                <div class="runner-card ${corredor.recogido == 1 ? 'recogido' : ''}" data-id="${corredor.id}">
                    <div class="runner-header">
                        <div class="runner-name">
                            ${corredor.nombre} ${corredor.apellido1} ${corredor.apellido2}
                        </div>
                        <div class="${dorsalInfo.class}">${dorsalInfo.text}</div>
                    </div>
                    
                    <div class="runner-details">
                        <div><strong>DNI:</strong> ${corredor.dni}</div>
                        <div><strong>Edad:</strong> ${corredor.edad} años</div>
                        <div><strong>Sexo:</strong> ${corredor.sexo}</div>
                        <div><strong>Carrera:</strong> ${corredor.carrera}</div>
                        <div><strong>Camisetas:</strong> ${corredor.camisetas || 'Sin camiseta'}</div>
                        <div><strong>Email:</strong> ${corredor.email}</div>
                        <div><strong>Teléfono:</strong> ${corredor.telefono}</div>
                    </div>
                    
                    <div class="runner-actions">
                        <button class="toggle-btn ${corredor.recogido == 1 ? 'recogido' : 'pendiente'}" 
                                onclick="toggleRecogida(${corredor.id})" id="btn-${corredor.id}">
                            ${corredor.recogido == 1 ? '✅ Recogido' : '📦 Marcar como Recogido'}
                        </button>
                    </div>
                </div>
                `;
            }).join('');
        }

        async function toggleRecogida(id) {
            const corredor = corredores.find(c => c.id === id);
            if (!corredor) return;

            const newStatus = corredor.recogido == 1 ? 0 : 1;
            const action = newStatus == 1 ? 'marcar como recogido' : 'desmarcar como recogido';
            
            if (!confirm(`¿Deseas ${action} a ${corredor.nombre} ${corredor.apellido1}?`)) {
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
                    corredor.recogido = newStatus;
                    updateStats();
                    applyFilters();
                    showToast(
                        newStatus == 1 
                            ? `${corredor.nombre} marcado como recogido` 
                            : `${corredor.nombre} desmarcado`,
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
            
            filteredCorredores = corredores.filter(corredor => {
                // Filtro de búsqueda
                const matchesSearch = !searchTerm || 
                    corredor.nombre.toLowerCase().includes(searchTerm) ||
                    corredor.apellido1.toLowerCase().includes(searchTerm) ||
                    corredor.apellido2.toLowerCase().includes(searchTerm) ||
                    corredor.dni.toLowerCase().includes(searchTerm) ||
                    corredor.dorsal.toString().includes(searchTerm);

                // Filtro por estado/categoría
                let matchesFilter = true;
                switch (currentFilter) {
                    case 'pendientes':
                        matchesFilter = corredor.recogido == 0;
                        break;
                    case 'recogidos':
                        matchesFilter = corredor.recogido == 1;
                        break;
                    case 'txiki':
                        matchesFilter = corredor.carrera.includes('Txiki') || corredor.carrera.includes('Benjamin') || corredor.carrera.includes('Alevin');
                        break;
                    case '10k':
                        matchesFilter = corredor.carrera.includes('10K');
                        break;
                    case '5k':
                        matchesFilter = corredor.carrera.includes('5K');
                        break;
                    case '2k':
                        matchesFilter = corredor.carrera.includes('2K');
                        break;
                    default:
                        matchesFilter = true;
                }

                return matchesSearch && matchesFilter;
            });

            renderCorredores();
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

        // Auto-refresh cada 30 segundos para sincronizar con otros usuarios
        setInterval(loadCorredores, 30000);

        // Cargar datos al iniciar
        loadCorredores();
    </script>
</body>
</html>