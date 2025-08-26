<?php
function getConfig($conn) {
    $sql = "SELECT * FROM configuracion WHERE id=1 LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row;
    }
    return [];
}

function updateConfig($conn, $data, $files) {
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

    $save = function($key) use ($files, $uploadDir) {
        if (!isset($files[$key]) || $files[$key]['error'] !== UPLOAD_ERR_OK) return null;
        $ext = strtolower(pathinfo($files[$key]['name'], PATHINFO_EXTENSION) ?: 'bin');
        $name = $key . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        if (!move_uploaded_file($files[$key]['tmp_name'], $uploadDir.$name)) return null;
        return $name;
    };

    $actual = getConfig($conn);

    $tema            = $data['tema'] ?? ($actual['tema'] ?? 'azul-amarillo-gris');
    $icono_principal = $save('icono_principal') ?? ($actual['icono_principal'] ?? null);
    $icono_blanco    = $save('icono_blanco')    ?? ($actual['icono_blanco'] ?? null);
    $banner_imagen   = $save('banner_imagen')   ?? ($actual['banner_imagen'] ?? null);
    $banner_mensaje  = trim($data['banner_mensaje'] ?? ($actual['banner_mensaje'] ?? ''));
    $quienes_somos   = trim($data['quienes_somos'] ?? ($actual['quienes_somos'] ?? ''));
    $quienes_img     = $save('quienes_img')     ?? ($actual['quienes_img'] ?? null);
    $facebook        = trim($data['facebook'] ?? ($actual['facebook'] ?? ''));
    $instagram       = trim($data['instagram'] ?? ($actual['instagram'] ?? ''));
    $tiktok          = trim($data['tiktok'] ?? ($actual['tiktok'] ?? ''));
    $direccion       = trim($data['direccion'] ?? ($actual['direccion'] ?? ''));
    $telefono        = trim($data['telefono'] ?? ($actual['telefono'] ?? ''));
    $email           = trim($data['email'] ?? ($actual['email'] ?? ''));

    $sql = "UPDATE configuracion SET
                tema=?, icono_principal=?, icono_blanco=?,
                banner_imagen=?, banner_mensaje=?,
                quienes_somos=?, quienes_img=?,
                facebook=?, instagram=?, tiktok=?,
                direccion=?, telefono=?, email=?
            WHERE id=1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssssssss",
        $tema, $icono_principal, $icono_blanco,
        $banner_imagen, $banner_mensaje,
        $quienes_somos, $quienes_img,
        $facebook, $instagram, $tiktok,
        $direccion, $telefono, $email
    );
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}


/* ================== CRUD USUARIO ================== */

function insertUsuario($conn, $nombre, $telefono, $email, $usuario, $contrasena, $privilegio, $primerAcceso){
    $sql = "INSERT INTO usuario (nombre, telefono, email, usuario, contrasena, privilegio, primerIngreso) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssii", $nombre, $telefono, $email, $usuario, $contrasena, $privilegio, $primerAcceso);
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

function updateUsuarioComplento($conn, $id, $nombre, $telefono, $email, $usuario, $contrasena, $privilegio, $primerAcceso){
    $sql = "UPDATE usuario 
            SET nombre = ?, telefono = ?, email = ?, usuario = ?, contrasena = ?, privilegio = ?, primerIngreso = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssiii", $nombre, $telefono, $email, $usuario, $contrasena, $privilegio, $primerAcceso, $id);
    if ($stmt->execute()){
        $stmt->close();
        return true;
    }
    $stmt->close();
    return false;
}

function updateUsuarioSinPass($conn, $id, $nombre, $telefono, $email, $usuario, $privilegio){
    $sql = "UPDATE usuario 
            SET nombre = ?, telefono = ?, email = ?, usuario = ?, privilegio = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $nombre, $telefono, $email, $usuario, $privilegio, $id);
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
    $sql = "SELECT * FROM usuario WHERE usuario = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row) {
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

function insertPropiedad($conn, $id_tipo, $destacada, $titulo, $agente_id, $imagen, $descripcion, $ubicacion, $fecha_pub, $precio){
    $sql = "INSERT INTO propiedades (id_tipo, destacada, titulo, agente_id, imagen, descripcion, ubicacion, fecha_pub, precio) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisissssi", $id_tipo, $destacada, $titulo, $agente_id, $imagen, $descripcion, $ubicacion, $fecha_pub, $precio);
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
            ORDER BY pr.id DESC";
    return $conn->query($sql);
}

function updatePropiedad($conn, $id, $id_tipo, $destacada, $titulo, $agente_id, $imagen, $descripcion, $ubicacion, $fecha_pub, $precio){
    $sql = "UPDATE propiedades 
            SET id_tipo = ?, destacada = ?, titulo = ?, agente_id = ?, imagen = ?, descripcion = ?, ubicacion = ?, fecha_pub = ?, precio = ?
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisissssii", $id_tipo, $destacada, $titulo, $agente_id, $imagen, $descripcion, $ubicacion, $fecha_pub,$precio, $id);
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

/* ================== Rol y Tipo Propiedades ================== */

function getCategorias($conn){
    $sql = "SELECT * FROM tipo_alquiler ORDER BY id ASC";
    return $conn->query($sql);
}

function getRoles($conn){
    $sql = "SELECT * FROM privilegio ORDER BY id ASC";
    return $conn->query($sql);
}
?>
