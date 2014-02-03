<?php

  $name = $_REQUEST['name'] ;
  $email = $_REQUEST['email'] ;
  $message = $_REQUEST['message'] ;
  
  $text = 
  "From: ".$email."
Name: ".$name."

Message: ".$message;

  mail( "info@bristolfavours.co.uk", "Contact Us",
    $text, "From: $email" );
  header('Refresh: 0; url="contact.php"');
?>