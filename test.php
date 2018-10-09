<?php
//LIBREPOST 1.0.0-1 UNIT TESTS

include("librepost-1.0.0-1.php");
echo librepost_display_plist("localhost","penguin","hy890765");

echo librepost_version();

echo "<br>";

librepost_add_user("librepost_test","test");

echo "All success.";
?>
