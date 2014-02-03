<?php
$mName="Product Detail Layout Mod"; // Module Name
$mCodeName="mod23"; // Module Code Name
$mVersion="6.0.1"; // Module Version
$mLink="#"; // Module Link

$mInstallFile="install.php";  // Module Install file name
$sVersion="PHP";  // Module Platform
$mAuthorCode="ECT";  // Module Author Code
$mAuthor="ECT Modules"; // Module Author
$modauthorlink="www.ectmodules.com/index.php?PARTNER=adminlink"; // Module Author link

?>
<html>
<head>
<title><?php print $mName;?> v<?php print $mVersion;?> <?php print $sVersion;?> Installation - by <?php print $mAuthorCode?></title>
<STYLE type="text/css">
<!--
p {  font: 10pt Verdana, Arial, Helvetica, sans-serif}
TD {  font: 10pt Verdana, Arial, Helvetica, sans-serif}
BODY {  font: 10pt Verdana, Arial, Helvetica, sans-serif}
-->
</STYLE>
</head>
<body>
<p>
<?php

include "vsadmin/db_conn_open.php";
$haserrors=FALSE;

function print_sql_error(){
	global $haserrors;
	$haserrors=TRUE;
	print "<font color='#FF0000'>" . mysql_error() . "</font><br>";
}

if(@$_POST["posted"]=="1"){

	$addcl 		=	"ADD COLUMN";
	$txtcl 		=	"VARCHAR";
	$smallcl 	=	"SMALLINT";
	$bytecl 	=	"TINYINT";
	$dblcl 		=	"DOUBLE";
	$memocl 	=	"TEXT";
	$datecl 	=	"DATETIME";
	$datets 	=	"TIMESTAMP";
	$autoinc 	=	"INT NOT NULL AUTO_INCREMENT PRIMARY KEY";
	$datedelim	=	"'";

	//Check ECT Version
	print "Checking ECT Version <br/>";
	$result = mysql_query("SELECT adminVersion FROM admin WHERE adminID=1") or print(mysql_error());
	$rs = mysql_fetch_assoc($result);
	$ectVersion=$rs["adminVersion"];
	mysql_free_result($result);
	print "ECT Store Version : <b><font color=navy>".$ectVersion."</font></b><br/><br/>";

	mysql_query("SELECT * FROM installedmods");
		//Remove old versions
		mysql_query("DELETE FROM installedmods WHERE modtitle='".$mName."'");
		//Add New Version
		$sSQL =	"INSERT INTO installedmods (modkey,modtitle,modauthor,modauthorlink,modversion,modectversion,modlink,moddate,modnotes) Values ";
		$sSQL.=	"('".$mCodeName."_".$mVersion."','".$mName."','".$mAuthor."','".$modauthorlink."','".$mVersion."','".$ectVersion."',";
		$sSQL.=	"'".$mLink."',CURDATE(),'')";
		mysql_query($sSQL) or print_sql_error();

//Mod database modification script Starts here


//Mod database modification script ends here


	//Check for errors
	flush();
	if($haserrors)
		print "<font color='#FF0000'><b>There was an error in your install.&nbsp;</b></font><br/>";
	else
		print "<font color='#FF0000'><b>Mod installed succesfully !</b></font><br/>";
	mysql_close($dbh);
}else{
	?>
	<form action="<?php print $mInstallFile?>" method="POST">
	<input type="hidden" name="posted" value="1">
	<table width="100%" height="171">
	<tr><td align="center" width="100%" height="167">
	<p>&nbsp;</p>
	<div align="center">
	  <center>
	  <table border="0" cellpadding="0" cellspacing="0" width="583">
		<tr>
		  <td width="577">
			<p align="center"><?php print $mName;?> v<?php print $mVersion;?> by <?php print $mAuthorCode?></td>
		</tr>
		<tr>
		  <td width="577">
			<p align="center"><font SIZE="1">Platform: <?php print $sVersion;?></font></td>
		</tr>
		<tr>
		  <td width="577">&nbsp;
			<p></td>
		</tr>
		<tr>
		  <td width="577">
			<p align="center">After performing the update, please delete this file from your web.</td>
		</tr>
		<tr>
		  <td width="577">&nbsp;
			<p></td>
		</tr>
		<tr>
		  <td width="577">
			<p align="center">
	<input type="submit" value="Install">
		  </td>
		</tr>
	  </table>
	  </center>
	</div>
	<p>&nbsp;</p>
	</td></tr>
	</table>
	</form>
	<?php
}
?>
</body>
</html>