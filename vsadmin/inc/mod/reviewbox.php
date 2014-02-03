<?php if(@$_POST['review']=='true' || @$_GET['review']=='all'){
	// Do nothing
}elseif(@$enablecustomerratings==TRUE && @$_GET['review']=='true'){
	if(@$onlyclientratings && @$_SESSION['clientID']=='')
		print '<tr><td align="center">Only logged in customers can review products.</td></tr>';
	else{ ?>
  <tr> 
    <td> <script language="javascript" type="text/javascript">
/* <![CDATA[ */
function checkratingform(frm){
if(frm.ratingstars.selectedIndex==0){
	alert("<?php print $xxRvPlsS?>.");
	frm.ratingstars.focus();
	return(false);
}
if(frm.reviewposter.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxRvPosb?>\".");
	frm.reviewposter.focus();
	return(false);
}
if(frm.reviewheading.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxRvHead?>\".");
	frm.reviewheading.focus();
	return(false);
}
return (true);
}
/* ]]> */
</script> <form method="post" action="<?php print detailpageurl($thecatid!='' ? 'cat='.$thecatid : '')?>" style="margin:0px;padding:0px;"  onsubmit="return checkratingform(this)">
        <input type="hidden" name="review" value="true" />
        <table border="0" cellspacing="0" cellpadding="2" width="100%" align="center">
          <tr> 
            <td align="right"><span class="review reviewstar" style="color:#FF0000">*</span><span class="review reviewform"><?php print $xxRvRati?>:</span></td>
            <td><select size="1" name="ratingstars" class="review reviewform">
                <option value=""><?php print $xxPlsSel?></option>
                <?php
			for($index=1; $index<=5; $index++){
				print '<option value="'.$index.'">'.$index.' '.$xxStars.'</option>';
			} ?>
              </select></td>
          </tr>
          <tr> 
            <td align="right"><span class="review reviewstar" style="color:#FF0000">*</span><span class="review reviewform"><?php print $xxRvPosb?>:</span></td>
            <td><input type="text" size="20" name="reviewposter" maxlength="64" value="<?php print str_replace('"','&quot;',@$_SESSION['clientUser'])?>" class="review reviewform" /></td>
          </tr>
          <?php	if(FALSE){ ?>
          <tr> 
            <td align="right">Email:</td>
            <td><input type="text" size="20" name="reviewemail" maxlength="64" class="review reviewform" /></td>
          </tr>
          <?php	} ?>
          <tr> 
            <td align="right"><span class="review reviewstar" style="color:#FF0000">*</span><span class="review reviewform"><?php print $xxRvHead?>:</span></td>
            <td><input type="text" size="40" name="reviewheading" maxlength="253" class="review reviewform" /></td>
          </tr>
          <tr> 
            <td align="right"><span class="review reviewform"><?php print $xxRvComm?>:</span></td>
            <td><textarea name="reviewcomments" cols="38" rows="8" class="review reviewform"></textarea></td>
          </tr>
          <tr> 
            <td align="right">&nbsp;</td>
            <td><input type="submit" value="<?php print $xxSubmt?>" class="review reviewform" /></td>
          </tr>
        </table>
      </form></td>
  </tr>
  <?php
	}
}elseif(@$enablecustomerratings==TRUE){
	$sSQL = "SELECT rtID,rtRating,rtPosterName,rtHeader,rtDate,rtComments FROM ratings WHERE rtApproved<>0 AND rtProdID='".escape_string($prodid)."'";
	if(@$ratingslanguages!='') $sSQL .= ' AND rtLanguage+1 IN ('.$ratingslanguages.')'; elseif(@$languageid!='') $sSQL .= ' AND rtLanguage='.((int)$languageid-1); else $sSQL .= ' AND rtLanguage=0';
	$sSQL .= ' ORDER BY rtDate DESC,rtRating DESC';
	if(! $reviewsshown && $productindb) print showreviews($sSQL,FALSE);
} ?>