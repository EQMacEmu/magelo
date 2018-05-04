<?php
function fetchRows($result) {
    $res = array();
    if ( $result ) {
        while ( $record = mysqli_fetch_assoc($result) ){
            $res[] = $record;
        }
    }
    return $res;
}
function numRows($result) {
    return mysqli_num_rows($result);
}
function sanitize($mydb, $val) {
    $val = mysqli_real_escape_string($mydb, trim($val));
    return $val;
}