<?php
    include('db2.php');
    include ("Library.php");

    $AllYield=getAllYield($conn,$mydate,$selectOption,$shift);
    $timer=checkTimer();

    session_start();
    if (!isset($_SESSION['flip'])) {
        $_SESSION['flip'] = false;
    }
    
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="utf-8">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="Chart.bundle.js"></script>
    <script src="chartjs-plugin-datalabels.js"></script>
    <script>var data = <?php echo json_encode($AllYield);?>; </script>
    <script src="TestChart.js"></script>
    <script src="ProdChart.js"></script>
    <!--<script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>-->
    
<title>Dashboard</title>  
    
    
</head>
<body>
   
<div class="header">
    <form action="" method="POST">
        <select name="machine">
            <?php 
            $formStation=getMachineList($conn);
            foreach ($formStation as $station): ?>
            <option value="<?php echo $station['value']; ?>">
            <?php echo $station['name']; ?>
            </option>
            <?php endforeach; ?>
        </select> 
        <input type="submit" name="Választ" alt="Kiválaszt">
    </form>
</div>    
    
<h2>Aktuális létszám: <?php $letszam=getAttendance($conn,$selectOption,$shift);
    echo $letszam ?></h2>

    
<?php  

    
if ($_SESSION['flip']) {
    echo "
<div class='grid-container'>
	<div class='grid-item item1'>
        <h3> Adott műszak tesztberendezések arányai:</h3> 
	</div>
    <div class='grid-item item2'>
		<canvas title='test' id='test' ></canvas> 
	</div> 
</div>";
    $_SESSION['flip'] = false;
} else {
    echo "
<div c<div class='grid-container'>
	<div class='grid-item item3'>
        <h3>Mai Tervezett és Gyártott darabszám:</h3>
	</div> 
    <div class='grid-item item4'>
		<canvas title='prod' id='prod' ></canvas>  
	</div>
    <div class='grid-item item5'>
	   <img src='due.png' class='grid-image'>
    </div> 
</div>";
    $_SESSION['flip'] = true;
}
        
?>

<!--script>
  setTimeout(function() {
    window.location.reload();
  }, 5000);
    
</script-->
 

    
    
</body>
</html>