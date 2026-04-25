"use client";

import { useMemo, useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { ExternalLink, X } from "lucide-react";
import { Github } from "../brand-icons";
import { projects, projectCategoryLabel, type Project, type ProjectCategory } from "@/lib/data";
import { SectionHeading } from "../section-heading";
import { cn } from "@/lib/utils";

type Filter = ProjectCategory | "all";

const filters: Filter[] = ["all", "web", "fullstack", "ui", "open-source"];

export function Projects() {
  const [filter, setFilter] = useState<Filter>("all");
  const [active, setActive] = useState<Project | null>(null);

  const filtered = useMemo(
    () => (filter === "all" ? projects : projects.filter((p) => p.category === filter)),
    [filter],
  );

  return (
    <section id="projects" className="relative py-24 sm:py-32">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          eyebrow="Projects"
          title={<>Beberapa <span className="gradient-text">karya saya</span></>}
          description="Pilih kategori untuk memfilter, klik kartu untuk melihat detail proyek."
        />

        <div className="mb-10 flex flex-wrap justify-center gap-2">
          {filters.map((f) => (
            <button
              key={f}
              onClick={() => setFilter(f)}
              className={cn(
                "rounded-full border px-4 py-1.5 text-xs font-medium transition-colors",
                filter === f
                  ? "border-foreground bg-foreground text-background"
                  : "border-border bg-card/60 text-muted-foreground hover:bg-muted hover:text-foreground",
              )}
            >
              {projectCategoryLabel[f]}
            </button>
          ))}
        </div>

        <motion.ul
          layout
          className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
        >
          <AnimatePresence mode="popLayout">
            {filtered.map((p, i) => (
              <motion.li
                key={p.slug}
                layout
                initial={{ opacity: 0, y: 30 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, scale: 0.9 }}
                transition={{ duration: 0.35, delay: i * 0.04 }}
                className="group cursor-pointer"
                onClick={() => setActive(p)}
              >
                <div className="relative h-full overflow-hidden rounded-2xl border border-border bg-card/60 transition-all hover:-translate-y-1 hover:border-primary/40 hover:shadow-lg hover:shadow-primary/10">
                  <div className="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-primary/20 via-muted to-accent/20">
                    <div className="absolute inset-0 flex items-center justify-center">
                      <span className="font-mono text-3xl font-bold text-foreground/40">
                        {p.title}
                      </span>
                    </div>
                    {p.featured && (
                      <span className="absolute left-3 top-3 rounded-full bg-foreground px-2 py-0.5 text-[10px] font-medium uppercase tracking-wider text-background">
                        Featured
                      </span>
                    )}
                    <span className="absolute right-3 top-3 rounded-full border border-border bg-background/80 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wider backdrop-blur">
                      {p.year}
                    </span>
                  </div>
                  <div className="p-5">
                    <h3 className="text-base font-semibold transition-colors group-hover:text-primary">
                      {p.title}
                    </h3>
                    <p className="mt-2 line-clamp-2 text-sm text-muted-foreground">
                      {p.short}
                    </p>
                    <div className="mt-4 flex flex-wrap gap-1.5">
                      {p.tags.slice(0, 4).map((t) => (
                        <span
                          key={t}
                          className="rounded-full bg-muted px-2 py-0.5 text-[10px] font-medium text-muted-foreground"
                        >
                          {t}
                        </span>
                      ))}
                    </div>
                  </div>
                </div>
              </motion.li>
            ))}
          </AnimatePresence>
        </motion.ul>
      </div>

      <ProjectModal project={active} onClose={() => setActive(null)} />
    </section>
  );
}

function ProjectModal({ project, onClose }: { project: Project | null; onClose: () => void }) {
  return (
    <AnimatePresence>
      {project && (
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          className="fixed inset-0 z-50 flex items-end justify-center bg-black/60 p-4 backdrop-blur sm:items-center"
          onClick={onClose}
        >
          <motion.div
            initial={{ y: 40, opacity: 0, scale: 0.97 }}
            animate={{ y: 0, opacity: 1, scale: 1 }}
            exit={{ y: 40, opacity: 0, scale: 0.97 }}
            transition={{ type: "spring", damping: 24, stiffness: 280 }}
            className="relative w-full max-w-2xl overflow-hidden rounded-2xl border border-border bg-background shadow-2xl"
            onClick={(e) => e.stopPropagation()}
          >
            <button
              type="button"
              aria-label="Tutup"
              onClick={onClose}
              className="absolute right-4 top-4 z-10 inline-flex size-8 items-center justify-center rounded-full bg-background/80 backdrop-blur transition-colors hover:bg-muted"
            >
              <X className="size-4" />
            </button>
            <div className="relative aspect-[16/9] bg-gradient-to-br from-primary/30 via-muted to-accent/30">
              <div className="absolute inset-0 flex items-center justify-center">
                <span className="font-mono text-4xl font-bold text-foreground/40">
                  {project.title}
                </span>
              </div>
            </div>
            <div className="p-6 sm:p-8">
              <div className="mb-2 flex items-center gap-2 text-xs text-muted-foreground">
                <span>{project.year}</span>
                <span>·</span>
                <span className="capitalize">{project.category}</span>
              </div>
              <h3 className="text-2xl font-bold">{project.title}</h3>
              <p className="mt-3 leading-relaxed text-muted-foreground">
                {project.description}
              </p>
              <div className="mt-5 flex flex-wrap gap-2">
                {project.tags.map((t) => (
                  <span
                    key={t}
                    className="rounded-full border border-border bg-card/60 px-2.5 py-0.5 text-xs"
                  >
                    {t}
                  </span>
                ))}
              </div>
              <div className="mt-6 flex flex-wrap gap-3">
                {project.demoUrl && (
                  <a
                    href={project.demoUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-2 rounded-full bg-foreground px-4 py-2 text-sm font-medium text-background transition-opacity hover:opacity-90"
                  >
                    <ExternalLink className="size-4" /> Live Demo
                  </a>
                )}
                {project.repoUrl && (
                  <a
                    href={project.repoUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-2 rounded-full border border-border bg-card/60 px-4 py-2 text-sm font-medium transition-colors hover:bg-muted"
                  >
                    <Github className="size-4" /> Source
                  </a>
                )}
              </div>
            </div>
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
