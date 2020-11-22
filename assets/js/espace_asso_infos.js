import Sortable from 'sortablejs';


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
            return "/api/search/user/" + phrase;
        },
        getValue: "full_name_and_email",
        theme: 'bootstrap',
        requestDelay: 500,
        list: {
            onChooseEvent: function () {
                let inputMembre = $("#inputMembre");
                let user = inputMembre.getSelectedItemData();

                let newLi = $('#member-template').clone();
                newLi.attr('id', user.id);
                newLi.html(newLi.html().replace('%id%', user.id).replace('%name%', user.first_name + ' ' + user.last_name).replace('%email%', user.email));

                let itemFound = false;
                $('#members-list li').each(function () {
                    if ($(this).attr('id') === user.id.toString()) {
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
        'parent_project': parseInt($('#inputParentProject').val()),
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

    let members = [];
    $('#members-list li').each(function (i) {
        let item = $(this);
        let m = {
            user: item.attr('id'),
            orderPosition: i,
            role: item.find('.role-input').val(),
            admin: item.find('.boutonAdmin').hasClass("active"),
            introduction: item.find('.intro-ta').val()
        };
        members.push(m);
    });

    // Upload logo si modifié
    if($('#inputLogo').get(0).files.length !== 0){
        let formData = new FormData();

        formData.append('project[logoFile]', $('input[id=inputLogo]')[0].files[0])
        $.ajax({
            url: '/api/project/' + id + '/logo',
            type: 'POST',
            dataType: 'json',
            contentType: false,
            processData: false,
            enctype: 'multipart/form-data',
            data: formData,
            success: function () {
                toastr.success('Le logo a été enregistré !');
            },
            error: function (data) {
                error(data);
            }
        });
    }

    ajaxRequest('/api/project/' + id, json, () => {
        ajaxRequest('/api/project/' + id + '/members', members, () => {
            toastr.success('Les modifications ont été enregistrées !');
            $('.overlay').hide();
        }, error);
    }, error);
}

window.ajaxRequest = function(uri, json, success, error) {
    $.ajax({
        url: uri,
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(json),
        success: function (data) { success(data) },
        error: function (data) { error(data) }
    });
}