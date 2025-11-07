<?PHP
class SanitizarEntrada {

    
    // Sanitiza una cadena eliminando espacios y etiquetas HTML
    public static function limpiarCadena($cadena) {
        return trim(strip_tags($cadena));
    }

    // Sanitiza un correo electrónico
    public static function limpiarEmail($email) {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    // Sanitiza un entero
    public static function limpiarInt($int) {
        return filter_var($int, FILTER_SANITIZE_NUMBER_INT);
    }

}//SanitizarEntrada

//$nombre = "<b>Juan</b> ";
//$nombreLimpio = SanitizarEntrada::limpiarCadena($nombre);  
//echo "la salida es: ".$nombre."<br>";
?>
