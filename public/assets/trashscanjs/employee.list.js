$(document).ready(function () {
	  $('#example').DataTable({
        dom: 'lBfrtip',
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
        "pageLength": 25,
        "bFilter": true,
        "bInfo": true,
        language: {
        search: "",
                searchPlaceholder: "Name, Email, Mobile",
                // paginate: {
                // next: '«',
                //         previous: '»'
                // },
        },
        "bAutoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
        "url": BaseUrl + "/employee-list",
            "type": "POST",
            "data":{ 
              _token: $('meta[name="csrf_token"]').attr('content'), 
              id: $('.filter-search').val()
            }
        },
         "columns": [
            { "data": "user_id" },
            { "data": "rolename" },
            { "data": "name" },
            { "data": "email" },
            { "data": "mobile" },
            { "data": "last_login" },
            { "data": "action"}		
        ]
   });

    $('.excel-option').click(function(){
        $('.buttons-excel').trigger('click');
    }); 

    //$("#newSearchPlace").html($(".dataTables_filter"));
    //$("#show-enties").html($(".dataTables_length")); 

    $('.makeaction').click(function () {
        $('#preloader').show();
    });
});

