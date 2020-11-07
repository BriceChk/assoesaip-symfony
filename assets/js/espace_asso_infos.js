import Sortable from 'sortablejs';
import 'easy-autocomplete/dist/easy-autocomplete.css';
import toastr from "toastr";

$(document).ready(function () {
    // Sortable members list
    let el = document.getElementById('members-list');
    Sortable.create(el, {
        animation: 150,
        easing: "cubic-bezier(1, 0, 0, 1)",
    });

    // Add member search
    let options = {
        url: function (phrase) {
            return "URL-API-USER-SEARCH?query=" + phrase;
        },
        getValue: "text",
        theme: 'bootstrap',
        requestDelay: 500,
        list: {
            onChooseEvent: function () {
                let inputMembre = $("#inputMembre");
                let id = inputMembre.getSelectedItemData().value;
                let texte = inputMembre.val();

                let newLi = $('#member-template').clone();
                newLi.attr('id', id);
                newLi.html(newLi.html().replace('%id%', id).replace('%name%', texte.split(' (')[0]).replace('%email%', texte.split(' (')[1].replace(')', '')));

                let itemFound = false;
                $('#members-list li').each(function () {
                    if ($(this).attr('id') === id) {
                        itemFound = true;
                    }
                });

                if (!itemFound) {
                    $('#members-list').append(newLi);
                }

                inputMembre.val('');
            }
        }
    };

    $("#inputMembre").easyAutocomplete(options);
    // Résoudre le bug bizarre qui réduit la taille de la barre d'input :
    $(".easy-autocomplete").removeAttr("style");
});

window.previewLogo = function(input) {
    if (input.files && input.files[0]) {
        let reader = new FileReader();

        reader.onload = function(e) {
            let logoProjet = $('#logo-projet-preview');
            logoProjet.attr('src', e.target.result);
            logoProjet.show();
        };

        reader.readAsDataURL(input.files[0]);
    }
}

/* Show and hide parent project seletion if Club or Project is selected */
$('#inputType').focus().change(function () {
    if ($(this).val() === "Club" || $(this).val() === "Projet") {
        $('#parentProjectGroup').show();
    } else {
        $('#parentProjectGroup').hide();
    }
});

window.save = function () {
    // Save les infos de base réseaux sociaux
    let json = {
        'name': $('#inputName').val(),
        'description': $('#inputDescription').val(),
        'category': parseInt($('#inputCategorie').val()),
        'type': $('#inputType').val(),
        'parentProject': parseInt($('#inputParentProject').val()),
        'keywords': $('#inputKeywords').val(),
        'email': $('#inputEmail').val(),
        'social': {}
    };

    let fb = $('#inputFb').val()
    let insta = $('#inputInsta').val()
    let yt = $('#inputYt').val()
    let discord = $('#inputDiscord').val()
    let twitter = $('#inputTwitter').val()
    let snap = $('#inputSnap').val()

    if (fb !== "")
        json['social']['fb'] = fb;
    if (insta !== "")
        json['social']['insta'] = insta;
    if (yt !== "")
        json['social']['yt'] = yt;
    if (discord !== "")
        json['social']['discord'] = discord;
    if (twitter !== "")
        json['social']['twt'] = twitter;
    if (snap !== "")
        json['social']['snap'] = snap;

    $.ajax({
        url: '/api/project/' + id,
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(json),
        success: function () {
            toastr.success('Les modifications ont été enregistrées !');
            $('.overlay').hide();
        },
        error: function (data) {
            console.log(data);
            toastr.error(data.responseJSON.message);
            $('.overlay').hide();
        }
    });

    // Save le logo
    // TODO: utiliser LiipImagine

    //TODO Save la liste des membres
}