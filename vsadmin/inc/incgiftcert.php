<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if($_SESSION["loggedon"] != $storesessionvalue || @$disallowlogin==TRUE) exit;
$success=TRUE;
if(@$dateadjust=='') $dateadjust=0;
if(@$dateformatstr == '') $dateformatstr = 'm/d/Y';
$admindatestr='Y-m-d';
if(@$admindateformat=='') $admindateformat=0;
if($admindateformat==1)
	$admindatestr='m/d/Y';
elseif($admindateformat==2)
	$admindatestr='d/m/Y';
$alreadygotadmin = getadminsettings();
$dorefresh=FALSE;
if(@$_POST['posted']=="1" || @$_GET['act']=='deleteassoc'){
	if(@$_POST['act']=='confirm'){
		$sSQL = "UPDATE giftcertificate SET gcAuthorized=1 WHERE gcID='" . escape_string(@$_POST['id']) . "'";
		mysql_query($sSQL) or print(mysql_error());
	}elseif(@$_POST['act']=='delete'){
		$sSQL = "SELECT gcCartID FROM giftcertificate WHERE gcID='" . escape_string(@$_POST['id']) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)){
			$cartID = $rs['gcCartID'];
			if($cartID!=0) mysql_query("DELETE FROM cart WHERE cartCompleted=0 AND cartID=".$cartID) or print(mysql_error());
		}
		mysql_free_result($result);
		$sSQL = "DELETE FROM giftcertificate WHERE gcID='" . escape_string(@$_POST['id']) . "'";
		mysql_query($sSQL) or print(mysql_error());
		$sSQL = "DELETE FROM giftcertsapplied WHERE gcaGCID='" . escape_string(@$_POST['id']) . "'";
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}elseif(@$_GET['act']=='deleteassoc'){
		if(@$_GET['refund']=='true'){
			$sSQL = "SELECT gcaAmount FROM giftcertsapplied WHERE gcaGCID='" . @$_GET['id'] . "' AND gcaOrdID=" . @$_GET['ord'];
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$sSQL = "UPDATE giftcertificate SET gcRemaining=gcRemaining+" . $rs['gcaAmount'] . " WHERE gcID='" . @$_GET['id'] . "'";
				mysql_query($sSQL) or print(mysql_error());
			}
			mysql_free_result($result);
		}
		$sSQL = "DELETE FROM giftcertsapplied WHERE gcaGCID='" . @$_GET['id'] . "' AND gcaOrdID=" . @$_GET['ord'];
		mysql_query($sSQL) or print(mysql_error());
	}elseif(@$_POST['act']=="doaddnew"){
		$sSQL = "SELECT gcID FROM giftcertificate WHERE gcID='" . strtoupper(escape_string(@$_POST['gcid'])) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result)>0) $success=FALSE; $errmsg = 'Duplicate Gift Certificate ID';
		mysql_free_result($result);
		if($success){
			$sSQL = 'INSERT INTO giftcertificate (gcID,gcFrom,gcTo,gcEmail,gcOrigAmount,gcRemaining,gcDateCreated,';
			if(trim(@$_POST['gcdateused'])<>"") $sSQL .= "gcDateUsed,";
			$sSQL .= "gcAuthorized,gcMessage) VALUES (" .
				"'" . strtoupper(escape_string(@$_POST['gcid'])) . "'" .
				",'" . escape_string(@$_POST['gcfrom']) . "'" .
				",'" . escape_string(@$_POST['gcto']) . "'" .
				",'" . escape_string(@$_POST['gcemail']) . "'" .
				"," . escape_string(@$_POST['gcorigamount']) .
				"," . escape_string(@$_POST['gcremaining']) .
				",'" . (trim(@$_POST['gcdatecreated'])!='' ? date('Y-m-d', parsedate(@$_POST['gcdatecreated'])) : date('Y-m-d')) . "'";
			if(trim(@$_POST['gcdateused'])!='') $sSQL .= ",'" . date('Y-m-d', parsedate(@$_POST['gcdateused'])) . "'";
			$sSQL .= "," . escape_string(@$_POST['gcauthorized']) .
			",'" . escape_string(@$_POST['gcmessage']) . "')";
			mysql_query($sSQL) or print(mysql_error());
			$dorefresh=TRUE;
		}
	}elseif(@$_POST['act']=="domodify"){
		$sSQL = "UPDATE giftcertificate SET " .
			"gcID='" . strtoupper(escape_string(@$_POST['gcid'])) . "'" .
			",gcFrom='" . escape_string(@$_POST['gcfrom']) . "'" .
			",gcTo='" . escape_string(@$_POST['gcto']) . "'" .
			",gcEmail='" . escape_string(@$_POST['gcemail']) . "'" .
			",gcOrigAmount=" . escape_string(@$_POST['gcorigamount']) .
			",gcRemaining=" . escape_string(@$_POST['gcremaining']) .
			",gcDateCreated='" . (trim(@$_POST['gcdatecreated'])!='' ? date('Y-m-d', parsedate(@$_POST['gcdatecreated'])) : date('Y-m-d')) . "'";
		if(trim(@$_POST['gcdateused'])!='') $sSQL .= ",gcDateUsed='" . date('Y-m-d', parsedate(@$_POST['gcdateused'])) . "'";
		$sSQL .= ",gcAuthorized=" . escape_string(@$_POST['gcauthorized']) .
			",gcMessage='" . escape_string(@$_POST['gcmessage']) . "'" .
			" WHERE gcID='" . strtoupper(escape_string(@$_POST['id'])) . "'";
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}elseif(@$_POST['act']=="purgeunconfirmed"){
		$sSQL = "DELETE FROM giftcertificate WHERE isconfirmed=0 AND mlConfirmDate<'" . date('Y-m-d', time()-($mailinglistpurgedays*60*60*24)) . "'";
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=admingiftcert.php';
	print '?stext=' . urlencode(@$_POST['stext']) . '&stype=' . @$_POST['stype'] . '&status=' . @$_POST['status'] . '&pg=' . @$_POST['pg'];
	print '">';
}
if(@$_GET['id']!='' || (@$_POST['posted']=='1' && (@$_POST['act']=='modify' || @$_POST['act']=='addnew'))){
?>
<script language="javascript" type="text/javascript">
<!--
function getgcchar(){
	var gcchar='';
	while(gcchar=="" || gcchar=="O" || gcchar=="I" || gcchar=="Q"){
		gcchar = String.fromCharCode('A'.charCodeAt(0)+Math.round(Math.random()*25));
	}
	return(gcchar);
}
function randomgc(){
	var rannum = Math.floor((Math.random()*899999999)+100000000);
	rannum = getgcchar() + getgcchar() + rannum + getgcchar();
	document.getElementById("gcid").value=rannum;
}
function formvalidator(theForm){
if (theForm.gcid.value == ""){
alert("<?php print $yyPlsEntr?> \"<?php print $yyCerNum?>\".");
theForm.gcid.focus();
return (false);
}
if (theForm.gcto.value == ""){
alert("<?php print $yyPlsEntr?> \"<?php print $yyTo?>\".");
theForm.gcto.focus();
return (false);
}
if (theForm.gcfrom.value == ""){
alert("<?php print $yyPlsEntr?> \"<?php print $yyFrom?>\".");
theForm.gcfrom.focus();
return (false);
}
if (theForm.gcemail.value == ""){
alert("<?php print $yyPlsEntr?> \"<?php print $yyEmail?>\".");
theForm.gcemail.focus();
return (false);
}
if (theForm.gcorigamount.value == ""){
alert("<?php print $yyPlsEntr?> \"<?php print $yyOriAmt?>\".");
theForm.gcorigamount.focus();
return (false);
}
if (theForm.gcremaining.value == ""){
alert("<?php print $yyPlsEntr?> \"<?php print $yyRemain?>\".");
theForm.gcremaining.focus();
return (false);
}
return (true);
}
//-->
</script>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		  <form name="mainform" method="post" action="admingiftcert.php" onsubmit="return formvalidator(this)">
			<td width="100%" align="center">
<?php		writehiddenvar("posted", "1");
			if(@$_POST['act']=="modify" || @$_GET['id']!='')
				writehiddenvar("act", "domodify");
			else
				writehiddenvar("act", "doaddnew");
			writehiddenvar("stext", @$_POST['stext']);
			writehiddenvar("status", @$_POST['status']);
			writehiddenvar("stype", @$_POST['stype']);
			writehiddenvar("pg", @$_POST['pg']);
			writehiddenvar("id", @$_REQUEST['id']); ?>
            <table width="100%" border="0" cellspacing="2" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><strong><?php print $yyGCMan . "<br />&nbsp;" ?></strong></td>
			  </tr>
<?php	if(@$_POST['act']=="modify" || @$_GET['id']!=""){
			$sSQL = "SELECT gcID,gcTo,gcFrom,gcEmail,gcOrigAmount,gcRemaining,gcDateCreated,gcDateUsed,gcAuthorized,gcMessage,gcCartID FROM giftcertificate WHERE gcID='" . escape_string(@$_REQUEST['id']) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$gcid = $rs['gcID'];
				$gcto = $rs['gcTo'];
				$gcfrom = $rs['gcFrom'];
				$gcemail = $rs['gcEmail'];
				$gcorigamount = $rs['gcOrigAmount'];
				$gcremaining = $rs['gcRemaining'];
				$gcdatecreated = $rs['gcDateCreated'];
				if(is_null($gcdatecreated)) $gcdatecreated = date($admindatestr, time() + ($dateadjust*60*60)); else $gcdatecreated = date($admindatestr, strtotime($gcdatecreated));
				$gcdateused = $rs['gcDateUsed'];
				if(is_null($gcdateused)) $gcdateused = date($admindatestr, time() + ($dateadjust*60*60)); else $gcdateused = date($admindatestr, strtotime($gcdateused));
				$gcauthorized = $rs['gcAuthorized'];
				$gcmessage = $rs['gcMessage'];
				$gccartid = $rs['gcCartID'];
			}
			mysql_free_result($result); ?>
<?php	}else{
			$gcid = "";
			$gcto = "";
			$gcfrom = "";
			$gcemail = "";
			$gcorigamount = "";
			$gcremaining = "";
			$gcdatecreated = date($admindatestr, time() + ($dateadjust*60*60));
			$gcdateused = "";
			$gcauthorized = 0;
			$gcmessage = "";
			$gccartid = 0;
		} ?>
			  <tr>
				<td align="right"><p><strong><?php print $yyCerNum?>:</strong></td>
				<td align="left"><?php
		if(@$_POST['act']=="modify")
			print '<input type="hidden" name="gcid" id="gcid" value="'.htmlspecials($gcid).'" /><strong>' . htmlspecials($gcid) . '</strong>';
		else
			print '<input type="text" name="gcid" id="gcid" size="22" value="'.htmlspecials($gcid).'" /> <input type="button" value="Random" onclick="randomgc()" /></td>';
?>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyTo?>:</strong></td>
				<td align="left"><input type="text" name="gcto" size="34" value="<?php print htmlspecials($gcto)?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyFrom?>:</strong></td>
				<td align="left"><input type="text" name="gcfrom" size="34" value="<?php print htmlspecials($gcfrom)?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyEmail?>:</strong></td>
				<td align="left"><input type="text" name="gcemail" size="34" value="<?php print htmlspecials($gcemail)?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyOriAmt?>:</strong></td>
				<td align="left"><input type="text" name="gcorigamount" size="10" value="<?php print htmlspecials($gcorigamount)?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyRemain?>:</strong></td>
				<td align="left"><input type="text" name="gcremaining" size="10" value="<?php print htmlspecials($gcremaining)?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyDatPur?>:</strong></td>
				<td align="left"><input type="text" name="gcdatecreated" size="10" value="<?php print $gcdatecreated?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyDatUsd?>:</strong></td>
				<td align="left"><input type="text" name="gcdateused" size="10" value="<?php print $gcdateused?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyAuthd?>:</strong></td>
				<td align="left"><select name="gcauthorized" size="1">
						<option value="0"><?php print $yyNo?></option>
						<option value="1" <?php if($gcauthorized!=0) print 'selected' ?>><?php print $yyYes?></option></td>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyMessag?>:</strong></td>
				<td align="left"><textarea name="gcmessage" cols="60" rows="5" wrap="virtual"><?php print $gcmessage?></textarea></td>
			  </tr>
<?php	if($gccartid!=0){
			$sSQL = "SELECT cartOrderID FROM cart WHERE cartID=" . $gccartid;
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)) $gcorderid = $rs['cartOrderID']; else $gcorderid = 0;
			mysql_free_result($result);
?>
			  <tr>
				<td align="right"><p><strong><?php print $yyPurOrd?>:</strong></td>
				<td align="left"><?php if($gcorderid==0) print $yyUncOrd; else print '('.$gcorderid.') <a href="adminorders.php?id='.$gcorderid.'">'.$yyClkVw.'.</a>'?></td>
			  </tr>
<?php	}
	if($gcid!=''){
		$sSQL = "SELECT gcaOrdID,gcaAmount FROM giftcertsapplied WHERE gcaGCID='".$gcid."'";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_assoc($result)){ ?>
			  <tr>
				<td align="right"><p><strong><?php print $yyConOrd?>:</strong></td>
				<td align="left"><?php print FormatEuroCurrency($rs['gcaAmount']) . ' (' . $rs['gcaOrdID']. ') <input type="button" value="' . $yyView . '" onclick="document.location=\'adminorders.php?id=' . $rs['gcaOrdID'] . '\'" /> <input type="button" value="' . $yyDelete . '" onclick="document.location=\'admingiftcert.php?act=deleteassoc&ord=' . $rs['gcaOrdID']. '&id='.$gcid.'\'" /> <input type="button" value="'.$yyDelRef.'" onclick="document.location=\'admingiftcert.php?act=deleteassoc&refund=true&ord='.$rs['gcaOrdID'].'&id='.$gcid.'\'" />'?></td>
			  </tr>
<?php	}
		mysql_free_result($result);
	}
?>
			  <tr>
                <td width="100%" colspan="2" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" />&nbsp;<input type="reset" value="<?php print $yyReset?>" /><br />&nbsp;</td>
			  </tr>
			  <tr>
                <td width="100%" colspan="2" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table></td>
		  </form>
        </tr>
      </table>
<?php
}elseif(@$_POST['posted']=="1" && @$_POST['act']!="confirm" && $success){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="admingiftcert.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />&nbsp;<br />&nbsp;
                </td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}elseif(@$_POST['posted']=="1" && @$_POST['act']!="confirm"){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a><p>&nbsp;</p><p>&nbsp;</p></td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}else{
	$sSQL = "SELECT count(*) AS thecount FROM giftcertificate WHERE gcRemaining>0";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)) $numemails = $rs['thecount']; else $numemails=0;
	mysql_free_result($result);
?>
<script language="javascript" type="text/javascript">
<!--
function mrec(id) {
	document.mainform.id.value = id;
	document.mainform.act.value = "modify";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function crec(id) {
	document.mainform.id.value = id;
	document.mainform.act.value = "confirm";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function newrec(id) {
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function sendem(id) {
	document.mainform.act.value = "sendem";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function drec(id) {
cmsg = "<?php print $yyConDel?>\n"
if (confirm(cmsg)) {
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
}
function startsearch(){
	document.mainform.action="admingiftcert.php";
	document.mainform.act.value = "search";
	document.mainform.listem.value = "";
	document.mainform.posted.value = "";
	document.mainform.submit();
}
function listem(thelet){
	document.mainform.action="admingiftcert.php";
	document.mainform.act.value = "search";
	document.mainform.listem.value = thelet;
	document.mainform.posted.value = "";
	document.mainform.submit();
}
function removeuncon(){
cmsg = "<?php print $yyConDel?>\n"
if (confirm(cmsg)) {
	document.mainform.act.value = "purgeunconfirmed";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
}
// -->
</script>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		<form name="mainform" method="post" action="admingiftcert.php">
		  <td width="100%">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="listem" value="<?php print @$_REQUEST['listem']?>" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="pg" value="<?php print (@$_POST['act']=="search" ? "1" : @$_GET['pg'])?>" />
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr>
				<td class="cobhl" colspan="4" align="center"><strong><?php
					print $numemails . " " . $yyActGC;
				?><strong></td>
			  </tr>
			  <tr> 
				<td class="cobhl" width="25%" align="right"><?php print $yySrchFr?>:</td>
				<td class="cobll" width="25%"><input type="text" name="stext" size="20" value="<?php print @$_REQUEST['stext']?>" /></td>
				<td class="cobhl" align="right"><?php print $yyStatus?>:</td>
				<td class="cobll"><select name="status" size="1">
					<option value="any">All Certificates</option>
					<option value="" <?php if(@$_REQUEST['status']=='') print 'selected'?>><?php print $yyActGC?></option>
					<option value="spent" <?php if(@$_REQUEST['status']=="spent") print 'selected'?>><?php print $yyInaGC?></option>
					</select>
				</td>
			  </tr>
			  <tr>
				<td class="cobhl" align="right"><?php print $yySrchTp?>:</td>
				<td class="cobll"><select name="stype" size="1">
					<option value=""><?php print $yySrchAl?></option>
					<option value="any" <?php if(@$_REQUEST['stype']=="any") print 'selected'?>><?php print $yySrchAn?></option>
					<option value="exact" <?php if(@$_REQUEST['stype']=="exact") print 'selected'?>><?php print $yySrchEx?></option>
					</select>
				</td>
				<td class="cobll" colspan="2" align="center">
						<input type="button" value="<?php print $yyListRe?>" onclick="startsearch();" />
						<input type="button" value="New Gift Certificate" onclick="newrec();" />
				</td>
			  </tr>
			</table>
            <table width="100%" border="0" cellspacing="0" cellpadding="1">
<?php
	if(@$_POST['act']=="search" || @$_GET['pg']!='' || @$_POST['act']=="confirm"){
		function displayprodrow($xrs){
			global $yyModify,$yyDelete,$bgcolor;
			if($xrs['gcAuthorized']!=0){ $startstyle=''; $endstyle=''; } else{ $startstyle='<span style="color:#FF0000">'; $endstyle='</span>'; }
			?><tr class="<?php print $bgcolor?>"><td><?php print $startstyle . htmlspecials($xrs['gcID']) . $endstyle?></td>
			<td><?php print $startstyle . htmlspecials($xrs['gcTo']) . $endstyle?></td>
			<td><?php print $startstyle . htmlspecials($xrs['gcFrom']) . $endstyle?></td>
			<td><?php print $startstyle . FormatEuroCurrency($xrs['gcOrigAmount']) . $endstyle?></td>
			<td><?php print $startstyle . FormatEuroCurrency($xrs['gcRemaining']) . $endstyle?></td>
			<td><?php print $startstyle . htmlspecials($xrs['gcDateCreated']) . $endstyle?></td>
			<td align="center"><input type="button" value="<?php print $yyModify?>" onclick="mrec('<?php print str_replace(array("\\","'",'"'),array("\\\\","\'",'&quot;'),$xrs['gcID'])?>')" /></td>
			<td align="center"><input type="button" value="<?php print $yyDelete?>" onclick="drec('<?php print str_replace(array("\\","'",'"'),array("\\\\","\'",'&quot;'),$xrs['gcID'])?>')" /></td>
			<td>&nbsp;</td></tr>
<?php	}
		function displayheaderrow(){
			global $yyCerNum,$yyTo,$yyFrom,$yyAmount,$yyRemain,$yyDate,$yyModify,$yyDelete;
?>
			<tr>
				<td><strong><?php print $yyCerNum?></strong></td>
				<td><strong><?php print $yyTo?></strong></td>
				<td><strong><?php print $yyFrom?></strong></td>
				<td><strong><?php print $yyAmount?></strong></td>
				<td><strong><?php print $yyRemain?></strong></td>
				<td><strong><?php print $yyDate?></strong></td>
				<td width="8%" align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyModify?></span></td>
				<td width="8%" align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyDelete?></span></td>
				<td width="10%">&nbsp;</td>
			</tr>
<?php	}
		$whereand = ' WHERE ';
		$sSQL = 'SELECT gcID,gcTo,gcFrom,gcEmail,gcOrigAmount,gcRemaining,gcDateCreated,gcDateUsed,gcAuthorized FROM giftcertificate ';
		if(trim(@$_REQUEST['stext'])!=''){
			$sText = escape_string(@$_REQUEST['stext']);
			$Xstext = escape_string(@$_REQUEST['stext']);
			$aText = explode(' ',$Xstext);
			$aFields[0]="gcID";
			$aFields[1]="gcTo";
			$aFields[2]="gcFrom";
			$aFields[3]="gcEmail";
			if(@$_REQUEST['stype']=="exact")
				$sSQL .= $whereand . " (gcID LIKE '%" . $Xstext . "%' OR gcTo LIKE '%" . $Xstext . "%' OR gcFrom LIKE '%" . $Xstext . "%' OR gcEmail LIKE '%" . $Xstext . "%') ";
			else{
				if(@$_REQUEST['stype']=="any") $sJoin="OR "; else $sJoin="AND ";
				$sSQL .= $whereand . "(";
				$whereand=' AND ';
				for($index=0;$index<=3;$index++){
					$sSQL .= "(";
					$rowcounter=0;
					$arrelms=count($aText);
					foreach($aText as $theopt){
						if(is_array($theopt))$theopt=$theopt[0];
						$sSQL .= $aFields[$index] . " LIKE '%" . $theopt . "%' ";
						if(++$rowcounter < $arrelms) $sSQL .= $sJoin;
					}
					$sSQL .= ") ";
					if($index < 3) $sSQL .= "OR ";
				}
				$sSQL .= ") ";
			}
			$whereand = " AND";
		}
		if(trim(@$_REQUEST['status'])==''){
			$sSQL .= $whereand . " (gcRemaining>0 AND gcAuthorized<>0)";
			$whereand = " AND";
		}elseif(trim(@$_REQUEST['status'])=='spent'){
			$sSQL .= $whereand . " (gcRemaining<=0 OR gcAuthorized=0)";
			$whereand = " AND";
		}
		$sSQL .= " ORDER BY gcDateCreated";
		if(! @is_numeric($_GET["pg"]))
			$CurPage = 1;
		else
			$CurPage = (int)($_GET["pg"]);
		$adminproductsperpage=100;
		$tmpSQL = str_replace('SELECT gcID,gcTo,gcFrom,gcEmail,gcOrigAmount,gcRemaining,gcDateCreated,gcDateUsed,gcAuthorized', 'SELECT COUNT(*) AS bar', $sSQL);
		$allprods = mysql_query($tmpSQL) or print(mysql_error());
		$iNumOfPages = ceil(mysql_result($allprods,0,"bar")/$adminproductsperpage);
		mysql_free_result($allprods);
		$sSQL .= ' LIMIT ' . ($adminproductsperpage*($CurPage-1)) . ', ' . $adminproductsperpage;
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result) > 0){
			$pblink = '<a href="adminprods.php?status=' . @$_REQUEST['status'] . '.stext=' . urlencode(@$_REQUEST['stext']) . '.stype=' . @$_REQUEST['stype'] . '.pg=';
			if($iNumOfPages > 1) print '<tr><td colspan="8" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
			displayheaderrow();
			$addcomma='';
			while($rs = mysql_fetch_assoc($result)){
				if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark';
				displayprodrow($rs);
				$addcomma=',';
			}
			if($iNumOfPages > 1) print '<tr><td colspan="8" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
		}else{
			print '<tr><td width="100%" colspan="8" align="center"><br />' . $yyItNone . '<br />&nbsp;</td></tr>';
		}
		mysql_free_result($result);
	} ?>
			  <tr> 
                <td width="100%" colspan="8" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table></td>
		  </form>
        </tr>
      </table>
<?php
}
?>