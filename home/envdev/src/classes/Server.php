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
        $servers = 0;
        $this->informations->profile_name      = getenv('PROFILE_NAME')      !== false ? getenv('PROFILE_NAME')     : null;
        $this->informations->projects_path     = getenv('PROJECTS_PATH')     !== false ? getenv('PROJECTS_PATH')    : null;
        $this->informations->php               = getenv('BASE_VERSION')       !== false ? getenv('BASE_VERSION')      : null;
        $this->informations->php_ip            = null;
        if (getenv('BASE_STATIC_IP') !== false && getenv('LANGUAGE_SERVER') == 'php') {
            $this->informations->php_ip =getenv ('BASE_STATIC_IP');
            $this->informations->php_status        = $this->testContainer($this->informations->php_ip);
            $servers++;
        }
        $this->informations->webserver         = getenv('WEB_SERVER')        !== false ? getenv('WEB_SERVER')       : null;
        $this->informations->webserver_version = getenv('WEB_VERSION')       !== false ? getenv('WEB_VERSION')      : null;
        $this->informations->webserver_ip      = null;
        if (getenv('WEB_STATIC_IP') !== false) {
            $this->informations->webserver_ip = getenv('WEB_STATIC_IP');
            $this->informations->webserver_status  = $this->testContainer($this->informations->webserver_ip);
            $servers++;
        }
        $this->informations->cache             = getenv('CACHE_SERVER')      !== false ? getenv('CACHE_SERVER')     : null;
        $this->informations->cache_version     = getenv('CACHE_VERSION')     !== false ? getenv('CACHE_VERSION')    : null;
        $this->informations->cache_ip          = null;
        if (getenv('CACHE_STATIC_IP') !== false) {
            $this->informations->cache_ip = getenv('CACHE_STATIC_IP');
            $this->informations->cache_status      = $this->testContainer($this->informations->cache_ip);
            $servers++;
        }
        $this->informations->db                = getenv('DB_SERVER')         !== false ? getenv('DB_SERVER')        : null;
        $this->informations->db_version        = getenv('DB_VERSION')        !== false ? getenv('DB_VERSION')       : null;
        $this->informations->db_ip             = getenv('DB_STATIC_IP')      !== false ? getenv('DB_STATIC_IP')     : null;
        if (getenv('DB_STATIC_IP') !== false) {
            $this->informations->db_ip = getenv('DB_STATIC_IP');
            $this->informations->db_status         = $this->testContainer($this->informations->db_ip);
            $servers++;
        }
        $this->informations->queuer            = getenv('QUEUER_SERVER')     !== false ? getenv('QUEUER_SERVER')    : null;
        $this->informations->queuer_ip         = getenv('QUEUER_STATIC_IP')  !== false ? getenv('QUEUER_STATIC_IP') : null;
        if (getenv('QUEUER_STATIC_IP')  !== false) {
            $this->informations->queuer_ip         = getenv('QUEUER_STATIC_IP');
            $this->informations->queuer_status     = $this->testContainer($this->informations->queuer_ip);
            $servers++;
        }
        $this->informations->node              = getenv('BASE_VERSION')      !== false ? getenv('BASE_VERSION')     : null;
        $this->informations->node_ip           = getenv('BASE_STATIC_IP')    !== false ? getenv('BASE_STATIC_IP')   : null;
        if (getenv('BASE_STATIC_IP') !== false && getenv('LANGUAGE_SERVER') == 'node') {
            $this->informations->node_ip           = getenv('BASE_STATIC_IP');
            $this->informations->node_status       = $this->testContainer($this->informations->node_ip);
            $servers++;
        }
        $this->informations->language_server   = getenv('LANGUAGE_SERVER');
        $this->informations->count = $servers;

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
