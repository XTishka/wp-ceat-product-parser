<?php
declare(strict_types=1);

namespace CeatProductParser\Admin;

use CeatProductParser\Services\HtmlParser;
use WP_Error;

final class ToolsPage
{
    private const PAGE_SLUG = 'ceat-product-parser';

    private HtmlParser $parser;

    public function __construct(HtmlParser $parser)
    {
        $this->parser = $parser;
    }

    public function register(): void
    {
        add_management_page(
            __('Ceat Product Parser', 'ceat-product-parser'),
            __('Ceat Product Parser', 'ceat-product-parser'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render']
        );
    }

    public function render(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'ceat-product-parser'));
        }

        $submitted_url      = '';
        $submitted_selector = 'class="pdp-main"';
        $results            = [];
        $error_message      = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['ceat_pp_action'])
            && $_POST['ceat_pp_action'] === 'parse'
        ) {
            check_admin_referer('ceat_pp_parse');

            if (isset($_POST['ceat_pp_url'])) {
                $submitted_url = trim((string) wp_unslash($_POST['ceat_pp_url']));
            }

            if (isset($_POST['ceat_pp_selector'])) {
                $submitted_selector = trim((string) wp_unslash($_POST['ceat_pp_selector']));
            }

            if ($submitted_url !== '') {
                $sanitized_url      = esc_url_raw($submitted_url);
                $sanitized_selector = sanitize_text_field($submitted_selector);

                if ($sanitized_url === '') {
                    $error_message = __('The provided URL is not valid.', 'ceat-product-parser');
                } else {
                    $parsed = $this->parser->extract($sanitized_url, $sanitized_selector);

                    if ($parsed instanceof WP_Error) {
                        $error_message = $parsed->get_error_message();
                    } else {
                        $results = $parsed;

                        if (empty($results)) {
                            $error_message = __('No matching elements were found.', 'ceat-product-parser');
                        }
                    }
                }
            }
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Ceat Product Parser', 'ceat-product-parser'); ?></h1>

            <?php if ($error_message !== '') : ?>
                <div class="notice notice-error"><p><?php echo esc_html($error_message); ?></p></div>
            <?php elseif (! empty($results)) : ?>
                <div class="notice notice-success"><p><?php echo esc_html__('Parsing completed successfully.', 'ceat-product-parser'); ?></p></div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(admin_url('tools.php?page=' . self::PAGE_SLUG)); ?>">
                <?php wp_nonce_field('ceat_pp_parse'); ?>
                <input type="hidden" name="ceat_pp_action" value="parse" />
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="ceat_pp_url"><?php echo esc_html__('Product URL', 'ceat-product-parser'); ?></label>
                        </th>
                        <td>
                            <input
                                type="url"
                                class="regular-text"
                                id="ceat_pp_url"
                                name="ceat_pp_url"
                                value="<?php echo esc_attr($submitted_url); ?>"
                                placeholder="https://example.com/product"
                                required
                            />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ceat_pp_selector"><?php echo esc_html__('Target Element', 'ceat-product-parser'); ?></label>
                        </th>
                        <td>
                            <input
                                type="text"
                                class="regular-text"
                                id="ceat_pp_selector"
                                name="ceat_pp_selector"
                                value="<?php echo esc_attr($submitted_selector); ?>"
                                placeholder="class=&quot;pdp-main&quot;"
                            />
                            <p class="description">
                                <?php echo esc_html__('Provide the element descriptor to parse, e.g., class="pdp-main" or #element-id.', 'ceat-product-parser'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Parse', 'ceat-product-parser')); ?>
            </form>

            <?php if (! empty($results)) : ?>
                <h2><?php echo esc_html__('HTML Output', 'ceat-product-parser'); ?></h2>
                <?php foreach ($results as $index => $html) : ?>
                    <h3><?php echo esc_html(sprintf(__('Match %d', 'ceat-product-parser'), $index + 1)); ?></h3>
                    <pre style="background:#f6f7f7; padding:12px; border:1px solid #ccd0d4; margin-top:0; overflow:auto; max-height:400px;">
<?php echo esc_html($html); ?>
                    </pre>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
