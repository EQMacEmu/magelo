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
 *   February 24, 2014 - Changed items to png files (Maudigan c/o Warmonger)
 *   September 26, 2014 - Maudigan
 *      made STR/STA/DEX/etc lowercase to match the db column names
 *      Updated character table name
 *      rewrote the code that pulls guild name/rank
 *      altered character profile initialization to remove redundant query
 ***************************************************************************/


 
 
define('INCHARBROWSER', true);
include_once("include/config.php");
include_once("include/global.php");
include_once("include/language.php");
include_once("include/functions.php");
include_once("include/profile.php");
include_once("include/itemclass.php");
include_once("include/statsclass.php");
include_once("include/calculatestats.php");

//exit now and post message if server doesnt have GD installed
if (!SERVER_HAS_GD) {
  print $language['MESSAGE_NO_GD'];
  exit();
}




//paths where files are saved
$path = array(
  'BACKGROUND'	=> 'images/signatures/backgrounds/%s.png',
  'BORDER' 	=> 'images/signatures/borders/%s.png',
  'SCREEN' 	=> 'images/signatures/screens/%s.png',
  'STATBORDER' 	=> 'images/signatures/statborders/%s.png',
  'EPICBORDER' 	=> 'images/signatures/epicborders/%s.png',
  //'EPIC' 	=> 'images/items/item_%s.gif',                          //removed 2/24/2014
  'EPIC' 	=> 'images/items/item_%s.png',                          //added 2/24/2014
  'FONT' 	=> (SERVER_HAS_FREETYPE) ? 'fonts/%s.ttf' : 'fontsold/%s.gdf'
);

//convert passed hex color to RGB
function HexToRGB($hex) {		
	$hex = ereg_replace("#", "", $hex);		
	$color = array(); 		
	if(strlen($hex) == 3) {			
	  $color['r'] = hexdec(substr($hex, 0, 1) . $r);			
	  $color['g'] = hexdec(substr($hex, 1, 1) . $g);			
	  $color['b'] = hexdec(substr($hex, 2, 1) . $b);		
	}		
	else if(strlen($hex) == 6) {			
	  $color['r'] = hexdec(substr($hex, 0, 2));			
	  $color['g'] = hexdec(substr($hex, 2, 2));			
	  $color['b'] = hexdec(substr($hex, 4, 2));		
	} 		
	return $color;	
}


//get our starting values from _GET or presets
// several parameters are '-' delimited since mod_rewrite can only handle 9 parameters
$defaultcolor   = array( 'r'=>255, 'g'=>255, 'b'=>255 );
$getone = explode("-", $_GET['one']);
$gettwo = explode("-", $_GET['two']);
$getstat = explode("-", $_GET['stat']);
$getbackground = explode("-", $_GET['background']);

$fontone	= ($getone[0]) ? sprintf($path['FONT'], $getone[0]) : sprintf($path['FONT'], "roman") ;
$sizeone	= ($getone[1]) ? $getone[1] : 20;
$colorone	= ($getone[2]) ? HexToRGB($getone[2]) : $defaultcolor;
$shadowone	= ($getone[3]) ? 1 : 0;

$fonttwo	= ($gettwo[0]) ? sprintf($path['FONT'], $gettwo[0]) : sprintf($path['FONT'], "roman") ;
$sizetwo	= ($gettwo[1]) ? $gettwo[1] : 10;
$colortwo	= ($gettwo[2]) ? HexToRGB($gettwo[2]) : $defaultcolor;
$shadowtwo	= ($gettwo[3]) ? 1 : 0;

$epicbg		= ($_GET['epic']) ? sprintf($path['EPICBORDER'], $_GET['epic']) : false ;

$statdisplay 	= array();
$statdisplay[0] = ($getstat[2]) ? $getstat[2] : false ;
$statdisplay[1] = ($getstat[3]) ? $getstat[3] : false ;
$statdisplay[2] = ($getstat[4]) ? $getstat[4] : false ;
$statdisplay[3] = ($getstat[5]) ? $getstat[5] : false ;
$statdisplay[4] = ($getstat[6]) ? $getstat[6] : false ;
$statcolor	= ($getstat[1]) ? HexToRGB($getstat[1]) : $defaultcolor;
$statbg		= ($getstat[0]) ? sprintf($path['STATBORDER'], $getstat[0]) : false ;

$background 	= ($getbackground[1]) ? sprintf($path['BACKGROUND'], $getbackground[1]) : false;
$bgcolor	= ($getbackground[0]) ? HexToRGB($getbackground[0]) : $defaultcolor;
$screen 	= ($getbackground[2]) ? sprintf($path['SCREEN'], $getbackground[2]) : false;

$border 	= ($_GET['border']) ? sprintf($path['BORDER'], $_GET['border']) : false;


//starting points of text
$line_start_x = 15;
$line_start_y = 12;

//stats constants, starting points, etc
$stat_start_x = 16;
$stat_start_y = 70;
$stat_step_x = 97;
$stat_width = 80;
$stat_height = 18;
$stat_text_y = 2;
$stat_text_x = 8;

//epic constants
$epic_x = 420;
$epic_y = 15;
$epic_icon_offset = 0;
$epic_width_height = 40;
$epic_icon_width_height = 40;

$signaturewidth = 500;
$signatureheight = 100;



//used for outputting errors since any text output will cause broken image links
//should be similar to message_die in functions.php
function png_message_die($error, $message) {
	global $signaturewidth, $signatureheight, $defaultcolor;
	$error_image = imagecreatetruecolor($signaturewidth, $signatureheight);
	$error_color = imagecolorallocate($error_image, $defaultcolor['r'], $defaultcolor['g'], $defaultcolor['b']);
	imagestring($error_image, 5, 10, 30, $error, $error_color);
	imagestring($error_image, 2, 10, 50, $message, $error_color);	
	header("Content-Type: image/png"); 
	imagepng($error_image); 
	ImageDestroy($error_image);
}


//if character name isnt provided post error message and exit
if(!$_GET['char']) png_message_die($language['MESSAGE_ERROR'],$language['MESSAGE_NO_CHAR']);
else $charName = $_GET['char'];


//character initializations - rewritten 9/28/2014
$char = new profile($charName); //the profile class will sanitize the character name
$charID = $char->char_id(); 
$mypermission = GetPermissions($char->GetValue('gm'), $char->GetValue('anon'), $char->char_id());

//block view if user level doesnt have permission
if ($mypermission['signatures']) png_message_die($language['MESSAGE_ERROR'],$language['MESSAGE_ITEM_NO_VIEW']);


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

//load guild name
//rewritten because the guild id was removed from the profile 9/26/2014
$query = "SELECT guilds.name, guild_members.rank 
          FROM guilds
          JOIN guild_members
          ON guilds.id = guild_members.guild_id
          WHERE guild_members.char_id = $charID LIMIT 1";
if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
$results = mysql_query($query);
if(mysql_num_rows($results) != 0)
{ 
   $row = mysql_fetch_array($results);
   $guild_name = $row['name'];
   $guild_rank = $guildranks[$row['rank']];
}


//place where all the items stats are added up
$itemstats = new stats();

																																						
// pull characters inventory slotid is loaded as
// "myslot" since items table also has a slotid field.
$query = "SELECT items.*, character_inventory.augslot1, character_inventory.augslot2, character_inventory.augslot3, character_inventory.augslot4, character_inventory.augslot5, character_inventory.slotid AS myslot from items, character_inventory where character_inventory.id = '$charID' AND  items.id = character_inventory.itemid";
if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
$results = mysql_query($query);
while ($row = mysql_fetch_array($results)) {
  $tempitem = new item($row);
  for ($i = 1; $i <= 5; $i++) {
    if ($row["augslot".$i]) {
      $query = "SELECT * from items where id = ".$row["augslot".$i]." LIMIT 1";
      if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
      $augresults = mysql_query($query);
      $augrow = mysql_fetch_array($augresults);
      $itemstats->additem($augrow);
    }
  }

  if ($tempitem->type() == EQUIPMENT)
    $itemstats->additem($row);
  
}

;
if ($epicbg) {
   $query = "SELECT items.icon, items.id FROM items 
             JOIN titles ON items.id = titles.item_id
             JOIN character_inventory ON items.id = character_inventory.itemid
             WHERE character_inventory.id = $charID
               AND titles.class = $class
             ORDER BY items.id DESC
             LIMIT 0, 1;";
   if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
    $results = mysql_query($query);
    if ($row = mysql_fetch_array($results)) $epicicon = sprintf($path['EPIC'], $row['icon']);
}



$chardata = array(
  'FIRST_NAME' => $name,
  'LAST_NAME' => $last_name,
  'TITLE' => $title,
  'GUILD_NAME' => $guild_name,
  'GUILD_RANK' => $guild_rank,
  'LEVEL' => $level,
  'CLASS' => $dbclassnames[$class],
  'RACE' => $dbracenames[$race],
  'DEITY' => $dbdeities[$deity],
);

$stats = array(
  'REGEN' => $itemstats->regen(),
  'FT' => $itemstats->FT(),
  'DS' => $itemstats->DS(),
  'HASTE' => $itemstats->haste()."%",
  'HP' => GetMaxHP($level,$class,($baseSTA+$itemstats->STA()),$itemstats->hp()),
  'MANA' => GetMaxMana($level,$class,($baseINT+$itemstats->INT()),($baseWIS+$itemstats->WIS()),+$itemstats->mana()),
  'ENDR' => GetMaxEndurance(($baseSTR+$itemstats->STR()),($baseSTA+$itemstats->STA()),($baseDEX+$itemstats->DEX()),($baseAGI+$itemstats->AGI()),$level,$itemstats->endurance()),
  'AC' => GetMaxAC(($baseAGI+$itemstats->AGI()), $level, $defense, $class, $itemstats->AC(), $race),
  'ATK' => GetMaxAtk($itemstats->attack(), ($baseSTR+$itemstats->STR()), $offense),
  'STR' => ($baseSTR+$itemstats->STR()),
  'STA' => ($baseSTA+$itemstats->STA()),
  'DEX' => ($baseDEX+$itemstats->DEX()),
  'AGI' => ($baseAGI+$itemstats->AGI()),
  'INT' => ($baseINT+$itemstats->INT()),
  'WIS' => ($baseWIS+$itemstats->WIS()),
  'CHA' => ($baseCHA+$itemstats->CHA()),
  'PR' => (PRbyRace($race)+$PRbyClass[$class]+$itemstats->PR()),
  'FR' => (FRbyRace($race)+$FRbyClass[$class]+$itemstats->FR()),
  'MR' => (MRbyRace($race)+$MRbyClass[$class]+$itemstats->MR()),
  'DR' => (DRbyRace($race)+$DRbyClass[$class]+$itemstats->DR()),
  'CR' => (CRbyRace($race)+$CRbyClass[$class]+$itemstats->CR()),
  'WT' => round($itemstats->WT()/10)
);



/**************************************************
**  DO NOT USE png_message_die past this point   **
**************************************************/

//create image
$image = imagecreatetruecolor($signaturewidth, $signatureheight);

//apply background color
$bgcolor = imagecolorallocate($image, $bgcolor['r'], $bgcolor['g'], $bgcolor['b']);
imagefilledrectangle($image, 0, 0, $signaturewidth - 1, $signatureheight - 1, $bgcolor);

//apply background image
if ($background) {
	$tempimage = imagecreatefrompng($background);
	imagecopy($image, $tempimage, 0, 0, 0, 0, $signaturewidth, $signatureheight);
	imagedestroy($tempimage);
}

//aply alpha screen to bg
if ($screen) {
	$tempimage = imagecreatefrompng($screen);
	imagecopy($image, $tempimage, 0, 0, 0, 0, $signaturewidth, $signatureheight);
	imagedestroy($tempimage);
}


//drop epic on if an icon was set earlier
if($epicbg && $epicicon) {
	$tempimage = imagecreatefrompng($epicbg);
	imagecopy($image, $tempimage, $epic_x, $epic_y, 0, 0, $epic_width_height, $epic_width_height);
	imagedestroy($tempimage);
	//$tempimage = imagecreatefromgif($epicicon);                   //removed 2/24/2014
	$tempimage = imagecreatefrompng($epicicon);                     //added 2/24/2014
	imagecopy($image, $tempimage, $epic_x + $epic_icon_offset, $epic_y + $epic_icon_offset, 0, 0, $epic_icon_width_height , $epic_icon_width_height );
	imagedestroy($tempimage);
}

// for drawing the 3 lines of text
$black = imagecolorallocate($image, 0, 0, 0);
function drawtext( $image, $text, $size, $font, $color, $offsetx, $offsety, $shadow) {
	global $black;
	$color = imagecolorallocate($image, $color['r'], $color['g'], $color['b']);
	
	if (SERVER_HAS_FREETYPE) {
	  $bbox = imagettfbbox($size, 0, $font, $text);	
  	  $height = abs($bbox[1]) + abs($bbox[5]);
	  $width = abs($bbox[4]) + abs($bbox[0]);
	  $x = $offsetx + abs($bbox[0]);
	  $y = $offsety + abs($bbox[5]);
	  if ($shadow) imagettftext($image, $size, 0, $x+1, $y+1, $black, $font, $text);
	  imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
	}
	else {
	  $hfont = ImageLoadFont($font);
	  $width = ImageFontWidth($hfont) * strlen($text);
	  $height = ImageFontHeight($hfont);
	  if ($shadow) imagestring($image, $hfont, $offsetx+1, $offsety+1, $text, $black);
	  imagestring($image, $hfont, $offsetx, $offsety, $text, $color);
	}
	return array( 'HEIGHT' => $height, 'WIDTH' => $width );
}


//draw line one
$ttftext = $chardata['FIRST_NAME']." ".$chardata['LAST_NAME'];
$ttfreturn = drawtext( $image, $ttftext, $sizeone, $fontone, $colorone, $line_start_x, $line_start_y, $shadowone);
$line_start_y += $ttfreturn['HEIGHT'];


//draw line two
$ttftext = $chardata['LEVEL']." ".$chardata['RACE']." ".$chardata['CLASS'];
$ttfreturn = drawtext( $image, $ttftext, $sizetwo, $fonttwo, $colortwo, $line_start_x, $line_start_y, $shadowtwo);
$line_start_y += $ttfreturn['HEIGHT'];

//draw line three
if ($chardata['GUILD_NAME']) {
	$ttftext = $chardata['GUILD_RANK']." of ".$chardata['GUILD_NAME'];
	$ttfreturn = drawtext( $image, $ttftext, $sizetwo, $fonttwo, $colortwo, $line_start_x, $line_start_y, $shadowtwo);
	$line_start_y += $ttfreturn['HEIGHT'];
}

// draw stats and boxes
if ($statbg) {
  $i = 0;
  $statcolor = imagecolorallocate($image, $statcolor['r'], $statcolor['g'], $statcolor['b']);
  foreach ($statdisplay as $key => $value) {
	if ($value > "")
	if (array_key_exists($value, $stats)) {
	  $stattext = $value." ".$stats[$value];
	  $tempimage = imagecreatefrompng($statbg);
	  imagecopy($image, $tempimage, $stat_start_x + $stat_step_x * $i , $stat_start_y, 0, 0, $stat_width, $stat_height);
	  imagestring($image, 2, $stat_text_x + $stat_start_x + $stat_step_x * $i , $stat_text_y + $stat_start_y, $stattext, $statcolor);
	  imagedestroy($tempimage);
	}
	$i++;
  }
}

if ($border) {
	$tempimage = imagecreatefrompng($border);
	imagecopy($image, $tempimage, 0, 0, 0, 0, $signaturewidth, $signatureheight);
	imagedestroy($tempimage);
}

header("Content-Type: image/png"); 
imagepng($image); 
ImageDestroy($image);


?> 