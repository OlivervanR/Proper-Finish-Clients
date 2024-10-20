<?php
session_start();

// create an empty array for the list or errors
$errors = array();

// Include necessary libraries and functions
require "../../includes/proper-finish-db.php";
include "../../includes/header.php";

// Set the values from form
$name = $_POST['name'] ?? "";
$phone = $_POST['phone'] ?? "";
$email = $_POST['email'] ?? "";
$address = $_POST['address'] ?? "";
$latitude = $_POST['latitude'] ?? 0;
$longitude = $_POST['longitude'] ?? 0;
$package = $_POST['package'] ?? '0';
$repairs = $_POST['repairs'] ?? 0;
$etc = $_POST['etc'] ?? "";
$notes = $_POST['notes'] ?? "";
$size = $_POST['deck-size'] ?? 0;

if(isset($_POST['submit'])) {
    // Retrieves a client if their names match 
    $query = "SELECT * FROM `Clients` WHERE `Name` = ?";
    $stmt_name = $pdo->prepare($query);
    $stmt_name->execute([$name]);
    $client = $stmt_name->fetch(PDO::FETCH_ASSOC);

    if(strlen($name) === 0) {
        $errors['name'] = true;
    }    
    elseif($client) {
        $errors['name2'] = true;
    }

    if(strlen($phone) == 0) {
        $errors['phone'] = true;
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = true;
    }

    if (strlen($address) === 0) {
        $errors['address'] = true;
    }

    if ($package == '0') {
        $errors['package'] = true;
    }

    if (count($errors) === 0) {
        // Insert into database
        $query = "INSERT INTO `Clients` (`Name`, `Number`, `Email`, `Address`, `Latitude`, `Longitude`, `Package`, `ETC`, `Notes`, `Deck Size`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($query);
        $stmt_insert->execute([$name, $phone, $email, $address, $latitude, $longitude, $package, $etc, $notes, $size]);
        header("Location: main.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/main.css">
    <script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyAyT5RWRCu7d6yP0GKrS0UK-cpqL_nwgEk&v=<?= time() ?>&callback=initAutocomplete" async defer></script>
    <title>Add Client</title>
</head>
<body>
    <h1>Add Client</h1>
    <form id="form-add" method="post">
        <div>
            <label for="name">Name:</label>
            <input type="text" id="name1" name="name" value="<?= $name ?>">
            <span class="error <?= !isset($errors['name']) ? 'hidden' : '' ?>">Please enter a name.</span>
            <span class="error <?= !isset($errors['name2']) ? 'hidden' : '' ?>">Client already exists in the system.</span>
        </div>
        <div>
            <label for="phone">Phone Number:</label>
            <input type="number" id="phone" name="phone" value="<?= $phone ?>">
            <span class="error <?= !isset($errors['phone']) ? 'hidden' : '' ?>">Please enter a phone number.</span>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= $email ?>">
            <span class="error <?= !isset($errors['email']) ? 'hidden' : '' ?>">Please enter an email.</span>
        </div>
        <div>
            <label for="address">Address</label>
            <input type="text" id="address1" name="address" value="<?= $address ?>">
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">
            <span class="error <?= !isset($errors['address']) ? 'hidden' : '' ?>">Please enter an address.</span>
        </div>
        <div>
            <label for="package">Package:</label>
            <select name="package" id="package">
                <option value="0">Choose a package</option>
                <option value="Maintenance">Maintenance</option>
                <option value="Refinishing">Refinishing</option>
                <option value="Wash">Just Wash</option>
                <option value="Sand">Just Sand</option>
                <option value="Stain">Just Stain</option>
                <option value="No">None</option>
            </select>
            <span class="error <?= !isset($errors['package']) ? 'hidden' : '' ?>">Please choose a package.</span>
        </div>
        <div>
            <label for="repairs">Repairs? (yes/no):</label>
            <input type="checkbox" id="repairs" name="repairs" value="1">
        </div>
        <div>
            <label for="deck-size">Deck Size (Horizontal + Vertical in f<sup>2</sup>):</label>
            <input type="number" id="deck-size" name="deck-size" value="<?= $size ?>"></input>
        </div>
        <div>
            <label for="etc">Estimated Time of Completion(ETC): </label>
            <input type="date" id="etc" name="etc" value="<?= $etc ?>">
        </div>
        <div>
            <label for="notes">Notes:</label>
            <textarea id="notes" name="notes"><?= $notes ?></textarea>
        </div>
        <div>
            <button type="submit" name="submit" id="add-client">Add Client</button>
        </div>
    </form>
    <script>
        function initAutocomplete() {
            var searchInput = document.querySelector("#address1");
            var latitudeInput = document.querySelector("#latitude");
            var longitudeInput = document.querySelector("#longitude");

            console.log("initAutocomplete called");
            console.log("Search Input:", searchInput);
            console.log("Latitude Input:", latitudeInput);
            console.log("Longitude Input:", longitudeInput);

            if (!searchInput || !latitudeInput || !longitudeInput) {
                console.error("One or more input elements not found");
                return;
            }

            var autocomplete = new google.maps.places.Autocomplete(searchInput, { 
                types: ['geocode'],
                componentRestrictions: { country: 'ca' }
            });

            autocomplete.addListener('place_changed', function () { 
                var near_place = autocomplete.getPlace();

                if (!near_place.geometry) {
                    console.error("No geometry found for the place");
                    return;
                }

                var addressComponents = near_place.address_components;
                var street = "";
                var number = "";

                for (var i = 0; i < addressComponents.length; i++) {
                    var addressType = addressComponents[i].types[0];
                    if (addressType === "route") {
                        street = addressComponents[i]["long_name"];
                    } else if (addressType === "street_number") {
                        number = addressComponents[i]["long_name"];
                    }
                }

                searchInput.value = number + " " + street;
                latitudeInput.value = near_place.geometry.location.lat();
                longitudeInput.value = near_place.geometry.location.lng();

                console.log("Place Changed");
                console.log("Latitude:", latitudeInput.value);
                console.log("Longitude:", longitudeInput.value);
            });
        }
    </script>
</body>
</html>