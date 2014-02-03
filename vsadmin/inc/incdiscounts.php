<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if($_SESSION["loggedon"] != $storesessionvalue || @$disallowlogin==TRUE) exit;
$success=TRUE;
$sSQL = "";
$alreadygotadmin = getadminsettings();
if(@$maxloginlevels=='') $maxloginlevels=5;
if(@$_POST["posted"]=="1"){
	if(@$_POST["act"]=="delete"){
		$sSQL = "DELETE FROM cpnassign WHERE cpaCpnID=" . @$_POST["id"];
		mysql_query($sSQL) or print(mysql_error());
		$sSQL = "DELETE FROM coupons WHERE cpnID=" . @$_POST["id"];
		mysql_query($sSQL) or print(mysql_error());
		print '<meta http-equiv="refresh" content="1; url=admindiscounts.php">';
	}elseif(@$_POST["act"]=="domodify"){
		$sSQL = "UPDATE coupons SET cpnName='" . escape_string(unstripslashes(@$_POST["cpnName"])) . "'";
			for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 1024)==1024) $sSQL .= ",cpnName" . $index . "='" . escape_string(unstripslashes(@$_POST["cpnName" . $index])) . "'";
			}
			if(trim(@$_POST['cpnWorkingName']) != '')
				$sSQL .= ",cpnWorkingName='" . escape_string(unstripslashes(@$_POST['cpnWorkingName'])) . "'";
			else
				$sSQL .= ",cpnWorkingName='" . escape_string(unstripslashes(@$_POST['cpnName'])) . "'";
			if(@$_POST['cpnIsCoupon']=='0')
				$sSQL .= ",cpnNumber='',";
			else
				$sSQL .= ",cpnNumber='" . escape_string(unstripslashes(@$_POST['cpnNumber'])) . "',";
			$sSQL .= 'cpnType=' . @$_POST['cpnType'] . ',';
			$numdays=0;
			if(is_numeric(@$_POST['cpnEndDate'])) $numdays = (int)@$_POST['cpnEndDate'];
			if($numdays > 0)
				$sSQL .= "cpnEndDate='" . date('Y-m-d',(time() + ($numdays*60*60*24))) . "',";
			else
				$sSQL .= "cpnEndDate='3000-01-01',";
			if(is_numeric(@$_POST['cpnDiscount']) && @$_POST['cpnType'] != '0')
				$sSQL .= 'cpnDiscount=' . @$_POST['cpnDiscount'] . ',';
			else
				$sSQL .= 'cpnDiscount=0,';
			if(is_numeric(@$_POST['cpnThreshold']))
				$sSQL .= 'cpnThreshold=' . @$_POST['cpnThreshold'] . ',';
			else
				$sSQL .= 'cpnThreshold=0,';
			if(is_numeric(@$_POST['cpnThresholdMax']))
				$sSQL .= 'cpnThresholdMax=' . @$_POST['cpnThresholdMax'] . ',';
			else
				$sSQL .= 'cpnThresholdMax=0,';
			if(is_numeric(@$_POST['cpnThresholdRepeat']))
				$sSQL .= 'cpnThresholdRepeat=' . @$_POST['cpnThresholdRepeat'] . ',';
			else
				$sSQL .= 'cpnThresholdRepeat=0,';
			if(is_numeric(@$_POST['cpnQuantity']))
				$sSQL .= 'cpnQuantity=' . @$_POST['cpnQuantity'] . ',';
			else
				$sSQL .= 'cpnQuantity=0,';
			if(is_numeric(@$_POST['cpnQuantityMax']))
				$sSQL .= 'cpnQuantityMax=' . @$_POST['cpnQuantityMax'] . ',';
			else
				$sSQL .= 'cpnQuantityMax=0,';
			if(is_numeric(@$_POST['cpnQuantityRepeat']))
				$sSQL .= 'cpnQuantityRepeat=' . @$_POST['cpnQuantityRepeat'] . ',';
			else
				$sSQL .= 'cpnQuantityRepeat=0,';
			if(trim(@$_POST['cpnNumAvail']) != '' && is_numeric(@$_POST['cpnNumAvail']))
				$sSQL .= 'cpnNumAvail=' . @$_POST['cpnNumAvail'] . ',';
			else
				$sSQL .= 'cpnNumAvail=30000000,';
			if(@$_POST['cpnType']=='0')
				$sSQL .= 'cpnCntry=' . @$_POST['cpnCntry'] . ',';
			else
				$sSQL .= 'cpnCntry=0,';
			$cpnLoginLevel=(int)@$_POST['cpnLoginLevel'];
			if(@$_POST['cpnLoginLt']=='1') $cpnLoginLevel=-1-$cpnLoginLevel;
			$sSQL .= 'cpnLoginLevel='.$cpnLoginLevel.',';
			if(is_numeric(@$_POST['cpnHandling'])) $sSQL .= 'cpnHandling=' . @$_POST['cpnHandling'] . ',';
			$sSQL .= 'cpnIsCoupon=' . @$_POST['cpnIsCoupon'] . ',';
			if(@$_POST['cpnType']=='0')
				$sSQL .= 'cpnSitewide=1';
			else
				$sSQL .= 'cpnSitewide=' . @$_POST['cpnSitewide'];
			$sSQL .= ' WHERE cpnID=' . @$_POST['id'];
		mysql_query($sSQL) or print(mysql_error());
		print '<meta http-equiv="refresh" content="1; url=admindiscounts.php">';
	}elseif(@$_POST['act']=='doaddnew'){
		$sSQL = 'INSERT INTO coupons (cpnName';
			for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 1024)==1024) $sSQL .= ',cpnName' . $index;
			}
			$sSQL .= ",cpnWorkingName,cpnNumber,cpnType,cpnEndDate,cpnDiscount,cpnThreshold,cpnThresholdMax,cpnThresholdRepeat,cpnQuantity,cpnQuantityMax,cpnQuantityRepeat,cpnNumAvail,cpnCntry,cpnLoginLevel,cpnHandling,cpnIsCoupon,cpnSitewide) VALUES (";
			$sSQL .= "'" . escape_string(unstripslashes(@$_POST['cpnName'])) . "',";
			for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 1024)==1024) $sSQL .= "'" . escape_string(unstripslashes(@$_POST['cpnName' . $index])) . "',";
			}
			if(trim(@$_POST['cpnWorkingName']) != '')
				$sSQL .= "'" . escape_string(unstripslashes(@$_POST['cpnWorkingName'])) . "',";
			else
				$sSQL .= "'" . escape_string(unstripslashes(@$_POST['cpnName'])) . "',";
			if(@$_POST['cpnIsCoupon']=='0')
				$sSQL .= "'',";
			else
				$sSQL .= "'" . escape_string(unstripslashes(@$_POST['cpnNumber'])) . "',";
			$sSQL .= @$_POST['cpnType'] . ',';
			$numdays=0;
			if(is_numeric(@$_POST['cpnEndDate'])) $numdays = (int)@$_POST['cpnEndDate'];
			if($numdays > 0)
				$sSQL .= "'" . date('Y-m-d',(time() + ($numdays*60*60*24))) . "',";
			else
				$sSQL .= "'3000-01-01',";
			if(is_numeric(@$_POST['cpnDiscount']) && @$_POST['cpnType'] != '0')
				$sSQL .= @$_POST['cpnDiscount'] . ',';
			else
				$sSQL .= '0,';
			if(is_numeric(@$_POST['cpnThreshold']))
				$sSQL .= @$_POST['cpnThreshold'] . ',';
			else
				$sSQL .= '0,';
			if(is_numeric(@$_POST['cpnThresholdMax']))
				$sSQL .= @$_POST['cpnThresholdMax'] . ',';
			else
				$sSQL .= '0,';
			if(is_numeric(@$_POST['cpnThresholdRepeat']))
				$sSQL .= @$_POST['cpnThresholdRepeat'] . ',';
			else
				$sSQL .= '0,';
			if(is_numeric(@$_POST['cpnQuantity']))
				$sSQL .= @$_POST['cpnQuantity'] . ',';
			else
				$sSQL .= '0,';
			if(is_numeric(@$_POST['cpnQuantityMax']))
				$sSQL .= @$_POST['cpnQuantityMax'] . ',';
			else
				$sSQL .= '0,';
			if(is_numeric(@$_POST['cpnQuantityRepeat']))
				$sSQL .= @$_POST['cpnQuantityRepeat'] . ',';
			else
				$sSQL .= '0,';
			if(trim(@$_POST['cpnNumAvail']) != '' && is_numeric(@$_POST['cpnNumAvail']))
				$sSQL .= @$_POST['cpnNumAvail'] . ',';
			else
				$sSQL .= '30000000,';
			if(@$_POST['cpnType']=='0')
				$sSQL .= @$_POST['cpnCntry'] . ',';
			else
				$sSQL .= '0,';
			$cpnLoginLevel=(int)@$_POST['cpnLoginLevel'];
			if(@$_POST['cpnLoginLt']=='1') $cpnLoginLevel=-1-$cpnLoginLevel;
			$sSQL .= $cpnLoginLevel.',';
			if(is_numeric(@$_POST['cpnHandling'])) $sSQL .= @$_POST['cpnHandling'] . ','; else $sSQL .= '0,';
			$sSQL .= @$_POST['cpnIsCoupon'] . ',';
			if(@$_POST['cpnType']=='0')
				$sSQL .= '1)';
			else
				$sSQL .= @$_POST['cpnSitewide'] . ')';
		mysql_query($sSQL) or print(mysql_error());
		print '<meta http-equiv="refresh" content="1; url=admindiscounts.php">';
	}
}
?>
<script language="javascript" type="text/javascript">
<!--
var savebg, savebc, savecol;
function formvalidator(theForm){
  if(theForm.cpnName.value == ""){
    alert("<?php print $yyPlsEntr?> \"<?php print $yyDisTxt?>\".");
    theForm.cpnName.focus();
    return (false);
  }
  if(theForm.cpnName.value.length > 255){
    alert("<?php print $yyMax255?> \"<?php print $yyDisTxt?>\".");
    theForm.cpnName.focus();
    return (false);
  }
  if(theForm.cpnType.selectedIndex!=0){
	if(theForm.cpnDiscount.value == ""){
	  alert("<?php print $yyPlsEntr?> \"<?php print $yyDscAmt?>\".");
	  theForm.cpnDiscount.focus();
	  return (false);
	}
	if(theForm.cpnType.selectedIndex==2){
	  if(theForm.cpnDiscount.value < 0 || theForm.cpnDiscount.value > 100){
		alert("<?php print $yyNum100?> \"<?php print $yyDscAmt?>\".");
		theForm.cpnDiscount.focus();
		return (false);
	  }
	}
  }
  if(theForm.cpnIsCoupon.selectedIndex==1){
	if(theForm.cpnNumber.value == ""){
	  alert("<?php print $yyPlsEntr?> \"<?php print $yyCpnCod?>\".");
	  theForm.cpnNumber.focus();
	  return (false);
	}
	var regex=/^[0-9A-Za-z\_\-]+$/;
	if (!regex.test(theForm.cpnNumber.value)){
		alert("<?php print $yyAlpha2?> \"<?php print $yyCpnCod?>\".");
		theForm.cpnNumber.focus();
		return (false);
	}
  }
  var regex=/^[0-9]*$/;
  if (!regex.test(theForm.cpnNumAvail.value)){
	alert("<?php print $yyOnlyNum?> \"<?php print $yyNumAvl?>\".");
	theForm.cpnNumAvail.focus();
	return (false);
  }
  if(theForm.cpnNumAvail.value != "" && theForm.cpnNumAvail.value > 1000000){
    alert("<?php print $yyNumMil?> \"<?php print $yyNumAvl?>\"<?php print $yyOrBlank?>");
    theForm.cpnNumAvail.focus();
    return (false);
  }
  var regex=/^[0-9]*$/;
  if (!regex.test(theForm.cpnEndDate.value)){
	alert("<?php print $yyOnlyNum?> \"<?php print $yyDaysAv?>\".");
	theForm.cpnEndDate.focus();
	return (false);
  }
  var regex=/^[0-9\.]*$/;
  if (!regex.test(theForm.cpnThreshold.value)){
	alert("<?php print $yyOnlyDec?> \"<?php print $yyMinPur?>\".");
	theForm.cpnThreshold.focus();
	return (false);
  }
  var regex=/^[0-9\.]*$/;
  if (!regex.test(theForm.cpnThresholdRepeat.value)){
	alert("<?php print $yyOnlyDec?> \"<?php print $yyRepEvy?>\".");
	theForm.cpnThresholdRepeat.focus();
	return (false);
  }
  var regex=/^[0-9\.]*$/;
  if (!regex.test(theForm.cpnThresholdMax.value)){
	alert("<?php print $yyOnlyDec?> \"<?php print $yyMaxPur?>\".");
	theForm.cpnThresholdMax.focus();
	return (false);
  }
  var regex=/^[0-9]*$/;
  if (!regex.test(theForm.cpnQuantity.value)){
	alert("<?php print $yyOnlyNum?> \"<?php print $yyMinQua?>\".");
	theForm.cpnQuantity.focus();
	return (false);
  }
  var regex=/^[0-9]*$/;
  if (!regex.test(theForm.cpnQuantityRepeat.value)){
	alert("<?php print $yyOnlyNum?> \"<?php print $yyRepEvy?>\".");
	theForm.cpnQuantityRepeat.focus();
	return (false);
  }
  var regex=/^[0-9]*$/;
  if (!regex.test(theForm.cpnQuantityMax.value)){
	alert("<?php print $yyOnlyNum?> \"<?php print $yyMaxQua?>\".");
	theForm.cpnQuantityMax.focus();
	return (false);
  }
  var regex=/^[0-9\.]*$/;
  if (!regex.test(theForm.cpnDiscount.value)){
	alert("<?php print $yyOnlyDec?> \"<?php print $yyDscAmt?>\".");
	theForm.cpnDiscount.focus();
	return (false);
  }
  document.mainform.cpnNumber.disabled=false;
  document.mainform.cpnDiscount.disabled=false;
  document.mainform.cpnCntry.disabled=false;
  document.mainform.cpnHandling.disabled=false;
  document.mainform.cpnSitewide.disabled=false;
  document.mainform.cpnThresholdRepeat.disabled=false;
  document.mainform.cpnQuantityRepeat.disabled=false;
  return (true);
}
function couponcodeactive(forceactive){
	if(document.mainform.cpnIsCoupon.selectedIndex==0){
		document.mainform.cpnNumber.style.backgroundColor="#DDDDDD";
		document.mainform.cpnNumber.disabled=true;
	}
	else if(document.mainform.cpnIsCoupon.selectedIndex==1){
		document.mainform.cpnNumber.style.backgroundColor=savebg;
		document.mainform.cpnNumber.disabled=false;
	}
}
function changecouponeffect(forceactive){
	if(document.mainform.cpnType.selectedIndex==0){
		document.mainform.cpnDiscount.style.backgroundColor="#DDDDDD";
		document.mainform.cpnDiscount.disabled=true;

		document.mainform.cpnCntry.style.backgroundColor=savebg;
		document.mainform.cpnCntry.disabled=false;
		
		document.mainform.cpnHandling.style.backgroundColor=savebg;
		document.mainform.cpnHandling.disabled=false;

		document.mainform.cpnSitewide.style.backgroundColor="#DDDDDD";
		document.mainform.cpnSitewide.disabled=true;
	}else{
		document.mainform.cpnDiscount.style.backgroundColor=savebg;
		document.mainform.cpnDiscount.disabled=false;

		document.mainform.cpnCntry.style.backgroundColor="#DDDDDD";
		document.mainform.cpnCntry.disabled=true;
		
		document.mainform.cpnHandling.style.backgroundColor="#DDDDDD";
		document.mainform.cpnHandling.disabled=true;

		document.mainform.cpnSitewide.style.backgroundColor=savebg;
		document.mainform.cpnSitewide.disabled=false;
	}
	if(document.mainform.cpnType.selectedIndex==1){
		document.mainform.cpnThresholdRepeat.style.backgroundColor=savebg;
		document.mainform.cpnThresholdRepeat.disabled=false;

		document.mainform.cpnQuantityRepeat.style.backgroundColor=savebg;
		document.mainform.cpnQuantityRepeat.disabled=false;
	}else{
		document.mainform.cpnThresholdRepeat.style.backgroundColor="#DDDDDD";
		document.mainform.cpnThresholdRepeat.disabled=true;

		document.mainform.cpnQuantityRepeat.style.backgroundColor="#DDDDDD";
		document.mainform.cpnQuantityRepeat.disabled=true;
	}
}
function setloglev(isequal){
var tobj=document.getElementById('cpnLoginLevel');
if(isequal.selectedIndex==0)
	tobj[0].text="<?php print $yyNoRes?>";
else
	tobj[0].text="<?php print $yyLiLev . ' 0'?>";
}
//-->
</script>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
<?php if(@$_POST['posted']=='1' && (@$_POST['act']=='modify' || @$_POST['act']=='clone' || @$_POST['act']=='addnew')){
		if(@$_POST['act']=='modify' || @$_POST['act']=='clone'){
			$sSQL = "SELECT cpnName,cpnName2,cpnName3,cpnWorkingName,cpnNumber,cpnType,cpnEndDate,cpnDiscount,cpnThreshold,cpnThresholdMax,cpnThresholdRepeat,cpnQuantity,cpnQuantityMax,cpnQuantityRepeat,cpnNumAvail,cpnCntry,cpnIsCoupon,cpnSitewide,cpnHandling,cpnLoginLevel FROM coupons WHERE cpnID=" . @$_POST["id"];
			$result = mysql_query($sSQL) or print(mysql_error());
			$rs = mysql_fetch_array($result);
			$cpnName = $rs['cpnName'];
			for($index=2; $index <= $adminlanguages+1; $index++)
				$cpnNames[$index] = $rs['cpnName' . $index];
			$cpnWorkingName = $rs['cpnWorkingName'];
			$cpnNumber = $rs['cpnNumber'];
			$cpnType = $rs['cpnType'];
			$cpnEndDate = $rs['cpnEndDate'];
			$cpnDiscount = $rs['cpnDiscount'];
			$cpnThreshold = $rs['cpnThreshold'];
			$cpnThresholdMax = $rs['cpnThresholdMax'];
			$cpnThresholdRepeat = $rs['cpnThresholdRepeat'];
			$cpnQuantity = $rs['cpnQuantity'];
			$cpnQuantityMax = $rs['cpnQuantityMax'];
			$cpnQuantityRepeat = $rs['cpnQuantityRepeat'];
			$cpnNumAvail = $rs['cpnNumAvail'];
			$cpnCntry = $rs['cpnCntry'];
			$cpnIsCoupon = $rs['cpnIsCoupon'];
			$cpnSitewide = $rs['cpnSitewide'];
			$cpnHandling = $rs['cpnHandling'];
			$cpnLoginLevel = $rs['cpnLoginLevel'];
			$cpnLoginLt = ($cpnLoginLevel<0);
			$cpnLoginLevel = abs($cpnLoginLevel);
			mysql_free_result($result);
		}else{
			$cpnName = '';
			for($index=2; $index <= $adminlanguages+1; $index++)
				$cpnNames[$index] = '';
			$cpnWorkingName = '';
			$cpnNumber = '';
			$cpnType = 0;
			$cpnEndDate = '3000-01-01 00:00:00';
			$cpnDiscount = '';
			$cpnThreshold = 0;
			$cpnThresholdMax = 0;
			$cpnThresholdRepeat = 0;
			$cpnQuantity = 0;
			$cpnQuantityMax = 0;
			$cpnQuantityRepeat = 0;
			$cpnNumAvail = 30000000;
			$cpnCntry = 0;
			$cpnIsCoupon = 0;
			$cpnSitewide = 0;
			$cpnHandling = 0;
			$cpnLoginLevel = 0;
			$cpnLoginLt = FALSE;
		}
?>
        <tr>
		  <td width="100%">
		  <form name="mainform" method="post" action="admindiscounts.php" onsubmit="return formvalidator(this)">
			<input type="hidden" name="posted" value="1" />
		<?php	if(@$_POST["act"]=="modify"){ ?>
			<input type="hidden" name="act" value="domodify" />
			<input type="hidden" name="id" value="<?php print @$_POST["id"]?>" />
		<?php	}else{ ?>
			<input type="hidden" name="act" value="doaddnew" />
		<?php	} ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><strong><?php print $yyDscNew?></strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyCpnDsc?>:</strong></td>
				<td width="60%"><select name="cpnIsCoupon" size="1" onchange="couponcodeactive(false);">
					<option value="0"><?php print $yyDisco?></option>
					<option value="1" <?php if((int)$cpnIsCoupon==1) print 'selected="selected"' ?>><?php print $yyCoupon?></option>
					</select></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyDscEff?>:</strong></td>
				<td width="60%"><select name="cpnType" size="1" onchange="changecouponeffect(false);">
					<option value="0"><?php print $yyFrSShp?></option>
					<option value="1" <?php if((int)$cpnType==1) print 'selected="selected"' ?>><?php print $yyFlatDs?></option>
					<option value="2" <?php if((int)$cpnType==2) print 'selected="selected"' ?>><?php print $yyPerDis?></option>
					</select></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyDisTxt?>:</strong></td>
				<td width="60%"><input type="text" name="cpnName" size="30" value="<?php print str_replace('"',"&quot;",$cpnName)?>" /></td>
			  </tr>
<?php		for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 1024)==1024){ ?>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyDisTxt . " " . $index?>:</strong></td>
				<td width="60%"><input type="text" name="cpnName<?php print $index?>" size="30" value="<?php print str_replace('"',"&quot;",$cpnNames[$index])?>" /></td>
			  </tr>
<?php			}
			} ?>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyWrkNam?>:</strong></td>
				<td width="60%"><input type="text" name="cpnWorkingName" size="30" value="<?php print str_replace('"',"&quot;",$cpnWorkingName)?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyCpnCod?>:</strong></td>
				<td width="60%"><input type="text" name="cpnNumber" size="30" value="<?php print $cpnNumber?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyNumAvl?>:</strong></td>
				<td width="60%"><input type="text" name="cpnNumAvail" size="10" value="<?php if((int)$cpnNumAvail != 30000000) print $cpnNumAvail?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyDaysAv?>:</strong></td>
				<td width="60%"><input type="text" name="cpnEndDate" size="10" value="<?php
				if($cpnEndDate != '3000-01-01 00:00:00')
					if(strtotime($cpnEndDate)-strtotime(date('Y-m-d')) < 0) print "Expired"; else print floor((strtotime($cpnEndDate)-time())/(60*60*24))+1; ?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyMinPur?>:</strong></td>
				<td width="60%"><input type="text" name="cpnThreshold" size="10" value="<?php if((int)$cpnThreshold>0) print $cpnThreshold?>" /> <strong><?php print $yyRepEvy?>:</strong> <input type="text" name="cpnThresholdRepeat" size="10" value="<?php if((int)$cpnThresholdRepeat > 0) print $cpnThresholdRepeat?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyMaxPur?>:</strong></td>
				<td width="60%"><input type="text" name="cpnThresholdMax" size="10" value="<?php if((int)$cpnThresholdMax>0) print $cpnThresholdMax?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyMinQua?>:</strong></td>
				<td width="60%"><input type="text" name="cpnQuantity" size="10" value="<?php if((int)$cpnQuantity>0) print $cpnQuantity?>" /> <strong><?php print $yyRepEvy?>:</strong> <input type="text" name="cpnQuantityRepeat" size="10" value="<?php if((int)$cpnQuantityRepeat > 0) print $cpnQuantityRepeat?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyMaxQua?>:</strong></td>
				<td width="60%"><input type="text" name="cpnQuantityMax" size="10" value="<?php if((int)$cpnQuantityMax>0) print $cpnQuantityMax?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyDscAmt?>:</strong></td>
				<td width="60%"><input type="text" name="cpnDiscount" size="10" value="<?php print $cpnDiscount?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyScope?>:</strong></td>
				<td width="60%"><select name="cpnSitewide" size="1">
					<option value="0"><?php print $yyIndCat?></option>
					<option value="3" <?php if((int)$cpnSitewide==3) print 'selected="selected"' ?>><?php print $yyDsCaTo?></option>
					<option value="2" <?php if((int)$cpnSitewide==2) print 'selected="selected"' ?>><?php print $yyGlInPr?></option>
					<option value="1" <?php if((int)$cpnSitewide==1) print 'selected="selected"' ?>><?php print $yyGlPrTo?></option>
					</select></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyAplHan?>:</strong></td>
				<td width="60%"><select name="cpnHandling" size="1">
					<option value="0"><?php print $yyNo?></option>
					<option value="1" <?php if((int)$cpnHandling!=0) print 'selected="selected"' ?>><?php print $yyYes?></option>
					</select></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyLiLev?>:</strong></td>
				<td width="60%">
					<select name="cpnLoginLt" size="1" onchange="setloglev(this)">
					<option value="0">&gt;=</option>
					<option value="1" <?php if($cpnLoginLt) print 'selected="selected"' ?>>=</option>
					</select>
					<select name="cpnLoginLevel" id="cpnLoginLevel" size="1">
						<option value="0"><?php print ($cpnLoginLt?$yyLiLev . ' 0':$yyNoRes)?></option>
<?php				for($index=1; $index<= $maxloginlevels; $index++){
						print '<option value="' . $index . '"';
						if(($cpnLoginLt && $cpnLoginLevel-1==$index) || (! $cpnLoginLt && $cpnLoginLevel==$index)) print ' selected="selected"';
						print '>' . $yyLiLev . ' ' . $index . '</option>';
					} ?>
						<option value="127"<?php if($cpnLoginLevel==127) print ' selected="selected"'?>><?php print $yyDisabl?></option>
					</select>
				</td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyRestr?>:</strong></td>
				<td width="60%"><select name="cpnCntry" size="1">
					<option value="0"><?php print $yyAppAll?></option>
					<option value="1" <?php if((int)$cpnCntry==1) print 'selected="selected"' ?>><?php print $yyYesRes?></option>
					</select></td>
			  </tr>
			  <tr>
                <td width="100%" colspan="2" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" /><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="2" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
		  </form>
<script language="javascript" type="text/javascript">
<!--
savebg=document.mainform.cpnNumber.style.backgroundColor;
couponcodeactive(false);
changecouponeffect(false);
//-->
</script>
		  </td>
        </tr>
<?php }elseif(@$_POST["posted"]=="1" && $success){ ?>
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="admindiscounts.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />&nbsp;</td>
			  </tr>
			</table></td>
        </tr>
<?php }elseif(@$_POST["posted"]=="1"){ ?>
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table></td>
        </tr>
<?php }else{ ?>
        <tr>
		  <td width="100%">
<script language="javascript" type="text/javascript">
<!--
function modrec(id){
	document.mainform.id.value = id;
	document.mainform.act.value = "modify";
	document.mainform.submit();
}
function clone(id){
	document.mainform.id.value = id;
	document.mainform.act.value = "clone";
	document.mainform.submit();
}
function newrec(id){
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.submit();
}
function delrec(id){
cmsg = "<?php print $yyConDel?>\n"
if (confirm(cmsg)) {
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.submit();
}
}
// -->
</script>
		  <form name="mainform" method="post" action="admindiscounts.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="selectedq" value="1" />
			<input type="hidden" name="newval" value="1" />
            <table width="100%" border="0" cellspacing="0" cellpadding="1">
			  <tr> 
                <td width="100%" colspan="7" align="center"><br /><strong><?php print $yyDscAdm?></strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td width="36%" align="left"><strong><?php print $yyWrkNam?></strong></td>
				<td width="10%" align="center"><strong><?php print $yyType?></strong></td>
				<td width="20%" align="center"><strong><?php print $yyExpDat?></strong></td>
				<td width="10%" align="center"><strong><?php print $yyGlobal?></strong></td>
				<td width="8%" align="center"><strong><?php print $yyClone?></strong></td>
				<td width="8%" align="center"><strong><?php print $yyModify?></strong></td>
				<td width="8%" align="center"><strong><?php print $yyDelete?></strong></td>
			  </tr>
<?php
	$bgcolor="";
	$sSQL = "SELECT cpnID,cpnWorkingName,cpnSitewide,cpnIsCoupon,cpnEndDate FROM coupons ORDER BY cpnIsCoupon,cpnWorkingName";
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result) > 0){
		while($alldata = mysql_fetch_row($result)){
			if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark'; ?>
			  <tr class="<?php print $bgcolor?>">
				<td><?php print $alldata[1]?></td>
				<td align="center"><?php	if($alldata[3]==1) print $yyCoupon; else print $yyDisco;?></td>
				<td align="center"><?php	if($alldata[4]=='3000-01-01 00:00:00')
												print $yyNever;
											elseif(strtotime($alldata[4])-strtotime(date('Y-m-d')) < 0)
												print '<span style="color:#FF0000">' . $yyExpird . '</span>';
											else
												print date("Y-m-d",strtotime($alldata[4])); ?></td>
				<td align="center"><?php if($alldata[2]==1 || $alldata[2]==2) print $yyYes; else print $yyNo; ?></td>
				<td align="center"><input type="button" value="<?php print $yyClone?>" onclick="clone('<?php print $alldata[0]?>')" /></td>
				<td align="center"><input type="button" value="<?php print $yyModify?>" onclick="modrec('<?php print $alldata[0]?>')" /></td>
				<td align="center"><input type="button" value="<?php print $yyDelete?>" onclick="delrec('<?php print $alldata[0]?>')" /></td>
			  </tr>
<?php	}
	}else{
?>
			  <tr> 
                <td width="100%" colspan="7" align="center"><br /><strong><?php print $yyNoDsc?></strong><br />&nbsp;</td>
			  </tr>
<?php
	}
	mysql_free_result($result);
?>
			  <tr> 
                <td width="100%" colspan="7" align="center"><br /><strong><?php print $yyPOClk?> </strong>&nbsp;&nbsp;<input type="button" value="<?php print $yyNewDsc?>" onclick="newrec()" /><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="7" align="center"><br />
					<a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
<?php
}
?>
      </table>