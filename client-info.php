<?php
// create an empty array for the list or errors
$errors = array();

// Include necessary libraries and functions
require "../../includes/proper-finish-db.php";
include "../../includes/header.php";

// Get item id and username
$client_id = $_GET['guid'];

// Get the client information from the guid
$query = "SELECT * FROM `Clients` WHERE `Id` = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$client_id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC); 

$id = $client['Id'];
$name = $client['Name'];
$number = $client['Number'];
$email = $client['Email'];
$address = $client['Address'];
$latitude = $client['Latitude'];
$longitude = $client['Longitude'];
$package = $client['Package'];
$etc = $client['ETC'];
$notes = $client['Notes'];
$wash = $client['Wash'];
$sand = $client['Sand'];
$stain = $client['Stain'];

// get informaiton of all clients for the map view
$query = "SELECT * FROM `Clients`";
$stmt = $pdo->prepare($query);
$stmt->execute();
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all rows into an array

// When the user has updated the progress
if (isset($_POST['submit'])) {
    //status in words
    function status($process) {
        if ($process == '0') {
            $process = "not-started";
        }
        elseif ($process == '1') {
            $process = "in-progress";
        }
        elseif ($process == '2') {
            $process = "complete";
        }
        return $process;
    }

    // Get the previous data
    $prev_wash = $wash;
    $prev_sand = $sand;
    $prev_stain = $stain;

    // Get the form data
    if ($package == "Wash" || $package == "Maintenance" || $package == "Refinishing") {
        $wash = status($_POST['wash']) ?? null;
    }
    if ($package == "Refinishing" || $package == "Sand") {
        $sand = status($_POST['sand']) ?? null;
    }
    if ($package == "Stain" || $package == "Refinishing" || $package == "Maintenance") {
        $stain = status($_POST['stain']) ?? null;
    }   

    // update to database
    $query = "UPDATE `Clients` SET `Wash` = ?, `Sand` = ?, `Stain` = ? WHERE `Id` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$wash, $sand, $stain, $client_id]); 

    // Send automatic Email
    // Collect form data
    $sendEmail = isset($_POST['sendEmail']);

    if ($sendEmail) {
        function sendStatusEmail($emailto, $name, $message) {
            $emailfrom = 'support@olivervanrossem.com';
            $fromname = 'Proper Finish';
            $subject = 'Project Status';
        
            $headers = 
                'Return-Path: ' . $emailfrom . "\r\n" . 
                'From: ' . $fromname . ' <' . $emailfrom . '>' . "\r\n" . 
                'X-Priority: 3' . "\r\n" . 
                'X-Mailer: PHP ' . phpversion() .  "\r\n" . 
                'Reply-To: ' . $fromname . ' <' . $emailfrom . '>' . "\r\n" .
                'MIME-Version: 1.0' . "\r\n" . 
                'Content-Transfer-Encoding: 8bit' . "\r\n" . 
                'Content-Type: text/plain; charset=UTF-8' . "\r\n";
            
            $params = '-f ' . $emailfrom;
            
            return mail($emailto, $subject, $message, $headers, $params);
        }
    
        if ($prev_wash !== $wash && $wash !== 'not-started') {
            $message = "The washing process is $wash";
            sendStatusEmail($email, $name, $message);
        }
        
        if ($prev_sand !== $sand && $sand !== 'not-started') {
            $message = "The sanding process is $sand";
            sendStatusEmail($email, $name, $message);
        }
        
        if ($prev_stain !== $stain && $stain !== 'not-started') {
            $message = "The staining process is $stain";
            sendStatusEmail($email, $name, $message);
        }
    }
}

// Handle Photo uploads
// Set the target directory for uploads
$target_dir = "client-images/";

if(isset($_POST["upload"])) {
    // Loop through each uploaded file
    foreach ($_FILES['file']['tmp_name'] as $index => $tmp_name) {
        // Get the file extension
        $imageFileType = strtolower(pathinfo($_FILES["file"]["name"][$index], PATHINFO_EXTENSION));

        // Find all files that match the pattern "client_id-*.extension"
        $existing_images = glob($target_dir . $id . '-*.{jpg,jpeg,png,gif}', GLOB_BRACE);

        // Initialize the highest photo number to 0
        $highest_photo_number = 0;

        // Loop through each existing image to find the highest photo number
        foreach ($existing_images as $image) {
            // Extract the photo number from the filename using a regular expression
            if (preg_match('/' . $id . '-(\d+)\.[a-zA-Z]+$/', $image, $matches)) {
                $photo_number = (int)$matches[1];
                // Update the highest photo number if the current one is greater
                if ($photo_number > $highest_photo_number) {
                    $highest_photo_number = $photo_number;
                }
            }
        }

        // The next photo number should be the highest one found + 1
        $next_photo_number = $highest_photo_number + 1;

        // Construct the target file path
        $target_file = $target_dir . $id . '-' . $next_photo_number . '.' . $imageFileType;

        $uploadOk = 1;

        // Check if the file is an actual image
        $check = getimagesize($tmp_name);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $errors['file-image'] = true;
            $uploadOk = 0;
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            $errors['file-exists'] = true;
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $errors['file-format'] = true;
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $errors['file-upload'] = true;
        }
        // If everything is ok, try to upload the file
        else {
            if (!move_uploaded_file($tmp_name, $target_file)) {
                $errors['file-upload'] = true;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/client.css">
    <script defer src="scripts/main.js"></script>
    <title>Client Detail</title>
</head>

<body>
    <div class="container">
        <div id="header">
            <div id="left-links">
                <a href="main.php"><img src="images/home.svg" alt="Home"></a>
            </div>

            <div id="right-links">
                <a href="edit-client.php?guid=<?= $client_id ?>"><img src="images/edit.svg" alt="Edit"></a>
                <a id="delete-client" href="delete-client.php?guid=<?= $client_id ?>" onclick="confirmDeletion(event, this.href)">
                    <img src="images/trash.svg" alt="Delete">
                </a>
            </div>
        </div>

        <img src="images/logo.jpeg" alt="Proper Finish" class="logo">
        <h1><?= $name ?></h1>
        <h2><?= $package ?> Package</h2>

        <div class="contact-info">
            <p>Email: <a href="mailto:<?= $email ?>"><?= $email ?></a></p>
            <p>Phone: <a href="tel:+1<?= $number ?>"><?= $number ?></a></p>
        </div>

        <hr>

        <form id="form-progress" method="post" onsubmit="return confirmSubmission()">
            <h3>Progress</h3>

            <div class="progress-bar">
                <?php if ($package == "Wash" || $package == "Maintenance" || $package == "Refinishing"): ?>
                    <select id="first-select" class="progress-segment" name="wash">
                        <option value="0" class="not-started" <?= $wash == 'not-started' ? 'selected' : '' ?>>Not Started</option>
                        <option value="1" class="in-progress" <?= $wash == 'in-progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="2" class="complete" <?= $wash == 'complete' ? 'selected' : '' ?>>Complete</option>
                    </select>
                <?php endif; ?>

                <?php if ($package == "Refinishing" || $package == "Sand"): ?>
                    <select id="second-select" class="progress-segment" name="sand">
                        <option value="0" class="not-started" <?= $sand == 'not-started' ? 'selected' : '' ?>>Not Started</option>
                        <option value="1" class="in-progress" <?= $sand == 'in-progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="2" class="complete" <?= $sand == 'complete' ? 'selected' : '' ?>>Complete</option>
                    </select>
                <?php endif; ?>

                <?php if ($package == "Stain" || $package == 'Refinishing' || $package == 'Maintenance'): ?>
                    <select id="third-select" class="progress-segment" name="stain">
                        <option value="0" class="not-started" <?= $stain == 'not-started' ? 'selected' : '' ?>>Not Started</option>
                        <option value="1" class="in-progress" <?= $stain == 'in-progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="2" class="complete" <?= $stain == 'complete' ? 'selected' : '' ?>>Complete</option>
                    </select>
                <?php endif; ?>
            </div>

            <?php if ($package == "Maintenance") : ?>
                <script src="scripts/maintenance.js"></script>
            <?php else : ?>
                <script src="scripts/refinishing.js"></script>
            <?php endif; ?>

            <div class="progress-legend">
                <?php if ($package == 'Maintenance' || $package == 'Refinishing' || $package == 'Wash'): ?>
                    <div class="legend-item">
                        <img src="images/droplet.svg" width="30px">
                        <p>Wash</p>
                    </div>
                <?php endif; ?>

                <?php if ($package == 'Refinishing' || $package == 'Sand'): ?>
                    <div class="legend-item">
                        <img src="images/tool.svg" width="30px">
                        <p>Sand</p>
                    </div>
                <?php endif; ?>

                <?php if($package == 'Maintenance' || $package == 'Refinishing' || $package == 'Stain'): ?>
                    <div class="legend-item">
                        <img src="images/paint.svg" width="30px">
                        <p>Stain</p>
                    </div>
                <?php endif; ?>
            </div>

            <div style="text-align: center;">
                <input type="checkbox" id="sendEmail" name="sendEmail">
                <label for="sendEmail">Send automatic email</label>
            </div>

            <button type="submit" name="submit" class="button">Update Progress</button>
        </form>

        <?php
        // Converts the date into a better format 
        $newDate = date("m-d-Y", strtotime($etc));
        ?>
        <p id="etc"><b>ETC:</b><?= $newDate ?></p>

        <?php
        $encoded_address = urlencode($address);
        $google_maps_url = "https://www.google.com/maps/search/?api=1&query=$encoded_address";
        ?>
        <a href="<?= $google_maps_url ?>" target="_blank">
            <div id="address">
                <img src="images/map-pin.svg">
                <?= $address ?>
            </div>
        </a>

        <div id="googleMap" style="width:100%;height:400px;"></div>

        <script>
            function myMap() {
                var mapProp = {
                    center: new google.maps.LatLng(<?= $latitude ?>, <?= $longitude ?>),
                    zoom: 12.5,
                    streetViewControl: false,
                    mapTypeControl: false,
                    fullscreenControl: false,
                    zoomControl: false,
                    rotateControl: false,
                    scaleControl: false,
                };
                var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);

                var myCenter = new google.maps.LatLng(<?= $latitude ?>, <?= $longitude ?>); // Define the marker position
                var marker = new google.maps.Marker({ position: myCenter });

                marker.setMap(map);

                var circle = new google.maps.Circle({
                    strokeColor: "#FF0000", // Outline color of the circle
                    strokeOpacity: 0.8,     // Outline opacity (0 is fully transparent, 1 is fully opaque)
                    strokeWeight: 2,        // Outline thickness
                    fillOpacity: 0.35,      // Fill opacity (0 is fully transparent, 1 is fully opaque)
                    map: map,
                    center: myCenter,
                    radius: 3000            // Radius of the circle in meters
                });

                <?php 
                function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
                    // Convert from degrees to radians
                    $latFrom = deg2rad($latitudeFrom);
                    $lonFrom = deg2rad($longitudeFrom);
                    $latTo = deg2rad($latitudeTo);
                    $lonTo = deg2rad($longitudeTo);

                    $latDelta = $latTo - $latFrom;
                    $lonDelta = $lonTo - $lonFrom;

                    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                    cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
                    return $angle * $earthRadius;
                }

                foreach ($clients as $client1) {
                    $guid = htmlspecialchars($client1['Id']);
                    $c_lat = htmlspecialchars($client1['Latitude']);
                    $c_lon = htmlspecialchars($client1['Longitude']);
                    $c_wash = htmlspecialchars($client1['Wash']);
                    $c_sand = htmlspecialchars($client1['Sand']);
                    $c_stain = htmlspecialchars($client1['Stain']);
                    $c_package = htmlspecialchars($client1['Package']);
                    $distance = haversineGreatCircleDistance($latitude, $longitude, $c_lat, $c_lon);

                    if ($distance <= 3000 && $id != $guid) {
                        ?>

                        var clientCenter = new google.maps.LatLng(<?= $c_lat ?>, <?= $c_lon ?>); // Define the marker position
                        var icon = null;

                        <?php
                        if ($c_wash == 'not-started' || $c_wash == 'in-progress') {
                            ?>
                            icon = 'images/wash-pin.svg';
                            <?php
                        } elseif ($c_sand == 'in-progress' || 
                            ($c_wash == 'complete' && $c_sand == 'not-started') || 
                            ($c_package == 'Sand' && $c_sand == 'not-started')) {
                            ?>
                            icon = 'images/sand-pin.svg';
                            <?php
                        } elseif ($c_stain == 'in-progress' || 
                            ($c_sand == 'complete' && $c_stain == 'not-started') || 
                            ($c_package == 'Maintenance' && $c_wash == 'complete' && $c_stain == 'not-started') || 
                            ($c_package == 'Stain' && $c_stain == 'not-started')) {
                            ?>
                            icon = 'images/stain-pin.svg';
                            <?php
                        } else {
                            ?>
                            icon = null; // This ensures no marker is placed if criteria not met
                            <?php
                        }
                        ?>

                        if (icon) {
                            var marker = new google.maps.Marker({
                                position: clientCenter,
                                icon: icon,
                            });

                            // Add click event listener to the marker
                            google.maps.event.addListener(marker, 'click', function() {
                                // Redirect to client detail page when marker is clicked
                                window.location.href = 'client-info.php?guid=<?= $guid ?>';
                            });

                            marker.setMap(map);
                        }

                        <?php
                    }
                }
                ?>
            }
        </script>

        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAyT5RWRCu7d6yP0GKrS0UK-cpqL_nwgEk&callback=myMap"></script>

        <h3>Approximate Pricing</h3>
        <table>
            <tr>
                <th>Name</th>
                <th>Factor</th>
                <th>Price</th>
            </tr>
            <tr>
                <td>Size</td>
                <td>53.4 ft<sup>2</sup></td>
                <td>$4330</td>
            </tr>
            <tr>
                <td>Solid Stain</td>
                <td>2 cans</td>
                <td>$95</td>
            </tr>
            <tr>
                <td>Drum sander</td>
                <td>5 hours</td>
                <td>$250</td>
            </tr>
            <tr>
                <th>Total</th>
                <td></td>
                <th>$4675</th>
            </tr>
        </table>

        <h3>Notes</h3>
        <?php
        // Split the text by " - " while preserving the hyphen in each item
        $items = explode('-', $notes);

        // Initialize an empty string to hold the formatted list
        $formatted_text = '<ul>'; // Start the unordered list

        // Loop through each item, trim it, and add it as a list item
        foreach ($items as $item) {
            $item = trim($item); // Trim any whitespace around the item
            if (!empty($item)) {
                $formatted_text .= '<li>' . $item . '</li>';
            }
        }

        $formatted_text .= '</ul>'; // Close the unordered list

        // Output the formatted list
        echo $formatted_text;
        ?>

        <h3>Photos</h3>
        <form method="post" enctype="multipart/form-data">
            <div class="photo-grid">
                <?php
                $images = glob($target_dir . $id . '-*.{jpg,jpeg,png,gif}', GLOB_BRACE);

                if (count($images) > 0) {
                    foreach ($images as $index => $image) {
                        echo "<div class='photo' id='photo_$index'>";
                        echo "<a href='$image' target='_blank'>";
                        echo "<img src='$image'>";
                        echo "</a>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No images found</p>";
                }
                ?>
            </div>
        </form>

        <form method="post" enctype="multipart/form-data">
            <label for="file">Choose Images:</label>
            <input type="file" id="file" name="file[]" accept="image/jpeg" multiple required><br>
            <span class="error <?= !isset($errors['file-image']) ? 'hidden' : '' ?>">File is not an image.</span>
            <span class="error <?= !isset($errors['file-exists']) ? 'hidden' : '' ?>">File already exists.</span>
            <span class="error <?= !isset($errors['file-format']) ? 'hidden' : '' ?>">File is not in the correct format.</span>
            <span class="error <?= !isset($errors['file-upload']) ? 'hidden' : '' ?>">File failed to upload.</span>

            <button type="submit" name="upload" class="button">Upload Images</button>
        </form>
    </div>
</body>
</html>