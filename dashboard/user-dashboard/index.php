<?php 
include "../../connection.php";
session_start();

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);

    $sql = "SELECT * FROM baris_userlogin WHERE login_token = '$token' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['userId'] = $row['userId'];
        $_SESSION['stationId'] = $row['StationId'];
        $_SESSION['db_usertype'] = $row['db_usertype'];
        $_SESSION['OrgID'] = $row['OrgID'];

        
    }
}
if (!isset($_SESSION['userId']) || empty($_SESSION['userId'])) {
    session_unset();
    session_destroy();
    header("Location: https://pmc.beatleme.co.in/");
    exit();
}
?>


<!doctype html>
<html lang="en">
  <!--begin::Head-->
<?php include"head.php" ?>
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
      <?php include"header.php" ?>
      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">
          </div>
        </div>
        <div class="app-content">
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <!--begin::Col-->
              <div class="col-lg-2 col-6">
                <!--begin::Small Box Widget 1-->
                <div class="small-box text-bg-primary">
                  <div class="inner">
                    <h3>1</h3>
                    <p>Deposit station</p>
                  </div>
                  <svg
                  class="small-box-icon"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                  xmlns="http://www.w3.org/2000/svg"
                  aria-hidden="true">
                  <path d="M7.5 19.5l-1.5 1.5m10.5-1.5l1.5 1.5M6 3a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V3zm2 0v11h8V3H8zm2 15h4m-5-3h6a3 3 0 0 0 3-3V3a3 3 0 0 0-3-3H8a3 3 0 0 0-3 3v11a3 3 0 0 0 3 3zm-1-8h6m-6 3h6" />
              </svg>
                </div>
              </div>
              <div class="col-lg-2 col-6">
                <div class="small-box text-bg-success">
                  <div class="inner">
                    <h3>53<sup class="fs-5">%</sup></h3>
                    <p>Auditors</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path
                      d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v15.75c0 1.035.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875V4.125c0-1.036-.84-1.875-1.875-1.875h-.75zM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 01-1.875-1.875V8.625zM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 013 19.875v-6.75z"></path>
                  </svg>
                 
                </div>
                <!--end::Small Box Widget 2-->
              </div>
              <!--end::Col-->
              <div class="col-lg-2 col-6">
                <!--begin::Small Box Widget 3-->
                <div class="small-box text-bg-warning">
                  <div class="inner">
                    <h3>44</h3>
                    <p>Audit</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path
                      d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z"></path>
                  </svg>
                </div>
              </div>
              <!--end::Col-->
              <div class="col-lg-2 col-6">
                <div class="small-box text-bg-danger">
                  <div class="inner">
                    <h3>65</h3>
                    <p>Trains</p>
                  </div>
                  <svg
                    class="small-box-icon"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path
                      clip-rule="evenodd"
                      fill-rule="evenodd"
                      d="M2.25 13.5a8.25 8.25 0 018.25-8.25.75.75 0 01.75.75v6.75H18a.75.75 0 01.75.75 8.25 8.25 0 01-16.5 0z"></path>
                    <path
                      clip-rule="evenodd"
                      fill-rule="evenodd"
                      d="M12.75 3a.75.75 0 01.75-.75 8.25 8.25 0 018.25 8.25.75.75 0 01-.75.75h-7.5a.75.75 0 01-.75-.75V3z"></path>
                  </svg>
                 
                </div>
                <!--end::Small Box Widget 4-->
              </div>
              <div class="col-lg-2 col-6">
                <!--begin::Small Box Widget 1-->
                <div class="small-box text-bg-Light">
                  <div class="inner">
                    <h3>1</h3>
                    <p>Coaches </p>
                  </div>
                  <svg
                  class="small-box-icon"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                  xmlns="http://www.w3.org/2000/svg"
                  aria-hidden="true">
                  <path d="M7.5 19.5l-1.5 1.5m10.5-1.5l1.5 1.5M6 3a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V3zm2 0v11h8V3H8zm2 15h4m-5-3h6a3 3 0 0 0 3-3V3a3 3 0 0 0-3-3H8a3 3 0 0 0-3 3v11a3 3 0 0 0 3 3zm-1-8h6m-6 3h6" />
              </svg>
              
           
                </div>
                <!--end::Small Box Widget 1-->
              </div>
              <div class="col-lg-2 col-6">
                <!--begin::Small Box Widget 1-->
                <div class="small-box text-bg-dark">
                  <div class="inner">
                    <h3>1</h3>
                    <p>Platform</p>
                  </div>
                  <svg
                  class="small-box-icon"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                  xmlns="http://www.w3.org/2000/svg"
                  aria-hidden="true">
                  <path d="M7.5 19.5l-1.5 1.5m10.5-1.5l1.5 1.5M6 3a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V3zm2 0v11h8V3H8zm2 15h4m-5-3h6a3 3 0 0 0 3-3V3a3 3 0 0 0-3-3H8a3 3 0 0 0-3 3v11a3 3 0 0 0 3 3zm-1-8h6m-6 3h6" />
              </svg>
              
           
                </div>
              </div>
              <style>

    #datetime {
      text-align: center;
    }
    #date {
      font-size: 1.5rem;
    
    }
    #time {
      font-size: 1rem;
      font-weight: bold;
    }
  </style>
               <div class="col-lg-2 col-6">
                <!--begin::Small Box Widget 1-->
                <div class="small-box text-bg-warning">
                  <div class="inner">
                    <h3></h3>
                      <div id="datetime">
    <div id="date"></div>
    <div id="time"></div>
  </div>
                  </div>
                  
              
           
                </div>
                
              </div>
                            <div class="col-lg-2 col-6">
                <!--begin::Small Box Widget 3-->
               
                <!--end::Small Box Widget 3-->
              </div>
                          
                            

            </div>
            
          </div>
        </div>
      </main>

      <?php include "footer.php" ?>

    </div>


    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
 <script>
    function updateDateTime() {
      const now = new Date();

      // Format date: e.g. "Friday, April 18, 2025"
      const dateOptions = {
        weekday: 'long',
        year:    'numeric',
        month:   'long',
        day:     'numeric'
      };
      const formattedDate = now.toLocaleDateString('en-US', dateOptions);

      // Format time: e.g. "02:34:56 PM"
      const formattedTime = now.toLocaleTimeString('en-US');

      // Update the DOM
      document.getElementById('date').textContent = formattedDate;
      document.getElementById('time').textContent = formattedTime;
    }

    // Initial call
    updateDateTime();
    // Update every second
    setInterval(updateDateTime, 1000);
  </script>

