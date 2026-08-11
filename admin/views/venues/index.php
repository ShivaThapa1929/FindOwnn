<?php $user = auth(); $role = $user['role']; ?>

<!-- Header Bar -->
<div class="d-flex flex-wrap gap-3 mb-4 align-items-center justify-content-between">
  <div>
    <h5 class="mb-1" style="color: var(--text); font-weight: 600;">Manage Venues</h5>
    <p class="text-muted small mb-0">Total: <?= $result['total'] ?> venues</p>
  </div>
  <?php if (in_array($role, ['super_admin','venue_owner'])): ?>
  <a href="<?= url('/venues/create') ?>" class="btn btn-primary">
    <i class="bi bi-plus-lg me-2"></i>Add New Venue
  </a>
  <?php endif; ?>
</div>

<!-- Advanced Filters -->
<div class="venue-filters mb-4">
  <div id="filterForm">
    <div class="row g-3">
      <!-- Search -->
      <div class="col-12 col-md-4">
        <div class="filter-group">
          <label class="filter-label"><i class="bi bi-search me-1"></i>Search</label>
          <input type="text" name="search" value="<?= e($search) ?>" 
                 class="form-control form-control-sm" 
                 placeholder="Venue name, city, owner..."
                 id="searchInput">
        </div>
      </div>

      <!-- Status Filter -->
      <div class="col-6 col-md-2">
        <div class="filter-group">
          <label class="filter-label"><i class="bi bi-check-circle me-1"></i>Status</label>
          <select name="status" class="form-select form-select-sm filter-select">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Status</option>
            <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="approved" <?= $filter === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="rejected" <?= $filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
          </select>
        </div>
      </div>

      <!-- City Filter -->
      <div class="col-6 col-md-2">
        <div class="filter-group">
          <label class="filter-label"><i class="bi bi-geo-alt me-1"></i>City</label>
          <select name="city" class="form-select form-select-sm filter-select">
            <option value="">All Cities</option>
            <?php foreach ($cities as $c): ?>
              <option value="<?= e($c['city']) ?>" <?= $city === $c['city'] ? 'selected' : '' ?>>
                <?= e($c['city']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Verified Badge Filter -->
      <div class="col-6 col-md-2">
        <div class="filter-group">
          <label class="filter-label"><i class="bi bi-patch-check me-1"></i>Verified</label>
          <select name="verified" class="form-select form-select-sm filter-select">
            <option value="">All</option>
            <option value="yes" <?= $verified === 'yes' ? 'selected' : '' ?>>Verified</option>
            <option value="no" <?= $verified === 'no' ? 'selected' : '' ?>>Not Verified</option>
          </select>
        </div>
      </div>

      <!-- Sort By -->
      <div class="col-6 col-md-2">
        <div class="filter-group">
          <label class="filter-label"><i class="bi bi-sort-down me-1"></i>Sort By</label>
          <select name="sort" class="form-select form-select-sm filter-select">
            <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest First</option>
            <option value="oldest" <?= $sortBy === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
            <option value="name_asc" <?= $sortBy === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
            <option value="name_desc" <?= $sortBy === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
            <option value="price_low" <?= $sortBy === 'price_low' ? 'selected' : '' ?>>Price (Low-High)</option>
            <option value="price_high" <?= $sortBy === 'price_high' ? 'selected' : '' ?>>Price (High-Low)</option>
            <option value="city" <?= $sortBy === 'city' ? 'selected' : '' ?>>City</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Filter Actions -->
    <div class="d-flex gap-2 mt-3 flex-wrap align-items-center">
      <button type="button" id="applyFilters" class="btn btn-sm btn-primary">
        <i class="bi bi-filter me-1"></i>Apply Filters
      </button>
      <?php if ($search || $filter !== 'all' || $city || $verified || $sortBy !== 'newest'): ?>
        <button type="button" id="clearFilters" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-x-circle me-1"></i>Clear All
        </button>
      <?php endif; ?>
      <div class="ms-auto text-muted small d-flex align-items-center gap-2">
        <span id="resultCount">Showing <?= count($result['data']) ?> of <?= $result['total'] ?> results</span>
        <span id="filterLoading" class="spinner-border spinner-border-sm" style="display: none;"></span>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('filterForm');
  const filterSelects = document.querySelectorAll('.filter-select');
  const searchInput = document.getElementById('searchInput');
  const applyButton = document.getElementById('applyFilters');
  const filterLoading = document.getElementById('filterLoading');
  let searchTimeout;

  // Auto-submit on select change
  filterSelects.forEach(select => {
    select.addEventListener('change', function() {
      applyFiltersInstantly();
    });
  });

  // Search with debounce
  searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
      applyFiltersInstantly();
    }, 500); // Wait 500ms after user stops typing
  });

  // Manual apply button
  applyButton.addEventListener('click', function() {
    applyFiltersInstantly();
  });

  // Search on Enter key
  searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      applyFiltersInstantly();
    }
  });

  function applyFiltersInstantly() {
    // Show loading indicator
    filterLoading.style.display = 'inline-block';
    applyButton.disabled = true;

    // Build URL with current filter values
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    // Remove empty values
    for (let [key, value] of Array.from(params.entries())) {
      if (!value || value === 'all' || (key === 'sort' && value === 'newest')) {
        params.delete(key);
      }
    }

    // Update URL and reload page
    const newUrl = params.toString() ? '?' + params.toString() : window.location.pathname;
    window.location.href = newUrl;
  }
});
</script>

<!-- Active Filters Tags -->
<?php 
$hasActiveFilters = $search || $filter !== 'all' || $city || $verified || $sortBy !== 'newest';
if ($hasActiveFilters): 
?>
<div class="active-filters mb-3">
  <span class="active-filters__label">Active Filters:</span>
  <?php if ($search): ?>
    <span class="active-filters__tag">
      Search: "<?= e($search) ?>"
      <a href="#" class="active-filters__remove" data-clear="search"><i class="bi bi-x"></i></a>
    </span>
  <?php endif; ?>
  <?php if ($filter !== 'all'): ?>
    <span class="active-filters__tag">
      Status: <?= ucfirst($filter) ?>
      <a href="#" class="active-filters__remove" data-clear="status"><i class="bi bi-x"></i></a>
    </span>
  <?php endif; ?>
  <?php if ($city): ?>
    <span class="active-filters__tag">
      City: <?= e($city) ?>
      <a href="#" class="active-filters__remove" data-clear="city"><i class="bi bi-x"></i></a>
    </span>
  <?php endif; ?>
  <?php if ($verified): ?>
    <span class="active-filters__tag">
      Verified: <?= ucfirst($verified) ?>
      <a href="#" class="active-filters__remove" data-clear="verified"><i class="bi bi-x"></i></a>
    </span>
  <?php endif; ?>
  <?php if ($sortBy !== 'newest'): ?>
    <span class="active-filters__tag">
      Sort: <?= match($sortBy) {
        'oldest' => 'Oldest First',
        'name_asc' => 'Name (A-Z)',
        'name_desc' => 'Name (Z-A)',
        'price_low' => 'Price (Low-High)',
        'price_high' => 'Price (High-Low)',
        'city' => 'City',
        default => ucfirst($sortBy)
      } ?>
      <a href="#" class="active-filters__remove" data-clear="sort"><i class="bi bi-x"></i></a>
    </span>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Venues Grid Container -->
<div id="venuesContainer">
<?php if (empty($result['data'])): ?>
<div class="panel text-center py-5">
  <i class="bi bi-building-x" style="font-size: 3rem; color: var(--text-muted); opacity: 0.5;"></i>
  <p class="text-muted mt-3 mb-0">No venues found</p>
</div>
<?php else: ?>
<div class="row g-3 mb-4">
  <?php foreach ($result['data'] as $v): ?>
  <div class="col-12 col-md-6 col-lg-4">
    <div class="venue-card">
      <!-- Card Header with Status -->
      <div class="venue-card__header">
        <div class="d-flex align-items-start justify-content-between">
          <div class="flex-grow-1">
            <h6 class="venue-card__title">
              <a href="<?= url('/venues/'.$v['id']) ?>" class="text-decoration-none">
                <?= e($v['name']) ?>
              </a>
            </h6>
            <div class="venue-card__location">
              <i class="bi bi-geo-alt-fill"></i>
              <?= e($v['city']) ?>
            </div>
          </div>
          <div class="d-flex flex-column gap-1 align-items-end">
            <?= statusBadge($v['verification_status']) ?>
            <?php if ($v['is_verified']): ?>
              <span class="badge bg-success"><i class="bi bi-patch-check-fill"></i></span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Card Body -->
      <div class="venue-card__body">
        <!-- Owner Info -->
        <div class="venue-card__owner">
          <div class="venue-card__owner-avatar">
            <i class="bi bi-person-circle"></i>
          </div>
          <div class="venue-card__owner-info">
            <div class="venue-card__owner-name"><?= e($v['owner_name'] ?? '—') ?></div>
            <div class="venue-card__owner-email"><?= e($v['owner_email'] ?? '') ?></div>
          </div>
        </div>

        <!-- Sports Tags -->
        <div class="venue-card__sports">
          <?php
          $sports = isset($v['sports']) ? $v['sports'] : '';
          if ($sports):
            $sportList = explode(',', $sports);
            foreach($sportList as $sport):
              $sport = trim($sport);
              if($sport):
          ?>
            <span class="venue-card__sport-tag"><?= e($sport) ?></span>
          <?php 
              endif;
            endforeach;
          else: ?>
            <span class="text-muted small">No sports available</span>
          <?php endif; ?>
        </div>

        <!-- Price Info -->
        <div class="venue-card__price">
          <div class="venue-card__price-label">Starting from</div>
          <div class="venue-card__price-amount">₹<?= number_format($v['price_per_hour']) ?><span>/hr</span></div>
        </div>
      </div>

      <!-- Card Footer with Actions -->
      <div class="venue-card__footer">
        <a href="<?= url('/venues/'.$v['id']) ?>" class="btn btn-sm btn-outline-secondary flex-fill">
          <i class="bi bi-eye me-1"></i>View
        </a>
        <a href="<?= url('/venues/'.$v['id'].'/edit') ?>" class="btn btn-sm btn-outline-primary flex-fill">
          <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <?php if (in_array($role, ['super_admin','admin'])): ?>
          <?php if ($v['verification_status'] === 'pending'): ?>
          <div class="dropdown">
            <button class="btn btn-sm btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
              <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <form action="<?= url('/venues/'.$v['id'].'/approve') ?>" method="POST">
                  <?= csrf_field() ?>
                  <button class="dropdown-item text-success">
                    <i class="bi bi-check-lg me-2"></i>Approve
                  </button>
                </form>
              </li>
              <li>
                <button class="dropdown-item text-danger" onclick="rejectVenue(<?= $v['id'] ?>)">
                  <i class="bi bi-x-lg me-2"></i>Reject
                </button>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form action="<?= url('/venues/'.$v['id'].'/delete') ?>" method="POST"
                  onsubmit="return confirm('Delete this venue permanently?')">
                  <?= csrf_field() ?>
                  <button class="dropdown-item text-danger">
                    <i class="bi bi-trash me-2"></i>Delete
                  </button>
                </form>
              </li>
            </ul>
          </div>
          <?php else: ?>
          <form action="<?= url('/venues/'.$v['id'].'/delete') ?>" method="POST" class="d-inline"
            onsubmit="return confirm('Delete this venue permanently?')">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-outline-danger" title="Delete">
              <i class="bi bi-trash"></i>
            </button>
          </form>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4">
  <small class="text-muted">Showing <?= count($result['data']) ?> of <?= $result['total'] ?> venues</small>
  <?php 
    $filterParams = http_build_query(array_filter([
      'status' => $filter !== 'all' ? $filter : null,
      'search' => $search ?: null,
      'city' => $city ?: null,
      'verified' => $verified ?: null,
      'sort' => $sortBy !== 'newest' ? $sortBy : null,
    ]));
    $paginationUrl = url('/venues') . ($filterParams ? '?' . $filterParams : '');
  ?>
  <?= paginate_links($result['page'], $result['pages'], $paginationUrl) ?>
</div>
<?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  console.log('AJAX Filter script loaded');
  
  const filterForm = document.getElementById('filterForm');
  const filterSelects = document.querySelectorAll('.filter-select');
  const searchInput = document.getElementById('searchInput');
  const applyButton = document.getElementById('applyFilters');
  const filterLoading = document.getElementById('filterLoading');
  const venuesContainer = document.getElementById('venuesContainer');
  const resultCount = document.getElementById('resultCount');
  let searchTimeout;

  console.log('Elements found:', {
    filterForm: !!filterForm,
    filterSelects: filterSelects.length,
    searchInput: !!searchInput,
    applyButton: !!applyButton
  });

  // Clear filters button
  const clearButton = document.getElementById('clearFilters');
  if (clearButton) {
    clearButton.addEventListener('click', function(e) {
      e.preventDefault();
      console.log('Clear filters clicked');
      
      // Reset all filters
      searchInput.value = '';
      filterSelects.forEach(select => {
        if (select.name === 'status') {
          select.value = 'all';
        } else if (select.name === 'sort') {
          select.value = 'newest';
        } else {
          select.value = '';
        }
      });
      
      // Fetch with cleared filters
      fetchVenues();
    });
  }

  // Handle individual filter tag removal
  document.addEventListener('click', function(e) {
    const removeLink = e.target.closest('.active-filters__remove');
    if (removeLink) {
      e.preventDefault();
      const filterType = removeLink.getAttribute('data-clear');
      console.log('Removing filter:', filterType);
      
      // Clear the specific filter
      if (filterType === 'search') {
        searchInput.value = '';
      } else {
        filterSelects.forEach(select => {
          if (select.name === filterType) {
            if (filterType === 'status') {
              select.value = 'all';
            } else if (filterType === 'sort') {
              select.value = 'newest';
            } else {
              select.value = '';
            }
          }
        });
      }
      
      fetchVenues();
    }
  });

  // Auto-submit on select change
  filterSelects.forEach(select => {
    select.addEventListener('change', function(e) {
      console.log('Filter select changed:', select.name, select.value);
      fetchVenues();
    });
  });

  // Search with debounce
  searchInput.addEventListener('input', function() {
    console.log('Search input changed');
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
      fetchVenues();
    }, 500);
  });

  // Manual apply button
  applyButton.addEventListener('click', function(e) {
    e.preventDefault();
    console.log('Apply button clicked');
    fetchVenues();
  });

  // Search on Enter key
  searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      console.log('Enter key pressed');
      fetchVenues();
    }
  });

  function fetchVenues() {
    console.log('=== fetchVenues called ===');
    
    // Show loading
    filterLoading.style.display = 'inline-block';
    applyButton.disabled = true;
    venuesContainer.style.opacity = '0.5';

    // Build URL parameters
    const params = new URLSearchParams();
    
    // Get search value
    const searchValue = searchInput.value.trim();
    if (searchValue) {
      params.append('search', searchValue);
    }
    
    // Get filter values
    filterSelects.forEach(select => {
      const value = select.value;
      const name = select.name;
      if (value && value !== 'all' && value !== '' && !(name === 'sort' && value === 'newest')) {
        params.append(name, value);
      }
    });

    // Add AJAX flag
    params.append('ajax', '1');

    const url = '<?= url('/venues') ?>?' + params.toString();
    console.log('Fetching URL:', url);

    // Fetch venues via AJAX
    fetch(url, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'text/html'
      },
      credentials: 'same-origin'
    })
    .then(response => {
      console.log('Response received:', response.status, response.ok);
      console.log('Response headers:', response.headers.get('content-type'));
      if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.status);
      }
      return response.text();
    })
    .then(html => {
      console.log('HTML received, length:', html.length);
      console.log('First 500 chars:', html.substring(0, 500));
      
      // Parse the response
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      
      // Update venues container
      const newContainer = doc.getElementById('venuesContainer');
      console.log('New container found:', !!newContainer);
      
      if (newContainer) {
        // Use innerHTML to replace just the content, not the container itself
        venuesContainer.innerHTML = newContainer.innerHTML;
        venuesContainer.style.opacity = '1';
        console.log('Container updated');
      } else {
        console.error('venuesContainer not found in response');
        console.error('Response body:', html);
      }
      
      // Update result count
      const newResultCount = doc.getElementById('resultCount');
      if (newResultCount && resultCount) {
        resultCount.textContent = newResultCount.textContent;
        console.log('Result count updated');
      }
      
      // Update URL without reload (remove ajax param)
      const displayParams = new URLSearchParams(params);
      displayParams.delete('ajax');
      const newUrl = displayParams.toString() ? '?' + displayParams.toString() : window.location.pathname;
      window.history.pushState({}, '', newUrl);
      console.log('URL updated:', newUrl);
    })
    .catch(error => {
      console.error('Fetch error:', error);
      alert('Error loading venues. Please try again.');
      venuesContainer.style.opacity = '1';
    })
    .finally(() => {
      filterLoading.style.display = 'none';
      applyButton.disabled = false;
      console.log('=== fetchVenues completed ===');
    });
  }
});
</script>


<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="rejectForm" method="POST">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h6 class="modal-title">Reject Venue</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Rejection Reason</label>
          <textarea name="notes" class="form-control" rows="4" placeholder="Explain why the venue is being rejected..." required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Reject Venue</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function rejectVenue(id) {
  document.getElementById('rejectForm').action = '<?= url('/venues/') ?>' + id + '/reject';
  new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>

