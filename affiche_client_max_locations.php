<?php
$db_hostname = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'dataengineer';

$cnx = mysqli_connect($db_hostname, $db_username, $db_password);

if ($cnx === false){
	// throw new Exception('Could not connect: \'' . $db_hostname . '\'. Error: ' . mysqli_error($cnx));
  print 'impossible de se connecter au serveur '.$db_hostname." Erreur : ". mysqli_error($cnx)."<br>";
}
	
$result = mysqli_select_db($cnx, $db_name);
if ($result === false) {
	// throw new Exception('Can\'t select database \'' . $db_name . '\'.' 	. ' Error: (' . mysqli_errno($cnx) . ') ' . mysqli_error($cnx));
  print 'impossible de se connecter à la bdd '.$db_name. " - Erreur : ".mysqli_errno($cnx)." ".mysqli_error($cnx)."<br>";
}else{
  print "<br>".'Affichage du client qui a fait le plus de locations'."<br><br>";

  // Cette requête affiche le 1er client trouvé parmi ceux qui ont fait le plus de locations.
  // En cas d'égalité, seul le 1er client trouvé sera affiché...  
  $requete_max_locations = "select first_name, last_name, nb_locations from ";
  $requete_max_locations .= "(select first_name, last_name, count(1) as nb_locations from customer join rental on customer.customer_id = rental.customer_id group by customer.customer_id order by nb_locations desc limit 1) as results_locations ";

  // print $requete_max_locations."<br>";
  
  // autre requête ramenant le même résultat
  /*
  $requete_max_locations = "select first_name, last_name, max(nb_locations) from ";
  $requete_max_locations .= " (select first_name, last_name, count(1) as nb_locations from customer join rental on customer.customer_id = rental.customer_id group by customer.customer_id order by nb_locations desc) as results_locations ";
  
  print $requete_max_locations."<br>";
  */
  
  $result_max_locations = mysqli_query($cnx,$requete_max_locations);
  while($row = mysqli_fetch_row($result_max_locations)){
  	$first_name    = $row[0];
  	$last_name     = $row[1];
  	$max_locations = $row[2];

    print $first_name." ".$last_name." : ".$max_locations." locations"."<br>";

  }
}

?>
