<?php
  //Connect to QC Server
  $serverName = "149.4.211.180";
  $user = "zhsh6528";
  $psw = "14226528";
  $conn = new mysqli($serverName, $user, $psw);
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  $selectionsAry = $_REQUEST["selectionsAry"]; 
  $resultsAry = array();
  $j = 0;
  while ($j < count($selectionsAry)) {
  	$resultID = $selectionsAry[$j];
  	$sql = "SELECT Title as title, URL as url, Description as description FROM $user.ResultsTable WHERE ResultID = '$resultID'";
  	$result = $conn->query($sql);
  	while($row = $result->fetch_assoc()){
	  	$resultsAry[] = $row;
	  }
  	$j++;
  }

  $conn->close();
  echo json_encode($resultsAry);
?>