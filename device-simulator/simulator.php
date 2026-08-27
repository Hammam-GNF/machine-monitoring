<?php

$apiUrl = getenv('SENSOR_API_URL') ?: 'http://127.0.0.1:8000/api/sensor-data';
$interval = (int) (getenv('SIMULATOR_INTERVAL') ?: 5);

$devices = json_decode(
    file_get_contents(__DIR__.'/devices.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);

if ($devices === []) {
    fwrite(STDERR, "No devices configured.\n");
exit(1);
}

echo "IoT device simulator started.\n";
echo "Devices: ".count($devices)."\n";
echo "Interval: {$interval}s\n\n";

while (true) {
    foreach ($devices as $device) {
        $payload = [
            'event_id' => sprintf(
                '%04x%04x-%04x-%04x-%04x-%012x',
                random_int(0, 0xffff),
                random_int(0, 0xffff),
                random_int(0, 0xffff),
                random_int(0x4000, 0x4fff),
                random_int(0x8000, 0xbfff),
                random_int(0, 0xffffffffffff)
            ),
            'machine_id' => $device['machine_id'],
            'sensor_id' => $device['sensor_id'],
            'status' => random_int(0, 1) ? 'ON' : 'OFF',
            'temperature' => round(random_int(5000, 9500) / 100, 2),
            'output' => random_int(80, 150),
            'recorded_at' => date('Y-m-d H:i:s'),
        ];

        $ch = curl_init($apiUrl);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            echo "FAILED machine {$device['machine_id']}: {$error}\n";
            continue;
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            echo "SENT machine {$device['machine_id']} / sensor {$device['sensor_id']}\n";
        } else {
            echo "FAILED machine {$device['machine_id']} / sensor {$device['sensor_id']} [HTTP {$httpCode}]\n";
        }
    }

    echo "\n";
    sleep($interval);
}
