<?php

class Home
{
    private $http_host;
    private $host;
    private $port;
    private $tools    = [];
    private $projects = [];
    private $profiles = [];
    private $vhosts   = [];
    private $version;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->http_host = explode(':', $_SERVER['HTTP_HOST']);
        $this->host = $this->http_host[0];
        $this->port = (isset($this->http_host[1])) ? $this->http_host[1] : 80;

        $this->setVersion();
        $this->setTools();
        $this->loadVHosts();
        $this->setProjects();
        $this->setProfiles();
        $this->readComposer();
    }

    /**
     * Set Tools
     *
     * @return Home
     */
    private function setTools()
    {
        // Setting tools
        if (getenv('DB_SERVER')) {
            $this->addTool('Database Admin', getenv('DBADMIN_PORT'), 'fa-list');
        }
        if (getenv('MAILDEV_PORT')) {
            $this->addTool('MailDev', getenv('MAILDEV_PORT'), 'fa-envelope');
        }
        if (getenv('LANGUAGE_SERVER') == 'php' ) {
            $this->addTool('PhpInfo', $this->port, 'fa-info-circle', 'http://phpinfo.localhost');
        }
        if (getenv('QUEUER_SERVER')) {
            $this->addTool('Queuer', getenv('QUEUER_PORT'), 'fa-align-justify');
        }
        if (getenv('CACHE_SERVER') && getenv('CACHE_SERVER') == 'redis') {
            $this->addTool('Redis admin', getenv('CACHEADMIN_PORT'), 'fa-users-cog');
        }

        return $this;
    }

    /**
     * Set envdev version
     *
     * @return self
     */
    private function setVersion()
    {
        if (file_exists('/var/www/html/envdev/version')) {
            $this->version = file_get_contents('/var/www/html/envdev/version');
        }
        return $this;
    }

    /**
     * Get envdev version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     *
     * Add Tool item
     *
     * @param string $name
     * @param string $port
     * @param string $icon
     * @param string $path
     * @return Home
     */
    private function addTool($name, $port, $icon, $path = null)
    {
        $tool       = new stdClass;
        $tool->name = $name;
        $tool->icon = $icon;
        if (strpos($path, 'http') !== false)
        {
            $tool->port = '';
            $tool->url  = $path;
        } else {
            $tool->port = $port;
            $tool->icon = $icon;
            $tool->url  = 'http://' . $this->host . ':' . $port . $path;
        }

        array_push($this->tools, $tool);

        return $this;
    }

    /**
     * Set projects from disk
     *
     * @return self
     */
    private function setProjects()
    {
        $projects_path = getenv('PROJECTS_PATH_DEST');
        $directories = glob($projects_path . '/*', GLOB_ONLYDIR);

        foreach ($directories as $directory) {
            $exploded_directories = explode('/', $directory);
            $reversed_directories = array_reverse($exploded_directories);

            $project            = new stdClass();
            $project->path      = $directory;
            $project->directory = $reversed_directories[0];
            $project->url       = '//localhost/' . $project->directory;
            $project->hostname  = $this->getProjectHostname($directory);

            array_push($this->projects, $project);
        }

        return $this;
    }

    /**
     * Set profiles from disk
     *
     * @return void
     */
    private function setProfiles()
    {
        $profiles_path = '/var/www/html/profiles';
        $files = glob($profiles_path.'/*');

        // Read default file
        $content_default = '';
        if (file_exists($profiles_path.'/default.env'))
        {
            $content_default = file_get_contents($profiles_path.'/default.env');
        }

        foreach ($files as $file) {
            $profile = new stdClass();
            $profile->name = basename($file, '.env');
            if ($profile->name == 'default') {
                continue;
            }
            $profile->default = false;
            if (strpos($content_default, 'PROFILE_NAME='.$profile->name) !== false) {
                $profile->default = true;
            }

            $profile->current = (getenv('PROFILE_NAME') == $profile->name) ? true: false;

            array_push($this->profiles, $profile);
        }

        return $this;
    }

    /**
     * Read composer file if exist, to extract name & description
     *
     * @return self
     */
    private function readComposer()
    {
        for ($i = 0; $i < count($this->projects); $i++) {
            $composer = ($this->projects[$i])->path . DIRECTORY_SEPARATOR . 'composer.json';
            if (file_exists($composer)) {
                $json_file = file_get_contents($composer);
                $json = json_decode($json_file);

                if (isset($json->name)) {
                    ($this->projects[$i])->name = $json->name;
                }
                if (isset($json->description)) {
                    ($this->projects[$i])->description = $json->description;
                }
            }
        }

        return $this;
    }

    /**
     * Get project hostname if defined in vhost
     *
     * @param string $directory
     * @return string|null
     */
    private function getProjectHostname($directory)
    {
        $webserver = getenv('WEB_SERVER');
        if (isset($this->vhosts[$webserver]) && is_array($this->vhosts[$webserver])) {
            foreach ($this->vhosts[$webserver] as $vhostfile) {
                switch ($webserver) {
                    case 'nginx':
                        $hostname = $this->readNGinxVHOst(file('/envdevconf/nginx/vhosts/' . $vhostfile->name), $directory);
                        break;
                    case 'apache':
                        $hostname = $this->readApacheVHOst(file('/envdevconf/apache/vhosts/' . $vhostfile->name), $directory);
                        break;
                    default:
                        throw new Exception('Unknown WebServer : ' . $webserver);
                }
                if (!is_null($hostname)) {
                    return $hostname;
                }
            }
        }
        return null;
    }

    /**
     * Read NGinx Vhosts
     *
     * @param string $vhost_content
     * @param string $directory
     * @return string|null
     */
    private function readNGinxVHOst($vhost_content, $directory)
    {
        $hostname = $server_name = null;
        // Detect server name from vhost file
        foreach ($vhost_content as $line) {
            if (strstr($line, 'server_name') !== false) {
                $server_name = str_replace('server_name', '', $line);
                $server_name = str_replace(';', '', $server_name);
                $server_name = explode(' ', trim($server_name));
                $server_name = $server_name[0];
            }
        }
        if (!is_null($server_name)) {
            foreach ($vhost_content as $line) {
                if (strstr($line, 'root ') !== false) {
                    $line = trim(str_replace('root ', '', $line));
                    if (strstr($line, $directory . ';') !== false || strstr($line, $directory . '/') !== false) {
                        $hostname = $server_name;
                    }
                }
            }
        }
        return $hostname;
    }

    /**
     * Read Apache Vhosts
     *
     * @param string $vhost_content
     * @param string $directory
     * @return string|null
     */
    private function readApacheVHost($vhost_content, $directory)
    {
        $hostname = $server_name = null;
        foreach ($vhost_content as $line) {
            if (strstr($line, 'ServerName') !== false) {
                $server_name = trim(str_replace('ServerName', '', $line));
            }
        }
        if (!is_null($server_name)) {
            foreach ($vhost_content as $line) {
                if (strstr($line, 'DocumentRoot') !== false) {
                    $line = trim(str_replace('DocumentRoot', '', $line));
                    if (strstr($line, $directory) !== false || strstr($line, $directory . '/') !== false) {
                        $hostname = $server_name;
                    }
                }
            }
        }
        return $hostname;
    }

    /**
     * Get Tools list array
     *
     * @return array
     */
    public function getTools()
    {
        return $this->tools;
    }

    /**
     * Get Projects list array
     *
     * @return array
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Get profiles list array
     *
     * @return array
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

    /**
     * Get VHosts list array
     *
     * @return array
     */
    public function getVHosts()
    {
        return $this->vhosts;
    }

    private function loadVHosts()
    {
        // Load Apache VHosts
        $directories = glob('/envdevconf/apache/vhosts/*');

        $this->vhosts['apache'] = [];
        foreach ($directories as $directory) {
            $vhost           = new stdClass();
            $vhost->name     = (array_reverse(explode('/', $directory)))[0];
            $vhost->hostname = $this->getProjectHostname($vhost->name);

            array_push($this->vhosts['apache'], $vhost);
        }

        // Load NGinx VHosts
        $directories = glob('/envdevconf/nginx/vhosts/*');

        $this->vhosts['nginx'] = [];
        foreach ($directories as $directory) {
            $vhost           = new stdClass();
            $vhost->name     = (array_reverse(explode('/', $directory)))[0];
            $vhost->hostname = $this->getProjectHostname($vhost->name);

            array_push($this->vhosts['nginx'], $vhost);
        }
        return $this;
    }
}
