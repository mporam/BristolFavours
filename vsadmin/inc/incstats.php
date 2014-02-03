<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if($_SESSION["loggedon"] != $storesessionvalue || @$disallowlogin==TRUE) exit;
$success=TRUE;
$admindatestr="Y-m-d";
if(@$admindateformat=="") $admindateformat=0;
if($admindateformat==1)
	$admindatestr="m/d/Y";
elseif($admindateformat==2)
	$admindatestr="d/m/Y";
$alreadygotadmin = getadminsettings();
$fromdate = trim(@$_POST["fromdate"]);
$todate = trim(@$_POST["todate"]);
$hasfromdate=FALSE;
$hastodate=FALSE;
if(strtolower($adminencoding)=='iso-8859-1') $raquo='»'; else $raquo='>';
function writemenulevel($id,$itlevel){
	global $allcatsa,$numcats,$thecat,$raquo;
	if($itlevel<10){
		for($wmlindex=0; $wmlindex < $numcats; $wmlindex++){
			if($allcatsa[$wmlindex][2]==$id){
				print "<option value='" . $allcatsa[$wmlindex][0] . "'";
				if(is_array($thecat)){
					foreach($thecat as $catid){
						if($thecat==$allcatsa[$wmlindex][0]) print ' selected="selected"';
					}
				}
				print '>';
				for($index = 0; $index < $itlevel-1; $index++)
					print $raquo . ' ';
				print $allcatsa[$wmlindex][1] . "</option>\n";
				if($allcatsa[$wmlindex][3]==0) writemenulevel($allcatsa[$wmlindex][0],$itlevel+1);
			}
		}
	}
}
function getdatesql($datecol){
	global $hasfromdate, $hastodate, $thefromdate, $thetodate;
	$datesql='';
	if(! ($hasfromdate || $hastodate))
		; // nothing
	elseif($hasfromdate && $hastodate)
		$datesql = " AND ".$datecol." BETWEEN '" . date("Y-m-d", $thefromdate) . "' AND '" . date("Y-m-d", $thetodate) . "'";
	elseif($hasfromdate)
		$datesql = " AND ".$datecol." BETWEEN '" . date("Y-m-d", $thefromdate) . "' AND '" . date("Y-m-d", $thefromdate+86400) . "'";
	return($datesql);
}
if($fromdate != ''){
	$hasfromdate=TRUE;
	if(is_numeric($fromdate))
		$thefromdate = time()-($fromdate*60*60*24);
	else
		$thefromdate = parsedate($fromdate);
	$hastodate=TRUE;
	if($todate=='')
		$hastodate=FALSE;
	elseif(is_numeric($todate))
		$thetodate = time()-($todate*60*60*24);
	else
		$thetodate = parsedate($todate);
	if($hasfromdate && $hastodate){
		if($thefromdate > $thetodate){
			$tmpdate = $thetodate;
			$thetodate = $thefromdate;
			$thefromdate = $tmpdate;
		}
		$thetodate+=86400;
	}
}else{
	$thefromdate = time()-(60*60*24*365);
	$thetodate = time();
}
$numstatus=0;
$sSQL = "SELECT statID,statPrivate FROM orderstatus WHERE statPrivate<>'' ORDER BY statID";
$result = mysql_query($sSQL) or print(mysql_error());
while($rs = mysql_fetch_assoc($result)){
	$allstatus[$numstatus++]=$rs;
}
mysql_free_result($result);
$themask = 'yyyy-mm-dd';
if($admindateformat==1)
	$themask='mm/dd/yyyy';
elseif($admindateformat==2)
	$themask='dd/mm/yyyy';
$ordstate = @$_POST['ordstate'];
$ordcountry = @$_POST['ordcountry'];
$ordstatus = @$_POST['ordstatus'];
$thecat = @$_POST['scat'];
$payprovider = @$_POST['payprovider'];
$stext = trim(@$_POST['stext']);
$stsearch = @$_POST['stsearch'];
//foreach($_POST as $objItem => $objValue){
//	print $objItem . " : " . $_POST[$objItem] . "<br>";
//}
?>
<script language="javascript" type="text/javascript" src="popcalendar.js">
</script>
		  <form method="post" action="adminorders.php" name="psearchform">
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr><td class="cobhl" colspan="4" align="center"><strong><?php print $yySalRep?></strong></td></tr>
			  <tr> 
                <td class="cobhl" align="right" width="25%"><strong><?php print $yyOrdFro?>:</strong></td>
				<td class="cobll" align="left" width="25%">&nbsp;<input type="text" size="14" name="fromdate" value="<?php print $fromdate?>" /> <input type="button" onclick="popUpCalendar(this, document.forms.psearchform.fromdate, '<?php print $themask?>', 0)" value='DP' /></td>
				<td class="cobhl" align="right" width="25%"><strong><?php print $yyOrdTil?>:</strong></td>
				<td class="cobll" align="left" width="25%">&nbsp;<input type="text" size="14" name="todate" value="<?php print $todate?>" /> <input type="button" onclick="popUpCalendar(this, document.forms.psearchform.todate, '<?php print $themask?>', -205)" value='DP' /></td>
			  </tr>
			  <tr>
				<td class="cobhl" align="right"><strong><?php print $yySeaTxt?>:</strong></td>
				<td class="cobll" align="left" colspan="3">&nbsp;<input type="text" size="24" name="stext" value="<?php print htmlspecials($stext)?>" />
				<select name="stype" size="1">
						<option value=""><?php print $yySrchAl?></option>
						<option value="any" <?php if(@$_POST['stype']=='any') print 'selected="selected"'?>><?php print $yySrchAn?></option>
						<option value="exact" <?php if(@$_POST['stype']=='exact') print 'selected="selected"'?>><?php print $yySrchEx?></option>
						</select> &nbsp;
				<input type="checkbox" name="stsearch[]" value="ordaffiliate" <?php if(is_array($stsearch) && in_array('ordaffiliate', $stsearch)!==FALSE) print 'checked '?>/> <?php print $yyAffili?>
				<input type="checkbox" name="stsearch[]" value="cartprodid" <?php if(is_array($stsearch) && in_array('cartprodid', $stsearch)!==FALSE) print 'checked '?>/> <?php print $yyPrId?>
				<input type="checkbox" name="stsearch[]" value="cartprodname" <?php if(is_array($stsearch) && in_array('cartprodname', $stsearch)!==FALSE) print 'checked '?>/> <?php print $yyPrName?>
				</td>
			  </tr>
			  <tr>
				<td class="cobhl" align="center"><strong><?php print $yySection?></strong>&nbsp;&nbsp;<input type="checkbox" name="notsection" value="ON" <?php if(@$_POST['notsection']=='ON') print 'checked '?>/><strong>...<?php print $yyNot?></strong></td>
				<td class="cobhl" align="center"><strong><?php print $yyOrdSta?></strong>&nbsp;&nbsp;<input type="checkbox" name="notstatus" value="ON" <?php if(@$_POST['notstatus']=='ON') print 'checked '?>/><strong>...<?php print $yyNot?></strong></td>
				<td class="cobhl" align="center"><strong><?php print $yyState?></strong>&nbsp;&nbsp;<input type="checkbox" name="notstate" value="ON" <?php if(@$_POST['notstate']=='ON') print 'checked '?>/><strong>...<?php print $yyNot?></strong></td>
				<td class="cobhl" align="center"><strong><?php print $yyCountry?></strong>&nbsp;&nbsp;<input type="checkbox" name="notcountry" value="ON" <?php if(@$_POST['notcountry']=='ON') print 'checked '?>/><strong>...<?php print $yyNot?></strong></td>
			  </tr>
			  <tr>
				<td class="cobll" align="center"><select name="scat[]" size="5" multiple="multiple"><?php
						$sSQL = "SELECT sectionID,sectionWorkingName,topSection,rootSection FROM sections " . (@$adminonlysubcats==TRUE ? "WHERE rootSection=1 ORDER BY sectionWorkingName" : "ORDER BY sectionOrder");
						$allcats = mysql_query($sSQL) or print(mysql_error());
						$lasttsid = -1;
						$numcats = 0;
						while($row = mysql_fetch_row($allcats)){
							$allcatsa[$numcats++]=$row;
						}
						mysql_free_result($allcats);
						if($numcats > 0){
							if(@$adminonlysubcats==TRUE){
								for($index=0;$index<$numcats;$index++){
									print '<option value="' . $allcatsa[$index][0] . '"';
									if(is_array($thecat)){
										foreach($thecat as $catid){
											if($allcatsa[$index][0]==$catid) print ' selected="selected"';
										}
									}
									print '>' . $allcatsa[$index][1] . "</option>\n";
								}
							}else
								writemenulevel(0,1);
						} ?>
					  </select></td>
				<td class="cobll" align="center"><select name="ordstatus[]" size="5" multiple="multiple"><?php
						$numstatus=0;
						$sSQL = "SELECT statID,statPrivate FROM orderstatus WHERE statPrivate<>'' ORDER BY statID";
						$result = mysql_query($sSQL) or print(mysql_error());
						while($rs = mysql_fetch_assoc($result)){
							print '<option value="' . $rs['statID'] . '"';
							if(is_array($ordstatus)){
								foreach($ordstatus as $objValue)
									if($objValue==$rs['statID']) print ' selected="selected"';
							}
							print ">" . $rs["statPrivate"] . "</option>";
						}
						mysql_free_result($result); ?></select></td>
				<td class="cobll" align="center"><select name="ordstate[]" size="5" multiple="multiple"><?php
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
						mysql_free_result($result); ?></select></td>
				<td class="cobll" align="center"><select name="ordcountry[]" size="5" multiple="multiple"><?php
						$sSQL = "SELECT countryID,countryName FROM countries WHERE countryEnabled=1 ORDER BY countryOrder DESC, countryName";
						$result = mysql_query($sSQL) or print(mysql_error());
						while($rs = mysql_fetch_assoc($result)){
							print '<option value="' . htmlspecials($rs["countryName"]) . '"';
							if(is_array($ordcountry)){
								foreach($ordcountry as $objValue){
									if($objValue==$rs['countryName']) print ' selected="selected"';
								}
							}
							print '>' . $rs['countryName'] . "</option>\n";
						}
						mysql_free_result($result); ?></select></td>
			  </tr>
			  <tr>
				<td class="cobhl" align="center" colspan="2"><strong><?php print $yyPayMet?>:</strong>
				&nbsp;<select name="payprovider" size="1"><?php
						$sSQL = "SELECT payProvID,payProvName FROM payprovider WHERE payProvEnabled=1 ORDER BY payProvOrder";
						$result = mysql_query($sSQL) or print(mysql_error());
						print '<option value="">'.$yyAll.'</option>';
						while($rs = mysql_fetch_assoc($result)){
							print '<option value="' . $rs['payProvID'] . '"';
							if($payprovider==$rs['payProvID']) print ' selected="selected"';
							print '>' . $rs['payProvName'] . '</option>';
						}
						mysql_free_result($result); ?></select>
				&nbsp;&nbsp;<input type="checkbox" name="notpayprov" value="ON" <?php if(@$_POST['notpayprov']=='ON') print 'checked '?>/><strong>...<?php print $yyNot?></strong>
				</td>
				<td class="cobhl" align="center" colspan="2">
					<select name="grouping" size="1">
					<option value="">Totals</option>
					<option value="1" <?php if(@$_POST['grouping']=='1') print 'selected="selected"'?>><?php print $yyGrByWk?></option>
					<option value="2" <?php if(@$_POST['grouping']=='2') print 'selected="selected"'?>><?php print $yyGrByMo?></option>
					<option value="3" <?php if(@$_POST['grouping']=='3') print 'selected="selected"'?>><?php print $yyGrByYr?></option></select>
					<input type="button" value="Stats" onclick="document.forms.psearchform.action='adminstats.php';document.forms.psearchform.submit();" /> </td>
			  </tr>
			</table>
		  </form>
<?php
$whereclause = 'WHERE cartCompleted=1 ';
if(is_array($ordstatus)) $whereclause .= 'AND ' . (@$_POST['notstatus']=='ON' ? 'NOT ' : '') . "(ordStatus IN (" . implode(',', $ordstatus) . ")) "; else $whereclause .= 'AND ordStatus<>0 AND ordStatus<>1 ';
if(is_array($payprovider)) $whereclause .= 'AND ' . (@$_POST['notpayprov']=='ON' ? 'NOT ' : '') . "(ordPayProvider IN (" . implode(',', $payprovider) . ")) ";
if(is_array($ordstate)) $whereclause .= 'AND ' . (@$_POST['notstate']=='ON' ? 'NOT ' : '') . "(ordState IN ('" . implode("','", $ordstate) . "')) ";
if(is_array($ordcountry)) $whereclause .= 'AND ' . (@$_POST['notcountry']=='ON' ? 'NOT ' : '') . "(ordCountry IN ('" . implode("','",$ordcountry) . "')) ";
$orderclause = str_replace('cartCompleted=1 AND ', '', $whereclause);
if(trim(@$_POST['stext']) != '' && is_array(@$_POST['stsearch'])){
	$sText = escape_string($stext);
	$aText = explode(' ', $sText);
	$aFields = $stsearch;
	if(@$_POST['stype']=='exact'){
		$whereclause .= 'AND (';
		$rowcounter=0;
		$arrelms=count($aFields);
		foreach($aFields as $thefield){
			if(is_array($thefield))$thefield=$thefield[0];
			$whereclause .= $thefield . " LIKE '%" . $sText . "%' ";
			if($thefield=='ordaffiliate') $orderclause .= ' AND ' . $thefield . " LIKE '%" . $sText . "%' ";
			if(++$rowcounter < $arrelms) $whereclause .= 'OR ';
		}
		$whereclause .= ') ';
	}else{
		$sJoin='AND ';
		if(@$_POST['stype']=='any') $sJoin='OR ';
		$whereclause .= 'AND (';
		$whereand=' AND ';
		$index=0;
		$numFields=count($aFields);
		foreach($aFields as $thefield){
			if(is_array($thefield))$thefield=$thefield[0];
			$whereclause .= '(';
			$rowcounter=0;
			$arrelms=count($aText);
			foreach($aText as $theopt){
				if(is_array($theopt))$theopt=$theopt[0];
				$whereclause .= $thefield . " LIKE '%".$theopt."%' ";
				if($thefield=='ordaffiliate') $orderclause .= ' AND ' . $thefield . " LIKE '%" . $sText . "%' ";
				if(++$rowcounter < $arrelms) $whereclause .= $sJoin;
			}
			$whereclause .= ') ';
			if(++$index < $numFields) $whereclause .= 'OR ';
		}
		$whereclause .= ') ';
	}
}
if(is_array($thecat)){
	$catlist = $addcomma = '';
	foreach($thecat as $catid){
		$catlist .= $addcomma . $catid;
		$addcomma=',';
	}
	$sectionids = getsectionids($catlist, TRUE);
	if($sectionids!='') $whereclause .= 'AND ' . (@$_POST['notsection']=='ON' ? 'NOT ' : '') . "(products.pSection IN (" . $sectionids . ")) ";
}
if(@$_POST['grouping']!=''){
	$success = TRUE;
	$dateSQL = 'SELECT ordDate FROM products INNER JOIN cart ON products.pID=cart.cartProdID INNER JOIN orders ON cart.cartOrderID=orders.ordID ';
	$dateSQL .= $whereclause;
	$result = mysql_query($dateSQL . 'ORDER BY ordDate LIMIT 0,1') or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		$minfromdate = strtotime($rs['ordDate']);
		if(! $hasfromdate || $minfromdate > $thefromdate){
			$thefromdate = $minfromdate;
			$hasfromdate = TRUE;
		}
	}else
		$success = FALSE;
	mysql_free_result($result);
	$result = mysql_query($dateSQL . 'ORDER BY ordDate DESC LIMIT 0,1') or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		$maxtodate = strtotime($rs['ordDate']);
		if(! $hastodate || $maxtodate < $thetodate){
			$thetodate = $maxtodate;
			$hastodate = TRUE;
		}
	}else
		$success = FALSE;
	mysql_free_result($result);
	// print "Dates: " . date("Y-m-d", $thefromdate) . ", " . date("Y-m-d", $thetodate) . "<br>";
	if($success){
		if(@$_POST['grouping']=='1'){ // week
			$dotw = (int)date('w',$thefromdate);
			if($dotw==0) $dotw=7;
			$thefromdate -= ($dotw-1)*86400;
		}elseif(@$_POST['grouping']=='2'){ // month
			$thefromdate = mktime(0,0,0,date('m',$thefromdate),1,date('Y',$thefromdate));
		}else{
			$thefromdate = mktime(0,0,0,1,1,date('Y',$thefromdate));
		}
		$thetodate = $thetodate+86400;
	}
	// print "Dates: " . date("Y-m-d", $thefromdate) . ", " . date("Y-m-d", $thetodate) . "<br>";
	if($thefromdate > $thetodate) $success = FALSE;
?>
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" align="center"><strong><?php print $yySalGra?></strong><br />&nbsp;</td>
			  </tr>
<?php
	if(! $success){
		print '<tr><td align="center">No Data</td></tr>';
	}else{
		$maxtot = 0;
		$rowcounter = 0;
		// $sSQL = "SELECT SUM(cartQuantity) AS numorders,SUM(cartProdPrice*cartQuantity) AS theordtot,SUM(ordHandling) AS tothandling,SUM(ordStateTax) AS totstatetax,SUM(ordCountryTax) AS totcountrytax,SUM(ordHSTTax) AS tothsttax,SUM(ordDiscount) AS totdiscount, SUM(ordShipping) AS totshipping ";
		$sSQL = "SELECT SUM(cartQuantity) AS numorders,SUM(cartProdPrice*cartQuantity) AS theordtot ";
		$sSQL .= "FROM products RIGHT JOIN cart ON products.pID=cart.cartProdID INNER JOIN orders ON cart.cartOrderID=orders.ordID ";
		$sSQL .= $whereclause;
		
		$sSQLopts = 'SELECT SUM(coPriceDiff*cartQuantity) AS theordtot ';
		$sSQLopts .= 'FROM cartoptions INNER JOIN (cart LEFT OUTER JOIN products ON cart.cartProdId=products.pID) ON cartoptions.coCartID=cart.cartID INNER JOIN orders ON cart.cartOrderID=orders.ordID ';
		$sSQLopts .= $whereclause;
		// print '<tr><td>' . $sSQL . '<br />' . $sSQLopts . '</td></tr>';
		$themaxdate = $thetodate;
		print '<tr><td align="left"><table border="0" cellspacing="0" cellpadding="0" width="100%" align="left">';
		while($thefromdate<$themaxdate){
			if(@$_POST['grouping']=='1') // week
				$thetodate = $thefromdate + (86400 * 7);
			elseif(@$_POST['grouping']=='2') // month
				$thetodate = mktime(0,0,0,date('m',$thefromdate)+1,date('d',$thefromdate),date('Y',$thefromdate));
			else
				$thetodate = mktime(0,0,0,date('m',$thefromdate),date('d',$thefromdate),date('Y',$thefromdate)+1);
			$result = mysql_query($sSQL . getdatesql('cartDateAdded')) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				if($rs['numorders']==0){
					$outputvals[$rowcounter][0] = $thefromdate;
					$outputvals[$rowcounter][1] = 0;
					$outputvals[$rowcounter][2] = 0;
				}else{
					// $thetot = ($rs['theordtot']+$rs['totshipping']+$rs['tothandling']+$rs['totstatetax']+$rs['totcountrytax']+$rs['tothsttax'])-$rs['totdiscount'];
					$thetot = $rs['theordtot'];
					$outputvals[$rowcounter][0] = $thefromdate;
					$outputvals[$rowcounter][1] = $rs['numorders'];
					$outputvals[$rowcounter][2] = $thetot;
				}
			}
			mysql_free_result($result);
		
			$result = mysql_query($sSQLopts . getdatesql('cartDateAdded')) or print(mysql_error());
			if($rs = mysql_fetch_assoc($result)){
				if($rs['theordtot']==0){
				}else{
					$thetot = $rs['theordtot'];
					$outputvals[$rowcounter][2] += $thetot;
				}
			}
			mysql_free_result($result);

			if($outputvals[$rowcounter][2] > $maxtot) $maxtot = $outputvals[$rowcounter][2];
			$thefromdate = $thetodate;
			$rowcounter++;
		}
		$divisor = $maxtot / 400;
		if($divisor == 0) $divisor = 1;
		print '<tr><td align="right"><strong>Date</strong></td><td align="right"><strong>Sales</strong></td><td align="right"><strong>Grand Total</strong></td>';
		print '<td>&nbsp;</td><td>&nbsp;</td>';
		print '</tr>';
		for($index = 0; $index < $rowcounter; $index++){
			$pixelcolor='bluepixel';
			if(@$_POST['grouping']=='1'){ // week
				if(date('W', $outputvals[$index][0])=='1') $pixelcolor='redpixel';
			}elseif(@$_POST['grouping']=='2'){ // month
				if(date('m', $outputvals[$index][0])=='1') $pixelcolor='redpixel';
			}
			print '<tr><td align="right">' . date($admindatestr, $outputvals[$index][0]) . '</td><td align="right">' . $outputvals[$index][1] . '</td><td align="right">' . number_format($outputvals[$index][2], (@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2),'.','') . '</td>';
			print '<td>&nbsp;</td><td><img src="adminimages/' . $pixelcolor . '.gif" width="' . (int)($outputvals[$index][2] / $divisor) . '" height="2" alt="" /></td>';
			print '</tr>';
		}
		print '</table></td></tr>';
	}
?>
			</table>
<?php
}else{
$whereclause .= getdatesql('cartDateAdded');
$orderclause .= getdatesql('ordDate');
?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%" align="center">
            <table width="650" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" align="center"><strong>Order Results (Not limited by product / section)</strong><br />&nbsp;</td>
			  </tr>
<?php
	$sSQL = "SELECT COUNT(ordID) AS numorders,SUM(ordTotal) AS theordtot,SUM(ordHandling) AS tothandling,SUM(ordStateTax) AS totstatetax,SUM(ordCountryTax) AS totcountrytax,SUM(ordHSTTax) AS tothsttax,SUM(ordDiscount) AS totdiscount, SUM(ordShipping) AS totshipping ";
	$sSQL .= "FROM orders ";
	$sSQL .= $orderclause;
	// print '<tr><td>' . $sSQL . '</td></tr>';
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		print '<tr><td align="left"><table border="0" cellspacing="0" cellpadding="0" width="100%" align="left">';
		print '<tr><td><strong>'.$yyTotOrd.'</strong></td><td><strong>' . $xxOrdTot . '</strong></td><td><strong>' . $xxShippg . '</strong></td><td><strong>' . $xxHndlg . '</strong></td><td><strong>' . $xxDscnts . '</strong></td><td><strong>' . $xxStaTax . '</strong></td><td><strong>' . $xxCntTax . '</strong></td><td><strong>' . $xxGndTot . '</strong></td></tr>';
		if($rs['numorders']==0)
			print '<tr><td>0</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>';
		else
			print '<tr><td>' . $rs['numorders'] . '</td><td>' . FormatEuroCurrency($rs['theordtot']) . '</td><td>' . FormatEuroCurrency($rs['totshipping']) . '</td><td>' . FormatEuroCurrency($rs['tothandling']) . '</td><td>' . FormatEuroCurrency($rs['totdiscount']) . '</td><td>' . FormatEuroCurrency($rs['totstatetax']) . '</td><td>' . FormatEuroCurrency($rs['totcountrytax']) . '</td><td>' . FormatEuroCurrency(($rs['theordtot']+$rs['totshipping']+$rs['tothandling']+$rs['totstatetax']+$rs['totcountrytax']+$rs['tothsttax'])-$rs['totdiscount']) . '</td></tr>';
		print '</table></td></tr>';
	}
	mysql_free_result($result);
?>
			</table>
		  </td>
		</tr>
	  </table>
<?php	flush() ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%" align="center">
            <table width="200" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" align="center">&nbsp;<br /><strong><?php print $yySalRes?></strong><br />&nbsp;</td>
			  </tr>
<?php
	$sSQLopts = 'SELECT SUM(coPriceDiff*cartQuantity) AS theordtot ';
	$sSQLopts .= 'FROM cartoptions INNER JOIN cart ON cartoptions.coCartID=cart.cartID LEFT OUTER JOIN products ON cart.cartProdId=products.pID INNER JOIN orders ON cart.cartOrderID=orders.ordID ';
	$sSQLopts .= $whereclause;
	$totopts = 0;
	$result = mysql_query($sSQLopts) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		if(is_numeric($rs['theordtot'])) $totopts = $rs['theordtot'];
	}
	mysql_free_result($result);
	
	$sSQL = "SELECT SUM(cartQuantity) AS numorders,SUM(cartProdPrice*cartQuantity) AS theordtot ";
	$sSQL .= "FROM products INNER JOIN cart ON products.pID=cart.cartProdID INNER JOIN orders ON cart.cartOrderID=orders.ordID ";
	$sSQL .= $whereclause;
	// print '<tr><td>' . $sSQL . '</td></tr>';
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		print '<tr><td align="left"><table border="0" cellspacing="0" cellpadding="0" width="100%" align="left">';
		print '<tr><td><strong>'.$yyTotItm.'</strong></td><td><strong>'.$yyItmTot.'</strong></td></tr>';
		if($rs['numorders']==0)
			print '<tr><td>0</td><td>-</td></tr>';
		else
			print '<tr><td>' . $rs['numorders'] . '</td><td>' . FormatEuroCurrency($rs['theordtot']+$totopts) . '</td></tr>';
		print '</table></td></tr>';
	}
	mysql_free_result($result);
?>
			</table>
		  </td>
		</tr>
	  </table>
<?php
	flush();
	$sSQLopts = 'SELECT SUM(coPriceDiff*cartQuantity) AS theordtot,cartProdID,cartProdName ';
	$sSQLopts .= 'FROM cartoptions INNER JOIN cart ON cartoptions.coCartID=cart.cartID LEFT OUTER JOIN products ON cart.cartProdId=products.pID INNER JOIN orders ON cart.cartOrderID=orders.ordID ';
	$sSQLopts .= $whereclause . ' GROUP BY cartProdID,cartProdName';
	$numalloptions = 0;
	$result = mysql_query($sSQLopts) or print(mysql_error());
	while($rs = mysql_fetch_assoc($result)){
		$alloptions[$numalloptions++] = $rs;
	}
	mysql_free_result($result);
	for($index2=1; $index2<=2; $index2++){	
?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%" align="center">
			<table width="650" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" align="center">&nbsp;<br /><strong><?php
					print $yyTopSal;
					if($index2==1) print ' By Quantity'; else print ' By Amount'; ?></strong><br />&nbsp;</td>
			  </tr>
<?php
		$sSQL = "SELECT SUM(cartQuantity) AS thecount,SUM(cartProdPrice*cartQuantity) AS theordtot,cartProdID,cartProdName ";
		$sSQL .= "FROM products INNER JOIN cart ON products.pID=cart.cartProdID INNER JOIN orders ON cart.cartOrderID=orders.ordID ";
		$sSQL .= $whereclause . ' GROUP BY cartProdID,cartProdName ';
		if($index2==1)
			$sSQL .= 'ORDER BY thecount DESC LIMIT 0,100';
		else
			$sSQL .= 'ORDER BY theordtot DESC LIMIT 0,100';
		// print '<tr><td>' . $sSQL . '</td></tr>';
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result)>0){
			print '<tr><td align="left"><table border="0" cellspacing="0" cellpadding="0" width="100%" align="left">';
			print '<tr><td><strong>' . $yyPrId . '</strong></td><td><strong>' . $yyPrName . '</strong></td><td align="right"><strong>' . $yyTotSal . '</strong></td><td align="right"><strong>' . $yyAmount . '</strong></td></tr>';
			while($rs = mysql_fetch_assoc($result)){
				$addoptions=0;
				if($numalloptions > 0){
					foreach($alloptions as $key => $val){
						if($val['cartProdID']==$rs['cartProdID'] && $val['cartProdName']==$rs['cartProdName']){
							$addoptions = $val['theordtot'];
							break;
						}
					}
				}
				print '<tr><td>' . $rs['cartProdID'] . '</td><td>' . $rs['cartProdName'] . '</td><td align="right">' . $rs['thecount'] . '</td><td align="right">' . number_format($rs['theordtot']+$addoptions, (@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2),'.','') . '</td></tr>';
			}
			print '</table></td></tr>';
		}
		mysql_free_result($result);
?>
			</table>
		  </td>
		</tr>
	  </table>
<?php
	}
	flush() ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%" align="center">
			<table width="550" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" align="center">&nbsp;<br /><strong><?php print $yyTopCou?></strong><br />&nbsp;</td>
			  </tr>
<?php
	$sSQLopts = 'SELECT SUM(coPriceDiff*cartQuantity) AS theordtot,ordCountry ';
	$sSQLopts .= 'FROM cartoptions INNER JOIN cart ON cartoptions.coCartID=cart.cartID LEFT OUTER JOIN products ON cart.cartProdId=products.pID INNER JOIN orders ON cart.cartOrderID=orders.ordID ';
	$sSQLopts .= $whereclause . ' GROUP BY ordCountry';
	$numalloptions = 0;
	$alloptions='';
	$result = mysql_query($sSQLopts) or print(mysql_error());
	while($rs = mysql_fetch_assoc($result)){
		$alloptions[$numalloptions++] = $rs;
	}
	mysql_free_result($result);

	$sSQL = "SELECT SUM(cartQuantity) AS thecount,SUM(cartProdPrice*cartQuantity) AS theordtot,ordCountry ";
	$sSQL .= "FROM products INNER JOIN cart ON products.pID=cart.cartProdID INNER JOIN orders ON cart.cartOrderID=orders.ordID ";
	$sSQL .= $whereclause . ' GROUP BY ordCountry ORDER BY thecount DESC LIMIT 0,100';
	// print '<tr><td>' . $sSQL . '</td></tr>';
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result)>0){
		print '<tr><td align="left"><table border="0" cellspacing="0" cellpadding="0" width="100%" align="left">';
		print '<tr><td><strong>'.$yyCntNam.'</strong></td><td align="right"><strong>' . $yyTotSal . '</strong></td><td align="right"><strong>' . $yyAmount . '</strong></td></tr>';
		while($rs = mysql_fetch_assoc($result)){
			$addoptions=0;
			if($numalloptions > 0){
				foreach($alloptions as $key => $val){
					if($val['ordCountry']==$rs['ordCountry']){
						$addoptions = $val['theordtot'];
						break;
					}
				}
			}
			print '<tr><td>' . $rs['ordCountry'] . '</td><td align="right">' . $rs['thecount'] . '</td><td align="right">' . number_format($rs['theordtot']+$addoptions, (@$overridecurrency==TRUE && is_numeric(@$orcdecplaces) ? $orcdecplaces : 2),'.','') . '</td></tr>';
		}
		print '</table></td></tr>';
	}
	mysql_free_result($result);
?>
			  <tr> 
                <td width="100%" align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </td>
		</tr>
	  </table>
<?php
} // grouping
?>
