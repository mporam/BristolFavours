<?php
session_cache_limiter('none');
session_start();
ob_start();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

    <link rel="icon" href="favicon.ico" type="image/ico" />
    <link rel="shortcut icon" href="favicon.ico" type="image/ico" />
    
	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="css/default.css" type="text/css" media="screen" />
    
	<link rel="stylesheet" href="css/default2.css" type="text/css" media="screen" />
    <link href="css/dropdown.css" media="screen" rel="stylesheet" type="text/css" />
    <link href="css/default.advanced.css" media="screen" rel="stylesheet" type="text/css" />
    
    <!--[if lte IE 7]>
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.dropdown.js"></script>
    <![endif]-->
    
	<title>Bristol Favours</title>
</head>
<body>
<div id="container">
    <div id="head">
        <div id="logo" class="left">Bristol Favours</div>
        
        <div id="basket" class="right" onclick="document.location = 'cart.php'">
        <?php include "vsadmin/db_conn_open.php" ?>
		<?php include "vsadmin/inc/languagefile.php" ?>
        <?php include "vsadmin/includes.php" ?>
        <?php include "vsadmin/inc/incfunctions.php" ?>
        <table width="130" bgcolor="#FFFFFF" class="mincart">
        <tbody><tr> 
          <td bgcolor="#F0F0F0" align="center" class="mincart"><img width="16" height="15" align="top" alt="Shopping cart" src="images/littlecart1.gif"> 
            &nbsp;<strong><a href="http://www.bristolfavours.co.uk/preview2/cart.php" class="ectlink mincart">Shopping cart</a></strong></td>
        </tr>
        <tr><td bgcolor="#F0F0F0" align="center" class="mincart">0 Product(s) in cart</td></tr>
      </tbody></table>
        </div>
    </div>

<span class="details"><a href="mailto:info@bristolfavours.co.uk">info@bristolfavours.co.uk</a> | 07974 449 801</span>

<div id="nav">
<?php
require("nav.html");
?>
</div>

<div class="clear"></div>

<?php
// unset cookies
if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time()-1000);
        setcookie($name, '', time()-1000, '/');
    }
}
?>
<div id="bodyFull" class="left">
    <div class="titleFull">
    	<img src="images/cart.gif" alt="Cart" />
    </div>

    <?php include "vsadmin/inc/incsorry.php" ?>

</div>

<div class="clear"></div>

<?php
require("footer.html");
?>

</div>
</body>
</html>
