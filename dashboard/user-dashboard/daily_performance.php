<?php 
    // Start the session
    session_start();

    // Check if userId is not set
    if (!isset($_SESSION['userId']) || empty($_SESSION['userId'])) {
        // Destroy the session
        session_unset();
        session_destroy();
        header("Location: https://pmc.beatleme.co.in/");
        exit();
    }

?>

<?php 
 include"../../connection.php";?>
<?php $id = $_GET['id']; ?>
<!doctype html>
<html lang="en">
  <!--begin::Head-->
 <?php include "head.php"?>
  <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 5px;
            text-align: center;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        h2 {
            text-align: center;
        }
        .header-info {
            margin-bottom: 10px;
        }
    </style>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
   <?php include "header.php"?>
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0"> Daily performace Log</h3></div>
              
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row g-4">
              <div class="col-md-12">
                <div class="card card-info card-outline mb-4">
                  <form class="needs-validation" novalidate>
                    <!--begin::Body-->
                    <div class="card-body">
                      <!--begin::Row-->
                      <div class="row g-3">

                        <div class="card-footer">
                          
                          <a href="daily_performance_summary.php" class="btn btn-success" target="blank" > summary</a> 
                          <a href="daily_performance_summary_2.php?id=<?php echo $id;  ?>" class="btn btn-success" target="blank" > summary2 bhuj</a> 
                           <a href="daily_performance_report.php" target="blank" class="btn btn-success" > Daily performace Log</a> 
                          
                  
                            <a href="daily-performance-target.php?id=<?php echo $id;  ?>" target="blank" class="btn btn-success" > Daily performace target</a>
                        </div>
         
                      </div>
                     
                    </div>
                
                  </form>
                </div>
                <!--end::Form Validation-->
              </div>
              <!--end::Col-->
            </div>
            <!--end::Row-->
          </div>
        </div>
        <!--end::App Content-->
      </main>
     <?php include "footer.php" ?>
    </div>
  </body>
</html>
