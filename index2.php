<!DOCTYPE html>
<html lang="en-US">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="utf-8">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <!--<meta http-equiv="X-UA-Compatible" content="IE=Edge"-->
    <!--meta name="viewport" content="width=device-width, initial-scale=1"-->
    <script src="Chart.bundle.js"></script>
    <script src="MyChart.js"></script>
    <!--<script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>-->
    <script src="chartjs-plugin-datalabels.js"></script>
    
<?php
    include('db2.php');
    
 // var_dump($processNames);

    // var_dump($mySqlNight);
?>
    
<title>Dashboard</title>  
    
    
</head>
<body>
   
<div class="header">
    <form action="" method="POST">
        <select name="machine">
            <?php foreach ($stationData as $station): ?>
            <option value="<?php echo $station['value']; ?>"><?php echo $station['name']; ?></option>
            <?php endforeach; ?>
        </select> 
        <input type="submit" name="Választ" alt="Kiválaszt">
    </form>
</div>    
    
<h2>Aktuális létszám: <?php echo json_encode($letszam)?></h2>
    
    <div class='grid-item item2'>
		<canvas title='test' id='test' ></canvas> 
	</div> 
    
       
<?php  
    session_start();
if (!isset($_SESSION['flip'])) {
    $_SESSION['flip'] = false;
}

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
		<canvas title='prod' id='prod'></canvas> 
	</div>
    <div class='grid-item item5'>
	   <img src='due.png' class='grid-image'>
    </div> 
</div>";
    $_SESSION['flip'] = true;
}
    if (checkTimer()) {
    $_SESSION['flip'] = true;
}
        
?>


<!--script>
  setTimeout(function() {
    window.location.reload();
  }, 5000);
    
</script-->
 
    
<script>
    ///Chart.plugins.register(ChartDataLabels);
    var ctx = document.getElementById("prod").getContext("2d");
    var data=<?=json_encode($processAll)?>;
    var prodplan = data["HU"]["sumpass"];
    var xValues = [123,432]; //<?php echo json_encode($ProdPlan[1])?>;
    var prodChart = new Chart("prod", 
    {
    type: "horizontalBar",
    data: 
        {
        labels: xValues,
        datasets: 
                [{
                data: xValues,
                backgroundColor: ["rgb(0, 100, 153)","rgb(0, 167, 228)"],
                },
                /*{
                data: xValues,
                backgroundColor: ["rgb(0, 167, 228)"],
                }*/]
        },
    options: {
        plugins: [ChartDataLabels],
        legend: {
            display: true,
            labels: {
                fontColor: 'white', 
            }
        },
        responsive:true,
        animation: {
            onComplete: function () {
                var ctx = this.chart.ctx;
                ctx.font = Chart.helpers.fontString(50, 'normal', Chart.defaults.global.defaultFontFamily);
                ctx.fillStyle = "white";
                ctx.textAlign = 'center';
                ctx.textBaseline = 'center';

                this.data.datasets.forEach(function (dataset)
                {
                    for (var i = 0; i < dataset.data.length; i++) {
                        for(var key in dataset._meta)
                        {
                        var model = dataset._meta[key].data[i]._model;
                        ctx.fillText(dataset.data[i]+ ' ', model.x, model.y);
                        }
                    }
                });
            }
        },
    },
    });
</script>

    
    
</body>
</html>