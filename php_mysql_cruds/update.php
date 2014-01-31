<?php

	include('includes/actions.php');
	global $sadb;
	
	$sadb->update('test',array('name'=>"Name 4",'email'=>"name4@email.com"),'id="1" AND name="Name 1"'); // Table name, column names and values, WHERE conditions
	$res = $sadb->getResult();
	
	echo "<pre>";
	print_r($res);
	echo "</pre>";
	
?>
