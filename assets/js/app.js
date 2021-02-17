/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
require('offline-plugin/runtime').install();

import $ from 'jquery';
global.$ = global.jQuery = $;

import "bootstrap";
import "admin-lte";

import toastr from "toastr";
require('easy-autocomplete/dist/jquery.easy-autocomplete');

global.toastr = toastr;

import "admin-lte/dist/css/adminlte.css";
import "@fortawesome/fontawesome-free/css/all.css";
import 'toastr/toastr.scss';
import 'easy-autocomplete/dist/easy-autocomplete.css';
import "../css/bs_custom.scss"
import "../css/app.scss";

window.error = function(data) {
    let errors = data.responseJSON;
    let s = '';
    for (let i = 0; i < errors.length; i++) {
        s += errors[i].message + '<br>';
    }
    toastr.error(s, 'Erreur', {timeOut: 5000});
    $('.overlay').hide();
}

window.disableModalButtons = function() {
    $('.modal-btn').prop('disabled', true);
}

window.enableModalButtons = function() {
    $('.modal-btn').prop('disabled', false);
}

$(document).ready(function () {
    let options = {
        url: function (phrase) {
            return "/api/search/" + phrase;
        },
        getValue: "name",
        theme: 'bootstrap',
        requestDelay: 200,
        list: {
            onChooseEvent: function () {
                let search = $("#global-search");
                let item = search.getSelectedItemData();
                window.location = item.url;
            }
        },
        template: {
            type: "description",
            fields: {
                description: "type"
            }
        }
    };

    $("#global-search").easyAutocomplete(options);

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
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