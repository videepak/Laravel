<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Barcode: {{ ucwords($qrproperty->name) }}</h4>
</div>

    <div class="modal-body">
        <div class="container">
            <div class="col-lg-12" id="printableArea">
                <img
                    class="barcode-img"
                    src="data:image/png;base64,
                    {!! 
                        base64_encode(
                            QrCode::format('png')
                            ->size(250)
                            ->generate(
                                url("guest/residents/$qrproperty->id")
                            )
                        )
                    !!}"
                />
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input
            type="button" id="print" value="Print"
            onclick="printDiv('printableArea')"
            class="btn btn-success"
        />
    </div>
<script>
    function printDiv(divName) {
        var contents = $("#printableArea").html();
        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        frame1.css({"position": "absolute", "top": "-1000000px"});
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
        //Create a new HTML document.
        frameDoc.document.write('<html><head><title>DIV Contents</title>');
        frameDoc.document.write('</head><body>');
        //Append the external CSS file.
        frameDoc.document.write('<link href="{{url("assets/build/css/print.css")}}" rel="stylesheet" type="text/css" />');

        //Append the DIV contents.
        frameDoc.document.write(contents);
        frameDoc.document.write('</body></html>');
        frameDoc.document.close();
        setTimeout(function () {
            window.frames["frame1"].focus();
            window.frames["frame1"].print();
            //frame1.remove();
        }, 500);
    }
</script>