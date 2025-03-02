# Modern WooCommerce Cart & Checkout

A WordPress plugin that enhances the WooCommerce cart and checkout experience with a modern, user-friendly design and improved functionality.

## Features

- Modern, clean design for cart and checkout pages
- AJAX-powered cart updates without page reload
- Responsive layout that works on all devices
- Custom templates for cart and checkout pages
- Improved user experience with instant quantity updates
- Modern styling that overrides default WooCommerce design

## Installation

1. Download the plugin files
2. Upload the plugin folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Make sure WooCommerce is installed and activated

## Requirements

- WordPress 5.0 or higher
- WooCommerce 6.0 or higher
- PHP 7.2 or higher

## File Structure

modern-woocommerce-cart-checkout/ 
├── assets/ │ 
├── css/ │ 
│ └── style.css │ 
└── js/ │ 
└── script.js 
├── templates/ │ 
├── cart/ │ 
│ └── cart.php │ 
└── checkout/ │ 
└── form-checkout.php 
└── modern-woocommerce-cart-checkout.php


## Usage

After activation, the plugin automatically replaces the default WooCommerce cart and checkout templates with its modern versions. No additional configuration is needed.

## Development

The plugin uses:
- jQuery for AJAX functionality
- Custom CSS for styling
- WooCommerce hooks and filters for template overrides

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

[GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html)
