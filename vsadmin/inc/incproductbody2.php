<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
$prodoptions='';
$extraimages=0;
if(@$imgcheckoutbutton=='') $imgcheckoutbutton='images/checkout.gif';
if(@$cs=='')$cs='';
$localcount=0;
if(@$currencyseparator=='') $currencyseparator=' ';
if(@$_SESSION['clientID']=='' || @$enablewishlists==FALSE || @$wishlistonproducts=='') $wishlistonproducts=FALSE;
if(@$overridecurrency!=TRUE || @$orcdecimals=='') $orcdecimals='.';
if(@$overridecurrency!=TRUE || @$orcthousands=='') $orcthousands=',';
productdisplayscript(@$noproductoptions!=TRUE,FALSE); ?>
		<table class="<?php print $cs?>products" width="98%" border="0" cellspacing="3" cellpadding="3">
<?php	if(@$productcolumns=="") $productcolumns=1;
		if(! (@isset($showcategories) && @$showcategories==FALSE)){ ?>
			  <tr>
				<td colspan="<?php print $productcolumns;?>">
				  <table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
					  <td class="prodnavigation" align="left"><?php print $sstrong . '<p class="prodnavigation">' . $tslist . '</p>'; ?></td>
					  <td align="right">&nbsp;<?php if(@$nobuyorcheckout != TRUE) print imageorbutton($imgcheckoutbutton,$xxCOTxt,'checkoutbutton','cart.php', FALSE)?></td>
					</tr>
				  </table>
				</td>
			  </tr>
<?php	}
	if(@$isproductspage) dofilterresults($productcolumns);
if(@$nowholesalediscounts==TRUE && @$_SESSION["clientUser"]!="")
	if((($_SESSION["clientActions"] & 8) == 8) || (($_SESSION["clientActions"] & 16) == 16)) $noshowdiscounts=TRUE;
if(@$noshowdiscounts != TRUE){
	$sSQL = "SELECT DISTINCT ".getlangid("cpnName",1024)." FROM coupons LEFT OUTER JOIN cpnassign ON coupons.cpnID=cpnassign.cpaCpnID WHERE (";
	$addor = "";
	if($catid != "0"){
		$sSQL .= $addor . "((cpnSitewide=0 OR cpnSitewide=3) AND cpaType=1 AND cpaAssignment IN ('" . str_replace(",","','",$topsectionids) . "'))";
		$addor = " OR ";
	}
	$sSQL .= $addor . "(cpnSitewide=1 OR cpnSitewide=2)) AND cpnNumAvail>0 AND cpnEndDate>='" . date("Y-m-d",time()) ."' AND cpnIsCoupon=0 AND ((cpnLoginLevel>=0 AND cpnLoginLevel<=".$minloglevel.") OR (cpnLoginLevel<0 AND -1-cpnLoginLevel=".$minloglevel.")) ORDER BY cpnID";
	$result2 = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result2) > 0){ ?>
			  <tr>
				<td align="left" class="allproddiscounts" colspan="<?php print $productcolumns;?>">
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
	if($iNumOfPages > 1 && @$pagebarattop==1){ ?>
<tr><td colspan="<?php print $productcolumns;?>" align="center" class="pagenums"><p class="pagenums"><?php print writepagebar($CurPage,$iNumOfPages,$xxPrev,$xxNext,$pblink,$nofirstpg) . '<br />'; ?><img src="images/clearpixel.gif" width="50" height="5" alt="" /></p></td></tr>
<?php
	}
	$totrows = mysql_num_rows($allprods);
	if(mysql_num_rows($allprods) == 0)
		print '<tr><td colspan="' . $productcolumns . '" align="center"><p>'.$xxNoPrds.'</p></td></tr>';
	else while($rs = mysql_fetch_array($allprods)){
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
				$startlink='<a class="ectlink" href="'. $thedetailslink .'">';
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
			$sSQL .= ') AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.')) ORDER BY cpnID';
			$result2 = mysql_query($sSQL) or print(mysql_error());
			while($rs2=mysql_fetch_row($result2))
				$alldiscounts .= $rs2[0] . '<br />';
			mysql_free_result($result2);
		}
		if(($localcount % $productcolumns) == 0) print "<tr>" ?>
				<td width="<?php print (int)(100 / $productcolumns);?>%" align="center" valign="top" class="<?php print $cs?>product"><div class="<?php print $cs?>product">
<?php	if(@$perproducttaxrate==TRUE && ! is_null($rs['pTax'])) $thetax = $rs['pTax']; else $thetax = $countryTaxRate;
		updatepricescript(@$noproductoptions!=TRUE,$thetax,FALSE);
		$thedesc = trim($rs[getlangid('pDescription',2)]);
		if(@$shortdescriptionlimit!='') $thedesc = substr($thedesc, 0, $shortdescriptionlimit) . (strlen($thedesc)>$shortdescriptionlimit && $shortdescriptionlimit!=0 ? '...' : ''); ?>
				<form method="post" name="tForm<?php print $Count;?>" id="ectform<?php print $Count;?>" action="cart.php" style="margin:0;padding:0;" onsubmit="return formvalidator<?php print $Count;?>(this)">
				  <table width="100%" border="0" cellspacing="4" cellpadding="4">
<?php	if(@$showproductid==TRUE) print '<tr><td><div class="'.$cs.'prodid">' . $sstrong . $xxPrId . ': ' . $estrong . $rs['pId'] . '</div></td></tr>';
		if(@$manufacturerfield!='' && ! is_null($rs['mfName'])) print '<tr><td><div class="'.$cs.'prodmanufacturer">' . $sstrong . $manufacturerfield . ': ' . $estrong . $rs['mfName'] . '</div></td></tr>';
		if(@$showproductsku!='' && $rs['pSKU']!='') print '<tr><td><div class="'.$cs.'prodsku">' . $sstrong . $showproductsku . ': ' . $estrong . $rs['pSKU'] . '</div></td></tr>'; ?>
				    <tr>
					  <td width="100%" align="center" class="<?php print $cs?>prodimage">
<?php		if(! is_array($allimages)){
				print '&nbsp;';
			}else{
				if($numallimages>1) print '<table border="0" cellspacing="1" cellpadding="1"><tr><td colspan="3">';
				print $startlink.'<img id="prodimage'.$Count.'" class="'.@$cs.'prodimage" src="'.str_replace('%s','',$allimages[0]['imageSrc']).'" border="0" alt="'.str_replace('"', '&quot;', strip_tags($rs[getlangid('pName',1)])).'" />'.$endlink;
				if($numallimages>1) print '</td></tr><tr><td align="left"><img border="0" src="images/leftimage.gif" onclick="return updateprodimage('.$Count.', false);" onmouseover="this.style.cursor=\'pointer\'" style="float:left;margin:0px;" alt="'.$xxPrev.'"/></td><td align="center"><span class="extraimage extraimagenum" id="extraimcnt'.$Count.'">1</span> <span class="extraimage">'.$xxOf.' '.$extraimages.'</span></td><td align="right"><img border="0" src="images/rightimage.gif" onclick="return updateprodimage('.$Count.', true);" onmouseover="this.style.cursor=\'pointer\'" style="float:right;margin:0px;" alt="'.$xxNext.'"/></td></tr></table>';
			} ?>
					  </td>
					</tr>
					<tr>
					  <td width="100%">
<?php	print $sstrong . '<div class="'.$cs.'prodname">' . $startlink . $rs[getlangid("pName",1)] . $endlink . $xxDot . '</div>' . $estrong;
		if($alldiscounts != "") print ' ' . (@$nomarkup?'':'<font color="#FF0000">') .$sstrong.'<span class="discountsapply">' . $xxDsApp . '</span>'.$estrong . (@$nomarkup?'':'</font>') . '<br /><div class="'.$cs.'proddiscounts"' . (@$nomarkup?'':' style="font-size:11px;color:#FF0000;"') . '>' . $alldiscounts . '</div>';
		if(@$ratingsonproductspage==TRUE && $rs['pNumRatings']>0) print showproductreviews(2, $cs.'prodrating');
		if($useStockManagement && @$showinstock==TRUE){ if((int)$rs['pStockByOpts']==0) print '<div class="'.$cs.'prodinstock">' . $sstrong . $xxInStoc . ': ' . $estrong . $rs['pInStock'] . '</div>'; } ?>
<?php	if($thedesc!="") print '<div class="'.$cs.'proddescription">' . $thedesc . '</div>'; else print '<br />';
		$optionshavestock=true;
		$hasmultipurchase = 0;
		if(is_array($prodoptions)){
			if($noproductoptions==TRUE){
				$hasmultipurchase=2;
			}else{
				if($prodoptions[0]['optType']==4 && @$noproductoptions!=TRUE) $thestyle=''; else $thestyle=' width="100%"';
				$optionshtml = displayproductoptions($sstrong . '<span class="prodoption">','</span>'.$estrong,$optdiff,$thetax,FALSE,$hasmultipurchase,$optjs);
				if($optjs!='') print '<script language="javascript" type="text/javascript">/* <![CDATA[ */'.$optjs.'/* ]]> */</script>';
				if($optionshtml!='') print '<div class="'.$cs.'prodoptions"><table class="'.$cs.'prodoptions" border="0" cellspacing="1" cellpadding="1"'.$thestyle.'>' . $optionshtml . '</table></div>';
				$rs['pPrice'] += $optdiff;
			}
			if($hasmultipurchase==2) print '<br/>';
		}
		if($rs['pId']!=$giftcertificateid && $rs['pId']!=$donationid && @$noprice!=TRUE){
			if((double)$rs['pListPrice']!=0.0) print '<div class="'.$cs.'listprice">' . str_replace('%s', FormatEuroCurrency($rs['pListPrice']), $xxListPrice) . '</div>';
			print '<div class="'.$cs.'prodprice"><strong>' . $xxPrice . ':</strong> <span class="price" id="pricediv' . $Count . '">' . ($rs['pPrice']==0 && @$pricezeromessage!='' ? $pricezeromessage : FormatEuroCurrency(@$showtaxinclusive===2 && ($rs['pExemptions'] & 2)!=2 ? $rs['pPrice']+($rs['pPrice']*$thetax/100.0) : $rs['pPrice'])) . '</span> ';
			if(@$showtaxinclusive==1 && ($rs['pExemptions'] & 2)!=2) printf('<span id="taxmsg' . $Count . '"' . ($rs['pPrice']==0 ? ' style="display:none"' : '') . '>' . $ssIncTax . '</span>','<span id="pricedivti' . $Count . '">' . ($rs['pPrice']==0 ? '-' : FormatEuroCurrency($rs['pPrice']+($rs['pPrice']*$thetax/100.0))) . '</span> ');
			print '</div>';
			$extracurr = '';
			if($currRate1!=0 && $currSymbol1!='') $extracurr = str_replace('%s',number_format($rs['pPrice']*$currRate1,checkDPs($currSymbol1),$orcdecimals,$orcthousands),$currFormat1) . $currencyseparator;
			if($currRate2!=0 && $currSymbol2!='') $extracurr .= str_replace('%s',number_format($rs['pPrice']*$currRate2,checkDPs($currSymbol2),$orcdecimals,$orcthousands),$currFormat2) . $currencyseparator;
			if($currRate3!=0 && $currSymbol3!='') $extracurr .= str_replace('%s',number_format($rs['pPrice']*$currRate3,checkDPs($currSymbol3),$orcdecimals,$orcthousands),$currFormat3);
			if($extracurr!='') print '<div class="'.$cs.'prodcurrency"><span class="extracurr" id="pricedivec' . $Count . '">' . ($rs['pPrice']==0 ? '' : $extracurr) . '</span></div>';
		} ?>
					  </td>
					</tr><?php
		if(@$nobuyorcheckout != TRUE){
			if($rs['pId']==$giftcertificateid || $rs['pId']==$donationid) $hasmultipurchase=2;
			print '<tr><td align="center">';
			if($useStockManagement)
				if($rs['pStockByOpts']!=0) $isInStock = $optionshavestock; else $isInStock = ((int)($rs['pInStock']) > 0);
			else
				$isInStock = ($rs['pSell'] != 0);
			if($rs['pPrice']==0 && @$nosellzeroprice==TRUE){
				print '&nbsp;';
			}elseif(! $isInStock && !($useStockManagement && $hasmultipurchase==2)){
				if(@$notifybackinstock)
					print imageorlink(@$imgnotifyinstock, $xxNotBaS, "return notifyinstock(false,'".str_replace("'","\\'",$rs['pId'])."','".str_replace("'","\\'",$rs['pId'])."',".($rs['pStockByOpts']!=0&&!@$optionshavestock?'-1':'0').")", TRUE);
				else
					print '<strong>'.$xxOutStok.'</strong>';
			}elseif($hasmultipurchase==2)
				print imageorbutton(@$imgconfigoptions,$xxConfig,'configbutton',$thedetailslink, FALSE);
			else{
				writehiddenvar('id', $rs['pId']);
				writehiddenvar('mode', 'add');
				if(@$showquantonproduct && $hasmultipurchase==0) print '<table><tr><td align="center">';
				if($wishlistonproducts) writehiddenvar('listid', '');
				if(@$showquantonproduct && $hasmultipurchase==0) print '<input type="text" name="quant" size="2" maxlength="5" value="1" alt="'.$xxQuant.'" />' . (@$showquantonproduct && $hasmultipurchase==0 ? '</td><td align="center">' : '');
				if(@$custombuybutton!='') print $custombuybutton; else print imageorsubmit(@$imgbuybutton,$xxAddToC,'buybutton');
				if($wishlistonproducts) print '<br />' . imageorlink(@$imgaddtolist,$xxAddLis,'gtid='.$Count.';return displaysavelist(event,window)',TRUE);
				if(@$showquantonproduct && $hasmultipurchase==0) print '</td></tr></table>';
			}
			print '</td></tr>';
		} ?>			  </table>
				  </form></div>
				</td><?php
		$Count++;
		$localcount++;
		if(($localcount % $productcolumns) == 0){
			print "</tr>";
			if(! ($localcount==$totrows) && $localcount < $adminProdsPerPage){
				if(@$noproductseparator!=TRUE){
					print '<tr>';
					for($index=1; $index <= $productcolumns; $index++)
						print '<td class="prodseparator">' . (@$prodseparator!='' ? $prodseparator : '<hr class="prodseparator" width="70%" align="center"/>') . '</td>';
					print '</tr>';
				}
			}
		}
	}
	if($localcount % $productcolumns != 0){
		while($localcount % $productcolumns != 0){
			print '<td class="'.$cs.'noproduct" width="' . (int)(100 / $productcolumns) . '%" align="center">&nbsp;</td>';
			$localcount++;
		}
		print '</tr>';
	}
?>
<tr><td colspan="<?php print $productcolumns;?>" align="center" class="pagenums"><p class="pagenums"><?php if($iNumOfPages > 1 && @$nobottompagebar<>TRUE) print writepagebar($CurPage,$iNumOfPages,$xxPrev,$xxNext,$pblink,$nofirstpg) . '<br />'; ?><img src="images/clearpixel.gif" width="50" height="1" alt="" /></p></td></tr>
</table>
<?php if($defimagejs!='') print '<script language="javascript" type="text/javascript">'.$defimagejs.'</script>'; ?>