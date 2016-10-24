;(function($) {

    /**
     * Return a date formatted with custom date and time formats
     * @param $input
     */
    function displayFormattedDate($input) {
        $.post(
            ajaxurl,
            {
                'action': 'p5_date',
                'date': $input.val()
            },
            function(response) {
                var $wrapper = $input.parents('li:first'),
                    $clearDateLink = $wrapper.find('.p5-remove-date');
                $wrapper.find('.p5_formatted_date .p5-trigger-datepicker').text(response.date);

                if(parseInt(response.success) !== 1) $clearDateLink.addClass('hide');
                else $clearDateLink.removeClass('hide');
            },
            'json'
        );
    }

    /**
     * Activate the datetime picker on a password
     * @param $input
     */
    var applyDatetimePickerOnInput = function($input) {
        $input.datetimepicker({
            dateFormat: "yy-mm-dd",
            timeFormat: "HH:mm:ss",
            separator: ' ',
            onClose: function(date, datepicker) {
                //console.log(date);
                displayFormattedDate($input);
            }
        });
    };

    $(document).ready(function() {
        // Move the P5 section under 'Password protected' radio button
        var $wrapper = $('#submitdiv #submitpost #minor-publishing #misc-publishing-actions #visibility #post-visibility-select');
        $wrapper.find('label[for="visibility-radio-password"]').after( $('#p5-section'));

        // If no password is define, avoid one click by displaying the first password field
        // This field in hidden by default (for accessibility reasons)
        if( $('ul#p5_postpasswords>li').length < 2 ) {
            $('ul#p5_postpasswords>li').removeClass('hide-if-js');
        }

        // Hide the default password input
        $wrapper.find('#password-span').hide();

        // Toggle display of the p5 UI
        var $visibilityInputs = $('input[name="visibility"]');
        $visibilityInputs.on('change', function() {
            if($(this).val() == 'password') $('#p5-section').show();
            else $('#p5-section').hide();
        });
        if($visibilityInputs.filter(':checked').val() != 'password') $('#p5-section').hide();

        // Open Datepicker
        $(document).on('click', 'a.p5-trigger-datepicker', function(event) {
            event.preventDefault();
            $(this).parents('li.p5_postpassword').find('.p5-expiration-date').datepicker( "show" );
        });

        // Delete a password
        $(document).on('click', 'a.p5_delete_password', function(event) {
            event.preventDefault();
            var $wrapper = $(this).parents('li.p5_postpassword');
            $wrapper.find('input.p5_checkbox_delete_password').attr('checked', 'checked');
            $wrapper.fadeOut();
        });

        // Clear the date
        $(document).on('click', 'a.p5-remove-date', function(event) {
            event.preventDefault();
            var $input = $(this).parents('li.p5_postpassword').find('.p5-expiration-date');
            $input.datepicker( "setDate", null );
            displayFormattedDate($input);
        });

        // Add a password
        $(document).on('click', 'a#p5_add_password', function(event) {
            event.preventDefault();
            $.post(
                ajaxurl,
                {
                    'action': 'p5_get_new_password_ui'
                },
                function(response) {
                    var $li = $('<li class="p5_postpassword"></li>').append(response);
                    $('ul#p5_postpasswords').append($li);
                    // Enable datetime picker on newly added password
                    applyDatetimePickerOnInput($li.find('.p5-expiration-date'));
                }
            );
        });

        // Activate the datetime picker on each password
        $('.p5-expiration-date').each(function() {
            applyDatetimePickerOnInput($(this));
        });
        // Use CSS scope
        $('#ui-datepicker-div').wrap('<div class="jquery-ui-p5"></div>');

    });
})(jQuery);