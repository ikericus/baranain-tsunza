<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>BT 2024 - Timing</title>

  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  <link href="vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet">
  <link href="css/sb-admin.css" rel="stylesheet">
</head>

<?php 
 	include 'Db.php';
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		 // The request is using the POST method
		
		$db = new Db();
		
		$dorsal = $_POST['dorsal'];
		$ctime = time();
		
		$query = "INSERT INTO tiempos (timestamp, dorsal) VALUES (" . $ctime .", " . $dorsal .")";
				
		if($db -> query($query) === TRUE)
		{	
		}
	}
	else {
?>
	<body class="container">
		
		<h1>Timing BT 2024</h1>		
		
		<button id="scanButton">Scan</button>
		
		<script>
		  var ChromeSamples = {
			log: function() {
			  var line = Array.prototype.slice.call(arguments).map(function(argument) {
				return typeof argument === 'string' ? argument : JSON.stringify(argument);
			  }).join(' ');

			  document.querySelector('#log').textContent += line + '\n';
			},

			clearLog: function() {
			  document.querySelector('#log').textContent = '';
			},

			setStatus: function(status) {
			  document.querySelector('#status').textContent = status;
			},

			setContent: function(newContent) {
			  var content = document.querySelector('#content');
			  while(content.hasChildNodes()) {
				content.removeChild(content.lastChild);
			  }
			  content.appendChild(newContent);
			}
		  };
		</script>

		<div id="output" class="output">
		  <div id="content"></div>
		  <div id="status"></div>
		  <pre id="log"></pre>
		</div>

		<script>
		  if (/Chrome\/(\d+\.\d+.\d+.\d+)/.test(navigator.userAgent)){
			if (89 > parseInt(RegExp.$1)) {
			  ChromeSamples.setStatus('Warning! Keep in mind this sample has been tested with Chrome ' + 89 + '.');
			}
		  }
		</script>
		<script>
			log = ChromeSamples.log;

			if (!("NDEFReader" in window))
			  ChromeSamples.setStatus("Web NFC is not available. Use Chrome on Android.");
		</script>
		<script>
		   async function saveTime(dorsal) {

				let xhr = new XMLHttpRequest();

				xhr.onreadystatechange = function(){
					if (xhr.readyState === 4) {
						if (xhr.status === 200) {							
							log(`> Dorsal: ${dorsal}`);
						} else {
							log("Unexpected result: "+xhr.status);
						}
					}
				}
				xhr.open('POST', 'timing.php', true);
				// set the mime type
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				// encode the data from the form and send it.
				xhr.send('dorsal=' + dorsal);

			}
	
		scanButton.addEventListener("click", async () => {
		  log("Iniciando...");
		  const decoder = new TextDecoder();
		  var data;
		  try {
			const ndef = new NDEFReader();
			await ndef.scan();
			log("Listo para leer dorsales.");

			ndef.addEventListener("readingerror", () => {
			  log("Argh! No he podido leer el dorsal. Inténtalo de nuevo!");
			});

			ndef.addEventListener("reading", ({ message, serialNumber }) => {
			try
			  {
				data = decoder.decode(message.records[0].data);
				
				var dorsal = parseInt(data.split(':')[1]);
				
				saveTime(dorsal);
			  }
			  catch(error) {
				log("Argh! " + error);
			  }
			  
			});
		  } catch (error) {
			log("Argh! " + error);
		  }
		});

		</script>

	</body>
	  
		<script src="vendor/jquery/jquery.min.js"></script>
		<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
		<script src="vendor/datatables/jquery.dataTables.js"></script>
		<script src="vendor/datatables/dataTables.bootstrap4.js"></script>
		<script src="js/sb-admin.min.js"></script>
		<script src="js/sb-admin-datatables.min.js"></script>

	<?php } ?>
</html>