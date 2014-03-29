<html>
<head>
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">

        // Load the Visualization API and the piechart package.
        google.load('visualization', '1.0', {'packages':['corechart']});

        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart);

        // Callback that creates and populates a data table,
        // instantiates the pie chart, passes in the data and
        // draws it.
        function drawChart() {
            {
                // Create the level table.
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Topping');
                data.addColumn('number', 'Slices');
                <?php foreach($levelList as $level) { ?>
                    data.addRows([
                        ['<?php echo $level['level'] ?>', <?php echo $level['amount'] ?>]
                    ]);
                <?php } ?>
                // Set chart options
                var options = {'title':'Players levels',
                    'width':400,
                    'height':400};
                // Instantiate and draw our chart, passing in some options.
                var chart = new google.visualization.PieChart(document.getElementById('level_div'));
                chart.draw(data, options);
            }

            {
                // Create the level table.
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Topping');
                data.addColumn('number', 'Slices');
                <?php foreach($musicList as $level) { ?>
                data.addRows([
                    ['<?php echo $level['music'] ?>', <?php echo $level['amount'] ?>]
                ]);
                <?php } ?>
                // Set chart options
                var options = {'title':'Players music',
                    'width':400,
                    'height':400};
                // Instantiate and draw our chart, passing in some options.
                var chart = new google.visualization.PieChart(document.getElementById('music_div'));
                chart.draw(data, options);
            }

            {
                // Create the level table.
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Topping');
                data.addColumn('number', 'Slices');
                <?php foreach($sfxList as $level) { ?>
                data.addRows([
                    ['<?php echo $level['sfx'] ?>', <?php echo $level['amount'] ?>]
                ]);
                <?php } ?>
                // Set chart options
                var options = {'title':'Players sfx',
                    'width':400,
                    'height':400};
                // Instantiate and draw our chart, passing in some options.
                var chart = new google.visualization.PieChart(document.getElementById('sfx_div'));
                chart.draw(data, options);
            }

            {
                // Create the level table.
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Topping');
                data.addColumn('number', 'Slices');
                <?php foreach($coinsList as $level) { ?>
                data.addRows([
                    ['From  ' + (<?php echo $level['hundreds'] ?> * 500) + ' coins', <?php echo $level['amount'] ?>]
                ]);
                <?php } ?>
                // Set chart options
                var options = {'title':'Players coins',
                    'width':1500,
                    'height':400};
                // Instantiate and draw our chart, passing in some options.
                var chart = new google.visualization.ColumnChart(document.getElementById('coins_div'));
                chart.draw(data, options);
            }

            {
                // Create the level table.
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Topping');
                data.addColumn('number', 'Slices');
                <?php foreach($bucksList as $level) { ?>
                data.addRows([
                    ['From  ' + (<?php echo $level['hundreds'] ?> * 5) + ' bucks', <?php echo $level['amount'] ?>]
            ]);
                <?php } ?>
                // Set chart options
                var options = {'title':'Players coins',
                    'width':1500,
                    'height':400};
                // Instantiate and draw our chart, passing in some options.
                var chart = new google.visualization.ColumnChart(document.getElementById('bucks_div'));
                chart.draw(data, options);
            }
        }
    </script>
</head>

<body>
<!--Div that will hold the pie chart-->
<div id="level_div" style="display:inline-block"></div>
<div id="music_div" style="display:inline-block"></div>
<div id="sfx_div" style="display:inline-block"></div><br />
<div id="coins_div" style="display:inline-block"></div>
<div id="bucks_div" style="display:inline-block"></div>
</body>
</html>
