# Design Document

## Overview

This design addresses a routing mismatch in the operator verification system. The sidebar navigation attempts to link to a general verification history page, but the current route requires a content ID parameter. We will create a new general verification history page that displays all verification activities while preserving the existing content-specific history functionality.

## Architecture

The solution follows Laravel's MVC pattern with the following components:

1. **New Route**: `operator.verification.history.index` - General history page (no parameters)
2. **Existing Route**: `operator.verification.history` - Content-specific history (requires content ID)
3. **Controller Method**: New `historyIndex()` method in `VerificationController`
4. **Service Method**: New `getAllVerificationHistory()` method in `VerificationService`
5. **View**: New `operator.verification.history-index.blade.php` view

## Components and Interfaces

### Route Changes

**New Route (General History)**:
```php
Route::get('/verification/history', [VerificationController::class, 'historyIndex'])
    ->name('verification.history.index');
```

**Existing Route (Content-Specific History)** - No changes:
```php
Route::get('/verification/{content}/history', [VerificationController::class, 'history'])
    ->name('verification.history');
```

### Controller Interface

**New Method in `VerificationController`**:
```php
public function historyIndex(Request $request): View
```

This method will:
- Accept optional filters (date range, SKPD, status)
- Call the service layer to retrieve verification history
- Return a paginated view of all verification records

**Existing Method** - No changes:
```php
public function history(int $contentId): View
```

### Service Interface

**New Method in `VerificationService`**:
```php
public function getAllVerificationHistory(array $filters = []): LengthAwarePaginator
```

Parameters:
- `$filters['start_date']` - Optional start date filter
- `$filters['end_date']` - Optional end date filter
- `$filters['skpd_id']` - Optional SKPD filter
- `$filters['status']` - Optional status filter (Approved/Rejected)
- `$filters['search']` - Optional search term for content title

Returns: Paginated collection of Verification records with relationships loaded

## Data Models

No changes to existing models. The solution uses the existing `Verification` model with its relationships:

- `Verification` belongs to `Content`
- `Verification` belongs to `User` (verifikator)
- `Content` belongs to `Skpd`
- `Content` belongs to `KategoriKonten`

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Acceptance Criteria Testing Prework:

1.1 WHEN an operator clicks the "Riwayat" link in the sidebar THEN the system SHALL display a page showing all verification history records
  Thoughts: This is testing a specific UI interaction - clicking a link and verifying the page loads with data. This is an example test case.
  Testable: yes - example

1.2 WHEN displaying verification history records THEN the system SHALL show the content title, SKPD name, verification action (approved/rejected), operator name, and timestamp
  Thoughts: This is about what information must be present in the rendered output. For any verification record, the rendered view should contain all these fields. This is a property that applies to all records.
  Testable: yes - property

1.3 WHEN displaying verification history THEN the system SHALL order records by most recent first
  Thoughts: This is about the ordering of results. For any set of verification records, they should be ordered by timestamp descending. This is a property.
  Testable: yes - property

1.4 WHEN the verification history page loads THEN the system SHALL support filtering by date range, SKPD, and verification status
  Thoughts: This is testing that filter functionality exists and works. For any valid filter combination, the results should only include records matching those filters. This is a property.
  Testable: yes - property

1.5 WHEN an operator views the general history page THEN the system SHALL provide links to view individual content details
  Thoughts: This is about the presence of links in the rendered output. For any verification record displayed, there should be a link to view that content's details. This is a property.
  Testable: yes - property

2.1 WHEN viewing a specific content item THEN the system SHALL continue to provide access to that content's verification history
  Thoughts: This is ensuring existing functionality still works. For any valid content ID, the content-specific history route should still function. This is a property.
  Testable: yes - property

2.2 WHEN the content-specific history route is called THEN the system SHALL require a valid content ID parameter
  Thoughts: This is testing that the route parameter validation works. If called without a content ID, it should fail. This is an edge case.
  Testable: edge-case

2.3 WHEN displaying content-specific history THEN the system SHALL show the complete verification timeline for that content only
  Thoughts: This is testing data isolation. For any content ID, the history should only show verifications for that specific content, not others. This is a property.
  Testable: yes - property

### Property Reflection

Reviewing the properties for redundancy:
- Property 1.2 (display required fields) and Property 1.5 (provide links) are both about rendered output but test different aspects - keep both
- Property 1.3 (ordering) is independent - keep
- Property 1.4 (filtering) is independent - keep
- Property 2.1 (existing functionality) and Property 2.3 (data isolation) test different aspects - keep both

No redundant properties identified.

### Correctness Properties

Property 1: Verification record completeness
*For any* verification record displayed on the general history page, the rendered output should contain the content title, SKPD name, verification action, operator name, and timestamp
**Validates: Requirements 1.2**

Property 2: Chronological ordering
*For any* set of verification records retrieved, they should be ordered by verified_at timestamp in descending order (most recent first)
**Validates: Requirements 1.3**

Property 3: Filter correctness
*For any* valid filter combination (date range, SKPD, status), all returned verification records should match the specified filter criteria
**Validates: Requirements 1.4**

Property 4: Content detail link presence
*For any* verification record displayed, the rendered output should include a clickable link to view that content's details
**Validates: Requirements 1.5**

Property 5: Content-specific history isolation
*For any* valid content ID, the content-specific history route should return only verification records associated with that content
**Validates: Requirements 2.3**

## Error Handling

1. **Invalid Content ID**: The existing content-specific history route will return a 404 if the content doesn't exist
2. **Invalid Filter Values**: The service layer will sanitize and validate filter inputs, ignoring invalid values
3. **Empty Results**: Both views will display appropriate "no records found" messages
4. **Authorization**: Existing middleware ensures only operators can access these routes

## Testing Strategy

### Unit Tests

1. Test `getAllVerificationHistory()` returns correct records without filters
2. Test date range filtering works correctly
3. Test SKPD filtering works correctly
4. Test status filtering works correctly
5. Test search filtering works correctly
6. Test pagination works correctly
7. Test that existing `getVerificationHistory()` still works for content-specific history

### Property-Based Tests

We will use **PHPUnit with data providers** for property-based testing in PHP/Laravel. Each property test will run with at least 100 iterations using generated test data.

Property tests will be tagged with comments in this format: `**Feature: fix-verification-history-route, Property {number}: {property_text}**`

1. **Property 1 Test**: Generate random verification records and verify all required fields are present in rendered output
2. **Property 2 Test**: Generate random sets of verification records with different timestamps and verify ordering
3. **Property 3 Test**: Generate random filter combinations and verify all results match filters
4. **Property 4 Test**: Generate random verification records and verify links are present
5. **Property 5 Test**: Generate random content with multiple verifications and verify isolation

### Integration Tests

1. Test the general history page loads successfully from sidebar navigation
2. Test filtering UI updates results correctly
3. Test pagination controls work
4. Test links to content details navigate correctly
5. Test existing content-specific history page still works

## Implementation Notes

1. The sidebar link in `resources/views/layouts/app.blade.php` line 113 should be updated to use `route('operator.verification.history.index')` instead of `route('operator.verification.history')`
2. The new view should follow the existing design patterns in the operator views
3. Filters should be implemented using query parameters to allow bookmarking and sharing filtered views
4. Pagination should default to 20 records per page
5. The service method should eager load all necessary relationships to avoid N+1 queries
