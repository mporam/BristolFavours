<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if($_SESSION["loggedon"] != $storesessionvalue || @$disallowlogin==TRUE) exit;
$success=TRUE;
if(@$admincatsperpage=='')$admincatsperpage = 200;
if(@$maxloginlevels=='') $maxloginlevels=5;
$dorefresh=FALSE;
if(strtolower($adminencoding)=='iso-8859-1') $raquo='»'; else $raquo='>';
function writemenulevel($id,$itlevel){
	global $allcatsa,$numcats,$thecat,$raquo;
	if($itlevel<10){
		for($wmlindex=0; $wmlindex < $numcats; $wmlindex++){
			if($allcatsa[$wmlindex][2]==$id){
				print "<option value='" . $allcatsa[$wmlindex][0] . "'";
				if($thecat==$allcatsa[$wmlindex][0]) print ' selected="selected">'; else print ">";
				for($index = 0; $index < $itlevel-1; $index++)
					print $raquo . ' ';
				print $allcatsa[$wmlindex][1] . "</option>\n";
				if($allcatsa[$wmlindex][3]==0) writemenulevel($allcatsa[$wmlindex][0],$itlevel+1);
			}
		}
	}
}
$sSQL = "";
$alldata = "";
$alreadygotadmin = getadminsettings();
if(@$defaultcatimages=='') $defaultcatimages = 'images/';
if(@$_POST['act']=='changepos'){
	$theid = (int)@$_POST['id'];
	$neworder = ((int)@$_POST['newval'])-1;
	$sSQL = "SELECT sectionOrder,topSection FROM sections WHERE sectionID=" . $theid;
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)) $topsection = $rs['topSection'];
	mysql_free_result($result);
	$rc=0;
	if(@$menucategoriesatroot && $catalogroot!=0){
		$sSQL="SELECT sectionID,topSection FROM sections WHERE (sectionID=".$topsection." OR topSection=".$topsection.") AND sectionID=".$catalogroot;
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)) $topsection=$rs['sectionID'].','.$rs['topSection'];
		mysql_free_result($result);
	}
	$sSQL='SELECT sectionID,sectionOrder FROM sections WHERE topSection IN ('.$topsection.') ORDER BY sectionOrder';
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs = mysql_fetch_assoc($result)){
		if($rs['sectionID']==$theid)
			$sSQL = "UPDATE sections SET sectionOrder=".$neworder." WHERE sectionID=".$theid;
		else
			$sSQL = "UPDATE sections SET sectionOrder=".($rc<$neworder?$rc:$rc+1)." WHERE sectionID=".$rs['sectionID'];
		mysql_query($sSQL) or print(mysql_error());
		$rc++;
	}
	mysql_free_result($result);
	$dorefresh=TRUE;
}elseif(@$_POST['posted']=='1'){
	if(@$_POST['act']=='delete'){
		$sSQL = "DELETE FROM cpnassign WHERE cpaType=1 AND cpaAssignment='" . @$_POST['id'] . "'";
		mysql_query($sSQL) or print(mysql_error());
		$sSQL = "DELETE FROM sections WHERE sectionID=" . @$_POST['id'];
		mysql_query($sSQL) or print(mysql_error());
		$sSQL = "DELETE FROM multisections WHERE pSection=" . @$_POST['id'];
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}elseif(@$_POST['act']=='domodify'){
		$olddisabled=0;
		$sSQL = "SELECT sectionDisabled FROM sections WHERE sectionID=" . @$_POST["id"];
		$result=mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)){
			$olddisabled = $rs['sectionDisabled'];
		}
		mysql_free_result($result);
		$sSQL = "UPDATE sections SET sectionName='" . escape_string(unstripslashes(@$_POST["secname"])) . "',sectionDescription='" . escape_string(unstripslashes(@$_POST["secdesc"])) . "',sectionImage='" . escape_string(unstripslashes(@$_POST["secimage"])) . "',topSection=" . @$_POST["tsTopSection"] . ",rootSection=" . @$_POST["catfunction"];
		$workname = escape_string(unstripslashes(@$_POST["secworkname"]));
		if($workname != "")
			$sSQL .= ",sectionWorkingName='" . $workname . "'";
		else
			$sSQL .= ",sectionWorkingName='" . escape_string(unstripslashes(@$_POST["secname"])) . "'";
		for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 256)==256) $sSQL .= ",sectionName" . $index . "='" . escape_string(unstripslashes(@$_POST["secname" . $index])) . "'";
			if(($adminlangsettings & 512)==512) $sSQL .= ",sectionDescription" . $index . "='" . escape_string(unstripslashes(@$_POST["secdesc" . $index])) . "'";
			if(($adminlangsettings & 2048)==2048) $sSQL .= ',sectionurl' . $index . "='" . escape_string(unstripslashes(@$_POST['sectionurl' . $index])) . "'";
		}
		$sSQL .= ",sectionDisabled=" . @$_POST['sectionDisabled'];
		$sSQL .= ",sectionurl='" . escape_string(unstripslashes(@$_POST["sectionurl"])) . "'";
		$sSQL .= " WHERE sectionID=" . @$_POST["id"];
		mysql_query($sSQL) or print(mysql_error());
		if(@$_POST['catalogroot']=='ON'){
			if($catalogroot!=(int)@$_POST['id'])
				mysql_query('UPDATE admin SET catalogRoot='.@$_POST['id'].' WHERE adminID=1') or print(mysql_error());
		}else{
			if($catalogroot==(int)@$_POST['id'])
				mysql_query('UPDATE admin SET catalogRoot=0 WHERE adminID=1') or print(mysql_error());
		}
		if(($olddisabled!=(int)@$_POST['sectionDisabled'] || $_POST['forcesubsection']=='1') && $_POST['forcesubsection']!='2'){
			$idlist=@$_POST['id'];
			mysql_query('UPDATE sections SET sectionDisabled=' . @$_POST['sectionDisabled'] . ' WHERE topSection=' . $idlist) or print(mysql_error());
			for($index=1; $index<=10; $index++){
				$sSQL='SELECT sectionID,sectionDisabled,rootSection FROM sections WHERE rootSection=0 AND topSection IN (' . $idlist . ')';
				$idlist='';
				$result=mysql_query($sSQL) or print(mysql_error());
				while($rs = mysql_fetch_assoc($result)){
					$sSQL='UPDATE sections SET sectionDisabled=' . @$_POST['sectionDisabled'] . ' WHERE topSection=' . $rs['sectionID'];
					mysql_query($sSQL) or print(mysql_error());
					$idlist.=$rs['sectionID'].',';
				}
				mysql_free_result($result);
				if($idlist!='') $idlist=substr($idlist,0,-1); else break;
			}
		}
		$dorefresh=TRUE;
	}elseif(@$_POST["act"]=="doaddnew"){
		$sSQL = "SELECT MAX(sectionOrder) AS mxOrder FROM sections";
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_assoc($result);
		$mxOrder = $rs["mxOrder"];
		if(is_null($mxOrder) || $mxOrder=="") $mxOrder=1; else $mxOrder++;
		mysql_free_result($result);
		$sSQL = "INSERT INTO sections (sectionName,sectionDescription,sectionImage,sectionOrder,topSection,rootSection,sectionWorkingName";
		for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 256)==256) $sSQL .= ",sectionName" . $index;
			if(($adminlangsettings & 512)==512) $sSQL .= ",sectionDescription" . $index;
			if(($adminlangsettings & 2048)==2048) $sSQL .= ',sectionurl' . $index;
		}
		$sSQL .= ",sectionDisabled,sectionurl) VALUES ('" . escape_string(unstripslashes(@$_POST["secname"])) . "','" . escape_string(unstripslashes(@$_POST["secdesc"])) . "','" . escape_string(unstripslashes(@$_POST["secimage"])) . "'," . $mxOrder . "," . @$_POST["tsTopSection"] . "," . @$_POST["catfunction"];
		$workname = escape_string(unstripslashes(@$_POST["secworkname"]));
		if($workname != "")
			$sSQL .= ",'" . $workname . "'";
		else
			$sSQL .= ",'" . escape_string(unstripslashes(@$_POST["secname"])) . "'";
		for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 256)==256) $sSQL .= ",'" . escape_string(unstripslashes(@$_POST["secname" . $index])) . "'";
			if(($adminlangsettings & 512)==512) $sSQL .= ",'" . escape_string(unstripslashes(@$_POST["secdesc" . $index])) . "'";
			if(($adminlangsettings & 2048)==2048) $sSQL .= ",'" . escape_string(unstripslashes(@$_POST["sectionurl" . $index])) . "'";
		}
		$sSQL .= "," . trim(@$_POST["sectionDisabled"]);
		$sSQL .= ",'" . escape_string(unstripslashes(@$_POST["sectionurl"])) . "')";
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}elseif(@$_POST["act"]=="dodiscounts"){
		$sSQL = "INSERT INTO cpnassign (cpaCpnID,cpaType,cpaAssignment) VALUES (" . @$_POST["assdisc"] . ",1,'" . @$_POST["id"] . "')";
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}elseif(@$_POST["act"]=="deletedisc"){
		$sSQL = "DELETE FROM cpnassign WHERE cpaID=" . @$_POST["id"];
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}elseif(@$_POST['act']=='sort'){
		setcookie('catsort', @$_POST['sort'], time()+31536000, '/', '', @$_SERVER['HTTPS']=='on');
	}
}elseif(@$_GET['catorman']!=''){
	setcookie('ccatorman', @$_GET['catorman'], time()+80000000, '/', '', @$_SERVER['HTTPS']=='on');
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="'.(@$_POST['act']=='changepos'?0:1).'; url=admincats.php';
	print '?stext=' . urlencode(@$_REQUEST['stext']) . '&catfun=' . @$_REQUEST['catfun'] . '&stype=' . @$_REQUEST['stype'] . '&scat=' . @$_REQUEST['scat'] . '&pg=' . @$_REQUEST['pg'];
	print '" />' . "\r\n";
}else{
?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function formvalidator(theForm){
  if(theForm.secname.value == ""){
    alert("<?php print $yyPlsEntr?> \"<?php print $yyCatNam?>\".");
    theForm.secname.focus();
    return (false);
  }
  if(theForm.tsTopSection[theForm.tsTopSection.selectedIndex].value == ""){
    alert("<?php print $yyPlsSel?> \"<?php print $yyCatSub?>\".");
    theForm.tsTopSection.focus();
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
	prnttext += '<tr><td align="center" colspan="2">&nbsp;<br /><strong><?php print str_replace("'", "\\'", $yyUplIma)?></strong><br />&nbsp;</td></tr>';
	prnttext += '<tr><td align="center" colspan="2"><?php print str_replace("'", "\\'", $yyPlsSUp)?><br />&nbsp;</td></tr>';
	prnttext += '<tr><td align="center" colspan="2"><?php print str_replace("'", "\\'", $yyLocIma)?>:<input type="file" name="imagefile" /></td></tr>';
<?php	if(extension_loaded('gd')){
			$winhei = 260; ?>
	prnttext += '<tr><td colspan="2">&nbsp;</td></tr><tr><td align="right"><select size="1" name="thumbdim0" id="thumbdim0"><option value="">Don\'t Resize Image</option><option value="1">Resize to Width:</option><option value="2">Resize to Height:</option></select></td><td><input type="text" name="newdim0" id="newdim0" size="3" />:px&nbsp;&nbsp;</td></tr>';
<?php	}else
			$winhei = 200; ?>
	prnttext += '<tr><td colspan="2" align="center">&nbsp;<br /><input type="submit" value="<?php print str_replace("'", "\\'", $yySubmit)?>" /></td></tr>';
	prnttext += '</table></form>';
	prnttext += '<p align="center"><a href="javascript:window.close()"><strong><?php print str_replace("'", "\\'", $yyClsWin)?></strong></a></p>';
	prnttext += '</body></html>';
	var scrwid=screen.width; var scrhei=screen.height;
	var newwin = window.open("","uploadimage",'menubar=no,scrollbars=yes,width='+winwid+',height='+winhei+',left='+((scrwid-winwid)/2)+',top=100,directories=no,location=no,resizable=yes,status=no,toolbar=no');
	newwin.document.open();
	newwin.document.write(prnttext);
	newwin.document.close();
	newwin.focus();
}
/* ]]> */
</script>
<?php
} ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
<?php
if(@$_POST["posted"]=="1" && (@$_POST["act"]=="modify" || @$_POST["act"]=="addnew")){
		$ntopsections=0;
		$sectionID = "";
		$sectionName = "";
		$sectionDescription = "";
		for($index=2; $index <= $adminlanguages+1; $index++){
			$sectionNames[$index] = "";
			$sectionDescriptions[$index] = "";
			$sectionurls[$index] = "";
		}
		$sectionImage = "";
		$sectionWorkingName = "";
		$topSection = 0;
		$sectionDisabled = 0;
		$rootSection = 1;
		$sectionurl = '';
		$sSQL = "SELECT sectionID, sectionWorkingName FROM sections WHERE rootSection=0 ORDER BY sectionWorkingName";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_assoc($result))
			$alltopsections[$ntopsections++] = $rs;
		mysql_free_result($result);
		if(@$_POST["act"]=="modify" && is_numeric(@$_POST["id"])){
			$sSQL = "SELECT sectionID,sectionName,sectionName2,sectionName3,sectionDescription,sectionDescription2,sectionDescription3,sectionImage,sectionWorkingName,topSection,sectionDisabled,rootSection,sectionurl";
			if(($adminlangsettings & 2048)==2048){
				if($adminlanguages>=1) $sSQL .= ',sectionurl2';
				if($adminlanguages>=2) $sSQL .= ',sectionurl3';
			}
			$sSQL .= " FROM sections WHERE sectionID=" . @$_POST["id"];
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$sectionID = $rs["sectionID"];
				$sectionName = $rs["sectionName"];
				$sectionDescription = $rs["sectionDescription"];
				for($index=2; $index <= $adminlanguages+1; $index++){
					$sectionNames[$index] = $rs["sectionName" . $index];
					$sectionDescriptions[$index] = $rs["sectionDescription" . $index];
					if(($adminlangsettings & 2048)==2048) $sectionurls[$index] = $rs['sectionurl' . $index];
				}
				$sectionImage = $rs["sectionImage"];
				$sectionWorkingName = $rs["sectionWorkingName"];
				$topSection = $rs["topSection"];
				$sectionDisabled = $rs["sectionDisabled"];
				$rootSection = $rs["rootSection"];
				$sectionurl = $rs["sectionurl"];
			}
			mysql_free_result($result);
		}
?>
        <tr>
		  <td width="100%">
		  <form name="mainform" method="post" action="admincats.php" onsubmit="return formvalidator(this)">
			<input type="hidden" name="posted" value="1" />
			<?php if(@$_POST['act']=='modify'){ ?>
			<input type="hidden" name="act" value="domodify" />
			<?php }else{ ?>
			<input type="hidden" name="act" value="doaddnew" />
			<?php }
			writehiddenvar('stext', @$_POST['stext']);
			writehiddenvar('stype', @$_POST['stype']);
			writehiddenvar('catfun', @$_POST['catfun']);
			writehiddenvar('scat', @$_POST['scat']);
			writehiddenvar('pg', @$_POST['pg']);
			writehiddenvar('id', @$_POST['id']); ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><strong><?php print $yyCatAdm?></strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td width="40%" align="center" valign="top"><strong><?php print $yyCatNam?></strong><br /><input type="text" name="secname" size="30" value="<?php print str_replace("\"","&quot;",$sectionName)?>" /><br />
<?php	for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 256)==256){ ?>
				<strong><?php print $yyCatNam . " " . $index ?></strong><br />
				<input type="text" name="secname<?php print $index?>" size="30" value="<?php print str_replace('"','&quot;',$sectionNames[$index])?>" /><br />
<?php		}
		} ?>
                </td>
				<td width="60%" rowspan="9" align="center" valign="top"><strong><?php print $yyCatDes?></strong><br /><textarea name="secdesc" cols="38" rows="8"><?php print $sectionDescription?></textarea><br />
<?php	for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 512)==512){ ?>
				<strong><?php print $yyCatDes . " " . $index ?></strong><br />
				<textarea name="secdesc<?php print $index?>" cols="38" rows="8"><?php print $sectionDescriptions[$index]?></textarea><br />
<?php		}
		} ?>
				&nbsp;<br /><select name="sectionDisabled" size="1">
				<option value="0"><?php print $yyNoRes?></option>
<?php	for($index=1; $index<= $maxloginlevels; $index++){
						print '<option value="' . $index . '"';
						if($sectionDisabled==$index) print ' selected="selected"';
						print '>' . $yyLiLev . ' ' . $index . '</option>';
		} ?>
				<option value="127"<?php if($sectionDisabled==127) print ' selected="selected"'?>><?php print $yyDisCat?></option>
				</select>
<?php	if(@$_POST['act']=='modify'){ ?>
				<select name="forcesubsection" size="1">
				<option value="0"><?php print $yySSForM?></option>
				<option value="1"><?php print $yySSForF?></option>
				<option value="2"><?php print $yySSForN?></option>
				</select>
<?php	} ?>
				<br />
				&nbsp;<br /><strong><?php print $yyCatURL.' ('.$yyOptnl.')'?></strong><br />
				<input type="text" name="sectionurl" size="40" value="<?php print str_replace('"','&quot;',$sectionurl)?>" /><br />
<?php	for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 2048)==2048){ ?>
				<strong><?php print $yyCatURL.' '.$index.' ('.$yyOptnl.')'?></strong><br />
				<input type="text" name="sectionurl<?php print $index?>" size="40" value="<?php print str_replace('"','&quot;',$sectionurls[$index])?>" /><br />
<?php		}
		} ?>
				<br />
				<input type="checkbox" name="catalogroot" value="ON" <?php if($catalogroot==$sectionID) print 'checked="checked" '?>/> <strong>(Optional) Check to make this category the product catalog root.</strong>
                </td>
			  </tr>
			  <tr>
				<td align="center" valign="top"><strong><?php print $yyCatWrNa?></strong></td>
			  </tr>
			  <tr>
				<td align="center" valign="top"><input type="text" name="secworkname" size="30" value="<?php print str_replace("\"","&quot;",$sectionWorkingName)?>" /></td>
			  </tr>
			  <tr>
				<td align="center" valign="top"><strong><?php print $yyCatSub?></strong></td>
			  </tr>
			  <tr>
				<td align="center" valign="top"><select name="tsTopSection" size="1"><option value="0"><?php print $yyCatHom?></option>
				<?php	$foundcat=($topSection==0);
						for($index=0;$index<$ntopsections; $index++){
							if($alltopsections[$index]["sectionID"] != $sectionID){
								print '<option value="' . $alltopsections[$index]["sectionID"] . '"';
								if($topSection==$alltopsections[$index]["sectionID"]){
									print ' selected="selected"';
									$foundcat=TRUE;
								}
								print ">" . $alltopsections[$index]["sectionWorkingName"] . "</option>\n";
							}
						}
						if(! $foundcat) print '<option value="" selected="selected">**undefined**</option>';
					?></select>
                </td>
			  </tr>
			  <tr>
				<td align="center" valign="top"><strong><?php print $yyCatFn?></strong></td>
			  </tr>
			  <tr>
				<td align="center" valign="top"><select name="catfunction" size="1">
				  <option value="1"><?php print $yyCatPrd?></option>
				  <option value="0" <?php if($rootSection==0) print 'selected="selected"'?>><?php print $yyCatCat?></option>
				  </select></td>
			  </tr>
			  <tr>
				<td align="center" valign="top"><strong><?php print $yyCatImg?></strong></td>
			  </tr>
			  <tr>
				<td align="center" valign="top"><input type="text" name="secimage" id="secimage" size="30" value="<?php print str_replace("\"","&quot;",$sectionImage)?>" /> <input type="button" name="smallimup" value="..." onclick="uploadimage('secimage')" /></td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" /></td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="2"><br /><ul>
				  <li><?php print $yyCatEx1?></li>
				  <li><?php print $yyCatEx2?></li>
				  </ul></td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="2" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
<?php
}elseif(@$_POST["act"]=="discounts"){
		$sSQL = "SELECT sectionName FROM sections WHERE sectionID=" . @$_POST["id"];
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_assoc($result);
		$thisname=$rs["sectionName"];
		mysql_free_result($result);
		$numassigns=0;
		$sSQL = "SELECT cpaID,cpaCpnID,cpnWorkingName,cpnSitewide,cpnEndDate,cpnType FROM cpnassign LEFT JOIN coupons ON cpnassign.cpaCpnID=coupons.cpnID WHERE cpaType=1 AND cpaAssignment='" . @$_POST["id"] . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs=mysql_fetch_assoc($result))
			$alldata[$numassigns++]=$rs;
		mysql_free_result($result);
		$numcoupons=0;
		$sSQL = "SELECT cpnID,cpnWorkingName,cpnSitewide FROM coupons WHERE (cpnSitewide=0 OR cpnSitewide=3) AND cpnEndDate >='" . date("Y-m-d",time()) ."'";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs=mysql_fetch_assoc($result))
			$alldata2[$numcoupons++]=$rs;
		mysql_free_result($result);
?>
        <tr>
		  <td width="100%">
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function delrec(id) {
cmsg = "<?php print $yyConAss?>\n"
if (confirm(cmsg)) {
	document.mainform.id.value = id;
	document.mainform.act.value = "deletedisc";
	document.mainform.submit();
}
}
/* ]]> */
</script>
		  <form name="mainform" method="post" action="admincats.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="dodiscounts" />
			<input type="hidden" name="id" value="<?php print @$_POST["id"]?>" />
			<input type="hidden" name="pg" value="<?php print @$_POST["pg"]?>" />
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="4" align="center"><strong><?php print $yyAssDis?> &quot;<?php print $thisname?>&quot;.</strong><br />&nbsp;</td>
			  </tr>
<?php
	$gotone=FALSE;
	if($numcoupons>0){
		$thestr = '<tr><td colspan="4" align="center">' . $yyAsDsCp . ': <select name="assdisc" size="1">';
		for($index=0;$index < $numcoupons;$index++){
			$alreadyassign=FALSE;
			if($numassigns>0){
				for($index2=0;$index2<$numassigns;$index2++){
					if($alldata2[$index]["cpnID"]==$alldata[$index2]["cpaCpnID"]) $alreadyassign=TRUE;
				}
			}
			if(! $alreadyassign){
				$thestr .= "<option value='" . $alldata2[$index]["cpnID"] . "'>" . $alldata2[$index]["cpnWorkingName"] . "</option>\n";
				$gotone=TRUE;
			}
		}
		$thestr .= '</select> <input type="submit" value="'.$yyGo.'" /></td></tr>';
	}
	if($gotone){
		print $thestr;
	}else{
?>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br /><strong><?php print $yyNoDis?></td>
			  </tr>
<?php
	}
	if($numassigns>0){
?>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br /><strong><?php print $yyCurDis?> &quot;<?php print $thisname?>&quot;.</strong><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td><strong><?php print $yyWrkNam?></strong></td>
				<td><strong><?php print $yyDisTyp?></strong></td>
				<td><strong><?php print $yyExpire?></strong></td>
				<td align="center"><strong><?php print $yyDelete?></strong></td>
			  </tr>
<?php
		for($index=0;$index<$numassigns;$index++){
			$prefont = "";
			$postfont = "";
			if((int)$alldata[$index]["cpnSitewide"]==1 || ($alldata[$index]["cpnEndDate"] != '3000-01-01 00:00:00' && strtotime($alldata[$index]["cpnEndDate"])-time() < 0)){
				$prefont = '<span style="color:#FF0000">';
				$postfont = '</span>';
			}
?>
			  <tr> 
                <td><?php	print $prefont . $alldata[$index]["cpnWorkingName"] . $postfont ?></td>
				<td><?php	if($alldata[$index]["cpnType"]==0)
								print $prefont . $yyFrSShp . $postfont;
							elseif($alldata[$index]["cpnType"]==1)
								print $prefont . $yyFlatDs . $postfont;
							elseif($alldata[$index]["cpnType"]==2)
								print $prefont . $yyPerDis . $postfont; ?></td>
				<td><?php	print $prefont;
							if($alldata[$index]["cpnEndDate"] == '3000-01-01 00:00:00')
								print $yyNever;
							elseif(strtotime($alldata[$index]["cpnEndDate"])-time() < 0)
								print $yyExpird;
							else
								print date("Y-m-d",strtotime($alldata[$index]["cpnEndDate"]));
							print $postfont; ?></td>
				<td align="center"><input type="button" name="discount" value="Delete Assignment" onclick="delrec('<?php print $alldata[$index]["cpaID"]?>')" /></td>
			  </tr>
<?php
		}
	}else{
?>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br /><strong><?php print $yyNoAss?></strong></td>
			  </tr>
<?php
	}
?>
			  <tr>
                <td width="100%" colspan="4" align="center"><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
<?php
}elseif(@$_POST["act"]=="changepos"){ ?>
        <tr>
          <td width="100%" align="center">
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p><strong><?php print $yyUpdat?> . . . . . . . </strong></p>
			<p>&nbsp;</p>
			<p><?php print $yyNoFor?> <a href="admincats.php"><?php print $yyClkHer?></a>.</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
		  </td>
		</tr>
<?php
}elseif(@$_POST['posted']=='1' && @$_POST['act']!='sort' && $success){ ?>
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="admincats.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />
				<img src="../images/clearpixel.gif" width="300" height="3" alt="" />
                </td>
			  </tr>
			</table></td>
        </tr>
<?php
}elseif(@$_POST['posted']=='1' && @$_POST['act']!='sort'){ ?>
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table></td>
        </tr>
<?php
}else{
	$pract='';
	if(@$_POST['sort']!='') $sortorder = $_POST['sort']; else $sortorder = @$_COOKIE['catsort'];
	if(@$_GET['catorman']!='') $catorman = $_GET['catorman']; else $catorman = @$_COOKIE['ccatorman'];
	$allcoupon="";
	$numcoupons=0;
	$sSQL = "SELECT DISTINCT cpaAssignment FROM cpnassign WHERE cpaType=1";
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs=mysql_fetch_array($result))
		$allcoupon[$numcoupons++]=$rs;
	mysql_free_result($result);
?>
        <tr>
		  <td width="100%">
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
var rowsingrp=[];
function cpu(x,theid,grpid,secid){
	if(x.length>1) return;
	x.onchange=function(){chi(secid,x);};
	var totrows=rowsingrp[grpid];
	for(index=theid-1; index>0; index--){
		var y=document.createElement('option');
		y.text=index;
		y.value=index;
		var sel=x.options[0];
		try{
			x.add(y, sel); // FF etc
		}
		catch(ex){
			x.add(y, 0); // IE
		}
	}
	for(index=theid+1; index<=totrows; index++){
		var y=document.createElement('option');
		y.text=index;
		y.value=index;
		try{
			x.add(y, null); // FF etc
		}
		catch(ex){
			x.add(y); // IE
		}
	}
}
function chi(id,obj){
	document.mainform.action="admincats.php?catfun=<?php print @$_REQUEST['catfun']?>&stext=<?php print urlencode(@$_REQUEST['stext'])?>&sprice=<?php print urlencode(@$_REQUEST['sprice'])?>&stype=<?php print @$_REQUEST['stype']?>&scat=<?php print @$_REQUEST['scat']?>&pg=<?php print (@$_GET['pg']=='' ? 1 : @$_GET['pg'])?>";
	document.mainform.newval.value = obj.selectedIndex+1;
	document.mainform.id.value = id;
	document.mainform.act.value = "changepos";
	document.mainform.submit();
}
function mrk(id){
	document.mainform.action="admincats.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "modify";
	document.mainform.submit();
}
function newrec(id){
	document.mainform.action="admincats.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.submit();
}
function dsk(id) {
	document.mainform.action="admincats.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "discounts";
	document.mainform.submit();
}
function drk(id){
cmsg = "<?php print $yyConDel?>\n"
if (confirm(cmsg)) {
	document.mainform.action="admincats.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.submit();
}
}
function startsearch(){
	document.mainform.action="admincats.php";
	document.mainform.act.value = "search";
	document.mainform.posted.value = "";
	document.mainform.submit();
}
function inventorymenu(){
	themenuitem=document.mainform.inventoryselect.options[document.mainform.inventoryselect.selectedIndex].value;
	if(themenuitem=="1") document.mainform.act.value = "catinventory";
	document.mainform.action="dumporders.php";
	document.mainform.submit();
}
function changesortorder(men){
	document.mainform.action="admincats.php<?php if(@$_POST['act']=='search' || @$_GET['pg']!='') print '?pg=1'?>";
	document.mainform.id.value = men.options[men.selectedIndex].value;
	document.mainform.act.value = "sort";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function switchcatorman(obj){
	document.location = "admincats.php?catorman="+obj[obj.selectedIndex].value+"&stext=<?php print urlencode(@$_REQUEST['stext'])?>&stype=<?php print @$_REQUEST['stype']?>&pg=<?php print (@$_GET['pg']=='' && @$_POST['act']=='search' ? 1 : @$_GET['pg'])?>";
}
/* ]]> */
</script>
<?php
$numcats=0;
$thecat = @$_REQUEST['scat'];
if($thecat!='') $thecat = (int)$thecat;
if(@$noadmincategorysearch!=TRUE){
	$sSQL = "SELECT sectionID,sectionWorkingName,topSection,rootSection FROM sections WHERE rootSection=0 ORDER BY sectionOrder";
	$allcats = mysql_query($sSQL) or print(mysql_error());
	while($row = mysql_fetch_row($allcats)){
		$allcatsa[$numcats++]=$row;
	}
	mysql_free_result($allcats);
} ?>
		  <form name="mainform" method="post" action="admincats.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="pg" value="<?php if(@$_POST['act']=='search') print '1'; else print @$_GET['pg']?>" />
			<input type="hidden" name="selectedq" value="1" />
			<input type="hidden" name="newval" value="1" />
				<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
				  <tr> 
	                <td class="cobhl" width="25%" align="right"><?php print $yySrchFr?>:</td>
					<td class="cobll" width="25%"><input type="text" name="stext" size="20" value="<?php print @$_REQUEST['stext']?>" /></td>
					<td class="cobhl" width="25%" align="right"><?php print str_replace('...','',$yyCatFn)?>:</td>
					<td class="cobll" width="25%"><select name="catfun" size="1">
						<option value=""><?php print $yySrchAC?></option>
						<option value="1"<?php if(@$_REQUEST['catfun']=='1') print ' selected="selected"'?>><?php print $yyCatPrd?></option>
						<option value="2"<?php if(@$_REQUEST['catfun']=='2') print ' selected="selected"'?>><?php print $yyCatCat?></option>
						<option value="3"<?php if(@$_REQUEST['catfun']=='3') print ' selected="selected"'?>>Restricted Categories</option>
						<option value="4"<?php if(@$_REQUEST['catfun']=='4') print ' selected="selected"'?>>Disabled Categories</option>
					</select></td>
				  </tr>
				  <tr>
				    <td class="cobhl" width="25%" align="right"><?php print $yySrchTp?>:</td>
					<td class="cobll" width="25%"><select name="stype" size="1">
						<option value=""><?php print $yySrchAl?></option>
						<option value="any"<?php if(@$_REQUEST['stype']=='any') print ' selected="selected"'?>><?php print $yySrchAn?></option>
						<option value="exact"<?php if(@$_REQUEST['stype']=='exact') print ' selected="selected"'?>><?php print $yySrchEx?></option>
						</select>
					</td>
					<td class="cobhl" width="25%" align="right"><select size="1" name="catorman" onchange="switchcatorman(this)">
						<option value="cat"><?php print $yySrchCt?></option>
						<option value="non"<?php if($catorman=='non') print ' selected="selected"'?>><?php print $yyNone?></option>
						</select></td>
					<td class="cobll" width="25%">
<?php	if($catorman=='non')
			print '&nbsp;';
		else{ ?>
					  <select name="scat" size="1">
					  <option value=""><?php print $yySrchAC?></option>
					<?php	writemenulevel(0,1); ?>
					  </select>
<?php	} ?>
					</td>
	              </tr>
				  <tr>
				    <td class="cobhl" align="center">&nbsp;</td>
				    <td class="cobll" colspan="3"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					    <tr>
						  <td class="cobll" align="center" style="white-space: nowrap">
							<select name="sort" size="1" onchange="changesortorder(this)">
							<option value="can"<?php if($sortorder=='can') print ' selected="selected"'?>>Sort - Cat Name</option>
							<option value="cwn"<?php if($sortorder=='cwn') print ' selected="selected"'?>>Sort - Working Name</option>
							<option value="act"<?php if($sortorder=='act'||$sortorder=='') print ' selected="selected"'?>>Sort - Actual Order</option>
							<option value="nsf"<?php if($sortorder=='nsf') print ' selected="selected"'?>>No Sort (Fastest)</option>
							</select>
							<input type="submit" value="List Categories" onclick="startsearch();" />
							<input type="button" value="<?php print $yyNewCat?>" onclick="newrec()" />
						  </td>
						  <td class="cobll" height="26" width="20%" align="right" style="white-space: nowrap">
						<select name="inventoryselect" size="1">
							<option value="1">Category Inventory</option>
						</select>&nbsp;<input type="button" value="<?php print $yyGo?>" onclick="javascript:inventorymenu();" />
						  </td>
						</tr>
					  </table></td>
				  </tr>
				</table>
            <table width="100%" border="0" cellspacing="0" cellpadding="1">
<?php
	if(@$_POST['act']=='search' || @$_GET['pg'] != ''){
		$CurPage = 1;
		$roottopsection=0;
		if(@$menucategoriesatroot){
			$sSQL="SELECT topSection FROM sections WHERE sectionID=".$catalogroot;
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)) $roottopsection=$rs['topSection'];
			mysql_free_result($result);
		}
		if(is_numeric(@$_GET['pg'])) $CurPage = (int)(@$_GET['pg']);
		$sSQL = "SELECT COUNT(*) AS bar FROM sections";
		$result = mysql_query($sSQL) or print(mysql_error());
		$totalcats = mysql_result($result,0,"bar");
		$iNumOfPages = ceil($totalcats/$admincatsperpage);
		mysql_free_result($result);
		if(! @$menucategoriesatroot)
			$sSQL = 'SELECT sec1.sectionID,sec1.sectionWorkingName,sec1.sectionDescription,sec1.topSection AS topSection,sec1.rootSection,sec1.sectionDisabled,sec1.sectionOrder FROM sections AS sec1 LEFT JOIN sections AS sec2 ON sec1.topSection=sec2.sectionID';
		else
			$sSQL = 'SELECT sec1.sectionID,sec1.sectionWorkingName,sec1.sectionDescription,IF(sec1.topSection='.$catalogroot.','.$roottopsection.',sec1.topSection) AS topSection,sec1.rootSection,sec1.sectionDisabled,sec1.sectionOrder FROM sections AS sec1 LEFT JOIN sections AS sec2 ON IF(sec1.topSection='.$catalogroot.','.$roottopsection.',sec1.topSection)=sec2.sectionID';
		$whereand=' WHERE ';
		if($thecat!=''){
			$sectionids = getsectionids($thecat, TRUE);
			if($sectionids!='')
				$sSQL .= $whereand . 'sec1.sectionID IN (' . $sectionids . ') ';
			$whereand=' AND ';
		}
		if(trim(@$_REQUEST['stext']) != ''){
			$Xstext = escape_string(@$_REQUEST['stext']);
			$aText = explode(' ',$Xstext);
			if(@$nosearchadmindescription) $maxsearchindex=0; else $maxsearchindex=1;
			$aFields[0]=getlangid('sectionName',256);
			$aFields[1]=getlangid('sectionDescription',512);
			if(@$_REQUEST['stype']=='exact'){
				$sSQL .= $whereand . "(sec1.sectionWorkingName LIKE '%".$Xstext."%' OR ";
				for($index=1; $index<=$adminlanguages+1; $index++){
					$sSQL .= 'sec1.sectionName'.($index==1?'':$index)." LIKE '%".$Xstext."%' OR sec1.sectionDescription".($index==1?'':$index)." LIKE '%".$Xstext."%'";
					if($index<$adminlanguages+1) $sSQL .= " OR ";
				}
				$sSQL .= ") ";
				$whereand=" AND ";
			}else{
				$sJoin='AND ';
				if(@$_REQUEST['stype']=='any') $sJoin='OR ';
				$sSQL .= $whereand . '(';
				$whereand=' AND ';
				for($index=0;$index<=$maxsearchindex;$index++){
					$sSQL .= '(';
					$rowcounter=0;
					$arrelms=count($aText);
					foreach($aText as $theopt){
						if(is_array($theopt))$theopt=$theopt[0];
						$sSQL .= 'sec1.' . $aFields[$index] . " LIKE '%" . $theopt . "%' ";
						if(++$rowcounter < $arrelms) $sSQL .= $sJoin;
					}
					$sSQL .= ') ';
					if($index < $maxsearchindex) $sSQL .= 'OR ';
				}
				$sSQL .= ') ';
			}
		}
		if(@$_REQUEST['catfun']=='1'){ $sSQL .= $whereand . "sec1.rootSection=1 "; $whereand=" AND "; }
		if(@$_REQUEST['catfun']=='2'){ $sSQL .= $whereand . "sec1.rootSection=0 "; $whereand=" AND "; }
		if(@$_REQUEST['catfun']=='3'){ $sSQL .= $whereand . "sec1.sectionDisabled<>0 "; $whereand=" AND "; }
		if(@$_REQUEST['catfun']=='4'){ $sSQL .= $whereand . "sec1.sectionDisabled=127 "; $whereand=" AND "; }
		if($sortorder=='can')
			$sSQL .= " ORDER BY sec1.sectionName";
		elseif($sortorder=='cwn')
			$sSQL .= " ORDER BY sec1.sectionWorkingName";
		elseif($sortorder=='nsf')
			; // Nothing
		else
			$sSQL .= " ORDER BY sec2.sectionOrder,sec2.sectionID,sec1.sectionOrder";
		$sSQL .= ' LIMIT ' . ($admincatsperpage*($CurPage-1)) . ', ' . $admincatsperpage;
		$currgroup=-1;
		$result = mysql_query($sSQL) or print(mysql_error());
		if($totalcats > 0){
			$islooping=FALSE;
			$noproducts=FALSE;
			$hascatinprodsection=FALSE;
			$rowcounter=0;
			$bgcolor="";
			$pblink = '<a href="admincats.php?scat='.@$_REQUEST['scat'].'&stext='.urlencode(@$_REQUEST['stext']).'&stype='.@$_REQUEST['stype'].'&catfun='.@$_REQUEST['catfun'].'&pg=';
			if($iNumOfPages > 1) print '<tr><td align="center" colspan="6">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '<br /><br /></td></tr>';
?>				  <tr>
					<td width="5%"><strong><?php print $yyOrder?></strong></td>
					<td align="left"><strong><?php print $yyCatPat?></strong></td>
					<td align="left"><strong><?php print $yyCatNam?></strong></td>
					<td width="5%" align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyDiscnt?></span></td>
					<td width="5%" align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyModify?></span></td>
					<td width="5%" align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyDelete?></span></td>
				  </tr>
<?php		$ordingroup=1;
			$rowsingrp='';
			$checkfirstgroup=($CurPage!=1);
			while($rs = mysql_fetch_assoc($result)){
				if($currgroup==-1) $currgroup=$rs['topSection'];
				if($currgroup!=$rs['topSection']){
					if($checkfirstgroup){
						$result2 = mysql_query('SELECT COUNT(*) AS catcnt FROM sections WHERE topSection='.$currgroup) or print(mysql_error());
						if($rs2 = mysql_fetch_assoc($result2)) $ordingroup=$rs2['catcnt']+1;
						mysql_free_result($result2);
						$checkfirstgroup=FALSE;
					}
					if($sortorder==''||$sortorder=='act') print '<tr><td colspan="6">&nbsp;</td></tr>';
					$rowsingrp.='rowsingrp['.$currgroup.']='.($ordingroup-1).";";
					$currgroup=$rs['topSection'];
					$ordingroup=1;
				}
				if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark'; ?>
<tr class="<?php print $bgcolor?>"><td><?php
				$currpos = min($totalcats,max(1,$rs['sectionOrder']));
				if($sortorder==''||$sortorder=='act'){
					print '<select onmouseover="cpu(this,'.$ordingroup.','.$rs['topSection'].','.$rs['sectionID'].')">';
					print '<option value="'.$currpos.'">'.$ordingroup.($ordingroup<100?'&nbsp;':'').'</option>';
					print '</select>';
				}else
					print '&nbsp;' ?></td><td style="font-size:10px"><?php
				$tslist = "";
				$thetopts = $rs["topSection"];
				for($index=0; $index <= 10; $index++){
					if($thetopts==0){
						$tslist = substr($tslist,3);
						break;
					}elseif($index==10){
						$tslist = '<span style="color:#FF0000;font-weight:bold">' . $yyLoop . '</span>' . $tslist;
						$islooping=TRUE;
					}else{
						$sSQL = "SELECT sectionID,topSection,sectionWorkingName,rootSection FROM sections WHERE sectionID=" . $thetopts;
						$result2 = mysql_query($sSQL) or print(mysql_error());
						if(mysql_num_rows($result2) > 0){
							$rs2 = mysql_fetch_assoc($result2);
							$errstart = '';
							$errend = '';
							if($rs2['rootSection']==1){
								$errstart = '<span style="color:#FF0000;font-weight:bold">';
								$errend = '</span>';
								$hascatinprodsection=TRUE;
							}
							$tslist = ' » ' . $errstart . $rs2['sectionWorkingName'] . $errend . $tslist;
							$thetopts = $rs2['topSection'];
						}else{
							$tslist = '<span style="color:#FF0000;font-weight:bold">' . $yyTopDel . '</span>' . $tslist;
							break;
						}
						mysql_free_result($result2);
					}
				}
				print $tslist . '</td><td>';
				if($rs['rootSection']==1) print '<strong>';
				if($rs['sectionDisabled']==127) print '<span style="color:#FF0000;text-decoration:line-through">';
				if($catalogroot==$rs['sectionID']) print '<span title="Catalog Root" style="padding-left:10px;text-decoration:underline overline;font-size:larger;">';
				print $rs['sectionWorkingName'] . ' (' . $rs['sectionID'] . ')';
				if($catalogroot==$rs['sectionID']) print '</span>';
				if($rs['sectionDisabled']==127) print '</span>';
				if($rs['rootSection']==1) print '</strong>';
				print '</td><td><input';
				for($index=0;$index<$numcoupons;$index++){
					if((int)$allcoupon[$index][0]==$rs['sectionID']){
						print ' style="color:#FF0000" ';
						break;
					}
				}
		?> type="button" value="<?php print $yyAssign?>" onclick="dsk(<?php print $rs["sectionID"]?>)" /></td>
<td><input type="button" value="<?php print $yyModify?>" onclick="mrk(<?php print $rs["sectionID"]?>)" /></td>
<td><input type="button" value="<?php print $yyDelete?>" onclick="drk(<?php print $rs["sectionID"]?>)" /></td>
</tr><?php	$rowcounter++;
				$ordingroup++;
			}
			if($iNumOfPages > 1) print '<tr><td align="center" colspan="6"><br />' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
			if($islooping){ ?>
				  <tr><td width="100%" colspan="6"><br /><span style="color:#FF0000;font-weight:bold">** </span><?php print $yyCatEx3?></td></tr>
<?php		}
			if($hascatinprodsection){ ?>
				  <tr><td width="100%" colspan="6"><br /><ul><li><?php print $yyCPErr?></li></ul></td></tr>
<?php		} ?>
				  <tr><td width="100%" colspan="6"><br /><ul><li><?php print $yyCatEx4?></li></ul></td></tr>
<?php	}else{ ?>
				  <tr><td width="100%" colspan="6" align="center"><br /><strong><?php print $yyCatEx5?><br />&nbsp;</td></tr>
<?php	}
		mysql_free_result($result);
		$result = mysql_query('SELECT COUNT(*) AS catcnt FROM sections WHERE topSection='.$currgroup) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)) $ordingroup=$rs['catcnt']+1;
		mysql_free_result($result);
		$rowsingrp.='rowsingrp['.$currgroup.']='.($ordingroup-1).';';
	}
?>
			  <tr>
                <td width="100%" colspan="6" align="center"><br />
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
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
<?php print @$rowsingrp?>
/* ]]> */
</script>