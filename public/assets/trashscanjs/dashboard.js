function loadTable() {
    let range = $("#report-daliy").find("span").text().split('-');
    
    $('#example').DataTable({
        dom: 'lBfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [ 0, 1, 2, 3, 4 ]
                }
            },
        ],  
        "bPaginate": true,
        "ordering": false,
        "bLengthChange": false,
        "pageLength": 5,
        "bFilter": true,
        "bInfo": true,
        language: {
        search: "",
                searchPlaceholder: "Search By Property",
                // paginate: {
                // next: '«',
                //         previous: '»'
                // },
        },
        "bAutoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
        "url": BaseUrl + "/dashboard/daliy-reports",
            "type": "POST",
            "data":{ 
              _token: $('meta[name="csrf_token"]').attr('content'), 
              id: $('.filter-search').val(),
              startTime: range[0], 
              endTime: range[1], 	
            }
        },
        "fnInitComplete": function (oSettings, json) {
            $.ajax({
                type: 'POST',
                url: BaseUrl + '/dashboard/daliy-status',
                data: {
                    _token: $('meta[name="csrf_token"]').attr('content'),
                    id: $(this).val(),
                    isUpdate: 0
                },
                success: function (data) {
                    $('#multiple-checkboxes').multiselect('select', data.checked);
                    $('#example').DataTable().columns(data.checked).visible(true);        
                    $('#example').DataTable().columns(data.unchecked).visible(false);
                }
            });
        },
        "columns": [
            { "data": "user_id" },
            { "data": "property" },
            { "data": "pickup_completed" },
            { "data": "active_units" },
            { "data": "route_checkpoints_scanned" },
            { "data": "checkpoints_by_property" },
            { "data": "building_walk_throughs"},
            { "data": "active_building"},
            { "data": "checkinout_duration"},
            { "data": "total_tasks_completed"},
            { "data": "total_tasks"},
            { "data": "missed_property_checkouts"}
        ]
   });
}
 
$(document).ready(function () {
    loadTable();
    $('#multiple-checkboxes').multiselect({
        //nonSelectedText: 'Customize View',
        //maxHeight: 150,
        includeSelectAllOption: false,
        allSelectedText: 'All',
        deselectAll: true,
        buttonWidth: 230,
        numberDisplayed: 0,
        delimiterText: ', ',
        buttonText: function(options, select) {
            return 'Customize';
        },
        onDropdownShow: function(select, container) {
            var selectedOptions = $('#multiple-checkboxes option:selected');

            if (selectedOptions.length > 4) {
                // Disable all other checkboxes.
                var nonSelectedOptions = $('#multiple-checkboxes option').filter(function() {
                    return !$(this).is(':selected');
                });

                nonSelectedOptions.each(function() {
                    var input = $('input[value="' + $(this).val() + '"]');
                    input.prop('disabled', true);
                    input.parent('.multiselect-option').addClass('disabled');
                });
            }
        },
        onChange: function(option, checked) {
            // Get selected options.
            var selectedOptions = $('#multiple-checkboxes option:selected');
            let i = true;

            if (selectedOptions.length > 4) {
                // Disable all other checkboxes.
                i = false;
                var nonSelectedOptions = $('#multiple-checkboxes option').filter(function() {
                    return !$(this).is(':selected');
                });

                selectedOptions.each(function() {
                    var input = $('input[value="' + $(this).val() + '"]');
                    input.prop('disabled', false);
                    input.parent('.multiselect-option').addClass('disabled');
                });

                nonSelectedOptions.each(function() {
                    var input = $('input[value="' + $(this).val() + '"]');
                    input.prop('disabled', true);
                    input.parent('.multiselect-option').addClass('disabled');
                });
            } else {
                // Enable all checkboxes.
                $('#multiple-checkboxes option').each(function() {
                    var input = $('input[value="' + $(this).val() + '"]');
                    input.prop('disabled', false);
                    input.parent('.multiselect-option').addClass('disabled');
                });
            }

            // if (selectedOptions.length < 5) {
            //     selectedOptions.each(function() {
            //         var input = $('input[value="' + $(this).val() + '"]');
            //         input.prop('disabled', true);
            //         input.parent('.multiselect-option').addClass('disabled');
            //     });
            // } else if (i) {
            //     // Enable all checkboxes.
            //     $('#multiple-checkboxes option').each(function() {
            //         var input = $('input[value="' + $(this).val() + '"]');
            //         input.prop('disabled', false);
            //         input.parent('.multiselect-option').addClass('disabled');
            //     });
            // }
        }
    });

    $('.toggle-vis').on('change', function (e) {
        e.preventDefault();
        $("#chart_div").empty();
        // if ($(this).val().length < 4 || $(this).val().length > 5) {
        //     new PNotify({
        //         title: "Daliy Report",
        //         text: "You can select only 4 or 5 checkboxes.",
        //         type: "danger",
        //         styling: 'bootstrap3'
        //     });
        //     return;
        // }
       
        $.ajax({
            type: 'POST',
            url: BaseUrl + '/dashboard/daliy-status',
            data: {
                _token: $('meta[name="csrf_token"]').attr('content'),
                id: $(this).val(),
                isUpdate: 1
            },
            success: function (data) {
                //Hide the checked columns.
                $('#example').DataTable().columns(data.unchecked).visible(false);
                // Show the unchecked columns.
                $('#example').DataTable().columns(data.checked).visible(true);
                $('.daliy-report').css('display', 'none');
            }
        });
    }
);

    $(document).on('click', '#propertyId', function () {
        let range = $("#report-daliy").find("span").text().split('-');
        $.ajax({
            type: 'POST',
            url: BaseUrl + '/dashboard/daliy-report-remote',
            data: {
                _token: $('meta[name="csrf_token"]').attr('content'),
                id: $(this).data('id'),
                startTime: range[0],
                endTime: range[1],
            },
            beforeSend: function () {
                $('#chart_div').html(`<div class="row" style='margin: 0% 0% 3% -13%;'><i class="fa fa-spinner fa-spin" style="font-size:24px"></i></div>`);
            },
            success: function (data) {
                $("#chart_div").html(data);
                $('.daliy-report').css('display', 'block');
                $(this).closest('tr').css('background', 'aliceblue');
            }
        });
    });
});