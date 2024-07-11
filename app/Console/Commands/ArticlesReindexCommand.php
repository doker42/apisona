<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Elastic\Elasticsearch\Client;

class ArticlesReindexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:reindex-articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexes all articles to Elasticsearch';

    /** @var \Elastic\Elasticsearch\Client */
    private $elasticsearch;

    public function __construct(Client $elasticsearch)
    {
        parent::__construct();

        $this->elasticsearch = $elasticsearch;
    }

    public function handle()
    {
        $this->info('Indexing all articles. This might take a while...');

        foreach (Article::cursor() as $article)
        {
            $this->elasticsearch->index([
                'index' => $article->getIndex(),
                'type'  => $article->getType(),
                'id'    => $article->id,
                'body'  => $article->toBodyArray(),
            ]);

            // PHPUnit-style feedback
            $this->output->write('.');
        }

        $this->info("\nDone!");
    }
}
