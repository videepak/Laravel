function loadTable() 
{
    var building = $("#buildList").val(); 
    $('#route-point').DataTable(
        {
        "bPaginate":true,
        "ordering": false,
        "bLengthChange": true,
        "pageLength": 10,
        "bFilter": true,
        "bInfo": true,
        "bAutoWidth": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": BaseUrl + '/routecheck-point/get-list',
            "type": "POST",
            "data":{ 
                _token: $('meta[name="csrf_token"]').attr('content'), 
                building : building,
                propertyId: propertyId,
            }
        },
        drawCallback: function(settings) {
            $('.change-status')
                .editable({
                    params: {
                        _token: $('meta[name="csrf_token"]').attr('content')
                    },
                    success: function(response, newValue) {
                        //Todo: Pnotify not working
                        new PNotify({
                            title: "Route Checkpoint",
                            text: "Successfully updated.",
                            type: "success",
                            styling: 'bootstrap3'
                        });
                    }
                })
        },
        "columns": [
            { "data": "id" },
            { "data": "barcode" },
            { "data": "property" },
           // { "data": "description" },
           // { "data": "mandatory"},
            { "data": "action"}		
        ]
    });
}

$(document).ready(function () {  
    loadTable();

    $(document).on('click', '.makeUnit', function() {
        $.post(BaseUrl + '/routecheck-point/make-checkpoint', {
            _token: $('meta[name="csrf_token"]').attr('content'),
            id: $(this).attr('data-ids'),
            status: $(this).attr('data-status'),
        }, function (data, status) {
            new PNotify({
                title: "Barcode",
                text: 'Unit made successfully.',
                type: 'success',
                styling: 'bootstrap3'
            });
        });
        $('#route-point').DataTable().destroy();
        loadTable();
    });
    
    $(document).on('click', '.create-route', function(e) {
        e.preventDefault();
        var name = $('#name').val();
        var addressOne = $('#addressOne').val();
        var addressTwo = $('#addressTwo').val();
        var description = $('#description').val();
        var buildingId = $('#buildingId').val();
        var isRequired = $('#isRequired').is(":checked") ? 1 : 0;
        
        if(name == "" || addressOne == "") {
            new PNotify({
                title: "Route Check Point",
                text: "Name and Address1 fields are required.",
                type: "danger",
                styling: 'bootstrap3'
            });

            return false;
        }

        $.ajax({
            type: "POST",
            url: BaseUrl + '/routecheck-point',
            data: {
                _token: $('meta[name="csrf_token"]').attr('content'), 
                name: name,
                addressOne: addressOne,
                addressTwo: addressTwo,
                description: description,
                buildingId: buildingId,
                isRequired: isRequired,
                propertyId: propertyId,
            },
            success: function( msg ) {

                new PNotify({
                    title: "Route Check Point",
                    text: "Successfully added route check point.",
                    type: "success",
                    styling: 'bootstrap3'
                });

                $('#route-point').DataTable().destroy();
                loadTable();
                $('#routeCheckPoint').modal('hide')
            }
        });
    });

    $(document).on('click', '.print', function() {

        var propertyId = $(this).data('id');

        $.ajax({
            type: 'post',
            url: BaseUrl + '/routecheck-point/print-barcodes/',
            data: {
                _token: $('meta[name="csrf_token"]').attr('content'), 
                propertyId: propertyId
            },
            beforeSend: function(){
                showLoader();
            },
            success: function( data ) {
                
                $("#outprint").html(data);
                
                var DocumentContainer = document.getElementById('outprint');
                var WindowObject = window.open('', "PrintWindow", "width=1000,height=650,top=50,left=50,toolbars=no,scrollbars=yes,status=no,resizable=yes");
                WindowObject.document.writeln(DocumentContainer.innerHTML);
                WindowObject.document.close();
                WindowObject.focus();
                WindowObject.print();
                
                setTimeout(
                    function() {
                        WindowObject.close(); 
                    }, 3000
                );

                //$("#outprint").html(data);
                //$("#outprint").print();
                //window.open(data, '_blank');

                hideLoader();
            }
        });
    });

    $(document).on('click', '.edit-route', function() {
        
        var routeId = $(this).data('id');

        $.ajax({
            type: 'GET',
            url: BaseUrl + '/routecheck-point/'+routeId+'',
            beforeSend: function(){
                showLoader();
            },
            success: function( data ) {
                hideLoader();
                if (data.result) {                    
                    
                    if(data.detail.is_required) {
                        $('.icheckbox_flat-green').addClass('checked');
                        $('.icheckbox_flat-green input[type="checkbox"]').prop('checked', true)
                    } else {
                        $('.icheckbox_flat-green').removeClass('checked');
                        $('.icheckbox_flat-green input[type="checkbox"]').prop('checked', false)
                    }

                    $('#name').val(data.detail.name);
                    $('#addressOne').val(data.detail.address1);
                    $('#addressTwo').val(data.detail.address2);
                    $('#description').val(data.detail.description);
                    
                    $('#buildingId option[value='+data.detail.building_id+']').attr('selected','selected');

                    $('.submit-btn').removeClass('create-route').addClass('update-route');
                    $('.submit-btn').attr('data-value', data.detail.id);
                    $(".modal-title").text("Edit Route Check Point");
                    $("#routeCheckPoint").modal("show");
                }
            }
        });
    });

    $(document).on('click', '.update-route', function(e) {
        
        e.preventDefault();

        var name = $('#name').val();
        $(this).removeData('value')
        var id = $(this).data('value'); 
        var addressOne = $('#addressOne').val();
        var addressTwo = $('#addressTwo').val();
        var description = $('#description').val();
        var buildingId = $('#buildingId').val();
        var isRequired = $('#isRequired').is(":checked") ? 1 : 0;

        if(name == "" || addressOne == "") {

            new PNotify({
                title: "Route Check Point",
                text: "Name and Address1 fields are required.",
                type: "danger",
                styling: 'bootstrap3'
            });

            return false;
        }

        $.ajax({
            type: "POST",
            url: BaseUrl + `/routecheck-point/${id}`,
            data: {
                _token: $('meta[name="csrf_token"]').attr('content'), 
                _method: 'PUT',
                name: name, 
                addressOne: addressOne, 
                addressTwo: addressTwo,
                description: description,
                buildingId: buildingId,
                isRequired: isRequired,
            },
            beforeSend: function(){
                showLoader();
            },
            success: function( msg ) {
                hideLoader();
                new PNotify({
                    title: "Route Check Point",
                    text: "Successfully edited route check point.",
                    type: "success",
                    styling: 'bootstrap3'
                });

                $('#route-point').DataTable().destroy();
                loadTable();
                $('#routeCheckPoint').modal('hide')
            }
        });
    });

    $("#route-point").on('click','.user-trash', function(e) {
        e.preventDefault();

        if(!confirm("Are you sure, you want delete!")) {
            return false;
        }

        var trashId = $(this).data('id');

        $.ajax({
            type: 'POST',
            url: BaseUrl + '/routecheck-point/'+trashId+'',
            data: {
                _token: $('meta[name="csrf_token"]').attr('content'), 
                _method: 'DELETE',
            },
            beforeSend: function(){
                showLoader();
            },
            success: function( msg ) {
                hideLoader();
                new PNotify({
                        title: "Route Check Point",
                        text: "Successfully deleted route check point.",
                        type: "success",
                        styling: 'bootstrap3'
                    });

                $('#route-point').DataTable().destroy();
                loadTable();
            }
        });
    });

     //Reset modal when close.
     $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $('.submit-btn').removeAttr('data-value');
        $(".modal-title").text("Add Route Check Point");
        $('.submit-btn').removeClass('update-route').addClass('create-route');
    });
});

$('#buildList').change(function() { 
    $('#route-point').DataTable().destroy();
    loadTable();
});
