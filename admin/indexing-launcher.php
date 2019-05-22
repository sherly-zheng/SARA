<?php
  $msg = "Please enter a URL to index:";
  //Connect to QC Server
  $serverName = "149.4.211.180";
  $user = "zhsh6528";
  $psw = "14226528";
  $conn = new mysqli($serverName, $user, $psw);
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  if($_SERVER['REQUEST_METHOD'] == "POST") {
    $url = $_POST["url"];
    indexPage($url);
  }

  function indexPage($url){
    $canIndex = true;
    
    //check if url can be reached
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_exec($curl);
    if(curl_getinfo($curl, CURLINFO_HTTP_CODE) == 404){
      $GLOBALS["msg"] = "The URL entered could not be accessed. Please try entering a different URL:";
    }
    else {
      $url = preg_replace("/(\W*)$/", "", trim($url));   
      $pageContents = file_get_contents($url);
      $title = "No title available";
      $description = "No description available";
      $lastModified = "1999-09-09 00:00:00";
      $lastIndexed = "";
      $timeToIndex = 0;
      $start = microtime(true);

      //get metadata
      $tags = get_meta_tags($url);
      $headers = get_headers($url, 1);
      if(!empty($tags["description"]))
        $description = $tags["description"];
      if(!empty($headers["Last-Modified"])){
        $lastModified = $headers["Last-Modified"];
        $lastModified = new DateTime($lastModified, new DateTimeZone('UTC'));
        $lastModified = $lastModified->format("Y-m-d h:i:s");
      }
      if(preg_match('/<title>(.*?)<\/title>/', $pageContents, $titleMatch)) {
          $title = $titleMatch[1];
      }
      $lastIndexed = date("Y-m-d h:i:s");
      $lastIndexed = new DateTime($lastIndexed, new DateTimeZone('UTC'));
      $lastIndexed = $lastIndexed->format("Y-m-d h:i:s");
    
      $sqlFindPage = 
        "SELECT lastModified, lastIndexed
        FROM zhsh6528.page
        WHERE url = LOWER('".$url."');";
      $q = $GLOBALS['conn']->query($sqlFindPage);
      if($q){
        $numResults = $q->num_rows;
        if($numResults > 0) {
          $row = $q->fetch_assoc();
          $daysPassed = strtotime($lastIndexed)-strtotime($row["lastIndexed"]);
          $daysPassed = abs(round($daysPassed/86400));
          if($row["lastModified"] == $lastModified && $daysPassed < 7){
            $canIndex = false;
            $GLOBALS["msg"] = "Your page has previously been indexed and its contents have not been modified.</br> Click <a href='../search-by/our-search-engine.html'>here</a> to search all indexed URL(s) or enter another URL to index:";
          }
        }
      }
        
      if($canIndex == true) {
        $pageContents = preg_replace("/\s+/", " ", trim(strip_tags($pageContents)));
        $words = explode(" ", $pageContents);
        $sqlGetPage = $GLOBALS['conn']->prepare(
          "SELECT pageID FROM zhsh6528.page
          WHERE url = LOWER(?);"
        );
        $sqlInsertPage = $GLOBALS['conn']->prepare(
          "INSERT INTO zhsh6528.page (url, title, description, lastModified, lastIndexed, timeToIndex)
          VALUES (LOWER(?),?,?,?,?, 0);");
        $sqlGetWord = $GLOBALS['conn']->prepare(
          "SELECT wordID
          FROM zhsh6528.word
          WHERE UPPER(?) = UPPER(wordName);");
        $sqlInsertWord = $GLOBALS['conn']->prepare(
          "INSERT INTO zhsh6528.word (wordName) VALUES (?);");
        $sqlGetPageWord = $GLOBALS['conn']->prepare(
          "SELECT pageWordID, freq
          FROM zhsh6528.page_word
          WHERE pageID = ? AND wordID = ?;");
        $sqlUpdatePageWord = $GLOBALS['conn']->prepare(
          "UPDATE zhsh6528.page_word
          SET freq = ?
          WHERE pageWordID = ?;");
        $sqlInsertPageWord = $GLOBALS['conn']->prepare(
          "INSERT INTO zhsh6528.page_word(pageID, wordID, freq)
          VALUES (?, ?, ?);");
        $sqlUpdatePage = $GLOBALS['conn']->prepare(
          "UPDATE zhsh6528.page 
          SET timeToIndex = ?
          WHERE pageID = ?;");

        //get pageID if page already exists in page table. if not, insert it.
        $sqlGetPage->bind_param("s", $pageUrl);
        $pageUrl = $url;
        $sqlGetPage->execute();
        $result = $sqlGetPage->get_result();
        $row = $result->fetch_assoc();
        if(isset($row["pageID"]))
            $pageID = $row["pageID"];
        else {
            $sqlInsertPage->bind_param("sssss", $pageUrl, $pageTitle, $pageDescript, $dateMod, $dateIndex);
            $pageUrl = $url;
            $pageTitle = $title;
            $pageDescript = $description;
            $dateMod = $lastModified;
            $dateIndex = $lastIndexed;
            if(!$sqlInsertPage->execute()) echo mysqli_error($GLOBALS["conn"]);
            $pageID = mysqli_insert_id($GLOBALS["conn"]);
        }

        foreach ($words as $index=>$word) {
          //if word is not a number or a single special character
          $numberRegEx = "/[0-9]+(,)*/";
          $specialCharRegEx = "/\W/";
          if(preg_match($numberRegEx, $word) !== 1 && preg_match($specialCharRegEx, $word) !== 1){
            $sqlGetWord->bind_param("s", $curWord);
            $curWord = $word;
            $sqlGetWord->execute();
            $result = $sqlGetWord->get_result();
            $row = $result->fetch_assoc();
            //check if word is in word table. if not in word table, insert it.
            if(isset($row["wordID"]))
              $wordID = $row["wordID"];
            else {
              $sqlInsertWord->bind_param("s", $curWord);
              $curWord = $word;
              $sqlInsertWord->execute();
              $wordID = mysqli_insert_id($GLOBALS["conn"]);
            }

            //check if word is in page
            $sqlGetPageWord->bind_param("ii", $curPageID, $curWordID);
            $curPageID = $pageID;
            $curWordID = $wordID;
            $sqlGetPageWord->execute();
            $result = $sqlGetPageWord->get_result();
            $row = $result->fetch_assoc();
            if(isset($row["pageWordID"])){
              $pageWordID = $row["pageWordID"];
              $freq = $row["freq"];
              $sqlUpdatePageWord->bind_param("ii", $newFreq, $curPageWordID);
              $newFreq = $freq + 1;
              $curPageWordID = $pageWordID;
              $sqlUpdatePageWord->execute();
            }
            else{
              $sqlInsertPageWord->bind_param("iii", $curPageID, $curWordID, $freq);
              $curPageID = $pageID;
              $curWordID = $wordID;
              $freq = 1;
              $sqlInsertPageWord->execute();
            }
          }
        }
        $sqlUpdatePage->bind_param("di", $timeToIndex, $curPageID);
        $timeToIndex = microtime(true) - $start;
        $curPageID = $pageID;
        if($sqlUpdatePage->execute())
          $GLOBALS["msg"] = "Success! Click <a href='../search-by/our-search-engine.html'>here</a> to search all indexed URL(s) or enter another URL to index:";
        
        $sqlGetPage->close();
        $sqlInsertPage->close();
        $sqlGetWord->close();
        $sqlInsertWord->close();
        $sqlGetPageWord->close();
        $sqlUpdatePageWord->close();
        $sqlInsertPageWord->close();
        $sqlUpdatePage->close();
      }
    }
    curl_close($curl);
  }
  $conn->close();
?><!DOCTYPE html> 
<html>
  <head> 
    <title>Indexing Launcher | SARA</title> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Sherly Zheng">
    <meta name="description" content="Index a webpage into our SARA search engine">
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
        <a href="../admin/indexing-launcher.php" class="active">Indexing Launcher</a>
        <a href="../admin/search-hist.php">Search History & Stats</a>
      </div>
    </div>
    <a href="javascript:void(0);" class="hamburger" onclick="navBarFunc()">
      <i class="fa fa-bars"></i>
    </a>
  </nav>
  <body> 
    <div class="adminpage">
      <div class="adminpagetitle">
        <h1>Indexing Launcher</h1>
        <p><?php echo $msg ?></p>
      </div>
      <div class="searchbar">
      <form class="searchform" action="indexing-launcher.php" method="POST" onsubmit="return checkURL()">     
        <input type="text" id="url" name="url" placeholder="https://www.google.com" autocomplete="off" required>
        <button type="submit" id="searchbtn" class="enterurlbtn">
          Go
        </button>
      </form>
    </div>
    <script src="../scripts/functions.js"></script>
  </body> 
</html>

