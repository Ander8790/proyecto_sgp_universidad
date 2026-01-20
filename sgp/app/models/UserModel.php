<?php
declare(strict_types=1);

class UserModel
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function findByEmail(string $email): ?array
    {
        // LEFT JOIN porque usuarios nuevos pueden NO tener datos_personales todavía
        $this->db->query("
            SELECT 
                u.id, 
                u.correo, 
                u.password, 
                u.rol_id as role_id, 
                u.requiere_cambio_clave,
                u.departamento_id,
                u.estado,
                COALESCE(CONCAT(dp.nombres, ' ', dp.apellidos), u.correo) as name
            FROM usuarios u
            LEFT JOIN datos_personales dp ON u.id = dp.usuario_id
            WHERE u.correo = :email 
            LIMIT 1
        ");
        $this->db->bind(':email', $email);
        $row = $this->db->single();
        return $row ? (array) $row : null;
    }

    public function findById(int $id): ?array
    {
        $this->db->query("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        return $row ? (array) $row : null;
    }

    public function create(array $data): bool
    {
        // Nueva arquitectura: Solo guardamos email, password y rol
        // Los nombres se guardarán en datos_personales (Nivel 2)
        // requiere_cambio_clave: 0 = registro público, 1 = creado por admin
        $this->db->query("
            INSERT INTO usuarios (correo, password, rol_id, estado, requiere_cambio_clave, departamento_id) 
            VALUES (:email, :pass, :role, 'activo', :req_cambio, :depto)
        ");
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':pass', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind(':role', $data['role_id']);
        $this->db->bind(':req_cambio', $data['requiere_cambio_clave'] ?? 1); // Default 1 para admin-created
        $this->db->bind(':depto', $data['departamento_id'] ?? null);
        
        return $this->db->execute();
    }
    
    // For getting last insert ID if needed, mainly for answers
    public function getLastId(): int 
    {
         return (int) $this->db->lastInsertId();
    }

    public function getSecurityQuestions(): array
    {
        $this->db->query("SELECT * FROM preguntas_seguridad WHERE activa = 1 ORDER BY RAND() LIMIT 3");
        $results = $this->db->resultSet();
        // Convert objects to arrays
        return array_map(fn($item) => (array) $item, $results);
    }

    public function saveSecurityAnswer(int $userId, int $questionId, string $answer): bool
    {
        // Normalize answer
        $hash = password_hash(strtolower(trim($answer)), PASSWORD_DEFAULT);
        $this->db->query("INSERT INTO usuarios_respuestas (usuario_id, pregunta_id, respuesta_hash) VALUES (:uid, :qid, :hash)");
        $this->db->bind(':uid', $userId);
        $this->db->bind(':qid', $questionId);
        $this->db->bind(':hash', $hash);
        
        return $this->db->execute();
    }

    public function getUserAnswers(int $userId): array
    {
         $sql = "SELECT q.pregunta as question, a.respuesta_hash as answer_hash, a.pregunta_id as question_id 
                 FROM usuarios_respuestas a 
                 JOIN preguntas_seguridad q ON a.pregunta_id = q.id 
                 WHERE a.usuario_id = :uid";
         $this->db->query($sql);
         $this->db->bind(':uid', $userId);
         $results = $this->db->resultSet();
         return array_map(fn($item) => (array) $item, $results);
    }

    public function updatePassword(int $userId, string $newPassword): bool
    {
        $this->db->query("UPDATE usuarios SET password = :pass, requiere_cambio_clave = 0 WHERE id = :uid");
        $this->db->bind(':pass', password_hash($newPassword, PASSWORD_DEFAULT));
        $this->db->bind(':uid', $userId);
        return $this->db->execute();
    }
    public function getAllUsers(): array
    {
        // Obtener nombres desde datos_personales (nueva arquitectura)
        $this->db->query("
            SELECT 
                u.id, 
                COALESCE(CONCAT(dp.nombres, ' ', dp.apellidos), u.correo) as name,
                u.correo as email, 
                u.rol_id as role_id, 
                r.nombre as role_name, 
                u.created_at,
                u.estado,
                d.nombre as departamento_nombre,
                CASE WHEN dp.id IS NOT NULL THEN 1 ELSE 0 END as perfil_completado
            FROM usuarios u 
            JOIN roles r ON u.rol_id = r.id 
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            LEFT JOIN datos_personales dp ON u.id = dp.usuario_id
            ORDER BY u.created_at DESC
        ");
        $results = $this->db->resultSet();
        return array_map(fn($item) => (array) $item, $results);
    }

    public function update(array $data): bool
    {
        // Nueva arquitectura: Solo actualizamos email, rol y opcionalmente password
        // Los nombres se actualizan en datos_personales
        if (!empty($data['password'])) {
            $this->db->query("
                UPDATE usuarios 
                SET correo = :email, rol_id = :role, password = :pass, departamento_id = :depto 
                WHERE id = :id
            ");
            $this->db->bind(':pass', password_hash($data['password'], PASSWORD_DEFAULT));
        } else {
            $this->db->query("
                UPDATE usuarios 
                SET correo = :email, rol_id = :role, departamento_id = :depto 
                WHERE id = :id
            ");
        }
        
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':role', $data['role_id']);
        $this->db->bind(':depto', $data['departamento_id'] ?? null);
        $this->db->bind(':id', $data['id']);
        
        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM usuarios WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // 🔒 NIVEL 2: VERIFICACIÓN DE PERFIL
    public function tienePerfil(int $usuario_id): bool
    {
        $this->db->query("SELECT COUNT(*) as total FROM datos_personales WHERE usuario_id = :uid");
        $this->db->bind(':uid', $usuario_id);
        $result = $this->db->single();
        return $result && $result->total > 0;
    }

    // 🔒 NIVEL 2: REGISTRO DE PERFIL
    public function registrarPerfil(array $datos): bool
    {
        $this->db->query("
            INSERT INTO datos_personales 
            (usuario_id, cedula, nombres, apellidos, telefono, direccion, genero, fecha_nacimiento) 
            VALUES 
            (:usuario_id, :cedula, :nombres, :apellidos, :telefono, :direccion, :genero, :fecha_nacimiento)
        ");
        
        $this->db->bind(':usuario_id', $datos['usuario_id']);
        $this->db->bind(':cedula', $datos['cedula']);
        $this->db->bind(':nombres', $datos['nombres']);
        $this->db->bind(':apellidos', $datos['apellidos']);
        $this->db->bind(':telefono', $datos['telefono']);
        $this->db->bind(':direccion', $datos['direccion']);
        $this->db->bind(':genero', $datos['genero']);
        $this->db->bind(':fecha_nacimiento', $datos['fecha_nacimiento']);
        
        return $this->db->execute();
    }

    // Check if Tutor has completed profile
    public function tieneDatosTutor(int $usuario_id): bool
    {
        $this->db->query("SELECT COUNT(*) as total FROM datos_tutor WHERE usuario_id = :uid");
        $this->db->bind(':uid', $usuario_id);
        $result = $this->db->single();
        return $result && $result->total > 0;
    }

    // Check if Pasante has completed profile
    public function tieneDatosPasante(int $usuario_id): bool
    {
        $this->db->query("SELECT COUNT(*) as total FROM datos_pasante WHERE usuario_id = :uid");
        $this->db->bind(':uid', $usuario_id);
        $result = $this->db->single();
        return $result && $result->total > 0;
    }

    // Generate temporary password for admin-created users
    public function generateTempPassword(string $cedula): string
    {
        return 'Sgp.' . $cedula;
    }

    // Create user with temporary password (Admin function)
    public function createWithTempPassword(array $data): bool
    {
        $tempPassword = $this->generateTempPassword($data['cedula']);
        
        $this->db->query("
            INSERT INTO usuarios (correo, password, rol_id, estado, requiere_cambio_clave) 
            VALUES (:email, :pass, :role, 'activo', 1)
        ");
        $this->db->bind(':email', $data['correo']);
        $this->db->bind(':pass', password_hash($tempPassword, PASSWORD_DEFAULT));
        $this->db->bind(':role', $data['rol_id']);
        
        return $this->db->execute();
    }

    // Reset user password to temporary (Admin function)
    public function resetToTempPassword(int $userId, string $cedula): bool
    {
        $tempPassword = $this->generateTempPassword($cedula);
        
        $this->db->query("
            UPDATE usuarios 
            SET password = :pass, requiere_cambio_clave = 1 
            WHERE id = :uid
        ");
        $this->db->bind(':pass', password_hash($tempPassword, PASSWORD_DEFAULT));
        $this->db->bind(':uid', $userId);
        
        return $this->db->execute();
    }
}
