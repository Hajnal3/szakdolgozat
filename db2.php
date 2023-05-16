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

try {
$conn = new PDO('mysql:host=localhost;dbname=szakdolgozat', "test", " yourpassword") or die;
       
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    

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
    
//form options    
    $myformquerry="SELECT station_id, station_name FROM stations";
    $stations=$conn->query($myformquerry);
    
//létszám sql
    $attendance_q="SELECT COUNT(name) AS letszam FROM attendance_presents 
    WHERE station_id like '".$selectOption."'
    AND shift_id like '".$shift."'";
    $present=$conn->query($attendance_q);
    
//eljárás sql 
    $myproquerry="SELECT process_name, GROUP_CONCAT(process_id SEPARATOR ',') as         process_ids FROM processes GROUP BY process_name";
    $pro=$conn->query($myproquerry);
    $prod_data = $pro->fetchAll(PDO::FETCH_ASSOC);

    
//yield sql
    $mySqlDay=("SELECT X.process_id, SUM(X.passed) As passed, SUM(X.failed) as failed
                FROM (SELECT CONVERT(DATE_ADD('" . $myday . "', INTERVAL 0 day),date) as process_date, MAIN.snr, MAIN.process_id, MAIN.station_id, SUM(MAIN.passed) as passed, SUM(MAIN.failed) as failed
                    FROM (
                        SELECT f.snr, f.process_id, f.station_id, SUM(-1*f.passed) As passed, SUM(-1*f.failed) As failed 
                        FROM process AS f 
                        WHERE (EXTRACT(HOUR from f.process_date) = ".$shift."-1) 
                        and CONVERT(f.process_date,date) = CONVERT( CONVERT(DATE_ADD('" . $myday . "', INTERVAL 0 day),date),date) 
                        and f.snr != 0 GROUP BY f.process_id, f.snr, f.station_id    
                        UNION   
                        SELECT f.snr, f.process_id, f.station_id, SUM(f.passed) As passed, SUM(f.failed) As failed 
                        FROM process AS f 
                        WHERE (EXTRACT(HOUR from f.process_date) = EXTRACT(HOUR from '" . $myday . "')) and CONVERT(f.process_date,date) = CONVERT(DATE_ADD('" . $myday . "', INTERVAL 0 day),date) and f.snr != 0 
                        GROUP BY f.process_id, f.snr, f.station_id
                    ) AS MAIN 
                    INNER JOIN stations AS C ON C.station_id = MAIN.station_id 
                    WHERE C.station_id = ".$selectOption."
                    GROUP BY MAIN.snr, MAIN.process_id, MAIN.station_id    
                ) AS X
                GROUP BY X.process_id");
    
    $mySqlNight=("SELECT X.process_id, SUM(X.passed) As passed, SUM(X.failed) as failed 
				FROM (    
                SELECT CONVERT(DATE_ADD('" . $myday . "', INTERVAL 1 day),date) as process_date, MAIN.snr, MAIN.process_id, MAIN.station_id, SUM(MAIN.passed) as passed, SUM(MAIN.failed) as failed 
                FROM (
                    SELECT f.snr, f.process_id, f.station_id, SUM(-1*f.passed) As passed, SUM(-1*f.failed) As failed 
                    FROM process AS f 
                    WHERE (EXTRACT(HOUR from f.process_date) = 21) and CONVERT(f.process_date,date) = CONVERT(DATE_ADD('".$myday."', INTERVAL 1 day),date) and f.snr != 0 
                    GROUP BY f.process_id, f.snr, f.station_id 
                UNION   
                    SELECT f.snr, f.process_id, f.station_id, SUM(f.passed) As passed, SUM(f.failed) As failed 
                    FROM process AS f 
                    WHERE (EXTRACT(HOUR from f.process_date) and CONVERT(f.process_date,date) = CONVERT(DATE_ADD('".$myday."', INTERVAL 1 day),date)) and f.snr != 0 
                    GROUP BY f.process_id, f.snr, f.station_id 
                UNION
                    SELECT f.snr, f.process_id, f.station_id, SUM(f.passed) As passed, SUM(f.failed) As failed 
					FROM process AS f 
					WHERE (EXTRACT(HOUR from f.process_date) = EXTRACT(HOUR from '".$myday."')) and CONVERT(f.process_date,date) = CONVERT(DATE_ADD('".$myday."', INTERVAL 0 day),date) and f.snr != 0 
					GROUP BY f.process_id, f.snr, f.station_id
                    ) AS MAIN 
                    INNER JOIN stations AS C ON C.station_id = MAIN.station_id 
                    WHERE C.station_id = ".$selectOption."
                    GROUP BY MAIN.snr, MAIN.process_id, MAIN.station_id
					) AS X
                GROUP BY X.process_id");

        if ( date('H') > 6){
        $handleY = $conn->query($mySqlDay);
        } 
        else {
        $handleY = $conn->query($mySqlNight);
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
  

    
    
$conn = null;
}
catch(PDOException $ex){
print($ex->getMessage());
} 
    
?>