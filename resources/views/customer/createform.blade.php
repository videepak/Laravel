@if(isset($allCustomers) && ($allCustomers->isNotEmpty())   )
<div class="form-group">
    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="title_field"></label>
    <div class="col-md-6 col-sm-6 col-xs-12">
        <label>
            <input type="radio" class="customerRadio flat day" 
                name="customerRadio" value="0" checked> New Customer
         </label>
         <label>
            <input type="radio" class="customerRadio flat day" 
                name="customerRadio" value="1"> Existing Customer
         </label>
    </div>
</div>

<div class="form-group customerSelect" style="display: none">
    <label for="customerSelect" class="control-label col-md-3 col-sm-3 col-xs-12">Existing Customer <span class="required req_field">*</span></label> 
    <div class="col-md-6 col-sm-6 col-xs-12">
        <select class="form-control select2 col-md-7 col-xs-12"
            name="existingCustomer"
            id="customerSelect"
            autocomplete="off" >
                    <option value="">Select Customer</option>
                    @foreach($allCustomers as $customers)
                        <option value="{{$customers->id}}">{{$customers->name}}</option>
                    @endforeach
        </select>
    </div>
</div>
@endif

<div class="form-group">
    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="title_field">Full Name
         <span class="required req_field">*</span>
    </label>
    <div class="col-md-6 col-sm-6 col-xs-12">
        <input id="title_field"
            name="customer_name" 
            autocomplete="off"
            data-parsley-required-message="Please enter your full name" 
            required="required" 
            value="{{ old('customer_name') }}@if(isset($customer->name) && !empty($customer->name)){{$customer->name}}@endif"
            class="form-control col-md-7 col-xs-12" type="text"
        >
    </div>
</div>

<div class="form-group">
    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email_field">Email
        <span class="required req_field">*</span>
    </label>
    <div class="col-md-6 col-sm-6 col-xs-12">
        <input 
            required="required"
            name="email"
            id="email_field"
            type="text" 
            autocomplete="off"
            class="form-control col-md-7 col-xs-12"
            data-parsley-type="email"
            data-parsley-trigger="focusin focusout"
            data-parsley-required-message="Please enter your email" 
            data-parsley-remote-message="This email has already been taken."
            data-parsley-remote="@if(isset($customer->id) && !empty($customer->id)){{url('validate/customer/email?id='.$customer->id)}} @else{{url('validate/customer/email')}}@endif"
            value="{{ old('email') }}@if(isset($customer->email) && !empty($customer->email)){{$customer->email}}@endif"
            @if(isset($customer->email) && !empty($customer->email)) readonly @endif 
        />
    </div>
</div>

<div class="form-group">
    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="phone">Phone <span class="required req_field">*</span>
    </label>
    <div class="col-md-6 col-sm-6 col-xs-12">
        <input id="phone" 
            autocomplete="off"
            data-parsley-trigger="focusin focusout"
            name="phone"
            minlength="10"
            maxlength="10" 
            data-parsley-remote="@if(isset($customer->id) && !empty($customer->id)){{url('validate/customer/mobile?id='.$customer->id)}} @else{{url('validate/customer/mobile')}}@endif"
            data-parsley-length-message="This number is invalid. Please enter 10 digits in the field." onkeypress="return isNumber(event)" 
            data-parsley-type="digits"
            data-parsley-trigger="keyup"
            data-p  arsley-required-message="Please enter phone number" data-parsley-remote-message="This number has already been taken." data-parsley-type="digits" 
            data-parsley-maxlength="10"
            required="required" 
            class="form-control col-md-7 col-xs-12"
            value="{{old('phone')}}@if(isset($customer->phone)&& !empty($customer->phone)){{$customer->phone}}@endif"
            type="text"
        >
    </div>
</div>
<div class="form-group">
    <label for="address_field" class="control-label col-md-3 col-sm-3 col-xs-12">Address <span class="required req_field">*</span></label> 
    <div class="col-md-6 col-sm-6 col-xs-12">
        <input 
            autocomplete="off"
            id="address_field"
            class="form-control col-md-7 col-xs-12"
            name="address"
            data-parsley-required-message="Please enter your address"
            required="required"
            value="{{ old('address') }}@if(isset($customer->address) && !empty($customer->address)){{$customer->address}}@endif" 
            type="text"
        >
    </div>
</div>

<div class="form-group">
    <label for="city_field" class="control-label col-md-3 col-sm-3 col-xs-12">City <span class="required req_field">*</span></label> 
    <div class="col-md-6 col-sm-6 col-xs-12">
        <input id="city_field"
            autocomplete="off"    
            class="form-control col-md-7 col-xs-12"
            name="city"
            data-parsley-required-message="Please enter your city"
            required="required"
            type="text"
            value="{{ old('city') }}@if(isset($customer->city) && !empty($customer->city)){{$customer->city}}@endif"
        >
    </div>
</div>

<div class="form-group">
    <label for="state_field" class="control-label col-md-3 col-sm-3 col-xs-12">State <span class="required req_field">*</span></label> 
    <div class="col-md-6 col-sm-6 col-xs-12">
        <select 
            autocomplete="off"
            id="state_field" 
            class="form-control col-md-7 col-xs-12" 
            name="state" data-parsley-required-message="Please select your state" required="required">
            @if(isset($states) && ($states->isNotEmpty()))
            <option value="">Select State</option>
            @foreach($states as $state)
            <option @if(isset($customer->state) && ($customer->state == $state->id)) selected="selected" @endif value="{{$state->id}}" {{ old('state') == $state->id ? 'selected' : '' }}>{{$state->name}}</option>
            @endforeach
            @endif
        </select>
    </div>
</div>

<div class="form-group">
    <label for="zip_field" class="control-label col-md-3 col-sm-3 col-xs-12">Zip <span class="required req_field">*</span></label> 
    <div class="col-md-6 col-sm-6 col-xs-12">
        <input 
        autocomplete="off"
        id="zip_field"
        data-parsley-type="digits" 
        class="form-control col-md-7 col-xs-12"
        name="zip"
        data-parsley-required-message="Please enter zip code"
        required="required"
        value="{{old('zip')}}@if(isset($customer->zip) && !empty($customer->zip)){{$customer->zip}}@endif"
        type="text">
    </div>
</div>
<input type="hidden" id="localStorage" name="localStorage" value="">
@section('js')
<script>

    $('#add_customer').parsley({
        excluded: '.two input'
    });

    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }

    $(document).ready(function () {
        $('#customerSelect').on('change', function (event) {
            $.ajax({
                url: BaseUrl + '/customer/exsiting',
                type: "POST",
                data: {
                    _token: $('meta[name="csrf_token"]').attr('content'),
                    id: $(this).val()
                },
                beforeSend: function () {
                    showLoader();
                },
                success: function (data) {
                    hideLoader();
                    $('#add_customer').parsley().reset();
                    $('#title_field').val(data.response.name);
                    $('#email_field').val(data.response.email);
                    $('#phone').val(data.response.phone);
                    $('#address_field').val(data.response.address);
                    $('#city_field').val(data.response.city);
                    $('#address_field').val(data.response.address);
                    $('#zip_field').val(data.response.zip);
                    $("#state_field").children('[value="' + data.response.state + '"]').prop("selected", true);


                }
            });
        });

        $('#phone').bind('copy paste cut', function (e) {
            e.preventDefault(); //disable cut,copy,paste
        });

        $('input[name="customerRadio"]').on('ifClicked', function (event) {
            if (this.value == 1) {
                $('#add_customer').parsley().destroy()
                $('#email_field').attr('data-parsley-remote-message', '')
                $('#phone').attr('data-parsley-remote-message', '');
                
                //$('#add_customer').parsley().reset();
                $('#add_customer input').attr('readonly', 'readonly');
                $('#state_field').attr("disabled", true);
                $(this).closest('form').find("input[type=text], select").val("");
                $(".customerSelect").show();
            } else {
                $('#add_customer').parsley().reset();
                
                $('#email_field').attr('data-parsley-remote-message', 'This email has already been taken.')
                $('#phone').attr('data-parsley-remote-message', 'This number has already been taken.')

                $(".customerSelect").hide();
                $(this).closest('form').find("input[type=text]").attr("readonly", false);
                $('#state_field').prop("disabled", false);
                $(this).closest('form').find("input[type=text], select").val("");
            }
        });

        if (localStorage.getItem("customerStatus")) {
            $('#localStorage').val(localStorage.getItem("customerStatus"));
        }
    });
</script>
@endsection