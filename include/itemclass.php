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
 *   March 14, 2011 - Updated for Bank slots 17-24 
 *   February 5, 2014 - Updated for Powersource (Maudigan c/o Natedog)
 *   February 9, 2014 - Fixed charm item type getting set wrong (Maudigan)
 ***************************************************************************/ 
  
  
  
  
 if ( !defined('INCHARBROWSER') ) 
{ 
        die("Hacking attempt"); 
} 
Include_once("include/item.php"); 
// holds all the items 
class item { 
  
        // Variables 
        var $mytype; 
        //1: Equipment 
        //2: the 8 inventory slots 
        //3: the 16 bank slots 
        //other: bag contents, i.e. 22 would be an item in bag 22 
  
        var $myicon; 
        //item icon for this item 
  
        var $myslot; 
        //actual slot in the inventory table 
        
        var $myid; 
        //id of the item for linking 
  
        var $myhtml; 
        //html text for displaying the item 
  
        var $myname; 
        //name to use for display later 
  
        var $myslotcount; 
        //0 for items, 1-10 for bags 
  
        var $myvslot; 
        //if it goes in a bag, this is 1-10 for which bag slot 
        
        var $myaugshtml = array(); 
        var $myaugsname = array(); 
        var $myaugsid = array(); 
        var $myaugcount = 0; 
        //augment arrays 


         public function addaug($row){ 
           $this->myaugshtml[] = GetItem($row); 
           $this->myaugsname[] = $row['Name']; 
           $this->myaugsid[] = $row['id']; 
           $this->myaugcount++;          
         } 

         public function item($row) { 
           $this->myslot = $row['myslot']; 
           $this->myhtml = GetItem($row); 
           $this->myslotcount = $row['bagslots']; 
           $this->myicon=$row['icon']; 
           $this->myname=$row['Name']; 
           $this->myid=$row['id']; 
           switch (true){
		// Equipment Slots
                case ($this->myslot >= 0 && $this->myslot <= 21 || $this->myslot == 9999):
                     $this->mytype = 1; 
                     $this->myvslot = $this->myslot; 
                     break; 
		// Inventory Slots
                case ($this->myslot >= 22 && $this->myslot <= 29): 
                     $this->mytype = 2; 
                     $this->myvslot = $this->myslot; 
                     break; 
		// Inventory Bag Slot
                case ($this->myslot > 249 && $this->myslot < 330): 
		     $base = intval($this->myslot / 10);
                     $this->mytype = $base - 3; // Which Bag?
                     $this->myvslot = $this->myslot - ($base * 10 - 1); // Which Bag Slot?
                     break; 
		// Bank Slots
                case ($this->myslot >= 2000 && $this->myslot <= 2023): 
                     $this->mytype = 3; 
                     $this->myvslot = $this->myslot; 
                     break; 
		// Bank Bag Slot
                case (true && $this->myslot > 2029 && $this->myslot < 2270): 
		     $base = intval(($this->myslot - 2030) / 10);
                     $this->mytype = 2000 + $base; // Which Bag?
                     $this->myvslot = $this->myslot - ($base * 10 + 2030 - 1); // Which Bag Slot?
                     break; 
                default: 
                     $this->mytype = 0; 
                     $this->myvslot = 0; 
                     break; 
           } 
         } 

        
        function aughtml($key) { 
          return $this->myaugshtml[$key]; 
        } 
        
        function augname($key) { 
          return $this->myaugsname[$key]; 
        } 
        
        function augid($key) { 
          return $this->myaugsid[$key]; 
        } 
        
        function augcount() { 
          return $this->myaugcount; 
        } 
        
        function icon() { 
         return $this->myicon; 
        } 
        
        function id() { 
         return $this->myid; 
        } 
  
        function slot() { 
         return $this->myslot; 
        } 
         
        function html() { 
         return $this->myhtml; 
        } 
  
        function name() { 
         return $this->myname; 
        } 
  
        function slotcount() { 
         return $this->myslotcount; 
        } 
  
        function type() { 
         return $this->mytype; 
        } 
  
        function vslot() { 
         return $this->myvslot; 
        } 
  
        function setvslot($setval) { 
         $this->myvslot = $setval; 
        } 
  
        function seticon($setval) { 
         $this->myicon = $setval; 
        } 
  
        function setslot($setval) { 
         $this->myslot = $setval; 
        } 
  
        //pass item id, pulls html from getitem function 
        function sethtml($setval) { 
         $this->myhtml = GetItem($setval); 
        } 
  
        function setname($setval) { 
         $this->myname = $setval; 
        } 
  
        function setslotcount($setval) { 
         $this->myslotcount = $setval; 
        } 

        function settype($setval) { 
         $this->mytype = $setval; 
        } 
  
        function setid($setval) { 
         $this->myid = $setval; 
        } 
  
} 

