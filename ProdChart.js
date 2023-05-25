$(document).ready(function () {
    var ctx = document.getElementById("prod").getContext("2d");
    //var prodplan = data["HU"]["sumpass"];
    var xValues = [123,432]; //<?php echo json_encode($ProdPlan[1])?>;
    var myChart = new Chart("prod", 
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
            display: false,
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
  });
  