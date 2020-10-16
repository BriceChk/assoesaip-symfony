import "@fullcalendar/bootstrap/main.css";
import "@fullcalendar/list/main.css";

import { Calendar } from "@fullcalendar/core";
import listPlugin from '@fullcalendar/list'
import bootstrapPlugin from '@fullcalendar/bootstrap';

$(document).ready( function () {
    let calendarEl = document.getElementById('calendar');
    let calendar = new Calendar(calendarEl, {
        plugins: [listPlugin, bootstrapPlugin],
        themeSystem: 'bootstrap',
        locale: 'fr',
        defaultView: 'list',
        height: 250,
        header: false,
        allDayText: 'Toute la journée',
        noEventsMessage: 'Aucun événement',
        events: [
            {
                title  : 'event1',
                start  : '2020-06-19'
            },
            {
                title  : 'event2',
                start  : '2020-06-20',
                end    : '2020-06-21'
            },
            {
                title  : 'event3',
                start  : '2020-06-22T12:30:00',
                allDay : false // will make the time show
            },
            {
                title  : 'event4',
                start  : '2020-06-22T12:30:00',
                allDay : false // will make the time show
            },
            {
                title  : 'event5',
                start  : '2020-06-22T12:30:00',
                allDay : false // will make the time show
            }
        ],
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
        eventRender: function (info) {
            let el = $(info.el);
            el.attr('data-toggle', 'tooltip');
            el.attr('data-placement', 'left');
            el.attr('title', info.event.extendedProps.description);
            el.tooltip();
        }
    });

    calendar.render();

    // Adding the header to the calendar card
    $('.fc-view').removeClass('card-primary').prepend('<div class="card-header"><h3 class="card-title">Calendrier</h3></div>').append('<div class="card-footer" style="z-index: 10">\n' +
        '                            <a class="btn btn-primary btn-sm mt-auto" href="'+ eventsListRoute + '" role="button">Liste des événements</a>\n' +
        '                            <a class="btn btn-primary btn-sm mt-auto" href="'+ newEventRoute +'" role="button">Nouvel événement</a>\n' +
        '                        </div>');

    $('#questionButton').tooltip();
    $('#newsButton').tooltip();
});