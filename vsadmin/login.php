<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protect under law as the intellectual property
//of Internet Business Solutions SL. Any use, reproduction, disclosure or copying
//of any kind without the express and written permission of Internet Business 
//Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
session_cache_limiter('none');
session_start();
ob_start();
header('cache-Control: no-cache, no-store');
header('Pragma: no-cache');
include 'db_conn_open.php';
include 'includes.php';
include 'inc/languageadmin.php';
include 'inc/incfunctions.php';
$isprinter=FALSE;
$alreadygotadmin = getadminsettings();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Control panel login</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
BODY {
margin:0px;
padding:0px;
font-family: arial, helvetica, sans-serif;
font-size: 11px;
color: #000;
background: #FFF;
background: url(adminimages/loginbg.jpg);
background-repeat:repeat-x;
}
body, td, input, textarea, select, p {	color: #000; font: normal 11px Arial, sans-serif; }
table.center {margin-left:auto; margin-right:auto; height: 100%; width: 100%;}
td.content{padding: 4px; height:34px;}
A:link {
	color: #FFF;
	text-decoration: none;
}
A:visited {
	color: #FFF;
	text-decoration: none;
}
A:active {
	color: #FFF;
	text-decoration: none;
}
A:hover {
	color: #FFF;
	text-decoration: underline;
}
</style>
<meta name="viewport" content="width=320,user-scalable=false" />
</head>

<body>
<table width="320" border="0" cellpadding="0" cellspacing="0" class="center">
        <tr> 
          <td>
<?php include 'inc/incdologin.php';?>
		  </td>
        </tr>
      </table>
</body>
</html>
