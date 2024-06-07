function loadTable() {

    let username = $("#usename").val();
    let range = $("#reportrange").find("span").text().split('-');
    
    $('#activitylog').DataTable({
        "bPaginate": true,
        "ordering": false,
        "bLengthChange": true,
        "pageLength": 10,
        "bFilter": false,
        "bInfo": true,
        language: {
        search: "",
            //searchPlaceholder: "Name, Email, Mobile",
            // paginate: {
            //     next: '«',
            //     previous: '»'
            // },
        },
        "bAutoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": BaseUrl + '/activity/get-activitylog',
            "type": "POST",
            "data":{
              _token: $('meta[name="csrf_token"]').attr('content'), 
              username: username,
              startTime: range[0], 
              endTime: range[1], 			
              id: id,
            }
        },
         "columns": [
            { "data": "id" },
            { "data": "property" },
            { "data": "username" },
            { "data": "email" },
            { "data": "text" },
            { "data": "info"}		
        ]
    });
}

$(document).ready(function () {
    loadTable();

    $(document).on('change', '.filter', function() {
        $('#activitylog').DataTable().destroy();
        loadTable();
    });
});
