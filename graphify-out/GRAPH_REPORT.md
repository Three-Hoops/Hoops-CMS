# Graph Report - laravel/resources/js + laravel/app + laravel/tests  (2026-04-20)

## Corpus Check
- 73 files · ~6,276 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 149 nodes · 116 edges · 65 communities detected
- Extraction: 69% EXTRACTED · 31% INFERRED · 0% AMBIGUOUS · INFERRED: 36 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- [[_COMMUNITY_Auth Middleware Tests|Auth Middleware Tests]]
- [[_COMMUNITY_Auth Controller & Session|Auth Controller & Session]]
- [[_COMMUNITY_Login Request & User Prefs|Login Request & User Prefs]]
- [[_COMMUNITY_Session Security Middleware|Session Security Middleware]]
- [[_COMMUNITY_Rate Limiting Tests|Rate Limiting Tests]]
- [[_COMMUNITY_Inertia Middleware & Layout|Inertia Middleware & Layout]]
- [[_COMMUNITY_User Preference Tests|User Preference Tests]]
- [[_COMMUNITY_Placeholder Tests|Placeholder Tests]]
- [[_COMMUNITY_Application Bootstrap|Application Bootstrap]]
- [[_COMMUNITY_Locale & i18n Tests|Locale & i18n Tests]]
- [[_COMMUNITY_User Role Permissions|User Role Permissions]]
- [[_COMMUNITY_Security Headers Middleware|Security Headers Middleware]]
- [[_COMMUNITY_Role Authorization Middleware|Role Authorization Middleware]]
- [[_COMMUNITY_Dashboard Controller|Dashboard Controller]]
- [[_COMMUNITY_Security Headers Tests|Security Headers Tests]]
- [[_COMMUNITY_App Entry Point|App Entry Point]]
- [[_COMMUNITY_Theme Mode Composable|Theme Mode Composable]]
- [[_COMMUNITY_UI Utility Functions|UI Utility Functions]]
- [[_COMMUNITY_Base Controller|Base Controller]]
- [[_COMMUNITY_Test Base Class|Test Base Class]]
- [[_COMMUNITY_Ziggy Type Definitions|Ziggy Type Definitions]]
- [[_COMMUNITY_Model Type Definitions|Model Type Definitions]]
- [[_COMMUNITY_App Bootstrap Test|App Bootstrap Test]]
- [[_COMMUNITY_Login Page Tests|Login Page Tests]]
- [[_COMMUNITY_Auth Store|Auth Store]]
- [[_COMMUNITY_Card Action Component|Card Action Component]]
- [[_COMMUNITY_Card Component|Card Component]]
- [[_COMMUNITY_Card Content Component|Card Content Component]]
- [[_COMMUNITY_Card Title Component|Card Title Component]]
- [[_COMMUNITY_Card Header Component|Card Header Component]]
- [[_COMMUNITY_Card Footer Component|Card Footer Component]]
- [[_COMMUNITY_Card UI Exports|Card UI Exports]]
- [[_COMMUNITY_Card Description Component|Card Description Component]]
- [[_COMMUNITY_Input UI Exports|Input UI Exports]]
- [[_COMMUNITY_Input Component|Input Component]]
- [[_COMMUNITY_Separator UI Exports|Separator UI Exports]]
- [[_COMMUNITY_Separator Component|Separator Component]]
- [[_COMMUNITY_Label Component|Label Component]]
- [[_COMMUNITY_Label UI Exports|Label UI Exports]]
- [[_COMMUNITY_Dropdown Menu Label|Dropdown Menu Label]]
- [[_COMMUNITY_Dropdown Radio Item|Dropdown Radio Item]]
- [[_COMMUNITY_Dropdown Separator|Dropdown Separator]]
- [[_COMMUNITY_Dropdown Group|Dropdown Group]]
- [[_COMMUNITY_Dropdown Trigger|Dropdown Trigger]]
- [[_COMMUNITY_Dropdown Content|Dropdown Content]]
- [[_COMMUNITY_Dropdown Shortcut|Dropdown Shortcut]]
- [[_COMMUNITY_Dropdown Checkbox Item|Dropdown Checkbox Item]]
- [[_COMMUNITY_Dropdown Item|Dropdown Item]]
- [[_COMMUNITY_Dropdown Radio Group|Dropdown Radio Group]]
- [[_COMMUNITY_Dropdown Menu Root|Dropdown Menu Root]]
- [[_COMMUNITY_Dropdown Menu Exports|Dropdown Menu Exports]]
- [[_COMMUNITY_Dropdown Sub Content|Dropdown Sub Content]]
- [[_COMMUNITY_Dropdown Sub Trigger|Dropdown Sub Trigger]]
- [[_COMMUNITY_Dropdown Sub Root|Dropdown Sub Root]]
- [[_COMMUNITY_Button UI Exports|Button UI Exports]]
- [[_COMMUNITY_Button Component|Button Component]]
- [[_COMMUNITY_Badge Component|Badge Component]]
- [[_COMMUNITY_Badge UI Exports|Badge UI Exports]]
- [[_COMMUNITY_Flash Banner Component|Flash Banner Component]]
- [[_COMMUNITY_Admin Layout|Admin Layout]]
- [[_COMMUNITY_Home Page|Home Page]]
- [[_COMMUNITY_Dashboard Page|Dashboard Page]]
- [[_COMMUNITY_Login Page|Login Page]]
- [[_COMMUNITY_Flash Type Enum|Flash Type Enum]]
- [[_COMMUNITY_Theme Mode Enum|Theme Mode Enum]]

## God Nodes (most connected - your core abstractions)
1. `User` - 26 edges
2. `auth()` - 7 edges
3. `LoginTest` - 7 edges
4. `LoginRequest` - 6 edges
5. `SessionSecurityTest` - 6 edges
6. `RateLimitingTest` - 6 edges
7. `EnsureUserIsActiveTest` - 5 edges
8. `UserPreferenceTest` - 5 edges
9. `AuthController` - 4 edges
10. `ExampleTest` - 4 edges

## Surprising Connections (you probably didn't know these)
- None detected - all connections are within the same source files.

## Communities

### Community 0 - "Auth Middleware Tests"
Cohesion: 0.19
Nodes (3): EnsureUserIsActiveTest, LoginTest, User

### Community 1 - "Auth Controller & Session"
Cohesion: 0.17
Nodes (3): AbsoluteSessionTimeout, AuthController, auth()

### Community 2 - "Login Request & User Prefs"
Cohesion: 0.27
Nodes (2): LoginRequest, UserPreferenceController

### Community 3 - "Session Security Middleware"
Cohesion: 0.24
Nodes (2): EnsureUserIsActive, SessionSecurityTest

### Community 4 - "Rate Limiting Tests"
Cohesion: 0.29
Nodes (1): RateLimitingTest

### Community 5 - "Inertia Middleware & Layout"
Cohesion: 0.33
Nodes (2): url(), HandleInertiaRequests

### Community 6 - "User Preference Tests"
Cohesion: 0.33
Nodes (1): UserPreferenceTest

### Community 7 - "Placeholder Tests"
Cohesion: 0.4
Nodes (1): ExampleTest

### Community 8 - "Application Bootstrap"
Cohesion: 0.5
Nodes (1): AppServiceProvider

### Community 9 - "Locale & i18n Tests"
Cohesion: 0.5
Nodes (1): LocaleColumnTest

### Community 10 - "User Role Permissions"
Cohesion: 0.67
Nodes (0): 

### Community 11 - "Security Headers Middleware"
Cohesion: 0.67
Nodes (1): SecurityHeaders

### Community 12 - "Role Authorization Middleware"
Cohesion: 0.67
Nodes (1): EnsureUserHasRole

### Community 13 - "Dashboard Controller"
Cohesion: 0.67
Nodes (1): DashboardController

### Community 14 - "Security Headers Tests"
Cohesion: 0.67
Nodes (1): SecurityHeadersTest

### Community 15 - "App Entry Point"
Cohesion: 1.0
Nodes (0): 

### Community 16 - "Theme Mode Composable"
Cohesion: 1.0
Nodes (0): 

### Community 17 - "UI Utility Functions"
Cohesion: 1.0
Nodes (0): 

### Community 18 - "Base Controller"
Cohesion: 1.0
Nodes (1): Controller

### Community 19 - "Test Base Class"
Cohesion: 1.0
Nodes (1): TestCase

### Community 20 - "Ziggy Type Definitions"
Cohesion: 1.0
Nodes (0): 

### Community 21 - "Model Type Definitions"
Cohesion: 1.0
Nodes (0): 

### Community 22 - "App Bootstrap Test"
Cohesion: 1.0
Nodes (0): 

### Community 23 - "Login Page Tests"
Cohesion: 1.0
Nodes (0): 

### Community 24 - "Auth Store"
Cohesion: 1.0
Nodes (0): 

### Community 25 - "Card Action Component"
Cohesion: 1.0
Nodes (0): 

### Community 26 - "Card Component"
Cohesion: 1.0
Nodes (0): 

### Community 27 - "Card Content Component"
Cohesion: 1.0
Nodes (0): 

### Community 28 - "Card Title Component"
Cohesion: 1.0
Nodes (0): 

### Community 29 - "Card Header Component"
Cohesion: 1.0
Nodes (0): 

### Community 30 - "Card Footer Component"
Cohesion: 1.0
Nodes (0): 

### Community 31 - "Card UI Exports"
Cohesion: 1.0
Nodes (0): 

### Community 32 - "Card Description Component"
Cohesion: 1.0
Nodes (0): 

### Community 33 - "Input UI Exports"
Cohesion: 1.0
Nodes (0): 

### Community 34 - "Input Component"
Cohesion: 1.0
Nodes (0): 

### Community 35 - "Separator UI Exports"
Cohesion: 1.0
Nodes (0): 

### Community 36 - "Separator Component"
Cohesion: 1.0
Nodes (0): 

### Community 37 - "Label Component"
Cohesion: 1.0
Nodes (0): 

### Community 38 - "Label UI Exports"
Cohesion: 1.0
Nodes (0): 

### Community 39 - "Dropdown Menu Label"
Cohesion: 1.0
Nodes (0): 

### Community 40 - "Dropdown Radio Item"
Cohesion: 1.0
Nodes (0): 

### Community 41 - "Dropdown Separator"
Cohesion: 1.0
Nodes (0): 

### Community 42 - "Dropdown Group"
Cohesion: 1.0
Nodes (0): 

### Community 43 - "Dropdown Trigger"
Cohesion: 1.0
Nodes (0): 

### Community 44 - "Dropdown Content"
Cohesion: 1.0
Nodes (0): 

### Community 45 - "Dropdown Shortcut"
Cohesion: 1.0
Nodes (0): 

### Community 46 - "Dropdown Checkbox Item"
Cohesion: 1.0
Nodes (0): 

### Community 47 - "Dropdown Item"
Cohesion: 1.0
Nodes (0): 

### Community 48 - "Dropdown Radio Group"
Cohesion: 1.0
Nodes (0): 

### Community 49 - "Dropdown Menu Root"
Cohesion: 1.0
Nodes (0): 

### Community 50 - "Dropdown Menu Exports"
Cohesion: 1.0
Nodes (0): 

### Community 51 - "Dropdown Sub Content"
Cohesion: 1.0
Nodes (0): 

### Community 52 - "Dropdown Sub Trigger"
Cohesion: 1.0
Nodes (0): 

### Community 53 - "Dropdown Sub Root"
Cohesion: 1.0
Nodes (0): 

### Community 54 - "Button UI Exports"
Cohesion: 1.0
Nodes (0): 

### Community 55 - "Button Component"
Cohesion: 1.0
Nodes (0): 

### Community 56 - "Badge Component"
Cohesion: 1.0
Nodes (0): 

### Community 57 - "Badge UI Exports"
Cohesion: 1.0
Nodes (0): 

### Community 58 - "Flash Banner Component"
Cohesion: 1.0
Nodes (0): 

### Community 59 - "Admin Layout"
Cohesion: 1.0
Nodes (0): 

### Community 60 - "Home Page"
Cohesion: 1.0
Nodes (0): 

### Community 61 - "Dashboard Page"
Cohesion: 1.0
Nodes (0): 

### Community 62 - "Login Page"
Cohesion: 1.0
Nodes (0): 

### Community 63 - "Flash Type Enum"
Cohesion: 1.0
Nodes (0): 

### Community 64 - "Theme Mode Enum"
Cohesion: 1.0
Nodes (0): 

## Knowledge Gaps
- **2 isolated node(s):** `Controller`, `TestCase`
  These have ≤1 connection - possible missing edges or undocumented components.
- **Thin community `App Entry Point`** (2 nodes): `setup()`, `app.ts`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Theme Mode Composable`** (2 nodes): `useThemeMode.ts`, `useThemeMode()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `UI Utility Functions`** (2 nodes): `utils.ts`, `cn()`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Base Controller`** (2 nodes): `Controller`, `Controller.php`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Test Base Class`** (2 nodes): `TestCase.php`, `TestCase`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Ziggy Type Definitions`** (1 nodes): `ziggy.d.ts`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Model Type Definitions`** (1 nodes): `models.ts`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `App Bootstrap Test`** (1 nodes): `app.test.ts`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Login Page Tests`** (1 nodes): `Login.test.ts`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Auth Store`** (1 nodes): `useAuthStore.ts`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Card Action Component`** (1 nodes): `CardAction.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Card Component`** (1 nodes): `Card.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Card Content Component`** (1 nodes): `CardContent.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Card Title Component`** (1 nodes): `CardTitle.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Card Header Component`** (1 nodes): `CardHeader.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Card Footer Component`** (1 nodes): `CardFooter.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Card UI Exports`** (1 nodes): `index.ts`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Card Description Component`** (1 nodes): `CardDescription.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Input UI Exports`** (1 nodes): `index.ts`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Input Component`** (1 nodes): `Input.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Separator UI Exports`** (1 nodes): `index.ts`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Separator Component`** (1 nodes): `Separator.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Label Component`** (1 nodes): `Label.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Label UI Exports`** (1 nodes): `index.ts`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dropdown Menu Label`** (1 nodes): `DropdownMenuLabel.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dropdown Radio Item`** (1 nodes): `DropdownMenuRadioItem.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dropdown Separator`** (1 nodes): `DropdownMenuSeparator.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dropdown Group`** (1 nodes): `DropdownMenuGroup.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dropdown Trigger`** (1 nodes): `DropdownMenuTrigger.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dropdown Content`** (1 nodes): `DropdownMenuContent.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dropdown Shortcut`** (1 nodes): `DropdownMenuShortcut.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dropdown Checkbox Item`** (1 nodes): `DropdownMenuCheckboxItem.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dropdown Item`** (1 nodes): `DropdownMenuItem.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dropdown Radio Group`** (1 nodes): `DropdownMenuRadioGroup.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dropdown Menu Root`** (1 nodes): `DropdownMenu.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dropdown Menu Exports`** (1 nodes): `index.ts`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dropdown Sub Content`** (1 nodes): `DropdownMenuSubContent.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dropdown Sub Trigger`** (1 nodes): `DropdownMenuSubTrigger.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dropdown Sub Root`** (1 nodes): `DropdownMenuSub.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Button UI Exports`** (1 nodes): `index.ts`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Button Component`** (1 nodes): `Button.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Badge Component`** (1 nodes): `Badge.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Badge UI Exports`** (1 nodes): `index.ts`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Flash Banner Component`** (1 nodes): `FlashBanner.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Admin Layout`** (1 nodes): `AdminLayout.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Home Page`** (1 nodes): `Home.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Dashboard Page`** (1 nodes): `Dashboard.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Login Page`** (1 nodes): `Login.vue`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Flash Type Enum`** (1 nodes): `FlashType.php`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.
- **Thin community `Theme Mode Enum`** (1 nodes): `ThemeMode.php`
  Too small to be a meaningful cluster - may be noise or needs more connections extracted.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `Auth Middleware Tests` to `Auth Controller & Session`, `Login Request & User Prefs`, `Session Security Middleware`, `Rate Limiting Tests`, `Inertia Middleware & Layout`, `User Preference Tests`, `Locale & i18n Tests`, `Role Authorization Middleware`?**
  _High betweenness centrality (0.190) - this node is a cross-community bridge._
- **Why does `auth()` connect `Auth Controller & Session` to `Login Request & User Prefs`, `Session Security Middleware`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **Are the 24 inferred relationships involving `User` (e.g. with `.share()` and `.handle()`) actually correct?**
  _`User` has 24 INFERRED edges - model-reasoned connections that need verification._
- **Are the 6 inferred relationships involving `auth()` (e.g. with `.handle()` and `.handle()`) actually correct?**
  _`auth()` has 6 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Controller`, `TestCase` to the rest of the system?**
  _2 weakly-connected nodes found - possible documentation gaps or missing edges._