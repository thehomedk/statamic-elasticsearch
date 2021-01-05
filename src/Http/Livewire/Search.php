<?php

namespace TheHome\StatamicElasticsearch\Http\Livewire;

use Livewire\Component;
use Statamic\Facades\Search as StatamicSearch;

class Search extends Component
{
    /** @var string */
    public $index;

    /** @var int */
    public $size;

    /** @var string */
    public $q;

    /** @var string */
    public $site;

    /** @var string */
    public $collection;

    /** @var int */
    public $page = 1;

    /** @var int */
    public $pages = 1;
  
    /** @var string */
    public $range;

    /** @var int */
    public $total;
    
    /** @var array */
    protected $queryString = [
        'q' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'nextPage' => 'nextPage',
        'previousPage' => 'previousPage',
    ];

    /**
     * mount
     * 
     * @param  string $index
     * @param  int $size
     * @param  string $collection
     * @return void
     */
    public function mount($index = 'default', $size = 10, $collection = null) {
        $this->index = $index;
        $this->size = $size;
        $this->site = \Statamic\Facades\Site::current()->handle();
        $this->collection = $collection;
    }

    /**
     * render
     * 
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function render()
    {
        return view('elasticsearch::search', ['result' => $this->search()]);
    }
    
    /**
     * resetPage
     * 
     * @return void
     */
    public function resetPage()
    {
        $this->page = 1;
    }

    /**
     * previousPage
     * 
     * @return void
     */
    public function previousPage()
    {
        $this->page--;
    }

    /**
     * nextPage
     * 
     * @return void
     */
    public function nextPage()
    {
        $this->page++;
    }

    /**
     * Perform search.
     *
     * @return array
     */
    protected function search()
    {   
        if ($this->q !== null) {
            $offset = $this->size * ($this->page - 1);
            $builder = StatamicSearch::index($this->index)
                ->ensureExists()
                ->useElasticPagination()
                ->search($this->q)
                ->site($this->site)
                ->collection($this->collection)
                ->limit($this->size)
                ->offset($offset);
            $items = $builder->getItems();
            $this->total = $builder->getTotal();
            $this->pages = (int) ceil($this->total / $this->size);
            
            if ($this->pages > 1) {
                $last = $this->page < $this->pages ? $offset + $this->size : $this->total;
                $this->range = sprintf("%d-%d", $offset + 1, $last);
            } else {
                $this->range = sprintf("1-%d", $this->total);  
            }

            return $items;
        }

        return [];
    }
}