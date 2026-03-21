<?php
$chars = json_decode(file_get_contents('https://teyvat-dev.vercel.app/api/characters'), true);
echo "Total characters: " . count($chars) . "\n";
echo "\nFirst character full structure:\n";
echo json_encode($chars[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
