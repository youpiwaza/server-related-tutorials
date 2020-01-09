<?php
	
	// $host 	= "localhost";
	$host 	= "db_tartopaum";
	// $user 	= "root";
	// $pass 	= "pizza";
	$user 	= "bob";
	$pass 	= "bobspw";
	$dbname = "my_awesome_db";

	try {
		
		// Création du PDO, si besoin (singleton)
		// http://php.net/manual/fr/pdo.connections.php
		$pdo = new PDO('mysql:host=' . $host . ';dbname='.$dbname , $user, $pass);
		
		// Configuration de la gestion des erreurs
		// http://php.net/manual/fr/pdo.error-handling.php
		$pdo->setAttribute(PDO::ATTR_ERRMODE, 				PDO::ERRMODE_EXCEPTION);
		
		// Par défaut, on retourne un tableau associatif
		// http://php.net/manual/fr/pdo.setattribute.php
		// http://php.net/manual/fr/pdostatement.fetch.php
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, 	PDO::FETCH_ASSOC);

		// Test du SQL avant envoi au serveur
		// http://php.net/manual/fr/pdo.errorinfo.php
		// $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false); 

		// ^ Non
		// https://stackoverflow.com/questions/10113562/pdo-mysql-use-pdoattr-emulate-prepares-or-not

		// Bonne gestion de l'encodage (caractères spéciaux, accentués, kanjis, etc.)
		// http://magix-cjquery.com/post/2011/12/22/Prise-en-charge-encodage-utf8-avec-PHP-et-PDO-MYSQL
		$pdo->exec("SET CHARACTER SET utf8");

		// echo "connexion réussie";
	}

	// Erreur lors de la connexion à la base de données
	catch (PDOException $e) {
		
		echo "Erreur lors de la connexion a la base de données :<br />";
		echo "<pre>" . $e->getMessage() . "</pre><br/>";
		echo "<h2>Details</h2>";
		echo "<pre>";
		var_dump($e);
		echo "</pre><br/>";

		die();
	}
	
	echo '<p>Connexion à la BDD okay</p>';

?>