<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$forceloginonhttps && (@$_SERVER['HTTPS']!='on' && @$_SERVER['SERVER_PORT']!='443') && strpos(@$pathtossl,'https')!==FALSE){ header('Location: '.$pathtossl.'vsadmin/'.basename($_SERVER['PHP_SELF'])); exit; }
$success=TRUE;
$dorefresh=FALSE;
$repeatedattempts=FALSE;
if($success){
	if(@$_POST["posted"]=="1"){
		$alreadygotadmin = getadminsettings();
		$thashedpw=dohashpw($_POST['pass']);
		$adminuser="";
		$adminpassword="";
		$adminuserlock=0;
		$sSQL = "SELECT adminEmail,adminUser,adminPassword,adminUserLock,adminPWLastChange FROM admin WHERE adminID=1";
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_assoc($result);
		$datelastchanged=$rs['adminPWLastChange'];
		$adminuser = $rs['adminUser'];
		$adminpassword = $rs['adminPassword'];
		$adminuserlock=$rs['adminUserLock'];
		mysql_free_result($result);
		if(@$storesessionvalue=='') $storesessionvalue='virtualstore';
		if(@$disallowlogin==TRUE){
			$success=FALSE;
			$errmsg = $yyLogSor;
		}elseif($adminuserlock>=6 && @$nopadsscompliance!=TRUE){
			$success=FALSE;
			$disallowlogin=TRUE;
			$repeatedattempts=TRUE;
		}elseif(! (trim($_POST['user'])==$adminuser && $thashedpw==$adminpassword)){
			$sSQL="SELECT adminloginid,adminloginname,adminloginpassword,adminloginpermissions,adminLoginLock FROM adminlogin WHERE adminloginname='" . escape_string($_POST['user']) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_array($result)){
				if($rs['adminLoginLock']>=6 && @$nopadsscompliance!=TRUE){
					$success=FALSE;
					$disallowlogin=TRUE;
					$repeatedattempts=TRUE;
				}elseif($rs['adminloginpassword']==$thashedpw){
					$_SESSION['loggedon'] = $storesessionvalue;
					$_SESSION['loggedonpermissions'] = $rs['adminloginpermissions'];
					$_SESSION['loginid']=$rs['adminloginid'];
					$_SESSION['loginuser']=$rs['adminloginname'];
					$dorefresh=TRUE;
				}else{
					$success=FALSE;
					$errmsg = $yyLogSor;
				}
			}else{
				$success=FALSE;
				$errmsg = $yyLogSor;
			}
			mysql_free_result($result);
		}else{
			$_SESSION['loggedon'] = $storesessionvalue;
			$_SESSION['loggedonpermissions'] = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
			$_SESSION['loginid']=0;
			$_SESSION['loginuser']=$adminuser;
			if($thashedpw=='50481f28d0f9c62842ad64b8985ab91c') $_SESSION['mustchangepw']='A';
			if(time()-strtotime($datelastchanged)>(90*60*60*24) && @$nopadsscompliance!=TRUE) $_SESSION['mustchangepw']='B';
			$dorefresh=TRUE;
		}
		if(! $success){
			mysql_query("UPDATE admin SET adminUserLock=adminUserLock+1 WHERE adminUser='".escape_string($_POST['user'])."'") or print(mysql_error());
			mysql_query("UPDATE adminlogin SET adminLoginLock=adminLoginLock+1 WHERE adminLoginName='".escape_string($_POST['user'])."'") or print(mysql_error());
		}else{
			mysql_query("UPDATE admin SET adminUserLock=0 WHERE adminUser='".escape_string($_POST['user'])."'") or print(mysql_error());
			mysql_query("UPDATE adminlogin SET adminLoginLock=0 WHERE adminLoginName='".escape_string($_POST['user'])."'") or print(mysql_error());
		}
		logevent($_POST['user'],"LOGIN",$success,"LOGIN","");
		if(@$notifyloginattempt==TRUE){
			if(@$htmlemails==TRUE) $emlNl = "<br />"; else $emlNl="\n";
			$sMessage = "This is notification of a login attempt at your store."  . $emlNl;
			$sMessage .= $storeurl . $emlNl;
			if($success || (trim($_POST['user'])==$adminuser && trim($_POST['pass'])==$adminpassword))
				$sMessage .= "A correct login / password was used." . $emlNl;
			else{
				$sMessage .= "An incorrect login was used." . $emlNl .
					"Username: " . $_POST["user"] . $emlNl .
					"Password: " . $_POST["pass"] . $emlNl;
			}
			$sMessage .= "User Agent: " . @$_SERVER["HTTP_USER_AGENT"] . $emlNl .
				"IP: " . @$_SERVER["REMOTE_ADDR"] . $emlNl;
			dosendemail($emailAddr, $emailAddr, '', 'Login attempt at your store', $sMessage);
		}
		if($success && @$_POST['cook']=='ON'){
			setcookie("WRITECKL",trim($_POST['user']),time()+(60*60*24*365), '/', '', @$_SERVER['HTTPS']=='on');
			setcookie("WRITECKP",$thashedpw,time()+(60*60*24*365), '/', '', @$_SERVER['HTTPS']=='on');
		}
		if($dorefresh){
			print '<meta http-equiv="refresh" content="1; url=admin.php">';
		}
	}
}
?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
<?php
	if(@$_POST["posted"]=="1" && $success){ ?>
        <tr>
          <td width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center" class="content"><br /><strong><?php print $yyLogCor?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?><a href="admin.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />
				<p align="center"><img src="../images/clearpixel.gif" width="320" height="1" alt="" /> 
                  </p>
                </td>
			  </tr>
			</table>
		  </td>
        </tr>
<?php
	}else{
		if(@$disallowlogin){ $success=FALSE; $errmsg="<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;" . "Login Disabled" . ($repeatedattempts?'<br />(Repeated login attempts)':'') . "<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;"; } ?>
        <tr>
          <td width="100%">
			<form method="post" name="mainform" action="login.php">
			<input type="hidden" name="posted" value="1">
            <table width="302" border="0" align="center" cellpadding="2" cellspacing="0" bgcolor="#E9EAEE">
			  <tr> 
                <td width="100%" colspan="2" align="center"><a href="admin.php"><img src="adminimages/loginect.jpg" alt="Control Panel" border="0" width="302" height="62" /></a></td>
			  </tr>
<?php	if(! $success){ ?>
			  <tr> 
                <td class="content" width="100%" colspan="2" align="center"><span style="color:#FF0000"><?php print $errmsg?></span></td>
			  </tr>
<?php	}
		if(@$disallowlogin!=TRUE){ ?>
              <tr>
                <td class="content" width="30%" align="right"><strong><?php print $yyUname?>: </strong></td>
				<td align="left"><input type="text" name="user" id="user" size="20" /></td>
			  </tr>
			  <tr>
                <td class="content" align="right"><strong><?php print $yyPass?>: </strong></td>
				<td align="left"><input type="password" name="pass" size="20" autocomplete="off" /></td>
			  </tr>
			  <tr>
                <td align="right"><input type="checkbox" name="cook" value="ON" /></td>
				<td align="left"><strong><?php print $yyWrCoo?></strong><br /><span style="font-size:10px"><?php print $yyNoRec?></span></td>
			  </tr>
			  <tr>
                <td width="100%" colspan="2" align="center"><br /><input type="submit" value="<?php print $yySubmit?>"><br />
				<p align="center"><img src="../images/clearpixel.gif" width="300" height="1" alt="" /></p>
                </td>
			  </tr>
<?php	} ?>
			  <tr>
			    <td colspan="2" align="center" bgcolor="#23548C"><span style="color:#FFFFFF"><a href="http://www.ecommercetemplates.com/">Shopping
		          Cart Software</a> by Ecommerce Templates</span></td>
			    </tr>
            </table>
			</form>
          </td>
        </tr>
<script language="javascript" type="text/javascript">
<!--
document.getElementById('user').focus();
// -->
</script>
<?php
	} ?>
      </table>