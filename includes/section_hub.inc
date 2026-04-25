<?php
/**********************************************************************
    VIP Accounting System — Section Hub Renderer
    Renders a premium dashboard hub for PAGE-type sidebar sections.
    Used by section folder index.php files.
**********************************************************************/

if (!isset($path_to_root) || isset($_GET['path_to_root']) || isset($_POST['path_to_root']))
	die("Restricted access");

function render_section_hub($config)
{
	global $path_to_root;
	$is_ar = isset($_SESSION['language']) && $_SESSION['language']->code === 'ar_EG';
	$dir   = $is_ar ? 'rtl' : 'ltr';
	$title    = $is_ar ? $config['title_ar']    : $config['title_en'];
	$subtitle = $is_ar ? ($config['subtitle_ar'] ?? '') : ($config['subtitle_en'] ?? '');
	$color    = $config['color'] ?? '#1B3F7A';

	/* ── Hub CSS ── */
	echo <<<'HUBCSS'
<style>
.vip-hub { max-width:1100px; margin:0 auto; padding:8px 0 40px; }
.vip-hub-header {
  background:linear-gradient(135deg,#0A2342 0%,#0F3364 100%);
  border-radius:18px; padding:32px 36px 28px; margin-bottom:28px;
  position:relative; overflow:hidden;
  border-bottom:3px solid rgba(212,175,55,.5);
}
.vip-hub-header::after {
  content:''; position:absolute; top:-40px; right:-40px;
  width:160px; height:160px; border-radius:50%;
  background:radial-gradient(circle,rgba(212,175,55,.12) 0%,transparent 70%);
  pointer-events:none;
}
.vip-hub-title {
  font-family:'Playfair Display','Tajawal',sans-serif !important;
  font-size:28px; font-weight:800; color:#fff; margin:0 0 6px;
  letter-spacing:-.02em; position:relative; z-index:1;
}
.vip-hub-subtitle {
  font-family:'DM Sans','Tajawal',sans-serif;
  font-size:14px; color:rgba(255,255,255,.55); margin:0;
  font-weight:400; position:relative; z-index:1;
}
[dir="rtl"] .vip-hub-title { font-family:'Tajawal','Playfair Display',sans-serif !important; }
[dir="rtl"] .vip-hub-subtitle { font-family:'Tajawal','DM Sans',sans-serif; }
[dir="rtl"] .vip-hub-header::after { right:auto; left:-40px; }

.vip-hub-group { margin-bottom:24px; }
.vip-hub-group-title {
  font-family:'Playfair Display','Tajawal',sans-serif !important;
  font-size:13px; font-weight:700; text-transform:uppercase;
  letter-spacing:.1em; color:#0A2342; margin:0 0 14px;
  display:flex; align-items:center; gap:10px;
  padding-bottom:8px; border-bottom:1px solid #E2E8F0;
}
.vip-hub-group-title::before {
  content:''; display:block; width:18px; height:2.5px;
  background:linear-gradient(90deg,#D4AF37,#F0D060); border-radius:2px; flex-shrink:0;
}
[dir="rtl"] .vip-hub-group-title { font-family:'Tajawal','Playfair Display',sans-serif !important; }

.vip-hub-grid {
  display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
  gap:12px;
}
.vip-hub-card {
  display:flex; align-items:center; gap:12px;
  padding:14px 18px; border-radius:12px;
  background:#fff; border:1.5px solid #E4EAF4;
  text-decoration:none !important; color:#1C2B40 !important;
  transition:all .18s ease;
  box-shadow:0 1px 4px rgba(10,35,66,.04);
}
.vip-hub-card:hover {
  border-color:#B8CFEC; background:#F7FAFF;
  box-shadow:0 4px 16px rgba(10,35,66,.10);
  transform:translateY(-2px);
  text-decoration:none !important;
}
.vip-hub-card-dot {
  width:8px; height:8px; border-radius:50%; flex-shrink:0;
  box-shadow:0 0 6px currentColor;
}
.vip-hub-card-icon {
  width:36px; height:36px; border-radius:10px;
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0; color:#fff;
}
.vip-hub-card-label {
  font-family:'DM Sans','Tajawal',sans-serif;
  font-size:13.5px; font-weight:600; flex:1;
  line-height:1.4;
}
[dir="rtl"] .vip-hub-card-label { font-family:'Tajawal','DM Sans',sans-serif; font-weight:700; }
.vip-hub-card:hover .vip-hub-card-label { color:#0A2342; }

.vip-hub-card-arrow {
  width:14px; height:14px; color:#CBD8EC; flex-shrink:0;
  transition:all .15s; opacity:0;
}
.vip-hub-card:hover .vip-hub-card-arrow { opacity:1; color:#1B3F7A; transform:translateX(2px); }
[dir="rtl"] .vip-hub-card:hover .vip-hub-card-arrow { transform:translateX(-2px); }

@media(max-width:768px) {
  .vip-hub-header { padding:22px 20px 18px; border-radius:14px; }
  .vip-hub-title { font-size:22px; }
  .vip-hub-grid { grid-template-columns:1fr; }
}
</style>
HUBCSS;

	/* ── Render Hub ── */
	echo "<div class='vip-hub' dir='{$dir}'>\n";

	/* Header */
	echo "<div class='vip-hub-header'>\n";
	echo "  <h2 class='vip-hub-title'>" . htmlspecialchars($title) . "</h2>\n";
	if ($subtitle) echo "  <p class='vip-hub-subtitle'>" . htmlspecialchars($subtitle) . "</p>\n";
	echo "</div>\n";

	/* Groups */
	if (!empty($config['groups'])) {
		foreach ($config['groups'] as $group) {
			$gtitle = $is_ar ? ($group['title_ar'] ?? $group['title_en']) : $group['title_en'];
			echo "<div class='vip-hub-group'>\n";
			echo "  <h3 class='vip-hub-group-title'>" . htmlspecialchars($gtitle) . "</h3>\n";
			echo "  <div class='vip-hub-grid'>\n";

			foreach ($group['items'] as $item) {
				$label  = $is_ar ? ($item['ar'] ?? $item['en']) : $item['en'];
				$href   = $item['href'] ?? '#';
				$access = $item['access'] ?? '';
				$icolor = $item['color'] ?? $color;

				/* Access check */
				if ($access && isset($_SESSION["wa_current_user"])
					&& !$_SESSION["wa_current_user"]->can_access_page($access)) continue;

				/* Normalize href */
				if (strpos($href, 'http') !== 0 && strpos($href, '/') === 0) {
					$href = $path_to_root . $href;
				} elseif (strpos($href, 'http') !== 0 && strpos($href, '.') !== 0) {
					$href = $path_to_root . '/' . $href;
				}

				echo "    <a class='vip-hub-card' href='" . htmlspecialchars($href) . "'>\n";
				echo "      <span class='vip-hub-card-dot' style='background:{$icolor};color:{$icolor}'></span>\n";
				echo "      <span class='vip-hub-card-label'>" . htmlspecialchars($label) . "</span>\n";
				echo "      <svg class='vip-hub-card-arrow' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5'><polyline points='9 18 15 12 9 6'/></svg>\n";
				echo "    </a>\n";
			}

			echo "  </div>\n</div>\n";
		}
	}

	echo "</div>\n";
}
