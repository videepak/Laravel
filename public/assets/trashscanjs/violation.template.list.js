$(document).ready(function () {

    var token = $('meta[name="csrf_token"]').attr('content');

    $(".get-detail").click(function () {

        let id = $(this).data('id');

        $.ajax({
            url: BaseUrl + '/violation/template/detail/' + id,
            type: "GET",
            beforeSend: function () {
                showLoader();
            },
            success: function (data) {
                hideLoader();
                if (data.result) {
                    $("#subject").val(data.detail.subject);
                    $("#template").val(data.detail.content);
                    $("#name").val(data.detail.name);
                    $(".modal-title").text("Edit Template");
                    $("#violatio-template").attr('action', BaseUrl + '/update/template/' + id);
                    $("#add-template").modal("show");
                }
            }
        });
    });

    //Reset modal when close.
    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $("#violatio-template").attr('action', BaseUrl + "/add-template");
        $(".modal-title").text("Add Template");
    });

    //textarea not accept whitw space:Start 
    $(".validate-space").on("keydown", function (e) {

        var len = $(this).val().length;
        
        if (len == 0)
        {
            return e.which !== 32;
        } else
        {
            var pos = $(this).getCursorPosition();
            if (pos == 0)
            {
                return e.which !== 32;
            }
        }
    });
    //textarea not accept whitw space:End

});