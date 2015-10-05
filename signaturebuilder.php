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
 
//for getting server locations for bbcode
function GetFileDir($php_self){ 
$filename = explode("/", $php_self); // THIS WILL BREAK DOWN THE PATH INTO AN ARRAY 
for( $i = 0; $i < (count($filename) - 1); ++$i ) { 
$filename2 .= $filename[$i].'/'; 
} 
return $filename2; 
} 


 																																
define('INCHARBROWSER', true);
include_once("include/config.php");
include_once("include/language.php");
include_once("include/functions.php");
include_once("include/global.php");


if (!SERVER_HAS_GD) {
  message_die($language['MESSAGE_ERROR'],$language['MESSAGE_NO_GD']);
}

//drop page
$d_title = " - ".$language['PAGE_TITLES_SIGBUILD'];
include("include/header.php");

$template->set_filenames(array(
  'settings' => 'settings_body.tpl')
);

$template->set_filenames(array(
  'sigbuild' => 'signature_builder_body.tpl')
);

$template->assign_vars(array( 
  'SIGNATURE_DIR' => "http://".$_SERVER['HTTP_HOST'].GetFileDir($_SERVER['PHP_SELF']),
  'CAN_CHANGE_FONT_SIZE' => (SERVER_HAS_FREETYPE) ? "" : "Disabled",
   
  'L_SIGNATURE_BUILDER' => $language['SIGNATURE_SIGNATURE_BUILDER'],
  'L_NAME' => $language['SIGNATURE_NAME'],
  'L_FONT_ONE' => $language['SIGNATURE_FONT_ONE'],
  'L_FONT_SIZE_ONE' => $language['SIGNATURE_FONT_SIZE_ONE'],
  'L_FONT_COLOR_ONE' => $language['SIGNATURE_FONT_COLOR_ONE'],
  'L_FONT_SHADOW_ONE' => $language['SIGNATURE_FONT_SHADOW_ONE'],
  'L_FONT_TWO' => $language['SIGNATURE_FONT_TWO'],
  'L_FONT_SIZE_TWO' => $language['SIGNATURE_FONT_SIZE_TWO'],
  'L_FONT_COLOR_TWO' => $language['SIGNATURE_FONT_COLOR_TWO'],
  'L_FONT_SHADOW_TWO' => $language['SIGNATURE_FONT_SHADOW_TWO'],
  'L_EPIC_BORDER' => $language['SIGNATURE_EPIC_BORDER'],
  'L_STAT_BORDER' => $language['SIGNATURE_STAT_BORDER'],
  'L_STAT_COLOR' => $language['SIGNATURE_STAT_COLOR'],
  'L_STATS' => $language['SIGNATURE_STATS'],
  'L_MAIN_BORDER' => $language['SIGNATURE_MAIN_BORDER'],
  'L_MAIN_BACKGROUND' => $language['SIGNATURE_MAIN_BACKGROUND'],
  'L_MAIN_COLOR' => $language['SIGNATURE_MAIN_COLOR'],
  'L_MAIN_SCREEN' => $language['SIGNATURE_MAIN_SCREEN'],
  'L_PREVIEW' => $language['SIGNATURE_PREVIEW'],
  'L_CREATE' => $language['SIGNATURE_CREATE'],
  'L_BBCODE' => $language['SIGNATURE_BBCODE'],
  'L_HTML' => $language['SIGNATURE_HTML'],
  'L_NEED_NAME' => $language['SIGNATURE_NEED_NAME'])
);


//pull font files
if (SERVER_HAS_FREETYPE) $filehandle = opendir("./fonts");
else $filehandle = opendir("./fontsold");
while (false != ($file = readdir($filehandle))) {
  $name = explode(".", $file);
  if ($name[0])
    $template->assign_block_vars("font", array( 
      'TEXT' => $name[0],
      'VALUE' => $name[0])
    );
}
closedir($filehandle);

//pull epic background files
$template->assign_block_vars("epicborders", array( 
  'TEXT' => $language['SIGNATURE_OPTION_EPIC'],
  'VALUE' => 0)
);
$filehandle = opendir("./images/signatures/epicborders");
while (false != ($file = readdir($filehandle))) {
  $name = explode(".", $file);
  if ($name[0])
    $template->assign_block_vars("epicborders", array( 
      'TEXT' => $name[0], 
      'VALUE' => $name[0])
    );
}
closedir($filehandle);

//pull stat background files
$template->assign_block_vars("statborders", array( 
  'TEXT' => $language['SIGNATURE_OPTION_STAT_ALL'],
  'VALUE' => 0)
);
$filehandle = opendir("./images/signatures/statborders");
while (false != ($file = readdir($filehandle))) {
  $name = explode(".", $file);
  if ($name[0])
    $template->assign_block_vars("statborders", array( 
      'TEXT' => $name[0], 
      'VALUE' => $name[0])
    );
}
closedir($filehandle);

//populate available stats
$stats = array(
  'REGEN', 'FT', 'DS', 'HASTE', 'HP', 'MANA', 'ENDR', 'AC', 'ATK', 'STR', 'STA', 'DEX',
  'AGI', 'INT', 'WIS', 'CHA', 'PR', 'FR', 'MR', 'DR', 'CR', 'WT'
);
$template->assign_block_vars("stats", array( 
  'TEXT' => $language['SIGNATURE_OPTION_STAT_IND'],
  'VALUE' => 0)
);
foreach ($stats as $value) {
    $template->assign_block_vars("stats", array( 
      'TEXT' => $value,
      'VALUE' => $value)
    );
}

//populate borders
$template->assign_block_vars("borders", array( 
  'TEXT' => $language['SIGNATURE_OPTION_BORDER'],
  'VALUE' => 0)
);
$filehandle = opendir("./images/signatures/borders");
while (false != ($file = readdir($filehandle))) {
  $name = explode(".", $file);
  if ($name[0])
    $template->assign_block_vars("borders", array(  
      'TEXT' => $name[0],
      'VALUE' => $name[0])
    );
}
closedir($filehandle);

//populate backgrounds
$template->assign_block_vars("backgrounds", array( 
  'TEXT' => $language['SIGNATURE_OPTION_BACKGROUND'],
  'VALUE' => 0)
);
$filehandle = opendir("./images/signatures/backgrounds");
while (false != ($file = readdir($filehandle))) {
  $name = explode(".", $file);
  if ($name[0])
    $template->assign_block_vars("backgrounds", array(  
      'TEXT' => $name[0],
      'VALUE' => $name[0])
    );
}
closedir($filehandle);

//populate screen filters
$template->assign_block_vars("screens", array( 
  'TEXT' => $language['SIGNATURE_OPTION_SCREEN'],
  'VALUE' => 0)
);
$filehandle = opendir("./images/signatures/screens");
while (false != ($file = readdir($filehandle))) {
  $name = explode(".", $file);
  if ($name[0])
    $template->assign_block_vars("screens", array(  
      'TEXT' => $name[0],
      'VALUE' => $name[0])
    );
}
closedir($filehandle);

//populate font sizes
for ($i = 5; $i <= 40; $i++ ) {
    $template->assign_block_vars("fontsize", array(  
      'TEXT' => $i,
      'VALUE' => $i)
    );
}

$template->pparse('sigbuild');

$template->destroy;


//added to monitor database performance 9/28/2014
if (defined('DB_PERFORMANCE')) print dbp_dump_buffer('query');

include("include/footer.php");

?>