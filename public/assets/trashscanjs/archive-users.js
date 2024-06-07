function load_data() {
    $('#archive-users').DataTable({
        "bPaginate": true,
        "ordering": false,
        "bLengthChange": true,
        "pageLength": 10,
        "bFilter": false,
        "bInfo": true,
        "searching": true,
        "bAutoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": BaseUrl + '/admin/archive-users-list',
            "type": "POST",
            "data":{ 
              _token: $('meta[name="csrf_token"]').attr('content'), 
            }
        },
         "columns": [
            { "data": "id" },
            { "data": "companyName" },
            { "data": "rolename" },
            { "data": "name" },
            { "data": "email" },
            { "data": "mobile" },
            { "data": "lastlogin" },

        ]
    });
}
$(document).ready(function () {
    load_data();
});