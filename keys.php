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
 *   September 26 2014 
 *      cleaned up double carriage returns through whole file 
 *      update character table name
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
if ($mypermission['keys'] && !isAdmin()) message_die($language['MESSAGE_ERROR'],$language['MESSAGE_ITEM_NO_VIEW']);

//============================= 
//grab the keys the user has
//============================= 
$query = "SELECT k.item_id, i.Name AS 'key' FROM character_keyring AS k LEFT JOIN items AS i ON i.id = k.item_id WHERE k.id = $charID ORDER BY i.Name;";
if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
$results = $game_db->query($query);
if (!numRows($results)) message_die($language['KEYS_KEY']." - ".$name,$language['MESSAGE_NO_KEYS']);

//drop page 
$d_title = " - ".$name.$language['PAGE_TITLES_KEYS']; 
include("include/header.php"); 

//build body template 
$template->set_filenames(array( 
  'keys' => 'keys_body.tpl') 
); 

$template->assign_vars(array(  
  'NAME' => $name,

  'L_KEY' => $language['KEYS_KEY'],
  'L_KEYS' => $language['BUTTON_KEYS'],
  'L_AAS' => $language['BUTTON_AAS'],
  'L_FLAGS' => $language['BUTTON_FLAGS'],
  'L_SKILLS' => $language['BUTTON_SKILLS'],
  'L_CORPSE' => $language['BUTTON_CORPSE'],
  'L_FACTION' => $language['BUTTON_FACTION'],
  'L_BOOKMARK' => $language['BUTTON_BOOKMARK'],
  'L_INVENTORY' => $language['BUTTON_INVENTORY'],
  'L_CHARMOVE' => $language['BUTTON_CHARMOVE'],
  'L_DONE' => $language['BUTTON_DONE'])
);

foreach($results AS $row) {
    $template->assign_block_vars("keys", array( 
      'KEY' => $row['key'],
      'ITEM_ID' => $row["item_id"])
    );
}

$template->pparse('keys');

$template->destroy;

//added to monitor database performance 9/28/2014
if (defined('DB_PERFORMANCE')) print dbp_dump_buffer('query');

include("include/footer.php");

?>
