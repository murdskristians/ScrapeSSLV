<?php
error_reporting(E_ALL); // Error/Exception engine, always use E_ALL
ini_set('ignore_repeated_errors', TRUE); // always use TRUE
ini_set('display_errors', FALSE); // Error/Exception display, use FALSE only in production environment or real server. Use TRUE in development environment
ini_set('log_errors', TRUE); // Error/Exception file logging engine.
ini_set('error_log', 'kludu.log'); // Logging file path


$content = file_get_contents("https://www.ss.lv/lv/real-estate/flats/riga/centre/");
$parts = explode( '<tr id="tr_', $content);

unset($parts[0]);
array_pop($parts);

$offers = [];

foreach($parts as $part){
    $cells = explode("</td>", $part);

    $tmp = explode('<a href="', $cells[1])[1];
    $link = "https://www.ss.com/".explode('"', $tmp)[0];

    foreach($cells as $i=>$c){
        $cells[$i] = strip_tags($c);
    }
     echo json_encode($cells);
     echo '<br><br>';
    $data = [
        "street" => $cells[3],
        "rooms" => $cells[4],
        "area" => $cells[5],
        "price" => $cells[9]
    ];

    $offers[$link] = $data;
}

$offersOld = json_decode(file_get_contents("offers.json"), true);
// print_r($offersOld);
// exit;

$message = '';
foreach($offers as $link=>$offer){
 
    if(!isset($offersOld[$link])){

        $message .= 'Jauns sludinājums: ';
        $message .= $offer['street'];
        $message .= ' ';
        $message .= $offer['rooms'];
        $message .= ' ';
        $message .= $offer['area'];
        $message .= ' ';
        $message .= $offer['price'];
        $message .= ' ';
        $message .= $link;
        $message .= '||||||||||||||||||||||||';
    }
}

if(strlen($message) > 0){


    file_put_contents('offers.json', json_encode($offers));

    $message = urlencode($message);
    echo $message;

    // $sms_key = '9056A029EB94FAE7A3E42C26B40D0243';      
            
    $url = 'https://api.text2reach.com/sms/send?api_key=9056A029EB94FAE7A3E42C26B40D0243&phone=37129886177&from=SIA+Va+DEMO&message='.$message;

    // Initialize a cURL session
    $ch = curl_init($url);
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Send the request and get the response
    $response = curl_exec($ch);
    // Check if the request was successful
    if ($response === false) {
        // An error occurred, handle it
        $error = curl_error($ch);
        echo "cURL request failed: $error\n";
    } else {
        // The request was successful, process the response
        echo "Response: $response\n";
    }
    // Close the cURL session
    curl_close($ch);

}else{
    echo 'nav';
}




