<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
include "./vsadmin/inc/incemail.php";
$ordGrandTotal = $ordTotal = $ordStateTax = $ordHSTTax = $ordCountryTax = $ordShipping = $ordHandling = $ordDiscount = 0;
$ordID = $affilID = $ordCity = $ordState = $ordCountry = $ordDiscountText = $ordEmail = '';
if(@$_SERVER['CONTENT_LENGTH'] != '' && $_SERVER['CONTENT_LENGTH'] > 10000) exit;
if(@$dateformatstr == "") $dateformatstr = "m/d/Y";
$success = true;
$digidownloads=false;
$alreadygotadmin = getadminsettings();
if(@$_POST["posted"]=="1"){
	$email = escape_string(unstripslashes(@$_POST["email"]));
	$ordid = escape_string(unstripslashes(@$_POST["ordid"]));
	if(! is_numeric($ordid)){
		$success = false;
		$errormsg = $xxStaEr1;
	}elseif($email != "" && $ordid != ""){
		$sSQL = "SELECT ordStatus,ordStatusDate,".getlangid("statPublic",64).",ordTrackNum,ordAuthNumber,ordStatusInfo FROM orders INNER JOIN orderstatus ON orders.ordStatus=orderstatus.statID WHERE ordID=" . $ordid . " AND ordEmail='" . $email . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result)>0){
			$rs = mysql_fetch_array($result);
			$ordStatus = $rs["ordStatus"];
			$ordStatusDate = strtotime($rs["ordStatusDate"]);
			$statPublic = $rs[getlangid("statPublic",64)];
			$ordAuthNumber = trim($rs['ordAuthNumber']);
			$ordStatusInfo = trim($rs["ordStatusInfo"]);
			$ordTrackNum = trim($rs["ordTrackNum"]);
			if(@$trackingnumtext == '') $trackingnumtext=$xxTrackT;
			if($ordTrackNum != '') $trackingnum=str_replace('%s', $ordTrackNum, $trackingnumtext); else $trackingnum='';
		}else{
			$success = false;
			$errormsg = $xxStaEr2;
		}
		mysql_free_result($result);
	}else{
		$success = false;
		$errormsg = $xxStaEnt;
	}
}
?>
<br />
		<form method="post" name="statusform" action="orderstatus.php">
		  <input type="hidden" name="posted" value="1" />
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
<?php	if(@$_POST["posted"]=="1" && $success){ ?>
			  <tr>
				<td class="cobhl" colspan="2" height="34" align="center"><span style="font-size:18px;font-weight:bold"><?php print $xxStaVw?></span></td>
			  </tr>
			  <tr>
			    <td class="cobhl" height="34" align="center" colspan="2"><strong><?php print $xxStaCur . " " . $ordid?></strong></td>
			  </tr>
			  <tr>
			    <td class="cobhl" height="34" align="right" width="40%"><strong><?php print $xxStatus?> : </strong></td>
				<td class="cobll" height="34" align="left"><?php print $statPublic?></td>
			  </tr>
			  <tr>
			    <td class="cobhl" height="34" align="right" width="40%"><strong><?php print $xxDate?> : </strong></td>
				<td class="cobll" height="34" align="left"><?php print date($dateformatstr, $ordStatusDate)?></td>
			  </tr>
			  <tr>
			    <td class="cobhl" height="34" align="right" width="40%"><strong><?php print $xxTime?> : </strong></td>
				<td class="cobll" height="34" align="left"><?php print date("H:i", $ordStatusDate)?></td>
			  </tr>
<?php		if($trackingnum != ""){ ?>
			  <tr>
			    <td class="cobhl" height="34" align="right" width="40%"><strong><?php print $xxTraNum?> : </strong></td>
				<td class="cobll" height="34" align="left"><?php print $trackingnum?></td>
			  </tr>
<?php		}
			if($ordStatusInfo != ""){ ?>
			  <tr>
			    <td class="cobhl" height="34" align="right" width="40%"><strong><?php print $xxAddInf?> : </strong></td>
				<td class="cobll" height="34" align="left"><?php print $ordStatusInfo?></td>
			  </tr>
<?php		}
			if($ordAuthNumber != ''){ ?>
			  <tr>
				<td class="cobll" colspan="2" align="center"><?php
					$xxThkYou='';
					$xxRecEml='';
					do_order_success($ordid,'',FALSE,TRUE,FALSE,FALSE,FALSE) ?></td>
			  </tr>
<?php		}
		}else{ ?>
			  <tr>
				<td class="cobhl" colspan="2" align="center">&nbsp;<br /><span style="font-size:18px;font-weight:bold"><?php print $xxStaVw?></span><br />&nbsp;</td>
			  </tr>
<?php	} ?>
			  <tr>
			    <td class="cobhl" colspan="2" height="34" align="center"><strong><?php print $xxStaEnt?></strong></td>
			  </tr>
<?php	if($success==false){ ?>
			  <tr>
			    <td class="cobhl" width="40%" height="34" align="right"><strong><?php print $xxStaErr?> : </strong></td>
				<td class="cobll" height="34" align="left"><span style="color:#FF0000"><?php print $errormsg?></span></td>
			  </tr>
<?php	} ?>
			  <tr>
			    <td class="cobhl" width="40%" height="34" align="right"><strong><?php print $xxOrdId?> : </strong></td>
				<td class="cobll" height="34" align="left"><input type="text" size="20" name="ordid" value="<?php print htmlspecials(unstripslashes(trim(@$_POST['ordid'])))?>" /></td>
			  </tr>
			  <tr>
			    <td class="cobhl" width="40%" height="34" align="right"><strong><?php print $xxEmail?> : </strong></td>
				<td class="cobll" height="34" align="left"><input type="text" size="30" name="email" value="<?php print htmlspecials(unstripslashes(trim(@$_POST['email'])))?>" /></td>
			  </tr>
			  <tr>
				<td class="cobll" height="34" colspan="2" align="center" valign="middle"><?php print imageorsubmit(@$imgvieworderstatus, $xxStaVw, '')?></td>
			  </tr>
			</table>
		  </form>
		  <br />&nbsp;
