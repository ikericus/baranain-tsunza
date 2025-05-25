<?php
	include 'Db.php';
	
    $db = new Db();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>BT 2022 - Dorsales</title>
  <!-- Bootstrap core CSS-->
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom fonts for this template-->
  <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  <!-- Page level plugin CSS-->
  <link href="vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet">
  <!-- Custom styles for this template-->
  <link href="css/sb-admin.css" rel="stylesheet">
</head>

<body> 
  

          <div class="table-responsive">	
		  
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                  <th>Identificador</th>
                  <th>Orden</th>
				  <th>Email</th>
                  <th>Telf</th>
                  <th>DNI</th>
                  <th>Nombre</th>
				  <th>Apellido 1</th>
				  <th>Apellido 2</th>
				  <th>Edad</th>
				  <th>Sexo</th>
				  <th>Dorsal</th>
				  <th>Carrera</th>				  
				  <th>Camisetas inscripción</th>

                </tr>
              </thead>
              <tbody>
<?php	
				$result = $db -> query("SELECT id, orden, email, telf, dni, nombre, apellido1, apellido2, edad, sexo, dorsal, carrera, camisetas, recogido FROM 	( SELECT  			CASE			WHEN id_carrera = 1 THEN @diez:=@diez+1           					WHEN id_carrera = 2 THEN @cinco:=@cinco+1           					WHEN id_carrera = 3 THEN @dos:=@dos+1			WHEN id_carrera = 7 THEN @sd:=@sd+0       					ELSE @txiki:=@txiki+1 			END AS dorsal, 			id, orden, email, telf, dni, nombre, apellido1, apellido2, edad, id_carrera, carrera, sexo, camisetas, recogido        			FROM (               					SELECT 			(i.id) id,							(i.orden) orden,                          									min(i.email) email,                        									min(i.telefono) telf,                       									min(i.recogido) recogido,                       									min(co.dni) dni,                        									min(co.nombre) nombre,                       									min(co.apellido1) apellido1,                        									min(co.apellido2) apellido2,                        									min(co.edad) edad,                        									min(ca.id) id_carrera,                        									min(ca.descripcion) carrera,                        									min(s.descripcion) sexo,                        									GROUP_CONCAT(t.descripcion_corta SEPARATOR ', ') camisetas               									FROM inscripcion i     									join corredor co on i.id = co.id_inscripcion               									join carrera ca on co.id_carrera = ca.id               									join sexo s on co.id_sexo = s.id               									left outer join camiseta c on i.id = c.id_inscripcion               									left outer join talla t on c.id_talla = t.id      									WHERE i.pagado = 1      									group by co.id               									order by i.id asc     ) 	t1 , 							(SELECT @diez:=800) t2 , 							(SELECT @cinco:=440) t3 , 							(SELECT @dos:=240) t4 , 							(SELECT @txiki:=0) t6, 			(SELECT @sd:=-1) t7 ) as e     UNION ALL  	SELECT i.id, i.orden, i.email, i.telefono, '', '', '', '', '', '', 'Sólo camiseta', '', t.descripcion_corta, i.recogido   	FROM camiseta c   	left outer join inscripcion i on i.id = c.id_inscripcion   	left outer join talla t on c.id_talla = t.id   	WHERE c.id_inscripcion not in (SELECT distinct(id_inscripcion) FROM corredor)  	order by carrera, apellido1, apellido2, nombre");
								
				$row_cnt = $result->num_rows;
				$num = 1;
				if($row_cnt > 0)
				{
					for ($i = 1; $i <= $row_cnt; $i++) {
						$row = $result->fetch_array(MYSQLI_NUM);
						
						echo "<tr>
								<td>" . $row[0] . "</td>
								<td>" . $row[1] . "</td>
								<td>" . $row[2] . "</td>
								<td>" . $row[3] . "</td>
								<td>" . $row[4] . "</td>								
								<td>" . $row[5] . "</td>								
								<td>" . $row[6] . "</td>								
								<td>" . $row[7] . "</td>								
								<td>" . $row[8] . "</td>								
								<td>" . $row[9] . "</td>
								<td>" . $row[10] . "</td>
								<td>" . $row[11] . "</td>
								<td>" . $row[12] . "</td>";
								
						echo "</tr>";
						
						$num++;
					}
				}
?>	
              </tbody>
            </table>
          </div>
		  

	
    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Page level plugin JavaScript-->
    <script src="vendor/datatables/jquery.dataTables.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin.min.js"></script>
    <!-- Custom scripts for this page-->
    <script src="js/sb-admin-datatables.min.js"></script>
	
			  <script>
		  
			$(document).ready(function() {

				$('.table').change(function() {
					$('.recogidoCheckbox').change(function() {
						
						if ($(this).prop("checked"))
							var returnVal = confirm("¿Deseas marcar como recogido el identificador " + this.id + " ?");	
						else
							var returnVal = confirm("¿Deseas desmarcar como recogido el identificador " + this.id + " ?");						
						
						if(returnVal)
						{
							$.ajax({
							  method: "POST",
							  url: "recoger.php",
							  data: { id: this.id }
							})
							  .done(function( msg ) {
								alert( msg );
								location.reload();
							  });
						}		
						$(this).prop("checked", !this.checked)					
					});
				});
			});
		  </script>
  </div>
</body>

</html>