<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    #[OA\Get(
        path: '/products',
        summary: 'Lista produtos com paginação',
        tags: ['Products'],
        security: [['ApiKeyAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', description: 'Número da página', required: false, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista de produtos'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function index()
    {
        $products = Product::orderBy('id')->paginate(10);
        return response()->json($products);
    }

    #[OA\Get(
        path: '/products/{code}',
        summary: 'Obtém um produto pelo código',
        tags: ['Products'],
        security: [['ApiKeyAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'code', in: 'path', description: 'Código do produto', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalhes do produto'),
            new OA\Response(response: 404, description: 'Produto não encontrado'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function show($code)
    {
        $product = Product::where('code', $code)->firstOrFail();
        return response()->json($product);
    }

    #[OA\Put(
        path: '/products/{code}',
        summary: 'Atualiza um produto',
        tags: ['Products'],
        security: [['ApiKeyAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'code', in: 'path', description: 'Código do produto', required: true, schema: new OA\Schema(type: 'string'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'product_name', type: 'string'),
                    new OA\Property(property: 'status', type: 'string', enum: ['draft', 'trash', 'published'])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Produto atualizado'),
            new OA\Response(response: 404, description: 'Produto não encontrado'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function update(Request $request, $code)
    {
        $product = Product::where('code', $code)->firstOrFail();
        
        $validated = $request->validate([
            'status' => 'sometimes|in:draft,trash,published',
            'product_name' => 'sometimes|string',
            'quantity' => 'sometimes|string',
            'brands' => 'sometimes|string',
            'categories' => 'sometimes|string',
            'labels' => 'sometimes|string',
            'cities' => 'sometimes|string',
            'purchase_places' => 'sometimes|string',
            'stores' => 'sometimes|string',
            'ingredients_text' => 'sometimes|string',
            'traces' => 'sometimes|string',
            'serving_size' => 'sometimes|string',
            'serving_quantity' => 'sometimes|numeric',
            'nutriscore_score' => 'sometimes|integer',
            'nutriscore_grade' => 'sometimes|string',
            'main_category' => 'sometimes|string',
            'image_url' => 'sometimes|url',
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    #[OA\Delete(
        path: '/products/{code}',
        summary: "Muda o status do produto para 'trash'",
        tags: ['Products'],
        security: [['ApiKeyAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'code', in: 'path', description: 'Código do produto', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Status atualizado'),
            new OA\Response(response: 404, description: 'Produto não encontrado'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function destroy($code)
    {
        // O desafio pediu explicitamente para não deletar do banco.
        // Então em vez de deletar o registro (hard delete), eu apenas mudo o status.
        // Isso preserva os dados caso a gente precise recuperar depois.
        $product = Product::where('code', $code)->firstOrFail();
        $product->update(['status' => 'trash']);

        return response()->json(['message' => 'Product status updated to trash']);
    }
}
