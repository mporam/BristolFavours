<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
$catname='';
$alreadygotadmin = getadminsettings();
$theid=trim(@$_GET['region']);
if(@$regionid!='url' && is_numeric(@$regionid)) $theid=$regionid;
if(! is_numeric($theid)) $theid=0;
$sSQL = "SELECT ".getlangid('contentData',32768)." FROM contentregions WHERE contentID='".escape_string($theid)."'";
$result = mysql_query($sSQL) or print(mysql_error());
if($rs = mysql_fetch_array($result))
	$contentdata=$rs[getlangid("contentData",32768)];
else
	$contentdata = "Content Region ID " . $theid . " not defined.";
mysql_free_result($result);
print $contentdata;
$regionid='';
?>

