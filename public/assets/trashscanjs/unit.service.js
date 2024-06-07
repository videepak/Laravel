$(document).ready(function () {
  
    $('#example').DataTable({
      "bPaginate": true,
      "ordering": false,
      "bLengthChange": true,
      "pageLength": 25,
      "bFilter": true,
      "bInfo": true,
      "bAutoWidth": false,
      "processing": true,
      "serverSide": true,
      "ajax": {
        "url": BaseUrl + "/property-manager/serviced-list",
        "type": "POST",
        "data": { 
        _token: $('meta[name="csrf_token"]').attr('content'), 
        }
      },
      "columns": [
        { "data": "user_id" },
        { "data": "qrcode" },            
        { "data": "unitof" },
        { "data": "property" },
	      { "data": "detail" },
      ]    
    });
});

