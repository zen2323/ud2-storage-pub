<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HelloWorldControllerTest extends TestCase
{
    public function testIndex()
    {
        Storage::fake('local');

        // Creamos algunos archivos falsos
        Storage::disk('local')->put('file1.txt', 'Content 1');
        Storage::disk('local')->put('file2.txt', 'Content 2');

        // Realizamos la petición al endpoint
        $response = $this->getJson('/api/hello');

        // Verificamos la respuesta
        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Listado de ficheros',
                     'contenido' => ['file1.txt', 'file2.txt'],
                 ]);
    }

    public function testStore()
    {
        Storage::fake('local');

        // Realizamos la petición para crear un archivo
        $response = $this->postJson('/api/hello', [
            'filename' => 'file1.txt',
            'content' => 'Content 1',
        ]);

        // Verificamos la respuesta
        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Guardado con éxito',
                 ]);

        // Verificamos que el archivo haya sido guardado
        Storage::disk('local')->assertExists('file1.txt');
        $this->assertEquals('Content 1', Storage::disk('local')->get('file1.txt'));
    }

    public function testStoreFileAlreadyExists()
    {
        Storage::fake('local');

        // Guardamos un archivo previamente
        Storage::disk('local')->put('file1.txt', 'Content 1');

        // Intentamos guardar otro archivo con el mismo nombre
        $response = $this->postJson('/api/hello', [
            'filename' => 'file1.txt',
            'content' => 'Content 2',
        ]);

        // Verificamos la respuesta
        $response->assertStatus(409)
                 ->assertJson([
                     'mensaje' => 'El archivo ya existe',
                 ]);
    }

    public function testShow()
    {
        // Usamos Storage::fake() para simular los archivos
        Storage::fake('local');
    
        // Guardamos un archivo de prueba en el almacenamiento simulado
        Storage::disk('local')->put('file1.txt', 'Content 1');
    
        // Realizamos la petición para obtener el archivo
        $response = $this->getJson('/api/hello/file1.txt');
    
        // Verificamos que la respuesta sea correcta
        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Archivo leído con éxito',
                     'contenido' => 'Content 1',
                 ]);
    }

    public function testShowFileNotFound()
    {
        Storage::fake('local');

        // Realizamos la petición para obtener un archivo inexistente
        $response = $this->getJson('/api/hello/nonexistent.txt');

        // Verificamos la respuesta
        $response->assertStatus(404)
                 ->assertJson([
                     'mensaje' => 'Archivo no encontrado',
                 ]);
    }

    public function testUpdate()
    {
        Storage::fake('local');

        // Guardamos un archivo previamente
        Storage::disk('local')->put('file1.txt', 'Content 1');

        // Realizamos la petición para actualizar el archivo
        $response = $this->putJson('/api/hello/file1.txt', [
            'content' => 'Updated Content',
        ]);

        // Verificamos la respuesta
        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Actualizado con éxito',
                 ]);

        // Verificamos que el archivo haya sido actualizado
        Storage::disk('local')->assertExists('file1.txt');
        $this->assertEquals('Updated Content', Storage::disk('local')->get('file1.txt'));
    }

    public function testUpdateFileNotFound()
    {
        Storage::fake('local');

        // Realizamos la petición para actualizar un archivo inexistente
        $response = $this->putJson('/api/hello/nonexistent.txt', [
            'content' => 'Updated Content',
        ]);

        // Verificamos la respuesta
        $response->assertStatus(404)
                 ->assertJson([
                     'mensaje' => 'El archivo no existe',
                 ]);
    }

    public function testDestroy()
    {
        Storage::fake('local');

        // Guardamos un archivo previamente
        Storage::disk('local')->put('file1.txt', 'Content 1');

        // Realizamos la petición para eliminar el archivo
        $response = $this->deleteJson('/api/hello/file1.txt');

        // Verificamos la respuesta
        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Eliminado con éxito',
                 ]);

        // Verificamos que el archivo haya sido eliminado
        Storage::disk('local')->assertMissing('file1.txt');
    }

    public function testDestroyFileNotFound()
    {
        Storage::fake('local');

        // Realizamos la petición para eliminar un archivo inexistente
        $response = $this->deleteJson('/api/hello/nonexistent.txt');

        // Verificamos la respuesta
        $response->assertStatus(404)
                 ->assertJson([
                     'mensaje' => 'El archivo no existe',
                 ]);
    }
}
