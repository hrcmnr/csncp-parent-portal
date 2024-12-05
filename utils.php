<?php
function generateRandomPassword($length = 10) {
    return bin2hex(random_bytes($length / 2)); // Generates a random password
}
?>