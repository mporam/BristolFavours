<?php
session_cache_limiter('none');
session_start();
ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
<?php
require("head.html");
?>

<div id="nav">
<?php
include("nav.html");
?>
</div>


<div class="clear"></div>

<div id="body" class="left">
    <div class="title">
    	<img src="images/prod_desc.gif" alt="Product Description" />
	</div>
    
<?php include "vsadmin/inc/incproddetail.php" ?>

<div class="shop" onclick="history.go(-1)">Continue Shopping</div>

<!--<?php include "vsadmin/inc/increcentview.php" ?> -->

</div>
<div id="right_column" class="right">
<a href="special_offers.php"><img src="images/banners/banner2.jpg" width="268" height="356" class="first" alt="Bath Confetti Roses &pound;4.50 OFF - View now &gt;"  /></a>
</div>

<div class="clear"></div>

<?php
require("footer.html");
?>

</div>
</body>
</html>
