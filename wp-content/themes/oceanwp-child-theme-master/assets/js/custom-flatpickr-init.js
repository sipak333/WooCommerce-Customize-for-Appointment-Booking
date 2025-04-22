document.addEventListener('DOMContentLoaded', function () {
    // Inject custom CSS styles into the page
    var style = document.createElement('style');
    style.innerHTML = `
        .flatpickr-day{
            margin: 1px;}
        .flatpickr-day.flatpickr-disabled, 
        .flatpickr-day.flatpickr-disabled:hover {
            cursor: not-allowed;
            color: rgba(0, 0, 0, 0.48);
            background: #d9dbde;
        }
    `;
    document.head.appendChild(style);

    // Initialize Flatpickr for the input field
    flatpickr("#product_date", {
        minDate: "today", // Disable past dates
        disable: [
            function(date) {
                // Disable Sundays (day 0 in the Date object is Sunday)
                return date.getDay() === 0; // Disable Sundays
            }
        ],
        dateFormat: "Y-m-d", // Date format (e.g., 2025-03-08)
        onOpen: function(selectedDates, dateStr, instance) {
            // Apply custom background color to the calendar when opened
            var calendar = document.querySelector(".flatpickr-calendar");
            if (calendar) {
                calendar.style.backgroundColor = '#ffffff'; // Set background color of the calendar
            }
        },
        onClose: function(selectedDates, dateStr, instance) {
            // Reset calendar background color when the calendar is closed
            var calendar = document.querySelector(".flatpickr-calendar");
            if (calendar) {
                calendar.style.backgroundColor = ''; // Reset to default background
            }
        }
    });
});
