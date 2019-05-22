<?php
  $contactsubject = $contactname = $contactemail = $contactmessage = $confirmation = NULL;
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    $myemail = "D-15r23s9vu3ujb1@maildrop.cc";
    $contactsubject = $_POST["contactsubject"];
    $contactname = $_POST["contactname"];
    $contactemail = $_POST["contactemail"];
    $contactmessage = $_POST["contactmessage"];

    $contactemail = trim($contactemail);
    if (!filter_var($contactemail, FILTER_VALIDATE_EMAIL)) {
      $confirmation = "<h2>Please enter a valid e-mail address.</h2>"; 
      $contactemail = NULL;
    }

    else {
      $mailbody = "Name: $contactname\nEmail: $contactemail\n\n$contactmessage";
      $confirmation = "<h2>Got it! We'll be in touch shortly.</h2>";
      mail($myemail, $contactsubject, $mailbody, "From: $contactname <$contactemail>");
      $contactsubject = $contactname = $contactemail = $contactmessage = NULL;
    }
  }
?>
<!DOCTYPE html> 
<html>
  <head> 
    <title>Contact Us | SARA</title> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Contact the site developer about any questions, comments and concerns.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../stylesheets/layout.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto+Condensed">
    <link rel="SHORTCUT ICON" href="../SARA-icon.png">
  </head> 
  <nav class="topnav" id="topNav">
    <a class="logo" href="../home.html"><img src="../SARA-icon.png" alt="SARA" style="width:50px; height: 50px"></a>
    <div class="dropdown">
      <button class="dropbtn">Search
        <i class="fa fa-caret-down"></i>
      </button>
      <div class="dropdown-content">
        <a href="../search-by/fixed-list.html">Fixed List</a>
        <a href="../search-by/from-file.html">From File</a>
        <a href="../search-by/google-api.html">Google API</a>
        <a href="../search-by/our-search-engine.html">Our Search Engine</a>
      </div>
    </div>
    <div class="dropdown">
      <button class="dropbtn">Course
        <i class="fa fa-caret-down"></i>
      </button>
      <div class="dropdown-content">
        <a href="https://learn.zybooks.com/zybook/CUNYCSCI355TeitelmanSpring2019" target="_blank">Zybook</a>
      </div>
    </div>
    <div class="dropdown">
      <button class="dropbtn">Browser
        <i class="fa fa-caret-down"></i>
      </button>
      <div class="dropdown-content">
        <a href="../browser/navigator.html">Navigator</a>
        <a href="../browser/window.html">Window</a>
        <a href="../browser/screen.html">Screen</a>
        <a href="../browser/location.html">Location</a>
        <a href="../browser/geolocation.html">Geolocation</a>
      </div>
    </div>
    <div class="dropdown">
      <button class="dropbtn">About
        <i class="fa fa-caret-down"></i>
      </button>
      <div class="dropdown-content">
        <a href="developer.html">Developer</a>
        <a href="contact-us.php" class="active">Contact Us</a>
      </div>
    </div>
    <div class="dropdown">
      <button class="dropbtn">Admin
        <i class="fa fa-caret-down"></i>
      </button>
      <div class="dropdown-content">
        <a href="../admin/indexing-launcher.php">Indexing Launcher</a>
        <a href="../admin/search-hist.php">Search History & Stats</a>
      </div>
    </div>
    <a href="javascript:void(0);" class="hamburger" onclick="navBarFunc()">
      <i class="fa fa-bars"></i>
    </a>
  </nav>
  <body>
    <div class="contactuspage">
      <h1>Contact Us</h1>
      <div class="contactus">
      	<?php echo $confirmation;?>
        <form action="contact-us.php" method="post">
          <label for="contactname">Your Name (required)</label>
          <input type="text" id="contactname" name="contactname" value="<?php echo $contactname;?>"required>

          <label for="contactemail">Your Email (required)</label>
          <input type="text" id="contactemail" name="contactemail" value="<?php echo $contactemail;?>"required>

          <label for="contactsubject">Subject</label>
          <input type="text" id="contactsubject" name="contactsubject" value="<?php echo $contactsubject;?>">

          <label for="contactmessage">Your Message (required)</label>
          <textarea id="contactmessage" name="contactmessage" required><?php echo $contactmessage;?></textarea>
          
          <input type="submit" name="submit">
        </form>    
      </div>
    </div>
    <script src="../scripts/functions.js"></script>
  </body> 
</html>
