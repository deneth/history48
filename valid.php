<?php
	header('Content-Type: text/html; charset=utf-8'); // pour avoir les caractères en utf8
	include ("config.inc.php");
	include ("lib/chrono_deb.php");
	
	include ("lib/connexion_pdo.php"); // connexion à la base de données en PDO

	$Type = $_POST["type"];
	
	switch ($Type)
	{
		case "girl":
			$IdGirl = $_POST["id"];
			$FirstName = $_POST["firstname"];
			$LastName = $_POST["lastname"];
			$Kanji = $_POST["kanji"];
			$NickName = $_POST["nickname"];
			$BirthDate = $_POST["birthdate"];
			$BirthPlace = $_POST["birthplace"];
			$Agency = $_POST["agency"];
			$Height  = $_POST["height"];
			$Blood = $_POST["blood"];
			//$Gen = $_POST["gen"]; // on ne modifie pas la generation
			
			echo $IdGirl." ".$FirstName." ".$LastName." ".$Kanji." ";
			echo $NickName." ".$BirthDate." ".$BirthPlace." ".$Agency." ";
			echo $Height." ".$Blood;
			
			$res = $bdd->query("UPDATE girls
								SET firstname = '".$FirstName."',
								lastname = '".$LastName."',
								kanji = '".$Kanji."',
								nickname = '".$NickName."',
								birthdate = '".$BirthDate."',
								birthplace = '".$BirthPlace."',
								agency = '".$Agency."',
								height = ".$Height.",
								blood = '".$Blood."'								
								WHERE id = ".$IdGirl);
			
			break;
		case "autre":
			break;
	}

	header("Location: index.php");
	exit;

?>