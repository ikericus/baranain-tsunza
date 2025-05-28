<?php
header('Content-Type: application/json');
include 'Db.php';

$db = new Db();

try {
    // Consulta basada en tu estructura existente de dorsales.php
    $query = "
        SELECT 
            id, orden, email, telf as telefono, dni, nombre, apellido1, apellido2, 
            edad, sexo, dorsal, carrera, camisetas, recogido 
        FROM (
            SELECT  
                CASE
                    WHEN id_carrera = 1 THEN @diez:=@diez+1           					
                    WHEN id_carrera = 2 THEN @cinco:=@cinco+1           					
                    WHEN id_carrera = 3 THEN @dos:=@dos+1			
                    WHEN id_carrera = 7 THEN @sd:=@sd+0       					
                    ELSE @txiki:=@txiki+0 
                END AS dorsal, 
                id, orden, email, telf, dni, nombre, apellido1, apellido2, 
                edad, id_carrera, carrera, sexo, camisetas, recogido        
            FROM (               
                SELECT 
                    (i.id) id,
                    (i.orden) orden,                          
                    min(i.email) email,                        
                    min(i.telefono) telf,                       
                    min(i.recogido) recogido,                       
                    min(co.dni) dni,                        
                    min(co.nombre) nombre,                       
                    min(co.apellido1) apellido1,                        
                    min(co.apellido2) apellido2,                        
                    min(co.edad) edad,                        
                    min(ca.id) id_carrera,                        
                    min(ca.descripcion) carrera,                        
                    min(s.descripcion) sexo,                        
                    GROUP_CONCAT(t.descripcion_corta SEPARATOR ', ') camisetas               
                FROM inscripcion i     
                JOIN corredor co ON i.id = co.id_inscripcion               
                JOIN carrera ca ON co.id_carrera = ca.id               
                JOIN sexo s ON co.id_sexo = s.id               
                LEFT OUTER JOIN camiseta c ON i.id = c.id_inscripcion               
                LEFT OUTER JOIN talla t ON c.id_talla = t.id      
                WHERE i.pagado = 1      
                GROUP BY co.id               
                ORDER BY i.id ASC     
            ) t1, 
            (SELECT @diez:=800) t2, 
            (SELECT @cinco:=440) t3, 
            (SELECT @dos:=240) t4, 
            (SELECT @txiki:=0) t6, 
            (SELECT @sd:=-1) t7 
        ) as e 
        
        UNION ALL
        
        SELECT 
            i.id, i.orden, i.email, i.telefono, '' as dni, '' as nombre, 
            '' as apellido1, '' as apellido2, 0 as edad, '' as sexo, 
            0 as dorsal, 'Sólo camiseta' as carrera, 
            t.descripcion_corta as camisetas, i.recogido
        FROM camiseta c   
        LEFT OUTER JOIN inscripcion i ON i.id = c.id_inscripcion   
        LEFT OUTER JOIN talla t ON c.id_talla = t.id   
        WHERE c.id_inscripcion NOT IN (SELECT DISTINCT(id_inscripcion) FROM corredor)
        AND i.pagado = 1
        
        ORDER BY carrera, apellido1, apellido2, nombre
    ";
    
    $result = $db->query($query);
    $corredores = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $corredores[] = [
                'id' => (int)$row['id'],
                'orden' => (int)$row['orden'],
                'email' => $row['email'],
                'telefono' => $row['telefono'],
                'dni' => $row['dni'],
                'nombre' => $row['nombre'],
                'apellido1' => $row['apellido1'],
                'apellido2' => $row['apellido2'] ?: '',
                'edad' => (int)$row['edad'],
                'sexo' => $row['sexo'] ?: 'N/A',
                'dorsal' => (int)$row['dorsal'],
                'carrera' => $row['carrera'],
                'camisetas' => $row['camisetas'] ?: 'Sin camiseta',
                'recogido' => (int)$row['recogido']
            ];
        }
    }
    
    echo json_encode($corredores);
    
} catch (Exception $e) {
    error_log("get_corredores.php error: " . $e->getMessage(), 3, "../../errors.log");
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
?>