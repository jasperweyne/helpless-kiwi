// Styles
require('../style/admin.scss');

// Dependencies
const $ = require('jquery');

require('select2');
require('select2/dist/css/select2.css');

$(document).ready(function() {
    $('select[data-select="true"]').select2();
});