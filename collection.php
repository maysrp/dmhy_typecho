<?php
	require  './conf/config.php';
	require  './class/medoo.php';
	require './class/alibrary.php';
	include_once 'phpQuery/phpQuery.php';

	$alibrary=new alibrary();
	$alibrary->index();