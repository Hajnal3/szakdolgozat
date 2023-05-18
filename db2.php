<?php


function checkTimer() {
  $startTime = time();
  $interval = 200; // 20 seconds interval
  $currentTime = time();
  $diff = $currentTime - $startTime;
  if ($diff >= $interval) {
    // Reset the timer
    $startTime = time();
    return true;
  } else {
    return false;
  }
}

try 
{
$conn = new PDO('mysql:host=localhost;dbname=szakdolgozat', "test", " yourpassword");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 
catch(PDOException $ex){
print($ex->getMessage());
} 
//print_r($conn);
       
    
// Function to execute an SQL query and return the result
/*function executeQuery($query) {
  global $conn;
  $stmt = $conn->prepare($query);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
*/
    

    $mydate=date('Y-m-d H:i:s');
    $myday=date("2023-04-20"); //tesztelés miatta fix változó                     //$myday=date('Y-m-d');
    $hour = date('H');
    switch( true ){
        case ( $hour >= 6 && $hour < 14 ):$shift=6; break;
        case ( $hour >=14 && $hour < 22 ):$shift=14; break;
        default: $shift=22; break;        
    }

    //választott cella cellid-a 
    if(isset($_POST['machine'])){
        $selectOption = $_POST['machine'];
    }
    if (!isset($selectOption)){
        $selectOption=101;
    }
    
  
    

    $handleProdPlan = $conn->query("SELECT quantity FROM prodplan 
    WHERE station_id = ".$selectOption."  
    AND shift_id = ".$shift."
    AND deadline = ".$myday."");
    $result = $handleProdPlan->fetchAll(PDO::FETCH_NUM);

        $prod_sum=0;
        $plan_sum=0;
        foreach($result as $row)
        {
            $prod_sum=$row[0]+$prod_sum;
            $plan_sum=$row[1]+$plan_sum;
        }
        $ProdPlan=array();
        array_push($ProdPlan, $prod_sum,$plan_sum);   

    //$data=array_merge($dataFkt,$dataRun,$dataFla,$dataAss);
  

    
    

    
?>