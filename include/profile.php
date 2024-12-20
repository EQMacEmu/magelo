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
 *    September 29, 2014 - Maudigan
 *       This is a new rewrite to accomodate the retirement of the
 *       character blob. The load function was removed and a constructor
 *       was added instead.
 ***************************************************************************/

include_once("include/config.php");
include_once("include/debug.php");
include_once("include/sql.php");

global $game_db;

if (!defined('INCHARBROWSER')) { die("Hacking attempt"); }

/********************************************
 **           DATA LOCATOR ARRAYS           **
 ** these describe where different types    **
 ** of data are found                       **
 ********************************************/

//constants to reference indexes in the locator array
define('LOCATOR_TABLE',  0);
define('LOCATOR_COLUMN', 1);
define('LOCATOR_INDEX',  2);

// the name of the second pk of a table
// --------------------------------------------------------------
// SYNTAX:   "<TABLE>" => "<COLUMN>",
// --------------------------------------------------------------
// <TABLE>  = the name of the table
// <COLUMN> = the name of the tables secondary pk
$locator_pk = array (
    "character_alternate_abilities" => "slot",
    "character_skills" => "skill_id",
    "character_languages" => "lang_id",
);

// the table, column, and index of where to find a value
// --------------------------------------------------------------
// SYNTAX:  "<DATA>" => array("<TABLE>", "<COLUMN>", "<INDEX>"),
// --------------------------------------------------------------
// <DATA>   = The shortname reference for the value, 
//            it usually matches the column name.
// <TABLE>  = the name of the table the data comes from
// <COLUMN> = the column the data appears in
// <INDEX>  = if there are multiple rows for the character
//            because of a second PK, then this is the 
//            value of that second PK, otherwise its false.
$locator = array (
    "id" => array("character_data", "id", false),
    "account_id" => array("character_data", "account_id", false),
    "name" => array("character_data", "name", false),
    "last_name" => array("character_data", "last_name", false),
    "title" => array("character_data", "title", false),
    "suffix" => array("character_data", "suffix", false),
    "zone_id" => array("character_data", "zone_id", false),
    "zone_instance" => array("character_data", "zone_instance", false),
    "y" => array("character_data", "y", false),
    "x" => array("character_data", "x", false),
    "z" => array("character_data", "z", false),
    "heading" => array("character_data", "heading", false),
    "gender" => array("character_data", "gender", false),
    "race" => array("character_data", "race", false),
    "class" => array("character_data", "class", false),
    "level" => array("character_data", "level", false),
    "deity" => array("character_data", "deity", false),
    "birthday" => array("character_data", "birthday", false),
    "last_login" => array("character_data", "last_login", false),
    "time_played" => array("character_data", "time_played", false),
    "level2" => array("character_data", "level2", false),
    "anon" => array("character_data", "anon", false),
    "gm" => array("character_data", "gm", false),
    "face" => array("character_data", "face", false),
    "hair_color" => array("character_data", "hair_color", false),
    "hair_style" => array("character_data", "hair_style", false),
    "beard" => array("character_data", "beard", false),
    "beard_color" => array("character_data", "beard_color", false),
    "eye_color_1" => array("character_data", "eye_color_1", false),
    "eye_color_2" => array("character_data", "eye_color_2", false),
   //"ability_time_seconds" => array("character_data", "ability_time_seconds", false),
   //"ability_number" => array("character_data", "ability_number", false),
   //"ability_time_minutes" => array("character_data", "ability_time_minutes", false),
   //"ability_time_hours" => array("character_data", "ability_time_hours", false),
    "exp" => array("character_data", "exp", false),
    "aa_points_spent" => array("character_data", "aa_points_spent", false),
    "aa_exp" => array("character_data", "aa_exp", false),
    "aa_points" => array("character_data", "aa_points", false),
    "points" => array("character_data", "points", false),
    "cur_hp" => array("character_data", "cur_hp", false),
    "mana" => array("character_data", "mana", false),
    "endurance" => array("character_data", "endurance", false),
    "intoxication" => array("character_data", "intoxication", false),
    "str" => array("character_data", "str", false),
    "sta" => array("character_data", "sta", false),
    "cha" => array("character_data", "cha", false),
    "dex" => array("character_data", "dex", false),
    "int" => array("character_data", "int", false),
    "agi" => array("character_data", "agi", false),
    "wis" => array("character_data", "wis", false),
    "zone_change_count" => array("character_data", "zone_change_count", false),
   //"toxicity" => array("character_data", "toxicity", false),
    "hunger_level" => array("character_data", "hunger_level", false),
    "thirst_level" => array("character_data", "thirst_level", false),
   //"ability_up" => array("character_data", "ability_up", false),
    "pvp_status" => array("character_data", "pvp_status", false),
   //"pvp_kills" => array("character_data", "pvp_kills", false),
   //"pvp_deaths" => array("character_data", "pvp_deaths", false),
   //"pvp_current_points" => array("character_data", "pvp_current_points", false),
   //"pvp_career_points" => array("character_data", "pvp_career_points", false),
   //"pvp_best_kill_streak" => array("character_data", "pvp_best_kill_streak", false),
   //"pvp_worst_death_streak" => array("character_data", "pvp_worst_death_streak", false),
   //"pvp_current_kill_streak" => array("character_data", "pvp_current_kill_streak", false),
   //"pvp2" => array("character_data", "pvp2", false),
   //"pvp_type" => array("character_data", "pvp_type", false),
   //"show_helm" => array("character_data", "show_helm", false),
   //"group_auto_consent" => array("character_data", "group_auto_consent", false),
   //"raid_auto_consent" => array("character_data", "raid_auto_consent", false),
   //"guild_auto_consent" => array("character_data", "guild_auto_consent", false),
   //"RestTimer" => array("character_data", "RestTimer", false),
    "air_remaining" => array("character_data", "air_remaining", false),
    "autosplit_enabled" => array("character_data", "autosplit_enabled", false),
   //"lfp" => array("character_data", "lfp", false),
   //"lfg" => array("character_data", "lfg", false),
    "mailkey" => array("character_data", "mailkey", false),
   //"xtargets" => array("character_data", "xtargets", false),
    "firstlogon" => array("character_data", "firstlogon", false),
    "e_aa_effects" => array("character_data", "e_aa_effects", false),
    "e_percent_to_aa" => array("character_data", "e_percent_to_aa", false),
    "e_expended_aa_spent" => array("character_data", "e_expended_aa_spent", false),
    "id" => array("character_currency", "id", false),
    "platinum" => array("character_currency", "platinum", false),
    "gold" => array("character_currency", "gold", false),
    "silver" => array("character_currency", "silver", false),
    "copper" => array("character_currency", "copper", false),
    "platinum_bank" => array("character_currency", "platinum_bank", false),
    "gold_bank" => array("character_currency", "gold_bank", false),
    "silver_bank" => array("character_currency", "silver_bank", false),
    "copper_bank" => array("character_currency", "copper_bank", false),
    "platinum_cursor" => array("character_currency", "platinum_cursor", false),
    "gold_cursor" => array("character_currency", "gold_cursor", false),
    "silver_cursor" => array("character_currency", "silver_cursor", false),
    "copper_cursor" => array("character_currency", "copper_cursor", false),
    "1h_blunt" => array("character_skills", "value", 0),
    "1h_slashing" => array("character_skills", "value", 1),
    "2h_blunt" => array("character_skills", "value", 2),
    "2h_slashing" => array("character_skills", "value", 3),
    "abjuration" => array("character_skills", "value", 4),
    "alteration" => array("character_skills", "value", 5),
    "apply_poison" => array("character_skills", "value", 6),
    "archery" => array("character_skills", "value", 7),
    "backstab" => array("character_skills", "value", 8),
    "bind_wound" => array("character_skills", "value", 9),
    "bash" => array("character_skills", "value", 10),
    "block" => array("character_skills", "value", 11),
    "brass_instruments" => array("character_skills", "value", 12),
    "channeling" => array("character_skills", "value", 13),
    "conjuration" => array("character_skills", "value", 14),
    "defense" => array("character_skills", "value", 15),
    "disarm" => array("character_skills", "value", 16),
    "disarm_traps" => array("character_skills", "value", 17),
    "divination" => array("character_skills", "value", 18),
    "dodge" => array("character_skills", "value", 19),
    "double_attack" => array("character_skills", "value", 20),
    "dragon_punch" => array("character_skills", "value", 21),
    "dual_wield" => array("character_skills", "value", 22),
    "eagle_strike" => array("character_skills", "value", 23),
    "evocation" => array("character_skills", "value", 24),
    "feign_death" => array("character_skills", "value", 25),
    "flying_kick" => array("character_skills", "value", 26),
    "forage" => array("character_skills", "value", 27),
    "hand_to_hand" => array("character_skills", "value", 28),
    "hide" => array("character_skills", "value", 29),
    "kick" => array("character_skills", "value", 30),
    "meditate" => array("character_skills", "value", 31),
    "mend" => array("character_skills", "value", 32),
    "offense" => array("character_skills", "value", 33),
    "parry" => array("character_skills", "value", 34),
    "pick_lock" => array("character_skills", "value", 35),
    "piercing" => array("character_skills", "value", 36),
    "riposte" => array("character_skills", "value", 37),
    "round_kick" => array("character_skills", "value", 38),
    "safe_fall" => array("character_skills", "value", 39),
    "sense_heading" => array("character_skills", "value", 40),
    "sing" => array("character_skills", "value", 41),
    "sneak" => array("character_skills", "value", 42),
    "specialize_abjure" => array("character_skills", "value", 43),
    "specialize_alteration" => array("character_skills", "value", 44),
    "specialize_conjuration" => array("character_skills", "value", 45),
    "specialize_divinatation" => array("character_skills", "value", 46),
    "specialize_evocation" => array("character_skills", "value", 47),
    "pick_pockets" => array("character_skills", "value", 48),
    "stringed_instruments" => array("character_skills", "value", 49),
    "swimming" => array("character_skills", "value", 50),
    "throwing" => array("character_skills", "value", 51),
    "tiger_claw" => array("character_skills", "value", 52),
    "tracking" => array("character_skills", "value", 53),
    "wind_instruments" => array("character_skills", "value", 54),
    "fishing" => array("character_skills", "value", 55),
    "make_poison" => array("character_skills", "value", 56),
    "tinkering" => array("character_skills", "value", 57),
    "research" => array("character_skills", "value", 58),
    "alchemy" => array("character_skills", "value", 59),
    "baking" => array("character_skills", "value", 60),
    "tailoring" => array("character_skills", "value", 61),
    "sense_traps" => array("character_skills", "value", 62),
    "blacksmithing" => array("character_skills", "value", 63),
    "fletching" => array("character_skills", "value", 64),
    "brewing" => array("character_skills", "value", 65),
    "alcohol_tolerance" => array("character_skills", "value", 66),
    "begging" => array("character_skills", "value", 67),
    "jewelry_making" => array("character_skills", "value", 68),
    "pottery" => array("character_skills", "value", 69),
    "percussion_instruments" => array("character_skills", "value", 70),
    "intimidation" => array("character_skills", "value", 71),
    "berserking" => array("character_skills", "value", 72),
    "taunt" => array("character_skills", "value", 73),
    "frenzy" => array("character_skills", "value", 74),
    "common_tongue" => array("character_languages", "value", 0),
    "barbarian" => array("character_languages", "value", 1),
    "erudian" => array("character_languages", "value", 2),
    "elvish" => array("character_languages", "value", 3),
    "dark_elvish" => array("character_languages", "value", 4),
    "dwarvish" => array("character_languages", "value", 5),
    "troll" => array("character_languages", "value", 6),
    "ogre" => array("character_languages", "value", 7),
    "gnomish" => array("character_languages", "value", 8),
    "halfling" => array("character_languages", "value", 9),
    "thieves_cant" => array("character_languages", "value", 10),
    "old_erudian" => array("character_languages", "value", 11),
    "elder_elvish" => array("character_languages", "value", 12),
    "froglok" => array("character_languages", "value", 13),
    "goblin" => array("character_languages", "value", 14),
    "gnoll" => array("character_languages", "value", 15),
    "combine_tongue" => array("character_languages", "value", 16),
    "elder_teirdal" => array("character_languages", "value", 17),
    "lizardman" => array("character_languages", "value", 18),
    "orcish" => array("character_languages", "value", 19),
    "faerie" => array("character_languages", "value", 20),
    "dragon" => array("character_languages", "value", 21),
    "elder_dragon" => array("character_languages", "value", 22),
    "dark_speech" => array("character_languages", "value", 23),
    "vah_shir" => array("character_languages", "value", 24),
    "aa_id_0" => array("character_alternate_abilities", "aa_id", 0),
    "aa_id_1" => array("character_alternate_abilities", "aa_id", 1),
    "aa_id_2" => array("character_alternate_abilities", "aa_id", 2),
    "aa_id_3" => array("character_alternate_abilities", "aa_id", 3),
    "aa_id_4" => array("character_alternate_abilities", "aa_id", 4),
    "aa_id_5" => array("character_alternate_abilities", "aa_id", 5),
    "aa_id_6" => array("character_alternate_abilities", "aa_id", 6),
    "aa_id_7" => array("character_alternate_abilities", "aa_id", 7),
    "aa_id_8" => array("character_alternate_abilities", "aa_id", 8),
    "aa_id_9" => array("character_alternate_abilities", "aa_id", 9),
    "aa_id_10" => array("character_alternate_abilities", "aa_id", 10),
    "aa_id_11" => array("character_alternate_abilities", "aa_id", 11),
    "aa_id_12" => array("character_alternate_abilities", "aa_id", 12),
    "aa_id_13" => array("character_alternate_abilities", "aa_id", 13),
    "aa_id_14" => array("character_alternate_abilities", "aa_id", 14),
    "aa_id_15" => array("character_alternate_abilities", "aa_id", 15),
    "aa_id_16" => array("character_alternate_abilities", "aa_id", 16),
    "aa_id_17" => array("character_alternate_abilities", "aa_id", 17),
    "aa_id_18" => array("character_alternate_abilities", "aa_id", 18),
    "aa_id_19" => array("character_alternate_abilities", "aa_id", 19),
    "aa_id_20" => array("character_alternate_abilities", "aa_id", 20),
    "aa_id_21" => array("character_alternate_abilities", "aa_id", 21),
    "aa_id_22" => array("character_alternate_abilities", "aa_id", 22),
    "aa_id_23" => array("character_alternate_abilities", "aa_id", 23),
    "aa_id_24" => array("character_alternate_abilities", "aa_id", 24),
    "aa_id_25" => array("character_alternate_abilities", "aa_id", 25),
    "aa_id_26" => array("character_alternate_abilities", "aa_id", 26),
    "aa_id_27" => array("character_alternate_abilities", "aa_id", 27),
    "aa_id_28" => array("character_alternate_abilities", "aa_id", 28),
    "aa_id_29" => array("character_alternate_abilities", "aa_id", 29),
    "aa_id_30" => array("character_alternate_abilities", "aa_id", 30),
    "aa_id_31" => array("character_alternate_abilities", "aa_id", 31),
    "aa_id_32" => array("character_alternate_abilities", "aa_id", 32),
    "aa_id_33" => array("character_alternate_abilities", "aa_id", 33),
    "aa_id_34" => array("character_alternate_abilities", "aa_id", 34),
    "aa_id_35" => array("character_alternate_abilities", "aa_id", 35),
    "aa_id_36" => array("character_alternate_abilities", "aa_id", 36),
    "aa_id_37" => array("character_alternate_abilities", "aa_id", 37),
    "aa_id_38" => array("character_alternate_abilities", "aa_id", 38),
    "aa_id_39" => array("character_alternate_abilities", "aa_id", 39),
    "aa_id_40" => array("character_alternate_abilities", "aa_id", 40),
    "aa_id_41" => array("character_alternate_abilities", "aa_id", 41),
    "aa_id_42" => array("character_alternate_abilities", "aa_id", 42),
    "aa_id_43" => array("character_alternate_abilities", "aa_id", 43),
    "aa_id_44" => array("character_alternate_abilities", "aa_id", 44),
    "aa_id_45" => array("character_alternate_abilities", "aa_id", 45),
    "aa_id_46" => array("character_alternate_abilities", "aa_id", 46),
    "aa_id_47" => array("character_alternate_abilities", "aa_id", 47),
    "aa_id_48" => array("character_alternate_abilities", "aa_id", 48),
    "aa_id_49" => array("character_alternate_abilities", "aa_id", 49),
    "aa_id_50" => array("character_alternate_abilities", "aa_id", 50),
    "aa_id_51" => array("character_alternate_abilities", "aa_id", 51),
    "aa_id_52" => array("character_alternate_abilities", "aa_id", 52),
    "aa_id_53" => array("character_alternate_abilities", "aa_id", 53),
    "aa_id_54" => array("character_alternate_abilities", "aa_id", 54),
    "aa_id_55" => array("character_alternate_abilities", "aa_id", 55),
    "aa_id_56" => array("character_alternate_abilities", "aa_id", 56),
    "aa_id_57" => array("character_alternate_abilities", "aa_id", 57),
    "aa_id_58" => array("character_alternate_abilities", "aa_id", 58),
    "aa_id_59" => array("character_alternate_abilities", "aa_id", 59),
    "aa_id_60" => array("character_alternate_abilities", "aa_id", 60),
    "aa_id_61" => array("character_alternate_abilities", "aa_id", 61),
    "aa_id_62" => array("character_alternate_abilities", "aa_id", 62),
    "aa_id_63" => array("character_alternate_abilities", "aa_id", 63),
    "aa_id_64" => array("character_alternate_abilities", "aa_id", 64),
    "aa_id_65" => array("character_alternate_abilities", "aa_id", 65),
    "aa_id_66" => array("character_alternate_abilities", "aa_id", 66),
    "aa_id_67" => array("character_alternate_abilities", "aa_id", 67),
    "aa_id_68" => array("character_alternate_abilities", "aa_id", 68),
    "aa_id_69" => array("character_alternate_abilities", "aa_id", 69),
    "aa_id_70" => array("character_alternate_abilities", "aa_id", 70),
    "aa_id_71" => array("character_alternate_abilities", "aa_id", 71),
    "aa_id_72" => array("character_alternate_abilities", "aa_id", 72),
    "aa_id_73" => array("character_alternate_abilities", "aa_id", 73),
    "aa_id_74" => array("character_alternate_abilities", "aa_id", 74),
    "aa_id_75" => array("character_alternate_abilities", "aa_id", 75),
    "aa_id_76" => array("character_alternate_abilities", "aa_id", 76),
    "aa_id_77" => array("character_alternate_abilities", "aa_id", 77),
    "aa_id_78" => array("character_alternate_abilities", "aa_id", 78),
    "aa_id_79" => array("character_alternate_abilities", "aa_id", 79),
    "aa_id_80" => array("character_alternate_abilities", "aa_id", 80),
    "aa_id_81" => array("character_alternate_abilities", "aa_id", 81),
    "aa_id_82" => array("character_alternate_abilities", "aa_id", 82),
    "aa_id_83" => array("character_alternate_abilities", "aa_id", 83),
    "aa_id_84" => array("character_alternate_abilities", "aa_id", 84),
    "aa_id_85" => array("character_alternate_abilities", "aa_id", 85),
    "aa_id_86" => array("character_alternate_abilities", "aa_id", 86),
    "aa_id_87" => array("character_alternate_abilities", "aa_id", 87),
    "aa_id_88" => array("character_alternate_abilities", "aa_id", 88),
    "aa_id_89" => array("character_alternate_abilities", "aa_id", 89),
    "aa_id_90" => array("character_alternate_abilities", "aa_id", 90),
    "aa_id_91" => array("character_alternate_abilities", "aa_id", 91),
    "aa_id_92" => array("character_alternate_abilities", "aa_id", 92),
    "aa_id_93" => array("character_alternate_abilities", "aa_id", 93),
    "aa_id_94" => array("character_alternate_abilities", "aa_id", 94),
    "aa_id_95" => array("character_alternate_abilities", "aa_id", 95),
    "aa_id_96" => array("character_alternate_abilities", "aa_id", 96),
    "aa_id_97" => array("character_alternate_abilities", "aa_id", 97),
    "aa_id_98" => array("character_alternate_abilities", "aa_id", 98),
    "aa_id_99" => array("character_alternate_abilities", "aa_id", 99),
    "aa_id_100" => array("character_alternate_abilities", "aa_id", 100),
    "aa_id_101" => array("character_alternate_abilities", "aa_id", 101),
    "aa_id_102" => array("character_alternate_abilities", "aa_id", 102),
    "aa_id_103" => array("character_alternate_abilities", "aa_id", 103),
    "aa_id_104" => array("character_alternate_abilities", "aa_id", 104),
    "aa_id_105" => array("character_alternate_abilities", "aa_id", 105),
    "aa_id_106" => array("character_alternate_abilities", "aa_id", 106),
    "aa_id_107" => array("character_alternate_abilities", "aa_id", 107),
    "aa_id_108" => array("character_alternate_abilities", "aa_id", 108),
    "aa_id_109" => array("character_alternate_abilities", "aa_id", 109),
    "aa_id_110" => array("character_alternate_abilities", "aa_id", 110),
    "aa_id_111" => array("character_alternate_abilities", "aa_id", 111),
    "aa_id_112" => array("character_alternate_abilities", "aa_id", 112),
    "aa_id_113" => array("character_alternate_abilities", "aa_id", 113),
    "aa_id_114" => array("character_alternate_abilities", "aa_id", 114),
    "aa_id_115" => array("character_alternate_abilities", "aa_id", 115),
    "aa_id_116" => array("character_alternate_abilities", "aa_id", 116),
    "aa_id_117" => array("character_alternate_abilities", "aa_id", 117),
    "aa_id_118" => array("character_alternate_abilities", "aa_id", 118),
    "aa_id_119" => array("character_alternate_abilities", "aa_id", 119),
    "aa_id_120" => array("character_alternate_abilities", "aa_id", 120),
    "aa_id_121" => array("character_alternate_abilities", "aa_id", 121),
    "aa_id_122" => array("character_alternate_abilities", "aa_id", 122),
    "aa_id_123" => array("character_alternate_abilities", "aa_id", 123),
    "aa_id_124" => array("character_alternate_abilities", "aa_id", 124),
    "aa_id_125" => array("character_alternate_abilities", "aa_id", 125),
    "aa_id_126" => array("character_alternate_abilities", "aa_id", 126),
    "aa_id_127" => array("character_alternate_abilities", "aa_id", 127),
    "aa_id_128" => array("character_alternate_abilities", "aa_id", 128),
    "aa_id_129" => array("character_alternate_abilities", "aa_id", 129),
    "aa_id_130" => array("character_alternate_abilities", "aa_id", 130),
    "aa_id_131" => array("character_alternate_abilities", "aa_id", 131),
    "aa_id_132" => array("character_alternate_abilities", "aa_id", 132),
    "aa_id_133" => array("character_alternate_abilities", "aa_id", 133),
    "aa_id_134" => array("character_alternate_abilities", "aa_id", 134),
    "aa_id_135" => array("character_alternate_abilities", "aa_id", 135),
    "aa_id_136" => array("character_alternate_abilities", "aa_id", 136),
    "aa_id_137" => array("character_alternate_abilities", "aa_id", 137),
    "aa_id_138" => array("character_alternate_abilities", "aa_id", 138),
    "aa_id_139" => array("character_alternate_abilities", "aa_id", 139),
    "aa_id_140" => array("character_alternate_abilities", "aa_id", 140),
    "aa_id_141" => array("character_alternate_abilities", "aa_id", 141),
    "aa_id_142" => array("character_alternate_abilities", "aa_id", 142),
    "aa_id_143" => array("character_alternate_abilities", "aa_id", 143),
    "aa_id_144" => array("character_alternate_abilities", "aa_id", 144),
    "aa_id_145" => array("character_alternate_abilities", "aa_id", 145),
    "aa_id_146" => array("character_alternate_abilities", "aa_id", 146),
    "aa_id_147" => array("character_alternate_abilities", "aa_id", 147),
    "aa_id_148" => array("character_alternate_abilities", "aa_id", 148),
    "aa_id_149" => array("character_alternate_abilities", "aa_id", 149),
    "aa_id_150" => array("character_alternate_abilities", "aa_id", 150),
    "aa_id_151" => array("character_alternate_abilities", "aa_id", 151),
    "aa_id_152" => array("character_alternate_abilities", "aa_id", 152),
    "aa_id_153" => array("character_alternate_abilities", "aa_id", 153),
    "aa_id_154" => array("character_alternate_abilities", "aa_id", 154),
    "aa_id_155" => array("character_alternate_abilities", "aa_id", 155),
    "aa_id_156" => array("character_alternate_abilities", "aa_id", 156),
    "aa_id_157" => array("character_alternate_abilities", "aa_id", 157),
    "aa_id_158" => array("character_alternate_abilities", "aa_id", 158),
    "aa_id_159" => array("character_alternate_abilities", "aa_id", 159),
    "aa_id_160" => array("character_alternate_abilities", "aa_id", 160),
    "aa_id_161" => array("character_alternate_abilities", "aa_id", 161),
    "aa_id_162" => array("character_alternate_abilities", "aa_id", 162),
    "aa_id_163" => array("character_alternate_abilities", "aa_id", 163),
    "aa_id_164" => array("character_alternate_abilities", "aa_id", 164),
    "aa_id_165" => array("character_alternate_abilities", "aa_id", 165),
    "aa_id_166" => array("character_alternate_abilities", "aa_id", 166),
    "aa_id_167" => array("character_alternate_abilities", "aa_id", 167),
    "aa_id_168" => array("character_alternate_abilities", "aa_id", 168),
    "aa_id_169" => array("character_alternate_abilities", "aa_id", 169),
    "aa_id_170" => array("character_alternate_abilities", "aa_id", 170),
    "aa_id_171" => array("character_alternate_abilities", "aa_id", 171),
    "aa_id_172" => array("character_alternate_abilities", "aa_id", 172),
    "aa_id_173" => array("character_alternate_abilities", "aa_id", 173),
    "aa_id_174" => array("character_alternate_abilities", "aa_id", 174),
    "aa_id_175" => array("character_alternate_abilities", "aa_id", 175),
    "aa_id_176" => array("character_alternate_abilities", "aa_id", 176),
    "aa_id_177" => array("character_alternate_abilities", "aa_id", 177),
    "aa_id_178" => array("character_alternate_abilities", "aa_id", 178),
    "aa_id_179" => array("character_alternate_abilities", "aa_id", 179),
    "aa_id_180" => array("character_alternate_abilities", "aa_id", 180),
    "aa_id_181" => array("character_alternate_abilities", "aa_id", 181),
    "aa_id_182" => array("character_alternate_abilities", "aa_id", 182),
    "aa_id_183" => array("character_alternate_abilities", "aa_id", 183),
    "aa_id_184" => array("character_alternate_abilities", "aa_id", 184),
    "aa_id_185" => array("character_alternate_abilities", "aa_id", 185),
    "aa_id_186" => array("character_alternate_abilities", "aa_id", 186),
    "aa_id_187" => array("character_alternate_abilities", "aa_id", 187),
    "aa_id_188" => array("character_alternate_abilities", "aa_id", 188),
    "aa_id_189" => array("character_alternate_abilities", "aa_id", 189),
    "aa_id_190" => array("character_alternate_abilities", "aa_id", 190),
    "aa_id_191" => array("character_alternate_abilities", "aa_id", 191),
    "aa_id_192" => array("character_alternate_abilities", "aa_id", 192),
    "aa_id_193" => array("character_alternate_abilities", "aa_id", 193),
    "aa_id_194" => array("character_alternate_abilities", "aa_id", 194),
    "aa_id_195" => array("character_alternate_abilities", "aa_id", 195),
    "aa_id_196" => array("character_alternate_abilities", "aa_id", 196),
    "aa_id_197" => array("character_alternate_abilities", "aa_id", 197),
    "aa_id_198" => array("character_alternate_abilities", "aa_id", 198),
    "aa_id_199" => array("character_alternate_abilities", "aa_id", 199),
    "aa_value_0" => array("character_alternate_abilities", "aa_value", 0),
    "aa_value_1" => array("character_alternate_abilities", "aa_value", 1),
    "aa_value_2" => array("character_alternate_abilities", "aa_value", 2),
    "aa_value_3" => array("character_alternate_abilities", "aa_value", 3),
    "aa_value_4" => array("character_alternate_abilities", "aa_value", 4),
    "aa_value_5" => array("character_alternate_abilities", "aa_value", 5),
    "aa_value_6" => array("character_alternate_abilities", "aa_value", 6),
    "aa_value_7" => array("character_alternate_abilities", "aa_value", 7),
    "aa_value_8" => array("character_alternate_abilities", "aa_value", 8),
    "aa_value_9" => array("character_alternate_abilities", "aa_value", 9),
    "aa_value_10" => array("character_alternate_abilities", "aa_value", 10),
    "aa_value_11" => array("character_alternate_abilities", "aa_value", 11),
    "aa_value_12" => array("character_alternate_abilities", "aa_value", 12),
    "aa_value_13" => array("character_alternate_abilities", "aa_value", 13),
    "aa_value_14" => array("character_alternate_abilities", "aa_value", 14),
    "aa_value_15" => array("character_alternate_abilities", "aa_value", 15),
    "aa_value_16" => array("character_alternate_abilities", "aa_value", 16),
    "aa_value_17" => array("character_alternate_abilities", "aa_value", 17),
    "aa_value_18" => array("character_alternate_abilities", "aa_value", 18),
    "aa_value_19" => array("character_alternate_abilities", "aa_value", 19),
    "aa_value_20" => array("character_alternate_abilities", "aa_value", 20),
    "aa_value_21" => array("character_alternate_abilities", "aa_value", 21),
    "aa_value_22" => array("character_alternate_abilities", "aa_value", 22),
    "aa_value_23" => array("character_alternate_abilities", "aa_value", 23),
    "aa_value_24" => array("character_alternate_abilities", "aa_value", 24),
    "aa_value_25" => array("character_alternate_abilities", "aa_value", 25),
    "aa_value_26" => array("character_alternate_abilities", "aa_value", 26),
    "aa_value_27" => array("character_alternate_abilities", "aa_value", 27),
    "aa_value_28" => array("character_alternate_abilities", "aa_value", 28),
    "aa_value_29" => array("character_alternate_abilities", "aa_value", 29),
    "aa_value_30" => array("character_alternate_abilities", "aa_value", 30),
    "aa_value_31" => array("character_alternate_abilities", "aa_value", 31),
    "aa_value_32" => array("character_alternate_abilities", "aa_value", 32),
    "aa_value_33" => array("character_alternate_abilities", "aa_value", 33),
    "aa_value_34" => array("character_alternate_abilities", "aa_value", 34),
    "aa_value_35" => array("character_alternate_abilities", "aa_value", 35),
    "aa_value_36" => array("character_alternate_abilities", "aa_value", 36),
    "aa_value_37" => array("character_alternate_abilities", "aa_value", 37),
    "aa_value_38" => array("character_alternate_abilities", "aa_value", 38),
    "aa_value_39" => array("character_alternate_abilities", "aa_value", 39),
    "aa_value_40" => array("character_alternate_abilities", "aa_value", 40),
    "aa_value_41" => array("character_alternate_abilities", "aa_value", 41),
    "aa_value_42" => array("character_alternate_abilities", "aa_value", 42),
    "aa_value_43" => array("character_alternate_abilities", "aa_value", 43),
    "aa_value_44" => array("character_alternate_abilities", "aa_value", 44),
    "aa_value_45" => array("character_alternate_abilities", "aa_value", 45),
    "aa_value_46" => array("character_alternate_abilities", "aa_value", 46),
    "aa_value_47" => array("character_alternate_abilities", "aa_value", 47),
    "aa_value_48" => array("character_alternate_abilities", "aa_value", 48),
    "aa_value_49" => array("character_alternate_abilities", "aa_value", 49),
    "aa_value_50" => array("character_alternate_abilities", "aa_value", 50),
    "aa_value_51" => array("character_alternate_abilities", "aa_value", 51),
    "aa_value_52" => array("character_alternate_abilities", "aa_value", 52),
    "aa_value_53" => array("character_alternate_abilities", "aa_value", 53),
    "aa_value_54" => array("character_alternate_abilities", "aa_value", 54),
    "aa_value_55" => array("character_alternate_abilities", "aa_value", 55),
    "aa_value_56" => array("character_alternate_abilities", "aa_value", 56),
    "aa_value_57" => array("character_alternate_abilities", "aa_value", 57),
    "aa_value_58" => array("character_alternate_abilities", "aa_value", 58),
    "aa_value_59" => array("character_alternate_abilities", "aa_value", 59),
    "aa_value_60" => array("character_alternate_abilities", "aa_value", 60),
    "aa_value_61" => array("character_alternate_abilities", "aa_value", 61),
    "aa_value_62" => array("character_alternate_abilities", "aa_value", 62),
    "aa_value_63" => array("character_alternate_abilities", "aa_value", 63),
    "aa_value_64" => array("character_alternate_abilities", "aa_value", 64),
    "aa_value_65" => array("character_alternate_abilities", "aa_value", 65),
    "aa_value_66" => array("character_alternate_abilities", "aa_value", 66),
    "aa_value_67" => array("character_alternate_abilities", "aa_value", 67),
    "aa_value_68" => array("character_alternate_abilities", "aa_value", 68),
    "aa_value_69" => array("character_alternate_abilities", "aa_value", 69),
    "aa_value_70" => array("character_alternate_abilities", "aa_value", 70),
    "aa_value_71" => array("character_alternate_abilities", "aa_value", 71),
    "aa_value_72" => array("character_alternate_abilities", "aa_value", 72),
    "aa_value_73" => array("character_alternate_abilities", "aa_value", 73),
    "aa_value_74" => array("character_alternate_abilities", "aa_value", 74),
    "aa_value_75" => array("character_alternate_abilities", "aa_value", 75),
    "aa_value_76" => array("character_alternate_abilities", "aa_value", 76),
    "aa_value_77" => array("character_alternate_abilities", "aa_value", 77),
    "aa_value_78" => array("character_alternate_abilities", "aa_value", 78),
    "aa_value_79" => array("character_alternate_abilities", "aa_value", 79),
    "aa_value_80" => array("character_alternate_abilities", "aa_value", 80),
    "aa_value_81" => array("character_alternate_abilities", "aa_value", 81),
    "aa_value_82" => array("character_alternate_abilities", "aa_value", 82),
    "aa_value_83" => array("character_alternate_abilities", "aa_value", 83),
    "aa_value_84" => array("character_alternate_abilities", "aa_value", 84),
    "aa_value_85" => array("character_alternate_abilities", "aa_value", 85),
    "aa_value_86" => array("character_alternate_abilities", "aa_value", 86),
    "aa_value_87" => array("character_alternate_abilities", "aa_value", 87),
    "aa_value_88" => array("character_alternate_abilities", "aa_value", 88),
    "aa_value_89" => array("character_alternate_abilities", "aa_value", 89),
    "aa_value_90" => array("character_alternate_abilities", "aa_value", 90),
    "aa_value_91" => array("character_alternate_abilities", "aa_value", 91),
    "aa_value_92" => array("character_alternate_abilities", "aa_value", 92),
    "aa_value_93" => array("character_alternate_abilities", "aa_value", 93),
    "aa_value_94" => array("character_alternate_abilities", "aa_value", 94),
    "aa_value_95" => array("character_alternate_abilities", "aa_value", 95),
    "aa_value_96" => array("character_alternate_abilities", "aa_value", 96),
    "aa_value_97" => array("character_alternate_abilities", "aa_value", 97),
    "aa_value_98" => array("character_alternate_abilities", "aa_value", 98),
    "aa_value_99" => array("character_alternate_abilities", "aa_value", 99),
    "aa_value_100" => array("character_alternate_abilities", "aa_value", 100),
    "aa_value_101" => array("character_alternate_abilities", "aa_value", 101),
    "aa_value_102" => array("character_alternate_abilities", "aa_value", 102),
    "aa_value_103" => array("character_alternate_abilities", "aa_value", 103),
    "aa_value_104" => array("character_alternate_abilities", "aa_value", 104),
    "aa_value_105" => array("character_alternate_abilities", "aa_value", 105),
    "aa_value_106" => array("character_alternate_abilities", "aa_value", 106),
    "aa_value_107" => array("character_alternate_abilities", "aa_value", 107),
    "aa_value_108" => array("character_alternate_abilities", "aa_value", 108),
    "aa_value_109" => array("character_alternate_abilities", "aa_value", 109),
    "aa_value_110" => array("character_alternate_abilities", "aa_value", 110),
    "aa_value_111" => array("character_alternate_abilities", "aa_value", 111),
    "aa_value_112" => array("character_alternate_abilities", "aa_value", 112),
    "aa_value_113" => array("character_alternate_abilities", "aa_value", 113),
    "aa_value_114" => array("character_alternate_abilities", "aa_value", 114),
    "aa_value_115" => array("character_alternate_abilities", "aa_value", 115),
    "aa_value_116" => array("character_alternate_abilities", "aa_value", 116),
    "aa_value_117" => array("character_alternate_abilities", "aa_value", 117),
    "aa_value_118" => array("character_alternate_abilities", "aa_value", 118),
    "aa_value_119" => array("character_alternate_abilities", "aa_value", 119),
    "aa_value_120" => array("character_alternate_abilities", "aa_value", 120),
    "aa_value_121" => array("character_alternate_abilities", "aa_value", 121),
    "aa_value_122" => array("character_alternate_abilities", "aa_value", 122),
    "aa_value_123" => array("character_alternate_abilities", "aa_value", 123),
    "aa_value_124" => array("character_alternate_abilities", "aa_value", 124),
    "aa_value_125" => array("character_alternate_abilities", "aa_value", 125),
    "aa_value_126" => array("character_alternate_abilities", "aa_value", 126),
    "aa_value_127" => array("character_alternate_abilities", "aa_value", 127),
    "aa_value_128" => array("character_alternate_abilities", "aa_value", 128),
    "aa_value_129" => array("character_alternate_abilities", "aa_value", 129),
    "aa_value_130" => array("character_alternate_abilities", "aa_value", 130),
    "aa_value_131" => array("character_alternate_abilities", "aa_value", 131),
    "aa_value_132" => array("character_alternate_abilities", "aa_value", 132),
    "aa_value_133" => array("character_alternate_abilities", "aa_value", 133),
    "aa_value_134" => array("character_alternate_abilities", "aa_value", 134),
    "aa_value_135" => array("character_alternate_abilities", "aa_value", 135),
    "aa_value_136" => array("character_alternate_abilities", "aa_value", 136),
    "aa_value_137" => array("character_alternate_abilities", "aa_value", 137),
    "aa_value_138" => array("character_alternate_abilities", "aa_value", 138),
    "aa_value_139" => array("character_alternate_abilities", "aa_value", 139),
    "aa_value_140" => array("character_alternate_abilities", "aa_value", 140),
    "aa_value_141" => array("character_alternate_abilities", "aa_value", 141),
    "aa_value_142" => array("character_alternate_abilities", "aa_value", 142),
    "aa_value_143" => array("character_alternate_abilities", "aa_value", 143),
    "aa_value_144" => array("character_alternate_abilities", "aa_value", 144),
    "aa_value_145" => array("character_alternate_abilities", "aa_value", 145),
    "aa_value_146" => array("character_alternate_abilities", "aa_value", 146),
    "aa_value_147" => array("character_alternate_abilities", "aa_value", 147),
    "aa_value_148" => array("character_alternate_abilities", "aa_value", 148),
    "aa_value_149" => array("character_alternate_abilities", "aa_value", 149),
    "aa_value_150" => array("character_alternate_abilities", "aa_value", 150),
    "aa_value_151" => array("character_alternate_abilities", "aa_value", 151),
    "aa_value_152" => array("character_alternate_abilities", "aa_value", 152),
    "aa_value_153" => array("character_alternate_abilities", "aa_value", 153),
    "aa_value_154" => array("character_alternate_abilities", "aa_value", 154),
    "aa_value_155" => array("character_alternate_abilities", "aa_value", 155),
    "aa_value_156" => array("character_alternate_abilities", "aa_value", 156),
    "aa_value_157" => array("character_alternate_abilities", "aa_value", 157),
    "aa_value_158" => array("character_alternate_abilities", "aa_value", 158),
    "aa_value_159" => array("character_alternate_abilities", "aa_value", 159),
    "aa_value_160" => array("character_alternate_abilities", "aa_value", 160),
    "aa_value_161" => array("character_alternate_abilities", "aa_value", 161),
    "aa_value_162" => array("character_alternate_abilities", "aa_value", 162),
    "aa_value_163" => array("character_alternate_abilities", "aa_value", 163),
    "aa_value_164" => array("character_alternate_abilities", "aa_value", 164),
    "aa_value_165" => array("character_alternate_abilities", "aa_value", 165),
    "aa_value_166" => array("character_alternate_abilities", "aa_value", 166),
    "aa_value_167" => array("character_alternate_abilities", "aa_value", 167),
    "aa_value_168" => array("character_alternate_abilities", "aa_value", 168),
    "aa_value_169" => array("character_alternate_abilities", "aa_value", 169),
    "aa_value_170" => array("character_alternate_abilities", "aa_value", 170),
    "aa_value_171" => array("character_alternate_abilities", "aa_value", 171),
    "aa_value_172" => array("character_alternate_abilities", "aa_value", 172),
    "aa_value_173" => array("character_alternate_abilities", "aa_value", 173),
    "aa_value_174" => array("character_alternate_abilities", "aa_value", 174),
    "aa_value_175" => array("character_alternate_abilities", "aa_value", 175),
    "aa_value_176" => array("character_alternate_abilities", "aa_value", 176),
    "aa_value_177" => array("character_alternate_abilities", "aa_value", 177),
    "aa_value_178" => array("character_alternate_abilities", "aa_value", 178),
    "aa_value_179" => array("character_alternate_abilities", "aa_value", 179),
    "aa_value_180" => array("character_alternate_abilities", "aa_value", 180),
    "aa_value_181" => array("character_alternate_abilities", "aa_value", 181),
    "aa_value_182" => array("character_alternate_abilities", "aa_value", 182),
    "aa_value_183" => array("character_alternate_abilities", "aa_value", 183),
    "aa_value_184" => array("character_alternate_abilities", "aa_value", 184),
    "aa_value_185" => array("character_alternate_abilities", "aa_value", 185),
    "aa_value_186" => array("character_alternate_abilities", "aa_value", 186),
    "aa_value_187" => array("character_alternate_abilities", "aa_value", 187),
    "aa_value_188" => array("character_alternate_abilities", "aa_value", 188),
    "aa_value_189" => array("character_alternate_abilities", "aa_value", 189),
    "aa_value_190" => array("character_alternate_abilities", "aa_value", 190),
    "aa_value_191" => array("character_alternate_abilities", "aa_value", 191),
    "aa_value_192" => array("character_alternate_abilities", "aa_value", 192),
    "aa_value_193" => array("character_alternate_abilities", "aa_value", 193),
    "aa_value_194" => array("character_alternate_abilities", "aa_value", 194),
    "aa_value_195" => array("character_alternate_abilities", "aa_value", 195),
    "aa_value_196" => array("character_alternate_abilities", "aa_value", 196),
    "aa_value_197" => array("character_alternate_abilities", "aa_value", 197),
    "aa_value_198" => array("character_alternate_abilities", "aa_value", 198),
    "aa_value_199" => array("character_alternate_abilities", "aa_value", 199),
);

class profile {
   // Variables
   private $cached_tables = array();
   private $cached_records = array();
   private $account_id;
   private $char_id;

   /********************************************
    **              CONSTRUCTOR                **
    ********************************************/
   // get the basic data, like char id.
   function __construct($name) {
      global $language, $game_db;

      //we can't call the local query method as it assumes the character id
      //which we need to get in the first place
      $table_name = "character_data";

      //don't go sticking just anything in the database
      if (!IsAlphaSpace($name)) message_die($language['MESSAGE_ERROR'],$language['MESSAGE_NAME_ALPHA']);

      //build the query
      $template = "SELECT * FROM `%s` WHERE `name` = '%s'";
      $query = sprintf($template, $table_name, $name);

      //gather database stats
      if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query);

      //get the results/error
      $results = $game_db->query($query) or message_die('profile.php', $query, mysql_error());

      //collect the data from returned row
      if($row = fetchRows($results)) {
         //save it
         $this->cached_records[$table_name] = $row[0];
         $this->account_id = $row[0]['account_id'];
         $this->char_id = $row[0]['id'];
      }
      else message_die($language['MESSAGE_ERROR'],$language['MESSAGE_NO_FIND']);
   }

   /********************************************
    **            PUBLIC FUNCTIONS             **
    ********************************************/

   // Return Account ID
   public function accountid() { return $this->account_id; }

   // Return char ID
   public function char_id() { return $this->char_id; }

   //gets all the records for a double pk character from a table
   public function GetTable($table_name) {
      //we don't need to clean the name up before
      //handing it to the private function because
      //it has to bee in the locator arrays
      return $this->_getTableCache($table_name);
   }

   //gets a single record for a character from a table
   public function GetRecord($table_name) {
       global $game_db;
      //table name goes straight into a query 
      // so we need to escape it
      $table_name = sanitize($game_db, $table_name);

      return $this->_getRecordCache($table_name);
   }

   //uses the locator data to find the requested setting
   public function GetValue($data_key) { return $this->_getValue($data_key); }

   /********************************************
    **            PRIVATE FUNCTIONS            **
    ********************************************/

   //uses the locator data to find the requested setting
   private function _getValue($data_key) {
      global $locator, $language;

      // Pull Profile Info
      if (!array_key_exists($data_key, $locator)) {
         message_die('profile.php', sprintf($language['MESSAGE_PROF_NOKEY'], $data_key),$language['MESSAGE_ERROR']);
      }

      //get the locator data for this setting so we can find it
      $table_name  = $locator[$data_key][LOCATOR_TABLE];
      $column_name = $locator[$data_key][LOCATOR_COLUMN];
      $index       = $locator[$data_key][LOCATOR_INDEX];

      //if the locator lists a strict index of false then there
      //will only be 1 record
      if ($index === false) {
         //fetch the cached record
         $cached_record = $this->_getRecordCache($table_name);
      }

      //otherwise the locator lists a numeric value representing
      //the value of the second pk
      else {
         //fetch this table from the db/cache
         $cached_table = $this->_getTableCache($table_name);

         //this is not a failure, this just means the character doesn't have a record
         //for this skill, or whatever is being requested
         if (!array_key_exists($index, $cached_table))
         {
            return false;
         }
         $cached_record = $cached_table[$index];
      }

      //make sure our column exists in the record
      if (!array_key_exists($column_name, $cached_record)) {
         message_die('profile.php', sprintf($language['MESSAGE_PROF_NOCACHE'], $data_key, $table_name, $column_name),$language['MESSAGE_ERROR']);
      }

      //return the value
      return $cached_record[$column_name];
   }

   // gets a TABLE, it loads it into memory so the same TABLE
   // isnt double queried. It keeps every record and uses the 
   // second column as the array index 
   private function _getTableCache($table_name) {
      global $locator_pk, $language;

      //get the name of the second pk on the table
      if (!array_key_exists($table_name, $locator_pk)) {
         message_die('profile.php', sprintf($language['MESSAGE_PROF_NOTABKEY'], $table_name),$language['MESSAGE_ERROR']);
      }
      $second_column_name = $locator_pk[$table_name];

      //if we haven't already loaded data from this table then load it
      if (!array_key_exists($table_name, $this->cached_tables)) {
         //since we are accessing the database, we'll go ahead and 
         //load every column for the character and store it for later use
         $results = $this->_doCharacterQuery($table_name);

         //parse the results
         if(numRows($results) != 0) {
            //this is a table with two primary keys, we need to load it
            //into a supporting array, indexed by it's second pk
            $temp_array = array();

             foreach($results AS $row) {
               $temp_array[$row[$second_column_name]] = $row;
            }

            $this->cached_tables[$table_name] = $temp_array;
         }
         else message_die('profile.php', sprintf($language['MESSAGE_PROF_NOROWS'], $table_name),$language['MESSAGE_ERROR']);
      }

      //hand the table/record over
      return $this->cached_tables[$table_name];
   }

   // gets a RECORD, it loads it into memory so the same RECORD
   // isnt double queried.
   private function _getRecordCache($table_name) {
      global $language;

      //if we haven't already loaded data from this table then load it
      if (!array_key_exists($table_name, $this->cached_records)) {
         //since we are accessing the database, we'll go ahead and 
         //load every column for the character and store it for later use
         $results = $this->_doCharacterQuery($table_name);

         //parse the results
         if(numRows($results) != 0) {
            //this is a simple table with only 1 row per character
            //we just store it in the root structure
            $this->cached_records[$table_name] = fetchRows($results)[0];
         }
         else message_die('profile.php', sprintf($language['MESSAGE_PROF_NOROWS'], $table_name),$language['MESSAGE_ERROR']);
      }

      //hand the table/record over
      return $this->cached_records[$table_name];
   }

   //gets all the records from a table for this character instance
   //we even get ones we dont need; they'll get cached for later use
   private function _doCharacterQuery($table_name) {
       global $game_db;
      //build the query
      $template = "SELECT * FROM `%s` WHERE `id` = '%d'";
      $query = sprintf($template, $table_name, $this->char_id);

      //gather database stats
      if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query);

      //get the results/error
       $results = $game_db->query($query) or message_die('profile.php', $query, mysql_error());

      //serve em up
      return $results;
   }
}

?>
