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
    <img src="images/accessories.gif" alt="Accessories" />
    </div>
    
    <p>It is often the attention to detail which really makes a party stand out. For green&ndash;minded people we have biodegradable confetti; for people who like their parties to go with a bang we have luxury party poppers; and, if you are hopelessly attracted to all things shiny, we have scatter crystals!</p>

<p>If you&rsquo;re a bride to be who wouldn&rsquo;t dream of stepping down the aisle without something old, new, borrowed and blue, our collection of &lsquo;Something Blue&rsquo; charms is not to be missed.</p>

        <a href="products.php?cat=27" class="subitemtitle">Something Blue</a>
        <a href="products.php?cat=40" class="subitemtitle">Confetti</a>
        <a href="products.php?cat=30" class="subitemtitle">Candles</a>
    	<div class="subitem"><a href="products.php?cat=27"><img src="images/blue.jpg" width="300" height="90" alt="Something Blue" /></a></div>
        <div class="subitem"><a href="products.php?cat=40"><img src="images/confetti.jpg" width="300" height="90" alt="Confetti" /></a></div>
        <div class="subitem"><a href="products.php?cat=30"><img src="images/candles.jpg" width="300" height="90" alt="Candles" /></a></div>
        
        <a href="products.php?cat=31" class="subitemtitle">Candle Holders</a>
        <a href="products.php?cat=43" class="subitemtitle">Sparkling Details</a>
        <a class="subitemtitle hide">hide</a>
        <div class="subitem"><a href="products.php?cat=31"><img src="images/candle_holder.jpg" width="300" height="90" alt="Candle Holders" /></a></div>
        <div class="subitem"><a href="products.php?cat=43"><img src="images/sparkling.jpg" width="300" height="90" alt="Sparkling Details" /></a></div>
    	
        <div class="clear"></div>
    



</div>


<div class="clear"></div>

<?php
require("footer.html");
?>

</div>
</body>
</html>
