import 'summernote/dist/summernote-bs4'
import 'summernote/dist/summernote-bs4.css'
import 'summernote/dist/lang/summernote-fr-FR'

let summernote = $('#summernote');
summernote.summernote({
    placeholder: "Rédigez ici le contenu de la News",
    tabsize: 2,
    height: 700,
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
});

$('#inputLongNews').change(function() {
    if ($('#inputLongNews').is(':checked')) {
        $('#inputLinkGroup').hide();
        $('#summernote-container').show();
    } else {
        $('#inputLinkGroup').show();
        $('#summernote-container').hide();
    }
});

window.save = function (id) {
    let newArticle = id === undefined;
    $('.overlay').show();
    let json = {
        title: $('#inputTitle').val(),
        content: $('#inputContent').val(),
        has_html: $('#inputLongNews').is(':checked'),
        link: $('#inputLink').val(),
        published: $('#inputPublished').is(':checked'),
        html: $('#summernote').summernote('code')
    };
    if (!newArticle) {
        json.id = id;
    }

    $.ajax({
        url: '/api/assoesaip-news' + (newArticle ? '' : '/' + id),
        type: newArticle ? 'PUT' : 'POST',
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(json),
        success: function (result) {
            toastr.success('Les modifications ont été enregistrées !');
            // Update btn link
            $('#saveBtn').attr('onclick', 'save(' + result.id + ')');
            $('.overlay').hide();
        },
        error: function (data) {
            toastr.error(data.responseJSON.message);
            $('.overlay').hide();
        }
    });

}