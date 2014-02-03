<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if($_SESSION["loggedon"] != $storesessionvalue || @$disallowlogin==TRUE) exit;
if(@$dateadjust=='') $dateadjust=0;
if(@$dateformatstr == '') $dateformatstr = 'm/d/Y';
$admindatestr='Y-m-d';
if(@$admindateformat=='') $admindateformat=0;
if($admindateformat==1)
	$admindatestr='m/d/Y';
elseif($admindateformat==2)
	$admindatestr='d/m/Y';
$resultcounter=0;
$dynamicadminmenus=(strpos(@$_SERVER['HTTP_USER_AGENT'], 'Gecko')!==FALSE || strpos(@$_SERVER['HTTP_USER_AGENT'], 'Opera')!==FALSE);
if(strtolower($adminencoding)=='iso-8859-1') $raquo='»'; else $raquo='>';
function writemenulevel($id,$itlevel){
	global $allcatsa,$numcats,$thecat,$raquo;
	if($itlevel<10){
		for($wmlindex=0; $wmlindex < $numcats; $wmlindex++){
			if($allcatsa[$wmlindex][2]==$id){
				print "<option value='" . $allcatsa[$wmlindex][0] . "'";
				if($thecat==$allcatsa[$wmlindex][0]) print ' selected="selected">'; else print ">";
				for($index = 0; $index < $itlevel-1; $index++)
					print $raquo . ' ';
				print htmlspecials($allcatsa[$wmlindex][1]) . "</option>\n";
				if($allcatsa[$wmlindex][3]==0) writemenulevel($allcatsa[$wmlindex][0],$itlevel+1);
			}
		}
	}
}
$success=TRUE;
$nprodoptions=0;
$nprodsections=0;
$nalloptions=0;
$nallsections=0;
$nalldropship=0;
$nallmanufacturer=0;
$alreadygotadmin = getadminsettings();
$simpleOptions = (($adminTweaks & 2)==2);
$simpleSections = (($adminTweaks & 4)==4);
$dorefresh=FALSE;
if(@$maxprodsects=="") $maxprodsects=20;
// $usesshipweight=($shipType==2 || $shipType==3 || $shipType==4 || $shipType==6 || $shipType==7 || $adminIntShipping==2 || $adminIntShipping==3 || $adminIntShipping==4 || $adminIntShipping==6 || $adminIntShipping==7);
$usesshipweight=TRUE;
$usesflatrate=($shipType==1 || $adminIntShipping==1);
if(@$htmlemails==TRUE) $emlNl = '<br />'; else $emlNl="\n";
function dodeleteprod($pid){
	$sSQL = "DELETE FROM pricebreaks WHERE pbProdID='" . escape_string($pid) . "'";
	mysql_query($sSQL) or print(mysql_error());
	$sSQL = "DELETE FROM cpnassign WHERE cpaType=2 AND cpaAssignment='" . escape_string($pid) . "'";
	mysql_query($sSQL) or print(mysql_error());
	$sSQL = "DELETE FROM products WHERE pID='" . escape_string($pid) . "'";
	mysql_query($sSQL) or print(mysql_error());
	$sSQL = "DELETE FROM prodoptions WHERE poProdID='" . escape_string($pid) . "'";
	mysql_query($sSQL) or print(mysql_error());
	$sSQL = "DELETE FROM multisections WHERE pID='" . escape_string($pid) . "'";
	mysql_query($sSQL) or print(mysql_error());
	$sSQL = "DELETE FROM relatedprods WHERE rpProdID='" . escape_string($pid) . "' OR rpRelProdID='" . escape_string($pid) . "'";
	mysql_query($sSQL) or print(mysql_error());
	$sSQL = "DELETE FROM notifyinstock WHERE nsProdID='" . escape_string($pid) . "' OR nsTriggerProdID='" . escape_string($pid) . "'";
	mysql_query($sSQL) or print(mysql_error());
}
function notifyallstock(){
	$allprods='';
	$sSQL = 'SELECT DISTINCT nsTriggerProdID FROM notifyinstock INNER JOIN products ON notifyinstock.nsTriggerProdID=products.pID WHERE pInStock>0 AND nsOptID=0';
	$resultna = mysql_query($sSQL) or print(mysql_error());
	while($rsna=mysql_fetch_assoc($resultna)){
		checknotifystock($rsna['nsTriggerProdID']);
	}
	mysql_free_result($resultna);
	$sSQL = 'SELECT DISTINCT nsOptID FROM notifyinstock INNER JOIN options ON notifyinstock.nsOptID=options.optID WHERE optStock>0 AND nsOptID<>0';
	$resultna = mysql_query($sSQL) or print(mysql_error());
	while($rsna=mysql_fetch_assoc($resultna)){
		checknotifystockoption($rsna['nsOptID']);
	}
	mysql_free_result($resultna);
}
function checknotifystockoption($theoid){
	global $stockManage,$notifybackinstock,$storeurl,$htmlemails,$emailAddr,$emlNl;
	if($stockManage!=0 && $notifybackinstock){
		$sSQL = 'SELECT '.getlangid('notifystocksubject',4096).','.getlangid('notifystockemail',4096).' FROM emailmessages WHERE emailID=1';
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs=mysql_fetch_assoc($result)){
			$oemailsubject=trim($rs[getlangid('notifystocksubject',4096)]);
			$oemailmessage=$rs[getlangid('notifystockemail',4096)];
		}
		mysql_free_result($result);
		
		$idlist='';
		$sSQL="SELECT DISTINCT nsProdID FROM notifyinstock INNER JOIN prodoptions ON notifyinstock.nsProdID=prodoptions.poProdID INNER JOIN options ON prodoptions.poOptionGroup=options.optGroup WHERE nsOptID=-1 AND optID=".$theoid;
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs=mysql_fetch_assoc($result)){
			$gotall=TRUE;
			$sSQL = "SELECT poOptionGroup FROM prodoptions INNER JOIN optiongroup ON prodoptions.poOptionGroup=optiongroup.optGrpID WHERE poProdID='".escape_string($rs['nsProdID'])."'";
			$result2 = mysql_query($sSQL) or print(mysql_error());
			while($rs2=mysql_fetch_assoc($result2)){
				$sSQL = "SELECT optID FROM options WHERE optStock>0 AND optGroup=".$rs2['poOptionGroup'];
				$result3 = mysql_query($sSQL) or print(mysql_error());
				if(mysql_num_rows($result3)==0) $gotall=FALSE;
				mysql_free_result($result3);
			}
			mysql_free_result($result2);
			if($gotall) $idlist.="'".escape_string($rs['nsProdID'])."',";
		}
		mysql_free_result($result);
		if($idlist!='') $idlist=substr($idlist,0,-1);
		
		$pStockByOpts=0;
		$sSQL = "SELECT pId,pName,pStockByOpts,pStaticPage,pInStock,nsEmail FROM products INNER JOIN notifyinstock ON products.pID=notifyinstock.nsProdID WHERE nsOptId=".$theoid;
		if($idlist!='') $sSQL.=' OR (nsOptID=-1 AND nsProdID IN ('.$idlist.'))';
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs=mysql_fetch_assoc($result)){
			$nspid=$rs['pId'];
			$pName=trim($rs['pName']);
			$pStockByOpts=$rs['pStockByOpts'];
			$pStaticPage=$rs['pStaticPage'];
			$pInStock=$rs['pInStock'];
			$theemail=$rs['nsEmail'];
			if($pStaticPage!=0)
					$thelink = $storeurl . cleanforurl($pName).'.php';
				else
					$thelink = $storeurl . 'proddetail.php?prod='.trim($nspid);
			if(@$htmlemails==TRUE && $thelink!='') $thelink = '<a href="' . $thelink . '">' . $thelink . '</a>';
			$emailsubject = str_replace('%pid%',trim($nspid),$oemailsubject);
			$emailsubject = str_replace('%pname%',$pName,$emailsubject);
			$emailmessage = str_replace('%pid%',trim($nspid),$oemailmessage);
			$emailmessage = str_replace('%pname%',$pName,$emailmessage);
			$emailmessage = str_replace('%link%',$thelink,$emailmessage);
			$emailmessage = str_replace('%storeurl%',$storeurl,$emailmessage);
			$emailmessage = str_replace('<br />',$emlNl,$emailmessage);
			$emailmessage = str_replace('%nl%',$emlNl,$emailmessage);
			dosendemail($rs['nsEmail'],$emailAddr,'',$emailsubject,$emailmessage);
		}
		mysql_free_result($result);
		$sSQL='DELETE FROM notifyinstock WHERE nsOptId='.$theoid;
		if($idlist!='') $sSQL.=' OR (nsOptID=-1 AND nsProdID IN ('.$idlist.'))';
		mysql_query($sSQL) or print(mysql_error());
	}
}
function checknotifystock($thepid){
	global $stockManage,$notifybackinstock,$storeurl,$htmlemails,$emailAddr,$emlNl;
	if($stockManage!=0 && $notifybackinstock){
		$pStockByOpts=1;
		$sSQL = "SELECT pName,pStockByOpts,pStaticPage,pInStock FROM products WHERE pID='".escape_string($thepid)."'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs=mysql_fetch_assoc($result)){
			$pName=trim($rs['pName']);
			$pStockByOpts=$rs['pStockByOpts'];
			$pStaticPage=$rs['pStaticPage'];
			$pInStock=$rs['pInStock'];
		}
		mysql_free_result($result);
		if($pStockByOpts==0&&$pInStock>0){
			$sSQL = 'SELECT '.getlangid('notifystocksubject',4096).','.getlangid('notifystockemail',4096).' FROM emailmessages WHERE emailID=1';
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs=mysql_fetch_assoc($result)){
				$emailsubject=trim($rs[getlangid('notifystocksubject',4096)]);
				$emailmessage=$rs[getlangid('notifystockemail',4096)];
			}
			mysql_free_result($result);
			$sSQL="SELECT nsEmail,nsProdId FROM notifyinstock WHERE nsTriggerProdID='".escape_string($thepid)."'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs=mysql_fetch_assoc($result)){
				$nspid=$rs['nsProdId'];
				if($pStaticPage!=0)
					$thelink = $storeurl . cleanforurl($pName).'.php';
				else
					$thelink = $storeurl . 'proddetail.php?prod='.trim($nspid);
				if(@$htmlemails==TRUE && $thelink!='') $thelink = '<a href="' . $thelink . '">' . $thelink . '</a>';
				$emailsubject = str_replace('%pid%',trim($nspid),$emailsubject);
				$emailsubject = str_replace('%pname%',$pName,$emailsubject);
				$emailmessage = str_replace('%pid%',trim($nspid),$emailmessage);
				$emailmessage = str_replace('%pname%',$pName,$emailmessage);
				$emailmessage = str_replace('%link%',$thelink,$emailmessage);
				$emailmessage = str_replace('%storeurl%',$storeurl,$emailmessage);
				$emailmessage = str_replace('<br />',$emlNl,$emailmessage);
				$emailmessage = str_replace('%nl%',$emlNl,$emailmessage);
				do {
					dosendemail($rs['nsEmail'],$emailAddr,'',$emailsubject,$emailmessage);
				} while($rs=mysql_fetch_assoc($result));
			}
			mysql_free_result($result);
			mysql_query("DELETE FROM notifyinstock WHERE nsTriggerProdID='".escape_string($thepid)."'") or print(mysql_error());
		}
	}
}
if(@$defaultprodimages=='') $defaultprodimages = 'prodimages/';
if(@$_POST['posted']=='1'){
	$pExemptions=0;
	if(is_array(@$_POST["pExemptions"])){
		foreach(@$_POST["pExemptions"] as $pExemptObj)
			$pExemptions += $pExemptObj;
	}
	if(@$_POST['act']=='allstk'){
		notifyallstock();
		$dorefresh=TRUE;
	}elseif(@$_POST['act']=='delete'){
		dodeleteprod(@$_POST['id']);
		$dorefresh=TRUE;
	}elseif(@$_POST['act']=='updaterelations'){
		$rid=trim(@$_POST['rid']);
		foreach(@$_POST as $objItem => $objValue){
			if(substr($objItem,0,4)=='updq'){
				$theprodid=substr($objItem, 4);
				$sSQL = "DELETE FROM relatedprods WHERE (rpProdID='" . escape_string($rid) . "' AND rpRelProdID='" . escape_string($objValue) . "')";
				if(@$relatedproductsbothways==TRUE) $sSQL .= " OR (rpRelProdID='" . escape_string($rid) . "' AND rpProdID='" . escape_string($objValue) . "')";
				mysql_query($sSQL) or print(mysql_error());
				if(@$_POST['updr' . $theprodid]=='1'){
					$sSQL = "INSERT INTO relatedprods (rpProdID,rpRelProdID) VALUES ('" . escape_string($rid) . "','" . escape_string($objValue) . "')";
					mysql_query($sSQL) or print(mysql_error());
				}
			}
		}
		$dorefresh=TRUE;
	}elseif(@$_POST['act']=='quickupdate'){
		foreach(@$_POST as $objItem => $objValue){
			if(substr($objItem, 0, 4)=='pra_'){
				$origid = substr($objItem, 4);
				$theid = str_replace('ect_dot_xzq','.',$origid);
				$theval = trim(unstripslashes($objValue));
				$pract = @$_POST['pract'];
				$sSQL = '';
				if($pract=='prn'){
					if($theval!='') $sSQL = "UPDATE products SET pName='" . escape_string($theval) . "'";
				}elseif($pract=='prn2'){
					if($theval!='') $sSQL = "UPDATE products SET pName2='" . escape_string($theval) . "'";
				}elseif($pract=='prn3'){
					if($theval!='') $sSQL = "UPDATE products SET pName3='" . escape_string($theval) . "'";
				}elseif($pract=='pri'){
					if(is_numeric($theval)) $sSQL = 'UPDATE products SET pPrice=' . $theval;
				}elseif($pract=='wpr'){
					if(is_numeric($theval)) $sSQL = 'UPDATE products SET pWholesalePrice=' . $theval;
				}elseif($pract=='lpr'){
					if(is_numeric($theval)) $sSQL = 'UPDATE products SET pListPrice=' . $theval;
				}elseif($pract=='stk'){
					if(is_numeric($theval)) $sSQL = 'UPDATE products SET pInStock=' . $theval;
				}elseif($pract=='sta'){
					if(is_numeric($theval)) $sSQL = 'UPDATE products SET pInStock=pInStock+' . $theval;
				}elseif($pract=='del'){
					if($theval=='del') dodeleteprod($theid);
					$sSQL = '';
				}elseif($pract=='prw'){
					if(is_numeric($theval)) $sSQL = 'UPDATE products SET pWeight=' . $theval;
				}elseif($pract=='dip'){
					$sSQL = 'UPDATE products SET pDisplay=' . (@$_POST['prb_' . $origid]=='1'?'1':'0');
				}elseif($pract=='stp'){
					$sSQL = 'UPDATE products SET pStaticPage=' . (@$_POST['prb_' . $origid]=='1'?'1':'0');
				}elseif($pract=='rec'){
					$sSQL = 'UPDATE products SET pRecommend=' . (@$_POST['prb_' . $origid]=='1'?'1':'0');
				}elseif($pract=='sku'){
					$sSQL = "UPDATE products SET pSKU='" . escape_string($theval) . "'";
				}elseif($pract=='pro'){
					if(is_numeric($theval)) $sSQL = 'UPDATE products SET pOrder=' . $theval;
				}elseif($pract=='sel'){
					$sSQL = 'UPDATE products SET pSell=' . (@$_POST['prb_' . $origid]=='1'?'1':'0');
				}
				if($sSQL!=''){
					$sSQL .= " WHERE pID='".escape_string($theid)."'";
					mysql_query($sSQL) or print(mysql_error());
				}
				if($pract=='stk' || $pract=='sta'){
					if((int)$theval>0)
						checknotifystock($theid);
				}
			}
		}
		$dorefresh=TRUE;
	}elseif(@$_POST['act']=='domodify'){
		if(trim(@$_POST["newid"]) != trim(@$_POST["id"])){
			$sSQL = "SELECT * FROM products WHERE pID='" . trim(@$_POST["newid"]) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			$success = (mysql_num_rows($result)==0);
			mysql_free_result($result);
			if($success){
				mysql_query("UPDATE pricebreaks SET pbProdID='" . escape_string(unstripslashes(@$_POST['newid'])) . "' WHERE pbProdID='" . escape_string(unstripslashes(@$_POST['id'])) . "'") or print(mysql_error());
				mysql_query("UPDATE cpnassign SET cpaAssignment='" . escape_string(unstripslashes(@$_POST['newid'])) . "' WHERE cpaType=2 AND cpaAssignment='" . escape_string(unstripslashes(@$_POST['id'])) . "'") or print(mysql_error());
				mysql_query("UPDATE relatedprods SET rpProdID='" . escape_string(unstripslashes(@$_POST['newid'])) . "' WHERE rpProdID='" . escape_string(unstripslashes(@$_POST['id'])) . "'") or print(mysql_error());
				mysql_query("UPDATE relatedprods SET rpRelProdID='" . escape_string(unstripslashes(@$_POST['newid'])) . "' WHERE rpRelProdID='" . escape_string(unstripslashes(@$_POST['id'])) . "'") or print(mysql_error());
				mysql_query("UPDATE ratings SET rtProdID='" . escape_string(unstripslashes(@$_POST['newid'])) . "' WHERE rtProdID='" . escape_string(unstripslashes(@$_POST['id'])) . "'") or print(mysql_error());
			}
		}
		if($success){
			$pOrder = trim(@$_POST['pOrder']);
			if(! is_numeric($pOrder)) $pOrder=0;
			$sSQL = "UPDATE products SET ";
						$sSQL .= "pID='" . escape_string(unstripslashes(@$_POST['newid'])) . "', ";
						$sSQL .= "pName='" . escape_string(unstripslashes(@$_POST['pName'])) . "', ";
						$sSQL .= 'pSection=' . trim(@$_POST['pSection']) . ', ';
						$sSQL .= 'pDropship=' . trim(@$_POST['pDropship']) . ', ';
						$sSQL .= 'pManufacturer=' . trim(@$_POST['pManufacturer']) . ', ';
						$sSQL .= 'pSearchCriteria=' . (@$_POST['pSearchCriteria']!=''?@$_POST['pSearchCriteria']:'0') . ', ';
						$sSQL .= "pSKU='" . escape_string(unstripslashes(@$_POST['pSKU'])) . "', ";
						$sSQL .= 'pOrder=' . $pOrder . ', ';
						$sSQL .= 'pExemptions=' . $pExemptions . ', ';
						$sSQL .= "pSearchParams='" . escape_string(unstripslashes(@$_POST['pSearchParams'])) . "', ";
						$sSQL .= "pDescription='" . escape_string(unstripslashes(@$_POST['pDescription'])) . "', ";
						$sSQL .= "pLongDescription='" . escape_string(unstripslashes(@$_POST['pLongDescription'])) . "', ";
						for($index=2; $index <= $adminlanguages+1; $index++){
							if(($adminlangsettings & 1)==1) $sSQL .= "pName" . $index . "='" . escape_string(unstripslashes(@$_POST['pName' . $index])) . "', ";
							if(($adminlangsettings & 2)==2) $sSQL .= "pDescription" . $index . "='" . escape_string(unstripslashes(@$_POST['pDescription' . $index])) . "', ";
							if(($adminlangsettings & 4)==4) $sSQL .= "pLongDescription" . $index . "='" . escape_string(unstripslashes(@$_POST['pLongDescription' . $index])) . "', ";
						}
						if(trim(@$_POST['pDisplay']) == 'ON')
							$sSQL .= 'pDisplay=1,';
						else
							$sSQL .= 'pDisplay=0,';
						if(@$perproducttaxrate==TRUE)
							$sSQL .= 'pTax=' . trim(@$_POST['pTax']) . ',';
						if($stockManage != 0 && is_numeric(trim(@$_POST['inStock'])))
							$sSQL .= 'pInStock=' . trim(@$_POST['inStock']) . ',';
						$sSQL .= 'pStockByOpts=' . (trim(@$_POST['pStockByOpts']) == '1' ? 1 : 0) . ',';
						$sSQL .= 'pStaticPage=' . (trim(@$_POST['pStaticPage']) == '1' ? 1 : 0) . ',';
						$sSQL .= 'pRecommend=' . (trim(@$_POST['pRecommend']) == '1' ? 1 : 0) . ',';
						$sSQL .= 'pSell=' . (trim(@$_POST['pSell']) == 'ON' ? 1 : 0) . ',';
						if(($adminUnits & 12) > 0)
							$sSQL .= "pDims='" . trim(@$_POST['plen']) . 'x' . trim(@$_POST['pwid']) . 'x' . trim(@$_POST['phei']) . "',";
						if(@$digidownloads==TRUE)
							$sSQL .= "pDownload='" . escape_string(unstripslashes(@$_POST['pDownload'])) . "',";
						if(! is_numeric(trim(@$_POST['pShipping'])))
							$sSQL .= 'pShipping=0,';
						else
							$sSQL .= 'pShipping=' . trim(@$_POST['pShipping']) . ',';
						if(! is_numeric(trim(@$_POST['pShipping2'])))
							$sSQL .= 'pShipping2=0,';
						else
							$sSQL .= 'pShipping2=' . trim(@$_POST['pShipping2']) . ',';
						if(! is_numeric(trim(@$_POST['pWeight'])))
							$sSQL .= 'pWeight=0,';
						else
							$sSQL .= 'pWeight=' . trim(@$_POST['pWeight']) . ',';
						if(trim(@$_POST['pWholesalePrice']) != '')
							$sSQL .= 'pWholesalePrice=' . trim(@$_POST['pWholesalePrice']) . ',';
						else
							$sSQL .= 'pWholesalePrice=0,';
						if(trim(@$_POST['pListPrice']) != '')
							$sSQL .= 'pListPrice=' . trim(@$_POST['pListPrice']) . ',';
						else
							$sSQL .= 'pListPrice=0,';
						if(trim(@$_POST['pDateAdded']) != '')
							$sSQL .= "pDateAdded='" . date('Y-m-d', parsedate(@$_POST['pDateAdded'])) . "',";
						else
							$sSQL .= "pDateAdded='" . date('Y-m-d', time()-86400) . "',";
						$sSQL .= 'pPrice=' . trim(@$_POST['pPrice']) . ' ';
						$sSQL .= "WHERE pID='" . escape_string(unstripslashes(@$_POST['id'])) . "'";
			mysql_query($sSQL) or print(mysql_error());
			$sSQL = "DELETE FROM prodoptions WHERE poProdID='" . escape_string(unstripslashes(@$_POST['id'])) . "'";
			mysql_query($sSQL) or print(mysql_error());
			for($rowcounter=0; $rowcounter < maxprodopts; $rowcounter++){
				if(@$_POST['pOption' . $rowcounter] != '' && @$_POST['pOption' . $rowcounter] != 0){
					$sSQL = "INSERT INTO prodoptions (poProdID,poOptionGroup) VALUES ('" . @$_POST['newid'] . "'," . @$_POST['pOption' . $rowcounter] . ')';
					mysql_query($sSQL) or print(mysql_error());
				}
			}
			$sSQL = "DELETE FROM multisections WHERE pID='" . escape_string(unstripslashes(@$_POST['id'])) . "'";
			mysql_query($sSQL) or print(mysql_error());
			for($rowcounter=0; $rowcounter < $maxprodsects; $rowcounter++){
				if(@$_POST['pSection' . $rowcounter] != '' && @$_POST['pSection' . $rowcounter] != 0 && @$_POST['pSection'] != @$_POST['pSection' . $rowcounter]){
					$sSQL = "INSERT INTO multisections (pID,pSection) VALUES ('" . @$_POST['newid'] . "'," . @$_POST['pSection' . $rowcounter] . ')';
					mysql_query($sSQL) or print(mysql_error());
				}
			}
			if(is_numeric(trim(@$_POST['inStock']))){
				if((int)@$_POST['inStock']>0){
					checknotifystock(trim(@$_POST['newid']));
				}
			}
			$dorefresh=TRUE;
		}else
			$errmsg = $yyPrDup;
	}elseif(@$_POST['act']=='doaddnew'){
		$sSQL = "SELECT * FROM products WHERE pID='" . trim(@$_POST['newid']) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		$success = (mysql_num_rows($result)==0);
		mysql_free_result($result);
		if($success){
			$pOrder = trim(@$_POST['pOrder']);
			if(! is_numeric($pOrder)) $pOrder=0;
			$sSQL = "INSERT INTO products (pID,pName,pDateAdded,pSection,pDropship,pManufacturer,pSearchCriteria,pSKU,pOrder,pExemptions,pSearchParams,pDescription,pLongDescription,";
			for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 1)==1) $sSQL .= 'pName' . $index . ',';
				if(($adminlangsettings & 2)==2) $sSQL .= 'pDescription' . $index . ',';
				if(($adminlangsettings & 4)==4) $sSQL .= 'pLongDescription' . $index . ',';
			}
			$sSQL .= 'pPrice,pWholesalePrice,pListPrice,pShipping,pShipping2,pDisplay,';
			if(@$perproducttaxrate==TRUE) $sSQL .= 'pTax,';
			if($stockManage != 0 && is_numeric(trim(@$_POST['inStock']))) $sSQL .= 'pInStock,';
			if(($adminUnits & 12) > 0) $sSQL .= 'pDims,';
			if(@$digidownloads==TRUE) $sSQL .= 'pDownload,';
			$sSQL .= 'pStockByOpts,pStaticPage,pRecommend,pSell,pWeight) VALUES (';
						$sSQL .= "'" . escape_string(unstripslashes(@$_POST['newid'])) . "',";
						$sSQL .= "'" . escape_string(unstripslashes(@$_POST['pName'])) . "',";
						if(trim(@$_POST['pDateAdded']) != '')
							$sSQL .= "'" . date('Y-m-d', parsedate(@$_POST['pDateAdded'])) . "',";
						else
							$sSQL .= "'" . date('Y-m-d', time()) . "',";
						$sSQL .= @$_POST['pSection'] . ',';
						$sSQL .= @$_POST['pDropship'] . ',';
						$sSQL .= @$_POST['pManufacturer'] . ',';
						$sSQL .= (@$_POST['pSearchCriteria']!=''?@$_POST['pSearchCriteria']:'0') . ',';
						$sSQL .= "'" . escape_string(unstripslashes(@$_POST['pSKU'])) . "',";
						$sSQL .= $pOrder . ",";
						$sSQL .= $pExemptions . ",";
						$sSQL .= "'" . escape_string(unstripslashes(@$_POST['pSearchParams'])) . "',";
						$sSQL .= "'" . escape_string(unstripslashes(@$_POST['pDescription'])) . "',";
						$sSQL .= "'" . escape_string(unstripslashes(@$_POST["pLongDescription"])) . "',";
						for($index=2; $index <= $adminlanguages+1; $index++){
							if(($adminlangsettings & 1)==1) $sSQL .= "'" . escape_string(unstripslashes(@$_POST["pName" . $index])) . "',";
							if(($adminlangsettings & 2)==2) $sSQL .= "'" . escape_string(unstripslashes(@$_POST["pDescription" . $index])) . "',";
							if(($adminlangsettings & 4)==4) $sSQL .= "'" . escape_string(unstripslashes(@$_POST["pLongDescription" . $index])) . "',";
						}
						$sSQL .= trim(@$_POST['pPrice']) . ',';
						if(trim(@$_POST['pWholesalePrice']) != '')
							$sSQL .= trim(@$_POST['pWholesalePrice']) . ',';
						else
							$sSQL .= '0,';
						if(trim(@$_POST['pListPrice']) != '')
							$sSQL .= trim(@$_POST['pListPrice']) . ',';
						else
							$sSQL .= '0,';
						if(! is_numeric(trim(@$_POST['pShipping'])))
							$sSQL .= '0,';
						else
							$sSQL .= trim(@$_POST['pShipping']) . ',';
						if(! is_numeric(trim(@$_POST['pShipping2'])))
							$sSQL .= '0,';
						else
							$sSQL .= trim(@$_POST['pShipping2']) . ',';
						if(trim(@$_POST['pDisplay']) == 'ON')
							$sSQL .= '1,';
						else
							$sSQL .= '0,';
						if(@$perproducttaxrate==TRUE) $sSQL .= "'" . @$_POST['pTax'] . "',";
						if($stockManage != 0 && is_numeric(trim(@$_POST['inStock'])))
							$sSQL .= trim(@$_POST['inStock']) . ',';
						if(($adminUnits & 12) > 0)
							$sSQL .= "'" . trim(@$_POST['plen']) . 'x' . trim(@$_POST['pwid']) . 'x' . trim(@$_POST['phei']) . "',";
						if(@$digidownloads==TRUE)
							$sSQL .= "'" . escape_string(unstripslashes(@$_POST['pDownload'])) . "',";
						$sSQL .= (trim(@$_POST['pStockByOpts']) == '1' ? 1 : 0) . ',';
						$sSQL .= (trim(@$_POST['pStaticPage']) == '1' ? 1 : 0) . ',';
						$sSQL .= (trim(@$_POST['pRecommend']) == '1' ? 1 : 0) . ',';
						$sSQL .= (trim(@$_POST['pSell']) == 'ON' ? 1 : 0) . ',';
						if(is_numeric(trim(@$_POST['pWeight']))) $sSQL .= trim(@$_POST['pWeight']); else $sSQL .= '0';
						$sSQL .= ')';
			mysql_query($sSQL) or print(mysql_error());
			for($rowcounter=0; $rowcounter < maxprodopts; $rowcounter++){
				if(@$_POST['pOption' . $rowcounter] != '' && @$_POST['pOption' . $rowcounter] != 0){
					$sSQL = "INSERT INTO prodoptions (poProdID,poOptionGroup) VALUES ('" . @$_POST['newid'] . "'," . @$_POST['pOption' . $rowcounter] . ')';
					mysql_query($sSQL) or print(mysql_error());
				}
			}
			$sSQL = "DELETE FROM multisections WHERE pID='" . @$_POST['newid'] . "'";
			mysql_query($sSQL) or print(mysql_error());
			for($rowcounter=0; $rowcounter < $maxprodsects; $rowcounter++){
				if(@$_POST['pSection' . $rowcounter] != '' && @$_POST['pSection' . $rowcounter] != 0 && @$_POST['pSection'] != @$_POST['pSection' . $rowcounter]){
					$sSQL = "INSERT INTO multisections (pID,pSection) VALUES ('" . @$_POST['newid'] . "'," . @$_POST['pSection' . $rowcounter] . ')';
					mysql_query($sSQL) or print(mysql_error());
				}
			}
			$dorefresh=TRUE;
		}else
			$errmsg = $yyPrDup;
	}elseif(@$_POST["act"]=="dodiscounts"){
		$sSQL = "INSERT INTO cpnassign (cpaCpnID,cpaType,cpaAssignment) VALUES (" . @$_POST["assdisc"] . ",2,'" . @$_POST["id"] . "')";
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}elseif(@$_POST["act"]=="deletedisc"){
		$sSQL = "DELETE FROM cpnassign WHERE cpaID=" . @$_POST["id"];
		mysql_query($sSQL) or print(mysql_error());
		$dorefresh=TRUE;
	}
	if($success && (@$_POST['act']=="domodify" || @$_POST['act']=='doaddnew')){
		$maximgindex=(int)@$_POST['maximgindex'];
		if($_POST['act']=='domodify') mysql_query("DELETE FROM productimages WHERE imageProduct='" . escape_string(@$_POST['id']) . "'") or print(mysql_error());
		for($index=0; $index<=$maximgindex; $index++){
			if(@$_POST['smim' . $index]!='') mysql_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('" . escape_string(@$_POST['newid']) . "','" . escape_string(@$_POST['smim' . $index]) . "'," . $index . ",0)") or print(mysql_error());
			if(@$_POST['lgim' . $index]!='') mysql_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('" . escape_string(@$_POST['newid']) . "','" . escape_string(@$_POST['lgim' . $index]) . "'," . $index . ",1)") or print(mysql_error());
			if(@$_POST['gtim' . $index]!='') mysql_query("INSERT INTO productimages (imageProduct,imageSrc,imageNumber,imageType) VALUES ('" . escape_string(@$_POST['newid']) . "','" . escape_string(@$_POST['gtim' . $index]) . "'," . $index . ",2)") or print(mysql_error());
		}
	}
	if(@$_POST['act']=='modify' || @$_POST['act']=='clone' || @$_POST['act']=='addnew'){
		$sSQL = 'SELECT optGrpID,optGrpWorkingName,optType FROM optiongroup ORDER BY optGrpWorkingName';
		$nalloptions=0;
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_row($result))
			$alloptions[$nalloptions++] = $rs;
		mysql_free_result($result);
		if(@$_POST['act']=='modify' || @$_POST['act']=='clone'){
			$sSQL = "SELECT poID, poOptionGroup FROM prodoptions WHERE poProdID='" . escape_string(unstripslashes(@$_POST['id'])) . "' ORDER BY poID";
			$nprodoptions=0;
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_row($result))
				$prodoptions[$nprodoptions++] = $rs;
			mysql_free_result($result);
			$sSQL = "SELECT pSection FROM multisections WHERE pID='" . escape_string(unstripslashes(@$_POST['id'])) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_row($result))
				$prodsections[$nprodsections++] = $rs;
			mysql_free_result($result);
		}
		$sSQL = "SELECT sectionID, sectionWorkingName FROM sections WHERE rootSection=1 ORDER BY sectionWorkingName";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_assoc($result))
			$allsections[$nallsections++] = $rs;
		mysql_free_result($result);
		$dynamicadminmenus = ($dynamicadminmenus && $nallsections>200);
		$sSQL = "SELECT dsID,dsName FROM dropshipper ORDER BY dsName";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_assoc($result))
			$alldropship[$nalldropship++] = $rs;
		mysql_free_result($result);
		$sSQL = "SELECT mfID,mfName FROM manufacturer ORDER BY mfName";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_assoc($result))
			$allmanufacturer[$nallmanufacturer++] = $rs;
		mysql_free_result($result);
	}elseif(@$_POST['act']=='sort'){
		setcookie('psort', @$_POST['sort'], time()+31536000, '/', '', @$_SERVER['HTTPS']=='on');
	}
}elseif(@$_GET['pract']!=''){
	setcookie('pract', @$_GET['pract'], time()+31536000, '/', '', @$_SERVER['HTTPS']=='on');
}elseif(@$_GET['catorman']!=''){
	setcookie('pcatorman', @$_GET['catorman'], time()+80000000, '/', '', @$_SERVER['HTTPS']=='on');
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=adminprods.php';
	print '?rid=' . @$_POST['rid'] . '&disp=' . @$_POST['disp'] . '&stext=' . urlencode(@$_POST['stext']) . '&sprice=' . urlencode(@$_POST['sprice']) . '&stype=' . @$_POST['stype'] . '&scat=' . @$_POST['scat'] . '&pg=' . @$_POST['pg'];
	print '">';
}
function show_info(){
	global $yyPrEx1, $yyPrEx2;
?>
		<a name="info"></a><ul>
		  <li><span style="font-size:10px"><?php print $yyPrEx1?></span></li>
		  <li><span style="font-size:10px"><?php print $yyPrEx2?></span></li>
		</ul>
<?php
}
if(@$_POST["posted"]=="1" && (@$_POST["act"]=="modify" || @$_POST["act"]=="clone" || @$_POST["act"]=="addnew")){
		if(@$htmleditor=='tinymce'){ ?>
<script language="javascript" type="text/javascript" src="tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
	tinyMCE.init({
		theme : "simple",
		mode : "textareas",
		valid_elements : "*[*]",
		extended_valid_elements : "a[class|href|target|name|onclick]," +
			"embed[quality|type|pluginspage|width|height|src|align]," +
			"hr[class|width|size|noshade]," + 
			"img[class|src|border|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name]," +
			"object[classid|codebase|width|height|align]," +
			"param[name|value]," +
			"input[checked|class|disabled|id|name|type|value|size|maxlength|src|width|height|readonly|tabindex|onfocus|onblur|onchange|onselect]",
		debug : false
	});
	tinyMCE.addToLang('',{
		plus_desc : 'Plus'
	});
/* ]]> */
</script>
<?php	}elseif(@$htmleditor=='fckeditor'){ ?>
<script type="text/javascript" src="fckeditor.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
function FCKeditor_OnComplete(editorInstance){
	editorInstance.Events.AttachEvent('OnBlur', FCKeditor_OnBlur);
	editorInstance.Events.AttachEvent('OnFocus', FCKeditor_OnFocus);
	editorInstance.ToolbarSet.Collapse();
}
function FCKeditor_OnBlur(editorInstance){
	editorInstance.ToolbarSet.Collapse();
}
function FCKeditor_OnFocus(editorInstance){
	editorInstance.ToolbarSet.Expand();
}
var sBasePath = document.location.pathname.substring(0,document.location.pathname.lastIndexOf('adminprods.php'));
/* ]]> */
</script>
<?php	}
		$maximagenumber=-1;
		$imageindex=0;
		$smimgindx=0;
		$lgimgindx=0;
		$gtimgindx=0;
		$numsmimgs=0;
		$numlgimgs=0;
		$numgtimgs=0;
		function getnext3images(&$smimg,&$lgimg,&$gtimg){
			global $smimgindx,$lgimgindx,$gtimgindx,$numsmimgs,$numlgimgs,$numgtimgs,$allsmimgs,$alllgimgs,$allgtimgs;
			$smimg=''; $lgimg=''; $gtimg='';
			if($smimgindx<$numsmimgs){ $smimg=$allsmimgs[$smimgindx]['imageSrc']; $smimgindx++; }else $smimg='';
			if($lgimgindx>=$numlgimgs){
				if($gtimgindx>=$numgtimgs) $gtimg=''; else{ $gtimg=$allgtimgs[$gtimgindx]['imageSrc']; $gtimgindx++; }
			}elseif($gtimgindx>=$numgtimgs){
				if($lgimgindx>=$numlgimgs) $lgimg=''; else{ $lgimg=$alllgimgs[$lgimgindx]['imageSrc']; $lgimgindx++; }
			}elseif($alllgimgs[$lgimgindx]['imageNumber'] > $allgtimgs[$gtimgindx]['imageNumber']){
				$gtimg=$allgtimgs[$gtimgindx]['imageSrc']; $gtimgindx++;
			}elseif($alllgimgs[$lgimgindx]['imageNumber'] < $allgtimgs[$gtimgindx]['imageNumber']){
				$lgimg=$alllgimgs[$lgimgindx]['imageSrc']; $lgimgindx++;
			}else{
				$lgimg=$alllgimgs[$lgimgindx]['imageSrc']; $lgimgindx++;
				$gtimg=$allgtimgs[$gtimgindx]['imageSrc']; $gtimgindx++;
			}
		}
		function displayimagerow($imgrow,$smimg,$lgimg,$gtimg){
			print '<tr>';
			print '<td><input type="text" name="smim' . $imgrow . '" id="smim' . $imgrow . '" value="' . htmlspecials($smimg) . '" size="30" ' . ($imgrow==0 ? 'onchange="document.getElementById(\'pImage\').value=this.value"' : '') . '/>&nbsp;<input type="button" value="..." onclick="uploadimage(\'smim' . $imgrow . '\')" /></td>';
			print '<td><input type="text" name="lgim' . $imgrow . '" id="lgim' . $imgrow . '" value="' . htmlspecials($lgimg) . '" size="30" ' . ($imgrow==0 ? 'onchange="document.getElementById(\'pLargeImage\').value=this.value"' : '') . '/>&nbsp;<input type="button" value="..." onclick="uploadimage(\'lgim' . $imgrow . '\')" /></td>';
			print '<td><input type="text" name="gtim' . $imgrow . '" id="gtim' . $imgrow . '" value="' . htmlspecials($gtimg) . '" size="30" ' . ($imgrow==0 ? 'onchange="document.getElementById(\'pGiantImage\').value=this.value"' : '') . '/>&nbsp;<input type="button" value="..." onclick="uploadimage(\'gtim' . $imgrow . '\')" /></td>';
			print '</tr>';
		}
		$doaddnew=TRUE;
		if(@$_POST['act']=='modify' || @$_POST['act']=='clone'){
			$sSQL="SELECT imageSrc,imageNumber,imageType FROM productimages WHERE imageProduct='" . escape_string(@$_POST['id']) . "' AND imageType=0 ORDER BY imageNumber";
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_assoc($result)){
				$allsmimgs[$numsmimgs++]=$rs;
			}
			mysql_free_result($result);
			$sSQL="SELECT imageSrc,imageNumber,imageType FROM productimages WHERE imageProduct='" . escape_string(@$_POST['id']) . "' AND imageType=1 ORDER BY imageNumber";
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_assoc($result)){
				$alllgimgs[$numlgimgs++]=$rs;
			}
			mysql_free_result($result);
			$sSQL="SELECT imageSrc,imageNumber,imageType FROM productimages WHERE imageProduct='" . escape_string(@$_POST['id']) . "' AND imageType=2 ORDER BY imageNumber";
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_assoc($result)){
				$allgtimgs[$numgtimgs++]=$rs;
			}
			mysql_free_result($result);
			$maximagenumber=max(max($numsmimgs,$numlgimgs),$numgtimgs);
			$sSQL = 'SELECT pId,pName,pName2,pName3,pSection,pDescription,pDescription2,pDescription3,pPrice,pWholesalePrice,pListPrice,pDisplay,pStaticPage,pRecommend,pStockByOpts,pSell,pShipping,pShipping2,pWeight,pLongDescription,pLongDescription2,pLongDescription3,pExemptions,pSearchParams,pInStock,pDims,pTax,pDropship,pManufacturer,pSearchCriteria,pSKU,pOrder,pDateAdded';
			if(@$digidownloads==TRUE) $sSQL .= ',pDownload';
			$sSQL .= " FROM products WHERE pId='" . escape_string(unstripslashes(@$_POST['id'])) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($alldata = mysql_fetch_assoc($result)){
				$doaddnew=FALSE;
				$pId = $alldata['pId'];
				$pName = $alldata['pName'];
				for($index=2; $index <= $adminlanguages+1; $index++){
					$pNames[$index] = $alldata['pName' . $index];
					$pDescriptions[$index] = $alldata['pDescription' . $index];
					$pLongDescriptions[$index] = $alldata['pLongDescription' . $index];
				}
				$pSection = $alldata['pSection'];
				$pDescription = $alldata['pDescription'];
				$pPrice = $alldata['pPrice'];
				$pWholesalePrice = $alldata['pWholesalePrice'];
				$pListPrice = $alldata['pListPrice'];
				$pDisplay = $alldata['pDisplay'];
				$pStaticPage = $alldata['pStaticPage'];
				$pRecommend = $alldata['pRecommend'];
				$pStockByOpts = $alldata['pStockByOpts'];
				$pSell = $alldata['pSell'];
				$pShipping = $alldata['pShipping'];
				$pShipping2 = $alldata['pShipping2'];
				$pWeight = $alldata['pWeight'];
				$pLongDescription = $alldata['pLongDescription'];
				$pExemptions = $alldata['pExemptions'];
				$pSearchParams = $alldata['pSearchParams'];
				$pInStock = $alldata['pInStock'];
				$pDims = $alldata['pDims'];
				$pTax = $alldata['pTax'];
				$pDropship = $alldata['pDropship'];
				$pManufacturer = $alldata['pManufacturer'];
				$pSearchCriteria = $alldata['pSearchCriteria'];
				$pSKU = $alldata['pSKU'];
				$pOrder = $alldata['pOrder'];
				$pDateAdded = $alldata['pDateAdded'];
				if(is_null($pDateAdded) || @$_POST['act']=='clone') $pDateAdded = date($admindatestr, time() + ($dateadjust*60*60)); else $pDateAdded = date($admindatestr, strtotime($pDateAdded));
				if(@$digidownloads==TRUE) $pDownload = $alldata['pDownload'];
			}
			mysql_free_result($result);
		}
		if($doaddnew){
			$pId = '';
			$pName = '';
			for($index=2; $index <= $adminlanguages+1; $index++){
				$pNames[$index] = '';
				$pDescriptions[$index] = '';
				$pLongDescriptions[$index] = '';
			}
			if(trim(@$_POST['scat']) != '') $pSection=(int)trim(@$_POST['scat']); else $pSection = 0;
			$pSearchParams = '';
			$pDescription = '';
			$pImage = $defaultprodimages;
			$pPrice = '';
			$pWholesalePrice = '';
			$pListPrice = 0;
			$pDisplay = 1;
			$pStaticPage = 0;
			$pRecommend = 0;
			$pStockByOpts = 0;
			$pSell = 1;
			$pShipping = '';
			$pShipping2 = '';
			$pLargeImage = $defaultprodimages;
			$pGiantImage = '';
			$pWeight = '';
			$pLongDescription = '';
			$pExemptions = 0;
			$pInStock = '';
			$pDims = '';
			$pTax = '';
			$pDropship = 0;
			$pManufacturer = 0;
			$pSearchCriteria = 0;
			$pSKU = '';
			$pOrder = 0;
			$pDateAdded = date($admindatestr, time() + ($dateadjust*60*60));
			$pDownload = '';
		}
?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
var mUO = new Array(); // Multi Input Options must be unique
<?php
for($rowcounter=0;$rowcounter < $nalloptions;$rowcounter++){
	if($alloptions[$rowcounter][2]==4) print 'mUO['.$alloptions[$rowcounter][0].']=1;';
}
?>
function checkastring(thestr,validchars){
  for (i=0; i < thestr.length; i++){
    ch = thestr.charAt(i);
    for (j = 0;  j < validchars.length;  j++)
      if (ch == validchars.charAt(j))
        break;
    if (j == validchars.length)
	  return(false);
  }
  return(true);
}
function formvalidator(theForm){
  if (theForm.newid.value == ""){
    alert("<?php print $yyPlsEntr?> \"<?php print $yyPrRef?>\".");
    theForm.newid.focus();
    return (false);
  }
  if (theForm.pSection.options[theForm.pSection.selectedIndex].value == ""){
    alert("<?php print $yyPlsSel?> \"<?php print $yySection?>\".");
    theForm.pSection.focus();
    return (false);
  }
  if (theForm.pName.value == ""){
    alert("<?php print $yyPlsEntr?> \"<?php print $yyPrNam?>\".");
    theForm.pName.focus();
    return (false);
  }
<?php	for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 1)==1){ ?>
  if (theForm.pName<?php print $index?>.value == ""){
    alert("<?php print $yyPlsEntr?> \"<?php print $yyPrNam . " " . $index?>\".");
    theForm.pName<?php print $index?>.focus();
    return (false);
  }
<?php		}
		} ?>
  if (theForm.pPrice.value == ""){
    alert("<?php print $yyPlsEntr?> \"<?php print $yyPrPri?>\".");
    theForm.pPrice.focus();
    return (false);
  }
  var checkOK = "'\" ";
  var checkStr = theForm.newid.value;
  var allValid = true;
  for (i = 0;  i < checkStr.length;  i++){
    ch = checkStr.charAt(i);
    for (j = 0;  j < checkOK.length;  j++)
      if (ch == checkOK.charAt(j)){
	    allValid = false;
        break;
	  }
  }
  if (!allValid){
    alert("<?php print $yyQuoSpa?> \"<?php print $yyPrRef?>\".");
    theForm.newid.focus();
    return (false);
  }
  if (!checkastring(theForm.pPrice.value,"0123456789.")){
    alert("<?php print $yyOnlyDec?> \"<?php print $yyPrPri?>\".");
    theForm.pPrice.focus();
    return (false);
  }
  if (!checkastring(theForm.pWholesalePrice.value,"0123456789.")){
    alert("<?php print $yyOnlyDec?> \"<?php print $yyWhoPri?>\".");
    theForm.pWholesalePrice.focus();
    return (false);
  }
  if (!checkastring(theForm.pListPrice.value,"0123456789.")){
    alert("<?php print $yyOnlyDec?> \"<?php print $yyListPr?>\".");
    theForm.pListPrice.focus();
    return (false);
  }
<?php	if(($adminUnits & 12) > 0){ ?>
  var checkOK = "0123456789.";
  if (!checkastring(theForm.plen.value,checkOK)){
	alert("<?php print $yyOnlyDec?> \"<?php print $yyDims?>\".");
	theForm.plen.focus();
	return(false);
  }
  if (!checkastring(theForm.pwid.value,checkOK)){
	alert("<?php print $yyOnlyDec?> \"<?php print $yyDims?>\".");
	theForm.pwid.focus();
	return(false);
  }
  if (!checkastring(theForm.phei.value,checkOK)){
	alert("<?php print $yyOnlyDec?> \"<?php print $yyDims?>\".");
	theForm.phei.focus();
	return(false);
  }
<?php	}
		if($usesshipweight){ ?>
  var checkOK = "0123456789.";
  if (!checkastring(theForm.pWeight.value,checkOK)){
    alert("<?php print $yyOnlyDec?> \"<?php print $yyPrWght?>\".");
    theForm.pWeight.focus();
    return (false);
  }
<?php	}
		if($usesflatrate){ ?>
  var checkOK = "0123456789.";
  if (!checkastring(theForm.pShipping.value,checkOK)){
    alert("<?php print $yyOnlyDec?> \"<?php print $yyFlatShp . ": " . $yyFirShi?>\".");
    theForm.pShipping.focus();
    return (false);
  }
  if (!checkastring(theForm.pShipping2.value,"0123456789.")){
    alert("<?php print $yyOnlyDec?> \"<?php print $yyFlatShp . ": " . $yySubShi?>\".");
    theForm.pShipping2.focus();
    return (false);
  }
<?php	}
		if($stockManage != 0){ ?>
  if (!(theForm.pStockByOpts.selectedIndex==1) && theForm.inStock.value == ""){
    alert("<?php print $yyPlsEntr?> \"<?php print $yyInStk?>\".");
    theForm.inStock.focus();
    return (false);
  }
  if (!(theForm.pStockByOpts.selectedIndex==1) && !checkastring(theForm.inStock.value,"0123456789")){
    alert("<?php print $yyOnlyNum?> \"<?php print $yyInStk?>\".");
    theForm.inStock.focus();
    return (false);
  }
  if(theForm.pStockByOpts.selectedIndex==1 && theForm.pNumOptions.selectedIndex==0){
    alert("<?php print $yyStkWrn?>");
    theForm.pStockByOpts.focus();
    return (false);
  }
<?php	}
		if(@$perproducttaxrate==TRUE){ ?>
  if (theForm.pTax.value == ""){
	alert("<?php print $yyPlsEntr?> \"<?php print $yyTax?>\".");
	theForm.pTax.focus();
	return(false);
  }
  if (!checkastring(theForm.pTax.value,"0123456789.")){
    alert("<?php print $yyOnlyDec?> \"<?php print $yyTax?>\".");
    theForm.pTax.focus();
    return (false);
  }
<?php	} ?>
  if (!checkastring(theForm.pOrder.value,"0123456789")){
    alert("<?php print $yyOnlyNum?> \"<?php print $yyProdOr?>\".");
    theForm.pOrder.focus();
    return (false);
  }
  if(theForm.pNumOptions.selectedIndex>1){
	nummultioptions=0;
	unselected=0;
	for(index=0;index<currnumopts;index++){
		var thisOption = document.getElementById('pOption'+index);
		if(parseInt(thisOption.selectedIndex)==0){
			unselected++;
		}else if(mUO[thisOption.options[thisOption.selectedIndex].value]==1){
			nummultioptions++;
		}
	}
	if(nummultioptions>1){
		alert("<?php print $yyMBOUni?>");
		theForm.pNumOptions.focus();
		return (false);
	}
  }
  return (true);
}
var prodOptGrpArr = new Array();
var prodSectGrpArr = new Array();
var currnumopts=0;
<?php
$rowcounter=0;
for($rowcounter=0;$rowcounter < $nprodoptions;$rowcounter++)
	print "prodOptGrpArr[" . $rowcounter . "]=" . $prodoptions[$rowcounter][1] . ";\r\n";
print "for(ii=" . $rowcounter . ";ii<" . maxprodopts . ";ii++) prodOptGrpArr[ii]=0;\r\n";
for($rowcounter=0;$rowcounter < $nprodsections;$rowcounter++)
	print "prodSectGrpArr[" . $rowcounter . "]=" . $prodsections[$rowcounter][0] . ";\r\n";
print "for(ii=" . $rowcounter . ";ii<" . $maxprodsects . ";ii++) prodSectGrpArr[ii]=0;\r\n";
?>
function update_opts(index){
	var thisOption = document.getElementById('pOption'+index);
	prodOptGrpArr[index] = thisOption.options[thisOption.selectedIndex].value;
}
function update_sects(index){
	var thisSection = document.getElementById('pSection'+index);
	prodSectGrpArr[index] = thisSection.options[thisSection.selectedIndex].value;
}
function setprodoptions(){
	var newnumopts = document.forms.mainform.pNumOptions.selectedIndex;
	var baserow = document.getElementById('numoptionsrow').rowIndex+1;
	var thetable = document.getElementById('producttable');
	currrows=Math.ceil(currnumopts/2);
	newrows=Math.ceil(newnumopts/2);
	if(newnumopts>currnumopts){
		for(index=0; index<(newrows-currrows); index++){
			newrow = thetable.insertRow(baserow+currrows);
			newcell = newrow.insertCell(0);
			newcell.align='right';
			newcell = newrow.insertCell(1);
			newcell.innerHTML = '&nbsp;';
			newcell = newrow.insertCell(2);
			newcell.align='right';
			newcell = newrow.insertCell(3);
			newcell.innerHTML = '&nbsp;';
		}
		for(index=currnumopts; index<newnumopts; index++){
			var therow=baserow+Math.ceil((index+1)/2)-1;
			var thecell=(1-((index+1) % 2))*2;
			thetable.rows[therow].cells[thecell].innerHTML = '<?php print $yyPrdOpt?> '+(index+1)+':';
			thetable.rows[therow].cells[thecell+1].innerHTML = '<select size="1" id="pOption'+index+'" name="pOption'+index+'" onchange="update_opts('+index+');"><option value="0"><?php print $yyNone?></option><?php
		for($rowcounter=0;$rowcounter < $nalloptions;$rowcounter++)
			print '<option value="' . $alloptions[$rowcounter][0] . '">' . str_replace("'","\'",$alloptions[$rowcounter][1]) . '</option>';
?></select>';
		}
		for(index=currnumopts; index<newnumopts; index++){
			var thisOption = document.getElementById('pOption'+index);
			for (index2=0;index2<thisOption.length;index2++){
				if (thisOption[index2].value==prodOptGrpArr[index]){
					thisOption.selectedIndex=index2;
					thisOption.options[index2].selected = true;
				}
				else
					thisOption.options[index2].selected = false;
			}
		}
	}else if(newnumopts<currnumopts){
		if(newrows<currrows){
			for(index=0; index<(currrows-newrows); index++){
				thetable.deleteRow((baserow+currrows)-(index+1));
			}
		}
		if((newnumopts % 2)!=0){
			var therow=baserow+Math.ceil(newnumopts/2)-1;
			thetable.rows[therow].cells[2].innerHTML = '&nbsp;';
			thetable.rows[therow].cells[3].innerHTML = '&nbsp;';
		}
		
	}
	currnumopts = newnumopts;
}
var car=[], caro=[];
<?php
	for($rowcounter=0;$rowcounter < $nallsections;$rowcounter++){
		print 'caro['.$rowcounter.']='.$allsections[$rowcounter]['sectionID'].';car[' . $allsections[$rowcounter]['sectionID'] . "]='" . str_replace("'","\'",$allsections[$rowcounter]['sectionWorkingName']) . "';\n";
	}
?>
function populatecatmenu(tmen){
	if(! tmen.ispopulated){
		currmen=tmen.options[tmen.selectedIndex];
		currval=currmen.value;
		addbefore=(currval!=''&&currval!='0');
		for(ii in car){
			if(ii==currval){
				addbefore=false;
			}else{
				var y=document.createElement('option');
				y.text=car[ii];
				y.value=ii;
				if(addbefore){
					tmen.add(y,currmen);
				}else{
					try{
						tmen.add(y,null);
					}
					catch(ex){
						tmen.add(y);
					}
				}
			}
		}
		tmen.ispopulated=true;
	}
}
function setprodsections(){
	var noSects = document.forms.mainform.pNumSections.selectedIndex;
	var theHTML="";
	var index=0;
	var theElm=document.getElementById('prodsections');
	var theHTMLHead='<table width="100%" border="0" cellspacing="0" cellpadding="3">';
	var theHTMLSel='<select size="1" id="pSectionGGREPLACEMExx" name="pSectionGGREPLACEMExx" onchange="update_sects(GGREPLACEMExx);"<?php print ($dynamicadminmenus==TRUE?' onclick="populatecatmenu(this)"':'')?>><option value="0">None</option>';
<?php	if($dynamicadminmenus!=TRUE){ ?>
	for(var ii=0; ii<caro.length;ii++)
		theHTML+='<option value="'+caro[ii]+'">'+car[caro[ii]]+'</option>';
<?php	} ?>
	theHTML+='</select>';
	for (index=0;index<noSects;index++){
		if(index % 2 == 0) theHTMLHead+='<tr>';
		theHTMLHead+='<td align="right">Prod. Section '+(index+1)+':</td><td>'+theHTMLSel.replace(/GGREPLACEMExx/g,index)+<?php print ($dynamicadminmenus==TRUE?"(prodSectGrpArr[index]!=0?'<option value=\"'+prodSectGrpArr[index]+'\">'+car[prodSectGrpArr[index]]+'</option>':'')+":'')?>theHTML+'</td>';
		if(index % 2 != 0) theHTMLHead+='</tr>';
	}
	if(index % 2 != 0) theHTMLHead+='<td colspan="2">&nbsp;</td></tr>';
	theHTMLHead+='</table>';
	theElm.innerHTML=theHTMLHead;
	for (index=0;index<noSects;index++){
		var thisSection = document.getElementById('pSection'+index);
		for (index2=0;index2<thisSection.length;index2++){
			if (thisSection[index2].value==prodSectGrpArr[index]){
				thisSection.selectedIndex=index2;
				break;
			}
		}
	}
}
function setstocktype(){
var si = document.forms.mainform.pStockByOpts.selectedIndex;
document.forms.mainform.inStock.disabled=(si==1);
}
function uploadimage(imfield){
	var addthumb=0;
	var winwid=350; var winhei=220;
	if(imfield.substring(0,2)=='pG' || imfield.substring(0,2)=='gt'){ addthumb=2; winhei=320; }
	if(imfield.substring(0,2)=='pL' || imfield.substring(0,2)=='lg'){ addthumb=1; winhei=290; }
	var prnttext = '<html><head><link rel="stylesheet" type="text/css" href="adminstyle.css"/><script type="text/javascript">function getCookie(c_name){if(document.cookie.length>0){var c_start=document.cookie.indexOf(c_name + "=");if(c_start!=-1){c_start=c_start+c_name.length+1;var c_end=document.cookie.indexOf(";",c_start);if(c_end==-1)c_end=document.cookie.length;return unescape(document.cookie.substring(c_start,c_end));}}return "";}';
	prnttext += 'function checkcookies(){ for(var ind=0; ind<='+addthumb+'; ind++){\r\n';
	prnttext += 'document.getElementById("newdim"+ind).value=getCookie("newdim"+ind);\r\n';
	prnttext += 'if(getCookie("suffix"+ind)!="")document.getElementById("suffix"+ind).value=getCookie("suffix"+ind);\r\n';
	prnttext += 'if(getCookie("thumbdim"+ind)!="")document.getElementById("thumbdim"+ind).selectedIndex=getCookie("thumbdim"+ind);}\r\n';
	if(addthumb>0) prnttext += 'if(getCookie("populate")=="ON")document.getElementById("populate").checked=true;\r\n';
	prnttext += '}<'+'/script></head><body<?php if(extension_loaded('gd')) print ' onload="checkcookies()"'?>>\n';
	prnttext += '<form name="mainform" method="post" action="doupload.php?defimagepath=<?php print $defaultprodimages?>" enctype="multipart/form-data">';
	prnttext += '<input type="hidden" name="defimagepath" value="<?php print $defaultprodimages?>" />';
	prnttext += '<input type="hidden" name="imagefield" value="'+imfield+'" />';
	prnttext += '<table border="0" cellspacing="1" cellpadding="1" width="100%">';
	prnttext += '<tr><td align="center" colspan="2">&nbsp;<br /><strong><?php print str_replace("'", "\\'", $yyUplIma)?></strong><br />&nbsp;</td></tr>';
	prnttext += '<tr><td align="center" colspan="2"><?php print str_replace("'", "\\'", $yyPlsSUp)?><br />&nbsp;</td></tr>';
	prnttext += '<tr><td align="center" colspan="2"><?php print str_replace("'", "\\'", $yyLocIma)?>:<input type="file" name="imagefile" /></td></tr>';
<?php	if(extension_loaded('gd')){
			$winhei = 260; ?>
	prnttext += '<tr><td colspan="2">&nbsp;</td></tr><tr><td align="right"><select size="1" name="thumbdim0" id="thumbdim0"><option value="">Don\'t Resize Image</option><option value="1">Resize to Width:</option><option value="2">Resize to Height:</option></select></td><td><input type="text" name="newdim0" id="newdim0" size="3" />:px&nbsp;&nbsp;</td></tr>';
	if(imfield.substring(0,2)=='pL' || imfield.substring(0,2)=='lg' || imfield.substring(0,2)=='pG' || imfield.substring(0,2)=='gt') prnttext += '<tr><td align="right"><input type="hidden" name="hasrow1" value="1" /><select size="1" name="thumbdim1" id="thumbdim1"><option value="">No <?php print $yyImage?></option><option value="1"><?php print $yyImage?> Width:</option><option value="2"><?php print $yyImage?> Height:</option></select></td><td><input type="text" name="newdim1" id="newdim1" size="3" />:px&nbsp;&nbsp;Suffix:<input type="text" name="suffix1" id="suffix1" size="6" value="_small" /></td></tr>';
	if(imfield.substring(0,2)=='pG' || imfield.substring(0,2)=='gt') prnttext += '<tr><td align="right"><input type="hidden" name="hasrow2" value="1" /><select size="1" name="thumbdim2" id="thumbdim2"><option value="">No <?php print $yyLgeImg?></option><option value="1"><?php print $yyLgeImg?> Width:</option><option value="2"><?php print $yyLgeImg?> Height:</option></select></td><td><input type="text" name="newdim2" id="newdim2" size="3" />:px&nbsp;&nbsp;Suffix:<input type="text" name="suffix2" id="suffix2" size="6" value="_medium" /></td></tr>';
	if(addthumb>0) prnttext += '<tr><td colspan="2" align="center">&nbsp;<br />Populate smaller image fields? <input type="checkbox" name="populate" id="populate" value="ON" /></td></tr>';
<?php	}else
			$winhei = 200; ?>
	prnttext += '<tr><td colspan="2" align="center">&nbsp;<br /><input type="submit" value="<?php print str_replace("'", "\\'", $yySubmit)?>" /></td></tr>';
	prnttext += '</table></form>';
	prnttext += '<p align="center"><a href="javascript:window.close()"><strong><?php print str_replace("'", "\\'", $yyClsWin)?></strong></a></p>';
	prnttext += '</body></html>';
	var scrwid=screen.width; var scrhei=screen.height;
	var newwin = window.open("","uploadimage",'menubar=no,scrollbars=yes,width='+winwid+',height='+winhei+',left='+((scrwid-winwid)/2)+',top=100,directories=no,location=no,resizable=yes,status=no,toolbar=no');
	newwin.document.open();
	newwin.document.write(prnttext);
	newwin.document.close();
	newwin.focus();
}
function imagemanager(){
	if(document.getElementById('extraimages').style.display=='none'){
		document.getElementById('extraimages').style.display='';
		document.getElementById('lessimages').style.display='none';
		document.getElementById('but_pImage').value="<?php print $yyClose.' '.$yyImgMgr?>";
		document.getElementById('pImage').disabled=true;
		document.getElementById('smallimup').style.display='none';
		document.getElementById('moreimages').style.display='';
	}else{
		document.getElementById('extraimages').style.display='none';
		document.getElementById('lessimages').style.display='';
		document.getElementById('but_pImage').value="<?php print $yyOpen.' '.$yyImgMgr?>";
		document.getElementById('pImage').disabled=false;
		document.getElementById('smallimup').style.display='';
		document.getElementById('moreimages').style.display='none';
	}
}
function moreimagefn(){
	var thetable = document.getElementById('extraimagetable');
	var currmax = parseInt(document.getElementById('maximgindex').value);
	for(imindx=currmax; imindx<currmax+5; imindx++){
		newrow = thetable.insertRow(-1);
		newcell = newrow.insertCell(0);
		newcell.innerHTML = '<input type="text" name="smim' + imindx + '" id="smim' + imindx + '" value="" size="30" />&nbsp;<input type="button"" value="..." onclick="uploadimage(\'smim' + imindx + '\')" />';
		newcell = newrow.insertCell(1);
		newcell.innerHTML = '<input type="text" name="lgim' + imindx + '" id="lgim' + imindx + '" value="" size="30" />&nbsp;<input type="button"" value="..." onclick="uploadimage(\'lgim' + imindx + '\')" />';
		newcell = newrow.insertCell(2);
		newcell.innerHTML = '<input type="text" name="gtim' + imindx + '" id="gtim' + imindx + '" value="" size="30" />&nbsp;<input type="button"" value="..." onclick="uploadimage(\'gtim' + imindx + '\')" />';
	}
	document.getElementById('maximgindex').value=imindx;
}
/* ]]> */
</script>
<script language="javascript" type="text/javascript" src="popcalendar.js">
</script>
	<form name="mainform" method="post" action="adminprods.php" onsubmit="return formvalidator(this)">
			<input type="hidden" name="posted" value="1" />
			<?php	if(@$_POST["act"]=="modify" && !$doaddnew){ ?>
			<input type="hidden" name="act" value="domodify" />
			<input type="hidden" name="id" value="<?php print str_replace('"',"&quot;",$pId)?>" />
			<?php	}else{ ?>
			<input type="hidden" name="act" value="doaddnew" />
			<?php	}
					writehiddenvar('disp', @$_POST['disp']);
					writehiddenvar('stext', @$_POST['stext']);
					writehiddenvar('sprice', @$_POST['sprice']);
					writehiddenvar('scat', @$_POST['scat']);
					writehiddenvar('stype', @$_POST['stype']);
					writehiddenvar('pg', @$_POST['pg']);
					if(!$usesflatrate){
						print '<input type="hidden" name="pShipping" value="'.$pShipping.'" />';
						print '<input type="hidden" name="pShipping2" value="'.$pShipping2.'" />';
					} ?>
            <table id="producttable" width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="4" align="center"><strong><?php
					if($doaddnew)
						print $yyPrUpd;
					elseif(@$_POST['act']=='modify')
						print $yyYouMod . ' &quot;' . $pName . '&quot;';
					else
						print $yyYouCln . ' &quot;' . $pName . '&quot;';
				?></strong><br />&nbsp;</td>
			  </tr>
			  <tr>
			    <td align="right"><?php print $redasterix.$yyPrRef?>:</td><td><input type="text" name="newid" size="25" value="<?php print str_replace('"',"&quot;",$pId)?>" /></td>
			    <td align="right"><?php print $redasterix.$yySection?>:</td><td><select size="1" name="pSection"<?php print ($dynamicadminmenus==TRUE?' onclick="populatecatmenu(this)"':'')?>><option value=""><?php print $yySelect?></option><?php
					for($index=0;$index<$nallsections;$index++){
						if($dynamicadminmenus!=TRUE || $allsections[$index]['sectionID']==$pSection){
							print "<option value='" . $allsections[$index]['sectionID'] . "'";
							if($allsections[$index]['sectionID']==$pSection) print ' selected="selected"';
							print '>' . $allsections[$index]['sectionWorkingName'] . "</option>\n";
						}
					} ?></select></td>
			  </tr>
			  <tr>
			    <td align="right"><?php print $redasterix.$yyPrNam?>:</td><td><input type="text" name="pName" size="40" value="<?php print htmlspecials($pName)?>" /></td>
			    <td align="right"><?php print $redasterix.$yyPrPri?>:</td><td><input type="text" name="pPrice" size="15" value="<?php print $pPrice?>" /></td>
			  </tr>
<?php		for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 1)==1){
			?><tr>
			    <td align="right"><?php print $redasterix.$yyPrNam . " " . $index?>:</td><td colspan="3"><input type="text" name="pName<?php print $index?>" size="25" value="<?php print str_replace(array('&','"'),array('&amp;','&quot;'),$pNames[$index])?>" /></td>
			  </tr><?php
				}
			} ?>
			  <tr>
			    <?php if($useStockManagement){ ?>
				<td align="right">
				<input type="hidden" name="pSell" value="<?php if((int)$pSell != 0) print "ON" ?>" />
				<select name="pStockByOpts" size="1" onchange="setstocktype();">
				<option value="0">&nbsp;&nbsp;&nbsp;<?php print $yyInStk?>:</option>
				<option value="1"<?php if((int)$pStockByOpts != 0) print 'selected="selected"' ?>><?php print $yyByOpt?>:</option></select>
				</td><td><input type="text" name="inStock" size="10" value="<?php print $pInStock?>" /></td>
				<?php }else{ ?>
				<input type="hidden" name="pStockByOpts" value="<?php if((int)$pStockByOpts != 0) print "1" ?>" />
				<td align="right"><?php print $yySellBut?>:</td><td><input type="checkbox" name="pSell" value="ON"<?php if((int)$pSell != 0) print ' checked="checked"' ?> /></td>
				<?php } ?>
				<td align="right"><?php print $yyWhoPri?> <span style="font-size:10px">(<a href="#info">info</a>)</span>:</td><td><input type="text" name="pWholesalePrice" size="15" value="<?php print $pWholesalePrice?>" /></td>
			  </tr>
			  <tr>
			    <td align="right"><?php print $yyDisPro?>:</td><td><input type="checkbox" name="pDisplay" value="ON"<?php if((int)$pDisplay != 0) print ' checked="checked"' ?> /></td>
				<td align="right"><?php print $yyListPr?> <span style="font-size:10px">(<a href="#info">info</a>)</span>:</td><td><input type="text" name="pListPrice" size="15" value="<?php if((double)$pListPrice<>0.0) print $pListPrice ?>" /></td>
			  </tr>
			  <tr>
			    <td align="right"><?php print $yyPrWght?></td>
                <td align="left"><input type="text" name="pWeight" size="9" value="<?php print $pWeight?>" /></td>
				<?php	if(($adminUnits & 12) > 0){
							$proddims = explode("x", $pDims) ?>
				<td align="right"><?php print $yyDims?>:</td>
				<td><input type="text" name="plen" size="4" value="<?php print @$proddims[0]?>" /> <strong>X</strong> 
				<input type="text" name="pwid" size="4" value="<?php print @$proddims[1]?>" /> <strong>X</strong> 
				<input type="text" name="phei" size="4" value="<?php print @$proddims[2]?>" /></td>
				<?php	}else{ ?>
			    <td align="center" colspan="2">&nbsp;</td>
				<?php	} ?>
			  </tr>
			  <tr>
                <td align="right"><span style="color:#BB0000"><?php print $yyImage?></span>:</td><td><input type="text" id="pImage" size="30" value="<?php if($numsmimgs>0) print htmlspecials($allsmimgs[0]['imageSrc']) ?>" onchange="document.getElementById('smim0').value=this.value" />&nbsp;<input type="button" id="smallimup" value="..." onclick="uploadimage('smim0')" />&nbsp;<input type="button" id="but_pImage" value="<?php print $yyOpen.' '.$yyImgMgr?>" onclick="imagemanager()" />&nbsp;<input type="button" id="moreimages" value="<?php print $yyMorImg?>" onclick="moreimagefn()" style="display:none" /></td>
<?php			if(@$digidownloads==TRUE){ ?>
                <td align="right"><?php print $yyDownl?>:</td>
                <td align="left"><input type="text" size="30" name="pDownload" value="<?php print $pDownload?>" /></td>
<?php			}else{ ?>
				<td colspan="2">&nbsp;</td>
<?php			} ?>
			  </tr>
			  <tr id="lessimages">
                <td align="right"><span style="color:#00BB00"><?php print $yyLgeImg?></span>:</td>
                <td align="left"><input type="text" id="pLargeImage" size="30" value="<?php if($numlgimgs>0) print htmlspecials($alllgimgs[0]['imageSrc']) ?>" onchange="document.getElementById('lgim0').value=this.value" /> <input type="button" value="..." onclick="uploadimage('lgim0')" /></td>
                <td align="right"><span style="color:#0000BB"><?php print $yyGiaImg?></span>:</td>
                <td align="left"><input type="text" id="pGiantImage" size="30" value="<?php if($numgtimgs>0) print htmlspecials($allgtimgs[0]['imageSrc']) ?>" onchange="document.getElementById('gtim0').value=this.value" /> <input type="button" value="..." onclick="uploadimage('gtim0')" /></td>
			  </tr>
			  <tr id="extraimages" style="display:none">
				<td align="right"><?php print $yyImgMgr?>:</td><td colspan="3">
				  <table id="extraimagetable" style="border:1px;border-color:#555;border-style:solid;padding:3px">
					<tr><td align="center" height="30"><span style="color:#BB0000"><?php print $yyImage?></span></td><td align="center"><span style="color:#00BB00"><?php print $yyLgeImg?></span></td><td align="center"><span style="color:#0000BB"><?php print $yyGiaImg?></span></td></tr>
<?php			if(! $doaddnew){
					for($imageindex=0; $imageindex<$maximagenumber; $imageindex++){
						getnext3images($smallimg,$largeimg,$giantimg);
						displayimagerow($imageindex,$smallimg,$largeimg,$giantimg);
					}
				}
				for($maximgindex=$imageindex; $maximgindex<=max(5,$imageindex+2); $maximgindex++){
					displayimagerow($maximgindex,'','','');
				}
?>
				  </table>
				  <input type="hidden" name="maximgindex" id="maximgindex" value="<?php print $maximgindex?>" />
				</td>
			  </tr>
<?php			if($usesflatrate){ ?>
			  <tr>
                <td align="right"><?php print $yyFlatShp . ':<br />' . $yyFirShi?>:</td>
                <td align="left"><input type="text" name="pShipping" size="15" value="<?php print $pShipping?>" /></td>
                <td align="right"><?php print $yyFlatShp . ':<br />' . $yySubShi?></td>
                <td align="left"><input type="text" name="pShipping2" size="15" value="<?php print $pShipping2?>" /></td>
			  </tr>
<?php			} ?>
			  <tr>
				<td align="right"><?php print $yyManuf?>:</td>
				<td align="left"><select name="pManufacturer" size="1">
				  <option value="0"><?php print $yyNone?></option><?php
					$gotmanufacturer=FALSE;
					for($index=0;$index<$nallmanufacturer;$index++){
						print "<option value='" . $allmanufacturer[$index]['mfID'] . "'";
						if($allmanufacturer[$index]['mfID']==$pManufacturer){ print ' selected="selected"'; $gotmanufacturer=TRUE; }
						print '>' . $allmanufacturer[$index]['mfName'] . "</option>\n";
					}
					if($pManufacturer!=0 && ! $gotmanufacturer) print '<option value="0" selected="selected">** DELETED **</option>'; ?>
				  </select>
<?php				$sSQL = "SELECT scID,scWorkingName FROM searchcriteria ORDER BY scGroup,scOrder";
					$result = mysql_query($sSQL) or print(mysql_error());
					if(mysql_num_rows($result)>0){
						print ' / <select name="pSearchCriteria" size="1">';
						print '<option value="0">'.$yyNone.'</option>';
						while($rs = mysql_fetch_assoc($result)){
							print '<option value="'.$rs['scID'].'"';
							if($rs['scID']==$pSearchCriteria) print ' selected="selected"';
							print '>'.$rs['scWorkingName']."</option>\r\n";
						}
						print '</select>';
					} ?>
				  </td>
				<td align="right"><?php print $yyDrSppr?>:</td>
				<td align="left"><select name="pDropship" size="1">
				  <option value="0"><?php print $yyNone?></option><?php
						for($index=0;$index<$nalldropship;$index++){
							print "<option value='" . $alldropship[$index]['dsID'] . "'";
							if($alldropship[$index]['dsID']==$pDropship) print ' selected="selected"';
							print '>' . $alldropship[$index]['dsName'] . "</option>\n";
						} ?>
				  </select></td>
			  </tr>
			  <tr id="numoptionsrow">
		<?php	if($simpleOptions){ ?>
				<td colspan="2">&nbsp;</td>
		<?php	}else{ ?>
                <td align="right"><?php print $yyNumOpt?>:</td>
                <td>
				  <select size="1" name="pNumOptions" onchange="setprodoptions();">
					<option value='0'><?php print $yyNone?></option>
					<?php	for($rowcounter=1; $rowcounter <= maxprodopts; $rowcounter++)
								print "<option value='" . $rowcounter . "'>" . $rowcounter . "</option>"; ?>
				  </select></td>
		<?php	}
				$themask = 'yyyy-mm-dd';
				if($admindateformat==1)
					$themask='mm/dd/yyyy';
				elseif($admindateformat==2)
					$themask='dd/mm/yyyy'; ?>
				<td align="right"><?php print $yyDateAd?>:</td>
				<td align="left"><input type="text" size="14" name="pDateAdded" value="<?php if($pDateAdded!='') print $pDateAdded?>" /> <input type="button" onclick="popUpCalendar(this, document.forms.mainform.pDateAdded, '<?php print $themask?>', -200)" value='DP' /></td>
			  </tr>
<?php	if($simpleOptions){
			for($index=0;$index < maxprodopts; $index++){
				if(($index % 2)==0) print '<tr>';
				print '<td align="right">' . $yyPrdOpt . ' ' . ($index+1) . ':</td><td><select size="1" id="pOption' . $index . '" name="pOption' . $index . '"><option value="0">None</option>';
				for($rowcounter=0;$rowcounter < $nalloptions;$rowcounter++){
					print '<option value="' . $alloptions[$rowcounter][0] . '"';
					if($index < $nprodoptions){
						if($prodoptions[$index][1]==$alloptions[$rowcounter][0]) print ' selected="selected"';
					}
					print '>' . $alloptions[$rowcounter][1] . '</option>';
				}
				print '</td>';
				if(($index % 2) != 0) print "</tr>\n";
			}
			if(($index % 2)==0)
				print "</tr>\n";
			else
				print '<td colspan="2">&nbsp;</td></tr>';
		} ?>
			  <tr> 
                <td align="right"><?php print $yyDesc?>:</td>
                <td colspan="2"><textarea name="pDescription" cols="55" rows="8"><?php print str_replace('&','&amp;',$pDescription)?></textarea></td>
				<td align="center">
				<?php print $yyExemp?> <span style="font-size:10px">&lt;Ctrl>+Click</span><br />
					<select name="pExemptions[]" size="4" multiple="multiple">
					<option value="1"<?php if(($pExemptions&1)==1) print ' selected="selected"'?>><?php print $yyExStat?></option>
					<option value="2"<?php if(($pExemptions&2)==2) print ' selected="selected"'?>><?php print $yyExCoun?></option>
					<option value="4"<?php if(($pExemptions&4)==4) print ' selected="selected"'?>><?php print $yyExShip?></option>
					<option value="8"<?php if(($pExemptions&8)==8) print ' selected="selected"'?>><?php print $yyExHand?></option>
					</select><br /><img src="adminimages/clearpixel.gif" width="20" height="3" alt="" />
<?php			if(@$perproducttaxrate==TRUE){ ?>
					<br /><?php print $yyTax?>: <input type="text" style="text-align:right" size="6" name="pTax" value="<?php print $pTax?>" />%
<?php			} ?>
				</td>
			  </tr>
<?php	for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 2)==2){ ?>
			  <tr>
				<td align="right"><?php print $yyDesc . " " . $index?>:</td>
                <td colspan="3"><textarea name="pDescription<?php print $index?>" cols="55" rows="8"><?php print str_replace('&','&amp;',$pDescriptions[$index])?></textarea></td>
			  </tr>
<?php		}
		} ?>
			  <tr>
                <td align="right"><?php print $yyLnDesc?>:</td>
                <td colspan="3" align="left"><textarea name="pLongDescription" cols="65" rows="9"><?php print str_replace('&','&amp;',$pLongDescription)?></textarea></td>
			  </tr>
<?php	for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 4)==4){ ?>
			  <tr>
				<td align="right"><?php print $yyLnDesc . " " . $index?>:</td>
                <td colspan="3"><textarea name="pLongDescription<?php print $index?>" cols="65" rows="9"><?php print str_replace('&','&amp;',$pLongDescriptions[$index])?></textarea></td>
			  </tr>
<?php		}
		} ?>
			  <tr>
				<td align="right"><?php print $yyStatPg?>:</td>
                <td><input type="checkbox" name="pStaticPage" value="1"<?php if((int)$pStaticPage != 0) print ' checked="checked"' ?> /></td>
				<td align="right"><?php print $yyRecomd?>:</td>
                <td><input type="checkbox" name="pRecommend" value="1"<?php if((int)$pRecommend != 0) print ' checked="checked"' ?> /></td>
			  </tr>
			  <tr>
				<td align="right">SKU:</td>
                <td align="left"><input type="text" name="pSKU" size="30" value="<?php print htmlspecials($pSKU)?>" /></td>
				<td align="right"><?php print $yyProdOr?>:</td>
                <td align="left"><input type="text" name="pOrder" size="10" value="<?php print $pOrder?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><?php print $yyAddSrP?>:</td>
                <td align="left" colspan="3"><input type="text" name="pSearchParams" size="60" value="<?php print htmlspecials($pSearchParams)?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyAddSec?>:</strong></td>
                <td colspan="3" align="left">
<?php		if(! $simpleSections){
				print '<select size="1" name="pNumSections" onchange="setprodsections();"><option value="0">' . $yyNone . '</option>';
				for($rowcounter=1;$rowcounter <= $maxprodsects; $rowcounter++)
					print "<option value='" . $rowcounter . "'>" . $rowcounter . "</option>";
				print "</select>";
			} ?>&nbsp;</td>
			  </tr>
<?php	if($simpleSections){
			for($index=0;$index < $maxprodsects; $index++){
				if(($index % 2)==0) print "<tr>";
				print '<td align="right">' . $yyPrdSec . ' ' . ($index+1) . ':</td><td><select size="1" id="pSection' . $index . '" name="pSection' . $index . '"><option value="0">' . $yyNone . '</option>';
				for($rowcounter=0;$rowcounter < $nallsections;$rowcounter++){
					print '<option value="' . $allsections[$rowcounter]["sectionID"] . '"';
					if($index < $nprodsections){
						if($prodsections[$index][0]==$allsections[$rowcounter]["sectionID"]) print ' selected="selected"';
					}
					print ">" . $allsections[$rowcounter]["sectionWorkingName"] . "</option>";
				}
				print "</td>";
				if(($index % 2) != 0) print "</tr>\n";
			}
			if(($index % 2)==0)
				print "</tr>\n";
			else
				print "<td colspan=\"2\">&nbsp;</td></tr>\n";
		}else{ ?>
			</table>
			<div id="prodsections">
			</div>
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
<?php	} ?>
			  <tr> 
                <td width="100%" colspan="4">
                  <p align="center"><input type="submit" value="<?php print $yySubmit?>" />&nbsp;&nbsp;<input type="reset" value="<?php print $yyReset?>" /></p>
<?php	show_info() ?>
                </td>
			  </tr>
            </table>
	</form>
<?php
	if(@$htmleditor=='fckeditor'){
		if(@$pathtossl != '' && (@$_SERVER['HTTPS'] == 'on' || @$_SERVER['SERVER_PORT'] == '443')){
			if(substr($pathtossl,-1) != "/") $storeurl = $pathtossl . "/"; else $storeurl = $pathtossl;
		}
		print '<script type="text/javascript">';
		$streditor = "var oFCKeditor = new FCKeditor('pDescription');oFCKeditor.BasePath=sBasePath;oFCKeditor.Config.BaseHref='".$storeurl."';oFCKeditor.ToolbarSet = 'Basic';oFCKeditor.ReplaceTextarea();\r\n";
		print $streditor;
		for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 2)==2) print str_replace('pDescription', 'pDescription' . $index, $streditor);
		}
		print str_replace('pDescription', 'pLongDescription', $streditor);
		for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 4)==4) print str_replace('pDescription', 'pLongDescription' . $index, $streditor);
		}
		print '</script>';
	}
		if(! $doaddnew){ ?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
<?php	if(! $simpleOptions){ ?>
document.forms.mainform.pNumOptions.selectedIndex=<?php print $nprodoptions ?>;
document.forms.mainform.pNumOptions.options[<?php print $nprodoptions ?>].selected = true;
setprodoptions();
<?php	}
		if(! $simpleSections){ ?>
document.forms.mainform.pNumSections.selectedIndex=<?php print $nprodsections ?>;
document.forms.mainform.pNumSections.options[<?php print $nprodsections ?>].selected = true;
setprodsections();
<?php	}
		if($useStockManagement){ ?>
setstocktype();
<?php	} ?>
/* ]]> */
</script>
<?php	}
}elseif(@$_POST['act']=='discounts'){
		$sSQL = "SELECT pName FROM products WHERE pID='" . escape_string(unstripslashes(@$_POST['id'])) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		$rs = mysql_fetch_assoc($result);
		$thisname=$rs['pName'];
		mysql_free_result($result);
		$numassigns=0;
		$sSQL = "SELECT cpaID,cpaCpnID,cpnWorkingName,cpnSitewide,cpnEndDate,cpnType FROM cpnassign LEFT JOIN coupons ON cpnassign.cpaCpnID=coupons.cpnID WHERE cpaType=2 AND cpaAssignment='" . @$_POST["id"] . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs=mysql_fetch_assoc($result))
			$alldata[$numassigns++]=$rs;
		mysql_free_result($result);
		$numcoupons=0;
		$sSQL = "SELECT cpnID,cpnWorkingName,cpnSitewide FROM coupons WHERE cpnSitewide=0 AND cpnEndDate >='" . date("Y-m-d",time()) ."'";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs=mysql_fetch_assoc($result))
			$alldata2[$numcoupons++]=$rs;
		mysql_free_result($result);
?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function drk(id){
cmsg = "<?php print $yyConAss?>\n"
if (confirm(cmsg)){
	document.mainform.id.value = id;
	document.mainform.act.value = "deletedisc";
	document.mainform.submit();
}
}
/* ]]> */
</script>
        <tr>
		<form name="mainform" method="post" action="adminprods.php">
		  <td width="100%">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="dodiscounts" />
			<input type="hidden" name="id" value="<?php print @$_POST["id"]?>" />
<?php				writehiddenvar('disp', @$_POST['disp']);
					writehiddenvar('stext', @$_POST['stext']);
					writehiddenvar('sprice', @$_POST['sprice']);
					writehiddenvar('scat', @$_POST['scat']);
					writehiddenvar('stype', @$_POST['stype']);
					writehiddenvar('pg', @$_POST['pg']); ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="4" align="center"><strong><?php print $yyAssPrd?> &quot;<?php print $thisname?>&quot;.</strong><br />&nbsp;</td>
			  </tr>
<?php
	$gotone=FALSE;
	if($numcoupons>0){
		$thestr = '<tr><td colspan="4" align="center">' . $yyAsDsCp . ': <select name="assdisc" size="1">';
		for($index=0;$index < $numcoupons;$index++){
			$alreadyassign=FALSE;
			if($numassigns>0){
				for($index2=0;$index2<$numassigns;$index2++){
					if($alldata2[$index]["cpnID"]==$alldata[$index2]["cpaCpnID"]) $alreadyassign=TRUE;
				}
			}
			if(! $alreadyassign){
				$thestr .= "<option value='" . $alldata2[$index]["cpnID"] . "'>" . $alldata2[$index]["cpnWorkingName"] . "</option>\n";
				$gotone=TRUE;
			}
		}
		$thestr .= "</select> <input type='submit' value='Go' /></td></tr>";
	}
	if($gotone){
		print $thestr;
	}else{
?>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br /><strong><?php print $yyNoDis?></strong></td>
			  </tr>
<?php
	}
	if($numassigns>0){
?>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br /><strong><?php print $yyCurDis?> &quot;<?php print $thisname?>&quot;.</strong><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td><strong><?php print $yyWrkNam?></strong></td>
				<td><strong><?php print $yyDisTyp?></strong></td>
				<td><strong><?php print $yyExpire?></strong></td>
				<td align="center"><strong><?php print $yyDelete?></strong></td>
			  </tr>
<?php
		for($index=0;$index<$numassigns;$index++){
			$prefont = "";
			$postfont = "";
			if((int)$alldata[$index]["cpnSitewide"]==1 || ($alldata[$index]["cpnEndDate"] != '3000-01-01 00:00:00' && strtotime($alldata[$index]["cpnEndDate"])-time() < 0)){
				$prefont = '<span style="color:#FF0000">';
				$postfont = '</span>';
			}
?>
			  <tr> 
                <td><?php	print $prefont . $alldata[$index]["cpnWorkingName"] . $postfont ?></td>
				<td><?php	if($alldata[$index]["cpnType"]==0)
								print $prefont . $yyFrSShp . $postfont;
							elseif($alldata[$index]["cpnType"]==1)
								print $prefont . $yyFlatDs . $postfont;
							elseif($alldata[$index]["cpnType"]==2)
								print $prefont . $yyPerDis . $postfont; ?></td>
				<td><?php	if($alldata[$index]["cpnEndDate"] == '3000-01-01 00:00:00')
								print $yyNever;
							elseif(strtotime($alldata[$index]["cpnEndDate"])-time() < 0)
								print '<span style="color:#FF0000">' . $yyExpird . '</span>';
							else
								print $prefont . date("Y-m-d",strtotime($alldata[$index]["cpnEndDate"])) . $postfont?></td>
				<td align="center"><input type="button" name="discount" value="Delete Assignment" onclick="drk('<?php print $alldata[$index]["cpaID"]?>')" /></td>
			  </tr>
<?php
		}
	}else{
?>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br /><strong><?php print $yyNoAss?></strong></td>
			  </tr>
<?php
	}
?>
			  <tr>
                <td width="100%" colspan="4" align="center"><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table></td>
		  </form>
        </tr>
<?php
}elseif(@$_POST["posted"]=="1" && @$_POST['act']!='sort' && $success){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="adminprods.php<?php
							print '?rid=' . @$_POST['rid'] . '&disp=' . @$_POST['disp'] . '&stext=' . urlencode(@$_POST['stext']) . '&sprice=' . urlencode(@$_POST['sprice']) . '&stype=' . @$_POST['stype'] . '&scat=' . @$_POST['scat'] . '&pg=' . @$_POST['pg'];
						?>"><strong>click here</strong></a>.<br />
                        <br />&nbsp;<br />&nbsp;
                </td>
			  </tr>
			</table>
		  </td>
        </tr>
      </table>
<?php
}elseif(@$_POST["posted"]=="1" && @$_POST['act']!='sort'){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table>
		  </td>
        </tr>
      </table>
<?php
}elseif(@$_GET['act']=='stknot'){ ?>
	<form method="post" action="adminprods.php">
	<input type="hidden" name="posted" value="1" />
	<input type="hidden" name="act" value="allstk" />
      <table border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
          <td align="center">
			<table class="admin-table-b" border="0" cellspacing="3" cellpadding="3">
			<thead>
			  <tr> 
                <th scope="col" style="white-space:nowrap">&nbsp;<?php print $yyPrId?>&nbsp;</th>
				<th scope="col" style="white-space:nowrap">&nbsp;<?php print $yyPrName?>&nbsp;</th>
				<th scope="col" style="white-space:nowrap">&nbsp;<?php print $yyPOName?>&nbsp;</th>
				<th scope="col" style="white-space:nowrap">&nbsp;<?php print $yyQuant?>&nbsp;</th>
				<th scope="col" style="white-space:nowrap">&nbsp;<?php print $yyDelete?>&nbsp;</th>
			  </tr>
			</thead>
<?php	if(@$_GET['pid']!='' && @$_GET['oid']!=''){
			$sSQL="DELETE FROM notifyinstock WHERE nsProdID='".escape_string(@$_GET['pid'])."' AND nsOptID=".@$_GET['oid'];
			mysql_query($sSQL) or print(mysql_error());
		}
		$sSQL="SELECT nsProdID,nsTriggerProdID,pName,nsOptID,COUNT(*) AS tcnt FROM notifyinstock LEFT JOIN products on notifyinstock.nsProdID=products.pID GROUP BY nsProdID,nsTriggerProdID,pName,nsOptID ORDER BY tcnt DESC";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs=mysql_fetch_assoc($result)){
			$optname='';
			if($rs['nsOptID']!=0){
				$sSQL="SELECT optName FROM options WHERE optID=".$rs['nsOptID'];
				$result2 = mysql_query($sSQL) or print(mysql_error());
				if($rs2=mysql_fetch_assoc($result2)) $optname=$rs2['optName'];
				mysql_free_result($result2);
			}
			$pname=trim($rs['pName']);
			if($pname=='') $pname='**DELETED**';
			$prodid=trim($rs['nsProdID']);
			if(trim($rs['nsTriggerProdID'])!=$prodid) $prodid .= ' / ' . $rs['nsTriggerProdID'];
			print '<tr><td style="white-space:nowrap">'.$prodid.'</td><td style="white-space:nowrap">'.$pname.'</td><td style="white-space:nowrap">'.($optname!=''?$optname:'-').'</td><td style="white-space:nowrap">'.$rs['tcnt'].'</td><td style=""white-space:nowrap""><input type="button" value="'.$yyDelete.'" onclick="document.location=\'adminprods.php?act=stknot&pid='.$rs['nsProdID'].'&oid='.$rs['nsOptID'].'\'" /></td></tr>';
		}
		mysql_free_result($result);
?>			  <tr> 
                <td colspan="5" align="center">&nbsp;<br /><input type="submit" value="Send All Stock Notifications" /> <input type="button" onclick="document.location='adminprods.php'" value="<?php print $yyClkBac?>" /></td>
			  </tr>
			</table></td>
        </tr>
      </table>
	</form>
<?php
}else{
	if(@$_GET['pract']!='') $pract = $_GET['pract']; else $pract = @$_COOKIE['pract'];
	if(@$_POST['sort']!='') $sortorder = $_POST['sort']; else $sortorder = @$_COOKIE['psort'];
	if(@$_GET['catorman']!='') $catorman = $_GET['catorman']; else $catorman = @$_COOKIE['pcatorman']; ?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function mrk(id,evt){
	document.mainform.action="adminprods.php";
	document.mainform.id.value = id;
	<?php if(strstr(@$_SERVER['HTTP_USER_AGENT'], 'Gecko')){ ?>
	if(evt.ctrlKey || evt.altKey)
	<?php }else{ ?>
	theevnt=window.event;
	if(theevnt.ctrlKey)
	<?php } ?>
		document.mainform.act.value = "clone";
	else
		document.mainform.act.value = "modify";
	document.mainform.submit();
}
function rel(id){
	document.mainform.action="adminprods.php?related=go";
	document.mainform.rid.value = id;
	document.mainform.act.value = "search";
	document.mainform.posted.value = "";
	document.mainform.submit();
}
function updaterelations(){
	document.mainform.action="adminprods.php";
	document.mainform.act.value = "updaterelations";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function newrec(id){
	document.mainform.action="adminprods.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.submit();
}
function quickupdate(){
	if(document.mainform.pract.value=="del"){
		if(!confirm("<?php print $yyConDel?>\n"))
			return;
	}
	document.mainform.action="adminprods.php";
	document.mainform.act.value = "quickupdate";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function dsc(id){
	document.mainform.action="adminprods.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "discounts";
	document.mainform.submit();
}
function startsearch(){
	document.mainform.action="adminprods.php";
	document.mainform.act.value = "search";
	document.mainform.posted.value = "";
	document.mainform.submit();
}
function inventorymenu(){
	themenuitem=document.mainform.inventoryselect.options[document.mainform.inventoryselect.selectedIndex].value;
	if(themenuitem=="1") document.mainform.act.value = "stockinventory";
	if(themenuitem=="2") document.mainform.act.value = "fullinventory";
	if(themenuitem=="3") document.mainform.act.value = "dump2COinventory";
	if(themenuitem=="4") document.mainform.act.value = "productimages";
	document.mainform.action="dumporders.php";
	document.mainform.submit();
}
function drk(id){
cmsg = "<?php print $yyConDel?>\n"
if (confirm(cmsg)){
	document.mainform.action="adminprods.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.submit();
}
}
function changepract(obj){
	document.location = "adminprods.php?pract="+obj[obj.selectedIndex].value+"&rid=<?php print @$_REQUEST['rid']?>&disp=<?php print @$_REQUEST['disp']?>&stext=<?php print urlencode(@$_REQUEST['stext'])?>&sprice=<?php print urlencode(@$_REQUEST['sprice'])?>&stype=<?php print @$_REQUEST['stype']?>&scat=<?php print @$_REQUEST['scat']?>&pg=<?php print (@$_GET['pg']=='' ? 1 : $_GET['pg'])?>";
}
function switchcatorman(obj){
	document.location = "adminprods.php?catorman="+obj[obj.selectedIndex].value+"&rid=<?php print @$_REQUEST['rid']?>&disp=<?php print @$_REQUEST['disp']?>&stext=<?php print urlencode(@$_REQUEST['stext'])?>&sprice=<?php print urlencode(@$_REQUEST['sprice'])?>&stype=<?php print @$_REQUEST['stype']?>&pg=<?php print (@$_GET['pg']=='' && @$_POST['act']=='search' ? 1 : @$_GET['pg'])?>";
}
function changesortorder(men){
	document.mainform.action="adminprods.php<?php if(@$_POST['act']=='search' || @$_GET['pg']!='') print '?pg=1'?>";
	document.mainform.id.value = men.options[men.selectedIndex].value;
	document.mainform.act.value = "sort";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function addto(){
	maxitems=document.getElementById("resultcounter").value;
	amnt=document.getElementById("txtadd").value;
	if(amnt.indexOf("%") > 0) ispercent=true; else ispercent=false;
	amnt.replace(/%/g, "");
	amnt=parseFloat(amnt);
	if(! isNaN(amnt)){
		for(index=0;index<maxitems;index++){
			if(document.getElementById("chkbx"+index)){
				theval = parseFloat(document.getElementById("chkbx"+index).value);
				if(! isNaN(theval)){
					if(ispercent)
						document.getElementById("chkbx"+index).value=theval+((amnt*theval)/100.0);
					else
						document.getElementById("chkbx"+index).value=theval+amnt;
				}
			}
		}
	}
}
function checkboxes(docheck){
	maxitems=document.getElementById("resultcounter").value;
	for(index=0;index<maxitems;index++){
		document.getElementById("chkbx"+index).checked=docheck;
	}
}
/* ]]> */
</script>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
<?php
	$rid = trim(@$_REQUEST['rid']);
	$ridarr = '';
	$numrid = 0;
	if($rid != ''){
		$sSQL = "SELECT rpRelProdID FROM relatedprods WHERE rpProdID='" . escape_string($rid) . "'";
		if(@$relatedproductsbothways==TRUE) $sSQL .= "UNION SELECT rpProdID FROM relatedprods WHERE rpRelProdID='" . escape_string($rid) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs=mysql_fetch_array($result))
			$ridarr[$numrid++]=$rs;
		mysql_free_result($result);
	}
	if(@$_POST['disp']!=''){
		setcookie('pdisp', @$_POST['disp'], time()+31536000, '/', '', @$_SERVER['HTTPS']=='on');
	}
	if(@$_REQUEST['disp']!='') $productdisplay=$_REQUEST['disp']; else $productdisplay=@$_COOKIE['pdisp'];
	if(@$_GET['related']=='go') $_SESSION['savesearch']='disp=' . @$_POST['disp'] . '&stext=' . urlencode(@$_POST['stext']) . '&sprice=' . urlencode(@$_POST['sprice']) . '&stype=' . @$_POST['stype'] . '&scat=' . @$_POST['scat'] . '&pg=' . @$_POST['pg'];
?>
        <tr>
		  <td width="100%">
		  <form name="mainform" method="post" action="adminprods.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="rid" value="<?php print $rid?>" />
			<input type="hidden" name="pg" value="<?php print (@$_POST['act']=='search' ? '1' : @$_GET['pg']) ?>" />
<?php
	$numcats=0;
	$scat=unstripslashes(@$_REQUEST["scat"]);
	$stext=unstripslashes(@$_REQUEST["stext"]);
	$stype=unstripslashes(@$_REQUEST["stype"]);
	$sprice=unstripslashes(@$_REQUEST["sprice"]);
	if(! @is_numeric($_GET["pg"]))
		$CurPage = 1;
	else
		$CurPage = (int)($_GET["pg"]);
	$thecat = @$_REQUEST['scat'];
	if($thecat != '') $thecat = (int)$thecat;
	$sSQL = "SELECT payProvEnabled,payProvData1 FROM payprovider WHERE payProvID=2";
	$result = mysql_query($sSQL) or print(mysql_error());
	$rs = mysql_fetch_assoc($result);
	if($rs["payProvEnabled"]==1 AND trim($rs["payProvData1"]) != "") $twocoinventory=TRUE; else $twocoinventory=FALSE;
	mysql_free_result($result);
?>			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php		if($rid != ''){ ?>
				  <tr><td class="cobhl" align="center" colspan="4" height="22"><strong> Products related to <?php print $rid ?></strong> </td></tr>
<?php		} ?>
			  <tr> 
                <td class="cobhl" width="25%" align="right"><?php print $yySrchFr?>:</td>
				<td class="cobll" width="25%"><input type="text" name="stext" size="20" value="<?php print str_replace("\"","&quot;",$stext)?>" /></td>
				<td class="cobhl" width="25%" align="right"><?php print $yySrchMx?>:</td>
				<td class="cobll" width="25%"><input type="text" name="sprice" size="10" value="<?php print $sprice?>" /></td>
			  </tr>
			  <tr>
			    <td class="cobhl" width="25%" align="right"><?php print $yySrchTp?>:</td>
				<td class="cobll" width="25%"><select name="stype" size="1">
					<option value=""><?php print $yySrchAl?></option>
					<option value="any"<?php if($stype=="any") print ' selected="selected"'?>><?php print $yySrchAn?></option>
					<option value="exact"<?php if($stype=="exact") print ' selected="selected"'?>><?php print $yySrchEx?></option>
					</select>
				</td>
				<td class="cobhl" width="25%" align="right"><select size="1" name="catorman" onchange="switchcatorman(this)">
						<option value="cat"><?php print $yySrchCt?></option>
						<option value="man"<?php if($catorman=='man') print ' selected="selected"'?>><?php print $yyManuf?></option>
						<option value="non"<?php if($catorman=='non') print ' selected="selected"'?>><?php print $yyNone?></option>
						</select></td>
				<td class="cobll" width="25%">
<?php	if($catorman=='non')
			print '&nbsp;';
		else{ ?>
				  <select name="scat" size="1">
				  <option value=""><?php print ($catorman=='man'?$yyManDes:$yySrchAC)?></option>
<?php
		$lasttsid = -1;
		if($catorman=='man'){
			$adminonlysubcats=TRUE;
			$sSQL = 'SELECT mfID,mfName,0,0 FROM manufacturer ORDER BY mfName';
			$allcats = mysql_query($sSQL) or print(mysql_error());
			while($row = mysql_fetch_row($allcats)){
				$allcatsa[$numcats++]=$row;
			}
			mysql_free_result($allcats);
		}elseif(@$noadmincategorysearch!=TRUE){
			$sSQL = "SELECT sectionID,sectionWorkingName,topSection,rootSection FROM sections " . (@$adminonlysubcats==TRUE ? "WHERE rootSection=1 ORDER BY sectionWorkingName" : "ORDER BY sectionOrder");
			$allcats = mysql_query($sSQL) or print(mysql_error());
			while($row = mysql_fetch_row($allcats)){
				$allcatsa[$numcats++]=$row;
			}
			mysql_free_result($allcats);
		}
		if($numcats > 0){
			if(@$adminonlysubcats==TRUE){
				for($index=0;$index<$numcats;$index++){
					print '<option value="' . $allcatsa[$index][0] . '"';
					if($allcatsa[$index][0]==$thecat) print ' selected="selected"';
					print '>' . htmlspecials($allcatsa[$index][1]) . "</option>\n";
				}
			}else
				writemenulevel(0,1);
		}
?>
				  </select>
<?php	} ?>
				</td>
              </tr>
			  <tr>
				    <td class="cobhl" align="center"><?php
					if(@$_POST['act']=='search' || @$_GET['pg']!=''){
						if($pract=='del' || $pract=='dip' || $pract=='stp' || $pract=='rec'){ ?>
						<input type="button" value="Check All" onclick="checkboxes(true);" /> <input type="button" value="Uncheck All" onclick="checkboxes(false);" />
<?php					}elseif($pract=='pri' || $pract=='wpr' || $pract=='lpr' || $pract=='stk' || $pract=='prw' || $pract=='pro'){ ?>
						<input type="text" name="txtadd" id="txtadd" size="5" value="0" /> <input type="button" value="Add" onclick="addto()" />
<?php					}
					}else
						print '&nbsp;' ?></td>
				    <td class="cobll" colspan="3"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					    <tr>
						  <td class="cobll" align="center" style="white-space: nowrap">
							<select name="sort" size="1" onchange="changesortorder(this)">
							<option value="ida"<?php if($sortorder=='ida') print ' selected="selected"'?>>Sort - ID ASC</option>
							<option value="idd"<?php if($sortorder=='idd') print ' selected="selected"'?>>Sort - ID DESC</option>
							<option value=""<?php if($sortorder=='') print ' selected="selected"'?>>Sort - Name ASC</option>
							<option value="nad"<?php if($sortorder=='nad') print ' selected="selected"'?>>Sort - Name DESC</option>
							<option value="pra"<?php if($sortorder=='pra') print ' selected="selected"'?>>Sort - Price ASC</option>
							<option value="prd"<?php if($sortorder=='prd') print ' selected="selected"'?>>Sort - Price DESC</option>
							<option value="daa"<?php if($sortorder=='daa') print ' selected="selected"'?>>Sort - Date ASC</option>
							<option value="dad"<?php if($sortorder=='dad') print ' selected="selected"'?>>Sort - Date DESC</option>
							<option value="poa"<?php if($sortorder=='poa') print ' selected="selected"'?>>Sort - pOrder ASC</option>
							<option value="pod"<?php if($sortorder=='pod') print ' selected="selected"'?>>Sort - pOrder DESC</option>
<?php				if($useStockManagement) print '<option value="sta"'.($sortorder=='sta'?' selected="selected"':'').'>Sort - Stock ASC</option><option value="std"'.($sortorder=='std'?' selected="selected"':'').'>Sort - Stock DESC</option>' ?>
							<option value="nsf"<?php if($sortorder=='nsf') print ' selected="selected"'?>>No Sort (Fastest)</option>
							</select>
							<select name="disp" size="1">
							<option value="5">Visible Prods</option>
							<option value="1"<?php if($productdisplay=='1') print ' selected="selected"'?>>All Prods</option>
							<option value="2"<?php if($productdisplay=='2') print ' selected="selected"'?>>Hidden Prods</option>
<?php				if($useStockManagement) print '<option value="3"'.($productdisplay=='3' ? ' selected="selected"' : '').'>'.$yyOOStoc.'</option>' ?>
							<option value="4"<?php if($productdisplay=='4') print ' selected="selected"'?>>Orphan Prods</option>
							</select>
							<input type="submit" value="<?php print $yyListPd?>" onclick="startsearch();" />
<?php				if($rid != ''){ ?>
							<strong>&raquo;</strong> <input type="button" value="<?php print $yyBckLis?>" onclick="document.location='adminprods.php?<?php print @$_SESSION['savesearch']?>'">
<?php				}else{ ?>
							<input type="button" value="<?php print $yyNewPr?>" onclick="newrec();" />
<?php					if(@$notifybackinstock){
							$sSQL="SELECT COUNT(*) AS tcnt FROM notifyinstock";
							$result = mysql_query($sSQL) or print(mysql_error());
							if($rs = mysql_fetch_assoc($result)){
								if($rs['tcnt']>0) print '<input type="button" value="'.$yyStkNot.' ('.$rs['tcnt'].')'.'" onclick="document.location=\'adminprods.php?act=stknot\'" />';
							}
							mysql_free_result($result);
						}
					} ?>
						  </td>
						  <td class="cobll" height="26" width="20%" align="right" style="white-space: nowrap">
<?php				if($rid != ''){ ?>
							<input type="button" value="<?php print $yyUpdRel?>" onclick="updaterelations()">
<?php				}else{ ?>
						<select name="inventoryselect" size="1">
						  <?php if($stockManage != 0) print '<option value="1">' . $yyStkInv . '</option>'; ?>
							<option value="2"><?php print $yyFulInv?></option>
							<?php if($twocoinventory) print '<option value="3">2Checkout Inventory</option>' ?>
							<option value="4">Product Images</option>
						  </select>&nbsp;<input type="button" value="Go" onclick="javascript:inventorymenu();" />
<?php				} ?></td>
						</tr>
					  </table></td>
				  </tr>
			</table>
            <table width="100%" border="0" cellspacing="0" cellpadding="1">
<?php
	if(@$_POST['act']=='search' || @$_GET['pg'] != ''){
		function patch_pid($id){
			return str_replace('.','ect_dot_xzq',$id);
		}
		function displayprodrow($xrs,$rownum){
			global $bgcolor,$stockManage,$yyAssign,$yyModify,$yyRelate,$yyDelete,$numcoupons,$allcoupon,$rid,$numrid,$ridarr,$resultcounter,$useStockManagement,$stockbyoptions,$resultcounter,$pract,$redasterix;
			$stockbyoptions=false;
			if($stockManage != 0){
				if($xrs['pStockByOpts'] != 0) $stockbyoptions=true;
			} ?><tr<?php print $bgcolor?>><td align="center"><?php
				if($pract=='prn')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="18" name="pra_'.patch_pid($xrs['pID']).'" value="' . htmlspecials($xrs['pName']) . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='prn2')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="18" name="pra_'.patch_pid($xrs['pID']).'" value="' . htmlspecials($xrs['pName2']) . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='prn3')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="18" name="pra_'.patch_pid($xrs['pID']).'" value="' . htmlspecials($xrs['pName3']) . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='pri')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="5" name="pra_'.patch_pid($xrs['pID']).'" value="' . $xrs['pPrice'] . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='wpr')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="5" name="pra_'.patch_pid($xrs['pID']).'" value="' . $xrs['pWholesalePrice'] . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='lpr')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="5" name="pra_'.patch_pid($xrs['pID']).'" value="' . $xrs['pListPrice'] . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='stk'){
					if($stockbyoptions) print '-'; else print '<input type="text" id="chkbx'.$resultcounter.'" size="5" name="pra_'.patch_pid($xrs['pID']).'" value="' . $xrs['pInStock'] . '" tabindex="'.($resultcounter+1).'"/>';
				}elseif($pract=='sta'){
					if($stockbyoptions) print '-'; else print '<input type="text" id="chkbx'.$resultcounter.'" size="5" name="pra_'.patch_pid($xrs['pID']).'" value="" tabindex="'.($resultcounter+1).'"/>';
				}elseif($pract=='del')
					print '<input type="checkbox" id="chkbx'.$resultcounter.'" name="pra_'.patch_pid($xrs['pID']).'" value="del" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='prw')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="5" name="pra_'.patch_pid($xrs['pID']).'" value="' . $xrs['pWeight'] . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='dip')
					print '<input type="hidden" name="pra_'.patch_pid($xrs['pID']).'" value="1" /><input type="checkbox" id="chkbx'.$resultcounter.'" name="prb_'.patch_pid($xrs['pID']).'" value="1" ' . ($xrs['pDisplay']!=0 ? 'checked="checked"' : '') . 'tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='stp')
					print '<input type="hidden" name="pra_'.patch_pid($xrs['pID']).'" value="1" /><input type="checkbox" id="chkbx'.$resultcounter.'" name="prb_'.patch_pid($xrs['pID']).'" value="1" ' . ($xrs['pStaticPage']!=0 ? 'checked="checked"' : '') . 'tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='rec')
					print '<input type="hidden" name="pra_'.patch_pid($xrs['pID']).'" value="1" /><input type="checkbox" id="chkbx'.$resultcounter.'" name="prb_'.patch_pid($xrs['pID']).'" value="1" ' . ($xrs['pRecommend']!=0 ? 'checked="checked"' : '') . 'tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='sku')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="10" name="pra_'.patch_pid($xrs['pID']).'" value="' . htmlspecials($xrs['pSKU']) . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='pro')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="5" name="pra_'.patch_pid($xrs['pID']).'" value="' . $xrs['pOrder'] . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='sel')
					print '<input type="hidden" name="pra_'.patch_pid($xrs['pID']).'" value="1" /><input type="checkbox" id="chkbx'.$resultcounter.'" name="prb_'.patch_pid($xrs['pID']).'" value="1" ' . ($xrs['pSell']!=0 ? 'checked="checked"' : '') . 'tabindex="'.($resultcounter+1).'"/>';
				else
					print '&nbsp;';
			?></td><td><?php print htmlspecials($xrs['pID'])?></td><td><?php
				if(@$noautocheckorphans==TRUE && @$_REQUEST['disp']!='4'){
					// nothing
				}elseif(is_null($xrs['rootSection']) || $xrs['rootSection'] != 1){
					print $redasterix.' ';
					$haveerrprods=TRUE;
				}

				$hasstock = true;
				if((int)$xrs['pDisplay'] == 0 || ($stockManage != 0 && $xrs['pInStock'] <= 0  && ! $stockbyoptions) || ($stockManage == 0 && $xrs['pSell'] == 0)) $hasstock=FALSE;
				if(! $hasstock) print '<span style="color:#FF0000;font-weight:bold">';
				if((int)$xrs['pDisplay'] == 0) print "<strike>";
				print $xrs['pName'];
				if((int)$xrs['pDisplay'] == 0) print "</strike>";
				if(! $hasstock) print '</span>';
				if($stockManage>0) print " (" . ($stockbyoptions?"-":$xrs['pInStock']) . ")"?></td><td align="center"><input <?php
				for($index=0;$index<$numcoupons;$index++){
					if($allcoupon[$index][0]==$xrs['pID']){
						print 'style="color:#FF0000" ';
						break;
					}
				}
			?>type="button" value="<?php print $yyAssign?>" onclick="dsc('<?php print str_replace(array("\\","'",'"'),array("\\\\","\'",'&quot;'),$xrs['pID'])?>')" /></td><td><input type="button" value="<?php print $yyModify?>" onclick="mrk('<?php print str_replace(array("\\","'",'"'),array("\\\\","\'",'&quot;'),$xrs['pID'])?>',event)" /></td><?php
				if($rid != ''){
			?><td align="center"><input type="hidden" name="updq<?php print $rownum?>" value="<?php print htmlspecials($xrs['pID'])?>" /><input type="checkbox" name="updr<?php print $rownum?>" value="1" <?php
					if($rid==$xrs['pID'])
						print 'disabled ';
					else{
						for($index=0; $index<$numrid; $index++)
							if($ridarr[$index]['rpRelProdID']==$xrs['pID']){ print 'checked="checked" '; break; }
					} ?>/></td><?php
				}else{
			?><td><input type="button" id="rel<?php print htmlspecials($xrs['pID'])?>" value="<?php print $yyRelate?>" onclick="rel('<?php print str_replace(array("\\","'",'"'),array("\\\\","\'",'&quot;'),$xrs['pID'])?>')" /></td><?php
				}
			?><td><input type="button" value="<?php print $yyDelete?>" onclick="drk('<?php print str_replace(array("\\","'",'"'),array("\\\\","\'",'&quot;'),$xrs['pID'])?>')" /></td></tr><?php
			print "\r\n";
			$resultcounter++;
		}
		function displayheaderrow(){
			global $yyPrId,$yyPrName,$yyDiscnt,$yyModify,$yyRelate,$yyDelete,$yyStck,$useStockManagement,$pract,$adminlangsettings,$adminlanguages,
			$yyPrPri,$yyWhoPri,$yyListPr,$yyStck,$yyDelete,$yyPrWght,$yyDisPro,$yyStatPg,$yyRecomd,$yyProdOr,$yySellBut; ?>
			<tr>
				<td width="5%" align="center">
					<select name="pract" id="pract" size="1" onchange="changepract(this)">
					<option value="none">Quick Entry...</option>
					<option value="prn"<?php if($pract=='prn') print ' selected="selected"'?>><?php print $yyPrName?></option>
<?php		for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 1)==1) print '<option value="prn'.$index.'"' . ($pract==('prn'.$index)?' selected="selected"':'') . '>' . $yyPrName . ' ' . $index . '</option>';
			} ?>
					<option value="pri"<?php if($pract=='pri') print ' selected="selected"'?>><?php print $yyPrPri?></option>
					<option value="wpr"<?php if($pract=='wpr') print ' selected="selected"'?>><?php print $yyWhoPri?></option>
					<option value="lpr"<?php if($pract=='lpr') print ' selected="selected"'?>><?php print $yyListPr?></option>
					<option value="stk"<?php if($pract=='stk') print ' selected="selected"'?>><?php print $yyStck?></option>
					<option value="sta"<?php if($pract=='sta') print ' selected="selected"'?>><?php print $yyStck?> Add</option>
					<option value="prw"<?php if($pract=='prw') print ' selected="selected"'?>><?php print $yyPrWght?></option>
					<option value="dip"<?php if($pract=='dip') print ' selected="selected"'?>><?php print $yyDisPro?></option>
					<option value="stp"<?php if($pract=='stp') print ' selected="selected"'?>><?php print $yyStatPg?></option>
					<option value="rec"<?php if($pract=='rec') print ' selected="selected"'?>><?php print $yyRecomd?></option>
					<option value="sku"<?php if($pract=='sku') print ' selected="selected"'?>>SKU</option>
					<option value="pro"<?php if($pract=='pro') print ' selected="selected"'?>><?php print $yyProdOr?></option>
<?php		if(! $useStockManagement){ ?>
					<option value="sel"<?php if($pract=='sel') print ' selected="selected"'?>><?php print $yySellBut?></option>
<?php		} ?>
					<option value="" disabled="disabled">---------------------</option>
					<option value="del"<?php if($pract=='del') print ' selected="selected"'?>><?php print $yyDelete?></option>
					</select></td>
				<td><strong><?php print $yyPrId?></strong></td>
				<td><strong><?php print $yyPrName?></strong></td>
				<td width="5%" align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyDiscnt?></span></td>
				<td width="5%" align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyModify?></span></td>
				<td width="5%" align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyRelate?></span></td>
				<td width="5%" align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyDelete?></span></td>
			</tr>
<?php	}
		$allcoupon=''; $pidlist='';
		$numcoupons=0;
		$rowcounter=0;
		$sSQL = "SELECT DISTINCT cpaAssignment FROM cpnassign WHERE cpaType=2";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs=mysql_fetch_array($result))
			$allcoupon[$numcoupons++]=$rs;
		mysql_free_result($result);
		if(@$_GET['related']=='go'){
			$sSQL = "SELECT DISTINCT products.pID,pName,pName2,pName3,pDisplay,pSell,pInStock,rootSection,pStockByOpts,pPrice,pWholesalePrice,pListPrice,pOrder,pRecommend,pStaticPage,pSKU,pWeight FROM relatedprods INNER JOIN products ON products.pId=relatedprods.rpRelProdId LEFT OUTER JOIN sections ON products.pSection=sections.sectionID WHERE rpProdId='" . escape_string($rid) . "'";
			if(@$relatedproductsbothways==TRUE) $sSQL .= "UNION SELECT DISTINCT products.pID,pName,pName2,pName3,pDisplay,pSell,pInStock,rootSection,pStockByOpts,pPrice,pWholesalePrice,pListPrice,pOrder,pRecommend,pStaticPage,pSKU,pWeight FROM relatedprods INNER JOIN products ON products.pId=relatedprods.rpProdId LEFT OUTER JOIN sections ON products.pSection=sections.sectionID WHERE rpRelProdId='" . escape_string($rid) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if(mysql_num_rows($result)>0){
				displayheaderrow();
				while($rs = mysql_fetch_assoc($result)){
					if(@$bgcolor!='') $bgcolor=''; else $bgcolor=' class="altdark"';
					displayprodrow($rs,$rowcounter++);
				}
			}else
				print '<tr><td width="100%" colspan="7" align="center"><p>&nbsp;</p><p>' . $yyPrNoRe . '</p><p>' . $yyPrReSe . '</p><p>' . $yyPrReLs . '</p>&nbsp;</td></tr>';
			mysql_free_result($result);
		}else{
			$whereand = ' WHERE ';
			if($thecat=='' || $sortorder=='nsf')
				$sSQL = ' FROM products LEFT OUTER JOIN sections ON products.pSection=sections.sectionID';
			else
				$sSQL = " FROM multisections RIGHT JOIN products ON products.pId=multisections.pId LEFT OUTER JOIN sections ON products.pSection=sections.sectionID";
			if($thecat!=''){
				if($catorman=='man'){
					$sSQL .= $whereand . 'products.pManufacturer=' . $thecat;
					$whereand=' AND ';
				}else{
					$sectionids = getsectionids($thecat, TRUE);
					if($sectionids!=''){
						if(@$sortorder=='nsf')
							$sSQL .= $whereand . " products.pSection IN (" . $sectionids . ") ";
						else
							$sSQL .= $whereand . " (products.pSection IN (" . $sectionids . ") OR multisections.pSection IN (" . $sectionids . ")) ";
						$whereand=' AND ';
					}
				}
			}
			if(@$noautocheckorphans==TRUE && @$_REQUEST['disp']!='4'){
				$sSQL = str_replace('LEFT OUTER JOIN sections ON products.pSection=sections.sectionID','',$sSQL);
			}
			if($sprice != ''){
				if(strpos($sprice, '-') !== FALSE){
					$pricearr=explode('-', $sprice);
					if(! is_numeric($pricearr[0])) $pricearr[0]=0;
					if(! is_numeric($pricearr[1])) $pricearr[1]=10000000;
					$sSQL .= $whereand . "pPrice BETWEEN " . $pricearr[0] . " AND " . $pricearr[1];
					$whereand=' AND ';
				}elseif(is_numeric($sprice)){
					$sSQL .= $whereand . "pPrice='" . escape_string($sprice) . "' ";
					$whereand=' AND ';
				}
			}
			if(trim($stext) != ''){
				$Xstext = escape_string($stext);
				$aText = explode(' ',$Xstext);
				if(@$nosearchadmindescription) $maxsearchindex=1; else $maxsearchindex=2;
				$aFields[0]='products.pId';
				$aFields[1]=getlangid('pName',1);
				$aFields[2]=getlangid('pDescription',2);
				if($stype=='exact'){
					$sSQL .= $whereand . "(products.pId LIKE '%" . $Xstext . "%' OR ".getlangid("pName",1)." LIKE '%" . $Xstext . "%' OR ".getlangid("pDescription",2)." LIKE '%" . $Xstext . "%' OR ".getlangid("pLongDescription",4)." LIKE '%" . $Xstext . "%') ";
					$whereand=' AND ';
				}else{
					$sJoin='AND ';
					if($stype=='any') $sJoin='OR ';
					$sSQL .= $whereand . '(';
					$whereand=' AND ';
					for($index=0;$index<=$maxsearchindex;$index++){
						$sSQL .= '(';
						$rowcounter=0;
						$arrelms=count($aText);
						foreach($aText as $theopt){
							if(is_array($theopt))$theopt=$theopt[0];
							$sSQL .= $aFields[$index] . " LIKE '%" . $theopt . "%' ";
							if(++$rowcounter < $arrelms) $sSQL .= $sJoin;
						}
						$sSQL .= ') ';
						if($index < $maxsearchindex) $sSQL .= 'OR ';
					}
					$sSQL .= ') ';
				}
			}
			if(@$_REQUEST['disp']=='4'){ $sSQL .= $whereand . 'rootSection IS NULL'; $whereand=' AND '; }
			if(@$_REQUEST['disp']=='3'){ $sSQL .= $whereand . '(pInStock<=0 AND pStockByOpts=0)'; $whereand=' AND '; }
			if(@$_REQUEST['disp']=='' || @$_REQUEST['disp']=='5'){ $sSQL .= $whereand . 'pDisplay<>0'; $whereand=' AND '; }
			if(@$_REQUEST['disp']=='2'){ $sSQL .= $whereand . 'pDisplay=0'; $whereand=' AND '; }
			if($sortorder=='ida')
				$sSQL .= ' ORDER BY products.pid';
			elseif($sortorder=='ida')
				$sSQL .= ' ORDER BY products.pid DESC';
			elseif($sortorder=='')
				$sSQL .= ' ORDER BY pName';
			elseif($sortorder=='nad')
				$sSQL .= ' ORDER BY pName DESC';
			elseif($sortorder=='pra')
				$sSQL .= ' ORDER BY pPrice';
			elseif($sortorder=='prd')
				$sSQL .= ' ORDER BY pPrice DESC';
			elseif($sortorder=='daa')
				$sSQL .= ' ORDER BY pDateAdded';
			elseif($sortorder=='dad')
				$sSQL .= ' ORDER BY pDateAdded DESC';
			elseif($sortorder=='poa')
				$sSQL .= ' ORDER BY pOrder';
			elseif($sortorder=='pod')
				$sSQL .= ' ORDER BY pOrder DESC';
			elseif($sortorder=='sta')
				$sSQL .= ' ORDER BY products.pInStock';
			elseif($sortorder=='std')
				$sSQL .= ' ORDER BY products.pInStock DESC';
			if(@$adminproductsperpage=='') $adminproductsperpage=200;
			$tmpSQL = 'SELECT COUNT(DISTINCT products.pId) AS bar' . $sSQL;
			$sSQL = 'SELECT DISTINCT products.pID,pName,pName2,pName3,pDisplay,pSell,pInStock,rootSection,pStockByOpts,pPrice,pWholesalePrice,pListPrice,pOrder,pRecommend,pStaticPage,pSKU,pWeight' . $sSQL;
			if(@$noautocheckorphans==TRUE && @$_REQUEST['disp']!='4') $sSQL = str_replace('rootSection,','',$sSQL);
			$allprods = mysql_query($tmpSQL) or print(mysql_error());
			$iNumOfPages = ceil(mysql_result($allprods,0,'bar')/$adminproductsperpage);
			mysql_free_result($allprods);
			$sSQL .= ' LIMIT ' . ($adminproductsperpage*($CurPage-1)) . ', ' . $adminproductsperpage;
			$result = mysql_query($sSQL) or print(mysql_error());
			$haveerrprods=FALSE;
			if(mysql_num_rows($result) > 0){
				$pblink = '<a href="adminprods.php?rid=' . @$_REQUEST['rid'] . '&disp=' . @$_REQUEST['disp'] . '&scat=' . $scat . '&stext=' . urlencode($stext) . '&stype=' . $stype . '&sprice=' . urlencode($sprice) . '&pg=';
				if($iNumOfPages > 1) print '<tr><td colspan="7" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
				displayheaderrow();
				$addcomma='';
				while($rs = mysql_fetch_assoc($result)){
					if(@$bgcolor!='') $bgcolor=''; else $bgcolor=' class="altdark"';
					displayprodrow($rs,$rowcounter++);
					$pidlist .= $addcomma . "'" . $rs['pID'] . "'";
					$addcomma=',';
				}
				if($haveerrprods) print '<tr><td width="100%" colspan="7"><br />' . $redasterix . $yySeePr . '</td></tr>';
				if($iNumOfPages > 1) print '<tr><td colspan="7" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
			}else{
				print '<tr><td width="100%" colspan="7" align="center"><br />' . $yyPrNone . '<br />&nbsp;</td></tr>';
			}
			mysql_free_result($result);
		}
		if($pidlist != '' && $rid==''){
			print "\r\n" . '<script language="javascript" type="text/javascript">function setcl(tid){document.getElementById(\'rel\'+tid).style.color=\'#FF0000\';}' . "\r\n";
			$sSQL = 'SELECT DISTINCT rpProdId FROM relatedprods WHERE rpProdId IN (' . $pidlist . ')';
			if(@$relatedproductsbothways==TRUE) $sSQL .= ' UNION SELECT DISTINCT rpRelProdId FROM relatedprods WHERE rpRelProdId IN (' . $pidlist . ')';
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_assoc($result))
				print "setcl('" . $rs['rpProdId'] . "');\r\n";
			mysql_free_result($result);
			print '</script>';
		}
	} ?>
			  <tr>
				<td align="center" style="white-space: nowrap"><?php if($resultcounter>0 && $pract!='' && $pract!='none') print '<input type="hidden" name="resultcounter" id="resultcounter" value="'.$resultcounter.'" /><input type="button" value="'.$yyUpdate.'" onclick="quickupdate()" /> <input type="reset" value="'.$yyReset.'" />'; else print '&nbsp;'?></td>
                <td width="100%" colspan="6" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;<br />
					To Clone an item please hold down the &lt;Ctrl&gt; key and click &quot;Modify&quot;.</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
      </table>
<?php
}
?>
