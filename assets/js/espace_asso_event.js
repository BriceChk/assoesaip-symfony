import 'tempusdominus-bootstrap/build/css/tempusdominus-bootstrap.css'
let rrule = require('rrule');

$(document).ready(function () {
    // Enable datepickers
    let startDate = $('#inputStartDate');
    let endDate = $('#inputEndDate');
    startDate.datetimepicker({
        locale: 'fr',
        sideBySide: true
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

    moment.locale('fr');
});

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

//TODO Dates : lors de la modif date de départ : si date de fin inférieure, mettre au meme jour. Si la date de début est supéieure, mettre au meme jour.


// Fonction pour forcer un nombre à 2 chiffres (ex: 2 -> 02)
function twoDigits(d) {
    if(0 <= d && d < 10) return "0" + d.toString();
    if(-10 < d && d < 0) return "-0" + (-1*d).toString();
    return d.toString();
}
