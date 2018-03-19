<?php

	$res = $bdd->query("SELECT MIN(date_ev) AS date_ev FROM nbgirl");
	$rows = $res->fetchAll();
	$StartDate = $rows[0]["date_ev"];
	
	$Today = new DateTime();
	$EndDate = $Today->format('Y-m-d');
	
?>
<ul class="menu">
	<li><a href="index.php">Main Page</a></li>
	<li><a href="index.php?page=showteam">Show Teams</a></li>
	<li><a href="index.php?page=histogroup">Group History</a></li>
	<li><a href="index.php?page=histogen">Generation History</a></li>
	<li><a href="index.php?page=graph">Chart</a></li>
	<li><a href="index.php?page=concurrent">Concurrent Position</a></li>
	<li><a href="index.php?page=sousenkyo">Sousenkyo</a></li>
	<li><a href="index.php?page=stage">Stages</a></li>
	<li><a href="index.php?page=addgirl">Add Girl</a></li>
</ul>
<div class="reset"></div>