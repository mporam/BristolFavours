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
    <img src="images/home_consultations.gif" alt="Home Consultations" />
    </div>  
<strong>Home Consultation Service</strong> 

<p>Ensuring that your favours match the other details of your wedding can be a time consuming and difficult process, which is why we offer a home consultation service.</p>

<p>A Bristol Favours representative will arrange to visit at a time to suit you, and work with you to create beautiful favours that will integrate seamlessly into your special day.</p>

<p>Whether you&rsquo;ve planned your favours down to the tiniest detail and just need assistance with the execution, you know what kind of filling you want but are unsure of how to present it, or are still looking for inspiration we are more than happy to help. We are always excited to hear new ideas, and share our own, in order to achieve your perfect wedding  favours.</p>

<p>What to expect:<br />
<br />
Upon arranging your consultation, we will ask you for some details about your preferences and what you want from your wedding favours, for example:</p>
<ul>
	<li>The theme or colour scheme of your wedding.</li>
	<li>Your preferences regarding the outer and filling of your favours.</li>
	<li>The number of guests (how many favours will be needed).</li>
	<li>Your expected budget.</li>
	<li>The date of the wedding.</li>
</ul>
<p>With this information, we will use our expertise to create a number of different examples of favours for you which we will then bring to the consultation, along with plenty of other samples so that you can make a truly informed decision. You can then see how these complement your colour schemes etc., and we can discuss any ideas you may have to come up with your final design. We can also make other arrangements such as whether you would like your favours delivered to your home address or to the venue.</p>

<p>Home consultations are completely free of charge and to book yours please get in touch via the <a href="contact.php">Contact Us</a> page.</p>

<p>Although you can book your consultation at any time, we ask that you book at least 6 weeks prior to the wedding day in order to ensure your favours are ready in time.</p>
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
