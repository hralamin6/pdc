@section('title', $page->title)
@section('image', getImage($page, 'featured_image', 'thumb'))
@section('description', Str::limit(strip_tags($page->content), 333))
<div>

        <div class="min-h-screen bg-base-200">
            {{-- Header Section --}}
            <div class="bg-gradient-to-br from-primary/10 via-base-100 to-secondary/10 py-16">
                <div class="container mx-auto px-4">
                    <div class="max-w-4xl mx-auto text-center">
                        <h1 class="text-4xl md:text-5xl font-bold text-base-content mb-4">
                            {{ $page->title }}
                        </h1>
                        @if($page->published_at)
                            <div class="text-sm text-base-content/60">
                                <x-icon name="o-calendar" class="w-4 h-4 inline" />
                                {{ __('Published on') }} {{ $page->published_at->format('F d, Y') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Content Section --}}
            <div class="container mx-auto px-4 py-12">
                <div class="max-w-4xl mx-auto">
                    <x-card class="shadow-xl">

                            <figure class="mb-6">
                                <img src="{{ getImage($page, 'featured_image', 'thumb') }}"
                                     alt="{{ $page->title }}"
                                     class="w-full h-auto rounded-lg">
                            </figure>

                      <article class="prose dark:prose-invert max-w-none line-clamp-2" x-on:click="$el.classList.toggle('line-clamp-2')">
                        {!! $page->content !!}
                      </article>

                        {{-- Footer Info --}}
                        <div class="divider my-8"></div>
                        <div class="text-sm text-base-content/60">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div>
                                    {{ __('Last updated') }}: {{ $page->updated_at->format('F d, Y') }}
                                </div>
                                <div>
                                    <a href="/" class="link link-primary">
                                        <x-icon name="o-home" class="w-4 h-4 inline" />
                                        {{ __('Back to Home') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>
        </div>

</div>
