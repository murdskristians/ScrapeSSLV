<?php
error_reporting(E_ALL); // Error/Exception engine, always use E_ALL
ini_set('ignore_repeated_errors', TRUE); // always use TRUE
ini_set('display_errors', FALSE); // Error/Exception display, use FALSE only in production environment or real server. Use TRUE in development environment
ini_set('log_errors', TRUE); // Error/Exception file logging engine.
ini_set('error_log', 'kludu.log'); // Logging file path

// https://www.ss.lv/lv/search-result/?q=android+box
$content = file_get_contents("https://www.ss.lv/lv/real-estate/flats/riga/centre/");
$parts = explode( '<tr id="tr_', $content);

unset($parts[0]);
array_pop($parts);

// var_dump($parts);
// json_encode($parts);
// echo $parts;

$offers = [];

foreach ($parts as $part){
    
    $cells = explode("</td>", $part);
    $tmp = explode('<a href="', $cells[1])[1];

    $link = "https://ss.com/".explode('"', $tmp)[0];

    foreach ($cells as $i => $c){
        $cells[$i] = strip_tags($c);

        $data = [
            "street"=>$cells[3],
            "rooms"=>$cells[4],
            "area"=>$cells[5],
            "price"=>$cells[9],
        ]

        $offers[$link] = $data;
    }

    $offersOld = json_encode(file_put_contents("offers.json"));

    foreach ($offers as $link=>$offer){

        if (!issset($offersOld[$link])){

            $message = "Jauns sludinājums ".$offer['street']." - 
            istabas:".$offer['rooms'].", platiba:"$offer['area'].",
            price:".$offer['price']." ".$link;

            $message = urlencode($massage);

            // echo "IR JAUNS";
            // include "secret/php";

            //te vajag api
            $url = "https:/api.text2reach.com/sms/sending";


            file_put_contents($url);

            echo "X";
        }
    }

    file_put_contents("offers.json",json_encode($offers));
}