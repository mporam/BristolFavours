<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if($_SESSION["loggedon"] != $storesessionvalue || @$disallowlogin==TRUE) exit;
$success=TRUE;
$numzones=0;
$alreadygotadmin = getadminsettings();
$editzones = (($shipType==2 || $shipType==5 || $adminIntShipping==2 || $adminIntShipping==5 || @$alternateratesweightbased != '') && $splitUSZones);
if(@$_POST['posted']=='1'){
	$cena=0;
	if(@$_POST['ena'] != '') $cena=1;
	$fsa=0;
	if(@$_POST['fsa'] != '') $fsa=1;
	$tax = @$_POST['tax'];
	if(! is_numeric($tax)){
		$success=FALSE;
		$errmsg = $yyNum100 . ' "' . $yyTax . '".';
	}elseif($tax > 100 || $tax < 0){
		$success=FALSE;
		$errmsg = $yyNum100 . ' "' . $yyTax . '".';
	}else{
		if($editzones)
			$sSQL = "UPDATE states SET stateEnabled=" . $cena . ",stateTax=" . $tax . ",stateFreeShip=" . $fsa . ",stateZone=" . @$_POST['zon'] . " WHERE stateID=" . $_POST['id'];
		else
			$sSQL = "UPDATE states SET stateEnabled=" . $cena . ",stateTax=" . $tax . ",stateFreeShip=" . $fsa . " WHERE stateID=" . $_POST['id'];
		mysql_query($sSQL) or print(mysql_error());
	}
	if($success)
		print '<meta http-equiv="refresh" content="1; url=adminstate.php">';
}elseif(@$_POST['setallact']!=''){
	$setallact = $_POST['setallact'];
	$theids = @$_POST['ids'];
	$cena=0;
	if(@$_POST['allenable']=='ON') $cena=1;
	$fsa=0;
	if(@$_POST['allfsa']!='') $fsa=1;
	$tax = @$_POST['alltax'];
	$pos = @$_POST['allpos'];
	$zone = @$_POST['allzone'];
	if($setallact=='allenable')
		$sSQL = "UPDATE states SET stateEnabled=" . $cena . " WHERE stateID IN (" . implode(',', $theids) . ")";
	elseif($setallact=='allfsa')
		$sSQL = "UPDATE states SET stateFreeShip=" . $fsa . " WHERE stateID IN (" . implode(',', $theids) . ")";
	elseif($setallact=='alltax'){
		if(! is_numeric($tax)){
			$success=FALSE;
			$errmsg = $yyNum100 . ' "' . $yyTax . '".';
		}elseif($tax > 100 || $tax < 0){
			$success=FALSE;
			$errmsg = $yyNum100 . ' "' . $yyTax . '".';
		}else
			$sSQL = "UPDATE states SET stateTax=" . $tax . " WHERE stateID IN (" . implode(',', $theids) . ")";
	}elseif($setallact=='allpos')
		$sSQL = "UPDATE states SET stateOrder=" . $pos . " WHERE stateID IN (" . implode(',', $theids) . ")";
	elseif($setallact=='allzone')
		$sSQL = "UPDATE states SET stateZone=" . $zone . " WHERE stateID IN (" . implode(',', $theids) . ")";
	if($success)
		mysql_query($sSQL) or print(mysql_error());
}
$sSQL = "SELECT pzID,pzName FROM postalzones WHERE pzName<>'' AND pzID>100";
$result = mysql_query($sSQL) or print(mysql_error());
while($rs = mysql_fetch_assoc($result))
	$allzones[$numzones++] = $rs;
mysql_free_result($result);
if((@$_POST['posted']=='1' || @$_POST['setallact']!='') && ! $success){ ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold">Some records could not be updated.</span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table>
<?php
}elseif(@$_GET['id']!=''){ ?>
		  <form name="mainform" method="post" action="adminstate.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="id" value="<?php print $_GET['id']?>" />
			<table width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><strong><?php print $yyStaAdm?></strong><br /><br />
				<span style="font-size:10px"><?php print $yyFSANot?><br />&nbsp;</span></td>
			  </tr>
<?php
	$sSQL = "SELECT stateID,stateName,stateEnabled,stateTax,stateZone,stateFreeShip FROM states WHERE stateID='" . escape_string($_GET['id']) . "'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		?>
			  <tr>
				<td align="right" width="50%"><strong><?php print $yyStaNam?></strong></td>
				<td><strong><?php print $rs["stateName"]?></strong></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyEnable?></strong></td>
				<td><input type="checkbox" name="ena"<?php if((int)$rs["stateEnabled"]==1) print ' checked="checked"' ?> /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyTax?></strong></td>
				<td><input type="text" name="tax" value="<?php print (double)$rs["stateTax"]?>" size="4" />%</td>
			  </tr>
			  <tr>
				<td align="right"><strong><acronym title="<?php print $yyFSApp?>"><?php print $yyFSApp . ' ('.$yyFSA.')'?></acronym></strong></td>
				<td><input type="checkbox" name="fsa"<?php if((int)$rs["stateFreeShip"]==1) print ' checked="checked"'?> /></td>
			  </tr>
<?php	if($editzones){ ?>
			  <tr>
				<td align="right"><strong><?php print $yyPZone;?></strong></td>
<?php		$foundzone=FALSE;
			print '<td><select name="zon" size="1">';
			for($index=0; $index < $numzones; $index++){
				print '<option value="' . $allzones[$index]['pzID'] . '"';
				if($rs['stateZone']==$allzones[$index]['pzID']){
					print ' selected="selected"';
					$foundzone=TRUE;
				}
				print '>' . $allzones[$index]['pzName'] . "</option>\n";
			}
			if(!$foundzone)print '<option value="0" selected="selected"><?php print $yyUndef?></option>';
			print '</select></td>';
		}
	}
	mysql_free_result($result); ?>
			  </tr>
			  <tr> 
                <td width="100%" colspan="2" align="center">
				  <p>&nbsp;</p>
                  <p><input type="submit" value="<?php print $yySubmit?>" />&nbsp;&nbsp;<input type="reset" value="<?php print $yyReset?>" /><br />&nbsp;</p>
                </td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="2" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a></td>
			  </tr>
			</table>
		  </form>
<?php
}else{
	if($editzones) $colspan='7'; else $colspan='6';
?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function docheckall(){
	allcbs = document.getElementsByName('ids[]');
	mainidchecked = document.getElementById('xdocheckall').checked;
	for(i=0;i<allcbs.length;i++) {
		allcbs[i].checked=mainidchecked;
	}
	return(true);
}
function setall(theact){
	allcbs = document.getElementsByName('ids[]');
	var onechecked=false;
	for(i=0;i<allcbs.length;i++) {
		if(allcbs[i].checked)onechecked=true;
	}
	if(onechecked){
		document.getElementById('setallact').value=theact;
		document.forms.mainform.submit();
	}else{
		alert("<?php print $yyNoSelO?>");
	}
}
/* ]]> */
</script>
		  <form name="mainform" method="post" action="adminstate.php">
		  	<input type="hidden" name="setallact" id="setallact" value="" />
            <table width="100%" border="0" cellspacing="1" cellpadding="1">
			  <tr> 
                <td width="100%" align="center"><strong><?php print $yyStaAdm?></strong><br /><br />
				<span style="font-size:10px"><?php print $yyFSANot?><br />&nbsp;</span></td>
			  </tr>
			  <tr> 
                <td colspan="<?php print $colspan?>" align="center">
				  <table border="0" cellspacing="1" cellpadding="3" class="cobtbl">
					<tr><td class="cobhl" colspan="3" align="center"><strong><?php print $yyWitSel?>...</strong></td></tr>
					<tr><td class="cobhl" align="right"><strong><?php print $yyEnable?>:</strong></td><td class="cobll" align="left"><select name="allenable" size="1"><option value="ON"><?php print $yyYes?></option><option value=""><?php print $yyNo?></option></select></td><td class="cobll"><input type="button" value="<?php print $yySubmit?>" onclick="setall('allenable')" /></td></tr>
					<tr><td class="cobhl" align="right"><strong><?php print $yyTax?>:</strong></td><td class="cobll" align="left"><input type="text" name="alltax" size="5" />%</td><td class="cobll"><input type="button" value="<?php print $yySubmit?>" onclick="setall('alltax')" /></td></tr>
					<tr><td class="cobhl" align="right"><strong><?php print $yyFSApp?>:</strong></td><td class="cobll" align="left"><select name="allfsa" size="1"><option value="ON"><?php print $yyYes?></option><option value=""><?php print $yyNo?></option></select></td><td class="cobll"><input type="button" value="<?php print $yySubmit?>" onclick="setall('allfsa')" /></td></tr>
<?php
	if($editzones){ ?>
					<tr><td class="cobhl" align="right"><strong><?php print $yyPZone?>:</strong></td><td class="cobll" align="left"><select name="allzone" size="1">
<?php	for($index=0; $index < $numzones; $index++){
			print '<option value="' . $allzones[$index]['pzID'] . '"';
			print '>' . $allzones[$index]['pzName'] . "</option>\n";
		} ?>
					</select></td><td class="cobll"><input type="button" value="<?php print $yySubmit?>" onclick="setall('allzone')" /></td></tr>
<?php
	} ?>
				  </table><br />
				</td>
			  </tr>
			</table>
			<table border="0" cellspacing="1" cellpadding="2" class="cobtbl">
			  <tr>
				<td class="cobhl" width="1%"><input type="checkbox" id="xdocheckall" value="1" onclick="docheckall()" /></td>
				<td class="cobhl"><strong><?php print $yyStaNam?></strong></td>
				<td class="cobhl" align="center"><strong><?php print $yyEnable?></strong></td>
				<td class="cobhl" align="center"><strong><?php print $yyTax?></strong></td>
				<td class="cobhl" align="center"><strong><acronym title="<?php print $yyFSApp?>"><?php print $yyFSA?></acronym></strong></td>
<?php
	if($editzones) print '<td class="cobhl" align="center"><strong>' . $yyPZone . '</strong></td>' ?>
				<td class="cobhl" align="center"><strong><?php print $yyModify?></strong></td>
			  </tr><?php
	$theids = @$_POST['ids'];
	$bgcolor='cobhl';
	$sSQL = "SELECT stateID,stateName,stateEnabled,stateTax,stateZone,stateFreeShip FROM states ORDER BY stateEnabled DESC,stateName";
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs = mysql_fetch_assoc($result)){
		if($bgcolor=='cobhl') $bgcolor='cobll'; else $bgcolor='cobhl';
		?><tr align="center" class="<?php print $bgcolor?>">
<td align="center"><input type="checkbox" name="ids[]" value="<?php print $rs['stateID']?>" <?php
		if(is_array($theids)){
			foreach($theids as $anid){
				if($anid==$rs['stateID']){
					print 'checked="checked" ';
					break;
				}
			}
		}
		?>/></td>
<td align="left"><?php
		if((int)$rs['stateEnabled']==1) print '<strong>';
		print $rs['stateName'];
		if((int)$rs['stateEnabled']==1) print '</strong>';?></td>
<td><?php
		if((int)$rs['stateEnabled']==1) print $yyYes; else print '&nbsp;';?></td>
<td><?php
		if((double)$rs['stateTax']!=0) print (double)$rs['stateTax'].'%'; else print '&nbsp;';?></td>
<td><?php
		if((int)$rs['stateFreeShip']==1) print $yyYes; else print '&nbsp;';?></td>
<?php
		if($editzones){
			if((int)$rs['stateEnabled']!=1)
				print '<td>-</td>';
			else{
				$foundzone=FALSE;
				for($index=0; $index < $numzones; $index++){
					if($rs['stateZone']==$allzones[$index]['pzID']){
						print '<td>' . $allzones[$index]['pzName'] . '</td>';
						$foundzone=TRUE;
					}
				}
				if(!$foundzone)print '<td>' . $yyUndef . '</td>';
			}
		}
		print '<td>';
		print '<input type="button" onclick="document.location=\'adminstate.php?id='.$rs['stateID'].'\'" value="' . $yyModify . '"/>';
		print '</td></tr>';
	}
	mysql_free_result($result);
?>
			  <tr> 
                <td class="cobll" width="100%" colspan="<?php print $colspan?>" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </form>
<?php
}
?>
