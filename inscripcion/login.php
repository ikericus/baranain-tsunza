<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>BT</title>
  <!-- Bootstrap core CSS-->
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom fonts for this template-->
  <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  <!-- Page level plugin CSS-->
  <link href="vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet">
  <!-- Custom styles for this template-->
  <link href="css/sb-admin.css" rel="stylesheet">


</head>

<body >
	
    <div class="container-fluid">

	<div class="card">
		<div class="card-body">
		
		<?php
			include 'Db.php';

			session_start();

			$db = new Db();  
			if ( isset ( $_POST [ 'buttonSalir' ] )){
				$_SESSION['loggedin'] = false;
			}			
			if ( isset ( $_POST [ 'buttonEntrar' ] )){
				
				$username = $db -> quote ($_POST['username']);
				$password = $db -> quote ($_POST['password']);
				
				$result = $db -> query("SELECT * FROM usuario where username = " . $username . "and activo = 1");
			
				$row_cnt = $result->num_rows;
							
				if($row_cnt > 0)
				{
					$row = $result->fetch_array(MYSQLI_NUM);
					$pass = $row[2];
					
					//echo $username . "<br />";
					//echo $password  . "<br />";
					//echo password_hash($password, PASSWORD_DEFAULT)  . "<br />";
					
					if (password_verify($password, $pass)) { 

						$_SESSION['loggedin'] = true;
						$_SESSION['username'] = $username;
						$_SESSION['start'] = time();
						$_SESSION['expire'] = $_SESSION['start'] + (5 * 60);
						
						$_SESSION['id_usuario'] = $db -> query("SELECT id FROM usuario WHERE username = ". $_SESSION['username']) -> fetch_object() -> id;
					 }
				}
			}
			if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
				
				echo "Has entrado como " . $_SESSION['username'];
				echo "<br />";
				
				//$query = "SELECT sum(precio) FROM inscripcion WHERE id_usuario = " . $_SESSION['id_usuario'] . " GROUP BY id_usuario";
				//$result = $db -> query($query);
				//
				//$row_cnt = $result->num_rows;
				//
				//if($row_cnt > 0)
				//{
				//	$row = $result->fetch_array(MYSQLI_NUM);
				//	echo "TOTAL metálico: " . $row[0]/100 . " euros.";
				//	echo "<br />";
				//}
				?>
				<br />
				<form method= "post" action="" />
					<div class="class="form-group">
						<input class="btn btn-primary btn-block" name= "buttonSalir" type= "submit" value= "Salir" /></td>
					</div>
				</form>
			<?php
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
</div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Page level plugin JavaScript-->
    <script src="vendor/chart.js/Chart.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin.min.js"></script>
    <!-- Custom scripts for this page-->
    <script src="js/sb-admin-datatables.min.js"></script>
    <script src="js/sb-admin-charts.min.js"></script>
  </div>
</body>

</html>
