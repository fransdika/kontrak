<!-- <?php 
// print_r($company);
?> -->
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

		<ul class="nav nav-tabs" id="myTab" role="tablist">
			<li class="nav-item">
				<a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">
                    Tables & Views
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                aria-controls="profile" aria-selected="false">Triggers & Procedures</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="query-pos-tab" data-toggle="tab" href="#query-pos" role="tab"
                aria-controls="query-pos" aria-selected="false">POS Exec</a>
            </li>

        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab" style="background-color:floralwhite">
                <div class="container-fluid p-3">
                    <form class="frm_exe">
                        <label for="taQuery">Query:</label>
                        <div class="form-group">
                            <textarea name="query_sql" id="query" class="form-control" style="height: 400px;"
                            placeholder="Put Your Query here..." p></textarea>
                            <div class="form-group"></div>
                            <input type="submit" name="btn_exe" value="Execute Query" class="btn btn-success" style="float: right;">
                        </div>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab" style="background-color:floralwhite">
                <div class="container-fluid p-3">
                    <form class="frm_exe_trproc">
                        <label for="taQuery">Query:</label>
                        <div class="form-group">
                            <textarea name="query_sql" id="query_sql" class="form-control" style="height: 400px;"
                            placeholder="Put Your Query here..." p></textarea>
                            <div class="form-group">
                            </div>
                            <input type="submit" name="btn_exe" value="Execute Query" class="btn btn-success"
                            style="float: right;">
                        </div>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="query-pos" role="tabpanel" aria-labelledby="query-pos-tab" style="background-color:floralwhite">
                <div class="container-fluid p-3">
                    <form id="frm_exe_query_pos">
                        <div class="row">
                            <div class="col-md-5">
                                <label for="taQuery">Company:</label>
                                <div class="form-group">
                                    <select id="cid" name="cid" class="form-control">
                                        <option value="">Pilih Company</option>
                                        <?php foreach ($company as $key_company => $value_company): ?>
                                            <option value="<?=$value_company->company_id ?>"><?=$value_company->nama_usaha." -- ".$value_company->company_id ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="taQuery">Sinkronisasi?:</label>
                                <div class="form-group">
                                    <input type="radio" name="isSinkro" value="0" checked> TIDAK
                                    <input type="radio" name="isSinkro" value="1"> IYA
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label for="taQuery">Keyword:</label>
                                <div class="form-group">
                                    <input type="type" name="keyword" placeholder="keyword(tabel)" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <label for="taQuery">Query:</label>
                        <div class="form-group">
                            <textarea name="query_sql" id="query_sqlite" class="form-control" style="height: 400px;"
                            placeholder="Put Your Query here..." p></textarea>
                            <div class="form-group">
                            </div>
                            <input type="submit" name="btn_exe" value="Execute Query" class="btn btn-success"
                            style="float: right;">
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
        $('.frm_exe_trproc').on('submit',function(e) {
            e.preventDefault();
            let input='';
            input= $('#query_sql').val();
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
                                var base_url = {!! json_encode(url('/')) !!};
                                $.ajax({
                                    type:"POST",
                                    url:`${base_url}/api/utilities/multi-query-alter`,
                                    data:{query:input,password:split[1]},
                                    contentType: 'application/x-www-form-urlencoded',
                                    dataType:'json',
                                    crossDomain: true,
                                    success:function(r){
                                        if (r.status==1) {
                                        // alert('success');
                                        window.location.reload();
                                    }
                                }
                            });
                            }else{
                                $.alert('Failed to Execute Query');
                                return false;
                            }
                        }
                    },
                    cancel: function () {
                        // close
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
        $('#frm_exe_query_pos').on('submit',function(e) {
            e.preventDefault();
            let input='';
            input= $('#query_sqlite').val();
            sinkro= $('input[name="isSinkro"]:checked').val();;
            keyword= $("input[name=keyword]").val();
            
            // console.log($(this).serialize());
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
                                var base_url = {!! json_encode(url('/')) !!};
                                let company_id=$('#cid').val();
                                $.ajax({
                                    type:"POST",
                                    url:`${base_url}/api/utilities/sqlite-pos-query/`+company_id,
                                    data:{sqlitequery:input,password:split[1],isSinkro:sinkro,keyword:keyword},
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
                            }else{
                                $.alert('Failed to Execute Query');
                                return false;
                            }
                        }
                    },
                    cancel: function () {
                        // close
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
        })
    </script>
</body>
</html>