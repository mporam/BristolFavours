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
include 'inc/languageadmin.php';
include 'inc/languagefile.php';
include 'includes.php';
include 'inc/incemail.php';
include 'inc/incfunctions.php';
ob_clean();
if(@$storesessionvalue=='') $storesessionvalue='virtualstore';
if(! (@$_GET['action']=='notifystock' || @$_GET['action']=='clord' || @$_GET['action']=='applycert' || @$_GET['action']=='centinel' || @$_GET['action']=='centinel2')){
	if(@$_SESSION['loggedon'] != $storesessionvalue || @$disallowlogin==TRUE){
		if(@$_SERVER['HTTPS'] == 'on' || @$_SERVER['SERVER_PORT'] == '443')$prot='https://';else $prot='http://';
		header('Location: '.$prot.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/login.php');
		exit;
	}
}
$alreadygotadmin = getadminsettings();
if(@$dateadjust=='') $dateadjust=0;
if(@$dateformatstr == '') $dateformatstr = 'm/d/Y';
$admindatestr='Y-m-d';
if(@$admindateformat=='') $admindateformat=0;
if($admindateformat==1)
	$admindatestr='m/d/Y';
elseif($admindateformat==2)
	$admindatestr='d/m/Y';
function sendmessagewithbasicauth($themessage){
	global $googledata1,$googledata2,$googledemomode,$curlproxy,$success;
	$cfurl='https://' . ($googledemomode ? 'sandbox' : 'checkout') . '.google.com' . ($googledemomode ? '/checkout' : '') . '/api/checkout/v2/request/Merchant/' . $googledata1;
	$success = TRUE;
	if(@$pathtocurl != ''){
		exec($pathtocurl . ' -H \'Authorization: Basic ' . base64_encode($googledata1 . ':' . $googledata2) . '\' -H \'Content-Type: application/xml\' -H \'Accept: application/xml\' --data-binary ' . escapeshellarg('<?xml version="1.0" encoding="UTF-8"?>' . $themessage) . ' ' . $cfurl, $cfres, $retvar);
		$cfres = implode("\n",$cfres);
	}else{
		if(!$ch = curl_init()) {
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
			curl_setopt($ch, CURLOPT_POSTFIELDS, '<?xml version="1.0" encoding="UTF-8"?>' . $themessage);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if(@$curlproxy!=''){
				curl_setopt($ch, CURLOPT_PROXY, $curlproxy);
			}
			$cfres = curl_exec($ch);
			// print str_replace("<","<br />&lt;",str_replace('<'.'/','&lt;/',$cfres)) . "<br />\n";
			if(curl_error($ch) != ""){
				print 'cURL error: ' . curl_error($ch) . '<br />';
				$success=FALSE;
			}else{
				curl_close($ch);
			}
		}
	}
	return($cfres);
}
if(@$_GET['action']=="centinel" OR @$_GET['action']=="centinel2"){
	if(@$pathtossl!=''){
		if(substr($pathtossl,-1)!='/') $storeurl = $pathtossl . '/'; else $storeurl = $pathtossl;
	}
}
function jsenc($tstr){
	global $adminencoding;
	if(strtolower(@$adminencoding)!='utf-8') $tstr=utf8_encode($tstr);
	return($tstr);
}
if(@$_GET['action']=='notifystock'){
	$oid=@$_GET['oid'];
	$legalrequest=TRUE;
	if(! is_numeric($oid)) $legalrequest=FALSE;
	$sSQL = "SELECT pId FROM products WHERE pId='".escape_string(@$_GET['pid'])."'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result)==0) $legalrequest=FALSE;
	mysql_free_result($result);
	$sSQL = "SELECT pId FROM products WHERE pId='".escape_string(@$_GET['tpid'])."'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result)==0) $legalrequest=FALSE;
	mysql_free_result($result);
	mysql_query("DELETE FROM notifyinstock WHERE nsDate<'".date('Y-m-d', time() - (60*60*24*365))."'") or print(mysql_error());
	mysql_query("DELETE FROM notifyinstock WHERE nsTriggerProdID='".escape_string(@$_GET['tpid'])."' AND nsEmail='".escape_string(@$_GET['email'])."'") or print(mysql_error());
	if($legalrequest) mysql_query("INSERT INTO notifyinstock (nsProdID,nsTriggerProdID,nsOptID,nsEmail,nsDate) VALUES ('".escape_string(@$_GET['pid'])."','".escape_string(@$_GET['tpid'])."',".$oid.",'".escape_string(@$_GET['email'])."','".date('Y-m-d')."')") or print(mysql_error());
	print 'SUCCESS';
}elseif(@$_GET['action']=='clord'){
	if(@$closeorderimmediately){
		$thesessionid=@$_SESSION['sessionid'];		
		$sSQL = "SELECT ordID FROM orders WHERE ordStatus>1 AND ordAuthNumber='' AND " . getordersessionsql();
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)) $orderid=$rs['ordID']; else $orderid='';
		mysql_free_result($result);
		if($orderid!=''){
			mysql_query("UPDATE cart SET cartCompleted=2 WHERE cartCompleted=0 AND cartOrderID=".$orderid) or print(mysql_error());
			mysql_query("UPDATE orders SET ordAuthNumber='CHECK MANUALLY' WHERE ordID=".$orderid) or print(mysql_error());
		}
	}
}elseif(@$_GET['action']=='centinel2'){
	$signatureverify='';
	$_SESSION['ErrorDesc']='';
	$_SESSION['PAResStatus']='';
	$sXML = '<CardinalMPI>' .
		addtag('Version','1.7') .
		addtag('MsgType','cmpi_authenticate') .
		addtag('ProcessorId',$cardinalprocessor) .
		addtag('MerchantId',$cardinalmerchant) .
		addtag('TransactionPwd',$cardinalpwd) .
		addtag('TransactionType','C') .
		addtag('TransactionId',@$_SESSION['cardinal_transaction']) .
		addtag('OrderId',@$_SESSION['cardinal_orderid']) .
		addtag('PAResPayload',@$_POST['PaRes']) . '</CardinalMPI>';
	$theurl='https://'.(@$_SESSION['cardinal_method']=='7'||@$_SESSION['cardinal_method']=='18'?'paypal':'centinel400').'.cardinalcommerce.com/maps/txns.asp';
	if(@$cardinaltestmode) $theurl='https://centineltest.cardinalcommerce.com/maps/txns.asp';
	if(@$cardinalurl!='') $theurl=$cardinalurl;
	if(callcurlfunction($theurl, 'cmpi_msg=' . urlencode($sXML), $res, '', $errormsg, 12)){
		$xmlDoc = new vrXMLDoc($res);
		$nodeList = $xmlDoc->nodeList->childNodes[0];
		for($i = 0; $i < $nodeList->length; $i++){
			if($nodeList->nodeName[$i]=='PAResStatus') $_SESSION['PAResStatus']=$nodeList->nodeValue[$i];
			if($nodeList->nodeName[$i]=='SignatureVerification') $signatureverify=$nodeList->nodeValue[$i];
			if($nodeList->nodeName[$i]=='Cavv') $_SESSION['Cavv']=$nodeList->nodeValue[$i];
			if($nodeList->nodeName[$i]=='EciFlag') $_SESSION['EciFlag']=$nodeList->nodeValue[$i];
			if($nodeList->nodeName[$i]=='Xid') $_SESSION['Xid']=$nodeList->nodeValue[$i];
			if($nodeList->nodeName[$i]=='ErrorDesc') $_SESSION['ErrorDesc']=$nodeList->nodeValue[$i];
		}
	}
	if(@$signatureverify!='Y' || @$_SESSION['PAResStatus']=='N') $_SESSION['centinelok']='N'; else $_SESSION['centinelok']='Y';
?>
<html><head>
<script language="javascript" type="text/javascript">
function onLoadHandler(){document.frmLaunchACS.submit();}
</script>
</head>
<body onload="onLoadHandler();">
<center>
<form name="frmLaunchACS" method="post" action="<?php print $storeurl?>cart.php" target="_top">
<noscript>
<p>&nbsp;</p>
<h1>Processing your transaction</h1>
<?php print '<p>'.$xxNoJS.'</p><p>'.$xxMstClk.'</p>'?><p>&nbsp;</p>
<input type="submit" value="<?php print $xxSubmt?>"></center>
</noscript>
<input type="hidden" name="mode" value="authorize" />
<input type="hidden" name="method" value="<?php print @$_SESSION['cardinal_method']?>" />
<input type="hidden" name="ordernumber" value="<?php print @$_SESSION['cardinal_ordernum']?>" />
<input type="hidden" name="sessionid" value="<?php print @$_SESSION['cardinal_sessionid']?>" />
</form></center></body></html>
<?php
}elseif(@$_GET['action']=="centinel"){
?>
<html><head>
<script language="javascript" type="text/javascript">
function onLoadHandler(){document.frmLaunchACS.submit();}
</script>
</head>
<body onload="onLoadHandler();">
<center>
<form name="frmLaunchACS" method="post" action="<?php print str_replace('"', '', @$_GET['url'])?>">
<noscript>
<p>&nbsp;</p>
<h1>Processing your transaction</h1>
<?php print '<p>'.$xxNoJS.'</p><p>'.$xxMstClk.'</p>'?><p>&nbsp;</p>
<input type="submit" value="<?php print $xxSubmt?>"></center>
</noscript>
<input type="hidden" name="PaReq" value="<?php print str_replace('"', '&quot;', @$_SESSION['cardinal_pareq'])?>">
<input type="hidden" name="TermUrl" value="<?php print $storeurl.'vsadmin/ajaxservice.php?action=centinel2'?>">
<input type="hidden" name="MD" value="">
</form></center></body></html>
<?php
}elseif(@$_GET['action']=='applycert'){
	if(@$_SESSION['lastcertapplied']!='') $lastapplied = time() - $_SESSION['lastcertapplied']; else $lastapplied = 1000;
	$_SESSION['lastcertapplied'] = time();
	$rgcpncode = @$_GET['cpncode'];
	if($lastapplied < 3 && @$_GET['act']!='delete'){
		print $xxFldCnt;
	}elseif($rgcpncode!=''){
		$gotcpncode = FALSE;
		print '<table border="0"><tr><td align="center" colspan="3">';
		if(@$_GET['act']=='delete'){
			$gotcpncode = TRUE;
			$_SESSION['giftcerts'] = str_replace(trim(str_replace("'","",$rgcpncode)) . " ", "", @$_SESSION['giftcerts']);
			$_SESSION['cpncode'] = str_replace(trim(str_replace("'","",$rgcpncode)) . " ", "", @$_SESSION['cpncode']);
			print '<strong>' . $xxCpGcDl. '</strong>';
		}else{
			$sSQL = "SELECT gcID FROM giftcertificate WHERE gcRemaining>0 AND gcAuthorized<>0 AND gcID='" . escape_string($rgcpncode) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				if(strpos(@$_SESSION['giftcerts'], $rs['gcID'] . ' ')===FALSE){
					@$_SESSION['giftcerts'] .= $rs['gcID'] . ' ';
					print '<strong>' . $xxGcApld. '</strong>';
				}else
					print '<strong>' . $xxGcAlAp. '</strong>';
				$gotcpncode = TRUE;
			}
			mysql_free_result($result);
		}
		if(! $gotcpncode){
			$sSQL = "SELECT cpnID,cpnNumber FROM coupons WHERE cpnIsCoupon=1 AND cpnNumAvail>0 AND cpnEndDate>='" . date('Y-m-d',time()) ."' AND cpnNumber='" . escape_string($rgcpncode) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				if(strpos(@$_SESSION['cpncode'], $rs['cpnNumber'] . ' ')===FALSE){
					if(trim(@$_SESSION['cpncode'])==''){
						@$_SESSION['cpncode'] .= $rs['cpnNumber'] . ' ';
						print '<strong>' . $xxCpnApd. '</strong>';
					}else
						print '<strong>' . $xxOnOnCp. '</strong>';
				}else
					print '<strong>' . $xxCpAlAp. '</strong>';
			}else
				print '<strong>' . $xxGCCNoF. '</strong>';
			mysql_free_result($result);
		}
		print '</td></tr>';
		if(@$_SESSION['giftcerts']!='' || @$_SESSION['cpncode']!=''){
			$gcarr = explode(' ', trim(@$_SESSION['giftcerts']));
			foreach($gcarr as $key => $value){
				if($value!='') print '<tr><td align="right">' . $xxAppGC . ':</td><td>' . $value . '</td><td>(<a href="#" onclick="removecert(\''.$value.'\')"><strong>'.$xxRemove.'</strong></a>)</td></tr>';
			}
			$cpnarr = explode(' ', trim(@$_SESSION['cpncode']));
			foreach($cpnarr as $key => $value){
				if($value!='') print '<tr><td align="right">' . $xxApdCpn . ':</td><td>' . $value . '</td><td>(<a href="#" onclick="removecert(\''.$value.'\')"><strong>'.$xxRemove.'</strong></a>)</td></tr>';
			}
		}
		print '</table>';
	}
}elseif(@$_GET['action']=='checkupdates'){
	$success = TRUE;
	$str = "?versions=true&format=PHP&plusver=".urlencode(@$_GET['storever']);
	if(@$usecurlforfsock){
		if(@$pathtocurl != ""){
			exec($pathtocurl . ' --data-binary \'X\' http://www.ecommercetemplates.com/updaterversions.asp' . $str, $res, $retvar);
			$sXML = implode("\n",$res);
			if(trim($sXML)=='') $success=FALSE;
		}else{
			if (!$ch = curl_init()) {
				$success = FALSE;
				$errormsg = "cURL package not installed in PHP";
			}else{
				curl_setopt($ch, CURLOPT_URL,'http://www.ecommercetemplates.com/updaterversions.asp' . $str); 
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "X");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				if(@$curlproxy!=''){
					curl_setopt($ch, CURLOPT_PROXY, $curlproxy);
				}
				$sXML = curl_exec($ch);
				if(curl_error($ch) != ""){ $success = FALSE; $errormsg = curl_error($ch); }
				curl_close($ch);
			}
		}
	}else{
		$header = "POST /updaterversions.asp" . $str . " HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: 1\r\n\r\n";
		$fp = @fsockopen('www.ecommercetemplates.com', 80, $errno, $errstr, 30);
		if (!$fp){
			$errormsg = $errstr .' (' . $errno . ')'; // HTTP error handling
			$success = FALSE;
		}else{
			fputs ($fp, $header . "X");
			$sXML="";
			while (!feof($fp))
				$sXML .= fgets ($fp, 1024);
		}
	}
	if(! $success){
		$recommendedversion = $errormsg . " : " . $yyCdNoCo;
		$securityrelease = 'false';
		$shouldupdate = 'true';
		$xxhaserror = 'true';
	}else{
		$xmlDoc = new vrXMLDoc($sXML);
		$nodeList = $xmlDoc->nodeList->childNodes[0];
		$recommendedversion = $nodeList->getValueByTagName('recommendedversion');
		$securityrelease = ($nodeList->getValueByTagName('securityrelease')=='true');
		$shouldupdate = ($nodeList->getValueByTagName('shouldupdate')=='true');
		$xxhaserror = 'false';
		$sSQL = "UPDATE admin SET updLastCheck='".date('Y-m-d')."',updRecommended='".escape_string($recommendedversion)."',updSecurity=".($securityrelease?"1":"0").",updShouldUpd=".($shouldupdate?"1":"0")." WHERE adminID=1";
		mysql_query($sSQL) or print(mysql_error());
	}
	header('Content-Type: text/xml');
	print '<?xml version="1.0"?><updaterresults><recommendedversion>'.$recommendedversion.'</recommendedversion><securityupdate>'.($securityrelease?'true':'false').'</securityupdate><shouldupdate>'.($shouldupdate?'true':'false').'</shouldupdate><haserror>'.$xxhaserror.'</haserror></updaterresults>';
}elseif(@$_GET['action']=='getlist'){
	$rc=0;
	if(@$maxadminlookup=='') $maxadminlookup=50;
	if(@$_GET['listtype']!='adddets') print @$_GET['objid'].'==LISTOBJ==';
	$listtext=@$_POST['listtext'];
	if(@$_GET['listtype']=='adddets'){
		$actarr=explode('|',$listtext);
		if($actarr[0]=='0'){
			print $actarr[1] . '==LISTELM==';
			$sSQL="SELECT clID,clUserName FROM customerlogin WHERE clID=".$actarr[1];
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs=mysql_fetch_assoc($result)){
				if(@$usefirstlastname){
					$ordName=trim($rs['clUserName']);
					$ordLastName='';
					if(strstr($thename,' ')){
						$namearr = explode(' ',$thename,2);
						$ordName = $namearr[0];
						$ordLastName = $namearr[1];
					}
					print jsenc($ordName) . '==LISTELM==' . jsenc($ordLastName);
				}else
					print jsenc($rs['clUserName']) . '==LISTELM==';
			}
			mysql_free_result($result);
		}elseif($actarr[0]=='1' || $actarr[0]=='2'){
			if($actarr[0]=='1')
				$sSQL = "SELECT addCustID,addName,addLastName,addAddress,addAddress2,addCity,addState,addZip,addCountry,addPhone,addExtra1,addExtra2 FROM address WHERE addID=".$actarr[1];
			else
				$sSQL = "SELECT ordID AS addCustID,ordName AS addName,ordLastName AS addLastName,ordAddress AS addAddress,ordAddress2 AS addAddress2,ordCity AS addCity,ordState AS addState,ordZip AS addZip,ordCountry AS addCountry,ordPhone AS addPhone,ordExtra1 AS addExtra1,ordExtra2 AS addExtra2 FROM orders WHERE ordID=".$actarr[1];
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs=mysql_fetch_assoc($result)){
				print $rs['addCustID'] . '==LISTELM==' . jsenc($rs['addName']) . '==LISTELM==' . jsenc($rs['addLastName']) . '==LISTELM==';
				print jsenc($rs['addAddress']) . '==LISTELM==' . jsenc($rs['addAddress2']) . '==LISTELM==';
				print jsenc($rs['addCity']) . '==LISTELM==' . jsenc($rs['addState']) . '==LISTELM==';
				print jsenc($rs['addZip']) . '==LISTELM==' . jsenc($rs['addCountry']) . '==LISTELM==';
				print jsenc($rs['addPhone']) . '==LISTELM==';
				print jsenc($rs['addExtra1']) . '==LISTELM==' . jsenc($rs['addExtra2']);
			}
			mysql_free_result($result);
		}
	}elseif(@$_GET['listtype']=='email'){
		// noaddress=0 addresstable=1 orderstable=2 | clid or addid or ordid
		$gotresults=FALSE;
		if($rc<10){
			$sSQL="SELECT clID,clEmail,clUserName,addName,addLastName,addAddress,addAddress2,addID,addCity,addState,addZip,addCountry FROM customerlogin INNER JOIN address ON customerlogin.clID=address.addCustID WHERE ";
			$sSQL .="clEmail LIKE '".escape_string($listtext)."%' ";
			$sSQL .='ORDER BY clEmail LIMIT 0,20';
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0 && $gotresults){ print jsenc('----------------').'==LISTELM=='.jsenc('----------------').'==LISTOBJ=='; $gotresults=FALSE;}
			while($rs=mysql_fetch_assoc($result)){
				$theaddress = $rs['clEmail'].' / '.trim($rs['addName'].' '.$rs['addLastName']).' - '.$rs['addAddress'];
				if(trim($rs['addAddress2'])!='') $theaddress .=  ', ' . $rs['addAddress2'];
				$theaddress .=  ', ' . $rs['addState'] . ', ' . $rs['addZip'] . ', ' . $rs['addCountry'];
				$thecode = '1|'.$rs['addID'];
				print jsenc($rs['clEmail']).'==LISTELM=='.jsenc($theaddress).'==LISTELM=='.$thecode.'==LISTOBJ==';
				$rc++;
				$gotresults=TRUE;
			}
			mysql_free_result($result);
		}
		if($rc<20){
			$sSQL="SELECT DISTINCT MAX(ordID) AS ordID,ordEmail,ordName,ordLastName,ordAddress,ordAddress2,ordState,ordZip,ordCountry FROM orders WHERE ";
			$sSQL .="ordEmail LIKE '".escape_string($listtext)."%' ";
			$sSQL .='GROUP BY ordEmail,ordName,ordLastName,ordAddress,ordAddress2,ordState,ordZip,ordCountry ORDER BY ordEmail LIMIT 0,20';
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0 && $gotresults){ print jsenc('----------------').'==LISTELM=='.jsenc('----------------').'==LISTOBJ=='; $gotresults=FALSE;}
			while($rs=mysql_fetch_assoc($result)){
				$theaddress = $rs['ordEmail'].' / '.trim($rs['ordName'].' '.$rs['ordLastName']).' - '.$rs['ordAddress'];
				if(trim($rs['ordAddress2'])!='') $theaddress .=  ', ' . $rs['ordAddress2'];
				$theaddress .=  ', ' . $rs['ordState'] . ', ' . $rs['ordZip'] . ', ' . $rs['ordCountry'];
				$thecode = '2|'.$rs['ordID'];
				print jsenc($rs['ordEmail']).'==LISTELM=='.jsenc($theaddress).'==LISTELM=='.$thecode.'==LISTOBJ==';
				$rc++;
				$gotresults=TRUE;
			}
			mysql_free_result($result);
		}
		if($rc<40){
			$sSQL='SELECT clID,clEmail,clUserName FROM customerlogin WHERE ';
			$sSQL .="clEmail LIKE '".escape_string($listtext)."%' ";
			$sSQL .='ORDER BY clEmail LIMIT 0,10';
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0 && $gotresults){ print jsenc('----------------').'==LISTELM=='.jsenc('----------------').'==LISTOBJ=='; $gotresults=FALSE;}
			while($rs=mysql_fetch_assoc($result)){
				$theaddress = $rs['clEmail'].' / '.$rs['clUserName'];
				$thecode = '0|'.$rs['clID'];
				print jsenc($rs['clEmail']).'==LISTELM=='.jsenc($theaddress).'==LISTELM=='.$thecode.'==LISTOBJ==';
				$rc++;
				$gotresults=TRUE;
			}
			mysql_free_result($result);
		}
	}elseif(@$_GET['listtype']=='prodid' OR @$_GET['listtype']=='prodname'){
		$sSQL='SELECT pID,pName FROM products WHERE ';
		if(@$_GET['listtype']=='prodname')
			$sSQL .="pName LIKE '".escape_string($listtext)."%' ORDER BY pName";
		else
			$sSQL .="pID LIKE '".escape_string($listtext)."%' ORDER BY pID";
		$sSQL .=' LIMIT 0,'.$maxadminlookup;
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs=mysql_fetch_assoc($result)){
			print jsenc($rs['pID']).'==LISTELM=='.jsenc($rs['pName']).'==LISTOBJ==';
			$rc++;
		}
		mysql_free_result($result);
		if($maxadminlookup-30 > $rc){
			$sSQL='SELECT pID,pName FROM products WHERE ';
			if(@$_GET['listtype']=='prodname')
				$sSQL .="pName LIKE '%".escape_string($listtext)."%' AND NOT (pName LIKE '".escape_string($listtext)."%') ORDER BY pName";
			else
				$sSQL .="pID LIKE '%".escape_string($listtext)."%' AND NOT (pID LIKE '".escape_string($listtext)."%') ORDER BY pID";
			$sSQL .=' LIMIT 0,'.$maxadminlookup;
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0 && $rc!=0) print jsenc('----------------').'==LISTELM=='.jsenc('----------------').'==LISTOBJ==';
			while($rs=mysql_fetch_assoc($result)){
				print jsenc($rs['pID']).'==LISTELM=='.jsenc($rs['pName']).'==LISTOBJ==';
				$rc++;
			}
			mysql_free_result($result);
		}
	}
}elseif(trim(@$_GET['action'])=='updateoptions'){
	$byoptions=FALSE;
	$optstockjs='';
	$id = @$_GET['index'];
	$productid = @$_POST['productid'];
	if(strtolower($adminencoding)!='utf-8') $productid=utf8_decode($productid);
	$sSQL = 'SELECT ' . getlangid('pName',1) . ",pPrice,pStockByOpts,pInStock,pExemptions FROM products WHERE pID='" . escape_string($productid) . "'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs=mysql_fetch_assoc($result)){
		$prodname=$rs[getlangid('pName',1)];
		$prodprice=$rs['pPrice'];
		if($rs['pStockByOpts']!=0){ $prodstock = "'bo'"; $byoptions=TRUE; } else $prodstock = (is_null($rs['pInStock'])?'0':$rs['pInStock']);
		$prodexemptions=$rs['pExemptions'];
	}else{
		$prodname='Not Found: ' . $productid;
		$prodprice=0;
		$prodstock="''";
		$prodexemptions=0;
	}
	mysql_free_result($result);
	$opttext='';
	$sSQL = "SELECT poOptionGroup,optType,optFlags FROM prodoptions INNER JOIN optiongroup ON optiongroup.optGrpID=prodoptions.poOptionGroup WHERE poProdID='" . escape_string($productid) . "' ORDER BY poID";
	$prodoptions = mysql_query($sSQL) or print(mysql_error());
	$jstext='';
	if(mysql_num_rows($prodoptions)==0){
		$opttext.='-';
	}else{
		$rowcounter=0;
		$opttext.='<table border="0" cellspacing="0" cellpadding="1" width="100%">';
		while($theopt = mysql_fetch_assoc($prodoptions)){
			$index=0;
			$sSQL="SELECT optID," . getlangid('optName',32) . "," . getlangid('optGrpName',16) . ',optPriceDiff,optType,optFlags,optStock,optTxtCharge,optPriceDiff AS optDims FROM options INNER JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optGroup=' . $theopt['poOptionGroup'] . ' ORDER BY optID';
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs2=mysql_fetch_array($result)){
				if(abs((int)$rs2['optType'])==3){
					$opttext.= '<tr><td align="right" width="30%"><strong>' . $rs2[getlangid('optGrpName',16)] . ':</strong></td><td align="left"> <input type="hidden" name="optn' . $id . '_' . $rs2['optID'] . '" value="' . $rs2['optID'] . '" />';
					if($rs2['optTxtCharge']!=0) $jstext.='opttxtcharge[' . $rs2['optID'] . ']=' . $rs2['optTxtCharge'] . ';';
					$opttext.= '<textarea wrap="virtual" name="voptn' . $id . '_' . $rs2['optID'] . '" id="voptn' . $id . '_' . $rs2['optID'] . '" cols="30" rows="3">';
					$opttext.= $rs2[getlangid('optName',32)] . '</textarea>';
					$opttext.= '</td></tr>';
				}else{
					$opttext.= '<tr><td align="right" width="30%"><strong>' . $rs2[getlangid('optGrpName',16)] . ':</strong></td><td align="left"> <select class="prodoption" onchange="dorecalc(true)" name="optn' . $id . '_' . $rowcounter . '" id="optn' . $id . '_' . $rowcounter . '" size="1">';
					$opttext.= "<option value=''>".$xxPlsSel.'</option>';
					do{
						$opttext.= "<option value='" . $rs2['optID'] . '|' . (($rs2['optFlags'] & 1) == 1 ? ($prodprice*$rs2['optPriceDiff'])/100.0 : $rs2['optPriceDiff']) . "'>" . $rs2[getlangid('optName',32)];
						if((double)($rs2['optPriceDiff']) != 0){
							$opttext.= ' ';
							if((double)($rs2['optPriceDiff']) > 0) $opttext.= '+';
							if(($rs2['optFlags']&1)==1)
								$opttext.= number_format(($prodprice*$rs2['optPriceDiff'])/100.0,2,'.','');
							else
								$opttext.= number_format($rs2['optPriceDiff'],2,'.','');
						}
						$opttext.= "</option>\r\n";
						if($byoptions){
							$optstockjs .= "if(typeof(stock['oid_".$rs2['optID']."'])==\"undefined\"){";
							$optstockjs .= "stock['oid_".$rs2['optID']."']=".$rs2['optStock'].";";
							$optstockjs .= '}';
						}
					} while($rs2=mysql_fetch_array($result));
					$opttext.= '</select></td></tr>';
				}
			}
			mysql_free_result($result);
			$rowcounter++;
		}
		$opttext.= '</table>';
	}
	mysql_free_result($prodoptions);
	$jstext.="document.getElementById('prodname".$id."').value = '".str_replace("'","\\'",$prodname)."';";
	$jstext.="document.getElementById('price".$id."').value = '".$prodprice."';";
	$jstext.="document.getElementById('stateexempt".$id."').value = '".(($prodexemptions & 1)==1?'true':'false')."';";
	$jstext.="document.getElementById('countryexempt".$id."').value = '".(($prodexemptions & 2)==2?'true':'false')."';";
	$jstext.="document.getElementById('optdiffspan".$id."').value = 0;";
	$jstext.="if(typeof(stock['pid_".str_replace("'", '', $productid)."'])=='undefined'){";
	$jstext.="stock['pid_".str_replace("'", '', $productid)."']=".$prodstock.";";
	$jstext.=$optstockjs."}";
	print $id.'==LISTELM=='.jsenc($opttext).'==LISTELM=='.jsenc($jstext);
}elseif(@$_GET['processor'] == 'Google'){
	$ordID = str_replace("'",'',@$_GET['gid']);
	$sSQL = "SELECT ordPayProvider,ordAuthNumber,payProvData1,payProvData2,payProvDemo FROM orders INNER JOIN payprovider ON orders.ordPayProvider=payprovider.payProvID WHERE ordID='" . escape_string($ordID) . "'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		$authcode=$rs['ordAuthNumber'];
		$googledata1=$rs['payProvData1'];
		$googledata2=$rs['payProvData2'];
		$googledemomode=$rs['payProvDemo'];
	}
	if(@$_GET['act']=='charge'){
		// First set the status to process-order
		sendmessagewithbasicauth('<process-order xmlns="http://checkout.google.com/schema/2" google-order-number="' . $authcode . '"/>');

		$acttext = '<charge-order xmlns="http://checkout.google.com/schema/2" google-order-number="' . $authcode . '"></charge-order>';
	}elseif(@$_GET['act']=='cancel')
		$acttext = '<cancel-order xmlns="http://checkout.google.com/schema/2" google-order-number="' . $authcode . '"><reason>Cancelled by store admin on ' . date('F d Y H:i:s') . '.</reason></cancel-order>';
	elseif(@$_GET['act']=='refund')
		$acttext = '<refund-order xmlns="http://checkout.google.com/schema/2" google-order-number="' . $authcode . '"><reason>Refunded by store admin on ' . date('F d Y H:i:s') . '.</reason></refund-order>';
	elseif(@$_GET['act']=='ship'){
		// First set the status to process-order
		sendmessagewithbasicauth('<process-order xmlns="http://checkout.google.com/schema/2" google-order-number="' . $authcode . '"/>');

		$acttext = '<deliver-order xmlns="http://checkout.google.com/schema/2" google-order-number="' . $authcode . '">';
		if(@$_GET['carrier'] != '' && @$_GET['trackno'] != ''){
			$sSQL = "UPDATE orders SET ordTrackNum='" . escape_string($_GET['trackno']) . "',ordShipCarrier=" . escape_string(@$_GET['carrier']) . " WHERE ordID='" . escape_string($ordID) . "'";
			mysql_query($sSQL) or print(mysql_error());
			$acttext .= '<tracking-data><carrier>';
			switch($_GET['carrier']){
				case "3":
					$acttext .= "USPS";
				break;
				case "4":
					$acttext .= "UPS";
				break;
				case "7":
					$acttext .= "FedEx";
				break;
				case "8":
					$acttext .= "DHL";
				break;
				default:
					$acttext .= "Other";
			}
			$acttext .= '</carrier><tracking-number>' . trim($_GET['trackno']) . '</tracking-number></tracking-data>';
		}
		$acttext .= '</deliver-order>';
	}elseif(@$_GET['act']=='message'){
		// First set the status to process-order
		sendmessagewithbasicauth('<process-order xmlns="http://checkout.google.com/schema/2" google-order-number="' . $authcode . '"/>');
		
		$acttext = '<send-buyer-message xmlns="http://checkout.google.com/schema/2" google-order-number="' . $authcode . '"><message>' . @$_POST['googlemessage'] . '</message><send-email>true</send-email></send-buyer-message>';
	}
	
	$cfres = sendmessagewithbasicauth($acttext);
	
	if(! $success){
		print '<span style="color:#FF0000">' . "Error, couldn't update order " . $ordID . '</span><br/>';
	}else{
		$xmlDoc = new vrXMLDoc($cfres);
		$nodeList = $xmlDoc->nodeList->childNodes[0];
		if(($errmsg = $nodeList->getValueByTagName('error-message')) != null)
			print '<span style="color:#FF0000">' . $errmsg . '</span><br/>';
		else
			print 'Finished updating order ' . $ordID;
	}
}elseif(@$_GET['processor']=='PayPal'){
	$ordID = str_replace("'",'',@$_GET['gid']);
	$sSQL = "SELECT ordPayProvider,ordAuthNumber,ordTransID FROM orders WHERE ordID='".escape_string($ordID)."'";
	$result = mysql_query($sSQL) or print(mysql_error());
	$rs = mysql_fetch_array($result);
	$authcode=$rs['ordAuthNumber'];
	$transid=$rs['ordTransID'];
	if($success = getpayprovdetails(19,$username,$password,$data3,$demomode,$ppmethod)){
		$data2arr = explode('&',$password);
		$password=urldecode(@$data2arr[0]);
		$isthreetoken=(trim(urldecode(@$data2arr[2]))=='1');
		$signature=''; $sslcertpath='';
		if($isthreetoken) $signature=urldecode(@$data2arr[1]); else $sslcertpath=urldecode(@$data2arr[1]);
	}
	if($demomode) $sandbox = '.sandbox'; else $sandbox = '';
	if(! $success){
		print 'username / pw not set for express checkout';
	}else{
		if(@$_GET['act']=='charge'){
			$sXML = ppsoapheader($username, $password, $signature) . '<soap:Body><DoCaptureReq xmlns="urn:ebay:api:PayPalAPI">' .
				'<DoCaptureRequest xmlns="urn:ebay:api:PayPalAPI">' .
					'<Version xmlns="urn:ebay:apis:eBLBaseComponents" xsi:type="xsd:string">1.0</Version>' .
					'<AuthorizationID>' . $authcode . '</AuthorizationID>' .
					'<Amount currencyID="' . $countryCurrency . '" xsi:type="cc:BasicAmountType">' . $_POST['amount'] . '</Amount>' .
					'<CompleteType>' . (@$_POST['additionalcapture']=='1' ? 'NotComplete' : 'Complete') . '</CompleteType>' .
					'<Note>' . @$_POST['comments'] . '</Note>' .
				'</DoCaptureRequest></DoCaptureReq></soap:Body></soap:Envelope>';
		}elseif(@$_GET['act']=='void'){
			$sXML = ppsoapheader($username, $password, $signature) . '<soap:Body><DoVoidReq xmlns="urn:ebay:api:PayPalAPI">' .
				'<DoVoidRequest xmlns="urn:ebay:api:PayPalAPI">' .
					'<Version xmlns="urn:ebay:apis:eBLBaseComponents" xsi:type="xsd:string">1.0</Version>' .
					'<AuthorizationID>' . $authcode . '</AuthorizationID>' .
					'<Note>' . @$_POST['comments'] . '</Note>' .
				'</DoVoidRequest></DoVoidReq></soap:Body></soap:Envelope>';
		}elseif(@$_GET['act']=='reauth'){
			$sXML = ppsoapheader($username, $password, $signature) . '<soap:Body><DoReauthorizationReq xmlns="urn:ebay:api:PayPalAPI">' .
				'<DoReauthorizationRequest xmlns="urn:ebay:api:PayPalAPI">' .
					'<Version xmlns="urn:ebay:apis:eBLBaseComponents" xsi:type="xsd:string">1.0</Version>' .
					'<AuthorizationID>' . $authcode . '</AuthorizationID>' .
					'<Amount currencyID="' . $countryCurrency . '" xsi:type="cc:BasicAmountType">' . @$_POST['amount'] . '</Amount>' .
					'<Note>' . @$_POST['comments'] . '</Note>' .
				'</DoReauthorizationRequest></DoReauthorizationReq></soap:Body></soap:Envelope>';
		}
		if(callcurlfunction('https://api-aa' . ($sandbox=='' && $isthreetoken ? '-3t' : '') . $sandbox . '.paypal.com/2.0/', $sXML, $res, $sslcertpath, $errormsg, FALSE)){
			$success=FALSE;$vsERRCODE='';$vsRESPMSG='';$vsTRANSID='';
			$xmlDoc = new vrXMLDoc($res);
			$nodeList = $xmlDoc->nodeList->childNodes[0];
			for($i = 0; $i < $nodeList->length; $i++){
				if($nodeList->nodeName[$i]=="SOAP-ENV:Body"){
					$e = $nodeList->childNodes[$i];
					for($j = 0; $j < $e->length; $j++){
						if($e->nodeName[$j] == "DoCaptureResponse" || $e->nodeName[$j] == "DoVoidResponse" || $e->nodeName[$j] == "DoReauthorizationResponse"){
							$ee = $e->childNodes[$j];
							for($jj = 0; $jj < $ee->length; $jj++){
								if($ee->nodeName[$jj] == 'Ack'){
									if($ee->nodeValue[$jj]=='Success' || $ee->nodeValue[$jj]=='SuccessWithWarning'){
										$success=TRUE;
										$vsRESPMSG = 'Success';
									}
								}elseif($ee->nodeName[$jj] == "Errors"){
									$ff = $ee->childNodes[$jj];
									for($kk = 0; $kk < $ff->length; $kk++){
										if($ff->nodeName[$kk] == "LongMessage"){
											$themsg=$ff->nodeValue[$kk];
										}elseif($ff->nodeName[$kk] == "ErrorCode"){
											$vsERRCODE=$ff->nodeValue[$kk];
										}elseif($ff->nodeName[$kk] == "SeverityCode"){
											$iswarning=($ff->nodeValue[$kk]=='Warning');
										}
									}
									if(! $iswarning)
										$vsRESPMSG = $themsg . '<br />' . $vsRESPMSG;
								}elseif($ee->nodeName[$jj] == "DoCaptureResponseDetails"){
									$vsTRANSID=$ee->getValueByTagName('TransactionID');
								}
							}
						}
					}
				}
			}
			if($success){
				if(@$_GET['act']=='charge'){
					mysql_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID=". $ordID) or print(mysql_error());
					mysql_query("UPDATE orders SET ordTransID='" . $vsTRANSID . "' WHERE ordID=". $ordID) or print(mysql_error());
					mysql_query("UPDATE orders SET ordStatus=3,ordStatusDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "' WHERE ordStatus<3 AND ordID=". $ordID) or print(mysql_error());
				}elseif(@$_GET['act']=='void'){
					mysql_query("UPDATE orders SET ordTransID='void' WHERE ordID=". $ordID) or print(mysql_error());
					mysql_query("UPDATE orders SET ordStatus=0,ordStatusDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "' WHERE ordID=". $ordID) or print(mysql_error());
				}
				print $vsRESPMSG;
			}elseif($vsERRCODE != ''){
				print $vsRESPMSG . ' (' . $vsERRCODE . ')';
			}
		}else{
			print $errormsg;
		}
	}
}elseif(@$_GET['processor']=='Amazon'){
	function amazonparam($nam, $val){
		global $urlstr,$signaturestr;
		$urlstr .= '&' . $nam . '=' . urlencode($val);
		$signaturestr .= $nam . $val;
	}
	$ordID = str_replace("'",'',@$_GET['gid']);
	$sSQL = "SELECT ordPayProvider,ordAuthNumber,payProvData1,payProvData2,payProvDemo,ordStatusInfo FROM orders INNER JOIN payprovider ON orders.ordPayProvider=payprovider.payProvID WHERE ordPayProvider=21 AND ordID='".escape_string($ordID)."'";
	$result = mysql_query($sSQL) or print(mysql_error());
	$rs = mysql_fetch_array($result);
	$authcode=$rs['ordAuthNumber'];
	$data1=$rs['payProvData1'];
	$data2=$rs['payProvData2'];
	$demomode=$rs['payProvDemo'];
	$statusinfo=$rs['ordStatusInfo'];
	mysql_free_result($result);
	if(@$_GET['act']=='settle'){
		$urlstr = 'Action=Settle';
		$signaturestr = 'ActionSettle';
		amazonparam('AWSAccessKeyId',$data1);
		amazonparam('SignatureVersion','1');
		amazonparam('Timestamp',gmdate('Y-m-d\TH:i:s\Z'));
		amazonparam('TransactionId', $authcode);
		$signature = base64_encode(CalcHmacSha1($signaturestr,$data2));
		$urlstr .= '&Signature=' . urlencode($signature);

		$theurl='https://fps.'.($demomode ? 'sandbox.' : '').'amazonaws.com/paynow/';
		// print $theurl . '?' . $urlstr . '<br>';
		if(!callcurlfunction($theurl . '?' . $urlstr, '', $res, '', $errormsg, FALSE)){
			print '<span style="color:#FF0000">' . "Error, couldn't update order " . $ordID . '</span><br/>';
		}else{
			// print str_replace('<','<br />&lt;',str_replace('</','&lt;/',$res)) . "<br />\n";
			$xmlDoc = new vrXMLDoc($res);
			$nodeList = $xmlDoc->nodeList->childNodes[0];
			$errnode = $nodeList->getValueByTagName('Error');
			if($errnode!=''){
				$errormsg = $nodeList->getValueByTagName('Message');
				print '<span style="color:#FF0000">' . $errormsg . '</span>';
			}else{
				$transstat = $nodeList->getValueByTagName('ns1:TransactionStatus');
				if($transstat!=''){
					print $transstat;
					if($transstat=='Success')
						mysql_query("UPDATE orders SET ordAuthStatus='',ordStatusDate='" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "' WHERE ordPayProvider=21 AND ordID='".escape_string($ordID)."'") or print(mysql_error());
					elseif($transstat=='Initiated')
						mysql_query("UPDATE orders SET ordAuthStatus='Pending: Settle Initiated',ordStatusDate='" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "' WHERE ordPayProvider=21 AND ordID='".escape_string($ordID)."'") or print(mysql_error());
				}
				$pendreason = $nodeList->getValueByTagName('ns1:PendingReason');
				if($pendreason!=''){
					print ' : ' . $pendreason;
				}
			}
		}
	}elseif(@$_GET['act']=='refund' || @$_GET['act']=='partialrefund'){
		$success=TRUE;
		$theamount=@$_GET['amount'];
		if(! is_numeric($theamount)) $theamount='';
		if(@$_GET['act']=='partialrefund' && $theamount==''){ $success=FALSE; print '<span style="color:#FF0000">' . 'Invalid Amount ' . $theamount . '</span>'; }
		$urlstr = 'Action=Refund';
		$signaturestr = 'ActionRefund';
		amazonparam('AWSAccessKeyId',$data1);
		if(@$_GET['act']=='partialrefund' && $theamount!=''){
			amazonparam('RefundAmount.CurrencyCode',$countryCurrency);
			amazonparam('RefundAmount.Value',$theamount);
		}
		amazonparam('RefundTransactionReference',$authcode.' '.time());
		amazonparam('SignatureVersion','1');
		amazonparam('Timestamp',gmdate('Y-m-d\TH:i:s\Z'));
		amazonparam('TransactionId', $authcode);
		$signature = base64_encode(CalcHmacSha1($signaturestr,$data2));
		$urlstr .= '&Signature=' . urlencode($signature);
		if($success)
			$theurl='https://fps.'.($demomode ? 'sandbox.' : '').'amazonaws.com/paynow/';
		if(! $success){
		}elseif(!callcurlfunction($theurl . '?' . $urlstr, '', $res, '', $errormsg, FALSE)){
			print '<span style="color:#FF0000">' . "Error, couldn't update order " . $ordID . '</span><br/>';
		}else{
			$xmlDoc = new vrXMLDoc($res);
			$nodeList = $xmlDoc->nodeList->childNodes[0];
			$errnode = $nodeList->getValueByTagName('Error');
			if($errnode!=''){
				$errormsg = $nodeList->getValueByTagName('Message');
				print '<span style="color:#FF0000">' . $errormsg . '</span>';
			}else{
				$transstat = $nodeList->getValueByTagName('ns1:TransactionStatus');
				if($transstat!=''){
					print $transstat;
					if(@$_GET['act']=='partialrefund' && ($transstat=='Success' || $transstat=='Initiated'))
						mysql_query("UPDATE orders SET ordStatusInfo='" . escape_string($statusinfo . "\r\n" . 'Partial Refund (' . FormatEuroCurrency($theamount) . ') ' . date('Y-m-d H:i', time() + ($dateadjust*60*60))) . "',ordStatusDate='" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "' WHERE ordPayProvider=21 AND ordID='".escape_string($ordID)."'") or print(mysql_error());
					elseif($transstat=='Success')
						mysql_query("UPDATE orders SET ordStatus=0,ordStatusDate='" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "' WHERE ordPayProvider=21 AND ordID='".escape_string($ordID)."'") or print(mysql_error());
					elseif($transstat=='Initiated')
						mysql_query("UPDATE orders SET ordStatus=0,ordStatusDate='" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "',ordAuthStatus='Pending: Refund Initiated' WHERE ordPayProvider=21 AND ordID='".escape_string($ordID)."'") or print(mysql_error());
				}
				$pendreason = $nodeList->getValueByTagName('ns1:PendingReason');
				if($pendreason!=''){
					print ' : ' . $pendreason;
				}
			}
		}
	}
}
?>