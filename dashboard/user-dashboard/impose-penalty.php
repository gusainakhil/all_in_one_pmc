<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <?php include"head.php" ?>
  <style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background-color: #f8f8f8;
    }
    .container {
        width: 80%;
        margin: auto;
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .header {
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 20px;
    }
    .penalty-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .penalty-buttons button {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        font-weight: bold;
        border: none;
        border-radius: 5px;
        background: #ccc;
        cursor: pointer;
    }
    .penalty-form {
        margin-top: 20px;
        padding: 10px;
        background: #eee;
        border-radius: 5px;
    }
    .penalty-form input, .penalty-form textarea {
        width: calc(100% - 20px);
        margin: 5px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    .submit-btn {
        background: orange;
        color: white;
        border: none;
        padding: 10px;
        width: 100%;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        border-radius: 5px;
    }
    .penalty-table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
    }
    .penalty-table th, .penalty-table td {
        border: 1px solid #ccc;
        padding: 10px;
        text-align: center;
    }
    .penalty-table th {
        background: #007bff;
        color: white;
    }
</style>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
  <?php include"header.php" ?>
      <main class="app-main">
        <div class="app-content">
            <div class="container-fluid">
                <div class="header">IMPOSE PENALTY</div>
                <div class="penalty-buttons">
                    <button>NON REMOVAL OF GARBAGE FROM DUSTBINS</button>
                    <button>OPEN BURNING OF WASTE IN RAILWAYS PREMISES</button>
                    <button>ROOF OF PLATFORM SHELTERS</button>
                </div>
                <div class="penalty-form">
                    <input type="date" placeholder="Select Date">
                    <input type="number" placeholder="Penalty Amount">
                    <textarea placeholder="Penalty Review"></textarea>
                    <button class="submit-btn">Submit</button>
                </div>
                <table class="penalty-table">
                    <tr>
                        <th>Penalty Date</th>
                        <th>Penalty Amount</th>
                        <th>Penalty Review</th>
                    </tr>
                    <tr>
                        <td colspan="3">No data available in table</td>
                    </tr>
                </table>
                <div class="header">Total Penalty: 0</div>
                <button style="width: 100%; background: #aaa; padding: 10px; font-weight: bold; border-radius: 5px;">PENALTY IMPOSED BY NGT</button>
            </div>
      </main>

       <?php include"footer.php" ?>
 
    </div>

  </body>
  <!--end::Body-->
</html>


