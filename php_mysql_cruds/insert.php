<?php
	
	include('includes/actions.php');
	global $sadb;
	
	$sadb->insert('test',array('name'=>'Name 5','email'=>'name5@email.com')); // Table name, column names and respective values
	$res = $sadb->getResult();
	
	echo "<pre>";
	print_r($res);
	echo "</pre>";
	
?>	