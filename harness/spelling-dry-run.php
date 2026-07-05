<?php
/**
 * Plugin Name: Spelling Dry Run
 * Description: Phase 1 — dry-run report for PR #30 spelling fixes. Reads the DB,
 *              joins wp_icl_translations, reports match counts + language + sample
 *              URLs for each rule. Writes NOTHING.
 * Version:     1.0
 * Requires Plugins: sitepress-multilingual-cms
 *
 * Install: zip this file → Plugins → Add New → Upload Plugin → Activate.
 * Use:     Tools → Spelling Dry Run → "Run Dry Run"
 * Remove:  deactivate + delete when done.
 *
 * Payload: PR #30 (348 EN errors / 521 pages), runbook from PR #38.
 */

if (!defined('ABSPATH')) { exit; }

define('SDR_SLUG', 'spelling-dry-run');

add_action('admin_menu', function () {
    add_management_page(
        'Spelling Dry Run',
        'Spelling Dry Run',
        'manage_options',
        SDR_SLUG,
        'sdr_render_page'
    );
});

// ── Rules definition (from the runbook) ──────────────────────────────────────

function sdr_rules(): array {
    return [
        // 🟢 Global-safe — unambiguous English; no French word collision
        ['id' =>  1, 'risk' => 'green', 'search' => 'currated',       'replace' => 'curated',       'pattern' => '\\bcurrated\\b',         'type' => 'regex', 'expected' => 20, 'note' => ''],
        ['id' =>  2, 'risk' => 'green', 'search' => 'beautifuly',     'replace' => 'beautifully',    'pattern' => '\\bbeautifuly\\b',       'type' => 'regex', 'expected' => 20, 'note' => ''],
        ['id' =>  3, 'risk' => 'green', 'search' => 'Recylced',       'replace' => 'Recycled',       'pattern' => '\\bRecylced\\b',         'type' => 'regex', 'expected' => 12, 'note' => ''],
        ['id' =>  4, 'risk' => 'green', 'search' => 'Velvelt',        'replace' => 'Velvet',         'pattern' => '\\bVelvelt\\b',          'type' => 'regex', 'expected' => 10, 'note' => ''],
        ['id' =>  5, 'risk' => 'green', 'search' => 'ornates',        'replace' => 'adorns',         'pattern' => '\\bornates\\b',          'type' => 'regex', 'expected' => 31, 'note' => ''],
        ['id' =>  6, 'risk' => 'green', 'search' => 'Artic',          'replace' => 'Arctic',         'pattern' => '\\bArtic\\b',            'type' => 'regex', 'expected' =>  8, 'note' => 'capital A mandatory — substring of article/particular'],
        ['id' =>  7, 'risk' => 'green', 'search' => 'traveling',      'replace' => 'travelling',     'pattern' => '\\btraveling\\b',        'type' => 'regex', 'expected' => 10, 'note' => ''],
        ['id' =>  8, 'risk' => 'green', 'search' => 'colorful',       'replace' => 'colourful',      'pattern' => '\\bcolorful\\b',         'type' => 'regex', 'expected' => 14, 'note' => ''],
        ['id' =>  9, 'risk' => 'green', 'search' => 'g raduated',     'replace' => 'graduated',      'pattern' => 'g raduated',             'type' => 'like',  'expected' => 12, 'note' => 'literal broken-space artifact; may be encoding issue'],

        // 🟠 Language-ambiguous — valid French words; must filter WHERE language_code='en'
        ['id' => 10, 'risk' => 'amber', 'search' => 'personnalis',    'replace' => 'personalis',     'pattern' => 'personnalis',            'type' => 'like',  'expected' => 40, 'note' => 'FR personnalisé/personnalisation are CORRECT — EN-only'],
        ['id' => 11, 'risk' => 'amber', 'search' => 'gros grain',     'replace' => 'grosgrain',      'pattern' => 'gros grain',             'type' => 'like',  'expected' => 21, 'note' => 'FR gros=grain is CORRECT (big grain) — EN-only'],
        ['id' => 12, 'risk' => 'amber', 'search' => 'quadri-color',   'replace' => 'four-colour',    'pattern' => 'quadri-color',           'type' => 'like',  'expected' => 41, 'note' => 'French may use it in specs — EN-only'],
        ['id' => 13, 'risk' => 'amber', 'search' => 'favorite',       'replace' => 'favourite',      'pattern' => '\\bfavorite\\b',         'type' => 'regex', 'expected' => 51, 'note' => 'FR favorite (fem. of favori) is CORRECT — EN-only'],
    ];
}

// ── Render ───────────────────────────────────────────────────────────────────

function sdr_render_page(): void {
    $ran      = isset($_POST['sdr_run']) && wp_verify_nonce($_POST['_sdr_nonce'] ?? '', 'sdr_run');
    $results  = $ran ? sdr_execute() : null;
    ?>
    <div class="wrap" style="max-width:1100px">
        <h1>Spelling Fix — Dry Run (Phase 1)</h1>
        <p>Queries <code>wp_posts.post_content</code> + <code>post_excerpt</code> for all 13 runbook rules,
           joins <code>wp_icl_translations</code> to report language breakdown.
           <strong>Reads only. Writes nothing.</strong></p>

        <form method="post">
            <?php wp_nonce_field('sdr_run', '_sdr_nonce'); ?>
            <p><button type="submit" name="sdr_run" class="button button-primary" style="font-size:15px;padding:8px 24px">
                Run Dry Run
            </button></p>
        </form>

        <?php if ($ran && $results): ?>
            <hr>
            <h2>Results — <?php echo count($results); ?> rules</h2>
            <p style="color:#666">Columns: <strong>ID, Title, Type, Language, Context snippet.</strong>
               Language from <code>wp_icl_translations</code>. <em>NULL</em> = no translation record (untranslated / unlinked).</p>
            <?php echo sdr_render_results($results); ?>
            <hr>
            <h3>Interpretation</h3>
            <ul style="list-style:disc;margin-left:18px">
                <li><strong>🟢 Green rules</strong> — all matches are in EN rows. Safe to global-replace.</li>
                <li><strong>🟠 Amber rules</strong> — matches appear in BOTH EN and FR rows. Global replace would corrupt French.
                    Phase 2 replace must filter <code>WHERE language_code='en'</code>.</li>
                <li><strong>🔴</strong> — matches appear ONLY in FR rows (rule mis-scoped; abort this rule).</li>
                <li>Precise match counts may differ from the audit's expected counts — the audit read rendered pages,
                    not raw <code>post_content</code>; shortcodes/WPBakery markup may shift counts.</li>
            </ul>
        <?php elseif ($ran): ?>
            <p style="color:#c00"><strong>No results — check that <code>wp_icl_translations</code> exists and posts are published.</strong></p>
        <?php endif; ?>
    </div>
    <?php
}

// ── Execute ───────────────────────────────────────────────────────────────────

function sdr_execute(): array {
    global $wpdb;
    $prefix     = $wpdb->prefix;
    $has_wpml   = $wpdb->get_var("SHOW TABLES LIKE '{$prefix}icl_translations'") === "{$prefix}icl_translations";
    $all_results = [];

    foreach (sdr_rules() as $rule) {
        $rule_results = [];

        // Search both post_content and post_excerpt
        foreach (['post_content', 'post_excerpt'] as $col) {
            if ($rule['type'] === 'regex') {
                // MySQL 8.0+ ICU regex supports \b word boundaries.
                // In PHP single-quoted strings, \\b → literal \b sent to MySQL.
                $where = "$col REGEXP %s";
            } else {
                $where = "$col LIKE %s";
                $rule['pattern'] = '%' . $wpdb->esc_like($rule['pattern']) . '%';
            }

            $sql = "
                SELECT p.ID, p.post_title, p.post_type, p.post_name, p.post_status,
                       SUBSTRING($col,
                           GREATEST(1, LOCATE(%s, $col) - 80),
                           160 + LENGTH(%s)
                       ) AS context,
                       " . ($has_wpml ? "t.language_code" : "NULL AS language_code") . ",
                       '$col' AS matched_column
                FROM {$prefix}posts p
                " . ($has_wpml ? "LEFT JOIN {$prefix}icl_translations t ON t.element_id = p.ID AND t.element_type LIKE 'post\\_%'" : "") . "
                WHERE p.post_status = 'publish'
                  AND $where
                ORDER BY p.post_type, p.post_title
                LIMIT 500
            ";

            $args = [$rule['search'], $rule['search'], $rule['pattern']];
            $query = $wpdb->prepare($sql, ...$args);
            $rows  = $wpdb->get_results($query);

            foreach ($rows as $row) {
                // Highlight the match in context
                $ctx = esc_html($row->context);
                $hl  = esc_html($rule['search']);
                $ctx = str_ireplace($hl, "<mark>$hl</mark>", $ctx);
                $rule_results[] = [
                    'id'           => $row->ID,
                    'title'        => esc_html($row->post_title),
                    'type'         => esc_html($row->post_type),
                    'slug'         => esc_html($row->post_name),
                    'lang'         => esc_html($row->language_code ?? 'NULL'),
                    'context'      => $ctx,
                    'matched_col'  => $row->matched_column,
                ];
            }
        }

        // Deduplicate across content + excerpt
        $seen = [];
        $rule_results = array_filter($rule_results, function ($r) use (&$seen) {
            $key = $r['id'] . '|' . $r['matched_col'];
            if (isset($seen[$key])) { return false; }
            $seen[$key] = true; return true;
        });

        $all_results[] = [
            'rule'    => $rule,
            'matches' => array_values($rule_results),
        ];
    }

    return $all_results;
}

// ── Render results ────────────────────────────────────────────────────────────

function sdr_render_results(array $results): string {
    $out = '';
    foreach ($results as $block) {
        $rule    = $block['rule'];
        $matches = $block['matches'];
        $icon    = $rule['risk'] === 'green' ? '🟢' : '🟠';
        $risk_label = $rule['risk'] === 'green' ? 'GLOBAL-SAFE' : 'LANG-AMBIGUOUS — EN-only';

        // Language breakdown
        $langs = [];
        foreach ($matches as $m) {
            $l = $m['lang'] ?: 'NULL';
            $langs[$l] = ($langs[$l] ?? 0) + 1;
        }

        $out .= "<h3 style=\"margin-bottom:2px\">$icon Rule {$rule['id']}: <code>{$rule['search']}</code> → <code>{$rule['replace']}</code> "
              . "<small>[$risk_label · expected ~{$rule['expected']} pages · " . count($matches) . " DB rows]</small></h3>";
        if ($rule['note']) {
            $out .= "<p style=\"color:#666;margin:0 0 4px\">⚠️ {$rule['note']}</p>";
        }

        // Language summary
        $out .= "<p style=\"margin:2px 0\"><strong>By language:</strong> ";
        $parts = [];
        foreach ($langs as $lang => $cnt) {
            $color = ($lang === 'en') ? '#070' : (($lang === 'fr') ? '#c00' : '#666');
            $parts[] = "<span style=\"color:$color;font-weight:600\">$lang: $cnt</span>";
        }
        $out .= implode(' · ', $parts) . "</p>";

        // Sample rows (up to 15)
        if (count($matches) > 0) {
            $out .= '<table class="widefat striped" style="table-layout:fixed;font-size:12px">';
            $out .= '<thead><tr><th style="width:50px">ID</th><th style="width:180px">Title</th>'
                   .'<th style="width:60px">Type</th><th style="width:40px">Lang</th><th>Context</th></tr></thead><tbody>';
            $show = array_slice($matches, 0, 15);
            foreach ($show as $m) {
                $lang_color = ($m['lang'] === 'en') ? '#070' : (($m['lang'] === 'fr') ? '#c00' : '#666');
                $out .= "<tr>";
                $out .= "<td><a href=\"/wp-admin/post.php?post={$m['id']}&action=edit\" target=\"_blank\">{$m['id']}</a></td>";
                $out .= "<td style=\"word-wrap:break-word\">{$m['title']}</td>";
                $out .= "<td>{$m['type']}</td>";
                $out .= "<td style=\"color:$lang_color;font-weight:600\">{$m['lang']}</td>";
                $out .= "<td style=\"word-wrap:break-word\"><small>{$m['context']}</small></td>";
                $out .= "</tr>";
            }
            if (count($matches) > 15) {
                $out .= "<tr><td colspan=\"5\"><em>… and " . (count($matches) - 15) . " more rows.</em></td></tr>";
            }
            $out .= '</tbody></table>';
        } else {
            $out .= '<p style="color:#c00">⚠️ No matches found in DB — possible causes: content in postmeta, theme-rendered labels, or spelling only in FR translations.</p>';
        }
        $out .= '<br>';
    }
    return $out;
}
