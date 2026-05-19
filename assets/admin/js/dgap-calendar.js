document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('dgap-calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        editable: false,
        eventSources: [
            {
                url: dgap_calendar.ajax_url,
                method: 'POST',
                extraParams: {
                    action: 'dgap_get_calendar_bookings',
                    _dgap_nonce : dgap_calendar.nonce
                },
                failure: function() {
                    alert('There was an error fetching bookings!');
                }
            }
        ],
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        }
    });

    calendar.render();
});
