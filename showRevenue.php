<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>FastRepair, Inc.</title>
    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="data_index.css" rel="stylesheet">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
</head>

<body id="page-top" data-spy="scroll">

  <nav class="navbar navbar-custom navbar-static-top" role="navigation">
    <div class="container">
      <div class="navbar-header">
        <a class="navbar-brand page-scroll" href="#">FastRepairs, Inc.</a>
      </div>
      <div class="navbar-right">
        <ul class="nav navbar-nav">
          <li class="hidden"><a href="#page-top"></a></li>
          <li><a class="page-scroll" href="#">Customer</a></li>
          <li><a class="page-scroll" href="#">Employer</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="intro">
	<div class="container img-rounded col-md-offset-2 col-md-4 search-box">
	  <div class="row">
		<?php

		if ($_SERVER["REQUEST_METHOD"] == "POST") {

			$date1= $_POST['date1'];
			$date2= $_POST['date2'];  
			
			if(empty($date1) or empty($date2))
				echo "<h3> You didn't enter two dates. Go back and enter them to generate the total revenue.</h3>";
			else 
				showRevenue($date1, $date2);					
		}

		function showRevenue($date1,$date2)
		{	
			$conn=oci_connect('ireyhano','xxxxxx', '//dbserver.engr.scu.edu/db11g');
			if ($conn === false) {
				print "<br> connection failed:";
				exit;
			}
			
			$query = oci_parse($conn, "select calculateRevenue(:date1, :date2) from DUAL");
				
			oci_bind_by_name($query, ':date1', $date1);
			oci_bind_by_name($query, ':date2', $date2);

			oci_execute($query);

			$row = oci_fetch_array($query, OCI_BOTH);
			echo "<p> Total revenue generated: $row[0] </p>";

			OCILogoff($conn);
		}
		?>
	  </div>
	</div>
  </div>
</body>

</html>
