<?php
$db_hostname = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'dataengineer';

$cnx = mysqli_connect($db_hostname, $db_username, $db_password);

if ($cnx === false){
  print 'impossible de se connecter au serveur '.$db_hostname." Erreur : ". mysqli_error($cnx)."<br>";
}
	
$result = mysqli_select_db($cnx, $db_name);
if ($result === false) {
  print 'impossible de se connecter à la bdd '.$db_name. " - Erreur : ".mysqli_errno($cnx)." ".mysqli_error($cnx)."<br>";
}else{
  // Cette requête affiche le 1er client trouvé parmi ceux qui ont fait le plus de locations.
  // En cas d'égalité, seul le 1er client trouvé sera affiché...  
  $requete_max_locations = "select first_name, last_name, address, city, postal_code, latitude, longitude, nb_locations  from ";
  $requete_max_locations .= "(select first_name, last_name, address, city, postal_code, latitude, longitude, count(1) as nb_locations from customer join rental on customer.customer_id = rental.customer_id join address on customer.address_id = address.address_id group by customer.customer_id order by nb_locations desc limit 1) as results_locations ";

  // autre requête ramenant le même résultat
  /*
  $requete_max_locations = "select first_name, last_name, address, city, postal_code, latitude, longitude, max(nb_locations) from ";
  $requete_max_locations .= " (select first_name, last_name, address, city, postal_code, latitude, longitude, count(1) as nb_locations from customer join rental on customer.customer_id = rental.customer_id join address on customer.address_id = address.address_id group by customer.customer_id order by nb_locations desc) as results_locations ";
  */
  
  $result_max_locations = mysqli_query($cnx,$requete_max_locations);
  // while($row = mysqli_fetch_row($result_max_locations)){
  if($row = mysqli_fetch_row($result_max_locations)){
  	$first_name    = $row[0];
  	$last_name     = $row[1];
    $address       = $row[2];
    $city          = $row[3];
    $postal_code   = $row[4];
    $latitude      = $row[5];
    $longitude     = $row[6];
  	$max_locations = $row[7];
?>
<!DOCTYPE html>
<html>
  <head>
    <style>
    table, th, td {
      border: 1px solid black;
      border-collapse: collapse;
    }
    </style>
  </head>
  
  <body>
    <h1>Affichage du client qui a fait le plus de locations</h1>
      <table style="width:100%">
        <tr>
          <td>NOM</td>
          <td>Prénom</td>
          <td>Nb locations</td>
          <td>Adresse</td>
          <td>Code Postal</td>
          <td>Ville</td>
          <td>Latitude</td>
          <td>Longitude</td>
        </tr>
        <tr>
          <td><?php print $last_name; ?></td>
          <td><?php print $first_name; ?></td>
          <td><?php print $max_locations; ?></td>
          <td><?php print $address; ?></td>
          <td><?php print $postal_code; ?></td>
          <td><?php print $city; ?></td>
          <td><?php print $latitude; ?></td>
          <td><?php print $longitude; ?></td>
        </tr>
        
      </table>
  </body>
<?php
  }
}

?>
