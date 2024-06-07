$(document).ready(function () {
  
    $('#example').DataTable({
        dom: 'lBfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [ 0, 1, 2, 3]
                }
            },
        ],
	    "bPaginate": true,
        "ordering": false,
        "bLengthChange": true,
        "pageLength": 25,
        "bFilter": true,
        "bInfo": true,
        language: {
        search: "",
                searchPlaceholder: "Name, Cities, Address.",
                // paginate: {
                // next: '«',
                //         previous: '»'
                // },
        },
        "bAutoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
        "url": BaseUrl + "/getBuilding",
            "type": "POST",
            "data":{ 
                _token: $('meta[name="csrf_token"]').attr('content'), 
                propertyId: new URLSearchParams(window.location.search).get('property'),
                id: $('.filter-search').val()     
            }
        },
         "columns": [
            { "data": "user_id" },
            { "data": "name" },            
            { "data": "type" },   
            { "data": "building" },   
            { "data": "address" },
            //{ "data": "action" }
        ]
     });

    $('.excel-option').click(function() {
        $('.buttons-excel').trigger('click');
    }); 

	/* model js  */
    $(document).on('click', '.assign-user', function () {
        var property_id = $(this).data('propertyid');
        $('#assign_user_modal').modal({
            show: true
        });
        $('#property_id').val(property_id);
        var token = $('meta[name="csrf_token"]').attr('content'); 
        var property_id = $('#property_id').val();

        $.ajax({
            url: BaseUrl+"/property/asssigned/employees/all",
            type: "POST",
            data: {_token: token, property_id: property_id},
            success: function (data) {
                var employee_ids = data;
                $(".select_emp").val(employee_ids).select2({
                    width: '100%'
                });
            }
        });
    });

    $('.select_emp').on("select2:unselecting", function (e) {
        //var unselected_value = $('#emp').val();
        var property_id = $('#property_id').val();
        var token = $('meta[name="csrf_token"]').attr('content');
        var id = e.params.args.data.id; //your id

        $.ajax({
            url: BaseUrl+"/property/asssigned/employees/delete",
            type: "POST",
            data: {_token: token, property_id: property_id, empid: id},
            success: function (data) {
                if (data.status == 0) {
                        new PNotify({
                            title: "Employee",
                            text: data.message,
                            type: "success",
                            styling: 'bootstrap3'
                        });
                } else {
                    $('#error_message').html('');
                }
            }
        });

    }).trigger('change'); 
});

