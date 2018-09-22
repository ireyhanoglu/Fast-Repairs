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
          <li><a class="page-scroll" href="#">Employee</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="intro-status">
	<div class="container img-rounded col-md-offset-2 col-md-4 search-box">
	  <div class="row">
		<?php
		if ($_SERVER["REQUEST_METHOD"] == "POST") {

			$machineid= $_POST['machineid']; 
			$ownerphone = $_POST['ownerphone'];
			
			if(!empty($machineid))
				checkStatusMachineID($machineid);
			else if(!empty($ownerphone))
				checkStatusPhone($ownerphone);
			else {
				echo "<h2> You didn't enter any values. Go back and submit either a device id or a phone #.</h2>";
			} 
					
		}
		function checkStatusMachineID($machineid)
		{	
			$conn=oci_connect('ireyhano','xxxxxx', '//dbserver.engr.scu.edu/db11g');
			if ($conn === false) {
				print "<br> connection failed:";
				exit;
			}
			
			$query = oci_parse($conn, "select status from repairjob where machineid=:machineid");
			oci_bind_by_name($query, ":machineid", $machineid);
			oci_execute($query);

			while (($row = oci_fetch_array($query, OCI_BOTH)) != false) {
				echo "<p> $row[0] </p>";
			}
			OCILogoff($conn);
		}
		function checkStatusPhone($ownerphone)
		{	
			$conn=oci_connect('ireyhano','xxxxxx', '//dbserver.engr.scu.edu/db11g');
			if ($conn === false) {
				print "<br> connection failed:";
				exit;
			}
			
			$query = oci_parse($conn, "select status from repairjob where ownerphone=$ownerphone");

			oci_execute($query);

			while (($row = oci_fetch_array($query, OCI_BOTH)) != false) {
				echo "<p> $row[0] </p>";
			}
			OCILogoff($conn);
		}
		?>
	  </div>
	</div>
  </div>
</body>

</html>



