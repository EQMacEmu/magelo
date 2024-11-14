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
 *   March 1, 2011 
 *   Planes of Power updated to reflect current PEQ Quest SVN flagging 
 *   Gates of Discord updated through Qvic access per PEQ Quest SVN flagging 
 *   Gates of Discord updated through Txevu assuming PEQ design does not change 
 *   August 1, 2011
 *   Fixed misprint on GOD flag, KT_2
 *   March 19, 2012
 *   Fixed misprint on GOD flag, KT_3
 *   November 17, 2013 - Sorvani
 *   Fixed bad getflag conditions in sewer 2/3/4 sections
 *   Fixed bad language array index in sewer 4 section
 *   September 26, 2014 - Maudigan
 *      Updated character table name
 *   September 28, 2014 - Maudigan
 *      added code to monitor database performance
 *      altered character profile initialization to remove redundant query
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
if ($mypermission['flags'] && !isAdmin()) message_die($language['MESSAGE_ERROR'],$language['MESSAGE_ITEM_NO_VIEW']);

//============================= 
//preload all quest globals 
//============================= 
$quest_globals = array(); 
$query = "SELECT name, value FROM quest_globals WHERE charid = $charID;";
if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
$results = $game_db->query($query);
if (numRows($results)) {
    foreach ($results AS $row) {
        $quest_globals[$row['name']] = $row['value'];
    }
}

//============================= 
//preload all quintessence items
//============================= 
$quintessence_items = array(); 
$quintessence_item_id = 29165;
$rathe_item_id = 29146;
$fennin_item_id = 29147;
$coirnav_item_id = 29163;
$xegony_item_id = 29164;
$query = "SELECT itemid FROM character_inventory WHERE id = $charID AND itemid IN ($rathe_item_id, $fennin_item_id, $coirnav_item_id, $xegony_item_id, $quintessence_item_id);";
if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query);
$results = $game_db->query($query);
if (numRows($results)) {
    foreach ($results AS $row) {
        $quintessence_items[$row['itemid']] = 1;
    }
}

//============================= 
//preload all zone flags 
//============================= 
$zone_flags = array(); 
$query = "SELECT zoneID FROM character_zone_flags WHERE id = $charID;";
if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
$results = $game_db->query($query);
if (numRows($results)) {
    foreach ($results AS $row) {
        $zone_flags[] = $row['zoneID'];
    }
}
// These flags get merged then deleted along the way
if(array_key_exists("zebuxoruk", $quest_globals)) {
    $quest_globals["karana"] = 4;
    $quest_globals["marr_book"] = 1;
}
if(array_key_exists("cipher", $quest_globals)) {
    $quest_globals["mmarr"] = 1;
    $quest_globals["saryrn"] = 1;
}

//============================= 
//check quest globals for flag 
//============================= 
function getflag($condition, $flagname) { 
    global $quest_globals;    
    if (!array_key_exists($flagname,$quest_globals)) return 0; 
    if ($quest_globals[$flagname]<$condition) return 0; 
    return 1; 
}

function getflagbit($bit, $flagname) {
    global $quest_globals;    
    if (!array_key_exists($flagname,$quest_globals)) return 0; 
    if (substr($quest_globals[$flagname], 2, 1) != "1") return 0; 
    return 1; 
}

function timeitem($item) {
    global $quintessence_items;
    global $quest_globals;
    if (array_key_exists($item, $quintessence_items) ||
        array_key_exists($quintessence_item_id, $quintessence_items) ||
	array_key_exists("time", $quest_globals)) {
        return 1;
    }
    return 0;
}

//============================= 
//check zone flags for access 
//============================= 
function getzoneflag($zoneid) { 
    global $zone_flags;      
    if (!in_array($zoneid, $zone_flags)) return 0; 
    return 1; 
} 

//drop page 
$d_title = " - ".$name.$language['PAGE_TITLES_FLAGS']; 
include("include/header.php"); 

//build body template 
$template->set_filenames(array( 
  'flags' => 'flags_body.tpl') 
);

$template->assign_vars(array(  
  'NAME' => $name, 

  'L_DONE' => $language['BUTTON_DONE'], 
  'L_AAS' => $language['BUTTON_AAS'], 
  'L_KEYS' => $language['BUTTON_KEYS'],
  'L_FLAGS' => $language['BUTTON_FLAGS'], 
  'L_SKILLS' => $language['BUTTON_SKILLS'], 
  'L_CORPSE' => $language['BUTTON_CORPSE'], 
  'L_FACTION' => $language['BUTTON_FACTION'], 
  'L_BOOKMARK' => $language['BUTTON_BOOKMARK'], 
  'L_INVENTORY' => $language['BUTTON_INVENTORY'], 
  'L_CHARMOVE' => $language['BUTTON_CHARMOVE'],  
  'L_FLAGS' => $language['FLAG_FLAGS']) 
); 

//because they enabled the level bypass and the fact that clicking the door is what sets your zone flag. 
//this will also be important when the 85/15 raid rule is implemented for letting people into zones. 
//for most of the PoP zones, we can not just check the zone flag to know if we have the flag. 
//for each zone i used the zone flag in combination with enough flags for each zone that it would not show erroneously. 
//for some zones it is only 1 other flag, for others it was multiple other flags. 

// use HasFlag in if statement and then set the $tempplate then reuse $HasFlag 
$HasFlag = 0; 

$template->assign_block_vars( "mainhead" , array( 'TEXT' => $language['FLAG_PoP']) ); 

if (getzoneflag(221) && getflag(2, "thelin")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 1, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_PoNB']) ); 

if (getzoneflag(214) && getflag(2, "zeks")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 2, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_PoTactics']) ); 

if (getzoneflag(200) && getflag(3, "fuirstel")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 3, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_CoD']) ); 

if (getzoneflag(208) && getflag(3, "mavuin")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 4, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_PoSPoV']) ); 

if (getzoneflag(211) && getflag(3, "mavuin") && getflag(2, "aerindar")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 5, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_HoHA']) ); 

if (getzoneflag(209) && getflag(3, "mavuin") && getflag(3, "karana")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 6, 'FLAG' =>  $HasFlag, 'TEXT' => $language['FLAG_PoP_BoT']) ); 

if (getzoneflag(220) && getflag(3, "mavuin") && getflag(2, "aerindar") && getflagbit(1, "hohtrials") && getflag(2, "hohtrials") && getflag(3, "hohtrials")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 7, 'FLAG' =>  $HasFlag, 'TEXT' => $language['FLAG_PoP_HoHB']) ); 

if (getzoneflag(207) && getflag(4, "thelin") && getflag(5, "fuirstel")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 8, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_PoTorment']) ); 

if (getzoneflag(212) && getflag(6, "zeks") && getflag(1, "cipher")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 9, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_SolRoTower']) ); 

if (getzoneflag(217) && getflag(2, "pofire")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 10, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_PoFire']) ); 

if (getzoneflag(216) && getflag(2, "zebuxoruk")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 11, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_PoAirEarthWater']) ); 

if (getflag(1, "time")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 12, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_PoTime']) ); 

//PoN B 
$template->assign_block_vars( "head" , array( 'ID' => 1, 'NAME' => $language['FLAG_PoP_PoNB']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "thelin"), 'TEXT' => $language['FLAG_PoP_PreHedge']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "thelin"), 'TEXT' => $language['FLAG_PoP_Hedge']) ); 
//Tactics 
$template->assign_block_vars( "head" , array( 'ID' => 2, 'NAME' => $language['FLAG_PoP_PoTactics']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "poi_door"), 'TEXT' => $language['FLAG_PoP_Xana']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "zeks"), 'TEXT' => $language['FLAG_PoP_PreMB']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "zeks"), 'TEXT' => $language['FLAG_PoP_MB']) ); 
//CoD 
// Technically, Grummus awards two flags; "grummus=1" and "fuirstel=2"
// grummus is used to SetZoneFlag(200) for access to codecay
// fuirstel=2 is used to continue the quest line. It seems we could replace grummus with fuirstel=2
$template->assign_block_vars( "head" , array( 'ID' => 3, 'NAME' => $language['FLAG_PoP_CoD']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "fuirstel"), 'TEXT' => $language['FLAG_PoP_PreGrummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "grummus"), 'TEXT' => $language['FLAG_PoP_Grummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "fuirstel"), 'TEXT' => $language['FLAG_PoP_PostGrummus']) ); 
//Valor & Storms 
$template->assign_block_vars( "head" , array( 'ID' => 4, 'NAME' => $language['FLAG_PoP_PoSPoV']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "mavuin"), 'TEXT' => $language['FLAG_PoP_PreTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "mavuin"), 'TEXT' => $language['FLAG_PoP_Trial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "mavuin"), 'TEXT' => $language['FLAG_PoP_PostTrial']) ); 
//HoH A 
$template->assign_block_vars( "head" , array( 'ID' => 5, 'NAME' => $language['FLAG_PoP_HoHA']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "mavuin"), 'TEXT' => $language['FLAG_PoP_PreTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "mavuin"), 'TEXT' => $language['FLAG_PoP_Trial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "mavuin"), 'TEXT' => $language['FLAG_PoP_PostTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "aerindar"), 'TEXT' => $language['FLAG_PoP_AD1']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "aerindar"), 'TEXT' => $language['FLAG_PoP_AD2']) ); 
//BoT 
$template->assign_block_vars( "head" , array( 'ID' => 6, 'NAME' => $language['FLAG_PoP_BoT']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "mavuin"), 'TEXT' => $language['FLAG_PoP_PreTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "mavuin"), 'TEXT' => $language['FLAG_PoP_Trial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "mavuin"), 'TEXT' => $language['FLAG_PoP_PostTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "karana"), 'TEXT' => $language['FLAG_PoP_Askr1']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "karana"), 'TEXT' => $language['FLAG_PoP_Askr2']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "karana"), 'TEXT' => $language['FLAG_PoP_Askr3']) ); 
//HoH B 
$template->assign_block_vars( "head" , array( 'ID' => 7, 'NAME' => $language['FLAG_PoP_HoHB']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "mavuin"), 'TEXT' => $language['FLAG_PoP_PreTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "mavuin"), 'TEXT' => $language['FLAG_PoP_Trial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "mavuin"), 'TEXT' => $language['FLAG_PoP_PostTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "aerindar"), 'TEXT' => $language['FLAG_PoP_AD1']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "aerindar"), 'TEXT' => $language['FLAG_PoP_AD2']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflagbit(1, "hohtrials"), 'TEXT' => $language['FLAG_PoP_Faye']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflagbit(2, "hohtrials"), 'TEXT' => $language['FLAG_PoP_Trell']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflagbit(3, "hohtrials"), 'TEXT' => $language['FLAG_PoP_Garn']) ); 
//Torment 
$template->assign_block_vars( "head" , array( 'ID' => 8, 'NAME' => $language['FLAG_PoP_PoTorment']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "fuirstel"), 'TEXT' => $language['FLAG_PoP_PreGrummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "grummus"), 'TEXT' => $language['FLAG_PoP_Grummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "fuirstel"), 'TEXT' => $language['FLAG_PoP_PostGrummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "thelin"), 'TEXT' => $language['FLAG_PoP_PreHedge']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "thelin"), 'TEXT' => $language['FLAG_PoP_Hedge']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "thelin"), 'TEXT' => $language['FLAG_PoP_TT']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(4, "thelin"), 'TEXT' => $language['FLAG_PoP_PostTerris']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "bertox_key"), 'TEXT' => $language['FLAG_PoP_Carpin']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(4, "fuirstel"), 'TEXT' => $language['FLAG_PoP_Bertox']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(5, "fuirstel"), 'TEXT' => $language['FLAG_PoP_PostBertox']) ); 
//Sol Ro Tower 
$template->assign_block_vars( "head" , array( 'ID' => 9, 'NAME' => $language['FLAG_PoP_SolRoTower']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "zeks"), 'TEXT' => $language['FLAG_PoP_PreMB']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "zeks"), 'TEXT' => $language['FLAG_PoP_MB']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "zeks"), 'TEXT' => $language['FLAG_PoP_VZ']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(4, "zeks"), 'TEXT' => $language['FLAG_PoP_TZ']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(6, "zeks"), 'TEXT' => $language['FLAG_PoP_MaelinInfo1']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "tylis"), 'TEXT' => $language['FLAG_PoP_PreSaryrn']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "tylis"), 'TEXT' => $language['FLAG_PoP_KoS']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "saryrn"), 'TEXT' => $language['FLAG_PoP_Saryrn']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "mmarr"), 'TEXT' => $language['FLAG_PoP_MM']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "cipher"), 'TEXT' => $language['FLAG_PoP_Cipher']) ); 
//Fire 
$template->assign_block_vars( "head" , array( 'ID' => 10, 'NAME' => $language['FLAG_PoP_PoFire']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pofire"), 'TEXT' => $language['FLAG_PoP_PreSolRo']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "zeks"), 'TEXT' => $language['FLAG_PoP_PreMB']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "zeks"), 'TEXT' => $language['FLAG_PoP_MB']) ); 
// Vallon Zek is 3, Tallon Zek is 4, both is 5
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "zeks"), 'TEXT' => $language['FLAG_PoP_VZ']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(4, "zeks"), 'TEXT' => $language['FLAG_PoP_TZ']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(6, "zeks"), 'TEXT' => $language['FLAG_PoP_MaelinInfo1']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflagbit(2, "sol_room"), 'TEXT' => $language['FLAG_PoP_Arlyxir']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflagbit(3, "sol_room"), 'TEXT' => $language['FLAG_PoP_Dresolik']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflagbit(5, "sol_room"), 'TEXT' => $language['FLAG_PoP_Jiva']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflagbit(4, "sol_room"), 'TEXT' => $language['FLAG_PoP_Rizlona']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflagbit(5, "sol_room"), 'TEXT' => $language['FLAG_PoP_Xusl']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "saryrn"), 'TEXT' => $language['FLAG_PoP_Saryrn']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "mmarr"), 'TEXT' => $language['FLAG_PoP_MM']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "cipher"), 'TEXT' => $language['FLAG_PoP_Cipher']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(7, "zeks"), 'TEXT' => $language['FLAG_PoP_RZ']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "pofire"), 'TEXT' => $language['FLAG_PoP_SolRo']) ); 

//Air/Earth/Water 
$template->assign_block_vars( "head" , array( 'ID' => 11, 'NAME' => $language['FLAG_PoP_PoAirEarthWater']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "thelin"), 'TEXT' => $language['FLAG_PoP_PreHedge']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "thelin"), 'TEXT' => $language['FLAG_PoP_Hedge']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "mavuin"), 'TEXT' => $language['FLAG_PoP_PreTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "mavuin"), 'TEXT' => $language['FLAG_PoP_Trial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "mavuin"), 'TEXT' => $language['FLAG_PoP_PostTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "thelin"), 'TEXT' => $language['FLAG_PoP_TT']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(4, "thelin"), 'TEXT' => $language['FLAG_PoP_PostTerris']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "fuirstel"), 'TEXT' => $language['FLAG_PoP_PreGrummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "grummus"), 'TEXT' => $language['FLAG_PoP_Grummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "fuirstel"), 'TEXT' => $language['FLAG_PoP_PostGrummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "karana"), 'TEXT' => $language['FLAG_PoP_Askr1']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "karana"), 'TEXT' => $language['FLAG_PoP_Askr2']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "karana"), 'TEXT' => $language['FLAG_PoP_Askr3']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(4, "karana"), 'TEXT' => $language['FLAG_PoP_Agnarr']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "aerindar"), 'TEXT' => $language['FLAG_PoP_AD1']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "aerindar"), 'TEXT' => $language['FLAG_PoP_AD2']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflagbit(1, "hohtrials"), 'TEXT' => $language['FLAG_PoP_Faye']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflagbit(2, "hohtrials"), 'TEXT' => $language['FLAG_PoP_Trell']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflagbit(3, "hohtrials"), 'TEXT' => $language['FLAG_PoP_Garn']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "mmarr"), 'TEXT' => $language['FLAG_PoP_MM']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "bertox_key"), 'TEXT' => $language['FLAG_PoP_Carpin']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(4, "fuirstel"), 'TEXT' => $language['FLAG_PoP_Bertox']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(5, "fuirstel"), 'TEXT' => $language['FLAG_PoP_PostBertox']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "tylis"), 'TEXT' => $language['FLAG_PoP_PreSaryrn']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "saryrn"), 'TEXT' => $language['FLAG_PoP_Saryrn']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "tylis"), 'TEXT' => $language['FLAG_PoP_KoS']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(7, "zeks"), 'TEXT' => $language['FLAG_PoP_RZ']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "zebuxoruk"), 'TEXT' => $language['FLAG_PoP_MaelinLore']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(2, "zebuxoruk"), 'TEXT' => $language['FLAG_PoP_MaelinInfo2']) ); 
//Time 
$template->assign_block_vars( "head" , array( 'ID' => 12, 'NAME' => $language['FLAG_PoP_PoTime']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => timeitem($fennin_item_id), 'TEXT' => $language['FLAG_PoP_Fennin']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => timeitem($xegony_item_id), 'TEXT' => $language['FLAG_PoP_Xegony']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => timeitem($coirnav_item_id), 'TEXT' => $language['FLAG_PoP_Coirnav']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "earthb_key"), 'TEXT' => $language['FLAG_PoP_Arbitor']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => timeitem($rathe_item_id), 'TEXT' => $language['FLAG_PoP_Rathe']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "time"), 'TEXT' => $language['FLAG_PoP_Time']) ); 

$template->pparse('flags'); 

$template->destroy; 

//added to monitor database performance 9/28/2014
if (defined('DB_PERFORMANCE')) print dbp_dump_buffer('query');

include("include/footer.php"); 

?>
