<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=='') $storesessionvalue='virtualstore'.time();
if($_SESSION['loggedon'] != $storesessionvalue || @$disallowlogin==TRUE) exit;
if(@$dateadjust=='') $dateadjust=0;
if(@$dateformatstr == '') $dateformatstr = 'm/d/Y';
$admindatestr='Y-m-d';
if(@$admindateformat=='') $admindateformat=0;
if($admindateformat==1)
	$admindatestr='m/d/Y';
elseif($admindateformat==2)
	$admindatestr='d/m/Y';
$success = TRUE;
$showaccount = TRUE;
$dorefresh = FALSE;
$alreadygotadmin = getadminsettings();
if(@$_POST['editaction']=='modify'){
	if(trim(@$_POST['affilid'])!=trim(@$_POST['origaffilid'])){
		$sSQL = "SELECT affilID FROM affiliates WHERE affilID='" . escape_string(unstripslashes(@$_POST['affilid']))."'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result)>0){ $errmsg=$yyAffDup; $success=FALSE; }
		mysql_free_result($result);
	}
	if($success){
		$sSQL = "UPDATE affiliates SET affilID='" . escape_string(unstripslashes(@$_POST['affilid'])) . "',";
		if(trim(@$_POST['affilpw'])!='') $sSQL .="affilPW='" . escape_string(dohashpw(unstripslashes(@$_POST['affilpw']))) . "',";
		$sSQL .= "affilEmail='" . escape_string(unstripslashes(@$_POST['email'])) . "'," .
			"affilName='" . escape_string(unstripslashes(@$_POST['name'])) . "'," .
			"affilAddress='" . escape_string(unstripslashes(@$_POST['address'])) . "'," .
			"affilCity='" . escape_string(unstripslashes(@$_POST['city'])) . "'," .
			"affilState='" . escape_string(unstripslashes(@$_POST['state'])) . "'," .
			"affilCountry='" . escape_string(unstripslashes(@$_POST['country'])) . "'," .
			"affilZip='" . escape_string(unstripslashes(@$_POST['zip'])) . "',";
		if(! is_numeric(trim(@$_POST['affilcommision'])))
			$sSQL .= 'affilCommision=0,';
		else
			$sSQL .= 'affilCommision=' . trim(@$_POST['affilcommision']) . ',';
		if(trim(@$_POST['affildate']) != '')
			$sSQL .= "affilDate='" . date('Y-m-d', parsedate(@$_POST['affildate'])) . "',";
		else
			$sSQL .= "affilDate='" . date('Y-m-d', time() + ($dateadjust*60*60)) . "',";
		$sSQL .= 'affilInform=' . (@$_POST['inform']=='ON' ? '1 ' : '0 ');
		$sSQL .= "WHERE affilID='" . escape_string(unstripslashes(@$_POST['affilid'])) . "'";
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}
}elseif(@$_POST['editaction']=='addnew'){
	$sSQL = "SELECT affilID FROM affiliates WHERE affilID='" . escape_string(unstripslashes(@$_POST['affilid'])) . "'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result)>0){ $errmsg=$yyAffDup; $success=FALSE; }
	mysql_free_result($result);
	if($success){
		$sSQL = 'INSERT INTO affiliates (affilID,affilPW,affilEmail,affilName,affilAddress,affilCity,affilState,affilCountry,affilZip,affilCommision,affilDate,affilInform) VALUES (';
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['affilid'])) . "'," .
			"'" . escape_string(dohashpw(unstripslashes(@$_POST['affilpw']))) . "'," .
			"'" . escape_string(unstripslashes(@$_POST['email'])) . "'," .
			"'" . escape_string(unstripslashes(@$_POST['name'])) . "'," .
			"'" . escape_string(unstripslashes(@$_POST['address'])) . "'," .
			"'" . escape_string(unstripslashes(@$_POST['city'])) . "'," .
			"'" . escape_string(unstripslashes(@$_POST['state'])) . "'," .
			"'" . escape_string(unstripslashes(@$_POST['country'])) . "'," .
			"'" . escape_string(unstripslashes(@$_POST['zip'])) . "',";
		if(! is_numeric(trim(@$_POST['affilcommision'])))
			$sSQL .= '0,';
		else
			$sSQL .= trim(@$_POST['affilcommision']) . ',';
		if(trim(@$_POST['affildate']) != '')
			$sSQL .= "'" . date('Y-m-d', parsedate(@$_POST['affildate'])) . "',";
		else
			$sSQL .= "'" . date('Y-m-d', time() + ($dateadjust*60*60)) . "',";
		$sSQL .= (@$_POST['inform']=='ON' ? '1 ' : '0 ') . ')';
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}
}elseif(@$_POST['editaction']=='delete'){
	$sSQL = "DELETE FROM affiliates WHERE affilID='" . escape_string(unstripslashes(@$_POST['affilid'])) . "'";
	mysql_query($sSQL) or print(mysql_error());
	$dorefresh=TRUE;
}elseif(@$_POST['editaction']=='editaffil'){
	$sSQL = "UPDATE orders SET ordAffiliate='" . escape_string(unstripslashes(@$_POST['affilid'])) . "' WHERE ordID='" . escape_string(@$_POST['id']) . "'";
	mysql_query($sSQL) or print(mysql_error());
}elseif(@$_POST['editaction']=='removeaffil'){
	$sSQL = "UPDATE orders SET ordAffiliate='' WHERE ordAffiliate='" . escape_string(unstripslashes(@$_POST['affilid'])) . "'";
	mysql_query($sSQL) or print(mysql_error());
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=adminaffil.php';
	print '?stext=' . urlencode(@$_POST['stext']) . '&sd=' . @$_REQUEST['sd'] . '&ed=' . @$_REQUEST['ed'] . '&stype=' . @$_POST['stype'] . '&resorder=' . @$_POST['resorder'] . '&pg=1';
	print '">';
}
if(@$_POST['act']=='modify' || @$_POST['act']=='addnew'){
	if(@$_POST['act']=='modify'){
		$sSQL = "SELECT affilName,affilPW,affilAddress,affilCity,affilState,affilZip,affilCountry,affilEmail,affilInform,affilCommision,affilDate FROM affiliates WHERE affilID='" . escape_string(unstripslashes(@$_POST['id'])) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_array($result)){
			$affilID = unstripslashes(@$_POST['id']);
			$affilName = $rs['affilName'];
			$affilPW = '';
			$affilAddress = $rs['affilAddress'];
			$affilCity = $rs['affilCity'];
			$affilState = $rs['affilState'];
			$affilZip = $rs['affilZip'];
			$affilCountry = $rs['affilCountry'];
			$affilEmail = $rs['affilEmail'];
			$affilInform = ((int)$rs['affilInform'])==1;
			$affilCommision = $rs['affilCommision'];
			$affilDate = date($admindatestr, strtotime($rs['affilDate']));
		}
		mysql_free_result($result);
	}else{
		$affilID = '';
		$affilName = '';
		$affilPW = '';
		$affilAddress = '';
		$affilCity = '';
		$affilState = '';
		$affilZip = '';
		$affilCountry = '';
		$affilEmail = '';
		$affilInform = 0;
		$affilCommision = 0;
		$affilDate = date($admindatestr, time() + ($dateadjust*60*60));
	}
?>
<script language="javascript" type="text/javascript">
<!--
function checkform(frm){
if(frm.affilid.value==""){
	alert("<?php print $yyPlsEntr?> \"<?php print $yyAffId?>\".");
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
    alert("<?php print $yyOnlyAl?> \"<?php print $yyAffId?>\" field.");
    frm.affilid.focus();
    return (false);
}
<?php	if(@$_POST['act']!='modify'){ ?>
if(frm.affilpw.value==""){
	alert("<?php print $yyPlsEntr?> \"<?php print $yyPass?>\".");
	frm.affilpw.focus();
	return (false);
}
<?php	} ?>
if(frm.name.value==""){
	alert("<?php print $yyPlsEntr?> \"<?php print $yyName?>\".");
	frm.name.focus();
	return (false);
}
if(frm.email.value==""){
	alert("<?php print $yyPlsEntr?> \"<?php print $yyEmail?>\".");
	frm.email.focus();
	return (false);
}
if(frm.address.value==""){
	alert("<?php print $yyPlsEntr?> \"<?php print $yyAddress?>\".");
	frm.address.focus();
	return (false);
}
if(frm.city.value==""){
	alert("<?php print $yyPlsEntr?> \"<?php print $yyCity?>\".");
	frm.city.focus();
	return (false);
}
if(frm.state.value==""){
	alert("<?php print $yyPlsEntr?> \"<?php print $yyState?>\".");
	frm.state.focus();
	return (false);
}
if(frm.zip.value==""){
	alert("<?php print $yyPlsEntr?> \"<?php print $yyZip?>\".");
	frm.zip.focus();
	return (false);
}
var checkOK = "0123456789.";
var checkStr = frm.affilcommision.value;
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
    alert("<?php print $yyOnlyDec?> \"<?php print $yyCommis?>\".");
    frm.affilcommision.focus();
    return (false);
}
return (true);
}
//-->
</script>
		  <form method="post" action="adminaffil.php" onsubmit="return checkform(this)">
			<input type="hidden" name="origaffilid" value="<?php print htmlspecials($affilID)?>" />
			<input type="hidden" name="editaction" value="<?php print (@$_POST['act']=='modify' ? 'modify' : 'addnew')?>" />
			<input type="hidden" name="stext" value="<?php print @$_POST['stext']?>" />
			<input type="hidden" name="sd" value="<?php print @$_POST['sd']?>" />
			<input type="hidden" name="ed" value="<?php print @$_POST['ed']?>" />
			<input type="hidden" name="resorder" value="<?php print @$_POST['resorder']?>" />
			<input type="hidden" name="posted" value="1" />
			  <table width="100%" border="0" cellspacing="0" cellpadding="3">
				<tr>
				  <td width="100%" align="center" colspan="4"><strong><?php print $yyAffAdm?></strong></td>
				</tr>
				<tr>
				  <td width="25%" align="right"><strong><?php print $redasterix.$yyAffId?>:</strong></td>
				  <td width="25%" align="left"><input type="text" name="affilid" size="20" value="<?php print htmlspecials($affilID)?>" /></td>
				  <td width="25%" align="right"><strong><?php print (@$_POST['act']=='modify'?$yyReset.' '.$yyPass:$redasterix.$yyPass)?>:</strong></td>
				  <td width="25%" align="left"><input type="text" name="affilpw" size="20" value="" /></td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $redasterix.$yyName?>:</strong></td>
				  <td align="left"><input type="text" name="name" size="20" value="<?php print htmlspecials($affilName)?>" /></td>
				  <td align="right"><strong><?php print $redasterix.$yyEmail?>:</strong></td>
				  <td align="left"><input type="text" name="email" size="25" value="<?php print htmlspecials($affilEmail)?>" /></td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $redasterix.$yyAddress?>:</strong></td>
				  <td align="left"><input type="text" name="address" size="20" value="<?php print htmlspecials($affilAddress)?>" /></td>
				  <td align="right"><strong><?php print $redasterix.$yyCity?>:</strong></td>
				  <td align="left"><input type="text" name="city" size="20" value="<?php print htmlspecials($affilCity)?>" /></td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $redasterix.$yyState?>:</strong></td>
				  <td align="left"><input type="text" name="state" size="20" value="<?php print htmlspecials($affilState)?>" /></td>
				  <td align="right"><strong><?php print $redasterix.$yyCountry?>:</strong></td>
				  <td align="left"><select name="country" size="1">
<?php
function show_countries($tcountry){
	$sSQL = 'SELECT countryName FROM countries ORDER BY countryOrder DESC, countryName';
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs = mysql_fetch_array($result)){
		print "<option value='" . htmlspecials($rs['countryName']) . "'";
		if($tcountry==$rs['countryName'])
			print ' selected';
		print '>' . $rs['countryName'] . "</option>\n";
	}
	mysql_free_result($result);
}
show_countries(@$affilCountry)
?>
					</select>
				  </td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $redasterix.$yyZip?>:</strong></td>
				  <td align="left"><input type="text" name="zip" size="10" value="<?php print htmlspecials($affilZip)?>" /></td>
				  <td align="right"><strong>Inform me:</strong></td>
				  <td align="left"><input type="checkbox" name="inform" value="ON" <?php if($affilInform) print "checked";?> /></td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $yyCommis?>:</strong></td>
				  <td align="left"><input type="text" name="affilcommision" size="6" value="<?php print htmlspecials($affilCommision)?>" />%</td>
				  <td align="right"><strong><?php print $yyDate?>:</strong></td>
				  <td align="left"><input type="text" name="affildate" size="10" value="<?php print htmlspecials($affilDate)?>" /></td>
				</tr>
				<tr>
				  <td width="100%" colspan="4">
					<span style="font-size:10px"><ul><li><?php print $yyAffInf?></li></ul></span>
				  </td>
				</tr>
				<tr>
				  <td width="50%" align="center" colspan="4"><input type="submit" value="<?php print $yySubmit?>" /> <input type="reset" value="<?php print $yyReset?>" /></td>
				</tr>
			  </table>
			</form>
<?php
}elseif(@$_POST['posted']=='1' && $success){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="adminaffil.php<?php
							print "?rid=" . @$_POST['rid'] . "&stock=" . @$_POST['stock'] . "&stext=" . urlencode(@$_POST['stext']) . "&sd=" . @$_POST['sd'] . "&ed=" . @$_POST['ed'] . "&stype=" . @$_POST['stype'] . "&approved=" . @$_POST['approved'] . "&pg=" . @$_POST['pg'];
						?>"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />&nbsp;
                </td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}elseif(@$_POST['posted']=='1'){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}else{
	$hasdaterange=FALSE;
	if(trim(@$_REQUEST['sd'])!=''){ $thefromdate=parsedate($_REQUEST['sd']); $hasdaterange=TRUE; }
	if(trim(@$_REQUEST['ed'])=='') $thetodate=time()+($dateadjust*60*60); else $thetodate=parsedate($_REQUEST['ed']);
	if(FALSE){
		$hasdaterange=FALSE;
		$errmsg=$yyDatInv;
	}
	if($hasdaterange){
		$thetodate += (60*60*24);
	}
	$sText = escape_string(@$_REQUEST['stext']);
	$findinvalids = (trim(@$_REQUEST['stype'])=='invalid');
	$themask = 'yyyy-mm-dd';
	if($admindateformat==1)
		$themask='mm/dd/yyyy';
	elseif($admindateformat==2)
		$themask='dd/mm/yyyy';

	$numaffiliates=0;
	$sSQL = 'SELECT COUNT(*) AS thecount FROM affiliates';
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs=mysql_fetch_assoc($result)){
		if(! is_null($rs['thecount'])) $numaffiliates=$rs['thecount'];
	}
	mysql_free_result($result);
	$alldata = '';
	if($findinvalids){
		$sSQL = "SELECT ordAffiliate,ordID,ordDate,ordReferer,ordQueryStr,ordTotal FROM orders LEFT JOIN affiliates ON orders.ordAffiliate=affiliates.affilID WHERE ordAffiliate<>'' AND NOT (ordAffiliate IS NULL) AND affilID IS NULL";
		if($hasdaterange) $sSQL .= " AND ordDate BETWEEN '".date('Y-m-d', $thefromdate)."' AND '".date('Y-m-d', $thetodate)."'";
		if($sText!='') $sSQL .= " AND (ordAffiliate LIKE '%" . $sText . "%' OR ordName LIKE '%" . $sText . "%')";
		$sSQL .= ' ORDER BY ordID DESC';
		$alldata = mysql_query($sSQL) or print(mysql_error());
	}else{
		$affillist='';
		if($hasdaterange){
			$addcomma='';
			$sSQL = "SELECT DISTINCT ordAffiliate FROM orders WHERE ordStatus>=3 AND ordAffiliate<>'' AND NOT (ordAffiliate IS NULL) AND ordDate BETWEEN '".date('Y-m-d', $thefromdate)."' AND '".date('Y-m-d', $thetodate)."'";
			if($sText!='') $sSQL .= " AND ordAffiliate LIKE '%" . $sText . "%'";
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_assoc($result)){
				$affillist .= $addcomma."'".str_replace(array("'",'<'), '', $rs['ordAffiliate']) . "'";
				$addcomma=',';
			}
			mysql_free_result($result);
		}
		if($affillist!=''){
			$sSQL = "SELECT affilID,affilName,affilPW,affilEmail,affilCommision,SUM(ordTotal-ordDiscount) AS affilQuant,affilDate FROM affiliates LEFT JOIN orders ON affiliates.affilID=orders.ordAffiliate WHERE ordStatus>=3 AND affilID IN (".$affillist.")";
			if($hasdaterange) $sSQL .= " AND ordDate BETWEEN '".date('Y-m-d', $thefromdate)."' AND '".date('Y-m-d', $thetodate)."'";
			$sSQL .= ' GROUP BY affilID,affilName,affilPW,affilEmail,affilCommision';
			if(@$_REQUEST['resorder']=='1') $sSQL .= ' ORDER BY affilID'; else $sSQL .= ' ORDER BY affilQuant DESC';
		}else{
			$sSQL = 'SELECT affilID,affilName,affilPW,affilEmail,affilCommision,0 AS affilQuant,affilDate FROM affiliates';
			if($sText!=''){
				$sSQL .= " WHERE affilID LIKE '%" . $sText . "%' OR affilName LIKE '%" . $sText . "%' OR affilEmail LIKE '%" . $sText . "%'";
			}
			$sSQL .= ' ORDER BY affilID';
		}
		if(! ($hasdaterange && $affillist==''))
			$alldata = mysql_query($sSQL) or print(mysql_error());
	}
?>
<script language="javascript" type="text/javascript" src="popcalendar.js">
</script>
<script language="javascript" type="text/javascript">
<!--
function mrec(id){
	document.mainform.action="adminaffil.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "modify";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function newrec(id){
	document.mainform.action="adminaffil.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function delrec(id) {
cmsg = "<?php print $yyConDel?>\n"
if (confirm(cmsg)) {
	document.mainform.affilid.value = id;
	document.mainform.act.value = "search";
	document.mainform.editaction.value = "delete";
	document.mainform.submit();
}
}
function dumpinventory(){
	document.mainform.action="dumporders.php";
	document.mainform.act.value = "dumpaffiliate";
	document.mainform.submit();
}
function startsearch(){
	document.mainform.action="adminaffil.php";
	document.mainform.act.value = "search";
	document.mainform.stock.value = "";
	document.mainform.posted.value = "";
	document.mainform.submit();
}
function proccod(tmen,ordid,affid){
	theact = tmen[tmen.selectedIndex].value;
	if(theact=="1"){
		newwin=window.open("adminorders.php?id="+ordid,"Orders","menubar=no, scrollbars=yes, width=800, height=680, directories=no,location=no,resizable=yes,status=no,toolbar=no");
	}else if(theact=="2"){
		if((affid=prompt("Please enter the new affiliate id for this order.",affid))!=null){
			document.mainform.action="adminaffil.php";
			document.mainform.act.value = "search";
			document.mainform.editaction.value = "editaffil";
			document.mainform.id.value = ordid;
			document.mainform.affilid.value = affid;
			document.mainform.posted.value = "";
			document.mainform.submit();
		}
	}else if(theact=="3"){
		if(confirm("<?php print $yySureCa?>")){
			document.mainform.action="adminaffil.php";
			document.mainform.act.value = "search";
			document.mainform.editaction.value = "editaffil";
			document.mainform.id.value = ordid;
			document.mainform.affilid.value = "";
			document.mainform.posted.value = "";
			document.mainform.submit();
		}
	}else if(theact=="4"){
		if(confirm("Are you sure you want to remove all instances of affiliate code: "+affid)){
			document.mainform.action="adminaffil.php";
			document.mainform.act.value = "search";
			document.mainform.editaction.value = "removeaffil";
			document.mainform.affilid.value = affid;
			document.mainform.posted.value = "";
			document.mainform.submit();
		}
	}
	tmen.selectedIndex=0;
}
// -->
</script>
	<form name="mainform" method="post" action="adminaffil.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="" />
			<input type="hidden" name="stock" value="" />
			<input type="hidden" name="id" value="" />
			<input type="hidden" name="editaction" value="" />
			<input type="hidden" name="affilid" value="" />
			<input type="hidden" name="pg" value="<?php print (@$_POST['act']=='search' ? '1' : @$_GET['pg'])?>" />
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr>
				<td class="cobhl" width="100%" align="center" colspan="4"><strong><?php print '('.$numaffiliates.') '.$yyAffAdm?></strong></td>
			  </tr>
			  <tr> 
				<td class="cobhl" width="20%" align="right"><?php print $yySrchFr?>:</td>
				<td class="cobll" width="30%"><input type="text" name="stext" size="20" value="<?php print @$_REQUEST['stext']?>" /></td>
				<td class="cobhl" width="20%" align="right"><?php print $yyAffBet?>:</td>
				<td class="cobll" width="30%" style="white-space: nowrap"><input type="text" name="sd" size="10" value="<?php print @$_REQUEST['sd']?>" />&nbsp;<input type="button" onclick="popUpCalendar(this, document.forms.mainform.sd, '<?php print $themask?>', -205)" value="DP" />&nbsp;<?php print $yyAnd?>&nbsp;<input type="text" name="ed" size="10" value="<?php print @$_REQUEST['ed']?>" />&nbsp;<input type="button" onclick="popUpCalendar(this, document.forms.mainform.ed, '<?php print $themask?>', -205)" value="DP" /></td>
			  </tr>
			  <tr>
				<td class="cobhl"align="right"><?php print $yySrchTp?>:</td>
				<td class="cobll"><select name="stype" size="1">
					<option value="">Valid Affiliates</option>
					<option value="invalid"<?php if(@$_REQUEST['stype']=='invalid') print ' selected="selected"'?>>Invalid Affilates</option>
					</select>
				</td>
				<td class="cobhl"align="right"><?php print $yyResOrd?>:</td>
				<td class="cobll">
				  <select name="resorder" size="1">
				  <option value=""><?php print $yyTotSal?></option>
				  <option value="1" <?php if(@$_REQUEST['resorder']=="1") print ' selected="selected"'?>><?php print $yyAffId?></option>
				  </select>
				</td>
			  </tr>
			  <tr>
				<td class="cobhl">&nbsp;</td>
				<td class="cobll" colspan="3"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
					  <td class="cobll" align="center"><input type="button" value="<?php print $yyListRe?>" onclick="startsearch();" /> 
						<input type="button" value="<?php print $yyNewAff?>" onclick="newrec();" />
						<input type="button" value="<?php print $yyAffRep?>" onclick="dumpinventory()" />
					  </td>
					  <td class="cobll" height="26" width="20%" align="right">&nbsp;</td>
					</tr>
				  </table></td>
			  </tr>
			</table>
<?php
	if(@$_REQUEST['act']=='search' || @$_GET['pg']!=''){
		if($hasdaterange || $findinvalids) $extcols='6'; else $extcols='4';
?>
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
<?php	if($findinvalids){ ?>
				<tr>
				  <td><strong><?php print $yyAffId?></strong></td>
				  <td align="center"><strong><?php print $yyOrdId?></strong></td>
				  <td align="center"><strong><?php print $yyDate?></strong></td>
				  <td align="center"><strong><?php print $yyWebURL?></strong></td>
				  <td align="right"><strong><?php print $yyAmount?></strong></td>
				  <td align="center"><strong><?php print $yyAct?></strong></td>
				</tr>
<?php	}else{ ?>
				<tr>
				  <td><strong><?php print $yyAffId?></strong></td>
				  <td><strong><?php print $yyName?></strong></td>
				  <td><strong><?php print $yyEmail?></strong></td>
<?php		if($hasdaterange){ ?>
				  <td align="right"><strong><?php print str_replace(' ', '&nbsp;', $yyTotSal)?></strong></td>
				  <td align="right"><strong><?php print $yyCommis?></strong></td>
<?php		} ?>
				  <td align="center"><strong><?php print $yyDelete?></strong></td>
				</tr>
<?php	}
		if($alldata=='' || mysql_num_rows($alldata)==0){ ?>
				<tr>
				  <td width="100%" align="center" colspan="<?php print $extcols?>"><br />&nbsp;<br /><strong><?php print $yyItNone?></strong><br />&nbsp;</td>
				</tr>
<?php	}else{
			$totsales=0;
			$totcomission=0;
			while($rs = mysql_fetch_array($alldata)){
				if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark'; ?>
				<tr class="<?php print $bgcolor?>">
<?php			if($findinvalids){ ?>
				  <td><strong><?php print htmlspecials($rs[0])?></strong></td>
				  <td align="right"><?php print htmlspecials($rs[1])?>&nbsp;</td>
				  <td align="right"><?php print date($admindatestr, strtotime($rs[2]))?>&nbsp;</td>
				  <td><?php
						$fullurl = $rs[3].(trim($rs[4])!='' ? '?'.$rs[4] : '');
						if($fullurl!='') print '<a href="'.$fullurl.'" title="'.$fullurl.'" target="_blank">'.substr($fullurl, 0, 50).(strlen($fullurl)>50?'...':'').'</a>';
				?></td>
				  <td align="right"><?php print FormatEuroCurrency($rs[5])?></td>
				  <td align="right"><select size="1" onchange="proccod(this,'<?php print $rs[1]?>','<?php print str_replace(array("'",'"'),array("\'",'&quot;'),$rs[0])?>')">
				  <option value=""><?php print $yySelect?></option>
				  <option value="1"><?php print $yyVieDet?></option>
				  <option value="2">Edit Code</option>
				  <option value="3">Remove Code</option>
				  <option value="4">Remove All</option>
				  </select></td>
<?php			}else{ ?>
				  <td><a href="javascript:mrec('<?php print jsspecials($rs[0])?>')"><strong><?php print htmlspecials($rs[0])?></strong></a>
<?php				if(time()-strtotime($rs['affilDate']) < (7*60*60*24)) print ' <span style="color:#FF0000">' . '**'.$yyNew.'**' . '</span>'?>
				  </td>
				  <td><?php print htmlspecials($rs[1])?></td>
				  <td><a href="mailto:<?php print htmlspecials($rs[3])?>"><?php print htmlspecials($rs[3])?></a></td>
<?php				if($hasdaterange){ ?>
				  <td align="right"><?php if(! is_numeric($rs[5])) print "-"; else{ print FormatEuroCurrency($rs[5]); $totsales += $rs[5]; } ?></td>
				  <td align="right"><?php if(! is_numeric($rs[5]) || $rs[4]==0) print "-"; else{ print FormatEuroCurrency(($rs[4]*$rs[5]) / 100.0); $totcomission += (($rs[4]*$rs[5]) / 100.0); }?></td>
<?php				} ?>
				  <td align="center"><input type="button" value="<?php print $yyDelete?>" onclick="delrec('<?php print jsspecials($rs[0])?>')" /></td>
<?php			} ?>
				</tr>
<?php		}
 			if($totsales>0 || $totcomission>0){ ?>
				<tr><td colspan="3">&nbsp;</td><td align="right"><?php print FormatEuroCurrency($totsales)?></td><td align="right"><?php print FormatEuroCurrency($totcomission)?></td><td>&nbsp;</td></tr>
<?php		}
		} ?>
			  <tr>
                <td width="100%" colspan="<?php print $extcols?>" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
			</table>
<?php
	}else
		print '&nbsp;<br />&nbsp;<br />&nbsp;<br />';
	if($alldata!='') mysql_free_result($alldata);
?>
	</form>
<?php
}
?>