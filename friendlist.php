<?php
session_start();
require_once("settings.php");

// Check if user is logged in
if (!isset($_SESSION["logged_in"])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION["email"];

// Get profile data
$sql = "SELECT * FROM friends WHERE friend_email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$num_of_friends = $user['num_of_friends'];

$stmt->close();

// Get friends from database
$sql = "SELECT f.* FROM friends f 
        JOIN myfriends mf ON (f.friend_id = mf.friend_id2 OR f.friend_id = mf.friend_id1) 
        WHERE (mf.friend_id1 = ? OR mf.friend_id2 = ?) 
        AND f.friend_id != ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $user['friend_id'], $user['friend_id'], $user['friend_id']);
$stmt->execute();
$result = $stmt->get_result();
$friends = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();

function deleteFriend($conn, $user_id, $friend_id)
{
    // Delete from myfriends table
    $sql = "DELETE FROM myfriends WHERE (friend_id1 = ? AND friend_id2 = ?) OR (friend_id1 = ? AND friend_id2 = ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
    $stmt->execute();

    $stmt->close();

    // Update friends count
    $sql = "UPDATE friends SET num_of_friends = num_of_friends - 1 WHERE friend_id IN (?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $friend_id);
    $stmt->execute();

    $stmt->close();
}

// Unfriend button
if (isset($_POST["unfriend"])) {
    deleteFriend($conn, $user['friend_id'], $_POST["friend_id"]);

    // Reload page
    header("Location: friendlist.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friend List</title>
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
            </ul>
        </nav>
    </header>

    <div class="container main">
        <h1>My Friend System</h1>
        <h3>My Friend List</h3>
        <br>
        <p class="subtitle"><?php echo htmlspecialchars($user["profile_name"]); ?>'s Friend List Page</p>
        <p>Total number of friends is <?php echo $num_of_friends; ?></p>

        <table>
            <tr>
                <th>Profile Name</th>
            </tr>
            <?php foreach ($friends as $friend) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($friend["profile_name"]); ?></td>
                    <td>
                        <form method="POST" action="friendlist.php">
                            <input type="hidden" name="friend_id" value="<?php echo $friend["friend_id"]; ?>">
                            <button class="logout" type="submit" name="unfriend">Unfriend</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <br>

        <div>
            <button><a href="friendadd.php">Add Friends</a></button>
            <button class="logout"><a href="logout.php">Log Out</a></button>
        </div>
    </div>
</body>

</html>

<?php
$conn->close();
?>