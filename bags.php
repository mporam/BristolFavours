<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="css/default.css" type="text/css" media="screen" />
    
	<link rel="stylesheet" href="css/default2.css" type="text/css" media="screen" />
    <link href="css/dropdown.css" media="screen" rel="stylesheet" type="text/css" />
    <link href="css/default.advanced.css" media="screen" rel="stylesheet" type="text/css" />
        	<link rel="stylesheet" href="css/lightbox.css" type="text/css" media="screen" />
    <script src="js/paging.js"></script>
    <script type="text/javascript" src="js/xml.js"></script>
    <script type="text/javascript">
		// perform JavaScript after the document is scriptable.
$(function() {
	// setup ul.tabs to work as tabs for each div directly under div.panes
	$("ul.tabs").tabs("div.panes > div");
});

xmlDoc=loadXMLDoc("xml/product1.xml");
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
    <img src="images/bags.gif" alt="Bags" />
    </div>  

<div class="clear"></div>
<!-- tab "panes" -->
<div class="panes">
	<div>     
<script type="text/javascript">
var x=xmlDoc.getElementsByTagName("product1");
for (i=0;i<x.length;i++)
  {
  document.write("<div class='item left'><span class='name'>");
  document.write(x[i].getElementsByTagName("name")[0].childNodes[0].nodeValue);
  document.write("</span><img src='images/products/");
  document.write(x[i].getElementsByTagName("image")[0].childNodes[0].nodeValue);
  document.write("' class='img' /><span class='price'>&pound;");
  document.write(x[i].getElementsByTagName("price")[0].childNodes[0].nodeValue);
  document.write("</span><a href='productID=");
  document.write(x[i].getElementsByTagName("ID")[0].childNodes[0].nodeValue);
  document.write(".php' /><img src='images/view_item.gif' class='img' alt='View Item' /></a></div>");
  }
</script>  
	</div>
</div>
<div class="clear"></div>



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
