<?php
/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   Portions of this program are derived from publicly licensed software
 *   projects including, but not limited to phpBB, Magelo Clone, 
 *   EQEmulator, EQEditor, and Allakhazam Clone.
 *
 *                                  Author:
 *                           Maudigan(Airwalking) 
 *
 *  September 26, 2014 Maudigan
 *     updated character table name, zone id column name, and removed zonename
 *   September 28, 2014 - Maudigan
 *      added code to monitor database performance
 ***************************************************************************/
 

 
define('INCHARBROWSER', true);
include_once("include/config.php");
include_once("include/language.php");
include_once("include/functions.php");
include_once("include/global.php");

function trymove($name, $login, $zone) {
  global $language, $charmovezones;

  if (!$login || !$zone || !$name) return $login." / ".$name." / ".$zone." - one or more fields was left blank";
  if (!preg_match("/^[a-zA-Z]*\z/", $name)) return $login." / ".$name." / ".$zone." - character name contains illegal characters";
  //if (!preg_match("/^[a-zA-Z]*\z/", $login)) return $login." / ".$name." / ".$zone." - login contains illegal characters";
  if (!preg_match("/^[a-zA-Z]*\z/", $zone)) return $login." / ".$name." / ".$zone." - zone contains illegal characters";
  if (!$charmovezones[$zone]) return $login." / ".$name." / ".$zone." - zone is not a legal selection";  
  
  //get zone id, and verify shortname from db
  $template = "SELECT `long_name`, `short_name`, `zoneidnumber` FROM `zone` "
             ."WHERE LCASE(`short_name`)='%s' "
             ."LIMIT 1";
  $query = sprintf($template ,mysql_real_escape_string(strtolower($zone)));
  if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
  $result = mysql_query($query);
  
  if (!mysql_num_rows($result))  return $login." / ".$name." / ".$zone." - zone database error";  
  
  $row = mysql_fetch_array($result);
  $zonesn = $row['short_name'];
  $zoneln = $row['long_name'];
  $zoneid = $row['zoneidnumber'];
  
  //verify acct info is correct
  //updated character table name 9/26/2014
  $template = "SELECT `character_data`.`id` FROM `character_data` "
             ."JOIN `account` "
             ." ON `account`.`id` = `character_data`.`account_id` "
             ."WHERE LCASE(`account`.`name`)='%s' "
             ." AND LCASE(`character_data`.`name`)='%s' "
             ."LIMIT 1";
  $query = sprintf($template ,mysql_real_escape_string(strtolower($login)),mysql_real_escape_string(strtolower($name)));
  if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
  $result = mysql_query($query);
  
  if (!mysql_num_rows($result))  { 
    sleep(2);
    return $login." / ".$name." / ".$zone." - Login or character name was not correct";  
  }
  
  $row = mysql_fetch_array($result);
  $charid = $row['id'];
  
  //move em
  // updated character table name, zone id column name, and removed zonename 9/26/2014
  $template = "UPDATE `character_data` " 
             ."SET `zone_id` = '%s' "
             ."   ,`x` = '%s' "
             ."   ,`y` = '%s' "
             ."   ,`z` = '%s' "
             ."WHERE `id`='%s' ";
  $query = sprintf($template ,mysql_real_escape_string($zoneid)
                             ,mysql_real_escape_string($charmovezones[$zone]['x'])
                             ,mysql_real_escape_string($charmovezones[$zone]['y'])
                             ,mysql_real_escape_string($charmovezones[$zone]['z'])
                             ,mysql_real_escape_string($charid)
                             );
  if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
  $result = mysql_query($query);
   
  
  return $login." / ".$name." - moved to ".$zoneln;
}



//dont display bazaaar if blocked in config.php 
if ($blockcharmove) message_die($language['MESSAGE_ERROR'],$language['MESSAGE_ITEM_NO_VIEW']);

$names = $_GET['name'];
$zones = $_GET['zone'];
$logins = $_GET['login'];
$char = $_GET['char'];

//drop page
$d_title = " - ".$language['PAGE_TITLES_CHARMOVE'];
include("include/header.php");

if ($names && $logins && $zones) {
	$template->set_filenames(array(
	  'mover' => 'charmove_result_body.tpl')
	);
	
	$template->assign_vars(array( 
	  'L_CHARACTER_MOVER' => $language['CHARMOVE_CHARACTER_MOVER'],
	  'L_BOOKMARK' => $language['CHARMOVE_BOOKMARK'],
	  'L_BACK' => $language['BUTTON_BACK'])
	);
	
	foreach ($names as $key => $value) {
	  $template->assign_block_vars( "results", array( 
	    'OUTPUT' => trymove($value, $logins[$key], $zones[$key]))
	  );
	}
}
else {
	$template->set_filenames(array(
	  'mover' => 'charmove_body.tpl')
	);
	
	$template->assign_vars(array( 
	  'CHARNAME' => $char, 
	  'L_CHARACTER_MOVER' => $language['CHARMOVE_CHARACTER_MOVER'],
	  'L_LOGIN' => $language['CHARMOVE_LOGIN'],
	  'L_CHARNAME' => $language['CHARMOVE_CHARNAME'],
	  'L_ZONE' => $language['CHARMOVE_ZONE'],
	  'L_ADD_CHARACTER' => $language['CHARMOVE_ADD_CHARACTER'],
	  'L_MOVE' => $language['BUTTON_CHARMOVE'])
	);

	foreach($charmovezones as $key => $value) {
	  $template->assign_block_vars( "zones", array(
	    'VALUE' => $key)
	  );
	}
}


$template->pparse('mover');

$template->destroy;

//added to monitor database performance 9/28/2014
if (defined('DB_PERFORMANCE')) print dbp_dump_buffer('query');

include("include/footer.php");

?>