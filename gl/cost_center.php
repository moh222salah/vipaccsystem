<?php
$path_to_root = "..";
$page_security = 'SA_DIMTRANSVIEW';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");

$is_ar = isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG';

page($is_ar ? "\xd9\x85\xd8\xb1\xd8\xa7\xd9\x83\xd8\xb2 \xd8\xa7\xd9\x84\xd8\xaa\xd9\x83\xd9\x84\xd9\x81\xd8\xa9" : "Cost Centers");

// Fetch dimensions from DB
$sql = "SELECT id, reference, name, type_, closed, date_ FROM ".TB_PREF."dimensions ORDER BY type_, name";
$result = db_query($sql, "Could not retrieve dimensions");

$dimensions = array();
while ($row = db_fetch($result)) {
    $dimensions[] = $row;
}

// Group by type
$grouped = array();
foreach ($dimensions as $dim) {
    $type_key = $dim['type_'];
    if (!isset($grouped[$type_key])) {
        $grouped[$type_key] = array();
    }
    $grouped[$type_key][] = $dim;
}

$type_labels = array(
    1 => ($is_ar ? "\xd8\xa7\xd9\x84\xd8\xa8\xd8\xb9\xd8\xaf \xd8\xa7\xd9\x84\xd8\xa3\xd9\x88\xd9\x84 - \xd9\x85\xd8\xb1\xd8\xa7\xd9\x83\xd8\xb2 \xd8\xa7\xd9\x84\xd8\xaa\xd9\x83\xd9\x84\xd9\x81\xd8\xa9" : "Dimension 1 - Cost Centers"),
    2 => ($is_ar ? "\xd8\xa7\xd9\x84\xd8\xa8\xd8\xb9\xd8\xaf \xd8\xa7\xd9\x84\xd8\xab\xd8\xa7\xd9\x86\xd9\x8a - \xd8\xa7\xd9\x84\xd9\x85\xd8\xb4\xd8\xa7\xd8\xb1\xd9\x8a\xd8\xb9" : "Dimension 2 - Projects"),
);

$dir_attr = $is_ar ? ' dir="rtl"' : '';
$title = $is_ar ? "\xd9\x85\xd8\xb1\xd8\xa7\xd9\x83\xd8\xb2 \xd8\xa7\xd9\x84\xd8\xaa\xd9\x83\xd9\x84\xd9\x81\xd8\xa9" : "Cost Centers";
$subtitle = $is_ar ? "\xd8\xa5\xd8\xaf\xd8\xa7\xd8\xb1\xd8\xa9 \xd9\x85\xd8\xb1\xd8\xa7\xd9\x83\xd8\xb2 \xd8\xa7\xd9\x84\xd8\xaa\xd9\x83\xd9\x84\xd9\x81\xd8\xa9 \xd9\x88\xd8\xa7\xd9\x84\xd8\xa3\xd8\xa8\xd8\xb9\xd8\xa7\xd8\xaf" : "Manage Cost Centers & Dimensions";
$lbl_ref = $is_ar ? "\xd8\xa7\xd9\x84\xd9\x85\xd8\xb1\xd8\xac\xd8\xb9" : "Reference";
$lbl_date = $is_ar ? "\xd8\xa7\xd9\x84\xd8\xaa\xd8\xa7\xd8\xb1\xd9\x8a\xd8\xae" : "Date";
$lbl_status = $is_ar ? "\xd8\xa7\xd9\x84\xd8\xad\xd8\xa7\xd9\x84\xd8\xa9" : "Status";
$lbl_open = $is_ar ? "\xd9\x85\xd9\x81\xd8\xaa\xd9\x88\xd8\xad" : "Open";
$lbl_closed = $is_ar ? "\xd9\x85\xd8\xba\xd9\x84\xd9\x82" : "Closed";
$lbl_no_data = $is_ar ? "\xd9\x84\xd8\xa7 \xd8\xaa\xd9\x88\xd8\xac\xd8\xaf \xd9\x85\xd8\xb1\xd8\xa7\xd9\x83\xd8\xb2 \xd8\xaa\xd9\x83\xd9\x84\xd9\x81\xd8\xa9 \xd9\x85\xd8\xb9\xd8\xb1\xd9\x81\xd8\xa9" : "No cost centers defined";
$lbl_total = $is_ar ? "\xd8\xa5\xd8\xac\xd9\x85\xd8\xa7\xd9\x84\xd9\x8a" : "Total";

echo <<<STYLES
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
.vip-tree-container {
    font-family: 'DM Sans', sans-serif;
    max-width: 960px;
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
    letter-spacing: 0.5px;
}
.vip-tree-header p {
    color: rgba(255,255,255,0.6);
    font-size: 14px;
    margin: 0;
}
.vip-tree-card {
    background: linear-gradient(145deg, #050E1F 0%, #0B1D3D 100%);
    border: 1px solid rgba(212,175,55,0.2);
    border-radius: 14px;
    padding: 0;
    margin-bottom: 16px;
    overflow: hidden;
    backdrop-filter: blur(10px);
}
.vip-tree-card details {
    border-bottom: 1px solid rgba(212,175,55,0.08);
}
.vip-tree-card details:last-child { border-bottom: none; }
.vip-tree-card summary {
    display: flex;
    align-items: center;
    padding: 16px 24px;
    cursor: pointer;
    color: #fff;
    font-weight: 500;
    font-size: 15px;
    list-style: none;
    transition: background 0.2s;
    gap: 12px;
    user-select: none;
}
.vip-tree-card summary::-webkit-details-marker { display: none; }
.vip-tree-card summary:hover { background: rgba(212,175,55,0.06); }
.vip-tree-card summary .tree-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    background: rgba(212,175,55,0.12);
    border: 1px solid rgba(212,175,55,0.25);
}
.vip-tree-card summary .tree-label { flex: 1; }
.vip-tree-card summary .tree-badge {
    background: rgba(212,175,55,0.15);
    color: #D4AF37;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.vip-tree-card summary .tree-arrow {
    color: rgba(212,175,55,0.5);
    transition: transform 0.3s;
    font-size: 12px;
}
.vip-tree-card details[open] > summary .tree-arrow {
    transform: rotate(90deg);
}
.vip-tree-children {
    padding: 0 24px 12px 72px;
}
[dir="rtl"] .vip-tree-children {
    padding: 0 72px 12px 24px;
}
.vip-tree-child {
    position: relative;
    padding: 12px 16px;
    margin-bottom: 6px;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(212,175,55,0.1);
    border-radius: 10px;
    transition: all 0.2s;
}
.vip-tree-child:hover {
    background: rgba(212,175,55,0.04);
    border-color: rgba(212,175,55,0.25);
}
.vip-tree-child::before {
    content: '';
    position: absolute;
    left: -24px;
    top: 50%;
    width: 18px;
    height: 1px;
    background: rgba(212,175,55,0.2);
}
[dir="rtl"] .vip-tree-child::before {
    left: auto;
    right: -24px;
}
.vip-tree-child .child-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 6px;
}
.vip-tree-child .child-icon {
    width: 28px; height: 28px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}
.vip-tree-child .child-icon.open-icon {
    background: rgba(46,204,113,0.15);
    border: 1px solid rgba(46,204,113,0.3);
}
.vip-tree-child .child-icon.closed-icon {
    background: rgba(231,76,60,0.15);
    border: 1px solid rgba(231,76,60,0.3);
}
.vip-tree-child .child-name {
    color: #fff;
    font-weight: 500;
    font-size: 14px;
    flex: 1;
}
.vip-tree-child .child-meta {
    display: flex;
    gap: 16px;
    padding-left: 38px;
    font-size: 12px;
    color: rgba(255,255,255,0.45);
}
[dir="rtl"] .vip-tree-child .child-meta {
    padding-left: 0;
    padding-right: 38px;
}
.vip-tree-child .child-meta span { display: flex; align-items: center; gap: 4px; }
.vip-tree-child .status-open { color: #2ecc71; }
.vip-tree-child .status-closed { color: #e74c3c; }
.vip-empty {
    text-align: center;
    padding: 48px 24px;
    color: rgba(255,255,255,0.4);
    font-size: 15px;
}
.vip-empty .empty-icon { font-size: 40px; margin-bottom: 12px; opacity: 0.4; }
.vip-stats-row {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.vip-stat-card {
    flex: 1;
    min-width: 140px;
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
    font-size: 12px;
    color: rgba(255,255,255,0.5);
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 1px;
}
</style>
STYLES;

// Count stats
$total_dims = count($dimensions);
$open_dims = 0;
$closed_dims = 0;
foreach ($dimensions as $d) {
    if ($d['closed']) $closed_dims++;
    else $open_dims++;
}

echo '<div class="vip-tree-container"' . $dir_attr . '>';

// Header
echo '<div class="vip-tree-header">';
echo '<h2>' . htmlspecialchars($title) . '</h2>';
echo '<p>' . htmlspecialchars($subtitle) . '</p>';
echo '</div>';

// Stats row
echo '<div class="vip-stats-row">';
echo '<div class="vip-stat-card"><div class="stat-value">' . $total_dims . '</div><div class="stat-label">' . htmlspecialchars($lbl_total) . '</div></div>';
echo '<div class="vip-stat-card"><div class="stat-value">' . $open_dims . '</div><div class="stat-label">' . htmlspecialchars($lbl_open) . '</div></div>';
echo '<div class="vip-stat-card"><div class="stat-value">' . $closed_dims . '</div><div class="stat-label">' . htmlspecialchars($lbl_closed) . '</div></div>';
echo '</div>';

if (empty($dimensions)) {
    echo '<div class="vip-tree-card"><div class="vip-empty">';
    echo '<div class="empty-icon">&#128193;</div>';
    echo '<div>' . htmlspecialchars($lbl_no_data) . '</div>';
    echo '</div></div>';
} else {
    echo '<div class="vip-tree-card">';
    foreach ($grouped as $type_key => $items) {
        $type_label = isset($type_labels[$type_key]) ? $type_labels[$type_key] : (($is_ar ? "\xd8\xa7\xd9\x84\xd8\xa8\xd8\xb9\xd8\xaf " : "Dimension ") . $type_key);
        $count = count($items);
        echo '<details open>';
        echo '<summary>';
        echo '<span class="tree-icon">&#128194;</span>';
        echo '<span class="tree-label">' . htmlspecialchars($type_label) . '</span>';
        echo '<span class="tree-badge">' . $count . '</span>';
        echo '<span class="tree-arrow">&#9654;</span>';
        echo '</summary>';
        echo '<div class="vip-tree-children">';
        foreach ($items as $dim) {
            $is_closed = $dim['closed'] ? true : false;
            $icon_class = $is_closed ? 'closed-icon' : 'open-icon';
            $icon_char = $is_closed ? '&#128683;' : '&#9989;';
            $status_class = $is_closed ? 'status-closed' : 'status-open';
            $status_text = $is_closed ? $lbl_closed : $lbl_open;

            echo '<div class="vip-tree-child">';
            echo '<div class="child-header">';
            echo '<span class="child-icon ' . $icon_class . '">' . $icon_char . '</span>';
            echo '<span class="child-name">' . htmlspecialchars($dim['name']) . '</span>';
            echo '</div>';
            echo '<div class="child-meta">';
            echo '<span>' . htmlspecialchars($lbl_ref) . ': <strong style="color:#D4AF37">' . htmlspecialchars($dim['reference']) . '</strong></span>';
            echo '<span>' . htmlspecialchars($lbl_date) . ': ' . htmlspecialchars($dim['date_']) . '</span>';
            echo '<span class="' . $status_class . '">' . htmlspecialchars($status_text) . '</span>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '</details>';
    }
    echo '</div>';
}

echo '</div>';

end_page();
