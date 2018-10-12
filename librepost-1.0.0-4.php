<?php
include('librepost-dep1.php');
session_start();
$map_count = 0;
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
	 $result1 = mysqli_stmt_execute($stmt1);
	 if(!$result1)
		die("Librepost: query error<br>");
	 $true_result = mysqli_stmt_get_result($stmt1);
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
	 
	 $stmt = mysqli_prepare($GLOBALS['conn'],"SELECT * FROM users WHERE UNAME = ?;");
	 if(!$stmt)
	 die(mysqli_error($GLOBALS['conn']));
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
	      $_SESSION["librepost_login_uid"] = $row["ID"];
	      header("Location: " . $redirect);
	   } else {
	   return "Password incorrect.";
	   }
	   }
	 }
	
	 
}
function librepost_get_login_name() {
	 if(!isset($_SESSION["librepost_login"]))
	 return false;

	 return $_SESSION["librepost_login_uname"];
}
function librepost_check_login() {
	 if($_SESSION["librepost_login"] == 1 && isset($_SESSION["librepost_login_uname"])) {
	 return true;
	 }
	 return false;
}
function librepost_add_post($title,$description,$address,$redirect) {
if(empty($title) || empty($description) || empty($address)){
		 echo "Incomplete fields.";
		 return -1;
}

	 $encoded_address = urlencode($address);
	 $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $encoded_address ."&key=". $GLOBALS['gmaps_api_key'];
	// echo $url . "<br>";
	 $json = file_get_contents($url);
	 $response = json_decode($json,true);
	 if($response['status'] != 'OK')
	 die("Librepost: maps request error : " . $response['status']);

	 $lati = isset($response['results'][0]['geometry']['location']['lat']) ? $response['results'][0]['geometry']['location']['lat'] : "";
        $longi = isset($response['results'][0]['geometry']['location']['lng']) ? $response['results'][0]['geometry']['location']['lng'] : "";
	$formatted_address = isset($response['results'][0]['formatted_address']) ? $response['results'][0]['formatted_address'] : "";
         
//	echo $lati . ", " . $longi;
	$stmt = mysqli_prepare($GLOBALS['conn'],"INSERT INTO POSTS(TITLE,DESCRIPTION,LAT,LNG,ADDRESS) VALUES (?,?,?,?,?,?);");
	mysqli_stmt_bind_param($stmt,"ssssss",$title,$description,$lati,$longi,$formatted_address,$_SESSION["librepost_login_uid"]);
	if(!mysqli_stmt_execute($stmt))
	die("Librepost: query error<br>");
header("location: " . $redirect);

}
function librepost_get_map($center_location,$width,$height,$postpage,$display_all = true,$number = null) {
	 if ($display_all && $number != null) {
	    die("Librepost: illegal operation<br>");
	 }
	 if ($display_all) {
	 $result = mysqli_query($GLOBALS['conn'],"SELECT * FROM POSTS;");
	 ?>
	 <div id="librepost_map<?php echo $GLOBALS['map_count'];  ?>" style="width: <?php echo $width; ?>; height: <?php echo $height; ?>;"></div>
	 <script>

	
	function initMap() {
        
        var map = new google.maps.Map(document.getElementById('librepost_map<?php echo $GLOBALS['map_count']; ?>'), {
          center: {lat: 0, lng: 0},
          zoom: 15
        });

	var geocoder = new google.maps.Geocoder();
	var address = '<?php echo $center_location; ?>';
        geocoder.geocode({'address': address}, function(results, status) {
          if (status === 'OK') {
            map.setCenter(results[0].geometry.location);
            
          } else {
            alert('Geocode was not successful for the following reason: ' + status);
          }
        });

		<?php
		
			echo "var arr = [";
			$b = "";
			while($arr = mysqli_fetch_assoc($result)){
				if ($arr["LAT"] == "") continue;
		$b .= "[" . $arr["ID"] . "," . "\"" . $arr["LAT"] . "\",". $arr["LNG"].",'". $arr["TITLE"] . "'],";
	}
			//at end:
			
			$b = substr($b,0,-1);
			echo $b;
			echo "];";
		?>
		for (i = 0;i<arr.length;i++) {
			var marker = new google.maps.Marker({
				position: new google.maps.LatLng(arr[i][1], arr[i][2]),
				map: map
			
			});
			//marker.content = "<?php echo $arr["TITLE"]; ?>";
			  var infowindow = new google.maps.InfoWindow()
var content="<b>" + arr[i][3] + "</b><br /><a href=\"<?php echo $postpage; ?>" + arr[i][0] + "\">More details</a>";
google.maps.event.addListener(marker,'click', (function(marker,content,infowindow){ 
        return function() {
           infowindow.setContent(content);
           infowindow.open(map,marker);
        };
    })(marker,content,infowindow)); 

			
		}
      }
	  
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $GLOBALS['gmaps_api_key']; ?>&callback=initMap"
    async defer></script>
	 <?php

	 }
$GLOBALS['map_count']++;
}

function librepost_version() {
	 return "Librepost 1.0.0-4 by Heng Ye";
}
?>
