<?php
/**
 * Renders a legal document from centralized sections.
 *
 * @var string $doc_title
 * @var string $doc_subtitle
 * @var array  $sections
 * @var string $doc_kind   'privacy' | 'terms'
 */
$doc_title    = $doc_title ?? 'Legal';
$doc_subtitle = $doc_subtitle ?? '';
$sections     = $sections ?? [];
$doc_kind     = $doc_kind ?? 'privacy';

$backUrl = function_exists('site_home_url') && str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin')
    ? rtrim(site_home_url(), '/') . '/'
    : rtrim($asset_base ?? './', '/');

$sibling = $doc_kind === 'privacy'
    ? ['label' => 'Terms & Conditions', 'url' => legal_terms_url(), 'icon' => 'bi-file-earmark-text']
    : ['label' => 'Privacy Policy', 'url' => legal_privacy_url(), 'icon' => 'bi-shield-lock'];

$heroIcon = $doc_kind === 'privacy' ? 'bi-shield-lock-fill' : 'bi-file-earmark-text-fill';
$heroBadge = $doc_kind === 'privacy' ? 'Privacy & Data' : 'Platform Terms';
?>
<header class="page-header legal-hero">
    <div class="glow-orb glow-orb-bottom-left"></div>
    <div class="container position-relative z-1 animate-on-scroll">
        <div class="legal-hero__toolbar">
            <a href="<?= e($backUrl) ?>" class="legal-back-btn">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Home</span>
            </a>
            <a href="<?= e($sibling['url']) ?>" class="legal-sibling-link">
                <i class="bi <?= e($sibling['icon']) ?> me-1"></i><?= e($sibling['label']) ?>
            </a>
        </div>

        <div class="legal-hero__body text-center">
            <div class="legal-hero__icon" aria-hidden="true">
                <i class="bi <?= e($heroIcon) ?>"></i>
            </div>
            <span class="badge-premium mb-3"><?= e($heroBadge) ?></span>
            <h1 class="display-5 fw-bold text-white mb-2"><?= e($doc_title) ?></h1>
            <?php if ($doc_subtitle !== ''): ?>
            <p class="text-secondary mx-auto legal-hero__subtitle"><?= e($doc_subtitle) ?></p>
            <?php endif; ?>

            <div class="legal-meta-bar mx-auto">
                <span class="legal-meta-item">
                    <i class="bi bi-calendar3"></i>
                    Last updated: <strong><?= e(legal_last_updated()) ?></strong>
                </span>
                <span class="legal-meta-divider"></span>
                <span class="legal-meta-item">
                    <i class="bi bi-list-ul"></i>
                    <?= count($sections) ?> sections
                </span>
            </div>
        </div>
    </div>
</header>

<section class="py-5 legal-page-section">
    <div class="container legal-page-wrap">
        <div class="row g-4 g-lg-5">

            <!-- Table of contents (desktop sticky) -->
            <aside class="col-lg-4 order-lg-1">
                <nav class="legal-toc glass-card" aria-label="Table of contents">
                    <div class="legal-toc__head">
                        <i class="bi bi-list-nested text-success"></i>
                        <span>On this page</span>
                    </div>
                    <ol class="legal-toc__list">
                        <?php foreach ($sections as $i => $section): ?>
                        <li>
                            <a href="#section-<?= (int) $i + 1 ?>" class="legal-toc__link">
                                <span class="legal-toc__num"><?= (int) $i + 1 ?></span>
                                <?= e($section['title'] ?? '') ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                    <div class="legal-toc__foot">
                        <a href="mailto:<?= e(site_contact_email()) ?>" class="legal-toc__contact">
                            <i class="bi bi-envelope me-1"></i> Legal questions?
                        </a>
                    </div>
                </nav>
            </aside>

            <!-- Main content -->
            <div class="col-lg-8 order-lg-2">
                <div class="legal-intro glass-card">
                    <div class="legal-intro__icon"><i class="bi bi-info-circle"></i></div>
                    <div>
                        <h2 class="legal-intro__title">Please read carefully</h2>
                        <p class="legal-intro__text mb-0">
                            <?php if ($doc_kind === 'privacy'): ?>
                            This policy explains what data we collect when you book courts, register as a player, or manage venues on Findownn — and how we keep it secure.
                            <?php else: ?>
                            These terms govern your use of Findownn as a player or venue owner. By using our platform, you agree to the rules below.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="legal-sections">
                    <?php foreach ($sections as $i => $section): ?>
                    <article class="legal-section-card" id="section-<?= (int) $i + 1 ?>">
                        <div class="legal-section-card__head">
                            <span class="legal-section-card__badge"><?= (int) $i + 1 ?></span>
                            <h2 class="legal-section-card__title"><?= e($section['title'] ?? '') ?></h2>
                        </div>
                        <div class="legal-section-card__body">
                            <?php foreach ($section['paragraphs'] ?? [] as $paragraph): ?>
                            <p class="legal-paragraph"><?= $paragraph ?></p>
                            <?php endforeach; ?>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>

                <!-- Contact CTA -->
                <div class="legal-cta glass-card">
                    <div class="legal-cta__content">
                        <h3 class="text-white h5 mb-2">Questions about this document?</h3>
                        <p class="text-secondary small mb-0">
                            Our team is happy to clarify any section. Reach out and we’ll respond as soon as possible.
                        </p>
                    </div>
                    <div class="legal-cta__actions">
                        <a href="mailto:<?= e(site_contact_email()) ?>" class="btn btn-premium btn-sm">
                            <i class="bi bi-envelope me-1"></i> Email Us
                        </a>
                        <a href="<?= e(legal_public_url('contact')) ?>" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-chat-dots me-1"></i> Support
                        </a>
                    </div>
                </div>

                <p class="legal-page-foot text-secondary small text-center mb-0">
                    Also see:
                    <a href="<?= e($sibling['url']) ?>" class="text-success text-decoration-none fw-600"><?= e($sibling['label']) ?></a>
                </p>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
  var links = document.querySelectorAll('.legal-toc__link');
  var sections = document.querySelectorAll('.legal-section-card');

  function setActive() {
    var scrollY = window.scrollY + 120;
    var current = null;
    sections.forEach(function (sec) {
      if (sec.offsetTop <= scrollY) current = sec.id;
    });
    links.forEach(function (link) {
      var active = link.getAttribute('href') === '#' + current;
      link.classList.toggle('is-active', active);
    });
  }

  links.forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      var target = document.querySelector(link.getAttribute('href'));
      if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  window.addEventListener('scroll', setActive, { passive: true });
  setActive();
})();
</script>
