<?php
include('config.php');

// Establish a database connection using the constants from config.php
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the courier_data table
$courierSql = "SELECT customer_id, SUM(amount) AS total_amount FROM courier_data GROUP BY customer_id";
$courierResult = $conn->query($courierSql);

if (!$courierResult) {
    die("Error executing the courier_data query: " . $conn->error);
}

// Update total_amount in the customer_data table
while ($row = $courierResult->fetch_assoc()) {
    $customerId = $row['customer_id'];
    $totalAmount = $row['total_amount'];

    $updateSql = "UPDATE customer_data SET total_amount = $totalAmount WHERE id = $customerId";
    $updateResult = $conn->query($updateSql);

    if (!$updateResult) {
        die("Error updating total_amount in customer_data: " . $conn->error);
    }
}

// Fetch updated data from the customer_data table
$customerSql = "SELECT * FROM customer_data";
$customerResult = $conn->query($customerSql);

if (!$customerResult) {
    die("Error executing the customer_data query: " . $conn->error);
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icon.ico" type="image/x-icon" sizes="32x32">
    <title>Customers</title>
    <link rel="stylesheet" href="customers.css">
</head>

<body>
    <header>
        <div class="navbar">
            <div class="navbar-logo">
                <img src="logo.png" alt="Logo" />
            </div>
        </div>
    </header>
    <div class="btn-con">
        <a href="home.html">Home</a>
        <a href="dataentry.html">Add Entry</a>
        <div class="link"><a href="customers.php">Customers</a></div>
    </div>

    <div class="container">
        <div class="table-container">
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search by Name or Phone No">
            </div>
            <table id="data-table">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Name</th>
                        <th>Phone No</th>
                        <th>Address</th>
                        <th>Total Amount</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $customerResult->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td>{$row['name']}</td>";
                        echo "<td>{$row['phone_no']}</td>";
                        echo "<td>{$row['address']}</td>";
                        echo "<td>{$row['total_amount']}</td>";
                        echo "<td><a href='details.php?id={$row['id']}&name={$row['name']}' style='color: #ff0000; text-decoration: none;'>View Details</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        document.getElementById('searchInput').addEventListener('input', function() {
            var filter = this.value.toUpperCase();
            var tableRows = document.querySelectorAll('#data-table tbody tr');

            tableRows.forEach(function(row) {
                var nameCell = row.cells[1];
                var phoneCell = row.cells[2];

                var nameText = nameCell.textContent || nameCell.innerText;
                var phoneText = phoneCell.textContent || phoneCell.innerText;

                if (nameText.toUpperCase().indexOf(filter) > -1 || phoneText.indexOf(filter) > -1) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>