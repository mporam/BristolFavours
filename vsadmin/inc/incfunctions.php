<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
$incfunctionsdefined=TRUE;
$defimagejs='';
@set_magic_quotes_runtime(0);
$magicq = (get_magic_quotes_gpc()==1);
if(@$giftcertificateid=='') $giftcertificateid='giftcertificate';
if(@$donationid=='') $donationid='donation';
if(@$emailencoding=='') $emailencoding='iso-8859-1';
if(@$adminencoding=='') $adminencoding='iso-8859-1';
if(@$_SESSION['languageid'] != '') $languageid=$_SESSION['languageid'];
if(@$emailcr=='')$emailcr="\r\n";
if(@$htmlemails==TRUE) $emlNl = '<br />'; else $emlNl=$emailcr;
if(@$nomarkup==TRUE){
	$sstrong='';
	$estrong='';
}else{
	$sstrong='<strong>';
	$estrong='</strong>';
}
if(@$customeraccounturl=='') $customeraccounturl='clientlogin.php';
if(@$fedextestmode) $fedexurl='https://wsbeta.fedex.com:443/web-services'; else $fedexurl='https://ws.fedex.com:443/web-services';
if(@$loyaltypointvalue=='') $loyaltypointvalue=0.0001;
$redasterix='<span style="color:#FF0000">*</span>';
$fedexcopyright='FedEx service marks are owned by Federal Express Corporation and are used by permission.';
if(@$righttoleft==TRUE){ $tright='left'; $tleft='right'; }else{ $tright='right'; $tleft='left'; }
function getadminsettings(){
	global $alreadygotadmin,$splitUSZones,$adminLocale,$countryCurrency,$countryNumCurrency,$orcurrencyisosymbol,$useEuro,$storeurl,$stockManage,$useStockManagement,$adminProdsPerPage,$countryTax,$countryTaxRate,$delccafter,$handling,$handlingchargepercent,$adminCanPostUser,$packtogether,$origZip,$shipType,$adminIntShipping,$origCountry,$origCountryCode,$origCountryID,$uspsUser,$smartPostHub,$uspsPw,$upsUser,$upsPw,$upsAccess,$upsAccount,$upsnegdrates,$fedexaccount,$fedexmeter,$fedexuserkey,$fedexuserpwd,$adminUnits,$emailAddr,$sendEmail,$adminTweaks,$adminlanguages,$adminlangsettings,$currRate1,$currSymbol1,$currRate2,$currSymbol2,$currRate3,$currSymbol3,$currConvUser,$currConvPw,$currLastUpdate,$adminSecret,$cardinalprocessor,$cardinalmerchant,$cardinalpwd,$catalogroot,$adminAltRates,$prodfilter,$prodfiltertext,$dosortby,$sortoptions;
	if(! @$alreadygotadmin){
		$sSQL = "SELECT adminEmail,adminEmailConfirm,adminProdsPerPage,adminStoreURL,adminHandling,adminHandlingPercent,adminPacking,adminDelCC,adminUSZones,adminStockManage,adminShipping,adminIntShipping,adminCanPostUser,adminZipCode,adminUnits,adminUSPSUser,smartPostHub,adminUSPSpw,adminUPSUser,adminUPSpw,adminUPSAccess,adminUPSAccount,adminUPSNegotiated,FedexAccountNo,FedexMeter,FedexUserKey,FedexUserPwd,adminlanguages,adminlangsettings,currRate1,currSymbol1,currRate2,currSymbol2,currRate3,currSymbol3,currConvUser,currConvPw,currLastUpdate,adminSecret,countryLCID,countryCurrency,countryNumCurrency,countryName,countryCode,countryID,countryTax,cardinalProcessor,cardinalMerchant,cardinalPwd,catalogRoot,adminAltRates,prodFilter,prodFilterText,prodFilterText2,prodFilterText3,sortOrder,sortOptions FROM admin LEFT JOIN countries ON admin.adminCountry=countries.countryID WHERE adminID=1";
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_array($result);
		$splitUSZones = ((int)$rs['adminUSZones']==1);
		$adminLocale = $rs['countryLCID'];
		$countryCurrency = $rs['countryCurrency'];
		$countryNumCurrency = $rs['countryNumCurrency'];
		if(@$orcurrencyisosymbol != '') $countryCurrency=$orcurrencyisosymbol;
		$useEuro = ($countryCurrency=='EUR');
		$storeurl = $rs['adminStoreURL'];
		$stockManage = (int)$rs['adminStockManage'];
		$useStockManagement = ($stockManage != 0);
		$adminProdsPerPage = $rs['adminProdsPerPage'];
		$countryTax=(double)$rs['countryTax'];
		$countryTaxRate=(double)$rs['countryTax'];
		$delccafter = (int)$rs['adminDelCC'];
		$handling=(double)$rs['adminHandling'];
		$handlingchargepercent=(double)$rs['adminHandlingPercent'];
		$adminCanPostUser=trim($rs['adminCanPostUser']);
		$packtogether = ((int)$rs['adminPacking']==1);
		$origZip = $rs['adminZipCode'];
		$shipType=(int)$rs['adminShipping'];
		$adminIntShipping=(int)$rs['adminIntShipping'];
		$origCountry = $rs['countryName'];
		$origCountryCode = $rs['countryCode'];
		$origCountryID = $rs['countryID'];
		$uspsUser = $rs['adminUSPSUser'];
		$uspsPw = $rs['adminUSPSpw'];
		$upsUser = upsdecode($rs['adminUPSUser'], '');
		$upsPw = upsdecode($rs['adminUPSpw'], '');
		$smartPostHub = $rs['smartPostHub'];
		$upsAccess = $rs['adminUPSAccess'];
		$upsAccount = $rs['adminUPSAccount'];
		$upsnegdrates = $rs['adminUPSNegotiated'];
		$fedexaccount = $rs['FedexAccountNo'];
		$fedexmeter = $rs['FedexMeter'];
		$fedexuserkey = $rs['FedexUserKey'];
		$fedexuserpwd = $rs['FedexUserPwd'];
		$adminUnits = (int)$rs['adminUnits'];
		$emailAddr = $rs['adminEmail'];
		$sendEmail = ((int)$rs['adminEmailConfirm']==1);
		$adminTweaks = 0;
		$adminlanguages = (int)$rs['adminlanguages'];
		$adminlangsettings = (int)$rs['adminlangsettings'];
		$currRate1=(double)$rs['currRate1'];
		$currSymbol1=trim($rs['currSymbol1']);
		$currRate2=(double)$rs['currRate2'];
		$currSymbol2=trim($rs['currSymbol2']);
		$currRate3=(double)$rs['currRate3'];
		$currSymbol3=trim($rs['currSymbol3']);
		$currConvUser=$rs['currConvUser'];
		$currConvPw=$rs['currConvPw'];
		$currLastUpdate=$rs['currLastUpdate'];
		$adminSecret=$rs['adminSecret'];
		$cardinalprocessor=$rs['cardinalProcessor'];
		$cardinalmerchant=$rs['cardinalMerchant'];
		$cardinalpwd=$rs['cardinalPwd'];
		$catalogroot=$rs['catalogRoot'];
		$adminAltRates=$rs['adminAltRates'];
		$dosortby = $rs['sortOrder'];
		$sortoptions = $rs['sortOptions'];
		$prodfilter = $rs['prodFilter'];
		$prodfiltertext = $rs[getlangid('prodFilterText',262144)];
		mysql_free_result($result);
	}
	// Overrides
	global $orstoreurl,$oremailaddr;
	if(@$orstoreurl != '') $storeurl=$orstoreurl;
	if((substr(strtolower($storeurl),0,7) != 'http://') && (substr(strtolower($storeurl),0,8) != 'https://'))
		$storeurl = 'http://' . $storeurl;
	if(substr($storeurl,-1) != '/') $storeurl .= '/';
	if(@$oremailaddr != '') $emailAddr=$oremailaddr;
	return(TRUE);
}
function replaceaccents($surl){
	return(str_replace(array('à','â','ç','è','é','ê','ë','î','ï','ò','ô','ö','ù','û','ü','ñ'),array('a','a','c','e','e','e','e','i','i','o','o','o','u','u','u','n'),$surl));
}
function cleanforurl($surl){
global $urlfillerchar;
if(! @isset($urlfillerchar)) $urlfillerchar = '_';
$surl = str_replace(' ',$urlfillerchar,strtolower(strip_tags($surl)));
$surl = replaceaccents($surl);
return(preg_replace('/[^a-z\\'.$urlfillerchar.'0-9]/','',$surl));
}
function vrxmlencode($xmlstr){
	return str_replace(array('&','"',"'",'<','>','’'),array('&amp;','&quot;','&apos;','&lt;','&gt;','&apos;'),$xmlstr);
}
function xmlencodecharref($xmlstr){
	$xmlstr = str_replace(array('&reg;','&','<','>','®'),array('','&#x26;','&#x3c;','&#x3e;',''),$xmlstr);
	$tmp_str='';
	for($i=0; $i < strlen($xmlstr); $i++){
		$ch_code=ord(substr($xmlstr,$i,1));
		if($ch_code<=130) $tmp_str .= substr($xmlstr,$i,1);
	}
	return($tmp_str);
}
function getlangid($col, $bfield){
	global $languageid, $adminlangsettings;
	if(@$languageid=='' || @$languageid==1){
		return($col);
	}else{
		if(($adminlangsettings & $bfield) != $bfield) return($col);
	}
	return($col . $languageid);
}
function parsedate($tdat){
	global $admindateformat;
	if($admindateformat==0)
		list($year, $month, $day) = sscanf($tdat, '%d-%d-%d');
	elseif($admindateformat==1)
		list($month, $day, $year) = sscanf($tdat, '%d/%d/%d');
	elseif($admindateformat==2)
		list($day, $month, $year) = sscanf($tdat, '%d/%d/%d');
	if(! is_numeric($year))
		$year = date('Y');
	elseif((int)$year < 39)
		$year = (int)$year + 2000;
	elseif((int)$year < 100)
		$year = (int)$year + 1900;
	if($year < 1970 || $year > 2038) $year = date('Y');
	if(! is_numeric($month))
		$month = date('m');
	if(! is_numeric($day))
		$day = date('d');
	return(mktime(0, 0, 0, $month, $day, $year));
}
function unstripslashes($slashedText){
	global $magicq;
	return($magicq?trim(stripslashes($slashedText)):trim($slashedText));
}
function getattributes($attlist,$attid){
	$pos = strpos($attlist, $attid.'=');
	if($pos === false)
		return '';
	$pos += strlen($attid) + 1;
	$quote = $attlist[$pos];
	$pos2 = strpos($attlist, $quote, $pos + 1);
	$retstr = substr($attlist, $pos + 1, $pos2 - ($pos + 1));
	return($retstr); 
}
class vrNodeList{
	var $length;
	var $childNodes;
	var $nodeName;
	var $nodeValue;
	var $attributes;

	function createNodeList($xmlStr){
		$xLen = strlen($xmlStr);
		for($i=0; $i < $xLen; $i++){
			if(substr($xmlStr, $i, 1)=='<' && substr($xmlStr, $i+1, 1) != '/' && substr($xmlStr, $i+1, 1) != '?'){ // Got a tag
				$j = strpos($xmlStr,'>',$i);
				$l = strpos($xmlStr,' ',$i);
				if(is_integer($l) && $l < $j){
					$this->nodeName[$this->length]=substr($xmlStr,$i+1,$l-($i+1));
					$this->attributes[$this->length] = substr($xmlStr,$l+1,($j-$l)-1);
				}else
					$this->nodeName[$this->length]=substr($xmlStr,$i+1,$j-($i+1));
				$k = $i+1;
				$nodeNameLen=strlen($this->nodeName[$this->length]);
				if(substr($xmlStr, $j-1, 1)=='/'){
					$this->nodeValue[$this->length]=null;
				}else{
					$currLev=0;
					while($k < $xLen && $currLev >= 0){
						if(substr($xmlStr, $k, 2)=='</'){
							if($currLev==0 && substr($xmlStr, $k+2, $nodeNameLen)==$this->nodeName[$this->length])
								break;
							$currLev--;
						}elseif(substr($xmlStr, $k, 1)=='<')
							$currLev++;
						elseif(substr($xmlStr, $k, 2)=='/>')
							$currLev--;
						$k++;
					}
					$this->nodeValue[$this->length]=substr($xmlStr,$j+1,$k-($j+1));
				}
				$this->childNodes[$this->length] = new vrNodeList($this->nodeValue[$this->length]);
				$this->length++;
				$i = $k;
			}
		}
	}
	function vrNodeList($xmlStr){
		$this->length=0;
		$this->childNodes='';
		$this->createNodeList($xmlStr);
	}
	function getValueByTagName($tagname){
		for($i=0; $i < $this->length; $i++){
			if($this->nodeName[$i]==$tagname){
				return($this->nodeValue[$i]);
			}else{
				if($this->childNodes!=''){
					if(($retval = $this->childNodes[$i]->getValueByTagName($tagname)) != null)
						return($retval);
				}
			}
		}
		return(null);
	}
	function getAttributeByTagName($tagname, $attrib){
		for($i=0; $i < $this->length; $i++){
			if($this->nodeName[$i]==$tagname){
				return(getattributes($this->attributes[$i], $attrib));
			}else{
				if($this->childNodes!=''){
					if(($retval = $this->childNodes[$i]->getAttributeByTagName($tagname, $attrib)) != null)
						return($retval);
				}
			}
		}
		return(null);
	}
}
class vrXMLDoc{
	var $tXMLStr;
	var $nodeList;
	function vrXMLDoc($xmlStr){
		$this->tXMLStr = $xmlStr;
		$this->nodeList = new vrNodeList($xmlStr);
	}
	function getElementsByTagName($tagname){
		$currlevel=0;
		$taglen = strlen($tagname);
	}
}
$codestr='2952710692840328509902143349209039553396765';
function upsencode($thestr, $propcodestr){
	global $codestr;
	if($propcodestr=='') $localcodestr=$codestr; else $localcodestr=$propcodestr;
	$newstr='';
	for($index=0; $index < strlen($localcodestr); $index++){
		$thechar = substr($localcodestr,$index,1);
		if(! is_numeric($thechar)){
			$thechar = ord($thechar) % 10;
		}
		$newstr .= $thechar;
	}
	$localcodestr = $newstr;
	while(strlen($localcodestr) < 40)
		$localcodestr .= $localcodestr;
	$newstr='';
	for($index=0; $index < strlen($thestr); $index++){
		$thechar = substr($thestr,$index,1);
		$newstr .= chr(ord($thechar)+(int)substr($localcodestr,$index,1));
	}
	return $newstr;
}
function upsdecode($thestr, $propcodestr){
	global $codestr;
	if($propcodestr=='') $localcodestr=$codestr; else $localcodestr=$propcodestr;
	$newstr='';
	for($index=0; $index < strlen($localcodestr); $index++){
		$thechar = substr($localcodestr,$index,1);
		if(! is_numeric($thechar)){
			$thechar = ord($thechar) % 10;
		}
		$newstr .= $thechar;
	}
	$localcodestr = $newstr;
	while(strlen($localcodestr) < 40)
		$localcodestr .= $localcodestr;
	if(is_null($thestr)){
		return '';
	}else{
		$newstr='';
		for($index=0; $index < strlen($thestr); $index++){
			$thechar = substr($thestr,$index,1);
			$newstr .= chr(ord($thechar)-(int)substr($localcodestr,$index,1));
		}
		return($newstr);
	}
}
$locale_info = '';
function FormatEuroCurrency($amount){
	global $useEuro, $adminLocale, $locale_info, $overridecurrency, $orcsymbol, $orcdecplaces, $orcdecimals, $orcthousands, $orcpreamount;
	if(@$overridecurrency==TRUE){
		if($orcpreamount)
			return $orcsymbol . number_format($amount,$orcdecplaces,$orcdecimals,$orcthousands);
		else
			return number_format($amount,$orcdecplaces,$orcdecimals,$orcthousands) . $orcsymbol;
	}else{
		if(! is_array($locale_info)){
			setlocale(LC_MONETARY,$adminLocale);
			$locale_info = localeconv();
			setlocale(LC_MONETARY,'en_US');
		}
		if($useEuro)
			return number_format($amount,2,$locale_info['decimal_point'],$locale_info['thousands_sep']) . ' &euro;';
		else
			return $locale_info['currency_symbol'] . number_format($amount,2,$locale_info['decimal_point'],$locale_info['thousands_sep']);
	}
}
function FormatCurrencyZeroDP($amount){
	global $useEuro, $adminLocale, $locale_info, $overridecurrency, $orcsymbol, $orcdecplaces, $orcdecimals, $orcthousands, $orcpreamount;
	if(@$overridecurrency==TRUE){
		if($orcpreamount)
			return $orcsymbol . number_format($amount,0,$orcdecimals,$orcthousands);
		else
			return number_format($amount,0,$orcdecimals,$orcthousands) . $orcsymbol;
	}else{
		if(! is_array($locale_info)){
			setlocale(LC_MONETARY,$adminLocale);
			$locale_info = localeconv();
			setlocale(LC_MONETARY,'en_US');
		}
		if($useEuro)
			return number_format($amount,0,$locale_info['decimal_point'],$locale_info['thousands_sep']) . ' &euro;';
		else
			return $locale_info['currency_symbol'] . number_format($amount,0,$locale_info['decimal_point'],$locale_info['thousands_sep']);
	}
}
function FormatEmailEuroCurrency($amount){
	global $useEuro, $adminLocale, $locale_info, $overridecurrency, $orcemailsymbol, $orcdecplaces, $orcdecimals, $orcthousands, $orcpreamount;
	if(@$overridecurrency==TRUE){
		if($orcpreamount)
			return $orcemailsymbol . number_format($amount,$orcdecplaces,$orcdecimals,$orcthousands);
		else
			return number_format($amount,$orcdecplaces,$orcdecimals,$orcthousands) . $orcemailsymbol;
	}else{
		if(! is_array($locale_info)){
			setlocale(LC_ALL,$adminLocale);
			$locale_info = localeconv();
			setlocale(LC_ALL,'en_US');
		}
		if($useEuro)
			return number_format($amount,2,$locale_info['decimal_point'],$locale_info['thousands_sep']) . ' Euro';
		else
			return $locale_info['currency_symbol'] . number_format($amount,2,$locale_info['decimal_point'],$locale_info['thousands_sep']);
	}
}
if(trim(@$_GET['PARTNER']) != '' || trim(@$_GET['REFERER']) != ''){
	if(@$expireaffiliate == '') $expireaffiliate=30;
	if(trim(@$_GET['PARTNER'])!='') $thereferer=trim(strip_tags(@$_GET['PARTNER'])); else $thereferer=trim(strip_tags(@$_GET['REFERER']));
	print "<script src='vsadmin/savecookie.php?PARTNER=" . urlencode(str_replace(array('"',"'"),'',$thereferer)) . '&EXPIRES=' . $expireaffiliate . "'></script>";
}
$stockManage=0;
function do_stock_management($smOrdId){
}
function stock_subtract($smOrdId){
	global $stockManage;
	if($stockManage != 0){
		$sSQL="SELECT cartID,cartProdID,cartQuantity,pStockByOpts FROM cart INNER JOIN products ON cart.cartProdID=products.pID WHERE cartOrderID='" . escape_string($smOrdId) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_array($result)){
			if((int)$rs['pStockByOpts'] != 0){
				$sSQL = 'SELECT coOptID FROM cartoptions INNER JOIN options ON cartoptions.coOptID=options.optID INNER JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optType IN (-4,-2,-1,1,2,4) AND coCartID=' . $rs['cartID'];
				$result2 = mysql_query($sSQL) or print(mysql_error());
				while($rs2 = mysql_fetch_array($result2)){
					$sSQL = 'UPDATE options SET optStock=optStock-' . $rs['cartQuantity'] . ' WHERE optID=' . $rs2['coOptID'];
					mysql_query($sSQL) or print(mysql_error());
				}
				mysql_free_result($result2);
			}else{
				$sSQL = 'UPDATE products SET pInStock=pInStock-' . $rs['cartQuantity'] . " WHERE pID='" . $rs['cartProdID'] . "'";
				mysql_query($sSQL) or print(mysql_error());
			}
		}
		mysql_free_result($result);
	}
}
function release_stock($smOrdId){
	global $stockManage;
	if($stockManage != 0){
		$sSQL="SELECT cartID,cartProdID,cartQuantity,pStockByOpts FROM cart LEFT JOIN orders ON cart.cartOrderID=orders.ordID INNER JOIN products ON cart.cartProdID=products.pID WHERE ordAuthStatus<>'MODWARNOPEN' AND cartOrderID=" . $smOrdId;
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_array($result)){
			if(((int)$rs['pStockByOpts'] <> 0)){
				$sSQL = 'SELECT coOptID FROM cartoptions INNER JOIN options ON cartoptions.coOptID=options.optID INNER JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optType IN (-4,-2,-1,1,2,4) AND coCartID=' . $rs['cartID'];
				$result2 = mysql_query($sSQL) or print(mysql_error());
				while($rs2 = mysql_fetch_array($result2)){
					$sSQL = 'UPDATE options SET optStock=optStock+' . $rs['cartQuantity'] . ' WHERE optID=' . $rs2['coOptID'];
					mysql_query($sSQL) or print(mysql_error());
				}
				mysql_free_result($result2);
			}else{
				$sSQL = 'UPDATE products SET pInStock=pInStock+' . $rs['cartQuantity'] . " WHERE pID='" . $rs['cartProdID'] . "'";
				mysql_query($sSQL) or print(mysql_error());
			}
		}
		mysql_free_result($result);
	}
}
function emailfriendjavascript(){
global $xxPlsEntr,$xxEFNam,$xxEFEm,$xxEFFEm;
?>
<script language="javascript" type="text/javascript">
<!--
function efformvalidator(theForm){
	if(document.getElementById('yourname').value==""){
		alert("<?php print $xxPlsEntr?> \"<?php print $xxEFNam?>\".");
		document.getElementById('yourname').focus();
		return(false);
	}
	if(document.getElementById('youremail').value==""){
		alert("<?php print $xxPlsEntr?> \"<?php print $xxEFEm?>\".");
		document.getElementById('youremail').focus();
		return(false);
	}
	if(document.getElementById('askq').value!='1'){
		if(document.getElementById('friendsemail').value==""){
			alert("<?php print $xxPlsEntr?> \"<?php print $xxEFFEm?>\".");
			document.getElementById('friendsemail').focus();
			return(false);
		}
	}
	return(true);
}
function dosendefdata(){
	if(efformvalidator(document.getElementById('efform'))){
		var ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		var yourname=document.getElementById("yourname").value;
		var youremail=document.getElementById("youremail").value;
		var friendsemail=(document.getElementById('askq').value=='1'?'':document.getElementById("friendsemail").value);
		var yourcomments=document.getElementById("yourcomments").value;
		var efcheck=document.getElementById("efcheck").value;
		postdata = "posted=1&efid=" + encodeURIComponent(document.getElementById('efid').value) + (document.getElementById('askq').value=='1'?'&askq=1':'') + "&yourname=" + encodeURIComponent(yourname) + "&youremail=" + encodeURIComponent(youremail) + "&friendsemail=" + encodeURIComponent(friendsemail) + "&efcheck=" + encodeURIComponent(efcheck) + "&yourcomments=" + encodeURIComponent(yourcomments);
		ajaxobj.open("POST", "emailfriend.php",false);
		ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxobj.setRequestHeader("Content-length", postdata.length);
		ajaxobj.setRequestHeader("Connection", "close");
		ajaxobj.send(postdata);
		document.getElementById('efrcell').innerHTML=ajaxobj.responseText;
	}
}
//-->
</script>
<?php
}
function productdisplayscript($doaddprodoptions,$isdetail){
global $prodoptions,$countryTaxRate,$xxPrdEnt,$xxPrdChs,$xxPrd255,$xxOptOOS,$xxOpSkTx,$xxInStNo,$xxValEm,$xxNoStEm,$xxEmail,$xxSubmt,$xxCancel,$useStockManagement,$prodlist,$OWSP,$xxEntMul,$noupdateprice,$noprice,$hideoptpricediffs,$showinstock,$noshowoptionsinstock,$showtaxinclusive,$notifybackinstock,$xxInvCha;
global $currSymbol1,$currFormat1,$currSymbol2,$currFormat2,$currSymbol3,$currFormat3,$pricecheckerisincluded,$xxDigits,$sstrong,$estrong,$currRate1,$currSymbol1,$currRate2,$currSymbol2,$currRate3,$currSymbol3,$currencyseparator,$enablewishlists,$wishlistonproducts,$xxMyWisL,$pricezeromessage,$xxMaxChr,$inlinepopups;
if($currSymbol1!='' && $currFormat1=='') $currFormat1='%s <span style="font-weight:bold">'  . $currSymbol1 . '</span>';
if($currSymbol2!='' && $currFormat2=='') $currFormat2='%s <span style="font-weight:bold">'  . $currSymbol2 . '</span>';
if($currSymbol3!='' && $currFormat3=='') $currFormat3='%s <span style="font-weight:bold">'  . $currSymbol3 . '</span>';
	if(! (@$pricecheckerisincluded==TRUE)){
		if(@$_SESSION['clientID']!='' && (@$wishlistonproducts || $isdetail)){ ?>
<div id="savelistdiv" style="position:absolute; visibility:hidden; top:0px; left:0px; width:auto; height:auto; z-index:10000;" onmouseover="oversldiv=true;" onmouseout="oversldiv=false;setTimeout('checksldiv()',1000)">
<table class="cobtbl" cellspacing="1" cellpadding="3">
<tr><td class="cobll" onmouseover="this.className='cobhl'" onmouseout="this.className='cobll'" style="white-space: nowrap"><a class="ectlink wishlistmenu" href="#" onclick="document.getElementById('ectform'+gtid).listid.value=0;return subformid(gtid);"><?php print $xxMyWisL?></a></td></tr>
<?php		$sSQL = "SELECT listID,listName FROM customerlists WHERE listOwner=" . $_SESSION['clientID'];
			$result2 = mysql_query($sSQL) or print(mysql_error());
			while($rs2 = mysql_fetch_array($result2)){
				print '<tr><td class="cobll" onmouseover="this.className=\'cobhl\'" onmouseout="this.className=\'cobll\'" style="white-space: nowrap"><a class="ectlink wishlistmenu" href="#" onclick="document.getElementById(\'ectform\'+gtid).listid.value='.$rs2['listID'].';return subformid(gtid);">'.htmlspecials($rs2['listName']).'</a></td></tr>';
			}
			mysql_free_result($result2); ?>
</table></div>
<?php	}
		if(@$notifybackinstock){ ?>
<div id="notifyinstockcover" style="<?php print (strstr(@$_SERVER['HTTP_USER_AGENT'],'Gecko')?'':'filter:alpha(opacity=50);')?>opacity:0.5;background:#AAAAAA;position:fixed;visibility:hidden;top:0px;left:0px;width:100%;height:auto;z-index:99;">&nbsp;</div>
<div id="notifyinstockdiv" style="position:absolute;visibility:hidden;top:10px;left:10px;height:auto;z-index:100;">
<table class="cobtbl" cellspacing="1" cellpadding="3" bgcolor="#B1B1B1">
<tr><td class="cobhl" bgcolor="#EBEBEB" colspan="2" align="right"><a href="javascript:closeinstock()"><img src="images/close.gif" border="0" alt="Close" /></a></td></tr>
<tr id="notifyoption"><td class="cobll" bgcolor="#FFFFFF" style="white-space:nowrap;text-align:center" colspan="2"><?php print $xxOptOOS?></td></tr>
<tr><td class="cobll" bgcolor="#FFFFFF" style="white-space:nowrap" colspan="2"><?php print $xxNoStEm?></td></tr>
<tr><td class="cobll" bgcolor="#FFFFFF" align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php print $xxEmail?>:</td><td class="cobll" bgcolor="#FFFFFF"><input type="text" id="nsemailadd" size="35" /></td></tr>
<tr><td class="cobll" bgcolor="#FFFFFF" style="white-space:nowrap" colspan="2" align="center"><input type="button" value="<?php print $xxSubmt?>" onclick="regnotifystock()" /> &nbsp; <input type="button" value="<?php print $xxCancel?>" onclick="closeinstock()" /></td></tr>
</table></div>
<?php	}
?><input type="hidden" id="hiddencurr" value="<?php print htmlspecials(FormatEuroCurrency(0))?>" /><script language="javascript" type="text/javascript">
/* <![CDATA[ */
<?php	if(@$enablewishlists==TRUE){ ?>
var oversldiv;
var gtid;
function displaysavelist(evt,twin){
	oversldiv=false
	var theevnt=(!evt)?twin.event:evt;//IE:FF
	var sld = document.getElementById('savelistdiv');
	var scrolltop=(document.documentElement.scrollTop?document.documentElement.scrollTop:document.body.scrollTop);
	var scrollleft=(document.documentElement.scrollLeft?document.documentElement.scrollLeft:document.body.scrollLeft);
	sld.style.left = ((theevnt.clientX+scrollleft)-sld.offsetWidth)+'px';
    sld.style.top = (theevnt.clientY+scrolltop)+'px';
	sld.style.visibility = "visible";
	setTimeout('checksldiv()',2000);
	return(false);
}
function checksldiv(){
	var sld = document.getElementById('savelistdiv');
	if(! oversldiv)
		sld.style.visibility = 'hidden';
}
<?php	}
		if(@$notifybackinstock){ ?>
var notifystockid;
var notifystocktid;
var notifystockoid;
var nsajaxobj;
function notifystockcallback(){
	if(nsajaxobj.readyState==4){
		var rstxt=nsajaxobj.responseText;
		if(rstxt!='SUCCESS')alert(rstxt);else alert("<?php print $xxInStNo?>");
		closeinstock();
	}
}
function regnotifystock(){
	var regex = /[^@]+@[^@]+\.[a-z]{2,}$/i;
	var theemail = document.getElementById('nsemailadd');
	if(!regex.test(theemail.value)){
		alert("<?php print $xxValEm?>");
		theemail.focus();
		return(false);
	}else{
		nsajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
		nsajaxobj.onreadystatechange = notifystockcallback;
		nsajaxobj.open("GET", "vsadmin/ajaxservice.php?action=notifystock&pid="+encodeURIComponent(notifystockid)+'&tpid='+encodeURIComponent(notifystocktid)+'&oid='+encodeURIComponent(notifystockoid)+'&email='+encodeURIComponent(theemail.value),true);
		nsajaxobj.send(null);
	}
}
function closeinstock(){
	document.getElementById('notifyinstockdiv').style.visibility='hidden';
	document.getElementById('notifyinstockcover').style.visibility='hidden';
}
function notifyinstock(isoption,pid,tpid,oid){
	notifystockid=pid;
	notifystocktid=tpid;
	notifystockoid=oid;
	var ie=document.all && !window.opera;
	var bsd = document.getElementById('notifyinstockdiv');
	var isc = document.getElementById('notifyinstockcover');
	var viewportwidth=600;
	var viewportheight=400;
	if (typeof window.innerWidth != 'undefined'){
		viewportwidth = window.innerWidth;
		viewportheight = window.innerHeight;
	}else if(typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth!='undefined' && document.documentElement.clientWidth != 0){
		viewportwidth = document.documentElement.clientWidth;
		viewportheight = document.documentElement.clientHeight;
	}
	var scrolltop=(document.documentElement.scrollTop?document.documentElement.scrollTop:document.body.scrollTop);
	var scrollleft=(document.documentElement.scrollLeft?document.documentElement.scrollLeft:document.body.scrollLeft);
	var notifyoption=document.getElementById('notifyoption');
	notifyoption.style.display=(isoption?'':'none');
	isc.style.height='2000px';
	isc.style.visibility='visible';
	bsd.style.left = (scrollleft+((viewportwidth-bsd.offsetWidth)/2))+'px';
    bsd.style.top = (scrolltop+((viewportheight-bsd.offsetHeight)/2))+'px';
	bsd.style.visibility = 'visible';
	return(false);
}
<?php	} ?>
function subformid(tid){
	var tform=document.getElementById('ectform'+tid);
	if(tform.onsubmit())tform.submit();
	return(false);
}
var op=[]; // Option Price Difference
var aIM=[]; // Option Alternate Image
var dIM=[]; // Default Image
var pIM=[]; // Product Image
var pIX=[]; // Product Image Index
var ot=[]; // Option Text
var pp=[]; // Product Price
var pi=[]; // Alternate Product Image
var or=[]; // Option Alt Id
var cp=[]; // Current Price
var oos=[]; // Option Out of Stock Id
var rid=[]; // Resulting product Id
var otid=[]; // Original product Id
var opttype=[];
var optperc=[];
var optmaxc=[];
var optacpc=[];
var fid=[];
var baseid='';<?php
			if($useStockManagement){ ?>
var oS=[];
var ps=[];
function checkStock(x,i){
if((i!=''&&oS[i]>0)||or[i])return(true);
<?php	if(@$notifybackinstock){ ?>
notifyinstock(true,x.form.id.value,x.form.id.value,i);
<?php	}else{ ?>
alert('<?php print str_replace("'","\'",$xxOptOOS)?>');
<?php	} ?>
x.focus();return(false);
}<?php		} ?>
var isW3 = (document.getElementById&&true);
var tax=<?php print $countryTaxRate ?>;
function dummyfunc(){};
function pricechecker(cnt,i){
if(i!=''&&i in op)return(op[i]);return(0);}
function regchecker(cnt,i){
if(i!='')return(or[i]);return('');}
function enterValue(x){
alert('<?php print str_replace("'","\'",$xxPrdEnt)?>');
x.focus();return(false);}
function invalidChars(x){
alert("<?php print $xxInvCha?>" + x);
return(false);}
function enterDigits(x){alert("<?php print str_replace("'","\'",$xxDigits)?>");x.focus();return(false);}
function enterMultValue(){alert("<?php print str_replace("'","\'",$xxEntMul)?>");return(false);}
function chooseOption(x){
alert("<?php print $xxPrdChs?>");
x.focus();return(false);}
function dataLimit(x,numchars){
alert("<?php print $xxPrd255?>".replace(255,numchars));
x.focus();return(false);}
var hiddencurr='';
function addCommas(ns,decs,thos){
if((dpos=ns.indexOf(decs))<0)dpos=ns.length;
dpos-=3;
while(dpos>0){
	ns=ns.substr(0,dpos)+thos+ns.substr(dpos);
	dpos-=3;
}
return(ns);
}
function formatprice(i, currcode, currformat){
<?php
	$tempStr = FormatEuroCurrency(0);
	print "if(hiddencurr=='')hiddencurr=document.getElementById('hiddencurr').value;var pTemplate=hiddencurr;\n";
	print "if(currcode!='') pTemplate=' " . number_format(0,2,'.',',') . "' + (currcode!=' '?'<strong>'+currcode+'<\/strong>':'');";
	print 'if(currcode==" JPY")i=Math.round(i).toString();';
	if(strstr($tempStr,',') || strstr($tempStr,'.')){ ?>
else if(i==Math.round(i))i=i.toString()+".00";
else if(i*10.0==Math.round(i*10.0))i=i.toString()+"0";
else if(i*100.0==Math.round(i*100.0))i=i.toString();
<?php }
	print 'i=addCommas(i.toString()'.(strstr($tempStr,',')?".replace(/\\./,','),',','.'":",'.',','").');';
	print 'if(currcode!="")pTemplate = currformat.toString().replace(/%s/,i.toString());';
	print 'else pTemplate = pTemplate.toString().replace(/\d[,.]*\d*/,i.toString());';
	print 'return(pTemplate);';
?>}
function openEFWindow(id,askq){
<?php	if(@$inlinepopups!=TRUE){ ?>
window.open('emailfriend.php?'+(askq?'askq=1&':'')+'id='+id,'email_friend','menubar=no, scrollbars=no, width=420, height=400, directories=no,location=no,resizable=yes,status=no,toolbar=no')
<?php	}else{ ?>
var ecx = window.pageXOffset ? window.pageXOffset : document.documentElement && document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body ? document.body.scrollLeft : 0;
var ecy = window.pageYOffset ? window.pageYOffset : document.documentElement && document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body ? document.body.scrollTop : 0;
var viewportwidth=600;
if (typeof window.innerWidth != 'undefined'){
	viewportwidth = window.innerWidth;
}else if(typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth!='undefined' && document.documentElement.clientWidth != 0){
	viewportwidth = document.documentElement.clientWidth;
}
efrdiv = document.createElement('div');
efrdiv.setAttribute('id', 'efrdiv');
efrdiv.style.zIndex=1000;
efrdiv.style.position = 'absolute';
efrdiv.style.width = '100%';
efrdiv.style.height = '2000px';
efrdiv.style.top = '0px';
efrdiv.style.left = ecx + 'px';
efrdiv.style.textAlign = 'center';
efrdiv.style.backgroundImage='url(images/opaquepixel.png)';
document.body.appendChild(efrdiv);
ajaxobj=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("MSXML2.XMLHTTP");
ajaxobj.open("GET", 'emailfriend.php?'+(askq?'askq=1&':'')+'id='+id, false);
ajaxobj.send(null);
efrdiv.innerHTML=ajaxobj.responseText;
document.getElementById('emftable').style.top = (ecy+100)+'px';
document.getElementById('emftable').style.left = (((viewportwidth-500)/2))+'px';
<?php	} ?>
}
function updateoptimage(theitem,themenu,opttype){
if(imageitem=document.getElementById("prodimage"+theitem)){
var imageitemsrc='';
if(opttype==1){
	theopt=document.getElementsByName('optn'+theitem+'x'+themenu)
	for(var i=0; i<theopt.length; i++)
		if(theopt[i].checked)theid=theopt[i].value;
}else{
	theopt=document.getElementById('optn'+theitem+'x'+themenu)
	theid=theopt.options[theopt.selectedIndex].value;
}
if(aIM[theid]){
	if(typeof(imageitem.src)!='unknown')imageitem.src=aIM[theid];
}
}
}
function updateprodimage(theitem,isnext){
var imlist=pIM[theitem].split('\|');
if(isnext) pIX[theitem]++; else pIX[theitem]--;
if(pIX[theitem]<0) pIX[theitem]=imlist.length-2;
if(pIX[theitem]>imlist.length-2) pIX[theitem]=0;
document.getElementById("prodimage"+theitem).src=imlist[pIX[theitem]];
document.getElementById("extraimcnt"+theitem).innerHTML=pIX[theitem]+1;
return false;
}
<?php	if($doaddprodoptions){ ?>
function sz(szid,szprice,<?php if($useStockManagement) print 'szstock,'?>szimage){
<?php		if($useStockManagement) print 'ps[szid]=szstock;'; ?>
	pp[szid]=szprice;
	szimage = szimage.replace("|", "prodimages/")
	szimage = szimage.replace("<", ".gif")
	szimage = szimage.replace(">", ".jpg")
	if(szimage!='')pi[szid]=szimage;
}
function gfid(tid){
	if(tid in fid)
		return(fid[tid]);
	fid[tid]=document.getElementById(tid);
	return(fid[tid]);
}
function applyreg(arid,arreg){
	if(arreg&&arreg!=''){
		arreg = arreg.replace('%s', arid);
		if(arreg.indexOf(' ')>0){
			var ida = arreg.split(' ', 2);
			arid = arid.replace(ida[0], ida[1]);
		}else
			arid = arreg;
	}
	return(arid);
}
function getaltid(theid,optns,prodnum,optnum,optitem,numoptions){
	var thereg = '';
	for(var index=0; index<numoptions; index++){
		if(Math.abs(opttype[index])==4){
			thereg = or[optitem];
		}else if(Math.abs(opttype[index])==2){
			if(optnum==index)
				thereg = or[optns.options[optitem].value];
			else{
				var opt=gfid("optn"+prodnum+"x"+index);
				thereg = or[opt.options[opt.selectedIndex].value];
			}
		}else if(Math.abs(opttype[index])==1){
			opt=document.getElementsByName("optn"+prodnum+"x"+index);
			if(optnum==index){
				thereg = or[opt[optitem].value];
			}else{
				for(var y=0;y<opt.length;y++)
					if(opt[y].checked) thereg = or[opt[y].value];
			}
		}else
			continue;
		theid = applyreg(theid,thereg);
	}
	return(theid);
}
function getnonaltpricediff(optns,prodnum,optnum,optitem,numoptions,theoptprice){
	var nonaltdiff=0;
	for(index=0; index<numoptions; index++){
		var optid = '';
		if(Math.abs(opttype[index])==4){
			optid=optitem;
		}else if(Math.abs(opttype[index])==2){
			if(optnum==index)
				optid = optns.options[optitem].value;
			else{
				var opt=gfid("optn"+prodnum+"x"+index);
				optid = opt.options[opt.selectedIndex].value;
			}
		}else if(Math.abs(opttype[index])==1){
			var opt=document.getElementsByName("optn"+prodnum+"x"+index);
			if(optnum==index)
				optid = opt[optitem].value;
			else{
				for(var y=0;y<opt.length;y++){ if(opt[y].checked) optid=opt[y].value; }
			}
		}else
			continue;
		if(!or[optid]&&optid in op)
			if(optperc[index])nonaltdiff+=(op[optid]*theoptprice)/100.0;else nonaltdiff+=op[optid];
	}
	return(nonaltdiff);
}
function updateprice(numoptions,prodnum,prodprice,origid,thetax,stkbyopts,taxexmpt){
	baseid=origid;
	if(!isW3) return;
	oos[prodnum]='';
	var origprice=prodprice;
	var hasmultioption=false;
	for(cnt=0; cnt<numoptions; cnt++){
		if(Math.abs(opttype[cnt])==2){
			optns=gfid("optn"+prodnum+"x"+cnt);
			baseid=applyreg(baseid,regchecker(prodnum,optns.options[optns.selectedIndex].value));
		}else if(Math.abs(opttype[cnt])==1){
			optns=document.getElementsByName("optn"+prodnum+"x"+cnt);
			for(var i=0;i<optns.length;i++){ if(optns[i].checked) baseid=applyreg(baseid,regchecker(prodnum,optns[i].value)); }
		}
		if(baseid in pp)prodprice=pp[baseid];
	}
	var baseprice=prodprice;
	for(cnt=0; cnt<numoptions; cnt++){
		if(Math.abs(opttype[cnt])==2){
			optns=gfid("optn"+prodnum+"x"+cnt);
			if(optperc[cnt])
				prodprice+=((baseprice*pricechecker(prodnum,optns.options[optns.selectedIndex].value))/100.0);
			else
				prodprice+=pricechecker(prodnum,optns.options[optns.selectedIndex].value);
		}else if(Math.abs(opttype[cnt])==1){
			optns=document.getElementsByName("optn"+prodnum+"x"+cnt);
			if(optperc[cnt])
				for(var i=0;i<optns.length;i++){ if(optns[i].checked) prodprice+=((baseprice*pricechecker(prodnum,optns[i].value))/100.0); }
			else
				for(var i=0;i<optns.length;i++){ if(optns[i].checked) prodprice+=pricechecker(prodnum,optns[i].value); }
		}
	}
	var totalprice=prodprice;
	for(cnt=0; cnt<numoptions; cnt++){
		if(Math.abs(opttype[cnt])==2){
			var optns=gfid("optn"+prodnum+"x"+cnt);
			for(var i=0;i<optns.length;i++){
				if(optns.options[i].value!=''){
					theid = origid;
					optns.options[i].text=ot[optns.options[i].value];
					theid = getaltid(theid,optns,prodnum,cnt,i,numoptions);
					theoptprice = (theid in pp?pp[theid]:origprice);
					if(pi[theid]&&pi[theid]!=''&&or[optns.options[i].value])aIM[optns.options[i].value]=pi[theid];<?php
	if($useStockManagement){ ?>
					theoptstock=((theid in ps&&or[optns.options[i].value])||!stkbyopts ? ps[theid] : oS[optns.options[i].value]);
					if(theoptstock<=0&&optns.selectedIndex==i){oos[prodnum]="optn"+prodnum+"x"+cnt;rid[prodnum]=theid;otid[prodnum]=origid;}
					canresolve=(!or[optns.options[i].value]||theid in ps)?true:false;<?php
	} ?>
					var staticpricediff = getnonaltpricediff(optns,prodnum,cnt,i,numoptions,theoptprice);
					theoptpricediff=(theoptprice+staticpricediff)-totalprice;
<?php
	if(@$noprice!=TRUE && @$hideoptpricediffs!=TRUE) print "if(theoptpricediff!=0)optns.options[i].text+=' ('+(theoptpricediff>0?'+':'-')+formatprice(Math.abs(Math.round((theoptpricediff".(@$showtaxinclusive===2?'+(!taxexmpt?theoptpricediff*thetax/100.0:0)':'').")*100)/100.0), '', '')+')';";
	if($useStockManagement && @$showinstock==TRUE && @$noshowoptionsinstock!=TRUE) print "if(stkbyopts&&canresolve)optns.options[i].text+='".str_replace("'","\'",$xxOpSkTx)."'.replace('%s',theoptstock);";
	if($useStockManagement) print "if(theoptstock>0||!stkbyopts||!canresolve)optns.options[i].className='';else optns.options[i].className='oostock';"; ?>
				}
			}
		}else if(Math.abs(opttype[cnt])==1){
			optns=document.getElementsByName("optn"+prodnum+"x"+cnt);
			for(var i=0;i<optns.length;i++){
				theid = origid;
				optn=gfid("optn"+prodnum+"x"+cnt+"y"+i);
				optn.innerHTML=ot[optns[i].value];
				theid = getaltid(theid,optns,prodnum,cnt,i,numoptions);
				theoptprice = (theid in pp?pp[theid]:origprice);
				if(pi[theid]&&pi[theid]!=''&&or[optns[i].value])aIM[optns[i].value]=pi[theid];<?php
	if($useStockManagement){ ?>
				theoptstock = ((theid in ps&&or[optns[i].value])||!stkbyopts ? ps[theid] : oS[optns[i].value]);
				if(theoptstock<=0&&optns[i].checked){oos[prodnum]="optn"+prodnum+"x"+cnt+"y"+i;rid[prodnum]=theid;otid[prodnum]=origid;}
				canresolve = (!or[optns[i].value]||theid in ps)?true:false;<?php
	} ?>
				var staticpricediff = getnonaltpricediff(optns,prodnum,cnt,i,numoptions,theoptprice);
				theoptpricediff=(theoptprice+staticpricediff)-totalprice;
<?php
	if(@$noprice!=TRUE && @$hideoptpricediffs!=TRUE) print "if(theoptpricediff!=0)optn.innerHTML+=' ('+(theoptpricediff>0?'+':'-')+formatprice(Math.abs(Math.round((theoptpricediff".(@$showtaxinclusive===2?'+(!taxexmpt?theoptpricediff*thetax/100.0:0)':'').")*100)/100.0), '', '')+')';";
	if($useStockManagement && @$showinstock==TRUE && @$noshowoptionsinstock!=TRUE) print "if(stkbyopts&&canresolve)optn.innerHTML+='".str_replace("'","\'",$xxOpSkTx)."'.replace('%s',theoptstock);";
	if($useStockManagement) print "if(theoptstock>0||!stkbyopts||!canresolve)optn.className='';else optn.className='oostock';"; ?>
			}
		}else if(Math.abs(opttype[cnt])==4){
			var tstr="optm"+prodnum+"x"+cnt+"y";
			var tlen=tstr.length;
			var optns=document.getElementsByTagName("input");
			hasmultioption=true;
			for(var i=0;i<optns.length;i++){
				if(optns[i].id.substr(0,tlen)==tstr){
					theid = origid;
					var oid=optns[i].name.substr(4);
					var optn=optns[i]
					var optnt=gfid(optns[i].id.replace(/optm/,"optx"));
					optnt.innerHTML='&nbsp;- '+ot[oid];
					theid = getaltid(theid,optns,prodnum,cnt,oid,numoptions);
					theoptprice = (theid in pp?pp[theid]:origprice);<?php
	if($useStockManagement){ ?>
				theoptstock = ((theid in ps&&or[oid])||!stkbyopts ? ps[theid] : oS[oid]);
				if(theoptstock<=0&&optns[i].checked){oos[prodnum]="optm"+prodnum+"x"+cnt+"y"+i;rid[prodnum]=theid;otid[prodnum]=origid;}
				canresolve = (!or[oid]||(applyreg(theid,or[oid]) in ps))?true:false;<?php
	} ?>
				var staticpricediff = getnonaltpricediff(optns,prodnum,cnt,oid,numoptions,theoptprice);
				theoptpricediff=(theoptprice+staticpricediff)-totalprice;
<?php
	if(@$noprice!=TRUE && @$hideoptpricediffs!=TRUE) print "if(theoptpricediff!=0)optnt.innerHTML+=' ('+(theoptpricediff>0?'+':'-')+formatprice(Math.abs(Math.round((theoptpricediff".(@$showtaxinclusive===2?'+(!taxexmpt?theoptpricediff*thetax/100.0:0)':'').")*100)/100.0), '', '')+')';";
	if($useStockManagement && @$showinstock==TRUE && @$noshowoptionsinstock!=TRUE) print "if(stkbyopts&&canresolve&&!(or[oid]&&theoptstock<=0))optnt.innerHTML+='".str_replace("'","\'",$xxOpSkTx)."'.replace('%s',theoptstock);";
	if($useStockManagement) print "if(theoptstock>0||(or[oid]&&!canresolve)){optn.className='multioption';optn.disabled=false;optn.style.backgroundColor='#FFFFFF';}else{optn.className='multioption oostock';optn.disabled=true;optn.style.backgroundColor='#EBEBE4';}"; ?>
				}
			}
		}
	}
	if(hasmultioption)oos[prodnum]='';
	if((!cp[prodnum]||cp[prodnum]==0)&&prodprice==0)return;
	cp[prodnum]=prodprice;
<?php
	if(@$noprice!=true){
		print "if(document.getElementById('taxmsg'+prodnum))document.getElementById('taxmsg'+prodnum).style.display='';";
		if(@$noupdateprice!=TRUE) print "document.getElementById('pricediv'+prodnum).innerHTML=".(@$pricezeromessage!=''?"prodprice==0?'".str_replace("'","\'",$pricezeromessage)."':":'').'formatprice(Math.round((prodprice'.(@$showtaxinclusive===2?'+(!taxexmpt?prodprice*thetax/100.0:0)':'').")*100.0)/100.0, '', '');\r\n";
		if(@$showtaxinclusive==1) print "if(!taxexmpt&&prodprice!=0){ document.getElementById('pricedivti'+prodnum).innerHTML=formatprice(Math.round((prodprice+(prodprice*thetax/100.0))*100.0)/100.0, '', ''); }else{ if(document.getElementById('taxmsg'+prodnum))document.getElementById('taxmsg'+prodnum).style.display='none'; }\r\n";
		$extracurr='';
		if($currRate1!=0 && $currSymbol1!='') $extracurr = "+formatprice(Math.round((prodprice*".$currRate1.")*100.0)/100.0, ' " . $currSymbol1 . "','" . str_replace("'","\'",$currFormat1) . "')+'".str_replace("'","\'",$currencyseparator)."'";
		if($currRate2!=0 && $currSymbol2!='') $extracurr .= "+formatprice(Math.round((prodprice*".$currRate2.")*100.0)/100.0, ' " . $currSymbol2 . "','" . str_replace("'","\'",$currFormat2) . "')+'".str_replace("'","\'",$currencyseparator)."'";
		if($currRate3!=0 && $currSymbol3!='') $extracurr .= "+formatprice(Math.round((prodprice*".$currRate3.")*100.0)/100.0, ' " . $currSymbol3 . "','" . str_replace("'","\'",$currFormat3) . "');";
		if($extracurr!='') print "document.getElementById('pricedivec'+prodnum).innerHTML=prodprice==0?'':''" . $extracurr . "\r\n";
	}
?>}
function ectvalidate(theForm,numoptions,prodnum,stkbyopts){
	for(cnt=0; cnt<numoptions; cnt++){
		if(Math.abs(opttype[cnt])==4){
			var intreg = /^(\d*)$/;var inputs = theForm.getElementsByTagName('input');var tt='';
			for(var i=0;i<inputs.length;i++){if(inputs[i].type=='text'&&inputs[i].id.substr(0,4)=='optm'){if(! inputs[i].value.match(intreg))return(enterDigits(inputs[i]));tt+=inputs[i].value;}}if(tt=='')return(enterMultValue());
		}else if(Math.abs(opttype[cnt])==3){
			var voptn=eval('theForm.voptn'+cnt);
			if(optacpc[cnt].length>0){ var re = new RegExp("["+optacpc[cnt]+"]","g"); if(voptn.value.replace(re, "")!='')return(invalidChars(voptn.value.replace(re, ""))); }
			if(opttype[cnt]==3&&voptn.value=='')return(enterValue(voptn));
			if(voptn.value.length>(optmaxc[cnt]>0?optmaxc[cnt]:255))return(dataLimit(voptn,optmaxc[cnt]>0?optmaxc[cnt]:255));
		}else if(Math.abs(opttype[cnt])==2){
			optn=document.getElementById("optn"+prodnum+"x"+cnt);
			if(opttype[cnt]==2){ if(optn.selectedIndex==0)return(chooseOption(eval('theForm.optn'+cnt))); }
			if(stkbyopts&&optn.options[optn.selectedIndex].value!=''){ if(!checkStock(optn,optn.options[optn.selectedIndex].value))return(false); }
		}else if(Math.abs(opttype[cnt])==1){
			havefound='';optns=document.getElementsByName('optn'+prodnum+'x'+cnt);
			if(opttype[cnt]==1){ for(var i=0; i<optns.length; i++) if(optns[i].checked)havefound=optns[i].value;if(havefound=='')return(chooseOption(optns[0])); }
			if(stkbyopts){ if(havefound!=''){if(!checkStock(optns[0],havefound))return(false);} }
		}
	}
<?php print @$customvalidator?>
if(oos[prodnum]&&oos[prodnum]!=''){<?php if(@$notifybackinstock) print 'notifyinstock(true,otid[prodnum],rid[prodnum],0);'; else print 'alert("'.$xxOptOOS.'");'?>document.getElementById(oos[prodnum]).focus();return(false);}
return (true);
}
<?php	} // doaddprodoptions
?>/* ]]> */
</script><?php
		$pricecheckerisincluded=TRUE;
	}
}
function updatepricescript($doaddprodoptions,$thetax,$isdetail){
	global $rs,$extraimages,$giftcertificateid,$donationid,$useStockManagement,$showinstock,$noshowoptionsinstock,$prodoptions,$Count,$allimages,$numallimages;
	$prodoptions='';
	$sSQL = "SELECT poOptionGroup,optType,optFlags,optTxtMaxLen,optAcceptChars FROM prodoptions INNER JOIN optiongroup ON optiongroup.optGrpID=prodoptions.poOptionGroup WHERE poProdID='".$rs['pId']."' AND NOT (poProdID='".$giftcertificateid."' OR poProdID='".$donationid."') ORDER BY poID";
	$result = mysql_query($sSQL) or print(mysql_error());
	for($rowcounter=0;$rowcounter<mysql_num_rows($result);$rowcounter++){
		$prodoptions[$rowcounter] = mysql_fetch_assoc($result);
	}
	mysql_free_result($result); ?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
<?php
	if($doaddprodoptions && $prodoptions!=''){ ?>
function setvals<?php print $Count?>(){<?php
		foreach($prodoptions as $rowcounter => $theopt){
			print 'optacpc[' . $rowcounter . "]='" . jsspecials($theopt['optAcceptChars']) . "';optmaxc[" . $rowcounter . ']=' . $theopt['optTxtMaxLen'] . ';opttype['.$rowcounter.']=' . (int)$theopt['optType'] . ';optperc['.$rowcounter.']=' . (($theopt['optFlags'] & 1)==1 ? 'true' : 'false') . ";\r\n";
		} ?>
}
<?php
	} ?>
function formvalidator<?php print $Count?>(theForm){
<?php
	if($doaddprodoptions && $prodoptions!=''){
		print 'setvals'.$Count.'();';
		print 'return(ectvalidate(theForm,'.count($prodoptions).','.$Count.','.($useStockManagement && $rs['pStockByOpts']!=0 ? 'true' : 'false').'));';
	}else print 'return(true);'; ?>
}
<?php
	if($doaddprodoptions && $prodoptions!=''){ ?>
function updateprice<?php print $Count?>(){<?php
		print 'setvals'.$Count.'();';
		print 'updateprice('.count($prodoptions).','.$Count.','.$rs['pPrice'].",'".$rs['pId']."',".$thetax.','.($useStockManagement && $rs['pStockByOpts']!=0 ? 'true' : 'false').','.(($rs['pExemptions'] & 2)==2 ? 'true' : 'false').');'; ?>
}
<?php
	}
	if(is_array($allimages) && $numallimages>1){
		print 'pIX['.$Count.']=0;pIM['.$Count."]='";
		print $allimages[0]['imageSrc'].'|';
		$extraimages=1;
		for($index=1;$index<$numallimages;$index++){
			print $allimages[$index]['imageSrc'].'|'; $extraimages++;
		}
		print "';\r\n";
	}
?>/* ]]> */
</script><?php
}
function checkDPs($currcode){
	if($currcode=="JPY") return(0); else return(2);
}
function checkCurrencyRates($currConvUser,$currConvPw,$currLastUpdate,&$currRate1,$currSymbol1,&$currRate2,$currSymbol2,&$currRate3,$currSymbol3){
	global $countryCurrency,$usecurlforfsock,$pathtocurl,$curlproxy;
	$ccsuccess = true;
	if($currConvUser!="" && $currConvPw!="" && (strtotime($currLastUpdate) < time()-(60*60*24))){
		$str = "";
		if($currSymbol1!='') $str .= '&curr=' . $currSymbol1;
		if($currSymbol2!='') $str .= '&curr=' . $currSymbol2;
		if($currSymbol3!='') $str .= '&curr=' . $currSymbol3;
		if($str==''){
			mysql_query("UPDATE admin SET currLastUpdate='" . date('Y-m-d H:i:s', time()) . "'") or print(mysql_error());
			return;
		}
		$str = '?source=' . $countryCurrency . '&user=' . $currConvUser . '&pw=' . $currConvPw . $str;
		if(@$usecurlforfsock){
			if(@$pathtocurl!=''){
				exec($pathtocurl . ' --data-binary \'X\' http://www.ecommercetemplates.com/currencyxml.asp' . $str, $res, $retvar);
				$sXML = implode("\n",$res);
			}else
				$ccsuccess = callcurlfunction('http://www.ecommercetemplates.com/currencyxml.asp' . $str, 'X', $sXML, '', $errormsg, FALSE);
		}else{
			$header = 'POST /currencyxml.asp' . $str . " HTTP/1.0\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: 1\r\n\r\n";
			$fp = fsockopen('www.ecommercetemplates.com', 80, $errno, $errstr, 30);
			if (!$fp){
				$errormsg = $errstr.' ('.$errno.')';
				$ccsuccess = FALSE;
			}else{
				fputs($fp, $header.'X');
				$sXML='';
				while (!feof($fp))
					$sXML .= fgets ($fp, 1024);
			}
		}
		if($ccsuccess){
			$xmlDoc = new vrXMLDoc($sXML);
			$nodeList = $xmlDoc->nodeList->childNodes[0];
			for($j = 0; $j < $nodeList->length; $j++){
				if($nodeList->nodeName[$j]=='currError'){
					print $nodeList->nodeValue[$j];
					$ccsuccess = false;
				}elseif($nodeList->nodeName[$j]=='selectedCurrency'){
					$e = $nodeList->childNodes[$j];
					$currRate = 0;
					for($i = 0; $i < $e->length; $i++){
						if($e->nodeName[$i]=='currSymbol')
							$currSymbol = $e->nodeValue[$i];
						elseif($e->nodeName[$i]=='currRate')
							$currRate = $e->nodeValue[$i];
					}
					if($currSymbol1 == $currSymbol){
						$currRate1 = $currRate;
						mysql_query('UPDATE admin SET currRate1=' . $currRate . ' WHERE adminID=1') or print(mysql_error());
					}
					if($currSymbol2 == $currSymbol){
						$currRate2 = $currRate;
						mysql_query('UPDATE admin SET currRate2=' . $currRate . ' WHERE adminID=1') or print(mysql_error());
					}
					if($currSymbol3 == $currSymbol){
						$currRate3 = $currRate;
						mysql_query('UPDATE admin SET currRate3=' . $currRate . ' WHERE adminID=1') or print(mysql_error());
					}
				}
			}
			if($ccsuccess) mysql_query("UPDATE admin SET currLastUpdate='" . date('Y-m-d H:i:s', time()) . "'");
		}
	}
}
function getsectionids($thesecid, $delsections){
	$resolvedids='';
	$secarr = explode(',', $thesecid);
	$secid = ''; $addcomma = ''; $addcomma2 = '';
	foreach($secarr as $sect){
		if(is_numeric(trim($sect))) $secid .= $addcomma . $sect; $addcomma = ',';
	}
	if($secid == '') $secid='0';
	$iterations = 0;
	$iteratemore = TRUE;
	if(@$_SESSION['clientLoginLevel'] != '') $minloglevel=$_SESSION['clientLoginLevel']; else $minloglevel=0;
	if($delsections) $nodel = ''; else $nodel = 'sectionDisabled<=' . $minloglevel . ' AND ';
	while($iteratemore && $iterations<10){
		$sSQL2 = 'SELECT DISTINCT sectionID,rootSection FROM sections WHERE ' . $nodel . '(topSection IN (' . $secid . ')';
		if($iterations==0) $sSQL2 .= ' OR (sectionID IN (' . $secid . ') AND rootSection=1))'; else $sSQL2 .= ')';
		$secid = '';
		$iteratemore = FALSE;
		$result2 = mysql_query($sSQL2) or print(mysql_error());
		$addcomma = '';
		while($rs2 = mysql_fetch_assoc($result2)){
			if($rs2['rootSection']==0){
				$iteratemore = TRUE;
				$secid .= $addcomma . $rs2['sectionID'];
				$addcomma = ',';
			}else{
				$resolvedids .= $addcomma2 . $rs2['sectionID'];
				$addcomma2 = ',';
			}
		}
		$iterations++;
	}
	if($resolvedids=='') $resolvedids = '0';
	return($resolvedids);
}
function callcurlfunction($cfurl, $cfxml, &$cfres, $cfcert, &$cferrmsg, $settimeouts){
	global $curlproxy,$pathtocurl,$xmlfnheaders,$debugmode,$emailcr,$emailAddr,$htmlemails;
	$cfsuccess=TRUE;
	if(@$pathtocurl != ""){
		exec($pathtocurl . ($cfcert != '' ? ' -E \'' . $cfcert . '\'' : '') . ' --data-binary ' . escapeshellarg($cfxml) . ' ' . $cfurl, $cfres, $retvar);
		$cfres = implode("\n",$cfres);
	}else{
		if(!function_exists('curl_init')||!$ch = curl_init()) {
			$cferrmsg = 'cURL package not installed in PHP. Set \$pathtocurl parameter.';
			$cfsuccess=FALSE;
		}else{
			curl_setopt($ch, CURLOPT_URL, $cfurl);
			if(is_array($xmlfnheaders))curl_setopt($ch, CURLOPT_HTTPHEADER, $xmlfnheaders);
			if($cfcert!='') curl_setopt($ch, CURLOPT_SSLCERT, $cfcert); 
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $cfxml);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if($settimeouts){
				if($settimeouts>10)curl_setopt($ch, CURLOPT_TIMEOUT, $settimeouts); else curl_setopt($ch, CURLOPT_TIMEOUT, 120);
			}
			if(@$curlproxy!='')
				curl_setopt($ch, CURLOPT_PROXY, $curlproxy);
			$cfres = curl_exec($ch);
			if(curl_error($ch) != ""){
				if($cfcert != '' && ! is_file($cfcert)){
					$cferrmsg='Certificate file not found: ' . $cfcert . '<br />';
				}else
					$cferrmsg=curl_error($ch) . '<br />';
				$cfsuccess=FALSE;
			}else
				curl_close($ch);
		}
	}
	if($debugmode){ $htmlemails=FALSE; dosendemail($emailAddr, $emailAddr, '', 'PHP XML Function Debug', $cfxml . $emailcr . $emailcr . $cfres . $emailcr . $emailcr . $cfsuccess); }
	return($cfsuccess);
}
function getpayprovdetails($ppid,&$ppdata1,&$ppdata2,&$ppdata3,&$ppdemo,&$ppmethod){
	$sSQL = "SELECT payProvData1,payProvData2,payProvData3,payProvDemo,payProvMethod FROM payprovider WHERE payProvEnabled=1 AND payProvID='" . escape_string($ppid) . "'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		$ppdata1 = trim($rs['payProvData1']);
		$ppdata2 = trim($rs['payProvData2']);
		$ppdata3 = trim($rs['payProvData3']);
		$ppdemo = ((int)$rs['payProvDemo']==1);
		$ppmethod = (int)$rs['payProvMethod'];
	}else
		return(FALSE);
	return(TRUE);
}
function writehiddenvar($hvname,$hvval){
print '<input type="hidden" name="' . $hvname . '" value="' . htmlspecials($hvval) . '" />' . "\r\n";
}
function writehiddenidvar($hvname,$hvval){
print '<input type="hidden" name="' . $hvname . '" id="' . $hvname . '" value="' . htmlspecials($hvval) . '" />' . "\r\n";
}
function ppsoapheader($username, $password, $signature){
return '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Header><RequesterCredentials xmlns="urn:ebay:api:PayPalAPI"><Credentials xmlns="urn:ebay:apis:eBLBaseComponents">' . (strpos($username,'@AB@')===FALSE ? '<Username>' . $username . '</Username><Password>' . $password . '</Password>' . ($signature != '' ? '<Signature>' . $signature . '</Signature>' : '') : '<Subject>'.str_replace('@AB@','',$username).'</Subject>') . '</Credentials></RequesterCredentials></soap:Header>';
}
function getoptpricediff($opd,$theid,$theexp,$pprice,&$pstock){
	global $WSP;
	$retval = (double)$opd;
	if($theexp!='' && substr($theexp, 0, 1)!='!'){
		$theexp = str_replace('%s', $theid, $theexp);
		if(strpos($theexp, ' ')!==FALSE){ // Search and replace
			$exparr = explode(' ', $theexp, 2);
			$theid = str_replace($exparr[0], $exparr[1], $theid);
		}else
			$theid = $theexp;
		$sSQL = 'SELECT '.$WSP."pPrice,pInStock FROM products WHERE pID='".escape_string($theid)."'";
		$result3 = mysql_query($sSQL) or print(mysql_error());
		if($rs3=mysql_fetch_assoc($result3)){ $retval = $rs3['pPrice']-$pprice; $pstock=$rs3['pInStock']; }
		mysql_free_result($result3);
	}
	return($retval);
}
function addtoaltids($theexp, &$altidarr, &$altids){
	$theexp = trim($theexp);
	if($theexp!='' && substr($theexp, 0, 1)!='!'){
		if(! is_array($altidarr)){
			$altidarr = explode(' ', trim($altids));
			$altids = '';
		}
		foreach($altidarr as $theid){
			$theexpa = str_replace('%s', $theid, $theexp);
			if(strpos($theexpa, ' ')!==FALSE){ // Search and replace
				$exparr = explode(' ', $theexpa, 2);
				$theid = str_replace($exparr[0], $exparr[1], $theid);
			}else
				$theid = $theexpa;
			$altids .= $theid . ' ';
		}
	}
}
$optjsunique=',';
function addtooptionsjs(&$optionsjs, $isdetail, $origoptpricediff){
	global $rs2,$useStockManagement,$optjsunique;
	if(strpos($optjsunique,','.$rs2['optID'].',')===FALSE){
		if($useStockManagement) $optionsjs .= 'oS['.$rs2['optID'].']='.$rs2['optStock'].';';
		if(($rs2['optRegExp']=='' || substr($rs2['optRegExp'],0,1)=='!') && $origoptpricediff!=0)$optionsjs .= 'op['.$rs2['optID'].']='.$origoptpricediff.';';
		if($rs2['optRegExp']!='' && substr($rs2['optRegExp'],0,1)!='!')$optionsjs .= 'or['.$rs2['optID']."]='".$rs2['optRegExp']."';";
		$optionsjs .= 'ot['.$rs2['optID']."]='".str_replace("'","\'",$rs2[getlangid('optName',32)])."';";
		if(trim($rs2['optAlt'.($isdetail?'Large':'').'Image'])!='') $optionsjs .= 'aIM['.$rs2['optID']."]='".$rs2['optAlt'.($isdetail?'Large':'').'Image']."';";
		$optionsjs .= "\r\n";
		$optjsunique.=$rs2['optID'].',';
	}
}
function displayproductoptions($grpnmstyle,$grpnmstyleend,&$optpricediff,$thetax,$isdetail,&$hasmulti,&$optionsjs){
	global $rs,$rs2,$prodoptions,$useStockManagement,$hideoptpricediffs,$pricezeromessage,$noprice,$WSP,$OWSP,$xxPlsSel,$Count,$optionshavestock,$xxOpSkTx,$noshowoptionsinstock,$showinstock,$showtaxinclusive,$defimagejs,$multipurchasecolumns,$xxSelOpt,$xxClkHere,$startlink,$endlink,$noselectoptionlabel,$optjsunique,$tleft,$tright;
	$optionshtml='';
	$optionsjs='';
	$altids=$rs['pId'];
	$optpricediff=0;
	$hasmulti=FALSE;
	$savedefimagejs=$defimagejs;
	$saveoptionsjs=$optionsjs;
	$saveoptjsunique=$optjsunique;
	$altidarr='';
	foreach($prodoptions as $rowcounter => $theopt){
		$opthasstock=false;
		$sSQL='SELECT optID,'.getlangid('optName',32).','.getlangid('optGrpName',16).',' . $OWSP . 'optPriceDiff,optType,optGrpSelect,optFlags,optTxtMaxLen,optTxtCharge,optStock,optPriceDiff AS optDims,optDefault,optAlt'.($isdetail?'Large':'').'Image,optRegExp FROM options LEFT JOIN optiongroup ON options.optGroup=optiongroup.optGrpID WHERE optGroup=' . $theopt['poOptionGroup'] . ' ORDER BY optID';
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs2=mysql_fetch_array($result)){
			if(abs((int)$rs2['optType'])==3){
				$opthasstock=true;
				$fieldHeight = round(((double)($rs2['optDims'])-(int)($rs2['optDims']))*100.0);
				$optionshtml .= '<tr><td align="' . $tright . '" width="30%" class="optiontext">' . $grpnmstyle . '<label for="optn'.$Count.'x'.$rowcounter.'">' . $rs2[getlangid('optGrpName',16)] . '</label>:' . $grpnmstyleend . '</td><td align="' . $tleft . '" class="options"> <input type="hidden" name="optn' . $rowcounter . '" value="' . $rs2["optID"] . '" />';
				if($fieldHeight != 1){
					$optionshtml .= '<textarea class="prodoption" name="voptn' . $rowcounter . '" id="optn'.$Count.'x'.$rowcounter.'" cols="' . (int)$rs2["optDims"] . '" rows="' . $fieldHeight . '">';
					$optionshtml .= $rs2[getlangid('optName',32)] . '</textarea>';
				}else
					$optionshtml .= '<input type="text" class="prodoption" maxlength="255" name="voptn' . $rowcounter . '" id="optn'.$Count.'x'.$rowcounter.'" size="' . $rs2['optDims'] . '" value="' . str_replace('"','&quot;',$rs2[getlangid('optName',32)]) . '" alt="' . $rs2[getlangid('optGrpName',16)] . '" />';
				$optionshtml .= '</td></tr>';
			}elseif(abs((int)$rs2['optType'])==1){
				$optionshtml .= '<tr><td align="' . $tright . '" valign="baseline" width="30%" class="optiontext">' . $grpnmstyle . $rs2[getlangid('optGrpName',16)] . ':' . $grpnmstyleend . '</td><td align="' . $tleft . '" class="options"> ';
				$index=0;
				do {
					$origoptpricediff = getoptpricediff($rs2['optPriceDiff'],$rs['pId'],trim($rs2['optRegExp']),$rs['pPrice'],$stocknotused);
					addtoaltids($rs2['optRegExp'], $altidarr, $altids);
					$optionshtml .= '<input type="'.(mysql_num_rows($result)==1?'checkbox':'radio').'" class="prodoption" style="vertical-align:middle" onclick="updateoptimage('.$Count.','.$rowcounter.',1);updateprice'.$Count.'();" name="optn'.$Count.'x'.$rowcounter.'" ';
					if((int)$rs2['optDefault']!=0) $optionshtml .= 'checked="checked" ';
					$optionshtml .= 'value="' . $rs2['optID'] . '" /><span id="optn'.$Count.'x'.$rowcounter.'y'.$index.'"';
					if($useStockManagement && $rs['pStockByOpts']!=0 && $rs2['optStock']<=0 && trim($rs2['optRegExp'])=='') $optionshtml .= ' class="oostock" '; else $opthasstock=true;
					$optionshtml .= '>' . $rs2[getlangid('optName',32)];
					if(@$hideoptpricediffs != TRUE && $origoptpricediff!=0 && trim($rs2['optRegExp'])==''){
						$optionshtml .= ' (';
						if($origoptpricediff>0) $optionshtml .= '+';
						if(($rs2['optFlags']&1)==1)$pricediff = ($rs['pPrice']*$origoptpricediff)/100.0;else$pricediff = $origoptpricediff;
						if(@$showtaxinclusive===2 && ($rs['pExemptions'] & 2)!=2) $pricediff+=($pricediff*$thetax/100.0);
						$optionshtml .= FormatEuroCurrency($pricediff) . ')';
						if($rs2['optDefault']!=0) $optpricediff += $pricediff;
					}
					if($useStockManagement && @$showinstock==TRUE && @$noshowoptionsinstock != TRUE && (int)$rs["pStockByOpts"] != 0) $optionshtml .= str_replace('%s', $rs2['optStock'], $xxOpSkTx);
					$optionshtml .= '</span>';
					if(($rs2['optFlags'] & 4) != 4) $optionshtml .= "<br />\r\n";
					$index++;
					addtooptionsjs($optionsjs, $isdetail, $origoptpricediff);
				} while($rs2=mysql_fetch_assoc($result));
				unset($altidarr);
				$optionshtml .= '</td></tr>';
			}elseif(abs((int)$rs2['optType'])==4){
				if(@$multipurchasecolumns=='') $multipurchasecolumns=2;
				$colwid=(int)(100/$multipurchasecolumns);
				if((int)$rs2['optGrpSelect']!=0 && ! $isdetail){
					$hasmulti=2;
					$optionshtml='';
					$optionsjs='';
					$altids=$rs['pId'];
					$defimagejs=$savedefimagejs;
					$optionsjs=$saveoptionsjs;
					$optjsunique=$saveoptjsunique;
					$opthasstock=TRUE;
				}else{
					$optionshtml.='<tr><td align="center" colspan="2">&nbsp;<br /><table class="multioptiontable">';
					$index = 0;
					do {
						$stocklevel=$rs2['optStock'];
						$origoptpricediff = getoptpricediff($rs2['optPriceDiff'],$rs['pId'],trim($rs2['optRegExp']),$rs['pPrice'],$stocklevel);
						addtoaltids($rs2['optRegExp'], $altidarr, $altids);
						if($useStockManagement && $rs['pStockByOpts']!=0 && $stocklevel<=0 && trim($rs2['optRegExp'])=='') $oostock=TRUE; else $oostock=FALSE;
						if(($index % $multipurchasecolumns) == 0) $optionshtml .= '<tr>';
						$optionshtml .= '<td width="'.$colwid.'%" align="' . $tleft . '" class="optiontext multioptiontext" style="white-space:nowrap">';
						if(trim($rs2['optAlt'.($isdetail?'Large':'').'Image'])!='') $optionshtml .= '&nbsp;&nbsp;<img class="multiimage" src="'.trim($rs2['optAlt'.($isdetail?'Large':'').'Image']).'" alt="" />';
						$optionshtml .= '&nbsp;&nbsp;<input type="text" maxlength="5" name="optm'.$rs2['optID'].'" id="optm'.$Count.'x'.$rowcounter.'y'.$index.'" size="1" '.($oostock?'style="background-color:#EBEBE4" disabled="disabled"':'').'/>';
						$optionshtml .= '<label for="optm'.$Count.'x'.$rowcounter.'y'.$index.'"><span id="optx'.$Count.'x'.$rowcounter.'y'.$index.'" class="multioption';
						if($oostock) $optionshtml .= ' oostock"'; else{ $optionshtml .= '"'; $opthasstock=TRUE; }
						$optionshtml .= '> - ' . $rs2[getlangid('optName',32)];
						if(@$hideoptpricediffs != TRUE && $origoptpricediff!=0){
							$optionshtml .= ' (';
							if($origoptpricediff > 0) $optionshtml .= '+';
							if(($rs2['optFlags']&1)==1 && trim($rs2['optRegExp'])=='')$pricediff = ($rs['pPrice']*$origoptpricediff)/100.0;else $pricediff = $origoptpricediff;
							if(@$showtaxinclusive===2 && ($rs['pExemptions'] & 2)!=2) $pricediff+=($pricediff*$thetax/100.0);
							$optionshtml .= FormatEuroCurrency($pricediff) . ')';
						}
						$optionshtml .= '</span></label></td>';
						$index++;
						if(($index % $multipurchasecolumns) == 0) $optionshtml .= '</tr>';
						addtooptionsjs($optionsjs, $isdetail, $origoptpricediff);
					} while($rs2=mysql_fetch_assoc($result));
					if(($index % $multipurchasecolumns) != 0){
						while(($index % $multipurchasecolumns) != 0){
							if($index>=$multipurchasecolumns) $optionshtml .= '<td>&nbsp;</td>';
							$index++;
						}
						if(($index % $multipurchasecolumns) == 0) $optionshtml .= '</tr>';
					}
					$hasmulti = 1;
					$optionshtml.='</table></td></tr>';
				}
			}else{
				$optionshtml .= '<tr>' . (@$noselectoptionlabel!=TRUE ? '<td align="' . $tright . '" width="30%" class="optiontext">' . $grpnmstyle . '<label for="optn'.$Count.'x'.$rowcounter.'">' . $rs2[getlangid('optGrpName',16)] . '</label>:' . $grpnmstyleend . '</td><td align="' . $tleft . '" class="options"> ' : '<td colspan="2" class="prodoption selectoption">') . '<select class="prodoption" onchange="updateprice'.$Count.'();updateoptimage('.$Count.','.$rowcounter.');" name="optn' . $rowcounter . '" id="optn'.$Count.'x'.$rowcounter.'" size="1">';
				$defimagejs .= 'updateoptimage('.$Count.','.$rowcounter.',2);';
				$gotdefaultdiff = FALSE;
				$firstpricediff = 0;
				$origoptpricediff = $rs2['optPriceDiff'];
				if((int)$rs2['optGrpSelect']!=0)
					$optionshtml .= '<option value="">' . $xxPlsSel . '</option>';
				else
					if(($rs2['optFlags']&1)==1)$firstpricediff = ($rs['pPrice']*$origoptpricediff)/100.0;else $firstpricediff = $origoptpricediff;
				do {
					$origoptpricediff = getoptpricediff($rs2['optPriceDiff'],$rs['pId'],trim($rs2['optRegExp']),$rs['pPrice'],$stocknotused);
					addtoaltids($rs2['optRegExp'], $altidarr, $altids);
					$optionshtml .= '<option ';
					if($useStockManagement && $rs['pStockByOpts']!=0 && $rs2['optStock'] <= 0 && trim($rs2['optRegExp'])=='') $optionshtml .= 'class="oostock" '; else $opthasstock=true;
					$optionshtml .= 'value="' . $rs2['optID'] . '"'.((int)$rs2['optDefault']!=0?' selected="selected"':'').'>' . $rs2[getlangid('optName',32)];
					if(@$hideoptpricediffs!=TRUE && trim($rs2['optRegExp'])==''){
						if($origoptpricediff!=0){
							$optionshtml .= ' (';
							if($origoptpricediff>0) $optionshtml .= '+';
							if(($rs2['optFlags']&1)==1)$pricediff = ($rs['pPrice']*$origoptpricediff)/100.0;else $pricediff = $origoptpricediff;
							if(@$showtaxinclusive===2 && ($rs['pExemptions'] & 2)!=2) $pricediff+=($pricediff*$thetax/100.0);
							$optionshtml .= FormatEuroCurrency($pricediff) . ')';
							if($rs2['optDefault']!=0)$optpricediff += $pricediff;
						}
						if($rs2['optDefault']!=0)$gotdefaultdiff=TRUE;
					}
					if($useStockManagement && @$showinstock==TRUE && @$noshowoptionsinstock != TRUE && (int)$rs["pStockByOpts"] != 0) $optionshtml .= str_replace('%s', $rs2['optStock'], $xxOpSkTx);
					$optionshtml .= "</option>\n";
					addtooptionsjs($optionsjs, $isdetail, $origoptpricediff);
				} while($rs2=mysql_fetch_assoc($result));
				unset($altidarr);
				if(@$hideoptpricediffs != TRUE && ! $gotdefaultdiff) $optpricediff += $firstpricediff;
				$optionshtml .= '</select></td></tr>';
			}
		}
		mysql_free_result($result);
		$optionshavestock = ($optionshavestock && $opthasstock);
		if($hasmulti==2) break;
	}
	$sSQL = 'SELECT pID,'.$WSP."pPrice,pInStock FROM products WHERE pID IN ('" . str_replace(' ', "','", $altids) . "')";
	$result = mysql_query($sSQL) or print(mysql_error());
	while($rs2=mysql_fetch_assoc($result)){
		$sSQL = "SELECT imageSrc FROM productimages WHERE imageProduct='" . escape_string($rs2['pID']) . "' AND imageNumber=0 AND imageType=" . ($isdetail?'1':'0') . ' LIMIT 0,1';
		$result3 = mysql_query($sSQL) or print(mysql_error());
		if($rs3=mysql_fetch_assoc($result3)){
			$pi = str_replace('\\','/',$rs3['imageSrc']);
			if($pi=='prodimages/') $pi='';
			$pi = str_replace(array('|','<','>'), array('%7C','%3C','%3E'), $pi);
			$pi = str_replace(array('prodimages/','.gif','.jpg'), array('|','<','>'), $pi);
		}else
			$pi='';
		mysql_free_result($result3);
		$optionsjs .= "sz('".$rs2['pID']."'";
		$optionsjs .= ',' . $rs2['pPrice'];
		if($useStockManagement) $optionsjs .= ',' . $rs2['pInStock'];
		$optionsjs .= ",'" . str_replace("'", "\'", $pi) . "');";
	}
	mysql_free_result($result);
	if($hasmulti!=2) $defimagejs='updateprice'.$Count.'();'.$defimagejs;
	return($optionshtml);
}
function CalcHmacSha1($data, $key){
    $blocksize = 64;
    $hashfunc = 'sha1';
    if (strlen($key) > $blocksize){
        $key = pack('H*', $hashfunc($key));
    }
    $key = str_pad($key, $blocksize, chr(0x00));
    $ipad = str_repeat(chr(0x36), $blocksize);
    $opad = str_repeat(chr(0x5c), $blocksize);
    $hmac = pack('H*', $hashfunc(($key^$opad).pack('H*', $hashfunc(($key^$ipad).$data))));
    return $hmac;
}
function encodeemailsubject($in_str, $charset){
	$out_str = $in_str;
	if($out_str && $charset){
		// define start delimimter, end delimiter and spacer
		$end = "?=";
		$start = "=?" . $charset . "?B?";
		$spacer = $end . "\r\n " . $start;
		// determine length of encoded text within chunks and ensure length is even
		$length = 75 - strlen($start) - strlen($end);
		$length = floor($length/2) * 2;
		// encode the string and split it into chunks with spacers after each chunk
		$out_str = base64_encode($out_str);
		$out_str = chunk_split($out_str, $length, $spacer);
		// remove trailing spacer and add start and end delimiters
		$spacer = preg_quote($spacer);
		$out_str = preg_replace("/" . $spacer . "$/", "", $out_str);
		$out_str = $start . $out_str . $end;
	}
	return $out_str;
}
if(@$enableclientlogin==TRUE || @$forceclientlogin==TRUE){
	if(@$_SESSION['clientID'] != ''){
	}elseif(@$_POST['checktmplogin']!='' && @$_POST['sessionid'] != ''){
		$sSQL = "SELECT tmploginname FROM tmplogin WHERE tmploginid='" . escape_string(@$_POST['sessionid']) . "' AND tmploginchk='" . escape_string(@$_POST['checktmplogin']) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_array($result)){
			$_SESSION['clientID']=$rs['tmploginname'];
			mysql_free_result($result);
			$sSQL = "SELECT clUserName,clActions,clLoginLevel,clPercentDiscount FROM customerlogin WHERE clID='" . escape_string($_SESSION['clientID']) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_array($result)){
				$_SESSION['clientUser']=$rs['clUserName'];
				$_SESSION['clientActions']=$rs['clActions'];
				$_SESSION['clientLoginLevel']=$rs['clLoginLevel'];
				$_SESSION['clientPercentDiscount']=(100.0-(double)$rs['clPercentDiscount'])/100.0;
			}
		}
		mysql_free_result($result);
	}elseif(@$_COOKIE['WRITECLL'] != ''){
		$clientEmail = str_replace("'",'',@$_COOKIE['WRITECLL']);
		$clientPW = str_replace("'",'',@$_COOKIE['WRITECLP']);
		$sSQL = "SELECT clID,clUserName,clActions,clLoginLevel,clPercentDiscount FROM customerlogin WHERE (clEmail<>'' AND clEmail='" . escape_string($clientEmail) . "' AND clPW='" . escape_string($clientPW) . "') OR (clEmail='' AND clUserName='" . escape_string($clientEmail) . "' AND clPW='" . escape_string($clientPW) . "')";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_array($result)){
			$_SESSION['clientID']=$rs['clID'];
			$_SESSION['clientUser']=$rs['clUserName'];
			$_SESSION['clientActions']=$rs['clActions'];
			$_SESSION['clientLoginLevel']=$rs['clLoginLevel'];
			$_SESSION['clientPercentDiscount']=(100.0-(double)$rs['clPercentDiscount'])/100.0;
		}
		mysql_free_result($result);
	}
	if(@$requiredloginlevel != ''){
		if((int)$requiredloginlevel > @$_SESSION['clientLoginLevel']){
			ob_end_clean();
			if(@$_SERVER['HTTPS'] == 'on' || @$_SERVER['SERVER_PORT'] == '443')$prot='https://';else $prot='http://';
			header('Location: '.$prot.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/cart.php?mode=login&refurl=' . urlencode(@$_SERVER['PHP_SELF'] . (@$_SERVER['QUERY_STRING'] !='' ? '?' . @$_SERVER['QUERY_STRING'] : '')));
			exit;
		}
	}
	if((@$_SESSION['clientActions'] & 2)==2) $showtaxinclusive=FALSE;
}
function getsessionsql(){
	global $thesessionid;
	return(@$_SESSION['clientID'] != '' ? 'cartClientID=' . escape_string($_SESSION['clientID']) : "(cartClientID=0 AND cartSessionID='" . escape_string($thesessionid) . "')");
}
function getordersessionsql(){
	global $thesessionid;
	return("ordDate>'" . date('Y-m-d', time()-(2*60*60*24)) . "' AND ".(@$_SESSION['clientID'] != '' ? 'ordClientID=' . escape_string($_SESSION['clientID']) : "(ordClientID=0 AND ordSessionID='" . escape_string($thesessionid) . "')"));
}
function trimoldcartitems($cartitemsdel){
	global $dateadjust;
	if(@$dateadjust=='') $dateadjust=0;
	$thetocdate = time() + ($dateadjust*60*60);
	$sSQL = "SELECT adminDelUncompleted,adminClearCart FROM admin WHERE adminID=1";
	$result = mysql_query($sSQL) or print(mysql_error());
	$rs = mysql_fetch_assoc($result);
	$delAfter=$rs['adminDelUncompleted'];
	$delSavedCartAfter=$rs['adminClearCart'];
	mysql_free_result($result);
	if($delAfter != 0){
		$sSQL = "SELECT ordID FROM orders WHERE ordAuthNumber='' AND ordDate<'" . date("Y-m-d H:i:s", $thetocdate-($delAfter*60*60*24)) . "' AND ordStatus=2";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_assoc($result)){
			release_stock($rs['ordID']);
			mysql_query("UPDATE cart SET cartOrderID=0 WHERE cartOrderID=".$rs['ordID']);
			mysql_query("DELETE FROM orders WHERE ordID=".$rs['ordID']);
		}
		mysql_free_result($result);
	}
	$sSQL = 'SELECT cartID,listOwner FROM cart LEFT JOIN customerlists ON cart.cartListID=customerlists.listID WHERE cartCompleted=0 AND cartOrderID=0 AND ';
	$sSQL .= "((cartClientID=0 AND cartDateAdded<'" . date("Y-m-d H:i:s", $cartitemsdel) . "') ";
	if($delSavedCartAfter != 0) $sSQL .= "OR (cartDateAdded<'" . date("Y-m-d H:i:s", $thetocdate-($delSavedCartAfter*60*60*24)) . "') ";
	$sSQL .= ')';
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result) > 0){
		$delOptions=$addcomma='';
		while($rs = mysql_fetch_assoc($result)){
			if(! is_null($rs['listOwner'])){
				mysql_query("UPDATE cart SET cartCompleted=3,cartClientID=" . $rs['listOwner'] . " WHERE cartID=" . $rs['cartID']) or print(mysql_error());
			}else{
				$delOptions .= $addcomma . $rs['cartID'];
				$addcomma = ',';
			}
		}
		if($delOptions!='') mysql_query("DELETE FROM cartoptions WHERE coCartID IN (" . $delOptions . ')');
		if($delOptions!='') mysql_query("DELETE FROM cart WHERE cartID IN (" . $delOptions . ')');
	}
	mysql_free_result($result);
}
function htmlspecials($thestr){
	return(str_replace(array('&','>','<','"'), array('&amp;','&gt;','&lt;','&quot;'), $thestr));
}
function htmlspecialsid($thestr){
	return(str_replace(array('&','>','<','"',"'"), '', $thestr));
}
function jsspecials($thestr){
	return(str_replace(array('\\','\''),array('\\\\','\\\''), htmlspecials($thestr)));
}
function addtomailinglist($theemail,$thename){
	global $storeurl,$emailAddr,$xxMLConf,$xxConfEm,$emailencoding,$noconfirmationemail,$htmlemails,$uspsUser,$upsUser,$origZip,$checksumtext,$warncheckspamfolder;
	$theemail=trim(strtolower(strip_tags(str_replace('"','',$theemail))));
	if(strpos($theemail, '@')!==FALSE && strpos($theemail, '.')!==FALSE && strlen($theemail)>5){
		$sSQL = "SELECT email,isconfirmed FROM mailinglist WHERE email='" . escape_string($theemail) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_assoc($result)){
			$emailexists = TRUE;
			$isconfirmed = $rs['isconfirmed'];
		}else
			$emailexists = $isconfirmed = FALSE;
		if(! $emailexists) mysql_query("INSERT INTO mailinglist (email,mlName,isconfirmed,mlConfirmDate,mlIPAddress) VALUES ('" . escape_string($theemail) . "','" . escape_string($thename) . "'," . (@$noconfirmationemail?1:0) . ",'".date('Y-m-d', time())."','".escape_string(getipaddress())."')");
		if(! $isconfirmed && ! @$noconfirmationemail){
			$warncheckspamfolder=TRUE;
			if(@$htmlemails==TRUE) $emlNl = '<br />'; else $emlNl="\r\n";
			$thelink = $storeurl . 'cart.php?emailconf='.urlencode($theemail).'&check='.substr(md5($uspsUser.$upsUser.$origZip.@$checksumtext.':'.$theemail), 0, 10);
			if(@$htmlemails==TRUE) $thelink = '<a href="' . $thelink . '">' . $thelink . '</a>';
			dosendemail($theemail, $emailAddr, '', $xxMLConf, $xxConfEm . $emlNl . $emlNl . $thelink);
		}
	}
}
function getipaddress(){
	if(trim(@$_SERVER['HTTP_X_FORWARDED_FOR'])!=''){
		$ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		$ip = explode(':', $ip[0]);
		return($ip[0]);
	}else
		return(@$_SERVER['REMOTE_ADDR']);
}
function escape_string($estr){
	if(version_compare(phpversion(),'4.3.0')=='-1') return(mysql_escape_string(trim($estr))); else return(mysql_real_escape_string(trim($estr)));
}
function imageorlink($theimg, $thetext, $thelink, $isjs){
	if($theimg!='')
		return '<img border="0" src="'.$theimg.'" onmouseover="this.style.cursor=\'pointer\';window.status=\''.str_replace("'","\'",$thetext).'\';return true" onmouseout="window.status=\'\';return true" onclick="'.($isjs ? '' : "document.location='") . $thelink . ($isjs ? '' : "'").'" alt="'.$thetext.'" />';
	else
		return '<a class="ectlink" href="'.($isjs ? '#" onclick="' : '') . $thelink . '" onmouseover="window.status=\''.str_replace("'","\'",$thetext).'\';return true" onmouseout="window.status=\'\';return true"><strong>'.$thetext.'</strong></a>';
}
function imageorbutton($theimg,$thetext,$theclass,$thelink, $isjs){
	if($theimg!='' && $theimg!='button')
		return '<img border="0" src="'.$theimg.'" '.($theclass!=''?'class="'.$theclass.'" ':'').'onmouseover="this.style.cursor=\'pointer\';window.status=\''.str_replace("'","\'",$thetext).'\';return true" onmouseout="window.status=\'\';return true" onclick="'.($isjs ? '' : "document.location='") . $thelink . ($isjs ? '' : "'").'" alt="'.$thetext.'" />';
	else
		return '<input type="button" value="'.$thetext.'" '.($theclass!=''?'class="'.$theclass.'" ':'').'onclick="'.($isjs ? '' : "document.location='") . $thelink . ($isjs ? '' : "'").'" />';
}
function imageorsubmit($theimg,$thetext,$theclass){
	if($theimg!='' && $theimg!='button')
		return '<input type="image" src="'.$theimg.'" alt="'.$thetext.'" '.($theclass!=''?'class="'.$theclass.'" ':'').'/>';
	else
		return '<input type="submit" value="'.$thetext.'" '.($theclass!=''?'class="'.$theclass.'" ':'').'/>';
}
function dosendemail($doseto, $dosefrom, $dosereplyto, $dosesubject, $dosebody){
	global $customheaders,$emailencoding,$htmlemails,$debugmode,$usemailer,$smtphost,$smtpusername,$smtppassword,$smtpport,$smtpsecure,$emailflags,$emailcr;
	if(@$usemailer=='phpmailer'){
		if(file_exists('./vsadmin/inc/class.phpmailer.php')) $issiteroot=TRUE; else $issiteroot=FALSE;
		if($issiteroot)
			include_once('./vsadmin/inc/class.phpmailer.php');
		else
			include_once('./inc/class.phpmailer.php');
		$mail = new PHPMailer();
		if($issiteroot) $mail->SetLanguage('en', './vsadmin/inc/'); else $mail->SetLanguage('en', './inc/');
		$mail->IsSMTP();
		if(@$debugmode) $mail->SMTPDebug = 2;
		$mail->Host = $smtphost;
		$mail->SMTPAuth = (@$smtpusername!='' && @$smtppassword!='');
		if(@$smtpusername!='' && @$smtppassword!=''){
			$mail->Username = $smtpusername;
			$mail->Password = $smtppassword;
		}
		if(@$smtpport!='') $mail->Port=$smtpport;
		if(@$smtpsecure!='') $mail->SMTPSecure=$smtpsecure;
		$mail->From = $dosefrom;
		$mail->FromName = $dosefrom;
		$mail->AddAddress($doseto);
		if($dosereplyto!='') $mail->AddReplyTo($dosereplyto);
		// $mail->WordWrap = 50;
		$mail->IsHTML(@$htmlemails==TRUE);
		$mail->Subject = $dosesubject;
		$mail->Body    = $dosebody;
		// $mail->AltBody = "Plain Text";
		if(!$mail->Send() && @$debugmode) echo 'Failed to send mail: ' . $mail->ErrorInfo;
	}else{
		if(@$customheaders==''){
			$headers = "MIME-Version: 1.0".$emailcr;
			$headers .= "From: %from% <%from%>".$emailcr;
			if($dosereplyto!='') $headers .= "Reply-To: %replyto% <%replyto%>".$emailcr;
			if(@$htmlemails==TRUE)
				$headers .= 'Content-type: text/html; charset='.$emailencoding.$emailcr;
			else
				$headers .= 'Content-type: text/plain; charset='.$emailencoding.$emailcr;
		}else{
			$headers = $customheaders;
			if($dosereplyto==''){
				if(($startpos=strpos(strtolower($headers), 'reply-to'))!==FALSE){
					if(($endpos=strpos($headers,"\n",$startpos+1))!==FALSE){
						$headers=substr_replace($headers,'',$startpos,($endpos-$startpos)+1);
					}
				}
			}
		}
		$headers = str_replace('%from%',$dosefrom,$headers);
		$headers = str_replace('%to%',$doseto,$headers);
		$headers = str_replace('%replyto%',$dosereplyto,$headers);
		$emailflags=str_replace('%from%',$dosefrom,@$emailflags);
		if($emailflags!=''){
			if(@$debugmode==TRUE)
				mail($doseto, $dosesubject, $dosebody, $headers, $emailflags);
			else
				@mail($doseto, $dosesubject, $dosebody, $headers, $emailflags);
		}else{
			if(@$debugmode==TRUE)
				mail($doseto, $dosesubject, $dosebody, $headers);
			else
				@mail($doseto, $dosesubject, $dosebody, $headers);
		}
	}
}
function getgcchar(){
	$tc='';
	while($tc=='' || $tc=='O' || $tc=='I' || $tc=='Q')
		$tc = chr(rand(65, 90));
	return($tc);
}
function getrndchar(){
	return(chr(rand(65, 90)));
}
function replaceemailtxt($thestr, $txtsearch, $txtreplace){
	if($thestr=='') $i=FALSE; else $i = strpos($thestr, $txtsearch);
	if($i!==FALSE){
		$lenstr = strlen($thestr);
		$revstr = strrev($thestr);
		$thepos = strpos($revstr, '{', $lenstr - $i);
		if($thepos===FALSE) $t1=FALSE; else $t1 = strlen($thestr) - $thepos;
		$thepos = strpos($revstr, '}', $lenstr - $i);
		if($thepos===FALSE) $t2=FALSE; else $t2 = strlen($thestr) - $thepos;
		$t3 = strpos($thestr, '{', $i);
		$t4 = strpos($thestr, '}', $i);
	}
	if($i===FALSE){
		return($thestr);
	}elseif($txtreplace==''){ // want to replace all of txtsearch OR {...txtsearch...}
		if($t1!==FALSE && $t4!==FALSE && ($t2===FALSE || $t2 < $t1) && ($t3===FALSE || $t3 > $t4)) return(substr($thestr, 0, $t1-1) . substr($thestr, $t4+1)); else return(str_replace($txtsearch, '', $thestr));
	}else{ // Want to remove the { AND }
		if($t1!==FALSE && $t4!==FALSE && ($t2===FALSE || $t2 < $t1) && ($t3===FALSE || $t3 > $t4)) $thestr = substr($thestr, 0, $t1-1) . substr($thestr, $t1, $t4-$t1) . substr($thestr, $t4+1);
		return(str_replace($txtsearch, $txtreplace, $thestr));
	}
}
function showproductreviews($disptype, $classname){
	global $rs,$xxBasRat,$xxView,$thedetailslink;
	$spr = '<div class="'.$classname.'"><a href="'.$thedetailslink.'#reviews">';
	$therating = (int)($rs['pTotRating']/$rs['pNumRatings']);
	for($index=1; $index <= (int)($therating / 2); $index++){
		$spr .= '<img class="'.$classname.'" src="images/sreviewcart.gif" alt="" style="vertical-align:middle;margin:0px;border:0px;" />';
	}
	$ratingover = $therating;
	if($ratingover / 2 > (int)($ratingover / 2)){
		$spr .= '<img class="'.$classname.'" src="images/sreviewcarthg.gif" alt="" style="vertical-align:middle;margin:0px;border:0px;" />';
		$ratingover++;
	}
	for($index=(int)($ratingover / 2) + 1; $index <= 5; $index++){
		$spr .= '<img class="'.$classname.'" src="images/sreviewcartg.gif" alt="" style="vertical-align:middle;margin:0px;border:0px;" />';
	}
	$spr .= '</a>';
	if($disptype==2) $spr .= ' <a class="ectlink" href="'.$thedetailslink.'#reviews">' . str_replace('%s', $rs['pNumRatings'], $xxBasRat) . '</a>'; elseif($disptype==1) $spr .= ' ' . str_replace('%s', $rs['pNumRatings'], $xxBasRat) . ' (<a class="ectlink" href="' . $thedetailslink . '#reviews">' . $xxView . '</a>)';
	return($spr . '</div>');
}
function splitfirstlastname($thename,&$firstfull,&$lastname){
global $usefirstlastname;
	if(@$usefirstlastname&&strpos($thename, ' ')!==FALSE){
		$namearr = explode(' ',$thename,2);
		$firstfull = $namearr[0];
		$lastname = $namearr[1];
	}else{
		$firstfull=$thename;
		$lastname='';
	}
}
function getcatid($sid,$snam){
	global $usecategoryname;
	if(@$usecategoryname && $snam!='') return(urlencode($snam)); else return($sid);
}
function cleanupemail($theemail){
	$theemail = str_replace(array('"',' ',"'",'(',')'),'',$theemail);
	$theemail = strip_tags($theemail);
	$gotat=FALSE;
	$tmpstr='';
	for($i=0; $i < strlen($theemail); $i++){
		$ch=substr($theemail,$i,1);
		if(!($ch=='@'&&$gotat)) $tmpstr .= substr($theemail,$i,1);
		if($ch=='@')$gotat=TRUE;
	}
	return($tmpstr);
}
function get_wholesaleprice_sql(){
	global $WSP,$OWSP,$TWSP,$wholesaleoptionpricediff,$nowholesalediscounts,$nodiscounts;
	if(@$_SESSION['clientUser']!=''){
		if(($_SESSION['clientActions'] & 8)==8){
			$WSP = 'pWholesalePrice AS ';
			$TWSP = 'pWholesalePrice';
			if(@$wholesaleoptionpricediff==TRUE) $OWSP = 'optWholesalePriceDiff AS ';
			if(@$nowholesalediscounts==TRUE) $nodiscounts=TRUE;
		}
		if(($_SESSION['clientActions'] & 16)==16){
			$WSP = $_SESSION['clientPercentDiscount'] . '*'.(($_SESSION['clientActions'] & 8)==8?'pWholesalePrice':'pPrice').' AS ';
			$TWSP = $_SESSION['clientPercentDiscount'] . '*'.(($_SESSION['clientActions'] & 8)==8?'pWholesalePrice':'pPrice');
			if(@$wholesaleoptionpricediff==TRUE) $OWSP = $_SESSION['clientPercentDiscount'] . '*'.(($_SESSION['clientActions'] & 8)==8?'optWholesalePriceDiff':'optPriceDiff').' AS ';
			if(@$nowholesalediscounts==TRUE) $nodiscounts=TRUE;
		}
	}
}
function writepagebar($CurPage,$iNumPages,$sprev,$snext,$sLink,$nofirstpage){
	$startPage = max(1,round(floor((double)$CurPage/10.0)*10));
	$endPage = min($iNumPages,round(floor((double)$CurPage/10.0)*10)+10);
	if($CurPage > 1)
		$sStr = $sLink . '1"><span style="font-family:Verdana;font-weight:bold">&laquo;</span></a> ' . $sLink . ($CurPage-1) . '"'.($CurPage>2?' rel="prev"':'').'>'.$sprev.'</a> | ';
	else
		$sStr = '<span style="font-family:Verdana;font-weight:bold">&laquo;</span> '.$sprev.' | ';
	for($i=$startPage;$i <= $endPage; $i++){
		if($i==$CurPage)
			$sStr .= '<span class="currpage">' . $i . '</span> | ';
		else{
			$sStr .= $sLink . $i . '">';
			if($i==$startPage && $i > 1) $sStr .= '...';
			$sStr .= $i;
			if($i==$endPage && $i < $iNumPages) $sStr .= '...';
			$sStr .= '</a> | ';
		}
	}
	if($CurPage < $iNumPages)
		$sStr .= $sLink . ($CurPage+1) . '" rel="next">'.$snext.'</a> ' . $sLink . $iNumPages . '"><span style="font-family:Verdana;font-weight:bold">&raquo;</span></a>';
	else
		$sStr .= ' '.$snext.' <span style="font-family:Verdana;font-weight:bold">&raquo;</span>';
	if($nofirstpage) $sStr = str_replace(array('&amp;pg=1"','?pg=1"'),'" rel="start"',$sStr);
	return($sStr);
}
function addtag($tagname, $strValue){
	return('<' . $tagname . '>' . str_replace('<', '&lt;', str_replace('&', '&amp;', $strValue)) . '</' . $tagname . '>');
}
function whv($hvname,$hvval){
return('<input type="hidden" name="' . $hvname . '" value="' . htmlspecials($hvval) . '" />' . "\r\n");
}
function getsessionid(){
	global $persistentcart;
	if(is_numeric(@$persistentcart)&&(int)@$persistentcart>0){
		if(@$_COOKIE['ectcartcookie']!=''){
			return(str_replace("'",'',$_COOKIE['ectcartcookie']));
		}else{
			$gotunique=FALSE;
			while(! $gotunique){
				$sequence = substr(md5(uniqid('',TRUE).session_id()),0,26);
				$sSQL = "SELECT cartSessionID FROM cart WHERE cartSessionID='" . $sequence . "'";
				$result = mysql_query($sSQL) or print(mysql_error());
				if(mysql_num_rows($result)==0) $gotunique = TRUE;
				mysql_free_result($result);
			}
			setcookie('ectcartcookie', $sequence, time()+($persistentcart*60*60*24), '/', '', @$_SERVER['HTTPS']=='on');
			return($sequence);
		}
	}else
		return(session_id());
}
function dohashpw($thepw){
	if(trim($thepw)=='') return(''); else return(md5('ECT IS BEST'.trim($thepw)));
}
function logevent($userid,$eventtype,$eventsuccess,$eventorigin,$areaaffected){
	global $nopadsscompliance;
	if(@$nopadsscompliance!=TRUE){
		$sSQL = "SELECT logID FROM auditlog WHERE eventType='STARTLOG'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result)==0){
			$sSQL = 'INSERT INTO auditlog (userID,eventType,eventDate,eventSuccess,eventOrigin,areaAffected) VALUES (' .
				"'" . escape_string(substr($userid,0,48)) . "','STARTLOG','" . date('Y-m-d H:i:s') . "',1," .
				"'" . escape_string(substr($eventorigin,0,48)) . "','" . escape_string(substr($areaaffected,0,48)) . "')";
			mysql_query($sSQL) or print(mysql_error());
		}
		mysql_free_result($result);
		$sSQL = 'INSERT INTO auditlog (userID,eventType,eventDate,eventSuccess,eventOrigin,areaAffected) VALUES (' .
			"'" . escape_string(substr($userid,0,48)) . "','" . escape_string(substr($eventtype,0,48)) . "'," .
			"'" . date('Y-m-d H:i:s') . "'," . ($eventsuccess?1:0) . "," .
			"'" . escape_string(substr($eventorigin,0,48)) . "','" . escape_string(substr($areaaffected,0,48)) . "')";
		mysql_query($sSQL) or print(mysql_error());
		mysql_query("DELETE FROM auditlog WHERE eventDate<'" . date('Y-m-d H:i:s',time()-60*60*24*365) . "'") or print(mysql_error());
	}
}
function splitname($thename, &$firstname, &$lastname){
	if(strstr($thename,' ')){
		list($firstname,$lastname)=explode(' ',$thename,2);
	}else{
		$firstname = '';
		$lastname = $thename;
	}
}
function updaterchecker(){
	global $yyNoNew,$yyLasChk,$yyChkMan,$yyClkHer,$yyNewRec,$yyRUSec,$yyChkNew,$disableupdatechecker,$nopadsscompliance;
	$sSQL = 'SELECT adminVersion,updLastCheck,updRecommended,updSecurity,updShouldUpd,adminStoreURL FROM admin WHERE adminID=1';
	$result = mysql_query($sSQL) or print(mysql_error());
	$rs = mysql_fetch_assoc($result);
	$storeVersion = $rs['adminVersion'];
	$updLastCheck = $rs['updLastCheck'];
	$recommendedversion = $rs['updRecommended'];
	$securityrelease = $rs['updSecurity'];
	$shouldupdate = $rs['updShouldUpd'];
	$storeURL = $rs['adminStoreURL'];
	mysql_free_result($result);
	if(@$disableupdatechecker){
		$checkupdates=FALSE;
	}else{
		$checkupdates = (time()-strtotime($updLastCheck))>=(3*60*60*24);

		$admindatestr='Y-m-d';
		if(@$admindateformat=='') $admindateformat=0;
		if($admindateformat==1)
			$admindatestr='m/d/Y';
		elseif($admindateformat==2)
			$admindatestr='d/m/Y';
?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function ajaxcallback() {
	if(ajaxobj.readyState==4){
		var newtxt='';
		var xmlDoc=ajaxobj.responseXML.documentElement;
		var recver = xmlDoc.getElementsByTagName("recommendedversion")[0].childNodes[0].nodeValue;
		var shouldupdate = (xmlDoc.getElementsByTagName("shouldupdate")[0].childNodes[0].nodeValue=='true');
		var securityupdate = (xmlDoc.getElementsByTagName("securityupdate")[0].childNodes[0].nodeValue=='true');
		var haserror = (xmlDoc.getElementsByTagName("haserror")[0].childNodes[0].nodeValue=='true');
		if(haserror){
			newtxt = '<span style="color:#FF0000;font-weight:bold">' + recver + '!</span><br /><?php print str_replace("'","\'",$yyChkMan)?> <a href="http://www.ecommercetemplates.com/updaters.asp" target="_blank"><?php print str_replace("'","\'",$yyClkHer)?></a><br />';
			newtxt += 'To disable this function please <a href="http://www.ecommercetemplates.com/phphelp/ecommplus/parameters.asp#dissupcheck" target="_blank"><?php print str_replace("'","\'",$yyClkHer)?></a><br />';
		}else{
			if(shouldupdate) newtxt = '<a href="http://www.ecommercetemplates.com/updaters.asp" target="_blank"><?php print str_replace("'","\'",$yyNewRec)?>: v' + recver + '</a><br />';
			if(securityupdate) newtxt += '<span style="color:#FF0000;font-weight:bold"><?php print str_replace("'","\'",$yyRUSec)?></span><br />';
		}
		document.getElementById("checkupdates").innerHTML=(shouldupdate?newtxt:'<?php print str_replace("'","\'",$yyNoNew)?>');
	}
}
function checkforupdates(){
	if(window.XMLHttpRequest)
		ajaxobj = new XMLHttpRequest();
	else
		ajaxobj = new ActiveXObject("MSXML2.XMLHTTP");
	ajaxobj.onreadystatechange = ajaxcallback;
	ajaxobj.open("GET", "ajaxservice.php?action=checkupdates&storever=<?php print urlencode($storeVersion)?>", true);
	ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxobj.send(null);
}
<?php if($checkupdates) print "checkforupdates();\r\n";
?>/* ]]> */
</script>
<?php
	} ?><div class="admin_menu_text">
<div style="font-size:11px;border:1px;padding:2px;font-weight:bold"><?php print str_replace(' PHP','<br />PHP', $storeVersion)?></div>
<span id="checkupdates" style="font-size:10px;border:1px;"><?php
	if(@$disableupdatechecker)
		print '<span style="color:#FF0000;font-weight:bold">Auto update feature disabled!</span><br />' . $yyChkMan . ' <a href="http://www.ecommercetemplates.com/updaters.asp" target="_blank">' . $yyClkHer . '</a><br />';
	elseif($checkupdates)
		print $yyChkNew . '...';
	else{
		if($shouldupdate){
			print '<div style="padding:1px"><a href="http://www.ecommercetemplates.com/updaters.asp" target="_blank">' . $yyNewRec . ': v' . $recommendedversion . '</a></div>';
			if($securityrelease) print '<div style="color:#FF0000;padding:1px;">' . $yyRUSec . '</div>';
		}else{
			print $yyNoNew . '<div style="padding:1px;">'.$yyLasChk.':<br /><span style="font-weight:bold"><a href="javascript:checkforupdates()">' . date($admindatestr, strtotime($updLastCheck)) . '</a></span></div>';
		}
	} ?></span>
</div>
<?php
	if(@$nopadsscompliance!=TRUE){ ?>
<script type="text/javascript">
/* <![CDATA[ */
setTimeout("document.location='logout.php';",900000);
/* ]]> */
</script>
<?php
	}
}
if(@$_SESSION['httpreferer']=='' && @$_SERVER['HTTP_REFERER']!=''){
	$httpreferer = substr($_SERVER['HTTP_REFERER'], 0, 255);	
	if(strlen($httpreferer)>=255){
		$andpos = strrpos($httpreferer, '&');
		if($andpos > 0) $httpreferer = substr($httpreferer, 0, $andpos);
	}
	$_SESSION['httpreferer']=$httpreferer;
}
?>