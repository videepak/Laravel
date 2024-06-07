$(document).ready(function () {
    let range = $("#reportrange").find("span").text().split('-');
  
    $('#task').DataTable({
        dom: 'lBfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [ 0, 1, 2, 3, 4, 5, 6 ]
                }
            },
        ],
	    "bPaginate": true,
        "ordering": false,
        "bLengthChange": true,
        "pageLength": 25,
        "bFilter": true,
        "bInfo": true,
        language: {
        search: "",
                searchPlaceholder: "Search",
                // paginate: {
                // next: '«',
                //         previous: '»'
                // },
        },
        "bAutoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
        "url": BaseUrl + "/tasks/get-task",
            "type": "POST",
            "data":{ 
              _token: $('meta[name="csrf_token"]').attr('content'),
              id: $('.filter-search').val()
            }
        },
         "columns": [
            { "data": "s_no" },
            { "data": "name" },            
            { "data": "start" },
            { "data": "user" },
            { "data": "photo" },
            { "data": "notify" },
            { "data": "property" },
            { "data": "action" }		
        ]    
     });

     $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $("#violatio-template").attr('action', BaseUrl + "/tasks");
        $(".modal-title").text("Add Task");
    });

    $('.excel-option').click(function() {
        $('.buttons-excel').trigger('click');
    });

    $('#task-select').select2();

    var token = $('meta[name="csrf_token"]').attr('content');

    $(document).on("click", ".get-detail", function () {
        let id = $(this).data('id');
        $.ajax({
            url: BaseUrl + '/tasks/task/detail/' + id,
            type: "GET",
            beforeSend: function () {
                showLoader();
            },
            success: function (data) {
                hideLoader();
                if (data.result) {
                    $("#name").val(data.detail.name);
                    
                    if (data.detail.is_photo == 1) {
                        $('#is-photo').trigger('click');
                    }

                    if (data.detail.notify_property_manager == 1) {
                        $('#notify').trigger('click');
                    }                    

                    if (data.detail.frequency == 1) {
                        $(':radio[name=frequency][value=1]').iCheck('check');
                    }
                    
                    if (data.detail.frequency == 2) {
                        $(':radio[name=frequency][value=2]').iCheck('check');
                    }
                    
                    if (data.detail.frequency == 3) {
                        $(':radio[name=frequency][value=3]').iCheck('check');
                    }

                    if (!jQuery.isEmptyObject(data.detail.property)) {
                        $("#task-select").val([data.detail.property[0].id]).trigger('change');
                    } else {
                        $("#task-select").val(['']).trigger('change');
                    }

                    $("#description").val(data.detail.description);
                    
                    $("#start").val(`${data.detail.start_date}-${data.detail.end_date}`);
                    
                    $(".modal-title").text("Edit Task");
                    
                    $("#task-form").attr('action', BaseUrl + '/tasks/update-task/' + id);
                    
                    setTimeout(
                        function() { 
                            $("#add-template").modal("show"); 
                        }, 
                        1500
                    );
                }
            }
        });
    });

    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $("#task-form").attr('action', BaseUrl + "/tasks");
        $(".modal-title").text("Add Template");
    });
});