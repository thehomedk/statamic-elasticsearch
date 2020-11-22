<div>
    <form wire:submit.prevent="resetPage" class="w-full max-w-lg">
        <div class="flex">
            <input  class="shadow border rounded w-1/2 py-2 px-3" id="q" type="text" placeholder="Indtast søgeord" wire:model.defer="q" />
            <button class="font-bold py-2 px-3 ml-4 rounded border">Søg</button>
        </div>
    </form>

@if(!empty($result))
    <section class="space-y-2 md:space-y-4 my-6">
        @foreach ($result as $item)
        <a href="{{ $item->url() }}" class="block max-w-2xl p-2 border rounded no-underline">
        <div class="font-bold">{{ $item->title }}</div>
        <div>{{ Str::words($item->description, 10, '...') }}</div>
        </a>
        @endforeach
    </section>
    @if($this->showPaginator())
        <div class="inline-flex justify-between space-x-1">
        
        @if ($this->onFirstPage())
        <button disabled class="bg-transparent py-2 px-4 border rounded text-gray-300" wire:click="previousPage">Previous</button>
        @else
        <button class="bg-transparent py-2 px-4 border rounded" wire:click="previousPage">Previous</button>
        @endif

        @foreach ($this->pagination() as $k => $v)
            @if($v === '...')
            <div class="w-8 h-10 text-center pt-5">...</div>
            @elseif($v === $page) 
                <button class="bg-transparent py-2 px-4 border rounded border-gray-500 focus:outline-none" wire:click="setPage({{ $v }})">{{ $v }}</button>
            @else
                <button class="bg-transparent py-2 px-4 border rounded" wire:click="setPage({{ $v }})">{{ $v }}</button>
            @endif
        @endforeach
        
        @if($this->onLastPage())
        <button disabled class="bg-transparent py-2 px-4 border rounded text-gray-300" wire:click="nextPage">Next</button>
        @else
        <button class="bg-transparent py-2 px-4 border rounded" wire:click="nextPage">Next</button>
        @endif

        </div>
    @endif

    @if($total === 0)
    <div>.. no hits</div>
    @endif

@endif

</div>
