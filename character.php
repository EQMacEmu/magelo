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
 *   February 25, 2014 - added heroic stats/augs (Maudigan c/o Kinglykrab) 
 *   September 26, 2014 - Maudigan
 *      made STR/STA/DEX/etc lowercase to match the db column names
 *      Updated character table name
 *      rewrote the code that pulls guild name/rank
 *   September 28, 2014 - Maudigan
 *      added code to monitor database performance
 *      altered character profile initialization to remove redundant query
 ***************************************************************************/
 
define('INCHARBROWSER', true);
include_once("include/config.php");
include_once("include/debug.php");
include_once("include/sql.php");
include_once("include/global.php");
include_once("include/language.php");
include_once("include/functions.php");
include_once("include/profile.php");
include_once("include/itemclass.php");
include_once("include/statsclass.php");
include_once("include/calculatestats.php");

global $game_db;
																							
//if character name isnt provided post error message and exit
if(!$_GET['char']) message_die($language['MESSAGE_ERROR'],$language['MESSAGE_NO_CHAR']);
else $charName = $_GET['char'];

//character initializations - rewritten 9/28/2014
$char = new profile($charName); //the profile class will sanitize the character name
$charID = $char->char_id(); 
$mypermission = GetPermissions($char->GetValue('gm'), $char->GetValue('anon'), $char->char_id());

//block view if user level doesnt have permission
if ($mypermission['inventory'] && !isAdmin()) message_die($language['MESSAGE_ERROR'],$language['MESSAGE_ITEM_NO_VIEW']);

//load profile information for the character
$name 		= $char->GetValue('name');
$last_name 	= $char->GetValue('last_name');
$title 		= $char->GetValue('title');
$level 		= $char->GetValue('level');
$deity 		= $char->GetValue('deity');
$baseSTR 	= $char->GetValue('str'); //changed stats to lowercase 9/26/2014
$baseSTA 	= $char->GetValue('sta');
$baseAGI 	= $char->GetValue('agi');
$baseDEX 	= $char->GetValue('dex');
$baseWIS 	= $char->GetValue('wis');
$baseINT 	= $char->GetValue('int');
$baseCHA 	= $char->GetValue('cha');
$defense 	= $char->GetValue('defense'); //TODO multi row table
$offense 	= $char->GetValue('offense'); //TODO multi row table
$race 		= $char->GetValue('race');
$class 		= $char->GetValue('class');
$pp 		= $char->GetValue('platinum');
$gp 		= $char->GetValue('gold');
$sp 		= $char->GetValue('silver');
$cp 		= $char->GetValue('copper');
$bpp 		= $char->GetValue('platinum_bank');
$bgp 		= $char->GetValue('gold_bank');
$bsp 		= $char->GetValue('silver_bank');
$bcp 		= $char->GetValue('copper_bank'); 

// solar: new stats 2020-12-21
$query = "SELECT
  weight,
  aa_points_unspent,
  aa_points_spent,
  
  hp_regen_standing_base,
  hp_regen_sitting_base,
  hp_regen_resting_base,
  hp_regen_standing_total,
  hp_regen_sitting_total,
  hp_regen_resting_total,
  hp_regen_item,
  hp_regen_item_cap,
  hp_regen_aa,
  
  mana_regen_standing_base,
  mana_regen_sitting_base,
  mana_regen_standing_total,
  mana_regen_sitting_total,
  mana_regen_item,
  mana_regen_item_cap,
  mana_regen_aa,
  
  hp_max_total,
  hp_max_item,
  
  mana_max_total,
  mana_max_item,
  
  end_max_total,
  
  ac_total,
  ac_item,
  ac_shield,
  ac_avoidance,
  ac_mitigation,
  
  atk_total,
  atk_item,
  atk_item_cap,
  atk_offense,
  atk_tohit,
  
  STR_total,
  STR_base,
  STR_item,
  STR_aa,
  STR_cap,
  
  STA_total,
  STA_base,
  STA_item,
  STA_aa,
  STA_cap,
  
  AGI_total,
  AGI_base,
  AGI_item,
  AGI_aa,
  AGI_cap,
  
  DEX_total,
  DEX_base,
  DEX_item,
  DEX_aa,
  DEX_cap,

  CHA_total,
  CHA_base,
  CHA_item,
  CHA_aa,
  CHA_cap,
  
  INT_total,
  INT_base,
  INT_item,
  INT_aa,
  INT_cap,
  
  WIS_total,
  WIS_base,
  WIS_item,
  WIS_aa,
  WIS_cap,
  
  MR_total,
  MR_item,
  MR_aa,
  MR_cap,
  
  FR_total,
  FR_item,
  FR_aa,
  FR_cap,
  
  CR_total,
  CR_item,
  CR_aa,
  CR_cap,
  
  DR_total,
  DR_item,
  DR_aa,
  DR_cap,
  
  PR_total,
  PR_item,
  PR_aa,
  PR_cap,
  
  damage_shield_item,
  haste_item
FROM character_magelo_stats
WHERE id = $charID";
if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
$results = $game_db->query($query);
$character_magelo_stats = array();
if(numRows($results) != 0)
{
  $row = fetchRows($results);
  $character_magelo_stats = $row[0];
}

//load guild name
//rewritten because the guild id was removed from the profile 9/26/2014
$query = "SELECT guilds.name, guild_members.rank 
          FROM guilds
          JOIN guild_members
          ON guilds.id = guild_members.guild_id
          WHERE guild_members.char_id = $charID LIMIT 1";
if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
$results = $game_db->query($query);

if(numRows($results) != 0)
{
  $row = fetchRows($results);
  $guild_name = $row[0]['name'];
  $guild_rank = $guildranks[$row[0]['rank']];
}

// place where all the items stats are added up
$itemstats = new stats();

// holds all of the items and info about them
$allitems = array();

// pull characters inventory slotid is loaded as
// "myslot" since items table also has a slotid field.
$query = "SELECT items.*, character_inventory.slotid AS myslot from items, character_inventory where character_inventory.id = '$charID' AND  items.id = character_inventory.itemid";
if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
$results = $game_db->query($query);
// loop through inventory results saving Name, Icon, and preload HTML for each
// item to be pasted into its respective div later
foreach($results AS $row) {
  $tempitem = new item($row);

  if ($tempitem->type() == EQUIPMENT)
    $itemstats->additem($row);

  if ($tempitem->type() == EQUIPMENT || $tempitem->type() == INVENTORY)
    $itemstats->addWT($row['weight']);

  $allitems[$tempitem->slot()] = $tempitem;
}

//drop page
$d_title = " - ".$name.$language['PAGE_TITLES_CHARACTER'];
include("include/header.php");

//build body template
$template->set_filenames(array(
  'character' => 'character_body.tpl')
);

$template->assign_vars(array(  
  'HIGHLIGHT_GM' => (($highlightgm && $gm)? "GM":""),
  'REGEN' => min($character_magelo_stats['hp_regen_item'], $character_magelo_stats['hp_regen_item_cap']),
  'FT' => min($character_magelo_stats['mana_regen_item'], $character_magelo_stats['mana_regen_item_cap']),
  'DS' => $character_magelo_stats['damage_shield_item'],
  'HASTE' => $character_magelo_stats['haste_item'],
  'FIRST_NAME' => $name,
  'LAST_NAME' => $last_name,
  'TITLE' => $title,
  'GUILD_NAME' => $guild_name,
  'LEVEL' => $level,
  'CLASS' => $dbclassnames[$class],
  'RACE' => $dbracenames[$race],
  'CLASS_NUM' => $class,
  'DEITY' => $dbdeities[$deity],
  'HP' => $character_magelo_stats['hp_max_total'],
  'MANA' => $character_magelo_stats['mana_max_total'],
  'ENDR' => $character_magelo_stats['end_max_total'],
  'AC' => $character_magelo_stats['ac_total'],
  'ATK' => $character_magelo_stats['atk_total'],
  'STR' => min($character_magelo_stats['STR_total'], $character_magelo_stats['STR_cap']),
  'STA' => min($character_magelo_stats['STA_total'], $character_magelo_stats['STA_cap']),
  'DEX' => min($character_magelo_stats['DEX_total'], $character_magelo_stats['DEX_cap']),
  'AGI' => min($character_magelo_stats['AGI_total'], $character_magelo_stats['AGI_cap']),
  'INT' => min($character_magelo_stats['INT_total'], $character_magelo_stats['INT_cap']),
  'WIS' => min($character_magelo_stats['WIS_total'], $character_magelo_stats['WIS_cap']),
  'CHA' => min($character_magelo_stats['CHA_total'], $character_magelo_stats['CHA_cap']),
  'POISON' => min($character_magelo_stats['PR_total'], $character_magelo_stats['PR_cap']),
  'FIRE' => min($character_magelo_stats['FR_total'], $character_magelo_stats['FR_cap']),
  'MAGIC' => min($character_magelo_stats['MR_total'], $character_magelo_stats['MR_cap']),
  'DISEASE' => min($character_magelo_stats['DR_total'], $character_magelo_stats['DR_cap']),
  'COLD' => min($character_magelo_stats['CR_total'], $character_magelo_stats['CR_cap']),
  'WEIGHT' => $character_magelo_stats['weight'],
  'PP' => (($mypermission['coininventory'])?$language['MESSAGE_DISABLED']:$pp),
  'GP' => (($mypermission['coininventory'])?$language['MESSAGE_DISABLED']:$gp),
  'SP' => (($mypermission['coininventory'])?$language['MESSAGE_DISABLED']:$sp),
  'CP' => (($mypermission['coininventory'])?$language['MESSAGE_DISABLED']:$cp),
  'BPP' => (($mypermission['coinbank'])?$language['MESSAGE_DISABLED']:$bpp),
  'BGP' => (($mypermission['coinbank'])?$language['MESSAGE_DISABLED']:$bgp),
  'BSP' => (($mypermission['coinbank'])?$language['MESSAGE_DISABLED']:$bsp),
  'BCP' => (($mypermission['coinbank'])?$language['MESSAGE_DISABLED']:$bcp),
 
  'L_HEADER_INVENTORY' => $language['CHAR_INVENTORY'],
  'L_HEADER_BANK' => $language['CHAR_BANK'],
  'L_REGEN' => $language['CHAR_REGEN'],
  'L_FT' => $language['CHAR_FT'],
  'L_DS' => $language['CHAR_DS'],
  'L_HASTE' => $language['CHAR_HASTE'],
  'L_HP' => $language['CHAR_HP'],
  'L_MANA' => $language['CHAR_MANA'],
  'L_ENDR' => $language['CHAR_ENDR'],
  'L_AC' => $language['CHAR_AC'],
  'L_ATK' => $language['CHAR_ATK'],
  'L_STR' => $language['CHAR_STR'],
  'L_STA' => $language['CHAR_STA'],
  'L_DEX' => $language['CHAR_DEX'],
  'L_AGI' => $language['CHAR_AGI'],
  'L_INT' => $language['CHAR_INT'],
  'L_WIS' => $language['CHAR_WIS'],
  'L_CHA' => $language['CHAR_CHA'],
  'L_POISON' => $language['CHAR_POISON'],
  'L_MAGIC' => $language['CHAR_MAGIC'],
  'L_DISEASE' => $language['CHAR_DISEASE'],
  'L_FIRE' => $language['CHAR_FIRE'],
  'L_COLD' => $language['CHAR_COLD'],
  'L_WEIGHT' => $language['CHAR_WEIGHT'],
  'L_AAS' => $language['BUTTON_AAS'],
  'L_KEYS' => $language['BUTTON_KEYS'],
  'L_FLAGS' => $language['BUTTON_FLAGS'],
  'L_SKILLS' => $language['BUTTON_SKILLS'],
  'L_CORPSE' => $language['BUTTON_CORPSE'],
  'L_INVENTORY' => $language['BUTTON_INVENTORY'],
  'L_FACTION' => $language['BUTTON_FACTION'],
  'L_BOOKMARK' => $language['BUTTON_BOOKMARK'],
  'L_CHARMOVE' => $language['BUTTON_CHARMOVE'],
  'L_CONTAINER' => $language['CHAR_CONTAINER'],
  'L_DONE' => $language['BUTTON_DONE'])
);

//dump inventory items ICONS
foreach ($allitems as $value) {
  if ($value->type() == INVENTORY && $mypermission['bags']) continue; 
  if ($value->type() == EQUIPMENT || $value->type() == INVENTORY)
    $template->assign_block_vars("invitem", array( 
      'SLOT' => $value->slot(),	   
      'ICON' => $value->icon(),
      'ISBAG' => (($value->slotcount() > 0) ? "true":"false"))
    );
}

//dump bags windows
foreach ($allitems as $value) {
  if ($value->type() == INVENTORY && $mypermission['bags']) continue; 
  if ($value->type() == BANK && $mypermission['bank']) continue;
  if ($value->slotcount() > 0)  {
  
    $template->assign_block_vars("bags", array( 
      'SLOT' => $value->slot(),	   
      'ROWS' => floor($value->slotcount()/2))
    );
    
    for ($i = 1;$i <= $value->slotcount(); $i++) 
      $template->assign_block_vars("bags.bagslots", array( 
        'BS_SLOT' => $i)
      );
      
    foreach ($allitems as $subvalue) 
  	 	if ($subvalue->type() == $value->slot()) 
  	 	  $template->assign_block_vars("bags.bagitems", array( 
        	    'BI_SLOT' => $subvalue->slot(),
        	    'BI_RELATIVE_SLOT' => $subvalue->vslot(),
        	    'BI_ICON' => $subvalue->icon())
      		  );
  } 
}

/* (bank display disabled)
//dump bank items ICONS
if (!$mypermission['bank']) {
	foreach ($allitems as $value) {
	  if ($value->type() == BANK) 
	    $template->assign_block_vars("bankitem", array( 
	      'SLOT' => $value->slot(),	   
	      'ICON' => $value->icon(),
	      'ISBAG' => (($value->slotcount() > 0) ? "true":"false"))
	    );
	}
}
*/

//dump items WINDOWS
foreach ($allitems as $value) {
  if ($value->type() == INVENTORY && $mypermission['bags']) continue; 
  //if ($value->type() == BANK && $mypermission['bank']) continue;
    $template->assign_block_vars("item", array(
      'SLOT' => $value->slot(),	   
      'NAME' => $value->name(),
      'ID' => $value->id(),
      'HTML' => $value->html())
    );
}

$template->pparse('character');

$template->destroy;

//added to monitor database performance 9/28/2014
if (defined('DB_PERFORMANCE')) print dbp_dump_buffer('query');

include("include/footer.php");

?>