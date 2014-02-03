<?php
session_cache_limiter('none');
session_start();
ob_start();
include 'vsadmin/db_conn_open.php';
include 'vsadmin/inc/languagefile.php';
include 'vsadmin/includes.php';
include 'vsadmin/inc/incfunctions.php';
		if(@$inlinepopups!=TRUE){ ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php print jsenc($xxEmFrnd)?></title>
<link rel="stylesheet" type="text/css" href="style.css" />
<meta name="robots" content="noindex,nofollow" />
</head>
<body style="margin: 5px 5px 5px 5px;">
<?php	}else{
header('Cache-Control: no-cache');
header('Pragma: no-cache');
?>
<table width="500" border="0" id="emftable" style="position:absolute">
	<tr>
	  <td align="center" id="efrcell">
<?php	}
$alreadygotadmin = getadminsettings();
if(@$multiemfblockmessage=='') $multiemfblockmessage="I'm sorry. We are experiencing temporary difficulties at the moment. Please try again later.";
if(@$_REQUEST['askq']=='1' && @$useaskaquestion==TRUE) $isaskquestion=TRUE; else $isaskquestion=FALSE;
function jsenc($tstr){
	global $adminencoding,$inlinepopups;
	if(@$inlinepopups==TRUE){
		if(strtolower(@$adminencoding)!='utf-8') $tstr=utf8_encode($tstr);
	}
	return($tstr);
}
function checkemfuserblock(){
	global $blockmultiemf;
	if(@$blockmultiemf=='') $blockmultiemf=20;
	$multiemfblocked=FALSE;
	$theip = @$_SERVER['REMOTE_ADDR'];
	if($theip=='') $theip = 'none';
	if(@$blockmultiemf!=''){
		mysql_query("DELETE FROM multibuyblock WHERE lastaccess<'" . date('Y-m-d H:i:s', time()-(60*60*24)) . "'") or print(mysql_error());
		$sSQL = "SELECT ssdenyid,sstimesaccess FROM multibuyblock WHERE ssdenyip = 'EMF " . trim(escape_string($theip)) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs = mysql_fetch_array($result)){
			mysql_query("UPDATE multibuyblock SET sstimesaccess=sstimesaccess+1,lastaccess='" . date('Y-m-d H:i:s', time()) . "' WHERE ssdenyid=" . $rs['ssdenyid']) or print(mysql_error());
			if($rs['sstimesaccess'] >= $blockmultiemf) $multiemfblocked=TRUE;
		}else{
			mysql_query("INSERT INTO multibuyblock (ssdenyip,lastaccess) VALUES ('EMF " . trim(escape_string($theip)) . "','" . date('Y-m-d H:i:s', time()) . "')") or print(mysql_error());
		}
		mysql_free_result($result);
	}
	if($theip=='none' || ip2long($theip)==FALSE)
		$sSQL = 'SELECT dcid FROM ipblocking LIMIT 0,1';
	else
		$sSQL = 'SELECT dcid FROM ipblocking WHERE (dcip1=' . ip2long($theip) . ' AND dcip2=0) OR (dcip1 <= ' . ip2long($theip) . ' AND ' . ip2long($theip) . ' <= dcip2 AND dcip2 <> 0)';
	$result = mysql_query($sSQL) or print(mysql_error());
	if(mysql_num_rows($result) > 0)
		$multiemfblocked = TRUE;
	return($multiemfblocked);
}
	if(@$_POST['posted']=='1'){
		$success=TRUE;
		$referer = @$_SERVER['HTTP_REFERER'];
		$host = @$_SERVER['HTTP_HOST'];
		if(@$useemailfriend!=TRUE && @$useaskaquestion!=TRUE){
			$xxEFThk='<strong><font color="#FF0000">Email Friend / Ask a Question not enabled.</font></strong>';
		}elseif(strpos($referer, $host)===FALSE || @$_POST['efcheck']!=@$_SESSION['eftimestampcheck'] || (time()-$_SESSION['eftimestamp']) > (60*60)){
			$xxEFThk='<strong><font color="#FF0000">I\'m sorry but your email could not be sent at this time.</font></strong>';
			ob_end_clean();
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}elseif(checkemfuserblock()){
			$xxEFThk='<strong><font color="#FF0000">' . $multiemfblockmessage . '</font></strong>';
			ob_end_clean();
			header('HTTP/1.1 403 Forbidden');
			exit;
		}else{
			$theprodid = trim(substr(@$_POST['efid'],0,50));
			$sSQL='SELECT adminEmail,adminStoreURL FROM admin WHERE adminID=1';
			$result = mysql_query($sSQL) or print(mysql_error());
			$rs = mysql_fetch_array($result);
			$emailAddr = $rs['adminEmail'];
			$adminStoreURL = $rs['adminStoreURL'];
			if($isaskquestion && @$useaskaquestion==TRUE)
				$friendsemail = $emailAddr;
			elseif(@$useemailfriend==TRUE && strlen(@$_POST['friendsemail'])<50)
				$friendsemail = str_replace(array("\r","\n"), '', @$_POST['friendsemail']);
			else
				$friendsemail = '';
			$yourname = str_replace(array("\r","\n"), '', substr(@$_POST['yourname'],0,50));
			$youremail = str_replace(array("\r","\n"), '', substr(@$_POST['youremail'],0,50));
			$yourcomments = str_replace("\r\n",$emlNl,trim(substr(@$_POST['yourcomments'],0,2000)));
			if($isaskquestion){
				$seBody = $xxAskQue . ': ' . $yourname . $emlNl . $emlNl . $yourcomments . $emlNl;
				$thesubject=$xxAsqSub;
			}else{
				if((substr(strtolower($adminStoreURL),0,7) != 'http://') && (substr(strtolower($adminStoreURL),0,8) != 'https://'))
					$adminStoreURL = 'http://' . $adminStoreURL;
				if(substr($adminStoreURL,-1) != '/') $adminStoreURL .= '/';
				mysql_free_result($result);
				$seBody = $xxEFYF1 . $yourname . ' (' . $youremail . ')' . $xxEFYF2;
				if(trim(@$_POST['yourcomments'])!=''){
					$seBody .= $xxEFYF3 . $emlNl;
					$seBody .= $yourcomments . $emlNl;
				}else
					$seBody .= '.' . $emlNl;
				$produrl = 'proddetail.php?prod=' . $theprodid;
				if($theprodid!=''){
					$sSQL = "SELECT pName,pStaticPage FROM products WHERE pID='" . escape_string($theprodid) . "'";
					$result = mysql_query($sSQL) or print(mysql_error());
					if($rs = mysql_fetch_array($result)){
						if($rs['pStaticPage'] != 0) $produrl = cleanforurl($rs['pName']) . '.php';
					}
					mysql_free_result($result);
				}
				if(@$htmlemails==true){
					$storeLink = $adminStoreURL;
					if(trim(@$_POST['efid']) != "") $storeLink .= $produrl;
					$seBody .= $emlNl . '<a href="' . $storeLink . '">' . $storeLink . '</a>';
				}else{
					$seBody .= $emlNl . $adminStoreURL;
					if(trim(@$_POST['efid']) != "") $seBody .= $produrl;
				}
				$thesubject=$yourname . $xxEFRec;
			}
			$seBody .= $emlNl;
			if($friendsemail!='') dosendemail($friendsemail, $emailAddr, $youremail, $thesubject, $seBody);
		}
?>
<br />
  <table class="cobtbl emftbl" border="0" cellspacing="1" cellpadding="3" width="<?php print (@$inlinepopups==TRUE?'500':'100%')?>">
	<tr>
	  <td class="cobll emfll" colspan="2" align="center" width="100%"><p>&nbsp;</p>
	  <p><?php print ($isaskquestion?$xxAsqThk:$xxEFThk)?></p>
	  <p><?php print jsenc($xxClkClo)?></p>
	  <p>&nbsp;</p>
	  <?php print imageorbutton(@$imgefclose, jsenc($xxClsWin), '', (@$inlinepopups==TRUE?"document.body.removeChild(document.getElementById('efrdiv'))":'javascript:self.close()'), TRUE); ?>
	  <p>&nbsp;</p>
	  </td>
	</tr>
  </table>
<?php
	}else{
		$eftimestamp=time();
		$eftimestampcheck=md5($eftimestamp.'This is a check'.$adminSecret.' A b H k');
		$_SESSION['eftimestamp']=$eftimestamp;
		$_SESSION['eftimestampcheck']=$eftimestampcheck;
		if(@$inlinepopups!=TRUE) emailfriendjavascript();
?>
<form id="efform" method="post" action="emailfriend.php" onsubmit="return efformvalidator(this)">
  <input type="hidden" name="posted" value="1" />
  <input type="hidden" id="efid" name="efid" value="<?php print htmlspecialchars(@$_GET['id'])?>" />
  <input type="hidden" id="efcheck" name="efcheck" value="<?php print $eftimestampcheck?>" />
  <input type="hidden" id="askq" name="askq" value="<?php print ($isaskquestion?"1":"")?>" />
  <table class="cobtbl emftbl" border="0" cellspacing="1" cellpadding="7" width="<?php print (@$inlinepopups==TRUE?'500':'100%')?>">
	<tr>
	  <td class="cobhl emfhl" bgcolor="#EBEBEB" colspan="2" align="center" width="100%" height="30"><?php print ($isaskquestion?$xxAskQue:$xxEmFrnd)?></td>
	</tr>
    <tr>
		<td class="cobll emfll" width="100%" align="left"><?php print ($isaskquestion?$xxAQBlr:$xxEFBlr)?><br />
      <br /><font color="#FF0000">*</font><?php print jsenc($xxEFNam)?><br /><input type="text" id="yourname" name="yourname" size="30" /><br />
      <font color="#FF0000">*</font><?php print jsenc($xxEFEm)?><br /><input type="text" id="youremail" name="youremail" size="30" /><br />
<?php	if(! $isaskquestion){ ?>
     <font color="#FF0000">*</font><?php print jsenc($xxEFFEm)?><br /><input type="text" id="friendsemail" name="friendsemail" size="30" /><br />
<?php	}else{
			$theproduct=trim(substr(@$_GET['id'],0,50));
			$sSQL = "SELECT pName FROM products WHERE pID='" . escape_string($theproduct) . "'";
			$result = mysql_query($sSQL) or print(mysql_error());
			if($rs = mysql_fetch_array($result))
				$theproduct .= ' - ' . $rs['pName'];
			mysql_free_result($result);
		} ?>
     <font color="#FF0000">*</font><?php print jsenc($xxEFCmt)?><br /><textarea id="yourcomments" name="yourcomments" cols="46" rows="6"><?php print ($isaskquestion ? htmlspecials(str_replace('%nl%',"\r\n",$xxAskCom) . $theproduct) : '')?></textarea>
		<p align="center"><?php
		if(@$inlinepopups==TRUE)
			print imageorbutton(@$imgefsend, jsenc($xxSend), '', 'dosendefdata()', TRUE);
		else
			print imageorsubmit(@$imgefsend, jsenc($xxSend), '');
		print '&nbsp;&nbsp;';
		print imageorbutton(@$imgefclose, jsenc($xxClsWin), '', (@$inlinepopups==TRUE?"document.body.removeChild(document.getElementById('efrdiv'))":'javascript:self.close()'), TRUE);
?></p>
      </td>
	</tr>
  </table>
<?php	if(@$inlinepopups==TRUE){ ?>
	</td>
  </tr>
</table>
<?php	} ?>
</form>
<?php
	}
		if(@$inlinepopups!=TRUE){ ?>
</body>
</html>
<?php	} ?>