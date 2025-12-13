# Design Document - Sistem Manajemen Konten SKPD

## Overview

Sistem Manajemen Konten SKPD adalah aplikasi web berbasis Laravel yang dirancang untuk mengelola proses publikasi konten dari berbagai website SKPD di lingkungan Pemerintah Kabupaten Tanah Bumbu. Sistem ini mengubah paradigma dari monitoring infrastruktur teknis menjadi manajemen aktivitas publikasi konten dengan alur verifikasi yang jelas.

### Tujuan Desain

1. **Memisahkan concern** antara infrastruktur teknis (sistem lama) dan manajemen konten (sistem baru)
2. **Menyediakan alur kerja yang jelas** untuk input konten → verifikasi → approval → monitoring
3. **Memastikan akuntabilitas** melalui audit trail lengkap
4. **Mendukung evaluasi kinerja** melalui laporan dan dashboard yang informatif
5. **Menjaga integritas data** dengan validasi dan keamanan yang ketat

### Prinsip Desain

- **Separation of Concerns**: Memisahkan logika bisnis, data access, dan presentation layer
- **Role-Based Access Control (RBAC)**: Setiap user memiliki hak akses sesuai role
- **Audit Trail**: Setiap aksi penting tercatat untuk keperluan audit
- **Data Integrity**: Validasi input dan relasi database yang ketat
- **User Experience**: Interface yang intuitif dan responsif

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Presentation Layer                       │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Publisher  │  │   Operator   │  │     Admin    │      │
│  │   Dashboard  │  │   Dashboard  │  │   Dashboard  │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    Application Layer                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Content    │  │ Verification │  │   Reporting  │      │
│  │   Service    │  │   Service    │  │   Service    │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Notification │  │  Activity    │  │    SKPD      │      │
│  │   Service    │  │ Log Service  │  │   Service    │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      Data Layer                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Content    │  │     SKPD     │  │     User     │      │
│  │    Model     │  │    Model     │  │    Model     │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Verification │  │  Activity    │  │ Notification │      │
│  │    Model     │  │  Log Model   │  │    Model     │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    Database (SQLite/MySQL)                   │
└─────────────────────────────────────────────────────────────┘
```

### Technology Stack

- **Framework**: Laravel 11.x
- **Database**: SQLite (development) / MySQL 8.0+ (production)
- **Frontend**: Blade Templates, Bootstrap 5, Alpine.js
- **Authentication**: Laravel Session-based Auth
- **Export**: PhpSpreadsheet (Excel), DomPDF (PDF)
- **Validation**: Laravel Validation Rules
- **Testing**: PHPUnit, Pest PHP

## Components and Interfaces

### 1. Authentication & Authorization

#### AuthController
```php
class AuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    public function logout(): RedirectResponse
    public function checkRole(): string
}
```

#### Middleware
- `AuthCustom`: Memverifikasi user sudah login
- `AdminOnly`: Memverifikasi role Admin
- `OperatorOnly`: Memverifikasi role Operator
- `PublisherOnly`: Memverifikasi role Publisher

### 2. Content Management

#### ContentController
```php
class ContentController extends Controller
{
    public function index(): View
    public function create(): View
    public function store(StoreContentRequest $request): RedirectResponse
    public function show(int $id): View
    public function edit(int $id): View
    public function update(UpdateContentRequest $request, int $id): RedirectResponse
}
```

#### ContentService
```php
class ContentService
{
    public function createContent(array $data, User $publisher): Content
    public function updateContent(Content $content, array $data): Content
    public function getContentByPublisher(User $publisher, array $filters): Collection
    public function validateUrl(string $url): bool
    public function checkQuotaProgress(Skpd $skpd, int $month, int $year): array
}
```

### 3. Verification Management

#### VerificationController
```php
class VerificationController extends Controller
{
    public function index(): View
    public function show(int $contentId): View
    public function approve(ApproveContentRequest $request, int $contentId): RedirectResponse
    public function reject(RejectContentRequest $request, int $contentId): RedirectResponse
    public function history(int $contentId): View
}
```

#### VerificationService
```php
class VerificationService
{
    public function approveContent(Content $content, User $operator, string $reason): Verification
    public function rejectContent(Content $content, User $operator, string $reason): Verification
    public function getPendingContents(array $filters): Collection
    public function getVerificationHistory(Content $content): Collection
}
```

### 4. SKPD Management

#### SkpdController
```php
class SkpdController extends Controller
{
    public function index(): View
    public function create(): View
    public function store(Request $request): RedirectResponse
    public function edit(int $id): View
    public function update(Request $request, int $id): RedirectResponse
    public function destroy(int $id): RedirectResponse
    public function show(int $id): View
}
```

#### SkpdService
```php
class SkpdService
{
    public function createSkpd(array $data): Skpd
    public function updateSkpd(Skpd $skpd, array $data): Skpd
    public function deleteSkpd(Skpd $skpd): bool
    public function getSkpdWithQuotaStatus(int $month, int $year): Collection
    public function calculateComplianceStatus(Skpd $skpd, int $month, int $year): string
}
```

### 5. Reporting & Monitoring

#### ReportController
```php
class ReportController extends Controller
{
    public function dashboard(): View
    public function contentHistory(Request $request): View
    public function skpdPerformance(Request $request): View
    public function exportContentReport(Request $request): Response
    public function exportSkpdReport(Request $request): Response
}
```

#### ReportService
```php
class ReportService
{
    public function getDashboardStats(int $month, int $year): array
    public function getContentHistory(array $filters): Collection
    public function getSkpdPerformance(int $month, int $year): Collection
    public function generateContentReport(array $filters, string $format): mixed
    public function generateSkpdReport(int $month, int $year, string $format): mixed
}
```

### 6. Notification System

#### NotificationService
```php
class NotificationService
{
    public function sendContentSubmittedNotification(Content $content): void
    public function sendContentVerifiedNotification(Content $content, Verification $verification): void
    public function sendQuotaReminderNotification(Skpd $skpd, int $remaining): void
    public function sendQuotaWarningNotification(Skpd $skpd): void
    public function getUnreadNotifications(User $user): Collection
    public function markAsRead(int $notificationId): void
}
```

### 7. Activity Logging

#### ActivityLogService
```php
class ActivityLogService
{
    public function logContentCreated(Content $content, User $user): void
    public function logContentVerified(Content $content, Verification $verification, User $user): void
    public function logSkpdUpdated(Skpd $skpd, array $changes, User $user): void
    public function logUserAction(string $action, string $detail, User $user): void
    public function getActivityLogs(array $filters): Collection
}
```

## Data Models

### Entity Relationship Diagram

```
┌─────────────────┐         ┌─────────────────┐
│      User       │         │      SKPD       │
├─────────────────┤         ├─────────────────┤
│ user_id (PK)    │         │ id (PK)         │
│ username        │         │ nama_skpd       │
│ password_hash   │         │ website_url     │
│ full_name       │         │ email           │
│ role            │◄───┐    │ kuota_bulanan   │
│ skpd_id (FK)    │    │    │ status          │
│ email           │    │    │ server_id (FK)  │
│ is_active       │    │    └─────────────────┘
└─────────────────┘    │            │
        │              │            │
        │              │            │ 1:N
        │ 1:N          │            ▼
        ▼              │    ┌─────────────────┐
┌─────────────────┐    └────│    Content      │
│  Notification   │         ├─────────────────┤
├─────────────────┤         │ id (PK)         │
│ id (PK)         │         │ skpd_id (FK)    │
│ user_id (FK)    │         │ publisher_id(FK)│
│ type            │         │ judul           │
│ message         │         │ deskripsi       │
│ is_read         │         │ kategori_id(FK) │
│ created_at      │         │ url_publikasi   │
└─────────────────┘         │ tanggal_publish │
                            │ status          │
                            │ created_at      │
                            └─────────────────┘
                                    │
                                    │ 1:N
                                    ▼
                            ┌─────────────────┐
                            │  Verification   │
                            ├─────────────────┤
                            │ id (PK)         │
                            │ content_id (FK) │
                            │ verifikator_id  │
                            │ status          │
                            │ alasan          │
                            │ verified_at     │
                            └─────────────────┘

┌─────────────────┐         ┌─────────────────┐
│  ActivityLog    │         │    Kategori     │
├─────────────────┤         ├─────────────────┤
│ log_id (PK)     │         │ id (PK)         │
│ user_id (FK)    │         │ nama_kategori   │
│ action_type     │         │ deskripsi       │
│ detail          │         │ is_active       │
│ created_at      │         └─────────────────┘
└─────────────────┘
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Role-based access control
*For any* user with a specific role (Admin, Operator, or Publisher), when accessing system features, the system should grant or deny access according to the role's defined permissions
**Validates: Requirements 1.2, 1.3, 1.4, 10.3**

### Property 2: Publisher-SKPD association
*For any* Publisher user created, the user must be associated with exactly one SKPD
**Validates: Requirements 1.5**

### Property 3: SKPD data persistence
*For any* SKPD creation, all required fields (nama_skpd, website_url, email, kuota_bulanan) must be persisted to the database
**Validates: Requirements 2.1**

### Property 4: Default quota assignment
*For any* SKPD created without explicit quota value, the system should assign the default quota of 3 konten per month
**Validates: Requirements 2.2**

### Property 5: SKPD deletion constraint
*For any* SKPD with active content (status Pending, Approved, or Published), deletion attempts should be rejected
**Validates: Requirements 2.4**

### Property 6: Quota compliance calculation
*For any* SKPD and given month/year, the compliance status should be correctly calculated based on approved content count versus quota
**Validates: Requirements 2.5, 5.2, 5.4**

### Property 7: Content required fields validation
*For any* content submission attempt, if any required field (judul, deskripsi, kategori, url_publikasi, tanggal_publikasi) is missing, the submission should be rejected
**Validates: Requirements 3.1**

### Property 8: Content default status
*For any* content created by Publisher, the initial status must be "Pending"
**Validates: Requirements 3.2**

### Property 9: URL validation
*For any* URL input, the system should validate the URL format and reject malformed URLs
**Validates: Requirements 3.3, 10.1**

### Property 10: Data isolation for Publishers
*For any* Publisher user, when viewing content list, only content from their associated SKPD should be visible
**Validates: Requirements 3.5**

### Property 11: Pending content filtering
*For any* Operator viewing the dashboard, only content with status "Pending" should appear in the verification queue
**Validates: Requirements 4.1**

### Property 12: Content approval state transition
*For any* content with status "Pending", when approved by Operator with a reason, the status should change to "Approved" and a verification record should be created
**Validates: Requirements 4.3**

### Property 13: Content rejection state transition
*For any* content with status "Pending", when rejected by Operator with a reason, the status should change to "Rejected" and a verification record should be created
**Validates: Requirements 4.4**

### Property 14: Notification on content submission
*For any* content created by Publisher, a notification should be created for all Operator users
**Validates: Requirements 3.2, 7.1**

### Property 15: Notification on content verification
*For any* content that is verified (approved or rejected), a notification should be created for the Publisher who created the content
**Validates: Requirements 4.5, 7.2**

### Property 16: Unread notification display
*For any* user, when viewing dashboard, only notifications with is_read = false should appear in the notification list
**Validates: Requirements 7.5**

### Property 17: Dashboard statistics accuracy
*For any* given month and year, dashboard statistics (total SKPD, total content, pending content, non-compliant SKPD) should match the actual database counts
**Validates: Requirements 5.1**

### Property 18: Content history filtering
*For any* filter combination (SKPD, period, status, kategori), the content history should return only records matching all specified criteria
**Validates: Requirements 6.1, 8.4, 9.3**

### Property 19: Report content completeness
*For any* generated report, it must include all required sections: jumlah konten, kategori konten, tingkat kepatuhan kuota, and tren publikasi
**Validates: Requirements 6.4**

### Property 20: Audit trail completeness
*For any* important user action (content creation, verification, SKPD update), an activity log entry must be created with user_id, action_type, detail, and timestamp
**Validates: Requirements 2.3, 3.4, 8.1, 8.2, 8.3**

### Property 21: SKPD change tracking
*For any* SKPD update, if field values change, the activity log should record the old value, new value, and the user who made the change
**Validates: Requirements 8.3**

### Property 22: Category persistence
*For any* content with selected category, the category_id should be persisted and retrievable
**Validates: Requirements 9.2**

### Property 23: Inactive category exclusion
*For any* category with is_active = false, it should not appear in the category selection for new content, but should still be visible for existing content
**Validates: Requirements 9.5**

### Property 24: Input sanitization
*For any* user input containing potentially malicious content (XSS, SQL injection patterns), the system should sanitize or reject the input
**Validates: Requirements 10.2**

### Property 25: Password strength validation
*For any* password change attempt, passwords shorter than 8 characters or without alphanumeric combination should be rejected
**Validates: Requirements 10.5**

## Error Handling

### Error Categories

1. **Validation Errors**: Input data tidak memenuhi kriteria
2. **Authorization Errors**: User tidak memiliki hak akses
3. **Business Logic Errors**: Operasi melanggar aturan bisnis
4. **System Errors**: Database connection, file system, external service failures

### Error Handling Strategy

#### Validation Errors
```php
try {
    $validated = $request->validate([
        'judul' => 'required|string|max:255',
        'deskripsi' => 'required|string',
        'url_publikasi' => 'required|url|max:500',
        'kategori_id' => 'required|exists:kategori_konten,id',
        'tanggal_publikasi' => 'required|date',
    ]);
} catch (ValidationException $e) {
    return back()->withErrors($e->errors())->withInput();
}
```

#### Authorization Errors
```php
if (!auth()->user()->hasRole('Operator')) {
    abort(403, 'Anda tidak memiliki akses untuk verifikasi konten');
}
```

#### Business Logic Errors
```php
if ($skpd->contents()->whereIn('status', ['Pending', 'Approved', 'Published'])->exists()) {
    return back()->with('error', 'SKPD tidak dapat dihapus karena masih memiliki konten aktif');
}
```

## Testing Strategy

### Unit Testing

Unit tests akan fokus pada:
1. **Model Methods**: Test business logic di model
2. **Service Methods**: Test service layer logic
3. **Validation Rules**: Test custom validation rules
4. **Helper Functions**: Test utility functions

**Testing Framework**: PHPUnit (Laravel default)

### Property-Based Testing

Property-based tests akan menggunakan **Pest PHP** untuk memverifikasi correctness properties.

**Configuration**:
- Minimum 100 iterations per property test
- Use Faker for generating random test data
- Tag each test with corresponding property number

**Example Property Test**:
```php
// Feature: manajemen-konten-skpd, Property 10: Data isolation for Publishers
test('publishers can only see content from their own SKPD', function () {
    $skpd1 = Skpd::factory()->create();
    $skpd2 = Skpd::factory()->create();
    
    $publisher1 = User::factory()->create(['role' => 'Publisher', 'skpd_id' => $skpd1->id]);
    
    Content::factory()->count(5)->create(['skpd_id' => $skpd1->id]);
    Content::factory()->count(5)->create(['skpd_id' => $skpd2->id]);
    
    $visibleContent = Content::where('skpd_id', $publisher1->skpd_id)->get();
    
    expect($visibleContent->pluck('skpd_id')->unique()->toArray())->toBe([$skpd1->id]);
});
```

### Integration Testing

Integration tests akan fokus pada:
1. **Complete User Flows**: Publisher creates content → Operator approves → Notification sent
2. **Database Transactions**: Content creation with notification and activity log
3. **External Dependencies**: URL validation, file export generation

### Test Coverage Goals

- **Unit Tests**: 80%+ code coverage for services and models
- **Property Tests**: 100% coverage of all correctness properties
- **Integration Tests**: Coverage of all critical user flows
