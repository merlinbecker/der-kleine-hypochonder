<?php
/**
SQLite examles: https://www.if-not-true-then-false.com/2012/php-pdo-sqlite3-example/

**/
define ("DATABASE","sqlite:data/dkh_db.sqlite3");

try {
    $file_db = new PDO(DATABASE);
    $file_db->setAttribute(PDO::ATTR_ERRMODE, 
                            PDO::ERRMODE_EXCEPTION);
  
	// Select all data from memory db messages table 
    $result = $file_db->query('SELECT `time` as zeit, heart_beats FROM pebble_health WHERE heart_beats>0 ORDER by zeit ASC');
 
	$output=array();
	foreach($result as $row) {
      $tmp=array("new Date(".$row['zeit']."*1000)",$row['heart_beats']);
	  $output[]=$tmp;
    }
	//remove quotation marks
	$output=json_encode($output);
	$output=str_replace("\"","",$output);
  }
  catch(PDOException $e) {
    // Print PDOException message
    echo $e->getMessage();
  }  
?>

<html>
  <head>
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
	google.charts.load('current', {'packages':['corechart','line','controls']});
    google.charts.setOnLoadCallback(drawStuff);

      function drawStuff() {

        var dashboard = new google.visualization.Dashboard(
          document.getElementById('dashboard_div'));

        // We omit "var" so that programmaticSlider is visible to changeRange.
        var programmaticSlider = new google.visualization.ControlWrapper({
          'controlType': 'DateRangeFilter',
          'containerId': 'filter_div',
          'options': {
            'filterColumnLabel': 'Zeit',
            'ui': {'labelStacking': 'vertical'}
          }
        });

        var programmaticChart  = new google.visualization.ChartWrapper({
          'chartType': 'LineChart',
          'containerId': 'chart_div',
          'options': {
			hAxis: {
				title: 'Zeit'
			},
			vAxis: {
				title: 'Herzschlag'
			},
			colors: ['#a52714']
          }
        });

       var data = new google.visualization.DataTable();
		data.addColumn('datetime', 'Zeit');
		data.addColumn('number', 'HR');
		data.addRows(<?= $output?>);


        dashboard.bind(programmaticSlider, programmaticChart);
        dashboard.draw(data);

        changeRange = function() {
          programmaticSlider.setState({'lowValue': 2, 'highValue': 5});
          programmaticSlider.draw();
        };

        changeOptions = function() {
         // programmaticChart.setOption('is3D', true);
          programmaticChart.draw();
        };
      }
    </script>
  </head>

  <body>
    <!--Div that will hold the pie chart-->
	<div id="dashboard_div">
		<div id="filter_div"></div>
		<div id="chart_div"></div>
	</div>
  </body>
</html>