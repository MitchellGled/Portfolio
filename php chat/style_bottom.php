<br/><br/><br/><br/><br/>
<html>
<body>
<?php
if(!isset($set)) {
	$q_ry = array();
	$q_ry = "SELECT * FROM `settings`";
        $settg = array();
    $settg = mysql_query($q_ry);
    $r = array();
	
	while ( $r = mysql_fetch_array($settg)) {
		$set[$r['conf_name']]=$r['conf_value'];
	}	
}
 include ('./includes/connections.php');
require_once ('./includes/brain_file.php');

$userid = $_SESSION['playerid'];

if($pl['chatOn']==1) { require_once '/home/ccm/public_html/includes/chat.vnm.php'; }
?>

  <div class = 'bgwrap' id = "bgw"></div>





  </body>
  </html>
  