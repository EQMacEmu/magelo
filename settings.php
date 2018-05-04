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
 *   September 28, 2014 - Maudigan
 *      added code to monitor database performance
 ***************************************************************************/
 
define('INCHARBROWSER', true);
include_once("include/config.php");
include_once("include/language.php");
include_once("include/functions.php");
include_once("include/global.php");

// keys match the permissions array in 
// config.php for easy foreach looping
$l_permission = array ( 
  'inventory' 		=> $language['SETTINGS_INVENTORY'],
  'coininventory' 	=> $language['SETTINGS_ICOIN'], 
  'coinbank' 		=> $language['SETTINGS_BCOIN'],
  'bags'		=> $language['SETTINGS_BAGS'],
  'bank'		=> $language['SETTINGS_BANK'],
  'corpses'		=> $language['SETTINGS_CORPSES'],
  'flags'		=> $language['SETTINGS_FLAGS'],
  'AAs'			=> $language['SETTINGS_AAS'],
  'keys'		=> $language['SETTINGS_KEYS'],
  'factions'		=> $language['SETTINGS_FACTIONS'],
  'advfactions'		=> $language['SETTINGS_ADVFACTIONS'],    
  'skills'		=> $language['SETTINGS_SKILLS'],
  'languageskills'	=> $language['SETTINGS_LSKILLS'],
  'signatures'		=> $language['SETTINGS_SIGNATURES']
);

$l_users = array (
  'ALL' 		=> $language['SETTINGS_USERS_ALL'],
  'ROLEPLAY' 		=> $language['SETTINGS_USERS_RP'],
  'ANON' 		=> $language['SETTINGS_USERS_ANON'],
  'GM' 			=> $language['SETTINGS_USERS_GM'],
  'PUBLIC' 		=> $language['SETTINGS_USERS_PUBLIC'],
  'PRIVATE' 		=> $language['SETTINGS_USERS_PRIVATE'],
);

//drop page
$d_title = " - ".$language['PAGE_TITLES_SETTINGS'];
include("include/header.php");

$template->set_filenames(array(
  'settings' => 'settings_body.tpl')
);
//column heads
$template->assign_block_vars( "rows" , array(
  'UNUSED' => "")
);
$template->assign_block_vars( "rows.cols" , array(
  'VALUE' => "" )
);  
foreach ($l_users as $key => $value) {
  $template->assign_block_vars( "rows.cols" , array(
   'VALUE' => $value )
  );    
}

//column data
foreach ($l_permission as $key => $value) {
  $template->assign_block_vars( "rows" , array(
    'UNUSED' => "")
  );
  $template->assign_block_vars( "rows.cols" , array(
   'VALUE' => $value)
  );   
  foreach ($l_users as $key2 => $value2) {
    $template->assign_block_vars( "rows.cols" , array(
     'VALUE' => ($permissions[$key2][$key]) ? "" : "x" )
    );    
  }
}

$template->assign_vars(array(  
  'S_RESULTS' => $numToDisplay,
  'S_HIGHLIGHT_GM' => (($highlightgm)?$language['SETTINGS_ENABLED']:$language['SETTINGS_DISABLED']),
  'S_BAZAAR' => (($blockbazaar)?$language['SETTINGS_DISABLED']:$language['SETTINGS_ENABLED']),
  'S_CHARMOVE' => (($blockcharmove)?$language['SETTINGS_DISABLED']:$language['SETTINGS_ENABLED']),

  'L_RESULTS' => $language['SETTINGS_RESULTS'],
  'L_CHARMOVE' => $language['SETTINGS_CHARMOVE'],
  'L_HIGHLIGHT_GM' => $language['SETTINGS_HIGHLIGHT_GM'],
  'L_BAZAAR' => $language['SETTINGS_BAZAAR'],
  'L_SETTINGS' => $language['SETTINGS_SETTINGS'],
  'L_BACK' => $language['BUTTON_BACK'])
);

$template->pparse('settings');

$template->destroy;

//added to monitor database performance 9/28/2014
if (defined('DB_PERFORMANCE')) print dbp_dump_buffer('query');

include("include/footer.php");

?>