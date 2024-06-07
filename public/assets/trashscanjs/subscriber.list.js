function load_data() {
    $('#subscriber').DataTable({
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
            "url": BaseUrl + '/admin/get-subscriber',
            "type": "POST",
            "data":{ 
              _token: $('meta[name="csrf_token"]').attr('content'), 
            }
        },
         "columns": [
            { "data": "id" },
            { "data": "companyName" },
            { "data": "email" },
            { "data": "mobile" },
            { "data": "last_login" },
            { "data": "createdAt" },
            { "data": "subStartDate" },
            { "data": "subEndDate"},		
            { "data": "status"},		
            { "data": "action"}		
        ]
    });
}

$(document).ready(function () {
    load_data();

    $(document).on("click", ".get-detail", function() {
        $('#subscriber_id').val($(this).data('id'));
        $("#add-superadmin").modal("show");
    });

    var password = document.getElementById("password")
    , confirm_password = document.getElementById("confirmPass");
  
      function validatePassword(){
          if(password.value != confirm_password.value) {
              confirm_password.setCustomValidity("Passwords Don't Match");
          } else {
              confirm_password.setCustomValidity('');
          }
      }
  
      password.onchange = validatePassword;
      confirm_password.onkeyup = validatePassword;

    
});


    


