<?php
if(@$nobuyorcheckout == TRUE)
	print '&nbsp;';
else{
	if($rs['pPrice']==0 && @$nosellzeroprice==TRUE){
		print '&nbsp;';
	}elseif($isInStock){
		writehiddenvar('id', $rs['pId']);
		writehiddenvar('mode', 'add');
		if($wishlistondetail) writehiddenvar('listid', '');
		if(@$showquantondetail && $hasmultipurchase==0) print '<table><tr><td align="center"><input type="text" name="quant" size="2" maxlength="5" value="1" alt="'.$xxQuant.'" />' . (@$showquantondetail && $hasmultipurchase==0 ? '</td><td align="center">' : '');
		if(@$custombuybutton!='') print $custombuybutton; else print imageorsubmit(@$imgbuybutton,$xxAddToC,'buybutton');
		if($wishlistondetail) print '<br />' . imageorlink(@$imgaddtolist,$xxAddLis,'gtid='.$Count.';return displaysavelist(event,window)',TRUE);
		if(@$showquantondetail && $hasmultipurchase==0) print '</td></tr></table>';
	}else{
		if(@$notifybackinstock)
			print imageorlink(@$imgnotifyinstock, $xxNotBaS, "return notifyinstock(false,'".str_replace("'","\\'",$rs['pId'])."','".str_replace("'","\\'",$rs['pId'])."',".($rs['pStockByOpts']!=0&&!@$optionshavestock?'-1':'0').")", TRUE);
		else
			print '<strong>'.$xxOutStok.'</strong>';
	}
}			?>