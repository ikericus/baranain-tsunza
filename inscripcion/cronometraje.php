<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cronometraje BT2025</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 10px; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { background: #2c3e50; color: white; padding: 25px; margin-bottom: 20px; text-align: center; border-radius: 8px; }
        .timer { font-size: 4em; font-weight: bold; color: #e74c3c; margin: 10px 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
        .race-status { font-size: 1.2em; margin: 10px 0; }
        .controls { margin: 20px 0; text-align: center; }
        .controls button { padding: 12px 25px; margin: 5px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-start { background: #27ae60; color: white; }
        .btn-reset { background: #e74c3c; color: white; }
        .btn-refresh { background: #3498db; color: white; }
        .input-section { background: #ecf0f1; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center; }
        .input-section h3 { margin: 0 0 15px 0; color: #2c3e50; }
        .dorsal-input { font-size: 24px; padding: 12px; width: 120px; margin-right: 15px; border: 2px solid #bdc3c7; border-radius: 5px; text-align: center; }
        .add-btn { font-size: 20px; padding: 12px 25px; background: #27ae60; color: white; border: none; cursor: pointer; border-radius: 5px; }
        .last-added { display: block; margin-top: 15px; padding: 10px; background: #d5edda; color: #155724; border-radius: 5px; font-weight: bold; font-size: 18px; }
        .results { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 30px; }
        .category { border: 2px solid #bdc3c7; padding: 15px; background: white; border-radius: 8px; }
        .category h3 { margin: 0 0 15px 0; background: #34495e; color: white; padding: 10px; text-align: center; border-radius: 5px; font-size: 16px; }
        .time-list { height: 400px; overflow-y: auto; border: 1px solid #ecf0f1; }
        .time-entry { display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; border-bottom: 1px solid #ecf0f1; font-size: 14px; }
        .time-entry:hover { background: #f8f9fa; }
        .time-entry:nth-child(-n+3) { background: #fff3cd; font-weight: bold; }
        .position { font-weight: bold; color: #e74c3c; min-width: 25px; }
        .runner-info { flex: 1; margin: 0 10px; }
        .time-display { font-weight: bold; color: #2c3e50; min-width: 70px; }
        .edit-time { width: 70px; padding: 3px; border: 1px solid #bdc3c7; border-radius: 3px; font-size: 12px; }
        .delete-btn { background: #e74c3c; color: white; border: none; padding: 4px 8px; cursor: pointer; border-radius: 3px; font-size: 11px; }
        .manual-add { margin-top: 20px; padding: 20px; background: #ffeaa7; border-radius: 8px; }
        .manual-add h3 { margin: 0 0 15px 0; color: #2c3e50; }
        .manual-inputs { display: flex; justify-content: center; align-items: center; gap: 10px; }
        .manual-inputs input { padding: 8px; border: 1px solid #bdc3c7; border-radius: 5px; }
        .manual-inputs button { padding: 8px 15px; background: #f39c12; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0; }
        .stat-card { background: white; padding: 15px; border-radius: 8px; text-align: center; border: 2px solid #bdc3c7; }
        .stat-number { font-size: 2em; font-weight: bold; color: #e74c3c; }
        .stat-label { color: #7f8c8d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cronometraje BT2025</h1>
            <div class="timer" id="timer">00:00:00</div>
            <div class="race-status" id="raceStatus">Carrera no iniciada</div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number" id="totalRunners">0</div>
                <div class="stat-label">Corredores finalizados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="lastDorsal">-</div>
                <div class="stat-label">Último dorsal</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="lastTime">-</div>
                <div class="stat-label">Último tiempo</div>
            </div>
        </div>

        <div class="controls">
            <button id="startBtn" class="btn-start" onclick="startRace()">Iniciar Carrera</button>
            <button id="resetBtn" class="btn-reset" onclick="resetRace()">Reset Carrera</button>
            <button class="btn-refresh" onclick="refreshResults()">Actualizar Resultados</button>
        </div>

        <div class="input-section">
            <h3>Registrar Dorsal</h3>
            <input type="number" id="dorsalInput" class="dorsal-input" placeholder="Dorsal" min="1">
            <button class="add-btn" onclick="addTime()">Añadir Tiempo</button>
            <div id="lastAdded" class="last-added" style="display: none;"></div>
        </div>

        <div class="manual-add">
            <h3>Añadir Tiempo Manual</h3>
            <div class="manual-inputs">
                <input type="number" id="manualDorsal" placeholder="Dorsal" style="width: 80px;">
                <input type="text" id="manualTime" placeholder="HH:MM:SS" style="width: 90px;">
                <button onclick="addManualTime()">Añadir Manual</button>
            </div>
        </div>

        <div class="results" id="results">
            <div class="category">
                <h3>10K Hombres</h3>
                <div class="time-list" id="results_10k_h"></div>
            </div>
            <div class="category">
                <h3>10K Mujeres</h3>
                <div class="time-list" id="results_10k_m"></div>
            </div>
            <div class="category">
                <h3>5K Hombres</h3>
                <div class="time-list" id="results_5k_h"></div>
            </div>
            <div class="category">
                <h3>5K Mujeres</h3>
                <div class="time-list" id="results_5k_m"></div>
            </div>
            <div class="category">
                <h3>2K Hombres</h3>
                <div class="time-list" id="results_2k_h"></div>
            </div>
            <div class="category">
                <h3>2K Mujeres</h3>
                <div class="time-list" id="results_2k_m"></div>
            </div>
        </div>
    </div>

    <script>
        let startTime = null;
        let timerInterval = null;
        let raceStarted = false;

        // Simulación de datos de corredores (en implementación real vendría de la base de datos)
        const runners = {};

        // Cargar datos iniciales
        loadRaceStatus();
        refreshResults();

        // Timer
        function updateTimer() {
            if (!raceStarted || !startTime) {
                document.getElementById('timer').textContent = '00:00:00';
                return;
            }
            
            const now = Date.now();
            const elapsed = Math.floor((now - startTime) / 1000);
            
            const hours = Math.floor(elapsed / 3600);
            const minutes = Math.floor((elapsed % 3600) / 60);
            const seconds = elapsed % 60;
            
            document.getElementById('timer').textContent = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        function startRace() {
            if (confirm('¿Iniciar la carrera? Esto borrará todos los tiempos anteriores.')) {
                raceStarted = true;
                startTime = Date.now();
                
                // Llamada al servidor para iniciar carrera
                fetch('cronometraje_backend.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=start'
                });
                
                timerInterval = setInterval(updateTimer, 1000);
                document.getElementById('startBtn').disabled = true;
                document.getElementById('raceStatus').textContent = 'Carrera en curso';
                document.getElementById('dorsalInput').focus();
            }
        }

        function resetRace() {
            if (confirm('¿Reset completo? Esto borrará todos los tiempos.')) {
                raceStarted = false;
                startTime = null;
                clearInterval(timerInterval);
                
                fetch('cronometraje_backend.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=reset'
                });
                
                document.getElementById('startBtn').disabled = false;
                document.getElementById('raceStatus').textContent = 'Carrera no iniciada';
                document.getElementById('totalRunners').textContent = '0';
                document.getElementById('lastDorsal').textContent = '-';
                document.getElementById('lastTime').textContent = '-';
                document.getElementById('lastAdded').style.display = 'none';
                updateTimer();
                refreshResults();
            }
        }

        function addTime() {
            const dorsal = document.getElementById('dorsalInput').value;
            if (!dorsal || !raceStarted) {
                alert('Introduce un dorsal válido y asegúrate de que la carrera ha comenzado');
                return;
            }

            const currentTime = Date.now();
            
            fetch('cronometraje_backend.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=add_time&dorsal=${dorsal}&timestamp=${Math.floor(currentTime/1000)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const elapsed = Math.floor((currentTime - startTime) / 1000);
                    const timeStr = formatTime(elapsed);
                    
                    const lastAddedDiv = document.getElementById('lastAdded');
                    lastAddedDiv.innerHTML = `✅ Dorsal ${dorsal}: ${timeStr}`;
                    lastAddedDiv.style.display = 'block';
                    
                    document.getElementById('lastDorsal').textContent = dorsal;
                    document.getElementById('lastTime').textContent = timeStr;
                    
                    document.getElementById('dorsalInput').value = '';
                    document.getElementById('dorsalInput').focus();
                    refreshResults();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }

        function addManualTime() {
            const dorsal = document.getElementById('manualDorsal').value;
            const timeStr = document.getElementById('manualTime').value;
            
            if (!dorsal || !timeStr) {
                alert('Introduce dorsal y tiempo');
                return;
            }

            const timeParts = timeStr.split(':');
            if (timeParts.length !== 3) {
                alert('Formato de tiempo incorrecto (HH:MM:SS)');
                return;
            }

            const seconds = parseInt(timeParts[0]) * 3600 + parseInt(timeParts[1]) * 60 + parseInt(timeParts[2]);
            
            // Si la carrera está iniciada, calculamos el timestamp correcto
            let timestamp;
            if (raceStarted && startTime) {
                timestamp = Math.floor(startTime/1000) + seconds;
            } else {
                // Si no hay carrera iniciada, usamos tiempo actual menos los segundos
                timestamp = Math.floor(Date.now()/1000) - seconds;
            }

            fetch('cronometraje_backend.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=add_manual&dorsal=${dorsal}&timestamp=${timestamp}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('manualDorsal').value = '';
                    document.getElementById('manualTime').value = '';
                    
                    const lastAddedDiv = document.getElementById('lastAdded');
                    lastAddedDiv.innerHTML = `✅ Manual - Dorsal ${dorsal}: ${timeStr}`;
                    lastAddedDiv.style.display = 'block';
                    
                    refreshResults();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }

        function editTime(dorsal, newTime) {
            const timeParts = newTime.split(':');
            if (timeParts.length !== 3) {
                alert('Formato incorrecto (HH:MM:SS)');
                return;
            }

            const seconds = parseInt(timeParts[0]) * 3600 + parseInt(timeParts[1]) * 60 + parseInt(timeParts[2]);
            
            // Calcular timestamp correcto basado en el tiempo de inicio
            let timestamp;
            if (raceStarted && startTime) {
                timestamp = Math.floor(startTime/1000) + seconds;
            } else {
                timestamp = Math.floor(Date.now()/1000) - seconds;
            }

            fetch('cronometraje_backend.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=edit_time&dorsal=${dorsal}&timestamp=${timestamp}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    refreshResults();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }

        function deleteTime(dorsal) {
            if (confirm(`¿Eliminar tiempo del dorsal ${dorsal}?`)) {
                fetch('cronometraje_backend.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=delete_time&dorsal=${dorsal}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        refreshResults();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        function refreshResults() {
            fetch('cronometraje_backend.php?action=get_results')
            .then(response => response.json())
            .then(data => {
                displayResults(data);
            });
        }

        function displayResults(data) {
            const categories = ['10k_h', '10k_m', '5k_h', '5k_m', '2k_h', '2k_m'];
            let totalRunners = 0;
            
            categories.forEach(cat => {
                const container = document.getElementById(`results_${cat}`);
                container.innerHTML = '';
                
                if (data[cat]) {
                    totalRunners += data[cat].length;
                    data[cat].forEach((runner, index) => {
                        const div = document.createElement('div');
                        div.className = 'time-entry';
                        div.innerHTML = `
                            <span class="position">${index + 1}º</span>
                            <span class="runner-info">${runner.nombre} ${runner.apellido1} (${runner.dorsal})</span>
                            <span class="time-display">${runner.tiempo}</span>
                            <input type="text" class="edit-time" value="${runner.tiempo}" 
                                   onblur="editTime(${runner.dorsal}, this.value)" />
                            <button class="delete-btn" onclick="deleteTime(${runner.dorsal})">X</button>
                        `;
                        container.appendChild(div);
                    });
                }
            });
            
            document.getElementById('totalRunners').textContent = totalRunners;
        }

        function loadRaceStatus() {
            fetch('cronometraje_backend.php?action=get_status')
            .then(response => response.json())
            .then(data => {
                if (data.race_started) {
                    raceStarted = true;
                    startTime = data.start_time * 1000; // Convertir a milliseconds
                    timerInterval = setInterval(updateTimer, 1000);
                    document.getElementById('startBtn').disabled = true;
                    document.getElementById('raceStatus').textContent = 'Carrera en curso';
                } else {
                    document.getElementById('raceStatus').textContent = 'Carrera no iniciada';
                }
                updateTimer();
            });
        }

        function formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        // Permitir añadir tiempo con Enter
        document.getElementById('dorsalInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                addTime();
            }
        });

        // Auto-refresh cada 30 segundos
        setInterval(refreshResults, 30000);
    </script>
</body>
</html>