<?php


// Путь к файлу .ovpn
$ovpnFile = '/var/www/api.ladyzenko.pro/tg/ozna/vpn/stockholm.ovpn';

// 1. Запуск OpenVPN
$command = "openvpn --config " . escapeshellarg($ovpnFile) . " --daemon"; // Запуск в фоновом режиме
exec($command, $output, $returnVar);

// Проверка успешности запуска OpenVPN\
echo("<pre>");
var_dump($command);
var_dump($returnVar);
if ($returnVar === 0) {
    echo "OpenVPN запущен\n";
    
    // Даём время на установку соединения (зависит от сервера, обычно 5-10 секунд)
    sleep(10);

    // 2. Выполнение HTTP-запроса через VPN с помощью cURL
    $url = 'https://api.example.com/data';
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    // Дополнительные заголовки, если нужны
    $headers = ['Content-Type: application/json'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Выполнение запроса
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Ошибка cURL: ' . curl_error($ch);
    } else {
        echo 'Ответ: ' . $response;
    }

    curl_close($ch);

    // 3. (Опционально) Остановка OpenVPN после выполнения запроса
    exec('pkill openvpn');
    echo "\nOpenVPN остановлен\n";
} else {
    echo "Ошибка при запуске OpenVPN\n";
}

echo("end");

return;

// Load composer
require __DIR__ . '/vendor/autoload.php';

use GrokPHP\Client\Clients\GrokClient;
use GrokPHP\Client\Config\GrokConfig;
use GrokPHP\Client\Config\ChatOptions;
use GrokPHP\Client\Enums\Model;

echo("test!23");

$proxy = "cagsan:jw22wdw@108.61.25.223:34792";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_PROXY, $proxy);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_HEADER, 1);
$curl_scraped_page = curl_exec($ch);
curl_close($ch);

return;
// Initialize the client
$config = new GrokConfig('xai-cHb9kO337kfWzpTHAPV7uHSPcQwl9djhAJhvSD5rvd9zdl9KGPIoPdNxkrH6o5hCB0pAgKhwIau2OS5s');
$client = new GrokClient($config);

// Define messages
$messages = [
    ['role' => 'system', 'content' => 'You are an AI assistant and best xml report creator.'],
    ['role' => 'user', 'content' => 'Привет! Как дела, умеешь отчёты составлять?']
];

// Call API
$options = new ChatOptions(model: Model::GROK_2, temperature: 0.7, stream: false);
$response = $client->chat($messages, $options);

echo "AI Response: " . $response['choices'][0]['message']['content'];


echo("done");

?>