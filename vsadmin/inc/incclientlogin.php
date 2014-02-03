<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
include './vsadmin/inc/incemail.php';
if(@$_SERVER['CONTENT_LENGTH'] != '' && $_SERVER['CONTENT_LENGTH'] > 10000) exit;
$success=TRUE;
if(@$dateformatstr=='') $dateformatstr = 'm/d/Y';
$ordGrandTotal = $ordTotal = $ordStateTax = $ordHSTTax = $ordCountryTax = $ordShipping = $ordHandling = $ordDiscount = 0;
$ordID = $affilID = $ordCity = $ordState = $ordCountry = $ordDiscountText = $ordEmail = '';
$digidownloads=FALSE;
$allstates='';
$allcountries='';
$warncheckspamfolder = FALSE;
if(@$enableclientlogin!=TRUE && @$forceclientlogin!=TRUE){
	$success=FALSE;
	$errmsg="Client login not enabled";
}
if(@$pathtossl!=''){
	if(substr($pathtossl,-1)!='/') $pathtossl.='/';
}else
	$pathtossl='';
$pagename = htmlentities(basename($_SERVER['PHP_SELF']));
if(@$forceloginonhttps) $thisaction = $pathtossl . basename(@$_SERVER['PHP_SELF']); else $thisaction = @$_SERVER['PHP_SELF'];
function show_states($tstate){
	global $xxOutState,$allstates,$numallstates,$usestateabbrev;
	$foundmatch=FALSE;
	if($xxOutState!='') print '<option value="">' . $xxOutState . '</option>';
	for($index=0;$index<$numallstates;$index++){
		print '<option value="' . htmlspecials(@$usestateabbrev==TRUE?$allstates[$index]['stateAbbrev']:$allstates[$index]['stateName']) . '"';
		if($tstate==$allstates[$index]['stateName'] || $tstate==$allstates[$index]['stateAbbrev']){
			print ' selected="selected"';
			$foundmatch=TRUE;
		}
		print '>' . $allstates[$index]['stateName'] . "</option>\n";
	}
	return $foundmatch;
}
function show_countries($tcountry){
	global $numhomecountries,$nonhomecountries,$allcountries,$numallcountries;
	for($index=0;$index<$numallcountries;$index++){
		print '<option value="' . htmlspecials($allcountries[$index]['countryName']) . '"';
		if($tcountry==$allcountries[$index]['countryName']) print ' selected="selected"';
		print '>' . $allcountries[$index][2] . "</option>\n";
	}
}
$alreadygotadmin = getadminsettings();
?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function vieworder(theid){
	document.forms.mainform.action.value="vieworder";
	document.forms.mainform.theid.value=theid;
	document.forms.mainform.submit();
}
function editaddress(theid){
	document.forms.mainform.action.value="editaddress";
	document.forms.mainform.theid.value=theid;
	document.forms.mainform.submit();
}
function newaddress(){
	document.forms.mainform.action.value="newaddress";
	document.forms.mainform.submit();
}
function editaccount(){
	document.forms.mainform.action.value="editaccount";
	document.forms.mainform.submit();
}
function deleteaddress(theid){
	if(confirm("<?php print $xxDelAdd?>")){
		document.forms.mainform.action.value="deleteaddress";
		document.forms.mainform.theid.value=theid;
		document.forms.mainform.submit();
	}
}
function createlist(){
if(document.forms.mainform.listname.value==''){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxLisNam?>\".");
	document.forms.mainform.listname.focus();
	return(false);
}else{
	document.forms.mainform.action.value="createlist";
	document.forms.mainform.submit();
}
}
function deletelist(theid){
	if(confirm("<?php print $xxDelLis?>")){
		document.forms.mainform.action.value="deletelist";
		document.forms.mainform.theid.value=theid;
		document.forms.mainform.submit();
	}
}
/* ]]> */
</script>
<?php
	if(@$_POST['doresetpw']=="1"){
		$sSQL = "SELECT clID FROM customerlogin WHERE clEmail='".escape_string(@$_POST['rst'])."' AND clPw='".escape_string(@$_POST['rsk'])."'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)) $clid=$rs['clID']; else $clid='';
		if(trim(@$_POST['newpw'])=='') $clid='';
		mysql_free_result($result);
		if($clid!='') mysql_query("UPDATE customerlogin SET clPw='".escape_string(dohashpw(@$_POST['newpw']))."' WHERE clID=" . $clid) or print(mysql_error());
?>	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr>
		  <td class="cobhl" align="center" height="38" colspan="2"><strong><?php print $xxCusAcc?></strong></td>
		</tr>
		  <tr>
			<td class="cobhl" align="right" height="38" width="40%"><strong><?php print $xxForPas?></strong></td>
			<td class="cobll" align="left" height="38"><?php print ($clid==''?$xxEmNtFn:$xxPasRsS) ?></td>
		  </tr>
		  <tr>
			<td class="cobll" align="center" height="38" colspan="2"><?php
		if($clid!='')
			print imageorbutton(@$imglogin,$xxLogin,'',(@$forceloginonhttps?$pathtossl:'').'cart.php?mode=login',FALSE);
		else
			print imageorbutton(@$imggoback,$xxGoBack,'','history.go(-1)',TRUE); ?></p></td>
		  </tr>
      </table>
<?php
	}elseif(@$_GET['rst']!='' && @$_GET['rsk']!=''){
		$sSQL = "SELECT clID FROM customerlogin WHERE clEmail='".escape_string(@$_GET['rst'])."' AND clPw='".escape_string(@$_GET['rsk'])."'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result)>0) $success=TRUE; else $success=FALSE;
		mysql_free_result($result);
		if(! $success){ ?>
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr>
		  <td class="cobhl" align="center" height="38" colspan="2"><strong><?php print $xxCusAcc?></strong></td>
		</tr>
		  <tr>
			<td class="cobhl" align="right" height="38" width="40%"><strong><?php print $xxForPas?></strong></td>
			<td class="cobll" align="left" height="38"><?php print $xxSorRes ?></td>
		  </tr>
		  <tr>
			<td class="cobll" align="center" height="38" colspan="2"><?php print imageorbutton(@$imgcancel,$xxCancel,"",$storeurl,FALSE) ?></td>
		  </tr>
      </table>
<?php	}else{ ?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function checknewpw(frm){
if(frm.newpw.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxNewPwd?>\".");
	frm.newpw.focus();
	return(false);
}
var newpw = frm.newpw.value;
var newpw2 = frm.newpw2.value;
if(newpw!=newpw2){
	alert("<?php print $xxPwdMat?>");
	frm.newpw.focus();
	return(false);
}
return true;
}
/* ]]> */
</script>
	<form method="post" name="mainform" action="<?php print $thisaction?>" onsubmit="return checknewpw(this)">
	<input type="hidden" name="doresetpw" value="1" />
	<input type="hidden" name="rst" value="<?php print str_replace('"','',@$_GET['rst'])?>" />
	<input type="hidden" name="rsk" value="<?php print str_replace('"','',@$_GET['rsk'])?>" />
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr>
		  <td class="cobhl" align="center" height="38" colspan="2"><strong><?php print $xxCusAcc . ' ' . $xxForPas?></strong></td>
		</tr>
		  <tr>
			<td class="cobhl" align="right" height="38" width="40%"><strong><?php print $xxNewPwd?></strong></td>
			<td class="cobll" align="left" height="38"><input type="password" size="20" name="newpw" value="" autocomplete="off" /></td>
		  </tr>
		  <tr>
			<td class="cobhl" align="right" height="38" width="40%"><strong><?php print $xxRptPwd?></strong></td>
			<td class="cobll" align="left" height="38"><input type="password" size="20" name="newpw2" value="" autocomplete="off" /></td>
		  </tr>
		  <tr>
			<td class="cobll" align="center" height="38" colspan="2">
		<?php print imageorsubmit(@$imgsubmit,$xxSubmt,'').' '.imageorbutton(@$imgcancel,$xxCancel,'',$storeurl,FALSE)?></td>
		  </tr>
      </table>
	</form>
<?php	}
	}elseif(@$_GET['action']=='logout'){
		$_SESSION['clientID']=NULL; unset($_SESSION['clientID']);
		$_SESSION['clientUser']=NULL; unset($_SESSION['clientUser']);
		$_SESSION['clientActions']=NULL; unset($_SESSION['clientActions']);
		$_SESSION['clientLoginLevel']=NULL; unset($_SESSION['clientLoginLevel']);
		$_SESSION['clientPercentDiscount']=NULL; unset($_SESSION['clientPercentDiscount']);
		print '<script src="vsadmin/savecookie.php?DELCLL=true"></script>';
		if(@$clientlogoutref != '')
			$refURL = $clientlogoutref;
		else
			$refURL = $xxHomeURL;
		print '<meta http-equiv="refresh" content="3; url=' . $refURL . '">';
?>
		<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		  <tr>
			<td class="cobll" align="center">
			  <p>&nbsp;</p>
			  <br /><strong><?php print $xxLOSuc?></strong><br /><br /><?php print $xxAutFo?><br /><br />
				<?php print $xxForAut?> <a class="ectlink" href="<?php print $refURL?>"><strong><?php print $xxClkHere?></strong></a>.<br />
				<br />&nbsp;
			</td>
		  </tr>
		</table>
<?php	
	}elseif(@$_POST['action']=='dolostpassword'){
		$theemail = cleanupemail(unstripslashes(@$_POST['email']));
		$sSQL = "SELECT clPW FROM customerlogin WHERE clEmail<>'' AND clEmail='" . escape_string($theemail) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result) > 0){
			$rs = mysql_fetch_assoc($result);
			if(@$htmlemails==TRUE) $emlNl = '<br />'; else $emlNl="\n";
			$tlink = $storeurl . $pagename . "?rst=" . $theemail . "&rsk=" . $rs['clPW'];
			if(@$htmlemails==TRUE) $tlink = '<a href="' . $tlink . '">' . $tlink . '</a>';
			dosendemail($theemail, $emailAddr, '', $xxForPas, $xxLosPw1 . $emlNl . $storeurl . $emlNl . $emlNl . $xxResPas . $emlNl . $tlink . $emlNl . $emlNl . $xxLosPw3 . $emlNl);
			$success=TRUE;
		}else{
			$success=FALSE;
		}
		mysql_free_result($result); ?>
	  <form method="post" name="mainform" action="<?php print $thisaction?>">
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr>
		  <td class="cobhl" align="center" height="38" colspan="2"><strong><?php print $xxCusAcc?></strong></td>
		</tr>
		  <tr>
			<td class="cobhl" align="right" height="38" width="40%"><strong><?php print $xxForPas?></strong></td>
			<td class="cobll" align="left" height="38"><?php if($success) print $xxSenPw; else print $xxSorPw; ?></td>
		  </tr>
		  <tr>
			<td class="cobll" align="center" height="38" colspan="2"><?php
		if($success)
			print imageorbutton(@$imglogin,$xxLogin,'',(@$forceloginonhttps?$pathtossl:'') . 'cart.php?mode=login',FALSE);
		else
			print imageorbutton(@$imggoback,$xxGoBack,'','history.go(-1)',TRUE);
		?></td>
		  </tr>
	  </table>
	  </form>
<?php
	}elseif(@$_GET['mode'] == 'lostpassword'){ ?>
	  <form method="post" name="mainform" action="<?php print $thisaction?>">
	  <input type="hidden" name="action" value="dolostpassword" />
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr>
		  <td class="cobhl" align="center" height="32" colspan="2"><strong><?php print $xxCusAcc?></strong></td>
		</tr>
		<tr>
		  <td class="cobhl" align="right" height="26"><strong><?php print $xxForPas?></strong></td>
		  <td class="cobll" align="left" height="26"><span style="font-size:10px"><?php print $xxEntEm?></span></td>
		</tr>
		<tr>
		  <td class="cobhl" align="right" height="26"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php print $xxEmail?>: </strong></td>
		  <td class="cobll" align="left" height="26"><input type="text" name="email" size="31" /></td>
		</tr>
		<tr>
		  <td class="cobhl" align="center" height="26" colspan="2"><?php print imageorsubmit(@$imgsubmit,$xxSubmt,'')?></td>
		</tr>
      </table>
	  </form>
<?php
	}elseif(@$_SESSION['clientID']==''){ ?>
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
        <tr>
		  <td class="cobhl" align="center" height="32" colspan="2"><strong><?php print $xxCusAcc?></strong></td>
		</tr>
		<tr>
		  <td class="cobll" align="center" height="32" colspan="2"><p>&nbsp;</p><p><?php print $xxMusLog?></p>
		  <p><?php print imageorbutton(@$imglogin,$xxLogin,'',(@$forceloginonhttps?$pathtossl:'')."cart.php?mode=login&amp;refurl=".urlencode(@$_SERVER['PHP_SELF']),FALSE)?></p>
		  <p>&nbsp;</p>
		  </td>
		</tr>
      </table>
<?php
	}else{ // is logged in
		if(@$_POST['action']=='vieworder'){ ?>
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
        <tr>
		  <td width="100%" class="cobll"><?php
			$ordID = str_replace("'",'',@$_POST['theid']);
			if(is_numeric($ordID)) $success=TRUE; else $success=FALSE;
			if($success){
				$sSQL = "SELECT ordID FROM orders WHERE ordID=" . $ordID . " AND ordClientID=" . $_SESSION['clientID'];
				$result = mysql_query($sSQL) or print(mysql_error());
				if(mysql_num_rows($result)==0) $success=FALSE;
				mysql_free_result($result);
			}
			if($success){
				$xxThkYou=imageorbutton(@$imgbackacct,$xxBack,'','history.go(-1)',TRUE);
				$xxRecEml='';
				$thankspagecontinue='javascript:history.go(-1)';
				$xxCntShp=$xxBack;
				$imgcontinueshopping=@$imgbackacct;
				do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
			}else{
				$errtext = "Sorry, could not find a matching order.";
				order_failed();
			} ?>
		  </td>
		</tr>
	  </table>
<?php	}elseif(@$_POST['action']=='doeditaccount'){
			$oldpw = dohashpw(unstripslashes(@$_POST['oldpw']));
			$newpw = unstripslashes(@$_POST['newpw']);
			$newpw2 = unstripslashes(@$_POST['newpw2']);
			$clientuser = unstripslashes(@$_POST['name']);
			$clientemail = cleanupemail(unstripslashes(@$_POST['email']));
			$allowemail = @$_POST['allowemail'];
			$sSQL = "SELECT clPW,clEmail FROM customerlogin WHERE clID=" . $_SESSION['clientID'];
			$result = mysql_query($sSQL) or print(mysql_error());
			$rs = mysql_fetch_assoc($result);
			mysql_free_result($result);
			$oldpassword=$rs['clPW'];
			$oldemail=$rs['clEmail'];
			$success=TRUE;
			if($newpw!='' || $newpw2!=''){
				if($oldpw!=$oldpassword){
					$success=FALSE;
					$errmsg=$xxExNoMa;
				}
			}
			if($oldemail != $clientemail){
				$sSQL = "SELECT clID FROM customerlogin WHERE clEmail='" . escape_string($clientemail) . "'";
				$result = mysql_query($sSQL) or print(mysql_error());
				if(mysql_num_rows($result) > 0){
					$success=FALSE;
					$errmsg=$xxEmExi;
				}
				mysql_free_result($result);
			}
			if($success){
				$sSQL = 'UPDATE customerlogin SET ';
				$sSQL .= "clUserName='" . escape_string($clientuser) . "',";
				$sSQL .= "clEmail='" . escape_string($clientemail) . "'";
				if($newpw!='') $sSQL .= ",clPW='" . escape_string(dohashpw($newpw)) . "'";
				$sSQL .= " WHERE clID=" . $_SESSION['clientID'];
				mysql_query($sSQL) or print(mysql_error());
				if($allowemail=='ON'){
					addtomailinglist($clientemail,$clientuser);
					if($oldemail != $clientemail) mysql_query("DELETE FROM mailinglist WHERE email='" . escape_string($oldemail) . "'");
				}else{
					mysql_query("DELETE FROM mailinglist WHERE email='" . escape_string($clientemail) . "'");
					mysql_query("DELETE FROM mailinglist WHERE email='" . escape_string($oldemail) . "'");
				}
				$_SESSION['clientUser']=$clientuser;
				print '<meta http-equiv="Refresh" content="2; URL=' . $_SERVER['PHP_SELF'] . '">';
			}
?>
	<form method="post" name="mainform" action="<?php print $thisaction?>">
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr>
		  <td class="cobhl" align="center" height="38" colspan="2"><strong><?php print $xxCusAcc?></strong></td>
		</tr>
		<tr>
		  <td class="cobll" align="center" height="38"><?php if($success) print $xxUpdSuc; else print $errmsg ?></td>
		</tr>
		<tr>
		  <td class="cobll" align="center" height="38" colspan="2"><?php
		if($success)
			print imageorsubmit(@$imgcustomeracct,$xxCusAcc,'');
		else
			print imageorbutton(@$imggoback,$xxGoBack,'','history.go(-1)',TRUE);
		?></td>
		</tr>
	  </table>
	</form>
<?php	}elseif(@$_POST['action']=='editaccount'){
			if(@$forceloginonhttps && (@$_SERVER['HTTPS']!='on' && @$_SERVER['SERVER_PORT']!='443') && strpos(@$pathtossl,'https')!==FALSE){ header('Location: '.$pathtossl.basename($_SERVER['PHP_SELF']).(@$_SERVER['QUERY_STRING']!='' ? '?'.$_SERVER['QUERY_STRING'] : '')); exit; }
?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
var checkedfullname=false;
function checknewaccount(){
frm=document.forms.mainform;
if(frm.name.value==""||frm.name.value=="<?php print $xxFirNam?>"){
	alert("<?php print $xxPlsEntr?> \"<?php print (@$usefirstlastname ? $xxFirNam : $xxName)?>\".");
	frm.name.focus();
	return (false);
}
gotspace=false;
var checkStr = frm.name.value;
for (i = 0; i < checkStr.length; i++){
	if(checkStr.charAt(i)==" ")
		gotspace=true;
}
if(!checkedfullname && !gotspace){
	alert("<?php print $xxFulNam?> \"<?php print $xxName?>\".");
	frm.name.focus();
	checkedfullname=true;
	return (false);
}
if(frm.email.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxEmail?>\".");
	frm.email.focus();
	return (false);
}
var regex = /[^@]+@[^@]+\.[a-z]{2,}$/i;
if(!regex.test(frm.email.value)){
	alert("<?php print $xxValEm?>");
	frm.email.focus();
	return (false);
}
var newpw = frm.newpw.value;
var newpw2 = frm.newpw2.value;
if(newpw!='' && newpw!=newpw2){
	alert("<?php print $xxPwdMat?>");
	frm.newpw.focus();
	return(false);
}
return true;
}
/* ]]> */
</script>
		<form method="post" name="mainform" action="<?php print $thisaction?>" onsubmit="return checknewaccount()">
		<input type="hidden" name="action" value="doeditaccount" />
		<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		  <tr>
            <td class="cobhl" align="center" height="34"><strong><?php print $xxAccDet?></strong></td>
		  </tr>
		  <tr>
            <td class="cobll" align="center">
				  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php		$sSQL = "SELECT clID,clUserName,clActions,clLoginLevel,clPercentDiscount,clEmail,loyaltyPoints FROM customerlogin WHERE clID=" . $_SESSION['clientID'];
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)) $theemail=$rs['clEmail']; else $_SESSION['clientID']='';
			mysql_free_result($result);
			$sSQL = "SELECT email FROM mailinglist WHERE email='" . escape_string(@$theemail) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0) $allowemail=1; else $allowemail=0;
			mysql_free_result($result);
?>
					<tr><td class="cobhl" align="right" width="<?php print (@$nounsubscribe?'50':'20')?>%"><strong><?php print $xxName?>:</strong></td>
					<td class="cobll" align="left" width="<?php print (@$nounsubscribe?'50':'30')?>%"><input type="text" size="30" name="name" value="<?php print htmlspecials($_SESSION['clientUser'])?>" /></td>
<?php		if(@$nounsubscribe!=TRUE){ ?>
					<td class="cobll" align="right" width="8%" rowspan="2"><input type="checkbox" name="allowemail" value="ON"<?php if($allowemail!=0) print ' checked="checked"'?> /></td>
					<td class="cobhl" align="left" rowspan="2"><strong><?php print $xxAlPrEm?></strong><br />
					<span style="font-size:10px"><?php print $xxNevDiv?></span></td>
<?php		} ?>
					</tr><tr><td class="cobhl" align="right"><strong><?php print $xxEmail?>:</strong></td>
					<td class="cobll" align="left"><input type="text" size="30" name="email" value="<?php print $theemail?>" /></td>
					</tr><tr><td class="cobhl" align="center" colspan="<?php print (@$nounsubscribe?'2':'4')?>" height="34"><strong><?php print $xxPwdChg?></strong></td></tr>
					
					<tr><td class="cobhl" align="right" <?php print (@$nounsubscribe?'':'colspan="2"')?>><strong><?php print $xxOldPwd?>:</strong></td>
					<td class="cobll" align="left" <?php print (@$nounsubscribe?'':'colspan="2"')?>><input type="password" size="20" name="oldpw" value="" autocomplete="off" /></td></tr>
					<tr><td class="cobhl" align="right" <?php print (@$nounsubscribe?'':'colspan="2"')?>><strong><?php print $xxNewPwd?>:</strong></td>
					<td class="cobll" align="left" <?php print (@$nounsubscribe?'':'colspan="2"')?>><input type="password" size="20" name="newpw" value="" autocomplete="off" /></td></tr>
					<tr><td class="cobhl" align="right" <?php print (@$nounsubscribe?'':'colspan="2"')?>><strong><?php print $xxRptPwd?>:</strong></td>
					<td class="cobll" align="left" <?php print (@$nounsubscribe?'':'colspan="2"')?>><input type="password" size="20" name="newpw2" value="" autocomplete="off" /></td></tr>
	
					<tr><td class="cobll" align="center" colspan="4" height="34"><?php print imageorsubmit(@$imgsubmit,$xxSubmt,'').' '.imageorbutton(@$imgcancel,$xxCancel,'','history.go(-1)',TRUE)?></td></tr>
				  </table>
			</td>
		  </tr>
		</table>
		</form>
<?php	}elseif(@$_POST['action']=='editaddress' || @$_POST['action']=='newaddress'){
			$addID = str_replace("'",'',@$_POST['theid']);
			$addIsDefault='';
			$addName='';
			$addLastName='';
			$addAddress='';
			$addAddress2='';
			$addState='';
			$addCity='';
			$addZip='';
			$addPhone='';
			$addCountry='';
			$addExtra1='';
			$addExtra2='';
			$havestate=FALSE;
			$sSQL = "SELECT stateName,stateAbbrev FROM states WHERE stateEnabled=1 ORDER BY stateName";
			$result = mysql_query($sSQL) or print(mysql_error());
			$numallstates=0;
			$numallcountries=0;
			while($rs = mysql_fetch_array($result))
				$allstates[$numallstates++]=$rs;
			mysql_free_result($result);
			$numhomecountries = 0;
			$nonhomecountries = 0;
			$sSQL = "SELECT countryName,countryOrder,".getlangid("countryName",8)." FROM countries WHERE countryEnabled=1 ORDER BY countryOrder DESC," . getlangid("countryName",8);
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_array($result)){
				$allcountries[$numallcountries++]=$rs;
				if($rs['countryOrder']>=2)$numhomecountries++;else $nonhomecountries++;
			}
			mysql_free_result($result);
			if(@$_POST['action']=='editaddress'){
				$sSQL = "SELECT addID,addIsDefault,addName,addLastName,addAddress,addAddress2,addState,addCity,addZip,addPhone,addCountry,addExtra1,addExtra2 FROM address WHERE addID=" . $addID . " AND addCustID='" . $_SESSION['clientID'] . "' ORDER BY addIsDefault";
				$result = mysql_query($sSQL) or print(mysql_error());
				if($rs = mysql_fetch_assoc($result)){
					$addIsDefault=$rs['addIsDefault'];
					$addName=$rs['addName'];
					$addLastName=$rs['addLastName'];
					$addAddress=$rs['addAddress'];
					$addAddress2=$rs['addAddress2'];
					$addState=$rs['addState'];
					$addCity=$rs['addCity'];
					$addZip=$rs['addZip'];
					$addPhone=$rs['addPhone'];
					$addCountry=$rs['addCountry'];
					$addExtra1=$rs['addExtra1'];
					$addExtra2=$rs['addExtra2'];
				}
				mysql_free_result($result);
			} ?>
	<form method="post" name="mainform" action="<?php print $thisaction?>" onsubmit="return checkform(this)">
	<input type="hidden" name="action" value="<?php if(@$_POST['action']=='editaddress') print "doeditaddress"; else print "donewaddress" ?>" />
	<input type="hidden" name="theid" value="<?php print $addID?>" />
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr><td align="center" class="cobhl" colspan="2" height="32"><strong><?php print $xxEdAdd?></strong></td></tr>
		<?php	if(trim(@$extraorderfield1) != ''){ ?>
		<tr><td align="right" class="cobhl"><strong><?php print (@$extraorderfield1required==TRUE ? $redasterix : '') . $extraorderfield1 ?>:</strong></td><td class="cobll"><?php if(@$extraorderfield1html != '') print $extraorderfield1html; else print '<input type="text" name="ordextra1" id="ordextra1" size="20" value="' . htmlspecials($addExtra1) . '" />'?></td></tr>
		<?php	} ?>
		<tr><td align="right" class="cobhl"><strong><?php print $redasterix.$xxName?>:</strong></td><td class="cobll"><?php
		if(@$usefirstlastname){
			$thestyle='';
			if($addName=='' && $addLastName==''){ $addName=$xxFirNam; $addLastName=$xxLasNam; $thestyle='style="color:#BBBBBB" '; }
			print '<input type="text" name="name" size="11" value="'.htmlspecials($addName).'" alt="'.$xxFirNam.'" onfocus="if(this.value==\''.$xxFirNam.'\'){this.value=\'\';this.style.color=\'\';}" '.$thestyle.'/> <input type="text" name="lastname" size="11" value="'.htmlspecials($addLastName).'" alt="'.$xxLasNam.'" onfocus="if(this.value==\''.$xxLasNam.'\'){this.value=\'\';this.style.color=\'\';}" '.$thestyle.'/>';
		}else
			print '<input type="text" name="name" id="name" size="20" value="'.htmlspecials($addName).'" />';
		?></td></tr>
		<tr><td align="right" class="cobhl"><strong><?php print $redasterix.$xxAddress?>:</strong></td><td class="cobll"><input type="text" name="address" id="address" size="25" value="<?php print htmlspecials($addAddress)?>" /></td></tr>
		<?php	if(@$useaddressline2==TRUE){ ?>
		<tr><td align="right" class="cobhl"><strong><?php print $xxAddress2?>:</strong></td><td class="cobll"><input type="text" name="address2" id="address2" size="25" value="<?php print htmlspecials($addAddress2)?>" /></td></tr>
		<?php	} ?>
		<tr><td align="right" class="cobhl"><strong><?php print $redasterix.$xxCity?>:</strong></td><td class="cobll"><input type="text" name="city" id="city" size="20" value="<?php print htmlspecials($addCity)?>" /></td></tr>
		<?php	if($numallstates>0){ ?>
		<tr><td align="right" class="cobhl"><strong><span id="outspandd" style="color:#FF0000;visibility:hidden">*</span><?php print $xxState?>:</strong></td><td class="cobll"><select name="state" id="state" size="1" onchange="dosavestate('')"><?php $havestate = show_states($addState) ?></select></td></tr>
		<?php	}
			if($nonhomecountries != 0){ ?>
		<tr><td align="right" class="cobhl"><strong><span id="outspan" style="color:#FF0000;visibility:hidden">*</span><?php print $xxNonState?>:</strong></td><td class="cobll"><input type="text" name="state2" id="state2" size="20" value="<?php if(! $havestate) print htmlspecials($addState)?>" /></td></tr>
		<?php	} ?>
		<tr><td align="right" class="cobhl"><strong><?php print $redasterix.$xxCountry?>:</strong></td><td class="cobll"><select name="country" id="country" size="1" onchange="checkoutspan('')" ><?php show_countries($addCountry) ?></select></td></tr>
		<tr><td align="right" class="cobhl"><strong><?php if(@$zipoptional!=TRUE) print $redasterix; print $xxZip?>:</strong></td><td class="cobll"><input type="text" name="zip" id="zip" size="10" value="<?php print htmlspecials($addZip)?>" /></td></tr>
		<tr><td align="right" class="cobhl"><strong><?php print $redasterix.$xxPhone?>:</strong></td><td class="cobll"><input type="text" name="phone" id="phone" size="20" value="<?php print htmlspecials($addPhone)?>" /></td></tr>
		<?php	if(trim(@$extraorderfield2) != ''){ ?>
		<tr><td align="right" class="cobhl"><strong><?php print (@$extraorderfield2required==true ? $redasterix : '') . $extraorderfield2 ?>:</strong></td><td class="cobll"><?php if(@$extraorderfield2html != '') print $extraorderfield2html; else print '<input type="text" name="ordextra2" id="ordextra2" size="20" value="' . htmlspecials($addExtra2) . '" />'?></td></tr>
		<?php	} ?>
		<tr><td align="center" colspan="2" class="cobll"><?php print imageorsubmit(@$imgsubmit,$xxSubmt,'').' '.imageorbutton(@$imgcancel,$xxCancel,'','history.go(-1)',TRUE)?></td></tr>
	  </table>
	</form>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
var checkedfullname=false;
var numhomecountries=0,nonhomecountries=0;
function checkform(frm)
{
<?php if(trim(@$extraorderfield1) != '' && @$extraorderfield1required==true){ ?>
if(frm.ordextra1.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $extraorderfield1?>\".");
	frm.ordextra1.focus();
	return (false);
}
<?php } ?>
if(frm.name.value==""||frm.name.value=="<?php print $xxFirNam?>"){
	alert("<?php print $xxPlsEntr?> \"<?php print (@$usefirstlastname ? $xxFirNam : $xxName)?>\".");
	frm.name.focus();
	return (false);
}
<?php	if(@$usefirstlastname){ ?>
if(frm.lastname.value==""||frm.lastname.value=="<?php print $xxLasNam?>"){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxLasNam?>\".");
	frm.lastname.focus();
	return (false);
}
<?php	}else{ ?>
gotspace=false;
var checkStr = frm.name.value;
for (i = 0; i < checkStr.length; i++){
	if(checkStr.charAt(i)==" ")
		gotspace=true;
}
if(!checkedfullname && !gotspace){
	alert("<?php print $xxFulNam?> \"<?php print $xxName?>\".");
	frm.name.focus();
	checkedfullname=true;
	return (false);
}
<?php	} ?>
if(frm.address.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxAddress?>\".");
	frm.address.focus();
	return (false);
}
if(frm.city.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxCity?>\".");
	frm.city.focus();
	return (false);
}
if(frm.country.selectedIndex < numhomecountries){
<?php	if($numallstates>0 && $xxOutState != ''){ ?>
	if(frm.state.selectedIndex==0){
		alert("<?php print $xxPlsSlct . " " . $xxState?>.");
		frm.state.focus();
		return (false);
	}
<?php	} ?>
}else{
<?php	if($nonhomecountries>0){ ?>
	if(frm.state2.value==""){
		alert("<?php print $xxPlsEntr?> \"<?php print str_replace('<br />',' ',$xxNonState)?>\".");
		frm.state2.focus();
		return (false);
	}
<?php	} ?>}
if(frm.zip.value==""<?php if(@$zipoptional==TRUE) print ' && false'?>){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxZip?>\".");
	frm.zip.focus();
	return (false);
}
if(frm.phone.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxPhone?>\".");
	frm.phone.focus();
	return (false);
}
<?php if(trim(@$extraorderfield2) != '' && @$extraorderfield2required==TRUE){ ?>
if(frm.ordextra2.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $extraorderfield2?>\".");
	frm.ordextra2.focus();
	return (false);
}
<?php } ?>
return (true);
}
<?php if(@$termsandconditions==TRUE){ ?>
function showtermsandconds(){
newwin=window.open("termsandconditions.php","Terms","menubar=no, scrollbars=yes, width=420, height=380, directories=no,location=no,resizable=yes,status=no,toolbar=no");
}
<?php } ?>
var savestate=0;
var ssavestate=0;
function dosavestate(shp){
	thestate = eval('document.forms.mainform.'+shp+'state');
	eval(shp+'savestate = thestate.selectedIndex');
}
function checkoutspan(shp){
if(shp=='s' && document.getElementById('saddress').value=="")visib='hidden';else visib='visible';<?php
if($nonhomecountries>0) print "thestyle = document.getElementById(shp+'outspan').style;\r\n";
if($numallstates>0){
	print "theddstyle = document.getElementById(shp+'outspandd').style;\r\n";
	print "thestate = eval('document.forms.mainform.'+shp+'state');\r\n";
} ?>
thecntry = eval('document.forms.mainform.'+shp+'country');
if(thecntry.selectedIndex < numhomecountries){<?php
if($nonhomecountries>0) print "thestyle.visibility='hidden';\r\n";
if($numallstates>0){
	print "theddstyle.visibility=visib;\r\n";
	print "thestate.disabled=false;\r\n";
	print "eval('thestate.selectedIndex='+shp+'savestate');\r\n";
} ?>
}else{<?php
if($nonhomecountries>0) print "thestyle.visibility=visib;\r\n";
if($numallstates>0){ ?>
theddstyle.visibility="hidden";
if(thestate.disabled==false){
thestate.disabled=true;
eval(shp+'savestate = thestate.selectedIndex');
thestate.selectedIndex=0;}
<?php } ?>
}}
<?php
	if($numallstates>0) print "savestate = document.forms.mainform.state.selectedIndex;\r\n";
	print 'numhomecountries=' . $numhomecountries . ";\r\n";
	print "checkoutspan('');\r\n";
?>/* ]]> */
</script>
<?php	}elseif((@$_POST['action']=='createlist' && trim(@$_POST['listname'])!='') || @$_POST['action']=='deletelist' || @$_POST['action']=='deleteaddress' || @$_POST['action']=='doeditaddress' || @$_POST['action']=='donewaddress'){
			$addID = str_replace("'",'',@$_POST['theid']);
			$ordName=unstripslashes(@$_POST['name']);
			$ordLastName=unstripslashes(@$_POST['lastname']);
			$ordAddress=unstripslashes(@$_POST['address']);
			$ordAddress2=unstripslashes(@$_POST['address2']);
			$ordState=unstripslashes(@$_POST['state2']);
			if(trim(@$_POST['state']) != '')
				$ordState = unstripslashes(@$_POST['state']);
			$ordCity=unstripslashes(@$_POST['city']);
			$ordZip=unstripslashes(@$_POST['zip']);
			$ordPhone=unstripslashes(@$_POST['phone']);
			$ordCountry=unstripslashes(@$_POST['country']);
			$ordExtra1=unstripslashes(@$_POST['ordextra1']);
			$ordExtra2=unstripslashes(@$_POST['ordextra2']);
			$headertext='';
			if(@$_POST['action']=='createlist' && @$enablewishlists==TRUE){
				$headertext=$xxLisMan;
				$listaccess = md5(time() . @$_POST['listname'] . $adminSecret);
				$sSQL = "INSERT INTO customerlists (listName,listOwner,listAccess) VALUES ('" . escape_string(unstripslashes(@$_POST['listname'])) . "'," . $_SESSION['clientID'] . ",'" . escape_string($listaccess) . "')";
				mysql_query($sSQL) or print(mysql_error());
			}elseif(@$_POST['action']=='deletelist' && @$enablewishlists==TRUE){
				$headertext=$xxLisMan;
				$sSQL = "DELETE FROM customerlists WHERE listID=" . $addID . " AND listOwner=" . $_SESSION['clientID'];
				mysql_query($sSQL) or print(mysql_error());
				$sSQL = "DELETE FROM cart WHERE cartListID=" . $addID . " AND cartClientID=" . $_SESSION['clientID'];
				mysql_query($sSQL) or print(mysql_error());
			}elseif(@$_POST['action']=='deleteaddress'){
				$headertext=$xxAddMan;
				$sSQL = "DELETE FROM address WHERE addID=" . $addID . " AND addCustID=" . $_SESSION['clientID'];
				mysql_query($sSQL) or print(mysql_error());
			}elseif(@$_POST['action']=='donewaddress'){
				$headertext=$xxAddMan;
				$sSQL = "INSERT INTO address (addCustID,addIsDefault,addName,addLastName,addAddress,addAddress2,addCity,addState,addZip,addCountry,addPhone,addExtra1,addExtra2) VALUES (" . $_SESSION['clientID'] . ",0,'" . escape_string($ordName) . "','" . escape_string($ordLastName) . "','" . escape_string($ordAddress) . "','" . escape_string($ordAddress2) . "','" . escape_string($ordCity) . "','" . escape_string($ordState) . "','" . escape_string($ordZip) . "','" . escape_string($ordCountry) . "','" . escape_string($ordPhone) . "','" . escape_string($ordExtra1) . "','" . escape_string($ordExtra2) . "')";
				mysql_query($sSQL) or print(mysql_error());
			}elseif(@$_POST['action']=='doeditaddress'){
				$headertext=$xxAddMan;
				$sSQL = "UPDATE address SET addName='" . escape_string($ordName) . "',addLastName='" . escape_string($ordLastName) . "',addAddress='" . escape_string($ordAddress) . "',addAddress2='" . escape_string($ordAddress2) . "',addCity='" . escape_string($ordCity) . "',addState='" . escape_string($ordState) . "',addZip='" . escape_string($ordZip) . "',addCountry='" . escape_string($ordCountry) . "',addPhone='" . escape_string($ordPhone) . "',addExtra1='" . escape_string($ordExtra1) . "',addExtra2='" . escape_string($ordExtra2) . "' WHERE addCustID=" . $_SESSION['clientID'] . " AND addID=" . $addID;
				mysql_query($sSQL) or print(mysql_error());
			}
			print '<meta http-equiv="Refresh" content="2; URL=' . $_SERVER['PHP_SELF'] . '">';
?>	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr>
          <td class="cobll" width="100%" align="center">
			<p><br /><br /><strong><?php print $headertext?></strong><br /><br /></p>
			<p><br /><?php print $xxUpdSuc?><br /><br /><br /><br /></p>
		  </td>
        </tr>
	  </table>
<?php	}else{ ?>
		  <form method="post" name="mainform" action="<?php print $thisaction?>">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="action" value="none" />
			<input type="hidden" name="theid" value="" />
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
              <tr> 
                <td class="cobhl" align="center" height="34"><a name="acct"><strong><?php print $xxAccDet?></strong></a></td>
			  </tr>
			  <tr> 
                <td class="cobll" height="34" align="center">
				  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php		$sSQL = "SELECT clID,clUserName,clActions,clLoginLevel,clPercentDiscount,clEmail,loyaltyPoints FROM customerlogin WHERE clID=" . $_SESSION['clientID'];
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){ $theemail=$rs['clEmail']; $loyaltypointtotal=$rs['loyaltyPoints']; } else $theemail='ACCOUNT DELETED';
			mysql_free_result($result);
			$sSQL = "SELECT email,isconfirmed FROM mailinglist WHERE email='" . escape_string($theemail) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){ $allowemail=1; $isconfirmed=$rs['isconfirmed']; }else{ $allowemail=0; $isconfirmed=FALSE; }
			mysql_free_result($result);
?>
					<tr><td class="cobhl" align="right" width="<?php print (@$nounsubscribe?'50':'20')?>%"><strong><?php print $xxName?>:</strong></td>
					<td class="cobll" align="left" width="<?php print (@$nounsubscribe?'50':'30')?>%"><?php print htmlspecials($_SESSION['clientUser'])?></td>
<?php		if(@$nounsubscribe!=TRUE){ ?>
					<td class="cobll" align="right" width="8%" rowspan="<?php print (@$loyaltypoints!=''?3:2)?>"><?php if(@$noconfirmationemail!=TRUE && $allowemail!=0 && $isconfirmed==0) print $xxWaiCon; else print '<input type="checkbox" name="allowemail" value="ON"' . ($allowemail!=0 ? ' checked="checked"' : '') . ' disabled="disabled" />'; ?></td>
					<td class="cobhl" align="left" rowspan="<?php print (@$loyaltypoints!=''?3:2)?>"><strong><?php print $xxAlPrEm?></strong><br />
					<span style="font-size:10px"><?php print $xxNevDiv?></span></td>
<?php		} ?>
					</tr><tr><td class="cobhl" align="right"><strong><?php print $xxEmail?>:</strong></td>
					<td class="cobll" align="left"><?php print $theemail?></td>
					</tr>
<?php		if(@$loyaltypoints!=''){ ?>
					<tr><td class="cobhl" align="right"><strong><?php print $xxLoyPoi?>:</strong></td>
					<td class="cobll" align="left"><?php print $loyaltypointtotal?></td>
					</tr>
<?php		} ?>
					<tr><td class="cobll" align="left" colspan="<?php print (@$nounsubscribe?'2':'4')?>"><br /><ul><li><?php print $xxChaAcc?> <a class="ectlink" href="javascript:editaccount()"><strong><?php print $xxClkHere?></strong></a>.</li></ul></td>
					</tr>
				  </table>
				</td>
			  </tr>
<?php		// Address Management
?>              <tr> 
                <td class="cobhl" align="center" height="34"><a name="add"><strong><?php print $xxAddMan?></strong></a></td>
			  </tr>
			  <tr> 
                <td class="cobll" height="34" align="center">
				  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php		$sSQL = "SELECT addID,addIsDefault,addName,addLastName,addAddress,addAddress2,addState,addCity,addZip,addPhone,addCountry FROM address WHERE addCustID=" . $_SESSION['clientID'] . " ORDER BY addIsDefault";
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0){
				while($rs = mysql_fetch_assoc($result)){
					print '<tr><td width="50%" class="cobll" align="left">' . htmlspecials(trim($rs['addName'].' '.$rs['addLastName'])) . "<br />" . htmlspecials($rs['addAddress']) . (trim($rs['addAddress2']) != '' ? '<br />' . htmlspecials($rs['addAddress2']) : '') . "<br /> " . htmlspecials($rs['addCity']) . ", " . htmlspecials($rs['addState']) . ($rs['addZip'] != '' ? '<br />' . htmlspecials($rs['addZip']) : '') . '<br />' . htmlspecials($rs['addCountry']) . '</td>';
					print '<td class="cobhl" align="left"><ul><li><a class="ectlink" href="javascript:editaddress(' . $rs['addID'] . ')">' . $xxEdAdd . '</a><br /><br /></li><li><a class="ectlink" href="javascript:deleteaddress(' . $rs['addID'] . ')">' . $xxDeAdd . '</a></li></ul></td></tr>';
				}
			}else{
				print '<tr><td class="cobll" align="center" colspan="2" height="34">' . $xxNoAdd . '</td></tr>';
			}
			mysql_free_result($result);
?>
					<tr><td class="cobhl" colspan="2" align="left"><br /><ul><li><?php print $xxPCAdd?> <a class="ectlink" href="javascript:newaddress()"><strong><?php print $xxClkHere?></strong></a>.</li></ul></td></tr>
				  </table>
				</td>
			  </tr>
<?php		// Gift Registry Management
			if(@$enablewishlists==TRUE){
?>			  <tr>
                <td class="cobhl" align="center" height="34"><a name="list"><strong><?php print $xxLisMan?></strong></a></td>
			  </tr>
			  <tr> 
                <td class="cobll" height="34" align="center">
				  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php			$sSQL = "SELECT listID,listName,listAccess FROM customerlists WHERE listOwner=" . $_SESSION['clientID'] . " ORDER BY listName";
				$result = mysql_query($sSQL) or print(mysql_error());
				if(mysql_num_rows($result)>0){
					while($rs = mysql_fetch_assoc($result)){
						$numitems=0;
						$sSQL = "SELECT COUNT(*) AS numitems FROM cart WHERE cartListID=" . $rs['listID'];
						$result2 = mysql_query($sSQL) or print(mysql_error());
						if($rs2 = mysql_fetch_assoc($result2))
							if(! is_null($rs2['numitems'])) $numitems=$rs2['numitems'];
						mysql_free_result($result2);
						print '<tr><td width="50%" class="cobll" align="left">' . htmlspecials(trim($rs['listName'])) . ' (' . $numitems . ')<br /></td>';
						print '<td class="cobhl" align="left"><ul><li><a class="ectlink" href="javascript:deletelist(' . $rs['listID'] . ')">' . $xxDelGRe . '</a></li>';
						if($numitems>0) print '<li><a href="cart.php?mode=sc&lid=' . $rs['listID'] . '">' . $xxVieGRe . '</a></li>';
						print '</ul></td></tr>';
						print '<tr><td colspan="2" class="cobll" align="left">' . $xxPubAcc . ':<br />' . $storeurl . 'cart.php?pli=' . $rs['listID'] . '&pla=' . $rs['listAccess'] . '</td></tr>';
					}
				}else
					print '<tr><td class="cobll" align="center" colspan="2" height="34">' . $xxNoGRe . '</td></tr>';
				mysql_free_result($result);
?>
					<tr><td class="cobhl" align="right" width="50%"><input type="text" name="listname" size="40" maxlength="50" /></td><td class="cobhl"><?php print imageorbutton(@$imgcreatelist,'Create New List','','createlist()',TRUE)?></td></tr>
				  </table>
				</td>
			  </tr>
<?php		}
			// Order Management
?>			  <tr> 
                <td class="cobhl" align="center" height="34"><a name="ord"><strong><?php print $xxOrdMan?></strong></a></td>
			  </tr>
			  <tr> 
                <td class="cobll" height="34" align="center">
				  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php		$hastracknum=FALSE;
			$sSQL = "SELECT ordID FROM orders WHERE ordClientID=" . $_SESSION['clientID'] . " AND ordTrackNum<>''";
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0) $hastracknum=TRUE;
			mysql_free_result($result); ?>
					<tr><td class="cobhl"><?php print $xxOrdId?></td>
					<td class="cobhl"><?php print $xxDate?></td>
					<td class="cobhl"><?php print $xxStatus?></td>
<?php		if($hastracknum) print '<td class="cobhl">' . $xxTraNum . '</td>'; ?>
					<td class="cobhl"><?php print $xxGndTot?></td>
					<td class="cobhl"><?php print $xxCODets?></td></tr>			
<?php
			$sSQL = "SELECT ordID,ordDate,ordTrackNum,ordTotal,ordStateTax,ordCountryTax,ordShipping,ordHSTTax,ordHandling,ordDiscount," . getlangid('statPublic',64) . " FROM orders LEFT OUTER JOIN orderstatus ON orders.ordStatus=orderstatus.statID WHERE ordClientID=" . $_SESSION['clientID'] . " ORDER BY ordDate";
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0){
				while($rs = mysql_fetch_assoc($result)){
					print '<tr><td class="cobll">' . $rs['ordID'] . '</td>';
					print '<td class="cobll">' . date($dateformatstr, strtotime($rs['ordDate'])) . '</td>';
					print '<td class="cobll">' . $rs[getlangid("statPublic",64)] . '</td>';
					if($hastracknum) print '<td class="cobll">' . ($rs['ordTrackNum']!=''?$rs['ordTrackNum']:'&nbsp;') . '</td>';
					print '<td class="cobll">' . FormatEuroCurrency(($rs['ordTotal']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordShipping']+$rs['ordHSTTax']+$rs['ordHandling'])-$rs['ordDiscount']) . '</td>';
					print '<td class="cobll"><a class="ectlink" href="javascript:vieworder(' . $rs['ordID'] . ')">' . $xxClkHere . '</a></td></tr>';
				}
			}else{
				print '<tr><td class="cobll" colspan="5" height="34" align="center">' . $xxNoOrd . '</td></tr>';
			}
			mysql_free_result($result);
?>
				  </table>
				</td>
			  </tr>
			</table>
		  </form>
<?php	}
	} ?>
