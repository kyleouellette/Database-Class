<?php
require('constants.php');
require('class.database.php');
$db = new Database();
$all = $db->get('users');
echo "<pre>";
print_r(all);
echo "</pre>";
