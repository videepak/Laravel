$(document).ready(function () {
    $('.model_link').click(function () {

        var id = $(this).data('id');
        var heading = "Note Details";

        $('.hide-footer').css('display', 'none');

        $.ajax({
            url: BaseUrl + "/getNote",
            type: "POST",
            data: {
                _token: $('meta[name="csrf_token"]').attr('content'),
                id: id
            },
            success: function (data) {
                $('.modal-body').html(data);
                $('#popup-heading').text(heading);
                $('#myModal').modal('show');
            }
        });
    });

    $('.get-image').click(function () {
        var src = $(this).attr('src');
        $('.modal-body').html('<p><img src="' + src + '" style="margin: 0 auto;" class="get-image img-responsive" /></p>');
        $('#popup-heading').text('Note Image');
        $('.hide-footer').css('display', 'none');
        $('#myModal').modal('show');
    });
});

