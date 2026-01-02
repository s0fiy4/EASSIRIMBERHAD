<?php
$pdo = new PDO('mysql:host=localhost;dbname=sirim', 'root', '');

$selectedMonth = $_GET['month'] ?? '';
$whereClause = '';
$params = [];

if (!empty($selectedMonth)) {
    $whereClause = "AND DATE_FORMAT(a.payment_date, '%Y-%m') = ?";
    $params[] = $selectedMonth;
}

// Total payments
$totalPaymentsQuery = "
    SELECT SUM(e.payment_amount) AS total 
    FROM attendees a 
    JOIN events e ON a.event_id = e.event_id 
    WHERE e.payment_required = 1 
      AND a.payment_status = 'Paid' 
      AND a.payment_date IS NOT NULL
      " . ($selectedMonth ? " AND DATE_FORMAT(a.payment_date, '%Y-%m') = ?" : "");

$totalPaymentsStmt = $pdo->prepare($totalPaymentsQuery);
$totalPaymentsStmt->execute($selectedMonth ? [$selectedMonth] : []);
$totalPayments = $totalPaymentsStmt->fetchColumn() ?: 0;


// Generate 12 months from Jan to Dec of 2025
$year = 2025;
$allMonths = [];
for ($m = 1; $m <= 12; $m++) {
    $monthKey = sprintf('%04d-%02d', $year, $m);
    $allMonths[$monthKey] = 0;
}

// Fetch actual data
$sql = "
  SELECT DATE_FORMAT(a.payment_date, '%Y-%m') AS month, 
         COUNT(*) AS total_attendees
  FROM attendees a
  JOIN events e ON a.event_id = e.event_id
  WHERE e.payment_required = 1 
    AND a.payment_status = 'Paid' 
    AND a.payment_date IS NOT NULL
  GROUP BY month
  ORDER BY month
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$rawData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // ['2025-06' => 10, ...]

foreach ($rawData as $month => $count) {
    $allMonths[$month] = (int)$count;
}

// Build cumulative attendance
$paymentLabels = [];
$paymentData = [];
$cumulative = 0;
foreach ($allMonths as $month => $count) {
    $cumulative += $count;
    $paymentLabels[] = date('F Y', strtotime($month . '-01'));
    $paymentData[] = $cumulative;
}


// Total events
$totalEventsStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM events 
    WHERE 1
    " . ($selectedMonth ? " AND DATE_FORMAT(event_start_date, '%Y-%m') = ?" : "")
);
$totalEventsStmt->execute($selectedMonth ? [$selectedMonth] : []);
$totalEvents = $totalEventsStmt->fetchColumn() ?: 0;

// Total attendees
$totalAttendeesStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM attendees a
    JOIN events e ON a.event_id = e.event_id
    WHERE 1
    " . ($selectedMonth ? " AND DATE_FORMAT(e.event_start_date, '%Y-%m') = ?" : "")
);
$totalAttendeesStmt->execute($selectedMonth ? [$selectedMonth] : []);
$totalAttendees = $totalAttendeesStmt->fetchColumn() ?: 0;

// Average attendance per event
$avgAttendance = $totalEvents > 0 ? round($totalAttendees / $totalEvents, 1) : 0;

// Event location distribution
$locationStmt = $pdo->prepare("
    SELECT event_location, COUNT(*) as total 
    FROM events 
    WHERE 1
    " . ($selectedMonth ? " AND DATE_FORMAT(event_start_date, '%Y-%m') = ?" : "") . "
    GROUP BY event_location
");
$locationStmt->execute($selectedMonth ? [$selectedMonth] : []);
$locations = [];
$locationCounts = [];
while ($row = $locationStmt->fetch(PDO::FETCH_ASSOC)) {
    $locations[] = $row['event_location'];
    $locationCounts[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MONTHLY DASHBOARD</title>
  <link rel="icon" type="image/png" href="images/LOGOSIRIM.jpg" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-[Poppins] min-h-screen">
  <div class="flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-[#002B5C] text-white min-h-screen p-6">
      <div class="mb-8">
        <img src="images/LOGOSIRIM.jpg" alt="Logo" class="w-10 mb-2" />
        <h1 class="text-lg font-semibold">EVENT ATTENDANCE<br>SIRIM BERHAD</h1>
      </div>
      <nav class="space-y-3">
        <a href="#" class="block px-3 py-2 rounded hover:bg-[#004080]">MAIN</a>
        <a href="homepage.html" class="block px-3 py-2 rounded hover:bg-[#004080]">HOMEPAGE</a>
        <a href="logout.php" class="block px-3 py-2 rounded hover:bg-[#004080]">LOGOUT</a>
      </nav>
    </aside>

    <!-- Main Dashboard -->
    <main class="flex-1 p-10">
      <h2 class="text-2xl font-bold mb-4">
  📅 Dashboard for <?= $selectedMonth ? date('F Y', strtotime($selectedMonth . '-01')) : 'All Months' ?>
  </h2>


      <label for="monthFilter" class="block font-semibold mb-2">📅 Filter by Month:</label>
      <select id="monthFilter" class="p-2 border rounded mb-6">
      <option value="">All</option>
      <option value="2025-01">January 2025</option>
      <option value="2025-02">February 2025</option>
      <option value="2025-03">March 2025</option>
      <option value="2025-04">April 2025</option>
      <option value="2025-05">May 2025</option>
      <option value="2025-06">June 2025</option>
      <option value="2025-07">July 2025</option>
      <option value="2025-08">August 2025</option>
      <option value="2025-09">September 2025</option>
      <option value="2025-10">October 2025</option>
      <option value="2025-11">November 2025</option>
      <option value="2025-12">December 2025</option>
      </select>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
  <div class="bg-white rounded-lg shadow p-5 cursor-pointer hover:bg-gray-100" onclick="openDashboardModal('payments')">
    <p class="text-gray-500">TOTAL PAYMENTS</p>
    <h3 class="text-2xl font-bold text-green-600">RM <?= number_format($totalPayments, 2) ?></h3>
  </div>

  <div class="bg-white rounded-lg shadow p-5 cursor-pointer hover:bg-gray-100" onclick="openDashboardModal('events')">
    <p class="text-gray-500">EVENTS CREATED</p>
    <h3 class="text-2xl font-bold text-blue-600"><?= $totalEvents ?> Events</h3>
  </div>

  <div class="bg-white rounded-lg shadow p-5 cursor-pointer hover:bg-gray-100" onclick="openDashboardModal('attendees')">
    <p class="text-gray-500">TOTAL ATTENDEES</p>
    <h3 class="text-2xl font-bold text-indigo-600"><?= $totalAttendees ?> Attendees</h3>
  </div>

  <div class="bg-white rounded-lg shadow p-5 cursor-pointer hover:bg-gray-100" onclick="openDashboardModal('average')">
    <p class="text-gray-500">AVG. ATTENDANCE/EVENT</p>
    <h3 class="text-2xl font-bold text-yellow-600"><?= $avgAttendance ?></h3>
  </div>
</div>

<div id="dashboardModal" class="fixed inset-0 hidden bg-black bg-opacity-50 z-50 flex items-center justify-center">
  <div class="bg-white p-6 rounded shadow-lg w-full max-w-3xl relative max-h-[80vh] overflow-y-auto">
    <h2 class="text-2xl font-bold mb-4" id="dashboardModalTitle">Details</h2>
    <div id="dashboardModalContent">
      <!-- Dynamic content here -->
    </div>
    <div class="text-right mt-6">
      <button onclick="document.getElementById('dashboardModal').classList.add('hidden')" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">Close</button>
    </div>
  </div>
</div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payments Bar Chart -->
        <div class="bg-white rounded-lg shadow p-6">
          <h4 class="text-lg font-semibold mb-4">CUMULATIVE ATTENDANCE GROWTH</h4>
          <canvas id="paymentsChart" height="200"></canvas>
        </div>

        <!-- Location Pie Chart -->
        <div class="bg-white rounded-lg shadow p-6">
          <h4 class="text-lg font-semibold mb-4">EVENT LOCATIONS DISTRIBUTION</h4>
          <canvas id="locationsChart" height="200"></canvas>
        </div>
      </div>
    </main>
  </div>

  <script>
    // Payments Chart (Static for now, can be dynamic with PHP if monthly payment data exists)
  const paymentLabels = <?= json_encode($paymentLabels) ?>;
  const paymentData = <?= json_encode($paymentData) ?>;

new Chart(document.getElementById('paymentsChart').getContext('2d'), {
  type: 'line',
  data: {
    labels: paymentLabels,
    datasets: [{
      label: 'Cumulative Attendees',
      data: paymentData,
      fill: false,
      borderColor: '#10b981',
      backgroundColor: '#10b981',
      tension: 0.3
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: true } },
    scales: {
      y: {
        beginAtZero: true,
        title: {
          display: true,
          text: 'Total Attendees'
        }
      }
    }
  }
});

    // Locations Pie Chart (Dynamic from PHP)
    const locationLabels = <?= json_encode($locations) ?>;
    const locationCounts = <?= json_encode($locationCounts) ?>;

    new Chart(document.getElementById('locationsChart').getContext('2d'), {
      type: 'pie',
      data: {
        labels: locationLabels,
        datasets: [{
          label: 'Event Count',
          data: locationCounts,
          backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6']
        }]
      },
      options: {
        responsive: true,
      }
    });

    document.getElementById('monthFilter').addEventListener('change', function () {
  const month = this.value;
  const url = new URL(window.location.href);
  if (month) {
    url.searchParams.set('month', month);
  } else {
    url.searchParams.delete('month');
  }
  window.location.href = url.toString(); // Reload with new filter
});

function openDashboardModal(type) {
  const modal = document.getElementById('dashboardModal');
  const title = document.getElementById('dashboardModalTitle');
  const content = document.getElementById('dashboardModalContent');
  const month = new URLSearchParams(window.location.search).get('month') || '';

  title.innerText = 'Loading...';
  content.innerHTML = '<p class="text-gray-500">Please wait...</p>';
  modal.classList.remove('hidden');

  fetch(`get_dashboard_detail.php?type=${type}&month=${month}`)
    .then(res => res.text())
    .then(html => {
      title.innerText = type.toUpperCase().replace('_', ' ');
      content.innerHTML = html;
    })
    .catch(err => {
      content.innerHTML = '<p class="text-red-600">Failed to load data.</p>';
    });
}

function openDashboardModal(type) {
  const modal = document.getElementById('dashboardModal');
  const title = document.getElementById('dashboardModalTitle');
  const content = document.getElementById('dashboardModalContent');
  const month = new URLSearchParams(window.location.search).get('month') || '';

  title.innerText = 'Loading...';
  content.innerHTML = '<p class="text-gray-500">Please wait...</p>';
  modal.classList.remove('hidden');

  fetch(`get_dashboard_detail.php?type=${type}&month=${month}`)
    .then(res => res.text())
    .then(html => {
      title.innerText = type.toUpperCase().replace('_', ' ');
      content.innerHTML = html;
    })
    .catch(err => {
      content.innerHTML = '<p class="text-red-600">Failed to load data.</p>';
    });
}

// Chart click for location breakdown
const locationCtx = document.getElementById('locationsChart').getContext('2d');
const locationsChart = new Chart(locationCtx, {
  type: 'pie',
  data: {
    labels: locationLabels,
    datasets: [{
      label: 'Event Count',
      data: locationCounts,
      backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6']
    }]
  },
  options: {
    responsive: true,
    onClick: function (e, elements) {
      if (elements.length > 0) {
        const index = elements[0].index;
        const location = locationLabels[index];
        const month = new URLSearchParams(window.location.search).get('month') || '';

        const title = `Events in ${location}`;
        const modal = document.getElementById('dashboardModal');
        const titleEl = document.getElementById('dashboardModalTitle');
        const content = document.getElementById('dashboardModalContent');

        titleEl.innerText = title;
        content.innerHTML = '<p class="text-gray-500">Loading...</p>';
        modal.classList.remove('hidden');

        fetch(`get_dashboard_detail.php?type=location&location=${encodeURIComponent(location)}&month=${month}`)
          .then(res => res.text())
          .then(html => {
            content.innerHTML = html;
          })
          .catch(() => {
            content.innerHTML = '<p class="text-red-600">Failed to load data.</p>';
          });
      }
    }
  }
});

  </script>
</body>
</html>
