<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$incfunctionsdefined!=TRUE && @$isadmincsv!=TRUE){
	print 'Illegal Call';
	flush();
	exit;
}
if(@$forceloginonhttps && (@$_SERVER['HTTPS']!='on' && @$_SERVER['SERVER_PORT']!='443') && strpos(@$pathtossl,'https')!==FALSE){ header('Location: '.(substr($pathtossl,-1)=='/'?substr($pathtossl,0,-1):$pathtossl).$_SERVER['PHP_SELF']); exit; }
if(@$storesessionvalue=="") $storesessionvalue="virtualstore";
$mustchangefordate=FALSE;
if(@$nopadsscompliance!=TRUE){
	header('Cache-Control: no-store,no-cache');
	header('Pragma: no-cache');
}
if(@$_SESSION['loggedon'] != $storesessionvalue && trim(@$_COOKIE['WRITECKL'])!='' && @$disallowlogin!=TRUE){
	$sSQL="SELECT adminID,adminUser,adminPWLastChange FROM admin WHERE adminPassword='" . escape_string(trim(@$_COOKIE['WRITECKP'])) . "' AND adminID=1";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs=mysql_fetch_assoc($result)){
		if($rs['adminUser']==trim(@$_COOKIE['WRITECKL'])){
			$_SESSION['loggedon'] = $storesessionvalue;
			$_SESSION['loggedonpermissions'] = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
			$_SESSION['loginid']=0;
			$_SESSION['loginuser']=$rs['adminUser'];
			if(time()-strtotime($rs['adminPWLastChange'])>(90*60*60*24) && @$nopadsscompliance!=TRUE){ $_SESSION['mustchangepw']='B'; $mustchangefordate=TRUE; }
		}
	}
	mysql_free_result($result);
	if(@$_SESSION['loggedon']!=$storesessionvalue){
		$sSQL="SELECT adminloginid,adminloginname,adminloginpermissions,adminLoginLastChange FROM adminlogin WHERE adminloginname='" . escape_string(trim(@$_COOKIE['WRITECKL'])) . "' AND adminloginpassword='" . escape_string(trim(@$_COOKIE['WRITECKP'])) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_assoc($result)){
			if($rs['adminloginname']==trim(@$_COOKIE['WRITECKL'])){
				$_SESSION['loggedon'] = $storesessionvalue;
				$_SESSION['loggedonpermissions'] = $rs['adminloginpermissions'];
				$_SESSION['loginid']=$rs['adminloginid'];
				$_SESSION['loginuser']=$rs['adminloginname'];
				if(time()-strtotime($rs['adminLoginLastChange'])>(90*60*60*24) && @$nopadsscompliance!=TRUE){ $_SESSION['mustchangepw']='B'; $mustchangefordate=TRUE; }
			}
		}
		mysql_free_result($result);
	}
	logevent(@$_COOKIE['WRITECKL'],'LOGIN',@$_SESSION['loggedon']==$storesessionvalue,'LOGIN','');
}
if(@$_SERVER['HTTPS'] == 'on' || @$_SERVER['SERVER_PORT'] == '443')$prot='https://';else $prot='http://';
if(@$_SESSION['loggedon'] != $storesessionvalue || @$disallowlogin==TRUE){
	header('Location: '.$prot.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/login.php');
	exit;
}
if((@$_SESSION['mustchangepw']!='' || $mustchangefordate) && ! (@$thispagename=='adminlogin')){
	header('Location: '.$prot.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/adminlogin.php');
	exit;
}
$isprinter=FALSE;
$alreadygotadmin = getadminsettings();
?>