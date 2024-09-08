<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['utype'] != "Owner") {
    header("Location: index.php");
    exit();
}

$add = "";
if (isset($_POST['add'])) {
    require_once 'inc/database.php';
    $conn = initDB();

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO buses (bname, bus_no, from_loc, from_time, to_loc, to_time, fare, owner_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("ssssssdi", $_POST['bname'], $_POST['bus_no'], $_POST['from_loc'], $_POST['from_time'], $_POST['to_loc'], $_POST['to_time'], $_POST['fare'], $_SESSION['user']['id']);

        // Execute the statement
        if ($stmt->execute()) {
            $add = "ok";
        } else {
            $add = "Error: " . $stmt->error;
        }
        
        // Close the statement
        $stmt->close();
    } else {
        $add = "Error: " . $conn->error;
    }
    
    // Close the connection
    $conn->close();
}

include 'inc/basic_template.php';
t_header("Bus Ticket Booking");
t_login_nav();
t_owner_sidebar();
?>

<div class="modal" tabindex="-1" role="dialog" style="display: <?php echo ($_GET['act'] == 'add') ? 'block' : 'none'; ?>;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Bus</h5>
        <a href="my_buses.php"><button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button></a>
      </div>
      <form method="post" action="my_buses.php">
      <div class="modal-body">
        <p>
        <div class="form-group row">
            <div class="col-sm-3">Bus Name</div>
            <div class="col-sm-8">
                <input type="text" name="bname" class="form-control" required/>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-3">Bus No.</div>
            <div class="col-sm-8">
                <input type="text" name="bus_no" class="form-control" required/>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-3">From</div>
            <div class="col-sm-8">
                <input type="text" name="from_loc" class="form-control" id="inputFrom" required/>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-3">Departure Time</div>
            <div class="col-sm-8">
                <input type="time" name="from_time" class="form-control" required/>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-3">To</div>
            <div class="col-sm-8">
                <input type="text" name="to_loc" class="form-control" id="inputTo" required/>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-3">Arrival Time</div>
            <div class="col-sm-8">
                <input type="time" name="to_time" class="form-control" required/>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-3">Fare</div>
            <div class="col-sm-8">
                <input type="number" step="0.01" name="fare" class="form-control" required/>
            </div>
        </div>
        </p>
      </div>
      <div class="modal-footer">
        <input type="submit" class="btn btn-primary" value="Add" name="add"/>
        <a href="my_buses.php"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button></a>
      </div>
      </form>
    </div>
  </div>
</div>

<div class="container">
<?php
if ($add != "") {
    if ($add == "ok") {
        echo '<div class="alert alert-success">Bus Added <strong>Successfully</strong>!</div>';
    } else {
        echo '<div class="alert alert-danger"><strong>Error: </strong>' . $add . '</div>';
    }
}
?>
<div class="row mb-2">
    <h4 class="col-md-3">My Buses</h4>
    <div class="col-md-8 text-right ml-4">
        <a href="my_buses.php?act=add"><button type="button" class="btn btn-sm btn-primary">+ Add Bus</button></a>
    </div>
</div>

<table width="95%" class="table-con">
<tr class="head">
    <th>ID</th>
    <th>Bus Name</th>
    <th>Bus No.</th>
    <th>From</th>
    <th>Departure</th>
    <th>To</th>
    <th>Arrival</th>
    <th>Fare</th>
    <th>Status</th>
</tr>
<?php
require_once 'inc/database.php';
$conn = initDB();
$res = $conn->query("SELECT * FROM buses WHERE owner_id=" . $_SESSION['user']['id']);

if ($res->num_rows == 0) {
    echo '<tr class="row"><td colspan="9">No Bus</td></tr>';
} else {
    while ($row = $res->fetch_assoc()) {
        echo '
        <tr class="content">
            <td>' . $row["id"] . '</td>
            <td>' . $row["bname"] . '</td>
            <td>' . $row["bus_no"] . '</td>
            <td>' . $row["from_loc"] . '</td>
            <td>' . $row["from_time"] . '</td>
            <td>' . $row["to_loc"] . '</td>
            <td>' . $row["to_time"] . '</td>
            <td>' . $row["fare"] . '</td>
            <td>' . (($row["approved"]) ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>' ) . '</td>
        </tr>';
    }
}
$conn->close();
?>
</table>
</div>

<?php
t_footer();
?>
