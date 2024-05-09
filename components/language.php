<?php

// Define the translations for each language
$translations = array(
    'en_US' => array(
        "welcome_message" => "Welcome to our website!",
        "login_button" => "Login",
        "register_button" => "Register",
        "error_message" => "An error occurred. Please try again later."
    ),
    'fr_FR' => array(
        "welcome_message" => "Bienvenue sur notre site web !",
        "login_button" => "Connexion",
        "register_button" => "S'inscrire",
        "error_message" => "Une erreur s'est produite. Veuillez réessayer plus tard."
    ),
    // Add more languages and their translations as needed
);

// Specify the desired language
$language = 'en_US'; // Change this to the desired language

// Get the JSON data for the specified language
$jsonData = json_encode($translations[$language], JSON_PRETTY_PRINT);

// Specify the file path where you want to save the JSON file
$filePath = 'ecommerceWebsite' . $language . '.json'; // Replace this with the desired file path

// Write the JSON data to the file
if (file_put_contents($filePath, $jsonData) !== false) {
    echo "JSON file created successfully for language: $language";
} else {
    echo "Failed to create JSON file for language: $language";
}

?>
