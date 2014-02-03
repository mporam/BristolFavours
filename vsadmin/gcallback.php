<?php
//=========================================
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property
//of Internet Business Solutions SL. Any use, reproduction, disclosure or copying
//of any kind without the express and written permission of Internet Business 
//Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
function microtime_float(){
   list($usec, $sec) = explode(' ', microtime());
   return((float)$usec + (float)$sec);
}
@include 'adminsession.php';
session_cache_limiter('none');
session_start();
ob_start();
include "db_conn_open.php";
include "inc/languagefile.php";
include "includes.php";
include "inc/incemail.php";
include "inc/incfunctions.php";
if(@$debugmode==TRUE) $time_start = microtime_float();
$debuginfo='';
$enableclientlogin=FALSE;
$forceclientlogin=FALSE;
if(@$dateadjust=='') $dateadjust=0;
$usehst=FALSE;
$maxcacheid=0;
$giftcerts=array();
function debug_mysql_error(){
	global $debugmode,$debuginfo;
	$errtxt = mysql_error();
	if($debugmode) $debuginfo .= $errtxt . "\r\n";
	return $errtxt;
}
function getgcsessionsql(){
	global $hasclientid,$thesessionid;
	return ($hasclientid ? 'cartClientID=' . $thesessionid : "(cartClientID=0 AND cartSessionID='" . $thesessionid . "')");
}
function getgcordersessionsql(){
	global $hasclientid,$thesessionid;
	return ($hasclientid ? 'ordClientID=' . $thesessionid : "(ordClientID=0 AND ordSessionID='" . $thesessionid . "')");
}
function writeresultstructure(){
	global $cpncodes,$giftcerts,$cpnmessage,$shipmethod,$cacheaddress,$responsexml,$maxcacheid,$noshipping,$addressid,$countryCurrency,$shipping,$handling,$freeshipamnt,$rgcpncode,$gotcpncode,$totaldiscounts,$stateTax,$countryTax,$appliedcouponname,$appliedcouponamount,$xxGCBal,$xxGCCNoF;
	$responsexml2 = '<result' . ($noshipping ? '' : ' shipping-name="' . $shipmethod . '"') . ' address-id="' . $addressid . '">';
	if(! $noshipping) $responsexml2 .= '<shipping-rate currency="' . $countryCurrency . '">' . round(($shipping+$handling)-$freeshipamnt, 2) . '</shipping-rate>';
	$responsexml2 .= '<shippable>true</shippable>';
	if(count($cpncodes) > 0 || count($giftcerts) > 0){
		$responsexml2 .= '<merchant-code-results>';
		foreach($giftcerts as $key => $value){
			$sSQL = "SELECT gcID,gcRemaining FROM giftcertificate WHERE gcID='" . escape_string($value) . "' AND gcRemaining>0 AND gcAuthorized<>0";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				$responsexml2 .= '<coupon-result><valid>true</valid>';
				$responsexml2 .= '<calculated-amount currency="' . $countryCurrency . '">' . round($rs['gcRemaining'], 2) . '</calculated-amount>';
				$responsexml2 .= '<code>' . $value . '</code>';
				$responsexml2 .= "<message>" . $xxGCBal . FormatEuroCurrency($rs['gcRemaining']) . '</message>';
			}else{
				$responsexml2 .= '<coupon-result><valid>false</valid>';
				$responsexml2 .= '<code>' . $value . '</code>';
				$responsexml2 .= '<message>' . $xxGCCNoF . '</message>';
			}
			mysql_free_result($result);
			$responsexml2 .= '</coupon-result>';
		}
		foreach($cpncodes as $key => $value){
			if($value==$rgcpncode){
				$responsexml2 .= '<coupon-result><valid>' . ($gotcpncode ? 'true' : 'false') . '</valid>';
				if($totaldiscounts>0) $responsexml2 .= '<calculated-amount currency="' . $countryCurrency . '">' . $appliedcouponamount . '</calculated-amount>';
				$responsexml2 .= '<code>' . $rgcpncode . '</code>';
				if($cpnmessage != '')
					$responsexml2 .= '<message>' . xmlencodecharref(str_replace('<br />',"\r\n",$appliedcouponname)) . '</message>';
			}else{
				$responsexml2 .= '<coupon-result><valid>false</valid>';
				$responsexml2 .= '<code>' . $value . '</code>';
				$responsexml2 .= '<message>This coupon is not valid in conjunction with other coupons.</message>';
			}
			$responsexml2 .= '</coupon-result>';
		}
		$responsexml2 .= '</merchant-code-results>';
	}
	$responsexml2 .= '<total-tax currency="' . $countryCurrency . '">' . round($stateTax+$countryTax,2) . '</total-tax>';
	$responsexml2 .= '</result>';
	$responsexml .= $responsexml2;
	$cacheaddress[$maxcacheid][3] .= $responsexml2;
}

$alreadygotadmin = getadminsettings();

$success = getpayprovdetails(20,$googledata1,$googledata2,$googledata3,$googledemomode,$ppmethod);
if(isset($HTTP_RAW_POST_DATA))
	$xmlResponse = $HTTP_RAW_POST_DATA;
else
	$xmlResponse = implode("\r\n", file('php://input'));

$xmlResponse2 = '<?xml version="1.0" encoding="UTF-8"?> ....';
// print str_replace("<","<br />&lt;",str_replace("</","&lt;/",$xmlResponse2)) . "<br />\n";

$responsexml='';
$standalonetestmode=FALSE;

if($standalonetestmode) $xmlResponse=$xmlResponse2;

if(@$googlecallbackscript != ''){
	if(strpos(@$_SERVER['PHP_SELF'], '/vsadmin/gcallback.php')!==FALSE) $success=FALSE; else $disablebasicauth=TRUE;
}

if(@$disablebasicauth==TRUE){
	// Do Nothing
}elseif($success){
	$http_auth = @$_SERVER['HTTP_AUTHORIZATION'];
	if($http_auth=='') $http_auth = @$_SERVER['HTTP_AUTHENTICATION'];
	if($http_auth=='') $http_auth = @$_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
	if($http_auth==''){
		if($googledata1!=@$_SERVER['PHP_AUTH_USER'] || $googledata2!=@$_SERVER['PHP_AUTH_PW'] || $googledata1=='')
			$success=FALSE;
	}elseif(substr($http_auth, 0, 6)=='Basic '){
		$http_auth = substr($http_auth, 6);
		$http_auth = base64_decode($http_auth);
		if(strpos($http_auth, ':')===FALSE){
			$success=FALSE;
		}else{
			$auth_split = explode(':',$http_auth);
			if($googledata1 != $auth_split[0] || $googledata2 != $auth_split[1]) $success=FALSE;
		}
	}else
		$success=FALSE;
	if($debugmode){
		if(@$_SERVER['PHP_AUTH_USER']=='' && @$_SERVER['PHP_AUTH_PW']=='' && @$_SERVER['HTTP_AUTHENTICATION']=='' && @$_SERVER['HTTP_AUTHORIZATION']==''){
			$responsexml .= 'Checking authentication. Basic auth sent by Google is blank.' . "\n";
			foreach($_SERVER as $key => $value){
				$responsexml .= $key . " : " . $value . "\n";
			}
		}
	}
}
if($standalonetestmode) $success=TRUE;
if(! $success){
	// response.clear
	if(! $standalonetestmode){
		header('HTTP/1.1 401 Unauthorized');
		echo '<html><head><title>401 Unauthorized</title></head><body>';
		echo 'I\'m sorry, you are not authorized to view this page.<br>';
		echo '</body></html>';
	}else
		print 'auth failure<br>';
}else{
	$gcXmlDoc = new vrXMLDoc($xmlResponse);
	$nodeList = $gcXmlDoc->nodeList->childNodes[0];
	$thismessage = $gcXmlDoc->nodeList->nodeName[0];
	switch ($thismessage) {
	case 'merchant-calculation-callback':
		$cartisincluded=TRUE;
		$rgcpncode='';
		$ordPayProvider=20;
		if($standalonetestmode) print '<html><body>';
		$responsexml = '<?xml version="1.0" encoding="UTF-8"?>';
		$responsexml .= '<merchant-calculation-results xmlns="http://checkout.google.com/schema/2">';
		$responsexml .= '<results>';
		$thesessionid = $nodeList->getValueByTagName('sessionid');
		if(substr($thesessionid,0,3)=='cid') $hasclientid=TRUE; else $hasclientid=FALSE;
		$thesessionid = str_replace("'",'',substr($thesessionid,3));
		$clientuser = trim($nodeList->getValueByTagName('clientuser'));
		unset($_SESSION['clientID']);
		if($hasclientid){
			$_SESSION['clientID']=$thesessionid;
			$sSQL = "SELECT clUserName,clActions,clLoginLevel,clPercentDiscount FROM customerlogin WHERE clID='" . escape_string($thesessionid) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_array($result)){
				$_SESSION['clientUser']=$rs['clUserName'];
				$_SESSION['clientActions']=$rs['clActions'];
				$_SESSION['clientLoginLevel']=$rs['clLoginLevel'];
				$_SESSION['clientPercentDiscount']=(100.0-(double)$rs['clPercentDiscount'])/100.0;
			}
			mysql_free_result($result);
		}
		include './inc/uspsshipping.php';
		include './inc/inccart.php';
		getpayprovhandling();
		for($i1 = 0; $i1 < $nodeList->length; $i1++){
			if($nodeList->nodeName[$i1]=='calculate'){
				$obj2=$nodeList->childNodes[$i1];
				$shipmethods=array();
				$cpncodes=array();
				$usestateabbrev=TRUE;
				$cpnmessage = '<br />';
				for($i2 = 0; $i2 < $obj2->length; $i2++){
					if($obj2->nodeName[$i2]=='shipping'){
						$obj3=$obj2->childNodes[$i2];
						for($i3 = 0; $i3 < $obj3->length; $i3++){
							if($obj3->nodeName[$i3]=='method'){
								$themethod = getattributes($obj3->attributes[$i3], 'name');
								array_push($shipmethods, $themethod);
							}
						}
					}elseif($obj2->nodeName[$i2]=='merchant-code-strings'){
						$obj3=$obj2->childNodes[$i2];
						for($i3 = 0; $i3 < $obj3->length; $i3++){
							if($obj3->nodeName[$i3]=='merchant-code-string'){
								$thecode = getattributes($obj3->attributes[$i3], 'code');
								$sSQL = "SELECT gcID FROM giftcertificate WHERE gcID='" . escape_string($thecode)."'";
								$result = mysql_query($sSQL) or print(mysql_error());
								if(mysql_num_rows($result)==0) $isgiftcert=FALSE; else $isgiftcert=TRUE;
								mysql_free_result($result);
								if($isgiftcert){
									array_push($giftcerts, $thecode);
								}else{
									if($rgcpncode=='') $rgcpncode = $thecode; // Because they arrive in NON reverse order
									array_push($cpncodes, $thecode);
								}
							}
						}
					}
				}
				$saveshipmethods = $shipmethods;
				for($i2 = 0; $i2 < $obj2->length; $i2++){
					if($obj2->nodeName[$i2]=='addresses'){
						$obj3=$obj2->childNodes[$i2];
						for($i3 = 0; $i3 < $obj3->length; $i3++){
							if($obj3->nodeName[$i3]=='anonymous-address'){
								$shipmethods = $saveshipmethods;
								$numshipoptions=0;
								$totShipOptions=0;
								$freeshippingapplied=FALSE;
								$noshipping=($shipType==0 && $handling==0);
								$totaldiscounts=0;
								$gotcpncode=FALSE;
								$cpnmessage = '<br />';
								$iTotItems = 0;
								$destinationsupported=TRUE;
								$shipstate='';
								$addressid = getattributes($obj3->attributes[$i3], 'id');
								$obj4=$obj3->childNodes[$i3];
								for($i4 = 0; $i4 < $obj4->length; $i4++){
									if($obj4->nodeName[$i4]=='country-code'){
										$shipCountryCode = $obj4->nodeValue[$i4];
									}elseif($obj4->nodeName[$i4]=='region'){
										$shipstate = $obj4->nodeValue[$i4];
									}elseif($obj4->nodeName[$i4]=='postal-code'){
										$destZip = $obj4->nodeValue[$i4];
									}
								}
								// Firstly check in the cache
								$foundincache=-1;
								for($gindex3=0; $gindex3 < $maxcacheid; $gindex3++){
									if($cacheaddress[$gindex3][0]==$destZip && $cacheaddress[$gindex3][1]==$shipCountryCode) $foundincache=$gindex3;
								}
								if($foundincache >= 0){
									$responsexml .= str_replace($cacheaddress[$foundincache][2], $addressid, $cacheaddress[$foundincache][3]);
									$debuginfo.="Found in cache: " . (microtime_float() - $time_start) . "\r\n";
								}else{
									$cacheaddress[$maxcacheid][0] = $destZip;
									$cacheaddress[$maxcacheid][1] = $shipCountryCode;
									$cacheaddress[$maxcacheid][2] = $addressid;
									$cacheaddress[$maxcacheid][3] = '';
									$sSQL = "SELECT countryID,countryName,countryTax,countryCode,countryFreeShip,countryOrder,countryEnabled FROM countries WHERE ";
									if($shipCountryCode=='GB')
										$sSQL .= 'countryID=201';
									elseif($shipCountryCode=='FR')
										$sSQL .= 'countryID=65';
									elseif($shipCountryCode=='PT')
										$sSQL .= 'countryID=153';
									elseif($shipCountryCode=='ES')
										$sSQL .= 'countryID=175';
									else
										$sSQL .= "countryCode='" . escape_string($shipCountryCode) . "'";
									$result = mysql_query($sSQL) or print(mysql_error());
									if($rs = mysql_fetch_array($result)){
										if(trim(@$_SESSION['clientUser']) != '' && ((@$_SESSION['clientActions'] & 2) == 2)) $countryTaxRate=0; else $countryTaxRate = $rs['countryTax'];
										$shipCountryID = $rs['countryID'];
										$shipCountryCode = $rs['countryCode'];
										$freeshipavailtodestination = ($rs['countryFreeShip']==1);
										$shiphomecountry = ($rs['countryOrder']>=2);
										$shipcountry = $rs['countryName'];
										if($rs['countryEnabled']==0) $destinationsupported=FALSE;
									}
									if($shipCountryCode=='GB' && $shiphomecountry && $shipstate==''){
										getshipstatefromzip();
									}
									if($shiphomecountry){
										$sSQL = "SELECT stateTax,stateAbbrev,stateFreeShip,stateEnabled FROM states WHERE stateName='" . escape_string($shipstate) . "' OR stateAbbrev='" . escape_string($shipstate) . "'";
										$result = mysql_query($sSQL) or print(mysql_error());
										if(mysql_num_rows($result)==0 && $shipCountryCode=='GB'){
											getshipstatefromzip(); // The state wasn't found so give it one more try to get the region from the zip.
											$sSQL = "SELECT stateTax,stateAbbrev,stateFreeShip,stateEnabled FROM states WHERE stateName='" . escape_string($shipstate) . "' OR stateAbbrev='" . escape_string($shipstate) . "'";
											$result = mysql_query($sSQL) or print(mysql_error());
										}
										if($rs = mysql_fetch_array($result)){
											$stateTaxRate=$rs['stateTax'];
											$shipStateAbbrev=$rs['stateAbbrev'];
											$freeshipavailtodestination=($freeshipavailtodestination && ($rs['stateFreeShip']==1));
											if($rs['stateEnabled']==0 && $shipCountryCode!='GB') $destinationsupported=FALSE;
										}
									}
									if(! $destinationsupported){
										foreach($shipmethods as $key => $shipmethod){
											$responsexml .= '<result' . ($noshipping ? '' : ' shipping-name="' . $shipmethod . '"') . ' address-id="' . $addressid . '"><shipping-rate currency="' . $countryCurrency . '">0.00</shipping-rate><shippable>false</shippable><total-tax currency="' . $countryCurrency . '">0.00</total-tax></result>';
										}
									}else{
										$thePWeight=0; $thePQuantity=0;
										initshippingmethods();
										$totalgoods=0;
										$alldata='';
										$index = 0;
										$sSQL = "SELECT cartID,cartProdID,cartProdName,cartProdPrice,cartQuantity,pWeight,pShipping,pShipping2,pExemptions,pSection,topSection,pDims,pTax," . getlangid('pDescription',2) . " FROM cart LEFT JOIN products ON cart.cartProdID=products.pID LEFT OUTER JOIN sections ON products.pSection=sections.sectionID WHERE cartCompleted=0 AND " . getgcsessionsql();
										$allcart = mysql_query($sSQL) or print(mysql_error());
										if(($itemsincart = mysql_num_rows($allcart)) > 0){
											while($rsCart=mysql_fetch_array($allcart)){
												$index++;
												if(is_null($rsCart['pWeight'])) $rsCart['pWeight']=0;
												if(($rsCart['cartProdID']==$giftcertificateid || $rsCart['cartProdID']==$donationid) && is_null($rsCart['pExemptions'])) $rsCart['pExemptions']=15;
												$sSQL = "SELECT SUM(coPriceDiff) AS coPrDff FROM cartoptions WHERE coCartID=". $rsCart["cartID"];
												$result = mysql_query($sSQL) or print(mysql_error());
												if($rs = mysql_fetch_array($result)){
													$rsCart["cartProdPrice"] += (double)$rs["coPrDff"];
												}
												mysql_free_result($result);
												$sSQL = "SELECT SUM(coWeightDiff) AS coWghtDff FROM cartoptions WHERE coCartID=". $rsCart["cartID"];
												$result = mysql_query($sSQL) or print(mysql_error());
												if($rs = mysql_fetch_array($result)){
													$rsCart["pWeight"] += (double)$rs["coWghtDff"];
												}
												mysql_free_result($result);
												$runTot=$rsCart["cartProdPrice"] * (int)($rsCart["cartQuantity"]);
												$totalquantity += (int)($rsCart["cartQuantity"]);
												$totalgoods += $runTot;
												$thistopcat=0;
												if(trim(@$_SESSION['clientUser']) != '') $rsCart['pExemptions'] = ((int)$rsCart['pExemptions'] | (int)$_SESSION['clientActions']);
												if(($shipType==2 || $shipType==3 || $shipType==4 || $shipType==6 || $shipType==7) && (double)$rsCart['pWeight']<=0.0)
													$rsCart['pExemptions'] = ($rsCart['pExemptions'] | 4);
												if(($rsCart['pExemptions'] & 1)==1) $statetaxfree += $runTot;
												if(($rsCart['pExemptions'] & 8)!=8){ $handlingeligableitem=TRUE; $handlingeligablegoods += $runTot; }
												if(@$perproducttaxrate==TRUE){
													if(is_null($rsCart['pTax'])) $rsCart['pTax'] = $countryTaxRate;
													if(($rsCart['pExemptions'] & 2) != 2) $countryTax += (($rsCart['pTax'] * $runTot) / 100.0);
												}else{
													if(($rsCart['pExemptions'] & 2)==2) $countrytaxfree += $runTot;
												}
												if(($rsCart['pExemptions'] & 4)==4) $shipfreegoods += $runTot;
												addproducttoshipping($rsCart, $index);
											}
										}else{
											$errormsg = "Error, couldn't find cart";
											$success=FALSE;
										}
										calculatediscounts($totalgoods, false, $rgcpncode);
										if($totaldiscounts > $totalgoods) $totaldiscounts = $totalgoods;
										if($success && calculateshipping()){
											$freeshipamnt=0;
											$freeshippingincludeshandling=FALSE;
											insuranceandtaxaddedtoshipping();
											calculateshippingdiscounts(false);
											$freeshipamnt=0;
											$cpnmessage = substr($cpnmessage, 6);
											if(count($shipmethods) == 0){
												$noshipping=TRUE;
												if($freeshippingincludeshandling==TRUE){ $handling=0; $handlingchargepercent=0; }else{ $handling=$orighandling; $handlingchargepercent=$orighandlingpercent; }
												$shipping=0;
												calculatetaxandhandling();
												writeresultstructure();
											}else{
												if($shipType==0 || $shipType==1 || ! $somethingToShip){
													foreach($shipmethods as $key => $shipmethod){
														if(xmlencodecharref($xxShipHa) == $shipmethod){
															if($freeshippingincludeshandling==TRUE){ $handling=0; $handlingchargepercent=0; }else{ $handling=$orighandling; $handlingchargepercent=$orighandlingpercent; }
															if($freeshippingapplied) $shipping=0;
															$freeshipamnt=0;
															calculatetaxandhandling();
															writeresultstructure();
															$shipmethods[$key]='';
														}
													}
												}elseif($shipType>=2 && $shipType<=7){
													if($shipType==2 || $shipType==5) $totShipOptions=$numshipoptions; else $totShipOptions=$maxshipoptions;
													for($gindex4=0; $gindex4 < $totShipOptions; $gindex4++){
														foreach($shipmethods as $key => $shipmethod){
															// print "matching: " . $intShipping[$gindex4][5] . " : " . $intShipping[$gindex4][4] . " : " . $shipmethod . "<br>";
															if($shipmethod==''){
																// Already matched
															}elseif($shipType==3 || $shipType==4 || $shipType==6 || $shipType==7){
																if($intShipping[$gindex4][3]==TRUE){
																	if(xmlencodecharref($intShipping[$gindex4][0]) == $shipmethod){
																		if($freeshippingincludeshandling==TRUE){ $handling=0; $handlingchargepercent=0; }else{ $handling=$orighandling; $handlingchargepercent=$orighandlingpercent; }
																		if($freeshippingapplied && $intShipping[$gindex4][4] != 0) $shipping=0; else $shipping=$intShipping[$gindex4][2];
																		calculatetaxandhandling();
																		writeresultstructure();
																		$shipmethods[$key]='';
																	}
																}
															}else{
																// print "matching: " . $intShipping[$gindex4][0] . " : " . $intShipping[$gindex4][4] . " : " . $shipmethod . "<br>";
																if(xmlencodecharref($intShipping[$gindex4][0]) == $shipmethod){
																	if($freeshippingincludeshandling==TRUE){ $handling=0; $handlingchargepercent=0; }else{ $handling=$orighandling; $handlingchargepercent=$orighandlingpercent; }
																	if($freeshippingapplied && $intShipping[$gindex4][4] != 0) $shipping=0; else $shipping=$intShipping[$gindex4][2];
																	calculatetaxandhandling();
																	writeresultstructure();
																	$shipmethods[$key]='';
																}
															}
														}
													}
												}elseif($shipType==0){
													if($freeshippingincludeshandling==TRUE){ $handling=0; $handlingchargepercent=0; }else{ $handling=$orighandling; $handlingchargepercent=$orighandlingpercent; }
													$shipping=0;
													calculatetaxandhandling();
													writeresultstructure();
												}
												if(@$willpickuptext != ''){
													$noshipping=FALSE;
													foreach($shipmethods as $key => $shipmethod){
														if(xmlencodecharref($willpickuptext) == $shipmethod){
															if(@$willpickupcost=='') $willpickupcost=0;
															if($freeshippingincludeshandling==TRUE){ $handling=0; $handlingchargepercent=0; }else{ $handling=$orighandling; $handlingchargepercent=$orighandlingpercent; }
															$shipping=$willpickupcost;
															$freeshipamnt=0;
															calculatetaxandhandling();
															writeresultstructure();
															$shipmethods[$key]='';
														}
													}
												}
												foreach($shipmethods as $key => $shipmethod){
													if($shipmethod != ''){
														$responsexml2 = '<result' . ($noshipping ? '' : ' shipping-name="' . $shipmethod . '"') . ' address-id="' . $addressid . '"><shipping-rate currency="' . $countryCurrency . '">0.00</shipping-rate><shippable>false</shippable><total-tax currency="' . $countryCurrency . '">0.00</total-tax></result>';
														$responsexml .= $responsexml2;
														$cacheaddress[$maxcacheid][3] .= $responsexml2;
													}
												}
											}
										}else{
											$responsexml .= '<error-message>' . $errormsg . '</error-message>';
										}
										$maxcacheid++;
									}
									$debuginfo.="NOT found in cache: " . (microtime_float() - $time_start) . "\r\n";
								}
							}
						}
					}
				}
			}
		}
		$responsexml .= '</results></merchant-calculation-results>';
		if($standalonetestmode)
			print "<HR>" . str_replace("<","<br />&lt;",str_replace('</','&lt;/',$responsexml)) . "<br />\n";
		else{
			ob_end_clean();
			print $responsexml;
		}
	break;
	
	case 'new-order-notification':
		function get_google_address($xmlobj,&$gEmail,&$gName,&$gLastName,&$gAddress,&$gAddress2,&$gCity,&$gState,&$gZip,&$gCountry,&$gPhone){
			for($index2=0; $index2 < $xmlobj->length; $index2++){
				switch($xmlobj->nodeName[$index2]){
				case "email":
					$gEmail=$xmlobj->nodeValue[$index2];
				break;
				case "contact-name":
					splitfirstlastname($gName=$xmlobj->nodeValue[$index2],$gName,$gLastName);
				break;
				case "address1":
					$gAddress=$xmlobj->nodeValue[$index2];
				break;
				case "address2":
					$gAddress2=$xmlobj->nodeValue[$index2];
				break;
				case "city":
					$gCity=$xmlobj->nodeValue[$index2];
				break;
				case "region":
					$gState=$xmlobj->nodeValue[$index2];
				break;
				case "postal-code":
					$gZip=$xmlobj->nodeValue[$index2];
				break;
				case "country-code":
					$gCountry=$xmlobj->nodeValue[$index2];
				break;
				case "phone":
					$gPhone=$xmlobj->nodeValue[$index2];
				break;
				}
			}
		}
		$totaldiscounts=0;
		$stateTax=0;
		$countryTax=0;
		$totalgoods=0;
		$handling=0;
		$shipping=0;
		$freeshipamnt=0;
		$cpnmessage='';
		$ordComLoc=0;
		$ordAddInfo='';
		$ordAffiliate='';
		$ordExtra1='';
		$ordExtra2='';
		$ordShipExtra1='';
		$ordShipExtra2='';
		$ordCheckoutExtra1='';
		$ordCheckoutExtra2='';
		$emailisallowed=FALSE;
		$success=TRUE;
		for($i1 = 0; $i1 < $nodeList->length; $i1++){
			switch($nodeList->nodeName[$i1]){
			case "google-order-number":
				$ordAuthNumber=$nodeList->nodeValue[$i1];
			break;
			case "order-total":
				$ordTotal=$nodeList->nodeValue[$i1];
			break;
			case "shopping-cart":
				$hasclientid=FALSE;
				$thesessionid = '0';
				$thesessionid = $nodeList->childNodes[$i1]->getValueByTagName('sessionid');
				if(substr($thesessionid,0,3)=='cid') $hasclientid=TRUE;
				$thesessionid = str_replace("'", '', substr($thesessionid,3));
				if($thesessionid==''){
					$success=FALSE;
					$thesessionid = '0';
				}
				$ordAffiliate = $nodeList->childNodes[$i1]->getValueByTagName('partner');
				$obj2=$nodeList->childNodes[$i1];
				for($i2 = 0; $i2 < $obj2->length; $i2++){
					if($obj2->nodeName[$i2]=='items'){
						$obj3=$obj2->childNodes[$i2];
						for($i3 = 0; $i3 < $obj3->length; $i3++){
							if($obj3->nodeName[$i3]=='item'){
								$obj4=$obj3->childNodes[$i3];
								if(($objdisc = $obj4->getValueByTagName("discountflag")) != null){
									if($objdisc=='true'){
										$obj5 = $obj4->getValueByTagName("unit-price");
										$totaldiscounts -= $obj5;
										$obj5 = $obj4->getValueByTagName("item-description");
										$cpnmessage = str_replace(' - ', '<br />', $obj5) . '<br />' . $cpnmessage;
									}
								}
							}
						}
					}
				}
			break;
			case 'total-tax':
				$countryTax=$nodeList->nodeValue[$i1];
			break;
			case 'order-adjustment':
				$obj2=$nodeList->childNodes[$i1];
				for($i2 = 0; $i2 < $obj2->length; $i2++){
					if($obj2->nodeName[$i2]=='merchant-codes'){
						$obj3=$obj2->childNodes[$i2];
						for($i3 = 0; $i3 < $obj3->length; $i3++){
							if($obj3->nodeName[$i3]=='coupon-adjustment'){
								$obj4=$obj3->childNodes[$i3];
								for($i4 = 0; $i4 < $obj4->length; $i4++){
									if($obj4->nodeName[$i4]=='code')
										$giftcertcode = $obj4->nodeValue[$i4];
									elseif($obj4->nodeName[$i4]=='applied-amount'){
										$giftcertdiscount = $obj4->nodeValue[$i4];
										$totaldiscounts += $obj4->nodeValue[$i4];
									}elseif($obj4->nodeName[$i4]=='message')
										$cpnmessage = $obj4->nodeValue[$i4] . '<br />' . $cpnmessage;
								}
								$giftcerts[]=array($giftcertcode, $giftcertdiscount);
							}
						}
					}elseif($obj2->nodeName[$i2]=='shipping'){
						$obj3=$obj2->childNodes[$i2];
						if(($obj4 = $obj3->getValueByTagName('shipping-name')) != null) $shipMethod=$obj4;
						if(($obj4 = $obj3->getValueByTagName('shipping-cost')) != null) $shipping=$obj4;
					}elseif($obj2->nodeName[$i2]=='total-tax'){
						$countryTax += $obj2->nodeValue[$i2];
					}
				}
			break;
			case 'buyer-billing-address':
				get_google_address($nodeList->childNodes[$i1],$ordEmail,$ordName,$ordLastName,$ordAddress,$ordAddress2,$ordCity,$ordState,$ordZip,$ordCountry,$ordPhone);
			break;
			case 'buyer-shipping-address':
				get_google_address($nodeList->childNodes[$i1],$dummyEmail,$ordShipName,$ordShipLastName,$ordShipAddress,$ordShipAddress2,$ordShipCity,$ordShipState,$ordShipZip,$ordShipCountry,$ordShipPhone);
			break;
			case 'buyer-marketing-preferences':
				$obj2=$nodeList->childNodes[$i1];
				$emailisallowed = ($obj2->getValueByTagName('email-allowed')=='true');
			break;
			}
		}
		if($success){
			$sSQL = "SELECT cartID FROM cart WHERE cartCompleted=0 AND " . getgcsessionsql();
			$result = mysql_query($sSQL) or print(mysql_error());
			$success = (mysql_num_rows($result) > 0);
		}
		if($success){
			$totalgoods = ($ordTotal - ($stateTax+$countryTax+$shipping+$handling)) + $totaldiscounts;
			$sSQL = "SELECT ordID FROM orders WHERE ordAuthNumber='' AND " . getgcordersessionsql();
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_array($result))
				$orderid=$rs["ordID"];
			else
				$orderid="";
			mysql_free_result($result);
			if($ordShipName=='' && $ordShipAddress=='' && $ordShipAddress2=='' && $ordShipCity=='') $ordShipCountry='';
			if($orderid==""){
				$sSQL = "INSERT INTO orders (ordSessionID,ordClientID,ordName,ordLastName,ordAddress,ordAddress2,ordCity,ordState,ordZip,ordCountry,ordEmail,ordPhone,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipZip,ordShipCountry,ordShipPhone,ordPayProvider,ordAuthNumber,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordHandling,ordShipType,ordShipCarrier,ordTotal,ordDate,ordStatus,ordAuthStatus,ordStatusDate,ordComLoc,ordIP,ordAffiliate,ordExtra1,ordExtra2,ordShipExtra1,ordShipExtra2,ordCheckoutExtra1,ordCheckoutExtra2,ordDiscount,ordDiscountText,ordAddInfo) VALUES (";
				$sSQL .= "'" . '0' . "',";
				if($hasclientid) $sSQL .= "'" . escape_string($thesessionid) . "',"; else $sSQL .= "'0',";
				$sSQL .= "'" . escape_string($ordName) . "',";
				$sSQL .= "'" . escape_string($ordLastName) . "',";
				$sSQL .= "'" . escape_string($ordAddress) . "',";
				$sSQL .= "'" . escape_string($ordAddress2) . "',";
				$sSQL .= "'" . escape_string($ordCity) . "',";
				$sSQL .= "'" . escape_string($ordState) . "',";
				$sSQL .= "'" . escape_string($ordZip) . "',";
				$sSQL .= "'" . escape_string($ordCountry) . "',";
				$sSQL .= "'" . escape_string($ordEmail) . "',";
				$sSQL .= "'" . escape_string($ordPhone) . "',";
				$sSQL .= "'" . escape_string($ordShipName) . "',";
				$sSQL .= "'" . escape_string($ordShipLastName) . "',";
				$sSQL .= "'" . escape_string($ordShipAddress) . "',";
				$sSQL .= "'" . escape_string($ordShipAddress2) . "',";
				$sSQL .= "'" . escape_string($ordShipCity) . "',";
				$sSQL .= "'" . escape_string($ordShipState) . "',";
				$sSQL .= "'" . escape_string($ordShipZip) . "',";
				$sSQL .= "'" . escape_string($ordShipCountry) . "',";
				$sSQL .= "'" . escape_string($ordShipPhone) . "',";
				$sSQL .= "'20',"; // ordPayProvider
				$sSQL .= "'" . $ordAuthNumber . "',";
				$sSQL .= "'" . escape_string($shipping-$freeshipamnt) . "',";
				if($usehst){
					$sSQL .= "0,";
					$sSQL .= "0,";
					$sSQL .= ($stateTax + $countryTax) . ",";
				}else{
					$sSQL .= "'" . escape_string($stateTax) . "',";
					$sSQL .= "'" . escape_string($countryTax) . "',";
					$sSQL .= "0,";
				}
				$sSQL .= "'" . escape_string($handling) . "',";
				$sSQL .= "'" . escape_string($shipMethod) . "',";
				if($adminIntShipping != 0 && $ordShipCountry != $origCountryCode)
					$sSQL .= "'" . escape_string($adminIntShipping) . "',";
				else
					$sSQL .= "'" . escape_string($shipType) . "',";
				$sSQL .= "'" . escape_string($totalgoods) . "',";
				$sSQL .= "'" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "',";
				$sSQL .= "2,"; // Status
				$sSQL .= "'',"; // AuthStatus
				$sSQL .= "'" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "',";
				$sSQL .= "'" . $ordComLoc . "',";
				$sSQL .= "'',"; // IP
				$sSQL .= "'" . escape_string($ordAffiliate) . "',";
				$sSQL .= "'" . escape_string($ordExtra1) . "',";
				$sSQL .= "'" . escape_string($ordExtra2) . "',";
				$sSQL .= "'" . escape_string($ordShipExtra1) . "',";
				$sSQL .= "'" . escape_string($ordShipExtra2) . "',";
				$sSQL .= "'" . escape_string($ordCheckoutExtra1) . "',";
				$sSQL .= "'" . escape_string($ordCheckoutExtra2) . "',";
				$sSQL .= "'" . escape_string($totaldiscounts) . "',";
				$sSQL .= "'" . escape_string(substr($cpnmessage,0,255)) . "',";
				$sSQL .= "'" . escape_string($ordAddInfo) . "')";
				mysql_query($sSQL) or print(debug_mysql_error());
				$orderid = mysql_insert_id();
			}else{
				$sSQL = "UPDATE orders SET ";
				$sSQL .= "ordSessionID='" . escape_string($thesessionid) . "',";
				$sSQL .= "ordName='" . escape_string($ordName) . "',";
				$sSQL .= "ordLastName='" . escape_string($ordLastName) . "',";
				$sSQL .= "ordAddress='" . escape_string($ordAddress) . "',";
				$sSQL .= "ordAddress2='" . escape_string($ordAddress2) . "',";
				$sSQL .= "ordCity='" . escape_string($ordCity) . "',";
				$sSQL .= "ordState='" . escape_string($ordState) . "',";
				$sSQL .= "ordZip='" . escape_string($ordZip) . "',";
				$sSQL .= "ordCountry='" . escape_string($ordCountry) . "',";
				$sSQL .= "ordEmail='" . escape_string($ordEmail) . "',";
				$sSQL .= "ordPhone='" . escape_string($ordPhone) . "',";
				$sSQL .= "ordShipName='" . escape_string($ordShipName) . "',";
				$sSQL .= "ordShipAddress='" . escape_string($ordShipAddress) . "',";
				$sSQL .= "ordShipAddress2='" . escape_string($ordShipAddress2) . "',";
				$sSQL .= "ordShipCity='" . escape_string($ordShipCity) . "',";
				$sSQL .= "ordShipState='" . escape_string($ordShipState) . "',";
				$sSQL .= "ordShipZip='" . escape_string($ordShipZip) . "',";
				$sSQL .= "ordShipCountry='" . escape_string($ordShipCountry) . "',";
				$sSQL .= "ordShipPhone='" . escape_string($ordShipPhone) . "',";
				$sSQL .= "ordPayProvider='20',";
				$sSQL .= "ordAuthNumber='" . $ordAuthNumber . "',"; // Not yet authorized
				$sSQL .= "ordShipping='" . ($shipping - $freeshipamnt) . "',";
				if($usehst){
					$sSQL .= "ordStateTax=0,";
					$sSQL .= "ordCountryTax=0,";
					$sSQL .= "ordHSTTax=" . ($stateTax + $countryTax) . ",";
				}else{
					$sSQL .= "ordStateTax='" . $stateTax . "',";
					$sSQL .= "ordCountryTax='" . $countryTax . "',";
					$sSQL .= "ordHSTTax=0,";
				}
				$sSQL .= "ordHandling='" . $handling . "',";
				$sSQL .= "ordShipType='" . $shipMethod . "',";
				if($adminIntShipping != 0 && $ordShipCountry != $origCountryCode)
					$sSQL .= "ordShipCarrier='" . $adminIntShipping . "',";
				else
					$sSQL .= "ordShipCarrier='" . $shipType . "',";
				$sSQL .= "ordTotal='" . $totalgoods . "',";
				$sSQL .= "ordDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "',";
				$sSQL .= "ordStatus=2,";
				$sSQL .= "ordAuthStatus='',";
				$sSQL .= "ordComLoc=" . $ordComLoc . ",";
				$sSQL .= "ordIP='" . @$_SERVER["REMOTE_ADDR"] . "',";
				$sSQL .= "ordAffiliate='" . escape_string($ordAffiliate) . "',";
				$sSQL .= "ordExtra1='" . escape_string($ordExtra1) . "',";
				$sSQL .= "ordExtra2='" . escape_string($ordExtra2) . "',";
				$sSQL .= "ordShipExtra1='" . escape_string($ordShipExtra1) . "',";
				$sSQL .= "ordShipExtra2='" . escape_string($ordShipExtra2) . "',";
				$sSQL .= "ordCheckoutExtra1='" . escape_string($ordCheckoutExtra1) . "',";
				$sSQL .= "ordCheckoutExtra2='" . escape_string($ordCheckoutExtra2) . "',";
				$sSQL .= "ordDiscount='" . $totaldiscounts . "',";
				$sSQL .= "ordDiscountText='" . escape_string(substr($cpnmessage,0,255)) . "',";
				$sSQL .= "ordAddInfo='" . escape_string($ordAddInfo) . "'";
				$sSQL .= " WHERE ordID='" . $orderid . "'";
				mysql_query($sSQL) or print(debug_mysql_error());
			}
			$sSQL="UPDATE cart SET cartOrderID=". $orderid . ",cartCompleted=2 WHERE cartCompleted=0 AND " . getgcsessionsql();
			mysql_query($sSQL) or print(debug_mysql_error());
			
			if(count($giftcerts) > 0){
				$sSQL = "SELECT gcaGCID,gcaAmount FROM giftcertsapplied WHERE gcaOrdID=".$orderid;
				$result = mysql_query($sSQL) or print(mysql_error());
				while($rs = mysql_fetch_assoc($result)){
					mysql_query("UPDATE giftcertificate SET gcRemaining=gcRemaining+" . $rs['gcaAmount'] . " WHERE gcID='" . $rs['gcaGCID'] . "'") or print(mysql_error());
				}
				mysql_free_result($result);
				mysql_query("DELETE FROM giftcertsapplied WHERE gcaOrdID=".$orderid) or print(mysql_error());
				foreach($giftcerts as $key => $value){
					if($value[1] > 0){
						$sSQL = "SELECT gcID,gcRemaining FROM giftcertificate WHERE gcRemaining>0 AND gcAuthorized<>0 AND gcID='" . $value[0] . "'";
						$result = mysql_query($sSQL) or print(mysql_error());
						if($rs = mysql_fetch_assoc($result)){
							mysql_query("INSERT INTO giftcertsapplied (gcaGCID,gcaOrdID,gcaAmount) VALUES ('" . $rs['gcID'] . "'," . $orderid . "," . $value[1] . ')') or print(mysql_error());
							mysql_query("UPDATE giftcertificate SET gcRemaining=gcRemaining-" . $value[1] . " WHERE gcID='" . $rs['gcID'] . "'") or print(mysql_error());
						}
						mysql_free_result($result);
					}
				}
			}

			$cfurl='https://' . ($googledemomode ? 'sandbox' : 'checkout') . '.google.com' . ($googledemomode ? '/checkout' : '') . '/api/checkout/v2/request/Merchant/' . $googledata1;
			$acttext = '<add-merchant-order-number xmlns="http://checkout.google.com/schema/2" google-order-number="' . $ordAuthNumber . '"><merchant-order-number>' . $orderid . '</merchant-order-number></add-merchant-order-number>';
			if(@$pathtocurl != ""){
				exec($pathtocurl . ($cfcert != '' ? ' -E \'' . $cfcert . '\'' : '') . ' --data-binary ' . escapeshellarg('<?xml version="1.0" encoding="UTF-8"?>' . $acttext) . ' ' . $cfurl, $cfres, $retvar);
				$cfres = implode("\n",$cfres);
			}else{
				if (!$ch = curl_init()) {
					print "cURL package not installed in PHP. Set \$pathtocurl parameter.";
					$success=FALSE;
				}else{
					curl_setopt($ch, CURLOPT_URL, $cfurl);
					$headers = array('Authorization: Basic ' . base64_encode($googledata1 . ":" . $googledata2), 'Content-Type: application/xml', 'Accept: application/xml');
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_POSTFIELDS, '<?xml version="1.0" encoding="UTF-8"?>' . $acttext);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					if(@$curlproxy!=''){
						curl_setopt($ch, CURLOPT_PROXY, $curlproxy);
					}
					$cfres = curl_exec($ch);
					if(curl_error($ch) == '')
						curl_close($ch);
				}
			}
		}
		if($emailisallowed){
			addtomailinglist($ordEmail,trim($ordName.' '.$ordLastName));
		}
		print '<?xml version="1.0" encoding="UTF-8"?><notification-acknowledgment xmlns="http://checkout.google.com/schema/2"/>';
	break;
	case 'order-state-change-notification':
		$ordnumber = $nodeList->getValueByTagName('google-order-number');
		$sSQL = "SELECT ordID FROM orders WHERE ordAuthNumber='" . escape_string($ordnumber) . "' AND ordPayProvider=20";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result))
			$ordID=$rs['ordID'];
		else
			$ordID='';
		$financialstate = str_replace("'",'',$nodeList->getValueByTagName('new-financial-order-state'));
		$oldfinancialstate = str_replace("'",'',$nodeList->getValueByTagName('previous-financial-order-state'));
		$fulfillmentstate = str_replace("'",'',$nodeList->getValueByTagName('new-fulfillment-order-state'));
		$oldfulfillmentstate = str_replace("'",'',$nodeList->getValueByTagName('previous-fulfillment-order-state'));
		if($ordID != ''){
			if(@$googleneworderstate=='') $googleneworderstate=3;
			if(@$googlechargedstate=='') $googlechargedstate=4;
			if(@$googledeliveredstate=='') $googledeliveredstate=5;
			if($oldfinancialstate != $financialstate){
				$result = mysql_query("SELECT ordStatus FROM orders WHERE ordID='" . $ordID . "'") or print(mysql_error());
				if($rs = mysql_fetch_assoc($result)) $oldstatus=(int)$rs['ordStatus']; else $oldstatus=999;
				switch($financialstate){
				case "CHARGEABLE":
					if($oldstatus < 3) stock_subtract($ordID);
					mysql_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID=" . $ordID) or print(mysql_error());
					mysql_query("UPDATE orders SET ordStatus=".$googleneworderstate.",ordAuthStatus='',ordStatusDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "' WHERE ordID=" . $ordID) or print(mysql_error());
					do_order_success($ordID,$emailAddr,$sendEmail && ($oldstatus < 3),FALSE,($oldstatus < 3),FALSE,FALSE);
				break;
				case "CHARGING":
				break;
				case "CHARGED":
					if($oldstatus < 3) stock_subtract($ordID);
					mysql_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID=" . $ordID) or print(mysql_error());
					mysql_query("UPDATE orders SET ordStatus=".$googlechargedstate.",ordAuthStatus='',ordStatusDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "' WHERE ordID=" . $ordID) or print(mysql_error());
					do_order_success($ordID,$emailAddr,$sendEmail && ($oldstatus < 3),FALSE,($oldstatus < 3),TRUE,TRUE);
				break;
				case "PAYMENT_DECLINED":
					if($oldstatus >= 3) release_stock($ordID);
					mysql_query("UPDATE orders SET ordStatus=2,ordStatusDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "' WHERE ordID=" . $ordID) or print(mysql_error());
				break;
				case "CANCELLED":
					if($oldstatus >= 3) release_stock($ordID);
					mysql_query("UPDATE orders SET ordStatus=0,ordStatusDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "' WHERE ordID=" . $ordID) or print(mysql_error());
				break;
				case "CANCELLED_BY_GOOGLE":
					if($oldstatus >= 3) release_stock($ordID);
					$sSQL = "SELECT ordStatusInfo FROM orders WHERE ordID=" . $ordID;
					$result = mysql_query($sSQL) or print(mysql_error());
					if($rs = mysql_fetch_array($result)) $currstatusinfo = $rs['ordStatusInfo']; else $currstatusinfo = '';
					mysql_query("UPDATE orders SET ordStatus=0,ordStatusDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "',ordStatusInfo='" . escape_string('Cancelled By Google.' . "\r\n" . $currstatusinfo) . "' WHERE ordID=" . $ordID) or print(mysql_error());
				break;
				}
			}
			if($oldfulfillmentstate != $fulfillmentstate){
				switch($fulfillmentstate){
				case "DELIVERED":
					mysql_query("UPDATE orders SET ordStatus=".$googledeliveredstate.",ordStatusDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "' WHERE ordID=" . $ordID) or print(mysql_error());
				break;
				}
			}
		}
		print '<?xml version="1.0" encoding="UTF-8"?><notification-acknowledgment xmlns="http://checkout.google.com/schema/2"/>';
	break;
	case 'charge-amount-notification':
		print '<?xml version="1.0" encoding="UTF-8"?><notification-acknowledgment xmlns="http://checkout.google.com/schema/2"/>';
	break;
	case 'chargeback-amount-notification':
		$success=TRUE;
		$amount=0;
		$ordID=0;
		$ordnumber = $nodeList->getValueByTagName('google-order-number');
		$sSQL = "SELECT ordID,ordShipping,ordStateTax,ordCountryTax,ordHandling,ordTotal,ordDiscount,ordAuthNumber,ordStatus FROM orders WHERE ordAuthNumber='" . escape_string($ordnumber) . "' AND ordPayProvider=20";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_array($result)){
			$ordID = $rs['ordID'];
			$amount = ($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordTotal']+$rs['ordHandling'])-$rs['ordDiscount'];
			$oldstatus = $rs['ordStatus'];
		}else
			$success = FALSE;
		$refundamount = $nodeList->getValueByTagName('total-chargeback-amount');
		if($success && $amount <= $refundamount){
			if($oldstatus >= 3) release_stock($ordID);
			mysql_query("UPDATE orders SET ordStatus=0,ordStatusDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "' WHERE ordID=" . $ordID);
		}
		print '<?xml version="1.0" encoding="UTF-8"?><notification-acknowledgment xmlns="http://checkout.google.com/schema/2"/>';
	break;
	case 'refund-amount-notification':
		$success=TRUE;
		$amount=0;
		$ordID=0;
		$ordnumber = $nodeList->getValueByTagName('google-order-number');
		$sSQL = "SELECT ordID,ordShipping,ordStateTax,ordCountryTax,ordHandling,ordTotal,ordDiscount,ordAuthNumber,ordStatus FROM orders WHERE ordAuthNumber='" . escape_string($ordnumber) . "' AND ordPayProvider=20";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_array($result)){
			$ordID = $rs['ordID'];
			$amount = ($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordTotal']+$rs['ordHandling'])-$rs['ordDiscount'];
			$oldstatus = $rs['ordStatus'];
		}else
			$success = FALSE;
		$refundamount = $nodeList->getValueByTagName('total-refund-amount');
		if($success && $amount <= $refundamount){
			if($oldstatus >= 3) release_stock($ordID);
			mysql_query("UPDATE orders SET ordStatus=0,ordStatusDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "' WHERE ordID=" . $ordID);
		}
		print '<?xml version="1.0" encoding="UTF-8"?><notification-acknowledgment xmlns="http://checkout.google.com/schema/2"/>';
	break;
	case 'risk-information-notification':
		$ipaddress = '';
		$avs = '';
		$cvv = '';
		$iseligable = '';
		$partialcc = '';
		$acctage = 0;
		$ordnumber = $nodeList->getValueByTagName('google-order-number');
		for($i1 = 0; $i1 < $nodeList->length; $i1++){
			if($nodeList->nodeName[$i1]=='risk-information'){
				$obj2=$nodeList->childNodes[$i1];
				for($i2 = 0; $i2 < $obj2->length; $i2++){
					if($obj2->nodeName[$i2]=='ip-address'){
						$ipaddress = $obj2->nodeValue[$i2];
					}elseif($obj2->nodeName[$i2]=='avs-response'){
						$avs = $obj2->nodeValue[$i2];
					}elseif($obj2->nodeName[$i2]=='cvn-response'){
						$cvv = $obj2->nodeValue[$i2];
					}elseif($obj2->nodeName[$i2]=='buyer-account-age'){
						$acctage = $obj2->nodeValue[$i2];
					}elseif($obj2->nodeName[$i2]=='partial-cc-number'){
						$partialcc = $obj2->nodeValue[$i2];
					}elseif($obj2->nodeName[$i2]=='eligible-for-protection'){
						$iseligable = $obj2->nodeValue[$i2];
						if($iseligable=='false') $iseligable=$xxNo; else $iseligable=$xxYes;
					}
				}
			}
		}
		if($ordnumber != ''){
			$sSQL = "UPDATE orders SET ordIP='" . escape_string($ipaddress) . "',ordAVS='" . escape_string($avs) . "/" . $iseligable . "',ordCVV='" . escape_string($cvv) . '/' . $acctage . "',ordCNum='" . $partialcc . "' WHERE ordAuthNumber='" . $ordnumber . "' AND ordPayProvider=20";
			mysql_query($sSQL);
		}
		print '<?xml version="1.0" encoding="UTF-8"?><notification-acknowledgment xmlns="http://checkout.google.com/schema/2"/>';
	break;
	case 'request-received':
		print '<?xml version="1.0" encoding="UTF-8"?><notification-acknowledgment xmlns="http://checkout.google.com/schema/2"/>';
	break;
	case 'error':
		print '<?xml version="1.0" encoding="UTF-8"?><notification-acknowledgment xmlns="http://checkout.google.com/schema/2"/>';
	break;
	case 'diagnosis':
		print '<?xml version="1.0" encoding="UTF-8"?><notification-acknowledgment xmlns="http://checkout.google.com/schema/2"/>';
	break;
	default:
	}
}
if(@$debugmode==TRUE){
	$htmlemails=FALSE;
	$emlNl="\n";
	$headers = "MIME-Version: 1.0\n";
	$headers .= "From: ".$emailAddr." <".$emailAddr.">\n";
	$headers .= "Content-type: text/plain; charset=".$emailencoding."\n";
	$emailtxt = "ThisMessage: " . $xmlResponse . $emlNl . $emlNl . "Response: " . $responsexml . $emlNl;
	$emailtxt .= "Callback took: " . (microtime_float() - $time_start) . " seconds" . $emlNl;
	$emailtxt .= $debuginfo;
	mail($emailAddr, "gcallback.php debug", $emailtxt, $headers);
}
function getshipstatefromzip(){
	global $shipstate,$destZip;
	$left2zip = strtoupper(substr($destZip, 0, 2));
	$left1zip = strtoupper(substr($destZip, 0, 2));
	if($left2zip=='BT')
		$shipstate='County Antrim';
	elseif($left2zip=='KA')
		$shipstate='Ayrshire';
	elseif($left2zip=='ML')
		$shipstate='Lanarkshire';
	elseif($left2zip=='PA')
		$shipstate='Argyll';
	elseif($left2zip=='HS' || $left2zip=='IV')
		$shipstate='Inverness-shire';
	elseif($left2zip=='KW')
		$shipstate='Caithness';
	elseif($left2zip=='AB')
		$shipstate='Aberdeenshire';
	elseif($left2zip=='ZE')
		$shipstate='Isle of Shetland';
	elseif($left2zip=='DD')
		$shipstate='Perthshire';
	elseif($left2zip=='PH')
		$shipstate='Perthshire';
	elseif($left2zip=='FK')
		$shipstate='West Lothian';
	elseif($left2zip=='KY')
		$shipstate='Fife';
	elseif($left2zip=='EH')
		$shipstate='Edinburgh';
	elseif($left2zip=='TD')
		$shipstate='Roxburghshire';
	elseif($left2zip=='BL' || $left2zip=='WN' || $left2zip=='OL' || $left2zip=='SK')
		$shipstate='Manchester';
	elseif($left2zip=='FY' || $left2zip=='PR' || $left2zip=='BB' || $left2zip=='LA')
		$shipstate='Lancashire';
	elseif($left2zip=='DG')
		$shipstate='Dumfriesshire';
	elseif($left2zip=='CA')
		$shipstate='Cumbria';
	elseif($left2zip=='CH' || $left2zip=='CW' || $left2zip=='WA')
		$shipstate='Cheshire';
	elseif($left2zip=='LL')
		$shipstate='Clwyd';
	elseif($left2zip=='IM')
		$shipstate='Isle of Man';
	elseif($left2zip=='NE' || $left2zip=='SR')
		$shipstate='Tyne and Wear';
	elseif($left2zip=='TS')
		$shipstate='Cleveland';
	elseif($left2zip=='DH' || $left2zip=='DL')
		$shipstate='County Durham';
	elseif($left2zip=='YO' || $left2zip=='HG')
		$shipstate='North Yorkshire';
	elseif($left2zip=='HU')
		$shipstate='East Yorkshire';
	elseif($left2zip=='DN')
		$shipstate='South Yorkshire';
	elseif($left2zip=='LS' || $left2zip=='WF' || $left2zip=='BD' || $left2zip=='HX' || $left2zip=='HD')
		$shipstate='West Yorkshire';
	elseif($left2zip=='DE')
		$shipstate='Derbyshire';
	elseif($left2zip=='LE')
		$shipstate='Leicestershire';
	elseif($left2zip=='CV' || $left2zip=='DY' || $left2zip=='WS' || $left2zip=='WV' || $left2zip=='ST')
		$shipstate='West Midlands';
	elseif($left2zip=='NN')
		$shipstate='Northamptonshire';
	elseif($left2zip=='LU')
		$shipstate='Bedfordshire';
	elseif($left2zip=='MK')
		$shipstate='Buckinghamshire';
	elseif($left2zip=='NR')
		$shipstate='Norfolk';
	elseif($left2zip=='CB' || $left2zip=='PE')
		$shipstate='Cambridgeshire';
	elseif($left2zip=='IP')
		$shipstate='Suffolk';
	elseif($left2zip=='JE' || $left2zip=='GY')
		$shipstate='Channel Islands';
	elseif($left2zip=='LD')
		$shipstate='Powys';
	elseif($left2zip=='SY' || $left2zip=='TF')
		$shipstate='Shropshire';
	elseif($left2zip=='NG')
		$shipstate='Nottinghamshire';
	elseif($left2zip=='OX')
		$shipstate='Oxfordshire';
	elseif($left2zip=='HP' || $left2zip=='AL' || $left2zip=='SG')
		$shipstate='Hertfordshire';
	elseif($left2zip=='KT' || $left2zip=='TW' || $left2zip=='SM' || $left2zip=='CR' || $left2zip=='BR' || $left2zip=='UB' || $left2zip=='HA' || $left2zip=='WC' || $left2zip=='EC' || $left2zip=='SW' || $left2zip=='SE' || $left2zip=='NW' || $left2zip=='EN' || $left2zip=='WD' || $left2zip=='IG' || $left2zip=='RM')
		$shipstate='London';
	elseif($left2zip=='SS' || $left2zip=='CO' || $left2zip=='CM')
		$shipstate='Essex';
	elseif($left2zip=='DA' || $left2zip=='ME' || $left2zip=='TN' || $left2zip=='CT')
		$shipstate='Kent';
	elseif($left2zip=='GU' || $left2zip=='RH')
		$shipstate='Surrey';
	elseif($left2zip=='BN')
		$shipstate='East Sussex';
	elseif($left2zip=='PO')
		$shipstate='Hampshire';
	elseif($left2zip=='SL' || $left2zip=='RG')
		$shipstate='Berkshire';
	elseif($left2zip=='GL')
		$shipstate='Gloucestershire';
	elseif($left2zip=='SP' || $left2zip=='SN')
		$shipstate='Wiltshire';
	elseif($left2zip=='BA')
		$shipstate='Somerset';
	elseif($left2zip=='BS')
		$shipstate='Gloucestershire';
	elseif($left2zip=='TA')
		$shipstate='Somerset';
	elseif($left2zip=='PL' || $left2zip=='TQ' || $left2zip=='EX')
		$shipstate='Devon';
	elseif($left2zip=='SO')
		$shipstate='Hampshire';
	elseif($left2zip=='DT' || $left2zip=='BH')
		$shipstate='Dorset';
	elseif($left2zip=='WR')
		$shipstate='Worcestershire';
	elseif($left2zip=='HR')
		$shipstate='Herefordshire';
	elseif($left2zip=='TR')
		$shipstate='Cornwall';
	elseif($left2zip=='NP')
		$shipstate='Gwent';
	elseif($left2zip=='CF')
		$shipstate='Glamorgan';
	elseif($left2zip=='SA')
		$shipstate='West Glamorgan';
	elseif($left1zip=='G')
		$shipstate='Glasgow';
	elseif($left1zip=='M')
		$shipstate='Manchester';
	elseif($left1zip=='L')
		$shipstate='Merseyside';
	elseif($left1zip=='S')
		$shipstate='South Yorkshire';
	elseif($left1zip=='B')
		$shipstate='West Midlands';
	elseif($left1zip=='E' || $left1zip=='N' || $left1zip=='W')
		$shipstate='London';
	else{
		$debuginfo.='No mapping for ' . $destZip . "\r\n";
		$shipstate='Derbyshire'; // Failsafe, but shouldn't get here.
	}
}
?>