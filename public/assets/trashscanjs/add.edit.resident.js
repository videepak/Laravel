function residentLoad() {
    $('#resident_logs').DataTable({
        dom: 'lBfrtip',
        paging: true,
        buttons: [
            {
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [ 0, 1, 2, 3, 4 ]
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
                searchPlaceholder: "Search By Unit",
        },
        "bAutoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
        "url": BaseUrl + "/property-manager/get-residentunit",
            "type": "POST",
            "data":{ 
              _token: $('meta[name="csrf_token"]').attr('content'), 
              id: $('.filter-search').val(),
              res_id: $('#resident_logs').attr('data-id'),
            }
        },
         "columns": [
            { "data": "id" },
            { "data": "property"},
            { "data": "unit_id"},
            { "data": "move_in_date"},
            { "data": "move_out_date"},
            { "data": "action"}
        ]
      }
  );
}

$(document).ready(function () {
	residentLoad();

    $('#residentDate').datetimepicker({
            format: 'MM-DD-YYYY',
            minDate: new Date(1999, 10 - 1, 25),
    });

    $('#residentMoveOut').datetimepicker({
        format: 'MM-DD-YYYY',
        minDate: new Date(1999, 10 - 1, 25),
    });

    $(document).on('click', ".move_out_date", function () {
     var unit_id = $(".move_out_date").attr('data-id');
     var resi_id = $(".move_out_date").attr('data-resi');

     $.ajax({
        url: "/property-manager/change-moveoutdate",
        type: "POST",
        data: {
            _token: $('meta[name="csrf_token"]').attr('content'),
            unit_id: unit_id,
            resi_id: resi_id,
        },
        success: function(data) {
            if (data.status) {
                $('#resident_logs').DataTable().destroy();
                residentLoad();
                new PNotify({
                    title: "Resident",
                    text: data.message,
                    type: "success",
                    styling: 'bootstrap3'
                });
            }
        },
        error: function(response) {
            console.log(response);
        }
      });
  });
 
  $('.unit-change')
    .editable({
        source: uniDetails,
        params: {
            _token: $('meta[name="csrf_token"]').attr('content')
         },
         success: function(success,params) {
            if(success){
                $('#resident_logs').DataTable().destroy();
                residentLoad();
                    new PNotify({
                        title: "Resident",
                        text: "Resident updated successfully",
                        type: "success",
                        styling: 'bootstrap3'
                    });
            }
         }
    })
});



