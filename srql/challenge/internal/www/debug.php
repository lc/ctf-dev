<?php
header('Content-Type: text/plain');
$servername = "172.18.0.4";
$username = "ctflol";
$password = "c7fL00l!";
$dbname = "randomcorp";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$secure = $_GET['name'];
$sql = "SELECT name FROM ".$secure;
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo $row['name'];
    }
} else {
    echo "no results for query: ".$sql;
}
$conn->close();
?>
