<script>
(function() {
    function registerPrayerWidget() {
        Alpine.data('prayerWidget', () => ({
            lat: null, lng: null,
            locationName: 'Detecting location...',
            calcSource: localStorage.getItem('pr_src') || 'api',
            calcMethod: localStorage.getItem('pr_mth') || 'Karachi',
            madhab: localStorage.getItem('pr_mdh') || 'hanafi',
            times: {}, fmt: {},
            nextName: '---', nextTime: '--:--', nextDt: null,
            countdown: '--:--:--',
            bgFrom: 'from-slate-900 via-slate-800 to-slate-900',
            timer: null, isLoading: true, showModal: false,
            searchQ: '', searching: false, searchErr: '',

            init() {
                this.$watch('calcSource', v => localStorage.setItem('pr_src', v));
                this.$watch('calcMethod', v => localStorage.setItem('pr_mth', v));
                this.$watch('madhab', v => localStorage.setItem('pr_mdh', v));
                this.boot();
            },

            boot() {
                const la = localStorage.getItem('pr_lat');
                const ln = localStorage.getItem('pr_lng');
                const nm = localStorage.getItem('pr_name');
                if (la && ln) {
                    this.lat = parseFloat(la); this.lng = parseFloat(ln);
                    this.locationName = nm || 'Saved location';
                    this.load();
                } else {
                    this.tryGPS();
                }
            },

            tryGPS() {
                if (!navigator.geolocation) { this.fromIP(); return; }
                this.locationName = 'Requesting GPS...';
                navigator.geolocation.getCurrentPosition(
                    async p => {
                        this.lat = p.coords.latitude; this.lng = p.coords.longitude;
                        this.locationName = 'GPS (' + this.lat.toFixed(2) + '°, ' + this.lng.toFixed(2) + '°)';
                        // Reverse geocode to get city name
                        try {
                            const url = 'https://nominatim.openstreetmap.org/reverse?lat=' + this.lat + '&lon=' + this.lng + '&format=json';
                            const g = await (await fetch(url)).json();
                            const addr = g.address || {};
                            const city = addr.city || addr.town || addr.village || addr.county || '';
                            const country = addr.country || '';
                            if (city || country) {
                                this.locationName = '📍 ' + [city, country].filter(Boolean).join(', ');
                            } else {
                                this.locationName = '📍 ' + (g.display_name || '').split(',').slice(0,2).join(',').trim();
                            }
                        } catch {
                            this.locationName = '📍 GPS (' + this.lat.toFixed(3) + ', ' + this.lng.toFixed(3) + ')';
                        }
                        this.saveLoc(); this.load();
                    },
                    () => this.fromIP(),
                    { timeout: 8000 }
                );
            },

            async fromIP() {
                this.locationName = 'Detecting via IP...';
                try {
                    const d = await (await fetch('https://ipapi.co/json/')).json();
                    if (d.latitude) {
                        this.lat = d.latitude; this.lng = d.longitude;
                        const city = d.city || d.region || '';
                        const country = d.country_name || '';
                        this.locationName = '🌐 ' + [city, country].filter(Boolean).join(', ') + ' (estimated)';
                        this.saveLoc(); this.load();
                    } else { this.locationName = 'Location unknown'; this.isLoading = false; }
                } catch { this.locationName = 'Location unavailable'; this.isLoading = false; }
            },

            forceGPS() {
                ['pr_lat','pr_lng','pr_name'].forEach(k => localStorage.removeItem(k));
                this.locationName = 'Requesting GPS...'; this.searchErr = ''; this.tryGPS();
            },

            async searchLocation() {
                if (!this.searchQ.trim()) return;
                this.searching = true; this.searchErr = '';
                try {
                    const url = 'https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(this.searchQ) + '&format=json&limit=1';
                    const d = await (await fetch(url)).json();
                    if (d && d.length) {
                        this.lat = parseFloat(d[0].lat); this.lng = parseFloat(d[0].lon);
                        this.locationName = d[0].display_name.split(',').slice(0,2).join(',').trim();
                        this.saveLoc(); this.load();
                    } else { this.searchErr = 'City not found. Try again.'; }
                } catch { this.searchErr = 'Search failed. Try again.'; }
                this.searching = false;
            },

            saveLoc() {
                localStorage.setItem('pr_lat', this.lat);
                localStorage.setItem('pr_lng', this.lng);
                localStorage.setItem('pr_name', this.locationName);
            },

            async load() {
                if (!this.lat || !this.lng) return;
                this.isLoading = true;
                const mmap = { MuslimWorldLeague:3, Egyptian:5, Karachi:1, UmmAlQura:4, Dubai:16, MoonsightingCommittee:15, NorthAmerica:2, Kuwait:9, Qatar:10, Singapore:11, Tehran:7, Turkey:13 };
                const m = mmap[this.calcMethod] || 1;
                const sc = this.madhab === 'hanafi' ? 1 : 0;
                try {
                    const url = 'https://api.aladhan.com/v1/timings/today?latitude=' + this.lat + '&longitude=' + this.lng + '&method=' + m + '&school=' + sc;
                    const d = await (await fetch(url)).json();
                    const t = d.data.timings;
                    this.times = {
                        Fajr: this.hm(t.Fajr), Sunrise: this.hm(t.Sunrise),
                        Dhuhr: this.hm(t.Dhuhr), Asr: this.hm(t.Asr),
                        Maghrib: this.hm(t.Maghrib), Isha: this.hm(t.Isha),
                    };
                    this.finish();
                } catch { this.isLoading = false; }
            },

            hm(str) {
                const [h, m] = str.replace(/\s*\(.*\)/, '').split(':');
                const d = new Date(); d.setHours(parseInt(h), parseInt(m), 0, 0); return d;
            },

            finish() {
                this.fmt = {};
                for (const [n, dt] of Object.entries(this.times))
                    this.fmt[n] = dt.toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' });
                this.calcNext(); this.isLoading = false;
            },

            calcNext() {
                const now = new Date();
                const order = ['Fajr','Sunrise','Dhuhr','Asr','Maghrib','Isha'];
                let found = null;
                for (const n of order) { if (this.times[n] > now) { found = n; break; } }
                if (!found) {
                    found = 'Fajr';
                    this.times.Fajr = new Date(this.times.Fajr.getTime() + 86400000);
                    this.fmt.Fajr = this.times.Fajr.toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' });
                }
                this.nextName = found; this.nextTime = this.fmt[found]; this.nextDt = this.times[found];
                this.setBg();
                if (this.timer) clearInterval(this.timer);
                this.tick();
                this.timer = setInterval(() => this.tick(), 1000);
            },

            tick() {
                if (!this.nextDt) return;
                const d = this.nextDt - new Date();
                if (d <= 0) { this.load(); return; }
                const h = Math.floor(d/3600000), m = Math.floor((d%3600000)/60000), s = Math.floor((d%60000)/1000);
                this.countdown = String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
            },

            setBg() {
                const map = {
                    Fajr:'from-slate-900 via-blue-950 to-black',
                    Sunrise:'from-indigo-900 via-violet-700 to-orange-700',
                    Dhuhr:'from-sky-500 via-blue-600 to-cyan-800',
                    Asr:'from-sky-400 via-orange-400 to-amber-600',
                    Maghrib:'from-orange-500 via-red-600 to-purple-900',
                    Isha:'from-indigo-900 via-violet-950 to-slate-900'
                };
                this.bgFrom = map[this.nextName] || 'from-slate-900 via-slate-800 to-slate-900';
            },

            emo(n) {
                return { Fajr:'🌙', Sunrise:'🌅', Dhuhr:'☀️', Asr:'🌤️', Maghrib:'🌆', Isha:'🌃' }[n] || '🕌';
            },

            applySettings() { this.showModal = false; this.load(); }
        }));
    }

    // Handle both first page load and Livewire wire:navigate
    if (window.Alpine) {
        registerPrayerWidget();
    } else {
        document.addEventListener('alpine:init', registerPrayerWidget, { once: true });
    }
})();
</script>

<div x-data="prayerWidget()" class="max-w-7xl mx-auto space-y-8">

    {{-- Page Header --}}
    <div>
        <h1 class="text-3xl font-extrabold text-base-content">{{ __('Dashboard') }}</h1>
        <p class="text-base-content/50 italic text-sm mt-1">{{ __('"Waste no more time arguing what a good man should be, be one." — Marcus Aurelius') }}</p>
    </div>

    {{-- Prayer Times Card --}}
    <div class="relative rounded-3xl overflow-hidden shadow-2xl text-white min-h-[240px]">
        <div class="absolute inset-0 bg-gradient-to-br transition-all duration-1000" :class="bgFrom"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row divide-y lg:divide-y-0 lg:divide-x divide-white/10">

            {{-- Left: Location + Next Prayer + Countdown --}}
            <div class="flex-none lg:w-80 p-6 md:p-8 flex flex-col gap-5 justify-between">

                <div class="flex items-center justify-between">
                    <button @click="showModal = true" class="flex items-center gap-2 group min-w-0">
                        <svg class="w-4 h-4 text-white/60 group-hover:text-white shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="text-xs font-bold text-white/70 group-hover:text-white uppercase tracking-widest truncate transition-colors" x-text="locationName"></span>
                    </button>
                    <button @click="showModal = true" class="p-1.5 rounded-xl hover:bg-white/15 transition-colors text-white/60 hover:text-white" title="{{ __('Settings') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </button>
                </div>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-white/50 mb-2">{{ __('Upcoming Prayer') }}</p>
                    <div class="flex items-baseline gap-2 flex-wrap">
                        <span class="text-4xl md:text-5xl font-black tracking-tight" x-text="nextName"></span>
                        <span class="text-xl font-bold text-white/70" x-text="nextTime"></span>
                    </div>
                </div>

                <div class="inline-flex items-center gap-3 bg-black/30 backdrop-blur-sm border border-white/10 rounded-2xl px-4 py-3 w-max">
                    <div x-show="isLoading" class="loading loading-spinner loading-sm text-white/70"></div>
                    <svg x-show="!isLoading" class="w-4 h-4 text-white/50 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-mono text-xl font-extrabold tracking-widest" x-text="countdown"></span>
                    <span class="text-[9px] font-bold uppercase tracking-[0.2em] text-white/40">{{ __('left') }}</span>
                </div>
            </div>

            {{-- Right: Prayer Times Grid --}}
            <div class="flex-1 p-6 md:p-8 relative">
                <div x-show="isLoading && Object.keys(fmt).length === 0" class="absolute inset-0 flex items-center justify-center">
                    <div class="loading loading-spinner loading-lg text-white/50"></div>
                </div>
                <div class="grid grid-cols-3 xl:grid-cols-6 gap-3 h-full items-center">
                    <template x-for="(time, name) in fmt" :key="name">
                        <div class="flex flex-col items-center justify-center py-4 px-2 rounded-2xl border backdrop-blur-sm transition-all duration-500"
                            :class="nextName === name ? 'bg-white/25 border-white/40 shadow-lg scale-105' : 'bg-black/20 border-white/10 hover:bg-white/10 hover:border-white/20'">
                            <span class="text-2xl mb-2 leading-none" x-text="emo(name)"></span>
                            <span class="text-[9px] font-bold uppercase tracking-widest text-white/50 mb-1" x-text="name"></span>
                            <span class="text-sm font-extrabold" x-text="time"></span>
                            <div x-show="nextName === name" class="mt-2">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Settings Modal (INSIDE same x-data scope) --}}
    <div class="modal" :class="{'modal-open': showModal}">
        <div class="modal-box rounded-3xl max-w-md">

            <h3 class="font-bold text-xl mb-1">{{ __('⚙️ Prayer Settings') }}</h3>
            <p class="text-sm text-base-content/50 mb-6">{{ __('Customize your location and calculation method') }}</p>

            <div class="space-y-6">

                {{-- Location --}}
                <div class="space-y-3">
                    <p class="text-xs font-bold uppercase tracking-widest text-base-content/40 border-b border-base-content/10 pb-2">📍 {{ __('Location') }}</p>

                    <div class="grid grid-cols-2 gap-2">
                        <button @click="forceGPS()" class="btn btn-primary btn-sm rounded-xl">{{ __('🛰 Use GPS') }}</button>
                        <button @click="fromIP()" class="btn btn-outline btn-sm rounded-xl">{{ __('🌐 Auto (IP)') }}</button>
                    </div>

                    <div class="divider text-xs text-base-content/30">{{ __('or search manually') }}</div>

                    <div class="flex gap-2">
                        <input type="text" x-model="searchQ" @keydown.enter="searchLocation()" placeholder="{{ __('e.g. Dhaka, Bangladesh') }}" class="input input-bordered input-sm flex-1 rounded-xl text-sm" />
                        <button @click="searchLocation()" class="btn btn-secondary btn-sm rounded-xl px-4" :disabled="searching">
                            <span x-show="!searching">{{ __('Go') }}</span>
                            <span x-show="searching" class="loading loading-spinner loading-xs"></span>
                        </button>
                    </div>
                    <p class="text-xs text-error" x-show="searchErr" x-text="searchErr"></p>

                    <div class="text-xs font-semibold text-base-content/60 bg-base-200 rounded-xl px-3 py-2">
                        {{ __('Current:') }} <span class="text-primary" x-text="locationName"></span>
                    </div>
                </div>

                {{-- Calculation Engine --}}
                <div class="space-y-3">
                    <p class="text-xs font-bold uppercase tracking-widest text-base-content/40 border-b border-base-content/10 pb-2">{{ __('🧮 Calculation') }}</p>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all" :class="calcSource==='api' ? 'border-primary bg-primary/10' : 'border-base-content/10'">
                            <input type="radio" name="pr_src" value="api" x-model="calcSource" class="radio radio-primary radio-sm" />
                            <div><p class="text-sm font-bold">{{ __('API') }}</p><p class="text-[10px] text-base-content/40">{{ __('Aladhan, online') }}</p></div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all" :class="calcSource==='local' ? 'border-primary bg-primary/10' : 'border-base-content/10'">
                            <input type="radio" name="pr_src" value="local" x-model="calcSource" class="radio radio-primary radio-sm" />
                            <div><p class="text-sm font-bold">{{ __('Local') }}</p><p class="text-[10px] text-base-content/40">{{ __('JS, offline') }}</p></div>
                        </label>
                    </div>

                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-sm font-bold">{{ __('Calculation Method') }}</span></label>
                        <select x-model="calcMethod" class="select select-bordered select-sm rounded-xl">
                            <option value="Karachi">{{ __('Univ. of Islamic Sciences, Karachi') }}</option>
                            <option value="MuslimWorldLeague">{{ __('Muslim World League') }}</option>
                            <option value="Egyptian">{{ __('Egyptian General Authority') }}</option>
                            <option value="UmmAlQura">{{ __('Umm al-Qura, Makkah') }}</option>
                            <option value="Dubai">{{ __('Dubai') }}</option>
                            <option value="MoonsightingCommittee">{{ __('Moonsighting Committee') }}</option>
                            <option value="NorthAmerica">{{ __('ISNA (North America)') }}</option>
                            <option value="Kuwait">{{ __('Kuwait') }}</option>
                            <option value="Qatar">{{ __('Qatar') }}</option>
                            <option value="Singapore">{{ __('Singapore') }}</option>
                            <option value="Tehran">{{ __('Tehran') }}</option>
                            <option value="Turkey">{{ __('Turkey') }}</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-sm font-bold">{{ __('Asr Time (Madhab)') }}</span></label>
                        <select x-model="madhab" class="select select-bordered select-sm rounded-xl">
                            <option value="hanafi">{{ __('Hanafi (later Asr)') }}</option>
                            <option value="shafi">{{ __('Shafi / Maliki / Hanbali') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-action mt-6">
                <button @click="showModal = false" class="btn btn-ghost btn-sm rounded-xl">{{ __('Cancel') }}</button>
                <button @click="applySettings()" class="btn btn-primary btn-sm rounded-xl px-6">{{ __('Apply & Reload') }}</button>
            </div>
        </div>
        <div class="modal-backdrop" @click="showModal = false"><button>{{ __('close') }}</button></div>
    </div>

    {{-- Live Quiz Widget --}}
    @livewire('quizzes.live-quiz-widget')

    {{-- Donation Widget --}}
    <div class="bg-base-100 rounded-3xl shadow-xl border border-base-200 p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-6">
            <div class="p-4 bg-primary/10 text-primary rounded-2xl hidden sm:block">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold mb-1">{{ __('Community Fund') }}</h2>
                <p class="text-base-content/70">{{ __('Support our Halaqahs and community activities through your generous donations. Consistency is loved by Allah.') }}</p>
            </div>
        </div>
        <div class="flex gap-3 w-full md:w-auto shrink-0">
            <a href="{{ route('app.donations.campaigns') }}" wire:navigate class="btn btn-outline rounded-xl flex-1 md:flex-none">{{ __('My Record') }}</a>
            <a href="{{ route('app.donations.campaigns') }}" wire:navigate class="btn btn-primary rounded-xl flex-1 md:flex-none">{{ __('Donate Now') }}</a>
        </div>
    </div>
</div>
