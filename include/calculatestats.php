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
function GetMaxAtk($iatk, $str, $offense) {
	$myatk = $iatk + (($str + $offense) * 9 / 10);
	return floor($myatk);
}

function acmod($agility,$level) {

	if($agility < 1 || $level < 1)
		return(0);

	if ($agility <=74){
		if ($agility == 1)
			return -24;
		else if ($agility <=3)
			return -23;
		else if ($agility == 4)
			return -22;
		else if ($agility <=6)
			return -21;
		else if ($agility <=8)
			return -20;
		else if ($agility == 9)
			return -19;
		else if ($agility <=11)
			return -18;
		else if ($agility == 12)
			return -17;
		else if ($agility <=14)
			return -16;
		else if ($agility <=16)
			return -15;
		else if ($agility == 17)
			return -14;
		else if ($agility <=19)
			return -13;
		else if ($agility == 20)
			return -12;
		else if ($agility <=22)
			return -11;
		else if ($agility <=24)
			return -10;
		else if ($agility == 25)
			return -9;
		else if ($agility <=27)
			return -8;
		else if ($agility == 28)
			return -7;
		else if ($agility <=30)
			return -6;
		else if ($agility <=32)
			return -5;
		else if ($agility == 33)
			return -4;
		else if ($agility <=35)
			return -3;
		else if ($agility == 36)
			return -2;
		else if ($agility <=38)
			return -1;
		else if ($agility <=65)
			return 0;
		else if ($agility <=70)
			return 1;
		else if ($agility <=74)
			return 5;
	}
	else if($agility <= 137) {
		if ($agility == 75){
			if ($level <= 6)
				return 9;
			else if ($level <= 19)
				return 23;
			else if ($level <= 39)
				return 33;
			else
				return 39;
		}
		else if ($agility >= 76 && $agility <= 79){
			if ($level <= 6)
				return 10;
			else if ($level <= 19)
				return 23;
			else if ($level <= 39)
				return 33;
			else
				return 40;
		}
		else if ($agility == 80){
			if ($level <= 6)
				return 11;
			else if ($level <= 19)
				return 24;
			else if ($level <= 39)
				return 34;
			else
				return 41;
		}
		else if ($agility >= 81 && $agility <= 85){
			if ($level <= 6)
				return 12;
			else if ($level <= 19)
				return 25;
			else if ($level <= 39)
				return 35;
			else
				return 42;
		}
		else if ($agility >= 86 && $agility <= 90){
			if ($level <= 6)
				return 12;
			else if ($level <= 19)
				return 26;
			else if ($level <= 39)
				return 36;
			else
				return 42;
		}
		else if ($agility >= 91 && $agility <= 95){
			if ($level <= 6)
				return 13;
			else if ($level <= 19)
				return 26;
			else if ($level <= 39)
				return 36;
			else
				return 43;
		}
		else if ($agility >= 96 && $agility <= 99){
			if ($level <= 6)
				return 14;
			else if ($level <= 19)
				return 27;
			else if ($level <= 39)
				return 37;
			else
				return 44;
		}
		else if ($agility == 100 && $level >= 7){
			if ($level <= 19)
				return 28;
			else if ($level <= 39)
				return 38;
			else
				return 45;
		}
		else if ($level <= 6) {
			return 15;
		}
		//$level is >6
		else if ($agility >= 101 && $agility <= 105){
			if ($level <= 19)
				return 29;
			else if ($level <= 39)
				return 39;// not verified
			else
				return 45;
		}
		else if ($agility >= 106 && $agility <= 110){
			if ($level <= 19)
				return 29;
			else if ($level <= 39)
				return 39;// not verified
			else
				return 46;
		}
		else if ($agility >= 111 && $agility <= 115){
			if ($level <= 19)
				return 30;
			else if ($level <= 39)
				return 40;// not verified
			else
				return 47;
		}
		else if ($agility >= 116 && $agility <= 119){
			if ($level <= 19)
				return 31;
			else if ($level <= 39)
				return 41;
			else
				return 47;
		}
		else if ($level <= 19) {
			return 32;
		}
		//$level is > 19
		else if ($agility == 120){
			if ($level <= 39)
				return 42;
			else
				return 48;
		}
		else if ($agility <= 125){
			if ($level <= 39)
				return 42;
			else
				return 49;
		}
		else if ($agility <= 135){
			if ($level <= 39)
				return 42;
			else
				return 50;
		}
		else {
			if ($level <= 39)
				return 42;
			else
				return 51;
		}
	} else if($agility <= 300) {
		if($level <= 6) {
			if($agility <= 139)
				return(21);
			else if($agility == 140)
				return(22);
			else if($agility <= 145)
				return(23);
			else if($agility <= 150)
				return(23);
			else if($agility <= 155)
				return(24);
			else if($agility <= 159)
				return(25);
			else if($agility == 160)
				return(26);
			else if($agility <= 165)
				return(26);
			else if($agility <= 170)
				return(27);
			else if($agility <= 175)
				return(28);
			else if($agility <= 179)
				return(28);
			else if($agility == 180)
				return(29);
			else if($agility <= 185)
				return(30);
			else if($agility <= 190)
				return(31);
			else if($agility <= 195)
				return(31);
			else if($agility <= 199)
				return(32);
			else if($agility <= 219)
				return(33);
			else if($agility <= 239)
				return(34);
			else
				return(35);
		} else if($level <= 19) {
			if($agility <= 139)
				return(34);
			else if($agility == 140)
				return(35);
			else if($agility <= 145)
				return(36);
			else if($agility <= 150)
				return(37);
			else if($agility <= 155)
				return(37);
			else if($agility <= 159)
				return(38);
			else if($agility == 160)
				return(39);
			else if($agility <= 165)
				return(40);
			else if($agility <= 170)
				return(40);
			else if($agility <= 175)
				return(41);
			else if($agility <= 179)
				return(42);
			else if($agility == 180)
				return(43);
			else if($agility <= 185)
				return(43);
			else if($agility <= 190)
				return(44);
			else if($agility <= 195)
				return(45);
			else if($agility <= 199)
				return(45);
			else if($agility <= 219)
				return(46);
			else if($agility <= 239)
				return(47);
			else
				return(48);
		} else if($level <= 39) {
			if($agility <= 139)
				return(44);
			else if($agility == 140)
				return(45);
			else if($agility <= 145)
				return(46);
			else if($agility <= 150)
				return(47);
			else if($agility <= 155)
				return(47);
			else if($agility <= 159)
				return(48);
			else if($agility == 160)
				return(49);
			else if($agility <= 165)
				return(50);
			else if($agility <= 170)
				return(50);
			else if($agility <= 175)
				return(51);
			else if($agility <= 179)
				return(52);
			else if($agility == 180)
				return(53);
			else if($agility <= 185)
				return(53);
			else if($agility <= 190)
				return(54);
			else if($agility <= 195)
				return(55);
			else if($agility <= 199)
				return(55);
			else if($agility <= 219)
				return(56);
			else if($agility <= 239)
				return(57);
			else
				return(58);
		} else {	//lvl >= 40
			if($agility <= 139)
				return(51);
			else if($agility == 140)
				return(52);
			else if($agility <= 145)
				return(53);
			else if($agility <= 150)
				return(53);
			else if($agility <= 155)
				return(54);
			else if($agility <= 159)
				return(55);
			else if($agility == 160)
				return(56);
			else if($agility <= 165)
				return(56);
			else if($agility <= 170)
				return(57);
			else if($agility <= 175)
				return(58);
			else if($agility <= 179)
				return(58);
			else if($agility == 180)
				return(59);
			else if($agility <= 185)
				return(60);
			else if($agility <= 190)
				return(61);
			else if($agility <= 195)
				return(61);
			else if($agility <= 199)
				return(62);
			else if($agility <= 219)
				return(63);
			else if($agility <= 239)
				return(64);
			else
				return(65);
		}
	}
	else{
		//seems about 21 agil per extra AC pt over 300...
		return (65 + (($agility-300) / 21));
	}

	return 0;
};


function GetMaxAC($agility, $level, $defense, $class, $iac, $race) {

	$WARRIOR = 1;
	$CLERIC = 2;
	$PALADIN = 3;
	$RANGER = 4;
	$SHADOWKNIGHT = 5;
	$DRUID = 6;
	$MONK = 7;
	$BARD = 8;
	$ROGUE = 9;
	$SHAMAN = 10;
	$NECROMANCER = 11;
	$WIZARD = 12;
	$MAGICIAN = 13;
	$ENCHANTER = 14;
	$BEASTLORD = 15;
	$BERSERKER = 16;

	// new formula
	$avoidance = 0;
	$avoidance = (acmod($agility,$level) + (($defense*16)/9));
	if ($avoidance < 0)
		$avoidance = 0;

	$mitigation = 0;
	if ($class == $WIZARD || $class == $MAGICIAN || $class == $NECROMANCER || $class == $ENCHANTER) {

		$mitigation = $defense/4 + ($iac+1);

		$mitigation -= 4;
	} else {

		$mitigation = $defense/3 + (($iac*4)/3);
		if($class == $MONK)
			$mitigation += $level * 13/10;	//the 13/10 might be wrong, but it is close...
	}
	$displayed = 0;
	$displayed += (($avoidance+$mitigation)*1000)/847;	//natural AC

	//Iksar AC, untested
	if ($race == 128) {
		$displayed += 12;
		$iksarlevel = $evel;
		$iksarlevel -= 10;
		if ($iksarlevel > 25)
			$iksarlevel = 25;
		if ($iksarlevel > 0)
			$displayed += $iksarlevel * 12 / 10;
	}

	//spell AC bonuses are added directly to natural total


	$AC = $displayed;
	return floor($AC);
}

/* 
====================================================================
Class- and race-based resist adjustements modified by Pithy, 2/6/17.
====================================================================
*/

// CLASS-BASED RESIST ADJUSTMENTS

/* class indices:
1 - warrior
2 - cleric
3 - paladin
4 - ranger
5 - shadow knight
6 - druid
7 - monk
8 - bard
9 - rogue
10 - shaman
11 - necromancer
12 - wizard
13 - magician
14 - enchanter
15 - beastlord
16 - berserker
*/

/*
Bonuses are level-dependent: 4 (or 8) + floor(level/4). 
That gives 19 (or 23) at level 60, updated to 20 (or 24) at 65.
The warrior MR bonus is different: 21 at 60, 24 at 65.
The arrays below have the level 60 values.

2020-03-05-Mokli: matched the values @60 to TAKP values verified on dev server.  Basically lowered most by 4, raised War MR up to 30.


$PRbyClass=array(0,0,0,0,0,15,0,0,0,19,0,0,0,0,0,0,0);
$MRbyClass=array(0,30,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
$DRbyClass=array(0,0,0,19,0,15,0,0,0,0,0,0,0,0,0,15,0);
$FRbyClass=array(0,0,0,0,15,0,0,19,0,0,0,0,0,0,0,0,0);
$CRbyClass=array(0,0,0,0,15,0,0,0,0,0,0,0,0,0,0,15,0);

2020-03-04-Mokli:  quick edit to above.  Padded arrays with a 0, since arrays start with 0 not 1.  Was causing class alignment to be shifted by 1.
2020-03-08-Mokli:  commented the above out entirely.  Handling with functions for each just below here.
*/

function PRbyClass($class) {
	if(($class == 5) && ($level >= 50)) return floor(($level - 49) + 4);
	else if(($class == 5) && ($level < 50)) return 4;
	else if(($class == 9)  && ($level >= 50)) return floor(($level - 49) + 8);
	else if(($class == 9)  && ($level < 50)) return 8;
	else return 0;
}

function MRbyClass($class) {
	if(($class == 1) return floor($level / 2);
	else return 0;
}

function DRbyClass($class) {
	if(($class == 3) && ($level >= 50)) return floor(($level - 49) + 8);
	else if(($class == 3) && ($level < 50)) return 8;	
	else if(($class == 5) && ($level >= 50)) return floor(($level - 49) + 4);
	else if(($class == 5) && ($level < 50)) return 4;
	else if(($class == 15) && ($level >= 50)) return floor(($level - 49) + 4);
	else if(($class == 15) && ($level < 50)) return 4;
	else return 0;
}

function FRbyClass($class) {
	if(($class == 4) && ($level >= 50)) return floor(($level - 49) + 4);
	else if(($class == 4) && ($level < 50)) return 4;
	else if(($class == 7)  && ($level >= 50)) return floor(($level - 49) + 8);
	else if(($class == 7)  && ($level < 50)) return 8;
	else return 0;
}

function CRbyClass($class) {
	if(($class == 4) && ($level >= 50)) return floor(($level - 49) + 4);
	else if(($class == 4) && ($level < 50)) return 4;
	else if(($class == 15)  && ($level >= 50)) return floor(($level - 49) + 4);
	else if(($class == 15)  && ($level < 50)) return 4;
	else return 0;
}



// RACE-BASED RESIST ADJUSTMENTS

/* race numbers:
1 - human
2 - barbarian
3 - erudite
4 - wood elf
5 - high elf
6 - dark elf
7 - half elf
8 - dwarf
9 - troll
10 - ogre
11 - halfling
12 - gnome
128 - iksar
*/

function PRbyRace($race) {
	if($race == 8)   return 20; // dwarf
	else if($race == 11)  return 20; // halfling
	else return 15;
}

function MRbyRace($race) {
	if($race == 3)   return 30; // erudite
	else if($race == 8)   return 30; // dwarf
	else return 25;
}

function DRbyRace($race) {
	if($race == 3)   return 10; // erudite
	else if($race == 11)  return 20; // halfling
	else return 15;
}

function FRbyRace($race) {
	if($race == 9)   return  5; // troll
	else if($race == 128) return 30; // iksar
	else return 25;
}

function CRbyRace($race) {
	if($race == 2)   return 35; // barbarian
	else if($race == 128) return 15; // iksar
	else return 25;
}

/* 2020-03-04-Mokli edit:
Removed from MRbyRace:  	else if($race == 128) return 33; // iksar
Iksar does not appear to have an MR starting bonus on TAKP
*/

/* 
===================================================================
End Pithy's class- and race-based resist adjustement modifications.
===================================================================
*/

/* 
=========================================================================
Max hitpoint calculation adjustments based on Zygor's Alla'Kabor formula.
Implemented by Pithy, 2/6/17.
=========================================================================
*/

// This function will need to be modified in Luclin for the ND and PE AAs.
// min(255,$sta) should be changed to $sta when the 255 stat cap is lifted.
function GetMaxHP($level,$class,$sta,$ihp)
{
	$base_hp = GetHPBase($level,$class,min(255,$sta));
	$hp = floor($base_hp) + floor($ihp);
	return $hp;
}

// This function will need to be modified in Luclin for the ND and PE AAs.
function GetHPBase($level,$class,$sta)
{
	$lm = floatval(GetLM($class,$level));
	$post_255_sta = ((max(0,($sta-255))));
	$staGain = floor(($sta - round($post_255_sta/2))*10/3);
	$hp_from_level = ($level*$lm);
	$hp_from_sta = ($level*$lm*$staGain/1000);
	$base_hp = 5 + $hp_from_level + $hp_from_sta;
	return $base_hp;
}

// This function gets the level multiplier used to calculate base hitpoints.
function getLM($class, $level) {

	$className = getClassName($class);

	if($className == 'Monk' || $className == 'Rogue' || $className == 'Beastlord' || $className == 'Bard') {
		if($level > 57) return 20;
		if($level > 50) return 19;
		return 18;
	}

	if($className == 'Cleric' || $className == 'Druid' || $className == 'Shaman') {
		return 15;
	}

	if($className == 'Magician' || $className == 'Necromancer' || $className == 'Enchanter' || $className == 'Wizard') {
		return 12;
	}

	if($className == 'Ranger') {
		if($level > 57) return 21;
		return 20;
	}

	if($className == 'Shadow Knight' || $className == 'Shadowknight' || $className == 'Paladin') {
		if($level > 59) return 26;
		if($level > 55) return 25;
		if($level > 50) return 24;
		if($level > 44) return 23;
		if($level > 34) return 22;
		return 21;
	}

	if($className == 'Warrior') {
		if($level > 59) return 30;
		if($level > 56) return 29;
		if($level > 52) return 28;
		if($level > 39) return 27;
		if($level > 29) return 25;
		if($level > 19) return 23;
		return 22;
	}
}

// This function maps a class number into the corresponding class name.
// Called by getLM. 
function getClassName($class) {
	switch($class) {
		case '1':  return "Warrior";      break;
		case '2':  return "Cleric";       break;
		case '3':  return "Paladin";      break;
		case '4':  return "Ranger";       break;
		case '5':  return "Shadow Knight"; break;
		case '6':  return "Druid";        break;
		case '7':  return "Monk";         break;
		case '8':  return "Bard";         break;
		case '9':  return "Rogue";        break;
		case '10': return "Shaman";       break;
		case '11': return "Necromancer";  break;
		case '12': return "Wizard";       break;
		case '13': return "Magician";     break;
		case '14': return "Enchanter";    break;
		case '15': return "Beastlord";    break;
		case '16': return "Berserker";    break;
		default:   return "Unknown Class"; break;
	}
}

/* 
=================================================
End Pithy's max hitpoint calculation adjustments.
=================================================
*/



//function copied/converted from EQEMU sourcecode may 2, 2009
function GetCasterClass($class){
	$WARRIOR = 1;
	$CLERIC = 2;
	$PALADIN = 3;
	$RANGER = 4;
	$SHADOWKNIGHT = 5;
	$DRUID = 6;
	$MONK = 7;
	$BARD = 8;
	$ROGUE = 9;
	$SHAMAN = 10;
	$NECROMANCER = 11;
	$WIZARD = 12;
	$MAGICIAN = 13;
	$ENCHANTER = 14;
	$BEASTLORD = 15;
	$BERSERKER = 16;

	switch($class)
	{
		case $CLERIC:
		case $PALADIN:
		case $RANGER:
		case $DRUID:
		case $SHAMAN:
		case $BEASTLORD:
			return 'W';
			break;

		case $SHADOWKNIGHT:
		case $BARD:
		case $NECROMANCER:
		case $WIZARD:
		case $MAGICIAN:
		case $ENCHANTER:
			return 'I';
			break;

		default:
			return 'N';
			break;
	}
}

/* replaced by new sod hp/mana/end calculations
//function copied/converted from EQEMU sourcecode may 2, 2009, was  named CalcMaxMana();
function GetMaxMana($level,$class,$int,$wis,$imana)
{
	$WisInt = 0;
	$MindLesserFactor = 0;
	$MindFactor = 0;
	switch(GetCasterClass($class))
	{
		case 'I': 
			$WisInt = $int;

			if((( $WisInt - 199 ) / 2) > 0)
				$MindLesserFactor = ( $WisInt - 199 ) / 2;
			else
				$MindLesserFactor = 0;

			$MindFactor = $WisInt - $MindLesserFactor;
			if($WisInt > 100)
				$max_mana = (((5 * ($MindFactor + 20)) / 2) * 3 * $level / 40);
			else
				$max_mana = (((5 * ($MindFactor + 200)) / 2) * 3 * $level / 100);	
			$max_mana += $imana;
			break;

		case 'W':
			$WisInt = $wis;

			if((( $WisInt - 199 ) / 2) > 0)
				$MindLesserFactor = ( $WisInt - 199 ) / 2;
			else
				$MindLesserFactor = 0;

			$MindFactor = $WisInt - $MindLesserFactor;
			if($WisInt > 100)
				$max_mana = (((5 * ($MindFactor + 20)) / 2) * 3 * $level / 40);
			else
				$max_mana = (((5 * ($MindFactor + 200)) / 2) * 3 * $level / 100);	
			
			$max_mana += $imana;
			break;
				
		case 'N': {
			$max_mana = 0;
			break;
		}

	}

	return floor($max_mana);
}*/

/* 
=============================================================================
Pithy update, 2/6/17: applied the 255 int/wis hardcap in the first two lines.
=============================================================================
*/

//function copied/converted from EQEMU sourcecode oct 26, 2010 
function GetMaxMana($level,$class,$int,$wis,$imana)
{
	// The next two lines should be updated when AAs lift the stat cap.
	$int = min(255,$int);
	$wis = min(255,$wis);

	$WisInt = 0;
	$MindLesserFactor = 0;
	$MindFactor = 0;
	$max_m = 0;
	$wisint_mana = 0;
	//$base_mana = 0;
	$base_mana = 3.8505*$level + 0.1869*$level*min(200,$int) + 0.0907*$level*(max(200,$int)-200);
	$ConvertedWisInt = 0;
	switch(GetCasterClass($class))
	{
		case 'I':
			$WisInt = $int;
			if ($WisInt > 100) {
				$ConvertedWisInt = ((($WisInt - 100) * 5 / 2) + 100);
				if ($WisInt > 201) {
					$ConvertedWisInt -= (($WisInt - 201) * 5 / 4);
				}
			}
			else {
				$ConvertedWisInt = $WisInt;
			}
			if ($level < 41) {
				$wisint_mana = ($level * 75 * $ConvertedWisInt / 1000);
				$base_mana = ($level * 15);
			}
			else if ($level < 81) {
				$wisint_mana = ((3 * $ConvertedWisInt) + (($level - 40) * 15 * $ConvertedWisInt / 100));
				$base_mana = (600 + (($level - 40) * 30));
			}
			else {
				$wisint_mana = (9 * $ConvertedWisInt);
				$base_mana = (1800 + (($level - 80) * 18));
			}
			$max_mana = $base_mana + $wisint_mana;
			$max_mana += $imana;
			break;

		case 'W':
			$WisInt = $wis;
			if ($WisInt > 100) {
				$ConvertedWisInt = ((($WisInt - 100) * 5 / 2) + 100);
				if ($WisInt > 201) {
					$ConvertedWisInt -= (($WisInt - 201) * 5 / 4);
				}
			}
			else {
				$ConvertedWisInt = $WisInt;
			}
			if ($level < 41) {
				$wisint_mana = ($level * 75 * $ConvertedWisInt / 1000);
				$base_mana = ($level * 15);
			}
			else if ($level < 81) {
				$wisint_mana = ((3 * $ConvertedWisInt) + (($level - 40) * 15 * $ConvertedWisInt / 100));
				$base_mana = (600 + (($level - 40) * 30));
			}
			else {
				$wisint_mana = (9 * $ConvertedWisInt);
				$base_mana = (1800 + (($level - 80) * 18));
			}
			$max_mana = $base_mana + $wisint_mana;
			$max_mana += $imana;
			break;

		case 'N': {
			$max_mana = 0;
			break;
		}

	}

	return floor($max_mana);
}

/* 
=============================================
End Pithy's 255 int/wis hardcap modification.
=============================================
*/



/* replaced by new sod hp/mana/end calculations
function GetMaxEndurance($STR,$STA,$DEX,$AGI,$level,$iendurance)
{
	$Stats = $STR + $STA + $DEX + $AGI;

	$LevelBase = $level * 15;

	$at_most_800 = $Stats;
	if($at_most_800 > 800)
		$at_most_800 = 800;
	
	$Bonus400to800 = 0;
	$HalfBonus400to800 = 0;
	$Bonus800plus = 0;
	$HalfBonus800plus = 0;
	
	$BonusUpto800 = floor( $at_most_800 / 4 ) ;
	if($Stats > 400) {
		$Bonus400to800 = floor( ($at_most_800 - 400) / 4 );
		$HalfBonus400to800 = floor( max( ( $at_most_800 - 400 ), 0 ) / 8 );
		
		if($Stats > 800) {
			$Bonus800plus = floor( ($Stats - 800) / 8 ) * 2;
			$HalfBonus800plus = floor( ($Stats - 800) / 16 );
		}
	}
	$bonus_sum = $BonusUpto800 + $Bonus400to800 + $HalfBonus400to800 + $Bonus800plus + $HalfBonus800plus;
	
	$max_end = $LevelBase;

	//take all of the sums from above, then multiply by level*0.075
	$max_end += ( $bonus_sum * 3 * $level ) / 40;
	
	$max_end += $iendurance;
	return floor($max_end);
}*/

//function copied/converted from EQEMU sourcecode oct 26, 2010 
function GetMaxEndurance($STR,$STA,$DEX,$AGI,$level,$iendurance)
{
	$Stats = ($STR + $STA + $DEX + $AGI)/4;
	$base_endurance = 0;
	$ConvertedStats = 0;
	$sta_end = 0;

	if (($Stats) > 100) {
		$ConvertedStats = ((($Stats - 100) * 5 / 2) + 100);
		if ($Stats > 201) {
			$ConvertedStats -= (($Stats - 201) * 5 / 4);
		}
	}
	else {
		$ConvertedStats = $Stats;
	}

	if ($level < 41) {
		$sta_end = ($level * 75 * $ConvertedStats / 1000);
		$base_endurance = ($level * 15);
	}
	else if ($level < 81) {
		$sta_end = ((3 * $ConvertedStats) + (($level - 40) * 15 * $ConvertedStats / 100));
		$base_endurance = (600 + (($level - 40) * 30));
	}
	else {
		$sta_end = (9 * $ConvertedStats);
		$base_endurance = (1800 + (($level - 80) * 18));
	}
	$max_end = ($base_endurance + $sta_end);


	$max_end += $iendurance;
	return floor($max_end);
}

?>