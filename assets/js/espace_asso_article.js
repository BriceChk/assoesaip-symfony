import 'summernote/dist/summernote-bs4'
import 'summernote/dist/summernote-bs4.css'
import 'summernote/dist/lang/summernote-fr-FR'

let summernote = $('#summernote');
summernote.summernote({
    placeholder: "Rédigez ici le contenu de l'article",
    tabsize: 2,
    height: 500,
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
});

function uploadSummerNoteImage(file) {
    let data = new FormData();
    data.append("image[file]", file);
    let url = articleId === -1 ? '/api/project/' + projectId + '/image' : "/api/article/" + articleId + '/image';

    $.ajax({
        type: "POST",
        url: url,
        contentType: false,
        processData: false,
        dataType: 'text',
        enctype: 'multipart/form-data',
        data: data,
        success: function(url) {
            let img = $('<img>').attr({src: url, class: 'img-fluid'});
            $('#summernote').summernote('insertNode', img[0]);
        }
    });
}

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})

window.save = function (published) {

    let json = {
        title: $('.titre-page').text(),
        abstract: $('.lead').text(),
        html: $('#summernote').summernote('code'),
        category: $('#categorie').val(),
        private: $('#customRadio2').is(':checked'),
        published: published,
        notify: $('#notifCheck').is(':checked')
    }

    let method = 'POST';
    let url = '/api/article/' + articleId;
    if (articleId === -1) {
        method = 'PUT';
        url = '/api/project/' + projectId + '/articles';
    }

    $.ajax({
        url: url,
        type: method,
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(json),
        success: function (data) {
            toastr.success("Modifications enregistrées !");
            articleId = data.id;
            if (data.published) {
                let url = '/article/' + data.url;
                $('#link-a').text('https://asso.esaip.org' + url).attr('href', url);
                $('#published-div').show();
                $('#draft-div').hide();
                $('#save-btn').attr('onclick', 'save(true)');
            } else {
                $('#published-div').hide();
                $('#draft-div').show();
                $('#save-btn').attr('onclick', 'save(false)');
                $('#pub-btn').show();
            }
            updateNotifCheckDisplay();
        },
        error: function (data) { error(data) }
    });
}

$('input[name=customRadio]').change(function () {
    updateNotifCheckDisplay();
});

function updateNotifCheckDisplay() {
    if ($('#customRadio1').is(':checked')) {
        if (articleId !== -1){
            $('#notifCheckContainer').show();
        }
    } else {
        $('#notifCheckContainer').hide();
    }
}