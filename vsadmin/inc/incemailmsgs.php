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
$dorefresh=FALSE;
if(@$_POST['posted']=="1"){
	if(@$_POST['act']=="domodify"){
		if(($adminlangsettings & 4096)==4096) $maxlangs=$adminlanguages; else $maxlangs=0;
		for($index=0; $index <= $maxlangs; $index++){
			if($index==0) $mesgid=''; else $mesgid=$index+1;
			$sSQL = "UPDATE emailmessages SET ";
			$themessage = unstripslashes(@$_POST['emtextarea' . ($index+1)]);
			if(! (@$htmlemails && @$htmleditor=='fckeditor'))
				$themessage = str_replace("\r\n", '<br />', $themessage);
			if(@$_POST['id']=="orderstatusemail"){
				$sSQL .= "orderstatussubject".$mesgid."='" . escape_string(unstripslashes(@$_POST['eminputtext' . ($index+1)])) . "',";
				$sSQL .= "orderstatusemail".$mesgid."='" . escape_string($themessage) . "'";
			}elseif(@$_POST['id']=="emailheaders"){
				$sSQL .= "emailsubject".$mesgid."='" . escape_string(unstripslashes(@$_POST['eminputtext' . ($index+1)])) . "',";
				$sSQL .= "emailheaders".$mesgid."='" . escape_string($themessage) . "'";
			}elseif(@$_POST['id']=="receiptheaders"){
				$sSQL .= "receiptheaders".$mesgid."='" . escape_string($themessage) . "'";
			}elseif(@$_POST['id']=="dropshipheaders"){
				$sSQL .= "dropshipsubject".$mesgid."='" . escape_string(unstripslashes(@$_POST['eminputtext' . ($index+1)])) . "',";
				$sSQL .= "dropshipheaders".$mesgid."='" . escape_string($themessage) . "'";
			}elseif(@$_POST['id']=="giftcertificate"){
				$sSQL .= "giftcertsubject".$mesgid."='" . escape_string(unstripslashes(@$_POST['eminputtext' . ($index+1)])) . "',";
				$sSQL .= "giftcertemail".$mesgid."='" . escape_string($themessage) . "'";
			}elseif(@$_POST['id']=="giftcertsender"){
				$sSQL .= "giftcertsendersubject".$mesgid."='" . escape_string(unstripslashes(@$_POST['eminputtext' . ($index+1)])) . "',";
				$sSQL .= "giftcertsender".$mesgid."='" . escape_string($themessage) . "'";
			}elseif(@$_POST['id']=="notifybackinstock"){
				$sSQL .= "notifystocksubject".$mesgid."='" . escape_string(unstripslashes(@$_POST['eminputtext' . ($index+1)])) . "',";
				$sSQL .= "notifystockemail".$mesgid."='" . escape_string($themessage) . "'";
			}
			$sSQL .= ' WHERE emailID=1';
			mysql_query($sSQL) or print(mysql_error());
		}
		$dorefresh=TRUE;
	}
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=adminemailmsgs.php';
	print '?id=' . urlencode(@$_POST['id']);
	print '">';
}
if(@$_POST['id']!='' && @$_POST['act']=='modify'){
	if(@$htmlemails!=TRUE) $htmleditor='';
	if(@$htmleditor=='fckeditor'){ ?>
<script type="text/javascript" src="fckeditor.js"></script>
<script type="text/javascript">
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
var sBasePath = document.location.pathname.substring(0,document.location.pathname.lastIndexOf('adminprods.php'));
</script>
<?php
	} ?>
<script language="javascript" type="text/javascript">
<!--
function formvalidator(theForm){
return (true);
}
//-->
</script>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		  <td width="100%" align="center">
		  <form name="mainform" method="post" action="adminemailmsgs.php" onsubmit="return formvalidator(this)">
<?php	writehiddenvar('posted', '1');
		writehiddenvar('act', 'domodify');
		writehiddenvar('id', trim(@$_POST['id']));
?>
            <table width="100%" border="0" cellspacing="2" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><strong><?php print $yyEmlAdm . ': ' . @$_POST['id'] . '<br />&nbsp;'; ?></strong></td>
			  </tr>
<?php	$theid = trim(@$_POST['id']);
		if(($adminlangsettings & 4096)==4096) $maxlangs=$adminlanguages; else $maxlangs=0;
		for($index=0; $index <= $maxlangs; $index++){
			$replacementfields = '';
			$subjectreplacementfields = '';
			$hassubject=FALSE;
			$languageid=$index+1;
			if($theid=='orderstatusemail'){
				$fieldlist = getlangid('orderstatussubject',4096).','.getlangid('orderstatusemail',4096);
				$replacementfields = '%orderid% %ordername% %orderdate% %oldstatus% %newstatus% %date% {%statusinfo%} {%trackingnum%} {%reviewlinks%}';
				$hassubject=TRUE;
			}elseif($theid=='emailheaders'){
				$fieldlist = getlangid('emailsubject',4096).','.getlangid('emailheaders',4096);
				$replacementfields = '%emailmessage% %ordername% %orderdate% {%reviewlinks%}';
				$subjectreplacementfields='%orderid% %ordername%';
				$hassubject=TRUE;
			}elseif($theid=='receiptheaders'){
				$fieldlist = getlangid('receiptheaders',4096);
				$replacementfields = '%messagebody% %reviewlinks% %ordername% %orderdate%';
				$hassubject=FALSE;
			}elseif($theid=='dropshipheaders'){
				$fieldlist = getlangid('dropshipsubject',4096).','.getlangid('dropshipheaders',4096);
				$replacementfields = '%emailmessage% %ordername% %orderdate%';
				$subjectreplacementfields='%orderid%';
				$hassubject=TRUE;
			}elseif($theid=='giftcertificate'){
				$fieldlist = getlangid('giftcertsubject',4096).','.getlangid('giftcertemail',4096);
				$replacementfields = '%toname% %fromname% %value% %certificateid% %storeurl% {%message%}';
				$subjectreplacementfields='%fromname%';
				$hassubject=TRUE;
			}elseif($theid=='giftcertsender'){
				$fieldlist = getlangid('giftcertsendersubject',4096).','.getlangid('giftcertsender',4096);
				$replacementfields = '%toname%';
				$subjectreplacementfields='%toname%';
				$hassubject=TRUE;
			}elseif($theid=='notifybackinstock'){
				$fieldlist = getlangid('notifystocksubject',4096).','.getlangid('notifystockemail',4096);
				$replacementfields = '%pid% %pname% %link% %storeurl%';
				$subjectreplacementfields='%pid %pname%';
				$hassubject=TRUE;
			}
			$sSQL = "SELECT ".$fieldlist." FROM emailmessages WHERE emailID=1";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				if($theid=='orderstatusemail'){
					$thesubject = trim($rs[getlangid('orderstatussubject',4096)]);
					$themessage = trim($rs[getlangid('orderstatusemail',4096)]);
				}elseif($theid=='emailheaders'){
					$thesubject = trim($rs[getlangid('emailsubject',4096)]);
					$themessage = trim($rs[getlangid('emailheaders',4096)]);
				}elseif($theid=='receiptheaders'){
					$themessage = trim($rs[getlangid('receiptheaders',4096)]);
				}elseif($theid=='dropshipheaders'){
					$thesubject = trim($rs[getlangid('dropshipsubject',4096)]);
					$themessage = trim($rs[getlangid('dropshipheaders',4096)]);
				}elseif($theid=='giftcertificate'){
					$thesubject = trim($rs[getlangid('giftcertsubject',4096)]);
					$themessage = trim($rs[getlangid('giftcertemail',4096)]);
				}elseif($theid=='giftcertsender'){
					$thesubject = trim($rs[getlangid('giftcertsendersubject',4096)]);
					$themessage = trim($rs[getlangid('giftcertsender',4096)]);
				}elseif($theid=='notifybackinstock'){
					$thesubject = trim($rs[getlangid('notifystocksubject',4096)]);
					$themessage = trim($rs[getlangid('notifystockemail',4096)]);
				}else
					print 'id not set';
			}
			mysql_free_result($result);
			if(! ($htmlemails && @$htmleditor=='fckeditor')){
				$themessage = str_replace('<br />', "\r\n", $themessage);
				$themessage = str_replace('<br>', "\r\n", $themessage);
				$themessage = str_replace('%nl%', "\r\n", $themessage);
			}else{
				$themessage = str_replace(array('<br>','%nl%'), '<br />', $themessage);
				$themessage = str_replace('<', '&lt;', $themessage);
			}
			if($adminlanguages > 0){ ?>
			  <tr>
				<td align="center" colspan="2"><strong><?php print $yyLanID . ': ' . ($index+1)?></strong></td>
			  </tr>
<?php		}
			if($hassubject){ ?>
			  <tr>
				<td align="right"><strong><?php print $yyRepFld?>:</strong></td>
				<td align="left"><?php print $subjectreplacementfields?></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yySubjc?>:</strong></td>
				<td align="left"><input type="text" name="eminputtext<?php print ($index+1)?>" size="55" maxlength="255" value="<?php print $thesubject?>" /></td>
			  </tr>
<?php		}
			if($replacementfields!=''){ ?>
			  <tr>
				<td align="right"><strong><?php print $yyRepFld?>:</strong></td>
				<td align="left"><?php print $replacementfields?></td>
			  </tr>
<?php		} ?>
			  <tr>
				<td align="right"><strong><?php print $yyMessag?>:</strong></td>
				<td align="left"><textarea name="emtextarea<?php print ($index+1)?>" cols="90" rows="15"><?php print $themessage?></textarea></td>
			  </tr>
<?php	} ?>
			  <tr>
                <td width="100%" colspan="2" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" />&nbsp;<input type="reset" value="<?php print $yyReset?>" />&nbsp;<input type="button" value="<?php print $yyCancel?>" onclick="document.location='adminemailmsgs.php?id=<?php print @$_POST['id']?>'" /><br />&nbsp;</td>
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
      </table>
<?php
	if(@$htmleditor=='fckeditor'){
		if(@$pathtossl != '' && (@$_SERVER['HTTPS'] == 'on' || @$_SERVER['SERVER_PORT'] == '443')){
			if(substr($pathtossl,-1) != "/") $storeurl = $pathtossl . "/"; else $storeurl = $pathtossl;
		}
		print '<script type="text/javascript">function loadeditors(){';
		$streditor = "var oFCKeditor = new FCKeditor('emtextarea');oFCKeditor.BasePath=sBasePath;oFCKeditor.Config.BaseHref='".$storeurl."';oFCKeditor.ToolbarSet = 'Basic';oFCKeditor.ReplaceTextarea();\r\n";
		if(($adminlangsettings & 4096)==4096) $maxlangs=$adminlanguages; else $maxlangs=0;
		for($index=1; $index <= $maxlangs+1; $index++)
			print str_replace("emtextarea", "emtextarea" . $index, $streditor);
		print '}window.onload=function(){loadeditors();}</script>';
	}
}elseif(@$_POST['posted']=="1" && $success){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="adminemailmsgs.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />&nbsp;<br />&nbsp;
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
?>
<script language="javascript" type="text/javascript">
<!--
function mrec(id) {
	// document.mainform.id.value = id;
}
// -->
</script>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		  <td width="100%">
		  <form name="mainform" method="post" action="adminemailmsgs.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="modify" />
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr>
				<td class="cobhl" colspan="2" align="center"><strong><?php
					print $yyHTMLic . ' ';
					if(@$htmlemails) print $yyOn; else print $yyNot . " " . $yyOn; ?></strong></td>
			  </tr>
			  <tr>
				<td class="cobhl" align="right">Email Message:</td>
				<td class="cobll"><select name="id" id="idselector" size="1">
					<option value="orderstatusemail" <?php if(@$_REQUEST['id']=="orderstatusemail") print 'selected'?>>Order Status Email</option>
					<option value="emailheaders" <?php if(@$_REQUEST['id']=="emailheaders") print 'selected'?>><?php print $yyEmlHdr?></option>
					<option value="receiptheaders" <?php if(@$_REQUEST['id']=="receiptheaders") print 'selected'?>>Receipt Headers</option>
					<option value="dropshipheaders" <?php if(@$_REQUEST['id']=="dropshipheaders") print 'selected'?>><?php print $yyDrSppr . ' ' . $yyEmlHdr?></option>
					<option value="giftcertificate" <?php if(@$_REQUEST['id']=="giftcertificate") print 'selected'?>>Gift Certificate Email</option>
					<option value="giftcertsender" <?php if(@$_REQUEST['id']=="giftcertsender") print 'selected'?>>Gift Certificate Sender</option>
					<option value="notifybackinstock" <?php if(@$_REQUEST['id']=="notifybackinstock") print 'selected'?>>Notify Back In Stock Email</option>
					</select>
						<input type="submit" value="Edit Email" />
				</td>
			  </tr>
			  <tr> 
                <td class="cobll" colspan="2" align="center"><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p></td>
			  </tr>
			  <tr> 
                <td class="cobll" colspan="2" align="center"><br />
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
