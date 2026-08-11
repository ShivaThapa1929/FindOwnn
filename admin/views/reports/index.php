<?php
$months  = array_column($monthlyRev, 'month');
$revenue = array_column($monthlyRev, 'revenue');
$bookCnt = array_column($monthlyRev, 'total_bookings');
$typeLabels = array_column($revenueByType, 'type');
$typeRev    = array_column($revenueByType, 'revenue');
$ugMonths   = array_column($userGrowth, 'month');
$ugTotals   = array_column($userGrowth, 'total');
?>

<!-- KPI Row -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card--green">
      <div class="stat-card__icon"><i class="bi bi-calendar-check-fill"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($bookStats['total'] ?? 0) ?></div>
        <div class="stat-card__label">Total Bookings</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card--blue">
      <div class="stat-card__icon"><i class="bi bi-currency-rupee"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value">₹<?= number_format($bookStats['total_revenue'] ?? 0) ?></div>
        <div class="stat-card__label">Total Revenue</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card--purple">
      <div class="stat-card__icon"><i class="bi bi-credit-card-fill"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value">₹<?= number_format($subStats['total_revenue'] ?? 0) ?></div>
        <div class="stat-card__label">Sub Revenue</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card--teal">
      <div class="stat-card__icon"><i class="bi bi-building"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($venueStats['total'] ?? 0) ?></div>
        <div class="stat-card__label">Total Venues</div>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row 1 -->
<div class="row g-4 mb-4">
  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-head">
        <h6 class="panel-title">Monthly Revenue & Bookings (Last 12 months)</h6>
      </div>
      <div class="panel-body">
        <canvas id="revenueChart" height="90"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="panel">
      <div class="panel-head"><h6 class="panel-title">Revenue by Sport Type</h6></div>
      <div class="panel-body">
        <canvas id="typeChart" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row 2 -->
<div class="row g-4 mb-4">
  <div class="col-lg-6">
    <div class="panel">
      <div class="panel-head"><h6 class="panel-title">User Growth (Last 6 months)</h6></div>
      <div class="panel-body">
        <canvas id="userGrowthChart" height="120"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="panel">
      <div class="panel-head"><h6 class="panel-title">Booking Status Breakdown</h6></div>
      <div class="panel-body d-flex justify-content-center">
        <canvas id="bookingStatusChart" height="200" style="max-width:280px;"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Revenue by Type Table -->
<div class="panel">
  <div class="panel-head"><h6 class="panel-title">Revenue by Venue Type</h6></div>
  <div class="panel-body p-0">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Sport Type</th><th>Bookings</th><th>Revenue</th></tr></thead>
        <tbody>
          <?php foreach ($revenueByType as $row): ?>
          <tr>
            <td><span class="badge bg-dark"><?= ucwords(str_replace('_',' ',$row['type'] ?? 'N/A')) ?></span></td>
            <td><?= number_format($row['bookings']) ?></td>
            <td class="fw-500">₹<?= number_format($row['revenue']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($revenueByType)): ?>
            <tr><td colspan="3" class="text-center py-4 text-muted">No data available</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="text-end mt-3">
  <a href="<?= url('/reports/audit-logs') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-journal-text me-1"></i>View Audit Logs
  </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Check if Chart.js is loaded
  if (typeof Chart === 'undefined') {
    console.error('Chart.js library not loaded');
    return;
  }

  const green = 'rgba(34,197,94,0.8)', greenL = 'rgba(34,197,94,0.15)';
  const blue  = 'rgba(59,130,246,0.8)', blueL  = 'rgba(59,130,246,0.15)';

  // Data preparation
  const months = <?= json_encode($months) ?>;
  const revenue = <?= json_encode(array_map('floatval', $revenue)) ?>;
  const bookCnt = <?= json_encode(array_map('intval', $bookCnt)) ?>;
  const typeLabels = <?= json_encode(array_map(fn($t)=>ucwords(str_replace('_',' ',$t ?? 'Unknown')),$typeLabels)) ?>;
  const typeRev = <?= json_encode(array_map('floatval', $typeRev)) ?>;
  const ugMonths = <?= json_encode($ugMonths) ?>;
  const ugTotals = <?= json_encode(array_map('intval', $ugTotals)) ?>;

  console.log('Report Data:', { months, revenue, bookCnt, typeLabels, typeRev, ugMonths, ugTotals });

  // 1. Revenue + Bookings Chart
  try {
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
      if (months.length === 0 || revenue.every(v => v === 0)) {
        revenueCtx.parentElement.innerHTML = '<div style="text-align:center;padding:3rem;color:#86a892;"><i class="bi bi-graph-down" style="font-size:3rem;opacity:0.3;display:block;margin-bottom:1rem;"></i><p>No revenue data available</p></div>';
      } else {
        new Chart(revenueCtx, {
          data: {
            labels: months,
            datasets: [
              { 
                type:'bar',  
                label:'Revenue (₹)', 
                data: revenue, 
                backgroundColor: green, 
                borderRadius: 5, 
                yAxisID:'y',
                order: 2
              },
              { 
                type:'line', 
                label:'Bookings',    
                data: bookCnt, 
                borderColor: blue, 
                backgroundColor: blueL, 
                tension:.4, 
                pointRadius:4, 
                pointBackgroundColor: blue,
                pointBorderColor: '#0a0f0b',
                pointBorderWidth: 2,
                yAxisID:'y1',
                order: 1
              }
            ]
          },
          options: {
            responsive:true,
            maintainAspectRatio: true,
            interaction:{ mode:'index', intersect:false },
            plugins:{ 
              legend:{ 
                labels:{ 
                  color:'#86a892', 
                  boxWidth:12,
                  padding: 15,
                  font: { size: 12 }
                } 
              },
              tooltip: {
                backgroundColor: 'rgba(10,15,11,0.95)',
                titleColor: '#f0fdf4',
                bodyColor: '#d1e7d9',
                borderColor: 'rgba(34,197,94,0.3)',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 8
              }
            },
            scales:{
              y:  { 
                type: 'linear',
                position: 'left',
                beginAtZero:true, 
                ticks:{ 
                  color:'#86a892', 
                  callback: v => '₹' + v.toLocaleString('en-IN')
                }, 
                grid:{ color:'rgba(134,168,146,0.08)' },
                border: { display: false }
              },
              y1: { 
                type: 'linear',
                position:'right', 
                beginAtZero:true, 
                ticks:{ 
                  color:'#86a892',
                  stepSize: 1
                }, 
                grid:{ drawOnChartArea: false },
                border: { display: false }
              },
              x:  { 
                ticks:{ color:'#86a892' }, 
                grid:{ display:false },
                border: { display: false }
              }
            }
          }
        });
        console.log('✓ Revenue chart initialized');
      }
    }
  } catch (error) {
    console.error('Revenue chart error:', error);
  }

  // 2. Sport type doughnut
  try {
    const typeCtx = document.getElementById('typeChart');
    if (typeCtx) {
      if (typeLabels.length === 0 || typeRev.every(v => v === 0)) {
        typeCtx.parentElement.innerHTML = '<div style="text-align:center;padding:3rem;color:#86a892;"><i class="bi bi-pie-chart" style="font-size:3rem;opacity:0.3;display:block;margin-bottom:1rem;"></i><p>No sport type data</p></div>';
      } else {
        const colors = ['#22c55e','#3b82f6','#f59e0b','#ef4444','#a855f7','#06b6d4','#ec4899','#14b8a6'];
        new Chart(typeCtx, {
          type:'doughnut',
          data:{ 
            labels: typeLabels, 
            datasets:[{ 
              data: typeRev, 
              backgroundColor: colors.slice(0, typeLabels.length), 
              borderColor: '#0a0f0b',
              borderWidth: 2, 
              hoverOffset: 8,
              hoverBorderColor: '#fff',
              hoverBorderWidth: 3
            }] 
          },
          options:{ 
            cutout:'65%', 
            plugins:{ 
              legend:{ 
                position:'bottom', 
                labels:{ 
                  color:'#86a892', 
                  boxWidth:10, 
                  padding:14,
                  font: { size: 11 }
                } 
              },
              tooltip: {
                backgroundColor: 'rgba(10,15,11,0.95)',
                titleColor: '#f0fdf4',
                bodyColor: '#d1e7d9',
                borderColor: 'rgba(34,197,94,0.3)',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 8,
                callbacks: {
                  label: function(context) {
                    return context.label + ': ₹' + context.parsed.toLocaleString('en-IN');
                  }
                }
              }
            } 
          }
        });
        console.log('✓ Sport type chart initialized');
      }
    }
  } catch (error) {
    console.error('Sport type chart error:', error);
  }

  // 3. User growth
  try {
    const userCtx = document.getElementById('userGrowthChart');
    if (userCtx) {
      if (ugMonths.length === 0 || ugTotals.every(v => v === 0)) {
        userCtx.parentElement.innerHTML = '<div style="text-align:center;padding:3rem;color:#86a892;"><i class="bi bi-people" style="font-size:3rem;opacity:0.3;display:block;margin-bottom:1rem;"></i><p>No user growth data</p></div>';
      } else {
        new Chart(userCtx, {
          type:'line',
          data:{ 
            labels: ugMonths, 
            datasets:[{ 
              label:'New Users', 
              data: ugTotals, 
              borderColor:'#22c55e', 
              backgroundColor:'rgba(34,197,94,0.15)', 
              fill:true, 
              tension:.4, 
              pointRadius:5,
              pointBackgroundColor: '#22c55e',
              pointBorderColor: '#0a0f0b',
              pointBorderWidth: 2,
              pointHoverRadius: 7,
              borderWidth: 3
            }] 
          },
          options:{ 
            responsive:true, 
            maintainAspectRatio: true,
            plugins:{ 
              legend:{ display:false },
              tooltip: {
                backgroundColor: 'rgba(10,15,11,0.95)',
                titleColor: '#f0fdf4',
                bodyColor: '#d1e7d9',
                borderColor: 'rgba(34,197,94,0.3)',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 8
              }
            }, 
            scales:{ 
              y:{ 
                beginAtZero:true, 
                ticks:{ 
                  color:'#86a892',
                  stepSize: 1
                }, 
                grid:{ color:'rgba(134,168,146,0.08)' },
                border: { display: false }
              }, 
              x:{ 
                ticks:{ color:'#86a892' }, 
                grid:{ display:false },
                border: { display: false }
              } 
            } 
          }
        });
        console.log('✓ User growth chart initialized');
      }
    }
  } catch (error) {
    console.error('User growth chart error:', error);
  }

  // 4. Booking status
  try {
    const statusCtx = document.getElementById('bookingStatusChart');
    if (statusCtx) {
      const confirmed = <?= (int)($bookStats['confirmed']??0) ?>;
      const cancelled = <?= (int)($bookStats['cancelled']??0) ?>;
      const total = <?= (int)($bookStats['total']??0) ?>;
      const pending = Math.max(0, total - confirmed - cancelled);
      
      if (total === 0) {
        statusCtx.parentElement.innerHTML = '<div style="text-align:center;padding:3rem;color:#86a892;"><i class="bi bi-calendar-x" style="font-size:3rem;opacity:0.3;display:block;margin-bottom:1rem;"></i><p>No booking data</p></div>';
      } else {
        new Chart(statusCtx, {
          type:'pie',
          data:{
            labels:['Confirmed','Pending','Cancelled'],
            datasets:[{ 
              data:[confirmed, pending, cancelled], 
              backgroundColor:['#22c55e','#f59e0b','#ef4444'], 
              borderColor: '#0a0f0b',
              borderWidth: 2,
              hoverOffset: 8,
              hoverBorderColor: '#fff',
              hoverBorderWidth: 3
            }]
          },
          options:{ 
            plugins:{ 
              legend:{ 
                position:'bottom', 
                labels:{ 
                  color:'#86a892', 
                  boxWidth:10, 
                  padding:14,
                  font: { size: 11 }
                } 
              },
              tooltip: {
                backgroundColor: 'rgba(10,15,11,0.95)',
                titleColor: '#f0fdf4',
                bodyColor: '#d1e7d9',
                borderColor: 'rgba(34,197,94,0.3)',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 8,
                callbacks: {
                  label: function(context) {
                    const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                  }
                }
              }
            } 
          }
        });
        console.log('✓ Booking status chart initialized');
      }
    }
  } catch (error) {
    console.error('Booking status chart error:', error);
  }

  console.log('✓ All charts initialized successfully');
});
</script>
