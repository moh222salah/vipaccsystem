<?php
// Search ALL of xampp/htdocs for BOM files
$roots = [
    dirname(dirname(__DIR__)),           // htdocs
    dirname(dirname(__DIR__)) . '/vip-system',
    dirname(dirname(__DIR__)) . '/vipaccsystem',
];

$fixed = [];
$checked = 0;
$bom = "ï»¿";

foreach ($roots as $path) {
    if (!is_dir($path)) continue;
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iter as $f) {
        $ext = strtolower($f->getExtension());
        if (!in_array($ext, ['php','inc','js','css','html'])) continue;
        $checked++;
        $fp = $f->getPathname();
        $handle = fopen($fp, 'rb');
        $first3 = fread($handle, 3);
        fclose($handle);
        if ($first3 === $bom) {
            $content = file_get_contents($fp);
            file_put_contents($fp, substr($content, 3));
            $fixed[] = $fp;
        }
    }
}

echo "<h2 style='font-family:sans-serif'>BOM Fix Results</h2>";
echo "<p style='font-family:sans-serif'>Checked: <b>$checked</b> files</p>";
echo "<p style='font-family:sans-serif'>Fixed: <b>" . count($fixed) . "</b> files</p>";
if ($fixed) {
    echo "<ul style='font-family:monospace;font-size:12px'>";
    foreach ($fixed as $ff) echo "<li>" . htmlspecialchars($ff) . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color:red;font-family:sans-serif'>No BOM found in any file.</p>";
    echo "<p style='font-family:sans-serif'>The garbage chars might come from PHP sending whitespace/output before headers.<br>";
    echo "Check: does items.php have any whitespace BEFORE the opening &lt;?php tag?</p>";
    
    // Check items.php specifically
    $items = dirname(dirname(__DIR__)) . '/vipaccsystem/inventory/manage/items.php';
    if (file_exists($items)) {
        $c = file_get_contents($items);
        $first10 = bin2hex(substr($c, 0, 10));
        echo "<p style='font-family:monospace'>items.php first bytes (hex): <b>$first10</b></p>";
        echo "<p style='font-family:sans-serif'>Expected start: <b>3c3f706870</b> (which is &lt;?php)</p>";
        
        // Also show raw first 20 chars
        $visible = htmlspecialchars(substr($c, 0, 50));
        echo "<p style='font-family:monospace'>Raw start: <b>$visible</b></p>";
    }
}

// Also specifically check items.php
$items_path = dirname(dirname(__DIR__)) . '/vipaccsystem/inventory/manage/items.php';
if (file_exists($items_path)) {
    $c = file_get_contents($items_path);
    $hex = bin2hex(substr($c, 0, 6));
    echo "<hr><p style='font-family:monospace'><b>items.php</b> hex start: $hex</p>";
}

echo "<br><a href='/vipaccsystem/inventory/manage/items.php' style='font-family:sans-serif'>Test items.php</a>";
?>
