<?php
	include 'Db.php';
	include 'carrito.php';	
	include 'apiRedsys.php';
	
    $db = new Db();
	$carrito = new Carrito();
	$inscripcion = new Inscripcion();
	
	$inscripcionOK = false;
	
	function inscripcion(){
		
		global $inscripcionOK;
		global $db;
		global $carrito;
		global $inscripcion;				

		$miObj = new RedsysAPI;			
		$config = parse_ini_file('../../config.ini'); 
	
		$datos 				= $_GET["Ds_MerchantParameters"];
		$signatureRecibida 	= $_GET["Ds_Signature"];
								
		$kc 		= $config['clave_tpv']; 
		$firma 		= $miObj->createMerchantSignatureNotif($kc,$datos);	
		
		if ($firma === $signatureRecibida){
			
			$order	= $miObj->getParameter("Ds_Order");

			$result = $db -> query("SELECT orden FROM inscripcion WHERE pagado = true and orden = " . $order);								
			$row_cnt = $result->num_rows;
			
			if($row_cnt > 0)
			{
				$inscripcionOK = true;
				$carrito->destroy();
				$inscripcion->destroy();	
			}
			else {
			    error_log("inscritos.php - Sin filas" . $row_cnt . PHP_EOL, 3, "../../errors.log");
			}
		}
		else {
			error_log("inscritos.php - Firma diferente" . $firma . " != " . $signatureRecibida . PHP_EOL, 3, "../../errors.log");
		}
	}	
	
	if(!empty($_GET)){ inscripcion(); }
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>BT 2025 - Inscritos</title>
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
		
		<?PHP if (!empty($_GET)){
				if ($inscripcionOK) {  ?>
		
			<div class="alert alert-success">
			  <strong>Bien!</strong> Inscripción realizada con éxito ¡GRACIAS! <br />
			  Te hemos enviado un correo electrónico con los datos a la dirección facilitada, <strong>comprueba que lo has recibido</strong> (puede estar en la bandeja de correo no deseado o spam!)
			</div>
				
		<?PHP } else { ?>
		
			<div class="alert alert-danger">
			  <strong>Jolines!</strong> Ha ocurrido algún error, contacta con nosotros!
			</div>
				
		<?PHP }} ?>
	
		<?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) { ?>
		
			<form id="realizarPago" method="post" >
				<input class="btn btn-success btn-block" type="submit" name="action" value="Descargar corredores" />
			</form>
		
		<?php }?>
      <div class="card mb-3">
        <div class="card-header">
          <i class="fa fa-table"></i> Inscritos</div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Apellido 1</th>
                  <th>Apellido 2</th>
                  <th>Sexo</th>
                  <th>Carrera</th>
                </tr>
              </thead>
              <tbody>
<?php
				$result = $db -> query("SELECT co.nombre, co.apellido1, co.apellido2, s.descripcion, ca.descripcion FROM corredor as co JOIN sexo as s on co.id_sexo = s.id JOIN inscripcion as i on co.id_inscripcion = i.id     JOIN carrera as ca on co.id_carrera = ca.id where i.pagado = true order by co.id desc");
								
				$row_cnt = $result->num_rows;
				$num = 1;
				if($row_cnt > 0)
				{
					for ($i = 1; $i <= $row_cnt; $i++) {
						$row = $result->fetch_array(MYSQLI_NUM);
						
						echo "<tr>
								<td>" . $num . "</td>
								<td>" . $row[0] . "</td>
								<td>" . $row[1] . "</td>
								<td>" . $row[2] . "</td>
								<td>" . $row[3] . "</td>
								<td>" . $row[4] . "</td>
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