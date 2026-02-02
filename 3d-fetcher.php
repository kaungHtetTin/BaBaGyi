<?php
// Initialize cURL session
$ch = curl_init();

// Set the URL of the page you want to access
$url = "https://news.sanook.com/lotto/";

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return response as string
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification (optional)
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36");  // Simulate a browser

// Execute the cURL request
$response = curl_exec($ch);

$dom = new DOMDocument();

// Suppress warnings due to malformed HTML
libxml_use_internal_errors(true);

// Load HTML from a string
$dom->loadHTML($response);

// Create a new XPath object
$xpath = new DOMXPath($dom);

// Query for all elements with class "content"
$nodes = $xpath->query('//*[contains(@class, "sukhumvit lotto__number lotto__number--first lotto__number--0")]');

// Loop through the nodes and get the text

$win_num = $nodes[0]->nodeValue;
$win_num = substr($win_num,-3);

$res['number'] = $win_num;

echo json_encode($res);

//echo $response;

curl_close($ch);
?>
