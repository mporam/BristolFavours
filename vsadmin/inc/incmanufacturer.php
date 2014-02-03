<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if($_SESSION['loggedon'] != $storesessionvalue || @$disallowlogin==TRUE) exit;
function writeposition($currpos,$maxpos){
	$reqtext="<select name='newpos" . $currpos . "' onchange='chi(" . $currpos . ");'>";
	for($i = 1; $i <= $maxpos; $i++){
		$reqtext .= '<option'; // value='" . $i . "'";
		if($currpos==$i) $reqtext .= " selected";
		$reqtext .= ">" . $i; // . "</option>";
		if($i >= 10 && $i < ($maxpos-15) && abs($currpos-$i) > 40) $i += 9;
	}
	return($reqtext . "</select>");
}
if(@$dateadjust=="") $dateadjust=0;
if(@$dateformatstr == "") $dateformatstr = "m/d/Y";
$admindatestr="Y-m-d";
if(@$admindateformat=="") $admindateformat=0;
if($admindateformat==1)
	$admindatestr="m/d/Y";
elseif($admindateformat==2)
	$admindatestr="d/m/Y";
$addsuccess = TRUE;
$maxcatsperpage = 500;
$success = TRUE;
$showaccount = TRUE;
$dorefresh = FALSE;
$alreadygotadmin = getadminsettings();
if(@$defaultcatimages=='') $defaultcatimages = 'images/';
if(@$_POST['act']=='changepos'){
	$currentorder = (int)@$_POST['selectedq'];
	$neworder = (int)@$_POST['newval'];
	$sSQL = 'SELECT mfID FROM manufacturer ORDER BY mfOrder';
	$result = mysql_query($sSQL) or print(mysql_error());
	$rowcounter=1;
	while($rs = mysql_fetch_assoc($result)){
		$theorder = $rowcounter;
		if($currentorder == $theorder)
			$theorder = $neworder;
		elseif(($currentorder > $theorder) && ($neworder <= $theorder))
			$theorder++;
		elseif(($currentorder < $theorder) && ($neworder >= $theorder))
			$theorder--;
		$sSQL="UPDATE manufacturer SET mfOrder=" . $theorder . " WHERE mfID=" . $rs['mfID'];
		mysql_query($sSQL) or print(mysql_error());
		$rowcounter++;
	}
	mysql_free_result($result);
	print '<meta http-equiv="refresh" content="1; url=adminmanufacturer.php?pg=' . @$_POST['pg'] . '">';
}elseif(@$_POST['act']=="domodify"){
	$sSQL = "UPDATE manufacturer SET " .
		"mfName='" . escape_string(unstripslashes(@$_POST['name'])) . "'," .
		"mfAddress='" . escape_string(unstripslashes(@$_POST['address'])) . "'," .
		"mfCity='" . escape_string(unstripslashes(@$_POST['city'])) . "'," .
		"mfState='" . escape_string(unstripslashes(@$_POST['state'])) . "'," .
		"mfCountry='" . escape_string(unstripslashes(@$_POST['country'])) . "'," .
		"mfZip='" . escape_string(unstripslashes(@$_POST['zip'])) . "'," .
		"mfLogo='" . escape_string(unstripslashes(@$_POST['mflogo'])) . "'," .
		"mfURL='" . escape_string(unstripslashes(@$_POST['mfurl'])) . "'," .
		"mfDescription='" . escape_string(unstripslashes(@$_POST['mfdescription'])) . "',";
	for($index=2; $index <= $adminlanguages+1; $index++){
		if(($adminlangsettings & 8192)==8192)
			$sSQL .= "mfURL" . $index . "='" . escape_string(unstripslashes(@$_POST['mfurl' . $index])) . "',";
		if(($adminlangsettings & 16384)==16384)
			$sSQL .= "mfDescription" . $index . "='" . escape_string(unstripslashes(@$_POST['mfdescription' . $index])) . "',";
	}
	$sSQL .= "mfEmail='" . escape_string(unstripslashes(@$_POST['email'])) . "' " .
		"WHERE mfID=" . escape_string(unstripslashes(@$_POST['mfID']));
	mysql_query($sSQL) or print(mysql_error());
	$dorefresh=TRUE;
}elseif(@$_POST['act']=="doaddnew"){
	$sSQL = "SELECT MAX(mfOrder) AS theorder FROM manufacturer";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_array($result)) $theorder = $rs['theorder']+1; else $theorder=1;
	mysql_free_result($result);
	$sSQL = "INSERT INTO manufacturer (mfName,mfOrder,mfAddress,mfCity,mfState,mfCountry,mfZip,mfLogo,mfURL,mfDescription,";
	for($index=2; $index <= $adminlanguages+1; $index++){
		if(($adminlangsettings & 8192)==8192)
			$sSQL .= 'mfURL' . $index . ',';
		if(($adminlangsettings & 16384)==16384)
			$sSQL .= 'mfDescription' . $index . ',';
	}	
	$sSQL .= 'mfEmail) VALUES (' .
		"'" . escape_string(unstripslashes(@$_POST['name'])) . "'," .
		"'" . $theorder . "'," .
		"'" . escape_string(unstripslashes(@$_POST['address'])) . "'," .
		"'" . escape_string(unstripslashes(@$_POST['city'])) . "'," .
		"'" . escape_string(unstripslashes(@$_POST['state'])) . "'," .
		"'" . escape_string(unstripslashes(@$_POST['country'])) . "'," .
		"'" . escape_string(unstripslashes(@$_POST['zip'])) . "'," .
		"'" . escape_string(unstripslashes(@$_POST['mflogo'])) . "'," .
		"'" . escape_string(unstripslashes(@$_POST['mfurl'])) . "'," .
		"'" . escape_string(unstripslashes(@$_POST['mfdescription'])) . "',";
	for($index=2; $index <= $adminlanguages+1; $index++){
		if(($adminlangsettings & 8192)==8192)
			$sSQL .= "'" . escape_string(unstripslashes(@$_POST['mfurl' . $index])) . "',";
		if(($adminlangsettings & 16384)==16384)
			$sSQL .= "'" . escape_string(unstripslashes(@$_POST['mfdescription' . $index])) . "',";
	}
	$sSQL .= "'" . escape_string(unstripslashes(@$_POST['email'])) . "')";
	mysql_query($sSQL) or print(mysql_error());
	$dorefresh=TRUE;
}elseif(@$_POST['act']=="delete"){
	$sSQL = "DELETE FROM manufacturer WHERE mfID=" . trim(@$_POST['id']);
	mysql_query($sSQL) or print(mysql_error());
	$sSQL = "UPDATE products SET pManufacturer=0 WHERE pManufacturer=" . trim(@$_POST['id']);
	mysql_query($sSQL) or print(mysql_error());
	$dorefresh=TRUE;
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=adminmanufacturer.php">';
?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="adminmanufacturer.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />
				<img src="../images/clearpixel.gif" width="300" height="3" alt="" />
                </td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}elseif(trim(@$_POST['act'])=="modify" || trim(@$_POST['act'])=="addnew"){
	if(trim(@$_POST['act'])=="modify"){
		$mfID=trim(@$_POST['id']);
		$sSQL = "SELECT mfName,mfAddress,mfCity,mfState,mfZip,mfCountry,mfEmail,mfLogo,mfURL,mfURL2,mfURL3,mfDescription,mfDescription2,mfDescription3 FROM manufacturer WHERE mfID=" . $mfID;
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_array($result)){
			$mfName = $rs['mfName'];
			$mfAddress = $rs['mfAddress'];
			$mfCity = $rs['mfCity'];
			$mfState = $rs['mfState'];
			$mfZip = $rs['mfZip'];
			$mfCountry = $rs['mfCountry'];
			$mfEmail = $rs['mfEmail'];
			$mfLogo = $rs['mfLogo'];
			for($index=1; $index<=3; $index++){
				$mfURL[$index] = $rs['mfURL'.($index==1?'':$index)];
				$mfDescription[$index] = $rs['mfDescription'.($index==1?'':$index)];
			}
		}
		mysql_free_result($result);
	}else{
		$mfName = "";
		$mfAddress = "";
		$mfCity = "";
		$mfState = "";
		$mfZip = "";
		$mfCountry = "";
		$mfEmail = "";
		$mfLogo = '';
		for($index=1; $index<=3; $index++){
			$mfURL[$index] = '';
			$mfDescription[$index] = '';
		}
	}
?>
<script language="javascript" type="text/javascript">
<!--
function checkform(frm)
{
if(frm.name.value==""){
	alert("<?php print $yyPlsEntr?> \"<?php print $yyName?>\".");
	frm.name.focus();
	return (false);
}
return (true);
}
function uploadimage(imfield){
	var addthumb=0;
	var winwid=350; var winhei=220;
	if(imfield.substring(0,2)=='pG'){ addthumb=2; winhei=300; }
	if(imfield.substring(0,2)=='pL'){ addthumb=1; winhei=280; }
	var prnttext = '<html><head><link rel="stylesheet" type="text/css" href="adminstyle.css"/><script type="text/javascript">function getCookie(c_name){if(document.cookie.length>0){var c_start=document.cookie.indexOf(c_name + "=");if(c_start!=-1){c_start=c_start+c_name.length+1;var c_end=document.cookie.indexOf(";",c_start);if(c_end==-1)c_end=document.cookie.length;return unescape(document.cookie.substring(c_start,c_end));}}return "";}';
	prnttext += 'function checkcookies(){ for(var ind=0; ind<='+addthumb+'; ind++){\r\n';
	prnttext += 'document.getElementById("newdim"+ind).value=getCookie("newdim"+ind);\r\n';
	prnttext += 'if(getCookie("suffix"+ind)!="")document.getElementById("suffix"+ind).value=getCookie("suffix"+ind);\r\n';
	prnttext += 'if(getCookie("thumbdim"+ind)!="")document.getElementById("thumbdim"+ind).selectedIndex=getCookie("thumbdim"+ind);}\r\n';
	prnttext += '}<'+'/script></head><body<?php if(extension_loaded('gd')) print ' onload="checkcookies()"'?>>\n';
	prnttext += '<form name="mainform" method="post" action="doupload.php?defimagepath=<?php print $defaultcatimages?>" enctype="multipart/form-data">';
	prnttext += '<input type="hidden" name="defimagepath" value="<?php print $defaultcatimages?>" />';
	prnttext += '<input type="hidden" name="imagefield" value="'+imfield+'" />';
	prnttext += '<table border="0" cellspacing="1" cellpadding="1" width="100%">';
	prnttext += '<tr><td align="center" colspan="2">&nbsp;<br /><strong><?php print str_replace("'","\\'", $yyUplIma)?></strong><br />&nbsp;</td></tr>';
	prnttext += '<tr><td align="center" colspan="2"><?php print str_replace("'","\\'", $yyPlsSUp)?><br />&nbsp;</td></tr>';
	prnttext += '<tr><td align="center" colspan="2"><?php print str_replace("'","\\'", $yyLocIma)?>:<input type="file" name="imagefile" /></td></tr>';
<?php	if(extension_loaded('gd')){
			$winhei = 260; ?>
	prnttext += '<tr><td colspan="2">&nbsp;</td></tr><tr><td align="right"><select size="1" name="thumbdim0" id="thumbdim0"><option value="">Don\'t Resize Image</option><option value="1">Resize to Width:</option><option value="2">Resize to Height:</option></select></td><td><input type="text" name="newdim0" id="newdim0" size="3" />:px&nbsp;&nbsp;</td></tr>';
<?php	}else
			$winhei = 200; ?>
	prnttext += '<tr><td colspan="2" align="center">&nbsp;<br /><input type="submit" value="<?php print str_replace("'","\\'", $yySubmit)?>" /></td></tr>';
	prnttext += '</table></form>';
	prnttext += '<p align="center"><a href="javascript:window.close()"><strong><?php print str_replace("'","\\'", $yyClsWin)?></strong></a></p>';
	prnttext += '</body></html>';
	var scrwid=screen.width; var scrhei=screen.height;
	var newwin = window.open("","uploadimage",'menubar=no,scrollbars=yes,width='+winwid+',height='+winhei+',left='+((scrwid-winwid)/2)+',top=100,directories=no,location=no,resizable=yes,status=no,toolbar=no');
	newwin.document.open();
	newwin.document.write(prnttext);
	newwin.document.close();
	newwin.focus();
}
//-->
</script>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
		    <form method="post" action="adminmanufacturer.php" onsubmit="return checkform(this)">
		<?php	if(trim(@$_POST['act'])=='modify'){ ?>
			<input type="hidden" name="act" value="domodify" />
		<?php	}else{ ?>
			<input type="hidden" name="act" value="doaddnew" />
		<?php	} ?>
			<input type="hidden" name="mfID" value="<?php print $mfID?>" />
			  <table width="100%" border="0" cellspacing="0" cellpadding="3">
				<tr>
				  <td width="100%" align="center" colspan="3"><strong><?php print $yyMFAdm?></strong><br /></td>
				</tr>
				<tr>
				  <td width="20%" align="right"><strong><?php print $redasterix.$yyName?>:</strong></td>
				  <td width="30%" align="left"><input type="text" name="name" size="20" value="<?php print htmlspecials($mfName)?>" /></td>
				  <td align="center" rowspan="8"><strong>Description</strong><br />
				  <textarea name="mfdescription" cols="38" rows="8"><?php print htmlspecials($mfDescription[1])?></textarea><br />
<?php		for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 16384)==16384){ ?>
					<strong>Description <?php print $index?></strong><br />
					<textarea name="mfdescription<?php print $index?>" cols="38" rows="8"><?php print htmlspecials($mfDescription[$index])?></textarea><br />
<?php			}
			} ?>
			&nbsp;<br />
					<strong>Static Page URL (Optional)</strong><br />
					<input type="text" name="mfurl" size="30" value="<?php print htmlspecials($mfURL[1])?>" /><br />
<?php		for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 8192)==8192){ ?>
					<strong>Static Page URL <?php print $index?> (Optional)</strong><br />
					<input type="text" name="mfurl<?php print $index?>" size="30" value="<?php print htmlspecials($mfURL[$index])?>" /><br />
<?php			}
			} ?>
				  </td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $yyAddress?>:</strong></td>
				  <td align="left"><input type="text" name="address" size="20" value="<?php print htmlspecials($mfAddress)?>" /></td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $yyCity?>:</strong></td>
				  <td align="left"><input type="text" name="city" size="20" value="<?php print htmlspecials($mfCity)?>" /></td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $yyState?>:</strong></td>
				  <td align="left"><input type="text" name="state" size="20" value="<?php print htmlspecials($mfState)?>" /></td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $yyCountry?>:</strong></td>
				  <td align="left"><select name="country" size="1">
<?php
function show_countries($tcountry){
	$sSQL = 'SELECT countryName FROM countries ORDER BY countryOrder DESC, countryName';
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs = mysql_fetch_array($result)){
		print "<option value='" . htmlspecials($rs['countryName']) . "'";
		if($tcountry==$rs['countryName'])
			print ' selected="selected"';
		print '>' . $rs['countryName'] . "</option>\n";
	}
	mysql_free_result($result);
}
show_countries($mfCountry);
?>
					</select>
				  </td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $yyZip?>:</strong></td>
				  <td align="left"><input type="text" name="zip" size="10" value="<?php print htmlspecials($mfZip)?>" /></td>
				</tr>
				<tr>
				  <td width="20%" align="right"><strong><?php print $yyEmail?>:</strong></td>
				  <td width="30%" align="left"><input type="text" name="email" size="25" value="<?php print htmlspecials($mfEmail)?>" /></td>
				</tr>
				<tr>
				  <td align="right"><strong>Manufacturer Logo:</strong></td>
				  <td align="left"><input type="text" name="mflogo" id="mflogo" size="30" value="<?php print htmlspecials($mfLogo)?>" /> <input type="button" name="smallimup" value="..." onclick="uploadimage('mflogo')" /></td>
				</tr>
				<tr>
				  <td align="center" colspan="3"><input type="submit" value="<?php print $yySubmit?>" /> <input type="reset" value="<?php print $yyReset?>" /> </td>
				</tr>
				<tr><td width="100%" colspan="3">&nbsp;</td></tr>
			  </table>
			</form>
		  </td>
        </tr>
      </table>
<?php
}else{
	if(! is_numeric(@$_GET['pg']))
		$CurPage = 1;
	else
		$CurPage = (int)(@$_GET['pg']);
	$thetime=time() + ($dateadjust*60*60);
	if(@$_POST['sd'] != "")
		$sd = @$_POST['sd'];
	elseif(@$_GET['sd'] != "")
		$sd = @$_GET['sd'];
	else
		$sd = date($admindatestr, mktime(0, 0, 0, date("m",$thetime), 1, date("Y",$thetime)));
	if(@$_POST['ed'] != "")
		$ed = @$_POST['ed'];
	elseif(@$_GET['ed'] != "")
		$ed = @$_GET['ed'];
	else
		$ed = date($admindatestr, $thetime);
	$sd = parsedate($sd);
	$ed = parsedate($ed);
	if($sd > $ed) $ed = $sd;
?>
<script language="javascript" type="text/javascript">
<!--
function chi(currindex) {
	var i = eval("document.mainform.newpos"+currindex+".selectedIndex");
	document.mainform.newval.value = eval("document.mainform.newpos"+currindex+".options[i].text");
	document.mainform.selectedq.value = currindex;
	document.mainform.act.value = "changepos";
	document.mainform.submit();
}
function modrec(id) {
	document.mainform.id.value = id;
	document.mainform.act.value = "modify";
	document.mainform.submit();
}
function newrec(id) {
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.submit();
}
function delrec(id) {
cmsg = "<?php print $yyConDel?>\n"
if (confirm(cmsg)) {
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.submit();
}
}
// -->
</script>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%" align="center">
			<table width="80%" border="0" cellspacing="0" cellpadding="2">
			  <tr>
				<td width="100%" align="center" colspan="4"><strong><?php print $yyMFAdm?></strong><br /></td>
			  </tr>
			  <form name="mainform" method="post" action="adminmanufacturer.php">
				<tr>
				  <td width="5%"><strong><?php print $yyOrder?></strong></td>
				  <td width="10%"><strong><?php print $yyID?></strong></td>
				  <td align="left"><strong><?php print $yyName?></strong></td>
				  <td width="10%"><strong><?php print $yyModify?></strong></td>
				  <td width="10%"><strong><?php print $yyDelete?></strong></td>
				</tr>
				<input type="hidden" name="id" value="xxx" />
				<input type="hidden" name="act" value="xxxxx" />
				<input type="hidden" name="selectedq" value="1" />
				<input type="hidden" name="newval" value="1" />
<?php
	$sSQL = 'SELECT mfID,mfName,mfEmail FROM manufacturer ORDER BY ' . (@$sortcategoriesalphabetically ? 'mfName' : 'mfOrder');
	$alldata = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($alldata)==0){
?>
				<tr>
				  <td width="100%" align="center" colspan="4"><br />&nbsp;<br /><strong><?php print $yyNoAff?></strong><br />&nbsp;</td>
				</tr>
<?php
	}else{
		$bgcolor='';
		$rowcounter=0;
		$numids = mysql_num_rows($alldata);
		while($rs=mysql_fetch_array($alldata)){
			if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark';
?>
				<tr class="<?php print $bgcolor?>">
				  <td><?php
				if(@$sortcategoriesalphabetically)
					print '-';
				else
					print writeposition(($maxcatsperpage*($CurPage-1))+$rowcounter+1,$numids);?></td>
				  <td><?php print $rs['mfID']?></td>
				  <td align="left"><?php print htmlspecials($rs['mfName'])?></td>
				  <td><input type="button" value="Modify" onclick="modrec('<?php print $rs['mfID']?>')" /></td>
				  <td><input type="button" value="Delete" onclick="delrec('<?php print $rs['mfID']?>')" /></td>
				</tr><?php
			$rowcounter++;
		}
	}
	mysql_free_result($alldata); ?>
				<tr> 
				  <td width="100%" colspan="4" align="center"><br /><input type="button" value="<?php print $yyAddNew?>" onclick="newrec()" /><br />&nbsp;</td>
				</tr>
				<tr> 
				  <td width="100%" colspan="4" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
				</tr>
			  </form>
			</table>
		  </td>
        </tr>
      </table>
<?php
}
?>