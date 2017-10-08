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
 * @link https://github.com/praxisnetau/silverware-components
 */

namespace SilverWare\MailChimp\Components;

use SilverStripe\Control\Director;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ValidationResult;
use SilverWare\Components\BaseComponentController;
use SilverWare\Validator\Validator;
use Exception;

/**
 * An extension of the base component controller class for a MailChimp Signup component controller.
 *
 * @package SilverWare\MailChimp\Components
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-components
 */
class MailChimpSignupController extends BaseComponentController
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
     * Answers the signup form object for the template.
     *
     * @return Form
     */
    public function Form()
    {
        // Create Form Fields:
        
        $fields = FieldList::create(
            $email = EmailField::create(
                'Email',
                _t(__CLASS__ . '.EMAILADDRESS', 'Email Address')
            )
        );
        
        if ($this->UsePlaceholders) {
            $email->setAttribute('placeholder', $email->Title())->setTitle('');
        }
        
        // Create First Name Field:
        
        if ($this->ShowFirstName) {
            
            $fields->push(
                $fname = TextField::create(
                    'FirstName',
                    _t(__CLASS__ . '.FIRSTNAME', 'First Name')
                )
            );
            
            if ($this->UsePlaceholders) {
                $fname->setAttribute('placeholder', $fname->Title())->setTitle('');
            }
            
        }
        
        // Create Last Name Field:
        
        if ($this->ShowLastName) {
            
            $fields->push(
                $lname = TextField::create(
                    'LastName',
                    _t(__CLASS__ . '.LASTNAME', 'Last Name')
                )
            );
            
            if ($this->UsePlaceholders) {
                $lname->setAttribute('placeholder', $lname->Title())->setTitle('');
            }
            
        }
        
        // Create Form Actions:
        
        $actions = FieldList::create(
            FormAction::create(
                'doSubscribe',
                $this->ButtonLabel
            )
        );
        
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
        
        // Define Form ID:
        
        $form->setHTMLID(sprintf('%s_Form', $this->getHTMLID()));
        
        // Enable Spam Protection (if installed):
        
        if ($form->hasMethod('enableSpamProtection')) {
            $form->enableSpamProtection();
        }
        
        // Restore Form State (following ID change):
        
        $form->restoreFormState();
        
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
        
        // Attempt to Subscribe User to List via API:
        
        try {
            
            // Obtain API Response:
            
            $response = $this->api->post(
                $this->getSubscribeMethod(),
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
     * @return string
     */
    protected function getSubscribeMethod()
    {
        return sprintf('lists/%s/members', $this->ListID);
    }
}
