<?php
$path_to_root = "..";
$page_security = 'SA_GLACCOUNT';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");

$is_ar = isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG';

page($is_ar ? "\xd8\xaf\xd9\x84\xd9\x8a\xd9\x84 \xd8\xa7\xd9\x84\xd8\xad\xd8\xb3\xd8\xa7\xd8\xa8\xd8\xa7\xd8\xaa" : "Chart of Accounts");

// Fetch full chart: class -> type -> accounts
$sql = "SELECT coa.account_code, coa.account_code2, coa.account_name,
        ct.id AS type_id, ct.name AS type_name, ct.parent AS type_parent,
        cc.cid AS class_id, cc.class_name, cc.ctype
    FROM ".TB_PREF."chart_master coa
    JOIN ".TB_PREF."chart_types ct ON coa.account_type = ct.id
    JOIN ".TB_PREF."chart_class cc ON ct.class_id = cc.cid
    ORDER BY cc.cid, ct.id, coa.account_code";
$result = db_query($sql, "Could not retrieve chart of accounts");

$data = array();
while ($row = db_fetch($result)) {
    $data[] = $row;
}

// Also fetch account types for sub-grouping
$sql_types = "SELECT ct.id, ct.name, ct.class_id, ct.parent,
        (SELECT COUNT(*) FROM ".TB_PREF."chart_master cm WHERE cm.account_type = ct.id) AS acct_count
    FROM ".TB_PREF."chart_types ct
    ORDER BY ct.class_id, ct.id";
$result_types = db_query($sql_types, "Could not retrieve account types");

$all_types = array();
while ($row = db_fetch($result_types)) {
    $all_types[$row['id']] = $row;
}

// Build tree: class -> type -> accounts
$tree = array();
foreach ($data as $row) {
    $cid = $row['class_id'];
    $tid = $row['type_id'];
    if (!isset($tree[$cid])) {
        $tree[$cid] = array(
            'name' => $row['class_name'],
            'ctype' => $row['ctype'],
            'types' => array()
        );
    }
    if (!isset($tree[$cid]['types'][$tid])) {
        $tree[$cid]['types'][$tid] = array(
            'name' => $row['type_name'],
            'accounts' => array()
        );
    }
    $tree[$cid]['types'][$tid]['accounts'][] = $row;
}

$dir_attr = $is_ar ? ' dir="rtl"' : '';
$title = $is_ar ? "\xd8\xaf\xd9\x84\xd9\x8a\xd9\x84 \xd8\xa7\xd9\x84\xd8\xad\xd8\xb3\xd8\xa7\xd8\xa8\xd8\xa7\xd8\xaa" : "Chart of Accounts";
$subtitle = $is_ar ? "\xd8\xa7\xd9\x84\xd9\x87\xd9\x8a\xd9\x83\xd9\x84 \xd8\xa7\xd9\x84\xd8\xb4\xd8\xac\xd8\xb1\xd9\x8a \xd9\x84\xd8\xaf\xd9\x84\xd9\x8a\xd9\x84 \xd8\xa7\xd9\x84\xd8\xad\xd8\xb3\xd8\xa7\xd8\xa8\xd8\xa7\xd8\xaa" : "Hierarchical Account Structure";
$lbl_code = $is_ar ? "\xd8\xa7\xd9\x84\xd8\xb1\xd9\x85\xd8\xb2" : "Code";
$lbl_alt_code = $is_ar ? "\xd8\xa7\xd9\x84\xd8\xb1\xd9\x85\xd8\xb2 \xd8\xa7\xd9\x84\xd8\xa8\xd8\xaf\xd9\x8a\xd9\x84" : "Alt Code";
$lbl_accounts = $is_ar ? "\xd8\xad\xd8\xb3\xd8\xa7\xd8\xa8\xd8\xa7\xd8\xaa" : "accounts";
$lbl_classes = $is_ar ? "\xd8\xa7\xd9\x84\xd8\xa3\xd8\xb5\xd9\x86\xd8\xa7\xd9\x81" : "Classes";
$lbl_types = $is_ar ? "\xd8\xa7\xd9\x84\xd8\xa3\xd9\x86\xd9\x88\xd8\xa7\xd8\xb9" : "Types";
$lbl_total_accts = $is_ar ? "\xd8\xa5\xd8\xac\xd9\x85\xd8\xa7\xd9\x84\xd9\x8a \xd8\xa7\xd9\x84\xd8\xad\xd8\xb3\xd8\xa7\xd8\xa8\xd8\xa7\xd8\xaa" : "Total Accounts";
$lbl_no_data = $is_ar ? "\xd9\x84\xd8\xa7 \xd8\xaa\xd9\x88\xd8\xac\xd8\xaf \xd8\xa8\xd9\x8a\xd8\xa7\xd9\x86\xd8\xa7\xd8\xaa" : "No accounts defined";
$lbl_bs = $is_ar ? "\xd9\x85\xd9\x8a\xd8\xb2\xd8\xa7\xd9\x86\xd9\x8a\xd8\xa9" : "Balance Sheet";
$lbl_pl = $is_ar ? "\xd8\xa3\xd8\xb1\xd8\xa8\xd8\xa7\xd8\xad \xd9\x88\xd8\xae\xd8\xb3\xd8\xa7\xd8\xa6\xd8\xb1" : "Profit & Loss";

// Class type icons and colors
$class_styles = array(
    0 => array('icon' => '&#127974;', 'color' => 'rgba(46,204,113,0.15)', 'border' => 'rgba(46,204,113,0.3)'),  // BS - Assets
    1 => array('icon' => '&#128200;', 'color' => 'rgba(52,152,219,0.15)', 'border' => 'rgba(52,152,219,0.3)'),  // PL
);

echo <<<STYLES
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
.vip-tree-container {
    font-family: 'DM Sans', sans-serif;
    max-width: 1000px;
    margin: 20px auto;
    padding: 0 16px;
}
.vip-tree-container * { box-sizing: border-box; }
.vip-tree-header {
    background: linear-gradient(135deg, #050E1F 0%, #0A1A3A 50%, #050E1F 100%);
    border: 1px solid rgba(212,175,55,0.3);
    border-radius: 16px;
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.vip-tree-header::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, #D4AF37, transparent);
}
.vip-tree-header h2 {
    font-family: 'Playfair Display', serif;
    color: #D4AF37;
    font-size: 26px;
    margin: 0 0 6px 0;
    font-weight: 700;
}
.vip-tree-header p {
    color: rgba(255,255,255,0.6);
    font-size: 14px;
    margin: 0;
}
.vip-stats-row {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.vip-stat-card {
    flex: 1;
    min-width: 130px;
    background: linear-gradient(145deg, #050E1F 0%, #0B1D3D 100%);
    border: 1px solid rgba(212,175,55,0.2);
    border-radius: 12px;
    padding: 18px 20px;
    text-align: center;
}
.vip-stat-card .stat-value {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    color: #D4AF37;
    font-weight: 700;
}
.vip-stat-card .stat-label {
    font-size: 11px;
    color: rgba(255,255,255,0.5);
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.vip-tree-card {
    background: linear-gradient(145deg, #050E1F 0%, #0B1D3D 100%);
    border: 1px solid rgba(212,175,55,0.2);
    border-radius: 14px;
    margin-bottom: 16px;
    overflow: hidden;
}
/* Level 1: Class */
.vip-cls { border-bottom: 1px solid rgba(212,175,55,0.1); }
.vip-cls:last-child { border-bottom: none; }
.vip-cls > summary {
    display: flex;
    align-items: center;
    padding: 20px 24px;
    cursor: pointer;
    color: #D4AF37;
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 18px;
    list-style: none;
    transition: background 0.2s;
    gap: 14px;
    user-select: none;
}
.vip-cls > summary::-webkit-details-marker { display: none; }
.vip-cls > summary:hover { background: rgba(212,175,55,0.05); }
.vip-cls > summary .cls-icon {
    width: 42px; height: 42px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.vip-cls > summary .cls-label { flex: 1; }
.vip-cls > summary .cls-tag {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-family: 'DM Sans', sans-serif;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.cls-tag-bs { background: rgba(46,204,113,0.12); color: #2ecc71; }
.cls-tag-pl { background: rgba(52,152,219,0.12); color: #3498db; }
.vip-cls > summary .cls-badge {
    background: rgba(212,175,55,0.15);
    color: #D4AF37;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-family: 'DM Sans', sans-serif;
    font-weight: 600;
}
.vip-cls > summary .cls-arrow {
    color: rgba(212,175,55,0.5);
    transition: transform 0.3s;
    font-size: 12px;
}
.vip-cls[open] > summary .cls-arrow { transform: rotate(90deg); }

/* Level 2: Type */
.vip-type-wrap { padding: 0 24px 14px 66px; }
[dir="rtl"] .vip-type-wrap { padding: 0 66px 14px 24px; }
.vip-typ {
    border: 1px solid rgba(212,175,55,0.1);
    border-radius: 10px;
    margin-bottom: 8px;
    background: rgba(255,255,255,0.015);
    position: relative;
}
.vip-typ::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 22px;
    width: 16px;
    height: 1px;
    background: rgba(212,175,55,0.15);
}
[dir="rtl"] .vip-typ::before { left: auto; right: -20px; }
.vip-typ > summary {
    display: flex;
    align-items: center;
    padding: 14px 18px;
    cursor: pointer;
    color: #fff;
    font-weight: 500;
    font-size: 14px;
    list-style: none;
    transition: background 0.2s;
    gap: 10px;
    user-select: none;
}
.vip-typ > summary::-webkit-details-marker { display: none; }
.vip-typ > summary:hover { background: rgba(212,175,55,0.04); }
.vip-typ > summary .typ-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    background: rgba(155,89,182,0.15);
    border: 1px solid rgba(155,89,182,0.3);
}
.vip-typ > summary .typ-label { flex: 1; }
.vip-typ > summary .typ-count {
    color: rgba(255,255,255,0.4);
    font-size: 12px;
}
.vip-typ > summary .typ-arrow {
    color: rgba(255,255,255,0.3);
    transition: transform 0.3s;
    font-size: 11px;
}
.vip-typ[open] > summary .typ-arrow { transform: rotate(90deg); }

/* Level 3: Account rows */
.vip-acct-wrap { padding: 6px 18px 12px 48px; }
[dir="rtl"] .vip-acct-wrap { padding: 6px 48px 12px 18px; }
.vip-acct-row {
    display: flex;
    align-items: center;
    padding: 9px 14px;
    margin-bottom: 3px;
    border-radius: 8px;
    border: 1px solid rgba(212,175,55,0.05);
    transition: all 0.2s;
    gap: 10px;
    position: relative;
}
.vip-acct-row:hover {
    background: rgba(212,175,55,0.04);
    border-color: rgba(212,175,55,0.2);
}
.vip-acct-row::before {
    content: '';
    position: absolute;
    left: -16px;
    top: 50%;
    width: 12px;
    height: 1px;
    background: rgba(212,175,55,0.12);
}
[dir="rtl"] .vip-acct-row::before { left: auto; right: -16px; }
.vip-acct-row .acct-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
    background: #D4AF37;
    opacity: 0.5;
}
.vip-acct-row .acct-code {
    color: #D4AF37;
    font-size: 13px;
    font-weight: 600;
    min-width: 80px;
}
.vip-acct-row .acct-name {
    flex: 1;
    color: rgba(255,255,255,0.85);
    font-size: 13px;
}
.vip-acct-row .acct-alt {
    color: rgba(255,255,255,0.3);
    font-size: 11px;
    min-width: 60px;
    text-align: right;
}
[dir="rtl"] .vip-acct-row .acct-alt { text-align: left; }
.vip-empty {
    text-align: center;
    padding: 48px 24px;
    color: rgba(255,255,255,0.4);
    font-size: 15px;
}
.vip-empty .empty-icon { font-size: 40px; margin-bottom: 12px; opacity: 0.4; }
</style>
STYLES;

// Stats
$total_accts = count($data);
$total_classes = count($tree);
$total_types = count($all_types);

echo '<div class="vip-tree-container"' . $dir_attr . '>';

echo '<div class="vip-tree-header">';
echo '<h2>' . htmlspecialchars($title) . '</h2>';
echo '<p>' . htmlspecialchars($subtitle) . '</p>';
echo '</div>';

echo '<div class="vip-stats-row">';
echo '<div class="vip-stat-card"><div class="stat-value">' . $total_classes . '</div><div class="stat-label">' . htmlspecialchars($lbl_classes) . '</div></div>';
echo '<div class="vip-stat-card"><div class="stat-value">' . $total_types . '</div><div class="stat-label">' . htmlspecialchars($lbl_types) . '</div></div>';
echo '<div class="vip-stat-card"><div class="stat-value">' . $total_accts . '</div><div class="stat-label">' . htmlspecialchars($lbl_total_accts) . '</div></div>';
echo '</div>';

if (empty($tree)) {
    echo '<div class="vip-tree-card"><div class="vip-empty">';
    echo '<div class="empty-icon">&#128218;</div>';
    echo '<div>' . htmlspecialchars($lbl_no_data) . '</div>';
    echo '</div></div>';
} else {
    echo '<div class="vip-tree-card">';
    foreach ($tree as $cid => $class_data) {
        $ctype = intval($class_data['ctype']);
        $style = isset($class_styles[$ctype]) ? $class_styles[$ctype] : $class_styles[0];
        $tag_class = ($ctype == 0) ? 'cls-tag-bs' : 'cls-tag-pl';
        $tag_text = ($ctype == 0) ? $lbl_bs : $lbl_pl;

        $class_acct_count = 0;
        foreach ($class_data['types'] as $t) {
            $class_acct_count += count($t['accounts']);
        }

        echo '<details class="vip-cls" open>';
        echo '<summary>';
        echo '<span class="cls-icon" style="background:' . $style['color'] . ';border:1px solid ' . $style['border'] . '">' . $style['icon'] . '</span>';
        echo '<span class="cls-label">' . htmlspecialchars($class_data['name']) . '</span>';
        echo '<span class="cls-tag ' . $tag_class . '">' . htmlspecialchars($tag_text) . '</span>';
        echo '<span class="cls-badge">' . $class_acct_count . '</span>';
        echo '<span class="cls-arrow">&#9654;</span>';
        echo '</summary>';
        echo '<div class="vip-type-wrap">';

        foreach ($class_data['types'] as $tid => $type_data) {
            $type_count = count($type_data['accounts']);
            echo '<details class="vip-typ">';
            echo '<summary>';
            echo '<span class="typ-icon">&#128193;</span>';
            echo '<span class="typ-label">' . htmlspecialchars($type_data['name']) . '</span>';
            echo '<span class="typ-count">' . $type_count . ' ' . htmlspecialchars($lbl_accounts) . '</span>';
            echo '<span class="typ-arrow">&#9654;</span>';
            echo '</summary>';
            echo '<div class="vip-acct-wrap">';

            foreach ($type_data['accounts'] as $acct) {
                $alt = $acct['account_code2'] ? $acct['account_code2'] : '';
                echo '<div class="vip-acct-row">';
                echo '<span class="acct-dot"></span>';
                echo '<span class="acct-code">' . htmlspecialchars($acct['account_code']) . '</span>';
                echo '<span class="acct-name">' . htmlspecialchars($acct['account_name']) . '</span>';
                if ($alt) {
                    echo '<span class="acct-alt">' . htmlspecialchars($alt) . '</span>';
                }
                echo '</div>';
            }

            echo '</div>';
            echo '</details>';
        }

        echo '</div>';
        echo '</details>';
    }
    echo '</div>';
}

echo '</div>';

end_page();
