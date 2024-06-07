function load_data() {
 
    let status = $("#status").val();
    let username = $("#usename").val();
    let reason = $("#rule").val(); 
    let action = $("#action").val();	
    let property = $("#properties").val();	
    let range = $("#reportrange").find("span").text().split('-');

    $('#violations-list').DataTable({
        responsive: true,
        dom: 'lBfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [ 1, 2, 3, 4, 5, 6, 7, 8, 9],
                    format: {
                        body: function ( data, row, column, node ) {
                            if(column === 2 || column === 3 || column === 4 || column === 5) {
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
                            if (column === 1 || column === 2 || column === 3 || column === 4 || column === 5) {
                                return data.substring(data.indexOf("value")+9,data.indexOf("</option"));
                            } else {
                                return data;
                            }
                        }
                    }
                }, 
                customize: function(xlsx) {
                    $('col', xlsx.xl.worksheets['sheet1.xml']).each(function () {
                        $(this).attr('width', 20);
                    });       
                }
            },
        ],
        "bPaginate": true,
        "ordering": false,
        "bLengthChange": true,
        "pageLength": 10,
        "bFilter": true,
        "bInfo": true,
        "searching": true,
	    "language": {
            search: "",
            searchPlaceholder: "Search...",
        },
        'columnDefs': [
            {
                "targets": [7, 8],
                "visible": false,
                "searchable": false,
            },
        ],
        "autoWidth": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": BaseUrl + "/violation/getViolations/",
            "type": "post",
            "data": {
                _token: $('meta[name="csrf_token"]').attr('content'),
                property: property,                
                status: status,
                username: username,
                action: action,
		        reason: reason,
                startTime: range[0], 
                endTime: range[1], 			
            }
        },
        drawCallback: function(settings) {

            $('.reason-change')
                .editable({
                    source: reasonEdit,
                    params: {
                       _token: $('meta[name="csrf_token"]').attr('content')
                    },
                    success: function(response, newValue) {
                        //Todo: Pnotify not working
                         if (response.status) {
                            $(function() {
                                new PNotify({
                                    title: "Violation",
                                    text: "This violation is rolled back by the user.",
                                    type: "danger",
                                    styling: 'bootstrap3'
                                });
                            });
                        }
                    }
                });
	    
    $('.excel-option').click(function() {
        $('.buttons-excel').trigger('click');
    }); 

             $('.action-change')
                .editable({
                    source: actionEdit,
                    params: {
                       _token: $('meta[name="csrf_token"]').attr('content')
                    },
                    success: function(response, newValue) {
                        
                        //Todo: Pnotify not working
                         if (response.status) {
                            $(function() {
                                new PNotify({
                                    title: "Violation",
                                    text: "This violation is rolled back by the user.",
                                    type: "danger",
                                    styling: 'bootstrap3'
                                });
                            });
                        }
                    }
                })	
		
		    $('.statu-chane')
                .editable({
                    source: vioStatus,
                    params: {
                       _token: $('meta[name="csrf_token"]').attr('content')
                    },
                    success: function(response, newValue) {
                        //Todo: Pnotify not working
                         if (response.status) {
                            $(function() {
                                new PNotify({
                                    title: "Violation",
                                    text: "This violation is rolled back by the user.",
                                    type: "danger",
                                    styling: 'bootstrap3'
                                });
                            });
                        }
                    }
                })	


                
		//Disable all checkbox if property selectbox value is empty:Start.
		    var propertyId = $("#properties").val();	

		    if (propertyId === undefined || propertyId === null || propertyId === '') { 
			
			//$(".bulk-actions").css('display', 'none');
			$('.datatable-checkbox').attr('disabled', 'disabled');
			$('.icheckbox_flat-green').removeClass('disabled');
		    } else {
			
			if(localStorage.getItem('checkboxId') != null) {
				$.each(localStorage.getItem('checkboxId').split(','), function (index, value) {
				    $(`:checkbox[value = ${value}]`).prop("checked", true);
				    $(`:checkbox[value = ${value}]`).parent("div").addClass('checked');
	                   });
			}

			//$(".bulk-actions").css('display', 'block');
			$('.datatable-checkbox').removeAttr('disabled');
			$('.icheckbox_flat-green').addClass('disabled');
		    }
		//Disable all checkbox if property selectbox value is empty:End.

		//If property id not match with current property id then., we will clear local storage: Start
		//if (localStorage.getItem("propertyId") != propertyId) {
		  //  localStorage.clear();
		//}
	        //If property id not match with current property id then., we will clear local storage: End
        },
        "columns": [
	        {
                "data": "checkBox"
            },
            {
                "data": "name"
            },
            {
                "data": "property"
            },
            {
                "data": "rule"
            },
            {
                "data": "action"
            },            
            {
                "data": "status"
            },
            {
                "data": "detail"
            },	
            {
                "data": "specail"
            },
            {
                "data": "building"
            },
	        {
                "data": "created_at"
            },		
            {
                "data": "icon"
            }
        ]
    });
}

$(document).ready(function () {
	
   load_data();
    
    $(document).on(
        'change',
        '.filter',
        function() {
            //alert($('select[name="violations-list_length"]').val());
            localStorage.setItem('entries', $('select[name="violations-list_length"]').val());
            //alert(localStorage.getItem('entries'));   
            $('#violations-list').DataTable().destroy();
            load_data();

            setTimeout(
                function () {
                    $('select[name^="example_length"] option[value="' + localStorage.getItem('entries') + '"]').attr("selected","selected").trigger("change");
                    localStorage.setItem('entries', '');
                },
                250
            );
	
	//If property id not match with current property id then., we will clear local storage: Start
	   var propertyId = $("#properties").val();		
	   if (localStorage.getItem("propertyId") != propertyId) {
	 	localStorage.clear();
	   }
       //If property id not match with current property id then., we will clear local storage: End

	
    });
	
    $("#get-template").on('change', function () {

        //Separated body and subject with the help of "|||":Start
        if ($(this).val() != "") {
            $('#to-subject').val($(this).val().split('|||')[1]);
            $('#to-body').val($(this).val().split('|||')[0]);
        } else {
            $('#to-subject').val(templateSubject);
            $('#to-body').val(defaultTemplate);
        }
        //Separated body and subject with the help of "|||":End
    });

    $(".tagsemail").tagsInput({
        width: "auto",
        defaultText: 'Add Email',
        onAddTag: function (val) {

            var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

            if (!regex.test(val)) {
                $(this).removeTag(val);
            }
        }
    });


    $('#send-mail-violation').click(function () {

        var token = $('meta[name="csrf_token"]').attr('content');
        var id = $(this).data('id');
        var toMail = $('#tagsemail').val();
        var ccMail = $('#cc-mail').val();
        var subject = $('#to-subject').val();
        var body = $('#to-body').val();
        var violationId = $('#violation-id').val();
        var isCheck = $('#is-check:checkbox:checked').length > 0;

        $.ajax({
            url: BaseUrl + '/violation-send-mail',
            type: "POST",
            data: {
                _token: token,
                id: id,
                toEmail: toMail,
                ccEmail: ccMail,
                violationId: violationId,
                isCheck: isCheck,
                subject: subject,
                body: body
            },
            beforeSend: function () {
                showLoader();
            },
            success: function (data) {
                hideLoader();
                if (data.result) { 
                    new PNotify({
                        title: "Violation",
                        text: "E-mail sent successfully.",
                        type: "success",
                        styling: 'bootstrap3'
                    });
                    $('#send-mail-popup').modal('hide');
                } else {
                    $('#send-mail-message').html(data);
                }
            }
        });
    });

    $(document).on('click', ".send-mail", function () {

        //Get property manager email for send email popup (To email field):Start
        var propertyId = $('#filter-property-id').val();

        let single = $(this).data('id');
        var violationId = typeof single === "undefined" ? localStorage.getItem('checkboxId').split(',') : [single];
        let unitdata = $(this).data('unitid');

        $.post(BaseUrl + "/get/manager/email/violation/", {
            _token: $('meta[name="csrf_token"]').attr('content'),
            propertyId: propertyId,
            violtionId: violationId,
            unitdata: unitdata,
        }, function (data) {
            //$('#tagsemail').tagsInput({pattern: /^[0-9]*$/});
            //$("#tagsemail").importTags(data.result);
            $("#tagsemail").importTags(data.unit);
            $("#cc-mail").importTags(data.result);
        });
        //Get property manager email for send email popup (To email field):End

        $('#violation-id').val(violationId);
        $('#send-mail-popup').modal('show');
    });

    //Resident email send start
    $(document).on('click', ".resident_mail_violation", function () {

        let unitdata = $(this).attr('data-unitid');
        var email = $(this).data('email');
        
        var optiondata = '';
            $.ajax({
                url: BaseUrl + "/get-template-violation",
                type: "POST",
                data: {
                    _token: $('meta[name="csrf_token"]').attr('content'),
                    id: unitdata,
                },
                success: function (data) {
                    if(data){
                        $.each(data, function() {
                            $.each(this, function(k, v) {
                                optiondata += $('#violation-template-data')
                                .append(`<option value="${v.id}">${v.name}</option>`);
                            });
                          });
                    }
                        $("#viotagsemail").importTags(email);
                   },
                error: function(response) {
                    console.log(response);
                }
            });
        $('#violation-mail-popup').modal('show');
    });

    $('.violation-template-data').on('change' , function () {
        var templateId = this.value;
        //alert(templateId);
        $.ajax({
            url: BaseUrl + "/fetch-template",
            type: "POST",
            data: {
                _token: $('meta[name="csrf_token"]').attr('content'),
                id: templateId,
            },
            success: function(data) {
                $('.violation-subject').val(data.subject);
                $('.violation-body').val(data.content);
            },
            error: function(response) {
                console.log(response);
            }
        });
    });
    $(document).on('click', "#send-residentmail-violation", function () {
        var email = $('#viotagsemail').val();
        var subject = $('#violation-subject').val();
        var body = $('#violation-body').val();
        let cc = $('#violation-cc-mail').val();
        var token = $('meta[name="csrf_token"]').attr('content');
         $.ajax({
                url: BaseUrl + "/residentEmailViolation",
                type: "POST",
                data: {
                    _token: token,
                    subject: subject,
                    body: body,
                    email: email,
                    cc: cc,
                },
                beforeSend: function () {
                    showLoader();
                },
                success: function (data) {
                    hideLoader();
                    if (data.result) { 
                        new PNotify({
                            title: "Violation",
                            text: "E-mail sent successfully.",
                            type: "success",
                            styling: 'bootstrap3'
                        });
                        $('#violation-mail-popup').modal('hide');
                    } 
                },
                error: function(response) {
                    console.log(response);
                }
            });
    });
    $(".viotagsemail").tagsInput({
        width: "auto",
        defaultText: 'Add Email',
        onAddTag: function (val) {
    
            var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    
            if (!regex.test(val)) {
                $(this).removeTag(val);
            }
        }
    });
     //Resident email send end
    //Print single and multiple violation:Start.
    $(document).on('click', ".print-view", function () { 

        showLoader();

        let bulkCheck = localStorage.getItem('checkboxId');

        let single = $(this).data('id');
        var id = typeof single === "undefined" ? bulkCheck : single;

        $.ajax({
            url: BaseUrl + '/download/violation/pdf',
            type: "POST",
            data: {_token: $('meta[name="csrf_token"]').attr('content'), id: id}
        }).done(function (data) {

            if (data.result == false) {
                // $(function () {
                //     new PNotify({
                //         title: "Violation",
                //         text: "This violation is rolled back by the user.",
                //         type: "danger",
                //         styling: 'bootstrap3'
                //     });
                // });
                // setTimeout(function () {
                //     window.location = BaseUrl + $(location).attr('pathname');
                // }, 2000);
            } else {
                window.open(BaseUrl + '/uploads/pdf/' + data.data);
            }
            hideLoader();
        });
    });
    //Print single and multiple violation:End.

    //Change multiple violation status:Start.
    $(document).on('change', ".multiple-status", function () {

        if ($(this).val() == "") {
            return false;
        }

        if (confirm("Are you sure you want to change status?")) {
            let bulkCheck = localStorage.getItem('checkboxId').split(',');
//            let bulkCheck = $("input[name='table_records']:checked").map(function () {
//                return $(this).val()
//            }).get();

            $.post(BaseUrl + '/change-violation-status', {
                _token: $('meta[name="csrf_token"]').attr('content'),
                id: bulkCheck,
                value: $(this).val()
            }, function (data, status) {
                setTimeout(function () {
                    window.location = BaseUrl + $(location).attr('pathname');
                }, 1000);
            });
        }
    });
    //Change multiple violation status:End.
    //Change single violation status:Start.
    $(document).on('change', ".change-role, .change-status", function () {

        if (confirm("Are you sure you want to change status?")) {
            $.post(BaseUrl + '/change-violation-status', {
                _token: $('meta[name="csrf_token"]').attr('content'),
                id: [$(this).data('id')],
                value: $(this).val()
            }, function (data, status) {

                if (data.status == false && data.message != "") {
                    new PNotify({
                        title: "Violation",
                        text: data.message,
                        type: danger,
                        styling: 'bootstrap3'
                    });
                }
                setTimeout(function () {
                    window.location = BaseUrl + $(location).attr('pathname');
                }, 2000);
            });
        } else {
            $('.' + $(this).attr('id') + ' option').removeAttr('selected');
            $('.' + $(this).attr('id') + ' option[value="' + $(this).data('preselect') + '"]').attr("selected", "selected");
        }
    });
    //Change single violation status:End.

    $('.get-image').click(function () {
        $('#popup-heading').text('Violation Image');
        $('.hide-footer').css('display', 'none');
        $('#myModal').modal('show');
    });

    $('#statustype, #filter-property-id').change(function () {

        var filterPropertyId = $('#filter-property-id').val();
        var statusType = $('#statustype').val();

        if (typeof statusType === "undefined" || statusType == "") {
            var statusType = 10;
        }

        

        if ($(this).val() != '' && $(this).val() >= 0) {
            localStorage.setItem("propertyId", $(this).val());
            window.location = BaseUrl + "/violation-filter-by/" + statusType + "/" + filterPropertyId;
        } else {
            window.location = BaseUrl + "/violation";
        }
    });

    //$('.datatable-checkbox').on('ifChanged', function (e) {
    $(document).on('change', "#check-all", function () {	
    	if ($(this).is(':checked')) {
           $("input[name='table_records']").prop('checked', true);       
 	} else {
	    localStorage.setItem('checkboxId', "");	
	   $("input[name='table_records']").prop('checked', false);	
        }
    });
    
    $(document).on('change', ".datatable-checkbox", function () {	
	
	var currentVal = $(this).val();

        if ($(this).is(':checked')) {
		
            let add = $("input[name='table_records']:checked").map(function () {
                return $(this).val()
            }).get();
	    
            let localStore = `${localStorage.getItem('checkboxId')},${add}`;

            //Remove comma from first and last postion:Start.
            let updatelocalStorage = localStore.replace(/^,|,$/g, '');
            //Remove comma from first and last postion:End.
            //Filter unique value:Start.
            let unique = updatelocalStorage.split(',').filter(function (itm, i, a) {
                return i == a.indexOf(itm);
            });
            //Filter unique value:End.

            localStorage.setItem('checkboxId', unique);
        } else if ($(this).is(':not(:checked)')) {	    

            let remove = jQuery.grep(localStorage.getItem('checkboxId').split(','), function (value) {
                if (value != "") {
                    return value != currentVal;
                }
            });

            localStorage.setItem('checkboxId', remove);
        }

        if(localStorage.getItem('checkboxId') == "" || localStorage.getItem('checkboxId') == "null") {
	        $(".bulk-actions").css('display', 'none');		
        } else {
	        $(".bulk-actions").css('display', 'block');	
        }

	    // alert(localStorage.getItem('checkboxId'));
        //alert($.isArray(localStorage.getItem('checkboxId').split(',')));
        //return;
    });
});

$(window).load(function () {

    //uncheck all checkbox when reload the page (For Mozila):Start
    $('.icheckbox_flat-green').removeClass('checked');
    $('.icheckbox_flat-green input[type="checkbox"]').prop('checked', false)
    //uncheck all checkbox when reload the page (For Mozila):End

    var propertyId = $("#properties").val();	

    if (propertyId != '') {
        $.each(localStorage.getItem('checkboxId').split(','), function (index, value) {
            $(`:checkbox[value = ${value}]`).prop("checked", true);
            $(`:checkbox[value = ${value}]`).parent("div").addClass('checked');
        });
    }

    var getCheckVal = $("input[name='table_records']:checked").map(function () {
        return $(this).val()
    }).get();

    if (getCheckVal != "" && propertyId != '') {
        //$(".violation-head").css('display', 'none');
    }
});

$(document).on("click", ".sub-popup", function () {	
    
    let comments = $("#comment").val();    
    let id = $("#violationId").val();    
    
    $.ajax({
        url: BaseUrl + '/violation/comment',
        type: "POST",
        data: {
            _token: $('meta[name="csrf_token"]').attr('content'),
            comments: comments,
            id: id,
        },
        beforeSend: function () {
            showLoader();
        },
        success: function (data) {
            hideLoader();
            
            if (!data.status) {
                new PNotify({
                    title: "Violation",
                    text: data.error,
                    type: "danger",
                    styling: 'bootstrap3'
                });
            } else {
                new PNotify({
                    title: "Violation",
                    text: "Comment Updated Successfully",
                    type: "success",
                    styling: 'bootstrap3'
                });
            }
        }
    });
});