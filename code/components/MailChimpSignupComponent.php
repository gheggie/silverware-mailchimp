<?php

/**
 * An extension of the base component class for a MailChimp signup component.
 */
class MailChimpSignupComponent extends BaseComponent
{
    private static $singular_name = "MailChimp Signup";
    private static $plural_name   = "MailChimp Signups";
    
    private static $description = "A component to show a MailChimp signup form";
    
    private static $icon = "silverware-mailchimp/images/icons/MailChimpSignupComponent.png";
    
    private static $hide_ancestor = "BaseComponent";
    
    private static $allowed_children = "none";
    
    private static $db = array(
        'ListID' => 'Varchar(16)',
        'OnErrorMessage' => 'Varchar(255)',
        'OnSubscribeMessage' => 'Varchar(255)',
        'OnAlreadySubscribedMessage' => 'Varchar(255)',
        'LastNameRequired' => 'Boolean'
    );
    
    private static $required_js = array(
        'silverware-mailchimp/javascript/mailchimp-signup-component.js'
    );
    
    /**
     * Answers a collection of field objects for the CMS interface.
     *
     * @return FieldList
     */
    public function getCMSFields()
    {
        // Obtain Field Objects (from parent):
        
        $fields = parent::getCMSFields();
        
        // Add Status Message (if defined):
        
        $fields->addStatusMessage(MailChimpConfig::get_status_message());
        
        // Create Main Fields:
        
        if (MailChimpAPI::get_api_key()) {
            
            try {
                
                // Create Subscriber List Dropdown Field:
                
                $fields->addFieldToTab(
                    'Root.Main',
                    DropdownField::create(
                        'ListID',
                        _t('MailChimpSignupComponent.MAILCHIMPSUBSCRIBERLIST', 'MailChimp subscriber list'),
                        $this->getSubscriberListOptions()
                    )->setRightTitle(
                        _t(
                            'MailChimpSignupComponent.MAILCHIMPSUBSCRIBERLISTRIGHTTITLE',
                            'Select the MailChimp subscriber list that users will subscribe to.'
                        )
                    )->setEmptyString(' ')
                );
                
            } catch (\Exception $e) {
                
                $fields->addStatusMessage($e->getMessage(), 'bad');
                
            }
            
        }
        
        // Create Intro Field:
        
        $fields->addFieldToTab(
            'Root.Main',
            HtmlEditorField::create(
                'Content',
                _t('MailChimpSignupComponent.INTRO', 'Intro')
            )->setRows(15)
        );
        
        // Create Options Fields:
        
        $fields->addFieldToTab(
            'Root.Options',
            ToggleCompositeField::create(
                'MailChimpSignupOptions',
                $this->i18n_singular_name(),
                array(
                    TextField::create(
                        'OnSubscribeMessage',
                        _t(
                            'MailChimpSignupComponent.ONSUBSCRIBEMESSAGE',
                            'On Subscribe message'
                        )
                    )->setRightTitle(
                        _t(
                            'MailChimpSignupComponent.ONSUBSCRIBEMESSAGERIGHTTITLE',
                            'Shown to the user after subscribing.'
                        )
                    ),
                    TextField::create(
                        'OnAlreadySubscribedMessage',
                        _t(
                            'MailChimpSignupComponent.ONALREADYSUBSCRIBEDMESSAGE',
                            'On Already Subscribed message'
                        )
                    )->setRightTitle(
                        _t(
                            'MailChimpSignupComponent.ONALREADYSUBSCRIBEDMESSAGERIGHTTITLE',
                            'Shown to the user if they have already subscribed.'
                        )
                    ),
                    TextField::create(
                        'OnErrorMessage',
                        _t(
                            'MailChimpSignupComponent.ONERRORMESSAGE',
                            'On Error message'
                        )
                    )->setRightTitle(
                        _t(
                            'MailChimpSignupComponent.ONERRORMESSAGERIGHTTITLE',
                            'Shown to the user when an error occurs.'
                        )
                    ),
                    CheckboxField::create(
                        'LastNameRequired',
                        _t('MailChimpSignupComponent.LASTNAMEREQUIRED', 'Last name required')
                    )
                )
            )
        );
        
        // Answer Field Objects:
        
        return $fields;
    }
    
    /**
     * Answers a validator for the CMS interface.
     *
     * @return RequiredFields
     */
    public function getCMSValidator()
    {
        return RequiredFields::create(
            array(
                'ListID'
            )
        );
    }
    
    /**
     * Populates the default values for the attributes of the receiver.
     */
    public function populateDefaults()
    {
        // Populate Defaults (from parent):
        
        parent::populateDefaults();
        
        // Populate Defaults:
        
        $this->OnErrorMessage = _t(
            'MailChimpSignupComponent.DEFAULTONERRORMESSAGE',
            'Sorry, an error has occurred. Please try again later.'
        );
        
        $this->OnSubscribeMessage = _t(
            'MailChimpSignupComponent.DEFAULTONSUBSCRIBEMESSAGE',
            'Thank you for subscribing to our mailing list.'
        );
        
        $this->OnAlreadySubscribedMessage = _t(
            'MailChimpSignupComponent.DEFAULTONALREADYSUBSCRIBEDMESSAGE',
            'You have already subscribed to our mailing list.'
        );
    }
    
    /**
     * Answers the content for the HTML template.
     *
     * @param string $layout
     * @param string $title
     * @return string
     */
    public function Content($layout = null, $title = null)
    {
        return $this->dbObject('Content');
    }
    
    /**
     * Answers an array of subscriber list options for a dropdown field.
     *
     * @return array
     */
    protected function getSubscriberListOptions()
    {
        // Create Options Array:
        
        $options = array();
        
        // Obtain API Instance:
        
        $api = MailChimpAPI::inst();
        
        // Obtain Subscriber List Data:
        
        $response = $api->get('lists');
        
        // Verify API Success:
        
        if ($api->success()) {
            
            // Define Options Array:
            
            if (isset($response['lists'])) {
                
                foreach ($response['lists'] as $list) {
                    $options[$list['id']] = $list['name'];
                }
                
            }
            
        } else {
            
            // Throw Exception on Error:
            
            throw new \Exception($api->getLastError());
            
        }
        
        // Answer Options Array:
        
        return $options;
    }
    
    /**
     * Answers true if a MailChimp API key has not been defined or a list has not been selected.
     *
     * @return boolean
     */
    public function Disabled()
    {
        if (!MailChimpAPI::get_api_key() || !$this->ListID) {
            return true;
        }
        
        return parent::Disabled();
    }
}

/**
 * An extension of the base component controller class for a MailChimp signup component.
 */
class MailChimpSignupComponent_Controller extends BaseComponent_Controller
{
    /**
     * Defines the allowed actions for this controller.
     */
    private static $allowed_actions = array(
        'Form',
        'doSubscribe'
    );
    
    /**
     * Performs initialisation before any action is called on the receiver.
     */
    public function init()
    {
        // Initialise Parent:
        
        parent::init();
    }
    
    /**
     * Answers the signup form object for the template.
     *
     * @return Form
     */
    public function Form()
    {
        // Create Form Fields:
        
        $fields = FieldList::create(
            EmailField::create(
                'EMAIL',
                _t('MailChimpSignupComponent.EMAILADDRESS', 'Email Address')
            ),
            TextField::create(
                'FNAME',
                _t('MailChimpSignupComponent.FIRSTNAME', 'First Name')
            ),
            TextField::create(
                'LNAME',
                _t('MailChimpSignupComponent.LASTNAME', 'Last Name')
            )
        );
        
        // Create Form Actions:
        
        $actions = FieldList::create(
            FormAction::create('doSubscribe', 'Subscribe')
        );
        
        // Define Required Fields:
        
        $required = array(
            'EMAIL',
            'FNAME'
        );
        
        if ($this->LastNameRequired) {
            $required[] = "LNAME";
        }
        
        // Create Form Validator:
        
        if (class_exists('ZenValidator')) {
            $validator = ZenValidator::create()->addRequiredFields($required);
        } else {
            $validator = RequiredFields::create($required);
        }
        
        // Create Form Object:
        
        $form = Form::create($this, 'Form', $fields, $actions, $validator);
        
        // Define Form ID:
        
        $form->setHTMLID($this->getHTMLID() . '_Form');
        
        // Enable Spam Protection (if installed):
        
        if ($form->hasExtension('FormSpamProtectionExtension')) {
            $form->enableSpamProtection();
        }
        
        // Answer Form Object:
        
        return $form;
    }
    
    /**
     * Handles the submission of the signup form.
     *
     * @param array $data
     * @param Form $form
     * @return SS_HTTPResponse
     */
    public function doSubscribe($data, $form)
    {
        // Create Result Array:
        
        $result = array();
        
        // Obtain API Instance:
        
        $api = MailChimpAPI::inst();
        
        // Attempt to Subscribe User to List:
        
        $response = $api->post(
            "lists/{$this->ListID}/members",
            array(
                'email_address' => $data['EMAIL'],
                'email_type' => 'html',
                'status' => 'subscribed',
                'merge_fields' => array(
                    'FNAME' => $data['FNAME'],
                    'LNAME' => $data['LNAME']
                )
            )
        );
        
        // Determine API Result Message:
        
        if ($api->success()) {
            
            // Report Success:
            
            $result = array(
                'message' => $this->OnSubscribeMessage,
                'type' => 'good'
            );
            
        } else {
            
            if (isset($response['title']) && $response['title'] == 'Member Exists') {
                
                // Report Already Subscribed:
                
                $result = array(
                    'message' => $this->OnAlreadySubscribedMessage,
                    'type' => 'warning'
                );
                
            } else {
                
                // Report Unknown Error:
                
                $result = array(
                    'message' => $this->OnErrorMessage,
                    'type' => 'bad'
                );
                
            }
            
        }
        
        // Return JSON Response (AJAX-only):
        
        if ($this->getRequest()->isAjax()) {
            
            $response = $this->getResponse();
            
            $response->addHeader('Content-Type', 'text/json');
            
            $response->setBody(Convert::raw2json($result));
            
            return $response;
            
        }
        
        // Define Form Session Message:
        
        if (isset($result['message']) && isset($result['type'])) {
            
            $form->sessionMessage($result['message'], $result['type']);
            
        }
        
        // Redirect Back to Page (non-AJAX):
        
        $this->redirectBack();
    }
}
