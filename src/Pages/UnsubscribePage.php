<?php

/**
 * This file is part of SilverWare.
 *
 * PHP version >=5.6.0
 *
 * For full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 *
 * @package SilverWare\MailChimp\Pages
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2018 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-mailchimp
 */

namespace SilverWare\MailChimp\Pages;

use SilverStripe\Forms\TextField;
use SilverWare\Forms\FieldSection;
use SilverWare\MailChimp\Forms\MailChimpListField;
use Page;

/**
 * An extension of the page class for a MailChimp unsubscribe page.
 *
 * @package SilverWare\MailChimp\Pages
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2018 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-ma
 */
class UnsubscribePage extends Page
{
    /**
     * Human-readable singular name.
     *
     * @var string
     * @config
     */
    private static $singular_name = 'Unsubscribe Page';
    
    /**
     * Human-readable plural name.
     *
     * @var string
     * @config
     */
    private static $plural_name = 'Unsubscribe Pages';
    
    /**
     * Description of this object.
     *
     * @var string
     * @config
     */
    private static $description = 'Allows a user to unsubscribe from a MailChimp mailing list';
    
    /**
     * Icon file for this object.
     *
     * @var string
     * @config
     */
    private static $icon = 'silverware/mailchimp: admin/client/dist/images/icons/UnsubscribePage.png';
    
    /**
     * Defines the table name to use for this object.
     *
     * @var string
     * @config
     */
    private static $table_name = 'SilverWare_MailChimp_UnsubscribePage';
    
    /**
     * Maps field names to field types for this object.
     *
     * @var array
     * @config
     */
    private static $db = [
        'ListID' => 'Varchar(32)',
        'OnErrorMessage' => 'Varchar(255)',
        'OnUnsubscribeMessage' => 'Varchar(255)',
        'OnSubcriberNotFoundMessage' => 'Varchar(255)'
    ];
    
    /**
     * Answers a list of field objects for the CMS interface.
     *
     * @return FieldList
     */
    public function getCMSFields()
    {
        // Obtain Field Objects (from parent):
        
        $fields = parent::getCMSFields();
        
        // Add Status Message (if exists):
        
        $fields->addStatusMessage($this->getSiteConfig()->getMailChimpStatusMessage());
        
        // Define Placeholder:
        
        $placeholder = _t(__CLASS__ . '.DROPDOWNSELECT', 'Select');
        
        // Create Main Fields:
        
        $fields->addFieldToTab(
            'Root.Main',
            MailChimpListField::create(
                'ListID',
                $this->fieldLabel('ListID')
            )->setEmptyString(' ')->setAttribute('data-placeholder', $placeholder),
            'Content'
        );
        
        // Create Options Fields:
        
        $fields->addFieldsToTab(
            'Root.Options',
            [
                FieldSection::create(
                    'MessageOptions',
                    $this->fieldLabel('Messages'),
                    [
                        TextField::create(
                            'OnUnsubscribeMessage',
                            $this->fieldLabel('OnUnsubscribeMessage')
                        )->setRightTitle(
                            _t(
                                __CLASS__ . '.ONUNSUBSCRIBEMESSAGERIGHTTITLE',
                                'Shown to the user after unsubscribing.'
                            )
                        ),
                        TextField::create(
                            'OnSubcriberNotFoundMessage',
                            $this->fieldLabel('OnSubcriberNotFoundMessage')
                        )->setRightTitle(
                            _t(
                                __CLASS__ . '.ONSUBSCRIBERNOTFOUNDMESSAGERIGHTTITLE',
                                'Shown to the user if a subscriber cannot be found with the given details.'
                            )
                        ),
                        TextField::create(
                            'OnErrorMessage',
                            $this->fieldLabel('OnErrorMessage')
                        )->setRightTitle(
                            _t(
                                __CLASS__ . '.ONERRORMESSAGERIGHTTITLE',
                                'Shown to the user when an error occurs.'
                            )
                        )
                    ]
                )
            ]
        );
        
        // Answer Field Objects:
        
        return $fields;
    }
    
    /**
     * Answers the labels for the fields of the receiver.
     *
     * @param boolean $includerelations Include labels for relations.
     *
     * @return array
     */
    public function fieldLabels($includerelations = true)
    {
        // Obtain Field Labels (from parent):
        
        $labels = parent::fieldLabels($includerelations);
        
        // Define Field Labels:
        
        $labels['ListID'] = _t(__CLASS__ . '.MAILINGLIST', 'Mailing List');
        $labels['Messages'] = _t(__CLASS__ . '.MESSAGES', 'Messages');
        
        // Define Message Field Labels:
        
        $labels['OnErrorMessage'] = _t(
            __CLASS__ . '.ONERRORMESSAGE',
            'On Error message'
        );
        
        $labels['OnUnsubscribeMessage'] = _t(
            __CLASS__ . '.ONUNSUBSCRIBEMESSAGE',
            'On Unsubscribe message'
        );
        
        $labels['OnSubcriberNotFoundMessage'] = _t(
            __CLASS__ . '.ONSUBSCRIBERNOTFOUNDMESSAGE',
            'On Subscriber Not Found message'
        );
        
        // Answer Field Labels:
        
        return $labels;
    }
    
    /**
     * Populates the default values for the fields of the receiver.
     *
     * @return void
     */
    public function populateDefaults()
    {
        // Populate Defaults (from parent):
        
        parent::populateDefaults();
        
        // Populate Defaults:
        
        $this->Content = _t(
            __CLASS__ . '.DEFAULTCONTENT',
            '<p>Please enter your email address to unsubscribe from the mailing list.</p>'
        );
        
        $this->OnErrorMessage = _t(
            __CLASS__ . '.DEFAULTONERRORMESSAGE',
            'Sorry, an error has occurred. Please try again later.'
        );
        
        $this->OnUnsubscribeMessage = _t(
            __CLASS__ . '.DEFAULTONUNSUBSCRIBEMESSAGE',
            'You have been unsubscribed from our mailing list.'
        );
        
        $this->OnSubscriberNotFoundMessage = _t(
            __CLASS__ . '.DEFAULTONSUBSCRIBERNOTFOUNDMESSAGE',
            'Sorry, we could not find a subscriber with the given details.'
        );
    }
    
    /**
     * Answers a message string to be shown when no list is available.
     *
     * @return string
     */
    public function getNoListMessage()
    {
        return _t(__CLASS__ . '.NOLISTAVAILABLE', 'No list available.');
    }
}
