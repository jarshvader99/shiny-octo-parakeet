# Copilot Instructions for Congressional Consensus Platform

## Platform Mission
A **civic engagement platform** that enables informed public discourse and consensus measurement on U.S. Congressional bills. Users can review bills, submit reasoned stances (support/oppose/mixed/undecided), and participate in structured discussions around legislation.

## Architecture Overview

### Three-Layer System
1. **Ingest Layer** - Periodic scraping/syncing from Congress.gov API (minimize direct API calls)
2. **Canonical Legislative Model** - Internal source of truth for bills, versions, actors, events
3. **Social/Consensus Layer** - User stances, discussions, consensus metrics, summarization

### Tech Stack
- **Backend:** Laravel 12 (PHP 8.2+) with Jetstream for authentication, Sanctum for API auth
- **Frontend:** Vue 3 SPA via Inertia.js (SSR-like experience without API overhead), compiled with Vite
- **Styling:** Tailwind CSS v3 with forms and typography plugins
- **Routing:** Ziggy for Laravel routes in Vue components
- **Data Sync:** Laravel Queue workers for background ingestion jobs
- **Maps:** Leaflet.js for choropleth/heat maps (open source, no API keys)
- **Charts:** Chart.js or Apache ECharts for data visualization (open source)
- **Philosophy:** Prefer open source packages over commercial APIs/services

**Critical architectural decisions:**
- Inertia.js eliminates traditional REST APIs for web views - controllers return Inertia responses with props
- **Users never hit Congress.gov API directly** - all bill data is cached locally and synced periodically
- Store legislative changes as immutable **events**, not just current state (enables timeline/audit features)
- **Open source first:** Use open source libraries for maps, charts, and UI components - avoid vendor lock-in

## Development Roadmap

### Phase 1: User Foundation (Weeks 1-2)
**Goal:** Get users into the system with required context

**Tasks:**
1. **Extend User model** - Add `zip_code`, `congressional_district` fields
2. **Onboarding flow** - Post-registration ZIP code collection
3. **ZIP → District lookup** - Service/API to derive congressional district from ZIP
4. **Profile management** - Allow users to update ZIP code
5. **Basic verification** - Email verification (already in Jetstream)

**Deliverables:**
- `database/migrations/xxxx_add_location_to_users_table.php`
- `resources/js/Pages/Onboarding/ZipCode.vue`
- `app/Services/CongressionalDistrictService.php`
- Updated `resources/js/Pages/Profile/UpdateProfileInformationForm.vue`

**Definition of Done:**
- New users must provide ZIP before accessing dashboard
- Congressional district auto-populates and displays in profile
- Existing users prompted for ZIP on first login after migration

---

### Phase 2: Data Ingestion Layer (Weeks 2-4)
**Goal:** Establish internal bill database without user-facing API calls

**Tasks:**
1. **Bill model + migrations** - Core legislative entity schema
2. **BillVersion model** - Track text changes over time
3. **BillEvent model** - Immutable event log (introduced, amended, etc.)
4. **BillActor model** - Sponsors, cosponsors, committees
5. **Congress.gov sync job** - `app/Jobs/SyncBillsFromCongressJob.php`
6. **Change detection job** - `app/Jobs/DetectBillChangesJob.php`
7. **Queue configuration** - Ensure jobs run in background

**Deliverables:**
- `app/Models/Bill.php` with relationships
- `app/Models/BillVersion.php`, `BillEvent.php`, `BillActor.php`
- `app/Jobs/SyncBillsFromCongressJob.php` (batch API calls, rate-limited)
- `app/Jobs/DetectBillChangesJob.php` (periodic scraping/checking)
- `database/migrations/xxxx_create_bills_tables.php`
- Admin command: `php artisan bills:sync` for manual triggering

**Definition of Done:**
- Can run `php artisan bills:sync` to fetch latest bills from Congress.gov API
- Bills stored with congress number, chamber, bill number, status, timestamps
- BillEvents capture all state changes with source attribution
- Queue workers process sync jobs in background during `composer run dev`

---

### Phase 3: Bill Discovery & Dashboard (Weeks 4-6)
**Goal:** Users can browse bills, prioritizing local relevance

**Tasks:**
1. **Dashboard controller** - Fetch bills relevant to user's district
2. **Bill index page** - List view with filtering/search
3. **Bill detail page** - Single bill view with full context
4. **Local bill algorithm** - Logic to determine "local relevance"
5. **Bill card component** - Reusable UI component
6. **Search/filter UI** - Keywords, status, chamber, sponsor

**Deliverables:**
- `app/Http/Controllers/BillController.php` (index, show methods)
- `resources/js/Pages/Dashboard.vue` (local bills prioritized)
- `resources/js/Pages/Bills/Index.vue` (all bills, searchable)
- `resources/js/Pages/Bills/Show.vue` (single bill detail)
- `resources/js/Components/BillCard.vue` (card component)
- `app/Services/LocalBillService.php` (determines relevance)

**Definition of Done:**
- Dashboard shows bills from user's representatives at top
- National bills appear below local content
- Bill detail page shows: title, summary, status, sponsors, last action
- Search works by keyword, filter by status/chamber
- All data comes from internal database, not external API

---

### Phase 4: User Stances & Voting (Weeks 6-7)
**Goal:** Users can submit and revise their positions on bills

**Tasks:**
1. **UserStance model** - Support/Oppose/Mixed/Undecided/NeedsMoreInfo
2. **Stance submission form** - Modal or inline component
3. **Stance history tracking** - Revision support
4. **Reason requirement** - Text field for substantive explanation
5. **Stance display** - Show user's current stance on bill page
6. **Consensus preview** - Aggregate stats on bill page

**Deliverables:**
- `app/Models/UserStance.php` (with zip_code snapshot)
- `database/migrations/xxxx_create_user_stances_table.php`
- `resources/js/Components/StanceForm.vue` (form component)
- `resources/js/Components/StanceDisplay.vue` (show user's stance)
- `app/Http/Controllers/StanceController.php` (store, update)
- Route: `POST /bills/{bill}/stances`

**Definition of Done:**
- Users can select stance (5 options) with required reason text
- Stance includes ZIP code snapshot at time of submission
- Users can revise stance; history is preserved
- Bill page shows user's current stance if exists
- Basic aggregate stats shown (X% support, Y% oppose)

---

### Phase 5: Discussion Threads (Weeks 7-9)
**Goal:** Structured, bill-centric conversations

**Tasks:**
1. **Discussion model** - Threaded comments under bills
2. **Discussion sections** - KeyQuestions, ArgumentsFor, ArgumentsAgainst, etc.
3. **Comment threading** - Nested replies
4. **Discussion UI** - Collapsible sections, reply forms
5. **Moderation hooks** - Flag inappropriate content (basic)
6. **"Bill changed" warnings** - Alert when discussing outdated version

**Deliverables:**
- `app/Models/Discussion.php` (polymorphic or bill-scoped)
- `app/Models/Comment.php` (nested set or closure table)
- `database/migrations/xxxx_create_discussions_table.php`
- `resources/js/Components/DiscussionThread.vue`
- `resources/js/Components/CommentForm.vue`
- `app/Http/Controllers/DiscussionController.php`

**Definition of Done:**
- Bill page has tabs/sections for different discussion types
- Users can post top-level comments and replies
- Discussions show timestamps relative to bill version
- Warning shown if bill amended since comment posted
- Basic flagging system for moderation

---

### Phase 6: Consensus Metrics & Visualizations (Weeks 9-10)
**Goal:** Make aggregate sentiment understandable and trustworthy

**Tasks:**
1. **Consensus calculation service** - Weighted vs. raw sentiment
2. **Chart components** - Donut charts for stance breakdown
3. **Timeline visualization** - Bill progress over time
4. **Trend analysis** - Consensus shifts after amendments
5. **Verified vs. unverified** - Separate metrics (future prep)

**Deliverables:**
- `app/Services/ConsensusMetricsService.php`
- `resources/js/Components/ConsensusChart.vue` (Chart.js integration)
- `resources/js/Components/BillTimeline.vue` (progress visualization)
- `resources/js/Components/ConsensusTrend.vue` (shifts over time)
- Install Chart.js: `npm install chart.js`

**Definition of Done:**
- Bill page shows donut chart of stance distribution
- Timeline shows bill's journey through Congress
- Consensus metrics distinguish "engaged" vs. "raw" sentiment
- All charts are accessible (ARIA labels, keyboard nav)
- Data freshness indicators on all metrics

---

### Phase 7: Geographic Heat Maps (Weeks 10-12)
**Goal:** Visualize regional consensus patterns

**Tasks:**
1. **Geographic aggregation service** - Group stances by district/state
2. **Leaflet integration** - Install and configure vue-leaflet
3. **Choropleth component** - Color-coded districts
4. **GeoJSON data** - Congressional district boundaries
5. **Privacy safeguards** - Never expose individual locations
6. **Sample size indicators** - Show n per region

**Deliverables:**
- `app/Services/GeographicConsensusService.php`
- `resources/js/Components/ConsensusHeatMap.vue` (Leaflet integration)
- Install Leaflet: `npm install leaflet vue-leaflet`
- `public/geojson/congressional-districts.json` (boundary data)
- Heat map page or tab on bill detail

**Definition of Done:**
- Bill detail page has "Regional Consensus" tab
- Heat map shows color intensity by support/oppose levels
- Aggregate by congressional district or state only
- Shows sample size per region (e.g., "234 responses")
- Neutral color palette (no red/blue partisan colors)
- Fully functional with keyboard navigation

---

### Phase 8: Polish & Launch Prep (Weeks 12-14)
**Goal:** Production-ready platform

**Tasks:**
1. **Amendment change detection** - Alert users when bills change
2. **Email notifications** - Followed bills, discussions
3. **Onboarding tour** - Help new users understand platform
4. **Trust indicators** - Data freshness, source attribution everywhere
5. **Performance optimization** - Query optimization, caching
6. **Mobile responsive** - Test all flows on mobile devices
7. **Accessibility audit** - WCAG AA compliance
8. **Analytics** - Privacy-respecting usage tracking

**Deliverables:**
- `app/Notifications/BillAmendedNotification.php`
- User follow system for bills
- Onboarding tour component
- Performance improvements
- Mobile testing checklist
- Accessibility testing report

**Definition of Done:**
- Users notified when followed bills change
- All pages work seamlessly on mobile
- WCAG AA compliant
- < 2s page load times
- Production deployment successful

---

## Current Phase
**Phase 1: User Foundation** - Start here. All development should follow this sequence.

## Project Structure

### Domain Models (Future)
- `app/Models/Bill.php` - Core legislative entity (congress_number, chamber, bill_number, status, affected_regions)
- `app/Models/BillVersion.php` - Versioned text snapshots with diffs
- `app/Models/BillEvent.php` - Immutable change log (introduced, amended, voted, reported)
- `app/Models/BillActor.php` - Sponsors, cosponsors, committees
- `app/Models/UserStance.php` - Support/Oppose/Mixed/Undecided with reason text, includes user's zip_code at time of stance
- `app/Models/Discussion.php` - Bill-centric threaded conversations
- `app/Models/User.php` - Extended with zip_code, congressional_district, verification_level (basic/id_verified)

### Application Structure
- `app/Http/Controllers/` - Nearly empty (Jetstream provides auth; will add BillController, DiscussionController)
- `app/Jobs/` - Background ingestion jobs (SyncBillsJob, DetectChangesJob)
- `resources/js/Pages/` - Vue page components (Bills/, Discussions/, Dashboard, Auth/, Profile/)
- `resources/js/Components/` - Reusable Vue components (BillCard, StanceForm, ConsensusMetrics)
- `routes/web.php` - Web routes return `Inertia::render()` with bill/discussion data
- `routes/api.php` - Internal API for AJAX requests (vote submission, comment posting)
- `database/migrations/` - Schema for bills, versions, events, stances, discussions

## Development Workflow

### Initial Setup
```bash
composer run setup  # One-command setup: install deps, generate key, migrate, build assets
```

### Daily Development (critical workflow)
```bash
composer run dev  # Orchestrates ALL dev services concurrently:
                  # - Laravel server (localhost:8000)
                  # - Queue worker
                  # - Log viewer (Pail)
                  # - Vite HMR server (port 5173)
```
**Never run `php artisan serve` or `npm run dev` individually** - always use `composer run dev` to ensure queue workers and logs are running.

### Testing
```bash
composer run test  # Clears config cache, runs PHPUnit tests
php artisan test   # Alternative direct command
```

## Code Conventions

### Data Ingestion Patterns
- **Jobs in `app/Jobs/`** run via queue workers (active during `composer run dev`)
- Store changes as **events**, not state overwrites - use `BillEvent` model
- Track data freshness with `last_synced_at`, `confidence_score` fields
- Always include `source` metadata (api/scrape) and timestamps
- Rate-limit external API calls - batch operations, use exponential backoff

### Legislative Data Models
- **Bill identification:** `congress_number` + `chamber` + `bill_number` (e.g., 119th Congress, House, H.R. 1234)
- **Versioning:** Store full text + diffs in `BillVersion`, never overwrite
- **Status tracking:** Use enums (introduced, committee, floor, passed, failed)
- **Link back to source:** Always include Congress.gov URL, last_updated timestamp

### User Engagement Patterns
- **Stances are not binary votes:** Support/Oppose/Mixed/Undecided/NeedsMoreInfo
- Include `reason` text with every stance (required for quality consensus)
- Track stance history - users can revise opinions as bills evolve
- Discussions are **bill-centric** - thread under sections (KeyQuestions, ArgumentsFor, ArgumentsAgainst)
- Show "Bill has changed since you last viewed" warnings after amendments

### Geographic/Location Features
- **ZIP code is self-submitted** during registration/onboarding (required for dashboard)
- Derive congressional_district from ZIP code using lookup table/service
- Store zip_code snapshot with each stance (enables heat map generation)
- **Dashboard prioritizes local bills:**
  - Bills sponsored by user's representatives
  - Bills affecting user's congressional district/state
  - National bills shown below local content
- **Heat maps:** Aggregate stances by ZIP/district to visualize geographic consensus patterns
- Never expose individual user locations - only aggregated geographic data

### User Verification Strategy (Future)
- **Tiered approach:** Basic (email+phone) vs. ID-verified (ID.me/Persona)
- All users can participate; verification is optional for "Verified Constituent" badge
- Track `verification_level` and `verification_provider` in User model
- Weight verified users higher in consensus metrics
- Focus: prevent duplicates and ensure US residency, not mandatory ID requirements

### Frontend (Vue/Inertia)
- **Page components** in `resources/js/Pages/` correspond to routes
- Use `Inertia::render('PageName', $data)` in controllers - data becomes props
- Access Laravel routes in Vue: `route('route.name')` via Ziggy
- Form submissions: Use Inertia's `form.post()`, `form.put()`, etc. (handles CSRF automatically)

Example route → page mapping:
```UI/UX Priorities

### Dashboard Design
- **Primary view:** Local bills affecting user's district/state (based on ZIP code)
- **Bill cards** show: title, short summary, sponsor, status, local relevance indicator
- **Consensus visualizations:** Show aggregate stance breakdown (support/oppose/mixed/undecided)
- **Heat map integration:** Geographic heatmap showing regional consensus on selected bills
- Use Tailwind for responsive design - mobile-first approach

### Visual Hierarchy
- Local/relevant bills → Top of dashboard
- Trending national bills → Secondary section
- Search/browse all bills → Tertiary/sidebar
- User's recent stances → Profile section

### Geographic Visualizations
- **Use Leaflet.js** for all map visualizations (open source, no API keys required)
- Heat maps use choropleth visualization (color intensity by consensus level)
- Aggregate by congressional district or state (never individual ZIP codes)
- Show sample size per region (e.g., "234 constituents responded")
- Use neutral color schemes - avoid red/blue political associations
- GeoJSON data for district boundaries (available from US Census Bureau)

### Data Visualization
- **Use Chart.js or Apache ECharts** for charts and graphs (open source)
- Consensus breakdown: donut/pie charts showing stance distribution
- Timeline charts for bill progress tracking
- Trend lines for consensus shifts over time
- All visualizations must be accessible (ARIA labels, keyboard navigation)

## Design System & Styling

### Core Design Philosophy
**"Calm, credible, quietly engaging"** - This is a space for thinking, not reacting. Users should feel safe to read, invited to think, comfortable disagreeing, and not rushed.

### Color Palette (Tailwind)
**Neutral-First Approach:**
- Base: `slate-950` or `zinc-950` backgrounds
- Surface: `slate-900` panels and cards
- Borders: `slate-800` subtle dividers
- Text primary: `slate-100` high contrast
- Text secondary: `slate-400` supporting text

**Muted Accents (color as signal, not decoration):**
- Support stance: `emerald-500/60` muted green
- Oppose stance: `rose-500/60` muted red
- Mixed/Neutral: `amber-500/60` or `stone-500`
- Links/actions: `indigo-600` or `blue-600`

**Why:** Low saturation reduces cognitive load; color only appears when conveying meaning.

### Motion Design
**Micro, purposeful, optional:**
- Use: `transition-all duration-150 ease-out` for hover states
- Use: subtle scale transforms (`hover:scale-[1.02]`)
- Use: opacity transitions for state changes
- Always include: `motion-reduce:transition-none` to respect user preferences
- **Avoid:** bouncing, parallax, auto-play animations, shimmer skeletons

### Layout & Readability
**Max-width for long-form content:**
```vue
<div class="max-w-3xl mx-auto lg:max-w-4xl">
```
- Contain bill text, discussions, and policy content for comfortable reading
- Use `space-y-6` for vertical rhythm between sections
- Use `leading-relaxed` or `leading-loose` for text-heavy content
- Readable first, dense second

### Component Styling Patterns

**Buttons:**
```vue
<button class="px-4 py-2 transition-colors rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-100">
```
- Soft edges (`rounded-lg`), no gradients, subtle hover only
- Primary buttons should be rare - prefer secondary/ghost styles

**Voting/Stance Controls:**
```vue
<!-- Support -->
<button class="border-2 border-emerald-600/60 text-emerald-400 hover:bg-emerald-600/10">
<!-- Oppose -->
<button class="border-2 border-rose-600/60 text-rose-400 hover:bg-rose-600/10">
```
- Use segmented controls or outlined buttons with icon + label
- No gamification, counters, or confetti - civic engagement, not social media likes

**Cards:**
```vue
<div class="p-6 border shadow-sm bg-slate-900 border-slate-800 rounded-xl">
```
- Cards should feel like paper, not widgets
- Avoid heavy drop shadows

### Typography
**Font Strategy:**
- UI/Navigation: Sans-serif (Inter, system fonts via Tailwind defaults)
- Bill text/summaries: Serif fonts (`font-serif` - signals "slow down and read")
- Configure in `tailwind.config.js`:
```js
fontFamily: {
  sans: ['Inter', ...defaultTheme.fontFamily.sans],
  serif: ['Source Serif 4', ...defaultTheme.fontFamily.serif],
}
```

### Engagement Without Overstimulation
**Informational nudges, not dopamine hooks:**
- Use subtle badges for "Bill changed since last visit"
- Inline alerts over banner notifications
- Prompts like "3 viewpoints you haven't seen" instead of "New comments!"
- Small colored dots or discrete indicators for status changes

### Accessibility & Trust Signals
**Required:**
- Visible focus states (`focus:ring-2 focus:ring-indigo-500`)
- High contrast text (minimum WCAG AA)
- Keyboard navigation for all interactive elements
- ARIA labels on all charts, maps, and complex interactions

**Trust UI Elements:**
- Show "Last updated" timestamps on all bill data
- Source attribution (Congress.gov links, bill numbers)
- Change logs when bills are amended
- Data freshness indicators ("Synced 2 hours ago")

### Tailwind Theme Tokens
Define semantic color tokens in `tailwind.config.js`:
```js
theme: {
  extend: {
    colors: {
      surface: '#0f172a',
      panel: '#020617',
      accent: '#64748b',
      support: '#10b981',
      oppose: '#ef4444',
    }
  }
}
```
Use tokens consistently - never hard-code raw colors in components.

**Success metric:** Users read for 10+ minutes, forget they're "on social media", and feel smarter not angrier.

## Common Patterns

### Creating a new page
1. Add route in `routes/web.php`: `Route::get('/my-page', fn() => Inertia::render('MyPage'))`
2. Create `resources/js/Pages/MyPage.vue`
3. No API needed - pass data as props from controller

### Working with forms
```vue
<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
})

const submit = () => {
  form.post(route('endpoint'), {
    onSuccess: () => form.reset(),
  })
}
</script>
```

### Backend (Laravel)
- **Models** use standard Eloquent conventions (see `app/Models/User.php`)
- **Middleware groups:** Use `auth:sanctum`, `verified` for protected routes (see web.php line 16-20)
- **Database:** Migrations in `database/migrations/`, factories in `database/factories/`
- **Jobs/Queues:** Will run automatically when `composer run dev` is active

### Authentication
- Jetstream handles registration, login, 2FA, profile, API tokens
- Auth views in `resources/js/Pages/Auth/` and `Pages/Profile/`
- Sanctum for API token-based auth (see `routes/api.php`)
- Do NOT build custom auth - extend Jetstream actions in `app/Actions/Fortify/` or `app/Actions/Jetstream/`

### Adding Tailwind utilities
Extend theme in `tailwind.config.js` - purging configured for `.blade.php`, `.vue`, and Jetstream vendor files.
```vue
<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
})

const submit = () => {
  form.post(route('endpoint'), {
    onSuccess: () => form.reset(),
  })
}
</script>
```

### Adding Tailwind utilities
Extend theme in `tailwind.config.js` - purging configured for `.blade.php`, `.vue`, and Jetstream vendor files.

## Critical Guardrails

### What This Platform Is NOT
- Not legal advice or legislative prediction
- Not endorsement of bills or positions
- Not a voting recommendation engine
- Platform measures **informed consensus**, not popularity

### Data Integrity Rules
- Always cite: bill number, congress number, last_updated timestamp
- Link to official Congress.gov sources
- Highlight when cached data is stale (> 24hrs for active bills)
- Show clear warnings when bills have been amended since user's last view

### Backend (PHP/Composer)
- **Tightenco Ziggy:** Exposes Laravel routes to frontend (`vendor/tightenco/ziggy`)
- **Laravel Pint:** PHP code style fixer (use `./vendor/bin/pint`)
- **Laravel Pail:** Real-time log viewer (runs in dev mode)

### Frontend (NPM) - Open Source Only
- **Leaflet.js:** Interactive maps and choropleth visualizations
- **Chart.js or Apache ECharts:** Data visualization and charts
- **vue-leaflet:** Vue 3 wrapper for Leaflet (if needed)
- Install with: `npm install leaflet chart.js`
- Avoid: Google Maps API, Mapbox (paid), proprietary charting libraries
- Enforce civility standards in discussions
- Require substantive reasons for stances (not just "I agree")
- Flag disinformation that contradicts official bill text
- Separate "raw sentiment" from "engaged/informed consensus"

## External Dependencies
- **Tightenco Ziggy:** Exposes Laravel routes to frontend (`vendor/tightenco/ziggy`)
- **Laravel Pint:** PHP code style fixer (use `./vendor/bin/pint`)
- **Laravel Pail:** Real-time log viewer (runs in dev mode)

## Testing Guidelines
- Feature tests in `tests/Feature/` cover Jetstream flows (see ApiTokenPermissionsTest, AuthenticationTest)
- Tests use `Illuminate\Foundation\Testing\RefreshDatabase` trait
- Run specific test: `php artisan test --filter=TestName`

## Key Files for Reference
- [vite.config.js](vite.config.js) - Asset bundling config
- [tailwind.config.js](tailwind.config.js) - Styling framework config
- [config/jetstream.php](config/jetstream.php) - Auth stack config (Inertia mode)
- [resources/js/app.js](resources/js/app.js) - Frontend entry point
- [routes/web.php](routes/web.php) - Primary routing file
