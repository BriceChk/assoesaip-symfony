import { Calendar } from "@fullcalendar/core";
import listPlugin from '@fullcalendar/list'
import bootstrapPlugin from '@fullcalendar/bootstrap';

import '@fullcalendar/bootstrap/main.css';

$(document).ready( function () {
    let calendarEl = document.getElementById('calendar');
    let calendar = new Calendar(calendarEl, {
        plugins: [listPlugin, bootstrapPlugin],
        themeSystem: 'bootstrap',
        locale: 'fr',
        initialView: 'list',
        height: 250,
        headerToolbar: false,
        allDayContent: 'Toute la journée',
        noEventsContent: 'Aucun événement',
        events: [],
        //events: 'https://asso.esaip.org/api/api-v1.php?action=get_events',
        visibleRange: function (currentDate) {
            // Generate a new date for manipulating in the next step
            let startDate = new Date(currentDate.valueOf());
            let endDate = new Date(currentDate.valueOf());

            // Adjust the start & end dates, respectively
            startDate.setDate(startDate.getDate());
            endDate.setDate(endDate.getDate() + 7);

            return {start: startDate, end: endDate};
        },
        eventDidMount: function (info) {
            let el = $(info.el);
            el.attr('data-toggle', 'tooltip');
            el.attr('data-placement', 'left');
            el.attr('title', info.event.extendedProps.description);
            el.tooltip();
        }
    });

    calendar.render();

    // Adding the header to the calendar card
    //$('.fc-view').removeClass('card-primary').prepend('<div class="card-header">Prochains événements</div>');
});