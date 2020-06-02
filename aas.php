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
 *   October 16, 2013 - Leere
 *      Fixed an error in the AA query that left out berzerker AAs 
 *   September 26, 2014 - Maudigan
 *      Added underscore to 'aapoints' to make it the same as the column name
 *      Updated character table name
 *   September 28, 2014 - Maudigan
 *      added code to monitor database performance
 *      altered character profile initialization to remove redundant query
 *   September 28, 2014 - Maudigan
 *      replaced char blob
 *      added new aa tabs
 ***************************************************************************/
 
define('INCHARBROWSER', true);
include_once("include/config.php");
include_once("include/debug.php");
include_once("include/sql.php");
include_once("include/profile.php");
include_once("include/global.php");
include_once("include/language.php");
include_once("include/functions.php");

global $game_db;

//if character name isnt provided post error message and exit
if(!$_GET['char']) message_die($language['MESSAGE_ERROR'],$language['MESSAGE_NO_CHAR']);
else $charName = $_GET['char'];

//character initializations - rewritten 9/28/2014
$char = new profile($charName); //the profile class will sanitize the character name
$charID = $char->char_id(); 
$name = $char->GetValue('name');
$mypermission = GetPermissions($char->GetValue('gm'), $char->GetValue('anon'), $char->char_id());

//block view if user level doesnt have permission
if ($mypermission['AAs'] && !isAdmin()) message_die($language['MESSAGE_ERROR'],$language['MESSAGE_ITEM_NO_VIEW']);

$classbit = array(0,2,4,8,16,32,64,128,256,512,1024,2048,4096,8192,16384,32768,1);
//rewritten to replace character blob - 9/929/3014
//this probably needs the logic rethought, this is a bandaid
$temp = $char->GetTable("character_alternate_abilities");
$aa_array = array();
foreach($temp as $key => $value)
{
   $aa_array[$value["aa_id"]] = $value["aa_value"];
}

$aatabs = array();
$aatabs[1] = $language['AAS_TAB_1'];
$aatabs[2] = $language['AAS_TAB_2'];
$aatabs[3] = $language['AAS_TAB_3'];
$aatabs[4] = $language['AAS_TAB_4'];
$aatabs[5] = $language['AAS_TAB_5'];

//drop page
$d_title = " - ".$name.$language['PAGE_TITLES_AAS'];
include("include/header.php");

//build body template
$template->set_filenames(array(
  'aas' => 'aas_body.tpl')
);

$Color = "7b714a";
foreach ($aatabs as $key => $value) {
  $template->assign_block_vars("tabs", array( 
    'COLOR' => $Color,	   
    'ID' => $key,
    'TEXT' => $value)
  );
  $Color = "FFFFFF";
}

$SpentAA = 0;
$Display = "block";
foreach ($aatabs as $key => $value) {
  $template->assign_block_vars("boxes", array( 	   
    'ID' => $key,
    'DISPLAY' => $Display)
  );
  $Display = "none";

  // pull the classes AA's from the DB
  $query = "SELECT skill_id, name, cost, cost_inc, max_level FROM altadv_vars WHERE type = ".$key." AND ".$classbit[$char->GetValue('class')]." AND name NOT LIKE 'NOT USED' ORDER BY skill_id";

  if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
  $results = $game_db->query($query);
  foreach($results AS $row) {
    //calculate cost
    for($i = 1 ; $i <= $aa_array[$row['skill_id']] ; $i++) {
      $SpentAA += $row['cost'] + ($row['cost_inc'] * ($i - 1));
    }
    $template->assign_block_vars("boxes.aas", array( 	   
      'NAME' => $row['name'],
      'CUR' => sprintf("%d", $aa_array[$row['skill_id']]),
      'MAX' => $row['max_level'],
      'COST' => $row['cost'])
    );
  }
}

$template->assign_vars(array(  
  'NAME' => $name,
  'AA_POINTS' => $char->GetValue("aa_points"), //added underscore 9/26/2014
  'POINTS_SPENT' => $SpentAA,

  'L_ALTERNATE_ABILITIES' => $language['AAS_ALTERNATE_ABILITIES'], 
  'L_TITLE' => $language['AAS_TITLE'],
  'L_CUR_MAX' => $language['AAS_CUR_MAX'],
  'L_COST' => $language['AAS_COST'],
  'L_AA_POINTS' => $language['AAS_AA_POINTS'],
  'L_POINTS_SPENT' => $language['AAS_POINTS_SPENT'],
  'L_AAS' => $language['BUTTON_AAS'],
  'L_KEYS' => $language['BUTTON_KEYS'],
  'L_FLAGS' => $language['BUTTON_FLAGS'],
  'L_SKILLS' => $language['BUTTON_SKILLS'],
  'L_CORPSE' => $language['BUTTON_CORPSE'],
  'L_FACTION' => $language['BUTTON_FACTION'],
  'L_INVENTORY' => $language['BUTTON_INVENTORY'],
  'L_BOOKMARK' => $language['BUTTON_BOOKMARK'],
  'L_CHARMOVE' => $language['BUTTON_CHARMOVE'],  
  'L_DONE' => $language['BUTTON_DONE'])
);

$template->pparse('aas');
$template->destroy;

//added to monitor database performance 9/28/2014
if (defined('DB_PERFORMANCE')) print dbp_dump_buffer('query');

include("include/footer.php");

?>