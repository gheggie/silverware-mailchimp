<?php

use \DrewM\MailChimp\MailChimp;

/**
 * Singleton class acting as a wrapper for the thirdparty MailChimp API class.
 */
class MailChimpAPI
{
    /**
     * @config
     * @var string
     */
    private static $api_key;
    
    /**
     * @var MailChimpAPI Singleton instance of the receiver.
     */
    protected static $instance;
    
    /**
     * @var MailChimp Instance of MailChimp API wrapper.
     */
    protected $api;
    
    /**
     * Answers the MailChimp API instance.
     *
     * @return MailChimpAPI
     */
    public static function inst()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
    
    /**
     * Answers the key for the MailChimp API from either site config or YAML config.
     *
     * @return string
     */
    public static function get_api_key()
    {
        $key = trim(SiteConfig::current_site_config()->MailChimpAPIKey);
        
        if (!$key) {
            $key = trim(Config::inst()->get(__CLASS__, 'api_key', Config::FIRST_SET));
        }
        
        return $key;
    }
    
    /**
     * Routes inaccessible methods to the MailChimp API instance.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->api, $name), $arguments);
    }
    
    /**
     * Constructs the object upon instantiation (protected for Singleton pattern).
     */
    protected function __construct()
    {
        $this->api = new MailChimp(self::get_api_key());
    }
}
