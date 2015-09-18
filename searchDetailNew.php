<?php
/*
 * Match search strings passed in against inventory
 */
session_start();
include_once("config.php");

// validate our credentials
include('validateSession.php');

// Check our POST input values
$searchString = $_POST["searchString"];
$section = $_POST["section"];
$contentMatch = $_POST["useAllContent"];

error_log("Search Params: $searchString, $section, $userid,$contentMatch",0);

function pullFabricData($row) {
  $ret = array();

  $ret['type'] = 'Fabric';
  $ret['id'] = $row['id'];
  $ret['name'] = $row['type'];
  $ret['color'] = $row['color'];
  $ret['description'] = $row['description'];
  $ret['brand'] = $row['brand'];
  $ret['model'] = $row['model'];
  $ret['quantityType'] = $row['quantityType'];
  $ret['category'] = "";

  return $ret;
}
function pullNotionData($row) {
  $ret = array();

  $ret['type'] = 'Notion';
  $ret['id'] = $row['id'];
  $ret['name'] = $row['type'];
  $ret['color'] = $row['color'];
  $ret['description'] = $row['description'];
  $ret['brand'] = $row['brand'];
  $ret['model'] = $row['model'];
  $ret['quantityType'] = "";
  $ret['category'] = "";

  return $ret;
}
function pullPatternData($row) {
  $ret = array();

  $ret['type'] = 'Pattern';
  $ret['id'] = $row['id'];
  $ret['name'] = $row['project'];
  $ret['color'] = $row['style'];
  $ret['description'] = $row['description'];
  $ret['brand'] = $row['brand'];
  $ret['model'] = $row['model'];
  $ret['quantityType'] = "";
  $ret['category'] = $row['category'];

  return $ret;
}
function pullProjectData($row) {
  $ret = array();
  $ret['type'] = 'Project';
  $ret['id'] = $row['id'];
  $ret['name'] = $row['name'];
  $ret['color'] = $row['forwhome'];
  $ret['description'] = $row['description'];
  $ret['brand'] = $row['deadlineEvent'];
  $ret['model'] = $row['deadlineDate'];
  $ret['quantityType'] = "";
  $ret['category'] = $row['category'];

  return $ret;
}

function queryFabric($searchString,$con,$userid) {
  // id, table, name/type, desc, color/style, brand, model
  $sort = $_GET["jtSorting"];
  if($sort == "name ASC") {
    $sort = "type ASC";
  } else if($sort == "name DESC") {
    $sort = "type DESC";
  }
  //error_log("Sort string is " . $sort,0);

  $searchTerms = explode(' ', $searchString);
  $searchTermBitsType = array();
  $searchTermBitsColor = array();
  $searchTermBitsDesc = array();
  $searchTermBitsBrand = array();
  $searchTermBitsModel = array();
  $searchTermBitsUnit = array();
  foreach ($searchTerms as $term) {
      $term = trim($term);
      if (!empty($term)) {
          $searchTermBitsType[]  = "MATCH(type) AGAINST('%$term%')";
          $searchTermBitsColor[] = "MATCH(color) AGAINST('%$term%')";
          $searchTermBitsDesc[]  = "MATCH(description) AGAINST('%$term%')";
          $searchTermBitsBrand[] = "MATCH(brand) AGAINST('%$term%')";
          $searchTermBitsModel[] = "MATCH(model) AGAINST('%$term%')";
          $searchTermBitsUnit[]  = "MATCH(quantityType) AGAINST('%$term%')";
      }
  }

  $sql = "SELECT * FROM Fabrics WHERE ((".implode(' OR ', $searchTermBitsType);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsColor);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsDesc);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsBrand);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsModel);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsUnit);
  $sql .= ")) AND userid='$userid' ORDER BY $sort LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
  error_log("Multi word select: " . $sql,0);
  //$result = mysql_query($sql);


  //$sql = "SELECT * FROM Fabrics WHERE (type LIKE '%$searchString%' OR color LIKE '%$searchString%' OR description LIKE '%$searchString%' OR brand LIKE '%$searchString%' OR model LIKE '%$searchString%') AND userid='$userid' ORDER BY $sort LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
  if(!$result = $con->query($sql)) {
    $errMsg = "Error running query [" . $con->error. "] sql [" . $sql . "]";
    trigger_error($errMsg,E_USER_ERROR);
  }
  return $result;
}

function queryNotion($searchString,$con,$userid) {
  $sort = $_GET["jtSorting"];
  if($sort == "name ASC") {
    $sort = "type ASC";
  } else if($sort == "name DESC") {
    $sort = "type DESC";
  }
  //error_log("Sort string is " . $sort,0);

  $searchTerms = explode(' ', $searchString);
  $searchTermBitsType = array();
  $searchTermBitsColor = array();
  $searchTermBitsDesc = array();
  $searchTermBitsBrand = array();
  $searchTermBitsModel = array();
  foreach ($searchTerms as $term) {
      $term = trim($term);
      if (!empty($term)) {
          $searchTermBitsType[]  = "MATCH(type) AGAINST('%$term%')";
          $searchTermBitsColor[] = "MATCH(color) AGAINST('%$term%')";
          $searchTermBitsDesc[]  = "MATCH(description) AGAINST('%$term%')";
          $searchTermBitsBrand[] = "MATCH(brand) AGAINST('%$term%')";
          $searchTermBitsModel[] = "MATCH(model) AGAINST('%$term%')";
      }
  }

  $sql = "SELECT * FROM Notions WHERE ((".implode(' OR ', $searchTermBitsType);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsColor);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsDesc);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsBrand);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsModel);
  $sql .= ")) AND userid='$userid' ORDER BY $sort LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
  error_log("Multi word select: " . $sql,0);

  //$sql = "SELECT * FROM Notions WHERE (type LIKE '%$searchString%' OR brand LIKE '%$searchString%' OR description LIKE '%$searchString%' OR color LIKE '%$searchString%' OR model LIKE '%$searchString%') AND userid='$userid' ORDER BY $sort LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
  if(!$result = $con->query($sql)) {
    $errMsg = "Error running query [" . $con->error. "] sql [" . $sql . "]";
    trigger_error($errMsg,E_USER_ERROR);
  }

  return $result;
}

function queryPattern($searchString,$con,$userid) {
  $sort = $_GET["jtSorting"];
  if($sort == "name ASC") {
    $sort = "project ASC";
  } else if($sort == "name DESC") {
    $sort = "project DESC";
  } else if($sort == "color ASC") {
    $sort = "style ASC";
  } else if($sort == "color DESC") {
    $sort = "style DESC";
  } else if($sort == "type ASC") {
    $sort = "project ASC";
  } else if($sort == "type DESC") {
    $sort = "project DESC";
  } else if($sort == "category ASC") {
    $sort = "category ASC";
  } else if($sort == "category DESC") {
    $sort = "category DESC";
  }
  //error_log("Sort string is " . $sort,0);

  $searchTerms = explode(' ', $searchString);
  $searchTermBitsStyle = array();
  $searchTermBitsProject = array();
  $searchTermBitsDesc = array();
  $searchTermBitsBrand = array();
  $searchTermBitsModel = array();
  $searchTermBitsCategory = array();
  foreach ($searchTerms as $term) {
      $term = trim($term);
      if (!empty($term)) {
          $searchTermBitsStyle[]  = "MATCH(style) AGAINST('%$term%')";
          $searchTermBitsDesc[]  = "MATCH(description) AGAINST('%$term%')";
          $searchTermBitsBrand[] = "MATCH(brand) AGAINST('%$term%')";
          $searchTermBitsModel[] = "MATCH(model) AGAINST('%$term%')";
          $searchTermBitsCategory[] = "MATCH(category) AGAINST('%$term%')";
          $searchTermBitsProject[] = "MATCH(project) AGAINST('%$term%')";
      }
  }

  $sql = "SELECT * FROM Patterns WHERE ((".implode(' OR ', $searchTermBitsStyle);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsProject);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsDesc);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsBrand);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsModel);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsCategory);
  $sql .= ")) AND userid='$userid' ORDER BY $sort LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
  error_log("Multi word select: " . $sql,0);


  //$sql = "SELECT * FROM Patterns WHERE (project LIKE '%$searchString%' OR brand LIKE '%$searchString%' OR description LIKE '%$searchString%' OR style LIKE '%$searchString%' OR model LIKE '%$searchString%' OR category LIKE '%$searchString%') AND userid='$userid' ORDER BY $sort LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
  if(!$result = $con->query($sql)) {
    $errMsg = "Error running query [" . $con->error. "] sql [" . $sql . "]";
    trigger_error($errMsg,E_USER_ERROR);
  }

  return $result;
}

function queryProject($searchString,$con,$userid) {
  $sort = $_GET["jtSorting"];
  if($sort == "color ASC") {
    $sort = "forwhome ASC";
  } else if($sort == "color DESC") {
    $sort = "forwhome DESC";
  } else if($sort == "brand ASC") {
    $sort = "sizes ASC";
  } else if($sort == "brand DESC") {
    $sort = "sizes DESC";
  } else if($sort == "model ASC") {
    $sort = "deadlineEvent ASC";
  } else if($sort == "model DESC") {
    $sort = "deadlineEvent DESC";
  } else if($sort == "type ASC") {
    $sort = "deadlineEvent ASC";
  } else if($sort == "type DESC") {
    $sort = "deadlineEvent DESC";
  } else if($sort == "category ASC") {
    $sort = "category ASC";
  } else if($sort == "category DESC") {
    $sort = "category DESC";
  }
  //error_log("Sort string is " . $sort,0);

  $searchTerms = explode(' ', $searchString);
  $searchTermBitsCategory = array();
  $searchTermBitsName = array();
  $searchTermBitsDesc = array();
  $searchTermBitsFor = array();
  $searchTermBitsDeadline = array();
  foreach ($searchTerms as $term) {
      $term = trim($term);
      if (!empty($term)) {
          $searchTermBitsCategory[]  = "MATCH(category) AGAINST('%$term%')";
          $searchTermBitsName[] = "MATCH(name) AGAINST('%$term%')";
          $searchTermBitsDesc[]  = "MATCH(description) AGAINST('%$term%')";
          $searchTermBitsFor[] = "MATCH(forwhome) AGAINST('%$term%')";
          $searchTermBitsDeadline[] = "MATCH(deadlineEvent) AGAINST('%$term%')";
      }
  }

  $sql = "SELECT * FROM Projects WHERE ((".implode(' OR ', $searchTermBitsCategory);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsName);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsDesc);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsFor);
  $sql .= ") OR (".implode( ' OR ', $searchTermBitsDeadline);
  $sql .= ")) AND userid='$userid' ORDER BY $sort LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
  error_log("Multi word select: " . $sql,0);

  //$sql = "SELECT * FROM Projects WHERE (name LIKE '%$searchString%' OR forwhome LIKE '%$searchString%' OR description LIKE '%$searchString%' OR deadlineEvent LIKE '%$searchString%' OR category LIKE '%$searchString%') AND userid='$userid' ORDER BY $sort LIMIT " . $_GET["jtStartIndex"] . "," . $_GET["jtPageSize"];
  if(!$result = $con->query($sql)) {
    $errMsg = "Error running query [" . $con->error. "] sql [" . $sql . "]";
    trigger_error($errMsg,E_USER_ERROR);
  }

  return $result;
}

//-------------- Entry Point ---------------------
// Query our database based on the user selection(s)

try
{
  //Open database connection
  global $_glDatabaseServer;
  global $_glDBUser;
  global $_glDBPwd;
  global $_glDBDatabase;

  $con = new mysqli($_glDatabaseServer, $_glDBUser, $_glDBPwd, $_glDBDatabase);
  if($con->connect_errno > 0){
      $errMsg = "Unable to connect to database [" . $con->connect_error . "]";
      var_dump($errMsg);
      trigger_error($errMsg,E_USER_ERROR);
      die();
  }

  //error_log("pulling content sorted by " . $_GET['jtSorting'] . " and section " . $section,0);
  $sql = "";
  switch($section) {
    case 0: // all
      $result = queryFabric($searchString,$con,$userid);
      $rows = array();
      while($row = $result->fetch_assoc()) {
          $rows[] = pullFabricData($row);
      }

      $result = queryProject($searchString,$con,$userid);
      while($row = $result->fetch_assoc()) {
          $rows[] = pullProjectData($row);
      }

      $result = queryPattern($searchString,$con,$userid);
      while($row = $result->fetch_assoc()) {
          $rows[] = pullPatternData($row);
      }

      $result = queryNotion($searchString,$con,$userid);
      while($row = $result->fetch_assoc()) {
          $rows[] = pullNotionData($row);
      }

      //Return result to jTable
      $jTableResult = array();
      $jTableResult['Result'] = "OK";
      $jTableResult['TotalRecordCount'] = count($rows);
      $jTableResult['Records'] = $rows;
      print json_encode($jTableResult);
      break;
    case 1: // fabrics
      $result = queryFabric($searchString,$con,$userid);
      $rows = array();
      while($row = $result->fetch_assoc()) {
          $rows[] = pullFabricData($row);
      }

      //Return result to jTable
      $jTableResult = array();
      $jTableResult['Result'] = "OK";
      $jTableResult['TotalRecordCount'] = count($rows);
      $jTableResult['Records'] = $rows;
      print json_encode($jTableResult);
      break;
    case 2: // projects
      $result = queryProject($searchString,$con,$userid);
      $rows = array();
      while($row = $result->fetch_assoc()) {
          $rows[] = pullProjectData($row);
      }

      //Return result to jTable
      $jTableResult = array();
      $jTableResult['Result'] = "OK";
      $jTableResult['TotalRecordCount'] = count($rows);
      $jTableResult['Records'] = $rows;
      print json_encode($jTableResult);
      break;
    case 3: // patterns
      $result = queryPattern($searchString,$con,$userid);
      $rows = array();
      while($row = $result->fetch_assoc()) {
          $rows[] = pullPatternData($row);
      }

      //Return result to jTable
      $jTableResult = array();
      $jTableResult['Result'] = "OK";
      $jTableResult['TotalRecordCount'] = count($rows);
      $jTableResult['Records'] = $rows;
      print json_encode($jTableResult);
      break;
    case 4: // notions
      $result = queryNotion($searchString,$con,$userid);
      $rows = array();
      while($row = $result->fetch_assoc()) {
          $rows[] = pullNotionData($row);
      }

      //Return result to jTable
      $jTableResult = array();
      $jTableResult['Result'] = "OK";
      $jTableResult['TotalRecordCount'] = count($rows);
      $jTableResult['Records'] = $rows;
      print json_encode($jTableResult);
      break;
    default:
      break;
  }
}
catch(Exception $ex)
{
    //Return error message
    $jTableResult = array();
    $jTableResult['Result'] = "ERROR";
    $jTableResult['Message'] = $ex->getMessage();
    print json_encode($jTableResult);
}

//Close database connection
$con->close();
?>
