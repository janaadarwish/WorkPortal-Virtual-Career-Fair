<?php
$conn = mysqli_connect("localhost","root","","fair_db");

if(isset($_POST['submit'])) {

    $name = $_POST['event_name'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];

    mysqli_query($conn,
      "INSERT INTO events (event_name, start_time, end_time)
       VALUES ('$name','$start','$end')"
    );

    header("Location: admin.php");
    echo "Saved Successfully!";
}
?>

[5/12/2026 6:32 AM] مِنّـةُ اللّٰه: <?php
$conn = mysqli_connect("localhost","root","","fair_db");

if(isset($_POST['submit'])) {

    $name = $_POST['event_name'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];

    mysqli_query($conn,
      "INSERT INTO events (event_name, start_time, end_time)
       VALUES ('$name','$start','$end')"
    );

    header("Location: admin.php");
}
?>


<style>
body {
  font-family: Arial, sans-serif;
  background: #f4f6f9;
}

/* container */
.table-container {
  width: 85%;
  margin: 40px auto;
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.08);
}

/* table */
.admin-table {
  width: 100%;
  border-collapse: collapse;
  overflow: hidden;
  border-radius: 10px;
}

/* header */
.admin-table thead {
  background: #2c3e50;
  color: #fff;
}

.admin-table th {
  padding: 14px;
  text-align: left;
  font-size: 14px;
}

/* rows */
.admin-table td {
  padding: 12px;
  border-bottom: 1px solid #eee;
}

/* hover effect */
.admin-table tr:hover {
  background: #f9fbff;
  transition: 0.2s;
}

/* inputs inside table */
.admin-table input {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  outline: none;
  font-size: 13px;
  transition: 0.2s;
}

.admin-table input:focus {
  border-color: #3498db;
  box-shadow: 0 0 5px rgba(52,152,219,0.3);
}

/* submit button */
.approve {
  background: #2ecc71;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  transition: 0.2s;
}

.approve:hover {
  background: #27ae60;
  transform: scale(1.05);
}

/* optional reject style */
.reject {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
}

.reject:hover {
  background: #c0392b;
}
</style>

<form method="POST" action="admin.php">

<div class="table-container">

  <table class="admin-table">

    <thead>
      <tr>
        <th>Event Name</th>
        <th>Start Time</th>
        <th>End Time</th>
        <th>Action</th>
      </tr>
    </thead>

    <tbody>

      <tr>
        <td>
          <input type="text" name="event_name" value="">
        </td>

        <td>
          <input type="datetime-local" name="start_time">
        </td>

        <td>
          <input type="datetime-local" name="end_time">
        </td>

        <td>
          <button type="submit" name="submit" class="approve">
            Submit
          </button>
        </td>
      </tr>

    </tbody>

  </table>

</div>

</form>