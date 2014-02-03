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
    <img src="images/gifts.gif" alt="Gifts" />
    </div> 
    
    <p>Although the wedding day is all about the happy couple, it just wouldn&rsquo;t be the same without all your friends and family members. These gifts are to thank all the people who help you through every stage of the marriage process, from helping pick out the perfect engagement ring and organising the stag and hen parties, to welcoming the guests and looking after the rings. Show them just how invaluable they are with our fabulous range of gifts. </p>

		<a href="products.php?cat=11" class="subitemtitle">&lsquo;Top Table&rsquo; Ladies</a>
   		<a href="products.php?cat=12" class="subitemtitle">&lsquo;Top Table&rsquo; Gentlemen</a>
   		<a href="products.php?cat=13" class="subitemtitle">Flower Girl &amp; Page Boy</a>
    	<div class="subitem"><a href="products.php?cat=41"><img src="images/ladies.jpg" width="300" height="90" alt="Top Table Ladies" /></a></div>
        <div class="subitem"><a href="products.php?cat=42"><img src="images/gentlemen.jpg" width="300" height="90" alt="Top Table Gentlemen" /></a></div>
        <div class="subitem"><a href="products.php?cat=23"><img src="images/flowers.jpg" width="300" height="90" alt="gift" /></a></div>
        <a href="products.php?cat=11" class="subitemtitle hide">hide</a>
   		<a href="products.php?cat=26" class="subitemtitle">Thank You</a>
   		<a href="products.php?cat=13" class="subitemtitle hide">hide</a>
        <div class="subitem hide"></div>
        <div class="subitem"><a href="products.php?cat=26"><img src="images/thanks.jpg" width="300" height="90" alt="Thank Yuo" /></a></div>
    	
        <div class="clear"></div>
    



</div>


<div class="clear"></div>

<?php
require("footer.html");
?>

</div>
</body>
</html>
