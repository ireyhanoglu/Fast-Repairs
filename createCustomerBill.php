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

			$machineid= $_POST['cust-machineid']; 

			
			if(!empty($machineid))
				createCustomerBill($machineid);
			else {
				echo "<h2> You didn't enter the device ID. Go back and enter a device ID to generate the customer bill.</h2>";
			} 
					
		}
		function createCustomerBill($machineid)
		{	
			$conn=oci_connect('ireyhano','xxxxxx', '//dbserver.engr.scu.edu/db11g');
			if ($conn === false) {
				print "<br> connection failed:";
				exit;
			}
			
			$query = oci_parse($conn, "select custname,custphone,model,timein,timeout,probid,problem,hours,costofparts,totalcharge from customerbill where machineid=:machineid");
			oci_bind_by_name($query, ":machineid", $machineid);

			oci_execute($query);

			while (($row = oci_fetch_array($query, OCI_BOTH)) != false) {
				echo "<p> Customer Name: $row[0] </p>";
				echo "<p> Customer Phone: $row[1] </p>";
				echo "<p> Model: $row[2] </p>";
				echo "<p> Time Brought In: $row[3] </p>";
				echo "<p> Time Since Ready: $row[4] </p>";
				echo "<p> Problem ID: $row[5] </p>";
				echo "<p> Problem Description: $row[6] </p>";
				echo "<p> Hours of labor: $row[7] </p>";
				echo "<p> Cost of All Parts: $row[8] </p>";
				echo "<p> Total Charge: $row[9] </p>";
			}
			OCILogoff($conn);
		}
		?>
	  </div>
	</div>
  </div>
</body>

</html>



