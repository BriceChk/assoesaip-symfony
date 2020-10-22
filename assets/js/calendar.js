import { Calendar } from "@fullcalendar/core";
import listPlugin from '@fullcalendar/list'
import daygridPlugin from '@fullcalendar/daygrid'
import timegridPlugin from '@fullcalendar/timegrid'
import bootstrapPlugin from '@fullcalendar/bootstrap';

import '@fullcalendar/bootstrap/main.css';

$(document).ready( function () {
    let calendarEl = document.getElementById('calendar');
    let calendar = new Calendar(calendarEl, {
        plugins: [listPlugin, bootstrapPlugin, daygridPlugin, timegridPlugin],
        themeSystem: 'bootstrap',
        locale: 'fr',
        firstDay: 1,
        buttonText: {
            today:    'Aujourd\'hui',
            month:    'Grille',
            week:     'Semaine',
            day:      'jour',
            list:     'Liste'
        },
        headerToolbar: {
            left:   'title',
            center: '',
            right:  'today prev,next timeGridWeek,dayGridMonth,listMonth'
        },
        initialView: 'timeGridWeek',
        allDayContent : 'Toute la journée',
        noEventsContent: 'Aucun événement',
        events: [],
        //events: 'https://asso.esaip.org/api/api-v1.php?action=get_events',
        eventDidMount: function (info) {
            let el = $(info.el);
            el.attr('data-toggle', 'tooltip');
            el.attr('data-placement', 'left');
            el.attr('title', info.event.extendedProps.description);
            el.tooltip();
        }
    });

    calendar.render();
});