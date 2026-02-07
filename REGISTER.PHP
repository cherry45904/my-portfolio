<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

if (isLoggedIn()) {
    redirect('../pages/profile.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    
    // Validate inputs
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (usernameExists($username)) {
        $errors['username'] = 'Username already taken';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } elseif (emailExists($email)) {
        $errors['email'] = 'Email already registered';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    if (empty($full_name)) {
        $errors['full_name'] = 'Full name is required';
    }
    
    // If no errors, register user
    if (empty($errors)) {
        if (registerUser($username, $email, $password, $full_name)) {
            $success = true;
        } else {
            $errors['general'] = 'Registration failed. Please try again.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="register-container">
    <h2>Create Your Account</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            Registration successful! <a href="login.php">Login here</a>.
        </div>
    <?php else: ?>
        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                <?php if (isset($errors['username'])): ?>
                    <span class="error"><?php echo $errors['username']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <?php if (isset($errors['email'])): ?>
                    <span class="error"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
                <?php if (isset($errors['password'])): ?>
                    <span class="error"><?php echo $errors['password']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="error"><?php echo $errors['confirm_password']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                <?php if (isset($errors['full_name'])): ?>
                    <span class="error"><?php echo $errors['full_name']; ?></span>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>