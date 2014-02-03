<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if($_SESSION['loggedon'] != $storesessionvalue || @$disallowlogin==TRUE) exit;
$addsuccess = FALSE;
$success = FALSE;
$maxcatsperpage = 500;
$showaccount = FALSE;
$dorefresh = FALSE;
$alreadygotadmin = getadminsettings();
if(@$_POST['act']=='changepos'){
	$theid = (int)@$_POST['id'];
	$neworder = ((int)@$_POST['newval'])-1;
	$sSQL = "SELECT scGroup FROM searchcriteria WHERE scID=" . $theid;
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)) $topsection = $rs['scGroup'];
	mysql_free_result($result);
	$rc=0;
	$sSQL='SELECT scID,scOrder FROM searchcriteria WHERE scGroup IN ('.$topsection.') ORDER BY scOrder';
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs = mysql_fetch_assoc($result)){
		if($rs['scID']==$theid)
			$sSQL = "UPDATE searchcriteria SET scOrder=".$neworder." WHERE scID=".$theid;
		else
			$sSQL = "UPDATE searchcriteria SET scOrder=".($rc<$neworder?$rc:$rc+1)." WHERE scID=".$rs['scID'];
		mysql_query($sSQL) or print(mysql_error());
		$rc++;
	}
	$dorefresh=TRUE;
}elseif(@$_POST['act']=='domodify'){
	$scworkingname=trim(@$_POST['scworkingname']);
	if($scworkingname=='') $scworkingname=trim(@$_POST['scname']);
	$sSQL = "UPDATE searchcriteria SET " .
		"scName='" . escape_string(@$_POST['scname']) . "',";
	for($index=2; $index<=$adminlanguages+1; $index++){
		if(($adminlangsettings & 131072)==131072)
			$sSQL .= "scName".$index."='".escape_string(@$_POST['scname' . $index]) . "',";
	}
	$sSQL .= "scWorkingName='" . escape_string($scworkingname) . "'," .
		"scGroup=" . @$_POST['scgroup'] . ' ' .
		"WHERE scID=" . str_replace("'",'',trim(@$_POST['scID']));
	mysql_query($sSQL) or print(mysql_error());
	$dorefresh=TRUE;
}elseif(@$_POST['act']=='doaddnew'){
	$theorder=1;
	$sSQL = "SELECT MAX(scOrder) AS theorder FROM searchcriteria WHERE scGroup=".@$_POST['scgroup'];
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_array($result)) $theorder = $rs['theorder']+1; else $theorder=1;
	mysql_free_result($result);
	$scworkingname=trim(@$_POST['scworkingname']);
	if($scworkingname=='') $scworkingname=trim(@$_POST['scname']);
	$sSQL = "INSERT INTO searchcriteria (scName,scWorkingName,scGroup,";
	for($index=2; $index<=$adminlanguages+1; $index++){
		if(($adminlangsettings & 131072)==131072)
			$sSQL .= "scName".$index.",";
	}	
	$sSQL .= "scOrder) VALUES (" .
		"'".escape_string(@$_POST['scname']) . "'," .
		"'".escape_string($scworkingname) . "'," .
		@$_POST['scgroup'] . ",";
	for($index=2; $index<=$adminlanguages+1; $index++){
		if(($adminlangsettings & 131072)==131072)
			$sSQL .= "'".escape_string(@$_POST['scName' . $index]) . "',";
	}
	$sSQL .= $theorder . ")";
	mysql_query($sSQL) or print(mysql_error());
	mysql_query("UPDATE searchcriteria SET scGroup=scID WHERE scGroup=0") or print(mysql_error());
	$dorefresh=TRUE;
}elseif(@$_POST['act']=="delete"){
	$sSQL = "DELETE FROM searchcriteria WHERE scID=" . trim(@$_POST['id']);
	mysql_query($sSQL) or print(mysql_error());
	$sSQL = "UPDATE products SET pSearchCriteria=0 WHERE pSearchCriteria=" . trim(@$_POST['id']);
	mysql_query($sSQL) or print(mysql_error());
	$dorefresh=TRUE;
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=adminsearchcriteria.php">';
}
if($dorefresh){
?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="adminsearchcriteria.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />
				<img src="../images/clearpixel.gif" width="300" height="3" alt="" />
                </td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}elseif(trim(@$_POST['act'])=="modify" || trim(@$_POST['act'])=="addnew"){
	if(trim(@$_POST['act'])=='modify'){
		$scID=trim(@$_POST['id']);
		$sSQL = "SELECT scName,scName2,scName3,scWorkingName,scGroup FROM searchcriteria WHERE scID=".$scID;
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_array($result)){
			$scworkingname = $rs['scWorkingName'];
			$scgroup = $rs['scGroup'];
			for($index=1; $index<=3; $index++){
				$scaName[$index] = $rs['scName'.($index==1?'':$index)];
			}
		}
		mysql_free_result($result);
	}else{
		$scworkingname = '';
		$scgroup = '';
		for($index=1; $index<=3; $index++){
			$scaName[$index] = '';
		}
	}
?>
<script language="javascript" type="text/javascript">
<!--
function checkform(frm){
if(frm.name.value==""){
	alert("<?php print $yyPlsEntr?> \"<?php print $yyName?>\".");
	frm.scname.focus();
	return (false);
}
return (true);
}
//-->
</script>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
		    <form method="post" action="adminsearchcriteria.php" onsubmit="return checkform(this)">
	<?php	if(trim(@$_POST['act'])=='modify'){ ?>
			<input type="hidden" name="act" value="domodify" />
	<?php	}else{ ?>
			<input type="hidden" name="act" value="doaddnew" />
	<?php	} ?>
			<input type="hidden" name="scID" value="<?php print $scID?>" />
			  <table width="100%" border="0" cellspacing="0" cellpadding="3">
				<tr>
				  <td width="100%" align="center" colspan="2"><strong><?php print $yySeaCri?></strong><br />&nbsp;</td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $redasterix.$yyName?>:</strong></td>
				  <td align="left"><input type="text" name="scname" size="30" value="<?php print $scaName[1]?>" /></td>
				</tr>
<?php		for($index=2; $index<=$adminlanguages+1; $index++){
				if(($adminlangsettings & 131072)==131072){ ?>
				<tr>
				  <td align="right"><strong><?php print $redasterix.$yyName?> <?php print $index?></strong></td>
				  <td align="left"><input type="text" name="scname<?php print $index?>" size="30" value="<?php print htmlspecials($scaName[$index])?>" />
				  </td>
				</tr>
<?php			}
			} ?>
				<tr>
				  <td align="right"><strong><?php print "Group With"?>:</strong></td>
				  <td align="left"><select size="1" name="scgroup"><option value="0">New Group</option>
<?php		$sSQL = "SELECT scID,scGroup,scWorkingName FROM searchcriteria WHERE scGroup<>0 AND scGroup=scID ORDER BY scGroup,scOrder";
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_array($result)){
				print '<option value="'.$rs['scID'].'"'.($scgroup=$rs['scGroup']?' selected="selected"':'').'>'.$rs['scWorkingName']."</option>\r\n";
			}
			mysql_free_result($result); ?>
				  </select></td>
				</tr>
				<tr>
				  <td width="50%" align="right"><strong><?php print $yyWrkNam?>:</strong></td>
				  <td align="left"><input type="text" name="scworkingname" size="30" value="<?php print $scworkingname?>" /></td>
				</tr>
				<tr><td align="center" colspan="2">&nbsp;</td></tr>
				<tr>
				  <td align="center" colspan="2"><input type="submit" value="<?php print $yySubmit?>" /> <input type="reset" value="<?php print $yyReset?>" /> </td>
				</tr>
				<tr><td align="center" colspan="2">&nbsp;</td></tr>
			  </table>
			</form>
		  </td>
        </tr>
      </table>
<?php
}else{ ?>
<script language="javascript" type="text/javascript">
<!--
var rowsingrp=[];
function popsel(x,theid,grpid){
	if(x.length>1) return;
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
	document.mainform.action="adminsearchcriteria.php";
	document.mainform.newval.value = obj.selectedIndex+1;
	document.mainform.id.value = id;
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
			<form name="mainform" method="post" action="adminsearchcriteria.php">
			<input type="hidden" name="id" value="xxx" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="selectedq" value="1" />
			<input type="hidden" name="newval" value="1" />
			  <table width="80%" border="0" cellspacing="0" cellpadding="2">
				<tr>
				  <td width="100%" align="center" colspan="6"><strong><?php print $yySeaCri?></strong><br />&nbsp;</td>
				</tr>
				<tr>
				  <td width="5%"><strong><?php print $yyOrder?></strong></td>
				  <td width="10%"><strong><?php print $yyID?></strong></td>
				  <td align="left"><strong><?php print $yyGroup?></strong></td>
				  <td align="left"><strong><?php print $yyName?></strong></td>
				  <td width="10%"><strong><?php print $yyModify?></strong></td>
				  <td width="10%"><strong><?php print $yyDelete?></strong></td>
				</tr>
<?php
	$rowcounter=0;
	$sSQL = "SELECT sc1.scID,sc1.scWorkingName,sc1.scGroup,sc2.scWorkingName AS grpName FROM searchcriteria AS sc1 LEFT JOIN searchcriteria AS sc2 ON sc1.scGroup=sc2.scID ORDER BY sc1.scGroup,sc1.scOrder";
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result)==0){ ?>
				<tr>
				  <td width="100%" align="center" colspan="4"><br />&nbsp;<br /><strong><?php print $yyItNone?></strong><br />&nbsp;</td>
				</tr>
<?php
	}else{
		$currgroup=-1;
		$ordingroup=1;
		$rowsingrp='';
		while($rs = mysql_fetch_array($result)){
			if($currgroup==-1) $currgroup=$rs['scGroup'];
			if($currgroup!=$rs['scGroup']){
				print '<tr><td colspan="6">&nbsp;</td></tr>';
				$rowsingrp.="rowsingrp[".$currgroup."]=".($ordingroup-1).";";
				$currgroup=$rs['scGroup'];
				$ordingroup=1;
			}
			if(@$bgcolor=="altdark") $bgcolor="altlight"; else $bgcolor="altdark";
?>
				<tr class="<?php print $bgcolor?>">
				  <td><?php
					print '<select name="newpos" onchange="chi('.$rs['scID'].',this)" onmouseover="popsel(this,'.$ordingroup.",".$rs['scGroup'].')">';
					print '<option value="" selected="selected">'.$ordingroup.($ordingroup<100?'&nbsp;':'').'</option>';
					print '</select>'; ?></td>
				  <td><?php print $rs['scID']?></td>
				  <td align="left"><?php print $rs['grpName']?>&nbsp;</td>
				  <td align="left"><?php print $rs['scWorkingName']?></td>
				  <td><input type="button" value="<?php print $yyModify?>" onclick="modrec('<?php print $rs['scID']?>')" /></td>
				  <td><input type="button" value="<?php print $yyDelete?>" onclick="delrec('<?php print $rs['scID']?>')" /></td>
				</tr><?php
			$rowcounter++;
			$ordingroup++;
		}
		$rowsingrp.='rowsingrp['.$currgroup."]=".($ordingroup-1).";";
	} ?>
				<tr> 
				  <td width="100%" colspan="6" align="center"><br /><input type="button" value="<?php print $yyAddNew?>" onclick="newrec()" /><br />&nbsp;</td>
				</tr>
				<tr> 
				  <td width="100%" colspan="6" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
				</tr>
			  </table>
			</form>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
<?php print $rowsingrp?>
/* ]]> */
</script>
		  </td>
        </tr>
      </table>
<?php
}
?>
