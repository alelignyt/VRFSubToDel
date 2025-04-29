<?php
include_once('db.php');

// Initialize filter variables
$selected_month = $_POST['month'] ?? '';
$selected_year = $_POST['year'] ?? '';
$selected_hub = $_POST['hub'] ?? '';

// Fetch distinct months for the filter (from sub2delivery)
$months_result = $conn->query("SELECT DISTINCT Month1 FROM sub2delivery ORDER BY Month1");
$months = $months_result->fetch_all(MYSQLI_ASSOC);

// Fetch distinct years for the filter (from sub2delivery)
$years_result = $conn->query("SELECT DISTINCT Year FROM sub2delivery ORDER BY Year DESC");
$years = $years_result->fetch_all(MYSQLI_ASSOC);

// Fetch distinct hubs for the filter (from health_facility_profile)
$hubs_result_profile = $conn->query("SELECT DISTINCT Hub FROM health_facility_profile ORDER BY Hub");
$hubs_profile = $hubs_result_profile->fetch_all(MYSQLI_ASSOC);

// Prepare SQL query to fetch expected VRF count (from health_facility_profile) with hub filter
$expectedVRF_sql = "SELECT COUNT(*) AS total FROM health_facility_profile WHERE 1=1";
if (!empty($selected_hub)) {
    $expectedVRF_sql .= " AND Hub = '$selected_hub'";
}
$expectedVRFResult = $conn->query($expectedVRF_sql);
$expectedVRFRow = $expectedVRFResult->fetch_assoc();
$expectedVRFCount = $expectedVRFRow['total'];

// Prepare SQL query to fetch total VRF count with filters from sub2delivery
$totalVRF_sql = "SELECT COUNT(*) AS total FROM sub2delivery WHERE 1=1";
if (!empty($selected_month)) {
    $totalVRF_sql .= " AND Month1 = '$selected_month'";
}
if (!empty($selected_year)) {
    $totalVRF_sql .= " AND Year = '$selected_year'";
}
if (!empty($selected_hub)) {
    $totalVRF_sql .= " AND Hub = '$selected_hub'";
}
$totalVRFResult = $conn->query($totalVRF_sql);
$totalVRFRow = $totalVRFResult->fetch_assoc();
$totalSubmittedVRFCount = $totalVRFRow['total']; // Renamed for clarity

// Prepare SQL query to fetch data for timely VRF submission count per hub with filters from sub2delivery
$timely_sql = "SELECT Hub, COUNT(*) AS timely_submissions
                 FROM sub2delivery
                 WHERE Is_the_VRF_submission_is_timly = 'The VRF report is Timely Submitted'";

if (!empty($selected_month)) {
    $timely_sql .= " AND Month1 = '$selected_month'";
}

if (!empty($selected_year)) {
    $timely_sql .= " AND Year = '$selected_year'";
}

if (!empty($selected_hub)) {
    $timely_sql .= " AND Hub = '$selected_hub'";
}

$timely_sql .= " GROUP BY Hub
                 ORDER BY Hub";
$timely_result = $conn->query($timely_sql);
$timelyVRFData = $timely_result->fetch_all(MYSQLI_ASSOC);

// Calculate total timely VRF submissions (filtered)
$totalTimelyVRF = 0;
foreach ($timelyVRFData as $row) {
    $totalTimelyVRF += (int)$row['timely_submissions'];
}

// Prepare data for the timely VRF bar chart (filtered)
$timelyVRFChartLabels = [];
$timelyVRFChartDataValues = [];
if (!empty($timelyVRFData)) {
    foreach ($timelyVRFData as $row) {
        $timelyVRFChartLabels[] = $row['Hub'];
        $timelyVRFChartDataValues[] = (int)$row['timely_submissions'];
    }
}

// Prepare SQL query to fetch data for late VRF submission count per hub with filters from sub2delivery
$late_sql = "SELECT Hub, COUNT(*) AS late_submissions
               FROM sub2delivery
               WHERE Is_the_VRF_submission_is_timly = 'The VRF is Submitted Late'";

if (!empty($selected_month)) {
    $late_sql .= " AND Month1 = '$selected_month'";
}

if (!empty($selected_year)) {
    $late_sql .= " AND Year = '$selected_year'";
}

if (!empty($selected_hub)) {
    $late_sql .= " AND Hub = '$selected_hub'";
}

$late_sql .= " GROUP BY Hub
               ORDER BY Hub";
$late_result = $conn->query($late_sql);
$lateVRFData = $late_result->fetch_all(MYSQLI_ASSOC);

// Calculate total late VRF submissions (filtered)
$totalLateVRF = 0;
foreach ($lateVRFData as $row) {
    $totalLateVRF += (int)$row['late_submissions'];
}

// Prepare data for the late VRF bar chart (filtered)
$lateVRFChartLabels = [];
$lateVRFChartDataValues = [];
if (!empty($lateVRFData)) {
    foreach ($lateVRFData as $row) {
        $lateVRFChartLabels[] = $row['Hub'];
        $lateVRFChartDataValues[] = (int)$row['late_submissions'];
    }
}

// Prepare SQL query for timely vaccine delivery count per hub with filters from sub2delivery
$timelyVaccineSql = "SELECT Hub, COUNT(*) AS timely_vaccine_deliveries
                     FROM sub2delivery
                     WHERE Is_the_vaccine_delivery_is_timly = 'The Vaccine is Distributed Timely'";
if (!empty($selected_month)) {
    $timelyVaccineSql .= " AND Month1 = '$selected_month'";
}
if (!empty($selected_year)) {
    $timelyVaccineSql .= " AND Year = '$selected_year'";
}
if (!empty($selected_hub)) {
    $timelyVaccineSql .= " AND Hub = '$selected_hub'";
}
$timelyVaccineSql .= " GROUP BY Hub ORDER BY Hub";
$timelyVaccineResult = $conn->query($timelyVaccineSql);
$timelyVaccineData = $timelyVaccineResult->fetch_all(MYSQLI_ASSOC);

// Calculate total timely vaccine deliveries (filtered)
$totalTimelyVaccine = 0;
foreach ($timelyVaccineData as $row) {
    $totalTimelyVaccine += (int)$row['timely_vaccine_deliveries'];
}
$timelyDistributedVaccinesCount = $totalTimelyVaccine;

$timelyVaccineChartLabels = [];
$timelyVaccineChartDataValues = [];
if (!empty($timelyVaccineData)) {
    foreach ($timelyVaccineData as $row) {
        $timelyVaccineChartLabels[] = $row['Hub'];
        $timelyVaccineChartDataValues[] = (int)$row['timely_vaccine_deliveries'];
    }
}

// Prepare SQL query for late vaccine delivery count per hub with filters from sub2delivery
$lateVaccineSql = "SELECT Hub, COUNT(*) AS late_vaccine_deliveries
                     FROM sub2delivery
                     WHERE Is_the_vaccine_delivery_is_timly = 'The Vaccine is Distributed Late'";
if (!empty($selected_month)) {
    $lateVaccineSql .= " AND Month1 = '$selected_month'";
}
if (!empty($selected_year)) {
    $lateVaccineSql .= " AND Year = '$selected_year'";
}
if (!empty($selected_hub)) {
    $lateVaccineSql .= " AND Hub = '$selected_hub'";
}
$lateVaccineSql .= " GROUP BY Hub ORDER BY Hub";
$lateVaccineResult = $conn->query($lateVaccineSql);
$lateVaccineData = $lateVaccineResult->fetch_all(MYSQLI_ASSOC);

// Calculate total late vaccine deliveries (filtered)
$totalLateVaccine = 0;
foreach ($lateVaccineData as $row) {
    $totalLateVaccine += (int)$row['late_vaccine_deliveries'];
}
$lateDistributedVaccinesCount = $totalLateVaccine;

$lateVaccineChartLabels = [];
$lateVaccineChartDataValues = [];
if (!empty($lateVaccineData)) {
    foreach ($lateVaccineData as $row) {
        $lateVaccineChartLabels[] = $row['Hub'];
        $lateVaccineChartDataValues[] = (int)$row['late_vaccine_deliveries'];
    }
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include "title.html"; ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <style>
        .average-card-row {
            display: flex;
            flex-wrap: nowrap; /* Prevent wrapping to the next line */
            gap: 0.5%; /* Adjust spacing between cards */
            
        }
        .average-card {
            flex: 0 0 auto; /* Don't grow or shrink, take up auto width */
            width: 16%; /* Adjust to fit 6 cards with spacing (approx.) */
            margin-right: 0.5% !important; /* Maintain right margin */
        }
        .average-card:last-child {
            margin-right: 0 !important;
        }
      
    </style>


    <style>
        /* ... (Your existing styles) ... */
        .filter-container {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            gap: 10px; /* Reduced gap */
            flex-wrap: wrap;
        }

        .filter-container label {
            margin: 0;
            font-size: 0.9rem; /* Slightly smaller label */
        }

        .filter-container select,
        .filter-container button,
        .filter-container .btn-view-detail {
            padding: 8px 10px; /* Reduced padding */
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 0.9rem; /* Slightly smaller font */
            height: auto; /* Adjust height */
        }

        .filter-container select {
            width: 120px; /* Default width for most selects */
        }

        #hub {
            width: 100px; /* Minimized width for hub */
            flex-grow: 0; /* Prevent hub from growing excessively */
        }

        .filter-container button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .filter-container button:hover {
            background-color: #0056b3;
        }

        .filter-container .btn-view-detail {
            background-color: #28a745;
            color: white;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .filter-container .btn-view-detail:hover {
            background-color: #1e7e34;
        }

        .filter-row {
            display: flex;
            margin-bottom: 20px;
        }

        .filter-card {
            width: 100%;
        }

        .summary-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .summary-card {
            flex: 1;
            min-width: 200px;
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            position: relative; /* For dropdown positioning */
        }

        .summary-card h3 {
            margin-top: 0;
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 5px; /* Space for dropdown */
        }

        .summary-card p {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 0;
        }

        .chart-container {
            width: 100%; /* Make the container take full width */
            margin-bottom: 20px;
            position: relative; /* For absolute positioning of no-data message */
        }

        .chart-canvas {
            width: 100%;
            height: 300px !important; /* Adjusted height */
        }

        .applied-filters-container {
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #555;
        }

        .filter-badge {
            background-color: #f0f0f0;
            color: #333;
            border-radius: 5px;
            padding: 5px 8px;
            margin-right: 5px;
            display: inline-block;
        }

        .no-data-message {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.2rem;
            color: #999;
            font-style: italic;
        }

        .display-toggle {
            position: absolute;
            top: 0;
            right: 0;
            margin: 5px;
        }

        .display-toggle select {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 0.9rem;
        }

        .average-card {
            width: 100%;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 0.25rem;
            margin-bottom: 20px;
        }

        .average-card .card-header {
            background-color: #007bff;
            color: white;
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid #dee2e6;
            border-top-left-radius: 0.25rem;
            border-top-right-radius: 0.25rem;
            position: relative;
        }

        .average-card .card-title {
            font-size: 1.1rem;
            font-weight: bold;
        }

        .average-card .card-tools {
            position: absolute;
            right: 1.25rem;
            top: 0.75rem;
        }

        .average-card .```dropdown-menu {
            left: auto !important;
            right: 0 !important;
        }

        .average-card .card-body {
            padding: 1.25rem;
        }

        .average-card .card-body h2 {
            font-size: 2rem;
            margin-bottom: 0;
        }
    </style>


</head>
<body>
<div>
    <?php include "navbar/navBar.php"; ?>

    <div style="margin-left: 2%; margin-right: 2%;">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1></h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="filter-row">
                    <div class="card card-info filter-card">
                        <div class="card-header">
                            <h3 class="card-title">Filter</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" id="filterForm">
                                <div class="filter-container">
                                    <label for="month">Month:</label>
                                    <select class="form-control select2" id="month" name="month" onchange="submitForm()">
                                        <option value="">All Months</option>
                                        <?php foreach ($months as $month): ?>
                                            <option value="<?php echo $month['Month1']; ?>" <?php if ($selected_month === $month['Month1']) echo 'selected'; ?>><?php echo $month['Month1']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="year">Year:</label>
                                    <select class="form-control select2" id="year" name="year" onchange="submitForm()">
                                        <option value="">All Years</option>
                                        <?php foreach ($years as $year): ?>
                                            <option value="<?php echo $year['Year']; ?>" <?php if ($selected_year === $year['Year']) echo 'selected'; ?>><?php echo $year['Year']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="hub">Hub:</label>
                                    <select class="form-control select2" style="width: 200px" id="hub" name="hub" onchange="submitForm()">
                                        <option value="">All Hubs</option>
                                        <?php foreach ($hubs_profile as $hub): ?>
                                            <option value="<?php echo $hub['Hub']; ?>" <?php if ($selected_hub === $hub['Hub']) echo 'selected'; ?>><?php echo $hub['Hub']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-default">Reset Filter</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php if (!empty($selected_month) || !empty($selected_year) || !empty($selected_hub)): ?>
                    <div class="applied-filters-container">
                        Applied Filters:
                        <?php if (!empty($selected_month)): ?>
                            <span class="filter-badge">Month: <?php echo $selected_month; ?></span>
                        <?php endif; ?>
                        <?php if (!empty($selected_year)): ?>
                            <span class="filter-badge">Year: <?php echo $selected_year; ?></span>
                        <?php endif; ?>
                        <?php if (!empty($selected_hub)): ?>
                            <span class="filter-badge">Hub: <?php echo $selected_hub; ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="row">
                    <div class="average-card-row" >
                        <div class="card card-info average-card" style="width: 22%">
                            <div class="card-header">
                                <h3 class="card-title" style="font-size: 0.9rem;">Expected VRFs Per Month</h3>
                                <div class="card-tools">
                                </div>
                            </div>
                            <div class="card-body text-center">
                                <h2 id="expectedVRFCount" data-value="<?php echo $expectedVRFCount; ?>" style="font-size: 1.1rem;"><?php echo number_format($expectedVRFCount); ?></h2>
                            </div>
                        </div>
                        <div class="card card-info average-card" style="width: 19.5%">
                            <div class="card-header">
                                 <h3 class="card-title" style="font-size: 0.9rem;">Total Submitted <br> VRFs</h3>
                                    <div class="card-tools">
                                        <div class="chart-options-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-tool dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleCardValuePercentage('totalSubmittedVRFCount', false)"><i class="fas fa-sort-numeric-down-alt mr-2"></i> N</a>
                                                    <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleCardValuePercentage('totalSubmittedVRFCount', true)"><i class="fas fa-percentage mr-2"></i> %</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                            <div class="card-body text-center">
                                <h2 id="totalSubmittedVRFCount" data-value="<?php echo $totalSubmittedVRFCount; ?>" style="font-size: 1.1rem;"><?php echo number_format($totalSubmittedVRFCount); ?></h2>
                            </div>
                        </div>
                        <div class="card card-info average-card" style="width: 19.5%">
                            <div class="card-header">
                                <h3 class="card-title" style="font-size: 0.9rem;">Timely VRF Submissions</h3>
                                 <div class="card-tools">
                                        <div class="chart-options-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-tool dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                     <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleCardValuePercentage('timelyVRFCount', false)"><i class="fas fa-sort-numeric-down-alt mr-2"></i> N</a>
                                                    <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleCardValuePercentage('timelyVRFCount', true)"><i class="fas fa-percentage mr-2"></i> %</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                            <div class="card-body text-center">
                                <h2 id="timelyVRFCount" data-value="<?php echo $totalTimelyVRF; ?>" style="font-size: 1.1rem;"><?php echo number_format($totalTimelyVRF); ?></h2>
                            </div>
                        </div>
                        <div class="card card-warning average-card" style="width:19.5%">
                            <div class="card-header">
                                <h3 class="card-title" style="font-size: 0.9rem;">Late VRF <br>Submissions</h3>
                                 <div class="card-tools">
                                        <div class="chart-options-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-tool dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                     <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleCardValuePercentage('lateVRFCount', false)"><i class="fas fa-sort-numeric-down-alt mr-2"></i> N</a>
                                                    <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleCardValuePercentage('lateVRFCount', true)"><i class="fas fa-percentage mr-2"></i> %</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                            <div class="card-body text-center">
                                <h2 id="lateVRFCount" data-value="<?php echo $totalLateVRF; ?>" style="font-size: 1.1rem;"><?php echo number_format($totalLateVRF); ?></h2>
                            </div>
                        </div>
                        <div class="card card-success average-card" style="width: 19.5%">
                            <div class="card-header">
                                <h3 class="card-title" style="font-size: 0.9rem;">Timely Vaccine <br> Distribution</h3>
                                 <div class="card-tools">
                                        <div class="chart-options-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-tool dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                     <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleCardValuePercentage('timelyVaccineCount', false)"><i class="fas fa-sort-numeric-down-alt mr-2"></i> N</a>
                                                    <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleCardValuePercentage('timelyVaccineCount', true)"><i class="fas fa-percentage mr-2"></i> %</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                            <div class="card-body text-center">
                                <h2 id="timelyVaccineCount" data-value="<?php echo $totalTimelyVaccine; ?>" style="font-size: 1.1rem;"><?php echo number_format($totalTimelyVaccine); ?></h2>
                            </div>
                        </div>
                        <div class="card card-danger average-card" style="width: 19.5%">
                            <div class="card-header">
                                <h3 class="card-title" style="font-size: 0.9rem;">Late Vaccine <br> Distribution</h3>
                                 <div class="card-tools">
                                        <div class="chart-options-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-tool dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                     <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleCardValuePercentage('lateVaccineCount', false)"><i class="fas fa-sort-numeric-down-alt mr-2"></i> N</a>
                                                    <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleCardValuePercentage('lateVaccineCount', true)"><i class="fas fa-percentage mr-2"></i> %</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                            <div class="card-body text-center">
                                <h2 id="lateVaccineCount" data-value="<?php echo $totalLateVaccine; ?>" style="font-size: 1.1rem;"><?php echo number_format($totalLateVaccine); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Timely VRF Submissions per Hub</h3>
                                <div class="card-tools">
                                    <div class="chart-options-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-tool dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleChartValuePercentage('timelyVRFChart', 'value')"><i class="fas fa-sort-numeric-down-alt mr-2"></i> N</a>
                                                <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleChartValuePercentage('timelyVRFChart', 'percentage')"><i class="fas fa-percentage mr-2"></i> %</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($timelyVRFData)): ?>
                                    <canvas id="timelyVRFChart" class="chart-canvas"></canvas>
                                <?php else: ?>
                                    <div class="no-data-message">No data available for the selected filters.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title">Late VRF Submissions per Hub</h3>
                                <div class="card-tools">
                                    <div class="chart-options-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-tool dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleChartValuePercentage('lateVRFChart', 'value')"><i class="fas fa-sort-numeric-down-alt mr-2"></i> N</a>
                                                <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleChartValuePercentage('lateVRFChart', 'percentage')"><i class="fas fa-percentage mr-2"></i> %</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($lateVRFData)): ?>
                                    <canvas id="lateVRFChart" class="chart-canvas"></canvas>
                                <?php else: ?>
                                    <div class="no-data-message">No data available for the selected filters.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Timely Vaccine Distribution per Hub</h3>
                                <div class="card-tools">
                                    <div class="chart-options-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-tool dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleChartValuePercentage('timelyVaccineChart', 'value')"><i class="fas fa-sort-numeric-down-alt mr-2"></i> N</a>
                                                <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleChartValuePercentage('timelyVaccineChart', 'percentage')"><i class="fas fa-percentage mr-2"></i> %</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($timelyVaccineData)): ?>
                                    <canvas id="timelyVaccineChart" class="chart-canvas"></canvas>
                                <?php else: ?>
                                    <div class="no-data-message">No data available for the selected filters.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title">Late Vaccine Distribution per Hub</h3>
                                <div class="card-tools">
                                    <                                    <div class="chart-options-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-tool dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleChartValuePercentage('lateVaccineChart', 'value')"><i class="fas fa-sort-numeric-down-alt mr-2"></i> N</a>
                                                <a class="dropdown-item" style="color:black; cursor: pointer; font-size: 0.8rem;" onclick="toggleChartValuePercentage('lateVaccineChart', 'percentage')"><i class="fas fa-percentage mr-2"></i> %</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($lateVaccineData)): ?>
                                    <canvas id="lateVaccineChart" class="chart-canvas"></canvas>
                                <?php else: ?>
                                    <div class="no-data-message">No data available for the selected filters.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>
<script src="plugins/select2/js/select2.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function () {
        $('.select2').select2();
        createTimelyVRFChart();
        createLateVRFChart();
        createTimelyVaccineChart();
        createLateVaccineChart();
    });

    function submitForm() {
        document.getElementById('filterForm').submit();
    }

    let timelyVRFChartInstance = null;
    let lateVRFChartInstance = null;
    let timelyVaccineChartInstance = null;
    let lateVaccineChartInstance = null;
    let showPercentage = {
        totalSubmittedVRFCount: false,
        timelyVRFCount: false,
        lateVRFCount: false,
        timelyVaccineCount: false,
        lateVaccineCount: false,
        timelyVRFChart: 'value',  // 'value' or 'percentage'
        lateVRFChart: 'value',    // 'value' or 'percentage'
        timelyVaccineChart: 'value',
        lateVaccineChart: 'value'
    };

    const expectedVRFCount = <?php echo $expectedVRFCount; ?>;
    const totalSubmittedVRFCount = <?php echo $totalSubmittedVRFCount; ?>;
    const timelyVRFCount = <?php echo $totalTimelyVRF; ?>;
    const lateVRFCount = <?php echo $totalLateVRF; ?>;
    const timelyVaccineCount = <?php echo $totalTimelyVaccine; ?>;
    const lateVaccineCount = <?php echo $totalLateVaccine; ?>;


    function toggleCardValuePercentage(cardId, displayPercentage) {
        showPercentage[cardId] = displayPercentage;
        updateCardValue(cardId);
    }

    function updateCardValue(cardId) {
        const element = document.getElementById(cardId);
        if (!element) return;

        let value = 0;
        switch (cardId) {
            case 'totalSubmittedVRFCount':
                value = totalSubmittedVRFCount;
                break;
            case 'timelyVRFCount':
                value = timelyVRFCount;
                break;
            case 'lateVRFCount':
                value = lateVRFCount;
                break;
             case 'timelyVaccineCount':
                value = timelyVaccineCount;
                break;
            case 'lateVaccineCount':
                value = lateVaccineCount;
                break;
            default:
                return;
        }

        if (showPercentage[cardId]) {
            let percentage = (expectedVRFCount > 0) ? (value / expectedVRFCount) * 100 : 0;
            element.textContent = percentage.toFixed(2) + '%';
        } else {
            element.textContent = number_format(value);
        }
    }


    function toggleChartValuePercentage(chartId, displayType) {
        showPercentage[chartId] = displayType;
        switch (chartId) {
            case 'timelyVRFChart':
                createTimelyVRFChart();
                break;
            case 'lateVRFChart':
                createLateVRFChart();
                break;
            case 'timelyVaccineChart':
                createTimelyVaccineChart();
                break;
             case 'lateVaccineChart':
                createLateVaccineChart();
                break;
        }
    }

    function createTimelyVRFChart() {
        const ctx = document.getElementById('timelyVRFChart');
        if (!ctx) return;

        const timelyVRFChartLabels = <?php echo json_encode($timelyVRFChartLabels); ?>;
        const timelyVRFChartDataValues = <?php echo json_encode($timelyVRFChartDataValues); ?>;
        const totalExpectedVRF = <?php echo $expectedVRFCount; ?>;


        let displayData = [];
        if (showPercentage.timelyVRFChart === 'percentage') {
            displayData = timelyVRFChartDataValues.map(value => {
                return totalExpectedVRF > 0 ? ((value / totalExpectedVRF) * 100).toFixed(2) + '%' : '0%';
            });
        } else {
            displayData = timelyVRFChartDataValues;
        }

        if (timelyVRFChartInstance) {
            timelyVRFChartInstance.destroy();
        }

        timelyVRFChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: timelyVRFChartLabels,
                datasets: [{
                    label: showPercentage.timelyVRFChart === 'percentage' ? 'Timely VRF (%)' : 'Timely VRF',
                    data: timelyVRFChartDataValues,
                    backgroundColor: '#49a385',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#000',
                        font: {
                            size: 14
                        },
                        formatter: (value, context) => {
                            return displayData[context.dataIndex];
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                         ticks: {
                            callback: function(value) {
                                if(showPercentage.timelyVRFChart === 'value') {
                                   return value;
                                }
                                else{
                                    return value + '%';
                                }

                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    function createLateVRFChart() {
        const ctx = document.getElementById('lateVRFChart');
        if (!ctx) return;

        const lateVRFChartLabels = <?php echo json_encode($lateVRFChartLabels); ?>;
        const lateVRFChartDataValues = <?php echo json_encode($lateVRFChartDataValues); ?>;
        const totalExpectedVRF = <?php echo $expectedVRFCount; ?>;

        let displayData = [];
        if (showPercentage.lateVRFChart === 'percentage') {
             displayData = lateVRFChartDataValues.map(value => {
                return totalExpectedVRF > 0 ? ((value / totalExpectedVRF) * 100).toFixed(2) + '%' : '0%';
            });
        } else {
            displayData = lateVRFChartDataValues;
        }

        if (lateVRFChartInstance) {
            lateVRFChartInstance.destroy();
        }

        lateVRFChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: lateVRFChartLabels,
                datasets: [{
                    label: showPercentage.lateVRFChart === 'percentage' ? 'Late VRF (%)' : 'Late VRF',
                    data: lateVRFChartDataValues,
                    backgroundColor: '#dc3545',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#000',
                        font: {
                            size: 14
                        },
                         formatter: (value, context) => {
                            return displayData[context.dataIndex];
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                 if(showPercentage.lateVRFChart === 'value') {
                                   return value;
                                }
                                else{
                                     return value + '%';
                                }
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    function createTimelyVaccineChart() {
        const ctx = document.getElementById('timelyVaccineChart');
        if (!ctx) return;

        const timelyVaccineChartLabels = <?php echo json_encode($timelyVaccineChartLabels); ?>;
        const timelyVaccineChartDataValues = <?php echo json_encode($timelyVaccineChartDataValues); ?>;
        const totalExpectedVRF = <?php echo $expectedVRFCount; ?>;

        let displayData = [];
        if (showPercentage.timelyVaccineChart === 'percentage') {
            displayData = timelyVaccineChartDataValues.map(value => {
               return totalExpectedVRF > 0 ? ((value / totalExpectedVRF) * 100).toFixed(2) + '%' : '0%';
            });
        } else {
             displayData = timelyVaccineChartDataValues;
        }


        if (timelyVaccineChartInstance) {
            timelyVaccineChartInstance.destroy();
        }

        timelyVaccineChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: timelyVaccineChartLabels,
                datasets: [{
                    label: showPercentage.timelyVaccineChart === 'percentage' ? 'Timely Vaccine (%)' : 'Timely Vaccine',
                    data: timelyVaccineChartDataValues,
                    backgroundColor: '#28a745',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#000',
                        font: {
                            size: 14
                        },
                         formatter: (value, context) => {
                            return displayData[context.dataIndex];
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if(showPercentage.timelyVaccineChart === 'value'){
                                     return value;
                                }
                                else{
                                     return value + '%';
                                }
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    function createLateVaccineChart() {
        const ctx = document.getElementById('lateVaccineChart');
        if (!ctx) return;

        const lateVaccineChartLabels = <?php echo json_encode($lateVaccineChartLabels); ?>;
        const lateVaccineChartDataValues = <?php echo json_encode($lateVaccineChartDataValues); ?>;
         const totalExpectedVRF = <?php echo $expectedVRFCount; ?>;

        let displayData = [];
        if (showPercentage.lateVaccineChart === 'percentage') {
            displayData = lateVaccineChartDataValues.map(value => {
              return totalExpectedVRF > 0 ? ((value / totalExpectedVRF) * 100).toFixed(2) + '%' : '0%';
            });
        } else {
            displayData = lateVaccineChartDataValues;
        }

        if (lateVaccineChartInstance) {
            lateVaccineChartInstance.destroy();
        }

        lateVaccineChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: lateVaccineChartLabels,
                datasets: [{
                    label: showPercentage.lateVaccineChart === 'percentage' ? 'Late Vaccine (%)' : 'Late Vaccine',
                    data: lateVaccineChartDataValues,
                    backgroundColor: '#ffc107',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#000',
                        font: {
                            size: 14
                        },
                         formatter: (value, context) => {
                            return displayData[context.dataIndex];
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                         ticks: {
                            callback: function(value) {
                                if(showPercentage.lateVaccineChart === 'value'){
                                     return value;
                                }
                                else{
                                     return value + '%';
                                }
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    function number_format(number) {
        return number.toLocaleString();
    }
</script>
</body>
</html>
