jQuery(document).ready(function($) {
    $('.obfuscated-input').each(function() {
        var originalValue = $(this).val();
        var obfuscatedValue = obfuscate(originalValue);
        $(this).val(obfuscatedValue);
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
    $('.obfuscated-input').on('input', function() {
        var obfuscatedValue = '*'.repeat($(this).val().length);
        $(this).val(obfuscatedValue);
    });
    $('.obfuscated-input').on('copy', function(event) {
        event.preventDefault();
    });
});

function obfuscate(value) {
    return '*'.repeat(value.length);
}