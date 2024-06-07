<div style="text-align:center;">
    <h4>Route Checkpoint</h4>
</div>
<table>           
    @foreach($routes as $route)
        <tr>
            <td> 
                <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(150)->generate($route->barcode_id)) !!}" style="width="250px" height="250px" />
            </td>
        </tr>
        <tr>    
            <th style="text-align:center">{{$route->barcode_id}}</th>
        </tr>    
    @endforeach            
</table>