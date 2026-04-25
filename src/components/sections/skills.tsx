"use client";

import { motion } from "framer-motion";
import { skills, skillCategoryLabel, type SkillCategory } from "@/lib/data";
import { SectionHeading } from "../section-heading";

const categories: SkillCategory[] = ["frontend", "backend", "database", "tools", "soft"];

export function Skills() {
  return (
    <section id="skills" className="relative py-24 sm:py-32">
      <div className="absolute inset-0 -z-10 bg-dots opacity-30" />
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <SectionHeading
          eyebrow="Skills"
          title={<>Keahlian <span className="gradient-text">teknis</span></>}
          description="Stack & tools yang saya pakai sehari-hari, dengan tingkat kenyamanan saat menggunakannya."
        />

        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          {categories.map((cat, ci) => {
            const items = skills.filter((s) => s.category === cat);
            if (items.length === 0) return null;
            return (
              <motion.div
                key={cat}
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, amount: 0.2 }}
                transition={{ duration: 0.5, delay: ci * 0.05 }}
                className="rounded-2xl border border-border bg-card/60 p-6 backdrop-blur"
              >
                <h3 className="mb-5 text-sm font-semibold uppercase tracking-widest text-muted-foreground">
                  {skillCategoryLabel[cat]}
                </h3>
                <ul className="space-y-4">
                  {items.map((s, i) => (
                    <li key={s.name}>
                      <div className="mb-1.5 flex items-center justify-between text-sm">
                        <span className="font-medium">{s.name}</span>
                        <span className="font-mono text-xs text-muted-foreground">
                          {s.level}%
                        </span>
                      </div>
                      <div className="h-1.5 overflow-hidden rounded-full bg-muted">
                        <motion.div
                          initial={{ width: 0 }}
                          whileInView={{ width: `${s.level}%` }}
                          viewport={{ once: true, amount: 0.4 }}
                          transition={{ duration: 0.8, delay: 0.1 + i * 0.04, ease: "easeOut" }}
                          className="h-full rounded-full bg-gradient-to-r from-primary to-accent"
                        />
                      </div>
                    </li>
                  ))}
                </ul>
              </motion.div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
