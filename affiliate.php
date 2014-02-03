<?php
session_cache_limiter('none');
session_start();
ob_start();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><!-- #BeginTemplate "/Templates/Main.dwt" -->
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
<head>
<!-- #BeginEditable "doctitle" --> 
<title>Home</title>
<!-- #EndEditable -->
<link rel="stylesheet" type="text/css" href="style.css"/>
</head>

<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td bgcolor="#000033" width="50%"><a href="http://www.ecommercetemplates.com/" target="_blank"><img src="images/logo.gif" width="299" height="49" border="0" alt=""/></a></td>
    <td bgcolor="#000033" width="50%"><img src="images/clearpixel.gif" width="1" height="60" alt=""/></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td bgcolor="#3399CC" width="130" valign="top"><img src="images/clearpixel.gif" width="130" height="1" alt=""/><br/>
      <table width="120" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
          <td><br/>
            <a href="index.php">Home</a><br/>
            <a href="instructions.php">Instructions </a><br/>
            <a href="categories.php">Products</a><br/>
            <a href="affiliate.php">Affiliates</a><br/>
            <a href="orderstatus.php">Order Status</a><br/>
            <a href="search.php">Search</a><br/>
            <a href="cart.php">Checkout</a></td>
        </tr>
      </table>
    </td>
    <td width="100%" valign="top"><!-- #BeginEditable "Body" -->
<?php include "vsadmin/db_conn_open.php" ?>
<?php include "vsadmin/inc/languagefile.php" ?>
<?php include "vsadmin/includes.php" ?>
<?php include "vsadmin/inc/incfunctions.php" ?>
<?php include "vsadmin/inc/incaffiliate.php" ?>
      <!-- #EndEditable -->
    </td>
  </tr>
  <tr>
    <td width="130" bgcolor="#3399CC">&nbsp;</td>
    <td class="smaller"  width="100%" align="center"> 
      <hr/>
      <p class="smaller"><a href="index.php">Home</a> - <a href="categories.php">Products</a> 
        - <a href="search.php">Search</a> - <a href="cart.php">Checkout</a></p>
      <p class="smaller"> &copy; Copyright <a href="http://www.ecommercetemplates.com/" target="_blank">Shopping 
        cart software</a> by Ecommercetemplates.com</p>
    </td>
  </tr>
</table>
</body>
<!-- #EndTemplate --></html>
