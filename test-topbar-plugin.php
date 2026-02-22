<?php
/**
 * Plugin Name: Test Topbar
 * Description: Adds a fixed top bar that says TEST on every front-end page.
 * Version: 1.0.0
 * Author: Cursor Agent
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the top bar markup.
 */
function test_topbar_render_bar() {
    echo '<div id="test-topbar-plugin-bar">TEST</div>';
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
    ?>
    <style id="test-topbar-plugin-styles">
        #test-topbar-plugin-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 40px;
            line-height: 40px;
            background: #111;
            color: #fff;
            text-align: center;
            font-weight: 700;
            letter-spacing: 0.06em;
            z-index: 99999;
        }

        body {
            padding-top: 40px;
        }

        .admin-bar #test-topbar-plugin-bar {
            top: 32px;
        }

        .admin-bar body {
            padding-top: 72px;
        }

        @media screen and (max-width: 782px) {
            .admin-bar #test-topbar-plugin-bar {
                top: 46px;
            }

            .admin-bar body {
                padding-top: 86px;
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'test_topbar_output_styles');
