$(document).ready(function () {
    $('.logo-redirect').click(function () {
        window.location.href = "{{url('/home')}}";
    });
});

function showLoader() {
    $('.loading').fadeIn(1000);
    //$('.loading').show();
}

function hideLoader() {
    $('.loading').fadeOut(1000);
    //$('.loading').hide();
}

//Model empty when new request send: Start.
$(function () {
    $('body').on('hidden.bs.modal', '.modal', function () {
        $(this).removeData('bs.modal');
    });
});
//Model empty when new request send: End.    

$(window).on("load", function (event) {
    hideLoader();
});  



//For Safari Tasks: #891: Start.
$(window).bind("pageshow", function (event) {
    if (event.originalEvent.persisted) {
        window.location.reload()
    }
});
//For Safari Tasks: #891: End.