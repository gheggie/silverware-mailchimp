<?php

/**
 * This file is part of SilverWare.
 *
 * PHP version >=5.6.0
 *
 * For full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 *
 * @package SilverWare\MailChimp\Extensions
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-mailchimp
 */

namespace SilverWare\MailChimp\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverWare\Extensions\Config\ServicesConfig;
use SilverWare\Forms\FieldSection;
use SilverWare\MailChimp\API\MailChimpAPI;

/**
 * An extension of the services config class which adds MailChimp settings to site configuration.
 *
 * @package SilverWare\MailChimp\Extensions
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-mailchimp
 */
class MailChimpConfig extends ServicesConfig
{
    /**
     * Maps field names to field types for this object.
     *
     * @var array
     * @config
     */
    private static $db = [
        'MailChimpAPIKey' => 'Varchar(128)'
    ];
    
    /**
     * Updates the CMS fields of the extended object.
     *
     * @param FieldList $fields List of CMS fields from the extended object.
     *
     * @return void
     */
    public function updateCMSFields(FieldList $fields)
    {
        // Update Field Objects (from parent):
        
        parent::updateCMSFields($fields);
        
        // Create MailChimp Tab:
        
        $fields->findOrMakeTab(
            'Root.SilverWare.Services.MailChimp',
            $this->owner->fieldLabel('MailChimp')
        );
        
        // Create Field Objects:
        
        $fields->addFieldsToTab(
            'Root.SilverWare.Services.MailChimp',
            [
                FieldSection::create(
                    'MailChimpAPIConfig',
                    $this->owner->fieldLabel('MailChimpAPIConfig'),
                    [
                        TextField::create(
                            'MailChimpAPIKey',
                            $this->owner->fieldLabel('MailChimpAPIKey')
                        )->setRightTitle(
                            _t(
                                __CLASS__ . '.MAILCHIMPAPIKEYRIGHTTITLE',
                                'Create an API key from your MailChimp account page and paste the key here.'
                            )
                        )
                    ]
                )
            ]
        );
    }
    
    /**
     * Updates the field labels of the extended object.
     *
     * @param array $labels Array of field labels from the extended object.
     *
     * @return void
     */
    public function updateFieldLabels(&$labels)
    {
        // Update Field Labels (from parent):
        
        parent::updateFieldLabels($labels);
        
        // Update Field Labels:
        
        $labels['MailChimp'] = _t(__CLASS__ . '.MAILCHIMP', 'MailChimp');
        $labels['MailChimpAPIKey'] = _t(__CLASS__ . '.MAILCHIMPAPIKEY', 'MailChimp API Key');
        $labels['MailChimpAPIConfig'] = _t(__CLASS__ . '.MAILCHIMPAPI', 'MailChimp API');
    }
    
    /**
     * Event method called before the extended object is written to the database.
     *
     * @return void
     */
    public function onBeforeWrite()
    {
        // Clean Attributes:
        
        $this->owner->MailChimpAPIKey = trim($this->owner->MailChimpAPIKey);
    }
    
    /**
     * Answers a status message array for the CMS interface.
     *
     * @return string
     */
    public function getMailChimpStatusMessage()
    {
        $api = MailChimpAPI::singleton();
        
        if (!$api->hasAPIKey()) {
            
            return _t(
                __CLASS__ . '.MAILCHIMPAPIKEYMISSING',
                'MailChimp API key has not been entered into site configuration.'
            );
            
        }
        
        if (!$api->isAPIKeyValid()) {
            
            return _t(
                __CLASS__ . '.MAILCHIMPAPIKEYINVALID',
                'The MailChimp API key configured for the site appears to be invalid.'
            );
            
        }
    }
}
