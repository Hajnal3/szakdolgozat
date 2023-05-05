<!DOCTYPE html>
<html lang="en-US">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="Chart.bundle.js"></script>
    <!--<script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>-->
    <script src="chartjs-plugin-datalabels.js"></script>
    
<?php
    header("refresh: 300;");
?>
    

      
<title>Dashboard</title>  
    
</head>
<body>
<?php
    include('db2.php');
    
    echo $mySql;
    echo $selectOption;
    
?>
    
 
<div class="header">
    <form action="db2.php" method="POST">
        <select name="machine">
        <option value=101>Assembly Station 1</option>
        <option value=58>BOOSTER_21</option>
        <option value=15>MGU_18</option>
        <option value=111>MGU_21</option>
        <option value=112>MGU_22</option>
        <option value=14>RAM</option>
        <option value=57>WAVE</option>
        </select> 
        <input type="submit" name="Választ" alt="Kiválaszt">
    </form>
        
<h2>Aktuális létszám: <?php echo json_encode($letszam)?></h2>
    
<h3>Adott műszak tesztberendezések arányai:</h3>

<canvas title="test" id="test" height="50%"></canvas> 


</div>
    
<div class="grid-container">
    
    <!--<canvas title="prod" id="prod" height="50%"></canvas>-->
    
    <div class="grid-item">
        <h3>Mai Tervezett és Gyártott darabszám:</h3>
        <canvas title="prod" id="prod" height="50%"></canvas>
    </div>
    
    <div class="grid-item">
        <table>
            <tr>
                <th>Cikkszám</th>
                <th>Típus</th>
                <th>Terv</th>
                <th>Tény</th>
            </tr>
            <tr>
                <!--https://stackoverflow.com/questions/11943479/create-html-table-from-sql-table-->
                <td>1111111</td>
                <td>próba</td>
                <td>45</td>
                <td>63</td>
            </tr>
            <tr>
                <!--https://stackoverflow.com/questions/11943479/create-html-table-from-sql-table-->
                <td>1111111</td>
                <td>próba</td>
                <td>45</td>
                <td>63</td>
            </tr>
            <tr>
                <!--https://stackoverflow.com/questions/11943479/create-html-table-from-sql-table-->
                <td>1111111</td>
                <td>próba</td>
                <td>45</td>
                <td>63</td>
            </tr>
        </table>
    </div>
</div>
    
<script>
    //Object.keys(data).map(function(d) { return data[d]["sumpass"]; });
    //Chart.plugins.register(ChartDataLabels);
    var ctx = document.getElementById("test").getContext("2d");
    var data = <?=json_encode($processAll)?>;
    var pass = Object.keys(data).map(d => data[d]["sumpass"]);
    var fail = Object.keys(data).map(d => data[d]["sumfail"]);
    var xValues = Object.keys(data);   
    var testChart = new Chart("test", 
    {
    type: "bar",
    data: 
        {
        labels:xValues,
        datasets: 
                [{
                label:'pass',
                data:pass,
                backgroundColor: "rgb(0, 230, 115)",
                },
                {
                label:"fail",
                backgroundColor: "rgb(255, 51, 51)",
                data: fail
                }]
        },
    options: {
        plugins: [ChartDataLabels],
        responsive:true,
        animation: {
            onComplete: function () {
                var ctx = this.chart.ctx;
                ctx.font = Chart.helpers.fontString(50, 'normal', Chart.defaults.global.defaultFontFamily);
                ctx.fillStyle = "white";
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';

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
        scales:{
            grid:{
                borderColor:"rgb(221, 153, 255)",
            },
            
             yAxes: [{
                 gridlines:{
                   zeroLineColor: "rgb(221, 153, 255)"
                 },
                 Color:"rgb(221, 153, 255)",
                display: true,
                    
            }]
        },
        legend: {
              display:false,
          },
        layout: {
            padding: {
                top: 50
            }
        }
    }
        
    });
    
</script>
    
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