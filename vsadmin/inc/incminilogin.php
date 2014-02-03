<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
$alreadygotadmin = getadminsettings();
if(@$forceloginonhttps && (@$_SERVER['HTTPS']!='on' && @$_SERVER['SERVER_PORT']!='443') && (str_replace('http:','https:',@$storeurl)!=@$pathtossl)) $pagename=''; else $pagename=@$_SERVER['PHP_SELF'].(@$_SERVER['QUERY_STRING']!=''?'?'.$_SERVER['QUERY_STRING']:'');
?>
      <table class="mincart" width="130" bgcolor="#FFFFFF">
        <tr> 
          <td class="mincart" bgcolor="#F0F0F0" align="center"><img src="images/minipadlock.gif" align="top" alt="<?php print $xxMLLIS?>" />
<?php		if(@$_SESSION['clientID']!='' && @$customeraccounturl!=''){ ?>
			&nbsp;<a class="ectlink mincart" href="<?php print $customeraccounturl?>"><strong><?php print $xxYouAcc?></strong></a>
<?php		}else{ ?>
            &nbsp;<strong><?php print $xxMLLIS?></strong></td>
<?php		} ?>
        </tr>
<?php	if(@$enableclientlogin!=TRUE && @$forceclientlogin!=TRUE){ ?>
		<tr>
		  <td class="mincart" bgcolor="#F0F0F0" align="center">
		  <p class="mincart">Client login not enabled</p>
		  </td>
		</tr>
<?php	}elseif(@$_SESSION['clientID'] != ''){ ?>
		<tr>
		  <td class="mincart" bgcolor="#F0F0F0" align="center">
		  <p class="mincart"><?php print $xxMLLIA?><strong><br /><?php print htmlspecials($_SESSION['clientUser'])?></strong></p>
		  </td>
		</tr>
		<tr> 
          <td class="mincart" bgcolor="#F0F0F0" align="center"><span style="font-family:Verdana">&raquo;</span> <a class="ectlink mincart" href="<?php print $storeurl?>cart.php?mode=logout"><strong><?php print $xxLogout?></strong></a></td>
        </tr>
<?php	}else{ ?>
		<tr>
		  <td class="mincart" bgcolor="#F0F0F0" align="center">
		  <p class="mincart"><?php print $xxMLNLI?></p>
		  </td>
		</tr>
		<tr> 
          <td class="mincart" bgcolor="#F0F0F0" align="center"><span style="font-family:Verdana">&raquo;</span> <a class="ectlink mincart" href="<?php print $storeurl?>cart.php?mode=login&amp;refurl=<?php print urlencode($pagename)?>"><strong><?php print $xxLogin?></strong></a></td>
        </tr>
<?php	} ?>
      </table>