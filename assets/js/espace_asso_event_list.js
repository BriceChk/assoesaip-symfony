window.deleteEvent = function (id, name) {
    let modal = $('#deleteEventModal');
    modal.find('.delete-event-title').text(name);
    modal.find('.delete-event-btn').off('click').click(function () {
        disableModalButtons();
        $.ajax({
            url: '/api/event/' + id,
            type: 'DELETE',
            contentType: 'application/json',
            success: function () {
                $('#event-' + id).remove();
                toastr.success("L'événement a été supprimé.");
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