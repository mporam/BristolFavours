<?php $alldiscounts = '';
	if(@$nowholesalediscounts==TRUE && @$_SESSION["clientUser"]!='')
		if((($_SESSION["clientActions"] & 8) == 8) || (($_SESSION["clientActions"] & 16) == 16)) $noshowdiscounts=TRUE;
	if(@$noshowdiscounts != TRUE && $prodid!=$giftcertificateid && $prodid!=$donationid){
		$sSQL = "SELECT DISTINCT ".getlangid("cpnName",1024)." FROM coupons LEFT OUTER JOIN cpnassign ON coupons.cpnID=cpnassign.cpaCpnID WHERE cpnNumAvail>0 AND cpnEndDate>='" . date("Y-m-d",time()) ."' AND cpnIsCoupon=0 AND " .
			"((cpnSitewide=1 OR cpnSitewide=2) OR (cpnSitewide=0 AND cpaType=2 AND cpaAssignment='" . $rs["pId"] . "') " .
			"OR ((cpnSitewide=0 OR cpnSitewide=3) AND cpaType=1 AND cpaAssignment IN ('" . str_replace(",","','",$topsectionids) . "')))" .
			" AND ((cpnLoginLevel>=0 AND cpnLoginLevel<=".$minloglevel.") OR (cpnLoginLevel<0 AND -1-cpnLoginLevel=".$minloglevel."))";
		$result2 = mysql_query($sSQL) or print(mysql_error());
		while($rs2=mysql_fetch_assoc($result2))
			$alldiscounts .= $rs2[getlangid("cpnName",1024)] . "<br />";
		mysql_free_result($result2);
	}
	if(@$enablecustomerratings==TRUE && @$_POST['review']=='true'){
		$hitlimit = FALSE;
		print '<table border="0" cellspacing="2" cellpadding="2" width="100%" align="center">';
		$sSQL = "SELECT COUNT(*) as thecount FROM ratings WHERE rtDate='" . date('Y-m-d', time()) . "' AND rtIPAddress='" . escape_string(getipaddress()) . "'";
		$result = mysql_query($sSQL) or print(mysql_error());
		if($rs2 = mysql_fetch_assoc($result)){
			if(@$dailyratinglimit=='') $dailyratinglimit=10;
			if(! is_null($rs2['thecount'])){
				if($rs2['thecount']>$dailyratinglimit) $hitlimit=TRUE;
			}
		}
		mysql_free_result($result);
		$theip = @$_SERVER['REMOTE_ADDR'];
		if($theip == '') $theip = 'none';
		if($theip == 'none')
			$sSQL = 'SELECT dcid FROM ipblocking LIMIT 0,1';
		else
			$sSQL = 'SELECT dcid FROM ipblocking WHERE (dcip1=' . ip2long($theip) . ' AND dcip2=0) OR (dcip1 <= ' . ip2long($theip) . ' AND ' . ip2long($theip) . ' <= dcip2 AND dcip2 <> 0)';
		$result = mysql_query($sSQL) or print(mysql_error());
		if(mysql_num_rows($result) > 0)
			$hitlimit = TRUE;
		$referer = @$_SERVER['HTTP_REFERER'];
		$host = @$_SERVER['HTTP_HOST'];
		if(strpos($referer, $host)===FALSE){
			print '<tr><td align="center">Sorry but your review could not be sent at this time.</td></tr>';
		}elseif($hitlimit)
			print '<tr><td>'.$xxRvLim.'</td></tr>';
		elseif(@$onlyclientratings && @$_SESSION['clientID']=='')
			print '<tr><td align="center">Only logged in customers can review products.</td></tr>';
		elseif(is_numeric(@$_POST['ratingstars'])){
			$sSQL = 'INSERT INTO ratings (rtProdID,rtRating,rtPosterName,rtHeader,rtIPAddress,rtApproved,rtLanguage,rtDate,rtPosterLoginID,rtComments) VALUES (';
			$sSQL .= "'" . escape_string(strip_tags($prodid)) . "',";
			$sSQL .= "'" . (is_numeric(@$_POST['ratingstars']) ? escape_string((int)@$_POST['ratingstars'] * 2) : 0) . "',";
			$sSQL .= "'" . escape_string(strip_tags(unstripslashes(@$_POST['reviewposter']))) . "',";
			$sSQL .= "'" . escape_string(strip_tags(unstripslashes(@$_POST['reviewheading']))) . "',";
			$sSQL .= "'" . escape_string(strip_tags(unstripslashes(getipaddress()))) . "',";
			$sSQL .= '0,';
			if(@$languageid!='') $sSQL .= ((int)$languageid-1).','; else $sSQL .= '0,';
			$sSQL .= "'" . date('Y-m-d', time()) . "',";
			$sSQL .= (@$_SESSION['clientID']!='' ? @$_SESSION['clientID'] : 0) . ',';
			$sSQL .= "'" . escape_string(strip_tags(unstripslashes(@$_POST['reviewcomments']))) . "')";
			mysql_query($sSQL) or print(mysql_error());
			print '<tr><td align="center">&nbsp;<br />&nbsp;<br />'.$xxRvThks.'<br />&nbsp;<br />&nbsp;';
			print $xxRvRet.' <a class="ectlink" href="' . detailpageurl($thecatid!='' ? 'cat='.$thecatid : '') . '">' . $xxClkHere . '</a>';
			print '<br />&nbsp;<br />&nbsp;';
			print '<meta http-equiv="Refresh" content="3; URL=' . detailpageurl($thecatid!='' ? 'cat='.$thecatid : '') . '">';
			print '</td></tr>';
		}
		print '</table>';
	}elseif(@$enablecustomerratings==TRUE && @$_GET['review']=='all'){
		print '<table border="0" cellspacing="2" cellpadding="2" width="100%" align="center">';
		print '<tr><td>';
		if(! (trim($rs['pImage'])=='' || trim($rs['pImage'])=='prodimages/'))
			print '<img align="middle" id="prodimage0" class="prodimage" src="'.str_replace('%s','',$rs['pImage']).'" border="0" alt="'.strip_tags($rs[getlangid('pName',1)]).'" />&nbsp;';
		print '<span class="review reviewsforprod">'.$xxRvRevP.' - </span><span class="review reviewprod">' . $rs[getlangid('pName',1)] . '</span> <span class="review reviewback">(<a class="ectlink" href="' . detailpageurl($thecatid!='' ? 'cat='.$thecatid : '') . '">' . $xxRvBack . '</a>)</span><br />&nbsp;</td></tr>';
		$sSQL = "SELECT rtID,rtRating,rtPosterName,rtHeader,rtDate,rtComments FROM ratings WHERE rtApproved<>0 AND rtProdID='" . escape_string($prodid) . "'";
		if(@$ratingslanguages!='') $sSQL .= ' AND rtLanguage+1 IN ('.$ratingslanguages.')'; elseif(@$languageid!='') $sSQL .= ' AND rtLanguage='.((int)$languageid-1); else $sSQL .= ' AND rtLanguage=0';
		if(@$_GET['ro']=='1')
			$sSQL .= ' ORDER BY rtRating DESC';
		elseif(@$_GET['ro']=='2')
			$sSQL .= ' ORDER BY rtRating';
		elseif(@$_GET['ro']=='3')
			$sSQL .= ' ORDER BY rtDate';
		else
			$sSQL .= ' ORDER BY rtDate DESC';
		print showreviews($sSQL,TRUE);
		print '</table>';
	}elseif(@$enablecustomerratings==TRUE && @$_GET['review']=='true'){
		print '<table border="0" cellspacing="2" cellpadding="2" width="100%" align="center">';
		print '<tr><td><span class="review reviewing">'.$xxRvAreR.' - </span><span class="review reviewprod">' . $rs[getlangid('pName',1)] . '</span> <span class="review reviewback">(<a class="ectlink" href="' . detailpageurl($thecatid!='' ? 'cat='.$thecatid : '') . '">' . $xxRvBack . '</a>)</span><br />&nbsp;</td></tr>';
		print '</table>';
	}elseif($prodid==$giftcertificateid || $prodid==$donationid){
		$isincluded = TRUE;
		include './vsadmin/inc/incspecials.php';
	} ?>