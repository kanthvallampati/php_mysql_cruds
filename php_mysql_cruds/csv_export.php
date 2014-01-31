<?php
	
	include('includes/actions.php');
	global $sadb;
	
	$res = $sadb->csv_export('ads_banners', 'banner_name,banner_width,banner_height', NULL, NULL, NULL); // Table name, Column Names, JOIN, WHERE conditions, ORDER BY conditions
	
	echo $res;
	
?>