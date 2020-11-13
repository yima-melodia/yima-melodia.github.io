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
  
// initialize object
$stock = new Stock($db);
  
// query stock
$stmt = $stock->read();
$num = $stmt->rowCount();
  
// check if more than 0 record found
if($num>0){
  
    // data array
    $stock_arr=array();
    $stock_arr["stocks"]=array();
  
    // retrieve data
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        extract($row);
  
        $stock_item = array(
            "id" => $id,
            "location" => $location,
            "qty" => $qty,
            "product" => $product,
            "last_update" => $last_update
        );
  
        array_push($stock_arr["stocks"], $stock_item);
    }
  
    // set response code - 200 OK
    http_response_code(200);
    $stock_arr["status_code"] = 200;
    $stock_arr["status_message"] = "Success";
  
    // show products data in json format
    echo json_encode($stock_arr);
}else{
  
    // set response code - 404 Not found
    http_response_code(404);
    $stock_arr["status_code"] = 404;
    $stock_arr["status_message"] = "Failed";
  
    // tell the user no products found
    echo json_encode(
        array("message" => "Something wrong.")
    );
}