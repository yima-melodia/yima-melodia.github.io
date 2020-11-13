<?php
class Stock{
  
    // database connection and table name
    private $conn;
    private $table_name = "ms_stocks";
    private $table_log = "logs";
  
    // object properties
    public $id;
    public $location;
    public $qty;
    public $qtyadj;
    public $product;
    public $logtype;
    
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    // read stock
    function read($id = null){
        if($id){
            $this->id=htmlspecialchars(strip_tags($id));
            $where_statement = "WHERE id = :id";
            
        }else{
            $where_statement = "";
        }

        // select all query
        $query = "SELECT
                    id, `location`, qty, product, last_update
                FROM
                    " . $this->table_name . "
                ".$where_statement."
                ORDER BY
                    id asc";
    
        // prepare query statement
        $stmt = $this->conn->prepare($query);

        if($id){
            $stmt->bindParam(':id', $id);
        }
    
        // execute query
        $stmt->execute();
    
        return $stmt;
    }

    // update the product
    function update(){
        // sanitize
       $this->qtyadj=htmlspecialchars(strip_tags($this->qtyadj));
       $this->id=htmlspecialchars(strip_tags($this->id));
       $this->product=htmlspecialchars(strip_tags($this->product));
        
        // update query
        $query = "UPDATE " . $this->table_name . "
                SET

                    qty = qty + :qtyadj,
                    last_update = :last_update
                WHERE
                    id = :id
                    AND product = :product
                    ";
                    
        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // bind new values
        $currDate = date("Y-m-d H:i:s");
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':qtyadj', $this->qtyadj);
        $stmt->bindParam(':product', $this->product);
        $stmt->bindParam(':last_update', $currDate);
        
        // execute the query
        
        if($stmt->execute()){
            return true;
        }
    
        return false;
    }

    // create
    function createLog(){
        // sanitize
        $currDate = date("Y-m-d H:i:s");
        $this->logtype=htmlspecialchars(strip_tags($this->logtype));
        $this->qtyadj=htmlspecialchars(strip_tags($this->qtyadj));
        $this->qty=htmlspecialchars(strip_tags($this->qty));
        $currDate=htmlspecialchars(strip_tags($currDate));
        $this->id=htmlspecialchars(strip_tags($this->id));

        // query to insert
        $query = "INSERT INTO
                    " . $this->table_log . "
                
                SET
                    location=:id, type=:logtype, created_time=:created_time, adjustment=:qtyadj, qty=:qty";
    
        // prepare query
        $stmt = $this->conn->prepare($query);        
    
        // bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":logtype", $this->logtype);
        $stmt->bindParam(":qtyadj", $this->qtyadj);
        $stmt->bindParam(":qty", $this->qty);
        $stmt->bindParam(":created_time", $currDate);
    
        // execute query
        if($stmt->execute()){
            return true;
        }
    
        return false;
        
    }

    // read adjustment logs
    function readLog($id = null){
        if($id){
            $this->id=htmlspecialchars(strip_tags($id));
            $where_statement = "WHERE location = :id";
            
        }else{
            $where_statement = "";
        }

        // select all query
        $query = "SELECT
                    `location`, `type`, created_time, adjustment, qty
                FROM
                    " . $this->table_log . "
                ".$where_statement."
                ORDER BY
                    logs_id asc";
    
        // prepare query statement
        $stmt = $this->conn->prepare($query);

        if($id){
            $stmt->bindParam(':id', $id);
        }
    
        // execute query
        $stmt->execute();
    
        return $stmt;
    }
}
?>