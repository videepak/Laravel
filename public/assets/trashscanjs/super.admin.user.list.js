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
            "url": BaseUrl + '/admin/users',
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
            { "data": "last_login" },
            { "data": "action" },
        ]
    });


}

$(document).ready(function () {
    load_data();

    $(document).on("click", ".get-detail", function() {
        $('#user_id').val($(this).data('id'));
        $("#add-user").modal("show");
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

      $(document).on("click", ".delete-users", function(e) {
        let id = $(this).data('id');
        // console.log(id);
        var token = $('meta[name="csrf_token"]').attr('content');

        $.ajax({
            url: BaseUrl + '/admin/admin-user-delete/' + id,
            type: "POST",
            data: {_token: token, userid: id},
            beforeSend: function () {
                  //showLoader();
             },
            success: function (data) {
                hideLoader();
                if (data.status == 0) {
                    $('#subscriber').DataTable().destroy();
                    load_data();
                    new PNotify({
                        title: "User",
                        text: data.message,
                        type: "success",
                        styling: 'bootstrap3'
                    });
                } else {
                    $('#error_message').html('');
                }
               
            }
        });
    });
});

