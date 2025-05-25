<?php
	include 'Db.php';
	include 'carrito.php';	
	include 'apiRedsys.php';
	
    $db = new Db();
	$carrito = new Carrito();
	$inscripcion = new Inscripcion();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Camisetas</title>
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
                  <th>Talla</th>
                  <th>Vendidas</th>
                  <th>Pedidas</th>
                </tr>
              </thead>
              <tbody>
<?php	
				$result = $db -> query("SELECT t.descripcion, count(1), t.total FROM camiseta as c JOIN talla as t on c.id_talla = t.id JOIN inscripcion as i on c.id_inscripcion = i.id	WHERE i.pagado = 1 GROUP BY t.descripcion ORDER BY c.id_talla asc");
				
				$row_cnt = $result->num_rows;
				$num = 1;
				if($row_cnt > 0)
				{
					for ($i = 1; $i <= $row_cnt; $i++) {
						$row = $result->fetch_array(MYSQLI_NUM);
						
						echo "<tr>
								<td>" . $row[0] . "</td>
								<td>" . $row[1] . "</td>
								<td>" . $row[2] . "</td>";
								
						echo "</tr>";
						
						$num++;
					}
				}
?>	
              </tbody>
            </table>
			
			 <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
				  <th>Nº orden</th>
				  <th>Email</th>
                  <th>Telefono</th>   
				  <th>Precio</th>               
				  <th>Tallas</th>
                  <th>Dirección</th>
				  <th>Donativo</th>
                </tr>
              </thead>
              <tbody>
<?php	
				$result = $db -> query("SELECT orden, email, telefono,  precio, camisetas, direcciones, donativos
from
(
	SELECT 	i.id,
			i.orden, 
			i.precio, 
			i.email, 
			i.telefono, 
			GROUP_CONCAT(t.descripcion_corta SEPARATOR ', ') camisetas	
	FROM inscripcion i 
	left join camiseta c on i.id = c.id_inscripcion 
	left join talla t on c.id_talla = t.id 
	WHERE i.pagado = 1 and i.precio > 0 
	group by i.id
) uno
left join
(	SELECT 	i.id,
			GROUP_CONCAT(don.descripcion SEPARATOR ', ') donativos	
	FROM inscripcion i 
	left join donativo d on i.id = d.id_inscripcion 
	left join donacion don on d.id_donacion = don.id 
	
	WHERE i.pagado = 1 and i.precio > 0

	group by i.id
) dos
ON uno.id = dos.id
left join
(	SELECT 	i.id,			
			GROUP_CONCAT(e.direccion SEPARATOR ', ') direcciones
	FROM inscripcion i 
	left join envio e on i.id = e.id_inscripcion 
	
	WHERE i.pagado = 1 and i.precio > 0

	group by i.id
) tres
ON uno.id = tres.id
order by direcciones desc, email");
								
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
								<td>" . $row[6] . "</td>";
								
						echo "</tr>";
						
						$num++;
					}
				}
?>	
              </tbody>
            </table>
          </div>
		  



  </div>
</body>

</html>