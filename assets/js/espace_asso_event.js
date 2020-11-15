import 'tempusdominus-bootstrap/build/css/tempusdominus-bootstrap.css'
let rrule = require('rrule');

import 'summernote/dist/summernote-bs4'
import 'summernote/dist/summernote-bs4.css'
import 'summernote/dist/lang/summernote-fr-FR'


$(document).ready(function () {
    moment.locale('fr');

    // Enable datepickers
    let startDate = $('#inputStartDate');
    let endDate = $('#inputEndDate');
    startDate.datetimepicker({
        locale: 'fr',
        sideBySide: true,

    });
    endDate.datetimepicker({
        useCurrent: false,
        locale: 'fr',
        sideBySide: true
    });
    startDate.on("change.datetimepicker", function (e) {
        endDate.datetimepicker('minDate', e.date);
    });
    endDate.on("change.datetimepicker", function (e) {
        startDate.datetimepicker('maxDate', e.date);
    });

    // If an event is being modified
    if (startDate.is('[data-date]')) {
        startDate.datetimepicker('date', moment(startDate.attr('data-date')));
    }
    if (endDate.is('[data-date]')) {
        endDate.datetimepicker('date', moment(endDate.attr('data-date')));
    }


    updateDisplayOptions();


    $('#summernote').summernote({
        placeholder: "Rédigez ici le contenu de l'article",
        tabsize: 2,
        height: 500,
        lang: 'fr-FR',
        toolbar: [
            // [groupName, [list of button]]
            ['fontsize', ['style', 'fontsize', 'hr']],
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['misc', ['fullscreen', 'codeview', 'help']],
            ['edit', ['undo', 'redo']]
        ],
        fontNames: ['Segoe UI'],
        callbacks: {
            onImageUpload: function(files) {
                //uploadSummerNoteImage(files[0]);
            }
        }
    });
});

window.save = function (published) {
    let allDay = $('#allDay').is(':checked');

    let json = {
        title: $('.titre-page').text(),
        abstract: $('.lead').text(),
        html: $('#summernote').summernote('code'),
        category: $('#categorie').val(),
        private: $('#customRadio2').is(':checked'),
        published: published,
        all_day: allDay,
        interval_count: parseInt($('#inputInterval').val()),
        interval_type: $('#inputIntervalType').val()
    }

    let eventRepeated = $('#inputRepeat').val() !== '0';

    // Date de début et durée
    let dateDebutPicker = $('#inputStartDate').datetimepicker('viewDate');
    let dateDebut = allDay ? dateDebutPicker.format('YYYY-MM-DD 00:00:00') : dateDebutPicker.format('YYYY-MM-DD HH:mm:00');
    json['duration'] = 0;
    if (allDay) {
        // Conversion des jours en minutes
        if (eventRepeated) {
            json['duration'] = $('#inputDaysDuration').val() * 1440;
        }
    } else {
        // Conversion du temps hh:mm en minutes
        if (eventRepeated) {
            let duree = $('#inputHoursDuration').val().split(':');
            json['duration'] = parseInt(duree[0]) * 60 + parseInt(duree[1]);
        }
    }
    json['date_start'] = dateDebut;

    if (eventRepeated) {
        // Si répété plusieurs fois (value == 1)
        json['occurrences_count'] = $('#inputOccurrences').val();

        // Liste des occurrences
        let listeOccurrences = [];
        $.each($('.occurrence'), function () {
            let date = $(this).attr('id');
            listeOccurrences.push(date);
        });
        json['occurrences'] = listeOccurrences;
    } else {
        // Si répété 1 fois (value == 0)
        json['occurrences_count'] = 1;
        let dateFin = $('#inputEndDate').datetimepicker('viewDate');
        json['date_end'] = allDay ? dateFin.format('YYYY-MM-DD 00:00:00') : dateFin.format('YYYY-MM-DD HH:mm:00');
        json['occurrences'] = [json['date_start']];
    }

    // Jours de la semaine pour un event weekly
    let days = [];
    $.each($('#daysOfWeek input:checked'), function () {
        days.push($(this).val());
    });
    json['days_of_week'] = days;


    let method = 'POST';
    let url = '/api/event/' + eventId;
    if (eventId === -1) {
        method = 'PUT';
        url = '/api/project/' + id + '/events';
    }

    $.ajax({
        url: url,
        type: method,
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(json),
        success: function (data) {
            toastr.success("Modifications enregistrées !");
            eventId = data.id;
            if (data.published) {
                let url = '/evenement/' + data.url;
                $('#link-a').text('https://asso.esaip.org' + url).attr('href', url);
                $('#published-div').show();
                $('#draft-div').hide();
                $('#save-btn').attr('onclick', 'save(true)');
            } else {
                $('#published-div').hide();
                $('#draft-div').show();
                $('#save-btn').attr('onclick', 'save(false)');
                $('#pub-btn').show();
            }
        },
        error: function (data) { error(data) }
    });
}

// Afficher le choix des jours de la semaine
$('#inputIntervalType').change(function () {
    if ($("#inputIntervalType option:selected").val() === 'weekly') {
        $('#daysOfWeek').show();
    } else {
        $("#daysOfWeek input[type=checkbox]").prop('checked', false).change();
        $('#daysOfWeek').hide();
    }
});

// Activer le choix d'heure
$('#allDay').change(function() {
    updateDisplayOptions();
});

// Afficher le choix de répétition
$("#inputRepeat").change(function() {
    updateDisplayOptions();
});

function updateDisplayOptions() {
    let allDay = $('#allDay').is(':checked');
    if (allDay) {
        $('#inputStartDate').datetimepicker('format', 'L');
        $('#inputEndDate').datetimepicker('format', 'L');
    } else {
        $('#inputStartDate').datetimepicker('format', false);
        $('#inputEndDate').datetimepicker('format', false);
    }

    if ($("#inputRepeat option:selected").val() === '1') {
        $('#inputStartDate').datetimepicker('maxDate', false);
        $('#repeatOptions').show();
        $('#occurList').show();
        $('#inputEndDateGroup').hide();
        if (allDay) {
            $('#inputHoursGroup').hide();
            $('#inputDaysGroup').show();
        } else {
            $('#inputHoursGroup').show();
            $('#inputDaysGroup').hide();
        }

        if ($("#inputIntervalType option:selected").val() === 'weekly') {
            $('#daysOfWeek').show();
        } else {
            $("#daysOfWeek input[type=checkbox]").prop('checked', false).change();
            $('#daysOfWeek').hide();
        }
    } else {
        $('#repeatOptions').hide();
        $('#occurList').hide();
        $('#inputEndDateGroup').show();
        $('#inputHoursGroup').hide();
        $('#inputDaysGroup').hide();
        $('#daysOfWeek').hide();
    }
}

window.updateOccurrences = function () {
    let allDay = $('#allDay').is(':checked');
    let dtstartMoment = $('#inputStartDate').datetimepicker('viewDate');
    let dtstart = allDay ? dtstartMoment.format('YYYYMMDDT000000') : dtstartMoment.format('YYYYMMDDTHHmm00');

    let interval = $('#inputInterval').val();
    let count = $('#inputOccurrences').val();
    let freq = $('#inputIntervalType').val().toUpperCase();

    if ($('#inputRepeat option:selected').val() === '0') {
        count = 1;
    }

    let rruleString = "RRULE:FREQ=" + freq + ";INTERVAL=" + interval + ";COUNT=" + count;

    let days = [];
    $.each($('#daysOfWeek input:checked'), function () {
        days.push($(this).val());
    });
    let byday = days.join(',').toUpperCase();
    if (byday.length !== 0) {
        rruleString += ";BYDAY=" + byday;
    }

    rruleString += ";DTSTART=" + dtstart;

    let rule = rrule.rrulestr(rruleString);
    let all = rule.all();
    let table = $("#tableBody");
    table.empty();
    for (let i = 0; i < all.length; i++) {
        let occ = moment(all[i]);
        let fdate = occ.utc().format('dddd L');

        fdate = fdate[0].toUpperCase() + fdate.slice(1);

        let tr = $('<tr></tr>');
        tr.append('<td class="occurrence" id="' + occ.utc().format('YYYY-MM-DD HH:mm:00') + '">' + fdate + '</td>');
        tr.append($('<td onclick="$(this).parent().remove()"><i class="fas fa-trash-alt"></i></td>'));

        table.append(tr);

        if (i === all.length - 1) {
            $('#lastOcc').html(fdate).parent().show();
        }
    }

}


// Fonction pour forcer un nombre à 2 chiffres (ex: 2 -> 02)
function twoDigits(d) {
    if(0 <= d && d < 10) return "0" + d.toString();
    if(-10 < d && d < 0) return "-0" + (-1*d).toString();
    return d.toString();
}
