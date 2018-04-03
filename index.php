<?php
	include ("config.inc.php");
	include ("lib/chrono_deb.php");
	include ("lib/connexion_pdo.php"); // connexion à la base de données en PDO
	
	if (!isset($_GET["page"]))
	{ // il n'y a pas de page
		$Page = "main";
	} else
	{ // il y a une variable page
		$Page = $_GET["page"];
	}
	//echo $Page;
	switch ($Page)
	{
		// *********************************************
		// *********************************************
		case "main": // affichage de la page principale
			// nb de filles au total
			$res = $bdd->query("SELECT DISTINCT id_girl FROM v_teams");
			$rows = $res->fetchAll();
			$NbGirl = $res->rowCount();
			
			// nb de filles par groupe
			$res = $bdd->query("SELECT v.id_team, g.id, g.group48, COUNT(v.id_girl) AS nb
								FROM v_teams v
								LEFT JOIN team t
								ON v.id_team = t.id
								LEFT JOIN group48 g
								ON t.id_group48 = g.id
								GROUP BY g.id
								ORDER BY g.id, v.id_team");
			$rows = $res->fetchAll();
			$NbGirlByGroup = "";
			foreach ($rows as $val)
			{
				$NbGirlByGroup .= $val["group48"]." : ".$val["nb"]." members<br />";
			}

			// nb de filles par team
			$res = $bdd->query("SELECT v.id_team, g.id, g.group48, v.team, COUNT(v.id_girl) AS nb
								FROM v_teams v
								LEFT JOIN team t
								ON v.id_team = t.id
								LEFT JOIN group48 g
								ON t.id_group48 = g.id
								GROUP BY v.id_team
								ORDER BY g.id, v.id_team");
			$rows = $res->fetchAll();
			$NbGirlByTeam = "";
			foreach ($rows as $val)
			{
				$NbGirlByTeam .= $val["team"]." : ".$val["nb"]." members<br />";
			}
			
			// last events
			$res = $bdd->query("SELECT e.date_ev, g.firstname, g.lastname, te.`event`, t.team
								FROM ev_girl e
								LEFT JOIN girls g
									ON e.id_girl = g.id
								LEFT JOIN type_event te
									ON e.id_type = te.id
								LEFT JOIN team t
									ON e.id_team = t.id
								WHERE e.id_type NOT IN (9,10,13)
								ORDER BY e.date_ev DESC, e.id_girl
								LIMIT 0,10");
			$rows = $res->fetchAll();
			$LastEvent = "";
			foreach ($rows as $val)
			{
				$LastEvent .= $val["date_ev"]." - ".$val["firstname"]." ".$val["lastname"]." ";
				$LastEvent .= $val["event"]." ".$val["team"]."<br />";
			}
			$html = $NbGirl." active members.<br />".PHP_EOL;
			$html .= "<br />".PHP_EOL;
			$html .= $NbGirlByGroup;
			$html .= "<br /><br />".PHP_EOL;
			$html .= $NbGirlByTeam;
			$html .= "<br />".PHP_EOL;
			$html .= "Last Event<br />".PHP_EOL;
			$html .= $LastEvent;
			break;
		// *******************************************************
		// *******************************************************	
		case "showteam": // affichage de la composition d'une team
			if (isset($_GET["team"])) {
				$NumTeam = $_GET["team"];
			} else
			{
				$NumTeam = "";
			}
		
			if ($NumTeam == 0)
			{
				$NumTeam = "";
			}
			//echo $NumTeam;
			if (isset($_GET["date"]))
			{
				$DateAff = $_GET["date"];
			} else
			{
				$Today = new DateTime();
				$DateAff = $Today->format("Y-m-d");
			}
			
			$res = $bdd->query("SELECT t.id, t.team, g.group48
								FROM team t
								LEFT JOIN group48 g
								ON t.id_group48 = g.id
								ORDER BY g.id,id");
			$rows = $res->fetchAll();
			$res->closeCursor(); // Termine le traitement de la requête
			
			$htmlSelTeam = "<option value=0>All</option>";
			foreach ($rows as $val)
			{
				if ($val['id']==$NumTeam)
				{
					$htmlSelTeam .= "<option value=".$val['id']." selected>".$val['group48']." ".$val['team']."</option>";
				} else
				{
					$htmlSelTeam .= "<option value=".$val['id'].">".$val['group48']." ".$val['team']."</option>";
				}
			}
			
			$AffTeam = "";
			if ($NumTeam<>"")
			{
				//$Today = new DateTime();
				//$DateAff = $Today->format("Y-m-d");
			
				// pour avoir la composition d'une team à une certaine date
				$res = $bdd->query("SELECT L.id_girl, M.firstname, M.lastname, t1.team, t2.team AS 'double'
									FROM ev_girl L
									INNER JOIN girls M
										ON M.id = L.id_girl
									LEFT JOIN ev_girl S
										ON S.date_ev <= '$DateAff'
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
									AND L.date_ev <= '$DateAff'
									AND L.id_type IN (2,3,7)
									AND L.id_team = $NumTeam
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
									WHERE C.team_double = $NumTeam
									AND end_date IS NULL
									AND start_date <= '$DateAff'
									ORDER BY firstname;");  // id_girl;");
				/*					
				// on utilise la vue SQL pour avoir la composition d'une team à la date du jour
				$res = $bdd->query("SELECT id_girl, firstname, lastname, team, `double`
									FROM v_teams
									WHERE id_team = ".$NumTeam);
									*/
				$rows = $res->fetchAll();
				$NbGirl = $res->rowCount();
				$res->closeCursor(); // Termine le traitement de la requête
				
				if ($NbGirl > 0 )
				{
					$AffTeam = "<strong>".$rows[0]['team']."</strong> ($DateAff) ";
				}
				$AffTeam .= $NbGirl." members<br>";
				foreach ($rows as $val)
				{
					$AffTeam .= "<a href='index.php?page=descgirl&girl=".$val['id_girl']."'>";
					$AffTeam .= $val['firstname']." ".$val['lastname']."</a>";
					if ($val["double"]<>NULL)
					{
						$AffTeam .= " ( concurrent to ".$val["double"].")";
					}
					$AffTeam .= "<br>";
				}
			
				if ($NumTeam <> "")
				{
					$AffTransfered = "<h3>Transfered</h3>";
					// pour avoir la composition d'une team à une certaine date
					$res = $bdd->query("SELECT id_girl, date_ev, firstname, lastname FROM ev_girl
										LEFT JOIN girls
										ON girls.id = ev_girl.id_girl
										WHERE id_type = 9
										AND id_team = $NumTeam");
					$rows = $res->fetchAll();
					$NbGirl = $res->rowCount();
					$res->closeCursor(); // Termine le traitement de la requête
					if ($NbGirl >0 )
					{
						foreach ($rows as $val)
						{
							$AffTransfered .= "<a href='index.php?page=descgirl&girl=".$val['id_girl']."'>";
							$AffTransfered .= $val['firstname']." ".$val['lastname']."</a>";
							$AffTransfered .= " (".$val['date_ev'].")";
							$AffTransfered .= "<br>";
						}
						
					}
		
					$AffGraduated = "<h3>Graduated</h3>";
					// pour avoir la composition d'une team à une certaine date
					$res = $bdd->query("SELECT id_girl, date_ev, firstname, lastname FROM ev_girl
										LEFT JOIN girls
										ON girls.id = ev_girl.id_girl
										WHERE id_type = 4
										AND id_team = $NumTeam");
					$rows = $res->fetchAll();
					$NbGirl = $res->rowCount();
					$res->closeCursor(); // Termine le traitement de la requête
					if ($NbGirl >0 )
					{
						foreach ($rows as $val)
						{
							$AffGraduated .= "<a href='index.php?page=descgirl&girl=".$val['id_girl']."'>";
							$AffGraduated .= $val['firstname']." ".$val['lastname']."</a>";
							$AffGraduated .= " (".$val['date_ev'].")";
							$AffGraduated .= "<br>";
						}
						
					}
				} else
				{
					$AffTransfered = "";
					$AffGraduated = "";
				}
			} else
			{
				$AffTransfered = "";
				$AffGraduated = "";
			}
			$html = "<form method=\"get\" action=\"index.php\">".PHP_EOL;
			$html .= "<input type=\"hidden\" name=\"page\" value=\"showteam\" />".PHP_EOL;
			$html .= "<select name=\"team\">".PHP_EOL;
			$html .= $htmlSelTeam;
			$html .= "</select>".PHP_EOL;
			$html .= "<input type=\"date\" name=\"date\" value=\"".$DateAff."\" />".PHP_EOL;
			$html .= "<input type=\"submit\" value=\"Submit\">".PHP_EOL;
			$html .= "</form>".PHP_EOL;
			$html .= $AffTeam;
			$html .= "<br>".PHP_EOL;
			$html .= $AffTransfered;
			$html .= "<br>".PHP_EOL;
			$html .= $AffGraduated;

			break;
		// ***************************************************
		// ***************************************************
		case "histogroup": // affiche l'historique des groupes
		$res = $bdd->query("SELECT eg.date_ev, t.event, g.group48 AS team, g.group48
					FROM ev_group eg
					LEFT JOIN type_event t
					ON eg.id_type = t.id
					LEFT JOIN group48 g
					ON eg.id_group48 = g.id
					UNION
					SELECT ev.date_ev, te.`event`, t.team, g.group48
					FROM ev_team ev
					LEFT JOIN team t
					ON ev.id_team = t.id
					LEFT JOIN type_event te
					ON ev.id_type = te.id
					LEFT JOIN group48 g
					ON t.id_group48 = g.id
					ORDER BY date_ev");
			$rows = $res->fetchAll();
			$res->closeCursor(); // Termine le traitement de la requête

			$Histo = "";
			foreach ($rows as $val)
			{
			$Histo .= $val["date_ev"]." ".$val["event"]." ".$val["team"]." (".$val["group48"].")<br />";
			}

			$res = $bdd->query("SELECT id, team FROM team");
			$rows = $res->fetchAll();
			$SelTeam = "";
			foreach ($rows as $val)
			{
			$SelTeam .= "<option value=".$val["id"].">".$val["team"]."</option>";
			}

			$res = $bdd->query("SELECT id, group48 FROM group48");
			$rows = $res->fetchAll();
			$SelGroup = "";
			foreach ($rows as $val)
			{
			$SelGroup .= "<option value=".$val["id"].">".$val["group48"]."</option>";
			}

			$html = $Histo;
			$html .= "<p>Add a event</p>".PHP_EOL;
			$html .= "<form method=\"POST\" action=\"add.php\">".PHP_EOL;
			$html .= "<input type=\"hidden\" name=\"type\" value=\"team\" />".PHP_EOL;
			$html .= "<label for=\"date\">Date : </label><input type=\"date\" id=\"date\" name=\"date\" />".PHP_EOL;
			$html .= "<br>".PHP_EOL;
			$html .= "<input type=\"radio\" id=\"creategroup\" name=\"radio\" value=1><label for=\"creategroup\">Creation Group</label>".PHP_EOL;
			$html .= "<input type=\"text\" name=\"creategroup\" />".PHP_EOL;
			$html .= "<br>".PHP_EOL;
			$html .= "<input type=\"radio\" id=\"disbandgroup\" name=\"radio\" value=2><label for=\"disbandgroup\">Disband Group</label>".PHP_EOL;
			$html .= "<select name=\"group\">".PHP_EOL;
			$html .= $SelGroup;
			$html .= "</select>".PHP_EOL;
			$html .= "<br>".PHP_EOL;
			$html .= "<input type=\"radio\" id=\"createteam\" name=\"radio\" value=3 checked><label for=\"createteam\">Creation Team</label>".PHP_EOL;
			$html .= "<input type=\"text\" name=\"createteam\" /> in".PHP_EOL;
			$html .= "<select name=\"team-group\">".PHP_EOL;
			$html .= $SelGroup;
			$html .= "</select>".PHP_EOL;
			$html .= "<br>".PHP_EOL;
			$html .= "<input type=\"radio\" id=\"disbandteam\" name=\"radio\" value=4><label for=\"disbandteam\">Disband Team</label>".PHP_EOL;
			$html .= "<select name=\"team\">".PHP_EOL;
			$html .= $SelTeam;
			$html .= "</select>".PHP_EOL;
			$html .= "<input type=\"submit\" value=\"Submit\"  />".PHP_EOL;
			$html .= "</form>".PHP_EOL;
			break;
		// **********************************************************	
		// **********************************************************
		case "histogen": // affichage de l'historique des générations
					$res = $bdd->query("SELECT gen.id_girl, gene.type, g.firstname, g.lastname
					FROM gen
					LEFT JOIN girls g
					ON gen.id_girl = g.id
					LEFT JOIN generation gene
					ON gen.id_gen = gene.id						
					ORDER BY gene.id_group, gene.rank, g.firstname");
			$rows = $res->fetchAll();
			$res->closeCursor(); // Termine le traitement de la requête

			$Histo = "";
			foreach ($rows as $val)
			{
			$Histo .= $val["type"]." generation - ";
			$Histo .= "<a href=\"index.php?page=descgirl&girl=".$val["id_girl"]."\">".$val["firstname"]." ".$val["lastname"]."</a><br />";
			}
			$res = $bdd->query("SELECT id, group48 FROM group48");
			$rows = $res->fetchAll();
			$sel_group = "";
			foreach ($rows as $val)
			{
			$sel_group .= "<option value=".$val["id"].">".$val["group48"]."</option>";
			}
			$html = $Histo;
			$html .= "<form method=\"POST\" action=\"add.php\">".PHP_EOL;
			$html .= "<input type=\"hidden\" name=\"type\" value=\"gen\" />".PHP_EOL;
			$html .= "Group : <select name=\"group\">".PHP_EOL;
			$html .= $sel_group;
			$html .= "</select>".PHP_EOL;
			$html .= "<input type=\"submit\" value=\"Add a Gen\"  />".PHP_EOL;
			$html .= "</form>".PHP_EOL;

			break;
		// *********************************************	
		// *********************************************
		case "concurrent": // affiche la page des kennin
					$res = $bdd->query("SELECT c.id_girl, g.firstname, g.lastname, c.start_date, 
					CONCAT(G1.group48,' ',t.team) AS pos, c.team_double, c.team_orig,
					CONCAT(G2.group48,' ',t2.team) AS orig
					FROM concurrent c
					LEFT JOIN girls g
						ON c.id_girl = g.id
					LEFT JOIN team t
						ON c.team_double = t.id
					LEFT JOIN group48 G1
						ON t.id_group48 = G1.id
					LEFT JOIN team t2
						ON c.team_orig = t2.id
					LEFT JOIN group48 G2
						ON t2.id_group48 = G2.id
					WHERE end_date IS NULL
					ORDER BY c.start_date;");
			$rows = $res->fetchAll();
			$PresentPosition = "";
			foreach ($rows as $val)
			{
			$PresentPosition .= "<a href='index.php?page=descgirl&girl=".$val["id_girl"]."'>".$val["firstname"]." ".$val["lastname"]."</a>";
			$PresentPosition .= " (".$val["orig"].")";
			$PresentPosition .= " since ".$val["start_date"]." in ".$val["pos"]."<br />";
			}

			$res = $bdd->query("SELECT c.id_girl, g.firstname, g.lastname, c.start_date, c.end_date,  
					CONCAT(G1.group48,' ',t.team) AS pos, c.team_double, c.team_orig,
					CONCAT(G2.group48,' ',t2.team) AS orig
					FROM concurrent c
					LEFT JOIN girls g
						ON c.id_girl = g.id
					LEFT JOIN team t
						ON c.team_double = t.id
					LEFT JOIN group48 G1
						ON t.id_group48 = G1.id
					LEFT JOIN team t2
						ON c.team_orig = t2.id
					LEFT JOIN group48 G2
						ON t2.id_group48 = G2.id
					WHERE end_date IS NOT NULL
					ORDER BY c.start_date;");
			$rows = $res->fetchAll();
			$PastPosition = "";
			foreach ($rows as $val)
			{
			$PastPosition .= "<a href='index.php?page=descgirl&girl=".$val["id_girl"]."'>";
			$PastPosition .= $val["firstname"]." ".$val["lastname"]."</a>";
			$PastPosition .= " (".$val["orig"].") ";
			$PastPosition .= " from ".$val["start_date"]." to ".$val["end_date"];
			$PastPosition .= " was in ".$val["pos"]."<br>";
			}
			$html = "<p>Present concurrent position:</p>".PHP_EOL;
			$html .= $PresentPosition; 
			$html .= "<p>Past concurrent position:</p>".PHP_EOL;
			$html .= $PastPosition;

			break;
		// ***********************************************	
		// ***********************************************
		case "sousenkyo": // affiche la page des sousenkyo
			if (isset($_GET["year"])) {
				$Year = $_GET["year"];
			} else
			{
				$res = $bdd->query("SELECT id FROM sousenkyo
									ORDER BY date_sousenkyo DESC
									LIMIT 0,1");
				$row = $res->fetch();
				
				$Year[] = $row['id'];
				// $Year[] = 7; // *********************************
			}
		
			$res = $bdd->query("SELECT id, YEAR(date_sousenkyo) AS date, design 
								FROM sousenkyo
								ORDER BY date");
			$rows = $res->fetchAll();
			$AffForm = "";
			foreach ($rows as $val)
			{
				if (in_array($val["id"], $Year))
				{
					$AffForm .= "<input type='checkbox' name='year[]' value='".$val["id"]."' checked>".$val["date"]." ";
				} else
				{
					$AffForm .= "<input type='checkbox' name='year[]' value='".$val["id"]."'>".$val["date"]." ";
				}
			}
			
			$AffSousenkyo = "";
			foreach ($Year as $v)
			{
				$res = $bdd->query("SELECT e.rank_girl, g.firstname, g.lastname, e.nbvote, e.id_girl, s.design, YEAR(s.date_sousenkyo) AS date
									FROM election e
									LEFT JOIN girls g
										ON e.id_girl = g.id
									LEFT JOIN sousenkyo s
										ON e.id_sousenkyo = s.id
									WHERE e.id_sousenkyo = ".$v." 
									ORDER BY e.rank_girl"); 
				$rows = $res->fetchAll();
				
				$AffSousenkyo .= $rows[0]["date"]." - ".$rows[0]["design"]."<br>";
				foreach ($rows as $val)
				{
					$AffSousenkyo .= "Place:".$val['rank_girl']." : ";
					$AffSousenkyo .= "<a href='index.php?page=descgirl&girl=".$val['id_girl']."'>";
					$AffSousenkyo .= $val['firstname']." ".$val['lastname']."</a>"; 
					$AffSousenkyo .= " ".$val['nbvote']." points<br>";
				}
				$AffSousenkyo .= "<br><br>";
			}
				
			$res->closeCursor(); // Termine le traitement de la requête

			$html = "<form method=\"GET\" action=\"index.php\">".PHP_EOL;
			$html .= "<input type=\"hidden\" name=\"page\" value=\"sousenkyo\" />".PHP_EOL;
			$html .= $AffForm;
			$html .= "<input type=\"submit\" value=\"Submit\">".PHP_EOL;
			$html .= "</form>".PHP_EOL;
			$html .= $AffSousenkyo;

			break;
		// ****************************************
		// ****************************************	
		case "stage": // affiche la page des stages
			if (isset($_GET["girl"])) {
				$IdGirl = $_GET["girl"];
			} else
			{
				$IdGirl = "";
			}
		
			if ($IdGirl <> "")
			{ // une fille est spécifiée, on affiche que ses stages
				$res = $bdd->query("SELECT COUNT(id_girl) AS nb FROM stage_members WHERE id_girl = $IdGirl");
				$rows = $res->fetchAll();
				$NbStage = $rows[0];
		
				$res = $bdd->query("SELECT firstname, lastname, kanji
									FROM girls
									WHERE id=$IdGirl"); 
				$rows = $res->fetchAll();
				$res->closeCursor(); // Termine le traitement de la requête
				
				$infoGirl = $rows[0]['firstname']." ".$rows[0]['lastname']." ".$rows[0]['kanji'];
				$infoGirl .= "<br>";
				$infoGirl .= $NbStage['nb']." stages";
		
				$res = $bdd->query("SELECT sm.id_stage, ss.date_stage, ss.time_stage, ss.commentary,
											t.team, st.num, st.name
									FROM stage_members sm
									LEFT JOIN stage_schedule ss
									ON sm.id_stage = ss.id
									LEFT JOIN stage st
									ON ss.id_stage = st.id
									LEFT JOIN team t
									ON st.team = t.id
									WHERE sm.id_girl = $IdGirl");
				$rows = $res->fetchAll();
				$res->closeCursor(); // Termine le traitement de la requête
				$AffStage = "";
				foreach ($rows as $val)
				{
					$AffStage .= $val['date_stage']." ".$val['time_stage']." ".$val['team'].$val['num']." ".$val['name'];
					$AffStage .= "<br>";
				}
		
		
			} else
			{ // pas de fille spécifiée, on affiche tous les stages
				$infoGirl = "";
		
				$AffStage = "";
			}
			$html = $infoGirl;
			$html .= "<br>".PHP_EOL;
			$html .= $AffStage;

			break;
		// *********************************
		// *********************************	
		case "addgirl": // ajouter une fille 
			$res = $bdd->query("SELECT id, type FROM generation");
			$rows = $res->fetchAll();
			$sel_gen = "";
			foreach ($rows as $val)
			{
				$sel_gen .= "<option value=".$val["id"].">".$val["type"]."</option>";
			}

			$html = "<p>Add a girl</p>".PHP_EOL;
			$html .= "<form method=\"POST\" action=\"add.php\">".PHP_EOL;
			$html .= "<input type=\"hidden\" name=\"type\" value=\"girl\" />".PHP_EOL;
			$html .= "FirstName : <input type=\"text\" name=\"fname\" />".PHP_EOL;
			$html .= "LastName : <input type=\"text\" name=\"lname\" />".PHP_EOL;
			$html .= "<br>".PHP_EOL;
			$html .= "Kanji : <input type=\"text\" name=\"kanji\" />".PHP_EOL;
			$html .= "NickName : <input type=\"text\" name=\"nick\" />".PHP_EOL;
			$html .= "<br>".PHP_EOL;
			$html .= "BirthDay : <input type=\"date\" name=\"birthday\" />".PHP_EOL;
			$html .= "BirthPlace : <input type=\"text\" name=\"birthplace\" />".PHP_EOL;
			$html .= "<br>".PHP_EOL;
			$html .= "Agency : <input type=\"text\" name=\"agency\" />".PHP_EOL;
			$html .= "Height : <input type=\"text\" name=\"height\" />".PHP_EOL;
			$html .= "Blood : <input type=\"text\" name=\"blood\" />".PHP_EOL;
			$html .= "<br>".PHP_EOL;
			$html .= "Generation : <select name=\"gen\">".PHP_EOL;
			$html .= $sel_gen;
			$html .= "</select>".PHP_EOL;
			$html .= "<input type=\"submit\" value=\"Add\"  />".PHP_EOL;
			$html .= "</form>".PHP_EOL;

			break;
		// ************************************
		// ************************************	
		case "graph": // affichage du graphique
			
			$WidthBars = 10000;

			include("count_girl.php");

			//print_r($Team).PHP_EOL.PHP_EOL;

			$Data = "";
			$res = $bdd->query("SELECT id, team, color FROM team WHERE color IS NOT NULL;");
			$rows = $res->fetchAll();
			foreach ($rows as $val)
			{

				$Data .= "{
							label: \"".$val['team']."\",
							data: [ ".$Team[$val['id']]." ],
							color: \"#".$val['color']."\" },";
			}
			$res->closeCursor(); // Termine le traitement de la requête

		//print_r($Data);

			$CSS = "<link rel=\"stylesheet\" href=\"css/graph.css\" type=\"text/css\" />";

			$JS = "<script language=\"javascript\" type=\"text/javascript\" src=\"js/flot/jquery.js\"></script>
<script language=\"javascript\" type=\"text/javascript\" src=\"js/flot/jquery.flot.js\"></script>
<script language=\"javascript\" type=\"text/javascript\" src=\"js/flot/jquery.flot.stack.js\"></script>
<script language=\"javascript\" type=\"text/javascript\" src=\"js/flot/jquery.flot.time.js\"></script>
<script language=\"javascript\" type=\"text/javascript\" src=\"js/flot/jquery.flot.axislabels.js\"></script>
<script type=\"text/javascript\">
	function zeroFill( number, width )
	{
	  width -= number.toString().length;
	  if ( width > 0 )
	  {
		return new Array( width + (/\./.test( number ) ? 2 : 1) ).join( '0' ) + number;
	  }
	  return number + \"\"; // always return a string
	}

	$(function() {

		var datasets = [ ".$Data." ];
		

		var stack = 0,
			bars = true,
			lines = false,
			steps = false;
		
		var options = {
												 			
				series: {
					stack: stack,
					lines: {
						show: lines,
						fill: true
						//steps: false
					},
					bars: {
						show: bars,
						barWidth: 24 * 60 * 60 * ".$WidthBars.",
						align: \"center\"
					}
				},
				xaxis: {
					mode: \"time\",
					//tickSize: [3, \"day\"],
					//tickLength: 10,
					color: \"black\",
					axisLabel: \"Date\",
					axisLabelUseCanvas: true,
					axisLabelFontSizePixels: 12,
					axisLabelFontFamily: 'Verdana, Arial',
					axisLabelPadding: 10,
					timeformat: \"%Y-%m-%d\"
				},
				yaxis: {
					color: \"black\",
					axisLabel: \"Girls's Number\",
					axisLabelUseCanvas: true,
					axisLabelFontSizePixels: 12,
					axisLabelFontFamily: 'Verdana, Arial',
					axisLabelPadding: 3
					//tickFormatter: function (v, axis) {
						//  return $.formatNumber(v, { format: \"#,###\", locale: \"us\" });
					//}
				},
				grid: {
					hoverable: true,
					borderWidth: 2,       
					backgroundColor: { colors: [\"#EDF5FF\", \"#ffffff\"] }
				},
				legend: {
					show: true,
					//labelFormatter: null or (fn: string, series object -> string)
					//labelBoxBorderColor: color
					noColumns: 2, //noColumns: number
					position: \"nw\" //position: \"ne\" or \"nw\" or \"se\" or \"sw\"
					//margin: number of pixels or [x margin, y margin]
					//backgroundColor: null or color
					//backgroundOpacity: number between 0 and 1
					//container: null or jQuery object/DOM element/jQuery expression
					//sorted: null/false, true, \"ascending\", \"descending\", \"reverse\", or a comparator
				}


			}
		
		$(\"<div id='tooltip'></div>\").css({
			position: \"absolute\",
			display: \"none\",
			//border: \"1px solid #f55\",
			\"border-radius\": \"5px\",
			padding: \"2px\",
			\"background-color\": \"#fff\",
			\"color\": \"black\",
			opacity: 0.80
		}).appendTo(\"body\");
		
		$(\"#placeholder\").bind(\"plothover\", function (event, pos, item) {

			if (item) {
				var x = item.datapoint[0]; //.toFixed(2),
				var	y = item.datapoint[1]; //.toFixed(2);
				//var did = item.dataIndex;
				//var tot = item.datapoint;
				var did = item.series.data[item.dataIndex][1];
				var color = item.series.color;
				
				var d = new Date(x);
				var day = d.getFullYear() + \"-\" + zeroFill((d.getMonth()+1),2) + \"-\" + zeroFill(d.getDate(),2); //new Date(x).getDate();

				$(\"#tooltip\").html(item.series.label + \": \" + did + \" members<br>at \" + day)
					.css({top: item.pageY+5, left: item.pageX+5, border: \"2px solid \"+color})
					.fadeIn(200);
			} else {
				$(\"#tooltip\").hide();
			}
		});
		
		
		// insert checkboxes 
		var choiceContainer = $(\"#choices\");
		$.each(datasets, function(key, val) {
			choiceContainer.append(\"<br/><input type='checkbox' name='\" + key +
				\"' checked='checked' id='id\" + key + \"'></input>\" +
				\"<label for='id\" + key + \"'>\"
				+ val.label + \"</label>\");
		});

		choiceContainer.find(\"input\").click(plotWithOptions);

		function plotWithOptions() {
			var data = [];

			choiceContainer.find(\"input:checked\").each(function () {
				var key = $(this).attr(\"name\");
				if (key && datasets[key]) {
					data.push(datasets[key]);
				}
			});
			
			$.plot(\"#placeholder\", data, options  );
			//$(\"#placeholder\").UseTooltip();
		}

		function gd(year, month, day) {
			return new Date(year, month - 1, day).getTime();
		}

		plotWithOptions();

	});
// http://www.jqueryflottutorial.com/how-to-make-jquery-flot-stacked-chart.html
// http://www.saltycrane.com/blog/2010/03/jquery-flot-stacked-bar-chart-example/
// http://www.benknowscode.com/2013/02/graphing-with-flot-controlling-series_9976.html
</script>";

			$html = "<div class=\"demo-container\">".PHP_EOL;
			$html .= "<div id=\"placeholder\" class=\"demo-placeholder\" style=\"float:left; width:90%;\"></div>".PHP_EOL;
			$html .= "<p id=\"choices\" style=\"float:right; width:10%; color:black;\"></p>".PHP_EOL;
			$html .= "</div>".PHP_EOL;

			break;
		// ************************************************
		// ************************************************	
		case "descgirl": // affichage des infos d'une fille
			if (isset($_GET["girl"])) {
				$IdGirl = $_GET["girl"];
			} else
			{
				$IdGirl = "";
			}
		
			$res = $bdd->query("SELECT firstname, lastname, birthdate, kanji, generation.rank, G.group48
								FROM girls
								LEFT JOIN gen
								ON girls.id = gen.id_girl
								LEFT JOIN generation
								ON gen.id_gen = generation.id
								LEFT JOIN group48 G
								ON generation.id_group = G.id
								WHERE girls.id=$IdGirl
								ORDER BY rank DESC
								LIMIT 0,1"); // pas forcement la meilleur solution pour avoir la gen
			$rows = $res->fetchAll();
			$res->closeCursor(); // Termine le traitement de la requête
			
			$infoGirl = $rows[0]['firstname']." ".$rows[0]['lastname']." ".$rows[0]['kanji'];
			$infoGirl .= " (".$rows[0]['group48']." gen.:".$rows[0]['rank'].")"; 
			$infoGirl .= "<br>".$rows[0]['birthdate'];
			$Birth = new DateTime($rows[0]['birthdate']);
			$Today = new DateTime();
			$Age = $Birth->diff($Today);
			$infoGirl .= " (".$Age->format("%y years old)");
			
			$res = $bdd->query("SELECT e.date_ev, t.`event`, team.team
								FROM ev_girl e
								LEFT JOIN type_event t
									ON e.id_type = t.id
								LEFT JOIN team
									ON e.id_team = team.id
								WHERE e.id_girl=$IdGirl
									AND e.id_type<>9 AND e.id_type<>10 AND id_type<>13
								UNION
								SELECT C.start_date, 'Concurrent to', t1.team
								FROM concurrent C
								LEFT JOIN team t1
									ON C.team_double = t1.id
								WHERE C.id_girl=$IdGirl
									AND C.end_date IS NULL
								UNION
								SELECT C.start_date, 'Concurrent to', t2.team
								FROM concurrent C
								LEFT JOIN team t2
									ON C.team_double=t2.id
								WHERE C.id_girl=$IdGirl
									AND C.end_date IS NOT NULL
								UNION 
								SELECT C.end_date, 'End concurrent to', t3.team
								FROM concurrent C
								LEFT JOIN team t3
									ON C.team_double = t3.id
								WHERE C.id_girl=$IdGirl
									AND C.end_date IS NOT NULL
								ORDER BY date_ev;"); // a rajouter la position concurrent
			$rows = $res->fetchAll();
			$res->closeCursor(); // Termine le traitement de la requête
			
			$histoGirl = "";
			foreach ($rows as $val)
			{
				$histoGirl .= "<br>".$val['date_ev']." ".$val['event']." ".$val['team'];
			}
			
			$res = $bdd->query("SELECT id, event FROM type_event");
			$rows = $res->fetchAll();
			$sel_event = "";
			foreach ($rows as $val)
			{
					$sel_event .= "<option value=".$val["id"].">".$val["event"]."</option>";
			}
			$res = $bdd->query("SELECT id, team FROM team");
			$rows = $res->fetchAll();
			$sel_team = "";
			foreach ($rows as $val)
			{
				$sel_team .= "<option value=".$val["id"].">".$val["team"]."</option>";
			}
			
			$res = $bdd->query("SELECT s.nb, e.rank_girl, e.nbvote, YEAR(s.date_sousenkyo) AS date, s.design
								FROM sousenkyo s
								LEFT JOIN (SELECT s.nb, e.rank_girl, e.nbvote, s.date_sousenkyo, s.id AS id
												FROM election e
												JOIN sousenkyo s
												ON e.id_sousenkyo = s.id
												AND e.id_girl IS NOT NULL
												WHERE id_girl = ".$IdGirl.") e
									ON s.id = e.id
								ORDER BY s.date_sousenkyo");
			$rows = $res->fetchAll();
			$Election = "";
			foreach ($rows as $val)
			{
				$Election .= $val["nb"]." Senbatsu Election ".$val["date"]." - ";
				if ($val["rank_girl"] <> NULL)
				{
					$Election .= "#".$val["rank_girl"]." (".$val["nbvote"]." points)<br>";
				} else
				{
					$Election .= "Not ranked<br>";
				}
			}
		
			$res = $bdd->query("SELECT COUNT(id_girl) AS nb FROM stage_members WHERE id_girl = $IdGirl");
			$rows = $res->fetchAll();
			$NbStage = $rows[0];

			$html = $infoGirl;
			$html .= "<br>".PHP_EOL;
			$html .= "<a href=\"index.php?page=editgirl&girl=".$IdGirl."\">Edit</a>".PHP_EOL;
			$html .= "<br>".PHP_EOL;
			$html .= $histoGirl;
			$html .= "<br><br>".PHP_EOL;
			$html .= "AKB48 Senbatsu Elections".PHP_EOL;
			$html .= "<br>".PHP_EOL;
			$html .= $Election;
			$html .= "<br>".PHP_EOL;
			$html .= "Has participed at <a href=\"index.php?page=stage&girl=$IdGirl\">".$NbStage['nb']." stages</a>".PHP_EOL;
	
			$html .= "<p>Add a event</p>".PHP_EOL;
			$html .= "<form method=\"POST\" action=\"add.php\">".PHP_EOL;
			$html .= "<input type=\"hidden\" name=\"type\" value=\"event\" />".PHP_EOL;
			$html .= "<input type=\"hidden\" name=\"id\" value=".$IdGirl." />".PHP_EOL;
			$html .= "Date : <input type=\"date\" name=\"date\" />".PHP_EOL;
			$html .= "Type : <select name=\"typeev\">".PHP_EOL;
			$html .= $sel_event; 
			$html .= "</select>".PHP_EOL;
			$html .= "Team : <select name=\"team\">".PHP_EOL;
			$html .= $sel_team;
			$html .= "</select>".PHP_EOL;
			$html .= "<input type=\"submit\" value=\"Add\"  />".PHP_EOL;
			$html .= "</form>".PHP_EOL;

			break;
		// *****************************************
		// *****************************************
		case "editgirl": // modification d'une fille
			$IdGirl = $_GET["girl"];
		
			$res = $bdd->query("SELECT firstname, lastname, kanji, nickname,
								birthdate, birthplace, agency, height, blood
								FROM girls WHERE id=".$IdGirl);
			$rows = $res->fetchAll();

			$html = "<form method=\"POST\" action=\"valid.php\" accept-charset=\"utf-8\">".PHP_EOL;
			$html .= "<input type=\"hidden\" name=\"type\" value=\"girl\" />".PHP_EOL;
			$html .= "<input type=\"hidden\" name=\"id\" value=\"".$IdGirl."\" />".PHP_EOL;
			$html .= "<label for=\"fname\">Firstname : </label>";
			$html .= "<input type=\"text\" id=\"fname\" name=\"firstname\" value=\"".$rows[0]['firstname']."\" />".PHP_EOL;
			$html .= "<label for=\"lname\">Lastname : </label>";
			$html .= "<input type=\"text\" id=\"lname\" name=\"lastname\" value=\"".$rows[0]['lastname']."\" />".PHP_EOL;
			$html .= "<br>".PHP_EOL;
			$html .= "<label for=\"kanji\">Kanji : </label>";
			$html .= "<input type=\"text\" id=\"kanji\" name=\"kanji\" value=\"".$rows[0]['kanji']."\" />".PHP_EOL;
			$html .= "<label for=\"nick\">Nickname : </label>";
			$html .= "<input type=\"text\" id=\"nick\" name=\"nickname\" value=\"".$rows[0]['nickname']."\" />".PHP_EOL;
			$html .= "<br>".PHP_EOL;
			$html .= "<label for=\"date\">Birthdate : </label>";
			$html .= "<input type=\"date\" id=\"date\" name=\"birthdate\" value=\"".$rows[0]['birthdate']."\" />".PHP_EOL;
			$html .= "<label for=\"place\">Birthplace : </label>";
			$html .= "<input type=\"text\" id=\"place\" name=\"birthplace\" value=\"".$rows[0]['birthplace']."\" />".PHP_EOL;
			$html .= "<label for=\"agency\">Agency : </label>";
			$html .= "<input type=\"text\" id=\"agency\" name=\"agency\" value=\"".$rows[0]['agency']."\" />".PHP_EOL;
			$html .= "<br>".PHP_EOL;
			$html .= "<label for=\"height\">Height : </label>";
			$html .= "<input type=\"text\" id=\"height\" name=\"height\" value=\"".$rows[0]['height']."\" />".PHP_EOL;
			$html .= "<label for=\"blood\">Blood : </label>";
			$html .= "<input type=\"text\" id=\"blood\" name=\"blood\" value=\"".$rows[0]['blood']."\" />".PHP_EOL;
			$html .= "<br>".PHP_EOL;
			$html .= "<input type=\"submit\" value=\"Submit\">".PHP_EOL;
			$html .= "</form>".PHP_EOL;

			break;

	}

?>
<!DOCTYPE html>
<html lang="<?php echo LANG; ?>">
<head>
	<meta charset="utf-8" />
	<title><?php echo TITLE; ?></title>
	<link rel="stylesheet" href="css/style.css" type="text/css" />
	<link rel="stylesheet" href="css/akb48g-colors.css" type="text/css" />
	<?php 
		if (isset($CSS))
		{
			echo $CSS.PHP_EOL;
		}
		if (isset($JS))
		{
			echo $JS.PHP_EOL;
		}
	?>
</head>
<body>
	<?php include("header.php"); ?>
	<br />
	<?php include("menu.php"); ?>
	<br />
	<div class="contenu">
		<?php echo $html; ?>
	</div>
	<?php include ("footer.php"); ?>
</body>
</html>