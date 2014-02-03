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

<div id="bodyFull" class="left">
    <div class="titleFull">
    <img src="images/wedding_favours.gif" alt="Wedding Favours" />
    </div>
    
    <p>Your wedding is one of the most magical times of your life, it is an event that is anticipated by all and remembered forever.  At Bristol Favours we want to play a part in your special day by sharing our passion for perfection with you. Wedding favours are our area of expertise and we hope to use our experience to help you make your day simply perfect.</p>

<p>We understand how important the details of the day are to you and it is our aim to take the stress out of some of the planning process, which is why we offer a home consultation service where we will meet with you in the comfort of your own home to discuss your thoughts and ideas.  We want to understand your themes and match your colours whilst at the same time showing you the quality of our service &ndash; no more taking a chance on things you have never seen.</p>

		<a href="products.php?cat=11" class="subitemtitle">Bags</a>
   		<a href="products.php?cat=12" class="subitemtitle">Boxes</a>
   		<a href="products.php?cat=13" class="subitemtitle">Tins</a>
    	<div class="subitem"><a href="products.php?cat=11"><img src="images/bags.jpg" width="300" height="90" alt="Bags" /></a></div>
        <div class="subitem"><a href="products.php?cat=12"><img src="images/box.jpg" width="300" height="90" alt="Box" /></a></div>
        <div class="subitem"><a href="products.php?cat=13"><img src="images/tins.jpg" width="300" height="90" alt="Tins" /></a></div>
        <a href="products.php?cat=14" class="subitemtitle">Tulles</a>
   		<a href="products.php?cat=17" class="subitemtitle">Wraps</a>
   		<a href="products.php?cat=18" class="subitemtitle">Other</a>
        <div class="subitem"><a href="products.php?cat=14"><img src="images/tulles.jpg" width="300" height="90" alt="Tulles" /></a></div>
        <div class="subitem"><a href="products.php?cat=17"><img src="images/tissue.jpg" width="300" height="90" alt="Wraps" /></a></div>
        <div class="subitem"><a href="products.php?cat=18"><img src="images/other.jpg" width="300" height="90" alt="Other" /></a></div>
        <a href="products.php?cat=44" class="subitemtitle">Torta Bomboniera</a>
        <a href="products.php?cat=19" class="subitemtitle">Fillings</a>
   		<a href="products.php?cat=20" class="subitemtitle">Decorations</a>
        <div class="subitem"><a href="products.php?cat=44"><img src="images/torta.jpg" width="300" height="90" alt="Torta Bomboniera" /></a></div>
        <div class="subitem"><a href="products.php?cat=19"><img src="images/fillings.jpg" width="300" height="90" alt="Fillings" /></a></div>
        <div class="subitem"><a href="products.php?cat=20"><img src="images/decorations.jpg" width="300" height="90" alt="Decorations	" /></a></div>
    	
        <div class="clear"></div>
    



</div>


<div class="clear"></div>

<?php
require("footer.html");
?>

</div>
</body>
</html>
