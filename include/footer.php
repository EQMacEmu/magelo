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

$template->set_filenames(array(
  'footer' => 'footer_body.tpl')
);

$template->assign_vars(array(  
  'TITLE' => $mytitle,
  'VERSION' => $version,
  'ADVERTISEMENT' => $adscript)
);

$template->pparse('footer');

$template->destroy;
?>