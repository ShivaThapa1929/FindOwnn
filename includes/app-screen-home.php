<?php
/** Real FindOwnn app UI — Home screen (matches Flutter FindOwnnHomeScreen) */
/** @var string $asset_base Base path for assets — set by index.php / header.php */
if (!isset($asset_base)) {
    $script_dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $asset_base = ($script_dir === '') ? '/' : $script_dir . '/';
}
?>
<div class="findownn-app-screen findownn-app-screen--home findownn-app-screen--screenshot" aria-hidden="true">
    <img
        src="<?= $asset_base ?>assets/images/app-home-screen.png"
        alt=""
        loading="lazy"
        width="390"
        height="844">
</div>
