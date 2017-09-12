<?php

/**
 * This file is part of SilverWare.
 *
 * PHP version >=5.6.0
 *
 * For full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 *
 * @package SilverWare\MailChimp\API
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-mailchimp
 */

namespace SilverWare\MailChimp\API;

use DrewM\MailChimp\MailChimp;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\SiteConfig\SiteConfig;
use Exception;

/**
 * A singleton wrapper providing access to the MailChimp API.
 *
 * @package SilverWare\MailChimp\API
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-mailchimp
 */
class MailChimpAPI
{
    use Injectable;
    use Configurable;
    
    /**
     * Defines the default API timeout period in seconds.
     *
     * @var integer
     * @config
     */
    private static $default_timeout = 10;
    
    /**
     * Holds the MailChimp API instance.
     *
     * @var DrewM\MailChimp\MailChimp
     */
    protected $api;
    
    /**
     * Defines the API timeout period in seconds.
     *
     * @var integer
     */
    protected $timeout;
    
    /**
     * Constructs the object upon instantiation.
     */
    public function __construct()
    {
        // Define Timeout:
        
        $this->timeout = self::config()->default_timeout;
        
        // Attempt API Instantiation:
        
        try {
            $this->api = new MailChimp($this->getAPIKey());
        } catch (Exception $e) {
            // NOOP
        }
    }
    
    /**
     * Defines the value of the timeout attribute.
     *
     * @param integer $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (integer) $timeout;
        
        return $this;
    }
    
    /**
     * Answers the value of the timeout attribute.
     *
     * @return integer
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
    
    /**
     * Answers the API key from site or YAML configuration.
     *
     * @return string
     */
    public function getAPIKey()
    {
        $key = SiteConfig::current_site_config()->MailChimpAPIKey;
        
        if (!$key) {
            $key = self::config()->api_key;
        }
        
        return $key;
    }
    
    /**
     * Answers true if the receiver has an API key.
     *
     * @return boolean
     */
    public function hasAPIKey()
    {
        return (boolean) $this->getAPIKey();
    }
    
    /**
     * Answers true if the receiver has an API key and the key is valid.
     *
     * @return boolean
     */
    public function isAPIKeyValid()
    {
        return ($this->hasAPIKey() && is_object($this->api));
    }
    
    /**
     * Issues an HTTP DELETE request on the API and answers the response.
     *
     * @params string $method
     * @params array $params
     *
     * @return array
     */
    public function delete($method, $params = [])
    {
        return $this->request('delete', $method, $params);
    }
    
    /**
     * Issues an HTTP GET request on the API and answers the response.
     *
     * @params string $method
     * @params array $params
     *
     * @return array
     */
    public function get($method, $params = [])
    {
        return $this->request('get', $method, $params);
    }
    
    /**
     * Issues an HTTP PATCH request on the API and answers the response.
     *
     * @params string $method
     * @params array $params
     *
     * @return array
     */
    public function patch($method, $params = [])
    {
        return $this->request('patch', $method, $params);
    }
    
    /**
     * Issues an HTTP POST request on the API and answers the response.
     *
     * @params string $method
     * @params array $params
     *
     * @return array
     */
    public function post($method, $params = [])
    {
        return $this->request('post', $method, $params);
    }
    
    /**
     * Issues an HTTP PUT request on the API and answers the response.
     *
     * @params string $method
     * @params array $params
     *
     * @return array
     */
    public function put($method, $params = [])
    {
        return $this->request('put', $method, $params);
    }
    
    /**
     * Answers true if the last request was successful.
     *
     * @return boolean
     */
    public function success()
    {
        return $this->api()->success();
    }
    
    /**
     * Answers the last error message returned by the API.
     *
     * @return string
     */
    public function error()
    {
        return $this->api()->getLastError();
    }
    
    /**
     * Answers the last response returned by the API.
     *
     * @return array
     */
    public function response()
    {
        return $this->api()->getLastResponse();
    }
    
    /**
     * Issues an HTTP request of the specified type and passes the method and parameters.
     *
     * @params string $type
     * @params string $method
     * @params array $params
     *
     * @return array
     */
    protected function request($type, $method, $params)
    {
        return $this->api()->{$type}($method, $params, $this->timeout);
    }
    
    /**
     * Answers the API instance or throws an exception if the API is unavailable.
     *
     * @throws Exception
     *
     * @return DrewM\MailChimp\MailChimp
     */
    protected function api()
    {
        if (!$this->api) {
            throw new Exception('MailChimp API unavailable');
        }
        
        return $this->api;
    }
}
