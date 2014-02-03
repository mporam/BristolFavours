<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if($_SESSION["loggedon"] != $storesessionvalue || @$disallowlogin==TRUE) exit;
$success=TRUE;
$alreadygotadmin = getadminsettings();
$numshipmethods=8;
if(@$_POST["posted"]=="1"){
	$admintweaks=0;
	if(is_array(@$_POST["admintweaks"])){
		foreach(@$_POST["admintweaks"] as $objValue)
			$admintweaks += $objValue;
	}
	$adminlangsettings=0;
	if(is_array(@$_POST['adminlangsettings'])){
		foreach(@$_POST['adminlangsettings'] as $objValue)
			$adminlangsettings += $objValue;
	}
	$prodfilter=0;
	$prodfiltertext='';
	$prodfiltertext2='';
	$prodfiltertext3='';
	for($index=0; $index<=5; $index++){
		if(@$_POST['filtercb'.$index]=='ON') $prodfilter+=pow(2, $index);
		if($index!=0){
			$prodfiltertext.='&';
			$prodfiltertext2.='&';
			$prodfiltertext3.='&';
		}
		$prodfiltertext.=str_replace('&','%26',@$_POST['filtertext'.$index]);
		$prodfiltertext2.=str_replace('&','%26',@$_POST['filtertext'.$index.'x2']);
		$prodfiltertext3.=str_replace('&','%26',@$_POST['filtertext'.$index.'x3']);
	}
	$sortoptions=0;
	for($index=1; $index<=20; $index++){
		if(@$_POST['sortid'.$index]=="ON") $sortoptions+=pow(2,($index-1));
	}
	$sSQL = "UPDATE admin SET adminEmail='" . @$_POST["email"] . "',adminStoreURL='" . @$_POST["url"] . "' WHERE adminID=1";
	mysql_query($sSQL) or print(mysql_error());
	$sSQL = "UPDATE admin SET adminEmail='" . @$_POST["email"] . "',adminStoreURL='" . @$_POST["url"] . "',adminProdsPerPage='" . @$_POST["prodperpage"] . "',adminShipping=" . @$_POST["shipping"] . ",adminIntShipping=" . @$_POST["intshipping"] . ",adminUSPSUser='" . @$_POST["USPSUser"] . "',adminZipCode='" . @$_POST["zipcode"] . "',adminCountry=" . @$_POST["countrySetting"] . ",adminDelUncompleted=" . @$_POST["deleteUncompleted"] . ",adminClearCart=" . @$_POST["adminClearCart"] . ",adminPacking=" . @$_POST["packing"] . ",adminStockManage=" . @$_POST["stockManage"] . ",adminHandling=" . (is_numeric(@$_POST['handling'])?@$_POST['handling']:0) . ",adminHandlingPercent=" . (is_numeric(@$_POST['handlingpercent'])?@$_POST['handlingpercent']:0) . ",adminTweaks=" . $admintweaks . ",adminCanPostUser='" . @$_POST["adminCanPostUser"] . "',smartPostHub='" . @$_POST["smartPostHub"] . "',";
	if(@$_POST["emailconfirm"]=="ON")
		$sSQL .= "adminEmailConfirm=1, ";
	else
		$sSQL .= "adminEmailConfirm=0, ";
	$sSQL .= "adminUnits=" . ((int)@$_POST["adminUnits"] + (int)@$_POST["adminDims"]);
	for($index=1;$index<=3;$index++){
		if(! is_numeric(@$_POST["currRate" . $index]))
			$sSQL .= ",currRate" . $index . "=0";
		else
			$sSQL .= ",currRate" . $index . "=" . @$_POST["currRate" . $index];
		$sSQL .= ",currSymbol" . $index . "='" . @$_POST["currSymbol" . $index] . "'";
	}
	$sSQL .= ",currLastUpdate='" . escape_string(date('Y-m-d H:i:s', time()-100000)) . "'";
	$sSQL .= ",currConvUser='" . escape_string(@$_POST['currConvUser']) . "'";
	$sSQL .= ",currConvPw='" . escape_string(@$_POST['currConvPw']) . "'";
	$sSQL .= ",cardinalProcessor='" . escape_string(@$_POST['cardinalprocessor']) . "'";
	$sSQL .= ",cardinalMerchant='" . escape_string(@$_POST['cardinalmerchant']) . "'";
	$sSQL .= ",cardinalPwd='" . escape_string(@$_POST['cardinalpwd']) . "'";
	$sSQL .= ",adminlanguages='" . @$_POST['adminlanguages'] . "'";
	$sSQL .= ",adminAltRates='" . @$_POST['adminAltRates'] . "'";
	$sSQL .= ",prodFilter=" . $prodfilter;
	$sSQL .= ",prodFilterText='" . escape_string($prodfiltertext) . "'";
	if(($adminlangsettings & 262144)==262144){
		if($adminlanguages>=1) $sSQL .= ",prodFilterText2='" . escape_string($prodfiltertext2) . "'";
		if($adminlanguages>=2) $sSQL .= ",prodFilterText3='" . escape_string($prodfiltertext3) . "'";
	}
	$sSQL .= ",sortOrder=" . @$_POST['sortorder'];
	$sSQL .= ",sortOptions=" . $sortoptions;
	$sSQL .= ",adminlangsettings='" . $adminlangsettings . "'";
	mysql_query($sSQL) or print(mysql_error());
	$sSQL = 'SELECT adminSecret FROM admin WHERE adminID=1';
	$result = mysql_query($sSQL) or print(mysql_error());
	$rs = mysql_fetch_assoc($result);
	$currsecret=trim($rs['adminSecret']);
	mysql_free_result($result);
	if($currsecret=='') mysql_query("UPDATE admin SET adminSecret='its a real secret ".rand(1000000,9999999)." now' WHERE adminID=1") or print(mysql_error());

	$altrateids = explode(',',@$_POST['altrateids']);
	$altrateuse = explode(',',@$_POST['altrateuse']);
	$altrateuseintl = explode(',',@$_POST['altrateuseintl']);
	$altratetext = explode(',',@$_POST['altratetext']);
	$altratetext2 = explode(',',@$_POST['altratetext2']);
	$altratetext3 = explode(',',@$_POST['altratetext3']);

	for($index=1; $index<=$numshipmethods; $index++){
		if($index==1 && trim($altratetext[$index-1])=='') $altratetext[$index-1]=$yyFlatShp;
		if($index==2 && trim($altratetext[$index-1])=='') $altratetext[$index-1]=$yyWghtShp;
		if($index==3 && trim($altratetext[$index-1])=='') $altratetext[$index-1]=$yyUSPS;
		if($index==4 && trim($altratetext[$index-1])=='') $altratetext[$index-1]=$yyUPS;
		if($index==5 && trim($altratetext[$index-1])=='') $altratetext[$index-1]=$yyPriShp;
		if($index==6 && trim($altratetext[$index-1])=='') $altratetext[$index-1]=$yyCanPos;
		if($index==7 && trim($altratetext[$index-1])=='') $altratetext[$index-1]=$yyFedex;
		if($index==8 && trim($altratetext[$index-1])=='') $altratetext[$index-1]='FedEx SmartPost';
		$altratetext[$index-1]=substr($altratetext[$index-1],0,200);
		$altratetext2[$index-1]=substr($altratetext2[$index-1],0,200);
		$altratetext3[$index-1]=substr($altratetext3[$index-1],0,200);
		if(trim($altratetext2[$index-1])=='') $altratetext2[$index-1]=$altratetext[$index-1];
		if(trim($altratetext3[$index-1])=='') $altratetext3[$index-1]=$altratetext[$index-1];

		$sSQL = "UPDATE alternaterates SET altratetext='".escape_string(urldecode($altratetext[$index-1]))."',altratetext2='".escape_string(urldecode($altratetext2[$index-1]))."',altratetext3='".escape_string(urldecode($altratetext3[$index-1]))."', usealtmethod=".$altrateuse[$index-1].",usealtmethodintl=".$altrateuseintl[$index-1].",altrateorder=".$index." WHERE altrateid=".$altrateids[$index-1];
		mysql_query($sSQL) or print(mysql_error());
	}

	print '<meta http-equiv="refresh" content="1; url=adminmain.php">';
}else{
	$allcurrencies="";
	$numcurrencies=0;
	$sSQL = "SELECT DISTINCT countryCurrency FROM countries ORDER BY countryCurrency";
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs=mysql_fetch_array($result))
		$allcurrencies[$numcurrencies++]=$rs;
	mysql_free_result($result);
}
?>
<script language="javascript" type="text/javascript">
<!--
function formvalidator(theForm){
  if(theForm.prodperpage.value == ""){
    alert("<?php print $yyPlsEntr?> \"<?php print $yyPPP?>\".");
    theForm.prodperpage.focus();
    return (false);
  }
  var checkOK = "0123456789";
  var checkStr = theForm.prodperpage.value;
  var allValid = true;
  for (i = 0;  i < checkStr.length;  i++){
	ch = checkStr.charAt(i);
	for (j = 0;  j < checkOK.length;  j++)
		if (ch == checkOK.charAt(j))
			break;
	if (j == checkOK.length){
		allValid = false;
			break;
	}
  }
  if (!allValid){
	alert("<?php print $yyOnlyNum?> \"<?php print $yyPPP?>\".");
	theForm.prodperpage.focus();
	return (false);
  }
for(index=1;index<=3;index++){
  var checkOK = "0123456789.";
  var thisRate = eval("theForm.currRate" + index);
  var checkStr = thisRate.value;
  var allValid = true;
  for (i = 0;  i < checkStr.length;  i++){
	ch = checkStr.charAt(i);
	for (j = 0;  j < checkOK.length;  j++)
		if (ch == checkOK.charAt(j))
			break;
	if (j == checkOK.length){
		allValid = false;
			break;
	}
  }
  if (!allValid){
	alert("<?php print $yyOnlyDec?> \"<?php print $yyConRat?> " + index + "\".");
	thisRate.focus();
	return (false);
  }
}

  if(theForm.handling.value==""){
    alert('<?php print $yyPlsEntr?> \"<?php print $yyHanChg?>\". <?php print $yyNoHan?>');
    theForm.handling.focus();
    return (false);
  }
  var checkOK = "0123456789.";
  var checkStr = theForm.handling.value;
  var allValid = true;
  for (i = 0;  i < checkStr.length;  i++){
	ch = checkStr.charAt(i);
	for (j = 0;  j < checkOK.length;  j++)
		if (ch == checkOK.charAt(j))
			break;
	if (j == checkOK.length){
		allValid = false;
			break;
	}
  }
  if (!allValid){
	alert("<?php print $yyOnlyDec?> \"<?php print $yyHanChg?>\".");
	theForm.handling.focus();
	return (false);
  }

	var altrateids="";
	var altrateuse="";
	var altrateuseintl="";
	var altratetext="";
	var altratetext2="";
	var altratetext3="";
	for(var index=1; index<=<?php print $numshipmethods?>; index++){
		altrateids+=(document.getElementById("altrateids"+index).value+',');
		altrateuse+=((document.getElementById("altrateuse"+index).checked?'1':'0')+',');
		altrateuseintl+=((document.getElementById("altrateuseintl"+index).checked?'1':'0')+',');
		altratetext+=(encodeURIComponent(document.getElementById("altratetext_"+index).value)+',');
		altratetext2+=(encodeURIComponent(document.getElementById("altratetext2_"+index).value)+',');
		altratetext3+=(encodeURIComponent(document.getElementById("altratetext3_"+index).value)+',');
	}
	altrateids=altrateids.substr(0,altrateids.length-1);
	altrateuse=altrateuse.substr(0,altrateuse.length-1);
	altrateuseintl=altrateuseintl.substr(0,altrateuseintl.length-1);
	altratetext=altratetext.substr(0,altratetext.length-1);
	altratetext2=altratetext2.substr(0,altratetext2.length-1);
	altratetext3=altratetext3.substr(0,altratetext3.length-1);
	// alert(altrateids+"\n"+altrateuse+"\n"+altrateuseintl+"\n"+altratetext+"\n"+altratetext2+"\n"+altratetext3);
	document.getElementById("altrateids").value=altrateids;
	document.getElementById("altrateuse").value=altrateuse;
	document.getElementById("altrateuseintl").value=altrateuseintl;
	document.getElementById("altratetext").value=altratetext;
	document.getElementById("altratetext2").value=altratetext2;
	document.getElementById("altratetext3").value=altratetext3;
	return (true);
}
//-->
</script>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
<?php if(@$_POST["posted"]=="1" && $success){ ?>
        <tr>
          <td width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="adminmain.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />
				<img src="../images/clearpixel.gif" width="300" height="1" alt="" /></td>
			  </tr>
			</table></td>
        </tr>
<?php }else{
		$sSQL = "SELECT adminEmail,adminStoreURL,adminProdsPerPage,adminShipping,adminIntShipping,adminUSPSUser,smartPostHub,adminZipCode,adminEmailConfirm,adminCountry,adminUnits,adminDelUncompleted,adminClearCart,adminPacking,adminStockManage,adminHandling,adminHandlingPercent,adminTweaks,currRate1,currSymbol1,currRate2,currSymbol2,currRate3,currSymbol3,currConvUser,currConvPw,cardinalProcessor,cardinalMerchant,cardinalPwd,adminCanPostUser,adminlanguages,adminlangsettings,adminAltRates,prodFilter,prodFilterText,prodFilterText2,prodFilterText3,sortOrder,sortOptions FROM admin WHERE adminID=1";
		$result = mysql_query($sSQL) or print(mysql_error());
		$rsAdmin = mysql_fetch_assoc($result);
		mysql_free_result($result);
?>
        <tr>
		  <td width="100%">
		  <form method="post" action="adminmain.php" onsubmit="return formvalidator(this)">
<input type="hidden" name="posted" value="1" />
<table width="98%" border="0" cellspacing="2" cellpadding="2">
<?php		if(! $success){ ?>
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000"><?php print $errmsg?></span></td>
			  </tr>
<?php		} ?>
  <tr>
    <td colspan="2"><table class="admin-table-a">
      <thead>
        <tr>
          <th colspan="2" scope="col"><?php print $yyStoSet?></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan="2"><?php print $yyCsSym?></td>
        </tr>
        <tr>
          <td><strong><?php print $yyCouSet?>: </strong></td>
          <td><select name="countrySetting" size="1"><?php
					$sSQL = "SELECT countryID,countryName FROM countries WHERE countryLCID<>'' ORDER BY countryOrder DESC, countryName";
					$rsCountry = mysql_query($sSQL) or print(mysql_error());
					while($rs = mysql_fetch_assoc($rsCountry)){
						print "<option value='" . $rs['countryID'] . "'";
						if($rsAdmin['adminCountry']==$rs['countryID']) print ' selected="selected"';
						print '>'. $rs['countryName'] . "</option>\n";
					}
					mysql_free_result($rsCountry);
				  ?></select></td>
        </tr>
        <tr>
          <td colspan="2"><?php print $yyURLEx . ' ' . $yyExample?><br /><?php
						$guessURL = 'http://' . @$_SERVER['SERVER_NAME'] . @$_SERVER['REQUEST_URI'];
						$guessURL = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
						$wherevs = strpos(strtolower($guessURL),'vsadmin');
						if($wherevs > 0)
							$guessURL = substr($guessURL, 0, $wherevs);
						else
							$guessURL = 'http://www.myurl.com/mystore/';
						print $guessURL;
						?></td>
        </tr>
        <tr>
          <td><strong><?php print $yyStoreURL?>:</strong></td>
          <td><input type="text" name="url" size="45" value="<?php print $rsAdmin['adminStoreURL']?>" />
          </td>
        </tr>
        <tr>
          <td colspan="2"><?php print $yyHMPPP?></td>
        </tr>
        <tr>
          <td><strong><?php print $yyPPP?>:</strong></td>
          <td><input type="text" name="prodperpage" size="10" value="<?php print $rsAdmin['adminProdsPerPage']?>" /></td>
        </tr>
		<tr>
          <td><strong><?php print $yyDefSor?>:</strong></td>
          <td><select name="sortorder" size="1">
			<option value="0"><?php print $yySelect?></option>
			<option value="1"<?php if($rsAdmin['sortOrder']==1) print ' selected="selected"'?>><?php print $yySortAl?></option>
			<option value="2"<?php if($rsAdmin['sortOrder']==2) print ' selected="selected"'?>><?php print $yySortID?></option>
			<option value="3"<?php if($rsAdmin['sortOrder']==3) print ' selected="selected"'?>><?php print $yySortPA?></option>
			<option value="4"<?php if($rsAdmin['sortOrder']==4) print ' selected="selected"'?>><?php print $yySortPD?></option>
			<option value="5"<?php if($rsAdmin['sortOrder']==5) print ' selected="selected"'?>><?php print $yySortNS?></option>
			<option value="6"<?php if($rsAdmin['sortOrder']==6) print ' selected="selected"'?>><?php print $yySortOA?></option>
			<option value="7"<?php if($rsAdmin['sortOrder']==7) print ' selected="selected"'?>><?php print $yySortOD?></option>
			<option value="8"<?php if($rsAdmin['sortOrder']==8) print ' selected="selected"'?>><?php print $yySortDA?></option>
			<option value="9"<?php if($rsAdmin['sortOrder']==9) print ' selected="selected"'?>><?php print $yySortDD?></option>
			<option value="10"<?php if($rsAdmin['sortOrder']==10) print ' selected="selected"'?>><?php print $yySortMa?></option>
		  </select></td>
        </tr>
      </tbody>
    </table>
<?php 
	$prodfilter=$rsAdmin['prodFilter'];
	$filtertext=explode('&',$rsAdmin['prodFilterText']);
	$filtertext2=explode('&',$rsAdmin['prodFilterText2']);
	$filtertext3=explode('&',$rsAdmin['prodFilterText3']);
	for($index=0; $index<9; $index++){
		$filtertext[$index]=str_replace("%26","&",@$filtertext[$index]);
		$filtertext2[$index]=str_replace("%26","&",@$filtertext2[$index]);
		$filtertext3[$index]=str_replace("%26","&",@$filtertext3[$index]);
	}
	$sortoptions=$rsAdmin['sortOptions'];
?>
	  <table class="admin-table-a">
        <thead>
          <tr>
            <th colspan="2" scope="col"><?php print $yyPrFiBr?></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="2"><?php print $yyFilSec?></td>
          </tr>
          <tr>
            <td><strong><?php print $yyFilMan?>: </strong></td>
            <td><input type="checkbox" name="filtercb0" value="ON" <?php if(($prodfilter & 1)==1) print 'checked="checked" '?>/> <?php print $yyLabOpt?> <input type="text" name="filtertext0" size="20" maxlength="50" value="<?php print htmlspecials(@$filtertext[0])?>" />
		<?php	if(($adminlangsettings & 262144)==262144){
					if($adminlanguages>=1)	print '<input type="text" name="filtertext0x2" size="20" maxlength="50" value="'.htmlspecials(@$filtertext2[0]).'" /> ';
					if($adminlanguages>=2)	print '<input type="text" name="filtertext0x3" size="20" maxlength="50" value="'.htmlspecials(@$filtertext3[0]).'" /> ';
				} ?></td>
          </tr>
		  <tr>
            <td><strong><?php print $yyFilScr?>: </strong></td>
            <td><input type="checkbox" name="filtercb1" value="ON" <?php if(($prodfilter & 2)==2) print 'checked="checked" '?>/> <?php print $yyLabOpt?> <input type="text" name="filtertext1" size="20" maxlength="50" value="<?php print htmlspecials(@$filtertext[1])?>" />
		<?php	if(($adminlangsettings & 262144)==262144){
					if($adminlanguages>=1)	print '<input type="text" name="filtertext1x2" size="20" maxlength="50" value="'.htmlspecials(@$filtertext2[1]).'" /> ';
					if($adminlanguages>=2)	print '<input type="text" name="filtertext1x3" size="20" maxlength="50" value="'.htmlspecials(@$filtertext3[1]).'" /> ';
				} ?></td>
          </tr>		  
          <tr>
            <td><strong><?php print $yyFilPri?>: </strong></td>
            <td><input type="checkbox" name="filtercb2" value="ON" <?php if(($prodfilter & 4)==4) print 'checked="checked" '?>/> <?php print $yyLabOpt?> <input type="text" name="filtertext2" size="20" maxlength="50" value="<?php print htmlspecials(@$filtertext[2])?>" />
		<?php	if(($adminlangsettings & 262144)==262144){
					if($adminlanguages>=1)	print '<input type="text" name="filtertext2x2" size="20" maxlength="50" value="'.htmlspecials(@$filtertext2[2]).'" /> ';
					if($adminlanguages>=2)	print '<input type="text" name="filtertext2x3" size="20" maxlength="50" value="'.htmlspecials(@$filtertext3[2]).'" /> ';
				} ?></td>
          </tr>
		  <tr>
            <td><strong><?php print $yyCusSor?>: </strong></td>
            <td><input type="checkbox" name="filtercb3" value="ON" <?php if(($prodfilter & 8)==8) print 'checked="checked" '?>/> <?php print $yyLabOpt?> <input type="text" name="filtertext3" size="20" maxlength="50" value="<?php print htmlspecials(@$filtertext[3])?>" />
		<?php	if(($adminlangsettings & 262144)==262144){
					if($adminlanguages>=1)	print '<input type="text" name="filtertext3x2" size="20" maxlength="50" value="'.htmlspecials(@$filtertext2[3]).'" /> ';
					if($adminlanguages>=2)	print '<input type="text" name="filtertext3x3" size="20" maxlength="50" value="'.htmlspecials(@$filtertext3[3]).'" /> ';
				} ?></td>
          </tr>
		  <tr>
            <td colspan="2">
			<table width="100%"><tr>
			<td width="20%" style="font-size:10px;border:0"><label><input type="checkbox" name="sortid1" value="ON" <?php if(($sortoptions & pow(2,0))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortAl?></label></td>
			<td width="20%" style="font-size:10px;border:0"><label><input type="checkbox" name="sortid2" value="ON" <?php if(($sortoptions & pow(2,1))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortID?></label></td>
			<td width="20%" style="font-size:10px;border:0"><label><input type="checkbox" name="sortid3" value="ON" <?php if(($sortoptions & pow(2,2))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortPA?></label></td>
			<td width="20%" style="font-size:10px;border:0"><label><input type="checkbox" name="sortid4" value="ON" <?php if(($sortoptions & pow(2,3))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortPD?></label></td>
			<td width="20%" style="font-size:10px;border:0"><label><input type="checkbox" name="sortid5" value="ON" <?php if(($sortoptions & pow(2,4))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortNS?></label></td>
			</tr><tr>
			<td style="font-size:10px;border:0"><label><input type="checkbox" name="sortid6" value="ON" <?php if(($sortoptions & pow(2,5))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortOA?></label></td>
			<td style="font-size:10px;border:0"><label><input type="checkbox" name="sortid7" value="ON" <?php if(($sortoptions & pow(2,6))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortOD?></label></td>
			<td style="font-size:10px;border:0"><label><input type="checkbox" name="sortid8" value="ON" <?php if(($sortoptions & pow(2,7))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortDA?></label></td>
			<td style="font-size:10px;border:0"><label><input type="checkbox" name="sortid9" value="ON" <?php if(($sortoptions & pow(2,8))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortDD?></label></td>
			<td style="font-size:10px;border:0"><label><input type="checkbox" name="sortid10" value="ON" <?php if(($sortoptions & pow(2,9))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortMa?></label></td>
			</tr></table>
			</td>
          </tr>
		  <tr>
            <td><strong><?php print $yyProPag?>: </strong></td>
            <td><input type="checkbox" name="filtercb4" value="ON" <?php if(($prodfilter & 16)==16) print 'checked="checked" '?>/> <?php print $yyLabOpt?> <input type="text" name="filtertext4" size="20" maxlength="50" value="<?php print htmlspecials(@$filtertext[4])?>" />
		<?php	if(($adminlangsettings & 262144)==262144){
					if($adminlanguages>=1)	print '<input type="text" name="filtertext4x2" size="20" maxlength="50" value="'.htmlspecials(@$filtertext2[4]).'" /> ';
					if($adminlanguages>=2)	print '<input type="text" name="filtertext4x3" size="20" maxlength="50" value="'.htmlspecials(@$filtertext3[4]).'" /> ';
				} ?></td>
          </tr>
		  <tr>
            <td><strong><?php print $yyFilKey?>: </strong></td>
            <td><input type="checkbox" name="filtercb5" value="ON" <?php if(($prodfilter & 32)==32) print 'checked="checked" '?>/> <?php print $yyLabOpt?> <input type="text" name="filtertext5" size="20" maxlength="50" value="<?php print htmlspecials(@$filtertext[5])?>" />
		<?php	if(($adminlangsettings & 262144)==262144){
					if($adminlanguages>=1)	print '<input type="text" name="filtertext5x2" size="20" maxlength="50" value="'.htmlspecials(@$filtertext2[5]).'" /> ';
					if($adminlanguages>=2)	print '<input type="text" name="filtertext5x3" size="20" maxlength="50" value="'.htmlspecials(@$filtertext3[5]).'" /> ';
				} ?></td>
          </tr>
        </tbody>
      </table>
      <table class="admin-table-a">
        <thead>
          <tr>
            <th colspan="2" scope="col"><?php print $yyOrdMan?></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="2"><?php print $yyStkMgt?></td>
          </tr>
          <tr>
            <td><strong><?php print $yyStock?>: </strong></td>
            <td><select name="stockManage" size="1">
					<option value="0"><?php print $yyNoStk?></option>
					<option value="1" <?php if((int)($rsAdmin['adminStockManage'])!=0) print 'selected="selected"'?>> &nbsp;&nbsp; <?php print $yyOn?></option>
					</select></td>
          </tr>			  
          <tr>
            <td colspan="2"><?php print $yyDelUnc?></td>
          </tr>
          <tr>
            <td><strong><?php print $yyDelAft?>:</strong></td>
            <td><select name="deleteUncompleted" size="1">
					<option value="0"><?php print $yyNever?></option>
					<option value="1" <?php if((int)($rsAdmin["adminDelUncompleted"])==1) print 'selected="selected"'?>>1 <?php print $yyDay?></option>
					<option value="2" <?php if((int)($rsAdmin["adminDelUncompleted"])==2) print 'selected="selected"'?>>2 <?php print $yyDays?></option>
					<option value="3" <?php if((int)($rsAdmin["adminDelUncompleted"])==3) print 'selected="selected"'?>>3 <?php print $yyDays?></option>
					<option value="4" <?php if((int)($rsAdmin["adminDelUncompleted"])==4) print 'selected="selected"'?>>4 <?php print $yyDays?></option>
					<option value="7" <?php if((int)($rsAdmin["adminDelUncompleted"])==7) print 'selected="selected"'?>>1 <?php print $yyWeek?></option>
					<option value="14" <?php if((int)($rsAdmin["adminDelUncompleted"])==14) print 'selected="selected"'?>>2 <?php print $yyWeeks?></option>
					</select><?php
			if(! (@$enableclientlogin==TRUE || @$forceclientlogin==TRUE)) writehiddenvar("adminClearCart",$rsAdmin['adminClearCart']) ?></td>
          </tr>
<?php			if(@$enableclientlogin==TRUE || @$forceclientlogin==TRUE){ ?>
		  <tr>
            <td colspan="2"><?php print $yyRemLII?></td>
          </tr>
          <tr>
            <td><strong><?php print $yyDelAft?>:</strong></td>
            <td><select name="adminClearCart" size="1">
					<option value="0"><?php print $yyNever?></option>
					<option value="14" <?php if((int)$rsAdmin['adminClearCart']==14) print 'selected="selected"'?>>2 <?php print $yyWeek?></option>
					<option value="28" <?php if((int)$rsAdmin['adminClearCart']==28) print 'selected="selected"'?>>4 <?php print $yyWeek?></option>
					<option value="70" <?php if((int)$rsAdmin['adminClearCart']==70) print 'selected="selected"'?>>10 <?php print $yyWeek?></option>
					<option value="140" <?php if((int)$rsAdmin['adminClearCart']==140) print 'selected="selected"'?>>20 <?php print $yyWeek?></option>
					<option value="210" <?php if((int)$rsAdmin['adminClearCart']==210) print 'selected="selected"'?>>30 <?php print $yyWeek?></option>
					<option value="364" <?php if((int)$rsAdmin['adminClearCart']==364) print 'selected="selected"'?>>52 <?php print $yyWeek?></option>
					<option value="525" <?php if((int)$rsAdmin['adminClearCart']==525) print 'selected="selected"'?>>75 <?php print $yyWeek?></option>
					<option value="728" <?php if((int)$rsAdmin['adminClearCart']==728) print 'selected="selected"'?>>104 <?php print $yyWeek?></option>
					</select></td>
          </tr>
<?php			} ?>
        </tbody>
      </table>
      <table class="admin-table-a">
        <thead>
          <tr>
            <th colspan="2" scope="col"><?php print $yyEmlSet?></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="2"><?php print $yyLikeCE?></td>
          </tr>
          <tr>
            <td><strong><?php print $yyConEm?>:</strong></td>
            <td><input type="checkbox" name="emailconfirm" value="ON" <?php if((int)($rsAdmin['adminEmailConfirm'])==1) print 'checked="checked"'?> /></td>
          </tr>
          <tr>
            <td colspan="2"><?php print $yyCEAddr?></td>
          </tr>
          <tr>
            <td><strong><?php print $yyEmail?>:</strong></td>
            <td><input type="text" name="email" size="30" value="<?php print $rsAdmin['adminEmail']?>" /></td>
          </tr>
        </tbody>
      </table>
      <table class="admin-table-a">
        <thead>
          <tr>
            <th colspan="2" scope="col"><?php print $yyShHaSe?></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="2"><?php print $yySelShp?></td>
          </tr>
          <tr>
            <td><strong><?php print $yyShpTyp?>: </strong></td>
            <td><select name="shipping" size="1">
					<option value="0"><?php print $yyNoShp?></option>
					<option value="1" <?php if((int)($rsAdmin["adminShipping"])==1) print 'selected="selected"'?>><?php print $yyFlatShp?></option>
					<option value="2" <?php if((int)($rsAdmin["adminShipping"])==2) print 'selected="selected"'?>><?php print $yyWghtShp?></option>
					<option value="5" <?php if((int)($rsAdmin["adminShipping"])==5) print 'selected="selected"'?>><?php print $yyPriShp?></option>
					<option value="3" <?php if((int)($rsAdmin["adminShipping"])==3) print 'selected="selected"'?>><?php print $yyUSPS?></option>
					<option value="4" <?php if((int)($rsAdmin["adminShipping"])==4) print 'selected="selected"'?>><?php print $yyUPS?></option>
					<option value="6" <?php if((int)($rsAdmin["adminShipping"])==6) print 'selected="selected"'?>><?php print $yyCanPos?></option>
					<option value="7" <?php if((int)($rsAdmin["adminShipping"])==7) print 'selected="selected"'?>><?php print $yyFedex?></option>
					<option value="8" <?php if((int)($rsAdmin["adminShipping"])==8) print 'selected="selected"'?>>FedEx SmartPost</option>
					</select></td>
          </tr>
		  <tr>
            <td colspan="2"><?php print $yyWAltRa?></td>
          </tr>
          <tr>
            <td><strong><?php print $yyUseAlt?>: </strong></td>
            <td><select name="adminAltRates" size="1" onchange="showhidealtrates(this)">
				<option value="0"><?php print $yyNoAltR?></option>
				<option value="1"<?php if($rsAdmin['adminAltRates']==1) print 'selected="selected"'?>><?php print $yyAlRaMe?></option>
				<option value="2"<?php if($rsAdmin['adminAltRates']==2) print 'selected="selected"'?>><?php print $yyAlRaTo?></option>
				</select>
			</td>
		  </tr>
		  <tr id="altraterowtitle"<?php if($rsAdmin['adminAltRates']==0) print ' style="display:none"'?>>
            <td colspan="2"><?php print $yyAltSel?></td>
          </tr>
          <tr id="altraterow"<?php if($rsAdmin['adminAltRates']==0) print ' style="display:none"'?>>
            <td><strong><?php print $yyAltShp?>: </strong></td>
            <td>
			<input type="hidden" name="altrateids" id="altrateids" value="" />
			<input type="hidden" name="altrateuse" id="altrateuse" value="" />
			<input type="hidden" name="altrateuseintl" id="altrateuseintl" value="" />
			<input type="hidden" name="altratetext" id="altratetext" value="" />
			<input type="hidden" name="altratetext2" id="altratetext2" value="" />
			<input type="hidden" name="altratetext3" id="altratetext3" value="" />
			<table id="altshptable">
<?php			$index=1;
				$sSQL = "SELECT altrateid,altratename,altratetext,altratetext2,altratetext3,usealtmethod,usealtmethodintl FROM alternaterates ORDER BY usealtmethod DESC,altrateorder,altrateid";
				$result = mysql_query($sSQL) or print(mysql_error());
				while($rs2 = mysql_fetch_assoc($result)){ ?>
				  <tr>
					<td>
					<input type="hidden" id="altrateids<?php print $index?>" value="<?php print $rs2['altrateid']?>" />
					<?php
						if($index==0){
							print '&nbsp;';
						}else{ ?>
					<img src="adminimages/uparrow.png" alt="Move Up" onclick="swaptbrows(<?php print $index?>)" />
<?php					} ?></td>
					<td><input type="checkbox" id="altrateuse<?php print $index?>" value="ON" <?php print ($rs2['usealtmethod']  ? 'checked="checked" ' : '')?>/></td>
					<td><input type="checkbox" id="altrateuseintl<?php print $index?>" value="ON" <?php print ($rs2['usealtmethodintl']  ? 'checked="checked" ' : '')?>/></td>
					<td><span id="methodname<?php print $index?>"><?php print $rs2['altratename'] ?></span></td>
					<td><input type="text" id="altratetext_<?php print $index?>" size="30" value="<?php print htmlspecials($rs2['altratetext']) ?>" /><br />
<?php					for($index2=2; $index2<=3; $index2++){
							if($index2<=($adminlanguages+1) && ($adminlangsettings & 65536)==65536){ ?>
					<input type="text" id="altratetext<?php print $index2?>_<?php print $index?>" size="30" value="<?php print htmlspecials($rs2['altratetext'.$index2]) ?>" /><br />
<?php						}else{ ?>
					<input type="hidden" id="altratetext<?php print $index2?>_<?php print $index?>" value="<?php print htmlspecials($rs2['altratetext'.$index2]) ?>" />
<?php						}
						} ?></td>
				  </tr>
<?php					$index++;
				}
				mysql_free_result($result); ?>
				</table>
<script language="javascript" type="text/javascript">
<!--
function showhidealtrates(obj){
	if(obj.options[obj.selectedIndex].value=="0"){
		document.getElementById('altraterowtitle').style.display='none';
		document.getElementById('altraterow').style.display='none';
	}else{
		document.getElementById('altraterowtitle').style.display='';
		document.getElementById('altraterow').style.display='';
	}
}
function swaptbrows(rid){
	if(rid==1){
	}else{
		rid2=rid-1;
		var altrateids=document.getElementById("altrateids"+rid).value;
		var altrateuse=document.getElementById("altrateuse"+rid).checked;
		var altrateuseintl=document.getElementById("altrateuseintl"+rid).checked;
		var methodname=document.getElementById("methodname"+rid).innerHTML;
		var altratetext=document.getElementById("altratetext_"+rid).value;
		var altratetext2=document.getElementById("altratetext2_"+rid).value;
		var altratetext3=document.getElementById("altratetext3_"+rid).value;
		
		document.getElementById("altrateids"+rid).value=document.getElementById("altrateids"+rid2).value;
		document.getElementById("altrateuse"+rid).checked=document.getElementById("altrateuse"+rid2).checked;
		document.getElementById("altrateuseintl"+rid).checked=document.getElementById("altrateuseintl"+rid2).checked;
		document.getElementById("methodname"+rid).innerHTML=document.getElementById("methodname"+rid2).innerHTML;
		document.getElementById("altratetext_"+rid).value=document.getElementById("altratetext_"+rid2).value;
		document.getElementById("altratetext2_"+rid).value=document.getElementById("altratetext2_"+rid2).value;
		document.getElementById("altratetext3_"+rid).value=document.getElementById("altratetext3_"+rid2).value;
		
		document.getElementById("altrateids"+rid2).value=altrateids;
		document.getElementById("altrateuse"+rid2).checked=altrateuse;
		document.getElementById("altrateuseintl"+rid2).checked=altrateuseintl;
		document.getElementById("methodname"+rid2).innerHTML=methodname;
		document.getElementById("altratetext_"+rid2).value=altratetext;
		document.getElementById("altratetext2_"+rid2).value=altratetext2;
		document.getElementById("altratetext3_"+rid2).value=altratetext3;
	}
}
//-->
</script>
			</td>
		  </tr>
          <tr>
            <td colspan="2"><?php print $yySelShI?></td>
          </tr>
          <tr>
            <td><strong><?php print $yyShpTyp?>: </strong></td>
            <td><select name="intshipping" size="1">
					<option value="0"><?php print $yySamDom?></option>
					<option value="1" <?php if((int)($rsAdmin["adminIntShipping"])==1) print 'selected="selected"'?>><?php print $yyFlatShp?></option>
					<option value="2" <?php if((int)($rsAdmin["adminIntShipping"])==2) print 'selected="selected"'?>><?php print $yyWghtShp?></option>
					<option value="5" <?php if((int)($rsAdmin["adminIntShipping"])==5) print 'selected="selected"'?>><?php print $yyPriShp?></option>
					<option value="3" <?php if((int)($rsAdmin["adminIntShipping"])==3) print 'selected="selected"'?>><?php print $yyUSPS?></option>
					<option value="4" <?php if((int)($rsAdmin["adminIntShipping"])==4) print 'selected="selected"'?>><?php print $yyUPS?></option>
					<option value="6" <?php if((int)($rsAdmin["adminIntShipping"])==6) print 'selected="selected"'?>><?php print $yyCanPos?></option>
					<option value="7" <?php if((int)($rsAdmin["adminIntShipping"])==7) print 'selected="selected"'?>><?php print $yyFedex?></option>
					</select></td>
          </tr>
          <tr>
            <td colspan="2"><?php print $yyHowPck?><br /><span style="font-size:10px"><?php print $yyOnlyAf?></span></td>
          </tr>
          <tr>
            <td><strong><?php print $yyPackPr?>: </strong></td>
            <td><select name="packing" size="1">
					<option value="0"><?php print $yyPckSep?></option>
					<option value="1" <?php if((int)($rsAdmin["adminPacking"])==1) print 'selected="selected"'?>><?php print $yyPckTog?></option>
					</select></td>
          </tr>
		  <tr>
            <td colspan="2"><?php print $yyIfUSPS?><br />
				<span style="font-size:10px"><?php print $yyUPSForm?> <a href="adminupslicense.php"><?php print $yyHere?></a>.</span></td>
          </tr>
          <tr>
            <td><strong><?php print $yyUname?>: </strong></td>
            <td><input type="text" size="15" name="USPSUser" value="<?php print $rsAdmin['adminUSPSUser']?>" /></td>
          </tr>
		   <tr>
            <td colspan="2"><?php print $yyEnMerI?></td>
          </tr>
          <tr>
            <td><strong><?php print $yyRetID?>: </strong></td>
            <td><input type="text" size="36" name="adminCanPostUser" value="<?php print $rsAdmin['adminCanPostUser']?>" /></td>
          </tr>
		  <tr>
            <td colspan="2">If using FedEx SmartPost&reg; you need to enter your Hub ID here.</td>
          </tr>
          <tr>
            <td><strong>SmartPost Hub ID: </strong></td>
            <td><input type="text" size="15" name="smartPostHub" value="<?php print $rsAdmin['smartPostHub']?>" /></td>
          </tr>
		  <tr>
            <td colspan="2"><?php print $yyEntZip?></td>
          </tr>
          <tr>
            <td><strong><?php print $yyZip?>: </strong></td>
            <td><input type="text" name="zipcode" size="10" value="<?php print $rsAdmin['adminZipCode']?>" /></td>
          </tr>
		   <tr>
            <td colspan="2"><?php print $yyUPSUnt?></td>
          </tr>
          <tr>
            <td><strong><?php print $yyShpUnt?>: </strong><br /><br /><strong><?php print $yyDims?>: </strong></td>
            <td>  <select name="adminUnits" size="1">
					<option value="1" <?php if(((int)$rsAdmin["adminUnits"] & 3)==1) print 'selected="selected"'?>>LBS</option>
					<option value="0" <?php if(((int)$rsAdmin["adminUnits"] & 3)==0) print 'selected="selected"'?>>KGS</option>
					</select><br /><br />
				   <select name="adminDims" size="1">
					<option value="0"><?php print $yyNotSpe?></option>
					<option value="4" <?php if(((int)$rsAdmin["adminUnits"] & 12)==4) print 'selected="selected"'?>>IN</option>
					<option value="8" <?php if(((int)$rsAdmin["adminUnits"] & 12)==8) print 'selected="selected"'?>>CM</option>
					</select></td>
          </tr>
		  <tr>
            <td colspan="2"><ul>
				  <li><span style="font-size:10px"><?php print $redasterix.$yyUntNote?></span></li>
				  <li><span style="font-size:10px"><?php print $redasterix.$yyUntNo2?></span></li></ul></td>
          </tr>
		  <tr>
            <td colspan="2"><?php print $yyHandEx?></td>
          </tr>
          <tr>
            <td><strong><?php print $yyHanChg?>: </strong><br /><br /><strong><?php print $yyHanChg . ' (' . $yyPercen . ')'?>: </strong></td>
            <td><input type="text" name="handling" size="10" value="<?php print $rsAdmin['adminHandling']?>" /><br /><br /><input type="text" name="handlingpercent" size="10" style="text-align:right" value="<?php print $rsAdmin['adminHandlingPercent']?>" />%</td>
          </tr>
        </tbody>
      </table></td>
    </tr>
  <tr>
    <td class="adtabcon">
	<table class="admin-table-a">
<thead>
<tr>

<th colspan="2" scope="col"><?php print $yyLaSet?></th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2"><?php print $yyHowLan?></td>
</tr>
<tr>
<td><strong><?php print $yyNumLan?>: </strong></td>
<td><select name="adminlanguages" size="1">
					<option value="0">1</option>
					<option value="1" <?php if((int)($rsAdmin["adminlanguages"])==1) print 'selected="selected"'?>>2</option>
					<option value="2" <?php if((int)($rsAdmin["adminlanguages"])==2) print 'selected="selected"'?>>3</option>
					</select></td>
</tr>
<tr>
<td colspan="2"><?php print $yyWhMull?><br />
<span style="font-size:10px"><?php print $yyLonrel?></span></td>
</tr>
<tr>
<td><strong><?php print $yyLaSet?>: </strong></td>
<td><select name="adminlangsettings[]" size="5" multiple="multiple">
					<option value="1" <?php if(((int)$rsAdmin['adminlangsettings'] & 1)==1) print 'selected="selected"'?>><?php print $yyPrName?></option>
					<option value="2" <?php if(((int)$rsAdmin['adminlangsettings'] & 2)==2) print 'selected="selected"'?>><?php print $yyDesc?></option>
					<option value="4" <?php if(((int)$rsAdmin['adminlangsettings'] & 4)==4) print 'selected="selected"'?>><?php print $yyLnDesc?></option>
					<option value="8" <?php if(((int)$rsAdmin['adminlangsettings'] & 8)==8) print 'selected="selected"'?>><?php print $yyCntNam?></option>
					<option value="16" <?php if(((int)$rsAdmin['adminlangsettings'] & 16)==16) print 'selected="selected"'?>><?php print $yyPOName?></option>
					<option value="32" <?php if(((int)$rsAdmin['adminlangsettings'] & 32)==32) print 'selected="selected"'?>><?php print $yyPOChoi?></option>
					<option value="64" <?php if(((int)$rsAdmin['adminlangsettings'] & 64)==64) print 'selected="selected"'?>><?php print $yyOrdSta?></option>
					<option value="128" <?php if(((int)$rsAdmin['adminlangsettings'] & 128)==128) print 'selected="selected"'?>><?php print $yyPayMet?></option>
					<option value="256" <?php if(((int)$rsAdmin['adminlangsettings'] & 256)==256) print 'selected="selected"'?>><?php print $yyCatNam?></option>
					<option value="512" <?php if(((int)$rsAdmin['adminlangsettings'] & 512)==512) print 'selected="selected"'?>><?php print $yyCatDes?></option>
					<option value="1024" <?php if(((int)$rsAdmin['adminlangsettings'] & 1024)==1024) print 'selected="selected"'?>><?php print $yyDisTxt?></option>
					<option value="2048" <?php if(((int)$rsAdmin['adminlangsettings'] & 2048)==2048) print 'selected="selected"'?>><?php print $yyCatURL?></option>
					<option value="4096" <?php if(((int)$rsAdmin['adminlangsettings'] & 4096)==4096) print 'selected="selected"'?>><?php print $yyEmlHdr?></option>
					<option value="8192" <?php if(((int)$rsAdmin['adminlangsettings'] & 8192)==8192) print 'selected="selected"'?>><?php print $yyManURL?></option>
					<option value="16384" <?php if(((int)$rsAdmin['adminlangsettings'] & 16384)==16384) print 'selected="selected"'?>><?php print $yyManDsc?></option>
					<option value="32768" <?php if(((int)$rsAdmin['adminlangsettings'] & 32768)==32768) print 'selected="selected"'?>><?php print $yyContReg?></option>
					<option value="65536" <?php if(((int)$rsAdmin['adminlangsettings'] & 65536)==65536) print 'selected="selected"'?>><?php print $yyAltShM?></option>
					<option value="131072" <?php if(((int)$rsAdmin['adminlangsettings'] & 131072)==131072) print 'selected="selected"'?>><?php print $yySeaCri?></option>
					<option value="262144" <?php if(((int)$rsAdmin['adminlangsettings'] & 262144)==262144) print 'selected="selected"'?>>Filter Bar</option>
					</select></td>
</tr>
</tbody>
</table>

<table class="admin-table-a">
<thead>
<tr>
<th colspan="2" scope="col">Cardinal Commerce</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2"><?php print $yyCaCoAc?></td>
</tr>
<tr>
<td><strong><?php print "Cardinal Processor ID"?>: </strong></td>
<td><input type="text" name="cardinalprocessor" size="30" value="<?php print htmlspecials($rsAdmin['cardinalProcessor'])?>" /></td>
</tr>
<tr>
<td><strong><?php print "Cardinal Merchant ID"?>: </strong></td>
<td><input type="text" name="cardinalmerchant" size="30" value="<?php print htmlspecials($rsAdmin['cardinalMerchant'])?>" /></td>
</tr>
<tr>
<td><strong><?php print "Cardinal Transaction Password"?>: </strong></td>
<td><input type="text" name="cardinalpwd" size="30" value="<?php print htmlspecials($rsAdmin['cardinalPwd'])?>" /></td>
</tr>

</tbody>
</table>

</td>
<td class="adtabcon"><table class="admin-table-a">
<thead>
<tr>
<th colspan="2" scope="col"><?php print $yyCurenc?></th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2"><?php print $yy3CurCon?><br />
<span style="font-size:10px"><?php print $yyNo3Con?></span></td>
</tr>
<tr>
<td><strong><?php print $yyConv?> 1: </strong></td>
<td>&nbsp;<?php print $yyRate?> <input type="text" name="currRate1" size="10" value="<?php if($rsAdmin["currRate1"] != 0) print $rsAdmin["currRate1"]?>" />&nbsp;&nbsp;&nbsp;Symbol <select name="currSymbol1" size="1"><option value="">None</option>
  <?php	for($index=0; $index<$numcurrencies; $index++){
							print "<option value='" . $allcurrencies[$index][0] . "'";
							if($rsAdmin["currSymbol1"]==$allcurrencies[$index][0]) print ' selected="selected"';
							print ">" . $allcurrencies[$index][0] . "</option>\n";
						} ?></select></td>
</tr>
<tr>
<td><strong><?php print $yyConv?> 2: </strong></td>
<td>&nbsp;<?php print $yyRate?> <input type="text" name="currRate2" size="10" value="<?php if($rsAdmin["currRate2"] != 0) print $rsAdmin["currRate2"]?>" />&nbsp;&nbsp;&nbsp;Symbol <select name="currSymbol2" size="1"><option value="">None</option>
  <?php	for($index=0; $index<$numcurrencies; $index++){
							print "<option value='" . $allcurrencies[$index][0] . "'";
							if($rsAdmin["currSymbol2"]==$allcurrencies[$index][0]) print ' selected="selected"';
							print ">" . $allcurrencies[$index][0] . "</option>\n";
						} ?></select></td>
</tr>
<tr>
  <td><strong><?php print $yyConv?> 3: </strong></td>
  <td>&nbsp;<?php print $yyRate?> <input type="text" name="currRate3" size="10" value="<?php if($rsAdmin["currRate3"] != 0) print $rsAdmin["currRate3"]?>" />&nbsp;&nbsp;&nbsp;Symbol <select name="currSymbol3" size="1"><option value="">None</option>
  <?php	for($index=0; $index<$numcurrencies; $index++){
							print "<option value='" . $allcurrencies[$index][0] . "'";
							if($rsAdmin["currSymbol3"]==$allcurrencies[$index][0]) print ' selected="selected"';
							print ">" . $allcurrencies[$index][0] . "</option>\n";
						} ?></select></td>
</tr>
<tr>
<td colspan="2"><font size="1"><?php print $yyAutoLogin?></td>
</tr>
<tr>
<td><strong><?php print $yyUname?>: </strong><br /><br /><strong><?php print $yyPass?>: </strong></td>
<td><input type="text" name="currConvUser" size="15" value="<?php print $rsAdmin['currConvUser']?>" /><br /><br /><input type="text" name="currConvPw" size="15" value="<?php print $rsAdmin['currConvPw']?>" /></td>
</tr>
</tbody>
</table>
</td>
  </tr>
</table>
<div align="center"><input type="submit" value="Submit" />&nbsp; &nbsp;<input type="reset" value="Reset" /><br />&nbsp;</div>
	</table>
		  </form>
		  </td>
        </tr>
<?php } ?>
      </table>