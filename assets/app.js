/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";


document.addEventListener('DOMContentLoaded', function() {
    // General datepicker initialization
    flatpickr('.js-datepicker:not(.js-dob-datepicker)', {
        dateFormat: 'd/m/Y',
        maxDate: 'today',
        minDate: '01/01/2002',
    });

    // Date of Birth specific initialization
    flatpickr('.js-dob-datepicker', {
        dateFormat: 'd/m/Y',
        maxDate: 'today',
        minDate: '26/10/2002',  // Ensure the minimum date is 26 October 2002
        onChange: function(selectedDates, dateStr, instance) {
            // Convert the selected date to compare properly
            const selectedDate = selectedDates[0];

            // Set minimum date to 26 October 2002
            const minDate = new Date('2002-10-26');

            // If a date before 26 October 2002 is selected, reset to 26 October 2002
            if (selectedDate && selectedDate < minDate) {
                instance.setDate(minDate);
            }
        }
    });
});
