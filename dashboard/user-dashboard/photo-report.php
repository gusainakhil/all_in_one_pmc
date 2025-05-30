<?php session_start(); 
 include"../../connection.php";?>
<!doctype html>
<html lang="en">
  <?php
  include"head.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$startDate = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$endDate = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-t');
$station_id = $_SESSION['stationId'];
$period = new DatePeriod(
    new DateTime($startDate),
    new DateInterval('P1D'),
    (new DateTime($endDate))->modify('+1 day')
);
?>
<head>
    <title>photo report </title>
<style>
    .railway-frame {
        height: 90vh;
        overflow-y: auto;
         font-size:11px;
         font-weight:400;
        box-sizing: border-box;
    }

    .railway-container {
        width: 95%;
        margin: auto;
        page-break-after: always;
    }

    .railway-report-title {
        text-align: center;
        font-weight: bold;
    }

    .railway-report-subtitle {
        text-align: center;
        
    }

    .railway-section-title {
        text-align: center;
        font-weight: bold;
        font-size:14px;
        
    }

    .railway-table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 20px;
    }

    .railway-table, .railway-table th, .railway-table td {
        border: 1px solid black;
        text-align: center;
    }

    .railway-table th {
        background-color: #f2f2f2;
    }
    .railway-table th:nth-child(1) { width: 5%; }   
    .railway-table th:nth-child(2) { width: 30%; } 
    .railway-table th:nth-child(3) { width: 20%; }  
    .railway-table th:nth-child(4) { width: 10%; } 
    .railway-table th:nth-child(5) { width: 10%; }  
    .railway-table th:nth-child(6) { width: 10%; }
    .railway-filter-form {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        margin: 4px auto;
        width: fit-content;
        flex-wrap: wrap;
    }
    .railway-filter-form label {
        font-weight: 500;
        margin-right: 5px;
    }
    
    
    .railway-filter-form input[type="date"],
    .railway-filter-form button {
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
    }
    .railway-filter-form select {
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
    }
    
    .railway-filter-form button {
        background-color: green;
        color: white;
        border: none;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    /*.railway-filter-form button:hover {*/
    /*    background-color: green;*/
    /*}*/
    /* Print styles */
    
</style>
</head>
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
     <?php include"header.php"?>
      <main class="app-main">
        
        <form class="railway-filter-form" method="GET">
           
            <select>
                <option>select Auditor</option>
                
            </select>
            
    <label for="from_date">From:</label>
    <input type="date" name="from_date" id="from_date"
        value="<?php echo isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01'); ?>">
    <label for="to_date">To:</label>
    <input type="date" name="to_date" id="to_date"
        value="<?php echo isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-t'); ?>">
    <input type="hidden" name="station_id" value="<?php echo htmlspecialchars($station_id); ?>">
    <button type="submit">Go</button>
     <button type="submit"><a   href="daily_surprise_summary.php" target="blank" style="text-decoration:none; color:white;"; >summary</a></button>
</form>
<?php echo $_SESSION['stationName'] ?>
<br>
<div class="railway-frame">

</div>
      </main>
      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">Anything you want</div>
        <strong>
          Copyright &copy; 2025&nbsp;
          <a href="#" class="text-decoration-none"></a>.
        </strong>
        All rights reserved.
      </footer>
    </div>
  </body>
</html>
