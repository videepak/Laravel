function load_data() {
	
    var status = $(".status").val();
    var username = $("#name").val();
    var reasonSubject = $("#reasonSubject").val();
    var notesType = $("#notesType").val();
    let range = $("#reportrange").find("span").text().split('-');

    $('#notes-list').DataTable({
        dom: 'lBfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [ 0, 1, 2, 4, 5 ], 
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
        "bPaginate": true,
        "ordering": false,
        "bLengthChange": true,
        "pageLength": 10,
        "bFilter": true,
        "bInfo": true,
        language: {
            search: "",
            searchPlaceholder: "Search Notes By Property.",
            // paginate: {
            //     next: '«',
            //     previous: '»'
            // },
        },
        "bAutoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": BaseUrl + "/get-note-list/",
            "type": "post",
            "data": {
                _token: $('meta[name="csrf_token"]').attr('content'),
                status: status,
                username: username,
                notesType: notesType,
                reasonSubject: reasonSubject,
                startTime: range[0], 
                endTime: range[1], 			
            }
        },
        drawCallback: function(settings) {

            $('.change-status')
                .editable({
                    source: noteSubject,
                    params: {
                        _token: $('meta[name="csrf_token"]').attr('content')
                    },
                    success: function(response, newValue) {
                        //Todo: Pnotify not working
                        if (response.status) {
                            $(function() {
                                new PNotify({
                                    title: "Violation",
                                    text: "This violation is rolled back by the user.",
                                    type: "danger",
                                    styling: 'bootstrap3'
                                });
                            });
                        }
                    }
                })
        },
        "columns": [
            {
                "data": "id"
            },
            {
                "data": "name"
            },
            {
                "data": "subject"
            },
            {
                "data": "unit"
            },
            {
                "data": "image"
            },
            {
                "data": "type"
            },
            {
                "data": "detail"
            },
            {
                "data": "action"
            },
        ]
    });
}


$(document).ready(function() {

    $('.excel-option').click(function(){
        $('.buttons-excel').trigger('click');
    }); 

    load_data();    

    $(document).on('change', '.filter', function() {

        $('#notes-list').DataTable().destroy();
        load_data();
    });

    $(document).on('click', '.model_link', function() {

        var id = $(this).data('id');
        var heading = "Note Details";

        $('.hide-footer').css('display', 'none');

        $.ajax({
            url: BaseUrl + "/getNote",
            type: "POST",
            data: {
                _token: $('meta[name="csrf_token"]').attr('content'),
                id: id
            },
            beforeSend: function() {
                showLoader();
            },
            success: function(data) {
                $('.modal-body').html(data);
                $('#popup-heading').text(heading);
		setTimeout(function(){ $('#myModal').modal('show'); }, 2000);
            },
            complete: function() {
                hideLoader();
            }
        });
    });

    $(document).on('click', '.get-image', function() {

        var src = $(this).attr('src');

        $('.modal-body').html('<p><img src="' + src + '" style="margin: 0 auto;" class="get-image img-responsive" /></p>');
        $('#popup-heading').text('Note Image');
        $('.hide-footer').css('display', 'none');
        $('#myModal').modal('show');
    });

   // $('#name').select2();

});
