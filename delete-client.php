<?php 
session_start();

// Include necessary libraries and functions
require "../../includes/proper-finish-db.php";
include "../../includes/header.php";

$guid = $_GET['guid'];

$query = "DELETE FROM `Clients` WHERE `Id` = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$guid]);

header("Location:main.php");
exit();