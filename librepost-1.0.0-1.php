<?php
//LIBREPOST - By Heng Ye GPLv3
include('librepost-dep1.php');

$conn = mysqli_connect($db_hostn,$db_uname,$db_passw,$db_dname);
if (!$conn)
	 die ("Librepost: could not connect to db server <br>");


function librepost_display_plist($postpg = "postview?row=") {

	 $returnval = "";
	 
	 
	 $result=mysqli_query($GLOBALS['conn'],"SELECT * FROM POSTS;");
	 if (!$result)
	 die ("Librepost: query error");

	 if (mysqli_num_rows($result) === 0)
	 $returnval .= "<em>No posts yet.</em>";
	 
	 while ($row = mysqli_fetch_assoc($result)) {
	 	 $returnval .= '<a href="' . $postpg . $row["ID"] . '">' . $row["TITLE"] . '</a><br>';
	 }
}

function librepost_add_user($username,$password) {
	 $hash = password_hash($password,PASSWORD_DEFAULT);
	 $stmt = mysqli_prepare($GLOBALS['conn'],"INSERT INTO users (UNAME,PASSW) VALUES(?,?);");
	 mysqli_stmt_bind_param($stmt,"ss",$username,$hash);
	 if(!mysqli_stmt_execute($stmt)) {
	 		die("Librepost: query error <br>");
	 }
}


function librepost_version() {
	 return "Librepost 1.0.0-1 by Heng Ye";
}
?>
