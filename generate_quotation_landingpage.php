<?php
include_once 'partials/_dbconnect.php';
session_start();
$companyname001 = $_SESSION['companyname'];
$enterprise=$_SESSION['enterprise'];
$dashboard_url = '';
if ($enterprise === 'rental') {
    $dashboard_url = 'rental_dashboard.php';
} elseif ($enterprise === 'logistics') {
    $dashboard_url = 'logisticsdashboard.php';
} 
elseif ($enterprise === 'epc') {
    $dashboard_url = 'epc_dashboard.php';
} 
else {
    $dashboard_url = '';
}
$showAlert = false;
$showError = false;
$show_celebration=false;

// Pagination setup
$quotations_per_page = 40; // changed from 10 to 40
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $quotations_per_page;

// Count total quotations for pagination
$sql_count = "SELECT COUNT(*) as total FROM `quotation_generated` WHERE company_name='$companyname001'";
$result_count = mysqli_query($conn, $sql_count);
$total_quotations = 0;
if ($row_count = mysqli_fetch_assoc($result_count)) {
    $total_quotations = intval($row_count['total']);
}
$total_pages = ceil($total_quotations / $quotations_per_page);

// Fetch only current page quotations
$sql = "SELECT * FROM `quotation_generated` WHERE company_name='$companyname001' ORDER BY ref_no DESC LIMIT $quotations_per_page OFFSET $offset";
$result = mysqli_query($conn, $sql);
?>
<?php
if(isset($_SESSION['success'])){
  $showAlert= true;
  unset($_SESSION['success']);
}
else if(isset($_SESSION['failed'])){
  $showError= true;
  unset($_SESSION['failed']);

}
?>
<?php
if(isset($_POST['quote_status_btn'])){
  include "partials/_dbconnect.php";
  $stats_ref_no = !empty($_POST['stats_ref_no']) ? $_POST['stats_ref_no'] : null;
  $stats_compname = !empty($_POST['stats_compname']) ? $_POST['stats_compname'] : null;
  $generated_By = !empty($_POST['generated_By']) ? $_POST['generated_By'] : null;
  $current_status_ = !empty($_POST['current_status_']) ? $_POST['current_status_'] : null;

  $enquiry_close = !empty($_POST['enquiry_close']) ? $_POST['enquiry_close'] : null;
  $postponed_date = !empty($_POST['postponed_date']) ? $_POST['postponed_date'] : null;
  $lost_to = !empty($_POST['lost_to']) ? $_POST['lost_to'] : null;
  $regretted_reason = !empty($_POST['regretted_reason']) ? $_POST['regretted_reason'] : null;
  $unique_id=$_POST['unique_id'];

  // New fields for 'Won' status
  $client_name = isset($_POST['client_name']) ? $_POST['client_name'] : null;
  $crane_dispatch_date = isset($_POST['crane_dispatch_date']) ? $_POST['crane_dispatch_date'] : null;
  $crane_dispatch_time = isset($_POST['crane_dispatch_time']) ? $_POST['crane_dispatch_time'] : null;
  $rental_fix = isset($_POST['rental_fix']) ? $_POST['rental_fix'] : null;
  $mob_price = isset($_POST['mob_price']) ? $_POST['mob_price'] : null;
  $demob_price = isset($_POST['demob_price']) ? $_POST['demob_price'] : null;

  $check_query = "SELECT * FROM `quotation_status` WHERE `ref_no` = '$stats_ref_no' AND `companyname` = '$stats_compname'";
  $check_result = mysqli_query($conn, $check_query);

  if(mysqli_num_rows($check_result) > 0){
    // Update query
    $sql_crnt_status = "UPDATE `quotation_status` SET 
                        `generated_by` = '$generated_By', 
                        `current_status` = '$current_status_', 
                        `inquiry_closed_reason` = '$enquiry_close', 
                        `regretted_reason` = '$regretted_reason', 
                        `lost_to` = '$lost_to', 
                        `postponed_date` = '$postponed_date', 
                        `unique_id`='$unique_id',
                        `client_name` = '$client_name',
                        `crane_dispatch_date` = '$crane_dispatch_date',
                        `crane_dispatch_time` = '$crane_dispatch_time',
                        `rental_fix` = '$rental_fix',
                        `mob_price` = '$mob_price',
                        `demob_price` = '$demob_price'
                        WHERE `ref_no` = '$stats_ref_no' AND `companyname` = '$stats_compname'";
  } else {
    // Insert query
    $sql_crnt_status = "INSERT INTO `quotation_status` 
                        (`unique_id`,`ref_no`, `generated_by`, `companyname`, `current_status`, `inquiry_closed_reason`, `regretted_reason`, `lost_to`, `postponed_date`, `client_name`, `crane_dispatch_date`, `crane_dispatch_time`, `rental_fix`, `mob_price`, `demob_price`) 
                        VALUES 
                        ('$unique_id','$stats_ref_no', '$generated_By', '$stats_compname', '$current_status_', '$enquiry_close', '$regretted_reason', '$lost_to', '$postponed_date', '$client_name', '$crane_dispatch_date', '$crane_dispatch_time', '$rental_fix', '$mob_price', '$demob_price')";
  }
  $result_new_=mysqli_query($conn,$sql_crnt_status);
  if($result_new_){
    $showAlert=true;
  }
  else{
    $showError=true;
  }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quotations</title> 
<link rel="shortcut icon" href="favicon.jpg" type="image/x-icon"> 
<link rel="stylesheet" href="style.css"> 
<link rel="stylesheet" href="tiles.css">
<script src="main.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>

body {
  margin: 0;
  padding: 0;
}

.quotation-table-container {
  padding: 20px; 

}

.quotation-table {
  width: 100%;
  font-size: 13px;
  border-collapse: collapse;
  margin-top: 20px;
  margin: 0 auto; 
  padding: 10px; 

}
.date_content_td{
  width: 7%;
}
.ref_no_th{
  width: 5%;
}
.quotation-table, .quotation-table th, .quotation-table td {
  border: 1px solid black!important; 
  margin-top:30px;
}

.quotation-table th, .quotation-table td {
  padding: 8px!important;
  text-align: left!important;
}  

.quotation-table .table-heading { 
    background-color: #4067B5!important; 
    color: white!important;
}

.generate-btn-container {
  display: flex!important;
  justify-content: space-between!important;
  align-items: center!important;
  margin-bottom: 20px!important;
}

.generate-btn {
  background-color: white!important;
  color: white!important;
  border: none!important;
  border-radius: 4px!important;
  padding: 10px 20px!important;
  cursor: pointer!important;
} 

.article-wrapper{ 
    width:288px!important; 
    height:68px;
}

.quotation-icon { 
  background-color:#B4C5E4; 
  color:black; 
  border: 1px; 
  border-radius:5px; 
  width: 22px; /* Set width for the icon */
  height: 22px; /* Set height for the icon */
  display: inline-block; /* Ensure icon width and height are respected */
  text-align: center; /* Center icon within its container */
  line-height: 22px; /* Ensure icon is vertically centered */
}

.pagination-nav {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  gap: 2px;
  margin: 0 auto;
  padding: 0;
}

.pagination-btn {
  color: #4067B5;
  padding: 6px 14px;
  text-decoration: none;
  border: 1px solid #4067B5;
  margin: 0 2px;
  border-radius: 4px;
  background: #fff;
  font-weight: 500;
  transition: background 0.2s, color 0.2s;
  min-width: 36px;
  text-align: center;
  display: inline-block;
}

.pagination-btn:hover {
  background: #4067B5;
  color: #fff;
}

.pagination-btn.active {
  background: #4067B5;
  color: #fff;
  border: 1px solid #4067B5;
  pointer-events: none;
}

.pagination-ellipsis {
  padding: 6px 8px;
  color: #888;
  background: none;
  border: none;
  font-size: 16px;
  min-width: 20px;
  pointer-events: none;
}

/* Responsive for pagination */
@media screen and (max-width: 600px) {
  .pagination-nav {
    width: 100%;
    font-size: 14px;
    gap: 0;
  }
  .pagination-btn {
    padding: 6px 8px;
    font-size: 13px;
    min-width: 28px;
  }
}

@media screen and (max-width: 600px) {
  .quotation-table {
    border: 0;
  }
  .quotation-table thead {
    display: none;
  }
  .quotation-table tr {
    border-bottom: 1px solid black;
    display: block;
    margin-bottom: 10px;
  }
  .quotation-table td {
    border-bottom: none;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 10px 0;
  }
  .quotation-table td:before {
    content: attr(data-label);
    float: left;
    font-weight: bold;
    text-transform: uppercase;
  }
  .generate-btn-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
  }
  .generate-btn-container h2 {
    margin: 0;
  }
  .generate-btn {
    margin-left: auto;
    padding: 8px 16px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }
}
</style>

</head>
<body> 

    <div class="navbar1">
    <div class="logo_fleet">
    <img src="logo_fe.png" alt="FLEET EIP" onclick="window.location.href='<?php echo $dashboard_url  ?>'">
</div>

        <div class="iconcontainer">
        <ul>
      <li><a href="<?php echo $dashboard_url ?>">Dashboard</a></li>
            <li><a href="news/">News</a></li>
            <li><a href="logout.php">Log Out</a></li></ul>
        </div>
    </div>
    <?php
if ($showAlert) {
    echo  '<label>
    <input type="checkbox" class="alertCheckbox" autocomplete="off" />
    <div class="alert notice">
      <span class="alertClose">X</span>
      <span class="alertText_addfleet"><b>Success! </b>
          <br class="clear"/></span>
    </div>
  </label>';
}
if ($showError) {
    echo '<label>
    <input type="checkbox" class="alertCheckbox" autocomplete="off" />
    <div class="alert error">
      <span class="alertClose">X</span>
      <span class="alertText">Something Went Wrong
          <br class="clear"/></span>
    </div>
  </label>';

}


?>
<?php

// Define your SQL query to get the counts of quotations by sender
$sql_quote_stats = "
    SELECT sender_name, COUNT(*) AS quotation_count
    FROM quotation_generated
    WHERE company_name = '$companyname001'
    GROUP BY sender_name
";

// Execute the query
$result_quote_stats = mysqli_query($conn, $sql_quote_stats);

// Check if the query was successful
if ($result_quote_stats === false) {
    die("Error in SQL query: " . mysqli_error($conn));
}
?>

<div class="quotation_stats">
<table class="quotation_Stats_table" <?php if ($result_quote_stats->num_rows <= 0) { echo 'hidden'; } ?>>
<thead>
      <tr>
        <th>Names</th>
        <th>QT</th>
        <th class="status_heading2">Not Defined</th>
        <th class="status_heading">Statuses</th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Loop through each row from the result
      while ($row_quote = mysqli_fetch_assoc($result_quote_stats)) {
          // Get sender name and total quotations
          $sender_name = htmlspecialchars($row_quote['sender_name']);
          $total_quotations = intval($row_quote['quotation_count']);

          // Define SQL query to get the status counts for the current sender
          $sql_quote_won_lost = "
          SELECT current_status, COUNT(*) AS status_count
          FROM quotation_status
          WHERE generated_by = '$sender_name' AND companyname = '$companyname001'
          GROUP BY current_status";
          // Execute the secondary query
          $result_quote_won = mysqli_query($conn, $sql_quote_won_lost);

          // Check if the secondary query was successful
          if ($result_quote_won === false) {
              die("Error in SQL query: " . mysqli_error($conn));
          }

          // Initialize an array to store status counts
          $status_counts = [];
          $total_status_counts = 0;
          while ($row_status = mysqli_fetch_assoc($result_quote_won)) {
              $status = htmlspecialchars($row_status['current_status']);
              $count = intval($row_status['status_count']);
              $status_counts[$status] = $count;
              $total_status_counts += $count;
          }

          // Calculate open quotations
          $open_quotations = $total_quotations - $total_status_counts;

          // Prepare the statuses for display
          $status_display = '';
          foreach ($status_counts as $status => $count) {
              $status_display .= $status . ": " . $count . " | ";
          }
          
          // Remove the trailing ' | ' if there is any status to display
          $status_display = rtrim($status_display, ' | ');

          // Display the data in table row
          echo "<tr>";
          echo "<td><a href='quotationinfo.php?id=$sender_name'>" . $sender_name . "</a></td>";
          echo "<td>" . $total_quotations . "</td>";
          echo "<td>" . $open_quotations . "</td>";
          echo "<td class='status_heading'>" . $status_display . "</td>";
          echo "</tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<!-- <div class="won_image_container" id="celebration_gif_cont">
  <img src="celebration1.gif" alt="">
</div> -->
<div class="quotation-table-container">
  <div class="generate-btn-container">
    <h2>Submitted Quotation</h2>
    <button class="generate-btn"> 
    <article class="article-wrapper" onclick="location.href='generate_quotation.php'" > 
  <div class="rounded-lg container-projectss ">
    </div>
    <div class="project-info">
      <div class="flex-pr">
        <div class="project-title text-nowrap">Generate  Quotations</div>
          <div class="project-hover">
            <svg style="color: black;" xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" color="black" stroke-linejoin="round" stroke-linecap="round" viewBox="0 0 24 24" stroke-width="2" fill="none" stroke="currentColor"><line y2="12" x2="19" y1="12" x1="5"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
          </div>
          <div class="types">
        </div>
    </div>
</article>
    </button>
  </div>



<?php if(mysqli_num_rows($result)>0){ ?>
  <div class="filters" id="quotationfilter">
    <p>Apply Filter :</p>
    <form action="quotationfilter.php" class="quotationfilterform" method="GET">
        <select name="filtertype" onchange="quotation_filter()" id="quotationfilterdd" class="filter_button">
            <option value="" disabled selected>Select Filter</option>
            <option value="Date">Date </option>
            <option value="Client">Client </option>
            <option value="Status">Status </option>
            <option value="Asset Code">Asset Code </option>
        </select>
        <input type="date" name="quotefilter_date" id="quotationfilterdate" class="quotation_select">
        <select name="filterclient" id="clientfilter" class="quotation_select">
          <option value=""disabled selected>Select Client</option>
          <?php 
          $sqlfilterclient="SELECT * FROM `rentalclient_basicdetail` where companyname='$companyname001' ORDER BY clientname ASC";
          $result_client=mysqli_query($conn,$sqlfilterclient);
          while($rowclientfilter=mysqli_fetch_assoc($result_client)){
            ?>
          <option value="<?php echo $rowclientfilter['clientname'] ?>"><?php  echo $rowclientfilter['clientname'] ?></option>
            <?php
          }
          ?>
        </select>
        <select name="filterstatus" id="statusfilter" class="quotation_select">
          <option value=""disabled selected>Select Status</option> 
          <option value="Open">Open</option>
          <option value="Won">Won</option> 
          <option value="Lost">Lost</option> 
          <option value="Regretted">Regretted</option>
          <option value="Inquiry Closed">Inquiry Closed</option>
        </select>
        <select name="filterassetcode" id="acfilter" class="quotation_select">
          <option value=""disabled selected>Select Assetcode</option>
          <?php
          $filterac="SELECT * FROM `fleet1` where companyname='$companyname001'";
          $resultfilterac=mysqli_query($conn,$filterac);
          while($rowacfilter=mysqli_fetch_assoc($resultfilterac)){
            ?>
          <option value="<?php echo $rowacfilter['assetcode'] ?>"><?php echo $rowacfilter['assetcode'] .'-'. $rowacfilter['sub_type'] .'-'. $rowacfilter['model'] ?></option>
            <?php
          }
          ?>
        </select>
        <button id="quotationfilterbutton" class="filter_button">Submit</button>
    </form>
</div>

<table class="quotation-table">
  <thead>
    <tr>
      <th class="table-heading date_content_td">Date</th>
      <th class="table-heading ref_no_th">Ref No</th>
      <th class="table-heading">To</th>
      <th class="table-heading">Asset Details</th> <!-- Combined Column -->
      <th class="table-heading">Shift</th>
      <th class="table-heading">Duration</th>
      <th class="table-heading">Site</th>
      <th class="table-heading">Status</th>
      <th class="table-heading">Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php
  while ($row = mysqli_fetch_assoc($result)) {
      $sql_crnt_status_check = "SELECT * FROM `quotation_status` WHERE ref_no = '" . $row['ref_no'] . "' AND companyname = '" . $companyname001 . "' AND unique_id = '" . $row['uniqueid'] . "'";
      $result_crnt_status_check = mysqli_query($conn, $sql_crnt_status_check);
      $row_crnt_status_check = mysqli_fetch_assoc($result_crnt_status_check);
  ?>
    <tr>
        <td data-label="Date"><?php echo date('d-m-y', strtotime($row['quote_date'])); ?></td>
        <td data-label="Ref No"><?php echo $row['ref_no']; ?></td>
        <td class="todatacont" data-label="To"><?php echo $row['to_name']; ?></td>
        <td data-label="Asset Details">
            <?php 
            echo ($row['asset_code'] === 'New Equipment') ? 'New' : htmlspecialchars($row['asset_code']); 
            echo " - <strong>" . $row['make'] . " " . $row['model'] . "</strong>"; 
            echo " - ₹" . number_format($row['rental_charges']);
            ?>
        </td>
        <td data-label="Duration"><?php echo $row['shift_info']; ?></td>
        <td data-label="Period Contract"><?php echo $row['period_contract']; ?></td>
        <td class="todatacont" data-label="Site Location"><?php echo $row['site_loc']; ?></td>
        <td data-label="Current Status" onclick="toggleModal('modal_<?php echo $row['ref_no']; ?>')" style="cursor: pointer;">
            <mark><?php echo isset($row_crnt_status_check['current_status']) && $row_crnt_status_check['current_status'] !== '' ? $row_crnt_status_check['current_status'] : 'Open'; ?></mark>
        </td>        
        <!-- Modal inside the loop with unique ID -->
        <div class="modal" id="modal_<?php echo $row['ref_no']; ?>">
            <form class="quote_status_form" action="generate_quotation_landingpage.php" method="POST" autocomplete="off">
                <div class="stat_container">
                    <div class="icon_container">
                        <p class="headingpara">Quotation Status</p>
                        <span class="back_icon" onclick="toggleModal('modal_<?php echo $row['ref_no']; ?>')">X</span>
                    </div>

                    <div class="trial1">
                        <input type="text" name="stats_ref_no" placeholder="" value="<?php echo htmlspecialchars($row['ref_no']); ?>" class="input02" readonly>
                        <label class="placeholder2">Ref No</label>
                    </div>
                    
                    <input type="hidden" name="unique_id" value="<?php echo $row['uniqueid'] ?>" readonly>
                    <input type="hidden" name="stats_compname" value="<?php echo htmlspecialchars($companyname001); ?>">
                    
                    <div class="trial1">
                        <input type="text" name="generated_By" placeholder="" value="<?php echo htmlspecialchars($row['sender_name']); ?>" class="input02" readonly>
                        <label class="placeholder2">Generated By</label>
                    </div>
                    
                    <div class="trial1">
<select name="current_status_" class="input02" onchange="status_change(this.value, '<?php echo $row['ref_no']; ?>')" required>
    <?php
    $current_status_val = isset($row_crnt_status_check['current_status']) && $row_crnt_status_check['current_status'] !== '' ? $row_crnt_status_check['current_status'] : 'Open';
    ?>
    <option value="" disabled <?php if($current_status_val == '') echo 'selected'; ?>>Current Status</option>
    <option value="Closed" <?php if($current_status_val == 'Closed' || $current_status_val == 'Inquiry Closed') echo 'selected'; ?>>Inquiry Closed</option>
    <option value="Open" <?php if($current_status_val == 'Open') echo 'selected'; ?>>Open</option>
    <option value="Regretted" <?php if($current_status_val == 'Regretted') echo 'selected'; ?>>Regretted</option>
    <option value="Won" <?php if($current_status_val == 'Won') echo 'selected'; ?>>Won</option>
    <option value="Lost" <?php if($current_status_val == 'Lost') echo 'selected'; ?>>Lost</option>
</select>
</div> 

                    <!-- Fields visible only when status is 'Won' -->
                    <div class="trial1 won-only" id="client_name_<?php echo $row['ref_no']; ?>" style="display: none;">
                        <input type="text" name="client_name" placeholder="" class="input02">
                        <label class="placeholder2">Client Name</label>
                    </div>  

                    <div class="trial1 won-only" id="crane_dispatch_date_<?php echo $row['ref_no']; ?>" style="display: none;">
                        <input type="date" name="crane_dispatch_date" placeholder="" class="input02">
                        <label class="placeholder2">Crane Dispatch Date</label>
                    </div> 

                    <div class="trial1 won-only" id="crane_dispatch_time_<?php echo $row['ref_no']; ?>" style="display: none;">
                        <input type="time" name="crane_dispatch_time" id="crane_dispatch_time_input_<?php echo $row['ref_no']; ?>" class="input02">
                        <label class="placeholder2">Crane Dispatch Time</label>
                    </div> 

                    <div class="trial1 won-only" id="rental_fix_<?php echo $row['ref_no']; ?>" style="display: none;">
                        <select name="rental_fix" class="input02">
                            <option value="" disabled selected>Select</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                        <label class="placeholder2">Is Rental FIX?</label>
                    </div>  

                    <div class="trial1 won-only" id="mob_price_<?php echo $row['ref_no']; ?>" style="display: none;">
                        <input type="text" name="mob_price" placeholder="" class="input02">
                        <label class="placeholder2">Mob Price</label>
                    </div>   

                    <div class="trial1 won-only" id="demob_price_<?php echo $row['ref_no']; ?>" style="display: none;">
                        <input type="text" name="demob_price" placeholder="" class="input02">
                        <label class="placeholder2">Demob Price</label>
                    </div>   
                    <!-- End won-only fields -->

                    <div class="trial1 reason-dd" id="enquiry_closed_dd_<?php echo $row['ref_no']; ?>" style="display: none;">
                        <select name="enquiry_close" class="input02" onchange="enquiry_info()">
                            <option value="" disabled selected>Inquiry Closed Reason</option>
                            <option value="Required Equipment Changed">Required Equipment Changed</option>
                            <option value="Requirement Postponed">Requirement Postponed</option>
                            <option value="Project Issue">Project Issue</option>
                        </select>
                    </div>
                    
                    <div class="trial1 reason-dd" id="postponed_to_<?php echo $row['ref_no']; ?>" style="display: none;">
                        <input type="date" name="postponed_date" placeholder="" class="input02">
                        <label class="placeholder2">Postponed Date</label>
                    </div>
                    
                    <div class="trial1 reason-dd" id="lost_to_<?php echo $row['ref_no']; ?>" style="display: none;">
                        <input type="text" name="lost_to" placeholder="" class="input02">
                        <label class="placeholder2">Lost To ?</label>
                    </div>
                    
                    <div class="trial1 reason-dd" id="regretted_reason_<?php echo $row['ref_no']; ?>" style="display: none;">
                        <select name="regretted_reason" class="input02" >
                            <option value="" disabled selected>Regretted Reason</option>
                            <option value="Price Issue">Price Issue</option>
                            <option value="Equipment Not Available">Equipment Not Available</option>
                            <option value="Other Issue">Other Issue</option>
                        </select>
                    </div>
                    
                    <button class="basic-detail-button" type="submit" name="quote_status_btn">Submit</button>
                </div>
            </form>
        </div>
        
        <td data-label="Actions">
            <a href="editquotation.php?quote_id=<?php echo $row['sno']; ?>" class="quotation-icon" title="Edit">
                <i style="width: 22px; height: 22px;" class="bi bi-pencil"></i>
            </a>
            <a href="delete_quotation.php?del_id=<?php echo $row['sno']; ?>" onclick="return confirmDelete();" class="quotation-icon" title="Delete">
                <i style="width: 22px; height: 22px;" class="bi bi-trash"></i>
            </a>
            <a href="view_quotation.php?quote_id=<?php echo $row['sno']; ?>" class="quotation-icon" title="Print">
                <i style="width: 22px; height: 22px;" class="bi bi-file-text"></i>
            </a>
        </td>
    </tr>
<?php
} }
?>
  </tbody>
</table>

<!-- Pagination Controls -->
<?php if ($total_pages > 1): ?>
<div style="text-align:center; margin:20px 0;">
    <nav class="pagination-nav">
        <?php if ($page > 1): ?>
            <a href="?page=1" class="pagination-btn">First</a>
            <a href="?page=<?php echo $page-1; ?>" class="pagination-btn">&laquo; Prev</a>
        <?php endif; ?>

        <?php
        // Show first page, ellipsis, 2 before, current, 2 after, ellipsis, last page
        $visible = 2; // pages before/after current
        if ($total_pages <= 7) {
            // Show all pages if few
            for ($i = 1; $i <= $total_pages; $i++) {
                echo '<a href="?page='.$i.'" class="pagination-btn'.($i==$page?' active':'').'">'.$i.'</a>';
            }
        } else {
            // Always show first page
            echo '<a href="?page=1" class="pagination-btn'.($page==1?' active':'').'">1</a>';
            // Ellipsis if needed
            if ($page > $visible + 2) {
                echo '<span class="pagination-ellipsis">...</span>';
            }
            // Middle pages
            $start = max(2, $page - $visible);
            $end = min($total_pages - 1, $page + $visible);
            for ($i = $start; $i <= $end; $i++) {
                echo '<a href="?page='.$i.'" class="pagination-btn'.($i==$page?' active':'').'">'.$i.'</a>';
            }
            // Ellipsis if needed
            if ($page < $total_pages - $visible - 1) {
                echo '<span class="pagination-ellipsis">...</span>';
            }
            // Always show last page
            echo '<a href="?page='.$total_pages.'" class="pagination-btn'.($page==$total_pages?' active':'').'">'.$total_pages.'</a>';
        }
        ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page+1; ?>" class="pagination-btn">Next &raquo;</a>
            <a href="?page=<?php echo $total_pages; ?>" class="pagination-btn">Last</a>
        <?php endif; ?>
    </nav>
</div>
<?php endif; ?>
</body>
<script>
function toggleModal(modalId) {
    var modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = (modal.style.display === 'block') ? 'none' : 'block';
    } else {
        console.error("Modal with ID " + modalId + " not found.");
    }
}
function status_change(status, ref_no) {
    const enquiry_closed_dd = document.getElementById(`enquiry_closed_dd_${ref_no}`);
    const postponed_to = document.getElementById(`postponed_to_${ref_no}`);
    const lost_to = document.getElementById(`lost_to_${ref_no}`);
    const regretted_reason = document.getElementById(`regretted_reason_${ref_no}`);

    // Won-only fields
    const client_name = document.getElementById(`client_name_${ref_no}`);
    const crane_dispatch_date = document.getElementById(`crane_dispatch_date_${ref_no}`);
    const crane_dispatch_time = document.getElementById(`crane_dispatch_time_${ref_no}`);
    const crane_dispatch_time_input = document.getElementById(`crane_dispatch_time_input_${ref_no}`);
    const rental_fix = document.getElementById(`rental_fix_${ref_no}`);
    const mob_price = document.getElementById(`mob_price_${ref_no}`);
    const demob_price = document.getElementById(`demob_price_${ref_no}`);

    if (status === 'Closed') {
        enquiry_closed_dd.style.display = 'block';
        postponed_to.style.display = 'none';
        lost_to.style.display = 'none';
        regretted_reason.style.display = 'none';
        // Hide won-only fields
        if (client_name) client_name.style.display = 'none';
        if (crane_dispatch_date) crane_dispatch_date.style.display = 'none';
        if (crane_dispatch_time) crane_dispatch_time.style.display = 'none';
        if (rental_fix) rental_fix.style.display = 'none';
        if (mob_price) mob_price.style.display = 'none';
        if (demob_price) demob_price.style.display = 'none';
    } else if (status === 'Lost') {
        lost_to.style.display = 'block';
        enquiry_closed_dd.style.display = 'none';
        postponed_to.style.display = 'none';
        regretted_reason.style.display = 'none';
        // Hide won-only fields
        if (client_name) client_name.style.display = 'none';
        if (crane_dispatch_date) crane_dispatch_date.style.display = 'none';
        if (crane_dispatch_time) crane_dispatch_time.style.display = 'none';
        if (rental_fix) rental_fix.style.display = 'none';
        if (mob_price) mob_price.style.display = 'none';
        if (demob_price) demob_price.style.display = 'none';
    } else if (status === 'Regretted') {
        regretted_reason.style.display = 'block';
        enquiry_closed_dd.style.display = 'none';
        postponed_to.style.display = 'none';
        lost_to.style.display = 'none';
        // Hide won-only fields
        if (client_name) client_name.style.display = 'none';
        if (crane_dispatch_date) crane_dispatch_date.style.display = 'none';
        if (crane_dispatch_time) crane_dispatch_time.style.display = 'none';
        if (rental_fix) rental_fix.style.display = 'none';
        if (mob_price) mob_price.style.display = 'none';
        if (demob_price) demob_price.style.display = 'none';
    } else if (status === 'Postponed') {
        postponed_to.style.display = 'block';
        enquiry_closed_dd.style.display = 'none';
        lost_to.style.display = 'none';
        regretted_reason.style.display = 'none';
        // Hide won-only fields
        if (client_name) client_name.style.display = 'none';
        if (crane_dispatch_date) crane_dispatch_date.style.display = 'none';
        if (crane_dispatch_time) crane_dispatch_time.style.display = 'none';
        if (rental_fix) rental_fix.style.display = 'none';
        if (mob_price) mob_price.style.display = 'none';
        if (demob_price) demob_price.style.display = 'none';
    } else if (status === 'Won') {
        // Show won-only fields
        if (client_name) client_name.style.display = 'block';
        if (crane_dispatch_date) crane_dispatch_date.style.display = 'block';
        if (crane_dispatch_time) crane_dispatch_time.style.display = 'block';
        if (rental_fix) rental_fix.style.display = 'block';
        if (mob_price) mob_price.style.display = 'block';
        if (demob_price) demob_price.style.display = 'block';
        // Set current time in 12-hour format with AM/PM if input is empty
        if (crane_dispatch_time_input && !crane_dispatch_time_input.value) {
            const now = new Date();
            let hours = now.getHours();
            const minutes = now.getMinutes();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            const minutesStr = minutes < 10 ? '0' + minutes : minutes;
            crane_dispatch_time_input.value = hours + ':' + minutesStr + ' ' + ampm;
        }
        // Hide other reason fields
        enquiry_closed_dd.style.display = 'none';
        postponed_to.style.display = 'none';
        lost_to.style.display = 'none';
        regretted_reason.style.display = 'none';
    } else {
        enquiry_closed_dd.style.display = 'none';
        postponed_to.style.display = 'none';
        lost_to.style.display = 'none';
        regretted_reason.style.display = 'none';
        // Hide won-only fields
        if (client_name) client_name.style.display = 'none';
        if (crane_dispatch_date) crane_dispatch_date.style.display = 'none';
        if (crane_dispatch_time) crane_dispatch_time.style.display = 'none';
        if (rental_fix) rental_fix.style.display = 'none';
        if (mob_price) mob_price.style.display = 'none';
        if (demob_price) demob_price.style.display = 'none';
    }
}
function enquiry_info() {
    const enquiry_reason = document.getElementById("enquiry_reason");
    const postponed_to = document.getElementById("postponed_to_");

    if (enquiry_reason.value === 'Requirement Postponed') {
        postponed_to.style.display = 'block';
    } else {
        postponed_to.style.display = 'none';
    }
}
function goBack() {
    window.location.href = "generate_quotation_landingpage.php";
}

document.addEventListener('DOMContentLoaded', function() {
    // Check if choose_Ac2 is not empty and call choose_new_equ2()
    var quotationfilterdd = document.getElementById('quotationfilterdd');
    if (quotationfilterdd.value !== '') {
      quotation_filter();
    }

    // Rearrange table rows so "Open" status rows are at the top
    function sortQuotationRowsByStatusOpen() {
        var table = document.querySelector('.quotation-table');
        if (!table) return;
        var tbody = table.querySelector('tbody');
        if (!tbody) return;

        var rows = Array.from(tbody.querySelectorAll('tr'));
        var openRows = [];
        var otherRows = [];

        rows.forEach(function(row) {
            var statusCell = row.querySelectorAll('td')[7];
            if (statusCell && statusCell.textContent.trim().toLowerCase() === 'open') {
                openRows.push(row);
            } else {
                otherRows.push(row);
            }
        });

        // Clear tbody and append sorted rows
        tbody.innerHTML = '';
        openRows.concat(otherRows).forEach(function(row) {
            tbody.appendChild(row);
        });
    }

    sortQuotationRowsByStatusOpen();
});

function confirmDelete() {
        return confirm("Are you sure you want to delete ?");
    }



</script>
</html>