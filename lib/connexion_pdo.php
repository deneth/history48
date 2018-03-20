<?php
try
{
	$pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
	
	// connexion avec les infos du fichier de config
	$bdd = new PDO('mysql:host='.BDD_HOST.';port='.BDD_PORT.';dbname='.BDD_BDD.'', ''.BDD_USER.'', ''.BDD_PASS.'', $pdo_options);

	$bdd->query("SET NAMES 'utf8'"); // pour faire afficher les caracteres utf8
	$bdd->query("SET lc_time_names = 'fr_FR'"); // pour avoir les dates en francais
	
	}
	catch (Exception $e)
{
       die('Erreur : ' . $e->getMessage());
}	
?>