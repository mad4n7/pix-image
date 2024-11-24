# PIX QR Code Generator

A PHP library for generating PIX (Brazilian Instant Payment System) QR codes.

## Author

**Arthur Silva (mad4n7)**
- Website: [arthursilva.com](https://arthursilva.com)
- Email: contact@arthursilva.com
- GitHub: [@mad4n7](https://github.com/mad4n7)

## Requirements

- PHP 7.4 or higher
- Composer
- GD extension for PHP (for QR code image generation)

## Installation

1. Clone the repository:
```bash
git clone <your-repository-url>
cd pix-image
```

2. Install dependencies:
```bash
composer install
```

3. Create and configure your `.env` file:
```bash
cp .env.example .env
```

4. Edit the `.env` file with your PIX information:
```
PIX_KEY=your_pix_key
DESCRIPTION="Your description"
MERCHANT_NAME="Your business name"
MERCHANT_CITY="Your city"
```

Note: 
- MERCHANT_NAME is limited to 25 characters
- MERCHANT_CITY is limited to 15 characters
- DESCRIPTION is limited to 50 characters, avoid special characters

## Usage

To generate a PIX QR code, access the URL with an amount parameter:

```
http://your-domain/index.php?amount=349.00
```

This will generate a QR code image that can be scanned by any PIX-compatible banking app in Brazil.

## Running Tests

The project uses PHPUnit for testing. To run the tests:

1. Make sure you have installed dependencies with Composer:
```bash
composer install
```

2. Run the test suite:
```bash
./vendor/bin/phpunit
```

### Test Coverage

The test suite includes:

- PIX string generation validation
- CRC16 calculation verification
- Field length limit checks
- Amount formatting tests
- Required field presence validation

## API Reference

### Main Function

```php
generatePixString($pixKey, $merchantName, $merchantCity, $amount, $description)
```

Parameters:
- `$pixKey`: Your PIX key (CNPJ, CPF, email, or phone number)
- `$merchantName`: Your business name (max 25 characters)
- `$merchantCity`: Your city (max 15 characters)
- `$amount`: Transaction amount (will be formatted to 2 decimal places)
- `$description`: Transaction description (max 50 characters)

Returns a PIX-compliant string that is encoded into a QR code.

## Troubleshooting

Common issues:

1. QR Code not readable:
   - Ensure your PIX key is valid
   - Check that all fields are within length limits
   - Verify the amount format (use decimal point, not comma)

2. Bank app errors:
   - Verify your PIX key is active and registered
   - Ensure the amount is greater than 0
   - Check that all special characters are properly encoded

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the Apache License 2.0 - see the [LICENSE](LICENSE) file for details.

Copyright 2024 Arthur Silva (mad4n7)

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
