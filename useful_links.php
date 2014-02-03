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
    <img src="images/useful_links.gif" alt="Useful Links" />
    </div>  
<strong>Title 1</strong>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam viverra, dui sed congue accumsan, purus odio vulputate tellus, vel pellentesque arcu mi eu nibh. Nam mattis vehicula lacus sit amet volutpat. Quisque neque dolor, consectetur non mattis nec, blandit in lacus. Pellentesque rhoncus enim ut justo ullamcorper rutrum. Donec sollicitudin, eros vel sagittis mattis, libero quam lacinia metus, sit amet lobortis leo lacus et nisi. Ut nec sapien mauris. Quisque tincidunt aliquam lacus, sed tempus diam adipiscing nec. Morbi nec lacus nulla. Phasellus non pretium tortor. Curabitur vulputate dui vel ligula ullamcorper ut convallis mauris eleifend. Nullam pharetra dictum lacus ut congue. Donec vitae elementum turpis. Maecenas feugiat condimentum urna non dictum.</p>

<p>Nullam adipiscing auctor velit ut viverra. Sed nunc turpis, pellentesque sed vestibulum vel, imperdiet eu ligula. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam id orci id turpis sodales feugiat id id risus. Donec ligula est, varius at molestie at, gravida et tortor. In ac condimentum orci. Nulla magna leo, mattis placerat aliquam et, auctor ac nunc. Nunc ante tortor, tincidunt vel adipiscing eget, tristique nec felis. Praesent tincidunt feugiat feugiat. Suspendisse dapibus, nulla vel fringilla tempor, leo ipsum sodales lectus, porttitor porta sapien diam in risus.</p>

<p>Nullam malesuada molestie velit, ac sagittis orci dictum ut. Fusce non est neque. Nullam laoreet turpis sit amet sem tincidunt ac congue quam aliquam. Sed interdum odio in lorem porttitor in hendrerit est scelerisque. Sed at nulla at mi congue lobortis facilisis vitae urna. Etiam et erat vel neque ultricies egestas. Nulla a eros eget lorem vehicula consectetur non sit amet risus. Praesent ullamcorper suscipit nulla. Etiam euismod turpis eget lacus ultrices rhoncus. Nulla scelerisque eros tincidunt justo iaculis et euismod ante ullamcorper. Mauris vel diam odio, eget cursus nibh. Nunc urna libero, dictum eu porttitor at, bibendum non neque. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec imperdiet interdum ante, non aliquam augue tincidunt nec. </p>
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
