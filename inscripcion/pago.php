<html> 
<body> 
<?php
	include 'Db.php';
	include 'apiRedsys.php';
						
	function enviarEmail($subject, $to, $message){		
		$headers 	= 'From: info@baranain-tsunza.com' . PHP_EOL ;				
		mail ( $to, $subject, utf8_decode($message), $headers ) ;
	}

	function inscripcion(){
		
		$db = new Db();		
		$miObj = new RedsysAPI;			
		
		$config = parse_ini_file('../../config.ini'); 
	
		$datos 				= $_REQUEST["Ds_MerchantParameters"];
		$signatureRecibida 	= $_REQUEST["Ds_Signature"];
								
		$kc 		= $config['clave_tpv']; 
		$firma 		= $miObj->createMerchantSignatureNotif($kc,$datos);	
				
		$response 	= $miObj->getParameter("Ds_Response");		
		
		if ($firma === $signatureRecibida && $response == '0000'){
			
			$order	= $miObj->getParameter("Ds_Order");	
			
			if($db -> query("UPDATE inscripcion SET pagado = true where orden = " . $order))
			{
				enviarCorreoOrden($db, $order);
			}
		}
	}
	
	function inscripcionFisica(){
		
		$db = new Db();	
				
		//echo $_POST['username'];
		$result = $db -> query("SELECT * FROM usuario where username = " . $db -> quote ($_POST['username']) . "and activo = 1");
	
		$row_cnt = $result->num_rows;
					
		if($row_cnt > 0)
		{
			$row = $result->fetch_array(MYSQLI_NUM);
			$hashDB = $row[2];
			
			if (password_verify($_POST['userpass'], $hashDB)) { 
				
				$order	= $_POST["order"];	
								
				if($db -> query("UPDATE inscripcion SET pagado = true where orden = " . $order))
				{	
					
					echo "<form action='inscripcion.php' method='post' > <input type='hidden' value='Vaciar carro' name='action'><button type='submit'>Otra inscripción</button></form>";
			
					echo "</br>";
					echo "</br>";
					enviarCorreoOrden($db, $order);
				}
			 }
		}
	}
	
	function enviarCorreoOrden($db, $order){  
	
		$texto  = "¡Felicidades!  \n\n";
		$texto .= "Estos son los datos de inscripción (Nº " . $order . "): \n\n";
		
		$inscripcion 	= $db -> select("SELECT * FROM inscripcion WHERE pagado = true and orden = " . $order)[0];
		$id_inscripcion = $inscripcion['id'];
		$email 			= $inscripcion['email'];
		$subject 		= 'Inscripcion Baranain-Tsunza 2025';
						
		if($id_inscripcion)
		{			
			$corredores = $db -> select("SELECT * FROM corredor WHERE id_inscripcion = " . $id_inscripcion);	
			$corredor_num = 1;
			$total = 0;
			
			foreach ($corredores as $row)
			{
				$id_sexo	= $row['id_sexo'];
				$id_carrera	= $row['id_carrera'];
				$nombre		= $row['nombre'];
				$apellido1 	= $row['apellido1'];
				$apellido2 	= $row['apellido2'];
				$edad		= $row['edad'];
				
				$sexo		= $db -> select("SELECT descripcion FROM sexo where id = " . $id_sexo)[0]['descripcion']; 
				$carrera	= $db -> select("SELECT * FROM carrera where id = " . $id_carrera)[0];
				
				$distancia	= $carrera['descripcion'];
				$precio		= $carrera['precio'];
				
				$total 		+= $precio;
				
				$texto .= "\t- Corredor {$corredor_num} ({$precio} euros) \n";
				$texto .= "\t\tDistancia: {$distancia} \n";
				$texto .= "\t\tSexo: {$sexo} \n";
				$texto .= "\t\tNombre y apellidos: {$nombre} {$apellido1} {$apellido2} \n\n";
				
				$corredor_num++;
			}
			
			$camisetas = $db -> select("SELECT id_talla FROM camiseta WHERE id_inscripcion = " . $id_inscripcion);	
			$camiseta_num = 1;
			foreach ($camisetas as $row)
			{
				$id_talla	= $row['id_talla'];
				
				$descripcion = $db -> query("SELECT descripcion FROM talla where id = " . $id_talla) -> fetch_object() -> descripcion;
				
				$texto .= "\t- Camiseta {$camiseta_num} (Talla: {$descripcion}) \n\n";
				
				$camiseta_num++;
			}
			
			$donaciones = $db -> select("SELECT id_donacion FROM donativo WHERE id_inscripcion = " . $id_inscripcion);	
			$donacion_num = 1;
			foreach ($donaciones as $row)
			{
				$id_donacion	= $row['id_donacion'];
				
				$donacion = $db -> query("SELECT precio, descripcion FROM donacion where id = " . $id_donacion);
				
				$precio 	 = $donacion -> fetch_object() -> precio;
				
				$total 		+= $precio;
								
				$texto .= "\t- Donación {$donacion_num} ({$precio} euros) \n\n";
				
				$donacion_num++;
			}
			
			
			$envio	= $db -> select("SELECT direccion FROM envio WHERE id_inscripcion = " . $id_inscripcion)[0]; 			
			if($envio)
			{
				$texto 		.= "\t- Envio a domicilio. Dirección: " . $envio['direccion'] . ". (3 euros) \n\n";								
				$total 		+= 3;
			}
			$texto .= "\nTOTAL " . $total . " euros";
			 
			 
			$texto .= "\n\n Únete al Club Baranain-Tsunza de Strava para que veamos tu carrera! https://www.strava.com/clubs/baranain-tsunza \n";
			
			echo nl2br($texto);
						
			enviarEmail($subject, $email, $texto);
		}
	}	
	
	if (isset($_POST['username']) && isset($_POST['userpass'])) { 
	
//		echo 'inscripcion fisica'.'</br>';
		inscripcionFisica();
	}
	else {
		//echo 'inscripcion tpv';
		inscripcion();
	}
?>
		    


</body> 
</html> 