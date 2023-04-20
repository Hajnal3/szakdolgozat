<?php
try {
$conn = new PDO("sqlsrv:Database=Applications;server=HISZWSDB08", "MD_USER", "Harman123$") or die;
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
    $myquerry="SELECT COUNT(name) AS letszam FROM Qlik_attendance_presents 
    WHERE area like 'BMW'
    AND plant like  'Székesfehérvár Elektronika'
    AND cshift like '".$shift."' 
    AND '".$mydate."' BETWEEN startdate AND enddate" ;
    //echo $myquerry;
    $present=$conn->query($myquerry);
    $result = $present->fetchAll(PDO::FETCH_NUM);
    $letszam = $result[0][0];
    //$letszam;
    
//get FKT values
    $searchId=array( 10004, 10069, 10073, 10079, 10089, 10140, 10159, 10162, 10165, 10170, 10172, 10182, 10193, 10197);
    $sqltest="SELECT passed, failed FROM [Applications_DB05].[dbo].[fm_datas] WHERE cellid ='".$selectOption."' AND ddate BETWEEN '".$myday." 00:00:00.000' AND '".$myday." 23:59:00.000' AND processid IN (".implode(',',$searchId).")";
    $handleFkt = $conn->query($sqltest);
    $result = $handleFkt->fetchAll(PDO::FETCH_NUM);
        
    $pass_sum=0;
    $fail_sum=0;
    foreach($result as $row)
    {
        //echo $row[0];
        $pass_sum=$row[0]+$pass_sum;
        $fail_sum=$row[1]+$fail_sum;
    }

$passpercFkt=$pass_sum;//round($pass_sum/($pass_sum+$fail_sum)*100,2);
$failpercFkt=$fail_sum;//round($fail_sum/($pass_sum+$fail_sum)*100,2);
 
   
//get FLASH values
    $searchId=array(10066,10067,10146,10180,10215,10216,10230);
    $handleRun = $conn->query("SELECT passed, failed FROM [Applications_DB05].[dbo].[fm_datas] WHERE cellid ='".$selectOption."' AND ddate BETWEEN '".$myday." 00:00:00.000' AND '".$myday." 23:59:00.000' AND processid IN (".implode(',',$searchId).")");
    $result = $handleRun->fetchAll(PDO::FETCH_NUM);
        
    $pass_sum=0;
    $fail_sum=0;
    foreach($result as $row)
    {
        $pass_sum=$row[0]+$pass_sum;
        $fail_sum=$row[1]+$fail_sum;
    }
    
$passpercRun=$pass_sum;//round($pass_sum/($pass_sum+$fail_sum)*100,2);
$failpercRun=$fail_sum;//round($fail_sum/($pass_sum+$fail_sum)*100,2);
    //$dataRun=array();
    //array_push($dataRun, $passpercRun, $failpercRun);
     
//get ASSEMBLY values
$searchId=array(10051,10058,10059,10060,10064,10112,10113,10116,10119,10121,10123,10199,10200,10219);
    $handleRun = $conn->query("SELECT passed, failed FROM [Applications_DB05].[dbo].[fm_datas] WHERE cellid ='".$selectOption."' AND ddate BETWEEN '".$myday." 00:00:00.000' AND '".$myday." 23:59:00.000' AND processid IN (".implode(',',$searchId).")");
    $result = $handleRun->fetchAll(PDO::FETCH_NUM);
        
    $pass_sum=0;
    $fail_sum=0;
    foreach($result as $row)
    {
        $pass_sum=$row[0]+$pass_sum;
        $fail_sum=$row[1]+$fail_sum;
    }
    
$passpercAss=($pass_sum);//round($pass_sum/($pass_sum+$fail_sum)*100,2);
$failpercAss=($fail_sum); //round($fail_sum/($pass_sum+$fail_sum)*100,2);

//get FLASH values
$searchId=array(10066);
    $handleFla = $conn->query("SELECT passed, failed FROM [Applications_DB05].[dbo].[fm_datas] WHERE cellid ='".$selectOption."' AND ddate BETWEEN '".$myday." 00:00:00.000' AND '".$myday." 23:59:00.000' AND processid IN (".implode(',',$searchId).")");
    $result = $handleFla->fetchAll(PDO::FETCH_NUM);
        
    $pass_sum=0;
    $fail_sum=0;
    foreach($result as $row)
    {
        $pass_sum=$row[0]+$pass_sum;
        $fail_sum=$row[1]+$fail_sum;
    }
    
$passpercFla=($pass_sum);//round($pass_sum/($pass_sum+$fail_sum)*100,2);
$failpercFla=($fail_sum); //round($fail_sum/($pass_sum+$fail_sum)*100,2);
    
//Get AFT
$searchId=array(10011);
    $mySql=("SELECT X.processid, SUM(X.passed) As passed, SUM(X.failed) as failed FROM (SELECT CONVERT(date,CONVERT(date,(DATEADD(DAY,0,GETDATE())))) as ddate, MAIN.snr, MAIN.processid, MAIN.cellid, SUM(MAIN.passed) as passed, SUM(MAIN.failed) as failed FROM (SELECT f.snr, f.processid, f.cellid, SUM(-1*f.passed) As passed, SUM(-1*f.failed) As failed FROM [Applications_DB05].[dbo].[fm_datas] AS f WHERE (DATEPART(HOUR, f.ddate) = ".$shift."-1) and CONVERT(date, f.ddate) = CONVERT(date, CONVERT(date,(DATEADD(DAY,0,GETDATE())))) and f.snr != 0 GROUP BY f.processid, f.snr, f.cellid UNION SELECT f.snr, f.processid, f.cellid, SUM(f.passed) As passed, SUM(f.failed) As failed FROM [Applications_DB05].[dbo].[fm_datas] AS f WHERE (DATEPART(HOUR, f.ddate) = DATEPART(HOUR,GETDATE())) and CONVERT(date, f.ddate) = CONVERT(date, CONVERT(date,(DATEADD(DAY,0,GETDATE())))) and f.snr != 0 GROUP BY f.processid, f.snr, f.cellid) AS MAIN INNER JOIN [Applications_DB05].[dbo].[fm_cells] AS C ON C.cellid = MAIN.cellid WHERE MAIN.processid IN (".implode(',',$searchId).") AND C.assy_cellid = ".$selectOption." GROUP BY MAIN.snr, MAIN.processid, MAIN.cellid) AS X ");
    $handleAft = $conn->query($mySql);
    $result = $handleAft->fetchAll(PDO::FETCH_NUM);
        
    $pass_sum=0;
    $fail_sum=0;
    foreach($result as $row)
    {
        $pass_sum=$row[0]+$pass_sum;
        $fail_sum=$row[1]+$fail_sum;
    }
    
$passpercAft=($pass_sum);//round($pass_sum/($pass_sum+$fail_sum)*100,2);
$failpercAft=($fail_sum); //round($fail_sum/($pass_sum+$fail_sum)*100,2);
    
//Get GSL
    $searchId=array(10078,10175);
    $handleGsl = $conn->query("SELECT passed, failed FROM [Applications_DB05].[dbo].[fm_datas] WHERE cellid ='".$selectOption."' AND ddate BETWEEN '".$myday." 00:00:00.000' AND '".$myday." 23:59:00.000' AND processid IN (".implode(',',$searchId).")");
    $result = $handleGsl->fetchAll(PDO::FETCH_NUM);
        
    $pass_sum=0;
    $fail_sum=0;
    foreach($result as $row)
    {
        $pass_sum=$row[0]+$pass_sum;
        $fail_sum=$row[1]+$fail_sum;
    }
    
$passpercGsl=($pass_sum);//round($pass_sum/($pass_sum+$fail_sum)*100,2);
$failpercGsl=($fail_sum); //round($fail_sum/($pass_sum+$fail_sum)*100,2);

//Get HU
    $searchId=array(10010);
    $handleHu = $conn->query("SELECT passed, failed FROM [Applications_DB05].[dbo].[fm_datas] WHERE cellid ='".$selectOption."' AND ddate BETWEEN '".$myday." 00:00:00.000' AND '".$myday." 23:59:00.000' AND processid IN (".implode(',',$searchId).")");
    $result = $handleHu->fetchAll(PDO::FETCH_NUM);
        
    $pass_sum=0;
    $fail_sum=0;
    foreach($result as $row)
    {
        $pass_sum=$row[0]+$pass_sum;
        $fail_sum=$row[1]+$fail_sum;
    }
   
$passpercHu=($pass_sum);//round($pass_sum/($pass_sum+$fail_sum)*100,2);
$failpercHu=($fail_sum); //round($fail_sum/($pass_sum+$fail_sum)*100,2);
    
//Final arrays os passed and failed tests
$passTotal=array();
array_push($passTotal, $passpercFkt, $passpercRun, $passpercAss, $passpercFla,$passpercAft,$passpercGsl, $passpercHu);
    
$failTotal=array();
array_push($failTotal, $failpercFkt, $failpercRun, $failpercAss, $failpercFla,$failpercAft,$failpercGsl,$failpercHu);
    

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