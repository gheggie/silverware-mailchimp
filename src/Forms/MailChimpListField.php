<?php

/**
 * This file is part of SilverWare.
 *
 * PHP version >=5.6.0
 *
 * For full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 *
 * @package SilverWare\MailChimp\Forms
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-mailchimp
 */

namespace SilverWare\MailChimp\Forms;

use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Flushable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\DropdownField;
use Exception;

/**
 * An extension of the dropdown field class for selecting a MailChimp mailing list.
 *
 * @package SilverWare\MailChimp\Forms
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-mailchimp
 */
class MailChimpListField extends DropdownField implements Flushable
{
    /**
     * Defines the injector dependencies for this object.
     *
     * @var array
     * @config
     */
    private static $dependencies = [
        'api' => '%$MailChimpAPI'
    ];
    
    /**
     * Defines the validity period of the cache in seconds.
     *
     * @var integer
     */
    protected $cacheTimeout = 300;
    
    /**
     * Clears the mailing list cache upon flush.
     *
     * @return void
     */
    public static function flush()
    {
        self::cache()->clear();
    }
    
    /**
     * Answers the cache object.
     *
     * @return CacheInterface
     */
    public static function cache()
    {
        return Injector::inst()->get(CacheInterface::class . '.MailChimpListFieldCache');
    }
    
    /**
     * Answers the field type for the template.
     *
     * @return string
     */
    public function Type()
    {
        return sprintf('mailchimplistfield dropdown %s', parent::Type());
    }
    
    /**
     * Defines the value of the cacheTimeout attribute.
     *
     * @param integer $cacheTimeout
     *
     * @return $this
     */
    public function setCacheTimeout($cacheTimeout)
    {
        $this->cacheTimeout = (integer) $cacheTimeout;
        
        return $this;
    }
    
    /**
     * Answers the value of the cacheTimeout attribute.
     *
     * @return integer
     */
    public function getCacheTimeout()
    {
        return $this->cacheTimeout;
    }
    
    /**
     * Answers the source items for the field.
     *
     * @return array
     */
    public function getSource()
    {
        // Answer Custom Source:
        
        if ($source = $this->source) {
            return $source;
        }
        
        // Answer Cached Source:
        
        if ($source = self::cache()->get($this->getCacheKey())) {
            return $source;
        }
        
        // Obtain Source from API:
        
        $error  = false;
        $source = [];
        
        try {
            
            // Obtain Lists:
            
            $response = $this->api->get('lists');
            
            // Did It Work?
            
            if ($this->api->success()) {
                
                // Define Source:
                
                if (isset($response['lists'])) {
                    
                    foreach ($response['lists'] as $list) {
                        $source[$list['id']] = $list['name'];
                    }
                    
                }
                
                // Cache Source:
                
                if (!empty($source)) {
                    
                    self::cache()->set(
                        $this->getCacheKey(),
                        $source,
                        $this->cacheTimeout
                    );
                    
                }
                
            } else {
                
                // Obtain Last Error:
                
                $error = $this->api->error();
                
            }
            
        } catch (Exception $e) {
            
            // Obtain Exception Message:
            
            $error = $e->getMessage();
            
        }
        
        // Disable On Error:
        
        if ($error) {
            $this->setMessage($error)->setDisabled(true);
        }
        
        // Answer Source:
        
        return $source;
    }
    
    /**
     * Answers the key used with the cache.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return sprintf('mailchimp-api-%s', substr($this->api->getAPIKey(), 0, 8));
    }
}
