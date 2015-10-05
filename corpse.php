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
 *   September 26, 2014 - Maudigan 
 *      Updated character table name 
 *      repaired double carriage returns through whole file
 *   September 28, 2014 - Maudigan
 *      added code to monitor database performance
 *      altered character profile initialization to remove redundant query
 ***************************************************************************/
 
 
 
 
define('INCHARBROWSER', true);
include_once("include/config.php");
include_once("include/profile.php");
include_once("include/global.php");
include_once("include/language.php");
include_once("include/functions.php");


//if character name isnt provided post error message and exit
if(!$_GET['char']) message_die($language['MESSAGE_ERROR'],$language['MESSAGE_NO_CHAR']);
else $charName = $_GET['char'];

//character initializations - rewritten 9/28/2014
$char = new profile($charName); //the profile class will sanitize the character name
$charID = $char->char_id(); 
$name = $char->GetValue('name');
$mypermission = GetPermissions($char->GetValue('gm'), $char->GetValue('anon'), $char->char_id());

//block view if user level doesnt have permission
if ($mypermission['corpses']) message_die($language['MESSAGE_ERROR'],$language['MESSAGE_ITEM_NO_VIEW']);




// pull the characters corpses from the DB
$query = "SELECT zone.short_name, zone.zoneidnumber, character_corpses.isburried, character_corpses.x, character_corpses.y, character_corpses.rezzed, character_corpses.timeofdeath FROM zone, character_corpses WHERE character_corpses.charid = ".$charID." AND zone.zoneidnumber = character_corpses.zoneid ORDER BY character_corpses.timeofdeath DESC;";
if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
$results = mysql_query($query);
if (!mysql_num_rows($results)) message_die($language['CORPSE_CORPSES']." - ".$name,$language['MESSAGE_NO_CORPSES']);

//drop page
$d_title = " - ".$name.$language['PAGE_TITLES_CORPSE'];
include("include/header.php");

//build body template
$template->set_filenames(array(
  'corpse' => 'corpse_body.tpl')
);

$template->assign_vars(array(  
  'NAME' => $name,

  'L_REZZED' => $language['CORPSE_REZZED'],
  'L_TOD' => $language['CORPSE_TOD'],
  'L_LOC' => $language['CORPSE_LOC'],
  'L_MAP' => $language['CORPSE_MAP'],
  'L_CORPSES' => $language['CORPSE_CORPSES'],
  'L_AAS' => $language['BUTTON_AAS'],
  'L_KEYS' => $language['BUTTON_KEYS'],
  'L_FLAGS' => $language['BUTTON_FLAGS'],
  'L_SKILLS' => $language['BUTTON_SKILLS'],
  'L_CORPSE' => $language['BUTTON_CORPSE'],
  'L_FACTION' => $language['BUTTON_FACTION'],
  'L_BOOKMARK' => $language['BUTTON_BOOKMARK'],
  'L_INVENTORY' => $language['BUTTON_INVENTORY'],
  'L_CHARMOVE' => $language['BUTTON_CHARMOVE'],
  'L_DONE' => $language['BUTTON_DONE'])
);

//dump corpses
while ($row = mysql_fetch_array($results)) {
    $template->assign_block_vars("corpses", array( 
      'REZZED' => ((!$row['rezzed']) ? "0":"1"),	   
      'TOD' => $row['timeofdeath'],
      'LOC' => (($row['isburried']) ?  "(burried)":"(".floor($row['y']).", ".floor($row['x']).")"),
      'ZONE' => (($row['isburried']) ?  "shadowrest":$row['short_name']),
      'ZONE_ID' => $row["zoneidnumber"],
      'X' => floor($row['y']),
      'Y' => floor($row['x']))
    );
}

$template->pparse('corpse');

$template->destroy;



//added to monitor database performance 9/28/2014
if (defined('DB_PERFORMANCE')) print dbp_dump_buffer('query');

include("include/footer.php");


?>