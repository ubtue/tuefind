var Zeder = {
    RenderView: function (zederViewId, htmlContainerId) {
        $(document).ready(function() {
            let selector = '#' + htmlContainerId;

            // Reference for translations:
            // https://datatables.net/reference/option/language
            // Note: VuFind display texts need to be manually registered in view.phtml to make them available for the JS side
            let languageSettings = {
                search: VuFind.translate('Search'),
                info: VuFind.translate('showing_items_of_html', {'%%start%%': '_START_', '%%end%%': '_END_', '%%total%%': '_TOTAL_'}),
                lengthMenu: ' _MENU_ ' + VuFind.translate('Results per page')
            };
            $(selector).DataTable( {
                ajax: {
                    url: VuFind.path + '/Zeder/Proxy/' + encodeURIComponent(zederViewId),
                    // dataSrc: '' is important because Zeder delivers an array but datatables expects an object
                    dataSrc: ''
                    // Note: additional options must be put into the "initComplete" block below where the table is re-initialized - they will have no effect here.
                },
                initComplete: function(settings, json) {
                    // auto-generate columns from JSON (without this, headlines need to be specified explicitly)
                    const columns = Object.keys(json[0]).map(key => ({
                        title: key,
                        data: key
                    }));

                    // (re-initialize table)
                    $(selector).DataTable().clear().destroy();
                    $(selector).DataTable({
                        data 		: json,
                        columns 	: columns,

                        // put additional options here
                        lengthMenu	: [ 10,20,50,100,200,500,1000 ],
                        pageLength 	: 20,
                        language        : languageSettings // adjust language when data is available + page is re-rendered
                    });
                },
                language: languageSettings // adjust language here as well, in case ajax operation fails & defaults are shown
            });
        });
    }
};
