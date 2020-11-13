<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
  
// include database and object files
include_once '../config/database.php';
include_once '../objects/stock.php';

// init db
$database = new Database();
$db = $database->getConnection();

$id = isset($_GET["id"]) ? $_GET["id"] : "";


if(empty($id)){
    // set response code - 404 Not found
    http_response_code(404);
    $stock_arr["status_code"] = 404;
    $stock_arr["status_message"] = "Failed";
  
    echo json_encode(
        array("message" => "Missing Location, No data found.")
    );
}else{
    // initialize object
    $stock = new Stock($db);

    //get stock data
    $currData = $stock->read($id);
    $currData = $currData->fetch(PDO::FETCH_ASSOC);

    //get log entry
    $stmt = $stock->readLog($id);
    $num = $stmt->rowCount();
    
    // check if more than 0 record found
    if($num>0){
    
        // data array
        $stock_arr=array();
        $stock_arr["logs"]=array();
        
        // retrieve data
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            
            // extract row
            // this will make $row['name'] to
            // just $name only
            extract($row);

            $logs_entry = array(
                "id" => $row["location"],
                "type" => $row["type"],
                "adjustment" => $row["adjustment"],
                "qty" => $row["qty"],
                "created_at" => $row["created_time"],
            );
    
            array_push($stock_arr["logs"], $logs_entry);
            
        }
        // set response code - 200 OK
        http_response_code(200);
        $stock_arr["status_code"] = 200;
        $stock_arr["status"] = "Success, logs found";
        $stock_arr["location_id"] = $id;
        $stock_arr["location_name"] = $currData["location"];
        $stock_arr["product"] = $currData["product"];
        $stock_arr["current_qty"] = $currData["qty"];
    
        // show data in json
        echo json_encode($stock_arr);
    }else{
    
        // set response code - 404 Not found
        http_response_code(404);
        $stock_arr["status_code"] = 404;
        $stock_arr["status_message"] = "Failed";
    
        echo json_encode(
            array("message" => "No Data Found.")
        );
    }
}
