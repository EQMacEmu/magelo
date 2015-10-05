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
 ***************************************************************************/
 
 
 


if ( !defined('INCHARBROWSER') )
{
	die("Hacking attempt");
}

/****************************************************
**                  SQL Settings                   **
**  Database account only needs SELECT and UPDATE  **
**  privelages                                     **
****************************************************/
$db     =   "peqmac";
$host   =   "127.0.0.1";
$user   =   "magelo";
$pass   =   "changeme";
/***************************************************/


/*****************************************************
**                  General Settings		    **
*****************************************************/
$numToDisplay = 50 ;//search results per page                 
$highlightgm  = 1  ;//highlight GM inventories 0=off 1=on
$blockbazaar  = 1  ;//disable bazaar 0=on 1=off     
    
     
/*****************************************************
**                  Title  settings                 **
*****************************************************/
$mytitle      =    "Character Browser"; 
$subtitle     =    " � Be Nosey";
$version      =    "2.41"; 


/*****************************************************
**   The following 5 settings are for the dynamic   **
**   title image(title.php). If you have freetype   **
**   installed you use a fontname from the /fonts   **
**   directory. If not use a fontname from the      **
**   /fontsold directory. Don't include the file    **
**   extension. Contact your system admin or use    **
**   php_info() to find out if you have freetype.   **
**   If you don't have freetype fontsize wont be    **
**   changable.                                     **
*****************************************************/
$titlefont    =    "colchester";  
$titlefontR   =    "35";
$titlefontG   =    "35";
$titlefontB   =    "1"; 
$titlefontsize=    "45";


/*****************************************************
**  Character mover settings			    **
** assuming the layout of zones is understandable   **
** ask for help if needed.                          **
*****************************************************/
$blockcharmove = 1; //disable charmove 0=on 1=off
$charmovezones = array(
 // 'nexus'	=> array( 'x' => 0, 'y' => 0, 'z' => 0 ),
 // 'oot'		=> array( 'x' => -9199, 'y' => 390, 'z' => 5 ),
    'ecommons' => array( 'x' => 846, 'y' => -1067, 'z' => -2 ),
);  


/*****************************************************
**  permissions for each user level. ALL applies to **
**  users other than ROLEPLAY, ANON, and GM         **
**  PUBLIC/PRIVATE are turned on using a quest      **
**  variable. For more information there is a       **
**  readme and example quest file in the Quest      **
**  Permissions directory.                          **
**      0 = display       1 = block                 **
*****************************************************/
$permissions = array(
'ALL' => array( 
    'inventory'         => 0,
    'coininventory'     => 1,
    'coinbank'          => 1,
    'bags'              => 1,
    'bank'              => 1,
    'corpses'           => 1,
    'flags'             => 0,
    'AAs'               => 0,
    'factions'          => 0,
    'advfactions'       => 0,
    'skills'            => 1,
    'languageskills'    => 0,
    'keys'              => 0,
    'signatures'        => 0),
'ROLEPLAY' => array(
    'inventory'         => 0,
    'coininventory'     => 1,
    'coinbank'          => 1,
    'bags'              => 1,
    'bank'              => 1,
    'corpses'           => 1,
    'flags'             => 0,
    'AAs'               => 0,
    'factions'          => 0,
    'advfactions'       => 0,
    'skills'            => 1,
    'languageskills'    => 0,
    'keys'              => 0,
    'signatures'        => 0),
'ANON' => array(
    'inventory'         => 0,
    'coininventory'     => 1,
    'coinbank'          => 1,
    'bags'              => 1,
    'bank'              => 1,
    'corpses'           => 1,
    'flags'             => 0,
    'AAs'               => 0,
    'factions'          => 0,
    'advfactions'       => 0,
    'skills'            => 1,
    'languageskills'    => 0,
    'keys'              => 0,
    'signatures'        => 0),
'GM' => array(
    'inventory'         => 1,
    'coininventory'     => 1,
    'coinbank'          => 1,
    'bags'              => 1,
    'bank'              => 1,
    'corpses'           => 1,
    'flags'             => 1,
    'AAs'               => 1,
    'factions'          => 1,
    'advfactions'       => 1,
    'skills'            => 1,
    'languageskills'    => 1,
    'keys'              => 1,
    'signatures'        => 0),
'PUBLIC' => array(
    'inventory'         => 0,
    'coininventory'     => 1,
    'coinbank'          => 1,
    'bags'              => 1,
    'bank'              => 1,
    'corpses'           => 1,
    'flags'             => 0,
    'AAs'               => 0,
    'factions'          => 0,
    'advfactions'       => 0,
    'skills'            => 1,
    'languageskills'    => 0,
    'keys'              => 0,
    'signatures'        => 0),
'PRIVATE' => array(
    'inventory'         => 0,
    'coininventory'     => 1,
    'coinbank'          => 1,
    'bags'              => 1,
    'bank'              => 1,
    'corpses'           => 1,
    'flags'             => 0,
    'AAs'               => 0,
    'factions'          => 0,
    'advfactions'       => 0,
    'skills'            => 1,
    'languageskills'    => 0,
    'keys'              => 0,
    'signatures'        => 0)
);
/*****************************************************/







/*****************************************************
**  If you use advertising banner paste the html    **
**  into this variable and it will be a banner      **
**  at the top of every page. Add <center> tags     **
**  if you want it centered; default is left align. **
*****************************************************/
$adscript = "";
/****************************************************/




/****************************************************
**                 DATABASE STATS                  **
**  uncommenting this will dump database           **
**  statistics at the bottom of each page          **
**                                                 **
**  WARNING: this will be publicly viewable        **
****************************************************/
//define('DB_PERFORMANCE',true);
/****************************************************/



/******************************************************
*******************************************************
****              STOP EDITING HERE                ****
*******************************************************
******************************************************/

// Non-configurable stuff
mysql_connect($host, $user, $pass) or die("<center>The database host/user/password supplied were invalid.</center>");
mysql_select_db("$db") or die("<center>Could not find designated database.</center>");

?>