<?php
@include 'adminsession.php';
session_cache_limiter('none');
session_start();
ob_start();
header('Cache-Control: no-cache');
header('Pragma: no-cache');
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property
//of Internet Business Solutions SL. Any use, reproduction, disclosure or copying
//of any kind without the express and written permission of Internet Business 
//Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
include 'db_conn_open.php';
include 'inc/languagefile.php';
include 'includes.php';
include 'inc/incfunctions.php';
$cartisincluded=TRUE;
include './inc/uspsshipping.php';
include './inc/inccart.php';
$adminIntShipping=0; // So shipping doesn't get changed
$handlingeligableitem=FALSE;
$standalonetestmode=TRUE;
$debginfo='';
$thesessionid = @$_GET['sessionid'];
$destZip = @$_GET['destzip'];
$shipCountryCode = @$_GET['scc'];
$shipcountry = @$_GET['sc'];
$shipStateAbbrev = @$_GET['sta'];
$shipType=(int)@$_GET['shiptype'];
$numshipmethods=0;
$freeshipamnt=0;
$rgcpncode = trim(@$_SESSION['cpncode']);
$sSQL = "SELECT countryID,countryTax,countryCode,countryFreeShip,countryOrder FROM countries WHERE countryCode='" . escape_string($shipCountryCode) . "'";
$result = mysql_query($sSQL) or print(mysql_error());
if($rs = mysql_fetch_assoc($result)){
	$countryTaxRate = $rs['countryTax'];
	$shipCountryID = $rs['countryID'];
	$shipCountryCode = $rs['countryCode'];
	$freeshipavailtodestination = ($rs['countryFreeShip']==1);
	$shiphomecountry = ($rs['countryOrder']>=2);
}
mysql_free_result($result);
if($shiphomecountry){
	$sSQL = "SELECT stateFreeShip FROM states WHERE stateAbbrev='" . escape_string($shipStateAbbrev) . "'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)) $freeshipavailtodestination=($freeshipavailtodestination && ($rs['stateFreeShip']==1));
	mysql_free_result($result);
}
initshippingmethods();
$totalgoods=0;
$alldata='';
$success=TRUE;
$numshiprate=(int)@$_GET['numshiprate'];
$numshiprateingroup=0;
$sSQL = 'SELECT cartID,cartProdID,cartProdName,cartProdPrice,cartQuantity,pWeight,pShipping,pShipping2,pExemptions,pSection,pDims,pTax,cartCompleted,'.getlangid('pDescription',2).' FROM cart LEFT JOIN products ON cart.cartProdID=products.pID LEFT OUTER JOIN sections ON products.pSection=sections.sectionID WHERE cartCompleted=0 AND ' . getsessionsql();
$result = mysql_query($sSQL) or print(mysql_error());
if(($itemsincart=mysql_num_rows($result))>0){
	$index = 0;
	while($alldata=mysql_fetch_assoc($result)){
		$index++;
		if(is_null($alldata['pWeight'])) $alldata['pWeight']=0;
		if(($alldata['cartProdID']==$giftcertificateid || $alldata['cartProdID']==$donationid) && is_null($alldata['pExemptions'])) $alldata['pExemptions']=15;
		$sSQL = 'SELECT SUM(coPriceDiff) AS coPrDff FROM cartoptions WHERE coCartID='. $alldata['cartID'];
		$optresult = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_array($optresult)){
			$alldata['cartProdPrice'] += (double)$rs['coPrDff'];
		}
		mysql_free_result($optresult);
		$sSQL = 'SELECT SUM(coWeightDiff) AS coWghtDff FROM cartoptions WHERE coCartID='. $alldata['cartID'];
		$optresult = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_array($optresult)){
			$alldata['pWeight'] += (double)$rs['coWghtDff'];
		}
		mysql_free_result($optresult);
		$runTot=$alldata['cartProdPrice'] * (int)($alldata['cartQuantity']);
		$totalquantity += (int)($alldata['cartQuantity']);
		$totalgoods += $runTot;
		$thistopcat=0;
		if(trim(@$_SESSION['clientID'])!='') $alldata['pExemptions'] = ((int)$alldata['pExemptions'] | ((int)$_SESSION['clientActions'] & 7));
		if(($shipType==2 || $shipType==3 || $shipType==4 || $shipType==6 || $shipType==7) && (double)$alldata['pWeight']<=0.0)
			$alldata['pExemptions'] = ($alldata['pExemptions'] | 4);
		if(($alldata['pExemptions'] & 1)==1) $statetaxfree += $runTot;
		if(($alldata['pExemptions'] & 8)!=8){ $handlingeligableitem=TRUE; $handlingeligablegoods += $runTot; }
		if(@$perproducttaxrate==TRUE){
			if(is_null($alldata['pTax'])) $alldata['pTax'] = $countryTaxRate;
			if(($alldata['pExemptions'] & 2)!=2) $countryTax += (($alldata['pTax'] * $runTot) / 100.0);
		}else{
			if(($alldata['pExemptions'] & 2)==2) $countrytaxfree += $runTot;
		}
		if(($alldata['pExemptions'] & 4)==4) $shipfreegoods += $runTot;
		addproducttoshipping($alldata, $index);
	}
}else{
	$errormsg = "Error, couldn't find cart.";
	$success = FALSE;
}
mysql_free_result($result);
calculatediscounts($totalgoods, FALSE, $rgcpncode);
if($totaldiscounts > $totalgoods) $totaldiscounts = $totalgoods;
$shipsellogo = getshiplogo($shipType);
if($success && calculateshipping()){
	if(@$_GET['ratetype']=='estimator'){
		if(@$nohandlinginestimator){ $handling=0; $handlingchargepercent=0; }
		if((is_numeric(@$shipinsuranceamt) || (@$useuspsinsurancerates==TRUE && $shipType==3)) && abs(@$addshippinginsurance)==1) $shipping += (@$useuspsinsurancerates==TRUE && $shipType==3) ? getuspsinsurancerate((double)$totalgoods) : ($addshippinginsurance==1 ? (((double)$totalgoods*(double)$shipinsuranceamt)/100.0) : $shipinsuranceamt);
		if(@$taxShipping==1 && @$showtaxinclusive) $shipping += ((double)$shipping*(double)$countryTaxRate)/100.0;
		calculateshippingdiscounts(FALSE);
		if($handlingeligableitem==FALSE)
			$handling = 0;
		else{
			if(@$handlingchargepercent!=0){
				$temphandling = ((($totalgoods + $shipping + $handling) - ($totaldiscounts + $freeshipamnt)) * $handlingchargepercent / 100.0);
				if($handlingeligablegoods < $totalgoods && $totalgoods > 0) $temphandling = $temphandling * ($handlingeligablegoods / $totalgoods);
				$handling += $temphandling;
			}
			if(@$taxHandling==1 && @$showtaxinclusive) $handling += ((double)$handling*(double)$countryTaxRate)/100.0;
		}
		if(@$perproducttaxrate!=TRUE) $countryTax = round(((($totalgoods-$countrytaxfree)+(@$taxShipping==2 ? $shipping-$freeshipamnt : 0)+(@$taxHandling==2 ? $handling : 0))-$totaldiscounts)*$countryTaxRate/100.0, 2);
		$countryTax = round($countryTax,2);
		if(is_numeric(@$_GET['best'])) $currbest = (double)@$_GET['best']; else $currbest=100000000;
		if((($shipping+$handling)-$freeshipamnt) < $currbest){
			$_SESSION['xsshipping']=(($shipping+$handling)-$freeshipamnt);
			$_SESSION['xscountrytax']=$countryTax;
			$_SESSION['altrates']=$shipType;
		}
		print '&nbsp;';
		print 'SHIPSELPARAM=' . (($shipping+$handling)-$freeshipamnt);
		print 'SHIPSELPARAM=SUCCESS';
		print 'SHIPSELPARAM=' . $countryTax;
		print 'SHIPSELPARAM=' . $shipType;
	}else{
		$orderid=@$_GET['orderid'];
		if(is_numeric($orderid)){
			retrieveorderdetails($orderid, $thesessionid);
			$freeshippingincludeshandling=FALSE;
			insuranceandtaxaddedtoshipping();
			calculateshippingdiscounts(FALSE);
			calculatetaxandhandling();
			$cpnmessage = substr($cpnmessage,6);
			if($shipType>=2){
				if(@$shippingoptionsasradios!=TRUE) print '<select size="1" onchange="updateshiprate(this,(this.selectedIndex-1)+'.$numshiprate.')"><option value="">'.$xxPlsSel.' ('.$xxFromSE.': '.FormatEuroCurrency(($shipping+(@$combineshippinghandling?$handling:0))-$freeshipamnt).')</option>'; else print '<table border="0">';
				for($indexmso=0; $indexmso<$maxshipoptions; $indexmso++){
					$shipRow = $intShipping[$indexmso];
					if($shipRow[3]){
						calculatetaxandhandling();
						writeshippingoption(round($shipRow[2], 2), $shipRow[4], $shipRow[0], FALSE, $shipRow[1]);
					}
				}
				if($shippingoptionsasradios!=TRUE) print '</select>'; else print '</table>';
			}
			saveshippingoptions();
		}
		print 'SHIPSELPARAM='.str_replace('+','%20',urlencode($shipsellogo));
		print 'SHIPSELPARAM=REMOVEME';
		print 'SHIPSELPARAM=REMOVEME';
		print 'SHIPSELPARAM='.$numshiprate;
	}
}else{
	$success=FALSE;
	print '&nbsp;' . $errormsg;
	print 'SHIPSELPARAM='.str_replace('+','%20',urlencode($shipsellogo));
	print 'SHIPSELPARAM=ERROR';
}
?>