window.deleteArticle = function (id, name) {
    let modal = $('#deleteArticleModal');
    modal.find('.delete-article-title').text(name);
    modal.find('.delete-article-btn').click(function () {
        disableModalButtons();
        $.ajax({
            url: '/api/article/' + id,
            type: 'DELETE',
            contentType: 'application/json',
            success: function () {
                $('#article-' + id).remove();
                toastr.success("L'article a été supprimé.");
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