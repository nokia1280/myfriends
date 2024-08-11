<?php
session_start();
require_once("settings.php");

$errors = array();
$email = "";

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

        if ($result->num_rows == 0) {
            $errors[] = "Email does not exists.";
        }

        $stmt->close();
    }
}

function validatePassword($email, $password, &$errors)
{
    global $conn;

    $sql = "SELECT * FROM friends WHERE friend_email = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Verify password
    if ($row === null || $row["password"] !== $password) {
        $errors[] = "Incorrect password.";
    }

    $stmt->close();
}

// Main script
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    validateAllInputs([$email, $password], $errors);
    validateEmail($email, $errors);
    validatePassword($email, $password, $errors);

    // Login if no error
    if (empty($errors)) {
        $_SESSION["logged_in"] = true;
        $_SESSION["email"] = $email;
        header("Location: friendlist.php");
        exit();

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
    <title>Login</title>
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
        <h3>Log In</h3>
        <br>
        <p>Welcome back!</p>
        <form method="post" action="login.php">
            <div class="form-group">
                <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Email">
            </div>

            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Password">
            </div>

            <button type="submit">Log In</button>
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