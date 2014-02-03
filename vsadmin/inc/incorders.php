<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$dateadjust=='') $dateadjust=0;
if(@$dateformatstr == '') $dateformatstr = 'm/d/Y';
$admindatestr='Y-m-d';
if(@$admindateformat=='') $admindateformat=0;
if($admindateformat==1)
	$admindatestr='m/d/Y';
elseif($admindateformat==2)
	$admindatestr='d/m/Y';
if(@$storesessionvalue=='') $storesessionvalue='virtualstore'.time();
if(@$_GET['doedit']=='true' || @$_GET['id']=='new') $doedit=TRUE; else $doedit=FALSE;
$isinvoice=(@$_GET['invoice']=='true');
function editspecial($data,$col,$size,$special){
	global $doedit;
	if($doedit) return('<input type="text" id="' . $col . '" name="' . $col . '" value="' . htmlspecials($data) . '" size="' . $size . '" '.$special.' />'); else return(htmlspecials($data));
}
function editfunc($data,$col,$size){
	global $doedit;
	if($doedit) return('<input type="text" id="' . $col . '" name="' . $col . '" value="' . htmlspecials($data) . '" size="' . $size . '" />'); else return(htmlspecials($data));
}
function editnumeric($data,$col,$size){
	global $doedit;
	if($doedit) return('<input type="text" id="' . $col . '" name="' . $col . '" value="' . number_format(strip_tags($data),2,'.','') . '" size="' . $size . '" />'); else return(FormatEuroCurrency(strip_tags($data)));
}
function getNumericField($fldname){
	$fldval = trim(@$_POST[$fldname]);
	if(! is_numeric($fldval)) return(0); else return((double)$fldval);
}
function decodehtmlentities($thestr){
	return(str_replace(array('&quot;','&nbsp;'), array('"', ' '), $thestr));
}
function writesearchparams(){
	writehiddenvar('fromdate', @$_SESSION['fromdate']);
	writehiddenvar('todate', @$_SESSION['todate']);
	writehiddenvar('notstatus', @$_SESSION['notstatus']);
	writehiddenvar('notsearchfield', @$_SESSION['notsearchfield']);
	writehiddenvar('searchtext', @$_SESSION['searchtext']);
	if(is_array($_SESSION['ordStatus'])){
		foreach($_SESSION['ordStatus'] as $key => $val)
			writehiddenvar('ordStatus[]', $val);
	}
	if(is_array($_SESSION['ordstate'])){
		foreach($_SESSION['ordstate'] as $key => $val)
			writehiddenvar('ordstate[]', $val);
	}
	if(is_array($_SESSION['ordcountry'])){
		foreach($_SESSION['ordcountry'] as $key => $val)
			writehiddenvar('ordcountry[]', $val);
	}
	if(is_array($_SESSION['payprovider'])){
		foreach($_SESSION['payprovider'] as $key => $val)
			writehiddenvar('payprovider[]', $val);
	}
	writehiddenvar('ordersearchfield', @$_COOKIE['ordersearchfield']);
}
function showgetoptionsselect($oid){
	return('<div style="position:absolute"><select id="'.$oid.'" size="15" ' .
		'style="display:none;position:absolute;min-width:140px;top:0px;left:0px;" ' .
		'onblur="this.style.display=\'none\'" ' .
		'onchange="comboselect_onchange(this)" ' .
		'onclick="comboselect_onclick(this)" ' .
		'onkeyup="comboselect_onkeyup(event.keyCode,this)">' .
		'<option value="">Populating...</option>' .
		'</select></div>');
}
function updateorderstatus($iordid, $ordstatus){
	global $htmlemails,$yyTrackT,$ordstatusemail,$dateformatstr,$dateadjust,$emlNl,$emailencoding,$emailAddr,$trackingnumtext,$ordstatussubject,$storeurl,$adminlangsettings,$languageid,$loyaltypoints,$giftcertificateid;
	$ordauthno='';
	$oldordstatus=999;
	$payprovider=0;
	$ordClientID=0;
	$loyaltypointtotal=0;
	$savelangid=@$languageid;
	if($iordid != ''){
		$result = mysql_query("SELECT ordStatus,ordAuthNumber,ordEmail,ordDate,".getlangid("statPublic",64).",ordStatusInfo,ordName,ordLastName,ordTrackNum,ordPayProvider,ordLang,ordClientID,loyaltyPoints,ordTotal,ordDiscount,pointsRedeemed FROM orders INNER JOIN orderstatus ON orders.ordStatus=orderstatus.statID WHERE ordID=" . $iordid) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)){
			$oldordstatus=$rs['ordStatus'];
			$ordauthno=$rs['ordAuthNumber'];
			$ordemail=$rs['ordEmail'];
			$orddate=strtotime($rs['ordDate']);
			$oldstattext=$rs[getlangid('statPublic',64)];
			$ordstatinfo=$rs['ordStatusInfo'];
			if(@$htmlemails==TRUE) $ordstatinfo=str_replace("\r\n", '<br />', $ordstatinfo);
			$ordername=trim($rs['ordName'] . ' ' . $rs['ordLastName']);
			$trackingnum = trim($rs['ordTrackNum']);
			$payprovider=$rs['ordPayProvider'];
			$languageid=$rs['ordLang']+1;
			$ordClientID=$rs['ordClientID'];
			$loyaltypointtotal=$rs['loyaltyPoints'];
			$ordTotal=$rs['ordTotal'];
			$ordDiscount=$rs['ordDiscount'];
			$pointsredeemed=$rs['pointsRedeemed'];
		}
		mysql_free_result($result);
		$result = mysql_query("SELECT ".getlangid("statPublic",64)." FROM orders INNER JOIN orderstatus ON orders.ordStatus=orderstatus.statID WHERE ordID=" . $iordid) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)){
			$oldstattext=$rs[getlangid('statPublic',64)];
		}
		mysql_free_result($result);
	}
	if($payprovider != 20){
		if($oldordstatus!=999 && ($oldordstatus<3 && $ordstatus>=3)){
			if($ordauthno=='') mysql_query("UPDATE orders SET ordAuthNumber='". escape_string($yyManAut) . "' WHERE ordID=" . $iordid) or print(mysql_error());
			mysql_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID=" . $iordid) or print(mysql_error());
			
			$sSQL = "SELECT cartProdId,cartProdName,cartProdPrice,cartQuantity,cartID FROM cart LEFT JOIN products ON cart.cartProdId=products.pID WHERE cartOrderID='" . escape_string($iordid) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result) > 0){
				while($rs = mysql_fetch_assoc($result)){
					if($rs['cartProdId']==$giftcertificateid){
						$sSQL = 'UPDATE giftcertificate SET gcAuthorized=1,gcOrigAmount='.$rs['cartProdPrice'].',gcRemaining='.$rs['cartProdPrice'].' WHERE gcAuthorized=0 AND gcCartID=' . $rs['cartID'];
						mysql_query($sSQL) or print(mysql_error());
					}
				}
			}
			mysql_free_result($result);
			if(@$loyaltypoints!=''){
				$loyaltypointtotal=(int)(($ordTotal-$ordDiscount)*$loyaltypoints);
				mysql_query("UPDATE orders SET loyaltyPoints=" . $loyaltypointtotal . " WHERE ordID=" . $iordid) or print(mysql_error());
				if($ordClientID!=0) mysql_query("UPDATE customerlogin SET loyaltyPoints=loyaltyPoints+" . $loyaltypointtotal . " WHERE clID=" . $ordClientID);
			}
		}elseif($oldordstatus!=999 && ($oldordstatus>=3 && $ordstatus<3)){
			mysql_query("UPDATE giftcertificate SET gcAuthorized=0 WHERE gcOrderID=" . $iordid) or print(mysql_error());
			if($ordClientID!=0 && $loyaltypoints!='') mysql_query("UPDATE customerlogin SET loyaltyPoints=loyaltyPoints-" . $loyaltypointtotal . " WHERE clID=" . $ordClientID);
		}
		if($oldordstatus!=999 && ($oldordstatus<2 && $ordstatus>=2)){
			if($ordClientID!=0) mysql_query("UPDATE customerlogin SET loyaltyPoints=loyaltyPoints-" . $pointsredeemed . " WHERE clID=" . $ordClientID);
		}elseif($oldordstatus!=999 && ($oldordstatus>=2 && $ordstatus<2)){
			if($ordClientID!=0) mysql_query("UPDATE customerlogin SET loyaltyPoints=loyaltyPoints+" . $pointsredeemed . " WHERE clID=" . $ordClientID);
		}
		if($oldordstatus!=999 && ($oldordstatus<=1 && $ordstatus>1) && (time()-$orddate) < (86400*365)) stock_subtract($iordid);
		if($oldordstatus!=999 && ($oldordstatus>1 && $ordstatus<=1) && (time()-$orddate) < (86400*365)) release_stock($iordid);
		if($iordid != '' && $ordstatus != ''){
			if($oldordstatus != (int)$ordstatus && @$_POST['emailstat']=='1' && $ordstatus!=1){
				$result = mysql_query("SELECT ".getlangid('statPublic',64).",emailstatus FROM orderstatus WHERE statID=" . $ordstatus);
				if($rs = mysql_fetch_assoc($result)){
					$newstattext = $rs[getlangid('statPublic',64)];
					$emailstatus = ($rs['emailstatus']!=0);
				}else
					$emailstatus = FALSE;
				mysql_free_result($result);
				if(($adminlangsettings & 4096)==0) $languageid=1;
				if(@$ordstatussubject[$languageid] != '') $emailsubject=$ordstatussubject[$languageid]; else $emailsubject = 'Order status updated';
				$ose = $ordstatusemail[$languageid];
				$ose = str_replace('%orderid%', $iordid, $ose);
				$ose = str_replace('%orderdate%', date($dateformatstr, $orddate) . ' ' . date('H:i', $orddate), $ose);
				$ose = str_replace('%oldstatus%', $oldstattext, $ose);
				$ose = str_replace('%newstatus%', $newstattext, $ose);
				$thetime = time() + ($dateadjust*60*60);
				$ose = str_replace('%date%', date($dateformatstr, $thetime) . ' ' . date('H:i', $thetime), $ose);
				$ose = str_replace('%ordername%', $ordername, $ose);
				$ose = replaceemailtxt($ose, '%statusinfo%', $ordstatinfo);
				$ose = replaceemailtxt($ose, '%trackingnum%', $trackingnum);
				$reviewlinks='';
				if(strpos($ose, '%reviewlinks%')!==FALSE){
					$sSQL = "SELECT DISTINCT pID,".getlangid('pName',1).",pStaticPage,pDisplay FROM products INNER JOIN cart ON products.pID=cart.cartProdID WHERE cartOrderID=".$iordid;
					$result = mysql_query($sSQL) or print(mysql_error());
					while($rs = mysql_fetch_assoc($result)){
						if($rs['pDisplay']!=0){
							if($rs['pStaticPage']!=0)
								$thelink = $storeurl . cleanforurl($rs[getlangid('pName',1)]).'.php?review=true';
							else
								$thelink = $storeurl . 'proddetail.php?prod='.$rs['pID'].'&review=true';
							if(@$htmlemails==TRUE) $thelink = '<a href="' . $thelink . '">' . $thelink . '</a>';
							$reviewlinks .= $thelink . $emlNl;
						}
					}
					mysql_free_result($result);
				}
				$ose = replaceemailtxt($ose, '%reviewlinks%', $reviewlinks);
				$ose = str_replace(array('%nl%','<br />'), $emlNl, $ose);
				if($emailstatus!=0) dosendemail($ordemail, $emailAddr, '', $emailsubject, $ose);
			}
			if($oldordstatus != (int)$ordstatus) mysql_query("UPDATE orders SET ordStatus=" . $ordstatus . ",ordStatusDate='" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "' WHERE ordID=" . $iordid) or print(mysql_error());
		}
	}
	$languageid=$savelangid;
}
if($_SESSION['loggedon'] != $storesessionvalue || @$disallowlogin==TRUE) exit;
if(@$htmlemails==TRUE) $emlNl = '<br />'; else $emlNl="\n";
$success=true;
$alreadygotadmin = getadminsettings();
if(@$_POST['updatestatus']=='1' || @$_POST['act']=='status'){
	$sSQL = 'SELECT orderstatussubject,orderstatussubject2,orderstatussubject3,orderstatusemail,orderstatusemail2,orderstatusemail3 FROM emailmessages WHERE emailID=1';
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		$ordstatussubject[1]=$rs['orderstatussubject'];
		$ordstatusemail[1]=$rs['orderstatusemail'];
		$ordstatussubject[2]=$rs['orderstatussubject2'];
		$ordstatusemail[2]=$rs['orderstatusemail2'];
		$ordstatussubject[3]=$rs['orderstatussubject3'];
		$ordstatusemail[3]=$rs['orderstatusemail3'];
	}
	mysql_free_result($result);
}
if(@$_POST['updatestatus']=='1'){
	mysql_query("UPDATE orders SET ordTrackNum='" . escape_string(unstripslashes(@$_POST['ordTrackNum'])) . "',ordStatusInfo='" . escape_string(unstripslashes(@$_POST['ordStatusInfo'])) . "',ordInvoice='" . escape_string(unstripslashes(@$_POST['ordInvoice'])) . "'" . (trim(@$_POST['shipcarrier']) != '' ? ',ordShipCarrier=' . trim(@$_POST['shipcarrier']) : '') . " WHERE ordID=" . @$_POST['orderid']) or print(mysql_error());
	updateorderstatus(@$_POST['orderid'], trim(@$_POST['ordStatus']));
}elseif(@$_GET['id'] != '' && @$_GET['id'] != 'multi' && @$_GET['id'] != 'new'){
	if(@$_POST['delccdets'] != '')
		mysql_query("UPDATE orders SET ordCNum='' WHERE ordID=" . @$_GET['id']) or print(mysql_error());
}else{
	if($delccafter != 0) mysql_query("UPDATE orders SET ordCNum='' WHERE ordDate<'" . date("Y-m-d H:i:s", time()-($delccafter*60*60*24)) . "'") or print(mysql_error());
	if(@$persistentcart=='') $persistentcart=3;
	if(@$_SESSION['hasdeletedoldcart'] != '1'){ trimoldcartitems(time()-($persistentcart*60*60*24)); $_SESSION['hasdeletedoldcart']='1'; }
}
$numstatus=0;
$sSQL = "SELECT statID,statPrivate FROM orderstatus WHERE statPrivate<>'' ORDER BY statID";
$result = mysql_query($sSQL) or print(mysql_error());
while($rs = mysql_fetch_assoc($result)){
	$allstatus[$numstatus++]=$rs;
}
mysql_free_result($result);
if(@$_POST['updatestatus']=='1'){
?>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<form id="searchparamsform" method="post" action="adminorders.php">
<?php		writesearchparams(); ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr>
                <td width="100%" colspan="4" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?><br/><br/><input type="submit" value="<?php print $yyClkHer?>"><br /><br />&nbsp;</td>
			  </tr>
			</table>
			</form>
		  </td>
		</tr>
	  </table>
<script language="javascript" type="text/javascript">
setTimeout('document.getElementById("searchparamsform").submit()', 500);
</script>
<?php
}elseif(@$_POST['doedit'] == 'true'){
	$OWSP = '';
	$orderid = $_POST['orderid'];
	$ordstatus = (int)trim($_POST['ordStatus']);
	$oldordstatus=0;
	$ordComLoc = 0;
	$thecustomerid = 0;
	if($orderid!='new'){
		if(@$_POST['updatestock']=='ON') release_stock($orderid);
		$sSQL = "SELECT ordSessionID,ordClientID,ordAuthStatus,ordShipType,loyaltyPoints,ordStatus FROM orders WHERE ordID='" . $orderid . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_array($result);
		$thesessionid = $rs['ordSessionID'];
		$thecustomerid = $rs['ordClientID'];
		$loyaltypointtotal = $rs['loyaltyPoints'];
		$oldordstatus = $rs['ordStatus'];
		if($thecustomerid!=0 && $loyaltypoints!='' && $loyaltypointtotal!=0 && $oldordstatus>=3) mysql_query('UPDATE customerlogin SET loyaltyPoints=loyaltyPoints-' . $loyaltypointtotal . ' WHERE clID=' . $thecustomerid) or print(mysql_error());
		if($rs['ordAuthStatus']=='MODWARNOPEN' || is_null($rs['ordAuthStatus'])) mysql_query("UPDATE orders SET ordAuthStatus='' WHERE ordID='" . $orderid . "'") or print(mysql_error());
		if($rs['ordShipType']=='MODWARNOPEN' || is_null($rs['ordShipType'])) mysql_query("UPDATE orders SET ordShipType='' WHERE ordID='" . $orderid . "'") or print(mysql_error());
		mysql_free_result($result);
	}
	if(trim(@$_POST['commercialloc'])=='Y') $ordComLoc = 1;
	if(trim(@$_POST['wantinsurance'])=='Y') $ordComLoc += 2;
	if(trim(@$_POST['saturdaydelivery'])=='Y') $ordComLoc += 4;
	if(trim(@$_POST['signaturerelease'])=='Y') $ordComLoc += 8;
	if(trim(@$_POST['insidedelivery'])=='Y') $ordComLoc += 16;
	if(trim(@$_POST['custid'])!='' && is_numeric(@$_POST['custid']))
		$thecustomerid=@$_POST['custid'];
	if($orderid=='new'){
		$thesessionid = 'A1';
		$sSQL = 'INSERT INTO orders (ordSessionID,ordClientID,ordName,ordLastName,ordAddress,ordAddress2,ordCity,ordState,ordZip,ordCountry,ordEmail,ordPhone,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipZip,ordShipCountry,ordShipPhone,ordPayProvider,ordAuthNumber,ordShipping,ordStateTax,ordCountryTax,ordHSTTax,ordHandling,ordShipType,ordShipCarrier,loyaltyPoints,ordTotal,ordDate,ordStatus,ordAuthStatus,ordStatusDate,ordComLoc,ordIP,ordAffiliate,ordExtra1,ordExtra2,ordShipExtra1,ordShipExtra2,ordCheckoutExtra1,ordCheckoutExtra2,ordDiscount,ordDiscountText,ordAddInfo) VALUES (';
		$sSQL .= "'".$thesessionid."',";
		$sSQL .= "'".$thecustomerid."',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['name'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['lastname'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['address'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['address2'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['city'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['state'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['zip'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['country'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['email'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['phone'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['sname'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['slastname'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['saddress'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['saddress2'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['scity'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['sstate'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['szip'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['scountry'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['sphone'])) . "',";
		$sSQL .= "'4',"; // ordPayProvider
		$sSQL .= "'" . escape_string(@$_POST['ordAuthNumber']) . "',";
		$sSQL .= "'" . escape_string(@$_POST['ordShipping']) . "',";
		$sSQL .= "'" . escape_string(@$_POST['ordStateTax']) . "',";
		$sSQL .= "'" . escape_string(@$_POST['ordCountryTax']) . "',";
		if(@$canadataxsystem==TRUE) $sSQL .= "'" . escape_string(@$_POST['ordHSTTax']) . "',"; else $sSQL .= "'0',";
		$sSQL .= "'" . escape_string(@$_POST['ordHandling']) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['shipmethod'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['shipcarrier'])) . "',";
		$sSQL .= "'" . getNumericField('loyaltyPoints') . "',";
		$sSQL .= "'" . escape_string(@$_POST['ordtotal']) . "',";
		$sSQL .= "'" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "',";
		$sSQL .= "'" . escape_string($ordstatus) . "',";
		$sSQL .= "'',"; // ordAuthStatus
		$sSQL .= "'" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "',";
		$sSQL .= "'" . $ordComLoc . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['ipaddress'])) . "',";
		$sSQL .= "'" . trim(@$_POST['PARTNER']) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['ordextra1'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['ordextra2'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['ordshipextra1'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['ordshipextra2'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['ordcheckoutextra1'])) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['ordcheckoutextra2'])) . "',";
		$sSQL .= "'" . escape_string(@$_POST['ordDiscount']) . "',";
		$sSQL .= "'" . escape_string(str_replace(array("\r\n","\n","\r"),array('<br />','<br />','<br />'),unstripslashes(@$_POST['discounttext']))) . "',";
		$sSQL .= "'" . escape_string(unstripslashes(@$_POST['ordAddInfo'])) . "')";
		mysql_query($sSQL) or print(mysql_error());
		$orderid = mysql_insert_id();
	}else{
		$sSQL = "UPDATE orders SET ";
		$sSQL .= "ordName='" . escape_string(unstripslashes(@$_POST['name'])) . "',";
		$sSQL .= "ordLastName='" . escape_string(unstripslashes(@$_POST['lastname'])) . "',";
		$sSQL .= "ordAddress='" . escape_string(unstripslashes(@$_POST['address'])) . "',";
		$sSQL .= "ordAddress2='" . escape_string(unstripslashes(@$_POST['address2'])) . "',";
		$sSQL .= "ordCity='" . escape_string(unstripslashes(@$_POST['city'])) . "',";
		$sSQL .= "ordState='" . escape_string(unstripslashes(@$_POST['state'])) . "',";
		$sSQL .= "ordZip='" . escape_string(unstripslashes(@$_POST['zip'])) . "',";
		$sSQL .= "ordCountry='" . escape_string(unstripslashes(@$_POST['country'])) . "',";
		$sSQL .= "ordEmail='" . escape_string(unstripslashes(@$_POST['email'])) . "',";
		$sSQL .= "ordPhone='" . escape_string(unstripslashes(@$_POST['phone'])) . "',";
		$sSQL .= "ordShipName='" . escape_string(unstripslashes(@$_POST['sname'])) . "',";
		$sSQL .= "ordShipLastName='" . escape_string(unstripslashes(@$_POST['slastname'])) . "',";
		$sSQL .= "ordShipAddress='" . escape_string(unstripslashes(@$_POST['saddress'])) . "',";
		$sSQL .= "ordShipAddress2='" . escape_string(unstripslashes(@$_POST['saddress2'])) . "',";
		$sSQL .= "ordShipCity='" . escape_string(unstripslashes(@$_POST['scity'])) . "',";
		$sSQL .= "ordShipState='" . escape_string(unstripslashes(@$_POST['sstate'])) . "',";
		$sSQL .= "ordShipZip='" . escape_string(unstripslashes(@$_POST['szip'])) . "',";
		$sSQL .= "ordShipCountry='" . escape_string(unstripslashes(@$_POST['scountry'])) . "',";
		$sSQL .= "ordShipPhone='" . escape_string(unstripslashes(@$_POST['sphone'])) . "',";
		$sSQL .= "ordShipType='" . escape_string(unstripslashes(@$_POST['shipmethod'])) . "',";
		$sSQL .= "ordShipCarrier='" . escape_string(unstripslashes(@$_POST['shipcarrier'])) . "',";
		$sSQL .= "ordIP='" . escape_string(unstripslashes(@$_POST['ipaddress'])) . "',";
		$sSQL .= "ordComLoc=" . $ordComLoc . ",";
		$sSQL .= "ordAffiliate='" . trim(@$_POST['PARTNER']) . "',";
		$sSQL .= "ordAddInfo='" . escape_string(unstripslashes(@$_POST['ordAddInfo'])) . "',";
		$sSQL .= "ordStatusInfo='" . escape_string(unstripslashes(@$_POST['ordStatusInfo'])) . "',";
		$sSQL .= "ordStatus='" . escape_string($ordstatus) . "',";
		$sSQL .= "ordTrackNum='" . escape_string(unstripslashes(@$_POST['ordTrackNum'])) . "',";
		$sSQL .= "ordDiscountText='" . escape_string(str_replace(array("\r\n","\n","\r"),array('<br />','<br />','<br />'),unstripslashes(@$_POST['discounttext']))) . "',";
		$sSQL .= "ordInvoice='" . escape_string(unstripslashes(@$_POST['ordInvoice'])) . "',";
		$sSQL .= "ordExtra1='" . escape_string(unstripslashes(@$_POST['ordextra1'])) . "',";
		$sSQL .= "ordExtra2='" . escape_string(unstripslashes(@$_POST['ordextra2'])) . "',";
		$sSQL .= "ordShipExtra1='" . escape_string(unstripslashes(@$_POST['ordshipextra1'])) . "',";
		$sSQL .= "ordShipExtra2='" . escape_string(unstripslashes(@$_POST['ordshipextra2'])) . "',";
		$sSQL .= "ordCheckoutExtra1='" . escape_string(unstripslashes(@$_POST['ordcheckoutextra1'])) . "',";
		$sSQL .= "ordCheckoutExtra2='" . escape_string(unstripslashes(@$_POST['ordcheckoutextra2'])) . "',";
		$sSQL .= "ordShipping='" . escape_string(@$_POST['ordShipping']) . "',";
		$sSQL .= "ordStateTax='" . escape_string(@$_POST['ordStateTax']) . "',";
		$sSQL .= "ordCountryTax='" . escape_string(@$_POST['ordCountryTax']) . "',";
		if(@$canadataxsystem==TRUE) $sSQL .= "ordHSTTax='" . escape_string(@$_POST['ordHSTTax']) . "',";
		$sSQL .= "ordDiscount='" . escape_string(@$_POST['ordDiscount']) . "',";
		$sSQL .= "ordHandling='" . escape_string(@$_POST['ordHandling']) . "',";
		$ordauthnumber = trim(@$_POST['ordAuthNumber']);
		if((int)trim(@$_POST['ordStatus'])>2 && $ordauthnumber=='') $ordauthnumber='manual auth';
		$sSQL .= "ordAuthNumber='" . escape_string($ordauthnumber) . "',";
		$sSQL .= "ordTransID='" . escape_string(@$_POST['ordTransID']) . "',";
		$sSQL .= "loyaltyPoints='" . getNumericField('loyaltyPoints') . "',";
		$sSQL .= "ordTotal='" . escape_string(@$_POST['ordtotal']) . "'";
		$sSQL .= " WHERE ordID='" . $_POST['orderid'] . "'";
		mysql_query($sSQL) or print(mysql_error());
	}
	if($ordstatus>2) mysql_query("UPDATE giftcertificate SET gcAuthorized=1 WHERE gcOrderID=" . $orderid) or print(mysql_error());
	if($ordstatus<=2) mysql_query("UPDATE giftcertificate SET gcAuthorized=0 WHERE gcOrderID=" . $orderid) or print(mysql_error());
	if($thecustomerid!=0 && $loyaltypoints!='' && $ordstatus>=3) mysql_query('UPDATE customerlogin SET loyaltyPoints=loyaltyPoints+' . getNumericField('loyaltyPoints') . ' WHERE clID=' . $thecustomerid) or print(mysql_error());
	foreach($_POST as $objItem => $objValue){
		//print $objItem . " : " . $objValue . "<br />";
		if(substr($objItem,0,6)=='prodid'){
			$idno = (int)substr($objItem, 6);
			$cartid = trim(@$_POST['cartid' . $idno]);
			$prodid = trim(@$_POST['prodid' . $idno]);
			$quant = trim(@$_POST['quant' . $idno]);
			$theprice = trim(@$_POST['price' . $idno]);
			$prodname = trim(@$_POST['prodname' . $idno]);
			$delitem = trim(@$_POST['del_' . $idno]);
			if($delitem=='yes' || ($cartid!='' && trim($prodid)=='')){
				mysql_query("DELETE FROM cart WHERE cartID=" . $cartid) or print(mysql_error());
				mysql_query("DELETE FROM cartoptions WHERE coCartID=" . $cartid) or print(mysql_error());
				$cartid = '';
			}elseif($cartid!=''){
				$sSQL = "UPDATE cart SET cartProdID='" . escape_string(unstripslashes($prodid)) . "',cartProdPrice=" . $theprice . ",cartProdName='" . escape_string(unstripslashes($prodname)) . "',cartQuantity=" . $quant . " WHERE cartID=" . $cartid;
				mysql_query($sSQL) or print(mysql_error());
				mysql_query("DELETE FROM cartoptions WHERE coCartID=" . $cartid) or print(mysql_error());
			}else{
				$sSQL = "INSERT INTO cart (cartSessionID,cartClientID,cartProdID,cartQuantity,cartCompleted,cartProdName,cartProdPrice,cartOrderID,cartDateAdded) VALUES (";
				$sSQL .= "'" . $thesessionid . "',";
				$sSQL .= "'" . $thecustomerid . "',";
				$sSQL .= "'" . escape_string(unstripslashes($prodid)) . "',";
				$sSQL .= $quant . ",";
				$sSQL .= "1,";
				$sSQL .= "'" . escape_string(unstripslashes($prodname)) . "',";
				$sSQL .= "'" . $theprice . "',";
				$sSQL .= $orderid . ",";
				$sSQL .= "'" . date("Y-m-d H:i:s", time() + ($dateadjust*60*60)) . "')";
				mysql_query($sSQL) or print(mysql_error());
				$cartid = mysql_insert_id();
			}
			if($cartid != ""){
				$optprefix = "optn" . $idno . '_';
				$prefixlen = strlen($optprefix);
				foreach($_POST as $kk => $kkval){
					if(substr($kk,0,$prefixlen)==$optprefix && trim($kkval) != ''){
						$optidarr = explode('|', $kkval);
						$optid = $optidarr[0];
						if(@$_POST['v' . $kk] == ""){
							$sSQL="SELECT optID,".getlangid("optGrpName",16).",".getlangid("optName",32)."," . $OWSP . "optPriceDiff,optWeightDiff,optType,optFlags FROM options LEFT JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optID='" . escape_string($kkval) . "'";
							$result = mysql_query($sSQL) or print(mysql_error());
							if($rs = mysql_fetch_array($result)){
								if(abs($rs['optType']) != 3){
									$sSQL = "INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff) VALUES (" . $cartid . "," . $rs['optID'] . ",'" . escape_string($rs[getlangid("optGrpName",16)]) . "','" . escape_string($rs[getlangid("optName",32)]) . "',";
									$sSQL .= $optidarr[1] . ",0)";
								}else
									$sSQL = "INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff) VALUES (" . $cartid . "," . $rs['optID'] . ",'" . escape_string($rs[getlangid("optGrpName",16)]) . "','',0,0)";
								mysql_query($sSQL) or print(mysql_error());
							}
							mysql_free_result($result);
						}else{
							$sSQL="SELECT optID,".getlangid("optGrpName",16).",".getlangid("optName",32)." FROM options LEFT JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optID='" . escape_string($kkval) . "'";
							$result = mysql_query($sSQL) or print(mysql_error());
							$rs = mysql_fetch_array($result);
							mysql_free_result($result);
							$sSQL = "INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff) VALUES (" . $cartid . "," . $rs['optID'] . ",'" . escape_string($rs[getlangid("optGrpName",16)]) . "','" . escape_string(unstripslashes(@$_POST['v' . $kk])) . "',0,0)";
							mysql_query($sSQL) or print(mysql_error());
						}
					}
				}
			}
		}
	}
	if(@$_POST['updatestock']=='ON') stock_subtract($orderid); ?>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<form id="searchparamsform" method="post" action="adminorders.php">
<?php		writesearchparams();
			if(@$_POST['orderid']!='new') writehiddenvar('ctrlmod', 2); ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr>
                <td width="100%" colspan="4" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?><br/><br/><input type="submit" value="<?php print $yyClkHer?>"><br /><br />&nbsp;</td>
			  </tr>
			</table>
			</form>
		  </td>
		</tr>
	  </table>
<script language="javascript" type="text/javascript">
setTimeout('document.getElementById("searchparamsform").submit()', 500);
</script>
<?php
}elseif(@$_GET['id'] != ''){
	if($_GET['id']=='new')
		$idlist = array('0');
	elseif($_GET['id']=='multi')
		$idlist = $_POST['ids'];
	else
		$idlist = array($_GET['id']);
	$numids = count($idlist);
	$numorders = 0;
	foreach($idlist as $theid){
		$numids--;
		$allorders = '';
		$statetaxrate=0;
		$countrytaxrate=0;
		$hsttaxrate=0;
		$countryorder=0;
		if($_GET['id']=='new'){
			$alldata['ordStatus']=3; $alldata['ordAuthStatus']=''; $alldata['ordStatusDate']=time(); $alldata['ordID']=''; $alldata['ordName']=''; $alldata['ordLastName']=''; $alldata['ordAddress']=''; $alldata['ordAddress2']=''; $alldata['ordCity']=''; $alldata['ordState']=''; $alldata['ordZip']=''; $alldata['ordCountry']=''; $alldata['ordEmail']=''; $alldata['ordPhone']=''; $alldata['ordShipName']=''; $alldata['ordShipLastName']=''; $alldata['ordShipAddress']=''; $alldata['ordShipAddress2']=''; $alldata['ordShipCity']=''; $alldata['ordShipState']=''; $alldata['ordShipZip']=''; $alldata['ordShipCountry']=''; $alldata['ordShipPhone']=''; $alldata['ordPayProvider']=0; $alldata['ordAuthNumber']='manual auth'; $alldata['ordTransID']=''; $alldata['ordTotal']=0; $alldata['ordDate']=time(); $alldata['ordStateTax']=0; $alldata['ordCountryTax']=0; $alldata['ordShipping']=0; $alldata['ordShipType']=''; $alldata['ordShipCarrier']=0; $alldata['ordIP']=getipaddress(); $alldata['ordAffiliate']=''; $alldata['ordDiscount']=0; $alldata['ordDiscountText']=''; $alldata['ordHandling']=0; $alldata['ordComLoc']=0; $alldata['ordExtra1']=''; $alldata['ordExtra2']=''; $alldata['ordShipExtra1']=''; $alldata['ordShipExtra2']=''; $alldata['ordCheckoutExtra1']=''; $alldata['ordCheckoutExtra2']=''; $alldata['ordHSTTax']=0; $alldata['ordTrackNum']=''; $alldata['ordInvoice']=''; $alldata['ordClientID']=0; $alldata['ordReferer']=''; $alldata['loyaltyPoints']=''; $alldata['ordAddInfo']=''; $alldata['ordStatusInfo']='';
		}else{
			$sSQL = "SELECT cartProdId,cartProdName,cartProdPrice,cartQuantity,cartID,pStockByOpts,pExemptions FROM cart LEFT JOIN products on cart.cartProdID=products.pId WHERE cartOrderID=" . $theid . ' ORDER BY ' . ($isprinter && ! $isinvoice && @$packingslipsort!=''?$packingslipsort:'cartID');
			$allorders = mysql_query($sSQL) or print(mysql_error());
			$numorders = mysql_num_rows($allorders);
			$sSQL = "SELECT ordID,ordStatus,ordAuthStatus,ordStatusDate,ordName,ordLastName,ordAddress,ordAddress2,ordCity,ordState,ordZip,ordCountry,ordEmail,ordPhone,ordShipName,ordShipLastName,ordShipAddress,ordShipAddress2,ordShipCity,ordShipState,ordShipZip,ordShipCountry,ordShipPhone,ordPayProvider,ordAuthNumber,ordTransID,ordTotal,ordDate,ordStateTax,ordCountryTax,ordHSTTax,ordShipping,ordShipType,ordShipCarrier,ordIP,ordAffiliate,ordDiscount,ordHandling,ordDiscountText,ordComLoc,ordExtra1,ordExtra2,ordShipExtra1,ordShipExtra2,ordCheckoutExtra1,ordCheckoutExtra2,ordAddInfo,ordTrackNum,ordInvoice,ordClientID,ordReferer,ordQuerystr,loyaltyPoints,ordStatusInfo FROM orders LEFT JOIN payprovider ON payprovider.payProvID=orders.ordPayProvider WHERE ordID='" . $theid . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($alldata = mysql_fetch_assoc($result)){
				$alldata['ordDate'] = strtotime($alldata['ordDate']);
				$alldata['ordStatusDate'] = strtotime($alldata['ordStatusDate']);
			}else
				$alldata=array('ordID'=>0, 'ordStatus'=>0, 'ordAuthStatus'=>'', 'ordStatus'=>time(), 'ordName'=>'&nbsp;', 'ordLastName'=>'', 'ordAddress'=>'', 'ordAddress2'=>'', 'ordCity'=>'', 'ordState'=>'', 'ordZip'=>'', 'ordCountry'=>'', 'ordEmail'=>'', 'ordPhone'=>'', 'ordShipName'=>'', 'ordShipLastName'=>'', 'ordShipAddress'=>'', 'ordShipAddress2'=>'', 'ordShipCity'=>'', 'ordShipState'=>'', 'ordShipZip'=>'', 'ordShipCountry'=>'', 'ordShipPhone'=>'', 'ordPayProvider'=>0, 'ordAuthNumber'=>'', 'ordTransID'=>'', 'ordTotal'=>0, 'ordDate'=>0, 'ordStateTax'=>0, 'ordCountryTax'=>0, 'ordShipping'=>0, 'ordShipType'=>'', 'ordShipCarrier'=>0, 'ordIP'=>'', 'ordAffiliate'=>'', 'ordDiscount'=>0, 'ordDiscountText'=>'', 'ordHandling'=>0, 'ordComLoc'=>0, 'ordExtra1'=>'', 'ordExtra2'=>'', 'ordShipExtra1'=>'', 'ordShipExtra2'=>'', 'ordCheckoutExtra1'=>'', 'ordCheckoutExtra2'=>'', 'ordHSTTax'=>0, 'ordTrackNum'=>'', 'ordInvoice'=>'', 'ordClientID'=>0, 'ordReferer'=>'', 'ordQuerystr'=>'', 'loyaltyPoints'=>'', 'ordAddInfo'=>'', 'ordStatusInfo'=>'');
			if(! $isprinter && ! $doedit){ // previous and next id
				$sSQL = "SELECT ordID FROM orders WHERE ordID<".$theid." ORDER BY ordID DESC LIMIT 0,1";
				$result2 = mysql_query($sSQL) or print(mysql_error());
				if($rs = mysql_fetch_assoc($result2)) $previousid=$rs['ordID'];
				mysql_free_result($result2);
				$sSQL = "SELECT ordID FROM orders WHERE ordID>".$theid." ORDER BY ordID LIMIT 0,1";
				$result2 = mysql_query($sSQL) or print(mysql_error());
				if($rs = mysql_fetch_assoc($result2)) $nextid=$rs['ordID'];
				mysql_free_result($result2);
			}
			mysql_free_result($result);
		}
		if($doedit){
			print '<form method="post" name="editform" action="adminorders.php" onsubmit="return confirmedit()"><input type="hidden" name="orderid" value="' . $_GET["id"] . '" /><input type="hidden" name="doedit" value="true" />';
			$overridecurrency=TRUE;
			$orcsymbol='';
			$orcdecplaces=2;
			$orcpreamount=TRUE;
			$orcdecimals=".";
			$orcthousands='';
		}
		if(! $isprinter){
?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
var newwin="";
var plinecnt=0;
var numcartitems=<?php print $numorders?>;
function openemailpopup(id){
  popupWin = window.open('popupemail.php?'+id,'emailpopup','menubar=no, scrollbars=no, width=300, height=250, directories=no,location=no,resizable=yes,status=no,toolbar=no')
}
function uaajaxcallback(){
	if(ajaxobj.readyState==4){
		var restxt=ajaxobj.responseText;
		resarr=restxt.split('==LISTELM==');
		if(resarr.length>0){
			document.getElementById("custid").value=resarr[0];
			document.getElementById("name").value=resarr[1];
<?php	if(@$usefirstlastname) print 'document.getElementById("lastname").value=resarr[2];' . "\r\n" ?>
		}
		if(resarr.length>5){
			document.getElementById("address").value=resarr[3];
<?php	if(@$useaddressline2) print 'document.getElementById("address2").value=resarr[4];' . "\r\n" ?>
			document.getElementById("city").value=resarr[5];
			document.getElementById("state").value=resarr[6];
			document.getElementById("zip").value=resarr[7];
			cntry=document.getElementById("country");
			cntxt=resarr[8];
			for(index=0; index<cntry.length; index++){
				if(cntry.options[index].text==cntxt||cntry.options[index].value==cntxt){
					cntry.selectedIndex=index;
				}
			}
			document.getElementById("phone").value=resarr[9];
<?php
	if(trim(@$extraorderfield1)!='') print 'document.getElementById("ordextra1").value=resarr[10];' . "\r\n";
	if(trim(@$extraorderfield2)!='') print 'document.getElementById("ordextra2").value=resarr[11];' . "\r\n" ?>
			setstatetax();
			setcountrytax();
		}
	}
}
function updateaddress(id){
	ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
	ajaxobj.onreadystatechange = uaajaxcallback;
	ajaxobj.open("POST", "ajaxservice.php?action=getlist&listtype=adddets", true);
	ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxobj.setRequestHeader("Content-length", ('listtext='+adds[id]).length);
	ajaxobj.send('listtext='+adds[id]);
}
function upajaxcallback(){
	if(ajaxobj.readyState==4){
		var restxt=ajaxobj.responseText.replace(/^\s+|\s+$/g,"");
		resarr=restxt.split('==LISTELM==');
		document.getElementById('optionsspan'+resarr[0]).innerHTML=resarr[1];
		try{eval(resarr[2]);}catch(err){document.getElementById('optionsspan'+resarr[0]).innerHTML='javascript error'}
	}
}
function updateoptions(id){
	prodid = document.getElementById('prodid'+id).value;
	if(prodid != ''){
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange = upajaxcallback;
		ajaxobj.open("POST", 'ajaxservice.php?action=updateoptions&index='+id, true);
		ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxobj.setRequestHeader("Content-length", ('productid='+prodid).length);
		ajaxobj.send('productid='+prodid);
	}
	return(false);
}
function extraproduct(plusminus){
var productspan=document.getElementById('productspan');
var thetable = document.getElementById('producttable');
if(plusminus=='+'){
numcartitems++;
newrow = thetable.insertRow(numcartitems);
newrow.className='cobll';
newcell = newrow.insertCell(0);
newcell.vAlign='top';
newcell.innerHTML = '<input type="button" value="..." onclick="updateoptions('+(plinecnt+1000)+')" />&nbsp;<input name="prodid'+(plinecnt+1000)+'" size="18" id="prodid'+(plinecnt+1000)+'" AUTOCOMPLETE="off" onkeydown="return combokey(this,event)" onkeyup="combochange(this,event)" /><input type="hidden" id="stateexempt'+(plinecnt+1000)+'" value="false" /><input type="hidden" id="countryexempt'+(plinecnt+1000)+'" value="false" />';
newcell.innerHTML+= '<?php print str_replace("'","\\'",showgetoptionsselect('xxxx'))?>'.replace(/xxxx/,'selectprodid'+(plinecnt+1000));
newcell = newrow.insertCell(1);
newcell.vAlign='top';
newcell.innerHTML = '<input type="text" id="prodname'+(plinecnt+1000)+'" name="prodname'+(plinecnt+1000)+'" size="24" AUTOCOMPLETE="off" onkeydown="return combokey(this,event)" onkeyup="combochange(this,event)" />';
newcell.innerHTML+= '<?php print str_replace("'","\\'",showgetoptionsselect('xxxx'))?>'.replace(/xxxx/,'selectprodname'+(plinecnt+1000));
newcell = newrow.insertCell(2);
newcell.innerHTML = '<span id="optionsspan'+(plinecnt+1000)+'">-</span>';
newcell = newrow.insertCell(3);
newcell.vAlign='top';
newcell.innerHTML = '<input type="text" id="quant'+(plinecnt+1000)+'" name="quant'+(plinecnt+1000)+'" size="5" value="1" />';
newcell = newrow.insertCell(4);
newcell.vAlign='top';
newcell.innerHTML = '<input type="text" id="price'+(plinecnt+1000)+'" name="price'+(plinecnt+1000)+'" value="0" size="7" /><br /><input type="hidden" id="optdiffspan'+(plinecnt+1000)+'" value="0" />';
newcell = newrow.insertCell(5);
newcell.innerHTML = '&nbsp;';
plinecnt++;
}else{
if(plinecnt>0){
thetable.deleteRow(numcartitems);
plinecnt--;
numcartitems--;
}
}
}
function confirmedit(){
<?php
	if($stockManage!=0){ ?>
var stockwarn="The following items do not have sufficient stock\n\n";
var outstock=false;
var oostock=new Array();
var oostockqnt=new Array();
for(var i in document.forms.editform){
	if(i.substr(0,5)=="quant"){
		theid = i.substr(5);
		delbutton = document.getElementById("del_"+theid);
		if(delbutton==null)
			isdeleted=false;
		else
			isdeleted=delbutton.checked;
		if(! isdeleted){
			var pid=document.getElementById("prodid"+theid).value;
			var stocklevel=stock['pid_' + pid];
			var quant = document.getElementById("quant"+theid).value
			if(typeof(stocklevel)=="undefined"){
				// Do nothing, pid not defined.
			}else if(stocklevel=="bo"){ // By Options
				for(var ii in document.forms.editform){
					var opttext="optn"+theid+"_";
					if(ii.substr(0,opttext.length)==opttext){
						theitem = document.getElementById(ii);
						if(document.getElementById('v'+ii)==null){
							thevalue = theitem[theitem.selectedIndex].value.split('|')[0];
							stocklevel = stock['oid_'+thevalue];
							if(typeof(oostockqnt['oid_'+thevalue])=="undefined")
								oostockqnt['oid_'+thevalue]=parseInt(quant);
							else
								oostockqnt['oid_'+thevalue]+=parseInt(quant);
							if(parseInt(stocklevel)<oostockqnt['oid_'+thevalue]){
								oostock['oid_'+thevalue] = document.getElementById("prodname"+theid).value + " (" + theitem[theitem.selectedIndex].text + ") : Required " + oostockqnt['oid_'+thevalue] + " available ";
							}
						}
					}
				}
			}else{
				if(typeof(oostockqnt['pid_' + pid])=="undefined")
					oostockqnt['pid_' + pid]=parseInt(quant);
				else
					oostockqnt['pid_' + pid]+=parseInt(quant);
				if(parseInt(stocklevel)<oostockqnt['pid_' + pid]){
					oostock['pid_' + pid] = document.getElementById("prodname"+theid).value + ": Required " + oostockqnt['pid_' + pid] + " available ";
				}
			}
		}
	}
}
for(var i in oostock){
	outstock=true;
	stockwarn += oostock[i] + stock[i] + "\n";
}
if(outstock){
	if(! confirm(stockwarn+"\nPress \"OK\" to submit changes or cancel to adjust quantities\n"))
		return(false);
}
<?php
	} ?>
if(confirm('<?php print str_replace("'","\'",$yyChkRec)?>'))
	return(true);
return(false);
}
var opttxtcharge=[];
function dorecalc(onlytotal){
var thetotal=0,totoptdiff=0,statetaxabletotal=0,countrytaxabletotal=0;
for(var zz=0; zz < document.forms.editform.length; zz++){
var iq=document.forms.editform[zz].name;
if(iq.substr(0,5)=="quant"){
	theid = iq.substr(5);
	totopts=0;
	delbutton = document.getElementById("del_"+theid);
	if(delbutton==null)
		isdeleted=false;
	else
		isdeleted=delbutton.checked;
	if(! isdeleted){
	for(var ii in document.forms.editform){
		var opttext="optn"+theid+"_";
		if(ii.substr(0,opttext.length)==opttext){
			theitem = document.getElementById(ii);
			if(document.getElementById('v'+ii)==null){
				thevalue = theitem[theitem.selectedIndex].value;
				if(thevalue.indexOf('|')>0){
					totopts += parseFloat(thevalue.substr(thevalue.indexOf('|')+1));
				}
			}else{
				optid=parseInt(ii.substr(opttext.length));
				if(opttxtcharge[optid]){
					if(opttxtcharge[optid]>0){
						totopts+=opttxtcharge[optid]*document.getElementById('v'+ii).value.length;
					}else if(document.getElementById('v'+ii).value.length>0){
						totopts+=Math.abs(opttxtcharge[optid]);
					}
				}
			}
		}
	}
	thequant = parseInt(document.getElementById(iq).value);
	if(isNaN(thequant)) thequant=0;
	theprice = parseFloat(document.getElementById("price"+theid).value);
	if(isNaN(theprice)) theprice=0;
	document.getElementById("optdiffspan"+theid).value=totopts;
	optdiff = parseFloat(document.getElementById("optdiffspan"+theid).value);
	if(isNaN(optdiff)) optdiff=0;
	thetotal += thequant * (theprice + optdiff);
	if(!document.getElementById("stateexempt"+theid)||document.getElementById("stateexempt"+theid).value!='true')
		statetaxabletotal += thequant * (theprice + optdiff);
	if(!document.getElementById("countryexempt"+theid)||document.getElementById("countryexempt"+theid).value!='true')
		countrytaxabletotal += thequant * (theprice + optdiff);
	totoptdiff += thequant * optdiff;
	}
}
}
document.getElementById("optdiffspan").innerHTML=totoptdiff.toFixed(2);
document.getElementById("ordtotal").value = thetotal.toFixed(2);
if(onlytotal==true) return;
statetaxrate = parseFloat(document.getElementById("staterate").value);
if(isNaN(statetaxrate)) statetaxrate=0;
countrytaxrate = parseFloat(document.getElementById("countryrate").value);
if(isNaN(countrytaxrate)) countrytaxrate=0;
discount = parseFloat(document.getElementById("ordDiscount").value);
if(isNaN(discount)){
	discount=0;
	document.getElementById("ordDiscount").value=0;
}
statetaxtotal = (statetaxrate * Math.max(statetaxabletotal-discount,0)) / 100.0;
countrytaxtotal = (countrytaxrate * Math.max(countrytaxabletotal-discount,0)) / 100.0;
shipping = parseFloat(document.getElementById("ordShipping").value);
if(isNaN(shipping)){
	shipping=0;
	document.getElementById("ordShipping").value=0;
}
handling = parseFloat(document.getElementById("ordHandling").value);
if(isNaN(handling)){
	handling=0;
	document.getElementById("ordHandling").value=0;
}
<?php	if(@$taxShipping==2){ ?>
statetaxtotal += (statetaxrate * shipping) / 100.0;
countrytaxtotal += (countrytaxrate * shipping) / 100.0;
<?php	}
		if(@$taxHandling==2){ ?>
statetaxtotal += (statetaxrate * handling) / 100.0;
countrytaxtotal += (countrytaxrate * handling) / 100.0;
<?php	} ?>
var hsttax=0;
<?php	if(@$canadataxsystem==TRUE){ ?>
	var ssa=getshipstateabbrev();
	if(getshipcountry()=='canada'){
		if(ssa=="NB" || ssa=="NF" || ssa=="NS" || ssa=="ON" || ssa=="BC"){
			hsttax=statetaxtotal+countrytaxtotal;
			statetaxtotal=0;
			countrytaxtotal=0;
		}else if(ssa=="PE" || ssa=="QC"){
			statetaxtotal+=(countrytaxtotal*statetaxrate) / 100.0;
		}
	}
	document.getElementById("ordHSTTax").value = hsttax.toFixed(2);
<?php	} ?>
statetaxtotal=roundNumber(statetaxtotal,2);
countrytaxtotal=roundNumber(countrytaxtotal,2);
document.getElementById("ordStateTax").value = statetaxtotal.toFixed(2);
document.getElementById("ordCountryTax").value = countrytaxtotal.toFixed(2);
grandtotal = (thetotal + shipping + handling + statetaxtotal + countrytaxtotal + hsttax) - discount;
document.getElementById("grandtotalspan").innerHTML = grandtotal.toFixed(2);
<?php	if(@$loyaltypoints!=''){ ?>
	document.getElementById("loyaltyPoints").value=Math.round((thetotal.toFixed(2)-discount)*<?php print $loyaltypoints?>);
<?php	} ?>
}
function roundNumber(num, dec){
	var result = Math.round(Math.round(num * Math.pow(10, dec+1) ) / 10) / Math.pow(10,dec);
	return result;
}
function ppajaxcallback(){
	if(ajaxobj.readyState==4){
		document.getElementById("googleupdatespan").innerHTML = ajaxobj.responseText;
	}
}
function updategoogleorder(theprocessor,theact,ordid){
	if(confirm('Inform '+theprocessor+' of change to order id ' + ordid + "?")){
		document.getElementById("googleupdatespan").innerHTML = '';
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange = ppajaxcallback;
		extraparams='';
		if(theact=='ship'){
			shipcar = document.getElementById("shipcarrier");
			if(shipcar!= null){
				trackno=document.getElementById("ordTrackNum").value
				if(trackno!='' && confirm('Include tracking and carrier info?')){
					extraparams='&carrier='+(shipcar.options[shipcar.selectedIndex].value)+'&trackno='+document.getElementById("ordTrackNum").value;
				}
			}
		}
		if(document.getElementById("txamount")){
			extraparams+='&amount='+document.getElementById("txamount").value;
		}
		document.getElementById("googleupdatespan").innerHTML = 'Connecting...';
		ajaxobj.open("GET", "ajaxservice.php?processor="+theprocessor+"&gid="+ordid+"&act="+theact+extraparams, true);
		ajaxobj.send(null);
	}
}
function updatepaypalorder(theprocessor,ordid){
	if(confirm('Inform '+theprocessor+' of change to order id ' + ordid + "?")){
		document.getElementById("googleupdatespan").innerHTML = '';
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange = ppajaxcallback;
		var additionalcapture = document.getElementById("additionalcapture")[document.getElementById("additionalcapture").selectedIndex].value;
		var theact = document.getElementById("paypalaction")[document.getElementById("paypalaction").selectedIndex].value;
		document.getElementById("googleupdatespan").innerHTML = 'Connecting...';
		postdata = "additionalcapture=" + additionalcapture + "&amount=" + encodeURIComponent(document.getElementById("captureamount").value) + "&comments=" + encodeURIComponent(document.getElementById("buyernote").value)
		ajaxobj.open("POST", "ajaxservice.php?processor="+theprocessor+"&gid="+ordid+"&act="+theact, true);
		ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxobj.setRequestHeader("Content-length", postdata.length);
		ajaxobj.setRequestHeader("Connection", "close");
		ajaxobj.send(postdata);
	}
}
function setpaypalelements(){
	var theact = document.getElementById("paypalaction")[document.getElementById("paypalaction").selectedIndex].value;
	if(theact=='void'){
		document.getElementById("captureamount").disabled=true;
		document.getElementById("additionalcapture").disabled=true;
	}else if(theact=='reauth'){
		document.getElementById("captureamount").disabled=false;
		document.getElementById("additionalcapture").disabled=true;
	}else{
		document.getElementById("captureamount").disabled=false;
		document.getElementById("additionalcapture").disabled=false;
	}
}
function updategooglestatus(theact,ordid){
	if(confirm('Update Google account status and inform customer of this status change?')){
		document.getElementById("googleupdatespan").innerHTML = '';
		ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		ajaxobj.onreadystatechange = ppajaxcallback;
		themessage="googlemessage=" + encodeURI(document.getElementById("ordStatusInfo").value);
		document.getElementById("googleupdatespan").innerHTML = 'Connecting...';
		ajaxobj.open("POST", "ajaxservice.php?gid="+ordid+"&act="+theact, true);
		ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxobj.setRequestHeader('Content-Length', themessage.length);
		ajaxobj.send(themessage);
	}
}
function copybillingtoshipping(){
<?php	if(trim(@$extraorderfield1)!='') print 'document.getElementById("ordshipextra1").value = document.getElementById("ordextra1").value;' ?>
	document.getElementById("sname").value = document.getElementById("name").value;
<?php	if(@$usefirstlastname) print 'document.getElementById("slastname").value = document.getElementById("lastname").value;' ?>
	document.getElementById("saddress").value = document.getElementById("address").value;
<?php	if(@$useaddressline2==TRUE) print 'document.getElementById("saddress2").value = document.getElementById("address2").value;' ?>
	document.getElementById("scity").value = document.getElementById("city").value;
	document.getElementById("sstate").value = document.getElementById("state").value;
	document.getElementById("szip").value = document.getElementById("zip").value;
	document.getElementById("scountry").selectedIndex = document.getElementById("country").selectedIndex;
	document.getElementById("sphone").value = document.getElementById("phone").value;
<?php	if(trim(@$extraorderfield2)!='') print 'document.getElementById("ordshipextra2").value = document.getElementById("ordextra2").value;' ?>
}
<?php		if($doedit){ ?>
function setCookie(c_name,value,expiredays){
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}
var adds=[];
var opensels=[];
document.getElementById('main').onclick=function(){
	for(var ii=0; ii<opensels.length; ii++)
		document.getElementById(opensels[ii]).style.display='none';
};
function addopensel(id){
	for(var ii=0; ii<opensels.length; ii++)
		if(id==opensels[ii]) return;
	opensels.push(id);
}
function plajaxcallback(){
	if(ajaxobj.readyState==4){
		var resarr=ajaxobj.responseText.replace(/^\s+|\s+$/g,"").split('==LISTOBJ==');
		var index,isname=false;
		oSelect=document.getElementById(resarr[0]);
		var act=resarr[0].replace(/\d/g,'');
		for(index=0; index<resarr.length-2; index++){
			var val1=resarr[index+1].split('==LISTELM==')[0];
			var val2=resarr[index+1].split('==LISTELM==')[1];
			if(resarr[index+1].length>=2) adds[index]=resarr[index+1].split('==LISTELM==')[2];
			if(index<oSelect.length)
				var y=oSelect.options[index];
			else
				var y=document.createElement('option');
			if(act=='selectprodname'){
				y.text=val2;
				y.value=val1;
			}else if(act=='selectemail'){
				y.text=val2;
				y.value=val1;
			}else{
				y.text=val1;
				y.value=val1;
			}
			if(y.text=='----------------') y.disabled=true; else y.disabled=false;
			if(index>=oSelect.length){
				try{oSelect.add(y, null);} // FF etc
				catch(ex){oSelect.add(y);} // IE
			}
		}
		if(oSelect){
			for(var ii=oSelect.length;ii>=index;ii--){
				oSelect.remove(ii);
			}
		}
	}
}
var gsid;
var gltyp;
var gtxt;
var tmrid;
function populatelist(){
	var objid=gsid;
	var listtype=gltyp;
	var stext=gtxt;
	ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
	ajaxobj.onreadystatechange = plajaxcallback;
	ajaxobj.open("POST", "ajaxservice.php?action=getlist&objid="+objid+"&listtype="+listtype, true);
	ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxobj.setRequestHeader("Content-length", ('listtext='+stext).length);
	ajaxobj.send('listtext='+stext);
}
function combochange(oText,e){
	if(document.getElementById("autocomplete").checked==false)
		return;
	keyCode = e.keyCode;
	if(keyCode<32&&keyCode!=8)return true;
	oSelect=document.getElementById('select'+oText.id);
	addopensel(oSelect.id);
	oSelect.style.display='';
	toFind = oText.value.toLowerCase();
	gsid=oSelect.id;
	gltyp=oText.id.replace(/\d/g,'');
	gtxt=toFind;
	clearTimeout(tmrid);
	tmrid=setTimeout("populatelist()",800);
}
function writedbg(msg){
	document.getElementById("debugdiv").innerHTML+=msg.replace(/</g,'&lt;').replace(/\r\n/g,'<br>')+"<br />";
}
function combokey(oText,e){
	if(document.getElementById("autocomplete").checked==false)
		return
	oSelect=document.getElementById('select'+oText.id);
	keyCode = e.keyCode;
	if(keyCode==40 || keyCode==38){ // Up / down arrows
		addopensel(oSelect.id);
		oSelect.style.display='';
		oSelect.focus();
		comboselect_onchange(oSelect);
	}
	else if(keyCode==13){
		oSelect.style.display='none';
		oText.focus();
		updateoptions(oText.id.replace(/prodid|prodname/,''));
		return getvalsfromserver(oSelect);
	}
	return true;
}
function getvalsfromserver(oSelect){
	var act=oSelect.id.replace(/\d/g,'');
	oText=document.getElementById(oSelect.id.replace('select',''));
	if(oSelect.selectedIndex != -1){
		if(act=='selectprodname'){
			oText.value = oSelect.options[oSelect.selectedIndex].text;
			document.getElementById(oText.id.replace('prodname','prodid')).value=oSelect.options[oSelect.selectedIndex].value;
		}else
			oText.value = oSelect.options[oSelect.selectedIndex].value;
		oSelect.style.display='none';
		oText.focus();
		if(act=='selectemail')
			updateaddress(oSelect.selectedIndex);
		else
			updateoptions(oText.id.replace(/prodid|prodname/,''));
	}
	return false;
}
function comboselect_onclick(oSelect){
	return(getvalsfromserver(oSelect));
}
function comboselect_onchange(oSelect){
	oText=document.getElementById(oSelect.id.replace('select',''));
	if(oSelect.selectedIndex != -1){
		if(oText.id.indexOf('prodname')!=-1)
			oText.value = oSelect.options[oSelect.selectedIndex].text;
		else
			oText.value = oSelect.options[oSelect.selectedIndex].value;
	}
}
function comboselect_onkeyup(keyCode,oSelect){
	if(keyCode==13){
		getvalsfromserver(oSelect);
	}
	return(false);
}
var countrytaxrates=[];
var statetaxrates=[];
var stateabbrevs=[];
<?php
	$sSQL = "SELECT stateName,stateAbbrev,stateTax FROM states WHERE stateTax<>0";
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs2 = mysql_fetch_array($result)){
		print 'statetaxrates["'.strtolower($rs2['stateName']).'"]='.$rs2['stateTax'].";\r\n";
		print 'statetaxrates["'.strtolower($rs2['stateAbbrev']).'"]='.$rs2['stateTax'].";\r\n";
		print 'stateabbrevs["'.strtolower($rs2['stateName']).'"]="'.$rs2['stateAbbrev']."\";\r\n";
	}
	mysql_free_result($result);
	$sSQL = 'SELECT countryName,countryTax FROM countries WHERE countryTax<>0';
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs2 = mysql_fetch_array($result)){
		print 'countrytaxrates["'.strtolower($rs2['countryName']).'"]='.$rs2['countryTax'].";\r\n";
	}
	mysql_free_result($result); ?>
function setstatetax(){
	var addans='';
	if(document.getElementById('saddress').value!='') addans='s';
	var rgnname=document.getElementById(addans+'state').value.toLowerCase();
	if(statetaxrates[rgnname]) statetaxrate = parseFloat(statetaxrates[rgnname]); else statetaxrate=0;
	document.getElementById("staterate").value = statetaxrate;
}
function setcountrytax(){
	var addans='';
	if(document.getElementById('saddress').value!='') addans='s';
	var tobj=document.getElementById(addans+'country');
	var rgnname=tobj.options[tobj.selectedIndex].value.toLowerCase();
	if(countrytaxrates[rgnname]) countrytaxrate = parseFloat(countrytaxrates[rgnname]); else countrytaxrate=0;
	document.getElementById("countryrate").value = countrytaxrate;
}
function getshipstateabbrev(){
	var addans='';
	if(document.getElementById('saddress').value!='') addans='s';
	var rgnname=document.getElementById(addans+'state').value.toLowerCase();
	if(stateabbrevs[rgnname]) return(stateabbrevs[rgnname]); else return document.getElementById(addans+'state').value;
}
function getshipcountry(){
	var addans='';
	if(document.getElementById('saddress').value!='') addans='s';
	var tobj=document.getElementById(addans+'country');
	return(tobj.options[tobj.selectedIndex].value.toLowerCase());
}
<?php		} ?>
/* ]]> */
</script>
<?php	} // ! $isprinter ?>
<table border="0" cellspacing="0" cellpadding="0" width="100%" align="center" <?php if($numids>0) print 'style="page-break-after: always"'?>>
  <tr>
	<td width="100%">
	  <table width="100%" border="0" cellspacing="0" cellpadding="2">
		<tr><td width="50%"><img src="adminimages/clearpixel.gif" width="20" height="1" alt="" /></td><td width="50%"><img src="adminimages/clearpixel.gif" width="20" height="1" alt="" /></td></tr>
<?php	if($isprinter && ! @isset($packingslipheader)) $packingslipheader=@$invoiceheader;
		if($isinvoice && @$invoiceheader != ""){ ?>
		<tr><td width="100%" colspan="2"><?php print $invoiceheader?></td></tr>
<?php	}elseif($isprinter && @$packingslipheader != ""){ ?>
		<tr><td width="100%" colspan="2"><?php print $packingslipheader?></td></tr>
<?php	} ?>
		<tr><td width="100%" colspan="2" align="center">
		  <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td align="left" width="30%">&nbsp; <?php
		if($doedit) print '&nbsp;<input type="checkbox" value="ON" name="autocomplete" id="autocomplete" onclick="setCookie(\'ectautocomp\',this.checked?1:0,600)" '.(@$_COOKIE['ectautocomp']=="1"?'checked="checked" ':'').'/> <strong>'.$yyUsAuCo.'</strong>';
		if(! $isprinter && ! $doedit){
			if(@$previousid!='') print '<input style="width:100px" type="button" value="&laquo; '.$yyPrev.'" onclick="document.location=\'adminorders.php?id='.$previousid.'\'" />';
		} ?>
			</td><td align="center"><strong><?php print $xxOrdNum . ' ' . $alldata['ordID'] . '<br /><br />';
		if(@$fordertimeformatstr!=''){
			setlocale(LC_TIME, $adminLocale);
			print strftime($fordertimeformatstr, $alldata['ordDate']);
		}else
			print date($dateformatstr, $alldata['ordDate']) . ' ' . date('H:i', $alldata['ordDate']);
		?></strong></td><td align="right" width="30%">
<?php	if(! $isprinter && ! $doedit){
			if(@$nextid!='') print '<input style="width:100px" type="button" value="'.$yyNext.' &raquo;" onclick="document.location=\'adminorders.php?id='.$nextid.'\'" />';
		} ?>
			&nbsp;</td></tr></table>
		</td></tr>
<?php	if($isprinter && ! @isset($packingslipaddress)) $packingslipaddress=$invoiceaddress;
		if($isinvoice && @$invoiceaddress != ""){ ?>
		<tr><td width="100%" colspan="2"><?php print $invoiceaddress?></td></tr>
<?php	}elseif($isprinter && @$packingslipaddress != ""){ ?>
		<tr><td width="100%" colspan="2"><?php print $packingslipaddress?></td></tr>
<?php	} ?>
		<tr>
		  <td width="50%" valign="top">
			<input type="hidden" name="custid" id="custid" value="" />
			<table class="ordtbl" width="100%" border="0" cellspacing="2" cellpadding="0">
			  <tr>
				<td>
				  <table width="100%" border="0" cellspacing="0" cellpadding="2" bgcolor="#FFFFFF">
					<tr>
					  <td width="100%" align="center" colspan="2"><strong><?php print $yyBilDet?>.</strong></td>
					</tr>
<?php	if(trim(@$extraorderfield1)!='' && (! $isprinter || trim($alldata['ordExtra1'])!='')){ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $extraorderfield1 ?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php print editfunc($alldata['ordExtra1'],'ordextra1',25)?></td>
					</tr>
<?php	} ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $yyName?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php if(@$usefirstlastname) print editfunc($alldata['ordName'],'name',11).' '.editfunc($alldata['ordLastName'],'lastname',11); else print editfunc($alldata['ordName'],'name',25)?></td>
					</tr>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxAddress?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php print editfunc($alldata['ordAddress'],'address',25)?></td>
					</tr>
<?php	if(@$useaddressline2==TRUE || trim($alldata['ordAddress2']) != ''){ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print ($isprinter?'&nbsp;':$xxAddress2.':')?></strong></td>
					  <td align="<?php print $tleft?>"><?php print editfunc($alldata['ordAddress2'],'address2',25)?></td>
					</tr>
<?php	}
		if($isprinter){ ?>
					<tr>
					  <td>&nbsp;</td>
					  <td align="<?php print $tleft?>"><?php print $alldata['ordCity'].(trim($alldata['ordCity'])!='' && trim($alldata['ordState'])!='' ? ', ' : '').$alldata['ordState']?></td>
					</tr>
<?php	}else{ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxCity?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php print editfunc($alldata['ordCity'],'city',25)?></td>
					</tr>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxAllSta?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php print editspecial($alldata['ordState'],'state',25,'onblur="setstatetax()"')?></td>
					</tr>
<?php	} ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print ($isprinter?'&nbsp;':$xxZip.':')?></strong></td>
					  <td align="<?php print $tleft?>"><?php print editfunc($alldata['ordZip'],'zip',15)?></td>
					</tr>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print ($isprinter?'&nbsp;':$xxCountry.':')?></strong></td>
					  <td align="<?php print $tleft?>"><?php
		if($doedit){
			$foundmatch=FALSE;
			print '<select name="country" id="country" size="1" onchange="setcountrytax()">';
			$sSQL = "SELECT countryName,countryTax,countryOrder FROM countries ORDER BY countryOrder DESC, countryName";
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs2 = mysql_fetch_array($result)){
				print '<option value="' . htmlspecials($rs2['countryName']) . '"';
				if($alldata['ordCountry']==$rs2['countryName'] || ($_GET['id']=='new' && ! $foundmatch)){
					print ' selected="selected"';
					$foundmatch=TRUE;
					$countrytaxrate=$rs2['countryTax'];
					$countryorder=$rs2['countryOrder'];
				}
				print '>' . $rs2['countryName'] . "</option>\r\n";			}
			mysql_free_result($result);
			if(! $foundmatch) print '<option value="' . htmlspecials($alldata['ordCountry']) . '" selected="selected">' . $alldata['ordCountry'] . "</option>\r\n";
			print '</select>';
			if($countryorder==2){
				$sSQL = "SELECT stateTax FROM states WHERE stateName='" . escape_string($alldata['ordState']) . "' OR stateAbbrev='" . escape_string($alldata['ordState']) . "'";
				$result = mysql_query($sSQL) or print(mysql_error());
				if($rs2 = mysql_fetch_array($result))
					$statetaxrate = $rs2['stateTax'];
				mysql_free_result($result);
			}
		}else
			print $alldata['ordCountry'];?></td>
					</tr>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxPhone?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php print editfunc($alldata['ordPhone'],'phone',25)?></td>
					</tr>
<?php	if(trim(@$extraorderfield2)!='' && (! $isprinter || trim($alldata['ordExtra2'])!='')){ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print @$extraorderfield2 ?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php print editfunc($alldata['ordExtra2'],'ordextra2',25)?></td>
					</tr>
<?php	} ?>
				  </table>
				</td>
			  </tr>
			</table>
		  </td>
		  <td width="50%">
<?php	if(trim($alldata['ordShipName']) != '' || trim($alldata['ordShipAddress']) != '' || trim($alldata['ordShipCity']) != '' || trim($alldata['ordShipExtra1'])!='' || $doedit){ ?>
			<table class="ordtbl" width="100%" border="0" cellspacing="2" cellpadding="0">
			  <tr>
				<td>
				  <table width="100%" border="0" cellspacing="0" cellpadding="2" bgcolor="#FFFFFF">
					<tr>
					  <td width="100%" align="center" colspan="2"><strong><?php print $xxShpDet?>.<?php if($doedit) print ' &raquo; <a href="#" onclick="copybillingtoshipping(); return(false);"><strong>'.$yyCopBil.'</strong></a>'?></strong></td>
					</tr>
<?php		if(trim(@$extraorderfield1)!='' && (! $isprinter || trim($alldata['ordShipExtra1'])!='')){ ?>
					<tr>
					  <td align="right"><strong><?php print @$extraorderfield1 ?>:</strong></td>
					  <td align="left"><?php print editfunc($alldata['ordShipExtra1'],'ordshipextra1',25)?></td>
					</tr>
<?php		} ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $yyName?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php if(@$usefirstlastname) print editfunc($alldata['ordShipName'],'sname',11).' '.editfunc($alldata['ordShipLastName'],'slastname',11); else print editfunc($alldata['ordShipName'],'sname',25)?></td>
					</tr>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxAddress?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php print editspecial($alldata['ordShipAddress'],'saddress',25,'onblur="setstatetax();setcountrytax();"')?></td>
					</tr>
<?php		if(@$useaddressline2==TRUE || trim($alldata['ordShipAddress2']) != ''){ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print ($isprinter?'&nbsp;':$xxAddress2.':')?></strong></td>
					  <td align="<?php print $tleft?>"><?php print editfunc($alldata['ordShipAddress2'],'saddress2',25)?></td>
					</tr>
<?php		}
			if($isprinter){ ?>
					<tr>
					  <td>&nbsp;</td>
					  <td align="<?php print $tleft?>"><?php print $alldata['ordShipCity'].(trim($alldata['ordShipCity'])!='' && trim($alldata['ordShipState'])!='' ? ', ' : '').$alldata['ordShipState']?></td>
					</tr>
<?php		}else{ ?>
					<tr>
					  <td align="right"><strong><?php print $xxCity?>:</strong></td>
					  <td align="left"><?php print editfunc($alldata['ordShipCity'],'scity',25)?></td>
					</tr>
					<tr>
					  <td align="right"><strong><?php print $xxAllSta?>:</strong></td>
					  <td align="left"><?php print editspecial($alldata['ordShipState'],'sstate',25,'onblur="setstatetax()"')?></td>
					</tr>
<?php		} ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print ($isprinter?'&nbsp;':$xxZip.':')?></strong></td>
					  <td><?php print editfunc($alldata['ordShipZip'],'szip',15)?></td>
					</tr>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print ($isprinter?'&nbsp;':$xxCountry.':')?></strong></td>
					  <td align="<?php print $tleft?>"><?php
			if($doedit){
				if(trim($alldata['ordShipName']) != '' || trim($alldata['ordShipAddress']) != '') $usingshipcountry=TRUE; else $usingshipcountry=FALSE;
				$foundmatch=($_GET['id']=='new');
				print '<select name="scountry" id="scountry" size="1" onchange="setcountrytax()">';
				$sSQL = "SELECT countryName,countryTax,countryOrder FROM countries ORDER BY countryOrder DESC, countryName";
				$result = mysql_query($sSQL) or print(mysql_error());
				while($rs2 = mysql_fetch_array($result)){
					print '<option value="' . htmlspecials($rs2['countryName']) . '"';
					if($alldata['ordShipCountry']==$rs2['countryName']){
						print ' selected="selected"';
						$foundmatch=TRUE;
						if($usingshipcountry) $countrytaxrate=$rs2['countryTax'];
						$countryorder=$rs2['countryOrder'];
					}
					print '>' . $rs2['countryName'] . "</option>\r\n";			}
				mysql_free_result($result);
				if(! $foundmatch) print '<option value="' . htmlspecials($alldata['ordShipCountry']) . '" selected="selected">' . $alldata['ordShipCountry'] . "</option>\r\n";
				print '</select>';
				if($countryorder==2 && $usingshipcountry){
					$sSQL = "SELECT stateTax FROM states WHERE stateName='" . escape_string($alldata['ordShipState']) . "' OR stateAbbrev='" . escape_string($alldata['ordShipState']) . "'";
					$result = mysql_query($sSQL) or print(mysql_error());
					if($rs2 = mysql_fetch_array($result))
						$statetaxrate = $rs2['stateTax'];
					mysql_free_result($result);
				}
			}else
				print $alldata['ordShipCountry']?></td>
					</tr>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxPhone?>:</strong></td>
					  <td><?php print editfunc($alldata['ordShipPhone'],'sphone',25)?></td>
					</tr>
<?php		if(trim(@$extraorderfield2) != '' && (! $isprinter || trim($alldata['ordShipExtra2'])!='')){ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $extraorderfield2 ?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php print editfunc($alldata['ordShipExtra2'],'ordshipextra2',25)?></td>
					</tr>
<?php		} ?>
				  </table>
				</td>
			  </tr>
			</table>
<?php	}else{
			print '&nbsp;';
		} ?>
		  </td>
		</tr>
		<tr>
		  <td colspan="2">
			<table class="ordtbl" width="100%" border="0" cellspacing="2" cellpadding="0">
			  <tr>
				<td>
				  <table width="100%" border="0" cellspacing="4" cellpadding="0" bgcolor="#FFFFFF">
					<tr><td colspan="4" align="center"><strong><?php print $yyAddDet?>.</strong></td></tr>
					<tr>
					  <td align="<?php print $tright?>"><?php if(! $isprinter && $alldata['ordAuthNumber'] != '' && ! $doedit) print '<input type="button" value="Resend" onclick="javascript:openemailpopup(\'id=' . $alldata['ordID'] . '\')" />' ?>
					  <strong><?php print $xxEmail?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php
		if($isprinter || $doedit) print editspecial($alldata['ordEmail'],'email',35,'AUTOCOMPLETE="off" onkeydown="return combokey(this,event)" onkeyup="combochange(this,event)"'); else print '<a href="mailto:' . htmlspecials($alldata['ordEmail']) . '">' . htmlspecials($alldata['ordEmail']) . '</a>';
		if($doedit) print showgetoptionsselect('selectemail'); ?></td>
					</tr>
<?php	if(trim(@$extracheckoutfield1) != ''){
		$checkoutfield1 = '<strong>' . $extracheckoutfield1 . '</strong>';
		$checkoutfield2 = editfunc($alldata['ordCheckoutExtra1'],'ordcheckoutextra1',25)
?>					<tr>
					  <td align="<?php print $tright?>"><?php if(@$extracheckoutfield1reverse) print $checkoutfield2; else print $checkoutfield1 . '<strong>:</strong>' ?></td>
					  <td align="<?php print $tleft?>"><?php if(@$extracheckoutfield1reverse) print $checkoutfield1; else print $checkoutfield2 ?></td>
					</tr>
<?php	}
		if(trim(@$extracheckoutfield2) != ''){
			$checkoutfield1 = '<strong>' . $extracheckoutfield2 . '</strong>';
			$checkoutfield2 = editfunc($alldata['ordCheckoutExtra2'],'ordcheckoutextra2',25)
?>					<tr>
					  <td align="<?php print $tright?>"><?php if(@$extracheckoutfield2reverse) print $checkoutfield2; else print $checkoutfield1 . '<strong>:</strong>' ?></td>
					  <td align="<?php print $tleft?>" colspan="3"><?php if(@$extracheckoutfield2reverse) print $checkoutfield1; else print $checkoutfield2 ?></td>
					</tr>
<?php	}
		if(! $isprinter){ ?>
					<tr>
					  <td align="right"><strong><?php print $yyIPAdd?>:</strong></td>
					  <td align="left"><?php if($doedit) print editfunc($alldata['ordIP'],'ipaddress',15); else print '<a href="http://www.infosniper.net/index.php?lang=1&ip_address='.urlencode($alldata['ordIP']).'" target="_blank">'.htmlspecials($alldata['ordIP']).'</a>'?></td>
					  <td align="right"><strong><?php print $yyAffili?>:</strong></td>
					  <td align="left"><?php print editfunc($alldata['ordAffiliate'],'PARTNER',15)?></td>
					</tr>
<?php	}
		if((trim($alldata['ordDiscountText'])!='' && (! $isprinter || $isinvoice)) || $doedit){ ?>
					<tr>
					  <td align="right" valign="top"><strong><?php print $xxAppDs?>:</strong></td>
					  <td align="left" colspan="3"><?php if($doedit) print '<textarea name="discounttext" cols="50" rows="2">' . str_replace(array('<br />','<'), array("\r\n",'&lt;'), $alldata['ordDiscountText']) . '</textarea>'; else print str_replace("\r\n",'<br />',htmlspecials(str_replace('<br />',"\r\n",$alldata['ordDiscountText']))); ?></td>
					</tr>
<?php	}
		if(! $isprinter){
			$sSQL = "SELECT gcaGCID,gcaAmount FROM giftcertsapplied WHERE gcaOrdID=".$theid;
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_assoc($result))
				print '<tr><td align="right"><strong>' . $yyCerNum . '</strong></td><td>' . $rs['gcaGCID'] . ' ' . FormatEuroCurrency($rs['gcaAmount']) . ' ' . '<a href="admingiftcert.php?id=' . $rs['gcaGCID'] . '">' . $yyClkVw . '</a></td></tr>';
			mysql_free_result($result);
		}
		if(! $isprinter && ! $doedit) print '<form method="post" action="adminorders.php"><input type="hidden" name="updatestatus" value="1" /><input type="hidden" name="orderid" value="' . @$_GET["id"] . '" />';
		if($alldata['ordShipCarrier'] != 0 || $alldata['ordShipType'] != '' || $doedit){ ?>
					<tr>
					  <td align="<?php print $tright?>"><strong><?php print $xxShpMet?>:</strong></td>
					  <td align="<?php print $tleft?>"><?php	if(! $isprinter){ ?>
							<select name="shipcarrier" id="shipcarrier" size="1">
							<option value="<?php print $alldata['ordShipCarrier']?>"><?php print $yyOther?></option>
							<option value="3"<?php if((int)$alldata['ordShipCarrier']==3) print ' selected="selected"'?>>USPS</option>
							<option value="4"<?php if((int)$alldata['ordShipCarrier']==4) print ' selected="selected"'?>>UPS</option>
							<option value="6"<?php if((int)$alldata['ordShipCarrier']==6) print ' selected="selected"'?>>CanPos</option>
							<option value="7"<?php if((int)$alldata['ordShipCarrier']==7) print ' selected="selected"'?>>FedEx</option>
							<option value="8"<?php if((int)$alldata['ordShipCarrier']==8) print ' selected="selected"'?>>DHL</option>
							</select> <?php		}
												print str_replace('&amp;','&',editfunc($alldata['ordShipType']=='MODWARNOPEN'?$yyMoWarn:$alldata['ordShipType'],'shipmethod',25)); ?></td>
					  <td align="<?php print $tright?>"><strong><?php if($doedit) print $xxCLoc . ':'?></strong></td>
					  <td align="<?php print $tleft?>"><?php	if($doedit){
													print '<select name="commercialloc" size="1">';
													print '<option value="N">' . $yyNo . '</option>';
													print '<option value="Y"' . (($alldata['ordComLoc']&1)==1 ? ' selected="selected"' : '') . '>' . $yyYes . '</option>';
													print '</select>';
												}?></td>
					</tr>
<?php		if($doedit){ ?>
					<tr>
					  <td align="right"><strong><?php print $xxShpIns?>:</strong></td>
					  <td align="left"><?php	print '<select name="wantinsurance" size="1">';
												print '<option value="N">' . $yyNo . '</option>';
												print '<option value="Y"' . (($alldata['ordComLoc'] & 2)==2 ? ' selected="selected"' : '') . '>' . $yyYes . '</option>';
												print '</select>'; ?></td>
					  <td align="right"><strong><?php print $xxSatDe2?>:</strong></td>
					  <td align="left"><?php	print '<select name="saturdaydelivery" size="1">';
												print '<option value="N">' . $yyNo . '</option>';
												print '<option value="Y"' . (($alldata['ordComLoc'] & 4)==4 ? ' selected="selected"' : '') . '>' . $yyYes . '</option>';
												print '</select>' ?></td>
					</tr>
					<tr>
					  <td align="right"><strong><?php print $xxSigRe2?>:</strong></td>
					  <td align="left"><?php	print '<select name="signaturerelease" size="1">';
												print '<option value="N">' . $yyNo . '</option>';
												print '<option value="Y"' . (($alldata['ordComLoc'] & 8)==8 ? ' selected="selected"' : '') . '>' . $yyYes . '</option>';
												print '</select>' ?></td>
					  <td align="right"><strong><?php print $xxInsDe2?>:</strong></td>
					  <td align="left"><?php	print '<select name="insidedelivery" size="1">';
												print '<option value="N">' . $yyNo . '</option>';
												print '<option value="Y"' . (($alldata['ordComLoc'] & 16)==16 ? ' selected="selected"' : '') . '>' . $yyYes . '</option>';
												print '</select>' ?></td>
					</tr>
<?php		}elseif($alldata['ordComLoc'] > 0){
				if($isprinter) $thestyle=''; else $thestyle=' style="color:#FF0000"';
				$shipopts='<strong>Shipping options:</strong>';
				if(($alldata['ordComLoc'] & 1)==1){ print '<tr><td align="right">' . $shipopts.'</td><td align="left" colspan="3"'.$thestyle.'>' . $xxCerCLo . '</td></tr>'; $shipopts='';}
				if(($alldata['ordComLoc'] & 2)==2){ print '<tr><td align="right">' . $shipopts.'</td><td align="left" colspan="3"'.$thestyle.'>' . $xxShiInI . '</td></tr>'; $shipopts='';}
				if(($alldata['ordComLoc'] & 4)==4){ print '<tr><td align="right">' . $shipopts.'</td><td align="left" colspan="3"'.$thestyle.'>' . $xxSatDeR . '</td></tr>'; $shipopts='';}
				if(($alldata['ordComLoc'] & 8)==8){ print '<tr><td align="right">' . $shipopts.'</td><td align="left" colspan="3"'.$thestyle.'>' . $xxSigRe2 . '</td></tr>'; $shipopts='';}
				if(($alldata['ordComLoc'] & 16)==16){ print '<tr><td align="right">' . $shipopts.'</td><td align="left" colspan="3"'.$thestyle.'>' . $xxInsDe2 . '</td></tr>'; $shipopts='';}
			}
		}
		$ordAuthNumber = trim($alldata['ordAuthNumber']);
		$ordTransID = trim($alldata['ordTransID']);
		if(! $isprinter && ($ordAuthNumber != '' || $ordTransID != '' || $doedit)){ ?>
					<tr>
					  <td align="right"><strong><?php print $yyAutCod?>:</strong></td>
					  <td align="left"><?php print editfunc($ordAuthNumber,'ordAuthNumber',15) ?></td>
					  <td align="right"><strong><?php print $yyTranID?>:</strong></td>
					  <td align="left"><?php print editfunc($ordTransID,'ordTransID',15) ?></td>
					</tr>
<?php	}
		$ordAddInfo = Trim($alldata['ordAddInfo']);
		if($ordAddInfo != '' || $doedit){ ?>
					<tr>
					  <td align="<?php print $tright?>" valign="top"><strong><?php print str_replace(' ', '&nbsp;', $xxAddInf)?>:</strong></td>
					  <td align="<?php print $tleft?>" colspan="3"><?php
			if($doedit)
				print '<textarea name="ordAddInfo" cols="50" rows="4">' . strip_tags($ordAddInfo) . '</textarea>';
			else
				print str_replace(array("\r\n","\n"),array('<br />','<br />'),strip_tags($ordAddInfo)); ?></td>
					</tr>
<?php	}
		if(! $isprinter){
			//if($alldata['ordPayProvider']==20){
			if(FALSE){
				$ordCNum = $alldata['ordCNum'];
				if($ordCNum != ''){ ?>
					<tr>
					  <td align="right"><strong>Partial CC Number:</strong></td>
					  <td align="left" colspan="3">-<?php print htmlspecials($ordCNum) ?></td>
					</tr>
<?php			}
			}
		?>			<tr>
					  <td align="right"><strong><?php print $yyTraNum?>:</strong></td>
					  <td align="left"><input type="text" name="ordTrackNum" id="ordTrackNum" size="25" value="<?php print htmlspecials($alldata['ordTrackNum'])?>" /></td>
					  <td align="right"><strong><?php print $yyInvNum?>:</strong></td>
					  <td align="left"><input type="text" name="ordInvoice" size="25" value="<?php print htmlspecials($alldata['ordInvoice'])?>" /></td>
					</tr>
					<tr>
					  <td align="right"><strong><?php print $yyOrdSta?>:</strong></td>
					  <td align="left"<?php if(@$loyaltypoints=='') print ' colspan="3"'?>><select name="ordStatus" size="1"><?php
		for($index=0; $index < $numstatus; $index++){
			print '<option value="' . $allstatus[$index]['statID'] . '"';
			if($alldata['ordStatus']==$allstatus[$index]['statID']) print ' selected="selected">' . $allstatus[$index]['statPrivate'] . ' ' . date($admindatestr, $alldata['ordStatusDate']) . ' ' . date('H:i', $alldata['ordStatusDate']) . '</option>'; else print '>' . $allstatus[$index]['statPrivate'] . '</option>';
		} ?></select>&nbsp;&nbsp;<?php if(! $doedit){ ?><input type="checkbox" name="emailstat" value="1" <?php if(@$_POST["emailstat"]=="1" || @$alwaysemailstatus==TRUE) print "checked"?>/> <?php print $yyEStat?><?php } ?></td>
<?php		if(@$loyaltypoints!=''){ ?>
					  <td align="right"><strong><?php print $xxLoyPoi?>:</strong></td>
					  <td align="left"><?php print editfunc($alldata['loyaltyPoints'],'loyaltyPoints',10) ?></td>
<?php		} ?>
					</tr>
					<tr>
					  <td align="right" valign="top"><strong><?php print $yyStaInf?>:</strong></td>
					  <td align="left" colspan="3"><textarea name="ordStatusInfo" id="ordStatusInfo" cols="50" rows="4"><?php print htmlspecials($alldata['ordStatusInfo'])?></textarea> <?php if(! $doedit) print '<input type="submit" value="' . $yyUpdate . '" ' . ($alldata['ordPayProvider']==20 ? 'onclick="updategooglestatus(\'message\',' . $_GET['id'] . ')" ' : '') . '/>'?></td>
					</tr>
<?php		if($alldata['ordReferer']!=''){ ?>
					<tr>
					  <td align="right"><strong>Referer:</strong></td>
					  <td align="left" colspan="3"><input type="text" name="ordreferer" value="<?php print str_replace('"', '&quot;', $alldata['ordReferer'] . ($alldata['ordQuerystr']!='' ? '?' . $alldata['ordQuerystr'] : ''))?>" size="80" /></td>
					</tr>
<?php		}
			if(($alldata['ordPayProvider']==1 || $alldata['ordPayProvider']==3 || $alldata['ordPayProvider']==13 || $alldata['ordPayProvider']==18 || $alldata['ordPayProvider']==19 || $alldata['ordPayProvider']==20 || $alldata['ordPayProvider']==21) && $alldata['ordAuthNumber'] != ''){
				if($alldata['ordPayProvider']==20){ ?>
					<tr><td align="center" colspan="4"><strong>Update Google Account Status:</strong> <span id="googleupdatespan"></span></td></tr>
					<tr>
					  <td align="center" colspan="4">
						<input type="button" value="Charge Order" onclick="updategoogleorder('Google','charge',<?php print $alldata['ordID']?>)" />
						<input type="button" value="Cancel Order" onclick="updategoogleorder('Google','cancel',<?php print $alldata['ordID']?>)" />
						<input type="button" value="Refund Order" onclick="updategoogleorder('Google','refund',<?php print $alldata['ordID']?>)" />
						<input type="button" value="Ship Order" onclick="updategoogleorder('Google','ship',<?php print $alldata['ordID']?>)" />
					  </td>
					</tr>
<?php			}elseif($alldata['ordPayProvider']==21){ ?>
					<tr><td align="center" colspan="4"><strong>Amazon Settle / Refund:</strong> <span id="googleupdatespan"></span></td></tr>
					<tr>
					  <td align="center" colspan="4">
						<input type="button" value="Settle Order" onclick="updategoogleorder('Amazon','settle',<?php print $alldata['ordID']?>)" />
						<input type="button" value="Refund Order" onclick="updategoogleorder('Amazon','refund',<?php print $alldata['ordID']?>)" />
						<input type="button" value="Partial Refund:" onclick="updategoogleorder('Amazon','partialrefund',<?php print $alldata['ordID']?>)" />
						<input type="text" name="txamount" id="txamount" size="5" value="<?php print number_format(($alldata['ordTotal']+$alldata['ordStateTax']+$alldata['ordCountryTax']+$alldata['ordHSTTax']+$alldata['ordShipping']+$alldata['ordHandling'])-$alldata['ordDiscount'], (@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2),'.','')?>" />
					  </td>
					</tr>
<?php			}elseif($alldata['ordPayProvider']==1 || $alldata['ordPayProvider']==18 || $alldata['ordPayProvider']==19){ ?>
					<tr><td align="center" colspan="4"><strong>PayPal Authorization / Capture:</strong> <span id="googleupdatespan"></span></td></tr>
					<tr>
					  <td align="right"><strong>Capture Amount:</strong></td>
					  <td align="left" colspan="3"><input type="text" name="captureamount" id="captureamount" size="10" value="<?php print number_format(($alldata['ordTotal']+$alldata['ordStateTax']+$alldata['ordCountryTax']+$alldata['ordHSTTax']+$alldata['ordShipping']+$alldata['ordHandling'])-$alldata['ordDiscount'], (@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2),'.','')?>" />
					  <select name="additionalcapture" id="additionalcapture" size="1"><option value="0">Close Authorization</option><option value="1">Leave Open for Additional Capture</option></select>
					  </td>
					</tr>
					<tr>
					  <td align="right"><strong>Note to buyer:</strong></td>
					  <td align="left" colspan="3"><textarea name="buyernote" id="buyernote" cols="50" rows="4"></textarea></td>
					</tr>
					<tr>
					  <td align="right"><strong>Action:</strong></td>
					  <td align="left" colspan="3"><select name="paypalaction" id="paypalaction" size="1" onchange="setpaypalelements()"><option value="charge">Capture</option><option value="void">Void</option><option value="reauth">Reauthorization</option></select>
					  <input type="button" value="Inform PayPal" onclick="updatepaypalorder('PayPal',<?php print $alldata['ordID']?>)" />
					  </td>
					</tr>
<?php			}else{ ?>
					<tr><td align="center" colspan="4"><input type="button" value="Capture Funds" onclick="javascript:openemailpopup('oid=<?php print $alldata['ordID']?>')" /></td></tr>
<?php			}
			}
			if(! $doedit) print '</form>';
			//if((int)$alldata["ordPayProvider"]==10){
			if(FALSE){			?>
					<tr>
					  <td align="center" colspan="4"><hr width="50%">
					  </td>
					</tr>
<?php			if(@$_SERVER["HTTPS"] != "on" && (@$_SERVER["SERVER_PORT"] != "443") && @$nochecksslserver != TRUE){ ?>
					<tr>
					  <td align="center" colspan="4"><span style="color:#FF0000;font-weight:bold">You do not appear to be viewing this page on a secure (https) connection. Credit card information cannot be shown.</span></td>
					</tr>
<?php			}else{
					$ordCNum = $alldata["ordCNum"];
					if($ordCNum != ""){
						$cnumarr = "";
						$encryptmethod = strtolower(@$encryptmethod);
						if($encryptmethod=="none"){
							$cnumarr = explode("&",$ordCNum);
						}elseif($encryptmethod=="mcrypt"){
							if(@$mcryptalg == "") $mcryptalg = MCRYPT_BLOWFISH;
							$td = mcrypt_module_open($mcryptalg, '', 'cbc', '');
							$thekey = @$ccencryptkey;
							$thekey = substr($thekey, 0, mcrypt_enc_get_key_size($td));
							$cnumarr = explode(" ", $ordCNum);
							$iv = @$cnumarr[0];
							$iv = @pack("H" . strlen($iv), $iv);
							$ordCNum = @pack("H" . strlen(@$cnumarr[1]), @$cnumarr[1]);
							mcrypt_generic_init($td, $thekey, $iv);
							$cnumarr = explode("&", mdecrypt_generic($td, $ordCNum));
							mcrypt_generic_deinit($td);
							mcrypt_module_close($td);
						}elseif($encryptmethod=="publickey"){
						}else{
							print '<tr><td colspan="4">WARNING: $encryptmethod is not set. Please see http://www.ecommercetemplates.com/phphelp/ecommplus/parameters.asp#encryption</td></tr>';
						}
					}
					if($encryptmethod=="publickey"){ ?>
					<tr>
					  <td align="center" colspan="4">
				  <table>
					<tr>
					  <td align="right" colspan="2"><strong><?php print "Encrypted Data"?>:</strong></td>
					  <td align="left" colspan="2"><textarea cols="70" rows="4" id="ordcnumenctxt"><?php
							print $ordCNum ?></textarea></td>
					</tr>
				  </table>
<script type="text/javascript">
document.getElementById('ordcnumenctxt').select();
</script>
					  </td>
					</tr>
<?php				}else{ ?>
					<tr>
					  <td width="50%" align="right" colspan="2"><strong><?php print $xxCCName?>:</strong></td>
					  <td width="50%" align="left" colspan="2"><?php
							if(@$encryptmethod!=""){
									if(is_array(@$cnumarr)) print trim(htmlspecials(URLDecode(@$cnumarr[4])));
							} ?></td>
					</tr>
					<tr>
					  <td align="right" colspan="2"><strong><?php print $yyCarNum?>:</strong></td>
					  <td align="left" colspan="2"><?php
							if($ordCNum != ""){
								if(is_array($cnumarr)) print htmlspecials($cnumarr[0]);
							}else{
								print "(no data)";
							} ?></td>
					</tr>
					<tr>
					  <td align="right" colspan="2"><strong><?php print $yyExpDat?>:</strong></td>
					  <td align="left" colspan="2"><?php
							if(@$encryptmethod!=""){
									if(is_array(@$cnumarr)) print htmlspecials(@$cnumarr[1]);
							} ?></td>
					</tr>
					<tr>
					  <td align="right" colspan="2"><strong>CVV Code:</strong></td>
					  <td align="left" colspan="2"><?php
							if(@$encryptmethod!=""){
									if(is_array(@$cnumarr)) print htmlspecials(@$cnumarr[2]);
							} ?></td>
					</tr>
					<tr>
					  <td align="right" colspan="2"><strong>Issue Number:</strong></td>
					  <td align="left" colspan="2"><?php
							if(@$encryptmethod!=""){
									if(is_array(@$cnumarr)) print htmlspecials(@$cnumarr[3]);
							} ?></td>
					</tr>
<?php				}
					if($ordCNum != "" && !$doedit){ ?>
				  <form method=POST action="adminorders.php?id=<?php print $_GET["id"]?>">
					<input type="hidden" name="delccdets" value="<?php print $_GET["id"]?>" />
					<tr>
					  <td width="100%" align="center" colspan="4"><input type=submit value="<?php print $yyDelCC?>" /></td>
					</tr>
				  </form>
<?php				}
				}
			}
		}elseif($isinvoice && trim($alldata['ordInvoice']) != ''){ ?>
					<tr>
					  <td align="right"><strong><?php print $yyInvNum?>:</strong></td>
					  <td align="left" colspan="3"><?php print editfunc($alldata['ordInvoice'],'ordInvoice',15)?></td>
					</tr>
<?php
		} ?>
					<tr><td width="100%" align="center" colspan="4">&nbsp;<br /></td></tr>
				  </table>
				</td>
			  </tr>
			</table>
		  </td>
		</tr>
	  </table>
<div id="debugdiv"></div>
<span id="productspan">
<?php	$WSP=''; $OWSP='';
		if($alldata['ordClientID']!=0){
			$sSQL = "SELECT clActions,clPercentDiscount FROM customerlogin WHERE clID='".$alldata['ordClientID']."'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs2 = mysql_fetch_assoc($result)){
				if(($rs2['clActions'] & 8) == 8){
					$WSP = 'pWholesalePrice AS ';
					if(@$wholesaleoptionpricediff==TRUE) $OWSP = 'optWholesalePriceDiff AS ';
				}
				if(($rs2['clActions'] & 16) == 16){
					$WSP = ((100.0-(double)$rs['clPercentDiscount'])/100.0) . '*'.(($rs2['clActions'] & 8)==8?'pWholesalePrice':'pPrice').' AS ';
					if(@$wholesaleoptionpricediff==TRUE) $OWSP = ((100.0-$rs2['clPercentDiscount'])/100.0) . '*'.(($rs2['clActions'] & 8)==8?'optWholesalePriceDiff':'optPriceDiff').' AS ';
				}
			}
			mysql_free_result($result);
		}
?>
	<table width="100%" border="0" cellspacing="2" cellpadding="0" bgcolor="#FFFFFF">
	  <tr>
		<td>
		  <table class="ordtbl" id="producttable" width="100%" border="0" cellspacing="2" cellpadding="4">
			<tr class="cobll">
			  <td><strong><?php print $xxPrId?></strong></td>
			  <td><strong><?php print $xxPrNm?></strong></td>
			  <td><strong><?php print $xxPrOpts?></strong></td>
<?php	if($isinvoice) print '<td><strong>' . $xxUnitPr . '</strong></td>'; ?>
			  <td><strong><?php print $xxQuant?></strong></td>
<?php	if(! $isprinter || $isinvoice) print '<td><strong>' . ($doedit ? $xxUnitPr : $xxPrice) . '</strong></td>';
		if($doedit) print '<td align="center"><strong>DEL</strong></td>' ?>
			</tr>
<?php	$totoptpricediff = 0;
		$stockjs = '';
		if($allorders!='' && mysql_num_rows($allorders)>0){
			$totoptpricediff = 0;
			$rowcounter=0;
			while($rsOrders = mysql_fetch_assoc($allorders)){
				$optpricediff = 0;
				if($rsOrders['pStockByOpts']==0 && $alldata['ordAuthStatus']!='MODWARNOPEN') $stockjs .= "stock['pid_" . $rsOrders['cartProdId'] . "']+=" . $rsOrders['cartQuantity'] . ";\r\n";
?>
			<tr class="cobll">
			  <td valign="top" style="white-space:nowrap;"><?php
				if($doedit) print '<input type="button" value="..." onclick="updateoptions(' . $rowcounter . ')">&nbsp;<input type="hidden" name="cartid' . $rowcounter . '" value="' . htmlspecials($rsOrders['cartID']) . '" /><input type="hidden" id="stateexempt' . $rowcounter . '" value="' . (($rsOrders['pExemptions'] AND 1)==1?'true':'false') . '" /><input type="hidden" id="countryexempt' . $rowcounter . '" value="' . (($rsOrders['pExemptions'] & 2)==2?'true':'false') . '" />';
				print '<strong>' . editspecial($rsOrders["cartProdId"],'prodid' . $rowcounter,18,'AUTOCOMPLETE="off" onkeydown="return combokey(this,event)" onkeyup="combochange(this,event)"') . '</strong>';
				if($rsOrders['cartProdId']==$giftcertificateid){
					$sSQL = "SELECT gcID FROM giftcertificate WHERE gcCartID=" . $rsOrders['cartID'];
					$result = mysql_query($sSQL) or print(mysql_error());
					if($rs = mysql_fetch_assoc($result)){
						print '<input type="button" value="'.$yyView.'" onclick="document.location=\'admingiftcert.php?id='.$rs['gcID'].'\'" />';
					}
					mysql_free_result($result);
				}
				if($doedit) print showgetoptionsselect('selectprodid'.$rowcounter);
				?></td>
			  <td valign="top"><?php
				print str_replace('&amp;','&',editspecial(decodehtmlentities($rsOrders['cartProdName']),'prodname' . $rowcounter,24,'AUTOCOMPLETE="off" onkeydown="return combokey(this,event)" onkeyup="combochange(this,event)"'));
				if($doedit) print showgetoptionsselect('selectprodname'.$rowcounter);
			?></td>
			  <td valign="top"><?php
				if($doedit) print '<span id="optionsspan' . $rowcounter . '">';
				$sSQL = "SELECT coOptGroup,coCartOption,coPriceDiff,coOptID,optGroup FROM cartoptions LEFT JOIN options ON cartoptions.coOptID=options.optID WHERE coCartID=" . $rsOrders["cartID"] . " ORDER BY coID";
				$result = mysql_query($sSQL) or print(mysql_error());
				if(mysql_num_rows($result) > 0){
					$rs2 = mysql_fetch_array($result);
					if($rsOrders['pStockByOpts']!=0 && $alldata['ordAuthStatus']!='MODWARNOPEN') $stockjs .= "stock['oid_" . $rs2['coOptID'] . "']+=" . $rsOrders['cartQuantity'] . ";\r\n";
					if($doedit) print '<table border="0" cellspacing="0" cellpadding="1" width="100%">';
					do{
						if($doedit){
							print '<tr><td align="right"><strong>' . $rs2["coOptGroup"] . ':</strong></td><td>';
							if(is_null($rs2["optGroup"])){
								print 'xxxxxx';
							}else{
								$sSQL="SELECT optID," . getlangid('optName',32) . ','.$OWSP."optPriceDiff,optType,optFlags,optStock,optTxtCharge,optPriceDiff AS optDims FROM options INNER JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optGroup=" . $rs2["optGroup"] . ' ORDER BY optID';
								$result2 = mysql_query($sSQL) or print(mysql_error());
								if($rsl = mysql_fetch_assoc($result2)){
									if(abs($rsl['optType'])==1 || abs($rsl['optType'])==2 || abs($rsl['optType'])==4){
										print '<select onchange="dorecalc(true)" name="optn' . $rowcounter . '_' . $rs2["coOptID"] . '" id="optn' . $rowcounter . '_' . $rs2["coOptID"] . '" size="1">';
										do {
											print '<option value="' . $rsl["optID"] . "|" . (($rsl["optFlags"] & 1) == 1 ? ($rsOrders["cartProdPrice"]*$rsl["optPriceDiff"])/100.0 : $rsl["optPriceDiff"]) . '"';
											if($rsl["optID"]==$rs2["coOptID"]) print ' selected="selected"';
											print '>' . $rsl[getlangid("optName",32)];
											if((double)$rsl["optPriceDiff"] != 0){
												print ' ';
												if((double)$rsl["optPriceDiff"] > 0) print '+';
												if(($rsl["optFlags"] & 1) == 1)
													print number_format(($rsOrders["cartProdPrice"]*$rsl["optPriceDiff"])/100.0,2,'.','');
												else
													print number_format($rsl["optPriceDiff"],2,'.','');
											}
											print '</option>';
										} while($rsl = mysql_fetch_array($result2));
										print '</select>';
									}else{
										if($rsl['optTxtCharge']!=0) print '<script language="javascript" type="text/javascript">opttxtcharge[' . $rsl['optID'] . ']=' . $rsl['optTxtCharge'] . ';</script>';
										print "<input type='hidden' name='optn" . $rowcounter . '_' . $rs2["coOptID"] . "' value='" . $rsl["optID"] . "' /><textarea name='voptn" . $rowcounter . '_' . $rs2["coOptID"] . "' id='voptn". $rowcounter. '_' . $rs2["coOptID"] . "' cols='30' rows='3'>";
										print htmlspecials($rs2['coCartOption']) . '</textarea>';
									}
								}
								mysql_free_result($result2);
							}
							print '</td></tr>';
						}else{
							print '<strong>' . $rs2["coOptGroup"] . ':</strong> ' . str_replace(array("\r\n","\n"),array('<br />','<br />'),htmlspecials($rs2['coCartOption'])) . '<br />';
						}
						if($doedit)
							$optpricediff += $rs2["coPriceDiff"];
						else
							$rsOrders["cartProdPrice"] += $rs2["coPriceDiff"];
					}while($rs2 = mysql_fetch_array($result));
					if($doedit) print '</table>';
				}else{
					print ' - ';
				}
				mysql_free_result($result);
				if($doedit) print '</span>' ?></td>
<?php			if($isinvoice) print '<td valign="top">' . FormatEuroCurrency($rsOrders['cartProdPrice']) . '</td>'; ?>
			  <td valign="top"><?php print editfunc($rsOrders["cartQuantity"],'quant' . $rowcounter . '" onchange="dorecalc(true)',5)?></td>
<?php			if(! $isprinter || $isinvoice){ ?>
			  <td valign="top"><?php if($doedit) print editnumeric($rsOrders['cartProdPrice'],'price' . $rowcounter . '" onchange="dorecalc(true)',7); else print FormatEuroCurrency($rsOrders["cartProdPrice"]*$rsOrders["cartQuantity"])?>
<?php					if($doedit){
							print '<input type="hidden" id="optdiffspan' . $rowcounter . '" value="' . $optpricediff . '">';
							$totoptpricediff += ($optpricediff*$rsOrders["cartQuantity"]);
						}
			?></td>
<?php			}
				if($doedit) print '<td align="center"><input type="checkbox" name="del_' . $rowcounter . '" id="del_' . $rowcounter . '" value="yes" /></td>' ?>
			</tr>
<?php				$rowcounter++;
			}
		}
		if($allorders!='') mysql_free_result($allorders);
		if($doedit){ ?>
			<tr class="cobll">
			  <td align="right" colspan="4">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				  <tr>
					<td align="center"><?php if($doedit) print '<input style="width:30px;" type="button" value="-" onclick="extraproduct(\'-\')"> ' . $yyMoProd . ' <input style="width:30px;" type="button" value="+" onclick="extraproduct(\'+\')"> &nbsp; <input type="button" value="' . $yyRecal . '" onclick="dorecalc(false)">'?></td>
					<td align="right" width="100"><strong><?php print str_replace(' ', '&nbsp;', $yyOptTot)?></strong></td>
				  </tr>
				</table></td>
			  <td align="left" colspan="2"><span id="optdiffspan"><?php print number_format($totoptpricediff, 2, '.', '')?></span><script language="javascript" type="text/javascript">
			var stock = new Array();
<?php		$optgroups='';
			$addcomma='';
			if($theid!='0'){
				$sSQL = "SELECT DISTINCT cartID,pID,pInStock,pStockByOpts FROM cart INNER JOIN products ON cart.cartProdId=products.pID WHERE cartOrderID=".$theid;
				$result = mysql_query($sSQL) or print(mysql_error());
				while($rs = mysql_fetch_assoc($result)){
					print "stock['pid_".$rs['pID']."']=";
					if($rs['pStockByOpts']==0)
						print $rs['pInStock'].";\r\n";
					else{
						print "'bo';\r\n";
						$sSQL = "SELECT coID,optStock,coOptID,optGrpID FROM cart INNER JOIN cartoptions ON cart.cartID=cartoptions.coCartID INNER JOIN options ON cartoptions.coOptID=options.optID INNER JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optType IN (-4,-2,-1,1,2,4) AND cartID=".$rs['cartID'];
						$result2 = mysql_query($sSQL) or print(mysql_error());
						while($rs2 = mysql_fetch_assoc($result2)){
							$optgroups .= $addcomma . $rs2['optGrpID'];
							$addcomma = ',';
						}
						mysql_free_result($result2);
					}
				}
				mysql_free_result($result);
			}
			if($optgroups!=''){
				$sSQL = "SELECT optID,optStock FROM options WHERE optGroup IN (" . $optgroups . ")";
				$result = mysql_query($sSQL) or print(mysql_error());
				while($rs = mysql_fetch_assoc($result)){
					print "stock['oid_" . $rs['optID']."']=" . $rs['optStock'] . ";\r\n";
				}
				mysql_free_result($result);
			}
			print $stockjs;
			if(@$_GET['id']=='new') print "extraproduct('+');\r\n";
?></script></td>
			</tr>
<?php	}
		if(! $isprinter || $isinvoice){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				  <tr>
					<td align="center"><?php if($stockManage!=0 && $doedit) print ' <input type="checkbox" name="updatestock" value="ON" checked> <strong>' . $yyUpStLv . '</strong>'; else print '&nbsp;' ?></td>
					<td align="<?php print $tright?>" width="100"><strong><?php print str_replace(' ', '&nbsp;', $xxOrdTot)?>:</strong></td>
				  </tr>
				</table></td>
			  <td align="<?php print $tleft?>"><?php print editnumeric($alldata['ordTotal'],'ordtotal',7)?></td>
<?php			if($doedit) print '<td align="center">&nbsp;</td>' ?>
			</tr>
<?php		if($isprinter && @$combineshippinghandling==TRUE){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxShipHa?>:</strong></td>
			  <td align="<?php print $tleft?>"><?php print FormatEuroCurrency($alldata['ordShipping']+$alldata['ordHandling'])?></td>
			</tr>
<?php		}else{
				if((double)$alldata['ordShipping']!=0.0 || $doedit){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxShippg?>:</strong></td>
			  <td align="<?php print $tleft?>"><?php print editnumeric($alldata['ordShipping'],'ordShipping',7)?></td>
<?php			if($doedit) print '<td align="center">&nbsp;</td>' ?>
			</tr>
<?php			}
				if((double)$alldata['ordHandling']!=0.0 || $doedit){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxHndlg?>:</strong></td>
			  <td align="<?php print $tleft?>"><?php print editnumeric($alldata['ordHandling'],'ordHandling',7)?></td>
<?php				if($doedit) print '<td align="center">&nbsp;</td>' ?>
			</tr>
<?php			}
			}
			if((double)$alldata['ordDiscount']!=0.0 || $doedit){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxDscnts?>:</strong></td>
			  <td align="<?php print $tleft?>"><span style="color:#FF0000"><?php print editnumeric($alldata['ordDiscount'],'ordDiscount',7)?></span></td>
<?php			if($doedit) print '<td align="center">&nbsp;</td>' ?>
			</tr>
<?php		}
			if((double)$alldata['ordStateTax']!=0.0 || $doedit){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxStaTax?>:</strong></td>
			  <td align="<?php print $tleft?>"><?php print editnumeric($alldata['ordStateTax'],'ordStateTax',7)?></td>
<?php			if($doedit) print '<td align="center" style="white-space:nowrap;"><input type="text" style="text-align:right" name="staterate" id="staterate" size="2" value="' . $statetaxrate . '">%</td>' ?>
			</tr>
<?php		}
			if((double)$alldata['ordCountryTax']!=0.0 || $doedit){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxCntTax?>:</strong></td>
			  <td align="<?php print $tleft?>"><?php print editnumeric($alldata['ordCountryTax'],'ordCountryTax',7)?></td>
<?php			if($doedit) print '<td align="center" style="white-space:nowrap;"><input type="text" style="text-align:right" name="countryrate" id="countryrate" size="2" value="' . $countrytaxrate . '">%</td>' ?>
			</tr>
<?php		}
			if((double)$alldata['ordHSTTax']!=0.0 || ($doedit && @$canadataxsystem)){ ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxHST?>:</strong></td>
			  <td align="<?php print $tleft?>"><?php print editnumeric($alldata['ordHSTTax'],'ordHSTTax',7)?></td>
<?php			if($doedit) print '<td align="center" style="white-space:nowrap;"><input type="text" style="text-align:right" name="hstrate" id="hstrate" size="2" value="' . $hsttaxrate . '">%</td>' ?>
			</tr>
<?php		} ?>
			<tr class="cobll">
			  <td align="<?php print $tright?>" colspan="<?php print ($isinvoice?'5':'4')?>"><strong><?php print $xxGndTot?>:</strong></td>
			  <td align="<?php print $tleft?>"><span id="grandtotalspan"><?php print FormatEuroCurrency(($alldata['ordTotal']+$alldata['ordStateTax']+$alldata['ordCountryTax']+$alldata['ordHSTTax']+$alldata['ordShipping']+$alldata['ordHandling'])-$alldata['ordDiscount'])?></span></td>
<?php		if($doedit) print '<td align="center">&nbsp;</td>' ?>
			</tr>
<?php	} // ! $isprinter || $isinvoice ?>
		  </table>
		</td>
	  </tr>
	</table>
</span>
		  </td>
		</tr>
<?php	if($isprinter && ! @isset($packingslipfooter)) $packingslipfooter=$invoicefooter;
		if($isinvoice && @$invoicefooter != ''){ ?>
		<tr><td width="100%"><?php print $invoicefooter?></td></tr>
<?php	}elseif($isprinter && @$packingslipfooter != ''){ ?>
		<tr><td width="100%"><?php print $packingslipfooter?></td></tr>
<?php	}elseif($doedit){ ?>
		<tr> 
          <td align="center" width="100%">&nbsp;<br /><input type="submit" value="<?php print $yyUpdate?>" /><br />&nbsp;</td>
		</tr>
<?php	} ?>
	  </table>
<?php
		if($doedit) print '</form>';
	} // foreach($idlist as $theid)
}else{
	$sSQL = "SELECT ordID FROM orders WHERE ordStatus=1";
	if(@$_POST["act"] != "purge") $sSQL .= " AND ordStatusDate<'" . date("Y-m-d H:i:s", time()-(3*60*60*24)) . "'";
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs = mysql_fetch_assoc($result)){
		$theid = $rs["ordID"];
		$delOptions = "";
		$addcomma = "";
		$result2 = mysql_query("SELECT cartID FROM cart WHERE cartOrderID=" . $theid) or print(mysql_error());
		while($rs2 = mysql_fetch_assoc($result2)){
			$delOptions .= $addcomma . $rs2["cartID"];
			$addcomma = ",";
		}
		mysql_free_result($result2);
		if($delOptions != ""){
			$sSQL = "DELETE FROM cartoptions WHERE coCartID IN (" . $delOptions . ")";
			mysql_query($sSQL) or print(mysql_error());
		}
		mysql_query("DELETE FROM cart WHERE cartOrderID=" . $theid) or print(mysql_error());
		mysql_query("DELETE FROM orders WHERE ordID=" . $theid) or print(mysql_error());
		mysql_query("DELETE FROM giftcertificate WHERE gcOrderID=" . $theid) or print(mysql_error());
		mysql_query("DELETE FROM giftcertsapplied WHERE gcaOrdID=" . $theid) or print(mysql_error());
	}
	mysql_free_result($result);
	if(@$_POST['act']=='authorize'){
		mysql_query("UPDATE orders set ordAuthNumber='" . escape_string($_POST['authcode']!='' ? $_POST['authcode'] : $yyManAut) . "' WHERE ordID=" . $_POST['id']) or print(mysql_error());
		mysql_query('UPDATE cart SET cartCompleted=1 WHERE cartOrderID=' . $_POST['id']) or print(mysql_error());
		updateorderstatus($_POST['id'], 3);
	}elseif(@$_POST['act']=='unpending'){
		mysql_query("UPDATE orders set ordAuthStatus='' WHERE ordID=" . $_POST['id']) or print(mysql_error());
		mysql_query("UPDATE orders set ordShipType='" . escape_string($yyMoWarn) . "' WHERE ordShipType='MODWARNOPEN' AND ordID=" . $_POST['id']) or print(mysql_error());
		mysql_query("UPDATE orders set ordAuthNumber='" . escape_string($yyManAut) . "' WHERE ordAuthNumber='' AND ordID=" . $_POST['id']) or print(mysql_error());
		mysql_query("UPDATE cart SET cartCompleted=1 WHERE cartOrderID=" . $_POST['id']) or print(mysql_error());
		$sSQL="SELECT ordStatus FROM orders WHERE ordID=" . $_POST['id'];
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_assoc($result);
		$oldordstatus=$rs['ordStatus'];
		mysql_free_result($result);
		if($oldordstatus<3) updateorderstatus($_POST['id'], $oldordstatus<3 ? 3 : $oldordstatus);
	}elseif(@$_POST['act']=='editablefield'){
		setcookie('editablefield', @$_POST['id'], time()+31536000, '/', '', @$_SERVER['HTTPS']=='on');
	}elseif(@$_POST['act']=='searchfield'){
		setcookie('searchfield', @$_POST['id'], time()+31536000, '/', '', @$_SERVER['HTTPS']=='on');
	}elseif(@$_POST['act']=='status' && @$_POST['theeditablefield']!='' && @$_POST['theeditablefield']!='status'){
		$maxitems=(int)($_POST['maxitems']);
		$editfield=$_POST['theeditablefield'];
		for($index=0; $index < $maxitems; $index++){
			$iordid = trim(@$_POST['ordid' . $index]);
			mysql_query("UPDATE orders SET ord" . $_POST['theeditablefield'] . "='" . escape_string(@$_POST[$editfield . $index]) . "' WHERE ordID=" . $iordid);
		}
	}elseif(@$_POST["act"]=="status"){
		$maxitems=(int)($_POST['maxitems']);
		for($index=0; $index < $maxitems; $index++){
			updateorderstatus(trim(@$_POST['ordid' . $index]), trim(@$_POST['ordStatus' . $index]));
		}
	}
	$hasfromdate=FALSE;
	$hastodate=FALSE;
	$fromdate = trim(@$_REQUEST['fromdate']);
	$todate = trim(@$_REQUEST['todate']);
	if($fromdate != ''){
		$hasfromdate=TRUE;
		if(is_numeric($fromdate))
			$thefromdate = time()-($fromdate*60*60*24);
		else
			$thefromdate = parsedate($fromdate);
	}else
		$thefromdate = strtotime(date('Y-m-d', time()+($dateadjust*60*60)));
	if($todate != ''){
		$hastodate=TRUE;
		if(is_numeric($todate))
			$thetodate = time()-($todate*60*60*24);
		else
			$thetodate = parsedate($todate);
	}else
		$thetodate = strtotime(date('Y-m-d', time()+($dateadjust*60*60)));
	if($hasfromdate && $hastodate){
		if($thefromdate > $thetodate){
			$tmpdate = $thetodate;
			$thetodate = $thefromdate;
			$thefromdate = $tmpdate;
		}
	}
	$sSQL = 'SELECT DISTINCT ordID,ordName,ordLastName,payProvName,ordAuthNumber,ordDate,ordStatus,ordTotal-ordDiscount AS ordTot,ordTransID,ordAVS,ordCVV,ordPayProvider,ordAuthStatus,ordTrackNum,ordInvoice,ordShipType FROM orders INNER JOIN payprovider ON payprovider.payProvID=orders.ordPayProvider ';
	$origsearchtext = unstripslashes(@$_POST['searchtext']);
	$searchtext = escape_string(unstripslashes(@$_POST['searchtext']));
	$ordersearchfield = trim(@$_POST['ordersearchfield']);
	if($ordersearchfield!='')setcookie('ordersearchfield',$ordersearchfield,time()+31536000, '/', '', @$_SERVER['HTTPS']=='on');
	$ordstatus = @$_POST['ordStatus'];
	$ordstate = @$_POST['ordstate'];
	$ordcountry = @$_POST['ordcountry'];
	$payprovider = @$_POST['payprovider'];
	if($ordersearchfield=='product' && $searchtext!='') $sSQL .= 'INNER JOIN cart ON orders.ordID=cart.cartOrderID ';
	if($ordersearchfield=='ordid' && $searchtext != '' && is_numeric($searchtext)){
		$sSQL .= " WHERE ordID='" . $searchtext . "' ";
	}else{
		if(is_array($ordstatus)) $sSQL .= ' WHERE ' . (@$_POST['notstatus']=='ON' ? 'NOT ' : '') . '(ordStatus IN (' . implode(',', $ordstatus) . '))'; else $sSQL .= ' WHERE ordStatus<>1';
		if(is_array($ordstate)) $sSQL .= ' AND ' . (@$_POST['notsearchfield']=='ON' ? 'NOT ' : '') . "(ordState IN ('" . implode("','", $ordstate) . "'))";
		if(is_array($ordcountry)) $sSQL .= ' AND ' . (@$_POST['notsearchfield']=='ON' ? 'NOT ' : '') . "(ordCountry IN ('" . implode("','", $ordcountry) . "'))";
		if(is_array($payprovider)) $sSQL .= ' AND ' . (@$_POST['notsearchfield']=='ON' ? 'NOT ' : '') . '(ordPayprovider IN (' . implode(',', $payprovider) . '))';
		if($hasfromdate)
			$sSQL .= " AND ordDate BETWEEN '" . date('Y-m-d', $thefromdate) . "' AND '" . date('Y-m-d', ($hastodate ? $thetodate+96400 : $thefromdate+96400)) . "'";
		elseif($searchtext=='' && $ordstatus=='' && $ordstate=='' && $ordcountry=='' && $payprovider=='')
			$sSQL .= " AND ordDate BETWEEN '" . date('Y-m-d', time()+($dateadjust*60*60)) . "' AND '" . date('Y-m-d', time()+($dateadjust*60*60)+96400) . "'";
		if($searchtext != ''){
			if($ordersearchfield=='ordid' || $ordersearchfield=='name'){
				if($usefirstlastname){
					splitfirstlastname($searchtext,$firstname,$lastname);
					if($lastname=='')
						$namesql = "(ordName LIKE '%".$firstname."%' OR ordLastName LIKE '%".$firstname."%')";
					else
						$namesql = "(ordName LIKE '%".$firstname."%' AND ordLastName LIKE '%".$lastname."%')";
				}else
					$namesql = "ordName LIKE '%".$searchtext."%'";
			}
			if($ordersearchfield=='ordid')
				$sSQL .= " AND (ordEmail LIKE '%" . $searchtext . "%' OR ".$namesql.')';
			elseif($ordersearchfield=='email')
				$sSQL .= " AND ordEmail LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='authcode')
				$sSQL .= " AND (ordAuthNumber LIKE '%" . $searchtext . "%' OR ordTransID LIKE '%" . $searchtext . "%')";
			elseif($ordersearchfield=='name')
				$sSQL .= " AND " . $namesql;
			elseif($ordersearchfield=='product')
				$sSQL .= " AND (cartProdID LIKE '%" . $searchtext . "%' OR cartProdName LIKE '%" . $searchtext . "%')";
			elseif($ordersearchfield=='address')
				$sSQL .= " AND (ordAddress LIKE '%" . $searchtext . "%' OR ordAddress2 LIKE '%" . $searchtext . "%' OR ordCity LIKE '%" . $searchtext . "%' OR ordState LIKE '%" . $searchtext . "%')";
			elseif($ordersearchfield=='phone')
				$sSQL .= " AND ordPhone LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='zip')
				$sSQL .= " AND ordZip LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='invoice')
				$sSQL .= " AND ordInvoice LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='affiliate')
				$sSQL .= " AND ordAffiliate='" . $searchtext . "'";
			elseif($ordersearchfield=='extra1')
				$sSQL .= " AND ordExtra1 LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='extra2')
				$sSQL .= " AND ordExtra2 LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='checkout1')
				$sSQL .= " AND ordCheckoutExtra1 LIKE '%" . $searchtext . "%'";
			elseif($ordersearchfield=='checkout2')
				$sSQL .= " AND ordCheckoutExtra2 LIKE '%" . $searchtext . "%'";
		}
	}
	$sSQL .= " ORDER BY ordID";
	$alldata = mysql_query($sSQL) or print(mysql_error());
	$hasdeleted=false;
	$sSQL = "SELECT COUNT(*) AS NumDeleted FROM orders WHERE ordStatus=1";
	$result = mysql_query($sSQL) or print(mysql_error());
	$rs = mysql_fetch_assoc($result);
	if($rs["NumDeleted"] > 0) $hasdeleted=true;
	mysql_free_result($result);
?>
<script language="javascript" type="text/javascript" src="popcalendar.js">
</script>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function delrec(id){
cmsg = "<?php print $yyConDel?>\n";
if (confirm(cmsg)){
	document.psearchform.id.value = id;
	document.psearchform.act.value = "delete";
	document.psearchform.submit();
}
}
function authrec(id, currauth){
var aucode;
cmsg = "<?php print $yyEntAuth?>";
if(currauth=='')currauth='<?php print $yyManAut?>';
if((aucode=prompt(cmsg,currauth))!=null){
	document.psearchform.id.value = id;
	document.psearchform.act.value = "authorize";
	document.psearchform.authcode.value = aucode;
	document.psearchform.submit();
}
}
function unpendrec(id){
if(confirm("<?php print $yyWarni?>This will not make any changes at your payment processor!\n\nRemove pending status of this order?")){
	document.psearchform.id.value = id;
	document.psearchform.act.value = "unpending";
	document.psearchform.submit();
}
}
function unmodwarn(id){
<?php	$yyModWar='The customer changed cart contents after creating this order.\\nBefore authorizing this order check order totals carefully.\\n\\nPlease click "OK" to edit the order and check stock levels as stock has not yet been subtracted for this order.';
		if($stockManage!=0){ ?>
if(confirm("<?php print $yyWarni?>\n\n<?php print str_replace('"','\\"',$yyModWar)?>")){
	document.location='adminorders.php?doedit=true&id='+id;
}
<?php	}else{ ?>
if(confirm("<?php print $yyWarni?>\n\n<?php print str_replace('"','\\"',$yyModWar)?>")){
	document.psearchform.id.value = id;
	document.psearchform.act.value = "unpending";
	document.psearchform.submit();
}
<?php	} ?>
}
function checkcontrol(tt,evt){
<?php if(strstr(@$_SERVER['HTTP_USER_AGENT'], 'Gecko')){ ?>
theevnt = evt;
return;
<?php }else{ ?>
theevnt=window.event;
<?php } ?>
if(theevnt.ctrlKey){
	maxitems=document.psearchform.maxitems.value;
	for(index=0;index<maxitems;index++){
		isdisabled = eval('document.psearchform.ordStatus'+index+'.disabled');
		if(! isdisabled){
			if(eval('document.psearchform.ordStatus'+index+'.length') > tt.selectedIndex){
				eval('document.psearchform.ordStatus'+index+'.selectedIndex='+tt.selectedIndex);
				eval('document.psearchform.ordStatus'+index+'.options['+tt.selectedIndex+'].selected=true');
			}
		}
	}
}
}
function checkprinter(tt,evt){
<?php if(strstr(@$_SERVER['HTTP_USER_AGENT'], 'Gecko')){ ?>
if(evt.ctrlKey || evt.altKey || document.psearchform.ctrlmod[document.psearchform.ctrlmod.selectedIndex].value=="1"){
	tt.href += "&printer=true";
	window.location.href = tt.href;
}else if(document.psearchform.ctrlmod[document.psearchform.ctrlmod.selectedIndex].value=="3"){
	tt.href += "&invoice=true";
	window.location.href = tt.href;
}else if(document.psearchform.ctrlmod[document.psearchform.ctrlmod.selectedIndex].value=="2"){
	tt.href += "&doedit=true";
	window.location.href = tt.href;
}
<?php }else{ ?>
theevnt=window.event;
if(theevnt.ctrlKey || document.psearchform.ctrlmod[document.psearchform.ctrlmod.selectedIndex].value=="1")tt.href += "&printer=true";
if(document.psearchform.ctrlmod[document.psearchform.ctrlmod.selectedIndex].value=="3")tt.href += "&invoice=true";
if(document.psearchform.ctrlmod[document.psearchform.ctrlmod.selectedIndex].value=="2")tt.href += "&doedit=true";
<?php } ?>
return(true);
}
function setdumpformat(){
formatindex = document.forms.psearchform.filedump[document.forms.psearchform.filedump.selectedIndex].value;
if(formatindex==1)
	document.psearchform.act.value='dumporders';
else if(formatindex==2)
	document.psearchform.act.value='dumpdetails';
else if(formatindex==3)
	document.psearchform.act.value='quickbooks';
else if(formatindex==4)
	document.psearchform.act.value='ouresolutionsxmldump';
document.psearchform.action='dumporders.php';
document.psearchform.submit();
}
function docheckall(){
	allcbs = document.getElementsByName('ids[]');
	mainidchecked = document.getElementById('xdocheckall').checked;
	for(i=0;i<allcbs.length;i++){
		allcbs[i].checked=mainidchecked;
	}
	return(true);
}
function checkchecked(printorinvoice){
	allcbs = document.getElementsByName('ids[]');
	var onechecked=false;
	for(i=0;i<allcbs.length;i++){
		if(allcbs[i].checked)onechecked=true;
	}
	if(onechecked){
		document.forms.psearchform.action='adminorders.php?'+printorinvoice+'=true&id=multi';
		document.forms.psearchform.submit();
	}else{
		alert("<?php print $yyNoSelO?>");
	}
}
function changeselectfield(whichfield){
	var editablefield = document.getElementById(whichfield);
	var editfieldval = editablefield[editablefield.selectedIndex].value;
	document.psearchform.reset();
	document.psearchform.action='adminorders.php';
	document.psearchform.id.value=editfieldval;
	document.psearchform.act.value=whichfield;
	document.psearchform.submit();
}
/* ]]> */
</script>
<?php	$themask = 'yyyy-mm-dd';
		if($admindateformat==1)
			$themask='mm/dd/yyyy';
		elseif($admindateformat==2)
			$themask='dd/mm/yyyy';
		if(! $success) print '<p><span style="color:#FF0000">' . $errmsg . '</span></p>';
		if(@$_POST['act']=='editablefield') $editablefield = $_POST['id']; else $editablefield = @$_COOKIE['editablefield'];
		if(@$_POST['act']=='searchfield') $searchfield = $_POST['id']; else $searchfield = @$_COOKIE['searchfield'];
		if(@$_POST['ordersearchfield']!='') $ordersearchfield = $_POST['ordersearchfield']; else $ordersearchfield = @$_COOKIE['ordersearchfield'];
		$_SESSION['fromdate']=$fromdate;
		$_SESSION['todate']=$todate;
		$_SESSION['notstatus']=@$_POST['notstatus'];
		$_SESSION['notsearchfield']=@$_POST['notsearchfield'];
		$_SESSION['searchtext']=$origsearchtext;
		$_SESSION['ordStatus']=@$_POST['ordStatus'];
		$_SESSION['ordstate']=@$_POST['ordstate'];
		$_SESSION['ordcountry']=@$_POST['ordcountry'];
		$_SESSION['payprovider']=@$_POST['payprovider'];
		?><form method="post" action="adminorders.php" name="psearchform">
            <input type="hidden" name="act" value="" />
			<input type="hidden" name="id" value="" />
			<input type="hidden" name="authcode" value="" />
            <input type="hidden" name="theeditablefield" value="<?php print $editablefield?>" />
			<input type="hidden" name="thesearchfield" value="<?php print $searchfield?>" />
            <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr><td class="cobhl" align="center" colspan="4"><strong><?php print $yyPowSea?></strong></td></tr>
			  <tr> 
                <td class="cobhl" align="right" width="25%" style="white-space: nowrap"><strong><?php print $yyOrdFro?>:</strong></td>
				<td class="cobll" align="left" width="25%" style="white-space: nowrap"><input type="text" size="14" name="fromdate" value="<?php print $fromdate; ?>" /> <input type="button" onclick="popUpCalendar(this, document.forms.psearchform.fromdate, '<?php print $themask?>', 0)" value='DP' /> <input type="button" onclick="document.forms.psearchform.fromdate.value='<?php print date($admindatestr, time() + ($dateadjust*60*60))?>'" value="<?php print $yyToday?>" /></td>
				<td class="cobhl" align="right" width="16%" style="white-space: nowrap"><strong><?php print $yyOrdTil?>:</strong></td>
				<td class="cobll" align="left" width="34%" style="white-space: nowrap"><input type="text" size="14" name="todate" value="<?php print $todate; ?>" /> <input type="button" onclick="popUpCalendar(this, document.forms.psearchform.todate, '<?php print $themask?>', -205)" value='DP' /> <input type="button" onclick="document.forms.psearchform.todate.value='<?php print date($admindatestr, time() + ($dateadjust*60*60))?>'" value="<?php print $yyToday?>" /></td>
			  </tr>
			  <tr>
				<td class="cobhl" align="center" style="white-space: nowrap"><strong><?php print $yyOrdSta?></strong>&nbsp;&nbsp;<input type="checkbox" name="notstatus" value="ON" <?php if(@$_POST['notstatus']=='ON') print "checked "?>/><strong>...<?php print $yyNot?></strong></td>
				<td class="cobhl" align="center" style="white-space: nowrap"><select name="searchfield" id="searchfield" size="1" onchange="changeselectfield('searchfield')">
					<option value="state" <?php if($searchfield=='state') print 'selected="selected"'?>><?php print $yyState?></option>
					<option value="country" <?php if($searchfield=='country') print 'selected="selected"'?>><?php print $yyCountry?></option>
					<option value="payprovider" <?php if($searchfield=='payprovider' || $searchfield=='') print 'selected="selected"'?>><?php print $yyPayMet?></option>
					</select>&nbsp;&nbsp;<input type="checkbox" name="notsearchfield" value="ON" <?php if(@$_POST['notsearchfield']=='ON') print "checked "?>/><strong>...<?php print $yyNot?></strong></td>
				<td class="cobhl" align="right" style="white-space: nowrap"><strong><?php print $yySeaTxt?>:</strong></td>
				<td class="cobll" align="left" style="white-space: nowrap"><input type="text" size="24" name="searchtext" value="<?php print $origsearchtext?>" />&nbsp;<select name="ordersearchfield" size="1">
					<option value="ordid" <?php if($ordersearchfield=='ordid') print 'selected="selected"'?>><?php print $xxOrdId?></option>
					<option value="email" <?php if($ordersearchfield=='email') print 'selected="selected"'?>><?php print $yyEmail?></option>
					<option value="authcode" <?php if($ordersearchfield=='authcode') print 'selected="selected"'?>><?php print $yyAutCod?></option>
					<option value="name" <?php if($ordersearchfield=='name') print 'selected="selected"'?>><?php print $yyName?></option>
					<option value="product" <?php if($ordersearchfield=='product') print 'selected="selected"'?>><?php print $yyPrName?></option>
					<option value="address" <?php if($ordersearchfield=='address') print 'selected="selected"'?>><?php print $yyAddress?></option>
					<option value="zip" <?php if($ordersearchfield=='zip') print 'selected="selected"'?>><?php print $yyZip?></option>
					<option value="phone" <?php if($ordersearchfield=='phone') print 'selected="selected"'?>><?php print $yyTelep?></option>
					<option value="invoice" <?php if($ordersearchfield=='invoice') print 'selected="selected"'?>><?php print $yyInvNum?></option>
					<option value="affiliate" <?php if($ordersearchfield=='affiliate') print 'selected="selected"'?>><?php print $yyAffili?></option>
<?php				if(@$extraorderfield1!='') print '<option value="extra1" ' . ($ordersearchfield=='extra1' ? 'selected="selected"' : '') . '>' . htmlspecials(substr(strip_tags($extraorderfield1), 0, 16)) . '</option>';
					if(@$extraorderfield2!='') print '<option value="extra2" ' . ($ordersearchfield=='extra2' ? 'selected="selected"' : '') . '>' . htmlspecials(substr(strip_tags($extraorderfield2), 0, 16)) . '</option>';
					if(@$extracheckoutfield1!='') print '<option value="checkout1" ' . ($ordersearchfield=='checkout1' ? 'selected="selected"' : '') . '>' . htmlspecials(substr(strip_tags($extracheckoutfield1), 0, 16)) . '</option>';
					if(@$extracheckoutfield2!='') print '<option value="checkout2" ' . ($ordersearchfield=='checkout2' ? 'selected="selected"' : '') . '>' . htmlspecials(substr(strip_tags($extracheckoutfield2), 0, 16)) . '</option>';
?>					</select>
				</td>
			  </tr>
			  <tr>
				<td class="cobll" align="center">
		<select name="ordStatus[]" size="5" multiple="multiple"><?php
		$ordstatus="";
		$addcomma = "";
		if(is_array(@$_POST['ordStatus'])){
			foreach($_POST['ordStatus'] as $objValue){
				if(is_array($objValue))$objValue=$objValue[0];
				$ordstatus .= $addcomma . $objValue;
				$addcomma = ",";
			}
		}else
			$ordstatus = trim(@$_POST['ordStatus']);
		$ordstatusarr = explode(",", $ordstatus);
		for($index=0; $index < $numstatus; $index++){
			print '<option value="' . $allstatus[$index]["statID"] . '"';
			if(is_array($ordstatusarr)){
				foreach($ordstatusarr as $objValue)
					if($objValue==$allstatus[$index]["statID"]) print ' selected="selected"';
			}
			print ">" . $allstatus[$index]["statPrivate"] . "</option>";
		} ?></select></td>
				<td class="cobll" align="center">
<?php
	if(@$searchfield=='state'){ ?>
		<select name="ordstate[]" size="5" multiple="multiple"><?php
		$ordstate = @$_POST['ordstate'];
		$sSQL = "SELECT stateID,stateName,stateAbbrev FROM states WHERE stateEnabled=1 ORDER BY stateName";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_assoc($result)){
			print '<option value="' . htmlspecials(@$usestateabbrev==TRUE?$rs['stateAbbrev']:$rs['stateName']) . '"';
			if(is_array($ordstate)){
				foreach($ordstate as $objValue){
					if($objValue==(@$usestateabbrev==TRUE?$rs['stateAbbrev']:$rs['stateName'])) print ' selected="selected"';
				}
			}
			print '>' . $rs['stateName'] . "</option>\n";
		}
		mysql_free_result($result); ?></select><?php
	}elseif(@$searchfield=='country'){ ?>
		<select name="ordcountry[]" size="5" multiple="multiple"><?php
		$ordcountry = @$_POST['ordcountry'];
		$sSQL = "SELECT countryID,countryName FROM countries WHERE countryEnabled=1 ORDER BY countryOrder DESC, countryName";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_assoc($result)){
			print '<option value="' . htmlspecials($rs['countryName']) . '"';
			if(is_array($ordcountry)){
				foreach($ordcountry as $objValue){
					if($objValue==$rs['countryName']) print ' selected="selected"';
				}
			}
			print '>' . $rs['countryName'] . "</option>\n";
		}
		mysql_free_result($result); ?></select><?php
	}else{ ?>
		<select name="payprovider[]" size="5" multiple="multiple"><?php
		$payprovider = @$_POST['payprovider'];
		$sSQL = "SELECT payProvID,payProvName FROM payprovider WHERE payProvEnabled=1 ORDER BY payProvOrder";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_assoc($result)){
			print '<option value="' . $rs['payProvID'] . '"';
			if(is_array($payprovider)){
				foreach($payprovider as $objValue){
					if($objValue==$rs['payProvID']) print ' selected="selected"';
				}
			}
			print '>' . $rs['payProvName'] . '</option>';
		}
		mysql_free_result($result); ?></select>
<?php
	} ?>
				</td>
				<td class="cobhl" colspan="2" align="center">
				<select name="filedump" size="1">
					<option value="1"><?php print $yyDmpOrd?></option>
					<option value="2"><?php print $yyDmpDet?></option>
<?php
	if(@$ouresolutionsxml != '') print '<option value="4">OurESolutions XML format</option>'; ?>
					</select> <input type="button" value="<?php print $yyGo?>" onclick="setdumpformat()" /> <input type="button" value="<?php print $yyNewOrd?>" onclick="document.forms.psearchform.action='adminorders.php?id=new';document.forms.psearchform.submit();" /><br /><br />
				  <input type="submit" value="<?php print $yySearch?>" onclick="document.forms.psearchform.action='adminorders.php';" /> <input type="button" value="Stats" onclick="document.forms.psearchform.action='adminstats.php';document.forms.psearchform.submit();" />
				  <input type="button" value="<?php print $yyPakSps?>" onclick="checkchecked('printer')" /> <input type="button" value="<?php print $yyInvces?>" onclick="checkchecked('invoice')" />
				</td>
			  </tr>
			</table>
		<img src="adminimages/clearpixel.gif" width="10" height="2" alt="" /><br/>
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr>
				<td class="cobhl" align="center" width="1%"><input type="checkbox" id="xdocheckall" value="1" onclick="docheckall()" /></td>
                <td class="cobhl" align="center"><strong><?php print $yyOrdId?></strong></td>
				<td class="cobhl" align="left"><strong><?php print $yyName?></strong></td>
				<td class="cobhl" align="center"><strong><?php print $yyMethod?></strong></td>
				<td class="cobhl" align="center" width="1%"><strong>AVS</strong></td>
				<td class="cobhl" align="center" width="1%"><strong>CVV</strong></td>
				<td class="cobhl" align="center"><strong><?php print $yyAutCod?></strong></td>
				<td class="cobhl" align="center"><strong><?php print $yyDate?></strong></td>
				<td class="cobhl" align="center"><select name="editablefield" id="editablefield" size="1" onchange="changeselectfield('editablefield')">
					<option value="status"><?php print $yyStatus?></option>
					<option value="tracknum" <?php if($editablefield=='tracknum') print 'selected="selected"'?>><?php print $yyTraNum?></option>
					<option value="invoice" <?php if($editablefield=='invoice') print 'selected="selected"'?>><?php print $yyInvNum?></option>
				</select></td>
			  </tr>
<?php
	if(mysql_num_rows($alldata) > 0){
		$rowcounter=0;
		$ordTot=0;
		while($rs = mysql_fetch_assoc($alldata)){
			if($rs['ordStatus']>=3) $ordTot += $rs['ordTot'];
			if(trim($rs['ordAuthNumber'])==''){
				$startfont='<span style="color:#FF0000">';
				$endfont='</span>';
			}else{
				$startfont='';
				$endfont='';
			}
			if(@$bgcolor=='cobll') $bgcolor='cobhl'; else $bgcolor='cobll';
				if($rs['ordAuthStatus']=='MODWARNOPEN' || $rs['ordShipType']=='MODWARNOPEN') $bgcolor='cobwarn';
?>			  <tr class="<?php print $bgcolor?>">
				<td align="center"><input type="checkbox" name="ids[]" value="<?php print $rs['ordID']?>" /></td>
				<td align="center"><a onclick="return(checkprinter(this,event));" href="adminorders.php?id=<?php print $rs['ordID']?>"><?php print '<strong>' . $startfont . $rs['ordID'] . $endfont . '</strong>'?></a></td>
				<td><a onclick="return(checkprinter(this,event));" href="adminorders.php?id=<?php print $rs['ordID']?>"><?php print $startfont . htmlspecials(trim($rs['ordName'].' '.$rs['ordLastName'])) . $endfont?></a></td>
				<td align="center"><?php print $startfont . htmlspecials($rs['payProvName']) . ($rs['payProvName']=='PayPal' && trim($rs['ordTransID']) != '' ? ' CC' : '') . $endfont?></td>
				<td align="center" width="1%"><?php if(trim($rs['ordAVS']) != '') print htmlspecials($rs['ordAVS']); else print '&nbsp;' ?></td>
				<td align="center" width="1%"><?php if(trim($rs['ordCVV']) != '') print htmlspecials($rs['ordCVV']); else print '&nbsp;' ?></td>
				<td align="center"><?php
					if($rs['ordAuthStatus']=='MODWARNOPEN' || $rs['ordShipType']=='MODWARNOPEN'){
						$isauthorized=FALSE;
						print '<input type="button" value="' . $yyMoWarn . '" onclick="unmodwarn(\'' . $rs['ordID'] . '\')" /><br />';
					}else{
						if(trim($rs['ordAuthStatus']) != '') print '<input type="button" value="' . $rs['ordAuthStatus'] . '" onclick="unpendrec(\'' . $rs['ordID'] . '\')" /><br />';
						if(trim($rs['ordAuthNumber'])==''){
							$isauthorized=FALSE;
							print '<input type="button" name="auth" value="' . $yyAuthor . '" onclick="authrec(\'' . $rs['ordID'] . '\',\'\')" />';
						}else{
							print '<a href="#" title="' . FormatEuroCurrency($rs['ordTot']) . '" onclick="authrec(\'' . $rs['ordID'] . '\',\''.$rs['ordAuthNumber'].'\');return(false);">' . $startfont . $rs['ordAuthNumber'] . $endfont . '</a>';
							$isauthorized=TRUE;
						}
					}
				?></td>
				<td align="center"><span style="font-size:10px"><?php print $startfont . date($admindatestr . "\<\\b\\r\ />H:i:s", strtotime($rs["ordDate"])) . $endfont?></span></td>
				<td align="center"><input type="hidden" name="ordid<?php print $rowcounter?>" value="<?php print $rs['ordID']?>" />
<?php		if($editablefield=='tracknum')
				print '<input type="text" name="tracknum'.$rowcounter.'" size="24" value="' . $rs['ordTrackNum'] . '" />';
			elseif($editablefield=='invoice')
				print '<input type="text" name="invoice'.$rowcounter.'" size="24" value="' . $rs['ordInvoice'] . '" />';
			else{ ?>
					<select name="ordStatus<?php print $rowcounter?>" size="1" onchange="checkcontrol(this,event)"<?php if($rs['ordPayProvider']==20) print ' disabled'?>><?php
						$gotitem=FALSE;
						for($index=0; $index<$numstatus; $index++){
							if(! $isauthorized && $allstatus[$index]['statID']>2) break;
							if(! ($rs['ordStatus'] != 2 && $allstatus[$index]['statID']==2)){
								print '<option value="' . $allstatus[$index]['statID'] . '"';
								if($rs['ordStatus']==$allstatus[$index]['statID']){
									print ' selected="selected"';
									$gotitem=TRUE;
								}
								print '>' . $allstatus[$index]['statPrivate'] . '</option>';
							}
						}
						if(! $gotitem) print '<option value="'.$allstatus[$index]['statID'].'" selected="selected">' . $yyUndef . '</option>' ?></select>
<?php		} ?>
				</td>
			  </tr>
<?php		$rowcounter++;
			if($rowcounter>=250){
				print '<tr class="cobll"><td colspan="9" align="center">&nbsp;<br /><strong>Limit of ' . $rowcounter . ' orders reached. Please refine your search.</strong><br />&nbsp;</td></tr>';
				break;
			}
		} ?>
			  <tr class="cobll">
				<td>&nbsp;</td>
				<td align="center"><?php print FormatEuroCurrency($ordTot)?></td>
				<td align="center"><?php if($hasdeleted){ ?><input type="submit" value="<?php print $yyPurDel?>" onclick="document.psearchform.action='adminorders.php';document.psearchform.act.value='purge';" /><?php }else print '&nbsp;'; ?></td>
				<td align="center" colspan="5"><select name="ctrlmod" size="1"><option value="0"><?php print $yyVieDet?></option><option value="1"><?php print $yyPPSlip?></option><option value="3"><?php print $yyPPInv?></option><option value="2" <?php if(@$_POST['ctrlmod']=='2') print 'selected="selected"';?>><?php print $yyEdOrd?></option></select>
				&nbsp;&nbsp;&nbsp;<input type="checkbox" name="emailstat" value="1" <?php if(@$_POST["emailstat"]=="1" || @$alwaysemailstatus==TRUE) print "checked"?>/> <?php print $yyEStat?></td>
				<td align="center"><input type="hidden" name="maxitems" value="<?php print $rowcounter?>" /><input type="submit" value="<?php print $yyUpdate?>" onclick="document.forms.psearchform.action='adminorders.php';document.psearchform.act.value='status';" /> <input type="reset" value="<?php print $yyReset?>" /></td>
			  </tr>
<?php
	}else{
?>
			  <tr class="cobll"> 
                <td colspan="9" width="100%" align="center">
					<p>&nbsp;</p>
					<p><?php print $yyNoMat1;?></p>
					<p>&nbsp;</p>
				</td>
			  </tr>
			  <?php if($hasdeleted){ ?>
			  <tr class="cobll">
				<td colspan="2">&nbsp;</td>
				<td width="20%" align="center"><input type="submit" value="<?php print $yyPurDel?>" onclick="document.psearchform.action='adminorders.php';document.psearchform.act.value='purge';" /></td>
				<td colspan="6">&nbsp;</td>
			  </tr>
			  <?php } ?>
<?php
	}
	mysql_free_result($alldata); ?>
			</table>
			<table width="100%" border="0" cellspacing="1" cellpadding="2">
			  <tr> 
                <td colspan="4" width="100%" align="center">
				  <p><br />
					<a href="adminorders.php?fromdate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thefromdate)-1,date("d",$thefromdate),date("Y",$thefromdate)))?>&amp;todate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thetodate)-1,date("d",$thetodate),date("Y",$thetodate)))?>"><strong>- <?php print $yyMonth?></strong></a> | 
					<a href="adminorders.php?fromdate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thefromdate),date("d",$thefromdate)-7,date("Y",$thefromdate)))?>&amp;todate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thetodate),date("d",$thetodate)-7,date("Y",$thetodate)))?>"><strong>- <?php print $yyWeek?></strong></a> | 
					<a href="adminorders.php?fromdate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thefromdate),date("d",$thefromdate)-1,date("Y",$thefromdate)))?>&amp;todate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thetodate),date("d",$thetodate)-1,date("Y",$thetodate)))?>"><strong>- <?php print $yyDay?></strong></a> | 
					<a href="adminorders.php"><strong><?php print $yyToday?></strong></a> | 
					<a href="adminorders.php?fromdate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thefromdate),date("d",$thefromdate)+1,date("Y",$thefromdate)))?>&amp;todate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thetodate),date("d",$thetodate)+1,date("Y",$thetodate)))?>"><strong><?php print $yyDay?> +</strong></a> | 
					<a href="adminorders.php?fromdate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thefromdate),date("d",$thefromdate)+7,date("Y",$thefromdate)))?>&amp;todate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thetodate),date("d",$thetodate)+7,date("Y",$thetodate)))?>"><strong><?php print $yyWeek?> +</strong></a> | 
					<a href="adminorders.php?fromdate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thefromdate)+1,date("d",$thefromdate),date("Y",$thefromdate)))?>&amp;todate=<?php print date($admindatestr,mktime(0,0,0,date("m",$thetodate),date("d",$thetodate)+1,date("Y",$thetodate)))?>"><strong><?php print $yyMonth?> +</strong></a>
				  </p>
				</td>
			  </tr>
			</table>
		  </form>
<?php
}
?>