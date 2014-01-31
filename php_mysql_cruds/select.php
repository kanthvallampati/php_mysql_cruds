<?php 

	include('includes/actions.php');
	global $sadb;
	
	$sadb->select('test', 'name,email', NULL, 'name="john"', 'product_id DESC'); // Table name, Column Names, JOIN, WHERE conditions, ORDER BY conditions
	$res = $sadb->getResult();
	
	echo "<pre>";
	print_r($res);
	echo "</pre>";
	
	echo $rows = $sadb->numRows();
	
?>