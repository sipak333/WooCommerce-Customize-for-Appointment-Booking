function highlightBookedSlots() {
  if (typeof booked_time_slots !== 'undefined') {
    const selectedDate = document.getElementById('product_date') ? document.getElementById('product_date').value : '';

    // If a date is selected and booked slots exist for that date
    if (selectedDate && booked_time_slots[selectedDate]) {
      console.log('Booked slots for ' + selectedDate + ':', booked_time_slots[selectedDate]); // Debug log

      booked_time_slots[selectedDate].forEach(function (timeSlot) {
        // Find the time slot element that corresponds to the booked time
        const timeSlotElement = document.querySelector(`#time_slots_container .time-slot-btn[data-time="${timeSlot}"]`);

        // If the time slot element exists, apply 'booked' styling
        if (timeSlotElement) {
          timeSlotElement.classList.add('booked'); // Add 'booked' class
          timeSlotElement.style.pointerEvents = 'none'; // Disable interaction
          timeSlotElement.style.backgroundColor = 'orange'; // Apply orange background for booked slots
          
        }
      });
    }
  }
}

function showTimeSlots() {
  const selectedDate = document.getElementById("product_date").value;
  const timeSlotsContainer = document.getElementById("time_slots_container");

  console.log("Selected date: " + selectedDate); // Debug log

  if (!selectedDate) {
    timeSlotsContainer.innerHTML = "";
    return;
  }

  const date = new Date(selectedDate);
  if (isNaN(date.getTime())) {
    timeSlotsContainer.innerHTML = "Please select a valid date.";
    return;
  }

  // Get current date and time in Kolkata
  const kolkataTime = new Date().toLocaleString("en-US", { timeZone: "Asia/Kolkata" });
  const kolkataDate = new Date(kolkataTime);

  console.log("Current Kolkata time: " + kolkataDate.toString()); // Debug log

  // For comparison of dates, set both to midnight
  const selectedDateMidnight = new Date(date);
  selectedDateMidnight.setHours(0, 0, 0, 0);

  const kolkataDateMidnight = new Date(kolkataDate);
  kolkataDateMidnight.setHours(0, 0, 0, 0);

  console.log("Selected date midnight: " + selectedDateMidnight.toString()); // Debug log
  console.log("Kolkata date midnight: " + kolkataDateMidnight.toString()); // Debug log

  // Check if selected date is in the past
  if (selectedDateMidnight < kolkataDateMidnight) {
    timeSlotsContainer.innerHTML = "Please select a valid future date.";
    return;
  }

  const dayOfWeek = date.getDay();
  if (dayOfWeek === 0) {
    timeSlotsContainer.innerHTML = '<p style="color:red;">Time slots are not available on Sundays.</p>';
    return;
  }

  // Get day name in lowercase (monday, tuesday, etc.)
  const dayOfWeekText = date.toLocaleString("en-us", { weekday: "long" }).toLowerCase();
  console.log("Day of week: " + dayOfWeekText); // Debug log

  // Get time slots for the selected day from the global variable
  const availableTimeSlots = window.available_time_slots || {};

  if (!availableTimeSlots[dayOfWeekText] || availableTimeSlots[dayOfWeekText].length === 0) {
    timeSlotsContainer.innerHTML = "No time slots available for this day.";
    return;
  }

  const timeSlots = availableTimeSlots[dayOfWeekText];
  console.log("Available time slots for " + dayOfWeekText + ": ", timeSlots); // Debug log

  let timeSlotsHtml = '<p style="font-size:15px; margin-bottom:0px;">Available time slots:</p>';

  // Check if selected date is today
  const isToday = selectedDateMidnight.getTime() === kolkataDateMidnight.getTime();
  console.log("Is today: " + isToday); // Debug log

  // Get current time in Kolkata in minutes for comparison
  const currentHour = kolkataDate.getHours();
  const currentMinute = kolkataDate.getMinutes();
  const currentTimeInMinutes = currentHour * 60 + currentMinute;
  console.log("Current time in minutes: " + currentTimeInMinutes); // Debug log

  // Flag to check if any time slots are available
  let hasAvailableSlots = false;

  // Loop through the available time slots
  timeSlots.forEach((slot, index) => {
    const timeSlot = slot.trim();
    const timeSlotInMinutes = timeStringToMinutes(timeSlot);
    console.log("Time slot: " + timeSlot + ", in minutes: " + timeSlotInMinutes); // Debug log

    // Disable past slots only for today
    let disabled = "";
    if (isToday && timeSlotInMinutes <= currentTimeInMinutes) {
      disabled = "disabled";
    }

    if (!disabled) {
      hasAvailableSlots = true;
    }

    const buttonStyle = disabled ? "background-color:grey;" : "";

    timeSlotsHtml += `
      <input type="radio" id="time_slot_${index}" name="time_slot" value="${timeSlot}" class="time-slot-radio" ${disabled} aria-label="${timeSlot}">
      <label for="time_slot_${index}" class="time-slot-btn" style="${buttonStyle}" data-time="${timeSlot}">${timeSlot}</label>
    `;
  });

  // If today and no available slots, show message
  if (isToday && !hasAvailableSlots) {
    timeSlotsHtml = '<p style="color:red;">No more time slots available for today. Please select another date.</p>';
  }

  timeSlotsContainer.innerHTML = timeSlotsHtml;

  // Highlight booked slots
  highlightBookedSlots();
}

// Function to convert time in "hh:mm AM/PM" format to minutes
function timeStringToMinutes(timeString) {
  const [time, modifier] = timeString.split(" ");
  let [hours, minutes] = time.split(":").map(Number);

  if (modifier === "PM" && hours !== 12) {
    hours += 12; // Convert PM hours to 24-hour format
  }
  if (modifier === "AM" && hours === 12) {
    hours = 0; // Convert 12 AM to 0 hours
  }

  return hours * 60 + minutes; // Return total minutes
}

// Attach event listener to date input
const dateInput = document.getElementById("product_date");
if (dateInput) {
  dateInput.addEventListener("change", showTimeSlots);

  // Set default date to today and trigger the change event
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");
  dateInput.value = `${year}-${month}-${day}`;

  // Trigger the change event to show today's available time slots
  const event = new Event("change");
  dateInput.dispatchEvent(event);
}




