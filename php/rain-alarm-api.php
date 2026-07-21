<?php

// Return JSON response
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);


// Send API response
function output($message, $status = Null){

	$response = [
		'status' => isset($status) ? $status : 'error',
		is_array($message) ? 'data' : 'message' => $message
	];

	echo json_encode($response);
	die;
}


function getRadarReadingAtCenter($image): array
{
    $width  = imagesx($image);
    $height = imagesy($image);

    $centerX = intdiv($width, 2);
    $centerY = intdiv($height, 2);

    $dbzValues = [];

    for ($y = $centerY - 2; $y <= $centerY + 2; $y++) {

        for ($x = $centerX - 2; $x <= $centerX + 2; $x++) {

            if ($x < 0 || $y < 0 || $x >= $width || $y >= $height) {
                continue;
            }

            $index = imagecolorat($image, $x, $y);
            $colors = imagecolorsforindex($image, $index);

            $result = classifyColor([
                'red'   => $colors['red'],
                'green' => $colors['green'],
                'blue'  => $colors['blue'],
                'alpha' => $colors['alpha'] ?? 0
            ]);

            $dbzValues[] = $result['dbz'];
        }
    }


    if (!$dbzValues) {
        return [
            'dbz' => null,
            'severity' => 'Unknown'
        ];
    }


    // Remove invalid values
    $dbzValues = array_filter($dbzValues, function($v){
        return $v !== null;
    });


    if (!$dbzValues) {
        return [
            'dbz' => null,
            'severity' => 'None'
        ];
    }


    // Median dBZ
    sort($dbzValues, SORT_NUMERIC);

    $dbz = $dbzValues[intdiv(count($dbzValues), 2)];


    // Convert dBZ to category
    if ($dbz < 15) {

        $severity = 'None';

    } elseif ($dbz < 35) {

        $severity = 'Light';

    } elseif ($dbz < 45) {

        $severity = 'Moderate';

    } elseif ($dbz < 55) {

        $severity = 'Heavy';

    } else {

        $severity = 'Hail';

    }


    return [
        'dbz' => $dbz,
        'severity' => $severity
    ];
}


function classifyColor(array $rgb): array
{
    static $palette = [
        // dBZ => '#RRGGBBAA'
        -32=>'#00000000',-31=>'#00000000',-30=>'#00000000',-29=>'#00000000',
        -28=>'#00000000',-27=>'#00000000',-26=>'#00000000',-25=>'#00000000',
        -24=>'#00000000',-23=>'#00000000',-22=>'#00000000',-21=>'#00000000',
        -20=>'#00000000',-19=>'#00000000',-18=>'#00000000',-17=>'#00000000',
        -16=>'#00000000',-15=>'#00000000',-14=>'#00000000',-13=>'#00000000',
        -12=>'#00000000',-11=>'#00000000',
        -10=>'#63615914', -9=>'#66635a19', -8=>'#69665c1e', -7=>'#6c685d24',
         -6=>'#6f6b5f29', -5=>'#726e612e', -4=>'#75706234', -3=>'#78736439',
         -2=>'#7c75653e', -1=>'#7f786744',  0=>'#827b6949',  1=>'#857d6a4e',
          2=>'#88806c54',  3=>'#8b826d59',  4=>'#8e856f5e',  5=>'#92887164',
          6=>'#9e93756e',  7=>'#aa9e7978',  8=>'#b6a97e82',  9=>'#c2b4828c',
         10=>'#cec08796', 11=>'#d2c48ba0', 12=>'#d6c88faa', 13=>'#dacc93b4',
         14=>'#ded097be', 15=>'#88ddeeff', 16=>'#6cd1ebff', 17=>'#51c5e8ff',
         18=>'#36bae5ff', 19=>'#1baee2ff', 20=>'#00a3e0ff', 21=>'#009ad5ff',
         22=>'#0091caff', 23=>'#0088bfff', 24=>'#007fb4ff', 25=>'#0077aaff',
         26=>'#0070a3ff', 27=>'#00699cff', 28=>'#006295ff', 29=>'#005b8eff',
         30=>'#005588ff', 31=>'#005180ff', 32=>'#004e78ff', 33=>'#004a70ff',
         34=>'#004768ff', 35=>'#ffee00ff', 36=>'#ffe000ff', 37=>'#ffd200ff',
         38=>'#ffc500ff', 39=>'#ffb700ff', 40=>'#ffaa00ff', 41=>'#ff9f00ff',
         42=>'#ff9500ff', 43=>'#ff8b00ff', 44=>'#ff8100ff', 45=>'#ff4400ff',
         46=>'#f23600ff', 47=>'#e62800ff', 48=>'#d91b00ff', 49=>'#cd0d00ff',
         50=>'#c10000ff', 51=>'#a80000ff', 52=>'#8f0000ff', 53=>'#760000ff',
         54=>'#5d0000ff', 55=>'#ffaaffff', 56=>'#ff9fffff', 57=>'#ff95ffff',
         58=>'#ff8bffff', 59=>'#ff81ffff', 60=>'#ff77ffff', 61=>'#ff6cffff',
         62=>'#ff62ffff', 63=>'#ff58ffff', 64=>'#ff4effff', 65=>'#ffffffff',
         66=>'#ffffffff', 67=>'#ffffffff', 68=>'#ffffffff', 69=>'#ffffffff',
         70=>'#ffffffff', 71=>'#ffffffff', 72=>'#ffffffff', 73=>'#ffffffff',
         74=>'#ffffffff', 75=>'#00ff00ff', 76=>'#00ff00ff', 77=>'#00ff00ff',
         78=>'#00ff00ff', 79=>'#00ff00ff', 80=>'#00ff00ff', 81=>'#00ff00ff',
         82=>'#00ff00ff', 83=>'#00ff00ff', 84=>'#00ff00ff', 85=>'#00ff00ff',
         86=>'#00ff00ff', 87=>'#00ff00ff', 88=>'#00ff00ff', 89=>'#00ff00ff',
         90=>'#00ff00ff', 91=>'#00ff00ff', 92=>'#00ff00ff', 93=>'#00ff00ff',
         94=>'#00ff00ff', 95=>'#00ff00ff'
    ];

    $bestDbz = null;
    $bestDistance = PHP_FLOAT_MAX;

    foreach ($palette as $dbz => $hex) {

        $hex = ltrim($hex, '#');

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $a = hexdec(substr($hex, 6, 2));

        $dr = $rgb['red']   - $r;
        $dg = $rgb['green'] - $g;
        $db = $rgb['blue']  - $b;
        $da = $rgb['alpha'] - $a;

        // Weighted RGBA distance
        $distance =
            $dr * $dr +
            $dg * $dg +
            $db * $db +
            ($da * $da * 0.25);

        if ($distance < $bestDistance) {
            $bestDistance = $distance;
            $bestDbz = $dbz;
        }
    }

    if ($bestDbz < 15) {
        $level = 'None';
    } elseif ($bestDbz < 35) {
        $level = 'Light';
    } elseif ($bestDbz < 45) {
        $level = 'Moderate';
    } elseif ($bestDbz < 55) {
        $level = 'Heavy';
    } else {
        $level = 'Hail';
    }

    return [
        'dbz'      => $bestDbz,
        'level'    => $level,
        'distance' => sqrt($bestDistance)
    ];
}


// Get latest radar timestamp
function getLastUpdate() {

	$url = 'https://api.rainviewer.com/public/weather-maps.json';


	// Download radar metadata
	$jsonString = @file_get_contents($url);


	if ($jsonString === false) {
		output('Error fetching the JSON data.');
	}


	// Decode JSON response
	$data = json_decode($jsonString, true);


	if (json_last_error() !== JSON_ERROR_NONE) {
		output('Error decoding JSON: ' . json_last_error_msg());
	}


	// Check radar data exists
	if (!isset($data['radar']['past'])) {
		output("'radar' or 'past' key is missing in the JSON data.");
	}


	// Get newest radar frame
	$lastPast = end($data['radar']['past']);


	return [
		'path' => $lastPast['path'],
		'time' => $lastPast['time']
	];
}


function getRadarTileColor(){

    $coords = getCoordinates();

    $lastUpdate = getLastUpdate();

    $imageUrl = "https://tilecache.rainviewer.com{$lastUpdate['path']}/256/7/{$coords['lat']}/{$coords['lon']}/2/1_1.png";

    $imagePath = 'downloaded_image.png';

    $imageContent = @file_get_contents($imageUrl);

    if ($imageContent === false) {
        output('Failed to download radar image.');
    }

    file_put_contents($imagePath, $imageContent);


    $image = imagecreatefrompng($imagePath);

    if (!$image) {
        output('Failed to create image.');
    }


    $radar = getRadarReadingAtCenter($image);



    unlink($imagePath);


    return [
        'rain' => ($radar['dbz'] !== null && $radar['dbz'] >= 15) ? 'True' : 'False',
        'dbz' => $radar['dbz'],
        'rr_val' => dbzToRainRate($radar['dbz']),
        'severity' => $radar['severity'],
        'lastUpdate' => $lastUpdate['time'],
        'generated' => time()
    ];
}

// Extract coordinates from request URL
function getCoordinates(){

	$path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';


	// Split URL path
	$coords = explode('/', trim(parse_url($path, PHP_URL_PATH), '/'));


	// Get latitude and longitude
	$lat = isset($coords[1]) ? $coords[1] : '';
	$lon = isset($coords[2]) ? $coords[2] : '';




	// Remove invalid characters
	$lat = preg_replace('/[^0-9.]+/', '', $lat);
	$lon = preg_replace('/[^0-9.]+/', '', $lon);


	// Validate coordinates
	if ($lat !== '' && $lon !== '' ){

		return [
			'lat' => $lat,
			'lon' => $lon 
		];

	}else{

		output('No coordinates provided, or not in right format.');

	}
}

//Calculate rain intensity
function dbzToRainRate(?float $dbz): float
{
    if ($dbz === null || $dbz < 0) {
        return 0.0;
    }

    $z = pow(10, $dbz / 10);

    // Rain rate in mm/h (similar to ARSO rr_val)
    return round(pow($z / 200, 1 / 1.6), 1);
}

// Redis based API rate limiter
class RateLimiter {

    private $redis;
    private $rateLimit;
    private $timeWindow;
    private $prefix;


    // Initialize Redis connection
    public function __construct($rateLimit, $timeWindow, $prefix = 'rate_limit:') {

        $this->redis = new Redis();

        $this->redis->connect('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');

        $this->rateLimit = $rateLimit;
        $this->timeWindow = $timeWindow;
        $this->prefix = $prefix;
    }


    // Check request limit
    public function isRateLimited($clientId) {

        $key = $this->prefix . $clientId;


        // Get current request count
        $currentCount = $this->redis->get($key);


        // Create new counter
        if ($currentCount === false) {

            $this->redis->set(
                $key,
                1,
                $this->timeWindow
            );

            return false;
        }


        // Increase counter if below limit
        if ($currentCount < $this->rateLimit) {

            $this->redis->incr($key);

            return false;
        }


        // Block exceeded requests
        return true;
    }
}



// Get client IP
$clientIp = $_SERVER['REMOTE_ADDR'];


// Allow 12 requests per hour
$rateLimiter = new RateLimiter(12, 3600);


// Check rate limit
if ($rateLimiter->isRateLimited($clientIp)) {

    output('Too Many Requests.');

}


// Generate final JSON response
output(
    getRadarTileColor(),
    'success'
);

?>