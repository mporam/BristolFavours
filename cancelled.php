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


<div id="bodyFull" class="left">
    <div class="titleFull">
    <img src="images/cart.gif" alt="Thank You" />
    </div>
    
<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
        <tbody><tr>
          <td width="100%">
            <table width="100%" cellspacing="3" cellpadding="3" border="0">
			  <tbody><tr> 
                <td width="100%" align="center" colspan="2"><br><strong>Your order has been cancelled</strong><br><br>If you need any help with your purchase, then please be sure to contact us.<br /><br />								<a href="http://www.bristolfavours.co.uk/preview2/" class="ectlink"><strong>Continue Shopping</strong></a>
                </td>
			  </tr>
			</tbody></table>
		  </td>
        </tr>
      </tbody></table>
      
</div>

<div class="clear"></div>

<?php
require("footer.html");
?>

</div>
</body>
</html>
