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
		
		$status = 'UNDER_REPAIR';
		showRepairJobs($status);

		function showRepairJobs($status)
		{	
			$conn=oci_connect('ireyhano','xxxxx', '//dbserver.engr.scu.edu/db11g');
			if ($conn === false) {
				print "<br> connection failed:";
				exit;
			}
			
			$query = oci_parse($conn, "select machineid,contractid,arrivaltime from repairjob where status=:status");
			oci_bind_by_name($query, ":status", $status);

			oci_execute($query);

			while (($row = oci_fetch_array($query, OCI_BOTH)) != false) {
				echo "<p> Device ID: $row[0] </p>";
				if (empty($row[1]))
					echo "<p> Service Contract ID: none </p>";				
				else				
					echo "<p> Service Contract ID: $row[1] </p>";
				echo "<p> Arrival Time: $row[2] </p>";
				echo "<hr>";
			}
			OCILogoff($conn);
		}
		?>
	  </div>
	</div>
  </div>
</body>

</html>
