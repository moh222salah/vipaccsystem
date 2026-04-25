<?php
$fp = "C:/xampp/htdocs/vipaccsystem/themes/default/renderer.php";
$bom = "ï»¿";
$content = file_get_contents($fp);

echo "File size: " . strlen($content) . " bytes<br>";
echo "First 6 bytes hex: " . bin2hex(substr($content, 0, 6)) . "<br>";

if (substr($content, 0, 3) === $bom) {
    $clean = substr($content, 3);
    file_put_contents($fp, $clean);
    echo "<b style='color:green'>✓ BOM removed from renderer.php!</b><br>";
    echo "New first bytes: " . bin2hex(substr($clean, 0, 6)) . "<br>";
} else {
    echo "<b style='color:orange'>No BOM found at start.</b><br>";
    // Check for BOM anywhere in first 10 bytes
    for ($i = 0; $i < 10; $i++) {
        if (substr($content, $i, 3) === $bom) {
            echo "BOM found at offset $i — removing...<br>";
            $clean = substr($content, 0, $i) . substr($content, $i + 3);
            file_put_contents($fp, $clean);
            echo "<b style='color:green'>✓ Fixed!</b><br>";
            break;
        }
    }
}

echo "<br><a href='/vipaccsystem/inventory/manage/items.php'>Test items.php now</a>";
?>
