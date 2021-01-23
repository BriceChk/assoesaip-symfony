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
                    alert("Ce n'est pas encore implémenté.");
                    //TODO Requete + ajout à la liste
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
        alert("Ce n'est pas encore implémenté.");
        //TODO La suppression
    }
}