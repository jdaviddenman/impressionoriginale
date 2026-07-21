<?php
/**
 * Plugin Name: IO — IndexNow auto-submit
 * Description: Submit URLs to IndexNow on publish/update. Bulk submits
 *              up to 10 URLs per call (API limit). No UI — fire and forget.
 * Version:     1.0.0
 */
if (!defined('ABSPATH')) exit;

define('IO_INDEXNOW_KEY', '1479359dbca34ecabae3e080d1b96001');
define('IO_INDEXNOW_HOST', 'www.impressionoriginale.com');
define('IO_INDEXNOW_KEY_URL', 'https://www.impressionoriginale.com/1479359dbca34ecabae3e080d1b96001.txt');
define('IO_INDEXNOW_ENDPOINT', 'https://api.indexnow.org/indexnow');
define('IO_INDEXNOW_TRANSIENT', 'io_indexnow_queue');
define('IO_INDEXNOW_DELAY', 60); // seconds between bulk sends

function io_indexnow_get_permalink($post_id) {
    $url = get_permalink($post_id);
    if (!$url || strpos($url, '?p=') !== false) return null;
    // Only real published content
    $post = get_post($post_id);
    if (!$post || $post->post_status !== 'publish') return null;
    if (in_array($post->post_type, array('revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation'))) return null;
    return $url;
}

function io_indexnow_submit($urls) {
    if (empty($urls)) return false;
    $urls = array_values(array_unique((array) $urls));
    if (empty($urls)) return false;

    $body = wp_json_encode(array(
        'host'        => IO_INDEXNOW_HOST,
        'key'         => IO_INDEXNOW_KEY,
        'keyLocation' => IO_INDEXNOW_KEY_URL,
        'urlList'     => $urls,
    ));

    $response = wp_remote_post(IO_INDEXNOW_ENDPOINT, array(
        'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
        'body'        => $body,
        'timeout'     => 15,
        'blocking'    => false, // fire and forget
        'httpversion' => '1.1',
    ));

    if (is_wp_error($response)) {
        error_log('IndexNow submit error: ' . $response->get_error_message());
        return false;
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        error_log('IndexNow submit HTTP ' . $code . ': ' . wp_remote_retrieve_body($response));
        return false;
    }
    return true;
}

// ── Submit on publish ──
function io_indexnow_on_publish($post_id, $post, $update) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    if ($post->post_status !== 'publish') return;
    // Don't submit on update if it's within 5 minutes of creation (double-fire guard)
    if ($update) {
        $created = get_post_time('U', true, $post_id);
        if ($created && (time() - $created) < 300) return;
    }

    $url = io_indexnow_get_permalink($post_id);
    if (!$url) return;

    io_indexnow_submit(array($url));
}
add_action('wp_after_insert_post', 'io_indexnow_on_publish', 20, 3);

// ── Attach to Yoast sitemap update (sends full sitemap on change) ──
// Yoast triggers wp_sitemaps_register event on sitemap rebuild
function io_indexnow_queue_sitemap() {
    // Prevent double-fire within 10 minutes
    $last = get_transient('io_indexnow_last_sitemap');
    if ($last) return;
    set_transient('io_indexnow_last_sitemap', time(), 600);

    // Schedule a one-time cron to do the bulk work
    if (!wp_next_scheduled('io_indexnow_process_queue')) {
        wp_schedule_single_event(time() + 30, 'io_indexnow_process_queue');
    }
}
add_action('wp_sitemaps_register', 'io_indexnow_queue_sitemap', 99);

// ── Process sitemap URLs in batches ──
function io_indexnow_process_queue() {
    // Try WP native sitemap first, fall back to Yoast
    $sitemap_urls = array(
        'https://' . IO_INDEXNOW_HOST . '/wp-sitemap.xml',
        'https://' . IO_INDEXNOW_HOST . '/sitemap_index.xml',
    );

    $all_urls = array();
    foreach ($sitemap_urls as $index_url) {
        $response = wp_remote_get($index_url, array('timeout' => 30));
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) continue;

        $body = wp_remote_retrieve_body($response);
        // Sitemap index — extract sub-sitemaps
        if (preg_match_all('#<loc>([^<]+\.xml[^<]*)</loc>#i', $body, $matches)) {
            foreach ($matches[1] as $sub_url) {
                $sub_resp = wp_remote_get($sub_url, array('timeout' => 30));
                if (is_wp_error($sub_resp)) continue;
                $sub_body = wp_remote_retrieve_body($sub_resp);
                if (preg_match_all('#<loc>([^<]+)</loc>#i', $sub_body, $url_matches)) {
                    foreach ($url_matches[1] as $u) {
                        $all_urls[] = $u;
                    }
                }
            }
        } elseif (preg_match_all('#<loc>([^<]+)</loc>#i', $body, $url_matches)) {
            // Direct URL sitemap
            foreach ($url_matches[1] as $u) {
                $all_urls[] = $u;
            }
        }
        if (!empty($all_urls)) break; // got URLs, stop
    }

    if (empty($all_urls)) return;

    $all_urls = array_unique($all_urls);
    $chunks = array_chunk($all_urls, 10); // IndexNow allows up to 10K, but we're conservative

    foreach ($chunks as $i => $chunk) {
        io_indexnow_submit($chunk);
        if ($i < count($chunks) - 1) {
            sleep(IO_INDEXNOW_DELAY);
        }
    }
}
add_action('io_indexnow_process_queue', 'io_indexnow_process_queue');
