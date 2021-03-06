window.confirmRemove = function (id, title) {
    let modal = $('#deleteNewsModal');
    modal.find('.delete-news-title').text(title);
    modal.find('.delete-news-btn').off('click').click(function () {
        disableModalButtons();
        $.ajax({
            url: '/api/assoesaip-news/' + id,
            type: 'DELETE',
            dataType: 'json',
            success: function () {
                $("#news-list-" + id).remove();
                toastr.success('La News a été supprimée !');
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

