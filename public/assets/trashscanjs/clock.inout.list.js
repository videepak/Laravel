function load_data() {
    
    let range = $("#reportrange").find("span").text().split('-');
    let reporting = $("#reporting").val(); 
    let name = $("#name").val(); 

    $('#example').DataTable({
        dom: 'lBfrtip',
        buttons: [
            { 
                extend: 'excelHtml5',
                title: `Clock-in/out(${range})`,
                exportOptions: {
                    format: {
                        header: function ( data, column, row ) {
                            return data.substring(data.indexOf("value")+9,data.indexOf("</option"));
                        }
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
            "url": BaseUrl + "/clockinout/getReport",
            "type": "POST",
            "data":{ 
              _token: $('meta[name="csrf_token"]').attr('content'), 
              startTime: range[0], 
              endTime: range[1], 			
              reporting: reporting,
              name: name
            }
        },
        drawCallback: function(settings) {
            $('.textare').editable({
                type: 'textarea',
                url: `${BaseUrl}/clockinout/resetDateTime`,
                title: 'Enter comments',
                rows: 5,
                params: {
                    _token: $('meta[name="csrf_token"]').attr('content')
                },
                placement: function (context, source) {
                    var popupWidth = 336;
                    if(($(window).scrollLeft() + popupWidth) > $(source).offset().left){
                      return "right";
                    } else {
                      return "left";
                    }
                  },
                  success: function(response) {
                    new PNotify({
                        title: "Clock In/out",
                        text: response.message,
                        type: response.alert,
                        styling: 'bootstrap3'
                    });
                }
            });

            $('.time').editable({
                type: 'combodate',
                url: BaseUrl + "/clockinout/resetDateTime",
                format : 'YYYY-MM-DD h:mm A',
                viewformat : 'MM-DD-YYYY h:mm A',
                template : 'MMM - D - YYYY h : mm A',
                params: {
                    _token: $('meta[name="csrf_token"]').attr('content')
                },
                combodate: {
                    minYear: 2018,
                    maxYear: new Date().getFullYear(),
                    minuteStep: 1
                },
                placement: function (context, source) {
                  var popupWidth = 336;
                  if(($(window).scrollLeft() + popupWidth) > $(source).offset().left){
                    return "right";
                  } else {
                    return "left";
                  }
                },
                success: function(response) {
                    new PNotify({
                        title: "Clock In/out",
                        text: response.message,
                        type: response.alert,
                        styling: 'bootstrap3'
                    });
                }
            });
        },
        "columns": [
            { "data": "user_id" },
            { "data": "name" },            
            { "data": "reportingname" },      
            { "data": "clockin" },
            { "data": "clockout" },
            { "data": "reason" }
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

    $('#single_cal4').change(function() { 
      $('#example').DataTable().destroy();
      load_data();
    });

    $('#reporting').change(function() { 
        $('#example').DataTable().destroy();
        load_data();
    });

    $('#name').change(function() { 
        $('#example').DataTable().destroy();
        load_data();
    });
});

