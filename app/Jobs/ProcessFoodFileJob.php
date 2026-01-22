<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessFoodFileJob implements ShouldQueue
{
    use Queueable;

    protected $filename;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Escolhi baixar o arquivo para um temporário antes de processar
        // pra não ficar pendurado na conexão HTTP por muito tempo enquanto leio o GZ.
        $baseUrl = "https://challenges.coode.sh/food/data/json/";
        $url = $baseUrl . $this->filename;
        $tempPath = storage_path('app/' . $this->filename);

        try {
            // Download the file
            $response = Http::timeout(300)->get($url);
            if ($response->failed()) {
                throw new \Exception("Failed to download file: " . $this->filename);
            }
            file_put_contents($tempPath, $response->body());

            // Aqui é o pulo do gato: gzopen me deixa ler o arquivo compactado sem descomprimir ele todo no disco,
            // economizando um espaço absurdo de storage.
            $handle = gzopen($tempPath, 'rb');
            if (!$handle) {
                throw new \Exception("Failed to open gz file: " . $this->filename);
            }

            $count = 0;
            // O desafio pediu os primeiros 100 de cada arquivo, então coloquei hard coded. 
            // Caso fosse algo mais dinamico poderia ser um parâmetro do job ou uma config. 
            while (!gzeof($handle) && $count < 100) {
                $line = gzgets($handle, 40960);
                if (empty($line)) continue;

                $data = json_decode($line, true);
                if (!$data) continue;

                $this->updateOrCreateProduct($data);
                $count++;
            }

            gzclose($handle);
            unlink($tempPath);

            Log::info("Successfully processed {$count} products from {$this->filename}");

        } catch (\Exception $e) {
            Log::error("Error processing {$this->filename}: " . $e->getMessage());
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            throw $e;
        }
    }

    protected function updateOrCreateProduct(array $data)
    {
        $code = str_replace('"', '', $data['code'] ?? ''); // Clean code if necessary

        if (empty($code)) return;

        // Percebi que o CSV/JSON as vezes traz campos numéricos vázios como string,
        // então forcei esse cast aqui para não quebrar o Postgres.
        $servingQuantity = isset($data['serving_quantity']) && $data['serving_quantity'] !== "" ? (double) $data['serving_quantity'] : null;
        $nutriscoreScore = isset($data['nutriscore_score']) && $data['nutriscore_score'] !== "" ? (int) $data['nutriscore_score'] : null;

        Product::updateOrCreate(
            ['code' => $code],
            [
                'status' => 'published', // Todo produto novo entra como publicado por padrão.
                'imported_t' => now(),
                'url' => $data['url'] ?? null,
                'creator' => $data['creator'] ?? null,
                'created_t' => $data['created_t'] ?? null,
                'last_modified_t' => $data['last_modified_t'] ?? null,
                'product_name' => $data['product_name'] ?? null,
                'quantity' => $data['quantity'] ?? null,
                'brands' => $data['brands'] ?? null,
                'categories' => $data['categories'] ?? null,
                'labels' => $data['labels'] ?? null,
                'cities' => $data['cities'] ?? null,
                'purchase_places' => $data['purchase_places'] ?? null,
                'stores' => $data['stores'] ?? null,
                'ingredients_text' => $data['ingredients_text'] ?? null,
                'traces' => $data['traces'] ?? null,
                'serving_size' => $data['serving_size'] ?? null,
                'serving_quantity' => $servingQuantity,
                'nutriscore_score' => $nutriscoreScore,
                'nutriscore_grade' => $data['nutriscore_grade'] ?? null,
                'main_category' => $data['main_category'] ?? null,
                'image_url' => $data['image_url'] ?? null,
            ]
        );
    }
}
