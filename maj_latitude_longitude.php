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
  print "<br>".'Mise à jour de la latitude et de la longitude :'."<br><br>";
  
  $nb_maj = 0;
  $nb_adresses_KO = 0;
  
  // Si on ne limite pas le nombre de résultats, l'appel de l'api nominatim générera une Fatal error: "Maximum execution time of (120) seconds exceeded"
  // $requete_address = "select address_id, address, city, postal_code from address where latitude is null and longitude is null";
  $requete_address = "select address_id, address, city, postal_code from address where latitude is null and longitude is null and retour_api_nominatim is null limit 200";

  $result_address = mysqli_query($cnx,$requete_address);
  while($row = mysqli_fetch_row($result_address)){
	$address_id    = $row[0];
	$address       = $row[1];
	$city          = $row[2];
	$postal_code   = $row[3];
    
    $address_param = $address." ".$postal_code." ".$city;
    $address_param = str_replace(" ", "+", $address_param);

    // l'appel de l'api nominatim génère l'erreur "failed to open stream: HTTP request failed! HTTP/1.1 403 Forbidden" si on ne passe pas d'adresse mail en paramètre...
    // $json = file_get_contents("https://nominatim.openstreetmap.org/search?q=".$address_param."&format=json&limit=1");
	  
    // Version "simple" avec le paramètre q (query)  
    // $json = file_get_contents("https://nominatim.openstreetmap.org/search?q=".$address_param."&format=json&limit=1&email=magali_schimpf@yahoo.fr");
	  
    // Version "structured" avec les paramètres street, city et postalcode
    $address = str_replace(" ", "+", $address);
    $city = str_replace(" ", "+", $city);
    $postal_code = str_replace(" ", "+", $postal_code);
    $json = file_get_contents("https://nominatim.openstreetmap.org/search?street=".$address."&city=".$city."&postalcode=".$postal_code."&format=json&limit=1&email=magali_schimpf@yahoo.fr"); // Lit tout un fichier dans une chaîne
    $json = json_decode($json);
    
    if(count($json)>0){
      $longitude=  ($json[0]->lon);
      $latitude=  ($json[0]->lat);
      
      if(isset($longitude) && isset($latitude) && isset($address_id)){
        $update_address = "update address set longitude = ".str_replace(',', '.',$longitude).", latitude = ".str_replace(',', '.',$latitude)." where address_id ='".$address_id."';";

        $result_update = mysqli_query($cnx,$update_address);
        if($result_update){
          $nb_maj ++;
        }
      }else{
        $update_address = "update address set retour_api_nominatim = 'latitude and/or longitude KO' where address_id ='".$address_id."';";
        $result_update = mysqli_query($cnx,$update_address);
        $nb_adresses_KO ++;
      }
      
    }else{
        $update_address = "update address set retour_api_nominatim = 'No search results found' where address_id ='".$address_id."';";
        $result_update = mysqli_query($cnx,$update_address);
        $nb_adresses_KO ++;
    }

  }
  print $nb_maj.' adresse(s) mise(s) à jour'.'<br>';
  if($nb_adresses_KO){
    print $nb_adresses_KO." adresses non mise(s) à jour car non reconnue(s) par l'api";
  }
}

?>
