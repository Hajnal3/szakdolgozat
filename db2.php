<?php
function checkTimer() {
  $startTime = time();
  $interval = 200000; // 20 seconds interval
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


try {
$conn = new PDO('mysql:host=localhost;dbname=szakdolgozat', "test", " yourpassword") or die;
       
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    

    $mydate=date('Y-m-d H:i:s');
    //$myday=date('Y-m-d');
    $hour = date('H');
    switch( true ){
        case ( $hour >= 6 && $hour < 14 ):$shift=6; break;
        case ( $hour >=14 && $hour < 22 ):$shift=14; break;
    default: $shift=22; break;
            
}
$myday=date("2023-04-20"); //tesztelés miatta fix változó

    
    //választott cella cellid-a 
if(isset($_POST['machine'])){
        $selectOption = $_POST['machine'];
    }
if (!isset($selectOption)){
    $selectOption=101;
}
    
//form options    
 $myformquerry="SELECT station_id, station_name FROM stations";
    $stations=$conn->query($myformquerry);
    $result = $stations->fetchAll(PDO::FETCH_NUM);
    $stationData = array();
    foreach($result as $row)
    {
        $stationid=$row[0];
        $stname=$row[1];
        $stationData[]=array("value" => $stationid,"name" => $stname);
    }
    
    
//$letszam
$myquerry="SELECT COUNT(name) AS letszam FROM attendance_presents 
    WHERE station_id like '".$selectOption."'
    AND shift_id like '".$shift."'";

    $present=$conn->query($myquerry);
    $result = $present->fetchAll(PDO::FETCH_NUM);
    $letszam = $result[0][0];
    
//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
/*$myproquerry="SELECT process_id, process_name FROM processes GROUP BY process_name";
    $pro=$conn->query($myproquerry);
    $result = $pro->fetchAll(PDO::FETCH_NUM);
    $processNames = array();
    foreach($result as $row)
    {
        $processid=$row[0];
        $prname=$row[1];
        $processNames[]=array("name" => $stname, "value" => $processid);
    }
    */
    
    $myproquerry="SELECT process_name, GROUP_CONCAT(process_id SEPARATOR ',') as process_ids FROM processes GROUP BY process_name";
$pro=$conn->query($myproquerry);
$result = $pro->fetchAll(PDO::FETCH_ASSOC);
$processNames = array();
foreach($result as $row)
{
    $prname=$row['process_name'];
    $processids=explode(',', $row['process_ids']);
    $processids=array_map('intval', $processids);
    $processNames[$prname]=$processids;
}
$processNamessssssssss=array(
    "FKT"=>array(10073,10079),
    "FLASH"=>array(10066,10180),
    "ASSEMLY"=>array(10051,10058,10199,10200,10112,10119),
    "FLASH"=>array(10066),
    "AFT"=>array(10011),
    "GSL"=>array(10078),
    "HU"=>array(10010)
);    
    
//Get yield
    $mySqlDay=("SELECT X.process_id, SUM(X.passed) As passed, SUM(X.failed) as failed
                FROM (SELECT CONVERT(DATE_ADD(NOW(), INTERVAL 0 day),date) as process_date, MAIN.snr, MAIN.process_id, MAIN.station_id, SUM(MAIN.passed) as passed, SUM(MAIN.failed) as failed
                    FROM (
                        SELECT f.snr, f.process_id, f.station_id, SUM(-1*f.passed) As passed, SUM(-1*f.failed) As failed 
                        FROM process AS f 
                        WHERE (EXTRACT(HOUR from f.process_date) = ".$shift."-1) 
                        and CONVERT(f.process_date,date) = CONVERT( CONVERT(DATE_ADD(NOW(), INTERVAL 0 day),date),date) 
                        and f.snr != 0 GROUP BY f.process_id, f.snr, f.station_id    
                        UNION   
                        SELECT f.snr, f.process_id, f.station_id, SUM(f.passed) As passed, SUM(f.failed) As failed 
                        FROM process AS f 
                        WHERE (EXTRACT(HOUR from f.process_date) = EXTRACT(HOUR from NOW())) and CONVERT(f.process_date,date) = CONVERT(DATE_ADD(NOW(), INTERVAL 0 day),date) and f.snr != 0 
                        GROUP BY f.process_id, f.snr, f.station_id
                    ) AS MAIN 
                    INNER JOIN stations AS C ON C.station_id = MAIN.station_id 
                    WHERE C.station_id = ".$selectOption."
                    GROUP BY MAIN.snr, MAIN.process_id, MAIN.station_id    
                ) AS X
                GROUP BY X.process_id");
    
    $mySqlNight=("SELECT X.process_id, SUM(X.passed) As passed, SUM(X.failed) as failed 
				FROM (    
                SELECT CONVERT(DATE_ADD(NOW(), INTERVAL 1 day),date) as process_date, MAIN.snr, MAIN.process_id, MAIN.station_id, SUM(MAIN.passed) as passed, SUM(MAIN.failed) as failed 
                FROM (
                    SELECT f.snr, f.process_id, f.station_id, SUM(-1*f.passed) As passed, SUM(-1*f.failed) As failed 
                    FROM process AS f 
                    WHERE (EXTRACT(HOUR from f.process_date) = 21) and CONVERT(f.process_date,date) = CONVERT(DATE_ADD(NOW(), INTERVAL 1 day),date) and f.snr != 0 
                    GROUP BY f.process_id, f.snr, f.station_id 
                UNION   
                    SELECT f.snr, f.process_id, f.station_id, SUM(f.passed) As passed, SUM(f.failed) As failed 
                    FROM process AS f 
                    WHERE (EXTRACT(HOUR from f.process_date) and CONVERT(f.process_date,date) = CONVERT(DATE_ADD(NOW(), INTERVAL 1 day),date)) and f.snr != 0 
                    GROUP BY f.process_id, f.snr, f.station_id 
                UNION
                    SELECT f.snr, f.process_id, f.station_id, SUM(f.passed) As passed, SUM(f.failed) As failed 
					FROM process AS f 
					WHERE (EXTRACT(HOUR from f.process_date) = EXTRACT(HOUR from NOW())) and CONVERT(f.process_date,date) = CONVERT(DATE_ADD(NOW(), INTERVAL 0 day),date) and f.snr != 0 
					GROUP BY f.process_id, f.snr, f.station_id
                    ) AS MAIN 
                    INNER JOIN stations AS C ON C.station_id = MAIN.station_id 
                    WHERE C.station_id = ".$selectOption."
                    GROUP BY MAIN.snr, MAIN.process_id, MAIN.station_id
					) AS X
                GROUP BY X.process_id");
    
    //[procdessid, passed, failed] => [processid =>[PASS: db, failed: db]]    
    
    /*if ( date('H') > 6){
        $handleY = $conn->query($mySqlDay);
        $result = $handleY->fetchAll(PDO::FETCH_NUM);
    } 
    else {
        $handleY = $conn->query($mySqlNight);
        $result = $handleY->fetchAll(PDO::FETCH_NUM);
    }*/
    
    $handleY = $conn->query($mySqlNight);
    $result = $handleY->fetchAll(PDO::FETCH_NUM);
    
    $processDb=array();
    foreach($result as $row)
    {
        $processid=$row[0];
        $pass=$row[1];
        $fail=$row[2];
        
        $processDb[$processid]=array("pass" => $pass,"fail" => $fail);
    }
    
    $processAll=array();
    foreach($processNames as $name => $processIds) {
        $sumpass=0;
        $sumfail=0;
        foreach($processIds as $processid){
            if(array_key_exists($processid, $processDb)) {
                $sumpass=$processDb[$processid]["pass"]+$sumpass;
                $sumfail=$processDb[$processid]["fail"]+$sumfail;
            }
        }
        $processAll[$name] = ["sumpass" => $sumpass, "sumfail" => $sumfail];
    }
   
     //die();
    
    //$pricessDbf[123]["pass"] => 1234db;
    //$pricessDbf[123][0] => 1234db;
    

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
  

    
    
$conn = null;
}
catch(PDOException $ex){
print($ex->getMessage());
} 
    
?>