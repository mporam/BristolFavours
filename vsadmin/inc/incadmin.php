<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
$success=0;
if(@$storesessionvalue=='') $storesessionvalue='virtualstore'.time();
if(@$_SESSION['loggedon'] != $storesessionvalue || @$disallowlogin==TRUE) exit;
if(@$_SESSION['loginid']==0 && @$_GET['act']=='events'){
	logevent(@$_SESSION['loginuser'],'EVENTLOG',TRUE,'admin.php','VIEW LOG');
	$sSQL = "SELECT userID,eventType,eventDate,eventSuccess,eventOrigin,areaAffected FROM auditlog ORDER BY logID DESC";
?>
<br /><br />
<div class="heading">
	<form method="post" action="dumporders.php">
	<input type="hidden" name="act" value="dumpevents" />
	<input type="submit" value="Dump Event Log" /> Event Log
	</form>
</div>
<table width="98%" class="admin-table-a">
  <thead>
	<tr>
	  <th scope="col">User ID</th>
	  <th scope="col">Event Type</th>
	  <th scope="col">Success</th>
	  <th scope="col">Origin</th>
	  <th scope="col">Area Affected</th>
	  <th scope="col">Date</th>
	</tr>
  </thead>
<?php
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result)>0){
		while($rs=mysql_fetch_assoc($result)){
			if($rs['eventSuccess']!=0){ $startfont=''; $endfont=''; }else{ $startfont='<span style="color:#FF0000">'; $endfont='</span>'; } ?>
  <tr>
	<td><?php print $startfont . htmlspecials(trim($rs['userID'])!=''?$rs['userID']:'-') . $endfont?></td>
	<td><?php print $startfont . htmlspecials(trim($rs['eventType'])!=''?$rs['eventType']:'-') . $endfont?></td>
	<td><?php print $startfont . htmlspecials($rs['eventSuccess']!=0?'TRUE':'FALSE') . $endfont?></td>
	<td><?php print $startfont . htmlspecials(trim($rs['eventOrigin'])!=''?$rs['eventOrigin']:'-') . $endfont?></td>
	<td><?php print $startfont . htmlspecials(trim($rs['areaAffected'])!=''?$rs['areaAffected']:'-') . $endfont?></td>
	<td><?php print $startfont . htmlspecials(trim($rs['eventDate'])!=''?$rs['eventDate']:'-') . $endfont?></td>
  </tr>
<?php	}
	}else{ ?>
  <tr>
    <td class="new" colspan="6" align="center">No events in log.</td>
  </tr>
<?php
	}
	mysql_free_result($result);
?>
</table>
<?php
}else{
if(@$dateadjust=='') $dateadjust=0;
$sSQL = 'SELECT adminShipping,adminVersion,adminUser,adminPassword FROM admin WHERE adminID=1';
$result = mysql_query($sSQL) or print(mysql_error());
$rs = mysql_fetch_assoc($result);
$storeVersion = $rs['adminVersion'];
$adminUser = $rs['adminUser'];
$adminPassword = $rs['adminPassword'];
mysql_free_result($result);
$neworders = 0;
$sSQL = "SELECT COUNT(*) AS thecnt FROM orders WHERE ordStatus>=2 AND ordDate>='".date('Y-m-d', time()+($dateadjust*60*60))."'";
$result = mysql_query($sSQL) or print(mysql_error());
if($rs = mysql_fetch_array($result)){
	if(!is_null($rs['thecnt'])) $neworders=$rs['thecnt'];
}		
$newratings = 0;
$sSQL = 'SELECT COUNT(*) AS thecnt FROM ratings WHERE rtApproved=0';
$result = mysql_query($sSQL) or print(mysql_error());
if($rs = mysql_fetch_array($result)){
	if(!is_null($rs['thecnt'])) $newratings=$rs['thecnt'];
}		
$newaccounts = 0;
$sSQL = "SELECT COUNT(*) AS thecnt FROM customerlogin WHERE clDateCreated>'".date('Y-m-d', time()-(60*60*24))."'";
$result = mysql_query($sSQL) or print(mysql_error());
if($rs = mysql_fetch_array($result)){
	if(!is_null($rs['thecnt'])) $newaccounts=$rs['thecnt'];
}	
$newmaillist = 0;
$sSQL = "SELECT COUNT(*) AS thecnt FROM mailinglist WHERE mlConfirmDate>'".date('Y-m-d', time()-(60*60*24))."'";
$result = mysql_query($sSQL) or print(mysql_error());
if($rs = mysql_fetch_array($result)){
	if(!is_null($rs['thecnt'])) $newmaillist=$rs['thecnt'];
}	
$newaffiliate = 0;
$sSQL = "SELECT COUNT(*) AS thecnt FROM affiliates WHERE affilDate>'".date('Y-m-d', time()-(60*60*24))."'";
$result = mysql_query($sSQL) or print(mysql_error());
if($rs = mysql_fetch_array($result)){
	if(!is_null($rs['thecnt'])) $newaffiliate=$rs['thecnt'];
}	
$newgiftcert = 0;
$sSQL = "SELECT COUNT(*) AS thecnt FROM giftcertificate WHERE gcDateCreated>'".date('Y-m-d', time()-(60*60*24))."'";
$result = mysql_query($sSQL) or print(mysql_error());
if($rs = mysql_fetch_array($result)){
	if(!is_null($rs['thecnt'])) $newgiftcert=$rs['thecnt'];
}
mysql_free_result($result);
$newstocknotify = 0;
if(@$notifybackinstock){
	$sSQL = "SELECT COUNT(*) AS thecnt FROM notifyinstock";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_array($result)){
		if(!is_null($rs['thecnt'])) $newstocknotify=$rs['thecnt'];
	}
	mysql_free_result($result);
}
$newlogevents=0;
if(@$_SESSION['loginid']==0){
	$sSQL = 'SELECT COUNT(*) AS thecnt FROM auditlog WHERE eventSuccess=0';
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_array($result)){
		if(! is_null($rs['thecnt'])) $newlogevents = $rs['thecnt'];
	}
	mysql_free_result($result);
}
if(@$_GET['writeck']=='no'){
	print '<script src="savecookie.php?DELCK=yes"></script>';
	print '<meta http-equiv="Refresh" content="2; URL=admin.php">';
	$success=1;
}
$admindatestr='Y-m-d';
if(@$admindateformat=='') $admindateformat=0;
if($admindateformat==1)
	$admindatestr='m/d/Y';
elseif($admindateformat==2)
	$admindatestr='d/m/Y';
	if($success==1){ ?>
			  <tr> 
				<td colspan="2" width="100%" align="center"><p>&nbsp;</p><p>&nbsp;</p>
				  <p><strong><?php print $yyOpSuc?></strong></p><p>&nbsp;</p>
				  <p><span style="font-size:10px"><?php print $yyNowFrd?><br /><br /><?php print $yyNoAuto?> <a href="admin.php"><?php print $yyClkHer?></a>.</span></td>
			  </tr>
<?php
	}elseif($success==2){ ?>
			  <tr> 
				<td colspan="2" width="100%" align="center"><p>&nbsp;</p><p>&nbsp;</p>
				  <p><strong><?php print $yyOpFai?></strong></p><p>&nbsp;</p>
				  <p><?php print $yyCorCoo?> <?php print $yyCorLI?> <a href="login.php"><?php print $yyClkHer?></a>.</p></td>
			  </tr>
<?php
	}else{ ?>
<br /><br />
<div class="heading"><?php print $yyDashbd?></div>
<table width="98%" class="admin-table-a">
  <thead>
	<tr>
	  <th scope="col"><?php print $yyNew?>&nbsp;&nbsp;&nbsp;</th>
	  <th scope="col"><?php print $yyAdmLnk?></th>
	  <th scope="col"><?php print $yyDesc?></th>
	  <th scope="col"><?php print $yyHlpFil?></th>
	</tr>
  </thead>
  <tr>
    <td class="new"><?php print $neworders?></td>
    <td onclick="document.location='adminorders.php'"><a href="adminorders.php"><?php print $yyVwOrd?></a></td>
    <td><?php print $yyOrStIn?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#orders" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
    <tr>
    <td class="new"><?php print $newaffiliate?></td>
	<td onclick="document.location='adminaffil.php'"><a href="adminaffil.php"><?php print $yyVwAff?></a></td>
    <td><?php print $yyAffDSt?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#affiliate" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
  <tr>
    <td class="new"><?php print $newratings?></td>
    <td onclick="document.location='adminratings.php'"><a href="adminratings.php"><?php print $yyVwRat?></a></td>
    <td><?php print $yyMPRRev?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#ratings" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
    <tr>
    <td class="new"><?php print $newaccounts?></td>
    <td onclick="document.location='adminclientlog.php'"><a href="adminclientlog.php"><?php print $yyCliLog?></a></td>
    <td><?php print $yyManCAc?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#clientlogin" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
  <tr>
    <td class="new"><?php print $newmaillist?></td>
    <td onclick="document.location='adminmailinglist.php'"><a href="adminmailinglist.php"><?php print $yyMaLiMa?></a></td>
    <td><?php print $yyVwSSeN?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#maillist" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
<?php	if(@$notifybackinstock && $newstocknotify>0){ ?>
  <tr>
    <td class="new"><?php print $newstocknotify?></td>
    <td onclick="document.location='adminprods.php?act=stknot'"><a href="adminprods.php?act=stknot"><?php print $yyStkNot?></a></td>
    <td><?php print $yyVwNoSk?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#notifystock" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
<?php	}
		if(@$_SESSION['loginid']==0){ ?>
  <tr>
    <td class="new"><?php print $newlogevents?></td>
    <td onclick="document.location='admin.php?act=events'"><a href="admin.php?act=events">View Activity Log</a></td>
    <td>View activity log events</td>
    <td><a href="<?php print helpbaseurl?>help.asp#actlog" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
<?php	} ?>
</table>
	
<div class="heading"><?php print $yyStoAdm?></div>
<table width="98%" class="admin-table-b">
  <thead>
	<tr>
	  <th scope="col"><?php print $yyAdmLnk?></th>
	  <th scope="col"><?php print $yyDesc?></th>
	  <th scope="col"><?php print $yyHlpFil?></th>
	</tr>
  </thead>
  <tr>
    <td><a href="adminmain.php"><?php print $yyEdAdm?></a></td>
    <td><?php print $yyDBGlob?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#admin" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
 <tr>
    <td><a href="adminlogin.php"><?php print $yyCngPw?></a></td>
    <td><?php print $yyDBLogA?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#uname" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
     <tr>
    <td><a href="adminpayprov.php"><?php print $yyEdPPro?></a></td>
    <td><?php print $yyDBConP?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#payprov" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
    <tr>
    <td><a href="adminordstatus.php"><?php print $yyEdOSta?></a></td>
    <td><?php print $yyDBConO?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#ordstat" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
      <tr>
    <td><a href="adminemailmsgs.php"><?php print $yyEmlAdm?></a></td>
    <td><?php print $yyDBConE?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#emailadmin" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
  <tr>
    <td><a href="admincontent.php"><?php print $yyContReg?></a></td>
    <td><?php print $yyContExp?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#contreg" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
  <tr>
    <td><a href="adminipblock.php"><?php print $yyIPBlock?></a></td>
    <td><?php print $yyDBBkIP?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#ipblock" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
</table>
	
<div class="heading"><?php print $yyPrdAdm?></div>
<table width="98%" class="admin-table-b">
  <thead>
	<tr>
	  <th scope="col"><?php print $yyAdmLnk?></th>
	  <th scope="col"><?php print $yyDesc?></th>
	  <th scope="col"><?php print $yyHlpFil?></th>
	</tr>
  </thead>
  <tr>
    <td><a href="adminprods.php"><?php print $yyEdPrd?></a></td>
    <td><?php print $yyDBMaPI?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#prods" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
    <tr>
    <td><a href="adminprodopts.php"><?php print $yyEdOpt?></a></td>
    <td><?php print $yyDBPrAt?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#prodopt" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
  <tr>
    <td><a href="admincats.php"><?php print $yyEdCat?></a></td>
    <td><?php print $yyDBCats?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#cats" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
  <tr>
    <td><a href="admindiscounts.php"><?php print $yyDisCou?></a></td>
    <td><?php print $yyDBSOFS?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#discounts" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
  <tr>
    <td><a href="adminpricebreak.php"><?php print $yyEdPrBk?></a></td>
    <td><?php print $yyDBBuPr?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#pricebreak" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
  <tr>
    <td><a href="admingiftcert.php"><?php print $yyGCMan?></a></td>
    <td><?php print $yyDBGifC?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#giftcert" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
    <tr>
    <td><a href="adminmanufacturer.php"><?php print $yyEdManu?></a></td>
    <td><?php print $yyDBManD?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#manuf" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
  <tr>
    <td><a href="adminsearchcriteria.php"><?php print $yyEdSeCr?></a></td>
    <td><?php print $yyCrSeCr?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#searcr" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
  <tr>
    <td><a href="admincsv.php"><?php print $yyCSVUpl?></a></td>
    <td><?php print $yyDBBUpI?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#csv" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
</table>	
	
<div class="heading"><?php print $yyShpAdm?></div>
<table width="98%" class="admin-table-b">
  <thead>
	<tr>
	  <th scope="col"><?php print $yyAdmLnk?></th>
	  <th scope="col"><?php print $yyDesc?></th>
	  <th scope="col"><?php print $yyHlpFil?></th>
	</tr>
  </thead>
  <tr>
    <td><a href="adminstate.php"><?php print $yyEdSta?></a></td>
    <td><?php print $yyDBStat?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#state" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
  <tr>
    <td><a href="admincountry.php"><?php print $yyEdCnt?></a></td>
    <td><?php print $yyDBCoun?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#country" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
  <tr>
    <td><a href="adminzones.php"><?php print $yyEdPzon?></a></td>
    <td><?php print $yyDBSZon?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#pzone" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
  <tr>
    <td><a href="adminuspsmeths.php"><?php print $yyShmReg?></a></td>
    <td><?php print $yyDBMSHO?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#shipmeth" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
  <tr>
     <td><a href="admindropship.php"><?php print $yyEdDrSp?></a></td>
    <td><?php print $yyDBDSDe?></td>
    <td><a href="<?php print helpbaseurl?>help.asp#droshp" target="ttshelp"><?php print $yyOnlHlp?></a></td>
  </tr>
</table>
<?php
	$sSQL = "SELECT modkey,modtitle,modauthor,modauthorlink,modversion,modectversion,modlink,moddate FROM installedmods ORDER BY moddate";
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result) > 0){
		print '<table width="98%" align="center">';
		print '<tr><td align="center" colspan="2">&nbsp;<br /><strong>---------------| Installed 3rd Party MODs |---------------<br />&nbsp;</strong></td></tr>';
		print '<tr><td align="center" colspan="2"><table border="0" cellspacing="0" cellpadding="0" width="100%">';
		print '<tr><td align="left"><strong>Title</strong></td><td align="left"><strong>Author</strong></td><td align="left"><strong>MOD Version</strong></td><td align="left"><strong>ECT Version</strong></td><td align="left"><strong>Admin Link</strong></td><td align="left"><strong>Install Date</strong></td></tr>';
		while($rs = mysql_fetch_array($result)){
			print '<tr><td align="left">' . $rs['modtitle'] . '</td>';
			print '<td align="left"><a href="http://' . $rs['modauthorlink'] . '" target="_blank">' . $rs['modauthor'] . '</a></td>';
			print '<td align="left">' . $rs['modversion'] . '</td>';
			print '<td align="left">' . $rs['modectversion'] . '</td>';
			print '<td align="left"><strong>' . (trim($rs['modlink']) != '' ? '<a href="' . $rs['modlink'] . '">Admin Page</a>' : '&nbsp;') . '</strong></td>';
			print '<td align="left">' . date($admindatestr, strtotime($rs['moddate'])) . '</td>';
		}
		print '</table><br />&nbsp;</td></tr></table>';
	}
	mysql_free_result($result);
	}
} ?>
<p>&nbsp;</p>