<?php
// For debugging will output all errors to kludu.log file that is in last line
error_reporting(E_ALL); // Error/Exception engine, always use E_ALL
ini_set('ignore_repeated_errors', TRUE); // always use TRUE
ini_set('display_errors', FALSE); // Error/Exception display, use FALSE only in production environment or real server. Use TRUE in development environment
ini_set('log_errors', TRUE); // Error/Exception file logging engine.
ini_set('error_log', 'kludu.log'); // Logging file path

// Link for getting ads
$content = file_get_contents("https://www.ss.lv/lv/real-estate/flats/riga/all/hand_over/filter/");

// Create array where each element is separate ad.
$parts = explode( '<tr id="tr_', $content);

// Cuts first and last element from array. Not needed as those are not ads.
unset($parts[0]);
array_pop($parts);

// Array where ads are saved
$ads = [];

// Takes each ad 1 by 1 and splits into parts of information in ad
foreach($parts as $part){

    // Splits into parts
    $adData = explode("</td>", $part);

    // Creates link that will be used as key to ads
    $tmp = explode('<a href="', $adData[1])[1];
    $link = "https://www.ss.com/".explode('"', $tmp)[0];

    // Removes HTML tags
    foreach($adData as $column=>$cell){
        $adData[$column] = strip_tags($cell);
    }

    // Makes data object to save info from ad data
    $data = [
        "street" => $adData[3],
        "rooms" => $adData[4],
        "area" => $adData[5],
        "price" => $adData[9]
    ];

    // Using link as key saves data in ads array
    $ads[$link] = $data;
}

// Saves all ads to compare and find new ads later
$adsOld = json_decode(file_get_contents("ads.json"), true);

$message = '';

// Takes each ad and creates message from individual ad data
foreach($ads as $link=>$offer){
 
    // Creates message if finds new ad
    if(!isset($adsOld[$link])){

        $message .= 'Jauns sludinājums: ';
        $message .= $offer['street'];
        $message .= ' iela, ';
        $message .= $offer['rooms'];
        $message .= ' istabas, ';
        $message .= $offer['area'];
        $message .= ' platība, cena';
        $message .= $offer['price'];
        $message .= ' , Links: ';
        $message .= $link;
        $message .= '\n';
    }
}

// If any massage is created
if(strlen($message) > 0){

    // Saves all ads in file
    file_put_contents('ads.json', json_encode($ads));

    // Outputs message on screen
    echo $message;
    
    // Because URL cannot contain Latvian we transform text for URL
    $message = urlencode($message);

    // Data needed to send SMS over internet API
    $sms_key = '9056A029EB94FAE7A3E42C26B40D0243';           
    $phoneNr = '37129886177';
    $name = 'Helper';
    $url = 'https://api.text2reach.com/sms/send?api_key='.$sms_key
    .'&phone='.$phoneNr
    .'&from='.$name
    .'&message='.$message;

    // Initialize a cURL session
    $ch = curl_init($url);
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Send the request and get the response (Execute URL)
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
    echo 'Nothing';
}




