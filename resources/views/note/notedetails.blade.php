<div class="row">
    <div class="col-md-6">
        <h2> </h2>
    </div>
</div>
<div class="row" >
    <div class="col-md-8">
        <div class="row" >
            <div class="form-group">
                <label class="control-label col-md-4 col-sm-4 col-xs-12">Employee: </label>
                <div class="col-md-8 col-sm-8 col-xs-12">

                    @isset($noteDetail['employee'])
                    {{$noteDetail['employee']}}
                    @else
                    -
                    @endisset
                </div>
            </div>
        </div>
        
        <div class="row" >
            <div class="form-group">
                <label class="control-label col-md-4 col-sm-4 col-xs-12">Property Name: </label>
                <div class="col-md-8 col-sm-8 col-xs-12">
                    @isset($noteDetail['propertyName'])
                        {{$noteDetail['propertyName']}}
                    @else
                         -
                    @endisset    
                </div>
            </div>
        </div>
        
        
        <div class="row" >
            <div class="form-group">
                <label class="control-label col-md-4 col-sm-4 col-xs-12">Property Address: </label>
                <div class="col-md-8 col-sm-8 col-xs-12">
                @isset($noteDetail['propertyAddress'])    
                    {{$noteDetail['propertyAddress']}}
                @else
                    -
                @endisset    
                </div>
            </div>
        </div>
        
        <div class="row" >
            <div class="form-group">
                <label class="control-label col-md-4 col-sm-4 col-xs-12">Unit Detail: </label>
                <div class="col-md-8 col-sm-8 col-xs-12">

                    @isset($noteDetail['unitNumber'])
                        
                        @isset($noteDetail['unitNumber'])
                            <b>Unit Number:</b> {{$noteDetail['unitNumber']}}
                        @endisset    
                        
                        @isset($noteDetail['address1'])
                            <b>Address1:</b> {{$noteDetail['address1']}}
                        @endisset    
                        
                        @isset($noteDetail['building'])
                            <br><b>Building:</b> {{$noteDetail['building']}}
                        @endisset    
                        
                        @isset($noteDetail['buildingAddress'])
                            <br><b>Building Address:</b> {{$noteDetail['buildingAddress']}}
                        @endisset    
                   
                    @else
                        General Note
                    @endif
                </div>
            </div>
        </div>
        <div class="row" >
            <div class="form-group">
                <label class="control-label col-md-4 col-sm-4 col-xs-12">Description: </label>
                <div class="col-md-8 col-sm-8 col-xs-12">
                    @isset($noteDetail['description'])
                    {{$noteDetail['description']}}
                    @else
                    -
                    @endisset
                </div>
            </div>
        </div>
        <div class="row" >
            <div class="form-group">
                <label class="control-label col-md-4 col-sm-4 col-xs-12">Note Reason: </label>
                <div class="col-md-8 col-sm-8 col-xs-12">
                    @isset($noteDetail['subject'])
                    {{$noteDetail['subject']}}
                    @else
                    -
                    @endisset
                </div>
            </div>
        </div>
        <div class="row" >
            <div class="form-group">
                <label class="control-label col-md-4 col-sm-4 col-xs-12">Latitude: </label>
                <div class="col-md-8 col-sm-8 col-xs-12">
                    @isset($noteDetail['long'])
                    {{$noteDetail['long']}}
                    @else
                    -
                    @endisset
                </div>
            </div>
        </div>
        <div class="row" >
            <div class="form-group">
                <label class="control-label col-md-4 col-sm-4 col-xs-12">Longitude: </label>
                <div class="col-md-8 col-sm-8 col-xs-12">
                    @isset($noteDetail['lat'])
                    {{$noteDetail['lat']}}
                    @else
                    -
                    @endisset
                </div>
            </div>
        </div>
        <div class="row" >
            <div class="form-group">
                <label class="control-label col-md-4 col-sm-4 col-xs-12">Date: </label>
                <div class="col-md-8 col-sm-8 col-xs-12">
                    @isset($noteDetail['date'])
                    {{$noteDetail['date']}}
                    @else
                    -
                    @endisset
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <img src="{{$noteDetail['url']}}" class="img-rounded img-responsive" />
    </div>
</div>
<div class="row">
    <br/>
</div>