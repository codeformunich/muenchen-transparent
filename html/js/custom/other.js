
$(function () {
    if (!Modernizr.testAllProps("hyphens")) yepnope.injectJs("/js/hyphenator.js");

    $.material.init();
});
