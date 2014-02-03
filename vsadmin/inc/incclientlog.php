<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=='') $storesessionvalue='virtualstore'.time();
if($_SESSION['loggedon'] != $storesessionvalue || @$disallowlogin==TRUE) exit;
$success=TRUE;
if(@$dateadjust=='') $dateadjust=0;
if(@$dateformatstr=='') $dateformatstr = 'm/d/Y';
$admindatestr='Y-m-d';
if(@$admindateformat=='') $admindateformat=0;
if($admindateformat==1)
	$admindatestr='m/d/Y';
elseif($admindateformat==2)
	$admindatestr='d/m/Y';
$alreadygotadmin = getadminsettings();
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
		print '<option value="' . htmlspecials($allcountries[$index]["countryName"]) . '"';
		if($tcountry==$allcountries[$index]["countryName"]) print ' selected="selected"';
		print '>' . $allcountries[$index][2] . "</option>\n";
	}
}
$sSQL = '';
$alldata='';
$dorefresh=FALSE;
if(@$maxloginlevels=='') $maxloginlevels=5;
if(@$_POST['posted']=='1'){
	if(@$_POST['act']=='delete'){
		$sSQL = "DELETE FROM customerlogin WHERE clID='" . @$_POST['id'] . "'";
		mysql_query($sSQL) or print(mysql_error());
		$sSQL = "DELETE FROM address WHERE addCustID='" . @$_POST['id'] . "'";
		mysql_query($sSQL) or print(mysql_error());
		$sSQL = "UPDATE orders SET ordClientID=0 WHERE ordClientID='" . @$_POST['id'] . "'";
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}elseif(@$_POST['act']=='deleteaddress'){
		$sSQL = "DELETE FROM address WHERE addID='" . @$_POST['theid'] . "'";
		mysql_query($sSQL) or print(mysql_error());
	}elseif(@$_POST['act']=='doeditaddress' || @$_POST['act']=='donewaddress'){
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
		if(@$_POST['act']=='doeditaddress')
			$sSQL = "UPDATE address SET addName='" . escape_string($ordName) . "',addLastName='" . escape_string($ordLastName) . "',addAddress='" . escape_string($ordAddress) . "',addAddress2='" . escape_string($ordAddress2) . "',addCity='" . escape_string($ordCity) . "',addState='" . escape_string($ordState) . "',addZip='" . escape_string($ordZip) . "',addCountry='" . escape_string($ordCountry) . "',addPhone='" . escape_string($ordPhone) . "',addExtra1='" . escape_string($ordExtra1) . "',addExtra2='" . escape_string($ordExtra2) . "' WHERE addID=" . $addID;
		else
			$sSQL = "INSERT INTO address (addCustID,addIsDefault,addName,addLastName,addAddress,addAddress2,addCity,addState,addZip,addCountry,addPhone,addExtra1,addExtra2) VALUES (" . @$_POST['id'] . ",0,'" . escape_string($ordName) . "','" . escape_string($ordLastName) . "','" . escape_string($ordAddress) . "','" . escape_string($ordAddress2) . "','" . escape_string($ordCity) . "','" . escape_string($ordState) . "','" . escape_string($ordZip) . "','" . escape_string($ordCountry) . "','" . escape_string($ordPhone) . "','" . escape_string($ordExtra1) . "','" . escape_string($ordExtra2) . "')";
		mysql_query($sSQL) or print(mysql_error());
	}elseif(@$_POST['act']=='domodify'){
		$sSQL = "SELECT clEmail FROM customerlogin WHERE clID<>'" . @$_POST['id'] . "' AND clEmail='" . escape_string(unstripslashes(@$_POST['clEmail'])) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_array($result)){
			$success=FALSE;
			$errmsg=$yyEmReg . '<br />' . htmlspecials(@$_POST['clEmail']);
		}
		mysql_free_result($result);
		if(trim(@$_POST['clUserName'])==''){
			$success=FALSE;
			$errmsg='Username is NULL';
		}
		if($success){
			$sSQL = "UPDATE customerlogin SET clUserName='" . escape_string(unstripslashes(@$_POST['clUserName'])) . "'";
			if(trim(@$_POST['clPW'])!='') $sSQL .= ",clPW='" . escape_string(dohashpw(unstripslashes(@$_POST['clPW']))) . "'";
			$sSQL .= ",clLoginLevel=" . @$_POST['clLoginLevel'];
			$sSQL .= ",loyaltyPoints=" . (is_numeric(@$_POST['loyaltyPoints'])?$_POST['loyaltyPoints']:0);
			$cpd = trim(@$_POST['clPercentDiscount']);
			$sSQL .= ",clPercentDiscount=" . (is_numeric($cpd) ? $cpd : 0);
			$sSQL .= ",clEmail='" . escape_string(unstripslashes(@$_POST['clEmail'])) . "'";
			$clActions=0;
			if(is_array(@$_POST['clActions'])){
				foreach(@$_POST['clActions'] as $objValue){
					if(is_array($objValue)) $objValue = $objValue[0];
					$clActions += $objValue;
				}
			}
			$sSQL .= ",clActions=" . $clActions;
			$sSQL .= " WHERE clID='" . @$_POST['id'] . "'";
			mysql_query($sSQL) or print(mysql_error());
			if(@$_POST['clAllowEmail']=='ON')
				@mysql_query("INSERT INTO mailinglist (email,isconfirmed,mlConfirmDate,mlIPAddress) VALUES ('" . escape_string(strtolower(unstripslashes(@$_POST['clEmail']))) . "',1,'".date('Y-m-d', time())."','".escape_string(getipaddress())."')");
			else
				mysql_query("DELETE FROM mailinglist WHERE email='" . escape_string(unstripslashes(@$_POST['clEmail'])) . "'");
			$dorefresh=TRUE;
		}
	}elseif(@$_POST['act']=='doaddnew'){
		$sSQL = "SELECT clEmail FROM customerlogin WHERE clEmail='" . escape_string(unstripslashes(@$_POST['clEmail'])) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_array($result)){
			$success=FALSE;
			$errmsg=$yyEmReg . '<br />' . htmlspecials(@$_POST['clEmail']);
		}
		mysql_free_result($result);
		if(trim(@$_POST['clUserName'])==''){
			$success=FALSE;
			$errmsg='Username is NULL';
		}
		if($success){
			$sSQL = "INSERT INTO customerlogin (clUserName,clPW,clLoginLevel,loyaltyPoints,clPercentDiscount,clEmail,clDateCreated,clActions) VALUES (";
			$sSQL .= "'" . escape_string(unstripslashes(@$_POST['clUserName'])) . "'";
			$sSQL .= ",'" . escape_string(dohashpw(unstripslashes(@$_POST['clPW']))) . "'";
			$sSQL .= "," . @$_POST['clLoginLevel'];
			$sSQL .= "," . (is_numeric(@$_POST['loyaltyPoints'])?$_POST['loyaltyPoints']:0);
			$cpd = trim(@$_POST['clPercentDiscount']);
			$sSQL .= "," . (is_numeric($cpd) ? $cpd : 0);
			$sSQL .= ",'" . escape_string(unstripslashes(@$_POST['clEmail'])) . "'";
			$sSQL .= ",'" . date('Y-m-d', time() + ($dateadjust*60*60)) . "'";
			$clActions=0;
			if(is_array(@$_POST['clActions'])){
				foreach(@$_POST['clActions'] as $objValue){
					if(is_array($objValue)) $objValue = $objValue[0];
					$clActions += $objValue;
				}
			}
			$sSQL .= ',' . $clActions . ')';
			mysql_query($sSQL) or print(mysql_error());
			if(@$_POST['clAllowEmail']=='ON')
				mysql_query("INSERT INTO mailinglist (email,isconfirmed,mlConfirmDate,mlIPAddress) VALUES ('" . escape_string(strtolower(unstripslashes(@$_POST['clEmail']))) . "',1,'".date('Y-m-d', time())."','".escape_string(getipaddress())."')");
			else
				mysql_query("DELETE FROM mailinglist WHERE email='" . escape_string(@$_POST['clEmail']) . "'");
			$dorefresh=TRUE;
		}
	}elseif(@$_POST['act']=='addorphans'){
		$sSQL = "SELECT clEmail FROM customerlogin WHERE clID='" . @$_POST['id'] . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)){
			$theemail = $rs['clEmail'];
		}
		mysql_free_result($result);
		if(@$loyaltypoints!=''){
			$loyaltypointtotal=0;
			$sSQL = "SELECT SUM(loyaltyPoints) AS pointsSum FROM orders WHERE ordClientID=0 AND ordEmail='" . escape_string($theemail) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result))
				if($rs['pointsSum']!=NULL) $loyaltypointtotal = $rs['pointsSum'];
			mysql_free_result($result);
			$sSQL = "UPDATE customerlogin SET loyaltyPoints=loyaltyPoints+" . $loyaltypointtotal . " WHERE clID='" . escape_string(@$_POST['id']) . "'";
			mysql_query($sSQL) or print(mysql_error());
		}
		$sSQL = "UPDATE orders SET ordClientID='".escape_string(@$_POST['id'])."' WHERE ordEmail='" . escape_string($theemail) . "'";
		mysql_query($sSQL) or print(mysql_error());
	}elseif(@$_POST['act']=='addorphan'){
		if(@$loyaltypoints!=''){
			$loyaltypointtotal=0;
			$sSQL = "SELECT loyaltyPoints FROM orders WHERE ordClientID=0 AND ordID='" . escape_string(@$_POST['theid']) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result))
				$loyaltypointtotal = $rs['loyaltyPoints'];
			mysql_free_result($result);
			$sSQL = "UPDATE customerlogin SET loyaltyPoints=loyaltyPoints+" . $loyaltypointtotal . " WHERE clID='" . escape_string(@$_POST['id']) . "'";
			mysql_query($sSQL) or print(mysql_error());
		}
		$sSQL = "UPDATE orders SET ordClientID='".escape_string(@$_POST['id'])."' WHERE ordID='" . escape_string(@$_POST['theid']) . "'";
		mysql_query($sSQL) or print(mysql_error());
	}
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=adminclientlog.php';
	print '?stext=' . urlencode(@$_POST['stext']) . '&accdate=' . urlencode(@$_POST['accdate']) . '&slevel=' . urlencode(@$_POST['slevel']) . '&stype=' . urlencode(@$_POST['stype']) . '&daterange=' . urlencode(@$_POST['daterange']) . '&pg=' . urlencode(@$_POST['pg']);
	print '">';
}
?>
<script language="javascript" type="text/javascript">
<!--
function formvalidator(theForm){
if (theForm.clUserName.value == ""){
alert("<?php print $yyPlsEntr?> \"<?php print $yyLiName?>\".");
theForm.clUserName.focus();
return (false);
}
return (true);
}
function vieworder(theid){
	document.location="adminorders.php?id="+theid;
}
function editaddress(theid){
	document.forms.mainform.act.value="editaddress";
	document.forms.mainform.theid.value=theid;
	document.forms.mainform.submit();
}
function newaddress(){
	document.forms.mainform.act.value="newaddress";
	document.forms.mainform.submit();
}
function editaccount(){
	document.forms.mainform.act.value="modify";
	document.forms.mainform.submit();
}
function addorphan(theid){
	if(confirm("<?php print $yySureCa?>")){
		document.forms.mainform.act.value="addorphan";
		document.forms.mainform.theid.value=theid;
		document.forms.mainform.submit();
	}
}
function addorphans(){
	if(confirm("<?php print $yySureCa?>")){
		document.forms.mainform.act.value="addorphans";
		document.forms.mainform.submit();
	}
}
function deleteaddress(theid){
	if(confirm("<?php print $xxDelAdd?>")){
		document.forms.mainform.act.value="deleteaddress";
		document.forms.mainform.theid.value=theid;
		document.forms.mainform.submit();
	}
}
//-->
</script>
<?php	if(@$_POST['posted']=='1' && (@$_POST['act']=='modify' || @$_POST['act']=='addnew')){
			if($_POST['act']=='modify'){
				$sSQL = "SELECT clUserName,clPW,clLoginLevel,clActions,clPercentDiscount,clEmail,clDateCreated,loyaltyPoints FROM customerlogin WHERE clID='" . @$_POST["id"] . "'";
				$result = mysql_query($sSQL) or print(mysql_error());
				$rs = mysql_fetch_array($result);
				$clUserName=$rs['clUserName'];
				$clPW='';
				$clLoginLevel=$rs['clLoginLevel'];
				$clActions=$rs['clActions'];
				$clPercentDiscount=$rs['clPercentDiscount'];
				$clEmail=$rs['clEmail'];
				$clDateCreated=$rs['clDateCreated'];
				$clLoyaltyPoints=$rs['loyaltyPoints'];
				mysql_free_result($result);
				$sSQL = "SELECT email FROM mailinglist WHERE email='" . escape_string($clEmail) . "'";
				$result = mysql_query($sSQL) or print(mysql_error());
				if(mysql_num_rows($result)>0) $clAllowEmail=1; else $clAllowEmail=0;
				mysql_free_result($result);
			}else{
				$clUserName='';
				$clPW='';
				$clLoginLevel=0;
				$clActions=0;
				$clPercentDiscount=0;
				$clEmail='';
				$clDateCreated=date('Y-m-d');
				$clAllowEmail=0;
				$clLoyaltyPoints=0;
			}
?>
	<form name="mainform" method="post" action="adminclientlog.php" onsubmit="return formvalidator(this)">
<?php		writehiddenvar('posted', '1');
			if($_POST['act']=='modify')
				writehiddenvar('act', 'domodify');
			else
				writehiddenvar('act', 'doaddnew');
			writehiddenvar('stext', @$_POST['stext']);
			writehiddenvar('accdate', @$_POST['accdate']);
			writehiddenvar('daterange', @$_POST['daterange']);
			writehiddenvar('slevel', @$_POST['slevel']);
			writehiddenvar('stype', @$_POST['stype']);
			writehiddenvar('pg', @$_POST['pg']);
			writehiddenvar('id', @$_POST['id']); ?>
            <table width="100%" border="0" cellspacing="2" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="4" align="center"><strong><?php print $yyLiAdm?></strong><br /><br /><?php print '<strong>' . $yyDateCr. ':</strong> ' . date($admindatestr, strtotime($clDateCreated)); ?><br /><br /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyLiName?>:</strong></td>
				<td align="left"><input type="text" name="clUserName" size="20" value="<?php print htmlspecials($clUserName)?>" /></td>
				<td align="right" rowspan="7" valign="top"><strong><?php print $yyActns?>:</strong></td>
				<td rowspan="7" align="left" valign="top"><select name="clActions[]" size="6" multiple="multiple">
				<option value="1"<?php if(($clActions & 1) == 1) print ' selected="selected"' ?>><?php print $yyExStat?></option>
				<option value="2"<?php if(($clActions & 2) == 2) print ' selected="selected"' ?>><?php print $yyExCoun?></option>
				<option value="4"<?php if(($clActions & 4) == 4) print ' selected="selected"' ?>><?php print $yyExShip?></option>
				<option value="8"<?php if(($clActions & 8) == 8) print ' selected="selected"' ?>><?php print $yyWholPr?></option>
				<option value="16"<?php if(($clActions & 16) == 16) print ' selected="selected"' ?>><?php print $yyPerDis?></option>
				</select></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyEmail?>:</strong></td>
				<td align="left"><input type="text" name="clEmail" size="20" value="<?php print htmlspecials($clEmail)?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyReset.' '.$yyPass?>:</strong></td>
				<td align="left"><input type="text" name="clPW" size="20" value="" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyLiLev?>:</strong></td>
				<td align="left"><select name="clLoginLevel" size="1">
				<?php	for($rowcounter=0; $rowcounter<=$maxloginlevels; $rowcounter++){
							print '<option value="' . $rowcounter . '"';
							if($rowcounter==(int)$clLoginLevel) print ' selected="selected"';
							print '>&nbsp; ' . $rowcounter . " </option>\r\n";
						} ?>
				</select></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyPerDis?>:</strong></td>
				<td align="left"><input type="text" name="clPercentDiscount" size="10" value="<?php print $clPercentDiscount?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyAllEml?>:</strong></td>
				<td align="left"><input type="checkbox" name="clAllowEmail" value="ON"<?php if($clAllowEmail!=0) print ' checked'?> /></td>
			  </tr>
<?php		if(@$loyaltypoints!=''){ ?>
			  <tr>
				<td align="right" height="22"><strong><?php print $xxLoyPoi?>:</strong></td>
				<td align="left"><input type="text" name="loyaltyPoints" size="10" value="<?php print $clLoyaltyPoints?>" /></td>
			  </tr>
<?php		} ?>
			  <tr>
                <td width="100%" colspan="4" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" />&nbsp;<input type="reset" value="<?php print $yyReset?>" /><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
	</form>
<?php	}elseif(@$_POST['act']=='editaddress' || @$_POST['act']=='newaddress'){
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
			if(@$_POST['act']=='editaddress'){
				$sSQL = "SELECT addID,addIsDefault,addName,addLastName,addAddress,addAddress2,addState,addCity,addZip,addPhone,addCountry,addExtra1,addExtra2 FROM address WHERE addID=" . $addID;
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
	<form method="post" name="mainform" action="" onsubmit="return checkform(this)">
	<input type="hidden" name="act" value="<?php if(@$_POST['act']=='editaddress') print 'doeditaddress'; else print 'donewaddress' ?>" />
	<input type="hidden" name="theid" value="<?php print $addID?>" />
	<input type="hidden" name="id" value="<?php print @$_POST['id']?>" />
	<input type="hidden" name="posted" value="1" />
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
		<tr><td align="center" colspan="2" class="cobll"><input type="submit" value="<?php print $xxSubmt?>" /> <input type="button" value="Cancel" onclick="history.go(-1)" /></td></tr>
	  </table>
	</form>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
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
	print "numhomecountries=" . $numhomecountries . ";\r\n";
	print "checkoutspan('');\r\n";
?>/* ]]> */
</script>
<?php	}elseif((@$_POST['act']=='viewacct' || @$_POST['act']=='deleteaddress' || @$_POST['act']=='addorphans' || @$_POST['act']=='addorphan') AND is_numeric(@$_POST['id'])){
			$clID = @$_POST['id'];
			$sSQL = "SELECT clUserName,clPW,clLoginLevel,clActions,clPercentDiscount,clEmail,clDateCreated,loyaltyPoints FROM customerlogin WHERE clID='" . $clID . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			$rs = mysql_fetch_array($result);
			$clUserName=$rs['clUserName'];
			$clPW=$rs['clPW'];
			$clLoginLevel=$rs['clLoginLevel'];
			$clActions=$rs['clActions'];
			$clPercentDiscount=$rs['clPercentDiscount'];
			$clEmail=$rs['clEmail'];
			$clDateCreated=$rs['clDateCreated'];
			$clLoyaltyPoints=$rs['loyaltyPoints'];
			mysql_free_result($result);
			$sSQL = "SELECT email FROM mailinglist WHERE email='" . escape_string($clEmail) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0) $clAllowEmail=1; else $clAllowEmail=0;
			mysql_free_result($result);
			$ordersnotinacct=0;
			$sSQL = "SELECT COUNT(*) AS thecnt FROM orders WHERE ordClientID=0 AND ordEmail='" . escape_string($clEmail) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				if(! is_null($rs['thecnt'])) $ordersnotinacct=$rs['thecnt'];
			}
			mysql_free_result($result); ?>
		  <form method="post" name="mainform" action="">
<?php		writehiddenvar('posted', '1');
			writehiddenvar('act', 'none');
			writehiddenvar('theid', '');
			writehiddenvar('stext', @$_POST['stext']);
			writehiddenvar('accdate', @$_POST['accdate']);
			writehiddenvar('daterange', @$_POST['daterange']);
			writehiddenvar('slevel', @$_POST['slevel']);
			writehiddenvar('stype', @$_POST['stype']);
			writehiddenvar('pg', @$_POST['pg']);
			writehiddenvar('id', $clID); ?>
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
              <tr> 
                <td class="cobhl" align="center" height="34"><strong><?php print $xxAccDet?></strong></td>
			  </tr>
			  <tr> 
                <td class="cobll" height="34" align="center">
				  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php		$sSQL = "SELECT email,isconfirmed FROM mailinglist WHERE email='" . escape_string($clEmail) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){ $allowemail=1; $isconfirmed=$rs['isconfirmed']; }else{ $allowemail=0; $isconfirmed=FALSE; }
			mysql_free_result($result);
?>
					<tr><td class="cobhl" align="right" width="25%" height="22"><strong><?php print $xxName?>:</strong></td>
					<td class="cobll" align="left" width="25%"><?php print htmlspecials($clUserName)?></td>
					<td class="cobhl" align="right" width="25%"><strong><?php print $yyActns?>:</strong></td>
					<td class="cobll" align="left" width="25%"><?php
						if(($clActions & 1) == 1) print 'STE ';
						if(($clActions & 2) == 2) print 'CTE ';
						if(($clActions & 4) == 4) print 'SHE ';
						if(($clActions & 8) == 8) print 'WSP ';
						if(($clActions & 16) == 16) print 'PED '; ?>&nbsp;</td>
					</tr>
					<tr><td class="cobhl" align="right" height="22"><strong><?php print $xxEmail?>:</strong></td>
					<td class="cobll" align="left"><?php print htmlspecials($clEmail)?></td>
					<td class="cobhl" align="right"><strong><?php print $xxAlPrEm?>:</strong></td>
					<td class="cobll" align="left"><?php if(@$noconfirmationemail!=TRUE && $allowemail!=0 && $isconfirmed==0) print $xxWaiCon; else print '<input type="checkbox" name="allowemail" value="ON"' . ($allowemail!=0 ? ' checked="checked"' : '') . ' disabled="disabled" />'?></td>
					</tr>
					<tr><td class="cobhl" align="right" height="22"><strong><?php print $yyPerDis?>:</strong></td>
					<td class="cobll" align="left"><?php if(($clActions & 16) == 16) print $clPercentDiscount; else print '-';?></td>
					<td class="cobhl" align="right"><strong><?php print $yyLiLev?>:</strong></td>
					<td class="cobll" align="left"><?php print $clLoginLevel?></td>
					</tr>
<?php		if(@$loyaltypoints!=''){ ?>
					<tr><td class="cobhl" align="right" height="22"><strong><?php print $xxLoyPoi?>:</strong></td>
					<td class="cobll" colspan="3" align="left"><?php print $clLoyaltyPoints?></td>
					</tr>
<?php		} ?>
					<tr><td class="cobll" align="left" colspan="4"><br /><ul><li><?php print $xxChaAcc?> <a class="ectlink" href="javascript:editaccount()"><strong><?php print $xxClkHere?></strong></a>.</li>
<?php		if($ordersnotinacct!=0) print '<li>' . $ordersnotinacct . " orders with this email are not registered to the account. To add them all please" . ' <a class="ectlink" href="javascript:addorphans()"><strong>'.$xxClkHere.'</strong></a>.</li>' ?>
					</ul></td>
					</tr>
				  </table>
				</td>
			  </tr>
              <tr> 
                <td class="cobhl" align="center" height="34"><strong><?php print $xxAddMan?></strong></td>
			  </tr>
			  <tr> 
                <td class="cobll" height="34" align="center">
				  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php		$sSQL = "SELECT addID,addIsDefault,addName,addLastName,addAddress,addAddress2,addState,addCity,addZip,addPhone,addCountry FROM address WHERE addCustID=" . $clID . " ORDER BY addIsDefault";
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0){
				while($rs = mysql_fetch_assoc($result)){
					print '<tr><td width="50%" class="cobll" align="left">' . htmlspecials(trim($rs['addName'].' '.$rs['addLastName'])) . '<br />' . htmlspecials($rs['addAddress']) . (trim($rs['addAddress2']) != '' ? '<br />' . htmlspecials($rs['addAddress2']) : '') . '<br />' . htmlspecials($rs['addCity']) . ', ' . htmlspecials($rs['addState']) . ($rs['addZip'] != '' ? '<br />' . htmlspecials($rs['addZip']) : '') . '<br />' . htmlspecials($rs['addCountry']) . '</td>';
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
			  <tr> 
                <td class="cobhl" align="center" height="34"><strong><?php print $xxOrdMan?></strong></td>
			  </tr>
			  <tr> 
                <td class="cobll" height="34" align="center">
				  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php		$hastracknum=FALSE;
			$sSQL = "SELECT ordID FROM orders WHERE ordClientID=" . $clID . " AND ordTrackNum<>''";
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0) $hastracknum=TRUE;
			mysql_free_result($result);
			$hasorphan=FALSE;
			$sSQL = "SELECT ordID FROM orders WHERE ordClientID=0 AND ordEmail='" . escape_string($clEmail) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0) $hasorphan=TRUE;
			mysql_free_result($result); ?>
					<tr><td class="cobhl"><?php print $xxOrdId?></td>
					<td class="cobhl"><?php print $xxDate?></td>
					<td class="cobhl"><?php print $xxStatus?></td>
<?php		if($hastracknum) print '<td class="cobhl">' . $xxTraNum . '</td>'; ?>
					<td class="cobhl"><?php print $xxGndTot?></td>
<?php		if($hasorphan) print '<td class="cobhl">' . 'Account' . '</td>'; ?>
					<td class="cobhl"><?php print $xxCODets?></td></tr>			
<?php		$grandtotal=0;
			$sSQL = "SELECT ordID,ordDate,ordTrackNum,ordTotal,ordStateTax,ordCountryTax,ordShipping,ordHSTTax,ordHandling,ordDiscount," . getlangid('statPublic',64) . ",ordClientID FROM orders LEFT OUTER JOIN orderstatus ON orders.ordStatus=orderstatus.statID WHERE ordClientID=" . $clID . " OR ordEmail='" . escape_string($clEmail) . "' ORDER BY ordDate";
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0){
				while($rs = mysql_fetch_assoc($result)){
					$subtotal = ($rs['ordTotal']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordShipping']+$rs['ordHSTTax']+$rs['ordHandling'])-$rs['ordDiscount'];
					$grandtotal += $subtotal;
					print '<tr><td class="cobll">' . $rs['ordID'] . '</td>';
					print '<td class="cobll">' . $rs['ordDate'] . '</td>';
					print '<td class="cobll">' . $rs[getlangid("statPublic",64)] . '</td>';
					if($hastracknum) print '<td class="cobll">' . ($rs['ordTrackNum']!=''?$rs['ordTrackNum']:'&nbsp;') . '</td>';
					print '<td class="cobll" align="right">' . FormatEuroCurrency($subtotal) . '&nbsp;</td>';
					if($hasorphan){
						print '<td class="cobll">';
						if($rs['ordClientID']==0) print '<a href="javascript:addorphan('.$rs['ordID'].')">'.'Link to Account'.'</a>'; else print '&nbsp;';
						print '</td>';
					}
					print '<td class="cobll"><a class="ectlink" href="javascript:vieworder(' . $rs['ordID'] . ')">' . $xxClkHere . '</a></td></tr>';
				}
				if($subtotal!=$grandtotal) print '<tr><td class="cobll" colspan="'.($hastracknum ? '4' : '3') . '">&nbsp;</td><td class="cobll" align="right">' . FormatEuroCurrency($grandtotal) . '&nbsp;</td><td class="cobll"'.($hasorphan?' colspan="2"':'').'>&nbsp;</td></tr>';
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
<?php	}elseif(trim(@$_GET['loginas'])!='' && is_numeric(@$_GET['loginas'])){
			$sSQL = "SELECT clID,clUserName,clActions,clLoginLevel,clPercentDiscount FROM customerlogin WHERE clID=".$_GET['loginas'];
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$_SESSION['clientID']=$rs['clID'];
				$_SESSION['clientUser']=$rs['clUserName'];
				$_SESSION['clientActions']=$rs['clActions'];
				$_SESSION['clientLoginLevel']=$rs['clLoginLevel'];
				$_SESSION['clientPercentDiscount']=(100.0-(double)$rs['clPercentDiscount'])/100.0;
				$redirecturl = $storeurl;
				if(@$_SERVER['HTTPS']=='on') $redirecturl=str_replace('http:','https:',$redirecturl);
				header('Location: ' . $redirecturl . 'cart.php');
			}else
				print 'Login not found';
		}elseif(@$_POST['posted']=='1' && $success){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
<?php		if(@$_POST['act']=='doeditaddress' || @$_POST['act']=='donewaddress'){ ?>
					<form action="adminclientlog.php" method="post" id="postform">
					<input type="hidden" name="act" value="viewacct" />
					<input type="hidden" name="id" value="<?php print @$_POST['id']?>" />
					&nbsp;<br />&nbsp;<br />
					<?php print $yyNoAuto?><br />&nbsp;<br />
					<input type="submit" value="<?php print $yyClkHer?>" /><br />&nbsp;<br />&nbsp;
					</form>
<?php			print '<script language="javascript" type="text/javascript">document.getElementById("postform").submit();</script>' . "\r\n";
			}else{ ?>
					<?php print $yyNoAuto?> <a href="adminclientlog.php"><strong><?php print $yyClkHer?></strong></a>.<br />&nbsp;</br />
<?php		} ?>                </td>
			  </tr>
			</table></td>
        </tr>
	  </table>
<?php	}elseif(@$_POST["posted"]=="1"){ ?>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a><br />&nbsp;<br />&nbsp;</td>
			  </tr>
			</table></td>
        </tr>
	  </table>
<?php	}else{ ?>
<script language="javascript" type="text/javascript" src="popcalendar.js">
</script>
<script language="javascript" type="text/javascript">
<!--
function mrec(id){
	document.mainform.id.value = id;
	document.mainform.act.value = "viewacct";
	document.mainform.submit();
}
function newrec(id){
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.submit();
}
function lrec(id){
	window.open('adminclientlog.php?loginas='+id,'clientlogin','menubar=no, scrollbars=yes, width=950, height=700, directories=no,location=no,resizable=yes,status=yes,toolbar=no')
}
function drec(id){
cmsg = "<?php print $yyConDel?>\n"
if (confirm(cmsg)){
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.submit();
}
}
function startsearch(){
	document.mainform.action="adminclientlog.php";
	document.mainform.act.value = "search";
	document.mainform.posted.value = "";
	document.mainform.submit();
}
// -->
</script>
	<form name="mainform" method="post" action="adminclientlog.php">
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		  <td width="100%">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="pg" value="<?php print (@$_POST['act']=='search' ? '1' : @$_GET['pg']) ?>" />
<?php	$themask = 'yyyy-mm-dd';
		if($admindateformat==1)
			$themask='mm/dd/yyyy';
		elseif($admindateformat==2)
			$themask='dd/mm/yyyy';
		$thelevel = @$_REQUEST['slevel'];
		if(@$thelevel != '') $thelevel = (int)$thelevel;
?>			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr> 
                <td class="cobhl" width="25%" align="right"><?php print $yySrchFr?>:</td>
				<td class="cobll" width="25%"><input type="text" name="stext" size="20" value="<?php print htmlspecials(@$_REQUEST['stext'])?>" /></td>
				<td class="cobhl" width="20%" align="right"><?php print $yyDate?>:</td>
				<td class="cobll">
					<select name="daterange" size="1">
					<option value=""><?php print $yySinc?></option>
					<option value="1"<?php if(@$_REQUEST['daterange']=="1") print ' selected="selected"'?>><?php print $yyTill?></option>
					<option value="2"<?php if(@$_REQUEST['daterange']=="2") print ' selected="selected"'?>><?php print $yyOn?></option>
					</select>
					<input type="text" size="14" name="accdate" value="<?php print htmlspecials(@$_REQUEST['accdate'])?>" /> <input type="button" onclick="popUpCalendar(this, document.forms.mainform.accdate, '<?php print $themask?>', -205)" value='DP' />
				</td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="right"><?php print $yySrchTp?>:</td>
				<td class="cobll"><select name="stype" size="1">
					<option value=""><?php print $yySrchAl?></option>
					<option value="any" <?php if(@$_REQUEST['stype']=='any') print 'selected="selected"'?>><?php print $yySrchAn?></option>
					<option value="exact" <?php if(@$_REQUEST['stype']=='exact') print 'selected="selected"'?>><?php print $yySrchEx?></option>
					</select>
				</td>
				<td class="cobhl" align="right"><?php print $yyLiLev?>:</td>
				<td class="cobll">
				  <select name="slevel" size="1">
				  <option value=""><?php print $yyAllLev?></option>
<?php						for($rowcounter=0; $rowcounter <= $maxloginlevels; $rowcounter++){
								print "<option value='" . $rowcounter . "'";
								if($thelevel !== '' && $thelevel !== NULL){
									if($thelevel==$rowcounter) print ' selected="selected"';
								}
								print '>&nbsp; ' . $rowcounter . ' </option>';
							} ?>
				  </select>
				</td>
              </tr>
			  <tr>
				    <td class="cobhl">&nbsp;</td>
				    <td class="cobll" colspan="3"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					    <tr>
						  <td class="cobll" align="center"><input type="button" value="<?php print $yyListRe?>" onclick="startsearch();" />
							<input type="button" value="<?php print $yyCLNew?>" onclick="newrec();" />
						  </td>
						  <td class="cobll" height="26" width="20%" align="right">&nbsp;</td>
						</tr>
					  </table></td>
				  </tr>
			</table>
            <table width="100%" border="0" cellspacing="0" cellpadding="1">
<?php	if(@$_POST['act']=='search' || @$_GET['pg'] != ''){
			function displayprodrow($xrs){
				global $bgcolor,$yyModify,$yyDelete,$yyLogin;
			?><tr class="<?php print $bgcolor?>"><td><?php print htmlspecials($xrs['clUserName'])?></td><td><?php print htmlspecials($xrs['clEmail'])?></td><td align="center"><?php print $xrs['clLoginLevel']?></td>
				<td><?php	if(($xrs['clActions'] & 1) == 1) print 'STE ';
							if(($xrs['clActions'] & 2) == 2) print 'CTE ';
							if(($xrs['clActions'] & 4) == 4) print 'SHE ';
							if(($xrs['clActions'] & 8) == 8) print 'WSP ';
							if(($xrs['clActions'] & 16) == 16) print 'PED ';
				?>&nbsp;</td>
				<td><input type="button" value="<?php print $yyLogin?>" onclick="lrec('<?php print $xrs['clID']?>',event)" /></td>
				<td><input type="button" value="<?php print $yyModify?>" onclick="mrec('<?php print $xrs['clID']?>',event)" /></td>
				<td><input type="button" value="<?php print $yyDelete?>" onclick="drec('<?php print $xrs['clID']?>')" /></td></tr>
<?php		}
			function displayheaderrow(){
				global $yyLiName,$yyEmail,$yyPass,$yyLiLev,$yyActns,$yyModify,$yyDelete,$yyLogin;
?>
			  <tr>
				<td><strong><?php print $yyLiName?></strong></td>
				<td><strong><?php print $yyEmail?></strong></td>
				<td align="center"><strong><?php print $yyLiLev?></strong></td>
				<td><strong><?php print $yyActns?></strong></td>
				<td width="5%">&nbsp;<span style="font-size:10px;font-weight:bold"><?php print $yyLogin?></span></td>
				<td width="5%">&nbsp;<span style="font-size:10px;font-weight:bold"><?php print $yyModify?></span></td>
				<td width="5%">&nbsp;<span style="font-size:10px;font-weight:bold"><?php print $yyDelete?></span></td>
			  </tr>
<?php		}
		$whereand = ' WHERE ';
		$sSQL = "SELECT clID,clUserName,clActions,clLoginLevel,clPercentDiscount,clEmail,clPW FROM customerlogin ";
		if($thelevel !== '' && $thelevel !== NULL){
			$sSQL .= $whereand . " clLoginLevel=" . $thelevel;
			$whereand=' AND ';
		}
		$accdate = trim(@$_REQUEST['accdate']);
		if($accdate != ''){
			$accdate = parsedate($accdate);
			if(@$_REQUEST['daterange']=='1') // Till
				$sSQL .= $whereand . "clDateCreated <= '" . date("Y-m-d", $accdate) . "' ";
			elseif(@$_REQUEST['daterange']=='2') // On
				$sSQL .= $whereand . "clDateCreated BETWEEN '"  . date("Y-m-d", $accdate) . "' AND '" . date("Y-m-d", $accdate+(60*60*24)) . "' ";
			else // Since
				$sSQL .= $whereand . "clDateCreated >= '" . date("Y-m-d", $accdate) . "' ";
			$whereand=' AND ';
		}
		if(trim(@$_REQUEST['stext']) != ''){
			$stext=unstripslashes($_REQUEST['stext']);
			$stype=trim(@$_REQUEST['stype']);
			$Xstext = escape_string($stext);
			$aText = explode(' ',$Xstext);
			$aFields[0]='clUserName';
			$aFields[1]='clPw';
			$aFields[2]='clEmail';
			if($stype=='exact'){
				$sSQL .= $whereand . "(clUserName LIKE '%" . $Xstext . "%' OR clPw LIKE '%" . $Xstext . "%' OR clEmail LIKE '%" . $Xstext . "%') ";
				$whereand=' AND ';
			}else{
				$sJoin='AND ';
				if($stype=='any') $sJoin='OR ';
				$sSQL .= $whereand . '(';
				$whereand=' AND ';
				for($index=0;$index<=2;$index++){
					$sSQL .= '(';
					$rowcounter=0;
					$arrelms=count($aText);
					foreach($aText as $theopt){
						if(is_array($theopt))$theopt=$theopt[0];
						$sSQL .= $aFields[$index] . " LIKE '%" . $theopt . "%' ";
						if(++$rowcounter < $arrelms) $sSQL .= $sJoin;
					}
					$sSQL .= ') ';
					if($index < 2) $sSQL .= 'OR ';
				}
				$sSQL .= ') ';
			}
		}
		$sSQL .= ' ORDER BY clUserName';
		if(! @is_numeric($_GET["pg"]))
			$CurPage = 1;
		else
			$CurPage = (int)($_GET["pg"]);
		if(@$adminclientloginperpage=='') $adminclientloginperpage=200;
		// $tmpSQL = "SELECT COUNT(DISTINCT products.pId) AS bar" . $sSQL;
		$tmpSQL = str_replace('clID,clUserName,clActions,clLoginLevel,clPercentDiscount,clEmail,clPW', 'COUNT(*) AS bar', $sSQL);
		$allprods = mysql_query($tmpSQL) or print(mysql_error());
		$iNumOfPages = ceil(mysql_result($allprods,0,"bar")/$adminclientloginperpage);
		mysql_free_result($allprods);
		$sSQL .= ' LIMIT ' . ($adminclientloginperpage*($CurPage-1)) . ', ' . $adminclientloginperpage;
		$result = mysql_query($sSQL) or print(mysql_error());
		$haveerrprods=FALSE;
		if(mysql_num_rows($result) > 0){
			$pblink = '<a href="adminclientlog.php?rid=' . @$_REQUEST['rid'] . '&stext=' . urlencode(@$_REQUEST['stext']) . '&stype=' . @$_REQUEST['stype'] . '&slevel=' . @$_REQUEST['slevel'] . '&accdate=' . @$_REQUEST['accdate'] . '&daterange=' . urlencode(@$_REQUEST['daterange']) . '&pg=';
			if($iNumOfPages > 1) print '<tr><td colspan="6" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
			displayheaderrow();
			while($rs = mysql_fetch_assoc($result)){
				if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark';
				displayprodrow($rs);
			}
			if($haveerrprods) print '<tr><td width="100%" colspan="6"><br />' . $redasterix . $yySeePr . '</td></tr>';
			if($iNumOfPages > 1) print '<tr><td colspan="6" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
		}else{
			print '<tr><td width="100%" colspan="6" align="center"><br />' . $yyItNone . '<br />&nbsp;</td></tr>';
		}
		mysql_free_result($result);
	} ?>
			  <tr>
                <td width="100%" colspan="7" align="center"><br /><ul><li><?php print $yyCLTyp?></li></ul>
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </td>
        </tr>
      </table>
	</form>
<?php
}
?>