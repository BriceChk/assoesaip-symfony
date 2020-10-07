import 'tempusdominus-bootstrap/build/css/tempusdominus-bootstrap.css'

$(document).ready(function () {
    $('#date').datetimepicker({
        format: 'L',
        locale: 'fr'
    });

    $('#heure-debut').datetimepicker({
        format: 'LT',
        locale: 'fr'
    });

    $('#heure-fin').datetimepicker({
        format: 'LT',
        locale: 'fr'
    });
});