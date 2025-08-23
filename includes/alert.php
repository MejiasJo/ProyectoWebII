<?php
function alertMenssage($message, $type = 'info') {
    $alertTypes = [
        'info' => 'alert-info',
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'danger' => 'alert-danger'
    ];
    
    $alertClass = isset($alertTypes[$type]) ? $alertTypes[$type] : $alertTypes['info'];
    
    echo "<div class='alert $alertClass' role='alert'>$message</div>";
}
?>