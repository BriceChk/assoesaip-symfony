// Generate url with title
document.getElementById("inputUrl").addEventListener('input', () => {
    document.getElementById("inputUrl").value = document.getElementById("inputUrl").value.replaceAll(" ", "-").toLocaleLowerCase().sansAccent();
});

document.getElementById("inputNameCateg").addEventListener('input', () => {
    document.getElementById("inputUrl").value = document.getElementById("inputNameCateg").value.replaceAll(" ", "-").toLocaleLowerCase().sansAccent();
});

String.prototype.sansAccent = function(){
    const accent = [
        /[\300-\306]/g, /[\340-\346]/g, // A, a
        /[\310-\313]/g, /[\350-\353]/g, // E, e
        /[\314-\317]/g, /[\354-\357]/g, // I, i
        /[\322-\330]/g, /[\362-\370]/g, // O, o
        /[\331-\334]/g, /[\371-\374]/g, // U, u
        /[\321]/g, /[\361]/g, // N, n
        /[\307]/g, /[\347]/g, // C, c
    ];
    const noaccent = ['A', 'a', 'E', 'e', 'I', 'i', 'O', 'o', 'U', 'u', 'N', 'n', 'C', 'c'];

    let str = this;
    for(let i = 0; i < accent.length; i++){
        str = str.replace(accent[i], noaccent[i]);
    }

    return str;
};

let inputCateg = $('#inputCateg');
let inputCategEvent = $('#inputCategEvent');
let inputCategArticle = $('#inputCategArticle');

// Loading project categories
inputCateg.change(function() {
    $(this).blur();
    if (inputCateg.val() === '-1') {
        resetFormCateg();
        return;
    }
    let categ = inputCateg.find(' :selected');

    $('#inputNameCateg').val(categ.text());
    $('#inputUrl').val(categ.attr('data-url'));
    $('#inputDescription').val(categ.attr('data-description'));
    $('#inputOrder').val(categ.attr('data-order'));
    $('#inputVisible').prop('checked', categ.attr('data-visible') === '1');
});

// Loading event categories
inputCategEvent.change(function() {
    $(this).blur();
    if (inputCategEvent.val() === '-1') {
        resetFormCateg();
        return;
    }
    let categ = inputCategEvent.find(' :selected');

    $('#inputNameCategEvent').val(categ.text());
    $('#inputColorEvent').val(categ.attr('data-color'));
});

// Loading article categories
inputCategArticle.change(function() {
    $(this).blur();
    if (inputCategArticle.val() === '-1') {
        resetFormCateg();
        return;
    }

    let categ = inputCategArticle.find(' :selected');

    $('#inputNameCategArticle').val(categ.text());
    $('#inputColorArticle').val(categ.attr('data-color'));
});

// Reset all fields
window.resetFormCateg = function () {
    inputCateg.val('-1');
    inputCategEvent.val('-1');
    inputCategArticle.val('-1');
    $(':input', '#form-categ')
        .not(':button, :submit, :reset, :hidden')
        .val('')
        .prop('checked', false)
        .prop('selected', false);
    $('#inputNameCategArticle').val('');
    $('#inputNameCategEvent').val('');
    $('#inputColorArticle').val('#000000');
    $('#inputColorEvent').val('#000000');
}

window.deleteCategory = function (type) {
    let el;
    switch (type) {
        case 'project':
            el = inputCateg;
            break;
        case 'article':
            el = inputCategArticle;
            break;
        default:
            el = inputCategEvent;
    }
    let id = el.val();
    if (id === '-1') {
        resetFormCateg();
        return;
    }

    if (confirm('Voulez-vous vraiment supprimer la catégorie ' + type + ' sélectionnée ?')) {
        $.ajax({
            url: '/api/' + type + '-category/' + id,
            type: 'DELETE',
            contentType: 'application/json',
            success: function () {
                toastr.success("La catégorie a été supprimée.");
                el.find(' :selected').remove();
                resetFormCateg();
            },
            error: function (data) {
                error(data);
            }
        });
    }
}

window.saveProjectCategory = function () {
    let id = inputCateg.val();
    let json = {
        name: $('#inputNameCateg').val(),
        url: $('#inputUrl').val(),
        description: $('#inputDescription').val(),
        list_order: $('#inputOrder').val(),
        visible: $('#inputVisible').is(':checked')
    }

    $.ajax({
        url: '/api/project-category' + (id === '-1' ? '' : '/' + id),
        type: id === '-1' ? 'PUT' : 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(json),
        success: function (data) {
            toastr.success("Catégorie enregistrée !");
            let el;
            if (id === '-1') {
                el = $('<option></option>');
            } else {
                el = inputCateg.find(' :selected');
            }

            el.val(data.id);
            el.text(data.name);
            el.attr('data-url', data.url);
            el.attr('data-description', data.description);
            el.attr('data-order', data.list_order);
            el.attr('data-visible', data.visible ? 1 : 0);

            if (id === '-1') {
                inputCateg.append(el);
            }
        },
        error: function (data) { error(data) }
    });
}

window.saveOtherCateg = function (type) {
    let el = type === 'article' ? inputCategArticle : inputCategEvent;
    let id = el.val();
    let json;
    if (type === 'article') {
        json = {
            name: $('#inputNameCategArticle').val(),
            color: $('#inputColorArticle').val(),
        }
    } else {
        json = {
            name: $('#inputNameCategEvent').val(),
            color: $('#inputColorEvent').val(),
        }
    }

    $.ajax({
        url: '/api/' + type + '-category' + (id === '-1' ? '' : '/' + id),
        type: id === '-1' ? 'PUT' : 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(json),
        success: function (data) {
            toastr.success("Catégorie enregistrée !");
            //TODO Maj les data attributes ou ajouter une option
        },
        error: function (data) { error(data) }
    });
}