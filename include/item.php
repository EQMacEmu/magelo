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
 *   February 24, 2014 - Added backstab damage (Maudigan c/o Kinglykrab)
 *   February 24, 2014 - Spelling--uncommented (Maudigan c/o Kinglykrab)
 *   February 25, 2014 - added heroic and aug types (Maudigan c/o Kinglykrab)
 *   February 25, 2014 - fixed maxcharges condition (Maudigan c/o Kinglykrab)
 *   September 28, 2014 - Maudigan
 *      added code to monitor database performance
 ***************************************************************************/

if (!defined('INCHARBROWSER')) { die("Hacking attempt"); }
include_once('global.php');
//include_once("./config.php");
//include_once("./debug.php");
//include_once("./sql.php");

global $game_db;

/** Runs '$query' and returns the value of '$field' of the first (arbitrarily) found row
 *  If no row is selected by '$query', returns an emty string
 */
function GetFieldByQuery($field, $query) {
	global $game_db;
	if (defined('DB_PERFORMANCE')) { dbp_query_stat('query', $query); } //added 9/28/2014
	$QueryResult = $game_db->query($query) or message_die('mysql.php','GetFiedByQuery',$query,mysql_error());
	if(numRows($QueryResult) > 0) {
		$rows = fetchRows($QueryResult) or message_die('mysql.php','GetFiedByQuery',"MYSQL_FETCH_ARRAY",mysql_error());
		$Result = $rows[0][$field];
	}
	else {
		$Result = "";
	}
	return $Result;
}

function strtolower_ucfirst($txt) {
	if ($txt=="") { return $txt; }
	else {
		$txt=strtolower($txt);
		$txt[0]=strtoupper($txt[0]);
		return $txt;
	}
}

/** Returns the list of slot names '$val' corresponds to (as a bit field) */
function getslots($val)
{ global $dbslots;
	reset($dbslots);
	do
	{ $key=key($dbslots);
		if($key <= $val)
		{ $val -= $key;
			$Result = current($dbslots).$v.$Result;
			$v=" ";
		}
	} while (next($dbslots));
	return $Result;
}

function getraces($val) {
	global $dbraces;
	$all_races = 14;
	if ($val == (2**$all_races) - 1) {
		return $dbraces[$all_races];
	} else if ($val == 0 ) {
		return "None";
	}
	$Result = array();
	for ($race = 0; $race < $all_races; $race++) {
		if ($val & (2**$race)) {
			array_push($Result, $dbraces[$race]);
		}
	}
	return implode(" ", $Result);
}

function getclasses($val) {
	global $dbiclasses;
	$all_classes = 15;
	if ($val == (2**$all_classes) - 1) {
		return $dbiclasses[$all_classes];
	} else if ($val == 0 ) {
		return "None";
	}
	$Result = array();
	for ($class = 0; $class < $all_classes; $class++) {
		if ($val & (2**$class)) {
			array_push($Result, $dbiclasses[$class]);
		}
	}
	return implode(" ", $Result);
}

function getdeities($val) {
	global $dbideities;
	reset($dbideities);
	do {
		$key=key($dbideities);
		if ($key<=$val) { $val-=$key; $res.=$v.current($dbideities); $v=", "; }
	} while (next($dbideities));
	return $res;
}

//added getautype() function 2/25/2014
function getaugtype($val) {
	global $augtypes;
	$Result = '';
	$v = '';
	reset($augtypes);
	do
	{
		$key = key($augtypes);
		if($key <= $val) {
			$val -= $key;
			$Result .= $v . current($augtypes);
			$v = ", ";
		}
	}
	while (next($augtypes));
	return $Result;
}

function sign($val) {
	if ($val>0) { return "+$val"; }
	else { return $val; }
}

function getsize($val) {
	switch($val) {
		case 0: return "Tiny"; break;
		case 1: return "Small"; break;
		case 2: return "Medium"; break;
		case 3: return "Large"; break;
		case 4: return "Giant"; break;
		default: return "$val?"; break;
	}
}

/** Returns an items stats formatted for display. */

function GetItem($item) {
	global $dbelements, $dbskills, $dam2h, $dbitypes, $tbspells, $tbraces, $dbbodytypes, $dbbardskills;

	//return buffer, build item here
	$Output = "";
	$tab = "           ";
	$spellurl = 'https://www.takproject.net/allaclone/spell.php?id=';

	// LORE AUGMENT NODROP NORENT MAGIC
	$spaceswitch= "";
	if($item["itemtype"] == 54)  { $Output .= "$spaceswitch AUGMENTATION"; $spaceswitch= " "; }
	if($item["magic"] == 1)      { $Output .= "$spaceswitch MAGIC ITEM";   $spaceswitch= " "; }
	$i = 0;
	if($item["lore"][$i] == "*")   { $i++; $Output .= "$spaceswitch LORE ITEM";    $spaceswitch= " "; }
	if($item["lore"][$i] == "#")   { $Output .= "$spaceswitch ARTIFACT";    $spaceswitch= " "; }
	if($item["nodrop"] == 0)     { $Output .= "$spaceswitch NO TRADE";       $spaceswitch= " "; }
	if($item["norent"] == 0)     { $Output .= "$spaceswitch NO RENT";       $spaceswitch= " "; }
	$Output .= "<br>\n";

	//EXPENDABLE, Charges
	if($item["clicktype"] == 3) { $Output .= $tab."EXPENDABLE "; }
	if($item["clicktype"]>0 && $item["maxcharges"]>0) { $Output .= "Charges: ".$item["maxcharges"]."<br>\n"; }
	// Augmentation type
	if($item["itemtype"] == 54) {
		//if($item["augtype"] > 0) { $Output .= $tab."Augmentation type: ".$item["augtype"]."<br>\n"; }            //removed 2/25/2014
		if($item["augtype"] > 0) { $Output .= $tab."Augmentation type: ".getaugtype($item["augtype"])."<br>\n"; }  //added 2/25/2014
		else { $Output .= $tab."Augmentation type: for all slots<br>\n"; }
	}

	// Slots
	if($item["slots"] > 0) {$Output .= $tab."Slot: ".strtoupper(getslots($item["slots"]))."<br>\n"; }

	// Bag-specific information
	if($item["bagslots"] > 0) {
		$Output .= $tab."Item type: Container<br>\n";
		$Output .= $tab."Number of slots: ".$item["bagslots"]."<br>\n";
		if($item["bagtype"] > 0) { $Output .= $tab."Trade skill container: ".$dbbagtypes[$item["bagtype"]]."<br>\n"; }
		if($item["bagwr"] > 0) { $Output .= $tab."Weight reduction: ".$item["bagwr"]."%<br>\n"; }
		$Output .= $tab."This can hold ".strtoupper(getsize($item["bagsize"]))." and smaller items.<br>\n";
	}

	// Damage/Delay
	if($item["damage"] > 0) {
		$WepSkill = $dbitypes[$item["itemtype"]];
		if ($item["itemtype"]==27) { $WepSkill = "Archery"; }
		$Output .= $tab."Skill: ".$WepSkill." ";
		$Output .= "Atk Delay: ".$item["delay"]."<br>\n".$tab."DMG:  ".$item["damage"]."";
		switch($item["itemtype"]) {
			case 0: // 1HS
			case 2: // 1HP
			case 3: // 1HB
			case 45: // H2H
			case 27: //Arrow
				$dmgbonus = 13; // floor((65-25)/3)  main hand
				$Output .= $tab."Dmg bonus:$dmgbonus <i>(lvl 65)</i>";
				if($item["ac"]==0)  { $Output .= "<br>\n"; }
				break;
			case 5: //archery
			case 1: // 2hs
			case 4: // 2hb 
			case 35: // 2hp
				$dmgbonus = $dam2h[$item["delay"]];
				$Output .= $tab."Dmg bonus: $dmgbonus <i>(lvl 65)</i>";
				if($item["ac"]==0) { $Output .= "<br>\n"; }
				break;
		}
	}

	//backstab dmg, added 2/24/2014
	if($item["backstabdmg"] > 0)
	{
		$Output .= "Backstab Damage: " . $item["backstabdmg"] . "<br>\n";
	}

	//AC
	if($item["ac"] != 0) { $Output .= $tab." AC: ".$item["ac"]."<br>\n"; }


	// Elemental DMG
	if (($item["elemdmgtype"]>0) AND ($item["elemdmgamt"]!=0)) { $Output .= $tab.strtolower_ucfirst($dbelements[$item["elemdmgtype"]])." DMG: ".sign($item["elemdmgamt"])."<br>\n"; }

	//Bane DMG
	if (($item["banedmgrace"]>0) AND ($item["banedmgraceamt"]!=0)) {
		$Output .= $tab."Bane DMG: ";
		$Output .= GetFieldByQuery("name","SELECT name FROM $tbraces WHERE id=".$item["banedmgrace"]);
		$Output .= " ".sign($item["banedmgraceamt"])."<br>\n";
	}
	if (($item["banedmgbody"]>0) AND ($item["banedmgamt"]!=0)) {
		$Output .= $tab."Bane DMG: ".$dbbodytypes[$item["banedmgbody"]];
		$Output .= " ".sign($item["banedmgamt"])."<br>\n";
	}

	// Skill Mods
	if (($item["skillmodtype"]>0) AND ($item["skillmodvalue"]!=0)) { $Output .= $tab."Skill Mod: ".strtolower_ucfirst($dbskills[$item["skillmodtype"]])." ".sign($item["skillmodvalue"])."%<br>\n"; }

	//item proc
	if (($item["proceffect"]>0) AND ($item["proceffect"]<65535)) {
		$Output .= $tab."Effect: <a href='".$spellurl.$item["proceffect"]."' target='_blank'>".GetFieldByQuery("name","SELECT name FROM $tbspells WHERE id=".$item["proceffect"])."</a>";
		$Output .= "&nbsp;(Combat)";
		$Output .= " <i>(Level ".$item["proclevel2"].")</i>";
		$Output .= "<br>\n";
	}

	// worn effect
	if (($item["worneffect"]>0) AND ($item["worneffect"]<65535)) {
		$Output .= $tab."Effect: <a href='".$spellurl.$item["worneffect"]."' target='_blank'>".GetFieldByQuery("name","SELECT name FROM $tbspells WHERE id=".$item["worneffect"])."</a>";
		$Output .= "&nbsp;(Worn)";
		$Output .= " <i>(Level ".$item["wornlevel"].")</i>";
		$Output .= "<br>\n";
	}

	// focus effect
	if (($item["focuseffect"]>0) AND ($item["focuseffect"]<65535)) {
		$Output .= $tab."Focus: <a href='".$spellurl.$item["focuseffect"]."' target='_blank'>".GetFieldByQuery("name","SELECT name FROM $tbspells WHERE id=".$item["focuseffect"])."</a>";
		if ($item["focuslevel"]>0) { $Output .= " <i>(Level ".$item["focuslevel"].")</i>";  }
		$Output .= "<br>\n";
	}

	// clicky effect
	if (($item["clickeffect"]>0) AND ($item["clickeffect"]<65535)) {
		$Output .= $tab."Effect: <a href='".$spellurl.$item["clickeffect"]."' target='_blank'>".GetFieldByQuery("name","SELECT name FROM $tbspells WHERE id=".$item["clickeffect"])."</a>";
		$Output .= "&nbsp;(";
		if ($item["clicktype"]==1) { $Output .= "Any Slot, "; }
		if ($item["clicktype"]==4) { $Output .= "Must Equip, ";	}
		if ($item["clicktype"]==5) { $Output .= "Any Slot/Can Equip, "; }
		$Output .= "Casting Time: ";
		if ($item["casttime"]>0) {
			$casttime = sprintf("%.1f",$item["casttime"]/1000);
			$Output .= $casttime;
		}
		else  { $Output .= "Instant"; }
		$Output .= ")";
		$Output .= " <i>(Level ".$item["clicklevel"].")</i>";
		$Output .= "<br>\n";
	}

	// Stats / HP / Mana / Endurance
	$Stats = "";
	//replaced block BEGIN 2/25/2014
	/*if($item[ "astr"] != 0)  $Stats .= " STR: "           .sign($item ["astr"]);
	if($item[ "asta"] != 0)  $Stats .= " STA: "           .sign($item ["asta"]);
	if($item[ "aagi"] != 0)  $Stats .= " AGI: "           .sign($item ["aagi"]);
	if($item[ "adex"] != 0)  $Stats .= " DEX: "           .sign($item ["adex"]);
	if($item[ "awis"] != 0)  $Stats .= " WIS: "           .sign($item ["awis"]);
	if($item[ "aint"] != 0)  $Stats .= " INT: "           .sign($item ["aint"]);
	if($item[ "acha"] != 0)  $Stats .= " CHA: "           .sign($item ["acha"]);
	if($item[   "hp"] != 0)  $Stats .= " HP: "            .sign($item   ["hp"]);
	if($item[ "mana"] != 0)  $Stats .= " MANA: "          .sign($item ["mana"]);
	if($item["endur"] != 0)  $Stats .= " Endurance: "     .sign($item["endur"]);*/
	if($item[ "astr"] != 0)  $Stats        .= " STR: "            . $item ["astr"];
	if($item["heroic_str"] != 0) $Stats .= " " . $item["heroic_str"] ;
	if($item[ "asta"] != 0)  $Stats        .= " STA: "            . $item ["asta"];
	if($item["heroic_sta"] != 0) $Stats .= " " . $item["heroic_sta"] ;
	if($item[ "aagi"] != 0)  $Stats        .= " AGI: "            . $item ["aagi"];
	if($item["heroic_agi"] != 0) $Stats .= " " . $item["heroic_agi"] ;
	if($item[ "adex"] != 0)  $Stats        .= " DEX: "            . $item ["adex"];
	if($item["heroic_dex"] != 0) $Stats .= " " . $item["heroic_dex"] ;
	if($item[ "awis"] != 0)  $Stats        .= " WIS: "            . $item ["awis"];
	if($item["heroic_wis"] != 0) $Stats .= " " . $item["heroic_wis"] ;
	if($item[ "aint"] != 0)  $Stats        .= " INT: "            . $item ["aint"];
	if($item["heroic_int"] != 0) $Stats .= " " . $item["heroic_int"] ;
	if($item[ "acha"] != 0)  $Stats        .= " CHA: "            . $item ["acha"];
	if($item["heroic_cha"] != 0) $Stats .= " " . $item["heroic_cha"] ;
	//replace block END 2/25/2014
	if($Stats != "") { $Output .= $tab.$Stats."<br>\n"; }

	//resists
	$Stats = "";
	//replaced block BEGIN 2/25/2014
	/*if($item[   "fr"] != 0)  $Stats .= " SV FIRE: "   .sign($item   ["fr"]);
	if($item[   "dr"] != 0)  $Stats .= " SV DISEASE: ".sign($item   ["dr"]);
	if($item[   "cr"] != 0)  $Stats .= " SV COLD: "   .sign($item   ["cr"]);
	if($item[   "mr"] != 0)  $Stats .= " SV MAGIC: "  .sign($item   ["mr"]);
	if($item[   "pr"] != 0)  $Stats .= " SV POISON: " .sign($item   ["pr"]);*/
	if($item[   "fr"] != 0)  $Stats .= " Fire: "           . $item["fr"] ;
	if($item["heroic_fr"] != 0) $Stats .= " " . $item["heroic_fr"] ;
	if($item[   "dr"] != 0)  $Stats .= " Disease: "        . $item["dr"] ;
	if($item["heroic_dr"] != 0) $Stats .= " " . $item["heroic_dr"] ;
	if($item[   "cr"] != 0)  $Stats .= " Cold: "           . $item["cr"] ;
	if($item["heroic_cr"] != 0) $Stats .= " " . $item["heroic_cr"] ;
	if($item[   "mr"] != 0)  $Stats .= " Magic: "          . $item["mr"] ;
	if($item["heroic_mr"] != 0) $Stats .= " " . $item["heroic_mr"];
	if($item[   "pr"] != 0)  $Stats .= " Poison: "         . $item["pr"];
	if($item["heroic_pr"] != 0) $Stats .= " " . $item["heroic_pr"];
	if($item[   "hp"] != 0)  $Stats .= " HP: "            .sign($item   ["hp"]);
	if($item[ "mana"] != 0)  $Stats .= " MANA: "          .sign($item ["mana"]);
	if($item["endur"] != 0)  $Stats .= " Endurance: "     .sign($item["endur"]);
	//replaced block END 2/25/2014
	if($Stats != "") { $Output .= $tab.$Stats."<br>\n"; }

	// bonuses
	if ($item["haste"]>0) {$Output .= $tab."Haste: ".$item["haste"]."%<br>\n";   }
	if ($item["avoidance"]>0) { $Output .= $tab."Avoidance: ".sign($item["avoidance"])."<br>\n";   }
	if ($item["attack"]>0) { $Output .= $tab."Attack: ".sign($item["attack"])."<br>\n";   }
	if ($item["extradmgamt"]>0) { $Output .= $tab.strtolower_ucfirst($dbskills[$item["extradmgskill"]])." DMG: ".sign($item["extradmgamt"])."<br>\n";   }
	if ($item["damageshield"]>0) { $Output .= $tab."Damage Shield: ".sign($item["damageshield"])."<br>\n";   }
	if ($item["dotshielding"]>0) { $Output .= $tab."Dot Shielding: ".sign($item["dotshielding"])."%<br>\n";   }
	if ($item["manaregen"]>0) { $Output .= $tab."Mana Regeneration: ".sign($item["manaregen"])."<br>\n";   }
	if ($item["shielding"]>0) { $Output .= $tab."Shielding: ".sign($item["shielding"])."%<br>\n";   }
	if ($item["hpregen"]>0) { $Output .= $tab."Regeneration: ".sign($item["hpregen"])."<br>\n";   }
	if ($item["combateffects"]>0) { $Output .= $tab."Combat Effects: ".sign($item["combateffects"])."<br>\n";   }
	if ($item["accuracy"]>0) { $Output .= $tab."Accuracy: ".sign($item["accuracy"])."<br>\n";   }
	if ($item["combatskill"]>0) { $Output .= $tab.strtolower_ucfirst($dbskills[$item["combatskill"]])." DMG: ".sign($item["combatskilldmg"])."<br>\n";   }
	if ($item["spellshield"]>0) { $Output .= $tab."Spell Shielding: ".sign($item["spellshield"])."%<br>\n";   }
	if ($item["strikethrough"]>0) { $Output .= $tab."Strikethrough: ".sign($item["strikethrough"])."%<br>\n";   }
	if ($item["stunresist"]>0) { $Output .= $tab."Stun Resist: ".sign($item["stunresist"])."%<br>\n";   }

	// bard item ?
	if ($item["bardtype"]>0) {
		$Output .= $tab.$dbbardskills[$item["bardtype"]].": ".$item["bardvalue"];
		$val=($item["bardvalue"]*10)-100;
		if ($val>0) { $Output .= "<i> (".sign($val)."%)</i>"; }
		$Output .= "<br>\n";
	}

	//required level
	if ($item["reqlevel"]>0) {
		$Output .= $tab."Required level of ".$item["reqlevel"].".<br>\n";
	}

	//recomended level
	if ($item["reclevel"]>0) {
		$Output .= $tab."Recommended level of ".$item["reclevel"].".<br>\n";
	}

	// Weight
	$weight= sprintf("%.1f",($item["weight"]/10));
	$Output .= $tab."WT: ".$weight." ";

	// Item range
	if($item["range"] > 0) { $Output .= $tab."Range: ".$item["range"]." "; }

	//size
	$Output .= $tab."Size: ".strtoupper(getsize($item["size"]))."<br>\n";

	//classes
	$Output .= $tab."Class: ".getclasses($item["classes"])."<br>\n";

	//races
	$Output .= $tab."Race: ".getraces($item["races"])."<br>\n";

	// Deity
	if($item["deity"] > 0) { $Output .= $tab."Deity: ".getdeities($item["deity"])."<br>\n"; }

	// Augmentations
	for( $i = 1; $i <= 5; $i ++) {
		if($item["augslot".$i."type"] > 0) { $Output .= $tab."Slot ".$i.": Type ".$item["augslot".$i."type"]."<br>\n"; }
	}

	// scroll
	if (($item["scrolleffect"]>0) AND ($item["scrolleffect"]<65535)) {
		$Output .= $tab."Effect: <a href='".$spellurl.$item["scrolleffect"]."' target='_blank'>".GetFieldByQuery("name","SELECT name FROM $tbspells WHERE id=".$item["scrolleffect"])."</a>";
		$Output .= "<br>\n";
	}
	//admindebug($item);
	return $Output;
}
?>
