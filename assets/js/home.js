require('easy-autocomplete/dist/jquery.easy-autocomplete');
import { Calendar } from "@fullcalendar/core";
import listPlugin from '@fullcalendar/list'
import bootstrapPlugin from '@fullcalendar/bootstrap';

import "easy-autocomplete/dist/easy-autocomplete.css";
import "@fullcalendar/core/main.css";
import "@fullcalendar/bootstrap/main.css";
import "@fullcalendar/list/main.css";


$(document).ready( function () {
    let calendarEl = document.getElementById('calendar');
    let calendar = new Calendar(calendarEl, {
        plugins: [listPlugin, bootstrapPlugin],
        themeSystem: 'bootstrap',
        locale: 'fr',
        defaultView: 'list',
        height: 340,
        header: false,
        allDayText: 'Toute la journée',
        noEventsMessage: 'Aucun événement',
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
        eventRender: function (info) {
            let el = $(info.el);
            el.attr('data-toggle', 'tooltip');
            el.attr('data-placement', 'left');
            el.attr('title', info.event.extendedProps.description);
            el.tooltip();
        }
    });

    calendar.render();

    let options = {
        url: function (phrase) {
            return "../api/api-v1.php?action=search_project&query=" + phrase;
        },
        getValue: "text",
        theme: 'bootstrap',
        requestDelay: 500,
        list: {
            onChooseEvent: function () {
                window.location.href = $("#inputSearch").getSelectedItemData().value;
            }
        }
    };

    $("#inputSearch").easyAutocomplete(options);
});