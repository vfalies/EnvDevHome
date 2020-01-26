<?php

class Server
{
    private $informations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->informations = new stdClass;
    }

    public function getInformations()
    {
        $this->informations->profile_name      = getenv('PROFILE_NAME')      !== false ? getenv('PROFILE_NAME')     : null;
        $this->informations->projects_path     = getenv('PROJECTS_PATH')     !== false ? getenv('PROJECTS_PATH')    : null;
        $this->informations->php               = getenv('BASE_VERSION')       !== false ? getenv('BASE_VERSION')      : null;
        $this->informations->php_ip            = getenv('BASE_STATIC_IP')     !== false ? getenv('BASE_STATIC_IP')    : null;
        $this->informations->php_status        = $this->testContainer($this->informations->php_ip);
        $this->informations->webserver         = getenv('WEB_SERVER')        !== false ? getenv('WEB_SERVER')       : null;
        $this->informations->webserver_version = getenv('WEB_VERSION')       !== false ? getenv('WEB_VERSION')      : null;
        $this->informations->webserver_ip      = getenv('WEB_STATIC_IP')     !== false ? getenv('WEB_STATIC_IP')    : null;
        $this->informations->webserver_status  = $this->testContainer($this->informations->webserver_ip);
        $this->informations->cache             = getenv('CACHE_SERVER')      !== false ? getenv('CACHE_SERVER')     : null;
        $this->informations->cache_version     = getenv('CACHE_VERSION')     !== false ? getenv('CACHE_VERSION')    : null;
        $this->informations->cache_ip          = getenv('CACHE_STATIC_IP')   !== false ? getenv('CACHE_STATIC_IP')  : null;
        $this->informations->cache_status      = $this->testContainer($this->informations->cache_ip);
        $this->informations->db                = getenv('DB_SERVER')         !== false ? getenv('DB_SERVER')        : null;
        $this->informations->db_version        = getenv('DB_VERSION')        !== false ? getenv('DB_VERSION')       : null;
        $this->informations->db_ip             = getenv('DB_STATIC_IP')      !== false ? getenv('DB_STATIC_IP')     : null;
        $this->informations->db_status         = $this->testContainer($this->informations->db_ip);
        $this->informations->queuer            = getenv('QUEUER_SERVER')     !== false ? getenv('QUEUER_SERVER')    : null;
        $this->informations->queuer_ip         = getenv('QUEUER_STATIC_IP')  !== false ? getenv('QUEUER_STATIC_IP') : null;
        $this->informations->queuer_status     = $this->testContainer($this->informations->queuer_ip);
        $this->informations->node              = getenv('BASE_VERSION')      !== false ? getenv('BASE_VERSION')     : null;
        $this->informations->node_ip           = getenv('BASE_STATIC_IP')    !== false ? getenv('BASE_STATIC_IP')   : null;
        $this->informations->node_status       = $this->testContainer($this->informations->node_ip);
        $this->informations->language_server   = getenv('LANGUAGE_SERVER');

        return $this->informations;
    }

    public function testContainer($ip)
    {
        if (is_null($ip)) {
            return false;
        }
        $ping = exec('ping -c1 -w2 '.$ip.' |grep transmitted |grep transmitted |cut -f3 -d"," |cut -f1 -d"%"');
        if ($ping ==0) {
            return true;
        }
        return false;
    }
}
