$(document).ready(function () {
    $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
        localStorage.setItem('activeTab', $(e.target).attr('href'));
    });
    var activeTab = localStorage.getItem('activeTab');
    if(activeTab){
        $('#myTab a[href="' + activeTab + '"]').tab('show');
    }
   
    if($('.onoff').is(":checked") || $('.onoffExcel').is(":checked") || $('.onoffclockIn').is(":checked") || $('.onoffViolation').is(":checked")) {
        $('#show-date').html(`<b>Next Service Report Date :</b> ${$('.day:checked').attr('data-date')}`);    
        $('#show-date').css('display', 'block');
    } else {
        $('#show-date').css('display', 'none');
    }

    $('.onoff').change(function() {

        //$('.frequency').iCheck('check');
        
        if (!$('.onoff').is(":checked") && !$('.onoffExcel').is(":checked")  && !$('.onoffclockIn').is(":checked") && !$('.onoffViolation').is(":checked")) {
            $('.day').iCheck('disable');
            $('#show-date').css('display', 'none');
        } else {    
            $('.day').iCheck('enable');
            $('#show-date').css('display', 'block');
        }
    });

    $('.day').on('ifClicked', function (event) {
        $('#show-date').html(`<b>Next Service Report Date :</b> ${$(this).attr('data-date')}`);
    });
	
    $('#myDatepicker3').datetimepicker({
           format: 'hh:mm A',
           //ignoreReadonly: true,
           stepping: 30,
           //minDate: moment({H:6}),
	   //defaultDate: moment({H:6}),
           //maxDate: moment({H:23}),
    });

    $('#myDatepicker4').datetimepicker({
        format: 'hh:mm A',
        //ignoreReadonly: true,
        stepping: 30, 
        //defaultDate: moment({H:6}),
    });
    
	 
    $('#property-id-select2').select2();
});

