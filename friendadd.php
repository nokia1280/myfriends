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
$num_of_friends = $user["num_of_friends"];

$friends_per_page = 5;
$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
$offset = ($page - 1) * $friends_per_page;

// Get non-friends from database with pagination and mutual friend count
$sql = "SELECT f.*, 
        (SELECT COUNT(*) 
        FROM myfriends mf1 
        JOIN myfriends mf2 ON mf1.friend_id2 = mf2.friend_id2 
        WHERE mf1.friend_id1 = ? AND mf2.friend_id1 = f.friend_id) AS mutual_friends_count
        
        FROM friends f 
        WHERE f.friend_id != ? 
        AND f.friend_id NOT IN (
            SELECT mf.friend_id2 FROM myfriends mf WHERE mf.friend_id1 = ?
            UNION
            SELECT mf.friend_id1 FROM myfriends mf WHERE mf.friend_id2 = ?
        )
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iiiiii', $user['friend_id'], $user['friend_id'], $user['friend_id'], $user['friend_id'], $friends_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$friends = $result->fetch_all(MYSQLI_ASSOC);

// Get the total count of non-friends for pagination
$sql = "SELECT COUNT(*) as total FROM friends f 
        WHERE f.friend_id != ? 
        AND f.friend_id NOT IN (
            SELECT mf.friend_id2 FROM myfriends mf WHERE mf.friend_id1 = ?
            UNION
            SELECT mf.friend_id1 FROM myfriends mf WHERE mf.friend_id2 = ?
        )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user["friend_id"], $user["friend_id"], $user["friend_id"]);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_friends = $row["total"];
$total_pages = ceil($total_friends / $friends_per_page);

function addFriend($conn, $user_id, $friend_id)
{
    // Add to myfriends table
    $sql = "INSERT INTO myfriends (friend_id1, friend_id2) VALUES (?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $friend_id);
    $stmt->execute();

    // Update friends count
    $sql = "UPDATE friends SET num_of_friends = num_of_friends + 1 WHERE friend_id IN (?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $friend_id);
    $stmt->execute();
}

// Add friend button
if (isset($_POST["add_friend"])) {
    addFriend($conn, $user['friend_id'], $_POST["friend_id"]);

    // Reload page
    header("Location: friendadd.php?page=" . $page);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Friend</title>
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
                <th>Mutual Friends</th>
                <th>Action</th>
            </tr>
            <?php foreach ($friends as $friend) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($friend["profile_name"]); ?></td>
                    <td><?php echo $friend["mutual_friends_count"]; ?></td>
                    <td>
                        <form method="POST" action="friendadd.php?page=<?php echo $page; ?>">
                            <input type="hidden" name="friend_id" value="<?php echo $friend["friend_id"]; ?>">
                            <button type="submit" name="add_friend">Add friend</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <br>

        <div>
            <?php if ($page > 1) : ?>
                <a href="friendadd.php?page=<?php echo $page - 1; ?>">Previous</a>
            <?php endif; ?>

            <?php if ($page < $total_pages) : ?>
                <a href="friendadd.php?page=<?php echo $page + 1; ?>">Next</a>
            <?php endif; ?>
        </div>

        <br>

        <div>
            <button><a href="friendlist.php">Friend List</a></button>
            <button class="logout"><a href="logout.php">Log Out</a></button>
        </div>
    </div>
</body>

</html>

<?php
$conn->close();
?>