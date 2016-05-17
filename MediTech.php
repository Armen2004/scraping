<?php

require_once("simple_html_dom.php");
require_once("AbstractWebGsScraper2.php");

class MediTech extends AbstractWebGsScraper2
{
    private $key = 1;
    private $carere_url;

    function __construct($url, $username = "", $password = "")
    {
        parent::__construct($url, $username, $password); //initialize parent constructor
        $this->setScrapperName(__CLASS__); //set ScraperName
        $this->carere_url = $this->getRealPath($url);
    }

    public function getRealPath($url)
    {
        return rtrim(explode('careers', $url)[0], '/');
    }

    public function loadJobDetails($url)
    {
        $this->curl->get($url);
        $content = $this->curl->getResponse();
        $dom = str_get_html(preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $content));
        $title = trim($dom->find('h1[class=page__title]', 0)->plaintext);
        $companyname = 'MediTech';
        $getKey = explode('/', $url);
        $keyCount = count($getKey);
        $key = trim($getKey[$keyCount - 2]);
        $snippet = trim($dom->find('div[class=container__two-third--with-sidebar]', 0)->plaintext);
//        $snippet = $this->getDescription($dom->find('div[class=container__two-third--with-sidebar]', 0));
        $city = '';
        $state = trim(explode('|', $dom->find('div[class=container__two-third--with-sidebar]', 0)->find('p', 0)->plaintext)[1]);
        $country = 'USA';
        $location = '';
        foreach ($dom->find('ul[class=snippet__card__filters] li') as $locations){
            $loc[] = trim($locations->plaintext);
        }
        $location = implode(' , ', $loc);

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
        $as = $dom->find('table tbody td a');
        foreach ($as as $a) {
            $url = $this->carere_url . $a->attr['href'];
            if (strlen($url) > 0) {
                $urls[] = $url;
            }
        }
        return $urls;
    }

    private function getDescription($find)
    {
        $text = '';
        $text .= $find->find('h5', 0)->plaintext . "<br>";
        $text .= $find->find('p', 1)->plaintext . "<br>";
        $text .= $find->find('ul', 0)->plaintext . "<br>";
        $text .= $find->find('h5', 1)->plaintext . "<br>";
        $text .= $find->find('ul', 1)->plaintext . "<br>";

        return $text;
    }
}