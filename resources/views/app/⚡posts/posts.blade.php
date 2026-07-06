<div class="space-y-6" x-data="{ filterOpen: false }">
  <x-header :title="__('Posts')" :subtitle="__('Manage blog posts with advanced features including SEO, scheduling, and media.')" separator />

  <x-card>
    <!-- Stats Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="stat bg-base-200 rounded-lg p-4">
        <div class="stat-title text-sm">{{ __('Total') }}</div>
        <div class="stat-value text-2xl">{{ $this->posts->total() }}</div>
      </div>
      <div class="stat bg-success/10 rounded-lg p-4">
        <div class="stat-title text-sm text-success">{{ __('Published') }}</div>
        <div class="stat-value text-2xl text-success">
          {{ \App\Models\Post::published()->count() }}
        </div>
      </div>
      <div class="stat bg-warning/10 rounded-lg p-4">
        <div class="stat-title text-sm text-warning">{{ __('Drafts') }}</div>
        <div class="stat-value text-2xl text-warning">
          {{ \App\Models\Post::draft()->count() }}
        </div>
      </div>
      <div class="stat bg-info/10 rounded-lg p-4">
        <div class="stat-title text-sm text-info">{{ __('Featured') }}</div>
        <div class="stat-value text-2xl text-info">
          {{ \App\Models\Post::featured()->count() }}
        </div>
      </div>
    </div>

    <!-- Filters Toggle -->
    <div class="mb-4">
      <button @click="filterOpen = !filterOpen" class="btn btn-ghost btn-sm">
        <x-icon name="o-funnel" class="w-4 h-4" />
        <span x-text="filterOpen ? '{{ __('Hide Filters') }}' : '{{ __('Show Filters') }}'"></span>
      </button>
      @if($showTrashed)
        <span class="badge badge-error ml-2">{{ __('Showing Trashed') }}</span>
      @endif
    </div>

    <!-- Advanced Filters -->
    <div x-show="filterOpen" x-collapse class="mb-6 p-4 bg-base-200 rounded-lg space-y-4">
      <div class="grid md:grid-cols-4 gap-4">
        <x-input wire:model.live.debounce.300ms="search" :label="__('Search')" icon="o-magnifying-glass" :placeholder="__('Title, content, excerpt...')" />
        
        <x-select :label="__('Category')" wire:model.live="categoryFilter" 
          :options="array_merge([['id' => null, 'name' => __('All Categories')]], $this->categoryOptions)" />
        
        @can('posts.view-all')
          <x-select :label="__('Author')" wire:model.live="authorFilter" 
            :options="array_merge([['id' => null, 'name' => __('All Authors')]], $this->authorOptions)" />
        @endcan
        
        <x-select :label="__('Status')" wire:model.live="statusFilter" :options="[
          ['id' => 'all', 'name' => __('All Status')],
          ['id' => 'published', 'name' => __('Published')],
          ['id' => 'draft', 'name' => __('Draft')],
          ['id' => 'scheduled', 'name' => __('Scheduled')],
        ]" />
      </div>
      
      <div class="grid md:grid-cols-4 gap-4">
        <x-select :label="__('Featured')" wire:model.live="featuredFilter" :options="[
          ['id' => null, 'name' => __('All')],
          ['id' => 1, 'name' => __('Featured Only')],
          ['id' => 0, 'name' => __('Not Featured')],
        ]" />
        
        <x-datetime :label="__('Date From')" wire:model.live="dateFrom" icon="o-calendar" />
        <x-datetime :label="__('Date To')" wire:model.live="dateTo" icon="o-calendar" />
        
        <div class="flex items-end gap-2">
          <x-toggle :label="__('Show Trashed')" wire:model.live="showTrashed" />
        </div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
      <div class="flex items-center gap-2">
        @can('posts.create')
          <x-button class="btn-primary" icon="o-plus" wire:click="create">{{ __('New Post') }}</x-button>
        @endcan
        
        @if($showBulkActions)
          <div class="flex items-center gap-2 bg-base-200 p-2 rounded-lg">
            <span class="text-sm">{{ count($selectedPosts) }} {{ __('selected') }}</span>
            <x-select wire:model="bulkAction" :options="[
              ['id' => '', 'name' => __('Bulk Action')],
              ['id' => 'publish', 'name' => __('Publish')],
              ['id' => 'feature', 'name' => __('Feature')],
              ['id' => 'unfeature', 'name' => __('Unfeature')],
              ['id' => 'delete', 'name' => __('Move to Trash')],
            ]" class="w-40" />
            <x-button class="btn-sm btn-primary" wire:click="executeBulkAction" :disabled="empty($bulkAction)">{{ __('Apply') }}</x-button>
          </div>
        @endif
      </div>
      
      <div class="flex items-center gap-2">
        <x-select wire:model.live="perPage" :options="[['id' => 10, 'name' => '10'], ['id' => 25, 'name' => '25'], ['id' => 50, 'name' => '50']]" class="w-20" />
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="table w-full">
        <thead>
          <tr>
            <th class="w-10">
              <input type="checkbox" wire:model.live="selectAll" class="checkbox checkbox-sm" />
            </th>
            <th class="cursor-pointer" wire:click="sortBy('title')">
              {{ __('Title') }} @if($sortField === 'title') <x-icon name="o-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }}" class="w-4 h-4 inline" /> @endif
            </th>
            <th>{{ __('Category') }}</th>
            <th>{{ __('Author') }}</th>
            <th class="cursor-pointer" wire:click="sortBy('published_at')">
              {{ __('Status') }} @if($sortField === 'published_at') <x-icon name="o-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }}" class="w-4 h-4 inline" /> @endif
            </th>
            <th class="cursor-pointer" wire:click="sortBy('views_count')">
              {{ __('Views') }} @if($sortField === 'views_count') <x-icon name="o-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }}" class="w-4 h-4 inline" /> @endif
            </th>
            <th class="text-right">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($this->posts as $post)
            <tr class="{{ $post->trashed() ? 'opacity-50' : '' }}">
              <td>
                <input type="checkbox" wire:model.live="selectedPosts" value="{{ $post->id }}" class="checkbox checkbox-sm" />
              </td>
              <td>
                <div class="flex items-center gap-3">
                  @if($post->featured_image_thumb_url)
                    <img src="{{ $post->featured_image_thumb_url }}" class="w-12 h-12 object-cover rounded" alt="" />
                  @else
                    <div class="w-12 h-12 bg-base-300 rounded flex items-center justify-center">
                      <x-icon name="o-photo" class="w-6 h-6 text-base-content/30" />
                    </div>
                  @endif
                  <div>
                    <div class="font-medium flex items-center gap-2">
                      {{ $post->title }}
                      @if($post->is_featured)
                        <x-icon name="o-star" class="w-4 h-4 text-warning" />
                      @endif
                    </div>
                    <div class="text-sm text-base-content/60">{{ Str::limit($post->slug, 40) }}</div>
                  </div>
                </div>
              </td>
              <td>
                @if($post->category)
                  <span class="badge badge-outline badge-sm">{{ $post->category->name }}</span>
                @else
                  <span class="text-base-content/40">—</span>
                @endif
              </td>
              <td>
                <div class="flex items-center gap-2">
                  <x-avatar :image="$post->user->avatar_url" class="w-6 h-6" />
                  <span class="text-sm">{{ $post->user->name }}</span>
                </div>
              </td>
              <td>
                <div x-data="{ status: '{{ $post->status }}' }">
                  <span class="badge badge-{{ $post->status_color }} badge-sm" x-text="status === 'published' ? '{{ __('Published') }}' : (status === 'draft' ? '{{ __('Draft') }}' : '{{ __('Scheduled') }}')">
                  </span>
                  @if($post->published_at)
                    <div class="text-xs text-base-content/60 mt-1">{{ $post->published_at->diffForHumans() }}</div>
                  @endif
                </div>
              </td>
              <td class="text-center">
                <span class="text-sm">{{ number_format($post->views_count) }}</span>
              </td>
              <td class="text-right">
                <div class="flex items-center justify-end gap-1">
                  @can('posts.feature')
                    <x-button class="btn-ghost btn-xs" wire:click="toggleFeatured({{ $post->id }})" :title="$post->is_featured ? __('Unfeature') : __('Feature')">
                      <x-icon name="o-star" class="w-4 h-4 {{ $post->is_featured ? 'text-warning' : 'text-base-content/30' }}" />
                    </x-button>
                  @endcan
                  
                  @if(!$post->trashed())
                    @can('posts.publish')
                      @if(!$post->published_at || $post->status === 'scheduled')
                        <x-button class="btn-ghost btn-xs" wire:click="publish({{ $post->id }})" title="{{ __('Publish') }}">
                          <x-icon name="o-check-circle" class="w-4 h-4 text-success" />
                        </x-button>
                      @else
                        <x-button class="btn-ghost btn-xs" wire:click="unpublish({{ $post->id }})" title="{{ __('Unpublish') }}">
                          <x-icon name="o-x-circle" class="w-4 h-4 text-warning" />
                        </x-button>
                      @endif
                    @endcan
                    
                    @can('posts.update-own')
                      <x-button class="btn-ghost btn-xs" wire:click="edit({{ $post->id }})" title="{{ __('Edit') }}">
                        <x-icon name="o-pencil-square" class="w-4 h-4" />
                      </x-button>
                    @endcan
                    
                    @can('posts.create')
                      <x-button class="btn-ghost btn-xs" wire:click="duplicate({{ $post->id }})" title="{{ __('Duplicate') }}">
                        <x-icon name="o-document-duplicate" class="w-4 h-4" />
                      </x-button>
                    @endcan
                    
                    @can('posts.delete-own')
                      <x-button class="btn-ghost btn-xs text-error" wire:click="confirmDelete({{ $post->id }})" title="{{ __('Delete') }}">
                        <x-icon name="o-trash" class="w-4 h-4" />
                      </x-button>
                    @endcan
                  @else
                    @can('posts.update-own')
                      <x-button class="btn-ghost btn-xs text-success" wire:click="confirmRestore({{ $post->id }})" title="{{ __('Restore') }}">
                        <x-icon name="o-arrow-uturn-left" class="w-4 h-4" />
                      </x-button>
                    @endcan
                    
                    @can('posts.delete')
                      <x-button class="btn-ghost btn-xs text-error" wire:click="confirmForceDelete({{ $post->id }})" title="{{ __('Delete Permanently') }}">
                        <x-icon name="o-trash" class="w-4 h-4" />
                      </x-button>
                    @endcan
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-8">
                <div class="flex flex-col items-center gap-2">
                  <x-icon name="o-document-text" class="w-12 h-12 opacity-30" />
                  <p class="text-base-content/60">{{ __('No posts found.') }}</p>
                  @can('posts.create')
                    <x-button class="btn-primary btn-sm mt-2" icon="o-plus" wire:click="create">{{ __('Create your first post') }}</x-button>
                  @endcan
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $this->posts->links() }}</div>
  </x-card>

  <!-- Create/Edit Modal -->
  <x-modal wire:model="showForm" :title="$isEditing ? __('Edit Post') : __('New Post')" class="max-w-4xl">
    <div x-data="{ tab: '{{ $activeTab }}' }" x-init="$watch('tab', value => $wire.set('activeTab', value))">
      <!-- Tabs -->
      <div class="tabs tabs-boxed mb-4">
        <button @click="tab = 'content'" :class="{ 'tab-active': tab === 'content' }" class="tab">
          <x-icon name="o-document-text" class="w-4 h-4 mr-1" /> {{ __('Content') }}
        </button>
        <button @click="tab = 'settings'" :class="{ 'tab-active': tab === 'settings' }" class="tab">
          <x-icon name="o-cog" class="w-4 h-4 mr-1" /> {{ __('Settings') }}
        </button>
        <button @click="tab = 'seo'" :class="{ 'tab-active': tab === 'seo' }" class="tab">
          <x-icon name="o-magnifying-glass" class="w-4 h-4 mr-1" /> {{ __('SEO') }}
        </button>
        @if($isEditing)
          <button @click="tab = 'media'" :class="{ 'tab-active': tab === 'media' }" class="tab">
            <x-icon name="o-photo" class="w-4 h-4 mr-1" /> {{ __('Media') }}
          </button>
        @endif
      </div>

      <!-- Content Tab -->
      <div x-show="tab === 'content'" x-cloak class="space-y-4">
        <x-input :label="__('Title')" wire:model.defer="title" required :placeholder="__('Post title')" />
        
        <div x-data="{ slug: @entangle('slug'), title: @entangle('title'), isEditing: @entangle('isEditing') }" x-init="$watch('title', value => { if (!isEditing || slug === '') slug = value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') })">
          <x-input :label="__('Slug')" wire:model.defer="slug" required 
            :hint="__('URL-friendly identifier')"  />
          
          @error('slug')
            <span class="text-error text-sm">{{ $message }}</span>
          @enderror
        </div>
        
        <x-select :label="__('Category')" wire:model.defer="category_id" 
          :options="array_merge([['id' => null, 'name' => __('Uncategorized')]], $this->categoryOptions)" />
        
        <x-textarea :label="__('Excerpt')" wire:model.defer="excerpt" rows="3" 
          :hint="__('Short summary (max 500 chars). Auto-generated from content if empty.')" 
          x-data="{ count: 0 }" x-init="count = $wire.excerpt.length" x-on:input="count = $event.target.value.length">
          <x-slot:append>
            <span class="text-xs" :class="count > 500 ? 'text-error' : 'text-base-content/50'" x-text="count + '/500'"></span>
          </x-slot:append>
        </x-textarea>
        
        <div wire:ignore>
          <x-textarea :label="__('Content')" wire:model.defer="content" rows="10" required 
            :placeholder="__('Write your post content here...')" />
        </div>
      </div>

      <!-- Settings Tab -->
      <div x-show="tab === 'settings'" x-cloak class="space-y-4">
        <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
          <div>
            <div class="font-medium">{{ __('Featured Post') }}</div>
            <div class="text-sm text-base-content/60">{{ __('Featured posts appear in special sections') }}</div>
          </div>
          <x-toggle wire:model.defer="is_featured" />
        </div>
        
        <x-datetime :label="__('Published At')" wire:model.defer="published_at" icon="o-calendar"
          :hint="__('Leave empty for draft. Set future date to schedule.')" />
        
        @can('posts.update')
          <x-select :label="__('Author')" wire:model.defer="user_id" required 
            :options="$this->authorOptions" />
        @else
          <input type="hidden" wire:model.defer="user_id" />
        @endcan
        
        @if($lastSaved)
          <div class="text-sm text-base-content/60 flex items-center gap-2">
            <x-icon name="o-clock" class="w-4 h-4" />
            {{ __('Last auto-saved') }}: <span x-timeago="{{ $lastSaved }}"></span>
          </div>
        @endif
      </div>

      <!-- SEO Tab -->
      <div x-show="tab === 'seo'" x-cloak class="space-y-4">
        <div x-data="{ title: @entangle('meta_title'), count: 0 }" x-init="count = title.length">
          <x-input :label="__('Meta Title')" wire:model.defer="meta_title" 
            :placeholder="__('SEO title (60 chars max)')" 
            x-on:input="count = $event.target.value.length">
            <x-slot:append>
              <span class="text-xs" :class="count > 60 ? 'text-error' : 'text-base-content/50'" x-text="count + '/60'"></span>
            </x-slot:append>
          </x-input>
        </div>
        
        <div x-data="{ desc: @entangle('meta_description'), count: 0 }" x-init="count = desc.length">
          <x-textarea :label="__('Meta Description')" wire:model.defer="meta_description" rows="3"
            :placeholder="__('SEO description (160 chars max)')"
            x-on:input="count = $event.target.value.length">
            <x-slot:append>
              <span class="text-xs" :class="count > 160 ? 'text-error' : 'text-base-content/50'" x-text="count + '/160'"></span>
            </x-slot:append>
          </x-textarea>
        </div>
        
        <x-input :label="__('Meta Keywords')" wire:model.defer="meta_keywords"
          :placeholder="__('keyword1, keyword2, keyword3')" />
        
        <!-- SEO Preview -->
        <div class="p-4 bg-base-200 rounded-lg">
          <div class="text-sm font-medium mb-2">{{ __('Search Preview') }}</div>
          <div class="font-medium text-blue-600 truncate" x-text="$wire.meta_title || $wire.title || '{{ __('No title') }}'"></div>
          <div class="text-sm text-green-700">{{ config('app.url') }}/<span x-text="$wire.slug || 'post-slug'"></span></div>
          <div class="text-sm text-gray-600 mt-1" x-text="$wire.meta_description || '{{ __('No description') }}'"></div>
        </div>
      </div>

      <!-- Media Tab -->
      @if($isEditing)
        <div x-show="tab === 'media'" x-cloak class="space-y-4">
          <div x-data="{ imageUrl: @entangle('featured_image_url'), hasImage: @entangle('remove_featured_image') }">
            @if($featured_image_url || $featured_image)
              <div class="mb-4">
                <p class="text-sm font-medium mb-2">{{ __('Current Featured Image') }}</p>
                <div class="relative w-full h-48 rounded-lg overflow-hidden bg-base-300">
                  @if($featured_image)
                    <img src="{{ $featured_image->temporaryUrl() }}" class="w-full h-full object-cover" alt="Preview" />
                  @elseif($selectedId)
                    @php
                      $post = \App\Models\Post::find($selectedId);
                    @endphp
                    @if($post && $post->featured_image_url)
                      <img src="{{ $post->featured_image_url }}" class="w-full h-full object-cover" alt="Featured" />
                    @endif
                  @endif
                </div>
              </div>
            @endif
            
            <x-input :label="__('Image URL')" wire:model="featured_image_url" type="url"
              :placeholder="__('https://example.com/image.jpg')" />
            
            <div class="text-center text-sm text-base-content/60 my-2">{{ __('OR') }}</div>
            
            <x-file wire:model="featured_image" :label="__('Upload Image')" accept="image/*"
              :hint="__('Max 10MB. JPG, PNG, WebP recommended.')" />
            
            @if($post && $post->featured_image_url)
              <div class="mt-4 p-3 bg-error/10 rounded-lg">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox" wire:model="remove_featured_image" class="checkbox checkbox-error checkbox-sm" />
                  <span class="text-error text-sm">{{ __('Remove featured image') }}</span>
                </label>
              </div>
            @endif
          </div>
        </div>
      @else
        <div x-show="tab === 'media'" x-cloak class="space-y-4">
          <x-alert :title="__('Save post first')" :description="__('You can add featured images after creating the post.')" icon="o-information-circle" class="alert-info" />
          
          <x-input :label="__('Image URL')" wire:model="featured_image_url" type="url"
            :placeholder="__('https://example.com/image.jpg')" />
          
          <x-file wire:model="featured_image" :label="__('Upload Image')" accept="image/*"
            :hint="__('Max 10MB. JPG, PNG, WebP recommended.')" />
        </div>
      @endif
    </div>

    <x-slot:actions>
      <x-button class="btn-ghost" wire:click="$set('showForm', false)">{{ __('Cancel') }}</x-button>
      <x-button class="btn-primary" wire:click="save" spinner="save">
        {{ $isEditing ? __('Update') : __('Create') }}
      </x-button>
    </x-slot:actions>
  </x-modal>

  <!-- Delete Modal -->
  <x-modal wire:model="confirmingDeleteId" :title="__('Move to Trash')">
    <p>{{ __('Are you sure you want to move this post to trash?') }}</p>
    <x-slot:actions>
      <x-button class="btn-ghost" wire:click="$set('confirmingDeleteId', null)">{{ __('Cancel') }}</x-button>
      <x-button class="btn-error" wire:click="deleteConfirmed">{{ __('Move to Trash') }}</x-button>
    </x-slot:actions>
  </x-modal>

  <!-- Restore Modal -->
  <x-modal wire:model="confirmingRestoreId" :title="__('Restore Post')">
    <p>{{ __('Restore this post from trash?') }}</p>
    <x-slot:actions>
      <x-button class="btn-ghost" wire:click="$set('confirmingRestoreId', null)">{{ __('Cancel') }}</x-button>
      <x-button class="btn-success" wire:click="restoreConfirmed">{{ __('Restore') }}</x-button>
    </x-slot:actions>
  </x-modal>

  <!-- Force Delete Modal -->
  <x-modal wire:model="confirmingForceDeleteId" :title="__('Delete Permanently')">
    <p class="text-error">{{ __('This action cannot be undone. The post will be permanently deleted.') }}</p>
    <x-slot:actions>
      <x-button class="btn-ghost" wire:click="$set('confirmingForceDeleteId', null)">{{ __('Cancel') }}</x-button>
      <x-button class="btn-error" wire:click="forceDeleteConfirmed">{{ __('Delete Permanently') }}</x-button>
    </x-slot:actions>
  </x-modal>
</div>
