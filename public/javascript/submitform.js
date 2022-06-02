$(document).ready(function(){
    $('#kategorie option').bind('click', function(){
        $.get('/secure/selectdata/'+$(this).val(), function (response){
            if(response){
               if($('#item').length > 0){
                    $('#item').remove();
               }
               $(response).insertAfter('#kategorie');
            }
        });
    });
    $('#abschicken').bind('click', function(event){
        event.preventDefault();
        const data = new FormData($('#submitform'));
        $.ajax({
            type: 'POST',
            enctype: 'multipart/form-data',
            url: "/secure/submit",
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 800000,
            success: function (data) {
                $('body').append('<div id="output"></div>');
                $("#output").text(data);
                console.log("SUCCESS : ", data);
                //$("#btnSubmit").prop("disabled", false);
            },

            error: function (e) {
                $('body').append('<div id="output"></div>');
                $("#output").text(e.responseText);
                console.log("ERROR : ", e);
                $("#btnSubmit").prop("disabled", false);
            }

        });

    });
});