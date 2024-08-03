# Rain Alarm API
Welcome to the Rain Alarm API! This simple tool uses the Rain Viewer API to give you real-time rain radar data, offering a reliable alternative to traditional weather forcast prediction models.

## Why Use This?
Unlike usual prediction models (like Accuweather api) that can often be inaccurate, Rain Alarm API leverages real-time rain radar data from all around the world. This means you get more precise and dependable rain information right when you need it.


### Example:
https://api.quart.studio/rainAlarm/51.5072/0.1276


## How it works
1. API Request: You send a request to the API with your desired geographic coordinates.
2. Image Retrieval: The API grabs a rain-tile image for your specified location.
3. Image Analysis: The image is analyzed to check for rain and its severity based on color.
4. JSON Response: The API responds with the rain status and severity in a neat JSON format.

## Setting Up the API
1. Edit `index.php:` Change `$this->redis->connect('XXXXXXXXXXXXX');` to your Redis connection details to limit requests.
2. No Redis? No Problem!: Just comment out these lines if you don't have Redis:

```
$clientIp = $_SERVER['REMOTE_ADDR']; // Use client's IP address or other unique identifier
$rateLimiter = new RateLimiter(12, 3600); // 12 requests per hour aka every 5 minues

if ($rateLimiter->isRateLimited($clientIp)) {
    output('Too Many Requests.');
} 
 
```

3. Upload `index.php` and `.htaccess` from php folder to your server. 

## Example JSON Response
Use `https://your-apr-server.com/rainAlarm/LATITUDE/LONGITUDE` to call request. Here's what a typical JSON response looks like:
```
{
  "status": "success",
  "data": {
    "rain": "False",
    "severety": "None",
    "lastUpdate": 1722672600,
    "generated": 1722672644
  }
```

## Setting Up Home Assistant
To display the Rain Alarm in Home Assistant, use the REST sensor template:
```yaml:
  - platform: rest
    name: Rain Alarm
    resource: https://your-apr-server.com/rainAlarm/LATITUDE/LONGITUDE
    method: GET
    value_template: "{{ value_json.data.rain }}"
    json_attributes_path: $.data
    json_attributes:
      - severety
      - lastUpdate
    scan_interval: 300 
```

## Play with contitional cards
![image](https://github.com/user-attachments/assets/dbbd2f59-b316-4687-bf4e-baa7283bcd20)


```
type: conditional
conditions:
  - condition: state
    entity: sensor.rain_alarm
    state: 'True'
card:
  type: custom:mushroom-template-card
  primary: Raining
  secondary: A rain shower is approaching, please bring in the clothes.
  icon: mdi:weather-pouring
  icon_color: white
  entity: sensor.air_quality
  badge_color: red
  tap_action:
    action: navigate
    navigation_path: /dashboard-radar/0
  card_mod:
    style:
      ha-state-icon$: |
        ha-icon {
          scale: 1.5 !important;
        }
      mushroom-shape-icon$: |
        .shape{
          scale: 1 !important;
          background: red !important;
        }
      mushroom-state-info$: |

        .container {
          
          text-align: left;
          
        }
        .container .primary{
          font-family: 'Segoe UI';
          font-size: 14px !important;
          font-weight: 400;
        }
      .: |


        ha-card {
          
          transition: 0s;
          background: 

                  rgba(255,0,0,0.2);


          
          height: 70px !important;


                
        }
   ``` 


### Enjoy!
