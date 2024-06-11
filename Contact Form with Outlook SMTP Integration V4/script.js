jQuery(document).ready(function($) {
    // Submit form using AJAX
    $('#outlook-contact-form').submit(function(e) {
        e.preventDefault(); // Prevent default form submission
        
        var formData = $(this).serialize(); // Serialize form data
        
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url, // Use WordPress AJAX URL
            data: formData,
            beforeSend: function() {
                $('.response').html('<p>Sending message...</p>').removeClass('success error').fadeIn();
            },
            success: function(response) {
                $('.response').html(response).addClass('success').fadeIn();
                $('#outlook-contact-form')[0].reset(); // Reset form fields
            },
            error: function(xhr, textStatus, errorThrown) {
                $('.response').html('<p>Error: ' + errorThrown + '</p>').addClass('error').fadeIn();
            }
        });
    });
});
