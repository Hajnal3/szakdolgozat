<?php
try {
$conn = new PDO("sqlsrv:Database=Applications;server=HISZWSDB08", "MD_USER", "paassss") or die;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $mydate=date('Y-m-d H:i:s');
    $myday=date('Y-m-d');
    $hour = date('H');
    switch( true ){
        case ( $hour >= 6 && $hour < 14 ):$shift=6; break;
        case ( $hour >=14 && $hour < 22 ):$shift=14; break;
    default: $shift=22; break;
}

//választott cella cellid-a    
$selectOption = $_POST['cell']; 
if (!isset($selectOption)){
    $selectOption=17;
}
    
    //GEF Leszürve costcenterre RondoHC-kolcsonadott_minutes+kolcsonvett_minutes/letszam     //Planned prod - shift, mydate 
//Get attendance
    $myquerry="SELECT COUNT(name) AS letszam FROM attendance_presents 
    WHERE area like 'BMW'
    AND plant like  'Székesfehérvár Elektronika'
    AND cshift like '".$shift."' 
    AND '".$mydate."' BETWEEN startdate AND enddate" ;
    //echo $myquerry;
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
    $mySql=("IF (DATEPART(HOUR,GETDATE()) >= 6)
BEGIN
SELECT X.processid, SUM(X.passed) As passed, SUM(X.failed) as failed 
    FROM (	
	SELECT CONVERT(date,CONVERT(date,(DATEADD(DAY,0,GETDATE())))) as ddate, MAIN.snr, MAIN.processid, MAIN.cellid, SUM(MAIN.passed) as passed, SUM(MAIN.failed) as failed 
    FROM (
	SELECT f.snr, f.processid, f.cellid, SUM(-1*f.passed) As passed, SUM(-1*f.failed) As failed 
    FROM [Applications_DB05].[dbo].[fm_datas] AS f 
    WHERE (DATEPART(HOUR, f.ddate) = ".$shift."-1) 
    and CONVERT(date, f.ddate) = CONVERT(date, CONVERT(date,(DATEADD(DAY,0,GETDATE())))) 
    and f.snr != 0 GROUP BY f.processid, f.snr, f.cellid    
    UNION   
    SELECT f.snr, f.processid, f.cellid, SUM(f.passed) As passed, SUM(f.failed) As failed 
    FROM [Applications_DB05].[dbo].[fm_datas] AS f 
    WHERE (DATEPART(HOUR, f.ddate) = DATEPART(HOUR,GETDATE())) and CONVERT(date, f.ddate) = CONVERT(date, CONVERT(date,(DATEADD(DAY,0,GETDATE())))) and f.snr != 0 
    GROUP BY f.processid, f.snr, f.cellid
	) AS MAIN 
    INNER JOIN [Applications_DB05].[dbo].[fm_cells] AS C ON C.cellid = MAIN.cellid 
    WHERE C.assy_cellid = ".$selectOption." 
	GROUP BY MAIN.snr, MAIN.processid, MAIN.cellid	
	) AS X
    GROUP BY X.processid
END
ELSE
BEGIN
SELECT X.processid, SUM(X.passed) As passed, SUM(X.failed) as failed 
    FROM (	
	SELECT CONVERT(date,CONVERT(date,(DATEADD(DAY,-1,GETDATE())))) as ddate, MAIN.snr, MAIN.processid, MAIN.cellid, SUM(MAIN.passed) as passed, SUM(MAIN.failed) as failed 
    FROM (
	--ELŐZŐ NAP 21:59-ig
	SELECT f.snr, f.processid, f.cellid, SUM(-1*f.passed) As passed, SUM(-1*f.failed) As failed 
    FROM [Applications_DB05].[dbo].[fm_datas] AS f 
    WHERE (DATEPART(HOUR, f.ddate) = 21) and CONVERT(date, f.ddate) = CONVERT(date, CONVERT(date,(DATEADD(DAY,-1,GETDATE())))) and f.snr != 0 
	GROUP BY f.processid, f.snr, f.cellid     
    UNION   
	-- ELŐZŐ NAP 23:59-ig
    SELECT f.snr, f.processid, f.cellid, SUM(f.passed) As passed, SUM(f.failed) As failed 
    FROM [Applications_DB05].[dbo].[fm_datas] AS f 
    WHERE (DATEPART(HOUR, f.ddate) = 23) and CONVERT(date, f.ddate) = CONVERT(date, CONVERT(date,(DATEADD(DAY,-1,GETDATE())))) and f.snr != 0 
    GROUP BY f.processid, f.snr, f.cellid	
	UNION
	--AKTUÁLIS NAP AKTUÁLIS ÓRÁIG
	SELECT f.snr, f.processid, f.cellid, SUM(f.passed) As passed, SUM(f.failed) As failed 
    FROM [Applications_DB05].[dbo].[fm_datas] AS f 
    WHERE (DATEPART(HOUR, f.ddate) = DATEPART(HOUR,GETDATE())) and CONVERT(date, f.ddate) = CONVERT(date, CONVERT(date,(DATEADD(DAY,0,GETDATE())))) and f.snr != 0 
    GROUP BY f.processid, f.snr, f.cellid	
	) AS MAIN 
    INNER JOIN [Applications_DB05].[dbo].[fm_cells] AS C ON C.cellid = MAIN.cellid 
    WHERE C.assy_cellid = ".$selectOption." 
	GROUP BY MAIN.snr, MAIN.processid, MAIN.cellid	
	) AS X
    GROUP BY X.processid
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