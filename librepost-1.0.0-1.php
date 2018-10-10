<?php
include('librepost-dep1.php');
session_start();
$conn = mysqli_connect($db_hostn,$db_uname,$db_passw,$db_dname);
if (!$conn)
	 die ("Librepost: could not connect to db server <br>");

function librepost_setup_db() {
	 $sql = "CREATE TABLE `users` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `UNAME` text NOT NULL, `PASSW` text NOT NULL, PRIMARY KEY (`ID`))";
	 $result=mysqli_query($GLOBALS['conn'],$sql);
	 if (!$result)
	 die ("setup error");
	  $sqln = "CREATE TABLE `POSTS` ( `ID` int(11) NOT NULL AUTO_INCREMENT, `TITLE` text NOT NULL, `DESCRIPTION` text NOT NULL,`POSTERID` int(11) NOT NULL, PRIMARY KEY (`ID`))";
	 $result2=mysqli_query($GLOBALS['conn'],$sqln);
	 if (!$result2)
	 die ("setup error");
	 
}
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
	 

	
	 $stmt1 = mysqli_prepare($GLOBALS['conn'],"SELECT * FROM users WHERE UNAME = ?;");
	 mysqli_stmt_bind_param($stmt1,"s",$username);
	 $result1 = mysqli_stmt_execute($stmt);
	 if(!$result1)
		die("Librepost: query error<br>");
	 $true_result = mysqli_stmt_get_result($stmt);
	 if(mysqli_num_rows($true_result) >= 1)
	 {
	 echo "Username already taken.";
	 return;
	 }
	 if(strlen($password) > 56 || strlen($password) < 5) {
	 echo "Password should be longer than 5 characters and shorter than 56 characters.";
	 return;
	 }
	 $hash = password_hash($password,PASSWORD_DEFAULT);
	 $stmt = mysqli_prepare($GLOBALS['conn'],"INSERT INTO users (UNAME,PASSW) VALUES(?,?);");
	 mysqli_stmt_bind_param($stmt,"ss",$username,$hash);
	 if(!mysqli_stmt_execute($stmt)) {
	 		die("Librepost: query error <br>");
	 }
}
function librepost_test_login($username,$password,$redirect) {
	 $hash = password_hash($password,PASSWORD_DEFAULT);
	 $stmt = mysqli_prepare($GLOBALS['conn'],"SELECT * FROM USERS WHERE UNAME = ?;");
	 mysqli_stmt_bind_param($stmt,"s",$username);
	 $result = mysqli_stmt_execute($stmt);
	 if(!$result)
		die("Librepost: query error<br>");
	 $true_result = mysqli_stmt_get_result($stmt);
	 if (mysqli_num_rows($true_result) < 1) {
	 return "Unknown user.";
	 } elseif (mysqli_num_rows($true_result) > 1) {
	 return "Cracked.";
	 } else {
	   while($row = mysqli_fetch_assoc($true_result)) {
	   $checked = password_verify($password,$row["PASSW"]);
	   if ($checked === true) {
	      session_regenerate_id();
	      $_SESSION["librepost_login"] = 1;
	      $_SESSION["librepost_login_uname"] = $username;
	      header("Location: " . $redirect);
	   } else {
	   return "Password incorrect.";
	   }
	   }
	 }
	
	 
}
function librepost_check_login() {
	 if($_SESSION["librepost_login"] == 1 && isset($_SESSION["librepost_login_uname"]))) {
	 return true;
	 }
	 return false;
}
function librepost_get_map($display_all = true,$number = null) {
	 if ($display_all && $number != null) {
	    die("Librepost: illegal operation<br>");
	 }
	 if ($display_all) {
	 ?>
	 
	 <?php
	 }
}
function librepost_add_post($title,$desc,$address
function librepost_version() {
	 return "Librepost 1.0.0-1 by Heng Ye";
}
?>
