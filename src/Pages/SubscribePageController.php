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

use SilverStripe\Control\Director;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ValidationResult;
use SilverWare\Validator\Validator;
use PageController;

/**
 * An extension of the page controller class for a MailChimp subscribe page controller.
 *
 * @package SilverWare\MailChimp\Pages
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2018 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-mailchimp
 */
class SubscribePageController extends PageController
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
     * Defines the allowed actions for this controller.
     *
     * @var array
     * @config
     */
    private static $allowed_actions = [
        'Form',
        'doSubscribe'
    ];
    
    /**
     * Answers the form object for the template.
     *
     * @return Form
     */
    public function Form()
    {
        // Create Form Fields:
        
        $fields = FieldList::create([
            EmailField::create(
                'Email',
                _t(__CLASS__ . '.EMAILADDRESS', 'Email Address')
            )
        ]);
        
        // Create First Name Field:
        
        if ($this->ShowFirstName) {
            
            $fields->push(
                $fname = TextField::create(
                    'FirstName',
                    _t(__CLASS__ . '.FIRSTNAME', 'First Name')
                )
            );
            
        }
        
        // Create Last Name Field:
        
        if ($this->ShowLastName) {
            
            $fields->push(
                $lname = TextField::create(
                    'LastName',
                    _t(__CLASS__ . '.LASTNAME', 'Last Name')
                )
            );
            
        }
        
        // Create Form Actions:
        
        $actions = FieldList::create([
            FormAction::create('doSubscribe', 'Subscribe')
        ]);
        
        // Define Required Fields:
        
        $required = ['Email'];
        
        if ($this->ShowFirstName && $this->RequireFirstName) {
            $required[] = 'FirstName';
        }
        
        if ($this->ShowLastName && $this->RequireLastName) {
            $required[] = 'LastName';
        }
        
        // Create Form Validator:
        
        $validator = Validator::create()->addRequiredFields($required);
        
        // Create Form Object:
        
        $form = Form::create($this, 'Form', $fields, $actions, $validator);
        
        // Enable Spam Protection (if available):
        
        if ($form->hasMethod('enableSpamProtection')) {
            $form->enableSpamProtection();
        }
        
        // Extend Form Object:
        
        $this->extend('updateForm', $form);
        
        // Answer Form Object:
        
        return $form;
    }
    
    /**
     * Handles the submission of the subscribe form.
     *
     * @param array $data
     * @param Form $form
     *
     * @return HTTPResponse
     */
    public function doSubscribe($data, $form)
    {
        // Initialise:
        
        $errors = [];
        
        // Create Validation Result:
        
        $result = ValidationResult::create();
        
        // Attempt to Unsubscribe User from List via API:
        
        try {
            
            // Obtain API Response:
            
            $response = $this->api->put(
                $this->getSubscribeMethod($data),
                [
                    'email_address' => $data['Email'],
                    'email_type' => 'html',
                    'status' => 'subscribed',
                    'merge_fields' => [
                        'FNAME' => isset($data['FirstName']) ? $data['FirstName'] : null,
                        'LNAME' => isset($data['LastName'])  ? $data['LastName']  : null
                    ]
                ]
            );
            
            // Did It Work?
            
            if ($this->api->success()) {
                
                // Add Subscribe Message:
                
                $result->addMessage($this->OnSubscribeMessage, ValidationResult::TYPE_GOOD);
                
            } elseif (isset($response['title']) && $response['title'] == 'Member Exists') {
                
                // Member Already Exists:
                
                $result->addMessage($this->OnAlreadySubscribedMessage, ValidationResult::TYPE_WARNING);
                
            } else {
                
                // Something Else Went Wrong! :(
                
                $errors[] = $this->api->error();
                
            }
            
        } catch (Exception $e) {
            
            // Obtain Exception Message:
            
            $errors[] = $e->getMessage();
            
        }
        
        // Detect Errors:
        
        if ($errors) {
            
            // Add Error Message:
            
            $result->addMessage($this->OnErrorMessage, ValidationResult::TYPE_ERROR);
            
            // Add Debug Messages (dev only):
            
            if (Director::isDev()) {
                
                foreach ($errors as $error) {
                    $result->addMessage($error, ValidationResult::TYPE_ERROR);
                }
                
            }
            
        }
        
        // Return JSON Response (Ajax-only):
        
        if ($this->getRequest()->isAjax()) {
            
            $response = $this->getResponse();
            
            $response->addHeader('Content-Type', 'text/json');
            
            $response->setBody(Convert::array2json($result->getMessages()));
            
            return $response;
            
        }
        
        // Define Session Validation Result:
        
        $form->setSessionValidationResult($result);
        
        // Redirect Back to Page (non-Ajax):
        
        return $this->redirectBack();
    }
    
    /**
     * Answers the API method for adding the subscriber.
     *
     * @param array $data
     *
     * @return string
     */
    protected function getSubscribeMethod($data)
    {
        return sprintf(
            'lists/%s/members/%s',
            $this->ListID,
            $this->api->hash($data['Email'])
        );
    }
}
