<?php
  $searchHist = "";
  //Connect to QC Server
  $serverName = "149.4.211.180";
  $user = "zhsh6528";
  $psw = "14226528";
  $conn = new mysqli($serverName, $user, $psw);
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $sqlGetHist = 
    "SELECT searchDate, terms, count, timeToSearch
    FROM zhsh6528.search
    ORDER BY searchID desc;";
  $q = $GLOBALS['conn']->query($sqlGetHist);
  $numResults = $q->num_rows;
  if($numResults > 0) {
    while($row = $q->fetch_assoc()){
      $searchDate = $row["searchDate"];
      $terms = $row["terms"];
      $count = $row["count"];
      $timeToSearch = $row["timeToSearch"];
      $GLOBALS['searchHist'] = $GLOBALS['searchHist'] . 
      "<tr>
        <td>".$searchDate."</td>
        <td>".$terms."</td>
        <td>".$count."</td>
        <td>".$timeToSearch."</td>
      </tr>";
    }
  }



?><!DOCTYPE html> 
<html>
  <head> 
    <title>Search History & Stats | SARA</title> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        <a href="../about/developer.html">Developer</a>
        <a href="../about/contact-us.php">Contact Us</a>
      </div>
    </div>
    <div class="dropdown">
      <button class="dropbtn">Admin
        <i class="fa fa-caret-down"></i>
      </button>
      <div class="dropdown-content">
        <a href="../admin/indexing-launcher.php">Indexing Launcher</a>
        <a href="../admin/search-hist.php" class="active">Search History & Stats</a>
      </div>
    </div>
    <a href="javascript:void(0);" class="hamburger" onclick="navBarFunc()">
      <i class="fa fa-bars"></i>
    </a>
  </nav>
  <body> 
    <h1>Search History</h1>
    <table>
      <tr class="tableheader">
        <th>Search Date</th>
        <th>Search Terms</th>
        <th>Search Count</th>
        <th>Search Time</th>
      </tr>
      <?php echo $searchHist ?>
    </table>
    <script src="../scripts/functions.js"></script>
  </body> 
</html>

