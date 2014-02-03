<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$prodid==''){
	if(trim(@$explicitid)!='') $prodid=trim($explicitid); else $prodid=trim(@$_REQUEST['prod']);
}
if($prodid!=$giftcertificateid && $prodid!=$donationid) $prodid=$giftcertificateid;
$WSP = '';
$OWSP = '';
$TWSP = 'pPrice';
$iNumOfPages = 0;
if(@$dateadjust=='') $dateadjust=0;
$thesessionid=getsessionid();
$alreadygotadmin = getadminsettings();
get_wholesaleprice_sql();
if(@$_SESSION["clientLoginLevel"] != "") $minloglevel=$_SESSION["clientLoginLevel"]; else $minloglevel=0;
$validitem=TRUE;
if(@$_POST['posted']=='1'){
	$validitem = (is_numeric(@$_POST['amount']) && trim(@$_POST['amount'])!='');
	if($validitem) $validitem = (double)@$_POST['amount']>0;
	if($validitem){
		$prodname = ($prodid==$giftcertificateid?$xxGifCtc:$xxDonat);
		$sSQL = 'SELECT '.getlangid('pName',1)." FROM products WHERE pID='" . escape_string($prodid) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)) $prodname = $rs[getlangid('pName',1)];
		mysql_free_result($result);
		$sSQL = 'INSERT INTO cart (cartSessionID,cartClientID,cartProdID,cartQuantity,cartCompleted,cartProdName,cartProdPrice,cartOrderID,cartDateAdded) VALUES (';
		$sSQL .= "'" . escape_string($thesessionid) . "','" . (@$_SESSION['clientID'] != '' ? escape_string($_SESSION['clientID']) : 0) . "','" . escape_string($prodid) . "',";
		$sSQL .= "1,0,'" . escape_string($prodname) . "','" . escape_string(is_numeric(@$_POST['amount']) ? @$_POST['amount'] : 10) . "',0,";
		$sSQL .= "'" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "')";
		mysql_query($sSQL) or print(mysql_error());
		$cartid = mysql_insert_id();
		if($prodid==$giftcertificateid){
			// Create GC id
			$gotunique=FALSE;
			srand((double)microtime()*1000000);
			while(! $gotunique){
				$sequence = getgcchar() . getgcchar() . rand(100000000, 999999999) . getgcchar();
				$sSQL = "SELECT gcID FROM giftcertificate WHERE gcID='" . $sequence . "'";
				$result = mysql_query($sSQL) or print(mysql_error());
				if(mysql_num_rows($result)==0) $gotunique = TRUE;
				mysql_free_result($result);
			}
			$sSQL = 'INSERT INTO giftcertificate (gcID,gcTo,gcFrom,gcEmail,gcOrigAmount,gcRemaining,gcDateCreated,gcCartID,gcAuthorized,gcMessage) VALUES (';
			$sSQL .= "'" . $sequence . "',";
			$sSQL .= "'" . escape_string(@$_POST['toname']) . "',";
			$sSQL .= "'" . escape_string(@$_POST['fromname']) . "',";
			$sSQL .= "'" . escape_string(@$_POST['toemail']) . "',";
			$sSQL .= "0,0,";
			$sSQL .= "'" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "',";
			$sSQL .= $cartid . ",0,";
			$sSQL .= "'" . escape_string(str_replace(array("\r\n","\n"),'<br />',@$_POST['gcmessage'])) . "')";
			mysql_query($sSQL) or print(mysql_error());
		}else{
			if(trim(@$_POST['fromname'])!=''){
				$sSQL = "INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff) VALUES (".$cartid.",0,'".escape_string($xxFrom) . "','".escape_string(substr(@$_POST['fromname'],0,255))."',0,0)";
				mysql_query($sSQL) or print(mysql_error());
			}
			if(trim(@$_POST['gcmessage'])!=''){
				$sSQL = "INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff) VALUES (".$cartid.",0,'".escape_string($xxMessag) . "','".escape_string(substr(@$_POST['gcmessage'],0,255))."',0,0)";
				mysql_query($sSQL) or print(mysql_error());
			}
		}
		if(ob_get_length()!==FALSE)
			header('Location: ' . $storeurl . 'cart.php');
		else
			print '<meta http-equiv="Refresh" content="0; URL=cart.php">';
	}
}
if(@$_POST['posted']!='1' || !$validitem){
	if($prodid==$giftcertificateid){
		if(@$giftcertificateminimum=='') $giftcertificateminimum=5;
?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function checkastring(thestr,validchars){
  for (i=0; i < thestr.length; i++){
    ch = thestr.charAt(i);
    for (j = 0;  j < validchars.length;  j++)
      if (ch == validchars.charAt(j))
        break;
    if (j == validchars.length)
	  return(false);
  }
  return(true);
}
function formvalidator0(frm){
if(frm.amount.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxAmount?>\".");
	frm.amount.focus();
	return(false);
}
if (!checkastring(frm.amount.value,"0123456789.,")){
	alert("<?php print $xxOnlyDec?> \"<?php print $xxAmount?>\".");
	frm.amount.focus();
	return(false);
}
if(frm.amount.value<<?php print $giftcertificateminimum?>){
	alert("<?php print $xxGCMini . ' ' . FormatEuroCurrency($giftcertificateminimum)?>.");
	frm.amount.focus();
	return(false);
}
if(frm.toname.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxTo?>\".");
	frm.toname.focus();
	return(false);
}
if(frm.fromname.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxFrom?>\".");
	frm.fromname.focus();
	return(false);
}

if(frm.toemail.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxReEmai?>\".");
	frm.toemail.focus();
	return(false);
}
var regex = /[^@]+@[^@]+\.[a-z]{2,}$/i;
if(!regex.test(frm.toemail.value)){
	alert("<?php print $xxValEm?>");
	frm.toemail.focus();
	return(false);
}
if(frm.toemail2.value!=frm.toemail.value){
	alert("<?php print $xxEmCNMa?>.");
	frm.toemail2.focus();
	return(false);
}
return (true);
}
/* ]]> */
</script>
	<form method="post" action="<?php print htmlentities(@$_SERVER['PHP_SELF'])?>" onsubmit="return formvalidator0(this)">
	<input type="hidden" name="posted" value="1" />
	<input type="hidden" name="prod" value="<?php print $giftcertificateid?>" />
      <table border="0" cellspacing="1" cellpadding="1" width="98%" align="center">
        <tr> 
          <td align="center" colspan="2"><strong><?php print $xxGCPurc?></strong><br />&nbsp;</td>
        </tr>
<?php	if(@$_POST['posted']=='1'){ ?>
        <tr>
          <td align="center" colspan="2"><span style="color:#FF0000;font-weight:bold"><?php print $xxAmtNov?></span><br />&nbsp;</td>
        </tr>
<?php	} ?>
		<tr> 
          <td align="right"><strong><label for="amount"><?php print $xxAmount?></label>:</strong></td>
		  <td align="left"><input type="text" name="amount" id="amount" size="4" maxlength="10" value="<?php print htmlspecials(@$_POST['amount'])?>" /></td>
        </tr>
		<tr> 
          <td align="right"><strong><label for="toname"><?php print $xxTo?></label>:</strong></td>
		  <td align="left"><input type="text" name="toname" id="toname" size="25" maxlength="50" value="<?php print htmlspecials(@$_POST['toname'])?>" /></td>
        </tr>
		<tr> 
          <td align="right"><strong><label for="fromname"><?php print $xxFrom?></label>:</strong></td>
		  <td align="left"><input type="text" name="fromname" id="fromname" size="25" maxlength="50" value="<?php print htmlspecials(@$_POST['fromname'])?>" /></td>
        </tr>
		<tr> 
          <td align="right"><strong><label for="toemail"><?php print $xxReEmai?></label>:</strong></td>
		  <td align="left"><input type="text" name="toemail" id="toemail" size="25" maxlength="50" value="<?php print htmlspecials(@$_POST['toemail'])?>" /></td>
        </tr>
		<tr> 
          <td align="right"><strong><label for="toemail2"><?php print $xxCReEma?></label>:</strong></td>
		  <td align="left"><input type="text" name="toemail2" id="toemail2" size="25" maxlength="50" value="<?php print htmlspecials(@$_POST['toemail2'])?>" /></td>
        </tr>
		<tr> 
          <td align="right"><strong><label for="gcmessage"><?php print $xxMessag?></label>:</strong></td>
		  <td align="left"><textarea name="gcmessage" id="gcmessage" cols="35" rows="4"><?php print htmlspecials(@$_POST['gcmessage'])?></textarea></td>
        </tr>
		<tr> 
          <td colspan="2" align="center"><input type="submit" value="<?php print $xxSubmt?>" /></td>
        </tr>
      </table>
	</form>
<?php
	}else{ ?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function checkastring(thestr,validchars){
  for (i=0; i < thestr.length; i++){
    ch = thestr.charAt(i);
    for (j = 0;  j < validchars.length;  j++)
      if (ch == validchars.charAt(j))
        break;
    if (j == validchars.length)
	  return(false);
  }
  return(true);
}
function formvalidator0(frm){
if(frm.amount.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxAmount?>\".");
	frm.amount.focus();
	return(false);
}
if (!checkastring(frm.amount.value,"0123456789.,")){
	alert("<?php print $xxOnlyDec?> \"<?php print $xxAmount?>\".");
	frm.amount.focus();
	return(false);
}
if(frm.gcmessage.value.length>255){
	alert("<?php print str_replace("'","\'",$xxPrd255)?>");
	frm.gcmessage.focus();
	return(false);
}
return (true);
}
/* ]]> */
</script>
<?php	if(! @$isincluded){ ?>
	<form method="post" action="<?php print htmlentities(@$_SERVER['PHP_SELF'])?>" onsubmit="return formvalidator0(this)">
<?php	} ?>
	<input type="hidden" name="posted" value="1" />
	<input type="hidden" name="prod" value="<?php print $donationid?>" />
      <table border="0" cellspacing="1" cellpadding="1" width="98%" align="center">
        <tr> 
          <td align="center" colspan="2"><strong><?php print $xxMakDon?></strong><br />&nbsp;</td>
        </tr>
<?php	if(@$_POST['posted']=='1'){ ?>
        <tr>
          <td align="center" colspan="2"><span style="color:#FF0000;font-weight:bold"><?php print $xxAmtNov?></span><br />&nbsp;</td>
        </tr>
<?php	} ?>
		<tr> 
          <td align="right"><strong><?php print $redasterix?><label for="amount"><?php print $xxAmount?></label>:</strong></td>
		  <td align="left"><input type="text" name="amount" id="amount" size="6" maxlength="10" value="<?php print htmlspecials(@$_POST['amount'])?>" /></td>
        </tr>
		<tr> 
          <td align="right"><strong><label for="fromname"><?php print $xxFrom?></label>:</strong></td>
		  <td align="left"><input type="text" name="fromname" id="fromname" size="25" maxlength="50" value="<?php print htmlspecials(@$_POST['fromname'])?>" /></td>
        </tr>
		<tr> 
          <td align="right"><strong><label for="gcmessage"><?php print $xxMessag?></label>:</strong></td>
		  <td align="left"><textarea name="gcmessage" id="gcmessage" cols="35" rows="4"><?php print htmlspecials(@$_POST['gcmessage'])?></textarea></td>
        </tr>
		<tr> 
          <td colspan="2" align="center"><input type="submit" value="<?php print $xxSubmt?>" /></td>
        </tr>
      </table>
<?php	if(! @$isincluded){ ?>
	</form>
<?php	}
	}
}
?>