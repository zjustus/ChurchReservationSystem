<?php

function db_connect(){
    $config = parse_ini_file('config.ini', true);
    $mysqli = new mysqli($config['database']['server'],$config['database']['username'],$config['database']['password'],$config['database']['dbname']);

    if (mysqli_connect_errno()) {
        //printf("Connect failed: %s\n", mysqli_connect_error());
        return false;
    }
    return $mysqli;
}

?>
