<?php


//kiszedi a terminálokat a formhoz
function getMachineList($conn)
{
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
    return $stationData;
}

//kiszedi a létszámot mikor az index kéri
function getAttendance($conn,$selectOption,$shift)
{
    $attendance_q="SELECT COUNT(name) AS letszam FROM attendance_presents 
    WHERE station_id like '".$selectOption."'
    AND shift_id like '".$shift."'";
    $present=$conn->query($attendance_q);

    $result = $present->fetchAll(PDO::FETCH_NUM);
    $letszam = $result[0][0];
    
    return $letszam;
}

//kiszedi a gárási eljárásokat mikor az index kéri
function getProdData($conn,$selectOption,$shift)
{
    $myproquerry="SELECT process_name, GROUP_CONCAT(process_id SEPARATOR ',') as         
    process_ids FROM processes GROUP BY process_name";
    $pro=$conn->query($myproquerry);
    $prod_data = $pro->fetchAll(PDO::FETCH_ASSOC);

    $processNames = array();
    foreach($prod_data as $row)
{
    $prname=$row['process_name'];
    $processids=explode(',', $row['process_ids']);
    $processids=array_map('intval', $processids);
    $processNames[$prname]=$processids;
}
    return $processNames;
}

//kiszedi a teljes yieldet adott eljárásonként mikor az index kéri
function getAllYield($conn, $myday,$selectOption,$shift)
{
      $mySqlDay=("SELECT X.process_id, SUM(X.passed) As passed, SUM(X.failed) as failed
                FROM (SELECT CONVERT(DATE_ADD('" . $myday . "', INTERVAL 0 day),date) as process_date, MAIN.snr, MAIN.process_id, MAIN.station_id, SUM(MAIN.passed) as passed, SUM(MAIN.failed) as failed
                    FROM (
                        SELECT f.snr, f.process_id, f.station_id, SUM(-1*f.passed) As passed, SUM(-1*f.failed) As failed 
                        FROM process AS f 
                        WHERE (EXTRACT(HOUR from f.process_date) = ".$shift."-1) 
                        and CONVERT(f.process_date,date) = CONVERT(DATE_ADD('" . $myday . "', INTERVAL 0 day),date)
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
    
    $result = $handleY->fetchAll(PDO::FETCH_NUM);
    print_r($result);
    var_dump($handleY);
    $processDb=array();
    foreach($result as $row)
    {
        $processid=$row[0];
        $pass=$row[1];
        $fail=$row[2];
        $processDb[$processid]=array("pass" => $pass,"fail" => $fail);
    }
    
    
    $processNames=getProdData($conn,$selectOption,$shift);
        foreach($processNames as $name => $processIds) {
        $sumpass=0;
        $sumfail=0;
        foreach($processIds as $processid){
            if(array_key_exists($processid, $processDb)) {
                $sumpass=$processDb[$processid]["pass"]+$sumpass;
                $sumfail=$processDb[$processid]["fail"]+$sumfail;
            }
        }
        $processAll=array();
        $processAll[$name] = ["sumpass" => $sumpass, "sumfail" => $sumfail];
    }
    return $processAll;
    
}






?>