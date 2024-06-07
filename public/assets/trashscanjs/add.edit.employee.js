$(document).ready(function () {
	
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

