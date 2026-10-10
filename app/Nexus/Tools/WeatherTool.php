<?php

namespace App\Nexus\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class WeatherTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get current weather information and forecast for any city or location.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'location' => $schema->string()->required()->description('City name or location (e.g., "Dhaka", "London, UK")'),
            'unit'     => $schema->string()->description('Temperature unit: celsius or fahrenheit')->default('celsius'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $location = trim($request->get('location'));
        $unit = $request->get('unit', 'celsius') === 'fahrenheit' ? 'u' : 'm';

        try {
            $encoded = urlencode($location);
            $response = Http::timeout(10)->get("https://wttr.in/{$encoded}", [
                'format' => 'j1',
                'u' => $unit === 'u' ? '' : null,
            ]);

            if ($response->failed()) {
                return "Could not get weather for \"{$location}\". Please try a different location.";
            }

            $data = $response->json();

            if (!isset($data['current_condition'][0])) {
                return "No weather data available for \"{$location}\".";
            }

            $current = $data['current_condition'][0];
            $area    = $data['nearest_area'][0] ?? [];
            $areaName = $area['areaName'][0]['value'] ?? $location;
            $country  = $area['country'][0]['value'] ?? '';

            $tempC = $current['temp_C'];
            $tempF = $current['temp_F'];
            $feels = $unit === 'u' ? $current['FeelsLikeF'].'°F' : $current['FeelsLikeC'].'°C';
            $temp  = $unit === 'u' ? "{$tempF}°F" : "{$tempC}°C";
            $desc  = $current['weatherDesc'][0]['value'] ?? 'N/A';
            $humidity = $current['humidity'];
            $wind    = $current['windspeedKmph'];

            $result  = "### 🌤 Weather in {$areaName}, {$country}\n";
            $result .= "**Temperature:** {$temp} (Feels like {$feels})\n";
            $result .= "**Condition:** {$desc}\n";
            $result .= "**Humidity:** {$humidity}%\n";
            $result .= "**Wind Speed:** {$wind} km/h\n";

            // 3-day forecast
            if (!empty($data['weather'])) {
                $result .= "\n**3-Day Forecast:**\n";
                foreach (array_slice($data['weather'], 0, 3) as $day) {
                    $date    = $day['date'];
                    $maxTemp = $unit === 'u' ? $day['maxtempF'].'°F' : $day['maxtempC'].'°C';
                    $minTemp = $unit === 'u' ? $day['mintempF'].'°F' : $day['mintempC'].'°C';
                    $dayDesc = $day['hourly'][4]['weatherDesc'][0]['value'] ?? 'N/A';
                    $result .= "- **{$date}:** {$dayDesc}, High {$maxTemp} / Low {$minTemp}\n";
                }
            }

            return $result;
        } catch (\Exception $e) {
            return "Weather error: " . $e->getMessage();
        }
    }
}
