<?php
	include ("config.inc.php");
	include ("lib/connexion_pdo.php"); // connexion à la base de données en PDO
	
	$Type = $_POST["type"];
	
	switch ($Type)
	{
		case "event":
			$Id = $_POST["id"];
			$DateEv = $_POST["date"];
			$TypeEv = $_POST["typeev"];
			$Team = $_POST["team"];
			
			if ($DateEv <> '')
			{
				if ($TypeEv == 3)
				{ // si c'est un transfert il faut créer le départ de la team
					// *****************************************************
					// on commence par récupérer la team actuelle
					$res = $bdd->query("SELECT id_team
										FROM ev_girl
										WHERE (id_type=2 OR id_type=3 OR id_type=7)
										AND id_girl=193
										AND date_ev < '$DateEv'
										ORDER BY date_ev DESC
										LIMIT 0,1");
					$row = $res->fetchAll();
					//print_r($row);
					$OldTeam = $row[0]['id_team']; 
					// on écrit le départ de la team actuelle
					$res = $bdd->query("INSERT INTO ev_girl (id_girl, date_ev, id_type, id_team)
										VALUES ( $Id, '".$DateEv."', 9, $OldTeam)");
				}
				if ($TypeEv == 8)
				{ // si c'est une rétrogradation en RS
					// on commence par récupérer la team actuelle
					$res = $bdd->query("SELECT id_team
										FROM ev_girl
										WHERE (id_type=2 OR id_type=3 OR id_type=7)
										AND id_girl=193
										AND date_ev < '$DateEv'
										ORDER BY date_ev DESC
										LIMIT 0,1");
					$row = $res->fetchAll();
					//print_r($row);
					$OldTeam = $row[0]['id_team']; 
					// on écrit le départ de la team actuelle
					$res = $bdd->query("INSERT INTO ev_girl (id_girl, date_ev, id_type, id_team)
										VALUES ( $Id, '".$DateEv."', 10, $OldTeam)");	
				}
				
				echo $Id." ".$DateEv." ".$TypeEv." ".$Team;
				$Sql = "INSERT INTO ev_girl (id_girl, date_ev, id_type, id_team)
									VALUES ( $Id, '".$DateEv."', $TypeEv, $Team )";
				echo $Sql;
				$res = $bdd->query($Sql);
			}
			header("Location: index.php&page=descgirl&girl=$Id");
			exit;
			break;
		case "team":
			$Date = $_POST["date"];
			if ($Date <> "")
			{
				$Radio = $_POST["radio"];
				switch ($Radio)
				{
					case 1: // creation group
						$Group = $_POST["creategroup"];
						if ($Group <> "")
						{
							$res = $bdd->query("INSERT INTO group48 ( group48 )
												VALUES ( '".$Group."' )");
							$Id = $bdd->lastInsertId(); // l'id du groupe que l'on vient d'inserer dans la base
							// on écrit maintenant dans la table event
							$res = $bdd->query("INSERT INTO ev_group (date_ev, id_type, id_group48)
												VALUES ( '".$Date."', 1, $Id )");
						}
						break;
					case 2: // disband group
						$Group = $_POST["group"];
						$res = $bdd->query("INSERT INTO ev_group (date_ev, id_type, id_group48)
											VALUES ( '".$Date."', 6, $Group )");	
						break;
					case 3: // creation team
						$Team = $_POST["createteam"];
						if ($Team <> "")
						{
							if (!preg_match("#^[Tt]eam#", $Team))
							{
								$Team = "Team ".$Team;
							}
							$IdGroup = $_POST["team-group"];
							$res = $bdd->query("INSERT INTO team (team, id_group48)
												VALUES ( '".$Team."', $IdGroup)");
							$Id = $bdd->lastInsertId(); // l'id de la team que l'on vient d'inserer dans la base
							// on écrit maintenant dans la table event
							$res = $bdd->query("INSERT INTO ev_team (date_ev, id_type, id_team)
												VALUES ( '".$Date."', 1, $Id )");
						}
						break;
					case 4: // disband team
						$Team = $_POST["team"];
						$res = $bdd->query("INSERT INTO ev_team (date_ev, id_type, id_team)
											VALUES ( '".$Date."', 6, $Team )");						
						break;
				}
			}
			break;
		case "girl":
			$FirstName = $_POST["fname"];
			$LastName = $_POST["lname"];
			$Kanji = $_POST["kanji"];
			$NickName = $_POST["nick"];
			$BirthDay = $_POST["birthday"];
			$BirthPlace = $_POST["birthplace"];
			$Agency = $_POST["agency"];
			$Height = $_POST["height"];
			$Blood = $_POST["blood"];
			$Gen = $_POST["gen"];
			
			if ($Height=="")
			{
				$Height = 0;
			}
			
			$res = $bdd->query("INSERT INTO girls ( firstname, lastname, kanji,
								nickname, birthdate, birthplace, agency,
								height, blood)
								VALUES ( '".$FirstName."', '".$LastName."', '".$Kanji."',
										'".$NickName."', '".$BirthDay."', '".$BirthPlace."', 
										'".$Agency."', ".$Height.", '".$Blood."')");
			$Id = $bdd->lastInsertId(); // l'id du groupe que l'on vient d'inserer dans la base
			// on écrit maintenant dans la table generation
			$res = $bdd->query("INSERT INTO gen ( id_girl, id_gen)
								VALUES ( $Id, $Gen )");
			
			
			break;
		case "gen":
		
			break;
	}
	
	header("Location: index.php");
	exit;
	
?>