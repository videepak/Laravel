<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
@if($tokenid==1)
<script language = "JavaScript">
    google.charts.load('current', {packages: ['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawTrendlines);

    function drawTrendlines() {
        var data = google.visualization.arrayToDataTable(<?php echo $deliverychart; ?>);

        var options = {

            legend: {position: 'top', maxLines: 2},
            bar: {groupWidth: '50%'},
            isStacked: true,
            series: {
                2: {type: 'line'},
                3: {type: 'line'},
            },
            hAxis: {
                //showTextEvery: 1
            },
            colors: ['#8FBC8F', '#228B22', '#FFFF00', '#FF0000', '#ADD8E6']

        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    }
</script>
@elseif($tokenid==2)
<script language = "JavaScript">
    google.charts.load('current', {packages: ['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawTrendlines);

    function drawTrendlines() {
        var data = google.visualization.arrayToDataTable(<?php echo $chartarr; ?>);

        var options = {

            legend: {position: 'top', maxLines: 2},
            bar: {groupWidth: '50%'},
            isStacked: true,
            series: {
                2: {type: 'line'},
                3: {type: 'line'},
            },
            colors: ['#8FBC8F', '#228B22', '#FFFF00', '#FF0000']

        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    }
</script>

@elseif($tokenid==3)

<script language = "JavaScript">
    Highcharts.chart('container', {
        chart: {
            type: 'column'
        },
        title: {
            text: 'Efficiency Report'
        },
        xAxis: {
            categories: <?php echo $chart_dates; ?>
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Total Number of bins'
            }
        },
        legend: {
            reversed: true
        },
        plotOptions: {

        },
        series: <?php echo $chartseries; ?>
    });
</script>
@elseif($tokenid == 4)
<script language = "JavaScript">
    google.charts.load('current', {packages: ['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawTrendlines);

    function drawTrendlines() {
        var data = google.visualization.arrayToDataTable(<?php echo $deliverychart; ?>);

        var options = {

            legend: {position: 'top', maxLines: 2},
            bar: {groupWidth: '50%'},
            isStacked: true,
            // series: {
            //     1: {type: 'line'},
            //     2: {type: 'line'},
            //     3: {type: 'line'},
            //     4: {type: 'line'},
            //     5: {type: 'line'},
            //     6: {type: 'line'},
            //     7: {type: 'line'},
            //     8: {type: 'line'},
            //     9: {type: 'line'},
            //     0: {type: 'line'},
            // },
            hAxis: {
                //showTextEvery: 1
            },
            colors: [
                '#e6194b',
                '#3cb44b',
                '#ffe119',
                '#4363d8',
                '#f58231',
                '#911eb4',
                '#46f0f0',
                '#f032e6',
                '#bcf60c',
                '#fabebe',
            ]
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);

        $(document).on('click', '#png', function () {
            var win = window.open('', $('#chart_div').html());
            win.document.write($('#chart_div').html());

            setTimeout(function () {
                win.document.close();
                win.focus();
                win.print();
                win.close(); 
            }, 1000);
        });
    }
</script>
@endif
