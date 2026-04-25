"use client";

import { motion } from "framer-motion";
import { ArrowRight, Download, Mail, Sparkles } from "lucide-react";
import { Github, Linkedin } from "../brand-icons";
import { personal, stats } from "@/lib/data";

export function Hero() {
  return (
    <section
      id="home"
      className="relative isolate flex min-h-dvh items-center overflow-hidden pt-24"
    >
      <div className="absolute inset-0 -z-10 bg-grid opacity-50" />
      <div className="absolute inset-x-0 top-0 -z-10 mx-auto h-[600px] max-w-5xl">
        <div className="absolute left-1/4 top-20 size-72 rounded-full bg-primary/30 blur-3xl" />
        <div className="absolute right-1/4 top-40 size-72 rounded-full bg-accent/30 blur-3xl" />
      </div>

      <div className="mx-auto grid w-full max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1.2fr_1fr] lg:gap-16 lg:px-8">
        <div>
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
            className="inline-flex items-center gap-2 rounded-full border border-border bg-card/60 px-3 py-1 text-xs"
          >
            <Sparkles className="size-3.5 text-primary" />
            <span className="text-muted-foreground">
              {personal.available ? "Tersedia untuk proyek baru" : "Sedang sibuk"}
            </span>
            <span className="relative flex size-1.5">
              <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-60" />
              <span className="relative inline-flex size-1.5 rounded-full bg-primary" />
            </span>
          </motion.div>

          <motion.h1
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.1 }}
            className="mt-6 text-balance text-4xl font-bold leading-tight tracking-tight sm:text-5xl lg:text-6xl"
          >
            Halo, saya{" "}
            <span className="gradient-text">{personal.name}</span>
            <br />
            <span className="text-muted-foreground">{personal.role}</span>
          </motion.h1>

          <motion.p
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.2 }}
            className="mt-6 max-w-xl text-pretty text-base leading-relaxed text-muted-foreground sm:text-lg"
          >
            {personal.tagline} {personal.bio}
          </motion.p>

          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.3 }}
            className="mt-8 flex flex-wrap items-center gap-3"
          >
            <a
              href="#contact"
              className="group inline-flex items-center gap-2 rounded-full bg-foreground px-5 py-2.5 text-sm font-medium text-background transition-opacity hover:opacity-90"
            >
              Hubungi saya
              <ArrowRight className="size-4 transition-transform group-hover:translate-x-1" />
            </a>
            <a
              href="#projects"
              className="inline-flex items-center gap-2 rounded-full border border-border bg-card/60 px-5 py-2.5 text-sm font-medium transition-colors hover:bg-muted"
            >
              Lihat proyek
            </a>
            <a
              href={personal.resumeUrl}
              className="inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
            >
              <Download className="size-4" /> Resume
            </a>
          </motion.div>

          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.5, delay: 0.4 }}
            className="mt-8 flex items-center gap-3"
          >
            <a
              href={personal.social.github}
              aria-label="GitHub"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex size-9 items-center justify-center rounded-full border border-border bg-card/60 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
            >
              <Github className="size-4" />
            </a>
            <a
              href={personal.social.linkedin}
              aria-label="LinkedIn"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex size-9 items-center justify-center rounded-full border border-border bg-card/60 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
            >
              <Linkedin className="size-4" />
            </a>
            <a
              href={`mailto:${personal.email}`}
              aria-label="Email"
              className="inline-flex size-9 items-center justify-center rounded-full border border-border bg-card/60 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
            >
              <Mail className="size-4" />
            </a>
            <span className="text-xs text-muted-foreground">{personal.location}</span>
          </motion.div>
        </div>

        {/* Right: Decorative card */}
        <motion.div
          initial={{ opacity: 0, scale: 0.95 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ duration: 0.6, delay: 0.2 }}
          className="relative mx-auto w-full max-w-md"
        >
          <div className="gradient-border relative aspect-square overflow-hidden rounded-3xl bg-card/40 backdrop-blur">
            <div className="absolute inset-0 bg-dots opacity-40" />
            <div className="absolute inset-0 flex flex-col items-center justify-center p-8">
              <motion.div
                animate={{ y: [0, -10, 0] }}
                transition={{ duration: 4, repeat: Infinity, ease: "easeInOut" }}
                className="relative flex size-40 items-center justify-center rounded-full bg-gradient-to-br from-primary to-accent text-7xl font-bold text-primary-foreground glow"
              >
                {personal.name.charAt(0)}
              </motion.div>
              <p className="mt-6 text-center font-mono text-xs text-muted-foreground">
                ~/{personal.name.toLowerCase()}.dev
              </p>
              <pre className="mt-2 rounded-lg bg-muted px-3 py-2 font-mono text-[11px] text-muted-foreground">
{`> hire(${personal.name.toLowerCase()})\n${"  "}// status: ${personal.available ? "available ✓" : "busy ✗"}`}
              </pre>
            </div>
          </div>

          {/* Floating stats */}
          <div className="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            {stats.map((s) => (
              <div
                key={s.label}
                className="rounded-xl border border-border bg-card/60 p-3 text-center backdrop-blur"
              >
                <p className="text-lg font-bold gradient-text">{s.value}</p>
                <p className="text-[10px] uppercase tracking-wider text-muted-foreground">
                  {s.label}
                </p>
              </div>
            ))}
          </div>
        </motion.div>
      </div>

      <motion.div
        animate={{ y: [0, 8, 0] }}
        transition={{ duration: 2, repeat: Infinity }}
        className="absolute bottom-6 left-1/2 -translate-x-1/2"
      >
        <a href="#about" aria-label="Scroll ke About" className="text-xs text-muted-foreground">
          Scroll ↓
        </a>
      </motion.div>
    </section>
  );
}
