<?php

require_once("simple_html_dom.php");
require_once("AbstractWebGsScraper2.php");

class SmartRecruiters extends AbstractWebGsScraper2
{
    private $key = 1;

    function __construct($url, $username = "", $password = "")
    {
        parent::__construct($url, $username, $password); //initialize parent constructor
        $this->setScrapperName(__CLASS__); //set ScraperName
    }

    public function loadJobDetails($url)
    {
        $this->curl->get($url);
        $content = $this->curl->getResponse();
        $dom = str_get_html(preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $content));
        $title = trim($dom->find('h1[class=job-title]', 0)->plaintext);
        $companyname = 'Micro Strategy';
        $getKey = explode('/', $url);
        $keyCount = count($getKey);
        $key = trim($getKey[$keyCount - 2]);
        $snippet = trim($dom->find('div[class=job-sections]', 0)->plaintext);
        $city = '';
        $state = '';
        $country = 'USA';
        $location = $dom->find('li[itemprop=jobLocation]', 0)->plaintext;
        $location = trim(str_replace('Location:', '', $location));
        $location = explode(',', $location);
        $city = trim($location[0]);
        if (count($location) > 1) {
            $state = trim($location[1]);
        }
        $this->setJobToList($url, $key, $title, $snippet, $city, $state, $country, date('Y-m-d'), $companyname);
    }

    public function parseJobList()
    {
        $cookie_jar = tempnam('/', 'cookie');
        $this->curl->setOpt(CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        $this->curl->setOpt(CURLOPT_FRESH_CONNECT, false);
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $this->curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
        $this->curl->setOpt(CURLOPT_HEADER, true);
        $this->curl->setOpt(CURLOPT_VERBOSE, false);
        $this->curl->setOpt(CURLOPT_COOKIESESSION, true);
        $this->curl->setOpt(CURLOPT_POST, true);
        $this->curl->setOpt(CURLOPT_COOKIEJAR, $cookie_jar);
        $this->curl->setOpt(CURLOPT_COOKIEFILE, $cookie_jar);
        $parsedURLs = array();
        $content = $this->getContent();
        $this->Check_Status(strlen(trim($content)), 'Possibly blocked by the website.');
        $urls = $this->extractjobstourl($content);
        $urls = array_unique($urls);
        foreach ($urls as $url) {
            if (in_array($url, $parsedURLs)) {
                break;
            }
            $this->setJobToList($url);
            $parsedURLs[] = $url;
        }
    }

    private function getContent()
    {
        $url = $this->url;
        $this->curl->get($url);
        $content = $this->curl->getResponse();
        return $content;
    }

    private function Check_Status($strlen, $string)
    {
        if ($strlen === 0) {
            throw new Exception($string);
        }
    }

    private function extractjobstourl($content)
    {
        $dom = str_get_html($content);
        $urls = array();
        $as = $dom->find('li[class=jobs-item]');
        foreach ($as as $k => $a) {
            if(isset($a->find('a', 0)->attr['href'])) {
                $url = $a->find('a', 0)->attr['href'];
                if (strlen($url) > 0) {
                    $urls[] = $url;
                }
            }
        }
        return $urls;
    }
}