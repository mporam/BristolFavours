<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(trim(@$_POST['sessionid']) != '')
	$thesessionid = trim(@$_POST['sessionid']);
else
	$thesessionid = getsessionid();
$thesessionid = str_replace("'",'',$thesessionid);
if(@$incfunctionsdefined==TRUE){
	$alreadygotadmin = getadminsettings();
}else{
	$sSQL = 'SELECT countryLCID,countryCurrency,adminStoreURL FROM admin INNER JOIN countries ON admin.adminCountry=countries.countryID WHERE adminID=1';
	$result = mysql_query($sSQL) or print(mysql_error());
	$rs = mysql_fetch_array($result);
	$adminLocale = $rs['countryLCID'];
	$storeurl = $rs['adminStoreURL'];
	if((substr(strtolower($storeurl),0,7) != 'http://') && (substr(strtolower($storeurl),0,8) != 'https://'))
		$storeurl = 'http://' . $storeurl;
	if(substr($storeurl,-1) != '/') $storeurl .= '/';
	mysql_free_result($result);
}
if(@$_POST['mode']!='checkout'){
	$sSQL = "SELECT rvProdName,rvProdURL,sectionName FROM recentlyviewed INNER JOIN sections ON recentlyviewed.rvProdSection=sections.sectionID WHERE rvProdID<>'".escape_string(@$prodid)."' AND " . (@$_SESSION['clientID']!='' ? 'rvCustomerID=' . escape_string(@$_SESSION['clientID']) : "(rvCustomerID=0 AND rvSessionID='".$thesessionid."')").' ORDER BY rvDate DESC';
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result)>0){ ?>
      <table class="mincart" width="130" bgcolor="#FFFFFF">
        <tr> 
          <td class="mincart" bgcolor="#F0F0F0" align="center"><img src="images/recentview.gif" align="top" width="16" height="15" alt="<?php print $xxRecVie?>" />
            &nbsp;<strong><a class="ectlink mincart" href="<?php print $storeurl?>cart.php"><?php print $xxRecVie?></a></strong></td>
        </tr>
<?php	while($rs = mysql_fetch_assoc($result)){ ?>
         <tr><td class="mincart" bgcolor="#F0F0F0" align="center">
		<span style="font-family:Verdana">&raquo;</span> <?php print $rs['sectionName']?><br />
		<a class="ectlink mincart" href="<?php print $storeurl.$rs['rvProdURL']?>"><?php print $rs['rvProdName']?></a></td></tr>
<?php	} ?>
      </table>
<?php
	}
	mysql_free_result($result);
}
?>