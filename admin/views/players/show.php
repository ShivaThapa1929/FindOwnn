<?php
/**
 * @var array $player
 * @var array $stats
 * @var array $bookings
 * @var array $whatsappHistory
 */
$player          = $player ?? [];
$stats           = $stats ?? [];
$bookings        = $bookings ?? [];
$whatsappHistory = $whatsappHistory ?? [];

$isWalkin = str_contains($player['email'] ?? '', '@offline.findownn');
$phone    = $player['whatsapp_number'] ?: ($player['phone'] ?? '—');
$canRemind = !empty($player['whatsapp_opt_in']) && ($player['whatsapp_number'] || $player['phone']);
?>

<div class="row g-4">

  <!-- Left: Profile -->
  <div class="col-lg-4">
    <div class="panel text-center mb-3">
      <div class="panel-body py-4">
        <div class="avatar-xxl mx-auto mb-3"><?= strtoupper(substr($player['name'], 0, 1)) ?></div>
        <h5 class="fw-800 mb-1"><?= e($player['name']) ?></h5>
        <p class="text-muted small mb-2"><?= e($isWalkin ? 'Walk-in customer' : $player['email']) ?></p>
        <?php if ($isWalkin): ?>
          <span class="badge bg-warning text-dark px-3 py-1 mb-2">Walk-in</span>
        <?php else: ?>
          <span class="badge bg-info px-3 py-1 mb-2">Registered Player</span>
        <?php endif; ?>
        <div class="mt-1"><?= statusBadge($player['status']) ?></div>
      </div>
    </div>

    <!-- Stats -->
    <div class="panel mb-3">
      <div class="panel-body">
        <div class="row g-3 text-center">
          <div class="col-6">
            <div class="fw-800 text-primary" style="font-size:1.4rem;"><?= (int) ($stats['total_bookings'] ?? 0) ?></div>
            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;">Bookings</div>
          </div>
          <div class="col-6">
            <div class="fw-800 text-success" style="font-size:1.4rem;">₹<?= number_format((float) ($stats['total_spent'] ?? 0)) ?></div>
            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;">Total Spent</div>
          </div>
          <div class="col-4">
            <div class="fw-700 text-orange"><?= (int) ($stats['upcoming'] ?? 0) ?></div>
            <div class="text-muted" style="font-size:.65rem;">Upcoming</div>
          </div>
          <div class="col-4">
            <div class="fw-700"><?= (int) ($stats['completed'] ?? 0) ?></div>
            <div class="text-muted" style="font-size:.65rem;">Completed</div>
          </div>
          <div class="col-4">
            <div class="fw-700 text-danger"><?= (int) ($stats['cancelled'] ?? 0) ?></div>
            <div class="text-muted" style="font-size:.65rem;">Cancelled</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact + Actions -->
    <div class="panel">
      <div class="panel-head"><h6 class="panel-title">Contact & Reminders</h6></div>
      <div class="panel-body">
        <div class="info-group mb-2">
          <span class="info-label">Phone</span>
          <span><?= e($player['phone'] ?? '—') ?></span>
        </div>
        <div class="info-group mb-2">
          <span class="info-label">WhatsApp</span>
          <span><?= e($player['whatsapp_number'] ?? '—') ?></span>
        </div>
        <div class="info-group mb-3">
          <span class="info-label">WhatsApp Opt-in</span>
          <?= !empty($player['whatsapp_opt_in'])
            ? '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Yes</span>'
            : '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>No</span>' ?>
        </div>

        <?php $latestB = !empty($bookings[0]) ? $bookings[0] : null; ?>
        <button type="button" 
                class="btn btn-success btn-sm w-100 mb-2 open-wa-modal-btn" 
                data-player-id="<?= $player['id'] ?>"
                data-player-name="<?= e($player['name']) ?>"
                data-player-phone="<?= e($player['whatsapp_number'] ?: ($player['phone'] ?? '')) ?>"
                data-booking-id="<?= e($latestB['booking_reference'] ?? ($latestB['booking_number'] ?? (!empty($latestB['id']) ? '#BK-'.$latestB['id'] : ''))) ?>"
                data-venue-name="<?= e($latestB['venue_name'] ?? '') ?>"
                data-sport-name="<?= e($latestB['sport_name'] ?? '') ?>"
                data-booking-date="<?= !empty($latestB['booking_date']) ? date('M j, Y', strtotime($latestB['booking_date'])) : '' ?>"
                data-booking-time="<?= !empty($latestB['start_time']) ? date('h:i A', strtotime($latestB['start_time'])) . (!empty($latestB['end_time']) ? ' - ' . date('h:i A', strtotime($latestB['end_time'])) : '') : '' ?>"
                data-booking-amount="<?= !empty($latestB['amount']) ? '₹' . number_format((float)$latestB['amount']) : '' ?>"
                data-booking-status="<?= e(ucfirst($latestB['status'] ?? '')) ?>">
          <i class="bi bi-whatsapp me-1"></i> Send Custom WhatsApp Message
        </button>

        <a href="<?= url('/players') ?>" class="btn btn-sm btn-outline-secondary w-100">
          <i class="bi bi-arrow-left me-1"></i>Back to Players
        </a>
      </div>
    </div>
  </div>

  <!-- Right: Details -->
  <div class="col-lg-8">

    <!-- Account Details -->
    <div class="panel mb-4">
      <div class="panel-head"><h6 class="panel-title"><i class="bi bi-person-fill me-2"></i>Player Details</h6></div>
      <div class="panel-body">
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">Registered</span>
              <span><?= date('M j, Y H:i', strtotime($player['created_at'])) ?></span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">Last Login</span>
              <span><?= !empty($player['last_login_at']) ? timeAgo($player['last_login_at']) : '<span class="text-muted">Never</span>' ?></span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">Last Booking</span>
              <span><?= !empty($stats['last_booking_date']) ? date('M j, Y', strtotime($stats['last_booking_date'])) : '—' ?></span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">Last WhatsApp Sent</span>
              <span><?= !empty($player['last_whatsapp_sent']) ? timeAgo($player['last_whatsapp_sent']) : '—' ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Booking History -->
    <div class="panel mb-4">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-calendar-check me-2"></i>Booking History</h6>
      </div>
      <div class="panel-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Reference</th>
                <th>Venue</th>
                <th>Date & Time</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Reminder</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($bookings as $b): ?>
              <?php
                $isUpcoming = in_array($b['status'], ['confirmed', 'pending'], true)
                    && strtotime($b['booking_date']) >= strtotime('today');
              ?>
              <tr>
                <td class="font-monospace small"><?= e($b['booking_reference']) ?></td>
                <td>
                  <div class="fw-500 small"><?= e($b['venue_name']) ?></div>
                  <div class="text-muted" style="font-size:.72rem;"><?= e($b['sport_name'] ?? '') ?></div>
                </td>
                <td class="small">
                  <?= date('M j, Y', strtotime($b['booking_date'])) ?><br>
                  <span class="text-muted"><?= substr($b['start_time'], 0, 5) ?> – <?= substr($b['end_time'], 0, 5) ?></span>
                </td>
                <td class="small">₹<?= number_format((float) $b['amount']) ?></td>
                <td><?= statusBadge($b['status']) ?></td>
                <td class="small text-muted">
                  <?= !empty($b['reminder_sent_at']) ? date('M j, H:i', strtotime($b['reminder_sent_at'])) : '—' ?>
                </td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="<?= url('/bookings/'.$b['id']) ?>" class="btn btn-xs btn-outline-secondary"><i class="bi bi-eye"></i></a>
                    <?php if ($isUpcoming && $canRemind): ?>
                    <form action="<?= url('/players/'.$player['id'].'/reminder') ?>" method="POST" class="d-inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                      <button class="btn btn-xs btn-outline-success" title="Send reminder"><i class="bi bi-whatsapp"></i></button>
                    </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($bookings)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">No bookings yet</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- WhatsApp History -->
    <?php if (!empty($whatsappHistory)): ?>
    <div class="panel">
      <div class="panel-head"><h6 class="panel-title"><i class="bi bi-whatsapp me-2"></i>Recent WhatsApp Messages</h6></div>
      <div class="panel-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr><th>Date</th><th>Type</th><th>Status</th><th>Message</th></tr></thead>
          <tbody>
            <?php foreach ($whatsappHistory as $msg): ?>
            <tr>
              <td class="text-muted small"><?= date('M j, H:i', strtotime($msg['created_at'])) ?></td>
              <td class="small"><?= e($msg['message_type']) ?></td>
              <td><?= statusBadge($msg['status']) ?></td>
              <td class="small text-muted text-truncate" style="max-width:280px;"><?= e(mb_substr($msg['message_content'] ?? $msg['message'] ?? '', 0, 80)) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- WhatsApp Custom Message Modal -->
<style>
.wa-modal-content {
  background: #0d1322 !important;
  border: 1px solid rgba(37, 211, 102, 0.25) !important;
  border-radius: 18px !important;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.75), 0 0 35px rgba(37, 211, 102, 0.1) !important;
  overflow: hidden;
}
.wa-modal-header {
  background: linear-gradient(135deg, rgba(37, 211, 102, 0.15) 0%, rgba(18, 140, 126, 0.05) 100%) !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
  padding: 1.25rem 1.5rem !important;
}
.wa-icon-badge {
  width: 42px;
  height: 42px;
  background: linear-gradient(135deg, #25D366, #128C7E);
  color: #ffffff;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 15px rgba(37, 211, 102, 0.35);
  font-size: 1.35rem;
}
.wa-info-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 0.85rem 1rem;
}
.wa-info-card label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #94a3b8;
  font-weight: 700;
}
.wa-info-value {
  color: #f8fafc;
  font-weight: 600;
  font-size: 0.95rem;
}
.wa-textarea {
  background: rgba(15, 23, 42, 0.85) !important;
  border: 1px solid rgba(255, 255, 255, 0.12) !important;
  color: #f1f5f9 !important;
  border-radius: 12px !important;
  font-size: 0.9rem;
  line-height: 1.6;
  resize: vertical;
  transition: all 0.25s ease;
}
.wa-textarea:focus {
  border-color: #25D366 !important;
  box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.2) !important;
  background: rgba(15, 23, 42, 0.98) !important;
}
.wa-btn-submit {
  background: linear-gradient(135deg, #25D366 0%, #128C7E 100%) !important;
  border: none !important;
  color: #ffffff !important;
  font-weight: 700 !important;
  border-radius: 10px !important;
  padding: 0.65rem 1.5rem !important;
  box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3) !important;
  transition: all 0.25s ease !important;
}
.wa-btn-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 22px rgba(37, 211, 102, 0.45) !important;
  color: #ffffff !important;
}
.wa-btn-submit:disabled {
  opacity: 0.5 !important;
  cursor: not-allowed !important;
  box-shadow: none !important;
}
.wa-badge-readonly {
  background: rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
  border: 1px solid rgba(255, 255, 255, 0.1);
  font-size: 0.65rem;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
}
</style>

<div class="modal fade" id="waMessageModal" tabindex="-1" aria-labelledby="waMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content wa-modal-content text-white">
      
      <!-- Modal Header -->
      <div class="modal-header wa-modal-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <div class="wa-icon-badge">
            <i class="bi bi-whatsapp"></i>
          </div>
          <div>
            <h5 class="modal-title h6 text-white fw-bold mb-0" id="waMessageModalLabel">Send WhatsApp Message</h5>
            <small class="text-secondary" style="font-size: 0.78rem;">Compose and send personalized WhatsApp updates</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-4">
        <!-- Error Alert Container -->
        <div id="waModalAlert" class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger py-2 px-3 small rounded-3 mb-3 d-none d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-octagon-fill fs-6"></i>
          <span id="waModalAlertText"></span>
        </div>

        <!-- Recipient Information Cards -->
        <div class="row g-3 mb-3">
          <div class="col-6">
            <div class="wa-info-card h-100">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <label>Player Name</label>
                <i class="bi bi-person-fill text-secondary"></i>
              </div>
              <div class="wa-info-value d-flex align-items-center gap-2">
                <span class="avatar-xs bg-success bg-opacity-25 text-success rounded-circle d-inline-flex align-items-center justify-content-center fw-bold" style="width:24px; height:24px; font-size:11px;" id="waAvatarChar">G</span>
                <span id="waPlayerNameText" class="text-truncate">Guest</span>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="wa-info-card h-100">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <label>WhatsApp Number</label>
                <span class="wa-badge-readonly"><i class="bi bi-lock-fill me-1"></i>Read Only</span>
              </div>
              <div class="wa-info-value font-monospace text-success text-opacity-75" id="waPlayerPhoneText">
                Not Available
              </div>
            </div>
          </div>
        </div>

        <!-- Message Editor -->
        <div class="mb-1">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <label for="waMessageText" class="form-label text-secondary small mb-0 fw-bold">Custom Message</label>
            <span class="badge bg-dark bg-opacity-75 text-secondary border border-secondary border-opacity-25 font-monospace px-2 py-1" id="waCharCount">0 characters</span>
          </div>
          <textarea id="waMessageText" class="form-control wa-textarea p-3" rows="7" placeholder="Type your WhatsApp message here..."></textarea>
          <div class="mt-2 text-secondary small" style="font-size: 0.75rem;">
            <i class="bi bi-info-circle text-info me-1"></i> You can edit this message before redirecting to WhatsApp.
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer border-top border-white border-opacity-10 py-3 px-4 d-flex justify-content-between align-items-center">
        <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-3" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn wa-btn-submit d-flex align-items-center gap-2" id="waSendBtn">
          <i class="bi bi-whatsapp fs-5"></i>
          <span>Send on WhatsApp</span>
        </button>
      </div>

    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const modalEl = document.getElementById('waMessageModal');
  if (!modalEl) return;

  const bsModal = new bootstrap.Modal(modalEl);
  const playerNameText = document.getElementById('waPlayerNameText');
  const playerPhoneText = document.getElementById('waPlayerPhoneText');
  const avatarChar = document.getElementById('waAvatarChar');
  const messageTextarea = document.getElementById('waMessageText');
  const alertBox = document.getElementById('waModalAlert');
  const alertText = document.getElementById('waModalAlertText');
  const sendBtn = document.getElementById('waSendBtn');
  const charCountEl = document.getElementById('waCharCount');

  let currentRawNumber = '';

  function showAlert(msg) {
    alertText.textContent = msg;
    alertBox.classList.remove('d-none');
  }

  function hideAlert() {
    alertBox.classList.add('d-none');
    alertText.textContent = '';
  }

  function updateCharCount() {
    const len = messageTextarea.value.length;
    charCountEl.textContent = len + ' character' + (len !== 1 ? 's' : '');
  }

  messageTextarea.addEventListener('input', updateCharCount);

  function normalizePhone(raw) {
    if (!raw) return '';
    let digits = raw.replace(/\D/g, '');
    if (!digits) return '';
    if (digits.length === 10) {
      return '91' + digits;
    }
    if (digits.length === 12 && digits.startsWith('91')) {
      return digits;
    }
    if (digits.length > 10 && digits.startsWith('0')) {
      return digits.substring(1);
    }
    return digits;
  }

  function formatTitleCase(str) {
    if (!str) return 'Guest';
    return str.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
  }

  document.querySelectorAll('.open-wa-modal-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      hideAlert();

      const rawName = this.getAttribute('data-player-name') || 'Guest';
      const formattedName = formatTitleCase(rawName);
      const phone = this.getAttribute('data-player-phone') || '';
      const bookingId = this.getAttribute('data-booking-id') || '';
      const venueName = this.getAttribute('data-venue-name') || '';
      const sportName = this.getAttribute('data-sport-name') || '';
      const bookingDate = this.getAttribute('data-booking-date') || '';
      const bookingTime = this.getAttribute('data-booking-time') || '';
      const bookingAmount = this.getAttribute('data-booking-amount') || '';
      const bookingStatus = this.getAttribute('data-booking-status') || '';

      currentRawNumber = normalizePhone(phone);

      playerNameText.textContent = formattedName;
      avatarChar.textContent = formattedName.charAt(0).toUpperCase();

      if (!phone || phone === '—') {
        playerPhoneText.textContent = 'Not Available';
        playerPhoneText.className = 'wa-info-value font-monospace text-danger-emphasis';
        showAlert('WhatsApp number is not available for this player.');
        sendBtn.disabled = true;
      } else if (currentRawNumber.length < 10) {
        playerPhoneText.textContent = phone;
        playerPhoneText.className = 'wa-info-value font-monospace text-warning-emphasis';
        showAlert('Please check the player\'s WhatsApp number.');
        sendBtn.disabled = true;
      } else {
        playerPhoneText.textContent = '+' + currentRawNumber.replace(/^(\d{2})(\d{5})(\d{5})$/, '$1 $2-$3');
        playerPhoneText.className = 'wa-info-value font-monospace text-success';
        sendBtn.disabled = false;
      }

      let defaultMsg = `Hello ${formattedName} 👋\n\nThank you for booking with FindOwnn.\n\n`;

      if (bookingId || venueName) {
        defaultMsg += `Your Booking Details:\n`;
        if (bookingId) defaultMsg += `• Booking ID: ${bookingId}\n`;
        if (venueName) defaultMsg += `• Venue: ${formatTitleCase(venueName)}\n`;
        if (sportName) defaultMsg += `• Sport: ${formatTitleCase(sportName)}\n`;
        if (bookingDate) defaultMsg += `• Date: ${bookingDate}\n`;
        if (bookingTime) defaultMsg += `• Time: ${bookingTime}\n`;
        if (bookingAmount) defaultMsg += `• Amount: ${bookingAmount}\n`;
        if (bookingStatus) defaultMsg += `• Status: ${bookingStatus}\n`;
        defaultMsg += `\n`;
      } else {
        defaultMsg += `Your account is active. If you need any assistance with booking a venue or court, please feel free to contact us.\n\n`;
      }

      defaultMsg += `If you need any assistance, please feel free to contact us.\n\nThank you,\nFindOwnn Team`;

      messageTextarea.value = defaultMsg;
      updateCharCount();

      bsModal.show();
    });
  });

  sendBtn.addEventListener('click', function() {
    hideAlert();

    const msg = messageTextarea.value.trim();

    if (!currentRawNumber || currentRawNumber.length < 10) {
      showAlert('WhatsApp number is not available or invalid for this player.');
      return;
    }

    if (!msg) {
      showAlert('Please enter a message before continuing.');
      return;
    }

    const waUrl = `https://api.whatsapp.com/send?phone=${currentRawNumber}&text=${encodeURIComponent(msg)}`;

    const newWin = window.open(waUrl, '_blank');
    if (newWin) {
      newWin.focus();
    } else {
      window.location.href = waUrl;
    }

    bsModal.hide();
  });
});
</script>
