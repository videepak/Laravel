function load_data() {
    let range = $("#reportrange").find("span").text().split('-');
    let name = $("#name").val(); 
    let property = $("#property").val(); 


    $('#historicalTable').DataTable({
        dom: 'lBfrtip',
        buttons: [
            { 
                extend: 'excelHtml5',
                title: `Clock-in/out(${range})`,
                exportOptions: {
                    format: {
                        body: function ( data, row, column, node ) {
                            if(column === 0) {
                                data = data.replace(/<br\s*\/?>/ig, "\r\n");
                                data = data.replace(/<.*?>/g, "");
                                data = data.replace("&amp;", "&");
                                data = data.replace("&nbsp;", "");
                                data = data.replace("&nbsp;", "");
                                data = data.replace( /[$,]/g, '' );
                                data = data.replace(/<[^>]+>/g, '');
                                return data;
                            } else {
                                return data;
                            }                            
                        },
                        header: function ( data, column, row ) {
                            if (column === 0 || column === 1) {
                                return data.substring(data.indexOf("value")+9,data.indexOf("</option"));
                            } else {
                                return data;
                            }                        }
                    }
                },
            },
        ],
	    "bPaginate": true,
        "ordering": false,
        "bLengthChange": true,
        "pageLength": 25,
        "bFilter": false,
        "bInfo": true,
        language: {
            search: "",
            searchPlaceholder: "Name, Cities, Address.",
        },
        "bAutoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": BaseUrl + "/report/getHistoricalReport",
            "type": "POST",
            "data":{ 
              _token: $('meta[name="csrf_token"]').attr('content'), 
              startTime: range[0], 
              endTime: range[1], 			
              property: property,
              name: name
            }
        },
       
        "columns": [
            //{ "data": "sNo" },            
            { "data": "name" },            
            { "data": "propertyName" },            
            { "data": "checkin" },            
            { "data": "checkout" },            
            { "data": "serviceduration" },            
            { "data": "violation" },            
            { "data": "checkpoints" },            
            { "data": "taskcompleted" }
        ]    
    });
}

$(document).ready(function () {  
    
    load_data();
    
    $('.excel-option').click(function() {
        $('.buttons-excel').trigger('click');
    }); 
});

$(window).ready(function() {

    $('#property').change(function() { 
        $('#historicalTable').DataTable().destroy();
        load_data();
    });

    $('#name').change(function() { 
        $('#historicalTable').DataTable().destroy();
        load_data();
    });
});

