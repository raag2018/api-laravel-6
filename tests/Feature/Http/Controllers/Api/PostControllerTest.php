<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\User;
use App\Post;
class PostControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_store()
    {
        //se crea un usuario para la autenticacion
        $user = factory(User::class)->create();
        //para ver las excepciones en el test
        $this->withoutExceptionHandling();
        //se envia el titulo por POST
        //se usa el metod actingAs para verificar que el usuario
        //tiene un token de acceso
        $response = $this->actingAs($user, 'api')
            ->json('POST','/api/posts',[
            'title' => 'El post de prueba'
        ]);
        //Se verifica la estructura de los datos en json
        //se verifica que el titulo sea igual
        //se verifica que el status 201 sea ok y haya creado el recurso en la bdd
        $response->assertJsonStructure(['id','title','created_at','updated_at'])
            ->assertJson(['title' => 'El post de prueba'])
            ->assertStatus(201);
        //se verifica que exista el registro en la bdd
        $this->assertDatabaseHas('posts',['title' => 'El post de prueba']);
    }
    public function test_validate_title(){
        $user = factory(User::class)->create();
        $response = $this->actingAs($user, 'api')
            ->json('POST', '/api/posts', [
            'title' => ''
        ]);
        //status 422 la solicitud se realizo exitosamente
        //pero fue dificil completarla
        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }
    public function test_show(){
        $user = factory(User::class)->create();
        $this->withoutExceptionHandling();
        //se crea el post en la bdd
        $post = factory(Post::class)->create();
        //se accede al post
        $response = $this->actingAs($user, 'api')
            ->json('GET', "/api/posts/$post->id");
        $response->assertJsonStructure(['id','title','created_at','updated_at'])
            ->assertJson(['title' => $post->title])
            ->assertStatus(200);
    }
    public function test_404_show(){
        $user = factory(User::class)->create();
        $response = $this->actingAs($user, 'api')
            ->json('GET','/api/posts/1000');
        $response->assertStatus(404);
    }
    public function test_update(){
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create();
        $response = $this->actingAs($user, 'api')
            ->json('PUT', "/api/posts/$post->id", [
            'title' => 'nuevo'
        ]);
        $response->assertJsonStructure(['id','title','created_at','updated_at'])
                ->assertJson(['title' => 'nuevo'])
                ->assertStatus(200);
        $this->assertDatabaseHas('posts',['title' => 'nuevo']);
    }
    public function test_delete(){
        //se crea el post
        $user = factory(User::class)->create();
        $post = factory(Post::class)->create();
        //ruta  para eliminar el post 
        $response = $this->actingAs($user, 'api')
            ->json('DELETE', "/api/posts/$post->id");
        //esperamos una respuesta null
        //status 204 sin contenido
        $response->assertSee(null)
            ->assertStatus(204);
        //se verifica que el post que se elimino ya no este en la bdd
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
    public function test_index(){
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        factory(Post::class, 5)->create();
        $response = $this->actingAs($user, 'api')
            ->json('GET', '/api/posts');
        $response->assertJsonStructure([
            "data" => [
                "*" => ['id', 'title', 'created_at', 'updated_at']
            ]
        ])->assertStatus(200);
    }
    public function test_guest(){
        //status 401 indica que no tiene acceso
        $this->json('GET',     '/api/posts')->assertStatus(401);
        $this->json('POST',    '/api/posts')->assertStatus(401);
        $this->json('GET',     '/api/posts/1000')->assertStatus(401);
        $this->json('PUT',     '/api/posts/1000')->assertStatus(401);
        $this->json('DELETE',  '/api/posts/1000')->assertStatus(401);
    }
}
