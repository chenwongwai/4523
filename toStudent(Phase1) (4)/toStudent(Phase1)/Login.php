<?php
session_start();

$conn = new mysqli("localhost", "root", "", "projectdb");

if ($conn->connect_error) {
    die("Connection failed：" . $conn->connect_error);
}

$userType = $_POST['userType'];  
$username = $_POST['username'];
$password = $_POST['password'];

if ($userType === 'customer') {
    $sql = "SELECT * FROM customer WHERE cname = ? AND cpassword = ?";
} else {
    $sql = "SELECT * FROM staff WHERE sname = ? AND spassword = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $userType;

    if ($userType === 'customer') {
        header("Location: customer.php");
    } else {
        header("Location: staff.php");
    }
} else {
    echo "<script>alert('Login failed. Account or password is incorrect.'); window.location.href='index.php';</script>";
}

$stmt->close();
$conn->close();
?>