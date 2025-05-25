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
  <meta http-equiv="refresh" content="2">
  <title>BT 2024 - Tiempos</title>

  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  <link href="vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet">
  <link href="css/sb-admin.css" rel="stylesheet">
</head>
<style>
th
{
	padding: 0px !important;
}
</style>

<body class="bg-dark" >
  
    <div class="container-fluid m-0 p-0">
		<div class="row">
		
			<div class="card-group col-12">
		
			  <div class="card m-1">
					<div class="card-body m-0 p-0">
						<div class="mx-auto" style="width: 800px;">

							<div class="form-inline">
								<?php
									
									if(isset($_POST['start'])){
										//echo "alert('Empieza');";
										$ctime = time();
										$db -> query("DELETE FROM parametro WHERE clave = 'start'"); 
										$db -> query("INSERT INTO parametro (Clave, Valor) VALUES ('start'," . $ctime .")"); 
										//echo "start();";										
									}
									if(isset($_POST['reset'])){			
										$db -> query("DELETE FROM parametro WHERE clave = 'start'"); 
										$db -> query("DELETE FROM tiempos"); 
									}
									
									$start_time	= $db -> query("SELECT valor FROM parametro WHERE clave = 'start'") -> fetch_object() -> valor; 
										
									if(isset($start_time))
									{
										$current_time = time();
										$elapsed = $current_time - $start_time;
									
										$hours= floor($elapsed/(60*60));
										$mins = floor(($elapsed-($hours*60*60))/(60));
										$secs = floor(($elapsed-(($hours*60*60)+($mins*60))));	
																				
										//echo "alert('horas: " . $hours . ", minutos: " . $mins . ", segundos: " . $secs . "');";
										
										//echo "let hours = '" . $hours . "', minutes = '" . str_pad($mins, 2, "0", STR_PAD_LEFT) . "',  seconds = '" . str_pad($secs, 2, "0", STR_PAD_LEFT) . "	';";
										//echo "start();";										
										echo "<span style='text-align: center; font-size: 6rem;'>" . $hours . ":" . str_pad($mins, 2, "0", STR_PAD_LEFT) . ":" . str_pad($secs, 2, "0", STR_PAD_LEFT) . "</span>";
									}
									else
									{
										//echo "alert('No hay carrera iniciada');";
										echo "<span style='text-align: center; font-size: 5rem;'>0:00:00</span>";									
									}
								?>
								
								<!--<span style="text-align: center; font-size: 5rem;">0:00:00</span>-->
								<form method="post" action="tiempos.php" >
									<input type="hidden" name="start" value="1">
									<button class="btn m-1"  id="play">Empieza carrera</button>
								</form>	
								<form method="post" action="tiempos.php" >
									<input type="hidden" name="reset" value="1">
									<button class="btn m-1" >Borrar tiempos</button>
								</form>								
							</div>
						</div>
						
					</div>
			  </div>
			</div>
		</div>
		<div class="row">
			<div class="card-group col-12">
		
				  
				  
				  <!-- 5K HOMBRE -->
				  <div class="card m-1 p-1">
					<div class="card-header p-1">
					  <i class="fa fa-table"></i> 5K Hombre</div>
						<div class="card-body p-0">
						  <div class="table-responsive">
							<table class="table table-bordered table-sm" >
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
																		WHERE i.pagado = 1 and co.id_carrera = 2								
																		ORDER BY i.id asc ) 	t1 ,					 												
																(SELECT @cinco:=440) t2
															) d
														JOIN tiempos t ON d.dorsal = t.dorsal
														WHERE id_sexo = 2
														ORDER BY timestamp asc
														LIMIT 10");
													
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
					<!-- <div class="card-footer small text-muted">Updated yesterday at 11:59 PM</div> -->
				  </div>
				  
				  
				  <!-- 10K HOMBRE -->
				  <div class="card m-1 p-1">
					<div class="card-header p-1">
					  <i class="fa fa-table"></i> 10K Hombre</div>
						<div class="card-body p-0">
						  <div class="table-responsive">
							<table class="table table-bordered table-sm" >
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
															SELECT @diez:=@diez+1 AS dorsal, 			
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
																		WHERE i.pagado = 1 and co.id_carrera = 1									
																		ORDER BY i.id asc ) 	t1 ,					 												
																(SELECT @diez:=800) t2
															) d
														JOIN tiempos t ON d.dorsal = t.dorsal
														WHERE id_sexo = 2
														ORDER BY timestamp asc
														LIMIT 10");
													
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
					<!-- <div class="card-footer small text-muted">Updated yesterday at 11:59 PM</div> -->
				  </div>
			</div>	
		</div>	
		
		<div class="row">
			<div class="card-group col-12">
		
			
			  
			  <!-- 5K MUJER -->
			  <div class="card m-1 p-1">

				<div class="card-header p-1">
				  <i class="fa fa-table"></i> 5K Mujer</div>
					<div class="card-body p-0">
					  <div class="table-responsive">
						<table class="table table-bordered table-sm" id="dataTable2" width="100%" cellspacing="0">
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
																		WHERE i.pagado = 1 and co.id_carrera = 2									
																		ORDER BY i.id asc ) 	t1 ,					 												
																(SELECT @cinco:=440) t2
															) d
														JOIN tiempos t ON d.dorsal = t.dorsal
														WHERE id_sexo = 1
														ORDER BY timestamp asc
														LIMIT 10");												
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
				<!-- <div class="card-footer small text-muted">Updated yesterday at 11:59 PM</div> -->
			  </div>
			
			
			  <!-- 10K MUJER -->
			  <div class="card m-1 p-1">
			  

				<div class="card-header p-1">
				  <i class="fa fa-table"></i> 10K Mujer</div>
					<div class="card-body p-0">
					  <div class="table-responsive">
						<table class="table table-bordered table-sm" id="dataTable2" width="100%" cellspacing="0">
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
															SELECT @diez:=@diez+1 AS dorsal, 			
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
																		WHERE i.pagado = 1 and co.id_carrera = 1									
																		ORDER BY i.id asc ) 	t1 ,					 												
																(SELECT @diez:=800) t2
															) d
														JOIN tiempos t ON d.dorsal = t.dorsal
														WHERE id_sexo = 1
														ORDER BY timestamp asc
														LIMIT 10");
												
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
				<!-- <div class="card-footer small text-muted">Updated yesterday at 11:59 PM</div> -->
			  </div>
						
			</div>
		</div>
	</div>
  
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.js"></script>
    <script src="js/sb-admin.min.js"></script>
    <script src="js/sb-admin-datatables.min.js"></script>
  </div>
</body>

</html>