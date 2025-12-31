# Implementation Plan - Sistem Manajemen Konten SKPD

## Overview

Task list ini mengikuti pendekatan incremental development, dimulai dari setup database dan model, kemudian service layer, controller, dan terakhir views. Setiap task membangun di atas task sebelumnya untuk memastikan integrasi yang baik.

---

- [x] 1. Setup Database Schema dan Model






  - [x] 1.1 Update User model dengan role dan SKPD association


    - Tambahkan field `role` (enum: Admin, Operator, Publisher), `skpd_id` (nullable FK), `is_active`
    - Implementasi relationship `belongsTo` ke SKPD
    - Implementasi method `hasRole()` dan `isPublisher()`
    - _Requirements: 1.1, 1.5_
  - [x] 1.2 Write property test untuk Publisher-SKPD association














    - **Property 2: Publisher-SKPD association** 
    - **Validates: Requirements 1.5**
  - [x] 1.3 Implementasi SKPD model dengan relationships


    - Definisikan fillable fields:  nama_skpd, website_url, email, kuota_bulanan, status
    - Implementasi relationships: hasMany Contents, hasMany Users (publishers)
    - Tambahkan default value kuota_bulanan = 3
    - _Requirements: 2.1, 2.2_

  - [x] 1.4 Write property test untuk default quota assignment












    - **Property 4: Default quota assignment**
    - **Validates: Requirements 2.2**
  - [x] 1.5 Implementasi KategoriKonten model


    - Definisikan fillable fields: nama_kategori, deskripsi, is_active
    - Implementasi scope `active()` untuk filter kategori aktif
    - _Requirements: 9.1, 9.4, 9.5_
  - [x] 1.6 Implementasi Content model dengan relationships



    - Definisikan fillable fields: skpd_id, publisher_id, judul, deskripsi, kategori_id, url_publikasi, tanggal_publikasi, status
    - Implementasi relationships: belongsTo SKPD, belongsTo User (publisher), belongsTo KategoriKonten, hasMany Verifications
    - Status enum: Draft, Pending, Approved, Rejected, Published
    - _Requirements: 3.1, 3.2_
  - [x] 1.7 Implementasi Verification model


    - Definisikan fillable fields: content_id, verifikator_id, status, alasan, verified_at
    - Implementasi relationships: belongsTo Content, belongsTo User (verifikator)
    - _Requirements: 4.3, 4.4_
  - [x] 1.8 Implementasi Notification model


    - Definisikan fillable fields: user_id, type, message, is_read, related_content_id
    - Implementasi scope `unread()` untuk filter notifikasi belum dibaca
    - _Requirements: 7.1, 7.2, 7.5_
  - [x] 1.9 Implementasi ActivityLog model


    - Definisikan fillable fields: user_id, action_type, detail, old_value, new_value
    - Implementasi scope untuk filter berdasarkan user, action_type, periode
    - _Requirements: 8.1, 8.3_

- [x] 2. Checkpoint - Pastikan semua model dan migration berjalan



  - Ensure all tests pass, ask the user if questions arise.

- [x] 3. Implementasi Authentication dan Authorization




  - [x] 3.1 Implementasi AuthController


    - Method login() dengan validasi credentials dan redirect berdasarkan role
    - Method logout() dengan session destroy
    - Method checkRole() untuk mendapatkan role user saat ini
    - _Requirements: 1.2, 1.3, 1.4_
  - [x] 3.2 Write property test untuk role-based access control










    - **Property 1: Role-based access control**
    - **Validates: Requirements 1.2, 1.3, 1.4, 10.3**
  - [x] 3.3 Implementasi middleware AuthCustom


    - Verifikasi user sudah login, redirect ke login jika belum
    - Handle session timeout
    - _Requirements: 10.4_
  - [x] 3.4 Implementasi middleware AdminOnly, OperatorOnly, PublisherOnly





    - Verifikasi role user sesuai dengan middleware
    - Return 403 jika role tidak sesuai
    - _Requirements: 10.3_

- [x] 4. Implementasi SKPD Service dan Controller





  - [x] 4.1 Implementasi SkpdService





    - Method createSkpd() dengan default kuota_bulanan = 3
    - Method updateSkpd() dengan tracking perubahan untuk activity log
    - Method deleteSkpd() dengan validasi tidak ada konten aktif
    - Method getSkpdWithQuotaStatus() untuk list SKPD dengan status kepatuhan
    - Method calculateComplianceStatus() untuk hitung status kuota
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_
  - [x] 4.2 Write property test untuk SKPD deletion constraint






    - **Property 5: SKPD deletion constraint**
    - **Validates: Requirements 2.4**

  - [x] 4.3 Write property test untuk quota compliance calculation











    - **Property 6: Quota compliance calculation**
    - **Validates: Requirements 2.5, 5.2, 5.4**
  - [x] 4.4 Implementasi SkpdController (Admin)





    - CRUD operations untuk SKPD
    - Integrasi dengan SkpdService
    - Validasi input menggunakan Form Request
    - _Requirements: 2.1, 2.3, 2.4_

- [x] 5. Implementasi Content Service dan Controller





  - [x] 5.1 Implementasi ContentService





    - Method createContent() dengan status default "Pending"
    - Method updateContent() untuk edit konten
    - Method getContentByPublisher() dengan filter SKPD sendiri
    - Method validateUrl() untuk validasi format dan ketersediaan URL
    - Method checkQuotaProgress() untuk cek progress kuota SKPD
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_
  - [x] 5.2 Write property test untuk content default status






    - **Property 8: Content default status**
    - **Validates: Requirements 3.2**
  - [x] 5.3 Write property test untuk URL validation







    - **Property 9: URL validation**
    - **Validates: Requirements 3.3, 10.1**

  - [x]* 5.4 Write property test untuk data isolation for Publishers





    - **Property 10: Data isolation for Publishers**
    - **Validates: Requirements 3.5**
  - [x] 5.5 Implementasi StoreContentRequest dan UpdateContentRequest



    - Validasi required fields: judul, deskripsi, kategori_id, url_publikasi, tanggal_publikasi
    - Validasi URL format
    - Sanitasi input untuk mencegah XSS
    - _Requirements: 3.1, 10.2_
  - [x] 5.6 Write property test untuk content required fields validation






    - **Property 7: Content required fields validation**
    - **Validates: Requirements 3.1**
  - [x] 5.7 Implementasi ContentController (Publisher)



    - Method index() menampilkan konten SKPD sendiri
    - Method create() dan store() untuk input konten baru
    - Method show() untuk detail konten
    - Method edit() dan update() untuk edit konten (hanya status Draft/Rejected)
    - _Requirements: 3.1, 3.4, 3.5_



- [x] 6. Checkpoint - Pastikan SKPD dan Content management berfungsi



  - Ensure all tests pass, ask the user if questions arise.

- [x] 7. Implementasi Verification Service dan Controller





  - [x] 7.1 Implementasi VerificationService


    - Method approveContent() mengubah status ke "Approved" dan buat record Verification
    - Method rejectContent() mengubah status ke "Rejected" dengan alasan wajib
    - Method getPendingContents() untuk list konten pending
    - Method getVerificationHistory() untuk timeline verifikasi
    - _Requirements: 4.1, 4.2, 4.3, 4.4_
  - [x] 7.2 Write property test untuk content approval state transition























    - **Property 12: Content approval state transition**
    - **Validates: Requirements 4.3**
  - [x] 7.3 Write property test untuk content rejection state transition






    - **Property 13: Content rejection state transition**
    - **Validates: Requirements 4.4**
  - [x] 7.4 Write property test untuk pending content filtering






    - **Property 11: Pending content filtering**
    - **Validates: Requirements 4.1**

  - [x] 7.5 Implementasi ApproveContentRequest dan RejectContentRequest

    - ApproveContentRequest: alasan optional
    - RejectContentRequest: alasan required
    - _Requirements: 4.3, 4.4_

  - [x] 7.6 Implementasi VerificationController (Operator)

    - Method index() menampilkan konten pending
    - Method show() menampilkan detail konten untuk review
    - Method approve() untuk approve konten
    - Method reject() untuk reject konten
    - Method history() untuk timeline verifikasi
    - _Requirements: 4.1, 4.2, 4.3, 4.4_

- [x] 8. Implementasi Notification Service






  - [x] 8.1 Implementasi NotificationService

    - Method sendContentSubmittedNotification() ke semua Operator
    - Method sendContentVerifiedNotification() ke Publisher terkait
    - Method sendQuotaReminderNotification() ke Publisher SKPD
    - Method sendQuotaWarningNotification() ke Admin
    - Method getUnreadNotifications() untuk user tertentu
    - Method markAsRead() untuk tandai sudah dibaca
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_
  - [x] 8.2 Write property test untuk notification on content submission






    - **Property 14: Notification on content submission**
    - **Validates: Requirements 3.2, 7.1**
  - [x] 8.3 Write property test untuk notification on content verification






    - **Property 15: Notification on content verification**
    - **Validates: Requirements 4.5, 7.2**
  - [x] 8.4 Write property test untuk unread notification display












    - **Property 16: Unread notification display**
    - **Validates: Requirements 7.5**

- [x] 9. Implementasi Activity Log Service






  - [x] 9.1 Implementasi ActivityLogService

    - Method logContentCreated() untuk log pembuatan konten
    - Method logContentVerified() untuk log verifikasi
    - Method logSkpdUpdated() dengan old_value dan new_value
    - Method logUserAction() untuk aksi umum
    - Method getActivityLogs() dengan filter
    - _Requirements: 8.1, 8.2, 8.3, 8.4_
  - [x] 9.2 Write property test untuk audit trail completeness
























    - **Property 20: Audit trail completeness**
    - **Validates: Requirements 2.3, 3.4, 8.1, 8.2, 8.3**
  - [x] 9.3 Write property test untuk SKPD change tracking













    - **Property 21: SKPD change tracking**
    - **Validates: Requirements 8.3**

- [x] 10. Checkpoint - Pastikan verification, notification, dan activity log berfungsi








  - Ensure all tests pass, ask the user if questions arise.

- [x] 11. Implementasi Report Service dan Dashboard





  - [x] 11.1 Implementasi ReportService


    - Method getDashboardStats() untuk statistik dashboard
    - Method getContentHistory() dengan filter SKPD, periode, status, kategori
    - Method getSkpdPerformance() untuk performa SKPD per bulan
    - Method generateContentReport() untuk export Excel/PDF
    - Method generateSkpdReport() untuk laporan SKPD
    - _Requirements: 5.1, 6.1, 6.3, 6.4_
  - [x] 11.2 Write property test untuk dashboard statistics accuracy






    - **Property 17: Dashboard statistics accuracy**
    - **Validates: Requirements 5.1**
  - [x] 11.3 Write property test untuk content history filtering






    - **Property 18: Content history filtering**
    - **Validates: Requirements 6.1, 8.4, 9.3**

  - [x] 11.4 Implementasi DashboardController

    - Dashboard berbeda untuk setiap role (Admin, Operator, Publisher)
    - Tampilkan statistik sesuai role
    - Tampilkan notifikasi unread
    - _Requirements: 5.1, 7.5_

  - [x] 11.5 Implementasi MonitoringController (Operator)

    - Method index() untuk monitoring semua SKPD
    - Method show() untuk detail SKPD dengan grafik tren 6 bulan
    - _Requirements: 5.2, 5.4, 5.5_

  - [x] 11.6 Implementasi ReportController

    - Method contentHistory() untuk riwayat konten
    - Method skpdPerformance() untuk performa SKPD
    - Method exportContentReport() untuk export Excel/PDF
    - Method exportSkpdReport() untuk export laporan SKPD
    - _Requirements: 6.1, 6.3, 6.4, 6.5_

- [x] 12. Implementasi Kategori Management







  - [x] 12.1 Implementasi KategoriController (Admin)

    - CRUD operations untuk kategori
    - Method untuk nonaktifkan kategori (soft disable)
    - _Requirements: 9.1, 9.4, 9.5_
  - [x] 12.2 Write property test untuk inactive category exclusion
    - **Property 23: Inactive category exclusion**
    - **Validates: Requirements 9.5**
  - [ ]* 12.3 Write property test untuk category persistence
    - **Property 22: Category persistence**
    - **Validates: Requirements 9.2**

- [x] 13. Implementasi User Management
  - [x] 13.1 Implementasi UserController (Admin)
    - CRUD operations untuk user
    - Assign role dan SKPD untuk Publisher
    - Validasi password strength
    - _Requirements: 1.1, 1.5, 10.5_
  - [ ]* 13.2 Write property test untuk password strength validation
    - **Property 25: Password strength validation**
    - **Validates: Requirements 10.5**

- [x] 14. Implementasi Security dan Validation
  - [x] 14.1 Implementasi input sanitization
    - Sanitasi semua input untuk mencegah XSS dan SQL injection
    - Gunakan Laravel built-in protection
    - _Requirements: 10.2_
  - [ ]* 14.2 Write property test untuk input sanitization
    - **Property 24: Input sanitization**
    - **Validates: Requirements 10.2**
  - [x] 14.3 Implementasi session timeout handling
    - Configure session lifetime
    - Auto logout dan redirect ke login
    - _Requirements: 10.4_

- [x] 15. Implementasi Views dan UI
  - [x] 15.1 Setup layout dan navigation
    - Base layout dengan Bootstrap 5
    - Navigation berbeda per role
    - Notification badge di navbar
    - _Requirements: 1.2, 1.3, 1.4_
  - [x] 15.2 Implementasi Auth views
    - Login page
    - _Requirements: 1.2, 1.3, 1.4_
  - [x] 15.3 Implementasi Publisher views
    - Dashboard dengan statistik konten sendiri
    - List konten dengan filter
    - Form input/edit konten
    - Detail konten dengan timeline verifikasi
    - _Requirements: 3.1, 3.5, 6.2_
  - [x] 15.4 Implementasi Operator views
    - Dashboard dengan statistik dan pending list
    - Verification queue
    - Detail konten untuk review
    - Monitoring SKPD dengan progress bar
    - Riwayat konten dengan filter
    - _Requirements: 4.1, 4.2, 5.1, 5.2, 6.1_
  - [x] 15.5 Implementasi Admin views
    - Dashboard dengan overview sistem
    - CRUD SKPD
    - CRUD User
    - CRUD Kategori
    - Activity Log viewer
    - Report generator
    - _Requirements: 2.1, 8.4, 8.5, 9.4_

- [x] 16. Implementasi Routes
  - [x] 16.1 Setup routes dengan middleware
    - Group routes berdasarkan role
    - Apply middleware untuk authorization
    - _Requirements: 10.3_

- [x] 17. Setup Database Seeders
  - [x] 17.1 Buat seeders untuk data awal
    - Seeder untuk kategori default (Berita, Pengumuman, Artikel, Kegiatan, Layanan Publik)
    - Seeder untuk user Admin default
    - _Requirements: 9.1_

- [x] 18. Final Checkpoint - Pastikan semua fitur berfungsi
  - Ensure all tests pass, ask the user if questions arise.
