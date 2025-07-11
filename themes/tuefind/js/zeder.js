var Zeder = {
    RenderView: function (zederViewId, htmlContainerId) {
        $(document).ready(function() {
            let selector = '#' + htmlContainerId;
            $(selector).DataTable( {
                ajax		: {
                    url: VuFind.path + '/ZederProxy/Load?action=' + zederViewId,
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
                        pageLength 	: 20
                    });
                }
            });
        });
    }
};
