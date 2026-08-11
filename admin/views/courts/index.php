<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h4 class="mb-1"><?= e($venue['name']) ?> - Courts</h4>
      <p class="text-muted mb-0">
        <a href="<?= url('/venues/' . $venue['id']) ?>" class="text-decoration-none">
          <i class="bi bi-arrow-left me-1"></i>Back to Venue
        </a>
      </p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourtModal">
      <i class="bi bi-plus-lg me-1"></i>Add Court
    </button>
  </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show">
  <?= e($success) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
  <?= e($error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Courts Grid -->
<?php if (empty($courts)): ?>
<div class="panel">
  <div class="panel-body text-center py-5">
    <i class="bi bi-grid-3x3-gap" style="font-size: 3rem; opacity: 0.3;"></i>
    <p class="text-muted mt-3 mb-4">No courts added yet. Add your first court to start accepting bookings.</p>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourtModal">
      <i class="bi bi-plus-lg me-1"></i>Add First Court
    </button>
  </div>
</div>
<?php else: ?>
<div class="row g-3">
  <?php foreach ($courts as $court): ?>
  <div class="col-md-6 col-lg-4">
    <div class="panel h-100">
      <div class="panel-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h6 class="mb-1 fw-600"><?= e($court['name']) ?></h6>
            <span class="badge bg-primary"><?= e($court['sport_name']) ?></span>
            <?php if ($court['court_number']): ?>
            <span class="badge bg-secondary"><?= e($court['court_number']) ?></span>
            <?php endif; ?>
          </div>
          <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
              <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a href="<?= url('/courts/' . $court['id'] . '/edit') ?>" class="dropdown-item">
                  <i class="bi bi-pencil me-2"></i>Edit
                </a>
              </li>
              <li>
                <a href="<?= url('/courts/' . $court['id'] . '/images') ?>" class="dropdown-item">
                  <i class="bi bi-images me-2"></i>Manage Images (<?= $court['image_count'] ?>)
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form action="<?= url('/courts/' . $court['id'] . '/delete') ?>" method="POST" 
                      onsubmit="return confirm('Delete this court?')">
                  <?= csrf_field() ?>
                  <button type="submit" class="dropdown-item text-danger">
                    <i class="bi bi-trash me-2"></i>Delete
                  </button>
                </form>
              </li>
            </ul>
          </div>
        </div>

        <?php if ($court['description']): ?>
        <p class="text-muted small mb-3"><?= e($court['description']) ?></p>
        <?php endif; ?>

        <div class="row g-2 mb-3">
          <?php if ($court['surface_type']): ?>
          <div class="col-6">
            <small class="text-muted d-block">Surface</small>
            <small class="fw-500"><?= e($court['surface_type']) ?></small>
          </div>
          <?php endif; ?>
          <?php if ($court['capacity']): ?>
          <div class="col-6">
            <small class="text-muted d-block">Capacity</small>
            <small class="fw-500"><?= $court['capacity'] ?> players</small>
          </div>
          <?php endif; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
          <div>
            <small class="text-muted d-block">Price/Hour</small>
            <span class="fw-600 text-success" style="font-size: 1.1rem;">₹<?= number_format($court['price_per_hour']) ?></span>
          </div>
          <div>
            <?php
            $statusColors = [
              'active' => 'success',
              'inactive' => 'secondary',
              'maintenance' => 'warning'
            ];
            $color = $statusColors[$court['status']] ?? 'secondary';
            ?>
            <span class="badge bg-<?= $color ?>"><?= ucfirst($court['status']) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>


<!-- Add Court Modal -->
<div class="modal fade" id="addCourtModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?= url('/courts/create') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="venue_id" value="<?= $venue['id'] ?>">
        
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Court</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Court Name *</label>
              <input type="text" name="name" class="form-control" placeholder="e.g., Court A, Main Court" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Court Number</label>
              <input type="text" name="court_number" class="form-control" placeholder="e.g., C1, A1">
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Sport *</label>
              <select name="sport_id" class="form-select" required>
                <option value="">Select Sport</option>
                <?php foreach ($sports as $sport): ?>
                <option value="<?= $sport['id'] ?>"><?= e($sport['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Price per Hour (₹) *</label>
              <input type="number" name="price_per_hour" class="form-control" min="1" step="50" required>
            </div>
            
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="2" placeholder="Brief description of the court"></textarea>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Surface Type</label>
              <input type="text" name="surface_type" class="form-control" placeholder="e.g., Artificial Turf, Concrete">
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Dimensions</label>
              <input type="text" name="dimensions" class="form-control" placeholder="e.g., 30x20 feet">
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Capacity (Max Players)</label>
              <input type="number" name="capacity" class="form-control" min="1" placeholder="e.g., 12">
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Features</label>
              <div class="form-check">
                <input type="checkbox" name="is_indoor" value="1" class="form-check-input" id="is_indoor">
                <label class="form-check-label" for="is_indoor">Indoor Court</label>
              </div>
              <div class="form-check">
                <input type="checkbox" name="has_lighting" value="1" class="form-check-input" id="has_lighting" checked>
                <label class="form-check-label" for="has_lighting">Has Lighting</label>
              </div>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Amenities</label>
              <input type="text" name="amenities" class="form-control" placeholder="Seating, Water, Changing Room">
              <small class="text-muted">Separate with commas</small>
            </div>
            
            <div class="col-md-6">
              <label class="form-label">Equipment Provided</label>
              <input type="text" name="equipment" class="form-control" placeholder="Balls, Nets, Scoreboard">
              <small class="text-muted">Separate with commas</small>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Create Court
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
