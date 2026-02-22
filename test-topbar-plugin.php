<?php
/**
 * Plugin Name: Test Topbar
 * Description: Adds a configurable top bar on every front-end page.
 * Version: 1.3.0
 * Author: Cursor Agent
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('TEST_TOPBAR_PLUGIN_VERSION')) {
    define('TEST_TOPBAR_PLUGIN_VERSION', '1.3.0');
}

/**
 * Return the default CSS for the top bar.
 *
 * @return string
 */
function test_topbar_get_default_css() {
    return <<<'CSS'
#test-topbar-plugin-bar {
    position: fixed;
    top: 12px;
    left: 50%;
    transform: translateX(-50%);
    width: min(720px, calc(100% - 24px));
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.22);
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.84), rgba(30, 41, 59, 0.78));
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    color: #f8fafc;
    font-weight: 700;
    letter-spacing: 0.24em;
    text-transform: uppercase;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    z-index: 99999;
    pointer-events: none;
    overflow: hidden;
}
#test-topbar-plugin-bar::after {
    content: "";
    position: absolute;
    inset: -1px;
    border-radius: inherit;
    background: linear-gradient(120deg, rgba(56, 189, 248, 0.28), rgba(129, 140, 248, 0.28), rgba(236, 72, 153, 0.24));
    opacity: 0.55;
    z-index: -1;
}
#test-topbar-plugin-bar .test-topbar-plugin-label {
    font-size: 0.82rem;
}
body {
    padding-top: 72px;
}
body.admin-bar #test-topbar-plugin-bar {
    top: 44px;
}
body.admin-bar {
    padding-top: 104px;
}
@media screen and (max-width: 782px) {
    body.admin-bar #test-topbar-plugin-bar {
        top: 58px;
    }

    body.admin-bar {
        padding-top: 118px;
    }
}
@media screen and (max-width: 480px) {
    #test-topbar-plugin-bar {
        width: calc(100% - 16px);
        height: 44px;
        border-radius: 12px;
    }

    body {
        padding-top: 68px;
    }

    body.admin-bar {
        padding-top: 112px;
    }
}
CSS;
}

/**
 * Return plugin default settings.
 *
 * @return array<string, mixed>
 */
function test_topbar_get_default_settings() {
    return array(
        'enabled' => 1,
        'text' => 'TEST',
        'css' => test_topbar_get_default_css(),
        'manual_version' => TEST_TOPBAR_PLUGIN_VERSION,
        'version_notes' => "1.3.0 - Added a Versions settings tab with editable release notes.",
    );
}

/**
 * Get saved settings merged with defaults.
 *
 * @return array<string, mixed>
 */
function test_topbar_get_settings() {
    $defaults = test_topbar_get_default_settings();
    $saved = get_option('test_topbar_settings', array());

    if (!is_array($saved)) {
        $saved = array();
    }

    return wp_parse_args($saved, $defaults);
}

/**
 * Normalize CSS input and strip style tags.
 *
 * @param string $css Raw CSS input.
 * @return string
 */
function test_topbar_sanitize_css($css) {
    $css = (string) $css;
    $css = str_replace("\0", '', $css);
    $css = preg_replace('#<\s*/?\s*style[^>]*>#i', '', $css);

    if ($css === null) {
        return '';
    }

    return trim($css);
}

/**
 * Sanitize settings before save.
 *
 * @param array<string, mixed> $input Submitted settings.
 * @return array<string, mixed>
 */
function test_topbar_sanitize_settings($input) {
    $defaults = test_topbar_get_default_settings();
    $current = test_topbar_get_settings();
    $output = wp_parse_args($current, $defaults);

    if (!is_array($input)) {
        return $output;
    }

    $active_tab = isset($input['_active_tab']) ? sanitize_key((string) $input['_active_tab']) : 'output';

    if ($active_tab === 'versions') {
        $output['manual_version'] = isset($input['manual_version']) ? sanitize_text_field(wp_unslash((string) $input['manual_version'])) : $output['manual_version'];
        $output['version_notes'] = isset($input['version_notes']) ? sanitize_textarea_field(wp_unslash((string) $input['version_notes'])) : $output['version_notes'];

        return $output;
    }

    $output['enabled'] = empty($input['enabled']) ? 0 : 1;
    $output['text'] = isset($input['text']) ? sanitize_text_field(wp_unslash((string) $input['text'])) : $output['text'];
    $output['css'] = isset($input['css']) ? test_topbar_sanitize_css(wp_unslash((string) $input['css'])) : $output['css'];

    return $output;
}

/**
 * Render the top bar markup.
 */
function test_topbar_render_bar() {
    if (is_admin()) {
        return;
    }

    $settings = test_topbar_get_settings();

    if (empty($settings['enabled'])) {
        return;
    }

    echo '<div id="test-topbar-plugin-bar" role="status" aria-label="Site test banner"><span class="test-topbar-plugin-label">' . esc_html((string) $settings['text']) . '</span></div>';
}
add_action('wp_body_open', 'test_topbar_render_bar');

/**
 * Fallback for themes that do not call wp_body_open.
 */
function test_topbar_render_bar_fallback() {
    if (did_action('wp_body_open')) {
        return;
    }

    test_topbar_render_bar();
}
add_action('wp_footer', 'test_topbar_render_bar_fallback', 1);

/**
 * Output styles for the top bar.
 */
function test_topbar_output_styles() {
    if (is_admin()) {
        return;
    }

    $settings = test_topbar_get_settings();

    if (empty($settings['enabled'])) {
        return;
    }

    $css = test_topbar_sanitize_css((string) $settings['css']);

    if ($css === '') {
        return;
    }

    ?>
    <style id="test-topbar-plugin-styles">
        <?php echo $css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </style>
    <?php
}
add_action('wp_head', 'test_topbar_output_styles');

/**
 * Register plugin settings and fields.
 */
function test_topbar_register_settings() {
    register_setting(
        'test_topbar_settings_group',
        'test_topbar_settings',
        array(
            'type' => 'array',
            'sanitize_callback' => 'test_topbar_sanitize_settings',
            'default' => test_topbar_get_default_settings(),
        )
    );

    add_settings_section(
        'test_topbar_main_section',
        'Front-end output',
        'test_topbar_main_section_description',
        'test-topbar-settings-output'
    );

    add_settings_field(
        'test_topbar_enabled',
        'Enable top bar',
        'test_topbar_enabled_field',
        'test-topbar-settings-output',
        'test_topbar_main_section'
    );

    add_settings_field(
        'test_topbar_text',
        'Top bar text',
        'test_topbar_text_field',
        'test-topbar-settings-output',
        'test_topbar_main_section'
    );

    add_settings_field(
        'test_topbar_css',
        'Top bar CSS',
        'test_topbar_css_field',
        'test-topbar-settings-output',
        'test_topbar_main_section'
    );

    add_settings_section(
        'test_topbar_versions_section',
        'Version details',
        'test_topbar_versions_section_description',
        'test-topbar-settings-versions'
    );

    add_settings_field(
        'test_topbar_code_version',
        'Code version',
        'test_topbar_code_version_field',
        'test-topbar-settings-versions',
        'test_topbar_versions_section'
    );

    add_settings_field(
        'test_topbar_manual_version',
        'Manual version',
        'test_topbar_manual_version_field',
        'test-topbar-settings-versions',
        'test_topbar_versions_section'
    );

    add_settings_field(
        'test_topbar_version_notes',
        'Version notes',
        'test_topbar_version_notes_field',
        'test-topbar-settings-versions',
        'test_topbar_versions_section'
    );
}
add_action('admin_init', 'test_topbar_register_settings');

/**
 * Add settings page in wp-admin.
 */
function test_topbar_add_settings_page() {
    add_options_page(
        'Test Topbar Settings',
        'Test Topbar',
        'manage_options',
        'test-topbar-settings',
        'test_topbar_render_settings_page'
    );
}
add_action('admin_menu', 'test_topbar_add_settings_page');

/**
 * Print section helper text.
 */
function test_topbar_main_section_description() {
    echo '<p>Control the front-end text and CSS for the top bar. CSS is output exactly as provided.</p>';
}

/**
 * Print versions section helper text.
 */
function test_topbar_versions_section_description() {
    echo '<p>Track plugin version info and maintain manual release notes.</p>';
}

/**
 * Render enabled checkbox field.
 */
function test_topbar_enabled_field() {
    $settings = test_topbar_get_settings();
    ?>
    <label>
        <input type="checkbox" name="test_topbar_settings[enabled]" value="1" <?php checked((int) $settings['enabled'], 1); ?> />
        Show top bar on front-end pages
    </label>
    <?php
}

/**
 * Render text field.
 */
function test_topbar_text_field() {
    $settings = test_topbar_get_settings();
    ?>
    <input
        type="text"
        name="test_topbar_settings[text]"
        value="<?php echo esc_attr((string) $settings['text']); ?>"
        class="regular-text"
        placeholder="TEST"
    />
    <?php
}

/**
 * Render CSS textarea field.
 */
function test_topbar_css_field() {
    $settings = test_topbar_get_settings();
    ?>
    <textarea
        name="test_topbar_settings[css]"
        rows="18"
        class="large-text code"
        spellcheck="false"
    ><?php echo esc_textarea((string) $settings['css']); ?></textarea>
    <p class="description">Use selectors like #test-topbar-plugin-bar and .test-topbar-plugin-label to fully style the output.</p>
    <?php
}

/**
 * Render code version display field.
 */
function test_topbar_code_version_field() {
    ?>
    <code><?php echo esc_html(TEST_TOPBAR_PLUGIN_VERSION); ?></code>
    <p class="description">This comes from the plugin file version constant.</p>
    <?php
}

/**
 * Render manual version field.
 */
function test_topbar_manual_version_field() {
    $settings = test_topbar_get_settings();
    ?>
    <input
        type="text"
        name="test_topbar_settings[manual_version]"
        value="<?php echo esc_attr((string) $settings['manual_version']); ?>"
        class="regular-text"
        placeholder="<?php echo esc_attr(TEST_TOPBAR_PLUGIN_VERSION); ?>"
    />
    <?php
}

/**
 * Render manual version notes field.
 */
function test_topbar_version_notes_field() {
    $settings = test_topbar_get_settings();
    ?>
    <textarea
        name="test_topbar_settings[version_notes]"
        rows="10"
        class="large-text"
        spellcheck="true"
    ><?php echo esc_textarea((string) $settings['version_notes']); ?></textarea>
    <p class="description">Add human-written notes for the current release.</p>
    <?php
}

/**
 * Return the active settings tab key.
 *
 * @return string
 */
function test_topbar_get_active_settings_tab() {
    $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash((string) $_GET['tab'])) : 'output';
    $allowed_tabs = array('output', 'versions');

    if (!in_array($tab, $allowed_tabs, true)) {
        return 'output';
    }

    return $tab;
}

/**
 * Render settings tabs.
 *
 * @param string $active_tab Selected tab.
 */
function test_topbar_render_settings_tabs($active_tab) {
    $tabs = array(
        'output' => 'Front-end Output',
        'versions' => 'Versions',
    );

    echo '<nav class="nav-tab-wrapper">';

    foreach ($tabs as $tab_key => $tab_label) {
        $tab_url = add_query_arg(
            array(
                'page' => 'test-topbar-settings',
                'tab' => $tab_key,
            ),
            admin_url('options-general.php')
        );

        $active_class = $tab_key === $active_tab ? ' nav-tab-active' : '';

        echo '<a href="' . esc_url($tab_url) . '" class="nav-tab' . esc_attr($active_class) . '">' . esc_html($tab_label) . '</a>';
    }

    echo '</nav>';
}

/**
 * Render the plugin settings page markup.
 */
function test_topbar_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $active_tab = test_topbar_get_active_settings_tab();
    $settings_page = $active_tab === 'versions' ? 'test-topbar-settings-versions' : 'test-topbar-settings-output';
    ?>
    <div class="wrap">
        <h1>Test Topbar Settings</h1>
        <?php test_topbar_render_settings_tabs($active_tab); ?>
        <form action="options.php" method="post">
            <?php
            settings_fields('test_topbar_settings_group');
            ?>
            <input type="hidden" name="test_topbar_settings[_active_tab]" value="<?php echo esc_attr($active_tab); ?>" />
            <?php
            do_settings_sections($settings_page);
            submit_button('Save Changes');
            ?>
        </form>
    </div>
    <?php
}
