<?php
	$Team = array();
	$res2 = $bdd->query("SELECT id FROM team WHERE color IS NOT NULL;");
	while ($team = $res2->fetch())
	{
		$Team[$team['id']] = NULL; 
	}
	$res = $bdd->query("SELECT DISTINCT date_ev FROM ev_girl ORDER BY date_ev;");
	$rows = $res->fetchAll();

	foreach ($rows as $date_ev)
	{
		$res2 = $bdd->query("SELECT id FROM team WHERE color IS NOT NULL;");
		while ($team = $res2->fetch())
		{
			// la requette pour récupérer le nb de fille dans une team à une date
			$res3 = $bdd->query("SELECT L.id_girl, M.firstname, M.lastname, t1.team, t2.team AS 'double'
							FROM ev_girl L
							INNER JOIN girls M
								ON M.id = L.id_girl
							LEFT JOIN ev_girl S
								ON S.date_ev <= '$date_ev[0]'
								AND S.date_ev > L.date_ev
								AND S.id_girl = L.id_girl
								AND S.id_team = L.id_team
							LEFT JOIN team t1
								ON L.id_team=t1.id
							LEFT JOIN concurrent C
								ON L.id_girl=C.id_girl
								AND C.end_date IS NULL
							LEFT JOIN team t2
								ON C.team_double=t2.id
							WHERE S.id IS NULL
							AND L.date_ev <= '$date_ev[0]'
							AND L.id_type IN (2,3,7)
							AND L.id_team = $team[0]
							UNION
							SELECT C.id_girl, M.firstname, M.lastname, t1.team AS team, 
									t3.team AS 'double'
							FROM concurrent C
							INNER JOIN girls M
								ON C.id_girl= M.id
							LEFT JOIN team t1
								ON C.team_double=t1.id
							LEFT JOIN team t3
								ON C.team_orig =t3.id			
							WHERE C.team_double = $team[0]
							AND end_date IS NULL
							AND start_date <= '$date_ev[0]'
							ORDER BY firstname;");
			$rows3 = $res3->fetchAll();
			$NbGirl = $res3->rowCount();

			$Date = explode("-", $date_ev[0]); 
			$Team[$team['id']] .= "[gd(".$Date[0].",".$Date[1].",".$Date[2]."),".$NbGirl."],";
		}
	}

	$res->closeCursor(); // Termine le traitement de la requête
	$res2->closeCursor(); // Termine le traitement de la requête
	$res3->closeCursor(); // Termine le traitement de la requête
?>