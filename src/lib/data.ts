/**
 * Edit file ini untuk mengisi data portofolio Anda.
 * Semua section di website tarik konten dari sini.
 */

export const personal = {
  name: "Aldi",
  fullName: "Aldi Perimacom",
  role: "Full-Stack Developer",
  tagline: "Membangun web modern, cepat, dan ramah pengguna.",
  bio: "Saya seorang full-stack developer yang fokus membangun aplikasi web modern dengan TypeScript, React, dan Node.js. Saya percaya kode yang baik adalah kode yang mudah dibaca, mudah di-maintain, dan menyelesaikan masalah nyata bagi penggunanya.",
  longBio:
    "Selama beberapa tahun terakhir saya bekerja di berbagai proyek mulai dari sistem e-commerce, ticketing, hingga aplikasi PPOB. Saya menyukai tantangan teknis dan terus belajar teknologi baru. Di luar coding, saya suka menulis, eksplorasi ide produk, dan ngopi.",
  location: "Indonesia",
  email: "aldiperimacom@gmail.com",
  phone: "+62 812-0000-0000",
  available: true,
  resumeUrl: "/resume.pdf",
  avatarUrl: "/avatar.svg",
  social: {
    github: "https://github.com/Aldi1963",
    linkedin: "https://linkedin.com/in/aldi",
    twitter: "https://twitter.com/aldi",
    instagram: "https://instagram.com/aldi",
  },
} as const;

export type SkillCategory = "frontend" | "backend" | "database" | "tools" | "soft";

export interface Skill {
  name: string;
  level: number; // 0-100
  category: SkillCategory;
}

export const skills: Skill[] = [
  { name: "TypeScript", level: 92, category: "frontend" },
  { name: "React / Next.js", level: 95, category: "frontend" },
  { name: "Tailwind CSS", level: 90, category: "frontend" },
  { name: "Framer Motion", level: 80, category: "frontend" },
  { name: "HTML / CSS", level: 95, category: "frontend" },
  { name: "Node.js", level: 88, category: "backend" },
  { name: "Express / Fastify", level: 85, category: "backend" },
  { name: "REST & GraphQL APIs", level: 82, category: "backend" },
  { name: "Python / FastAPI", level: 75, category: "backend" },
  { name: "PostgreSQL", level: 85, category: "database" },
  { name: "MongoDB", level: 78, category: "database" },
  { name: "Redis", level: 70, category: "database" },
  { name: "Prisma / Drizzle", level: 85, category: "database" },
  { name: "Git / GitHub", level: 92, category: "tools" },
  { name: "Docker", level: 78, category: "tools" },
  { name: "Vercel / Netlify", level: 90, category: "tools" },
  { name: "Linux", level: 80, category: "tools" },
  { name: "Communication", level: 90, category: "soft" },
  { name: "Problem Solving", level: 92, category: "soft" },
  { name: "Team Work", level: 88, category: "soft" },
];

export const skillCategoryLabel: Record<SkillCategory, string> = {
  frontend: "Frontend",
  backend: "Backend",
  database: "Database",
  tools: "Tools & DevOps",
  soft: "Soft Skills",
};

export type ProjectCategory = "web" | "mobile" | "fullstack" | "ui" | "open-source";

export interface Project {
  slug: string;
  title: string;
  short: string;
  description: string;
  category: ProjectCategory;
  tags: string[];
  image: string;
  demoUrl?: string;
  repoUrl?: string;
  featured?: boolean;
  year: number;
}

export const projects: Project[] = [
  {
    slug: "tiketing-event",
    title: "Event Ticketing Hub",
    short: "Platform pemesanan tiket event online dengan QR code & pembayaran terintegrasi.",
    description:
      "Aplikasi web untuk pemesanan tiket event yang mendukung multiple kategori, kursi, dan pembayaran. Tiket di-generate otomatis dalam bentuk QR code yang bisa dipindai panitia di lokasi. Dilengkapi dashboard admin untuk membuat event, melihat penjualan, dan mengelola peserta secara real time.",
    category: "fullstack",
    tags: ["Next.js", "TypeScript", "PostgreSQL", "Stripe", "QR Code"],
    image: "/projects/tiketing.svg",
    demoUrl: "https://example.com/tiketing",
    repoUrl: "https://github.com/Aldi1963/Tiketing-event",
    featured: true,
    year: 2026,
  },
  {
    slug: "ppob",
    title: "PPOB Dashboard",
    short: "Sistem Payment Point Online Bank dengan multi-vendor & laporan otomatis.",
    description:
      "Dashboard PPOB yang melayani transaksi pulsa, listrik, BPJS, dan lainnya. Mendukung banyak vendor sekaligus dengan logika failover otomatis kalau salah satu vendor down. Laporan keuangan dikirim otomatis tiap hari ke pemilik via email dan WhatsApp.",
    category: "fullstack",
    tags: ["TypeScript", "Node.js", "PostgreSQL", "Redis", "Cron"],
    image: "/projects/ppob.svg",
    demoUrl: "https://example.com/ppob",
    repoUrl: "https://github.com/Aldi1963/ppob",
    featured: true,
    year: 2026,
  },
  {
    slug: "wagataway",
    title: "Wagataway",
    short: "Aplikasi pemesanan paket wisata dengan rekomendasi cerdas.",
    description:
      "Wagataway adalah aplikasi pemesanan paket wisata lokal Indonesia. Memiliki fitur rekomendasi paket berdasarkan minat, riwayat, dan budget user. Dilengkapi sistem chat dengan tour guide dan integrasi maps untuk itinerary harian.",
    category: "web",
    tags: ["Next.js", "Tailwind", "Maps API", "Prisma"],
    image: "/projects/wagataway.svg",
    repoUrl: "https://github.com/Aldi1963/wagataway",
    featured: true,
    year: 2026,
  },
  {
    slug: "portfolio",
    title: "Personal Portfolio",
    short: "Portofolio modern dengan animasi halus dan dark mode.",
    description:
      "Website portofolio yang sedang Anda lihat sekarang. Dibangun dengan Next.js 16, Tailwind v4, Framer Motion, dan next-themes. Score Lighthouse 100/100/100/100, deploy ke Vercel.",
    category: "web",
    tags: ["Next.js", "Tailwind", "Framer Motion"],
    image: "/projects/portfolio.svg",
    repoUrl: "https://github.com/Aldi1963/portofolio-",
    featured: false,
    year: 2026,
  },
  {
    slug: "ui-kit",
    title: "Mini UI Kit",
    short: "Koleksi komponen UI reusable berbasis Tailwind & Radix.",
    description:
      "Open-source mini UI kit yang berisi 30+ komponen siap pakai berbasis Tailwind CSS dan Radix Primitives. Dibuat agar developer cepat membangun MVP tanpa rebuild komponen dasar dari nol.",
    category: "ui",
    tags: ["React", "Tailwind", "Radix", "Storybook"],
    image: "/projects/uikit.svg",
    repoUrl: "https://github.com/Aldi1963",
    featured: false,
    year: 2025,
  },
  {
    slug: "belajar-html",
    title: "Belajar HTML",
    short: "Sumber belajar HTML sederhana untuk pemula.",
    description:
      "Repository berisi materi belajar HTML dasar mulai dari struktur dokumen, semantic tags, sampai form. Dibuat sebagai bahan ajar saat saya membantu teman-teman yang baru mulai belajar web development.",
    category: "open-source",
    tags: ["HTML", "Tutorial"],
    image: "/projects/belajar.svg",
    repoUrl: "https://github.com/Aldi1963/belajar-html",
    featured: false,
    year: 2024,
  },
];

export const projectCategoryLabel: Record<ProjectCategory | "all", string> = {
  all: "Semua",
  web: "Web",
  mobile: "Mobile",
  fullstack: "Full-Stack",
  ui: "UI / Design",
  "open-source": "Open Source",
};

export interface ExperienceItem {
  role: string;
  company: string;
  location: string;
  start: string;
  end: string;
  current?: boolean;
  description: string;
  highlights: string[];
}

export const experience: ExperienceItem[] = [
  {
    role: "Senior Full-Stack Developer",
    company: "Freelance",
    location: "Remote",
    start: "Jan 2024",
    end: "Sekarang",
    current: true,
    description:
      "Mengerjakan proyek full-stack untuk klien dari berbagai industri (event, fintech, edukasi). Fokus pada delivery cepat dengan kualitas produksi.",
    highlights: [
      "Mengerjakan 10+ proyek dengan rating klien rata-rata 5/5",
      "Membangun arsitektur multi-tenant untuk SaaS internal klien",
      "Memimpin migrasi legacy PHP ke Next.js modern dengan downtime 0",
    ],
  },
  {
    role: "Full-Stack Developer",
    company: "Tech Startup",
    location: "Jakarta",
    start: "Mar 2022",
    end: "Des 2023",
    description:
      "Menjadi full-stack developer di tim engineering kecil yang membangun produk SaaS untuk UMKM.",
    highlights: [
      "Mempercepat halaman dashboard dari 6s menjadi 1.2s",
      "Implementasi sistem billing dengan Stripe & Midtrans",
      "Mentor untuk 3 junior developer",
    ],
  },
  {
    role: "Frontend Developer",
    company: "Digital Agency",
    location: "Bandung",
    start: "Jul 2020",
    end: "Feb 2022",
    description:
      "Membangun landing page dan web app untuk klien agensi dengan stack React & Next.js.",
    highlights: [
      "Delivery 25+ landing page dengan rata-rata Lighthouse 95+",
      "Standardisasi component library internal tim",
      "Dokumentasi onboarding untuk developer baru",
    ],
  },
];

export interface EducationItem {
  degree: string;
  school: string;
  location: string;
  start: string;
  end: string;
  description?: string;
}

export const education: EducationItem[] = [
  {
    degree: "S1 Teknik Informatika",
    school: "Universitas Indonesia",
    location: "Depok",
    start: "2016",
    end: "2020",
    description:
      "Lulus dengan IPK 3.65. Skripsi tentang sistem rekomendasi berbasis collaborative filtering.",
  },
  {
    degree: "Bootcamp Full-Stack JavaScript",
    school: "Hacktiv8",
    location: "Online",
    start: "2021",
    end: "2021",
    description: "Intensive 12-week bootcamp fokus pada MERN stack & engineering practices.",
  },
];

export interface Service {
  title: string;
  description: string;
  icon: string; // lucide icon name
}

export const services: Service[] = [
  {
    title: "Web Development",
    description:
      "Membangun website modern dari landing page sederhana sampai aplikasi web kompleks dengan stack terkini.",
    icon: "code",
  },
  {
    title: "Backend & API",
    description:
      "Desain & implementasi REST/GraphQL API yang scalable, terdokumentasi, dan mudah di-maintain.",
    icon: "server",
  },
  {
    title: "UI/UX Implementation",
    description:
      "Mengubah desain Figma jadi web yang pixel-perfect, responsif, dan beraksesibilitas baik.",
    icon: "palette",
  },
  {
    title: "Performance Optimization",
    description:
      "Audit & optimasi performa web — Core Web Vitals, bundle size, lazy loading, caching.",
    icon: "zap",
  },
  {
    title: "Consulting & Mentoring",
    description:
      "Code review, technical advice, dan mentoring untuk tim atau individual developer.",
    icon: "users",
  },
  {
    title: "Maintenance",
    description:
      "Update dependencies, fixing bugs, & continuous improvement untuk web yang sudah live.",
    icon: "wrench",
  },
];

export interface Testimonial {
  name: string;
  role: string;
  company: string;
  message: string;
  avatar: string;
}

export const testimonials: Testimonial[] = [
  {
    name: "Budi Santoso",
    role: "Founder",
    company: "EventKita",
    message:
      "Aldi membantu kami launching platform tiket dalam 6 minggu, padahal estimasi awal 3 bulan. Komunikasinya proaktif dan kualitas kode-nya jelas terlihat saat kami audit.",
    avatar: "/avatars/1.svg",
  },
  {
    name: "Sari Wulandari",
    role: "Product Manager",
    company: "FinTechId",
    message:
      "Engineer yang detail dan paham produk. Saya tidak perlu mikrofocus ke teknis karena Aldi selalu menawarkan solusi sebelum saya tanya.",
    avatar: "/avatars/2.svg",
  },
  {
    name: "Rizal Mahendra",
    role: "CTO",
    company: "Startup XYZ",
    message:
      "Sangat enjoyable bekerja sama. Refactoring frontend kami dari Vue 2 ke Next.js berjalan smooth, tanpa downtime, dengan dokumentasi yang rapi.",
    avatar: "/avatars/3.svg",
  },
  {
    name: "Linda Halim",
    role: "Designer",
    company: "Studio Pixel",
    message:
      "Hasil implementasi-nya pixel-perfect. Animasi-nya halus dan beneran sesuai prototype Figma yang saya kasih.",
    avatar: "/avatars/4.svg",
  },
];

export const stats = [
  { label: "Proyek Selesai", value: "30+" },
  { label: "Klien Puas", value: "20+" },
  { label: "Tahun Pengalaman", value: "5+" },
  { label: "Cangkir Kopi", value: "∞" },
];

export const navItems = [
  { id: "home", label: "Home" },
  { id: "about", label: "About" },
  { id: "skills", label: "Skills" },
  { id: "projects", label: "Projects" },
  { id: "experience", label: "Experience" },
  { id: "services", label: "Services" },
  { id: "testimonials", label: "Testimoni" },
  { id: "contact", label: "Contact" },
] as const;
