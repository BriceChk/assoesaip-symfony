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
                let inputMembre = $("#inputMembre");
                let user = inputMembre.getSelectedItemData();

                if (confirm('Voulez-vous vraiment ajouter ' + user.first_name + ' ' + user.last_name + ' aux administrateurs ?')) {
                    $.ajax({
                        url: '/api/admin/' + user.id,
                        type: 'PUT',
                        contentType: 'application/json',
                        success: function () {
                            toastr.success("L'utilisateur a été ajouté aux administrateurs.");
                            let el = $('#admin-template').clone();

                            el.attr('id', user.id).html(el.html()
                                .replace(/%name%/gi, user.first_name + ' ' + user.last_name)
                                .replace(/%mail%/gi, user.username)
                                .replace('%id%', user.id)
                            ).show();

                            $('#admin-list').append(el);
                        },
                        error: function (data) {
                            error(data);
                        }
                    });

                }

                inputMembre.val('');
            }
        }
    };

    $("#inputMembre").easyAutocomplete(options);
    // Résoudre le bug bizarre qui réduit la taille de la barre d'input :
    $(".easy-autocomplete").removeAttr("style");
});

window.deleteAdmin = function (id, name) {
    if (confirm("Supprimer l'admin " + name + " ?")) {
        $.ajax({
            url: '/api/admin/' + id,
            type: 'DELETE',
            contentType: 'application/json',
            success: function () {
                toastr.success("L'utilisateur a été supprimé des administrateurs.");
                $('#' + id).remove();
            },
            error: function (data) {
                error(data);
            }
        });
    }
}