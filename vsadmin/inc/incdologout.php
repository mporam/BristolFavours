<?php	$_SESSION['loggedon']='';
		setcookie('WRITECKL', '', (time() - 2592000), '/', '', 0);
		setcookie('WRITECKP', '', (time() - 2592000), '/', '', 0);
		print '<meta http-equiv="Refresh" content="3; URL=admin.php">';
?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyLogOut?></strong><br /><br /><?php print $yyLOMes?><br /><br />
				<img src="../images/clearpixel.gif" width="300" height="3" alt="" />
                </td>
			  </tr>
			</table>
		  </td>
        </tr>
      </table>