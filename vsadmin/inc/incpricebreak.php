<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if($_SESSION["loggedon"] != $storesessionvalue || @$disallowlogin==TRUE) exit;
$success=TRUE;
$maxcatsperpage = 100;
$maxpricebreaks = 25;
$sSQL = "";
$dropdown = (@$_POST["proddrop"]=="OK");
$alldata = "";
if(@$_POST["posted"]=="1"){
	if(@$_POST["act"]=="delete"){
		$sSQL = "DELETE FROM pricebreaks WHERE pbProdID='" . escape_string(@$_POST["id"]) . "'";
		mysql_query($sSQL) or print(mysql_error());
		print '<meta http-equiv="refresh" content="2; url=adminpricebreak.php?pg=' . @$_POST["pg"] . '">';
	}elseif(@$_POST["act"]=="domodify"){
		$theprod=trim($_POST["pid"]);
		$sSQL = "SELECT pID FROM products WHERE pID='" . str_replace("'","''",$theprod) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result)<=0){
			$success=FALSE;
			$errmsg = "The specified product id (" . $theprod . ") does not exist.";
		}
		mysql_free_result($result);
		if($success){
			mysql_query("DELETE FROM pricebreaks WHERE pbProdID='" . escape_string($theprod) . "'") or print(mysql_error());
			for($index=1; $index <= $maxpricebreaks; $index++){
				$thequant=trim(@$_POST["quant" . $index]);
				if(! is_numeric($thequant)) $thequant=0;
				$price=trim(@$_POST["price" . $index]);
				if(! is_numeric($price)) $price=0;
				$wprice=trim(@$_POST["wprice" . $index]);
				if(! is_numeric($wprice)) $wprice=0;
				if($thequant != 0 && ($price != 0 || $wprice != 0)){
					$sSQL = "INSERT INTO pricebreaks (pbProdID,pbQuantity,pPrice,pWholesalePrice) VALUES ('" . escape_string($theprod) . "',";
					$sSQL .= $thequant . ",";
					$sSQL .= $price . ",";
					$sSQL .= $wprice . ")";
					mysql_query($sSQL) or print(mysql_error());
				}
			}
			print '<meta http-equiv="refresh" content="2; url=adminpricebreak.php?pg=' . @$_POST["pg"] . '">';
		}
	}elseif(@$_POST["act"]=="doaddnew"){
		$theprod=trim($_POST["pid"]);
		$sSQL = "SELECT pbProdID FROM pricebreaks WHERE pbProdID='" . str_replace("'","''",$theprod) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result)>0){
			$success=FALSE;
			$errmsg = 'Price breaks already exist for this product id. You should use the "Modify" option on the price breaks admin page';
		}
		mysql_free_result($result);
		$sSQL = "SELECT pID FROM products WHERE pID='" . str_replace("'","''",$theprod) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result)<=0){
			$success=FALSE;
			$errmsg = "The specified product id (" . $theprod . ") does not exist.";
		}
		mysql_free_result($result);
		if($success){
			for($index=1; $index <= $maxpricebreaks; $index++){
				$thequant=trim(@$_POST["quant" . $index]);
				if(! is_numeric($thequant)) $thequant=0;
				$price=trim(@$_POST["price" . $index]);
				if(! is_numeric($price)) $price=0;
				$wprice=trim(@$_POST["wprice" . $index]);
				if(! is_numeric($wprice)) $wprice=0;
				if($thequant != 0 && ($price != 0 || $wprice != 0)){
					$sSQL = "INSERT INTO pricebreaks (pbProdID,pbQuantity,pPrice,pWholesalePrice) VALUES ('" . escape_string($theprod) . "',";
					$sSQL .= $thequant . ",";
					$sSQL .= $price . ",";
					$sSQL .= $wprice . ")";
					mysql_query($sSQL) or print(mysql_error());
				}
			}
			print '<meta http-equiv="refresh" content="2; url=adminpricebreak.php?pg=' . @$_POST["pg"] . '">';
		}
	}
}
?>
<script language="javascript" type="text/javascript">
<!--
function formvalidator(theForm){
<?php if($dropdown){ ?>
  if (theForm.pid.selectedIndex == 0){
    alert("<?php print $yyPlsSel?> \"<?php print $yyPrId?>\".");
<?php }else{ ?>
  if (theForm.pid.value == ""){
    alert("<?php print $yyPlsEntr?> \"<?php print $yyPrId?>\".");
<?php } ?>
    theForm.pid.focus();
    return (false);
  }
  return (true);
}
//-->
</script>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
<?php
if(@$_POST["posted"]=="1" && (@$_POST["act"]=="modify" || @$_POST["act"]=="clone")){ ?>
        <tr>
		  <td width="100%" align="center">
		  <form name="mainform" method="post" action="adminpricebreak.php" onsubmit="return formvalidator(this)">
			<input type="hidden" name="posted" value="1" />
			<?php if($_POST["act"]=="clone"){ ?>
			<input type="hidden" name="act" value="doaddnew" />
			<?php }else{ ?>
			<input type="hidden" name="act" value="domodify" />
			<input type="hidden" name="pid" value="<?php print @$_POST["id"]?>" />
			<?php } ?>
            <input type="hidden" name="pg" value="<?php print @$_POST["pg"]?>" />
			<table width="320" border="0" cellspacing="0" cellpadding="1">
			  <tr> 
                <td colspan="3" align="center"><strong><?php print $yyPBKAdm?></strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td colspan="3" align="center"><strong><?php print $yyPBFID?>:</strong> <?php
				if($dropdown && $_POST["act"]=="clone"){
					print '<select size="1" name="pid"><option value="">' . $yySelect . "</option>";
					$sSQL = "SELECT pID FROM products LEFT JOIN pricebreaks ON products.pID=pricebreaks.pbProdID WHERE pbProdID IS NULL ORDER BY pID";
					$result = mysql_query($sSQL) or print(mysql_error());
					while($rs = mysql_fetch_assoc($result))
						print '<option value="' . $rs["pID"] . '">' . $rs["pID"] . "</option>\r\n";
					mysql_free_result($result);
					print "</select>";
				}elseif($_POST["act"]=="clone"){
					print '<input type="text" name="pid" size="20" />';
				}else{
					print $_POST["id"];
				} ?></td>
			  </tr>
			  <tr>
				<td align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyQuaFro?></span></strong></td>
				<td align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyPrPri?></span></strong></td>
				<td align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyWhoPri?></span></strong></td>
			  </tr>
<?php		$sSQL = "SELECT pbQuantity,pPrice,pWholesalePrice FROM pricebreaks WHERE pbProdID='" . trim(str_replace("'","''",$_POST["id"])) . "' ORDER BY pbQuantity";
			$index=1;
			$result = mysql_query($sSQL) or print(mysql_error());
			while($rs = mysql_fetch_assoc($result)){ ?>
			  <tr>
				<td align="center"><input type="text" name="quant<?php print $index?>" size="12" value="<?php print $rs["pbQuantity"]?>" /></td>
				<td align="center"><input type="text" name="price<?php print $index?>" size="12" value="<?php print $rs["pPrice"]?>" /></td>
				<td align="center"><input type="text" name="wprice<?php print $index?>" size="12" value="<?php print $rs["pWholesalePrice"]?>" /></td>
			  </tr>
<?php			$index++;
			}
			mysql_free_result($result);
			for($index2=$index; $index2 < $maxpricebreaks; $index2++){ ?>
			  <tr>
				<td align="center"><input type="text" name="quant<?php print $index2?>" size="12" value="" /></td>
				<td align="center"><input type="text" name="price<?php print $index2?>" size="12" value="" /></td>
				<td align="center"><input type="text" name="wprice<?php print $index2?>" size="12" value="" /></td>
			  </tr>
<?php		} ?>
			  <tr>
                <td width="100%" colspan="3" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" /></td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="3" align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
<?php
}elseif(@$_POST["posted"]=="1" && @$_POST["act"]=="addnew"){ ?>
        <tr>
		  <td width="100%" align="center">
		  <form name="mainform" method="post" action="adminpricebreak.php" onsubmit="return formvalidator(this)">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="doaddnew" />
            <input type="hidden" name="pg" value="<?php print @$_POST["pg"]?>" />
			<table width="320" border="0" cellspacing="0" cellpadding="1">
			  <tr> 
                <td colspan="3" align="center"><strong><?php print $yyPBKAdm?></strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td colspan="3" align="center"><strong><?php print $yyPBFID?>:</strong> <?php
				if($dropdown){
					print '<select size="1" name="pid"><option value="">' . $yySelect . "</option>";
					$sSQL = "SELECT pID FROM products LEFT JOIN pricebreaks ON products.pID=pricebreaks.pbProdID WHERE pbProdID IS NULL ORDER BY pID";
					$result = mysql_query($sSQL) or print(mysql_error());
					while($rs = mysql_fetch_assoc($result))
						print '<option value="' . $rs["pID"] . '">' . $rs["pID"] . "</option>\r\n";
					mysql_free_result($result);
					print "</select>";
				}else{
					print '<input type="text" name="pid" size="20" />';
				} ?></td>
			  </tr>
			  <tr>
				<td align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyQuaFro?></span></strong></td>
				<td align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyPrPri?></span></strong></td>
				<td align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyWhoPri?></span></strong></td>
			  </tr>
<?php		for($index=1; $index < $maxpricebreaks; $index++){ ?>
			  <tr>
				<td align="center"><input type="text" name="quant<?php print $index?>" size="12" value="" /></td>
				<td align="center"><input type="text" name="price<?php print $index?>" size="12" value="" /></td>
				<td align="center"><input type="text" name="wprice<?php print $index?>" size="12" value="" /></td>
			  </tr>
<?php		} ?>
			  <tr>
                <td width="100%" colspan="3" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" /></td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="3" align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
<?php
}elseif(@$_POST["posted"]=="1" && $success){ ?>
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="adminpricebreak.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />&nbsp;</td>
			  </tr>
			</table></td>
        </tr>
<?php
}elseif(@$_POST["posted"]=="1"){ ?>
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table></td>
        </tr>
<?php
}else{ ?>
        <tr>
		  <td width="100%">
<script language="javascript" type="text/javascript">
<!--
function checkcontrol(evt){
<?php if(strstr(@$_SERVER['HTTP_USER_AGENT'], 'Gecko')){ ?>
if(evt.ctrlKey || evt.altKey){
document.mainform.proddrop.checked=true;
}
<?php }else{ ?>
theevnt=window.event;
if(theevnt.ctrlKey){
document.mainform.proddrop.checked=true;
}
<?php } ?>
return(true);
}
function mrk(id) {
	document.mainform.id.value = id;
	document.mainform.act.value = "modify";
	document.mainform.submit();
}
function newrec(evt) {
	checkcontrol(evt);
	document.mainform.act.value = "addnew";
	document.mainform.submit();
}
function crk(id, evt) {
	checkcontrol(evt);
	document.mainform.id.value = id;
	document.mainform.act.value = "clone";
	document.mainform.submit();
}
function drk(id) {
cmsg = "<?php print $yyConDel?>\n"
if (confirm(cmsg)) {
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.submit();
}
}
// -->
</script>
		  <form name="mainform" method="post" action="adminpricebreak.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="pg" value="<?php print @$_GET["pg"]?>" />
			<input type="hidden" name="selectedq" value="1" />
			<input type="hidden" name="newval" value="1" />
            <table width="100%" border="0" cellspacing="0" cellpadding="1">
			  <tr> 
                <td width="100%" colspan="5" align="center"><strong><?php print $yyPBKAdm?></strong><br />&nbsp;</td>
			  </tr>
<?php
	if(! is_numeric(@$_GET["pg"]))
		$CurPage = 1;
	else
		$CurPage = (int)(@$_GET["pg"]);
	$sSQL = "SELECT COUNT(DISTINCT pbProdID) AS bar FROM pricebreaks";
	$result = mysql_query($sSQL) or print(mysql_error());
	$numids = mysql_result($result,0,"bar");
	$iNumOfPages = ceil($numids/$maxcatsperpage);
	mysql_free_result($result);
	$sSQL = "SELECT DISTINCT pbProdID,pName FROM pricebreaks INNER JOIN products ON pricebreaks.pbProdID=products.pID ORDER BY pbProdID LIMIT " . ($maxcatsperpage*($CurPage-1)) . ", $maxcatsperpage";
	$result = mysql_query($sSQL) or print(mysql_error());
	if($numids > 0){
		$islooping=FALSE;
		$noproducts=FALSE;
		$hascatinprodsection=FALSE;
		$rowcounter=0;
		$bgcolor="";
		if($iNumOfPages > 1) print '<tr><td align="center" colspan="5">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,'<a href="adminpricebreak.php?pg=',FALSE) . '<br /><br /></td></tr>';
?>
			  <tr>
				<td align="left"><strong><?php print $yyPrId?></strong> <input type="checkbox" name="proddrop" value="OK" /></td>
				<td align="left"><strong><?php print $yyPrName?></strong></td>
				<td width="5%" align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyClone?></span></td>
				<td width="5%" align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyModify?></span></td>
				<td width="5%" align="center"><span style="font-size:10px;font-weight:bold"><?php print $yyDelete?></span></td>
			  </tr>
<?php	while($rs = mysql_fetch_assoc($result)){
			if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark'; ?>
<tr class="<?php print $bgcolor?>">
<td><?php print $rs["pbProdID"]?></td>
<td><?php print $rs["pName"]?></td>
<td><input type="button" value="<?php print $yyClone?>" onclick="crk('<?php print str_replace("'","\'",$rs["pbProdID"])?>', event)" /></td>
<td><input type="button" value="<?php print $yyModify?>" onclick="mrk('<?php print str_replace("'","\'",$rs["pbProdID"])?>')" /></td>
<td><input type="button" value="<?php print $yyDelete?>" onclick="drk('<?php print str_replace("'","\'",$rs["pbProdID"])?>')" /></td>
</tr><?php	$rowcounter++;
		}
		if($iNumOfPages > 1) print '<tr><td align="center" colspan="5"><br />' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,'<a href="adminpricebreak.php?pg=',FALSE) . '</td></tr>';
	}else{
?>
			  <tr><td width="100%" colspan="5" align="center"><br /> <input type="checkbox" name="proddrop" value="OK" /> <strong><?php print $yyNoPBK?><br />&nbsp;</td></tr>
<?php
	}
	mysql_free_result($result);
?>
			  <tr> 
                <td width="100%" colspan="5" align="center"><br /><strong><?php print $yyPBKNew?></strong>&nbsp;&nbsp;<input type="button" value="<?php print $yyNewPBK?>" onclick="newrec(event)" /><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="5" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />
				<img src="../images/clearpixel.gif" width="300" height="3" alt="" /></td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
<?php
}
?>
      </table>