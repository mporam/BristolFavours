<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="css/default.css" type="text/css" media="screen" />
    
	<link rel="stylesheet" href="css/default2.css" type="text/css" media="screen" />
    <link href="css/dropdown.css" media="screen" rel="stylesheet" type="text/css" />
    <link href="css/default.advanced.css" media="screen" rel="stylesheet" type="text/css" />
    
	<script src="js/paging.js"></script>
    <script type="text/javascript" src="js/xml.js"></script>
    
    <script type="text/javascript">
		$(function() {
		$(".image").click(function() {
		var image = $(this).attr("rel");
		$('#image').hide();
		$('#image').fadeIn('slow');
		$('#image').html('<img src="' + image + '" class="left border"/>');
		return false;
			});
		});
    </script>
    
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
        <div id="basketTxt" class="left">0 Items</div>
        
        <div id="basket" class="right">
        <img src="images/basket.jpg" width="48" height="48" alt="Your Shopping Basket" class="right" />
        </div>
    </div>

<span class="details"><a href="#">info@bristolfavours.co.uk</a> | 07000 000 000</span>

<div id="nav">
<?php
include("nav.html");
?>
</div>


<div class="clear"></div>

<div id="body" class="left">
    <div class="title">
    <div class="pad">Gifts</div>
    </div>  
    <div id="image"><img src="images/products/candles.jpg" class="left border" /></div>
    <div class="left productDetails"><b>Product Name</b><br />
    Product Description<br />
    <span class="cost">&pound;Price</span><br />
	<a href="javascript:void;"><img src='images/add_item.gif' class='img' alt='Add to Basket' /></a><br />
    <a href="javascript: history.go(-1)" class="left">Back to products</a>
    </div>
    
    <div id="thumbs">
    <a href="#" rel="images/products/cake_top.jpg" class="image"><img src='images/products/cake_top.jpg' width='50' height='50' class='left border' />pink</a>
    <a href="#" rel="images/products/perfume.jpg" class="image"><img src='images/products/perfume.jpg' width='50' height='50' class='left border' />green</a>
    <a href="#" rel="images/products/candles.jpg" class="image"><img src='images/products/candles.jpg' width='50' height='50' class='left border' />blue</a>
    <a href="#" rel="images/products/candles.jpg" class="image"><img src='images/products/candles.jpg' width='50' height='50' class='left border' />blue</a>
    <a href="#" rel="images/products/candles.jpg" class="image"><img src='images/products/candles.jpg' width='50' height='50' class='left border' />blue</a>
    <a href="#" rel="images/products/candles.jpg" class="image"><img src='images/products/candles.jpg' width='50' height='50' class='left border' />blue</a>
	</div>




</div>
<div id="right_column" class="right">
<a href="#"><img src="images/flower_banner.jpg" width="268" height="356" class="first" alt="View our Exquisit Wedding Flowers"  /></a>
<a href="#"><img src="images/confetti_banner.jpg" width="268" height="356" alt="View our Classic Flower Confetti"  /></a>
</div>

<div class="clear"></div>

<?php
require("footer.html");
?>

</div>
</body>
</html>
