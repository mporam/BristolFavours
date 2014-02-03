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
$emailfriendlink='';
if(@$useemailfriend) $emailfriendlink='<a class="ectlink emailfriend" rel="nofollow" href="javascript:openEFWindow(\''.urlencode($prodid).'\',false)"><strong>'.$xxEmFrnd.'</strong></a>';
if(@$emailfriendseparator=='') $emailfriendseparator=(@$usedetailbodyformat==1 || @$usedetailbodyformat=='' ? '<br /><hr class="efseparator" />' : ' | ');
if(@$useaskaquestion) $emailfriendlink.=($emailfriendlink==''?'':$emailfriendseparator) . '<a class="ectlink emailfriend" rel="nofollow" href="javascript:openEFWindow(\''.urlencode($prodid).'\',true)"><strong>'.$xxAskQue.'</strong></a>';
$useemailfriend = @$useemailfriend || @$useaskaquestion;
function displaytabs($thedesc){
	global $ecttabs,$ecttabsspecials,$reviewsshown,$prodid,$languageid,$enablecustomerratings,$relatedtabtemplate,$shortdescriptionlimit,$xxDescr,$relatedproductsbothways,$defaultdescriptiontab,$showtaxinclusive,$thetax,$ratingslanguages,$WSP;
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
					$sSQL = 'SELECT pId,pSection,'.getlangid('pName',1).','.$WSP.'pPrice,pStaticPage,pExemptions,'.getlangid('pDescription',2)." FROM products INNER JOIN relatedprods ON products.pId=relatedprods.rpRelProdID WHERE pDisplay<>0 AND rpProdID='".$prodid."'";
					if(@$relatedproductsbothways==TRUE) $sSQL .= ' UNION SELECT pId,pSection,'.getlangid('pName',1).','.$WSP.'pPrice,pStaticPage,pExemptions,'.getlangid('pDescription',2)." FROM products INNER JOIN relatedprods ON products.pId=relatedprods.rpProdID WHERE pDisplay<>0 AND rpRelProdID='".$prodid."'";
					$result = mysql_query($sSQL) or print(mysql_error());
					if(mysql_num_rows($result)==0)
						$hascontent=FALSE;
					else{
						while($rs2 = mysql_fetch_assoc($result)){
							$rpsmallimage=''; $rplargeimage='';
							$sSQL = "SELECT imageSrc,imageType FROM productimages WHERE imageProduct='" . $rs2['pId'] . "' AND (imageType=0 OR imageType=1) AND imageNumber=0";
							$result3 = mysql_query($sSQL) or print(mysql_error());
							while($rs3 = mysql_fetch_assoc($result3)){
								if($rs3['imageType']==0) $rpsmallimage=$rs3['imageSrc']; else $rplargeimage=$rs3['imageSrc'];
							}
							mysql_free_result($result3);
							if($rs2['pStaticPage'] != 0){
								$thedetailslink = cleanforurl($rs2[getlangid('pName',1)]) . '.php' . (@$catid != '' && @$catid != '0' && $catid != $rs2['pSection'] && @$nocatid != TRUE ? '?cat=' . $catid : '');
								$startlink='<a class="ectlink" href="' . $thedetailslink . '">';
								$endlink='</a>';
							}elseif(@$detailslink != ''){
								$startlink=str_replace('%pid%', $rs2['pId'], str_replace('%largeimage%', $rplargeimage, $detailslink));
								$endlink=@$detailsendlink;
							}else{
								$startlink='<a class="ectlink" href="'. 'proddetail.php?prod=' . urlencode($rs2['pId']) . (@$catid != '' && @$catid != '0' && $catid != $rs2['pSection'] && @$nocatid != TRUE ? '&amp;cat=' . $catid : '') .'">';
								$endlink='</a>';
							}
							$rtc = str_replace('%img%', ($rpsmallimage!='' ? $startlink . '<img class="reltabimage" src="' . $rpsmallimage . '" border="0" alt="'.htmlspecials(strip_tags($rs2[getlangid('pName',1)])).'" />' . $endlink : '&nbsp;'), $relatedtabtemplate);
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
						$displaytabs.='<img src="'.$dimage.'" alt="'.htmlspecials($dtitle).'" border="0" ';
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
	global $Count,$rs,$xxPrev,$xxNext,$xxEnlrge,$xxOf,$extraimages,$magictoolbox,$magictooloptionsjs,$magictooloptions,$magic360images,$allimages,$allgiantimages,$numallimages,$numallgiantimages,$psmallimage,$thumbnailstyle,$magictouchid;
	if(@$thumbnailstyle=='') $thumbnailstyle='width:75px;padding:3px';
	if(is_array($allimages)){
		if(@$magictoolbox!='' && is_array($allgiantimages)){
			print '<script src="' . ($magictoolbox=='MagicTouch' ? 'http://www.magictoolbox.com/mt/' . $magictouchid . '/magictouch.js' : strtolower($magictoolbox) . '/' . strtolower($magictoolbox) . '.js') . '" type="text/javascript"></script>' . @$magictooloptionsjs;
			if($magictoolbox=='MagicSlideshow' || $magictoolbox=='MagicScroll'){
				print '<div class="' . $magictoolbox . '">';
				for($index=0;$index<$numallimages;$index++){
					print '<img src="' . $allimages[$index]['imageSrc'] . '" alt="" />';
				}
				print '</div>';
			}elseif($magictoolbox=='magic360plus' || $magictoolbox=='magic360'){
				$anchorstr = '<a class="' . $magictoolbox . '" href="#" rel="' . $allgiantimages[0]['imageSrc'];
				$imagesstr = '<img src="' . $allimages[0]['imageSrc'] . '" alt="' . strip_tags($rs[getlangid('pName',1)]) . '" rel="' . $allimages[0]['imageSrc'];
				if(@$magic360images=='') $magic360images=18;
				for($magind=2;$magind<=$magic360images;$magind++){
					if($magictoolbox=='magic360plus') $anchorstr.= '*' . str_replace('01', ($magind<10 ? '0' . $magind : $magind), $allgiantimages[0]['imageSrc']);
					$imagesstr.= '*' . str_replace('01', ($magind<10 ? '0' . $magind : $magind), $allimages[0]['imageSrc']);
				}
				if($magictoolbox=='magic360plus') print $anchorstr . '">' . $imagesstr . '" /></a>'; else print $imagesstr . '" class="' . $magictoolbox . '" />';
			}elseif($magictoolbox=='MagicZoom' || $magictoolbox=='MagicZoomPlus' || $magictoolbox=='MagicTouch' || $magictoolbox=='MagicMagnify' || $magictoolbox=='MagicMagnifyPlus' || $magictoolbox=='MagicThumb'){
				if($numallimages>1) print '<table class="detailimage" border="0" cellspacing="1" cellpadding="1"><tr><td class="mainimage">';
				print '<a href="' . $allgiantimages[0]['imageSrc'] . '" class="' . $magictoolbox . '" ' . @$magictooloptions . ' id="zoom1"><img class="prodimage" src="' . $allimages[0]['imageSrc'] . '" border="0" alt="' . strip_tags($rs[getlangid('pName',1)]) . '" /></a>';
				if($magictoolbox=="MagicThumb") $relid="thumb-id:"; else $relid="";
				if($magictoolbox=="MagicZoom" || $magictoolbox=="MagicZoomPlus") $relid="zoom-id:";
				if($numallimages>1){
					print '</td></tr><tr><td class="thumbnailimage" align="center">';
					for($index=0;$index<$numallimages;$index++){
						if($index < $numallgiantimages) print '<a href="' . $allgiantimages[$index]['imageSrc'] . '" rev="' . $allimages[$index]['imageSrc'] . '" rel="' . $relid . 'zoom1"><img src="' . $allimages[$index]['imageSrc'] . '" style="' . $thumbnailstyle . '" border="0" alt="" /></a>';
					}
					print '</td></tr></table>';
				}
			}else
				print 'Magic Toolbox Option Not Recognized : ' . $magictoolbox . '<br />';
		}else{
			if($numallimages>1 || is_array($allgiantimages)) print '<table class="detailimage" border="0" cellspacing="1" cellpadding="1"><tr><td class="mainimage">';
			print '<img id="prodimage'.$Count.'" class="prodimage" src="' . $allimages[0]['imageSrc'] . '" border="0" alt="'.htmlspecials(strip_tags($rs[getlangid('pName',1)])).'" />';
			if($numallimages>1 || is_array($allgiantimages)) print '</td></tr><tr><td class="imagenavigator" align="center">'.($numallimages>1 ? '<img border="0" src="images/leftimage.gif" onclick="return updateprodimage('.$Count.', false);" onmouseover="this.style.cursor=\'pointer\'" alt="'.$xxPrev.'" style="vertical-align:middle;margin:0px;" />' : '&nbsp;').' '.($numallimages>1 ? '<span class="extraimage extraimagenum" id="extraimcnt'.$Count.'">1</span> <span class="extraimage">'.$xxOf.' '.$extraimages.'</span> ' : '').(is_array($allgiantimages) ? '<span class="extraimage">(<a class="ectlink" href="javascript:showgiantimage(\'' . $allgiantimages[0]['imageSrc'] . '\')">'.$xxEnlrge.'</a>)</span>' : '') . ' '.($numallimages>1 ? '<img border="0" src="images/rightimage.gif" onclick="return updateprodimage('.$Count.', true);" onmouseover="this.style.cursor=\'pointer\'" alt="'.$xxNext.'" style="vertical-align:middle;margin:0px;" />' : '&nbsp;').'</td></tr></table>';
		}
	}elseif($psmallimage!=''){
		print '<img id="prodimage'.$Count.'" class="prodimage" src="' . $psmallimage . '" border="0" alt="'.htmlspecials(strip_tags($rs[getlangid('pName',1)])).'" />';
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
	$pblink = '<a class="vrectlink" href="'.htmlentities(@$_SERVER['PHP_SELF']).'?';
	foreach(@$_GET as $objQS => $objValue)
		if($objQS!='cat' && $objQS!='id' AND $objQS!='pg') $pblink .= urlencode($objQS) . '=' . urlencode($objValue) . '&amp;';
	if($catid != '0' && @$explicitid=='') $pblink .= 'cat=' . $catid . '&amp;pg='; else $pblink .= 'pg=';
	if($showall && $iNumOfPages > 1) $srv .= '<tr><td align="center">' . writepagebar($CurPage,$iNumOfPages,$xxPrev,$xxNext,$pblink,TRUE) . '</td></tr>';
	return($srv);
}
$alreadygotadmin = getadminsettings();
$thesessionid=getsessionid();
checkCurrencyRates($currConvUser,$currConvPw,$currLastUpdate,$currRate1,$currSymbol1,$currRate2,$currSymbol2,$currRate3,$currSymbol3);
$disabledsection=FALSE;
$psmallimage='';
$allimages='';
$numallimages=0;
$allgiantimages='';
$numallgiantimages=0;
$sSQL = "SELECT imageSrc FROM productimages WHERE imageType=0 AND imageProduct='" . escape_string($prodid) . "' ORDER BY imageNumber LIMIT 0,1";
$result2 = mysql_query($sSQL) or print(mysql_error());
if($rs2 = mysql_fetch_assoc($result2)) $psmallimage=$rs2['imageSrc'];
mysql_free_result($result2);
$sSQL = "SELECT imageSrc FROM productimages WHERE imageType=1 AND imageProduct='" . escape_string($prodid) . "' ORDER BY imageNumber";
$result2 = mysql_query($sSQL) or print(mysql_error());
while($rs2 = mysql_fetch_assoc($result2)) $allimages[$numallimages++]=$rs2;
mysql_free_result($result2);
$sSQL = "SELECT imageSrc FROM productimages WHERE imageType=2 AND imageProduct='" . escape_string($prodid) . "' ORDER BY imageNumber";
$result2 = mysql_query($sSQL) or print(mysql_error());
while($rs2 = mysql_fetch_assoc($result2)) $allgiantimages[$numallgiantimages++]=$rs2;
mysql_free_result($result2);
$sSQL = 'SELECT pId,pSKU,'.getlangid('pName',1).','.getlangid('pDescription',2).','.$WSP.'pPrice,pSection,pListPrice,pSell,pStockByOpts,pStaticPage,pInStock,pExemptions,'.(@$detailslink!='' ? "'' AS " : '').'pTax,pOrder,pDateAdded,'.(@$manufacturerfield!=''?'mfName,':'').getlangid('pLongDescription',4).' FROM products '.(@$manufacturerfield!=''?'LEFT OUTER JOIN manufacturer on products.pManufacturer=manufacturer.mfID ':'')."WHERE pDisplay<>0 AND pId='" . escape_string($prodid) . "'";
$result = mysql_query($sSQL) or print(mysql_error());
$productindb=mysql_num_rows($result)>0;
if($productindb){
	$rs = mysql_fetch_array($result);
	$sectionid=$rs['pSection'];
	// if(trim(@$_GET['cat']) != '' && is_numeric(@$_GET['cat']) && trim(@$_GET['cat']) != '0') $sectionid = $_GET['cat'];
	// $index=0;
	// while($index<10 && $sectionid!=0 && $sectionid!=$catalogroot){
		$sSQL = "SELECT sectionDisabled,topSection FROM sections WHERE sectionID=" . $sectionid;
		$result2 = mysql_query($sSQL) or print(mysql_error());
		if($rs2 = mysql_fetch_array($result2)){
			if($rs2['sectionDisabled']>$minloglevel) $disabledsection=TRUE;
			// $sectionid=$rs2['topSection'];
		}
		// $index++;
		mysql_free_result($result2);
	// }
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
			if($thetopts==$catalogroot){
				$caturl=$xxHomeURL;
				if($catalogroot!=0){
					$sSQL = 'SELECT sectionID,topSection,'.getlangid('sectionName',256).',rootSection,sectionDisabled,'.getlangid('sectionurl',2048)." FROM sections WHERE sectionID='" . $catalogroot . "'";
					$result2 = mysql_query($sSQL) or print(mysql_error());
					if($rs2 = mysql_fetch_assoc($result2)){
						$xxHome=$rs2[getlangid('sectionName',256)];
						if(trim($rs2[getlangid('sectionurl',2048)])!='') $caturl=$rs2[getlangid('sectionurl',2048)];
					}
					mysql_free_result($result2);
				}
				$tslist = '<a class="ectlink" href="'.$caturl.'">' . $xxHome . '</a> ' . $tslist;
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
		if(@$currencyseparator=='') $currencyseparator=' ';
		productdisplayscript(TRUE,TRUE);
		if(@$perproducttaxrate==TRUE && ! is_null($rs['pTax'])) $thetax = $rs['pTax']; else $thetax = $countryTaxRate;
		updatepricescript(TRUE,$thetax,TRUE);
		if(@$inlinepopups==TRUE) emailfriendjavascript(); ?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */<?php
$liscript = '';
if($numallgiantimages>1){
	$liscript .= "pIX[999]=0;pIM[999]='";
	$liscript .= $allgiantimages[0]['imageSrc'].'|';
	for($index=1; $index<$numallgiantimages; $index++){
		$liscript .= $allgiantimages[$index]['imageSrc'].'|';
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
<?php	if($numallgiantimages>1){ ?>
newwin.document.write('<tr><td align="center" colspan="3"><img border="0" src="images/leftimage.gif" onclick="return updateprodimage(\'999\', false);" onmouseover="this.style.cursor=\'pointer\'" alt="<?php print $xxPrev?>" style="vertical-align:middle;margin:0px;" /> <span id="extraimcnt999">1</span> <?php print $xxOf.' '.$numallgiantimages?> <img border="0" src="images/rightimage.gif" onclick="return updateprodimage(\'999\', true);" onmouseover="this.style.cursor=\'pointer\'" alt="<?php print $xxNext?>" style="vertical-align:middle;margin:0px;" /></td></tr>');
<?php	}else{ ?>
newwin.document.write('<tr><td align="center" colspan="3">&nbsp;</td></tr>');
<?php	}
		if($numallgiantimages>0){ ?>
newwin.document.write('<tr><td align="center" colspan="3"><img id="prodimage999" class="giantimage prodimage" src="<?php print $allgiantimages[0]['imageSrc']?>" border="0" alt="<?php print str_replace(array("'",'"'), array("\\'",'&quot;'), strip_tags($rs[getlangid('pName',1)]))?>" <?php if($numallgiantimages>1) print 'onclick="return updateprodimage(\\\'999\\\', true);" onmouseover="this.style.cursor=\\\'pointer\\\'"'; ?> /></td></tr>');
<?php	}
		if($numallgiantimages>1){ ?>
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
	  <table id="giantimgspan" border="0" cellspacing="0" cellpadding="0" width="98%" align="center" style="display:none">
	    <tr><td><strong><span class="giantimgname detailname"><?php print $rs[getlangid('pName',1)] . ' </span> <span class="giantimgback">(<a class="ectlink" href="' . detailpageurl($thecatid!='' ? 'cat='.$thecatid : '') . '" onclick="javascript:return hidegiantimage();" >' . $xxRvBack . '</a>)</span>'; ?></strong><br />&nbsp;</td></tr>
		<tr>
		  <td>
		  <table border="0" cellspacing="1" cellpadding="1" align="center">
<?php	if($numallgiantimages>1){ ?>
			<tr><td colspan="2" align="center"><img border="0" src="images/leftimage.gif" onclick="return updateprodimage('999', false);" onmouseover="this.style.cursor='pointer'" alt="<?php print $xxPrev?>" style="vertical-align:middle;margin:0px;" /> <span class="extraimage extraimagenum" id="extraimcnt999">1</span> <span class="extraimage"><?php print $xxOf . ' ' . $numallgiantimages?></span> <img border="0" src="images/rightimage.gif" onclick="return updateprodimage('999', true);" onmouseover="this.style.cursor='pointer'" alt="<?php print $xxNext?>" style="vertical-align:middle;margin:0px;" /></td></tr>
<?php	} ?>
			<tr><td align="center" colspan="2"><img id="prodimage999" class="giantimage prodimage" src="images/clearpixel.gif" border="0" alt="<?php print str_replace('"', '&quot;', strip_tags($rs[getlangid('pName',1)]))?>" <?php if($numallgiantimages>1) print 'onclick="return updateprodimage(\'999\', true);" onmouseover="this.style.cursor=\'pointer\'"'; ?> style="margin:0px;" /></td></tr>
<?php	if($numallgiantimages>1){ ?>
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
	$theuagent=strtolower(@$_SERVER['HTTP_USER_AGENT']);
	if(@$recentlyviewed==TRUE){
		if(strpos($theuagent,'bingbot')!==FALSE || strpos($theuagent,'crawler')!==FALSE || strpos($theuagent,'exabot')!==FALSE || strpos($theuagent,'ezooms')!==FALSE ||
			strpos($theuagent,'googlebot')!==FALSE || strpos($theuagent,'gulliver')!==FALSE || strpos($theuagent,'ia_archiver')!==FALSE || strpos($theuagent,'infoseek')!==FALSE ||
			strpos($theuagent,'inktomi')!==FALSE || strpos($theuagent,'mj12bot')!==FALSE || strpos($theuagent,'scooter')!==FALSE || strpos($theuagent,'speedy spider')!==FALSE || strpos($theuagent,'yahoo!')!==FALSE ||
			strpos($theuagent,'yandexbot')!==FALSE)
			$recentlyviewed=FALSE;
	}
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
?>
	  <table id="mainbodyspan" border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
		<tr> 
		  <td width="100%">
			<form method="post" name="tForm<?php print $Count?>" id="ectform0" action="<?php if($prodid==$giftcertificateid || $prodid==$donationid) print str_replace('"','',strip_tags(@$_SERVER['REQUEST_URI'])); else print "cart.php"?>" onsubmit="return formvalidator<?php print $Count?>(this)" style="margin:0px;padding:0px;">
<?php if(! (@isset($showcategories) && @$showcategories==FALSE)){ ?>
			<table width="100%" border="0" cellspacing="3" cellpadding="3">
			  <tr> 
				<td class="prodnavigation" colspan="3" align="left" valign="top"><?php print $sstrong . '<p class="prodnavigation">' . $tslist . '<br /><img src="images/clearpixel.gif" width="300" height="8" alt="" /></p>' . $estrong?></td>
				<td align="right" valign="top">&nbsp;<?php
				if($isInStock && @$proddetailtopbuybutton==TRUE && @$nobuyorcheckout != TRUE && trim(@$_REQUEST['review'])=='') print imageorbutton(@$imgbuybutton,$xxAddToC,'buybutton','return subformid('.$Count.')', TRUE) . '&nbsp;';
				if(@$nobuyorcheckout != TRUE) print imageorbutton($imgcheckoutbutton,$xxCOTxt,'checkoutbutton','cart.php', FALSE)?></td>
			  </tr>
			</table>
<?php }
	$alldiscounts = '';
	if(@$nowholesalediscounts==TRUE && @$_SESSION["clientUser"]!='')
		if((($_SESSION["clientActions"] & 8) == 8) || (($_SESSION["clientActions"] & 16) == 16)) $noshowdiscounts=TRUE;
	if(@$noshowdiscounts != TRUE && $prodid!=$giftcertificateid && $prodid!=$donationid){
		$sSQL = "SELECT DISTINCT ".getlangid("cpnName",1024)." FROM coupons LEFT OUTER JOIN cpnassign ON coupons.cpnID=cpnassign.cpaCpnID WHERE cpnNumAvail>0 AND cpnEndDate>='" . date("Y-m-d",time()) ."' AND cpnIsCoupon=0 AND " .
			"((cpnSitewide=1 OR cpnSitewide=2) OR (cpnSitewide=0 AND cpaType=2 AND cpaAssignment='" . $rs['pId'] . "') " .
			"OR ((cpnSitewide=0 OR cpnSitewide=3) AND cpaType=1 AND cpaAssignment IN ('" . str_replace(',',"','",$topsectionids) . "')))" .
			' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
		$result2 = mysql_query($sSQL) or print(mysql_error());
		while($rs2=mysql_fetch_assoc($result2))
			$alldiscounts .= $rs2[getlangid("cpnName",1024)] . "<br />";
		mysql_free_result($result2);
	}
	if(@$enablecustomerratings==TRUE && @$_POST['review']=='true'){
		$hitlimit = FALSE;
		print '<table border="0" cellspacing="2" cellpadding="2" width="100%" align="center">';
		$sSQL = "SELECT COUNT(*) as thecount FROM ratings WHERE rtDate='" . date('Y-m-d', time()) . "' AND rtIPAddress='" . escape_string(getipaddress()) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs2 = mysql_fetch_assoc($result)){
			if(@$dailyratinglimit=='') $dailyratinglimit=10;
			if(! is_null($rs2['thecount'])){
				if($rs2['thecount']>$dailyratinglimit) $hitlimit=TRUE;
			}
		}
		mysql_free_result($result);
		$theip = @$_SERVER['REMOTE_ADDR'];
		if($theip=='') $theip = 'none';
		if($theip=='none' || ip2long($theip)==FALSE)
			$sSQL = 'SELECT dcid FROM ipblocking LIMIT 0,1';
		else
			$sSQL = 'SELECT dcid FROM ipblocking WHERE (dcip1=' . ip2long($theip) . ' AND dcip2=0) OR (dcip1 <= ' . ip2long($theip) . ' AND ' . ip2long($theip) . ' <= dcip2 AND dcip2 <> 0)';
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result) > 0)
			$hitlimit = TRUE;
		$referer = @$_SERVER['HTTP_REFERER'];
		$host = @$_SERVER['HTTP_HOST'];
		if(strpos($referer, $host)===FALSE){
			print '<tr><td align="center">Sorry but your review could not be sent at this time.</td></tr>';
		}elseif($hitlimit)
			print '<tr><td>'.$xxRvLim.'</td></tr>';
		elseif(@$onlyclientratings && @$_SESSION['clientID']=='')
			print '<tr><td align="center">Only logged in customers can review products.</td></tr>';
		elseif(is_numeric(@$_POST['ratingstars']) && trim(@$_POST['reviewposter'])!='' && trim(@$_POST['reviewheading'])!=''){
			$sSQL = 'INSERT INTO ratings (rtProdID,rtRating,rtPosterName,rtHeader,rtIPAddress,rtApproved,rtLanguage,rtDate,rtPosterLoginID,rtComments) VALUES (';
			$sSQL .= "'" . escape_string(strip_tags($prodid)) . "',";
			$sSQL .= "'" . (is_numeric(@$_POST['ratingstars']) ? escape_string((int)@$_POST['ratingstars'] * 2) : 0) . "',";
			$sSQL .= "'" . escape_string(strip_tags(unstripslashes(@$_POST['reviewposter']))) . "',";
			$sSQL .= "'" . escape_string(strip_tags(unstripslashes(@$_POST['reviewheading']))) . "',";
			$sSQL .= "'" . escape_string(strip_tags(unstripslashes(getipaddress()))) . "',";
			$sSQL .= '0,';
			if(@$languageid!='') $sSQL .= ((int)$languageid-1).','; else $sSQL .= '0,';
			$sSQL .= "'" . date('Y-m-d', time()) . "',";
			$sSQL .= (@$_SESSION['clientID']!='' ? @$_SESSION['clientID'] : 0) . ',';
			$sSQL .= "'" . escape_string(strip_tags(unstripslashes(@$_POST['reviewcomments']))) . "')";
			mysql_query($sSQL) or print(mysql_error());
			print '<tr><td align="center">&nbsp;<br />&nbsp;<br />'.$xxRvThks.'<br />&nbsp;<br />&nbsp;';
			print $xxRvRet.' <a class="ectlink" href="' . detailpageurl($thecatid!='' ? 'cat='.$thecatid : '') . '">' . $xxClkHere . '</a>';
			print '<br />&nbsp;<br />&nbsp;';
			print '<meta http-equiv="Refresh" content="3; URL=' . detailpageurl($thecatid!='' ? 'cat='.$thecatid : '') . '">';
			print '</td></tr>';
		}
		print '</table>';
	}elseif(@$enablecustomerratings==TRUE && @$_GET['review']=='all'){
		print '<table border="0" cellspacing="2" cellpadding="2" width="100%" align="center">';
		print '<tr><td>';
		if($psmallimage!='')
			print '<img align="middle" id="prodimage0" class="prodimage" src="'.str_replace('%s','',$psmallimage).'" border="0" alt="'.strip_tags($rs[getlangid('pName',1)]).'" />&nbsp;';
		print '<span class="review reviewsforprod">'.$xxRvRevP.' - </span><span class="review reviewprod">' . $rs[getlangid('pName',1)] . '</span> <span class="review reviewback">(<a class="ectlink" href="' . detailpageurl($thecatid!='' ? 'cat='.$thecatid : '') . '">' . $xxRvBack . '</a>)</span><br />&nbsp;</td></tr>';
		$sSQL = "SELECT rtID,rtRating,rtPosterName,rtHeader,rtDate,rtComments FROM ratings WHERE rtApproved<>0 AND rtProdID='" . escape_string($prodid) . "'";
		if(@$ratingslanguages!='') $sSQL .= ' AND rtLanguage+1 IN ('.$ratingslanguages.')'; elseif(@$languageid!='') $sSQL .= ' AND rtLanguage='.((int)$languageid-1); else $sSQL .= ' AND rtLanguage=0';
		if(@$_GET['ro']=='1')
			$sSQL .= ' ORDER BY rtRating DESC';
		elseif(@$_GET['ro']=='2')
			$sSQL .= ' ORDER BY rtRating';
		elseif(@$_GET['ro']=='3')
			$sSQL .= ' ORDER BY rtDate';
		else
			$sSQL .= ' ORDER BY rtDate DESC';
		print showreviews($sSQL,TRUE);
		print '</table>';
	}elseif(@$enablecustomerratings==TRUE && @$_GET['review']=='true'){
		print '<table border="0" cellspacing="2" cellpadding="2" width="100%" align="center">';
		print '<tr><td><span class="review reviewing">'.$xxRvAreR.' - </span><span class="review reviewprod">' . $rs[getlangid('pName',1)] . '</span> <span class="review reviewback">(<a class="ectlink" href="' . detailpageurl($thecatid!='' ? 'cat='.$thecatid : '') . '">' . $xxRvBack . '</a>)</span><br />&nbsp;</td></tr>';
		print '</table>';
	}elseif($prodid==$giftcertificateid || $prodid==$donationid){
		$isincluded = TRUE;
		include './vsadmin/inc/incspecials.php';
	}elseif(@$usedetailbodyformat==1 || @$usedetailbodyformat==''){ ?>
			<table width="100%" border="0" cellspacing="3" cellpadding="3">
			  <tr> 
				<td width="100%" colspan="4" class="detail"> 
<?php				if(@$showproductid==TRUE) print '<div class="detailid"><strong>' . $xxPrId . ':</strong> ' . $rs['pId'] . '</div>';
					if(@$manufacturerfield!='' && ! is_null($rs['mfName'])) print '<div class="prodmanufacturer detailmanufacturer"><strong>' . $manufacturerfield . ':</strong> ' . $rs['mfName'] . '</div>';
					if(@$showproductsku!='' && $rs['pSKU']!='') print '<div class="detailsku"><strong>' . $showproductsku . ':</strong> ' . $rs['pSKU'] . '</div>';
					print $sstrong . '<div class="detailname">' . $rs[getlangid('pName',1)] . $xxDot;
					if($alldiscounts!='') print ' ' . (@$nomarkup?'':'<font color="#FF0000">') . '<span class="discountsapply detaildiscountsapply">' . $xxDsApp . '</span>' . (@$nomarkup?'':'</font>') . '</div>' . $estrong . '<div class="detaildiscounts"' . (@$nomarkup?'':' style="font-size:11px;color:#FF0000;"') . '>' . $alldiscounts . '</div>'; else print '</div>' . $estrong;
					if($useStockManagement && @$showinstock==TRUE){ if((int)$rs["pStockByOpts"]==0) print '<div class="prodinstock detailinstock"><strong>' . $xxInStoc . ':</strong> ' . $rs["pInStock"] . '</div>'; } ?>
				</td>
			  </tr>
			  <tr> 
				<td width="100%" colspan="4" align="center" class="detailimage"><?php showdetailimages(); ?></td>
			  </tr>
			  <tr> 
				<td width="100%" colspan="4" class="detaildescription"><?php
				$longdesc = trim($rs[getlangid("pLongDescription",4)]);
				if($longdesc != '')
					print '<div class="detaildescription">' . displaytabs($longdesc) . '</div>';
				elseif(trim($rs[getlangid("pDescription",2)])!='')
					print '<div class="detaildescription">' . $rs[getlangid("pDescription",2)] . '</div>';
				else
					print '&nbsp;';
				print '&nbsp;<br />';
if(is_array($prodoptions)){
	$rs['pPrice'] += $optdiff;
	if($optionshtml!='') print '<div class="detailoptions" align="center"><table class="prodoptions detailoptions" border="0" cellspacing="1" cellpadding="1">' . $optionshtml . '</table></div>';
	if($optjs!='') print '<script language="javascript" type="text/javascript">/* <![CDATA[ */'.$optjs.'/* ]]> */</script>';
}
?>  			</td>
			  </tr>
			  <tr>
				<td width="20%"><?php if(@$useemailfriend) print $emailfriendlink; else print '&nbsp;' ?></td>
				<td width="60%" align="center" colspan="2"><?php
					if(@$noprice==TRUE){
						print '&nbsp;';
					}else{
						if((double)$rs['pListPrice']!=0.0) print '<div class="detaillistprice">' . str_replace('%s', FormatEuroCurrency($rs['pListPrice']), $xxListPrice) . '</div>';
						print '<div class="detailprice"><strong>' . $xxPrice . ':</strong> <span class="price" id="pricediv' . $Count . '">' . ($rs['pPrice']==0 && @$pricezeromessage != '' ? $pricezeromessage : FormatEuroCurrency(@$showtaxinclusive===2 && ($rs['pExemptions'] & 2)!=2 ? $rs['pPrice']+($rs['pPrice']*$thetax/100.0) : $rs['pPrice'])) . '</span> ';
						if(@$showtaxinclusive==1 && ($rs['pExemptions'] & 2)!=2) printf('<span id="taxmsg' . $Count . '"' . ($rs['pPrice']==0 ? ' style="display:none"' : '') . '>' . $ssIncTax . '</span>','<span id="pricedivti' . $Count . '">' . ($rs['pPrice']==0 ? '-' : FormatEuroCurrency($rs['pPrice']+($rs['pPrice']*$thetax/100.0))) . '</span> ');
						print '</div>';
						$extracurr = '';
						if($currRate1!=0 && $currSymbol1!='') $extracurr = str_replace('%s',number_format($rs['pPrice']*$currRate1,checkDPs($currSymbol1),$orcdecimals,$orcthousands),$currFormat1) . $currencyseparator;
						if($currRate2!=0 && $currSymbol2!='') $extracurr .= str_replace('%s',number_format($rs['pPrice']*$currRate2,checkDPs($currSymbol2),$orcdecimals,$orcthousands),$currFormat2) . $currencyseparator;
						if($currRate3!=0 && $currSymbol3!='') $extracurr .= str_replace('%s',number_format($rs['pPrice']*$currRate3,checkDPs($currSymbol3),$orcdecimals,$orcthousands),$currFormat3);
						if($extracurr!='') print '<div class="detailcurrency"><span class="extracurr" id="pricedivec' . $Count . '">' . ($rs['pPrice']==0 ? '' : $extracurr) . '</span></div>';
					} ?>
				</td><td width="20%" align="right">
<?php
if(@$nobuyorcheckout == TRUE)
	print '&nbsp;';
else{
	if($rs['pPrice']==0 && @$nosellzeroprice==TRUE){
		print '&nbsp;';
	}elseif($isInStock){
		writehiddenvar('id', $rs['pId']);
		writehiddenvar('mode', 'add');
		if($wishlistondetail) writehiddenvar('listid', '');
		if(@$showquantondetail && $hasmultipurchase==0) print '<table><tr><td align="center"><input type="text" name="quant" size="2" maxlength="5" value="1" alt="'.$xxQuant.'" />' . (@$showquantondetail && $hasmultipurchase==0 ? '</td><td align="center">' : '');
		if(@$custombuybutton!='') print $custombuybutton; else print imageorsubmit(@$imgbuybutton,$xxAddToC,'buybutton');
		if($wishlistondetail) print '<br />' . imageorlink(@$imgaddtolist,$xxAddLis,'gtid='.$Count.';return displaysavelist(event,window)',TRUE);
		if(@$showquantondetail && $hasmultipurchase==0) print '</td></tr></table>';
	}else{
		if(@$notifybackinstock)
			print imageorlink(@$imgnotifyinstock, $xxNotBaS, "return notifyinstock(false,'".str_replace("'","\\'",$rs['pId'])."','".str_replace("'","\\'",$rs['pId'])."',".($rs['pStockByOpts']!=0&&!@$optionshavestock?'-1':'0').")", TRUE);
		else
			print '<strong>'.$xxOutStok.'</strong>';
	}
}			?></td>
			</tr>
<?php
if($previousid != '' || $nextid != ''){
	print '<tr><td align="center" colspan="4" class="pagenums"><p class="pagenums">&nbsp;<br />';
	writepreviousnextlinks();
	print '</p></td></tr>';
} ?>
			</table>
<?php }else{ // if($usedetailbodyformat==2) ?>
			<table width="100%" border="0" cellspacing="3" cellpadding="3">
			  <tr> 
				<td width="30%" align="center" class="detailimage"><?php showdetailimages(); ?></td>
				<td>&nbsp;</td>
				<td width="70%" valign="top" class="detail"> 
<?php			if(is_array($prodoptions)){
					$rs['pPrice'] += $optdiff;
				}
				if(@$showproductid==TRUE) print '<div class="detailid"><strong>' . $xxPrId . ':</strong> ' . $rs['pId'] . '</div>';
				if(@$manufacturerfield!='' && ! is_null($rs['mfName'])) print '<div class="prodmanufacturer detailmanufacturer"><strong>' . $manufacturerfield . ':</strong> ' . $rs['mfName'] . '</div>';
				if(@$showproductsku!='' && $rs['pSKU']!='') print '<div class="detailsku"><strong>' . $showproductsku . ':</strong> ' . $rs['pSKU'] . '</div>';
				print $sstrong . '<div class="detailname">' . $rs[getlangid('pName',1)] . $xxDot;
				if($alldiscounts!='') print ' ' . (@$nomarkup?'':'<font color="#FF0000">') . '<span class="discountsapply detaildiscountsapply">' . $xxDsApp . '</span>' . (@$nomarkup?'':'</font>') . '</div>' . $estrong . '<div class="detaildiscounts"' . (@$nomarkup?'':' style="font-size:11px;color:#FF0000;"') . '>' . $alldiscounts . '</div>'; else print '</div>' . $estrong;
				if($useStockManagement && @$showinstock==TRUE){ if((int)$rs["pStockByOpts"]==0) print '<div class="prodinstock detailinstock"><strong>' . $xxInStoc . ':</strong> ' . $rs["pInStock"] . '</div>'; }
				print '<br />';
				$longdesc = trim($rs[getlangid('pLongDescription',4)]);
				if(@$usedetailbodyformat==3){
				}elseif($longdesc!='')
					print '<div class="detaildescription">' . displaytabs($longdesc) . '</div>';
				elseif(trim($rs[getlangid('pDescription',2)])!='')
					print '<div class="detaildescription">' . $rs[getlangid('pDescription',2)] . '</div>';
				if(@$noprice==TRUE){
					print '&nbsp;';
				}else{
					if((double)$rs['pListPrice']!=0.0) print '<div class="detaillistprice">' . str_replace('%s', FormatEuroCurrency($rs['pListPrice']), $xxListPrice) . '</div>';
					print '<div class="detailprice"><strong>' . $xxPrice . ':</strong> <span class="price" id="pricediv' . $Count . '">' . ($rs['pPrice']==0 && @$pricezeromessage != '' ? $pricezeromessage : FormatEuroCurrency(@$showtaxinclusive===2 && ($rs['pExemptions'] & 2)!=2 ? $rs['pPrice']+($rs['pPrice']*$thetax/100.0) : $rs['pPrice'])) . '</span> ';
					if(@$showtaxinclusive==1 && ($rs['pExemptions'] & 2)!=2) printf('<span id="taxmsg' . $Count . '"' . ($rs['pPrice']==0 ? ' style="display:none"' : '') . '>' . $ssIncTax . '</span>','<span id="pricedivti' . $Count . '">' . ($rs['pPrice']==0 ? '-' : FormatEuroCurrency($rs['pPrice']+($rs['pPrice']*$thetax/100.0))) . '</span> ');
					print '</div>';
					$extracurr = '';
					if($currRate1!=0 && $currSymbol1!='') $extracurr = str_replace('%s',number_format($rs['pPrice']*$currRate1,checkDPs($currSymbol1),$orcdecimals,$orcthousands),$currFormat1) . $currencyseparator;
					if($currRate2!=0 && $currSymbol2!='') $extracurr .= str_replace('%s',number_format($rs['pPrice']*$currRate2,checkDPs($currSymbol2),$orcdecimals,$orcthousands),$currFormat2) . $currencyseparator;
					if($currRate3!=0 && $currSymbol3!='') $extracurr .= str_replace('%s',number_format($rs['pPrice']*$currRate3,checkDPs($currSymbol3),$orcdecimals,$orcthousands),$currFormat3);
					if($extracurr!='') print '<div class="detailcurrency"><span class="extracurr" id="pricedivec' . $Count . '">' . ($rs['pPrice']==0 ? '' : $extracurr) . '</span></div>';
					print '<hr width="80%" />';
				}
if(is_array($prodoptions)){
	if($optjs!='') print '<script language="javascript" type="text/javascript">/* <![CDATA[ */'.$optjs.'/* ]]> */</script>';
	if($prodoptions[0]['optType']==4) $thestyle=''; else $thestyle=' width="100%"';
	if($optionshtml!='') print '<div class="detailoptions" align="center"><table class="prodoptions detailoptions" border="0" cellspacing="1" cellpadding="1"'.$thestyle.'>' . $optionshtml;
	if($isInStock && @$nobuyorcheckout != true && (@$showquantondetail==TRUE || ! @isset($showquantondetail)) && $hasmultipurchase==0){ ?>
	<tr><td align="right"><?php print $xxQuant?>:</td><td align="left"><input type="text" name="quant" size="4" maxlength="5" value="1" alt="<?php print $xxQuant?>" /></td></tr><?php
	}
	if($optionshtml!='') print '</table></div>';
}else{
	if($isInStock && @$nobuyorcheckout != true && (@$showquantondetail==TRUE || ! @isset($showquantondetail))){ ?>
	<table border="0" cellspacing="1" cellpadding="1" width="100%">
	<tr><td align="right"><?php print $xxQuant?>:</td><td><input type="text" name="quant" size="4" maxlength="5" value="1" alt="<?php print $xxQuant?>" /></td></tr>
	</table>
<?php
	}
}
?>
<p align="center">
<?php
if(@$nobuyorcheckout == TRUE)
	print '&nbsp;';
else{
	if($rs['pPrice']==0 && @$nosellzeroprice==TRUE){
		print '&nbsp;';
	}elseif($isInStock){
		writehiddenvar('id', $rs['pId']);
		writehiddenvar('mode', 'add');
		if($wishlistondetail) writehiddenvar('listid', '');
		if(@$custombuybutton != '') print $custombuybutton; else print imageorsubmit(@$imgbuybutton,$xxAddToC,'buybutton');
		if($wishlistondetail) print '<br />' . imageorlink(@$imgaddtolist,$xxAddLis,'gtid='.$Count.';return displaysavelist(event,window)',TRUE);
	}else{
		if(@$notifybackinstock)
			print imageorlink(@$imgnotifyinstock, $xxNotBaS, "return notifyinstock(false,'".str_replace("'","\\'",$rs['pId'])."','".str_replace("'","\\'",$rs['pId'])."',".($rs['pStockByOpts']!=0&&!@$optionshavestock?'-1':'0').")", TRUE);
		else
			print '<strong>'.$xxOutStok.'</strong>';
		print '<br />';
	}
}
if($previousid != '' || $nextid != ''){
	print '</p><p class="pagenums" align="center">';
	writepreviousnextlinks();
	print '<br />';
}
if(@$usedetailbodyformat==3 && @$useemailfriend) print '<br />' . $emailfriendlink;
?></p><hr width="80%" />
<?php	if(@$usedetailbodyformat==2 && @$useemailfriend) print '<p align="center">' . $emailfriendlink . '</p>' ?>
</td>
			</tr><?php
				if(@$usedetailbodyformat==2){
				}elseif($longdesc!='')
					print '<tr><td colspan="3" class="detaildescription"><div class="detaildescription">' . displaytabs($longdesc) . '</div></td></tr>';
				elseif(trim($rs[getlangid('pDescription',2)])!='')
					print '<tr><td colspan="3" class="detaildescription"><div class="detaildescription">' . $rs[getlangid('pDescription',2)] . '</div></td></tr>';
			?></table>
<?php } ?>
			</form>
          </td>
        </tr>
<?php
if(@$_POST['review']=='true' || @$_GET['review']=='all'){
	// Do nothing
}elseif(@$enablecustomerratings==TRUE && @$_GET['review']=='true'){
	if(@$onlyclientratings && @$_SESSION['clientID']=='')
		print '<tr><td align="center">Only logged in customers can review products.</td></tr>';
	else{ ?>
		<tr>
		  <td>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function checkratingform(frm){
if(frm.ratingstars.selectedIndex==0){
	alert("<?php print $xxRvPlsS?>.");
	frm.ratingstars.focus();
	return(false);
}
if(frm.reviewposter.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxRvPosb?>\".");
	frm.reviewposter.focus();
	return(false);
}
if(frm.reviewheading.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxRvHead?>\".");
	frm.reviewheading.focus();
	return(false);
}
return (true);
}
/* ]]> */
</script>
	<form method="post" action="<?php print detailpageurl($thecatid!='' ? 'cat='.$thecatid : '')?>" style="margin:0px;padding:0px;"  onsubmit="return checkratingform(this)">
	<input type="hidden" name="review" value="true" />
	  <table border="0" cellspacing="0" cellpadding="2" width="100%" align="center">
		<tr><td align="right"><span class="review reviewstar" style="color:#FF0000">*</span><span class="review reviewform"><?php print $xxRvRati?>:</span></td><td><select size="1" name="ratingstars" class="review reviewform"><option value=""><?php print $xxPlsSel?></option><?php
			for($index=1; $index<=5; $index++){
				print '<option value="'.$index.'">'.$index.' '.$xxStars.'</option>';
			} ?></select></td></tr>
		<tr><td align="right"><span class="review reviewstar" style="color:#FF0000">*</span><span class="review reviewform"><?php print $xxRvPosb?>:</span></td><td><input type="text" size="20" name="reviewposter" maxlength="64" value="<?php print htmlspecials(@$_SESSION['clientUser'])?>" class="review reviewform" /></td></tr>
<?php	if(FALSE){ ?>
		<tr><td align="right">Email:</td><td><input type="text" size="20" name="reviewemail" maxlength="64" class="review reviewform" /></td></tr>
<?php	} ?>
		<tr><td align="right"><span class="review reviewstar" style="color:#FF0000">*</span><span class="review reviewform"><?php print $xxRvHead?>:</span></td><td><input type="text" size="40" name="reviewheading" maxlength="253" class="review reviewform" /></td></tr>
		<tr><td align="right"><span class="review reviewform"><?php print $xxRvComm?>:</span></td><td><textarea name="reviewcomments" cols="38" rows="8" class="review reviewform"></textarea></td></tr>
		<tr><td align="right">&nbsp;</td><td><input type="submit" value="<?php print $xxSubmt?>" class="review reviewform" /></td></tr>
	  </table>
	</form>
		  </td>
		</tr>
<?php
	}
}elseif(@$enablecustomerratings==TRUE){
	$sSQL = "SELECT rtID,rtRating,rtPosterName,rtHeader,rtDate,rtComments FROM ratings WHERE rtApproved<>0 AND rtProdID='".escape_string($prodid)."'";
	if(@$ratingslanguages!='') $sSQL .= ' AND rtLanguage+1 IN ('.$ratingslanguages.')'; elseif(@$languageid!='') $sSQL .= ' AND rtLanguage='.((int)$languageid-1); else $sSQL .= ' AND rtLanguage=0';
	$sSQL .= ' ORDER BY rtDate DESC,rtRating DESC';
	if(! $reviewsshown && $productindb) print showreviews($sSQL,FALSE);
}
?></table><?php
} // EOF
mysql_free_result($result);
if($defimagejs!='') print '<script language="javascript" type="text/javascript">'.$defimagejs.'</script>'; ?>