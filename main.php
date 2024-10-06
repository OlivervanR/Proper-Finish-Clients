<?php
// Create an empty array for the list of errors
$errors = array();

// Include necessary libraries and functions
require "./includes/library.php";
include "../header.php";

// Select all clients from database
$query = "SELECT * FROM `Clients`";
$stmt = $pdo->prepare($query);
$stmt->execute();
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC); 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/main.css?">
    <title>Client Page</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
    <script defer src="scripts/main.js"></script>
</head>
<body>
    <img src="images/logo.jpeg" width="250 px">
    <input type="text" id="searchBar" placeholder="Search Client">
    <div class="horizontal">  
        <div>
            <label for="filter">Filter by...</label>
            <select name="filter" id="filter">
                <option value="name1">Name A-Z</option>
                <option value="name2">Name Z-A</option>
                <option value="date">Date</option>
                <option value="package1">Maintenance Package</option>
                <option value="package2">Refinishing Package</option>
            </select>
        </div>

        <a id="add-client" href="add-client.php">Add Client</a>
    </div>  

    <div id="clientList">
        <?php foreach ($clients as $client) { 
            $name = htmlspecialchars($client['Name']);
            $address = htmlspecialchars($client['Address']);
            $package = htmlspecialchars($client['Package']);
            $etc = htmlspecialchars($client['ETC']);
            $guid = htmlspecialchars($client['Id']);
            $wash = htmlspecialchars($client['Wash']);
            $sand = htmlspecialchars($client['Sand']);
            $stain = htmlspecialchars($client['Stain']);
            ?>
            <a href="client-info.php?guid=<?= $guid ?>&v=<?= time()?>" class="client-link">

                <?php if (empty($wash) && empty($sand) && empty($stain)) : ?>    
                    <div class="client">
                        <b class="name"><?= $name ?></b>
                        <div class="box" style="grid-row: span 2;">Not Started</div>

                <?php elseif ($wash !== 'in-progress' && $wash !== 'not-started' && $sand !== 'in-progress' && $sand !== 'not-started' && $stain !== 'in-progress' && $stain !== 'not-started'): ?>
                    <div class="client" style="background-color: #C9FFAF">
                        <b class="name"><?= $name ?></b>
                        <div class="box" style="grid-row: span 2;">Completed</div>   

                <?php else : ?>
                    <div class="client" style="background-color: #C7DFEC">
                        <b class="name"><?= $name ?></b>
                        <div class="progress" style="grid-row: span 2;">
                            <div class="box">
                                <?php if ($package == "Wash" || $package == "Maintenance" || $package == "Refinishing"):  ?>
                                    <span class="dot" id="<?= $wash ?>"></span>
                                    <label for="wash">Wash</label>
                                <?php endif; ?>
                            </div>
                            <div class="box">
                                <?php if ($package == "Refinishing" || $package == "Sand"): ?>
                                    <span class="dot" id="<?= $sand ?>"></span>
                                    <label for="sand">Sand</label>
                                <?php endif; ?>
                            </div>
                            <div class="box">
                                <?php if ($package == "Stain" || $package == 'Refinishing' || $package == 'Maintenance'): ?>
                                    <span class="dot" id="<?= $stain ?>"></span>
                                    <label for="stain">Stain</label>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="address"><?= $address ?></div>
                    <b class="package"><?= $package ?> Package</b>
                    <?php
                    // Converts the date into a better format 
                    $newDate = date("m-d-Y", strtotime($etc));
                    ?>
                    <div class="date"><?= $newDate ?></div>
                </div>
            </a>
        <?php } ?>
    </div>

    <script src="scripts/search-filter.js"></script>
</body>
</html>

