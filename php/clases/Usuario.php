<?php
// USUARIO.PHP  Clase Usuario (Hija de Modelo)

// CLASE 9: Programacion Orientada a Objetos (POO)

// HERENCIA 
//   La herencia permite que una clase HIJA herede atributos y metodos
//   de una clase PADRE. Esto evita duplicar codigo.

//   En nuestro proyecto:     Modelo (padre) -> Usuario (hija)

//   La palabra clave "extends" indica que Usuario HEREDA de Modelo.
//   Esto significa que Usuario tiene acceso a:
//     - $this->conexion (atributo protected del padre)
//     - $this->consultaPreparada() (metodo protected del padre)
//     - $this->obtenerResultado() (metodo public del padre)
//     - $this->ejecutar() (metodo public del padre)
//     - $this->obtenerUltimoId() (metodo public del padre)

// ENCAPSULACION:
//   Los atributos son PRIVATE (solo accesibles dentro de esta clase).
//   Para leerlos desde fuera, usamos GETTERS (metodos publicos).
//   Esto protege los datos internos del objeto.


// require_once: Incluir la clase padre (necesaria para extends)
require_once __DIR__ . '/Modelo.php';


// "extends Modelo": Usuario HEREDA de Modelo
// Usuario obtiene todo lo que tiene Modelo + sus propios metodos

class Usuario extends Modelo {

    // ATRIBUTOS PRIVADOS
    // private: SOLO accesibles dentro de esta clase (ni siquiera las hijas)
    // Esto es encapsulacion estricta

    private $id;
    private $nombre;
    private $email;


    // CONSTRUCTOR

    // Llama al constructor del padre con parent::__construct()
    // y establece la tabla como 'usuarios'

    public function __construct($conexion) {
        // parent::__construct(): Llama al constructor de la clase PADRE (Modelo)
        // Le pasamos la conexion y el nombre de la tabla
        parent::__construct($conexion, 'usuarios');
    }



    // METODOS PUBLICOS (la interfaz de la clase)
    // METODO: buscarPorNombre()
    
    // Busca un usuario por su nombre en la BD.
    // Usa consulta preparada del padre (herencia) para prevenir SQL injection.
    
    // Devuelve: Array con datos del usuario o null si no existe

    public function buscarPorNombre($nombre) {
        $sql = "SELECT id, nombre, email, password FROM usuarios WHERE nombre = ?";
        // $this->obtenerResultado() es un metodo HEREDADO de Modelo
        $resultados = $this->obtenerResultado($sql, 's', [$nombre]);

        if (count($resultados) > 0) {
            return $resultados[0]; // Devolver primera fila
        }
        return null;
    }


    // METODO: crear()
    
    // Crea un nuevo usuario en la BD con consulta preparada.
    // La contrasena ya debe venir hasheada (ver hashPassword).
    
    // Devuelve: El ID del nuevo usuario o false si falla

    public function crear($nombre, $email, $passwordHash) {
        $emailParaBD = !empty($email) ? $email : null;

        $sql = "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)";
        // $this->ejecutar() es un metodo HEREDADO de Modelo
        $resultado = $this->ejecutar($sql, 'sss', [$nombre, $emailParaBD, $passwordHash]);

        if ($resultado) {
            // $this->obtenerUltimoId() tambien es HEREDADO de Modelo
            $this->id = $this->obtenerUltimoId();
            $this->nombre = $nombre;
            $this->email = $email;
            return $this->id;
        }
        return false;
    }


    // METODO: verificarPassword()

    // Compara una contrasena en texto plano con el hash almacenado.
    // Usa password_verify() de PHP 

    // Devuelve: true si coincide, false si no

    public function verificarPassword($passwordTextoPlano, $hashAlmacenado) {
        // password_verify(): Extrae el salt del hash y compara
        // NO se puede comparar con == porque el hash incluye un salt aleatorio
        return password_verify($passwordTextoPlano, $hashAlmacenado);
    }


    // METODO ESTATICO: hashPassword()

    // static: Se puede llamar SIN crear un objeto.
    // Uso: Usuario::hashPassword('miClave123')

    // Genera un hash bcrypt seguro de la contrasena.
    // Devuelve: String con el hash (60+ caracteres)

    public static function hashPassword($password) {
        // password_hash(PASSWORD_DEFAULT): Usa bcrypt con salt automatico
        // Cada vez genera un hash diferente (por el salt aleatorio)
        return password_hash($password, PASSWORD_DEFAULT);
    }


    // METODO: iniciarSesion()
    
    // Crea la sesion PHP para el usuario.
    // Almacena datos en $_SESSION que persisten entre paginas.

    // Parametro: $datosUsuario - Array con id y nombre del usuario

    public function iniciarSesion($datosUsuario) {
        // session_regenerate_id(true): Genera nuevo ID de sesion
        // Previene ataques de session fixation (UD5 seccion 3.6)
        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $datosUsuario['id'];
        $_SESSION['usuario_nombre'] = $datosUsuario['nombre'];
        $_SESSION['logueado'] = true;
    }


    // GETTERS (acceso controlado a atributos privados)
   
    // Los getters son metodos publicos que permiten LEER
    // atributos privados desde fuera de la clase.
    // Esto es ENCAPSULACION: el exterior solo puede leer,
    // no modificar directamente los valores.

    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getEmail() {
        return $this->email;
    }


    // SETTERS (modificacion controlada)

    // Los setters permiten modificar atributos privados
    // con validacion. Ejemplo: no permitir nombre vacio.

    public function setNombre($nombre) {
        if (!empty($nombre) && strlen($nombre) >= 2) {
            $this->nombre = $nombre;
        }
    }
}