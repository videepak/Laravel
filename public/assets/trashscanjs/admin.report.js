function load_data() {
    let subscriber = $('#subscriber').val();
    let reporType = $('#reporType').val();
    let startTime = $('#metric-date').val();

    if (subscriber == '') { 
        new PNotify({
            title: "Manage Reports",
            text: "Please select the subscriber.",
            type: "danger",
            styling: 'bootstrap3'
        });
        
        return;
     }

    if (reporType == 1) {
        URL = BaseUrl + '/admin/violation-report/';
        var colD = [
            {title: 'S.No', targets: 0},
            {title: 'Username', targets: 1},
            {title:  'Property', targets: 2},
            {title:  'Rule', targets: 3},
            {title:  'Action', targets: 4},
            {title:  'Status', targets: 5},
            {title:  'Details', targets: 6},
            {title:  'Special Notes', targets: 7},
            {title:  'Building', targets: 8},
            {title:  'No.of Image', targets: 9},
            {title:  'Created At', targets: 10}
        ];
        var col = [
            { data: "user_id" },
            { data: "username" },
            { data: "property" },
            { data: "rule" },
            { data: "action" },
            { data: "status" },
            { data: "detail" },
            { data: "special" },
            { data: "building" },
            { data: "imagecount" },
            { data: "created_at" }
        ]
    } else if (reporType == 2) {
        URL = BaseUrl + '/admin/clock-report/'
        var colD = [
            {title: 'S.No', targets: 0},
            {title: 'Name', targets: 1},
            {title:  'Reporting Manager', targets: 2},
            {title:  'Clockin', targets: 3},
            {title:  'Clockout', targets: 4},
            {title:  'Reason', targets: 5}
        ];

        var col = [
            { data: "user_id" },
            { data: "name" },
            { data: "reportingname" },
            { data: "clockin" },
            { data: "clockout" },
            { data: "reason" }
        ]
    } else if (reporType == 3) {
        URL = BaseUrl + '/admin/unit-report/'
        var colD = [
            //{title: 'S.No', targets: 0},
            {title: 'Address1', targets: 0},
            {title:  'Address2', targets: 1},
            {title:  'Unit Number', targets: 2},
            {title:  'Activation Date', targets: 3},
            {title:  'Property', targets: 4},
            {title:  'Building', targets: 5},
            {title:  'Floor', targets: 6},
            {title:  'Latitude', targets: 7},
            {title:  'Longitude', targets: 8},
            {title:  'Barcode', targets: 9},
            {title:  'Last Scan Date', targets: 10},
            {title:  'Units', targets: 11},
            {title:  'Created At', targets: 12},
            {title:  'Updated At', targets: 13},
            {title:  'Status', targets: 14},
        ];

        var col = [
            //{ data: "Sno" },
            { data: "Address1" },
            { data: "Address2" },
            { data: "unitNumber" },
            { data: "activationDate" },
            { data: "Property" },
            { data: "Building" },
            { data: "Floor" },
            { data: "Latitude" },
            { data: "Longitude" },
            { data: "Barcode" },
            { data: "lastScanDate" },
            { data: "Units" },
            { data: "CreatedAt" },
            { data: "UpdatedAt" },
            { data: "Status" }
        ]
    } else if (reporType == 4) {
        URL = BaseUrl + '/admin/service-report/'
        var colD = [
            {title: 'S.No', targets: 0},
            {title: 'Property', targets: 1},
            {title:  'Building Name', targets: 2},
            {title:  'Unit', targets: 3},
            {title:  'Scan Date', targets: 4},
            {title:  'Volume', targets: 5},
            {title:  'Activity', targets: 6},
            {title:  'Scan By', targets: 7}
        ];

        var col = [
            { data: "sNo" },
            { data: "property_name" },
            { data: "building" },
            { data: "unit" },
            { data: "updated_at" },
            { data: "type" },
            { data: "status" },
            { data: "employee_name" }
        ]
    }
    
    $('#reportTable').DataTable({
        "bPaginate": true,
        "ordering": false,
        "bLengthChange": true,
        "pageLength": 10,
        "bFilter": false,
        "bInfo": true,
        "searching": false,
        "bAutoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": URL,
            "type": "POST",
            "data":{ 
                _token: $('meta[name="csrf_token"]').attr('content'), 
                subscriber: subscriber,
                reporType: reporType,
                startTime: startTime
            },
        },
        "columnDefs": colD,
        "columns": col
    });
}

$(document).ready(function () {
    
    //load_data();

    //var token = $('meta[name="csrf_token"]').attr('content');

    $(document).on("change", ".seleteItem", function() {
        if ($.fn.DataTable.isDataTable( '#reportTable' ) ) {
            $('#reportTable').DataTable().destroy();
            $('#reportTable').empty();
        }
        load_data();
        // $.ajax({
        //     url: BaseUrl + '/admin/violation-report/',
        //     type: "POST",
        //     data:{ 
        //         _token: $('meta[name="csrf_token"]').attr('content'), 
        //         subscriber: subscriber,
        //         reporType: reporType,
        //         startTime: startTime
        //     },
        //     beforeSend: function () {
        //        // showLoader();
        //     },
        //     success: function (data) {
        //         //hideLoader();
        //         var my_columns = [];
        //         console.log(data.data);
        //         console.log(data);
        //         $.each( data.data, function( key, value ) {
        //             var my_item = {};
        //             my_item.data = key;
        //             my_item.title = key;
        //             my_columns.push(my_item);
        //         });
        //         console.log(my_columns);
        //         $('#reportTable').DataTable({
        //             data: data.data,
        //             columns: my_columns
        //         });
        //     }
        // });
    });
});