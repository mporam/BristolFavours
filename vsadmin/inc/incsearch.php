<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$_SERVER['CONTENT_LENGTH'] != '' && $_SERVER['CONTENT_LENGTH'] > 10000) exit;
$iNumOfPages = 0;
$showcategories=FALSE;
$gotcriteria=FALSE;
$numcats=0;
$catid=0;
$nobox='';
$isrootsection=FALSE;
$topsectionids='0';
if(! is_numeric(@$_GET['pg']) || strlen(@$_GET['pg'])>8)
	$CurPage = 1;
else
	$CurPage = max(1, (int)($_GET['pg']));
if(@$_GET['nobox']=='true' || @$_POST['nobox']=='true')
	$nobox='true';
$WSP = $OWSP = '';
$TWSP = 'pPrice';
get_wholesaleprice_sql();
$tsID='';
$scat=preg_replace('/[^,\d]/','',@$_REQUEST['scat']);
$scat=preg_replace('/,+/',',',$scat);
$scat=preg_replace('/^,|,$/','',$scat);
$sman=preg_replace('/[^,\d]/','',@$_REQUEST['sman']);
$sman=preg_replace('/,+/',',',$sman);
$sman=preg_replace('/^,|,$/','',$sman);
$stext=unstripslashes(@$_REQUEST['stext']);
$stype=trim(@$_REQUEST['stype']);
if($stype!='any' && $stype!='exact')$stype='';
$sprice=trim(strip_tags(@$_REQUEST['sprice']));
if(!is_numeric($sprice))$sprice='';
$minprice=trim(strip_tags(@$_REQUEST['sminprice']));
if(!is_numeric($minprice))$minprice='';
if(substr($scat,0,2)=='ms') $thecat = substr($scat,2); else $thecat=$scat;
$thecat = str_replace("'",'',$thecat);
$catarr = explode(',', $thecat);
$manarr = explode(',', $sman);
$Count = 0;
if(strtolower($adminencoding)=='iso-8859-1') $raquo='»'; else $raquo='&raquo;';
function writemenulevel($id,$itlevel){
	global $allcatsa,$numcats,$thecat,$catarr,$raquo;
	if($itlevel<10){
		for($wmlindex=0; $wmlindex < $numcats; $wmlindex++){
			if($allcatsa[$wmlindex][2]==$id){
				print "<option value='" . $allcatsa[$wmlindex][0] . "'";
				if($catarr[0]==$allcatsa[$wmlindex][0]) print ' selected="selected">'; else print '>';
				for($index = 0; $index < $itlevel-1; $index++)
					print $raquo . ' ';
				print $allcatsa[$wmlindex][1] . "</option>\n";
				if($allcatsa[$wmlindex][3]==0) writemenulevel($allcatsa[$wmlindex][0],$itlevel+1);
			}
		}
	}
}
$pblink = '<a class="ectlink" href="'.htmlentities(@$_SERVER['PHP_SELF']).'?nobox=' . $nobox . '&amp;scat=' . urlencode($scat) . '&amp;stext=' . urlencode($stext) . '&amp;stype=' . $stype . '&amp;sprice=' . urlencode($sprice) . ($minprice!=''?"&amp;sminprice=".$minprice:'') . ($sman!=''?'&amp;sman='.$sman:'') . '&amp;pg=';
$nofirstpg=FALSE;
function getlike($fie,$t,$tjn){
	global $sNOTSQL;
	if(substr($t, 0, 1)=='-'){ // pSKU excluded to work around NULL problems
		if($fie!='pSKU' && $fie!='pSearchParams') $sNOTSQL .= $fie." LIKE '%".substr($t, 1)."%' OR ";
	}else
		return $fie . " LIKE '%".$t."%' ".$tjn;
}
$alreadygotadmin = getadminsettings();
if(@$orprodsperpage != '') $adminProdsPerPage=$orprodsperpage;
checkCurrencyRates($currConvUser,$currConvPw,$currLastUpdate,$currRate1,$currSymbol1,$currRate2,$currSymbol2,$currRate3,$currSymbol3);
if(@$_SESSION['clientLoginLevel'] != '') $minloglevel=$_SESSION['clientLoginLevel']; else $minloglevel=0;
if(@$_POST['posted']=='1' || @$_GET['pg'] != ''){
	if($thecat != ''){
		$sSQL = 'SELECT DISTINCT products.pId FROM multisections RIGHT JOIN products ON products.pId=multisections.pId INNER JOIN sections on products.pSection=sections.sectionID WHERE sectionDisabled<='.$minloglevel.' AND pDisplay<>0 ';
		$gotcriteria=TRUE;
		$sectionids = getsectionids($thecat, FALSE);
		if($sectionids != '') $sSQL .= "AND (products.pSection IN (" . $sectionids . ") OR multisections.pSection IN (" . $sectionids . ")) ";
	}else
		$sSQL = 'SELECT DISTINCT products.pId FROM products INNER JOIN sections on products.pSection=sections.sectionID WHERE sectionDisabled<='.$minloglevel.' AND pDisplay<>0 ';
	if(is_numeric($sprice)){
		$gotcriteria=TRUE;
		$sSQL .= "AND ".$TWSP."<='" . escape_string($sprice) . "' ";
	}
	if(is_numeric($minprice)){
		$gotcriteria=TRUE;
		$sSQL .= "AND ".$TWSP.">='" . escape_string($minprice) . "' ";
	}
	if($sman!=''){
		$gotcriteria=TRUE;
		$sSQL .= "AND pManufacturer IN (" . escape_string($sman) . ") ";
	}
	if(trim($stext) != ''){
		$gotcriteria=TRUE;
		$Xstext = escape_string(substr($stext, 0, 1024));
		$aText = explode(' ',$Xstext);
		$aFields[0]='products.pId';
		$aFields[1]=getlangid('pName',1);
		$aFields[2]=getlangid('pDescription',2);
		$aFields[3]=getlangid('pLongDescription',4);
		$aFields[4]='pSKU';
		$aFields[5]='pSearchParams';
		if($stype=='exact'){
			$sSQL.='AND ';
			if(substr($Xstext, 0, 1)=='-'){ $sSQL .= 'NOT '; $Xstext = substr($Xstext, 1); $isnot=TRUE; }else $isnot=FALSE;
			$sSQL .= "(products.pId LIKE '%".$Xstext."%' OR ".getlangid('pName',1)." LIKE '%".$Xstext."%'".(@$nosearchparams?'':" OR pSearchParams LIKE '%".$Xstext."%'").(@$nosearchdescription?'':' OR '.getlangid('pDescription',2)." LIKE '%".$Xstext."%'").(@$nosearchlongdescription?'':' OR '.getlangid('pLongDescription',2)." LIKE '%".$Xstext."%'") . ($isnot||@$nosearchsku? '' : " OR pSKU LIKE '%".$Xstext."%'") . ") ";
		}elseif(count($aText) < 24){
			$sNOTSQL = ''; $sYESSQL = '';
			if($stype=='any'){
				for($index=0;$index<=5;$index++){
					$tmpSQL = '';
					$arrelms=count($aText);
					foreach($aText as $theopt){
						if(is_array($theopt))$theopt=$theopt[0];
						if(! ((@$nosearchdescription==TRUE && $index==2) || (@$nosearchlongdescription==TRUE && $index==3) || (@$nosearchsku==TRUE && $index==4) || (@$nosearchparams==TRUE && $index==5)))
							$tmpSQL .= getlike($aFields[$index], $theopt, 'OR ');
					}
					if($tmpSQL!='') $sYESSQL.= '(' . substr($tmpSQL, 0, strlen($tmpSQL)-3) . ') ';
					if($tmpSQL!='') $sYESSQL .= 'OR ';
				}
				$sYESSQL = substr($sYESSQL, 0, -3);
			}else{
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
			}
			if($sYESSQL!='') $sSQL .= 'AND (' . $sYESSQL . ') ';
			if($sNOTSQL!='') $sSQL .= 'AND NOT (' . substr($sNOTSQL, 0, strlen($sNOTSQL)-4) . ')';
		}
	}
	if(@$_POST['sortby'] != '') $_SESSION['sortby']=(int)$_POST['sortby'];
	if(@$_SESSION['sortby'] != '') $sortBy=(int)($_SESSION['sortby']);
	if(@$orsortby!='') $sortBy=$orsortby;
	if(@$sortBy==2)
		$sSortBy = ' ORDER BY products.pId';
	elseif(@$sortBy==3)
		$sSortBy = ' ORDER BY '.$TWSP.',pId';
	elseif(@$sortBy==4)
		$sSortBy = ' ORDER BY '.$TWSP.' DESC,pId';
	elseif(@$sortBy==5)
		$sSortBy = '';
	elseif(@$sortBy==6)
		$sSortBy = ' ORDER BY pOrder,pId';
	elseif(@$sortBy==7)
		$sSortBy = ' ORDER BY pOrder DESC,pId';
	elseif(@$sortBy==8)
		$sSortBy = ' ORDER BY pDateAdded,pId';
	elseif(@$sortBy==9)
		$sSortBy = ' ORDER BY pDateAdded DESC,pId';
	else
		$sSortBy = ' ORDER BY '.getlangid('pName',1);
	if($gotcriteria)
		$tmpSQL = preg_replace('/DISTINCT products.pId/','COUNT(DISTINCT products.pId) AS bar',$sSQL, 1);
	else{
		$sSQL = 'SELECT products.pId FROM products INNER JOIN sections ON products.pSection=sections.sectionID WHERE sectionDisabled<='.$minloglevel.' AND pDisplay<>0';
		$tmpSQL = preg_replace('/products.pId/','COUNT(*) AS bar',$sSQL, 1);
	}
	if($useStockManagement && @$noshowoutofstock==TRUE) $extrasql .= ' AND (pInStock>0 OR pStockByOpts<>0)'; else $extrasql = '';
	$sSQL .= $extrasql;
	$tmpSQL .= $extrasql;
	$allprods = mysql_query($tmpSQL) or print(mysql_error());
	$iNumOfPages = ceil(mysql_result($allprods,0,'bar')/$adminProdsPerPage);
	mysql_free_result($allprods);
	$sSQL .= $sSortBy . ' LIMIT ' . ($adminProdsPerPage*($CurPage-1)) . ', ' . $adminProdsPerPage;
	$allprods = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($allprods) == 0)
		$success=FALSE;
	else{
		$success=TRUE;
		$prodlist = '';
		$addcomma='';
		while($rs = mysql_fetch_array($allprods)){
			$prodlist .= $addcomma . "'" . $rs['pId'] . "'";
			$addcomma=',';
		}
		mysql_free_result($allprods);
		$wantmanufacturer = (@$manufacturerfield!='' || (@$usedetailbodyformat==3 && strpos(@$cpdcolumns, 'manufacturer')!==FALSE));
		$sSQL = 'SELECT pId,pSKU,'.getlangid('pName',1).','.$WSP.'pPrice,pListPrice,pSection,pSell,pStockByOpts,pStaticPage,pInStock,pExemptions,pTax,pTotRating,pNumRatings,'.($wantmanufacturer?'mfName,':'').(@$shortdescriptionlimit===0?"'' AS ":'').getlangid('pDescription',2).','.getlangid('pLongDescription',4).' FROM products '.($wantmanufacturer?'LEFT OUTER JOIN manufacturer on products.pManufacturer=manufacturer.mfID ':'').'WHERE pId IN (' . $prodlist . ')' . $sSortBy;
		$allprods = mysql_query($sSQL) or print(mysql_error());
	}
}
if($nobox==''){
?>
	  <br />
	  <form method="get" action="search.php">
		  <input type="hidden" name="pg" value="1" />
            <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr>
                <td class="cobhl" align="center" colspan="4" height="30">
                  <strong><?php print $xxSrchPr?></strong>
                </td>
              </tr>
			  <tr>
				<td class="cobhl" width="25%" align="right"><?php print $xxSrchFr?>:</td>
				<td class="cobll" width="25%"><input type="text" name="stext" size="20" maxlength="1024" value="<?php print htmlspecials($stext)?>" /></td>
				<td class="cobhl" width="25%" align="right"><?php print $xxSrchMx?>:</td>
				<td class="cobll" width="25%"><input type="text" name="sprice" size="10" maxlength="64" value="<?php print htmlspecials($sprice)?>" /></td>
			  </tr>
			  <tr>
				<td class="cobhl" width="25%" align="right"><?php print $xxSrchTp?>:</td>
				<td class="cobll" width="25%"><select name="stype" size="1">
					<option value=""><?php print $xxSrchAl?></option>
					<option value="any" <?php if($stype=="any") print 'selected="selected"'?>><?php print $xxSrchAn?></option>
					<option value="exact" <?php if($stype=="exact") print 'selected="selected"'?>><?php print $xxSrchEx?></option>
					</select>
				</td>
				<td class="cobhl" width="25%" align="right"><?php print $xxSrchCt?>:</td>
				<td class="cobll" width="25%">
				  <select name="scat" size="1">
				  <option value=""><?php print $xxSrchAC?></option>
<?php	$lasttsid = -1;
		if(@$nocategorysearch!=TRUE){
			$sSQL = 'SELECT sectionID,'.getlangid('sectionName',256).',topSection,rootSection FROM sections WHERE sectionDisabled<=' . $minloglevel . ' ';
			if(@$onlysubcats==TRUE) $sSQL .= 'AND rootSection=1 ORDER BY '.getlangid('sectionName',256); else $sSQL .= 'ORDER BY '.(@$sortcategoriesalphabetically?getlangid('sectionName',256):'sectionOrder');
			$allcats = mysql_query($sSQL) or print(mysql_error());
			while($row = mysql_fetch_row($allcats)){
				$allcatsa[$numcats++]=$row;
			}
			mysql_free_result($allcats);
		}
		if($numcats > 0) writemenulevel($catalogroot,1);
?>
				  </select>
				</td>
              </tr>
<?php
	if(@$searchbymanufacturer!=''){ ?>
			  <tr>
			    <td class="cobhl" align="right"><?php print $searchbymanufacturer?>:</td>
				<td class="cobll"><select name="sman" size="1"><option value=""><?php print $xxSeaAll?></option><?php
		$sSQL = 'SELECT mfID,mfName FROM manufacturer ORDER BY mfName';
		$result2 = mysql_query($sSQL) or print(mysql_error());
		while($rs2 = mysql_fetch_assoc($result2)){
			print '<option value="' . $rs2['mfID'] . '"';
			if($manarr[0]==$rs2['mfID']) print ' selected="selected"';
			print '>' . $rs2['mfName'] . "</option>\r\n";
		}
		mysql_free_result($result2); ?></select></td>
			    <td class="cobll" colspan="2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
				    <tr>
					  <td width="98%" align="center"><?php print imageorsubmit(@$imgsearch,$xxSearch,'')?></td>
					  <td width="2%" height="26" align="right" valign="bottom"><img src="images/tablebr.png" alt="" /></td>
					</tr>
				  </table></td>
			  </tr>
<?php
	}else{ ?>
			  <tr>
			    <td class="cobhl">&nbsp;</td>
			    <td class="cobll" colspan="3"><table width="100%" cellspacing="0" cellpadding="0" border="0">
				    <tr>
					  <td width="66%" align="center"><?php print imageorsubmit(@$imgsearch,$xxSearch,'')?></td>
					  <td width="34%" height="26" align="right" valign="bottom"><img src="images/tablebr.png" alt="" /></td>
					</tr>
				  </table></td>
			  </tr>
<?php
	} ?>
			</table>
		</form>
<?php
}
if(@$_POST['posted']=='1' || @$_GET['pg'] != ''){
?>
		<table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
<?php
	if(!$success){ ?>
		<tr> 
		  <td align="center"> 
		    <p>&nbsp;</p>
		    <p><strong><?php print $xxSrchNM?></strong></p>
		  </td>
		</tr>
<?php
	}else{ ?>
        <tr> 
          <td width="100%">
<?php	if($usesearchbodyformat==3)
			include "./vsadmin/inc/incproductbody3.php";
		elseif($usesearchbodyformat==2)
			include "./vsadmin/inc/incproductbody2.php";
		else
			include "./vsadmin/inc/incproductbody.php"; ?>
          </td>
        </tr>
<?php
	}
	mysql_free_result($allprods);
?>
      </table>
<?php
}
?>
