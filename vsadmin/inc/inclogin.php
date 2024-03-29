<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if($_SESSION["loggedon"] != $storesessionvalue || @$disallowlogin==TRUE) exit;
$success=TRUE;
$errmsg='';
function addtopasswordhistory($loginid,$hpw){
	$sSQL2="INSERT INTO passwordhistory (liID,pwhPwd,datePWChanged) VALUES (" . $loginid . ",'" . $hpw . "','".date('Y-m-d H:i:s')."')";
	mysql_query($sSQL2) or print(mysql_error());
	$sSQL2 = "SELECT COUNT(*) AS pwhcount FROM passwordhistory WHERE liID=" . $loginid;
	$result = mysql_query($sSQL2) or print(mysql_error());
	$rs=mysql_fetch_assoc($result);
		$pwhcount=$rs['pwhcount'];
	mysql_free_result($result);
	if($pwhcount>4){
		$sSQL2="SELECT pwhID FROM passwordhistory WHERE liID=" . $loginid . " ORDER BY datePWChanged LIMIT 0,".($pwhcount-4);
		$result = mysql_query($sSQL2) or print(mysql_error());
		while($rs=mysql_fetch_assoc($result)){
			mysql_query("DELETE FROM passwordhistory WHERE pwhID=".$rs['pwhID']) or print(mysql_error());
		}
	}
}
if(@$_POST['posted']=='1'){
	if(@$_POST['act']=='changeprimary'){
		if(trim(@$_POST['pass']) != trim(@$_POST['pass2'])){
			$success = FALSE;
			$errmsg=$yyNoMat;
		}elseif(trim(@$_POST['pass'])=="changeme" && @$nopadsscompliance!=TRUE){
			$success = FALSE;
			$errmsg=$yyLIErr1;
		}else{
			$hashedpw=dohashpw(trim(@$_POST['pass']));
			if(@$nopadsscompliance!=TRUE){
				$sSQL="SELECT pwhID FROM passwordhistory WHERE liID=".@$_SESSION['loginid']." AND pwhPwd='". $hashedpw."'";
				$result = mysql_query($sSQL) or print(mysql_error());
				if(mysql_num_rows($result)>0){
					$success=FALSE;
					$errmsg="You cannot use the same password as any of your last 4 previous passwords.";
				}
				mysql_free_result($result);
			}
			if($success){
				if(substr(@$_SESSION['loggedonpermissions'],20,1)!='X'){
					$sSQL = "UPDATE adminlogin SET adminLoginName='" . trim(@$_POST['user']) . "'";
					if(trim(@$_POST['pass'])!=''){
						$sSQL .= ",adminLoginPassword='" . $hashedpw . "',adminLoginLastChange='".date('Y-m-d')."'";
						$_SESSION['mustchangepw']=NULL;
						unset($_SESSION['mustchangepw']);
					}
					$sSQL .= " WHERE adminLoginID=" . $_SESSION['loginid'];
				}else{
					$sSQL = "UPDATE admin SET adminUser='" . trim(@$_POST['user']) . "'";
					if(trim(@$_POST['pass'])!=''){
						$sSQL .= ",adminPassword='" . $hashedpw . "',adminPWLastChange='".date('Y-m-d')."'";
						$_SESSION['mustchangepw']=NULL;
						unset($_SESSION['mustchangepw']);
					}
					$sSQL .= " WHERE adminID=1";
				}
				mysql_query($sSQL) or print(mysql_error());
				addtopasswordhistory(@$_SESSION['loginid'],$hashedpw);
				print '<meta http-equiv="refresh" content="4; url=admin.php">';
			}
		}
		logevent(@$_SESSION['loginuser'],"CHANGEPASSWORD",$success,"adminlogin.php",@$_POST['user']);
	}elseif(substr(@$_SESSION['loggedonpermissions'],20,1)!='X'){
		$success = FALSE;
		$errmsg="No Permissions";
	}elseif(@$_POST['act']=='doaddnew' || @$_POST['act']=='domodify'){
		$permissions = '';
		if(@$_POST['main']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['orders']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['payprov']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['affiliate']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['clientlogin']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['products']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['categories']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['discounts']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['regions']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['shipping']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['ordstatus']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['dropship']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['ipblock']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['maillist']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['statistics']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['ratings']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['contentregion']=='ON') $permissions .= 'X'; else $permissions .= 'O';
		if(@$_POST['act']=='doaddnew'){
			$sSQL="SELECT adminloginid FROM adminlogin WHERE adminloginname='" . escape_string(@$_POST['user']) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0)
				$success=FALSE;
			mysql_free_result($result);
			$sSQL="SELECT adminID FROM admin WHERE adminUser='" . escape_string(@$_POST['user']) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0)
				$success=FALSE;
			mysql_free_result($result);
			if(!$success)
				$errmsg="That login is already in use. Please choose another.";
			$sSQL = "INSERT INTO adminlogin (adminloginname,adminloginpassword,adminloginpermissions) VALUES ('" . escape_string(@$_POST['user']) . "','" . escape_string(dohashpw(@$_POST['pass'])) . "','" . $permissions . "')";
		}else{
			$sSQL = "UPDATE adminlogin SET adminLoginLock=0,adminloginname='" . escape_string(@$_POST['user']) . "',";
			if(trim(@$_POST['pass'])!=''){
				$hashedpw=dohashpw(trim(@$_POST['pass']));
				$sSQL .= "adminloginpassword='" . escape_string($hashedpw) . "',";
				if(@$nopadsscompliance!=TRUE){
					$result = mysql_query("SELECT pwhID FROM passwordhistory WHERE liID=".@$_SESSION['loginid']." AND pwhPwd='". $hashedpw."'") or print(mysql_error());
					if(mysql_num_rows($result)>0){
						$success=FALSE;
						$errmsg="You cannot use the same password as any of your last 4 previous passwords.";
					}
					mysql_free_result($result);
					if($success) addtopasswordhistory(@$_SESSION['loginid'],$hashedpw);
				}
			}
			$sSQL .= "adminloginpermissions='" . $permissions . "' WHERE adminloginid=" . @$_POST['id'];
		}
		if($success){
			mysql_query($sSQL) or print(mysql_error());
			print '<meta http-equiv="refresh" content="2; url=adminlogin.php">';
		}
		logevent(@$_SESSION['loginuser'],"ALTERLOGIN",$success,"adminlogin.php",@$_POST['user']);
	}elseif(@$_POST['act']=='delete'){
		$sSQL = "DELETE FROM adminlogin WHERE adminloginid=" . @$_POST['id'];
		mysql_query($sSQL) or print(mysql_error());
		print '<meta http-equiv="refresh" content="2; url=adminlogin.php">';
	}
}
?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function checkform(frm){
	if(frm.pass.value!=""||frm.pass2.value!=""){
		if(frm.pass.value!=frm.pass2.value){
			alert("Your password does not match the confirm password.");
			frm.pass.focus();
			return(false);
		}
<?php	if(@$nopadsscompliance!=TRUE){ ?>
		if(frm.pass.value.length<7){
			alert("Your password must be at least 7 characters.");
			frm.pass.focus();
			return(false);
		}
		if(frm.pass.value=="changeme"){
			alert("That password is illegal.");
			frm.pass.focus();
			return(false);
		}
		var regexn = /[0-9]/;
		var regexa = /[a-z]/i;
		if(!(regexn.test(frm.pass.value)&&regexa.test(frm.pass.value))){
			alert("Your password must contain at least one numeric and one alphabetic character.");
			frm.pass.focus();
			return(false);
		}
<?php	} ?>
	}
	return(true);
}
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
function newrec(){
	document.mainform.act.value = "addnew";
	document.mainform.submit();
}
function delrec(id){
cmsg = "<?php print $yyConDel?>\n"
if(confirm(cmsg)){
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.submit();
}
}
/* ]]> */
</script>
	  <table border="0" cellspacing="1" cellpadding="1" width="100%" align="center">
<?php
if(@$_POST['act']=='addnew' || @$_POST['act']=='modify'){
	if(@$_POST['act']=='modify' && is_numeric(@$_POST['id'])){
		$sSQL = "SELECT adminloginid,adminloginname,adminloginpassword,adminloginpermissions FROM adminlogin WHERE adminloginid=" . @$_POST['id'];
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_assoc($result);
		mysql_free_result($result);
		$adminloginname=$rs['adminloginname'];
		$adminloginpassword='';
		$permissions=$rs['adminloginpermissions'];
	}else{
		$adminloginname='';
		$adminloginpassword='';
		$permissions='';
	}
?>
		<tr>
		  <td width="100%" align="center">
		  <form method="post" action="adminlogin.php" onsubmit="return checkform(this)">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="<?php print (@$_POST['act']=='modify' ? 'domodify' : 'doaddnew') ?>" />
			<input type="hidden" name="id" value="<?php print @$_POST['id']?>" />
			<table width="80%" border="0" cellspacing="0" cellpadding="3">
			  <tr>
				<td colspan="2" align="center"><br /><strong><?php print $yyPlDfPr?></strong></td>
			  </tr>
			  <tr> 
				<td width="50%" align="right"><strong><?php print $redasterix.$yyUname?>:</strong></td>
				<td align="left"><input type="text" name="user" size="20" value="<?php print $adminloginname?>" /></td>
			  </tr>
			  <tr> 
				<td width="50%" align="right"><strong><?php print (@$_POST['act']=='modify'?$yyReset.' '.$yyPass:$redasterix.$yyPass)?>:</strong></td>
				<td align="left"><input type="password" name="pass" size="20" value="<?php print $adminloginpassword?>" autocomplete="off" /></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyPassCo?>: </strong></td>
				<td align="left"><input type="password" name="pass2" size="20" value="" autocomplete="off" /></td>
			  </tr>
			  <tr>
				<td colspan="2" align="center"><hr /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyLLMain?></strong></td><td align="left"><input type="checkbox" name="main" value="ON" <?php if(substr($permissions,0,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyLLOrds?></strong></td><td align="left"><input type="checkbox" name="orders" value="ON" <?php if(substr($permissions,1,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyLLPayP?></strong></td><td align="left"><input type="checkbox" name="payprov" value="ON" <?php if(substr($permissions,2,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyLLAffl?></strong></td><td align="left"><input type="checkbox" name="affiliate" value="ON" <?php if(substr($permissions,3,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyLLClLo?></strong></td><td align="left"><input type="checkbox" name="clientlogin" value="ON" <?php if(substr($permissions,4,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyLLProA . " + " . $yyLLProO?></strong></td><td align="left"><input type="checkbox" name="products" value="ON" <?php if(substr($permissions,5,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyLLCats?></strong></td><td align="left"><input type="checkbox" name="categories" value="ON" <?php if(substr($permissions,6,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyLLDisc . " + " . $yyLLQuan . ' + ' . $yyLLGftC?></strong></td><td align="left"><input type="checkbox" name="discounts" value="ON" <?php if(substr($permissions,7,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyLLStat . " + " . $yyLLCoun?></strong></td><td align="left"><input type="checkbox" name="regions" value="ON" <?php if(substr($permissions,8,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyLLZone . " + " . $yyLLShpM?></strong></td><td align="left"><input type="checkbox" name="shipping" value="ON" <?php if(substr($permissions,9,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyLLOrSt ?></strong></td><td align="left"><input type="checkbox" name="ordstatus" value="ON" <?php if(substr($permissions,10,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyManuf . ' + ' . $yyDrShpr?></strong></td><td align="left"><input type="checkbox" name="dropship" value="ON" <?php if(substr($permissions,11,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyIPBlock ?></strong></td><td align="left"><input type="checkbox" name="ipblock" value="ON" <?php if(substr($permissions,12,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyMaLiMa ?></strong></td><td align="left"><input type="checkbox" name="maillist" value="ON" <?php if(substr($permissions,13,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyStatis ?></strong></td><td align="left"><input type="checkbox" name="statistics" value="ON" <?php if(substr($permissions,14,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyRating ?></strong></td><td align="left"><input type="checkbox" name="ratings" value="ON" <?php if(substr($permissions,15,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyContReg ?></strong></td><td align="left"><input type="checkbox" name="contentregion" value="ON" <?php if(substr($permissions,16,1)=='X') print 'checked="checked"'; ?>/></td>
			  </tr>
			  <tr> 
				<td colspan="2" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" />  <input type="reset" value="<?php print $yyReset?>" /></td>
			  </tr>
			</table>
		  </form>
		  </td>
		</tr>
<?php
}elseif(@$_POST["posted"]=="1" && $success){ ?>
		<tr>
		  <td width="100%">
			<table width="100%" border="0" cellspacing="3" cellpadding="3">
			  <tr> 
				<td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
						<?php print $yyNoAuto?> <a href="admin.php"><strong><?php print $yyClkHer?></strong></a>.<br />
						<br />&nbsp;<br />&nbsp;</td>
			  </tr>
			</table></td>
		</tr>
<?php
}elseif(@$_POST["posted"]=="1"){ ?>
		<tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a><br />
						<br />&nbsp;<br />&nbsp;</td>
			  </tr>
			</table></td>
        </tr>
<?php
}else{
	if(substr(@$_SESSION['loggedonpermissions'],20,1)=='X'){
		$sSQL = "SELECT adminUser FROM admin WHERE adminID=1";
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_assoc($result);
		$theuser = $rs["adminUser"];
		mysql_free_result($result);
	}else{
		$sSQL = "SELECT adminLoginName FROM adminlogin WHERE adminLoginID=".@$_SESSION['loginid'];
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_assoc($result);
		$theuser = $rs["adminLoginName"];
		mysql_free_result($result);
	}
	if(@$_SESSION['mustchangepw']!=''){
		if(@$_SESSION['mustchangepw']=="A") $errmsg=$yyLIErr1."<br />".$errmsg;
		if(@$_SESSION['mustchangepw']=="B") $errmsg=$yyLIErr2."<br />".$errmsg;
		$success=FALSE;
	}
?>
		<tr>
		  <td width="100%" align="center">
		  <form method="post" action="adminlogin.php" onsubmit="return checkform(this)">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="changeprimary" />
			<table style="border:1px dotted #194C7F" width="80%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
				<td colspan="2" align="center"><br /><strong><?php print $yyNewUN?></strong></td>
			  </tr>
<?php	if(! $success){ ?>
			  <tr> 
				<td colspan="2" align="center"><br /><span style="color:#FF0000"><?php print $errmsg?></span></td>
			  </tr>
<?php	} ?>
			  <tr> 
				<td width="50%" align="right"><strong><?php print $redasterix.$yyUname?>: </strong></td>
				<td width="50%" align="left"><input type="text" name="user" size="20" value="<?php print $theuser?>" /></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyReset.' '.$yyPass?>: </strong></td>
				<td align="left"><input type="password" name="pass" size="20" value="" autocomplete="off" /></td>
			  </tr>
			  <tr> 
				<td align="right"><strong><?php print $yyPassCo?>: </strong></td>
				<td align="left"><input type="password" name="pass2" size="20" value="" autocomplete="off" /></td>
			  </tr>
			  <tr> 
				<td colspan="2" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" /></td>
			  </tr>
			</table>
		  </form>
		  </td>
		</tr>
<?php	if(substr(@$_SESSION['loggedonpermissions'],20,1)=='X'){ ?>
		<tr>
		  <td width="100%" align="center">
		  <form method="post" name="mainform" action="adminlogin.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="" />
			<input type="hidden" name="id" value="" />
			<table style="border:1px dotted #194C7F" width="80%" border="0" cellspacing="0" cellpadding="3">
<?php
	$sSQL = "SELECT adminloginid,adminloginname,adminloginpermissions FROM adminlogin";
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result)==0){
?>
			  <tr> 
				<td colspan="20" align="center"><br /><strong><?php print $yyNoSecL?></strong></td>
			  </tr>

<?php
	}else{ ?>
			  <tr> 
				<td colspan="20" align="center"><br /><strong><?php print $yySecLog?></strong></td>
			  </tr>
			  <tr> 
				<td><strong><?php print $yyLiName?></strong></td>
				<td><acronym title="<?php print $yyLLMain?>">MAI</acronym></td>
				<td><acronym title="<?php print $yyLLOrds?>">ORD</acronym></td>
				<td><acronym title="<?php print $yyLLPayP?>">PAY</acronym></td>
				<td><acronym title="<?php print $yyLLAffl?>">AFF</acronym></td>
				<td><acronym title="<?php print $yyLLClLo?>">LOG</acronym></td>
				<td><acronym title="<?php print $yyLLProA . " + " . $yyLLProO?>">PRO</acronym></td>
				<td><acronym title="<?php print $yyLLCats?>">CAT</acronym></td>
				<td><acronym title="<?php print $yyLLDisc . " + " . $yyLLQuan?>">DSC</acronym></td>
				<td><acronym title="<?php print $yyLLStat . " + " . $yyLLCoun?>">REG</acronym></td>
				<td><acronym title="<?php print $yyLLZone . " + " . $yyLLShpM?>">SHI</acronym></td>
				<td><acronym title="<?php print $yyLLOrSt ?>">ORS</acronym></td>
				<td><acronym title="<?php print $yyDrShpr ?>">DRP</acronym></td>
				<td><acronym title="<?php print $yyIPBlock ?>">IPB</acronym></td>
				<td><acronym title="<?php print $yyMaLiMa ?>">MLM</acronym></td>
				<td><acronym title="<?php print $yyStatis ?>">STA</acronym></td>
				<td><acronym title="<?php print $yyRating ?>">RAT</acronym></td>
				<td><acronym title="<?php print $yyContReg ?>">CRG</acronym></td>
				<td><strong><?php print $yyModify?></strong></td>
				<td><strong><?php print $yyDelete?></strong></td>
			  </tr>
<?php	while($rs = mysql_fetch_assoc($result)){
			if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark'; ?>
			  <tr class="<?php print $bgcolor?>">
				<td align="left"> &nbsp; <?php print $rs['adminloginname']?></td>
<?php			for($index=0; $index<17; $index++){
					print '<td>';
					if(substr($rs['adminloginpermissions'],$index,1)=='X') print 'X'; else print '&nbsp;';
					print '</td>';
				} ?>
				<td align="center"><input type="button" value="<?php print $yyModify?>" onclick="modrec('<?php print $rs['adminloginid']?>')" /></td>
				<td align="center"><input type="button" value="<?php print $yyDelete?>" onclick="delrec('<?php print $rs['adminloginid']?>')" /></td>
			  </tr>
<?php	}
	}
	mysql_free_result($result); ?>
			  <tr> 
				<td colspan="20" align="center">&nbsp;<br /><input type="button" value="<?php print $yyNewSec?>" onclick="newrec()" /><br />&nbsp;</td>
			  </tr>
			</table>
		  </form>
		  </td>
		</tr>
<?php
	}
} ?>
	  </table>