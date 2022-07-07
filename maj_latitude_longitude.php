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
  print "<br>".'Mise à jour de la latitude et de la longitude :'."<br><br>";
  
  $nb_maj = 0;
  
  $requete_address = "select address_id, address, city, postal_code from address where latitude is null and longitude is null";

  $result_address = mysqli_query($cnx,$requete_address);
  while($row = mysqli_fetch_row($result_address)){
  	$address_id    = $row[0];
  	$address       = $row[1];
  	$city          = $row[2];
  	$postal_code   = $row[3];
    
    $address_param = $address." ".$postal_code." ".$city;
    $address_param = str_replace(" ", "+", $address_param);
    
    // print $address_param."<br>";

    // l'appel de l'api nominatim génère l'erreur "failed to open stream: HTTP request failed! HTTP/1.1 403 Forbidden"
    // $json = file_get_contents("https://nominatim.openstreetmap.org/search?q=".$address_param."&format=json&limit=1");
    $json = file_get_contents("https://api-adresse.data.gouv.fr/search/?q=".$address_param."&limit=1");
    $json = json_decode($json);
    if(count($json->features)>0){
      $longitude=  ($json->features[0]->geometry->coordinates[0]);
      $latitude =  ($json->features[0]->geometry->coordinates[1]);
      // print "longitude = ".$longitude."<br>";
      // print "latitude = ".$latitude."<br>";
      
      if(isset($longitude) && isset($latitude) && isset($address_id)){
        $update_address = "update address set longitude = ".str_replace(',', '.',$longitude).", latitude = ".str_replace(',', '.',$latitude)." where address_id ='".$address_id."';";
        // print $update_address."<br>"; 
        
        $result_update = mysqli_query($cnx,$update_address);
        if($result_update){
          $nb_maj ++;
        }
      }
    } 
  }
  print $nb_maj.' ligne(s) mise(s) à jour';
}

?>
