<?php

function checkTimer() {
  $startTime = time();
  $interval = 20; // 20 seconds interval
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
    
    //$mydate=date('Y-m-d H:i:s');
    $myday=date("2023-04-20");                    //$myday=date('Y-m-d');
    $hour = date('H');
    switch( true ){
        case ( $hour >= 6 && $hour < 14 ):$shift=06; break;
        case ( $hour >=14 && $hour < 22 ):$shift=14; break;
        default: $shift=22; break;        
    }
    $shift=14;
    $mydate=("2023-04-20 ".$shift.":00:00" ); //tesztelés miatta fix változó 
   
    //választott cella cellid-a 
    if(isset($_POST['machine'])){
        $selectOption = $_POST['machine'];
    }
    if (!isset($selectOption)){
        $selectOption=101;
    }
    

    
?>