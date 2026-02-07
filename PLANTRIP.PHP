<?php
require_once '../includes/config.php';
require_once '../includes/header.php';

if (!isLoggedIn()) {
    redirect('../pages/login.php');
}

$errors = [];
$success = false;

// Get all destinations for dropdown
$destinations = [];
$sql = "SELECT destination_id, name FROM destinations ORDER BY name";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $destinations[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $selected_destinations = $_POST['destinations'] ?? [];
    
    // Validate inputs
    if (empty($title)) {
        $errors['title'] = 'Trip title is required';
    }
    
    if (empty($start_date)) {
        $errors['start_date'] = 'Start date is required';
    }
    
    if (empty($end_date)) {
        $errors['end_date'] = 'End date is required';
    } elseif ($end_date < $start_date) {
        $errors['end_date'] = 'End date must be after start date';
    }
    
    if (empty($selected_destinations)) {
        $errors['destinations'] = 'Select at least one destination';
    }
    
    // If no errors, save trip plan
    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert trip plan
            $sql = "INSERT INTO trip_plans (user_id, title, start_date, end_date, notes) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issss", $_SESSION['user_id'], $title, $start_date, $end_date, $notes);
            $stmt->execute();
            $plan_id = $conn->insert_id;
            
            // Insert plan destinations
            foreach ($selected_destinations as $dest_id => $visit_date) {
                if (!empty($visit_date)) {
                    $sql = "INSERT INTO plan_destinations (plan_id, destination_id, visit_date) 
                            VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iis", $plan_id, $dest_id, $visit_date);
                    $stmt->execute();
                }
            }
            
            // Commit transaction
            $conn->commit();
            $success = true;
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $errors['general'] = 'Error saving trip plan: ' . $e->getMessage();
        }
    }
}
?>

<div class="trip-planning-container">
    <h2>Plan Your Nepal Adventure</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            Trip plan created successfully! <a href="my_plans.php">View your plans</a>.
        </div>
    <?php else: ?>
        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
        <?php endif; ?>
        
        <form action="plan_trip.php" method="post">
            <div class="form-group">
                <label for="title">Trip Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                <?php if (isset($errors['title'])): ?>
                    <span class="error"><?php echo $errors['title']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
                    <?php if (isset($errors['start_date'])): ?>
                        <span class="error"><?php echo $errors['start_date']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                    <?php if (isset($errors['end_date'])): ?>
                        <span class="error"><?php echo $errors['end_date']; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes">Notes (Optional)</label>
                <textarea id="notes" name="notes"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Select Destinations</label>
                <?php if (isset($errors['destinations'])): ?>
                    <span class="error"><?php echo $errors['destinations']; ?></span>
                <?php endif; ?>
                
                <div class="destinations-list">
                    <?php foreach ($destinations as $destination): ?>
                        <div class="destination-checkbox">
                            <input type="checkbox" id="dest_<?php echo $destination['destination_id']; ?>" 
                                   name="destinations[<?php echo $destination['destination_id']; ?>]" 
                                   value="<?php echo isset($_POST['destinations'][$destination['destination_id']]) ? 
                                   htmlspecialchars($_POST['destinations'][$destination['destination_id']]) : ''; ?>">
                            <label for="dest_<?php echo $destination['destination_id']; ?>">
                                <?php echo htmlspecialchars($destination['name']); ?>
                            </label>
                            <input type="date" name="destinations[<?php echo $destination['destination_id']; ?>]" 
                                   class="visit-date" placeholder="Visit date" 
                                   value="<?php echo isset($_POST['destinations'][$destination['destination_id']]) ? 
                                   htmlspecialchars($_POST['destinations'][$destination['destination_id']]) : ''; ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Save Trip Plan</button>
        </form>
    <?php endif; ?>
</div>

<script>
// Enable date input only when checkbox is checked
document.querySelectorAll('.destination-checkbox input[type="checkbox"]').forEach(checkbox => {
    const dateInput = checkbox.parentElement.querySelector('.visit-date');
    
    checkbox.addEventListener('change', function() {
        dateInput.disabled = !this.checked;
        if (!this.checked) {
            dateInput.value = '';
        }
    });
    
    // Initialize disabled state
    dateInput.disabled = !checkbox.checked;
});
</script>

<?php require_once '../includes/footer.php'; ?>