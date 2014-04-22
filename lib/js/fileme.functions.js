$(document).on('click', '#fileme-filelist a.fileme-directory, #fileme-breadcrumb-navigation > li > a', function(e) {

    e.preventDefault();
    if (!$('#fileme-filelist').hasClass('ajax-loading')) {

        var url = $(this).attr('href') + '&showtemplate=false';

        $.ajax({
            type : 'GET',
            url : url,
            async : true,
            dataType : 'JSON',
            beforeSend : function() {

                $('#loader').addClass('ajax-loading');
            },
            error : function(jqXHR, textStatus, errorThrown) {
                
                console.log("Sorry. There was an AJAX error: " + textStatus);
            },
            success : function(event, data) {

                $('<div/>').prependTo('#loader')
                    .addClass('alert')
                    .html(event.message);
                
                if (event.status == 'success') {
                    $('.alert').addClass('alert-success');
                } else if (event.status == 'error') {
                    $('.alert').addClass('alert-error');
                }
                
                $('.fileme-ui .alert').fadeIn(300)
                    .delay(2000)
                    .fadeOut(300)
                    .queue(function() {
                        $(this).remove(); 
                    });

                $('#ajax-filelist-loader').load(window.location + ' #fileme-filelist');
                $('#ajax-breadcrumbs-loader').load(window.location + ' #fileme-breadcrumb-navigation');
            },
            complete : function() {
                
                $('#loader').removeClass('ajax-loading');
                $('.idt-ajax-load-indicator').remove();
            }
        });
    }
});


