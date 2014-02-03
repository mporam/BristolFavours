<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$_SERVER['CONTENT_LENGTH'] != '' && $_SERVER['CONTENT_LENGTH'] > 10000) exit;
$addsuccess = TRUE;
$success = TRUE;
$showaccount = TRUE;
if(@$pathtossl!=''){
	if(substr($pathtossl,-1)!='/') $pathtossl.='/';
}else
	$pathtossl='';
if(@$forceloginonhttps && (@$_SERVER['HTTPS']!='on' && @$_SERVER['SERVER_PORT']!='443') && strpos(@$pathtossl,'https')!==FALSE){ header('Location: '.$pathtossl.basename($_SERVER['PHP_SELF']).(@$_SERVER['QUERY_STRING']!='' ? '?'.$_SERVER['QUERY_STRING'] : '')); exit; }
$theaffilid = preg_replace('/[\W]/', '', @$_POST['affilid']);
if(@$_POST["editaction"] != ""){
	if($theaffilid==''){
		$addsuccess = FALSE;
	}elseif(@$_POST["editaction"]=="modify"){
		$sSQL = "UPDATE affiliates SET ";
		if(trim(@$_POST["affilpw"])!='') $sSQL .= "affilPW='" . escape_string(dohashpw(unstripslashes(@$_POST["affilpw"]))) . "',";
		$sSQL .= "affilEmail='" . escape_string(unstripslashes(@$_POST["email"])) . "',";
		$sSQL .= "affilName='" . escape_string(unstripslashes(@$_POST["name"])) . "',";
		$sSQL .= "affilAddress='" . escape_string(unstripslashes(@$_POST["address"])) . "',";
		$sSQL .= "affilCity='" . escape_string(unstripslashes(@$_POST["city"])) . "',";
		$sSQL .= "affilState='" . escape_string(unstripslashes(@$_POST["state"])) . "',";
		$sSQL .= "affilCountry='" . escape_string(unstripslashes(@$_POST["country"])) . "',";
		$sSQL .= "affilZip='" . escape_string(unstripslashes(@$_POST["zip"])) . "',";
		if(trim(@$_POST["inform"])=="ON")
			$sSQL .= "affilInform=1 ";
		else
			$sSQL .= "affilInform=0 ";
		$sSQL .= "WHERE affilID='" . escape_string($theaffilid) . "'";
		mysql_query($sSQL) or print(mysql_error());
	}elseif(@$_POST["editaction"]=="new"){
		$sSQL = "SELECT affilID FROM affiliates WHERE affilID='" . escape_string($theaffilid) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result) > 0) $addsuccess = FALSE;
		mysql_free_result($result);
		if($addsuccess){
			$sSQL = "INSERT INTO affiliates (affilID,affilPW,affilEmail,affilName,affilAddress,affilCity,affilState,affilCountry,affilZip,affilCommision,affilDate,affilInform) VALUES (";
			$sSQL .= "'" . escape_string($theaffilid) . "',";
			$sSQL .= "'" . escape_string(dohashpw(unstripslashes(@$_POST["affilpw"]))) . "',";
			$sSQL .= "'" . escape_string(unstripslashes(@$_POST["email"])) . "',";
			$sSQL .= "'" . escape_string(unstripslashes(@$_POST["name"])) . "',";
			$sSQL .= "'" . escape_string(unstripslashes(@$_POST["address"])) . "',";
			$sSQL .= "'" . escape_string(unstripslashes(@$_POST["city"])) . "',";
			$sSQL .= "'" . escape_string(unstripslashes(@$_POST["state"])) . "',";
			$sSQL .= "'" . escape_string(unstripslashes(@$_POST["country"])) . "',";
			$sSQL .= "'" . escape_string(unstripslashes(@$_POST["zip"])) . "',";
			if(@$defaultcommission!=""){
				$sSQL .= $defaultcommission . ",";
				$_SESSION["affilCommision"]=(double)$defaultcommission;
			}else{
				$sSQL .= "0,";
				$_SESSION["affilCommision"]=0;
			}
			$sSQL .= "'" . date('Y-m-d') . "',";
			if(trim(@$_POST["inform"])=="ON")
				$sSQL .= "1) ";
			else
				$sSQL .= "0) ";
			mysql_query($sSQL) or print(mysql_error());
			print '<meta http-equiv="Refresh" content="0; URL=affiliate.php">';
		}
	}
	if($addsuccess){
		$_SESSION["xaffilid"] = $theaffilid;
		if(trim(@$_POST["affilpw"])!='') $_SESSION["xaffilpw"] = dohashpw(unstripslashes(@$_POST["affilpw"]));
		$_SESSION["xaffilName"] = unstripslashes(@$_POST["name"]);
	}
}elseif(@$_POST['act']=='affillogin'){
	$sSQL = "SELECT affilID,affilName,affilCommision,affilPW FROM affiliates WHERE affilID='" . escape_string($theaffilid) . "' AND affilPW='" . escape_string(dohashpw(@$_POST["affilpw"])) . "'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result)>0){
		$rs = mysql_fetch_assoc($result);
		$_SESSION["xaffilid"] = $theaffilid;
		$_SESSION["xaffilpw"] = $rs['affilPW'];
		$_SESSION["xaffilName"] = $rs["affilName"];
		$_SESSION["affilCommision"] = (double)$rs["affilCommision"];
		$showaccount=FALSE;
	}else
		$success=FALSE;
	mysql_free_result($result);
	if($success){
		print '<meta http-equiv="Refresh" content="3; URL=affiliate.php">';
?>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
		    <form method="post" action="affiliate.php">
			  <table width="100%" border="0" cellspacing="3" cellpadding="3">
				<tr>
				  <td width="100%" align="center" colspan="2"><strong><?php print $xxAffPrg . " " . $xxWelcom . " " . htmlspecials($_SESSION['xaffilName'])?>.</strong></td>
				</tr>
				<tr>
				  <td width="100%" align="center" colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td width="100%" align="center" colspan="2"><p><?php print $xxAffLog?></p>
					<p><?php print $xxForAut?> <a class="ectlink" href="affiliate.php"><strong><?php print $xxClkHere?></strong></a>.</p></td>
				</tr>
			  </table>
			</form>
		  </td>
        </tr>
      </table>
<?php
	}
}elseif(@$_POST['act']=='logout'){
	$_SESSION['xaffilid'] = '';
	$_SESSION['xaffilpw'] = '';
	$_SESSION['xaffilName'] = '';
}
if(@$_POST['act']=='newaffil' || (@$_POST['act']=='editaffil' && trim(@$_SESSION['xaffilid'])!='') || ! $addsuccess){
	$showaccount=FALSE;
?>
<script language="javascript" type="text/javascript">
<!--
function checkform(frm){
if(frm.affilid.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxAffID?>\".");
	frm.affilid.focus();
	return (false);
}
var checkOK = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
var checkStr = frm.affilid.value;
var allValid = true;
for (i = 0;  i < checkStr.length;  i++){
    ch = checkStr.charAt(i);
    for (j = 0;  j < checkOK.length;  j++)
      if (ch == checkOK.charAt(j))
        break;
    if (j == checkOK.length)
    {
      allValid = false;
      break;
    }
}
if (!allValid){
    alert("<?php print $xxAlphaNu?> \"<?php print $xxAffID?>\".");
    frm.affilid.focus();
    return (false);
}
<?php	if(@$_POST['act']!='editaffil'){ ?>
if(frm.affilpw.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxPwd?>\".");
	frm.affilpw.focus();
	return (false);
}
<?php	} ?>
if(frm.name.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxName?>\".");
	frm.name.focus();
	return (false);
}
if(frm.email.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxEmail?>\".");
	frm.email.focus();
	return (false);
}
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
if(frm.state.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxAllSta?>\".");
	frm.state.focus();
	return (false);
}
if(frm.zip.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxZip?>\".");
	frm.zip.focus();
	return (false);
}
return (true);
}
//-->
</script>
<?php
	$sAffilName = "";
	$sAffilPW = "";
	$sAffilid = "";
	$sAffilAddress = "";
	$sAffilCity = "";
	$sAffilState = "";
	$sAffilZip = "";
	$sAffilCountry = "";
	$sAffilEmail = "";
	$sAffilInform = FALSE;
	if(! $addsuccess){
		$sAffilName = unstripslashes(@$_POST["name"]);
		$sAffilPW = '';
		$sAffilid = unstripslashes(@$_POST["affilid"]);
		$sAffilAddress = unstripslashes(@$_POST["address"]);
		$sAffilCity = unstripslashes(@$_POST["city"]);
		$sAffilState = unstripslashes(@$_POST["state"]);
		$sAffilZip = unstripslashes(@$_POST["zip"]);
		$sAffilCountry = unstripslashes(@$_POST["country"]);
		$sAffilEmail = unstripslashes(@$_POST["email"]);
		$sAffilInform = trim(@$_POST["inform"])=="ON";
	}elseif(@$_POST['act']=='editaffil' && trim(@$_SESSION["xaffilid"]) != ""){
		$sSQL = "SELECT affilName,affilPW,affilAddress,affilCity,affilState,affilZip,affilCountry,affilEmail,affilInform FROM affiliates WHERE affilID='" . escape_string(@$_SESSION["xaffilid"]) . "' AND affilPW='" . escape_string(@$_SESSION["xaffilpw"]) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_array($result)){
			$sAffilName = $rs["affilName"];
			$sAffilPW = $rs["affilPW"];
			$sAffilAddress = $rs["affilAddress"];
			$sAffilCity = $rs["affilCity"];
			$sAffilState = $rs["affilState"];
			$sAffilZip = $rs["affilZip"];
			$sAffilCountry = $rs["affilCountry"];
			$sAffilEmail = $rs["affilEmail"];
			$sAffilInform = ((int)$rs["affilInform"])==1;
		}
		mysql_free_result($result);
	}
?>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
		    <form method="post" action="<?php if(@$forceloginonhttps) print $pathtossl?>affiliate.php" onsubmit="return checkform(this)">
			  <table width="100%" border="0" cellspacing="3" cellpadding="3">
				<tr>
				  <td width="100%" align="center" colspan="4"><strong><?php print $xxAffDts?></strong></td>
				</tr>
<?php if(! $addsuccess){ ?>
				<tr>
				  <td width="100%" align="center" colspan="4"><span style="color:#FF0000;font-weight:bold"><?php print $xxAffUse?></span></td>
				</tr>
<?php } ?>
				<tr>
				  <td width="25%" align="right"><strong><?php print $redasterix.$xxAffID?>:</strong></td>
				  <td width="25%" align="left"><?php
					if(@$_POST['act']=='editaffil' && trim(@$_SESSION['xaffilid']) != ''){
						print htmlspecials(trim(@$_SESSION['xaffilid']));
						?><input type="hidden" name="affilid" size="20" value="<?php print htmlspecials(trim(@$_SESSION['xaffilid']))?>" />
						  <input type="hidden" name="editaction" value="modify" /><?php
					}else{
						?><input type="text" name="affilid" size="20" value="<?php print htmlspecials($sAffilid)?>" />
						  <input type="hidden" name="editaction" value="new" /><?php
					} ?></td>
				  <td width="25%" align="right"><strong><?php print (@$_POST['act']=='editaffil'?$xxReset.' '.$xxPwd:$redasterix.$xxPwd)?>:</strong></td>
				  <td width="25%" align="left"><input type="password" name="affilpw" size="20" value="" autocomplete="off" /></td>
				</tr>
				<tr>
				  <td width="25%" align="right"><strong><?php print $redasterix.$xxName?>:</strong></td>
				  <td width="25%" align="left"><input type="text" name="name" size="20" value="<?php print htmlspecials($sAffilName)?>" /></td>
				  <td width="25%" align="right"><strong><?php print $redasterix.$xxEmail?>:</strong></td>
				  <td width="25%" align="left"><input type="text" name="email" size="25" value="<?php print htmlspecials($sAffilEmail)?>" /></td>
				</tr>
				<tr>
				  <td width="25%" align="right"><strong><?php print $redasterix.$xxAddress?>:</strong></td>
				  <td width="25%" align="left"><input type="text" name="address" size="20" value="<?php print htmlspecials($sAffilAddress)?>" /></td>
				  <td width="25%" align="right"><strong><?php print $redasterix.$xxCity?>:</strong></td>
				  <td width="25%" align="left"><input type="text" name="city" size="20" value="<?php print htmlspecials($sAffilCity)?>" /></td>
				</tr>
				<tr>
				  <td width="25%" align="right"><strong><?php print $redasterix.$xxAllSta?>:</strong></td>
				  <td width="25%" align="left"><input type="text" name="state" size="20" value="<?php print htmlspecials($sAffilState)?>" /></td>
				  <td width="25%" align="right"><strong><?php print $redasterix.$xxCountry?>:</strong></td>
				  <td width="25%" align="left"><select name="country" size="1">
<?php
function show_countries($tcountry){
	$sSQL = 'SELECT countryName,countryOrder,'.getlangid('countryName',8).' FROM countries ORDER BY countryOrder DESC,' . getlangid('countryName',8);
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs = mysql_fetch_array($result)){
		print "<option value='" . htmlspecials($rs['countryName']) . "'";
		if($tcountry==$rs['countryName'])
			print ' selected';
		print '>' . $rs[2] . "</option>\n";
	}
	mysql_free_result($result);
}
show_countries(@$sAffilCountry)
?>
					</select>
				  </td>
				</tr>
				<tr>
				  <td width="25%" align="right"><strong><?php print $redasterix.$xxZip?>:</strong></td>
				  <td width="25%" align="left"><input type="text" name="zip" size="10" value="<?php print htmlspecials($sAffilZip)?>" /></td>
				  <td width="25%" align="right"><strong><?php print $xxInfMe?>:</strong></td>
				  <td width="25%" align="left"><input type="checkbox" name="inform" value="ON" <?php if($sAffilInform) print "checked"?> /></td>
				</tr>
				<tr>
				  <td width="100%" colspan="4">
					<span style="font-size:10px"><ul><li><?php print $xxInform?></li></ul></span>
				  </td>
				</tr>
				<tr>
				  <td width="50%" align="center" colspan="4"><input type="submit" value="<?php print $xxSubmt?>" /> <input type="reset" value="Reset" /> <?php
					if(@$_POST['act']=='editaffil' && trim(@$_SESSION['xaffilid']) != ''){
						print '<br /><br />' . imageorbutton(@$imgbackacct,$xxBack,'','history.go(-1)',TRUE);
					} ?></td>
				</tr>
			  </table>
			</form>
		  </td>
        </tr>
      </table>
<?php
}
if($showaccount){
	if(@$_SESSION['xaffilid']==''){
?>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
			<form method="post" name="mainform" action="<?php if(@$forceloginonhttps) print $pathtossl?>affiliate.php">
			<input type="hidden" name="act" id="act" value="xxx" />
			  <table width="100%" border="0" cellspacing="3" cellpadding="3">
				<tr>
				  <td width="100%" align="center" colspan="2"><strong><?php print $xxAffPrg?></strong></td>
				</tr>
				<tr>
				  <td width="100%" align="center" colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td width="50%" align="right"><?php print $xxNewAct?>:</td>
				  <td><?php print imageorbutton(@$imgaffiliatego,$xxGo,'',"document.getElementById('act').value='newaffil';document.forms.mainform.submit();",TRUE)?></td>
				</tr>
				<tr>
				  <td width="100%" align="center" colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td width="100%" align="center" colspan="2"><strong><?php print $xxGotAct?></strong></td>
				</tr>
<?php if(! $success){ ?>
				<tr>
				  <td width="100%" align="center" colspan="2"><span style="color:#FF0000"><?php print $xxAffNo?></span></td>
				</tr>
<?php } ?>
				<tr>
				  <td width="50%" align="right"><?php print $xxAffID?>:</td>
				  <td><input type="text" name="affilid" size="20" value="<?php print htmlspecials(unstripslashes(@$_POST["affilid"]))?>" /></td>
				</tr>
				<tr>
				  <td width="50%" align="right"><?php print $xxPwd?>:</td>
				  <td><input type="password" name="affilpw" size="20" value="<?php print htmlspecials(unstripslashes(@$_POST["affilpw"]))?>" autocomplete="off" /></td>
				</tr>
				<tr>
				  <td width="100%" align="center" colspan="2"><?php print imageorsubmit(@$imgaffiliatelogin,$xxAffLI.'" onclick="document.getElementById(\'act\').value=\'affillogin\'','')?></td>
				</tr>
			  </table>
			</form>
		  </td>
        </tr>
      </table>
<?php
	}else{
		$lastmonth = mktime (0,0,0,date("m")-1,date("d"), date("Y"));
		$totalDay=0.0;
		$totalYesterday=0.0;
		$totalMonth=0.0;
		$totalLastMonth=0.0;
		
		$sSQL = "SELECT Sum(ordTotal-ordDiscount) as theCount FROM orders WHERE ordStatus>=3 AND ordAffiliate='" . escape_string(@$_SESSION["xaffilid"]) . "' AND ordDate BETWEEN '" . date("Y-m-d") . "' AND '" . date("Y-m-d") . " 23:59:59'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result))
			$totalDay = $rs["theCount"];
		mysql_free_result($result);
		$sSQL = "SELECT Sum(ordTotal-ordDiscount) as theCount FROM orders WHERE ordStatus>=3 AND ordAffiliate='" . escape_string(@$_SESSION["xaffilid"]) . "' AND ordDate BETWEEN '" . date("Y-m-d", time()-(60*60*24)) . "' AND '" . date("Y-m-d") . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result))
			$totalYesterday = $rs["theCount"];
		mysql_free_result($result);
		$sSQL = "SELECT Sum(ordTotal-ordDiscount) as theCount FROM orders WHERE ordStatus>=3 AND ordAffiliate='" . escape_string(@$_SESSION["xaffilid"]) . "' AND ordDate BETWEEN '" . date("Y-m-01") . "' AND '" . date("Y-m-d") . " 23:59:59'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result))
			$totalMonth = $rs["theCount"];
		mysql_free_result($result);
		$sSQL = "SELECT Sum(ordTotal-ordDiscount) as theCount FROM orders WHERE ordStatus>=3 AND ordAffiliate='" . escape_string(@$_SESSION["xaffilid"]) . "' AND ordDate BETWEEN '" . date("Y-m-01", $lastmonth) . "' AND '" . date("Y-m-01") . " 00:00:00'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result))
			$totalLastMonth = $rs["theCount"];
		mysql_free_result($result);
		if(is_null($totalDay)) $totalDay=0.0;
		if(is_null($totalYesterday)) $totalYesterday=0.0;
		if(is_null($totalMonth)) $totalMonth=0.0;
		if(is_null($totalLastMonth)) $totalLastMonth=0.0;
		$alreadygotadmin = getadminsettings();
?>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
		    <form method="post" name="mainform" action="affiliate.php">
			  <input type="hidden" name="act" value="" />
			  <table width="100%" border="0" cellspacing="3" cellpadding="3">
				<tr>
				  <td width="100%" align="center" colspan="2"><strong><?php print $xxAffPrg . ' ' . $xxWelcom . ' ' . htmlspecials(@$_SESSION['xaffilName'])?>.</strong></td>
				</tr>
				<tr>
				  <td width="100%" align="center" colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td width="50%" align="right"><strong><?php print $xxTotTod?>:</strong></td>
				  <td width="50%"><?php print FormatEuroCurrency($totalDay);
				  if($_SESSION["affilCommision"]!=0) print ' = ' . FormatEuroCurrency(($totalDay * $_SESSION["affilCommision"]) / 100.0) . ' <strong>' . $xxCommis . "</strong>";?></td>
				</tr>
				<tr>
				  <td width="50%" align="right"><strong><?php print $xxTotYes?>:</strong></td>
				  <td width="50%"><?php print FormatEuroCurrency($totalYesterday);
				  if($_SESSION["affilCommision"]!=0) print ' = ' . FormatEuroCurrency(($totalYesterday * $_SESSION["affilCommision"]) / 100.0) . ' <strong>' . $xxCommis . "</strong>";?></td>
				</tr>
				<tr>
				  <td width="50%" align="right"><strong><?php print $xxTotMTD?>:</strong></td>
				  <td width="50%"><?php print FormatEuroCurrency($totalMonth);
				  if($_SESSION["affilCommision"]!=0) print ' = ' . FormatEuroCurrency(($totalMonth * $_SESSION["affilCommision"]) / 100.0) . ' <strong>' . $xxCommis . "</strong>";?></td>
				</tr>
				<tr>
				  <td width="50%" align="right"><strong><?php print $xxTotLM?>:</strong></td>
				  <td width="50%"><?php print FormatEuroCurrency($totalLastMonth);
				  if($_SESSION["affilCommision"]!=0) print ' = ' . FormatEuroCurrency(($totalLastMonth * $_SESSION["affilCommision"]) / 100.0) . ' <strong>' . $xxCommis . "</strong>";?></td>
				</tr>
				<tr>
				  <td width="100%" align="center" colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td width="100%" align="center" colspan="2"><?php print imageorsubmit(@$imgeditaffiliate,$xxEdtAff.'" onclick="document.forms.mainform.act.value=\'editaffil\'','')?></td>
				</tr>
				<tr>
				  <td width="100%" align="center" colspan="2">&nbsp;</td>
				</tr>
				<tr>
				  <td width="100%" colspan="2"><span style="font-size:10px">
				    <ul>
					  <li><?php print $xxAffLI1?> <strong>products.php?PARTNER=<?php print htmlspecials(trim(@$_SESSION['xaffilid']))?></strong></li>
					  <li><?php print $xxAffLI2?></li>
					  <?php if($_SESSION["affilCommision"]==0){ ?>
					  <li><?php print $xxAffLI3?></li>
					  <?php } ?>
					</ul></span></td>
				</tr>
				<tr>
				  <td width="100%" align="center" colspan="2"><?php print imageorsubmit(@$imglogout,$xxLogout.'" onclick="document.forms.mainform.act.value=\'logout\'','')?></td>
				</tr>
			  </table>
			</form>
		  </td>
        </tr>
      </table>
<?php
	}
}
?>