<?php
session_start();

require_once dirname(__FILE__).'/includes/connections.php';
require_once dirname(__FILE__).'/includes/brain_file.php';
$playerid = abs((int)$_SESSION['playerid']);

if(!isset($_GET['action'])) exit("<span style='color:#F00'>Invalid action request [Ref: 01x".__LINE__."]</span>");
switch($_GET['action']) {
	case 'update':		updateBox();	break;
	case 'sendMessage':	sendMessage();	break;
	default: exit("<span style='color:#F00'>Invalid action request [Ref: 01x".__LINE__."]</span>"); break;
}

function updateBox() {
	global $playerid;
	if(!isset($_GET['id']) || !$_GET['id']=abs((int)$_GET['id'])) exit("<span style='color:#F00'>Invalid chatbox id sent [Ref: 01x".__LINE__."]</span>");
	$pl = mysql_fetch_assoc(mysql_query("SELECT `my_faction`,`am_i_staff` FROM `members` WHERE `playerid`='".$playerid."'"));
	$getBox = mysql_query("SELECT NULL FROM `chatBoxes` WHERE `cbox_id`='".$_GET['id']."' && (`cbox_type`='1' ".($pl['am_i_staff']>1 ? "|| `cbox_type`='4' " : "")."|| (`cbox_type`='2' && `cbox_gang`='".$pl['my_faction']."') || `cbox_players` LIKE '%[".$playerid."]%')");
	if(!mysql_num_rows($getBox)) exit("<span style='color:#F00'>Invalid chatbox requested [Ref: 01x".__LINE__."]</span>");
	$getPosts = mysql_query("SELECT * FROM `chatPosts` WHERE `cpost_box`='".$_GET['id']."' ORDER BY `cpost_id` DESC LIMIT 30");
	$posts = array();
		while($post=mysql_fetch_array($getPosts)) {
		$posts[] = $post;
	}
	$posts = array_reverse($posts);
	foreach($posts as $post) {
		$ot = mysql_fetch_assoc(mysql_query("SELECT `am_i_staff` FROM `members` WHERE `playerid`='".$post['cpost_player']."'"));
		$emoticons = array(
                       ':)' => "<img src='images/smilies/smile.gif'>",
			':P' => "<img src='images/smilies/tounge.gif'>",
			':(' => "<img src='images/smilies/sad.gif'>",
			':o' => "<img src='images/smilies/shocked.gif'>",
			':@' => "<img src='images/smilies/angry.gif'>",
			'o_O' => "<img src='images/smilies/sarcy.gif'>",
			':s' => "<img src='images/smilies/confused.gif'>",
			';)' => "<img src='images/smilies/wink.gif'>",
			':*' => "<img src='images/smilies/cool.gif'>",
			':|o' => "<img src='images/smilies/psyc.gif'>",
			':|' => "<img src='images/smilies/dissapointed.gif'>",
			':D' => "<img src='images/smilies/grin.gif'>",
			'xD' => "<img src='images/smilies/histericle.gif'>",
			':L' => "<img src='images/smilies/laughing.gif'>",
			':D' => "<img src='images/smilies/grin.gif'>",
		);
		$post['cpost_text'] = str_ireplace(array_keys($emoticons),array_values($emoticons),$post['cpost_text']);
		
		echo "<div class='vnm-chatPost' chatpost='".$post['cpost_id']."'".($ot['am_i_staff']>7 ? " style='color:#AA0000'" : "000").">".playerName($post['cpost_player']).": ".stripslashes($post['cpost_text'])." <br/><span><font color='#666666'>Last Message Sent: ".date('g:i:s a',$post['cpost_time'])."</font></span></div>";
	
	}
	}


function sendMessage() {
	global $playerid;
	if(!isset($_POST['msg']) || !$_POST['msg']=mysql_real_escape_string(htmlentities(urldecode($_POST['msg'])))) exit('Message was too short');
	$_GET['id'] = abs((int)$_GET['id']);
	if(strlen($_POST['msg'])<2) exit('Message was too short');
	$pl = mysql_fetch_assoc(mysql_query("SELECT `my_faction`,`chatban`,`am_i_staff` FROM `members` WHERE `playerid`='".$playerid."'")) or exit(mysql_error());
	if($pl['chatban']) exit("You're chat banned for ".$pl['chatban']." Days!");
	$getBox = mysql_query("SELECT NULL FROM `chatBoxes` WHERE `cbox_id`='".$_GET['id']."' && (`cbox_type`='1' ".($pl['am_i_staff']>1 ? "|| `cbox_type`='4' " : "")."|| (`cbox_type`='2' && `cbox_gang`='".$pl['my_faction']."') || `cbox_players` LIKE '%[".$playerid."]%')");
	if(!mysql_num_rows($getBox)) exit('Invalid chat box');
	mysql_query("INSERT INTO `chatPosts` (`cpost_player`,`cpost_text`,`cpost_box`,`cpost_time`) VALUES('".$playerid."','".$_POST['msg']."','".$_GET['id']."','".time()."')");
}

function playerName($id) {
	$getName = mysql_query("SELECT `playername`,`am_i_staff`,`my_dondays` FROM `members` WHERE `playerid`='".abs((int)$id)."'");
	if(!mysql_num_rows($getName)) return "System";
	else {
		$r = mysql_fetch_assoc($getName);
return "<a href='/profile.php?XID=".$id."' style='color:#".($r['am_i_staff']>3 ? 'AA0000' : ($r['am_i_staff']>1 ? 'fb00ff'  : '000'))."'><b>".$r['playername']."</b></a>";
	}

}

?>