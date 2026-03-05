@props(['action', 'filters', 'platforms', 'genders'])

<form method="GET" action="{{ $action }}">
    <div class="filter-panel">
        <div class="filter-grid">
            {{-- Search --}}
            <x-pornguru.filter-field :label="__('filters.search')">
                <input type="text" 
                       name="search" 
                       id="search" 
                       value="{{ $filters['search'] ?? '' }}"
                       placeholder="{{ __('filters.username_placeholder') }}">
            </x-pornguru.filter-field>

            {{-- Platform --}}
            <x-pornguru.filter-field :label="__('filters.platform')">
                <select name="platform" id="platform">
                    <option value="">{{ __('filters.all_platforms') }}</option>
                    @foreach($platforms as $platform)
                        <option value="{{ $platform }}" {{ ($filters['platform'] ?? '') === $platform ? 'selected' : '' }}>
                            {{ ucfirst($platform) }}
                        </option>
                    @endforeach
                </select>
            </x-pornguru.filter-field>

            {{-- Gender --}}
            <x-pornguru.filter-field :label="__('filters.gender')">
                <select name="gender" id="gender">
                    <option value="">{{ __('filters.all_genders') }}</option>
                    @foreach($genders as $gender)
                        <option value="{{ $gender }}" {{ ($filters['gender'] ?? '') === $gender ? 'selected' : '' }}>
                            {{ ucfirst($gender) }}
                        </option>
                    @endforeach
                </select>
            </x-pornguru.filter-field>

            {{-- Age Range --}}
            <x-pornguru.filter-field :label="__('filters.age_range')">
                <div class="filter-field-row">
                    <input type="number" 
                           name="age_min" 
                           value="{{ $filters['age_min'] ?? '' }}"
                           placeholder="{{ __('filters.min') }}"
                           min="18"
                           max="99">
                    <span class="filter-field-row-separator">-</span>
                    <input type="number" 
                           name="age_max" 
                           value="{{ $filters['age_max'] ?? '' }}"
                           placeholder="{{ __('filters.max') }}"
                           min="18"
                           max="99">
                </div>
            </x-pornguru.filter-field>

            {{-- Sort --}}
            <x-pornguru.filter-field :label="__('filters.sort_by')">
                <select name="sort" id="sort">
                    <option value="viewers_count" {{ ($filters['sort'] ?? 'viewers_count') === 'viewers_count' ? 'selected' : '' }}>{{ __('filters.viewers') }}</option>
                    <option value="goal_progress" {{ ($filters['sort'] ?? '') === 'goal_progress' ? 'selected' : '' }}>{{ __('filters.close_to_goal') }}</option>
                    <option value="rating" {{ ($filters['sort'] ?? '') === 'rating' ? 'selected' : '' }}>{{ __('filters.rating') }}</option>
                    <option value="favorited_count" {{ ($filters['sort'] ?? '') === 'favorited_count' ? 'selected' : '' }}>{{ __('filters.favorites') }}</option>
                    <option value="last_online_at" {{ ($filters['sort'] ?? '') === 'last_online_at' ? 'selected' : '' }}>{{ __('filters.last_online') }}</option>
                    <option value="age" {{ ($filters['sort'] ?? '') === 'age' ? 'selected' : '' }}>{{ __('filters.age') }}</option>
                </select>
            </x-pornguru.filter-field>

            {{-- Options --}}
            <x-pornguru.filter-field :label="__('filters.options')">
                <div class="filter-checkbox-group">
                    <label class="filter-checkbox-label">
                        <input type="checkbox" 
                               name="online" 
                               value="1"
                               {{ ($filters['online'] ?? false) ? 'checked' : '' }}>
                        <span>{{ __('filters.online') }}</span>
                    </label>
                    <label class="filter-checkbox-label">
                        <input type="checkbox" 
                               name="hd" 
                               value="1"
                               {{ ($filters['hd'] ?? false) ? 'checked' : '' }}>
                        <span>{{ __('filters.hd') }}</span>
                    </label>
                    <label class="filter-checkbox-label" title="{{ __('filters.autoplay_description') }}">
                        <input type="checkbox" 
                               id="autoplay-checkbox"
                               onchange="toggleAutoplay(this.checked)">
                        <span>{{ __('filters.autoplay_previews') }}</span>
                    </label>
                </div>
            </x-pornguru.filter-field>
        </div>

        {{-- Filter Actions --}}
        <div class="filter-actions">
            <a href="{{ $action }}" class="filter-clear">{{ __('filters.clear_filters') }}</a>
            <button type="submit" class="filter-submit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                {{ __('filters.apply_filters') }}
            </button>
        </div>
    </div>
</form>
