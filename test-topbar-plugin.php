<?php
/**
 * Plugin Name: Test Topbar
 * Description: Adds a fixed top bar that says TEST on every front-end page.
 * Version: 1.1.0
 * Author: Cursor Agent
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the top bar markup.
 */
function test_topbar_render_bar() {
    echo '<div id="test-topbar-plugin-bar" role="status" aria-label="Site test banner"><span class="test-topbar-plugin-label">TEST</span></div>';
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
    </style>
    <?php
}
add_action('wp_head', 'test_topbar_output_styles');
