<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
  
// include database and object files
include_once '../config/database.php';
include_once '../objects/stock.php';

//counter
$request = 0;
$adjusted = 0;
$finalResult = [];

//processing request
$data = json_decode(file_get_contents("php://input"), true);
if($data) {
    foreach($data as $d){
        $request += 1;
        $resultUpdate = handleUpdate($d);        
        $adjusted += $resultUpdate["count"];
        array_push($finalResult, $resultUpdate["result"]);
    }
}

// set response code - 200 OK
http_response_code(200);
$stock_arr["status_code"] = 200;
$stock_arr["requests"] = $request;
$stock_arr["adjusted"] = $adjusted;
$stock_arr["results"] = $finalResult;

// show products data in json format
echo json_encode($stock_arr);

function handleUpdate($data){
    // get database connection
    $database = new Database();
    $db = $database->getConnection();

    $stock = new Stock($db);
    
    $currData = $stock->read($data["location_id"]);
    $currData = $currData->fetch(PDO::FETCH_ASSOC);

    //set value
    $stock->id = $data["location_id"];
    $stock->product = $data["product"];
    $stock->qtyadj = $data["adjustment"];
    $stock->qty = $currData["qty"];
    $stock->logtype = $data["adjustment"] < 0 ? "Outbound" : "Inbound";
    
    //cek apakah nama product sama
    if($currData["product"] == $data["product"]){
        
        if($stock->update()){
            $res = array(
                "status" => "Success",
                "updated_at" => $currData["last_update"],
                "location_id" => $currData["id"],
                "location_name" => $currData["location"],
                "product" => $currData["product"],
                "adjustment" => $data["adjustment"],
                "stock_qty" => $currData["qty"],
            );
            
            //log
            $createlog = $stock->createLog();

            $val["result"] = $res;
            $val["count"] = 1;
        
            return $val;
        }
    }else{
        $res = array(
            "status" => "Failed",
            "error_message" => "Invalid Product",
            "location_id" => $data["location_id"]
        );

        $val["result"] = $res;
        $val["count"] = 0;

        return $val;
    }

}
?>
