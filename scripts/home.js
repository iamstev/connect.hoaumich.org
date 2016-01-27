function connectCleanup(){
	closeTopError();
	document.getElementById('modal_h1').innerHTML = '';
	document.getElementById('modal_content').innerHTML = '';
	$('#ajax-modal').removeClass('contact-form');
	$('#modal_close').unbind('click');
}

function contact(step, data1){
	fullScreenLoad('show');
	$('#modal_close').click(function(){ connectCleanup(); });
    $('#ajax-modal').addClass('contact-form');
	var ajx = $.post(
		"/ajax/contact/",
		{s:step, d1:data1},
		function( data ) {
			if(data.error === '0' || data.error === '1'){
				document.getElementById('modal_h1').innerHTML = data.h1;
				document.getElementById('modal_content').innerHTML = data.html;
				if(step === 1){
					showModal('ajax-modal');
					CKEDITOR.replace('contact_message', { toolbar : 'simplelink' });
					$('#contact-submit').click(function() {
						var msg = CKEDITOR.instances.contact_message.getData();
                        var dd = {
                            "name" : document.getElementById('contact-name').value,
							"email" : document.getElementById('contact-email').value,
							"dest" : document.getElementById('contact-dest').value,
							"msg" : msg
                        };
						closeTopError();
                        contact(2, dd);
					});
				}
				fullScreenLoad('hide');
            }
			if(data.error === '3'){
				topError(data.html);
				fullScreenLoad('hide');
			}
		},
		"json"
	);
	ajx.fail( function(){
		document.getElementById('modal_h1').innerHTML = 'Error';
		document.getElementById('modal_content').innerHTML = '<p>There was an error with the contact form. Please try again.</p><p>(ref: ajax fail)</p>';
		showModal('ajax-modal');
		fullScreenLoad('hide');
	});
}
















function newsletter(step, data1){
	fullScreenLoad('show');
	$('#modal_close').click(function(){ connectCleanup(); });
    $('#ajax-modal').addClass('contact-form');
	var ajx = $.post(
		"/ajax/newsletter/",
		{s:step, d1:data1},
		function( data ) {
			if(data.error === '0' || data.error === '1'){
				document.getElementById('modal_h1').innerHTML = data.h1;
				document.getElementById('modal_content').innerHTML = data.html;
				if(step === 1){
					showModal('ajax-modal');
					$('#newsletter-submit').click(function() {
                        var dd = {
                            "name" : document.getElementById('newsletter-name').value,
							"email" : document.getElementById('newsletter-email').value
                        };
						closeTopError();
                        newsletter(2, dd);
					});
				}
				fullScreenLoad('hide');
            }
			if(data.error === '3'){
				topError(data.html);
				fullScreenLoad('hide');
			}
		},
		"json"
	);
	ajx.fail( function(){
		document.getElementById('modal_h1').innerHTML = 'Error';
		document.getElementById('modal_content').innerHTML = '<p>There was an error with the newsletter form. Please try again.</p><p>(ref: ajax fail)</p>';
		showModal('ajax-modal');
		fullScreenLoad('hide');
	});
}
