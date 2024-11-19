<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class JsonController extends Controller
{
    /**
     * Función para validar si un string es un JSON válido.
     */
    private function isValidJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Lista todos los ficheros JSON de la carpeta storage/app.
     * Se debe comprobar fichero a fichero si su contenido es un JSON válido.
     *
     * @return JsonResponse La respuesta en formato JSON.
     */
    public function index()
    {
        $files = Storage::files('app');
        $validJsonFiles = [];

        foreach ($files as $file) {
            // Filtrar solo archivos .json válidos
            if (strpos($file, '.json') !== false && $this->isValidJson(Storage::get($file))) {
                $validJsonFiles[] = basename($file);
            }
        }

        return response()->json([
            'mensaje' => 'Operación exitosa',
            'contenido' => $validJsonFiles
        ]);
    }

    /**
     * Recibe el nombre de fichero y el contenido. Devuelve un JSON con el resultado de la operación.
     * Si el fichero ya existe, devuelve un 409.
     * Si el contenido no es un JSON válido, devuelve un 415.
     *
     * @param Request $request
     * @return JsonResponse La respuesta en formato JSON.
     */
    public function store(Request $request)
    {
        $filename = $request->input('filename');
        $content = $request->input('content');

        // Validar parámetros
        if (!$filename || !$content) {
            return response()->json(['mensaje' => 'Parametros inválidos'], 422);
        }

        // Verificar si el archivo ya existe
        if (Storage::exists("app/$filename")) {
            return response()->json(['mensaje' => 'El fichero ya existe'], 409);
        }

        // Validar que el contenido sea JSON
        if (!$this->isValidJson($content)) {
            return response()->json(['mensaje' => 'Contenido no es un JSON válido'], 415);
        }

        // Guardar el archivo
        Storage::put("app/$filename", $content);

        return response()->json(['mensaje' => 'Fichero guardado exitosamente']);
    }

    /**
     * Recibe el nombre de fichero y devuelve un JSON con su contenido.
     *
     * @param string $id Nombre del fichero.
     * @return JsonResponse La respuesta en formato JSON.
     */
    public function show(string $id)
    {
        $filePath = "app/$id";

        // Verificar si el archivo existe
        if (!Storage::exists($filePath)) {
            return response()->json(['mensaje' => 'El fichero no existe'], 404);
        }

        // Obtener el contenido del archivo
        $content = Storage::get($filePath);

        // Verificar que el contenido sea JSON
        if (!$this->isValidJson($content)) {
            return response()->json(['mensaje' => 'Contenido no es un JSON válido'], 415);
        }

        return response()->json([
            'mensaje' => 'Operación exitosa',
            'contenido' => json_decode($content)
        ]);
    }

    /**
     * Recibe el nombre de fichero y el contenido, y actualiza el archivo.
     * Devuelve un JSON con el resultado de la operación.
     * Si el fichero no existe devuelve un 404.
     * Si el contenido no es un JSON válido, devuelve un 415.
     *
     * @param Request $request
     * @param string $id Nombre del fichero.
     * @return JsonResponse La respuesta en formato JSON.
     */
    public function update(Request $request, string $id)
    {
        $filePath = "app/$id";
        $content = $request->input('content');

        // Verificar si el archivo existe
        if (!Storage::exists($filePath)) {
            return response()->json(['mensaje' => 'El fichero no existe'], 404);
        }

        // Validar que el contenido sea JSON
        if (!$this->isValidJson($content)) {
            return response()->json(['mensaje' => 'Contenido no es un JSON válido'], 415);
        }

        // Actualizar el archivo
        Storage::put($filePath, $content);

        return response()->json(['mensaje' => 'Fichero actualizado exitosamente']);
    }

    /**
     * Recibe el nombre de fichero y lo elimina.
     * Si el fichero no existe devuelve un 404.
     *
     * @param string $id Nombre del fichero.
     * @return JsonResponse La respuesta en formato JSON.
     */
    public function destroy(string $id)
    {
        $filePath = "app/$id";

        // Verificar si el archivo existe
        if (!Storage::exists($filePath)) {
            return response()->json(['mensaje' => 'El fichero no existe'], 404);
        }

        // Eliminar el archivo
        Storage::delete($filePath);

        return response()->json(['mensaje' => 'Fichero eliminado exitosamente']);
    }
}
