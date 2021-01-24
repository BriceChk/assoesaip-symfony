$(document).ready(function () {
    // Add member search
    let options = {
        url: function (phrase) {
            return "/api/search/user/" + phrase;
        },
        getValue: "full_name_and_email",
        theme: 'bootstrap',
        requestDelay: 500,
        list: {
            onChooseEvent: function () {
                let inputMembre = $("#input1erAdmin");
                let user = inputMembre.getSelectedItemData();

                let el = $('#admin-item');
                el.attr('value', user.id);
                $('#admin-name').text(user.first_name + ' ' + user.last_name + ' (' + user.username + ')');

                el.show();

                inputMembre.val('');
            }
        }
    };

    $("#input1erAdmin").easyAutocomplete(options);
    // Résoudre le bug bizarre qui réduit la taille de la barre d'input :
    $(".easy-autocomplete").removeAttr("style");
});

let inputUrl = $('#inputUrlProjet');

inputUrl.on('input', function () {
    let inputUrl = $('#inputUrlProjet');
    inputUrl.val(inputUrl.val().replaceAll(" ", "-").toLocaleLowerCase().sansAccent());
})

$('#inputNomProjet').on('input', function () {
    $('#url-exemple').html($('#inputNomProjet').val().replaceAll(" ", "-").toLocaleLowerCase().sansAccent());

})

window.utiliserUrl = function () {
    inputUrl.val($('#url-exemple').html());
}

window.createProject = function () {
    let adminId = $('#admin-item').attr('value');
    if (adminId == null) {
        alert('Vous devez choisir un admin');
        return;
    }

    let json = {
        name: $('#inputNomProjet').val(),
        campus: $('#inputCampus').val(),
        type: $('#inputType').val(),
        url: $('#inputUrlProjet').val(),
        admin_id: adminId
    }

    $.ajax({
        url: '/api/project',
        type: 'PUT',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(json),
        success: function (data) {
            toastr.success("Projet créé ! Rafraichir la page pour le voir dans la liste.");
            $(':input','#form-projet')
                .not(':button, :submit, :reset, :hidden')
                .val('')
                .prop('checked', false)
                .prop('selected', false);
            $('#admin-item').attr('value', null).hide();
        },
        error: function (data) { error(data) }
    });
}

window.deleteProject = function (el) {
    let e = $(el);
    if (confirm('Voulez-vous vraiment supprimer le projet ' + e.find('.project-name').text() + ' ?')) {
        $.ajax({
            url: '/api/project/' + e.attr('id'),
            type: 'DELETE',
            contentType: 'application/json',
            success: function () {
                toastr.success("Le projet a été supprimé.");
                e.remove();
            },
            error: function (data) {
                error(data);
            }
        });
    }
}