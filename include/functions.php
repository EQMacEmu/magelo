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
 *      added code to monitor database access upon request
 *
 ***************************************************************************/
 
if (!defined('INCHARBROWSER')) { die("Hacking attempt"); }

include_once("language.php");
include_once("config.php");
include_once("debug.php");
include_once("sql.php");

global $game_db;

//holds timers and log texts - added 9/28/2014
$dbp_time_start = array();
$dbp_output_buffer = array();

//starts an indexed timer - added 9/28/2014
function dbp_mark_time($index) {
   global $dbp_time_start;
   $dbp_time_start[$index] = microtime();
} 

//returns a string describing how long an indexed timer has been running - added 9/28/2014
function dbp_check_time($index) {
   global $dbp_time_start;
   list($old_usec, $old_sec) = explode(' ',$dbp_time_start[$index]);
   list($new_usec, $new_sec) = explode(' ',microtime());
   $old_mt = ((float)$old_usec + (float)$old_sec);
   $new_mt = ((float)$new_usec + (float)$new_sec);
   $timeout = sprintf("%01.6f",($new_mt - $old_mt));
   return $index." took ".$timeout."sec<br />";
}

//gathers performance data on a query - added 9/28/2014
function dbp_query_stat($index, $query) {
	global $game_db;
   //start the timer
   dbp_mark_time($index);
   
   //execute the query
	$game_db->query($query);
   
   //stop the timer
   $time = dbp_check_time($index);
   
   //get an explanation of the query
	//$result = $game_db->query("EXPLAIN ".$query);
	$result = $game_db->query($query);
   $explain_rows = '';
	foreach($result AS $row) {
      $explain_rows .= 
      "            <tr>\n".
      "               <td>".$row['select_type']."</td>\n".
      "               <td>".$row['table']."</td>\n".
      "               <td>".$row['type']."</td>\n".
      "               <td>".$row['possible_keys']."</td>\n".
      "               <td>".$row['key']."</td>\n".
      "               <td>".$row['key_len']."</td>\n".
      "               <td>".$row['ref']."</td>\n".
      "               <td>".$row['rows']."</td>\n".
      "               <td>".$row['Extra']."</td>\n".
      "            </tr>\n";
   }
   
   $explain_head = "\n".
      "      <table class='db_explain'>\n".
      "         <caption>Explanation:</caption>\n".
      "         <thead>\n".
      "            <tr>\n".
      "               <th>select_type</th>\n".
      "               <th>table</th>\n".
      "               <th>type</th>\n".
      "               <th>possible_keys</th>\n".
      "               <th>key</th>\n".
      "               <th>key_len</th>\n".
      "               <th>ref</th>\n".
      "               <th>rows</th>\n".
      "               <th>Extra</th>\n".
      "            </tr>\n".
      "         </thead>\n".
      "         <tbody>\n";
      
   $explain_tail = 
      "         </tbody>\n".
      "      </table>\n";
   
   dbp_append_buffer($index, "   <div class='db_query'>\n");
   dbp_append_buffer($index, "      <div class='db_sql'>\n         <h1>Query:</h1>\n         <p>" . $query . "</p>\n      </div>\n");  
   dbp_append_buffer($index, "      <div class='db_time'>\n         <h1>Time:</h1>\n         <p>" . $time . "<p>\n      </div>\n");  
   dbp_append_buffer($index, $explain_head . $explain_rows . $explain_tail);
   dbp_append_buffer($index, "   </div>\n");
}

//appends text onto the log that gets output later - added 9/28/2014
function dbp_append_buffer($index,$text) {
   global $dbp_output_buffer;
   $dbp_output_buffer[$index] .= $text;
}

//clears the output buffer - added 9/28/2014
function dbp_clear_buffer($index) {
   global $dbp_output_buffer;
   $dbp_output_buffer[$index] = "";
}

//returns the output buffer - added 9/28/2014
function dbp_dump_buffer($index) {
   global $dbp_output_buffer;
   return "<div class='db_dump'>\n".$dbp_output_buffer[$index]."</div>\n";
}

function GetPermissions($gm, $anonlevel, $char_id) {
	global $permissions, $game_db;
         
   $query = "SELECT value FROM quest_globals WHERE charid = $char_id and name = 'charbrowser_profile';";
   if (defined('DB_PERFORMANCE')) dbp_query_stat('query', $query); //added 9/28/2014
	$results = $game_db->query($query);
	if (numRows($results)) {
		$row = fetchRows($results);
	   if ($row[0]['value'] == 1) return $permissions['PUBLIC'];
	   if ($row[0]['value'] == 2) return $permissions['PRIVATE'];
   }
	if ($gm) return $permissions['GM'];
	if ($anonlevel == 2)  return $permissions['ROLEPLAY'];
	if ($anonlevel == 1)  return $permissions['ANON'];
	return $permissions['ALL'];
}

function message_die($dietitle, $text) {
	global $language, $template;
	
	//drop page
	$d_title = " - ".$dietitle;
	include("include/header.php");
	
	$template->set_filenames(array(
	  'message' => 'message_body.tpl')
	);
	
	$template->assign_vars(array(  
	  'DIETITLE' => $dietitle,
	  'TEXT' => $text,
	  'L_BACK' => $language['BUTTON_BACK'])
	);
	
	$template->pparse('message');
	
	//dump footer
	include("include/footer.php");
	exit();
}

function message($title, $text) {
	global $language;
	global $template;
	$template->set_filenames(array(
	  'message' => 'message_body.tpl')
	);
	
	$template->assign_vars(array(  
	  'TITLE' => $title,
	  'TEXT' => $text,
	  'L_BACK' => $language['BUTTON_BACK'])
	);
	$template->pparse('message');
}

function generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = TRUE) {
	global $language;
	$total_pages = ceil($num_items/$per_page);
	if ($total_pages == 1) { return ''; }
	$on_page = floor($start_item / $per_page) + 1;
	$page_string = '';
	if ($total_pages > 10) {
		$init_page_max = ( $total_pages > 3 ) ? 3 : $total_pages;

		for($i=1;$i<$init_page_max+1;$i++) {
			$page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="' . ($base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
			if ($i <  $init_page_max)	{
				$page_string .= ", ";
			}
		}

		if ($total_pages > 3) {
			if ($on_page > 1  && $on_page < $total_pages) {
				$page_string .= ($on_page > 5) ? ' ... ' : ', ';

				$init_page_min = ($on_page > 4) ? $on_page : 5;
				$init_page_max = ($on_page < $total_pages - 4) ? $on_page : $total_pages - 4;

				for($i=$init_page_min-1;$i<$init_page_max+2;$i++) {
					$page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . ($base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
					if ($i <  $init_page_max + 1) { $page_string .= ', '; }
				}
				$page_string .= ( $on_page < $total_pages - 4 ) ? ' ... ' : ', ';
			}
			else { $page_string .= ' ... '; }

			for($i=$total_pages-2;$i<$total_pages+1;$i++) {
				$page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>'  : '<a href="' . ($base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
				if($i < $total_pages) { $page_string .= ", "; }
			}
		}
	}
	else {
		for($i=1;$i<$total_pages+1;$i++) {
			$page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="' . ($base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
			if ($i <  $total_pages) { $page_string .= ', '; }
		}
	}

	if ($add_prevnext_text)	{
		if ($on_page > 1) {
			$page_string = ' <a href="' . ($base_url . "&amp;start=" . ( ( $on_page - 2 ) * $per_page ) ) . '">' . $language['SEARCH_PREVIOUS'] . '</a>&nbsp;&nbsp;' . $page_string;
		}

		if ($on_page < $total_pages) {
			$page_string .= '&nbsp;&nbsp;<a href="' . ($base_url . "&amp;start=" . ( $on_page * $per_page ) ) . '">' . $language['SEARCH_NEXT'] . '</a>';
		}
	}
	$page_string = $lang['Goto_page'] . ' ' . $page_string;
	return $page_string;
}

function IsAlphaSpace($str) {
   $old = Array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", " ");
   $new = Array("", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "");
   if (str_replace($old, $new, $str) == "") { return (true); }
   else { return (false); }
}

function IsAlphaNumericSpace($str) {
   $old = Array(" ", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
   $new = Array("", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "");
   if (str_replace($old, $new, $str) == "") { return (true); }
   else { return (false); }
}
?>