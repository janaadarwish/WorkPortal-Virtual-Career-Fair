<?php
$conn = mysqli_connect("localhost", "root", "", "fair_db");

$result = mysqli_query($conn, "SELECT * FROM events");
?>

<table border="1" width="100%">
  <tr>
    <th>Event Name</th>
    <th>Start</th>
    <th>End</th>
    <th>Status</th>
  </tr>

<?php while($row = mysqli_fetch_assoc($result)) { ?>

  <tr>
    <td><?php echo $row['event_name']; ?></td>
    <td><?php echo $row['start_time']; ?></td>
    <td><?php echo $row['end_time']; ?></td>
    <td><?php echo $row['status']; ?></td>
  </tr>

<?php } ?>

</table>