<?php

	// $num = 4; $zerofill= 3; returns "004"
	function zerofill ($num,$zerofill) 
	{
		while (strlen($num)<$zerofill) 
		{
			$num = "0".$num;
		}
		return $num;
	}
	
	function date_fr ($date) 
	{	
		return substr($date,8,2) . "/" . substr($date,5,2) . "/" . substr($date,0,4);
	}
	
	function secondes_to_duree($secondes)
	{
		$s=$secondes % 60; //reste de la division en minutes => secondes
		$m1=($secondes-$s) / 60; //minutes totales
		$m=$m1 % 60;//reste de la division en heures => minutes
		$h=($m1-$m) / 60; //heures
		$resultat=zerofill($h,2).":".zerofill($m,2).":".zerofill($s,2);
		return $resultat;
	}
	function duree_to_secondes($duree)
	{
		$array_duree=explode(":",$duree);
		$secondes=3600*$array_duree[0]+60*$array_duree[1]+$array_duree[2];
		return $secondes;
	}
	// fonction pour chercher dans le tableau de résultat SQL
	// retourne un tableau avec les heures + heures normales + type de jour
	function cherche($tab, $jour, $mois, $annee)
	{
		$ferie = getHolidays($annee);
		$date = date('Y-m-d',mktime(0, 0, 0, $mois, $jour, $annee));
		$ts = mktime(0, 0, 0, $mois, $jour, $annee);
		if (!empty($tab))
		{ // il y a des résultats dans la base
			foreach ($tab as $t)
			{
				if ($t['date']==$date)
				{
					$did = array($t[1], $t[2], $t[3], $t[4], $t[5], $t[6], $t[7]);
					break;
				} else
				{
					//echo $ts;
					$f="1";
					if (in_array($ts,$ferie))
					{
						$f="5";
					}
					$did = array("00:00:00", "00:00:00", "00:00:00", 
								"00:00:00", "00:00:00", "00:00:00", $f);
				}
			}
		} else
		{ // il n'y a pas de résultats dans la base ( on est en début de mois )
			//echo $ts;
					$f="1";
					if (in_array($ts,$ferie))
					{
						$f="5";
					}
					$did = array("00:00:00", "00:00:00", "00:00:00", 
								"00:00:00", "00:00:00", "00:00:00", $f);
		
		}
		
		return $did;
	}
	
	function max_tab($array) { // fonction qui permet de compter le nb max de traites d'une échéance
		$i=0;
		foreach ($array as $key => $val ) 
		{
			$c[$i] = count($val);
			$i++;
		}
		return max($c);
	}
	
	function color(){
        static $count = 0;
        $num_args = func_num_args();
        if ($num_args === 0) return;
        $args = func_get_args();
        $color = $args[$count % $num_args];
        ++$count;
        return $color;
    }
?>