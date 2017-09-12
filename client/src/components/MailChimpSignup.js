/* MailChimp Signup Component
===================================================================================================================== */

import $ from 'jquery';

$(function() {
  
  $('.mailchimpsignup').each(function() {
    
    var $self = $(this);
    var $form = $self.find('form');
    
    // Create Event Listener:
    
    $form.on('submit', function(e) {
      
      // Prevent Default Action:
      
      e.preventDefault();
      
      // Bail Early (if form already submitted):
      
      if ($form.data('submitted') === true) {
        return;
      }
      
      // Check Validation Result:
      
      if ($form.parsley().isValid()) {
        
        // Flag Form as Submitted:
        
        $form.data('submitted', true);
        
        // Obtain URL and Data:
        
        var url  = $form.attr('action');
        var data = $form.serialize();
        
        // Submit Form via Ajax:
        
        $.ajax({
          url: url,
          data: data,
          type: 'POST',
          dataType: 'json'
        }).done(function(response) {
          
          // Handle Response:
          
          $form.handleMessages({
            messages: response,
            onSuccess: function() {
              $form.find('input[type=text], input[type=email]').val('');
              $form.parsley().reset();
            }
          });
          
          // Flag Form as Unsubmitted:
          
          $form.data('submitted', false);
          
        });
        
      }
      
    });
    
  });
  
});
