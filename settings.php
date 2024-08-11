<?php
// account datails
$host = "feenix-mariadb.swin.edu.au";
$user = "s103426874";
$pswd = "221102";
$dbname = "s103426874_db";

// table name
$table1 = "friends";
$table2 = "myfriends";


// connection
$conn = @mysqli_connect($host, $user, $pswd, $dbname) or die("Connection failed: " . mysqli_connect_error());
