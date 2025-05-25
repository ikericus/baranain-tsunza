<?php
	include 'Db.php';	
    $db = new Db();
	$start_time	= $db -> query("SELECT valor FROM parametro WHERE clave = 'start'") -> fetch_object() -> valor; 
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>BT 2024</title>
  <!-- Bootstrap core CSS-->
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom fonts for this template-->
  <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  <!-- Page level plugin CSS-->
  <link href="vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet">
  <!-- Custom styles for this template-->
  <link href="css/sb-admin.css" rel="stylesheet">
</head>

<body class="bg-dark" >
  
    <div class="container-fluid  m-0 p-4">
		
      <div class="card mb-3">
        <div class="card-header">
          <i class="fa fa-table"></i> 2k Hombre/Gizon</div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
				  <th>#</th>
				  <th>Tiempo</th>
				  <th>Corredor</th>
				  <th>Dorsal</th>
                </tr>
              </thead>
              <tbody>
				<?php
				$result = $db -> query("SELECT t.dorsal, nombre, apellido1, apellido2, timestamp
														FROM (		
															SELECT @cinco:=@cinco+1 AS dorsal, 			
																id_sexo, 
																nombre, 
																apellido1, 
																apellido2     			
																FROM (               					
																		SELECT co.id_sexo, co.nombre nombre, co.apellido1 apellido1,co.apellido2 apellido2                     									
																		FROM inscripcion i     									
																		join corredor co on i.id = co.id_inscripcion               									
																		join carrera ca on co.id_carrera = ca.id               									
																		join sexo s on co.id_sexo = s.id               									
																		WHERE i.pagado = 1 and co.id_carrera = 3								
																		ORDER BY i.id asc ) 	t1 ,					 												
																(SELECT @cinco:=240) t2
															) d
														JOIN tiempos t ON d.dorsal = t.dorsal
														WHERE id_sexo = 2
														ORDER BY timestamp asc");
								
									$row_cnt = $result->num_rows;
									$num = 1;
									if($row_cnt > 0)
									{
										for ($i = 1; $i <= $row_cnt; $i++) {
											$row = $result->fetch_array(MYSQLI_NUM);
											$seconds = $row[4]-$start_time;
											$secs = $seconds % 60;
											$hrs = $seconds / 60;
											$mins = $hrs % 60;
											$hrs = $hrs / 60;

											echo "<tr>
													<td>" . $num . "</td>
													<td>" . (int)$hrs . ":" . str_pad($mins,  2, "0", STR_PAD_LEFT) . ":" . str_pad($secs,  2, "0", STR_PAD_LEFT) . "</td>
													<td>" . $row[1] . " "  . $row[2] . " " . $row[3] . "</td>												
													<td>" . $row[0] . "</td>
												  </tr>";
											$num++;
										}
									}
									?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
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
  </div>
</body>

</html>