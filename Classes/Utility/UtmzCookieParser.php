<?php

namespace Mediatis\Formrelay\Utility;

/**
 * UTMZ Cookie Parser parses values from Google Analytics cookies into variables
 * for population into hidden fields, databases or elsewhere
 * see http://daleconboy.com/portfolio/code/google-utmz-cookie-parser for more information
 */
class UtmzCookieParser
{

    public $utmz_source;
    public $utmz_medium;
    public $utmz_term;
    public $utmz_content;
    public $utmz_campaign;
    public $utmz_gclid;
    public $utmz;
    public $utmz_domainHash;
    public $utmz_timestamp;
    public $utmz_sessionNumber;
    public $utmz_campaignNumber;

    //Contstructor fires method that parses and assigns property values
    public function __construct()
    {
        $this->setUtmz();
    }

    //Grab utmz cookie if it exists
    private function setUtmz()
    {
        if (isset($_COOKIE['__utmz'])) {
            $this->utmz = $_COOKIE['__utmz'];
            $this->parseUtmz();
        } else {
            return false;
        }
    }

    //parse utmz cookie into variables
    private function parseUtmz()
    {
        //Break cookie in half
        $utmz_b = strstr($this->utmz, 'u');
        $utmz_a = substr($this->utmz, 0, strpos($this->utmz, $utmz_b) - 1);

        //assign variables to first half of cookie
        list(
            $this->utmz_domainHash,
            $this->utmz_timestamp,
            $this->utmz_sessionNumber,
            $this->utmz_campaignNumber
            ) = explode(
            '.',
            $utmz_a
        );
        //break apart second half of cookie
        $utmzPairs = [];
        $z = explode('|', $utmz_b);
        foreach ($z as $value) {
            $v = explode('=', $value);
            $utmzPairs[$v[0]] = $v[1];
        }
        //Variable assignment for second half of cookie
        foreach ($utmzPairs as $key => $value) {
            switch ($key) {
                case 'utmcsr':
                    $this->utmz_source = $value;
                    break;
                case 'utmcmd':
                    $this->utmz_medium = $value;
                    break;
                case 'utmctr':
                    $this->utmz_term = $value;
                    break;
                case 'utmcct':
                    $this->utmz_content = $value;
                    break;
                case 'utmccn':
                    $this->utmz_campaign = $value;
                    break;
                case 'utmgclid':
                    $this->utmz_gclid = $value;
                    break;
                default:
                    //do nothing
            }
        }
    }
}
