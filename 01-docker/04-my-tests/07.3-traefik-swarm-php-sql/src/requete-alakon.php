<?php 
	
	// $pdo est défini dans php/_connexion.php, appelé dans templates/_header.php
	// Il s'agit de l'objet permttant la connexion à la base de données
	
	// http://php.net/manual/fr/pdo.prepared-statements.php
	// Préparation de la requête SQL
	// Je récupère tous les champs de la table 'order' pour les 3 premiers résultats
	$stmt = $pdo->prepare("
	
		SELECT 	*
		FROM 	my_awesome_table
		;

	");
	
	// Gestion des erreurs dans le catch
	try {

		// Execution de la reqête
		$stmt->execute();
		
		// Récupération du seul résultat
		$retourRequete = $stmt->fetch();
		
		// Affichage en loose, avec une bdd bien naze x)
		echo $retourRequete['test_my_awesome_table'];
	}
	// http://php.net/manual/fr/class.pdoexception.php
	catch (PDOException $e) {

		$errorInfo = $stmt->errorInfo();
		// var_dump($errorInfo);
		
		echo "	<div class=\"sqlError\">
					<fieldset>
						<legend>Erreur sql ¯\_(ツ)_/¯</legend>
						<div class=\"content\">" . $errorInfo[2] . "</div>
					</fieldset>
				</div>
		";

		return false;
	}
?>