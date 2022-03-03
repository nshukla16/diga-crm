var sendFlag = false;
var data;
window.erp_email_sends = false;

AfterLogicApi.addPluginHook('ajax-default-request', function(sAction, oParameters){
    if(sAction == "MessageSend"){
        sendFlag = true;
        data = {'to' : parse_email(oParameters['To']), 'subject' : oParameters['Subject'], 'text' : oParameters['Text']};
        //console.log(oParameters);
    }
});

AfterLogicApi.addPluginHook('ajax-default-response', function(sAction, oData){
    if(sAction == "MessageSend" && sendFlag){
        window.erp_email_sends = true;
        sendFlag = false;
        from = window.App.getAccounts().collection().map(function(account){ return account.email(); });
        $.ajax({
            type: "POST",
            url: "/api/clients/outcoming_email",
            data: {
                from: JSON.stringify(from),
                to: data.to,
                text: data.text,
            }
        }).done(function(response) {
            if(response.errcode == 0){
                console.log('saved');
            }else{
                // Oops! We got some error
                alert(response.errmess);
            }
        }).fail(function() {
            alert("error");
        }).always(function() {
            window.erp_email_sends = false;
        });
    }
});

/* Triggers by button in visual interface*/

function incoming_email(from, to_with_name){
    $('.add-to-messages').css('background-color', '#DEAA13');
    $('.add-to-messages').text(window.pSevenI18N["MAILBOX/ADD_TO_ERP_PROCESSING"]);
    $.ajax({
        type: "POST",
        url: "/api/clients/incoming_email",
        data: {
            from: from,
            to: parse_email(to_with_name),
            text: $('div [data-x-div-type="body"]').html(),
        }
    }).done(function(response) {
        if(response.errcode == 0){
            alert(window.pSevenI18N["MAILBOX/EMAIL_ADDED_TO_ERP"]);
            console.log('saved');
        }else{
            // Oops! We got some error
            alert(response.errmess);
        }
    }).fail(function() {
        alert( "error" );
    }).always(function(){
        $('.add-to-messages').css('background-color', '#81C54B');
        $('.add-to-messages').text(window.pSevenI18N["MAILBOX/ADD_TO_ERP"]);
    });
}

function parse_email(to_with_name){
    email = to_with_name.match(/<(.*)>/i);
    if(email == null) {
        to = to_with_name;
    }else{
        to = email[1];
    }
    return to;
}