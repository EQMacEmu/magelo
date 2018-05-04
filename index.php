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

//drop page
$d_title = $subtitle;
include("include/header.php");

$template->set_filenames(array(
  'index' => 'index_body.tpl')
);

$template->assign_vars(array(  
  'TITLE' => $mytitle,
  'VERSION' => $version,
  'L_VERSION' => $language['INDEX_VERSION'],
  'L_BY' => $language['INDEX_BY'],
  'L_INTRO' => $language['INDEX_INTRO'])
);

$template->pparse('index');

$template->destroy;

//added to monitor database performance 9/28/2014
if (defined('DB_PERFORMANCE')) print dbp_dump_buffer('query');

include("include/footer.php");

?>