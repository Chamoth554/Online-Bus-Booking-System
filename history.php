<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['utype'] != "Passenger") {
    header("Location: index.php");
    exit();
}
include 'inc/basic_template.php';
t_header("Bus Ticket Booking &mdash; History");
t_login_nav();
t_sidebar();
?>

<div class="container">
    <div class="popup" id="seatViewer"></div>
    <div class="loader text-center" id="wait"><img src="/img/bus-loader.gif" alt="Wait..."/></div>
    <h4>History</h4>
    <div class="table-con">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Bus Name</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Dep. Time</th>
                        <th>Arr. Time</th>
                        <th>J. Date</th>
                        <th>Cost</th>
						
                    </tr>
                </thead>
                <tbody>
                    <?php
                    require_once 'inc/database.php';
                    $conn = initDB();
                    $query = "SELECT t.id AS t_id, t.jdate, t.fare, t.seats, b.id AS bus_id, b.bname AS bus_name,";
                    $query .= "b.from_loc, b.to_loc, b.from_time, b.to_time FROM tickets t JOIN buses b ON t.bus_id = b.id";
                    $query .= " WHERE t.passenger_id=" . intval($_SESSION['user']['id']);
                    $res = $conn->query($query);
                    
                    if ($res->num_rows == 0) {
                        echo '
                        <tr>
                            <td colspan="8" class="text-center">No Tickets</td>
                        </tr>';
                    } else {
                        while ($row = $res->fetch_assoc()) {
                            echo '
                            <tr class="content">
                                <td>' . htmlspecialchars($row["t_id"]) . '</td>
                                <td>' . htmlspecialchars($row["bus_name"]) . '</td>
                                <td>' . htmlspecialchars($row["from_loc"]) . '</td>
                                <td>' . htmlspecialchars($row["to_loc"]) . '</td>
                                <td>' . htmlspecialchars($row["from_time"]) . '</td>
                                <td>' . htmlspecialchars($row["to_time"]) . '</td>
                                <td>' . htmlspecialchars($row["jdate"]) . '</td>
                                <td>' . htmlspecialchars("Rs.".$row["fare"]) . '</td>
								 
                            </tr>';
                        }
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(".content").click(function() {
        var ticket = $(this).find(">:first-child").html();
        $.ajax({
            url: "/inc/ajax.php?type=showseats&ticket=" + ticket,
            success: function(result) {
                setTimeout(function() {
                    $("#seatViewer").html(result);
                }, 1000);
            },
            beforeSend: function() {
                $("#wait").show();
            },
            complete: function() {
                setTimeout(function() {
                    $("#wait").hide();
                }, 1000);
            }
        });
        setTimeout(function() {
            $("#seatViewer").show();
        }, 1000);
    });
</script>

<?php
t_footer();
?>
