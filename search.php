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
 *   September 28, 2014 - Maudigan
 *      added code to destroy template when finished
 *      added code to monitor database performance
 ***************************************************************************/
 
define('INCHARBROWSER', true);
include_once("include/config.php");
include_once("include/debug.php");
include_once("include/sql.php");
include_once("include/global.php");
include_once("include/language.php");
include_once("include/functions.php");

global $game_db;

$start		= (($_GET['start'])		? $_GET['start'] 	: "0");
$orderby	= (($_GET['orderby'])		? $_GET['orderby'] 	: "name");
$direction	= (($_GET['direction']=="DESC")	? "DESC" 		: "ASC");
$name	        = $_GET['name'];
$guild	        = $_GET['guild'];

//build baselink
$baselink="search.php?name=$name&guild=$guild";

//security for injection attacks
if (!IsAlphaSpace($name)) message_die($language['MESSAGE_ERROR'],$language['MESSAGE_NAME_ALPHA']);
if (!IsAlphaSpace($guild)) message_die($language['MESSAGE_ERROR'],$language['MESSAGE_GUILD_ALPHA']);
if (!IsAlphaSpace($orderby)) message_die($language['MESSAGE_ERROR'],$language['MESSAGE_ORDER_ALPHA']);
if (!is_numeric($start)) message_die($language['MESSAGE_ERROR'],$language['MESSAGE_START_NUMERIC']);

// update character table name 9/26/2014
$select = "SELECT character_data.class, character_data.level, character_data.name, guilds.name AS guildname 
           FROM character_data 
           LEFT JOIN guild_members
           ON character_data.id = guild_members.char_id 
           LEFT JOIN guilds
           ON guilds.id = guild_members.guild_id "; 

$where = "";
$divider = "WHERE ";
if ($name) {
  $where .= $divider."character_data.name LIKE '%".str_replace("_", "%", str_replace(" ","%",$name))."%'"; // update character table name 9/26/2014
  $divider = "AND ";
}
if ($guild) {
  $where .= $divider."guilds.name LIKE '%".str_replace("_", "%", str_replace(" ","%",$guild))."%'";
    $divider = "AND ";
}

$query = $select.$where;
if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
$results = $game_db->query($query);
if (!numRows($results)) message_die($language['MESSAGE_ERROR'],$language['MESSAGE_NO_RESULTS']);

$query = $select.$where."ORDER BY $orderby $direction LIMIT $start, $numToDisplay;";
if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
$results = $game_db->query($query);

//drop page
$d_title = " - ".$language['PAGE_TITLES_SEARCH'];
include("include/header.php");

//build body template
$template->set_filenames(array(
  'body' => 'search_body.tpl')
);

$template->assign_vars(array(  
  'ORDER_LINK' => $baselink."&start=$start&direction=".(($direction=="ASC") ? "DESC":"ASC"), 
  'PAGINATION' => generate_pagination("$baselink&orderby=$orderby&direction=$direction", $totalchars, $numToDisplay, $start, true),
  'L_RESULTS' => $language['SEARCH_RESULTS'],
  'L_NAME' => $language['SEARCH_NAME'],
  'L_LEVEL' => $language['SEARCH_LEVEL'],
  'L_CLASS' => $language['SEARCH_CLASS'],)
);

if(numRows($results) > 0) {
    foreach($results AS $row) {
    $template->assign_block_vars("characters", array( 
      'CLASS' => $dbclassnames[$row["class"]],	   
      'LEVEL' => $row["level"],
      'NAME' => $row["name"],
      'GUILD_NAME' => (($row["guildname"]) ? "&lt;".$row["guildname"]."&gt;":"") )
    );
  }
}

$template->pparse('body');

//added this, it was forgotten originall 9/28/2014
$template->destroy;

//added to monitor database performance 9/28/2014
if (defined('DB_PERFORMANCE')) print dbp_dump_buffer('query');

//dump footer
include("include/footer.php");

?>