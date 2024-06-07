function load_data() {
    var type = $(".type").val();
    
    var status = $(".status").val() != "" ? $(".status").val()
    : new URLSearchParams(window.location.search).get('status');

    let property = $('#properties').val() != "" ? $('#properties').val()
    : new URLSearchParams(window.location.search).get('property');

    $('#example').DataTable({
        dom: 'lBfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [ 1, 3, 4, 5, 6 ],
                    format: {
                        body: function ( data, row, column, node ) {
                            if(column === 3 || column === 4) {
                                data = data.replace(/<br\s*\/?>/ig, "\r\n");
                                data = data.replace(/<.*?>/g, "");
                                data = data.replace("&amp;", "&");
                                data = data.replace("&nbsp;", "");
                                data = data.replace("&nbsp;", "");
                                data = data.replace( /[$,]/g, '' );
                                data = data.replace(/<[^>]+>/g, '');
                                return data;
                            } else {
                                return data;
                            }                            
                        },
                        header: function ( data, column, row ) {
                            return data.substring(data.indexOf("value")+9,data.indexOf("</option"));
                        }
                    }
                },    
                customize: function(xlsx) {
                    $('col', xlsx.xl.worksheets['sheet1.xml']).each(function () {
                        $(this).attr('width', 20);
                    });       
                }
            },
        ],
        //"retrieve": false,
        'stateSave': true,
        "bPaginate": true,
        "ordering": false,
        "bLengthChange": true,
        "pageLength": 25,
        "bFilter": true,
        "bInfo": true,
        "searching": false,
        lengthMenu: [10, 25, 50, 100],
        language: {
            search: "",
            searchPlaceholder: "Serarch By Properties.",
            "processing": "Loading. Please wait..."
            // paginate: {
            //     next: '«',
            //     previous: '»'
            // },
        },
        "bAutoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": BaseUrl + "/barcodes/get-barcode-list/",
            "type": "post",
            "data": {
                _token: $('meta[name="csrf_token"]').attr('content'),
                status: status,
                property: property,
                type: type,
            }
        },
        "columns": [
            {
                "data": "checkBox"
            }, 	
            {
                "data": "id"
            },
            {
                "data": "barcode"
            },
            {
                "data": "unit_number"
            },
            {
                "data": "type"
            },
            {
                "data": "prodetail"
            },
            {
                "data": "status"
            },
            {
                "data": "action"
            },
        ]
    });
}

$(document).ready(function() {

    load_data();

    $(document).on('click', '.makeCheckpoint', function() {
        $.post(BaseUrl + '/routecheck-point/make-checkpoint', {
            _token: $('meta[name="csrf_token"]').attr('content'),
            id: $(this).attr('data-ids'),
            status: $(this).attr('data-status'),
        }, function (data, status) {
            new PNotify({
                title: "Barcode",
                text: 'Route checkpoint made successfully.',
                type: 'success',
                styling: 'bootstrap3'
            });

            $('#example').DataTable().ajax.reload( null, false );
        });
    });

    $('.excel-option')
        .click(
            function() {
                $('.buttons-excel').trigger('click');
            }
        ); 
    
    $(document).on('change', '.filter', function() {
         $('#example').DataTable().destroy();
         load_data();
    });

    $(document).on('click', ".bulk-activation", function () {
        if (confirm("Are you sure you want to activate the units?")) {
            let bulkCheck = localStorage.getItem('checkboxIds').split(',');

            $.post(BaseUrl + '/barcodes/bulk-activation', {
                _token: $('meta[name="csrf_token"]').attr('content'),
                id: bulkCheck
            }, function (data, status) {
                new PNotify({
                    title: "Barcode",
                    text: 'Units activeted successfully.',
                    type: 'success',
                    styling: 'bootstrap3'
                });
    
                $('#example').DataTable().ajax.reload( null, false );

                // $('#example').DataTable().destroy();
                // load_data();
            });
        }
    });

    $(document).on('click', ".make-route", function () {
        if (confirm("Are you sure you want to make the units as a route checkpoint?")) {
            let bulkCheck = localStorage.getItem('checkboxIds').split(',');

            $.post(
                BaseUrl + '/barcodes/make-route',
                {
                    _token: $('meta[name="csrf_token"]').attr('content'),
                    id: bulkCheck,
                    type: $(this).attr('data-type')
                },
                function (data, status) {
                    new PNotify(
                    {
                        title: "Barcode",
                        text: 'Units made as a route checkpoint successfully.',
                        type: 'success',
                        styling: 'bootstrap3'
                    }
                );

                $('#example').DataTable().ajax.reload( null, false );

                // $('#example').DataTable().destroy();
                // load_data();
            });
        }
    });

    $(document).on('change', ".deactive", function () {
        if (confirm("Are you sure you want to deactivate the unit?")) {	
            $.post(BaseUrl + '/barcodes/deactivation', {
                _token: $('meta[name="csrf_token"]').attr('content'),
                id: $(this).val()
            }, function (data, status) {
                new PNotify({
                    title: "Barcode",
                    text: 'Units deactivated successfully.',
                    type: 'success',
                    styling: 'bootstrap3'
                });
                
                $('#example').DataTable().ajax.reload( null, false );

                // $('#example').DataTable().destroy();
                // load_data();
            });
        }
    });

    $(document).on('click', ".deleteEntry", function () {
        $.ajax(
            {
                url: BaseUrl + '/barcode/' + $(this).attr('data-id'),
                type: 'POST',
                data: {
                    id: $(this).attr('data-id'),
                    _token: $('meta[name="csrf_token"]').attr('content'),
                },
                success: function(html) {
                    $('#example').DataTable().destroy();
                    load_data();

                    new PNotify(
                        {
                            title: html.title,
                            text: html.text,
                            type: html.class,
                            styling: 'bootstrap3'
                        }
                    );
                }
            }
        );
    });

    $(document).on('change', "#check-all", function () {	
    	if ($(this).is(':checked')) {
           $("input[name='table_records']").prop('checked', true);       
 	} else {
	    localStorage.setItem('checkboxIds', "");	
	   $("input[name='table_records']").prop('checked', false);	
        }
    });

    $(document).on('change', ".datatable-checkbox", function () {
        var currentVal = $(this).val();
    
            if ($(this).is(':checked')) {
       
                let add = $("input[name='table_records']:checked").map(function () {
                    return $(this).val()
                }).get();
       
                let localStore = `${localStorage.getItem('checkboxIds')},${add}`;
    
                //Remove comma from first and last postion:Start.
                let updatelocalStorage = localStore.replace(/^,|,$/g, '');
                //Remove comma from first and last postion:End.
                //Filter unique value:Start.
                let unique = updatelocalStorage.split(',').filter(function (itm, i, a) {
                    return i == a.indexOf(itm);
                });
                //Filter unique value:End.
    
                localStorage.setItem('checkboxIds', unique);
            } else if ($(this).is(':not(:checked)')) {
                let remove = jQuery.grep(localStorage.getItem('checkboxIds').split(','), function (value) {
                    if (value != "") {
                        return value != currentVal;
                    }
                });
    
                localStorage.setItem('checkboxIds', remove);
            }
    
            if(localStorage.getItem('checkboxIds') == "" || localStorage.getItem('checkboxIds') == "null") {
                $(".bulk-actions").css('display', 'none');	
            } else {
                $(".bulk-actions").css('display', 'block');	
            }
            
            if ($(".type").val() == '') {
                $(".bulk-actions").css('display', 'block');	
            } else if ($(".type").val() == 0) {
                $(".route-display").css('display', 'block');
                $(".unit-display").css('display', 'none');
            } else {
                $(".route-display").css('display', 'none');
                $(".unit-display").css('display', 'block');
            }
        });        

    $(document).on('change', ".type", function () {	
            if(localStorage.getItem('checkboxIds') == "" || localStorage.getItem('checkboxIds') == "null") {
                if ($(".type").val() == '') {
                    $(".bulk-actions").css('display', 'block');	
                } else if ($(".type").val() == 0) {
                    $(".route-display").css('display', 'block');
                    $(".unit-display").css('display', 'none');
                } else {
                    $(".route-display").css('display', 'none');
                    $(".unit-display").css('display', 'block');
                }
            } else {
                $(".bulk-actions").css('display', 'none');	
            }    
    });
       
    $(window).load(function () {
            //uncheck all checkbox when reload the page (For Mozila):Start
            $('.icheckbox_flat-green').removeClass('checked');
            $('.icheckbox_flat-green input[type="checkbox"]').prop('checked', false)
            //uncheck all checkbox when reload the page (For Mozila):End
            localStorage.removeItem('checkboxIds');
    });

    $('#example').DataTable().on( 'length', function ( e, settings, len ) {
            $('#example').DataTable().ajax.reload( null, false );
            // user paging is not reset on reload
    });

    // #1498: Home Page (Web App): Start
    if (new URLSearchParams(window.location.search).get('status') != null) {
        let urlString = new URLSearchParams(window.location.search).get('status');
        $(".status").children('[value="' + urlString + '"]').prop("selected", true);
    }

    if (new URLSearchParams(window.location.search).get('property') != null) {
        let urlString = new URLSearchParams(window.location.search).get('property');
        $("#properties").children('[value="' + urlString + '"]').prop("selected", true);
    }
    // #1498: Home Page (Web App): End
});
