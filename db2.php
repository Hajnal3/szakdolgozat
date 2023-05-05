<?php
try {
$conn = new PDO('mysql:host=localhost;dbname=szakdolgozat', "test", " yourpassword") or die;
       
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    

    $mydate=date('Y-m-d H:i:s');
    $myday=date('Y-m-d');
    $hour = date('H');
    switch( true ){
        case ( $hour >= 6 && $hour < 14 ):$shift=6; break;
        case ( $hour >=14 && $hour < 22 ):$shift=14; break;
    default: $shift=22; break;
            
}
$myday=date("2023-04-20"); //tesztelés miatta fix változó
//választott cella cellid-a    
$selectOption = $_POST['machine']; 
if (!isset($selectOption)){
    $selectOption=101;
}
    
    //GEF Leszürve costcenterre RondoHC-kolcsonadott_minutes+kolcsonvett_minutes/letszam     //Planned prod - shift, mydate 
//Get attendance
    $myquerry="SELECT COUNT(name) AS letszam FROM attendance_presents 
    WHERE station_id like '".$selectOption."'
    AND shift_id like '".$shift."'";

    $present=$conn->query($myquerry);
    $result = $present->fetchAll(PDO::FETCH_NUM);
    $letszam = $result[0][0];
    //$letszam;

    
    
$processNames=array(
    "FKT"=>array(10073,10079),
    "FLASH"=>array(10066,10180),
    "ASSEMLY"=>array(10051,10058,10199,10200,10112,10119),
    "FLASH"=>array(10066),
    "AFT"=>array(10011),
    "GSL"=>array(10078),
    "HU"=>array(10010)
);    
    
//Get AFT
    $mySql=("IF (EXTRACT(HOUR from now()) >= 6) 
    BEGIN
    SELECT X.process_id, SUM(X.passed) As passed, SUM(X.failed) as failed
    FROM (
	SELECT CONVERT(CONVERT(DATE_ADD(NOW(), INTERVAL 0 day),date),date) as ddate, MAIN.snr, MAIN.process_id, MAIN.station_id, SUM(MAIN.passed) as passed, SUM(MAIN.failed) as failed
    FROM (
	SELECT f.snr, f.process_id, f.station_id, SUM(-1*f.passed) As passed, SUM(-1*f.failed) As failed 
    FROM process AS f 
    WHERE (EXTRACT(HOUR from f.ddate) = ".$shift."-1) 
    and CONVERT(f.ddate,date) = CONVERT( CONVERT(DATE_ADD(NOW(), INTERVAL 0 day),date),date) 
    and f.snr != 0 GROUP BY f.process_id, f.snr, f.station_id    
    UNION   
    SELECT f.snr, f.process_id, f.station_id, SUM(f.passed) As passed, SUM(f.failed) As failed 
    FROM process AS f 
    WHERE (EXTRACT(HOUR from f.ddate) = EXTRACT(HOUR from NOW())) and CONVERT(f.ddate,date) = CONVERT( CONVERT(DATE_ADD(NOW(), INTERVAL 0 day),date),date) and f.snr != 0 
    GROUP BY f.process_id, f.snr, f.station_id
	) AS MAIN 
    INNER JOIN stations AS C ON C.cellid = MAIN.cellid 
    WHERE C.station_id = ".$selectOption." 
	GROUP BY MAIN.snr, MAIN.process_id, MAIN.station_id	
	) AS X
    GROUP BY X.process_id
END
ELSE
BEGIN
SELECT X.process_id, SUM(X.passed) As passed, SUM(X.failed) as failed 
    FROM (	
	SELECT CONVERT(CONVERT(DATE_ADD(NOW(), INTERVAL 1 day),date),date) as ddate, MAIN.snr, MAIN.process_id, MAIN.station_id, SUM(MAIN.passed) as passed, SUM(MAIN.failed) as failed 
    FROM (
	--ELŐZŐ NAP 21:59-ig
	SELECT f.snr, f.process_id, f.station_id, SUM(-1*f.passed) As passed, SUM(-1*f.failed) As failed 
    FROM process AS f 
    WHERE (EXTRACT(HOUR from f.ddate) = 21) and CONVERT(f.ddate,date) = CONVERT(CONVERT(DATE_ADD(NOW(), INTERVAL 1 day),date),date) and f.snr != 0 
	GROUP BY f.process_id, f.snr, f.station_id     
    UNION   
	-- ELŐZŐ NAP 23:59-ig
    SELECT f.snr, f.processid, f.station_id, SUM(f.passed) As passed, SUM(f.failed) As failed 
    FROM process AS f 
    WHERE (EXTRACT(HOUR from f.ddate) and CONVERT(f.ddate,date) = CONVERT(CONVERT(DATE_ADD(NOW(), INTERVAL 1 day),date),date) and f.snr != 0 
    GROUP BY f.process_id, f.snr, f.station_id	
	UNION
	--AKTUÁLIS NAP AKTUÁLIS ÓRÁIG
	SELECT f.snr, f.process_id, f.station_id, SUM(f.passed) As passed, SUM(f.failed) As failed 
    FROM process AS f 
    WHERE (EXTRACT(HOUR from f.ddate) = EXTRACT(HOUR from NOW())) and CONVERT(f.ddate,date) = CONVERT(CONVERT(DATE_ADD(NOW(), INTERVAL 0 day),date),date) and f.snr != 0 
    GROUP BY f.process_id, f.snr, f.station_id	
	) AS MAIN 
    INNER JOIN stations AS C ON C.cellid = MAIN.cellid 
    WHERE C.station_id = ".$selectOption." 
	GROUP BY MAIN.snr, MAIN.process_id, MAIN.station_id	
	) AS X
    GROUP BY X.process_id 
END");
    
    //[procdessid, passod, failed] => [processid =>[PASS: db, failed: db]]    
    
    $handleAft = $conn->query($mySql);
    $result = $handleAft->fetchAll(PDO::FETCH_NUM);
        
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
    

$handleProdPlan = $conn->query("SELECT goodqty, plannedqty FROM Qlik_produced_planned 
    WHERE area like 'BMW' 
    AND plant like 'Székesfehérvár Elektronika' 
    AND cshift like '".$shift."'
    AND cdate BETWEEN '".date('Y-m-d')." 00:00:00.000' AND '".date('Y-m-d')." 23:59:00.000'");
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