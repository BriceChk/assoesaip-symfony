import { Calendar } from "@fullcalendar/core";
import listPlugin from '@fullcalendar/list';
import daygridPlugin from '@fullcalendar/daygrid';
import timegridPlugin from '@fullcalendar/timegrid';
import bootstrapPlugin from '@fullcalendar/bootstrap';

import '@fullcalendar/core/main.css';
import '@fullcalendar/list/main.css';
import '@fullcalendar/daygrid/main.css';
import '@fullcalendar/timegrid/main.css';
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
            month:    'Mois',
            week:     'Semaine',
            day:      'jour',
            list:     'Liste'
        },
        height: 1000,
        header: {
            left:   'title',
            center: '',
            right:  'today prev,next listMonth,timeGridWeek,dayGridMonth'
        },
        minTime: '07:00:00',
        maxTime: '23:59:59',
        defaultView: 'listMonth',
        allDayText : 'Toute la journée',
        noEventsMessage: 'Aucun événement',
        events: '/api/project/category/' + categId + "/events/fullcalendar",
        eventRender: function (info) {
            let el = $(info.el);
            el.attr('data-toggle', 'tooltip');
            el.attr('data-placement', 'left');
            el.attr('title', info.event.extendedProps.description);
            el.tooltip();
        }
    });

    $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        calendar.render();
    })
});