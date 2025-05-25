<?php
	include 'Db.php';
	include 'carrito.php';
	include 'apiRedsys.php';
	
	$inscripcion = new Inscripcion();
	$carrito = new Carrito();
    $db = new Db();   

	$now = time();

	if($now > $_SESSION['expire']) {
		$_SESSION['loggedin'] == false;
	}
	$ok = true;   
		
	function añadirEnvio($carrito = array()){

		$direccion 	= $_POST['direccion'];
		
		$articulo = array(
		 "id" 		=> time(),
		 "cantidad" => 1,
		 "precio" 	=> 5,
		 "tipo"		=> 4, // 1: corredor, 2: camiseta, 3: donacion, 4:  envio
		 "datos" 	=> array(
			"direccion"		=> $direccion)
		 );		 
		 
		$carrito->add($articulo);
	}
		
	function añadirDonacion($db, $carrito = array()){

		$id_donacion 	= $_POST['donacion'];
		
		$articulo = array(
		 "id" 		=> time(),
		 "cantidad" => 1,
		 "precio" 	=> $db -> query("SELECT precio FROM donacion where id ={$id_donacion}") -> fetch_object() -> precio,
		 "tipo"		=> 3, // 1: corredor, 2: camiseta, 3: donacion
		 "datos" 	=> array(
			"donacion"		=> $id_donacion)
		 );		 
		 
		$carrito->add($articulo);
	}
	
	function validarDatos(){			
		if (strlen($_POST['nombre']) == 0) 										return "Introduce nombre"; 
		else if (strlen($_POST['apellido1']) == 0)  							return "Introduce apellido 1";
		else if (strlen($_POST['apellido2']) == 0) 								return "Introduce apellido 2";
		else if (strlen($_POST['edad']) == 0 || !is_numeric($_POST['edad'])) 	return "Introduce una edad numérica";
		else if (strlen($_POST['distancia']) > 0)								return "Introduce distancia";
		else if (strlen($_POST['localidad']) > 0)								return "Introduce localidad";
		//and strlen($_POST['email']) > 0 and strlen($_POST['telf']) > 0 )
		//	{}	
	}
	
	function iniciarInscripcion($inscripcion){
		$inscripcion -> set_property('email', $_POST['email']);
		$inscripcion -> set_property('telf', $_POST['telf']);
	}
	
	function nuevoCorredor($db, $carrito = array()){		
	
		$name 	= $_POST['nombre'];
		$ap1 	= $_POST['apellido1'];
		$ap2 	= $_POST['apellido2'];
		$edad 	= $_POST['edad'];
		$dist	= $_POST['distancia'];
		$local 	= $_POST['localidad'];
		$sexo 	= $_POST['sexo'];
		$talla 	= $_POST['talla'];
		//$dni 	= $_POST['dni'];
		
		$articulo = array(
		 "id" 		=> time(),
		 "cantidad" => 1,
		 "precio" 	=> $db -> query("SELECT precio FROM carrera where id ={$dist}") -> fetch_object() -> precio,
		 "tipo"		=> 1, // 1: corredor, 2: camiseta
		 "datos" 	=> array( 
			"nombre" 	=> $name,
			"apellido1" => $ap1,
			"apellido2" => $ap2,
			"edad"		=> $edad,
			"distancia" => $dist,
			"localidad" => $local,
			"sexo"		=> $sexo,
			"dni"		=> '', //$dni,
			"talla"		=> $talla)
		 );			 
		 
		$carrito->add($articulo);
	}
	
	function printCorredor($db, $producto = array(), $numero){ 		
	
		 $id 		= $producto['id'];
		 $precio	= $producto['precio'];
		 $tipo		= $producto['tipo'];
		 $sexo		= $producto['datos']['sexo'];
		 $nombre	= $producto['datos']['nombre'];
		 $apellido1 = $producto['datos']['apellido1'];
		 $apellido2 = $producto['datos']['apellido2'];
		 
		 $sexo		= $db -> query("SELECT descripcion FROM sexo where id ={$producto['datos']['sexo']}") -> fetch_object() -> descripcion; 
		 $distancia	= $db -> query("SELECT descripcion FROM carrera where id ={$producto['datos']['distancia']}") -> fetch_object() -> descripcion;
		 
		 echo 	<<<EOD
				Corredor $numero ({$precio} €) <br />
				<strong>{$distancia} {$sexo} {$nombre} {$apellido1} {$apellido2}</strong> 
EOD;
		 if($precio == 1)
		 {
			$email		= $producto['datos']['email'];
			$edad		= $producto['datos']['edad'];		 
			$talla		= $db -> query("SELECT descripcion FROM talla where id ={$producto['datos']['talla']}") -> fetch_object() -> descripcion;
			
			echo 	<<<EOD
				Camiseta: {$talla}
				Email: {$email}
EOD;
		 }
		 echo 	<<<EOD
				<br />
EOD;
	}
	
	function printCamiseta($db, $producto = array(), $numero){
		
		 $id 		= $producto['id'];
		 $precio	= $producto['precio'];
		 $tipo		= $producto['tipo'];

		 $talla		= $db -> query("SELECT descripcion FROM talla where id ={$producto['datos']['talla']}") -> fetch_object() -> descripcion;
		 
		 echo 	<<<EOD
				Camiseta extra $numero ({$precio} €) <br />
				<strong>Talla {$talla}</strong> 
				<br />
EOD;
	}
	
	function printEnvio($producto = array()){
		
		 $id 		= $producto['id'];
		 $precio	= $producto['precio'];
		 $direccion	= $producto['datos']['direccion'];
		 
		 echo 	<<<EOD
				Envio a domicilio ({$precio} €) <br />
				<strong>{$direccion}</strong> 
				<br />
EOD;
	}
		
	function printDonacion($db, $producto = array(), $numero){
				 
		$descripcion	= $db -> query("SELECT descripcion FROM donacion where id ={$producto['datos']['donacion']}") -> fetch_object() -> descripcion;
		 
		echo 	<<<EOD
				Donación $numero ({$producto['precio']} €) <br />
				<strong>{$descripcion}</strong> 
				<br />
EOD;
	}
	
	function printCarrito($db, $carrito = array(), $inscripcion){  
		$carro = $carrito->get_content();
		if($carro)
		{
			 $corredor = 1;
			 $camiseta = 1;
			 $donacion = 1;
			 
			 echo "<form method='post'>";
			 
			 foreach($carro as $producto)
			 {
				echo "<li class='list-group-item'>";
				 
				echo "<button type='submit' name='eliminarArticulo' value='{$producto['unique_id']}' class='close' aria-label='Close'>";
				echo "<span aria-hidden='true'>&times;</span>";
				echo "</button>";
				switch ($producto['tipo']) {
					case 1:
						printCorredor($db, $producto, $corredor);
						$corredor++;
						break;
					case 2:
						printCamiseta($db, $producto, $camiseta);
						$camiseta++;
						break;
					case 3:
						printDonacion($db, $producto, $donacion);
						$donacion++;
						break;
					case 4:
						printEnvio($producto);
				 }

				 echo "</li>";
			 }
			 echo "<li class='list-group-item' style='text-align: right;'>";
			 echo $inscripcion->get_property('email'), "<br />";
			 echo $inscripcion->get_property('telf'), "<br />";
			 echo "<strong>TOTAL ", $carrito->precio_total(), " €</strong>";
			 echo "</li>";
			 echo "</ul>";
			 echo "<br />";
			 echo "<br />";
			 echo "</form>";
		}
		else
		{
			echo "El carro está vacio, añade corredores y/o donaciones !";
		}		
	}	
	
	function grabarInscripcion($db, $carrito = array(), $inscripcion, $order, $amount, $ok) {
		
		$email 	= $db -> quote ($inscripcion->get_property('email'));
		$telf 	= $db -> quote ($inscripcion->get_property('telf'));
		$id_usuario = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 0;
		
		$query = "INSERT INTO inscripcion (orden, precio, email, telefono, pagado, id_usuario) VALUES (" . $order .", " . $amount .", " . $email . ", " . $telf . ", false, " . $id_usuario .")";
				
		if($db -> query($query) === TRUE)
		{			
			$carro = $carrito->get_content();
			$insert_id = $db -> insert_id();	
			
			foreach($carro as $producto)
			{										
				$id_inscripcion = $db -> quote($insert_id);
				$id_talla = $db -> quote($producto['datos']['talla']);
										
				switch ($producto['tipo']) {
					case 1:
						$ap1 			= $db -> quote($producto['datos']['apellido1']);
						$ap2 			= $db -> quote($producto['datos']['apellido2']);
						$edad 			= $db -> quote($producto['datos']['edad']);
						$id_carrera 	= $db -> quote($producto['datos']['distancia']);
						$id_sexo 		= $db -> quote($producto['datos']['sexo']);
						$local 			= $db -> quote($producto['datos']['localidad']);
						$nombre 		= $db -> quote($producto['datos']['nombre']);
						
						//$dni 			= $db -> quote($producto['datos']['dni']);
									
						if(!$db -> query("INSERT INTO corredor (apellido1, apellido2, edad, id_inscripcion, id_sexo, localidad, nombre, id_carrera) VALUES (" .  $ap1.", " .  $ap2 .", " . $edad.", " . $id_inscripcion.", " . $id_sexo.", " . $local.", " . $nombre.", " . $id_carrera.")")){
							$ok = false;
							mail ( 'dev@baranain-tsunza.com', 'ERROR 253', $db -> error(), 'From: dev@baranain-tsunza.com' . PHP_EOL ) ;
						}												
						
						if($id_talla)
						{
							if(!$db -> query("INSERT INTO camiseta (id_inscripcion, id_talla) VALUES (" . $id_inscripcion . ", " . $id_talla . ")"))
							{
								$ok = false;
								mail ( 'dev@baranain-tsunza.com', 'ERROR 261', $db -> error(), 'From: dev@baranain-tsunza.com' . PHP_EOL ) ;								
							}
						}
						break;
					case 2:
						if(!$db -> query("INSERT INTO camiseta (id_inscripcion, id_talla) VALUES (" . $id_inscripcion . ", " . $id_talla . ")")){
							$ok = false;
							mail ( 'dev@baranain-tsunza.com', 'ERROR 268', $db -> error(), 'From: dev@baranain-tsunza.com' . PHP_EOL ) ;		
						}
						break;
					case 3:
						$id_donacion = $db -> quote($producto['datos']['donacion']);
						if(!$db -> query("INSERT INTO donativo (id_inscripcion, id_donacion) VALUES (" . $id_inscripcion . ", " . $id_donacion . ")")){
							$ok = false;
							mail ( 'dev@baranain-tsunza.com', 'ERROR 275', $db -> error(), 'From: dev@baranain-tsunza.com' . PHP_EOL ) ;								
						}
						break;
					case 4:
						$direccion = $db -> quote($producto['datos']['direccion']);
						if(!$db -> query("INSERT INTO envio (id_inscripcion, direccion) VALUES (" . $id_inscripcion . ", " . $direccion . ")")){
							$ok = false;
							mail ( 'dev@baranain-tsunza.com', 'ERROR 282', $db -> error(), 'From: dev@baranain-tsunza.com' . PHP_EOL ) ;								
						}
						break;
				}
			}
		}
		else
		{
			mail ( 'dev@baranain-tsunza.com', 'ERROR 290', $db -> error(), 'From: dev@baranain-tsunza.com' . PHP_EOL ) ;
		}
		$inscripcion -> set_property('ok', $ok);
	}
		
	if(isset($_POST['nombre'])){			   
	   nuevoCorredor($db, $carrito);
	}
	if (isset($_POST['action'])) {
		switch ($_POST['action']) {
			case 'Vaciar carro':
				$carrito->destroy();
				$inscripcion->destroy();				
				break;
			//case 'Añadir camiseta adicional':
			//	camisetaExtra($carrito);
			//	break;
			case 'Añadir donación':
				añadirDonacion($db, $carrito);
				break;
			case 'Iniciar inscripción':
				iniciarInscripcion($inscripcion);
				break;
			case 'Añadir envio':
				añadirEnvio($carrito);
				break;
    }}
	if (isset($_POST['eliminarArticulo'])) {
		
		$carrito->remove_producto($_POST['eliminarArticulo']);		
    }	
  	if($carrito)
	{				
		$miObj = new RedsysAPI;
		$config = parse_ini_file('../../config.ini'); 
		
		$url_tpv 			= $config['url_tpv'];							
		$version 			= "HMAC_SHA256_V1"; 
		$clave 				= $config['clave_tpv']; 
		$code 				= $config['comercio_tpv'];
		$terminal			= $config['terminal'];
		$order				= time();
		$amount				= $carrito->precio_total()*100;
		$currency 			= '978';
		$consumerlng 		= '001';
		$transactionType 	= '0';
		$urlMerchant 		= $config['url_not'];//'https://www.baranain-tsunza.com/pago.php';   // URL DEL COMERCIO CMS 
		$urlweb_ok 			= $config['url_ok'];//'https://www.baranain-tsunza.com/inscritos.php'; // URL OK
		$urlweb_ko 			= $config['url_ko'];//'https://www.baranain-tsunza.com/inscripcion.php'; // URL NOK

		$miObj->setParameter("DS_MERCHANT_AMOUNT",$amount);
		$miObj->setParameter("DS_MERCHANT_CURRENCY",$currency);
		$miObj->setParameter("DS_MERCHANT_ORDER",strval($order));
		$miObj->setParameter("DS_MERCHANT_MERCHANTCODE",$code);
		$miObj->setParameter("DS_MERCHANT_TERMINAL",$terminal);
		$miObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE",$transactionType);
		$miObj->setParameter("DS_MERCHANT_MERCHANTURL",$urlMerchant);
		$miObj->setParameter("DS_MERCHANT_URLOK",$urlweb_ok);      
		$miObj->setParameter("DS_MERCHANT_URLKO",$urlweb_ko);
		$miObj->setParameter("DS_MERCHANT_CONSUMERLANGUAGE",$consumerlng); 

		$params = $miObj->createMerchantParameters();
		$signature = $miObj->createMerchantSignature($clave);
		
		$inscripcion -> set_property('order', $order);
		$inscripcion -> set_property('amount', $amount);			
			
		grabarInscripcion($db, $carrito, $inscripcion, $order, $amount, false);
	}

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>BT2025 - Inscripción</title>
  <!-- Bootstrap core CSS-->
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom fonts for this template-->
  <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  <!-- Custom styles for this template-->
  <link href="css/sb-admin.css" rel="stylesheet">

</head>

<body class="bg-dark" id="page-top">
  
				
    <div class="container-fluid m-0 p-4">
	
		<?PHP if (isset($_GET["Ds_MerchantParameters"]) && $carrito->get_content()) { ?>
		
			<div class="alert alert-danger">
			  <strong>Jolines!</strong> Ha ocurrido algún error con el pago. Vuelva a intentarlo
			</div>
				
		<?PHP } 
		if ( isset ( $_POST [ 'buttonSalir' ] )){
				$_SESSION['loggedin'] = false;
			  }
		if (isset($_GET["login"])) 
			{
		?>
			<!-- Start LOGIN card -->
			<div class="card mb-3">
				<div class="card-header">Login</div>
				<div class="card-body">
							
			<?php

				session_start();

				$db = new Db();  
		
				if ( isset ( $_POST [ 'buttonEntrar' ] )){
											
					$result = $db -> query("SELECT * FROM usuario WHERE username = {$db -> quote($_POST['username'])} and activo = 1");
					
					//debug_to_console($result);
					
					$row_cnt = $result -> num_rows;
					
					if($row_cnt > 0)
					{
						$row = $result->fetch_array(MYSQLI_NUM);
						$hashDB = $row[2];
						
						//echo $_POST['password']  . "<br />";
						//echo $hashDB  . "<br />";
					    
						if (password_verify($_POST['password'], $hashDB)) { 
							
							$_SESSION['loggedin'] 	= true;
							$_SESSION['username'] 	= $_POST['username'];						
							$_SESSION['userpass'] 	= $_POST['password'];
							$_SESSION['start'] 		= time();
							$_SESSION['expire'] 	= $_SESSION['start'] + (5 * 60);							
							
							$_SESSION['id_usuario'] = $db -> query("SELECT id FROM usuario WHERE username = ". $db -> quote($_SESSION['username'])) -> fetch_object() -> id;
							
							echo "Usuario y contraseña correctos! <a href='inscripcion.php'>Iniciar inscripciones</a> </br>";
							
						 }
					}
					else
					{
						echo 'Usuario y contraseña incorrectos! </br>';
					}
				}
				if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) 
				{
					echo "Has iniciado sesión " . $_SESSION['username'];
				}
				else{
				?>
					<form method= "post" action="" />
						<div class="class="form-group">
							<div class="form-group">
								<input class="form-control" type= "text" name="username" placeholder="Usuario" required>
								<div class="invalid-feedback"> Introduce usuario </div>
							</div>
							<div class="form-group">
								<input class="form-control" type= "password" name="password"  placeholder="Contraseña" required>
								<div class="invalid-feedback"> Introduce contraseña</div>
							</div>
							<input class="btn btn-primary btn-block" name= "buttonEntrar" type= "submit" value= "Entrar" /></td>
						</div>
					</form>		

				<?php } ?>
				</div>
			</div>
			<!-- Ends LOGIN card -->
		<?PHP } ?>

		<?PHP if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) { 
		
		?>
			<!-- Start LOGGED card -->
			<div class="card mb-3">
				<div class="card-header">Logged</div>
				<div class="card-body">
					<?php 
						echo "Logeado como " . $_SESSION['username']; 
						echo "<br />";
						echo "<br />";					
					
						//echo "USERNAME: ".$_SESSION['username'];
						$id_usuario = $db -> query("SELECT id FROM usuario WHERE username = ". $db -> quote($_SESSION['username'])) -> fetch_object() -> id;

						$total = $db -> query("SELECT COALESCE(sum(precio), 0) as total FROM inscripcion WHERE id_usuario = " . $id_usuario . " AND pagado = 1 GROUP BY id_usuario ")-> fetch_object() -> total;

						
						echo "TOTAL metálico: " . $total/100 . " euros.";
						echo "<br />";		
						echo "<br />";		
					?>
					<form method= "post" action="" />
						<div class="class="form-group">
							<input class="btn btn-primary btn-block" name= "buttonSalir" type= "submit" value= "Salir" /></td>
						</div>
					</form>
				</div>
			</div>
		<?php } ?>
  	  
	  <?PHP if(!($inscripcion -> get_property('email'))) {  ?>
	  <div class="card">
		<div class="card-header">Nueva inscripción</div>
		<div class="card-body">
			Necesitamos esta información para mandarte la confirmación de la inscripción y cualquier tipo de información de la carrera. <br /> <br />
            <form method="post" class="needs-validation" novalidate>
				<div class="form-group" >
					<input class="form-control" name="email" type="email" aria-describedby="emailHelp" placeholder="Correo electrónico" required>
					<div class="invalid-feedback"> Introduce un email válido</div>
				</div>
				<div class="form-group" >
					<input class="form-control" name="telf" type="tel" pattern="[67][0-9]{8}" aria-describedby="emailHelp" placeholder="Teléfono" required>
					<div class="invalid-feedback"> Introduce telefono</div>
				</div>
				  <div class="form-group">
				<div class="form-group">
					<div class="form-check">					
					  &emsp;&emsp;<input class="form-check-input" type="checkbox" id="invalidCheck" required> 
					  <label class="form-check-label" for="invalidCheck" style="padding: 0px">
						Acepto las  <button type="button" class="btn btn-link" data-toggle="modal" data-target="#exampleModal" style="padding:0px;margin-bottom: 4px;"> condiciones </button>
					  </label>
					  <div class="invalid-feedback"> Tienes que aceptar las condiciones</div>
					</div>
				</div>
				
			  <input class="btn btn-primary btn-block" type="submit" value="Iniciar inscripción" name="action">
			</form>
		</div>
	  </div>	
	  <!-- Modal -->
		<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		  <div class="modal-dialog" role="document">
			<div class="modal-content">
			  <div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Condiciones</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">&times;</span>
				</button>
			  </div>
			  <div class="modal-body">				
				La Organización de la Barañain-Tsunza está especialmente sensibilizada en la protección de los datos de los corredores y colaboradores a los que se accede a través de su web. Mediante la presente Política de Privacidad (en adelante , la Política) la Organización de la Barañain-Tsunza informa a los usuarios del sitio web www.baranain-tsunza.com del tratamiento y usos a los que se someten los datos de carácter personal que se recaban en la web, con el fin de que decidan, libre y voluntariamente, si desean facilitar la información solicitada. <br />
				<br />
				La Organización de la Barañain-Tsunza se reserva la facultad de modificar esta Política con el objeto de adaptarla a novedades legislativas, criterios jurisprudenciales, prácticas del sector, o intereses de la entidad. Cualquier modificación en la misma será anunciada con la debida antelación, a fin de que tengas perfecto conocimiento de su contenido.<br />
				<br />
				Ciertos servicios prestados en el portal pueden contener condiciones particulares con previsiones en materia de protección de Datos Personales. De ellos puedes informarte en los correspondientes apartados.<br />
				<br />
				TITULARIDAD DEL TRATAMIENTO<br />
				En dichos supuestos, los datos recabados serán incorporados a ficheros titularidad de la Organización de la Barañain-Tsunza, debidamente inscritos en el Registro General de Protección de Datos.<br />
				<br />
				USOS Y FINALIDADES<br />
				La finalidad de la recogida y tratamiento de los datos personales es la gestión, prestación y personalización de los servicios y contenidos del mismo que el usuario utilice.<br />
				<br />
				COMUNICACIÓN DE LOS DATOS<br />
				Los datos recabados a través de la web sólo serán cedidos en aquellos casos en que expresamente se informe de ello al usuario.<br />
				<br />
				ACTUALIZACIÓN DE LOS DATOS<br />
				Con el fin de que los datos obrantes en nuestras bases de datos siempre correspondan a tu situación real deberás mantenerlos actualizados, bien actualizándolos tú directamente en los caso en que ello sea posible bien comunicándolo al departamento correspondiente.<br />
				<br />
				UTILIZACIÓN DE COOKIES<br />
				Con el objeto de proteger la intimidad de los usuarios de su página web la Organización de La Barañain-Tsunza no emplea cookies cuando los mismos navegan por la misma.<br />
				<br />
				SEGURIDAD DE LOS DATOS<br />
				La Organización de la Barañain-Tsunza ha adoptado en su sistema de información las medidas técnicas y organizativas legalmente requeridas, a fin de garantizar la seguridad y confidencialidad de los datos almacenados, evitando así, en la medida de lo posible, su alteración, pérdida, tratamiento o acceso no autorizado.<br />
				<br />
				DERECHOS DE LOS USUARIOS<br />
				En todo caso podrás acceder a tus datos, rectificarlos, cancelarlos y en su caso, oponerte a su tratamiento mediante solicitud acompañada de una fotocopia de tu D.N.I., remitida a la siguiente dirección de correo electrónico: info@baranain-tsunza.com<br />

			  </div>
			</div>
		  </div>
		</div>

	   <?PHP } ?>
	  
	  <?PHP if($inscripcion -> get_property('email')) { ?>
		<div class="card-deck">	  
	  
			<div class="card">
		  <div class="card-header">Nuevo corredor</div>
		  <div class="card-body">
            <form method="post" action="inscripcion.php" class="needs-validation" novalidate>
	  			<div class="form-group">
				<select class="form-control distancia" name="distancia" aria-describedby="emailHelp" required>
				  <option value="" disabled selected>Seleccciona distancia</option>
					<?php
						$db = new Db();    
						
						$result = $db -> query("SELECT id, descripcion_larga FROM carrera WHERE disponible = 1");
										
						$row_cnt = $result->num_rows;
						
						var_dump($result->num_rows);
						
						if($result->num_rows > 0)
						{
							for ($i = 1; $i <= $result->num_rows; $i++) {
								$row = $result->fetch_array(MYSQLI_NUM);
								
								echo "<option value='", $row[0], "'>", $row[1], " (", $db -> query("SELECT precio FROM carrera where id ={$row[0]}") -> fetch_object() -> precio, " €) </option>";
							}
						}
						else
						{
						    echo "<option>lalal </option>";
						}
					?>				  
				</select>
				<div class="invalid-feedback"> Escoge distancia </div>
			  </div>	
			  <div class="form-group">
				<select class="form-control" name="sexo" aria-describedby="emailHelp" required>
				  <option value="" disabled selected>Seleccciona sexo</option>
					<?php
						$db = new Db();    
						$result = $db -> query("SELECT * FROM sexo");
										
						$row_cnt = $result->num_rows;
						
						if($row_cnt > 0)
						{
							for ($i = 1; $i <= $row_cnt; $i++) {
								$row = $result->fetch_array(MYSQLI_NUM);
								
								echo "<option value='" . $row[0] . "'>" . $row[1] . "</option>";
							}
						}
					?>
				</select>
				<div class="invalid-feedback"> Escoge sexo</div>
			  </div>
			  <div class="form-group"  >
				<select class="form-control " name="talla" aria-describedby="emailHelp" required>
				  <option value="" disabled selected>Seleccciona camiseta</option>
					<?php
						
						$db = new Db();    
						$result = $db -> query("SELECT * FROM talla t where disponible = 1 AND total > (SELECT count(1) FROM inscripcion i JOIN camiseta c ON i.id = c.id_inscripcion WHERE pagado = 1 AND c.id_talla = t.id) order by id");
										
						$row_cnt = $result->num_rows;
						
						if($row_cnt > 0)
						{
							for ($i = 1; $i <= $row_cnt; $i++) {
								$row = $result->fetch_array(MYSQLI_NUM);
								
								echo "<option value='". $row[0] . "'>" . $row[1] . "</option>";
							}
						}
					?>
				</select>
				<div class="invalid-feedback"> Escoge camiseta</div>
			  </div>			  
			  <div class="form-group">
				<input class="form-control" name="nombre" aria-describedby="emailHelp" placeholder="Nombre" required>
				<div class="invalid-feedback"> Introduce nombre </div>
			  </div>
			  <div class="form-group">
				<input class="form-control" name="apellido1" aria-describedby="emailHelp" placeholder="Primer apellido" required>
				<div class="invalid-feedback"> Introduce primero apellido </div>
			  </div>
			  <div class="form-group">
				<input class="form-control" name="apellido2" aria-describedby="emailHelp" placeholder="Segundo apellido" required>
				<div class="invalid-feedback"> Introduce segundo apellido </div>
			  </div>				
			  <!--<div class="form-group dni" style="display:none" >
				<input class="form-control dnir" name="dni" pattern="[0-9]{8}[A-Za-z]|[XYZxyz][0-9]{7}[A-Za-z]" aria-describedby="emailHelp" placeholder="DNI/NIE" required>
				<div class="invalid-feedback"> Introduce DNI/NIE</div>
			  </div>	-->		 
			  <div class="form-group">
				<input class="form-control" name="edad" pattern="[0-9]{1,2}" aria-describedby="emailHelp" placeholder="Edad" required>
				<div class="invalid-feedback"> Introduce edad</div>
			  </div>
			  <div class="form-group">
				<input class="form-control" name="localidad" aria-describedby="emailHelp" placeholder="Localidad" required>
				<div class="invalid-feedback"> Introduce localidad</div>
			  </div>
			  <input class="btn btn-primary btn-block" type="submit" value="Añadir corredor">
			</form>
        </div>
	    </div>
		
		
		<div class="card" style="border:0px;">

			<div class="card" style="margin: 0px">
			  <div class="card-header">Envio a domicilio</div>
			  <div class="card-body">
				<form method="post" class="needs-validation" novalidate>

					<div class="form-group">
						<div class="form-check">					
						&emsp;&emsp;<input class="form-check-input" type="checkbox" id="envioCheckId" > 
							<label class="form-check-label" for="envioCheckId" style="padding: 0px">
								Quiero recibir la camiseta en casa. (+5€)
							</label>
						</div>
						<span> SÓLO PARA RESIDENTES FUERA DE NAVARRA </span>
					</div>			  
					<div class="form-group" >
						<input class="form-control direccionr" name="direccion"  aria-describedby="emailHelp" placeholder="Dirección" disabled>
						<div class="invalid-feedback"> Introduce dirección</div>
					</div>
					<input class="btn btn-primary btn-block" type="submit" value="Añadir envio" name="action">
				</form>
				</div>
			</div>
			<br />
			<?php /*
			 <div class="card" style="margin: 0px">
			  <div class="card-header">Donación</div>
			  <div class="card-body">
				<form method="post" class="needs-validation" novalidate>

				  <div class="form-group">
					<select class="form-control" name="donacion" aria-describedby="emailHelp" required>
					  <option value="" disabled selected>Seleccciona donación</option>
						<?php
							$db = new Db();    
							$result = $db -> query("SELECT id, descripcion FROM donacion");
											
							$row_cnt = $result->num_rows;
							
							if($row_cnt > 0)
							{
								for ($i = 1; $i <= $row_cnt; $i++) {
									$row = $result->fetch_array(MYSQLI_NUM);
									
									echo "<option value='", $row[0], "'>", $row[1], "</option>";
								}
							}
						?>
					</select>
					<div class="invalid-feedback"> Escoge donación</div>
				  </div>			  

				  <input class="btn btn-primary btn-block" type="submit" value="Añadir donación" name="action">
				</form>
				</div>
			</div>
			*/ ?>
	    </div>
		
		
		<div class="card">
		  <div class="card-header">Carrito</div>
		  <div class="card-body d-flex flex-column">
			<?php printCarrito($db, $carrito, $inscripcion); ?>
			<?php if($carrito->get_content()) { ?>
			
				<form method="post" >
					<input class="btn btn-danger btn-block" type="submit" value="Vaciar carro" name="action">
				</form>		
				<br />
				
					<?php if($carrito->precio_total() > 0) {
					
						//echo $_SESSION['loggedin'];
						//echo $_SESSION['username'];
						//echo $_SESSION['userpass'];
						//$_SESSION['start'] = time();
						//$_SESSION['expire'] = $_SESSION['start'] + (5 * 60);
				
					if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) { ?>
				
					<form id="realizarPago" method="post" action="pago.php">
						<input type='hidden' name='order' 	value='<?php echo $order ?>'/> 
						<input type='hidden' name='username' 	value=<?php echo $_SESSION['username'] ?> /> 
						<input type='hidden' name='userpass' 	value=<?php echo $_SESSION['userpass'] ?> /> 
						<input class="btn btn-success btn-block" type="submit" name="submitMetalico" value="Finalizar inscripción con PAGO EN METÁLICO" />
					</form>
				
					<?php } else {
				
					echo "<form id='realizarPago' action='" . $url_tpv . "' method='post' target='_self' > 
							<input type='hidden' name='Ds_SignatureVersion' 	value='" . $version . "'/> 
							<input type='hidden' name='Ds_MerchantParameters' 	value='" . $params . "'/>
							<input type='hidden' name='Ds_Signature' 			value='" . $signature . "'/>
							<input type='hidden' name='Ds_Signature' 			value='" . $signature . "'/>
							
							<input class='btn btn-success btn-block' type='submit' name='submitPayment' value='PAGO SEGURO CON TARJETA'/>
						</form>";
				
					} 
			} } ?>
		  </div>
		</div>
		</div>
	  <?PHP } ?>
		</div>


      </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Page level plugin JavaScript-->
    <script src="vendor/chart.js/Chart.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin.min.js"></script>
    <!-- Custom scripts for this page-->
    <script src="js/sb-admin-charts.min.js"></script>
	<script>
		$("#envioCheckId").change(function() {

		if(this.checked)
			{
				 $(".direccionr").prop('required', true);
				 $(".direccionr").prop('disabled', false);
					
			}
			else
			{
				 $(".direccionr").prop('required', false);
				 $(".direccionr").prop('disabled', true);
			}
		});
		// $(".distancia").change(function() {

			// if(this.value == 1 || this.value == 2 || this.value == 3)
			// {
				 // $(".dni").show();	
				 // $(".camiseta").show();
				 			
				 // $(".dnir").prop('required', true);
				 // $(".camisetar").prop('required', true);
					
			// }
			// else
			// {
				 // $(".dni").hide();
				 // $(".camiseta").hide();	
				 
				 // $(".dnir").prop('required', false);
				 // $(".camisetar").prop('required', false);
			// }
		// });
	</script>
	<script>
	// Example starter JavaScript for disabling form submissions if there are invalid fields
	(function() {
	  'use strict';
	  window.addEventListener('load', function() {
		// Fetch all the forms we want to apply custom Bootstrap validation styles to
		var forms = document.getElementsByClassName('needs-validation');
		// Loop over them and prevent submission
		var validation = Array.prototype.filter.call(forms, function(form) {
		  form.addEventListener('submit', function(event) {
			if (form.checkValidity() === false) {
			  event.preventDefault();
			  event.stopPropagation();
			}
			form.classList.add('was-validated');
		  }, false);
		});
	  }, false);
	})();
	</script>
  </div>
  

</body>

</html>
