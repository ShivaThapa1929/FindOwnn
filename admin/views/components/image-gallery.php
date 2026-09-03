<?php
/**
 * Reusable Image Gallery Component
 * 
 * Usage:
 * include(__DIR__ . '/../components/image-gallery.php');
 * renderImageGallery('venue', $venueId, $images);
 * 
 * @param string $type 'venue' or 'court'
 * @param int $entityId ID of venue or court
 * @param array $images Array of image records
 */

function renderImageGallery(string $type, int $entityId, array $images = []): void
{
    $uploadUrl = $type === 'venue' ? url('/images/venues/upload') : url('/images/courts/upload');
    $deleteUrlBase = $type === 'venue' ? url('/images/venues/') : url('/images/courts/');
    $csrfToken = csrf_token();
?>

<div class="image-gallery-container" data-type="<?= $type ?>" data-entity-id="<?= $entityId ?>">
  
  <!-- Upload Section -->
  <div class="panel mb-3">
    <div class="panel-head">
      <h6 class="panel-title">
        <i class="bi bi-images me-2"></i>
        <?= ucfirst($type) ?> Images
      </h6>
      <button class="btn btn-sm btn-success" onclick="openImageUploadModal_<?= $type ?>_<?= $entityId ?>()">
        <i class="bi bi-plus-lg me-1"></i>Upload Images
      </button>
    </div>
    <div class="panel-body">
      
      <?php if (empty($images)): ?>
        <div class="text-center py-5 text-muted">
          <i class="bi bi-image d-block mb-3" style="font-size:3rem;opacity:0.3;"></i>
          <p class="mb-2">No images uploaded yet</p>
          <button class="btn btn-sm btn-primary" onclick="openImageUploadModal_<?= $type ?>_<?= $entityId ?>()">
            <i class="bi bi-upload me-1"></i>Upload Your First Image
          </button>
        </div>
      <?php else: ?>
        
        <!-- Image Grid -->
        <div class="row g-3" id="imageGrid_<?= $type ?>_<?= $entityId ?>">
          <?php foreach ($images as $img): ?>
            <div class="col-6 col-md-4 col-lg-3" data-image-id="<?= $img['id'] ?>">
              <div class="image-card">
                <div class="image-card-img">
                  <img src="<?= url('/public/uploads/' . $img['image_path']) ?>" alt="<?= e($img['caption'] ?? '') ?>">
                  <?php if ($img['image_type'] === 'featured'): ?>
                    <span class="image-badge badge-featured">
                      <i class="bi bi-star-fill"></i> Featured
                    </span>
                  <?php endif; ?>
                </div>
                <div class="image-card-body">
                  <div class="image-card-caption">
                    <?= e($img['caption'] ?: 'No caption') ?>
                  </div>
                  <div class="image-card-actions">
                    <button class="btn btn-xs btn-outline-secondary" 
                            onclick="editImage_<?= $type ?>(<?= $img['id'] ?>, '<?= e($img['caption']) ?>', '<?= $img['image_type'] ?>')" 
                            title="Edit">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <?php if ($img['image_type'] !== 'featured'): ?>
                    <button class="btn btn-xs btn-outline-warning" 
                            onclick="setFeatured_<?= $type ?>(<?= $img['id'] ?>)" 
                            title="Set as Featured">
                      <i class="bi bi-star"></i>
                    </button>
                    <?php endif; ?>
                    <button class="btn btn-xs btn-outline-danger" 
                            onclick="deleteImage_<?= $type ?>(<?= $img['id'] ?>)" 
                            title="Delete">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

      <?php endif; ?>

    </div>
  </div>

</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal_<?= $type ?>_<?= $entityId ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload <?= ucfirst($type) ?> Images</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="uploadForm_<?= $type ?>_<?= $entityId ?>" enctype="multipart/form-data">
          <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
          <input type="hidden" name="<?= $type ?>_id" value="<?= $entityId ?>">
          
          <div class="mb-3">
            <label class="form-label">Select Images</label>
            <input type="file" class="form-control" name="images[]" accept="image/*" multiple required>
            <div class="form-text">Select one or more images. Max 5MB per image. JPG, PNG, WEBP supported.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Image Type</label>
            <select class="form-select" name="image_type">
              <option value="gallery">Gallery Images</option>
              <option value="featured">Featured Image</option>
            </select>
            <div class="form-text">All selected images will have the same type.</div>
          </div>

          <div id="uploadProgress_<?= $type ?>_<?= $entityId ?>" style="display:none;">
            <div class="progress mb-3" style="height: 8px;">
              <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                   role="progressbar" 
                   style="width: 0%" 
                   id="uploadProgressBar_<?= $type ?>_<?= $entityId ?>"></div>
            </div>
            <small class="text-muted">Uploading...</small>
          </div>

          <div id="uploadError_<?= $type ?>_<?= $entityId ?>" class="alert alert-danger mb-0" style="display:none;"></div>
          <div id="uploadSuccess_<?= $type ?>_<?= $entityId ?>" class="alert alert-success mb-0" style="display:none;"></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="submitUpload_<?= $type ?>_<?= $entityId ?>()">
          <i class="bi bi-upload me-1"></i>Upload
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal_<?= $type ?>_<?= $entityId ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Image</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editForm_<?= $type ?>_<?= $entityId ?>">
          <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
          <input type="hidden" id="editImageId_<?= $type ?>_<?= $entityId ?>" name="image_id">
          
          <div class="mb-3">
            <label class="form-label">Caption</label>
            <input type="text" class="form-control" id="editCaption_<?= $type ?>_<?= $entityId ?>" name="caption">
          </div>

          <div class="mb-3">
            <label class="form-label">Image Type</label>
            <select class="form-select" id="editType_<?= $type ?>_<?= $entityId ?>" name="image_type">
              <option value="gallery">Gallery Image</option>
              <option value="featured">Featured Image</option>
            </select>
          </div>

          <div id="editError_<?= $type ?>_<?= $entityId ?>" class="alert alert-danger" style="display:none;"></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="submitEdit_<?= $type ?>_<?= $entityId ?>()">
          <i class="bi bi-check-lg me-1"></i>Save Changes
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Upload Modal
function openImageUploadModal_<?= $type ?>_<?= $entityId ?>() {
  const modal = new bootstrap.Modal(document.getElementById('uploadModal_<?= $type ?>_<?= $entityId ?>'));
  modal.show();
}

// Submit Upload
function submitUpload_<?= $type ?>_<?= $entityId ?>() {
  console.log('Upload function called for <?= $type ?> #<?= $entityId ?>');
  
  const form = document.getElementById('uploadForm_<?= $type ?>_<?= $entityId ?>');
  
  if (!form) {
    console.error('Form not found!');
    alert('Error: Upload form not found');
    return;
  }
  
  const fileInput = form.querySelector('input[type="file"]');
  if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
    alert('Please select at least one image file');
    return;
  }
  
  const filesCount = fileInput.files.length;
  console.log('Files selected:', filesCount);
  
  const progress = document.getElementById('uploadProgress_<?= $type ?>_<?= $entityId ?>');
  const progressBar = document.getElementById('uploadProgressBar_<?= $type ?>_<?= $entityId ?>');
  const errorDiv = document.getElementById('uploadError_<?= $type ?>_<?= $entityId ?>');
  const successDiv = document.getElementById('uploadSuccess_<?= $type ?>_<?= $entityId ?>');

  // Reset and show progress
  if (progress) progress.style.display = 'block';
  if (errorDiv) errorDiv.style.display = 'none';
  if (successDiv) successDiv.style.display = 'none';
  if (progressBar) progressBar.style.width = '0%';
  
  // Get fresh CSRF token
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '<?= $csrfToken ?>';
  
  const imageType = form.querySelector('select[name="image_type"]').value;
  
  let uploadedCount = 0;
  let failedCount = 0;
  const errors = [];
  
  // Upload each file separately
  Array.from(fileInput.files).forEach((file, index) => {
    const formData = new FormData();
    formData.append('_csrf', csrfToken);
    formData.append('<?= $type ?>_id', '<?= $entityId ?>');
    formData.append('image', file);
    formData.append('image_type', imageType);
    formData.append('caption', '');
    
    fetch('<?= $uploadUrl ?>', {
      method: 'POST',
      headers: {
        'X-CSRF-Token': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: formData
    })
    .then(res => res.text())
    .then(text => {
      let data;
      try {
        data = JSON.parse(text);
      } catch (e) {
        throw new Error('Upload failed: ' + (text.substring(0, 100) || 'Invalid server response'));
      }
      
      if (data.success) {
        uploadedCount++;
      } else {
        failedCount++;
        errors.push(file.name + ': ' + (data.message || 'Upload rejected'));
      }
    })
    .catch(err => {
      failedCount++;
      errors.push(file.name + ': ' + err.message);
    })
    .finally(() => {
      // Update progress
      const completed = uploadedCount + failedCount;
      const percent = (completed / filesCount) * 100;
      if (progressBar) progressBar.style.width = percent + '%';
      
      // All uploads finished
      if (completed === filesCount) {
        if (progress) progress.style.display = 'none';
        
        if (uploadedCount > 0 && failedCount === 0) {
          if (successDiv) {
            successDiv.textContent = `Successfully uploaded ${uploadedCount} image(s)!`;
            successDiv.style.display = 'block';
          }
          setTimeout(() => location.reload(), 1500);
        } else if (uploadedCount > 0 && failedCount > 0) {
          if (successDiv) {
            successDiv.textContent = `Uploaded ${uploadedCount} image(s), ${failedCount} failed`;
            successDiv.style.display = 'block';
          }
          if (errorDiv) {
            errorDiv.innerHTML = '<strong>Some uploads failed:</strong><br>' + errors.join('<br>');
            errorDiv.style.display = 'block';
          }
          setTimeout(() => location.reload(), 3000);
        } else {
          if (errorDiv) {
            errorDiv.innerHTML = '<strong>All uploads failed:</strong><br>' + errors.join('<br>');
            errorDiv.style.display = 'block';
          }
        }
      }
    });
  });
}

// Edit Image
function editImage_<?= $type ?>(imageId, caption, imageType) {
  const imageIdInput = document.getElementById('editImageId_<?= $type ?>_<?= $entityId ?>');
  const captionInput = document.getElementById('editCaption_<?= $type ?>_<?= $entityId ?>');
  const typeInput = document.getElementById('editType_<?= $type ?>_<?= $entityId ?>');
  
  if (imageIdInput) imageIdInput.value = imageId;
  if (captionInput) captionInput.value = caption;
  if (typeInput) typeInput.value = imageType;
  
  const modalElement = document.getElementById('editModal_<?= $type ?>_<?= $entityId ?>');
  if (modalElement) {
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
  }
}

// Submit Edit
function submitEdit_<?= $type ?>_<?= $entityId ?>() {
  const imageIdInput = document.getElementById('editImageId_<?= $type ?>_<?= $entityId ?>');
  const captionInput = document.getElementById('editCaption_<?= $type ?>_<?= $entityId ?>');
  const typeInput = document.getElementById('editType_<?= $type ?>_<?= $entityId ?>');
  const errorDiv = document.getElementById('editError_<?= $type ?>_<?= $entityId ?>');
  
  if (!imageIdInput || !captionInput || !typeInput) {
    console.error('Edit form elements not found');
    return;
  }

  const imageId = imageIdInput.value;
  const caption = captionInput.value;
  const imageType = typeInput.value;

  const formData = new FormData();
  formData.append('_csrf', '<?= $csrfToken ?>');
  formData.append('caption', caption);
  formData.append('image_type', imageType);

  fetch('<?= $deleteUrlBase ?>' + imageId + '/update', {
    method: 'POST',
    body: formData
  })
  .then(res => {
    console.log('Update response status:', res.status);
    return res.text();
  })
  .then(text => {
    console.log('Update response text:', text);
    try {
      const data = JSON.parse(text);
      if (data.success) {
        location.reload();
      } else {
        if (errorDiv) {
          errorDiv.textContent = data.message;
          errorDiv.style.display = 'block';
        }
      }
    } catch (e) {
      console.error('JSON parse error:', e);
      console.error('Response was:', text.substring(0, 500));
      if (errorDiv) {
        errorDiv.textContent = 'Update failed: Invalid response - ' + text.substring(0, 100);
        errorDiv.style.display = 'block';
      }
    }
  })
  .catch(err => {
    console.error('Fetch error:', err);
    if (errorDiv) {
      errorDiv.textContent = 'Update failed: ' + err.message;
      errorDiv.style.display = 'block';
    }
  });
}

// Set Featured
function setFeatured_<?= $type ?>(imageId) {
  const formData = new FormData();
  formData.append('_csrf', '<?= $csrfToken ?>');
  formData.append('image_type', 'featured');

  fetch('<?= $deleteUrlBase ?>' + imageId + '/update', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      location.reload();
    } else {
      alert(data.message);
    }
  })
  .catch(err => alert('Failed: ' + err.message));
}

// Delete Image
function deleteImage_<?= $type ?>(imageId) {
  if (!confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
    return;
  }

  const formData = new FormData();
  formData.append('_csrf', '<?= $csrfToken ?>');

  fetch('<?= $deleteUrlBase ?>' + imageId + '/delete', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      location.reload();
    } else {
      alert(data.message);
    }
  })
  .catch(err => alert('Deletion failed: ' + err.message));
}
</script>

<style>
.image-gallery-container {
  margin: 1.5rem 0;
}

.image-card {
  background: rgba(13,22,15,0.5);
  border: 1px solid rgba(134,168,146,0.2);
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.image-card:hover {
  border-color: rgba(56,135,198,0.4);
  box-shadow: 0 4px 16px rgba(56,135,198,0.1);
  transform: translateY(-2px);
}

.image-card-img {
  position: relative;
  width: 100%;
  padding-top: 75%; /* 4:3 aspect ratio */
  overflow: hidden;
  background: rgba(0,0,0,0.3);
}

.image-card-img img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-badge {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  font-size: 0.7rem;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-weight: 600;
}

.badge-featured {
  background: linear-gradient(135deg, #fbbf24, #f59e0b);
  color: #78350f;
}

.image-card-body {
  padding: 0.75rem;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.image-card-caption {
  font-size: 0.8rem;
  color: #86a892;
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.image-card-actions {
  display: flex;
  gap: 0.25rem;
  justify-content: flex-start;
}

.image-card-actions .btn {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
}
</style>

<?php
}
?>
