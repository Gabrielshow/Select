<?php
?>
 <form action="script.php" method="post">
  <label>Enter ASIN:</label>
  <input type="text" name="asin">

  <button type="submit">Submit</button>
</form>
<form>
  <label for="dishName">Dish Name:</label>
  <input type="text" id="dishName" name="dishName">

  <label for="expectedPrice">Expected Price:</label>  
  <input type="number" step="0.01" id="expectedPrice" name="expectedPrice">

  <button type="submit">Submit</button>
</form>

<?php
 class ContentAnalyzer {

  public function analyzeContent($content) {

	// Extract date 
preg_match('/(\d{4}-\d{2}-\d{2})/', $content, $matches);
$date = $matches[1]; 

// Extract price
preg_match('/\$(\d+\.?\d*)/', $content, $matches);
$price = $matches[1];

// Extract promo code 
preg_match('/code: ([A-Z0-9]+)/i', $content, $matches);
$code = $matches[1];

	// Check for expired promo codes
if($code && $expiry < time()) {
  $issues[] = 'Expired promo code: ' . $code; 
}

// Check for outdated events 
if($eventDate < time()) {
  $issues[] = 'Outdated event: ' . $eventName;
}

// etc...
return $issues;

// $curl = curl_init();

// curl_setopt_array($curl, [
	// CURLOPT_URL => "https://real-time-amazon-data.p.rapidapi.com/search?query=Phone&page=1&country=US&category_id=aps",
	// CURLOPT_RETURNTRANSFER => true,
	// CURLOPT_ENCODING => "",
	// CURLOPT_MAXREDIRS => 10,
	// CURLOPT_TIMEOUT => 30,
	// CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	// CURLOPT_CUSTOMREQUEST => "GET",
	// CURLOPT_HTTPHEADER => [
		// "X-RapidAPI-Host: real-time-amazon-data.p.rapidapi.com",
		// "X-RapidAPI-Key: bc401849f9mshcfa3f397106de30p171675jsn535fc2b1eff7"
	// ],
// ]);

// $response = curl_exec($curl);
// $err = curl_error($curl);

// curl_close($curl);

// if ($err) {
	// echo "cURL Error #:" . $err;
// } else {
	// echo $response;
// }



  }

}
function extractPriceFromContent(string $content): float|null {

  // Regex to match a $xx.xx price
  $pattern = '/\$([0-9]+\.[0-9]+)/'; 
  
  // Search content
  preg_match($pattern, $content, $matches);

  // Check if we have a match
  if(!isset($matches[1])) {
    return null;
  }

  // Return the captured price as float
  return (float)$matches[1];

}

//modular funtion
function getAmazonPrice(string $asin): float|null {

  $curl = curl_init();

curl_setopt_array($curl, [
	CURLOPT_URL => "https://amazon-product-price-data.p.rapidapi.com/product?asins=$asin&locale=US",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_HTTPHEADER => [
		"X-RapidAPI-Host: amazon-product-price-data.p.rapidapi.com",
		"X-RapidAPI-Key: bc401849f9mshcfa3f397106de30p171675jsn535fc2b1eff7"
	],
]);  

  $response = curl_exec($curl);
// $json = json_encode($response, JSON_PRETTY_PRINT);
// print_r($json);

  if(curl_errno($curl)) {
    return null; 
  }
   
 $data = json_decode($response, true);

  if (isset($data) && is_array($data)) {
	$products = $data;
    $priceStr = '';
	// $firstProduct = $data['data']['products'][0];
    // $price = $firstProduct['product_price'];
    foreach ($products as $product) {
      // $asin = $product['asin'];
      // $title = $product['product_title'];
      $priceStr = $product['current_price'];
    }
    $price = floatval(substr($priceStr, 1));
    return $price;
  }
  else{
  echo "No products found.";
  }

  return null;
}

function getKFCDishes($dishName, $expectedPrice) {

  $curl = curl_init();

  curl_setopt_array($curl, [
    CURLOPT_URL => "https://kfc-chickens.p.rapidapi.com/chickens",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
      "X-RapidAPI-Host: kfc-chickens.p.rapidapi.com",
      "X-RapidAPI-Key: bc401849f9mshcfa3f397106de30p171675jsn535fc2b1eff7"
    ],
  ]);

  $response = curl_exec($curl);
  
  if(curl_errno($curl)) {
    return false;
  }
  
  $dishes = json_decode($response, true);
  
  // Extract dish name and price
  foreach($dishes as $dish) {
	  $name = $dish['name'];
	  $price = $dish['price'];
	  if($name === $dishName){
	     if($price === $expectedPrice){
		  echo "Price matches!";
		  echo $name . ": $" . $price . PHP_EOL;
		  }
		  else {
		  echo "Price does not match. Expectd: $expectedPrice, Actual: {$dish['price']}";
		  }
	  return;
	  }
	 }
	  
       echo "Dish not found";
	   }
 

  $dishName = $_POST['dishName'] ?? '' ; 
  $expectedPrice = $_POST['expectedPrice'] ?? '';
  if($dishName && $expectedPrice){
  $success = getKFCDishes($dishName, $expectedPrice);
  echo $success;
  }

$asin = $_POST['asin'] ?? '';
if ($asin) {
$price = getAmazonPrice($asin);
if ($price != null){
echo "The current Amazon price is: $" . $price;
}
}


// $contentPrice = extractPriceFromContent($content);

// echo "Content price: $" . $contentPrice;


// Call API
// $apiPrice = getAmazonPrice($asin);

// Extract price from content
// $contentPrice = getPriceFromContent($content);

// Compare prices 
// if ($apiPrice !== $contentPrice) {
  // echo "Warning! Prices do not match"; 
// }

// Display prices
// echo "API Price:". $apiPrice;
// echo "Content Price: $contentPrice";




?>

