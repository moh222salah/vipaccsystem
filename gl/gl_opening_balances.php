<?php
$path_to_root = "..";
$page_security = 'SA_GLSETUP';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");

$is_ar = isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG';

page($is_ar ? "\xd8\xa3\xd8\xb1\xd8\xb5\xd8\xaf\xd8\xa9 \xd8\xa3\xd9\x88\xd9\x84 \xd8\xa7\xd9\x84\xd9\x85\xd8\xaf\xd8\xa9" : "Opening Balances");

// Fetch chart of accounts with types and opening balance from gl_trans
$sql = "SELECT coa.account_code, coa.account_name, ct.id AS type_id, ct.name AS type_name,
        cc.cid AS class_id, cc.class_name,
        COALESCE(SUM(gl.amount), 0) AS balance
    FROM ".TB_PREF."chart_master coa
    JOIN ".TB_PREF."chart_types ct ON coa.account_type = ct.id
    JOIN ".TB_PREF."chart_class cc ON ct.class_id = cc.cid
    LEFT JOIN ".TB_PREF."gl_trans gl ON gl.account = coa.account_code
    GROUP BY coa.account_code, coa.account_name, ct.id, ct.name, cc.cid, cc.class_name
    ORDER BY cc.cid, ct.id, coa.account_code";
$result = db_query($sql, "Could not retrieve opening balances");

$data = array();
while ($row = db_fetch($result)) {
    $data[] = $row;
}

// Group: class -> type -> accounts
$tree = array();
foreach ($data as $row) {
    $cid = $row['class_id'];
    $tid = $row['type_id'];
    if (!isset($tree[$cid])) {
        $tree[$cid] = array('name' => $row['class_name'], 'types' => array());
    }
    if (!isset($tree[$cid]['types'][$tid])) {
        $tree[$cid]['types'][$tid] = array('name' => $row['type_name'], 'accounts' => array());
    }
    $tree[$cid]['types'][$tid]['accounts'][] = $row;
}

$dir_attr = $is_ar ? ' dir="rtl"' : '';
$title = $is_ar ? "\xd8\xa3\xd8\xb1\xd8\xb5\xd8\xaf\xd8\xa9 \xd8\xa3\xd9\x88\xd9\x84 \xd8\xa7\xd9\x84\xd9\x85\xd8\xaf\xd8\xa9" : "Opening Balances";
$subtitle = $is_ar ? "\xd8\xb9\xd8\xb1\xd8\xb6 \xd8\xa3\xd8\xb1\xd8\xb5\xd8\xaf\xd8\xa9 \xd8\xa7\xd9\x84\xd8\xad\xd8\xb3\xd8\xa7\xd8\xa8\xd8\xa7\xd8\xaa" : "View Account Balances";
$lbl_code = $is_ar ? "\xd8\xa7\xd9\x84\xd8\xb1\xd9\x85\xd8\xb2" : "Code";
$lbl_balance = $is_ar ? "\xd8\xa7\xd9\x84\xd8\xb1\xd8\xb5\xd9\x8a\xd8\xaf" : "Balance";
$lbl_debit = $is_ar ? "\xd9\x85\xd8\xaf\xd9\x8a\xd9\x86" : "Debit";
$lbl_credit = $is_ar ? "\xd8\xaf\xd8\xa7\xd8\xa6\xd9\x86" : "Credit";
$lbl_accounts = $is_ar ? "\xd8\xad\xd8\xb3\xd8\xa7\xd8\xa8\xd8\xa7\xd8\xaa" : "accounts";
$lbl_no_data = $is_ar ? "\xd9\x84\xd8\xa7 \xd8\xaa\xd9\x88\xd8\xac\xd8\xaf \xd8\xa8\xd9\x8a\xd8\xa7\xd9\x86\xd8\xa7\xd8\xaa" : "No data available";

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
.vip-tree-card {
    background: linear-gradient(145deg, #050E1F 0%, #0B1D3D 100%);
    border: 1px solid rgba(212,175,55,0.2);
    border-radius: 14px;
    margin-bottom: 16px;
    overflow: hidden;
}
/* Level 1: Class */
.vip-l1 { border-bottom: 1px solid rgba(212,175,55,0.1); }
.vip-l1:last-child { border-bottom: none; }
.vip-l1 > summary {
    display: flex;
    align-items: center;
    padding: 18px 24px;
    cursor: pointer;
    color: #D4AF37;
    font-family: 'Playfair Display', serif;
    font-weight: 600;
    font-size: 17px;
    list-style: none;
    transition: background 0.2s;
    gap: 12px;
    user-select: none;
}
.vip-l1 > summary::-webkit-details-marker { display: none; }
.vip-l1 > summary:hover { background: rgba(212,175,55,0.05); }
.vip-l1 > summary .tree-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    background: rgba(212,175,55,0.12);
    border: 1px solid rgba(212,175,55,0.3);
}
.vip-l1 > summary .tree-label { flex: 1; }
.vip-l1 > summary .tree-badge {
    background: rgba(212,175,55,0.15);
    color: #D4AF37;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-family: 'DM Sans', sans-serif;
    font-weight: 600;
}
.vip-l1 > summary .tree-arrow {
    color: rgba(212,175,55,0.5);
    transition: transform 0.3s;
    font-size: 12px;
}
.vip-l1[open] > summary .tree-arrow { transform: rotate(90deg); }

/* Level 2: Type */
.vip-l2-wrap { padding: 0 24px 12px 60px; }
[dir="rtl"] .vip-l2-wrap { padding: 0 60px 12px 24px; }
.vip-l2 {
    border: 1px solid rgba(212,175,55,0.1);
    border-radius: 10px;
    margin-bottom: 8px;
    overflow: hidden;
    background: rgba(255,255,255,0.015);
}
.vip-l2 > summary {
    display: flex;
    align-items: center;
    padding: 12px 18px;
    cursor: pointer;
    color: #fff;
    font-weight: 500;
    font-size: 14px;
    list-style: none;
    transition: background 0.2s;
    gap: 10px;
    user-select: none;
}
.vip-l2 > summary::-webkit-details-marker { display: none; }
.vip-l2 > summary:hover { background: rgba(212,175,55,0.04); }
.vip-l2 > summary .t-icon {
    width: 28px; height: 28px;
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
    background: rgba(100,149,237,0.15);
    border: 1px solid rgba(100,149,237,0.3);
}
.vip-l2 > summary .t-label { flex: 1; }
.vip-l2 > summary .t-count {
    color: rgba(255,255,255,0.4);
    font-size: 12px;
}
.vip-l2 > summary .t-arrow {
    color: rgba(255,255,255,0.3);
    transition: transform 0.3s;
    font-size: 11px;
}
.vip-l2[open] > summary .t-arrow { transform: rotate(90deg); }

/* Level 3: Account leaves */
.vip-l3-wrap { padding: 6px 18px 12px 46px; }
[dir="rtl"] .vip-l3-wrap { padding: 6px 46px 12px 18px; }
.vip-acct {
    display: flex;
    align-items: center;
    padding: 10px 14px;
    margin-bottom: 4px;
    border-radius: 8px;
    border: 1px solid rgba(212,175,55,0.06);
    transition: all 0.2s;
    gap: 10px;
    position: relative;
}
.vip-acct:hover {
    background: rgba(212,175,55,0.04);
    border-color: rgba(212,175,55,0.2);
}
.vip-acct::before {
    content: '';
    position: absolute;
    left: -18px;
    top: 50%;
    width: 14px;
    height: 1px;
    background: rgba(212,175,55,0.15);
}
[dir="rtl"] .vip-acct::before { left: auto; right: -18px; }
.vip-acct .a-icon {
    width: 24px; height: 24px;
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px;
    flex-shrink: 0;
    background: rgba(212,175,55,0.08);
    border: 1px solid rgba(212,175,55,0.15);
}
.vip-acct .a-code {
    color: #D4AF37;
    font-size: 13px;
    font-weight: 600;
    min-width: 70px;
    font-family: 'DM Sans', sans-serif;
}
.vip-acct .a-name {
    flex: 1;
    color: rgba(255,255,255,0.85);
    font-size: 13px;
}
.vip-acct .a-bal {
    font-size: 13px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    min-width: 100px;
    text-align: right;
}
[dir="rtl"] .vip-acct .a-bal { text-align: left; }
.vip-acct .a-bal.positive { color: #2ecc71; }
.vip-acct .a-bal.negative { color: #e74c3c; }
.vip-acct .a-bal.zero { color: rgba(255,255,255,0.3); }
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
    min-width: 130px;
    background: linear-gradient(145deg, #050E1F 0%, #0B1D3D 100%);
    border: 1px solid rgba(212,175,55,0.2);
    border-radius: 12px;
    padding: 18px 20px;
    text-align: center;
}
.vip-stat-card .stat-value {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
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
</style>
STYLES;

// Compute stats
$total_accts = count($data);
$total_debit = 0;
$total_credit = 0;
foreach ($data as $row) {
    $bal = floatval($row['balance']);
    if ($bal > 0) $total_debit += $bal;
    else $total_credit += abs($bal);
}

echo '<div class="vip-tree-container"' . $dir_attr . '>';

// Header
echo '<div class="vip-tree-header">';
echo '<h2>' . htmlspecialchars($title) . '</h2>';
echo '<p>' . htmlspecialchars($subtitle) . '</p>';
echo '</div>';

// Stats
echo '<div class="vip-stats-row">';
echo '<div class="vip-stat-card"><div class="stat-value">' . $total_accts . '</div><div class="stat-label">' . htmlspecialchars($lbl_accounts) . '</div></div>';
echo '<div class="vip-stat-card"><div class="stat-value">' . number_format($total_debit, 2) . '</div><div class="stat-label">' . htmlspecialchars($lbl_debit) . '</div></div>';
echo '<div class="vip-stat-card"><div class="stat-value">' . number_format($total_credit, 2) . '</div><div class="stat-label">' . htmlspecialchars($lbl_credit) . '</div></div>';
echo '</div>';

if (empty($tree)) {
    echo '<div class="vip-tree-card"><div class="vip-empty">';
    echo '<div class="empty-icon">&#128202;</div>';
    echo '<div>' . htmlspecialchars($lbl_no_data) . '</div>';
    echo '</div></div>';
} else {
    echo '<div class="vip-tree-card">';
    foreach ($tree as $cid => $class_data) {
        // Count total accounts in this class
        $class_acct_count = 0;
        foreach ($class_data['types'] as $t) {
            $class_acct_count += count($t['accounts']);
        }
        echo '<details class="vip-l1" open>';
        echo '<summary>';
        echo '<span class="tree-icon">&#127970;</span>';
        echo '<span class="tree-label">' . htmlspecialchars($class_data['name']) . '</span>';
        echo '<span class="tree-badge">' . $class_acct_count . ' ' . htmlspecialchars($lbl_accounts) . '</span>';
        echo '<span class="tree-arrow">&#9654;</span>';
        echo '</summary>';
        echo '<div class="vip-l2-wrap">';

        foreach ($class_data['types'] as $tid => $type_data) {
            $type_count = count($type_data['accounts']);
            echo '<details class="vip-l2">';
            echo '<summary>';
            echo '<span class="t-icon">&#128196;</span>';
            echo '<span class="t-label">' . htmlspecialchars($type_data['name']) . '</span>';
            echo '<span class="t-count">' . $type_count . '</span>';
            echo '<span class="t-arrow">&#9654;</span>';
            echo '</summary>';
            echo '<div class="vip-l3-wrap">';

            foreach ($type_data['accounts'] as $acct) {
                $bal = floatval($acct['balance']);
                if ($bal > 0) {
                    $bal_class = 'positive';
                    $bal_str = number_format($bal, 2);
                } elseif ($bal < 0) {
                    $bal_class = 'negative';
                    $bal_str = '(' . number_format(abs($bal), 2) . ')';
                } else {
                    $bal_class = 'zero';
                    $bal_str = '0.00';
                }

                echo '<div class="vip-acct">';
                echo '<span class="a-icon">&#128209;</span>';
                echo '<span class="a-code">' . htmlspecialchars($acct['account_code']) . '</span>';
                echo '<span class="a-name">' . htmlspecialchars($acct['account_name']) . '</span>';
                echo '<span class="a-bal ' . $bal_class . '">' . $bal_str . '</span>';
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
