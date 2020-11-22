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

    /** @var int */
    public $page = 1;

    /** @var int */
    public $total;
    
    /** @var array */
    protected $queryString = [
        'q' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    /**
     * mount
     * 
     * @param  string $index
     * @param  int $size
     * @return void
     */
    public function mount($index = 'default', $size = 10) {
        $this->index = $index;
        $this->size = $size;
        $this->site = \Statamic\Facades\Site::current()->handle();
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
     * setPage
     * 
     * @param  int $page
     * @return void
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * totalPages
     * 
     * @return int
     */
    public function totalPages(): int
    {
        return (int) ceil($this->total / $this->size);
    }

    /**
     * pagination
     * 
     * @return array
     */
    public function pagination(): array
    {
        $pages[] = 1;
        if ($this->page > 2) {
            if ($this->page > 3) {
                $pages[] = '...';
            }
            if ($this->page === $this->totalPages()) {
                $pages[] = $this->page - 2;
            }
            $pages[] = $this->page - 1;
            $pages[] = $this->page;
            if ($this->page < $this->totalPages() - 1) {
                $pages[] = $this->page + 1;
            }
        } else {
            if ($this->page > 1) {
                $pages[] = 2;
            }
            if ($this->page < $this->totalPages() - 2) {
                $pages[] = $this->page + 1;
            }
            if ($this->page < $this->totalPages() - 1) {
                $pages[] = $this->page + 2;
            }
        }

        if ($this->page < $this->totalPages() - 2) {
            $pages[] = '...';
        }
        if ($this->page < $this->totalPages()) {
            $pages[] = $this->totalPages();
        }

        return $pages;
    }

    /**
     * Determine if the paginator is on the first page.
     *
     * @return bool
     */
    public function onFirstPage(): bool
    {
        return $this->page <= 1;
    }

    /**
     * Determine if the paginator should be visible.
     *
     * @return bool
     */
    public function showPaginator(): bool
    {
        return $this->total > $this->size;
    }

    /**
     * Determine if the paginator is on the last page.
     *
     * @return bool
     */
    public function onLastPage(): bool
    {
        return $this->page === $this->totalPages();
    }

    /**
     * Determine if the paginator is on the last page.
     *
     * @return array
     */
    protected function search()
    {
        if ($this->q !== null) {
            $builder = StatamicSearch::index($this->index)
                ->ensureExists()
                ->useElasticPagination()
                ->search($this->q)
                ->site($this->site)
                ->limit($this->size)
                ->offset($this->size * ($this->page - 1));
            $items = $builder->getItems();
            $this->total = $builder->getTotal();

            return $items;
        }

        return [];
    }
}