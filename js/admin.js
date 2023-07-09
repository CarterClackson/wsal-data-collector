jQuery(document).ready(function($) {
    //Obfuscation
    /*$('.obfuscated-input').each(function() {
        var originalValue = $(this).val();
        var obfuscatedValue = obfuscate(originalValue);
        $(this).val(obfuscatedValue);
    });*/
    $('.obfuscated-input').on('copy', function(event) {
        event.preventDefault();
    });

    // Function to show/hide fields based on the selected option
    function toggleFields() {
        var selectedOption = $('select[name="wp_event_data_collector_identity_dropdown"]').val();
        if (selectedOption === 'akv') {
            $('.wp-event-collector-identity-fields').prop('disabled', false);
            $('[name="wp_event_data_collector_primary_key"]').prop('disabled', true);
        } else {
            $('.wp-event-collector-identity-fields').prop('disabled', true);
            $('[name="wp_event_data_collector_primary_key"]').prop('disabled', false);
        }
    }

    // Call the toggleFields function on page load
    toggleFields();

    // Call the toggleFields function whenever the select option changes
    $('select[name="wp_event_data_collector_identity_dropdown"]').change(function() {
        toggleFields();
    });

    $('#notification-test').click(function() {
        // Call an AJAX function to execute the PHP function on button click
        $.ajax({
          url: ajaxurl, // WordPress AJAX handler
          type: 'POST',
          data: {
            action: 'test_notification', // The WordPress AJAX action
          },
          success: function(response) {
            // Handle the response from the server
            if (response.status === 'success') {
                // Data transfer successful
                console.log(response.message);
                $('#notification-response').html(response.message);
              } else if (response.status === 'error') {
                // Failed to send data
                console.error(response.message);
                $('#notification-response').html(response.message);
              }
          },
          error: function(xhr, status, error) {
            // Handle AJAX errors
            $('#notification-response').html(error);
            console.error(error);
          }
        });
      });

    $('#general-test').click(function() {
        // Call an AJAX function to execute the PHP function on button click
        $.ajax({
          url: ajaxurl, // WordPress AJAX handler
          type: 'POST',
          data: {
            action: 'test_push', // The WordPress AJAX action
          },
          success: function(response) {
            // Handle the response from the server
            if (response.status === 'success') {
                // Data transfer successful
                console.log(response.message);
                $('#general-response').html(response.message);
              } else if (response.status === 'error') {
                // Failed to send data
                console.error(response.message);
                $('#general-response').html(response.message);
              }
          },
          error: function(xhr, status, error) {
            // Handle AJAX errors
            $('#general-response').html(error);
            console.error(error);
          }
        });
      });

      $('#akv-test').click(function() {
        // Call an AJAX function to execute the PHP function on button click
        $.ajax({
          url: ajaxurl, // WordPress AJAX handler
          type: 'POST',
          data: {
            action: 'test_vault_connection', // The WordPress AJAX action
          },
          success: function(response) {
            // Handle the response from the server
            if (response.status === 'success') {
                // Data transfer successful
                console.log(response.message);
                $('#akv-response').html(response.message);
              } else if (response.status === 'error') {
                // Failed to send data
                console.error(response.message);
                $('#akv-response').html(response.message);
              }
          },
          error: function(xhr, status, error) {
            // Handle AJAX errors
            $('#akv-response').html(error);
            console.error(error);
          }
        });
      });

      $('#akv-push').click(function() {
        // Call an AJAX function to execute the PHP function on button click
        $.ajax({
          url: ajaxurl, // WordPress AJAX handler
          type: 'POST',
          data: {
            action: 'test_vault_connection_push', // The WordPress AJAX action
          },
          success: function(response) {
            // Handle the response from the server
            if (response.status === 'success') {
                // Data transfer successful
                console.log(response.message);
                $('#akv-push-response').html(response.message);
              } else if (response.status === 'error') {
                // Failed to send data
                console.error(response.message);
                $('#akv-push-response').html(response.message);
              }
          },
          error: function(xhr, status, error) {
            // Handle AJAX errors
            $('#akv-push-response').html(error);
            console.error(error);
          }
        });
      });

});

function obfuscate(value) {
    return '*'.repeat(value.length);
}