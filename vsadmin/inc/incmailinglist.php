<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if($_SESSION["loggedon"] != $storesessionvalue || @$disallowlogin==TRUE) exit;
$success=TRUE;
if(@$mailinglistpurgedays=='') $mailinglistpurgedays=32;
$alreadygotadmin = getadminsettings();
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
if(@$_POST['posted']=='1'){
	if(@$_POST['act']=='confirm'){
		$sSQL = "UPDATE mailinglist SET isconfirmed=1 WHERE email='" . escape_string(@$_POST['id']) . "'";
		mysql_query($sSQL) or print(mysql_error());
	}elseif(@$_POST['act']=='delete'){
		$sSQL = "DELETE FROM mailinglist WHERE email='" . escape_string(@$_POST['id']) . "'";
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}elseif(@$_POST['act']=='doaddnew'){
		$sSQL = "INSERT INTO mailinglist (email,mlName,isconfirmed,mlConfirmDate,mlIPAddress) VALUES ('" . escape_string(strtolower(@$_POST['email'])) . "','" . escape_string(@$_POST['mlname']) . "',1,'".date('Y-m-d', time())."','".escape_string(getipaddress())."')";
		@mysql_query($sSQL);
		$dorefresh=TRUE;
	}elseif(@$_POST['act']=='domodify'){
		$sSQL = "UPDATE mailinglist SET email='" . escape_string(strtolower(@$_POST['email'])) . "',mlName='" . escape_string(@$_POST['mlname']) . "' WHERE email='" . escape_string(strtolower(@$_POST['id'])) . "'";
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}elseif(@$_POST['act']=='purgeunconfirmed'){
		$sSQL = "DELETE FROM mailinglist WHERE isconfirmed=0 AND mlConfirmDate<'".date('Y-m-d', time()-($mailinglistpurgedays*60*60*24))."'";
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}elseif(@$_POST['act']=='clearsent'){
		$sSQL = "UPDATE mailinglist SET emailsent=0";
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=adminmailinglist.php';
	print '?stext=' . urlencode(@$_POST['stext']) . '&stype=' . urlencode(@$_POST['stype']) . '&listem=' . urlencode(@$_POST['listem']) . '&pg=' . urlencode(@$_POST['pg']);
	print '">';
}
if(@$_POST['posted']=='1' && @$_POST['act']=='dosendem'){
	@set_time_limit(1800);
?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		  <td width="100%" align="center">
		  <form name="mainform" method="post" action="adminmailinglist.php" onsubmit="return formvalidator(this)">
<?php		writehiddenvar("posted", "1");
			writehiddenvar("act", "dosendem");
			writehiddenvar("stext", @$_POST['stext']);
			writehiddenvar("listem", @$_POST['listem']);
			writehiddenvar("stype", @$_POST['stype']);
			writehiddenvar("pg", @$_POST['pg']);
			writehiddenvar("id", @$_POST['id']); ?>
            <table width="100%" border="0" cellspacing="2" cellpadding="1">
			  <tr>
                <td align="center"><strong><?php print $yyMaLiMa?> - Sending Emails</strong></td>
			  </tr>
			  <tr> 
                <td align="center"><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />
				<strong>Please do not refresh this page</strong>
				<br />&nbsp;<br />
				Sending email: <span name="sendspan" id="sendspan">0</span>
				<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;
				</td>
			  </tr>
			  <tr>
                <td align="center"><br />
                          <a href="adminmailinglist.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
      </table>
<?php
	if(@$_POST['emformat']=='1' || @$_POST['emformat']=='2') $htmlemails=TRUE; else $htmlemails=FALSE;
	$batchesof=@$_POST['batchesof'];
	setcookie('EMAILBATCHNUM', @$_POST['batchesof'], time()+86400000, '/', '', @$_SERVER['HTTPS']=='on');
	if(! is_numeric($batchesof) || $batchesof=='') $batchesof=0; else $batchesof=(int)$batchesof;
	if($htmlemails==TRUE) $emlNl = '<br />'; else $emlNl="\n";
	$theemail = unstripslashes(@$_POST['theemail']);
	$fromemail = unstripslashes(@$_POST['fromemail']);
	$unsubscribe = (@$_POST['unsubscribe']=='ON');
	$unsublink = '';
	print '</div>'; // to match the div that encloses this include file.
	$index = 1;
	if(@$customheaders == ''){
		$customheaders = "MIME-Version: 1.0\n";
		$customheaders .= "From: %from% <%from%>\n";
		if(@$htmlemails==TRUE)
			$customheaders .= 'Content-type: text/html; charset='.$emailencoding."\n";
		else
			$customheaders .= 'Content-type: text/plain; charset='.$emailencoding."\n";
	}else{
		if($htmlemails==TRUE)
			$customheaders = str_replace('text/plain', 'text/html', $customheaders);
		else
			$customheaders = str_replace('text/html', 'text/plain', $customheaders);
	}
	$rowcounter=0;
	$sSQL = 'SELECT email,mlName FROM mailinglist WHERE 1=1 ';
	if(@$_POST['sendto']=='0'){
		$sSQL = "SELECT adminEmail AS email,'Admin' AS mlName FROM admin";
		$batchesof=0;
	}elseif(trim(@$_POST['sendto'])=='1'){
		$sSQL .= "AND selected<>0 ";
	}elseif(trim(@$_POST['sendto'])=='2'){
		// Nothing - entire DB
	}elseif(trim(@$_POST['sendto'])=='3'){
		$sSQL = 'SELECT affilEmail AS email,affilName AS mlName FROM affiliates WHERE 1=1 ';
		$unsubscribe=FALSE;
	}
	if($batchesof!=0)
		$sSQL .= 'AND emailsent=0 ';
	if(@$_POST['sendto']!='0'&&@$_POST['sendto']!='3'){
		if(! @$noconfirmationemail==TRUE) $sSQL .= 'AND isconfirmed<>0 ';
		$sSQL .= ' ORDER BY email';
	}
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs = mysql_fetch_assoc($result)){
		if($unsubscribe){
			$unsublink = $emlNl . $emlNl . $yyToUnsu . $emlNl;
			$thelink = $storeurl . 'cart.php?unsubscribe=' . $rs['email'];
			if(@$htmlemails==TRUE) $thelink = '<a href="' . $thelink . '">' . $thelink . '</a>';
			$unsublink .= $thelink;
		}
		dosendemail($rs['email'], $fromemail, '', unstripslashes(@$_POST['emailsubject']), replaceemailtxt($theemail,'%name%',$rs['mlName']) . $unsublink);
		if($index % 50==0){
			print '<script language="javascript" type="text/javascript">document.getElementById(\'sendspan\').innerHTML=' . $index . ";</script>\r\n";
			flush();
		}
		if($batchesof!=0){
			$sSQL = "UPDATE mailinglist SET emailsent=1 WHERE email='".escape_string($rs['email'])."'";
			mysql_query($sSQL) or print(mysql_error());
			if($index>=$batchesof){ $index++; break; }
		}
		$index++;
	}
	mysql_free_result($result);
	print '<script language="javascript" type="text/javascript">document.getElementById(\'sendspan\').innerHTML=\'' . ($index-1) . " - All Done!';</script>\r\n";
	print '</body></html>';
	flush();
	exit;
}elseif(@$_POST['posted']=='1' && @$_POST['act']=='sendem'){ ?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function formvalidator(theForm){
<?php	if(@$htmleditor=='fckeditor'){ ?>
	if(wasusingfck){
		var inst = FCKeditorAPI.GetInstance("theemailfck");
		var sValue = inst.GetHTML();
		if(sValue=='<br />') sValue='';
		document.getElementById("theemail").value=sValue;
	}
<?php	} ?>
if (theForm.fromemail.value==""){
alert("<?php print $yyPlsEntr?> \"<?php print $yyFrmEm?>\".");
theForm.fromemail.focus();
return (false);
}
if (theForm.emailsubject.value==""){
alert("<?php print $yyPlsEntr?> \"<?php print $yySubjc?>\".");
theForm.emailsubject.focus();
return (false);
}
if (theForm.theemail.value==""){
alert("<?php print $yyPlsEntr?> \"<?php print $yyMessag?>\".");
if(!wasusingfck) theForm.theemail.focus();
return (false);
}
if (theForm.sendto.selectedIndex!=0){
	if(!confirm("<?php print str_replace('"','\"',$yyCanSpm)?>")){
		return(false);
	}
}
<?php	if(@$htmleditor=='fckeditor'){ ?>
	if(wasusingfck){
		document.getElementById("fckrow").style.display='none';
		document.getElementById("textarearow").style.display='';
	}
<?php	} ?>
return(true);
}
var wasusingfck=false;
function changeemailformat(obj){
<?php	if(@$htmleditor=='fckeditor'){ ?>
	var inst = FCKeditorAPI.GetInstance("theemailfck");
	if(obj.selectedIndex==2){
		if(!wasusingfck){
			inst.SetHTML(document.getElementById("theemail").value);
			document.getElementById("fckrow").style.display='';
			document.getElementById("textarearow").style.display='none';
		}
		wasusingfck=true;
	}else{
		if(wasusingfck){
			var sValue = inst.GetHTML();
			if(sValue=='<br />') sValue='';
			document.getElementById("theemail").value=sValue;
			document.getElementById("fckrow").style.display='none';
			document.getElementById("textarearow").style.display='';
		}
		wasusingfck=false;
	}
<?php	} ?>
}
/* ]]> */
</script>
<?php	if(@$htmleditor=='fckeditor'){ ?>
<script type="text/javascript" src="fckeditor.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
function FCKeditor_OnComplete(editorInstance){
	editorInstance.Events.AttachEvent('OnBlur', FCKeditor_OnBlur);
	editorInstance.Events.AttachEvent('OnFocus', FCKeditor_OnFocus);
	editorInstance.ToolbarSet.Collapse();
}
function FCKeditor_OnBlur(editorInstance){
	editorInstance.ToolbarSet.Collapse();
}
function FCKeditor_OnFocus(editorInstance){
	editorInstance.ToolbarSet.Expand();
}
var sBasePath = document.location.pathname.substring(0,document.location.pathname.lastIndexOf('adminmailinglist.php'));
/* ]]> */
</script>
<?php	} ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		  <td width="100%" align="center">
		  <form name="mainform" method="post" action="adminmailinglist.php" onsubmit="return formvalidator(this)">
<?php		$batchsent=0; $numselected=0;
			$sSQL = 'SELECT COUNT(*) AS batchsent FROM mailinglist WHERE emailsent<>0';
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				if(! is_null($rs['batchsent'])) $batchsent=$rs['batchsent'];
			}
			mysql_free_result($result);
			$sSQL = 'SELECT COUNT(*) AS numselected FROM mailinglist WHERE selected<>0';
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				if(! is_null($rs['numselected'])) $numselected=$rs['numselected'];
			}
			mysql_free_result($result);
			writehiddenvar("posted", "1");
			writehiddenvar("act", "dosendem");
			writehiddenvar("stext", trim(@$_POST['stext']));
			writehiddenvar("listem", trim(@$_POST['listem']));
			writehiddenvar("stype", @$_POST['stype']);
			writehiddenvar("pg", @$_POST['pg']);
			writehiddenvar("id", @$_POST['id']); ?>
            <table width="100%" border="0" cellspacing="2" cellpadding="2">
			  <tr> 
                <td colspan="2" align="center" height="34"><strong><?php print $yyMaLiMa.' - '.$yySeEma?></strong></td>
			  </tr>
			  <tr>
				<td align="right" height="34"><?php print $yySenTo?>:</td>
				<td align="left"><select name="sendto" size="1">
							<option value="0"><?php print $yyAdmEm?></option>
<?php	if($numselected>0) print '<option value="1">'.$yySelEm.' (' . $numselected . ')</option>'; ?>
							<option value="2"><?php print $yyEntML?></option>
							<option value="3"><?php print $yyEntAL?></option>
						</select>
				</td>
			  </tr>
			  <tr>
				<td align="right" height="34"><?php print $yyEmlFm?>:</td>
				<td align="left"><select name="emformat" size="1" onchange="changeemailformat(this)">
							<option value="0"><?php print $yyText?></option>
							<option value="1">HTML</option>
<?php			if(@$htmleditor=="fckeditor"){ ?>
							<option value="2">HTML Using FCK Editor</option>
<?php			} ?>
					</select>
				</td>
			  </tr>
			  <tr>
				<td align="right" height="34">Send in batches of:</td>
				<td align="left"><select name="batchesof" size="1">
							<option value="0">Unlimited</option>
							<option value="2"<?php if(@$_COOKIE['EMAILBATCHNUM']=='2') print ' selected="selected"'?>>2</option>
							<option value="50"<?php if(@$_COOKIE['EMAILBATCHNUM']=='50') print ' selected="selected"'?>>50</option>
							<option value="100"<?php if(@$_COOKIE['EMAILBATCHNUM']=='100') print ' selected="selected"'?>>100</option>
							<option value="150"<?php if(@$_COOKIE['EMAILBATCHNUM']=='150') print ' selected="selected"'?>>150</option>
							<option value="200"<?php if(@$_COOKIE['EMAILBATCHNUM']=='200') print ' selected="selected"'?>>200</option>
							<option value="300"<?php if(@$_COOKIE['EMAILBATCHNUM']=='300') print ' selected="selected"'?>>300</option>
							<option value="400"<?php if(@$_COOKIE['EMAILBATCHNUM']=='400') print ' selected="selected"'?>>400</option>
							<option value="500"<?php if(@$_COOKIE['EMAILBATCHNUM']=='500') print ' selected="selected"'?>>500</option>
							<option value="750"<?php if(@$_COOKIE['EMAILBATCHNUM']=='750') print ' selected="selected"'?>>750</option>
							<option value="1000"<?php if(@$_COOKIE['EMAILBATCHNUM']=='1000') print ' selected="selected"'?>>1000</option>
							<option value="1500"<?php if(@$_COOKIE['EMAILBATCHNUM']=='1500') print ' selected="selected"'?>>1500</option>
							<option value="2000"<?php if(@$_COOKIE['EMAILBATCHNUM']=='2000') print ' selected="selected"'?>>2000</option>
							<option value="3000"<?php if(@$_COOKIE['EMAILBATCHNUM']=='3000') print ' selected="selected"'?>>3000</option>
							<option value="4000"<?php if(@$_COOKIE['EMAILBATCHNUM']=='4000') print ' selected="selected"'?>>4000</option>
							<option value="5000"<?php if(@$_COOKIE['EMAILBATCHNUM']=='5000') print ' selected="selected"'?>>5000</option>
							<option value="10000"<?php if(@$_COOKIE['EMAILBATCHNUM']=='10000') print ' selected="selected"'?>>10000</option>
					</select>
<?php		if($batchsent!=0) print ' (' . $batchsent . ' Sent)' ?>
				</td>
			  </tr>
			  <tr>
				<td align="right" height="34"><?php print $yyFrmEm?>:</td>
				<td align="left"><input type="text" name="fromemail" size="40" value="<?php print $emailAddr?>" />
				</td>
			  </tr>
			  <tr>
				<td align="right" height="34"><?php print $yySubjc?>:</td>
				<td align="left"><input type="text" name="emailsubject" size="40" />
				</td>
			  </tr>
			  <tr>
				<td align="right" height="34"><?php print $yyUnsubL?>:</td>
				<td align="left"><input type="checkbox" name="unsubscribe" value="ON" checked="checked" />
				</td>
			  </tr>
<?php			if(@$htmleditor=="fckeditor"){ ?>
			  <tr id="fckrow" style="display:none">
				<td align="right" height="34">&nbsp;</td>
				<td align="left"><textarea name="theemailfck" id="theemailfck" cols="70" rows="35"></textarea></td>
			  </tr>
<?php			} ?>
			  <tr id="textarearow">
				<td align="right" height="34">&nbsp;</td>
				<td align="left"><textarea name="theemail" id="theemail" cols="70" rows="35"></textarea></td>
			  </tr>
			  <tr>
                <td colspan="2" align="center" height="34"><br /><input type="submit" value="<?php print $yySubmit?>" />&nbsp;<input type="reset" value="<?php print $yyReset?>" /><br />&nbsp;</td>
			  </tr>
			  <tr>
                <td colspan="2" align="center" height="34"><br />
                          <a href="adminmailinglist.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
      </table>
<?php
	if(@$htmleditor=='fckeditor'){
		if(@$pathtossl != '' && (@$_SERVER['HTTPS'] == 'on' || @$_SERVER['SERVER_PORT'] == '443')){
			if(substr($pathtossl,-1) != "/") $storeurl = $pathtossl . "/"; else $storeurl = $pathtossl;
		}
		print '<script type="text/javascript">';
		print "var oFCKeditor = new FCKeditor('theemailfck');oFCKeditor.Height=400;oFCKeditor.BasePath=sBasePath;oFCKeditor.Config.BaseHref='".$storeurl."';oFCKeditor.ToolbarSet = 'Basic';oFCKeditor.ReplaceTextarea();\r\n";
		print '</script>';
	}
}elseif(@$_POST['posted']=='1' && (@$_POST['act']=='modify' || @$_POST['act']=='addnew')){
?>
<script language="javascript" type="text/javascript">
<!--
function formvalidator(theForm){
if (theForm.email.value == ""){
alert("<?php print $yyPlsEntr?> \"<?php print $yyEmail?>\".");
theForm.email.focus();
return (false);
}
return (true);
}
//-->
</script>
<?php
		if(@$_POST['act']=='modify'){
			$email = @$_POST['id'];
			$sSQL = "SELECT isconfirmed,mlConfirmDate,mlIPAddress,mlName FROM mailinglist WHERE email='" . escape_string($email) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$dateadded = $rs['mlConfirmDate'];
				$ipaddress = $rs['mlIPAddress'];
				$mlname = $rs['mlName'];
			}
			mysql_free_result($result);
		}else{
			$email = '';
			$mlname = '';
		}
?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		  <td width="100%" align="center">
		  <form name="mainform" method="post" action="adminmailinglist.php" onsubmit="return formvalidator(this)">
<?php		writehiddenvar('posted', '1');
			if(@$_POST['act']=='modify'){
				writehiddenvar('act', 'domodify');
			}else
				writehiddenvar('act', 'doaddnew');
			writehiddenvar('stext', @$_POST['stext']);
			writehiddenvar('listem', @$_POST['listem']);
			writehiddenvar('stype', @$_POST['stype']);
			writehiddenvar('pg', @$_POST['pg']);
			writehiddenvar('id', @$_POST['id']); ?>
            <table width="100%" border="0" cellspacing="2" cellpadding="2">
			  <tr>
                <td width="100%" colspan="2" align="center" height="34"><strong><?php print $yyMaLiMa;
			if(@$_POST['act']=='modify') print ' - ' . htmlspecials(@$_POST['id'])?></strong></td>
			  </tr>
			  <tr>
				<td align="right" height="34"><strong><?php print $yyName?>:</strong></td>
				<td align="left"><input type="text" name="mlname" size="34" value="<?php print htmlspecials($mlname)?>" /></td>
			  </tr>
			  <tr>
				<td align="right" height="34"><strong><?php print $yyEmail?>:</strong></td>
				<td align="left"><input type="text" name="email" size="34" value="<?php print htmlspecials($email)?>" /></td>
			  </tr>
<?php	if(@$_POST['act']=='modify'){ ?>
			  <tr>
				<td align="right" height="34"><strong><?php print $yyDateAd?>:</strong></td>
				<td align="left"><?php print $dateadded?></td>
			  </tr>
			  <tr>
				<td align="right" height="34"><strong><?php print $yyIPAdd?>:</strong></td>
				<td align="left"><?php print $ipaddress?></td>
			  </tr>
<?php	} ?>
			  <tr>
                <td width="100%" colspan="2" align="center" height="34"><br /><input type="submit" value="<?php print $yySubmit?>" />&nbsp;<input type="reset" value="<?php print $yyReset?>" /><br />&nbsp;</td>
			  </tr>
			  <tr>
                <td width="100%" colspan="2" align="center" height="34"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
      </table>
<?php
}elseif(@$_POST['posted']=='1' && @$_POST['act']!='confirm' && $success){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="adminmailinglist.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />
				<img src="../images/clearpixel.gif" width="300" height="3" alt="" />
                </td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}elseif(@$_POST['posted']=='1' && @$_POST['act']!='confirm'){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}else{
	$sSQL = 'SELECT count(*) AS thecount FROM mailinglist';
	if(@$noconfirmationemail!=TRUE) $sSQL .= ' WHERE isconfirmed<>0';
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)) $numemails = $rs['thecount']; else $numemails=0;
	mysql_free_result($result);
	$sSQL = 'SELECT count(*) AS thecount FROM mailinglist WHERE emailsent<>0';
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)) $numsentemails = $rs['thecount']; else $numsentemails=0;
	mysql_free_result($result);

	$ordstate = @$_POST['ordstate'];
	$ordcountry = @$_POST['ordcountry'];
	$smanufacturer = @$_POST['smanufacturer'];
	$thecat = @$_POST['scat'];
	$stext = trim(@$_POST['stext']);
	$stype = trim(@$_POST['stype']);
	$stsearch = trim(@$_POST['stsearch']);
	$swholesale = trim(@$_POST['swholesale']);
?>
<script language="javascript" type="text/javascript">
<!--
function mrec(id) {
	document.mainform.action="adminmailinglist.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "modify";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function crec(id) {
	document.mainform.action="adminmailinglist.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "confirm";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function newrec(id) {
	document.mainform.action="adminmailinglist.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function sendem(id) {
	document.mainform.action="adminmailinglist.php";
	document.mainform.act.value = "sendem";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function drec(id) {
cmsg = "<?php print $yyConDel?>\n"
if (confirm(cmsg)) {
	document.mainform.action="adminmailinglist.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
}
function startsearch(){
	document.mainform.action="adminmailinglist.php";
	document.mainform.act.value = "search";
	document.mainform.listem.value = "";
	document.mainform.posted.value = "";
	document.mainform.submit();
}
function listem(thelet){
	document.mainform.action="adminmailinglist.php";
	document.mainform.act.value = "search";
	document.mainform.listem.value = thelet;
	document.mainform.posted.value = "";
	document.mainform.submit();
}
function removeuncon(){
cmsg = "<?php print $yyConDel?>\n"
if (confirm(cmsg)) {
	document.mainform.action="adminmailinglist.php";
	document.mainform.act.value = "purgeunconfirmed";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
}
function clearsent(){
if (confirm("<?php print $yySureCa?>")) {
	document.mainform.action="adminmailinglist.php";
	document.mainform.act.value = "clearsent";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
}
function checkact(tmen){
	tact=tmen[tmen.selectedIndex].value;
	if(tact=='CSL') clearsent();
	if(tact=='ROU') removeuncon();
	if(tact=='DUS'){
		document.mainform.action="dumporders.php";
		document.mainform.act.value = "dumpemails";
		document.mainform.submit();
	}
	if(tact=='DUE'){
		document.mainform.action="dumporders.php?entirelist=1";
		document.mainform.act.value = "dumpemails";
		document.mainform.submit();
	}
	tmen.selectedIndex=0;
}
// -->
</script>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		  <td width="100%">
		  <form name="mainform" method="post" action="adminmailinglist.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="listem" value="<?php print @$_REQUEST['listem']?>" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="pg" value="<?php print (@$_POST['act']=='search' ? '1' : @$_GET['pg'])?>" />
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr>
				<td class="cobhl" colspan="4" align="center"><strong><?php
					print $numemails . ' ' . 'Emails - ';
					print '<a href="javascript:listem(\'#\')">#</a> ';
					for($index=0; $index < 26; $index++){
						print '<a href="javascript:listem(\'' . chr(65+$index) . '\')">' . chr(65+$index) . '</a> ';
					}
				?></strong></td>
			  </tr>
			  <tr> 
				<td class="cobhl" width="25%" align="right"><?php print $yySrchFr?>:</td>
				<td class="cobll" colspan="3"><input type="text" name="stext" size="20" value="<?php print $stext?>" />
					<select name="stype" size="1">
					<option value=""><?php print $yySrchAl?></option>
					<option value="any" <?php if($stype=="any") print 'selected="selected"'?>><?php print $yySrchAn?></option>
					<option value="exact" <?php if($stype=="exact") print 'selected="selected"'?>><?php print $yySrchEx?></option>
					</select>
					<select name="stsearch" size="1">
					<option value="srchemail"><?php print $yyEmail?></option>
					<option value="srchprodid" <?php if($stsearch=="srchprodid") print 'selected="selected"'?>><?php print $yyPrId?></option>
					<option value="srchprodname" <?php if($stsearch=="srchprodname") print 'selected="selected"'?>><?php print $yyPrName?></option>
					</select>
					&nbsp;
					<select name="swholesale" size="1">
					<option value=""><?php print $yyAll?></option>
					<option value="wholesale" <?php if($swholesale=="wholesale") print 'selected="selected"'?>><?php print $yyWholes?></option>
					<option value="nonwholesale" <?php if($swholesale=="nonwholesale") print 'selected="selected"'?>><?php print $yyNoWhol?></option>
					</select>
				</td>
			  </tr>
			  <tr>
				<td class="cobhl" align="center"><strong><?php print $yySection?></strong>&nbsp;&nbsp;<input type="checkbox" name="notsection" value="ON" <?php if(@$_POST['notsection']=="ON") print 'checked="checked"'?>/><strong>...<?php print $yyNot?></strong></td>
				<td class="cobhl" align="center"><strong><?php print $yyManuf?></strong>&nbsp;&nbsp;<input type="checkbox" name="notmanufacturer" value="ON" <?php if(@$_POST['notmanufacturer']=="ON") print 'checked="checked"'?>/><strong>...<?php print $yyNot?></strong></td>
				<td class="cobhl" align="center"><strong><?php print $yyState?></strong>&nbsp;&nbsp;<input type="checkbox" name="notstate" value="ON" <?php if(@$_POST['notstate']=="ON") print 'checked="checked"'?>/><strong>...<?php print $yyNot?></strong></td>
				<td class="cobhl" align="center"><strong><?php print $yyCountry?></strong>&nbsp;&nbsp;<input type="checkbox" name="notcountry" value="ON" <?php if(@$_POST['notcountry']=="ON") print 'checked="checked"'?>/><strong>...<?php print $yyNot?></strong></td>
			  </tr>
			  <tr>
				<td class="cobll" align="center"><select name="scat[]" size="5" multiple="multiple"><?php
						$sSQL = "SELECT sectionID,sectionWorkingName,topSection,rootSection FROM sections " . (@$adminonlysubcats==TRUE ? "WHERE rootSection=1 ORDER BY sectionWorkingName" : "ORDER BY sectionOrder");
						$allcats = mysql_query($sSQL) or print(mysql_error());
						$lasttsid = -1;
						$numcats = 0;
						while($row = mysql_fetch_row($allcats)){
							$allcatsa[$numcats++]=$row;
						}
						mysql_free_result($allcats);
						if($numcats > 0){
							if(@$adminonlysubcats==TRUE){
								for($index=0;$index<$numcats;$index++){
									print '<option value="' . $allcatsa[$index][0] . '"';
									if(is_array($thecat)){
										foreach($thecat as $catid){
											if($allcatsa[$index][0]==$catid) print ' selected="selected"';
										}
									}
									print '>' . $allcatsa[$index][1] . "</option>\n";
								}
							}else
								writemenulevel(0,1);
						} ?>
					  </select></td>
				<td class="cobll" align="center"><select name="smanufacturer[]" size="5" multiple="multiple"><?php
						$sSQL = 'SELECT mfID,mfName FROM manufacturer ORDER BY mfName';
						$result = mysql_query($sSQL) or print(mysql_error());
						while($rs = mysql_fetch_assoc($result)){
							print '<option value="' . htmlspecials($rs['mfID']) . '"';
							if(is_array($smanufacturer)){
								foreach($smanufacturer as $objValue){
									if($objValue==$rs['mfID']) print ' selected="selected"';
								}
							}
							print '>' . $rs['mfName'] . "</option>\n";
						}
						mysql_free_result($result); ?></select></td>
				<td class="cobll" align="center"><select name="ordstate[]" size="5" multiple="multiple"><?php
						$sSQL = "SELECT stateID,stateName,stateAbbrev FROM states WHERE stateEnabled=1 ORDER BY stateName";
						$result = mysql_query($sSQL) or print(mysql_error());
						while($rs = mysql_fetch_assoc($result)){
							print '<option value="' . htmlspecials(@$usestateabbrev==TRUE?$rs['stateAbbrev']:$rs['stateName']) . '"';
							if(is_array($ordstate)){
								foreach($ordstate as $objValue){
									if($objValue==(@$usestateabbrev==TRUE?$rs['stateAbbrev']:$rs['stateName'])) print ' selected="selected"';
								}
							}
							print '>' . $rs['stateName'] . "</option>\n";
						}
						mysql_free_result($result); ?></select></td>
				<td class="cobll" align="center"><select name="ordcountry[]" size="5" multiple="multiple"><?php
						$sSQL = "SELECT countryID,countryName FROM countries WHERE countryEnabled=1 ORDER BY countryOrder DESC, countryName";
						$result = mysql_query($sSQL) or print(mysql_error());
						while($rs = mysql_fetch_assoc($result)){
							print '<option value="' . htmlspecials($rs["countryName"]) . '"';
							if(is_array($ordcountry)){
								foreach($ordcountry as $objValue){
									if($objValue==$rs['countryName']) print ' selected="selected"';
								}
							}
							print '>' . $rs['countryName'] . "</option>\n";
						}
						mysql_free_result($result); ?></select></td>
			  </tr>
			  <tr>
				<td class="cobhl">&nbsp;</td>
				<td class="cobll" colspan="3" align="center">
<?php
	$mlcount=0;
	$sSQL = "SELECT COUNT(*) AS mlcount FROM mailinglist WHERE isConfirmed=0 AND mlConfirmDate<'".date('Y-m-d', time()-($mailinglistpurgedays*60*60*24))."'";
	$result = mysql_query($sSQL) or print_sql_error();
	if($rs = mysql_fetch_assoc($result)){
		if(! is_null($rs['mlcount'])) $mlcount=$rs['mlcount'];
	}
	mysql_free_result($result); ?>
							<input type="button" value="<?php print $yyListRe?>" onclick="startsearch();" /> &nbsp;
							<input type="button" value="Add Email" onclick="newrec();" />
							<input type="button" value="Send Emails To List" onclick="sendem();" />
						<select onchange="checkact(this)">
						<option value=""><?php print $yyAct?>...</option>
						<option value="CSL">Clear &quot;Sent&quot; List<?php if($numsentemails!=0) print ' ('.$numsentemails.')'?></option>
<?php	if($mlcount > 0) print '<option value="ROU">Remove Old Unconfirmed ('.$mlcount.')</option>' ?>
						<option value="DUS">Dump <?php print $yySelEm?></option>
						<option value="DUE">Dump <?php print $yyEntML?></option>
						</select>
				</td>
			  </tr>
			</table>
            <table width="100%" border="0" cellspacing="0" cellpadding="1">
<?php
	if(@$_POST['act']=='search' || @$_GET['pg'] != '' || @$_POST['act']=='confirm'){
		function displayprodrow($xrs){
			global $yyModify, $yyDelete, $bgcolor, $noconfirmationemail, $yyConfrm;
?><tr class="<?php print $bgcolor?>"><td width="10%">&nbsp;</td><td><?php print htmlspecials($xrs['mlName']) ?>&nbsp;</td><td><?php print htmlspecials($xrs['email']) ?></td>
<td align="center"><?php if(@$noconfirmationemail!=TRUE && $xrs['isconfirmed']==0) print '<input type="button" value="'.$yyConfrm.'" onclick="crec(\''.str_replace(array("'",'"'),array("\'",'&quot;'),$xrs['email']).'\')" />'; else print '&nbsp;'?></td>
<td align="center"><input type="button" value="<?php print $yyModify?>" onclick="mrec('<?php print str_replace(array("'",'"'),array("\'",'&quot;'),$xrs['email'])?>')" /></td>
<td align="center"><input type="button" value="<?php print $yyDelete?>" onclick="drec('<?php print str_replace(array("'",'"'),array("\'",'&quot;'),$xrs['email'])?>')" /></td>
<td>&nbsp;</td></tr>
<?php	}
		function displayheaderrow(){
			global $yyName,$yyEmail,$yyModify,$yyDelete,$noconfirmationemail,$yyConfrm; ?>
			<tr>
				<td width="10%">&nbsp;</td>
				<td><strong><?php print $yyName?></strong></td>
				<td><strong><?php print $yyEmail?></strong></td>
				<td width="8%" align="center"><span style="font-size:10px;font-weight:bold"><?php if(@$noconfirmationemail!=TRUE) print $yyConfrm; else print '&nbsp;'?></span></td>
				<td width="8%" align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyModify?></span></td>
				<td width="8%" align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyDelete?></span></td>
				<td width="10%">&nbsp;</td>
			</tr>
<?php	}
		$rowcounter=0;
		$sSQL = 'SELECT DISTINCT email,mlName,isconfirmed FROM mailinglist ';
		if(($stext!='' && ($stsearch=='srchprodid' || $stsearch=='srchprodname')) || $thecat!='' || $smanufacturer!='' || $ordstate!='' || $ordcountry!='') $sSQL .= 'INNER JOIN orders ON mailinglist.email=orders.ordEmail INNER JOIN cart ON orders.ordID=cart.cartOrderID INNER JOIN products ON cart.cartProdID=products.pId ';
		$whereand='WHERE';
		if(trim(@$_REQUEST['listem']) != ''){
			if(@$_REQUEST['listem']=='#')
				$sSQL .= "WHERE (email < 'A') ";
			else
				$sSQL .= "WHERE (email LIKE '" . escape_string(@$_REQUEST['listem']) . "%') ";
			$whereand='AND';
		}elseif($stext!=''){
			$sText = escape_string($stext);
			$aText = explode(' ', $sText);
			$arrelms = count($aText);
			if($stype=="exact"){
				$sSQL .= $whereand . " (email LIKE '%" . $sText . "%') ";
				$whereand='AND';
			}else{
				if($stype=="any") $sJoin="OR "; else $sJoin="AND ";
				$sSQL .= $whereand . ' (';
				$whereand='AND';
				foreach($aText as $theopt){
					if(is_array($theopt))$theopt=$theopt[0];
					if($stsearch=='srchemail') $sSQL.="email ";
					if($stsearch=='srchprodid') $sSQL.="cartProdId ";
					if($stsearch=='srchprodname') $sSQL.="cartProdName ";
					$sSQL .= " LIKE '%" . $theopt . "%' ";
					if(++$rowcounter < $arrelms) $sSQL .= $sJoin;
				}
				$sSQL .= ') ';
			}
		}
		if(is_array($thecat)){
			$sectionids = getsectionids(implode(',',$thecat), TRUE);
			if($sectionids!=''){
				$sSQL.= $whereand . ' ' . (@$_POST['notsection']=='ON'?'NOT ':'') . "(products.pSection IN (" . $sectionids . ")) ";
				$whereand='AND';
			}
		}
		if(is_array($smanufacturer)){
			$sSQL.= $whereand . ' ' . (@$_POST['notmanufacturer']=='ON'?'NOT ':'') . "(products.pManufacturer IN (" . implode(',',$smanufacturer) . ")) ";
			$whereand='AND';
		}
		if(is_array($ordstate)){
			$sSQL.= $whereand . ' ' . (@$_POST['notstate']=='ON'?'NOT ':'') . "(ordState IN ('" . implode("','", $ordstate) . "')) ";
			$whereand='AND';
		}
		if(is_array($ordcountry)){
			$sSQL.= $whereand . ' ' . (@$_POST['notcountry']=='ON'?'NOT ':'') . "(ordCountry IN ('" . implode("','",$ordcountry) . "')) ";
			$whereand='AND';
		}
		if($whereand=='WHERE'){
			mysql_query("UPDATE mailinglist SET selected=1") or print(mysql_error());
		}else{
			mysql_query("UPDATE mailinglist SET selected=0") or print(mysql_error());
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_assoc($result)){
				mysql_query("UPDATE mailinglist SET selected=1 WHERE email='" . escape_string($rs['email']) . "'") or print(mysql_error());
			}
			mysql_free_result($result);
		}
		if($swholesale=='nonwholesale'){
			$sSQL = "SELECT DISTINCT email FROM mailinglist LEFT JOIN customerlogin ON mailinglist.email=customerlogin.clEmail WHERE selected<>0 AND (clActions&8)=8";
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_assoc($result)){
				mysql_query("UPDATE mailinglist SET selected=0 WHERE email='" . escape_string($rs['email']) . "'") or print(mysql_error());
			}
			mysql_free_result($result);
		}elseif($swholesale=='wholesale'){
			$sSQL = "SELECT DISTINCT email FROM mailinglist LEFT JOIN customerlogin ON mailinglist.email=customerlogin.clEmail WHERE selected<>0 AND ((clActions&8)<>8 OR clActions IS NULL)";
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_assoc($result)){
				mysql_query("UPDATE mailinglist SET selected=0 WHERE email='" . escape_string($rs['email']) . "'") or print(mysql_error());
			}
			mysql_free_result($result);
		}
		$sSQL = 'SELECT DISTINCT email,mlName,isconfirmed FROM mailinglist WHERE selected<>0 ORDER BY email';
		if(! @is_numeric($_GET['pg']))
			$CurPage = 1;
		else
			$CurPage = (int)($_GET['pg']);
		if(@$adminproductsperpage=='') $adminproductsperpage=200;
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result)>0) $iNumOfPages = ceil(mysql_num_rows($result)/$adminproductsperpage); else $iNumOfPages = 0;
		mysql_free_result($result);
		$sSQL .= ' LIMIT ' . ($adminproductsperpage*($CurPage-1)) . ', ' . $adminproductsperpage;
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result) > 0){
			$pblink = '<a href="adminmailinglist.php?stext=' . urlencode($stext) . '&stype=' . $stype . '&pg=';
			if($iNumOfPages > 1) print '<tr><td colspan="6" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
			displayheaderrow();
			while($rs = mysql_fetch_assoc($result)){
				if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark';
				displayprodrow($rs);
			}
			if($iNumOfPages > 1) print '<tr><td colspan="6" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
		}else{
			print '<tr><td width="100%" colspan="6" align="center"><br />' . $yyItNone . '<br />&nbsp;</td></tr>';
		}
		mysql_free_result($result);
	}else{
		$selectedunsent=0;
		$sSQL = "SELECT COUNT(*) AS selectedunsent FROM mailinglist WHERE selected<>0 AND emailsent=0";
		if(@$noconfirmationemail!=TRUE) $sSQL.= ' AND isconfirmed<>0';
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)) $selectedunsent = $rs['selectedunsent']; else $selectedunsent=0;
		mysql_free_result($result);
		if($selectedunsent!=0){ ?>
			<tr> 
                <td width="100%" colspan="7" align="center"><br />
                          <?php print $selectedunsent?> Unsent from previous search<br />&nbsp;</td>
			  </tr>
<?php	}
	} ?>
			  <tr> 
                <td width="100%" colspan="7" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
      </table>
<?php
}
?>
