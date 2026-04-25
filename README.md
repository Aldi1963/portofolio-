# Portofolio Aldi

Personal portfolio website built with **Next.js 16**, **TypeScript**, **Tailwind CSS v4**, **Framer Motion**, and **next-themes**.

## Fitur

- Hero section dengan animasi & CTA
- About / Bio section
- Skills section dengan kategori (frontend, backend, tools, soft skills)
- Projects section dengan filter kategori dan modal detail
- Experience timeline
- Education timeline
- Services / What I Do
- Testimonials carousel
- Contact form (terintegrasi dengan API route)
- Dark / Light mode toggle (persist via `next-themes`)
- Scroll progress indicator & animasi on-scroll
- Smooth scroll antar section
- Responsive untuk mobile, tablet, & desktop
- SEO ready (metadata, OpenGraph, sitemap, robots.txt)
- Deploy-ready ke Vercel

## Stack

| Kategori | Tools |
|----------|-------|
| Framework | Next.js 16 (App Router) |
| Bahasa | TypeScript |
| Styling | Tailwind CSS v4 |
| Animasi | Framer Motion |
| Icons | Lucide React |
| Theme | next-themes |
| Form | React Hook Form (lightweight) |

## Setup

```bash
npm install
npm run dev
```

Buka [http://localhost:3000](http://localhost:3000).

## Build

```bash
npm run build
npm run start
```

## Kustomisasi

Semua data portofolio (nama, bio, skills, projects, experience, dll.) ada di file:

```
src/lib/data.ts
```

Edit file tersebut untuk mengganti konten dengan data Anda sendiri.

## Deploy

Deploy paling mudah lewat [Vercel](https://vercel.com/new) — klik import dari GitHub, semua sudah jalan otomatis.

## Lisensi

MIT
