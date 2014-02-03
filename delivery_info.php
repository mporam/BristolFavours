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
    <img src="images/delivery_info.gif" alt="Delivery Information" />
    </div>  
<p>All orders will be delivered by Royal Mail or Courier. All orders will be charged a flat rate of &pound;6.</p>

<p>All postal orders will be sent using Recorded Delivery and will therefore need to be signed for. If you miss your delivery, the usual Royal Mail procedures will apply.</p>

<p>Orders sent by Royal Mail may be affected by postal strikes, etc. and we cannot accept responsibility for delays once your order has been dispatched.</p>

<p>We aim to dispatch all postal orders within 3 working days, however, if stock is not immediately available or for custom made items you will be notified when your order has been shipped and the date you can expect your order.</p>

<p>For all orders, postal or other, we advise that you allow approximately 3 to 4 weeks prior to the special day in order to make sure you receive everything in plenty of time and avoid disappointment.</p>

<p>Please note: at present, we can only ship to UK mainland addresses.</p>

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
