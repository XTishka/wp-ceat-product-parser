# Ceat Product Parser

Starter scaffold for the **Ceat Product Parser** WordPress plugin. The plugin follows an object-oriented architecture and uses a lightweight PSR-4 style autoloader without Composer.

## Structure

- `ceat-product-parser.php` – main plugin bootstrap, loads the autoloader and kicks off the plugin lifecycle.
- `includes/autoloader.php` – registers the custom autoloader that maps the `CeatProductParser` namespace to the `src/` directory.
- `src/` – houses namespaced PHP classes.
  - `Plugin.php` – singleton entry point that registers service providers and WordPress hooks.
  - `Container/Container.php` – minimal container for shared services and providers.
  - `Contracts/ServiceProviderInterface.php` – contract for service providers.
  - `Providers/CoreServiceProvider.php` – example provider that loads the text domain on `init`.
  - `Providers/AdminServiceProvider.php` – wires the admin Tools page and shares services.
  - `Services/HtmlParser.php` – reusable service that fetches remote pages and extracts elements with DOMXPath.
  - `Admin/ToolsPage.php` – renders the form and displays parsed HTML snippets.
- `languages/` – placeholder for translation files.

## Getting Started

1. Activate the plugin from the WordPress admin screen.
2. Visit Tools → Ceat Product Parser to test the URL and selector fields (default selector targets `class="pdp-main"`).
3. Add new service providers under `src/Providers` implementing `ServiceProviderInterface` and register them via `Plugin::register_provider()`.
4. Place additional classes anywhere under `src/` while keeping the `CeatProductParser` namespace prefix. The autoloader converts the namespace to paths automatically.

This scaffold is ready to be extended with parsing logic, admin pages, REST endpoints, or CLI commands as needed.
