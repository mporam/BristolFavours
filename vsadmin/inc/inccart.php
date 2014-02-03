<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$cartisincluded!=TRUE){
	include './vsadmin/inc/uspsshipping.php';
	include './vsadmin/inc/incemail.php';
}
if(@$dateadjust=='') $dateadjust=0;
$errormsg = '';
$demomode = FALSE;
$maxshipoptions=40;
$success = $isInStock=TRUE;
$fromshipselector = $nodiscounts = $usehst = $multipleoptions = $shiphomecountry = $stockwarning = $cartEmpty = $handlingeligableitem = FALSE;
$willpickup_ = $insidedelivery_ = $commercialloc_ = $wantinsurance_ = $saturdaydelivery_ = $signaturerelease_ = FALSE;
$shipping = $iTotItems = $iWeight = $stateTaxRate = $countryTax = $stateTax = $outofstockcnt = 0;
$alldata = $shipMethod = $WSP = $OWSP = $googlecart = $appliedcouponname = $ordAVS = $ordCVV = $stateAbbrev = $international = $cpnmessage = $cpnerror = $shipselectoraction = $altrate = '';
$appliedcouponamount = $totalquantity = $statetaxfree = $countrytaxfree = $shipfreegoods = $totalgoods = $handlingeligablegoods = 0;
$freeshippingincludeshandling = $somethingToShip = $freeshippingapplied = $warncheckspamfolder = $homecountry = $gotcpncode = $freeshipmethodexists = FALSE;
$selectedshiptype = $numshipoptions = $freeshipamnt = $rowcounter = $totalshipitems = $stockrelitems = $thePQuantity = $thepweight = $grandtotal = $totaldiscounts = $giftcertsamount = $loyaltypointdiscount = 0;
$payerid = $rgcpncode = $token = $checkoutmode = '';
$ordShipName = $ordShipLastName = $ordShipAddress = $ordShipAddress2 = $ordShipCity = $ordShipState = $ordShipZip = $ordShipPhone = $ordShipCountry = $ordAffiliate = $ordAddInfo = $ordExtra1 = $ordExtra2 = $ordShipExtra1 = $ordShipExtra2 = $ordCheckoutExtra1 = $ordCheckoutExtra2 = '';
$outofstockarr=array();
$redstar='<span style="color:#FF1010">*</span>';
if(@$imgcheckoutbutton=='') $imgcheckoutbutton='images/checkout.gif';
if(@$imgcheckoutbutton2=='') $imgcheckoutbutton2=$imgcheckoutbutton;
if(@$imgcheckoutbutton3=='') $imgcheckoutbutton3=$imgcheckoutbutton;
$alreadygotadmin = getadminsettings();
if(@$cartisincluded!=TRUE){
	if(@$_SERVER['CONTENT_LENGTH']!='' && $_SERVER['CONTENT_LENGTH'] > 100000) exit;
	$cartisincluded=FALSE;
	$rgcpncode = trim(str_replace(array("'",'"'),'',strip_tags(@$_REQUEST['cpncode'])));
	if(strpos(strtolower(@$_SESSION['cpncode']), strtolower($rgcpncode) . ' ')!==FALSE || strpos(strtolower(@$_SESSION['giftcerts']), strtolower($rgcpncode) . ' ')!==FALSE) $rgcpncode='';
	if($rgcpncode!=''){ // Check for gift certs
		$sSQL = "SELECT gcID FROM giftcertificate WHERE gcRemaining>0 AND gcAuthorized<>0 AND gcID='" . escape_string($rgcpncode) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_assoc($result)){
			if(strpos(@$_SESSION['giftcerts'], $rs['gcID'] . ' ')===FALSE) @$_SESSION['giftcerts'] .= $rs['gcID'] . ' ';
			$rgcpncode='';
		}
		mysql_free_result($result);
	}
	if($rgcpncode!=''){
		if(trim(@$_SESSION['cpncode'])!='') $cpnerror = $xxCanApp . ' ' . $rgcpncode . '. ' . $xxOnOnCp . '<br />'; else @$_SESSION['cpncode'] = trim($rgcpncode) . ' ';
	}
	$rgcpncode = trim(@$_SESSION['cpncode']);
	if(@$_POST['payerid']!='') $payerid = $_POST['payerid']; else $payerid = '';
	$token = trim(@$_REQUEST['token']);
	if(trim(@$_POST['sessionid'])!='') $thesessionid=str_replace(array("'",'"'),'',strip_tags($_POST['sessionid'])); else $thesessionid=getsessionid();
	$theid = escape_string(@$_POST['id']);
	$checkoutmode = trim(@$_POST['mode']);
	$commercialloc_ = (@$_POST['commercialloc']=='Y');
	$wantinsurance_ = (@$_POST['wantinsurance']=='Y');
	$saturdaydelivery_ = (@$_POST['saturdaydelivery']=='Y');
	$signaturerelease_ = (@$_POST['signaturerelease']=='Y');
	$insidedelivery_ = (@$_POST['insidedelivery']=='Y');
	$willpickup_ = (@$_POST['willpickup']=='Y');
	$ordPayProvider = trim(@$_POST['payprovider']);
	if(! is_numeric($ordPayProvider)) $ordPayProvider='';
	if(@$_GET['token']!='' && $ordPayProvider=='') $ordPayProvider=19;
	$shipselectoraction	= trim(@$_POST['shipselectoraction']);
	if(@$_POST['shipselectoraction']=='selector') $fromshipselector=TRUE;
	if(@$_POST['noredeempoints']=='1') $_SESSION['noredeempoints']=TRUE;
	if(is_numeric(@$_POST['altrates'])) $altrate=(int)@$_POST['altrates'];
}
$paypalexpress=FALSE;
get_wholesaleprice_sql();
$thefrompage=strip_tags(trim(@$_GET['rp'])!='' ? trim(@$_GET['rp']) : @$_SERVER['HTTP_REFERER']);
if(trim(@$_GET['rp'])==''){
	$pu=parse_url($thefrompage);
	if(@strpos(strtolower($storeurl), str_replace('www.','',@$pu['host']))===FALSE) $thefrompage='';
}
if(strpos(strtolower($thefrompage),'javascript:')!==FALSE||strpos(strtolower($thefrompage),'cart.php')!==FALSE||strpos(strtolower($thefrompage),'thanks.php')!==FALSE) $thefrompage='';
if(@$_SESSION['clientID']!='' && @$_SESSION['clientLoginLevel']!='') $minloglevel=$_SESSION['clientLoginLevel']; else $minloglevel=0;
$countryTax=0; // At present both countryTaxRate and countryTax are set in incfunctions
$origShipType=$shipType;
$orighandling = $handling;
$orighandlingpercent = $handlingchargepercent;
function getshiplogo($stype){
	global $shippinglogo;
	if($stype==3)
		$gsl = '<img src="images/usps_logo.gif" alt="USPS Logo" />';
	elseif($stype==4)
		$gsl = '<img src="images/upslogo.png" alt="UPS Logo" />';
	elseif($stype==6)
		$gsl = '<img src="images/canadapost.gif" alt="CanadaPost Logo" />';
	elseif($stype==7 || $stype==8)
		$gsl = '<img src="images/fedexlogo.png" alt="FedEx Logo" />';
	elseif(@$shippinglogo!='')
		$gsl = '<img src="'.$shippinglogo.'" alt="Logo" />';
	else
		$gsl = '<img src="images/clearpixel.gif" width="44" alt="" />';
	return(($stype!=6?'&nbsp;&nbsp;&nbsp;':'') . $gsl . ($stype!=6?'&nbsp;&nbsp;&nbsp;':''));
}
function writealtshipline($altmethod,$altid,$pretext,$defpretext,$rhs){
	global $shippingoptionsasradios,$shipType,$origShipType;
	if(@$shippingoptionsasradios==TRUE){
		if($altmethod!='' || $origShipType==$altid) print ($shipType==$altid?'<strong>':'').($rhs?'':($shipType==$altid?$defpretext:$pretext).$altmethod).'<input type="radio" style="vertical-align:bottom" value="'.$altid.'"'.($shipType==$altid?' checked="checked"':'').' onclick="selaltrate('.$altid.')" />'.($rhs?($shipType==$altid?$defpretext:$pretext).$altmethod:'').($shipType==$altid?'</strong>':'').'<br />';
	}else{
		if($altmethod!='' || $origShipType==$altid) print '<option value="'.$altid.'"'.($shipType==$altid?' selected="selected"':'').'>'.($shipType==$altid?$defpretext:$pretext).$altmethod.'</option>';
	}
}
function retrieveorderdetails($ordid, $sessid){
	global $ordName,$ordLastName,$ordAddress,$ordAddress2,$ordCity,$ordState,$ordZip,$ordCountry,$ordEmail,$ordPhone,$ordShipName,$ordShipLastName,$ordShipAddress,$ordShipAddress2,$ordShipCity,$ordShipState,$ordShipZip,$ordShipCountry,$ordShipPhone,$ordPayProvider,$ordComLoc,$ordExtra1,$ordExtra2,$ordShipExtra1,$ordShipExtra2,$ordCheckoutExtra1,$ordCheckoutExtra2,$ordAffiliate,$ordAVS,$ordCVV,$ordAddInfo;
	global $insidedelivery_,$commercialloc_,$wantinsurance_,$saturdaydelivery_,$signaturerelease_;
	$result = mysql_query("SELECT ordName,ordLastName,ordAddress,ordAddress2,ordCity,ordState,ordZip,ordCountry,ordEmail,ordPhone,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipZip,ordShipCountry,ordShipPhone,ordPayProvider,ordComLoc,ordExtra1,ordExtra2,ordShipExtra1,ordShipExtra2,ordCheckoutExtra1,ordCheckoutExtra2,ordAffiliate,ordAVS,ordCVV,ordAddInfo FROM orders WHERE ordID='".escape_string($ordid)."' AND ordSessionID='".escape_string($sessid)."'");
	if($rs = mysql_fetch_assoc($result)){
		$ordName = $rs['ordName'];
		$ordLastName = $rs['ordLastName'];
		$ordAddress = $rs['ordAddress'];
		$ordAddress2 = $rs['ordAddress2'];
		$ordCity = $rs['ordCity'];
		$ordState = $rs['ordState'];
		$ordZip = $rs['ordZip'];
		$ordCountry = $rs['ordCountry'];
		$ordEmail = $rs['ordEmail'];
		$ordPhone = $rs['ordPhone'];
		$ordShipName = $rs['ordShipName'];
		$ordShipLastName = $rs['ordShipLastName'];
		$ordShipAddress = $rs['ordShipAddress'];
		$ordShipAddress2 = $rs['ordShipAddress2'];
		$ordShipCity = $rs['ordShipCity'];
		$ordShipState = $rs['ordShipState'];
		$ordShipZip = $rs['ordShipZip'];
		$ordShipCountry = $rs['ordShipCountry'];
		$ordShipPhone = $rs['ordShipPhone'];
		$ordPayProvider = $rs['ordPayProvider'];
		$ordComLoc = $rs['ordComLoc'];
		$ordExtra1 = $rs['ordExtra1'];
		$ordExtra2 = $rs['ordExtra2'];
		$ordShipExtra1 = $rs['ordShipExtra1'];
		$ordShipExtra2 = $rs['ordShipExtra2'];
		$ordCheckoutExtra1 = $rs['ordCheckoutExtra1'];
		$ordCheckoutExtra2 = $rs['ordCheckoutExtra2'];
		$ordAffiliate = $rs['ordAffiliate'];
		$ordAVS = $rs['ordAVS'];
		$ordCVV = $rs['ordCVV'];
		$ordAddInfo = $rs['ordAddInfo'];
		if(($ordComLoc & 1)==1) $commercialloc_=TRUE;
		if(($ordComLoc & 2)==2 || abs(@$addshippinginsurance)==1) $wantinsurance_=TRUE;
		if(($ordComLoc & 4)==4) $saturdaydelivery_=TRUE;
		if(($ordComLoc & 8)==8) $signaturerelease_=TRUE;
		if(($ordComLoc & 16)==16) $insidedelivery_=TRUE;
	}
	mysql_free_result($result);
}
function getpayprovhandling(){
	global $ordPayProvider,$handling,$orighandling,$handlingchargepercent,$orighandlingpercent;
	if($ordPayProvider!='' && is_numeric($ordPayProvider)){
		$result = mysql_query("SELECT ppHandlingCharge,ppHandlingPercent FROM payprovider WHERE payProvID=".$ordPayProvider) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)){
			$handling += $rs['ppHandlingCharge'];
			$handlingchargepercent += $rs['ppHandlingPercent'];
		}
		mysql_free_result($result);
	}
	$orighandling = $handling;
	$orighandlingpercent = $handlingchargepercent;
}
if(@$_SESSION['couponapply']!=''){
	mysql_query('UPDATE coupons SET cpnNumAvail=cpnNumAvail+1 WHERE cpnID IN (0' . $_SESSION['couponapply'] . ')') or print(mysql_error());
	$_SESSION['couponapply']='';
}
function getcctypefromnum($thecardnum){
	if(substr($thecardnum, 0, 1)=='5')
		return('MasterCard');
	elseif(substr($thecardnum, 0, 1)=='6')
		return('Discover');
	elseif(substr($thecardnum, 0, 1)=='3')
		return('Amex');
	return('Visa');
}
function show_states($tstate){
	global $xxOutState,$allstates,$numallstates,$usestateabbrev;
	$foundmatch=FALSE;
	if($xxOutState!='') print '<option value="">' . $xxOutState . '</option>';
	for($index=0;$index<$numallstates;$index++){
		print '<option value="' . htmlspecials(@$usestateabbrev==TRUE?$allstates[$index]['stateAbbrev']:$allstates[$index]['stateName']) . '"';
		if($tstate==$allstates[$index]['stateName'] || $tstate==$allstates[$index]['stateAbbrev']){
			print ' selected="selected"';
			$foundmatch=TRUE;
		}
		print '>' . $allstates[$index]['stateName'] . "</option>\n";
	}
	return $foundmatch;
}
function show_countries($tcountry){
	global $numhomecountries,$nonhomecountries,$allcountries,$numallcountries;
	for($index=0;$index<$numallcountries;$index++){
		print '<option value="' . htmlspecials($allcountries[$index]['countryName']) . '"';
		if($tcountry==$allcountries[$index]['countryName']) print ' selected="selected"';
		print '>' . $allcountries[$index][2] . "</option>\n";
	}
}
function checkuserblock($thepayprov){
	global $blockmultipurchase,$multipurchaseblockmessage,$shipselectoraction;
	if(@$multipurchaseblockmessage=='') $multipurchaseblockmessage="I'm sorry. We are experiencing temporary difficulties at the moment. Please try your purchase again later.";
	$multipurchaseblocked=FALSE;
	if($thepayprov!='7' && $thepayprov!='13'){
		$theip = @$_SERVER['REMOTE_ADDR'];
		if($theip=='') $theip = 'none';
		if(@$blockmultipurchase!='' && $shipselectoraction==''){
			mysql_query("DELETE FROM multibuyblock WHERE lastaccess<'" . date('Y-m-d H:i:s', time()-(60*60*24)) . "'") or print(mysql_error());
			$sSQL = "SELECT ssdenyid,sstimesaccess FROM multibuyblock WHERE ssdenyip = '" . escape_string($theip) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				mysql_query("UPDATE multibuyblock SET sstimesaccess=sstimesaccess+1,lastaccess='" . date('Y-m-d H:i:s', time()) . "' WHERE ssdenyid=" . $rs['ssdenyid']) or print(mysql_error());
				if($rs['sstimesaccess'] >= $blockmultipurchase) $multipurchaseblocked=TRUE;
			}else{
				mysql_query("INSERT INTO multibuyblock (ssdenyip,lastaccess) VALUES ('" . escape_string($theip) . "','" . date('Y-m-d H:i:s', time()) . "')") or print(mysql_error());
			}
			mysql_free_result($result);
		}
		if($theip=='none' || ip2long($theip)==FALSE)
			$sSQL = 'SELECT dcid FROM ipblocking LIMIT 0,1';
		else
			$sSQL = 'SELECT dcid FROM ipblocking WHERE (dcip1=' . ip2long($theip) . ' AND dcip2=0) OR (dcip1 <= ' . ip2long($theip) . ' AND ' . ip2long($theip) . ' <= dcip2 AND dcip2<>0)';
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result) > 0)
			$multipurchaseblocked = TRUE;
	}
	return($multipurchaseblocked);
}
function checkpricebreaks($cpbpid,$origprice){
	global $WSP;
	$newprice='';
	$sSQL = 'SELECT SUM(cartQuantity) AS totquant FROM cart WHERE cartCompleted=0 AND ' . getsessionsql() . " AND cartProdID='".escape_string($cpbpid)."'";
	$result = mysql_query($sSQL) or print(mysql_error());
	$rs=mysql_fetch_assoc($result);
	if(is_null($rs['totquant'])) $thetotquant=0; else $thetotquant = $rs['totquant'];
	$sSQL='SELECT '.$WSP.'pPrice FROM pricebreaks WHERE '.$thetotquant.">=pbQuantity AND pbProdID='".escape_string($cpbpid)."' ORDER BY pbQuantity DESC";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs=mysql_fetch_assoc($result)) $thepricebreak = $rs['pPrice']; else $thepricebreak = $origprice;
	mysql_free_result($result);
	$sSQL = 'UPDATE cart SET cartProdPrice='.($thepricebreak).' WHERE cartCompleted=0 AND ' . getsessionsql() . " AND cartProdID='".escape_string($cpbpid)."'";
	mysql_query($sSQL) or print(mysql_error());
	$sSQL = 'SELECT cartID FROM cart WHERE cartCompleted=0 AND ' . getsessionsql() . " AND cartProdID='".escape_string($cpbpid)."'";
	$result2 = mysql_query($sSQL) or print(mysql_error());
	while($rs2=mysql_fetch_assoc($result2)){
		$sSQL = 'SELECT coCartOption FROM cartoptions WHERE coMultiply<>0 AND coCartID=' . $rs2['cartID'];
		$result3 = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result3)>0){
			$totaloptmultiplier=1;
			while($rs3=mysql_fetch_assoc($result3)){
				if(is_numeric($rs3['coCartOption'])) $totaloptmultiplier*=(double)$rs3['coCartOption']; else $totaloptmultiplier=0;
			}
			$sSQL = "UPDATE cart SET cartProdPrice=".($thepricebreak*$totaloptmultiplier).' WHERE cartID=' . $rs2['cartID'];
			mysql_query($sSQL) or print(mysql_error());
		}
		mysql_free_result($result3);
	}
	mysql_free_result($result2);
	return($thepricebreak);
}
function multShipWeight($theweight, $themul){
	return(($theweight*$themul)/100.0);
}
function subtaxesfordiscounts($theExemptions, $discAmount){
	global $statetaxfree,$countrytaxfree,$shipfreegoods;
	if(($theExemptions & 1)==1) $statetaxfree -= $discAmount;
	if(($theExemptions & 2)==2) $countrytaxfree -= $discAmount;
	if(($theExemptions & 4)==4) $shipfreegoods -= $discAmount;
}
function addadiscount($resset, $groupdiscount, $dscamount, $subcpns, $cdcpncode, $statetaxhandback, $countrytaxhandback, $theexemptions, $thetax){
	global $totaldiscounts,$cpnmessage,$statetaxfree,$countrytaxfree,$gotcpncode,$perproducttaxrate,$countryTax,$appliedcouponname,$appliedcouponamount,$minloglevel;
	$totaldiscounts += $dscamount;
	if($groupdiscount){
		$statetaxfree -= ($dscamount * $statetaxhandback);
		$countrytaxfree -= ($dscamount * $countrytaxhandback);
	}else{
		subtaxesfordiscounts($theexemptions, $dscamount);
		if(@$perproducttaxrate) $countryTax -= (($dscamount * $thetax) / 100.0);
	}
	if(stristr($cpnmessage,'<br />' . $resset[getlangid('cpnName',1024)] . '<br />')==FALSE) $cpnmessage .= $resset[getlangid('cpnName',1024)] . '<br />';
	if($subcpns){
		$theres = mysql_query('SELECT cpnID FROM coupons WHERE cpnNumAvail>0 AND cpnNumAvail<30000000 AND cpnID=' . $resset['cpnID'] . ' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))') or print(mysql_error());
		if($theresset = mysql_fetch_assoc($theres)) @$_SESSION['couponapply'] .= ',' . $resset['cpnID'];
		mysql_query('UPDATE coupons SET cpnNumAvail=cpnNumAvail-1 WHERE cpnNumAvail>0 AND cpnNumAvail<30000000 AND cpnID=' . $resset['cpnID'] . ' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))') or print(mysql_error());
	}
	if($cdcpncode!='' && strtolower(trim($resset['cpnNumber']))==strtolower($cdcpncode)){ $gotcpncode=TRUE; $appliedcouponname = $resset['cpnName']; $appliedcouponamount = $dscamount; }
}
function timesapply($taquant,$tathresh,$tamaxquant,$tamaxthresh,$taquantrepeat,$tathreshrepeat){
	if($tamaxquant==0) $taquantrepeat=0;
	if($tamaxthresh==0) $tathreshrepeat=0;
	if($taquantrepeat==0 && $tathreshrepeat==0)
		$tatimesapply = 1.0;
	elseif($tamaxquant==0)
		$tatimesapply = (int)(($tathresh - $tamaxthresh) / $tathreshrepeat)+1;
	elseif($tamaxthresh==0)
		$tatimesapply = (int)(($taquant - $tamaxquant) / $taquantrepeat)+1;
	else{
		$ta1 = (int)(($taquant - $tamaxquant) / $taquantrepeat)+1;
		$ta2 = (int)(($tathresh - $tamaxthresh) / $tathreshrepeat)+1;
		if($ta2 < $ta1) $tatimesapply = $ta2; else $tatimesapply = $ta1;
	}
	return($tatimesapply);
}
function jschk($str){
	return(str_replace(array('\\',"'",'<','>'),array('\\\\',"\\'",'\\<','\\>'), $str));
}
function calculatediscounts($cdgndtot, $subcpns, $cdcpncode){
	global $totaldiscounts,$cpnmessage,$statetaxfree,$countrytaxfree,$nodiscounts,$WSP,$rgcpncode,$gotcpncode,$thesessionid,$countryTaxRate,$countryTax,$giftcertificateid,$donationid,$dateadjust,$minloglevel;
	$totaldiscounts = 0;
	$cpnmessage = '<br />';
	$cdtotquant=0;
	if($cdgndtot==0){
		$statetaxhandback = 0.0;
		$countrytaxhandback = 0.0;
	}else{
		$statetaxhandback = 1.0 - (($cdgndtot - $statetaxfree) / $cdgndtot);
		$countrytaxhandback = 1.0 - (($cdgndtot - $countrytaxfree) / $cdgndtot);
	}
	if(! $nodiscounts){
		$sSQL = 'SELECT cartProdID,SUM(cartProdPrice*cartQuantity) AS thePrice,SUM(cartQuantity) AS sumQuant,pSection,COUNT(cartProdID),pExemptions,pTax FROM products INNER JOIN cart ON cart.cartProdID=products.pID WHERE cartProdID<>\'giftcertificate\' AND cartCompleted=0 AND ' . getsessionsql() . ' GROUP BY cartProdID,pSection,pExemptions,pTax';
		$cdresult = mysql_query($sSQL) or print(mysql_error());
		$cdadindex=0;
		while($cdrs = mysql_fetch_assoc($cdresult)){
			$cdalldata[$cdadindex++]=$cdrs;
		}
		for($index=0; $index<$cdadindex; $index++){
			$cdrs = $cdalldata[$index];
			if(($cdrs['cartProdID']==$giftcertificateid || $cdrs['cartProdID']==$donationid) && is_null($cdrs['pExemptions'])) $cdrs['pExemptions']=15;
			$sSQL = 'SELECT SUM(coPriceDiff*cartQuantity) AS totOpts FROM cart INNER JOIN cartoptions ON cart.cartID=cartoptions.coCartID WHERE cartCompleted=0 AND ' . getsessionsql() . " AND cartProdID='" . $cdrs['cartProdID'] . "'";
			$cdresult2 = mysql_query($sSQL) or print(mysql_error());
			$cdrs2 = mysql_fetch_assoc($cdresult2);
			if(! is_null($cdrs2['totOpts'])) $cdrs['thePrice'] += $cdrs2['totOpts'];
			$cdtotquant += $cdrs['sumQuant'];
			$topcpnids = $cdrs['pSection'];
			$thetopts = $cdrs['pSection'];
			if(is_null($cdrs['pTax'])) $cdrs['pTax'] = $countryTaxRate;
			for($cpnindex=0; $cpnindex<= 10; $cpnindex++){
				if($thetopts==0)
					break;
				else{
					$sSQL = 'SELECT topSection FROM sections WHERE sectionID=' . $thetopts;
					$result2 = mysql_query($sSQL) or print(mysql_error());
					if($rs2 = mysql_fetch_assoc($result2)){
						$thetopts = $rs2['topSection'];
						$topcpnids .= ',' . $thetopts;
					}else
						break;
				}
			}
			$sSQL = 'SELECT DISTINCT cpnID,cpnDiscount,cpnType,cpnNumber,'.getlangid('cpnName',1024).",cpnThreshold,cpnQuantity,cpnSitewide,cpnThresholdRepeat,cpnQuantityRepeat FROM coupons LEFT OUTER JOIN cpnassign ON coupons.cpnID=cpnassign.cpaCpnID WHERE cpnNumAvail>0 AND cpnEndDate>='" . date('Y-m-d', time()+($dateadjust*60*60)) ."' AND (cpnIsCoupon=0";
			if($cdcpncode!='') $sSQL .= " OR (cpnIsCoupon=1 AND cpnNumber='" . $cdcpncode . "')";
			$sSQL .= ') AND cpnThreshold<=' . $cdrs['thePrice'] . ' AND (cpnThresholdMax>' . $cdrs['thePrice'] . ' OR cpnThresholdMax=0) AND cpnQuantity<=' . $cdrs['sumQuant'] . ' AND (cpnQuantityMax>' . $cdrs['sumQuant'] . ' OR cpnQuantityMax=0) AND (cpnSitewide=0 OR cpnSitewide=2) AND ' .
				"(cpnSitewide=2 OR (cpaType=2 AND cpaAssignment='" . $cdrs['cartProdID'] . "') " .
				"OR (cpaType=1 AND cpaAssignment IN ('" . str_replace(',',"','",$topcpnids) . "')))" .
				' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
			$result2 = mysql_query($sSQL) or print(mysql_error());
			while($rs2 = mysql_fetch_assoc($result2)){
				if($rs2['cpnType']==1){ // Flat Rate Discount
					$thedisc = (double)$rs2['cpnDiscount'] * timesapply($cdrs['sumQuant'], $cdrs['thePrice'], $rs2['cpnQuantity'], $rs2['cpnThreshold'], $rs2['cpnQuantityRepeat'], $rs2['cpnThresholdRepeat']);
					if($cdrs['thePrice'] < $thedisc) $thedisc = $cdrs['thePrice'];
					addadiscount($rs2, FALSE, $thedisc, $subcpns, $cdcpncode, $statetaxhandback, $countrytaxhandback, $cdrs['pExemptions'], $cdrs['pTax']);
				}elseif($rs2['cpnType']==2){ // Percentage Discount
					addadiscount($rs2, FALSE, (((double)$rs2['cpnDiscount'] * (double)$cdrs['thePrice']) / 100.0), $subcpns, $cdcpncode, $statetaxhandback, $countrytaxhandback, $cdrs['pExemptions'], $cdrs['pTax']);
				}
			}
		}
		$sSQL = 'SELECT DISTINCT cpnID,cpnDiscount,cpnType,cpnNumber,'.getlangid('cpnName',1024).",cpnSitewide,cpnThreshold,cpnThresholdMax,cpnQuantity,cpnQuantityMax,cpnThresholdRepeat,cpnQuantityRepeat FROM coupons WHERE cpnNumAvail>0 AND cpnEndDate>='" . date('Y-m-d', time()+($dateadjust*60*60)) ."' AND (cpnIsCoupon=0";
		if($cdcpncode!='') $sSQL .= " OR (cpnIsCoupon=1 AND cpnNumber='" . $cdcpncode . "')";
		$sSQL .= ') AND cpnThreshold<=' . $cdgndtot . ' AND cpnQuantity<=' . $cdtotquant . ' AND (cpnSitewide=1 OR cpnSitewide=3) AND (cpnType=1 OR cpnType=2)' .
			' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
		$result2 = mysql_query($sSQL) or print(mysql_error());
		while($rs2 = mysql_fetch_assoc($result2)){
			$totquant = 0;
			$totprice = 0;
			if($rs2['cpnSitewide']==3){
				$sSQL = 'SELECT cpaAssignment FROM cpnassign WHERE cpaType=1 AND cpacpnID=' . $rs2['cpnID'];
				$result3 = mysql_query($sSQL) or print(mysql_error());
				$secids = '';
				$addcomma = '';
				while($rs3 = mysql_fetch_assoc($result3)){
					$secids .= $addcomma . $rs3['cpaAssignment'];
					$addcomma = ',';
				}
				if($secids!='') $sectionidsql = ' AND products.pSection IN (' . getsectionids($secids, FALSE) . ')'; else $sectionidsql='notassigned';
			}else // cpnSitewide==1
				$sectionidsql = '';
			if($sectionidsql!='notassigned'){
				$sSQL = 'SELECT SUM(cartProdPrice*cartQuantity) AS totPrice,SUM(cartQuantity) AS totQuant FROM products INNER JOIN cart ON cart.cartProdID=products.pID WHERE cartProdID<>\'giftcertificate\' AND cartCompleted=0 AND ' . getsessionsql() . $sectionidsql;
				$result3 = mysql_query($sSQL) or print(mysql_error());
				$rs3 = mysql_fetch_assoc($result3);
				if(is_null($rs3['totPrice'])) $totprice = 0; else $totprice = $rs3['totPrice'];
				if(is_null($rs3['totQuant'])) $totquant=0; else $totquant = $rs3['totQuant'];
				$sSQL = 'SELECT SUM(coPriceDiff*cartQuantity) AS optPrDiff FROM products INNER JOIN cart ON cart.cartProdID=products.pID LEFT OUTER JOIN cartoptions ON cart.cartID=cartoptions.coCartID WHERE cartCompleted=0 AND ' . getsessionsql() . $sectionidsql;
				$result3 = mysql_query($sSQL) or print(mysql_error());
				$rs3 = mysql_fetch_assoc($result3);
				if(! is_null($rs3['optPrDiff'])) $totprice = $totprice+$rs3['optPrDiff'];
			}
			if($totquant > 0 && $rs2['cpnThreshold'] <= $totprice && ($rs2['cpnThresholdMax'] > $totprice || $rs2['cpnThresholdMax']==0) && $rs2['cpnQuantity'] <= $totquant && ($rs2['cpnQuantityMax'] > $totquant || $rs2['cpnQuantityMax']==0)){
				if($rs2['cpnType']==1){ // Flat Rate Discount
					$thedisc = (double)$rs2['cpnDiscount'] * timesapply($totquant, $totprice, $rs2['cpnQuantity'], $rs2['cpnThreshold'], $rs2['cpnQuantityRepeat'], $rs2['cpnThresholdRepeat']);
					if($totprice < $thedisc) $thedisc = $totprice;
				}elseif($rs2['cpnType']==2){ // Percentage Discount
					$thedisc = ((double)$rs2['cpnDiscount'] * (double)$totprice) / 100.0;
				}
				addadiscount($rs2, TRUE, $thedisc, $subcpns, $cdcpncode, $statetaxhandback, $countrytaxhandback, 3, 0);
				if(@$perproducttaxrate && $cdgndtot > 0){
					for($index=0; $index<$cdadindex; $index++){
						$cdrs = $cdalldata[$index];
						$applicdisc =0;
						if($rs2['cpnType']==1 && $cdrs['sumQuant']>0) // Flat Rate Discount
							$applicdisc = $thedisc / ($cdtotquant / $cdrs['sumQuant']);
						elseif($rs2['cpnType']==2 && $cdrs['thePrice']>0) // Percentage Discount
							$applicdisc = $thedisc / ($cdgndtot / $cdrs['thePrice']);
						if(($cdrs['pExemptions'] & 2)!=2) $countryTax -= (($applicdisc * $cdrs['pTax']) / 100.0);
					}
				}
			}
		}
	}
	if($statetaxfree < 0) $statetaxfree = 0;
	if($countrytaxfree < 0) $countrytaxfree = 0;
	$totaldiscounts = round($totaldiscounts, 2);
}
function calculateshippingdiscounts($subcpns){
	global $freeshippingapplied,$nodiscounts,$totalgoods,$totalquantity,$rgcpncode,$freeshipavailtodestination,$freeshipmethodexists,$cpnmessage,$shipping,$freeshipamnt,$gotcpncode,$handling,$handlingchargepercent,$freeshippingincludeshandling,$dateadjust;
	global $somethingToShip,$shipType,$maxshipoptions,$intShipping,$selectedshiptype,$iTotItems,$numuspsmeths,$uspsmethods,$shipMethod,$fromshipselector,$minloglevel;
	$freeshipamnt = 0;
	if(! $nodiscounts){
		$sSQL = 'SELECT cpnID,'.getlangid('cpnName',1024).',cpnNumber,cpnDiscount,cpnThreshold,cpnCntry,cpnHandling FROM coupons WHERE cpnType=0 AND cpnSitewide=1 AND cpnNumAvail>0 AND cpnThreshold<='.$totalgoods.' AND (cpnThresholdMax>'.$totalgoods.' OR cpnThresholdMax=0) AND cpnQuantity<='.$totalquantity.' AND (cpnQuantityMax>'.$totalquantity." OR cpnQuantityMax=0) AND cpnEndDate>='" . date('Y-m-d', time()+($dateadjust*60*60)) ."' AND (cpnIsCoupon=0 OR (cpnIsCoupon=1 AND cpnNumber='".$rgcpncode."'))" .
			' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs=mysql_fetch_assoc($result)){
			if($freeshipavailtodestination || (int)$rs['cpnCntry']==0){
				if($rgcpncode!='' && strtolower(trim($rs['cpnNumber']))==strtolower($rgcpncode)){ $gotcpncode=TRUE; $appliedcouponname = $rs['cpnName']; }
				if($freeshipmethodexists){
					if($fromshipselector){
						if($intShipping[$selectedshiptype][4]==1){
							$freeshipamnt = $shipping;
							if(stristr($cpnmessage,'<br />' . $rs[getlangid('cpnName',1024)] . '<br />')==FALSE) $cpnmessage .= $rs[getlangid('cpnName',1024)] . '<br />';
						}
					}else{
						$freeshipamnt = $shipping;
						if(stristr($cpnmessage,'<br />' . $rs[getlangid('cpnName',1024)] . '<br />')==FALSE) $cpnmessage .= $rs[getlangid('cpnName',1024)] . '<br />';
					}
					if($rs['cpnHandling']!=0){ $freeshippingincludeshandling=TRUE; $handling=0; $handlingchargepercent=0; }
					if($subcpns){
						$theres = mysql_query('SELECT cpnID FROM coupons WHERE cpnNumAvail>0 AND cpnNumAvail<30000000 AND cpnID=' . $rs['cpnID'] . ' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))') or print(mysql_error());
						if($theresset = mysql_fetch_assoc($theres)) @$_SESSION['couponapply'] .= ',' . $rs['cpnID'];
						mysql_query('UPDATE coupons SET cpnNumAvail=cpnNumAvail-1 WHERE cpnNumAvail>0 AND cpnNumAvail<30000000 AND cpnID=' . $rs['cpnID']) or print(mysql_error());
					}
					$freeshippingapplied=TRUE;
				}
			}
		}
		mysql_free_result($result);
	}
	if($somethingToShip && ! $fromshipselector){
		$gotshipping=FALSE;
		if($shipType>=2){
			if($shipType==2 || $shipType==5) sortshippingarray();
			for($indexmso=0; $indexmso<$maxshipoptions; $indexmso++){
				if($intShipping[$indexmso][3]==TRUE){
					if(!$gotshipping || ($intShipping[$indexmso][4]&&$freeshippingapplied)){
						$shipping = $intShipping[$indexmso][2];
						$shipMethod = $intShipping[$indexmso][0];
						$selectedshiptype=$indexmso;
						$gotshipping=TRUE;
					}
					if($intShipping[$indexmso][4]&&$freeshippingapplied) $freeshipamnt=$shipping;
				}
			}
		}
	}
	if($freeshipamnt > $shipping) $freeshipamnt = $shipping;
}
function initshippingmethods(){
	global $shipType,$adminIntShipping,$allzones,$numzones,$splitUSZones,$shiphomecountry,$numshipoptions,$intShipping,$success,$errormsg,$commercialloc_,$codpaymentprovider,$signaturerelease_,$allowsignaturerelease,$signatureoption,$saturdaydelivery_,$saturdaypickup,$insidedelivery_,$insidepickup,$thesessionid,$adminSecret,$cartisincluded,$willpickup_,$selectedshiptype,$currShipType,$ordShipCity,$countryCurrency;
	global $uspsmethods,$numuspsmeths,$international,$shipcountry,$maxshipoptions,$origCountry,$willpickuptext,$willpickupcost,$shipstate,$xxNoMeth,$shipinsuranceamt,$fedexaccount,$fedexmeter,$stateAbbrev,$shipStateAbbrev,$usestateabbrev,$ordPayProvider,$usandcausedomesticservice,$adminAltRates,$altrate,$shipping,$handling,$shipMethod,$freeshipmethodexists,$multipleoptions,$fedexuserkey,$fedexuserpwd,$fedexpickuptype,$smartPostHub,$smartpostindicia,$smartpostancendorsement;
	global $sXML,$uspsUser,$uspsPw,$upsAccess,$upsUser,$upsPw,$upspickuptype,$origZip,$origCountryCode,$destZip,$shipCountryCode,$adminCanPostUser,$packaging,$adminUnits,$xxPlsSta,$homedelivery,$originstatecode,$ordCity,$ordShipAddress,$ordShipAddress2,$upsnegdrates,$upsnegotiatedaccess,$upsnegotiateduser,$upsnegotiatedpw,$upsAccount,$defaultshipstate,$fromshipselector,$thepweight,$initialpackweight,$iWeight;
	if(@$initialpackweight!=''){ $thepweight=$initialpackweight; $iWeight=$initialpackweight; }
	if($shipcountry!=$origCountry && ! ($shipType==3 && $shipCountryCode=='PR')){
		$international = 'Intl';
		$willpickuptext = '';
		$willpickup_=FALSE;
	}
	if($adminIntShipping!=0 && $shipcountry!=$origCountry && ! (($shipCountryCode=='US' || $shipCountryCode=='CA') && @$usandcausedomesticservice)){
		if($cartisincluded || @$_POST['altrates']=='') $shipType=$adminIntShipping;
	}
	if($willpickup_){
		$shipType=0;
		$adminAltRates=0;
		if(@$willpickupcost!='') $shipping=$willpickupcost; else $shipping=0;
		$shipMethod=$willpickuptext;
	}
	if($adminAltRates>0){
		$result = mysql_query('SELECT altrateid FROM alternaterates WHERE usealtmethod'.$international.'<>0') or print(mysql_error());
		if(mysql_num_rows($result)<2) $adminAltRates=0;
		mysql_free_result($result);
	}
	if($altrate!='' && $adminAltRates>0){
		$result = mysql_query('SELECT altrateid FROM alternaterates WHERE usealtmethod'.$international.'<>0 AND altrateid='.$altrate) or print(mysql_error());
		if(mysql_num_rows($result)>0) $shipType=$altrate;
		mysql_free_result($result);
	}
	for($i=0; $i < $maxshipoptions; $i++){
		$intShipping[$i][0]=''; // Name
		$intShipping[$i][1]=''; // Delivery
		$intShipping[$i][2]=0; // Cost
		$intShipping[$i][3]=0; // Used
		$intShipping[$i][4]=0; // FSA
		$intShipping[$i][5]=''; // Service ID (USPS)
		$intShipping[$i][6]=$shipType; // shipType
	}
	if($fromshipselector){
		if(is_numeric(@$_POST['orderid']) && is_numeric(@$_POST['shipselectoridx'])){
			$numshipoptions = 0;
			$sSQL = 'SELECT soMethodName,soCost,soFreeShip,soShipType,soDeliveryTime FROM shipoptions WHERE soOrderID=' . @$_POST['orderid'] . ' ORDER BY soIndex';
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs=mysql_fetch_assoc($result)){
				$intShipping[$numshipoptions][0] = $rs['soMethodName'];
				$intShipping[$numshipoptions][1] = $rs['soDeliveryTime'];
				$intShipping[$numshipoptions][2] = $rs['soCost'];
				$intShipping[$numshipoptions][3] = TRUE;
				$intShipping[$numshipoptions][4] = $rs['soFreeShip'];
				$freeshipmethodexists=($freeshipmethodexists || $intShipping[$numshipoptions][4]);
				$intShipping[$numshipoptions][6] = $rs['soShipType'];
				$numshipoptions++;
			}
			mysql_free_result($result);
			$selectedshiptype=(int)@$_POST['shipselectoridx'];
			$shipping = $intShipping[$selectedshiptype][2];
			$shipMethod = $intShipping[$selectedshiptype][0];
			$shipType = $intShipping[$selectedshiptype][6];
			$currShipType = $intShipping[0][6];
			$multipleoptions=TRUE;
			$numshipoptions--;
		}
	}elseif($shipType==2 || $shipType==5){ // Weight / Price based shipping
		$allzones='';
		$numzones=0;
		$zoneid=0;
		if($splitUSZones && $shiphomecountry)
			$sSQL = "states INNER JOIN postalzones ON postalzones.pzID=states.stateZone WHERE stateName='" . escape_string($shipstate) . "' OR stateAbbrev='" . escape_string($shipstate) . "'";
		else
			$sSQL = "countries INNER JOIN postalzones ON postalzones.pzID=countries.countryZone WHERE countryName='" . escape_string($shipcountry) . "'";
		$result = mysql_query('SELECT pzID,pzMultiShipping,pzFSA,pzMethodName1,pzMethodName2,pzMethodName3,pzMethodName4,pzMethodName5 FROM '.$sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)){
			$zoneid=$rs['pzID'];
			$numshipoptions=$rs['pzMultiShipping']+1;
			for($index3=0; $index3 < $numshipoptions; $index3++){
				$intShipping[$index3][0]=$rs['pzMethodName' . ($index3+1)];
				$intShipping[$index3][3]=TRUE;
				$intShipping[$index3][4]=(($rs['pzFSA'] & (1 << $index3)) > 0 ? 1 : 0);
			}
		}else{
			$success=FALSE;
			if($splitUSZones && $shiphomecountry && $shipstate=='') $errormsg = $xxPlsSta; else $errormsg = 'Country / state shipping zone is unassigned.';
		}
		mysql_free_result($result);
		$sSQL = 'SELECT zcWeight,zcRate,zcRate2,zcRate3,zcRate4,zcRate5,zcRatePC,zcRatePC2,zcRatePC3,zcRatePC4,zcRatePC5 FROM zonecharges WHERE zcZone=' . $zoneid . ' ORDER BY zcWeight';
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_row($result))
			$allzones[$numzones++] = $rs;
		mysql_free_result($result);
	}elseif($shipType==3 || $shipType==4 || $shipType==6 || $shipType==7 || $shipType==8){ // USPS / UPS / Canada Post / FedEx / FedEx SmartPost
		$uspsmethods='';
		$numuspsmeths=0;
		if($shipType==3){
			$sSQL = ' FROM uspsmethods WHERE uspsID<100 AND uspsLocal='.($international==''?'1':'0');
		}elseif($shipType==4){
			$shipinsuranceamt='';
			$sSQL = ' FROM uspsmethods WHERE uspsID>100 AND uspsID<200';
		}elseif($shipType==6)
			$sSQL = ' FROM uspsmethods WHERE uspsID>200 AND uspsID<300';
		elseif($shipType==7)
			$sSQL = ',uspsLocal FROM uspsmethods WHERE uspsID>300 AND uspsID<400'.($international==''&&$commercialloc_?" AND uspsMethod<>'GROUNDHOMEDELIVERY'":'');
		elseif($shipType==8)
			$sSQL = ',uspsLocal FROM uspsmethods WHERE uspsID>400 AND uspsID<500';
		$result = mysql_query('SELECT uspsMethod,uspsFSA,uspsShowAs'.$sSQL.' AND uspsUseMethod=1') or print(mysql_error());
		if(mysql_num_rows($result) > 0){
			while($rs = mysql_fetch_row($result))
				$uspsmethods[$numuspsmeths++] = $rs;
		}else{
			$success=FALSE;
			$errormsg = 'Admin Error: ' . $xxNoMeth;
		}
		mysql_free_result($result);
	}
	if(($shipType==4 || $shipType==7 || $shipType==8) && $shipCountryCode=='US' && $shipStateAbbrev=='PR') $shipCountryCode='PR';
	if($shipType==3 && $shipCountryCode=='PR'){ $shipCountryCode='US'; $shipStateAbbrev='PR'; }
	if(($shipCountryCode=='PR' || ($shipCountryCode=='US' && $shipStateAbbrev=='PR')) && strlen($destZip)==3) $destZip='00'.$destZip;
	if($shipType==3)
		$sXML = '<' . $international . 'Rate' . ($international=='' ? 'V3' : '') . 'Request USERID="' . $uspsUser . '">';
	elseif($shipType==4){
		if($shipCountryCode=='US' && $shipStateAbbrev=='VI') $shipCountryCode='VI';
		$sXML = '<?xml version="1.0"?><AccessRequest xml:lang="en-US">' . addtag('AccessLicenseNumber',$upsAccess) . addtag('UserId',$upsUser) . addtag('Password',$upsPw) . '</AccessRequest><?xml version="1.0"?>' .
			'<RatingServiceSelectionRequest xml:lang="en-US"><Request><TransactionReference><CustomerContext>Rating and Service</CustomerContext><XpciVersion>1.0001</XpciVersion></TransactionReference>' .
			'<RequestAction>Rate</RequestAction><RequestOption>shop</RequestOption></Request>';
		if(@$upspickuptype!='') $sXML .= '<PickupType><Code>' . $upspickuptype . '</Code></PickupType>';
		$sXML .= '<Shipment><Shipper>' . (@$upsnegdrates? addtag('ShipperNumber',$upsAccount) : '') . '<Address>' . (@$upsnegdrates ? addtag('StateProvinceCode',$defaultshipstate) : '') . addtag('PostalCode',$origZip) . addtag('CountryCode',$origCountryCode) . '</Address></Shipper>';
		$sXML .= '<ShipTo><Address>' . addtag('AddressLine1',$ordShipAddress) . addtag('AddressLine2',$ordShipAddress2) . addtag('City',$ordCity) . ($shipCountryCode=='US'||$shipCountryCode=='CA'?addtag('StateProvinceCode',$shipStateAbbrev):'') . addtag('PostalCode',$destZip) . addtag('CountryCode',$shipCountryCode) . (! $commercialloc_ ? '<ResidentialAddressIndicator/>' : '') . '</Address></ShipTo>';
	}elseif($shipType==6){
		$sXML = '<?xml version="1.0" ?> <eparcel><language> en </language><ratesAndServicesRequest><merchantCPCID> ' . $adminCanPostUser . ' </merchantCPCID><fromPostalCode> ' . $origZip . ' </fromPostalCode><lineItems>';
	}elseif($shipType==7 || $shipType==8){ // FedEx
		if($shipType==8) $smartposthubid=$smartPostHub;
		if(@$packaging!='') $packaging='FEDEX_' . strtoupper($packaging); else $packaging='YOUR_PACKAGING';
		if(@$fedexpickuptype=='') $fedexpickuptype='REGULAR_PICKUP';
		$sXML = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v9="http://fedex.com/ws/rate/v9">' .
		"<soapenv:Header/><soapenv:Body><v9:RateRequest><v9:WebAuthenticationDetail><v9:CspCredential><v9:Key>mKOUqSP4CS0vxaku</v9:Key><v9:Password>IAA5db3Pmhg3lyWW6naMh4Ss2</v9:Password></v9:CspCredential>" .
		"<v9:UserCredential><v9:Key>" . $fedexuserkey . "</v9:Key><v9:Password>" . $fedexuserpwd . "</v9:Password></v9:UserCredential></v9:WebAuthenticationDetail>" .
		"<v9:ClientDetail><v9:AccountNumber>" . $fedexaccount . "</v9:AccountNumber><v9:MeterNumber>" . $fedexmeter . "</v9:MeterNumber><v9:ClientProductId>IBTP</v9:ClientProductId><v9:ClientProductVersion>3272</v9:ClientProductVersion></v9:ClientDetail>" .
		"<v9:TransactionDetail><v9:CustomerTransactionId>Rate Request</v9:CustomerTransactionId></v9:TransactionDetail>" .
		"<v9:Version><v9:ServiceId>crs</v9:ServiceId><v9:Major>9</v9:Major><v9:Intermediate>0</v9:Intermediate><v9:Minor>0</v9:Minor></v9:Version>" .
		"<v9:ReturnTransitAndCommit>true</v9:ReturnTransitAndCommit>" .
		"<v9:RequestedShipment><v9:DropoffType>" . $fedexpickuptype . "</v9:DropoffType>" . (@$smartposthubid!='' ? "<v9:ServiceType>SMART_POST</v9:ServiceType>" : '') . "<v9:PackagingType>" . $packaging . "</v9:PackagingType>" .
		"<v9:Shipper><v9:Address><v9:PostalCode>" . $origZip . "</v9:PostalCode><v9:CountryCode>" . $origCountryCode . "</v9:CountryCode>" .
		"</v9:Address></v9:Shipper><v9:Recipient><v9:Address>";
		if($ordShipAddress!='') $sXML .= "<v9:StreetLines>" . $ordShipAddress . "</v9:StreetLines>";
		if($ordShipCity!='') $sXML .= "<v9:City>" . $ordShipCity . "</v9:City>";
		if($shipCountryCode=="US" || $shipCountryCode=="CA") $sXML .= "<v9:StateOrProvinceCode>" . $shipStateAbbrev . "</v9:StateOrProvinceCode>";
		$sXML .= "<v9:PostalCode>" . $destZip . "</v9:PostalCode>" .
		"<v9:CountryCode>" . $shipCountryCode . "</v9:CountryCode><v9:Residential>" . ($commercialloc_ ? "false" : "true") . "</v9:Residential></v9:Address></v9:Recipient>";
		if(@$smartposthubid!=''){
			if(@$smartpostindicia=='') $smartpostindicia="PARCEL_SELECT";
			$sXML .= "<v9:SmartPostDetail><v9:Indicia>" . $smartpostindicia . "</v9:Indicia>" . (@$smartpostancendorsement!='' ? "<v9:AncillaryEndorsement>" . $smartpostancendorsement . "</v9:AncillaryEndorsement>" : '') . "<v9:HubId>" . $smartposthubid . "</v9:HubId></v9:SmartPostDetail>";
		}else{
			$sXML .= "<v9:SpecialServicesRequested>";
			if($saturdaydelivery_==TRUE) $sXML .= "<v9:SpecialServiceTypes>SATURDAY_DELIVERY</v9:SpecialServiceTypes>";
			if($saturdaypickup==TRUE) $sXML .= "<v9:SpecialServiceTypes>SATURDAY_PICKUP</v9:SpecialServiceTypes>";
			if($insidedelivery_==TRUE) $sXML .= "<v9:SpecialServiceTypes>INSIDE_DELIVERY</v9:SpecialServiceTypes>";
			if($insidepickup==TRUE) $sXML .= "<v9:SpecialServiceTypes>INSIDE_PICKUP</v9:SpecialServiceTypes>";
			if(@$emailnotification==TRUE) $sXML .= "<v9:SpecialServiceTypes>EMAIL_NOTIFICATION</v9:SpecialServiceTypes>";
			if(@$_POST['homedelivery']!='') $sXML .= "<v9:SpecialServiceTypes>HOME_DELIVERY_PREMIUM</v9:SpecialServiceTypes><v9:HomeDeliveryPremiumDetail><v9:HomeDeliveryPremiumType>" . $_POST['homedelivery'] . "</v9:HomeDeliveryPremiumType></v9:HomeDeliveryPremiumDetail>";
			if($ordPayProvider!=''){
				//if(int(ordPayProvider)=codpaymentprovider then $sXML .= "<v9:SpecialServiceTypes>COD</v9:SpecialServiceTypes><v9:CodDetail><v9:CodCollectionAmount><v9:Currency>CAD</v9:Currency><v9:Amount>XXXFEDEXGRANDTOTXXX</v9:Amount></v9:CodCollectionAmount><v9:CollectionType>ANY</v9:CollectionType></v9:CodDetail>"
			}
			if(@$holdatlocation==TRUE) $sXML .= "<v9:SpecialServiceTypes>HOLD_AT_LOCATION</v9:SpecialServiceTypes><v9:HoldAtLocationDetail><v9:PhoneNumber>9052125251</v9:PhoneNumber><v9:LocationContactAndAddress><v9:Address><v9:StreetLines>HAL Address Line 1</v9:StreetLines><v9:City>St-Laurent</v9:City><v9:StateOrProvinceCode>QC</v9:StateOrProvinceCode><v9:PostalCode>H4T2A3</v9:PostalCode><v9:CountryCode>CA</v9:CountryCode></v9:Address></v9:LocationContactAndAddress></v9:HoldAtLocationDetail>";
			$sXML .= "</v9:SpecialServicesRequested>";
			$sXML .= "<v9:CustomsClearanceDetail>" . (@$customsaccountnumber!='' ? "<v9:DutiesPayment><v9:PaymentType>SENDER</v9:PaymentType></v9:DutiesPayment>" : '') . "<v9:CustomsValue><v9:Currency>" . $countryCurrency . "</v9:Currency><v9:Amount>XXXFEDEXGRANDTOTXXX</v9:Amount></v9:CustomsValue></v9:CustomsClearanceDetail>";
		}
		//$sXML .= "<v9:TotalInsuredValue><v9:Currency>" . countryCurrency . "</v9:Currency><v9:Amount>XXXFEDEXGRANDTOTXXX</v9:Amount></v9:TotalInsuredValue>"
		$sXML .= "<v9:RateRequestTypes>ACCOUNT</v9:RateRequestTypes><v9:PackageDetail>INDIVIDUAL_PACKAGES</v9:PackageDetail>";
	}
}
$packdims = array(0,0,0,0); // len : wid : hei : vol used
function zeropackdims(){
	global $packdims;
	$packdims[0]=0; $packdims[1]=0; $packdims[2]=0; $packdims[3]=0;
}
function reorderpackagedimensions(){
	global $packdims;
	if($packdims[2] > $packdims[1]){ $apdtemp = $packdims[1]; $packdims[1] = $packdims[2]; $packdims[2] = $apdtemp; }
	if($packdims[1] > $packdims[0]){ $apdtemp = $packdims[0]; $packdims[0] = $packdims[1]; $packdims[1] = $apdtemp; }
	if($packdims[2] > $packdims[1]){ $apdtemp = $packdims[1]; $packdims[1] = $packdims[2]; $packdims[2] = $apdtemp; }
}
function reorderproddims(&$pdims){
	$pdims[0]=(double)$pdims[0]; $pdims[1]=(double)$pdims[1]; $pdims[2]=(double)$pdims[2];
	if($pdims[2] > $pdims[1]){ $apdtemp = $pdims[1]; $pdims[1] = $pdims[2]; $pdims[2] = $apdtemp; }
	if($pdims[1] > $pdims[0]){ $apdtemp = $pdims[0]; $pdims[0] = $pdims[1]; $pdims[1] = $apdtemp; }
	if($pdims[2] > $pdims[1]){ $apdtemp = $pdims[1]; $pdims[1] = $pdims[2]; $pdims[2] = $apdtemp; }
}
function addpackagedimensions($dimens, $apdquant){
	global $packdims,$adminUnits;
	if(($adminUnits & 12)!=0){
		$origdimens = $packdims;
		$proddims = explode('x',$dimens);
		if(@$proddims[0]!=''&&@$proddims[1]!=''&&@$proddims[2]!=''){
			reorderproddims($proddims);
			$proddims[2] = $proddims[2] * $apdquant;
			$thelength=$proddims[0];
			reorderproddims($proddims);
			while($apdquant > 4 && $proddims[0] > $proddims[2] * 2 && $proddims[0] > $thelength){
				$proddims[0] = $proddims[0] / 2; $proddims[2] = $proddims[2] * 2; $apdquant = $apdquant / 2;
				reorderproddims($proddims);
			}
			$thelength = $proddims[0]; $thewidth = $proddims[1]; $theheight = $proddims[2];
			$objvol = $thelength * $thewidth * $theheight;
			if($thelength > $packdims[0]) $packdims[0] = $thelength;
			if($thewidth > $packdims[1]) $packdims[1] = $thewidth;
			if($theheight > $packdims[2]) $packdims[2] = $theheight;
			if($objvol + $packdims[3] > $packdims[0] * $packdims[1] * $packdims[2]) $packdims[2] = $packdims[2] + ($origdimens[2] > 0 && $origdimens[2] < $theheight ? $origdimens[2] : $theheight);
			if($objvol + $packdims[3] > $packdims[0] * $packdims[1] * $packdims[2]) $packdims[1] = $packdims[1] + ($origdimens[1] > 0 && $origdimens[1] < $thewidth ? $origdimens[1] : $thewidth);
			if($objvol + $packdims[3] > $packdims[0] * $packdims[1] * $packdims[2]) $packdims[0] = $packdims[0] + ($origdimens[0] > 0 && $origdimens[0] < $thelength ? $origdimens[0] : $thelength);
			$packdims[3] = $packdims[3] + $objvol;
			reorderpackagedimensions();
		}
	}
	// print "Bin is : " . $packdims[0] . ':' . $packdims[1] . ':' . $packdims[2] . ' = ' . ($packdims[0]*$packdims[1]*$packdims[2]) . '<br />';
}
function splitlargepacks(){
	global $packdims,$shipType,$adminUnits,$nosplitlargepacks;
	$slpnumpacks=1;
	if($shipType==4){
		if(($adminUnits & 12)==4){ $maxlenplusgirth = 165; $maxlength = 108; }else{ $maxlenplusgirth = 419; $maxlength = 270; }
	}elseif($shipType==7 || $shipType==8){
		if(($adminUnits & 12)==4){ $maxlenplusgirth = 165; $maxlength = 108; }else{ $maxlenplusgirth = 419; $maxlength = 274; }
	}else{ // USPS Default
		$maxlenplusgirth = 130; $maxlength = 0;
	}
	if(@$nosplitlargepacks!=TRUE && ($adminUnits & 12)!=0){
		if($packdims[0] + (($packdims[1] + $packdims[2]) * 2) > $maxlenplusgirth){ // Max Length + Girth
			$divisor = 1;
			while(($packdims[0]/sqrt($divisor)) + ((($packdims[1]/sqrt($divisor)) + $packdims[2]) * 2) > $maxlenplusgirth)
				$divisor++;
			if($packdims[0]/sqrt($divisor) > $maxlength && $maxlength!=0 && ($packdims[0]/$divisor) + (($packdims[1] + $packdims[2]) * 2) <= $maxlenplusgirth)
				$packdims[0] = $packdims[0]/$divisor;
			else{
				$packdims[0] = $packdims[0]/sqrt($divisor);
				$packdims[1] = $packdims[1]/sqrt($divisor);
			}
			$slpnumpacks *= $divisor;
			reorderpackagedimensions();
		}
		if($packdims[0] > $maxlength && $maxlength!=0){
			$packdims[0] = $packdims[0] / 2;
			$slpnumpacks *= 2;
			reorderpackagedimensions();
		}
	}
	return($slpnumpacks);
}
function addproducttoshipping($apsrs, $prodindex){
	global $shipping,$shipType,$packtogether,$shipThisProd,$somethingToShip,$itemsincart,$intShipping,$international,$shipcountry,$fromshipselector;
	global $rowcounter,$origZip,$destZip,$sXML,$numshipoptions,$allzones,$numzones,$dHighWeight,$adminUnits,$shipCountryCode,$totalshipitems;
	global $upspacktype,$splitpackat,$iTotItems,$thePQuantity,$thepweight,$iWeight,$totalgoods,$shipfreegoods,$packaging,$packdims,$initialpackweight;
	addpackagedimensions($apsrs['pDims'], ($packtogether ? (int)$apsrs['cartQuantity'] : 1));
	if($packtogether) $iTotItems=1; else $iTotItems += 1;
	$shipThisProd=TRUE;
	if(($apsrs['pExemptions'] & 4)==4){ // No Shipping on this product
		if(! $packtogether) $iTotItems -= (int)$apsrs['cartQuantity'];
		$shipThisProd=FALSE;
	}
	if($fromshipselector){
	}elseif($shipType==1){ // Flat rate shipping
		if($shipThisProd){
			$shipping += $apsrs['pShipping'] + $apsrs['pShipping2'] * ($apsrs['cartQuantity']-1);
			$somethingToShip=TRUE;
		}
	}elseif($shipType==2 || $shipType==5){ // Weight / Price based shipping
		$havematch=FALSE;
		for($index3=0; $index3 < $numshipoptions; $index3++)
			$dHighest[$index3]=0;
		if(is_array($allzones)){
			if($shipThisProd){
				$somethingToShip=TRUE;
				if($shipType==2) $tmpweight = (double)$apsrs['pWeight']; else $tmpweight = (double)$apsrs['cartProdPrice'];
				if($packtogether){
					$thepweight += ((double)($apsrs['cartQuantity'])*$tmpweight);
					$thePQuantity = 1;
				}else{
					$thepweight = $tmpweight + (@$initialpackweight!=''?$initialpackweight:0);
					$thePQuantity = (double)$apsrs['cartQuantity'];
				}
			}
			if(((!$packtogether && $shipThisProd) || ($packtogether && ($prodindex==$itemsincart))) && $somethingToShip){ // Only calculate pack together when we have the total
				for($index2=0; $index2 < $numzones; $index2++){
					if($allzones[$index2][0] >= $thepweight){
						$havematch=TRUE;
						for($index3=0; $index3 < $numshipoptions; $index3++){
							if($allzones[$index2][6+$index3]!=0) // Percentage
								$intShipping[$index3][2] += ((double)$allzones[$index2][1+$index3]*$thePQuantity*$thepweight)/100.0;
							else
								$intShipping[$index3][2] += ((double)$allzones[$index2][1+$index3]*$thePQuantity);
							if((double)$allzones[$index2][1+$index3]==-99999.0) $intShipping[$index3][3]=FALSE;
						}
						break;
					}
					$dHighWeight = $allzones[$index2][0];
					for($index3=0; $index3 < $numshipoptions; $index3++){
						if($allzones[$index2][6+$index3]!=0) // Percentage
							$dHighest[$index3]=($allzones[$index2][1+$index3]*$dHighWeight)/100.0;
						else
							$dHighest[$index3]=$allzones[$index2][1+$index3];
					}
				}
				if(! $havematch){
					for($index3=0; $index3 < $numshipoptions; $index3++){
						$intShipping[$index3][2] += $dHighest[$index3];
						if($dHighest[$index3]==-99999.0) $intShipping[$index3][3]=FALSE;
					}
					if($allzones[0][0] < 0){
						$dHighWeight = $thepweight - $dHighWeight;
						while($dHighWeight > 0){
							for($index3=0; $index3 < $numshipoptions; $index3++)
								$intShipping[$index3][2] += ((double)($allzones[0][1+$index3])*$thePQuantity);
							$dHighWeight += $allzones[0][0];
						}
					}
				}
			}
		}
	}elseif($shipType==3){ // USPS Shipping
		if($packtogether){
			if($shipThisProd){
				$somethingToShip=TRUE;
				$iWeight += ((double)$apsrs['pWeight'] * (int)$apsrs['cartQuantity']);
			}
			if(($prodindex==$itemsincart) && $somethingToShip){
				$numpacks=1;
				if(@$splitpackat!='')
					if($iWeight > $splitpackat){ $numpacks=ceil($iWeight/$splitpackat); $packdims[0] /= $numpacks; reorderpackagedimensions(); }
				if($numpacks > 1){
					if($international!='')
						$sXML .= addUSPSInternational($rowcounter,$splitpackat,$numpacks-1,'Package',$shipcountry);
					else
						$sXML .= addUSPSDomestic($rowcounter,'Parcel',$origZip,$destZip,$splitpackat,$numpacks-1,'None','REGULAR','True');
					$iTotItems++;
					$iWeight -= ($splitpackat*($numpacks-1));
					$rowcounter++;
				}
				if($international!='')
					$sXML .= addUSPSInternational($rowcounter,$iWeight,1,'Package',$shipcountry);
				else
					$sXML .= addUSPSDomestic($rowcounter,'Parcel',$origZip,$destZip,$iWeight,1,'None','REGULAR','True');
				$rowcounter++;
				zeropackdims();
			}
		}else{
			if($shipThisProd){
				$somethingToShip=TRUE;
				$iWeight=$apsrs['pWeight'] + (@$initialpackweight!=''?$initialpackweight:0);
				$numpacks=1;
				if(@$splitpackat!='')
					if($iWeight > $splitpackat){ $numpacks=ceil($iWeight/$splitpackat); $packdims[0] /= $numpacks; reorderpackagedimensions(); }
				if($numpacks > 1){
					if($international!='')
						$sXML .= addUSPSInternational($rowcounter,$splitpackat,$apsrs['cartQuantity']*($numpacks-1),'Package',$shipcountry);
					else
						$sXML .= addUSPSDomestic($rowcounter,'Parcel',$origZip,$destZip,$splitpackat,$apsrs['cartQuantity']*($numpacks-1),'None','REGULAR','True');
					$iTotItems++;
					$iWeight -= ($splitpackat*($numpacks-1));
					$rowcounter++;
				}
				if($international!='')
					$sXML .= addUSPSInternational($rowcounter,$iWeight,$apsrs['cartQuantity'],'Package',$shipcountry);
				else
					$sXML .= addUSPSDomestic($rowcounter,'Parcel',$origZip,$destZip,$iWeight,$apsrs['cartQuantity'],'None','REGULAR','True');
				$rowcounter++;
				zeropackdims();
			}
		}
	}elseif($shipType==4 || $shipType==6 || $shipType==7 || $shipType==8){ // UPS Shipping OR Canada Post OR FedEx
		if(@$packaging!=''){
			if($packaging=='envelope') $packaging='01';
			if($packaging=='pak') $packaging='04';
			if($packaging=='box') $packaging='21';
			if($packaging=='tube') $packaging='03';
			if($packaging=='10kgbox') $packaging='25';
			if($packaging=='25kgbox') $packaging='24';
		}elseif(@$upspacktype!='')
			$packaging=$upspacktype;
		else
			$packaging='02';
		if($packtogether){
			if($shipThisProd){
				$somethingToShip=TRUE;
				$iWeight += ((double)$apsrs['pWeight'] * (int)$apsrs['cartQuantity']);
			}
			if(($prodindex==$itemsincart) && $somethingToShip){
				$numpacks=1;
				if(@$splitpackat!='')
					if($iWeight > $splitpackat){ $numpacks=ceil($iWeight/$splitpackat); $packdims[0] /= $numpacks; reorderpackagedimensions(); }
				if($shipType==4) $numpacks *= splitlargepacks();
				for($index3=0;$index3 < $numpacks; $index3++)
					if($shipType==4)
						$sXML .= addUPSInternational($iWeight / $numpacks,$adminUnits,$packaging,$shipCountryCode,$totalgoods-$shipfreegoods,$packdims);
					elseif($shipType==6)
						$sXML .= addCanadaPostPackage($iWeight / $numpacks,$adminUnits,$packaging,$shipCountryCode,$totalgoods-$shipfreegoods,$packdims);
					else
						$sXML .= addFedexPackage($iWeight,$totalgoods-$shipfreegoods,$packdims);
				zeropackdims();
			}
		}else{
			if($shipThisProd){
				$somethingToShip=TRUE;
				$iWeight=$apsrs['pWeight'] + (@$initialpackweight!=''?$initialpackweight:0);
				$numpacks=1;
				if(@$splitpackat!='')
					if($iWeight > $splitpackat){ $numpacks=ceil($iWeight/$splitpackat); $packdims[0] /= $numpacks; reorderpackagedimensions(); }
				if($shipType==4) $numpacks *= splitlargepacks();
				for($index2=0;$index2 < (int)$apsrs['cartQuantity']; $index2++)
					for($index3=0;$index3 < $numpacks; $index3++)
						if($shipType==4)
							$sXML .= addUPSInternational($iWeight / $numpacks,$adminUnits,$packaging,$shipCountryCode,$apsrs['cartProdPrice'],$packdims);
						elseif($shipType==6)
							$sXML .= addCanadaPostPackage($iWeight / $numpacks,$adminUnits,$packaging,$shipCountryCode,$apsrs['cartProdPrice'],$packdims);
						else
							$sXML .= addFedexPackage($iWeight,$apsrs['cartProdPrice'],$packdims);
				zeropackdims();
			}
		}
	}
}
function calculateshipping(){
	global $shipType,$freeshipmethodexists,$multipleoptions,$somethingToShip,$willpickuptext,$willpickupcost,$allzones,$numshipoptions,$upsUser,$upsPw,$shipCountryCode,$destZip,$totalgoods,$thesessionid,$handling,$selectedshiptype,$willpickup_;
	global $shipping,$shipMethod,$success,$errormsg,$xxNoMeth,$sXML,$intShipping,$international,$iTotItems,$uspsmethods,$numuspsmeths,$shipstate,$maxshipoptions,$saturdaydelivery_,$saturdaypickup,$adminSecret,$upsnegdrates,$fromshipselector;
	if($fromshipselector){
	}elseif($shipType==1){
		$freeshipmethodexists = TRUE;
	}elseif($shipType==3 && $somethingToShip){
		$sXML .= '</' . $international . 'Rate' . ($international=='' ? 'V3' : '') . 'Request>';
		$success = USPSCalculate($sXML,$international,$errormsg,$intShipping);
		if(substr($errormsg, 0, 30)=='Warning - Bound Printed Matter') $success=TRUE;
		if($success){
			for($indexmso=0; $indexmso<$maxshipoptions; $indexmso++){
				$shipRow = $intShipping[$indexmso];
				if($iTotItems==$shipRow[3]){
					$intShipping[$indexmso][3]=TRUE;
					for($index2=0;$index2<$numuspsmeths;$index2++){
						if(str_replace('-',' ',strtolower($shipRow[5]))==str_replace('-',' ',strtolower($uspsmethods[$index2][0]))){
							$intShipping[$indexmso][4]=$uspsmethods[$index2][1];
						}
					}
				}else
					$intShipping[$indexmso][3]=FALSE;
			}
		}
	}elseif($shipType==4 && $somethingToShip){
		$sXML .= '<ShipmentServiceOptions>' . ($saturdaydelivery_ ? '<SaturdayDelivery/>' : '') . (@$saturdaypickup==TRUE ? '<SaturdayPickup/>' : '') . '</ShipmentServiceOptions>' . (@$upsnegdrates==TRUE?'<RateInformation><NegotiatedRatesIndicator /></RateInformation>':'') . '</Shipment></RatingServiceSelectionRequest>';
		if(trim($upsUser)!='' && trim($upsPw)!='')
			$success = UPSCalculate($sXML,$international,$errormsg,$intShipping);
		else{
			$success = FALSE;
			$errormsg = 'You must register with UPS by logging on to your online admin section and clicking the &quot;Register with UPS&quot; link before you can use the UPS OnLine&reg; Shipping Rates and Services Selection';
		}
	}elseif($shipType==6 && $somethingToShip){
		if(@$_POST['shipping']==''){
			$sXML = str_replace('<lineItems>', addtag('itemsPrice',$totalgoods) . '<lineItems>',$sXML) . ' </lineItems><city> </city> ';
			if($shipstate!='')
				$sXML .= '<provOrState> ' . $shipstate . ' </provOrState>';
			else{
				if($shipCountryCode=='US' || $shipCountryCode=='CA'){
					$thestate = ((trim(@$_POST['sname'])!='' || trim(@$_POST['saddress'])!='') ? @$_POST['sstate2'] : @$_POST['state2']);
					if($thestate=='') $thestate=($shipCountryCode=='US' ? 'CA' : 'QC');
					$sXML .= '<provOrState> ' . $thestate . ' </provOrState>';
				}else
					$sXML .= '<provOrState> </provOrState>';
			}
			$sXML .= addtag('country',$shipCountryCode) . addtag('postalCode',$destZip) . '</ratesAndServicesRequest></eparcel>';
			$success = CanadaPostCalculate($sXML,$international,$errormsg,$intShipping);
		}
	}elseif(($shipType==7 || $shipType==8) && $somethingToShip){
		$sXML = str_replace('XXXFEDEXGRANDTOTXXX',$totalgoods,$sXML);
		$sXML .= '</v9:RequestedShipment></v9:RateRequest></soapenv:Body></soapenv:Envelope>';
		$success = fedexcalculate($sXML,$international,$errormsg,$intShipping);
	}
	if($success && @$_POST['shipping']=='' && $somethingToShip && ! $fromshipselector && $shipType>=2){
		$totShipOptions = 0;
		$multipleoptions=TRUE;
		for($indexmso=0; $indexmso<$maxshipoptions; $indexmso++){
			if($intShipping[$indexmso][3]==TRUE){
				$totShipOptions++;
				if($intShipping[$indexmso][4]) $freeshipmethodexists=TRUE;
			}
		}
		if($totShipOptions==0 && ! $willpickup_){
			$multipleoptions=FALSE;
			$success=FALSE;
			$errormsg=$xxNoMeth;
		}
		if($willpickup_) $multipleoptions = TRUE;
	}
	return($success);
}
function saveshippingoptions(){
	global $shipType,$intShipping,$orderid,$maxshipoptions;
	$maxindex=0;
	if($shipType>=2 && is_numeric($orderid)){
		$sSQL = "SELECT MAX(soIndex) AS maxindex FROM shipoptions WHERE soOrderID='" . escape_string($orderid) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_assoc($result);
		if(! is_null($rs['maxindex'])) $maxindex=$rs['maxindex']+1;
		mysql_free_result($result);
		for($indexmso=0; $indexmso<$maxshipoptions; $indexmso++){
			if($intShipping[$indexmso][3]==TRUE){
				$sSQL = "INSERT INTO shipoptions (soOrderID,soIndex,soMethodName,soCost,soFreeShip,soShipType,soDeliveryTime,soDateAdded) VALUES (" .
					$orderid . "," . $maxindex . ",'" . escape_string($intShipping[$indexmso][0]) . "'," . $intShipping[$indexmso][2] . "," .
					$intShipping[$indexmso][4] . "," . $shipType . ",'" . escape_string($intShipping[$indexmso][1]) . "','" . date('Y-m-d', time()) . "')";
				mysql_query($sSQL) or print(mysql_error());
				$maxindex++;
			}
		}
	}
}
$numshiprate=0; $numshiprateingroup=0;
function writeshippingoption($shipcost,$freeship,$shipmethod,$isselected,$wsodelivery){
	global $combineshippinghandling,$numshiprate,$numshiprateingroup,$shippingoptionsasradios,$orighandling,$taxHandling,$orighandlingpercent,$stateTaxRate,$countryTaxRate,$freeshippingapplied,$totalgoods,$totaldiscounts,$freeshippingincludeshandling,$handlingeligableitem,$handlingeligablegoods,$shipType;
	if($freeshippingapplied && $freeship==1) $wsofreeshipamnt=$shipcost; else $wsofreeshipamnt=0;
	$wsohandling=round($orighandling, 2);
	if($handlingeligableitem==FALSE)
		$wsohandling = 0;
	elseif(@$orighandlingpercent!=0){
		$temphandling = ((($totalgoods + $shipcost + $wsohandling) - ($totaldiscounts + $wsofreeshipamnt)) * $orighandlingpercent / 100.0);
		if($handlingeligablegoods < $totalgoods && $totalgoods > 0) $temphandling = $temphandling * ($handlingeligablegoods / $totalgoods);
		$wsohandling += $temphandling;
	}
	if($taxHandling==1) $wsohandling += ((double)$wsohandling*((double)$stateTaxRate+(double)$countryTaxRate))/100.0;
	if($freeship==1 && @$freeshippingincludeshandling==TRUE) $wsohandling=0;
	if(@$shippingoptionsasradios==TRUE)
		print '<tr><td width="5"><input type="radio" value="RATE"'.($isselected?' checked="checked"':'').' onclick="updateshiprate(this,'.$numshiprate.')" /></td><td>'.($numshiprateingroup==0?'<strong>':'').str_replace(' ','&nbsp;',$shipmethod.' '.($wsodelivery!=''?'(' . $wsodelivery . ') ':'').(@$combineshippinghandling?FormatEuroCurrency(($shipcost+$wsohandling)-$wsofreeshipamnt):FormatEuroCurrency($shipcost-$wsofreeshipamnt))).($numshiprateingroup==0?'</strong>':'').'</td></tr>';
	else
		print '<option value="RATE"'.($isselected?' selected="selected"':'').'>'.$shipmethod.' '.($wsodelivery!=''?'(' . $wsodelivery . ') ':'').(@$combineshippinghandling?FormatEuroCurrency(($shipcost+$wsohandling)-$wsofreeshipamnt):FormatEuroCurrency($shipcost-$wsofreeshipamnt)).'</option>';
	$numshiprate++;
	$numshiprateingroup++;
}
$currShipType='';
function showshippingselect(){
	global $fromshipselector,$shipType,$shippingoptionsasradios,$numshiprate,$xxPlsSel,$currShipType,$selectedshiptype,$maxshipoptions,$intShipping,$numshiprateingroup;
	if(! $fromshipselector) calculateshippingdiscounts(FALSE);
	if($shipType>=2){
		if(@$shippingoptionsasradios!=TRUE){
			print '<select size="1" onchange="updateshiprate(this,(this.selectedIndex'.($fromshipselector?'-1':'').')+'.$numshiprate.')">';
			if($fromshipselector) print '<option value="">'.$xxPlsSel.'</option>';
		}else
			print '<table border="0">';
		for($indexmso=0; $indexmso<$maxshipoptions; $indexmso++){
			if($intShipping[$indexmso][3]){
				if($currShipType=='') $currShipType=$intShipping[$indexmso][6];
				if($currShipType!=$intShipping[$indexmso][6]){
					$currShipType=$intShipping[$indexmso][6];
					$numshiprateingroup=0;
					if(@$shippingoptionsasradios!=TRUE) print '</select>'; else print '</table>';
					print '</td></tr><tr><td align="center" style="white-space:nowrap">' . getshiplogo($currShipType) . '</td><td style="white-space:nowrap">';
					if(@$shippingoptionsasradios!=TRUE) print '<select size="1" onchange="updateshiprate(this,(this.selectedIndex-1)+'.$numshiprate.')"><option value="">'.$xxPlsSel.'</option>'; else print '<table border="0">';
				}
				writeshippingoption(round($intShipping[$indexmso][2], 2), $intShipping[$indexmso][4], $intShipping[$indexmso][0], $indexmso==$selectedshiptype, $intShipping[$indexmso][1]);
			}
		}
		if(@$shippingoptionsasradios!=TRUE) print '</select>'; else print '</table>';
	}
}
function getuspsinsurancerate($theamount){
	if($theamount<=0)
		return(0);
	elseif($theamount<=50)
		return(1.75);
	elseif($theamount<=100)
		return(2.25);
	elseif($theamount<=200)
		return(2.75);
	else
		return(4.70 + (1.0 * floor(($theamount-200.01) / 100.0)));
}
function insuranceandtaxaddedtoshipping(){
	global $shipinsuranceamt,$somethingToShip,$wantinsurance_,$addshippinginsurance,$maxshipoptions,$useuspsinsurancerates;
	global $totalgoods,$shipping,$taxShipping,$stateTaxRate,$countryTaxRate,$intShipping,$shipType;
	if((is_numeric(@$shipinsuranceamt) || @$useuspsinsurancerates==TRUE) && $somethingToShip){
		if((($wantinsurance_ && abs(@$addshippinginsurance)==2) || abs(@$addshippinginsurance)==1) && @$useuspsinsurancerates==TRUE && $shipType==3){
			for($index3=0; $index3 < $maxshipoptions; $index3++)
				$intShipping[$index3][2] += getuspsinsurancerate((double)$totalgoods);
			$shipping += getuspsinsurancerate((double)$totalgoods);
		}elseif(! is_numeric(@$shipinsuranceamt)){
			// Nothing
		}elseif(($wantinsurance_ && @$addshippinginsurance==2) || @$addshippinginsurance==1){
			for($index3=0; $index3 < $maxshipoptions; $index3++)
				$intShipping[$index3][2] += (((double)$totalgoods*(double)$shipinsuranceamt)/100.0);
			$shipping += (((double)$totalgoods*(double)$shipinsuranceamt)/100.0);
		}elseif(($wantinsurance_ && @$addshippinginsurance==-2) || @$addshippinginsurance==-1){
			for($index3=0; $index3 < $maxshipoptions; $index3++)
				$intShipping[$index3][2] += $shipinsuranceamt;
			$shipping += $shipinsuranceamt;
		}
	}
	if(@$taxShipping==1){
		for($index3=0; $index3 < $maxshipoptions; $index3++)
			$intShipping[$index3][2] += ((double)$intShipping[$index3][2]*((double)$stateTaxRate+(double)$countryTaxRate))/100.0;
		$shipping += ((double)$shipping*((double)$stateTaxRate+(double)$countryTaxRate))/100.0;
	}
}
function calculatetaxandhandling(){
	global $handlingchargepercent,$handling,$totalgoods,$shipping,$totaldiscounts,$freeshipamnt,$taxHandling,$stateTaxRate,$countryTaxRate,$taxShipping,$showtaxinclusive,$overridecurrency,$orcdecplaces;
	global $stateTax,$countryTax,$canadataxsystem,$shipCountryID,$shipStateAbbrev,$usehst,$statetaxfree,$countrytaxfree,$proratashippingtax,$perproducttaxrate,$handlingeligablegoods,$handlingeligableitem;
	if($handlingeligableitem==FALSE)
		$handling = 0;
	else{
		if($handlingchargepercent!=0){
			$temphandling = ((($totalgoods + $shipping + $handling) - ($totaldiscounts + $freeshipamnt)) * $handlingchargepercent / 100.0);
			if($handlingeligablegoods < $totalgoods && $totalgoods > 0) $temphandling = $temphandling * ($handlingeligablegoods / $totalgoods);
			$handling += $temphandling;
		}
		if(@$taxHandling==1) $handling += ((double)$handling*((double)$stateTaxRate+(double)$countryTaxRate))/100.0;
	}
	if(@$canadataxsystem==TRUE && $shipCountryID==2 && ($shipStateAbbrev=='NB' || $shipStateAbbrev=='NF' || $shipStateAbbrev=='NS' || $shipStateAbbrev=='ON' || $shipStateAbbrev=='BC')) $usehst=TRUE; else $usehst=FALSE;
	if(@$canadataxsystem==TRUE && $shipCountryID==2 && ($shipStateAbbrev=='PE' || $shipStateAbbrev=='QC')){
		$statetaxable = 0;
		$countrytaxable = 0;
		if(@$taxShipping==2 && ($shipping - $freeshipamnt > 0)){
			if(@$proratashippingtax==TRUE){
				if($totalgoods>0) $statetaxable += (((double)$totalgoods-((double)$totaldiscounts+(double)$statetaxfree)) / $totalgoods) * ((double)$shipping-(double)$freeshipamnt);
			}else
				$statetaxable += ((double)$shipping-(double)$freeshipamnt);
			$countrytaxable += ((double)$shipping-(double)$freeshipamnt);
		}
		if(@$taxHandling==2){
			$statetaxable += (double)$handling;
			$countrytaxable += (double)$handling;
		}
		if($totalgoods>0){
			$statetaxable += ((double)$totalgoods-((double)$totaldiscounts+(double)$statetaxfree));
			$countrytaxable += ((double)$totalgoods-((double)$totaldiscounts+(double)$countrytaxfree));
		}
		$countryTax = $countrytaxable*(double)$countryTaxRate/100.0;
		$stateTax = ($statetaxable+(double)$countryTax)*(double)$stateTaxRate/100.0;
	}else{
		if($totalgoods>0){
			$stateTax = ((double)$totalgoods-((double)$totaldiscounts+(double)$statetaxfree))*(double)$stateTaxRate/100.0;
			if(@$perproducttaxrate!=TRUE) $countryTax = ((double)$totalgoods-((double)$totaldiscounts+(double)$countrytaxfree))*(double)$countryTaxRate/100.0;
		//	if(@$showtaxinclusive==3){
		//		$countryTax = round(($totalgoods-($totaldiscounts+$countrytaxfree)) / ($countryTaxRate * (1+($countryTaxRate/100.0))),2);
		//		$totalgoods-=$countryTax;
		//	}
		}
		if(@$taxShipping==2 && ($shipping - $freeshipamnt > 0)){
			if(@$proratashippingtax==TRUE){
				if($totalgoods>0) $stateTax += (((double)$totalgoods-((double)$totaldiscounts+(double)$statetaxfree)) / $totalgoods) * (((double)$shipping-(double)$freeshipamnt)*(double)$stateTaxRate/100.0);
			}else
				$stateTax += (((double)$shipping-(double)$freeshipamnt)*(double)$stateTaxRate/100.0);
			$countryTax += (((double)$shipping-(double)$freeshipamnt)*(double)$countryTaxRate/100.0);
		}
		if(@$taxHandling==2){
			$stateTax += ((double)$handling*(double)$stateTaxRate/100.0);
			$countryTax += ((double)$handling*(double)$countryTaxRate/100.0);
		}
	}
	if($stateTax < 0) $stateTax = 0;
	if($countryTax < 0) $countryTax = 0;
	$stateTax = round($stateTax,(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
	$countryTax = round($countryTax,(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
	$handling = round($handling,(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
}
function do_stock_check($sublevels){
	global $stockManage,$quantity,$cartID,$outofstockarr,$WSP;
	$sameitemstock=array();
	$gotstock = TRUE;
	if($stockManage!=0){
		$sSQL = 'SELECT cartID,cartQuantity FROM cart WHERE cartCompleted=0 AND ' . getsessionsql() . ' ORDER BY cartDateAdded';
		$result3 = mysql_query($sSQL) or print(mysql_error());
		while($rs3 = mysql_fetch_assoc($result3)){
			$cartID = $rs3['cartID'];
			$thequant = $rs3['cartQuantity'];
			$pID = '';
			$sSQL='SELECT pInStock,pID,pStockByOpts,'.$WSP."pPrice FROM cart LEFT JOIN products ON cart.cartProdId=products.pID WHERE cartID='" . $cartID . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$pID = trim($rs['pID']);
				$pInStock = (int)$rs['pInStock'];
				$pStockByOpts = (int)$rs['pStockByOpts'];
				$pPrice = $rs['pPrice'];
			}
			mysql_free_result($result);
			if($pID!=''){
				if(($quantity = $thequant)==0){
					$gotstock=FALSE;
				}elseif((int)$pStockByOpts!=0){
					$sSQL = "SELECT coID,optStock,coOptID FROM cart INNER JOIN cartoptions ON cart.cartID=cartoptions.coCartID INNER JOIN options ON cartoptions.coOptID=options.optID INNER JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optType IN (-4,-2,-1,1,2,4) AND cartID='" . $cartID . "'";
					$result = mysql_query($sSQL) or print(mysql_error());
					while($rs = mysql_fetch_assoc($result)){
						$sameitems=0;
						foreach($sameitemstock as $samestockitem){
							if($samestockitem[0]==$rs['coOptID'] && $samestockitem[2]==TRUE) $sameitems+=$samestockitem[1];
						}
						$pInStock = (int)$rs['optStock'];
						if(($pInStock-$sameitems) < $quantity){
							$quantity = ($pInStock-$sameitems);
							if($quantity < 0) $quantity=0;
							if($sublevels) mysql_query('UPDATE cart SET cartQuantity='.$quantity.' WHERE (cartCompleted=0 OR cartCompleted=3) AND cartID='.$cartID) or print(mysql_error());
							$gotstock = FALSE;
							array_push($outofstockarr, array($rs['coID'], TRUE, $pID, $pPrice));
						}
						array_push($sameitemstock, array($rs['coOptID'], $thequant, TRUE));
					}
					mysql_free_result($result);
				}else{
					$sameitems=0;
					foreach($sameitemstock as $samestockitem){
						if($samestockitem[0]==$pID && $samestockitem[2]==FALSE) $sameitems+=$samestockitem[1];
					}
					if($pInStock < ($thequant+$sameitems)){
						$quantity = ($pInStock-$sameitems);
						if($quantity < 0) $quantity=0;
						if($sublevels) mysql_query('UPDATE cart SET cartQuantity='.$quantity.' WHERE (cartCompleted=0 OR cartCompleted=3) AND cartID='.$cartID) or print(mysql_error());
						$gotstock = FALSE;
						array_push($outofstockarr, array($cartID, FALSE, $pID, $pPrice));
					}
					array_push($sameitemstock, array($pID, $thequant, FALSE));
				}
			}
		}
	}
	if($sublevels){
		foreach($outofstockarr as $outofstockitem)
			checkpricebreaks($outofstockitem[2], $outofstockitem[3]);
	}
	return(! $gotstock);
}
function vrhmac2($key, $text){
	$idatastr = '                                                                ';
	$odatastr = '                                                                ';
	$hkey = (string)substr($key,0,64);
	$idatastr .= $text;
	for($i=0; $i<64; $i++){
		$idata[$i] = $ipad[$i] = 0x36;
		$odata[$i] = $opad[$i] = 0x5C;
	}
	for($i=0; $i< strlen($hkey); $i++){
		$ipad[$i] ^= ord($hkey{$i});
		$opad[$i] ^= ord($hkey{$i});
		$idata[$i] = ($ipad[$i] & 0xFF);
		$odata[$i] = ($opad[$i] & 0xFF);
	}
	for($i=0; $i< strlen($text); $i++)
		$idata[64+$i] = ord($text{$i}) & 0xFF;
	for($i=0; $i< strlen($idatastr); $i++)
		$idatastr{$i} = chr($idata[$i] & 0xFF);
	for($i=0; $i< strlen($odatastr); $i++)
		$odatastr{$i} = chr($odata[$i] & 0xFF);
	$innerhashout = md5($idatastr);
	for($i=0; $i<16; $i++)
		$odatastr .= chr(hexdec(substr($innerhashout,$i*2,2)));
	return md5($odatastr);
}
function checkdeletecart($thecartid){
	$sSQL = "SELECT cartID,cartListID,cartClientID,listOwner FROM cart LEFT JOIN customerlists ON cart.cartListID=customerlists.listID WHERE (cartCompleted=0 OR cartCompleted=3) AND cartID='".escape_string($thecartid)."' AND " . getsessionsql();
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs2 = mysql_fetch_assoc($result)){
		if(! is_null($rs2['listOwner'])) $listowner=(int)$rs2['listOwner']; else $listowner=0;
		if($rs2['cartListID']>0 && $listowner!=@$_SESSION['clientID'])
			mysql_query("UPDATE cart SET cartCompleted=3,cartOrderID=0,cartClientID=".$rs2['listOwner']." WHERE cartID='".escape_string($thecartid)."'") or print(mysql_error());
		else{
			mysql_query("DELETE FROM cart WHERE cartID='".escape_string($thecartid)."'") or print(mysql_error());
			mysql_query("DELETE FROM cartoptions WHERE coCartID='".escape_string($thecartid)."'") or print(mysql_error());
			mysql_query("DELETE FROM giftcertificate WHERE gcCartID='".escape_string($thecartid)."'") or print(mysql_error());
		}
	}elseif(@$_SESSION['clientID']!=''){
		mysql_free_result($result);
		$sSQL = "SELECT cartID FROM cart INNER JOIN customerlists ON cart.cartListID=customerlists.listID WHERE cartID='".escape_string($thecartid)."' AND listOwner=" . $_SESSION['clientID'];
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result)>0) mysql_query("UPDATE cart SET cartListID=0 WHERE cartID='".escape_string($thecartid)."'") or print(mysql_error());
	}
	mysql_free_result($result);
}
function createdynamicstates(){
	global $origCountry,$origCountryID,$numallstates,$allstates,$xxOutState; ?>
	function dynamiccountries(citem,stateid){
		var statemenu=document.getElementById(stateid);
		statemenu.disabled=false;
		var currcntry=citem[citem.selectedIndex].value;
		var currcntryid=0;
		if(currcntry=='United States of America') currcntryid=1;
		if(currcntry=='Canada') currcntryid=2;
		if(currcntry=='<?php print $origCountry?>') currcntryid=<?php print $origCountryID?>;
		if(currcntryid==1 || currcntryid==2 || currcntryid==<?php print $origCountryID?>){
			statemenu.options[0].value='';
			if(currcntryid==1) statemenu.options[0].text='Please select US State';
			if(currcntryid==2) statemenu.options[0].text='Please select CA Province';
			if(currcntryid==<?php print $origCountryID?>) statemenu.options[0].text='<?php print jsspecials($xxOutState)?>';
			for(var cntmind=0; cntmind<dynst[currcntryid].length; cntmind++){
				if(cntmind>=statemenu.length-1){
					statemenu.options[cntmind+1]=new Option(dynst[currcntryid][cntmind],dynab[currcntryid][cntmind],false,false);
				}else{
					statemenu.options[cntmind+1].value=dynab[currcntryid][cntmind];
					statemenu.options[cntmind+1].text=dynst[currcntryid][cntmind];
				}
			}
			statemenu.length=cntmind+1;
			stateselectordisabled[stateid=='sstate'?1:0]=false;
		}else{
			statemenu.disabled=true;
			stateselectordisabled[stateid=='sstate'?1:0]=true;
		}
		statemenu.selectedIndex=0;
	}
	function setinitialstate(){
		var initstate='<?php print jsspecials(@$_POST['state'])?>';
		var statemenu=document.getElementById("state");
		for(var cntmind=0; cntmind<statemenu.length; cntmind++){
			if(statemenu.options[cntmind].value==initstate){
				statemenu.selectedIndex=cntmind;
				break;
			}
		}
	}
	var stateselectordisabled=[];
	stateselectordisabled[0]=false;
	stateselectordisabled[1]=false;
	var dynst=[];var dynab=[];
	dynst[1]=new Array();dynst[2]=new Array();dynst[<?php print $origCountryID?>]=new Array();
	dynab[1]=new Array();dynab[2]=new Array();dynab[<?php print $origCountryID?>]=new Array();
	var savstates=[];var savstatab=[];
	dynst[1].push('Alabama');dynab[1].push('AL');
	dynst[1].push('Alaska');dynab[1].push('AK');
	dynst[1].push('American Samoa');dynab[1].push('AS');
	dynst[1].push('Arizona');dynab[1].push('AZ');
	dynst[1].push('Arkansas');dynab[1].push('AR');
	dynst[1].push('California');dynab[1].push('CA');
	dynst[1].push('Colorado');dynab[1].push('CO');
	dynst[1].push('Connecticut');dynab[1].push('CT');
	dynst[1].push('Delaware');dynab[1].push('DE');
	dynst[1].push('District Of Columbia');dynab[1].push('DC');
	dynst[1].push('Fdr. States Of Micronesia');dynab[1].push('FM');
	dynst[1].push('Florida');dynab[1].push('FL');
	dynst[1].push('Georgia');dynab[1].push('GA');
	dynst[1].push('Guam');dynab[1].push('GU');
	dynst[1].push('Hawaii');dynab[1].push('HI');
	dynst[1].push('Idaho');dynab[1].push('ID');
	dynst[1].push('Illinois');dynab[1].push('IL');
	dynst[1].push('Indiana');dynab[1].push('IN');
	dynst[1].push('Iowa');dynab[1].push('IA');
	dynst[1].push('Kansas');dynab[1].push('KS');
	dynst[1].push('Kentucky');dynab[1].push('KY');
	dynst[1].push('Louisiana');dynab[1].push('LA');
	dynst[1].push('Maine');dynab[1].push('ME');
	dynst[1].push('Marshall Islands');dynab[1].push('MH');
	dynst[1].push('Maryland');dynab[1].push('MD');
	dynst[1].push('Massachusetts');dynab[1].push('MA');
	dynst[1].push('Michigan');dynab[1].push('MI');
	dynst[1].push('Minnesota');dynab[1].push('MN');
	dynst[1].push('Mississippi');dynab[1].push('MS');
	dynst[1].push('Missouri');dynab[1].push('MO');
	dynst[1].push('Montana');dynab[1].push('MT');
	dynst[1].push('Nebraska');dynab[1].push('NE');
	dynst[1].push('Nevada');dynab[1].push('NV');
	dynst[1].push('New Hampshire');dynab[1].push('NH');
	dynst[1].push('New Jersey');dynab[1].push('NJ');
	dynst[1].push('New Mexico');dynab[1].push('NM');
	dynst[1].push('New York');dynab[1].push('NY');
	dynst[1].push('North Carolina');dynab[1].push('NC');
	dynst[1].push('North Dakota');dynab[1].push('ND');
	dynst[1].push('Northern Mariana Islands');dynab[1].push('MP');
	dynst[1].push('Ohio');dynab[1].push('OH');
	dynst[1].push('Oklahoma');dynab[1].push('OK');
	dynst[1].push('Oregon');dynab[1].push('OR');
	dynst[1].push('Palau');dynab[1].push('PW');
	dynst[1].push('Pennsylvania');dynab[1].push('PA');
	dynst[1].push('Puerto Rico');dynab[1].push('PR');
	dynst[1].push('Rhode Island');dynab[1].push('RI');
	dynst[1].push('South Carolina');dynab[1].push('SC');
	dynst[1].push('South Dakota');dynab[1].push('SD');
	dynst[1].push('Tennessee');dynab[1].push('TN');
	dynst[1].push('Texas');dynab[1].push('TX');
	dynst[1].push('Utah');dynab[1].push('UT');
	dynst[1].push('Vermont');dynab[1].push('VT');
	dynst[1].push('Virgin Islands');dynab[1].push('VI');
	dynst[1].push('Virginia');dynab[1].push('VA');
	dynst[1].push('Washington');dynab[1].push('WA');
	dynst[1].push('West Virginia');dynab[1].push('WV');
	dynst[1].push('Wisconsin');dynab[1].push('WI');
	dynst[1].push('Wyoming');dynab[1].push('WY');
	// Canadian Provinces
	dynst[2].push('Alberta');dynab[2].push('AB');
	dynst[2].push('British Columbia');dynab[2].push('BC');
	dynst[2].push('Manitoba');dynab[2].push('MB');
	dynst[2].push('New Brunswick');dynab[2].push('NB');
	dynst[2].push('Newfoundland');dynab[2].push('NF');
	dynst[2].push('North West Territories');dynab[2].push('NT');
	dynst[2].push('Nova Scotia');dynab[2].push('NS');
	dynst[2].push('Nunavut');dynab[2].push('NU');
	dynst[2].push('Ontario');dynab[2].push('ON');
	dynst[2].push('Prince Edward Island');dynab[2].push('PE');
	dynst[2].push('Quebec');dynab[2].push('QC');
	dynst[2].push('Saskatchewan');dynab[2].push('SK');
	dynst[2].push('Yukon Territory');dynab[2].push('YT');
<?php
	if($numallstates>0){
		for($index=0;$index<$numallstates;$index++){
			print 'dynst[' . $origCountryID . "].push('" . jsspecials($allstates[$index]['stateName']) . "');dynab[" . $origCountryID . "].push('" . jsspecials((@$usestateabbrev==TRUE?$allstates[$index]['stateAbbrev']:$allstates[$index]['stateName'])) . "');\r\n";
		}
	}
	print "dynamiccountries(document.getElementById('country'),'state');setinitialstate();\r\n";
}
if(@$_GET['token']!=''){ // PayPal Express
	if(getpayprovdetails(19,$username,$password,$data3,$demomode,$ppmethod)){
		$data2arr = explode('&',$password);
		$password=urldecode(@$data2arr[0]);
		$isthreetoken=(trim(urldecode(@$data2arr[2]))=='1');
		$signature=''; $sslcertpath='';
		if($isthreetoken) $signature=urldecode(@$data2arr[1]); else $sslcertpath=urldecode(@$data2arr[1]);
		if(strpos($username,'@AB@')!==FALSE){
			$isthreetoken=TRUE;
			$signature='AB';
		}
	}
	$sXML = ppsoapheader($username, $password, $signature) .
		'<soap:Body><GetExpressCheckoutDetailsReq xmlns="urn:ebay:api:PayPalAPI"><GetExpressCheckoutDetailsRequest><Version xmlns="urn:ebay:apis:eBLBaseComponents">60.00</Version>' .
		'  ' . addtag('Token',$_GET['token']) . 
		'</GetExpressCheckoutDetailsRequest></GetExpressCheckoutDetailsReq></soap:Body></soap:Envelope>';
	if($demomode) $sandbox = ".sandbox"; else $sandbox = '';
	if(callcurlfunction('https://api-aa' . ($isthreetoken ? '-3t' : '') . $sandbox . '.paypal.com/2.0/', $sXML, $res, $sslcertpath, $errormsg, FALSE)){
		$xmlDoc = new vrXMLDoc($res);
		$nodeList = $xmlDoc->nodeList->childNodes[0];
		$success=FALSE;
		$ordPhone = $ordEmail = $ordName = $ordLastName='';
		$countryid=0;
		$ordPayProvider = '19';
		$ordComLoc = 0;
		$gotaddress = FALSE;
		$token = $_GET['token'];
		if(abs(@$addshippinginsurance)==1) $ordComLoc += 2;
		for($i = 0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=='SOAP-ENV:Body'){
				$e = $nodeList->childNodes[$i];
				for($j = 0; $j < $e->length; $j++){
					if($e->nodeName[$j]=='GetExpressCheckoutDetailsResponse'){
						$ee = $e->childNodes[$j];
						for($jj = 0; $jj < $ee->length; $jj++){
							if($ee->nodeName[$jj]=='Ack'){
								if($ee->nodeValue[$jj]=='Success' || $ee->nodeValue[$jj]=='SuccessWithWarning')
									$success=TRUE;
							}elseif($ee->nodeName[$jj]=='GetExpressCheckoutDetailsResponseDetails'){
								$ff = $ee->childNodes[$jj];
								for($kk = 0; $kk < $ff->length; $kk++){
									if($ff->nodeName[$kk]=='PayerInfo'){
										$gg = $ff->childNodes[$kk];
										for($ll = 0; $ll < $gg->length; $ll++){
											if($gg->nodeName[$ll]=='Payer'){
												$ordEmail = $gg->nodeValue[$ll];
											}elseif($gg->nodeName[$ll]=='PayerID'){
												$payerid = $gg->nodeValue[$ll];
											}elseif($gg->nodeName[$ll]=='PayerStatus'){
												$ordCVV = 'U';
												$payer_status = strtolower($gg->nodeValue[$ll]);
												if($payer_status=='verified') $ordCVV = 'Y';
												elseif($payer_status=='unverified') $ordCVV = 'N';
											}elseif($gg->nodeName[$ll]=='PayerName'){
											}elseif($gg->nodeName[$ll]=='Address'){
												$hh = $gg->childNodes[$ll];
												for($mm = 0; $mm < $hh->length; $mm++){
													if($hh->nodeName[$mm]=='Name'){
														splitfirstlastname(trim($hh->nodeValue[$mm]),$ordName,$ordLastName);
													}elseif($hh->nodeName[$mm]=='Street1'){
														$ordAddress = $hh->nodeValue[$mm];
													}elseif($hh->nodeName[$mm]=='Street2'){
														$ordAddress2 = $hh->nodeValue[$mm];
													}elseif($hh->nodeName[$mm]=='CityName'){
														$ordCity = $hh->nodeValue[$mm];
													}elseif($hh->nodeName[$mm]=='StateOrProvince'){
														$ordState = $hh->nodeValue[$mm];
													}elseif($hh->nodeName[$mm]=='Country'){
														$tmpcntry = str_replace("'",'',$hh->nodeValue[$mm]);
														$sSQL = 'SELECT countryName,countryID,countryOrder FROM countries WHERE countryEnabled=1 AND ';
														if($tmpcntry=='GB')
															$sSQL .= 'countryID=201';
														elseif($tmpcntry=='FR')
															$sSQL .= 'countryID=65';
														elseif($tmpcntry=='PT')
															$sSQL .= 'countryID=153';
														elseif($tmpcntry=='ES')
															$sSQL .= 'countryID=175';
														else
															$sSQL .= "countryCode='" . escape_string($tmpcntry) . "'";
														$result = mysql_query($sSQL) or print(mysql_error());
														if($rs = mysql_fetch_assoc($result)){
															$ordCountry = $rs['countryName'];
															$countryid = $rs['countryID'];
															$homecountry = ($rs['countryOrder']>=2);
														}else{
															$errormsg = 'Purchasing from your country is not supported.';
															$checkoutmode='paypalcancel';
															$success=FALSE;
														}
														mysql_free_result($result);
													}elseif($hh->nodeName[$mm]=='PostalCode'){
														$ordZip = $hh->nodeValue[$mm];
													}elseif($hh->nodeName[$mm]=='AddressStatus'){
														$ordAVS = 'U';
														$address_status = strtolower($hh->nodeValue[$mm]);
														$gotaddress = ($address_status!='none');
														if($address_status=='confirmed') $ordAVS = 'Y';
														elseif($address_status=='unconfirmed') $ordAVS = 'N';
													}
												}
											}
										}
									}elseif($ff->nodeName[$kk]=='Custom'){
										$customarr = explode(':', $ff->nodeValue[$kk]);
										$thesessionid = $customarr[0];
										$ordAffiliate = $customarr[1];
										if(substr($thesessionid,0,3)=='cid'){
											$_SESSION['clientID'] = str_replace("'",'',substr($thesessionid,3));
											$sSQL = "SELECT clID,clUserName,clActions,clLoginLevel,clPercentDiscount FROM customerlogin WHERE clID='" . escape_string($_SESSION['clientID']) ."'";
											$result = mysql_query($sSQL) or print(mysql_error());
											if($rs = mysql_fetch_assoc($result)){
												$_SESSION['clientUser']=$rs['clUserName'];
												$_SESSION['clientActions']=$rs['clActions'];
												$_SESSION['clientLoginLevel']=$rs['clLoginLevel'];
												$_SESSION['clientPercentDiscount']=(100.0-(double)$rs['clPercentDiscount'])/100.0;
											}
										}else
											$thesessionid = str_replace("'",'',substr($thesessionid,3));
									}elseif($ff->nodeName[$kk]=='ContactPhone'){
										$ordPhone=$ff->nodeValue[$kk];
									}
								}
							}elseif($ee->nodeName[$jj]=="Errors"){
								$ff = $ee->childNodes[$jj];
								for($kk = 0; $kk < $ff->length; $kk++){
									if($ff->nodeName[$kk]=="ShortMessage"){
										$errormsg=$ff->nodeValue[$kk].'<br>'.$errormsg;
									}elseif($ff->nodeName[$kk]=="LongMessage"){
										$errormsg.=$ff->nodeValue[$kk];
									}elseif($ff->nodeName[$kk]=="ErrorCode"){
										$errcode=$ff->nodeValue[$kk];
									}
								}
							}
						}
					}
				}
			}
		}
		if(! $gotaddress){
			if(ob_get_length()!==FALSE)
				header('Location: ' . $storeurl . 'cart.php');
			else
				print '<meta http-equiv="Refresh" content="0; URL=' . $storeurl . 'cart.php">';
			$cartisincluded=TRUE;
		}elseif($success){
			$paypalexpress=TRUE;
			if(($countryid==1 || $countryid==2) && $homecountry && @$usestateabbrev!=TRUE){
				$sSQL = "SELECT stateName FROM states WHERE stateAbbrev='" . escape_string($ordState) . "'";
				$result = mysql_query($sSQL) or print(mysql_error());
				if($rs = mysql_fetch_assoc($result))
					$ordState=$rs['stateName'];
				mysql_free_result($result);
			}
		}else{
			print 'PayPal Payment Pro error: ' . $errormsg;
		}
	}else{
		print 'PayPal Payment Pro error: ' . $errormsg;
	}
}elseif($checkoutmode=='paypalexpress1'){
	if(getpayprovdetails(19,$username,$password,$data3,$demomode,$ppmethod)){
		$data2arr = explode('&',$password);
		$password=urldecode(@$data2arr[0]);
		$isthreetoken=(trim(urldecode(@$data2arr[2]))=='1');
		$signature=''; $sslcertpath='';
		if($isthreetoken) $signature=urldecode(@$data2arr[1]); else $sslcertpath=urldecode(@$data2arr[1]);
		if(strpos($username,'@AB@')!==FALSE){
			$isthreetoken=TRUE;
			$signature='AB';
		}
	}
	if($demomode) $sandbox = '.sandbox'; else $sandbox = '';
	if(@$pathtossl!=''){
		if(substr($pathtossl,-1)!='/') $storeurl = $pathtossl . '/'; else $storeurl = $pathtossl;
	}
	$theestimate = round((double)$_POST['estimate'],2);
	$sXML = ppsoapheader($username, $password, $signature) .
		'<soap:Body><SetExpressCheckoutReq xmlns="urn:ebay:api:PayPalAPI"><SetExpressCheckoutRequest><Version xmlns="urn:ebay:apis:eBLBaseComponents">72.00</Version>' .
		'<SetExpressCheckoutRequestDetails xmlns="urn:ebay:apis:eBLBaseComponents">' .
		'<OrderTotal currencyID="' . $countryCurrency . '">' . $theestimate . '</OrderTotal>' .
		'<ReturnURL>' . $storeurl . 'cart.php</ReturnURL><CancelURL>' . $storeurl . 'cart.php</CancelURL>' .
		'<Custom>' . (@$_SESSION['clientID']!='' ? 'cid' . $_SESSION['clientID'] : 'sid' . $thesessionid) . ':' . strip_tags(trim(@$_POST['PARTNER'])) . '</Custom>' .
		addtag('PaymentAction',$ppmethod==1?'Authorization':'Sale');
	$itemtotal=0;
	$sXML .= '<PaymentDetails>';
	$sSQL = "SELECT cartID,cartProdID,cartProdName,cartProdPrice,cartQuantity,pShipping,pShipping2,pExemptions,pTax,pDescription FROM cart LEFT JOIN products ON cart.cartProdID=products.pId WHERE cartCompleted=0 AND " . getsessionsql();
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs = mysql_fetch_assoc($result)){
		$itemtotal+=$rs['cartProdPrice']*$rs['cartQuantity'];
		$description=''; $addcomma='';
		$optiontotal=0;
		$sSQL = "SELECT coOptGroup,coCartOption,coPriceDiff FROM cartoptions WHERE coCartID=" . $rs['cartID'];
		$result2 = mysql_query($sSQL) or print(mysql_error());
		while($rs2 = mysql_fetch_assoc($result2)){
			$optiontotal+=$rs2['coPriceDiff'];
			$description.=$addcomma . vrxmlencode($rs2['coOptGroup']) . ' : ' . vrxmlencode($rs2['coCartOption']);
			$addcomma=', ';
		}
		mysql_free_result($result2);
		$itemtotal+=$optiontotal*$rs['cartQuantity'];
		$sXML .= '<PaymentDetailsItem><Number>' . vrxmlencode($rs['cartProdID']) . '</Number><Name>' . vrxmlencode($rs['cartProdName']) . '</Name><Description>' . substr($description,0,122) . (strlen($description)>122?'...':'') . '</Description><Amount currencyID="' . $countryCurrency . '">' . ($rs['cartProdPrice']+$optiontotal) . '</Amount><Quantity>' . $rs['cartQuantity'] . '</Quantity></PaymentDetailsItem>';
	}
	mysql_free_result($result);
	if($itemtotal!=$theestimate){
		if(@$xxPPEst1=='') $xxPPEst1='Estimate of Shipping, taxes, discounts, etc';
		if(@$xxPPEst2=='') $xxPPEst2='This value is an estimation which will be calculated on return to the cart.';
		$sXML .= '<PaymentDetailsItem><Name>' . vrxmlencode($xxPPEst1) . '</Name><Description>' . vrxmlencode($xxPPEst2) . '</Description><Amount currencyID="' . $countryCurrency . '">' . round($theestimate-$itemtotal, 2) . '</Amount><Quantity>1</Quantity></PaymentDetailsItem>';
	}
	$sXML .= '</PaymentDetails>';
	if(@$paypallc!='') $sXML .= addtag('LocaleCode',$paypallc);
	$sXML .= '  </SetExpressCheckoutRequestDetails>' .
		'</SetExpressCheckoutRequest></SetExpressCheckoutReq></soap:Body></soap:Envelope>';
	if($username==''){
		print '<meta http-equiv="Refresh" content="0; URL=http://altfarm.mediaplex.com/ad/ck/3484-23890-3840-61">';
		print '<p align="center">' . $xxAutFo . '</p>';
		print '<p align="center">' . $xxForAut . ' <a class="ectlink" href="http://altfarm.mediaplex.com/ad/ck/3484-23890-3840-61">' . $xxClkHere . '</a></p>';
	}elseif(callcurlfunction('https://api-aa' . ($isthreetoken ? '-3t' : '') . $sandbox . '.paypal.com/2.0/', $sXML, $res, $sslcertpath, $errormsg, FALSE)){
		$xmlDoc = new vrXMLDoc($res);
		$nodeList = $xmlDoc->nodeList->childNodes[0];
		$success=FALSE;
		$token='';
		for($i = 0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=="SOAP-ENV:Body"){
				$e = $nodeList->childNodes[$i];
				for($j = 0; $j < $e->length; $j++){
					if($e->nodeName[$j]=="SetExpressCheckoutResponse"){
						$ee = $e->childNodes[$j];
						for($jj = 0; $jj < $ee->length; $jj++){
							if($ee->nodeName[$jj]=='Ack'){
								if($ee->nodeValue[$jj]=='Success' || $ee->nodeValue[$jj]=='SuccessWithWarning')
									$success=TRUE;
							}elseif($ee->nodeName[$jj]=="Token"){
								$token=$ee->nodeValue[$jj];
							}elseif($ee->nodeName[$jj]=="Errors"){
								$ff = $ee->childNodes[$jj];
								for($kk = 0; $kk < $ff->length; $kk++){
									if($ff->nodeName[$kk]=="ShortMessage"){
										$errormsg=$ff->nodeValue[$kk].'<br />'.$errormsg;
									}elseif($ff->nodeName[$kk]=="LongMessage"){
										$errormsg.=$ff->nodeValue[$kk];
									}elseif($ff->nodeName[$kk]=="ErrorCode"){
										$errcode=$ff->nodeValue[$kk];
									}
								}
							}
						}
					}
				}
			}
		}
		if($success){
			if(ob_get_length()===FALSE){
				print '<meta http-equiv="Refresh" content="0; URL=https://www'.$sandbox.'.paypal.com/webscr?cmd=_express-checkout&token=' . $token . '">';
			}else{
				header('Location: https://www'.$sandbox.'.paypal.com/webscr?cmd=_express-checkout&token=' . $token);
			}
			print '<p align="center">' . $xxAutFo . '</p>';
			print '<p align="center">' . $xxForAut . ' <a class="ectlink" href="https://www'.$sandbox.'.paypal.com/webscr?cmd=_express-checkout&token=' . $token . '">' . $xxClkHere . '</a></p>';
		}else{
			print "PayPal Payment Pro error: " . $errormsg;
		}
	}else{
		print "PayPal Payment Pro error: " . $errormsg;
	}
}elseif($checkoutmode=='update' || $checkoutmode=='savecart' || $checkoutmode=='movetocart'){
	$_SESSION['xsshipping']=NULL; unset($_SESSION['xsshipping']);
	$_SESSION['discounts']=NULL; unset($_SESSION['discounts']);
	$_SESSION['xscountrytax']=NULL; unset($_SESSION['xscountrytax']);
	$sSQL = "SELECT ordID FROM orders WHERE ordStatus>1 AND ordAuthNumber='' AND " . getordersessionsql();
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		release_stock($rs['ordID']);
		mysql_query("UPDATE cart SET cartSessionID='".escape_string(getsessionid())."',cartClientID='" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "' WHERE cartCompleted=0 AND cartOrderID=" . $rs['ordID']) or print(mysql_error());
		mysql_query("UPDATE orders SET ordAuthStatus='MODWARNOPEN',ordShipType='MODWARNOPEN' WHERE ordID=" . $rs['ordID']) or print(mysql_error());
	}
	mysql_free_result($result);
	$listid='';
	if($checkoutmode=='savecart' && @$_POST['listid']!='' && is_numeric(@$_POST['listid']) && @$_SESSION['clientID']!=''){
		$result = mysql_query("SELECT listID FROM customerlists WHERE listID='".escape_string($_POST['listid'])."' AND listOwner='" . escape_string($_SESSION['clientID']) . "'") or print(mysql_error());
		if($rs=mysql_fetch_assoc($result)) $listid = $rs['listID'];
		mysql_free_result($result);
	}
	foreach(@$_POST as $objItem => $objValue){
		if(substr($objItem,0,5)=='quant' || substr($objItem,0,5)=='delet'){
			$thecartid = (int)substr($objItem, 5);
			$pPrice = 0;
			$pID = '';
			$sSQL='SELECT cartProdID,'.$WSP."pPrice FROM cart INNER JOIN products ON cart.cartProdId=products.pID WHERE cartID='" . $thecartid . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$pID = trim($rs['cartProdID']);
				$pPrice = $rs['pPrice'];
			}
			mysql_free_result($result);
			if($checkoutmode=='movetocart'){
				if(substr($objItem,0,5)=='delet'){
					$sSQL = "UPDATE cart SET cartCompleted=0,cartListID=0,cartDateAdded='" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "' WHERE cartCompleted=3 AND cartID='".escape_string($thecartid)."' AND " . getsessionsql();
					if(@$_GET['pli']!='' && is_numeric(@$_GET['pli']) && @$_GET['pla']!=''){
						$sSQL = "SELECT listID FROM customerlists WHERE listID='".escape_string(@$_GET['pli'])."' AND listAccess='".escape_string(@$_GET['pla'])."'";
						$result = mysql_query($sSQL) or print(mysql_error());
						if($rs = mysql_fetch_assoc($result)) $sSQL = "UPDATE cart SET cartCompleted=0,cartDateAdded='" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "',cartSessionID='".escape_string(getsessionid())."',cartClientID='" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "' WHERE cartCompleted=3 AND cartID='".escape_string($thecartid)."' AND cartListID=" . $rs['listID']; else $sSQL='';
						mysql_free_result($result);
					}
					if($sSQL!='') mysql_query($sSQL) or print(mysql_error());
				}
			}elseif($checkoutmode=='savecart'){
				if(substr($objItem,0,5)=='delet')
					mysql_query("UPDATE cart SET cartOrderID=0,cartCompleted=3,cartListID='".escape_string($listid!=''?$listid:'0')."',cartDateAdded='" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "' WHERE (cartCompleted=0 OR cartCompleted=3) AND cartID='".escape_string($thecartid)."' AND " . getsessionsql());
			}else{
				if(substr($objItem,0,5)=='quant'){
					if((int)$objValue==0){
						checkdeletecart($thecartid);
					}else{
						$thequant=abs((int)$objValue);
						if($thequant>99999)$thequant=99999;
						if($pID!=''){
							mysql_query('UPDATE cart SET cartQuantity=' . $thequant . ",cartDateAdded='" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "' WHERE cartQuantity<>".abs((int)$objValue)." AND (cartCompleted=0 OR cartCompleted=3) AND cartID='" . $thecartid . "'") or print(mysql_error());
						}
					}
				}elseif(substr($objItem,0,5)=='delet'){
					checkdeletecart(substr($objItem, 5));
				}
			}
			if($pID!=$giftcertificateid && $pID!=$donationid) checkpricebreaks($pID,$pPrice);
		}
	}
}
function additemtocart($ainame,$aiprice){
	global $thesessionid,$theid,$dateadjust,$quantity;
	$cartListID=0;
	$cartCompleted=0;
	if(@$_POST['listid']==='0' && @$_SESSION['clientID']!=''){
		$cartCompleted=3;
	}elseif(@$_POST['listid']!='' && is_numeric(@$_POST['listid']) && @$_SESSION['clientID']!=''){
		$sSQL="SELECT listID FROM customerlists WHERE listOwner='".escape_string($_SESSION['clientID'])."' AND listID='".escape_string($_POST['listid'])."'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)){ $cartListID=$rs['listID']; $cartCompleted=3; }
		mysql_free_result($result);
	}
	$sSQL = 'INSERT INTO cart (cartSessionID,cartClientID,cartProdID,cartQuantity,cartCompleted,cartProdName,cartProdPrice,cartOrderID,cartDateAdded,cartListID) VALUES (';
	$sSQL .= "'" . escape_string($thesessionid) . "','" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "','" . $theid . "',";
	$sSQL .= $quantity . ",".$cartCompleted.",'" . escape_string(strip_tags($ainame)) . "','" . $aiprice . "',0,";
	$sSQL .= "'" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "',".$cartListID.")";
	mysql_query($sSQL) or print(mysql_error());
	return(mysql_insert_id());
}
function addoption($opttoadd){
	global $addalternateoptions,$theid,$origid,$cartID,$OWSP,$totalquantity,$thepname,$thepprice,$thepweight;
	$optvalue=trim(@$_POST[$opttoadd]);
	if(! is_numeric($optvalue)) $optvalue='';
	if((substr($opttoadd,0,4)=='optn' || substr($opttoadd,0,4)=='optm') && $optvalue!=''){
		if(substr($opttoadd,0,4)=='optm'){
			$optID = substr($opttoadd, 4);
			$quantity=$optvalue;
			if(is_numeric($optID) && is_numeric($quantity)){
				if($quantity > 0){
					$totalquantity += $quantity;
					if($theid==$origid || @$addalternateoptions){
						$sSQL="SELECT optID,".getlangid('optGrpName',16).','.getlangid('optName',32).',' . $OWSP . "optPriceDiff,optWeightDiff,optType,optFlags,optRegExp FROM options LEFT JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optID='" . escape_string($optID) . "'";
						$result = mysql_query($sSQL) or print(mysql_error());
						if($rs = mysql_fetch_assoc($result)){
							$sSQL = 'INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff) VALUES (' . $cartID . ',' . $rs['optID'] . ",'" . escape_string($rs[getlangid('optGrpName',16)]) . "','" . escape_string($rs[getlangid('optName',32)]) . "',";
							if(($rs['optFlags']&1)==0) $sSQL .= (trim($rs['optRegExp'])!=''?0:$rs['optPriceDiff']) . ','; else $sSQL .= round(($rs['optPriceDiff'] * $thepprice)/100.0, 2) . ',';
							if(($rs['optFlags']&2)==0) $sSQL .= $rs['optWeightDiff'] . ')'; else $sSQL .= multShipWeight($thepweight,$rs['optWeightDiff']) . ')';
							mysql_query($sSQL) or print(mysql_error());
						}
						mysql_free_result($result);
					}
					checkpricebreaks($theid,$thepprice);
				}
			}
		}elseif(trim(@$_POST['v' . $opttoadd])==''){
			$sSQL='SELECT optID,'.getlangid('optGrpName',16).','.getlangid('optName',32).',' . $OWSP . "optPriceDiff,optWeightDiff,optType,optFlags,optRegExp FROM options LEFT JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optID='" . escape_string($optvalue) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				if(@$addalternateoptions!=TRUE && trim($rs['optRegExp'])!='' && substr($rs['optRegExp'], 0, 1)!='!'){
					// Do nothing
				}elseif(abs($rs['optType'])!=3){
					$sSQL = 'INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff) VALUES (' . $cartID . ',' . $rs['optID'] . ",'" . escape_string($rs[getlangid('optGrpName',16)]) . "','" . escape_string($rs[getlangid('optName',32)]) . "',";
					if(($rs['optFlags']&1)==0) $sSQL .= (@$addalternateoptions==TRUE && trim($rs['optRegExp']) ? 0 : $rs['optPriceDiff']) . ','; else $sSQL .= round(($rs['optPriceDiff'] * $thepprice)/100.0, 2) . ',';
					if(($rs['optFlags']&2)==0) $sSQL .= $rs['optWeightDiff'] . ')'; else $sSQL .= multShipWeight($thepweight,$rs['optWeightDiff']) . ')';
				}else
					$sSQL = 'INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff) VALUES (' . $cartID . ',' . $rs['optID'] . ",'" . escape_string($rs[getlangid('optGrpName',16)]) . "','',0,0)";
				mysql_query($sSQL) or print(mysql_error());
			}
			mysql_free_result($result);
		}else{
			$sSQL='SELECT optID,'.getlangid('optGrpName',16).','.getlangid('optName',32).",optTxtCharge,optMultiply,optAcceptChars FROM options LEFT JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optID='" . escape_string($optvalue) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$theopttoadd=unstripslashes(@$_POST['v' . $opttoadd]);
				$optPriceDiff=($rs['optTxtCharge']<0&&$theopttoadd!=''?abs($rs['optTxtCharge']):$rs['optTxtCharge']*strlen($theopttoadd));
				$optmultiply=0;
				if($rs['optMultiply']!=0){
					if(is_numeric($theopttoadd)) $optmultiply=(double)$theopttoadd; else $theopttoadd='#NAN';
				}
				$sSQL = 'INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff,coMultiply) VALUES (' . $cartID . ',' . $rs['optID'] . ",'" . escape_string($rs[getlangid('optGrpName',16)]) . "','" . escape_string($theopttoadd) . "',".$optPriceDiff.',0,' . $rs['optMultiply'] . ')';
				mysql_query($sSQL) or print(mysql_error());
			}
			mysql_free_result($result);
		}
	}
}
function addproduct($theid){
	global $thepname,$thepprice,$thepweight,$cartID,$objItem,$optarray,$numoptions,$WSP,$stockManage,$addedprods,$numaddedprods,$quantity;
	$idexists=TRUE;
	$sSQL = 'SELECT '.getlangid('pName',1).','.$WSP.'pPrice,pWeight FROM products WHERE '.($stockManage==0?'pSell<>0 AND':'')." pID='" . $theid . "'";
	$result2 = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result2)){
		$thepname=$rs[getlangid('pName',1)];
		$thepprice=round($rs['pPrice'],2);
		$thepweight=$rs['pWeight'];
	}else{
		$idexists=FALSE;
		$thepname="Product ID Error";
		$thepprice=0;
		$thepweight=0;
	}
	mysql_free_result($result2);
	$addedprods[$numaddedprods][0]=$theid;
	$addedprods[$numaddedprods][1]=$thepname;
	$addedprods[$numaddedprods][2]=$quantity;
	$addedprods[$numaddedprods][3]=$thepprice;
	$addedprods[$numaddedprods][4]=$idexists;
	$numaddedprods++;
	if($idexists){
		$cartID = additemtocart($thepname,$thepprice);
		for($index=0; $index<$numoptions; $index++){
			if($optarray[$index]=='multioption')
				addoption($objItem);
			else
				addoption($optarray[$index]);
		}
	}
}
if($checkoutmode=='add'){
	$optarray='';
	$addedprods='';
	$errid = '';
	$thesessionid = getsessionid();
	if(@$_SESSION['clientID']!='' && trim(@$_POST['listid'])!='' && is_numeric(@$_POST['listid'])) $listid = trim(@$_POST['listid']); else $listid='';
	$_SESSION['xsshipping']=NULL; unset($_SESSION['xsshipping']);
	$_SESSION['discounts']=NULL; unset($_SESSION['discounts']);
	$_SESSION['xscountrytax']=NULL; unset($_SESSION['xscountrytax']);
	$sSQL = "SELECT ordID FROM orders WHERE ordStatus>1 AND ordAuthNumber='' AND " . getordersessionsql();
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		release_stock($rs['ordID']);
		mysql_query("UPDATE cart SET cartSessionID='".escape_string(getsessionid())."',cartClientID='" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "' WHERE cartCompleted=0 AND cartOrderID=" . $rs['ordID']) or print(mysql_error());
		mysql_query("UPDATE orders SET ordAuthStatus='MODWARNOPEN',ordShipType='MODWARNOPEN' WHERE ordID=" . $rs['ordID']) or print(mysql_error());
	}
	mysql_free_result($result);
	if(@$_POST['quant']=='' || ! is_numeric(@$_POST['quant'])) $quantity=1; else $quantity=abs((int)@$_POST['quant']);
	if($quantity>99999)$quantity=99999;
	$hasmultioption=FALSE;
	$origquantity = $quantity;
	$altids='';
	$numoptions=0;
	$numaddedprods=0;
	foreach(@$_POST as $objItem => $objValue){ // Check if the product id is modified
		if(substr($objItem,0,4)=='optn' && is_numeric($objValue)){
			$doaddoption=FALSE;
			$sSQL="SELECT optRegExp FROM options WHERE optID='" . escape_string($objValue) . "'";
			$result2 = mysql_query($sSQL) or print(mysql_error());
			if($rs=mysql_fetch_assoc($result2)) $theexp = trim($rs['optRegExp']); else $theexp='';
			if($theexp!='' && substr($theexp, 0, 1)!='!'){
				$theexp = str_replace('%s', $theid, $theexp);
				$altids.=':'.$objValue.':';
				if(strpos($theexp,' ')!==FALSE){ // Search and replace
					$exparr = explode(' ', $theexp, 2);
					$theid = str_replace($exparr[0], $exparr[1], $theid);
				}else
					$theid = $theexp;
				if(@$addalternateoptions==TRUE) $doaddoption=TRUE;
			}else
				$doaddoption=TRUE;
			if($doaddoption){
				$optarray[$numoptions]=$objItem;
				$numoptions++;
			}
			mysql_free_result($result2);
		}elseif(substr($objItem,0,4)=='optm' && is_numeric($objValue)){
			if(! $hasmultioption){
				$optarray[$numoptions]='multioption';
				$numoptions++;
			}
			$hasmultioption=TRUE;
		}
	}
	if($hasmultioption){
		$origid = $theid;
		foreach(@$_POST as $objItem => $objValue){
			if(substr($objItem,0,4)=='optm' && is_numeric($objValue)){
				$quantity = abs((int)$objValue);
				if($quantity>99999)$quantity=99999;
				$theid=$origid;
				$sSQL="SELECT optRegExp FROM options WHERE optID='" . escape_string(substr($objItem, 4)) . "'";
				$result2 = mysql_query($sSQL) or print(mysql_error());
				if($rs=mysql_fetch_assoc($result2)) $theexp = trim($rs['optRegExp']); else $theexp='';
				if($theexp!='' && substr($theexp, 0, 1)!='!'){
					$theexp = str_replace('%s', $theid, $theexp);
					if(strpos($theexp, ' ') !== FALSE){ // Search and replace
						$exparr = explode(' ', $theexp, 2);
						$theid = str_replace($exparr[0], $exparr[1], $theid);
					}else
						$theid = $theexp;
				}
				mysql_free_result($result2);
				addproduct($theid);
			}
		}
	}else
		addproduct($theid);
	// Check duplicates
	$sSQL = "SELECT cartID,cartProdID,cartQuantity FROM cart WHERE cartCompleted=".($listid==''?0:3)." AND " . getsessionsql() . ($listid==''?'':" AND cartListID='".$listid."'") . ' ORDER BY cartID';
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs = mysql_fetch_assoc($result)){
		$hasoptions=FALSE;
		$thecartid='';
		$sSQL = "SELECT cartID,cartQuantity FROM cart WHERE cartID>" . $rs['cartID'] . " AND cartCompleted=0 AND " . getsessionsql() . ($listid==''?'':" AND cartListID='".$listid."'") . " AND cartProdID='" . escape_string($rs['cartProdID']) . "'";
		$result2 = mysql_query($sSQL) or print(mysql_error());
		while($rs2 = mysql_fetch_assoc($result2)){
			$thecartid=$rs2['cartID'];
			$thequant=$rs2['cartQuantity'];
			$hasoptions=TRUE;
			if($thecartid!=''){ // check options
				$optarr1cnt=0; $optarr2cnt=0;
				$sSQL = "SELECT coOptID,coCartOption FROM cartoptions WHERE coCartID=" . $rs['cartID'];
				$result3 = mysql_query($sSQL) or print(mysql_error());
				while($rs3 = mysql_fetch_assoc($result3))
					$optarr1[$optarr1cnt++]=$rs3;
				mysql_free_result($result3);
				$sSQL = "SELECT coOptID,coCartOption FROM cartoptions WHERE coCartID=" . $thecartid;
				$result3 = mysql_query($sSQL) or print(mysql_error());
				while($rs3 = mysql_fetch_assoc($result3))
					$optarr2[$optarr2cnt++]=$rs3;
				mysql_free_result($result3);
				if($optarr1cnt!=$optarr2cnt) $hasoptions=FALSE;
				if($optarr1cnt > 0 && $optarr2cnt > 0){
					if($hasoptions){
						for($index2=0; $index2 < $optarr1cnt; $index2++){
							$hasthisoption=FALSE;
							for($index3=0; $index3 < $optarr2cnt; $index3++){
								if($optarr1[$index2]['coOptID']==$optarr2[$index3]['coOptID'] && $optarr1[$index2]['coCartOption']==$optarr2[$index3]['coCartOption']) $hasthisoption=TRUE;
							}
							if(! $hasthisoption) $hasoptions=FALSE;
						}
					}
				}
			}
			if($hasoptions) break;
		}
		mysql_free_result($result2);
		if($thecartid!='' && $hasoptions){
			mysql_query("DELETE FROM cartoptions WHERE coCartID=".$thecartid) or print(mysql_error());
			mysql_query("DELETE FROM cart WHERE cartID=".$thecartid) or print(mysql_error());
			mysql_query("UPDATE cart SET cartQuantity=cartQuantity+".$thequant." WHERE cartID=".$rs['cartID']) or print(mysql_error());
		}
	}
	mysql_free_result($result);
	for($index=0; $index<$numaddedprods; $index++){
		if($addedprods[$index][4]) checkpricebreaks($theid,$thepprice); else{ $actionaftercart=0; $cartrefreshseconds=3; }
	} ?>
      <table border="0" cellspacing="3" cellpadding="3" width="100%" align="center">
		<tr> 
		  <td width="100%" align="center"><p>&nbsp;</p>
<?php
	if(! $stockwarning) $stockwarning = do_stock_check(FALSE);
	if($stockwarning) $actionaftercart=4;
	if(! @isset($cartrefreshseconds)) $cartrefreshseconds=3;
	if($listid!='') $listidurl='&mode=sc' . ($listid!='0'?'&lid='.$listid:''); else $listidurl='';
	if($thefrompage!='' && @$actionaftercart==3){
		if($cartrefreshseconds==0 && ob_get_length()!==FALSE)
			header('Location: ' . $thefrompage);
		else
			print '<meta http-equiv="Refresh" content="'.$cartrefreshseconds.'; URL=' . $thefrompage . '">';
	}elseif(@$actionaftercart==4 || $cartrefreshseconds==0){
		$urllink = '?rp='.urlencode($thefrompage);
		if($listid!='' && @$_SESSION['clientID']!='')
			$urllink .= $listidurl;
		elseif($stockwarning)
			$urllink .= '&mode=add';
		if(ob_get_length()===FALSE) print '<meta http-equiv="Refresh" content="0; URL=cart.php'.$urllink.'">'; else header('Location: '.$storeurl.'cart.php'.$urllink);
	}else
		print '<meta http-equiv="Refresh" content="'.$cartrefreshseconds.'; URL=cart.php?rp='.urlencode($thefrompage).$listidurl.'">';
	print '<table border="0" cellspacing="4" cellpadding="4">';
	if($stockwarning) print '<tr><td align="center" colspan="2">' . $xxInsMul . '</td></tr>';
	for($index=0; $index<$numaddedprods; $index++){
		print '<tr><td align="right">' . ($addedprods[$index][4]?$addedprods[$index][2]:'X') . '&nbsp;</td><td align="left"><span style="font-weight:bold">' . ($addedprods[$index][4] ? $addedprods[$index][1] . '</span> ' . $xxAddOrd : '<span style="color:#FF0000">The product id <span style="color:#000000">' . htmlspecials($addedprods[$index][0]) . '</span> does not exist in the product database.</span></span>') . '</td></tr>';
	}
	print '</table>';
	print '<p>' . $xxPlsWait . ' <a class="ectlink" href="';
	if($thefrompage!='' && @$actionaftercart==3) print $thefrompage; else print 'cart.php?rp='.urlencode($thefrompage).$listidurl;
	print '"><strong>' . $xxClkHere . '</strong></a>.</p>';
?>
				<p>&nbsp;</p><p>&nbsp;</p>
		  </td>
		</tr>
      </table>
<?php
}elseif($checkoutmode=='checkout'){
	$sSQL = "SELECT ordID FROM orders WHERE ordStatus>1 AND ordAuthNumber='' AND " . getordersessionsql();
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		release_stock($rs['ordID']);
		mysql_query("UPDATE cart SET cartSessionID='".escape_string($thesessionid)."',cartClientID='" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "' WHERE cartCompleted=0 AND cartOrderID=" . $rs['ordID']) or print(mysql_error());
		mysql_query("UPDATE orders SET ordAuthStatus='MODWARNOPEN',ordShipType='MODWARNOPEN' WHERE ordID=" . $rs['ordID']) or print(mysql_error());
	}
	mysql_free_result($result);
	$remember=FALSE;
	$havestate=FALSE;
	if(@$_POST['checktmplogin']!=''){
		$sSQL = "SELECT tmploginname FROM tmplogin WHERE tmploginid='" . escape_string(@$_POST['sessionid']) . "' AND tmploginchk='" . escape_string(@$_POST['checktmplogin']) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)){
			$_SESSION['clientID']=$rs['tmploginname'];
			mysql_free_result($result);
			$sSQL = "SELECT clUserName,clActions,clLoginLevel,clPercentDiscount,clEmail,clPW FROM customerlogin WHERE clID='" . escape_string($_SESSION['clientID']) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$_SESSION['clientUser']=$rs['clUserName'];
				$_SESSION['clientActions']=$rs['clActions'];
				$_SESSION['clientLoginLevel']=$rs['clLoginLevel'];
				$_SESSION['clientPercentDiscount']=(100.0-(double)$rs['clPercentDiscount'])/100.0;
				get_wholesaleprice_sql();
				if($rs['clEmail']!=@$_COOKIE['WRITECLL'] || $rs['clPW']!=@$_COOKIE['WRITECLP']) print '<script src="vsadmin/savecookie.php?WRITECLL=' . $rs['clEmail'] . '&WRITECLP=' . $rs['clPW'] . '"></script>';
			}
		}
		mysql_free_result($result);
	}else{
		$_SESSION['clientID']=NULL; unset($_SESSION['clientID']); $_SESSION['clientUser']=NULL; unset($_SESSION['clientUser']); $_SESSION['clientActions']=NULL; unset($_SESSION['clientActions']); $_SESSION['clientLoginLevel']=NULL; unset($_SESSION['clientLoginLevel']); $_SESSION['clientPercentDiscount']=NULL; unset($_SESSION['clientPercentDiscount']);
	}
	if(@$_COOKIE['id1']!='' && @$_COOKIE['id2']!=''){
		retrieveorderdetails($_COOKIE['id1'], $_COOKIE['id2']);
		$remember=TRUE;
	}
	if(@$ordZip=='') $ordZip=@$_SESSION['zip'];
	if(@$ordState=='') $ordState=@$_SESSION['state'];
	if(@$ordCountry=='') $ordCountry=@$_SESSION['country'];
	$sSQL = "SELECT stateName,stateAbbrev FROM states WHERE stateEnabled=1 ORDER BY stateName";
	$result = mysql_query($sSQL) or print(mysql_error());
	$numallstates=0;
	$numallcountries=0;
	while($rs = mysql_fetch_assoc($result))
		$allstates[$numallstates++]=$rs;
	mysql_free_result($result);
	$numhomecountries = 0;
	$nonhomecountries = 0;
	$sSQL = "SELECT countryName,countryOrder,".getlangid("countryName",8)." FROM countries WHERE countryEnabled=1 ORDER BY countryOrder DESC," . getlangid("countryName",8);
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs = mysql_fetch_array($result)){
		$allcountries[$numallcountries++]=$rs;
		if($rs['countryOrder']>=2)$numhomecountries++;else $nonhomecountries++;
	}
	mysql_free_result($result);
	$addresses='';
	$numaddresses=0;
	if((@$enableclientlogin==TRUE || @$forceclientlogin==TRUE) && @$_SESSION['clientID']!=''){
		$sSQL = "SELECT addID,addIsDefault,addName,addLastName,addAddress,addAddress2,addState,addCity,addZip,addPhone,addCountry,addExtra1,addExtra2 FROM address WHERE addCustID='" . escape_string($_SESSION['clientID']) . "' ORDER BY addAddress";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_assoc($result))
			$addresses[$numaddresses++]=$rs;
		mysql_free_result($result);
	} ?>
			<form method="post" name="mainform" action="cart.php" onsubmit="return checkform(this)">
<?php
	if(is_array($addresses)){ ?>
<script language="javascript" type="text/javascript">/* <![CDATA[ */
var addrs = new Array();
addrs[0]=new Array();addrs[0]['name']='';addrs[0]['lastname']='';addrs[0]['address']='';addrs[0]['address2']='';addrs[0]['city']='';addrs[0]['state']='';addrs[0]['zip']='';addrs[0]['phone']='';addrs[0]['country']='';addrs[0]['extra1']='';addrs[0]['extra2']='';
function checkeditbutton(isshipping){
	adidobj = document.getElementById(isshipping + 'addressid');
	theaddy = adidobj[adidobj.selectedIndex].value;
	if(theaddy=='') document.getElementById(isshipping + 'editbutton').disabled=true; else document.getElementById(isshipping + 'editbutton').disabled=false;
}
function editaddress(isshipping,isaddnew){
	eval(isshipping+'checkaddress=true;');
	adidobj = document.getElementById(isshipping + 'addressid');
	theaddy = adidobj[adidobj.selectedIndex].value;
	if(isaddnew)theaddy=0;
	document.getElementById(isshipping + 'name').value=addrs[theaddy]['name'];
<?php	if(@$usefirstlastname==TRUE) print "document.getElementById(isshipping + 'lastname').value=addrs[theaddy]['lastname'];" ?>
	document.getElementById(isshipping + 'address').value=addrs[theaddy]['address'];
<?php	if(@$useaddressline2==TRUE) print "document.getElementById(isshipping + 'address2').value=addrs[theaddy]['address2'];" ?>
	document.getElementById(isshipping + 'city').value=addrs[theaddy]['city'];
	document.getElementById(isshipping + 'zip').value=addrs[theaddy]['zip'];
	document.getElementById(isshipping + 'phone').value=addrs[theaddy]['phone'];
<?php	if(trim(@$extraorderfield1)!='') print "setdefs(document.getElementById('ord'+isshipping+'extra1'),addrs[theaddy]['extra1']);";
		if(trim(@$extraorderfield2)!='') print "setdefs(document.getElementById('ord'+isshipping+'extra2'),addrs[theaddy]['extra2']);"; ?>
	thecntry = document.getElementById(isshipping + 'country')
	foundcntry=9999;
	for(ind=0; ind < thecntry.length; ind++){
		if(thecntry[ind].value==addrs[theaddy]['country']){
			thecntry.selectedIndex=ind;
			foundcntry=ind;
		}
	}
	if(foundcntry==9999)thecntry.selectedIndex=0;
	foundstate=0;havegotstate=false;
<?php	if($numallstates>0){ ?>
	thestate = document.getElementById(isshipping + 'state')
	if(foundcntry < <?php print $numhomecountries?>){
		for(ind=0; ind < thestate.length; ind++){
			if(thestate[ind].value==addrs[theaddy]['state']){
				foundstate=ind;
				havegotstate=true;
			}
		}
	}
	thestate.selectedIndex=foundstate;
<?php	}
		if($nonhomecountries>0) print "if(! havegotstate)document.getElementById(isshipping + 'state2').value=addrs[theaddy]['state'];" ?>
}
<?php	for($ii=0; $ii<$numaddresses; $ii++){
			print 'addrs[' . $addresses[$ii]['addID'] . "]=new Array();\r\n";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['name']='" . jschk($addresses[$ii]['addName']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['lastname']='" . jschk($addresses[$ii]['addLastName']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['address']='" . jschk($addresses[$ii]['addAddress']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['address2']='" . jschk($addresses[$ii]['addAddress2']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['state']='" . jschk($addresses[$ii]['addState']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['city']='" . jschk($addresses[$ii]['addCity']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['zip']='" . jschk($addresses[$ii]['addZip']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['phone']='" . jschk($addresses[$ii]['addPhone']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['country']='" . jschk($addresses[$ii]['addCountry']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['extra1']='" . jschk($addresses[$ii]['addExtra1']) . "';";
			print 'addrs[' . $addresses[$ii]['addID'] . "]['extra2']='" . jschk($addresses[$ii]['addExtra2']) . "';\r\n";
		} ?>
/* ]]> */</script>
<?php
	}
	print whv('mode', 'go');
	print whv('sessionid', strip_tags(trim($thesessionid)));
	print whv('PARTNER', strip_tags(trim(@$_POST['PARTNER'])));
	print whv('altrates', strip_tags(trim(@$_POST['altrates'])));	?>
	<input type="hidden" name="addaddress" id="addaddress" value="<?php print ($numaddresses>0 ? '' : 'add')?>" />
	<input type="hidden" name="saddaddress" id="saddaddress" value="<?php print ($numaddresses>0 ? '' : 'add')?>" />
			  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3" style="text-align:<?php print $tleft?>;">
				<tr>
				  <td class="cobhl" align="center" colspan="4" height="34"><strong><?php print $xxCstDtl?></strong></td>
				</tr>
<?php
	function writeshippingflags($colspan){
		global $willpickuptext,$willpickupcost,$commercialloc,$saturdaydelivery,$addshippinginsurance,$allowsignaturerelease,$signatureoption,$insidedelivery,$ordComLoc,$xxComLoc,$xxSatDel,$xxWantIns,$xxSigRel,$xxInsDel,$redstar,$holdatlocation,$homedelivery,$tleft,$tright;
		if(@$willpickuptext!=''){ ?>
			<tr><td align="<?php print $tright?>" class="cobhl" colspan="<?php print $colspan?>"><input type="checkbox" name="willpickup" value="Y" /></td>
			<td class="cobll" colspan="<?php print 4-$colspan?>"><span style="font-size:10px"><?php print $willpickuptext . (@$willpickupcost!=''?' (' . FormatEuroCurrency($willpickupcost) . ')' : '')?></span></td></tr>
<?php	}
		if(@$commercialloc==TRUE){ ?>
			<tr><td align="<?php print $tright?>" class="cobhl" colspan="<?php print $colspan?>"><input type="checkbox" name="commercialloc" value="Y" <?php if(($ordComLoc & 1)==1) print 'checked="checked"'?> /></td>
			<td class="cobll" colspan="<?php print 4-$colspan?>"><span style="font-size:10px"><?php print $xxComLoc?></span></td></tr>
<?php	}
		if(@$saturdaydelivery==TRUE){ ?>
			<tr><td align="<?php print $tright?>" class="cobhl" colspan="<?php print $colspan?>"><input type="checkbox" name="saturdaydelivery" value="Y" <?php if(($ordComLoc & 4)==4) print 'checked="checked"'?> /></td>
			<td class="cobll" colspan="<?php print 4-$colspan?>"><span style="font-size:10px"><?php print $xxSatDel?></span></td></tr>
<?php	}
		if(abs(@$addshippinginsurance)==2){ ?>
			<tr><td align="<?php print $tright?>" class="cobhl" colspan="<?php print $colspan?>"><input type="checkbox" name="wantinsurance" value="Y" <?php if(($ordComLoc & 2)==2) print 'checked="checked"'?> /></td>
			<td class="cobll" colspan="<?php print 4-$colspan?>"><span style="font-size:10px"><?php print $xxWantIns?></span></td></tr>
<?php	}
		if(@$allowsignaturerelease==TRUE && @$signatureoption!=''){ ?>
			<tr><td align="<?php print $tright?>" class="cobhl" colspan="<?php print $colspan?>"><input type="checkbox" name="signaturerelease" value="Y" <?php if(($ordComLoc & 8)==8) print 'checked="checked"'?> /></td>
			<td class="cobll" colspan="<?php print 4-$colspan?>"><span style="font-size:10px"><?php print $xxSigRel?></span></td></tr>
<?php	}
		if(@$insidedelivery==TRUE){ ?>
			<tr><td align="<?php print $tright?>" class="cobhl" colspan="<?php print $colspan?>"><input type="checkbox" name="insidedelivery" value="Y" <?php if(($ordComLoc & 16)==16) print 'checked="checked"'?> /></td>
			<td class="cobll" colspan="<?php print 4-$colspan?>"><span style="font-size:10px"><?php print $xxInsDel?></span></td></tr>
<?php	}
		if(@$holdatlocation==TRUE){ ?>
			<tr><td align="<?php print $tright?>" class="cobhl" colspan="<?php print $colspan?>"><input type="checkbox" name="holdatlocation" value="Y" /></td>
			<td class="cobll" colspan="<?php print 4-$colspan?>"><span style="font-size:10px">Please click here to Hold at Location</span></td></tr>
<?php	}
		if(@$homedelivery==TRUE){ ?>
			<tr><td align="<?php print $tright?>" class="cobhl" colspan="<?php print $colspan?>">Delivery Options:</td>
			<td class="cobll" colspan="<?php print 4-$colspan?>"><select name="homedelivery" size="1">
			<option value="">Standard Delivery</option>
			<option value="EVENING">Evening Home Delivery</option>
			<option value="DATE_CERTAIN">Date Certain Home Delivery</option>
			<option value="APPOINTMENT">Appointment Home Delivery</option>
			</select></td></tr>
<?php	}
	}
	if($numaddresses > 0){ ?>
				<tr>
				  <td align="<?php print $tright?>" class="cobhl" colspan="2" width="30%" height="30"><strong><?php print $xxBilAdd?>:</strong></td>
				  <td class="cobll" colspan="2">
<?php	function writeaddressspans($isshp){
			global $useaddressline2,$extraorderfield1html,$extraorderfield2html,$extraorderfield1required,$extraorderfield2required,$extraorderfield1,$extraorderfield2,$numallstates,$nonhomecountries,$numaddresses,$addresses,$xxSamAs,$xxName,$xxFirNam,$xxLasNam,$xxAddress,$xxAddress2,$xxCity,$xxState,$xxNonState,$xxCountry,$zipoptional,$xxZip,$xxPhone,$xxNew,$xxEdit,$xxCancel,$usefirstlastname,$redstar,$tleft,$tright;
?>		<span name="<?php print $isshp?>addressspan1" id="<?php print $isshp?>addressspan1" style="display:block"><select name="<?php print $isshp?>addressid" id="<?php print $isshp?>addressid" size="1" onchange="checkeditbutton('<?php print $isshp?>')"><?php
		if($isshp=='s') print '<option value="">' . $xxSamAs . '</option>';
		for($index=0; $index < $numaddresses; $index++){
			print '<option value="' . $addresses[$index]['addID'] . '"' . ($addresses[$index]['addIsDefault']==($isshp=='s'?2:1) ? ' selected="selected"' : '') . '>' . htmlspecials($addresses[$index]['addName']) . ', ' . htmlspecials($addresses[$index]['addAddress']) . (trim($addresses[$index]['addAddress2'])!='' ? ', ' . htmlspecials($addresses[$index]['addAddress2']) : '') . ', ' . htmlspecials($addresses[$index]['addState']) . '</option>';
		} ?></select> <input type="button" value="<?php print $xxEdit?>" id="<?php print $isshp?>editbutton" onclick="editaddress('<?php print $isshp?>',false);document.getElementById('<?php print $isshp?>addressspan1').style.display='none';document.getElementById('<?php print $isshp?>addressspan2').style.display='block';document.getElementById('<?php print $isshp?>addaddress').value='edit';"> <input type="button" value="<?php print $xxNew?>" onclick="editaddress('<?php print $isshp?>',true);document.getElementById('<?php print $isshp?>addressspan1').style.display='none';document.getElementById('<?php print $isshp?>addressspan2').style.display='block';document.getElementById('<?php print $isshp?>addaddress').value='add';">
		</span><span name="<?php print $isshp?>addressspan2" id="<?php print $isshp?>addressspan2" style="display:none">
		<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3" style="font-size:11px;font-weight:bold;">
		<?php	if(trim(@$extraorderfield1)!=''){ ?>
		<tr><td align="<?php print $tright?>" class="cobhl"><?php print (@$extraorderfield1required==TRUE ? $redstar : '') . $extraorderfield1 ?>:</td><td class="cobll"><?php if(@$extraorderfield1html!='') print str_replace(array('ectfield','ordextra1'),'ord'.$isshp.'extra1',$extraorderfield1html); else print '<input type="text" name="ord'.$isshp.'extra1" id="ord'.$isshp.'extra1" size="20" alt="' . $extraorderfield1 . '" />'?></td></tr>
		<?php	} ?>
		<tr><td align="<?php print $tright?>" class="cobhl"><?php print $redstar . $xxName?>:</td><td class="cobll"><?php
		if(@$usefirstlastname)
			print '<input type="text" name="'.$isshp.'name" id="'.$isshp.'name" size="11" alt="'.$xxFirNam.'" onfocus="if(this.value==\''.$xxFirNam.'\'){this.value=\'\';this.style.color=\'\';}" /> <input type="text" name="'.$isshp.'lastname" id="'.$isshp.'lastname" size="11" alt="'.$xxLasNam.'" onfocus="if(this.value==\''.$xxLasNam.'\'){this.value=\'\';this.style.color=\'\';}" />';
		else
			print '<input type="text" name="'.$isshp.'name" id="'.$isshp.'name" size="20" alt="'.$xxName.'"/>';
		?></td></tr>
		<tr><td align="<?php print $tright?>" class="cobhl"><?php print $redstar . $xxAddress?>:</td><td class="cobll"><input type="text" name="<?php print $isshp?>address" id="<?php print $isshp?>address" size="25" alt="<?php print $xxAddress?>" /></td></tr>
		<?php	if(@$useaddressline2==TRUE){ ?>
		<tr><td align="<?php print $tright?>" class="cobhl"><?php print $xxAddress2?>:</td><td class="cobll"><input type="text" name="<?php print $isshp?>address2" id="<?php print $isshp?>address2" size="25" alt="<?php print $xxAddress2?>" /></td></tr>
		<?php	} ?>
		<tr><td align="<?php print $tright?>" class="cobhl"><?php print $redstar . $xxCity?>:</td><td class="cobll"><input type="text" name="<?php print $isshp?>city" id="<?php print $isshp?>city" size="20" alt="<?php print $xxCity?>" /></td></tr>
		<?php	if($numallstates > 0){ ?>
		<tr><td align="<?php print $tright?>" class="cobhl"><span id="<?php print $isshp?>outspandd" style="visibility:hidden;color:#F00">*</span><?php print $xxState?>:</td><td class="cobll"><select name="<?php print $isshp?>state" id="<?php print $isshp?>state" size="1" onchange="dosavestate('')"><?php $havestate = show_states(-1) ?></select></td></tr>
		<?php	}
				if($nonhomecountries!=0){ ?>
		<tr><td align="<?php print $tright?>" class="cobhl"><span id="<?php print $isshp?>outspan" style="visibility:hidden;color:#F00">*</span><?php print $xxNonState?>:</td><td class="cobll"><input type="text" name="<?php print $isshp?>state2" id="<?php print $isshp?>state2" size="20" alt="<?php print $xxNonState?>" /></td></tr>
		<?php	} ?>
		<tr><td align="<?php print $tright?>" class="cobhl"><?php print $redstar . $xxCountry?>:</td><td class="cobll"><select name="<?php print $isshp?>country" id="<?php print $isshp?>country" size="1" onchange="checkoutspan('<?php print $isshp?>')"><?php show_countries(-1) ?></select></td></tr>
		<tr><td align="<?php print $tright?>" class="cobhl"><?php if(@$zipoptional!=TRUE) print $redstar; print $xxZip?>:</td><td class="cobll"><input type="text" name="<?php print $isshp?>zip" id="<?php print $isshp?>zip" size="10" alt="<?php print $xxZip?>" /></td></tr>
		<tr><td align="<?php print $tright?>" class="cobhl"><?php if($isshp=='') print $redstar; print $xxPhone?>:</td><td class="cobll"><input type="text" name="<?php print $isshp?>phone" id="<?php print $isshp?>phone" size="20" alt="<?php print $xxPhone?>" /></td></tr>
		<?php	if(trim(@$extraorderfield2)!=''){ ?>
		<tr><td align="<?php print $tright?>" class="cobhl"><?php print (@$extraorderfield2required==TRUE ? $redstar : '') . $extraorderfield2 ?>:</td><td class="cobll"><?php if(@$extraorderfield2html!='') print str_replace(array('ectfield','ordextra2'),'ord'.$isshp.'extra2',$extraorderfield2html); else print '<input type="text" name="ord'.$isshp.'extra2" id="ord'.$isshp.'extra2" size="20" alt="' . $extraorderfield2 . '" />'?></td></tr>
		<?php	} ?>
		<tr><td align="center" colspan="2" class="cobll"><input type="button" value="<?php print $xxCancel?>" onclick="document.getElementById('<?php print $isshp?>addressspan2').style.display='none';document.getElementById('<?php print $isshp?>addressspan1').style.display='block';document.getElementById('<?php print $isshp?>addaddress').value='';<?php print $isshp?>checkaddress=false;"></td></tr>
		</table></span>
<?php	}
		writeaddressspans(''); ?>
				  </td>
				</tr>
<?php	writeshippingflags(2);
		if(@$noshipaddress!=TRUE){ ?>
				<tr>
				  <td align="<?php print $tright?>" class="cobhl" colspan="2" height="30"><strong><?php print $xxShpAdd?>:</strong></td>
				  <td class="cobll" colspan="2"><?php writeaddressspans('s'); ?></td>
				</tr>
<?php	}
	}else{
		if(@$_SESSION['clientID']!=''){
			$result = mysql_query("SELECT clUserName,clEmail FROM customerlogin WHERE clID='" . escape_string($_SESSION['clientID']) . "'") or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$ordName=trim($rs['clUserName']);
				if(@$usefirstlastname){
					if(strpos(trim($ordName), ' ')!==FALSE){
						$namearr = explode(' ',$ordName,2);
						$ordName = $namearr[0];
						$ordLastName = $namearr[1];
					}else
						$ordName = '';
				}
				$ordEmail=$rs['clEmail'];
			}
			mysql_free_result($result);
		}
		if(trim(@$extraorderfield1)!=''){ ?>
				<tr>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php if(@$extraorderfield1required==TRUE) print $redstar;
									print $extraorderfield1 ?>:</strong></td>
				  <td class="cobll" colspan="3"><?php if(@$extraorderfield1html!='') print str_replace('ectfield','ordextra1',$extraorderfield1html); else print '<input type="text" name="ordextra1" size="20" value="' . @$ordExtra1 . '" alt="' . $extraorderfield1 . '" />'?></td>
				</tr>
<?php	} ?>
				<tr>
				  <td align="<?php print $tright?>" class="cobhl" width="25%"><strong><?php print $redstar . $xxName?>:</strong></td>
				  <td class="cobll" width="25%" style="white-space:nowrap"><?php
		if(@$usefirstlastname){
			$thestyle='';
			if(@$ordName=='' && @$ordLastName==''){ $ordName=$xxFirNam; $ordLastName=$xxLasNam; $thestyle='style="color:#BBBBBB" '; }
			print '<input type="text" name="name" size="10" value="' . htmlspecials(@$ordName) . '" onfocus="if(this.value==\'' . $xxFirNam . '\'){this.value=\'\';this.style.color=\'\';}" ' . $thestyle . '/> <input type="text" name="lastname" size="10" value="' . htmlspecials(@$ordLastName) . '" alt="' . $xxLasNam . '" onfocus="if(this.value==\'' . $xxLasNam . '\'){this.value=\'\';this.style.color=\'\';}" ' . $thestyle . '/>';
		}else
			print '<input type="text" name="name" size="20" value="' . htmlspecials(@$ordName) . '" />'; ?></td>
				  <td align="<?php print $tright?>" class="cobhl" width="25%"><strong><?php print $redstar . $xxEmail?>:</strong></td>
				  <td class="cobll" width="25%"><input type="text" name="email" size="20" value="<?php print @$ordEmail?>" alt="<?php print $xxEmail?>" /></td>
				</tr>
				<tr>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php print $redstar . $xxAddress?>:</strong></td>
				  <td class="cobll"<?php if(@$useaddressline2==TRUE) print ' colspan="3"'?>><input type="text" name="address" id="address" size="25" value="<?php print @$ordAddress?>" alt="<?php print $xxAddress?>" /></td>
<?php	if(@$useaddressline2==TRUE){ ?>
				</tr>
				<tr>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php print $xxAddress2?>:</strong></td>
				  <td class="cobll"><input type="text" name="address2" size="25" value="<?php print @$ordAddress2?>" alt="<?php print $xxAddress2?>" /></td>
<?php	} ?>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php print $redstar . $xxCity?>:</strong></td>
				  <td class="cobll"><input type="text" name="city" size="20" value="<?php print @$ordCity?>" alt="<?php print $xxCity?>" /></td>
				</tr>
<?php	if($numallstates>0 || $nonhomecountries!=0){ ?>
				<tr>
<?php		if($numallstates > 0){ ?>
				  <td align="<?php print $tright?>" class="cobhl"><strong><span id="outspandd" style="visibility:hidden;color:#F00">*</span><?php print $xxState?>:</strong></td>
				  <td class="cobll"><select name="state" id="state" size="1" onchange="dosavestate('')"><?php $havestate = show_states(@$ordState) ?></select></td>
<?php		}
			if($nonhomecountries==0)
				print '<td class="cobll" colspan="2">&nbsp;</td>';
			else{ ?>
				  <td align="<?php print $tright?>" class="cobhl"><strong><span id="outspan" style="visibility:hidden;color:#F00">*</span><?php print $xxNonState?>:</strong></td>
				  <td class="cobll"><input type="text" name="state2" size="20" value="<?php if(! $havestate) print @$ordState?>" alt="<?php print $xxNonState?>" /></td>
<?php		}
			if($numallstates==0) print '<td class="cobll" colspan="2">&nbsp;</td>' ?>
				</tr>
<?php	} ?>
				<tr>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php print $redstar . $xxCountry?>:</strong></td>
				  <td class="cobll"><select name="country" id="country" size="1" onchange="checkoutspan('')"><?php show_countries(@$ordCountry) ?></select></td>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php if(@$zipoptional!=TRUE) print $redstar; print $xxZip?>:</strong></td>
				  <td class="cobll"><input type="text" name="zip" size="10" value="<?php print @$ordZip?>" alt="<?php print $xxZip?>" /></td>
				</tr>
				<tr>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php print $redstar . $xxPhone?>:</strong></td>
				  <td class="cobll"<?php	if(trim(@$extraorderfield2)=='') print ' colspan="3"'?>><input type="text" name="phone" size="20" value="<?php print @$ordPhone?>" alt="<?php print $xxPhone?>" /></td>
<?php	if(trim(@$extraorderfield2)!=''){ ?>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php if(@$extraorderfield2required==TRUE) print $redstar;
									print $extraorderfield2 ?>:</strong></td>
				  <td class="cobll"><?php if(@$extraorderfield2html!='') print str_replace('ectfield','ordextra2',$extraorderfield2html); else print '<input type="text" name="ordextra2" size="20" value="' . @$ordExtra2 . '" alt="' . $extraorderfield2 . '" />'?></td>
<?php	} ?>
				</tr>
<?php	writeshippingflags(1);
		if(@$noshipaddress!=TRUE){ ?>
				<tr>
				  <td align="center" colspan="4" class="cobhl" height="30"><strong><?php if(@$hideshipaddress!=TRUE){ print $xxShpDiff; $shprowstyle=''; }else{ print $xxShpDff . '<input type="checkbox" name="shipdiff" id="shipdiff" value="1" onclick="showshipform(this)" style="vertical-align:middle;" />'; $shprowstyle=' style="display:none"'; } ?></strong></td>
				</tr>
<?php		if(trim(@$extraorderfield1)!=''){ ?>
				<tr class="shipformrow"<?php print $shprowstyle?>>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php print $extraorderfield1 ?>:</strong></td>
				  <td class="cobll" colspan="3"><?php if(@$extraorderfield1html!='') print str_replace(array('ordextra1','ectfield'),'ordsextra1',$extraorderfield1html); else print '<input type="text" name="ordsextra1" size="20" value="' . @$ordShipExtra1 . '" alt="' . $extraorderfield1 . '" />'?></td>
				</tr>
<?php		} ?>
				<tr class="shipformrow"<?php print $shprowstyle?>>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php print $xxName?>:</strong></td>
				  <td class="cobll" colspan="3"><?php
		if(@$usefirstlastname){
			$thestyle='';
			if(@$ordShipName=='' && @$ordShipLastName==''){ $ordShipName=$xxFirNam; $ordShipLastName=$xxLasNam; $thestyle='style="color:#BBBBBB" '; }
			print '<input type="text" name="sname" size="10" value="' . htmlspecials(@$ordShipName) . '" onfocus="if(this.value==\'' . $xxFirNam . '\'){this.value=\'\';this.style.color=\'\';}" ' . $thestyle . '/> <input type="text" name="slastname" size="10" value="' . htmlspecials(@$ordShipLastName) . '" alt="' . $xxLasNam . '" onfocus="if(this.value==\'' . $xxLasNam . '\'){this.value=\'\';this.style.color=\'\';}" ' . $thestyle . '/>';
		}else
			print '<input type="text" name="sname" size="20" value="' . htmlspecials(@$ordShipName) . '" />'; ?></td>
				</tr>
				<tr class="shipformrow"<?php print $shprowstyle?>>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php print $xxAddress?>:</strong></td>
				  <td class="cobll"<?php if(@$useaddressline2==TRUE) print ' colspan="3"'?>><input type="text" name="saddress" id="saddress" size="25" value="<?php print trim(@$ordShipAddress)?>" alt="<?php print $xxAddress?>" /></td>
<?php		if(@$useaddressline2==TRUE){ ?>
				</tr>
				<tr class="shipformrow"<?php print $shprowstyle?>>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php print $xxAddress2?>:</strong></td>
				  <td class="cobll"><input type="text" name="saddress2" size="25" value="<?php print @$ordShipAddress2?>" alt="<?php print $xxAddress2?>" /></td>
<?php		} ?>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php print $xxCity?>:</strong></td>
				  <td class="cobll"><input type="text" name="scity" size="20" value="<?php print @$ordShipCity?>" alt="<?php print $xxCity?>" /></td>
				</tr>
<?php		if($numallstates > 0 || $nonhomecountries!=0){ ?>
				<tr class="shipformrow"<?php print $shprowstyle?>>
<?php			if($numallstates > 0){ ?>
				  <td align="<?php print $tright?>" class="cobhl"><strong><span id="soutspandd" style="visibility:hidden;color:#F00">*</span><?php print $xxState?>:</strong></td>
				  <td class="cobll"><select name="sstate" id="sstate" size="1" onchange="dosavestate('s')"><?php $havestate = show_states(@$ordShipState) ?></select></td>
<?php			}
				if($nonhomecountries==0)
					print '<td class="cobll" colspan="2">&nbsp;</td>';
				else{ ?>
				  <td align="<?php print $tright?>" class="cobhl"><strong><span id="soutspan" style="visibility:hidden;color:#F00">*</span><?php print $xxNonState?>:</strong></td>
				  <td class="cobll"><input type="text" name="sstate2" size="20" value="<?php if(! $havestate) print @$ordShipState?>" alt="<?php print $xxNonState?>" /></td>
<?php			}
				if($numallstates==0) print '<td class="cobll" colspan="2">&nbsp;</td>'; ?>
				</tr>
<?php		} ?>
				<tr class="shipformrow"<?php print $shprowstyle?>>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php print $xxCountry?>:</strong></td>
				  <td class="cobll"><select name="scountry" id="scountry" size="1" onchange="checkoutspan('s')"><?php show_countries(@$ordShipCountry) ?></select></td>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php print $xxZip?>:</strong></td>
				  <td class="cobll"><input type="text" name="szip" size="10" value="<?php print @$ordShipZip?>" alt="<?php print $xxZip?>" /></td>
				</tr>
				<tr class="shipformrow"<?php print $shprowstyle?>>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php print $xxPhone?>:</strong></td>
				  <td class="cobll"<?php	if(trim(@$extraorderfield2)=='') print ' colspan="3"' ?>><input type="text" name="sphone" size="20" value="<?php print @$ordShipPhone?>" alt="<?php print $xxPhone?>" /></td>
<?php		if(trim(@$extraorderfield2)!=''){ ?>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php print $extraorderfield2 ?>:</strong></td>
				  <td class="cobll"><?php if(@$extraorderfield2html!='') print str_replace(array('ordextra2','ectfield'),'ordsextra2',$extraorderfield2html); else print '<input type="text" name="ordsextra2" size="20" value="' . @$ordShipExtra2 . '" alt="' . $extraorderfield2 . '" />'?></td>
<?php		} ?>
				</tr>
<?php	} // noshipaddress
	} // ($numaddresses>0) ?>
				<tr><td class="cobhl" align="center" colspan="4" height="30"><strong><?php print $xxMisc?></strong></td></tr>
				<tr>
				  <td align="<?php print $tright?>" class="cobhl"><strong><?php print $xxAddInf?>.</strong></td>
				  <td class="cobll" colspan="3"><textarea name="ordAddInfo" rows="3" cols="44"><?php print @$ordAddInfo?></textarea></td>
				</tr>
<?php
	if(trim(@$extracheckoutfield1)!=''){
		$checkoutfield1 = '<strong>' . (@$extracheckoutfield1required==TRUE ? $redstar : '') . $extracheckoutfield1 . '</strong>';
		$checkoutfield2 = (@$extracheckoutfield1html!='' ? str_replace('ectfield','ordcheckoutextra1',$extracheckoutfield1html) : '<input type="text" name="ordcheckoutextra1" size="20" value="' . @$ordCheckoutExtra1 . '" alt="' . $extracheckoutfield1 . '" />');
?>				<tr>
				  <td align="<?php print $tright?>" class="cobhl"><?php if(@$extracheckoutfield1reverse) print $checkoutfield2; else print $checkoutfield1 . '<strong>:</strong>'?></td>
				  <td class="cobll" colspan="3"><?php if(@$extracheckoutfield1reverse) print $checkoutfield1; else print $checkoutfield2 ?></td>
				</tr>
<?php
	}
	if(trim(@$extracheckoutfield2)!=''){
		$checkoutfield1 = '<strong>' . (@$extracheckoutfield2required==TRUE ? $redstar : '') . $extracheckoutfield2 . '</strong>';
		$checkoutfield2 = (@$extracheckoutfield2html!='' ? str_replace('ectfield','ordcheckoutextra2',$extracheckoutfield2html) : '<input type="text" name="ordcheckoutextra2" size="20" value="' . @$ordCheckoutExtra2 . '" alt="' . $extracheckoutfield2 . '" />');
?>				<tr>
				  <td align="<?php print $tright?>" class="cobhl"><?php if(@$extracheckoutfield2reverse) print $checkoutfield2; else print $checkoutfield1 . '<strong>:</strong>' ?></td>
				  <td class="cobll" colspan="3"><?php if(@$extracheckoutfield2reverse) print $checkoutfield1; else print $checkoutfield2 ?></td>
				</tr>
<?php
	}
	if(@$termsandconditions==TRUE){ ?>
				<tr>
				  <td align="<?php print $tright?>" class="cobhl"><input type="checkbox" name="license" value="1" /></td>
				  <td class="cobll" colspan="3"><?php print $xxTermsCo?></td>
				</tr>
<?php
	}
	if(@$_SESSION['clientID']=='' && @$noremember!=TRUE){ ?>
				<tr>
				  <td align="<?php print $tright?>" class="cobhl"><input type="checkbox" name="remember" value="1" <?php if($remember) print 'checked="checked"'?> /></td>
				  <td class="cobll" colspan="3"><strong><?php print $xxRemMe?></strong><br /><span style="font-size:10px"><?php print $xxOpCook?></span></td>
				</tr>
<?php
	}
	if(@$nomailinglist!=TRUE){ ?>
				<tr>
				  <td align="<?php print $tright?>" class="cobhl"><input type="checkbox" name="allowemail" value="ON" <?php if(@$allowemaildefaulton) print 'checked="checked"'?> /></td>
				  <td class="cobll" colspan="3"><strong><?php print $xxAlPrEm?></strong><br /><span style="font-size:10px"><?php print $xxNevDiv?></span></td>
				</tr>
<?php
	}
	if(@$nogiftcertificate!=TRUE){ ?>
				<tr><td align="<?php print $tright?>" class="cobhl" height="30"><strong><?php print $xxGifNum?>:</strong></td><td class="cobll" colspan="3"><table border="0" cellpadding="0" cellspacing="0"><tr><td><input type="text" name="cpncode" id="cpncode" size="20" alt="<?php print $xxGifNum?>" /> <input type="button" value="<?php print $xxApply?>" onclick="applycert()" />&nbsp;&nbsp;</td><td><span id="cpncodespan"><?php
		if(@$_SESSION['giftcerts']!='' || @$_SESSION['cpncode']!=''){
			print '<table border="0">';
			if(trim(@$_SESSION['giftcerts'])!=''){
				$gcarr = explode(' ', trim(@$_SESSION['giftcerts']));
				foreach($gcarr as $key => $value){
					print '<tr><td align="'.$tright.'">' . $xxAppGC . ':</td><td>' . $value . '</td><td>(<a href="#" onclick="removecert(\''.$value.'\')"><strong>'.$xxRemove.'</strong></a>)</td></tr>';
				}
			}
			if(trim(@$_SESSION['cpncode'])!=''){
				$cpnarr = explode(' ', trim(@$_SESSION['cpncode']));
				foreach($cpnarr as $key => $value){
					print '<tr><td align="'.$tright.'">' . $xxApdCpn . ':</td><td>' . $value . '</td><td>(<a href="#" onclick="removecert(\''.$value.'\')"><strong>'.$xxRemove.'</strong></a>)</td></tr>';
				}
			}
			print '</table>';
		} ?>
		</span></td></tr></table></td></tr>
<?php
	}
	if(! @isset($noemailgiftcertorders)) $noemailgiftcertorders='4';
	$sSQL = "SELECT cartID FROM cart WHERE cartCompleted=0 AND (cartProdID='".$giftcertificateid."' OR cartProdID='".$donationid."') AND " . getsessionsql();
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result)!=0 && $noemailgiftcertorders!='') $exclemail=$noemailgiftcertorders.','; else $exclemail='';
	mysql_free_result($result);
	$sSQL = 'SELECT payProvID,'.getlangid('payProvShow',128).' FROM payprovider WHERE payProvEnabled=1 AND payProvLevel<=' . $minloglevel . ' AND NOT (payProvID IN ('.$exclemail.'19,20' . (@$paypalhostedsolution?',18':'') . ')) ORDER BY payProvOrder';
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result)==0){ ?>
				<tr><td colspan="4" align="center" class="cobhl"><strong><?php print $xxNoPay?></strong></td></tr>
<?php
	}elseif(mysql_num_rows($result)==1){
		$rs = mysql_fetch_assoc($result);
		print whv('payprovider',$rs['payProvID']);
		$nodefaultpayprovider=FALSE;
		$payproviderradios='';
	}else{ ?>
			    <tr><td align="<?php print $tright?>" class="cobhl" height="30"><strong><?php print $xxPlsChz?>:</strong></td>
				  <td class="cobll" colspan="3"><?php
		if(@$payproviderradios==1 || @$payproviderradios==2){
			print ($payproviderradios==2?'<table class="payprovider"><tr>':'');
			while($rs = mysql_fetch_assoc($result)){
				print ($payproviderradios==1?'<table class="payprovider"><tr>':'').'<td><input type="radio" name="payprovider" value="' . $rs['payProvID'] . '"';
				if(@$ordPayProvider==$rs['payProvID'] || ($ordPayProvider=='' && @$nodefaultpayprovider!=TRUE)){ print ' checked="checked"'; $ordPayProvider='-1'; }
				print '></td><td>' . $rs[getlangid('payProvShow',128)] . ($rs['payProvID']==1?'</td><td><img src="images/paypalacceptmark.gif">':'') . (@$payproviderradios==1?'</td></tr></table>':' </td>');
			}
			print ($payproviderradios==2?'</tr></table>':'');
		}else{
			print '<select name="payprovider" size="1">';
			if(@$nodefaultpayprovider==TRUE) print '<option value="">'.$xxPlsSel.'</option>';
			while($rs = mysql_fetch_assoc($result)){
				print '<option value="' . $rs['payProvID'] . '"';
				if(@$ordPayProvider==$rs['payProvID']) print ' selected="selected"';
				print '>' . $rs[getlangid('payProvShow',128)] . '</option>';
			}
			print '</select>';
		} ?></td></tr>
<?php
	}
	mysql_free_result($result); ?>
				<tr>
			      <td height="30" align="center" class="cobll" colspan="4"><table width="100%" cellspacing="0" cellpadding="0" border="0">
				    <tr>
					  <td width="16" height="26">&nbsp;</td>
					  <td width="100%" align="center"><?php print imageorsubmit($imgcheckoutbutton2,$xxCOTxt,'checkoutbutton')?></td>
					  <td width="16" align="right" valign="bottom"><img src="images/tablebr.png" alt="" /></td>
					</tr></table>
				  </td>
				</tr>
			  </table>
			</form>
<script language="javascript" type="text/javascript">/* <![CDATA[ */
<?php	if(@$hideshipaddress==TRUE){ ?>
function showshipform(curobj){
	var elem = document.getElementsByTagName('tr');
	for(var i=0; i<elem.length; i++){
		var classes = elem[i].className;
		if(classes.indexOf('shipformrow')!=-1){
			elem[i].style.display=(curobj.checked?'':'none');
		}
	}
}
<?php	} ?>
var checkedfullname=false;
var numhomecountries=0,nonhomecountries=0,checkaddress=true,scheckaddress=true;
function chkextra(ob,fldtxt){
	var hasselected=false;
	var fieldtype='';
	if(ob)fieldtype=(ob.type?ob.type:'radio');
	if(fieldtype=='text'||fieldtype=='textarea'||fieldtype=='password'){
		if(ob.value!='')hasselected=true;
	}else if(fieldtype=='select-one'){
		if(ob.selectedIndex!=0)hasselected=true;
	}else if(fieldtype=='radio'){
		for(var ii=0;ii<ob.length;ii++)if(ob[ii].checked)hasselected=true;
	}else if(fieldtype=='checkbox'){
		if(ob.checked)hasselected=true;
	}
	if(!hasselected){alert("<?php print $xxPlsEntr?> \""+fldtxt+"\".");if(ob.focus)ob.focus();else ob[0].focus();return(false);}
	return(true);
}
function setdefs(ob,deftxt){
	var fieldtype='';
	if(ob)fieldtype=(ob.type?ob.type:'radio');<?php if(@$debugmode) print 'else alert("Extra order field id not found");'?>
	if(fieldtype=='text'||fieldtype=='textarea'||fieldtype=='password'){
		ob.value=deftxt;
	}else if(fieldtype=='select-one'){
		for(var ii=0;ii<ob.length;ii++)if(ob[ii].value==deftxt)ob[ii].selected=true;
	}else if(fieldtype=='radio'){
		for(var ii=0;ii<ob.length;ii++)if(ob[ii].value==deftxt)ob[ii].checked=true;
	}else if(fieldtype=='checkbox'){
		if(ob.value==deftxt)ob.checked=true;
	}
}
<?php	if($numaddresses==0){
			if(trim(@$extraorderfield1)!='' && trim(@$ordExtra1)!='') print "setdefs(document.forms.mainform.ordextra1,'".str_replace(array("\r\n","\r","\n"),"\\n",$ordExtra1)."');\r\n";
			if(trim(@$extraorderfield2)!='' && trim(@$ordExtra2)!='') print "setdefs(document.forms.mainform.ordextra2,'".str_replace(array("\r\n","\r","\n"),"\\n",$ordExtra2)."');\r\n";
			if(@$noshipaddress!=TRUE){
				if(trim(@$extraorderfield1)!='' && trim(@$ordShipExtra1)!='') print "setdefs(document.forms.mainform.ordsextra1,'".str_replace(array("\r\n","\r","\n"),"\\n",$ordShipExtra1)."');\r\n";
				if(trim(@$extraorderfield2)!='' && trim(@$ordShipExtra2)!='') print "setdefs(document.forms.mainform.ordsextra2,'".str_replace(array("\r\n","\r","\n"),"\\n",$ordShipExtra2)."');\r\n";
			}
		}
		if(trim(@$extracheckoutfield1)!='' && trim(@$ordCheckoutExtra1)!='') print "setdefs(document.forms.mainform.ordcheckoutextra1,'".str_replace(array("\r\n","\r","\n"),"\\n",$ordCheckoutExtra1)."');\r\n";
		if(trim(@$extracheckoutfield2)!='' && trim(@$ordCheckoutExtra2)!='') print "setdefs(document.forms.mainform.ordcheckoutextra2,'".str_replace(array("\r\n","\r","\n"),"\\n",$ordCheckoutExtra2)."');\r\n"; ?>
function checkform(frm){
if(checkaddress){
<?php	if(trim(@$extraorderfield1)!='' && @$extraorderfield1required==TRUE) print 'if(!chkextra(frm.ordextra1,"'.$extraorderfield1.'"))return(false);'."\r\n";?>
if(frm.name.value==""||frm.name.value=="<?php print $xxFirNam?>"){
	alert("<?php print $xxPlsEntr?> \"<?php print (@$usefirstlastname ? $xxFirNam : $xxName)?>\".");
	frm.name.focus();
	return(false);
}
<?php	if(@$usefirstlastname==TRUE){ ?>
if(frm.lastname.value==""||frm.lastname.value=="<?php print $xxLasNam?>"){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxLasNam?>\".");
	frm.lastname.focus();
	return(false);
}
<?php	}else{ ?>
var regex=/ /;
if(!checkedfullname && !regex.test(frm.name.value)){
	alert("<?php print $xxFulNam?> \"<?php print $xxName?>\".");
	frm.name.focus();
	checkedfullname=true;
	return(false);
}
<?php	}
		if(! is_array($addresses)){ ?>
var regex = /[^@]+@[^@]+\.[a-z]{2,}$/i;
if(!regex.test(frm.email.value)){
	alert("<?php print $xxValEm?>");
	frm.email.focus();
	return(false);
}
<?php	} ?>
if(frm.address.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxAddress?>\".");
	frm.address.focus();
	return(false);
}
if(frm.city.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxCity?>\".");
	frm.city.focus();
	return(false);
}
<?php	if($origCountryCode!="US" && $origCountryCode!="CA" && @$dynamicuscastates==TRUE)
			print "if(stateselectordisabled[0]==false){\r\n";
		else
			print "if(frm.country.selectedIndex < numhomecountries){\r\n";
		if($numallstates>0 && $xxOutState!=''){ ?>
	if(frm.state.selectedIndex==0){
		alert("<?php print $xxPlsSlct . " " . $xxState?>");
		frm.state.focus();
		return(false);
	}
<?php	} ?>
}else{
<?php	if($nonhomecountries>0){ ?>
	if(frm.state2.value==""){
		alert("<?php print $xxPlsEntr?> \"<?php print str_replace("<br />"," ",$xxNonState)?>\".");
		frm.state2.focus();
		return(false);
	}
<?php	} ?>}
if(frm.zip.value==""<?php if(@$zipoptional==TRUE) print ' && false'?>){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxZip?>\".");
	frm.zip.focus();
	return(false);
}
if(frm.phone.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxPhone?>\".");
	frm.phone.focus();
	return(false);
}
<?php if(trim(@$extraorderfield2)!='' && @$extraorderfield2required==TRUE) print 'if(!chkextra(frm.ordextra2,"'.$extraorderfield2.'"))return(false);'."\r\n";?>
}
<?php if(@$noshipaddress!=TRUE){ ?>
if(scheckaddress&&frm.saddress.value!=""<?php if(@$hideshipaddress==TRUE&&$numaddresses==0) print '&&document.getElementById("shipdiff").checked'?>){
<?php	if(trim(@$extraorderfield1)!='' && @$extraorderfield1required==TRUE) print 'if(!chkextra(frm.ordsextra1,"'.$extraorderfield1.'"))return(false);'."\r\n";?>
	if(frm.sname.value==""||frm.sname.value=="<?php print $xxFirNam?>"){
		alert("<?php print $xxShpDtls?>\n\n<?php print $xxPlsEntr?> \"<?php print $xxName?>\".");
		frm.sname.focus();
		return(false);
	}
	if(frm.scity.value==""){
		alert("<?php print $xxShpDtls?>\n\n<?php print $xxPlsEntr?> \"<?php print $xxCity?>\".");
		frm.scity.focus();
		return(false);
	}
<?php	if($origCountryCode!="US" && $origCountryCode!="CA" && @$dynamicuscastates==TRUE)
			print "if(stateselectordisabled[1]==false){\r\n";
		else
			print "if(frm.scountry.selectedIndex < numhomecountries){\r\n";
		if($numallstates>0){ ?>
		if(frm.sstate.selectedIndex==0){
			alert("<?php print $xxShpDtls?>\n\n<?php print $xxPlsSlct . " " . $xxState?>.");
			frm.sstate.focus();
			return(false);
		}
<?php	} ?>
	}else{
<?php	if($nonhomecountries>0){ ?>
		if(frm.sstate2.value==""){
			alert("<?php print $xxShpDtls?>\n\n<?php print $xxPlsEntr?> \"<?php print str_replace("<br />"," ",$xxNonState)?>\".");
			frm.sstate2.focus();
			return(false);
		}
<?php	} ?>
	}
	if(frm.szip.value==""<?php if(@$zipoptional==TRUE) print ' && FALSE'?>){
		alert("<?php print $xxShpDtls?>\n\n<?php print $xxPlsEntr?> \"<?php print $xxZip?>\".");
		frm.szip.focus();
		return(false);
	}
<?php	if(trim(@$extraorderfield2)!='' && @$extraorderfield2required==TRUE) print 'if(!chkextra(frm.ordsextra2,"'.$extraorderfield2.'"))return(false);'."\r\n";?>
}
<?php }
		if(trim(@$extracheckoutfield1)!='' && @$extracheckoutfield1required==TRUE) print 'if(!chkextra(frm.ordcheckoutextra1,"'.$extracheckoutfield1.'"))return(false);'."\r\n";
		if(trim(@$extracheckoutfield2)!='' && @$extracheckoutfield2required==TRUE) print 'if(!chkextra(frm.ordcheckoutextra2,"'.$extracheckoutfield2.'"))return(false);'."\r\n";
		if(@$_SESSION['clientID']=='' && @$noremember!=TRUE){ ?>
if(frm.remember.checked==false){
	if(confirm("<?php print $xxWntRem?>")){
		frm.remember.checked=true
	}
}
<?php	}
		if(@$termsandconditions==TRUE){ ?>
if(frm.license.checked==false){
	alert("<?php print $xxPlsProc?>");
	frm.license.focus();
	return(false);
}
<?php	}
		if(@$payproviderradios!=''){ ?>
hasselected=false;
for(var ii=0;ii<frm.payprovider.length;ii++)if(frm.payprovider[ii].checked)hasselected=true;
if(!hasselected){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxPlsChz?>\".");
	return(false);
}
<?php	}elseif(@$nodefaultpayprovider){ ?>
if(frm.payprovider.selectedIndex==0){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxPlsChz?>\".");
	frm.payprovider.focus();
	return(false);
}
<?php	} ?>
return (true);
}
<?php if(@$termsandconditions==TRUE){ ?>
function showtermsandconds(){
newwin=window.open("termsandconditions.php","Terms","menubar=no, scrollbars=yes, width=420, height=380, directories=no,location=no,resizable=yes,status=no,toolbar=no");
}
<?php } ?>
var savestate=0;
var ssavestate=0;
function applycertcallback(){
	if(ajaxobj.readyState==4){
		document.getElementById("cpncodespan").innerHTML = ajaxobj.responseText;
	}
}
function applycert(){
	cpncode = document.getElementById("cpncode").value;
	if(cpncode!=""){
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange = applycertcallback;
		document.getElementById("cpncodespan").innerHTML = "<?php print $xxAplyng?>...";
		ajaxobj.open("GET", "vsadmin/ajaxservice.php?action=applycert&cpncode="+cpncode, true);
		ajaxobj.send(null);
	}
}
function removecert(cpncode){
	if(cpncode!=""){
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange = applycertcallback;
		document.getElementById("cpncodespan").innerHTML = "<?php print $xxDeltng?>...";
		ajaxobj.open("GET", "vsadmin/ajaxservice.php?action=applycert&act=delete&cpncode="+cpncode, true);
		ajaxobj.send(null);
		document.getElementById("cpncode").value="";
	}
}
function dosavestate(shp){
	thestate = eval('document.forms.mainform.'+shp+'state');
	eval(shp+'savestate = thestate.selectedIndex');
}
function checkoutspan(shp){
if(shp=='s' && document.getElementById('saddress').value=="")visib='hidden';else visib='visible';<?php
if($nonhomecountries>0) print "thestyle = document.getElementById(shp+'outspan').style;\r\n";
if($numallstates>0){
	print "theddstyle = document.getElementById(shp+'outspandd').style;\r\n";
	print "thestate = eval('document.forms.mainform.'+shp+'state');\r\n";
}
if($origCountryCode!="US" && $origCountryCode!="CA" && @$dynamicuscastates==TRUE){
	print "dynamiccountries(document.getElementById(shp+'country'),shp+'state');\r\n";
	print "if(stateselectordisabled[shp=='s'?1:0]==false){\r\n";
}else
	print "if(document.getElementById(shp+'country').selectedIndex < numhomecountries){\r\n";
if($nonhomecountries>0) print "thestyle.visibility='hidden';\r\n";
if($numallstates>0){
	print "theddstyle.visibility=visib;\r\n";
	print "thestate.disabled=false;\r\n";
	print "eval('thestate.selectedIndex='+shp+'savestate');\r\n";
} ?>
}else{<?php
if($nonhomecountries>0) print "thestyle.visibility=visib;\r\n";
if($numallstates>0){ ?>
theddstyle.visibility="hidden";
if(thestate.disabled==false){
thestate.disabled=true;
eval(shp+'savestate = thestate.selectedIndex');
thestate.selectedIndex=0;}
<?php	} ?>
}}
<?php
	if($origCountryCode!="US" && $origCountryCode!="CA" && @$dynamicuscastates==TRUE) createdynamicstates();
	if($numallstates>0 && ! is_array($addresses)) print "savestate = document.forms.mainform.state.selectedIndex;\r\n";
	if(is_array($addresses)) print "checkaddress=false;scheckaddress=false;\r\n";
	if(is_array($addresses) && @$noshipaddress!=TRUE) print "checkeditbutton('s');";
	print 'numhomecountries=' . $numhomecountries . ";checkoutspan('');\r\n";
	if(@$noshipaddress!=TRUE && ! is_array($addresses)){
		if($numallstates>0) print "ssavestate = document.forms.mainform.sstate.selectedIndex;\r\n";
		print "checkoutspan('s')\r\n";
	}
?>/* ]]> */</script><?php
}elseif($checkoutmode=='go' || $paypalexpress){
	if(@$_POST['orderid']!='' && is_numeric(@$_POST['orderid']) && @$_POST['sessionid']!=''){
		retrieveorderdetails(@$_POST['orderid'], @$_POST['sessionid']);
	}elseif(! $paypalexpress){
		if((@$enableclientlogin==TRUE || @$forceclientlogin==TRUE) && @$_SESSION['clientID']!=''){
			$sSQL = "SELECT clEmail FROM customerlogin WHERE clEmail<>'' AND clID='" . escape_string($_SESSION['clientID']) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)) $ordEmail = trim($rs['clEmail']); else $ordEmail = cleanupemail(unstripslashes(@$_POST['email']));
			mysql_free_result($result);
		}else
			$ordEmail = cleanupemail(unstripslashes(@$_POST['email']));
		if((@$enableclientlogin==TRUE || @$forceclientlogin==TRUE) && @$_POST['addressid']!='' && @$_POST['addaddress']=='' && @$_SESSION['clientID']!=''){
			$sSQL = "SELECT addName,addLastName,addAddress,addAddress2,addCity,addState,addZip,addCountry,addPhone,addExtra1,addExtra2 FROM address WHERE addCustID='" . escape_string($_SESSION['clientID']) . "' AND addID='" . escape_string(@$_POST['addressid']) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$ordName = $rs['addName'];
				$ordLastName = $rs['addLastName'];
				$ordAddress = $rs['addAddress'];
				$ordAddress2 = $rs['addAddress2'];
				$ordCity = $rs['addCity'];
				$ordState = $rs['addState'];
				$ordZip = $rs['addZip'];
				$ordCountry = $rs['addCountry'];
				$ordPhone = $rs['addPhone'];
				$ordExtra1 = $rs['addExtra1'];
				$ordExtra2 = $rs['addExtra2'];
				mysql_query("UPDATE address SET addIsDefault=0 WHERE addCustID='" . escape_string($_SESSION['clientID']) . "'") or print(mysql_error());
				mysql_query("UPDATE address SET addIsDefault=1 WHERE addCustID='" . escape_string($_SESSION['clientID']) . "' AND addID='" . escape_string(@$_POST['addressid']) . "'") or print(mysql_error());
			}
			mysql_free_result($result);
		}else{
			$ordName = (unstripslashes(@$_POST['name'])==$xxFirNam ? '' : strip_tags(unstripslashes(@$_POST['name'])));
			$ordLastName = (unstripslashes(@$_POST['lastname'])==$xxLasNam ? '' : strip_tags(unstripslashes(@$_POST['lastname'])));
			$ordAddress = strip_tags(unstripslashes(@$_POST['address']));
			$ordAddress2 = strip_tags(unstripslashes(@$_POST['address2']));
			$ordCity = strip_tags(unstripslashes(@$_POST['city']));
			if(trim(@$_POST['state'])!='')
				$ordState = strip_tags(unstripslashes(@$_POST['state']));
			else
				$ordState = strip_tags(unstripslashes(@$_POST['state2']));
			$ordZip = strip_tags(unstripslashes(@$_POST['zip']));
			$ordCountry = strip_tags(unstripslashes(@$_POST['country']));
			$ordPhone = strip_tags(unstripslashes(@$_POST['phone']));
			$ordExtra1 = strip_tags(unstripslashes(@$_POST['ordextra1']));
			$ordExtra2 = strip_tags(unstripslashes(@$_POST['ordextra2']));
		}
		if(@$_POST['allowemail']=='ON') addtomailinglist($ordEmail,trim($ordName.' '.$ordLastName));
		if((@$enableclientlogin==TRUE || @$forceclientlogin==TRUE) && @$_POST['saddressid']!='' && @$_POST['saddaddress']=='' && @$_SESSION['clientID']!=''){
			$sSQL = "SELECT addName,addLastName,addAddress,addAddress2,addCity,addState,addZip,addCountry,addPhone,addExtra1,addExtra2 FROM address WHERE addCustID='" . escape_string($_SESSION['clientID']) . "' AND addID='" . escape_string($_POST['saddressid']) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$ordShipName = $rs['addName'];
				$ordShipLastName = $rs['addLastName'];
				$ordShipAddress = $rs['addAddress'];
				$ordShipAddress2 = $rs['addAddress2'];
				$ordShipCity = $rs['addCity'];
				$ordShipState = $rs['addState'];
				$ordShipZip = $rs['addZip'];
				$ordShipCountry = $rs['addCountry'];
				$ordShipPhone = $rs['addPhone'];
				$ordShipExtra1 = $rs['addExtra1'];
				$ordShipExtra2 = $rs['addExtra2'];
			}
			mysql_free_result($result);
		}else{
			if((@$_SESSION['clientID']=='' && (@$hideshipaddress!=TRUE || @$_POST['shipdiff']=='1')) || (@$_SESSION['clientID']!='' && (@$_POST['saddaddress']=='add' || @$_POST['saddaddress']=='edit'))){
				$ordShipName = (unstripslashes(@$_POST['sname'])==$xxFirNam ? '' : strip_tags(unstripslashes(@$_POST['sname'])));
				$ordShipLastName = (unstripslashes(@$_POST['slastname'])==$xxLasNam ? '' : strip_tags(unstripslashes(@$_POST['slastname'])));
				$ordShipAddress = strip_tags(unstripslashes(@$_POST['saddress']));
				$ordShipAddress2 = strip_tags(unstripslashes(@$_POST['saddress2']));
				$ordShipCity = strip_tags(unstripslashes(@$_POST['scity']));
				$ordShipState = strip_tags(unstripslashes(@$_POST['sstate'.(trim(@$_POST['sstate'])==''?'2':'')]));
				$ordShipZip = strip_tags(unstripslashes(@$_POST['szip']));
				$ordShipCountry = strip_tags(unstripslashes(@$_POST['scountry']));
				$ordShipPhone = strip_tags(unstripslashes(@$_POST['sphone']));
				$ordShipExtra1 = strip_tags(unstripslashes(@$_POST['ordsextra1']));
				$ordShipExtra2 = strip_tags(unstripslashes(@$_POST['ordsextra2']));
			}
		}
		if(@$_SESSION['clientID']!=''){
			if(@$_POST['addaddress']=='add'){
				$sSQL = "INSERT INTO address (addCustID,addIsDefault,addName,addLastName,addAddress,addAddress2,addCity,addState,addZip,addCountry,addPhone,addExtra1,addExtra2) VALUES ('" . escape_string($_SESSION['clientID']) . "',0,'".escape_string($ordName)."','".escape_string($ordLastName)."','".escape_string($ordAddress)."','".escape_string($ordAddress2)."','".escape_string($ordCity)."','".escape_string($ordState)."','".escape_string($ordZip)."','".escape_string($ordCountry)."','".escape_string($ordPhone)."','".escape_string($ordExtra1)."','".escape_string($ordExtra2)."')";
				mysql_query($sSQL) or print(mysql_error());
			}elseif(@$_POST['addaddress']=='edit'){
				$sSQL = "UPDATE address SET addName='".escape_string($ordName)."',addLastName='".escape_string($ordLastName)."',addAddress='".escape_string($ordAddress)."',addAddress2='".escape_string($ordAddress2)."',addCity='".escape_string($ordCity)."',addState='".escape_string($ordState)."',addZip='".escape_string($ordZip)."',addCountry='".escape_string($ordCountry)."',addPhone='".escape_string($ordPhone)."',addExtra1='".escape_string($ordExtra1)."',addExtra2='".escape_string($ordExtra2)."' WHERE addCustID='" . escape_string(@$_SESSION['clientID']) . "' AND addID='" . escape_string(@$_POST['addressid']) . "'";
				mysql_query($sSQL) or print(mysql_error());
			}
			if($ordShipName!='' && $ordShipAddress!='' && $ordShipCity!=''){
				if(@$_POST['saddaddress']=='add'){
					$sSQL = "INSERT INTO address (addCustID,addIsDefault,addName,addLastName,addAddress,addAddress2,addCity,addState,addZip,addCountry,addPhone,addExtra1,addExtra2) VALUES ('" . escape_string($_SESSION['clientID']) . "',0,'".escape_string($ordShipName)."','".escape_string($ordShipLastName)."','".escape_string($ordShipAddress)."','".escape_string($ordShipAddress2)."','".escape_string($ordShipCity)."','".escape_string($ordShipState)."','".escape_string($ordShipZip)."','".escape_string($ordShipCountry)."','".escape_string($ordShipPhone)."','".escape_string($ordShipExtra1)."','".escape_string($ordShipExtra2)."')";
					mysql_query($sSQL) or print(mysql_error());
				}elseif(@$_POST['saddaddress']=='edit'){
					$sSQL = "UPDATE address SET addName='".escape_string($ordShipName)."',addLastName='".escape_string($ordShipLastName)."',addAddress='".escape_string($ordShipAddress)."',addAddress2='".escape_string($ordShipAddress2)."',addCity='".escape_string($ordShipCity)."',addState='".escape_string($ordShipState)."',addZip='".escape_string($ordShipZip)."',addCountry='".escape_string($ordShipCountry)."',addPhone='".escape_string($ordShipPhone)."',addExtra1='".escape_string($ordShipExtra1)."',addExtra2='".escape_string($ordShipExtra2)."' WHERE addCustID='" . escape_string(@$_SESSION['clientID']) . "' AND addID='" . escape_string(@$_POST['addressid']) . "'";
					mysql_query($sSQL) or print(mysql_error());
				}
			}
		}
		$ordAddInfo = unstripslashes(@$_POST['ordAddInfo']);
		if($commercialloc_) $ordComLoc = 1; else $ordComLoc = 0;
		if($wantinsurance_ || abs(@$addshippinginsurance)==1) $ordComLoc += 2;
		if($saturdaydelivery_) $ordComLoc += 4;
		if($signaturerelease_) $ordComLoc += 8;
		if($insidedelivery_) $ordComLoc += 16;
		$ordAffiliate = trim(strip_tags(substr(unstripslashes(@$_POST['PARTNER']),0,48)));
		$ordCheckoutExtra1 = trim(strip_tags(substr(unstripslashes(@$_POST['ordcheckoutextra1']),0,255)));
		$ordCheckoutExtra2 = trim(strip_tags(substr(unstripslashes(@$_POST['ordcheckoutextra2']),0,255)));
	}
	if($ordShipAddress!=''){
		$shipcountry = $ordShipCountry;
		$shipstate = $ordShipState;
		$destZip = $ordShipZip;
	}else{
		$shipcountry = $ordCountry;
		$shipstate = $ordState;
		$destZip = $ordZip;
		if(@$autobillingtoshipping==TRUE){
			$ordShipName = @$ordName;
			$ordShipLastName = @$ordLastName;
			$ordShipAddress = @$ordAddress;
			$ordShipAddress2 = @$ordAddress2;
			$ordShipCity = @$ordCity;
			$ordShipState = @$ordState;
			$ordShipZip = @$ordZip;
			$ordShipCountry = @$ordCountry;
			$ordShipPhone = @$ordPhone;
			$ordShipExtra1 = @$ordExtra1;
			$ordShipExtra2 = @$ordExtra2;
		}
	}
	$sSQL = "SELECT countryID,countryCode,countryOrder FROM countries WHERE countryName='" . escape_string($ordCountry) . "'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		$countryID = $rs['countryID'];
		$countryCode = $rs['countryCode'];
		$homecountry = ($rs['countryOrder']>=2);
	}
	mysql_free_result($result);
	if(! $homecountry) $perproducttaxrate=FALSE;
	$sSQL = "SELECT countryID,countryTax,countryCode,countryFreeShip,countryOrder FROM countries WHERE countryName='" . escape_string($shipcountry) . "'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		$countryTaxRate = $rs['countryTax'];
		$shipCountryID = $rs['countryID'];
		$shipCountryCode = $rs['countryCode'];
		$freeshipavailtodestination = ($rs['countryFreeShip']==1);
		$shiphomecountry = ($rs['countryOrder']>=2);
	}
	mysql_free_result($result);
	if($homecountry){
		$sSQL = "SELECT stateTax,stateAbbrev FROM states WHERE ".(@$usestateabbrev==TRUE?'stateAbbrev':'stateName')."='" . escape_string($ordState) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result))
			$stateAbbrev=$rs['stateAbbrev'];
		mysql_free_result($result);
	}
	if($shiphomecountry){
		$sSQL = "SELECT stateTax,stateAbbrev,stateFreeShip FROM states WHERE ".(@$usestateabbrev==TRUE?'stateAbbrev':'stateName')."='" . escape_string($shipstate) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)){
			$stateTaxRate=$rs['stateTax'];
			$shipStateAbbrev=$rs['stateAbbrev'];
			$freeshipavailtodestination = ($freeshipavailtodestination && ($rs['stateFreeShip']==1));
		}
		mysql_free_result($result);
	}
	if(($shipType==4 || $shipType==7 || $shipType==8) && $shipCountryID==1 && @$shipStateAbbrev=='GU') $shipCountryCode='GU';
	if(trim(@$_SESSION["clientUser"])!=''){
		if(((int)$_SESSION["clientActions"] & 1)==1) $stateTaxRate=0;
		if(((int)$_SESSION["clientActions"] & 2)==2) $countryTaxRate=0;
	}
	getpayprovhandling();
	initshippingmethods();
	$orderid=''; $ordauthstatus='';
	$sSQL = "SELECT ordID,ordAuthStatus FROM orders WHERE ordStatus>1 AND ordAuthNumber='' AND " . getordersessionsql();
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){ $orderid=$rs['ordID']; $ordauthstatus=$rs['ordAuthStatus']; }
	mysql_free_result($result);
	if($orderid=='' && ($ordPayProvider=='' || !is_numeric($ordPayProvider) || trim($ordName.$ordLastName)=='')) $success=FALSE;
	$sSQL = "SELECT cartID,cartProdID,cartProdPrice,cartQuantity,pWeight,pShipping,pShipping2,pExemptions,pSection,topSection,pDims,pTax FROM cart LEFT JOIN products ON cart.cartProdID=products.pId LEFT OUTER JOIN sections ON products.pSection=sections.sectionID WHERE cartCompleted=0 AND " . getsessionsql();
	$allcart = mysql_query($sSQL) or print(mysql_error());
	if($success && (($itemsincart=mysql_num_rows($allcart))>0)){
		$rowcounter = 0;
		$index=0;
		while($rsCart=mysql_fetch_assoc($allcart)){
			$index++;
			if(is_null($rsCart['pWeight'])) $rsCart['pWeight']=0;
			if(($rsCart['cartProdID']==$giftcertificateid || $rsCart['cartProdID']==$donationid) && is_null($rsCart['pExemptions'])) $rsCart['pExemptions']=15;
			$sSQL = "SELECT SUM(coPriceDiff) AS coPrDff FROM cartoptions WHERE coCartID=". $rsCart['cartID'];
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$rsCart['cartProdPrice'] += (double)$rs['coPrDff'];
			}
			mysql_free_result($result);
			$sSQL = 'SELECT SUM(coWeightDiff) AS coWghtDff FROM cartoptions WHERE coCartID='. $rsCart['cartID'];
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$rsCart['pWeight'] += (double)$rs['coWghtDff'];
			}
			mysql_free_result($result);
			$runTot=$rsCart['cartProdPrice'] * (int)($rsCart['cartQuantity']);
			$totalquantity += (int)($rsCart['cartQuantity']);
			$totalgoods += $runTot;
			$thistopcat=0;
			if(trim(@$_SESSION['clientID'])!='') $rsCart['pExemptions'] = ((int)$rsCart['pExemptions'] | ((int)$_SESSION['clientActions'] & 7));
			if(($shipType==2 || $shipType==3 || $shipType==4 || $shipType==6 || $shipType==7 || $shipType==8) && (double)$rsCart['pWeight']<=0.0)
				$rsCart['pExemptions'] = ($rsCart['pExemptions'] | 4);
			if(($rsCart['pExemptions'] & 1)==1) $statetaxfree += $runTot;
			if(($rsCart['pExemptions'] & 8)!=8){ $handlingeligableitem=TRUE; $handlingeligablegoods += $runTot; }
			if(@$perproducttaxrate==TRUE){
				if(is_null($rsCart['pTax'])) $rsCart['pTax'] = $countryTaxRate;
				if(($rsCart['pExemptions'] & 2)!=2) $countryTax += (($rsCart['pTax'] * $runTot) / 100.0);
			}else{
				if(($rsCart['pExemptions'] & 2)==2) $countrytaxfree += $runTot;
			}
			if(($rsCart['pExemptions'] & 4)==4) $shipfreegoods += $runTot;
			addproducttoshipping($rsCart, $index);
		}
		calculatediscounts(round($totalgoods,2),TRUE,$rgcpncode);
		calculateshipping();
		if(! $fromshipselector) insuranceandtaxaddedtoshipping();
		calculateshippingdiscounts(TRUE);
		if(@$_SESSION['clientID']!='' && @$_SESSION['clientActions']!=0) $cpnmessage .= $xxLIDis . strip_tags(str_replace('"','',$_SESSION['clientUser'])) . '<br />';
		$cpnmessage = substr($cpnmessage,6);
		calculatetaxandhandling();
		$totalgoods = round($totalgoods,(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
		$shipping = round($shipping,(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
		$freeshipamnt = round($freeshipamnt,(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
		$loyaltypointsused=0;
		if(@$loyaltypoints!='' && @$_SESSION['clientID']!='' && @$_SESSION['noredeempoints']!=TRUE){
			if($orderid!=''){
				$pointsRedeemed=0;
				$result = mysql_query("SELECT pointsRedeemed FROM orders WHERE ordID=" . $orderid) or print(mysql_error());
				if($rs = mysql_fetch_assoc($result))
					$pointsRedeemed=$rs['pointsRedeemed'];
				mysql_free_result($result);
				if($pointsRedeemed>0){
					mysql_query("UPDATE customerlogin SET loyaltyPoints=loyaltyPoints+" . $pointsRedeemed . " WHERE clID=" . $_SESSION['clientID']) or print(mysql_error());
					mysql_query("UPDATE orders SET loyaltyPoints=0 WHERE ordID=" . $orderid) or print(mysql_error());
				}
			}
			$result = mysql_query("SELECT loyaltyPoints FROM customerlogin WHERE clID=" . $_SESSION['clientID']) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result))
				$loyaltypointsused=$rs['loyaltyPoints'];
			mysql_free_result($result);
			if($loyaltypointsused>0){
				$loyaltypointdiscount = $loyaltypointsused*$loyaltypointvalue;
				if($loyaltypointdiscount>$totalgoods-$totaldiscounts){ $loyaltypointdiscount=$totalgoods-$totaldiscounts; $loyaltypointsused=(int)($loyaltypointdiscount/$loyaltypointvalue); }
				$totaldiscounts+=round($loyaltypointdiscount,2);
				mysql_query("UPDATE customerlogin SET loyaltyPoints=loyaltyPoints-" . $loyaltypointsused . " WHERE clID=" . $_SESSION['clientID']) or print(mysql_error());
				$cpnmessage .= $xxLoyPod . ': ' . FormatEuroCurrency($loyaltypointdiscount) . "<br />";
			}
		}
		if($totaldiscounts > $totalgoods) $totaldiscounts = $totalgoods;
		if(@$addshippingtodiscounts){
			$totaldiscounts += $freeshipamnt;
			$freeshipamnt = 0;
		}
		$totaldiscounts = round($totaldiscounts,(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
		$grandtotal = round(($totalgoods + $shipping + $stateTax + $countryTax + $handling) - ($totaldiscounts + $freeshipamnt),(@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2));
		if($grandtotal < 0) $grandtotal = 0;
		if($ordShipName=='' && $ordShipLastName=='' && $ordShipAddress=='' && $ordShipAddress2=='' && $ordShipCity=='') $ordShipCountry='';
		if(@$_POST['shipselectoraction']!='') $stockwarning=FALSE; else $stockwarning = do_stock_check(FALSE);
		if($stockwarning){
			$checkoutmode='';
			$isInStock = FALSE;
			if(ob_get_length()!==FALSE)
				header('Location: ' . $storeurl . 'cart.php');
			else
				print '<meta http-equiv="Refresh" content="0; URL=' . $storeurl . 'cart.php"></body></html>';
			flush();
			exit;
		}
		if(($success || @$_POST['shipselectoraction']=='') && ! $stockwarning){
			if($orderid==''){
				$isneworder=TRUE;
				$referer = @$_SESSION['httpreferer'];
				if(strpos(strtolower($referer), str_replace(array('http://','https://'),'',strtolower($storeurl)))!==FALSE || (@$pathtossl!=''&&strpos(strtolower($referer), str_replace(array('http://','https://'),'',strtolower(@$pathtossl)))!==FALSE)) $referer='';
				$referarr = explode('?', $referer, 2);
				$sSQL = 'INSERT INTO orders (ordSessionID,ordClientID,ordName,ordLastName,ordAddress,ordAddress2,ordCity,ordState,ordZip,ordCountry,ordEmail,ordPhone,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipZip,ordShipCountry,ordShipPhone,ordPayProvider,ordAuthNumber,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordHandling,ordShipType,ordShipCarrier,ordTotal,ordDate,ordStatus,ordAuthStatus,pointsRedeemed,ordStatusDate,ordComLoc,ordIP,ordAffiliate,ordExtra1,ordExtra2,ordShipExtra1,ordShipExtra2,ordCheckoutExtra1,ordCheckoutExtra2,ordAVS,ordCVV,ordLang,ordReferer,ordQuerystr,ordDiscount,ordDiscountText,ordAddInfo) VALUES (' .
					"'" . escape_string($thesessionid) . "'," .
					"'" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "'," .
					"'" . escape_string($ordName) . "','" . escape_string($ordLastName) . "'," .
					"'" . escape_string($ordAddress) . "','" . escape_string($ordAddress2) . "'," .
					"'" . escape_string($ordCity) . "'," .
					"'" . escape_string($ordState) . "'," .
					"'" . escape_string($ordZip) . "'," .
					"'" . escape_string($ordCountry) . "'," .
					"'" . escape_string($ordEmail) . "'," .
					"'" . escape_string($ordPhone) . "'," .
					"'" . escape_string($ordShipName) . "','" . escape_string($ordShipLastName) . "'," .
					"'" . escape_string($ordShipAddress) . "','" . escape_string($ordShipAddress2) . "'," .
					"'" . escape_string($ordShipCity) . "'," .
					"'" . escape_string($ordShipState) . "'," .
					"'" . escape_string($ordShipZip) . "'," .
					"'" . escape_string($ordShipCountry) . "'," .
					"'" . escape_string($ordShipPhone) . "'," .
					"'" . escape_string($ordPayProvider) . "'," .
					"''," .
					"'" . escape_string($shipping-$freeshipamnt) . "',";
				if($usehst)
					$sSQL .= '0,0,' . ($stateTax + $countryTax) . ',';
				else
					$sSQL .= "'" . escape_string($stateTax) . "','" . escape_string($countryTax) . "',0,";
				$sSQL .= "'" . escape_string($handling) . "'," .
					"'" . escape_string($shipMethod) . "'," .
					"'" . escape_string($shipType) . "'," .
					"'" . escape_string($totalgoods) . "'," .
					"'" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "'," .
					"2,''," . $loyaltypointsused . ',' . // ordStatus,ordAuthStatus,pointsRedeemed
					"'" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "'," .
					"'" . $ordComLoc . "'," .
					"'" . escape_string(getipaddress()) . "'," .
					"'" . escape_string($ordAffiliate) . "'," .
					"'" . escape_string($ordExtra1) . "','" . escape_string($ordExtra2) . "'," .
					"'" . escape_string($ordShipExtra1) . "','" . escape_string($ordShipExtra2) . "'," .
					"'" . escape_string($ordCheckoutExtra1) . "','" . escape_string($ordCheckoutExtra2) . "'," .
					"'" . escape_string($ordAVS) . "'," .
					"'" . escape_string($ordCVV) . "'," .
					"'" . escape_string((@$languageid==''?1:$languageid)-1) . "'," .
					"'" . escape_string($referarr[0]) . "','" . escape_string(@$referarr[1]) . "'," .
					"'" . escape_string($totaldiscounts) . "'," .
					"'" . escape_string(substr($cpnmessage,0,255)) . "'," .
					"'" . escape_string($ordAddInfo) . "')";
				mysql_query($sSQL) or print(mysql_error());
				$orderid = mysql_insert_id();
			}else{
				$isneworder=FALSE;
				$sSQL = 'UPDATE orders SET ';
				if(@$_POST['shipselectoraction']==''){
					$sSQL .= "ordSessionID='" . escape_string($thesessionid) . "'," .
						"ordClientID='" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "'," .
						"ordName='" . escape_string($ordName) . "',ordLastName='" . escape_string($ordLastName) . "'," .
						"ordAddress='" . escape_string($ordAddress) . "',ordAddress2='" . escape_string($ordAddress2) . "'," .
						"ordCity='" . escape_string($ordCity) . "'," .
						"ordState='" . escape_string($ordState) . "'," .
						"ordZip='" . escape_string($ordZip) . "'," .
						"ordCountry='" . escape_string($ordCountry) . "'," .
						"ordEmail='" . escape_string($ordEmail) . "'," .
						"ordPhone='" . escape_string($ordPhone) . "'," .
						"ordShipName='" . escape_string($ordShipName) . "',ordShipLastName='" . escape_string($ordShipLastName) . "'," .
						"ordShipAddress='" . escape_string($ordShipAddress) . "',ordShipAddress2='" . escape_string($ordShipAddress2) . "'," .
						"ordShipCity='" . escape_string($ordShipCity) . "'," .
						"ordShipState='" . escape_string($ordShipState) . "'," .
						"ordShipZip='" . escape_string($ordShipZip) . "'," .
						"ordShipCountry='" . escape_string($ordShipCountry) . "'," .
						"ordShipPhone='" . escape_string($ordShipPhone) . "'," .
						"ordPayProvider='" . escape_string($ordPayProvider) . "'," .
						"ordAuthNumber=''," . // Not yet authorized
						"ordTotal='" . $totalgoods . "'," .
						"ordComLoc=" . $ordComLoc . "," .
						"ordIP='" . escape_string(getipaddress()) . "'," .
						"ordAffiliate='" . escape_string($ordAffiliate) . "'," .
						"ordExtra1='" . escape_string($ordExtra1) . "',ordExtra2='" . escape_string($ordExtra2) . "'," .
						"ordShipExtra1='" . escape_string($ordShipExtra1) . "',ordShipExtra2='" . escape_string($ordShipExtra2) . "'," .
						"ordCheckoutExtra1='" . escape_string($ordCheckoutExtra1) . "',ordCheckoutExtra2='" . escape_string($ordCheckoutExtra2) . "'," .
						"ordAVS='" . escape_string($ordAVS) . "'," .
						"ordCVV='" . escape_string($ordCVV) . "'," .
						"ordLang='" . escape_string((@$languageid==''?1:$languageid)-1) . "'," .
						"ordDiscount='" . $totaldiscounts . "'," .
						"ordAddInfo='" . escape_string($ordAddInfo) . "',";
				}
				$sSQL .= "ordDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "'," .
					"ordStatusDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "'," .
					"ordShipping='" . ($shipping - $freeshipamnt) . "'," .
					"ordDiscountText='" . escape_string(substr($cpnmessage,0,255)) . "',";
				if($usehst)
					$sSQL .= "ordStateTax=0,ordCountryTax=0,ordHSTTax=" . ($stateTax + $countryTax) . ",";
				else
					$sSQL .= "ordStateTax='" . $stateTax . "',ordCountryTax='" . $countryTax . "',ordHSTTax=0,";
				$sSQL .= "ordHandling='" . $handling . "'," .
					"ordShipType='" . escape_string($shipMethod) . "',ordShipCarrier='" . $shipType . "',ordAuthStatus='',pointsRedeemed=" . $loyaltypointsused .
					" WHERE ordID='" . $orderid . "'";
				mysql_query($sSQL) or print(mysql_error());
			}
			$sSQL="UPDATE cart SET cartOrderID=". $orderid . " WHERE cartCompleted=0 AND " . getsessionsql();
			mysql_query($sSQL) or print(mysql_error());
			if($isneworder || $ordauthstatus=='MODWARNOPEN') stock_subtract($orderid);
			$sSQL = "SELECT gcaGCID,gcaAmount FROM giftcertsapplied WHERE gcaOrdID='" . $orderid . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_assoc($result)){
				mysql_query("UPDATE giftcertificate SET gcRemaining=gcRemaining+".round($rs['gcaAmount'], 2)." WHERE gcID='".$rs['gcaGCID']."'");
			}
			mysql_free_result($result);
			mysql_query("DELETE FROM giftcertsapplied WHERE gcaOrdID='" . $orderid . "'");
			if(@$_SESSION['giftcerts']!='' && $grandtotal > 0){
				$sSQL = "SELECT gcID,gcRemaining FROM giftcertificate WHERE gcRemaining>0 AND gcAuthorized<>0 AND gcID IN ('" . str_replace(' ',"','",escape_string(@$_SESSION['giftcerts'])) . "')";
				$result = mysql_query($sSQL) or print(mysql_error());
				while($rs = mysql_fetch_assoc($result)){
					if($giftcertsamount >= $grandtotal) break;
					$thiscertamount = min($grandtotal-$giftcertsamount, $rs['gcRemaining']);
					mysql_query("INSERT INTO giftcertsapplied (gcaGCID,gcaOrdID,gcaAmount) VALUES ('" . $rs['gcID'] . "','" . $orderid . "'," . $thiscertamount . ')');
					mysql_query("UPDATE giftcertificate SET gcRemaining=gcRemaining-" . round($thiscertamount, 2) . ",gcDateUsed='" . date("Y-m-d", time() + ($dateadjust*60*60)) . "' WHERE gcID='" . $rs['gcID'] . "'");
					$giftcertsamount += $thiscertamount;
				}
				mysql_free_result($result);
				$totaldiscounts += $giftcertsamount;
				$grandtotal -= $giftcertsamount;
				$cpnmessage .= $xxAppGC . ' ' . FormatEuroCurrency($giftcertsamount) . ($cpnmessage!='' ? '<br />' : '');
				$sSQL = "UPDATE orders SET ordDiscount=" . $totaldiscounts . ",ordDiscountText='" . escape_string($cpnmessage) . "' WHERE ordID='" . $orderid . "'";
				mysql_query($sSQL) or print(mysql_error());
			}
			$descstr='';
			$addcomma = '';
			$sSQL="SELECT cartID,cartProdID,cartQuantity,cartProdName FROM cart WHERE cartOrderID=" . $orderid . " AND cartCompleted=0";
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs=mysql_fetch_assoc($result)){
				if($rs['cartProdID']==$giftcertificateid) mysql_query("UPDATE giftcertificate SET gcOrderID='" . $orderid . "' WHERE gcCartID='" . $rs['cartID'] . "'") or print(mysql_error());
				$descstr .= $addcomma . $rs['cartQuantity'] . ' ' . $rs['cartProdName'];
				$addcomma = ', ';
			}
			mysql_free_result($result);
			$descstr = str_replace('"','',$descstr);
			if(! $fromshipselector){
				mysql_query("DELETE FROM shipoptions WHERE soOrderID=".$orderid." OR soDateAdded<'".date('Y-m-d', time() - (24*60*60))."'") or print(mysql_error());
				saveshippingoptions();
			}
			if(@$_POST['remember']=='1')
				print '<script type="text/javascript" src="vsadmin/savecookie.php?id1=' . $orderid . '&amp;id2=' . trim($thesessionid) . '"></script>';
		}
	}else
		$success=FALSE;
	mysql_free_result($allcart);
	if($stockwarning){
		$success=FALSE;
	}elseif($ordPayProvider!=''){
		$blockuser=checkuserblock($ordPayProvider);
		if($blockuser){
			$orderid = 0;
			$thesessionid = '';
			$xxMstClk = $multipurchaseblockmessage;
		}else
			getpayprovdetails($ordPayProvider,$data1,$data2,$data3,$demomode,$ppmethod);
		$origstoreurl = $storeurl;
		if(@$pathtossl!=''){
			if(substr($pathtossl,-1)!="/") $pathtossl .= "/";
			$storeurl = $pathtossl;
		}
		if(@$wpconfirmpage=='') $wpconfirmpage='wpconfirm.php';
		if($success==FALSE){
			print '<form method="post" action="cart.php">';
		}elseif($grandtotal > 0 && $ordPayProvider=='1'){ // PayPal
			$php_version = explode('.', phpversion());
			if(((int)$php_version[0])<5) print 'Please note, you are using PHP 4 and PHP 5 is required for PayPal.';
			if(@$paypalhostedsolution){
				print '<form method="post" action="https://securepayments.' . ($demomode ? 'sandbox.' : '') . 'paypal.com/cgi-bin/acquiringweb">';
				print whv('cmd', '_hosted-payment');
			}else{
				print '<form method="post" action="https://www.'.($demomode?'sandbox.':'').'paypal.com/cgi-bin/webscr">';
				print whv('cmd', '_ext-enter') . whv('redirect_cmd', '_xclick') . whv('rm', '2');
			}
			print whv('business', $data1) . whv('return', $storeurl.'thanks.php');
			print whv('notify_url', $storeurl.'vsadmin/ppconfirm.php') . whv('item_name', substr($descstr,0,127)) . whv('custom', $orderid) . whv('no_note','1');
			if(@$paypallc!='') print whv('lc', $paypallc);
			if(@$paypalhostedsolution){
				print whv('subtotal', number_format($grandtotal, (@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2),'.',''));
			}elseif(@$splitpaypalshipping){
				print whv('shipping', number_format(round(($shipping + $handling) - $freeshipamnt, 2), (@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2),'.',''));
				print whv('amount', number_format(round(($totalgoods + $stateTax + $countryTax) - $totaldiscounts, 2), (@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2),'.',''));
			}else{
				print whv('amount', number_format($grandtotal, (@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2),'.',''));
			}
			print whv('currency_code', $countryCurrency) . whv('bn', 'ecommercetemplates_Cart_WPS_US');
			if(@$usefirstlastname){
				print whv('first_name', $ordName) . whv('last_name', $ordLastName);
				if(@$paypalhostedsolution) print whv('billing_first_name', $ordName) . whv('billing_last_name', $ordLastName);
			}elseif(strpos(trim($ordName), ' ')!==FALSE){
				$namearr = explode(' ',trim($ordName),2);
				print whv('first_name', $namearr[0]) . whv('last_name', $namearr[1]);
				if(@$paypalhostedsolution) print whv('billing_first_name', $namearr[0]) . whv('billing_last_name', $namearr[1]);
			}else{
				print whv('last_name', $thename);
				if(@$paypalhostedsolution) print whv('billing_last_name', $thename);
			}
			if((trim($ordShipName)!='' || trim($ordShipLastName)!='' || trim($ordShipAddress)!='') && @$paypalhostedsolution)
				print whv('address1', $ordShipAddress) . whv('address2', $ordShipAddress2) . whv('city', $ordShipCity) . whv('state', $shipCountryID==1 && $shipStateAbbrev!='' ? $shipStateAbbrev : $ordShipState) . whv('country', $shipCountryCode) . whv('zip', $ordShipZip);
			else
				print whv('address1', $ordAddress) . whv('address2', $ordAddress2) . whv('city', $ordCity) . whv('state', $countryID==1 && $stateAbbrev!='' ? $stateAbbrev : $ordState) . whv('country', $countryCode) . whv('zip', $ordZip);
			print whv('email', $ordEmail);
			if(@$paypalhostedsolution) print whv('billing_address1', $ordAddress) . whv('billing_address2', $ordAddress2) . whv('billing_city', $ordCity) . whv('billing_state', $countryID==1 && $stateAbbrev!='' ? $stateAbbrev : $ordState) . whv('billing_country', $countryCode) . whv('buyer_email', $ordEmail) . whv('billing_zip', $ordZip);
			print whv('cancel_return', $origstoreurl.'cart.php');
			if($countryCode!='US' && $countryCode!='CA') print whv('night_phone_b', $ordPhone);
			if($ppmethod==1) print whv('paymentaction', 'authorization');
		}elseif($grandtotal > 0 && $ordPayProvider=="2"){ // 2Checkout
			$courl='https://www.2checkout.com/cgi-bin/sbuyers/cartpurchase.2c';
			if(is_numeric($data1))
				if($data1>200000 || @$use2checkoutv2==TRUE) $courl='https://www2.2checkout.com/2co/buyer/purchase';
			if(@$use2checkoutfastcheckout==TRUE) $courl='https://www.2checkout.com/checkout/spurchase';
			print '<form method="post" action="' . $courl . '">';
			print whv('cart_order_id', $orderid) . whv('merchant_order_id', $orderid) . whv('sid', $data1) . whv('total', $grandtotal) . whv('card_holder_name', trim($ordName.' '.$ordLastName)) . whv('street_address', $ordAddress . (trim($ordAddress2)!='' ? ', ' . unstripslashes($ordAddress2) : ''));
			if($countryID==1 || $countryID==2)
				print whv('city', $ordCity) . whv('state', $ordState);
			else
				print whv('city', $ordCity . ($ordState!='' ? ', ' . $ordState : '')) . whv('state', 'Outside US and Canada');
			print whv('zip', $ordZip) . whv('country', $countryCode) . whv('email', $ordEmail) . whv('phone', $ordPhone);
			print whv('id_type', '1');
			$sSQL = 'SELECT cartID,cartProdID,cartProdName,cartProdPrice,cartQuantity,' . (@$digidownloads==TRUE ? 'pDownload,' : '') . 'pDescription FROM cart LEFT JOIN products on cart.cartProdID=products.pID WHERE cartCompleted=0 AND ' . getsessionsql();
			$result = mysql_query($sSQL) or print(mysql_error());
			$index=1;
			while($rs=mysql_fetch_assoc($result)){
				$thedesc = substr(trim(preg_replace("(\r\n|\n|\r)",'\\n',strip_tags($rs['pDescription']))),0,254);
				if($thedesc=='') $thedesc = substr(trim(preg_replace("(\r\n|\n|\r)",'\\n',strip_tags($rs['cartProdName']))),0,254);
				print whv('c_prod_' . $index, str_replace(',','&#44;',str_replace('"','&quot;',$rs['cartProdID'])) . ',' . $rs['cartQuantity']);
				print whv('c_name_' . $index, str_replace('"','&quot;',strip_tags($rs['cartProdName'])));
				print whv('c_description_' . $index, str_replace('"','&quot;',$thedesc));
				print whv('c_price_' . $index, number_format($rs['cartProdPrice'],2,'.',''));
				if(@$digidownloads==TRUE)
					if(trim($rs['pDownload'])!='') print whv('c_tangible_' . $index, 'N');
				$index++;
			}
			if(trim($ordShipName)!='' || trim($ordShipLastName)!='' || trim($ordShipAddress)!='')
				print whv('ship_name', trim($ordShipName.' '.$ordShipLastName)) . whv('ship_street_address', $ordShipAddress . (trim($ordShipAddress2)!='' ? ', ' . unstripslashes($ordShipAddress2) : '')) . whv('ship_city', $ordShipCity) . whv('ship_state', $ordShipState) . whv('ship_zip', $ordShipZip) . whv('ship_country', $ordShipCountry);
			if($demomode) print whv('demo', 'Y');
			print whv('pay_method', 'CC') . whv('fixed', 'Y');
		}elseif($grandtotal > 0 && $ordPayProvider=="3"){ // Authorize.net SIM
			if(@$authnetemulateurl=='') $authnetemulateurl='https://secure.authorize.net/gateway/transact.dll';
			if(@$secretword!=''){
				$data1 = upsdecode($data1, $secretword);
				$data2 = upsdecode($data2, $secretword);
			}
			print '<form method="post" action="' . $authnetemulateurl . '">';
			print whv('x_Version', '3.0') . whv('x_Login', $data1) . whv('x_Show_Form', 'PAYMENT_FORM');
			if($ppmethod==1) print whv('x_type', 'AUTH_ONLY');
			if(@$usefirstlastname){
				print whv('x_First_Name', $ordName) . whv('x_Last_Name', $ordLastName);
			}elseif(strpos(trim($ordName), ' ')!==FALSE){
				$namearr = explode(' ',trim($ordName),2);
				print whv('x_First_Name', $namearr[0]) . whv('x_Last_Name', $namearr[1]);
			}else
				print whv('x_Last_Name', $ordName);
			$sequence = $orderid;
			if(@$authnetadjust!='') $tstamp = time() + $authnetadjust; else $tstamp = time();
			$fingerprint = vrhmac2($data2, $data1 . "^" . $sequence . "^" . $tstamp . "^" . number_format($grandtotal,2,'.','') . "^");
			print whv('x_fp_sequence', $sequence) . whv('x_fp_timestamp', $tstamp) . whv('x_fp_hash', $fingerprint);
			print whv('x_address', $ordAddress . (trim($ordAddress2)!='' ? ', ' . $ordAddress2 : '')) . whv('x_city', $ordCity) . whv('x_country', $ordCountry);
			print whv('x_phone', $ordPhone) . whv('x_state', $ordState) . whv('x_zip', $ordZip);
			print whv('x_cust_id', $orderid) . whv('x_Invoice_Num', $orderid) . whv('x_ect_ordid', $orderid) . whv('x_email', $ordEmail);
			print whv('x_Description', substr($descstr,0,255));
			if(trim($ordShipName)!='' || trim($ordShipLastName)!='' || trim($ordShipAddress)!=''){
				if(@$usefirstlastname){
					print whv('x_Ship_To_First_Name', $ordShipName) . whv('x_Ship_To_Last_Name', $ordShipLastName);
				}elseif(strpos(trim($ordName), ' ')!==FALSE){
					$namearr = explode(' ',trim($ordShipName),2);
					print whv('x_Ship_To_First_Name', $namearr[0]) . whv('x_Ship_To_Last_Name', $namearr[1]);
				}else
					print whv('x_Ship_To_Last_Name', $ordShipName);
				print whv('x_ship_to_address', $ordShipAddress . (trim($ordShipAddress2)!='' ? ', ' . $ordShipAddress2 : '')) . whv('x_ship_to_city', $ordShipCity) . whv('x_ship_to_country', $ordShipCountry) . whv('x_ship_to_state', $ordShipState) . whv('x_ship_to_zip', $ordShipZip);
			}
			print whv('x_Amount', number_format($grandtotal,2,'.',''));
			print whv('x_Relay_Response', 'True') . whv('x_Relay_URL', $storeurl.'vsadmin/'.$wpconfirmpage);
			if($demomode) print whv('x_Test_Request', 'TRUE');
		}elseif($grandtotal==0 || $ordPayProvider=="4"){ // Email
			print '<form method="post" action="thanks.php">' . whv('emailorder', $orderid) . whv('thesessionid', $thesessionid);
		}elseif($grandtotal > 0 && $ordPayProvider=="17"){ // Email 2
			print '<form method="post" action="thanks.php">' . whv('secondemailorder', $orderid) . whv('thesessionid', $thesessionid);
		}elseif($grandtotal > 0 && $ordPayProvider=="5"){ // WorldPay
			print '<form method="post" action="https://secure' . ($demomode ? '-test' : '') . '.worldpay.com/wcc/purchase">';
			print whv('instId', $data1) . whv('cartId', $orderid) . whv('amount', number_format($grandtotal,2,'.','')) . whv('currency', $countryCurrency);
			print whv('desc', substr($descstr,0,255));
			print whv('name', trim($ordName.' '.$ordLastName)) . whv('address', $ordAddress . (trim($ordAddress2)!='' ? ', ' . $ordAddress2 : '') . '&#10;' . $ordCity . '&#10;' . $ordState);
			print whv('postcode', $ordZip) . whv('country', $countryCode) . whv('tel', $ordPhone) . whv('email', $ordEmail);
			print whv('authMode', $ppmethod==1 ? 'E' : 'A') . whv('testMode', $demomode ? '100' : '0');
			$data2arr = explode('&',$data2);
			$data2 = @$data2arr[0];
			if($data2!=''){
				$sigfields='amount:currency:cartId:testMode';
				print whv('signatureFields', $sigfields) . whv('signature', md5($data2 . ';' . $sigfields . ';' . number_format($grandtotal,2,'.','') . ';' . $countryCurrency . ';' . $orderid . ';' . ($demomode?'100':'0')));
			}
		}elseif($grandtotal > 0 && $ordPayProvider=="6"){ // NOCHEX
			print '<form method="post" action="https://secure.nochex.com/">';
			print whv('merchant_id', $data1);
			print whv('success_url', $storeurl . 'thanks.php?ncretval=' . $orderid . '&ncsessid=' . $thesessionid) . whv('callback_url', $storeurl.'vsadmin/ncconfirm.php');
			print whv('description', substr($descstr,0,255));
			print whv('order_id', $orderid) . whv('amount', number_format($grandtotal,2,'.',''));
			print whv('billing_fullname', trim($ordName.' '.$ordLastName)) . whv('billing_address', $ordAddress . (trim($ordAddress2)!='' ? ', ' . $ordAddress2 : '')) . whv('billing_postcode', $ordZip) . whv('email_address', $ordEmail) . whv('customer_phone_number', $ordPhone);
			if(trim($ordShipName)!='' || trim($ordShipAddress)!=''){
				print whv('delivery_fullname', trim($ordShipName.' '.$ordShipLastName)) . whv('delivery_address', $ordShipAddress . (trim($ordShipAddress2)!='' ? ', ' . $ordShipAddress2 : '')) . whv('delivery_postcode', $ordShipZip);
			}
			if($demomode) print whv('test_transaction', '100');
		}elseif($grandtotal > 0 && $ordPayProvider=="7"){ // VeriSign Payflow Pro
			print '<form method="post" action="cart.php" onsubmit="return isvalidcard(this)">';
			print whv('mode', 'authorize') . whv('method', '7') . whv('ordernumber', $orderid);
		}elseif($grandtotal > 0 && $ordPayProvider=="8"){ // VeriSign Payflow Link
			$paymentlink = 'https://payflowlink.paypal.com';
			if($data2=="VSA") $paymentlink='https://payments.verisign.com.au/payflowlink';
			print '<form method="post" action="' . $paymentlink . '">';
			print whv('LOGIN', $data1) . whv('PARTNER', $data2) . whv('CUSTID', $orderid) . whv('AMOUNT', number_format($grandtotal,2,'.',''));
			print whv('TYPE',$ppmethod==1?'A':'S');
			print whv('DESCRIPTION', substr($descstr,0,255));
			print whv('NAME', trim($ordName.' '.$ordLastName)) . whv('ADDRESS', $ordAddress . (trim($ordAddress2)!='' ? ', ' . $ordAddress2 : '')) . whv('CITY', $ordCity) . whv('STATE', $ordState) . whv('ZIP', $ordZip) . whv('COUNTRY', ($countryCode=='US'?'USA':$ordCountry));
			print whv('EMAIL', $ordEmail) . whv('PHONE', $ordPhone);
			print whv('METHOD', 'CC') . whv('ORDERFORM', 'TRUE') . whv('SHOWCONFIRM', 'FALSE');
			if(trim($ordShipName)!='' || trim($ordShipAddress)!=''){
				print whv('NAMETOSHIP', trim($ordShipName.' '.$ordShipLastName)) . whv('ADDRESSTOSHIP', $ordShipAddress . (trim($ordShipAddress2)!='' ? ', ' . $ordShipAddress2 : '')) . whv('CITYTOSHIP', $ordShipCity) . whv('STATETOSHIP', $ordShipState) . whv('ZIPTOSHIP', $ordShipZip) . whv('COUNTRYTOSHIP', ($shipCountryCode=='US'?'USA':$ordShipCountry));
			}
		}elseif($grandtotal > 0 && $ordPayProvider=="9"){ // PayPoint.net
			print '<form method="post" action="https://www.secpay.com/java-bin/ValCard">';
			print whv('merchant', $data1) . whv('trans_id', $orderid) . whv('amount', number_format($grandtotal,2,'.',''));
			print whv('callback', $storeurl.'vsadmin/'.$wpconfirmpage) . whv('currency', $countryCurrency) . whv('cb_post', 'true');
			print whv('bill_name', trim($ordName.' '.$ordLastName)) . whv('bill_addr_1', $ordAddress) . whv('bill_addr_2', $ordAddress2) . whv('bill_city', $ordCity) . whv('bill_state', $ordState) . whv('bill_post_code', $ordZip) . whv('bill_country', $ordCountry) . whv('bill_email', $ordEmail) . whv('bill_tel', $ordPhone);
			if(trim($ordShipName)!='' || trim($ordShipLastName)!='' || trim($ordShipAddress)!=''){
				print whv('ship_name', trim($ordShipName.' '.$ordShipLastName)) . whv('ship_addr_1', $ordShipAddress) . whv('ship_addr_2', $ordShipAddress2) . whv('ship_city', $ordShipCity) . whv('ship_state', $ordShipState) . whv('ship_post_code', $ordShipZip) . whv('ship_country', $ordShipCountry);
			}
			$data2arr = explode('&',$data2);
			$data2md5=@$data2arr[0];
			$data2tpl=urldecode(@$data2arr[1]);
			if(trim($data2md5)!=''){
				print whv('digest', md5($orderid . number_format($grandtotal,2,'.','') . $data2md5));
				print whv('md_flds', 'trans_id:amount:callback');
			}
			print whv('mpi_description', substr($descstr,0,125));
			if(trim($data2tpl)!='') print whv('template', $data2tpl);
			if($ppmethod==1) print whv('deferred', 'reuse:5:5');
			if(@$requirecvv==TRUE) print whv('req_cv2', 'true');
			if($data3=='1') print whv('ssl_cb', 'true');
			if($demomode) print whv('options', 'test_status=true,dups=false');
		}elseif($grandtotal > 0 && $ordPayProvider=='10'){ // Capture Card
			print 'DISABLED!!<br />';
		}elseif($grandtotal > 0 && ($ordPayProvider=="11" || $ordPayProvider=="12")){ // PSiGate
			print '<form method="post" action="https://' . ($demomode ? 'dev' : 'checkout') . '.psigate.com/HTMLPost/HTMLMessenger"' . ($ordPayProvider=='12' ? ' onsubmit="return isvalidcard(this)"' : '') . '>';
			print whv('MerchantID', $data1) . whv('Oid', $orderid) . whv('FullTotal', number_format($grandtotal,2,'.','')) . whv('ThanksURL', $storeurl.'thanks.php') . whv('NoThanksURL', $storeurl.'thanks.php') . whv('CustomerRefNo', substr(md5($orderid.':'.@$secretword), 0, 24)) . whv('ChargeType', $ppmethod=='1' ? '1' : '0');
			if($ordPayProvider=='11') print whv('Bname', trim($ordName.' '.$ordLastName));
			print whv('Baddr1', $ordAddress) . whv('Baddr2', $ordAddress2) . whv('Bcity', $ordCity) . whv('IP', @$_SERVER['REMOTE_ADDR']) . whv('Bstate', $countryID==1 && $stateAbbrev!='' ? $stateAbbrev : $ordState) . whv('Bzip', $ordZip) . whv('Bcountry', $countryCode) . whv('Email', $ordEmail) . whv('Phone', $ordPhone);
			if(trim($ordShipName)!='' || trim($ordShipLastName)!='' || trim($ordShipAddress)!=''){
				print whv('Sname', trim($ordShipName.' '.$ordShipLastName)) . whv('Saddr1', $ordShipAddress) . whv('Saddr2', $ordShipAddress2) . whv('Scity', $ordShipCity) . whv('Sstate', $ordShipState) . whv('Szip', $ordShipZip) . whv('Scountry', $ordShipCountry);
			}
			if($demomode) print whv('Result', '1');
		}elseif($grandtotal > 0 && $ordPayProvider=="13"){ // Authorize.net AIM
			print '<form method="post" action="cart.php" onsubmit="return isvalidcard(this)">';
			print whv('mode', 'authorize') . whv('method', '13') . whv('ordernumber', $orderid) . whv('description', substr($descstr,0,254));
		}elseif($grandtotal > 0 && $ordPayProvider=="14"){ // Custom Pay Provider
			include './vsadmin/inc/customppsend.php';
		}elseif($grandtotal > 0 && $ordPayProvider=='15'){ // Netbanx
			$sequence = rand(1000000,9999999);
			print '<form method="post" action="https://pay.netbanx.com/' . $data1 . '">';
			print whv('nbx_merchant_reference', $orderid.'.' . $sequence) . whv('nbx_payment_amount', (int)($grandtotal*100)) . whv('nbx_currency_code', $countryCurrency) . whv('nbx_cardholder_name', trim($ordName.' '.$ordLastName)) . whv('nbx_email', $ordEmail) . whv('nbx_postcode', $ordZip);
			print whv('nbx_return_url', $storeurl.'categories.php');
			print whv('nbx_success_url', $storeurl.'vsadmin/ncconfirm.php');
			if($data2!='') print whv('nbx_checksum', sha1((int)($grandtotal*100).$countryCurrency.$orderid.'.'.$sequence.$data2));
		}elseif($grandtotal > 0 && $ordPayProvider=='16'){ // Linkpoint
			$lpsubtotal = round($totalgoods - $totaldiscounts, 2);
			$lpshipping = round(($shipping + $handling) - $freeshipamnt, 2);
			$lptax = round($stateTax + $countryTax, 2);
			print '<form action="https://www.'.($demomode?'staging.':'').'linkpointcentral.com/lpc/servlet/lppay" method="post"' . ($data2=='1'?' onsubmit="return isvalidcard(this)"':'') . '>';
			print whv('storename', $data1) . whv('mode', 'payonly') . whv('ponumber', $orderid) . whv('oid', $orderid.'.'.time()) . whv('responseURL', $storeurl.'thanks.php');
			print whv('subtotal', number_format($lpsubtotal,2,'.','')) . whv('chargetotal', number_format($lpsubtotal+$lpshipping+$lptax,2,'.','')) . whv('shipping', number_format($lpshipping,2,'.','')) . whv('tax', number_format($lptax,2,'.',''));
			if($data2!='1') print whv('bname', trim($ordName.' '.$ordLastName));
			print whv('baddr1', $ordAddress) . whv('baddr2', $ordAddress2) . whv('bcity', $ordCity);
			if($countryID==1 && $stateAbbrev!='') print whv('bstate', $stateAbbrev); else print whv('bstate2', $ordState);
			print whv('bzip', $ordZip) . whv('bcountry', $countryCode) . whv('email', $ordEmail) . whv('phone', $ordPhone);
			print whv('txntype', $ppmethod==1?'preauth':'sale');
			if(trim($ordShipName)!='' || trim($ordShipLastName)!='' || trim($ordShipAddress)!=''){
				print whv('sname', trim($ordShipName.' '.$ordShipLastName)) . whv('saddr1', $ordShipAddress) . whv('saddr2', $ordShipAddress2) . whv('scity', $ordShipCity) . whv('sstate', $ordShipState) . whv('szip', $ordShipZip) . whv('scountry', $shipCountryCode);
			}
			if($demomode) print whv('txnmode', 'test');
		}elseif($grandtotal > 0 && $ordPayProvider=="18"){ // PayPal Direct Payment
			$php_version = explode('.', phpversion());
			if(((int)$php_version[0])<5) print 'Please note, you are using PHP 4 and PHP 5 is required for PayPal.';
			print '<form method="post" action="cart.php" onsubmit="return isvalidcard(this)">';
			print whv('mode', 'authorize') . whv('method', '18') . whv('ordernumber', $orderid) . whv('description', substr($descstr,0,254));
		}elseif($grandtotal > 0 && $ordPayProvider=="19"){ // PayPal Express Payment
			$php_version = explode('.', phpversion());
			if(((int)$php_version[0])<5) print 'Please note, you are using PHP 4 and PHP 5 is required for PayPal.';
			print '<form method="post" action="thanks.php">';
			print whv('token', $token) . whv('method', 'paypalexpress') . whv('ordernumber', $orderid) . whv('payerid', $payerid) . whv('email', $ordEmail);
		}elseif($grandtotal > 0 && $ordPayProvider=='21'){ // Amazon Simple Pay
			print '<form action="https://authorize.payments' . ($demomode ? '-sandbox' : '') . '.amazon.com/pba/paypipeline" method="post">';
			$amazonstr = '';
			if($data3=='2') $amazonstr = "POST\nauthorize.payments" . ($demomode ? '-sandbox' : '') . ".amazon.com\n/pba/paypipeline\n";
			function amazonparam($nam, $val){
				global $amazonstr,$data3;
				$val2 = replaceaccents($val);
				print whv($nam, $val2);
				if($data3=='2') $amazonstr .= $nam . '=' . str_replace('%7E', '~', rawurlencode($val2)) . ($nam!='zip'?'&':''); else $amazonstr .= $nam . $val2;
			}
			if($data3=='2') amazonparam('SignatureMethod', 'HmacSHA256');
			if($data3=='2') amazonparam('SignatureVersion', 2);
			amazonparam('abandonUrl', $storeurl.'cart.php');
			amazonparam('accessKey', $data1);
			amazonparam('addressLine1', $ordAddress);
			if($ordAddress2!='') amazonparam('addressLine2', $ordAddress2);
			amazonparam('addressName', trim($ordName.' '.$ordLastName));
			amazonparam('amount', $countryCurrency . ' ' . $grandtotal);
			amazonparam('city', $ordCity);
			amazonparam('collectShippingAddress', 'FALSE');
			amazonparam('country', ($ordCountry=='United States of America' ? 'United States' : $ordCountry));
			amazonparam('description', substr($descstr,0,100));
			amazonparam('discount', $totaldiscounts);
			amazonparam('handling', $handling);
			amazonparam('immediateReturn', '1');
			amazonparam('ipnUrl', $storeurl.'vsadmin/ppconfirm.php');
			amazonparam('itemTotal', $totalgoods);
			amazonparam('phoneNumber', $ordPhone);
			amazonparam('processImmediate', ($ppmethod==1 ? 'FALSE' : 'TRUE'));
			amazonparam('referenceId', $orderid);
			amazonparam('returnUrl', $storeurl.'thanks.php');
			amazonparam('shipping', $shipping);
			amazonparam('state', $ordState);
			amazonparam('tax', $stateTax+$countryTax);
			amazonparam('zip', $ordZip);
			if($data3=='2')
				print whv('signature', base64_encode(hash_hmac('sha256',$amazonstr,$data2,TRUE)));
			else
				print whv('signature', base64_encode(CalcHmacSha1($amazonstr,$data2)));
		}
	}
	if(TRUE){
?>
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3" style="text-align:<?php print $tleft?>;">
			  <tr><td class="cobhl" height="30" colspan="2" align="center"><strong><?php print $xxChkCmp?></strong></td></tr>
<?php	if(($rgcpncode!='' || ($ordPayProvider=='19' && @$_GET['token']!='')) && (! $gotcpncode || @$cpnerror!='') && @$nogiftcertificate!=TRUE){ ?>
			  <tr>
			    <td class="cobhl" height="30" align="<?php print $tright?>"><strong><?php if($rgcpncode!='' && $ordPayProvider=='19' && ! $gotcpncode) print '<span style="color:#FF0000">' . $xxCpnNoF . '</span>'; else print $xxGifCer.':'?></strong></td>
				<td class="cobll"><span style="font-size:10px"><?php
			if($ordPayProvider=='19' && ! $gotcpncode && @$_GET['token']!=''){
				print '<input type="text" name="cpncode" id="cpncode" size="20" value="' . htmlspecials($rgcpncode) . '" alt="' . $xxGifNum . '" /> <input type="button" value="' . $xxAppCpn . '" onclick="document.location=\'cart.php?token='.$_GET['token'].'&cpncode=\'+document.getElementById(\'cpncode\').value" />';
			}else{
				print $cpnerror;
				if($rgcpncode!='' && ! $gotcpncode) printf($xxNoGfCr,$rgcpncode,1);
			} ?></span></td>
			  </tr>
<?php	}
		if(($warncheckspamfolder==TRUE || @$_POST['warncheckspamfolder']=='true') && @$noconfirmationemail!=TRUE){ ?>
			  <tr>
			    <td class="cobhl" height="30" align="<?php print $tright?>"><strong><?php print $xxThkSub?>:</strong></strong></td>
				<td class="cobll"><span style="color:#FF0000"><?php print $xxSpmWrn?></span></td>
			  </tr>
<?php	}
		if($cpnmessage!=''){ ?>
			  <tr>
			    <td class="cobhl" height="30" align="<?php print $tright?>"><strong><?php print $xxAppDs?>:</strong></strong></td>
				<td class="cobll"><?php print $cpnmessage?></td>
			  </tr>
<?php	} ?>
			  <tr>
			    <td class="cobhl" height="30" align="<?php print $tright?>" width="45%"><strong><?php print $xxTotGds?>:</strong></td>
				<td class="cobll"><?php print FormatEuroCurrency($totalgoods)?>
<script language="javascript" type="text/javascript">/* <![CDATA[ */
function updateshiprate(obj,theselector){
	if(obj.value!=''){
		document.getElementById("shipselectoridx").value=theselector;
		document.getElementById("shipselectoraction").value="selector";
		document.forms.shipform.submit();
	}
}
function selaltrate(id){
	document.getElementById('altrates').value=id;
	document.getElementById('shipselectoraction').value='altrates';
	document.forms.shipform.submit();
}
<?php	if(@$closeorderimmediately){
		$_SESSION['sessionid']=$thesessionid; ?>
function docloseorder(){
	ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
	ajaxobj.open("GET", "vsadmin/ajaxservice.php?action=clord", false);
	ajaxobj.send(null);
}
<?php	}
	if(@$adminAltRates==2){
		$sSQL = "SELECT altrateid FROM alternaterates WHERE usealtmethod".$international."<>0 AND altrateid<>".$shipType." ORDER BY altrateorder,altrateid";
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result)>0){
			print 'var extraship=[';
			$addcomma='';
			while($rs = mysql_fetch_assoc($result)){
				print $addcomma . $rs['altrateid'];
				$addcomma=',';
			}
			print "];\r\n" ?>
function acajaxcallback(){
	if(ajaxobj.readyState==4){
		var restxt=ajaxobj.responseText;
		var gssr=restxt.split('SHIPSELPARAM=');
		stable = document.getElementById('shipoptionstable');
		newrow = stable.insertRow(-1);
		newcell = newrow.insertCell(-1);
		newcell.align = 'center';
		newcell.style.whiteSpace='nowrap';
		newcell.innerHTML = decodeURIComponent(gssr[1]);
		newcell = newrow.insertCell(-1);
		newcell.innerHTML = gssr[0];
		if(decodeURIComponent(gssr[2])!='ERROR'){
			document.getElementById('numshiprate').value=gssr[4];
		}
		getalternatecarriers();
	}
}
function getalternatecarriers(){
	if(extraship.length>0){
		var shiptype=extraship.shift();
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange = acajaxcallback;
		ajaxobj.open("GET", "vsadmin/shipservice.php?shiptype="+shiptype+"&numshiprate="+document.getElementById('numshiprate').value+"&sessionid=<?php print urlencode($thesessionid)?>&destzip=<?php print urlencode($destZip)?>&sc=<?php print urlencode($shipcountry)?>&scc=<?php print urlencode($shipCountryCode)?>&sta=<?php print urlencode($shipStateAbbrev)?>&orderid=<?php print $orderid?>", true);
		ajaxobj.send(null);
	}
}
<?php	}
		mysql_free_result($result);
	} ?>
/* ]]> */</script>
				</td>
			  </tr>
<?php	if($shipType==0) $combineshippinghandling=FALSE;
		if($shipType!=0){ ?>
			  <tr>
			    <td class="cobhl" height="30" align="<?php print $tright?>"><strong><?php print (@$combineshippinghandling ? $xxShipHa : $xxShippg)?>:</strong></td>
				<td class="cobll"><?php
			$doshowlogo=($shipType!=0 && ! (($shipType==1||$shipType==2||$shipType==5)&&@$shippinglogo==''&&$adminAltRates==0));
			if($doshowlogo) print '<table id="shipoptionstable" border="0" cellspacing="2" cellpadding="2"><tr><td align="center" style="white-space:nowrap">';
			if($currShipType=='') $currShipType=$shipType;
			if($doshowlogo) print getshiplogo($currShipType) . '</td><td>';
			if(! $success){
				print '<span style="color:#FF0000">' . $errormsg . '</span>';
			}else{
				if($shipType!=0 || ($shipping-$freeshipamnt)!=0 || $willpickup_){
					if(! $multipleoptions) print FormatEuroCurrency(((double)$shipping+($combineshippinghandling ? $handling : 0))-$freeshipamnt) . ($shipMethod!='' ? ' - ' . $shipMethod : ''); else showshippingselect();
				}
			}
			if($doshowlogo) print '</td></tr></table>'; ?>
				</td>
			  </tr>
<?php	}
		if($success && $handling!=0 && @$combineshippinghandling!=TRUE){ ?>
			  <tr>
			    <td class="cobhl" height="30" align="<?php print $tright?>"><strong><?php print $xxHndlg?>:</strong></td>
				<td class="cobll"><?php print FormatEuroCurrency($handling)?></td>
			  </tr>
<?php	}
		if($adminAltRates==1){
			$sSQL = 'SELECT altrateid,altratename,'.getlangid('altratetext',65536).',usealtmethod,usealtmethodintl FROM alternaterates WHERE usealtmethod'.$international.'<>0 ORDER BY altrateorder,altrateid';
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0){ ?>
			  <tr>
			    <td align="<?php print $tright?>" class="cobhl" height="30"><strong><?php print $xxOrCom?>:</strong></td>
				<td class="cobll">
<?php			if(@$shippingoptionsasradios!=TRUE) print '<select id="altratesselect" size="1" onchange="selaltrate(this[this.selectedIndex].value)">';
				while($rs = mysql_fetch_assoc($result)){
					writealtshipline($rs[getlangid('altratetext',65536)],$rs['altrateid'],'','',TRUE);
				}
				if(@$shippingoptionsasradios!=TRUE) print '</select>';
?>				</td>
			  </tr>
<?php		}
			mysql_free_result($result);
		}
		if($totaldiscounts!=0&&(($totalgoods+$shipping+$handling)-($totaldiscounts+$freeshipamnt))>=0){ ?>
			  <tr>
			    <td class="cobhl" height="30" align="<?php print $tright?>"><strong><?php print $xxTotDs?>:</strong></td>
				<td class="cobll"><span style="color:#FF0000"><?php print FormatEuroCurrency($totaldiscounts)?></span></td>
			  </tr>
			  <tr>
			    <td class="cobhl" height="30" align="<?php print $tright?>"><strong><?php print $xxSubTot?>:</strong></td>
				<td class="cobll"><?php print FormatEuroCurrency(($totalgoods+$shipping+$handling)-($totaldiscounts+$freeshipamnt))?></td>
			  </tr>
<?php	}
		if($usehst){ ?>
			  <tr>
			    <td class="cobhl" height="30" align="<?php print $tright?>"><strong><?php print $xxHST?>:</strong></td>
				<td class="cobll"><?php print FormatEuroCurrency($stateTax+$countryTax)?></td>
			  </tr>
<?php	}else{
			if($stateTax!=0.0){ ?>
			  <tr>
			    <td class="cobhl" height="30" align="<?php print $tright?>"><strong><?php print $xxStaTax?>:</strong></td>
				<td class="cobll"><?php print FormatEuroCurrency($stateTax)?></td>
			  </tr>
<?php		}
			if($countryTax!=0.0){ ?>
			  <tr>
			    <td class="cobhl" height="30" align="<?php print $tright?>"><strong><?php print $xxCntTax?>:</strong></td>
				<td class="cobll"><?php print FormatEuroCurrency($countryTax)?></td>
			  </tr>
<?php		}
		}
		if($totaldiscounts!=0&&(($totalgoods+$shipping+$handling)-($totaldiscounts+$freeshipamnt))<0){ ?>
			  <tr>
			    <td class="cobhl" height="30" align="<?php print $tright?>"><strong><?php print $xxTotDs?>:</strong></td>
				<td class="cobll"><span style="color:#FF0000"><?php print FormatEuroCurrency($totaldiscounts)?></span></td>
			  </tr>
<?php	}?>
			  <tr>
			    <td class="cobhl" height="30" align="<?php print $tright?>"><strong><?php print $xxGndTot?>:</strong></td>
				<td class="cobll"><?php if(! $success) print '-'; else print FormatEuroCurrency($grandtotal)?></td>
			  </tr>
<?php	if(! ($ordPayProvider=='7' || $ordPayProvider=='13' || $ordPayProvider=='18')) $cardinalprocessor='';
		if($success && $grandtotal > 0 && ($ordPayProvider=='7' || $ordPayProvider=='10' || $ordPayProvider=='12' || $ordPayProvider=='13' || ($ordPayProvider=='14' && @$customppacceptcc) || ($ordPayProvider=='16' && $data2=='1') || $ordPayProvider=='18')){ // VeriSign Payflow Pro || PSiGate || Auth.NET AIM || PayPal Pro
			if($ordPayProvider!='10') $data1 = 'XXXXXXX0XXXXXXXXXXXXXXXXX';
			if($ordPayProvider!='10' && ($origCountryCode=='GB' || $origCountryCode=='IE')) $data1 = 'XXXXXXXXXXXXXXXXXXXXXXXXX';
			$isPSiGate = ($ordPayProvider=='12');
			$isLinkpoint = ($ordPayProvider=='16');
			if($isPSiGate){
				$sscardname='Bname';
				$sscardnum = 'CardNumber';
				$ssexmon = 'CardExpMonth';
				$ssexyear = 'CardExpYear';
				$sscvv2 = 'CardIDNumber';
			}elseif($isLinkpoint){
				$sscardname='bname';
				$sscardnum = 'cardnumber';
				$ssexmon = 'expmonth';
				$ssexyear = 'expyear';
				$sscvv2 = 'cvm';
			}else{
				$sscardname='cardname';
				$sscardnum = 'ACCT';
				$ssexmon = 'EXMON';
				$ssexyear = 'EXYEAR';
				$sscvv2 = 'CVV2';
			}
			$acceptecheck = ((@$acceptecheck==TRUE) && ($ordPayProvider=='13' || (@$customppacceptecheck && $ordPayProvider=='14')));
?>
<input type="hidden" name="sessionid" value="<?php print $thesessionid?>" />
<script language="javascript" type="text/javascript">/* <![CDATA[ */
var isswitchcard=false;
function clearcc(){
	document.getElementById("<?php print $sscardnum?>").value="";
	document.getElementById("<?php print $sscvv2?>").value="";
	document.getElementById("<?php print $ssexmon?>").selectedIndex=0;
	document.getElementById("<?php print $ssexyear?>").selectedIndex=0;
}
function donecc(){
	return true;
}
if(window.addEventListener){
	window.addEventListener("load", clearcc, false);
	window.addEventListener("unload",donecc,false);
}else if(window.attachEvent){
	window.attachEvent("onload", clearcc);
}
function isCreditCard(st){
  if(st.length > 19)return(false);
  sum = 0; mul = 1; l = st.length;
  for(i = 0; i < l; i++){
	digit = st.substring(l-i-1,l-i);
	tproduct = parseInt(digit ,10)*mul;
	if(tproduct >= 10)
		sum += (tproduct % 10) + 1;
	else
		sum += tproduct;
	if(mul==1)mul++;else mul--;
  }
  return((sum % 10)==0);
}
function isVisa(cc){
  if(((cc.length==16) || (cc.length==13)) && (cc.substr(0,1)==4))
	return isCreditCard(cc);
  return false;
}
function isMasterCard(cc){
  firstdig = cc.substr(0,1);
  seconddig = cc.substr(1,1);
  if((cc.length==16) && (firstdig==5) && ((seconddig >= 1) && (seconddig <= 5)))
	return isCreditCard(cc);
  return false;
}
function isAmericanExpress(cc){
  firstdig = cc.substr(0,1);
  seconddig = cc.substr(1,1);
  if(cc.length==15 && firstdig==3 && (seconddig==4 || seconddig==7))
	return isCreditCard(cc);
  return false;
}
function isDinersClub(cc){
  firstdig = cc.substr(0,1);
  seconddig = cc.substr(1,1);
  if(cc.length==14 && firstdig==3 && (seconddig==0 || seconddig==6 || seconddig==8))
	return isCreditCard(cc);
  return false;
}
function isDiscover(cc){
  first4digs = cc.substr(0,4);
  if(cc.length==16 && (first4digs=="6011" || cc.substr(0,3)=="622" || cc.substr(0,2)=="64" || cc.substr(0,2)=="65"))
	return isCreditCard(cc);
  return false;
}
function isAusBankcard(cc){
  first4digs = cc.substr(0,4);
  if(cc.length==16 && (first4digs=="5610"||first4digs=="5602"))
	return isCreditCard(cc);
  return false;
}
function isEnRoute(cc){
  first4digs = cc.substr(0,4);
  if(cc.length==15 && (first4digs=="2014" || first4digs=="2149"))
	return isCreditCard(cc);
  return false;
}
function isJCB(cc){
  first4digs = cc.substr(0,4);
  if(cc.length==16 && (first4digs=="3088" || first4digs=="3096" || first4digs=="3112" || first4digs=="3158" || first4digs=="3337" || first4digs=="3528" || first4digs=="3589"))
	return isCreditCard(cc);
  return false;
}
function isSwitch(cc){
  first4digs = cc.substr(0,4);
  if((cc.length>=16 && cc.length<=19) && (first4digs=="4903" || first4digs=="4911" || first4digs=="4936" || first4digs=="5018" || first4digs=="5020" || first4digs=="5038" || first4digs=="5641" || first4digs=="6304" || first4digs=="6333" || first4digs=="6334" || first4digs=="6759" || first4digs=="6761" || first4digs=="6763" || first4digs=="6767")){
	isswitchcard=true;
	return(isCreditCard(cc));
  }
  return false;
}
function isLaser(cc){
  first4digs = cc.substr(0,4);
  if((cc.length>=16 && cc.length<=19) && (first4digs=="6304" || first4digs=="6706" || first4digs=="6771" || first4digs=="6709"))
	return(isCreditCard(cc));
  return false;
}
function isvalidcard(theForm){
  cc = theForm.<?php print $sscardnum?>.value;
  newcode = "";
  var l = cc.length;
  for(i=0;i<l;i++){
	digit = cc.substring(i,i+1);
	digit = parseInt(digit ,10);
	if(!isNaN(digit)) newcode += digit;
  }
  cc=newcode;
  if(theForm.<?php print $sscardname?>.value==""){
	alert("<?php print $xxPlsEntr . ' \"' . $xxCCName . '\"' ?>");
	theForm.<?php print $sscardname?>.focus();
	return false;
  }
<?php if($acceptecheck==true){ ?>
if(cc!="" && theForm.accountnum.value!=""){
alert("Please enter either Credit Card OR ECheck details");
return(false);
}else if(theForm.accountnum.value!=""){
  if(theForm.accountname.value==""){
	alert("<?php print $xxPlsEntr?> \"Account Name\".");
	theForm.accountname.focus();
	return false;
  }
  if(theForm.bankname.value==""){
	alert("<?php print $xxPlsEntr?> \"Bank Name\".");
	theForm.bankname.focus();
	return false;
  }
  if(theForm.routenumber.value==""){
	alert("<?php print $xxPlsEntr?> \"Routing Number\".");
	theForm.routenumber.focus();
	return false;
  }
  if(theForm.accounttype.selectedIndex==0){
	alert("Please select your account type: (Checking / Savings).");
	theForm.accounttype.focus();
	return false;
  }
<?php	if(@$wellsfargo==TRUE){ ?>
  if(theForm.orgtype.selectedIndex==0){
	alert("Please select your account type: (Personal / Business).");
	theForm.orgtype.focus();
	return false;
  }
  if(theForm.taxid.value=="" && theForm.licensenumber.value==""){
	alert("Please enter either a Tax ID number or Drivers License Details.");
	theForm.taxid.focus();
	return false;
  }
  if(theForm.taxid.value==""){
	if(theForm.licensestate.selectedIndex==0){
		alert("Please select your Drivers License State.");
		theForm.licensestate.focus();
		return false;
	}
	if(theForm.dldobmon.selectedIndex==0){
		alert("Please select your Drivers License D.O.B. Month.");
		theForm.dldobmon.focus();
		return false;
	}
	if(theForm.dldobday.selectedIndex==0){
		alert("Please select your Drivers License D.O.B. Day.");
		theForm.dldobday.focus();
		return false;
	}
	if(theForm.dldobyear.selectedIndex==0){
		alert("Please select your Drivers License D.O.B. year.");
		theForm.dldobyear.focus();
		return false;
	}
  }
<?php	} ?>
}else{
<?php } ?>
  if(true <?php
		if(substr($data1,7,1)=='X') print '&& !isSwitch(cc) ';
		if(substr($data1,0,1)=='X') print '&& !isVisa(cc) ';
		if(substr($data1,1,1)=='X') print '&& !isMasterCard(cc) ';
		if(substr($data1,2,1)=='X') print '&& !isAmericanExpress(cc) ';
		if(substr($data1,3,1)=='X') print '&& !isDinersClub(cc) ';
		if(substr($data1,4,1)=='X') print '&& !isDiscover(cc) ';
		if(substr($data1,5,1)=='X') print '&& !isEnRoute(cc) ';
		if(substr($data1,6,1)=='X') print '&& !isJCB(cc) ';
		if(substr($data1,8,1)=='X') print '&& !isAusBankcard(cc) ';
		if(substr($data1,9,1)=='X') print '&& !isLaser(cc) '; ?>){
	<?php if($acceptecheck==TRUE) $xxValCC='Please enter a valid credit card number or bank account details if paying by ECheck.'; ?>
	alert("<?php print $xxValCC?>");
	theForm.<?php print $sscardnum?>.focus();
	return false;
  }
  if(theForm.<?php print $ssexmon?>.selectedIndex==0){
	alert("<?php print $xxCCMon?>");
	theForm.<?php print $ssexmon?>.focus();
	return false;
  }
  if(theForm.<?php print $ssexyear?>.selectedIndex==0){
	alert("<?php print $xxCCYear?>");
	theForm.<?php print $ssexyear?>.focus();
	return false;
  }
<?php if(substr($data1,7,1)=="X"){ ?>
	theForm.IssNum.value=theForm.IssNum.value.replace(/[^0-9]/g, '');
  if(theForm.IssNum.value=="" && isswitchcard){
	alert("Please enter an issue number / start date for Maestro/Solo cards.");
	theForm.IssNum.focus();
	return false;
  }
<?php }
	  if(@$requirecvv==TRUE){ ?>
  if(theForm.<?php print $sscvv2?>.value==""){
	alert("<?php print $xxPlsEntr . ' \"' . str_replace('"','\"',$xx34code) . '\"'?>");
	theForm.<?php print $sscvv2?>.focus();
	return false;
  }
<?php }
	  if(@$acceptecheck==TRUE) print '}'; ?>
  return true;
}
<?php if(@$cardinalprocessor!='' && @$cardinalmerchant!='' && @$cardinalpwd!=''){ ?>
vbvtext='<html><head><title>Verified by Visa</title><style type="text/css">body {font-family: verdana,sans-serif;font-size:10pt;}</style></head><body><p><h3><?php print str_replace("'","\'",$xxVBV1)?></h3></p><p><?php print str_replace("'","\'",$xxVBV2)?><img src="images/vbv_logo.gif" border="0" style="float:<?php print $tright?>;margin:4px;" /></p><p><?php print str_replace("'","\'",$xxVBV3)?></p><p><?php print str_replace("'","\'",$xxVBV4)?></p><p><?php print str_replace("'","\'",$xxVBV5)?></p><p align="center"><input type="button" value="<?php print str_replace("'","\'",$xxClsWin)?>" onclick="window.close()"></p></body></html>';
<?php } ?>
/* ]]> */</script>
<?php		if(@$_SERVER['HTTPS']!='on' && (@$_SERVER['SERVER_PORT']!='443') && @$nochecksslserver!=TRUE){ ?>
			  <tr>
			    <td class="cobhl" align="center" colspan="2" height="30"><span style="color:#FF0000;font-weight:bold">This site may not be secure. Do not enter real Credit Card numbers.</span></td>
			  </tr>
<?php		} ?>
			  <tr><td class="cobhl" height="30" colspan="2" align="center"><strong><?php print $xxCCDets ?></strong></td></tr>
			  <tr>
			    <td class="cobhl" align="<?php print $tright?>" height="30"><strong><?php print $xxCCName?>:</strong></td>
				<td class="cobll"><input type="text" name="<?php print $sscardname?>" size="21" value="<?php print trim($ordName.' '.$ordLastName)?>" AUTOCOMPLETE="off" alt="<?php print $xxCCName?>" /></td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="<?php print $tright?>" height="30"><strong><?php print $xxCrdNum?>:</strong></td>
				<td class="cobll"><table border="0" cellspacing="0" cellpadding="0"><tr><td><input type="text" name="<?php print $sscardnum?>" id="<?php print $sscardnum?>" size="21" AUTOCOMPLETE="off" alt="<?php print $xxCrdNum?>" /></td>
<?php			if(@$cardinalprocessor!='' && @$cardinalmerchant!='' && @$cardinalpwd!=''){ ?>
				<td>&nbsp;&nbsp;<a href="" onclick="javascript:newwin=window.open('','LearnMore','10,10,width=551,height=380,scrollbars=yes,resizable=yes');newwin.document.open();newwin.document.write(vbvtext);newwin.document.close();return false;"><img src="images/vbv_learn_more.gif" alt="Verified by Visa Learn More" border="0" /></a></td><td><a href="" onclick="javascript:window.open('http://www.mastercardbusiness.com/mcbiz/index.jsp?template=/orphans&content=securecodepopup','LearnMore','10,10,width=551,height=380,scrollbars=yes,resizable=yes');return false;"><img src="images/mcsc_learn_more.gif" alt="MasterCard SecureCode Learn More" border="0" /></a></td>
<?php			} ?>
				</tr></table></td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="<?php print $tright?>" height="30"><strong><?php print $xxExpEnd?>:</strong></td>
				<td class="cobll">
				  <select name="<?php print $ssexmon?>" id="<?php print $ssexmon?>" size="1">
					<option value=""><?php print $xxMonth?></option>
					<?php	for($index=1; $index<=12; $index++){
								if($index < 10) $themonth = "0" . $index; else $themonth = $index;
								print "<option value='" . $themonth . "'>" . $themonth . "</option>\n";
							} ?>
				  </select> / <select name="<?php print $ssexyear?>" id="<?php print $ssexyear?>" size="1">
					<option value=""><?php print $xxYear?></option>
					<?php	$thisyear=date("Y", time());
							for($index=$thisyear; $index <= $thisyear+10; $index++){
								print "<option value='" . ($isPSiGate?substr($index,-2):$index) . "'>" . $index . "</option>\n";
							} ?></select>
				</td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="<?php print $tright?>" height="30"><strong><?php print $xx34code?>:</strong></td>
				<td class="cobll"><input type="text" name="<?php print $sscvv2?>" id="<?php print $sscvv2?>" size="4" AUTOCOMPLETE="off" alt="<?php print $xx34code?>" /> <strong><?php if(@$requirecvv!=TRUE)print $xxIfPres?></strong></td>
			  </tr>
<?php		if(substr($data1,7,1)=="X"){ ?>
			  <tr>
			    <td class="cobhl" align="<?php print $tright?>" height="30"><strong>Issue Number / Start Date (mmyy):</strong></td>
				<td class="cobll"><input type="text" name="IssNum" size="4" AUTOCOMPLETE="off" alt="Issue Number / Start Date" /> <strong>(Maestro/Solo Only)</strong></td>
			  </tr>
<?php		}
			if($acceptecheck==TRUE){ // Auth.net ?>
			  <tr>
			    <td class="cobhl" height="30" colspan="2" align="center"><strong>ECheck Details</strong><br /><span style="font-size:10px">Please enter either Credit Card OR ECheck details</span></td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="<?php print $tright?>" height="30"><strong>Account Name:</strong></td>
				<td class="cobll"><input type="text" name="accountname" size="21" AUTOCOMPLETE="off" value="<?php print trim($ordName.' '.$ordLastName)?>" alt="Account Name" /></td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="<?php print $tright?>" height="30"><strong>Account Number:</strong></td>
				<td class="cobll"><input type="text" name="accountnum" size="21" AUTOCOMPLETE="off" alt="Account Number" /></td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="<?php print $tright?>" height="30"><strong>Bank Name:</strong></td>
				<td class="cobll"><input type="text" name="bankname" size="21" AUTOCOMPLETE="off" alt="Bank Name" /></td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="<?php print $tright?>" height="30"><strong>Routing Number:</strong></td>
				<td class="cobll"><input type="text" name="routenumber" size="10" AUTOCOMPLETE="off" alt="Routing Number" /></td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="<?php print $tright?>" height="30"><strong>Account Type:</strong></td>
				<td class="cobll"><select name="accounttype" size="1"><option value=""><?php print $xxPlsSel?></option><option value="CHECKING">Checking</option><option value="SAVINGS">Savings</option><option value="BUSINESSCHECKING">Business Checking</option></select></td>
			  </tr>
<?php			if(@$wellsfargo==TRUE){ ?>
			  <tr>
			    <td class="cobhl" align="<?php print $tright?>" height="30"><strong>Personal or Business Acct.:</strong></td>
				<td class="cobll"><select name="orgtype" size="1"><option value=""><?php print $xxPlsSel?></option><option value="I">Personal</option><option value="B">Business</option></select></td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="<?php print $tright?>" height="30"><strong>Tax ID:</strong></td>
				<td class="cobll"><input type="text" name="taxid" size="21" AUTOCOMPLETE="off" alt="Tax ID" /></td>
			  </tr>
			  <tr>
			    <td class="cobhl" height="30" colspan="2" align="center"><span style="font-size:10px">If you have provided a Tax ID then the following information is not necessary</span></td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="<?php print $tright?>" height="30"><strong>Drivers License Number:</strong></td>
				<td class="cobll"><input type="text" name="licensenumber" size="21" AUTOCOMPLETE="off" alt="Drivers License Number" /></td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="<?php print $tright?>" height="30"><strong>Drivers License State:</strong></td>
				<td class="cobll"><select size="1" name="licensestate"><option value=""><?php print $xxPlsSel?></option><?php
					$sSQL = 'SELECT stateName,stateAbbrev FROM states WHERE stateEnabled=1 ORDER BY stateName';
					$result = mysql_query($sSQL) or print(mysql_error());
					while($rs = mysql_fetch_assoc($result))
						print '<option value="' . str_replace('"','&quot;',$rs['stateAbbrev']) . '">' . $rs['stateName'] . '</option>';
					mysql_free_result($result); ?></select></td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="<?php print $tright?>" height="30"><strong>Date Of Birth On License:</strong></td>
				<td class="cobll"><select name="dldobmon" size="1"><option value=""><?php print $xxMonth?></option>
<?php				for($index=1; $index <= 12; $index++) print '<option value="' . $index . '">' . date("M", mktime(1,0,0,$index,1,1990)) . '</option>'; ?>
				</select> <select name="dldobday" size="1"><option value="">Day</option>
<?php				for($index=1; $index <= 31; $index++) print '<option value="' . $index . '">' . $index . '</option>'; ?>
				</select> <select name="dldobyear" size="1"><option value=""><?php print $xxYear?></option>
<?php				$thisyear = date("Y");
					for($index=$thisyear-100; $index <= $thisyear; $index++) print '<option value="' . $index . '">' . $index . '</option>'; ?>
				</select></td>
			  </tr>
<?php			}
			}
		}
		if($success){ ?>
			  <tr>
			    <td class="cobhl" height="30" colspan="2" align="center"><?php if(@$cardinalprocessor!='' && @$cardinalmerchant!='' && @$cardinalpwd!='') print $xxCentl; else print $xxMstClk?></td>
			  </tr>
			  <tr>
				<td class="cobll" colspan="2" align="center"><table width="100%" cellspacing="0" cellpadding="0" border="0">
				    <tr>
					  <td width="16" height="26">&nbsp;</td>
					  <td width="100%" align="center"><?php
			if($orderid!=0){
				if($ordPayProvider=='21') print '<input type="image" src="https://authorize.payments-sandbox.amazon.com/pba/images/SMPayNowWithAmazon.png" border="0">'; else print imageorsubmit($imgcheckoutbutton3,$xxCOTxt.(@$closeorderimmediately?'" onclick="docloseorder()':''),'checkoutbutton');
			} ?></td>
					  <td width="16" align="right" valign="bottom"><img src="images/tablebr.png" alt="" /></td>
					</tr>
				  </table></td>
			  </tr>
<?php	} ?>
			</table>
<?php	if($shipType==4){ ?>
			<p align="center">&nbsp;<br /><span style="font-size:10px"><?php print $xxUPStm?></span></p>
<?php	}elseif($shipType==7 || $shipType==8){ ?>
			<p align="center">&nbsp;<br /><span style="font-size:10px"><?php print $fedexcopyright?></span></p>
<?php	} ?>
	</form>
<?php	print '<form method="post" name="shipform" id="shipform" action="cart.php">';
		print whv('mode', 'go');
		print whv('sessionid', $thesessionid);
		print whv('orderid', $orderid);
		print whv('cpncode', $rgcpncode);
		print whv('token', $token);
		print whv('payerid', $payerid);
		print whv('remember', @$_POST['remember']);
		writehiddenidvar('altrates', $shipType);
		writehiddenidvar('shipselectoridx', '');
		writehiddenidvar('shipselectoraction', '');
		writehiddenidvar('numshiprate', $numshiprate);
		if(@$_POST['shipdiff']=='1') print whv('shipdiff', '1');
		if($warncheckspamfolder) print whv('warncheckspamfolder', 'true');
		print '</form>';
		$_SESSION['shipselectoridx']=(is_numeric(@$_POST['shipselectoridx']) ? @$_POST['shipselectoridx'] : '');
		$_SESSION['shipselectoraction']=(@$_POST['shipselectoraction']=='selector' || @$_POST['shipselectoraction']=='altrates' ? @$_POST['shipselectoraction'] : '');
		//$_SESSION['altrates']=$shipType;
		if(! $fromshipselector && $adminAltRates==2) print '<script language="javascript" type="text/javascript">getalternatecarriers();</script>';
	} // success
}elseif(@$_POST['mode']=='authorize'){
	$blockuser=checkuserblock('');
	$ordID = escape_string(str_replace("'",'',@$_POST['ordernumber']));
	if(! is_numeric($ordID)) $ordID=0;
	$vsRESULT='x';
	$vsRESPMSG=$vsAVSADDR=$vsAVSZIP=$vsTRANSID='';
	if(! getpayprovdetails(@$_POST['method'],$data1,$data2,$data3,$demomode,$ppmethod)) $ordID=0;
	$sSQL = "SELECT ordID FROM orders WHERE ordID='" . escape_string($ordID) . "' AND " . getordersessionsql();
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result)==0) $ordID=0;
	mysql_free_result($result);
	$centinelenrolled='N';
	$centinelerror=@$_SESSION['ErrorDesc'];
	if(@$_POST['method']=='14' && @$custompp3ds!=TRUE) $cardinalprocessor='';
	if($ordID!=0 && @$cardinalprocessor!='' && @$cardinalmerchant!='' && @$cardinalpwd!='' && @$_SESSION['centinelok']==''){
		$cardnum = str_replace(' ', '', @$_POST['ACCT']);
		$exmon = trim(@$_POST['EXMON']);
		$exyear = trim(@$_POST['EXYEAR']);
		$cardname = trim(unstripslashes(@$_POST['cardname']));
		$cvv2 = trim(@$_POST['CVV2']);
		$issuenum = trim(@$_POST['IssNum']);
		$sSQL = "SELECT ordID,ordName,ordLastName,ordCity,ordState,ordCountry,ordPhone,ordHandling,ordZip,ordEmail,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordTotal,ordDiscount,ordAddress,ordAddress2,ordIP,ordAuthNumber,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipCountry,ordShipZip FROM orders WHERE ordID='" . escape_string($ordID) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_assoc($result);
		$sXML = '<CardinalMPI>' .
			addtag('Version','1.7') .
			addtag('MsgType','cmpi_lookup') .
			addtag('ProcessorId',$cardinalprocessor) .
			addtag('MerchantId',$cardinalmerchant) .
			addtag('TransactionPwd',$cardinalpwd) .
			addtag('TransactionType','C') .
			addtag('Amount',(int)((($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordHSTTax']+$rs['ordTotal']+$rs['ordHandling']+0.001)-$rs['ordDiscount'])*100)) .
			addtag('CurrencyCode',$countryNumCurrency) .
			addtag('OrderNumber',$ordID) .
			addtag('OrderDescription','Order id ' . $ordID) .
			addtag('EMail',$rs['ordEmail']) .
			addtag('UserAgent',@$_SERVER['HTTP_USER_AGENT']) .
			addtag('BrowserHeader',@$_SERVER['HTTP_ACCEPT']) .
			addtag('IPAddress',@$_SERVER['REMOTE_ADDR']=='::1'?'127.0.0.1':@$_SERVER['REMOTE_ADDR']) .
			addtag('CardNumber',$cardnum) .
			addtag('CardExpMonth',$exmon) .
			addtag('CardExpYear',(strlen($exyear)==2?'20':'').$exyear) .
			'</CardinalMPI>';
		mysql_free_result($result);
		$theurl='https://'.(@$_POST['method']=='7'||@$_POST['method']=='18'?'paypal':'centinel400').'.cardinalcommerce.com/maps/txns.asp';
		if(@$cardinaltestmode) $theurl='https://centineltest.cardinalcommerce.com/maps/txns.asp';
		if(@$cardinalurl!='') $theurl=$cardinalurl;
		if(callcurlfunction($theurl, 'cmpi_msg=' . urlencode($sXML), $res, '', $errormsg, 12)){
			$xmlDoc = new vrXMLDoc($res);
			$nodeList = $xmlDoc->nodeList->childNodes[0];
			for($i = 0; $i < $nodeList->length; $i++){
				if($nodeList->nodeName[$i]=='ACSUrl') $acsurl=$nodeList->nodeValue[$i];
				if($nodeList->nodeName[$i]=='Payload') $_SESSION['cardinal_pareq']=$nodeList->nodeValue[$i];
				if($nodeList->nodeName[$i]=='Enrolled'){ $centinelenrolled=$nodeList->nodeValue[$i]; $_SESSION['centinel_enrolled']=$centinelenrolled; }
				if($nodeList->nodeName[$i]=='OrderId') $_SESSION['cardinal_orderid']=$nodeList->nodeValue[$i];
				if($nodeList->nodeName[$i]=='TransactionId') $_SESSION['cardinal_transaction']=$nodeList->nodeValue[$i];
				if($nodeList->nodeName[$i]=='EciFlag') $_SESSION['EciFlag']=$nodeList->nodeValue[$i];
				if($nodeList->nodeName[$i]=='ErrorDesc') $centinelerror=$nodeList->nodeValue[$i];
				if($nodeList->nodeName[$i]=='ErrorNo' && $nodeList->nodeValue[$i]=='1360'){ $centinelerror=''; break; }
			}
			if($centinelenrolled=='Y'){
				$_SESSION['cardinal_method']=@$_POST['method'];
				$_SESSION['cardinal_ordernum']=$ordID;
				$_SESSION['cardinal_sessionid']=$thesessionid;
				$_SESSION['cardinal_cardnum']=$cardnum;
				$_SESSION['cardinal_exmon']=trim(@$_POST['EXMON']);
				$_SESSION['cardinal_exyear']=trim(@$_POST['EXYEAR']);
				$_SESSION['cardinal_cardname']=trim(@$_POST['cardname']);
				$_SESSION['cardinal_cvv2']=trim(@$_POST['CVV2']);
				$_SESSION['cardinal_issnum']=trim(@$_POST['IssNum']);
				print '<div style="font-weight:bold;padding:5px;margin:5px;text-align:center;">' . $xxComOrd . '<br /><br />' . $xxNoBack . '<br /><br /><iframe id="centinelwin" src="vsadmin/ajaxservice.php?action=centinel&url='.urlencode($acsurl).'" width="440" height="400">Browser error.</iframe></div>';
			}
		}
	}elseif(@$_SESSION['centinelok']=='Y'){
		$cardnum = $_SESSION['cardinal_cardnum'];
		$exmon = $_SESSION['cardinal_exmon'];
		$exyear = $_SESSION['cardinal_exyear'];
		$cardname = $_SESSION['cardinal_cardname'];
		$cvv2 = $_SESSION['cardinal_cvv2'];
		$issuenum = $_SESSION['cardinal_issnum'];
		$_SESSION['cardinal_cardnum']=NULL; unset($_SESSION['cardinal_cardnum']);
		$_SESSION['cardinal_exmon']=NULL; unset($_SESSION['cardinal_exmon']);
		$_SESSION['cardinal_exyear']=NULL; unset($_SESSION['cardinal_exyear']);
		$_SESSION['cardinal_cardname']=NULL; unset($_SESSION['cardinal_cardname']);
		$_SESSION['cardinal_cvv2']=NULL; unset($_SESSION['cardinal_cvv2']);
		$_SESSION['cardinal_issnum']=NULL; unset($_SESSION['cardinal_issnum']);
	}else{
		$cardnum = str_replace(' ', '', @$_POST['ACCT']);
		$exmon = @$_POST['EXMON'];
		$exyear = @$_POST['EXYEAR'];
		$cardname = trim(unstripslashes(@$_POST['cardname']));
		$cvv2 = trim(@$_POST['CVV2']);
		$issuenum = trim(@$_POST['IssNum']);
	}
	if($ordID==0)
		$vsRESPMSG='Error';
	elseif($centinelenrolled=='Y'){
		// Do Nothing
	}elseif(@$_SESSION['centinelok']=='N'||$centinelerror!=''){
		$vsRESPMSG=($centinelerror!=''?$centinelerror.'<br />':'').$xx3DSFai;
	}elseif(@$_POST['method']=='7'){ // PayFlow Pro
		$vsdetails = explode('&', $data1);
		$vs1=@$vsdetails[0];
		$vs2=@$vsdetails[1];
		$vs3=@$vsdetails[2];
		$vs4=@$vsdetails[3];
		splitname($cardname, $firstname, $lastname);
		$sSQL = "SELECT ordName,ordLastName,ordZip,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordHandling,ordTotal,ordDiscount,ordAddress,ordAddress2,ordCity,ordState,ordCountry,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipCountry,ordShipZip,ordAuthNumber,ordEmail FROM orders WHERE ordID='" . $ordID . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_assoc($result);
		$vsAUTHCODE = $rs['ordAuthNumber'];
		$sSQL = "SELECT countryID,countryCode,countryOrder FROM countries WHERE countryName='" . escape_string($rs['ordCountry']) . "'";
		$result2 = mysql_query($sSQL) or print(mysql_error());
		if($rs2 = mysql_fetch_assoc($result2)){
			$countryid = $rs2['countryID'];
			$countryCode = $rs2['countryCode'];
			$homecountry = ($rs2['countryOrder']>=2);
		}
		mysql_free_result($result2);
		$sSQL = "SELECT countryCode FROM countries WHERE countryName='" . (trim($rs['ordShipAddress'])!='' ? escape_string($rs['ordShipCountry']) : escape_string($rs['ordCountry'])) . "'";
		$result2 = mysql_query($sSQL) or print(mysql_error());
		if($rs2 = mysql_fetch_assoc($result2))
			$shipCountryCode = $rs2['countryCode'];
		mysql_free_result($result2);
		function getstateabbrev($statename){
			$sSQL = "SELECT stateAbbrev FROM states WHERE stateName='" . escape_string($statename) . "'";
			$result2 = mysql_query($sSQL) or print(mysql_error());
			if($rs2 = mysql_fetch_assoc($result2))
				$statename=$rs2['stateAbbrev'];
			mysql_free_result($result2);
			return($statename);
		}
		if(trim($rs['ordShipAddress'])!='') $isshp='Ship'; else $isshp='';
		$ordState=$rs['ordState'];
		$ordShipState = $rs['ord'.$isshp.'State'];
		if(($countryid==1 || $countryid==2) && $homecountry && @$usestateabbrev!=TRUE){
			$ordState=getstateabbrev($ordState);
			$ordShipState=getstateabbrev($ordShipState);
		}
		splitname(trim($rs['ord'.$isshp.'Name'].' '.$rs['ord'.$isshp.'LastName']), $shipfirstname, $shiplastname);
		$sXML = 'PARTNER='.$vs3.'&VENDOR='.$vs2.'&TRXTYPE='.($ppmethod==1?'A':'S').'&TENDER=C&ZIP['.strlen($rs['ordZip']).']='.$rs['ordZip'].'&STREET['.strlen($rs['ordAddress']).']='.$rs['ordAddress']. ($rs['ordAddress2']!='' ? '&STREET2['.strlen($rs['ordAddress2']).']='.$rs['ordAddress2'] : '') . '&CITY['.strlen($rs['ordCity']).']='.$rs['ordCity'].'&STATE['.strlen($ordState).']='.$ordState.'&BILLTOCOUNTRY['.strlen($countryCode).']='.$countryCode.'&FIRSTNAME['.strlen($firstname).']='.$firstname.'&LASTNAME['.strlen($lastname).']='.$lastname.'&EMAIL='.$rs['ordEmail'];
		$sXML .= '&SHIPTOZIP['.strlen($rs['ord'.$isshp.'Zip']).']='.$rs['ord'.$isshp.'Zip'].'&SHIPTOSTREET['.strlen($rs['ord'.$isshp.'Address']).']='.$rs['ord'.$isshp.'Address']. ($rs['ord'.$isshp.'Address2']!='' ? '&SHIPTOSTREET2['.strlen($rs['ord'.$isshp.'Address2']).']='.$rs['ord'.$isshp.'Address2'] : '') . '&SHIPTOCITY['.strlen($rs['ord'.$isshp.'City']).']='.$rs['ord'.$isshp.'City'].'&SHIPTOSTATE['.strlen($ordShipState).']='.$ordShipState.'&SHIPTOCOUNTRYCODE['.strlen($shipCountryCode).']='.$shipCountryCode.'&SHIPTOCOUNTRY['.strlen($shipCountryCode).']='.$shipCountryCode.'&SHIPTOFIRSTNAME['.strlen($shipfirstname).']='.$shipfirstname.'&SHIPTOLASTNAME['.strlen($shiplastname).']='.$shiplastname;
		if($issuenum!=''){
			if(strlen($issuenum)==2) $sXML .= '&CARDISSUE=' . $issuenum; else $sXML .= '&CARDSTART=' . $issuenum;
		}
		$sXML .= '&COMMENT1=' . $ordID . '&ACCT=' . $cardnum . '&CUSTIP=' . @$_SERVER['REMOTE_ADDR'];
		$sXML .= '&PWD=' . $vs4 . '&USER=' . $vs1 . '&CVV2=' . $cvv2 . '&EXPDATE=' . $exmon . substr($exyear, -2);
		$sXML .= '&AMT=' . number_format(($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordHSTTax']+$rs['ordTotal']+$rs['ordHandling'])-$rs['ordDiscount'],2,'.','');
		mysql_free_result($result);
		if(@$cardinalprocessor!='' && @$cardinalmerchant!='' && @$cardinalpwd!=''){
			$sXML .= '&AUTHSTATUS3DS=' . @$_SESSION['PAResStatus'] . '&MPIVENDOR3DS=' . @$_SESSION['centinel_enrolled'] . '&CAVV=' . @$_SESSION['Cavv'] . '&ECI=' . @$_SESSION['EciFlag'] . '&XID=' . @$_SESSION['Xid'];
		}
		if($vsAUTHCODE==''){
			if($vs3=='VSA') $theurl = 'payflow'.($demomode?'-test':'').'.verisign.com.au'; else $theurl = ($demomode?'test-':'').'payflow.verisign.com';
			// $sXML .= '&REQUEST_ID='.$ordID;
			$xmlfnheaders=array('X-VPS-REQUEST-ID: ' . $ordID.'.'.rand(1000000,9999999));
			$success = callcurlfunction('https://' . ($demomode?'pilot-':'') . 'payflowpro.paypal.com', $sXML, $curString, '', $vsRESPMSG, TRUE);
			if(!is_array($curString)){
				$curStringArr = array();
				while(strlen($curString)!=0){
					if(strpos($curString,'&')!==FALSE) $varString = substr($curString, 0, strpos($curString , "&" )); else $varString = $curString;
					$name = substr($varString, 0, strpos($varString,'='));
					$curStringArr[$name] = substr($varString, (strlen($name)+1) - strlen($varString));
					if(strlen($curString)!=strlen($varString)) $curString = substr($curString,  (strlen($varString)+1) - strlen($curString)); else $curString = '';
				}
				$curString = $curStringArr;
			}
			$vsRESULT=$curString['RESULT'];
			$vsRESPMSG=@$curString['RESPMSG'];
			$vsAUTHCODE=@$curString['AUTHCODE'];
			if(array_key_exists('PNREF', $curString))$vsTRANSID=$curString['PNREF'];
			if(array_key_exists('PPREF', $curString))$vsTRANSID=$curString['PPREF'];
			$vsAVSADDR=@$curString['AVSADDR'];
			if(array_key_exists('AVSCODE', $curString))$vsAVSADDR=$curString['AVSCODE'];
			$vsAVSZIP=@$curString['AVSZIP'];
			$vsIAVS=@$curString['IAVS'];
			$vsCVV2=@$curString['CVV2MATCH'];
			if(array_key_exists('ACK', $curString)){
				if($curString['ACK']=='Success'){ $vsRESULT='0'; $vsRESPMSG=$xxTranAp; } else $vsRESULT='';
			}
			if(array_key_exists('L_LONGMESSAGE0', $curString))
				$vsRESPMSG=urldecode($curString['L_LONGMESSAGE0']);
			if(array_key_exists('L_ERRORCODE0', $curString))
				$vsERRCODE=$curString['L_ERRORCODE0'];
			if(array_key_exists('DUPLICATE', $curString)){
				if($curString['DUPLICATE']=='1'){ $vsRESULT=''; $vsRESPMSG='DUPLICATE'; $success=FALSE; }
			}
			if($vsRESULT=='0' || $vsRESULT=='126'){
				if($vsRESULT=='126'){ $underreview='Fraud Review:<br />';$vsRESPMSG='Approved'; }else $underreview='';
				mysql_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . $ordID . "'") or print(mysql_error());
				mysql_query("UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAVS='" . escape_string($vsAVSADDR . $vsAVSZIP) . "',ordCVV='" . escape_string($vsCVV2) . "',ordAuthNumber='" . escape_string($underreview . $vsAUTHCODE) . "',ordTransID='" . escape_string($vsTRANSID) . "' WHERE ordID='" . $ordID . "'") or print(mysql_error());
				do_order_success($ordID,$emailAddr,$sendEmail,FALSE,TRUE,TRUE,TRUE);
				$vsRESULT='0';
			}
		}else{
			$vsRESULT='0';
			$vsRESPMSG='Approved';
		}
	}elseif(@$_POST['method']=='13'){ // Auth.net AIM
		if(@$secretword!=''){
			$data1 = upsdecode($data1, $secretword);
			$data2 = upsdecode($data2, $secretword);
		}
		$sSQL = "SELECT ordID,ordCity,ordState,ordCountry,ordPhone,ordHandling,ordZip,ordEmail,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordTotal,ordDiscount,ordAddress,ordAddress2,ordIP,ordAuthNumber,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipCountry,ordShipZip FROM orders WHERE ordID='" . $ordID . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_assoc($result);
		mysql_free_result($result);
		$vsAUTHCODE = trim($rs['ordAuthNumber']);
		$sXML = 'x_version=3.1&x_delim_data=True&x_relay_response=False&x_delim_char=|&x_duplicate_window=15';
		$sXML .= '&x_login=' . $data1 . '&x_tran_key=' . $data2 . '&x_cust_id=' . $rs['ordID'] . '&x_Invoice_Num=' . $rs['ordID'];
		$sXML .= '&x_amount=' . number_format(($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordHSTTax']+$rs['ordTotal']+$rs['ordHandling'])-$rs['ordDiscount'],2,'.','');
		$sXML .= '&x_currency_code=' . $countryCurrency . '&x_Description=' . substr(urlencode(unstripslashes(str_replace('&quot;','"',@$_POST['description']))),0,255);
		if(trim(@$_POST['accountnum'])!=''){
			$sXML .= '&x_method=ECHECK&x_echeck_type=WEB&x_recurring_billing=NO';
			$sXML .= '&x_bank_acct_name=' . urlencode(unstripslashes(@$_POST['accountname'])) . '&x_bank_acct_num=' . urlencode(trim(@$_POST['accountnum']));
			$sXML .= '&x_bank_name=' . urlencode(unstripslashes(@$_POST['bankname'])) . '&x_bank_aba_code=' . urlencode(trim(@$_POST['routenumber']));
			$sXML .= '&x_bank_acct_type=' . urlencode(trim(@$_POST['accounttype'])) . '&x_type=AUTH_CAPTURE';
			if(@$wellsfargo==TRUE){
				$sXML .= '&x_customer_organization_type=' . trim(@$_POST['orgtype']);
				if(trim(@$_POST['taxid'])!='')
					$sXML .= '&x_customer_tax_id=' . urlencode(trim(@$_POST['taxid']));
				else
					$sXML .= '&x_drivers_license_num=' . urlencode(trim(@$_POST['licensenumber'])) . '&x_drivers_license_state=' . urlencode(trim(@$_POST['licensestate'])) . '&x_drivers_license_dob=' . urlencode(trim(@$_POST['dldobyear']) . '/' . trim(@$_POST['dldobmon']) . '/' . trim(@$_POST['dldobday']));
			}
		}else{
			$sXML .= '&x_method=CC&x_card_num=' . urlencode($cardnum) . '&x_exp_date=' . $exmon . $exyear;
			if($cvv2!='') $sXML .= '&x_card_code=' . $cvv2;
			if($ppmethod==1) $sXML .= '&x_type=AUTH_ONLY'; else $sXML .= '&x_type=AUTH_CAPTURE';
		}
		if(@$cardinalprocessor!='' && @$cardinalmerchant!='' && @$cardinalpwd!=''){
			$sXML .= '&x_cardholder_authentication_value=' . urldecode(@$_SESSION['Cavv']) . '&x_authentication_indicator=' . (int)@$_SESSION['EciFlag'];
		}
		if($cardname!=''){
			if(strstr($cardname,' ')){
				$namearr = explode(' ',$cardname,2);
				$sXML .= '&x_first_name=' . urlencode($namearr[0]) . '&x_last_name=' . urlencode($namearr[1]);
			}else
				$sXML .= '&x_last_name=' . urlencode($cardname);
		}
		$sXML .= '&x_address=' . urlencode($rs['ordAddress']);
		if($rs['ordAddress2']!='') $sXML .= urlencode(', ' . $rs['ordAddress2']);
		$sXML .= '&x_city=' . urlencode($rs['ordCity']) . '&x_state=' . urlencode($rs['ordState']) . '&x_zip=' . urlencode($rs['ordZip']) . '&x_country=' . urlencode($rs['ordCountry']) . '&x_phone=' . urlencode($rs['ordPhone']) . '&x_email=' . urlencode($rs['ordEmail']);
		if(trim($rs['ordShipName'])!='' || trim($rs['ordShipLastName'])!='' || $rs['ordShipAddress']!=''){
			if(@$usefirstlastname)
				$sXML .= '&x_ship_to_first_name=' . urlencode(trim($rs['ordShipName'])) . '&x_ship_to_last_name=' . urlencode(trim($rs['ordShipLastName']));
			elseif(strpos(trim($rs['ordShipName']),' ')!==FALSE){
				$namearr = explode(' ',trim($rs['ordShipName']),2);
				$sXML .= '&x_ship_to_first_name=' . urlencode($namearr[0]) . '&x_ship_to_last_name=' . urlencode($namearr[1]);
			}else
				$sXML .= '&x_ship_to_last_name=' . urlencode(trim($rs['ordShipName']));
			$sXML .= '&x_ship_to_address=' . urlencode($rs['ordShipAddress']);
			if($rs['ordShipAddress2']!='') $sXML .= urlencode(', ' . $rs['ordShipAddress2']);
			$sXML .= '&x_ship_to_city=' . urlencode($rs['ordShipCity']) . '&x_ship_to_state=' . urlencode($rs['ordShipState']) . '&x_ship_to_zip=' . urlencode($rs['ordShipZip']) . '&x_ship_to_country=' . urlencode($rs['ordShipCountry']);
		}
		if(trim($rs['ordIP'])!='') $sXML .= '&x_customer_ip=' . urlencode(trim($rs['ordIP']));
		if($demomode) $sXML .= '&x_test_request=TRUE';
		if($vsAUTHCODE==''){
			if(@$authnetemulateurl=='') $authnetemulateurl='https://secure.authorize.net/gateway/transact.dll';
			$success=TRUE;
			if($blockuser){
				$success = FALSE;
				$vsRESPMSG = $multipurchaseblockmessage;
			}else
				$success = callcurlfunction($authnetemulateurl, $sXML, $res, '', $vsRESPMSG, TRUE);
			if($success){
				$varString = explode('|', $res);
				if(count($varString)<20){
					$vsRESPMSG='Invalid response: ' . $res;
				}else{
					$vsRESULT=$varString[0];
					$vsERRCODE=$varString[2];
					$vsRESPMSG=$varString[3];
					$vsAUTHCODE=$varString[4];
					$vsAVSADDR=$varString[5];
					$vsTRANSID=$varString[6];
					$vsCVV2=$varString[38];
					if((int)$vsRESULT==1){
						if(trim(@$_POST['accountnum'])!='') $vsAUTHCODE='eCheck';
						$vsRESULT='0'; // Keep in sync with Payflow Pro
						mysql_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . $ordID . "'") or print(mysql_error());
						mysql_query("UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAVS='".escape_string($vsAVSADDR)."',ordCVV='".escape_string($vsCVV2)."',ordAuthNumber='" . escape_string($vsAUTHCODE) . "',ordTransID='" . escape_string($vsTRANSID) . "' WHERE ordID='" . $ordID . "'") or print(mysql_error());
						do_order_success($ordID,$emailAddr,$sendEmail,FALSE,TRUE,TRUE,TRUE);
					}
				}
			}
		}else{
			$vsRESULT='0';
			$vsRESPMSG=$xxTranAp;
			$pos = strpos($vsAUTHCODE, '-');
			if(! ($pos===FALSE))
				$vsAUTHCODE = substr($vsAUTHCODE, $pos + 1);
		}
	}elseif(@$_POST['method']=='14'){ // Custom Payment Processor
		retrieveorderdetails($ordID, $thesessionid);
		$sSQL = "SELECT ordID,ordHandling,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordTotal,ordDiscount,ordIP,ordAuthNumber FROM orders WHERE ordID='" . $ordID . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_assoc($result);
		$ordShipping=$rs['ordShipping'];
		$ordStateTax=$rs['ordStateTax'];
		$ordCountryTax=$rs['ordCountryTax'];
		$ordHSTTax=$rs['ordHSTTax'];
		$ordTotal=$rs['ordTotal'];
		$ordHandling=$rs['ordHandling'];
		$ordDiscount=$rs['ordDiscount'];
		$ordIP=$rs['ordIP'];
		$ordAuthNumber=trim($rs['ordAuthNumber']);
		$vsAUTHCODE=$ordAuthNumber;
		mysql_free_result($result);
		$grandtotal = ($ordShipping+$ordStateTax+$ordCountryTax+$ordHSTTax+$ordTotal+$ordHandling)-$ordDiscount;
		if($vsAUTHCODE==''){
			include './vsadmin/inc/customppreturn.php';
		}else{
			$vsRESULT='0';
			$vsRESPMSG=$xxTranAp;
		}
	}elseif(@$_POST['method']=='18'){ // PayPal Direct
		@set_time_limit(120);
		$data2arr = explode('&',$data2);
		$password=urldecode(@$data2arr[0]);
		$isthreetoken=(trim(urldecode(@$data2arr[2]))=='1');
		$signature=''; $sslcertpath='';
		if($isthreetoken) $signature=urldecode(@$data2arr[1]); else $sslcertpath=urldecode(@$data2arr[1]);
		$sSQL = "SELECT ordID,ordName,ordLastName,ordCity,ordState,ordCountry,ordPhone,ordHandling,ordZip,ordEmail,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordTotal,ordDiscount,ordAddress,ordAddress2,ordIP,ordAuthNumber,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipCountry,ordShipZip FROM orders WHERE ordID='" . $ordID . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_assoc($result);
		mysql_free_result($result);
		$sSQL = "SELECT countryCode FROM countries WHERE countryName='" . escape_string($rs['ordCountry']) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs2 = mysql_fetch_assoc($result))
			$countryCode = $rs2['countryCode'];
		mysql_free_result($result);
		$sSQL = "SELECT countryCode FROM countries WHERE countryName='" . escape_string($rs['ordShipCountry']) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs2 = mysql_fetch_assoc($result))
			$shipCountryCode = $rs2['countryCode'];
		else
			$shipCountryCode = '';
		mysql_free_result($result);
		if($countryCode=='US' || $countryCode=='CA'){
			$sSQL = "SELECT stateAbbrev FROM states WHERE stateName='" . escape_string($rs['ordState']) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs2 = mysql_fetch_assoc($result)) $rs['ordState']=$rs2['stateAbbrev'];
			mysql_free_result($result);
		}
		if($shipCountryCode=='US' || $shipCountryCode=='CA'){
			$sSQL = "SELECT stateAbbrev FROM states WHERE stateName='" . escape_string($rs['ordShipState']) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs2 = mysql_fetch_assoc($result)) $rs['ordShipState']=$rs2['stateAbbrev'];
			mysql_free_result($result);
		}
		$vsAUTHCODE = trim($rs['ordAuthNumber']);
		splitname($cardname, $firstname, $lastname);
		$cardtype = getcctypefromnum($cardnum);
		if(trim($rs['ordShipAddress'])!='') $doship='Ship'; else $doship='';
		$sXML = ppsoapheader($data1, $password, $signature) .
			'<soap:Body><DoDirectPaymentReq xmlns="urn:ebay:api:PayPalAPI">' .
			'<DoDirectPaymentRequest><Version xmlns="urn:ebay:apis:eBLBaseComponents">60.00</Version>' .
			'  <DoDirectPaymentRequestDetails xmlns="urn:ebay:apis:eBLBaseComponents">' .
			addtag('PaymentAction',$ppmethod==1?'Authorization':'Sale') .
			'    <PaymentDetails>' .
			'      <OrderTotal currencyID="' . $countryCurrency . '">' . number_format(($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordHSTTax']+$rs['ordTotal']+$rs['ordHandling'])-$rs['ordDiscount'],2,'.','') . '</OrderTotal>' .
			addtag('ButtonSource','ecommercetemplates_Cart_DP_US') .
			addtag('NotifyURL',$storeurl . 'vsadmin/ppconfirm.php') .
			addtag('Custom',$ordID) .
			'      <ShipToAddress><Name>' . vrxmlencode(trim($rs['ord'.$doship.'Name'].' '.$rs['ord'.$doship.'LastName'])) . '</Name><Street1>' . vrxmlencode($rs['ord'.$doship.'Address']) . '</Street1><Street2>' . vrxmlencode($rs['ord'.$doship.'Address2']) . '</Street2><CityName>' . $rs['ord'.$doship.'City'] . '</CityName><StateOrProvince>' . $rs['ord'.$doship.'State'] . '</StateOrProvince><Country>' . ($doship!='' ? $shipCountryCode : $countryCode) . '</Country><PostalCode>' . $rs['ord'.$doship.'Zip'] . '</PostalCode></ShipToAddress>' .
			'    </PaymentDetails><CreditCard>' .
			addtag('CreditCardType',$cardtype) . addtag('CreditCardNumber',vrxmlencode($cardnum)) . addtag('ExpMonth',$exmon) . addtag('ExpYear',$exyear) .
			'      <CardOwner>' .
			addtag('Payer',vrxmlencode($rs['ordEmail'])) .
			'<PayerName>' . addtag('FirstName',vrxmlencode($firstname)) . addtag('LastName',vrxmlencode($lastname)) . '</PayerName>' . addtag('PayerCountry',$countryCode) .
			'        <Address>' . addtag('Street1',vrxmlencode($rs['ordAddress'])) . addtag('Street2',vrxmlencode($rs['ordAddress2'])) . addtag('CityName',$rs['ordCity']) . addtag('StateOrProvince',$rs['ordState']) . addtag('Country',$countryCode) . addtag('PostalCode',$rs['ordZip']) . '</Address>' .
			'      </CardOwner>' .
			addtag('CVV2',$cvv2);
		if($issuenum!=''){
			if(strlen($issuenum)==2) $sXML .= addtag('IssueNumber',$issuenum); else $sXML .= addtag('StartMonth',substr($issuenum,0,2)) . addtag('StartYear',substr($issuenum,2));
		}
		if(@$cardinalprocessor!='' && @$cardinalmerchant!='' && @$cardinalpwd!=''){
			$sXML .= '<ThreeDSecureRequest>' . addtag('AuthStatus3ds',@$_SESSION['PAResStatus']) . addtag('MpiVendor3ds',@$_SESSION['centinel_enrolled']) . addtag('Cavv',@$_SESSION['Cavv']) . addtag('Eci3ds',@$_SESSION['EciFlag']) . addtag('Xid',@$_SESSION['Xid']) . '</ThreeDSecureRequest>';
		}
		$sXML .= ' </CreditCard>' .
			addtag('IPAddress',trim($rs['ordIP'])) . addtag('MerchantSessionId',$rs['ordID']) . 
			'  </DoDirectPaymentRequestDetails>' .
			'</DoDirectPaymentRequest></DoDirectPaymentReq></soap:Body></soap:Envelope>';
		if($demomode) $sandbox = '.sandbox'; else $sandbox = '';
		$vsRESULT='-1';
		if($vsAUTHCODE==''){
			if($blockuser){
				$success=FALSE;
				$vsRESPMSG = $multipurchaseblockmessage;
			}else
				$success = callcurlfunction('https://api-aa' . ($isthreetoken ? '-3t' : '') . $sandbox . '.paypal.com/2.0/', $sXML, $res, $sslcertpath, $vsRESPMSG, TRUE);
			if($success){
				$xmlDoc = new vrXMLDoc($res);
				$vsAUTHCODE='';$vsERRCODE='';$vsRESPMSG='';$vsAVSADDR='';$vsTRANSID='';$vsCVV2='';
				$nodeList = $xmlDoc->nodeList->childNodes[0];
				for($i = 0; $i < $nodeList->length; $i++){
					if($nodeList->nodeName[$i]=='SOAP-ENV:Body'){
						$e = $nodeList->childNodes[$i];
						for($j = 0; $j < $e->length; $j++){
							if($e->nodeName[$j]=='DoDirectPaymentResponse'){
								$ee = $e->childNodes[$j];
								for($jj = 0; $jj < $ee->length; $jj++){
									if($ee->nodeName[$jj]=='Ack'){
										if($ee->nodeValue[$jj]=='Success' || $ee->nodeValue[$jj]=='SuccessWithWarning'){
											$vsRESULT=1;
											$vsRESPMSG = $xxTranAp;
										}
									}elseif($ee->nodeName[$jj]=='TransactionID'){
										$vsAUTHCODE=$ee->nodeValue[$jj];
									}elseif($ee->nodeName[$jj]=='AVSCode'){
										$vsAVSADDR=$ee->nodeValue[$jj];
									}elseif($ee->nodeName[$jj]=='CVV2Code'){
										$vsCVV2=$ee->nodeValue[$jj];
									}elseif($ee->nodeName[$jj]=='Errors'){
										$themsg='';
										$thecode='';
										$iswarning=FALSE;
										$ff = $ee->childNodes[$jj];
										for($kk = 0; $kk < $ff->length; $kk++){
											if($ff->nodeName[$kk]=='ShortMessage'){
												//$vsRESPMSG=$ff->nodeValue[$kk].'<br>'.$vsRESPMSG;
											}elseif($ff->nodeName[$kk]=='LongMessage'){
												$themsg=$ff->nodeValue[$kk];
											}elseif($ff->nodeName[$kk]=='ErrorCode'){
												$thecode=$ff->nodeValue[$kk];
											}elseif($ff->nodeName[$kk]=='SeverityCode'){
												$iswarning=($ff->nodeValue[$kk]=='Warning');
											}
										}
										if(! $iswarning){
											$vsRESPMSG=$themsg;
											$vsERRCODE=$thecode;
										}
									}
								}
							}
						}
					}
				}
				if((int)$vsRESULT==1){
					$vsRESULT='0'; // Keep in sync with Payflow Pro
					mysql_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . $ordID . "'") or print(mysql_error());
					mysql_query("UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAVS='".escape_string($vsAVSADDR)."',ordCVV='".escape_string($vsCVV2)."',ordAuthNumber='" . escape_string($vsAUTHCODE) . "' WHERE ordID='" . $ordID . "'") or print(mysql_error());
					do_order_success($ordID,$emailAddr,$sendEmail,FALSE,TRUE,TRUE,TRUE);
				}elseif($vsERRCODE!='')
					$vsERRCODE = (int)$vsERRCODE;
			}
		}else{
			$vsRESULT='0';
			$vsRESPMSG=$xxTranAp;
			$pos = strpos($vsAUTHCODE, "-");
			if(! ($pos===FALSE))
				$vsAUTHCODE = substr($vsAUTHCODE, $pos + 1);
		}
	}elseif(@$_POST['method']=='10'){ // Capture Card
		print 'DISABLED!!<br />';
	}else{
		print 'Error';
		exit;
	}
	$_SESSION['centinelok']='';
	if($centinelenrolled!='Y'){
		logevent(substr(getipaddress(), 0, 24),'TRANSACTION',$vsRESULT=='0','cart.php','ORDERS');
?>	<br />
<?php	if($vsRESULT=='0'){ ?>
	  <form method="post" action="thanks.php" name="checkoutform">
		<input type="hidden" name="xxpreauth" value="<?php print $ordID?>" />
		<input type="hidden" name="xxpreauthmethod" value="<?php print (int)@$_POST['method']?>" />
		<input type="hidden" name="thesessionid" value="<?php print $thesessionid?>" />
		<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		  <tr><td class="cobhl" align="center" colspan="2" height="30"><strong><?php print $xxTnxOrd?></strong></td></tr>
		  <tr>
			<td class="cobhl" align="right" height="30" width="50%"><strong><?php print $xxTrnRes?>:</strong></td>
			<td class="cobll" align="left"><strong><?php print $vsRESPMSG?></strong></td>
		  </tr>
		  <tr>
			<td class="cobhl" align="right" height="30"><strong><?php print $xxOrdNum?>:</strong></td>
			<td class="cobll" align="left"><strong><?php print $ordID?></strong></td>
		  </tr>
		  <tr>
			<td class="cobhl" align="right" height="30"><strong><?php print $xxAutCod?>:</strong></td>
			<td class="cobll" align="left"><strong><?php print $vsAUTHCODE?></strong></td>
		  </tr>
		  <tr>
			<td class="cobll" colspan="2" height="30">
			  <table width="100%" cellspacing="0" cellpadding="0" border="0">
				<tr>
				  <td width="16" height="26">&nbsp;</td>
				  <td class="cobll" width="100%" align="center">&nbsp;<br /><input type="submit" value="<?php print $xxCliCon?>" /><br />&nbsp;</td>
				  <td width="16" align="right" valign="bottom"><img src="images/tablebr.png" alt="" /></td>
				</tr>
			  </table>
			</td>
		  </tr>
		</table>
	  </form>
	  <script language="javascript" type="text/javascript">setTimeout("document.checkoutform.submit()",5000);</script>
<?php	}else{ ?>
	  <form method="post" action="cart.php" name="checkoutform">
		<input type="hidden" name="mode" value="go" />
		<input type="hidden" name="orderid" value="<?php print $ordID?>" />
		<input type="hidden" name="sessionid" value="<?php print $thesessionid?>" />
		<input type="hidden" name="shipselectoridx" value="<?php print @$_SESSION['shipselectoridx']?>" />
		<input type="hidden" name="shipselectoraction" value="<?php print @$_SESSION['shipselectoraction']?>" />
		<input type="hidden" name="altrates" value="<?php print @$_SESSION['altrates']?>" />
		<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		  <tr><td class="cobhl" align="center" colspan="2" height="30"><strong><?php print $xxSorTrn?></strong></td></tr>
		  <tr>
			<td class="cobhl" align="right" height="30" width="50%"><strong><?php print $xxTrnRes?>:</strong></td>
			<td class="cobll" align="left"><strong><?php print (@$vsERRCODE!='' ? '(' . $vsERRCODE . ') ' : '') . $vsRESPMSG?></strong></td>
		  </tr>
		  <tr>
			<td class="cobll" colspan="2" height="30">
			  <table width="100%" cellspacing="0" cellpadding="0" border="0">
				<tr>
				  <td width="16" height="26">&nbsp;</td>
				  <td width="100%" align="center">&nbsp;<br /><input type="submit" value="<?php print $xxGoBack?>" /><br />&nbsp;</td>
				  <td width="16" align="right" valign="bottom"><img src="images/tablebr.png" alt="" /></td>
				</tr>
			  </table>
			</td>
		  </tr>
		</table>
	  </form>
<?php	}
	}
}elseif($checkoutmode=='mailinglistsignup'){
	addtomailinglist(@$_POST['mlsuemail'],@$_POST['mlsuname']);
	print '<div style="padding:24px;text-align:center;font-weight:bold">&nbsp;<br />&nbsp;<br />' . $xxThkSub . '</div>';

	if($warncheckspamfolder==TRUE) print '<div style="padding:24px;text-align:center;"><span style="color:#FF0000">' . $xxSpmWrn . '</span></div>';

	if(@$_POST['rp']!='') $thehref = htmlspecials(str_replace(array('"','<'),'',@$_POST['rp'])); else $thehref = $xxHomeURL;
	print '<div style="padding:24px;text-align:center;font-weight:bold">' . imageorbutton(@$imgcontinueshopping, $xxCntShp, '', $thehref, FALSE) . '<br />&nbsp;</div>';
	$_SESSION['MLSIGNEDUP']=TRUE;
}
if(@$_GET['emailconf']!='' || @$_GET['unsubscribe']!=''){
	if(@$_GET['emailconf']!='') $theemail=trim($_GET['emailconf']); else $theemail=trim($_GET['unsubscribe']);
	$sSQL = "SELECT email,isconfirmed FROM mailinglist WHERE email='" . escape_string($theemail) . "'";
	$result = mysql_query($sSQL) or print(mysql_error());
	$foundemail=FALSE;
	if($rs = mysql_fetch_assoc($result)){
		$foundemail=TRUE;
		$isconfirmed=($rs['isconfirmed']!=0);
	}
	mysql_free_result($result);
?>
	<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
	  <tr><td class="cobhl" align="center" height="26" colspan="6"><strong><?php print $xxMLConf?></strong></td></tr>
	  <tr><td class="cobll" align="center" height="26">&nbsp;<br/><?php
	if(! $foundemail)
		print $xxEmNtFn;
	elseif(@$_GET['unsubscribe']!=''){
		mysql_query("DELETE FROM mailinglist WHERE email='" . escape_string($theemail) . "'") or print(mysql_error());
		print $xxSucUns;
	}elseif($isconfirmed)
		print $xxAllSub;
	else{
		$thecheck = substr(md5($uspsUser.$upsUser.$origZip.@$checksumtext.':'.$theemail), 0, 10);
		if($thecheck==$_GET['check']){
			mysql_query("UPDATE mailinglist SET isconfirmed=1 WHERE email='" . escape_string($theemail) . "'") or print(mysql_error());
			print $xxSubAct;
		}else
			print $xxSubNAc;
	} ?>
	<br /><br /><a class="ectlink" href="<?php print $xxHomeURL?>" onmouseover="window.status='<?php print str_replace("'","\'",$xxCntShp)?>';return true" onmouseout="window.status='';return true"><strong><?php print $xxCntShp?></strong></a><br />&nbsp;
	</td></tr></table>
<?php
}elseif((@$_GET['token']=='' || $checkoutmode=='paypalcancel') && ($checkoutmode=='dologin' || $checkoutmode=='donewaccount' || $checkoutmode=='update' || $checkoutmode=='paypalcancel' || $checkoutmode=='savecart' || $checkoutmode=='movetocart' || $checkoutmode=='') && $cartisincluded!=TRUE){
	if(getsessionid()=='') print 'The PHP session has not been started. This can cause problems with the shopping cart function. For help please go to <a class="ectlink" href="http://www.ecommercetemplates.com/support/">http://www.ecommercetemplates.com/support/</a>';
	$gshipmethods=array();
	function writeuniquegoogleshipmethod($theshipmethod){
		global $countryCurrency,$sXML,$googledefaultshipping,$gshipmethods;
		if(@$googledefaultshipping=='') $googledefaultshipping='999.99';
		$gotshipmethod=FALSE;
		if(! in_array($theshipmethod,$gshipmethods)){
			array_push($gshipmethods, $theshipmethod);
			$sXML .= '<merchant-calculated-shipping name="' . $theshipmethod . '"><price currency="' . $countryCurrency . '">' . $googledefaultshipping . '</price><shipping-restrictions><allowed-areas><world-area/></allowed-areas><excluded-areas/></shipping-restrictions><address-filters><allowed-areas><world-area /></allowed-areas></address-filters></merchant-calculated-shipping>';
		}
	}
	function generatemerchantcalcshiptypes($theshiptype){
		global $countryCurrency,$sXML,$xxShipHa,$somethingToShip,$googledefaultshipping,$splitUSZones,$gshipmethods;
		if($theshiptype==1 || ! $somethingToShip){
			writeuniquegoogleshipmethod(xmlencodecharref($xxShipHa));
		}elseif($theshiptype==2 || $theshiptype==5){
			for($index3=1; $index3<=5; $index3++){
				$sSQL = "SELECT DISTINCT pzMethodName" . $index3 . " FROM postalzones WHERE pzName<>'' AND pzMethodName" . $index3 . "<>''";
				if(! $splitUSZones) $sSQL .= ' AND pzID < 100';
				$result = mysql_query($sSQL) or print(mysql_error());
				while($rs = mysql_fetch_assoc($result)){
					writeuniquegoogleshipmethod(trim(xmlencodecharref($rs['pzMethodName' . $index3])));
				}
			}
		}elseif($theshiptype==3 || $theshiptype==4 || $theshiptype==6 || $theshiptype==7){
			if($theshiptype==3) $startid=0;
			if($theshiptype==4) $startid=1;
			if($theshiptype==6) $startid=2;
			if($theshiptype==7) $startid=3;
			if($theshiptype==8) $startid=4;
			$sSQL = "SELECT DISTINCT uspsShowAs,uspsFSA FROM uspsmethods WHERE (uspsID>" . ($startid*100) . " AND uspsID<" . (($startid+1)*100) . ") AND uspsUseMethod=1 ORDER BY uspsFSA DESC,uspsShowAs";
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_assoc($result)){
				writeuniquegoogleshipmethod(xmlencodecharref($rs['uspsShowAs']));
			}
		}
	}
	function writegoogleparams($data1, $data2, $demomode){
		global $shipType,$adminIntShipping,$willpickuptext,$willpickupcost,$countryCurrency,$storeurl,$googlelineitems,$thesessionid,$sXML,$gcallbackpath,$googlecart,$googlecallbackscript,$orighandling,$xxShipHa,$origCountryCode,$parameterizedurl,$parameterizedurlparams,$dateadjust,$minloglevel;
		$sSQL = "SELECT cpnID FROM coupons WHERE cpnIsCoupon=1 AND cpnNumAvail>0 AND cpnEndDate>='" . date('Y-m-d', time()+($dateadjust*60*60)) . '\' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result)==0) $acoupondefined='false'; else $acoupondefined='true';
		mysql_free_result($result);
		$sSQL = "SELECT gcID FROM giftcertificate WHERE gcAuthorized<>0";
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result)==0) $agiftcertdefined='false'; else $agiftcertdefined='true';
		mysql_free_result($result);
		$sXML = '<?xml version="1.0" encoding="UTF-8"?><checkout-shopping-cart xmlns="http://checkout.google.com/schema/2"><shopping-cart>';
		$sXML .= '<items>' . $googlelineitems . '</items>';
		$sXML .= '<merchant-private-data><privateitems><sessionid>' . (@$_SESSION['clientID']!='' ? 'cid' . $_SESSION['clientID'] : 'sid' . $thesessionid) . '</sessionid>' . addtag('partner',xmlencodecharref(trim(@$_COOKIE['PARTNER']))) . '</privateitems></merchant-private-data></shopping-cart>';
		$sXML .= '<checkout-flow-support><merchant-checkout-flow-support><platform-id>236638029623651</platform-id>';
		if(@$parameterizedurl!=''){
			$sXML .= '<parameterized-urls><parameterized-url url="' . $parameterizedurl . '"><parameters>';
			if(@$parameterizedurlparams!=''){
				$urlparams = explode('&', $parameterizedurlparams);
				foreach($urlparams as $urlparam){
					$sXML .= '<url-parameter name="' . str_replace('=', '" type="', $urlparam) . '" />';
				}
			}
			$sXML .= '</parameters></parameterized-url></parameterized-urls>';
		}
		$sXML .= '<edit-cart-url>' . $storeurl . 'cart.php</edit-cart-url><continue-shopping-url>' . $storeurl . 'categories.php</continue-shopping-url>';
		$sXML .= '<shipping-methods>';
		generatemerchantcalcshiptypes($shipType);
		if($adminIntShipping!=0 && $adminIntShipping!=$shipType) generatemerchantcalcshiptypes($adminIntShipping);
		if(@$willpickuptext!=''){
			if(@$willpickupcost=='') $willpickupcost=0;
			$sXML .= '<merchant-calculated-shipping name="' . xmlencodecharref($willpickuptext) . '"><price currency="' . $countryCurrency . '">' . $willpickupcost . '</price></merchant-calculated-shipping>';
		}
		if($shipType==0 && $adminIntShipping==0 && $orighandling!=0)
			writeuniquegoogleshipmethod(xmlencodecharref($xxShipHa));
		$sXML .= '</shipping-methods>';
		$sXML .= '<request-buyer-phone-number>true</request-buyer-phone-number><tax-tables merchant-calculated="true"><default-tax-table><tax-rules></tax-rules></default-tax-table></tax-tables>';
		$sXML .= '<merchant-calculations>' . addtag('merchant-calculations-url',$gcallbackpath) . (($origCountryCode=='GB' || $origCountryCode=='IE') ? '' : addtag('accept-merchant-coupons',$acoupondefined)) . addtag('accept-gift-certificates',$agiftcertdefined) . '</merchant-calculations></merchant-checkout-flow-support></checkout-flow-support>';
		$sXML .= '</checkout-shopping-cart>';
		$googlecart = base64_encode($sXML);
		$thesignature = base64_encode(CalcHmacSha1($sXML,$data2));
		$theurl = 'https://' . ($demomode ? 'sandbox' : 'checkout') . '.google.com' . ($demomode ? '/checkout' : '') . '/api/checkout/v2/checkout/Merchant/' . $data1; // . '/diagnose';
		print whv('signature', $thesignature);
		print whv('analyticsdata', '');
		return($theurl);
	}
	$requiressl = FALSE;
	if(@$pathtossl==''){
		$sSQL = 'SELECT payProvID FROM payprovider WHERE payProvEnabled=1 AND (payProvID IN (7,10,12,13' . (@$paypalhostedsolution?'':',18') . ") OR (payProvID=16 AND payProvData2='1'))"; // All the ones that require SSL
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result) > 0) $requiressl = TRUE;
		mysql_free_result($result);
	}
	if(@$googlecallbackscript=='') $googlecallbackscript='vsadmin/gcallback.php';
	if($requiressl || @$pathtossl!=''){
		if(@$pathtossl!=''){
			if(substr($pathtossl,-1)!='/') $pathtossl .= '/';
			$cartpath = $pathtossl . 'cart.php';
			$gcallbackpath = $pathtossl . $googlecallbackscript;
		}else{
			$cartpath = str_replace('http:','https:',$storeurl) . 'cart.php';
			$gcallbackpath = str_replace('http:','https:',$storeurl) . $googlecallbackscript;
		}
	}else{
		$cartpath='cart.php';
		$gcallbackpath= $storeurl . $googlecallbackscript;
	}
	$loginerror='';
	if(@$_GET['mode']=='logout'){
		$_SESSION['clientID']=NULL; unset($_SESSION['clientID']);
		$_SESSION['clientUser']=NULL; unset($_SESSION['clientUser']);
		$_SESSION['clientActions']=NULL; unset($_SESSION['clientActions']);
		$_SESSION['clientLoginLevel']=NULL; unset($_SESSION['clientLoginLevel']);
		$_SESSION['clientPercentDiscount']=NULL; unset($_SESSION['clientPercentDiscount']);
		$xxSryEmp=$xxLOSuc;
		print '<script src="vsadmin/savecookie.php?WRITECLL=x&WRITECLP=&permanent=Y"></script>';
		if(@$pathtossl!='') print '<script src="'.$pathtossl.'vsadmin/savecookie.php?WRITECLL=x&WRITECLP=&permanent=Y"></script>';
	}
	$loginsuccess=FALSE;
	if($checkoutmode=='dologin' || ($checkoutmode=='donewaccount' && @$allowclientregistration==TRUE)){
		$loginsuccess=TRUE;
		$clientEmail = cleanupemail(unstripslashes(@$_POST['email']));
		$clientPW = trim(str_replace("'",'',dohashpw(@$_POST['pass'])));
		if($checkoutmode=='donewaccount'){
			if(trim(@$_POST['name'])!='' && $clientPW!='' && strpos($clientEmail,'@')!==FALSE && strpos($clientEmail,'.')!==FALSE){
				$sSQL = "SELECT clID FROM customerlogin WHERE clEmail='" . escape_string($clientEmail) . "'";
				$result = mysql_query($sSQL) or print(mysql_error());
				if(mysql_num_rows($result) > 0){
					$loginsuccess=FALSE;
					$loginerror=$xxEmExi;
				}
			}else{
				$loginsuccess=FALSE;
				$loginerror='Invalid login details';
			}
			if($loginsuccess && (strpos(@$_POST['name'],'<')!==FALSE || strpos(@$_POST['name'],'>')!==FALSE)){
				$loginsuccess=FALSE;
				$loginerror='Invalid Characters in Login Name';
			}
			if($loginsuccess){
				if(@$defaultcustomerloginlevel=='') $defaultcustomerloginlevel=0;
				if(@$defaultcustomerloginactions=='') $defaultcustomerloginactions=0;
				if(@$defaultcustomerlogindiscount=='') $defaultcustomerlogindiscount=0; else $defaultcustomerloginactions=(((int)$defaultcustomerloginactions)|16);
				$sSQL = "INSERT INTO customerlogin (clUserName,clEmail,clPw,clDateCreated,clLoginLevel,clActions,clPercentDiscount) VALUES ('" . escape_string(unstripslashes(@$_POST['name'])) . "','" . escape_string($clientEmail) . "','" . escape_string($clientPW) . "','" . date('Y-m-d', time()+($dateadjust*60*60)) . "','".$defaultcustomerloginlevel."','".$defaultcustomerloginactions."','".$defaultcustomerlogindiscount."')";
				mysql_query($sSQL) or print(mysql_error());
				if(@$_POST['allowemail']=='ON') addtomailinglist($clientEmail,unstripslashes(@$_POST['name']));
			}
		}
		if($loginsuccess){
			$sSQL = "SELECT clID,clUserName,clActions,clLoginLevel,clPercentDiscount FROM customerlogin WHERE (clEmail<>'' AND clEmail='" . escape_string($clientEmail) . "' AND clPW='" . escape_string($clientPW) . "') OR (clEmail='' AND clUserName='" . escape_string($clientEmail) . "' AND clPW='" . escape_string($clientPW) . "')";
			$result = mysql_query($sSQL) or print(mysql_error());
			$loginsuccess=FALSE;
			if($rs = mysql_fetch_assoc($result)){
				$_SESSION['clientID']=$rs['clID'];
				$_SESSION['clientUser']=$rs['clUserName'];
				$_SESSION['clientActions']=$rs['clActions'];
				$_SESSION['clientLoginLevel']=$rs['clLoginLevel'];
				$_SESSION['clientPercentDiscount']=(100.0-(double)$rs['clPercentDiscount'])/100.0;
				get_wholesaleprice_sql();
				print '<script src="vsadmin/savecookie.php?WRITECLL=' . $clientEmail . '&WRITECLP=' . $clientPW;
				if(@$_POST['cook']=='ON') print '&permanent=Y';
				print '"></script>';
				$loginsuccess=TRUE;
			}else
				$loginerror=$xxNoLogD;
		}
		if($loginsuccess){
			$cartchanged = FALSE;
			$sSQL = "SELECT ordID FROM orders WHERE ordStatus>1 AND ordAuthNumber='' AND (ordSessionID='" . escape_string($thesessionid) . "' OR ordClientID='" . escape_string($_SESSION['clientID']) . "')";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				release_stock($rs['ordID']);
				mysql_query("UPDATE cart SET cartSessionID='".escape_string(getsessionid())."',cartClientID='" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "' WHERE cartCompleted=0 AND cartOrderID=" . $rs['ordID']) or print(mysql_error());
				mysql_query("UPDATE orders SET ordAuthStatus='MODWARNOPEN',ordShipType='MODWARNOPEN' WHERE ordID=" . $rs['ordID']) or print(mysql_error());
			}
			mysql_free_result($result);
			$sSQL = "SELECT cartID,cartProdID FROM cart WHERE cartCompleted=0 AND cartClientID='" . escape_string($_SESSION['clientID']) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			while($cartarr = mysql_fetch_assoc($result)){
				$hasoptions=TRUE;
				$sSQL = "SELECT cartID,cartQuantity FROM cart WHERE cartClientID=0 AND cartCompleted=0 AND cartSessionID='" . escape_string($thesessionid) . "' AND cartProdID='" . escape_string($cartarr['cartProdID']) . "'";
				$result2 = mysql_query($sSQL) or print(mysql_error());
				if($rs = mysql_fetch_assoc($result2)){ $thecartid=$rs['cartID']; $thequant=$rs['cartQuantity']; } else $thecartid='';
				if($thecartid!=''){ // check options
					$optarr1cnt=0; $optarr2cnt=0;
					$sSQL = "SELECT coOptID,coCartOption FROM cartoptions WHERE coCartID=" . $cartarr['cartID'];
					$result3 = mysql_query($sSQL) or print(mysql_error());
					while($rs2 = mysql_fetch_assoc($result3))
						$optarr1[$optarr1cnt++]=$rs2;
					$sSQL = "SELECT coOptID,coCartOption FROM cartoptions WHERE coCartID=" . $thecartid;
					$result3 = mysql_query($sSQL) or print(mysql_error());
					while($rs2 = mysql_fetch_assoc($result3))
						$optarr2[$optarr2cnt++]=$rs2;
					if($optarr1cnt!=$optarr2cnt) $hasoptions=FALSE;
					if($optarr1cnt > 0 && $optarr2cnt > 0){
						if($hasoptions){
							for($index2=0; $index2 < $optarr1cnt; $index2++){
								$hasthisoption=FALSE;
								for($index3=0; $index3 < $optarr2cnt; $index3++){
									if($optarr1[$index2]['coOptID']==$optarr2[$index3]['coOptID'] && $optarr1[$index2]['coCartOption']==$optarr2[$index3]['coCartOption']) $hasthisoption=TRUE;
								}
								if(! $hasthisoption) $hasoptions=FALSE;
							}
						}
					}
				}
				if($thecartid!='' && $hasoptions){
					mysql_query("DELETE FROM cart WHERE cartID='".escape_string($cartarr['cartID'])."'") or print(mysql_error());
					mysql_query("DELETE FROM cartoptions WHERE coCartID='".escape_string($cartarr['cartID'])."'") or print(mysql_error());
				}
			}
			mysql_free_result($result);
			$sSQL = "UPDATE cart SET cartClientID='" . escape_string($_SESSION['clientID']) . "' WHERE cartClientID=0 AND cartCompleted=0 AND cartSessionID='" . escape_string($thesessionid) . "'";
			mysql_query($sSQL) or print(mysql_error());
			$sSQL = "SELECT cartID,cartProdID,cartProdPrice,pID,".$WSP."pPrice FROM cart LEFT JOIN products ON cart.cartProdId=products.pID WHERE cartClientID='" . escape_string($_SESSION['clientID']) . "' AND cartCompleted=0 AND cartProdID<>'".$giftcertificateid."' AND cartProdID<>'".$donationid."'";
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_assoc($result)){
				if(is_null($rs['pID'])){
					$cartchanged = TRUE;
					mysql_query("DELETE FROM cart WHERE cartID='".escape_string($rs['cartID'])."'") or print(mysql_error());
					mysql_query("DELETE FROM cartoptions WHERE coCartID='".escape_string($rs['cartID'])."'") or print(mysql_error());
				}else{
					$newprice = checkpricebreaks($rs['cartProdID'],$rs['pPrice']);
					if($rs['cartProdPrice']!=$newprice) $cartchanged = TRUE; // recalculate wholesale price plus quant discounts
					$sSQL = 'SELECT coID,coPriceDiff,'.$OWSP."optPriceDiff,optFlags FROM cart INNER JOIN cartoptions ON cart.cartID=cartoptions.coCartID INNER JOIN options ON cartoptions.coOptID=options.optID INNER JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optType IN (-4,-2,-1,1,2,4) AND cartID='".$rs['cartID']."'";
					$result2 = mysql_query($sSQL) or print(mysql_error());
					while($rs2 = mysql_fetch_assoc($result2)){
						$sSQL = 'UPDATE cartoptions SET coPriceDiff='.(($rs2['optFlags']&1)==0 ? $rs2['optPriceDiff'] : round(($rs2['optPriceDiff'] * $newprice)/100.0, 2))." WHERE coID='".$rs2['coID']."'";
						mysql_query($sSQL) or print(mysql_error());
					}
					mysql_free_result($result2);
				}
			}
			mysql_free_result($result);
			eval('$theref = @$clientloginref' . @$_SESSION['clientLoginLevel'] . ';');
			if($theref!='') $clientloginref=$theref;
			if($cartchanged)
				$refURL = 'cart.php?mode=acct&cartchanged=true';
			elseif($checkoutmode=='donewaccount' && $warncheckspamfolder)
				$refURL = 'cart.php?mode=acct&warncheckspamfolder=true';
			elseif(@$clientloginref=='referer' || @$clientloginref=='')
				if(trim(@$_POST['refurl'])!='') $refURL = trim(@$_POST['refurl']); else $refURL = 'cart.php?mode=acct';
			else
				$refURL = $clientloginref;
			$_SESSION['xsshipping']=NULL; unset($_SESSION['xsshipping']);
			$_SESSION['discounts']=NULL; unset($_SESSION['discounts']);
			$_SESSION['xscountrytax']=NULL; unset($_SESSION['xscountrytax']);
			print '<meta http-equiv="Refresh" content="1; URL=' . $refURL . '">';
		}
	}
	$addextrarows=0;
	$wantstateselector=(FALSE||@$forcestateselector||@$defaultshipstate!='');
	$wantcountryselector=FALSE;
	$wantzipselector=FALSE;
	if($shipType==0) $estimateshipping=FALSE;
	if(@$estimateshipping==TRUE){
		$shiphomecountry=TRUE;
		if($cartisincluded!=TRUE){
			if(@$_SESSION['clientID']!='' && @$_POST['country']=='' && @$_SESSION['country']=='' && $shipType>=2){
				$sSQL = "SELECT addState,addCountry,addZip FROM address WHERE addCustID='".escape_string($_SESSION['clientID'])."' ORDER BY addIsDefault DESC";
				$result = mysql_query($sSQL) or print(mysql_error());
				if($rs = mysql_fetch_assoc($result)){
					$_SESSION['country'] = $rs['addCountry']; $_SESSION['state'] = $rs['addState']; $_SESSION['zip'] = $rs['addZip'];
				}
				mysql_free_result($result);
			}
			if(@$_POST['state']!=''){
				$shipstate = unstripslashes(@$_POST['state']);
				$_SESSION['state'] = unstripslashes(@$_POST['state']);
			}elseif(@$_SESSION['state']!='')
				$shipstate = $_SESSION['state'];
			else
				$shipstate = @$defaultshipstate;
			if(@$_POST['zip']!=''){
				$destZip = unstripslashes(@$_POST['zip']);
				$_SESSION['zip'] = unstripslashes(@$_POST['zip']);
			}elseif(@$_SESSION['zip']!='')
				$destZip = $_SESSION['zip'];
			elseif(! (@$nodefaultzip==TRUE))
				$destZip = $origZip;
			else
				$destZip = '';
			if(@$_POST['country']!=''){
				$shipcountry = unstripslashes(@$_POST['country']);
				$_SESSION['country'] = unstripslashes(@$_POST['country']);
			}elseif(@$_SESSION['country']!='')
				$shipcountry = $_SESSION['country'];
			else{
				$shipCountryCode = $origCountryCode;
				$shipcountry = $origCountry;
			}
		}
		$sSQL = "SELECT countryID,countryTax,countryCode,countryFreeShip,countryOrder FROM countries WHERE countryName='" . escape_string($shipcountry) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)){
			if(trim(@$_SESSION['clientID'])!='' && ((int)$_SESSION['clientActions'] & 2)==2) $countryTaxRate=0; else $countryTaxRate = $rs['countryTax'];
			$shipCountryID = $rs['countryID'];
			$shipCountryCode = $rs['countryCode'];
			$freeshipavailtodestination = ($rs['countryFreeShip']==1);
			$shiphomecountry = ($rs['countryOrder']>=2);
		}
		mysql_free_result($result);
		if($shiphomecountry){
			$sSQL = "SELECT stateTax,stateAbbrev,stateFreeShip FROM states WHERE (stateAbbrev='" . escape_string($shipstate) . "' OR stateName='" . escape_string($shipstate) . "')";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$stateTaxRate=$rs['stateTax'];
				$shipStateAbbrev=$rs['stateAbbrev'];
				$freeshipavailtodestination = ($freeshipavailtodestination && ($rs['stateFreeShip']==1));
			}
			mysql_free_result($result);
		}else
			$shipstate = '';
		if(@$_SESSION['xsshipping']=='') initshippingmethods();
		$addextrarows=1;
		if($shipType==2 || $shipType==5){ // weight / price based
			$wantcountryselector=TRUE;
			if($splitUSZones)$wantstateselector=TRUE;
		}elseif($shipType==3 || $shipType==4 || $shipType==6 || $shipType==7 || $shipType==8){
			$wantzipselector=TRUE;
			$wantcountryselector=TRUE;
		}
		if($shipType==4 && @$upsnegdrates==TRUE) $wantstateselector=TRUE;
		if(! @$nodiscounts && ! $wantstateselector){
			$sSQL = "SELECT cpnID FROM coupons WHERE cpnCntry=1 AND cpnType=0 AND cpnSitewide=1 AND cpnNumAvail>0 AND cpnEndDate>='" . date('Y-m-d', time()+($dateadjust*60*60)) ."' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<=".$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)) $statelimiteddiscount=TRUE; else $statelimiteddiscount=FALSE;
			mysql_free_result($result);
			if($statelimiteddiscount){
				$sSQL = 'SELECT stateID FROM states WHERE stateFreeShip=0 AND stateEnabled<>0';
				$result = mysql_query($sSQL) or print(mysql_error());
				if(mysql_num_rows($result)>0) $wantstateselector=TRUE;
				mysql_free_result($result);
			}
		}
		if(($adminAltRates==1 || $adminAltRates==2) && (! $wantzipselector || ! $wantcountryselector)){
			$sSQL = 'SELECT altrateid FROM alternaterates WHERE (usealtmethod<>0 OR usealtmethodintl<>0) AND altrateid IN (3,4,6,7,8)';
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0){ $wantzipselector=TRUE; $wantcountryselector=TRUE; }
			mysql_free_result($result);
		}
		if($wantstateselector)$addextrarows++;
		if($wantcountryselector)$addextrarows++;
		if($wantzipselector)$addextrarows++;
	}else{
		$_SESSION['xsshipping']=NULL; unset($_SESSION['xsshipping']);
	}
	$loyaltypointsavailable=0;
	$redeempoints=TRUE;
	$_SESSION['noredeempoints']='';
	if(@$loyaltypoints!='' && @$_SESSION['clientID']!=''){
		if(@$_GET['redeempoints']=='no'){
			$_SESSION['noredeempoints']=TRUE;
			$redeempoints=FALSE;
		}
		$sSQL="SELECT loyaltyPoints FROM customerlogin WHERE clID=" . $_SESSION['clientID'];
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)){
			$loyaltypointsavailable=$rs['loyaltyPoints']; $addextrarows++;
		}
		mysql_free_result($result);
	}
	$stockalreadysubtracted=FALSE;
	$sSQL = "SELECT ordID FROM orders WHERE ordStatus>1 AND ordAuthNumber='' AND ordAuthStatus<>'MODWARNOPEN' AND " . getordersessionsql();
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result)>0) $stockalreadysubtracted=TRUE;
	mysql_free_result($result);
	if(@$_GET['mode']=='sc') $checkoutmode='savedcart';
	if(@$showtaxinclusive) $addextrarows++;
	if($stockalreadysubtracted) $isInStock=TRUE; else $isInStock = ! do_stock_check(TRUE);
	$alldata='';
	if(@$_GET['pla']!='') $hideoptpricediffs=TRUE;
	if(@$_SESSION['clientID']!='' && @$enablewishlists==TRUE){ ?>
<div id="savecartlist" style="position:absolute; visibility:hidden; top:0px; left:0px; width:auto; height:auto; z-index:10000;" onmouseover="cartoversldiv=true;" onmouseout="cartoversldiv=false;setTimeout('cartchecksldiv()',1000)">
<table class="cobtbl" cellspacing="1" cellpadding="3">
<tr><td class="cobll" align="left" onmouseover="this.className='cobhl'" onmouseout="this.className='cobll'" style="white-space:nowrap"><a class="ectlink wishlistmenu" href="#" onclick="dosaveitem('')"><?php print $xxMyWisL?></a></td></tr>
<?php	$sSQL = "SELECT listID,listName FROM customerlists WHERE listOwner='".escape_string($_SESSION['clientID'])."'";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_assoc($result))
			print '<tr><td class="cobll" align="left" onmouseover="this.className=\'cobhl\'" onmouseout="this.className=\'cobll\'" style="white-space:nowrap"><a class="ectlink wishlistmenu" href="#" onclick="dosaveitem('.$rs['listID'].')">'.htmlspecials($rs['listName']).'</a></td></tr>';
		mysql_free_result($result); ?>
<tr id="savelistcartrow"><td class="cobll" align="left" onmouseover="this.className='cobhl'" onmouseout="this.className='cobll'" style="white-space:nowrap"><a class="ectlink wishlistmenu" href="#" onclick="document.location='cart.php'"><?php print $xxSwCart?></a></td></tr>
<tr id="savelistcreaterow"><td class="cobll" align="left" onmouseover="this.className='cobhl'" onmouseout="this.className='cobll'" style="white-space:nowrap"><a class="ectlink wishlistmenu" href="#" onclick="document.location='<?php print $customeraccounturl.'#list'?>'"><?php print $xxCreaGR?></a></td></tr>
</table></div>
<?php
	}
	if(@$_GET['lid']!='' && is_numeric(@$_GET['lid'])) $listid=@$_GET['lid']; else $listid='';
	if(@$_SESSION['clientID']!='' && $checkoutmode=='savedcart' && $listid!=''){
		$sSQL = "SELECT listID,listName FROM customerlists WHERE listID='".escape_string($listid)."' AND listOwner='".escape_string($_SESSION['clientID'])."'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if(! ($rs = mysql_fetch_assoc($result))) $querystr = 'cartCompleted=0 AND '.getsessionsql(); else{ $listname=$rs['listName']; $querystr = 'cartCompleted>=0 AND cartListID='.$listid; }
		mysql_free_result($result);
	}elseif($checkoutmode=='savedcart'){
		$querystr = 'cartCompleted=3 AND cartListID=0 AND '.getsessionsql();
	}else
		$querystr = 'cartCompleted=0 AND '.getsessionsql();
	if(@$_GET['pli']!='' && is_numeric(@$_GET['pli']) && @$_GET['pla']!=''){
		$sSQL = "SELECT listID,listName FROM customerlists WHERE listID='".escape_string(@$_GET['pli'])."' AND listAccess='".escape_string(@$_GET['pla'])."'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)){
			$checkoutmode='savedcart';
			$listid=$rs['listID'];
			$listname=$rs['listName'];
			$querystr = 'cartCompleted=3 AND cartListID='.$listid;
		}else{
			// Error case
		}
		mysql_free_result($result);
	}
	$sSQL = "SELECT cartID,cartProdID,cartProdName,cartProdPrice,cartQuantity,pWeight,pShipping,pShipping2,pExemptions,pSection,pDims,pTax,pStaticPage,pDisplay,'' AS pImage,'' AS pLargeImage,cartCompleted,".getlangid('pDescription',2).','.getlangid('pLongDescription',4).' FROM cart LEFT JOIN products ON cart.cartProdID=products.pID LEFT OUTER JOIN sections ON products.pSection=sections.sectionID WHERE ' . $querystr . ' ORDER BY cartID';
	$result = mysql_query($sSQL) or print(mysql_error());
?><script language="javascript" type="text/javascript">/* <![CDATA[ */
var checkedfullname=false;
function checknewaccount(){
var frm=document.forms.checkoutform;
if(frm.name.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxName?>\".");
	frm.name.focus();
	return(false);
}
var regex=/ /;
if(!checkedfullname && !regex.test(frm.name.value)){
	alert("<?php print $xxFulNam?> \"<?php print $xxName?>\".");
	frm.name.focus();
	checkedfullname=true;
	return(false);
}
var regex = /[^@]+@[^@]+\.[a-z]{2,}$/i;
if(!regex.test(frm.email.value)){
	alert("<?php print $xxValEm?>");
	frm.email.focus();
	return(false);
}
if(frm.pass.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxPwd?>\".");
	frm.pass.focus();
	return(false);
}
var regex=/^[0-9A-Za-z\_\@\.\-]+$/;
if (!regex.test(frm.pass.value)){
    alert("<?php print $xxAlphaNu?> \"<?php print $xxPwd?>\".");
    frm.pass.focus();
    return(false);
}
frm.mode.value='donewaccount';
frm.action='<?php if(@$forceloginonhttps) print $pathtossl?>cart.php';
return true;
}
function checkchecked(){
	ischecked=false
	var inputs = document.getElementsByTagName('input');
	for(var i = 0; i < inputs.length; i++)
		if(inputs[i].type == 'checkbox'){
			if(inputs[i].checked&&inputs[i].name.substr(0,5)=='delet') ischecked = true;
		}
	if(! ischecked) alert("<?php print $xxNotSel?>");
	return(ischecked);
}
<?php	$theqs='';
	foreach(@$_GET as $key => $val){
		$theqs.=urlencode(strip_tags($key)) . '=' . urlencode(strip_tags($val)) . '&';
	}
	if($theqs!='') $theqs='?' . substr($theqs, 0, -1);
?>
function doupdate(){
	document.forms.checkoutform.mode.value='update';
	document.forms.checkoutform.action='cart.php<?php print $theqs ?>';
	document.forms.checkoutform.onsubmit='';
	document.forms.checkoutform.submit();
	return false;
}
var savemenuaction='saveitem';
function dosaveitem(lid){
	if(savemenuaction=='saveitem'){
		if(!checkchecked()) return;
		document.forms.checkoutform.mode.value='savecart';
		document.forms.checkoutform.listid.value=lid;
		document.forms.checkoutform.action='cart.php<?php print $theqs ?>';
		document.forms.checkoutform.onsubmit='';
		document.forms.checkoutform.submit();
	}else{
		document.location='cart.php?mode=sc&lid='+lid;
	}
}
function movetocart(){
	if(!checkchecked()) return(false);
	document.forms.checkoutform.mode.value='movetocart';
	document.forms.checkoutform.action='cart.php<?php print $theqs ?>';
	document.forms.checkoutform.onsubmit='';
	document.forms.checkoutform.submit();
	return(false);
}
var cartoversldiv;
function cartdispsavelist(isleft,wantextras,evt,twin){
	cartoversldiv=false
	var theevnt=(!evt)?twin.event:evt;//IE:FF
	if(wantextras){
		document.getElementById('savelistcartrow').style.display='';
		document.getElementById('savelistcreaterow').style.display='';
	}else{
		document.getElementById('savelistcartrow').style.display='none';
		document.getElementById('savelistcreaterow').style.display='none';
	}
	var sld = document.getElementById('savecartlist');
	var scrolltop=(document.documentElement.scrollTop?document.documentElement.scrollTop:document.body.scrollTop);
	var scrollleft=(document.documentElement.scrollLeft?document.documentElement.scrollLeft:document.body.scrollLeft);
	sld.style.left = ((theevnt.clientX+scrollleft)-(isleft?0:sld.offsetWidth))+'px';
    sld.style.top = (theevnt.clientY+scrolltop)+'px';
	sld.style.visibility = "visible";
	setTimeout('cartchecksldiv()',2000);
	return(false);
}
function cartchecksldiv(){
	var sld = document.getElementById('savecartlist');
	if(! cartoversldiv)
		sld.style.visibility = 'hidden';
}
function selaltrate(id){
	document.forms.checkoutform.altrates.value=id;
	doupdate();
}
<?php
	if($adminAltRates==2 && @$_SESSION['xsshipping']!='') $adminAltRates=0;
	if($adminAltRates==2){
		$sSQL = 'SELECT altrateid,'.getlangid('altratetext',65536).' FROM alternaterates WHERE usealtmethod'.$international.'<>0 ORDER BY altrateorder,altrateid';
		$result2 = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result2) > 0){
			print "var shipservicetext=[];\r\n";
			print 'var extraship=[';
			$addcomma='';
			$servicetext='';
			while($rs = mysql_fetch_assoc($result2)){
				$servicetext .= 'shipservicetext[' . $rs['altrateid'] . ']="' . $rs[getlangid('altratetext',65536)] . '";' . "\r\n";
				if($rs['altrateid']!=$shipType){
					print $addcomma . $rs['altrateid'];
					$addcomma=',';
				}
			}
			print "];\r\n";
			print $servicetext . "\r\n"; ?>
function addCommas(ns,decs,thos){
if((dpos=ns.indexOf(decs))<0)dpos=ns.length;
dpos-=3;
while(dpos>0){
	ns=ns.substr(0,dpos)+thos+ns.substr(dpos);
	dpos-=3;
}
return(ns);
}
function formatestprice(i){
<?php
	$tempStr = FormatEuroCurrency(0);
	print "var pTemplate='".$tempStr."';\r\n";
	if(strstr($tempStr,',') || strstr($tempStr,'.')){ ?>
if(i==Math.round(i))i=i.toString()+".00";
else if(i*10.0==Math.round(i*10.0))i=i.toString()+"0";
else if(i*100.0==Math.round(i*100.0))i=i.toString();
<?php
	}
	print 'i=addCommas(i.toString()'.(strstr($tempStr,',')?".replace(/\\./,','),',','.'":",'.',','").');';
	print 'pTemplate = pTemplate.toString().replace(/\d[,.]*\d*/,i.toString());';
	print 'return(pTemplate);';
?>}
function acajaxcallback(){
	if(ajaxobj.readyState==4){
		var restxt=ajaxobj.responseText;
		var gssr=restxt.split('SHIPSELPARAM=');
		if(gssr[2]!='ERROR'&&parseFloat(gssr[1])<bestestimate){
			if(document.getElementById('estimatorcell')){
				document.getElementById('estimatorcell').colSpan='1';
				document.getElementById('estimatorcell').align='right';
				var newcell=document.getElementById('estimatorrow').insertCell(-1);
				newcell.className='cobll';
				newcell.innerHTML = "&nbsp;";
				document.getElementById('estimatorcell').id='';
			}
			bestestimate=parseFloat(gssr[1]);
			bestcarrier=parseInt(gssr[4]);
			document.getElementById('estimatorspan').innerHTML=formatestprice(bestestimate);
<?php	if(@$showtaxinclusive) print "var countrytax=parseFloat(gssr[3]);document.getElementById('countrytaxspan').innerHTML=formatestprice(countrytax);\r\n"; else print "var countrytax=0;\r\n"; ?>
			document.getElementById('grandtotalspan').innerHTML=(formatestprice(Math.round((vstotalgoods+bestestimate+countrytax)*100)/100.0));
		}
		getalternatecarriers();
	}
}
<?php	if(@$xxBesRaU=='') $xxBesRaU="Best Rate Using" ?>
function getalternatecarriers(){
	if(extraship.length>0){
		var shiptype=extraship.shift();
		document.getElementById('checkaltspan').innerHTML='Checking ' + shipservicetext[shiptype] + ":";
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange = acajaxcallback;
		ajaxobj.open("GET", "vsadmin/shipservice.php?ratetype=estimator&best="+bestestimate+"&shiptype="+shiptype+"&sessionid=<?php print urlencode($thesessionid)?>&destzip=<?php print urlencode($destZip)?>&sc=<?php print urlencode($shipcountry)?>&scc=<?php print urlencode($shipCountryCode)?>&sta=<?php print urlencode($shipStateAbbrev)?>", true);
		ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxobj.send(null);
	}else{
		document.getElementById('checkaltspan').innerHTML="<?php print $xxBesRaU?> " + shipservicetext[bestcarrier] + ":";
		document.forms.checkoutform.altrates.value=bestcarrier;
	}
}
<?php	}
		mysql_free_result($result2);
	} ?>
/* ]]> */</script>
	<form method="post" name="checkoutform" action="<?php print $cartpath?>"<?php if(mysql_num_rows($result) > 0) print ' onsubmit="return changechecker(this)"'?>>
	<input type="hidden" name="mode" value="checkout" />
	<input type="hidden" name="sessionid" value="<?php print getsessionid();?>" />
	<input type="hidden" name="PARTNER" value="<?php print htmlspecials(strip_tags(trim(@$_COOKIE['PARTNER']))) ?>" />
	<input type="hidden" name="cart" value="" />
	<input type="hidden" name="listid" value="" />
<?php
	if(@$_SESSION['noredeempoints']==TRUE) writehiddenidvar("noredeempoints", "1");
	if($adminAltRates!=0) print whv('altrates',@$_SESSION['altrates']); ?>
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php
	function customerlistselector($currlist){
		global $enablewishlists,$listname,$checkoutmode,$xxVGifRe,$xxMyWisL,$imgsaveitems;
		if(@$enablewishlists==TRUE && @$_GET['mode']!='acct'){
			if($listname==''){
				if(@$checkoutmode=='savedcart') $clslistname = $xxMyWisL; else $clslistname = $xxVGifRe;
			}else
				$clslistname = $listname;
			print ' - <span class="cartwishlists">' . imageorlink(@$imgsaveitems, htmlspecials($clslistname), 'savemenuaction=\'switchlist\';return cartdispsavelist(true,true,event,window)', TRUE) . '</span>';
		}
	}
	function writeestimatormenu($esttxt){
		global $shippingoptionsasradios,$xxOrCom,$xxShEsWi,$adminAltRates,$international,$xxChkAlt,$freeshipamnt,$shipping,$handling,$xxShipHa;
		if($adminAltRates==2){
			print '<span id="checkaltspan" style="font-weight:bold">' . ($freeshipamnt==($shipping+$handling) ? $xxShipHa : $xxChkAlt) . '</span>';
		}elseif($adminAltRates==1){
			$sSQL = 'SELECT altrateid,altratename,'.getlangid('altratetext',65536).',usealtmethod,usealtmethodintl FROM alternaterates WHERE usealtmethod'.$international.'<>0 ORDER BY altrateorder,altrateid';
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0){
				if(@$shippingoptionsasradios!=TRUE) print '<select id="altratesselect" size="1" onchange="selaltrate(this[this.selectedIndex].value)">';
				while($rs = mysql_fetch_assoc($result)){
					writealtshipline($rs[getlangid('altratetext',65536)],$rs['altrateid'],$xxOrCom.' ',$xxShEsWi.' ',FALSE);
				}
				if(@$shippingoptionsasradios!=TRUE) print '</select>';
			}else
				print '<strong>'.$esttxt.':</strong>';
			mysql_free_result($result);
		}else
			print '<strong>'.$esttxt.':</strong>';
	}
	function displaycartactions(){
		global $checkoutmode,$xxRemLis,$xxDelete,$imgdelete,$imgsaveitems,$xxMovCar,$xxAddLis,$enablewishlists;
		if(@$_GET['pla']=='') print '<p style="margin: 3px">'.imageorlink(@$imgdelete, ($checkoutmode=='savedcart'?$xxRemLis:$xxDelete), 'return doupdate()', TRUE).'</p>';
		if($checkoutmode=='savedcart')
			print '<p style="margin: 3px">'.imageorlink(@$imgsaveitems, $xxMovCar, 'return movetocart()', TRUE).'</p>';
		if(@$_SESSION['clientID']!='' && @$enablewishlists==TRUE)
			print '<p style="margin: 3px">'.imageorlink(@$imgsaveitems, $xxAddLis, 'savemenuaction=\'saveitem\';return cartdispsavelist(false,false,event,window)', TRUE).'</p>';
	}
	if(@$enableclientlogin==TRUE || @$forceclientlogin==TRUE){
		if((@$_GET['mode']=='newaccount' && @$allowclientregistration==TRUE) || ($checkoutmode=='donewaccount' && @$loginerror!='')){
			if(@$forceloginonhttps && (@$_SERVER['HTTPS']!='on' && @$_SERVER['SERVER_PORT']!='443') && strpos(@$pathtossl,'https')!==FALSE){ header('Location: '.$pathtossl.basename($_SERVER['PHP_SELF']).(@$_SERVER['QUERY_STRING']!='' ? '?'.strip_tags(@$_SERVER['QUERY_STRING']) : '')); exit; }
?>
			  <tr> 
			    <td class="cobhl" align="center" height="26" colspan="6"><strong><?php print (@$loginerror!='' ? '<span style="color:#FF0000">' . $loginerror . '</span>' : $xxNewAcc)?></strong></td>
			  </tr>
			  <tr>
				<td class="cobhl" align="right" height="26"><strong><?php print $redstar . $xxName?>: </strong></td>
				<td class="cobll" align="left"><input type="text" name="name" size="31" value="<?php print htmlspecials(unstripslashes(@$_POST['name']))?>" alt="<?php print $xxName?>" /></td>
<?php		if(@$nomailinglist==TRUE){ ?>
				<td class="cobhl" colspan="4">&nbsp;</td>
<?php		}else{ ?>
			    <td class="cobhl" align="right" height="26"><input type="checkbox" name="allowemail" value="ON"<?php if(@$allowemaildefaulton==TRUE || @$_POST['allowemail']=='ON') print ' checked="checked"'?> /></td>
				<td class="cobll" align="left" colspan="3"><strong><?php print $xxAlPrEm?></strong><br /><span style="font-size:10px"><?php print $xxNevDiv?></span></td>
<?php		} ?>
			  </tr>
			  <tr>
				<td class="cobhl" align="right" height="26"><strong><?php print $redstar . $xxEmail?>: </strong></td>
				<td class="cobll" align="left"><input type="text" name="email" size="31" value="<?php print htmlspecials(@$_POST['email'])?>" alt="<?php print $xxEmail?>" /></td>
			    <td class="cobhl" align="right"><strong><?php print $redstar . $xxPwd?>: </strong></td>
				<td class="cobll" align="left" colspan="3"><input type="password" name="pass" size="20" value="<?php print htmlspecials(@$_POST['pass'])?>" alt="<?php print $xxPwd?>" autocomplete="off" /></td>
			  </tr>
			  <tr>
				<td class="cobll" align="center" height="26" colspan="6"><?php print imageorsubmit(@$imgcreateaccount,$xxCrNwAc.'" onclick="return checknewaccount();','')?></td>
			  </tr>
<?php	}elseif(@$_GET['mode']!='login' && @$loginerror==''){
			if(@$_SESSION['clientID']!=''){ ?>
			  <tr>
				<td class="cobll"  height="30" colspan="6"><?php
				print '<p class="cartloggedin">' . $xxLogInA . ' <span class="cartloginname">' . htmlspecials($_SESSION['clientUser']) . '</span>';
				customerlistselector($listid);
				print ' - <a class="ectlink" href="cart.php?mode=logout">' . $xxLogout . ' </a>';
				if(@$_GET['warncheckspamfolder']=='true') print '<br /><br /><table width="80%"><tr><td><strong>' . $xxThkSub . '</strong></td></tr><tr><td><span style="color:#FF0000;font-size:10px">' . $xxSpmWrn . '</span></td></tr></table>';
				if(@$_GET['cartchanged']=='true') print '<br /><br /><table width="80%"><tr><td align="center"><strong><span style="color:#FF0000">'.$xxCarCha.'</span></strong></td></tr></table>';
				print '</p>' ?></td>
			  </tr>
<?php		}elseif(@$noclientloginprompt!=TRUE && @$_GET['pli']==''){ ?>
			  <tr>
				<td class="cobll" height="30" colspan="6" align="center"><?php print $xxNotLI . ' ' . $xxTLIP?> <a class="ectlink" href="<?php if(@$forceloginonhttps) print $pathtossl?>cart.php?mode=login"><strong><?php print $xxClkHere?></strong></a><?php if(@$allowclientregistration==TRUE) print ' ' . $xxOr . ' <a class="ectlink" href="cart.php?mode=newaccount"><strong>' . $xxClkHere . '</strong></a> ' . $xxToCrAc?>.</td>
			  </tr>
<?php		}
		}else{
			if(@$forceloginonhttps && (@$_SERVER['HTTPS']!='on' && @$_SERVER['SERVER_PORT']!='443') && strpos(@$pathtossl,'https')!==FALSE){ header('Location: '.$pathtossl.basename($_SERVER['PHP_SELF']).(@$_SERVER['QUERY_STRING']!='' ? '?'.strip_tags(@$_SERVER['QUERY_STRING']) : '')); exit; }
?>
			  <tr>
			    <td class="cobhl" align="center" height="26" colspan="6"><strong><?php print (@$loginerror!='' ? '<span style="color:#FF0000">' . $loginerror . '</span>' : $xxLiDets)?></strong></td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="right" height="26"><strong><?php print $xxEmail?>: </strong></td>
				<td class="cobll" align="left" colspan="5"><input type="text" name="email" size="31" value="<?php print htmlspecials(@$_POST['email'])?>" alt="<?php print $xxEmail?>" /> 
				<input type="checkbox" name="cook" value="ON" /> <?php print $xxRemLog?></td>
			  </tr>
			  <tr>
			    <td class="cobhl" align="right" height="26"><strong><?php print $xxPwd?>: </strong></td>
				<td class="cobll" align="left" colspan="5"><?php
					print whv('refurl',strip_tags(@$_REQUEST['refurl'])); ?><input type="password" name="pass" size="20" value="<?php print htmlspecials(@$_POST['pass'])?>" autocomplete="off" /> <?php
					print imageorsubmit(@$imgcartaccountlogin,$xxSubmt."\" onclick=\"document.forms.checkoutform.action='".(@$forceloginonhttps?$pathtossl:'')."cart.php';document.forms.checkoutform.mode.value='dologin';",'');
					if(@$allowclientregistration==TRUE) print '&nbsp;&nbsp;' . imageorbutton(@$imgnewaccount,$xxNewAcc,'','cart.php?mode=newaccount',FALSE);
					print '&nbsp;&nbsp;' . imageorbutton(@$imgforgotpassword,$xxForPas,'',$customeraccounturl.'?mode=lostpassword',FALSE)?></td>
			  </tr>
<?php	}
	}
	if(@$_GET['pli']!='' && is_numeric(@$_GET['pli']) && @$_GET['pla']!=''){ ?>
			  <tr>
				<td class="cobll" height="30" colspan="6" align="left">
				<p class="wishlist viewlist">&nbsp;<?php print $xxArVwLi?>: <span class="listname"><?php print htmlspecials($listname)?></span></p></td>
			  </tr>
<?php
	}
	if($loginsuccess){ ?>
			  <tr>
			    <td class="cobll" colspan="6" align="center">
				  <p>&nbsp;</p><p><?php print $xxLISuc?></p><p>&nbsp;</p><p><a class="ectlink" href="cart.php"><strong><?php print $xxPlWtFw?></strong></a></p><p>&nbsp;</p>
				</td>
			  </tr>
<?php
	}elseif(@$_GET['mode']=='acct'){ ?>
			  <tr>
			    <td class="cobll" colspan="6" align="left">
					<table border="0" cellspacing="1" cellpadding="3" width="100%">
					  <tr><td>&nbsp;</td><td><p><strong><?php print $xxWhDoTo?></strong></p></td></tr>
					  <tr><td align="right"><img border="0" src="images/ecttick.gif" alt="" /></td><td><a href="<?php print $customeraccounturl.'#acct'?>"><?php print $xxManAcc?></a></td></tr>
					  <tr><td align="right"><img border="0" src="images/ecttick.gif" alt="" /></td><td><a href="<?php print $customeraccounturl.'#ord'?>"><?php print $xxVwPsOr?></a></td></tr>
<?php	if(@$enablewishlists==TRUE){ ?>
					  <tr><td align="right"><img border="0" src="images/ecttick.gif" alt="" /></td><td><a href="<?php print $customeraccounturl.'#list'?>"><?php print $xxCrGfRe?></a></td></tr>
					  <tr><td align="right"><img border="0" src="images/ecttick.gif" alt="" /></td><td><a href="#" onclick="savemenuaction='switchlist';return cartdispsavelist(false,false,event,window)"><?php print $xxVwGfRe?></a></td></tr>
<?php	} ?>
					  <tr><td align="right"><img border="0" src="images/ecttick.gif" alt="" /></td><td><a href="cart.php"><?php print $xxVwMyCa?></a></td></tr>
					  <tr><td align="right"><img border="0" src="images/ecttick.gif" alt="" /></td><td><a href="<?php print $customeraccounturl.'#ord'?>"><?php print $xxVwOrSt?></a></td></tr>
					  <tr><td align="right"><img border="0" src="images/ecttick.gif" alt="" /></td><td><a href="<?php print $customeraccounturl.'#add'?>"><?php print $xxManAdd?></a></td></tr>
					  <tr><td align="right"><img border="0" src="images/ecttick.gif" alt="" /></td><td><a href="<?php print $xxHomeURL?>"><?php print $xxGoShop?></a></td></tr>
					  <tr><td colspan="2">&nbsp;</td></tr>
					</table>
				</td>
			  </tr><?php
	}elseif(($itemsincart = mysql_num_rows($result)) > 0){
		if(! $isInStock){ ?>
			  <tr>
			    <td class="cobll" colspan="6" align="center" height="30"><?php
			print '<p><strong><span style="color:#FF0000">' . $xxNoStok.'</span><br/>'.$xxStkUTo.'<a class="ectlink" href="cart.php">' . $xxClkHere . '</a></strong></p>';
			if(@$_GET['mode']!='add' && $checkoutmode!='update') print '<p style="font-size:10px">('.$xxJusBuy.')</p>';
			 ?></td>
			  </tr>
<?php	} ?>
			  <tr style="height:30px;font-weight:bold;">
			    <td class="cobhl" width="15%" align="left"><?php print $xxCODets?></td>
			    <td class="cobhl" width="33%" align="left"><?php print $xxCOName?></td>
				<td class="cobhl" width="14%" align="center"><?php print $xxCOUPri?></td>
				<td class="cobhl" width="14%" align="center"><?php print $xxQuant?></td>
				<td class="cobhl" width="14%" align="center"><?php print $xxTotal?></td>
				<td class="cobhl" align="center"><?php print $xxCOSel?></td>
			  </tr>
<?php	$totaldiscounts = 0;
		$changechecker = '';
		$googlelineitems = '';
		$index = 0;
		while($alldata=mysql_fetch_assoc($result)){
			$index++;
			$sSQL = "SELECT imageSrc FROM productimages WHERE imageType=0 AND imageProduct='" . escape_string($alldata['cartProdID']) . "' ORDER BY imageNumber LIMIT 0,1";
			$result2 = mysql_query($sSQL) or print(mysql_error());
			if($rs2 = mysql_fetch_assoc($result2)) $alldata['pImage']=$rs2['imageSrc'];
			mysql_free_result($result2);
			if(is_null($alldata['pWeight'])) $alldata['pWeight']=0;
			if(($alldata['cartProdID']==$giftcertificateid || $alldata['cartProdID']==$donationid) && is_null($alldata['pExemptions'])) $alldata['pExemptions']=15;
			$changechecker .= 'if(document.checkoutform.quant' . $alldata['cartID'] . '.value!=' . $alldata['cartQuantity'] . ") dowarning=true;\n";
			$theoptions = '';
			$theoptionspricediff = 0;
			$isoutofstock = FALSE;
			$sSQL = "SELECT coID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff,optAltImage,optType FROM cartoptions LEFT JOIN options ON cartoptions.coOptID=options.optID LEFT JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE coCartID=" . $alldata['cartID'] . ' ORDER BY coID';
			$opts = mysql_query($sSQL) or print(mysql_error());
			$optPriceDiff=0;
			while($rs=mysql_fetch_assoc($opts)){
				$optoutofstock = FALSE;
				foreach($outofstockarr as $outofstockitem){
					if($outofstockitem[0]==$rs['coID'] && $outofstockitem[1]==TRUE){ $optoutofstock=TRUE; $isoutofstock=TRUE; }
				}
				$theoptionspricediff += $rs['coPriceDiff'];
				$alldata['pWeight'] += (double)$rs['coWeightDiff'];
				if(trim($rs['optAltImage'])!='' && @$useimageincart && $rs['optType']!=4){
					if(strpos(trim($alldata['pImage']), '%s')!==FALSE) $alldata['pImage'] = str_replace('%s', $rs['optAltImage'], trim($alldata['pImage'])); else $alldata['pImage'] = $rs['optAltImage'];
				}
				$theoptions .= '<tr><td class="cobhl" align="' . $tright . '" height="25"><span style="font-size:10px"><strong>' . $rs['coOptGroup'] . ':</strong></span></td>' .
					'<td class="cobll" align="' . $tleft . '"><span style="font-size:10px">&nbsp;- ' . str_replace(array("\r\n","\n"),array("<br />","<br />"),strip_tags($rs['coCartOption'])) . '</span></td>' .
					'<td class="cobll" align="' . $tright . '"><span style="font-size:10px">' . ($rs['coPriceDiff']==0 || @$hideoptpricediffs==TRUE ? '- ' : FormatEuroCurrency($rs['coPriceDiff'])) . '</span></td>' .
					'<td class="cobll" align="center"><span style="font-size:10px;font-weight:bold;color:#FF0000">'.($optoutofstock ? $xxLimSto : '&nbsp;').'</span></td>' .
					'<td class="cobll" align="' . $tright . '"><span style="font-size:10px">' . ($rs['coPriceDiff']==0 || @$hideoptpricediffs==TRUE ? '- ' : FormatEuroCurrency($rs['coPriceDiff']*$alldata['cartQuantity'])) . '</span></td>' .
					'<td class="cobll">&nbsp;</td></tr>' . "\r\n";
				$totalgoods += ($rs['coPriceDiff']*(int)$alldata['cartQuantity']);
				if(($alldata['pExemptions'] & 8)!=8) $handlingeligablegoods += $rs['coPriceDiff']*(int)$alldata['cartQuantity'];
			}
			$googlelineitems .= '<item><merchant-private-item-data><product-id>' . xmlencodecharref($alldata['cartProdID']) . '</product-id></merchant-private-item-data><item-name>' . xmlencodecharref(strip_tags($alldata['cartProdName'])) . '</item-name><item-description>' . xmlencodecharref(substr(strip_tags($alldata[getlangid('pDescription',2)]),0,301)) . '</item-description><unit-price currency="' . $countryCurrency . '">' . number_format($alldata['cartProdPrice'] + $theoptionspricediff,2,'.','') . '</unit-price><quantity>' . $alldata['cartQuantity'] . '</quantity></item>';
			mysql_free_result($opts);
			foreach($outofstockarr as $outofstockitem){
				if($outofstockitem[0]==$alldata['cartID'] && $outofstockitem[1]==FALSE) $isoutofstock=TRUE;
			}
			$opts = mysql_query("SELECT imageSrc FROM productimages WHERE imageType=0 AND imageProduct='" . escape_string($alldata['cartProdID']) . "' ORDER BY imageNumber LIMIT 0,1") or print(mysql_error());
			if($rs = mysql_fetch_assoc($opts)) $alldata['pImage']=$rs['imageSrc'];
			mysql_free_result($opts);
			$opts = mysql_query("SELECT imageSrc FROM productimages WHERE imageType=1 AND imageProduct='" . escape_string($alldata['cartProdID']) . "' ORDER BY imageNumber LIMIT 0,1") or print(mysql_error());
			if($rs = mysql_fetch_assoc($opts)) $alldata['pLargeImage']=$rs['imageSrc'];
			mysql_free_result($opts);
			if($alldata['pDisplay']!=0 && @$linkcartproducts==TRUE && (@$forcedetailslink==TRUE || trim($alldata[getlangid('pLongDescription',4)])!='' || trim($alldata['pLargeImage'])!='')){
				if($alldata['pStaticPage']!=0){
					$startlink='<a class="ectlink" href="' . cleanforurl($alldata['cartProdName']) . '.php' . '">';
					$endlink='</a>';
				}elseif(@$detailslink!=''){
					$sSQL = "SELECT imageSrc FROM productimages WHERE imageType=1 AND imageProduct='" . escape_string($alldata['cartProdID']) . "' ORDER BY imageNumber LIMIT 0,1";
					$result2 = mysql_query($sSQL) or print(mysql_error());
					if($rs2 = mysql_fetch_assoc($result2)) $alldata['pLargeImage']=$rs2['imageSrc'];
					mysql_free_result($result2);
					$startlink=str_replace('%pid%', $alldata['cartProdID'], str_replace('%largeimage%', $alldata['pLargeImage'], $detailslink));
					$endlink=@$detailsendlink;
				}else{
					$startlink='<a class="ectlink" href="proddetail.php?prod=' . urlencode($alldata['cartProdID']) . '">';
					$endlink='</a>';
				}
			}else{
				$startlink='';
				$endlink='';
			} ?>
			  <tr>
			    <td class="cobhl" align="<?php print $tleft?>" height="30"><?php if(@$useimageincart && ! (trim($alldata['pImage'])=='' || trim($alldata['pImage'])=='prodimages/')) print '<p align="center">' . $startlink . '<img class="prodimage cartimage" src="' . $alldata['pImage'] . '" border="0" alt="' . strip_tags($alldata['cartProdName']) . '" />' . $endlink . '</p>'; else print '<strong>' . $startlink . $alldata['cartProdID'] . $endlink . '</strong>' ?></td>
			    <td class="cobll" align="<?php print $tleft?>"><?php print $startlink . $alldata['cartProdName'] . $endlink ?></td>
				<td class="cobll" align="<?php print $tright?>"><?php print (@$hideoptpricediffs==TRUE ? FormatEuroCurrency($alldata['cartProdPrice'] + $theoptionspricediff) : FormatEuroCurrency($alldata['cartProdPrice']))?></td>
				<td class="cobll" align="center"><?php if(@$_GET['pla']!='') print $alldata['cartQuantity']; else print '<input type="text" name="quant' . $alldata['cartID'] . '" value="' . $alldata['cartQuantity'] . '" size="2" maxlength="5" ' . ($isoutofstock ? 'style="background-color:#FF7070;"' : '') . ' alt="' . $xxQuant . '" />'?></td>
				<td class="cobll" align="<?php print $tright?>"><?php print (@$hideoptpricediffs==TRUE ? FormatEuroCurrency(($alldata['cartProdPrice'] + $theoptionspricediff)*$alldata['cartQuantity']) : FormatEuroCurrency($alldata['cartProdPrice']*$alldata['cartQuantity']))?></td>
				<td class="cobll" align="center"><?php
			if($checkoutmode!='savedcart'){
			}elseif($alldata['cartCompleted']==0 || $alldata['cartCompleted']==2)
				print $xxPurcha . '<br />';
			elseif($alldata['cartCompleted']==1)
				print $xxPurchd . '<br />';
				?><input type="checkbox" id="delet<?php print $index?>" name="delet<?php print $alldata['cartID']?>" /></td>
			  </tr>
<?php		print $theoptions;
			$runTot=$alldata['cartProdPrice'] * (int)$alldata['cartQuantity'];
			$totalquantity += (int)$alldata['cartQuantity'];
			$totalgoods += ($alldata['cartProdPrice']*(int)$alldata['cartQuantity']);
			$alldata['cartProdPrice'] += $theoptionspricediff;
			if(trim(@$_SESSION['clientID'])!='') $alldata['pExemptions'] = ((int)$alldata['pExemptions'] | ((int)$_SESSION['clientActions'] & 7));
			if(($shipType==2 || $shipType==3 || $shipType==4 || $shipType==6 || $shipType==7 || $shipType==8) && (double)$alldata['pWeight']<=0.0)
				$alldata['pExemptions'] = ($alldata['pExemptions'] | 4);
			if(@$perproducttaxrate==TRUE){
				if(is_null($alldata['pTax'])) $alldata['pTax'] = $countryTaxRate;
				if(($alldata['pExemptions'] & 2)!=2) $countryTax += (($alldata['pTax'] * $alldata['cartProdPrice'] * (int)$alldata['cartQuantity']) / 100.0);
			}else{
				if(($alldata['pExemptions'] & 2)==2) $countrytaxfree += $runTot + ($theoptionspricediff * (int)($alldata['cartQuantity']));
			}
			if(($alldata['pExemptions'] & 4)==4) $shipfreegoods += $runTot; else $somethingToShip=TRUE;
			if(($alldata['pExemptions'] & 8)!=8){ $handlingeligableitem=TRUE; $handlingeligablegoods += $runTot; }
			if(@$estimateshipping==TRUE && @$_SESSION['xsshipping']=='')
				addproducttoshipping($alldata, $index);
		}
		if(@$showtaxinclusive==''){
			$stateTaxRate=0;
			$countryTaxRate=0;
		}
		if($checkoutmode=='savedcart'){
			$estimateshipping=FALSE;
			$addextrarows=0;
			$showtaxinclusive=FALSE;
		}else{
			calculatediscounts($totalgoods,FALSE,'');
			if($totaldiscounts > $totalgoods) $totaldiscounts = $totalgoods;
			if($totaldiscounts==0)
				$_SESSION['discounts'] = '';
			else{
				$_SESSION['discounts'] = $totaldiscounts;
				$addextrarows++;
				$glicpnmessage = substr($cpnmessage, 6, -6);
				$googlelineitems .= '<item><merchant-private-item-data><discountflag>true</discountflag></merchant-private-item-data>' . addtag('item-name',xmlencodecharref(strip_tags($xxAppDs))) . '<item-description>' . xmlencodecharref(strip_tags(str_replace('<br />', ' - ', $glicpnmessage))) . '</item-description><unit-price currency="' . $countryCurrency . '">-' . number_format($totaldiscounts,2,'.','') . '</unit-price><quantity>1</quantity></item>';
			}
		}
		if(@$estimateshipping==TRUE){
			if(@$_SESSION['xsshipping']=='' && $success){
				if(calculateshipping()){
					insuranceandtaxaddedtoshipping();
					calculateshippingdiscounts(FALSE);
					calculatetaxandhandling();
					$_SESSION['xsshipping']=($shipping+$handling)-$freeshipamnt;
				}else{
					calculatetaxandhandling();
					$handling = 0;
				}
			}elseif(@$_SESSION['xsshipping']=='' && $success){
				calculatetaxandhandling();
				$handling = 0;
			}else{
				$shipping = @$_SESSION['xsshipping'];
				$countryTax = @$_SESSION['xscountrytax'];
				$handling = 0;
			}
		}else
			calculatetaxandhandling();
		if($addextrarows > 0){ ?>
			  <tr>
				<td class="cobhl" rowspan="<?php print $addextrarows+4;?>">&nbsp;</td>
				<td class="cobll" align="<?php print $tright?>" colspan="3" height="30"><strong><?php print $xxSubTot?>:</strong></td>
				<td class="cobll" align="<?php print $tright?>"><?php print FormatEuroCurrency($totalgoods)?></td>
				<td class="cobll" align="center" style="white-space:nowrap"><?php displaycartactions()?></td>
			  </tr>
<?php	}
		if($totaldiscounts>0){ ?>
			  <tr>
				<td class="cobll" align="<?php print $tright?>" colspan="3" height="30"><span style="color:#FF0000;font-weight:bold"><?php print $xxDsApp?></span></td>
				<td class="cobll" align="<?php print $tright?>"><span style="color:#FF0000"><?php print FormatEuroCurrency($totaldiscounts)?></span></td>
				<td class="cobll" align="center">&nbsp;</td>
			  </tr>
<?php	}
		if($checkoutmode!='savedcart' && round($loyaltypointsavailable*$loyaltypointvalue,2)>0){
			if(@$_SESSION['noredeempoints']!=TRUE){
				$loyaltypointdiscount = $loyaltypointsavailable*$loyaltypointvalue;
				if($loyaltypointdiscount>$totalgoods-$totaldiscounts) $loyaltypointdiscount=$totalgoods-$totaldiscounts;
			}
?>
			  <tr>
				<td class="cobll" align="<?php print $tright?>" colspan="3" height="30"><span style="color:#FF0000"><select size="1" onchange="document.location='cart.php?redeempoints='+(this.selectedIndex==1?'no':'yes')">
				<option value=""><?php print $xxReLPts?></option>
				<option value=""<?php if(@$_SESSION['noredeempoints']==TRUE) print ' selected="selected"'?>><?php print $xxSaLPts?> (<?php print $loyaltypointsavailable?>)</option>
				</select>
				</td>
				<td class="cobll" align="<?php print $tright?>"><span style="color:#FF0000"><?php if(@$_SESSION['noredeempoints']==TRUE) print '-'; else print FormatEuroCurrency($loyaltypointdiscount)?></span></td>
				<td class="cobll" align="center">&nbsp;</td>
			  </tr>
<?php	}
		if(@$estimateshipping!=TRUE || @$nohandlinginestimator==TRUE){ $handling=0; $handlingchargepercent=0; }
		if(@$estimateshipping==TRUE){
			if($errormsg!=''){ ?>
			  <tr id="estimatorrow">
				<td class="cobll" align="<?php print $tright?>" colspan="3" height="30"><?php writeestimatormenu($xxShpEst)?></td>
				<td class="cobll" colspan="2" id="estimatorcell"><span id="estimatorspan"><span style="font-size:10px;color:#FF0000;font-weight:bold"><?php print $errormsg?></span></span></td>
			  </tr>
<?php		}else{ ?>
			  <tr>
				<td class="cobll" align="<?php print $tright?>" colspan="3" height="30"><?php if($handling==0) writeestimatormenu($xxShpEst); else writeestimatormenu($xxShHaEs);?></td>
				<td class="cobll" align="<?php print $tright?>"><?php if($freeshipamnt==($shipping+$handling)) print '<p align="center"><span style="color:#FF0000">' . $xxFree . '</span></p>'; else print '<span id="estimatorspan">' . FormatEuroCurrency(($shipping+$handling)-$freeshipamnt) . '</span>'?></td>
				<td class="cobll" align="center">&nbsp;</td>
			  </tr>
<?php		}
			if($wantstateselector){
				$sSQL = 'SELECT stateName,stateAbbrev FROM states WHERE stateEnabled=1 ORDER BY stateName';
				$result = mysql_query($sSQL) or print(mysql_error());
				$numallstates=0;
				$numallcountries=0;
				while($rs = mysql_fetch_assoc($result))
					$allstates[$numallstates++]=$rs;
				mysql_free_result($result);
				if(is_array($allstates)){ ?>
			  <tr>
				<td class="cobll" align="<?php print $tright?>" colspan="3" height="30"><strong><?php print $xxAllSta?>:</strong></td>
				<td class="cobll" align="<?php print $tleft?>" colspan="2"><select name="state" id="state" size="1"><?php show_states($shipstate) ?></select></td>
			  </tr>
<?php			}
			}
			if($wantcountryselector){ ?>
			  <tr>
				<td class="cobll" align="<?php print $tright?>" colspan="3" height="30"><strong><?php print $xxCountry?>:</strong></td>
				<td class="cobll" align="<?php print $tleft?>" colspan="2"><select name="country" id="country" size="1"<?php if($origCountryCode!="US" && $origCountryCode!="CA" && $wantstateselector && @$dynamicuscastates==TRUE && @$upsnegdrates==TRUE) print ' onchange="dynamiccountries(this,\'state\')"'?>><?php
				$sSQL = 'SELECT countryName,countryCode,' . getlangid('countryName',8) . ' AS cnameshow FROM countries WHERE countryEnabled=1 ORDER BY countryOrder DESC,' . getlangid('countryName',8);
				$result = mysql_query($sSQL) or print(mysql_error());
				while($rs = mysql_fetch_assoc($result)){
					print '<option value="' . $rs['countryName'] . '"';
					if($shipcountry==$rs['countryName']) print ' selected="selected"';
					print '>' . $rs['cnameshow'] . "</option>\r\n";
				}
				mysql_free_result($result); ?></select></td>
			  </tr>
<?php		}
			if($wantzipselector){ ?>
			  <tr>
				<td class="cobll" align="<?php print $tright?>" colspan="3" height="30"><strong><?php print $xxZip?>:</strong></td>
				<td class="cobll" align="<?php print $tleft?>" colspan="2"><input type="text" name="zip" size="8" value="<?php print htmlspecials($destZip)?>" alt="<?php print $xxZip?>" /></td>
			  </tr>
<?php		}
		}
		if(@$showtaxinclusive){
			$_SESSION['xscountrytax']=$countryTax;
?>			  <tr>
				<td class="cobll" align="<?php print $tright?>" colspan="3" height="30"><strong><?php print $xxCntTax?>:</strong></td>
				<td class="cobll" align="<?php print $tright?>"><span id="countrytaxspan"><?php print FormatEuroCurrency($countryTax)?></span></td>
				<td class="cobll" align="center">&nbsp;</td>
			  </tr>
<?php	} ?>	<tr>
<?php	if($addextrarows==0){ ?>
				<td class="cobhl" rowspan="<?php print ($checkoutmode=='savedcart'?2:3)?>">&nbsp;</td>
<?php	}
		if(@$_GET['pla']!=''){ ?>
				<td class="cobll" align="<?php print $tright?>" colspan="4">&nbsp;</td>
<?php	}else{ ?>
				<td class="cobll" align="<?php print $tright?>" colspan="3"><strong><?php if($checkoutmode=='savedcart') print $xxItmTot; else print $xxGndTot?>:</strong></td>
				<td class="cobll" align="<?php print $tright?>"><span id="grandtotalspan"><?php print FormatEuroCurrency(($totalgoods+$shipping+$handling+$countryTax)-($totaldiscounts+$freeshipamnt+$loyaltypointdiscount))?></span></td>
<?php	} ?>
				<td class="cobll" align="center" style="white-space:nowrap" height="30"><?php if($addextrarows==0) print displaycartactions(); else print '&nbsp;'; ?></td>
			  </tr>
<?php	if($checkoutmode!='savedcart'){ ?>
			  <tr>
				<td class="cobll" colspan="5" height="30">
				  <table width="100%" cellspacing="0" cellpadding="0" border="0">
				    <tr>
					  <td width="50%" align="center"><?php
			if($thefrompage!='' && (@$actionaftercart==2 || @$actionaftercart==3)) $thehref = htmlspecials($thefrompage); else $thehref = $xxHomeURL;
			print imageorlink(@$imgcontinueshopping, $xxCntShp, $thehref, FALSE);
					?></td>
					  <td width="50%" align="center"><?php print imageorlink(@$imgupdatetotals, $xxUpdTot, 'return doupdate()', TRUE) ?></td>
					</tr>
				  </table>
				</td>
			  </tr>
<?php	} ?>
			  <tr>
				<td class="cobll" colspan="5" height="30">
				  <table width="100%" cellspacing="0" cellpadding="0" border="0">
				    <tr>
					  <td align="center">
<?php			if(trim(@$_SESSION['clientID'])!=''){
					srand((double)microtime()*1000000);
					$sequence = ip2long(@$_SERVER['REMOTE_ADDR']);
					if($sequence===FALSE) $sequence=0;
					mysql_query("DELETE FROM tmplogin WHERE tmplogindate < '" . date("Y-m-d H:i:s", time()-(3*60*60*24)) . "' OR tmploginid='" . escape_string($thesessionid) . "'") or print(mysql_error());
					mysql_query("INSERT INTO tmplogin (tmploginid, tmploginname, tmploginchk, tmplogindate) VALUES ('" . escape_string($thesessionid) . "','" . escape_string($_SESSION['clientID']) . "','" . $sequence . "','" . date('Y-m-d H:i:s', time()) . "')") or print(mysql_error());
					print whv('checktmplogin',$sequence);
					if(($_SESSION['clientActions'] & 8)==8 || ($_SESSION['clientActions'] & 16)==16){
						if(@$minwholesaleamount!='') $minpurchaseamount=$minwholesaleamount;
						if(@$minwholesalemessage!='') $minpurchasemessage=$minwholesalemessage;
					}
				} ?>
			  <table width="100%" cellspacing="2" cellpadding="2" border="0">
<?php		if($checkoutmode=='savedcart'){
				// Do nothing
			}elseif($totalgoods < @$minpurchaseamount){ ?>
				<tr><td width="100%" align="center" colspan="2"><?php print @$minpurchasemessage?></td></tr>
<?php		}elseif(@$forceclientlogin==TRUE && @$_SESSION['clientID']==''){ ?>
				<tr><td width="100%" align="center" colspan="2"><?php print $xxBfChk?> <a class="ectlink" href="cart.php?mode=login"><strong><?php print $xxLogin?></strong></a><?php if(@$allowclientregistration==TRUE) print ' ' . $xxOr . ' <a class="ectlink" href="cart.php?mode=newaccount"><strong>' . $xxCrAc . '</strong></a>'?>.</td></tr>
<?php		}elseif($stockwarning){ ?>
				<tr><td width="100%" align="center" colspan="2">&nbsp;</td></tr>
<?php		}else{
				$sSQL = 'SELECT payProvID,payProvData1,payProvData2,payProvDemo FROM payprovider WHERE payProvEnabled=1 AND payProvLevel<=' . $minloglevel . (@$paypalhostedsolution?' AND payProvID<>18':'') . ' ORDER BY payProvOrder';
				$result = mysql_query($sSQL) or print(mysql_error());
				$regularcheckoutshown=FALSE;
				while($rs = mysql_fetch_assoc($result)){
					if($rs['payProvID']==19){ ?>
				<tr><td align="center" colspan="2"><?php print $xxPPPBlu?></td></tr>
				<tr><td colspan="2" align="center"><input type="image" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckoutsm.gif" onclick="document.forms.checkoutform.cart.value='';document.forms.checkoutform.mode.value='paypalexpress1';" alt="PayPal Express" /></td></tr>
<?php				}elseif($rs['payProvID']==20){
						if($xxGooCo!=''){ ?><tr><td align="center" colspan="2"><strong><?php print $xxGooCo?></strong></td></tr><?php } ?>
				<tr><td colspan="2" align="center"><?php $theurl = writegoogleparams($rs['payProvData1'], $rs['payProvData2'], $rs['payProvDemo']); ?><input type="image" alt="Google Checkout" src="http://checkout.google.com/buttons/checkout.gif?merchant_id=<?php print $rs['payProvData1'] . (@$googlebuttonparams!='' ? $googlebuttonparams : '&amp;w=160&amp;h=43&amp;style=white&amp;variant=text&amp;loc=en_US') ?>" onclick="document.forms.checkoutform.cart.value=googlecart;document.forms.checkoutform.action='<?php print $theurl?>';<?php if(@$googlecheckoutanalytics==TRUE) print 'setUrchinInputCode();' ?>" /></td></tr>
<?php				}elseif(! $regularcheckoutshown){
						$regularcheckoutshown=TRUE; ?>
				<tr><td width="100%" align="center" colspan="2"><strong><?php print $xxPrsChk?></strong></td></tr>
				<tr><td align="center" colspan="2"><?php print imageorsubmit($imgcheckoutbutton, $xxCOTxt . '" onclick="document.forms.checkoutform.action=\''.$cartpath.'\';document.forms.checkoutform.cart.value=\'\';document.forms.checkoutform.mode.value=\'checkout\';', 'checkoutbutton')?></td></tr>
<?php				}
				}
mysql_free_result($result);
			} ?>
			  </table>
<script language="javascript" type="text/javascript">/* <![CDATA[ */
<?php
	if(@$origCountryCode!="US" && $origCountryCode!="CA" && $wantstateselector && @$dynamicuscastates==TRUE && @$upsnegdrates==TRUE){
		createdynamicstates();
	}
	if($adminAltRates==2 && ((($shipping+$handling)-$freeshipamnt)>0 || $errormsg!='')){
		print "var bestcarrier=".$shipType.";var bestestimate=" . ((($shipping+$handling)-$freeshipamnt) + ($errormsg!=''?1000000:0)) . ";\r\n";
		print 'var vstotalgoods=' . $totalgoods . ";\r\ngetalternatecarriers();\r\n";
	}
?>
var googlecart='<?php print $googlecart?>';
function changechecker(){
<?php if($totalgoods < @$minpurchaseamount) print 'if(document.forms.checkoutform.mode.value!="dologin")return false;' ?>
dowarning=false;
<?php print $changechecker?>
if(dowarning){
	if(confirm('<?php print str_replace("'","\'",$xxWrnChQ)?>')) return doupdate(); else return(true);
}
return true;
}
/* ]]> */</script>
<input type="hidden" name="estimate" value="<?php print number_format(($totalgoods+$shipping+$stateTax+$countryTax)-($totaldiscounts+$freeshipamnt+$loyaltypointdiscount),2,'.','') ?>" />
					  </td>
					  <td width="16" height="26" align="right" valign="bottom"><img src="images/tablebr.png" alt="" /></td>
					</tr>
				  </table>
				</td>
			  </tr>
<?php
	}else{
		$cartEmpty=TRUE; ?>
			  <tr>
			    <td class="cobll" colspan="6" align="center">
				  <p>&nbsp;</p><?php
					if($checkoutmode=='savedcart')
						print '<p><strong>' . ($listname==''?$xxMyWisL:htmlspecials($listname)) . '</strong></p><p>' . $xxLisEmp . '</p><p>' . $xxViewMC . ' <a class="ectlink" href="cart.php"><strong>'.$xxClkHere.'</strong></a>.</p>';
					else{
						if(@$_GET['mode']!="login" && @$_GET['mode']!="newaccount") print '<p>' . $xxSryEmp . '</p>';
						if(@$_GET['mode']!="logout") print "<p>" . $xxGetSta . ' <a class="ectlink" href="'.$xxHomeURL.'"><strong>'.$xxClkHere.'</strong></a>.</p>';
					} ?>
<p>&nbsp;</p>

<script language="javascript" type="text/javascript">/* <![CDATA[ */
var ectvalue=Math.floor(Math.random()*10000 + 1);
document.cookie = "ECTTESTCART=" + ectvalue + "; path=/";
if((document.cookie+";").indexOf("ECTTESTCART=" + ectvalue + ";") < 0) document.write("<?php print str_replace('"', '\"', $xxNoCk . " " . $xxSecWar)?>");
/* ]]> */</script>
<noscript><?php print $xxNoJS . " " . $xxSecWar?></noscript>
				  <p><?php	if($thefrompage!='' && (@$actionaftercart==2 || @$actionaftercart==3)) $thehref = htmlspecials($thefrompage); else $thehref = $xxHomeURL;
							print imageorlink(@$imgcontinueshopping, $xxCntShp, $thehref, FALSE); ?></p><p>&nbsp;</p>
				</td>
			  </tr>
<?php
	} ?>	</table>
	</form>
<?php
}
?>