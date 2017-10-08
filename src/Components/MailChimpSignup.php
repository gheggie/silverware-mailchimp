<?php

/**
 * This file is part of SilverWare.
 *
 * PHP version >=5.6.0
 *
 * For full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 *
 * @package SilverWare\MailChimp\Components
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-mailchimp
 */

namespace SilverWare\MailChimp\Components;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverWare\Components\BaseComponent;
use SilverWare\Forms\FieldSection;
use SilverWare\MailChimp\Forms\MailChimpListField;

/**
 * An extension of the base component class for a MailChimp signup component.
 *
 * @package SilverWare\MailChimp\Components
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-mailchimp
 */
class MailChimpSignup extends BaseComponent
{
    /**
     * Human-readable singular name.
     *
     * @var string
     * @config
     */
    private static $singular_name = 'MailChimp Signup';
    
    /**
     * Human-readable plural name.
     *
     * @var string
     * @config
     */
    private static $plural_name = 'MailChimp Signups';
    
    /**
     * Description of this object.
     *
     * @var string
     * @config
     */
    private static $description = 'A component to show a MailChimp signup form';
    
    /**
     * Icon file for this object.
     *
     * @var string
     * @config
     */
    private static $icon = 'silverware/mailchimp: admin/client/dist/images/icons/MailChimpSignup.png';
    
    /**
     * Defines an ancestor class to hide from the admin interface.
     *
     * @var string
     * @config
     */
    private static $hide_ancestor = BaseComponent::class;
    
    /**
     * Maps field names to field types for this object.
     *
     * @var array
     * @config
     */
    private static $db = [
        'ListID' => 'Varchar(32)',
        'IntroContent' => 'HTMLText',
        'ShowFirstName' => 'Boolean',
        'ShowLastName' => 'Boolean',
        'RequireFirstName' => 'Boolean',
        'RequireLastName' => 'Boolean',
        'UsePlaceholders' => 'Boolean',
        'ButtonLabel' => 'Varchar(64)',
        'OnErrorMessage' => 'Varchar(255)',
        'OnSubscribeMessage' => 'Varchar(255)',
        'OnAlreadySubscribedMessage' => 'Varchar(255)'
    ];
    
    /**
     * Defines the default values for the fields of this object.
     *
     * @var array
     * @config
     */
    private static $defaults = [
        'ShowFirstName' => 1,
        'RequireFirstName' => 1,
        'UsePlaceholders' => 0
    ];
    
    /**
     * Defines the allowed children for this object.
     *
     * @var array|string
     * @config
     */
    private static $allowed_children = 'none';
    
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
        
        $fields->addFieldsToTab(
            'Root.Main',
            [
                MailChimpListField::create(
                    'ListID',
                    $this->fieldLabel('ListID')
                )->setEmptyString(' ')->setAttribute('data-placeholder', $placeholder),
                HTMLEditorField::create(
                    'IntroContent',
                    $this->fieldLabel('IntroContent')
                )
            ]
        );
        
        // Create Options Fields:
        
        $fields->addFieldsToTab(
            'Root.Options',
            [
                FieldSection::create(
                    'FieldOptions',
                    $this->fieldLabel('Fields'),
                    [
                        FieldGroup::create(
                            $this->fieldLabel('ShowFields'),
                            [
                                CheckboxField::create(
                                    'ShowFirstName',
                                    $this->fieldLabel('FirstName')
                                ),
                                CheckboxField::create(
                                    'ShowLastName',
                                    $this->fieldLabel('LastName')
                                )
                            ]
                        ),
                        FieldGroup::create(
                            $this->fieldLabel('RequireFields'),
                            [
                                CheckboxField::create(
                                    'RequireFirstName',
                                    $this->fieldLabel('FirstName')
                                ),
                                CheckboxField::create(
                                    'RequireLastName',
                                    $this->fieldLabel('LastName')
                                )
                            ]
                        ),
                        TextField::create(
                            'ButtonLabel',
                            $this->fieldLabel('ButtonLabel')
                        ),
                        CheckboxField::create(
                            'UsePlaceholders',
                            $this->fieldLabel('UsePlaceholders')
                        )
                    ]
                ),
                FieldSection::create(
                    'MessageOptions',
                    $this->fieldLabel('Messages'),
                    [
                        TextField::create(
                            'OnSubscribeMessage',
                            $this->fieldLabel('OnSubscribeMessage')
                        )->setRightTitle(
                            _t(
                                __CLASS__ . '.ONSUBSCRIBEMESSAGERIGHTTITLE',
                                'Shown to the user after subscribing.'
                            )
                        ),
                        TextField::create(
                            'OnAlreadySubscribedMessage',
                            $this->fieldLabel('OnAlreadySubscribedMessage')
                        )->setRightTitle(
                            _t(
                                __CLASS__ . '.ONALREADYSUBSCRIBEDMESSAGERIGHTTITLE',
                                'Shown to the user if they have already subscribed.'
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
        $labels['Fields'] = _t(__CLASS__ . '.FIELDS', 'Fields');
        $labels['Messages'] = _t(__CLASS__ . '.MESSAGES', 'Messages');
        $labels['LastName'] = _t(__CLASS__ . '.REQUIRELASTNAME', 'Last name');
        $labels['FirstName'] = _t(__CLASS__ . '.REQUIREFIRSTNAME', 'First name');
        $labels['ShowFields'] = _t(__CLASS__ . '.SHOWFIELDS', 'Show fields');
        $labels['IntroContent'] = _t(__CLASS__ . '.INTROCONTENT', 'Intro content');
        $labels['RequireFields'] = _t(__CLASS__ . '.REQUIREFIELDS', 'Require fields');
        $labels['ButtonLabel'] = _t(__CLASS__ . '.BUTTONLABEL', 'Button label');
        $labels['UsePlaceholders'] = _t(__CLASS__ . '.USEPLACEHOLDERS', 'Use placeholders');
        
        // Define Message Field Labels:
        
        $labels['OnErrorMessage'] = _t(
            __CLASS__ . '.ONERRORMESSAGE',
            'On Error message'
        );
        
        $labels['OnSubscribeMessage'] = _t(
            __CLASS__ . '.ONSUBSCRIBEMESSAGE',
            'On Subscribe message'
        );
        
        $labels['OnAlreadySubscribedMessage'] = _t(
            __CLASS__ . '.ONALREADYSUBSCRIBEDMESSAGE',
            'On Already Subscribed message'
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
        
        $this->ButtonLabel = _t(
            __CLASS__ . '.DEFAULTBUTTONLABEL',
            'Subscribe'
        );
        
        $this->OnErrorMessage = _t(
            __CLASS__ . '.DEFAULTONERRORMESSAGE',
            'Sorry, an error has occurred. Please try again later.'
        );
        
        $this->OnSubscribeMessage = _t(
            __CLASS__ . '.DEFAULTONSUBSCRIBEMESSAGE',
            'Thank you for subscribing to our mailing list.'
        );
        
        $this->OnAlreadySubscribedMessage = _t(
            __CLASS__ . '.DEFAULTONALREADYSUBSCRIBEDMESSAGE',
            'You have already subscribed to our mailing list.'
        );
    }
    
    /**
     * Answers true if the object is disabled within the template.
     *
     * @return boolean
     */
    public function isDisabled()
    {
        if (!$this->ListID) {
            return true;
        }
        
        return parent::isDisabled();
    }
}
