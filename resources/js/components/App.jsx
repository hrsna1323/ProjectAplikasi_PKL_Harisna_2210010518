import React, { useState } from 'react';
import { Bell, FileText, Users, CheckCircle, XCircle, Clock, Plus, Download, BarChart3, Home, Settings, LogOut, Menu, X } from 'lucide-react';

// Mock Data
const mockData = {
  admin: {
    stats: { totalSKPD: 25, totalContent: 178, pendingVerification: 12, nonCompliantSKPD: 3 },
    skpdList: [
      { id: 1, name: 'Dinas Pendidikan', quota: 3, published: 5, status: 'compliant' },
      { id: 2, name: 'Dinas Kesehatan', quota: 3, published: 2, status: 'warning' },
      { id: 3, name: 'Dinas Pariwisata', quota: 3, published: 0, status: 'critical' },
      { id: 4, name: 'Dinas Perhubungan', quota: 3, published: 4, status: 'compliant' },
      { id: 5, name: 'Dinas Sosial', quota: 3, published: 3, status: 'compliant' }
    ],
    recentActivities: [
      { id: 1, user: 'Operator 1', action: 'Approved content', detail: 'Pembukaan Tahun Ajaran - Dinas Pendidikan', time: '2 jam lalu' },
      { id: 2, user: 'Publisher Kesehatan', action: 'Submitted content', detail: 'Vaksinasi Massal', time: '3 jam lalu' },
      { id: 3, user: 'Admin', action: 'Created SKPD', detail: 'Dinas Koperasi', time: '5 jam lalu' }
    ]
  },
  operator: {
    stats: { pendingContent: 12, approvedToday: 8, rejectedToday: 2, totalVerified: 156 },
    pendingContents: [
      { id: 1, title: 'Pembukaan Tahun Ajaran Baru 2025', skpd: 'Dinas Pendidikan', publisher: 'Budi Santoso', category: 'Berita', date: '2025-01-05', url: 'https://disdik.tanbu.go.id/berita/pembukaan' },
      { id: 2, title: 'Vaksinasi Massal COVID-19', skpd: 'Dinas Kesehatan', publisher: 'Siti Aminah', category: 'Kegiatan', date: '2025-01-08', url: 'https://dinkes.tanbu.go.id/kegiatan/vaksinasi' },
      { id: 3, title: 'Workshop Guru Matematika', skpd: 'Dinas Pendidikan', publisher: 'Budi Santoso', category: 'Kegiatan', date: '2025-01-12', url: 'https://disdik.tanbu.go.id/workshop' }
    ]
  },
  publisher: {
    stats: { quotaProgress: { current: 2, total: 3 }, approved: 15, rejected: 2, pending: 1 },
    myContents: [
      { id: 1, title: 'Pembukaan Tahun Ajaran Baru 2025', category: 'Berita', status: 'approved', date: '2025-01-05', verifier: 'Operator 1', reason: 'Konten sesuai dan informatif' },
      { id: 2, title: 'Workshop Guru Matematika', category: 'Kegiatan', status: 'approved', date: '2025-01-12', verifier: 'Operator 1', reason: 'Kegiatan positif dan bermanfaat' },
      { id: 3, title: 'Prestasi Siswa Olimpiade', category: 'Berita', status: 'pending', date: '2025-01-20', verifier: '-', reason: '-' },
      { id: 4, title: 'Kegiatan Sosial', category: 'Kegiatan', status: 'rejected', date: '2024-12-28', verifier: 'Operator 2', reason: 'URL tidak dapat diakses' }
    ]
  }
};

// StatCard Component
const StatCard = ({ icon: Icon, title, value, subtitle, color }) => (
  <div className="bg-white rounded-lg shadow p-6 border-l-4" style={{ borderColor: color }}>
    <div className="flex items-center justify-between">
      <div>
        <p className="text-gray-500 text-sm">{title}</p>
        <p className="text-3xl font-bold mt-2">{value}</p>
        {subtitle && <p className="text-sm text-gray-600 mt-1">{subtitle}</p>}
      </div>
      <div className="bg-gray-100 p-4 rounded-full">
        <Icon className="w-8 h-8" style={{ color }} />
      </div>
    </div>
  </div>
);


// Admin Dashboard Component
const AdminDashboard = () => (
  <div className="space-y-6">
    <div className="flex items-center justify-between">
      <h1 className="text-2xl font-bold text-gray-800">Dashboard Admin</h1>
      <button className="bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-blue-700">
        <Download className="w-4 h-4" />Export Laporan
      </button>
    </div>
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <StatCard icon={Users} title="Total SKPD" value={mockData.admin.stats.totalSKPD} color="#3B82F6" />
      <StatCard icon={FileText} title="Konten Bulan Ini" value={mockData.admin.stats.totalContent} color="#10B981" />
      <StatCard icon={Clock} title="Pending Verifikasi" value={mockData.admin.stats.pendingVerification} color="#F59E0B" />
      <StatCard icon={XCircle} title="SKPD Warning" value={mockData.admin.stats.nonCompliantSKPD} color="#EF4444" />
    </div>
    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div className="bg-white rounded-lg shadow p-6">
        <h2 className="text-lg font-semibold mb-4">Status Kepatuhan SKPD</h2>
        <div className="space-y-3">
          {mockData.admin.skpdList.map(skpd => (
            <div key={skpd.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div className="flex-1">
                <p className="font-medium">{skpd.name}</p>
                <p className="text-sm text-gray-600">Kuota: {skpd.quota} | Publikasi: {skpd.published}</p>
              </div>
              <span className={`px-3 py-1 rounded-full text-sm font-medium ${skpd.status === 'compliant' ? 'bg-green-100 text-green-800' : skpd.status === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}`}>
                {skpd.status === 'compliant' ? '✓ Compliant' : skpd.status === 'warning' ? '⚠ Warning' : '✗ Critical'}
              </span>
            </div>
          ))}
        </div>
      </div>
      <div className="bg-white rounded-lg shadow p-6">
        <h2 className="text-lg font-semibold mb-4">Aktivitas Terkini</h2>
        <div className="space-y-3">
          {mockData.admin.recentActivities.map(activity => (
            <div key={activity.id} className="flex gap-3 p-3 bg-gray-50 rounded-lg">
              <div className="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
              <div className="flex-1">
                <p className="font-medium text-sm">{activity.user} - {activity.action}</p>
                <p className="text-sm text-gray-600">{activity.detail}</p>
                <p className="text-xs text-gray-500 mt-1">{activity.time}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  </div>
);


// Operator Dashboard Component
const OperatorDashboard = ({ setSelectedContent, setShowVerificationModal }) => (
  <div className="space-y-6">
    <div className="flex items-center justify-between">
      <h1 className="text-2xl font-bold text-gray-800">Dashboard Operator</h1>
      <button className="bg-green-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-green-700">
        <BarChart3 className="w-4 h-4" />Lihat Laporan
      </button>
    </div>
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <StatCard icon={Clock} title="Pending" value={mockData.operator.stats.pendingContent} subtitle="Menunggu verifikasi" color="#F59E0B" />
      <StatCard icon={CheckCircle} title="Approved Hari Ini" value={mockData.operator.stats.approvedToday} color="#10B981" />
      <StatCard icon={XCircle} title="Rejected Hari Ini" value={mockData.operator.stats.rejectedToday} color="#EF4444" />
      <StatCard icon={FileText} title="Total Verified" value={mockData.operator.stats.totalVerified} color="#3B82F6" />
    </div>
    <div className="bg-white rounded-lg shadow">
      <div className="p-6 border-b"><h2 className="text-lg font-semibold">Konten Pending Verifikasi</h2></div>
      <div className="divide-y">
        {mockData.operator.pendingContents.map(content => (
          <div key={content.id} className="p-6 hover:bg-gray-50">
            <div className="flex items-start justify-between">
              <div className="flex-1">
                <h3 className="font-semibold text-lg">{content.title}</h3>
                <div className="flex gap-4 mt-2 text-sm text-gray-600">
                  <span>🏢 {content.skpd}</span><span>👤 {content.publisher}</span><span>📁 {content.category}</span><span>📅 {content.date}</span>
                </div>
                <a href={content.url} target="_blank" rel="noopener noreferrer" className="text-blue-600 text-sm mt-2 inline-block hover:underline">🔗 {content.url}</a>
              </div>
              <div className="flex gap-2 ml-4">
                <button onClick={() => { setSelectedContent(content); setShowVerificationModal(true); }} className="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2">
                  <CheckCircle className="w-4 h-4" />Approve
                </button>
                <button onClick={() => { setSelectedContent(content); setShowVerificationModal(true); }} className="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 flex items-center gap-2">
                  <XCircle className="w-4 h-4" />Reject
                </button>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  </div>
);


// Publisher Dashboard Component
const PublisherDashboard = ({ setShowContentModal }) => (
  <div className="space-y-6">
    <div className="flex items-center justify-between">
      <h1 className="text-2xl font-bold text-gray-800">Dashboard Publisher - Dinas Pendidikan</h1>
      <button onClick={() => setShowContentModal(true)} className="bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-blue-700">
        <Plus className="w-4 h-4" />Input Konten Baru
      </button>
    </div>
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div className="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
        <p className="text-gray-500 text-sm">Progress Kuota Bulan Ini</p>
        <div className="mt-3">
          <div className="flex items-end gap-2">
            <span className="text-4xl font-bold">{mockData.publisher.stats.quotaProgress.current}</span>
            <span className="text-2xl text-gray-400">/ {mockData.publisher.stats.quotaProgress.total}</span>
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2 mt-3">
            <div className="bg-blue-600 h-2 rounded-full transition-all" style={{ width: `${(mockData.publisher.stats.quotaProgress.current / mockData.publisher.stats.quotaProgress.total) * 100}%` }}></div>
          </div>
          <p className="text-sm text-gray-600 mt-2">{mockData.publisher.stats.quotaProgress.current >= mockData.publisher.stats.quotaProgress.total ? '✓ Kuota terpenuhi' : `${mockData.publisher.stats.quotaProgress.total - mockData.publisher.stats.quotaProgress.current} konten lagi`}</p>
        </div>
      </div>
      <StatCard icon={CheckCircle} title="Approved" value={mockData.publisher.stats.approved} color="#10B981" />
      <StatCard icon={Clock} title="Pending" value={mockData.publisher.stats.pending} color="#F59E0B" />
      <StatCard icon={XCircle} title="Rejected" value={mockData.publisher.stats.rejected} color="#EF4444" />
    </div>
    <div className="bg-white rounded-lg shadow">
      <div className="p-6 border-b flex items-center justify-between">
        <h2 className="text-lg font-semibold">Riwayat Konten Saya</h2>
        <select className="border rounded-lg px-3 py-2 text-sm">
          <option>Semua Status</option><option>Approved</option><option>Pending</option><option>Rejected</option>
        </select>
      </div>
      <div className="divide-y">
        {mockData.publisher.myContents.map(content => (
          <div key={content.id} className="p-6 hover:bg-gray-50">
            <div className="flex items-start justify-between">
              <div className="flex-1">
                <div className="flex items-center gap-3">
                  <h3 className="font-semibold text-lg">{content.title}</h3>
                  <span className={`px-3 py-1 rounded-full text-xs font-medium ${content.status === 'approved' ? 'bg-green-100 text-green-800' : content.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}`}>
                    {content.status === 'approved' ? '✓ Approved' : content.status === 'pending' ? '⏱ Pending' : '✗ Rejected'}
                  </span>
                </div>
                <div className="flex gap-4 mt-2 text-sm text-gray-600">
                  <span>📁 {content.category}</span><span>📅 {content.date}</span>{content.status !== 'pending' && <span>✓ {content.verifier}</span>}
                </div>
                {content.reason !== '-' && (
                  <div className={`mt-3 p-3 rounded-lg text-sm ${content.status === 'approved' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'}`}>
                    <strong>Alasan:</strong> {content.reason}
                  </div>
                )}
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  </div>
);


// Verification Modal Component
const VerificationModal = ({ selectedContent, setShowVerificationModal }) => (
  <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div className="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div className="p-6 border-b flex items-center justify-between">
        <h2 className="text-xl font-semibold">Verifikasi Konten</h2>
        <button onClick={() => setShowVerificationModal(false)} className="text-gray-500 hover:text-gray-700"><X className="w-6 h-6" /></button>
      </div>
      <div className="p-6 space-y-4">
        <div>
          <h3 className="font-semibold text-lg">{selectedContent?.title}</h3>
          <div className="flex gap-4 mt-2 text-sm text-gray-600">
            <span>🏢 {selectedContent?.skpd}</span><span>👤 {selectedContent?.publisher}</span>
          </div>
        </div>
        <div>
          <label className="block text-sm font-medium mb-2">Alasan Verifikasi</label>
          <textarea className="w-full border rounded-lg p-3 min-h-[100px]" placeholder="Masukkan alasan approve/reject..."></textarea>
        </div>
        <div className="flex gap-3">
          <button className="flex-1 bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 flex items-center justify-center gap-2">
            <CheckCircle className="w-5 h-5" />Approve
          </button>
          <button className="flex-1 bg-red-600 text-white py-3 rounded-lg hover:bg-red-700 flex items-center justify-center gap-2">
            <XCircle className="w-5 h-5" />Reject
          </button>
        </div>
      </div>
    </div>
  </div>
);

// Content Input Modal Component
const ContentModal = ({ setShowContentModal }) => (
  <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div className="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div className="p-6 border-b flex items-center justify-between">
        <h2 className="text-xl font-semibold">Input Konten Baru</h2>
        <button onClick={() => setShowContentModal(false)} className="text-gray-500 hover:text-gray-700"><X className="w-6 h-6" /></button>
      </div>
      <div className="p-6 space-y-4">
        <div>
          <label className="block text-sm font-medium mb-2">Judul Konten *</label>
          <input type="text" className="w-full border rounded-lg p-3" placeholder="Masukkan judul konten" />
        </div>
        <div>
          <label className="block text-sm font-medium mb-2">Deskripsi *</label>
          <textarea className="w-full border rounded-lg p-3 min-h-[100px]" placeholder="Deskripsi konten"></textarea>
        </div>
        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium mb-2">Kategori *</label>
            <select className="w-full border rounded-lg p-3">
              <option>Pilih Kategori</option><option>Berita</option><option>Pengumuman</option><option>Artikel</option><option>Kegiatan</option><option>Layanan Publik</option>
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium mb-2">Tanggal Publikasi *</label>
            <input type="date" className="w-full border rounded-lg p-3" />
          </div>
        </div>
        <div>
          <label className="block text-sm font-medium mb-2">URL Publikasi *</label>
          <input type="url" className="w-full border rounded-lg p-3" placeholder="https://..." />
        </div>
        <div className="flex gap-3 pt-4">
          <button className="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700">Submit Konten</button>
          <button onClick={() => setShowContentModal(false)} className="flex-1 bg-gray-200 text-gray-700 py-3 rounded-lg hover:bg-gray-300">Batal</button>
        </div>
      </div>
    </div>
  </div>
);


// Main App Component
const App = () => {
  const [currentRole, setCurrentRole] = useState('admin');
  const [activeMenu, setActiveMenu] = useState('dashboard');
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [selectedContent, setSelectedContent] = useState(null);
  const [showVerificationModal, setShowVerificationModal] = useState(false);
  const [showContentModal, setShowContentModal] = useState(false);
  const [notifications] = useState([
    { id: 1, title: 'Konten Baru', message: 'Ada konten baru menunggu verifikasi', unread: true },
    { id: 2, title: 'Reminder Kuota', message: 'Kuota publikasi belum terpenuhi', unread: true }
  ]);

  return (
    <div className="flex h-screen bg-gray-100">
      {/* Sidebar */}
      <aside className={`${sidebarOpen ? 'w-64' : 'w-0'} bg-gradient-to-b from-blue-800 to-blue-900 text-white transition-all duration-300 overflow-hidden`}>
        <div className="p-6">
          <h1 className="text-xl font-bold">SKPD Content</h1>
          <p className="text-sm text-blue-200">Management System</p>
        </div>
        <nav className="mt-6">
          <div className="px-4 mb-2"><p className="text-xs text-blue-300 uppercase font-semibold">Role</p></div>
          <div className="space-y-1 px-2">
            <button onClick={() => setCurrentRole('admin')} className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg ${currentRole === 'admin' ? 'bg-blue-700' : 'hover:bg-blue-700'}`}>
              <Settings className="w-5 h-5" /><span>Admin</span>
            </button>
            <button onClick={() => setCurrentRole('operator')} className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg ${currentRole === 'operator' ? 'bg-blue-700' : 'hover:bg-blue-700'}`}>
              <CheckCircle className="w-5 h-5" /><span>Operator</span>
            </button>
            <button onClick={() => setCurrentRole('publisher')} className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg ${currentRole === 'publisher' ? 'bg-blue-700' : 'hover:bg-blue-700'}`}>
              <FileText className="w-5 h-5" /><span>Publisher</span>
            </button>
          </div>
          <div className="px-4 my-4"><div className="border-t border-blue-700"></div></div>
          <div className="space-y-1 px-2">
            <button onClick={() => setActiveMenu('dashboard')} className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg ${activeMenu === 'dashboard' ? 'bg-blue-700' : 'hover:bg-blue-700'}`}>
              <Home className="w-5 h-5" /><span>Dashboard</span>
            </button>
          </div>
        </nav>
        <div className="absolute bottom-0 w-64 p-4 border-t border-blue-700">
          <button className="w-full flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-700 text-red-300">
            <LogOut className="w-5 h-5" /><span>Logout</span>
          </button>
        </div>
      </aside>

      {/* Main Content */}
      <div className="flex-1 flex flex-col overflow-hidden">
        {/* Header */}
        <header className="bg-white shadow-sm border-b">
          <div className="flex items-center justify-between p-4">
            <button onClick={() => setSidebarOpen(!sidebarOpen)} className="p-2 hover:bg-gray-100 rounded-lg">
              <Menu className="w-6 h-6" />
            </button>
            <div className="flex items-center gap-4">
              <div className="relative">
                <button className="p-2 hover:bg-gray-100 rounded-lg relative">
                  <Bell className="w-6 h-6" />
                  {notifications.filter(n => n.unread).length > 0 && (
                    <span className="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                  )}
                </button>
              </div>
              <div className="flex items-center gap-3">
                <div className="text-right">
                  <p className="font-medium">{currentRole === 'admin' ? 'Admin Diskominfo' : currentRole === 'operator' ? 'Operator 1' : 'Budi Santoso'}</p>
                  <p className="text-sm text-gray-500 capitalize">{currentRole}</p>
                </div>
                <div className="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                  {currentRole[0].toUpperCase()}
                </div>
              </div>
            </div>
          </div>
        </header>

        {/* Content Area */}
        <main className="flex-1 overflow-y-auto p-6">
          {currentRole === 'admin' && <AdminDashboard />}
          {currentRole === 'operator' && <OperatorDashboard setSelectedContent={setSelectedContent} setShowVerificationModal={setShowVerificationModal} />}
          {currentRole === 'publisher' && <PublisherDashboard setShowContentModal={setShowContentModal} />}
        </main>
      </div>

      {/* Modals */}
      {showVerificationModal && <VerificationModal selectedContent={selectedContent} setShowVerificationModal={setShowVerificationModal} />}
      {showContentModal && <ContentModal setShowContentModal={setShowContentModal} />}
    </div>
  );
};

export default App;
