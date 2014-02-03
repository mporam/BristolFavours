<?php


if(is_array($prodoptions)){
	$rs['pPrice'] += $optdiff;
	if($optionshtml!='') print '<div class="detailoptions" align="center"><table class="prodoptions detailoptions" border="0" cellspacing="1" cellpadding="1">' . $optionshtml . '</table></div>';
	if($optjs!='') print '<script language="javascript" type="text/javascript">/* <![CDATA[ */'.$optjs.'/* ]]> */</script>';
}
?>