<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
$isproductspage=TRUE;
$iNumOfPages = 0;
if(! is_numeric(@$_GET['pg']) || strlen(@$_GET['pg'])>8)
	$CurPage = 1;
else
	$CurPage = max(1, (int)(@$_GET['pg']));
$alreadygotadmin = getadminsettings();
if(is_numeric(@$_GET['id'])) $catid = (int)(@$_GET['id']); else $catid = '';
if(trim(@$_GET['cat'])!='') $catid = unstripslashes(@$_GET['cat']);
if(trim(@$_GET['man'])!='') $manid = unstripslashes(@$_GET['man']); else $manid='';
if(@$manufacturerpageurl=='') $manufacturerpageurl='manufacturers.php';
$scrid=@$_GET['scri'];
if(!is_numeric($scrid)) $scrid='';
if(@$explicitid != '' && is_numeric(@$explicitid)) $catid=@$explicitid;
if(@$explicitmanid != '' && is_numeric(@$explicitmanid)) $manid=@$explicitmanid;
if(@$usecategoryname && $catid!=''){
	$sSQL = 'SELECT sectionID FROM sections WHERE '.getlangid('sectionName',256)."='".escape_string($catid)."'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs=mysql_fetch_assoc($result)){ $catname=$catid; $catid=$rs['sectionID']; }
	mysql_free_result($result);
}
if(@$usecategoryname && $manid!=''){
	$sSQL = "SELECT mfID FROM manufacturer WHERE mfName='".escape_string($manid)."'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs=mysql_fetch_assoc($result)){ $manname=$manid; $manid=$rs['mfID']; }
	mysql_free_result($result);
}
if(!is_numeric($catid)) $catid=$catalogroot;
if(($manid!='' && is_numeric($manid)) || @$manufacturers==TRUE) $manufacturers=TRUE; else{ $manufacturers=FALSE; $manid=''; }
$WSP = '';
$OWSP = '';
$TWSP = 'pPrice';
$sectionurl=htmlentities(strip_tags(@$_SERVER['PHP_SELF']));
get_wholesaleprice_sql();
if(@$filterpricebands=='') $filterpricebands=100;
if(@$xxFilUnd=='') $xxFilUnd='Under';
function getlike($fie,$t,$tjn){
	global $sNOTSQL;
	if(substr($t, 0, 1)=='-'){ // pSKU excluded to work around NULL problems
		if($fie!='pSKU') $sNOTSQL .= $fie." LIKE '%".substr($t, 1)."%' OR ";
	}else
		return $fie . " LIKE '%".$t."%' ".$tjn;
}
function sortline($soid, $sotext){
	global $sortoptions,$dosortby;
	if(($sortoptions & pow(2,($soid-1)))!=0) print '<option value="'.$soid.'"'.($dosortby==$soid?' selected="selected"':'').'>'.$sotext.'</option>';
}
$nofirstpg=TRUE;
$pblink = '<a class="ectlink" href="'.$sectionurl.'?';
foreach(@$_GET as $objQS => $objValue)
	if($objQS!='cat' && $objQS!='id' && $objQS!='pg') $pblink .= urlencode($objQS) . '=' . urlencode($objValue) . '&amp;';
if($catid!='0' && @$explicitid=='' && @$explicitmanid=='') $pblink .= 'cat=' . getcatid($catid,@$catname) . '&amp;pg='; else $pblink .= 'pg=';
function dofilterresults($numfcols){
	global $prodfilter,$prodfiltertext,$sectionurl,$xxGo,$xxSeaAll,$xxPlsSel,$imgfilterproducts,$manufacturers,$manid,$scrid,$filterpricebands,$sortoptions,$sectionids,$prodsperpage,$xxPerPag;
	global $sortoption1,$sortoption2,$sortoption3,$sortoption4,$sortoption5,$sortoption6,$sortoption7,$sortoption8,$sortoption9,$sortoption10,$countryTaxRate,$showtaxinclusive,$xxFilUnd,$TWSP;
	if($prodfilter!=0 && ! ($prodfilter==8 && $sortoptions==0)){
		if(($prodfilter & 1)==1 && ! $manufacturers){
			if(! is_numeric($manid)) $manid="";
			$manufaclist='';
			$sSQL = 'SELECT DISTINCT mfID,mfName FROM products INNER JOIN manufacturer ON products.pManufacturer=manufacturer.mfID WHERE pManufacturer<>0 ORDER BY mfName';
			if($sectionids!='') $sSQL = 'SELECT DISTINCT mfID,mfName FROM (products LEFT JOIN multisections ON products.pId=multisections.pId) INNER JOIN manufacturer ON products.pManufacturer=manufacturer.mfID WHERE pManufacturer<>0 AND (products.pSection IN (' . $sectionids . ') OR multisections.pSection IN (' . $sectionids . ')) ORDER BY mfName';
			$result2 = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result2)==0) $prodfilter-=1;
			while($rs2 = mysql_fetch_assoc($result2)){
				$manufaclist.='<option value="'.$rs2['mfID'].'"'.($rs2['mfID']==(int)$manid?' selected="selected"':'').'>' . htmlspecials($rs2['mfName']) . "</option>\r\n";
			}
			mysql_free_result($result2);
			if(strlen($manufaclist)>0) $manufaclist=substr($manufaclist,0,-1);
		}
		if(($prodfilter & 2)==2){
			$searchcriterialist='';
			$sSQL = 'SELECT DISTINCT scID,scName,scGroup,scOrder FROM products INNER JOIN searchcriteria ON products.pSearchCriteria=searchcriteria.scID WHERE pSearchCriteria<>0 ORDER BY scGroup,scOrder';
			if($sectionids!='') $sSQL = 'SELECT DISTINCT scID,scName,scGroup,scOrder FROM (products LEFT JOIN multisections ON products.pId=multisections.pId) INNER JOIN searchcriteria ON products.pSearchCriteria=searchcriteria.scID WHERE pSearchCriteria<>0 AND (products.pSection IN (' . $sectionids . ') OR multisections.pSection IN (' . $sectionids . ')) ORDER BY scGroup,scOrder';
			$result2 = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result2)==0) $prodfilter-=2;
			while($rs2 = mysql_fetch_assoc($result2)){
				$searchcriterialist.='<option value="'.$rs2['scID'].'"'.($rs2['scID']==(int)$scrid?' selected="selected"':'').'>' . htmlspecials($rs2['scName']) . "</option>\r\n";
			}
			mysql_free_result($result2);
			if(strlen($searchcriterialist)>0) $searchcriterialist=substr($searchcriterialist,0,-1);
		}
		$maxprice=$minprice=0;
		if(($prodfilter & 4)==4){
			$sSQL = 'SELECT MAX(' . $TWSP . ') AS maxprice,MIN(' . $TWSP . ') AS minprice FROM products WHERE pDisplay<>0';
			if($sectionids!='') $sSQL = 'SELECT MAX(' . $TWSP . ') AS maxprice,MIN(' . $TWSP . ') AS minprice FROM (products LEFT JOIN multisections ON products.pId=multisections.pId) WHERE pDisplay<>0 AND (products.pSection IN (' . $sectionids . ') OR multisections.pSection IN (' . $sectionids . '))';
			$result2 = mysql_query($sSQL) or print(mysql_error());
			if($rs2 = mysql_fetch_assoc($result2)){ if(! is_null($rs2['maxprice'])){ $maxprice=$rs2['maxprice']; $minprice=$rs2['minprice']; } }
			if(@$showtaxinclusive===2){ $maxprice+=$maxprice*($countryTaxRate/100.0); $minprice+=$minprice*($countryTaxRate/100.0); }
			mysql_free_result($result2);
		}
		$filtertext=explode('&',$prodfiltertext);
		for($index=0; $index<9; $index++){
			$filtertext[$index]=str_replace('%26','&',@$filtertext[$index]);
		}
		$filterurl='';
		$manfilterurl='';
		foreach(@$_GET as $objQS=>$objVal){
			if($objQS!='filter' && $objQS!='pg' && $objQS!='sortby' && $objQS!='perpage') $filterurl .= urlencode($objQS) . '=' . urlencode(unstripslashes($objVal)) . '&amp;';
			if($objQS!='filter' && $objQS!='pg' && $objQS!='sortby' && $objQS!='perpage' && $objQS!='sman' && $objQS!='scri' && $objQS!='sprice') $manfilterurl .= urlencode($objQS) . '=' . urlencode(unstripslashes($objVal)) . '&';
		}
		if($filterurl=='') $filterurl=$sectionurl.'?filter='; else $filterurl=$sectionurl.'?'.$filterurl.'filter=';
		if($manfilterurl=='') $manfilterurl=$sectionurl.'?'; else $manfilterurl=$sectionurl.'?'.$manfilterurl;
?>
			  <tr class="prodfilterbar">
				<td class="prodfilterbar" colspan="<?php print $numfcols?>">
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function filterbyman(caller){
var furl="<?php print str_replace(array('<','"'),array('','\\"'),$manfilterurl)?>";
if(document.getElementById('sman')){
	var smanobj=document.getElementById('sman');
	if(smanobj.selectedIndex!=0) furl+='sman='+smanobj[smanobj.selectedIndex].value+'&';
}
if(document.getElementById('scri')){
	var smanobj=document.getElementById('scri');
	if(smanobj.selectedIndex!=0) furl+='scri='+smanobj[smanobj.selectedIndex].value+'&';
}
if(document.getElementById('spriceobj')){
	var spriceobj=document.getElementById('spriceobj');
	if(spriceobj.selectedIndex!=0) furl+='sprice='+spriceobj[spriceobj.selectedIndex].value+'&';
}
if(document.getElementById('ectfilter')){
	if(document.getElementById('ectfilter').value!='')
		furl+='filter='+document.getElementById('ectfilter').value+'&';
}
document.location=furl.substr(0,furl.length-1);
}
/* ]]> */</script>
				  <table class="prodfilterbar"><tr>
<?php	if(($prodfilter & 1)==1){
			if($filtertext[0]!='') print '<td align="right" class="prodfilter" style="white-space:nowrap">'.$filtertext[0].'</td>';
				?><td class="prodfilter">
				<select name="sman" class="prodfilter" id="sman" size="1" onchange="filterbyman(1)"><option value=""><?php print $xxSeaAll?></option><?php print $manufaclist?></select></td>
<?php	}
		if(($prodfilter & 2)==2){ // Search Criteria
			if($filtertext[1]!='') print '<td align="right" class="prodfilter" style="white-space:nowrap">'.$filtertext[1].'</td>';
				?><td class="prodfilter">
				<select name="scri" class="prodfilter" id="scri" size="1" onchange="filterbyman(1)"><option value=""><?php print $xxSeaAll?></option><?php print $searchcriterialist?></select></td>
<?php	}
		if(($prodfilter & 4)==4){ // Price bands
			if($filtertext[2]!='') print '<td align="right" class="prodfilter" style="white-space:nowrap">'.$filtertext[2].'</td>';
			$rowcounter=2;
			$currpriceband=@$_GET['sprice'];
			?><td class="prodfilter"><select name="sprice" class="prodfilter" id="spriceobj" size="1" onchange="filterbyman(4)">
			<option value="0"><?php print $xxPlsSel?></option>
<?php		if($minprice==0 || $filterpricebands>=$minprice){ ?>
			<option value="1"<?php if($currpriceband=="1") print ' selected="selected"'?>><?php print $xxFilUnd.' '.FormatCurrencyZeroDP($filterpricebands)?></option>
<?php		}
			for($index=$filterpricebands; $index<$maxprice; $index+=$filterpricebands){
				if($minprice==0 || ($index+$filterpricebands)>=$minprice){ ?>
			<option value="<?php print $rowcounter?>"<?php if($currpriceband==$rowcounter) print ' selected="selected"'?>><?php print FormatCurrencyZeroDP($index)." - ".FormatCurrencyZeroDP($index+$filterpricebands)?></option>
<?php			}
				$rowcounter++;
				if($rowcounter>1000) break;
			} ?>
		  </select></td>
<?php	}
		if(($prodfilter & 8)==8 && $sortoptions!=0){
			if($filtertext[3]!='') print '<td align="right" class="prodfilter" style="white-space:nowrap">'.$filtertext[3].'</td>';
			?><td class="prodfilter"><select class="prodfilter" size="1" onchange="document.location='<?php print $filterurl?>'.replace(/filter=/,'sortby='+this[this.selectedIndex].value<?php if(($prodfilter & 32)==32) print "+'&filter='+document.getElementById('ectfilter').value"?>)">
			<option value="0"><?php print $xxPlsSel?></option>
<?php		sortline(1, @$sortoption1!=''?$sortoption1:'Sort Alphabetically');
			sortline(2, @$sortoption2!=''?$sortoption2:'Sort by Product ID');
			sortline(3, @$sortoption3!=''?$sortoption3:'Sort Price (Asc.)');
			sortline(4, @$sortoption4!=''?$sortoption4:'Sort Price (Desc.)');
			sortline(5, @$sortoption5!=''?$sortoption5:'Database Order');
			sortline(6, @$sortoption6!=''?$sortoption6:'Product Order');
			sortline(7, @$sortoption7!=''?$sortoption7:'Product Order (Desc.)');
			sortline(8, @$sortoption8!=''?$sortoption8:'Date Added (Asc.)');
			sortline(9, @$sortoption9!=''?$sortoption9:'Date Added (Desc.)');
			sortline(10, @$sortoption10!=''?$sortoption10:'Sort by Manufacturer');
?>		  </select></td>
<?php	}
		if(($prodfilter & 16)==16){
			if($filtertext[4]!='') print '<td align="right" class="prodfilter" style="white-space:nowrap">'.$filtertext[4].'</td>';
			?><td class="prodfilter"><select class="prodfilter" size="1" onchange="document.location='<?php print $filterurl?>'.replace(/filter=/,'perpage='+this[this.selectedIndex].value<?php if(($prodfilter & 32)==32) print "+'&filter='+document.getElementById('ectfilter').value"?>)">
<?php		for($index=1; $index<=5; $index++){
				print '<option value="'.$index.'"'.(@$_SESSION['perpage']==$index?' selected="selected"':'').'>'.($prodsperpage*$index).' '.$xxPerPag.'</option>';
			}
?>		  </select></td>
<?php	}
		if(($prodfilter & 32)==32){
			if($filtertext[5]!='') print '<td align="right" class="prodfilter" style="white-space:nowrap">'.$filtertext[5].'</td>';
			?><td class="prodfilter" style="white-space:nowrap"><input onkeydown="if(event&&event.keyCode==13)document.location='<?php print $filterurl?>'+this.value" type="text" class="prodfilter" size="20" id="ectfilter" name="filter" value="<?php print htmlspecials(@$_GET['filter'])?>" />
				<?php print imageorbutton(@$imgfilterproducts,$xxGo,"prodfilter","document.location='".$filterurl."'+document.getElementById('ectfilter').value",TRUE)?></td>
<?php	} ?>
				</tr></table></td>
			  </tr>
<?php
	}
}
if(@$orprodsperpage != '') $adminProdsPerPage=$orprodsperpage;
$prodsperpage=$adminProdsPerPage;
checkCurrencyRates($currConvUser,$currConvPw,$currLastUpdate,$currRate1,$currSymbol1,$currRate2,$currSymbol2,$currRate3,$currSymbol3);
$tslist = "";
$thetopts = $catid;
$topsectionids = $catid;
$isrootsection=FALSE;
$sectiondisabled=FALSE;
if(@$_SESSION['clientID']!='' && @$_SESSION['clientLoginLevel']!='') $minloglevel=$_SESSION['clientLoginLevel']; else $minloglevel=0;
if($manufacturers){
	$sSQL = "SELECT mfName FROM manufacturer WHERE mfID=" . $manid;
	$result2 = mysql_query($sSQL) or print(mysql_error());
	if($rs2 = mysql_fetch_assoc($result2)) $mfname=$rs2['mfName']; else $mfname='Not Found';
	mysql_free_result($result2);
	$tslist = '<a class="ectlink" href="'.$xxHomeURL.'">'.$xxHome.'</a> &raquo; <a class="ectlink" href="'.$manufacturerpageurl.'">'.$xxManuf.'</a> &raquo; ' . $mfname;
	if(@$explicitmanid!='') $sectionurl=htmlentities(@$_SERVER['PHP_SELF']);
	$isrootsection=TRUE;
}else{
	for($index=0; $index <= 10; $index++){
		if($thetopts==$catalogroot){
			$caturl=$xxHomeURL;
			if($catalogroot!=0){
				$sSQL = 'SELECT sectionID,topSection,'.getlangid('sectionName',256).',rootSection,sectionDisabled,'.getlangid('sectionurl',2048)." FROM sections WHERE sectionID='" . $catalogroot . "'";
				$result = mysql_query($sSQL) or print(mysql_error());
				if($rs = mysql_fetch_assoc($result)){
					$xxHome=$rs[getlangid('sectionName',256)];
					if(trim($rs[getlangid('sectionurl',2048)])!='') $caturl=$rs[getlangid('sectionurl',2048)];
				}
				mysql_free_result($result);
			}
			$tslist = '<a class="ectlink" href="'.$caturl.'">' . $xxHome . '</a> ' . $tslist;
			break;
		}elseif($index==10){
			$tslist = '<strong>Loop</strong>' . $tslist;
		}else{
			$sSQL = 'SELECT sectionID,topSection,'.getlangid('sectionName',256).',rootSection,sectionDisabled,'.getlangid('sectionurl',2048).' FROM sections WHERE sectionID=' . $thetopts;
			$result2 = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result2) > 0){
				$rs2 = mysql_fetch_assoc($result2);
				if($rs2['sectionID']==(int)$catid)$isrootsection = ($rs2['rootSection']==1);
				if($rs2['sectionDisabled']>$minloglevel) $catid=-1;
				if($rs2['sectionID']==(int)$catid && $isrootsection){
					$tslist = ' &raquo; ' . $rs2[getlangid('sectionName',256)] . $tslist;
					if(@$explicitid != '' && trim($rs2[getlangid('sectionurl',2048)]) != '') $sectionurl = trim($rs2[getlangid('sectionurl',2048)]);
					if(@$explicitid=='' && trim($rs2[getlangid('sectionurl',2048)]) != '' && @$redirecttostatic==TRUE){
						ob_end_clean();
						header('HTTP/1.1 301 Moved Permanently');
						if($rs2[getlangid('sectionurl',2048)]{0}=='/')$thelocation='http://'.$_SERVER['HTTP_HOST'].$rs2[getlangid('sectionurl',2048)];elseif(substr(strtolower($rs2[getlangid('sectionurl',2048)]),0,7) == 'http://')$thelocation=$rs2[getlangid('sectionurl',2048)];else $thelocation='http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'],'/')).'/'.$rs2[getlangid('sectionurl',2048)];
						header('Location: '.$thelocation);
						exit;
					}
				}elseif(trim($rs2[getlangid('sectionurl',2048)]) != ''){
					$tslist = ' &raquo; <a class="ectlink" href="' . $rs2[getlangid('sectionurl',2048)] . '">' . $rs2[getlangid('sectionName',256)] . '</a>' . $tslist;
					if(@$explicitid != '' && $rs2['sectionID']==(int)$catid) $sectionurl = trim($rs2[getlangid('sectionurl',2048)]);
				}elseif($rs2['rootSection']==1)
					$tslist = ' &raquo; <a class="ectlink" href="products.php?cat=' . getcatid($rs2['sectionID'],$rs2[getlangid('sectionName',256)]) . '">' . $rs2[getlangid('sectionName',256)] . '</a>' . $tslist;
				else
					$tslist = ' &raquo; <a class="ectlink" href="categories.php?cat=' . getcatid($rs2['sectionID'],$rs2[getlangid('sectionName',256)]) . '">' . $rs2[getlangid('sectionName',256)] . '</a>' . $tslist;
				$thetopts = $rs2['topSection'];
				$topsectionids .= ',' . $thetopts;
			}else{
				$tslist = 'Top Section Deleted ' . $tslist;
				break;
			}
			mysql_free_result($result2);
		}
	}
}
if(! $isrootsection && @$xxAlProd != '') $tslist .= ' &raquo; ' . $xxAlProd;
$filtersql='';
if(@$_GET['filter']!=''){
	$Xstext = escape_string(substr($_GET['filter'], 0, 1024));
	$aText = explode(' ',$Xstext);
	$aFields[0]='products.pId';
	$aFields[1]=getlangid('pName',1);
	$aFields[2]=getlangid('pDescription',2);
	$aFields[3]=getlangid('pLongDescription',4);
	$aFields[4]='pSKU';
	$aFields[5]='pSearchParams';
	$sNOTSQL = ''; $sYESSQL = '';
	foreach($aText as $theopt){
		$tmpSQL = '';
		$arrelms=count($aText);
		for($index=0;$index<=5;$index++){
			if(is_array($theopt))$theopt=$theopt[0];
			if(! ((@$nosearchdescription==TRUE && $index==2) || (@$nosearchlongdescription==TRUE && $index==3) || (@$nosearchsku==TRUE && $index==4) || (@$nosearchparams==TRUE && $index==5)))
				$tmpSQL .= getlike($aFields[$index], $theopt, 'OR ');
		}
		if($tmpSQL!='') $sYESSQL.= '(' . substr($tmpSQL, 0, strlen($tmpSQL)-3) . ') ';
		if($tmpSQL!='') $sYESSQL .= 'AND ';
	}
	$sYESSQL = substr($sYESSQL, 0, -4);
	if($sYESSQL!='') $filtersql = ' AND (' . $sYESSQL . ') ';
	if($sNOTSQL!='') $filtersql .= ' AND NOT (' . substr($sNOTSQL, 0, strlen($sNOTSQL)-4) . ')';
}
if(@$_GET['sprice']!='' && is_numeric(@$_GET['sprice'])){
	$priceband=(int)@$_GET['sprice'];
	$taxlevel=1;
	if(@$showtaxinclusive===2) $taxlevel+=($countryTaxRate/100.0);
	$filtersql .= ' AND ((' . $TWSP . '*'.$taxlevel.')>=' . (($priceband-1)*$filterpricebands) . ' AND (' . $TWSP . '*'.$taxlevel.')<=' . ($priceband*$filterpricebands) . ')';
}
if(($prodfilter & 1)==1 && ! $manufacturers){
	$manid=trim(@$_GET['sman']);
	if(! is_numeric($manid)) $manid="";
}
$sectionids='';
if($catid==$catalogroot){
	$result2 = mysql_query('SELECT sectionID FROM sections WHERE sectionDisabled>'.$minloglevel.' LIMIT 0,1') or print(mysql_error());
	if($rs2 = mysql_fetch_assoc($result2)) $disabledsections=TRUE; else $disabledsections=FALSE;
	mysql_free_result($result2);
	if($disabledsections)
		$sSQL = 'SELECT products.pId FROM products INNER JOIN sections ON products.pSection=sections.sectionID WHERE sectionDisabled<='.$minloglevel.' AND pDisplay<>0'.$filtersql;
	else
		$sSQL = 'SELECT products.pId FROM products WHERE pDisplay<>0'.$filtersql;
}else{
	$sectionids = getsectionids($catid, FALSE);
	$sSQL = 'SELECT DISTINCT products.pId FROM products LEFT JOIN multisections ON products.pId=multisections.pId WHERE pDisplay<>0'.$filtersql.' AND (products.pSection IN (' . $sectionids . ') OR multisections.pSection IN (' . $sectionids . '))';
}
if($manid!='0' && $manid!='') $sSQL .= ' AND pManufacturer=' . $manid;
if($scrid!='') $sSQL .= " AND pSearchCriteria=" . $scrid;
if($useStockManagement && @$noshowoutofstock==TRUE) $sSQL .= ' AND (pInStock>0 OR pStockByOpts<>0)';
if(@$_REQUEST['sortby']!='' && is_numeric(@$_REQUEST['sortby'])) $_SESSION['sortby']=(int)$_REQUEST['sortby'];
if(@$_SESSION['sortby']!='') $dosortby=$_SESSION['sortby'];
if(@$orsortby!='') $dosortby=$orsortby;
if(@$_REQUEST['perpage']!='' && is_numeric(@$_REQUEST['perpage'])) $_SESSION['perpage']=(int)@$_REQUEST['perpage'];
if(@$_SESSION['perpage']!='' && is_numeric(@$_SESSION['perpage'])) $adminProdsPerPage=(int)$_SESSION['perpage']*$prodsperpage;
if($adminProdsPerPage>1000) $adminProdsPerPage=$prodsperpage;
if(@$dosortby==2)
	$sSortBy = ' ORDER BY products.pId';
elseif(@$dosortby==3)
	$sSortBy = ' ORDER BY '.$TWSP.',pId';
elseif(@$dosortby==4)
	$sSortBy = ' ORDER BY '.$TWSP.' DESC,pId';
elseif(@$dosortby==5)
	$sSortBy = '';
elseif(@$dosortby==6)
	$sSortBy = ' ORDER BY pOrder,pId';
elseif(@$dosortby==7)
	$sSortBy = ' ORDER BY pOrder DESC,pId';
elseif(@$dosortby==8)
	$sSortBy = ' ORDER BY pDateAdded,pId';
elseif(@$dosortby==9)
	$sSortBy = ' ORDER BY pDateAdded DESC,pId';
elseif(@$dosortby==10)
	$sSortBy = ' ORDER BY pManufacturer';
else
	$sSortBy = ' ORDER BY '.getlangid('pName',1);
if(strpos($sSQL,"DISTINCT"))
	$tmpSQL = preg_replace("/DISTINCT products.pId/","COUNT(DISTINCT products.pId) AS bar",$sSQL, 1);
else
	$tmpSQL = preg_replace("/products.pId/","COUNT(*) AS bar",$sSQL, 1);
$allprods = mysql_query($tmpSQL) or print(mysql_error());
$iNumOfPages = ceil(mysql_result($allprods,0,"bar")/$adminProdsPerPage);
mysql_free_result($allprods);
$sSQL .=  $sSortBy . " LIMIT " . ($adminProdsPerPage*($CurPage-1)) . ", $adminProdsPerPage";
$allprods = mysql_query($sSQL) or print(mysql_error());
if(mysql_num_rows($allprods) > 0){
	$prodlist = "";
	$addcomma="";
	while($rs = mysql_fetch_array($allprods)){
		$prodlist .= $addcomma . "'" . $rs["pId"] . "'";
		$addcomma=",";
	}
	mysql_free_result($allprods);
	$wantmanufacturer = (@$manufacturerfield!='' || (@$useproductbodyformat==3 && strpos(@$cpdcolumns, 'manufacturer')!==FALSE));
	$sSQL = 'SELECT pId,pSKU,'.getlangid('pName',1).','.$WSP.'pPrice,pListPrice,pSection,pSell,pStockByOpts,pStaticPage,pInStock,pExemptions,pTax,pTotRating,pNumRatings,'.($wantmanufacturer?'mfName,':'').(@$shortdescriptionlimit===0?"'' AS ":'').getlangid('pDescription',2).','.getlangid('pLongDescription',4).' FROM products '.($wantmanufacturer?'LEFT OUTER JOIN manufacturer on products.pManufacturer=manufacturer.mfID ':'').'WHERE pId IN (' . $prodlist . ')' . $sSortBy;
	$allprods = mysql_query($sSQL) or print(mysql_error());
}
$Count = 0;
?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td colspan="3" width="100%">
<?php
if(@$useproductbodyformat==3)
	include "./vsadmin/inc/incproductbody3.php";
elseif(@$useproductbodyformat==2)
	include "./vsadmin/inc/incproductbody2.php";
else
	include "./vsadmin/inc/incproductbody.php";
mysql_free_result($allprods); ?>
		  </td>
        </tr>
      </table>
