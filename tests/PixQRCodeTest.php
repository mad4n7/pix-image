<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class PixQRCodeTest extends TestCase
{
    private $pixKey = '17078612000108';
    private $merchantName = '5lobos';
    private $merchantCity = 'Belo Horizonte';
    private $description = 'Servico';
    private $amount = '1.00';

    /**
     * @test
     */
    public function testGeneratePixString()
    {
        require_once __DIR__ . '/../index.php';

        $pixString = generatePixString(
            $this->pixKey,
            $this->merchantName,
            $this->merchantCity,
            $this->amount,
            $this->description
        );

        // Test string starts with payload format indicator
        $this->assertStringStartsWith('0002', $pixString);

        // Test merchant account info contains PIX GUI
        $this->assertStringContainsString('br.gov.bcb.pix', $pixString);

        // Test merchant account info contains PIX key
        $this->assertStringContainsString($this->pixKey, $pixString);

        // Test contains merchant category code
        $this->assertStringContainsString('52040000', $pixString);

        // Test contains currency code (BRL)
        $this->assertStringContainsString('5303986', $pixString);

        // Test contains country code
        $this->assertStringContainsString('5802BR', $pixString);

        // Test contains merchant name
        $this->assertStringContainsString($this->merchantName, $pixString);

        // Test contains merchant city
        $this->assertStringContainsString($this->merchantCity, $pixString);

        // Test contains description
        $this->assertStringContainsString($this->description, $pixString);

        // Test string ends with CRC16 (4 characters after '6304')
        $this->assertMatchesRegularExpression('/6304[A-F0-9]{4}$/', $pixString);
    }

    /**
     * @test
     */
    public function testCRC16Calculation()
    {
        require_once __DIR__ . '/../index.php';

        $testString = "123456789";
        $crc = crc16($testString);
        
        // Known CRC16-CCITT value for "123456789"
        $expectedCrc = 0x29B1;
        
        $this->assertEquals($expectedCrc, $crc);
    }

    /**
     * @test
     */
    public function testFieldLengthLimits()
    {
        require_once __DIR__ . '/../index.php';

        $longName = str_repeat('A', 30);
        $longCity = str_repeat('B', 20);
        $longDescription = str_repeat('C', 60);

        $pixString = generatePixString(
            $this->pixKey,
            $longName,
            $longCity,
            $this->amount,
            $longDescription
        );

        // Test merchant name is truncated to 25 characters
        $this->assertStringContainsString('59' . sprintf("%02d", 25) . str_repeat('A', 25), $pixString);

        // Test merchant city is truncated to 15 characters
        $this->assertStringContainsString('60' . sprintf("%02d", 15) . str_repeat('B', 15), $pixString);

        // Test description is truncated to 50 characters
        $this->assertStringContainsString('05' . sprintf("%02d", 50) . str_repeat('C', 50), $pixString);
    }

    /**
     * @test
     */
    public function testAmountFormatting()
    {
        require_once __DIR__ . '/../index.php';

        $testCases = [
            '1' => '1.00',
            '1.1' => '1.10',
            '1.23' => '1.23',
            '1000' => '1000.00',
            '1000.1' => '1000.10'
        ];

        foreach ($testCases as $input => $expected) {
            $pixString = generatePixString(
                $this->pixKey,
                $this->merchantName,
                $this->merchantCity,
                $input,
                $this->description
            );

            $this->assertStringContainsString('54' . sprintf("%02d", strlen($expected)) . $expected, $pixString);
        }
    }
}
