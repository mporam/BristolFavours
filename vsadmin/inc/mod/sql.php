<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(trim(@$explicitid) != '') $prodid=trim($explicitid); else $prodid=trim(@$_GET['prod']);
$prodlist = "'" . escape_string($prodid) . "'";
$WSP = '';
$OWSP = '';
$TWSP = 'pPrice';
$tslist = '';
if(@$dateformatstr == '') $dateformatstr = 'm/d/Y';
get_wholesaleprice_sql();
$Count=0;
$optionshtml='';
$previousid='';
$nextid='';
$hasmultipurchase=FALSE;
if(@$imgcheckoutbutton=='') $imgcheckoutbutton='images/checkout.gif';
if(@$numcustomerratings=='') $numcustomerratings=6;
$reviewsshown=FALSE;
if(@$wishlistonproducts==TRUE) $wishlistondetail=TRUE;
if(@$_SESSION['clientID']=='' || @$enablewishlists==FALSE || @$wishlistondetail=='') $wishlistondetail=FALSE;
if(@$overridecurrency!=TRUE || @$orcdecimals=='') $orcdecimals='.';
if(@$overridecurrency!=TRUE || @$orcthousands=='') $orcthousands=',';
if(@$_SESSION['clientID']!='' && @$_SESSION['clientLoginLevel']!='') $minloglevel=$_SESSION['clientLoginLevel']; else $minloglevel=0;
function displaytabs($thedesc){
	global $ecttabs,$ecttabsspecials,$reviewsshown,$prodid,$languageid,$enablecustomerratings,$relatedtabtemplate,$shortdescriptionlimit,$xxDescr,$relatedproductsbothways,$defaultdescriptiontab,$showtaxinclusive,$thetax,$ratingslanguages;
	$hasdesctab=(strpos($thedesc, '<ecttab')!==FALSE);
	if($hasdesctab || @$ecttabsspecials!='' || @$ecttabs!='' || @$defaultdescriptiontab!=''){
		if(@$defaultdescriptiontab=='')$defaultdescriptiontab='<ecttab title="'.$xxDescr.'">';
		if(! $hasdesctab && $thedesc!='') $thedesc = $defaultdescriptiontab . $thedesc;
		if(strpos(@$ecttabsspecials, '%tabs%')!==FALSE) $thedesc = str_replace('%tabs%', $thedesc, $ecttabsspecials); else $thedesc.=@$ecttabsspecials;
		if($ecttabs=='slidingpanel'){
			$displaytabs='<div class="slidingTabPanelWrapper"><ul class="slidingTabPanel">';
			$tabcontent='<div id="slidingPanel"><div>';
		}else{
			$displaytabs='<div class="TabbedPanels" id="TabbedPanels1"><ul class="TabbedPanelsTabGroup">';
			$tabcontent='<div class="TabbedPanelsContentGroup">';
		}
		$dind=strpos($thedesc, '<ecttab');
		$tabindex=1;
		while($dind!==FALSE){
			$dind+=8;
			$dind2=strpos($thedesc, '>', $dind);
			if($dind2!==FALSE){
				$dtitle=''; $dimage=''; $dimageov=''; $dspecial='';
				$tproperties = substr($thedesc,$dind,$dind2-$dind);
				$pind=strpos($tproperties, 'title=');
				if($pind!==FALSE){
					$pind=strpos($tproperties, '"', $pind)+1;
					$pind2=strpos($tproperties, '"', $pind+1);
					$dtitle=substr($tproperties,$pind,$pind2-$pind);
				}
				$pind=strpos($tproperties, 'img=');
				if($pind!==FALSE){
					$pind=strpos($tproperties, '"', $pind)+1;
					$pind2=strpos($tproperties, '"', $pind+1);
					$dimage=substr($tproperties,$pind,$pind2-$pind);
				}
				$pind=strpos($tproperties, 'imgov=');
				if($pind!==FALSE){
					$pind=strpos($tproperties, '"', $pind)+1;
					$pind2=strpos($tproperties, '"', $pind+1);
					$dimageov=substr($tproperties,$pind,$pind2-$pind);
				}
				$pind=strpos($tproperties, 'special=');
				if($pind!==FALSE){
					$pind=strpos($tproperties, '"', $pind)+1;
					$pind2=strpos($tproperties, '"', $pind+1);
					$dspecial=substr($tproperties,$pind,$pind2-$pind);
				}
				$dind2++;
				$dind=strpos($thedesc, '<ecttab', $dind2);
				if($dind===FALSE) $dcontent=substr($thedesc,$dind2); else $dcontent=substr($thedesc,$dind2,$dind-$dind2);
				$hascontent=TRUE;
				if($dspecial=='reviews'){
					if(@$enablecustomerratings){
						$sSQL = "SELECT rtID,rtRating,rtPosterName,rtHeader,rtDate,rtComments FROM ratings WHERE rtApproved<>0 AND rtProdID='".escape_string($prodid)."'";
						if(@$ratingslanguages!='') $sSQL .= ' AND rtLanguage+1 IN ('.$ratingslanguages.')'; elseif(@$languageid!='') $sSQL .= ' AND rtLanguage='.((int)$languageid-1); else $sSQL .= ' AND rtLanguage=0';
						$sSQL .= ' ORDER BY rtDate DESC,rtRating DESC';
						$dcontent = '<table border="0" cellspacing="0" cellpadding="0" width="100%">' . showreviews($sSQL,FALSE) . '</table>';
						$reviewsshown=TRUE;
					}else
						$hascontent=FALSE;
				}elseif($dspecial=='related'){
					$dcontent = '<table class="reltab" width="100%">';
					if(@$relatedtabtemplate==''){
						$relatedtabtemplate='<tr><td class="reltabimage" rowspan="2">%img%</td><td class="reltabname">%name% - %price%</td></tr>' .
							'<tr><td class="reltabdescription">%description%</td></tr>';
					}
					$sSQL = 'SELECT pId,pSection,pImage,pLargeImage,'.getlangid('pName',1).',pPrice,pStaticPage,pExemptions,'.getlangid('pDescription',2)." FROM products INNER JOIN relatedprods ON products.pId=relatedprods.rpRelProdID WHERE pDisplay<>0 AND rpProdID='".$prodid."'";
					if(@$relatedproductsbothways==TRUE) $sSQL .= ' UNION SELECT pId,pSection,pImage,pLargeImage,'.getlangid('pName',1).',pPrice,pStaticPage,pExemptions,'.getlangid('pDescription',2)." FROM products INNER JOIN relatedprods ON products.pId=relatedprods.rpProdID WHERE pDisplay<>0 AND rpRelProdID='".$prodid."'";
					$result = mysql_query($sSQL) or print(mysql_error());
					if(mysql_num_rows($result)==0)
						$hascontent=FALSE;
					else{
						while($rs2 = mysql_fetch_assoc($result)){
							if($rs2['pStaticPage'] != 0){
								$thedetailslink = cleanforurl($rs2[getlangid('pName',1)]) . '.php' . (@$catid != '' && @$catid != '0' && $catid != $rs2['pSection'] && @$nocatid != TRUE ? '?cat=' . $catid : '');
								$startlink='<a class="ectlink" href="' . $thedetailslink . '">';
								$endlink='</a>';
							}elseif(@$detailslink != ''){
								$startlink=str_replace('%pid%', $rs2['pId'], str_replace('%largeimage%', $rs2['pLargeImage'], $detailslink));
								$endlink=@$detailsendlink;
							}else{
								$startlink='<a class="ectlink" href="'. 'proddetail.php?prod=' . urlencode($rs2['pId']) . (@$catid != '' && @$catid != '0' && $catid != $rs2['pSection'] && @$nocatid != TRUE ? '&amp;cat=' . $catid : '') .'">';
								$endlink='</a>';
							}
							$rtc = str_replace('%img%', (trim($rs2['pImage'])!='' && $rs2['pImage']!='prodimage/' ? $startlink . '<img class="reltabimage" src="' . $rs2['pImage'] . '" border="0" alt="'.str_replace('"','&quot;',strip_tags($rs2[getlangid('pName',1)])).'" />' . $endlink : '&nbsp;'), $relatedtabtemplate);
							$rtc = str_replace('%name%', $startlink . $rs2[getlangid('pName',1)] . $endlink, $rtc);
							$rtc = str_replace('%id%', $startlink . $rs2['pId'] . $endlink, $rtc);
							$rtc = str_replace('%price%', ($rs2['pPrice']==0 && @$pricezeromessage != '' ? $pricezeromessage : FormatEuroCurrency(@$showtaxinclusive===2 && ($rs2['pExemptions'] & 2)!=2 ? $rs2['pPrice']+($rs2['pPrice']*$thetax/100.0) : $rs2['pPrice'])), $rtc);
							$shortdesc = $rs2[getlangid('pDescription',2)];
							if(@$shortdescriptionlimit!='') $shortdesc = substr($shortdesc, 0, $shortdescriptionlimit) . (strlen($shortdesc)>$shortdescriptionlimit && $shortdescriptionlimit!=0 ? '...' : '');
							$rtc = str_replace('%description%', $shortdesc, $rtc);
							$dcontent .= $rtc;
						}
					}
					mysql_free_result($result);
					$dcontent .= '</table>';
				}
				if($hascontent){
					if(@$ecttabs=='slidingpanel')
						$displaytabs.='<li><a href="#" id="ecttab'.$tabindex.'" class="tab'.($tabindex==1?'Active':'').'" title="'.$dtitle.'">';
					else
						$displaytabs.='<li class="TabbedPanelsTab" tabindex="0">';
					if($dimage!=''){
						$displaytabs.='<img src="'.$dimage.'" alt="'.str_replace('"','&quot;',$dtitle).'" border="0" ';
						if($dimageov!='') $displaytabs.='onmouseover="this.src=\''.$dimageov.'\'" onmouseout="this.src=\''.$dimage.'\'" ';
						$displaytabs.='/>';
					}else
						$displaytabs.=str_replace(' ','&nbsp;',$dtitle);
				}
				if(@$ecttabs=='slidingpanel'){
					$displaytabs.='</a></li>';
					$tabcontent.='<div id="ecttab'.$tabindex.'Panel" class="tabpanelcontent">'.$dcontent.'</div>';
				}else{
					$displaytabs.='</li>';
					$tabcontent.='<div class="tabpanelcontent">'.$dcontent.'</div>';
				}
				$tabindex++;
			}
		}
		if(@$ecttabs=='slidingpanel'){
			$displaytabs.='</ul></div>'.$tabcontent.'</div></div>';
			$displaytabs.='<script type="text/javascript">var sp2;var quotes;var lastTab="ecttab1";';
			$displaytabs.='function switchTab(tab){if(tab!=lastTab){document.getElementById(tab).className=("tabActive");document.getElementById(lastTab).className=("tab");sp2.showPanel(tab+"Panel");lastTab=tab;}}';
			$displaytabs.='Spry.Utils.addLoadListener(function(){';
			$displaytabs.="	Spry.$$('.slidingTabPanelWrapper').setStyle('display: block');";
			$displaytabs.="	Spry.$$('#ecttab1";
			for($i=2;$i<=$tabindex-1;$i++){
				$displaytabs.=',#ecttab'.$i;
			}
			$displaytabs.="').addEventListener('click', function(){ switchTab(this.id); return false; }, false);";
			$displaytabs.="	Spry.$$('#slidingPanel').addClassName('SlidingPanels').setAttribute('tabindex', '0');";
			$displaytabs.="	Spry.$$('#slidingPanel > div').addClassName('SlidingPanelsContentGroup');";
			$displaytabs.="	Spry.$$('#slidingPanel .SlidingPanelsContentGroup > div').addClassName('SlidingPanelsContent');";
			$displaytabs.="	sp2 = new Spry.Widget.SlidingPanels('slidingPanel');";
			$displaytabs.='});</script>';
		}else{
			$displaytabs.='</ul>'.$tabcontent.'</div></div>';
			$displaytabs.='<script type="text/javascript">var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");</script>';
		}
		return($displaytabs);
	}else
		return($thedesc);
}
function showdetailimages(){
	global $Count,$rs,$xxPrev,$xxNext,$xxEnlrge,$xxOf,$extraimages;
	if(! (trim($rs['pLargeImage'])=='' || trim($rs['pLargeImage'])=='prodimages/')){
		if(trim($rs['pLargeImage2'])!='' || trim($rs['pGiantImage'])!='') print '<table border="0" cellspacing="1" cellpadding="1"><tr><td>';
		print '<img id="prodimage'.$Count.'" class="prodimage" src="'.$rs['pLargeImage'].'" border="0" alt="'.str_replace('"','&quot;',strip_tags($rs[getlangid('pName',1)])).'" />';
		if(trim($rs['pLargeImage2'])!='' || trim($rs['pGiantImage'])!='') print '</td></tr><tr><td align="center">'.(trim($rs['pLargeImage2'])!='' ? '<img border="0" src="images/leftimage.gif" onclick="return updateprodimage('.$Count.', false);" onmouseover="this.style.cursor=\'pointer\'" alt="'.$xxPrev.'" style="vertical-align:middle;margin:0px;" />' : '&nbsp;').' '.(trim($rs['pLargeImage2'])!='' ? '<span class="extraimage extraimagenum" id="extraimcnt'.$Count.'">1</span> <span class="extraimage">'.$xxOf.' '.$extraimages.'</span> ' : '').(trim($rs['pGiantImage'])!='' ? '<span class="extraimage">(<a class="ectlink" href="javascript:showgiantimage(\''.trim($rs['pGiantImage']).'\')">'.$xxEnlrge.'</a>)</span>' : '') . ' '.(trim($rs['pLargeImage2'])!='' ? '<img border="0" src="images/rightimage.gif" onclick="return updateprodimage('.$Count.', true);" onmouseover="this.style.cursor=\'pointer\'" alt="'.$xxNext.'" style="vertical-align:middle;margin:0px;" />' : '&nbsp;').'</td></tr></table>';
	}elseif(! (trim($rs['pImage'])=='' || trim($rs['pImage'])=='prodimages/')){
		print '<img id="prodimage'.$Count.'" class="prodimage" src="'.$rs['pImage'].'" border="0" alt="'.str_replace('"','&quot;',strip_tags($rs[getlangid('pName',1)])).'" />';
	}else
		print '&nbsp;';
}
function writepreviousnextlinks(){
	global $xxPrev,$previousid,$previousidname,$previousidstatic,$previousidcat,$xxNext,$nextid,$nextidname,$nextidstatic,$nextidcat,$thecatid,$catid;
	$currcat = (int)($thecatid != '' ? $thecatid : $catid);
	if($previousid != ''){
		if($previousidstatic)
			print '<a class="ectlink" href="' . cleanforurl($previousidname) . '.php' . ($previousidcat!=$currcat && @$nocatid!=TRUE ? '?cat=' . $currcat : '') . '">';
		else
			print '<a class="ectlink" href="proddetail.php?prod=' . $previousid . ($previousidcat!=$currcat && @$nocatid!=TRUE ? '&amp;cat=' . $currcat : '') . '">';
	}
	print '<strong>&laquo; ' . $xxPrev . '</strong>';
	if($previousid != '') print '</a>';
	print ' | ';
	if($nextid != ''){
		if($nextidstatic)
			print '<a class="ectlink" href="' . cleanforurl($nextidname) . '.php' . ($nextidcat!=$currcat && @$nocatid!=TRUE ? '?cat=' . $currcat : '') . '">';
		else
			print '<a class="ectlink" href="proddetail.php?prod=' . $nextid . ($nextidcat!=$currcat && @$nocatid!=TRUE ? '&amp;cat=' . $currcat : '') . '">';
	}
	print '<strong>' . $xxNext . ' &raquo;</strong>';
	if($nextid != '') print '</a>';
}
function detailpageurl($params){
	global $hasstaticpage,$rs,$prodid;
	if($hasstaticpage)
		return cleanforurl($rs[getlangid('pName',1)]).'.php'.($params!='' ? '?' . $params : '');
	else
		return 'proddetail.php?prod='.urlencode($prodid) . ($params!='' ? '&amp;' . $params : '');
}
function showreviews($theSQL,$showall){
	global $prodid,$thecatid,$xxRvAvRa,$xxRvPrRe,$xxRvBest,$xxRvWors,$xxRvRece,$xxRvOld,$xxShoAll,$xxRvNone,$xxClkRev,$numcustomerratings,$customerratinglength,$onlyclientratings,$allreviewspagesize,$languageid,$dateformatstr,$rs,$catid,$xxPrev,$xxNext,$ratingslanguages;
	$srv='';
	$numreviews=0; $totrating=0; $maxrating=0;
	$totSQL = "SELECT COUNT(*) as numreviews, SUM(rtRating) AS totrating, MAX(rtRating) AS maxrating FROM ratings WHERE rtApproved<>0 AND rtProdID='" . escape_string($prodid) . "'";
	// if(@$ratingslanguages!='') $totSQL .= ' AND rtLanguage+1 IN ('.$ratingslanguages.')'; elseif(@$languageid!='') $totSQL .= ' AND rtLanguage='.((int)$languageid-1); else $totSQL .= ' AND rtLanguage=0';
	$result = mysql_query($totSQL) or print(mysql_error());
	$rs2 = mysql_fetch_assoc($result);
	if(! is_null($rs2['numreviews'])){
		$numreviews = $rs2['numreviews'];
		$totrating = $rs2['totrating'];
		$maxrating = $rs2['maxrating'];
	}
	mysql_free_result($result);
	$srv = '<tr><td class="review"><div class="hreview-aggregate"><span style="display:none" class="item"><span class="fn">' . $rs[getlangid('pName',1)] . '</span></span><a name="reviews"></a>&nbsp;<br /><span class="review numreviews"><span class="count">' . ($numreviews<>0 ? $numreviews . ' ' : '') . '</span>' . $xxRvPrRe;
	if($numreviews > 0)
		$srv .= ' - '.$xxRvAvRa.' <span class="rating"><span class="average">'.round(($totrating / $numreviews) / 2, 1) . '</span></span> / 5';
	$srv .= '</span><span class="review showallreview">';
	if($showall){
		$srv .= ' (<a class="ectlink" href="' . detailpageurl('review=all' . ($thecatid!='' ? '&amp;cat='.$thecatid : '').'&amp;ro=1') . '">'.$xxRvBest.'</a>';
		$srv .= ' | <a class="ectlink" href="' . detailpageurl('review=all' . ($thecatid!='' ? '&amp;cat='.$thecatid : '').'&amp;ro=2') . '">'.$xxRvWors.'</a>';
		$srv .= ' | <a class="ectlink" href="' . detailpageurl('review=all' . ($thecatid!='' ? '&amp;cat='.$thecatid : '')) . '">'.$xxRvRece.'</a>';
		$srv .= ' | <a class="ectlink" href="' . detailpageurl('review=all' . ($thecatid!='' ? '&amp;cat='.$thecatid : '').'&amp;ro=3') . '">'.$xxRvOld.'</a>)';
	}elseif($numreviews > 0)
		$srv .= ' (<a class="ectlink" href="' . detailpageurl('review=all' . ($thecatid!='' ? '&amp;cat='.$thecatid : '')) . '">'.$xxShoAll.'</a>)';
	$srv .= '</span><br /><hr class="review" />';
	if(@$allreviewspagesize=='') $allreviewspagesize = 30;
	if($showall) $thepagesize = $allreviewspagesize; else $thepagesize = $numcustomerratings;
	$iNumOfPages = ceil($numreviews/$thepagesize);
	if(! is_numeric(@$_GET['pg'])) $CurPage = 1; else $CurPage = max(1, (int)(@$_GET['pg']));
	if($numreviews > 0){
		$theSQL .=  ' LIMIT ' . ($thepagesize*($CurPage-1)) . ', ' . $thepagesize;
		$result = mysql_query($theSQL) or print(mysql_error());
		if(! (@$onlyclientratings && @$_SESSION['clientID']=='')) $srv .= '<span class="review clickreview"><a class="ectlink" rel="nofollow" href="' . detailpageurl('review=true' . ($thecatid!='' ? '&amp;cat='.$thecatid : '')) . '">'.$xxClkRev.'</a></span><br /><hr class="review" />';
		while($rs2 = mysql_fetch_assoc($result)){
			$srv .= '<div class="hreview"><span style="display:none" class="item"><span class="fn">' . $rs[getlangid('pName',1)] . '</span></span><span class="rating"><span class="value-title" title="' . round($rs2['rtRating']/2) . '" />';
			for($index=1; $index <= (int)$rs2['rtRating'] / 2; $index++)
				$srv .= '<img src="images/reviewcart.gif" alt="" style="vertical-align:middle;margin:0px;" />';
			$ratingover = $rs2['rtRating'];
			if($ratingover / 2 > (int)($ratingover / 2)){
				$srv .= '<img src="images/reviewcarthg.gif" alt="" style="vertical-align:middle;margin:0px;" />';
				$ratingover++;
			}
			for($index=((int)$ratingover / 2) + 1; $index <= 5; $index++)
				$srv .= '<img src="images/reviewcartg.gif" alt="" style="vertical-align:middle;margin:0px;" />';
			$srv .= '</span> <span class="review reviewheader">' . $rs2['rtHeader'] . '</span>';
			$srv .= '<br /><br /><span class="review reviewname"><span class="reviewer">' . $rs2['rtPosterName'] . '</span> - <span class="dtreviewed">' . date($dateformatstr, strtotime($rs2['rtDate'])) . '<span class="value-title" title="' . $rs2['rtDate'] . '" /></span></span>';
			$thecomments = $rs2['rtComments'];
			if(! $showall){
				if(@$customerratinglength=='') $customerratinglength=255;
				if(strlen($thecomments)>$customerratinglength) $thecomments = substr($thecomments, 0, $customerratinglength) . '...';
			}
			$srv .= '<br /><br /><span class="summary review reviewcomments">' . str_replace("\r\n", '<br />', $thecomments) . '</span><br /><hr class="review" />';
			$srv .= '</div>';
		}
		mysql_free_result($result);
	}else
		$srv .= '<span class="review noreview">' . $xxRvNone . '</span><br /><hr class="review" />';
	if(! (@$onlyclientratings && @$_SESSION['clientID']=='')) $srv .= '<span class="review clickreview"><a class="ectlink" rel="nofollow" href="' . detailpageurl('review=true' . ($thecatid!='' ? '&amp;cat='.$thecatid : '')) . '">'.$xxClkRev.'</a></span><br /><hr class="review" />';
	$srv .= '</div></td></tr>';
	$pblink = '<a class="vrectlink" href="'.@$_SERVER['PHP_SELF'].'?';
	foreach(@$_GET as $objQS => $objValue)
		if($objQS!='cat' && $objQS!='id' AND $objQS!='pg') $pblink .= urlencode($objQS) . '=' . urlencode($objValue) . '&amp;';
	if($catid != '0' && @$explicitid=='') $pblink .= 'cat=' . $catid . '&amp;pg='; else $pblink .= 'pg=';
	if($showall && $iNumOfPages > 1) $srv .= '<tr><td align="center">' . writepagebar($CurPage,$iNumOfPages,$xxPrev,$xxNext,$pblink,TRUE) . '</td></tr>';
	return($srv);
}
$alreadygotadmin = getadminsettings();
$thesessionid=getsessionid();
checkCurrencyRates($currConvUser,$currConvPw,$currLastUpdate,$currRate1,$currSymbol1,$currRate2,$currSymbol2,$currRate3,$currSymbol3);
$sSQL = 'SELECT pId,pSKU,'.getlangid('pName',1).','.getlangid('pDescription',2).',pImage,'.$WSP.'pPrice,pSection,pListPrice,pSell,pStockByOpts,pStaticPage,pInStock,pExemptions,'.(@$detailslink!='' ? "'' AS " : '').'pLargeImage,pLargeImage2,pLargeImage3,pLargeImage4,pLargeImage5,pGiantImage,pGiantImage2,pGiantImage3,pGiantImage4,pGiantImage5,pTax,pOrder,pDateAdded,'.(@$manufacturerfield!=''?'mfName,':'').getlangid('pLongDescription',4).' FROM products '.(@$manufacturerfield!=''?'LEFT OUTER JOIN manufacturer on products.pManufacturer=manufacturer.mfID ':'')."WHERE pDisplay<>0 AND pId='" . escape_string($prodid) . "'";
$result = mysql_query($sSQL) or print(mysql_error());
$productindb=mysql_num_rows($result)>0;
$disabledsection=FALSE;
if($productindb){
	$rs = mysql_fetch_array($result);
	$sectionid=$rs['pSection'];
	if(trim(@$_GET['cat']) != '' && is_numeric(@$_GET['cat']) && trim(@$_GET['cat']) != '0') $sectionid = $_GET['cat'];
	$index=0;
	while($index<10 && $sectionid!=0 && $sectionid!=$catalogroot){
		$sSQL = "SELECT sectionDisabled,topSection FROM sections WHERE sectionID=" . $sectionid;
		$result2 = mysql_query($sSQL) or print(mysql_error());
		if($rs2 = mysql_fetch_array($result2)){
			if($rs2['sectionDisabled']>$minloglevel) $disabledsection=TRUE;
			$sectionid=$rs2['topSection'];
		}
		$index++;
		mysql_free_result($result2);
	}
}
if((! $productindb && $prodid!=$giftcertificateid && $prodid!=$donationid) || $disabledsection){
	print '<p align="center">&nbsp;<br />'.$xxSryNA.'<br />&nbsp;</p>';
}else{
	$prodoptions='';
	if($prodid!=$giftcertificateid && $prodid!=$donationid){
		if(trim(@$_GET['prod']) != '' && $rs['pStaticPage'] != 0 && @$redirecttostatic==TRUE){
			ob_end_clean();
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'],'/')).'/'. cleanforurl($rs[getlangid('pName',1)]) . '.php');
			exit;
		}
		$hasstaticpage = ($rs['pStaticPage'] != 0);
		$catid = $rs['pSection'];
		if(trim(@$_GET['cat']) != '' && is_numeric(@$_GET['cat']) && trim(@$_GET['cat']) != '0') $catid = $_GET['cat'];
		if(trim(@$_GET['cat']) != '' && is_numeric(@$_GET['cat']) && trim(@$_GET['cat']) != '0') $thecatid = $_GET['cat']; else $thecatid='';
		$thetopts = $catid;
		$topsectionids = $catid;
		$isrootsection=FALSE;
		for($index=0; $index <= 10; $index++){
			if($thetopts==0){
				if($catid=="0")
					$tslist = $xxHome . " " . $tslist;
				else
					$tslist = '<a class="ectlink" href="categories.php">' . $xxHome . '</a> ' . $tslist;
				break;
			}elseif($index==10){
				$tslist = '<strong>Loop</strong>' . $tslist;
			}else{
				$sSQL = "SELECT sectionID,topSection,".getlangid("sectionName",256).",rootSection,".getlangid('sectionurl',2048)." FROM sections WHERE sectionID=" . $thetopts;
				$result2 = mysql_query($sSQL) or print(mysql_error());
				if(mysql_num_rows($result2) > 0){
					$rs2 = mysql_fetch_assoc($result2);
					if($rs2[getlangid('sectionurl',2048)] != '')
						$tslist = ' &raquo; <a class="ectlink" href="' . $rs2[getlangid('sectionurl',2048)] . '">' . $rs2[getlangid("sectionName",256)] . "</a>" . $tslist;
					elseif($rs2["rootSection"]==1)
						$tslist = ' &raquo; <a class="ectlink" href="products.php?cat=' . getcatid($rs2['sectionID'],$rs2[getlangid('sectionName',256)]) . '">' . $rs2[getlangid("sectionName",256)] . "</a>" . $tslist;
					else
						$tslist = ' &raquo; <a class="ectlink" href="categories.php?cat=' . getcatid($rs2['sectionID'],$rs2[getlangid('sectionName',256)]) . '">' . $rs2[getlangid("sectionName",256)] . "</a>" . $tslist;
					$thetopts = $rs2["topSection"];
					$topsectionids .= "," . $thetopts;
				}else{
					$tslist = "Top Section Deleted" . $tslist;
					break;
				}
				mysql_free_result($result2);
			}
		}
		$nextid='';
		$previousid='';
		$sectionids = getsectionids($catid, FALSE);
		$sSortBy='';
		if(@$sortBy==2 || @$sortBy==5){
		}elseif(@$sortBy==3 || @$sortBy==4){
			$sSortBy = $TWSP;
			$sSortValue = $rs['pPrice'];
		}elseif(@$sortBy==6 || @$sortBy==7){
			$sSortBy = 'pOrder';
			$sSortValue = $rs['pOrder'];
		}elseif(@$sortBy==8 || @$sortBy==9){
			$sSortBy = 'pDateAdded';
			$sSortValue = "'".$rs['pDateAdded']."'";
		}else{
			$sSortBy = getlangid('pName',1);
			$sSortValue = "'".escape_string($rs[getlangid('pName',1)])."'";
		}
		if(@$sortBy==4 || @$sortBy==7 || @$sortBy==9) $isdesc=TRUE; else $isdesc=FALSE;
		if(@$nopreviousnextlinks!=TRUE){
			$sSQL = "SELECT products.pId,".getlangid('pName',1).",pStaticPage,products.pSection FROM products LEFT JOIN multisections ON products.pId=multisections.pId WHERE (products.pSection IN (" . $sectionids . ") OR multisections.pSection IN (" . $sectionids . "))" . (($useStockManagement && @$noshowoutofstock==TRUE) ? ' AND (pInStock>0 OR pStockByOpts<>0)' : '') . " AND pDisplay<>0 AND " . ($sSortBy!= '' ? '(('.$sSortBy.'='.$sSortValue." AND products.pId > '" . escape_string($prodid) . "') OR " . $sSortBy . ($isdesc ? '<' : '>') . $sSortValue . ')' : "products.pId > '" . escape_string($prodid) . "'") . " ORDER BY " . ($sSortBy!='' ? $sSortBy . ($isdesc ? ' DESC,' : ' ASC,') : '') . "products.pId ASC LIMIT 1";
			$result2 = mysql_query($sSQL) or print(mysql_error());
			if($rs2=mysql_fetch_assoc($result2)){
				$nextid = urlencode($rs2['pId']);
				$nextidname = $rs2[getlangid('pName',1)];
				$nextidstatic = $rs2['pStaticPage'];
				$nextidcat = $rs2['pSection'];
			}
			mysql_free_result($result2);
			$sSQL = "SELECT products.pId,".getlangid('pName',1).",pStaticPage,products.pSection FROM products LEFT JOIN multisections ON products.pId=multisections.pId WHERE (products.pSection IN (" . $sectionids . ") OR multisections.pSection IN (" . $sectionids . "))" . (($useStockManagement && @$noshowoutofstock==TRUE) ? ' AND (pInStock>0 OR pStockByOpts<>0)' : '') . " AND pDisplay<>0 AND " . ($sSortBy!= '' ? '(('.$sSortBy.'='.$sSortValue." AND products.pId < '" . escape_string($prodid) . "') OR " . $sSortBy . ($isdesc ? '>' : '<') . $sSortValue . ')' : "products.pId < '" . escape_string($prodid) . "'") . " ORDER BY " . ($sSortBy!='' ? $sSortBy . ($isdesc ? ' ASC,' : ' DESC,') : '') . "products.pId DESC LIMIT 1";
			$result2 = mysql_query($sSQL) or print(mysql_error());
			if($rs2=mysql_fetch_assoc($result2)){
				$previousid = urlencode($rs2['pId']);
				$previousidname = $rs2[getlangid('pName',1)];
				$previousidstatic = $rs2['pStaticPage'];
				$previousidcat = $rs2['pSection'];
			}
			mysql_free_result($result2);
		}
		$extraimages=0;
		$giantimages=0;
		if(@$currencyseparator=='') $currencyseparator=' ';
		productdisplayscript(TRUE,TRUE);
		if(@$perproducttaxrate==TRUE && ! is_null($rs['pTax'])) $thetax = $rs['pTax']; else $thetax = $countryTaxRate;
		updatepricescript(TRUE,$thetax,TRUE); ?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */<?php
$liscript = '';
if(trim($rs['pGiantImage2'])!=''){
	$liscript .= "pIX[999]=0;pIM[999]='";
	$liscript .= $rs['pGiantImage'].'|';
	$giantimages=1;
	for($index=2; $index<=5; $index++){
		if(trim($rs['pGiantImage'.$index])!=''){ $liscript .= $rs['pGiantImage'.$index].'|'; $giantimages++; }
	}
	$liscript .= "';";
}
if(@$giantimageinpopup==TRUE){
	print 'liscript = "var pIM = new Array();var pIX = new Array();' . $liscript . '";';
	print "liscript += \"function updateprodimage(theitem,isnext){var imlist=pIM[theitem].split('\\|');if(isnext) pIX[theitem]++; else pIX[theitem]--;if(pIX[theitem]<0) pIX[theitem]=imlist.length-2;if(pIX[theitem]>imlist.length-2) pIX[theitem]=0;document.getElementById('prodimage'+theitem).onload=function(){doresize(document.getElementById('prodimage'+theitem));};document.getElementById('prodimage'+theitem).src=imlist[pIX[theitem]];document.getElementById('extraimcnt'+theitem).innerHTML=pIX[theitem]+1;return false;}\";";
}else
	print $liscript . "\r\n";
?>
function showgiantimage(imgname){
<?php
	if(@$giantimageinpopup==TRUE){
		if(@$giantimagepopupwidth=='') $giantimagepopupwidth=450;
		if(@$giantimagepopupheight=='') $giantimagepopupheight=600;
		print 'var winwid='.$giantimagepopupwidth.';var winhei='.$giantimagepopupheight.";\r\n"; ?>
scrwid=screen.width; scrhei=screen.height;
var newwin = window.open("","popupimage",'menubar=no,scrollbars=no,width='+winwid+',height='+winhei+',left='+((scrwid-winwid)/2)+',top=100,directories=no,location=no,resizable=yes,status=yes,toolbar=no');
newwin.document.open();
newwin.document.write('<html><head><title>Image PopUp</title><style type="text/css">body { margin:0px;font-family:Tahoma; }</style><' + 'script language="javascript" type="text/javascript">function doresize(tim){window.moveTo(('+scrwid+'-(tim.width+44))/2,Math.max(('+scrhei+'-30)-(tim.height+130),0)/2);window.resizeTo(tim.width+44,tim.height+130);};' + liscript + '<' + '/script></head><body onload="doresize(document.getElementById(\'prodimage999\'))" >');
newwin.document.write('<p align="center"><table border="0" cellspacing="1" cellpadding="1" align="center">');
<?php	if(trim($rs['pGiantImage2'])!=''){ ?>
newwin.document.write('<tr><td align="center" colspan="3"><img border="0" src="images/leftimage.gif" onclick="return updateprodimage(\'999\', false);" onmouseover="this.style.cursor=\'pointer\'" alt="<?php print $xxPrev?>" style="vertical-align:middle;margin:0px;" /> <span id="extraimcnt999">1</span> <?php print $xxOf.' '.$giantimages?> <img border="0" src="images/rightimage.gif" onclick="return updateprodimage(\'999\', true);" onmouseover="this.style.cursor=\'pointer\'" alt="<?php print $xxNext?>" style="vertical-align:middle;margin:0px;" /></td></tr>');
<?php	}else{ ?>
newwin.document.write('<tr><td align="center" colspan="3">&nbsp;</td></tr>');
<?php	} ?>
newwin.document.write('<tr><td align="center" colspan="3"><img id="prodimage999" class="giantimage prodimage" src="<?php print $rs['pGiantImage']?>" border="0" alt="<?php print str_replace(array("'",'"'), array("\\'",'&quot;'), strip_tags($rs[getlangid('pName',1)]))?>" <?php if(trim($rs['pGiantImage2'])!='') print 'onclick="return updateprodimage(\\\'999\\\', true);" onmouseover="this.style.cursor=\\\'pointer\\\'"'; ?> /></td></tr>');
<?php	if(trim($rs['pGiantImage2'])!=''){ ?>
newwin.document.write('<tr><td align="left"><img border="0" src="images/leftimage.gif" onclick="return updateprodimage(\'999\', false);" onmouseover="this.style.cursor=\'pointer\'" alt="<?php print $xxPrev?>" style="vertical-align:middle;margin:0px;" /></td><td align="center">&nbsp;</td><td align="right"><img border="0" src="images/rightimage.gif" onclick="return updateprodimage(\'999\', true);" onmouseover="this.style.cursor=\'pointer\'" alt="<?php print $xxNext?>" style="vertical-align:middle;margin:0px;" /></td></tr>');
<?php	} ?>
newwin.document.write('</table></p></body></html>');
newwin.document.close();
newwin.focus();
<?php
	}else{ ?>
document.getElementById('giantimgspan').style.display='';
document.getElementById('mainbodyspan').style.display='none';
document.getElementById('prodimage999').src=imgname;
<?php
	} ?>
}
function hidegiantimage(){
document.getElementById('giantimgspan').style.display='none';
document.getElementById('mainbodyspan').style.display='';
return(false);
}
/* ]]> */
</script>
	  <table id="giantimgspan" border="0" cellspacing="0" cellpadding="0" width="50%" align="center" style="display:none">
	    <tr><td><strong><span class="giantimgname detailname"><?php print $rs[getlangid('pName',1)] . ' </span> <span class="giantimgback">(<a class="ectlink" href="' . detailpageurl($thecatid!='' ? 'cat='.$thecatid : '') . '" onclick="javascript:return hidegiantimage();" >' . $xxRvBack . '</a>)</span>'; ?></strong><br />&nbsp;</td></tr>
		<tr>
		  <td>
		  <table border="0" cellspacing="1" cellpadding="1" align="center">
<?php	if(trim($rs['pGiantImage2'])!=''){ ?>
			<tr><td colspan="2" align="center"><img border="0" src="images/leftimage.gif" onclick="return updateprodimage('999', false);" onmouseover="this.style.cursor='pointer'" alt="<?php print $xxPrev?>" style="vertical-align:middle;margin:0px;" /> <span class="extraimage extraimagenum" id="extraimcnt999">1</span> <span class="extraimage"><?php print $xxOf . ' ' . $giantimages?></span> <img border="0" src="images/rightimage.gif" onclick="return updateprodimage('999', true);" onmouseover="this.style.cursor='pointer'" alt="<?php print $xxNext?>" style="vertical-align:middle;margin:0px;" /></td></tr>
<?php	} ?>
			<tr><td align="center" colspan="2"><img id="prodimage999" class="giantimage prodimage" src="images/clearpixel.gif" border="0" alt="<?php print str_replace('"', '&quot;', strip_tags($rs[getlangid('pName',1)]))?>" <?php if(trim($rs['pGiantImage2'])!='') print 'onclick="return updateprodimage(\'999\', true);" onmouseover="this.style.cursor=\'pointer\'"'; ?> style="margin:0px;" /></td></tr>
<?php	if(trim($rs['pGiantImage2'])!=''){ ?>
			<tr><td align="left"><img border="0" src="images/leftimage.gif" onclick="return updateprodimage('999', false);" onmouseover="this.style.cursor='pointer'" alt="<?php print $xxPrev?>" style="vertical-align:middle;margin:0px;" /></td><td align="right"><img border="0" src="images/rightimage.gif" onclick="return updateprodimage('999', true);" onmouseover="this.style.cursor='pointer'" alt="<?php print $xxNext?>" style="vertical-align:middle;margin:0px;" /></td></tr>
<?php	} ?>
		  </table>
		  </td>
		</tr>
	  </table>
<?php
	}else{
		$proddetailtopbuybutton=FALSE;
	}
	$optionshavestock=TRUE;
	if(is_array($prodoptions) && @$_REQUEST['review']==''){
		if(@$usedetailbodyformat==1 || @$usedetailbodyformat=='')
			$optionshtml = displayproductoptions('<strong><span class="detailoption">','</span></strong>',$optdiff,$thetax,TRUE,$hasmultipurchase,$optjs);
		else
			$optionshtml = displayproductoptions('<span class="detailoption">','</span>',$optdiff,$thetax,TRUE,$hasmultipurchase,$optjs);
	}
	if($prodid==$giftcertificateid || $prodid==$donationid)
		$isInStock = TRUE;
	elseif($useStockManagement)
		if($rs['pStockByOpts']!=0) $isInStock = $optionshavestock; else $isInStock = ((int)($rs['pInStock']) > 0);
	else
		$isInStock = ($rs['pSell'] != 0);
	if(@$recentlyviewed==TRUE && ! ($prodid==$giftcertificateid || $prodid==$donationid)){
		$tcnt=NULL;
		if(@$numrecentlyviewed=='') $numrecentlyviewed=6;
		$sSQL = "DELETE FROM recentlyviewed WHERE rvDate<'".date('Y-m-d', time()-(60*60*24*3))."'";
		mysql_query($sSQL) or print(mysql_error());
		$sSQL = "SELECT rvID FROM recentlyviewed WHERE rvProdID='".escape_string($prodid)."' AND " . (@$_SESSION['clientID']!='' ? 'rvCustomerID=' . escape_string(@$_SESSION['clientID']) : "(rvCustomerID=0 AND rvSessionID='".$thesessionid."')");
		$result2 = mysql_query($sSQL) or print(mysql_error());
		if(! ($rs2 = mysql_fetch_assoc($result2))){
			$sSQL = "INSERT INTO recentlyviewed (rvProdID,rvProdName,rvProdSection,rvProdURL,rvSessionID,rvCustomerID,rvDate) VALUES ('".escape_string($prodid)."','".escape_string($rs[getlangid('pName',1)])."',".(@$catid!=''?$catid:'0').",'".escape_string(detailpageurl((@$thecatid!=''?'cat='.$thecatid:'')))."','".$thesessionid."',".(@$_SESSION['clientID']!=''?$_SESSION['clientID']:0).",'".date('Y-m-d H:i:s')."')";
			mysql_query($sSQL) or print(mysql_error());
		}else{
			$sSQL = "UPDATE recentlyviewed SET rvDate='".date('Y-m-d H:i:s')."' WHERE rvID=".$rs2['rvID'];
			mysql_query($sSQL) or print(mysql_error());
		}
		mysql_free_result($result2);
		$sSQL = 'SELECT COUNT(*) AS tcnt FROM recentlyviewed WHERE ' . (@$_SESSION['clientID']!='' ? 'rvCustomerID=' . escape_string(@$_SESSION['clientID']) : "(rvCustomerID=0 AND rvSessionID='".$thesessionid."')");
		$result2 = mysql_query($sSQL) or print(mysql_error());
		if($rs2 = mysql_fetch_assoc($result2)) $tcnt=$rs2['tcnt'];
		mysql_free_result($result2);
		if(!is_null($tcnt)){
			if($tcnt>$numrecentlyviewed){
				$sSQL = 'SELECT rvID,MIN(rvDate) FROM recentlyviewed WHERE ' . (@$_SESSION['clientID']!='' ? 'rvCustomerID=' . escape_string(@$_SESSION['clientID']) : "(rvCustomerID=0 AND rvSessionID='".$thesessionid."')").' GROUP BY rvID';
				$result2 = mysql_query($sSQL) or print(mysql_error());
				if($rs2 = mysql_fetch_assoc($result2)){
					mysql_query('DELETE FROM recentlyviewed WHERE rvID='.$rs2['rvID']) or print(mysql_error());
				}
				mysql_free_result($result2);
			}
		}
	}
}
?>
