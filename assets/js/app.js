/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

import $ from 'jquery';
global.$ = global.jQuery = $;

import "bootstrap";
import "admin-lte";

import "admin-lte/dist/css/adminlte.css";
import "@fortawesome/fontawesome-free/css/all.css";
import "../css/bs_custom.scss"
import "../css/app.scss";
import toastr from "toastr";
import 'toastr/toastr.scss';
global.toastr = toastr;

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