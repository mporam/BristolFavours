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
    

    
    <script type="text/javascript" src="js/paging.js"></script>
    <script type="text/javascript" src="js/xml.js"></script>
    <script type="text/javascript">
		// perform JavaScript after the document is scriptable.
$(function() {
	// setup ul.tabs to work as tabs for each div directly under div.panes
	$("ul.tabs").tabs("div.panes > div");
});

xmlDoc=loadXMLDoc("xml/homepage.xml");
</script>
   
    <!--[if lte IE 7]>
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.dropdown.js"></script>
    <![endif]-->
    
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

<div id="body" class="left">
    <div class="title">
    <img src="images/latest_products.gif" alt="Latest Products" />
    </div>
    
<?php include "vsadmin/db_conn_open.php" ?>
<?php include "vsadmin/inc/languagefile.php" ?>
<?php include "vsadmin/includes.php" ?>
<?php include "vsadmin/inc/incfunctions.php" ?>
<?php include "vsadmin/inc/incorderstatus.php" ?>
      
</div>
<div id="right_column" class="right">
<a href="#"><img src="images/flower_banner.jpg" width="268" height="356" alt="View our Exquisit Wedding Flowers"  /></a>
</div>

<div class="clear"></div>

<?php
require("footer.html");
?>

</div>
</body>
</html>




