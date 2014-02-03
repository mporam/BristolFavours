<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if($_SESSION["loggedon"] != $storesessionvalue || @$disallowlogin==TRUE) exit;
$success=TRUE;
$sSQL = "";
$alldata="";
$alreadygotadmin = getadminsettings();
$errmsg='';
$resultcounter=0;
$dorefresh=FALSE;
if(@$htmlemails==TRUE) $emlNl = '<br />'; else $emlNl="\n";
function dodeleteoption($oid){
	global $success,$yyPOUse,$errmsg;
	$sSQL = "SELECT poID,poProdID FROM prodoptions INNER JOIN products ON prodoptions.poProdID=products.pID WHERE poOptionGroup=" . $oid;
	$result = mysql_query($sSQL) or print(mysql_error());
	if($rs = mysql_fetch_assoc($result)){
		$success=FALSE;
		$errmsg .= $yyPOUse . '<br />(' . $rs['poProdID'] . ')<br />';
	}else{
		mysql_query("DELETE FROM options WHERE optGroup=" . $oid) or print(mysql_error());
		mysql_query("DELETE FROM optiongroup WHERE optGrpID=" . $oid) or print(mysql_error());
		mysql_query("DELETE FROM prodoptions WHERE poOptionGroup=" . $oid) or print(mysql_error());
	}
	mysql_free_result($result);
	return($success);
}
function checknotifystock($theoid){
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
if(@$_POST["posted"]=="1"){
	if(@$_POST["act"]=="delete"){
		if(dodeleteoption($_POST['id']))
			$dorefresh=TRUE;
		else
			$errmsg = $yyPOErr . "<br />" . $errmsg;
	}elseif(@$_POST['act']=='quickupdate'){
		foreach(@$_POST as $objItem => $objValue){
			if(substr($objItem, 0, 4)=='pra_'){
				$theid = str_replace('ect_dot_xzq','.',substr($objItem, 4));
				$theval = trim(unstripslashes($objValue));
				$pract = @$_POST['pract'];
				$sSQL = '';
				if($pract=='del'){
					if($theval=='del') dodeleteoption($theid);
					$sSQL = '';
				}elseif($pract=='own'){
					$sSQL = "UPDATE optiongroup SET optGrpWorkingName='" . escape_string($theval) . "'";
				}elseif($pract=='opn'){
					$sSQL = "UPDATE optiongroup SET optGrpName='" . escape_string($theval) . "'";
				}elseif($pract=='opn2'){
					$sSQL = "UPDATE optiongroup SET optGrpName2='" . escape_string($theval) . "'";
				}elseif($pract=='opn3'){
					$sSQL = "UPDATE optiongroup SET optGrpName3='" . escape_string($theval) . "'";
				}
				if($sSQL!=''){
					$sSQL .= " WHERE optGrpID='".escape_string($theid)."'";
					mysql_query($sSQL) or print(mysql_error());
				}
			}
		}
		if($success) $dorefresh=TRUE; else $errmsg = $yyPOErr . '<br />' . $errmsg;
	}elseif(@$_POST['act']=='domodify' || @$_POST['act']=='doaddnew'){
		$sSQL = "";
		$bOption=FALSE;
		$maxoptnumber = @$_POST['maxoptnumber'];
		$optFlags = 0;
		if(@$_POST['pricepercent']=='1') $optFlags=1;
		if(@$_POST['weightpercent']=='1') $optFlags += 2;
		if(@$_POST['singleline']=='1') $optFlags += 4;
		if(@$_POST['optdefault']!='') $optDefault=(int)@$_POST['optdefault']; else $optDefault=-1;
		for($rowcounter=0; $rowcounter < $maxoptnumber; $rowcounter++){
			if(trim(@$_POST['opt' . $rowcounter]) != '') $bOption=TRUE;
			$aOption[$rowcounter][0]=escape_string(unstripslashes(@$_POST['opt' . $rowcounter]));
			for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 32)==32)
					$aOption[$rowcounter][7+$index]=escape_string(unstripslashes(@$_POST['opl' . $index . 'x' . $rowcounter]));
			}
			if(is_numeric(trim(@$_POST['pri' . $rowcounter])))
				$aOption[$rowcounter][1]=trim(@$_POST['pri' . $rowcounter]);
			else
				$aOption[$rowcounter][1]=0;
			if(is_numeric(trim(@$_POST['wsp' . $rowcounter])))
				$aOption[$rowcounter][4]=trim(@$_POST['wsp' . $rowcounter]);
			else
				$aOption[$rowcounter][4]=0;
			if(is_numeric(trim(@$_POST['wei' . $rowcounter])))
				$aOption[$rowcounter][2]=trim(@$_POST['wei' . $rowcounter]);
			else
				$aOption[$rowcounter][2]=0;
			if(is_numeric(trim(@$_POST['optStock' . $rowcounter])))
				$aOption[$rowcounter][3]=trim(@$_POST['optStock' . $rowcounter]);
			else
				$aOption[$rowcounter][3]=0;
			$aOption[$rowcounter][5]=escape_string(unstripslashes(@$_POST['regexp' . $rowcounter]));
			$aOption[$rowcounter][6]=trim(@$_POST['orig' . $rowcounter]);
			$aOption[$rowcounter][7]=escape_string(unstripslashes(@$_POST['altimg' . $rowcounter]));
			$aOption[$rowcounter][8]=escape_string(unstripslashes(@$_POST['altlimg' . $rowcounter]));
		}
		if((trim(@$_POST['secname'])=='' || ! $bOption) && @$_POST['optType'] != '3'){
			$success=FALSE;
			$errmsg = $yyPOErr . '<br />';
			$errmsg .= $yyPOOne;
		}else{
			if(@$_POST['optType']=='3'){ // Text option
				$fieldDims = trim(@$_POST['pri0']) . '.';
				if((int)@$_POST['fieldheight'] < 10) $fieldDims .= '0';
				$fieldDims .= trim(@$_POST['fieldheight']);
				$optTxtCharge = @$_POST['optTxtCharge'];
				if(! is_numeric($optTxtCharge)) $optTxtCharge=0;
				if(@$_POST['act']=='doaddnew'){
					$sSQL = "INSERT INTO optiongroup (optGrpName,";
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL .= "optGrpName" . $index . ",";
					}
					$sSQL .= "optType,optTxtMaxLen,optTxtCharge,optMultiply,optAcceptChars,optGrpWorkingName,optFlags) VALUES (";
					$sSQL .= "'" . escape_string(unstripslashes(@$_POST["secname"])) . "',";
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL .= "'" . escape_string(unstripslashes(@$_POST["secname" . $index])) . "',";
					}
					if(trim(@$_POST["forceselec"])=="ON") $sSQL .= "'3',"; else $sSQL .= "'-3',";
					$sSQL.=$_POST['optTxtMaxLen'].','.($_POST['iscostperentry']=='1'? 0-$optTxtCharge : $optTxtCharge);
					$sSQL.=',' . (@$_POST['optMultiply']=="ON" ? 1 : 0) . ",'" . escape_string($_POST['optAcceptChars']) . "'";
					if(trim(@$_POST["workingname"])=="")
						$sSQL .= ",'" . escape_string(unstripslashes(@$_POST["secname"]));
					else
						$sSQL .= ",'" . escape_string(unstripslashes(@$_POST["workingname"]));
					$sSQL .= "'," . $optFlags . ")";
					mysql_query($sSQL) or print(mysql_error());
					$iID  = mysql_insert_id();
					$sSQL = "INSERT INTO options (optGroup,optName,optPriceDiff";
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL .= ",optName" . $index;
					}
					$sSQL .= ",optWeightDiff) VALUES (" . $iID . ",'" . escape_string(unstripslashes(@$_POST["opt0"])) . "'," . $fieldDims;
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL .= ",'" . escape_string(unstripslashes(@$_POST["opl" . $index . "x0"])) . "'";
					}
					$sSQL .= ",0)";
					mysql_query($sSQL) or print(mysql_error());
				}else{
					$iID = @$_POST["id"];
					$sSQL = "UPDATE optiongroup SET optGrpName='" . escape_string(unstripslashes(@$_POST["secname"])) . "'";
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL .= ",optGrpName" . $index . "='" . escape_string(unstripslashes(@$_POST["secname" . $index])) . "'";
					}
					if(trim(@$_POST["forceselec"])=="ON")
						$sSQL .= ",optType='3'";
					else
						$sSQL .= ",optType='-3'";
					$sSQL .= ",optTxtMaxLen=" . @$_POST['optTxtMaxLen'];
					$sSQL .= ",optTxtCharge=" . (@$_POST['iscostperentry']=='1' ? 0-$optTxtCharge : $optTxtCharge);
					$sSQL .= ",optMultiply=" . (@$_POST['optMultiply']=='ON' ? 1 : 0);
					$sSQL .= ",optAcceptChars='" . @$_POST['optAcceptChars'] . "'";
					if(trim(@$_POST["workingname"])=="")
						$sSQL .= ",optGrpWorkingName='" . escape_string(unstripslashes(@$_POST["secname"])) . "',";
					else
						$sSQL .= ",optGrpWorkingName='" . escape_string(unstripslashes(@$_POST["workingname"])) . "',";
					$sSQL .= "optFlags=" . $optFlags;
					$sSQL .= " WHERE optGrpID=" . $iID;
					mysql_query($sSQL) or print(mysql_error());
					$sSQL = "UPDATE options SET optName='" . escape_string(unstripslashes(@$_POST["opt0"])) . "',optPriceDiff=" . $fieldDims;
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL .= ",optName" . $index . "='" . escape_string(unstripslashes(@$_POST["opl" . $index . "x0"])) . "'";
					}
					$sSQL .= " WHERE optGroup=" . $iID;
					mysql_query($sSQL) or print(mysql_error());
				}
			}else{ // Non-text Option
				if(@$_POST['act']=='doaddnew'){
					$sSQL = 'INSERT INTO optiongroup (optGrpName';
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL .= ',optGrpName' . $index;
					}
					$sSQL .= ',optType,optGrpWorkingName,optFlags,optGrpSelect) VALUES (';
					$sSQL .= "'" . escape_string(unstripslashes(@$_POST['secname'])) . "',";
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL .= "'" . escape_string(unstripslashes(@$_POST['secname' . $index])) . "',";
					}
					if(trim(@$_POST['forceselec'])=='ON')
						$sSQL .= "'".@$_POST['optType']."',";
					else
						$sSQL .= "'".(0-(int)@$_POST['optType'])."',";
					if(trim(@$_POST['workingname'])=='')
						$sSQL .= "'" . escape_string(unstripslashes(@$_POST['secname']));
					else
						$sSQL .= "'" . escape_string(unstripslashes(@$_POST['workingname']));
					$sSQL .= "'," . $optFlags . ',' . (trim(@$_POST['optgrpselect'])=='1' ? 1 : 0) . ')';
					mysql_query($sSQL) or print(mysql_error());
					$iID  = mysql_insert_id();
				}else{
					$iID = @$_POST["id"];
					$sSQL = "UPDATE optiongroup SET optGrpName='" . escape_string(unstripslashes(@$_POST["secname"])) . "'";
					for($index=2; $index <= $adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16)
							$sSQL .= ",optGrpName" . $index . "='" . escape_string(unstripslashes(@$_POST["secname" . $index])) . "'";
					}
					if(trim(@$_POST["forceselec"])=="ON")
						$sSQL .= ",optType='".@$_POST['optType']."'";
					else
						$sSQL .= ",optType='".(0-(int)@$_POST['optType'])."'";
					if(trim(@$_POST["workingname"])=="")
						$sSQL .= ",optGrpWorkingName='" . escape_string(unstripslashes(@$_POST["secname"])) . "',";
					else
						$sSQL .= ",optGrpWorkingName='" . escape_string(unstripslashes(@$_POST["workingname"])) . "',";
					$sSQL .= "optFlags=" . $optFlags;
					$sSQL .= ",optGrpSelect=" . (trim(@$_POST['optgrpselect'])=='1' ? 1 : 0);
					$sSQL .= " WHERE optGrpID=" . $iID;
					mysql_query($sSQL) or print(mysql_error());
				}
				for($rowcounter=0; $rowcounter < $maxoptnumber; $rowcounter++){
					if(trim($aOption[$rowcounter][0]) != ""){
						if($aOption[$rowcounter][6] != ''){
							$sSQL = "UPDATE options SET optName='" . $aOption[$rowcounter][0] . "',optRegExp='" . $aOption[$rowcounter][5] . "',optAltImage='" . $aOption[$rowcounter][7] . "',optAltLargeImage='" . $aOption[$rowcounter][8] . "',optPriceDiff=" . $aOption[$rowcounter][1] . ",optWeightDiff=" . $aOption[$rowcounter][2] . ",optStock=" . $aOption[$rowcounter][3];
							if(@$wholesaleoptionpricediff==TRUE) $sSQL .= ",optWholesalePriceDiff=" . $aOption[$rowcounter][4];
							for($index=2; $index <= $adminlanguages+1; $index++){
								if(($adminlangsettings & 32)==32)
									$sSQL .= ",optName" . $index . "='" . $aOption[$rowcounter][7+$index] . "'";
							}
							$sSQL .= ',optDefault=' . ($rowcounter==$optDefault ? '1' : '0');
							$sSQL .= " WHERE optID=" . $aOption[$rowcounter][6];
							mysql_query($sSQL) or print(mysql_error());
							if($aOption[$rowcounter][3])
								checknotifystock($aOption[$rowcounter][6]);
						}else{
							$sSQL = "INSERT INTO options (optGroup,optName,optRegExp,optAltImage,optAltLargeImage,optPriceDiff,optWeightDiff,optStock,optDefault";
							if(@$wholesaleoptionpricediff==TRUE) $sSQL .= ",optWholesalePriceDiff";
							for($index=2; $index <= $adminlanguages+1; $index++){
								if(($adminlangsettings & 32)==32) $sSQL .= ",optName" . $index;
							}
							$sSQL .= ") VALUES (" . $iID . ",'" . $aOption[$rowcounter][0] . "','" . $aOption[$rowcounter][5] . "','" . $aOption[$rowcounter][7] . "','" . $aOption[$rowcounter][8] . "'," . $aOption[$rowcounter][1] . "," . $aOption[$rowcounter][2] . "," . $aOption[$rowcounter][3] . ',' . ($rowcounter==$optDefault ? '1' : '0');
							if(@$wholesaleoptionpricediff==TRUE) $sSQL .= "," . $aOption[$rowcounter][4];
							for($index=2; $index <= $adminlanguages+1; $index++){
								if(($adminlangsettings & 32)==32) $sSQL .= ",'" . $aOption[$rowcounter][7+$index] ."'";
							}
							$sSQL .= ")";
							mysql_query($sSQL) or print(mysql_error());
						}
					}else{
						if($aOption[$rowcounter][6] != ''){
							$sSQL = "DELETE FROM options WHERE optID='" . $aOption[$rowcounter][6] . "'";
							mysql_query($sSQL) or print(mysql_error());
						}
					}
				}
			}
		}
		if($success)
			$dorefresh=TRUE;
	}
}elseif(@$_GET['pract']!=''){
	setcookie('practopt', @$_GET['pract'], time()+31536000, '/', '', @$_SERVER['HTTPS']=='on');
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=adminprodopts.php';
	print '?disp=' . @$_POST['disp'] . '&stext=' . urlencode(@$_POST['stext']) . '&stype=' . @$_POST['stype'] . '&pg=1'; // . @$_POST['pg'];
	print '">';
}
?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function formvalidator(theForm){
	var maxrow = document.getElementById("maxoptnumber").value;
	if(theForm.secname.value==""){
		alert("<?php print $yyPlsEntr?> \"<?php print $yyPOName?>\".");
		theForm.secname.focus();
		return (false);
	}
	for(index=0;index<maxrow;index++){
		document.getElementById("regexp" + index).disabled=false;
		document.getElementById("optStock" + index).disabled=false;
	}
	return (true);
}
function changeunits(){
	var nopercentchar="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	var maxrow = document.getElementById("maxoptnumber").value;
	for(index=0;index<maxrow;index++){
		wel = document.getElementById("wunitspan" + index);
		pel = document.getElementById("punitspan" + index);
		if(document.forms.mainform.weightpercent.checked){
			wel.innerHTML='&nbsp;%&nbsp;';
		}else{
			wel.innerHTML=nopercentchar;
		}
		if(document.forms.mainform.pricepercent.checked){
			pel.innerHTML='&nbsp;%&nbsp;';
		}else{
			pel.innerHTML='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}
	}
}
function doswitcher(){
	var maxrow = document.getElementById("maxoptnumber").value;
	var switcher = document.getElementById("switcher");
	if(switcher.selectedIndex==0){
		doswon='block';
		doswoff='none';
	}else if(switcher.options[1].disabled){
		switcher.selectedIndex=0;
		return;
	}else{
		doswon='none';
		doswoff='block';
	}
	for(index=-1;index<maxrow;index++){
		if(index==-1)theindex='';else theindex=index;
		document.getElementById("swprdiff" + theindex).style.display=doswon;
		document.getElementById("swaltid" + theindex).style.display=doswoff;
		document.getElementById("swwtdiff" + theindex).style.display=doswon;
		document.getElementById("swaltimg" + theindex).style.display=doswoff;
		document.getElementById("swstk" + theindex).style.display=doswon;
		document.getElementById("swaltlgim" + theindex).style.display=doswoff;
		if(index>=0){
			hasaltid = (document.getElementById("regexp" + theindex).value.replace(/ /,'')!='');
			document.getElementById("optStock" + theindex).disabled=hasaltid;
		}
	}
}
<?php	if(@$adminlanguages>1 && (($adminlangsettings & 32)==32)){ ?>
function doswitchlang(){
var langid = document.getElementById("langid");
var theid = langid[langid.selectedIndex].value;
var maxrow = document.getElementById("maxoptnumber").value;
for(index=0;index<maxrow;index++){
<?php		for($index=2; $index <= $adminlanguages+1; $index++){ ?>
document.getElementById("lang<?php print $index?>x" + index).style.display='none';
<?php		} ?>
}
for(index=0;index<maxrow;index++){
document.getElementById("lang" + theid + "x" + index).style.display='block';
}
}
<?php	} ?>
function doaddrow(){
var rownumber = document.getElementById("maxoptnumber").value;
opttable = document.getElementById('optiontable');
newrow = opttable.insertRow(opttable.rows.length);
if((parseInt(rownumber)%2)==0)newrow.style.backgroundColor='#E7EAEF';
newcell = newrow.insertCell(0);
newcell.innerHTML = '<input type="radio" name="optdefault" value="'+rownumber+'" />';

newcell = newrow.insertCell(1);
newcell.innerHTML = '<input type="button" id="insertopt'+rownumber+'" value="+" onclick="insertoption(this)" />';

newcell = newrow.insertCell(2);
newcell.align='center';
newcell.innerHTML = '<input type="text" name="opt'+rownumber+'" id="opt'+rownumber+'" size="20" value="" />';

newcell = newrow.insertCell(3);
newcell.innerHTML = '<strong>&raquo;</strong>';
					
<?php
	$extracells=0;
	if($adminlanguages>=1 && ($adminlangsettings & 32)==32){
		$extracells=2;
		$langtext = '';
		for($index=2; $index <= $adminlanguages+1; $index++){
			$langtext .= '<span id="lang'.$index.'x\'+rownumber+\'"';
			if($index>2) $langtext .= ' style="display:none">'; else $langtext .= '>';
			$langtext .= '<input type="text" name="opl'.$index.'x\'+rownumber+\'" id="opl'.$index.'x\'+rownumber+\'" size="20" /></span>';
		} ?>
newcell = newrow.insertCell(4);
newcell.align='center';
newcell.innerHTML = '<?php print $langtext?>';

newcell = newrow.insertCell(5);
newcell.innerHTML = '<strong>&raquo;</strong>';
<?php
	}

$langtext = '<span id="swprdiff\'+rownumber+\'">';
$langtext .= '&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="pri\'+rownumber+\'" id="pri\'+rownumber+\'" size="5" />';
if(@$wholesaleoptionpricediff==TRUE){
	$langtext .= ' / <input type="text" name="wsp\'+rownumber+\'" id="wsp\'+rownumber+\'" size="5" />';
}
$langtext .= '<span id="punitspan\'+rownumber+\'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
$langtext .= '</span><span id="swaltid\'+rownumber+\'" style="display:none"><input type="text" name="regexp\'+rownumber+\'" id="regexp\'+rownumber+\'" size="12" /></span>';
?>
newcell = newrow.insertCell(<?php print (4+$extracells)?>);
newcell.align='center';
newcell.innerHTML = '<?php print $langtext?>';

newcell = newrow.insertCell(<?php print (5+$extracells)?>);
newcell.innerHTML = '<strong>&raquo;</strong>';

<?php
$langtext = '<span id="swwtdiff\'+rownumber+\'">';
$langtext .= '&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="wei\'+rownumber+\'" id="wei\'+rownumber+\'" size="5" /><span id="wunitspan\'+rownumber+\'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
$langtext .= '</span><span id="swaltimg\'+rownumber+\'" style="display:none"><input type="text" name="altimg\'+rownumber+\'" id="altimg\'+rownumber+\'" size="20" /></span>';
?>
newcell = newrow.insertCell(<?php print (6+$extracells)?>);
newcell.align='center';
newcell.innerHTML = '<?php print $langtext?>';

newcell = newrow.insertCell(<?php print (7+$extracells)?>);
newcell.innerHTML = '<strong>&raquo;</strong>';

<?php
$langtext = '<span id="swstk\'+rownumber+\'">';
if(@$useStockManagement)
	$langtext .= '<input type="text" name="optStock\'+rownumber+\'" id="optStock\'+rownumber+\'" size="4" />';
else
	$langtext .= '<input type="hidden" name="optStock\'+rownumber+\'" id="optStock\'+rownumber+\'" />n/a';
$langtext .= '</span><span id="swaltlgim\'+rownumber+\'" style="display:none"><input type="text" name="altlimg\'+rownumber+\'" id="altlimg\'+rownumber+\'" size="20" /></span>';
?>
newcell = newrow.insertCell(<?php print (8+$extracells)?>);
newcell.align='center';
newcell.innerHTML = '<?php print $langtext?>';

document.getElementById("maxoptnumber").value = parseInt(rownumber)+1;
}
function addmorerows(){
	numextrarows = document.getElementById("numextrarows").value;
	numextrarows = parseInt(numextrarows);
	if(isNaN(numextrarows))numextrarows=1;
	if(numextrarows==0)numextrarows=1;
	if(numextrarows>100)numextrarows=100;
	for(index=0;index<numextrarows;index++){
		doaddrow();
	}
	doswitcher();
<?php	if($adminlanguages>1 && ($adminlangsettings & 32)==32){ ?>
	doswitchlang();
<?php	} ?>
}
function insertoption(theval){
	var maxoptnumber = parseInt(document.getElementById("maxoptnumber").value);
	var theid = theval.id;
	theid = parseInt(theid.replace(/insertopt/, ''));
	if(document.getElementById('opt' + (maxoptnumber-1)).value!=''){
		doaddrow();
		doswitcher();
		doswitchlang();
		maxoptnumber++;
	}
	for(index=maxoptnumber-1;index>theid;index--){
		document.getElementById('opt' + index).value = document.getElementById('opt' + (index-1)).value;
<?php
	if(($adminlangsettings & 32)==32){
		for($index=2; $index <= $adminlanguages+1; $index++){
			print "document.getElementById('opl".$index."x' + index).value = document.getElementById('opl".$index."x' + (index-1)).value;\r\n";
		}
	}
	if(@$wholesaleoptionpricediff==TRUE){
		print "document.getElementById('wsp' + index).value = document.getElementById('wsp' + (index-1)).value;\r\n";
	} ?>
		document.getElementById('pri' + index).value = document.getElementById('pri' + (index-1)).value;
		document.getElementById('regexp' + index).value = document.getElementById('regexp' + (index-1)).value;
		document.getElementById('wei' + index).value = document.getElementById('wei' + (index-1)).value;
		document.getElementById('altimg' + index).value = document.getElementById('altimg' + (index-1)).value;
		document.getElementById('optStock' + index).value = document.getElementById('optStock' + (index-1)).value;
		document.getElementById('altlimg' + index).value = document.getElementById('altlimg' + (index-1)).value;
	}
	document.getElementById('opt' + theid).value = '';
<?php
	if(($adminlangsettings & 32)==32){
		for($index=2; $index <= $adminlanguages+1; $index++){
			print "document.getElementById('opl".$index."x' + theid).value = '';\r\n";
		}
	}
	if(@$wholesaleoptionpricediff==TRUE){
		print "document.getElementById('wsp' + theid).value = '';\r\n";
	} ?>
	document.getElementById('pri' + theid).value = '';
	document.getElementById('regexp' + index).value = '';
	document.getElementById('wei' + index).value = '';
	document.getElementById('altimg' + index).value = '';
	document.getElementById('optStock' + index).value = '';
	document.getElementById('altlimg' + index).value = '';
}
function checkmultipurchase(dowarnmultiple){
	var opttype = document.getElementById('optType');
	var theopttype = opttype[opttype.selectedIndex];
	var maxrow = document.getElementById("maxoptnumber").value;
	if(theopttype.value==4){
//		if(dowarnmultiple){
//			opttype.selectedIndex=curropttype;
//			alert("<?php print $yyAlUsed . '\\n\\n' . $yyMBOUni?>");
//			return;
//		}
		document.getElementById('plsselspan').innerHTML='<?php print str_replace(' ','&nbsp;',$yyDtPgOn)?>';
	}else{
		document.getElementById('plsselspan').innerHTML='<?php print str_replace(' ','&nbsp;',$yyPlsSLi)?>';
	}
}
function switchtextinput(numrows){
	if(numrows>5) numrows=5;
	document.getElementById("opt0").rows=numrows;
	document.getElementById("opt0").style.whiteSpace=(numrows==1?"nowrap":"");
<?php
	for($index=2; $index <= $adminlanguages+1; $index++){
		if(($adminlangsettings & 16)==16){
			print 'document.getElementById("opl'.$index.'x0").rows=numrows;' . "\r\n";
			print 'document.getElementById("opl'.$index.'x0").style.whiteSpace=(numrows==1?"nowrap":"");' . "\r\n";
		}
	} ?>
}
var curropttype=0;
/* ]]> */
</script>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
<?php
if(@$_POST['posted']=='1' && (@$_POST['act']=='modify' || @$_POST['act']=='clone' || @$_POST['act']=='addnew')){
	$noptions=0;
	$iscloning = (@$_POST['act']=='clone');
	if((@$_POST['act']=='modify' || @$_POST['act']=='clone') && is_numeric(@$_POST['id'])){
		$doaddnew = false;
		$sSQL = "SELECT optID,optName,optName2,optName3,optGrpName,optGrpName2,optGrpName3,optGrpWorkingName,optPriceDiff,optType,optWeightDiff,optFlags,optStock,optWholesalePriceDiff,optRegExp,optDefault,optGrpSelect,optAltImage,optAltLargeImage,optTxtMaxLen,optTxtCharge,optMultiply,optAcceptChars FROM options LEFT JOIN optiongroup ON optiongroup.optGrpID=options.optGroup WHERE optGroup=" . @$_POST['id'] . " ORDER BY optID";
		$result = mysql_query($sSQL) or print(mysql_error());
		while($rs = mysql_fetch_assoc($result)){
			$alldata[$noptions++] = $rs;
		}
		mysql_free_result($result);
		$optName = $alldata[0]['optName'];
		$optGrpName = $alldata[0]['optGrpName'];
		for($index=2; $index <= $adminlanguages+1; $index++){
			$optNames[$index] = $alldata[0]['optName' . $index];
			$optGrpNames[$index] = $alldata[0]['optGrpName' . $index];
		}
		$optGrpWorkingName = $alldata[0]['optGrpWorkingName'];
		$optPriceDiff = $alldata[0]['optPriceDiff'];
		$optType = $alldata[0]['optType'];
		$optWeightDiff = $alldata[0]['optWeightDiff'];
		$optFlags = $alldata[0]['optFlags'];
		$optStock = $alldata[0]['optStock'];
		$optWholesalePriceDiff = $alldata[0]['optWholesalePriceDiff'];
		$optDefault = $alldata[0]['optDefault'];
		$optGrpSelect = $alldata[0]['optGrpSelect'];
		$optAltImage = $alldata[0]['optAltImage'];
		$optAltLargeImage = $alldata[0]['optAltLargeImage'];
		$optTxtMaxLen = $alldata[0]['optTxtMaxLen'];
		$optTxtCharge = $alldata[0]['optTxtCharge'];
		$optMultiply = $alldata[0]['optMultiply'];
		$optAcceptChars =$alldata[0]['optAcceptChars'];
	}else{
		$doaddnew = true;
		$optName = '';
		$optGrpName = '';
		for($index=2; $index <= $adminlanguages+1; $index++){
			$optNames[$index] = '';
			$optGrpNames[$index] = '';
		}
		$optGrpWorkingName = '';
		$optPriceDiff = 15;
		$optType = (int)@$_POST['optType'];
		$optWeightDiff = '';
		$optFlags = 0;
		$optStock = '';
		$optWholesalePriceDiff = '';
		$optName2 = '';
		$optName3 = '';
		$optGrpName2 = '';
		$optGrpName3 = '';
		$optDefault = '';
		$optGrpSelect = 1;
		$optAltImage = '';
		$optAltLargeImage = '';
		$optTxtMaxLen = 0;
		$optTxtCharge = 0;
		$optMultiply = 0;
		$optAcceptChars = '';
	}
	$iscostperentry = ($optTxtCharge<0);
	$optTxtCharge=abs($optTxtCharge);
	$warnmultiple = FALSE;
	//if(@$_POST['act']=='modify' && abs($optType)!=4){
	//	$mysqlhassubqueries=TRUE;
	//	$sSQL = 'SELECT poProdID FROM prodoptions WHERE poOptionGroup='.@$_POST['id'].' AND poProdID IN (SELECT poProdID FROM prodoptions GROUP BY poProdID HAVING COUNT(poProdID)>1)';
	//	$result = @mysql_query($sSQL) or $mysqlhassubqueries=FALSE;
	//	if($mysqlhassubqueries){
	//		if(mysql_num_rows($result)>0) $warnmultiple = TRUE;
	//		mysql_free_result($result);
	//	}else
	//		$warnmultiple = TRUE;
	//}
?>
        <tr>
		  <td width="100%" align="center">
			<form name="mainform" method="post" action="adminprodopts.php" onsubmit="return formvalidator(this)">
			<input type="hidden" name="posted" value="1" />
			<?php	if($iscloning || @$_POST["act"]=="addnew"){ ?>
			<input type="hidden" name="act" value="doaddnew" />
			<?php	}else{ ?>
			<input type="hidden" name="act" value="domodify" />
			<input type="hidden" name="id" value="<?php print @$_POST["id"]?>" />
			<?php	}
					writehiddenvar('disp', @$_POST['disp']);
					writehiddenvar('stext', @$_POST['stext']);
					writehiddenvar('stype', @$_POST['stype']);
					writehiddenvar('pg', @$_POST['pg']);
					if(abs($optType)==3) print '<input type="hidden" name="optType" value="3" />'; ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
<?php	if(abs((int)$optType)==3){
			$fieldHeight = round(((double)($optPriceDiff)-floor($optPriceDiff))*100.0); ?>
			  <tr> 
                <td colspan="4" align="center"><strong><?php print (@$_POST['act']=='clone'?$yyClone.': ':'').$yyPOAdm?></strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td align="right" height="30"><?php print $yyPOName?>:</td><td align="left"><input type="text" name="secname" size="30" value="<?php print str_replace('"',"&quot;",$optGrpName)?>" /></td>
				<td align="right"><?php print $yyDefTxt?>:</td><td align="left"><textarea name="opt0" id="opt0" cols="30" rows="<?php print $fieldHeight?>"><?php print str_replace('<','&lt;',$optName)?></textarea></td>
			  </tr>
<?php			for($index=2; $index <= $adminlanguages+1; $index++){
					if(($adminlangsettings & 16)==16){ ?>
			  <tr>
				<td align="right" height="30"><?php print $yyPOName . " " . $index?>:</td><td align="left"><input type="text" name="secname<?php print $index?>" size="30" value="<?php print str_replace('"',"&quot;",$optGrpNames[$index])?>" /></td>
				<td align="right"><?php print $yyDefTxt . " " . $index?>:</td><td align="left"><textarea name="opl<?php print $index?>x0" id="opl<?php print $index?>x0" cols="30" rows="<?php print $fieldHeight?>"><?php print str_replace('<','&lt;',$optNames[$index])?></textarea></td>
			  </tr><?php
					}
				} ?>
			  <tr>
				<td align="right" rowspan="3" height="30"><?php print $yyWrkNam?>:</td>
				<td align="left" rowspan="3"><input type="text" name="workingname" size="30" value="<?php print str_replace('"',"&quot;",$optGrpWorkingName)?>" /></td>
				<td align="right" height="30"><?php print $yyFldWdt?>:</td>
				<td align="left"><select name="pri0" size="1"><?php
					for($rowcounter=1; $rowcounter <= 35; $rowcounter++){
						print "<option value='" . $rowcounter . "'";
						if($rowcounter==(int)$optPriceDiff) print ' selected="selected"';
						print '>&nbsp; ' . $rowcounter . " </option>\n";
					}
				?>
				</select></td>
			  </tr>
			  <tr>
				<td align="right" height="30"><?php print $yyFldHgt?>:</td>
				<td align="left"><select name="fieldheight" size="1" onchange="switchtextinput(this.selectedIndex+1)"><?php
					for($rowcounter=1; $rowcounter <= 15; $rowcounter++){
						print "<option value='" . $rowcounter . "'";
						if($rowcounter==$fieldHeight) print ' selected="selected"';
						print '>&nbsp; ' . $rowcounter . " </option>\n";
					}
				?>
				</select></td>
			  </tr>
			  <tr>
				<td align="right" height="30"><?php print $yyMaxEnt?>:</td>
				<td align="left"><select name="optTxtMaxLen" size="1">
				<option value="0">MAX</option><?php
					for($rowcounter=1; $rowcounter <= 255; $rowcounter++){
						print "<option value='" . $rowcounter . "'";
						if($rowcounter==$optTxtMaxLen) print ' selected="selected"';
						print '>&nbsp; ' . $rowcounter . " </option>\n";
					}
				?>
				</select></td>
			  </tr>
			  <tr>
				<td align="right" height="30"><?php print $yyForSel?>:</td><td colspan="3" align="left"><input type="checkbox" name="forceselec" value="ON"<?php if($optType > 0) print ' checked="checked"'?> /></td>
			  </tr>			  
			  <tr>
				<td align="right" height="30"><?php print $yyCosPer?>:</td><td align="left" colspan="3"><select name="iscostperentry" size="1"><option value=""><?php print $yyCosCha?></option><option value="1"<?php if($iscostperentry) print ' selected="selected"'?>><?php print $yyCosEnt?></option></select> <input type="text" name="optTxtCharge" value="<?php print htmlspecials($optTxtCharge)?>" size="5" /></td>
			  </tr>
			  <tr>
				<td align="right" height="30"><?php print $yyIsMult?>:</td>
				<td align="left"><input type="checkbox" name="optMultiply"<?php if($optMultiply!=0) print ' checked="checked"'?> value="ON" /></td>
				<td align="right"><?php print $yyAccCha?>:</td>
				<td align="left"><input type="text" name="optAcceptChars" value="<?php print htmlspecials($optAcceptChars)?>" size="15" /></td>
			  </tr>
			  <tr>
				<td colspan="4" align="left">
				  <ul>
				  <li><span style="font-size:10px"><?php print $yyPOEx1?></span></li>
				  <li><span style="font-size:10px"><?php print $yyPOEx2?></span></li>
				  <li><span style="font-size:10px"><?php print $yyPOEx3?></span></li>
				  </ul>
				  <input type="hidden" name="maxoptnumber" id="maxoptnumber" value="0" />
                </td>
			  </tr>
<?php	}else{ ?>
			  <tr>
				<td width="30%" align="center">
				  <table border="0" cellspacing="0" cellpadding="3">
				  <tr><td align="right"><strong><?php print str_replace(' ','&nbsp;',$yyPOName)?></strong></td><td align="left" colspan="3">
				  <input type="text" name="secname" size="30" value="<?php print htmlspecials($optGrpName)?>" /></td></tr>
<?php			for($index=2; $index <= $adminlanguages+1; $index++){
					if(($adminlangsettings & 16)==16){
						?><tr><td align="right"><strong><?php print $yyPOName . ' ' . $index?></strong></td><td align="left" colspan="3">
						<input type="text" name="secname<?php print $index?>" size="30" value="<?php print htmlspecials($optGrpNames[$index])?>" /></td></tr><?php
					}
				} ?>
				  <tr><td align="right"><strong><?php print str_replace(' ','&nbsp;',$yyWrkNam)?></strong></td><td align="left" colspan="3"><input type="text" name="workingname" size="30" value="<?php print htmlspecials($optGrpWorkingName)?>" /></td></tr>
				  <tr><td align="right"><strong><?php print str_replace(' ','&nbsp;',$yyOptSty)?></strong></td><td align="left" colspan="3"><select name="optType" id="optType" size="1" onclick="curropttype=this.selectedIndex" onchange="checkmultipurchase(<?php if($warnmultiple)print 'true'; else print 'false';?>)"><option value="2"><?php print $yyDDMen?></option><option value="1"<?php if(abs($optType)==1) print ' selected'?>><?php print $yyRadBut?></option><option value="4"<?php if(abs($optType)==4) print ' selected'; elseif($warnmultiple) print ' style="color:graytext"'?>><?php print $yyMulPur?></option></select></td></tr>
				  <tr><td align="right"><strong><?php print str_replace(' ','&nbsp;',$yyForSel)?></strong></td><td align="left"><input type="checkbox" name="forceselec" value="ON"<?php if($optType > 0) print ' checked="checked"'?> />&nbsp;</td><td align="right">&nbsp;<input type="radio" name="optdefault" value="" /></td><td align="left"><strong><?php print str_replace(' ','&nbsp;',$yyNoDefa)?></strong></td></tr>
				  <tr><td align="right"><strong><?php print str_replace(' ','&nbsp;',$yySinLin)?></strong></td><td align="left"><input type="checkbox" name="singleline" value="1"<?php if(($optFlags & 4) == 4) print ' checked="checked"'?> /></td><td align="right"><input type="checkbox" name="optgrpselect" value="1"<?php if((int)$optGrpSelect!=0) print ' checked="checked"'?> /></td><td align="left"><strong><span id="plsselspan"><?php print str_replace(' ','&nbsp;',(abs($optType)==4?$yyDtPgOn:$yyPlsSLi))?></span></strong></td></tr>
				  </table>
                </td>
				<td colspan="2" align="left">
				  <p align="center"><strong><?php print (@$_POST['act']=='clone'?$yyClone.': ':'').$yyPOAdm?></strong></p>
				  <ul>
				  <li><span style="font-size:10px"><?php print $yyPOEx1?></span></li>
				  <li><span style="font-size:10px"><?php print $yyPOEx4?></span></li>
				  <li><span style="font-size:10px"><?php print $yyPOEx5?></span></li>
				  <?php if($useStockManagement){ ?>
				  <li><span style="font-size:10px"><?php print $yyPOEx6?></span></li>
				  <?php } ?>
				  </ul>
                </td>
			  </tr>
			</table>
			<table id="optiontable" width="500" border="0" cellspacing="0" cellpadding="3">
			  <tr>
			  	<td><strong><?php print $yyDefaul?></strong></td>
				<td width="3%" align="center">&nbsp;</td>
				<td align="center"><select name="switcher" id="switcher" size="1" onchange="doswitcher()"><option value="1"><?php print $yyPOOpts.' / '.$yyVals?></option><option value="2"><?php print $yyPOOpts.' / '.$yyAlts?></option></select></td>
				<td width="3%" align="center">&nbsp;</td>
<?php			if($adminlanguages>=1 && ($adminlangsettings & 32)==32){
					print '<td align="center"><select name="langid" id="langid" size="1" onchange="doswitchlang()">';
					for($index=2; $index <= $adminlanguages+1; $index++){
						print '<option value="'.$index.'">' . $yyPOOpts . ' Language ' . $index . '</option>';
					}
					print '</select></td><td align="center">&nbsp;</td>';
				} ?>
				<td align="center" style="white-space:nowrap;"><strong><span id="swprdiff"><?php if(@$wholesaleoptionpricediff==TRUE) print $yyPrWsa; else print $yyPOPrDf?>&nbsp;%<input class="noborder" type="checkbox" name="pricepercent" value="1" onclick="javascript:changeunits();"<?php if(($optFlags & 1) == 1) print ' checked="checked"'?> /></span><span id="swaltid" style="display:none"><?php print $yyAltPId?></span></strong></td>
				<td width="3%" align="center">&nbsp;</td>
				<td align="center" style="white-space:nowrap;"><strong><span id="swwtdiff"><?php print $yyPOWtDf?>&nbsp;%<input class="noborder" type="checkbox" name="weightpercent" value="1" onclick="javascript:changeunits();"<?php if(($optFlags & 2) == 2) print ' checked="checked"'?> /></span><span id="swaltimg" style="display:none"><?php print $yyAltIm?></span></strong></td>
				<td width="3%" align="center">&nbsp;</td>
				<td align="center" style="white-space:nowrap;"><strong><span id="swstk"><?php print $yyStkLvl?></span><span id="swaltlgim" style="display:none"><?php print $yyAltLIm?></span></strong></td>
			  </tr>
<?php		if(($optFlags & 1) == 1) $pdUnits="&nbsp;%&nbsp;"; else $pdUnits="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			if(($optFlags & 2) == 2) $wdUnits="&nbsp;%&nbsp;"; else $wdUnits="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			for($rowcounter=0; $rowcounter < max(15, $noptions+5); $rowcounter++){
				if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark'; ?>
			  <tr class="<?php print $bgcolor?>">
				<td><input type="radio" name="optdefault" value="<?php print $rowcounter?>"<?php if($rowcounter < $noptions){ if($alldata[$rowcounter]['optDefault']!=0) print ' checked="checked"'; }?> /></td>
				<td align="center"><input type="button" id="insertopt<?php print $rowcounter?>" value="+" onclick="insertoption(this)" /></td>
				<td align="center"><?php
					if($rowcounter < $noptions && ! $iscloning) print '<input type="hidden" name="orig' . $rowcounter . '" value="' . $alldata[$rowcounter]['optID'] . '" />';
					print '<input type="text" name="opt' . $rowcounter . '" id="opt' . $rowcounter . '" size="20" value="';
					if($rowcounter < $noptions) print str_replace('"', '&quot;',$alldata[$rowcounter]["optName"]);
					print "\" /><br />\n";
				?></td>
				<td align="center"><strong>&raquo;</strong></td>
<?php			if($adminlanguages>=1 && ($adminlangsettings & 32)==32){
					print '<td align="center">';
					for($index=2; $index <= $adminlanguages+1; $index++){
						print '<span id="lang'.$index.'x'.$rowcounter.'"';
						if($index>2) print ' style="display:none">'; else print '>';
						print '<input type="text" name="opl'.$index.'x'.$rowcounter.'" id="opl'.$index.'x'.$rowcounter.'" size="20" value="';
						if($rowcounter < $noptions) print str_replace('"', '&quot;',$alldata[$rowcounter]['optName' . $index]);
						print '" /></span>';
					}
					print '</td><td align="center"><strong>&raquo;</strong></td>';
				} ?>
				<td align="center"><span id="swprdiff<?php print $rowcounter?>"><?php
					print '&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="pri'.$rowcounter.'" id="pri'.$rowcounter.'" size="5" value="';
					if($rowcounter < $noptions) print $alldata[$rowcounter]['optPriceDiff'];
					print '" />';
					if(@$wholesaleoptionpricediff==TRUE){
						print ' / <input type="text" name="wsp'.$rowcounter.'" id="wsp'.$rowcounter.'" size="5" value="';
						if($rowcounter < $noptions) print $alldata[$rowcounter]['optWholesalePriceDiff'];
						print '" />';
					}
					print '<span id="punitspan'.$rowcounter.'">'.$pdUnits.'</span>';
				?></span><span id="swaltid<?php print $rowcounter?>" style="display:none"><input type="text" name="regexp<?php print $rowcounter?>" id="regexp<?php print $rowcounter?>" size="12" value="<?php if($rowcounter < $noptions) print $alldata[$rowcounter]['optRegExp']; ?>" /></span></td>
				<td align="center"><strong>&raquo;</strong></td>
				<td align="center" style="white-space:nowrap;"><span id="swwtdiff<?php print $rowcounter?>"><?php
					print '&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="wei'.$rowcounter.'" id="wei'.$rowcounter.'" size="5" value="';
					if($rowcounter < $noptions) print $alldata[$rowcounter]['optWeightDiff'];
					print '" /><span id="wunitspan'.$rowcounter.'">'.$wdUnits.'</span>';
				?></span><span id="swaltimg<?php print $rowcounter?>" style="display:none"><input type="text" name="altimg<?php print $rowcounter?>" id="altimg<?php print $rowcounter?>" size="20" value="<?php if($rowcounter < $noptions) print $alldata[$rowcounter]['optAltImage']; ?>" /></span></td>
				<td align="center"><strong>&raquo;</strong></td>
				<td align="center"><span id="swstk<?php print $rowcounter?>"><?php
					if($useStockManagement){
						print '<input type="text" name="optStock'.$rowcounter.'" id="optStock'.$rowcounter.'" size="4" value="';
						if($rowcounter < $noptions){
							print $alldata[$rowcounter]['optStock'];
							if(trim($alldata[$rowcounter]['optRegExp'])) print '" disabled="disabled';
						}
						print '" />';
					}else{
						print '<input type="hidden" name="optStock'.$rowcounter.'" id="optStock'.$rowcounter.'" value="';
						if($rowcounter < $noptions) print $alldata[$rowcounter]['optStock'];
						print '" />n/a';
					} ?></span><span id="swaltlgim<?php print $rowcounter?>" style="display:none"><input type="text" name="altlimg<?php print $rowcounter?>" id="altlimg<?php print $rowcounter?>" size="20" value="<?php if($rowcounter < $noptions) print $alldata[$rowcounter]['optAltLargeImage']; ?>" /></span></td>
			  </tr>
<?php		} ?>
			</table>
			<input type="hidden" name="maxoptnumber" id="maxoptnumber" value="<?php print $rowcounter?>" />
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
<?php	} ?>
			  <tr>
                <td width="100%" colspan="4" align="center"><br />
<?php	if(abs((int)$optType)!=3){ ?>
				<input type="text" name="numextrarows" id="numextrarows" value="10" size="4" /> <input type="button" value="<?php print $yyMore . ' ' . $yyPOOpts?>" onclick="addmorerows()" />&nbsp;&nbsp;&nbsp;&nbsp;
<?php	} ?>
				<input type="submit" value="<?php print $yySubmit?>" /><?php if(@$_POST['act']=='modify' || @$_POST['act']=='clone'){ ?>&nbsp;&nbsp;&nbsp;<input type="reset" value="<?php print $yyReset?>" /><?php } ?><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="4" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
<?php
}elseif(@$_POST['posted']=='1' && $success){ ?>
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="adminprodopts.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />&nbsp;
                </td>
			  </tr>
			</table></td>
        </tr>
<?php
}elseif(@$_POST["posted"]=="1"){ ?>
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table></td>
        </tr>
<?php
}else{
	if(@$_GET['pract']!='') $pract = $_GET['pract']; else $pract = @$_COOKIE['practopt']; ?>
        <tr>
		  <td width="100%">
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
<?php if(strstr(@$_SERVER['HTTP_USER_AGENT'], 'Gecko')){ ?>
function mr(id,evt){
theevnt = evt;
<?php }else{ ?>
function mr(id){
theevnt=window.event;
<?php } ?>
	if(theevnt.ctrlKey){
		cr(id)
	}else{
		document.mainform.id.value = id;
		document.mainform.act.value = "modify";
		document.mainform.submit();
	}
}
function cr(id){
	document.mainform.id.value = id;
	document.mainform.act.value = "clone";
	document.mainform.submit();
}
function newtextrec(id) {
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.optType.value = "3";
	document.mainform.submit();
}
function newrec(id) {
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.optType.value = "2";
	document.mainform.submit();
}
function quickupdate(){
	if(document.mainform.pract.value=="del"){
		if(!confirm("<?php print $yyConDel?>\n"))
			return;
	}
	document.mainform.action="adminprodopts.php";
	document.mainform.act.value = "quickupdate";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function dr(id){
if(confirm("<?php print $yyConDel?>\n")) {
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.submit();
}
}
function startsearch(){
	document.mainform.action="adminprodopts.php";
	document.mainform.act.value = "search";
	document.mainform.posted.value = "";
	document.mainform.submit();
}
function changepract(){
	document.location = "adminprodopts.php?pract="+document.getElementById("pract")[document.getElementById("pract").selectedIndex].value+"&disp=<?php print @$_REQUEST['disp']?>&stext=<?php print urlencode(@$_REQUEST['stext'])?>&stype=<?php print @$_REQUEST['stype']?>&pg=<?php print (@$_GET['pg']=='' ? 1 : $_GET['pg'])?>";
}
function checkboxes(docheck){
	maxitems=document.getElementById("resultcounter").value;
	for(index=0;index<maxitems;index++){
		document.getElementById("chkbx"+index).checked=docheck;
	}
}
/* ]]> */
</script>
<?php
	$stext=unstripslashes(@$_REQUEST["stext"]);
?>
		  <form name="mainform" method="post" action="adminprodopts.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="optType" value="xxxxx" />
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
				  <tr><td class="cobhl" align="center" colspan="4" height="22"><strong><?php print $yyPOAdm?></strong></td></tr>
				  <tr> 
	                <td class="cobhl" width="25%" align="right"><?php print $yySrchFr?>:</td>
					<td class="cobll" width="25%"><input type="text" name="stext" size="20" value="<?php print $stext?>" /></td>
				    <td class="cobhl" width="25%" align="right"><?php print $yySrchTp?>:</td>
					<td class="cobll" width="25%"><select name="stype" size="1">
						<option value=""><?php print $yySrchAl?></option>
						<option value="any"<?php if(@$_REQUEST['stype']=='any') print ' selected="selected"'?>><?php print $yySrchAn?></option>
						<option value="exact"<?php if(@$_REQUEST['stype']=='exact') print ' selected="selected"'?>><?php print $yySrchEx?></option>
						</select>
					</td>
	              </tr>
				  <tr>
				    <td class="cobhl" align="center"><?php
					if(@$_POST['act']=='search' || @$_GET['pg']!=''){
						if($pract=='del'){ ?>
						<input type="button" value="<?php print $yyCheckA?>" onclick="checkboxes(true);" /> <input type="button" value="<?php print $yyUCheck?>" onclick="checkboxes(false);" />
<?php					}
					}else
						print '&nbsp;' ?></td>
				    <td class="cobll" colspan="3"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					    <tr>
						  <td class="cobll" align="center" style="white-space: nowrap">
							<select name="disp" size="1">
							<option value="">All Options</option>
							<option value="2"<?php if(@$_REQUEST['disp']=='2') print ' selected="selected"'?>>Text Options</option>
							<option value="3"<?php if(@$_REQUEST['disp']=='3') print ' selected="selected"'?>>Multiple Purchase Options</option>
							<option value="4"<?php if(@$_REQUEST['disp']=='4') print ' selected="selected"'?>>Dropdown Options</option>
							<option value="5"<?php if(@$_REQUEST['disp']=='5') print ' selected="selected"'?>>Radio Options</option>
<?php					if($useStockManagement) print '<option value="6"'.(@$_REQUEST['disp']=='6'?' selected="selected"':'').'>'.$yyOOStoc.'</option>' ?>
							<option value="7"<?php if(@$_REQUEST['disp']=='7') print ' selected="selected"'?>>Unused Options</option>
							</select>
							<input type="submit" value="List Options" onclick="startsearch();" />
						  </td>
						  <td class="cobll" height="26" width="20%" align="right" style="white-space: nowrap">
							<input type="button" value="<?php print $yyPONew?>" onclick="newrec()" />&nbsp;&nbsp;
							<input type="button" value="<?php print $yyPONewT?>" onclick="newtextrec()" />
						  </td>
						</tr>
					  </table></td>
				  </tr>
				</table>
            <table width="100%" border="0" cellspacing="0" cellpadding="1">
<?php
if(@$_POST['act']=='search' || @$_GET['pg']!=''){
	$sSQL = 'SELECT optGrpID,optGrpName,optGrpName2,optGrpName3,optGrpWorkingName FROM optiongroup';
	$whereand=' WHERE ';
	if(@$_REQUEST['disp']=='6')
		$sSQL = "SELECT DISTINCT optGrpID,optGrpName,optGrpWorkingName FROM optiongroup INNER JOIN options ON optiongroup.optGrpID=options.optGroup INNER JOIN prodoptions ON options.optGroup=prodoptions.poOptionGroup INNER JOIN products ON prodoptions.poProdID=products.pID WHERE options.optStock<=0 AND (optRegExp='' OR optRegExp IS NULL) AND products.pStockByOpts<>0 AND optType IN (-4,-2,-1,1,2,4)";
	elseif(@$_REQUEST['disp']=='7')
		$sSQL = 'SELECT optGrpID,optGrpName,optGrpName2,optGrpName3,optGrpWorkingName,poProdID FROM optiongroup LEFT JOIN prodoptions ON optiongroup.optGrpID=prodoptions.poOptionGroup WHERE poProdID IS NULL';
	elseif(@$_REQUEST['disp']=='2')
		$sSQL .= ' WHERE optType IN (-3,3)';
	elseif(@$_REQUEST['disp']=='3')
		$sSQL .= ' WHERE optType IN (-4,4)';
	elseif(@$_REQUEST['disp']=='4')
		$sSQL .= ' WHERE optType IN (-2,2)';
	elseif(@$_REQUEST['disp']=='5')
		$sSQL .= ' WHERE optType IN (-1,1)';
	if(@$_REQUEST['disp']!='') $whereand=' AND ';
	if(trim($stext) != ''){
		$Xstext = escape_string($stext);
		$aText = explode(' ',$Xstext);
		$maxsearchindex=1;
		$aFields[0]='optGrpWorkingName';
		$aFields[1]='optGrpName';
		if(@$_REQUEST['stype']=='exact'){
			$sSQL .= $whereand . "(optGrpName LIKE '%" . $Xstext . "%' OR optGrpWorkingName LIKE '%" . $Xstext . "%') ";
			$whereand=' AND ';
		}else{
			$sJoin='AND ';
			if(@$_REQUEST['stype']=='any') $sJoin='OR ';
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
	$sSQL .= ' ORDER BY optGrpWorkingName,optGrpName';
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result) > 0){ ?>
			  <tr>
				<td width="5%" align="center">
					<select name="pract" id="pract" size="1" onchange="changepract()">
					<option value="none">Quick Entry...</option>
					<option value="opn"<?php if($pract=='opn') print ' selected="selected"'?>><?php print $yyPOName?></option>
<?php				for($index=2; $index<=$adminlanguages+1; $index++){
						if(($adminlangsettings & 16)==16) print '<option value="opn'.$index.'"'.($pract==("opn".$index)?' selected="selected"':'').'>'.$yyPOName.' '.$index.'</option>';
					} ?>
					<option value="own"<?php if($pract=='own') print ' selected="selected"'?>><?php print $yyWrkNam?></option>
					<option value="" disabled="disabled">------------------</option>
					<option value="del"<?php if($pract=='del') print ' selected="selected"'?>><?php print $yyDelete?></option>
					</select></td>

				<td width="32%"><strong><?php print $yyPOName?></strong></td>
				<td width="50%"><strong><?php print $yyWrkNam?></strong></td>
				<td width="6%" align="center"><strong><?php print $yyModify?></strong></td>
				<td width="6%" align="center"><strong><?php print $yyDelete?></strong></td>
			  </tr>
<?php	$bgcolor="";
		if(strstr(@$_SERVER['HTTP_USER_AGENT'], 'Gecko')) $eventstr=',event'; else $eventstr='';
		while($rs = mysql_fetch_assoc($result)){
			if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark'; ?>
<tr class="<?php print $bgcolor?>"><?php
				if($pract=='opn')
					print '<td><input type="text" id="chkbx'.$resultcounter.'" size="18" name="pra_'.$rs['optGrpID'].'" value="' . $rs['optGrpName'] . '" tabindex="'.($resultcounter+1).'"/></td>';
				elseif($pract=='opn2')
					print '<td><input type="text" id="chkbx'.$resultcounter.'" size="18" name="pra_'.$rs['optGrpID'].'" value="' . $rs['optGrpName2'] . '" tabindex="'.($resultcounter+1).'"/></td>';
				elseif($pract=='opn3')
					print '<td><input type="text" id="chkbx'.$resultcounter.'" size="18" name="pra_'.$rs['optGrpID'].'" value="' . $rs['optGrpName3'] . '" tabindex="'.($resultcounter+1).'"/></td>';
				elseif($pract=='own')
					print '<td><input type="text" id="chkbx'.$resultcounter.'" size="18" name="pra_'.$rs['optGrpID'].'" value="' . $rs['optGrpWorkingName'] . '" tabindex="'.($resultcounter+1).'"/></td>';
				elseif($pract=='del')
					print '<td align="center"><input type="checkbox" id="chkbx'.$resultcounter.'" name="pra_'.$rs['optGrpID'].'" value="del" tabindex="'.($resultcounter+1).'"/></td>';
				else
					print '<td>&nbsp;</td>';
?>
<td><?php print $rs['optGrpName']?></td><td><?php print $rs['optGrpWorkingName']?></td>
<td><input type="button" value="<?php print $yyModify?>" onclick="mr(<?php print $rs['optGrpID'].$eventstr?>)" /></td>
<td><input type="button" value="<?php print $yyDelete?>" onclick="dr(<?php print $rs['optGrpID']?>)" /></td>
</tr>
<?php		$resultcounter++;
		}
	}else{
?>
			  <tr>
                <td width="100%" colspan="5" align="center"><br /><?php print $yyItNone?><br />&nbsp;</td>
			  </tr>
<?php
	}
	mysql_free_result($result);
}
?>
			  <tr>
				<td align="center" style="white-space: nowrap"><?php if($resultcounter>0 && $pract!='' && $pract!='none') print '<input type="hidden" name="resultcounter" id="resultcounter" value="'.$resultcounter.'" /><input type="button" value="'.$yyUpdate.'" onclick="quickupdate()" /> <input type="reset" value="'.$yyReset.'" />'; else print '&nbsp;'?></td>
                <td width="100%" colspan="4" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;<br />
					To Clone an item please hold down the &lt;Ctrl&gt; key and click &quot;Modify&quot;.</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
<?php
}
?>
      </table>