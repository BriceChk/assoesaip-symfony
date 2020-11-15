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
            //TODO uploadSummerNoteImage(files[0]);
        }
    }
};

$(document).ready(function () {
    let el = document.getElementById('nav-list');
    Sortable.create(el, {
        animation: 150,
        easing: "cubic-bezier(1, 0, 0, 1)"
    });

    $('.tab-content [id^="summernote-"]').each(function (index) {
        $(this).summernote(summernoteOptions);
    });
});

window.changePage = function(id, el) {
    $('#nav-list a[href="#page-' + id + '"]').tab('show');
    $('#nav-list-card .btn-page').prop('disabled', false).text('Modifier');
    $(el).prop('disabled', true).text('Modif. en cours')
}

window.newPage = function () {
    let inputName = $('#inputName');
    let name = inputName.val();
    name = name.trim();

    if (name === "") {
        return;
    }

    let json = {
        name: name,
        order_position: $('#nav-list li').length,
        html: '',
        published: false
    }

    $.ajax({
        url: '/api/project/' + id + '/pages',
        type: 'PUT',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(json),
        success: function (page) {
            $('#nav-list-card .btn-page').prop('disabled', false).text('Modifier');

            let newTab = $('#tab-template').clone();
            newTab.attr('aria-labelledby', 'page-' + page.id).attr('id', 'page-' + page.id);
            newTab.append($('<div></div>').attr('id', 'summernote-' + page.id));
            $('.tab-content').append(newTab);

            let newListItem = $('#list-template').clone();
            newListItem.html(newListItem.html().replace(/%id%/gi, page.id).replace('%name%', name));
            newListItem.attr('id', 'page-list-' + page.id)
            $('#nav-list').append(newListItem);

            $('#summernote-' + page.id).summernote(summernoteOptions);
            inputName.val('');
            $('#nav-list a[href="#page-' + page.id + '"]').tab('show');

            toastr.success('La nouvelle page a été créée !');
        },
        error: function (data) { error(data) }
    });
}

window.removePage = function (pageId) {
    let modal = $('#deletePageModal');
    modal.find('.delete-page-title').text($('#page-list-' + pageId + ' .page-name').text());
    modal.find('.delete-page-btn').click(function () {
        disableModalButtons();
        $.ajax({
            url: '/api/project-page/' + pageId,
            type: 'DELETE',
            contentType: 'application/json',
            success: function () {
                $('#page-list-' + pageId).remove();
                $('#page-' + pageId).remove();
                toastr.success('La page a été supprimée.');
                changePage('home', $('#home-modif-btn'));
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

window.save = function () {
    let count = 0;

    //todo update la page Home

    let html = $('#summernote-home').summernote('code');
    let json = {
        html: html
    }

    $.ajax({
        url: '/api/project/' + id + "/pages/home",
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(json),
        success: function (data) {
            count++;
            if (count === $('#nav-list li').length) {
                toastr.success('Les pages ont été enregistrées !');
            }
        },
        error: function (data) { error(data) }
    });

    $('#nav-list li').each(function (i) {
        if ($(this).attr('id') !== undefined) {
            let pageId = $(this).attr('id').split('-')[2];
            let pageName = $(this).find('.page-name').text();
            let html = $('#summernote-' + pageId).summernote('code');
            let published = $(this).find('.boutonPublier').hasClass('active');


            let json = {
                name: pageName,
                html: html,
                published: published,
                order_position: i
            }

            $.ajax({
                url: '/api/project-page/' + pageId,
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify(json),
                success: function (data) {
                    count++;
                    if (count === $('#nav-list li').length) {
                        toastr.success('Les pages ont été enregistrées !');
                    }
                },
                error: function (data) { error(data) }
            });
        }
    });
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