<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
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
		<div class="row">
			<div class="col-md-6">
				<form id="frm_generateMamboCode">
					<div class="form-group">
						<label>Toko</label>
						<input type="text" class="form-control" name="toko" id="toko" value="Mane" readonly>
					</div>
					<div class="form-group">
						<input type="number" class="form-control" name="jumlah_generate" id="jumlah" value="1000" onclick="select()">
					</div>
					<input type="submit" class="btn btn-primary btn-sm" name="" value="Generate" >
				</form>
			</div>
		</div>
		<div class="row" style="margin-top: 10px;display: none;" id="hasil-generate">
			<div class="col-md-12">
				<label><strong>Generated Code</strong></label>
				<div id="code"></div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		$('#frm_generateMamboCode').on('submit',function(e){
			e.preventDefault();	
			let jumlah=$('jumlah').val();
			var base_url = {!! json_encode(url('/')) !!};
			$.ajax({
				type:"POST",
				url:`${base_url}/api/do-generate-mambo`,
				data:{company_id:'misterkong_comp2020100516254401',jumlah:jumlah},
				dataType:"json",
				success:function(r){
					data=r.data;
					html_data=``;
					Object.keys(obj).forEach(function(key) {
						console.log(obj[key]);
					});
					// for (var i = 0; i < data.length; i++) {
					// 	html_data+=data[i].toString();
					// }
					// console.log(data);
					// $('#code').html(data)
					// $('#hasil-generate').css('display','block');
						// console.log(r)
					}

				});
		})
	</script>
</body>
</html>