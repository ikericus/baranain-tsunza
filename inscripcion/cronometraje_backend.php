<?php
header('Content-Type: application/json');
include 'Db.php';

$db = new Db();

function getRunnerInfo($db, $dorsal) {
    // Consulta compleja para obtener dorsal, nombre y categoría basada en tu lógica existente
    $query = "
        SELECT 
            dorsal_calc.dorsal,
            dorsal_calc.nombre,
            dorsal_calc.apellido1,
            dorsal_calc.apellido2,
            dorsal_calc.id_sexo,
            dorsal_calc.id_carrera
        FROM (
            SELECT 
                CASE
                    WHEN id_carrera = 1 THEN @diez:=@diez+1           					
                    WHEN id_carrera = 2 THEN @cinco:=@cinco+1           					
                    WHEN id_carrera = 3 THEN @dos:=@dos+1			
                    WHEN id_carrera = 7 THEN @sd:=@sd+0       					
                    ELSE @txiki:=@txiki+1 
                END AS dorsal,
                co.nombre,
                co.apellido1,
                co.apellido2,
                co.id_sexo,
                co.id_carrera
            FROM (
                SELECT 
                    co.nombre,
                    co.apellido1,
                    co.apellido2,
                    co.id_sexo,
                    co.id_carrera
                FROM inscripcion i 
                JOIN corredor co ON i.id = co.id_inscripcion               									
                WHERE i.pagado = 1      									
                ORDER BY i.id ASC
            ) co,
            (SELECT @diez:=800) t2,
            (SELECT @cinco:=440) t3,
            (SELECT @dos:=240) t4,
            (SELECT @txiki:=0) t6,
            (SELECT @sd:=-1) t7
        ) dorsal_calc
        WHERE dorsal_calc.dorsal = " . intval($dorsal);
    
    $result = $db->query($query);
    return $result && $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

function formatTimeFromSeconds($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'start':
            // Iniciar carrera - borrar tiempos anteriores y establecer nuevo start time
            $db->query("DELETE FROM parametro WHERE clave = 'start'");
            $db->query("DELETE FROM tiempos");
            $start_time = time();
            $db->query("INSERT INTO parametro (clave, valor) VALUES ('start', " . $start_time . ")");
            echo json_encode(['success' => true, 'start_time' => $start_time]);
            break;
            
        case 'reset':
            // Reset completo
            $db->query("DELETE FROM parametro WHERE clave = 'start'");
            $db->query("DELETE FROM tiempos");
            echo json_encode(['success' => true]);
            break;
            
        case 'add_time':
            $dorsal = intval($_POST['dorsal']);
            $timestamp = intval($_POST['timestamp']);
            
            // Verificar que el dorsal existe
            $runner = getRunnerInfo($db, $dorsal);
            if (!$runner) {
                echo json_encode(['success' => false, 'message' => 'Dorsal no encontrado']);
                break;
            }
            
            // Verificar que no existe ya un tiempo para este dorsal
            $existing = $db->query("SELECT dorsal FROM tiempos WHERE dorsal = " . $dorsal);
            if ($existing && $existing->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Ya existe tiempo para este dorsal']);
                break;
            }
            
            // Insertar tiempo
            $result = $db->query("INSERT INTO tiempos (timestamp, dorsal) VALUES (" . $timestamp . ", " . $dorsal . ")");
            echo json_encode(['success' => (bool)$result]);
            break;
            
        case 'add_manual':
            $dorsal = intval($_POST['dorsal']);
            $timestamp = intval($_POST['timestamp']);
            
            // Verificar que el dorsal existe
            $runner = getRunnerInfo($db, $dorsal);
            if (!$runner) {
                echo json_encode(['success' => false, 'message' => 'Dorsal no encontrado']);
                break;
            }
            
            // Eliminar tiempo existente si lo hay
            $db->query("DELETE FROM tiempos WHERE dorsal = " . $dorsal);
            
            // Insertar nuevo tiempo
            $result = $db->query("INSERT INTO tiempos (timestamp, dorsal) VALUES (" . $timestamp . ", " . $dorsal . ")");
            echo json_encode(['success' => (bool)$result]);
            break;
            
        case 'edit_time':
            $dorsal = intval($_POST['dorsal']);
            $timestamp = intval($_POST['timestamp']);
            
            $result = $db->query("UPDATE tiempos SET timestamp = " . $timestamp . " WHERE dorsal = " . $dorsal);
            echo json_encode(['success' => (bool)$result]);
            break;
            
        case 'delete_time':
            $dorsal = intval($_POST['dorsal']);
            $result = $db->query("DELETE FROM tiempos WHERE dorsal = " . $dorsal);
            echo json_encode(['success' => (bool)$result]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} else {
    // GET requests
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_status':
            $start_result = $db->query("SELECT valor FROM parametro WHERE clave = 'start'");
            $race_started = false;
            $start_time = null;
            
            if ($start_result && $start_result->num_rows > 0) {
                $race_started = true;
                $start_time = $start_result->fetch_object()->valor;
            }
            
            echo json_encode([
                'race_started' => $race_started,
                'start_time' => $start_time
            ]);
            break;
            
        case 'get_results':
            $start_result = $db->query("SELECT valor FROM parametro WHERE clave = 'start'");
            $start_time = 0;
            if ($start_result && $start_result->num_rows > 0) {
                $start_time = $start_result->fetch_object()->valor;
            }
            
            $results = [
                '10k_h' => [],
                '10k_m' => [],
                '5k_h' => [],
                '5k_m' => [],
                '2k_h' => [],
                '2k_m' => []
            ];
            
            // 10K Hombres
            $query = "
                SELECT t.dorsal, dorsal_calc.nombre, dorsal_calc.apellido1, dorsal_calc.apellido2, t.timestamp
                FROM (
                    SELECT 
                        @diez:=@diez+1 AS dorsal,
                        co.id_sexo,
                        co.nombre,
                        co.apellido1,
                        co.apellido2
                    FROM (
                        SELECT 
                            co.id_sexo,
                            co.nombre,
                            co.apellido1,
                            co.apellido2
                        FROM inscripcion i 
                        JOIN corredor co ON i.id = co.id_inscripcion               									
                        JOIN carrera ca ON co.id_carrera = ca.id               									
                        WHERE i.pagado = 1 AND co.id_carrera = 1								
                        ORDER BY i.id ASC
                    ) co,
                    (SELECT @diez:=800) t2
                ) dorsal_calc
                JOIN tiempos t ON dorsal_calc.dorsal = t.dorsal
                WHERE dorsal_calc.id_sexo = 2
                ORDER BY t.timestamp ASC";
            
            $result = $db->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $elapsed = $row['timestamp'] - $start_time;
                    $results['10k_h'][] = [
                        'dorsal' => $row['dorsal'],
                        'nombre' => $row['nombre'],
                        'apellido1' => $row['apellido1'],
                        'apellido2' => $row['apellido2'],
                        'tiempo' => formatTimeFromSeconds($elapsed)
                    ];
                }
            }
            
            // 10K Mujeres
            $query = "
                SELECT t.dorsal, dorsal_calc.nombre, dorsal_calc.apellido1, dorsal_calc.apellido2, t.timestamp
                FROM (
                    SELECT 
                        @diez:=@diez+1 AS dorsal,
                        co.id_sexo,
                        co.nombre,
                        co.apellido1,
                        co.apellido2
                    FROM (
                        SELECT 
                            co.id_sexo,
                            co.nombre,
                            co.apellido1,
                            co.apellido2
                        FROM inscripcion i 
                        JOIN corredor co ON i.id = co.id_inscripcion               									
                        JOIN carrera ca ON co.id_carrera = ca.id               									
                        WHERE i.pagado = 1 AND co.id_carrera = 1								
                        ORDER BY i.id ASC
                    ) co,
                    (SELECT @diez:=800) t2
                ) dorsal_calc
                JOIN tiempos t ON dorsal_calc.dorsal = t.dorsal
                WHERE dorsal_calc.id_sexo = 1
                ORDER BY t.timestamp ASC";
            
            $result = $db->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $elapsed = $row['timestamp'] - $start_time;
                    $results['10k_m'][] = [
                        'dorsal' => $row['dorsal'],
                        'nombre' => $row['nombre'],
                        'apellido1' => $row['apellido1'],
                        'apellido2' => $row['apellido2'],
                        'tiempo' => formatTimeFromSeconds($elapsed)
                    ];
                }
            }
            
            // 5K Hombres
            $query = "
                SELECT t.dorsal, dorsal_calc.nombre, dorsal_calc.apellido1, dorsal_calc.apellido2, t.timestamp
                FROM (
                    SELECT 
                        @cinco:=@cinco+1 AS dorsal,
                        co.id_sexo,
                        co.nombre,
                        co.apellido1,
                        co.apellido2
                    FROM (
                        SELECT 
                            co.id_sexo,
                            co.nombre,
                            co.apellido1,
                            co.apellido2
                        FROM inscripcion i 
                        JOIN corredor co ON i.id = co.id_inscripcion               									
                        JOIN carrera ca ON co.id_carrera = ca.id               									
                        WHERE i.pagado = 1 AND co.id_carrera = 2								
                        ORDER BY i.id ASC
                    ) co,
                    (SELECT @cinco:=440) t3
                ) dorsal_calc
                JOIN tiempos t ON dorsal_calc.dorsal = t.dorsal
                WHERE dorsal_calc.id_sexo = 2
                ORDER BY t.timestamp ASC";
            
            $result = $db->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $elapsed = $row['timestamp'] - $start_time;
                    $results['5k_h'][] = [
                        'dorsal' => $row['dorsal'],
                        'nombre' => $row['nombre'],
                        'apellido1' => $row['apellido1'],
                        'apellido2' => $row['apellido2'],
                        'tiempo' => formatTimeFromSeconds($elapsed)
                    ];
                }
            }
            
            // 5K Mujeres
            $query = "
                SELECT t.dorsal, dorsal_calc.nombre, dorsal_calc.apellido1, dorsal_calc.apellido2, t.timestamp
                FROM (
                    SELECT 
                        @cinco:=@cinco+1 AS dorsal,
                        co.id_sexo,
                        co.nombre,
                        co.apellido1,
                        co.apellido2
                    FROM (
                        SELECT 
                            co.id_sexo,
                            co.nombre,
                            co.apellido1,
                            co.apellido2
                        FROM inscripcion i 
                        JOIN corredor co ON i.id = co.id_inscripcion               									
                        JOIN carrera ca ON co.id_carrera = ca.id               									
                        WHERE i.pagado = 1 AND co.id_carrera = 2								
                        ORDER BY i.id ASC
                    ) co,
                    (SELECT @cinco:=440) t3
                ) dorsal_calc
                JOIN tiempos t ON dorsal_calc.dorsal = t.dorsal
                WHERE dorsal_calc.id_sexo = 1
                ORDER BY t.timestamp ASC";
            
            $result = $db->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $elapsed = $row['timestamp'] - $start_time;
                    $results['5k_m'][] = [
                        'dorsal' => $row['dorsal'],
                        'nombre' => $row['nombre'],
                        'apellido1' => $row['apellido1'],
                        'apellido2' => $row['apellido2'],
                        'tiempo' => formatTimeFromSeconds($elapsed)
                    ];
                }
            }
            
            // 2K Hombres
            $query = "
                SELECT t.dorsal, dorsal_calc.nombre, dorsal_calc.apellido1, dorsal_calc.apellido2, t.timestamp
                FROM (
                    SELECT 
                        @dos:=@dos+1 AS dorsal,
                        co.id_sexo,
                        co.nombre,
                        co.apellido1,
                        co.apellido2
                    FROM (
                        SELECT 
                            co.id_sexo,
                            co.nombre,
                            co.apellido1,
                            co.apellido2
                        FROM inscripcion i 
                        JOIN corredor co ON i.id = co.id_inscripcion               									
                        JOIN carrera ca ON co.id_carrera = ca.id               									
                        WHERE i.pagado = 1 AND co.id_carrera = 3								
                        ORDER BY i.id ASC
                    ) co,
                    (SELECT @dos:=240) t4
                ) dorsal_calc
                JOIN tiempos t ON dorsal_calc.dorsal = t.dorsal
                WHERE dorsal_calc.id_sexo = 2
                ORDER BY t.timestamp ASC";
            
            $result = $db->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $elapsed = $row['timestamp'] - $start_time;
                    $results['2k_h'][] = [
                        'dorsal' => $row['dorsal'],
                        'nombre' => $row['nombre'],
                        'apellido1' => $row['apellido1'],
                        'apellido2' => $row['apellido2'],
                        'tiempo' => formatTimeFromSeconds($elapsed)
                    ];
                }
            }
            
            // 2K Mujeres
            $query = "
                SELECT t.dorsal, dorsal_calc.nombre, dorsal_calc.apellido1, dorsal_calc.apellido2, t.timestamp
                FROM (
                    SELECT 
                        @dos:=@dos+1 AS dorsal,
                        co.id_sexo,
                        co.nombre,
                        co.apellido1,
                        co.apellido2
                    FROM (
                        SELECT 
                            co.id_sexo,
                            co.nombre,
                            co.apellido1,
                            co.apellido2
                        FROM inscripcion i 
                        JOIN corredor co ON i.id = co.id_inscripcion               									
                        JOIN carrera ca ON co.id_carrera = ca.id               									
                        WHERE i.pagado = 1 AND co.id_carrera = 3								
                        ORDER BY i.id ASC
                    ) co,
                    (SELECT @dos:=240) t4
                ) dorsal_calc
                JOIN tiempos t ON dorsal_calc.dorsal = t.dorsal
                WHERE dorsal_calc.id_sexo = 1
                ORDER BY t.timestamp ASC";
            
            $result = $db->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $elapsed = $row['timestamp'] - $start_time;
                    $results['2k_m'][] = [
                        'dorsal' => $row['dorsal'],
                        'nombre' => $row['nombre'],
                        'apellido1' => $row['apellido1'],
                        'apellido2' => $row['apellido2'],
                        'tiempo' => formatTimeFromSeconds($elapsed)
                    ];
                }
            }
            
            echo json_encode($results);
            break;
            
        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
}
?>