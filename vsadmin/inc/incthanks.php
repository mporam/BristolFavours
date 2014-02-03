<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
include "./vsadmin/inc/incemail.php";
if(@$_SERVER['CONTENT_LENGTH'] != '' && $_SERVER['CONTENT_LENGTH'] > 10000) exit;
$success=FALSE;
$errtext="";
$errormsg="";
$thereference="";
$orderText="";
$ordGrandTotal = $ordTotal = $ordStateTax = $ordHSTTax = $ordCountryTax = $ordShipping = $ordHandling = $ordDiscount = 0;
$ordID = $affilID = $ordCity = $ordState = $ordCountry = $ordDiscountText = $ordEmail = '';
$_SESSION['couponapply']=NULL; unset($_SESSION['couponapply']);
$_SESSION['giftcerts']=NULL; unset($_SESSION['giftcerts']);
$_SESSION['cpncode']=NULL; unset($_SESSION['cpncode']);
$ordAuthNumber='';
function order_failed(){
	global $xxThkErr,$storeurl,$xxCntShp,$errtext,$success;
	$success = FALSE;
?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
            <table width="100%" border="0" cellspacing="3" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><?php print $xxThkErr?>
				<?php if($errtext != "") print "<p><strong>" . $errtext . "</strong></p>" ?>
				<a class="ectlink" href="<?php print $storeurl?>"><strong><?php print $xxCntShp?></strong></a><br />
				<img src="images/clearpixel.gif" width="300" height="3" alt="" />
                </td>
			  </tr>
			</table>
		  </td>
        </tr>
      </table>
<?php
}
$alreadygotadmin = getadminsettings();
if(@$paypalhostedsolution && @$_GET['tx']!=''){
	if(!getpayprovdetails(18,$data1,$data2,$data3,$demomode,$ppmethod)){
		$errtext = 'Payment method not set.';
		order_failed();
	}else{
		$data2arr = explode('&',$data2);
		$data2=urldecode(@$data2arr[0]);
		$isthreetoken=(trim(urldecode(@$data2arr[2]))=='1');
		$signature=''; $sslcertpath='';
		if($isthreetoken) $signature=urldecode(@$data2arr[1]); else $sslcertpath=urldecode(@$data2arr[1]);
		if(strpos($data1,'@AB@')!==FALSE){
			$isthreetoken=TRUE;
			$signature='AB';
		}
		$sXML='PWD=' . $data2 . '&USER=' . $data1 . ($signature!='' ? '&SIGNATURE=' . $signature : '') . '&METHOD=GetTransactionDetails&VERSION=84.0&TRANSACTIONID=' . $_GET['tx'];
		if(callcurlfunction('https://api-3t' . ($demomode ? '.sandbox' : '') . '.paypal.com/nvp', $sXML, $res, $sslcertpath, $errtext, FALSE)){
			$lines = explode('&', $res);
			$payment_status='';
			$pending_reason='';
			$txn_id='';
			for ($i=1; $i<(count($lines)-1);$i++){
				list($key,$val) = explode('=', $lines[$i]);
				if($key=='ACK') $success = ($val=='Success');
				if($key=='PAYMENTSTATUS') $payment_status = $val;
				if($key=='PENDINGREASON') $pending_reason = $val;
				if($key=='CUSTOM') $ordID = str_replace("'",'',$val);
				if($key=='TRANSACTIONID') $txn_id = str_replace("'",'',$val);
				if($key=='L_LONGMESSAGE0') $errtext = urldecode($val);
			}
			if($success){
				$sSQL = "SELECT ordAuthNumber FROM orders WHERE ordPayProvider=1 AND ordStatus>=3 AND ordAuthNumber='" . escape_string($txn_id) . "' AND ordID='" . escape_string($ordID) . "'";
				$result = mysql_query($sSQL) or print(mysql_error());
				$success = FALSE;
				if($rs = mysql_fetch_assoc($result))
					$success = (trim($rs["ordAuthNumber"])!="");
				mysql_free_result($result);
				if($success)
					do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
				else{
					mysql_query("UPDATE cart SET cartCompleted=2 WHERE cartCompleted=0 AND cartOrderID='" . escape_string($ordID) . "'") or print(mysql_error());
					mysql_query("UPDATE orders SET ordAuthNumber='no ipn' WHERE ordAuthNumber='' AND ordPayProvider=1 AND ordID='" . escape_string($ordID) . "'") or print(mysql_error());
					$xxThkErr = '';
					$errtext = '&nbsp;<br />&nbsp;<br />&nbsp;<br />';
					if($payment_status=='Pending')
						$errtext .= $xxPPPend;
					else
						$errtext .= $xxNoCnf . '<br />&nbsp;<br />&nbsp;<br />&nbsp;<br /><input type="button" value="'.$xxClkRel.'" onclick="window.location.reload()" /><br />&nbsp;<br />&nbsp;<br />';
					order_failed();
				}
			}else
				order_failed();
		}
	}
}elseif(@$_GET['amt']!='' && @$_GET['tx']!='' && @$_GET['st']!='' && @$_GET['cc']!='' && @$_GET['cm']!=''){
	$ordID='';
	if(!getpayprovdetails(1,$data1,$data2,$data3,$demomode,$ppmethod)){
		$errtext = 'Payment method not set.';
		order_failed();
	}elseif($data2 == ''){
		$errtext = 'Identity token for PayPal Payment Data Transfer (PDT) not set.';
		order_failed();
	}else{
		$success=TRUE;
		$req = 'cmd=_notify-synch';
		$req .= '&tx=' . $_GET['tx'] . '&at=' . $data2;
		$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		if(TRUE){ // if(@$usecurlforfsock)
			if(!callcurlfunction('https://www' . ($demomode ? '.sandbox' : '') . '.paypal.com/cgi-bin/webscr', $req, $res, '', $errtext, 30)){
				$success=FALSE;
				order_failed();
			}
		}else{
			// $fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
			if($fp = fsockopen('ssl://www' . ($demomode ? '.sandbox' : '') . '.paypal.com', 443, $errno, $errtext, 30)){
				fputs($fp, $header . $req); // read the body data 
				$res = '';
				$headerdone = false;
				while(!feof($fp)){
					$line = fgets ($fp, 1024);
					if(strcmp($line, "\r\n") == 0)
						$headerdone = true;
					else if ($headerdone) // header has been read. now read the contents
						$res .= $line;
				}
				fclose($fp);
			}else{
				$success=FALSE;
				order_failed();
			}
		}
		if($success){
			$lines = explode("\n", $res);
			if(strcmp ($lines[0], "SUCCESS") == 0){
				$payment_status='';
				$pending_reason='';
				$txn_id='';
				for ($i=1; $i<(count($lines)-1);$i++){
					list($key,$val) = explode("=", $lines[$i]);
					if($key=='payment_status') $payment_status = $val;
					if($key=='pending_reason') $pending_reason = $val;
					if($key=='custom') $ordID = $val;
					if($key=='txn_id') $txn_id = $val;
				}
				$sSQL = "SELECT ordAuthNumber FROM orders WHERE ordPayProvider=1 AND ordStatus>=3 AND ordAuthNumber='" . escape_string($txn_id) . "' AND ordID='" . escape_string($ordID) . "'";
				$result = mysql_query($sSQL) or print(mysql_error());
				$success = FALSE;
				if($rs = mysql_fetch_assoc($result))
					$success = (trim($rs["ordAuthNumber"])!="");
				mysql_free_result($result);
				if($success)
					do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
				else{
					mysql_query("UPDATE cart SET cartCompleted=2 WHERE cartCompleted=0 AND cartOrderID='" . escape_string($ordID) . "'") or print(mysql_error());
					mysql_query("UPDATE orders SET ordAuthNumber='no ipn' WHERE ordAuthNumber='' AND ordPayProvider=1 AND ordID='" . escape_string($ordID) . "'") or print(mysql_error());
					$xxThkErr = '';
					$errtext = '&nbsp;<br />&nbsp;<br />&nbsp;<br />';
					if($payment_status=='Pending')
						$errtext .= $xxPPPend;
					else
						$errtext .= $xxNoCnf . '<br />&nbsp;<br />&nbsp;<br />&nbsp;<br /><input type="button" value="'.$xxClkRel.'" onclick="window.location.reload()" /><br />&nbsp;<br />&nbsp;<br />';
					order_failed();
				}
			}else{
				$errtext = $res;
				order_failed();
			}
		}
	}
}elseif(@$_POST["custom"] != ""){ // PayPal
	$ordID = trim(@$_POST["custom"]);
	$txn_id = trim(@$_POST["txn_id"]);
	$sSQL = "SELECT ordAuthNumber FROM orders WHERE ordPayProvider=1 AND ordStatus>=3 AND ordAuthNumber='" . escape_string($txn_id) . "' AND ordID='" . escape_string($ordID) . "'";
	$result = mysql_query($sSQL) or print(mysql_error());
	$success = FALSE;
	if($rs = mysql_fetch_assoc($result))
		$success = (trim($rs["ordAuthNumber"])!="");
	mysql_free_result($result);
	if($success)
		do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
	else{
		mysql_query("UPDATE cart SET cartCompleted=2 WHERE cartCompleted=0 AND cartOrderID='" . escape_string($ordID) . "'") or print(mysql_error());
		mysql_query("UPDATE orders SET ordAuthNumber='no ipn' WHERE ordAuthNumber='' AND ordPayProvider=1 AND ordID='" . escape_string($ordID) . "'") or print(mysql_error());
		$xxThkErr = '';
		$errtext = '&nbsp;<br />&nbsp;<br />&nbsp;<br />';
		if(@$_POST["payment_status"]=="Pending")
			$errtext .= $xxPPPend;
		else
			$errtext .= $xxNoCnf . '<br />&nbsp;<br />&nbsp;<br />&nbsp;<br /><input type="button" value="'.$xxClkRel.'" onclick="window.location.reload()" /><br />&nbsp;<br />&nbsp;<br />';
		order_failed();
	}
}elseif(@$_POST["method"] == "paypalexpress" && @$_POST["token"] != ""){ // PayPal Express
	if($success = getpayprovdetails(19,$username,$password,$data3,$demomode,$ppmethod)){
		$data2arr = explode("&",$password);
		$password=urldecode(@$data2arr[0]);
		$isthreetoken=(trim(urldecode(@$data2arr[2]))=='1');
		$signature=''; $sslcertpath='';
		if($isthreetoken) $signature=urldecode(@$data2arr[1]); else $sslcertpath=urldecode(@$data2arr[1]);
		if(strpos($username,'@AB@')!==FALSE){
			$isthreetoken=TRUE;
			$signature='AB';
		}
	}
	$ordID = trim(@$_POST["ordernumber"]);
	$token = trim(@$_POST["token"]);
	$payerid = trim(@$_POST["payerid"]);
	$ordAuthNumber = '';
	$txn_id = $status = $pendingreason = '';
	if($demomode) $sandbox = ".sandbox"; else $sandbox = "";
	$sSQL = "SELECT ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordHandling,ordTotal,ordDiscount,ordAuthNumber,ordEmail FROM orders WHERE ordID='" . escape_string($ordID) . "'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		if($rs["ordEmail"]==trim(@$_POST["email"])) $ordAuthNumber = $rs["ordAuthNumber"];
	}else
		$success = FALSE;
	mysql_free_result($result);
	if($success){
		if($ordAuthNumber==''){
			$amount = number_format(($rs['ordShipping']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordHSTTax']+$rs['ordTotal']+$rs['ordHandling'])-$rs['ordDiscount'],2,'.','');
			$sXML = ppsoapheader($username, $password, $signature) .
				'<soap:Body>' .
				'  <DoExpressCheckoutPaymentReq xmlns="urn:ebay:api:PayPalAPI">' .
				'    <DoExpressCheckoutPaymentRequest>' .
				'      <Version xmlns="urn:ebay:apis:eBLBaseComponents">60.00</Version>' .
				'      <DoExpressCheckoutPaymentRequestDetails xmlns="urn:ebay:apis:eBLBaseComponents">' .
				'        <PaymentAction>' . ($ppmethod==1?'Authorization':'Sale') . '</PaymentAction>' .
				'        <Token>' . $token . '</Token><PayerID>' . $payerid . '</PayerID>' .
				'        <PaymentDetails>' .
				'          <OrderTotal currencyID="' . $countryCurrency . '">' . $amount . '</OrderTotal>' .
				'          <ButtonSource>ecommercetemplates_Cart_EC_US</ButtonSource>' .
				'    <NotifyURL>' . $storeurl . 'vsadmin/ppconfirm.php</NotifyURL>' .
				'        </PaymentDetails>' .
				'      </DoExpressCheckoutPaymentRequestDetails>' .
				'    </DoExpressCheckoutPaymentRequest>' .
				'  </DoExpressCheckoutPaymentReq>' .
				'</soap:Body></soap:Envelope>';
			if(callcurlfunction('https://api-aa' . ($isthreetoken ? '-3t' : '') . $sandbox . '.paypal.com/2.0/', $sXML, $res, $sslcertpath, $errtext, FALSE)){
				$xmlDoc = new vrXMLDoc($res);
				$nodeList = $xmlDoc->nodeList->childNodes[0];
				for($i = 0; $i < $nodeList->length; $i++){
					if($nodeList->nodeName[$i]=="SOAP-ENV:Body"){
						$e = $nodeList->childNodes[$i];
						for($j = 0; $j < $e->length; $j++){
							if($e->nodeName[$j] == "DoExpressCheckoutPaymentResponse"){
								$ee = $e->childNodes[$j];
								for($jj = 0; $jj < $ee->length; $jj++){
									if($ee->nodeName[$jj] == "Token"){
										$token=$ee->nodeValue[$jj];
									}elseif($ee->nodeName[$jj] == "DoExpressCheckoutPaymentResponseDetails"){
										$ff = $ee->childNodes[$jj];
										for($kk = 0; $kk < $ff->length; $kk++){
											if($ff->nodeName[$kk] == "PaymentInfo"){
												$gg = $ff->childNodes[$kk];
												for($ll = 0; $ll < $gg->length; $ll++){
													if($gg->nodeName[$ll] == "PaymentStatus"){
														$status=$gg->nodeValue[$ll];
													}elseif($gg->nodeName[$ll] == "PendingReason"){
														$pendingreason=$gg->nodeValue[$ll];
													}elseif($gg->nodeName[$ll] == "TransactionID"){
														$txn_id=$gg->nodeValue[$ll];
													}
												}
											}
										}
									}elseif($ee->nodeName[$jj] == "Errors"){
										$ff = $ee->childNodes[$jj];
										for($kk = 0; $kk < $ff->length; $kk++){
											if($ff->nodeName[$kk] == "ShortMessage"){
												$errtext=$ff->nodeValue[$kk].'<br>'.$errtext;
											}elseif($ff->nodeName[$kk] == "LongMessage"){
												$errtext.=$ff->nodeValue[$kk];
											}elseif($ff->nodeName[$kk] == "ErrorCode"){
												$errcode=$ff->nodeValue[$kk];
											}
										}
									}
								}
							}
						}
					}
				}
			}else
				$success = FALSE;
		}else{
			$status = "Refresh";
		}
		if($status=="Completed" || $status=="Pending"){
			if($pendingreason=='authorization') $pendingreason='Capture';
			if($status=='Pending' && $pendingreason != '') $pendingreason = 'Pending: ' . $pendingreason; else $pendingreason='';
			mysql_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or print(mysql_error());
			mysql_query("UPDATE orders SET ordStatus=3,ordAuthNumber='" . $txn_id . "',ordAuthStatus='" . escape_string($pendingreason) . "' WHERE ordPayProvider=19 AND ordID='" . escape_string($ordID) . "'") or print(mysql_error());
			do_order_success($ordID,$emailAddr,$sendEmail,TRUE,TRUE,TRUE,TRUE);
		}elseif($status=='Refresh'){
			do_order_success($ordID,$emailAddr,$sendEmail,TRUE,FALSE,FALSE,FALSE);
		}else{
			order_failed();
		}
	}else{
		order_failed();
	}
}elseif(@$_GET["ncretval"] != "" && @$_GET["ncsessid"] != ""){ // NOCHEX
	$ordID = trim(@$_GET["ncretval"]);
	$ncsessid = trim(@$_GET["ncsessid"]);
	$sSQL = "SELECT ordAuthNumber FROM orders WHERE ordPayProvider=6 AND ordStatus>=3 AND ordSessionID='" . escape_string($ncsessid) . "' AND ordID='" . escape_string($ordID) . "'";
	$result = mysql_query($sSQL) or print(mysql_error());
	$success = FALSE;
	if($rs = mysql_fetch_assoc($result))
		$success = (trim($rs["ordAuthNumber"])!="");
	mysql_free_result($result);
	if($success)
		do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
	else{
		mysql_query("UPDATE cart SET cartCompleted=2 WHERE cartCompleted=0 AND cartOrderID='" . escape_string($ordID) . "'") or print(mysql_error());
		mysql_query("UPDATE orders SET ordAuthNumber='no apc' WHERE ordAuthNumber='' AND ordPayProvider=6 AND ordID='" . escape_string($ordID) . "'") or print(mysql_error());
		$errtext = '&nbsp;<br />&nbsp;<br />&nbsp;<br />' . $xxNoCnf . '<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />';
		$errtext .= '<br />&nbsp;<br />&nbsp;<br />&nbsp;<br /><input type="button" value="'.$xxClkRel.'" onclick="window.location.reload()" /><br />&nbsp;<br />&nbsp;<br />';
		$xxThkErr = '';
		order_failed();
	}
}elseif(@$_POST['xxpreauth'] != ''){
	$ordID = trim(@$_POST['xxpreauth']);
	$thesessionid = trim(str_replace("'",'',@$_POST['thesessionid']));
	$themethod = trim(str_replace("'",'',@$_POST['xxpreauthmethod']));
	if($success = getpayprovdetails($themethod,$data1,$data2,$data3,$demomode,$ppmethod)){
		$sSQL = "SELECT ordAuthNumber FROM orders WHERE ordSessionID='" . escape_string($thesessionid) . "' AND ordID='" . escape_string($ordID) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		$success = FALSE;
		if($rs = mysql_fetch_assoc($result))
			$success = (trim($rs['ordAuthNumber'])!='');
		mysql_free_result($result);
	}
	if($success)
		do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
	else
		order_failed();
}elseif(@$_POST['cart_order_id'] != '' && @$_POST['order_number'] != ''){ // 2Checkout Transaction
	if(trim(@$_POST['credit_card_processed'])=='Y'){
		$ordID = trim(@$_POST['cart_order_id']);
		$success = getpayprovdetails(2,$acctno,$md5key,$data3,$demomode,$ppmethod);
		$keysmatch=TRUE;
		if($md5key != ''){
			$theirkey = trim(@$_POST['key']);
			$ourkey = trim(strtoupper(md5($md5key . $acctno . ($demomode ? '1' : @$_POST['order_number']) . @$_POST['total'])));
			if($ourkey==$theirkey) $keysmatch=TRUE; else $keysmatch=FALSE;
		}
		if($success && $keysmatch){
			mysql_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or print(mysql_error());
			mysql_query("UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAuthNumber='" . escape_string(@$_POST["order_number"]) . "' WHERE ordPayProvider=2 AND ordID='" . escape_string($ordID) . "'") or print(mysql_error());
			order_success($ordID,$emailAddr,$sendEmail);
		}else{
			order_failed();
		}
	}else{
		order_failed();
	}
}elseif(@$_POST["CUSTID"] != "" && @$_POST["AUTHCODE"] != ""){ // PayFlow Link
	$success = getpayprovdetails(8,$data1,$data2,$data3,$demomode,$ppmethod);
	if($success && trim(@$_POST["RESULT"])=="0"){
		$ordID = trim(@$_POST["CUSTID"]);
		mysql_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or print(mysql_error());
		mysql_query("UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAVS='" . escape_string(@$_POST["AVSDATA"]) . "',ordCVV='" . escape_string(@$_POST["CSCMATCH"]) . "',ordAuthNumber='" . escape_string(unstripslashes(@$_POST["AUTHCODE"])) . "' WHERE ordPayProvider=8 AND ordID='" . escape_string($ordID) . "'") or print(mysql_error());
		order_success($ordID,$emailAddr,$sendEmail);
	}else{
		order_failed();
	}
}elseif(@$_POST["emailorder"] != "" || @$_POST["secondemailorder"] != ""){
	$ordGndTot=1;
	if(@$emailorderstatus != "") $ordStatus=$emailorderstatus; else $ordStatus=3;
	if(@$_POST["emailorder"] != ""){
		$ordID = trim(str_replace("'",'',@$_POST['emailorder']));
		$ppid = 4;
	}else{
		$ordID = trim(str_replace("'",'',@$_POST['secondemailorder']));
		$ppid = 17;
	}
	$thesessionid = trim(str_replace("'",'',@$_POST['thesessionid']));
	if(! is_numeric($ordID)) $ordID=0;
	$sSQL = "SELECT ordAuthNumber,((ordShipping+ordStateTax+ordCountryTax+ordHSTTax+ordTotal+ordHandling)-ordDiscount) AS ordGndTot FROM orders WHERE ordSessionID='" . escape_string($thesessionid) . "' AND ordID='" . escape_string($ordID) . "'";
	$result = mysql_query($sSQL) or print(mysql_error());
	$success = FALSE;
	if($rs = mysql_fetch_assoc($result)){
		$success = TRUE;
		$ordGndTot=round($rs['ordGndTot'],2);
	}
	mysql_free_result($result);
	$sSQL = "SELECT payProvShow FROM payprovider WHERE (payProvEnabled=1 OR ".$ordGndTot."=0) AND payProvID=" . $ppid;
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		$authnumber = $rs['payProvShow'];
		if($ordGndTot==0){ // Check if it was a gift cert
			$sSQL = "SELECT gcaGCID FROM giftcertsapplied WHERE gcaOrdID='" . escape_string($ordID) . "'";
			$result2 = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result2)>0) $authnumber=$xxGifCtc;
			mysql_free_result($result2);
		}
		if($authnumber=='') $authnumber='Email';
	}else
		$success = FALSE;
	mysql_free_result($result);
	if($success){
		$sSQL="UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'";
		mysql_query($sSQL) or print(mysql_error());
		$sSQL="UPDATE orders SET ordStatus=" . $ordStatus . ",ordAuthStatus='',ordAuthNumber='" . substr(escape_string($authnumber),0,48) . "' WHERE (ordPayProvider=" . $ppid . " OR (ordTotal-ordDiscount)<=0) AND ordID='" . escape_string($ordID) . "'";
		mysql_query($sSQL) or print(mysql_error());
		order_success($ordID,$emailAddr,$sendEmail);
	}else
		order_failed();
}elseif(@$_GET['OrderID'] != '' && @$_GET['TransRefNumber'] != ''){ // PSiGate
	$sSQL = 'SELECT payProvID FROM payprovider WHERE payProvEnabled=1 AND payProvID=11 OR payProvID=12';
	$result = mysql_query($sSQL) or print(mysql_error());
	$success = (mysql_num_rows($result) > 0);
	mysql_free_result($result);
	if(@$_GET['Approved'] != 'APPROVED') $success=FALSE;
	if(@$_GET['CustomerRefNo'] != substr(md5(@$_GET['OrderID'].':'.@$secretword), 0, 24)) $success=FALSE;
	if($success){
		$ordID = trim(@$_GET['OrderID']);
		mysql_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . escape_string($ordID) . "'") or print(mysql_error());
		mysql_query("UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAVS='" . escape_string(@$_GET['AVSResult'].'/'.@$_GET['IPResult']) . "',ordCVV='" . escape_string(@$_GET['CardIDResult']) . "',ordAuthNumber='" . escape_string(@$_GET['CardAuthNumber']) . "',ordTransID='" . escape_string(@$_GET['CardRefNumber']) . "' WHERE (ordPayProvider=11 OR ordPayProvider=12) AND ordID='" . escape_string($ordID) . "'") or print(mysql_error());
		order_success($ordID,$emailAddr,$sendEmail);
	}else{
		$errtext = @$_GET['ErrMsg'];
		order_failed();
	}
}elseif(@$_POST["ponumber"] != "" && (@$_POST["approval_code"] != "" || @$_POST["failReason"] != "")){ // Linkpoint
	if(getpayprovdetails(16,$data1,$data2,$data3,$demomode,$ppmethod)){
		$ordID=escape_string(@$_POST["ponumber"]);
		$ordIDa=explode(",", $ordID);
		$ordID=$ordIDa[0];
		$theauthcode=escape_string(@$_POST["approval_code"]);
		$thesuccess=strtolower(trim(@$_POST["status"]));
		if(($thesuccess=="approved" || $thesuccess=="submitted") && $theauthcode != ''){
			$autharr = explode(':', $theauthcode);
			if($autharr[0]=='Y' && count($autharr) >= 3){
				$theauthcode = $autharr[1];
				$theavscode = $autharr[2];
				$sSQL = "SELECT ordID FROM orders WHERE ordAuthNumber='" . substr($theauthcode,0,6) . "' AND ordPayProvider=16 AND ordID='" . $ordID . "'";
				$result = mysql_query($sSQL) or print(mysql_error());
				$foundorder = (mysql_num_rows($result)>0);
				mysql_free_result($result);
				if($foundorder){
					do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
				}else{
					mysql_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID='$ordID'") or print(mysql_error());
					mysql_query("UPDATE orders SET ordStatus=3,ordAuthStatus='',ordAVS='" . substr($theavscode,0,3) . "',ordCVV='" . substr($theavscode,3) . "',ordAuthNumber='" . substr($theauthcode,0,6) . "',ordTransID='" . substr($theauthcode,6) . "' WHERE ordPayProvider=16 AND ordID='" . $ordID . "'") or print(mysql_error());
					order_success($ordID,$emailAddr,$sendEmail);
				}
			}else{
				$errtext = 'Invalid auth code';
				order_failed();
			}
		}else{
			$errtext = @$_POST["failReason"];
			$errtextarr = explode(':', $errtext);
			if(@$errtextarr[1] != '') $errtext = $errtextarr[1];
			order_failed();
		}
	}else
		order_failed();
}elseif(@$_GET['status']!='' && @$_GET['recipientName']!='' && @$_GET['signature']!=''){ // Amazon Simple Pay
	if(@$_GET['status']=="PF"){
		$errtext = "Payment Declined";
		order_failed();
	}elseif(@$_GET['status']=="PS" || @$_GET['status']=="PR" || @$_GET['status']=="PI"){
		$ordID = trim(str_replace("'", '', @$_GET['referenceId']));
		$txn_id = trim(str_replace("'", '', @$_GET['transactionId']));
		if(! is_numeric($ordID)) $ordID=0;
		$sSQL = "SELECT ordAuthNumber FROM orders WHERE ordPayProvider=21 AND ordStatus>=3 AND ordAuthNumber='".escape_string($txn_id)."' AND ordID='" . escape_string($ordID) . "'";
		$success = ($txn_id!='');
		$result = mysql_query($sSQL) or print(mysql_error());
		$success = (mysql_num_rows($result) > 0);
		mysql_free_result($result);
		if($success){
			do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
		}else{
			mysql_query("UPDATE cart SET cartCompleted=2 WHERE cartCompleted=0 AND cartOrderID='" . escape_string($ordID) . "'") or print(mysql_error());
			mysql_query("UPDATE orders SET ordAuthNumber='no ipn' WHERE ordAuthNumber='' AND ordPayProvider=21 AND ordID='" . escape_string($ordID) . "'") or print(mysql_error());
			$xxThkErr = '';
			$errtext = '&nbsp;<br />&nbsp;<br />&nbsp;<br />' . $xxNoCnf . '<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />';
			$errtext .= '<input type="button" value="' . $xxClkRel . '" onclick="window.location.reload()" /><br />&nbsp;<br />&nbsp;<br />';
			order_failed();
		}
	}
}elseif(@$_GET["OrdNo"] != "" && @$_GET["ErrMsg"] != ""){ // PSiGate Error Reporting
	$errtext = @$_GET['ErrMsg'];
	order_failed();
}else{
	include "./vsadmin/inc/customppreturn.php";
}
if(@$googleanalyticsinfo==TRUE && is_numeric($ordID)){
	// Order ID, Affiliation, Total, Tax, Shipping, City, State, Country
	$googleanalyticstrackorderinfo = "\r\n" . (@$usegoogleasync ? "_gaq.push(['_addTrans'," : "pageTracker._addTrans(") . '"' . $ordID . '","' . $affilID . '","' . $ordTotal . '","' . ($ordStateTax+$ordHSTTax+$ordCountryTax) . '","' . ($ordShipping+$ordHandling) . '","' . (@$usegoogleasync ? str_replace('"','\\"',$ordCity) . '","' : '') . str_replace('"','\\"',$ordState) . '","' . str_replace('"','\\"',$ordCountry) . '"' . (@$usegoogleasync ? ']' : '') . ');' . "\r\n";
	$sSQL = 'SELECT cartProdID,cartProdName,cartProdPrice,cartQuantity,'.getlangid('sectionName',256).",pSKU FROM cart INNER JOIN products ON cart.cartProdID=products.pID INNER JOIN sections ON products.pSection=sections.sectionID WHERE cartOrderID='".escape_string($ordID)."' ORDER BY cartID";
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs = mysql_fetch_assoc($result)){
		// Order ID, SKU, Product Name , Category, Price, Quantity
		$googleanalyticstrackorderinfo .= (@$usegoogleasync ? "_gaq.push(['_addItem'," : "pageTracker._addItem(") . '"' . $ordID . '","' . str_replace('"','\\"',$rs['cartProdID']) . '","' . str_replace('"','\\"',$rs['cartProdName']) . '","' . str_replace('"','\\"',$rs[getlangid('sectionName',256)]) . '","' . $rs['cartProdPrice'] . '","' . $rs['cartQuantity'] . '"' . (@$usegoogleasync ? ']' : '') . ');' . "\r\n";
	}
	mysql_free_result($result);
	$googleanalyticstrackorderinfo .= (@$usegoogleasync ? "_gaq.push(['_trackTrans']);" : "pageTracker._trackTrans();") . "\r\n";
}
?>