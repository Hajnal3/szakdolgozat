<form action="" method="post">
<select name="cell">
    <option value=41>BOOSTER_18</option>
    <option value=138>BOOSTER_22</option>
    <option value=42>MGU_18</option>
    <option value=139>MGU_21</option>
    <option value=155>MGU_22</option>
    <option value=138>RAM</option>
    <option value=140>WAVE</option>
</select>
<input type="submit" name="submit">
</form>
<?php
    include('db.php');
?>

<script src="Chart.bundle.js"></script>
<script src="chartjs-plugin-datalabels.js"></script>
    
<canvas id="test" width="400" height="400"></canvas>
<script>
    ///Chart.plugins.register(ChartDataLabels);
    var ctx = document.getElementById("test").getContext("2d");
    var prodplan = <?php echo json_encode($ProdPlan)?>;
    var xValues =  <?php echo json_encode($ProdPlan)?>;
    var myChart = new Chart("test", 
    {
    type: "horizontalBar",
    data: 
        {
        labels:xValues,
        datasets: 
                [{
                data: prodplan,
                backgroundColor: ["rgb(0, 100, 153)","rgb(0, 167, 228)"],
                }]
        },
    options: {
        plugins: [ChartDataLabels],
        responsive:true,
        animation: {
            onComplete: function () {
                var ctx = this.chart.ctx;
                ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontFamily, 'normal', Chart.defaults.global.defaultFontFamily);
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
        options: {
        scales: {
            x: {
                stacked: true
            },
            y: {
                stacked: true
            },
        },
        legend: {
            display: false,
            labels: {
                fontColor: 'white', 
            }
        },
    },
    },
    });
</script>