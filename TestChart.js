
$(document).ready(function () {
  var ctx = document.getElementById("test").getContext("2d");
  var pass = Object.keys(data).map(d => data[d]["sumpass"]);
  var fail = Object.keys(data).map(d => data[d]["sumfail"]);
  var xValues = Object.keys(data);   
  var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: xValues,
       datasets: 
        [{
            label:'pass',
            data:pass,
            backgroundColor: "rgb(0, 230, 115)",
            },{
                label:"fail",
                backgroundColor: "rgb(255, 51, 51)",
                data: fail,
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
    
});

