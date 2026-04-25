<?php
set_time_limit(30);

// Check items.php directly
$items = "C:/xampp/htdocs/vipaccsystem/inventory/manage/items.php";
$c = file_get_contents($items);
$hex = bin2hex(substr($c, 0, 10));
echo "items.php hex: <b>$hex</b><br>";
echo "Expected:      <b>3c3f706870</b> = &lt;?php<br><br>";

// Check all files that items.php includes
$includes = [
    "C:/xampp/htdocs/vipaccsystem/includes/session.inc",
    "C:/xampp/htdocs/vipaccsystem/includes/ui.inc",
    "C:/xampp/htdocs/vipaccsystem/includes/data_checks.inc",
    "C:/xampp/htdocs/vipaccsystem/inventory/includes/inventory_db.inc",
    "C:/xampp/htdocs/vipaccsystem/inventory/includes/db/items_db.inc",
    "C:/xampp/htdocs/vipaccsystem/inventory/manage/items.php",
    "C:/xampp/htdocs/vipaccsystem/themes/default/renderer.php",
    "C:/xampp/htdocs/vipaccsystem/vipaccsystem.php",
    "C:/xampp/htdocs/vipaccsystem/config.php",
    "C:/xampp/htdocs/vipaccsystem/config_db.php",
];

$bom = "ï»¿";
echo "<table border=1 cellpadding=4 style='font-family:monospace;font-size:12px'>";
echo "<tr><th>File</th><th>First bytes (hex)</th><th>BOM?</th></tr>";
foreach ($includes as $fp) {
    if (!file_exists($fp)) { echo "<tr><td>$fp</td><td colspan=2>NOT FOUND</td></tr>"; continue; }
    $content = file_get_contents($fp);
    $first3  = substr($content, 0, 3);
    $hex3    = bin2hex(substr($content, 0, 6));
    $has_bom = ($first3 === $bom);
    $color   = $has_bom ? "background:red;color:white" : "background:#d4edda";
    echo "<tr><td>" . basename($fp) . "</td><td>$hex3</td><td style='$color'>" . ($has_bom ? "YES - FIXING..." : "OK") . "</td></tr>";
    if ($has_bom) {
        file_put_contents($fp, substr($content, 3));
    }
}
echo "</table>";

// Also scan just vipaccsystem folder (not recursively deep)
echo "<br><b>Scanning vipaccsystem root + 1 level:</b><br>";
$dirs = glob("C:/xampp/htdocs/vipaccsystem/*.php");
$dirs2 = glob("C:/xampp/htdocs/vipaccsystem/includes/*.inc");
$dirs3 = glob("C:/xampp/htdocs/vipaccsystem/inventory/manage/*.php");
$dirs4 = glob("C:/xampp/htdocs/vipaccsystem/themes/default/*.php");
$all = array_merge($dirs, $dirs2, $dirs3, $dirs4);
$found = [];
foreach ($all as $fp) {
    $content = file_get_contents($fp);
    if (substr($content, 0, 3) === $bom) {
        file_put_contents($fp, substr($content, 3));
        $found[] = basename($fp);
    }
}
echo count($found) ? "Fixed: " . implode(', ', $found) : "No BOM found in scanned files.";
echo "<br><br><a href='/vipaccsystem/inventory/manage/items.php'>Test items.php now</a>";
?>
