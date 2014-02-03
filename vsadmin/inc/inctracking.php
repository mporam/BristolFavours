<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$_SERVER['CONTENT_LENGTH'] != '' && $_SERVER['CONTENT_LENGTH'] > 10000) exit;
$alreadygotadmin = getadminsettings();
$incupscopyright=FALSE;
$incfedexcopyright=FALSE;
$alternateratesusps=FALSE;
$alternateratesups=FALSE;
$alternateratesfedex=FALSE;
if($adminAltRates>0){
	$sSQL = "SELECT altrateid FROM alternaterates WHERE usealtmethod<>0 OR usealtmethodintl<>0";
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs = mysql_fetch_assoc($result)){
		if($rs['altrateid']==3) $alternateratesusps=TRUE;
		if($rs['altrateid']==4) $alternateratesups=TRUE;
		if($rs['altrateid']==7 || $rs['altrateid']==8) $alternateratesfedex=TRUE;
	}
	mysql_free_result($result);
}
if(@$_REQUEST['carrier'] != '')
	$theshiptype=$_REQUEST['carrier'];
else{
	$possshiptypes=0;
	if(@$defaulttrackingcarrier!='') $theshiptype=$defaulttrackingcarrier; else $theshiptype='ups';
	if($shipType==3 || $alternateratesusps || strpos(strtolower(@$trackingcarriers), 'usps')!==FALSE){
		$theshiptype='usps';
		$possshiptypes++;
	}
	if(@$shipType==4 || $alternateratesups || strpos(strtolower(@$trackingcarriers), 'ups')!==FALSE){
		$theshiptype='ups';
		$incupscopyright=TRUE;
		$possshiptypes++;
	}
	if($shipType==7 || $shipType==8 || $alternateratesfedex || strpos(strtolower(@$trackingcarriers), 'fedex')!==FALSE){
		$theshiptype='fedex';
		$incfedexcopyright=TRUE;
		$possshiptypes++;
	}
	if($possshiptypes>1) $theshiptype='undecided';
}
?>
<script language="javascript" type="text/javascript">
<!--
function viewlicense()
{
	var prnttext = '<html><head><STYLE TYPE="text/css">A:link {COLOR: #333333; TEXT-DECORATION: none}A:visited {COLOR: #333333; TEXT-DECORATION: none}A:active {COLOR: #333333; TEXT-DECORATION: none}A:hover {COLOR: #f39000; TEXT-DECORATION: none}TD {FONT-FAMILY: Verdana;}P {FONT-FAMILY: Verdana;}HR {color: #637BAD;height: 1px;}</STYLE></head><body><table width="100%" border="0" cellspacing="1" cellpadding="3">\n';
	prnttext += '<tr><td colspan="2" align="center"><a href="javascript:window.close()"><strong>Close Window</strong></a></td></tr>';
	prnttext += '<tr><td width="40"><img src="images/LOGO_S.gif"  alt="UPS" /></td><td><p><span style="font-size:16px;font-family:Verdana;font-weight:bold">UPS Tracking Terms and Conditions</span></p></td></tr>';
	prnttext += '<tr><td colspan="2"><p><span style="font-size:12px;font-family:Verdana">The UPS package tracking systems accessed via this Web Site (the &quot;Tracking Systems&quot;) and tracking information obtained through this Web Site (the &quot;Information&quot;) are the private property of UPS. UPS authorizes you to use the Tracking Systems solely to track shipments tendered by or for you to UPS for delivery and for no other purpose. Without limitation, you are not authorized to make the Information available on any web site or otherwise reproduce, distribute, copy, store, use or sell the Information for commercial gain without the express written consent of UPS. This is a personal service, thus your right to use the Tracking Systems or Information is non-assignable. Any access or use that is inconsistent with these terms is unauthorized and strictly prohibited.</span></p></td></tr>';
	prnttext += '<tr><td colspan="2" align="center"><hr /><span style="font-size:10px;font-family:Verdana"><?php print str_replace("'","\'",$xxUPStm)?></span></td></tr>';
	prnttext += '<tr><td colspan="2" align="center">&nbsp;<br /><a href="javascript:window.close()"><strong>Close Window</strong></a></td></tr>';
	prnttext += '</table></body></html>';
	var newwin = window.open("","viewlicense",'menubar=no, scrollbars=yes, width=500, height=420, directories=no,location=no,resizable=yes,status=no,toolbar=no');
	newwin.document.open();
	newwin.document.write(prnttext);
	newwin.document.close();
}
function checkaccept()
{
  if (document.trackform.agreeconds.checked == false)
  {
    alert("Please note: To track your package(s), you must accept the UPS Tracking Terms and Conditions by selecting the checkbox below.");
    return (false);
  }else{
	document.trackform.submit();
  }
  return (true);
}
//-->
</script>
<?php
if($theshiptype=="ups"){
?>
&nbsp;<br />
	<form method="post" name="trackform" action="tracking.php">
	<input type="hidden" name="carrier" value="ups" />
      <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr>
		  <td class="cobll" colspan="2">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
			  <tr>
				<td width="40"><img src="images/LOGO_S.gif" alt="UPS" /></td><td align="center">&nbsp;<br /><span style="font-size:18px;font-family:Verdana;font-weight:bold">UPS OnLine Tools&reg; Tracking</span><br />&nbsp;</td><td width="40">&nbsp;</td>
			  </tr>
			</table>
		  </td>
		</tr>
<?php
function getAddress($u, &$theAddress){
	$signedby = "";
	for($l = 0;$l < $u->length; $l++){
		//print "AddName : " . $u->nodeName[$l] . ", AddVal : " . $u->nodeValue[$l] . "<br />";
		if($u->nodeName[$l] == "AddressLine1")
			$addressline1 = $u->nodeValue[$l];
		elseif($u->nodeName[$l] == "AddressLine2")
			$addressline2 = $u->nodeValue[$l];
		elseif($u->nodeName[$l] == "AddressLine3")
			$addressline3 = $u->nodeValue[$l];
		elseif($u->nodeName[$l] == "City")
			$city = $u->nodeValue[$l];
		elseif($u->nodeName[$l] == "StateProvinceCode")
			$statecode = $u->nodeValue[$l];
		elseif($u->nodeName[$l] == "PostalCode")
			$postcode = $u->nodeValue[$l];
		elseif($u->nodeName[$l] == "CountryCode"){
			$sSQL = "SELECT countryName FROM countries WHERE countryCode='" . $u->nodeValue[$l] . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result) > 0){
				$rs = mysql_fetch_assoc($result);
				$countrycode = $rs["countryName"];
			}else
				$countrycode = $u->nodeValue[$l];
			mysql_free_result($result);
		}
	}
	$theAddress = "";
	if(@$addressline1 != "") $theAddress .= $addressline1 . "<br />";
	if(@$addressline2 != "") $theAddress .= $addressline2 . "<br />";
	if(@$addressline3 != "") $theAddress .= $addressline3 . "<br />";
	if(@$city != "") $theAddress .= $city . "<br />";
	if(@$statecode != "" && @$postcode != "")
		$theAddress .= $statecode . ", " . $postcode . "<br />";
	else{
		if(@$statecode != "") $theAddress .= $statecode . "<br />";
		if(@$postcode != "") $theAddress .= $postcode . "<br />";
	}
	if(@$countrycode != "") $theAddress .= $countrycode . "<br />";
}
function ParseUPSTrackingOutput($sXML, &$totActivity, &$shipperNo, &$serviceDesc, &$shipperaddress, &$shiptoaddress, &$scheddeldate, &$rescheddeldate, &$errormsg, &$activityList){
	$noError = TRUE;
	$totalCost = 0;
	$packCost = 0;
	$index = 0;
	$errormsg = "";
	$gotxml=FALSE;
	$theaddress="";
	// print str_replace("<","<br />&lt;",$sXML) . "<br />\n";
	$xmlDoc = new vrXMLDoc($sXML);
	// Set t2 = xmlDoc.getElementsByTagName("TrackResponse").Item(0)
	$nodeList = $xmlDoc->nodeList->childNodes[0];
	for($ii = 0; $ii < $nodeList->length; $ii++){
		if($nodeList->nodeName[$ii]=="Response"){
			$e = $nodeList->childNodes[$ii];
			for($j = 0; $j < $e->length; $j++){
				if($e->nodeName[$j]=="ResponseStatusCode"){
					$noError = ((int)$e->nodeValue[$j])==1;
				}
				if($e->nodeName[$j]=="Error"){
					$errormsg = "";
					$t = $e->childNodes[$j];
					for($k = 0; $k < $t->length; $k++){
						if($t->nodeName[$k]=="ErrorSeverity"){
							if($t->nodeValue[$k]=="Transient")
								$errormsg = "This is a temporary error. Please wait a few moments then refresh this page.<br />" . $errormsg;
						}elseif($t->nodeName[$k]=="ErrorDescription"){
							$errormsg .= $t->nodeValue[$k];
						}
					}
				}
			}
		}elseif($nodeList->nodeName[$ii]=="Shipment"){ // no Top-level Error
			$e = $nodeList->childNodes[$ii];
			for($i = 0;$i < $e->length; $i++){
				// print "Nodename is : " . $e->nodeName[$i] . "<br />";
				switch($e->nodeName[$i]){
					case "Shipper":
						$t = $e->childNodes[$i];
						for($k = 0; $k < $t->length; $k++){
							if($t->nodeName[$k] == "ShipperNumber")
								$shipperNo = $t->nodeValue[$k];
							elseif($t->nodeName[$k] == "Address")
								getAddress($t->childNodes[$k], $shipperaddress);
						}
					break;
					case "ShipTo":
						$t = $e->childNodes[$i];
						for($k = 0; $k < $t->length; $k++){
							if($t->nodeName[$k] == "Address")
								getAddress($t->childNodes[$k], $shiptoaddress);
						}
					break;
					case "ScheduledDeliveryDate":
						$scheddeldate = $e->nodeValue[$i];
					break;
					case "Service":
						$t = $e->childNodes[$i];
						for($k = 0; $k < $t->length; $k++){
							if($t->nodeName[$k] == "X_Code_X"){
								switch((int)$t->nodeValue[$k]){
									case 1:
										$serviceDesc = "Next Day Air";
										break;
									case 2:
										$serviceDesc = "2nd Day Air";
										break;
									case 3:
										$serviceDesc = "Ground Service";
										break;
									case 7:
										$serviceDesc = "Worldwide Express";
										break;
									case 8:
										$serviceDesc = "Worldwide Expedited";
										break;
									case 11:
										$serviceDesc = "Standard service";
										break;
									case 12:
										$serviceDesc = "3-Day Select";
										break;
									case 13:
										$serviceDesc = "Next Day Air Saver";
										break;
									case 14:
										$serviceDesc = "Next Day Air Early AM";
										break;
									case 54:
										$serviceDesc = "Worldwide Express Plus";
										break;
									case 59:
										$serviceDesc = "2nd Day Air AM";
										break;
									case 64:
										$serviceDesc = "UPS Express NA1";
										break;
									case 65:
										$serviceDesc = "Express Saver";
										break;
								}
								// print "The service code is : " . $t->nodeName[$k] . ":" . $t->nodeValue[$k] . "<br />";
							}elseif($t->nodeName[$k] == "Description"){
								$serviceDesc = $t->nodeValue[$k];
							}
						}
					break;
					case "Package":
						$t = $e->childNodes[$i];
						for($k = 0; $k < $t->length; $k++){
							if($t->nodeName[$k] == "RescheduledDeliveryDate"){
								$rescheddeldate = $t->nodeValue[$k];
							}elseif($t->nodeName[$k] == "Activity"){
								$u = $t->childNodes[$k];
								for($l = 0; $l < $u->length; $l++){
									if($u->nodeName[$l] == "ActivityLocation"){
										$v = $u->childNodes[$l];
										for($m = 0; $m < $v->length; $m++){
											if($v->nodeName[$m] == "Address")
												getAddress($v->childNodes[$m], $activityList[$totActivity][0]);
											elseif($v->nodeName[$m] == "Description")
												$description = $v->nodeValue[$m];
											elseif($v->nodeName[$m] == "SignedForByName")
												$activityList[$totActivity][1] = $v->nodeValue[$m];
										}
									}elseif($u->nodeName[$l] == "Status"){
										$v = $u->childNodes[$l];
										for($m = 0; $m < $v->length; $m++){
											if($v->nodeName[$m] == "StatusType"){
												$w = $v->childNodes[$m];
												for($nn = 0; $nn < $w->length; $nn++){
													if($w->nodeName[$nn] == "Code")
														$activityList[$totActivity][3]=$w->nodeValue[$nn];
													elseif($w->nodeName[$nn] == "Description")
														$activityList[$totActivity][4]=$w->nodeValue[$nn];
												}
											}elseif($v->nodeName[$m] == "StatusCode"){
												$w = $v->childNodes[$m];
												for($nn = 0; $nn < $w->length; $nn++){
													if($w->nodeName[$nn] == "Code")
														$activityList[$totActivity][5]=$w->nodeValue[$nn];
												}
											}
										}
									}else{
										if($u->nodeName[$l]=="Date")
											$activityList[$totActivity][6]=$u->nodeValue[$l];
										elseif($u->nodeName[$l]=="Time")
											$activityList[$totActivity][7]=$u->nodeValue[$l];
									}
								}
								$totActivity++;
							}
						}
					break;
				}
			}
		}
	}
	return $noError;
}
function UPSTrack($trackNo){
	global $upsAccess,$upsUser,$upsPw,$pathtocurl,$curlproxy;
	// activityList(100,10)
	// ActivityList(0) = Address
	// ActivityList(1) = SignedForByName
	// ActivityList(2) = Not Used
	// ActivityList(3) = Activity -> Status -> StatusType -> Code
	// ActivityList(4) = Activity -> Status -> StatusType -> Description
	// ActivityList(5) = Activity -> Status -> StatusCode -> Code
	// ActivityList(6) = Activity -> Date
	// ActivityList(7) = Activity -> Time
	$lastloc="xxxxxx";
	$success = true;

	$sXML = '<?xml version="1.0"?><AccessRequest xml:lang="en-US"><AccessLicenseNumber>' . $upsAccess . "</AccessLicenseNumber><UserId>" . $upsUser . "</UserId><Password>" . $upsPw . "</Password></AccessRequest>";
	$sXML .= '<?xml version="1.0"?><TrackRequest xml:lang="en-US"><Request><TransactionReference><CustomerContext>Example 3</CustomerContext><XpciVersion>1.0001</XpciVersion></TransactionReference><RequestAction>Track</RequestAction><RequestOption>';
	if(trim(@$_POST["activity"])=="LAST") $sXML .= "none"; else $sXML .= "activity";
	$sXML .= "</RequestOption></Request>";
	if(FALSE){
		$sXML .= "<ReferenceNumber><Value>" . $trackNo . "</Value></ReferenceNumber>";
		$sXML .= "<ShipperNumber>116593</ShipperNumber></TrackRequest>";
	}else
		$sXML .= "<TrackingNumber>" . $trackNo . "</TrackingNumber></TrackRequest>";
	if(@$pathtocurl != ""){
		exec($pathtocurl . ' --data-binary ' . escapeshellarg($sXML) . ' https://www.ups.com/ups.app/xml/Track', $res, $retvar);
		$res = implode("\n",$res);
	}else{
		if (!$ch = curl_init()) {
			$success = false;
			$errormsg = "cURL package not installed in PHP";
		}else{
			curl_setopt($ch, CURLOPT_URL,'https://www.ups.com/ups.app/xml/Track'); 
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $sXML);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if(@$curlproxy!=''){
				curl_setopt($ch, CURLOPT_PROXY, $curlproxy);
			}
			$res = curl_exec($ch);
			curl_close($ch);
			// print str_replace("<","<br />&lt;",$res) . "<br />\n";
		}
	}
	if($success){
		$totActivity = 0;
		$success = ParseUPSTrackingOutput($res, $totActivity, $shipperNo, $serviceDesc, $shipperaddress, $shiptoaddress, $scheduleddeliverydate, $rescheddeliverydate, $errormsg, $activityList);

		if($success){
			for($index2=0; $index2 < $totActivity-1; $index2++){
				for($index=0; $index < $totActivity-1; $index++){
					if((int)($activityList[$index][6] . $activityList[$index][7]) > (int)($activityList[$index+1][6] . $activityList[$index+1][7])){
						$tempArr = $activityList[$index];
						$activityList[$index]=$activityList[$index+1];
						$activityList[$index+1]=$tempArr;
					}
				}
			}
			if(trim($shipperNo) != ""){ ?>
	  <tr>
		<td class="cobhl" width="30%"><strong>Shipper Number</strong> </td>
		<td class="cobll"><?php print $shipperNo?></td>
	  </tr>
	<?php	}
			if(trim($serviceDesc) != ""){ ?>
	  <tr>
		<td class="cobhl" width="30%"><strong>Service Description</strong> </td>
		<td class="cobll"><?php print $serviceDesc?></td>
	  </tr>
	<?php	}
			if(trim($shipperaddress) != ""){ ?>
	  <tr>
		<td class="cobhl" width="30%" valign="top"><strong>Shipper Address</strong> </td>
		<td class="cobll"><?php print $shipperaddress?></td>
	  </tr>
	<?php	}
			if(trim($shiptoaddress) != ""){ ?>
	  <tr>
		<td class="cobhl" width="30%" valign="top"><strong>Ship-To Address</strong> </td>
		<td class="cobll"><?php print $shiptoaddress?></td>
	  </tr>
	<?php	}
			if(trim($scheduleddeliverydate) != ""){ ?>
	  <tr>
		<td class="cobhl" width="30%" valign="top"><strong>Sched. Delivery Date</strong> </td>
		<td class="cobll"><?php print date("m-d-Y",mktime(0,0,0,substr($scheduleddeliverydate,4,2),substr($scheduleddeliverydate,6,2),substr($scheduleddeliverydate,0,4)))?></td>
	  </tr>
	<?php	}
			if(trim($rescheddeliverydate) != ""){ ?>
	  <tr>
		<td class="cobhl" width="30%" valign="top"><strong>ReSched. Delivery Date</strong> </td>
		<td class="cobll"><?php print date("m-d-Y",mktime(0,0,0,substr($rescheddeliverydate,4,2),substr($rescheddeliverydate,6,2),substr($rescheddeliverydate,0,4)))?></td>
	  </tr>
	  <tr>
		<td class="cobhl" width="30%" valign="top"><strong>Note</strong> </td>
		<td class="cobll">Your package is in the UPS system and has a rescheduled delivery date of <?php print date("m-d-Y",mktime(0,0,0,substr($rescheddeliverydate,4,2),substr($rescheddeliverydate,6,2),substr($rescheddeliverydate,0,4)))?></td>
	  </tr>
	<?php	} ?>
			</table>
	  &nbsp;
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr>
			    <td class="cobhl"><strong>Location</strong></td>
				<td class="cobhl"><strong>Description</strong></td>
				<td class="cobhl"><strong>Date&nbsp;/&nbsp;Time</strong></td>
			  </tr>
<?php
	for($index=0; $index < $totActivity; $index++){ 
		if(($index % 2) == 0)
			$cellbg='class="cobll"';
		else
			$cellbg='class="cobhl"';
?>
			  <tr>
			    <td <?php print $cellbg?>><span style="font-size:10px"><?php
									if($lastloc==$activityList[$index][0])
										print '<p align="center">"</p>';
									else{
										print $activityList[$index][0];
										$lastloc = $activityList[$index][0];
									} ?></span></td>
				<td <?php print $cellbg?>><span style="font-size:10px"><?php print $activityList[$index][4];
									if(@$activityList[$index][1]!='') print "<br /><strong>Signed By :</strong> " . $activityList[$index][1]; ?></span></td>
				<td <?php print $cellbg?>><span style="font-size:10px"><?php
					$theDate = $activityList[$index][6];
					$theTime = $activityList[$index][7];
					print date("m-d-Y\<\B\R\>H:i:s",mktime(substr($theTime,0,2),substr($theTime,2,2),substr($theTime,4,2),substr($theDate,4,2),substr($theDate,6,2),substr($theDate,0,4)))?></span></td>
			  </tr>
<?php
	} ?>
			</table>
	  <hr width="70%" align="center" />
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php
		}else{
?>
			  <tr>
			    <td class="cobll" colspan="2" height="30" align="center"><strong>The tracking system returned the following error : <?php print $errormsg?></strong></td>
			  </tr>
			</table>
	  <hr width="70%" align="center" />
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php
		}
	}
	return $success;
}
if(trim(@$_POST["trackno"]) != "")
	UPSTrack(trim(@$_POST["trackno"]));
?>
			  <tr>
			    <td class="cobhl" width="50%" align="right">Please enter your UPS Tracking Number : </td>
				<td class="cobll" width="50%"><input type="text" size="30" name="trackno" value="<?php print htmlspecials(unstripslashes(@$_REQUEST['trackno']))?>" /></td>
			  </tr>
			  <tr>
			    <td class="cobhl" width="50%" align="right">Show Activity : </td>
				<td class="cobll" width="50%"><select name="activity" size="1"><option value="LAST">Show Last Activity Only</option><option value="ALL"<?php if(trim(@$_POST["activity"])=="ALL") print ' selected="selected"'?>>Show All Activity</option></select></td>
			  </tr>
			  <tr>
			    <td class="cobll" colspan="2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
				    <tr>
					  <td width="17%" height="26">&nbsp;</td>
					  <td width="66%" align="center"><?php print imageorbutton(@$imgviewlicense,'View License','','viewlicense()',TRUE).' '.imageorbutton(@$imgtrackpackage,'Track Package','','checkaccept()',TRUE)?></td>
					  <td width="17%" height="26" align="right" valign="bottom"><img src="images/tablebr.png" alt="" /></td>
					</tr>
				  </table></td>
			  </tr>
			  <tr>
				<td class="cobll" width="100%" height="30" colspan="2" align="center" valign="middle"><span style="font-size:10px"><input type="checkbox" name="agreeconds" value="ON" <?php if(@$_POST["agreeconds"]=="ON") print "checked"?> /> By selecting this box and the "Track Package" button, I agree to these <a class="ectlink" href="javascript:viewlicense();"><strong>Terms and Conditions</strong></a>.</span></td>
			  </tr>
			</table>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%" align="center"><p>&nbsp;<br /><span style="font-size:10px"><?php print str_replace("'","\'",$xxUPStm)?></span></p></td>
		</tr>
	  </table>
	</form>
<br />
<?php
}elseif($theshiptype=="usps"){
?>
&nbsp;<br />
	<form method="post" name="trackform" action="tracking.php">
	<input type="hidden" name="carrier" value="usps" />
      <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr>
		  <td class="cobll" colspan="2">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
			  <tr>
				<td width="40">&nbsp;</td><td align="center">&nbsp;<br /><span style="font-size:18px;font-family:Verdana;font-weight:bold">USPS Tracking Tool</span><br />&nbsp;</td><td width="40">&nbsp;</td>
			  </tr>
			</table>
		  </td>
		</tr>
<?php
function ParseUSPSTrackingOutput($sXML, &$totActivity, $onlylast, &$serviceDesc, &$shipperaddress, &$shiptoaddress, &$scheddeldate, &$rescheddeldate, &$errormsg, &$activityList){
	$noError = TRUE;
	$totalCost = 0;
	$packCost = 0;
	$index = 0;
	$errormsg = "";
	$gotxml=FALSE;
	$theaddress="";
	// print str_replace("<","<br />&lt;",$sXML) . "<br />\n";
	$xmlDoc = new vrXMLDoc($sXML);

	if($xmlDoc->nodeList->nodeName[0] == "Error"){ // Top-level Error
		$noError = FALSE;
		$nodeList = $xmlDoc->nodeList->childNodes[0];
		for($i = 0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=="Description"){
				$errormsg = $nodeList->nodeValue[$i];
			}
		}
	}else{ // no Top-level Error
		$nodeList = $xmlDoc->nodeList->childNodes[0];
		for($i = 0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=="TrackInfo"){
				$e = $nodeList->childNodes[$i];
				for($j = 0; $j < $nodeList->childNodes[$i]->length; $j++){
					$companyname= "";
					$city="";
					$statecode="";
					$postcode="";
					$countrycode="";
					if($e->nodeName[$j] == "Error"){ // Lower-level error
						$t = $e->childNodes[$j];
						for($k = 0; $k < $t->length; $k++){
							if($t->nodeName[$k] == "Description"){
								$noError = FALSE;
								$errormsg = $t->nodeValue[$k];
							}
						}
					}elseif($e->nodeName[$j] == "TrackDetail"){
						if(!$onlylast){
							$t = $e->childNodes[$j];
							for($k = 0; $k <=7; $k++){
								$activityList[$totActivity][$k]='';
							}
							for($k = 0; $k < $t->length; $k++){
								switch($t->nodeName[$k]){
								case "EventDate":
									$activityList[$totActivity][6]=$t->nodeValue[$k];
									break;
								case "EventTime":
									$activityList[$totActivity][7]=$t->nodeValue[$k];
									break;
								case "Event":
									$activityList[$totActivity][4]=$t->nodeValue[$k];
									break;
								case "EventCity":
									$city = $t->nodeValue[$k];
									break;
								case "EventState":
									$statecode = $t->nodeValue[$k];
									break;
								case "EventZIPCode":
									$postcode = $t->nodeValue[$k];
									break;
								case "EventCountry":
									$countrycode = $t->nodeValue[$k];
									break;
								case "FirmName":
									$companyname = $t->nodeValue[$k];
									break;
								}
							}
							$theAddress = "";
							if(@$companyname != "") $theAddress .= $companyname . "<br />";
							if(@$city != "") $theAddress .= $city . "<br />";
							if(@$statecode != "" && @$postcode != "")
								$theAddress .= $statecode . ", " . $postcode . "<br />";
							else{
								if(@$statecode != "") $theAddress .= $statecode . "<br />";
								if(@$postcode != "") $theAddress .= $postcode . "<br />";
							}
							if(@$countrycode != "") $theAddress .= $countrycode . "<br />";
							$activityList[$totActivity][0] = $theAddress;
							$totActivity++;
						}
					}elseif($e->nodeName[$j] == "TrackSummary"){
						$t = $e->childNodes[$j];
						for($k = 0; $k < $t->length; $k++){
							switch($t->nodeName[$k]){
							case "EventDate":
								$scheddeldate=$t->nodeValue[$k] . $scheddeldate;
								break;
							case "EventTime":
								$scheddeldate=$scheddeldate . " " . $t->nodeValue[$k];
								break;
							case "Event":
								$serviceDesc=$t->nodeValue[$k];
								break;
							case "EventCity":
								$city = $t->nodeValue[$k];
								break;
							case "EventState":
								$statecode = $t->nodeValue[$k];
								break;
							case "EventZIPCode":
								$postcode = $t->nodeValue[$k];
								break;
							case "EventCountry":
								$countrycode = $t->nodeValue[$k];
								break;
							case "FirmName":
								$companyname = $t->nodeValue[$k];
								break;
							}
						}
						$theAddress = "";
						if(@$companyname != "") $theAddress .= $companyname . "<br />";
						if(@$city != "") $theAddress .= $city . "<br />";
						if(@$statecode != "" && @$postcode != "")
							$theAddress .= $statecode . ", " . $postcode . "<br />";
						else{
							if(@$statecode != "") $theAddress .= $statecode . "<br />";
							if(@$postcode != "") $theAddress .= $postcode . "<br />";
						}
						if(@$countrycode != "") $theAddress .= $countrycode . "<br />";
						$shiptoaddress = $theAddress;
					}
				}
				$totalCost += $packCost;
				$packCost = 0;
			}
		}
	}
	return $noError;
}
function USPSTrack($trackNo){
	global $uspsUser,$pathtocurl,$curlproxy,$usecurlforfsock;
	// activityList(100,10)
	// ActivityList(0) = Address
	// ActivityList(1) = SignedForByName
	// ActivityList(2) = Not Used
	// ActivityList(3) = Activity -> Status -> StatusType -> Code
	// ActivityList(4) = Activity -> Status -> StatusType -> Description
	// ActivityList(5) = Activity -> Status -> StatusCode -> Code
	// ActivityList(6) = Activity -> Date
	// ActivityList(7) = Activity -> Time
	$lastloc="xxxxxx";
	$success = true;
	$sXML = '<TrackFieldRequest USERID="'.$uspsUser.'"><TrackID ID="'.str_replace(' ','',@$_POST['trackno']).'"></TrackID></TrackFieldRequest>';
	//print str_replace("<","<br />&lt;",str_replace("</","&lt;/",$sXML)) . "<br />\n";
	$sXML = "API=TrackV2&XML=" . $sXML;
	if(@$usecurlforfsock){
		$success = callcurlfunction('http://production.shippingapis.com/ShippingAPI.dll', $sXML, $res, '', $errormsg, FALSE);
	}else{
		$header = "POST /ShippingAPI.dll HTTP/1.0\r\n";
		//$header = "POST /ShippingAPITest.dll HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= 'Content-Length: ' . strlen($sXML) . "\r\n\r\n";
		$fp = fsockopen ('production.shippingapis.com', 80, $errno, $errstr, 30);
		if (!$fp){
			echo "$errstr ($errno)"; // HTTP error handling
			return FALSE;
		}else{
			$res = "";
			fputs ($fp, $header . $sXML);
			while (!feof($fp)) {
				$res .= fgets ($fp, 1024);
			}
			fclose ($fp);
		}
	}
	//print str_replace("<","<br />&lt;",str_replace("</","&lt;/",$res)) . "<br />\n";
	if($success){
		$totActivity = 0;
		$success = ParseUSPSTrackingOutput($res, $totActivity, trim(@$_POST["activity"])=='LAST', $serviceDesc, $shipperaddress, $shiptoaddress, $scheduleddeliverydate, $rescheddeliverydate, $errormsg, $activityList);
		if($success){
			for($index2=0; $index2 < $totActivity-1; $index2++){
				for($index=0; $index < $totActivity-1; $index++){
					if(strtotime($activityList[$index][6] . " " . $activityList[$index][7]) > strtotime($activityList[$index+1][6] . ' ' . $activityList[$index+1][7])){
						$tempArr = $activityList[$index];
						$activityList[$index]=$activityList[$index+1];
						$activityList[$index+1]=$tempArr;
					}
				}
			}
			if(trim($serviceDesc) != ""){ ?>
	  <tr>
		<td class="cobhl" width="30%"><strong>Event</strong> </td>
		<td class="cobll"><?php print $serviceDesc?></td>
	  </tr>
	<?php	}
			if(trim($shiptoaddress) != ""){ ?>
	  <tr>
		<td class="cobhl" width="30%" valign="top"><strong>Address</strong> </td>
		<td class="cobll"><?php print $shiptoaddress?></td>
	  </tr>
	<?php	}
			if(trim($scheduleddeliverydate) != ""){ ?>
	  <tr>
		<td class="cobhl" width="30%" valign="top"><strong>Event Date</strong> </td>
		<td class="cobll"><?php print $scheduleddeliverydate?></td>
	  </tr>
	<?php	} ?>
			</table>
<?php		if($totActivity > 0){ ?>
	  &nbsp;
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr>
			    <td class="cobhl"><strong>Location</strong></td>
				<td class="cobhl"><strong>Description</strong></td>
				<td class="cobhl"><strong>Date&nbsp;/&nbsp;Time</strong></td>
			  </tr>
<?php			for($index=0; $index < $totActivity; $index++){ 
					if(($index % 2) == 0)
						$cellbg='class="cobll"';
					else
						$cellbg='class="cobhl"'; ?>
			  <tr>
			    <td <?php print $cellbg?>><span style="font-size:10px"><?php
									if($lastloc==$activityList[$index][0])
										print '<p align="center">"</p>';
									else{
										print $activityList[$index][0];
										$lastloc = $activityList[$index][0];
									} ?></span></td>
				<td <?php print $cellbg?>><span style="font-size:10px"><?php print $activityList[$index][4];
									if(@$activityList[$index][1] != "") print "<br /><strong>Signed By :</strong> " . $activityList[$index][1]; ?></span></td>
				<td <?php print $cellbg?>><span style="font-size:10px"><?php
					$theDate = $activityList[$index][6];
					$theTime = $activityList[$index][7];
					print $theDate . '<br />' . $theTime; ?></span></td>
			  </tr>
<?php			} ?>
			</table>
<?php		} ?>
	  <hr width="70%" align="center" />
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php
		}else{
?>
			  <tr>
			    <td class="cobll" colspan="2" height="30" align="center"><strong>The tracking system returned the following error : <?php print $errormsg?></strong></td>
			  </tr>
			</table>
	  <hr width="70%" align="center" />
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php
		}
	}
	return $success;
}
if(trim(@$_POST["trackno"]) != "")
	USPSTrack(trim(@$_POST["trackno"]));
?>
			  <tr>
			    <td class="cobhl" width="50%" align="right">Please enter your USPS Tracking Number : </td>
				<td class="cobll" width="50%"><input type="text" size="30" name="trackno" value="<?php print htmlspecials(unstripslashes(@$_REQUEST['trackno']))?>" /></td>
			  </tr>
			  <tr>
			    <td class="cobhl" width="50%" align="right">Show Activity : </td>
				<td class="cobll" width="50%"><select name="activity" size="1"><option value="LAST">Show Last Activity Only</option><option value="ALL"<?php if(trim(@$_POST["activity"])=="ALL" || trim(@$_POST["activity"])=='') print ' selected="selected"'?>>Show All Activity</option></select></td>
			  </tr>
			  <tr>
			    <td class="cobll" colspan="2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
				    <tr>
					  <td class="cobll" width="17%" height="26">&nbsp;</td>
					  <td class="cobll" width="66%" align="center"><?php print imageorsubmit(@$imgtrackpackage,'Track Package','')?></td>
					  <td class="cobll" width="17%" height="26" align="right" valign="bottom"><img src="images/tablebr.png" alt="" /></td>
					</tr>
				  </table></td>
			  </tr>
			</table>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%" align="center"><p>&nbsp;</p></td>
		</tr>
	  </table>
	</form>
<br />
<?php
}elseif($theshiptype=="fedex"){
?>
&nbsp;<br />
	<form method="post" name="trackform" action="tracking.php">
	<input type="hidden" name="carrier" value="fedex" />
      <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr>
		  <td class="cobll" colspan="2">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
			  <tr>
				<td width="40"><img src="images/fedexlogo.png" alt="FedEx" /></td><td align="center">&nbsp;<br /><span style="font-size:18px;font-family:Verdana;font-weight:bold">FedEx<small>&reg;</small> Tracking Tool</span><br />&nbsp;</td><td width="40">&nbsp;</td>
			  </tr>
			</table>
		  </td>
		</tr>
<?php
function getFedExAddress($u, &$theAddress){
	global $fedexnamespace;
	$fns=$fedexnamespace;
	$signedby = "";
	for($l = 0;$l < $u->length; $l++){
		//print "AddName : " . $u->nodeName[$l] . ", AddVal : " . $u->nodeValue[$l] . "<br />";
		if($u->nodeName[$l] == "AddressLine1")
			$addressline1 = $u->nodeValue[$l];
		elseif($u->nodeName[$l] == "AddressLine2")
			$addressline2 = $u->nodeValue[$l];
		elseif($u->nodeName[$l] == "AddressLine3")
			$addressline3 = $u->nodeValue[$l];
		elseif($u->nodeName[$l] == $fns.":City")
			$city = $u->nodeValue[$l];
		elseif($u->nodeName[$l] == $fns.":StateOrProvinceCode")
			$statecode = $u->nodeValue[$l];
		elseif($u->nodeName[$l] == $fns.":PostalCode")
			$postcode = $u->nodeValue[$l];
		elseif($u->nodeName[$l] == $fns.":CountryCode"){
			$sSQL = "SELECT countryName FROM countries WHERE countryCode='" . $u->nodeValue[$l] . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result) > 0){
				$rs = mysql_fetch_assoc($result);
				$countrycode = $rs["countryName"];
			}else
				$countrycode = $u->nodeValue[$l];
			mysql_free_result($result);
		}
	}
	$theAddress = "";
	if(@$addressline1 != "") $theAddress .= $addressline1 . "<br />";
	if(@$addressline2 != "") $theAddress .= $addressline2 . "<br />";
	if(@$addressline3 != "") $theAddress .= $addressline3 . "<br />";
	if(@$city != "") $theAddress .= $city . "<br />";
	if(@$statecode != "" && @$postcode != "")
		$theAddress .= $statecode . ", " . $postcode . "<br />";
	else{
		if(@$statecode != "") $theAddress .= $statecode . "<br />";
		if(@$postcode != "") $theAddress .= $postcode . "<br />";
	}
	if(@$countrycode != "") $theAddress .= $countrycode . "<br />";
}
function ParseFedexTrackingOutput($sXML, &$totActivity, &$deliverydate, &$serviceDesc, &$packagecount, &$shiptoaddress, &$scheddeldate, &$signedforby, &$errormsg, &$activityList){
	global $fedexnamespace;
	$noError = TRUE;
	$totalCost = 0;
	$packCost = 0;
	$index = 0;
	$errormsg = "";
	$gotxml=FALSE;
	$theaddress="";
	$fns=$fedexnamespace;
	$xmlDoc = new vrXMLDoc($sXML);
	$nodeList = $xmlDoc->nodeList->childNodes[0];
	for($i = 0; $i < $nodeList->length; $i++){
		if(strpos($nodeList->nodeName[$i],'env:Body')!==FALSE){
			$nodeList=$nodeList->childNodes[$i];
		}
	}
	for($i = 0; $i < $nodeList->length; $i++){
		if($nodeList->nodeName[$i]==$fns.':TrackReply'){
			$nodeList=$nodeList->childNodes[$i];
		}
	}
	for($i = 0; $i < $nodeList->length; $i++){
		switch($nodeList->nodeName[$i]){
			case $fns.":HighestSeverity":
				$noError = ($nodeList->nodeValue[$i]!='ERROR' && $nodeList->nodeValue[$i]!='FAILURE');
			break;
			case $fns.":Notifications":
				$t = $nodeList->childNodes[$i];
				for($k = 0; $k < $t->length; $k++){
					if($t->nodeName[$k] == $fns.":Message"){
						$errormsg = $t->nodeValue[$k];
					}
				}
			break;
			case $fns.":TrackDetails":
				$fxw = $nodeList->childNodes[$i];
				for($k = 0; $k < $fxw->length; $k++){
					switch($fxw->nodeName[$k]){
					case $fns.":DeliverySignatureName":
						$signedforby = $fxw->nodeValue[$k];
					break;
					case $fns.":DestinationAddress":
						getFedExAddress($fxw->childNodes[$k], $shiptoaddress);
					break;
					case "DeliveredDate":
						$deliverydate = $fxw->nodeValue[$k] . $deliverydate;
					break;
					case "DeliveredTime":
						$deliverydate .= ' ' . $fxw->nodeValue[$k];
					break;
					case $fns.":ServiceType":
						$serviceDesc = $fxw->nodeValue[$k];
					break;
					case $fns.":PackageCount":
						$packagecount = $fxw->nodeValue[$k];
					break;
					case $fns.":Events":
						$t = $fxw->childNodes[$k];
						for($kfx = 0; $kfx < $t->length; $kfx++){
							if($t->nodeName[$kfx] == $fns.":Timestamp"){
								$activityList[$totActivity][6] = $t->nodeValue[$kfx];
							}elseif($t->nodeName[$kfx] == "Time"){
								$activityList[$totActivity][7] = $t->nodeValue[$kfx];
							}elseif($t->nodeName[$kfx] == "StatusExceptionCode"){
								$activityList[$totActivity][3] = $t->nodeValue[$kfx];
							}elseif($t->nodeName[$kfx] == $fns.":EventDescription" || $t->nodeName[$kfx] == "StatusExceptionDescription"){
								if($t->nodeValue[$kfx] != "Package status") $activityList[$totActivity][4] = $t->nodeValue[$kfx];
							}elseif($t->nodeName[$kfx] == $fns.":Address"){
								getFedExAddress($t->childNodes[$kfx], $activityList[$totActivity][0]);
							}
						}
						if($activityList[$totActivity][4] != '') $totActivity++;
					break;
					}
				}
			break;
		}
	}
	return $noError;
}
function FedexTrack($trackNo){
	global $fedexuserkey,$fedexuserpwd,$fedexaccount,$fedexmeter,$fedexurl,$fedexnamespace;
	// activityList(100,10)
	// ActivityList(0) = Address
	// ActivityList(1) = SignedForByName
	// ActivityList(2) = Not Used
	// ActivityList(3) = Activity -> Status -> StatusType -> Code
	// ActivityList(4) = Activity -> Status -> StatusType -> Description
	// ActivityList(5) = Activity -> Status -> StatusCode -> Code
	// ActivityList(6) = Activity -> Date
	// ActivityList(7) = Activity -> Time
	$lastloc="xxxxxx";
	$success = true;
	$sXML = '<?xml version="1.0" encoding="UTF-8" ?>';
	$sXML .= '<FDXTrackRequest xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="FDXTrackRequest.xsd">';
	$sXML .= '<RequestHeader>';
	$sXML .= '<CustomerTransactionIdentifier>transidentifier</CustomerTransactionIdentifier>';
	$sXML .= '<AccountNumber>' . $fedexaccount . '</AccountNumber>';
	$sXML .= '<MeterNumber>' . $fedexmeter . '</MeterNumber>';
	$sXML .= '<CarrierCode></CarrierCode>';
	$sXML .= '</RequestHeader>';
	$sXML .= '<PackageIdentifier>';
	$sXML .= '<Value>' . $trackNo . '</Value>';
	$sXML .= '<Type>TRACKING_NUMBER_OR_DOORTAG</Type>';
	$sXML .= '</PackageIdentifier>';
	if(trim(@$_POST["activity"])=="LAST") $sXML .= '<DetailScans>0</DetailScans>'; else $sXML .= '<DetailScans>1</DetailScans>';
	$sXML .= '</FDXTrackRequest>';
	
	$sXML ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v4="http://fedex.com/ws/track/v4">' .
"   <soapenv:Header/>" .
"   <soapenv:Body>" .
"      <v4:TrackRequest>" .
"         <v4:WebAuthenticationDetail>" .
"            <v4:CspCredential>" .
"               <v4:Key>mKOUqSP4CS0vxaku</v4:Key>" .
"               <v4:Password>IAA5db3Pmhg3lyWW6naMh4Ss2</v4:Password>" .
"            </v4:CspCredential>" .
"            <v4:UserCredential>" .
"               <v4:Key>" . $fedexuserkey . "</v4:Key>" .
"               <v4:Password>" . $fedexuserpwd . "</v4:Password>" .
"            </v4:UserCredential>" .
"         </v4:WebAuthenticationDetail>" .
"         <v4:ClientDetail>" .
"            <v4:AccountNumber>" . $fedexaccount . "</v4:AccountNumber>" .
"            <v4:MeterNumber>" . $fedexmeter . "</v4:MeterNumber>" .
"            <v4:ClientProductId>IBTB</v4:ClientProductId>" .
"            <v4:ClientProductVersion>3272</v4:ClientProductVersion>" .
"         </v4:ClientDetail>" .
"         <v4:TransactionDetail>" .
"            <v4:CustomerTransactionId>track Request</v4:CustomerTransactionId>" .
"         </v4:TransactionDetail>" .
"         <v4:Version>" .
"            <v4:ServiceId>trck</v4:ServiceId>" .
"            <v4:Major>4</v4:Major>" .
"            <v4:Intermediate>1</v4:Intermediate>" .
"            <v4:Minor>0</v4:Minor>" .
"         </v4:Version>" .
"         <v4:PackageIdentifier>" .
"            <v4:Value>" . $trackNo . "</v4:Value>" .
"            <v4:Type>TRACKING_NUMBER_OR_DOORTAG</v4:Type>" .
"         </v4:PackageIdentifier>" .
"         <v4:IncludeDetailedScans>" . (trim(@$_POST["activity"])=="LAST" ? "false" : "true") . "</v4:IncludeDetailedScans>" .
"      </v4:TrackRequest>" .
"   </soapenv:Body>" .
"</soapenv:Envelope>";

	$success = callcurlfunction($fedexurl, $sXML, $xmlres, '', $errormsg, FALSE);
	if($success){
		$totActivity = 0;
		if(@$dumpshippingxml) dumpxmloutput($sXML,$xmlres);
		$pattern = '/<(.{1,3}):TrackReply/';
		preg_match($pattern, $xmlres, $matches);
		$fedexnamespace=$matches[1];
		$success = ParseFedexTrackingOutput($xmlres, $totActivity, $deliverydate, $serviceDesc, $packagecount, $shiptoaddress, $scheduleddeliverydate, $signedforby, $errormsg, $activityList);
		if($success){
			for($index2=0; $index2 < $totActivity-1; $index2++){
				for($index=0; $index < $totActivity-1; $index++){
					if(($activityList[$index][6] . $activityList[$index][7]) > ($activityList[$index+1][6] . $activityList[$index+1][7])){
						$tempArr = $activityList[$index];
						$activityList[$index]=$activityList[$index+1];
						$activityList[$index+1]=$tempArr;
					}
				}
			}
			if(trim($serviceDesc) != ""){ ?>
	  <tr>
		<td class="cobhl" width="30%"><strong>Service Description</strong> </td>
		<td class="cobll"><?php print $serviceDesc?></td>
	  </tr>
	<?php	}
			if(trim($packagecount) != ""){ ?>
	  <tr>
		<td class="cobhl" width="30%" valign="top"><strong>Package Count</strong> </td>
		<td class="cobll"><?php print $packagecount?></td>
	  </tr>
	<?php	}
			if(trim($shiptoaddress) != ""){ ?>
	  <tr>
		<td class="cobhl" width="30%" valign="top"><strong>Ship-To Address</strong> </td>
		<td class="cobll"><?php print $shiptoaddress?></td>
	  </tr>
	<?php	}
			if(trim($signedforby) != ""){ ?>
	  <tr>
		<td class="cobhl" width="30%" valign="top"><strong>Signed For By</strong> </td>
		<td class="cobll"><?php print $signedforby?></td>
	  </tr>
	<?php	}
			if(trim($deliverydate) != ""){ ?>
	  <tr>
		<td class="cobhl" width="30%" valign="top"><strong>Delivery Date</strong> </td>
		<td class="cobll"><?php print $deliverydate?></td>
	  </tr>
	<?php	} ?>
			</table>
	  &nbsp;
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr>
			    <td class="cobhl"><strong>Location</strong></td>
				<td class="cobhl"><strong>Description</strong></td>
				<td class="cobhl"><strong>Date&nbsp;/&nbsp;Time</strong></td>
			  </tr>
<?php
	for($index=0; $index < $totActivity; $index++){ 
		if(($index % 2) == 0)
			$cellbg='class="cobll"';
		else
			$cellbg='class="cobhl"';
?>
			  <tr>
			    <td <?php print $cellbg?>><span style="font-size:10px"><?php
									if($lastloc==$activityList[$index][0])
										print '<p align="center">"</p>';
									else{
										print $activityList[$index][0];
										$lastloc = $activityList[$index][0];
									} ?></span></td>
				<td <?php print $cellbg?>><span style="font-size:10px"><?php print $activityList[$index][4];
									if(@$activityList[$index][1] != "") print "<br /><strong>Signed By :</strong> " . $activityList[$index][1]; ?></span></td>
				<td <?php print $cellbg?>><span style="font-size:10px"><?php
					$fxtimestamp = strtotime($activityList[$index][6]);
					$theDate=date('Y-m-d',$fxtimestamp);
					$theTime=date('H:m:s',$fxtimestamp);
					print $theDate . '<br />' . $theTime;?></span></td>
			  </tr>
<?php
	} ?>
			</table>
	  <hr width="70%" align="center" />
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php
		}else{
?>
			  <tr>
			    <td class="cobll" colspan="2" height="30" align="center"><strong>The tracking system returned the following error : <?php print $errormsg?></strong></td>
			  </tr>
			</table>
	  <hr width="70%" align="center" />
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php
		}
	}
	return $success;
}
if(trim(@$_POST["trackno"]) != "")
	FedexTrack(trim(@$_POST["trackno"]));
?>
			  <tr>
			    <td class="cobhl" width="50%" align="right">Please enter your FedEx Tracking Number : </td>
				<td class="cobll" width="50%"><input type="text" size="30" name="trackno" value="<?php print htmlspecials(unstripslashes(@$_REQUEST['trackno']))?>" /></td>
			  </tr>
			  <tr>
			    <td class="cobhl" width="50%" align="right">Show Activity : </td>
				<td class="cobll" width="50%"><select name="activity" size="1"><option value="LAST">Show Last Activity Only</option><option value="ALL"<?php if(trim(@$_POST["activity"])=="ALL") print ' selected="selected"'?>>Show All Activity</option></select></td>
			  </tr>
			  <tr>
			    <td class="cobll" colspan="2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
				    <tr>
					  <td class="cobll" width="17%" height="26">&nbsp;</td>
					  <td class="cobll" width="66%" align="center"><?php print imageorsubmit(@$imgtrackpackage,'Track Package','')?></td>
					  <td class="cobll" width="17%" height="26" align="right" valign="bottom"><img src="images/tablebr.png" alt="" /></td>
					</tr>
				  </table></td>
			  </tr>
			</table>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%" align="center"><p>&nbsp;<br /><span style="font-size:10px"><?php print $fedexcopyright?></span></p></td>
		</tr>
	  </table>
	</form>
<br />
<?php
}else{ // undecided
?>
&nbsp;<br />
	<form method="post" action="tracking.php">
	<input type="hidden" name="carrier" id="carrier" value="xxxxxx" />
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
		<tr>
		  <td class="cobll" colspan="2">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
			  <tr>
				<td width="98" align="left"><?php if($incupscopyright){ ?><img src="images/LOGO_S.gif" alt="UPS" /><?php }else{ print '&nbsp;';} ?></td><td align="center">&nbsp;<br /><span style="font-size:18px;font-family:Verdana;font-weight:bold">Please select your shipping carrier.</span><br />&nbsp;</td><td width="98"><?php if($incfedexcopyright){ ?><img src="images/fedexlogo.png" alt="FedEx" /><?php }else{ print "&nbsp;"; } ?></td>
			  </tr>
			</table>
		  </td>
		</tr>
<?php	if(@$shipType==4 || $alternateratesups || strpos(strtolower(@$trackingcarriers), 'ups')!==FALSE){ ?>
		<tr>
		  <td class="cobhl" width="50%" align="right">Products shipped via UPS : </td>
		  <td class="cobll" width="50%"><?php print imageorsubmit(@$imgtrackinggo,$xxGo.'" onclick="document.getElementById(\'carrier\').value=\'ups\'','')?></td>
		</tr>
<?php	}
		if($shipType==3 || $alternateratesusps || strpos(strtolower(@$trackingcarriers), 'usps')!==FALSE){ ?>
		<tr>
		  <td class="cobhl" width="50%" align="right">Products shipped via USPS : </td>
		  <td class="cobll" width="50%"><?php print imageorsubmit(@$imgtrackinggo,$xxGo.'" onclick="document.getElementById(\'carrier\').value=\'usps\'','')?></td>
		</tr>
<?php	}
		if($shipType==7 || $shipType==8 || $alternateratesfedex || strpos(strtolower(@$trackingcarriers), 'fedex')!==FALSE){ ?>
		<tr>
		  <td class="cobhl" width="50%" align="right">Products shipped via FedEx : </td>
		  <td class="cobll" width="50%"><?php print imageorsubmit(@$imgtrackinggo,$xxGo.'" onclick="document.getElementById(\'carrier\').value=\'fedex\'','')?></td>
		</tr>
<?php	} ?>
	  </table>
	</form>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
		<tr><td>&nbsp;</td></tr>
<?php	if($incupscopyright){ ?>
        <tr>
          <td width="100%" align="center"><p>&nbsp;<br /><span style="font-size:10px"><?php print str_replace("'","\'",$xxUPStm)?></span></p></td>
		</tr>
<?php	}
		if($incfedexcopyright){ ?>
		<tr>
          <td width="100%" align="center"><p>&nbsp;<br /><span style="font-size:10px"><?php print $fedexcopyright?></span></p></td>
		</tr>
<?php	} ?>
	  </table>
	  <br />
<?php
}
?>
