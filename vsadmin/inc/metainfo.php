<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(trim(@$prodid=='')) $prodid=trim(@$_GET['prod']);
if(trim(@$catid=='')) $catid=trim(@$_GET['cat']);
if(trim(@$manid=='')) $manid=trim(@$_GET['man']);
$productid='';
$productname='';
$productdescription='';
$sectionname='';
$sectiondescription='';
$topsection='';
$sntxt = 'sectionName';
$sdtxt = 'sectionDescription';
$pntxt = 'pName';
if(@$usemetalongdescription==TRUE) $pdtxt = 'pLongDescription'; else $pdtxt = 'pDescription';
function mi_escape_string($estr){
	if(version_compare(phpversion(),'4.3.0')=='-1') return(mysql_escape_string($estr)); else return(mysql_real_escape_string($estr));
}
if(function_exists('getadminsettings')){
	$alreadygotadmin = getadminsettings();
	$sntxt = getlangid('sectionName',256);
	$sdtxt = getlangid('sectionDescription',512);
	$pntxt = getlangid('pName',1);
	if(@$usemetalongdescription==TRUE) $pdtxt = getlangid('pLongDescription',4); else $pdtxt = getlangid('pDescription',2);
}
if(@$usecategoryname && $catid!=''){
	$sSQL = 'SELECT sectionID FROM sections WHERE '.$sntxt."='".mi_escape_string($catid)."'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs=mysql_fetch_assoc($result)){ $catname=$catid; $catid=$rs['sectionID']; }
	mysql_free_result($result);
}
if(@$usecategoryname && $manid!=''){
	$sSQL = "SELECT mfID FROM manufacturer WHERE mfName='".mi_escape_string($manid)."'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs=mysql_fetch_assoc($result)){ $manname=$manid; $manid=$rs['mfID']; }
	mysql_free_result($result);
}
if($prodid!=''){
	$result = mysql_query('SELECT pID,'.$pntxt.','.$pdtxt.','.$sntxt." FROM products INNER JOIN sections ON products.pSection=sections.sectionID WHERE pId='" . mi_escape_string($prodid) . "'") or print(mysql_error());
	if($rs = mysql_fetch_array($result)){
		$productid=str_replace('"', '&quot;', strip_tags($rs['pID']));
		$productname=str_replace('"', '&quot;', strip_tags($rs[$pntxt]));
		$productdescription=str_replace('"', '&quot;', strip_tags($rs[$pdtxt]));
		$sectionname=str_replace('"', '&quot;', strip_tags($rs[$sntxt]));
	}
	if($catid!='' && is_numeric($catid)){
		$result = mysql_query('SELECT '.$sntxt." FROM sections WHERE sectionID=" . $catid) or print(mysql_error());
		if($rs = mysql_fetch_array($result)) $sectionname=str_replace('"', '&quot;', strip_tags($rs[$sntxt]));
	}
}elseif($catid!='' && (is_numeric($catid) || @$usecategoryname)){
	$topsection=0;
	if(is_numeric($catid)) $sSQL="sectionID=".mi_escape_string($catid); else $sSQL="sectionName='".mi_escape_string($catid)."'";
	$result = mysql_query('SELECT '.$sntxt.','.$sdtxt.",topSection FROM sections WHERE " . $sSQL) or print(mysql_error());
	if($rs = mysql_fetch_array($result)){
		$sectionname=str_replace('"', '&quot;', strip_tags($rs[$sntxt]));
		$sectiondescription=str_replace('"', '&quot;', strip_tags($rs[$sdtxt]));
		$topsection=$rs['topSection'];
	}
	if($topsection!=0){
		$result = mysql_query('SELECT '.$sntxt.' FROM sections WHERE sectionID=' . $topsection) or print(mysql_error());
		if($rs = mysql_fetch_array($result))
			$topsection=str_replace('"', '&quot;', strip_tags($rs[$sntxt]));
	}else
		$topsection='';
}elseif($manid!='' && (is_numeric($manid) || @$usecategoryname)){
	$topsection='';
	if(function_exists('getadminsettings')) $sdtxt = getlangid('mfDescription',512); else $sdtxt = 'mfDescription';
	if(is_numeric($manid)) $sSQL="mfID=".mi_escape_string($manid); else $sSQL="mfName='".mi_escape_string($manid)."'";
	$result = mysql_query('SELECT mfName,'.$sdtxt.' FROM manufacturer WHERE ' . $sSQL) or print(mysql_error());
	if($rs = mysql_fetch_array($result)){
		$sectionname=str_replace('"', '&quot;', strip_tags($rs['mfName']));
		$sectiondescription=str_replace('"', '&quot;', strip_tags($rs[$sdtxt]));
	}
}
?>