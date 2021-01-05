<div>
    <div class="w-full p-8 bg-white text-center border rounded">
        <form class="center" wire:submit.prevent="resetPage">
                <div class="flex">
                    <input  class="border rounded mx-8 px-4 py-1" id="q" type="text" placeholder="Search for .." wire:model.defer="q" />
                    <button class="border rounded mx-8 px-4 py-1">Search</button>
                </div>
        </form>
    </div>

    <div id="search-loading" wire:loading class="w-full">
        <div> .. searching </div>
    </div>
    
    <div wire:loading.remove>
        @if($total > 0)

            <div class="w-full md:max-w-3xl my-10 pb-6 flex justify-between border-b-2 border-grey">
                <div class="text-base font-normal leading-none" role="status">{{ $range }} of {{ $total }} hits</div>
            </div>

            <div class="space-y-4 md:space-y-6" id="search-result">
              @foreach ($result as $item) 
              <article class="group relative block border-b border-grey">
                <a href="{{ $item->url() }}" class="flex items-center mb-4 md:mb-6 space-x-4 md:space-x-6 lg:space-x-12">
                    <div class="w-full ">
                        <div>{{  $item->search_score }}</div>
                    <h3 class="break-words hyphens-auto mt-1 group-hover:underline">
                        {{  $item->title }}
                    </h3>
                    <p class="paragraph break-words max-w-screen-sm mt-1 lg:block">
                       {{ Str::words($item->description, 10, '...') }}
                    </p>
                    </div>
                </a>
              </article>
              @endforeach
            </div>
                
            @if($pages > 1)
                <div class="flex justify-center items-center mt-8 space-x-4" role="navigation" aria-label="paginatotion" aria-live='polite'> 
                @if($page > 1)
                    <button wire:click="$emit('previousPage')" class="w-12 h-12 flex items-center justify-center bg-white hover:bg-grey-element border border-black rounded-full focus:outline-none focus-visible:shadow-outline text-black" aria-controls="article-listings" aria-label="Previous page">
                        <svg xmlns="http://www.w3.org/2000/svg" height="15" width="15" viewBox="0 0 12 13" fill="currentColor" stroke="currentColor">
                        <path class="st0" d="M2.9,6.3h8.6v0.5H2.9H1.7l0.9,0.9l3.8,3.8L6,11.8L0.7,6.5L6,1.2l0.4,0.4L2.5,5.4L1.7,6.3H2.9z"></path>
                        </svg>
                    </button>
                @endif

                <span class="text-lg font-bold leading-none" role="status">{{ $page }} / {{ $pages }}</span>
                
                @if($page < $pages)
                    <button wire:click="$emit('nextPage')" class="w-12 h-12 flex items-center justify-center bg-white hover:bg-grey-element border border-black rounded-full focus:outline-none focus-visible:shadow-outline text-black" aria-controls="article-listings" aria-label="Next page">
                    <svg viewBox="0 0 12 13"  height="15" width="15" fill="currentColor" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.1275 6.75H0.5V6.25H9.1275H10.3331L9.48137 5.39676L5.64929 1.55782L6 1.20711L11.2929 6.5L6 11.7929L5.64929 11.4422L9.48137 7.60324L10.3331 6.75H9.1275Z"></path>
                    </svg>
                    </button>
                @endif
                </div>
            @endif

        @endif

        @if($total === 0)
        <div class="mt-4 w-full" id='article-listings' role='status'>... no pages found</div>
        @endif
    </div>

</div>
