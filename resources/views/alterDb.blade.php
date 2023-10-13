<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
</head>
<body>
	<div class="container">
		<h2>Altering Database</h2>
		<hr>
		<form class="frm_exe">
			<label for="taQuery">Query:</label>
			<div class="form-group">
				<textarea name="query_sql" id="query" class="form-control" style="height: 400px;" placeholder="Put Your Query here..."p></textarea>
			</div>
			<div class="form-group">
				<input type="submit" name="btn_exe" value="Execute Query" class="btn btn-success" style="float: right;">
			</div>
		</form>
	</div>

	<script>
		$('.frm_exe').on('submit',function(e) {
			e.preventDefault();
			// alert('asdfa');
			let input='';
			input= $('#query').val();
			console.log($(this).serialize());
			let data='';
			$.confirm({
				title: 'Execute Query?',
				content: 
				'<form action="" class="formName">' +
				'<div class="form-group">' +
				'<label>Type <strong>Yes</strong> and Put <strong>Your Password</strong> below to confirm </label>' +
				'<input type="text" placeholder="Your password" class="name form-control" required />' +
				'</div>' +
				'</form>',
				buttons: {
					formSubmit: {
						text: 'Submit',
						btnClass: 'btn-blue',
						action: function () {
							var name = this.$content.find('.name').val();
							if(!name){
								$.alert('provide a valid key');
								return false;
							}
                            let split=name.split('_');
                            if (split[0]==="Yes"){
								// alert('d');
								// console.log($( this ).serialize());
								// console.log($(this).serialize());
								var base_url = {!! json_encode(url('/')) !!};
                                // console.log(base_url);
                                $.ajax({
                                   type:"POST",
                                   url:`${base_url}/api/query-all`,
                                   data:{query_sql:input,password:split[1]},
                                   contentType: 'application/x-www-form-urlencoded',
                                   dataType:'json',
                                   crossDomain: true,
                                   success:function(r){
                                      if (r.status==1) {
                                         alert('success');
											// window.location.reload();
										}
									}
								});
								// $.alert('Your name is ' + name);
							}else{
								$.alert('Failed to Execute Query');
								return false;
							}
						}
					},
					cancel: function () {
                		//close
                	},
                },
                onContentReady: function () {
            		// bind to events
            		var jc = this;
            		this.$content.find('form').on('submit', function (e) {
                		// if the user submits the form by pressing enter in the field.
                		e.preventDefault();
                		jc.$$formSubmit.trigger('click'); 
                		// reference the button and click it
                	});
            	}
            });
		});
	</script>
</body>
</html>