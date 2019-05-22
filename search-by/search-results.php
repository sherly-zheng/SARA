<?php 
  $results="";  
  $numResults = "";
  $resultList = "";
  $errorMsg = "";
  $resultSearch = "resultspage";
  $searchbarunder = "showsearchbarunder";
  $resultCount = 0;
  $advancedOpt = "";
  //Connect to QC Server
  $serverName = "149.4.211.180";
  $user = "zhsh6528";
  $psw = "14226528";
  $conn = new mysqli($serverName, $user, $psw);
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  //Create ResultsTable if doesn't exist already
  $sqlCheckTable = "SELECT 1 FROM $user.ResultsTable";
  if($conn->query($sqlCheckTable) === FALSE){
    $sqlCreateTable = "CREATE TABLE $user.ResultsTable (
      ResultID int(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      Title varchar(200) NOT NULL,
      URL varchar(200) NOT NULL,
      Description varchar(200) NOT NULL
      )";
    if($conn->query($sqlCreateTable) === FALSE) {
      $errorMsg = $errorMsg. "Error creating table <br>";
    }
  }

  if($_SERVER['REQUEST_METHOD'] == "GET") {
    $searchQ = $_GET["q"];
    $searchBy = $_GET["searchby"];

    //Enter fixed list into database.
    if($searchBy == "fixedlist") {
      //Clear all data from the ResultsTable so we can Insert data
      $sqlClearTable = "TRUNCATE TABLE $user.ResultsTable";
      if($conn->query($sqlClearTable) === FALSE) {
        $errorMsg = $errorMsg. "Error clearing table <br>";
      }


      $resultAry = array (
        array("Software Engineer Jobs, Employment in New York, NY | Indeed.com", "https://www.indeed.com/q-Software-Engineer-l-New-York,-NY-jobs.html", " 12,024 Software Engineer jobs available in New York, NY on Indeed.com. Apply to Software Engineer Intern, Software Engineer, Junior Software Engineer and more!"),
        array("Software Engineer Jobs in New York, NY | Glassdoor", "https://www.glassdoor.com/Job/new-york-software-engineer-jobs-SRCH_IL.0,8_IC1132348_KO9,26.htm", "Search Software Engineer jobs in New York, NY with company ratings & salaries. 7,679 open jobs for Software Engineer in New York."),
        array("11,000+ Software Engineer jobs in New York - LinkedIn", "https://www.linkedin.com/jobs/software-engineer-jobs-new-york", "Top 11000+ Software Engineer jobs in New York. Leverage your professional network, and get hired. New Software Engineer jobs added daily."),
        array("$86k-$124k Software Engineer Jobs in New York City, NY | ZipRecruiter", "https://www.ziprecruiter.com/Jobs/Software-Engineer/-in-New-York-City,NY", "Browse 42,698+ NEW YORK CITY, NY SOFTWARE ENGINEER job ($86K-$124K) listings hiring now from companies with openings. Find your next job opportunity near you!"),
        array("20 Best Software Engineer jobs in New York, NY (Hiring Now!) | Simply Hired", "https://www.simplyhired.com/", "Software Engineer jobs available in New York, NY. See salaries, compare reviews, easily apply, and get hired. New Software Engineer careers in New York, NY are added daily on SimplyHired.com."), 
        array("2019 Software Engineer Salary NYC | Built In NYC", "https://www.builtinnyc.com/salaries/dev-engineer/software-engineer/new-york", "The average salary for a Software Engineer in New York is $118,955. Software Engineer salaries are based on responses gathered by Built In NYC from anonymous Software Engineer employees in NYC."),
        array("Engineering jobs | Engineering jobs at Microsoft", "https://careers.microsoft.com/us/en/c/engineering-jobs?rt=professional", "Apply for Engineering jobs at Microsoft. Browse our opportunities and apply today to a Microsoft Engineering position."),
        array("Developer Jobs at Startups - AngelList","https://angel.co/r/software-engineer/jobs", "Browse developer jobs at startups all over the world. Apply privately. Get salary, equity, and funding info upfront. No recruiters, no spam. 20,000+ startups hiring for 60,000+ jobs."),
        array("Top 20 Software engineering jobs, Now Hiring | Dice.com", "https://www.dice.com/jobs/q-Software+engineering-jobs", "Browse 1-20 of 50,843 available Software engineering jobs on Dice.com. Apply to Entry Level Software Engineer, Software Engineer, Java Developer and more."),
        array("Entry-Level Software Engineer Job Guide | Career Advice &amp; Interview Tips | WayUp Guide", "https://www.wayup.com/guide/entry-level-software-engineer-job-guide/", "Interested in starting a career as a software engineer? This guide has all the info and tips you need to do it right.")
      );

      for($i = 0; $i < 10; $i++) {
        $title = $resultAry[$i][0];
        $url = $resultAry[$i][1];
        $description = $resultAry[$i][2];
        $sqlInsert = "INSERT INTO $user.ResultsTable (Title, URL, Description) VALUES ('$title', '$url', '$description')";
        if ($conn->query($sqlInsert) === FALSE) {
          $errorMsg = $errorMsg. "Error: " . $sqlInsert . "<br>" . $conn->error . "<br>";
        }
      }
      searchDB("%%");
    }

    //Use Google API to search
    if($searchBy == "google") {
      $resultSearch = "google";      
      //Clear all data from the ResultsTable so we can Insert data
      $sqlClearTable = "TRUNCATE TABLE $user.ResultsTable";
      if($conn->query($sqlClearTable) === FALSE) {
        $errorMsg = $errorMsg. "Error clearing table <br>";
      }
      
      $apikey = "AIzaSyBMOmzwLRls7KdAFbCMfyLtoI6_nOGuKqo";
      $cx = "009877674441692590120%3A6finvner-ss";
      $googleurl = "https://www.googleapis.com/customsearch/v1?key=".$apikey."&cx=".$cx."&q=".$searchQ;
      $curl = curl_init();
      curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $googleurl,
        CURLOPT_USERAGENT => "SARA"
      ]);
      $googleresponse = curl_exec($curl);
      if(!curl_exec($curl)) {
        die('Error: "'.curl_error($curl).'" - Code: '.curl_errno($curl));
      }
      curl_close($curl);
      
      $googleresponse = json_decode($googleresponse);
      $items = $googleresponse->items;
      foreach ($items as $item) {
        $title = $item->title;
        $url = $item->link;
        $description = $item->snippet;
        $sqlInsert = "INSERT INTO $user.ResultsTable (Title, URL, Description) VALUES ('$title', '$url', '$description')";
        if ($conn->query($sqlInsert) === FALSE) {
          $errorMsg = $errorMsg. "Error: " . $sqlInsert . "<br>" . $conn->error . "<br>";
        }
      }
      searchDB("%%");
    }

    //Use our engine to search 
    if($searchBy == "ourengine"){
      $resultSearch = "ourengine";
      $caseinsensitive = false;
      $partialsearch = false;
      $checkedci = "";
      $checkedps = "";
      if(isset($_GET["ci"])){
        $caseinsensitive = true;
        $checkedci = "checked";
      }
      if(isset($_GET["ps"])){
        $partialsearch = true;
        $checkedps = "checked";
      }

      if($caseinsensitive && !$partialsearch){
        $where = "UPPER(word.wordName) = UPPER('".$searchQ."')";
      }
      else if(!$caseinsensitive && $partialsearch){
        $where = "word.wordName LIKE '%".$searchQ."%'";
      }
      else if($caseinsensitive && $partialsearch){
        $where = "UPPER(word.wordName) LIKE UPPER('%".$searchQ."%')";
      }
      else {
        $where = "word.wordName = '".$searchQ."'";
      }

      $advancedOpt = 
        '<div class="advanced">
          <label>
            <input type="checkbox" id="caseinsensitive" name="ci" value="true" autocomplete="off" form="searchform" '.$checkedci.'>
            Case Insensitive
          </label>
          <label>
            <input type="checkbox" id="partialsearch" name="ps" value="true" autocomplete="off" form="searchform" '.$checkedps.'>
            Partial Search
          </label>
        </div>';

      //Clear all data from the ResultsTable so we can Insert data
      $sqlClearTable = "TRUNCATE TABLE $user.ResultsTable";
      if($conn->query($sqlClearTable) === FALSE) {
        $errorMsg = $errorMsg. "Error clearing table <br>";
      }
      $start = microtime(true);
      $sqlSelect = 
        "SELECT DISTINCT title, url, description
        FROM zhsh6528.page, zhsh6528.word, zhsh6528.page_word
        WHERE page.pageID = page_word.pageID AND word.wordID = page_word.wordID AND ".$where."
        ORDER BY freq DESC;";
      $q = $GLOBALS['conn']->query($sqlSelect);
      //finish query to select word data, insert into results table, select into results page
      $numResults = $q->num_rows;
      if($numResults > 0) {
        while($row = $q->fetch_assoc()){
          $title = $row["title"];
          $url = $row["url"];
          $description = $row["description"];
          $sqlInsert = "INSERT INTO $user.ResultsTable (Title, URL, Description) VALUES ('$title', '$url', '$description')";
          if ($conn->query($sqlInsert) === FALSE) {
            $errorMsg = $errorMsg. "Error: " . $sqlInsert . "<br>" . $conn->error . "<br>";
          }
        }
      }
      searchDB("%%");
      $searchDate = date("Y-m-d");
      $searchTime = microtime(true) - $start;
      $insertSearch = 
        "INSERT INTO zhsh6528.search (terms, count, searchDate, timeToSearch)
        VALUES ('".$searchQ."', ".$GLOBALS['resultCount'].", '".$searchDate."', ".$searchTime."); ";
      if ($conn->query($insertSearch) === FALSE) {
        $errorMsg = $errorMsg. "Error: " . $insertSearch . "<br>" . $conn->error . "<br>";
      }
    } 
    
    //Search the results page
    if($searchBy == "resultspage") {
      searchDB($searchQ);
    }
  }

  if($_SERVER['REQUEST_METHOD'] == "POST"){
    $searchQ = $_POST["q"];
    $searchBy = $_POST["searchby"];

    //Enter file results into database.
    if($searchBy == "file") {
      //Clear all data from the ResultsTable so we can Insert data
      $sqlClearTable = "TRUNCATE TABLE $user.ResultsTable";
      if($conn->query($sqlClearTable) === FALSE) {
        $errorMsg = $errorMsg. "Error clearing table <br>";
      }

      //Upload file
      $targetDir = "uploads/";
      $targetFile = $targetDir.basename($_FILES["browsefiles"]["name"]);
      $fileExt = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
      $searchBy= $_POST["filenameinput"];
      move_uploaded_file($_FILES["browsefiles"]["tmp_name"], $targetFile);

      //Parse XML file and insert parsed file data into database
      if($fileExt == "xml") {
        $xmlFileContents = simplexml_load_file($targetFile) or die("Error: Unable to load XML File.");
        foreach($xmlFileContents->children() as $result){
          $title = $result->title;
          $url = $result->url;
          $description = $result->description;
          $sqlInsert = "INSERT INTO $user.ResultsTable (Title, URL, Description) VALUES ('$title', '$url', '$description')";
          if ($conn->query($sqlInsert) === FALSE) {
            $errorMsg = $errorMsg. "Error: " . $sqlInsert . "<br>" . $conn->error . "<br>";
          }
        }
      }

      //Parse JSON file and insert parsed file data into database
      if($fileExt == "json") {
        $fileContents = file_get_contents($targetFile);
        $jsonFileContents = json_decode($fileContents, true);
        foreach ($jsonFileContents['Result'] as $result) {
          $title = $result['title'];
          $url = $result['url'];
          $description = $result['description'];
          $sqlInsert = "INSERT INTO $user.ResultsTable (Title, URL, Description) VALUES ('$title', '$url', '$description')";
          if ($conn->query($sqlInsert) === FALSE) {
            $errorMsg = $errorMsg. "Error: " . $sqlInsert . "<br>" . $conn->error . "<br>";
          }
        }
      }

      //Parse CSV file and insert parsed file data into database
      if($fileExt == "csv") {
        $fileContents = trim(file_get_contents($targetFile));
        $cvsFileContents = explode("\n", $fileContents);
        foreach ($cvsFileContents as $csvresults) {
          $result = explode(",", $csvresults);
          $title = $result[0];
          $url = $result[1];
          $description = $result[2];
          $sqlInsert = "INSERT INTO $user.ResultsTable (Title, URL, Description) VALUES ('$title', '$url', '$description')";
          if ($conn->query($sqlInsert) === FALSE) {
            $errorMsg = $errorMsg. "Error: " . $sqlInsert . "<br>" . $conn->error . "<br>";
          }
        }
      }
  /*    $Qstrings = explode(" ", $searchQ);
      foreach ($Qstrings as $string) {
        if($string !== ""){
          searchDB("%". $string ."%");
        }
        else {
          searchDB("");
        }
      } */
      searchDB($searchQ);
    } 
  }

  //Function to get results of query q in database
  function searchDB($q) {
    $sqlGetQ = "SELECT ResultID, Title, URL, Description FROM ".$GLOBALS['user'].".ResultsTable WHERE ";
    $Qstrings = explode(" ", $q);
    $q = "";
    for($i = 0; $i < count($Qstrings); $i++) {
      if($Qstrings[$i] !== ""){
        $q = "%". $Qstrings[$i] ."%";
      }
      $sqlGetQ = $sqlGetQ."lower(Description) LIKE lower('".$q."') OR lower(Title) LIKE lower('".$q."')";
      if($i < count($Qstrings)-1) {
        $sqlGetQ = $sqlGetQ." OR ";
      }
      else {
        $sqlGetQ = $sqlGetQ.";";
      }
    }

    $qResult = $GLOBALS['conn']->query($sqlGetQ);
    $numResults = $qResult->num_rows;
    if($numResults > 0) {
      while($row = $qResult->fetch_assoc()){
        $resultid = $row["ResultID"];
        $title = $row["Title"];
        $url = $row["URL"];
        $description = $row["Description"];
        $GLOBALS['resultList'] = $GLOBALS['resultList'] . 
        "<div class='result' id='result".$numResults."'>
          <label class='checkbox'>
            <input type='checkbox' class='resultcheckbox' id='".$resultid."' onclick='selectResult()' autocomplete='off'> 
            <span class='checkmark'></span>
          </label>
          <div class='resultdata'>
            <h2 class='title'>".$title."</h2>
            <p class='url'><a href=".$url.">".$url."</a></p>
            <p class='description'>".$description."</p>
          </div>
        </div>";
        $GLOBALS['resultCount']++;
      }
    }
    //Return total number of results
    if($GLOBALS['resultCount'] < 1) {
      $GLOBALS['searchbarunder'] = "hidesearchbarunder";
      $GLOBALS['errorMsg'] = "Your search returned no results.";
      $GLOBALS['numResults'] = "";
    }
    else if($GLOBALS['resultCount'] === 1) {
      $GLOBALS['searchbarunder'] = "showsearchbarunder";
      $GLOBALS['numResults'] = $GLOBALS['resultCount'] . " result found";
    }
    else {
      $GLOBALS['searchbarunder'] = "showsearchbarunder";
      $GLOBALS['numResults'] =  $GLOBALS['resultCount'] . " results found";
    }
  }
  $conn->close();

?><!DOCTYPE html> 
<html>
  <head> 
    <title>SARA | Search Results</title> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/solid.css" integrity="sha384-QokYePQSOwpBDuhlHOsX0ymF6R/vLk/UQVz3WHa6wygxI5oGTmDTv8wahFOSspdm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/fontawesome.css" integrity="sha384-vd1e11sR28tEK9YANUtpIOdjGW14pS87bUBuOIoBILVWLFnS+MCX9T6MMf0VdPGq" crossorigin="anonymous">
    <link rel="stylesheet" href="../stylesheets/layout.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto+Condensed">
    <link rel="SHORTCUT ICON" href="../SARA-icon.png" >
  </head>
  <nav class="topnav" id="topNav">
    <a class="logo" href="../home.html"><img src="../SARA-icon.png" alt="SARA" style="width:50px; height: 50px" ></a>
    <div class="dropdown">
      <button class="dropbtn">Search
        <i class="fa fa-caret-down"></i>
      </button>
      <div class="dropdown-content">
        <a href="fixed-list.html">Fixed List</a>
        <a href="from-file.html">From File</a>
        <a href="google-api.html">Google API</a>
        <a href="our-search-engine.html">Our Search Engine</a>
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
        <a href="../admin/search-hist.php">Search History & Stats</a>
      </div>
    </div>
    <a href="javascript:void(0);" class="hamburger" onclick="navBarFunc()">
      <i class="fa fa-bars"></i>
    </a>
  </nav>
  <body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="../scripts/functions.js"></script>
    <div class="searchresultspage">
      <div class="searchbar">
        <button type="submit" id="backbtn" onclick="goBack()">
           <i class="fas fa-chevron-left"></i>
        </button>
        <form class="searchform" method="get" autocomplete="off" id="searchform">
          <input type="hidden" name="searchby" value="<?php echo $resultSearch ?>">
          <input type="search" id="q" name="q" value="<?php echo $searchQ ?>">
          <button type="submit" id="searchbtn">
            <i class="fa fa-search"></i>
          </button>
        </form>
        <?php echo $advancedOpt ?>
      </div>
      <div class="<?php echo $searchbarunder ?>">
        <label class="checkbox">
          <input type="checkbox" id="selectall" name="selectall" onclick="selectDeselectAll()" autocomplete="off">
          <span class="checkmark"></span>
        </label>

        <div class="hidedownload" id="download">
          <div id="numselected"></div>
          <div class="save">
            <label for="drp" id= drplabel>Save as file type: </label>
            <span id="drplist">
              <select id="drp">
                <option value="1">.csv</option>
                <option value="2">.json</option>
                <option value="3">.xml</option>
              </select> 
            </span>       
            <button type="submit" id="savebtn" onclick="getSelections()">
            <i class="fas fa-download"></i>
            </button>
          </div>
        </div>

      </div>
      <div class="searchresults"><?php echo $results ?>
        <p class="resultcount"><?php echo $numResults ?></p>
        <div class="resultlist"><?php echo $resultList; echo $errorMsg ?></div>

        <div class="pagecount"></div>
      </div>
    </div>
    <span id="temp"></span>
  </body> 
</html>

