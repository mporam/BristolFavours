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
    <img src="images/about_us.gif" alt="About Us" />
    </div>  
<p>As anyone who has ever been to one knows: no party is complete without the <a href="wedding_favours.php">favours</a> &ndash; whether it&rsquo;s five traditional sugared almonds to wish your wedding guests prosperity, or a <a href="products.php?cat=11">bag</a> of goodies for your little one&rsquo;s friends to take home with their slice of the birthday cake &ndash; the favours are your guests&rsquo; lasting reminder of your special day.</p>

<p>It is our firm belief that the favours should be just as wonderful as the occasion, but there are enough stresses in the hustle and bustle of modern life without having to worry about whether or not your party favours will match the serviettes. That&rsquo;s why we will design, create and deliver your favours, giving you one less thing to worry about!</p>

<p>We also stock &lsquo;<a href="products.php?cat=26">thank you</a>&rsquo; gifts for the most important members of the wedding party (other than the bride and groom of course!), a number of <a href="accessories.php">accessories</a> such as table confetti and party poppers, to add a little sparkle to your party, and a selection of brilliantly unusual children&rsquo;s party <a href="products.php?cat=11">bags</a>.</p>

<p>If you have any comments or suggestions, or if there are any party items you would like us to source for you, please get in touch via the &lsquo;<a href="contact.php">Contact Us</a>&rsquo; page!</p>

<p>Thank you for visiting our site, we hope you enjoy browsing!</p>
<div class="clear"></div>



</div>
<div id="right_column" class="right">
<a href="special_offers.php"><img src="images/banners/banner2.jpg" width="268" height="356" class="first" alt="Bath Confetti Roses &pound;4.50 OFF - View now &gt;"  /></a>
<a href="products.php?cat=12&pg=2"><img src="images/banners/banner1.jpg" width="268" height="356" alt="Ribbon Box - View now &gt;"  /></a>
</div>

<div class="clear"></div>

<?php
require("footer.html");
?>

</div>
</body>
</html>
