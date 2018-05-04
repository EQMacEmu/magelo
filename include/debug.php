<?php

function debug($data){
    print "<pre style='color:black;display:inline-block;'>";
    print "Admin Debug Panel:\r\n";
    print print_r($data);
    print "</pre><br>";
}
function admindebug($content) {
    if (isadmin()) {
        debug($content);
    }
}
function isadmin() {
    global $adminIP;
    if ($_SERVER['REMOTE_ADDR'] == $adminIP) {
        return true;
    }
    return false;
}