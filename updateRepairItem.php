<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // collect input data
     $machineid = $_POST['machineid']; 
     $status = $_POST['status']; 
     $costofparts = $_POST['costofparts']; 
	 $hours = $_POST['hours'];
	 $problemid = $_POST['problemid'];
	
	updateRepairItem($machineid, $status, $costofparts, $hours, $problemid);
	
}

function readyActions($conn,$machineid, $status, $costofparts, $hours, $problemid){
	
	$query = oci_parse($conn, "update repairjob set status = :status, costofparts = :costofparts, hours = :hours where machineid = :machineid");
	
	oci_bind_by_name($query, ':machineid', $machineid);
	oci_bind_by_name($query, ':status', $status);
	oci_bind_by_name($query, ':costofparts', $costofparts);
	oci_bind_by_name($query, ':hours', $hours);

	oci_execute($query);

	$query = oci_parse($conn, "insert into problemreport values (:machineid, :problemid, NULL)");
	
	oci_bind_by_name($query, ':machineid', $machineid);
	oci_bind_by_name($query, ':problemid', $problemid);

	oci_execute($query);

	$query = oci_parse($conn, "insert into ready_repairjob values (:machineid)");
	
	oci_bind_by_name($query, ':machineid', $machineid);

	oci_execute($query);

}

function doneActions($conn,$machineid){

	$query = oci_parse($conn, "update repairjob set status = 'DONE' where machineid = :machineid");
	oci_bind_by_name($query, ':machineid', $machineid);
	oci_execute($query);

	$query = oci_parse($conn, "insert into done_repairjob values (:machineid)");
	oci_bind_by_name($query, ':machineid', $machineid);
	oci_execute($query);

}

function updateRepairItem($machineid, $status, $costofparts, $hours, $problemid){
	
	//connect to your database. Type in your username, password and the DB path
	$conn=oci_connect('ireyhano','xxxxxxx', '//dbserver.engr.scu.edu/db11g');
	if(!$conn) {
	     print "<br> connection failed:";       
        exit;
	}

	if ($status == 'READY')
	{
		readyActions($conn,$machineid, $status, $costofparts, $hours, $problemid);
	}
	else
	{
		doneActions($conn,$machineid);
	}
	//echo $id . ' ' . $model .  ' ' . $price .  ' ' . $year .  ' ' . $contractType . ' ' . $custName .  ' ' . $custPhone;

	OCILogoff($conn);	
}

?>

