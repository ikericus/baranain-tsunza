<?php
header('Content-Type: application/json');
include 'Db.php';

$db = new Db();

try {
    // Primero obtener las inscripciones básicas
    $inscripcionesQuery = "
        SELECT id, orden, email, telefono, recogido 
        FROM inscripcion 
        WHERE pagado = 1 
        ORDER BY orden ASC
    ";
    
    $inscripcionesResult = $db->query($inscripcionesQuery);
    $inscripciones = [];
    
    if ($inscripcionesResult && $inscripcionesResult->num_rows > 0) {
        // Contadores para dorsales por categoría
        $contadores = ['10k' => 900, '5k' => 400, '2k' => 250];
        
        while ($inscripcion = $inscripcionesResult->fetch_assoc()) {
            $inscripcionId = $inscripcion['id'];
            
            // Obtener corredores de esta inscripción
            $corredoresQuery = "
                SELECT 
                    co.nombre, co.apellido1, co.apellido2, co.dni, co.edad,
                    s.descripcion as sexo,
                    ca.id as carrera_id, ca.descripcion as carrera
                FROM corredor co
                JOIN carrera ca ON co.id_carrera = ca.id
                JOIN sexo s ON co.id_sexo = s.id
                WHERE co.id_inscripcion = $inscripcionId
                ORDER BY co.id
            ";
            
            $corredoresResult = $db->query($corredoresQuery);
            $corredores = [];
            
            if ($corredoresResult && $corredoresResult->num_rows > 0) {
                while ($corredor = $corredoresResult->fetch_assoc()) {
                    // Asignar dorsal según categoría
                    $dorsalInfo = '';
                    switch ($corredor['carrera_id']) {
                        case 1: // 10K
                            $contadores['10k']++;
                            $dorsalInfo = "10K-{$contadores['10k']}";
                            break;
                        case 2: // 5K
                            $contadores['5k']++;
                            $dorsalInfo = "5K-{$contadores['5k']}";
                            break;
                        case 3: // 2K
                            $contadores['2k']++;
                            $dorsalInfo = "2K-{$contadores['2k']}";
                            break;
                        case 7: // Sin dorsal
                            $dorsalInfo = "SD-0";
                            break;
                        default: // Txiki
                            $dorsalInfo = "TXIKI-0";
                            break;
                    }
                    
                    $corredores[] = [
                        'nombre' => $corredor['nombre'],
                        'apellido1' => $corredor['apellido1'],
                        'apellido2' => $corredor['apellido2'] ?: '',
                        'dni' => $corredor['dni'],
                        'edad' => (int)$corredor['edad'],
                        'sexo' => $corredor['sexo'],
                        'carrera' => $corredor['carrera'],
                        'dorsal_info' => $dorsalInfo
                    ];
                }
            }
            
            // Obtener camisetas de esta inscripción
            $camisetasQuery = "
                SELECT t.descripcion_corta as camiseta
                FROM camiseta c
                JOIN talla t ON c.id_talla = t.id
                WHERE c.id_inscripcion = $inscripcionId
                ORDER BY t.id
            ";
            
            $camisetasResult = $db->query($camisetasQuery);
            $camisetas = [];
            
            if ($camisetasResult && $camisetasResult->num_rows > 0) {
                while ($camiseta = $camisetasResult->fetch_assoc()) {
                    $camisetas[] = $camiseta['camiseta'];
                }
            }
            
            $inscripciones[] = [
                'inscripcion_id' => (int)$inscripcion['id'],
                'orden' => (int)$inscripcion['orden'],
                'email' => $inscripcion['email'],
                'telefono' => $inscripcion['telefono'],
                'recogido' => (int)$inscripcion['recogido'],
                'corredores' => $corredores,
                'camisetas' => !empty($camisetas) ? implode(', ', $camisetas) : 'Sin camisetas',
                'total_corredores' => count($corredores),
                'total_camisetas' => count($camisetas)
            ];
        }
    }
    
    echo json_encode($inscripciones);
    
} catch (Exception $e) {
    error_log("get_inscripciones.php error: " . $e->getMessage(), 3, "../../errors.log");
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
?>