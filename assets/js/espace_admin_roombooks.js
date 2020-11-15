window.reponse = function (id) {
    let choix = $('input[name=choix-' + id + ']:checked').val();
    let statut = "Acceptée";
    let room = $('#salle-' + id).val();
    if (choix === '0') {
        statut = "Refusée";
        room = '';
    } else {
        if (room === '') {
            toastr.error('Vous devez entrer le nom de la salle.')
            return;
        }
    }

    $('.overlay').show();

    let json = {
        'id': id,
        'room': room,
        'status': statut
    };

    $.ajax({
        url: '/api/roombook/' + id,
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(json),
        success: function () {
            toastr.success('Les modifications ont été enregistrées !');
            $('.overlay').hide();
        },
        error: function (data) {
            toastr.error(data.responseJSON.message);
            $('.overlay').hide();
        }
    });
}