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
//preload all zone flags 
//============================= 
$zone_flags = array(); 
$query = "SELECT zoneID FROM zone_flags WHERE charID = $charID;";
if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
$results = $game_db->query($query);
if (numRows($results)) {
    foreach ($results AS $row) {
        $zone_flags[] = $row['zoneID'];
    }
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

if (getzoneflag(221) && getflag(1, "pop_pon_hedge_jezith") && getflag(1, "pop_pon_construct")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 1, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_PoNB']) ); 

if (getzoneflag(214) && getflag(1, "pop_poi_behometh_preflag") && getflag(1, "pop_poi_behometh_flag")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 2, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_PoTactics']) ); 

if (getzoneflag(200) && getflag(1, "pop_pod_elder_fuirstel")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 3, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_CoD']) ); 

if (getzoneflag(208) && getflag(1, "pop_poj_valor_storms")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 4, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_PoSPoV']) ); 

if (getzoneflag(211) && getflag(1, "pop_poj_valor_storms") && getflag(1, "pop_pov_aerin_dar")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 5, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_HoHA']) ); 

if (getzoneflag(209) && getflag(1, "pop_poj_valor_storms") && getflag(1, "pop_pos_askr_the_lost_final")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 6, 'FLAG' =>  $HasFlag, 'TEXT' => $language['FLAG_PoP_BoT']) ); 

if (getzoneflag(220) && getflag(1, "pop_poj_valor_storms") && getflag(1, "pop_pov_aerin_dar") && getflag(1, "pop_hoh_faye") && getflag(1, "pop_hoh_trell") && getflag(1, "pop_hoh_garn")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 7, 'FLAG' =>  $HasFlag, 'TEXT' => $language['FLAG_PoP_HoHB']) ); 

if (getzoneflag(207) && getflag(1, "pop_pod_elder_fuirstel") && getflag(1, "pop_ponb_poxbourne") && getflag(1, "pop_cod_final")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 8, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_PoTorment']) ); 

if (getzoneflag(212) && getflag(1, "pop_poi_behometh_flag") && getflag(1, "pop_tactics_tallon") && getflag(1, "pop_tactics_vallon") && getflag(1, "pop_hohb_marr") && getflag(1, "pop_pot_saryrn_final")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 9, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_SolRoTower']) ); 

if (getzoneflag(217) && getflag(1, "pop_poi_behometh_flag") && getflag(1, "pop_tactics_tallon") && getflag(1, "pop_tactics_vallon") && getflag(1, "pop_hohb_marr") && getflag(1, "pop_tactics_ralloz") && getflag(1, "pop_sol_ro_arlyxir") && getflag(1, "pop_sol_ro_dresolik") && getflag(1, "pop_sol_ro_jiva") && getflag(1, "pop_sol_ro_rizlona") && getflag(1, "pop_sol_ro_xuzl") && getflag(1, "pop_sol_ro_solusk")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 10, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_PoFire']) ); 

if (getzoneflag(216) && getflag(1, "pop_elemental_grand_librarian")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 11, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_PoAirEarthWater']) ); 

if (getflag(1, "pop_time_maelin") && getflag(1, "pop_fire_fennin_projection") && getflag(1, "pop_wind_xegony_projection") && getflag(1, "pop_water_coirnav_projection") && getflag(1, "pop_eartha_arbitor_projection") && getflag(1, "pop_earthb_rathe")) { $HasFlag = 1; } else { $HasFlag = 0; } 
$template->assign_block_vars( "mainhead.main" , array( 'ID' => 12, 'FLAG' => $HasFlag, 'TEXT' => $language['FLAG_PoP_PoTime']) ); 

//PoN B 
$template->assign_block_vars( "head" , array( 'ID' => 1, 'NAME' => $language['FLAG_PoP_PoNB']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pon_hedge_jezith"), 'TEXT' => $language['FLAG_PoP_PreHedge']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pon_construct"), 'TEXT' => $language['FLAG_PoP_Hedge']) ); 
//Tactics 
$template->assign_block_vars( "head" , array( 'ID' => 2, 'NAME' => $language['FLAG_PoP_PoTactics']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poi_dragon"), 'TEXT' => $language['FLAG_PoP_Xana']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poi_behometh_preflag"), 'TEXT' => $language['FLAG_PoP_PreMB']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poi_behometh_flag"), 'TEXT' => $language['FLAG_PoP_MB']) ); 
//CoD 
$template->assign_block_vars( "head" , array( 'ID' => 3, 'NAME' => $language['FLAG_PoP_CoD']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pod_alder_fuirstel"), 'TEXT' => $language['FLAG_PoP_PreGrummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pod_grimmus_planar_projection"), 'TEXT' => $language['FLAG_PoP_Grummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pod_elder_fuirstel"), 'TEXT' => $language['FLAG_PoP_PostGrummus']) ); 
//Valor & Storms 
$template->assign_block_vars( "head" , array( 'ID' => 4, 'NAME' => $language['FLAG_PoP_PoSPoV']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poj_mavuin"), 'TEXT' => $language['FLAG_PoP_PreTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poj_tribunal"), 'TEXT' => $language['FLAG_PoP_Trial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poj_valor_storms"), 'TEXT' => $language['FLAG_PoP_PostTrial']) ); 
//HoH A 
$template->assign_block_vars( "head" , array( 'ID' => 5, 'NAME' => $language['FLAG_PoP_HoHA']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poj_mavuin"), 'TEXT' => $language['FLAG_PoP_PreTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poj_tribunal"), 'TEXT' => $language['FLAG_PoP_Trial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poj_valor_storms"), 'TEXT' => $language['FLAG_PoP_PostTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pov_aerin_dar"), 'TEXT' => $language['FLAG_PoP_AD']) ); 
//BoT 
$template->assign_block_vars( "head" , array( 'ID' => 6, 'NAME' => $language['FLAG_PoP_BoT']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poj_mavuin"), 'TEXT' => $language['FLAG_PoP_PreTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poj_tribunal"), 'TEXT' => $language['FLAG_PoP_Trial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poj_valor_storms"), 'TEXT' => $language['FLAG_PoP_PostTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "pop_pos_askr_the_lost"), 'TEXT' => $language['FLAG_PoP_Askr1']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pos_askr_the_lost_final"), 'TEXT' => $language['FLAG_PoP_Askr2']) ); 
//HoH B 
$template->assign_block_vars( "head" , array( 'ID' => 7, 'NAME' => $language['FLAG_PoP_HoHB']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poj_mavuin"), 'TEXT' => $language['FLAG_PoP_PreTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poj_tribunal"), 'TEXT' => $language['FLAG_PoP_Trial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poj_valor_storms"), 'TEXT' => $language['FLAG_PoP_PostTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pov_aerin_dar"), 'TEXT' => $language['FLAG_PoP_AD']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_hoh_faye"), 'TEXT' => $language['FLAG_PoP_Faye']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_hoh_trell"), 'TEXT' => $language['FLAG_PoP_Trell']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_hoh_garn"), 'TEXT' => $language['FLAG_PoP_Garn']) ); 
//Torment 
$template->assign_block_vars( "head" , array( 'ID' => 8, 'NAME' => $language['FLAG_PoP_PoTorment']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pod_alder_fuirstel"), 'TEXT' => $language['FLAG_PoP_PreGrummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pod_grimmus_planar_projection"), 'TEXT' => $language['FLAG_PoP_Grummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pod_elder_fuirstel"), 'TEXT' => $language['FLAG_PoP_PostGrummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pon_hedge_jezith"), 'TEXT' => $language['FLAG_PoP_PreHedge']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pon_construct"), 'TEXT' => $language['FLAG_PoP_Hedge']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_ponb_terris"), 'TEXT' => $language['FLAG_PoP_TT']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_ponb_poxbourne"), 'TEXT' => $language['FLAG_PoP_PostTerris']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_cod_preflag"), 'TEXT' => $language['FLAG_PoP_Carpin']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_cod_bertox"), 'TEXT' => $language['FLAG_PoP_Bertox']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_cod_final"), 'TEXT' => $language['FLAG_PoP_PostBertox']) ); 
//Sol Ro Tower 
$template->assign_block_vars( "head" , array( 'ID' => 9, 'NAME' => $language['FLAG_PoP_SolRoTower']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poi_behometh_preflag"), 'TEXT' => $language['FLAG_PoP_PreMB']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poi_behometh_flag"), 'TEXT' => $language['FLAG_PoP_MB']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_tactics_tallon"), 'TEXT' => $language['FLAG_PoP_TZ']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_tactics_vallon"), 'TEXT' => $language['FLAG_PoP_VZ']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pot_shadyglade"), 'TEXT' => $language['FLAG_PoP_PreSaryrn']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pot_newleaf"), 'TEXT' => $language['FLAG_PoP_KoS']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pot_saryrn"), 'TEXT' => $language['FLAG_PoP_Saryrn']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pot_saryrn_final"), 'TEXT' => $language['FLAG_PoP_PostSaryrn']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_hohb_marr"), 'TEXT' => $language['FLAG_PoP_MM']) ); 
//Fire 
$template->assign_block_vars( "head" , array( 'ID' => 10, 'NAME' => $language['FLAG_PoP_PoFire']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poi_behometh_preflag"), 'TEXT' => $language['FLAG_PoP_PreMB']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poi_behometh_flag"), 'TEXT' => $language['FLAG_PoP_MB']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_tactics_tallon"), 'TEXT' => $language['FLAG_PoP_TZ']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_tactics_vallon"), 'TEXT' => $language['FLAG_PoP_VZ']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_tactics_ralloz"), 'TEXT' => $language['FLAG_PoP_RZ']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_sol_ro_arlyxir"), 'TEXT' => $language['FLAG_PoP_Arlyxir']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_sol_ro_dresolik"), 'TEXT' => $language['FLAG_PoP_Dresolik']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_sol_ro_jiva"), 'TEXT' => $language['FLAG_PoP_Jiva']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_sol_ro_rizlona"), 'TEXT' => $language['FLAG_PoP_Rizlona']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_sol_ro_xuzl"), 'TEXT' => $language['FLAG_PoP_Xusl']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_sol_ro_solusk"), 'TEXT' => $language['FLAG_PoP_SolRo']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_hohb_marr"), 'TEXT' => $language['FLAG_PoP_MM']) ); 

//Air/Earth/Water 
$template->assign_block_vars( "head" , array( 'ID' => 11, 'NAME' => $language['FLAG_PoP_PoAirEarthWater']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pon_hedge_jezith"), 'TEXT' => $language['FLAG_PoP_PreHedge']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pon_construct"), 'TEXT' => $language['FLAG_PoP_Hedge']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poj_mavuin"), 'TEXT' => $language['FLAG_PoP_PreTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poj_tribunal"), 'TEXT' => $language['FLAG_PoP_Trial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_poj_valor_storms"), 'TEXT' => $language['FLAG_PoP_PostTrial']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_ponb_terris"), 'TEXT' => $language['FLAG_PoP_TT']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_ponb_poxbourne"), 'TEXT' => $language['FLAG_PoP_PostTerris']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pod_alder_fuirstel"), 'TEXT' => $language['FLAG_PoP_PreGrummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pod_grimmus_planar_projection"), 'TEXT' => $language['FLAG_PoP_Grummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pod_elder_fuirstel"), 'TEXT' => $language['FLAG_PoP_PostGrummus']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(3, "pop_pos_askr_the_lost"), 'TEXT' => $language['FLAG_PoP_Askr1']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pos_askr_the_lost_final"), 'TEXT' => $language['FLAG_PoP_Askr2']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_bot_agnarr"), 'TEXT' => $language['FLAG_PoP_Agnarr']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pov_aerin_dar"), 'TEXT' => $language['FLAG_PoP_AD']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_hoh_faye"), 'TEXT' => $language['FLAG_PoP_Faye']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_hoh_trell"), 'TEXT' => $language['FLAG_PoP_Trell']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_hoh_garn"), 'TEXT' => $language['FLAG_PoP_Garn']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_hohb_marr"), 'TEXT' => $language['FLAG_PoP_MM']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_cod_preflag"), 'TEXT' => $language['FLAG_PoP_Carpin']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_cod_bertox"), 'TEXT' => $language['FLAG_PoP_Bertox']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_cod_final"), 'TEXT' => $language['FLAG_PoP_PostBertox']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pot_shadyglade"), 'TEXT' => $language['FLAG_PoP_PreSaryrn']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pot_saryrn"), 'TEXT' => $language['FLAG_PoP_Saryrn']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pot_newleaf"), 'TEXT' => $language['FLAG_PoP_KoS']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_pot_saryrn_final"), 'TEXT' => $language['FLAG_PoP_PostSaryrn']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_tactics_ralloz"), 'TEXT' => $language['FLAG_PoP_RZ']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_elemental_grand_librarian"), 'TEXT' => $language['FLAG_PoP_Maelin']) ); 
//Time 
$template->assign_block_vars( "head" , array( 'ID' => 12, 'NAME' => $language['FLAG_PoP_PoTime']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_fire_fennin_projection"), 'TEXT' => $language['FLAG_PoP_Fennin']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_wind_xegony_projection"), 'TEXT' => $language['FLAG_PoP_Xegony']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_water_coirnav_projection"), 'TEXT' => $language['FLAG_PoP_Coirnav']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_eartha_arbitor_projection"), 'TEXT' => $language['FLAG_PoP_Arbitor']) ); 
$template->assign_block_vars( "head.flags" , array( 'FLAG' => getflag(1, "pop_earthb_rathe"), 'TEXT' => $language['FLAG_PoP_Rathe']) ); 

$template->pparse('flags'); 

$template->destroy; 

//added to monitor database performance 9/28/2014
if (defined('DB_PERFORMANCE')) print dbp_dump_buffer('query');

include("include/footer.php"); 

?>