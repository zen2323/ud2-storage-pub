<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class CsvController extends Controller
{
    /**
     * Lista todos los ficheros CSV de la carpeta storage/app.
     *
     * @return JsonResponse La respuesta en formato JSON.
     */
    public function index(): JsonResponse
    {
        $files = Storage::files(); // Obtiene todos los archivos en storage/app
        $csvFiles = array_filter($files, fn($file) => str_ends_with($file, '.csv')); // Filtra solo los CSV

        return response()->json([
            'mensaje' => 'Listado de ficheros',
            'contenido' => array_values($csvFiles), // Retorna un array plano de los nombres
        ]);
    }

    /**
     * Crea un nuevo fichero con el contenido especificado.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'filename' => 'required|string',
            'content' => 'required|string',
        ]);

        $filename = $request->input('filename');
        $content = $request->input('content');

        if (Storage::exists($filename)) {
            return response()->json(['mensaje' => 'El fichero ya existe'], 409);
        }

        Storage::put($filename, $content);

        return response()->json(['mensaje' => 'Guardado con éxito']);
    }

    /**
     * Lee el contenido de un fichero CSV y lo devuelve como JSON.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $filePath = 'app/' . $id; // Construye la ruta correcta
    
        if (!Storage::exists($filePath)) {
            return response()->json(['mensaje' => 'Fichero no encontrado'], 404);
        }
    
        $content = Storage::get($filePath);
        $lines = explode("\n", trim($content)); // Divide las líneas y elimina saltos de línea adicionales
    
        if (empty($lines)) {
            return response()->json(['mensaje' => 'El fichero está vacío', 'contenido' => []], 200);
        }
    
        $headers = str_getcsv(array_shift($lines)); // Obtiene los encabezados de la primera línea
    
        // Procesa las líneas restantes
        $data = array_map(function ($line) use ($headers) {
            $row = str_getcsv($line); // Convierte una línea CSV en un array
            return array_combine($headers, $row); // Combina encabezados con valores
        }, $lines);
    
        return response()->json([
            'mensaje' => 'Fichero leído con éxito',
            'contenido' => $data,
        ]);
    }
    


    /**
     * Actualiza el contenido de un fichero existente.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
{
    $filePath = 'app/' . $id; // Construye la ruta correcta

    $request->validate([
        'content' => 'required|string',
    ]);

    if (!Storage::exists($filePath)) {
        return response()->json(['mensaje' => 'Fichero no encontrado'], 404);
    }

    $content = $request->input('content');
    $decodedContent = json_decode($content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return response()->json(['mensaje' => 'Contenido no válido'], 415);
    }

    Storage::put($filePath, $content);

    return response()->json(['mensaje' => 'Fichero actualizado exitosamente']);
}


    /**
     * Elimina un fichero.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $filePath = 'app/' . $id; // Construye la ruta correcta
    
        if (!Storage::exists($filePath)) {
            return response()->json(['mensaje' => 'Fichero no encontrado'], 404);
        }
    
        Storage::delete($filePath);
    
        return response()->json(['mensaje' => 'Fichero eliminado exitosamente']);
    }
    
}
