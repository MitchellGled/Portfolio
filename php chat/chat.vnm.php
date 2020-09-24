<?php
$playerid = $_SESSION['playerid'];

$totalBoxes = 0;
$codes = array(':)',':P',':(',':o',':@','o_O',':s',';)',':*',':|o',':|',':D','xD',':L','xP');
   $images  = array(
   ' [img]images/smilies/smile.gif[/img] ',
   ' [img]images/smilies/tounge.gif[/img] ',
   ' [img]images/smilies/sad.gif[/img] ',
   ' [img]images/smilies/shocked.gif[/img] ',
   ' [img]images/smilies/angry.gif[/img] ',
   ' [img]images/smilies/sarcy.gif[/img] ',
   ' [img]images/smilies/confused.gif[/img] ',
   ' [img]images/smilies/wink.gif[/img] ',
   ' [img]images/smilies/cool.gif[/img] ',
   ' [img]images/smilies/psyc.gif[/img] ',
   ' [img]images/smilies/dissapointed.gif[/img] ',
   ' [img]images/smilies/grin.gif[/img] ',
   ' [img]images/smilies/histericle.gif[/img] ',
   ' [img]images/smilies/laughing.gif[/img] ',
   ' [img]images/smilies/histericletounge.gif[/img] ');
$getBoxes = mysql_query("SELECT `cbox_id`,`cbox_name`,`cbox_players`,`cbox_type` FROM `chatBoxes` WHERE `cbox_type`='1' ".($pl['am_i_staff']>1 ? "|| `cbox_type`='4' " : "").($pl['my_faction'] ? "|| `cbox_gang`='".$pl['my_faction']."' " : "")."|| `cbox_players` LIKE '%[".$playerid."]%'");
while($cBox=mysql_fetch_assoc($getBoxes)) {
	$otherPlayer = explode('['.$playerid.']',$cBox['cbox_players']);
	$otherPlayer = ($otherPlayer[1] ? $otherPlayer[1] : $otherPlayer[0]);
	$otherPlayer = preg_replace('/[^0-9]/','',$otherPlayer);
	if($otherPlayer) $otherName = mysql_result(mysql_query("SELECT `playername` FROM `members` WHERE `playerid`='".$otherPlayer."'"),0,0);
	echo "
	<div class='vnm-chatBox' id='vnm-chatBox".$cBox['cbox_id']."' chatbox='".$cBox['cbox_id']."' style='right:".(($totalBoxes*125)+25)."px'>
		<div class='vnm-chatBar".(isset($_COOKIE['chatBox'.$cBox['cbox_id']]) && $_COOKIE['chatBox'.$cBox['cbox_id']]=='open' ? " vnm-chatBoxMove" : "")."'>".($cBox['cbox_type']==3 ? $otherName : stripslashes($cBox['cbox_name']))."</div>
		<div class='vnm-chatContent' id='vnm-chatContent".$cBox['cbox_id']."' align='left'></div>
		<input type='text' class='vnm-chatInput' id='vnm-chatInput".$cBox['cbox_id']."' style='width:100%' />
	</div>";
	$totalBoxes++;
}
?>
<style type="text/css">
.vnm-chatBox { background-color: #FFF; position: fixed; bottom: -320px; height: 346px; width: 115px; z-index: 1000; border-top-left-radius: 10px; border-top-right-radius: 10px; }
.vnm-chatBar { text-align:left; border-right: 1px solid #000; border-left: 1px solid #000; border-top-left-radius: 10px; border-top-right-radius: 10px; font-weight: bold; background-color: #333; font-size: 14px; color: #FFF; text-shadow: 1px 1px 2px #000; padding: 5px; }
.vnm-chatBar:hover { cursor: pointer; }
.vnm-chatContent { margin: 5px !important; margin-top: 3px !important; width: 230px; height: 286px; overflow-y: scroll; overflow-x: hidden; word-break: loose; word-wrap: normal; }
.vnm-chatInput { background-color: #FFF; border-radius: 0px !important; width: 227px !important; margin: 0px 5px 0px 5px !important; }
.vnm-chatPost { padding: 1px 5px 1px 0px; }
.vnm-chatPost:first-child { padding-top: 0px; }
.vnm-chatEmoticon { display: inline-block; width: 12px; height: 12px; background-image: url('/images/chat.vnm.png'); background-repeat: no-repeat; }
</style>
<script type="text/javascript">
var chatBox = Array();
$(document).ready(function(e) {
	$('.vnm-chatBoxMove').each(function() {
		var currentRight = Number($(this).parents('.vnm-chatBox').css('right').replace(/\D/g,''));
		var myParent = $(this).parents('.vnm-chatBox');
		thisChatId = $(this).parents('.vnm-chatBox').attr('chatbox');
		myParent.css({bottom:'0px',width:'240px'});
		$(this).addClass('vnm-chatBarOpen');
		setCookie('chatBox'+thisChatId,'open','31');
		$('.vnm-chatBox').each(function() {
			var thisRight = Number($(this).css('right').replace(/\D/g,''));
			if(thisRight>currentRight) $(this).css({right:(thisRight+125)+'px'});
		});
		chatBox[thisChatId] = setTimeout("updateChat("+thisChatId+",true)",250);
	});
});
$('.vnm-chatBar').click(function() {
	var currentRight = Number($(this).parents('.vnm-chatBox').css('right').replace(/\D/g,''));
	var myParent = $(this).parents('.vnm-chatBox');
	thisChatId = $(this).parents('.vnm-chatBox').attr('chatbox');
	if($(this).hasClass('vnm-chatBarOpen')) {
		myParent.css({bottom:'-320px',width:'115px'});
		$(this).removeClass('vnm-chatBarOpen');
		setCookie('chatBox'+thisChatId,'null','1');
		$('.vnm-chatBox').each(function() {
			var thisRight = Number($(this).css('right').replace(/\D/g,''));
			if(thisRight>currentRight) $(this).css({right:(thisRight-125)+'px'});
		});
		if(chatBox[thisChatId]) clearTimeout(thisChatId);
	} else {
		myParent.css({bottom:'0px',width:'240px'});
		$(this).addClass('vnm-chatBarOpen');
		setCookie('chatBox'+thisChatId,'open','31');
		$('.vnm-chatBox').each(function() {
			var thisRight = Number($(this).css('right').replace(/\D/g,''));
			if(thisRight>currentRight) $(this).css({right:(thisRight+125)+'px'});
		});
		chatBox[thisChatId] = setTimeout("updateChat("+thisChatId+",true)",250);
	}
});
function updateChat(boxId,firstRefresh) {
	$.ajax({
		type: "POST",
		url: '/chat.ajax.vnm.php?action=update&id='+Number(boxId),
		data: '',
		success: function(chatResult) {
			$('#vnm-chatContent'+boxId).html(chatResult);
			if(firstRefresh==true) { $('#vnm-chatContent'+boxId).scrollTop(10000); }
			setTimeout("updateChat("+boxId+")",1000);
		}
	});
}
function sendMessage(boxId,string) {
	$.ajax({
		type: "POST",
		url: '/chat.ajax.vnm.php?action=sendMessage&id='+boxId,
		data: 'msg='+escape(string),
		success: function(resultCode) {
			if(resultCode) alert(resultCode);
		}
	});
}
$('.vnm-chatInput').keypress(function(e) {
	if(e.keyCode==13) {
		sendMessage($(this).parent('.vnm-chatBox').attr('chatbox'),$(this).val());
		$(this).val('');
	}
});
function setCookie(cookieName,cookieValue,cookieExpire) {
	var expireDate = new Date();
	expireDate.setDate(expireDate.getDate()+cookieExpire);
	var cookieValue=escape(cookieValue)+((cookieExpire==null) ? "" : "; expires="+expireDate.toUTCString());
	document.cookie=cookieName+"="+cookieValue;
}
</script>