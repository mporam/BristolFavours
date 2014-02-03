<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
$catname='';
$theid=trim(@$_GET['id']);
$alreadygotadmin = getadminsettings();
if(trim(@$_GET['cat'])!='') $theid = unstripslashes(@$_GET['cat']);
if(@$explicitid!='' && is_numeric(@$explicitid))
	$theid=@$explicitid;
elseif(@$usecategoryname && $theid!=''){
	$sSQL = 'SELECT sectionID FROM sections WHERE '.getlangid('sectionName',256)."='".escape_string($theid)."'";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs=mysql_fetch_assoc($result)){ $catname=$theid; $theid=$rs['sectionID']; }
	mysql_free_result($result);
}
if(! is_numeric($theid)) $theid=$catalogroot;
if(! is_numeric(@$categorycolumns) || $categorycolumns=='') $categorycolumns=1;
$cellwidth = (int)(100/$categorycolumns);
if(@$manufacturerpageurl=='') $manufacturerpageurl='manufacturers.php';
if(strpos(strtolower(@$_SERVER['PHP_SELF']), strtolower($manufacturerpageurl))!==FALSE) $manufacturers=TRUE; else $manufacturers=FALSE;
if(@$usecategoryformat==3){
	$afterimage='<br />';
	$beforedesc='';
}elseif(@$usecategoryformat==2){
	$afterimage='';
	$beforedesc='';
}else{
	$usecategoryformat=1;
	$afterimage='';
	$beforedesc='</td></tr><tr><td class="catdesc" colspan="2">';
}
$border=0;
if(! @isset($catseparator)) $catseparator = '<br />&nbsp;';
$tslist = '';
$thetopts = $theid;
$topsectionids = $theid;
$success = TRUE;
if(@$_SESSION['clientID']!='' && @$_SESSION['clientLoginLevel']!='') $minloglevel=$_SESSION['clientLoginLevel']; else $minloglevel=0;
$columncount=0;
if($manufacturers){
	$tslist = '<a class="ectlink" href="'.$xxHomeURL.'">'.$xxHome.'</a> &raquo; ' . $xxManuf;
	$xxAlProd = '';
	$noshowdiscounts = TRUE;
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
			if($theid==$catalogroot) $tslist = $xxHome . ' ' . $tslist; else $tslist = '<a class="ectlink" href="'.$xxHomeURL.'">'.$xxHome.'</a> '.$tslist;
			break;
		}elseif($index==10){
			$tslist = '<strong>Loop</strong>' . $tslist;
		}else{
			$sSQL = 'SELECT sectionID,topSection,'.getlangid('sectionName',256).',rootSection,sectionDisabled,'.getlangid('sectionurl',2048).' FROM sections WHERE sectionID=' . $thetopts;
			$result2 = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result2) > 0){
				$rs2 = mysql_fetch_assoc($result2);
				if($rs2['sectionDisabled'] > $minloglevel)
					$success=FALSE;
				elseif($rs2['sectionID']==(int)$theid){
					$tslist = ' &raquo; ' . $rs2[getlangid('sectionName',256)] . $tslist;
					if(@$explicitid=='' && trim($rs2[getlangid('sectionurl',2048)]) != '' && @$redirecttostatic==TRUE){
						ob_end_clean();
						header('HTTP/1.1 301 Moved Permanently');
						if($rs2[getlangid('sectionurl',2048)]{0}=='/')$thelocation='http://'.$_SERVER['HTTP_HOST'].$rs2[getlangid('sectionurl',2048)];elseif(substr(strtolower($rs2[getlangid('sectionurl',2048)]),0,7) == 'http://')$thelocation=$rs2[getlangid('sectionurl',2048)];else $thelocation='http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'],'/')).'/'.$rs2[getlangid('sectionurl',2048)];
						header('Location: '.$thelocation);
						exit;
					}
				}elseif(trim($rs2[getlangid('sectionurl',2048)]) !='')
					$tslist = ' &raquo; <a class="ectlink" href="' . $rs2[getlangid('sectionurl',2048)] . '">' . $rs2[getlangid('sectionName',256)] . '</a>' . $tslist;
				elseif($rs2['rootSection']==1)
					$tslist = ' &raquo; <a class="ectlink" href="products.php?cat=' . getcatid($rs2['sectionID'],$rs2[getlangid('sectionName',256)]) . '">' . $rs2[getlangid('sectionName',256)] . '</a>' . $tslist;
				else
					$tslist = ' &raquo; <a class="ectlink" href="categories.php?cat=' . getcatid($rs2['sectionID'],$rs2[getlangid('sectionName',256)]) . '">' . $rs2[getlangid('sectionName',256)] . '</a>' . $tslist;
				$thetopts = $rs2['topSection'];
				$topsectionids .= ',' . $thetopts;
			}else{
				$tslist = 'Top Section Deleted' . $tslist;
				break;
			}
			mysql_free_result($result2);
		}
	}
}
if(@$xxAlProd!='') $tslist .= ' &raquo; <a class="ectlink" href="products.php' . ($theid=='0'||$theid==$catalogroot ? '' : '?cat=' . getcatid($theid,$catname)) . '">' . $xxAlProd . '</a>';
if($manufacturers==TRUE){
	// $showcategories=FALSE;
	$showdiscounts=FALSE;
	$sSQL = 'SELECT mfID AS sectionID,mfName AS '.getlangid('sectionName',256).','.getlangid('mfDescription',16384).' AS '.getlangid('sectionDescription',512).',mfLogo AS sectionImage,mfOrder AS sectionOrder,1 AS rootSection,'.getlangid('mfURL',8192).' AS '.getlangid('sectionurl',2048).' FROM manufacturer ORDER BY ' . (@$sortcategoriesalphabetically==TRUE ? 'mfName' : 'mfOrder');
}else
	$sSQL = 'SELECT sectionID,'.getlangid('sectionName',256).','.(@$nocategorydescription==TRUE?"'' AS ":'').getlangid('sectionDescription',512).',sectionImage,sectionOrder,rootSection,'.getlangid('sectionurl',2048).' FROM sections WHERE topSection=' . $theid . ' AND sectionDisabled<=' . $minloglevel . ' ORDER BY ' . (@$sortcategoriesalphabetically==TRUE ? getlangid('sectionName',256) : 'sectionOrder');
$result = mysql_query($sSQL) or print(mysql_error());
if(!$success || mysql_num_rows($result)==0){
	$success=false;
	$mess1 = '<p>&nbsp;</p>' . $xxNoCats;
}else{
	$success=true;
	if(@$xxClkPrd != '') $mess1 = $xxClkPrd . '<br />&nbsp;'; else $mess1='';
}
if($usecategoryformat==1 || $usecategoryformat==2) $numcolumns=2*$categorycolumns; else $numcolumns=$categorycolumns;
?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
            <table width="100%" border="<?php print $border?>" cellspacing="3" cellpadding="3">
<?php	if($mess1 != ""){ ?>
			  <tr>
				<td align="center"<?php if($numcolumns>1) print ' colspan="' . $numcolumns . '"'?>>
				  <p><strong><?php print $mess1?></strong></p>
				</td>
			  </tr>
<?php
		}
	if(@$nowholesalediscounts==TRUE && @$_SESSION['clientUser']!='')
		if((($_SESSION['clientActions'] & 8) == 8) || (($_SESSION['clientActions'] & 16) == 16)) $noshowdiscounts=TRUE;
	if($success){
		if(@$noshowdiscounts != TRUE){
			if($theid=='0')
				$sSQL = 'SELECT DISTINCT '.getlangid('cpnName',1024)." FROM coupons WHERE (cpnSitewide=1 OR cpnSitewide=2)";
			else
				$sSQL = 'SELECT DISTINCT '.getlangid('cpnName',1024)." FROM coupons LEFT OUTER JOIN cpnassign ON coupons.cpnID=cpnassign.cpaCpnID WHERE (((cpnSitewide=0 OR cpnSitewide=3) AND cpaType=1 AND cpaAssignment IN ('" . str_replace(',',"','",$topsectionids) . "')) OR cpnSitewide=1 OR cpnSitewide=2)";
			$sSQL .= " AND cpnNumAvail>0 AND cpnEndDate>='" . date('Y-m-d',time()) ."' AND cpnIsCoupon=0 AND ((cpnLoginLevel>=0 AND cpnLoginLevel<=".$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
			$result2 = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result2) > 0){ ?>
			  <tr>
				<td align="left" class="allcatdiscounts"<?php if($numcolumns>1) print ' colspan="' . $numcolumns . '"'?>>
					<div class="discountsapply allcatdiscounts"<?php print (@$nomarkup?'':' style="font-weight:bold;"')?>><?php print $xxDsCat?></div><div class="catdiscounts allcatdiscounts"<?php print (@$nomarkup?'':' style="font-size:9px;color:#FF0000;"')?>><?php
						while($rs=mysql_fetch_assoc($result2)){
							print $rs[getlangid('cpnName',1024)] . '<br />';
						} ?>&nbsp;</div>
				</td>
			  </tr>
<?php		}
			mysql_free_result($result2);
		}
		print '</table>';
		if(! (@isset($showcategories) && @$showcategories==FALSE)){
			print '<table width="98%" border="0" cellspacing="3" cellpadding="3"><tr>';
			if(@$allproductsimage != '') print '<td class="catimage" width="5%" align="right"><a class="ectlink" href="products.php"><img class="catimage" src="' . @$allproductsimage . '" border="0" alt="' . $xxAlProd . '" /></a>' . $afterimage . '</td>';
			print '<td class="catnavigation">';
			print '<p class="catnavigation"><strong>' . $tslist . '</strong></p>';
			if($xxAlPrCa!='' && ! $manufacturers) print '<p class="navdesc">' . $xxAlPrCa . @$catseparator . '</p>';
			print "</td></tr>\r\n";
			print '</table>';
		}
		print '<table width="98%" border="0" cellspacing="' . ($usecategoryformat==1 && $categorycolumns>1 ? 0 : 3) . '" cellpadding="' . ($usecategoryformat==1 && $categorycolumns>1 ? 0 : 3) . '">';
		while($rs=mysql_fetch_row($result)){
			if(trim($rs[6])!="")
				$startlink='<a class="ectlink" href="' . $rs[6] . '">';
			elseif($rs[5]==0)
				$startlink='<a class="ectlink" href="categories.php?cat=' . getcatid($rs[0],$rs[1]) . '">';
			else
				$startlink='<a class="ectlink" href="products.php?' . ($manufacturers==TRUE?'man=':'cat=') . getcatid($rs[0],$rs[1]) . '">';
			$sSQL = 'SELECT DISTINCT '.getlangid('cpnName',1024)." FROM coupons LEFT OUTER JOIN cpnassign ON coupons.cpnID=cpnassign.cpaCpnID WHERE (cpnSitewide=0 OR cpnSitewide=3) AND cpnNumAvail>0 AND cpnEndDate>='" . date('Y-m-d',time()) ."' AND cpnIsCoupon=0 AND cpaType=1 AND cpaAssignment='" . $rs[0] . "'" .
				' AND ((cpnLoginLevel>=0 AND cpnLoginLevel<='.$minloglevel.') OR (cpnLoginLevel<0 AND -1-cpnLoginLevel='.$minloglevel.'))';
			$alldiscounts = '';
			if(@$noshowdiscounts != TRUE){
				$result2 = mysql_query($sSQL) or print(mysql_error());
				while($rs2=mysql_fetch_row($result2))
					$alldiscounts .= $rs2[0] . '<br />';
				mysql_free_result($result2);
			}
			$secdesc = trim($rs[2]);
			$noimage = (trim($rs[3]) == '');
			if($columncount==0) print '<tr>';
			if($usecategoryformat==1 && $categorycolumns>1) print '<td width="' . $cellwidth . '%" valign="top"><table width="100%" border="0" cellspacing="3" cellpadding="3"><tr>';
			if(($usecategoryformat==1 || $usecategoryformat==2) && ! $noimage){
				$cellwidth -= 5;
				print '<td class="catimage" width="5%" align="right">' . $startlink . '<img alt="' . str_replace('"','',$rs[1]) . '" class="catimage" src="' . $rs[3] . '" border="0" /></a>' . $afterimage . '</td>';
			}
			print '<td class="catname" width="' . ($usecategoryformat==1 && $categorycolumns>1 ? 95 : $cellwidth) . '%"' . (($usecategoryformat==1 || $usecategoryformat==2) && $noimage ? ' colspan="2"' : "") . '>';
			if(($usecategoryformat==1 || $usecategoryformat==2) && ! $noimage) $cellwidth += 5;
			if($usecategoryformat != 1 && $usecategoryformat != 2 && ! $noimage) print $startlink . '<img alt="' . str_replace('"','',$rs[1]) . '" class="catimage" src="' . $rs[3] . '" border="0" /></a>' . $afterimage;
			if(@$nocategoryname!=TRUE) print '<p class="catname"><strong>' . $startlink . $rs[1] . '</a>' . $xxDot . '</strong>';
			if($alldiscounts!= "") print ' <span style="color:#FF0000"><strong>' . $xxDsApp . '</strong><br />' . (@$nomarkup?'':'<font size="1">') . '<div class="catdiscounts">' . $alldiscounts . '</div>' . (@$nomarkup?'':'</font>') . '</span>';
			if($secdesc=='') print @$catseparator;
			if(@$nocategoryname!=TRUE) print '</p>';
			if($secdesc != "") print $beforedesc . '<p class="catdesc">' . $secdesc . $catseparator . '</p>';
			print "</td>\r\n";
			if($usecategoryformat==1 && $categorycolumns>1) print '</tr></table></td>';
			$columncount++;
			if($columncount==$categorycolumns){
				print '</tr>';
				$columncount=0;
			}
		}
	}
	if($columncount<$categorycolumns && $columncount != 0){
		while($columncount<$categorycolumns){
			print '<td ' . ($usecategoryformat==2 ? ' colspan="2"' : '') . '>&nbsp;</td>';
			$columncount++;
		}
		print '</tr>';
	}
print '</table><table width="100%" border="0" cellspacing="0" cellpadding="0">';
print '<tr><td><img src="images/clearpixel.gif" width="300" height="1" alt="" /></td></tr>';
mysql_free_result($result);
?>
            </table>
          </td>
        </tr>
      </table>