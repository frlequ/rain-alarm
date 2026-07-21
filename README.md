# Rain Alarm API V2 🌧️

A simple PHP-based rain radar API that provides current rain radar information based on GPS coordinates.

The API accepts latitude and longitude values and returns radar information as JSON. It is designed for easy integration with **Home Assistant REST sensors**, IoT systems, and automation platforms.

## Features

* 🌍 Coordinate-based rain radar lookup
* 🌧️ Current rain detection
* 📡 Radar reflectivity data (`dBZ`)
* 💧 Rain intensity values
* 🏠 Native Home Assistant REST sensor support
* ⚡ Lightweight JSON API

---

# API Usage

## Endpoint

```
GET https://your-domain.com/rainAlarm/{latitude}/{longitude}
```

Example:

```
https://your-domain.com/rainAlarm/46.0100237/14.3693499
```

Where:

| Parameter | Description   |
| --------- | ------------- |
| latitude  | GPS latitude  |
| longitude | GPS longitude |

Example location:

```
Latitude: 46.0100237
Longitude: 14.3693499
```

---

# API Response

The API returns JSON data.

Example:

```json
{
  "data": {
    "rain": true,
    "severety": 2,
    "dbz": 35,
    "rr_val": 1.8,
    "lastUpdate": "2026-07-21T12:00:00"
  }
}
```

---

# JSON Fields

| Field        | Type    | Description                   |
| ------------ | ------- | ----------------------------- |
| `rain`       | boolean | Indicates if rain is detected |
| `severety`   | number  | Rain severity level           |
| `dbz`        | number  | Radar reflectivity value      |
| `rr_val`     | number  | Rain rate estimation          |
| `lastUpdate` | string  | Time of last radar update     |

---

# Home Assistant Integration

Add the following REST sensor to your `configuration.yaml`:

```yaml
sensor:
  - platform: rest
    name: Rain Sensor
    resource: https://your-domain.com/rainAlarm/46.0100237/14.3693499
    method: GET
    timeout: 20

    value_template: >
      {% if value_json is defined and value_json.data is defined %}
        {{ value_json.data.dbz }}
      {% else %}
        unknown
      {% endif %}

    json_attributes_path: $.data
    json_attributes:
      - rain
      - severety
      - dbz
      - rr_val
      - lastUpdate

    scan_interval: 300
```

---

# Home Assistant Sensor Output

The sensor state will contain the radar `dbz` value:

Example:

```
sensor.rain_sensor = 35
```

Attributes:

```yaml
rain: true
severety: 2
dbz: 35
rr_val: 1.8
lastUpdate: "2026-07-21T12:00:00"
```

---

# Rain Detection Binary Sensor

Create a binary sensor based on the API rain value:

```yaml
template:
  - binary_sensor:
      - name: Rain Detected
        device_class: moisture
        state: >
          {{ state_attr('sensor.rain_sensor', 'rain') == true }}
```

Result:

```
ON  = Rain detected
OFF = No rain
```

---

# Example Automation

Send a notification when rain starts:

```yaml
automation:
  - alias: Rain Alert
    trigger:
      - platform: state
        entity_id: binary_sensor.rain_detected
        to: "on"

    action:
      - service: notify.mobile_app_your_phone
        data:
          message: "Rain detected by radar."
```

---

# Update Interval

Radar information normally changes every few minutes.

Recommended polling interval:

```yaml
scan_interval: 300
```

This means Home Assistant updates the sensor every 5 minutes.

For faster alerts:

```yaml
scan_interval: 120
```

---

# Testing the API

You can test the API using:

```bash
curl https://your-domain.com/rainAlarm/46.0100237/14.3693499
```

Expected response:

```json
{
  "data": {
    "rain": false,
    "severety": 0,
    "dbz": 0,
    "rr_val": 0,
    "lastUpdate": "2026-07-21T12:00:00"
  }
}
```

---

# Troubleshooting

## Home Assistant shows `unknown`

Check:

* The API URL is reachable
* The JSON response is valid
* The response contains the `data` object
* Your server allows external requests

---

## API returns empty data

Verify:

* Latitude and longitude are valid
* Radar provider data is available
* The API server has internet access

---

# Possible Automations

This API can be used for:

* 🌧️ Rain notifications
* 🪟 Automatic window closing
* 🌱 Smart irrigation control
* 🏡 Outdoor lighting automation
* 🚗 Garage door protection
* ☀️ Weather dashboards

---

# License

Use according to the license included in this repository.
