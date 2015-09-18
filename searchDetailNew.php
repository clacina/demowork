<?php
/*
 * Match search strings passed in against inventory
 */
session_start();
include_once("config.php");

// validate our credentials
include('validateSession.php');

// Define base class for query and sort operations
class baseDataRecord {
  var $sort;  // our sort string from the UI
  
  function __construct($sortString) {
    $this->sort = $sortString;
  }
  
  // override the default sort string based on the table type
  function getSortString() {
    return $this->sort;
  }
  
  // Build our specific query string based on the parameters passed in
  function getSQLQuery($searchString,$userid,$startIndex, $pageSize) {
    return "";
  }
  
  // pull the data from the returned SQL Query as an array for use
  //  in a JavaScript JTable object
  function pullData($row) {
    $ret = array();
    
    return $ret;
  }
}

// Specific class for Fabric Data
class fabricRecord extends baseDataRecord {
  function __construct($sortString) {
    parent::__construct($sortString);
  }
  
  function getSortString() {
    // id, table, name/type, desc, color/style, brand, model
    if($this->sort == "name ASC") {
      return "type ASC";
    } else if($this->sort == "name DESC") {
      return "type DESC";
    }
    return parent::getSortString();
  }
  
  function getSQLQuery($searchString,$userid,$startIndex, $pageSize) {
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

    // get the sort string for this query
    $sortString = $this->getSortString();
    
    $sql = "SELECT * FROM Fabrics WHERE ((".implode(' OR ', $searchTermBitsType);
    $sql .= ") OR (".implode( ' OR ', $searchTermBitsColor);
    $sql .= ") OR (".implode( ' OR ', $searchTermBitsDesc);
    $sql .= ") OR (".implode( ' OR ', $searchTermBitsBrand);
    $sql .= ") OR (".implode( ' OR ', $searchTermBitsModel);
    $sql .= ") OR (".implode( ' OR ', $searchTermBitsUnit);
    $sql .= ")) AND userid='$userid' ORDER BY $sortString LIMIT $startIndex , $pageSize";
    error_log("Multi word select: " . $sql,0);
    return $sql;
  }  
  
  function pullData($row) {
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
}

// Specific class for Pattern Data
class patternRecord extends baseDataRecord {
  function __construct($sortString) {
    parent::__construct($sortString);
  }
  
  function getSortString() {
    // id, table, name/type, desc, color/style, brand, model
    if($this->sort == "name ASC") {
      return "project ASC";
    } else if($this->sort == "name DESC") {
      return "project DESC";
    } else if($this->sort == "color ASC") {
      return "style ASC";
    } else if($this->sort == "color DESC") {
      return "style DESC";
    } else if($this->sort == "type ASC") {
      return "project ASC";
    } else if($this->sort == "type DESC") {
      return "project DESC";
    }
    return parent::getSortString();
  }
  
  function getSQLQuery($searchString,$userid,$startIndex, $pageSize) {
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

    // get the sort string for this query
    $sortString = $this->getSortString();

    $sql = "SELECT * FROM Patterns WHERE ((".implode(' OR ', $searchTermBitsStyle);
    $sql .= ") OR (".implode( ' OR ', $searchTermBitsProject);
    $sql .= ") OR (".implode( ' OR ', $searchTermBitsDesc);
    $sql .= ") OR (".implode( ' OR ', $searchTermBitsBrand);
    $sql .= ") OR (".implode( ' OR ', $searchTermBitsModel);
    $sql .= ") OR (".implode( ' OR ', $searchTermBitsCategory);
    $sql .= ")) AND userid='$userid' ORDER BY $sortString LIMIT $startIndex , $pageSize";
    error_log("Multi word select: " . $sql,0);
    return $sql;
  }  
  
  function pullData($row) {
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
}

// Specific class for Notion Data
class notionRecord extends baseDataRecord {
  function __construct($sortString) {
    parent::__construct($sortString);
  }
  
  function getSortString() {
    // id, table, name/type, desc, color/style, brand, model
    if($this->sort == "name ASC") {
      return "type ASC";
    } else if($this->sort == "name DESC") {
      return "type DESC";
    }
    return parent::getSortString();
  }
  
  function getSQLQuery($searchString,$userid,$startIndex, $pageSize) {
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

        // get the sort string for this query
    $sortString = $this->getSortString();

    $sql = "SELECT * FROM Notions WHERE ((".implode(' OR ', $searchTermBitsType);
    $sql .= ") OR (".implode( ' OR ', $searchTermBitsColor);
    $sql .= ") OR (".implode( ' OR ', $searchTermBitsDesc);
    $sql .= ") OR (".implode( ' OR ', $searchTermBitsBrand);
    $sql .= ") OR (".implode( ' OR ', $searchTermBitsModel);
    $sql .= ")) AND userid='$userid' ORDER BY $sortString LIMIT $startIndex , $pageSize";
    error_log("Multi word select: " . $sql,0);
    return $sql;
  }  
  
  function pullData($row) {
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
}


//-------------- Entry Point ---------------------
// Query our database based on the user selection(s)
// Check our POST input values
$searchString = $_POST["searchString"];
$section = $_POST["section"];

// Get our table display parameters
$startIndex = $_POST["jtStartIndex"];
$pageSize = $_POST["jtPageSize"];
$sortParam = $_POST["jtSorting"];

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

  // Build our list of queries to execute
  $sqlQueries = array();
  switch($section) {
    case 0: // all
      $sqlQueries[] = new fabricRecord($sortParam);
      $sqlQueries[] = new patternRecord($sortParam);
      $sqlQueries[] = new notionRecord($sortParam);
      break;
    case 1: // fabrics
      $sqlQueries[] = new fabricRecord($sortParam);
      break;
    case 2: // patterns
      $sqlQueries[] = new patternRecord($sortParam);
      break;
    case 3: // notions
      $sqlQueries[] = new notionRecord($sortParam);
      break;
    default:
      break;
  }
  
  // define our output array
  $rowsOut = array();
  
  // loop through our queries
  foreach($sqlQueries as $q) {
    // Format our SQL
    $sql = $q->getSQLQuery($searchString, $startIndex, $pageSize);
    
    // Query the database
    if(!$result = $con->query($sql)) {
      $errMsg = "Error running query [" . $con->error. "] sql [" . $sql . "]";
      trigger_error($errMsg,E_USER_ERROR);
    }

    // Append our results to our output array
    while($row = $result->fetch_assoc()) {
        $rowsOut[] = $q->pullData($row);
    }
  }  
} catch(Exception $ex) {
    //Return error message
    $jTableResult = array();
    $jTableResult['Result'] = "ERROR";
    $jTableResult['Message'] = $ex->getMessage();
    print json_encode($jTableResult);
} finally {
  //Return result to jTable
  $jTableResult = array();
  $jTableResult['Result'] = "OK";
  $jTableResult['TotalRecordCount'] = count($rowsOut);
  $jTableResult['Records'] = $rowsOut;
  print json_encode($jTableResult);
}

//Close database connection
$con->close();
?>
