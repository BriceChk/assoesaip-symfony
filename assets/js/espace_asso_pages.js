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
            //uploadSummerNoteImage(files[0]);
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

    //TODO: Call the API to create a new page, and get the inserted ID.
    let id = Math.floor(Math.random() * 100);

    $('#nav-list-card .btn-page').prop('disabled', false).text('Modifier');

    let newTab = $('#tab-template').clone();
    newTab.attr('aria-labelledby', 'page-' + id).attr('id', 'page-' + id);
    newTab.append($('<div></div>').attr('id', 'summernote-' + id));
    $('.tab-content').append(newTab);

    let newListItem = $('#list-template').clone();
    newListItem.html(newListItem.html().replace(/%id%/gi, id).replace('%name%', name));
    newListItem.attr('id', 'page-list-' + id)
    $('#nav-list').append(newListItem);

    $('#summernote-' + id).summernote(summernoteOptions);
    inputName.val('');
    $('#nav-list a[href="#page-' + id + '"]').tab('show');

}

window.removePage = function () {
    //TODO: Call the API to delete the page. But show a confirmation modal before.
}