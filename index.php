<?php

require __DIR__ . '/vendor/autoload.php';

use chillerlan\QRCode\{QRCode, QROptions};

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function generatePixString($key, $name, $city, $amount, $description = '') {
    // Basic PIX data
    $name = substr($name, 0, 25);
    $city = substr($city, 0, 15);
    $description = substr($description, 0, 50);
    $amount = number_format($amount, 2, '.', '');

    // Build merchant account info
    $gui = "br.gov.bcb.pix";
    $merchantAccount = "00" . sprintf("%02d", strlen($gui)) . $gui;
    $merchantAccount .= "01" . sprintf("%02d", strlen($key)) . $key;

    // Build additional data field
    $additionalData = "05" . sprintf("%02d", strlen($description)) . $description;

    // Build the payload array
    $payload = [
        "00" => "01",                     // Payload Format Indicator
        "26" => $merchantAccount,         // Merchant Account Information
        "52" => "0000",                   // Merchant Category Code
        "53" => "986",                    // Transaction Currency (BRL)
        "54" => $amount,                  // Transaction Amount
        "58" => "BR",                     // Country Code
        "59" => $name,                    // Merchant Name
        "60" => $city,                    // Merchant City
        "62" => $additionalData,          // Additional Data Field
    ];

    // Encode payload
    $pixString = "";
    foreach ($payload as $id => $value) {
        $pixString .= $id . sprintf("%02d", strlen($value)) . $value;
    }

    // Add CRC16
    $pixString .= "6304";
    $crc = crc16($pixString);
    return $pixString . strtoupper(sprintf("%04X", $crc));
}

function crc16($str) {
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($str); $i++) {
        $crc ^= (ord($str[$i]) << 8);
        for ($j = 0; $j < 8; $j++) {
            if ($crc & 0x8000) {
                $crc = ($crc << 1) ^ 0x1021;
            } else {
                $crc = $crc << 1;
            }
            $crc &= 0xFFFF;
        }
    }
    return $crc;
}

function formatCurrencyValue($value) {
    // Remove 'R$' prefix and trim whitespace
    $value = trim(str_replace('R$', '', $value));

    // Replace comma with dot for decimal separator
    $value = str_replace(',', '.', $value);

    // Remove thousand separators (dots)
    $value = str_replace('.', '', substr($value, 0, -3)) . substr($value, -3);

    return (float) $value;
}

try {
    // Format and validate amount
    $amount = isset($_GET['amount']) ? formatCurrencyValue($_GET['amount']) : 0;
    if ($amount <= 0) {
        throw new Exception("Valor inválido");
    }

    // Generate PIX string
    $pixString = generatePixString(
        $_ENV['PIX_KEY'],
        $_ENV['MERCHANT_NAME'],
        $_ENV['MERCHANT_CITY'],
        $amount,
        $_ENV['DESCRIPTION']
    );

    // QR Code options
    $options = new QROptions([
        'version'      => QRCode::VERSION_AUTO,
        'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel'     => QRCode::ECC_L,
        'scale'        => 5,
        'imageBase64'  => false,
    ]);

    // Generate and output QR Code
    header('Content-Type: image/png');
    echo (new QRCode($options))->render($pixString);

} catch (Exception $e) {
    header('Content-Type: text/plain; charset=utf-8');
    die("Erro: " . $e->getMessage());
}
