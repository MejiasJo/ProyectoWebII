<?php
/* ================== CRUD USUARIO ================== */

function insertUsuario($conn, $nombre, $telefono, $email, $usuario, $contrasena, $privilegio){
    $sql = "INSERT INTO usuario (nombre, telefono, email, usuario, contrasena, privilegio) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nombre, $telefono, $email, $usuario, $contrasena, $privilegio);
    if ($stmt->execute()){
        $stmt->close();
        return true;
    }
    $stmt->close();
    return false;
}

function getUsuario($conn, $id){
    $sql = "SELECT u.id, u.nombre, u.telefono, u.email, u.usuario, u.privilegio, p.nombre AS privilegio_nombre 
            FROM usuario u 
            INNER JOIN privilegio p ON u.privilegio = p.id
            WHERE u.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result();
}

function getUsuarios($conn){
    $sql = "SELECT u.id, u.nombre, u.telefono, u.email, u.usuario, u.privilegio, p.nombre AS privilegio_nombre 
            FROM usuario u 
            INNER JOIN privilegio p ON u.privilegio = p.id
            ORDER BY u.id ASC";
    return $conn->query($sql);
}

function updateUsuario($conn, $id, $nombre, $telefono, $email, $usuario, $contrasena, $privilegio){
    $sql = "UPDATE usuario 
            SET nombre = ?, telefono = ?, email = ?, usuario = ?, contrasena = ?, privilegio = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssii", $nombre, $telefono, $email, $usuario, $contrasena, $privilegio, $id);
    if ($stmt->execute()){
        $stmt->close();
        return true;
    }
    $stmt->close();
    return false;
}

function deleteUsuario($conn, $id){
    $sql = "DELETE FROM usuario WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()){
        $stmt->close();
        return true;
    }
    $stmt->close();
    return false;
}

/* ================== LOGING USUARIO ================== */

function loginUsuario($conn, $usuario, $contrasena){
    $sql = "SELECT u.*, p.nombre AS privilegio_nombre 
            FROM usuario u
            INNER JOIN privilegio p ON u.privilegio = p.id
            WHERE u.usuario = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($contrasena, $row['contrasena'])) {
            return $row;
        }
    }
    return false;
}

function verificarContrasena($conn, $id, $contrasena){
    $sql = "SELECT contrasena FROM usuario WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return password_verify($contrasena, $row['contrasena']);
    }
    return false;
}

/* ================== CRUD PROPIEDADES ================== */

function insertPropiedad($conn, $id_tipo, $destacada, $titulo, $agente_id, $imagen, $descripcion, $ubicacion, $fecha_pub){
    $sql = "INSERT INTO propiedades (id_tipo, destacada, titulo, agente_id, imagen, descripcion, ubicacion, fecha_pub) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisissss", $id_tipo, $destacada, $titulo, $agente_id, $imagen, $descripcion, $ubicacion, $fecha_pub);
    if ($stmt->execute()){
        $stmt->close();
        return true;
    }
    $stmt->close();
    return false;
}

function getPropiedad($conn, $id){
    $sql = "SELECT pr.*, ta.nombre AS tipo_alquiler, u.nombre AS agente_nombre 
            FROM propiedades pr
            INNER JOIN tipo_alquiler ta ON pr.id_tipo = ta.id
            INNER JOIN usuario u ON pr.agente_id = u.id
            WHERE pr.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result();
}

function getPropiedades($conn){
    $sql = "SELECT pr.*, ta.nombre AS tipo_alquiler, u.nombre AS agente_nombre 
            FROM propiedades pr
            INNER JOIN tipo_alquiler ta ON pr.id_tipo = ta.id
            INNER JOIN usuario u ON pr.agente_id = u.id
            ORDER BY pr.id ASC";
    return $conn->query($sql);
}

function updatePropiedad($conn, $id, $id_tipo, $destacada, $titulo, $agente_id, $imagen, $descripcion, $ubicacion, $fecha_pub){
    $sql = "UPDATE propiedades 
            SET id_tipo = ?, destacada = ?, titulo = ?, agente_id = ?, imagen = ?, descripcion = ?, ubicacion = ?, fecha_pub = ?
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisissssi", $id_tipo, $destacada, $titulo, $agente_id, $imagen, $descripcion, $ubicacion, $fecha_pub, $id);
    if ($stmt->execute()){
        $stmt->close();
        return true;
    }
    $stmt->close();
    return false;
}

function deletePropiedad($conn, $id){
    $sql = "DELETE FROM propiedades WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()){
        $stmt->close();
        return true;
    }
    $stmt->close();
    return false;
}
?>
