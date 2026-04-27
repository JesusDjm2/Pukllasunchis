<?php

namespace App\Console\Commands;

use App\Models\KnowledgeChunk;
use App\Services\PukllaBot\PukllaBotFaqFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

class PukllaBotIngestCommand extends Command
{
    protected $signature = 'pukllabot:ingest {--force : Vaciar e indexar de nuevo}';

    protected $description = 'Trocea los .md y faqs.json (storage/app/pukllabot) en knowledge_chunks para RAG léxico.';

    public function handle(): int
    {
        $dir = (string) config('pukllabot.corpus_dir', storage_path('app/pukllabot'));
        $items = config('pukllabot.corpora', []);

        if ($items === [] || ! is_array($items)) {
            $this->error('Config pukllabot.corpora vacío o inválido.');

            return self::FAILURE;
        }

        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['file'], $item['scope'])) {
                $this->error('Cada entrada de corpora debe incluir `file` y `scope`.');

                return self::FAILURE;
            }
        }

        if ($this->option('force')) {
            KnowledgeChunk::query()->delete();
        } elseif (KnowledgeChunk::query()->exists()) {
            if (! $this->confirm('Ya hay fragmentos indexados. ¿Borrarlos y volver a indexar?', true)) {
                return self::FAILURE;
            }
            KnowledgeChunk::query()->delete();
        }

        $totalN = 0;
        foreach ($items as $item) {
            $path = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.ltrim($item['file'], '/\\');
            $scope = (string) $item['scope'];
            if (! is_readable($path)) {
                $this->error("No se encuentra o no se puede leer: {$path}");

                return self::FAILURE;
            }

            $raw = File::get($path);
            $parts = $this->splitCorpus($raw);
            if ($parts === []) {
                $this->error("No se pudo dividir el corpus: {$item['file']}");

                return self::FAILURE;
            }

            $bar = $this->output->createProgressBar(count($parts));
            $bar->start();
            $n = 0;
            foreach ($parts as $p) {
                $title = $p['title'];
                $content = $p['content'];
                if (trim($content) === '') {
                    $bar->advance();

                    continue;
                }
                KnowledgeChunk::query()->create([
                    'source' => 'corpus',
                    'scope' => $scope,
                    'title' => $title,
                    'content' => $content,
                    'embedding' => null,
                ]);
                $n++;
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
            $this->info("Archivo `{$item['file']}` (ámbito {$scope}): {$n} fragmentos.");
            $totalN += $n;
        }

        $faqN = 0;
        $allowed = array_keys(config('pukllabot.chat_scopes', []));
        foreach (PukllaBotFaqFile::all() as $scope => $faqs) {
            if (! in_array($scope, $allowed, true)) {
                $this->warn("Omitido FAQ: ámbito desconocido «{$scope}» (no está en pukllabot.chat_scopes).");

                continue;
            }
            foreach ($faqs as $faq) {
                $q = (string) ($faq['q'] ?? '');
                $a = (string) ($faq['a'] ?? '');
                $title = 'Pregunta frecuente: '.$q;
                $content = "Pregunta: {$q}\n\nRespuesta autorizada para PukllaBot: {$a}";
                KnowledgeChunk::query()->create([
                    'source' => 'faq',
                    'scope' => $scope,
                    'title' => $title,
                    'content' => $content,
                    'embedding' => null,
                ]);
                $faqN++;
            }
        }
        if ($faqN > 0) {
            $this->newLine();
            $this->info("Preguntas frecuentes (faqs.json): {$faqN} fragmentos.");
            $totalN += $faqN;
        } elseif (File::isReadable(PukllaBotFaqFile::path())) {
            $this->line('faqs.json: presente, sin pares q+a rellenos para indexar.');
        }

        $this->newLine();
        $this->info("Total indexado: {$totalN} fragmentos en knowledge_chunks.");

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{title: string|null, content: string}>
     */
    private function splitCorpus(string $raw): array
    {
        $raw = str_replace("\r\n", "\n", $raw);
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $bits = preg_split('/\n(?=## )/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        if ($bits === false) {
            throw new RuntimeException('splitCorpus inválido');
        }

        $out = [];
        foreach ($bits as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }
            if (str_starts_with($block, '## ')) {
                $rest = ltrim(substr($block, 3));
                $lines = explode("\n", $rest, 2);
                $title = trim($lines[0]);
                $body = $lines[1] ?? '';
                $out[] = ['title' => $title, 'content' => trim($body) ?: $title];
            } else {
                $out[] = ['title' => 'Introducción', 'content' => $block];
            }
        }

        return $out;
    }
}
