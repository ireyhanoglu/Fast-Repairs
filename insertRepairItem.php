<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // collect input data
     $custName = $_POST['custName']; 
     $custPhone = $_POST['custPhone']; 
     $device = $_POST['device']; 
	 $price = $_POST['price'];
	 $year = $_POST['year'];
	 $model = $_POST['model'];
	 $contractType = $_POST['contractType'];
	
	insertRepairItemIntoDB($custName, $custPhone, $device, $price, $year, $model, $contractType);
	
}

function howManyRepairItems($conn)
{

	$query = oci_parse($conn, "SELECT repairitemcount from Dual");

	oci_execute($query);
	
	$row = oci_fetch_array($query, OCI_BOTH);
	return $row['REPAIRITEMCOUNT'];
	
}

function insertRepairItemIntoDB($custName, $custPhone, $device, $price, $year, $model, $contractType){
	
	//connect to your database. Type in your username, password and the DB path
	$conn=oci_connect('ireyhano','xxxxx', '//dbserver.engr.scu.edu/db11g');
	if(!$conn) {
	     print "<br> connection failed:";       
        exit;
	}

	$id = howManyRepairItems($conn)+1;

	if($device == 'computer')
	{
		$id = 'c' . $id;
	}
	else
	{
		$id = 'p' . $id;
	}
	//$id = (string) $id;

	//echo $id . ' ' . $model .  ' ' . $price .  ' ' . $year .  ' ' . $contractType . ' ' . $custName .  ' ' . $custPhone;

	$query = oci_parse($conn, "Insert Into repairitems(itemid, model, price, year, contracttype, custname, custphone) values(:id, :model, :price, :year, :contractType, :custName, :custPhone)");
	
	oci_bind_by_name($query, ':id', $id);
	oci_bind_by_name($query, ':model', $model);
	oci_bind_by_name($query, ':price', $price);
	oci_bind_by_name($query, ':year', $year);
	oci_bind_by_name($query, ':contractType', $contractType);
	oci_bind_by_name($query, ':custName', $custName);
	oci_bind_by_name($query, ':custPhone', $custPhone);
	
	// Execute the query
	
	$res = oci_execute($query);

	OCILogoff($conn);	
}

?>

