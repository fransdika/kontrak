<?php 
	$token = rand(1000, 9999);
	$kirim = ["token" => $token, "tujuan" => 'fransdika94@gmail.com', 'jenis' => '1'];
	// print_r($kirim);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://ssid.solidtechs.com/all_api/api_email/Send_email.php");
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $kirim);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	// print_r($output);
	curl_close($ch);
	$response = json_decode($output);
	$response->tujuan = $email;
	$response->tipe = 'email';
	echo json_encode($response);

 ?>