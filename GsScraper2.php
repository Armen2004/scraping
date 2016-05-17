<?php

require_once("GsScraperInterface2.php");
require_once("GsScraper2AddJobListener.php");

abstract class GsScraper2 implements GsScraperInterface2
{

    private $addJobListeners = array();
    private $scrapperName;

    private $jobs = array();

    public function addAddJobListener($listener)
    {
        $this->addJobListeners[] = $listener;
    }

    protected function notifyAddJobListeners($job)
    {
        foreach ($this->addJobListeners as $listener) {
            $listener->jobAdded2($this, $job);
        }
    }

    //Alias functions - START
    public function getJobs()
    {
        return $this->jobs;
    }

    public function getJobsCount()
    {
        return count($this->jobs);
    }

    //Alias functions - END

    public function getJobList()
    {
        return $this->jobs;
    }

    public function getJobListSize()
    {
        return count($this->jobs);
    }

    public function setJobToList($link, $key = '', $title = '', $snippet = '', $city = '', $state = '', $country = '', $datePosted = '', $companyName = '')
    {

        if (!isset($this->jobs[$link])) {
            $job = array(
                "key" => $key,
                "title" => $title,
                "snippet" => $snippet,
                "link" => $link,
                "city" => $city,
                "state" => $state,
                "country" => $country,
                "date_posted" => $datePosted,
                "company_name" => $companyName,
                "provider" => $this->scrapperName
            );

            $this->jobs[$link] = $job;
            $this->notifyAddJobListeners($job);
        } else {

            $this->jobs[$link]['key'] = ($key != '' ? $key : $this->jobs[$link]['key']);
            $this->jobs[$link]['title'] = ($title != '' ? $title : $this->jobs[$link]['title']);
            $this->jobs[$link]['snippet'] = ($snippet != '' ? $snippet : $this->jobs[$link]['snippet']);
            $this->jobs[$link]['city'] = ($city != '' ? $city : $this->jobs[$link]['city']);
            $this->jobs[$link]['state'] = ($state != '' ? $state : $this->jobs[$link]['state']);
            $this->jobs[$link]['country'] = ($country != '' ? $country : $this->jobs[$link]['country']);
            $this->jobs[$link]['date_posted'] = ($datePosted != '' ? $datePosted : $this->jobs[$link]['date_posted']);
            $this->jobs[$link]['company_name'] = ($companyName != '' ? $companyName : $this->jobs[$link]['company_name']);
        }
    }

    abstract public function loadJobDetails($url);

    public function getJobDetails($url)
    {
        $this->loadJobDetails($url);

        return $this->jobs[$url];
    }

    protected function setScrapperName($name)
    {
        $this->scrapperName = $name;
    }

    public function getScrapperName()
    {
        return $this->scrapperName;
    }

    public function saveToXML($path, $jobs = array())
    {

        //print_r($this->jobs);

        if (count($this->jobs) == 0 || !is_array($this->jobs)) {
            $this->jobs = $jobs;

            //print_r($jobs);
        }

        if ($handle = fopen($path, 'w+')) {
            $content = '<?xml version="1.0" encoding="utf-8"?>';
            $content .= '<data date="' . date('Y-m-d') . '" scraper="' . $this->getScrapperName() . '">';

            foreach ($this->jobs as $job) {
                $xml = '
						<job>
							<key><![CDATA[' . $job['key'] . ']]></key>
							<title><![CDATA[' . $job['title'] . ']]></title>
							<snippet><![CDATA[' . $job['snippet'] . ']]></snippet>
							<link><![CDATA[' . $job['link'] . ']]></link>
							<city><![CDATA[' . $job['city'] . ']]></city>
							<state><![CDATA[' . $job['state'] . ']]></state>
							<country><![CDATA[' . $job['country'] . ']]></country>
							<company_name><![CDATA[' . $job['company_name'] . ']]></company_name>
							<provider><![CDATA[' . $job['provider'] . ']]></provider>
							<date_posted><![CDATA[' . $job['date_posted'] . ']]></date_posted>
						</job>
						';

                $content .= $xml;
            }

            $content .= '</data>';

            fwrite($handle, $content);

            fclose($handle);
        } else {
            throw new Exception('Can not open file "' . $path . '" for writing');
        }
    }
}


?>