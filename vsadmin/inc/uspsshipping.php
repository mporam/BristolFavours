<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
$gxgtoobig=FALSE;
function sortshippingarray(){
	global $intShipping,$maxshipoptions;
	for($ssaindex2=0; $ssaindex2 < $maxshipoptions; $ssaindex2++){
		$intShipping[$ssaindex2][2] = (double)$intShipping[$ssaindex2][2];
		for($ssaindex=1; $ssaindex < $maxshipoptions; $ssaindex++){
			if(!$intShipping[$ssaindex-1][3] || ($intShipping[$ssaindex][3] && ((double)$intShipping[$ssaindex][2] < (double)$intShipping[$ssaindex-1][2]))){
				$tt = $intShipping[$ssaindex];
				$intShipping[$ssaindex] = $intShipping[$ssaindex-1];
				$intShipping[$ssaindex-1] = $tt;
			}
		}
	}
}
function ParseUSPSXMLOutput($sXML, $international,&$errormsg,&$intShipping){
	global $iTotItems,$xxDay,$xxDays,$dumpshippingxml,$numuspsmeths,$uspsmethods;
	$noError = TRUE;
	$packCost = 0;
	$errormsg = '';
	$xmlDoc = new vrXMLDoc($sXML);
	if($xmlDoc->nodeList->nodeName[0] == 'Error'){ // Top-level Error
		$noError = FALSE;
		$nodeList = $xmlDoc->nodeList->childNodes[0];
		for($i = 0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=='Description'){
				$errormsg = $nodeList->nodeValue[$i];
			}
		}
	}else{ // no Top-level Error
		$nodeList = $xmlDoc->nodeList->childNodes[0];
		for($i = 0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=='Package'){
				$tmpArr = explode('xx', getattributes($nodeList->attributes[$i], 'ID'));
				$quantity = (int)$tmpArr[2];
				$thisService = $tmpArr[0];
				$e = $nodeList->childNodes[$i];
				for($j = 0; $j < $nodeList->childNodes[$i]->length; $j++){
					if($e->nodeName[$j] == 'Error'){ // Lower-level error
						$t = $e->childNodes[$j];
						$errnum=0; $errdesc='';
						for($k = 0; $k < $t->length; $k++){
							if($t->nodeName[$k] == 'Number')
								$errnum=$t->nodeValue[$k];
							elseif($t->nodeName[$k] == 'Description'){
								$errdesc=$t->nodeValue[$k];
								if(@$dumpshippingxml) print 'USPS warning: ' . $t->nodeValue[$k] . '<br>';
							}
						}
						if($errnum=='-2147219497' || $errnum=='-2147219433'){ // Invalid Zip
							$noError = FALSE;
							$errormsg = $errdesc;
						}
					}else{
						if($e->nodeName[$j] == 'Postage'){
							if($international == ''){
								$therate = $e->getValueByTagName('Rate');
								$l = 0;
								while($intShipping[$l][5] != $thisService && $intShipping[$l][5] != '')
									$l++;
								$intShipping[$l][5] = $thisService;
								if($thisService=='PARCEL')
									$intShipping[$l][1] = '2-7 ' . $xxDays;
								elseif($thisService=='EXPRESS')
									$intShipping[$l][1] = 'Overnight to most areas';
								elseif($thisService=='PRIORITY')
									$intShipping[$l][1] = '2-3 ' . $xxDays;
								elseif($thisService=='BPM')
									$intShipping[$l][1] = '2-7 ' . $xxDays;
								elseif($thisService=='Media')
									$intShipping[$l][1] = '2-7 ' . $xxDays;
								elseif($thisService=='FIRST-CLASS')
									$intShipping[$l][1] = '1-3 ' . $xxDays;
								$intShipping[$l][2] = $intShipping[$l][2] + ($therate * $quantity);
								$intShipping[$l][3] = $intShipping[$l][3] + 1;
								for($index2=0;$index2<$numuspsmeths;$index2++){
									if(str_replace('-',' ',$thisService)==str_replace('-',' ',$uspsmethods[$index2][0])){ $intShipping[$l][0] = $uspsmethods[$index2][2]; break; }
								}
							}
						}elseif($e->nodeName[$j] == 'Service'){
							if($international!=''){
								$serviceid = getattributes($e->attributes[$j], 'ID');
								$t = $e->childNodes[$j];
								for($k = 0; $k < $t->length; $k++){
									if($t->nodeName[$k] == 'SvcDescription')
										$SvcDescription = $t->nodeValue[$k];
									elseif($t->nodeName[$k] == 'SvcCommitments')
										$SvcCommitments = $t->nodeValue[$k];
									elseif($t->nodeName[$k] == 'Postage')
										$Postage = $t->nodeValue[$k];
								}
								$l = 0;
								while($intShipping[$l][5]!='' && $intShipping[$l][5]!=$serviceid)
									$l++;
								$intShipping[$l][5] = $serviceid;
								$intShipping[$l][1] = $SvcCommitments;
								$intShipping[$l][2] += ($Postage * $quantity);
								$intShipping[$l][3]++;
								$wantthismethod=FALSE;
								for($index2=0;$index2<$numuspsmeths;$index2++){
									if($serviceid==$uspsmethods[$index2][0]){ $intShipping[$l][0] = $uspsmethods[$index2][2]; $wantthismethod=TRUE; break; }
								}
								if(! $wantthismethod) $intShipping[$l][3]=0;
							}else
								$thisService = $e->nodeValue[$j];
						}
					}
				}
				$packCost = 0;
			}
		}
	}
	return $noError;
}
function checkUPSShippingMeth($method, &$discountsApply, &$showAs){
	global $numuspsmeths, $uspsmethods;
	for($index=0; $index < $numuspsmeths; $index++){
		if($method==$uspsmethods[$index][0]){
			$discountsApply = $uspsmethods[$index][1];
			$showAs = $uspsmethods[$index][2];
			return(TRUE);
		}
	}
	return(FALSE);
}
function ParseUPSXMLOutput($sXML,$international,&$errormsg,&$errorcode,&$intShipping){
	global $xxDay,$xxDays,$upsnegdrates,$origCountryCode,$shipCountryCode;
	$noError = TRUE;
	$errormsg = '';
	$l = 0;
	$discntsApp = '';
	$xmlDoc = new vrXMLDoc($sXML);
	$nodeList = $xmlDoc->nodeList->childNodes[0];
	for($i = 0; $i < $nodeList->length; $i++){
		if($nodeList->nodeName[$i]=='Response'){
			$e = $nodeList->childNodes[$i];
			for($j = 0; $j < $e->length; $j++){
				if($e->nodeName[$j]=='ResponseStatusCode'){
					$noError = ((int)$e->nodeValue[$j])==1;
				}
				if($e->nodeName[$j]=='Error'){
					$errormsg = '';
					$t = $e->childNodes[$j];
					for($k = 0; $k < $t->length; $k++){
						if($t->nodeName[$k]=='ErrorCode'){
							$errorcode = $t->nodeValue[$k];
						}elseif($t->nodeName[$k]=='ErrorSeverity'){
							if($t->nodeValue[$k]=='Transient')
								$errormsg = 'This is a temporary error. Please wait a few moments then refresh this page.<br />' . $errormsg;
						}elseif($t->nodeName[$k]=='ErrorDescription'){
							$errormsg .= $t->nodeValue[$k];
						}
					}
				}
			}
		}elseif($nodeList->nodeName[$i]=='RatedShipment'){ // no Top-level Error
			$wantthismethod=TRUE;
			$nodeList = $xmlDoc->nodeList->childNodes[0];
			$e = $nodeList->childNodes[$i];
			$negotiatedrate = '';
			for($j = 0; $j < $e->length; $j++){
				if($e->nodeName[$j] == 'Service'){ // Lower-level error
					$t = $e->childNodes[$j];
					for($k = 0; $k < $t->length; $k++){
						if($t->nodeName[$k]=='Code'){
							if($t->nodeValue[$k]=='01')
								$intShipping[$l][0] = 'UPS Next Day Air&reg;';
							elseif($t->nodeValue[$k]=='02')
								$intShipping[$l][0] = 'UPS 2nd Day Air&reg;';
							elseif($t->nodeValue[$k]=='03')
								$intShipping[$l][0] = 'UPS Ground';
							elseif($t->nodeValue[$k]=='07')
								$intShipping[$l][0] = 'UPS Worldwide Express&reg;';
							elseif($t->nodeValue[$k]=='08')
								$intShipping[$l][0] = 'UPS Worldwide Expedited&reg;';
							elseif($t->nodeValue[$k]=='11')
								$intShipping[$l][0] = 'UPS Standard';
							elseif($t->nodeValue[$k]=='12')
								$intShipping[$l][0] = 'UPS 3 Day Select&reg;';
							elseif($t->nodeValue[$k]=='13')
								$intShipping[$l][0] = 'UPS Next Day Air Saver&reg;';
							elseif($t->nodeValue[$k]=='14')
								$intShipping[$l][0] = 'UPS Next Day Air&reg; Early A.M.&reg;';
							elseif($t->nodeValue[$k]=='54')
								$intShipping[$l][0] = 'UPS Worldwide Express Plus&reg;';
							elseif($t->nodeValue[$k]=='59')
								$intShipping[$l][0] = 'UPS 2nd Day Air A.M.&reg;';
							elseif($t->nodeValue[$k]=='65'){
								if($origCountryCode=='US' && $shipCountryCode!='US')
									$intShipping[$l][0] = 'UPS Worldwide Saver&reg;';
								else
									$intShipping[$l][0] = 'UPS Express Saver&reg;';
							}
							$wantthismethod = checkUPSShippingMeth($t->nodeValue[$k], $discntsApp, $notUsed);
							$intShipping[$l][4] = $discntsApp;
						}
					}
				}elseif($e->nodeName[$j] == 'TotalCharges'){
					$t = $e->childNodes[$j];
					for($k = 0; $k < $t->length; $k++){
						if($t->nodeName[$k]=='MonetaryValue'){
							$intShipping[$l][2] = (double)$t->nodeValue[$k];
						}
					}
				}elseif($e->nodeName[$j] == 'GuaranteedDaysToDelivery'){
					if(strlen($e->nodeValue[$j]) > 0){
						if($e->nodeValue[$j]=='1')
							$intShipping[$l][1] = '1 ' . $xxDay . $intShipping[$l][1];
						else
							$intShipping[$l][1] = $e->nodeValue[$j] . ' ' . $xxDays . $intShipping[$l][1];
					}
				}elseif($e->nodeName[$j] == 'ScheduledDeliveryTime'){
					if(strlen($e->nodeValue[$j]) > 0){
						$intShipping[$l][1] .= ' by ' . $e->nodeValue[$j];
					}
				}elseif($e->nodeName[$j] == 'NegotiatedRates'){ // Lower-level error
					$t = $e->childNodes[$j];
					$negrate = $t->getValueByTagName('MonetaryValue');
					if($negrate!=null) $negotiatedrate = $negrate;
				}
			}
			if($negotiatedrate!='' && @$upsnegdrates==TRUE){
				$intShipping[$l][2] = (double)$negotiatedrate;
			}
			if($wantthismethod){
				$intShipping[$l][3] = TRUE;
				$l++;
			}else
				$intShipping[$l][1] = '';
			$wantthismethod=TRUE;
		}
	}
	return $noError;
}
function ParseCanadaPostXMLOutput($sXML, $international,&$errormsg,&$errorcode,&$intShipping){
	global $xxDay,$xxDays;
	$noError = TRUE;
	$errormsg = '';
	$discntsApp = '';
	$l = strpos($sXML, ']>');
	if($l > 0) $sXML = substr($sXML, $l+2);
	$l = 0;
	$cphandlingcharge=0;
	$xmlDoc = new vrXMLDoc($sXML);
	$nodeList = $xmlDoc->nodeList->childNodes[0];
	for($i = 0; $i < $nodeList->length; $i++){
		if($nodeList->nodeName[$i]=='error'){
			$noError = FALSE;
			$e = $nodeList->childNodes[$i];
			for($j = 0; $j < $e->length; $j++){
				if($e->nodeName[$j]=='statusCode'){
					$errorcode = $e->nodeValue[$j];
				}elseif($e->nodeName[$j]=='statusMessage'){
					$errormsg = $e->nodeValue[$j];
				}
			}
		}elseif($nodeList->nodeName[$i]=='ratesAndServicesResponse'){ // no Top-level Error
			$wantthismethod=TRUE;
			$nodeList = $xmlDoc->nodeList->childNodes[0];
			$e = $nodeList->childNodes[$i];
			for($j = 0; $j < $e->length; $j++){
				if($e->nodeName[$j] == 'handling')
					$cphandlingcharge = $e->nodeValue[$j];
			}
			for($j = 0; $j < $e->length; $j++){
				if($e->nodeName[$j] == 'product'){
					$wantthismethod = checkUPSShippingMeth(getattributes($e->attributes[$j], 'id'), $discntsApp, $notUsed);
					$intShipping[$l][4] = $discntsApp;
					$wantthismethod=TRUE;
					$t = $e->childNodes[$j];
					for($k = 0; $k < $t->length; $k++){
						if($t->nodeName[$k]=='name'){
							$intShipping[$l][0] = $t->nodeValue[$k];
						}elseif($t->nodeName[$k]=='rate'){
							$intShipping[$l][2] = (double)$t->nodeValue[$k] + (double)$cphandlingcharge;
						}elseif($t->nodeName[$k]=='deliveryDate'){
							$today = getdate();
							$daytoday = $today['yday'];
							if(($ttimeval = strtotime($t->nodeValue[$k])) <= 0){
								$intShipping[$l][1] = $t->nodeValue[$k] . $intShipping[$l][1];
							}else{
								$deldate = getdate($ttimeval);
								$daydeliv = $deldate['yday'];
								if($daydeliv < $daytoday) $daydeliv+=365;
								$intShipping[$l][1] = ($daydeliv - $daytoday) . ' ' . ($daydeliv - $daytoday < 2?$xxDay:$xxDays) . $intShipping[$l][1];
							}
						}elseif($t->nodeName[$k]=='nextDayAM'){
							if($t->nodeValue[$k]=='true')
								$intShipping[$l][1] = $intShipping[$l][1] . ' AM';
						}
					}
					if($wantthismethod){
						$intShipping[$l][3] = TRUE;
						$l++;
					}else
						$intShipping[$l][1] = '';
					$wantthismethod=TRUE;
				}
			}
		}
	}
	return $noError;
}
function getuspscontainer($gpcweight,$ispriority){
	global $packdims;
	$getuspscont='';
	if($ispriority && $gpcweight<=20 && ($packdims[0]<=12.25 && $packdims[1]<=12.25 && $packdims[2]<=6)) $getuspscont='lg flat rate box';
	if($ispriority && $gpcweight<=20 && (($packdims[0]<=11 && $packdims[1]<=8.5 && $packdims[2]<=5.5) || ($packdims[0]<=13.625 && $packdims[1]<=11.875 && $packdims[2]<=3.375))) $getuspscont='flat rate box';
	if($ispriority && $gpcweight<=4 && ($packdims[0]<=8.625 && $packdims[1]<=5.375 && $packdims[2]<=1.625)) $getuspscont='sm flat rate box';
	if($gpcweight<=4 && ($packdims[0]<=12.5 && $packdims[1]<=9.5 && $packdims[2]<=1)) $getuspscont='flat rate envelope';
	if($packdims[0]<=0 || $packdims[1]<=0 || $packdims[2]<=0)$getuspscont='';
	return($getuspscont);
}
function addUSPSDomestic($id,$service,$orig,$dest,$iWeight,$quantity,$container,$size,$machinable){
	global $numuspsmeths,$uspsmethods,$firstclassmailtype,$uspsprioritycontainer,$uspsexpresscontainer,$packdims,$adminUnits;
	$sXML='';
	$pounds = (int)$iWeight;
	$ounces = round(($iWeight-$pounds)*16.0);
	if($pounds==0 && $ounces==0) $ounces=1;
	if(($adminUnits & 12)!=0){
		$quantity *= splitlargepacks();
		$totaldims = $packdims[0] + (2 * ($packdims[1] + $packdims[2]));
		if($totaldims > 84) $size = 'LARGE';
		if($totaldims > 108) $size = 'OVERSIZE';
	}
	for($index=0;$index<$numuspsmeths;$index++){
		$packsize=$size;
		$sXML .= '<Package ID="' . str_replace(' ','-',$uspsmethods[$index][0]) . 'xx' . $id . 'xx' . $quantity . '">';
		$sXML .= '<Service>' . $uspsmethods[$index][0] . '</Service>';
		if($uspsmethods[$index][0]=='FIRST CLASS') $sXML .= '<FirstClassMailType>' . (@$firstclassmailtype!='' ? $firstclassmailtype : 'PARCEL') . '</FirstClassMailType>';
		$sXML .= '<ZipOrigination>' . $orig . '</ZipOrigination><ZipDestination>' . substr($dest, 0, 5) . '</ZipDestination>';
		$sXML .= '<Pounds>' . $pounds . '</Pounds><Ounces>' . $ounces . '</Ounces>';
		$thecontainer = $container;
		if($uspsmethods[$index][0]=='PRIORITY'){
			if(($adminUnits & 12)!=0){
				if((($packdims[0] * $packdims[1] * $packdims[2]) > 1728) && $packsize=='REGULAR') $packsize='LARGE';
				if(@$uspsprioritycontainer=='auto') $uspsprioritycontainer=getuspscontainer($iWeight,TRUE);
			}
			if(@$uspsprioritycontainer=='' || @$uspsprioritycontainer=='auto') $thecontainer=($packsize=='LARGE'?'rectangular':''); else $thecontainer = $uspsprioritycontainer;
		}
		if($uspsmethods[$index][0]=='EXPRESS'){
			if(($adminUnits & 12)!=0 && @$uspsexpresscontainer=='auto') $uspsexpresscontainer=getuspscontainer($iWeight,FALSE);
			if(@$uspsexpresscontainer=='' || @$uspsexpresscontainer=='auto') $thecontainer=''; else $thecontainer = $uspsexpresscontainer;
		}
		$sXML .= '<Container>' . $thecontainer . '</Container><Size>' . $packsize . '</Size>';
		if((($adminUnits & 12)!=0) && $packdims[0] > 0 && $packdims[1] > 0 && $packdims[2] > 0) $sXML .= '<Width>' . round($packdims[1],1) . '</Width><Length>' . round($packdims[0],1) . '</Length><Height>' . round($packdims[2],1) . '</Height>';
		$sXML .= '<Machinable>' . $machinable . '</Machinable></Package>';
	}
	return $sXML;
}
function addUSPSInternational($id,$iWeight,$quantity,$mailtype,$country){
	global $packdims,$numuspsmeths,$uspsmethods,$adminUnits,$shipCountryCode,$gxgtoobig;
	if(($adminUnits & 12)!=0){
		$lenplusgirth = $packdims[0] + (2 * ($packdims[1] + $packdims[2]));
		for($xx=0; $xx < $numuspsmeths; $xx++){
			if($shipCountryCode=='AD' || $shipCountryCode=='AT' || $shipCountryCode=='BE' || $shipCountryCode=='CH' || $shipCountryCode=='CN' || $shipCountryCode=='CZ' || $shipCountryCode=='DE' || $shipCountryCode=='DK' || $shipCountryCode=='ES' || $shipCountryCode=='FI' || $shipCountryCode=='FR' || $shipCountryCode=='GR' || $shipCountryCode=='HK' || $shipCountryCode=='IE' || $shipCountryCode=='IT' || $shipCountryCode=='JP' || $shipCountryCode=='LI' || $shipCountryCode=='LU' || $shipCountryCode=='MC' || $shipCountryCode=='MT' || $shipCountryCode=='NL' || $shipCountryCode=='NO' || $shipCountryCode=='PT' || $shipCountryCode=='SE' || $shipCountryCode=='VA'){
				if($packdims[0]>60 || $lenplusgirth>108){ // Express Mail
					if($uspsmethods[$xx][0]=='1') $uspsmethods[$xx][0]='xxx';
				}
			}elseif($shipCountryCode=='CA'){
				if($packdims[0]>42 || $lenplusgirth>79){
					if($uspsmethods[$xx][0]=='1') $uspsmethods[$xx][0]='xxx';
				}
			}else{
				if($packdims[0]>36 || $lenplusgirth>79){
					if($uspsmethods[$xx][0]=='1') $uspsmethods[$xx][0]='xxx';
				}
			}
			if($shipCountryCode=='CA' || $shipCountryCode=='HK'){ // Priority Mail
				if($lenplusgirth>108){
					if($uspsmethods[$xx][0]=='2') $uspsmethods[$xx][0]='xxx';
				}
			}elseif($shipCountryCode=='AD' || $shipCountryCode=='AT' || $shipCountryCode=='BE' || $shipCountryCode=='CH' || $shipCountryCode=='CZ' || $shipCountryCode=='DE' || $shipCountryCode=='DK' || $shipCountryCode=='ES' || $shipCountryCode=='FI' || $shipCountryCode=='FR' || $shipCountryCode=='GI' || $shipCountryCode=='GB' || $shipCountryCode=='GR' || $shipCountryCode=='IE' || $shipCountryCode=='IT' || $shipCountryCode=='JP' || $shipCountryCode=='LI' || $shipCountryCode=='LU' || $shipCountryCode=='MC' || $shipCountryCode=='MT' || $shipCountryCode=='NL' || $shipCountryCode=='NO' || $shipCountryCode=='NZ' || $shipCountryCode=='PL' || $shipCountryCode=='PT' || $shipCountryCode=='SE' || $shipCountryCode=='VA'){
				if($packdims[0]>60 || $lenplusgirth>108){
					if($uspsmethods[$xx][0]=='2') $uspsmethods[$xx][0]='xxx';
				}
			}else{
				if($packdims[0]>42 || $lenplusgirth>79){
					if($uspsmethods[$xx][0]=='2') $uspsmethods[$xx][0]='xxx';
				}
			}
			if($packdims[0]>46 || $packdims[1]>46 || $packdims[2]>35 || $lenplusgirth>108){
				$gxgtoobig=TRUE;
				if($uspsmethods[$xx][0]=='4' || $uspsmethods[$xx][0]=='6' || $uspsmethods[$xx][0]=='7') $uspsmethods[$xx][0]='xxx'; // GXG
			}
			if($packdims[0]>12.5 || $packdims[1]>9.5 || $packdims[2]>1){
				if($uspsmethods[$xx][0]=='8' || $uspsmethods[$xx][0]=='10') $uspsmethods[$xx][0]='xxx'; // FRE
			}
			if(($packdims[0]>11 || $packdims[1]>8.5 || $packdims[2]>5.5) && ($packdims[0]>13.625 || $packdims[1]>11.875 || $packdims[2]<=3.375)){
				if($uspsmethods[$xx][0]=='9') $uspsmethods[$xx][0]='xxx'; // FRB
			}
			if($packdims[0]>12.25 || $packdims[1]>12.25 || $packdims[2]>6){
				if($uspsmethods[$xx][0]=='11') $uspsmethods[$xx][0]='xxx'; // LFRB
			}
			if($packdims[0]>11.5 || $packdims[1]>6.125 || $packdims[2]>0.25){
				if($uspsmethods[$xx][0]=='13') $uspsmethods[$xx][0]='xxx'; // FirstClass Letter
			}
			if($packdims[0]>15 || $packdims[1]>12 || $packdims[2]>0.75){
				if($uspsmethods[$xx][0]=='14') $uspsmethods[$xx][0]='xxx'; // FirstClass L-E
			}
			if($packdims[0]>24 || ($packdims[0]+$packdims[1]+$packdims[2])>36){
				if($uspsmethods[$xx][0]=='15') $uspsmethods[$xx][0]='xxx'; // FirstClass Package
			}
		}
	}
	$pounds = (int)$iWeight;
	$ounces = round(($iWeight-$pounds)*16.0);
	if($pounds==0 && $ounces==0) $ounces=1;
	$sXML = '<Package ID="xx' . $id . 'xx' . $quantity . '"><Pounds>' . $pounds . '</Pounds><Ounces>' . $ounces . '</Ounces><MailType>' . $mailtype . '</MailType>';
	if(! $gxgtoobig && ($adminUnits & 12)!=0 && ceil($packdims[0])>0 && ceil($packdims[1])>0 && ceil($packdims[2])>0) $sXML .= '<GXG><Length>' . max(ceil($packdims[0]),10) . '</Length><Width>' . ceil($packdims[2]) . '</Width><Height>' . max(ceil($packdims[1]),6) . '</Height><POBoxFlag>N</POBoxFlag><GiftFlag>N</GiftFlag></GXG>';
	$sXML .= '<Country>' . $country . '</Country>';
	return $sXML . '</Package>';
}
function addUPSInternational($iWeight,$adminUnits,$packTypeCode,$country,$packcost,&$dimens){
	global $addshippinginsurance,$countryCurrency,$adminUnits,$payproviderpost,$wantinsurance_,$signatureoption;
	if($iWeight<0.1) $iWeight=0.1;
	$sXML = '<Package><PackagingType><Code>' . $packTypeCode . '</Code><Description>Package</Description></PackagingType>';
	if($dimens[0] > 0 && $dimens[1] > 0 && $dimens[2] > 0) $sXML .= '<Dimensions><Length>' . round($dimens[0],0) . '</Length><Width>' . round($dimens[1],0) . '</Width><Height>' . round($dimens[2],0) . '</Height><UnitOfMeasurement><Code>' . (($adminUnits & 12)==4 ? 'IN' : 'CM') . '</Code></UnitOfMeasurement></Dimensions>';
	$sXML .= '<Description>Rate Shopping</Description><PackageWeight><UnitOfMeasurement><Code>' . (($adminUnits & 1)==1 ? 'LBS' : 'KGS') . '</Code></UnitOfMeasurement><Weight>' . $iWeight . '</Weight></PackageWeight><PackageServiceOptions>';
	if(abs(@$addshippinginsurance)==1 || (abs(@$addshippinginsurance)==2 && $wantinsurance_)){
		if($packcost > 50000) $packcost=50000;
		$sXML .= '<InsuredValue><CurrencyCode>' . $countryCurrency . '</CurrencyCode><MonetaryValue>' . number_format($packcost,2,'.','') . '</MonetaryValue></InsuredValue>';
	}
	if($payproviderpost != ''){
		if((int)$payproviderpost==@$codpaymentprovider) $sXML .= '<COD><CODFundsCode>0</CODFundsCode><CODCode>3</CODCode><CODAmount><CurrencyCode>'. $countryCurrency . '</CurrencyCode><MonetaryValue>' . number_format($packcost,2,'.','') . '</MonetaryValue></CODAmount></COD>';
	}
	if(@$signatureoption=='indirect')
		$sXML .= '<DeliveryConfirmation><DCISType>1</DCISType></DeliveryConfirmation>';
	elseif(@$signatureoption=='direct')
		$sXML .= '<DeliveryConfirmation><DCISType>2</DCISType></DeliveryConfirmation>';
	elseif(@$signatureoption=='adult')
		$sXML .= '<DeliveryConfirmation><DCISType>3</DCISType></DeliveryConfirmation>';
	return $sXML . '</PackageServiceOptions></Package>';
}
function addCanadaPostPackage($iWeight,$adminUnits,$packTypeCode,$country,$packcost,&$dimens){
	global $addshippinginsurance,$packtogether;
	if($iWeight<0.1) $iWeight=0.1;
	if($packtogether) $thesize = 1; else $thesize = 19;
	if($dimens[0]==0) $dimens[0] = $thesize;
	if($dimens[1]==0) $dimens[1] = $thesize;
	if($dimens[2]==0) $dimens[2] = $thesize;
	$tmpXML = '<item><quantity> 1 </quantity><weight> ' . $iWeight . ' </weight><length> '.$dimens[0].' </length><width> '.$dimens[1].' </width><height> '.$dimens[2].' </height><description> Goods for shipping rates selection </description><readyToShip/></item>';
	return $tmpXML;
}
function addFedexPackage($iWeight,$packcost,&$dimens){
	global $adminUnits,$addshippinginsurance,$wantinsurance_,$allowsignaturerelease,$signaturerelease_,$signatureoption,$ordPayProvider,$codpaymentprovider,$countryCurrency;
	$tmpXML = '<v9:RequestedPackageLineItems>';
	if($iWeight < 0.1) $iWeight=0.1;
	if(abs(@$addshippinginsurance)==1 || (abs(@$addshippinginsurance)==2 && $wantinsurance_)){
		$tmpXML .= '<v9:InsuredValue><v9:Currency>' . $countryCurrency . '</v9:Currency><v9:Amount>' . number_format($packcost,2,'.','') . '</v9:Amount></v9:InsuredValue>';
	}
	$tmpXML .= '<v9:Weight><v9:Units>' . (($adminUnits & 1)==1 ? 'LB' : 'KG') . '</v9:Units><v9:Value>' . number_format($iWeight,1,'.','') . '</v9:Value></v9:Weight>';
	if($dimens[0] > 0 && $dimens[1] > 0 && $dimens[2] > 0) $tmpXML .= '<v9:Dimensions><v9:Length>' . round($dimens[0],0) . '</v9:Length><v9:Width>' . round($dimens[1],0) . '</v9:Width><v9:Height>' . round($dimens[2],0) . '</v9:Height><v9:Units>' . (($adminUnits & 12)==4 ? 'IN' : 'CM') . '</v9:Units></v9:Dimensions>';
	$tmpXML .= '<v9:SpecialServicesRequested>';
	if($signaturerelease_ && @$allowsignaturerelease==TRUE){
	}elseif(@$signatureoption=='indirect')
		$tmpXML .= '<v9:SpecialServiceTypes>SIGNATURE_OPTION</v9:SpecialServiceTypes>';
	elseif(@$signatureoption=='direct')
		$tmpXML .= '<v9:SpecialServiceTypes>SIGNATURE_OPTION</v9:SpecialServiceTypes>';
	elseif(@$signatureoption=='adult')
		$tmpXML .= '<v9:SpecialServiceTypes>SIGNATURE_OPTION</v9:SpecialServiceTypes>';
	elseif(@$signatureoption=='none')
		$tmpXML .= '<v9:SpecialServiceTypes>SIGNATURE_OPTION</v9:SpecialServiceTypes>';
	if(@$nonstandardcontainer==TRUE) $tmpXML .= '<v9:SpecialServiceTypes>NON_STANDARD_CONTAINER</v9:SpecialServiceTypes>';
	if(@$dryice==TRUE) $tmpXML .= '<v9:SpecialServiceTypes>DRY_ICE</v9:SpecialServiceTypes><v9:DryIceWeight><v9:Units>KG</v9:Units><v9:Value>5</v9:Value></v9:DryIceWeight>';
	if(@$dangerousgoods==TRUE) $tmpXML .= '<v9:SpecialServiceTypes>DANGEROUS_GOODS</v9:SpecialServiceTypes><v9:DangerousGoodsDetail><v9:Accessibility>ACCESSIBLE</v9:Accessibility><v9:CargoAircraftOnly>1</v9:CargoAircraftOnly></v9:DangerousGoodsDetail>';
	if(@$ordPayProvider!=''){
		if((int)$ordPayProvider==$codpaymentprovider) $tmpXML .= '<v9:SpecialServiceTypes>COD</v9:SpecialServiceTypes><v9:CodDetail><v9:CodCollectionAmount><v9:Currency>CAD</v9:Currency><v9:Amount>XXXFEDEXGRANDTOTXXX</v9:Amount></v9:CodCollectionAmount><v9:CollectionType>ANY</v9:CollectionType></v9:CodDetail>';
	}
	if($signaturerelease_ && @$allowsignaturerelease==TRUE){
	}elseif(@$signatureoption=='indirect')
		$tmpXML .= '<v9:SignatureOptionDetail><v9:OptionType>INDIRECT</v9:OptionType></v9:SignatureOptionDetail>';
	elseif(@$signatureoption=='direct')
		$tmpXML .= '<v9:SignatureOptionDetail><v9:OptionType>DIRECT</v9:OptionType></v9:SignatureOptionDetail>';
	elseif(@$signatureoption=='adult')
		$tmpXML .= '<v9:SignatureOptionDetail><v9:OptionType>ADULT</v9:OptionType></v9:SignatureOptionDetail>';
	elseif(@$signatureoption=='none')
		$tmpXML .= '<v9:SignatureOptionDetail><v9:OptionType>NO_SIGNATURE_REQUIRED</v9:OptionType></v9:SignatureOptionDetail>';
	$tmpXML .= '</v9:SpecialServicesRequested>';
	return($tmpXML . '</v9:RequestedPackageLineItems>');
}
function USPSCalculate($sXML,$international,&$errormsg,&$intShipping){
	global $usecurlforfsock,$pathtocurl,$curlproxy,$destZip,$xxPlsZip,$maxshipoptions,$dumpshippingxml;
	$success = TRUE;
	if($destZip==''){
		$errormsg=$xxPlsZip;
		return(FALSE);
	}
	$sXML = 'API=' . $international . 'Rate' . ($international=='' ? 'V3' : '') . '&XML=' . $sXML;
	if(@$usecurlforfsock){
		$success = callcurlfunction('http://production.shippingapis.com/ShippingAPI.dll', $sXML, $res, '', $errormsg, FALSE);
	}else{
		$header = "POST /ShippingAPI.dll HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= 'Content-Length: ' . strlen($sXML) . "\r\n\r\n";
		$fp = @fsockopen('production.shippingapis.com', 80, $errno, $errstr, 30);
		if(!$fp){
			$errormsg = $errstr.' ('.$errno.')';
			return FALSE;
		}else{
			$res = '';
			fputs ($fp, $header . $sXML);
			while (!feof($fp)) {
				$res .= fgets ($fp, 1024);
			}
			fclose ($fp);
		}
	}
	if($success){
		if(@$dumpshippingxml) dumpxmloutput($sXML,$res);
		$success = ParseUSPSXMLOutput($res, $international,$errormsg,$intShipping);
		for($ind1=0; $ind1 < $maxshipoptions; $ind1++){
			for($ind2=$ind1+1; $ind2 < $maxshipoptions; $ind2++){
				if($intShipping[$ind1][3]!=0 && $intShipping[$ind2][3]!=0 && $intShipping[$ind1][5]==$intShipping[$ind2][5] && $intShipping[$ind2][5]!=''){
					if((double)$intShipping[$ind1][2]<(double)$intShipping[$ind2][2]) $intShipping[$ind2][3]=0; else $intShipping[$ind1][3]=0;
				}
			}
		}
		sortshippingarray();
	}
	return $success;
}
function UPSCalculate($sXML,$international,&$errormsg, &$intShipping){
	global $pathtocurl,$curlproxy,$xxPlsZip,$upstestmode,$dumpshippingxml;
	if(@$upstestmode==TRUE){ print 'UPS Test Mode<br />'; $upsurl='wwwcie.ups.com'; }else $upsurl='www.ups.com';
	if($success = callcurlfunction('https://'.$upsurl.'/ups.app/xml/Rate', $sXML, $res, '', $errormsg, FALSE)){
		if(@$dumpshippingxml) dumpxmloutput($sXML,$res);
		$success = ParseUPSXMLOutput($res, $international,$errormsg,$errorcode,$intShipping);
		sortshippingarray();
		if($errorcode == 111210) $errormsg = 'The destination zip / postal code is invalid.';
		if($errorcode == 110971) $errormsg = ''; // May differ from published rates.
		if($errorcode == 119070) $errormsg = ''; // Large package surcharge.
	}
	return $success;
}

function CanadaPostCalculate($sXML,$international,&$errormsg,&$intShipping){
	global $pathtocurl,$usecurlforfsock,$curlproxy,$destZip,$xxPlsZip,$dumpshippingxml;
	$success = true;
	if($destZip==''){
		$errormsg=$xxPlsZip;
		return(FALSE);
	}
	if(@$usecurlforfsock){
		$success = callcurlfunction('sellonline.canadapost.ca:30000', $sXML, $res, '', $errormsg, FALSE);
	}else{
		$header = "POST / HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= 'Content-Length: ' . strlen($sXML) . "\r\n\r\n";
		$fp = @fsockopen('sellonline.canadapost.ca', 30000, $errno, $errstr, 30);
		if (!$fp){
			$errormsg = $errstr.' ('.$errno.')';
			return FALSE;
		}else{
			$res = '';
			fputs ($fp, $header . $sXML);
			while (!feof($fp)) {
				$res .= fgets ($fp, 1024);
			}
			fclose ($fp);
		}
	}
	if(@$dumpshippingxml) dumpxmloutput($sXML,$res);
	if($success){
		$success = ParseCanadaPostXMLOutput($res, $international,$errormsg,$errorcode,$intShipping);
		sortshippingarray();
	}
	return $success;
}
function parsefedexXMLoutput($sXML, $international, &$errormsg, &$errorcode, &$intShipping){
	global $xxDay,$xxDays,$uselistshippingrates,$commercialloc_,$origCountryCode,$shipCountryCode,$nofedexinternationalground,$fedextestmode,$fedexnamespace;
	$noError = TRUE;
	$errormsg = '';
	$discntsApp = '';
	$l = strpos($sXML, ']>');
	if($l > 0) $sXML = substr($sXML, $l+2);
	$l = 0;
	$fns=$fedexnamespace; // if(@$fedextestmode) $fns='v9'; else $fns='ns';
	$xmlDoc = new vrXMLDoc($sXML);
	$nodeList = $xmlDoc->nodeList->childNodes[0];
	for($i = 0; $i < $nodeList->length; $i++){
		if($nodeList->nodeName[$i]=='soapenv:Body'){
			$nodeList=$nodeList->childNodes[$i];
		}
	}
	for($i = 0; $i < $nodeList->length; $i++){
		if($nodeList->nodeName[$i]==$fns.':RateReply'){
			$nodeList=$nodeList->childNodes[$i];
		}
	}
	for($i = 0; $i < $nodeList->length; $i++){
		if($nodeList->nodeName[$i]==$fns.':HighestSeverity'){
			if($nodeList->nodeValue[$i]=='ERROR'){
				$noError = FALSE;
				$e = $nodeList->childNodes[$i];
				for($j = 0; $j < $e->length; $j++){
					if($e->nodeName[$j]==$fns.':Message'){
						$errormsg = $e->nodeValue[$j];
					}elseif($e->nodeName[$j]==$fns.':Code'){
						$errorcode = $e->nodeValue[$j];
					}
				}
			}
		}elseif($nodeList->nodeName[$i]==$fns.':Notifications'){
			$iserror=FALSE;
			$themessage='';
			$thecode='';
			$e = $nodeList->childNodes[$i];
			for($j = 0; $j < $e->length; $j++){
				if($e->nodeName[$j]==$fns.':Message'){
					$themessage = $e->nodeValue[$j];
				}elseif($e->nodeName[$j]==$fns.':Code'){
					$thecode = $e->nodeValue[$j];
				}elseif($e->nodeName[$j]==$fns.':Severity'){
					$iserror = $e->nodeValue[$j]=='ERROR';
				}
			}
			if($iserror){
				$errormsg = $themessage;
				$errorcode = $thecode;
			}
		}elseif($nodeList->nodeName[$i]==$fns.':RateReplyDetails'){
			$wantthismethod=FALSE;
			$e = $nodeList->childNodes[$i];
			$entryweight = $e->getValueByTagName('BilledWeight');
			for($j = 0; $j < $e->length; $j++){
				if($e->nodeName[$j] == $fns.':ServiceType'){
					$theservicename = str_replace('_','',$e->nodeValue[$j]);
					$wantthismethod = checkUPSShippingMeth($theservicename, $discntsApp, $showAs);
					// if($e->nodeValue[$j]=='FEDEXGROUND' && $shipCountryCode!='CA' && $shipCountryCode!='PR' && !$commercialloc_ && $entryweight<=70.0) $wantthismethod=FALSE;
					if($origCountryCode!=$shipCountryCode){
						// if(strpos($showAs,'FedEx Ground')!==FALSE && @$nofedexinternationalground==TRUE) $wantthismethod=FALSE;
						$showAs = str_replace('FedEx Ground', 'FedEx International Ground', $showAs);
					}
					if($wantthismethod){
						$intShipping[$l][0] = $showAs;
						$intShipping[$l][4] = $discntsApp;
					}
				}elseif($e->nodeName[$j]==$fns.':RatedShipmentDetails'){
					$t = $e->childNodes[$j];
					for($k = 0; $k < $t->length; $k++){
						if($t->nodeName[$k]==$fns.':ShipmentRateDetail'){
							$intShipping[$l][2] = 0;
							$u = $t->childNodes[$k];
							for($kk = 0; $kk < $u->length; $kk++){
								if($u->nodeName[$kk]==$fns.':TotalNetFedExCharge'){
									$intShipping[$l][2] += (double)$u->childNodes[$kk]->getValueByTagName($fns.':Amount');
								}elseif($u->nodeName[$kk]=='TotalDiscount'){
									// if(@$uselistshippingrates==TRUE) $intShipping[$l][2] += (double)$u->nodeValue[$kk];
								}
							}
						}
					}
				}elseif($e->nodeName[$j]==$fns.':DeliveryTimestamp'){
					$today = getdate();
					$daytoday = $today['yday'];
					if(($ttimeval = strtotime($e->nodeValue[$j])) < 0){
						$intShipping[$l][1] = $e->nodeValue[$j] . $intShipping[$l][1];
					}else{
						$deldate = getdate($ttimeval);
						$daydeliv = $deldate['yday'];
						if($daydeliv < $daytoday) $daydeliv+=365;
						for($index=0; $index<=($daydeliv-$daytoday); $index++){
							$ckwekday=getdate(time()+60*60*24*$index);
							if($ckwekday['wday']==0 || $ckwekday['wday']==6) $daydeliv+=1;
						}
						$intShipping[$l][1] = ($daydeliv - $daytoday) . ' ' . ($daydeliv - $daytoday < 2?$xxDay:$xxDays) . $intShipping[$l][1];
					}
				}
			}
			if($wantthismethod){
				$intShipping[$l][3] = TRUE;
				$l++;
			}else
				$intShipping[$l][1] = '';
		}
	}
	return $noError;
}
function fedexcalculate($sXML,$international, &$errormsg, &$intShipping){
	global $destZip,$xxPlsZip,$payproviderpost,$dumpshippingxml,$fedexurl,$fedexnamespace;
	if($destZip==''){
		$errormsg=$xxPlsZip;
		return(FALSE);
	}
	if($success = callcurlfunction($fedexurl, $sXML, $xmlres, '', $errormsg, FALSE)){
		if(@$dumpshippingxml) dumpxmloutput($sXML,$xmlres);
		$pattern = '/<(.{1,3}):RateReply/';
		preg_match($pattern, $xmlres, $matches);
		$fedexnamespace=$matches[1];
		$success = parsefedexXMLoutput($xmlres, $international, $errormsg, $errorcode, $intShipping);
	}
	if($success) sortshippingarray();
	return $success;
}
function dumpxmloutput($sentxml,$recvdxml){
	print str_replace('<','<br />&lt;',str_replace('</','&lt;/',$sentxml)) . "<br />\n";
	print str_replace('<','<br />&lt;',str_replace('</','&lt;/',$recvdxml)) . "<br />\n";
}
?>