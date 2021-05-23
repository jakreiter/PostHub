/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

const $ = require('jquery');
global.$ = global.jQuery = $;
// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
import 'bootstrap';
import 'tablesorter';

import 'select2';

var ifrejmy = [];
var formNumber = 0;
global.ifrejmy = ifrejmy;
global.formNumber = formNumber;

import rebind from './js/rebind';

$(document).ready(function () {
    rebind();
});


// start the Stimulus application
import './bootstrap';
