<?php

function clean($data){
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url){
    header("Location: $url");
    exit();
}

// Generate ID formats like JC-2025-001
function generateCode($prefix, $id){
    return $prefix . "-" . date("Y") . "-" . str_pad($id, 3, "0", STR_PAD_LEFT);
}

?>
