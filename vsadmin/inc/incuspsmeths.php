<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if($_SESSION["loggedon"] != $storesessionvalue || @$disallowlogin==TRUE) exit;
$success=TRUE;
$alreadygotadmin = getadminsettings();
$method=trim(@$_REQUEST["method"]);
if($method != '') $shipType=(int)$method;
$shipmet = "USPS";
if($shipType==4) $shipmet = "UPS";
if($shipType==6) $shipmet = $yyCanPos;
if($shipType==7) $shipmet = "FedEx";
if(@$_POST["posted"]=="1"){
	if(@$_POST['doadmin']!=''){
		if($shipType==3){
			$sSQL="UPDATE admin SET adminUSPSUser='".escape_string(@$_POST['adminUSPSUser'])."' WHERE adminID=1";
			mysql_query($sSQL) or print(mysql_error());
		}elseif($shipType==4){
			$sSQL="UPDATE admin SET adminUPSNegotiated=".@$_POST['UPSNegotiated']." WHERE adminID=1";
			mysql_query($sSQL) or print(mysql_error());
		}elseif($shipType==6){
			$sSQL="UPDATE admin SET adminCanPostUser='".escape_string(@$_POST['adminCanPostUser'])."' WHERE adminID=1";
			mysql_query($sSQL) or print(mysql_error());
		}
	}else{
		if($shipType==3){
			for($index=1;$index<=50;$index++){
				if(trim(@$_POST['methodshow' . $index]) != ''){
					$sSQL = "UPDATE uspsmethods SET uspsShowAs='" . escape_string(unstripslashes(@$_POST['methodshow' . $index])) . "',";
					if(@$_POST['methodfsa' . $index]=='ON')
						$sSQL .= 'uspsFSA=1,';
					else
						$sSQL .= 'uspsFSA=0,';
					if(@$_POST['methoduse' . $index]=='ON')
						$sSQL .= 'uspsUseMethod=1 WHERE uspsID=' . $index;
					else
						$sSQL .= 'uspsUseMethod=0 WHERE uspsID=' . $index;
					mysql_query($sSQL) or print(mysql_error());
				}
			}
		}elseif($shipType==4 || $shipType==6 || $shipType==7){
			$indexadd=0;
			if($shipType==6) $indexadd=100; elseif($shipType==7) $indexadd=200;
			for($index=100+$indexadd;$index<=125+$indexadd;$index++){
				if(trim(@$_POST['methodshow' . $index]) != ''){
					$sSQL = 'UPDATE uspsmethods SET ';
					if(@$_POST['methodfsa' . $index]=='ON')
						$sSQL .= 'uspsFSA=1,';
					else
						$sSQL .= "uspsFSA=0,";
					if(@$_POST["methoduse" . $index]=="ON")
						$sSQL .= "uspsUseMethod=1 WHERE uspsID=" . $index;
					else
						$sSQL .= "uspsUseMethod=0 WHERE uspsID=" . $index;
					mysql_query($sSQL) or print(mysql_error());
				}
			}
		}
	}
	print '<meta http-equiv="refresh" content="2; url=adminuspsmeths.php">';
}
if(@$_GET['admin']!=''){
	$sSQL = 'SELECT adminUSPSUser,adminUPSUser,adminUPSPw,adminUPSAccess,adminUPSAccount,adminUPSNegotiated,adminCanPostUser FROM admin WHERE adminID=1';
	$result = mysql_query($sSQL) or print(mysql_error());
	$rs = mysql_fetch_assoc($result);
?>
		  <form method="post" action="adminuspsmeths.php">
<?php
	writehiddenvar('doadmin', '1');
	writehiddenvar('method', @$_GET['admin']);
	writehiddenvar('posted', '1'); ?>
			<table width="100%" border="0" cellspacing="2" cellpadding="3">
<?php
	if(@$_GET['admin']=='3'){ ?>
			  <tr>
                <td colspan="2" align="center"><strong>USPS Admin</strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td width="100%" align="center" colspan="2"><hr width="70%" /><?php print $yyIfUSPS?><br /></td>
			  </tr>
			  <tr>
				<td width="50%" align="right"><strong><?php print $yyUname?>: </strong></td>
				<td width="50%" align="left"><input type="text" size="15" name="adminUSPSUser" value="<?php print $rs['adminUSPSUser']?>" /></td>
			  </tr>
<?php
	}elseif(@$_GET['admin']=='4'){ ?>
			  <tr>
                <td colspan="2" align="center"><strong>UPS Admin</strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td width="100%" align="center" colspan="2"><hr width="70%" /><p>To obtain your UPS Rate Code you need to use the registration form <a href="adminupslicense.php"><strong>here</strong></a>.</p>
				<p>To use UPS Negotiated Rates, you need to register first and specify your UPS Shipper Number in the registration form. Then forward your UPS Rate Code and Shipper Number to your UPS Account Manager who will enable UPS Negotiated Rates once approved.</p></td>
			  </tr>
			  <tr>
				<td width="50%" align="right"><strong>UPS Rate Code: </strong></td>
				<td width="50%" align="left"><?php print upsdecode($rs['adminUPSUser'], '')?></td>
			  </tr>
			  <tr>
				<td width="50%" align="right"><strong>UPS Shipper Number: </strong></td>
				<td width="50%" align="left"><?php print $rs['adminUPSAccount']?></td>
			  </tr>
			  <tr>
				<td width="50%" align="right"><strong>Use Negotiated Rates: </strong></td>
				<td width="50%" align="left"><select size="1" name="UPSNegotiated">
					<option value="0">Use Published Rates</option>
<?php	if(trim($rs['adminUPSUser'])!='' && trim($rs['adminUPSAccount'])!='') print '<option value="1"' . ((int)$rs['adminUPSNegotiated']!=0 ? ' selected="selected"' : '') . '>Use Negotiated Rates</option>' ?>
					</select>
				</td>
			  </tr>
<?php
	}elseif(@$_GET['admin']=='6'){ ?>
			  <tr>
                <td colspan="2" align="center"><strong><?php print $yyCanPos?> Admin</strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td width="100%" align="center" colspan="2"><hr width="70%" /><?php print $yyEnMerI?></td>
			  </tr>
			  <tr>
				<td colspan="2" align="center"><strong><?php print $yyRetID?>: </strong><input type="text" size="36" name="adminCanPostUser" value="<?php print $rs['adminCanPostUser']?>" /></td>
			  </tr>
<?php
	} ?>
			  <tr>
				<td width="100%" align="center" colspan="2">&nbsp;</td>
			  </tr>
			  <tr>
				<td width="100%" align="center" colspan="2"><input type="submit" value="<?php print $yySubmit?>" /> <input type="reset" value="<?php print $yyReset?>" /></td>
			  </tr>
<?php
	if(@$_GET['admin']=='4'){ ?>
			  <tr>
				<td width="100%" align="center" colspan="2"><br /><span style="font-size:10px">Please note: Subsequent registrations for UPS OnLine® Tools will change the UPS Rate Code
within this application. In the event Negotiated Rates functionality was enabled under a previous UPS Rate Code, the
Negotiated Rates functionality will be disabled.</span></td>
			  </tr>
<?php
	} ?>
			  <tr>
				<td width="100%" align="center" colspan="2"><br />&nbsp;<br />&nbsp;<br /><a href="adminuspsmeths.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
			</table>
		  </form>
<?php
	mysql_free_result($result);
}elseif($method==''){ ?>
			<table width="100%" border="0" cellspacing="2" cellpadding="3">
			  <tr>
                <td align="center">
					&nbsp;<br /><strong><?php print $yyUsUpd . ' ' . $yyShpMet?>.</strong><br />&nbsp;<br />&nbsp;
			<table width="90%" border="0" cellspacing="1" cellpadding="3" class="cobtbl">
			  <tr>
				<td align="center" class="cobhl" height="25"><strong>USPS</strong></td>
				<td align="center" class="cobll"><strong>UPS</strong></td>
				<td align="center" class="cobhl"><strong>Canada Post</strong></td>
				<td align="center" class="cobll"><strong>FedEx</strong></td>
			  </tr>
			  <tr>
				<td align="center" class="cobhl" height="22"><input type="button" value="Register with USPS" onclick="window.open('https://secure.shippingapis.com/registration/','USPSSignup','')" /></strong></td>
				<td align="center" class="cobll"><input type="button" value="<?php print $yyRegUPS?>" onclick="document.location='adminupslicense.php'" /></td>
				<td class="cobhl">&nbsp; </td>
				<td align="center" class="cobll"><input type="button" value="<?php print str_replace("UPS","FedEx",$yyRegUPS)?>" onclick="document.location='adminfedexlicense.php'" /></td>
			  </tr>
			  <tr>
				<td align="center" class="cobhl" height="22"><input type="button" value="USPS Admin" onclick="document.location='adminuspsmeths.php?admin=3'" /></td>
				<td align="center" class="cobll"><input type="button" value="UPS Admin" onclick="document.location='adminuspsmeths.php?admin=4'" /></td>
				<td align="center" class="cobhl"><input type="button" value="Canada Post Admin" onclick="document.location='adminuspsmeths.php?admin=6'" /></td>
				<td align="center" class="cobll">&nbsp;</td>
			  </tr>
			  <tr>
				<td align="center" class="cobhl" height="22"><input type="button" value="<?php print $yyEdit.' '.$yyShpMet?>" onclick="document.location='adminuspsmeths.php?method=3'" /></td>
				<td align="center" class="cobll"><input type="button" value="<?php print $yyEdit.' '.$yyShpMet?>" onclick="document.location='adminuspsmeths.php?method=4'" /></td>
				<td align="center" class="cobhl"><input type="button" value="<?php print $yyEdit.' '.$yyShpMet?>" onclick="document.location='adminuspsmeths.php?method=6'" /></td>
				<td align="center" class="cobll"><input type="button" value="<?php print $yyEdit.' '.$yyShpMet?>" onclick="document.location='adminuspsmeths.php?method=7'" /></td>
			  </tr>
			  <tr>
                <td colspan="4" align="center" class="cobll"><br />&nbsp;</td>
			  </tr>
			</table>
			
			<br />&nbsp;<br />&nbsp;<br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;
			
				</td>
			  </tr>
			</table>
			<br />&nbsp;
<?php
}elseif(@$_POST["posted"]=="1" && $success){ ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="admin.php"><strong><?php print $yyClkHer?></strong></a>.<br /><br />&nbsp;
                </td>
			  </tr>
			</table>
<?php
}else{ ?>
		  <form method="post" action="adminuspsmeths.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="method" value="<?php print $method?>" />
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr> 
                <td width="100%" colspan="5" align="center"><br /><strong><?php print $yyUsUpd . " " . $shipmet . " " . $yyShpMet?>.</strong><br />&nbsp;</td>
			  </tr>
<?php	if(! $success){ ?>
			  <tr> 
                <td width="100%" colspan="5" align="center"><br /><span style="color:#FF0000"><?php print $errmsg?></span>
                </td>
			  </tr>
<?php	}
		$sSQL = "SELECT uspsID,uspsMethod,uspsShowAs,uspsUseMethod,uspsLocal,uspsFSA FROM uspsmethods ";
		if($shipType==3)
			$sSQL .= "WHERE uspsID<100 ";
		elseif($shipType==4)
			$sSQL .= "WHERE uspsID>100 AND uspsID<200 ";
		elseif($shipType==6)
			$sSQL .= "WHERE uspsID>200 AND uspsID<300 ";
		elseif($shipType==7)
			$sSQL .= "WHERE uspsID>300 AND uspsID<400 ";
		$sSQL .= "ORDER BY uspsLocal DESC, uspsID";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($shipType==3){
?>
			  <tr>
				<td colspan="5"><ul><li><span style="font-size:10px"><?php print $yyUSS1?></span></li>
				<li><span style="font-size:10px"><?php print $yyUSS2?> 
				<a href="http://www.usps.com">http://www.usps.com</a>.</span></li></ul></td>
			  </tr>
<?php		while($allmethods=mysql_fetch_assoc($result)){ ?>
			  <tr>
			    <td align="right"><?php print $yyUSPSMe?>:</td>
				<td align="left"><span style="font-size:10px;font-weight:bold"><?php
					if($allmethods['uspsID']=='1')
						print 'Express Mail';
					elseif($allmethods['uspsID']=='2')
						print 'Priority Mail';
					elseif($allmethods['uspsID']=='3')
						print 'Parcel Post';
					elseif($allmethods['uspsID']=='14')
						print 'Media Mail';
					elseif($allmethods['uspsID']=='15')
						print 'Bound Printed Matter';
					elseif($allmethods['uspsID']=='16')
						print 'First Class Mail';
					elseif($allmethods['uspsID']=='30')
						print 'Global Express Guaranteed Document';
					elseif($allmethods['uspsID']=='31')
						print 'Global Express Guaranteed Non-Document Rectangular';
					elseif($allmethods['uspsID']=='32')
						print 'Global Express Guaranteed Non-Document Non-Rectangular';
					elseif($allmethods['uspsID']=='33')
						print 'Express Mail International (EMS)';
					elseif($allmethods['uspsID']=='34')
						print 'Express Mail International (EMS) Flat Rate Envelope';
					elseif($allmethods['uspsID']=='35')
						print 'Priority Mail International';
					elseif($allmethods['uspsID']=='36')
						print 'Priority Mail International Flat Rate Envelope';
					elseif($allmethods['uspsID']=='37')
						print 'Priority Mail International Regular Flat-Rate Boxes';
					elseif($allmethods['uspsID']=='38')
						print 'First Class Mail International Letters';
					elseif($allmethods['uspsID']=='39')
						print 'First Class Mail International Large Envelope';
					elseif($allmethods['uspsID']=='40')
						print 'First Class Mail International Package';
					elseif($allmethods['uspsID']=='41')
						print 'Priority Mail International Large Flat-Rate Box';
					?></span></td>
				<td align="center"><?php print $yyUseMet?></td>
				<td align="center"><acronym title="<?php print $yyFSApp?>"><?php print $yyFSA?></acronym></td>
				<td align="center"><?php print $yyType?></td>
			  </tr>
			  <tr>
			    <td align="right"><?php print $yyShwAs?>:</td>
				<td align="left"><input type="text" name="methodshow<?php print $allmethods["uspsID"]?>" value="<?php print $allmethods["uspsShowAs"]?>" size="36" /></td>
				<td align="center"><input type="checkbox" name="methoduse<?php print $allmethods["uspsID"]?>" value="ON" <?php if((int)$allmethods["uspsUseMethod"]==1) print 'checked="checked"'?> /></td>
				<td align="center"><input type="checkbox" name="methodfsa<?php print $allmethods["uspsID"]?>" value="ON" <?php if((int)$allmethods["uspsFSA"]==1) print 'checked="checked"'?> /></td>
				<td align="center"><?php if($allmethods["uspsLocal"]==1) print '<span style="color:#FF0000">Domestic</span>'; else print '<span style="color:#0000FF">Internat.</span>';?></td>
			  </tr>
			  <tr>
				<td colspan="5" align="center"><hr width="80%" /></td>
			  </tr>
<?php		}
		}else{
			if($shipType==4){ ?>
			  <tr>
				<td colspan="5"><ul><li><span style="font-size:10px"><?php print $yyUSS3?></span></li>
				<li><span style="font-size:10px"><?php print str_replace("USPS","UPS",$yyUSS2)?> 
				<a href="http://www.ups.com">http://www.ups.com</a>.</span></li></ul></td>
			  </tr>
<?php		}else{ ?>
			  <tr>
				<td colspan="5"><ul><li><span style="font-size:10px">You can use this page to set which <?php print $shipmet?> shipping methods qualify for free shipping discounts by checking the FSA (Free Shipping Available) checkbox.</span></li>
				<li><span style="font-size:10px"><?php
			print str_replace("USPS",$shipmet,$yyUSS2);
			if($shipType==6){ ?>
				<a href="http://www.canadapost.ca">http://www.canadapost.ca</a>.
<?php		}else{ ?>
				<a href="http://www.fedex.com">http://www.fedex.com</a>.
<?php		} ?>
				</span></li>
				</ul></td>
			  </tr>
<?php		}
			while($allmethods=mysql_fetch_assoc($result)){ ?>
			  <tr>
			    <td align="right"><input type="hidden" name="methodshow<?php print $allmethods["uspsID"]?>" value="1" /><strong><?php print $yyShipMe?>:</strong></td>
				<td align="left"> <?php print $allmethods["uspsShowAs"]?></td>
				<td align="center"><strong><?php print ($shipType==4 || $shipType==7?$yyUseMet:"&nbsp;")?></strong></td>
				<td align="center"><acronym title="<?php print $yyFSApp?>"><?php print $yyFSA?></acronym></td>
				<td>&nbsp;</td>
			  </tr>
			  <tr>
				<td colspan="2">&nbsp;</td>
				<td align="center"><input type="<?php print ($shipType==4 || $shipType==7?"checkbox":"hidden")?>" name="methoduse<?php print $allmethods["uspsID"]?>" value="ON" <?php if((int)$allmethods["uspsUseMethod"]==1) print 'checked="checked"'?> /></td>
				<td align="center"><input type="checkbox" name="methodfsa<?php print $allmethods["uspsID"]?>" value="ON" <?php if((int)$allmethods["uspsFSA"]==1) print 'checked="checked"'?> /></td>
				<td>&nbsp;</td>
			  </tr>
			  <tr>
				<td colspan="5" align="center"><hr width="80%" /></td>
			  </tr>
<?php		}
		}
		mysql_free_result($result); ?>
			  <tr> 
                <td width="100%" colspan="5" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" /><br />&nbsp;</td>
			  </tr>
            </table>
		  </form>
<?php
} ?>