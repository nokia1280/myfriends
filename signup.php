<?php
session_start();
require_once("settings.php");

$errors = array();
$email = "";
$profile_name = "";

function validateAllInputs($fields, &$errors)
{
    foreach ($fields as $field) {
        if (empty($field)) {
            $errors[] = "All fields are required.";
            break;
        }
    }
}

function validateEmail($email, &$errors)
{
    global $conn;

    // Validate
    if (!preg_match("/^[a-zA-Z0-9.]+@[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/", $email)) {
        $errors[] = "Invalid email format.";

        // Check if email already exist on database
    } else {
        $email_check_query = "SELECT * FROM friends WHERE friend_email = ?";
        $stmt = $conn->prepare($email_check_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Email already exists.";
        }

        $stmt->close();
    }
}

function validateProfileName($profile_name, &$errors)
{
    if (!preg_match("/^[a-zA-Z]+(?:[-' ][a-zA-Z]+)*$/", $profile_name)) {
        $errors[] = "Profile name must contain only letters, spaces, hyphens, and cannot be blank.";
    }
}


function validatePassword($password, $confirm_password, &$errors)
{
    if (!preg_match("/^[a-zA-Z0-9]+$/", $password)) {
        $errors[] = "Password must contain only letters and numbers.";
    }

    if ($password != $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
}

// Main script
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $profile_name = trim($_POST["profile_name"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    validateAllInputs([$email, $profile_name, $password, $confirm_password], $errors);
    validateEmail($email, $errors);
    validateProfileName($profile_name, $errors);
    validatePassword($password, $confirm_password, $errors);

    // Add input if no error
    if (empty($errors)) {
        $conn = @mysqli_connect($host, $user, $pswd, $dbname) or die("Connection failed: " . mysqli_connect_error());

        $sql = "INSERT INTO friends (friend_email, password, profile_name, date_started, num_of_friends) VALUES (?, ?, ?, CURDATE(), 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $email, $password, $profile_name);

        if ($stmt->execute()) {
            $_SESSION["logged_in"] = true;
            $_SESSION["email"] = $email;
            header("Location: friendadd.php");
            exit();
        } else {
            $errors[] = "Error adding data to database: " . mysqli_error($conn);
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
        <h1>My Friend System</h1>
        <h3>Sign Up</h3>
        <br>
        <p class="subtitle">Create an account to start adding friends!</p>

        <form method="post" action="signup.php">
            <div class="form-group">
                <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Email">
            </div>

            <div class="form-group">
                <input type="text" id="profile_name" name="profile_name" value="<?php echo htmlspecialchars($profile_name); ?>" placeholder="Profile name">
            </div>

            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Password">
            </div>

            <div class="form-group">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password">
            </div>

            <button type="submit">Sign up</button>
        </form>

        <?php
        if (!empty($errors)) {
            echo "<ul class='error-list'>";
            foreach ($errors as $error) {
                echo "<li>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul>";
        }
        ?>
    </div>
</body>

</html>