<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Test PHP Mysql</title>
</head>
<body>
	<h1>Test PHP & MySQL</h1>

	<?php 
		require_once('_debug.php');
		require_once('_connexion.php');
		require_once('requete-alakon.php');
	?>
	<p><a href="http://localhost:8081/?server=db_tartopaum&username=bob" target="_blank">Administration SQL</a> / Login <code>bob</code> & pass <code>bobspw</code></p>
</body>
</html>