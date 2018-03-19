<?php
try
{
	$pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
	
	// pour MySQL
	//$bdd = new PDO('mysql:host=localhost;port=3306;dbname=cdismo', 'cdismo', 'cdismo01', $pdo_options);
	
	// pour MariaDB chez CDISMO
	//$bdd = new PDO('mysql:host=localhost;port=3307;dbname=akb48', 'cdismo', 'cdismo01', $pdo_options);
	
	// pour MariaDB sur kinsufi2
	$bdd = new PDO('mysql:host=188.165.254.73;port=3306;dbname=akb48', 'deneth', 'corwin48', $pdo_options);
	
	$bdd->query("SET NAMES 'utf8'"); // pour faire afficher les caracteres utf8
	$bdd->query("SET lc_time_names = 'fr_FR'"); // pour avoir les dates en francais
	
	}
	catch (Exception $e)
{
       die('Erreur : ' . $e->getMessage());
}	
?>