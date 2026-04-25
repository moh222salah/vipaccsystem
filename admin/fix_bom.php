<?php
// Find and fix all PHP files with UTF-8 BOM
$path = dirname(__DIR__); // vipaccsystem root
$fixed = [];
$checked = 0;

$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
foreach ($iter as $f) {
    if ($f->getExtension() !== 'php') continue;
    $checked++;
    $content = file_get_contents($f->getPathname());
    // Check for UTF-8 BOM (EF BB BF)
    if (substr($content, 0, 3) === "ï»¿") {
        $clean = substr($content, 3);
        file_put_contents($f->getPathname(), $clean);
        $fixed[] = str_replace($path, '', $f->getPathname());
    }
}

echo "<h2>BOM Fix Results</h2>";
echo "Checked: $checked files<br>";
echo "Fixed: " . count($fixed) . " files<br><br>";
if (count($fixed)) {
    echo "<b>Fixed files:</b><br>";
    foreach ($fixed as $ff) echo htmlspecialchars($ff) . "<br>";
} else {
    echo "<b style='color:orange'>No BOM files found in PHP files.</b><br><br>";
    echo "The issue might be in a non-PHP file or a different encoding problem.<br>";
    // Check .inc files too
    $iter2 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    $inc_fixed = [];
    foreach ($iter2 as $f) {
        if ($f->getExtension() !== 'inc') continue;
        $content = file_get_contents($f->getPathname());
        if (substr($content, 0, 3) === "ï»¿") {
            $clean = substr($content, 3);
            file_put_contents($f->getPathname(), $clean);
            $inc_fixed[] = str_replace($path, '', $f->getPathname());
        }
    }
    if (count($inc_fixed)) {
        echo "<br><b>Fixed .inc files:</b><br>";
        foreach ($inc_fixed as $ff) echo htmlspecialchars($ff) . "<br>";
    }
}
echo "<br><a href='/vipaccsystem/'>Test the site</a>";
?>
