<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';  // Database configuration

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['usertype'] == 'admin') {
        header("Location: dashboard.php");
    } elseif ($_SESSION['usertype'] == 'user') {
        header("Location: dashboardd.php");
    }
    exit();
}

// Clear incorrect inputs and retain valid ones
function retainValidInput($data, $key) {
    return isset($data[$key]) ? htmlspecialchars($data[$key]) : '';
}

// Registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);

    // Default usertype is 'user'
    $usertype = 'user';

    // Check password match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Check if email already exists
        $checkEmail = $conn->query("SELECT * FROM signup_table WHERE email='$username'");
        if ($checkEmail->num_rows > 0) {
            echo "<script>alert('Email is already registered!');</script>";
        } else {
            // Upload ID picture if provided
            $id_picture = null;
            if (!empty($_FILES['id_picture']['name'])) {
                $target_dir = "uploads/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                $id_picture = basename($_FILES['id_picture']['name']);
                $target_file = $target_dir . $id_picture;
                move_uploaded_file($_FILES['id_picture']['tmp_name'], $target_file);
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into DB
            $stmt = $conn->prepare("INSERT INTO signup_table (email, password, usertype, id_picture) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $hashed_password, $usertype, $id_picture);

            if ($stmt->execute()) {
                echo "<script>alert('Registration successful! You can now log in.');</script>";
            } else {
                echo "<script>alert('Error during registration.');</script>";
            }
            $stmt->close();
        }
    }
}

// Login (No verification check)
if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);

    $sql = "SELECT * FROM signup_table WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['usertype'] = $row['usertype']; 
            $_SESSION['name'] = isset($row['firstname'], $row['surname']) 
                                ? $row['firstname'] . ' ' . $row['surname'] 
                                : '';

            // Redirect based on usertype
            if ($row['usertype'] === 'admin') {
                header("Location: dashboard.php");
            } elseif ($row['usertype'] === 'user') {
                header("Location: dashboardd.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            echo "<script>alert('Invalid password');</script>";
        }
    } else {
        echo "<script>alert('No user found with this email');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <title>User Authentication</title>
</head>
<body>

<div class="container" id="container">
    <div class="form-container sign-up">
        <form action="index.php" method="POST" enctype="multipart/form-data">
            <h1>Register</h1>
            <input type="text" name="username" required placeholder="Username" value="<?php echo retainValidInput($_POST, 'username'); ?>">
            
            <div class="password-container">
                <input type="password" name="password" required placeholder="Password" id="password">
                <i class="fa-solid fa-eye" id="togglePassword"></i>
            </div>
            <div class="password-container">
                <input type="password" name="confirm_password" required placeholder="Confirm Password" id="confirm_password">
                <i class="fa-solid fa-eye" id="toggleConfirmPassword"></i>
            </div>

            <div class="button-container">
                <button type="submit" name="register">Register</button>
            </div>
        </form>
    </div>
    
    <div class="form-container sign-in">
        <form action="index.php" method="POST">
            <h1>Login</h1>
            <input type="text" name="email" required placeholder="Email" value="<?php echo retainValidInput($_POST, 'email'); ?>">
            <div class="loginpassword-container">
                <input type="password" name="password" required placeholder="Password" id="login_password">
                <i class="fa-solid fa-eye" id="toggleLoginPassword"></i>
            </div>
            <button type="submit" name="login">Login</button>
        </form>
    </div>

    <div class="toggle-container">
        <div class="toggle">
            <div class="toggle-panel toggle-left">
                <h1>Hello, Friend! Welcome Back!</h1>
                <p>Input the needed details to access Navigator</p>
                <button class="hidden" id="login">Sign In</button>
            </div>
            <div class="toggle-panel toggle-right">
                <h1>Welcome to Navigator!</h1>
                <p>Fill-up the needed details to access Navigator</p>
                <button class="hidden" id="register">Sign Up</button>
            </div>
        </div>
    </div>
</div>

<script>
    const container = document.getElementById('container');
    const registerBtn = document.getElementById('register');
    const loginBtn = document.getElementById('login');

    registerBtn.addEventListener('click', () => container.classList.add("active"));
    loginBtn.addEventListener('click', () => container.classList.remove("active"));

    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });

    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmPassword = document.getElementById('confirm_password');
    toggleConfirmPassword.addEventListener('click', function () {
        const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPassword.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });

    const toggleLoginPassword = document.getElementById('toggleLoginPassword');
    const loginPassword = document.getElementById('login_password');
    toggleLoginPassword.addEventListener('click', function () {
        const type = loginPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        loginPassword.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
</script>

</body>
</html>
