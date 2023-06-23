<?php

// create a cURL handle
$curl = curl_init();

// set the cURL options
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://www.agoda.com/api/personalization/PersonalizeRecommendedProperties/v1?finalPriceView=1&hotelId=1122442&hasSearchCriteria=true&checkIn=2023-08-12T00%3A00%3A00&lengthOfStay=1&adults=2&children=0&rooms=1&loyaltyProgramId=0&size=2&recommendationType=2&cityId=4064&_ts=1687523041224",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true
));

// execute the cURL request
$response = curl_exec($curl);

// close the cURL handle
curl_close($curl);

// print the response
echo $response;


?>