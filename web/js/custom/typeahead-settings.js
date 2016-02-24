$(function () {
    var $quicksearch = $("#quicksearch_form").find("input[type=text]");

    var mitglieder = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: $quicksearch.data("prefetch-url")
    });
    var ba_list = [];
    $("#ba_nav_list").find("a").each(function () {
        ba_list.push({
            "value": $(this).text(),
            "url": $(this).attr("href")
        });
    });
    var bas = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        local: ba_list
    });

    mitglieder.initialize();
    bas.initialize();

    $quicksearch.typeahead({
            hint: false,
            highlight: true,
            minLength: 1
        },
        {
            name: 'volltext',
            displayKey: 'value',
            source: function findMatches(q, cb) {
                cb([{
                    value: q,
                    url: $quicksearch.data("search-url").replace(/SUCHBEGRIFF/, encodeURI(q))
                }])
            },
            templates: {
                header: '<h3 class="quicksearch-cat">Volltextsuche nach:</h3>'
            }
        },
        {
            name: 'states',
            displayKey: 'value',
            source: bas.ttAdapter(),
            templates: {
                header: '<h3 class="quicksearch-cat">Bezirksausschuss</h3>'
            }
        },
        {
            name: 'mitglieder',
            displayKey: 'value',
            source: mitglieder.ttAdapter(),
            templates: {
                header: '<h3 class="quicksearch-cat">Stadtrats-/BA-Mitglieder</h3>'
            }
        });
    $quicksearch.on("typeahead:selected", function (ev, obj) {
        if (typeof(obj.url) != "undefined") window.location.href = obj.url;
    });
});
