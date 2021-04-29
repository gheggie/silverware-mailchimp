$(function(){
    
    // Obtain Form and Message:
    
    var form    = $('.mailchimpsignupcomponent form');
    var message = $('.mailchimpsignupcomponent form p.message');
    
    // Create Event Listener:
    
    $(form).submit(function(e) {
        
        // Prevent Default Action:
        
        e.preventDefault();
        
        // Check Validation Result:
        
        if ($(this).parsley().isValid()) {
            
            // Obtain Form URL and Data:
            
            var url  = $(this).attr('action');
            var data = $(this).serialize();
            
            // POST Form via Ajax:
            
            $.ajax({
                url: url,
                data: data,
                type: 'POST',
                dataType: 'json'
            })
            .done(function(response) {
                
                // Clear Form on Success:
                
                if (response.type == 'good') {
                    $(form).find('input[type=text], input[type=email]').val('');
                    $(form).parsley().reset();
                }
                
                // Define Form Status Message:
                
                $(message).html(response.message);
                $(message).attr('class', 'message').addClass(response.type);
                $(message).fadeIn();
                
            });
            
        }
        
    });
    
});
