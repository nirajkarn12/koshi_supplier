<?php require_once('header.php'); ?>

<style>
    .dashboard-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .dashboard-header h2 {
        margin: 0;
        font-size: 28px;
        font-weight: 300;
    }
    
    .dashboard-header .date {
        font-size: 14px;
        opacity: 0.9;
        margin-top: 5px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #667eea;
        transition: transform 0.2s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    }
    
    .stat-card.referral { border-left-color: #ff6b6b; }
    .stat-card.projects { border-left-color: #4ecdc4; }
    .stat-card.total-projects { border-left-color: #45b7d1; }
    
    .stat-number {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 5px;
        line-height: 1;
    }
    
    .stat-number .change {
        font-size: 14px;
        margin-left: 5px;
        font-weight: 400;
    }
    
    .stat-number.up .change { color: #28a745; }
    .stat-number.down .change { color: #dc3545; }
    
    .stat-label {
        color: #666;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }
    
    .stat-icon {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 24px;
        opacity: 0.3;
    }
    
    .charts-container {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .chart-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }
    
    .chart-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #333;
    }
    
    .doughnut-chart-container,
    .bar-chart-container,
    .map-chart-container {
        height: 200px;
        position: relative;
    }
    
    .doughnut-legend {
        display: flex;
        justify-content: space-around;
        margin-top: 15px;
        font-size: 12px;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        color: #666;
    }
    
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 5px;
    }
    
    .legend-value {
        font-weight: 600;
        color: #333;
    }
    
    .map-legend {
        position: absolute;
        top: 10px;
        right: 10px;
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        font-size: 12px;
    }
    
    .map-legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }
    
    .map-legend-color {
        width: 12px;
        height: 12px;
        margin-right: 5px;
    }
    
    @media (max-width: 768px) {
        .charts-container {
            grid-template-columns: 1fr;
        }
        
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
    }
</style>

<section class="content-header">
    <h1>Dashboard</h1>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_top_category");
$statement->execute();
$total_top_category = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_mid_category");
$statement->execute();
$total_mid_category = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_end_category");
$statement->execute();
$total_end_category = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_product");
$statement->execute();
$total_product = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_status='1'");
$statement->execute();
$total_customers = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_subscriber WHERE subs_active='1'");
$statement->execute();
$total_subscriber = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_shipping_cost");
$statement->execute();
$available_shipping = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_status=?");
$statement->execute(array('Completed'));
$total_order_completed = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE shipping_status=?");
$statement->execute(array('Completed'));
$total_shipping_completed = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_status=?");
$statement->execute(array('Pending'));
$total_order_pending = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_status=? AND shipping_status=?");
$statement->execute(array('Completed','Pending'));
$total_order_complete_shipping_pending = $statement->rowCount();

// Dynamic data based on database queries
$current_month = date('m');
$current_year = date('Y');
$prev_month = date('m', strtotime('-1 month'));
$prev_year = date('Y', strtotime('-1 month'));

// New Clients
$statement = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_customer WHERE MONTH(cust_datetime)=? AND YEAR(cust_datetime)=?");
$statement->execute(array($current_month, $current_year));
$result = $statement->fetch(PDO::FETCH_ASSOC);
$new_clients = $result['total'];

$statement = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_customer WHERE MONTH(cust_datetime)=? AND YEAR(cust_datetime)=?");
$statement->execute(array($prev_month, $prev_year));
$result = $statement->fetch(PDO::FETCH_ASSOC);
$prev_clients = $result['total'];

$client_change = ($prev_clients > 0) ? (($new_clients - $prev_clients) / $prev_clients) * 100 : 0;
$client_change_text = number_format($client_change, 1) . '%';
$client_change_class = ($client_change >= 0) ? 'up' : 'down';
$client_change_sign = ($client_change >= 0) ? '+' : '';

// Earnings of Month
$statement = $pdo->prepare("SELECT SUM(paid_amount) as total FROM tbl_payment WHERE payment_status='Completed' AND MONTH(payment_date)=? AND YEAR(payment_date)=?");
$statement->execute(array($current_month, $current_year));
$result = $statement->fetch(PDO::FETCH_ASSOC);
$earnings_month = $result['total'] ?? 0;

// New Projects (new orders this month)
$statement = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_payment WHERE MONTH(payment_date)=? AND YEAR(payment_date)=?");
$statement->execute(array($current_month, $current_year));
$result = $statement->fetch(PDO::FETCH_ASSOC);
$new_projects = $result['total'];

$statement = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_payment WHERE MONTH(payment_date)=? AND YEAR(payment_date)=?");
$statement->execute(array($prev_month, $prev_year));
$result = $statement->fetch(PDO::FETCH_ASSOC);
$prev_projects = $result['total'];

$project_change = ($prev_projects > 0) ? (($new_projects - $prev_projects) / $prev_projects) * 100 : 0;
$project_change_text = number_format($project_change, 1) . '%';
$project_change_class = ($project_change >= 0) ? 'up' : 'down';
$project_change_sign = ($project_change >= 0) ? '+' : '';

// Total Projects (total completed orders)
$total_projects = $total_order_completed;

// Sales by payment method for doughnut chart
$statement = $pdo->prepare("SELECT payment_method, SUM(paid_amount) as total FROM tbl_payment WHERE payment_status='Completed' GROUP BY payment_method");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

$method_colors = ['#ff6b6b', '#4ecdc4', '#45b7d1'];
$method_labels = [];
$method_data = [];
$method_colors_assigned = [];
$i = 0;
foreach ($result as $row) {
    $method_labels[] = $row['payment_method'];
    $method_data[] = $row['total'];
    $method_colors_assigned[] = $method_colors[$i % count($method_colors)];
    $i++;
}

// Fallback to mock if no data
if (empty($method_labels)) {
    $method_labels = ['Direct Sales', 'Referral Sales', 'Affiliate Sales'];
    $method_data = [5234, 2208, 2104];
    $method_colors_assigned = ['#ff6b6b', '#4ecdc4', '#45b7d1'];
}

// Monthly sales for bar chart (last 6 months)
$months = [];
$sales_data = [];
for ($i = 5; $i >= 0; $i--) {
    $m = date('m', strtotime('-' . $i . ' month'));
    $y = date('Y', strtotime('-' . $i . ' month'));
    $label = date('M', strtotime('-' . $i . ' month'));

    $statement = $pdo->prepare("SELECT SUM(paid_amount) as total FROM tbl_payment WHERE payment_status='Completed' AND MONTH(payment_date)=? AND YEAR(payment_date)=?");
    $statement->execute(array($m, $y));
    $result = $statement->fetch(PDO::FETCH_ASSOC);
    $sales_data[] = $result['total'] ?? 0;
    $months[] = $label;
}

// Earnings by location
$statement = $pdo->prepare("
    SELECT tbl_country.country_name as country, SUM(tbl_payment.paid_amount) as total 
    FROM tbl_payment 
    JOIN tbl_customer ON tbl_payment.customer_id = tbl_customer.cust_id 
    JOIN tbl_country ON tbl_customer.cust_country = tbl_country.country_id 
    WHERE tbl_payment.payment_status = 'Completed' 
    GROUP BY tbl_customer.cust_country
");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

$location_colors = ['#667eea', '#ff6b6b', '#51cf66', '#f8f9fa'];
$location_labels = [];
$location_data = [];
$location_colors_assigned = [];
$total_earnings = 0;
foreach ($result as $row) {
    $total_earnings += $row['total'];
}

$i = 0;
$other_percentage = 100;
if ($total_earnings > 0) {
    foreach ($result as $row) {
        $percentage = ($row['total'] / $total_earnings) * 100;
        $location_labels[] = $row['country'];
        $location_data[] = $percentage;
        $location_colors_assigned[] = $location_colors[$i % count($location_colors)];
        $other_percentage -= $percentage;
        $i++;
    }
    $location_labels[] = 'Others';
    $location_data[] = max(0, $other_percentage);
    $location_colors_assigned[] = $location_colors[$i % count($location_colors)];
} else {
    // Fallback to mock
    $location_labels = ['India', 'USA', 'China', 'Others'];
    $location_data = [28, 21, 12, 39];
    $location_colors_assigned = ['#667eea', '#ff6b6b', '#51cf66', '#f8f9fa'];
}
?>

<section class="content">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h2>Good Morning, Admin!</h2>
        <div class="date"><?php echo date('F d, Y'); ?></div>
        <!-- Replace the iframe section with this -->
<div class="chart-card">
    <div class="search-console-placeholder">
        <a href="https://search.google.com/search-console" target="_blank" class="btn btn-primary">
            Open Search Console
        </a>
        <a href="https://dashboard.tawk.to" target="_blank" class="btn btn-primary">Open Chat Dashboard</a>
    </div>
</div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">
                <?php echo $new_clients; ?><span class="change <?php echo $client_change_class; ?>"><?php echo $client_change_sign . $client_change_text; ?></span>
            </div>
            <div class="stat-label">New Orders</div>
            <div class="stat-icon">👥</div>
        </div>

        <div class="stat-card">
            <div class="stat-number">Rs.<?php echo number_format($earnings_month); ?></div>
            <div class="stat-label">Earnings of Month</div>
            <div class="stat-icon">💰</div>
        </div>

        <div class="stat-card new-projects">
            <div class="stat-number">
                <?php echo $new_projects; ?><span class="change <?php echo $project_change_class; ?>"><?php echo $project_change_sign . $project_change_text; ?></span>
            </div>
            <div class="stat-label">Total Orders</div>
            <div class="stat-icon">📋</div>
        </div>

        <div class="stat-card total-projects">
            <div class="stat-number"><?php echo $total_projects; ?></div>
            <div class="stat-label">Orders Completed</div>
            <div class="stat-icon">📁</div>
        </div>
    </div>

    <!-- Charts Container -->
    <div class="charts-container">
        <!-- Total Sales Doughnut Chart -->
        <div class="chart-card">
            <div class="chart-title">Total Sales</div>
            <div class="doughnut-chart-container">
                <canvas id="salesDoughnut"></canvas>
            </div>
            <div class="doughnut-legend">
                <?php for ($j = 0; $j < count($method_labels); $j++) { ?>
                <div class="legend-item">
                    <div class="legend-color" style="background: <?php echo $method_colors_assigned[$j]; ?>;"></div>
                    <span><?php echo $method_labels[$j]; ?></span>
                    <span class="legend-value">$<?php echo number_format($method_data[$j]); ?></span>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- Net Income Bar Chart -->
        <div class="chart-card">
            <div class="chart-title">Net Income</div>
            <div class="bar-chart-container">
                <canvas id="netIncomeBar"></canvas>
            </div>
        </div>

        <!-- Earnings by Location Map -->
        <div class="chart-card">
            <div class="chart-title">Earnings by Location</div>
            <div class="map-chart-container">
                <canvas id="locationMap"></canvas>
                <div class="map-legend">
                    <?php for ($j = 0; $j < count($location_labels) - 1; $j++) { ?>
                    <div class="map-legend-item">
                        <div class="map-legend-color" style="background: <?php echo $location_colors_assigned[$j]; ?>;"></div>
                        <span><?php echo $location_labels[$j]; ?> <strong><?php echo number_format($location_data[$j]); ?>%</strong></span>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    




    <!-- Additional Stats Cards -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3><?php echo $total_product; ?></h3>
                    <p>Products</p>
                </div>
                <div class="icon">
                    <i class="ion ion-android-cart"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3><?php echo $total_order_pending; ?></h3>
                    <p>Pending Orders</p>
                </div>
                <div class="icon">
                    <i class="ion ion-ios-paper-outline"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3><?php echo $total_order_completed; ?></h3>
                    <p>Completed Orders</p>
                </div>
                <div class="icon">
                    <i class="ion ion-checkmark-round"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3><?php echo $total_customers; ?></h3>
                    <p>Active Customers</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Doughnut Chart
    const salesCtx = document.getElementById('salesDoughnut').getContext('2d');
    new Chart(salesCtx, {
        type: 'doughnut',
        data: {
            labels: [<?php echo "'" . implode("','", $method_labels) . "'"; ?>],
            datasets: [{
                data: [<?php echo implode(',', $method_data); ?>],
                backgroundColor: [<?php echo "'" . implode("','", $method_colors_assigned) . "'"; ?>],
                borderWidth: 0,
                cutout: '60%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Net Income Bar Chart
    const netIncomeCtx = document.getElementById('netIncomeBar').getContext('2d');
    new Chart(netIncomeCtx, {
        type: 'bar',
        data: {
            labels: [<?php echo "'" . implode("','", $months) . "'"; ?>],
            datasets: [{
                label: 'Sales this Month',
                data: [<?php echo implode(',', $sales_data); ?>],
                backgroundColor: '#667eea',
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    ticks: {
                        stepSize: 2.5
                    }
                }
            }
        }
    });

    // Location Map Chart (using doughnut as placeholder for map)
    const mapCtx = document.getElementById('locationMap').getContext('2d');
    new Chart(mapCtx, {
        type: 'doughnut',
        data: {
            labels: [<?php echo "'" . implode("','", $location_labels) . "'"; ?>],
            datasets: [{
                data: [<?php echo implode(',', $location_data); ?>],
                backgroundColor: [<?php echo "'" . implode("','", $location_colors_assigned) . "'"; ?>],
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>

<?php require_once('footer.php'); ?>