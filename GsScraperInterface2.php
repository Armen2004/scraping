<?php 


interface GsScraperInterface2 {

	public function getJobList();
	
	public function getJobDetails($url);
	
	public function parseJobList();
	
	public function getJobListSize();
	
}


?>