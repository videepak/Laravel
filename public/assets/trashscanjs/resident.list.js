$(document).ready(function () {
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
      "bLengthChange": true,
      "pageLength": 25,
      "bFilter": true,
      "bInfo": true,
      language: {
      search: "",
              searchPlaceholder: "Name, Email, Mobile",
              // paginate: {
              // next: '«',
              //         previous: '»'
              // },
      },
      "bAutoWidth": false,
      "processing": true,
      "serverSide": true,
      "ajax": {
      "url": BaseUrl + "/property-manager/get-resident",
          "type": "POST",
          "data":{ 
            _token: $('meta[name="csrf_token"]').attr('content'), 
            id: $('.filter-search').val()
          }
      },
       "columns": [
          { "data": "id" },
          { "data": "name" },
          { "data": "mobile" },
          { "data": "email" },
          { "data": "unit_id" },
          { "data": "violation" },
          { "data": "service" },
          { "data": "action"}		
      ]
    }
);

$('.makeaction').click(function () {
      $('#preloader').show();
    }
);
var table = $('#example').DataTable();
var currentEmail; 
var id;
var property;
var unit_id;
$('#example tbody').on( 'click', 'tr', function () {
    var rowData = table.row( this ).data();
    $.each($(rowData),function(key,value){
        currentEmail = value["email"];
        id = value["resident_id"];
        property = value["property_id"];
        unit_id = value["unit_data"];
        $("#resident-email").attr("data-property",property);
        $("#resident-email").attr("data-unit",unit_id);
        $("#resident-email").attr("data-id",id);
        $('#resident-email').val(currentEmail);
    });
    });
$('#send-mail-resident').click(function () {

    var token = $('meta[name="csrf_token"]').attr('content');
    var name = $('#template_name').val();
    var subject = $('#to-subject').val();
    var body = $('#to-body').val();
    var email = $('#residenttagsemail').val();
    var resident_id = $('#resident-email').attr("data-id");
    var property_id = $('#resident-email').attr("data-property");
    var unit_id = $('#resident-email').attr("data-unit");
    let cc = $('#cc-mail').val();
   
    if(subject.trim() == '' ){
        $('#to-subject').after("<span class='sendResident'>Please enter value</span>");
        return false;
    }else if(body.trim() == '' ){
        $('#to-body').after("<span class='sendResident'>Please enter value</span>");
        return false;
    }else{
    $.ajax({
        url: BaseUrl + '/property-manager/resident-send-mail',
        type: "POST",
        data: {
            _token: token,
            name: name,
            subject: subject,
            body: body,
            email: email,
            cc: cc,
            resident_id: resident_id,
            property_id: property_id,
            unit_id: unit_id,
        },
        beforeSend: function () {
            showLoader();
        },
        success: function (data) {
            hideLoader();
            if (data.result) { 
                new PNotify({
                    title: "Resident",
                    text: "E-mail sent successfully.",
                    type: "success",
                    styling: 'bootstrap3'
                });
                $('#resident-mail-popup').modal('hide');
            } else {
                $('#resident-mail-message').html(data);
            }
        }
    });
    }
});
$(".residenttagsemail").tagsInput({
    width: "auto",
    defaultText: 'Add Email',
    onAddTag: function (val) {

        var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

        if (!regex.test(val)) {
            $(this).removeTag(val);
        }
    }
});

    $('#email_history').DataTable({
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
      "bLengthChange": true,
      "pageLength": 25,
      "bFilter": true,
      "bInfo": true,
      language: {
      search: "",
              searchPlaceholder: "Subject, CC, Body",
      },
      "bAutoWidth": false,
      "processing": true,
      "serverSide": true,
      "ajax": {
      "url": BaseUrl + "/property-manager/get-email-history",
          "type": "POST",
          "data":{ 
            _token: $('meta[name="csrf_token"]').attr('content'), 
            id: $('.filter-search').val(),
            emailId: $('#email_history').attr('data-id'),
          }
      },
       "columns": [
          { "data": "id" },
          { "data": "unit_id"},
          { "data": "name"},
          { "data": "subject" },
          { "data": "cc" },
          { "data": "body" },	
      ]
    }
);

    $('#residentSubmit').change(function() {
            $('#uploadResident').submit();
    });
    $(document).on('click', ".resident-mail", function () {
          
            $('#to-subject').val('');
            $('#to-body').val('');

        var residentname = $(this).attr("data-name");
        var email = $('#resident-email').val();
        $("#residenttagsemail").importTags(email);
        var regards = $(this).attr("data-resident");
        var contact = $(this).attr("data-contact");
       
        $('#template-data').on('change' , function () {
            var templateId = this.value;
        
            $.ajax({
                url: BaseUrl + "/property-manager/get-template",
                type: "POST",
                data: {
                    _token: $('meta[name="csrf_token"]').attr('content'),
                    id: templateId,
                },
                success: function(data) {
                    $('#template_name').val(data.name);
                    $('#to-subject').val(data.subject);
                    $('#to-body').val(residentname + "\n\n" + data.content + "\n\nRegards,\n" +regards + "\n" + contact);
                },
                error: function(response) {
                    console.log(response);
                }
            });
        })
        $('#resident-mail-popup').modal('show');
    });

   
});

