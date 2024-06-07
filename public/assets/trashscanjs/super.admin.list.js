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
            "url": BaseUrl + '/admin/super-admin',
            "type": "POST",
            "data":{ 
              _token: $('meta[name="csrf_token"]').attr('content'), 
            }
        },
         "columns": [
            { "data": "id" },
            { "data": "name" },
            { "data": "email" },
            { "data": "last_login" },
            { "data": "action" },
        ]
    });
}

$(document).ready(function () {
    
    load_data();

    var token = $('meta[name="csrf_token"]').attr('content');

    $(document).on("click", ".get-detail", function() {    
        let id = $(this).data('id');

        $.ajax({
            url: BaseUrl + '/admin/super-admin-detail/' + id,
            type: "GET",
            beforeSend: function () {
               // showLoader();
            },
            success: function (data) {
                hideLoader();
                if (data.result) {
                    $("#name").val(data.detail.name);
                    $("#email").val(data.detail.email);
                    $("#email").attr('readonly', true);
                    $(".modal-title").text("Edit Super Admin");
                    $("#superadmin-template").attr('action', BaseUrl + '/admin/update/' + id);
                    $("#add-superadmin").modal("show");
                    $("#password").attr('required', false);
                    $("#confirmPass").attr('required', false);
                }
            }
        });
    });

    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $("#superadmin-template").attr('action', BaseUrl + "/admin/add-super-admin");
        $(".modal-title").text("Add Super Admin");
        $("#password").attr('required', true);
        $("#confirmPass").attr('required', true);
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