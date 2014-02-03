<?php
session_cache_limiter('none');
session_start();
ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="css/default.css" type="text/css" media="screen" />
    
	<link rel="stylesheet" href="css/default2.css" type="text/css" media="screen" />
    <link href="css/dropdown.css" media="screen" rel="stylesheet" type="text/css" />
    <link href="css/default.advanced.css" media="screen" rel="stylesheet" type="text/css" />
    <!--[if lte IE 7]>
    <link rel="stylesheet" href="css/ie7.css" type="text/css" media="screen" />
    <![endif]-->
    <!--[if IE 8]>
    <link rel="stylesheet" href="css/ie8.css" type="text/css" media="screen" />
    <![endif]-->
   
    <!--[if lte IE 7]>
    <script type="text/javascript" src="js/jquery.dropdown.js"></script>
    <![endif]-->
    
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/imageCycle.js"></script>
    <script type="text/javascript">
$(function() {
if (!$('.pics').length > 0) return false;
$('.pics').cycle('fade');
});
</script>
    
    <link rel="icon" href="favicon.ico" type="image/ico" />
    <link rel="shortcut icon" href="favicon.ico" type="image/ico" />
    
	<title>Bristol Favours</title>
</head>
<body style="background:url(images/flowerbg.jpg) top left;">
<div id="container">
<?php
require("head.html");
?>

<div id="nav">
<?php
require("nav.html");
?>
</div>

<div class="clear"></div>

<div id="homeHeader">

    <div id="banner" class="pics left">
    	<a href="products.php?cat=44"><img src="images/banners/torta_banner.jpg" width="680" height="330" alt="Torta Bomboniera - View All" /></a>
    	<a href="proddetail.php?prod=101%2F19"><img src="images/banners/hearts_banner.jpg" width="680" height="330" alt="Hearts - View All" /></a>
    	<a href="products.php?cat=11"><img src="images/banners/bags_banner.jpg" width="680" height="330" alt="Bags - View All" /></a>
    	<a href="search.php?pg=1&stext=pillow+box&sprice=&stype=&scat="><img src="images/banners/pillowbox_banner.jpg" width="680" height="330" alt="Pillow Boxes - View All" /></a>
    </div>
    
    <div id="table" class="right">
    <table width="270" cellpadding="25px" cellspacing="0" align="center">
    	<tr>
        	<td width="270"><a href="cart.php"><img src="images/my_basket.jpg" alt="My Basket" border="0" /></a><br />Click <a href="cart.php" class="bold">here</a> to view your basket</td>
        </tr>
        <tr>
        	<td><a href="special_offers.php"><img src="images/special_offers.jpg" alt="Special Offers" /></a></td>
        </tr>
        <tr>
        	<td class="news"><img src="images/newsletter.jpg" alt="Newsletter" /><br />
            Sign up for our newsletter:<br />
            <form action="http://eliteweb.createsend.com/t/r/s/qjjgr/" method="post" id="subForm" class="newsletter">
				<input class="text" value="Email Address" type="text" name="cm-qjjgr-qjjgr" id="qjjgr-qjjgr" />
				<input class="button" type="submit" value="&nbsp;" />
			</form>
        	</td>
        </tr>
    </table>
    </div>
</div>

<div class="clear"></div>

<div id="body" class="left">
    <div class="title">
    <img src="images/latest_products.gif" alt="Latest Products" />
    </div>
    
<?php
$explicitid=16;
?>
<?php include "vsadmin/inc/incproducts.php" ?>
      
</div>
<div id="right_column" class="right">
<a href="products.php?cat=12&pg=2"><img class="first" src="images/banners/banner1.jpg" width="268" height="356" alt="Special Offer"  /></a>
</div>

<div class="clear"></div>

<?php
require("footer.html");
?>

</div>
</body>
</html>
