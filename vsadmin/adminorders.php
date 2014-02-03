<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protect under law as the intellectual property
//of Internet Business Solutions SL. Any use, reproduction, disclosure or copying
//of any kind without the express and written permission of Internet Business 
//Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
@ini_set('session.gc_maxlifetime', 15*60);
session_cache_limiter('none');
session_start();
ob_start();
include 'db_conn_open.php';
include 'inc/languageadmin.php';
include "inc/languagefile.php";
include 'includes.php';
include 'inc/incfunctions.php';
include 'inc/incloginfunctions.php';
$isprinter=(@$_GET['printer']=='true' || @$_GET['invoice']=='true');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<title>Admin Orders</title>
<link rel="stylesheet" type="text/css" href="adminstyle.css"/>
<meta http-equiv="Content-Type" content="text/html; charset=<?php print $adminencoding ?>"/>
</head>
<body <?php if($isprinter) print 'class="printbody"'?>>
<?php if(! $isprinter){ ?>

<!-- Header section -->
<div id="header1" align="right"><a class="topbar" href="http://www.ecommercetemplates.com/help.asp" target="_blank"><?php print $yyLLHelp?></a> &middot;
  <a href="http://www.ecommercetemplates.com/support/default.asp" target="_blank" class="topbar"><?php print $yyLLForu?></a> &middot; <a href="http://www.ecommercetemplates.com/support/search.asp" target="_blank" class="topbar"><?php print $yyLLForS?></a> &middot; <a href="http://www.ecommercetemplates.com/updaters.asp" target="_blank" class="topbar"><?php print $yyLLUpda?></a> &middot; <a class="topbar" href="logout.php"><?php print $yyLLLogO?></a>&nbsp;&nbsp;</div>
<div id="header"><a href="admin.php"><img src="adminimages/ecttop.jpg" alt="Ecommerce Templates" width="280" height="62" border="0"/></a></div>

<!-- Left menus -->
<br />
<h2 class="admin_menu"><?php print $yyVers?></h2>
<?php
if(@$_SESSION['loggedon']==@$storesessionvalue){
	updaterchecker();
}
?>

<img src="adminimages/leftbottom.jpg" alt="Administration" width="146" height="4"/><br/><br/>
<h2 class="admin_menu"><?php print $yyLMStAd?></h2>
<ul class="admin_menu_list">
	<li><a href="admin.php"><?php print strtolower($yyDashbd)?></a></li>
    <li><a href="adminmain.php"><?php print $yyLLMain?></a></li>
    <li><a href="adminorders.php"><?php print $yyLLOrds?></a></li>
    <li><a href="adminlogin.php"><?php print $yyLLPass?></a></li>
    <li><a href="adminpayprov.php"><?php print $yyLLPayP?></a></li>
    <li><a href="adminaffil.php"><?php print $yyLLAffl?></a></li>
    <li><a href="adminclientlog.php"><?php print $yyLLClLo?></a></li>
    <li><a href="adminordstatus.php"><?php print $yyLLOrSt?></a></li>
	<li><a href="adminemailmsgs.php"><?php print $yyLMEmla?></a></li>
    <li><a href="adminmailinglist.php"><?php print $yyLMMaLi?></a></li>
    <li><a href="admincontent.php"><?php print strtolower($yyContReg)?></a></li>
	<li><a href="adminipblock.php"><?php print strtolower($yyIPBlock)?></a></li>
</ul><img src="adminimages/leftbottom.jpg" alt="Administration" width="146" height="4"/><br/><br/>
<h2 class="admin_menu"><?php print $yyLMPrAd?></h2>
<ul class="admin_menu_list">
	<li><a href="adminprods.php"><?php print $yyLLProA?></a></li>
    <li><a href="adminprodopts.php"><?php print $yyLLProO?></a></li>
    <li><a href="admincats.php"><?php print $yyLLCats?></a></li>
    <li><a href="admindiscounts.php"><?php print $yyLLDisc?></a></li>
    <li><a href="adminpricebreak.php"><?php print $yyLLQuan?></a></li>
    <li><a href="adminratings.php"><?php print $xxLMRaRv?></a></li>
    <li><a href="admingiftcert.php"><?php print $yyLLGftC?></a></li>
	<li><a href="adminmanufacturer.php"><?php print strtolower($yyMFAdm)?></a></li>
	<li><a href="admincsv.php"><?php print strtolower($yyCSVUpl)?></a></li>
</ul><img src="adminimages/leftbottom.jpg" alt="Products" width="146" height="4"/><br/><br/>
<h2 class="admin_menu"><?php print $yyLMShAd?></h2>
<ul class="admin_menu_list">
	<li><a href="adminstate.php"><?php print $yyLLStat?></a></li>
    <li><a href="admincountry.php"><?php print $yyLLCoun?></a></li>
    <li><a href="adminzones.php"><?php print $yyLLZone?></a></li>
    <li><a href="adminuspsmeths.php"><?php print $yyLLShpM?></a></li>
    <li><a href="admindropship.php"><?php print $yyDrShpr?></a></li>
</ul><img src="adminimages/leftbottom.jpg" alt="Shipping" width="146" height="4"/><br/><br/>
<h2 class="admin_menu"><?php print $yyLmExtr?></h2>
<ul class="admin_menu_list">
	<li><a href="http://www.ecommercetemplates.com/affiliateinfo.asp" target="_blank"><?php print $yyLLAffP?></a></li>
    <li><a href="http://www.ecommercetemplates.com/addsite.asp" target="_blank"><?php print $yyLLSubm?></a></li>
    <li><a href="http://www.ecommercetemplates.com/support/default.asp" target="_blank"><?php print $yyLLForu?></a></li>
    <li><a href="<?php print @$storeurl?>" target="_blank"><?php print $yyLmVwSt?></a></li>
</ul><img src="adminimages/leftbottom.jpg" alt="Extras" width="146" height="4"/><br />
<?php } ?>
<!-- main content -->

<?php
	if(! $isprinter) print '<div id="main">'; else print '<div id="mainprint"' . (@$righttoleft==TRUE ? ' style="direction:rtl;"' : '') . '>';
	if(substr(@$_SESSION['loggedonpermissions'],1,1)!='X')
		print '<table width="100%" border="0" bgcolor=""><tr><td width="100%" colspan="4" align="center"><p>&nbsp;</p><p>&nbsp;</p><p><strong>'.$yyOpFai.'</strong></p><p>&nbsp;</p><p>'.$yyNoPer.' <br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br /><a href="admin.php"><strong>'.$yyAdmHom.'</strong></a>.</p><p>&nbsp;</p></td></tr></table>';
	else
		include 'inc/incorders.php';
	print "</div>"; ?>



</body>
</html>
