<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
$prodoptions='';
$nooptionshtml='';
$extraimages=0;
$optionshtml='';
$hasmultipurchase=0;
$totprice=0;
$optjs='';
// id,name,discounts,listprice,price,priceinctax,options,quantity,currency,instock,rating,buy
if(@$cpdcolumns=='') $cpdcolumns='id,name,discounts,listprice,price,priceinctax,instock,quantity,buy';
$cpdarray=explode(',',strtolower($cpdcolumns));
$noproductoptions=TRUE;
$savetaxinclusive=@$showtaxinclusive;
$showtaxinclusive=FALSE;
$hascurrency=FALSE;
$noupdateprice=TRUE;
if(@$currencyseparator=='') $currencyseparator=' ';
if(@$_SESSION['clientID']=='' || @$enablewishlists==FALSE || @$wishlistonproducts=='') $wishlistonproducts=FALSE;
if(@$overridecurrency!=TRUE || @$orcdecimals=='') $orcdecimals='.';
if(@$overridecurrency!=TRUE || @$orcthousands=='') $orcthousands=',';
function docallupdatepricescript(){
	global $noproductoptions,$hasmultipurchase,$optionshtml,$prodoptions,$sstrong,$estrong,$optdiff,$thetax,$rs,$updatepricecalled,$giftcertificateid,$donationid,$optjs;
	updatepricescript($noproductoptions != TRUE,$thetax,FALSE);
	$hasmultipurchase = 0;
	$optionshtml = '';
	if(is_array($prodoptions)){
		if($noproductoptions==TRUE){
			$hasmultipurchase=2;
		}else{
			$optionshtml = displayproductoptions($sstrong . '<span class="prodoption">','</span>' . $estrong,$optdiff,$thetax,FALSE,$hasmultipurchase,$optjs);
			$rs['pPrice'] += $optdiff;
		}
	}
	if($rs['pId']==$giftcertificateid || $rs['pId']==$donationid) $hasmultipurchase=2;
	$updatepricecalled=TRUE;
}
foreach($cpdarray as $cpdindex => $cpdarrval){
	switch(trim($cpdarrval)){
		case 'options':
			$noproductoptions=FALSE;
		break;
		case 'price':
			$noupdateprice=FALSE;
		break;
		case 'priceinctax':
			$showtaxinclusive=$savetaxinclusive;
		break;
		case 'currency':
			$hascurrency=TRUE;
		break;
	}
}
if(! $hascurrency){$currSymbol1=''; $currSymbol2=''; $currSymbol3='';}
if(@$imgcheckoutbutton=='') $imgcheckoutbutton='images/checkout.gif';
productdisplayscript(@$noproductoptions!=TRUE,FALSE); ?>
		<table width="98%" border="0" cellspacing="3" cellpadding="3">
<?php	if(! (@isset($showcategories) && @$showcategories==FALSE)){ ?>
			  <tr>
				<td class="prodnavigation" colspan="2" align="left"><?php print $sstrong . '<p class="prodnavigation">' . $tslist . '</p>' . $estrong?></td>
				<td align="right">&nbsp;<?php if(@$nobuyorcheckout != TRUE) print imageorbutton($imgcheckoutbutton,$xxCOTxt,'checkoutbutton','cart.php', FALSE)?></td>
			  </tr>
<?php	}
	if(@$isproductspage) dofilterresults(3);
if(@$nowholesalediscounts==TRUE && @$_SESSION["clientUser"]!='')
	if((($_SESSION["clientActions"] & 8) == 8) || (($_SESSION["clientActions"] & 16) == 16)) $noshowdiscounts=TRUE;
if(@$noshowdiscounts != TRUE){
	$sSQL = 'SELECT DISTINCT '.getlangid('cpnName',1024).' FROM coupons LEFT OUTER JOIN cpnassign ON coupons.cpnID=cpnassign.cpaCpnID WHERE (';
	$addor = '';
	if($catid != '0'){
		$sSQL .= $addor . "((cpnSitewide=0 OR cpnSitewide=3) AND cpaType=1 AND cpaAssignment IN ('" . str_replace(",","','",$topsectionids) . "'))";
		$addor = ' OR ';
	}
	$sSQL .= $addor . "(cpnSitewide=1 OR cpnSitewide=2)) AND cpnNumAvail>0 AND cpnEndDate>='" . date('Y-m-d',time()) ."' AND cpnIsCoupon=0 AND ((cpnLoginLevel>=0 AND cpnLoginLevel<=".$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.')) ORDER BY cpnID';
	$result2 = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result2) > 0){ ?>
			  <tr>
				<td align="left" class="allproddiscounts" colspan="3">
					<div class="discountsapply allproddiscounts"<?php print (@$nomarkup?'':' style="font-weight:bold;"')?>><?php print $xxDsProd?></div><div class="proddiscounts allproddiscounts"<?php print (@$nomarkup?'':' style="font-size:9px;color:#FF0000;"')?>><?php
						while($rs2=mysql_fetch_assoc($result2)){
							print $rs2[getlangid('cpnName',1024)] . '<br />';
						} ?></div>
				</td>
			  </tr>
<?php
	}
	mysql_free_result($result2);
}
?>
			  <tr>
				<td colspan="3" align="center" class="pagenums"><p class="pagenums"><?php
					If($iNumOfPages > 1 && @$pagebarattop==1) print writepagebar($CurPage,$iNumOfPages,$xxPrev,$xxNext,$pblink,$nofirstpg) . "<br />"; ?>
				  <img src="images/clearpixel.gif" width="300" height="8" alt="" /></p></td>
			  </tr>
<?php
	if(mysql_num_rows($allprods) == 0){
		print '<tr><td colspan="3" align="center"><p>'.$xxNoPrds.'</p></td></tr>';
	}else{
	print '<tr><td colspan="3"><table class="cobtbl cpd" width="100%" border="0" cellspacing="1" cellpadding="3">';
	if(@$cpdheaders!=''){
		$cpdheadarray=explode(',',$cpdheaders);
		print '<tr>';
		foreach($cpdheadarray as $cpdindex => $cpdheadarrval){
			print '<td class="cobhl cpdhl"><div class="cpdhl' . @$cpdarray[$cpdindex] . '">' . $cpdheadarrval . '</div></td>';
		}
		print '</tr>';
	}
	while($rs = mysql_fetch_array($allprods)){
		$thedetailslink = 'proddetail.php?prod=' . urlencode($rs['pId']) . (@$catid!='' && @$catid != '0' && $catid != $rs['pSection'] && @$nocatid != TRUE ? '&amp;cat=' . $catid : '');
		$allimages='';
		$numallimages=0;
		$needdetaillink=trim($rs[getlangid('pLongDescription',4)])!='';
		$result2 = mysql_query("SELECT imageSrc FROM productimages WHERE imageType=0 AND imageProduct='" . escape_string($rs['pId']) . "' ORDER BY imageNumber") or print(mysql_error());
		while($rs2 = mysql_fetch_assoc($result2)) $allimages[$numallimages++]=$rs2;
		mysql_free_result($result2);
		if((@$forcedetailslink!=TRUE && ! $needdetaillink) || @$detailslink!=''){
			$result2 = mysql_query("SELECT imageSrc FROM productimages WHERE imageType=1 AND imageProduct='" . escape_string($rs['pId']) . "' ORDER BY imageNumber LIMIT 0,1") or print(mysql_error());
			if($rs2 = mysql_fetch_assoc($result2)){ $needdetaillink=TRUE; $plargeimage=$rs2['imageSrc']; }
			mysql_free_result($result2);
		}
		if(@$forcedetailslink==TRUE || $needdetaillink){
			if($rs['pStaticPage'] != 0){
				$thedetailslink = cleanforurl($rs[getlangid('pName',1)]) . '.php' . (@$catid!='' && @$catid != '0' && $catid != $rs['pSection'] && @$nocatid != TRUE ? '?cat=' . $catid : '');
				$startlink='<a class="ectlink" href="' . $thedetailslink . '">';
				$endlink='</a>';
			}elseif(@$detailslink!=''){
				$startlink=str_replace('%pid%', $rs['pId'], str_replace('%largeimage%', $plargeimage, $detailslink));
				$endlink=@$detailsendlink;
			}else{
				$startlink='<a class="ectlink" href="' . $thedetailslink . '">';
				$endlink='</a>';
			}
		}else{
			$startlink='';
			$endlink='';
		}
		for($cpnindex=0; $cpnindex < $adminProdsPerPage; $cpnindex++) $aDiscSection[$cpnindex][0] = "";
		if(! $isrootsection){
			$thetopts = $rs["pSection"];
			$gotdiscsection = FALSE;
			for($cpnindex=0; $cpnindex < $adminProdsPerPage; $cpnindex++){
				if($aDiscSection[$cpnindex][0]==$thetopts){
					$gotdiscsection = TRUE;
					break;
				}elseif($aDiscSection[$cpnindex][0]=="")
					break;
			}
			$aDiscSection[$cpnindex][0] = $thetopts;
			if(! $gotdiscsection){
				$topcpnids = $thetopts;
				for($index=0; $index<= 10; $index++){
					if($thetopts==0)
						break;
					else{
						$sSQL = "SELECT topSection FROM sections WHERE sectionID=" . $thetopts;
						$result2 = mysql_query($sSQL) or print(mysql_error());
						if(mysql_num_rows($result2) > 0){
							$rs2 = mysql_fetch_assoc($result2);
							$thetopts = $rs2["topSection"];
							$topcpnids .= "," . $thetopts;
						}else
							break;
					}
				}
				$aDiscSection[$cpnindex][1] = $topcpnids;
			}else
				$topcpnids = $aDiscSection[$cpnindex][1];
		}
		$alldiscounts = "";
		if(@$noshowdiscounts != TRUE){
			$sSQL = "SELECT DISTINCT ".getlangid("cpnName",1024)." FROM coupons LEFT OUTER JOIN cpnassign ON coupons.cpnID=cpnassign.cpaCpnID WHERE (cpnSitewide=0 OR cpnSitewide=3) AND cpnNumAvail>0 AND cpnEndDate>='" . date("Y-m-d",time()) ."' AND cpnIsCoupon=0 AND ((cpaType=2 AND cpaAssignment='" . $rs["pId"] . "')";
			if(! $isrootsection) $sSQL .= " OR (cpaType=1 AND cpaAssignment IN ('" . str_replace(",","','",$topcpnids) . "') AND NOT cpaAssignment IN ('" . str_replace(",","','",$topsectionids) . "'))";
			$sSQL .= ") AND ((cpnLoginLevel>=0 AND cpnLoginLevel<=".$minloglevel.") OR (cpnLoginLevel<0 AND -1-cpnLoginLevel=".$minloglevel.")) ORDER BY cpnID";
			$result2 = mysql_query($sSQL) or print(mysql_error());
			while($rs2=mysql_fetch_row($result2))
				$alldiscounts .= $rs2[0] . "<br />";
			mysql_free_result($result2);
		}
		$optionshavestock=true;
		print '<tr class="cpdtr">';
		if(@$perproducttaxrate==TRUE && ! is_null($rs['pTax'])) $thetax = $rs['pTax']; else $thetax = $countryTaxRate;

		$updatepricecalled=FALSE;
		foreach($cpdarray as $cpdindex => $cpdarrval){
			switch(trim($cpdarrval)){
			case 'id': ?>
			<td class="cobll cpdll"><?php if(! $updatepricecalled) docallupdatepricescript(); ?><div class="prod3id"><?php print $startlink . $rs['pId'] . $endlink ?></div></td>
<?php		break;
			case 'sku': ?>
			<td class="cobll cpdll"><div class="prod3sku"><?php print $startlink . $rs['pSKU'] . $endlink ?></div></td>
<?php		break;
			case 'manufacturer': ?>
			<td class="cobll cpdll"><div class="prod3manufacturer"><?php print $rs['mfName']?></div></td>
<?php		break;
			case 'name': ?>
			<td class="cobll cpdll"><div class="prod3name"><?php print $rs[getlangid('pName',1)] ?></div></td>
<?php		break;
			case 'description': ?>
			<td class="cobll cpdll"><div class="prod3description"><?php
				$shortdesc = $rs[getlangid('pDescription',2)];
				if(@$shortdescriptionlimit=='') print $shortdesc; else print substr($shortdesc, 0, $shortdescriptionlimit) . (strlen($shortdesc)>$shortdescriptionlimit && $shortdescriptionlimit!=0 ? '...' : ''); ?></div></td>
<?php		break;
			case 'image': ?>
			<td class="cobll cpdll"><?php
			if(! $updatepricecalled) docallupdatepricescript();
			if(! is_array($allimages)){
				print '&nbsp;';
			}else{
				if($numallimages>1) print '<table border="0" cellspacing="1" cellpadding="1"><tr><td colspan="3">';
				print $startlink.'<img id="prodimage'.$Count.'" class="'.@$cs.'prod3image" src="'.str_replace('%s','',$allimages[0]['imageSrc']).'" border="0" alt="'.str_replace('"', '&quot;', strip_tags($rs[getlangid('pName',1)])).'" />'.$endlink;
				if($numallimages>1) print '</td></tr><tr><td align="left"><img border="0" src="images/leftimage.gif" onclick="return updateprodimage('.$Count.', false);" onmouseover="this.style.cursor=\'pointer\'" style="float:left;margin:0px;" alt="'.$xxPrev.'"/></td><td align="center"><span class="extraimage extraimagenum" id="extraimcnt'.$Count.'">1</span> <span class="extraimage">'.$xxOf.' '.$extraimages.'</span></td><td align="right"><img border="0" src="images/rightimage.gif" onclick="return updateprodimage('.$Count.', true);" onmouseover="this.style.cursor=\'pointer\'" style="float:right;margin:0px;" alt="'.$xxNext.'"/></td></tr></table>';
			} ?></td>
<?php		break;
			case 'discounts': ?>
			<td class="cobll cpdll"><div class="prod3discounts"><?php if($alldiscounts!='') print $alldiscounts; else print '&nbsp;' ?></div></td>
<?php		break;
			case 'details': ?>
			<td class="cobll cpdll"><div class="prod3details"><?php if($startlink!='') print $startlink . '<strong>' . $xxPrDets . '</strong></a>&nbsp;'; else print '&nbsp;'; ?></div></td>
<?php		break;
			case 'options': ?>
			<td class="cobll cpdll">
<?php			if(! $updatepricecalled) docallupdatepricescript();
				print '<form method="post" name="tForm' . $Count . '" id="ectform' . $Count . '" action="cart.php" onsubmit="return formvalidator' . $Count . '(this)">';
				writehiddenvar('id', $rs['pId']);
				writehiddenvar('mode', 'add');
				if($wishlistonproducts) writehiddenvar('listid', '');
				print '<input type="hidden" name="quant" id="qnt'.$Count.'x" value=""/>';
				if(is_array($prodoptions)){
					if($hasmultipurchase==2)
						print '&nbsp;';
					else{
						print '<div class="prod3options"><table class="prodoptions" border="0" cellspacing="1" cellpadding="1" width="100%">';
						print $optionshtml . '</table></div>';
					}
				}else{
					print '&nbsp;';
				}
				print '</form>';
				if($optjs!='') print '<script language="javascript" type="text/javascript">/* <![CDATA[ */'.$optjs.'/* ]]> */</script>'; ?></td>
<?php		break;
			case 'listprice': ?>
			<td class="cobll cpdll"><div class="prod3listprice"><?php if((double)$rs['pListPrice'] != 0.0) print FormatEuroCurrency($rs["pListPrice"]); else print '&nbsp;' ?></div></td>
<?php		break;
			case 'price': ?>
			<td class="cobll cpdll"><?php if(! $updatepricecalled) docallupdatepricescript(); ?><div class="prod3price"><?php
						if($rs['pId']==$giftcertificateid || $rs['pId']==$donationid)
							print '-';
						else
							print '<span class="price" id="pricediv' . $Count . '">' . ((double)$rs['pPrice']==0 && @$pricezeromessage!= '' ? $pricezeromessage : FormatEuroCurrency($rs['pPrice'])) . '</span>'; ?></div></td>
<?php		break;
			case 'priceinctax': ?>
			<td class="cobll cpdll"><div class="prod3pricetaxinc"><?php
						if($rs['pId']==$giftcertificateid || $rs['pId']==$donationid)
							print '-';
						elseif((double)$rs['pPrice']==0 && @$pricezeromessage!='')
							print '<span class="price" id="pricedivti' . $Count . '"> &nbsp; </span>';
						else{
							print '<span class="price" id="pricedivti' . $Count . '">';
							if(($rs['pExemptions'] & 2)==2) print FormatEuroCurrency($rs['pPrice']); else print FormatEuroCurrency($rs['pPrice']+($rs['pPrice']*$thetax/100.0));
							print '</span>';
						} ?></div></td>
<?php		break;
			case 'currency': ?>
			<td class="cobll cpdll"><?php
						$extracurr = '';
						if($currRate1!=0 && $currSymbol1!='') $extracurr = str_replace('%s',number_format($rs['pPrice']*$currRate1,checkDPs($currSymbol1),$orcdecimals,$orcthousands),$currFormat1) . $currencyseparator;
						if($currRate2!=0 && $currSymbol2!='') $extracurr .= str_replace('%s',number_format($rs['pPrice']*$currRate2,checkDPs($currSymbol2),$orcdecimals,$orcthousands),$currFormat2) . $currencyseparator;
						if($currRate3!=0 && $currSymbol3!='') $extracurr .= str_replace('%s',number_format($rs['pPrice']*$currRate3,checkDPs($currSymbol3),$orcdecimals,$orcthousands),$currFormat3);
						if($rs['pPrice']==0 && @$pricezeromessage!='') $extracurr = '';
						if($extracurr!='') print '<div class="prod3currency"><span class="extracurr" id="pricedivec' . $Count . '">' . $extracurr . '</span></div>';
						?></td>
<?php		break;
			case 'quantity': ?>
			<td class="cobll cpdll"><div class="prod3quant"><?php if($hasmultipurchase>0) print '&nbsp;'; else print '<input type="text" name="w'.$Count.'quant" size="2" maxlength="5" value="1" alt="'.$xxQuant.'" onchange="document.getElementById(\'qnt'.$Count.'x\').value=this.value" />&nbsp;'; ?></div></td>
<?php		break;
			case 'instock': ?>
			<td class="cobll cpdll"><div class="prod3instock"><?php if((int)$rs['pStockByOpts'] != 0 || $rs['pId']==$giftcertificateid || $rs['pId']==$donationid) print '-'; else print $rs['pInStock']; ?></div></td>
<?php		break;
			case 'rating': ?>
			<td class="cobll cpdll"><?php if($rs['pNumRatings']>0) print showproductreviews(3, 'prod3rating'); else print '&nbsp;'; ?></td>
<?php		break;
			case 'buy': ?>
			<td class="cobll cpdll"><?php if(! $updatepricecalled) docallupdatepricescript(); ?><div class="prod3buy"><?php
	if($useStockManagement)
		if($rs['pStockByOpts']!=0) $isInStock = $optionshavestock; else $isInStock = ((int)($rs['pInStock']) > 0);
	else
		$isInStock = ($rs['pSell'] != 0);
	if($rs['pPrice']==0 && @$nosellzeroprice==TRUE){
		print '&nbsp;';
	}elseif(! $isInStock){
		if(@$notifybackinstock)
			print imageorlink(@$imgnotifyinstock, $xxNotBaS, "return notifyinstock(false,'".str_replace("'","\\'",$rs['pId'])."','".str_replace("'","\\'",$rs['pId'])."',".($rs['pStockByOpts']!=0&&!@$optionshavestock?'-1':'0').")", TRUE);
		else
			print '<strong>'.$xxOutStok.'</strong>';
	}elseif($hasmultipurchase==2)
		print imageorbutton(@$imgconfigoptions,$xxConfig,'configbutton',$thedetailslink, FALSE);
	else{
		print imageorbutton(@$imgbuybutton,$xxAddToC,'buybutton','return subformid('.$Count.');', TRUE);
		if($wishlistonproducts) print '<br />' . imageorlink(@$imgaddtolist,$xxAddLis,'gtid='.$Count.';return displaysavelist(event,window)',TRUE);
	}
?></div></td>
<?php		break;
			}
		}
		if($noproductoptions==TRUE){
			$nooptionshtml .= '<form method="post" name="tForm'.$Count.'" id="ectform' . $Count . '" action="cart.php" onsubmit="return formvalidator'.$Count."(this)\">\r\n";
			$nooptionshtml .= '<input type="hidden" name="quant" id="qnt'.$Count.'x" />';
			$nooptionshtml .= '<input type="hidden" name="id" value="'. $rs['pId'].'" />';
			$nooptionshtml .= '<input type="hidden" name="mode" value="add" />';
			if($wishlistonproducts) $nooptionshtml .= '<input type="hidden" name="listid" value="" />';
			$nooptionshtml .= "</form>\r\n";
		}
		print '</tr>';
		$Count++;
	}
	print '</table>' . $nooptionshtml . '</td></tr>';
	}
?>			  <tr>
				<td colspan="3" align="center" class="pagenums"><p class="pagenums"><?php
					if($iNumOfPages > 1 && @$nobottompagebar<>TRUE) print writepagebar($CurPage,$iNumOfPages,$xxPrev,$xxNext,$pblink,$nofirstpg); ?><br />
				  <img src="images/clearpixel.gif" width="300" height="1" alt="" /></p></td>
			  </tr>
			</table>
<?php if($defimagejs!='') print '<script language="javascript" type="text/javascript">'.$defimagejs.'</script>'; ?>