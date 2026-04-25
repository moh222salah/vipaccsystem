<?php
/**********************************************************************
    Copyright (C) Vip Accounting System, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/

define("FA_LOGOUT_PHP_FILE","");

$page_security = 'SA_OPEN';
$path_to_root="..";
include($path_to_root . "/includes/session.inc");
add_js_file('login.js');

include($path_to_root . "/includes/page/header.inc");
page_header(_("Logout"), true, false, '');

?>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════════
   VIP ACCOUNTING SYSTEM — LOGOUT PAGE
   Aesthetic: Luxury Dark · Royal Navy × Gold
═══════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --navy-deep:   #050E1F;
  --navy-main:   #0A1930;
  --navy-card:   #0D2040;
  --navy-border: rgba(212,175,55,0.18);
  --gold:        #D4AF37;
  --gold-light:  #F0D060;
  --gold-dim:    rgba(212,175,55,0.12);
  --gold-glow:   rgba(212,175,55,0.25);
  --white-hi:    #FFFFFF;
  --white-mid:   rgba(255,255,255,0.82);
  --white-low:   rgba(255,255,255,0.38);
  --white-ghost: rgba(255,255,255,0.06);
  --radius:      14px;
  --font-h:      'Playfair Display', serif;
  --font-b:      'DM Sans', sans-serif;
}

html, body {
  min-height: 100%;
  background: var(--navy-deep);
  font-family: var(--font-b);
  overflow-y: auto;
}

/* ── Animated Background ── */
.vip-bg {
  position: fixed; inset: 0; z-index: 0;
  background:
    radial-gradient(ellipse 80% 60% at 15% 10%, rgba(212,175,55,0.08) 0%, transparent 60%),
    radial-gradient(ellipse 60% 80% at 85% 90%, rgba(22,78,143,0.25) 0%, transparent 60%),
    radial-gradient(ellipse 100% 100% at 50% 50%, #0A1930 0%, #050E1F 100%);
}

.orb {
  position: fixed; border-radius: 50%; filter: blur(80px);
  animation: float 12s ease-in-out infinite; pointer-events: none; z-index: 0;
}
.orb-1 { width:500px;height:500px;background:radial-gradient(circle,rgba(212,175,55,0.07),transparent 70%);top:-150px;left:-100px;animation-delay:0s; }
.orb-2 { width:400px;height:400px;background:radial-gradient(circle,rgba(22,78,143,0.12),transparent 70%);bottom:-100px;right:-80px;animation-delay:-6s; }
.orb-3 { width:300px;height:300px;background:radial-gradient(circle,rgba(212,175,55,0.05),transparent 70%);top:50%;right:20%;animation-delay:-3s; }
@keyframes float {
  0%,100% { transform:translate(0,0) scale(1); }
  33%      { transform:translate(30px,-20px) scale(1.05); }
  66%      { transform:translate(-20px,15px) scale(0.95); }
}

.vip-grid {
  position:fixed;inset:0;z-index:0;
  background-image:linear-gradient(rgba(212,175,55,0.03) 1px,transparent 1px),linear-gradient(90deg,rgba(212,175,55,0.03) 1px,transparent 1px);
  background-size:60px 60px;
}

/* ── Main Layout ── */
.vip-wrap {
  position:relative;z-index:10;
  min-height:100vh;
  display:grid;
  grid-template-columns:1fr 480px 1fr;
  grid-template-rows:1fr auto 1fr;
  align-items:center;
  justify-items:center;
  padding:24px;
}

/* ── Brand Side ── */
.vip-brand-side {
  grid-column:1;grid-row:2;
  justify-self:end;padding-right:60px;
  animation:slideInLeft 0.8s cubic-bezier(.22,1,.36,1) forwards;
}
@keyframes slideInLeft {
  from { opacity:0;transform:translateX(-30px); }
  to   { opacity:1;transform:translateX(0); }
}
.vip-brand-tagline { font-family:var(--font-h);font-size:42px;font-weight:700;color:var(--white-hi);line-height:1.15;letter-spacing:-0.02em;max-width:340px; }
.vip-brand-tagline span { color:var(--gold);display:block; }
.vip-brand-sub { margin-top:16px;font-size:14px;color:var(--white-low);font-weight:400;letter-spacing:0.04em;text-transform:uppercase;max-width:280px;line-height:1.7; }
.vip-brand-divider { width:48px;height:2px;background:linear-gradient(90deg,var(--gold),transparent);margin:20px 0; }
.vip-brand-stats { display:flex;gap:28px;margin-top:28px; }
.vip-stat { display:flex;flex-direction:column; }
.vip-stat-num { font-family:var(--font-h);font-size:26px;font-weight:700;color:var(--gold); }
.vip-stat-lbl { font-size:11px;color:var(--white-low);text-transform:uppercase;letter-spacing:.08em;margin-top:2px; }

/* ── Card ── */
.vip-card {
  grid-column:2;grid-row:2;width:100%;
  background:linear-gradient(160deg,rgba(13,32,64,0.95) 0%,rgba(5,14,31,0.98) 100%);
  border:1px solid var(--navy-border);
  border-radius:20px;
  padding:44px 40px;
  box-shadow:0 32px 80px rgba(0,0,0,0.6),0 0 0 1px rgba(212,175,55,0.06),inset 0 1px 0 rgba(255,255,255,0.05);
  backdrop-filter:blur(20px);
  animation:cardIn 0.7s cubic-bezier(.22,1,.36,1) 0.1s both;
  text-align:center;
}
@keyframes cardIn {
  from { opacity:0;transform:translateY(24px) scale(0.97); }
  to   { opacity:1;transform:translateY(0) scale(1); }
}

.vip-card-header { margin-bottom:32px; }
.vip-logo-mark {
  width:56px;height:56px;border-radius:14px;
  background:linear-gradient(135deg,var(--gold),#B8960A);
  display:flex;align-items:center;justify-content:center;
  margin:0 auto 16px;
  box-shadow:0 8px 24px rgba(212,175,55,0.35);
}
.vip-card-title { font-family:var(--font-h);font-size:22px;font-weight:700;color:var(--white-hi);letter-spacing:-0.01em; }
.vip-card-title span { color:var(--gold); }
.vip-card-subtitle { font-size:12px;color:var(--white-low);letter-spacing:.08em;text-transform:uppercase;margin-top:6px; }

/* ── Thank You Message ── */
.vip-thankyou {
  font-family:var(--font-h);
  font-size:20px;
  color:var(--white-mid);
  margin:20px 0 16px;
  line-height:1.5;
}
.vip-thankyou strong { color:var(--gold); }
.vip-version-text {
  font-size:13px;
  color:var(--white-low);
  margin-bottom:32px;
  letter-spacing:.03em;
}

/* ── Login Again Link ── */
.vip-login-again {
  display:inline-block;
  padding:15px 40px;
  background:linear-gradient(135deg,var(--gold) 0%,#B8960A 100%);
  border-radius:10px;
  color:#0A1930;
  font-family:var(--font-b);
  font-size:14px;font-weight:700;
  letter-spacing:.06em;text-transform:uppercase;
  text-decoration:none;
  box-shadow:0 4px 20px rgba(212,175,55,0.30);
  transition:all .22s ease;
  margin-top:8px;
}
.vip-login-again:hover { transform:translateY(-2px);box-shadow:0 8px 28px rgba(212,175,55,0.45);color:#0A1930; }

/* ── Gold Separator ── */
.vip-sep {
  width:60px;height:1px;
  background:linear-gradient(90deg,transparent,var(--gold),transparent);
  margin:24px auto;
}

/* ── Footer ── */
.vip-footer {
  grid-column:1/-1;grid-row:3;align-self:end;padding-bottom:20px;
  display:flex;align-items:center;justify-content:center;gap:24px;
  animation:fadeUp 0.6s ease 0.8s both;
}
@keyframes fadeUp {
  from { opacity:0;transform:translateY(10px); }
  to   { opacity:1;transform:translateY(0); }
}
.vip-footer a { font-size:11px;color:var(--white-low);text-decoration:none;letter-spacing:.06em;text-transform:uppercase;transition:color .2s; }
.vip-footer a:hover { color:var(--gold); }
.vip-footer-sep { width:3px;height:3px;border-radius:50%;background:var(--white-ghost); }
.vip-date { font-size:11px;color:var(--white-low);letter-spacing:.04em; }

/* ── Hide / neutralize FA framework wrappers ── */
table.login, table.titletext, table.bottomBar,
table.footer, #_page_body { display:none !important; }
.ex-shell, .ex-body, .ex-body.ex-nomenu, main.ex-main {
  display:contents !important;
}
#ajaxmark, .ex-page-title { display:none !important; }
/* Hide the ajax-spinner container (first child of ex-main) */
main.ex-main > div:first-child:not(.vip-bg):not(.vip-wrap):not(.vip-grid):not(.orb):not(.vip-dev-sig) {
  display:none !important;
}

@media (max-width:960px) {
  .vip-wrap { grid-template-columns:1fr;grid-template-rows:auto; }
  .vip-brand-side { display:none; }
  .vip-card { grid-column:1; }
  .vip-footer { grid-column:1; }
  .vip-dev-sig { left:50% !important; right:auto !important; transform:translateX(-50%); text-align:center; bottom:12px; }
  .vip-dev-links { justify-content:center; }
}

/* ── Brand feature list ── */
.vip-brand-features { margin-top:24px; display:flex; flex-direction:column; gap:8px; }
.vip-brand-feat { display:flex; align-items:center; gap:10px; font-size:12.5px; color:var(--white-low); letter-spacing:.02em; }
.vip-brand-feat-dot { width:5px; height:5px; border-radius:50%; background:var(--gold); flex-shrink:0; opacity:0.7; }

/* ── Logout success checkmark animation ── */
@keyframes checkDraw {
  from { stroke-dashoffset: 60; opacity: 0; }
  to   { stroke-dashoffset: 0;  opacity: 1; }
}
.vip-check-ring {
  width: 70px; height: 70px; margin: 0 auto 20px;
  display: flex; align-items: center; justify-content: center;
  background: rgba(16,185,129,.10);
  border: 1.5px solid rgba(16,185,129,.25);
  border-radius: 50%;
}
.vip-check-ring svg path {
  stroke-dasharray: 60; stroke-dashoffset: 60;
  animation: checkDraw 0.7s cubic-bezier(.22,1,.36,1) 0.4s forwards;
}

/* ── Setup Again Button ── */
.vip-setup-again {
  display:inline-flex; align-items:center; gap:8px;
  padding:13px 32px;
  background:transparent;
  border:1.5px solid rgba(212,175,55,.30);
  border-radius:10px;
  color:var(--gold);
  font-family:var(--font-b);
  font-size:13px; font-weight:600;
  letter-spacing:.04em; text-transform:uppercase;
  text-decoration:none;
  transition:all .22s ease;
  margin-top:12px;
}
.vip-setup-again:hover {
  background:rgba(212,175,55,.08);
  border-color:var(--gold);
  color:var(--gold);
  transform:translateY(-2px);
  box-shadow:0 4px 16px rgba(212,175,55,.15);
}
.vip-setup-again svg { opacity:.7; transition:opacity .2s; }
.vip-setup-again:hover svg { opacity:1; }

/* ── Buttons Row ── */
.vip-actions-row {
  display:flex; gap:14px; justify-content:center; flex-wrap:wrap; margin-top:8px;
}

/* ══════════════════════════════════════
   DEVELOPER SIGNATURE — Premium Design
══════════════════════════════════════ */
.vip-dev-sig {
  position:fixed; bottom:22px; right:26px; z-index:200;
  min-width:280px;
  backdrop-filter:blur(24px) saturate(180%);
  -webkit-backdrop-filter:blur(24px) saturate(180%);
  background:
    linear-gradient(135deg, rgba(13,32,64,.92) 0%, rgba(5,14,31,.95) 100%);
  border:1px solid rgba(212,175,55,.22);
  border-radius:20px;
  padding:0;
  overflow:hidden;
  box-shadow:
    0 8px 32px rgba(0,0,0,.50),
    0 0 0 1px rgba(212,175,55,.06),
    inset 0 1px 0 rgba(255,255,255,.04);
  animation:sigSlideIn 0.8s cubic-bezier(.22,1,.36,1) 1.2s both;
  transition:transform .3s ease, box-shadow .3s ease, border-color .3s ease;
}
@keyframes sigSlideIn {
  from { opacity:0; transform:translateY(30px) scale(0.95); }
  to   { opacity:1; transform:translateY(0) scale(1); }
}
.vip-dev-sig:hover {
  transform:translateY(-5px);
  border-color:rgba(212,175,55,.45);
  box-shadow:
    0 16px 48px rgba(0,0,0,.55),
    0 0 0 1px rgba(212,175,55,.12),
    0 0 40px rgba(212,175,55,.06);
}

/* Gold accent bar at top */
.vip-dev-sig::before {
  content:'';
  display:block;
  height:2px;
  background:linear-gradient(90deg, transparent 5%, var(--gold) 30%, var(--gold-light) 50%, var(--gold) 70%, transparent 95%);
  opacity:.7;
}

/* Inner padding */
.vip-dev-inner { padding:18px 22px 16px; }

/* Label row with icon */
.vip-dev-label-row {
  display:flex; align-items:center; gap:7px; margin-bottom:10px;
}
.vip-dev-label-icon {
  width:22px; height:22px; border-radius:6px;
  background:linear-gradient(135deg, rgba(212,175,55,.15), rgba(212,175,55,.05));
  border:1px solid rgba(212,175,55,.18);
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0;
}
.vip-dev-label {
  font-size:9px; color:rgba(255,255,255,.40);
  letter-spacing:.08em; text-transform:uppercase;
  line-height:1.3;
}
.vip-dev-label strong {
  color:rgba(255,255,255,.55); font-weight:600;
}

/* Name — large & golden */
.vip-dev-name {
  font-family:var(--font-h);
  font-size:17px; font-weight:800;
  background:linear-gradient(135deg, var(--gold-light) 0%, var(--gold) 50%, #C4992A 100%);
  -webkit-background-clip:text; -webkit-text-fill-color:transparent;
  background-clip:text;
  letter-spacing:.04em;
  margin-bottom:4px;
  text-align:right;
}

/* Title badge */
.vip-dev-badge {
  display:inline-flex; align-items:center; gap:5px;
  background:rgba(212,175,55,.08);
  border:1px solid rgba(212,175,55,.14);
  border-radius:20px;
  padding:4px 12px;
  margin-bottom:12px;
  float:right;
}
.vip-dev-badge-dot {
  width:5px; height:5px; border-radius:50%;
  background:var(--gold);
  box-shadow:0 0 6px rgba(212,175,55,.6);
  animation:dotPulse 2.5s ease-in-out infinite;
}
.vip-dev-badge-text {
  font-size:8.5px; color:rgba(255,255,255,.45);
  letter-spacing:.06em; text-transform:uppercase; font-weight:500;
}

/* Divider */
.vip-dev-divider {
  clear:both;
  height:1px;
  background:linear-gradient(90deg, transparent, rgba(212,175,55,.18), transparent);
  margin:8px 0 12px;
}

/* Links row */
.vip-dev-links {
  display:flex; gap:6px; justify-content:flex-end; flex-wrap:wrap;
}
.vip-dev-links a {
  display:inline-flex; align-items:center; gap:5px;
  padding:5px 10px;
  border-radius:8px;
  background:rgba(255,255,255,.03);
  border:1px solid rgba(255,255,255,.06);
  font-size:9.5px; color:rgba(255,255,255,.40);
  text-decoration:none; letter-spacing:.03em;
  font-weight:500;
  transition:all .2s ease;
}
.vip-dev-links a:hover {
  background:rgba(212,175,55,.10);
  border-color:rgba(212,175,55,.25);
  color:var(--gold);
  transform:translateY(-1px);
}
.vip-dev-links a svg { flex-shrink:0; opacity:.6; transition:opacity .2s; }
.vip-dev-links a:hover svg { opacity:1; }

/* RTL: Signature flips to left side */
[dir="rtl"] .vip-dev-sig { right:auto; left:26px; }
[dir="rtl"] .vip-dev-name { text-align:left; }
[dir="rtl"] .vip-dev-badge { float:left; }
[dir="rtl"] .vip-dev-links { justify-content:flex-start; }
</style>
<?php

// ── VIP LOGOUT UI ──
echo "<div class='vip-bg'></div>\n";
echo "<div class='vip-grid'></div>\n";
echo "<div class='orb orb-1'></div>\n";
echo "<div class='orb orb-2'></div>\n";
echo "<div class='orb orb-3'></div>\n";

echo "<div class='vip-wrap'>\n";

// ── Brand Side ──
echo "<div class='vip-brand-side'>\n";
echo "  <div class='vip-brand-tagline'><span>VIP Accounting</span>System</div>\n";
echo "  <div class='vip-brand-divider'></div>\n";
echo "  <div class='vip-brand-sub'> </div>\n";
echo "  <div class='vip-brand-features'>\n";
echo "    <div class='vip-brand-feat'><span class='vip-brand-feat-dot'></span>Session data cleared securely</div>\n";
echo "    <div class='vip-brand-feat'><span class='vip-brand-feat-dot'></span>All changes saved automatically</div>\n";
echo "    <div class='vip-brand-feat'><span class='vip-brand-feat-dot'></span>See you next time!</div>\n";
echo "  </div>\n";
echo "  <div class='vip-brand-stats'>\n";
	echo "    <div class='vip-stat'><span class='vip-stat-num'>VIP</span><span class='vip-stat-lbl'>System</span></div>\n";
	echo "    <div class='vip-stat'><span class='vip-stat-num'>V".$version."</span><span class='vip-stat-lbl'>Version</span></div>\n";
	echo "    <div class='vip-stat'><span class='vip-stat-num'>ERP</span><span class='vip-stat-lbl'>Platform</span></div>\n";
echo "  </div>\n";
echo "</div>\n";

// ── Card ──
echo "<div class='vip-card'>\n";
echo "  <div class='vip-card-header'>\n";
echo "    <div class='vip-logo-mark'>\n";
echo "      <svg width='28' height='28' viewBox='0 0 24 24' fill='none'>\n";
echo "        <path d='M12 2L2 7l10 5 10-5-10-5z' fill='#0A1930' stroke='#0A1930' stroke-width='.5'/>\n";
echo "        <path d='M2 17l10 5 10-5' stroke='#0A1930' stroke-width='2' stroke-linecap='round'/>\n";
echo "        <path d='M2 12l10 5 10-5' stroke='#0A1930' stroke-width='2' stroke-linecap='round'/>\n";
echo "      </svg>\n";
echo "    </div>\n";
echo "  <div class='vip-card-title'>Session  <span>Ended </span> Securely</div>\n";
echo "  <div class='vip-card-title'>  <span>Thanks for </span> using System </div>\n";
echo "  </div>\n";

echo "  <div class='vip-check-ring'>\n";
echo "    <svg width='32' height='32' viewBox='0 0 24 24' fill='none'>\n";
echo "      <path d='M5 13l4 4L19 7' stroke='#10B981' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'/>\n";
echo "    </svg>\n";
echo "  </div>\n";
echo "  <div class='vip-sep'></div>\n";
echo "  <div class='vip-actions-row'>\n";
echo "    <a class='vip-login-again' href='$path_to_root/admin/dashboard.php'>"
    ."<svg width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><path d='M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4'/><polyline points='10 17 15 12 10 7'/><line x1='15' y1='12' x2='3' y2='12'/></svg>"
    ."&nbsp;&nbsp;Login Again</a>\n";
echo "  </div>\n";
echo "</div>\n"; // /vip-card

// ── Footer ──
echo "<div class='vip-footer'>\n";
if (isset($_SESSION['wa_current_user']))
    $date = Today() . " | " . Now();
else
    $date = date("d/m/Y") . " | " . date("h:i a");
echo "</div>\n";

// ── Developer Signature — Premium ──
echo "<div class='vip-dev-sig'>\n";
echo "  <div class='vip-dev-inner'>\n";
echo "    <div class='vip-dev-label-row'>\n";
echo "      <div class='vip-dev-label-icon'><svg width='11' height='11' viewBox='0 0 24 24' fill='none' stroke='#D4AF37' stroke-width='2.5' stroke-linecap='round'><path d='M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2'/><circle cx='9' cy='7' r='4'/><path d='M22 21v-2a4 4 0 0 0-3-3.87'/><path d='M16 3.13a4 4 0 0 1 0 7.75'/></svg></div>\n";
echo "      <div class='vip-dev-label'><strong>Developed by</strong> &middot; مطور بواسطة</div>\n";
echo "    </div>\n";
echo "    <div class='vip-dev-name'>MOHAMED SALAH</div>\n";
echo "    <div class='vip-dev-badge'><span class='vip-dev-badge-dot'></span><span class='vip-dev-badge-text'>ERPNext &amp; Automation Expert</span></div>\n";
echo "    <div class='vip-dev-divider'></div>\n";
echo "    <div class='vip-dev-links'>\n";
echo "      <a href='https://linkedin.com/in/mo222salah' target='_blank' rel='noopener'>"
    ."<svg width='12' height='12' viewBox='0 0 24 24' fill='currentColor'>"
    ."<path d='M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z'/>"
    ."<rect x='2' y='9' width='4' height='12'/><circle cx='4' cy='4' r='2'/></svg>"
    ."LinkedIn</a>\n";
echo "      <a href='https://moh222salah.github.io/cv' target='_blank' rel='noopener'>"
    ."<svg width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'>"
    ."<path d='M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71'/>"
    ."<path d='M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71'/></svg>"
    ."Portfolio</a>\n";
echo "      <a href='https://wa.me/201113903070' target='_blank' rel='noopener'>"
    ."<svg width='12' height='12' viewBox='0 0 24 24' fill='currentColor'>"
    ."<path d='M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z'/></svg>"
    ."WhatsApp</a>\n";
echo "    </div>\n";
echo "  </div>\n";
echo "</div>\n";

echo "</div>\n"; // /vip-wrap

end_page(false, true);
session_unset();
@session_destroy();