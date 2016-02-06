function ckeditor_init($element, mode) {
    var options = {
        docType: '<!DOCTYPE HTML>',
        contentsLangDirection: 'lrt',

        allowedContent: true,
        floatSpaceDockedOffsetY: 45,
        floatSpaceDockedOffsetX: 0,

        toolbarGroups: [
            {name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
            {name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align']},
            {name: 'colors'},
            {name: 'links'},
            {name: 'clipboard', groups: ['clipboard', 'undo']},
            {name: 'tools'},
            '/',
            {name: 'document', groups: ['mode', 'document', 'doctools']},
            {name: 'styles'},
            {name: 'others'},
            {name: 'editing', groups: ['find', 'selection', 'spellchecker']},
            {name: 'about'},
            {name: 'forms'},
            {name: 'insert'}
        ],

    };
    CKEDITOR.dtd.$removeEmpty.span = 0;
    if (mode == "inline") {
        $element.attr("contenteditable", true);
        CKEDITOR.inline($element[0], options);
    }
    else CKEDITOR.replace($element[0], options);
}
