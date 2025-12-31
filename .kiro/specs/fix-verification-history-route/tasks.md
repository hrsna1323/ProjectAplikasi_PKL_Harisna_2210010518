# Implementation Plan

- [ ] 1. Add new route for general verification history
  - Add `Route::get('/verification/history', ...)` before the content-specific history route in `routes/web.php`
  - Name it `verification.history.index` to distinguish from content-specific route
  - Ensure route ordering places the parameterless route before the parameterized one
  - _Requirements: 1.1_

- [ ] 2. Implement service layer method for retrieving all verification history
  - [ ] 2.1 Add `getAllVerificationHistory()` method to `VerificationService`
    - Accept filters array parameter (start_date, end_date, skpd_id, status, search)
    - Query Verification model with eager loading for content, content.skpd, content.kategori, verifikator relationships
    - Apply date range filter if provided
    - Apply SKPD filter if provided
    - Apply status filter if provided
    - Apply search filter on content title if provided
    - Order by verified_at descending
    - Return paginated results (20 per page)
    - _Requirements: 1.2, 1.3, 1.4_

- [ ] 2.2 Write property test for chronological ordering
  - **Property 2: Chronological ordering**
  - **Validates: Requirements 1.3**

- [ ] 2.3 Write property test for filter correctness
  - **Property 3: Filter correctness**
  - **Validates: Requirements 1.4**

- [ ] 3. Implement controller method for general history page
  - [ ] 3.1 Add `historyIndex()` method to `VerificationController`
    - Extract filter parameters from request (start_date, end_date, skpd_id, status, search)
    - Call `getAllVerificationHistory()` from service layer
    - Load SKPD list for filter dropdown
    - Return view with verification history, filters, and SKPD list
    - _Requirements: 1.1, 1.4_

- [ ] 4. Create view for general verification history page
  - [ ] 4.1 Create `resources/views/operator/verification/history-index.blade.php`
    - Extend layouts.app
    - Display page title "Riwayat Verifikasi"
    - Create filter form with date range, SKPD dropdown, status dropdown, and search input
    - Display verification records in a table or card layout
    - Show content title, SKPD name, verification action badge, operator name, timestamp for each record
    - Include link to content details for each record
    - Add pagination controls
    - Display "no records found" message when empty
    - _Requirements: 1.2, 1.4, 1.5_

- [ ] 4.2 Write property test for record completeness
  - **Property 1: Verification record completeness**
  - **Validates: Requirements 1.2**

- [ ] 4.3 Write property test for content detail link presence
  - **Property 4: Content detail link presence**
  - **Validates: Requirements 1.5**

- [ ] 5. Update sidebar navigation link
  - [ ] 5.1 Fix the route reference in `resources/views/layouts/app.blade.php` line 113
    - Change `route('operator.verification.history')` to `route('operator.verification.history.index')`
    - _Requirements: 1.1_

- [ ] 6. Write property test for content-specific history isolation
  - **Property 5: Content-specific history isolation**
  - **Validates: Requirements 2.3**

- [ ] 7. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.
