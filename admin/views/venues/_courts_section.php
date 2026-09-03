<?php
/**
 * Courts section for venue create/edit forms.
 *
 * @var array  $sports
 * @var array  $courts       Existing or old-input court rows
 * @var array  $errors
 * @var string $mode         'create' | 'edit'
 */
$sports = $sports ?? [];
$courts = $courts ?? [];
$errors = $errors ?? [];
$mode   = $mode ?? 'create';

if ($courts === []) {
    $courts = [[]];
}
?>
<div class="section-divider mb-3">
  <span class="section-divider-label">Courts / Play Areas</span>
</div>

<p class="text-muted small mb-3">
  <?php if ($mode === 'create'): ?>
    Add at least one court or play area. Players book individual courts — not the whole venue.
  <?php else: ?>
    Update existing courts or add new ones. To manage photos, use <strong>Manage Courts</strong> on the venue page.
  <?php endif; ?>
</p>

<?php if (isset($errors['courts'])): ?>
<div class="alert alert-danger py-2 small mb-3"><?= e($errors['courts']) ?></div>
<?php endif; ?>

<div id="courtRowsContainer">
  <?php foreach ($courts as $i => $court): ?>
    <?php
    $index = is_numeric($i) ? (int) $i : 0;
    include ROOT_PATH . '/views/courts/_form_row.php';
    ?>
  <?php endforeach; ?>
</div>

<button type="button" class="btn btn-sm btn-outline-success mb-4" id="addCourtRowBtn">
  <i class="bi bi-plus-lg me-1"></i>Add Another Court
</button>

<template id="courtRowTemplate">
<?php
$index = '__INDEX__';
$court = [];
$removable = true;
include ROOT_PATH . '/views/courts/_form_row.php';
?>
</template>

<script>
(function () {
  var container = document.getElementById('courtRowsContainer');
  var template = document.getElementById('courtRowTemplate');
  var addBtn = document.getElementById('addCourtRowBtn');
  var venueTypeSelect = document.querySelector('select[name="type"]');
  var venuePriceInput = document.querySelector('input[name="price_per_hour"]');

  var sportSlugMap = {
    box_cricket: 'box-cricket',
    pickleball: 'pickleball',
    football: 'football',
    badminton: 'badminton',
    tennis: 'tennis'
  };

  function nextIndex() {
    var rows = container.querySelectorAll('.court-form-row');
    var max = -1;
    rows.forEach(function (row) {
      var idx = parseInt(row.getAttribute('data-court-index'), 10);
      if (!isNaN(idx) && idx > max) max = idx;
    });
    return max + 1;
  }

  function syncSportFromVenueType(scope) {
    if (!venueTypeSelect) return;
    var slug = sportSlugMap[venueTypeSelect.value] || venueTypeSelect.value;
    (scope || container).querySelectorAll('.court-sport-select').forEach(function (sel) {
      if (sel.value) return;
      var match = sel.querySelector('option[data-slug="' + slug + '"]');
      if (match) sel.value = match.value;
    });
  }

  function syncPriceFromVenue(scope) {
    if (!venuePriceInput || !venuePriceInput.value) return;
    (scope || container).querySelectorAll('.court-price-input').forEach(function (inp) {
      if (!inp.value) inp.value = venuePriceInput.value;
    });
  }

  if (addBtn && template) {
    addBtn.addEventListener('click', function () {
      var idx = nextIndex();
      var html = template.innerHTML.replace(/__INDEX__/g, String(idx));
      var wrap = document.createElement('div');
      wrap.innerHTML = html.trim();
      var row = wrap.firstElementChild;
      container.appendChild(row);
      syncSportFromVenueType(row);
      syncPriceFromVenue(row);
      bindRemove(row);
    });
  }

  function bindRemove(scope) {
    (scope || container).querySelectorAll('.court-row-remove').forEach(function (btn) {
      if (btn.dataset.bound) return;
      btn.dataset.bound = '1';
      btn.addEventListener('click', function () {
        var target = document.getElementById(btn.getAttribute('data-target'));
        if (target) target.remove();
      });
    });
  }

  bindRemove();
  syncSportFromVenueType();
  syncPriceFromVenue();

  if (venueTypeSelect) {
    venueTypeSelect.addEventListener('change', function () {
      container.querySelectorAll('.court-sport-select').forEach(function (sel) { sel.value = ''; });
      syncSportFromVenueType();
    });
  }
  if (venuePriceInput) {
    venuePriceInput.addEventListener('change', function () { syncPriceFromVenue(); });
  }
})();
</script>
