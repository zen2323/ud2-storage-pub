<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HelloWorldController extends Controller
{
    /**
     * Lista todos los ficheros de la carpeta storage/app.
     *
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     * - contenido: Un array con los nombres de los ficheros.
     */
    
    public function index()
    {
        // Obtener todos los archivos del almacenamiento local
        $files = Storage::disk('local')->files(); // O 'allFiles()' si quieres incluir directorios

        // Responder con un mensaje y la lista de archivos
        return response()->json([
            'mensaje' => 'Listado de ficheros',
            'contenido' => $files,
        ], 200); // Código de estado 200 indica éxito
    }

     /**
     * Recibe por parámetro el nombre de fichero y el contenido. Devuelve un JSON con el resultado de la operación.
     * Si el fichero ya existe, devuelve un 409.
     *
     * @param filename Parámetro con el nombre del fichero. Devuelve 422 si no hay parámetro.
     * @param content Contenido del fichero. Devuelve 422 si no hay parámetro.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     */

    public function store(Request $request)
    {
        // Validación de los parámetros, se asegura que 'filename' y 'content' sean enviados
        $request->validate([
            'filename' => 'required|string',
            'content' => 'required|string',
        ]);

        $filename = $request->input('filename');
        $content = $request->input('content');

        // Verificar si el archivo ya existe
        if (Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'El archivo ya existe',
            ], 409); // Código 409 indica que el archivo ya existe
        }

        // Guardar el archivo con el contenido proporcionado
        Storage::disk('local')->put($filename, $content);

        // Responder con el mensaje de éxito
        return response()->json([
            'mensaje' => 'Guardado con éxito',
        ], 200); // Código 200 para éxito
    }

     /**
     * Recibe por parámetro el nombre de fichero y devuelve un JSON con su contenido
     *
     * @param name Parámetro con el nombre del fichero.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     * - contenido: El contenido del fichero si se ha leído con éxito.
     */
    public function show(string $filename)
    {
        // Verificamos si el archivo existe
        if (!Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'Archivo no encontrado',
            ], 404);
        }
    
        // Leemos el contenido del archivo
        $content = Storage::disk('local')->get($filename);
    
        return response()->json([
            'mensaje' => 'Archivo leído con éxito',
            'contenido' => $content,
        ], 200);
    }
    

    /**
     * Recibe por parámetro el nombre de fichero, el contenido y actualiza el fichero.
     * Devuelve un JSON con el resultado de la operación.
     * Si el fichero no existe devuelve un 404.
     *
     * @param filename Parámetro con el nombre del fichero. Devuelve 422 si no hay parámetro.
     * @param content Contenido del fichero. Devuelve 422 si no hay parámetro.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     */
    public function update(Request $request, string $filename)
    {
        // Validar los parámetros
        $request->validate([
            'content' => 'required|string',
        ]);

        // Verificar si el archivo existe
        if (!Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'El archivo no existe',
            ], 404); // Código 404 si el archivo no se encuentra
        }

        // Actualizar el contenido del archivo
        Storage::disk('local')->put($filename, $request->input('content'));

        // Responder con el mensaje de éxito
        return response()->json([
            'mensaje' => 'Actualizado con éxito',
        ], 200); // Código 200 para éxito
    }

    /**
     * Recibe por parámetro el nombre de ficher y lo elimina.
     * Si el fichero no existe devuelve un 404.
     *
     * @param filename Parámetro con el nombre del fichero. Devuelve 422 si no hay parámetro.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     */
    public function destroy(string $filename)
    {
        // Verificar si el archivo existe
        if (!Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'El archivo no existe',
            ], 404); // Código 404 si el archivo no se encuentra
        }

        // Eliminar el archivo
        Storage::disk('local')->delete($filename);

        // Responder con el mensaje de éxito
        return response()->json([
            'mensaje' => 'Eliminado con éxito',
        ], 200); // Código 200 para éxito
    }
}
