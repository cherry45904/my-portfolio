<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<div class="hero-section">
    <h1>Discover the Hidden Treasures of Nepal</h1>
    <p>Explore breathtaking landscapes, ancient cultures, and unforgettable adventures</p>
    <a href="pages/destinations.php" class="btn btn-primary">Explore Destinations</a>
</div>

<div class="featured-destinations">
    <h2>Popular Destinations</h2>
    <div class="destination-grid">
        <?php
        $sql = "SELECT * FROM destinations ORDER BY RAND() LIMIT 4";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="destination-card">';
                echo '<img src="assets/images/' . htmlspecialchars($row['thumbnail']) . '" alt="' . htmlspecialchars($row['name']) . '">';
                echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                echo '<a href="pages/destination_detail.php?id=' . $row['destination_id'] . '" class="btn btn-secondary">View Details</a>';
                echo '</div>';
            }
        } else {
            echo '<p>No destinations found.</p>';
        }
        ?>
    </div>
</div>

<div class="user-testimonials">
    <h2>Traveler Experiences</h2>
    <div class="testimonial-slider">
        <?php
        $sql = "SELECT up.*, u.username, u.profile_pic, d.name AS destination_name 
                FROM user_posts up
                JOIN users u ON up.user_id = u.user_id
                LEFT JOIN destinations d ON up.destination_id = d.destination_id
                ORDER BY up.created_at DESC LIMIT 3";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="testimonial">';
                echo '<div class="user-info">';
                echo '<img src="assets/uploads/' . htmlspecialchars($row['profile_pic']) . '" alt="' . htmlspecialchars($row['username']) . '">';
                echo '<span>' . htmlspecialchars($row['username']) . '</span>';
                echo '</div>';
                echo '<div class="media">';
                if ($row['media_type'] == 'photo') {
                    echo '<img src="assets/uploads/' . htmlspecialchars($row['media_url']) . '" alt="Travel photo">';
                } else {
                    echo '<video controls><source src="assets/uploads/' . htmlspecialchars($row['media_url']) . '" type="video/mp4"></video>';
                }
                echo '</div>';
                echo '<p>' . htmlspecialchars($row['caption']) . '</p>';
                if ($row['destination_name']) {
                    echo '<small>Posted from ' . htmlspecialchars($row['destination_name']) . '</small>';
                }
                echo '</div>';
            }
        } else {
            echo '<p>No testimonials yet.</p>';
        }
        ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>