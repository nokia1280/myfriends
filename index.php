<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Homepage</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="signup.php">Sign up</a></li>
                <li><a href="login.php">Log in</a></li>
            </ul>
        </nav>
    </header>

    <div class="container main">
        <div class="content-wrapper">
            <div class="image-container">
                <img src="images\friends.jpg">
            </div>
            <div class="text-content">
                <h1 style="color: #ffffff;">My Friend System</h1>
                <h3 style="color: #64a6d2;">Assignment Homepage</h3>
            </div>
        </div>
    </div>

    <div class="container student-details">
        <p>Student name: Nguyen Cong Hoang</p>
        <p>Student ID: 103426874</p>
        <p>Email address: hoangncseh201026@gmail.com</p>
    </div>

    <p class="container declaration">I declare that this assignment is my individual work. I have not worked collaboratively nor have I copied from any other student's work or from any other source.</p>
</body>

</html>

<?php
require_once("settings.php");

global $conn;

// Create tables if not existed
function createTables($conn, $table1, $table2)
{
    $sqlFriends = "CREATE TABLE IF NOT EXISTS $table1 (
        friend_id INT NOT NULL AUTO_INCREMENT,
        friend_email VARCHAR(50) NOT NULL,
        password VARCHAR(20) NOT NULL,
        profile_name VARCHAR(30) NOT NULL,
        date_started DATE NOT NULL,
        num_of_friends INT UNSIGNED,
        PRIMARY KEY (friend_id));";

    if (!mysqli_query($conn, $sqlFriends)) {
        die("Error creating Friends $table1: " . mysqli_error($conn));
    }

    $sqlMyFriends = "CREATE TABLE IF NOT EXISTS $table2 (
        friend_id1 INT NOT NULL,
        friend_id2 INT NOT NULL,
        PRIMARY KEY (friend_id1, friend_id2),
        CONSTRAINT fk_friend1 FOREIGN KEY (friend_id1) REFERENCES friends(friend_id),
        CONSTRAINT fk_friend2 FOREIGN KEY (friend_id2) REFERENCES friends(friend_id)
    );";

    if (!mysqli_query($conn, $sqlMyFriends)) {
        die("Error creating MyFriends $table2: " . mysqli_error($conn));
    }
}

// Add sample data to table
// Check if table is empty then put sample data
function addSampleData($conn, $table1, $table2)
{
    // friends table
    $getFriends = "SELECT COUNT(*) FROM $table1";
    $result = $conn->query($getFriends);
    $row = $result->fetch_row();

    if ($row[0] == 0) {
        $sqlAddFriends = "INSERT INTO $table1 (friend_email, password, profile_name, date_started, num_of_friends) VALUES
        ('random1@mail.com', 'password1', 'Cristiano Ronaldo', '2020-01-02', 5),
        ('random2@mail.com', 'password2', 'Lionel Messi', '2020-06-07', 5),
        ('random3@mail.com', 'password3', 'Gustavo Fring', '2021-03-04', 6),
        ('random4@mail.com', 'password4', 'Walter White', '2021-12-25', 7),
        ('random5@mail.com', 'password5', 'Batman', '2022-11-22', 7),
        ('random6@mail.com', 'password6', 'The Joker', '2022-04-15', 5),
        ('random7@mail.com', 'password7', 'Homelander', '2023-05-06', 4),
        ('random8@mail.com', 'password8', 'Omni-Man', '2023-10-31', 3),
        ('random9@mail.com', 'password9', 'Jesus', '2024-07-14', 2),
        ('random10@mail.com', 'password10', 'Zeus', '2024-01-01', 0);";

        if (mysqli_query($conn, $sqlAddFriends)) {
            echo "<p>Sample data added to table $table1 successfully.</p>";
        } else {
            echo "<p>Error adding sample data to table $table1: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p>Table $table1 already has data!</p>";
    }

    // myfriends table
    $getMyFriends = "SELECT COUNT(*) FROM $table2";
    $result = $conn->query($getMyFriends);
    $row = $result->fetch_row();

    if ($row[0] == 0) {
        $sqlAddMyFriends = "INSERT INTO $table2 (friend_id1, friend_id2) VALUES
        (1, 2), (1, 3), (1, 4), (1, 5), (1, 6),
        (2, 3), (2, 4), (2, 5), (2, 6), (2, 7),
        (3, 4), (3, 5), (3, 6), (3, 7), (3, 8),
        (4, 5), (4, 6), (4, 7), (4, 8), (4, 9),
        (5, 6), (5, 7), (5, 8), (5, 9), (6, 7);";

        if (mysqli_query($conn, $sqlAddMyFriends)) {
            echo "<p>Sample data added to table $table2 successfully.</p>";
        } else {
            echo "<p>Error adding sample data to table $table2: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p>Table $table2 already has data!</p>";
    }
}

createTables($conn, $table1, $table2);
addSampleData($conn, $table1, $table2);

mysqli_close($conn);
?>