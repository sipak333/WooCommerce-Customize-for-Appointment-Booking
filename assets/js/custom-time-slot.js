function showTimeSlots() {
    var selectedDate = document.getElementById("product_date").value;
    var timeSlotsContainer = document.getElementById("time_slots_container");

    if (!selectedDate) {
        timeSlotsContainer.innerHTML = ''; // Clear the container if no date is selected
        return;
    }

    var date = new Date(selectedDate);
    var dayOfWeek = date.getDay(); // 0 is Sunday, 1 is Monday, etc.

    // Disable Sundays
    if (dayOfWeek === 0) {
        timeSlotsContainer.innerHTML = '<p style="color:red;">Time slots are not available on Sundays.</p>';
        return;
    }

    // Check if the selected date is before today
    var today = new Date();
    if (date < today) {
        timeSlotsContainer.innerHTML = 'Please select a valid future date.';
        return;
    }

    // Get the day of the week in text (e.g., "monday", "tuesday", etc.)
    var dayOfWeekText = date.toLocaleString('en-us', { weekday: 'long' }).toLowerCase();

    // Check if time slots are available for the selected day
    if (available_time_slots[dayOfWeekText]) {
        var timeSlots = available_time_slots[dayOfWeekText];
        var timeSlotsHtml = '<p style="font-size:15px;">Available time slot:</p>';

        // Create radio buttons styled as buttons
        timeSlots.forEach(function(slot, index) {
            timeSlotsHtml += `
                <input type="radio" id="time_slot_${index}" name="time_slot" value="${slot.trim()}" class="time-slot-radio">
                <label for="time_slot_${index}" class="time-slot-btn">${slot.trim()}</label>
            `;
        });

        timeSlotsContainer.innerHTML = timeSlotsHtml;
    } else {
        timeSlotsContainer.innerHTML = 'No time slots available for this day.';
    }
}



let newBtns = document.querySelectorAll(".wc-block-components-button");

newBtns.forEach(button => {
    button.textContent = "New Button Text"; 
});
     