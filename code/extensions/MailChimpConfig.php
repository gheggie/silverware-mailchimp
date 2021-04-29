<?php

/**
 * An extension of the SilverWare services config class to add MailChimp settings to site config.
 */
class MailChimpConfig extends SilverWareServicesConfig
{
    private static $db = array(
        'MailChimpAPIKey' => 'Varchar(128)'
    );
    
    /**
     * Answers an status message to display in the CMS interface.
     *
     * @return array
     */
    public static function get_status_message()
    {
        if (!MailChimpAPI::get_api_key()) {
            
            return array(
                'text' => _t(
                    'MailChimpConfig.APIKEYMISSINGMESSAGE',
                    'MailChimp API key has not been entered into site configuration'
                ),
                'type' => 'warning',
                'icon' => 'fa-warning'
            );
            
        }
    }
    
    /**
     * Updates the CMS fields of the extended object.
     *
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        // Update Field Objects (from parent):
        
        parent::updateCMSFields($fields);
        
        // Create MailChimp Tab:
        
        $fields->findOrMakeTab(
            'Root.SilverWare.Services.MailChimp',
            _t('MailChimpConfig.MAILCHIMP', 'MailChimp')
        );
        
        // Create Field Objects:
        
        $fields->addFieldToTab(
            'Root.SilverWare.Services.MailChimp',
            ToggleCompositeField::create(
                'MailChimpAPIToggle',
                _t('MailChimpConfig.API', 'API'),
                array(
                    TextField::create(
                        'MailChimpAPIKey',
                        _t('MailChimpConfig.MAILCHIMPAPIKEY', 'MailChimp API Key')
                    )->setRightTitle(
                        _t(
                            'MailChimpConfig.MAILCHIMPAPIKEYRIGHTTITLE',
                            'Create an API key from your MailChimp account page and paste the key here.'
                        )
                    )
                )
            )
        );
    }
}
