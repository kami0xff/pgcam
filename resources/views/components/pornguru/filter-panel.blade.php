@props(['action', 'filters', 'platforms', 'genders'])

<form method="GET" action="{{ $action }}">
    <div class="filter-panel">
        <div class="filter-grid">
            {{-- Search --}}
            <x-pornguru.filter-field label="Search">
                <input type="text" 
                       name="search" 
                       id="search" 
                       value="{{ $filters['search'] ?? '' }}"
                       placeholder="Username...">
            </x-pornguru.filter-field>

            {{-- Platform --}}
            <x-pornguru.filter-field label="Platform">
                <select name="platform" id="platform">
                    <option value="">All Platforms</option>
                    @foreach($platforms as $platform)
                        <option value="{{ $platform }}" {{ ($filters['platform'] ?? '') === $platform ? 'selected' : '' }}>
                            {{ ucfirst($platform) }}
                        </option>
                    @endforeach
                </select>
            </x-pornguru.filter-field>

            {{-- Gender --}}
            <x-pornguru.filter-field label="Gender">
                <select name="gender" id="gender">
                    <option value="">All Genders</option>
                    @foreach($genders as $gender)
                        <option value="{{ $gender }}" {{ ($filters['gender'] ?? '') === $gender ? 'selected' : '' }}>
                            {{ ucfirst($gender) }}
                        </option>
                    @endforeach
                </select>
            </x-pornguru.filter-field>

            {{-- Age Range --}}
            <x-pornguru.filter-field label="Age Range">
                <div class="filter-field-row">
                    <input type="number" 
                           name="age_min" 
                           value="{{ $filters['age_min'] ?? '' }}"
                           placeholder="Min"
                           min="18"
                           max="99">
                    <span class="filter-field-row-separator">-</span>
                    <input type="number" 
                           name="age_max" 
                           value="{{ $filters['age_max'] ?? '' }}"
                           placeholder="Max"
                           min="18"
                           max="99">
                </div>
            </x-pornguru.filter-field>

            {{-- Sort --}}
            <x-pornguru.filter-field label="Sort By">
                <select name="sort" id="sort">
                    <option value="viewers_count" {{ ($filters['sort'] ?? 'viewers_count') === 'viewers_count' ? 'selected' : '' }}>Viewers</option>
                    <option value="goal_progress" {{ ($filters['sort'] ?? '') === 'goal_progress' ? 'selected' : '' }}>Close to Goal</option>
                    <option value="rating" {{ ($filters['sort'] ?? '') === 'rating' ? 'selected' : '' }}>Rating</option>
                    <option value="favorited_count" {{ ($filters['sort'] ?? '') === 'favorited_count' ? 'selected' : '' }}>Favorites</option>
                    <option value="last_online_at" {{ ($filters['sort'] ?? '') === 'last_online_at' ? 'selected' : '' }}>Last Online</option>
                    <option value="age" {{ ($filters['sort'] ?? '') === 'age' ? 'selected' : '' }}>Age</option>
                </select>
            </x-pornguru.filter-field>

            {{-- Options --}}
            <x-pornguru.filter-field label="Options">
                <div class="filter-checkbox-group">
                    <label class="filter-checkbox-label">
                        <input type="checkbox" 
                               name="online" 
                               value="1"
                               {{ ($filters['online'] ?? false) ? 'checked' : '' }}>
                        <span>Online</span>
                    </label>
                    <label class="filter-checkbox-label">
                        <input type="checkbox" 
                               name="hd" 
                               value="1"
                               {{ ($filters['hd'] ?? false) ? 'checked' : '' }}>
                        <span>HD</span>
                    </label>
                    <label class="filter-checkbox-label" title="Auto-play live previews (uses more data)">
                        <input type="checkbox" 
                               id="autoplay-checkbox"
                               onchange="toggleAutoplay(this.checked)">
                        <span>Autoplay Previews</span>
                    </label>
                </div>
            </x-pornguru.filter-field>
        </div>

        {{-- Filter Actions --}}
        <div class="filter-actions">
            <a href="{{ $action }}" class="filter-clear">Clear filters</a>
            <button type="submit" class="filter-submit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                Apply Filters
            </button>
        </div>
    </div>
</form>
