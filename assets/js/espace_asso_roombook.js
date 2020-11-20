import 'tempusdominus-bootstrap/build/css/tempusdominus-bootstrap.css'

$(document).ready(function () {
    let d = $('#date');
    let hd = $('#heure-debut');
    let hf = $('#heure-fin');
    let now = new Date();

    d.datetimepicker({
        format: 'L',
        locale: 'fr'
    });
    d.datetimepicker('date', now)

    hd.datetimepicker({
        format: 'LT',
        locale: 'fr'
    });
    hd.datetimepicker('date', now);

    hf.datetimepicker({
        format: 'LT',
        locale: 'fr'
    });
    hf.datetimepicker('date', now);
});

window.createRoombook = function () {
    $('.overlay').show();
    let json = {
        start_time: $('#heure-debut').datetimepicker('viewDate').format('HH:mm:00'),
        end_time: $('#heure-fin').datetimepicker('viewDate').format('HH:mm:00'),
        date: $('#date').datetimepicker('viewDate').format('YYYY-MM-DD'),
        object: $('#objet').val(),
        needs: $('#demandes-part').val(),
        nb_participants: parseInt($('#nb-participants').val())
    }

    $.ajax({
        url: '/api/project/' + projectId + '/roombooks',
        type: 'PUT',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(json),
        success: function (data) {
            toastr.success("La demande a été enregistrée !");

            let dA = data.date.split('T')[0].split('-');
            let stA = data.start_time.split('T')[1].split(':');
            let etA = data.end_time.split('T')[1].split(':');

            let date = dA[2] + '/' + dA[1] + '/' + dA[0] + ' ' + stA[0] + 'h' + stA[1] + '-' + etA[0] + 'h' + etA[1];

            let newRb = $('#rb-template').clone();
            newRb.html(newRb.html()
                .replace('%date%', date)
                .replace('%id%', data.id)
                .replace('%part%', data.nb_participants.toString())
                .replace('%object%', data.object)
                .replace('%needs%', data.needs)
            );
            newRb.attr('id', 'rb-' + data.id).show();

            $('#listeDemandesEnCours').prepend(newRb);
        },
        error: function (data) { error(data) }
    }).done(function () {
        $('.overlay').hide();
    });
}

window.deleteRb = function (id) {
    let modal = $('#deleteRoombookModal');
    modal.find('.delete-rb-btn').off('click').click(function () {
        disableModalButtons();
        $.ajax({
            url: '/api/roombook/' + id,
            type: 'DELETE',
            contentType: 'application/json',
            success: function () {
                $('#rb-' + id).remove();
                toastr.success("La demande a été supprimée.");
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