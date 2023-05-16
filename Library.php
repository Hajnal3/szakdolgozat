<?php

//kiszedi a terminálokat mikor az index kéri
function getMachineList($data_dump)
{
    $result = $data_dump->fetchAll(PDO::FETCH_NUM);
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
function getAttendance($data_dump)
{
    $result = $data_dump->fetchAll(PDO::FETCH_NUM);
    $letszam = $result[0][0];
    return $letszam;
}

//kiszedi a gárási eljárásokat mikor az index kéri
function getProdData($data_dump)
{
    $processNames = array();
    foreach($data_dump as $row)
{
    $prname=$row['process_name'];
    $processids=explode(',', $row['process_ids']);
    $processids=array_map('intval', $processids);
    $processNames[$prname]=$processids;
}
    return $processNames;
}

//kiszedi a teljes yieldet adott eljárásonként mikor az index kéri
function getAllYield($processNames, $data_dump)
{
    $result = $data_dump->fetchAll(PDO::FETCH_NUM);
    
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
    return $processAll;
}






?>