<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(trim(@$_POST['sessionid']) != '')
	$thesessionid = trim(@$_POST['sessionid']);
else
	$thesessionid = getsessionid();
$thesessionid = str_replace("'",'',$thesessionid);
$useEuro=false;
$mcgndtot=0;
$totquant=0;
$shipping=0;
$mcdiscounts=0;
if(@$_SESSION['xscountrytax'] != '') $xscountrytax=$_SESSION['xscountrytax']; else $xscountrytax=0;
$optPriceDiff=0;
$mcpdtxt='';
if(@$incfunctionsdefined==TRUE){
	$alreadygotadmin = getadminsettings();
	$pageurl=$storeurl;
}else{
	$sSQL = 'SELECT countryLCID,countryCurrency,adminStoreURL FROM admin INNER JOIN countries ON admin.adminCountry=countries.countryID WHERE adminID=1';
	$result = mysql_query($sSQL) or print(mysql_error());
	$rs = mysql_fetch_array($result);
	$adminLocale = $rs['countryLCID'];
	$countryCurrency = $rs['countryCurrency'];
	if(@$orcurrencyisosymbol != '') $countryCurrency=$orcurrencyisosymbol;
	$useEuro = ($countryCurrency=='EUR');
	$pageurl = $rs['adminStoreURL'];
	if((substr(strtolower($pageurl),0,7) != 'http://') && (substr(strtolower($pageurl),0,8) != 'https://'))
		$pageurl = 'http://' . $pageurl;
	if(substr($pageurl,-1) != '/') $pageurl .= '/';
	mysql_free_result($result);
}
if(@$forceloginonhttps) $pageurl='';
if(@$_POST['mode']=='checkout'){
	if(@$_POST['checktmplogin'] != ''){
		$sSQL = "SELECT tmploginname FROM tmplogin WHERE tmploginid='" . escape_string(@$_POST['sessionid']) . "' AND tmploginchk='" . escape_string(@$_POST['checktmplogin']) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result))
			$_SESSION['clientID']=$rs['tmploginname'];
	}else{
		$_SESSION['clientID']=NULL; unset($_SESSION['clientID']);
	}
}
$sSQL = 'SELECT cartID,cartProdID,cartProdName,cartProdPrice,cartQuantity FROM cart WHERE cartCompleted=0 AND ' . getsessionsql();
$result = mysql_query($sSQL) or print(mysql_error());
while($rs = mysql_fetch_assoc($result)){
	$optPriceDiff=0;
	$mcpdtxt .= '<tr><td class="mincart" bgcolor="#F0F0F0">' . $rs['cartQuantity'] . ' ' . $rs['cartProdName'] . '</td></tr>';
	$sSQL = 'SELECT SUM(coPriceDiff) AS sumDiff FROM cartoptions WHERE coCartID=' . $rs['cartID'];
	$result2 = mysql_query($sSQL) or print(mysql_error());
	$rs2 = mysql_fetch_assoc($result2);
	if(! is_null($rs2['sumDiff'])) $optPriceDiff=$rs2['sumDiff'];
	mysql_free_result($result2);
	$subtot = (($rs['cartProdPrice']+$optPriceDiff)*(int)$rs['cartQuantity']);
	$totquant+=(int)$rs['cartQuantity'];
	$mcgndtot += $subtot;
}
mysql_free_result($result);
?>
      <table class="mincart" width="130" bgcolor="#FFFFFF">
        <tr> 
          <td class="mincart" bgcolor="#F0F0F0" align="center"><img src="images/littlecart1.gif" align="top" width="16" height="15" alt="<?php print $xxMCSC?>" /> 
            &nbsp;<strong><a class="ectlink mincart" href="<?php print $pageurl?>cart.php"><?php print $xxMCSC?></a></strong></td>
        </tr>
<?php		if(@$_POST['mode']=='update'){ ?>
		<tr><td class="mincart" bgcolor="#F0F0F0" align="center"><?php print $xxMainWn?></td></tr>
<?php		}else{ ?>
        <tr><td class="mincart" bgcolor="#F0F0F0" align="center"><?php print $totquant . " " . $xxMCIIC ?></td></tr>
<?php			print $mcpdtxt;
				if($mcpdtxt != '' && @$_SESSION['discounts'] != ''){
					$mcdiscounts = (double)$_SESSION['discounts']; ?>
        <tr><td class="mincart" bgcolor="#F0F0F0" align="center"><span style="color:#FF0000"><?php print $xxDscnts . " " . FormatEuroCurrency($mcdiscounts)?></span></td></tr>
<?php			}
				if($mcpdtxt != '' && (string)@$_SESSION['xsshipping'] != ''){
					$shipping = (double)$_SESSION['xsshipping'];
					if($shipping==0) $showshipping='<span style="color:#FF0000;font-weight:bold">'.$xxFree.'</span>'; else $showshipping=FormatEuroCurrency($shipping); ?>
        <tr><td class="mincart" bgcolor="#F0F0F0" align="center"><?php print $xxMCShpE . " " . $showshipping?></td></tr>
<?php			}
				if($mcpdtxt == '') $xscountrytax=0; ?>
        <tr><td class="mincart" bgcolor="#F0F0F0" align="center"><?php print $xxTotal . ' ' . FormatEuroCurrency(($mcgndtot+$shipping+$xscountrytax)-$mcdiscounts)?></td></tr>
<?php		} ?>
        <tr><td class="mincart" bgcolor="#F0F0F0" align="center"><span style="font-family:Verdana">&raquo;</span> <a class="ectlink mincart" href="<?php print $pageurl?>cart.php"><strong><?php print $xxMCCO?></strong></a></td></tr>
      </table>