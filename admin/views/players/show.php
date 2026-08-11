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

        <?php if ($canRemind && (int) ($stats['upcoming'] ?? 0) > 0): ?>
        <form action="<?= url('/players/'.$player['id'].'/reminder') ?>" method="POST"
              onsubmit="return confirm('Send WhatsApp reminder for next upcoming booking?')">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-success btn-sm w-100 mb-2">
            <i class="bi bi-whatsapp me-1"></i>Send Next Reminder
          </button>
        </form>
        <?php elseif ((int) ($stats['upcoming'] ?? 0) === 0): ?>
          <p class="text-muted small mb-2">No upcoming bookings to remind.</p>
        <?php else: ?>
          <p class="text-muted small mb-2">No phone number or WhatsApp opt-in disabled.</p>
        <?php endif; ?>

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
