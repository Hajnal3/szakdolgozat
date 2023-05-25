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
function getAllYield($conn,$mydate,$selectOption,$shift)
{
    //$mydate=date("2023-04-20 14:00:00");
      $mySqlY="select process_id, sum(passed), sum(failed) 
      from process 
      where process_date BETWEEN '".$mydate."' and  DATE_add('".$mydate."', INTERVAL 8 HOUR)
      and station_id=".$selectOption."
      group by process_id";
    $yield=$conn->query($mySqlY);
    //var_dump($mySqlY);
    $result = $yield->fetchAll(PDO::FETCH_NUM);
    //print_r($result);
    
    $processDb=array();
    foreach($result as $row)
    {
        $processid=$row[0];
        $pass=$row[1];
        $fail=$row[2];
        $processDb[$processid]=array("pass" => $pass,"fail" => $fail);
    }
    
    $processNames=getProdData($conn,$selectOption,$shift);
    
    foreach ($processNames as $name => $processIds) {
    $sumpass = 0;
    $sumfail = 0;
    $processAll = array();

    foreach ($processIds as $processid) {
        if (array_key_exists($processid, $processDb)) {
            $sumpass += $processDb[$processid]["pass"];
            $sumfail += $processDb[$processid]["fail"];
        }
    }
    $processAll = array("sumpass" => $sumpass, "sumfail" => $sumfail);
    $processedYield[$name] = $processAll;
         
}
return $processedYield; 
    
}





?>