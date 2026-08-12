<?php
/** iOS app home — full iPhone mockup image (frame included in PNG) */
/** @var string $asset_base Base path for assets — set by index.php / header.php */
if (!isset($asset_base)) {
    $script_dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $asset_base = ($script_dir === '') ? '/' : $script_dir . '/';
}
?>
<img
    class="hero-ios-mockup"
    src="<?= $asset_base ?>assets/images/app-home-screen.png"
    alt="FindOwnn iOS app — home screen"
    loading="eager"
    decoding="async"
    width="445"
    height="804">
