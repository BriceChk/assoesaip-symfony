import { Calendar } from "@fullcalendar/core";
import listPlugin from '@fullcalendar/list'
import bootstrapPlugin from '@fullcalendar/bootstrap';

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
    $('.fc-view').removeClass('card-primary').prepend('<div class="card-header"><h3 class="card-title">Calendrier</h3></div>').append('<div class="card-footer" style="z-index: 10">\n' +
        '                            <a class="btn btn-primary btn-sm mt-auto" href="'+ eventsListRoute + '" role="button">Liste des événements</a>\n' +
        '                            <a class="btn btn-primary btn-sm mt-auto" href="'+ newEventRoute +'" role="button">Nouvel événement</a>\n' +
        '                        </div>');

    $('#questionButton').tooltip();
    $('#newsButton').tooltip();
});

window.deleteNews = function (id) {
    let modal = $('#deleteNewsModal');
    modal.find('.delete-news-btn').click(function () {
        disableModalButtons();
        $.ajax({
            url: '/api/news/' + id,
            type: 'DELETE',
            contentType: 'application/json',
            success: function () {
                $('#news-' + id).remove();
                toastr.success("L'actu a été supprimée.");
                modal.modal('hide');
                enableModalButtons();
            },
            error: function (data) {
                error(data);
                modal.modal('hide');
                enableModalButtons();
            }
        });
    });
    modal.modal('show')
}

window.createNews = function () {
    let json = {
        content: $('#news-message').val(),
        link: $('#news-link').val(),
        notify: $('#news-notif').is(':checked')
    };

    $.ajax({
        url: '/api/project/' + projectId + '/news',
        type: 'PUT',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(json),
        success: function (data) {
            let dateA = data.date_published.split('T')[0].split('-');
            let timeA = data.date_published.split('T')[1].split(':');
            let date = dateA[2] + '/' + dateA[1] + '/' + dateA[0] + ' · ' + timeA[0] + 'h' + timeA[1];

            let newTab = $('#news-template').clone();
            newTab.attr('id', 'news-' + data.id)
                .html(newTab.html()
                    .replace('%id%', data.id)
                    .replace('%news-date%', date)
                    .replace('%news-content%', data.content)
                ).show();
            if (data.link !== '') {
                newTab.find('a').first().attr('href', data.link);
            } else {
                newTab.find('.news-link-container').remove();
            }
            $('#news-list').prepend(newTab);

            toastr.success("L'actu a été publiée !");
            $('#newNewsModal').modal('hide');
        },
        error: function (data) { error(data) }
    });


}