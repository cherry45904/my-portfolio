<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    redirect('../pages/login.php');
}

// Check if hotel_id is provided
if (!isset($_GET['hotel_id'])) {
    redirect('hotels.php');
}

$hotel_id = (int)$_GET['hotel_id'];
$errors = [];
$success = false;

// Get hotel details
$sql = "SELECT * FROM hotels WHERE hotel_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();
$hotel = $result->fetch_assoc();

if (!$hotel) {
    redirect('hotels.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $guests = (int)($_POST['guests'] ?? 1);
    $special_requests = $_POST['special_requests'] ?? '';
    
    // Validate inputs
    if (empty($check_in)) {
        $errors['check_in'] = 'Check-in date is required';
    } elseif (strtotime($check_in) < strtotime('today')) {
        $errors['check_in'] = 'Check-in date cannot be in the past';
    }
    
    if (empty($check_out)) {
        $errors['check_out'] = 'Check-out date is required';
    } elseif ($check_out <= $check_in) {
        $errors['check_out'] = 'Check-out date must be after check-in date';
    }
    
    if ($guests < 1) {
        $errors['guests'] = 'Number of guests must be at least 1';
    }
    
    // If no errors, process booking
    if (empty($errors)) {
        $sql = "INSERT INTO hotel_bookings (user_id, hotel_id, check_in, check_out, guests, special_requests) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissis", $_SESSION['user_id'], $hotel_id, $check_in, $check_out, $guests, $special_requests);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors['general'] = 'Booking failed. Please try again.';
        }
    }
}
?>

<div class="hotel-booking-container">
    <h2>Book <?php echo htmlspecialchars($hotel['name']); ?></h2>
    
    <div class="booking-details">
        <div class="hotel-info">
            <img src="../assets/images/<?php echo htmlspecialchars($hotel['thumbnail']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
            <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
            <p><?php echo htmlspecialchars($hotel['address']); ?></p>
            <p>Price Range: <?php echo htmlspecialchars($hotel['price_range']); ?></p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Booking successful! <a href="my_bookings.php">View your bookings</a>.
            </div>
        <?php else: ?>
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
            <?php endif; ?>
            
            <form action="book_hotel.php?hotel_id=<?php echo $hotel_id; ?>" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="check_in">Check-in Date</label>
                        <input type="date" id="check_in" name="check_in" value="<?php echo htmlspecialchars($_POST['check_in'] ?? ''); ?>">
                        <?php if (isset($errors['check_in'])): ?>
                            <span class="error"><?php echo $errors['check_in']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="check_out">Check-out Date</label>
                        <input type="date" id="check_out" name="check_out" value="<?php echo htmlspecialchars($_POST['check_out'] ?? ''); ?>">
                        <?php if (isset($errors['check_out'])): ?>
                            <span class="error"><?php echo $errors['check_out']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="guests">Number of Guests</label>
                    <input type="number" id="guests" name="guests" min="1" value="<?php echo htmlspecialchars($_POST['guests'] ?? 1); ?>">
                    <?php if (isset($errors['guests'])): ?>
                        <span class="error"><?php echo $errors['guests']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="special_requests">Special Requests (Optional)</label>
                    <textarea id="special_requests" name="special_requests"><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Confirm Booking</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
// Set minimum dates for check-in and check-out
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('check_in').min = today;
    
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    checkInInput.addEventListener('change', function() {
        checkOutInput.min = this.value;
        if (checkOutInput.value && checkOutInput.value < this.value) {
            checkOutInput.value = '';
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>