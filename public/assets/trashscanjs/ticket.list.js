function load_data() {
    let status = $("#status").val();
    
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
            "url": `${BaseUrl}/admin/tickets/all`,
            "type": "GET",
            "data":{ 
                _token: $('meta[name="csrf_token"]').attr('content'),
                status: status
            }
        },
        drawCallback: function(settings) {
            $('.action-change')
                .editable({
                    source: actionEdit,
                    params: {
                        _token: $('meta[name="csrf_token"]').attr('content')
                    },
                    success: function(response, newValue) {
                        if (response.status) {
                            new PNotify({
                                title: "Ticket",
                                text: "Ticket updated successfully.",
                                type: "success",
                                styling: 'bootstrap3'
                            });
                        }
                    }
                })	
        },
        "columns": [
            { "data": "id" },
            { "data": "ticketId" },
            { "data": "name" },
            { "data": "email" },
            { "data": "mobile" },
            { "data": "status" },
            // { "data": "message" },
            { "data": "createdAt" },
            { "data": "action" }
        ]
    });
}

$(document).ready(function () {
    
    load_data();

    var token = $('meta[name="csrf_token"]').attr('content');

    $(document).on('change', '.filter', function() {
        $('#subscriber').DataTable().destroy();
        load_data();
    });

    $(document).on("click", ".get-detail", function() {    
        let id = $(this).data('id');
        // $.ajax({
        //     url: BaseUrl + '/admin/super-admin-detail/' + id,
        //     type: "GET",
        //     beforeSend: function () {
        //        // showLoader();
        //     },
        //     success: function (data) {
        //         hideLoader();
        //         if (data.result) {
        //             $("#name").val(data.detail.name);
        //             $("#email").val(data.detail.email);
        //             $("#email").attr('readonly', true);
        //             $(".modal-title").text("Edit Super Admin");
        //             $("#superadmin-template").attr('action', BaseUrl + '/admin/update/' + id);
        //             $("#add-superadmin").modal("show");
        //             $("#password").attr('required', false);
        //             $("#confirmPass").attr('required', false);
        //         }
        //     }
        // });

        $.ajax({
            url: BaseUrl + '/admin/view-comment/' + id,
            type: "GET",
            beforeSend: function () {
                // showLoader();
            },
            success: function (data) {
                hideLoader();
                $("#comment-content").html(data);
                $("#add-superadmin").modal("show");
            }
        });

        $("#ticket_id").val(id);
        $("#comment-model").modal("show");
    });

    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
});