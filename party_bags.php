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
    <img src="images/party_bags.gif" alt="Party Bags" />
    </div>
    
    <p>Children these days aren&rsquo;t half as easily amused as they used to be and all of a sudden rectangular plastic bags filled with a balloon, a party blower and a few sweets just aren&rsquo;t capturing kids&rsquo; imaginations any more.</p>

<p>So we&rsquo;ve created a selection of more exciting children&rsquo;s party bags, appealing to the princess and the pirate inside every little girl and boy. With a range of bags to suit children between the ages of 3 and 12 years, you&rsquo;re sure to find the perfect party bag that&rsquo;ll make your child the pride of the playground!<br />
There are also plenty of extra items available to add to our set party bags for parents who can&rsquo;t resist treating their little ones.</p>

<p>Party favours don&rsquo;t just have to be for birthdays, and certainly not just for children, so if you&rsquo;d like to give your guests a piece of the fun to take home with them, please get in touch to discuss your ideas!</p>
		
        <a href="products.php?cat=33" class="subitemtitle">Girls &amp; Boys 3 to 5</a>
        <a href="products.php?cat=34" class="subitemtitle">Girls 5 to 8</a>
        <a href="products.php?cat=35" class="subitemtitle">Girls 8 to 12</a>
    	<div class="subitem"><a href="products.php?cat=33"><img src="images/3to5.jpg" width="300" height="90" alt="gift" /></a></div>
        <div class="subitem"><a href="products.php?cat=34"><img src="images/gift.gif" width="300" height="90" alt="gift" /></a></div>
        <div class="subitem"><a href="products.php?cat=35"><img src="images/gift.gif" width="300" height="90" alt="gift" /></a></div>
        <a href="products.php?cat=36" class="subitemtitle">Boys 5 to 8</a>
        <a href="products.php?cat=37" class="subitemtitle">Boys 8 to 12</a>
        <a href="products.php?cat=38" class="subitemtitle">Additional Fillers</a>
        <div class="subitem"><a href="products.php?cat=36"><img src="images/gift.gif" width="300" height="90" alt="gift" /></a></div>
        <div class="subitem"><a href="products.php?cat=37"><img src="images/gift.gif" width="300" height="90" alt="gift" /></a></div>
        <div class="subitem"><a href="products.php?cat=38"><img src="images/gift.gif" width="300" height="90" alt="gift" /></a></div>
    	
        <div class="clear"></div>
    



</div>


<div class="clear"></div>

<?php
require("footer.html");
?>

</div>
</body>
</html>
