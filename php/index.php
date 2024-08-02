<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

function output($message, $status = Null){
	
	//Finalize output
	$response = [
		'status' => isset($status) ? $status : 'error',
		is_array($message) ? 'data' : 'message' => $message
	];
	
	echo json_encode($response);
	die;

}

function getColorAtCenterFromUrl($image) {

    // Get the image dimensions
    $width = imagesx($image);
    $height = imagesy($image);

    // Calculate the center point
    $centerX = intval($width / 2);
    $centerY = intval($height / 2);

    // Get the color index at the center point
    $colorIndex = imagecolorat($image, $centerX, $centerY);

    // Get the colors from the color index, including alpha channel
    $colors = imagecolorsforindex($image, $colorIndex);

    // Return the color as an associative array including alpha
    return [
        'red' => $colors['red'],
        'green' => $colors['green'],
        'blue' => $colors['blue'],
        'alpha' => isset($colors['alpha']) ? $colors['alpha'] : 0
    ];
}

function classifyColor($rgb) {

    $red = $rgb['red'];
    $green = $rgb['green'];
    $blue = $rgb['blue'];
    $alpha = $rgb['alpha'];

    if ($alpha == 127) { // Fully transparent pixel in GD library has alpha value 127
        return 'None';
    } elseif ($red > $green && $red > $blue) {
        return 'Hail';
    } elseif ($green > $red && $green > $blue) {
        return 'Light';
    } elseif ($blue > $red && $blue > $green) {
        return 'Moderate';
    } elseif ($red > $green && $blue > $green) {
        return 'Heavy';
	} elseif ($red == $green && $blue == $green) {
        return 'Light';
    } else {
        return 'Unknown';
    }
}

function getLastUpdate() {
	// URL of the JSON data
	$url = 'https://api.rainviewer.com/public/weather-maps.json';


	// Fetch the JSON data from the URL
	$jsonString = @file_get_contents($url);

	// Check if the fetch was successful
	if ($jsonString === false) {
		output('Error fetching the JSON data.');
	}

	// Decode the JSON string
	$data = json_decode($jsonString, true);

	// Check if JSON decoding was successful
	if (json_last_error() !== JSON_ERROR_NONE) {
		output('Error decoding JSON: ' . json_last_error_msg());
	}

	// Check if 'radar' and its 'past' array exist in the JSON
	if (!isset($data['radar']['past'])) {
		output("'radar' or 'past' key is missing in the JSON data.");
	}
	
	// Get the last element of 'past' array
	$lastPast = end($data['radar']['past']);
	$lastPastTime = $lastPast['time'];

	return $lastPastTime;

}

function getRadarTileColor(){
	$coords = getCoordinates();
	$lastUpdate = getLastUpdate();
	
	// Construct the URL with the defined variables
	$imageUrl = "https://tilecache.rainviewer.com/v2/radar/{$lastUpdate}/256/9/{$coords['lat']}/{$coords['lon']}/1/1_1.png";

	// Specify the path where the image will be saved
	$imagePath = 'downloaded_image.png';

	// Download the image
	$imageContent = file_get_contents($imageUrl);
	if ($imageContent === false) {
		output('Failed to download the image from URL: ' . $imageUrl);
	}
	file_put_contents($imagePath, $imageContent);

	// Check if the image was successfully downloaded
	if (!file_exists($imagePath)) {
		output('Failed to download the image.');
	}

	// Load the image
	$image = imagecreatefrompng($imagePath);
	if (!$image) {
		output('Failed to create image from PNG.');
	}
	
	// Get center color
	$centerColor = getColorAtCenterFromUrl($image);
	$colorClassification = classifyColor ($centerColor);
	
	// Free up memory
    imagedestroy($image);
    unlink($imagePath); // Delete the temporary file
	
	return [
		'rain' => $colorClassification != 'None' ? 'True' : 'False',
		'severety' => $colorClassification,
		'lastUpdate' => $lastUpdate,
		'generated' => time()
	];
}

function getCoordinates(){
	// Extracting the path from the current request URL
	$path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

	// Remove query string (if any) and split the path into parts
	$coords = explode('/', trim(parse_url($path, PHP_URL_PATH), '/'));

	$lat = isset($coords[1]) ? $coords[1] : '';
	$lon = isset($coords[2]) ? $coords[2] : '';
	$lat = preg_replace('/[^0-9.]+/', '', $lat);
	$lon = preg_replace('/[^0-9.]+/', '', $lon);

	if ($lat !== '' && $lon !== '' ){
		return [
			'lat' => $lat,
			'lon' => $lon 
		];
	}else{
		output('No coordinates provided, or not in right format.');
	}
}

class RateLimiter {
    private $redis;
    private $rateLimit;
    private $timeWindow;
    private $prefix;

    public function __construct($rateLimit, $timeWindow, $prefix = 'rate_limit:') {
        $this->redis = new Redis();
        // Connect to Redis using the Unix socket
        $this->redis->connect('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
        $this->rateLimit = $rateLimit;
        $this->timeWindow = $timeWindow;
        $this->prefix = $prefix;
    }

    public function isRateLimited($clientId) {
        $key = $this->prefix . $clientId;
        $currentCount = $this->redis->get($key);

        if ($currentCount === false) {
            // Set the key with an expiration time
            $this->redis->set($key, 1, $this->timeWindow);
            return false;
        }

        if ($currentCount < $this->rateLimit) {
            $this->redis->incr($key);
            return false;
        }

        return true;
    }
}

// RateLimiter
$clientIp = $_SERVER['REMOTE_ADDR']; // Use client's IP address or other unique identifier
$rateLimiter = new RateLimiter(12, 3600); // 12 requests per hour aka every 5 minues

if ($rateLimiter->isRateLimited($clientIp)) {
    output('Too Many Requests.');
}

// Generate Rain Status Json
output(getRadarTileColor(), 'success');



?>
