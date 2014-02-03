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
    

    <script type="text/javascript" src="js/jquery.js"></script>
    <!--[if lte IE 7]>
    <script type="text/javascript" src="js/jquery.dropdown.js"></script>
    <![endif]-->
    
	<title>Bristol Favours</title>
</head>
<body>
<div id="container">
<?php
require("head.html");
?>

<div id="nav">
<?php
include("nav.html");
?>
</div>


<div class="clear"></div>

<div id="bodyFull" class="left">
    <div class="titleFull">
    	<img src="images/cart.gif" alt="Cart" />
    </div>

    <?php include "vsadmin/inc/inccart.php" ?>

</div>

<div class="clear"></div>

<?php
require("footer.html");
?>

</div>
</body>
</html>
