import Sortable from 'sortablejs';

import 'summernote/dist/summernote-bs4'
import 'summernote/dist/summernote-bs4.css'
import 'summernote/dist/lang/summernote-fr-FR'

const summernoteOptions = {
    placeholder: "Rédigez ici le contenu de la page",
    tabsize: 2,
    height: 800,
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
            uploadSummerNoteImage(files[0]);
        }
    }
};

function uploadSummerNoteImage(file) {
    let data = new FormData();
    data.append("image[file]", file);

    $.ajax({
        type: "POST",
        url: '/api/ressource-page/' +getCurrentPageId() + '/image',
        contentType: false,
        processData: false,
        dataType: 'text',
        enctype: 'multipart/form-data',
        data: data,
        success: function(url) {
            let img = $('<img>').attr({src: url, class: 'img-fluid'});
            $('#summernote-' + getCurrentPageId()).summernote('insertNode', img[0]);
        }
    });
}

function getCurrentPageId() {
    let page = $("#nav-list a.active").attr('href');
    return page.split('-')[1];
}

$(document).ready(function () {
    let el = document.getElementById('nav-list');
    Sortable.create(el, {
        animation: 150,
        easing: "cubic-bezier(1, 0, 0, 1)"
    });

    $('.tab-content [id^="summernote-"]').each(function () {
        $(this).summernote(summernoteOptions);
    });

});

window.changePage = function(id) {
    $('#nav-list a[href="#page-' + id + '"]').tab('show');
    $('#nav-list .btn-page').prop('disabled', false).text('Modifier');
    $('#page-list-' + id + ' .btn-page').prop('disabled', true).text('Modif. en cours')
}

window.newPage = function () {
    let inputTitle = $('#inputTitle');
    let inputShortTitle = $('#inputShortTitle');
    let inputUrl = $('#inputUrl');

    let title = inputTitle.val();
    let shortTitle = inputShortTitle.val();
    let url = inputUrl.val();
    let orderPosition = $('#nav-list li').length;

    title = title.trim();
    shortTitle = shortTitle.trim();
    url = url.trim();

    if (title === "" || shortTitle === "" || url === "") {
        toastr.error('Les trois champs doivent être remplis.');
        return;
    }

    //TODO: Call the API to create a new ressource, and get the inserted ID.
    let json = {
        title: title,
        shortTitle: shortTitle,
        url: url,
        orderPosition: orderPosition
    };

    disableModalButtons();
    $.ajax({
        url: '/api/ressource-pages',
        type: 'PUT',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(json),
        success: function (result) {
            // Stop modifying the current page
            $('#nav-list-card .btn-page').prop('disabled', false).text('Modifier');

            // Add new tab for the page
            let newTab = $('#tab-template').clone();
            newTab.attr('aria-labelledby', 'page-' + result.id).attr('id', 'page-' + result.id);
            newTab.html(newTab.html()
                .replace(/%id%/gi, result.id)
                .replace(/%title%/gi, result.title)
                .replace(/%shortTitle%/gi, result.shortTitle)
                .replace(/%url%/gi, result.url)
            )
            $('.tab-content').append(newTab);

            // Add new entry to the pages list
            let newListItem = $('#list-template').clone();
            newListItem.html(newListItem.html().replace(/%id%/gi, result.id).replace(/%title%/gi, result.shortTitle));
            newListItem.attr('id', 'page-list-' + result.id)
            $('#nav-list').append(newListItem);

            // Init summernote
            $('#summernote-' + result.id).summernote(summernoteOptions);

            // Hide and clear new page modal
            $("#addPageModal").modal('hide');
            enableModalButtons();
            inputTitle.val('');
            inputShortTitle.val('');
            inputUrl.val('');

            toastr.success('Nouvelle page créée !')

            // Change page
            window.changePage(result.id);
        },
        error: function (data) {
            toastr.error(data.responseJSON.message);
            enableModalButtons();
        }
    })
}

window.confirmRemove = function (id, title) {
    let modal = $('#deletePageModal');
    modal.find('.delete-page-title').text(title);
    modal.find('.delete-page-btn').off('click').click(function () {
        disableModalButtons();
        $.ajax({
            url: '/api/ressource-pages/' + id,
            type: 'DELETE',
            dataType: 'json',
            success: function () {
                $("#page-list-" + id).remove();
                $("#page-" + id).remove();
                let firstPageId = $('#nav-list li:first').attr('id').split('-')[2];
                changePage(firstPageId);
                toastr.success('La page a été supprimée !');
                modal.modal('hide');
                enableModalButtons();
            },
            error: function (data) {
                toastr.error(data.responseJSON.message);
                modal.modal('hide');
                enableModalButtons();
            }
        });
    });
    modal.modal('show')
}

window.savePage = function (id) {
    $('.overlay').show();
    let json = {
        id: id,
        title: $('#inputTitle-' + id).val(),
        shortTitle: $('#inputShortTitle-' + id).val(),
        url: $('#inputUrl-' + id).val(),
        description: $('#inputDescription-' + id).val(),
        html: $('#summernote-' + id).summernote('code'),
        published: $('#inputPublished-' + id).is(':checked'),
        orderPosition: $("#nav-list li").index($("#page-list-" + id))
    };

    $.ajax({
        url: '/api/ressource-pages/' + id,
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(json),
        success: function () {
            toastr.success('Les modifications ont été enregistrées !');
            // Update btn link
            $('#voir-page-btn-' + id).attr('href', '/espace-assos/ressources/' + json.url);
            $('.overlay').hide();
        },
        error: function (data) {
            toastr.error(data.responseJSON.message);
            $('.overlay').hide();
        }
    });
}